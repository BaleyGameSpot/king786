<?php
/**
 * FEATURE 11: B2B BILLING MODELS (EfiPay)
 * -----------------------------------------
 * Two billing models for franchisees:
 *   A) Fixed Monthly Fee:   Recurring subscription via EfiPay (https://dev.efipay.com.br/)
 *   B) Tiered Overage Fee:  Monthly trip quota, per-trip fee above limit
 *
 * EfiPay API v1 (Pix + Boleto + Credit Card subscriptions)
 * Docs: https://dev.efipay.com.br/docs/api-cobrancas/
 *
 * Setup:
 *   Define in app_configuration_file.php:
 *     EFI_CLIENT_ID     = 'Client_Id_...'
 *     EFI_CLIENT_SECRET = 'Client_Secret_...'
 *     EFI_SANDBOX       = 'Yes' | 'No'
 *
 * Or per-franchise via franchises.vEfiClientId / vEfiClientSecret
 */

class EfiBilling
{
    private $db;
    private $config;
    private bool   $sandbox;
    private string $globalClientId;
    private string $globalClientSecret;

    private const SANDBOX_URL = 'https://apisandbox.efipay.com.br';
    private const PROD_URL    = 'https://api.efipay.com.br';

    public function __construct($db, array $config)
    {
        $this->db                 = $db;
        $this->config             = $config;
        $this->sandbox            = (defined('EFI_SANDBOX') && strtoupper(EFI_SANDBOX) === 'YES');
        $this->globalClientId     = defined('EFI_CLIENT_ID')     ? EFI_CLIENT_ID     : '';
        $this->globalClientSecret = defined('EFI_CLIENT_SECRET') ? EFI_CLIENT_SECRET : '';
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['billing_action'] ?? '');

