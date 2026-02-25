<?php
/**
 * FEATURE 4: VERIFIED NO-SHOW FEE
 * --------------------------------
 * GPS tracking + admin approval to validate passenger absence.
 *
 * Flow:
 *   1. Driver marks "Arrived at pickup" → GPS breadcrumbs stored.
 *   2. After wait timer expires → driver can report no-show.
 *   3. System creates a PendingReview incident with GPS proof.
 *   4. Admin reviews GPS evidence and approves/rejects.
 *   5. On approval → no-show fee charged to passenger, credited to driver.
 */

class NoShowVerification
{
    private $db;
    private $config;
    private int $defaultWaitMinutes    = 5;   // how long driver must wait
    private float $maxPickupRadiusMeters = 200; // driver must be within 200m

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['noshow_action'] ?? '');

        switch ($action) {
            case 'driverArrived':   return $this->driverArrived($req);
            case 'reportNoShow':    return $this->reportNoShow($req);
            case 'addGpsProof':     return $this->addGpsProof($req);
            case 'adminApprove':    return $this->adminApprove($req);
            case 'adminReject':     return $this->adminReject($req);
            case 'getIncident':     return $this->getIncident($req);
            case 'getPendingList':  return $this->getPendingList($req);
            default:
                return $this->error('Unknown noshow_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Driver marks arrival at pickup point
    // ----------------------------------------------------------------
    private function driverArrived(array $req): string
    {
        $requestId       = (int)($req['iRequestId']       ?? 0);
        $driverId        = (int)($req['iDriverId']         ?? 0);
        $userId          = (int)($req['iUserId']           ?? 0);
        $driverLatLng    = trim($req['vDriverLatLng']      ?? '');
        $pickupLatLng    = trim($req['vPickupLatLng']      ?? '');
        $waitMinutes     = (int)($req['iWaitMinutes']      ?? $this->defaultWaitMinutes);

        if (!$requestId || !$driverId || !$driverLatLng || !$pickupLatLng) {
            return $this->error('iRequestId, iDriverId, vDriverLatLng, vPickupLatLng required.');
        }

        // Calculate distance from pickup
        $dist = $this->distanceMeters($driverLatLng, $pickupLatLng);

        // Warn if driver is too far
        if ($dist > $this->maxPickupRadiusMeters) {
            return $this->error(
                "Driver is {$dist}m from pickup. Must be within {$this->maxPickupRadiusMeters}m to mark arrived."
            );
        }

        $now        = date('Y-m-d H:i:s');
        $waitExpiry = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes"));
        $driverLatLng = addslashes($driverLatLng);
        $pickupLatLng = addslashes($pickupLatLng);

        // Check if incident already exists
        $existing = $this->db->MySQLSelect(
            "SELECT iNoShowId FROM no_show_incidents WHERE iRequestId=$requestId LIMIT 1"
        );
        if (!empty($existing)) {
            // Update arrived time if re-marking
            $id = $existing[0]['iNoShowId'];
            $this->db->sql_query(
                "UPDATE no_show_incidents
                 SET dDriverArrivedAt='$now', dWaitExpiredAt='$waitExpiry',
                     vDriverAtPickupLatLng='$driverLatLng', fDistanceFromPickup=$dist
                 WHERE iNoShowId=$id"
            );
            return $this->success(['iNoShowId' => $id, 'waitExpiry' => $waitExpiry, 'distanceMeters' => $dist]);
        }

        $this->db->sql_query(
            "INSERT INTO no_show_incidents
                (iRequestId, iDriverId, iUserId, vDriverAtPickupLatLng, vPickupLatLng,
                 fDistanceFromPickup, iWaitMinutes, dDriverArrivedAt, dWaitExpiredAt, eStatus, dCreatedAt)
             VALUES
                ($requestId, $driverId, $userId, '$driverLatLng', '$pickupLatLng',
                 $dist, $waitMinutes, '$now', '$waitExpiry', 'PendingReview', '$now')"
        );
        $noShowId = $this->db->MySQLLastInsertID();

        return $this->success([
            'iNoShowId'     => $noShowId,
            'waitExpiry'    => $waitExpiry,
            'distanceMeters'=> round($dist),
            'message'       => "Arrival recorded. You can report no-show after {$waitMinutes} minutes.",
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Driver formally reports a no-show after wait expires
    // ----------------------------------------------------------------
    private function reportNoShow(array $req): string
    {
        $noShowId    = (int)($req['iNoShowId']    ?? 0);
        $driverId    = (int)($req['iDriverId']    ?? 0);
        $noShowFee   = (float)($req['fNoShowFee'] ?? 0);

        if (!$noShowId || !$driverId) {
            return $this->error('iNoShowId and iDriverId required.');
        }

        $incident = $this->fetchIncident($noShowId);
        if (!$incident) return $this->error('Incident not found.');
        if ($incident['iDriverId'] != $driverId) return $this->error('Not authorised.');

        // Check wait time has actually elapsed
        if (!empty($incident['dWaitExpiredAt']) && strtotime($incident['dWaitExpiredAt']) > time()) {
            $remaining = strtotime($incident['dWaitExpiredAt']) - time();
            return $this->error("Must wait $remaining more seconds before reporting no-show.");
        }

        $this->db->sql_query(
            "UPDATE no_show_incidents
             SET eStatus='PendingReview', fNoShowFee=$noShowFee
             WHERE iNoShowId=$noShowId"
        );

        // Notify admin panel for review
        $this->notifyAdminsForReview($noShowId, $incident['iRequestId']);

        return $this->success([
            'iNoShowId' => $noShowId,
            'message'   => 'No-show reported. Pending admin review.',
        ]);
    }

    // ----------------------------------------------------------------
    // 3. Append GPS breadcrumb proof (called every 30s while driver waits)
    // ----------------------------------------------------------------
    private function addGpsProof(array $req): string
    {
        $noShowId  = (int)($req['iNoShowId'] ?? 0);
        $latLng    = trim($req['vLatLng']    ?? '');
        $timestamp = trim($req['vTimestamp'] ?? date('Y-m-d H:i:s'));

        if (!$noShowId || !$latLng) return $this->error('iNoShowId and vLatLng required.');

        $incident = $this->fetchIncident($noShowId);
        if (!$incident) return $this->error('Incident not found.');

        // Append to existing JSON proof
        $existing = json_decode($incident['vGpsProofJson'] ?? '[]', true) ?: [];
        $existing[] = [
            'latlng'    => $latLng,
            'timestamp' => $timestamp,
        ];

        $json = addslashes(json_encode($existing));
        $this->db->sql_query(
            "UPDATE no_show_incidents SET vGpsProofJson='$json' WHERE iNoShowId=$noShowId"
        );

        return $this->success(['message' => 'GPS proof added.', 'breadcrumbs' => count($existing)]);
    }

    // ----------------------------------------------------------------
    // 4a. Admin approves the no-show → charge passenger
    // ----------------------------------------------------------------
    private function adminApprove(array $req): string
    {
        $noShowId  = (int)($req['iNoShowId']   ?? 0);
        $adminId   = (int)($req['iAdminId']    ?? 0);
        $adminNote = addslashes(strip_tags($req['vAdminNote'] ?? ''));

        if (!$noShowId || !$adminId) return $this->error('iNoShowId and iAdminId required.');

        $incident = $this->fetchIncident($noShowId);
        if (!$incident) return $this->error('Incident not found.');

        $now = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "UPDATE no_show_incidents
             SET eStatus='AdminApproved', iAdminId=$adminId,
                 vAdminNote='$adminNote', dAdminActionAt='$now'
             WHERE iNoShowId=$noShowId"
        );

        $fee = (float)$incident['fNoShowFee'];

        // Charge passenger
        if ($fee > 0) {
            $this->chargePassenger((int)$incident['iUserId'], $fee, (int)$incident['iRequestId']);
            $this->db->sql_query(
                "UPDATE no_show_incidents SET eStatus='FeeCharged' WHERE iNoShowId=$noShowId"
            );
            // Credit penalty to driver
            $this->creditDriverPenalty((int)$incident['iDriverId'], $fee, (int)$incident['iRequestId']);
        }

        // Notify both parties
        $this->notifyPassenger((int)$incident['iUserId'], $fee, 'NoShowApproved');
        $this->notifyDriver((int)$incident['iDriverId'], $fee, 'NoShowApproved');

        return $this->success(['message' => 'No-show approved. Fee charged.', 'fNoShowFee' => $fee]);
    }

    // ----------------------------------------------------------------
    // 4b. Admin rejects the no-show report
    // ----------------------------------------------------------------
    private function adminReject(array $req): string
    {
        $noShowId  = (int)($req['iNoShowId']   ?? 0);
        $adminId   = (int)($req['iAdminId']    ?? 0);
        $adminNote = addslashes(strip_tags($req['vAdminNote'] ?? ''));

        if (!$noShowId || !$adminId) return $this->error('iNoShowId and iAdminId required.');

        $now = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "UPDATE no_show_incidents
             SET eStatus='AdminRejected', iAdminId=$adminId,
                 vAdminNote='$adminNote', dAdminActionAt='$now'
             WHERE iNoShowId=$noShowId"
        );

        $incident = $this->fetchIncident($noShowId);
        $this->notifyDriver((int)$incident['iDriverId'], 0, 'NoShowRejected');

        return $this->success(['message' => 'No-show report rejected.']);
    }

    // ----------------------------------------------------------------
    // Get a single incident
    // ----------------------------------------------------------------
    private function getIncident(array $req): string
    {
        $noShowId  = (int)($req['iNoShowId']  ?? 0);
        $requestId = (int)($req['iRequestId'] ?? 0);

        $where = $noShowId ? "iNoShowId=$noShowId" : "iRequestId=$requestId";
        if (!$noShowId && !$requestId) return $this->error('iNoShowId or iRequestId required.');

        $rows = $this->db->MySQLSelect("SELECT * FROM no_show_incidents WHERE $where LIMIT 1");
        if (empty($rows)) return $this->error('Incident not found.');

        return $this->success(['incident' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // Admin: list all incidents pending review
    // ----------------------------------------------------------------
    private function getPendingList(array $req): string
    {
        $rows = $this->db->MySQLSelect(
            "SELECT nsi.*,
                    CONCAT(rd.vName,' ',rd.vLastName) AS vDriverName,
                    r.vName AS vPassengerName
             FROM no_show_incidents nsi
             LEFT JOIN register_driver rd ON rd.iDriverId = nsi.iDriverId
             LEFT JOIN register r ON r.iUserId = nsi.iUserId
             WHERE nsi.eStatus = 'PendingReview'
             ORDER BY nsi.dCreatedAt DESC"
        );

        return $this->success(['incidents' => $rows ?: []]);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function fetchIncident(int $id): ?array
    {
        $rows = $this->db->MySQLSelect(
            "SELECT * FROM no_show_incidents WHERE iNoShowId=$id LIMIT 1"
        );
        return $rows[0] ?? null;
    }

    private function distanceMeters(string $ll1, string $ll2): float
    {
        [$lat1, $lon1] = $this->parseLatLng($ll1);
        [$lat2, $lon2] = $this->parseLatLng($ll2);

        $R    = 6371000; // Earth radius in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function parseLatLng(string $s): array
    {
        $p = explode(',', $s);
        return [(float)($p[0] ?? 0), (float)($p[1] ?? 0)];
    }

    private function chargePassenger(int $userId, float $amount, int $requestId): void
    {
        if (function_exists('chargeUserWallet')) {
            chargeUserWallet($userId, $amount, "No-show fee for request #$requestId");
        }
    }

    private function creditDriverPenalty(int $driverId, float $amount, int $requestId): void
    {
        $penaltyFile = __DIR__ . '/PenaltyTransfer.php';
        if (file_exists($penaltyFile)) {
            require_once $penaltyFile;
            $pt = new PenaltyTransfer($this->db, $this->config);
            $pt->transferDirect($driverId, $amount, $requestId, 'NoShow');
        }
    }

    private function notifyAdminsForReview(int $noShowId, int $requestId): void
    {
        if (function_exists('sendAdminNotification')) {
            sendAdminNotification(
                'No-Show Review Required',
                "Incident #$noShowId for request #$requestId requires GPS review.",
                ['type' => 'NoShowReview', 'noShowId' => $noShowId]
            );
        }
    }

    private function notifyPassenger(int $userId, float $fee, string $type): void
    {
        if (function_exists('sendPushNotification')) {
            sendPushNotification($userId, 'Passenger',
                'No-Show Decision',
                $type === 'NoShowApproved'
                    ? "A no-show fee of R$ " . number_format($fee, 2, ',', '.') . " has been applied."
                    : "Your no-show dispute was resolved.",
                ['type' => $type]
            );
        }
    }

    private function notifyDriver(int $driverId, float $fee, string $type): void
    {
        if (function_exists('sendPushNotification')) {
            sendPushNotification($driverId, 'Driver',
                'No-Show Decision',
                $type === 'NoShowApproved'
                    ? "No-show confirmed. R$ " . number_format($fee, 2, ',', '.') . " credited to your wallet."
                    : "Your no-show report was not approved.",
                ['type' => $type]
            );
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
