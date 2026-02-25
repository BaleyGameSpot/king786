<?php
/**
 * ADMIN: Franchise Management – Franchise List
 * Admin panel page to view and manage all franchises.
 */

$script = 'franchise';
require_once('../common.php');

// Auth check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch all franchises
$franchises = $obj->MySQLSelect(
    "SELECT f.*,
            (SELECT COUNT(*) FROM franchise_driver_map fdm WHERE fdm.iFranchiseId=f.iFranchiseId AND fdm.eStatus='Active') AS iDriverCount,
            (SELECT COUNT(*) FROM franchise_users fu WHERE fu.iFranchiseId=f.iFranchiseId AND fu.eStatus='Active') AS iAdminCount
     FROM franchises f
     ORDER BY f.vCity ASC"
);

$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

include('../header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Franchise Management <small>City-based territories</small></h1>
    <ol class="breadcrumb">
      <li><a href="admin.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Franchises</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">All Franchises</h3>
            <div class="box-tools pull-right">
              <a href="franchise_add.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Franchise</a>
            </div>
          </div>
          <div class="box-body">
            <table class="table table-bordered table-striped" id="franchiseTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>City / State</th>
                  <th>Revenue Split</th>
                  <th>Drivers</th>
                  <th>Admins</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($franchises)): ?>
                  <?php foreach ($franchises as $f): ?>
                  <tr>
                    <td><?= (int)$f['iFranchiseId'] ?></td>
                    <td><strong><?= htmlspecialchars($f['vFranchiseName']) ?></strong></td>
                    <td><?= htmlspecialchars($f['vCity']) ?>, <?= htmlspecialchars($f['vState']) ?></td>
                    <td>
                      <small>
                        Master: <?= number_format($f['fMasterSharePercent'], 1) ?>%<br>
                        Franchisee: <?= number_format($f['fRevenueSharePercent'], 1) ?>%<br>
                        Driver: <?= number_format($f['fDriverSharePercent'], 1) ?>%
                      </small>
                    </td>
                    <td><span class="badge bg-blue"><?= (int)$f['iDriverCount'] ?></span></td>
                    <td><span class="badge bg-green"><?= (int)$f['iAdminCount'] ?></span></td>
                    <td>
                      <?php
                        $statusClass = ['Active'=>'success','Inactive'=>'default','Suspended'=>'danger'][$f['eStatus']] ?? 'default';
                      ?>
                      <span class="label label-<?= $statusClass ?>"><?= htmlspecialchars($f['eStatus']) ?></span>
                    </td>
                    <td>
                      <a href="franchise_detail.php?id=<?= (int)$f['iFranchiseId'] ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> View</a>
                      <a href="franchise_add.php?id=<?= (int)$f['iFranchiseId'] ?>" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                      <a href="franchise_drivers.php?id=<?= (int)$f['iFranchiseId'] ?>" class="btn btn-xs btn-default"><i class="fa fa-users"></i> Drivers</a>
                      <a href="franchise_billing.php?id=<?= (int)$f['iFranchiseId'] ?>" class="btn btn-xs btn-primary"><i class="fa fa-money"></i> Billing</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="8" class="text-center">No franchises found. <a href="franchise_add.php">Add the first one</a>.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats cards -->
    <div class="row">
      <?php
        $totalFranchises = count($franchises ?? []);
        $activeFranchises = count(array_filter($franchises ?? [], fn($f) => $f['eStatus'] === 'Active'));
        $totalDrivers = array_sum(array_column($franchises ?? [], 'iDriverCount'));
      ?>
      <div class="col-md-3 col-sm-6">
        <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-building"></i></span>
          <div class="info-box-content"><span class="info-box-text">Total Franchises</span>
            <span class="info-box-number"><?= $totalFranchises ?></span></div></div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
          <div class="info-box-content"><span class="info-box-text">Active</span>
            <span class="info-box-number"><?= $activeFranchises ?></span></div></div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-car"></i></span>
          <div class="info-box-content"><span class="info-box-text">Total Assigned Drivers</span>
            <span class="info-box-number"><?= $totalDrivers ?></span></div></div>
      </div>
    </div>
  </section>
</div>

<script>
$(document).ready(function() {
  $('#franchiseTable').DataTable({ "order": [[1, "asc"]], "pageLength": 25 });
});
</script>
<?php include('../footer.php'); ?>
