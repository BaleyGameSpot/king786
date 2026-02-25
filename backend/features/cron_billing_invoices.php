<?php
/**
 * CRON: Monthly B2B Billing Invoice Generator
 * ---------------------------------------------
 * Runs on the 1st of each month to generate invoices
 * for all active franchise billing plans.
 *
 * Add to cron:
 *   0 9 1 * * php /path/to/backend/features/cron_billing_invoices.php >> /path/to/logs/billing.log 2>&1
 */

$rootPath = dirname(__DIR__);
if (!file_exists($rootPath . '/common.php')) {
    die("Cannot find common.php\n");
}
require_once $rootPath . '/common.php';
require_once __DIR__ . '/EfiBilling.php';

$billing      = new EfiBilling($obj, $tconfig);
$lastMonth    = (int)date('n', strtotime('last month'));
$lastMonthYear = (int)date('Y', strtotime('last month'));

echo "[" . date('Y-m-d H:i:s') . "] Billing Invoice Cron – generating for $lastMonth/$lastMonthYear\n";

// Get all franchises with active plans
$franchises = $obj->MySQLSelect(
    "SELECT DISTINCT iFranchiseId FROM franchise_billing_plans WHERE eStatus='Active'"
);

$generated = 0;
foreach ($franchises as $fr) {
    $fId    = (int)$fr['iFranchiseId'];
    $result = json_decode($billing->generateMonthlyInvoice([
        'billing_action' => 'generateMonthlyInvoice',
        'iFranchiseId'   => $fId,
        'iBillingMonth'  => $lastMonth,
        'iBillingYear'   => $lastMonthYear,
    ]), true);

    $status = $result['status'] ?? 'error';
    $msg    = $result['message'] ?? json_encode($result);
    echo "  Franchise #$fId: [$status] $msg\n";

    if ($status === 'success') $generated++;
}

echo "[" . date('Y-m-d H:i:s') . "] Generated: $generated invoices. Done.\n";
