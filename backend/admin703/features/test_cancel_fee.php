<?php
/**
 * ADMIN TEST: Proportional Cancellation Fee
 * Calculate GPS-based proportional cancellation fee
 */
$script = 'cancelFee';
require_once('../../common.php');
if (!isset($_SESSION['admin_id'])) { header("Location: ../../login.php"); exit; }

$result = null;

require_once dirname(__DIR__, 2) . '/features/ProportionalCancellation.php';
$pc = new ProportionalCancellation($obj, $tconfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = json_decode($pc->handleRequest($_POST), true);
}

include('../../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>📏 Proportional Cancellation Fee <small>GPS-based fee calculation test</small></h1>
    <ol class="breadcrumb">
      <li><a href="../admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="../dashboard.php">Dashboard</a></li>
      <li class="active">Cancellation Fee Test</li>
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
      <div class="col-md-5">

        <!-- Calculate Fee -->
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-calculator"></i> Calculate Proportional Fee</h3></div>
          <form method="POST">
            <input type="hidden" name="cancel_action" value="calculateFee">
            <div class="box-body">
              <div class="form-group">
                <label>Driver Start Location (lat,lng)</label>
                <input type="text" name="vDriverStartLatLng" class="form-control" value="-23.5505,-46.6333">
                <small class="help-block">Where driver started driving toward pickup</small>
              </div>
              <div class="form-group">
                <label>Driver Cancel Location (lat,lng)</label>
                <input type="text" name="vDriverCancelLatLng" class="form-control" value="-23.5550,-46.6350">
                <small class="help-block">Where driver was when cancellation happened</small>
              </div>
              <div class="form-group">
                <label>Pickup Location (lat,lng)</label>
                <input type="text" name="vPickupLatLng" class="form-control" value="-23.5600,-46.6400">
              </div>
              <div class="form-group">
                <label>Base Cancellation Fee (R$)</label>
                <input type="number" name="fBaseCancellationFee" class="form-control" step="0.01" value="10.00">
              </div>
              <div class="form-group">
                <label>Who cancelled?</label>
                <select name="eWho" class="form-control">
                  <option value="Passenger">Passenger</option>
                  <option value="Driver">Driver</option>
                </select>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-calculator"></i> Calculate Fee</button>
            </div>
          </form>
        </div>

        <!-- Record Cancel -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-times-circle"></i> Record Cancellation</h3></div>
          <form method="POST">
            <input type="hidden" name="cancel_action" value="recordCancel">
            <div class="box-body">
              <div class="row">
                <div class="col-xs-6">
                  <div class="form-group">
                    <label>Request ID</label>
                    <input type="number" name="iRequestId" class="form-control" value="100">
                  </div>
                </div>
                <div class="col-xs-6">
                  <div class="form-group">
                    <label>Driver ID</label>
                    <input type="number" name="iDriverId" class="form-control" value="1">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>User ID</label>
                <input type="number" name="iUserId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>Driver Start LatLng</label>
                <input type="text" name="vDriverStartLatLng" class="form-control" value="-23.5505,-46.6333">
              </div>
              <div class="form-group">
                <label>Driver Cancel LatLng</label>
                <input type="text" name="vDriverCancelLatLng" class="form-control" value="-23.5550,-46.6350">
              </div>
              <div class="form-group">
                <label>Pickup LatLng</label>
                <input type="text" name="vPickupLatLng" class="form-control" value="-23.5600,-46.6400">
              </div>
              <div class="form-group">
                <label>Base Fee (R$)</label>
                <input type="number" name="fBaseCancellationFee" class="form-control" step="0.01" value="10.00">
              </div>
              <div class="form-group">
                <label>Who cancelled?</label>
                <select name="eWho" class="form-control">
                  <option value="Passenger">Passenger</option>
                  <option value="Driver">Driver</option>
                </select>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Record Cancellation</button>
            </div>
          </form>
        </div>

      </div>

      <div class="col-md-7">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">How It Works</h3></div>
          <div class="box-body">
            <p>The cancellation fee is calculated proportionally based on how far the driver had already traveled toward the pickup point.</p>
            <div class="alert alert-info">
              <strong>Formula:</strong><br>
              <code>chargedFee = baseFee × (distanceTraveled / totalDistanceToPickup)</code>
            </div>
            <p><strong>Example:</strong> If driver traveled 50% of the way to the pickup, and base fee is R$10, the charged fee is R$5.</p>
            <ul>
              <li>Uses Haversine formula for accurate GPS distance</li>
              <li>If driver cancelled — passenger gets a penalty reduction (driver isn't charged)</li>
              <li>If passenger cancelled — charged fee is deducted from passenger wallet and credited to driver</li>
              <li>Penalty automatically transferred via PenaltyTransfer module</li>
            </ul>
            <h5>Available Actions:</h5>
            <code>calculateFee | recordCancel | waiveFee | getCancelDetail</code>
          </div>
        </div>

        <?php if ($result): ?>
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">API Response</h3></div>
          <div class="box-body"><pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
<?php include('../../footer.php'); ?>