        switch ($action) {
            case 'createPlan':           return $this->createBillingPlan($req);
            case 'updatePlan':           return $this->updateBillingPlan($req);
            case 'getPlan':              return $this->getPlan($req);
            case 'generateMonthlyInvoice': return $this->generateMonthlyInvoice($req);
            case 'getInvoice':           return $this->getInvoice($req);
            case 'getInvoiceList':       return $this->getInvoiceList($req);
            case 'createEfiSubscription':return $this->createEfiSubscription($req);
            case 'cancelSubscription':   return $this->cancelSubscription($req);
            case 'processOverage':       return $this->processMonthlyOverage($req);
            case 'webhookEfi':           return $this->handleWebhook($req);
            default:
                return $this->error('Unknown billing_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Create a billing plan for a franchise
    // ----------------------------------------------------------------
    private function createBillingPlan(array $req): string
    {
        $franchiseId       = (int)($req['iFranchiseId']        ?? 0);
        $planType          = trim($req['ePlanType']             ?? 'FixedMonthly');
        $monthlyFee        = (float)($req['fMonthlyFee']        ?? 0);
        $monthlyTripQuota  = (int)($req['iMonthlyTripQuota']    ?? 0);
        $overageFeePerTrip = (float)($req['fOverageFeePerTrip'] ?? 0);

        if (!$franchiseId) return $this->error('iFranchiseId required.');
        if (!in_array($planType, ['FixedMonthly', 'TieredOverage'])) {
            return $this->error('ePlanType must be FixedMonthly or TieredOverage.');
        }
        if ($planType === 'FixedMonthly' && $monthlyFee <= 0) {
            return $this->error('fMonthlyFee required for FixedMonthly plan.');
        }
        if ($planType === 'TieredOverage' && ($monthlyTripQuota <= 0 || $overageFeePerTrip <= 0)) {
            return $this->error('iMonthlyTripQuota and fOverageFeePerTrip required for TieredOverage plan.');
        }

        // Deactivate any existing active plan
        $this->db->sql_query(
            "UPDATE franchise_billing_plans SET eStatus='Inactive'
             WHERE iFranchiseId=$franchiseId AND eStatus='Active'"
        );

        $nextBilling = date('Y-m-d', strtotime('first day of next month'));
        $now         = date('Y-m-d H:i:s');

        $this->db->sql_query(
            "INSERT INTO franchise_billing_plans
                (iFranchiseId, ePlanType, fMonthlyFee, iMonthlyTripQuota, fOverageFeePerTrip,
                 eStatus, dNextBillingDate, dCreatedAt)
             VALUES
                ($franchiseId, '$planType', $monthlyFee, $monthlyTripQuota, $overageFeePerTrip,
                 'Active', '$nextBilling', '$now')"
        );
        $planId = $this->db->MySQLLastInsertID();

        return $this->success([
            'iPlanId'         => $planId,
            'ePlanType'       => $planType,
            'dNextBillingDate'=> $nextBilling,
            'message'         => "Billing plan created for franchise #$franchiseId.",
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Update a billing plan
    // ----------------------------------------------------------------
    private function updateBillingPlan(array $req): string
    {
        $planId  = (int)($req['iPlanId'] ?? 0);
        if (!$planId) return $this->error('iPlanId required.');

        $updates = [];
        if (isset($req['fMonthlyFee']))        $updates[] = "fMonthlyFee="       . (float)$req['fMonthlyFee'];
        if (isset($req['iMonthlyTripQuota']))   $updates[] = "iMonthlyTripQuota=" . (int)$req['iMonthlyTripQuota'];
        if (isset($req['fOverageFeePerTrip']))  $updates[] = "fOverageFeePerTrip=". (float)$req['fOverageFeePerTrip'];
        if (!empty($req['dNextBillingDate']))   $updates[] = "dNextBillingDate='" . addslashes($req['dNextBillingDate']) . "'";
        if (!empty($req['eStatus']))            $updates[] = "eStatus='"          . addslashes($req['eStatus'])         . "'";

        if (empty($updates)) return $this->error('Nothing to update.');
        $this->db->sql_query("UPDATE franchise_billing_plans SET " . implode(', ', $updates) . " WHERE iPlanId=$planId");
        return $this->success(['message' => 'Plan updated.']);
    }

    // ----------------------------------------------------------------
    // 3. Get plan
    // ----------------------------------------------------------------
    private function getPlan(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        $planId      = (int)($req['iPlanId']      ?? 0);

        $where = $planId ? "iPlanId=$planId" : "iFranchiseId=$franchiseId AND eStatus='Active'";
        $rows  = $this->db->MySQLSelect("SELECT * FROM franchise_billing_plans WHERE $where LIMIT 1");
        if (empty($rows)) return $this->error('Plan not found.');

        return $this->success(['plan' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // 4. Generate monthly invoice (called by cron)
    // ----------------------------------------------------------------
    public function generateMonthlyInvoice(array $req): string
    {
        $franchiseId  = (int)($req['iFranchiseId']       ?? 0);
        $billingMonth = (int)($req['iBillingMonth']       ?? (int)date('n'));
        $billingYear  = (int)($req['iBillingYear']        ?? (int)date('Y'));

        if (!$franchiseId) return $this->error('iFranchiseId required.');

        // Get active plan
        $planRows = $this->db->MySQLSelect(
            "SELECT * FROM franchise_billing_plans WHERE iFranchiseId=$franchiseId AND eStatus='Active' LIMIT 1"
        );
        if (empty($planRows)) return $this->error('No active billing plan found.');
        $plan = $planRows[0];

        // Count completed trips in billing period
        $tripsRows = $this->db->MySQLSelect(
            "SELECT COUNT(*) AS cnt FROM pagarme_split_logs
             WHERE iFranchiseId=$franchiseId
               AND eStatus='Completed'
               AND MONTH(dCreatedAt)=$billingMonth AND YEAR(dCreatedAt)=$billingYear"
        );
        $tripsCompleted = (int)($tripsRows[0]['cnt'] ?? 0);

        // Calculate fees
        $baseFee    = (float)$plan['fMonthlyFee'];
        $overageFee = 0.0;
        $tripsOver  = 0;

        if ($plan['ePlanType'] === 'TieredOverage') {
            $quota      = (int)$plan['iMonthlyTripQuota'];
            $tripsOver  = max(0, $tripsCompleted - $quota);
            $overageFee = round($tripsOver * (float)$plan['fOverageFeePerTrip'], 2);
            $baseFee    = 0.0; // TieredOverage has no fixed monthly fee
        }

        $totalAmount = $baseFee + $overageFee;

        // Check invoice not already generated
        $existing = $this->db->MySQLSelect(
            "SELECT iInvoiceId FROM franchise_billing_invoices
             WHERE iFranchiseId=$franchiseId AND iBillingPeriodMonth=$billingMonth AND iBillingPeriodYear=$billingYear
             LIMIT 1"
        );
        if (!empty($existing)) {
            return $this->error('Invoice already generated for this period.');
        }

        $invoiceNumber = 'INV-FR' . $franchiseId . '-' . $billingYear . str_pad($billingMonth, 2, '0', STR_PAD_LEFT);
        $dueDate       = date('Y-m-d', strtotime("+5 days"));
        $now           = date('Y-m-d H:i:s');

        $this->db->sql_query(
            "INSERT INTO franchise_billing_invoices
                (iFranchiseId, iPlanId, vInvoiceNumber, iBillingPeriodMonth, iBillingPeriodYear,
                 iTripsCompleted, iTripsIncluded, iTripsOverage,
                 fBaseFee, fOverageFee, fTotalAmount,
                 eStatus, dDueDate, dCreatedAt)
             VALUES
                ($franchiseId, {$plan['iPlanId']}, '$invoiceNumber', $billingMonth, $billingYear,
                 $tripsCompleted, {$plan['iMonthlyTripQuota']}, $tripsOver,
                 $baseFee, $overageFee, $totalAmount,
                 'Draft', '$dueDate', '$now')"
        );
        $invoiceId = $this->db->MySQLLastInsertID();

        // Send to EfiPay if amount > 0
        if ($totalAmount > 0) {
            $efiResult = $this->createEfiCharge($franchiseId, $invoiceId, $totalAmount, $dueDate);
            if (!empty($efiResult['vEfiChargeId'])) {
                $this->db->sql_query(
                    "UPDATE franchise_billing_invoices
                     SET eStatus='Sent',
                         vEfiChargeId='"   . addslashes($efiResult['vEfiChargeId'])   . "',
                         vEfiPaymentLink='" . addslashes($efiResult['vEfiPaymentLink'] ?? '') . "'
                     WHERE iInvoiceId=$invoiceId"
                );
            }
        } else {
            $this->db->sql_query(
                "UPDATE franchise_billing_invoices SET eStatus='Paid', dPaidAt='$now' WHERE iInvoiceId=$invoiceId"
            );
        }

        return $this->success([
            'iInvoiceId'     => $invoiceId,
            'vInvoiceNumber' => $invoiceNumber,
            'fTotalAmount'   => $totalAmount,
            'iTripsOverage'  => $tripsOver,
            'message'        => "Invoice $invoiceNumber generated. Total: R$ " . number_format($totalAmount, 2, ',', '.'),
        ]);
    }

    // ----------------------------------------------------------------
    // 5. Get invoice
    // ----------------------------------------------------------------
    private function getInvoice(array $req): string
    {
        $invoiceId  = (int)($req['iInvoiceId']     ?? 0);
        $invoiceNum = trim($req['vInvoiceNumber']  ?? '');

        $where = $invoiceId ? "iInvoiceId=$invoiceId" : "vInvoiceNumber='" . addslashes($invoiceNum) . "'";
        if (!$invoiceId && !$invoiceNum) return $this->error('iInvoiceId or vInvoiceNumber required.');

        $rows = $this->db->MySQLSelect("SELECT * FROM franchise_billing_invoices WHERE $where LIMIT 1");
        if (empty($rows)) return $this->error('Invoice not found.');

        return $this->success(['invoice' => $rows[0]]);
    }

    // ----------------------------------------------------------------
    // 6. List invoices for a franchise
    // ----------------------------------------------------------------
    private function getInvoiceList(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT iInvoiceId, vInvoiceNumber, iBillingPeriodMonth, iBillingPeriodYear,
                    iTripsCompleted, fTotalAmount, eStatus, dDueDate, dPaidAt, dCreatedAt
             FROM franchise_billing_invoices
             WHERE iFranchiseId=$franchiseId
             ORDER BY iBillingPeriodYear DESC, iBillingPeriodMonth DESC
             LIMIT 24"
        );
        return $this->success(['invoices' => $rows ?: []]);
    }

    // ----------------------------------------------------------------
    // 7. Create EfiPay subscription (fixed monthly)
    // ----------------------------------------------------------------
    private function createEfiSubscription(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        $planId      = (int)($req['iPlanId']       ?? 0);

        if (!$franchiseId || !$planId) return $this->error('iFranchiseId and iPlanId required.');

        $planRows = $this->db->MySQLSelect("SELECT * FROM franchise_billing_plans WHERE iPlanId=$planId LIMIT 1");
        if (empty($planRows)) return $this->error('Plan not found.');
        $plan = $planRows[0];

        $frRows = $this->db->MySQLSelect("SELECT * FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1");
        if (empty($frRows)) return $this->error('Franchise not found.');

        // Create EfiPay plan first
        $efiPlanPayload = [
            'name'          => "Ridey Franchise #{$franchiseId} – Monthly",
            'interval'      => 1,
            'repeats'       => null, // infinite
        ];
        $efiPlanResp = $this->efiRequest('POST', '/v1/plan', $efiPlanPayload, $franchiseId);
        $efiPlanId   = $efiPlanResp['data']['plan_id'] ?? null;

        if (!$efiPlanId) {
            return $this->error('Failed to create EfiPay plan: ' . json_encode($efiPlanResp));
        }

        // Create subscription charge
        $payload = [
            'plan'      => ['id' => $efiPlanId],
            'items'     => [[
                'name'      => 'Assinatura Mensal Ridey',
                'value'     => (int)($plan['fMonthlyFee'] * 100), // centavos
                'amount'    => 1,
            ]],
            'metadata'  => ['franchise_id' => $franchiseId],
        ];
        $resp       = $this->efiRequest('POST', '/v1/subscription', $payload, $franchiseId);
        $subscId    = $resp['data']['subscription_id'] ?? null;

        if ($subscId) {
            $this->db->sql_query(
                "UPDATE franchise_billing_plans
                 SET vEfiSubscriptionId='" . addslashes($subscId) . "',
                     vEfiPlanId='"         . addslashes($efiPlanId) . "'
                 WHERE iPlanId=$planId"
            );
        }

        return $this->success([
            'vEfiPlanId'         => $efiPlanId,
            'vEfiSubscriptionId' => $subscId,
            'message'            => $subscId ? 'EfiPay subscription created.' : 'Failed to create subscription.',
        ]);
    }

    // ----------------------------------------------------------------
    // 8. Cancel subscription
    // ----------------------------------------------------------------
    private function cancelSubscription(array $req): string
    {
        $planId = (int)($req['iPlanId'] ?? 0);
        if (!$planId) return $this->error('iPlanId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT * FROM franchise_billing_plans WHERE iPlanId=$planId LIMIT 1"
        );
        if (empty($rows)) return $this->error('Plan not found.');
        $plan  = $rows[0];
        $subscId = $plan['vEfiSubscriptionId'] ?? '';

        if ($subscId) {
            $resp = $this->efiRequest('PUT', "/v1/subscription/$subscId/cancel", [], (int)$plan['iFranchiseId']);
        }

        $this->db->sql_query(
            "UPDATE franchise_billing_plans SET eStatus='Cancelled' WHERE iPlanId=$planId"
        );
        return $this->success(['message' => 'Subscription cancelled.']);
    }

    // ----------------------------------------------------------------
    // 9. Process overage calculation mid-month (on-demand)
    // ----------------------------------------------------------------
    private function processMonthlyOverage(array $req): string
    {
        $franchiseId = (int)($req['iFranchiseId'] ?? 0);
        if (!$franchiseId) return $this->error('iFranchiseId required.');

        $planRows = $this->db->MySQLSelect(
            "SELECT * FROM franchise_billing_plans WHERE iFranchiseId=$franchiseId AND eStatus='Active' LIMIT 1"
        );
        if (empty($planRows) || $planRows[0]['ePlanType'] !== 'TieredOverage') {
            return $this->error('No active TieredOverage plan for this franchise.');
        }
        $plan = $planRows[0];

        $thisMonth = (int)date('n');
        $thisYear  = (int)date('Y');
        $tripsRows = $this->db->MySQLSelect(
            "SELECT COUNT(*) AS cnt FROM pagarme_split_logs
             WHERE iFranchiseId=$franchiseId AND eStatus='Completed'
               AND MONTH(dCreatedAt)=$thisMonth AND YEAR(dCreatedAt)=$thisYear"
        );
        $tripsCompleted = (int)($tripsRows[0]['cnt'] ?? 0);
        $quota          = (int)$plan['iMonthlyTripQuota'];
        $tripsOver      = max(0, $tripsCompleted - $quota);
        $overageAmount  = round($tripsOver * (float)$plan['fOverageFeePerTrip'], 2);

        return $this->success([
            'iTripsCompleted'  => $tripsCompleted,
            'iMonthlyQuota'    => $quota,
            'iTripsOverQuota'  => $tripsOver,
            'fCurrentOverage'  => $overageAmount,
            'fOverageFeePerTrip' => $plan['fOverageFeePerTrip'],
        ]);
    }

    // ----------------------------------------------------------------
    // 10. Handle EfiPay webhook (payment confirmation)
    // ----------------------------------------------------------------
    private function handleWebhook(array $req): string
    {
        $body     = file_get_contents('php://input');
        $data     = json_decode($body, true);
        $event    = $data['event']   ?? '';
        $subscId  = $data['subscription_id'] ?? ($data['data']['subscription_id'] ?? '');
        $status   = $data['data']['status'] ?? '';

        if ($status === 'paid' || $event === 'subscription_charge_paid') {
            // Find invoice and mark as paid
            $planRows = $this->db->MySQLSelect(
                "SELECT * FROM franchise_billing_plans WHERE vEfiSubscriptionId='" . addslashes($subscId) . "' LIMIT 1"
            );
            if (!empty($planRows)) {
                $franchiseId = (int)$planRows[0]['iFranchiseId'];
                $now = date('Y-m-d H:i:s');
                $this->db->sql_query(
                    "UPDATE franchise_billing_invoices
                     SET eStatus='Paid', dPaidAt='$now'
                     WHERE iFranchiseId=$franchiseId AND eStatus='Sent'
                     ORDER BY dCreatedAt DESC LIMIT 1"
                );
            }
        }

        return $this->success(['received' => true]);
    }

    // ----------------------------------------------------------------
    // Create a charge (boleto/PIX) via EfiPay for invoice payment
    // ----------------------------------------------------------------
    private function createEfiCharge(int $franchiseId, int $invoiceId, float $amount, string $dueDate): array
    {
        $payload = [
            'items' => [[
                'name'      => "Ridey Franchise Invoice #$invoiceId",
                'value'     => (int)($amount * 100),
                'amount'    => 1,
            ]],
            'shippings' => [],
            'metadata'  => ['franchise_id' => $franchiseId, 'invoice_id' => $invoiceId],
        ];

        // Try PIX charge (instant payment)
        $resp    = $this->efiRequest('POST', '/v2/cob', $payload, $franchiseId);
        $chargeId = $resp['txid'] ?? null;
        $pixLink  = $resp['pixCopiaECola'] ?? null;

        if (!$chargeId) {
            // Fallback: boleto
            $resp    = $this->efiRequest('POST', '/v1/charge', $payload, $franchiseId);
            $chargeId = (string)($resp['data']['charge_id'] ?? '');
            $pixLink  = $resp['data']['link'] ?? null;
        }

        return ['vEfiChargeId' => $chargeId, 'vEfiPaymentLink' => $pixLink];
    }

    // ----------------------------------------------------------------
    // EfiPay API request (handles OAuth2 token)
    // ----------------------------------------------------------------
    private function efiRequest(string $method, string $endpoint, array $payload, int $franchiseId = 0): array
    {
        $clientId     = $this->globalClientId;
        $clientSecret = $this->globalClientSecret;

        if ($franchiseId) {
            $frRows = $this->db->MySQLSelect(
                "SELECT vEfiClientId, vEfiClientSecret FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1"
            );
            if (!empty($frRows[0]['vEfiClientId'])) {
                $clientId     = $frRows[0]['vEfiClientId'];
                $clientSecret = $frRows[0]['vEfiClientSecret'];
            }
        }

        if (!$clientId || !$clientSecret) {
            return ['error' => 'EfiPay credentials not configured'];
        }

        $baseUrl = $this->sandbox ? self::SANDBOX_URL : self::PROD_URL;
        $token   = $this->getEfiToken($baseUrl, $clientId, $clientSecret);
        if (!$token) return ['error' => 'Failed to obtain EfiPay OAuth token'];

        $url = $baseUrl . $endpoint;
        $ch  = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $token",
                'Content-Type: application/json',
            ],
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? ['error' => 'Invalid JSON from EfiPay'];
    }

    private function getEfiToken(string $baseUrl, string $clientId, string $secret): ?string
    {
        $ch = curl_init("$baseUrl/oauth/token");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERPWD        => "$clientId:$secret",
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode(['grant_type' => 'client_credentials']),
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
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
