<?php
/**
 * FEATURE 5: LOST & FOUND CHAT
 * -----------------------------
 * Direct communication between passenger, driver and support.
 * Includes option to create a paid "return trip" for item delivery.
 *
 * Flow:
 *   1. Passenger opens a ticket after a completed trip.
 *   2. Admin/Support assigns and communicates via chat.
 *   3. If item is found, admin can create a paid return trip booking.
 *   4. Driver is notified and can accept the return trip.
 */

class LostAndFound
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
        $action = trim($req['laf_action'] ?? '');

        switch ($action) {
            case 'openTicket':       return $this->openTicket($req);
            case 'sendMessage':      return $this->sendMessage($req);
            case 'getMessages':      return $this->getMessages($req);
            case 'getTickets':       return $this->getTickets($req);
            case 'getTicketDetail':  return $this->getTicketDetail($req);
            case 'updateStatus':     return $this->updateTicketStatus($req);
            case 'createReturnTrip': return $this->createReturnTrip($req);
            case 'uploadImage':      return $this->uploadItemImage($req);
            case 'closeTicket':      return $this->closeTicket($req);
            default:
                return $this->error('Unknown laf_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Open a lost-and-found ticket
    // ----------------------------------------------------------------
    private function openTicket(array $req): string
    {
        $userId          = (int)($req['iUserId']          ?? 0);
        $driverId        = (int)($req['iDriverId']         ?? 0);
        $tripId          = (int)($req['iTripId']           ?? 0);
        $requestId       = (int)($req['iRequestId']        ?? 0);
        $description     = strip_tags(trim($req['vItemDescription'] ?? ''));
        $category        = strip_tags(trim($req['vItemCategory']    ?? ''));

        if (!$userId || !$driverId || !$tripId || !$description) {
            return $this->error('iUserId, iDriverId, iTripId and vItemDescription required.');
        }

        // Prevent duplicate open tickets for same trip
        $existing = $this->db->MySQLSelect(
            "SELECT iTicketId FROM lost_found_tickets
             WHERE iUserId=$userId AND iTripId=$tripId AND eStatus NOT IN ('Closed','ItemReturned')
             LIMIT 1"
        );
        if (!empty($existing)) {
            return $this->error('An open ticket already exists for this trip.');
        }

        $description = addslashes($description);
        $category    = addslashes($category);
        $now         = date('Y-m-d H:i:s');

        $this->db->sql_query(
            "INSERT INTO lost_found_tickets
                (iUserId, iDriverId, iTripId, iRequestId, vItemDescription, vItemCategory,
                 eStatus, eReturnTripCreated, dCreatedAt)
             VALUES
                ($userId, $driverId, $tripId, $requestId, '$description', '$category',
                 'Open', 'No', '$now')"
        );
        $ticketId = $this->db->MySQLLastInsertID();

        // Auto-send system message
        $this->insertMessage($ticketId, 'System', 0,
            "Lost & Found ticket #$ticketId opened. Our support team will contact you shortly."
        );

        // Notify driver
        $this->notifyDriver($driverId, $ticketId, 'A passenger reported a lost item from your recent trip.');

        // Notify admin
        $this->notifyAdmins($ticketId, "New lost item report from user #$userId on trip #$tripId.");

        return $this->success([
            'iTicketId' => $ticketId,
            'message'   => 'Ticket opened. Support will reach you soon.',
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Send a chat message
    // ----------------------------------------------------------------
    private function sendMessage(array $req): string
    {
        $ticketId    = (int)($req['iTicketId']   ?? 0);
        $senderType  = trim($req['eSenderType']  ?? '');
        $senderId    = (int)($req['iSenderId']   ?? 0);
        $message     = strip_tags(trim($req['vMessage'] ?? ''));

        if (!$ticketId || !in_array($senderType, ['Passenger', 'Driver', 'Admin', 'System']) || !$message) {
            return $this->error('iTicketId, eSenderType (Passenger|Driver|Admin|System) and vMessage required.');
        }

        $ticket = $this->fetchTicket($ticketId);
        if (!$ticket) return $this->error('Ticket not found.');
        if ($ticket['eStatus'] === 'Closed') return $this->error('Ticket is closed. No new messages.');

        $messageId = $this->insertMessage($ticketId, $senderType, $senderId, $message);

        // Notify the other parties
        $this->notifyOnNewMessage($ticket, $senderType, $senderId, $message);

        return $this->success(['iMessageId' => $messageId, 'message' => 'Message sent.']);
    }

    // ----------------------------------------------------------------
    // 3. Get all messages for a ticket
    // ----------------------------------------------------------------
    private function getMessages(array $req): string
    {
        $ticketId = (int)($req['iTicketId'] ?? 0);
        $userId   = (int)($req['iUserId']   ?? 0);
        $driverId = (int)($req['iDriverId'] ?? 0);

        if (!$ticketId) return $this->error('iTicketId required.');

        $ticket = $this->fetchTicket($ticketId);
        if (!$ticket) return $this->error('Ticket not found.');

        // Authorisation check
        if ($userId && $ticket['iUserId'] != $userId && $driverId && $ticket['iDriverId'] != $driverId) {
            return $this->error('Not authorised.');
        }

        $messages = $this->db->MySQLSelect(
            "SELECT * FROM lost_found_messages WHERE iTicketId=$ticketId ORDER BY dCreatedAt ASC"
        );

        // Mark messages as read
        if ($userId) {
            $this->db->sql_query(
                "UPDATE lost_found_messages SET eIsRead='Yes'
                 WHERE iTicketId=$ticketId AND eSenderType IN ('Driver','Admin','System') AND eIsRead='No'"
            );
        }

        return $this->success(['messages' => $messages ?: [], 'ticket' => $ticket]);
    }

    // ----------------------------------------------------------------
    // 4. Get list of tickets for user or driver
    // ----------------------------------------------------------------
    private function getTickets(array $req): string
    {
        $userId   = (int)($req['iUserId']   ?? 0);
        $driverId = (int)($req['iDriverId'] ?? 0);
        $status   = trim($req['eStatus']    ?? '');

        if (!$userId && !$driverId) return $this->error('iUserId or iDriverId required.');

        $where  = $userId ? "lft.iUserId=$userId" : "lft.iDriverId=$driverId";
        $sWhere = $status ? " AND lft.eStatus='" . addslashes($status) . "'" : '';

        $tickets = $this->db->MySQLSelect(
            "SELECT lft.*,
                    (SELECT COUNT(*) FROM lost_found_messages lfm
                     WHERE lfm.iTicketId=lft.iTicketId AND lfm.eIsRead='No'
                       AND lfm.eSenderType != '" . ($userId ? 'Passenger' : 'Driver') . "') AS iUnreadCount
             FROM lost_found_tickets lft
             WHERE $where $sWhere
             ORDER BY lft.dUpdatedAt DESC
             LIMIT 50"
        );

        return $this->success(['tickets' => $tickets ?: []]);
    }

    // ----------------------------------------------------------------
    // 5. Get single ticket detail
    // ----------------------------------------------------------------
    private function getTicketDetail(array $req): string
    {
        $ticketId = (int)($req['iTicketId'] ?? 0);
        if (!$ticketId) return $this->error('iTicketId required.');

        $ticket = $this->fetchTicket($ticketId);
        if (!$ticket) return $this->error('Ticket not found.');

        return $this->success(['ticket' => $ticket]);
    }

    // ----------------------------------------------------------------
    // 6. Update ticket status (admin / support)
    // ----------------------------------------------------------------
    private function updateTicketStatus(array $req): string
    {
        $ticketId   = (int)($req['iTicketId']  ?? 0);
        $adminId    = (int)($req['iAdminId']   ?? 0);
        $newStatus  = trim($req['eStatus']     ?? '');
        $adminNote  = addslashes(strip_tags($req['vAdminNote'] ?? ''));

        $allowed = ['Open', 'InProgress', 'ItemFound', 'ItemReturned', 'Closed', 'ReturnTripCreated'];
        if (!$ticketId || !in_array($newStatus, $allowed)) {
            return $this->error('iTicketId and valid eStatus required.');
        }

        $this->db->sql_query(
            "UPDATE lost_found_tickets
             SET eStatus='$newStatus', iHandledByAdminId=$adminId, vAdminNote='$adminNote'
             WHERE iTicketId=$ticketId"
        );

        $ticket = $this->fetchTicket($ticketId);
        $this->notifyPassengerTicketUpdate($ticket, $newStatus);

        return $this->success(['message' => "Ticket status updated to $newStatus."]);
    }

    // ----------------------------------------------------------------
    // 7. Create a paid return trip (admin or user request)
    // ----------------------------------------------------------------
    private function createReturnTrip(array $req): string
    {
        $ticketId      = (int)($req['iTicketId']    ?? 0);
        $returnFare    = (float)($req['fReturnTripFare'] ?? 0);
        $pickupLatLng  = trim($req['vPickupLatLng'] ?? '');
        $dropLatLng    = trim($req['vDropLatLng']   ?? '');

        if (!$ticketId || $returnFare <= 0) {
            return $this->error('iTicketId and fReturnTripFare required.');
        }

        $ticket = $this->fetchTicket($ticketId);
        if (!$ticket) return $this->error('Ticket not found.');
        if ($ticket['eReturnTripCreated'] === 'Yes') {
            return $this->error('Return trip already created for this ticket.');
        }

        // Create a new ride request for the return trip
        // Uses existing driver_request table with special note
        $now  = date('Y-m-d H:i:s');
        $note = "LOST_FOUND_RETURN:$ticketId";
        $pickupLatLng = addslashes($pickupLatLng);
        $dropLatLng   = addslashes($dropLatLng);

        $this->db->sql_query(
            "INSERT INTO driver_request
                (iDriverId, iUserId, eStatus, vStartLatlong, vEndLatlong,
                 fTaxiBidAmount, eBidMode, dAddedDate, vMsgCode)
             VALUES
                ({$ticket['iDriverId']}, {$ticket['iUserId']}, 'Pending',
                 '$pickupLatLng', '$dropLatLng',
                 $returnFare, 'Standard', '$now', '$note')"
        );
        $returnTripRequestId = $this->db->MySQLLastInsertID();

        $this->db->sql_query(
            "UPDATE lost_found_tickets
             SET eReturnTripCreated='Yes', iReturnTripRequestId=$returnTripRequestId,
                 fReturnTripFare=$returnFare, eStatus='ReturnTripCreated'
             WHERE iTicketId=$ticketId"
        );

        // Notify driver of the return trip
        $this->notifyDriver(
            (int)$ticket['iDriverId'],
            $ticketId,
            "You have a paid return trip (R$ " . number_format($returnFare, 2, ',', '.') . ") to return a lost item."
        );

        // Notify passenger
        $this->notifyPassengerReturnTrip((int)$ticket['iUserId'], $ticketId, $returnFare, $returnTripRequestId);

        return $this->success([
            'iReturnTripRequestId' => $returnTripRequestId,
            'fReturnTripFare'      => $returnFare,
            'message'              => 'Return trip created successfully.',
        ]);
    }

    // ----------------------------------------------------------------
    // 8. Upload image of lost item
    // ----------------------------------------------------------------
    private function uploadItemImage(array $req): string
    {
        $ticketId = (int)($req['iTicketId'] ?? 0);
        if (!$ticketId) return $this->error('iTicketId required.');

        if (empty($_FILES['itemImage'])) {
            return $this->error('No image file uploaded (field: itemImage).');
        }

        $file       = $_FILES['itemImage'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            return $this->error('Invalid image type. Allowed: jpg, jpeg, png, gif, webp.');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            return $this->error('Image too large. Max 5MB.');
        }

        $uploadDir  = $this->config['tsite_upload_images_path'] ?? (dirname(__DIR__) . '/webimages/lostfound/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'laf_' . $ticketId . '_' . time() . '.' . $ext;
        $fullPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return $this->error('Failed to save image.');
        }

        $relativePath = 'webimages/lostfound/' . $filename;
        $this->db->sql_query(
            "UPDATE lost_found_tickets SET vItemImagePath='" . addslashes($relativePath) . "'
             WHERE iTicketId=$ticketId"
        );

        return $this->success(['vItemImagePath' => $relativePath, 'message' => 'Image uploaded.']);
    }

    // ----------------------------------------------------------------
    // 9. Close a ticket
    // ----------------------------------------------------------------
    private function closeTicket(array $req): string
    {
        $ticketId = (int)($req['iTicketId'] ?? 0);
        $userId   = (int)($req['iUserId']   ?? 0);
        $adminId  = (int)($req['iAdminId']  ?? 0);

        if (!$ticketId) return $this->error('iTicketId required.');

        $where = $adminId ? "iTicketId=$ticketId" : "iTicketId=$ticketId AND iUserId=$userId";
        $this->db->sql_query(
            "UPDATE lost_found_tickets SET eStatus='Closed' WHERE $where"
        );

        $this->insertMessage($ticketId, 'System', 0, 'Ticket closed. Thank you for using Ridey Lost & Found.');

        return $this->success(['message' => 'Ticket closed.']);
    }

    // ----------------------------------------------------------------
    // Internal helpers
    // ----------------------------------------------------------------
    private function fetchTicket(int $id): ?array
    {
        $rows = $this->db->MySQLSelect(
            "SELECT * FROM lost_found_tickets WHERE iTicketId=$id LIMIT 1"
        );
        return $rows[0] ?? null;
    }

    private function insertMessage(int $ticketId, string $senderType, int $senderId, string $message): int
    {
        $message = addslashes($message);
        $now     = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "INSERT INTO lost_found_messages (iTicketId, eSenderType, iSenderId, vMessage, dCreatedAt)
             VALUES ($ticketId, '$senderType', $senderId, '$message', '$now')"
        );
        return $this->db->MySQLLastInsertID();
    }

    private function notifyOnNewMessage(array $ticket, string $senderType, int $senderId, string $message): void
    {
        $preview = mb_substr($message, 0, 60) . (mb_strlen($message) > 60 ? '...' : '');
        if ($senderType !== 'Passenger') {
            $this->pushUser((int)$ticket['iUserId'], 'Passenger', 'New message in your Lost & Found ticket', $preview, $ticket['iTicketId']);
        }
        if ($senderType !== 'Driver') {
            $this->pushUser((int)$ticket['iDriverId'], 'Driver', 'Lost & Found message', $preview, $ticket['iTicketId']);
        }
    }

    private function notifyDriver(int $driverId, int $ticketId, string $msg): void
    {
        $this->pushUser($driverId, 'Driver', 'Lost & Found', $msg, $ticketId);
    }

    private function notifyPassengerTicketUpdate(array $ticket, string $status): void
    {
        $messages = [
            'InProgress'       => 'Your lost item ticket is being handled.',
            'ItemFound'        => 'Great news! Your item has been found!',
            'ItemReturned'     => 'Your item has been returned.',
            'ReturnTripCreated'=> 'A return trip was created to deliver your item.',
            'Closed'           => 'Your ticket has been closed.',
        ];
        $msg = $messages[$status] ?? "Your ticket status changed to $status.";
        $this->pushUser((int)$ticket['iUserId'], 'Passenger', 'Lost & Found Update', $msg, (int)$ticket['iTicketId']);
    }

    private function notifyPassengerReturnTrip(int $userId, int $ticketId, float $fare, int $requestId): void
    {
        $this->pushUser(
            $userId, 'Passenger',
            'Return Trip Scheduled',
            "A driver will return your item. Trip #$requestId. Fare: R$ " . number_format($fare, 2, ',', '.'),
            $ticketId
        );
    }

    private function notifyAdmins(int $ticketId, string $msg): void
    {
        if (function_exists('sendAdminNotification')) {
            sendAdminNotification('Lost & Found', $msg, ['type' => 'LostFound', 'ticketId' => $ticketId]);
        }
    }

    private function pushUser(int $id, string $type, string $title, string $body, int $ticketId): void
    {
        if (function_exists('sendPushNotification')) {
            sendPushNotification($id, $type, $title, $body, ['type' => 'LostFound', 'ticketId' => $ticketId]);
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
