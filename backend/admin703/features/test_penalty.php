<?php
/**
 * ADMIN TEST: Automated Penalty Transfers Feature
 * View penalty transfer logs and driver wallet credits
 */
$script = 'penalty';
require_once('../../common.php');
if (!isset($_SESSION['admin_id'])) { header("Location: ../../login.php"); exit; }

$result = null;

require_once dirname(__DIR__, 2) . '/features/PenaltyTransfer.php';
$penalty = new PenaltyTransfer($obj, $tconfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = json_decode($penalty->handleRequest($_POST), true);
}

// Get stats by default
$stats    = json_decode($penalty->handleRequest(['penalty_action' => 'getStats']), true);
$driverLog= json_decode($penalty->handleRequest(['penalty_action' => 'getDriverLog', 'iDriverId' => 1, 'iPage' => 1]), true);

include('../../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>⚡ Penalty Transfers <small>Automated penalty management and driver wallet credits</small></h1>
    <ol class="breadcrumb">
      <li><a href="../admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="../dashboard.php">Dashboard</a></li>
      <li class="active">Penalty Transfers</li>
    </ol>
  </section>
  <section class="content">

    <?php if ($result): ?>
    <div class="alert alert-<?= ($result['status'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <button class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($result['message'] ?? json_encode($result)) ?>
    </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <?php if (!empty($stats['stats'])): ?>
    <div class="row">
      <div class="col-sm-3">
        <div class="info-box bg-green">
          <span class="info-box-icon"><i class="fa fa-money"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Transferred</span>
            <span class="info-box-number">R$ <?= number_format((float)($stats['stats']['fTotalTransferred'] ?? 0), 2, ',', '.') ?></span>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="info-box bg-blue">
          <span class="info-box-icon"><i class="fa fa-exchange"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Transfers</span>
            <span class="info-box-number"><?= (int)($stats['stats']['iTotalTransfers'] ?? 0) ?></span>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="info-box bg-yellow">
          <span class="info-box-icon"><i class="fa fa-undo"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Reversed</span>
            <span class="info-box-number"><?= (int)($stats['stats']['iTotalReversed'] ?? 0) ?></span>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="info-box bg-red">
          <span class="info-box-icon"><i class="fa fa-times"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Failed</span>
            <span class="info-box-number"><?= (int)($stats['stats']['iTotalFailed'] ?? 0) ?></span>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-4">

        <!-- Manual Transfer -->
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-send"></i> Manual Penalty Transfer</h3></div>
          <form method="POST">
            <input type="hidden" name="penalty_action" value="transfer">
            <div class="box-body">
              <div class="form-group">
                <label>Request ID</label>
                <input type="number" name="iRequestId" class="form-control" value="100">
              </div>
              <div class="form-group">
                <label>Driver ID</label>
                <input type="number" name="iDriverId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>User ID (Passenger)</label>
                <input type="number" name="iUserId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>Penalty Amount (R$)</label>
                <input type="number" name="fPenaltyAmount" class="form-control" step="0.01" value="8.50">
              </div>
              <div class="form-group">
                <label>Penalty Type</label>
                <select name="ePenaltyType" class="form-control">
                  <option value="Cancellation">Cancellation Fee</option>
                  <option value="NoShow">No-Show Fee</option>
                  <option value="LateCancel">Late Cancellation</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label>Transfer To</label>
                <select name="eTransferTo" class="form-control">
                  <option value="DriverWallet">Driver Wallet</option>
                  <option value="PassengerWallet">Passenger Wallet</option>
                  <option value="Platform">Platform (no transfer)</option>
                </select>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> Execute Transfer</button>
            </div>
          </form>
        </div>

        <!-- Reverse Transfer -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-undo"></i> Reverse Transfer</h3></div>
          <form method="POST">
            <input type="hidden" name="penalty_action" value="reverse">
            <div class="box-body">
              <div class="form-group">
                <label>Penalty Log ID</label>
                <input type="number" name="iPenaltyLogId" class="form-control" value="">
                <small class="help-block">Get the ID from the transfer log table below.</small>
              </div>
              <div class="form-group">
                <label>Admin Note</label>
                <textarea name="vAdminNote" class="form-control" rows="2" placeholder="Reason for reversal..."></textarea>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-warning"><i class="fa fa-undo"></i> Reverse Transfer</button>
            </div>
          </form>
        </div>

        <!-- Get Stats -->
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> Actions</h3></div>
          <div class="box-body">
            <form method="POST" style="display:inline">
              <input type="hidden" name="penalty_action" value="getStats">
              <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-refresh"></i> Refresh Stats</button>
            </form>
            &nbsp;
            <form method="POST" style="display:inline">
              <input type="hidden" name="penalty_action" value="getLog">
              <input type="hidden" name="iPage" value="1">
              <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-list"></i> Full Log</button>
            </form>
          </div>
        </div>

      </div>

      <div class="col-md-8">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">How Penalty Transfers Work</h3></div>
          <div class="box-body">
            <p>When a passenger cancels or is a no-show, the penalty fee is automatically transferred to the affected driver's wallet.</p>
            <ul>
              <li>Called automatically by <code>ProportionalCancellation</code> and <code>NoShowVerification</code></li>
              <li>Uses existing <code>addDriverWalletCredit()</code> function if available</li>
              <li>Falls back to direct DB insert to <code>driver_wallet</code> table</li>
              <li>All transfers are logged with full audit trail</li>
              <li>Admin can reverse any transfer with a reason</li>
            </ul>
            <h5>Transfer Flow:</h5>
            <ol>
              <li>Passenger wallet is charged (deducted)</li>
              <li>Transfer logged as <code>Pending</code></li>
              <li>Driver wallet is credited</li>
              <li>Transfer status updated to <code>Completed</code></li>
            </ol>
            <h5>Available Actions:</h5>
            <code>transfer | reverse | getLog | getDriverLog | getStats</code>
          </div>
        </div>

        <?php if ($result): ?>
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">API Response</h3></div>
          <div class="box-body"><pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></div>
        </div>
        <?php endif; ?>

        <!-- Driver Log -->
        <?php if (!empty($driverLog['logs'])): ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Transfer Log – Driver #1</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-sm table-bordered">
              <thead><tr><th>#</th><th>Type</th><th>Amount</th><th>Transfer To</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($driverLog['logs'] as $log): ?>
                <tr>
                  <td><?= (int)$log['iPenaltyLogId'] ?></td>
                  <td><?= htmlspecialchars($log['ePenaltyType']) ?></td>
                  <td>R$ <?= number_format((float)$log['fPenaltyAmount'], 2, ',', '.') ?></td>
                  <td><?= htmlspecialchars($log['eTransferTo']) ?></td>
                  <td>
                    <?php $c=['Completed'=>'success','Pending'=>'warning','Failed'=>'danger','Reversed'=>'info'][$log['eStatus']]??'default'; ?>
                    <span class="label label-<?= $c ?>"><?= htmlspecialchars($log['eStatus']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($log['dCreatedAt'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Transfer Log – Driver #1</h3></div>
          <div class="box-body"><p class="text-muted">No transfer logs found for Driver #1 yet.</p></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
<?php include('../../footer.php'); ?>
