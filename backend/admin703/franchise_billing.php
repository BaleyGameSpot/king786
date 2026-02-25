<?php
/**
 * ADMIN: Franchise Billing Management
 * View and manage billing plans and invoices for a franchise.
 */

$script = 'billing';
require_once('../common.php');

if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit; }

$franchiseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$franchiseId) { header("Location: franchise_list.php"); exit; }

$adminId = (int)$_SESSION['admin_id'];
$success = $error = '';

require_once dirname(__DIR__) . '/features/EfiBilling.php';
$billing = new EfiBilling($obj, $tconfig);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && SITE_TYPE !== 'Demo') {
    $postAction = $_POST['billing_action'] ?? '';
    $result = json_decode($billing->handleRequest(array_merge($_POST, ['iFranchiseId' => $franchiseId])), true);
    if (($result['status'] ?? '') === 'success') {
        $success = $result['message'] ?? 'Done.';
    } else {
        $error = $result['message'] ?? 'Error.';
    }
}

// Manual invoice generation
if ($_GET['act'] ?? '' === 'generate_invoice') {
    $result = json_decode($billing->generateMonthlyInvoice([
        'billing_action' => 'generateMonthlyInvoice',
        'iFranchiseId'   => $franchiseId,
        'iBillingMonth'  => (int)date('n'),
        'iBillingYear'   => (int)date('Y'),
    ]), true);
    $success = $result['message'] ?? ($result['status'] === 'error' ? $result['message'] : '');
    $error   = $result['status'] === 'error' ? $result['message'] : '';
}

// Get franchise details
$franchise = $obj->MySQLSelect("SELECT * FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1")[0] ?? null;
if (!$franchise) { header("Location: franchise_list.php"); exit; }

// Get active plan
$plan     = json_decode($billing->handleRequest(['billing_action' => 'getPlan', 'iFranchiseId' => $franchiseId]), true)['plan'] ?? null;
// Get invoices
$invoices = json_decode($billing->handleRequest(['billing_action' => 'getInvoiceList', 'iFranchiseId' => $franchiseId]), true)['invoices'] ?? [];
// Get overage
$overage  = json_decode($billing->handleRequest(['billing_action' => 'processOverage', 'iFranchiseId' => $franchiseId]), true);

