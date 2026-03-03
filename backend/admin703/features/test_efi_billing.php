<?php
/**
 * ADMIN TEST: EfiPay B2B Billing Feature
 * Test fixed monthly & tiered overage billing plans
 */
$script = 'efiBilling';
require_once('../../common.php');
if (!isset($_SESSION['admin_id'])) { header("Location: ../../login.php"); exit; }

$result = null;
$defined = defined('EFI_CLIENT_ID') && EFI_CLIENT_ID !== 'Client_Id_...';

require_once dirname(__DIR__, 2) . '/features/EfiBilling.php';
$efi = new EfiBilling($obj, $tconfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && SITE_TYPE !== 'Demo') {
    $result = json_decode($efi->handleRequest($_POST), true);
}

// Get franchises for the dropdown
$franchises = $obj->MySQLSelect("SELECT iFranchiseId, vFranchiseName, vCity FROM franchises WHERE eStatus='Active' ORDER BY vCity LIMIT 50");

include('../../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>📊 EfiPay B2B Billing <small>Fixed monthly &amp; tiered overage billing test</small></h1>
    <ol class="breadcrumb">
      <li><a href="../admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="../dashboard.php">Dashboard</a></li>
      <li class="active">EfiPay B2B Billing</li>
    </ol>
  </section>
  <section class="content">

    <?php if (!$defined): ?>
    <div class="alert alert-warning">
      <strong>Setup Required:</strong> Add EfiPay credentials to <code>app_configuration_file.php</code>:
      <br><code>define('EFI_CLIENT_ID', 'Client_Id_...');</code>
      <br><code>define('EFI_CLIENT_SECRET', 'Client_Secret_...');</code>
      <br><code>define('EFI_SANDBOX', 'Yes');</code>
    </div>
    <?php endif; ?>

    <?php if ($result): ?>
    <div class="alert alert-<?= ($result['status'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <button class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($result['message'] ?? json_encode($result)) ?>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-5">

        <!-- Create Billing Plan -->
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Create / Update Billing Plan</h3></div>
          <form method="POST" id="planForm">
            <input type="hidden" name="billing_action" value="createPlan">
            <div class="box-body">
              <div class="form-group">
                <label>Franchise</label>
                <select name="iFranchiseId" class="form-control" required>
                  <option value="">-- Select Franchise --</option>
                  <?php foreach ($franchises as $f): ?>
                  <option value="<?= (int)$f['iFranchiseId'] ?>"><?= htmlspecialchars($f['vFranchiseName']) ?> (<?= htmlspecialchars($f['vCity']) ?>)</option>
                  <?php endforeach; ?>
                  <?php if (empty($franchises)): ?>
                  <option value="1">Franchise #1 (demo)</option>
                  <?php endif; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Plan Type</label>
                <select name="ePlanType" class="form-control" id="planType" onchange="togglePlanFields()">
                  <option value="FixedMonthly">Fixed Monthly Fee</option>
                  <option value="TieredOverage">Tiered Overage</option>
                </select>
              </div>
              <div id="fixedFields">
                <div class="form-group">
                  <label>Monthly Fee (R$)</label>
                  <input type="number" name="fMonthlyFee" class="form-control" step="0.01" value="299.00">
                </div>
              </div>
              <div id="tieredFields" style="display:none">
                <div class="form-group">
                  <label>Monthly Trip Quota (included free)</label>
                  <input type="number" name="iMonthlyTripQuota" class="form-control" value="500">
                </div>
                <div class="form-group">
                  <label>Overage Fee per Extra Trip (R$)</label>
                  <input type="number" name="fOverageFeePerTrip" class="form-control" step="0.01" value="0.50">
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Plan</button>
            </div>
          </form>
        </div>

        <!-- Generate Invoice -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text"></i> Generate Monthly Invoice</h3></div>
          <form method="POST">
            <input type="hidden" name="billing_action" value="generateMonthlyInvoice">
            <div class="box-body">
              <div class="form-group">
                <label>Franchise ID</label>
                <input type="number" name="iFranchiseId" class="form-control" value="1">
              </div>
              <div class="row">
                <div class="col-xs-6">
                  <div class="form-group">
                    <label>Month</label>
                    <input type="number" name="iBillingMonth" class="form-control" value="<?= date('n') ?>" min="1" max="12">
                  </div>
                </div>
                <div class="col-xs-6">
                  <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="iBillingYear" class="form-control" value="<?= date('Y') ?>">
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-warning"><i class="fa fa-file"></i> Generate Invoice</button>
            </div>
          </form>
        </div>

        <!-- Get Plan -->
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-eye"></i> Get Billing Plan</h3></div>
          <form method="POST">
            <input type="hidden" name="billing_action" value="getPlan">
            <div class="box-body">
              <div class="form-group">
                <label>Franchise ID</label>
                <input type="number" name="iFranchiseId" class="form-control" value="1">
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-default"><i class="fa fa-search"></i> Get Plan</button>
            </div>
          </form>
        </div>

      </div>

      <div class="col-md-7">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">How B2B Billing Works</h3></div>
          <div class="box-body">
            <h5>Fixed Monthly Plan</h5>
            <p>Franchise pays a flat fee each month regardless of trip volume.</p>
            <table class="table table-sm table-bordered">
              <tr><td>Example: R$ 299/month</td><td>Unlimited trips included</td></tr>
            </table>
            <h5>Tiered Overage Plan</h5>
            <p>Franchise gets a quota of free trips, then pays per extra trip.</p>
            <table class="table table-sm table-bordered">
              <tr><td>500 trips/month free</td><td>R$ 0.50 per extra trip</td></tr>
              <tr><td>600 trips completed</td><td>Invoice = 100 × R$ 0.50 = R$ 50</td></tr>
            </table>
            <ul>
              <li>EfiPay creates PIX/Boleto charges automatically</li>
              <li>Monthly invoices auto-generated by cron on 1st of month</li>
              <li>Supports EfiPay subscription for recurring billing</li>
            </ul>
            <h5>Available Actions:</h5>
            <code>createPlan | updatePlan | getPlan | generateMonthlyInvoice | getInvoice | getInvoiceList | createEfiSubscription | cancelSubscription | processOverage | webhookEfi</code>
          </div>
        </div>

        <?php if ($result): ?>
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">API Response</h3></div>
          <div class="box-body"><pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></div>
        </div>
        <?php endif; ?>

        <!-- Quick link to dedicated billing page -->
        <?php if (!empty($franchises)): ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Quick Links – Franchise Billing Pages</h3></div>
          <div class="box-body">
            <?php foreach (array_slice($franchises, 0, 5) as $f): ?>
            <a href="../franchise_billing.php?id=<?= (int)$f['iFranchiseId'] ?>" class="btn btn-sm btn-primary" style="margin:3px">
              <i class="fa fa-money"></i> <?= htmlspecialchars($f['vFranchiseName']) ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
<script>
function togglePlanFields() {
    var t = document.getElementById('planType').value;
    document.getElementById('fixedFields').style.display  = t === 'FixedMonthly'  ? '' : 'none';
    document.getElementById('tieredFields').style.display = t === 'TieredOverage' ? '' : 'none';
}
</script>
<?php include('../../footer.php'); ?>
