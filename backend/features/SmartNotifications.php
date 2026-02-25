<?php
/**
 * FEATURE 7: SMART PUSH NOTIFICATIONS
 * -------------------------------------
 * Scheduled alerts with customizable repetition settings.
 *
 * Supports:
 *   - Immediate send
 *   - Scheduled (future date/time)
 *   - Recurring (Hourly / Daily / Weekly / Monthly with max count)
 *   - Target: All users, all drivers, specific IDs, franchise city
 *
 * Cron integration:
 *   Add to cron_smart_notifications.php (created separately) to process the queue.
 *
 * Admin usage (webservice):
 *   type=smartNotification&notif_action=create|send|pause|cancel|getList
 */

class SmartNotifications
{
    private $db;
    private $config;

    // FCM API v1 endpoint (HTTP Legacy also supported via sendLegacyFCM)
    private string $fcmEndpoint = 'https://fcm.googleapis.com/fcm/send';

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['notif_action'] ?? '');

        switch ($action) {
            case 'create':       return $this->createNotification($req);
            case 'send':         return $this->sendImmediately($req);
            case 'pause':        return $this->pauseNotification($req);
            case 'cancel':       return $this->cancelNotification($req);
            case 'resume':       return $this->resumeNotification($req);
            case 'getList':      return $this->getList($req);
            case 'getDetail':    return $this->getDetail($req);
            case 'getStats':     return $this->getStats($req);
            case 'processQueue': return $this->processQueue();   // called by cron
            default:
                return $this->error('Unknown notif_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Create a notification (draft or queued)
    // ----------------------------------------------------------------
    private function createNotification(array $req): string
    {
        $title          = strip_tags(trim($req['vTitle']          ?? ''));
        $body           = strip_tags(trim($req['vBody']           ?? ''));
        $targetType     = trim($req['eTargetType']                ?? 'AllUsers');
        $targetIds      = trim($req['vTargetIds']                 ?? '');
        $franchiseId    = (int)($req['iFranchiseId']              ?? 0);
        $scheduleType   = trim($req['eScheduleType']              ?? 'Immediate');
        $scheduledAt    = trim($req['dScheduledAt']               ?? '');
        $repeatInterval = trim($req['eRepeatInterval']            ?? 'None');
        $repeatCount    = (int)($req['iRepeatCount']              ?? 1);
        $dataPayload    = trim($req['vDataPayload']               ?? '{}');
        $adminId        = (int)($req['iAdminId']                  ?? 0);

        if (!$title || !$body) return $this->error('vTitle and vBody required.');

        $allowedTargets  = ['AllUsers', 'AllDrivers', 'SpecificUser', 'SpecificDriver', 'Segment', 'Franchise'];
        $allowedSchedule = ['Immediate', 'Scheduled', 'Recurring'];
        $allowedRepeat   = ['None', 'Hourly', 'Daily', 'Weekly', 'Monthly'];

        if (!in_array($targetType, $allowedTargets))    $targetType     = 'AllUsers';
        if (!in_array($scheduleType, $allowedSchedule)) $scheduleType   = 'Immediate';
        if (!in_array($repeatInterval, $allowedRepeat)) $repeatInterval = 'None';

        $eStatus    = 'Queued';
        $nextRunAt  = 'NOW()';

        if ($scheduleType === 'Scheduled' && $scheduledAt) {
            $nextRunAt = "'" . addslashes($scheduledAt) . "'";
            $eStatus   = 'Queued';
        } elseif ($scheduleType === 'Recurring') {
            $nextRunAt = $scheduledAt ? "'" . addslashes($scheduledAt) . "'" : 'NOW()';
        }

        $title        = addslashes($title);
        $body         = addslashes($body);
        $targetIds    = addslashes($targetIds);
        $dataPayload  = addslashes($dataPayload);
        $now          = date('Y-m-d H:i:s');
        $franchiseVal = $franchiseId ?: 'NULL';

        $this->db->sql_query(
            "INSERT INTO smart_notifications
                (vTitle, vBody, vDataPayload, eTargetType, vTargetIds, iFranchiseId,
                 eScheduleType, dScheduledAt, eRepeatInterval, iRepeatCount,
                 iSentCount, eStatus, dNextRunAt, iCreatedByAdmin, dCreatedAt)
             VALUES
                ('$title', '$body', '$dataPayload', '$targetType', '$targetIds', $franchiseVal,
                 '$scheduleType', " . ($scheduledAt ? "'" . addslashes($scheduledAt) . "'" : 'NULL') . ", '$repeatInterval', $repeatCount,
                 0, '$eStatus', $nextRunAt, " . ($adminId ?: 'NULL') . ", '$now')"
        );
        $notifId = $this->db->MySQLLastInsertID();

        // If immediate, send right now
        if ($scheduleType === 'Immediate') {
            $this->dispatchNotification($notifId);
        }

        return $this->success(['iNotificationId' => $notifId, 'eStatus' => $eStatus, 'message' => 'Notification created.']);
    }

    // ----------------------------------------------------------------
    // 2. Force send an existing notification immediately
    // ----------------------------------------------------------------
    private function sendImmediately(array $req): string
    {
        $notifId = (int)($req['iNotificationId'] ?? 0);
        if (!$notifId) return $this->error('iNotificationId required.');

        $count = $this->dispatchNotification($notifId);
        return $this->success(['sent' => $count, 'message' => "Sent to $count recipients."]);
    }

    // ----------------------------------------------------------------
    // 3 / 4 / 5. Pause / Cancel / Resume
    // ----------------------------------------------------------------
    private function pauseNotification(array $req): string
    {
        $id = (int)($req['iNotificationId'] ?? 0);
        if (!$id) return $this->error('iNotificationId required.');
        $this->db->sql_query("UPDATE smart_notifications SET eStatus='Paused' WHERE iNotificationId=$id");
        return $this->success(['message' => 'Paused.']);
    }

    private function cancelNotification(array $req): string
    {
        $id = (int)($req['iNotificationId'] ?? 0);
        if (!$id) return $this->error('iNotificationId required.');
        $this->db->sql_query("UPDATE smart_notifications SET eStatus='Cancelled' WHERE iNotificationId=$id");
        return $this->success(['message' => 'Cancelled.']);
    }

    private function resumeNotification(array $req): string
    {
        $id = (int)($req['iNotificationId'] ?? 0);
        if (!$id) return $this->error('iNotificationId required.');
        $this->db->sql_query(
            "UPDATE smart_notifications SET eStatus='Queued', dNextRunAt=NOW()
             WHERE iNotificationId=$id AND eStatus='Paused'"
        );
        return $this->success(['message' => 'Resumed.']);
    }

    // ----------------------------------------------------------------
    // 6. List notifications
    // ----------------------------------------------------------------
    private function getList(array $req): string
    {
        $status  = trim($req['eStatus'] ?? '');
        $where   = $status ? "WHERE eStatus='" . addslashes($status) . "'" : '';

        $rows = $this->db->MySQLSelect(
            "SELECT iNotificationId, vTitle, eTargetType, eScheduleType,
                    eRepeatInterval, iRepeatCount, iSentCount, eStatus,
                    dScheduledAt, dNextRunAt, dLastSentAt, dCreatedAt
             FROM smart_notifications
             $where
             ORDER BY dCreatedAt DESC
             LIMIT 100"
        );
        return $this->success(['notifications' => $rows ?: []]);
    }

    // ----------------------------------------------------------------
    // 7. Get single detail
    // ----------------------------------------------------------------
    private function getDetail(array $req): string
    {
        $id = (int)($req['iNotificationId'] ?? 0);
        if (!$id) return $this->error('iNotificationId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT * FROM smart_notifications WHERE iNotificationId=$id LIMIT 1"
        );
        if (empty($rows)) return $this->error('Notification not found.');

        return $this->success(['notification' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // 8. Get delivery stats
    // ----------------------------------------------------------------
    private function getStats(array $req): string
    {
        $id = (int)($req['iNotificationId'] ?? 0);
        if (!$id) return $this->error('iNotificationId required.');

        $totals = $this->db->MySQLSelect(
            "SELECT eDeliveryStatus, COUNT(*) AS cnt
             FROM smart_notification_logs
             WHERE iNotificationId=$id
             GROUP BY eDeliveryStatus"
        );

        return $this->success(['stats' => $totals ?: []]);
    }

    // ----------------------------------------------------------------
    // CRON: Process queued notifications
    // ----------------------------------------------------------------
    public function processQueue(): string
    {
        $now  = date('Y-m-d H:i:s');
        $rows = $this->db->MySQLSelect(
            "SELECT * FROM smart_notifications
             WHERE eStatus='Queued' AND dNextRunAt <= '$now'
             ORDER BY dNextRunAt ASC
             LIMIT 20"
        );

        $processed = 0;
        foreach ($rows as $row) {
            $sent = $this->dispatchNotification((int)$row['iNotificationId']);
            $processed++;

            $newSentCount = (int)$row['iSentCount'] + 1;
            $maxCount     = (int)$row['iRepeatCount'];

            // Determine next status
            $nextStatus  = 'Sent';
            $nextRunAt   = 'NULL';

            if ($row['eScheduleType'] === 'Recurring' && $row['eRepeatInterval'] !== 'None') {
                $isUnlimited = ($maxCount === 0);
                if ($isUnlimited || $newSentCount < $maxCount) {
                    $nextStatus = 'Queued';
                    $nextRunAt  = "'" . $this->calculateNextRun($row['eRepeatInterval']) . "'";
                }
            }

            $lastSent = date('Y-m-d H:i:s');
            $this->db->sql_query(
                "UPDATE smart_notifications
                 SET eStatus='$nextStatus', iSentCount=$newSentCount,
                     dLastSentAt='$lastSent', dNextRunAt=$nextRunAt
                 WHERE iNotificationId={$row['iNotificationId']}"
            );
        }

        return $this->success(['processed' => $processed, 'message' => "$processed notifications processed."]);
    }

    // ----------------------------------------------------------------
    // Internal: dispatch one notification to all resolved recipients
    // ----------------------------------------------------------------
    private function dispatchNotification(int $notifId): int
    {
        $notif = $this->db->MySQLSelect(
            "SELECT * FROM smart_notifications WHERE iNotificationId=$notifId LIMIT 1"
        );
        if (empty($notif)) return 0;
        $notif = $notif[0];

        $recipients = $this->resolveRecipients($notif);
        $sent       = 0;

        foreach ($recipients as $r) {
            $success = $this->sendFCM($r['token'], $notif['vTitle'], $notif['vBody'],
                                       json_decode($notif['vDataPayload'] ?? '{}', true));
            $this->logDelivery($notifId, (int)$r['userId'], $r['type'],
                               $success ? 'Sent' : 'Failed', $r['token']);
            if ($success) $sent++;
        }

        return $sent;
    }

    private function resolveRecipients(array $notif): array
    {
        $recipients = [];
        $target     = $notif['eTargetType'];

        if ($target === 'AllUsers' || $target === 'Segment') {
            $sql = "SELECT iUserId AS userId, vDeviceToken AS token, 'Passenger' AS type
                    FROM register WHERE vDeviceToken != '' AND eStatus='Active'";
            $rows = $this->db->MySQLSelect($sql);
            if ($rows) $recipients = array_merge($recipients, $rows);
        }

        if ($target === 'AllDrivers' || $target === 'Segment') {
            $sql = "SELECT iDriverId AS userId, vDeviceToken AS token, 'Driver' AS type
                    FROM register_driver WHERE vDeviceToken != '' AND eStatus='Active'";
            $rows = $this->db->MySQLSelect($sql);
            if ($rows) $recipients = array_merge($recipients, $rows);
        }

        if ($target === 'SpecificUser' && $notif['vTargetIds']) {
            $ids = implode(',', array_map('intval', explode(',', $notif['vTargetIds'])));
            $rows = $this->db->MySQLSelect(
                "SELECT iUserId AS userId, vDeviceToken AS token, 'Passenger' AS type
                 FROM register WHERE iUserId IN ($ids) AND vDeviceToken != ''"
            );
            if ($rows) $recipients = array_merge($recipients, $rows);
        }

        if ($target === 'SpecificDriver' && $notif['vTargetIds']) {
            $ids = implode(',', array_map('intval', explode(',', $notif['vTargetIds'])));
            $rows = $this->db->MySQLSelect(
                "SELECT iDriverId AS userId, vDeviceToken AS token, 'Driver' AS type
                 FROM register_driver WHERE iDriverId IN ($ids) AND vDeviceToken != ''"
            );
            if ($rows) $recipients = array_merge($recipients, $rows);
        }

        if ($target === 'Franchise' && $notif['iFranchiseId']) {
            $fId = (int)$notif['iFranchiseId'];
            $rows = $this->db->MySQLSelect(
                "SELECT rd.iDriverId AS userId, rd.vDeviceToken AS token, 'Driver' AS type
                 FROM register_driver rd
                 JOIN franchise_driver_map fdm ON fdm.iDriverId=rd.iDriverId
                 WHERE fdm.iFranchiseId=$fId AND rd.vDeviceToken != '' AND rd.eStatus='Active'"
            );
            if ($rows) $recipients = array_merge($recipients, $rows);
        }

        return $recipients;
    }

    private function sendFCM(string $token, string $title, string $body, array $data): bool
    {
        $serverKey = defined('FCM_SERVER_KEY') ? FCM_SERVER_KEY : '';
        if (!$serverKey || !$token) return false;

        $payload = json_encode([
            'to'           => $token,
            'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
            'data'         => $data,
            'priority'     => 'high',
        ]);

        $ch = curl_init($this->fcmEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "Authorization: key=$serverKey",
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resp = json_decode($response, true);
        return $httpCode === 200 && isset($resp['success']) && $resp['success'] > 0;
    }

    private function logDelivery(int $notifId, int $userId, string $type, string $status, string $token): void
    {
        $token = addslashes(substr($token, 0, 300));
        $now   = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "INSERT INTO smart_notification_logs
                (iNotificationId, iUserId, eUserType, eDeliveryStatus, vFcmToken, dSentAt)
             VALUES ($notifId, $userId, '$type', '$status', '$token', '$now')"
        );
    }

    private function calculateNextRun(string $interval): string
    {
        $map = [
            'Hourly'  => '+1 hour',
            'Daily'   => '+1 day',
            'Weekly'  => '+1 week',
            'Monthly' => '+1 month',
        ];
        return date('Y-m-d H:i:s', strtotime($map[$interval] ?? '+1 day'));
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