include('../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Billing – <?= htmlspecialchars($franchise['vFranchiseName']) ?> <small><?= htmlspecialchars($franchise['vCity']) ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="franchise_list.php">Franchises</a></li>
      <li class="active">Billing</li>
    </ol>
  </section>
  <section class="content">
    <?php if ($success): ?><div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger  alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="row">
      <!-- Plan Configuration -->
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title">Billing Plan</h3></div>
          <form method="POST">
            <input type="hidden" name="billing_action" value="createPlan">
            <div class="box-body">
              <div class="form-group">
                <label>Plan Type</label>
                <select name="ePlanType" class="form-control" id="planType" onchange="togglePlanFields()">
                  <option value="FixedMonthly" <?= ($plan['ePlanType'] ?? '') === 'FixedMonthly' ? 'selected' : '' ?>>Fixed Monthly Fee</option>
                  <option value="TieredOverage" <?= ($plan['ePlanType'] ?? '') === 'TieredOverage' ? 'selected' : '' ?>>Tiered Overage</option>
                </select>
              </div>
              <div id="fixedFields">
                <div class="form-group">
                  <label>Monthly Fee (R$)</label>
                  <input type="number" name="fMonthlyFee" class="form-control" step="0.01"
                         value="<?= htmlspecialchars($plan['fMonthlyFee'] ?? '299.00') ?>">
                </div>
              </div>
              <div id="tieredFields" style="display:none">
                <div class="form-group">
                  <label>Monthly Trip Quota</label>
                  <input type="number" name="iMonthlyTripQuota" class="form-control"
                         value="<?= htmlspecialchars($plan['iMonthlyTripQuota'] ?? '500') ?>">
                  <small class="help-block">Trips included for free per month</small>
                </div>
                <div class="form-group">
                  <label>Overage Fee per Trip (R$)</label>
                  <input type="number" name="fOverageFeePerTrip" class="form-control" step="0.01"
                         value="<?= htmlspecialchars($plan['fOverageFeePerTrip'] ?? '0.50') ?>">
                </div>
              </div>
              <?php if ($plan): ?>
              <div class="alert alert-info">
                <strong>Current Plan:</strong> <?= htmlspecialchars($plan['ePlanType']) ?><br>
                Status: <strong><?= htmlspecialchars($plan['eStatus']) ?></strong><br>
                Next Billing: <?= htmlspecialchars($plan['dNextBillingDate'] ?? 'N/A') ?>
              </div>
              <?php endif; ?>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary">Save Plan</button>
              <?php if ($plan): ?>
              <a href="?id=<?= $franchiseId ?>&act=generate_invoice" class="btn btn-warning"
                 onclick="return confirm('Generate invoice for current month?')">
                <i class="fa fa-file-text"></i> Gen Invoice
              </a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <?php if ($plan && $plan['ePlanType'] === 'TieredOverage' && $overage['status'] === 'success'): ?>
        <!-- Current month overage meter -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title">This Month Usage</h3></div>
          <div class="box-body">
            <strong>Trips completed:</strong> <?= (int)($overage['iTripsCompleted'] ?? 0) ?><br>
            <strong>Quota:</strong> <?= (int)($overage['iMonthlyQuota'] ?? 0) ?><br>
            <strong>Over quota:</strong> <?= (int)($overage['iTripsOverQuota'] ?? 0) ?><br>
            <strong>Current overage fee:</strong>
              <span class="text-danger">R$ <?= number_format((float)($overage['fCurrentOverage'] ?? 0), 2, ',', '.') ?></span>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Invoice list -->
      <div class="col-md-8">
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Invoice History</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-bordered table-sm" id="invoiceTable">
              <thead>
                <tr><th>Invoice #</th><th>Period</th><th>Trips</th><th>Base Fee</th><th>Overage</th><th>Total</th><th>Status</th><th>Due</th></tr>
              </thead>
              <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                  <td><?= htmlspecialchars($inv['vInvoiceNumber']) ?></td>
                  <td><?= str_pad($inv['iBillingPeriodMonth'],2,'0',STR_PAD_LEFT) ?>/<?= $inv['iBillingPeriodYear'] ?></td>
                  <td><?= (int)$inv['iTripsCompleted'] ?> / <?= (int)$inv['iTripsIncluded'] ?></td>
                  <td>R$ <?= number_format((float)$inv['fBaseFee'], 2, ',', '.') ?></td>
                  <td>R$ <?= number_format((float)$inv['fOverageFee'], 2, ',', '.') ?></td>
                  <td><strong>R$ <?= number_format((float)$inv['fTotalAmount'], 2, ',', '.') ?></strong></td>
                  <td>
                    <?php $sc = ['Draft'=>'default','Sent'=>'info','Paid'=>'success','Overdue'=>'danger','Cancelled'=>'default'][$inv['eStatus']] ?? 'default'; ?>
                    <span class="label label-<?= $sc ?>"><?= htmlspecialchars($inv['eStatus']) ?></span>
                  </td>
                  <td><?= $inv['dDueDate'] ? date('d/m/Y', strtotime($inv['dDueDate'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($invoices)): ?>
                <tr><td colspan="8" class="text-center">No invoices yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<script>
function togglePlanFields() {
    var t = $('#planType').val();
    $('#fixedFields').toggle(t === 'FixedMonthly');
    $('#tieredFields').toggle(t === 'TieredOverage');
}
$(document).ready(function() {
    togglePlanFields();
    $('#invoiceTable').DataTable({ order: [[0,'desc']], pageLength: 12 });
});
</script>
<?php include('../footer.php'); ?>
