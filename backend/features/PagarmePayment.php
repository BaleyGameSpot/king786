<?php
/**
 * FEATURE 10: PAGAR.ME SPLIT PAYMENT
 * -------------------------------------
 * Automated real-time payout division between:
 *   - Platform (Master)
 *   - Franchisee
 *   - Driver
 *
 * Uses Pagar.me API v5 (https://api.pagar.me/core/v5)
 *
 * Setup:
 *   Define in app_configuration_file.php:
 *     PAGARME_SECRET_KEY   = 'sk_...'
 *     PAGARME_PUBLIC_KEY   = 'pk_...'
 *     PAGARME_MASTER_RECIPIENT_ID = 'rp_...'
 *
 * Each franchise has its own recipient ID stored in franchises.vPagarmeRecipientId.
 * Each driver should have a recipient ID (stored in register_driver.vPagarmeRecipientId).
 */

class PagarmePayment
{
    private $db;
    private $config;
    private string $baseUrl    = 'https://api.pagar.me/core/v5';
    private string $secretKey;
    private string $masterRecipientId;

    public function __construct($db, array $config)
    {
        $this->db                 = $db;
        $this->config             = $config;
        $this->secretKey          = defined('PAGARME_SECRET_KEY')          ? PAGARME_SECRET_KEY          : '';
        $this->masterRecipientId  = defined('PAGARME_MASTER_RECIPIENT_ID') ? PAGARME_MASTER_RECIPIENT_ID : '';
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['pagarme_action'] ?? '');

