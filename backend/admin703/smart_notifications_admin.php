<?php
/**
 * ADMIN: Smart Push Notifications Management
 * Create, schedule, manage recurring push notifications.
 */

$script = 'notifications';
require_once('../common.php');

if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit; }

$adminId = (int)$_SESSION['admin_id'];
$success = $error = '';
$action  = $_GET['act'] ?? '';

require_once dirname(__DIR__) . '/features/SmartNotifications.php';
$notifManager = new SmartNotifications($obj, $tconfig);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && SITE_TYPE !== 'Demo') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'create') {
        $result  = json_decode($notifManager->handleRequest(array_merge($_POST, [
            'notif_action'  => 'create',
            'iAdminId'      => $adminId,
        ])), true);
        $success = $result['status'] === 'success' ? 'Notification created! ID: ' . $result['iNotificationId'] : '';
        $error   = $result['status'] !== 'success' ? $result['message'] : '';
    }
}

if ($action === 'send' && isset($_GET['id'])) {
    $result = json_decode($notifManager->handleRequest([
        'notif_action'    => 'send',
        'iNotificationId' => (int)$_GET['id'],
    ]), true);
    $success = "Sent to {$result['sent']} recipients.";
}
if ($action === 'cancel' && isset($_GET['id'])) {
    $notifManager->handleRequest(['notif_action' => 'cancel', 'iNotificationId' => (int)$_GET['id']]);
    $success = 'Notification cancelled.';
}

// Fetch notifications
$notifications = json_decode($notifManager->handleRequest(['notif_action' => 'getList']), true)['notifications'] ?? [];

// Get franchise list for targeting
$franchises = $obj->MySQLSelect("SELECT iFranchiseId, vFranchiseName, vCity FROM franchises WHERE eStatus='Active' ORDER BY vCity");

include('../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Smart Push Notifications</h1>
    <ol class="breadcrumb">
      <li><a href="admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Notifications</li>
    </ol>
  </section>
  <section class="content">
    <?php if ($success): ?><div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger  alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="row">
      <!-- Create form -->
      <div class="col-md-5">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bell"></i> Create Notification</h3></div>
          <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="box-body">
              <div class="form-group">
                <label>Title *</label>
                <input type="text" name="vTitle" class="form-control" required maxlength="200">
              </div>
              <div class="form-group">
                <label>Message *</label>
                <textarea name="vBody" class="form-control" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label>Target Audience</label>
                <select name="eTargetType" class="form-control" id="targetType" onchange="toggleTargetFields()">
                  <option value="AllUsers">All Passengers</option>
                  <option value="AllDrivers">All Drivers</option>
                  <option value="Segment">All (Passengers + Drivers)</option>
                  <option value="Franchise">Specific Franchise / City</option>
                  <option value="SpecificUser">Specific User IDs</option>
                  <option value="SpecificDriver">Specific Driver IDs</option>
                </select>
              </div>
              <div class="form-group" id="franchiseField" style="display:none">
                <label>Franchise</label>
                <select name="iFranchiseId" class="form-control">
                  <option value="">-- Select --</option>
                  <?php foreach ($franchises as $f): ?>
                  <option value="<?= (int)$f['iFranchiseId'] ?>"><?= htmlspecialchars($f['vFranchiseName']) ?> (<?= htmlspecialchars($f['vCity']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" id="targetIdsField" style="display:none">
                <label>Target IDs (comma-separated)</label>
                <input type="text" name="vTargetIds" class="form-control" placeholder="e.g. 1,2,3">
              </div>
              <div class="form-group">
                <label>Schedule Type</label>
                <select name="eScheduleType" class="form-control" id="scheduleType" onchange="toggleSchedule()">
                  <option value="Immediate">Send Immediately</option>
                  <option value="Scheduled">Scheduled</option>
                  <option value="Recurring">Recurring</option>
                </select>
              </div>
              <div id="scheduledFields" style="display:none">
                <div class="form-group">
                  <label>Send Date/Time</label>
                  <input type="datetime-local" name="dScheduledAt" class="form-control">
                </div>
              </div>
              <div id="recurringFields" style="display:none">
                <div class="row">
                  <div class="col-xs-6">
                    <div class="form-group">
                      <label>Repeat Every</label>
                      <select name="eRepeatInterval" class="form-control">
                        <option value="Hourly">Hourly</option>
                        <option value="Daily" selected>Daily</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Monthly">Monthly</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-xs-6">
                    <div class="form-group">
                      <label>Max Times (0=unlimited)</label>
                      <input type="number" name="iRepeatCount" class="form-control" value="0" min="0">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Notification list -->
      <div class="col-md-7">
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Notifications</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-bordered table-sm" id="notifTable">
              <thead>
                <tr><th>#</th><th>Title</th><th>Target</th><th>Schedule</th><th>Sent</th><th>Status</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($notifications as $n): ?>
                <tr>
                  <td><?= (int)$n['iNotificationId'] ?></td>
                  <td><?= htmlspecialchars(mb_substr($n['vTitle'], 0, 30)) ?></td>
                  <td><small><?= htmlspecialchars($n['eTargetType']) ?></small></td>
                  <td><small><?= htmlspecialchars($n['eScheduleType']) ?>
                      <?= $n['eRepeatInterval'] !== 'None' ? "/ {$n['eRepeatInterval']}" : '' ?></small></td>
                  <td><?= (int)$n['iSentCount'] ?></td>
                  <td>
                    <?php $sc = ['Queued'=>'info','Sending'=>'warning','Sent'=>'success','Paused'=>'default','Cancelled'=>'danger','Failed'=>'danger'][$n['eStatus']] ?? 'default'; ?>
                    <span class="label label-<?= $sc ?>"><?= $n['eStatus'] ?></span>
                  </td>
                  <td>
                    <?php if (in_array($n['eStatus'], ['Queued','Paused','Sent'])): ?>
                    <a href="?act=send&id=<?= (int)$n['iNotificationId'] ?>" class="btn btn-xs btn-success" onclick="return confirm('Send now?')">
                      <i class="fa fa-send"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!in_array($n['eStatus'], ['Cancelled','Sent'])): ?>
                    <a href="?act=cancel&id=<?= (int)$n['iNotificationId'] ?>" class="btn btn-xs btn-danger" onclick="return confirm('Cancel?')">
                      <i class="fa fa-ban"></i>
                    </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<script>
function toggleTargetFields() {
    var t = $('#targetType').val();
    $('#franchiseField').toggle(t === 'Franchise');
    $('#targetIdsField').toggle(t === 'SpecificUser' || t === 'SpecificDriver');
}
function toggleSchedule() {
    var s = $('#scheduleType').val();
    $('#scheduledFields').toggle(s === 'Scheduled' || s === 'Recurring');
    $('#recurringFields').toggle(s === 'Recurring');
}
$(document).ready(function() {
    $('#notifTable').DataTable({ order: [[0,'desc']], pageLength: 20 });
    toggleTargetFields();
    toggleSchedule();
});
</script>
<?php include('../footer.php'); ?>
