<?php
/**
 * ADMIN: Lost & Found Ticket Management
 * Admin page to view and manage all lost & found tickets.
 */

$script = 'lostFound';
require_once('../common.php');

if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit; }

$adminId = (int)$_SESSION['admin_id'];
$success = $error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && SITE_TYPE !== 'Demo') {
    $ticketId  = (int)($_POST['iTicketId'] ?? 0);
    $newStatus = strip_tags($_POST['eStatus'] ?? '');
    $adminNote = strip_tags(trim($_POST['vAdminNote'] ?? ''));

    require_once dirname(__DIR__) . '/features/LostAndFound.php';
    $laf    = new LostAndFound($obj, $tconfig);
    $result = json_decode($laf->handleRequest([
        'laf_action'  => 'updateStatus',
        'iTicketId'   => $ticketId,
        'iAdminId'    => $adminId,
        'eStatus'     => $newStatus,
        'vAdminNote'  => $adminNote,
    ]), true);
    $success = $result['message'] ?? 'Status updated.';
}

$filterStatus = $_GET['status'] ?? '';
$sWhere = $filterStatus ? "AND lft.eStatus='" . addslashes($filterStatus) . "'" : '';

$tickets = $obj->MySQLSelect(
    "SELECT lft.*,
            r.vName AS vPassengerName, r.vMobileNo AS vPassengerPhone,
            CONCAT(rd.vName,' ',rd.vLastName) AS vDriverName,
            (SELECT COUNT(*) FROM lost_found_messages lfm WHERE lfm.iTicketId=lft.iTicketId) AS iMsgCount
     FROM lost_found_tickets lft
     LEFT JOIN register r   ON r.iUserId=lft.iUserId
     LEFT JOIN register_driver rd ON rd.iDriverId=lft.iDriverId
     WHERE 1=1 $sWhere
     ORDER BY lft.eStatus='Open' DESC, lft.dUpdatedAt DESC
     LIMIT 200"
);

include('../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Lost &amp; Found <small>Manage customer reports</small></h1>
    <ol class="breadcrumb">
      <li><a href="admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Lost &amp; Found</li>
    </ol>
  </section>
  <section class="content">
    <?php if ($success): ?><div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- Filter -->
    <div class="box box-default">
      <div class="box-body">
        <strong>Filter by status:</strong>
        <?php $statuses = ['','Open','InProgress','ItemFound','ItemReturned','ReturnTripCreated','Closed'];
              $labels   = ['All','Open','In Progress','Item Found','Returned','Return Trip','Closed']; ?>
        <?php foreach ($statuses as $i => $s): ?>
          <a href="?status=<?= $s ?>" class="btn btn-sm <?= $filterStatus === $s ? 'btn-primary' : 'btn-default' ?>">
            <?= $labels[$i] ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">Tickets (<?= count($tickets) ?>)</h3></div>
      <div class="box-body table-responsive">
        <table class="table table-bordered table-hover" id="lafTable">
          <thead>
            <tr>
              <th>#</th><th>Passenger</th><th>Driver</th><th>Trip</th>
              <th>Item</th><th>Messages</th><th>Status</th><th>Date</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tickets as $t): ?>
            <tr>
              <td><?= (int)$t['iTicketId'] ?></td>
              <td><?= htmlspecialchars($t['vPassengerName'] ?? '') ?><br>
                  <small><?= htmlspecialchars($t['vPassengerPhone'] ?? '') ?></small></td>
              <td><?= htmlspecialchars($t['vDriverName'] ?? '') ?></td>
              <td>#<?= (int)$t['iTripId'] ?></td>
              <td>
                <strong><?= htmlspecialchars($t['vItemCategory'] ?? 'Unknown') ?></strong><br>
                <small><?= htmlspecialchars(mb_substr($t['vItemDescription'] ?? '', 0, 50)) ?>...</small>
              </td>
              <td><span class="badge bg-blue"><?= (int)$t['iMsgCount'] ?></span></td>
              <td>
                <?php $sc = ['Open'=>'warning','InProgress'=>'info','ItemFound'=>'primary','ItemReturned'=>'success','Closed'=>'default','ReturnTripCreated'=>'success'][$t['eStatus']] ?? 'default'; ?>
                <span class="label label-<?= $sc ?>"><?= htmlspecialchars($t['eStatus']) ?></span>
                <?php if ($t['eReturnTripCreated'] === 'Yes'): ?>
                  <span class="label label-info">Return Trip</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($t['dCreatedAt'])) ?></td>
              <td>
                <button class="btn btn-xs btn-info" onclick="viewMessages(<?= (int)$t['iTicketId'] ?>)">
                  <i class="fa fa-comments"></i> Chat
                </button>
                <button class="btn btn-xs btn-warning" onclick="updateStatus(<?= (int)$t['iTicketId'] ?>, '<?= $t['eStatus'] ?>')">
                  <i class="fa fa-edit"></i> Update
                </button>
                <?php if ($t['eReturnTripCreated'] !== 'Yes' && in_array($t['eStatus'], ['ItemFound','InProgress'])): ?>
                <button class="btn btn-xs btn-success" onclick="createReturnTrip(<?= (int)$t['iTicketId'] ?>, <?= (int)$t['iDriverId'] ?>)">
                  <i class="fa fa-car"></i> Return Trip
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

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4>Update Ticket Status</h4></div>
        <div class="modal-body">
          <input type="hidden" name="iTicketId" id="sTicketId">
          <div class="form-group">
            <label>New Status</label>
            <select name="eStatus" class="form-control">
              <option value="Open">Open</option>
              <option value="InProgress">In Progress</option>
              <option value="ItemFound">Item Found</option>
              <option value="ItemReturned">Item Returned</option>
              <option value="Closed">Closed</option>
            </select>
          </div>
          <div class="form-group">
            <label>Note</label>
            <textarea name="vAdminNote" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateStatus(ticketId, currentStatus) {
    $('#sTicketId').val(ticketId);
    $('select[name=eStatus]').val(currentStatus);
    $('#statusModal').modal('show');
}
function viewMessages(ticketId) {
    window.open('lost_found_chat.php?ticket=' + ticketId, '_blank', 'width=700,height=600');
}
function createReturnTrip(ticketId, driverId) {
    var fare = prompt('Enter the return trip fare (R$):');
    if (!fare || isNaN(parseFloat(fare))) return;
    var pickup = prompt('Enter pickup coordinates (lat,lng):');
    var dropoff = prompt('Enter dropoff coordinates (lat,lng):');
    if (!pickup || !dropoff) return;
    // Call API
    $.post('../features/features_webservice.php', {
        type: 'lostFound', laf_action: 'createReturnTrip',
        iTicketId: ticketId, fReturnTripFare: fare,
        vPickupLatLng: pickup, vDropLatLng: dropoff,
        vAuthToken: 'admin_session'
    }, function(r) {
        var res = typeof r === 'string' ? JSON.parse(r) : r;
        alert(res.status === 'success' ? 'Return trip created! Request ID: ' + res.iReturnTripRequestId : 'Error: ' + res.message);
        if (res.status === 'success') location.reload();
    });
}
$(document).ready(function() {
    $('#lafTable').DataTable({ order: [[7, 'desc']], pageLength: 25 });
});
</script>
<?php include('../footer.php'); ?>
