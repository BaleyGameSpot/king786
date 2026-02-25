<?php
/**
 * FEATURE 2: BOOK FOR OTHERS
 * --------------------------
 * Allows a registered user to book a ride on behalf of a third party.
 * The booker pays, but the beneficiary receives the ride.
 * Sends SMS/push notifications to the beneficiary.
 *
 * Integration: Call from webservice_shark.php with type='bookForOthers'
 */

class BookForOthers
{
    private $db;
    private $config;

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['book_others_action'] ?? '');

        switch ($action) {
            case 'create':          return $this->createBooking($req);
            case 'getMyBookings':   return $this->getMyBookings($req);
            case 'getBookingDetail':return $this->getBookingDetail($req);
            case 'cancel':          return $this->cancelBooking($req);
            case 'updateBeneficiary': return $this->updateBeneficiary($req);
            default:
                return $this->error('Unknown book_others_action.');
        }
    }

    // ----------------------------------------------------------------
    // Create a booking on behalf of another person
    // ----------------------------------------------------------------
    private function createBooking(array $req): string
    {
        $requestId          = (int)($req['iRequestId']           ?? 0);
        $bookedByUserId     = (int)($req['iUserId']              ?? 0);
        $beneficiaryName    = trim($req['vBeneficiaryName']      ?? '');
        $beneficiaryPhone   = trim($req['vBeneficiaryPhone']     ?? '');
        $beneficiaryCC      = trim($req['vBeneficiaryCountryCode'] ?? '+55');
        $relationship       = trim($req['eRelationship']         ?? 'Other');
        $notifyBeneficiary  = in_array($req['eNotifyBeneficiary'] ?? 'Yes', ['Yes', 'No'])
                                ? $req['eNotifyBeneficiary']
                                : 'Yes';

        if (!$requestId || !$bookedByUserId || !$beneficiaryName || !$beneficiaryPhone) {
            return $this->error('iRequestId, iUserId, vBeneficiaryName and vBeneficiaryPhone are required.');
        }

        // Validate phone format
        if (!preg_match('/^\+?[0-9]{7,15}$/', $beneficiaryPhone)) {
            return $this->error('Invalid beneficiary phone number format.');
        }

        $allowedRelationships = ['Family', 'Friend', 'Colleague', 'Other'];
        if (!in_array($relationship, $allowedRelationships)) {
            $relationship = 'Other';
        }

        $beneficiaryName  = addslashes(strip_tags($beneficiaryName));
        $beneficiaryPhone = addslashes($beneficiaryPhone);
        $beneficiaryCC    = addslashes($beneficiaryCC);
        $now              = date('Y-m-d H:i:s');

        $this->db->sql_query(
            "INSERT INTO ride_book_for_others
                (iRequestId, iBookedByUserId, vBeneficiaryName, vBeneficiaryPhone,
                 vBeneficiaryCountryCode, eRelationship, eNotifyBeneficiary, eStatus, dCreatedAt)
             VALUES
                ($requestId, $bookedByUserId, '$beneficiaryName', '$beneficiaryPhone',
                 '$beneficiaryCC', '$relationship', '$notifyBeneficiary', 'Active', '$now')"
        );
        $bookForOthersId = $this->db->MySQLLastInsertID();

        // Notify beneficiary via SMS if enabled
        if ($notifyBeneficiary === 'Yes') {
            $this->notifyBeneficiary($beneficiaryPhone, $beneficiaryCC, $beneficiaryName, $requestId);
        }

        // Fetch booker name for notifications
        $booker = $this->db->MySQLSelect(
            "SELECT vName FROM register WHERE iUserId=$bookedByUserId LIMIT 1"
        );
        $bookerName = $booker[0]['vName'] ?? 'Someone';

        return $this->success([
            'iBookForOthersId' => $bookForOthersId,
            'message'          => "Ride booked for $beneficiaryName.",
            'bookedBy'         => $bookerName,
        ]);
    }

    // ----------------------------------------------------------------
    // List all bookings made by this user for others
    // ----------------------------------------------------------------
    private function getMyBookings(array $req): string
    {
        $userId = (int)($req['iUserId'] ?? 0);
        if (!$userId) return $this->error('iUserId required.');

        $bookings = $this->db->MySQLSelect(
            "SELECT bfo.*, dr.eStatus AS eRequestStatus,
                    dr.tStartAddress, dr.tEndAddress, dr.dAddedDate
             FROM ride_book_for_others AS bfo
             LEFT JOIN driver_request AS dr ON dr.iRequestId = bfo.iRequestId
             WHERE bfo.iBookedByUserId = $userId
             ORDER BY bfo.dCreatedAt DESC
             LIMIT 50"
        );

        return $this->success(['bookings' => $bookings ?: []]);
    }

    // ----------------------------------------------------------------
    // Get single booking detail
    // ----------------------------------------------------------------
    private function getBookingDetail(array $req): string
    {
        $bookForOthersId = (int)($req['iBookForOthersId'] ?? 0);
        $userId          = (int)($req['iUserId']           ?? 0);

        if (!$bookForOthersId) return $this->error('iBookForOthersId required.');

        $whereUser = $userId ? "AND bfo.iBookedByUserId = $userId" : '';

        $rows = $this->db->MySQLSelect(
            "SELECT bfo.*, dr.eStatus AS eRequestStatus, dr.tStartAddress, dr.tEndAddress,
                    dr.vStartLatlong, dr.vEndLatlong, dr.dAddedDate
             FROM ride_book_for_others bfo
             LEFT JOIN driver_request dr ON dr.iRequestId = bfo.iRequestId
             WHERE bfo.iBookForOthersId = $bookForOthersId $whereUser
             LIMIT 1"
        );

        if (empty($rows)) return $this->error('Booking not found.');

        return $this->success(['booking' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // Cancel a book-for-others booking
    // ----------------------------------------------------------------
    private function cancelBooking(array $req): string
    {
        $bookForOthersId = (int)($req['iBookForOthersId'] ?? 0);
        $userId          = (int)($req['iUserId']           ?? 0);

        if (!$bookForOthersId || !$userId) {
            return $this->error('iBookForOthersId and iUserId required.');
        }

        $this->db->sql_query(
            "UPDATE ride_book_for_others
             SET eStatus='Cancelled'
             WHERE iBookForOthersId=$bookForOthersId AND iBookedByUserId=$userId"
        );

        return $this->success(['message' => 'Booking cancelled.']);
    }

    // ----------------------------------------------------------------
    // Update beneficiary contact details (before ride starts)
    // ----------------------------------------------------------------
    private function updateBeneficiary(array $req): string
    {
        $bookForOthersId = (int)($req['iBookForOthersId']    ?? 0);
        $userId          = (int)($req['iUserId']              ?? 0);
        $newName         = addslashes(strip_tags(trim($req['vBeneficiaryName']  ?? '')));
        $newPhone        = addslashes(trim($req['vBeneficiaryPhone'] ?? ''));

        if (!$bookForOthersId || !$userId) {
            return $this->error('iBookForOthersId and iUserId required.');
        }

        $updates = [];
        if ($newName)  $updates[] = "vBeneficiaryName='$newName'";
        if ($newPhone) $updates[] = "vBeneficiaryPhone='$newPhone'";

        if (empty($updates)) return $this->error('Nothing to update.');

        $this->db->sql_query(
            "UPDATE ride_book_for_others
             SET " . implode(', ', $updates) . "
             WHERE iBookForOthersId=$bookForOthersId AND iBookedByUserId=$userId
               AND eStatus='Active'"
        );

        return $this->success(['message' => 'Beneficiary updated.']);
    }

    // ----------------------------------------------------------------
    // Notify beneficiary via SMS (uses platform's existing SMS sender)
    // ----------------------------------------------------------------
    private function notifyBeneficiary(string $phone, string $cc, string $name, int $requestId): void
    {
        $message = "Hi $name, a ride has been booked for you (Ref #$requestId). "
                 . "Your driver will arrive shortly. - Ridey App";

        if (function_exists('sendSMS')) {
            sendSMS($cc . $phone, $message);
        }
        // Also try the platform's sendVerificationSMS pattern if available
        if (function_exists('sendVerificationNotification')) {
            sendVerificationNotification($phone, $cc, $message);
        }
    }

    private function success(array $data): string
    {
        return json_encode(array_merge(['status' => 'success'], $data));
    }

    private function error(string $message): string
    {
        return json_encode(['status' => 'error', 'message' => $message]);
    }
}
