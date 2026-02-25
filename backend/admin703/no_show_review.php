<?php
/**
 * ADMIN: No-Show Incident Review
 * Admin reviews GPS evidence and approves/rejects no-show claims.
 */

$script = 'noShow';
require_once('../common.php');

if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit; }

$adminId   = (int)$_SESSION['admin_id'];
$noShowId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success   = $error = '';

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && SITE_TYPE !== 'Demo') {
    $action    = $_POST['action']    ?? '';
    $noShowId  = (int)($_POST['iNoShowId']  ?? 0);
    $adminNote = strip_tags(trim($_POST['vAdminNote'] ?? ''));
    $now       = date('Y-m-d H:i:s');

    require_once dirname(__DIR__) . '/features/NoShowVerification.php';
    $ns = new NoShowVerification($obj, $tconfig);

    if ($action === 'approve') {
        $result = json_decode($ns->handleRequest([
            'noshow_action' => 'adminApprove',
            'iNoShowId'     => $noShowId,
            'iAdminId'      => $adminId,
            'vAdminNote'    => $adminNote,
        ]), true);
        $success = $result['message'] ?? 'Approved.';
    } elseif ($action === 'reject') {
        $result = json_decode($ns->handleRequest([
            'noshow_action' => 'adminReject',
            'iNoShowId'     => $noShowId,
            'iAdminId'      => $adminId,
            'vAdminNote'    => $adminNote,
        ]), true);
        $success = $result['message'] ?? 'Rejected.';
    }
}

// Fetch all pending incidents
$incidents = $obj->MySQLSelect(
    "SELECT nsi.*,
            CONCAT(rd.vName,' ',rd.vLastName) AS vDriverName, rd.vMobileNo AS vDriverPhone,
            r.vName AS vPassengerName, r.vMobileNo AS vPassengerPhone
     FROM no_show_incidents nsi
     LEFT JOIN register_driver rd ON rd.iDriverId=nsi.iDriverId
     LEFT JOIN register r ON r.iUserId=nsi.iUserId
     ORDER BY nsi.eStatus='PendingReview' DESC, nsi.dCreatedAt DESC
     LIMIT 100"
);

include('../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>No-Show Incident Review <small>GPS-verified passenger absence</small></h1>
    <ol class="breadcrumb">
      <li><a href="admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">No-Show Review</li>
    </ol>
  </section>
  <section class="content">
    <?php if ($success): ?><div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger  alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="box box-warning">
      <div class="box-header with-border"><h3 class="box-title">Incidents</h3></div>
      <div class="box-body table-responsive">
        <table class="table table-bordered table-hover" id="noShowTable">
          <thead>
            <tr>
              <th>#</th><th>Driver</th><th>Passenger</th><th>Distance from Pickup</th>
              <th>Wait (min)</th><th>No-Show Fee</th><th>Status</th><th>Date</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($incidents as $inc): ?>
            <tr class="<?= $inc['eStatus'] === 'PendingReview' ? 'warning' : '' ?>">
              <td><?= (int)$inc['iNoShowId'] ?></td>
              <td><?= htmlspecialchars($inc['vDriverName'] ?? '') ?><br>
                  <small><?= htmlspecialchars($inc['vDriverPhone'] ?? '') ?></small></td>
              <td><?= htmlspecialchars($inc['vPassengerName'] ?? '') ?><br>
                  <small><?= htmlspecialchars($inc['vPassengerPhone'] ?? '') ?></small></td>
              <td><?= number_format((float)$inc['fDistanceFromPickup']) ?> m</td>
              <td><?= (int)$inc['iWaitMinutes'] ?></td>
              <td>R$ <?= number_format((float)$inc['fNoShowFee'], 2, ',', '.') ?></td>
              <td>
                <?php $sc = ['PendingReview'=>'warning','AdminApproved'=>'success','AdminRejected'=>'danger','FeeCharged'=>'success','Waived'=>'default'][$inc['eStatus']] ?? 'default'; ?>
                <span class="label label-<?= $sc ?>"><?= htmlspecialchars($inc['eStatus']) ?></span>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($inc['dCreatedAt'])) ?></td>
              <td>
                <?php if ($inc['eStatus'] === 'PendingReview'): ?>
                <button class="btn btn-xs btn-success" onclick="openReview(<?= (int)$inc['iNoShowId'] ?>, 'approve')">
                  <i class="fa fa-check"></i> Approve
                </button>
                <button class="btn btn-xs btn-danger" onclick="openReview(<?= (int)$inc['iNoShowId'] ?>, 'reject')">
                  <i class="fa fa-times"></i> Reject
                </button>
                <?php else: ?>
                <span class="text-muted">Reviewed</span>
                <?php endif; ?>
                <?php if (!empty($inc['vGpsProofJson'])): ?>
                <button class="btn btn-xs btn-info" onclick="viewGps(<?= (int)$inc['iNoShowId'] ?>)">
                  <i class="fa fa-map-marker"></i> GPS
                </button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Review No-Show Incident</h4></div>
        <div class="modal-body">
          <input type="hidden" name="iNoShowId" id="modalNoShowId">
          <input type="hidden" name="action"    id="modalAction">
          <div class="form-group">
            <label>Admin Note</label>
            <textarea name="vAdminNote" class="form-control" rows="3" placeholder="Reason for decision..."></textarea>
          </div>
          <p id="modalWarning" class="text-danger" style="display:none">
            <i class="fa fa-exclamation-triangle"></i> Approving will charge the no-show fee to the passenger and credit the driver.
          </p>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Submit</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openReview(id, action) {
    $('#modalNoShowId').val(id);
    $('#modalAction').val(action);
    $('#modalSubmitBtn').removeClass('btn-success btn-danger').addClass(action === 'approve' ? 'btn-success' : 'btn-danger')
        .text(action === 'approve' ? 'Approve & Charge' : 'Reject');
    $('#modalWarning').toggle(action === 'approve');
    $('#reviewModal').modal('show');
}
function viewGps(id) {
    alert('GPS data for incident #' + id + ' – Implement GPS map viewer here.');
}
$(document).ready(function() {
    $('#noShowTable').DataTable({ order: [[6, 'asc']], pageLength: 25 });
});
</script>
<?php include('../footer.php'); ?>
