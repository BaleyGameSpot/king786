<?php
/**
 * ADMIN: Franchise Management – Add / Edit Franchise
 */

$script = 'franchise';
require_once('../common.php');

if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit; }

$franchiseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$franchise   = [];
$isEdit      = false;

if ($franchiseId) {
    $rows = $obj->MySQLSelect("SELECT * FROM franchises WHERE iFranchiseId=$franchiseId LIMIT 1");
    if (!empty($rows)) { $franchise = $rows[0]; $isEdit = true; }
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && SITE_TYPE !== 'Demo') {
    $name         = strip_tags(trim($_POST['vFranchiseName'] ?? ''));
    $city         = strip_tags(trim($_POST['vCity']          ?? ''));
    $state        = strip_tags(trim($_POST['vState']         ?? ''));
    $country      = strip_tags(trim($_POST['vCountry']       ?? 'Brazil'));
    $masterShare  = (float)($_POST['fMasterSharePercent']    ?? 15);
    $franchShare  = (float)($_POST['fRevenueSharePercent']   ?? 10);
    $driverShare  = (float)($_POST['fDriverSharePercent']    ?? 75);
    $pagarmeId    = strip_tags(trim($_POST['vPagarmeRecipientId'] ?? ''));
    $efiId        = strip_tags(trim($_POST['vEfiClientId']        ?? ''));
    $efiSecret    = strip_tags(trim($_POST['vEfiClientSecret']    ?? ''));

    if (!$name || !$city) {
        $error = 'Franchise Name and City are required.';
    } elseif (abs($masterShare + $franchShare + $driverShare - 100) > 0.01) {
        $error = 'Revenue shares must sum to 100%. Current: ' . ($masterShare + $franchShare + $driverShare) . '%';
    } else {
        $nameEsc    = addslashes($name);
        $cityEsc    = addslashes($city);
        $stateEsc   = addslashes($state);
        $countryEsc = addslashes($country);
        $pmEsc      = addslashes($pagarmeId);
        $efiIdEsc   = addslashes($efiId);
        $efiSEsc    = addslashes($efiSecret);
        $now        = date('Y-m-d H:i:s');

        if ($isEdit) {
            $obj->sql_query(
                "UPDATE franchises SET
                    vFranchiseName='$nameEsc', vCity='$cityEsc', vState='$stateEsc', vCountry='$countryEsc',
                    fMasterSharePercent=$masterShare, fRevenueSharePercent=$franchShare, fDriverSharePercent=$driverShare,
                    vPagarmeRecipientId='$pmEsc', vEfiClientId='$efiIdEsc', vEfiClientSecret='$efiSEsc'
                 WHERE iFranchiseId=$franchiseId"
            );
            $success = "Franchise updated successfully.";
        } else {
            $obj->sql_query(
                "INSERT INTO franchises
                    (vFranchiseName, vCity, vState, vCountry, fMasterSharePercent,
                     fRevenueSharePercent, fDriverSharePercent, eStatus,
                     vPagarmeRecipientId, vEfiClientId, vEfiClientSecret, dCreatedAt)
                 VALUES
                    ('$nameEsc', '$cityEsc', '$stateEsc', '$countryEsc', $masterShare,
                     $franchShare, $driverShare, 'Active',
                     '$pmEsc', '$efiIdEsc', '$efiSEsc', '$now')"
            );
            $newId   = $obj->MySQLLastInsertID();
            $success = "Franchise created! ID: $newId";
            header("Location: franchise_list.php?success=" . urlencode($success));
            exit;
        }
    }
}

include('../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $isEdit ? 'Edit Franchise' : 'Add Franchise' ?></h1>
    <ol class="breadcrumb">
      <li><a href="admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="franchise_list.php">Franchises</a></li>
      <li class="active"><?= $isEdit ? 'Edit' : 'Add' ?></li>
    </ol>
  </section>
  <section class="content">
    <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="row">
      <div class="col-md-8">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title">Franchise Details</h3></div>
          <form method="POST">
            <div class="box-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Franchise Name *</label>
                    <input type="text" name="vFranchiseName" class="form-control"
                           value="<?= htmlspecialchars($franchise['vFranchiseName'] ?? '') ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="vCity" class="form-control"
                           value="<?= htmlspecialchars($franchise['vCity'] ?? '') ?>" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>State</label>
                    <input type="text" name="vState" class="form-control"
                           value="<?= htmlspecialchars($franchise['vState'] ?? '') ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="vCountry" class="form-control"
                           value="<?= htmlspecialchars($franchise['vCountry'] ?? 'Brazil') ?>">
                  </div>
                </div>
              </div>

              <h4>Revenue Share Configuration <small>(must sum to 100%)</small></h4>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Master Platform %</label>
                    <input type="number" name="fMasterSharePercent" class="form-control share-input"
                           step="0.01" min="0" max="100"
                           value="<?= htmlspecialchars($franchise['fMasterSharePercent'] ?? '15') ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Franchisee %</label>
                    <input type="number" name="fRevenueSharePercent" class="form-control share-input"
                           step="0.01" min="0" max="100"
                           value="<?= htmlspecialchars($franchise['fRevenueSharePercent'] ?? '10') ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Driver %</label>
                    <input type="number" name="fDriverSharePercent" class="form-control share-input"
                           step="0.01" min="0" max="100"
                           value="<?= htmlspecialchars($franchise['fDriverSharePercent'] ?? '75') ?>">
                  </div>
                </div>
              </div>
              <div id="shareSum" class="alert alert-info">
                Total: <strong id="shareSumVal">100</strong>% (must be exactly 100%)
              </div>

              <h4>Payment Integration</h4>
              <div class="form-group">
                <label>Pagar.me Recipient ID</label>
                <input type="text" name="vPagarmeRecipientId" class="form-control" placeholder="rp_..."
                       value="<?= htmlspecialchars($franchise['vPagarmeRecipientId'] ?? '') ?>">
                <small class="help-block">Create via Pagar.me Dashboard or API.</small>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>EfiPay Client ID</label>
                    <input type="text" name="vEfiClientId" class="form-control"
                           value="<?= htmlspecialchars($franchise['vEfiClientId'] ?? '') ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>EfiPay Client Secret</label>
                    <input type="password" name="vEfiClientSecret" class="form-control"
                           placeholder="<?= $isEdit ? '(unchanged)' : '' ?>">
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>
                <?= $isEdit ? 'Update' : 'Create' ?> Franchise</button>
              <a href="franchise_list.php" class="btn btn-default">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
function updateShareSum() {
    var total = 0;
    $('.share-input').each(function() { total += parseFloat($(this).val()) || 0; });
    var $sum = $('#shareSumVal');
    $sum.text(total.toFixed(2));
    var $alert = $('#shareSum');
    $alert.removeClass('alert-info alert-success alert-danger');
    $alert.addClass(Math.abs(total - 100) < 0.01 ? 'alert-success' : 'alert-danger');
}
$('.share-input').on('input', updateShareSum);
updateShareSum();
</script>
<?php include('../footer.php'); ?>
