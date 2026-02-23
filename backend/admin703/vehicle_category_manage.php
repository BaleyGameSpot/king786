<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('../common.php');

$message = '';
$success = 0;

// Handle Add Category
if (isset($_POST['add_category'])) {
    $vCategoryName = isset($_POST['vCategoryName']) ? trim($_POST['vCategoryName']) : '';
    $vCategoryValue = isset($_POST['vCategoryValue']) ? trim($_POST['vCategoryValue']) : '';
    $eType = isset($_POST['eType']) ? $_POST['eType'] : 'Ride';
    $iDispOrder = isset($_POST['iDispOrder']) ? intval($_POST['iDispOrder']) : 1;
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Active';
    
    if (!empty($vCategoryName) && !empty($vCategoryValue)) {
        // Create table if not exists FIRST
        $createTableSql = "CREATE TABLE IF NOT EXISTS `vehicle_icon_types` (
            `iIconTypeId` int(11) NOT NULL AUTO_INCREMENT,
            `vIconName` varchar(255) NOT NULL,
            `vIconValue` varchar(100) NOT NULL,
            `eType` enum('Ride','Delivery','Both') DEFAULT 'Ride',
            `iDispOrder` int(11) DEFAULT 1,
            `eStatus` enum('Active','Inactive') DEFAULT 'Active',
            `dCreatedDate` datetime DEFAULT NULL,
            PRIMARY KEY (`iIconTypeId`),
            UNIQUE KEY `vIconValue` (`vIconValue`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        try {
            $obj->sql_query($createTableSql);
        } catch (Exception $e) {
            $message = "Error creating table: " . $e->getMessage();
            $success = 2;
        }
        
        // Check if already exists
        $checkSql = "SELECT COUNT(*) as count FROM vehicle_icon_types WHERE vIconValue = '" . addslashes($vCategoryValue) . "'";
        $checkResult = $obj->MySQLSelect($checkSql);
        
        if ($checkResult && $checkResult[0]['count'] == 0) {
            // Use direct SQL instead of MySQLQueryPerform
            $insertSql = "INSERT INTO vehicle_icon_types 
                (vIconName, vIconValue, eType, iDispOrder, eStatus, dCreatedDate) 
                VALUES (
                    '" . addslashes($vCategoryName) . "',
                    '" . addslashes($vCategoryValue) . "',
                    '" . addslashes($eType) . "',
                    " . intval($iDispOrder) . ",
                    '" . addslashes($eStatus) . "',
                    NOW()
                )";
            
            try {
                $result = $obj->sql_query($insertSql);
                if ($result) {
                    $message = "Vehicle category added successfully!";
                    $success = 1;
                } else {
                    $message = "Error adding category. Please try again.";
                    $success = 2;
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $success = 2;
            }
        } else {
            $message = "Category value already exists!";
            $success = 2;
        }
    } else {
        $message = "Please fill all required fields!";
        $success = 2;
    }
}

// Handle Update Status
if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT eStatus FROM vehicle_icon_types WHERE iIconTypeId = $id";
    $result = $obj->MySQLSelect($sql);
    
    if ($result && count($result) > 0) {
        $newStatus = ($result[0]['eStatus'] == 'Active') ? 'Inactive' : 'Active';
        $updateSql = "UPDATE vehicle_icon_types SET eStatus = '" . addslashes($newStatus) . "' WHERE iIconTypeId = $id";
        $obj->sql_query($updateSql);
        $message = "Status updated successfully!";
        $success = 1;
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $deleteSql = "DELETE FROM vehicle_icon_types WHERE iIconTypeId = $id";
    $obj->sql_query($deleteSql);
    $message = "Category deleted successfully!";
    $success = 1;
}

// Get all categories
$categoriesSql = "SELECT * FROM vehicle_icon_types ORDER BY iDispOrder ASC, iIconTypeId DESC";
$categories = $obj->MySQLSelect($categoriesSql);
if (!$categories) {
    $categories = array();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Manage Vehicle Categories</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style>
        .category-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }
        .category-card h4 {
            margin-top: 0;
            color: #333;
        }
        .badge-active {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .badge-inactive {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="padTop53">
<div id="wrap">
    <?php
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Manage Vehicle Categories / Icon Types</h2>
                </div>
            </div>
            <hr/>
            
            <?php if ($message != '') { ?>
                <div class="alert alert-<?= ($success == 1) ? 'success' : 'danger' ?> alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?= $message ?>
                </div>
            <?php } ?>
            
            <div class="row">
                <!-- Add New Category Form -->
                <div class="col-lg-5">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4>Add New Vehicle Category</h4>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label>Category Name <span class="text-danger">*</span></label>
                                    <input type="text" name="vCategoryName" class="form-control" 
                                           placeholder="e.g., Tuk Tuk, Electric Vehicle" required>
                                    <small class="text-muted">Display name for users</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Category Value <span class="text-danger">*</span></label>
                                    <input type="text" name="vCategoryValue" class="form-control" 
                                           placeholder="e.g., TukTuk, Electric" required>
                                    <small class="text-muted">Unique identifier (no spaces, use camelCase)</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="eType" class="form-control">
                                        <option value="Ride">Ride Only</option>
                                        <option value="Delivery">Delivery Only</option>
                                        <option value="Both">Both Ride & Delivery</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Display Order</label>
                                    <input type="number" name="iDispOrder" class="form-control" 
                                           value="<?= count($categories) + 1 ?>" min="1">
                                </div>
                                
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="eStatus" class="form-control">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="add_category" class="btn btn-primary btn-block">
                                    <i class="fa fa-plus"></i> Add Category
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Existing Categories List -->
                <div class="col-lg-7">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>Existing Categories (<?= count($categories) ?>)</h4>
                        </div>
                        <div class="panel-body" style="max-height: 600px; overflow-y: auto;">
                            <?php if (count($categories) > 0) { ?>
                                <?php foreach ($categories as $cat) { ?>
                                    <div class="category-card">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h4><?= htmlspecialchars($cat['vIconName']) ?></h4>
                                                <p>
                                                    <strong>Value:</strong> <code><?= htmlspecialchars($cat['vIconValue']) ?></code><br>
                                                    <strong>Type:</strong> <?= $cat['eType'] ?><br>
                                                    <strong>Order:</strong> <?= $cat['iDispOrder'] ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <span class="badge-<?= strtolower($cat['eStatus']) ?>">
                                                    <?= $cat['eStatus'] ?>
                                                </span>
                                                <br><br>
                                                <a href="?action=toggle_status&id=<?= $cat['iIconTypeId'] ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   onclick="return confirm('Change status?')">
                                                    <i class="fa fa-power-off"></i> Toggle
                                                </a>
                                                <a href="?action=delete&id=<?= $cat['iIconTypeId'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="alert alert-info">
                                    No categories found. Add your first category using the form.
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>

<script>
$(document).ready(function() {
    // Auto-generate category value from name
    $('input[name="vCategoryName"]').on('keyup', function() {
        var name = $(this).val();
        var value = name.replace(/\s+/g, ''); // Remove spaces
        $('input[name="vCategoryValue"]').val(value);
    });
});
</script>
</body>
</html>