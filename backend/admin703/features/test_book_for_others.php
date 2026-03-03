<?php
/**
 * ADMIN TEST: Book for Others Feature
 * Create and view third-party bookings
 */
$script = 'bookForOthers';
require_once('../../common.php');
if (!isset($_SESSION['admin_id'])) { header("Location: ../../login.php"); exit; }

$adminId = (int)$_SESSION['admin_id'];
$result  = null;

require_once dirname(__DIR__, 2) . '/features/BookForOthers.php';
$bfo = new BookForOthers($obj, $tconfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['book_others_action'] ?? 'create';
    $result = json_decode($bfo->handleRequest(array_merge($_POST, ['book_others_action' => $action])), true);
}

// Fetch recent bookings for user ID 1 as a demo
$demoBookings = json_decode($bfo->handleRequest(['book_others_action' => 'getMyBookings', 'iUserId' => 1, 'iPage' => 1]), true);

include('../../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>👥 Book for Others <small>Test – third-party ride booking</small></h1>
    <ol class="breadcrumb">
      <li><a href="../admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="../dashboard.php">Dashboard</a></li>
      <li class="active">Book for Others</li>
    </ol>
  </section>
  <section class="content">

    <?php if ($result): ?>
    <div class="alert alert-<?= ($result['status'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <button class="close" data-dismiss="alert">&times;</button>
      <strong><?= htmlspecialchars($result['status'] ?? '') ?>:</strong>
      <?= htmlspecialchars($result['message'] ?? json_encode($result)) ?>
    </div>
    <?php endif; ?>

    <div class="row">
      <!-- Create Booking Form -->
      <div class="col-md-5">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Create Booking for Someone Else</h3></div>
          <form method="POST">
            <input type="hidden" name="book_others_action" value="create">
            <div class="box-body">
              <div class="form-group">
                <label>Booking User ID (passenger who pays)</label>
                <input type="number" name="iUserId" class="form-control" value="1" required>
              </div>
              <div class="form-group">
                <label>Request ID (trip request)</label>
                <input type="number" name="iRequestId" class="form-control" value="1">
              </div>
              <div class="form-group">
                <label>Beneficiary Name</label>
                <input type="text" name="vBeneficiaryName" class="form-control" value="João Silva" required>
              </div>
              <div class="form-group">
                <label>Beneficiary Phone</label>
                <input type="text" name="vBeneficiaryPhone" class="form-control" value="11999999999" required>
              </div>
              <div class="form-group">
                <label>Country Code</label>
                <input type="text" name="vBeneficiaryCountryCode" class="form-control" value="+55">
              </div>
              <div class="form-group">
                <label>Relationship</label>
                <select name="eRelationship" class="form-control">
                  <option value="Family">Family</option>
                  <option value="Friend">Friend</option>
                  <option value="Colleague">Colleague</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label>Notify Beneficiary by SMS?</label>
                <select name="eNotifyBeneficiary" class="form-control">
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Create Booking</button>
            </div>
          </form>
        </div>

        <!-- Get Bookings Form -->
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-list"></i> Get My Bookings</h3></div>
          <form method="POST">
            <input type="hidden" name="book_others_action" value="getMyBookings">
            <div class="box-body">
              <div class="form-group">
                <label>User ID</label>
                <input type="number" name="iUserId" class="form-control" value="1">
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-default"><i class="fa fa-search"></i> Get Bookings</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Results / Demo -->
      <div class="col-md-7">
        <?php if ($result && isset($result['bookings'])): ?>
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">API Response</h3></div>
          <div class="box-body"><pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></div>
        </div>
        <?php endif; ?>

        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">Feature Info – Book for Others</h3></div>
          <div class="box-body">
            <p>This feature allows a passenger to book a ride on behalf of a third party (beneficiary).</p>
            <ul>
              <li>Passenger pays for the ride as normal</li>
              <li>Beneficiary name and phone are saved</li>
              <li>Optional SMS notification to beneficiary when ride starts</li>
              <li>Driver sees beneficiary name, not the booker's name</li>
            </ul>
            <h5>Available Actions:</h5>
            <code>create | getMyBookings | getBookingDetail | cancel | updateBeneficiary</code>
            <hr>
            <h5>Raw JSON Result:</h5>
            <?php if ($result): ?>
            <pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            <?php elseif (isset($demoBookings['bookings'])): ?>
            <p><em>Sample bookings for User #1:</em></p>
            <pre><?= htmlspecialchars(json_encode($demoBookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            <?php else: ?>
            <p class="text-muted">Submit a form on the left to see results here.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>
<?php include('../../footer.php'); ?>
