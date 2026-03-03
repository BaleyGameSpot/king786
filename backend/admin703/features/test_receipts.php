<?php
/**
 * ADMIN TEST: In-App Receipts Feature
 * Generate and view trip/order receipts
 */
$script = 'receipts';
require_once('../../common.php');
if (!isset($_SESSION['admin_id'])) { header("Location: ../../login.php"); exit; }

$result = null;

require_once dirname(__DIR__, 2) . '/features/InAppReceipts.php';
$receipts = new InAppReceipts($obj, $tconfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = json_decode($receipts->handleRequest($_POST), true);
}

// Demo: get receipts for user 1
$myReceipts = json_decode($receipts->handleRequest([
    'receipt_action' => 'getMyReceipts',
    'iUserId'  => 1,
    'eUserType'=> 'User',
    'iPage'    => 1,
]), true);

include('../../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>🧾 In-App Receipts <small>Generate and view trip receipts</small></h1>
    <ol class="breadcrumb">
      <li><a href="../admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="../dashboard.php">Dashboard</a></li>
      <li class="active">In-App Receipts</li>
    </ol>
  </section>
  <section class="content">

    <?php if ($result): ?>
    <div class="alert alert-<?= ($result['status'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <button class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($result['message'] ?? json_encode($result)) ?>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-4">

        <!-- Generate Receipt -->
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text"></i> Generate Receipt</h3></div>
          <form method="POST">
            <input type="hidden" name="receipt_action" value="generate">
            <div class="box-body">
              <div class="form-group">
                <label>Receipt Type</label>
                <select name="eReceiptType" class="form-control">
                  <option value="Trip">Trip</option>
                  <option value="Cancellation">Cancellation Fee</option>
                  <option value="NoShow">No-Show Fee</option>
                  <option value="Penalty">Penalty</option>
                </select>
              </div>
              <div class="form-group">
                <label>Reference ID (Trip/Request ID)</label>
                <input type="number" name="iReferenceId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>User ID</label>
                <input type="number" name="iUserId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>Driver ID</label>
                <input type="number" name="iDriverId" class="form-control" value="1">
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Generate</button>
            </div>
          </form>
        </div>

        <!-- Get Receipt by ID -->
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-search"></i> Get Receipt by ID</h3></div>
          <form method="POST">
            <input type="hidden" name="receipt_action" value="get">
            <div class="box-body">
              <div class="form-group">
                <label>Receipt ID</label>
                <input type="number" name="iReceiptId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>User ID (for auth)</label>
                <input type="number" name="iUserId" class="form-control" value="1">
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-default"><i class="fa fa-eye"></i> Get Receipt</button>
            </div>
          </form>
        </div>

        <!-- Email Receipt -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-envelope"></i> Email Receipt</h3></div>
          <form method="POST">
            <input type="hidden" name="receipt_action" value="emailReceipt">
            <div class="box-body">
              <div class="form-group">
                <label>Receipt ID</label>
                <input type="number" name="iReceiptId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>User ID</label>
                <input type="number" name="iUserId" class="form-control" value="1">
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-warning"><i class="fa fa-send"></i> Email Receipt</button>
            </div>
          </form>
        </div>

      </div>

      <div class="col-md-8">
        <?php if ($result): ?>
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">API Response</h3></div>
          <div class="box-body"><pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></div>
        </div>
        <?php endif; ?>

        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">Feature Info – In-App Receipts</h3></div>
          <div class="box-body">
            <p>Automatically generates detailed receipts for every trip, cancellation, no-show fee, and penalty.</p>
            <ul>
              <li>Unique receipt numbers like <code>TR-20260225-00123</code></li>
              <li>Stores passenger name, driver name, pickup/dropoff, fare breakdown</li>
              <li>Supports email delivery and PDF generation via TCPDF</li>
              <li>Receipts are linked to the original trip request</li>
            </ul>
            <h5>Available Actions:</h5>
            <code>generate | get | getMyReceipts | emailReceipt | generatePdf</code>
          </div>
        </div>

        <!-- Receipts List -->
        <?php if (!empty($myReceipts['receipts'])): ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Recent Receipts – User #1</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-sm table-bordered">
              <thead><tr><th>#</th><th>Number</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($myReceipts['receipts'] as $r): ?>
                <tr>
                  <td><?= (int)$r['iReceiptId'] ?></td>
                  <td><code><?= htmlspecialchars($r['vReceiptNumber']) ?></code></td>
                  <td><?= htmlspecialchars($r['eReceiptType']) ?></td>
                  <td>R$ <?= number_format((float)($r['fTotalAmount'] ?? 0), 2, ',', '.') ?></td>
                  <td><?= htmlspecialchars($r['dCreatedAt'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Recent Receipts – User #1</h3></div>
          <div class="box-body"><p class="text-muted">No receipts found for User #1. Generate one using the form on the left.</p></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
<?php include('../../footer.php'); ?>
