<?php
/**
 * ADMIN TEST: Facial Recognition Feature
 * Upload reference photo and test verification
 */
$script = 'facialRecognition';
require_once('../../common.php');
if (!isset($_SESSION['admin_id'])) { header("Location: ../../login.php"); exit; }

$result = null;

require_once dirname(__DIR__, 2) . '/features/FacialRecognition.php';
$fr = new FacialRecognition($obj, $tconfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['face_action'] ?? 'getSettings';
    $reqData = array_merge($_POST, ['face_action' => $action]);
    // For file upload actions, pass $_FILES too
    if (isset($_FILES['liveImage']) || isset($_FILES['referenceImage'])) {
        // Let the class pick up from $_FILES directly
    }
    $result = json_decode($fr->handleRequest($reqData), true);
}

// Get settings
$settings = json_decode($fr->handleRequest(['face_action' => 'getSettings']), true);

// Get verification history for driver 1
$history = json_decode($fr->handleRequest(['face_action' => 'getHistory', 'iDriverId' => 1, 'iPage' => 1]), true);

include('../../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>🤖 Facial Recognition <small>AI-powered identity verification test</small></h1>
    <ol class="breadcrumb">
      <li><a href="../admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="../dashboard.php">Dashboard</a></li>
      <li class="active">Facial Recognition</li>
    </ol>
  </section>
  <section class="content">

    <?php if ($result): ?>
    <div class="alert alert-<?= ($result['status'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible">
      <button class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($result['message'] ?? json_encode($result)) ?>
    </div>
    <?php endif; ?>

    <!-- Settings Info -->
    <?php if (!empty($settings['settings'])): ?>
    <div class="alert alert-info">
      <strong>Active Provider:</strong> <?= htmlspecialchars($settings['settings']['provider'] ?? 'Not configured') ?> &nbsp;|&nbsp;
      <strong>Similarity Threshold:</strong> <?= htmlspecialchars($settings['settings']['similarity_threshold'] ?? '90') ?>% &nbsp;|&nbsp;
      <strong>Required Events:</strong> <?= htmlspecialchars($settings['settings']['required_events'] ?? 'Login') ?>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
      <strong>Setup Required:</strong> Configure <code>FACE_PROVIDER</code>, <code>FACE_AWS_KEY</code>, <code>FACE_AWS_SECRET</code> in <code>app_configuration_file.php</code>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-5">

        <!-- Upload Reference Photo -->
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-upload"></i> Upload Reference Photo (Driver)</h3></div>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="face_action" value="uploadReference">
            <div class="box-body">
              <div class="form-group">
                <label>Driver ID</label>
                <input type="number" name="iDriverId" class="form-control" value="1" required>
              </div>
              <div class="form-group">
                <label>Reference Photo (ID / profile photo)</label>
                <input type="file" name="referenceImage" class="form-control" accept="image/*">
                <small class="help-block">JPG/PNG, max 5MB. This photo is stored as the trusted reference.</small>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload Reference</button>
            </div>
          </form>
        </div>

        <!-- Verify Live Photo -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-camera"></i> Verify Live Photo</h3></div>
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="face_action" value="verify">
            <div class="box-body">
              <div class="form-group">
                <label>Driver ID</label>
                <input type="number" name="iDriverId" class="form-control" value="1" required>
              </div>
              <div class="form-group">
                <label>Event Type</label>
                <select name="eEventType" class="form-control">
                  <option value="Login">Login</option>
                  <option value="StartShift">Start Shift</option>
                  <option value="AcceptRide">Accept Ride</option>
                  <option value="Periodic">Periodic Check</option>
                </select>
              </div>
              <div class="form-group">
                <label>Live Photo (selfie)</label>
                <input type="file" name="liveImage" class="form-control" accept="image/*">
                <small class="help-block">Take a live selfie to compare against reference.</small>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-warning"><i class="fa fa-check"></i> Verify Identity</button>
            </div>
          </form>
        </div>

      </div>

      <div class="col-md-7">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">How It Works</h3></div>
          <div class="box-body">
            <p>Facial recognition verifies that the driver using the app is the same person who registered.</p>
            <ul>
              <li><strong>Reference photo:</strong> Uploaded during driver onboarding (ID photo)</li>
              <li><strong>Live photo:</strong> Taken at login, shift start, or randomly during a ride</li>
              <li>AI compares both photos with a configurable similarity threshold (default: 90%)</li>
              <li>Supports <strong>AWS Rekognition</strong>, <strong>Face++</strong>, and <strong>Azure Face API</strong></li>
              <li>All verification events are logged for audit trail</li>
            </ul>
            <div class="alert alert-warning">
              <strong>Note:</strong> Actual face comparison requires cloud API keys to be configured. Without keys, the module returns a demo response.
            </div>
          </div>
        </div>

        <?php if ($result): ?>
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">API Response</h3></div>
          <div class="box-body"><pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></div>
        </div>
        <?php endif; ?>

        <!-- Verification History -->
        <?php if (!empty($history['logs'])): ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Verification History – Driver #1</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-sm table-bordered">
              <thead><tr><th>Event</th><th>Result</th><th>Score</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($history['logs'] as $log): ?>
                <tr>
                  <td><?= htmlspecialchars($log['eEventType']) ?></td>
                  <td>
                    <span class="label label-<?= $log['eResult'] === 'Match' ? 'success' : 'danger' ?>">
                      <?= htmlspecialchars($log['eResult']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($log['fSimilarityScore'] ?? '-') ?>%</td>
                  <td><?= htmlspecialchars($log['dCreatedAt'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
<?php include('../../footer.php'); ?>
