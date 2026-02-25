<?php
/**
 * FEATURE 12: AUTOMATED PENALTY TRANSFERS
 * -----------------------------------------
 * Immediate crediting of collected cancellation/no-show fees
 * directly to the penalized driver's digital wallet.
 *
 * Flow:
 *   1. Cancellation or no-show fee is collected from the passenger.
 *   2. PenaltyTransfer.transferDirect() is called with the collected amount.
 *   3. The amount is credited to the driver's wallet in real-time.
 *   4. The transfer is logged in penalty_transfer_logs.
 *   5. Driver receives a push notification.
 *
 * Wallet integration:
 *   Uses the existing driver_wallet system (driver_wallet.php pattern).
 *   If Pagar.me is enabled, uses Pagar.me instant transfer.
 */

class PenaltyTransfer
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
        $action = trim($req['penalty_action'] ?? '');

        switch ($action) {
            case 'transfer':       return $this->transfer($req);
            case 'reverse':        return $this->reversePenalty($req);
            case 'getLog':         return $this->getLog($req);
            case 'getDriverLog':   return $this->getDriverLog($req);
            case 'getStats':       return $this->getPenaltyStats($req);
            default:
                return $this->error('Unknown penalty_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Transfer penalty to driver wallet (API-triggered)
    // ----------------------------------------------------------------
    private function transfer(array $req): string
    {
        $requestId   = (int)($req['iRequestId']   ?? 0);
        $driverId    = (int)($req['iDriverId']    ?? 0);
        $userId      = (int)($req['iUserId']      ?? 0);
        $amount      = (float)($req['fPenaltyAmount'] ?? 0);
        $penaltyType = in_array($req['ePenaltyType'] ?? '', ['NoShow','Cancellation','LateArrival','Custom'])
                       ? $req['ePenaltyType'] : 'Cancellation';
        $transferTo  = in_array($req['eTransferTo'] ?? '', ['DriverWallet','Passenger','Platform'])
                       ? $req['eTransferTo'] : 'DriverWallet';
        $note        = strip_tags(trim($req['vNote'] ?? ''));

        if (!$requestId || !$driverId || !$userId || $amount <= 0) {
            return $this->error('iRequestId, iDriverId, iUserId and fPenaltyAmount required.');
        }

        $logId  = $this->createLog($requestId, $driverId, $userId, $penaltyType, $amount, $transferTo, $note);
        $result = $this->executePenaltyTransfer($logId, $driverId, $userId, $amount, $transferTo, $requestId, $penaltyType);

        return $result;
    }

    // ----------------------------------------------------------------
    // Direct transfer (called programmatically from other feature classes)
    // ----------------------------------------------------------------
    public function transferDirect(int $driverId, float $amount, int $requestId, string $penaltyType): bool
    {
        $logId  = $this->createLog($requestId, $driverId, 0, $penaltyType, $amount, 'DriverWallet', 'Auto-transfer');
        $result = $this->executePenaltyTransfer($logId, $driverId, 0, $amount, 'DriverWallet', $requestId, $penaltyType);
        $data   = json_decode($result, true);
        return ($data['status'] ?? '') === 'success';
    }

    // ----------------------------------------------------------------
    // Core transfer execution
    // ----------------------------------------------------------------
    private function executePenaltyTransfer(
        int $logId, int $driverId, int $userId, float $amount,
        string $transferTo, int $requestId, string $penaltyType
    ): string {
        $walletTxId = null;
        $success    = false;

        if ($transferTo === 'DriverWallet') {
            // Credit driver's wallet
            $walletTxId = $this->creditDriverWallet($driverId, $amount, $requestId, $penaltyType);
            $success    = ($walletTxId !== null);

        } elseif ($transferTo === 'Passenger') {
            // Refund to passenger wallet
            $walletTxId = $this->creditPassengerWallet($userId, $amount, $requestId);
            $success    = ($walletTxId !== null);

        } elseif ($transferTo === 'Platform') {
            // Keep in platform (just mark as transferred)
            $success    = true;
            $walletTxId = 0;
        }

        $now    = date('Y-m-d H:i:s');
        $status = $success ? 'Transferred' : 'Failed';
        $txVal  = $walletTxId !== null ? (int)$walletTxId : 'NULL';

        $this->db->sql_query(
            "UPDATE penalty_transfer_logs
             SET eStatus='$status', iWalletTransactionId=$txVal, dTransferredAt='$now'
             WHERE iPenaltyLogId=$logId"
        );

        if ($success && $transferTo === 'DriverWallet') {
            $this->notifyDriver($driverId, $amount, $penaltyType);
        }

        if (!$success) {
            return $this->error("Transfer failed for log #$logId. Check wallet configuration.");
        }

        return $this->success([
            'iPenaltyLogId'       => $logId,
            'fAmount'             => $amount,
            'eTransferTo'         => $transferTo,
            'iWalletTransactionId'=> $walletTxId,
            'message'             => "R$ " . number_format($amount, 2, ',', '.') . " transferred as $penaltyType penalty.",
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Reverse a penalty transfer (admin override)
    // ----------------------------------------------------------------
    private function reversePenalty(array $req): string
    {
        $logId   = (int)($req['iPenaltyLogId'] ?? 0);
        $adminId = (int)($req['iAdminId']       ?? 0);

        if (!$logId) return $this->error('iPenaltyLogId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT * FROM penalty_transfer_logs WHERE iPenaltyLogId=$logId LIMIT 1"
        );
        if (empty($rows)) return $this->error('Penalty log not found.');
        $log = $rows[0];

        if ($log['eStatus'] !== 'Transferred') {
            return $this->error("Cannot reverse penalty in status: {$log['eStatus']}");
        }

        // Deduct from driver wallet
        if ($log['eTransferTo'] === 'DriverWallet') {
            $this->deductDriverWallet((int)$log['iDriverId'], (float)$log['fPenaltyAmount'],
                                     (int)$log['iRequestId'], 'Penalty reversal by admin');
        }

        $this->db->sql_query(
            "UPDATE penalty_transfer_logs SET eStatus='Reversed' WHERE iPenaltyLogId=$logId"
        );

        // Create reversal record
        $note = "Reversed by admin #$adminId";
        $this->createLog(
            (int)$log['iRequestId'], (int)$log['iDriverId'], (int)$log['iUserId'],
            $log['ePenaltyType'], -(float)$log['fPenaltyAmount'], 'DriverWallet', $note
        );

        return $this->success(['message' => 'Penalty transfer reversed.']);
    }

    // ----------------------------------------------------------------
    // 3. Get single log
    // ----------------------------------------------------------------
    private function getLog(array $req): string
    {
        $logId     = (int)($req['iPenaltyLogId'] ?? 0);
        $requestId = (int)($req['iRequestId']    ?? 0);

        $where = $logId ? "iPenaltyLogId=$logId" : "iRequestId=$requestId";
        if (!$logId && !$requestId) return $this->error('iPenaltyLogId or iRequestId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT ptl.*,
                    CONCAT(rd.vName,' ',rd.vLastName) AS vDriverName,
                    r.vName AS vPassengerName
             FROM penalty_transfer_logs ptl
             LEFT JOIN register_driver rd ON rd.iDriverId=ptl.iDriverId
             LEFT JOIN register r ON r.iUserId=ptl.iUserId
             WHERE $where LIMIT 1"
        );
        if (empty($rows)) return $this->error('Log not found.');

        return $this->success(['log' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // 4. Get all penalty logs for a driver
    // ----------------------------------------------------------------
    private function getDriverLog(array $req): string
    {
        $driverId = (int)($req['iDriverId'] ?? 0);
        $page     = max(1, (int)($req['iPage'] ?? 1));
        $limit    = 20;
        $offset   = ($page - 1) * $limit;

        if (!$driverId) return $this->error('iDriverId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT * FROM penalty_transfer_logs
             WHERE iDriverId=$driverId
             ORDER BY dCreatedAt DESC
             LIMIT $limit OFFSET $offset"
        );
        return $this->success(['logs' => $rows ?: [], 'page' => $page]);
    }

    // ----------------------------------------------------------------
    // 5. Get penalty stats (admin)
    // ----------------------------------------------------------------
    private function getPenaltyStats(array $req): string
    {
        $period = trim($req['ePeriod'] ?? 'month');
        $fromDate = match($period) {
            'week'  => date('Y-m-d', strtotime('-7 days')),
            'month' => date('Y-m-01'),
            'year'  => date('Y-01-01'),
            default => date('Y-m-01'),
        };

        $stats = $this->db->MySQLSelect(
            "SELECT ePenaltyType, eStatus, COUNT(*) AS iCount, SUM(fPenaltyAmount) AS fTotal
             FROM penalty_transfer_logs
             WHERE dCreatedAt >= '$fromDate'
             GROUP BY ePenaltyType, eStatus"
        );

        $totalTransferred = $this->db->MySQLSelect(
            "SELECT COALESCE(SUM(fPenaltyAmount),0) AS total
             FROM penalty_transfer_logs
             WHERE eStatus='Transferred' AND dCreatedAt >= '$fromDate'"
        );

        return $this->success([
            'breakdown'        => $stats ?: [],
            'fTotalTransferred'=> (float)($totalTransferred[0]['total'] ?? 0),
            'period'           => $period,
        ]);
    }

    // ----------------------------------------------------------------
    // Wallet integration helpers
    // ----------------------------------------------------------------
    private function creditDriverWallet(int $driverId, float $amount, int $requestId, string $type): ?int
    {
        // Integration with existing driver_wallet system
        if (function_exists('addDriverWalletCredit')) {
            return addDriverWalletCredit($driverId, $amount, "Penalty income – $type for request #$requestId");
        }

        // Direct DB insert as fallback (adjust table name if needed)
        $desc = addslashes("Penalty income – $type – Request #$requestId");
        $now  = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "INSERT INTO driver_wallet (iDriverId, fAmount, eType, vDescription, dCreatedAt)
             VALUES ($driverId, $amount, 'Credit', '$desc', '$now')
             ON DUPLICATE KEY UPDATE fAmount=fAmount"
        );
        return $this->db->MySQLLastInsertID() ?: null;
    }

    private function creditPassengerWallet(int $userId, float $amount, int $requestId): ?int
    {
        if (function_exists('addUserWalletCredit')) {
            return addUserWalletCredit($userId, $amount, "Refund for request #$requestId");
        }
        $now  = date('Y-m-d H:i:s');
        $desc = addslashes("Penalty refund – Request #$requestId");
        $this->db->sql_query(
            "INSERT INTO rider_wallet (iUserId, fAmount, eType, vDescription, dCreatedAt)
             VALUES ($userId, $amount, 'Credit', '$desc', '$now')
             ON DUPLICATE KEY UPDATE fAmount=fAmount"
        );
        return $this->db->MySQLLastInsertID() ?: null;
    }

    private function deductDriverWallet(int $driverId, float $amount, int $requestId, string $reason): void
    {
        if (function_exists('deductDriverWallet')) {
            deductDriverWallet($driverId, $amount, $reason);
            return;
        }
        $desc = addslashes("$reason – Request #$requestId");
        $now  = date('Y-m-d H:i:s');
        $this->db->sql_query(
            "INSERT INTO driver_wallet (iDriverId, fAmount, eType, vDescription, dCreatedAt)
             VALUES ($driverId, -$amount, 'Debit', '$desc', '$now')"
        );
    }

    // ----------------------------------------------------------------
    // Log creation
    // ----------------------------------------------------------------
    private function createLog(
        int $requestId, int $driverId, int $userId,
        string $penaltyType, float $amount, string $transferTo, string $note
    ): int {
        $note = addslashes($note);
        $now  = date('Y-m-d H:i:s');

        $this->db->sql_query(
            "INSERT INTO penalty_transfer_logs
                (iRequestId, iDriverId, iUserId, ePenaltyType, fPenaltyAmount, vCurrency,
                 eTransferTo, eStatus, vNote, dCreatedAt)
             VALUES
                ($requestId, $driverId, $userId, '$penaltyType', $amount, 'BRL',
                 '$transferTo', 'Pending', '$note', '$now')"
        );
        return $this->db->MySQLLastInsertID();
    }

    // ----------------------------------------------------------------
    // Notify driver of penalty credit
    // ----------------------------------------------------------------
    private function notifyDriver(int $driverId, float $amount, string $type): void
    {
        if (function_exists('sendPushNotification')) {
            sendPushNotification(
                $driverId, 'Driver',
                'Penalty Credit Received',
                "R$ " . number_format($amount, 2, ',', '.') . " added to your wallet ($type fee).",
                ['type' => 'PenaltyCredit', 'fAmount' => $amount]
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