        switch ($action) {
            case 'processRidePayment':   return $this->processRidePayment($req);
            case 'createRecipient':      return $this->createRecipient($req);
            case 'getSplitDetail':       return $this->getSplitDetail($req);
            case 'refundPayment':        return $this->refundPayment($req);
            case 'getRecipientBalance':  return $this->getRecipientBalance($req);
            default:
                return $this->error('Unknown pagarme_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Process split payment for a completed ride
    // ----------------------------------------------------------------
    public function processRidePayment(array $req): string
    {
        $requestId    = (int)($req['iRequestId']     ?? 0);
        $tripId       = (int)($req['iTripId']        ?? 0);
        $driverId     = (int)($req['iDriverId']      ?? 0);
        $userId       = (int)($req['iUserId']        ?? 0);
        $totalAmount  = (float)($req['fTotalAmount'] ?? 0);
        $paymentToken = trim($req['vPaymentToken']   ?? ''); // Pagar.me card token
        $franchiseId  = (int)($req['iFranchiseId']   ?? 0);

        if (!$requestId || !$driverId || !$userId || $totalAmount <= 0) {
            return $this->error('iRequestId, iDriverId, iUserId and fTotalAmount required.');
        }

        // Fetch share percentages from franchise
        $shares = $this->resolveShares($franchiseId);

        $masterAmount     = round($totalAmount * ($shares['master']     / 100), 2);
        $franchiseeAmount = round($totalAmount * ($shares['franchisee'] / 100), 2);
        $driverAmount     = round($totalAmount - $masterAmount - $franchiseeAmount, 2); // remainder to driver

        // Resolve recipient IDs
        $franchiseeRecipientId = $this->getFranchiseeRecipientId($franchiseId);
        $driverRecipientId     = $this->getDriverRecipientId($driverId);

        // Build split rules
        $splitRules = [];
        if ($this->masterRecipientId) {
            $splitRules[] = [
                'amount'       => $this->toCents($masterAmount),
                'recipient_id' => $this->masterRecipientId,
                'type'         => 'flat',
                'options'      => ['charge_processing_fee' => true, 'charge_remainder_fee' => false, 'liable' => true],
            ];
        }
        if ($franchiseeRecipientId && $franchiseeAmount > 0) {
            $splitRules[] = [
                'amount'       => $this->toCents($franchiseeAmount),
                'recipient_id' => $franchiseeRecipientId,
                'type'         => 'flat',
                'options'      => ['charge_processing_fee' => false, 'charge_remainder_fee' => false, 'liable' => false],
            ];
        }
        if ($driverRecipientId && $driverAmount > 0) {
            $splitRules[] = [
                'amount'       => $this->toCents($driverAmount),
                'recipient_id' => $driverRecipientId,
                'type'         => 'flat',
                'options'      => ['charge_processing_fee' => false, 'charge_remainder_fee' => true, 'liable' => false],
            ];
        }

        // Build Pagar.me order payload
        $orderPayload = [
            'code'     => "RIDE-$requestId-" . time(),
            'amount'   => $this->toCents($totalAmount),
            'currency' => 'BRL',
            'items'    => [[
                'amount'   => $this->toCents($totalAmount),
                'quantity' => 1,
                'code'     => "RIDE-$requestId",
                'description' => "Ridey Trip #$requestId",
            ]],
            'payments' => [[
                'payment_method' => $paymentToken ? 'credit_card' : 'pix',
                'credit_card'    => $paymentToken ? [
                    'card_token'         => $paymentToken,
                    'installments'       => 1,
                    'statement_descriptor' => 'RIDEY',
                    'capture'            => true,
                ] : null,
                'split' => $splitRules,
            ]],
        ];

        // Remove null payment fields
        if (!$paymentToken) {
            unset($orderPayload['payments'][0]['credit_card']);
            $orderPayload['payments'][0]['pix'] = [
                'expires_in'       => 3600,
                'additional_information' => [['name' => 'Tipo', 'value' => 'Corrida']],
            ];
        }

        $response = $this->apiRequest('POST', '/orders', $orderPayload);

        $orderId       = $response['id']            ?? null;
        $chargeId      = $response['charges'][0]['id'] ?? null;
        $transactionId = $response['charges'][0]['last_transaction']['id'] ?? null;
        $apiStatus     = $response['status']        ?? 'failed';
        $rawJson       = addslashes(json_encode($response));

        $eStatus = in_array($apiStatus, ['paid', 'pending']) ? 'Completed' : 'Failed';

        $now        = date('Y-m-d H:i:s');
        $frnVal     = $franchiseId ?: 'NULL';
        $ordIdEsc   = addslashes($orderId ?? '');
        $chgIdEsc   = addslashes($chargeId ?? '');
        $txIdEsc    = addslashes($transactionId ?? '');
        $errMsg     = ($eStatus === 'Failed') ? addslashes(json_encode($response['errors'] ?? [])) : '';

        $this->db->sql_query(
            "INSERT INTO pagarme_split_logs
                (iRequestId, iTripId, iFranchiseId, iDriverId, iUserId,
                 fTotalAmount, fMasterAmount, fFranchiseeAmount, fDriverAmount,
                 vPagarmeOrderId, vPagarmeChargeId, vPagarmeTransactionId,
                 eStatus, vRawResponseJson, vErrorMessage, dCreatedAt, dProcessedAt)
             VALUES
                ($requestId, $tripId, $frnVal, $driverId, $userId,
                 $totalAmount, $masterAmount, $franchiseeAmount, $driverAmount,
                 '$ordIdEsc', '$chgIdEsc', '$txIdEsc',
                 '$eStatus', '$rawJson', '$errMsg', '$now', '$now')"
        );
        $splitLogId = $this->db->MySQLLastInsertID();

        if ($eStatus === 'Completed') {
            // Credit driver's wallet record for reference
            $this->creditDriverWallet($driverId, $driverAmount, $requestId);
        }

        return $this->success([
            'iSplitLogId'      => $splitLogId,
            'vPagarmeOrderId'  => $orderId,
            'eStatus'          => $eStatus,
            'fMasterAmount'    => $masterAmount,
            'fFranchiseeAmount'=> $franchiseeAmount,
            'fDriverAmount'    => $driverAmount,
            'apiStatus'        => $apiStatus,
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Create a Pagar.me recipient (for driver or franchisee)
    // ----------------------------------------------------------------
    public function createRecipient(array $req): string
    {
        $name           = strip_tags(trim($req['vName']     ?? ''));
        $document       = preg_replace('/\D/', '', $req['vDocument'] ?? ''); // CPF or CNPJ
        $email          = trim($req['vEmail']               ?? '');
        $bankCode       = trim($req['vBankCode']            ?? ''); // e.g. '341' = Itaú
        $agencyNumber   = trim($req['vAgencyNumber']        ?? '');
        $accountNumber  = trim($req['vAccountNumber']       ?? '');
        $accountType    = trim($req['vAccountType']         ?? 'checking'); // checking | savings
        $driverId       = (int)($req['iDriverId']           ?? 0);
        $franchiseId    = (int)($req['iFranchiseId']        ?? 0);

        if (!$name || !$document || !$bankCode || !$agencyNumber || !$accountNumber) {
            return $this->error('vName, vDocument, vBankCode, vAgencyNumber, vAccountNumber required.');
        }

        $payload = [
            'name'               => $name,
            'email'              => $email,
            'description'        => $driverId ? "Driver #$driverId" : "Franchise #$franchiseId",
            'type'               => strlen($document) === 11 ? 'individual' : 'company',
            'document'           => $document,
            'default_bank_account' => [
                'holder_name'    => $name,
                'holder_type'    => strlen($document) === 11 ? 'individual' : 'company',
                'holder_document'=> $document,
                'bank'           => $bankCode,
                'branch_number'  => $agencyNumber,
                'account_number' => $accountNumber,
                'type'           => $accountType,
            ],
            'transfer_settings'  => [
                'transfer_enabled'  => true,
                'transfer_interval' => 'daily',
                'transfer_day'      => 0,
            ],
        ];

        $response    = $this->apiRequest('POST', '/recipients', $payload);
        $recipientId = $response['id'] ?? null;

        if (!$recipientId) {
            return $this->error('Failed to create recipient: ' . json_encode($response['errors'] ?? $response));
        }

        // Save recipient ID
        if ($driverId) {
            $this->db->sql_query(
                "UPDATE register_driver SET vPagarmeRecipientId='" . addslashes($recipientId) . "'
                 WHERE iDriverId=$driverId"
            );
        }
        if ($franchiseId) {
            $this->db->sql_query(
                "UPDATE franchises SET vPagarmeRecipientId='" . addslashes($recipientId) . "'
                 WHERE iFranchiseId=$franchiseId"
            );
        }

        return $this->success(['vRecipientId' => $recipientId, 'message' => 'Pagar.me recipient created.']);
    }

    // ----------------------------------------------------------------
    // 3. Get split payment detail
    // ----------------------------------------------------------------
    public function getSplitDetail(array $req): string
    {
        $splitLogId = (int)($req['iSplitLogId'] ?? 0);
        $requestId  = (int)($req['iRequestId']  ?? 0);

        if (!$splitLogId && !$requestId) return $this->error('iSplitLogId or iRequestId required.');
        $where = $splitLogId ? "iSplitLogId=$splitLogId" : "iRequestId=$requestId";

        $rows = $this->db->MySQLSelect("SELECT * FROM pagarme_split_logs WHERE $where LIMIT 1");
        if (empty($rows)) return $this->error('Split log not found.');

        return $this->success(['split' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // 4. Refund a payment
    // ----------------------------------------------------------------
    public function refundPayment(array $req): string
    {
        $splitLogId = (int)($req['iSplitLogId'] ?? 0);
        $amount     = (float)($req['fAmount']   ?? 0); // 0 = full refund

        if (!$splitLogId) return $this->error('iSplitLogId required.');

        $rows = $this->db->MySQLSelect("SELECT * FROM pagarme_split_logs WHERE iSplitLogId=$splitLogId LIMIT 1");
        if (empty($rows)) return $this->error('Split log not found.');

        $log      = $rows[0];
        $chargeId = $log['vPagarmeChargeId'];

        if (!$chargeId) return $this->error('No Pagar.me charge ID found.');

        $payload  = $amount > 0 ? ['amount' => $this->toCents($amount)] : [];
        $response = $this->apiRequest('POST', "/charges/$chargeId/refund", $payload);

        $refunded = ($response['status'] ?? '') === 'refunded';
        if ($refunded) {
            $this->db->sql_query(
                "UPDATE pagarme_split_logs SET eStatus='Refunded' WHERE iSplitLogId=$splitLogId"
            );
        }

        return $this->success(['refunded' => $refunded, 'response' => $response]);
    }

    // ----------------------------------------------------------------
    // 5. Get recipient wallet balance
    // ----------------------------------------------------------------
    public function getRecipientBalance(array $req): string
    {
        $recipientId = trim($req['vRecipientId'] ?? '');
        $driverId    = (int)($req['iDriverId']   ?? 0);

        if (!$recipientId && $driverId) {
            $rows = $this->db->MySQLSelect(
                "SELECT vPagarmeRecipientId FROM register_driver WHERE iDriverId=$driverId LIMIT 1"
            );
            $recipientId = $rows[0]['vPagarmeRecipientId'] ?? '';
        }
        if (!$recipientId) return $this->error('vRecipientId or iDriverId required.');

        $response = $this->apiRequest('GET', "/recipients/$recipientId/balance", []);
        return $this->success(['balance' => $response]);
    }

    // ----------------------------------------------------------------
    // Internal helpers
    // ----------------------------------------------------------------
    private function resolveShares(int $franchiseId): array
    {
        if ($franchiseId) {
            $rows = $this->db->MySQLSelect(
                "SELECT fMasterSharePercent, fRevenueSharePercent, fDriverSharePercent
                 FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1"
            );
            if (!empty($rows)) {
                return [
                    'master'     => (float)$rows[0]['fMasterSharePercent'],
                    'franchisee' => (float)$rows[0]['fRevenueSharePercent'],
                    'driver'     => (float)$rows[0]['fDriverSharePercent'],
                ];
            }
        }
        // Default: no franchise → 15% platform, 0% franchisee, 85% driver
        return ['master' => 15.0, 'franchisee' => 0.0, 'driver' => 85.0];
    }

    private function getFranchiseeRecipientId(int $franchiseId): ?string
    {
        if (!$franchiseId) return null;
        $rows = $this->db->MySQLSelect(
            "SELECT vPagarmeRecipientId FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1"
        );
        return $rows[0]['vPagarmeRecipientId'] ?? null;
    }

    private function getDriverRecipientId(int $driverId): ?string
    {
        $rows = $this->db->MySQLSelect(
            "SELECT vPagarmeRecipientId FROM register_driver WHERE iDriverId=$driverId LIMIT 1"
        );
        return $rows[0]['vPagarmeRecipientId'] ?? null;
    }

    private function creditDriverWallet(int $driverId, float $amount, int $requestId): void
    {
        // Update driver wallet balance record for display (actual payout is via Pagar.me)
        if (function_exists('updateDriverWallet')) {
            updateDriverWallet($driverId, $amount, "Ride payout for request #$requestId");
        }
    }

    private function toCents(float $amount): int
    {
        return (int)round($amount * 100);
    }

    private function apiRequest(string $method, string $endpoint, array $payload): array
    {
        if (!$this->secretKey) {
            return ['error' => 'PAGARME_SECRET_KEY not configured'];
        }

        $url = $this->baseUrl . $endpoint;
        $ch  = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->secretKey . ':'),
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        } elseif ($method === 'GET') {
            // GET is default
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) return ['error' => 'cURL error: ' . $error];
        if (!$response) return ['error' => 'Empty response from Pagar.me'];

        return json_decode($response, true) ?? ['error' => 'Invalid JSON response'];
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
