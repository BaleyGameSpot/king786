<?php

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

include_once '../common.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$baseUrl = $tconfig["tsite_url"] ?? '';

if (!$MODULES_OBJ->mapAPIreplacementAvailable()) {
    header("Location:" . ($tconfig["tsite_url_main_admin"] ?? ''));
    exit;
}

if (!$userObj->hasPermission('view-map-api-service-account')) {
    $userObj->redirect();
}

$success = $_REQUEST['success'] ?? 0;

// Helper function to safely get MongoDB ID
function getMongoId($mongoObject) {
    if (is_object($mongoObject)) {
        return (string) $mongoObject;
    } elseif (is_array($mongoObject) && isset($mongoObject['$oid'])) {
        return $mongoObject['$oid'];
    } elseif (is_string($mongoObject)) {
        return $mongoObject;
    }
    return '';
}

// Helper function to safely access nested array values
function safeArrayAccess($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

// ===== Import map API Settings data start
$fileName = "";
if (isset($_FILES['import_file_map_api']) && !empty($_FILES['import_file_map_api']['tmp_name'])) {
    $fileName = $_FILES['import_file_map_api']['tmp_name'];    
}

$site_type = (defined('SITE_TYPE') && SITE_TYPE == 'Demo') ? SITE_TYPE : "No Demo";

if ($fileName != '') {
    if (defined('SITE_TYPE') && SITE_TYPE == 'Demo') {
        header("Location:map_api_setting.php?success=2");
        exit;
    }
    
    $newServiceData = [];
    $fileData = file_get_contents($fileName);
    
    if ($fileData === false) {
        echo "<script>
                alert('Error reading file.');
                window.location = window.location.href;
              </script>";
        exit;
    }
    
    $fileDataDecode = json_decode($fileData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<script>
                alert('Invalid JSON file.');
                window.location = window.location.href;
              </script>";
        exit;
    }
    
    $DbName = defined('TSITE_DB') ? TSITE_DB : '';
    $TableName = "auth_master_accounts_places";
    $TableNameauthAccPlace = "auth_accounts_places";
    $Tableauthreportaccountsplaces = "auth_report_accounts_places";
    
    $serviceCount = isset($fileDataDecode['servicedata']) ? count($fileDataDecode['servicedata']) : 0;
    $usage_reportCount = isset($fileDataDecode['usage_report']) ? count($fileDataDecode['usage_report']) : 0;
    $auth_accounts_placesCount = isset($fileDataDecode['auth_accounts_places']) ? count($fileDataDecode['auth_accounts_places']) : 0;
    
    if ($serviceCount > 0) {
        $obj->deleteAllRecordsFromMongoDB($DbName, $TableName);
        $obj->deleteAllRecordsFromMongoDB($DbName, $TableNameauthAccPlace);
        $obj->deleteAllRecordsFromMongoDB($DbName, $Tableauthreportaccountsplaces);
        
        foreach ($fileDataDecode['servicedata'] as $key => $servicedata) {
            if (isset($servicedata['_id']) && is_array($servicedata['_id']) && isset($servicedata['_id']['$oid'])) {
                $servicedata['_id'] = new ObjectId($servicedata['_id']['$oid']);
            }
            $obj->insertRecordsToMongoDBWithDBName($DbName, $TableName, $servicedata);
        }
        
        if (isset($fileDataDecode['usage_report'])) {
            foreach ($fileDataDecode['usage_report'] as $key => $usage_reports) {
                if (isset($usage_reports['_id']) && is_array($usage_reports['_id']) && isset($usage_reports['_id']['$oid'])) {
                    $usage_reports['_id'] = new ObjectId($usage_reports['_id']['$oid']);
                }
                if (isset($usage_reports['vUsageDate']) && is_array($usage_reports['vUsageDate']) && isset($usage_reports['vUsageDate']['$date'])) {
                    $usage_reports['vUsageDate'] = new UTCDateTime($usage_reports['vUsageDate']['$date']);
                }
                $obj->insertRecordsToMongoDBWithDBName($DbName, $Tableauthreportaccountsplaces, $usage_reports);
            }
        }
        
        if (isset($fileDataDecode['auth_accounts_places'])) {
            foreach ($fileDataDecode['auth_accounts_places'] as $key => $auth_accounts_placesData) {
                if (isset($auth_accounts_placesData['_id']) && is_array($auth_accounts_placesData['_id']) && isset($auth_accounts_placesData['_id']['$oid'])) {
                    $auth_accounts_placesData['_id'] = new ObjectId($auth_accounts_placesData['_id']['$oid']);
                }
                $obj->insertRecordsToMongoDBWithDBName($DbName, $TableNameauthAccPlace, $auth_accounts_placesData);
            }
        }
    } else {
        echo "<script>
                alert('Please upload valid file content.');
                window.location = window.location.href;
              </script>";
    }
}
// ===== Import map API Settings data End

$script = 'map_api_setting';
$iCompanyId = $_REQUEST['iCompanyId'] ?? '';

//Start Sorting
$sortby = $_REQUEST['sortby'] ?? 3;
$order = $_REQUEST['order'] ?? '1';
$ord = ' ORDER BY rd.iDriverId DESC';
$orderByField = [];

if ($sortby == 1) {
    $orderByField['vServiceName'] = ($order == 0) ? -1 : 1;
}
if ($sortby == '2') {
    $orderByField['vServiceName'] = ($order == 0) ? -1 : 1;
}
if ($sortby == 3) {
    $orderByField['vUsageOrder'] = ($order == 0) ? -1 : 1;
}
if ($sortby == 6) {
    $orderByField['eStatus'] = ($order == 0) ? -1 : 1;
}
//End Sorting

// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = $_REQUEST['searchDate'] ?? "";
$eStatus = $_REQUEST['eStatus'] ?? "";
$action = $_REQUEST['action'] ?? '';
// End Search Parameters

$show_page = 1;
$DbName = defined('TSITE_DB') ? TSITE_DB : '';
$TableName = "auth_master_accounts_places";
$TableName_Accounts = "auth_accounts_places";

if ($eStatus != '' || ($keyword != '' && count($orderByField) > 0)) {
    $searchQuery = [];
    if ($keyword != '') {
        $searchQuery['vServiceName'] = $keyword;
    }
    if ($eStatus != '') {
        $searchQuery['eStatus'] = $eStatus;
    }
    if (!empty($orderByField)) {
        $data_drv = $obj->fetchAllRecordsFromMongoDBWithSortParams($DbName, $TableName, $searchQuery, $orderByField);
    } else {
        $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);
    }
} else {
    $less = 0;
    $data_drv = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName);
    
    if (is_array($data_drv) && count($data_drv) < 2) {
        if (isset($data_drv[0]) && 
            safeArrayAccess($data_drv[0], 'vServiceId') == '1' && 
            safeArrayAccess($data_drv[0], 'vServiceName') == 'OpenMap') {
            echo "Map API Setting is invalid, kindly setup in proper.";
            exit;
        }
    }
    
    if (is_array($data_drv) && count($data_drv) < 2) {
        $sid = '';
        $oid = '';
        foreach ($data_drv as $data_drv_value) {
            if (safeArrayAccess($data_drv_value, 'vServiceName') != 'OpenMap') {
                $sid = safeArrayAccess($data_drv_value, 'vServiceId');
                $oid = getMongoId(safeArrayAccess($data_drv_value, '_id'));
            }
        }
        $duration = $_REQUEST['duration'] ?? "";
        $less = 1;
        include "usage_report.php";
        exit;
    }
}

// Initialize variables for the template
$data_drv = $data_drv ?? [];
$activeServices = 0;
$activeServicesArray = [];
$activeServiesList = [];

if (!empty($data_drv) && is_array($data_drv)) {
    for ($j = 0; $j < count($data_drv); $j++) {
        if (safeArrayAccess($data_drv[$j], 'eStatus') == "Active") {
            $activeServices++;
            $activeServiesList[$j] = explode(",", safeArrayAccess($data_drv[$j], 'vActiveServices'));
        }
    }
    
    $searchQueryActive['eStatus'] = "Active";
    $data_active_status = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName_Accounts, $searchQueryActive);
    $ActiveStatusAcounts = [];

    if (is_array($data_active_status)) {
        foreach ($data_active_status as $accountActiveData) {
            $serviceId = safeArrayAccess($accountActiveData, 'vServiceId');
            if ($serviceId !== '') {
              //  $ActiveStatusAcounts[$serviceId] = ($ActiveStatusAcounts[$serviceId] ?? 0) + 1;
            }
        }
    }

    $data_drvAcc = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName_Accounts);
    $serviceIDAccArr = [];

    if (is_array($data_drvAcc)) {
        foreach ($data_drvAcc as $accountData) {
            $serviceId = safeArrayAccess($accountData, 'vServiceId');
            if ($serviceId !== '') {
               // $serviceIDAccArr[$serviceId] = ($serviceIDAccArr[$serviceId] ?? 0) + 1;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?? '' ?> | <?php echo $langage_lbl_admin['LBL_MAP_API_SETTING_TXT_ADMIN'] ?? 'Map API Settings'; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once 'global_files.php'; ?>
    <link rel="stylesheet" href="../assets/css/modal_alert.css" />
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- Main Loading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once 'header.php'; ?>

    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <?php
                        $company_name = (($cmp_name ?? "") != "") ? " of " . $cmp_name : "";
                        ?>
                        <h2>Maps API Settings</h2>
                    </div>
                </div>
                <hr/>
            </div>

            <?php include 'valid_msg.php'; ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <input type="hidden" name="iDriverId" value="<?php echo $iDriverId ?? ''; ?>">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="10%" class="padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                            </select>
                        </td>
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?php
                            if (!empty($keyword)) {
                                echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
                            }
                            ?>" class="form-control"/>
                        </td>
                        <td width="12%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php if ($eStatus == 'Active') echo "selected"; ?>>
                                    Active
                                </option>
                                <option value="Inactive" <?php if ($eStatus == 'Inactive') echo "selected"; ?>>
                                    Inactive
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search"
                                   name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = 'map_api_setting.php'"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export">
                            <div class="changeStatus col-lg-12 option-box-left">
                                <span class="col-lg-2 new-select001">
                                    <?php if ($userObj->hasPermission(['update-status-map-api-service-account', 'delete-map-api-service-account'])) { ?>
                                        <select name="changeStatus" id="changeStatus" class="form-control"
                                                onChange="ChangeStatusAll(this.value);">
                                            <option value="">Select Action</option>
                                            <option value='Active' <?php if ($option == 'Active') echo "selected"; ?>>
                                                Activate</option>
                                            <option value="Inactive" <?php if ($option == 'Inactive') echo "selected"; ?>>
                                                Deactivate
                                            </option>
                                            <?php if (isset($_REQUEST['ENABLE_DELETE_ADMIN_XX4AT3LM']) && strtoupper($_REQUEST['ENABLE_DELETE_ADMIN_XX4AT3LM']) == "YES") { ?>
                                                <option value="Delete" <?php if ($option == 'Delete') echo "selected"; ?>>Delete</option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </span>
                            </div>
                            
                            <div class="panel-heading">
                                <form name="_export_form" id="_export_form" method="post" style="display: inline-flex;">
                                    <?php if ($userObj->hasPermission('export-map-api-service-account')) { ?>
                                    <button type="button" style="width: 106px !important;" id="exportall">Export All</button>
                                    <?php } ?>
                                    <?php if ($userObj->hasPermission('import-map-api-service-account')) { ?>
                                    <button type="button" style="width: 100px !important;"
                                            onClick="showImportTypes('map_api')">Import All</button>
                                    <?php } ?>
                                </form>
                            </div>
                        </div>
                        
                        <?php if ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable ">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD'] ?? 'Cannot edit/delete in demo mode'; ?>
                            </div>
                            <br/>
                        <?php } ?>
                        
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if ($userObj->hasPermission(['update-status-map-api-service-account', 'delete-map-api-service-account'])) { ?>
                                            <th width="3%" class="align-center">
                                                <input type="checkbox" id="setAllCheck">
                                            </th>
                                        <?php } ?>
                                        
                                        <?php if (!defined('ONLYDELIVERALL') || ONLYDELIVERALL == 'No') { ?>
                                            <th width="15%">
                                                <a href="javascript:void(0);"
                                                   onClick="Redirect(1,<?php echo ($sortby == '1') ? $order : 0; ?>)">
                                                   Service Name 
                                                   <?php
                                                    if ($sortby == '1') {
                                                        echo ($order == 0) ? '<i class="fa fa-sort-amount-asc" aria-hidden="true"></i>' : '<i class="fa fa-sort-amount-desc" aria-hidden="true"></i>';
                                                    } else {
                                                        echo '<i class="fa fa-sort" aria-hidden="true"></i>';
                                                    }
                                                    ?>
                                                </a>
                                            </th>
                                        <?php } ?>
                                        
                                        <th width="18%" class="align-center">Active Services</th>
                                        <th width="13%" class="align-center">
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(3,<?php echo ($sortby == '3') ? $order : 0; ?>)">
                                               Usage Order 
                                               <?php
                                                if ($sortby == 3) {
                                                    echo ($order == 0) ? '<i class="fa fa-sort-amount-asc" aria-hidden="true"></i>' : '<i class="fa fa-sort-amount-desc" aria-hidden="true"></i>';
                                                } else {
                                                    echo '<i class="fa fa-sort" aria-hidden="true"></i>';
                                                }
                                                ?>
                                            </a>
                                        </th>
                                        <th width="13%" class="align-center">Accounts</th>
                                        
                                        <?php if ($userObj->hasPermission('viewused-map-api-service-account')) { ?>
                                        <th width="12%" class="align-center">Usage Report</th>
                                        <?php } ?>
                                        
                                        <th width="12%" class="align-center">
                                            <a href="javascript:void(0);" 
                                               onClick="Redirect(6,<?php echo ($sortby == '6') ? $order : 0; ?>)">
                                               Status 
                                               <?php
                                                if ($sortby == 6) {
                                                    echo ($order == 0) ? '<i class="fa fa-sort-amount-asc" aria-hidden="true"></i>' : '<i class="fa fa-sort-amount-desc" aria-hidden="true"></i>';
                                                } else {
                                                    echo '<i class="fa fa-sort" aria-hidden="true"></i>';
                                                }
                                                ?>
                                            </a>
                                        </th>
                                        
                                        <?php if ($userObj->hasPermission(['edit-map-api-service-account','update-status-map-api-service-account'])) { ?>
                                        <th width="8%" class="align-center">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv) && is_array($data_drv)) {
                                        for ($i = 0; $i < count($data_drv); $i++) {
                                            $currentRow = $data_drv[$i];
                                            $vAvailableServicesArry = explode(",", safeArrayAccess($currentRow, 'vAvailableServices'));
                                            $serviceid['vServiceId'] = safeArrayAccess($currentRow, 'vServiceId');
                                            $authAccPlacesCount = $serviceIDAccArr[safeArrayAccess($currentRow, 'vServiceId')] ?? 0;
                                            $ActiveStatusAcountsCount = $ActiveStatusAcounts[safeArrayAccess($currentRow, 'vServiceId')] ?? 0;
                                            $default = '';
                                            $mongoId = getMongoId(safeArrayAccess($currentRow, '_id'));
                                            ?>

                                            <tr class="gradeA">
                                            <?php if ($userObj->hasPermission(['update-status-map-api-service-account', 'delete-map-api-service-account'])) { ?>
                                                <td style="vertical-align: middle;" align="center">
                                                    <input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?>
                                                           value="<?php echo htmlspecialchars(safeArrayAccess($currentRow, 'vServiceName'), ENT_QUOTES, 'UTF-8'); ?>" />
                                                </td>
                                            <?php } ?>
                                            
                                            <td style="vertical-align: middle;">
                                                <a target="_blank" href="<?= htmlspecialchars(safeArrayAccess($currentRow, 'vServiceURL'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?= htmlspecialchars(safeArrayAccess($currentRow, 'vServiceName'), ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </td>
                                            
                                            <td style="vertical-align: middle;" align="center">
                                                <?php
                                                $activeServieListArry = explode(",", safeArrayAccess($currentRow, 'vActiveServices'));
                                                $key = array_search('PlaceDetails', $activeServieListArry);
                                                if (false !== $key) {
                                                    unset($activeServieListArry[$key]);
                                                }
                                                echo !empty(safeArrayAccess($currentRow, 'vActiveServices')) ? implode(", ", $activeServieListArry) : '--';
                                                
                                                if (count($vAvailableServicesArry) > 1) {
                                                    ?>
                                                    <div style="vertical-align: middle;margin-top:5px;">
                                                        <?php if (safeArrayAccess($currentRow, 'vServiceName') != 'Google') { ?>
                                                            <a href="javascript:void(0);"
                                                               onclick="show_services_config('<?= $mongoId; ?>','<?php echo $i; ?>','<?php echo safeArrayAccess($currentRow, 'eStatus'); ?>','<?php echo $activeServices; ?>')"
                                                               class="add-btn-sub">Config</a>
                                                        <?php } else { ?>
                                                            <a href="javascript:void(0);" data-toggle="tooltip"
                                                               title="Google Maps API will remain as a base to provide backup support in the failure event of other Maps API services. So, it's not possible to alter existing configuration of Google Maps API services."
                                                               disabled='disabled' style="cursor: no-drop;"
                                                               class="add-btn-sub">Config</a>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                            
                                            <td style="word-break: break-all;vertical-align: middle;" class="align-center">
                                                <?= htmlspecialchars(safeArrayAccess($currentRow, 'vUsageOrder'), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            
                                            <td style="vertical-align: middle;text-align:center">
                                                <?php 
                                                if (safeArrayAccess($currentRow, 'vServiceName') == "OpenMap") {
                                                    echo '<span style="text-align:center;">--</span>';
                                                } else {
                                                    $svgicon = '<span style="position: absolute;" data-toggle="tooltip" title="Accounts are not available. Please add it."><img style="width:20px; height:20px; margin-left: 5px" src="' . $baseUrl . 'assets/img/danger-new.svg" /></span>'; ?>
                                                    <a style="margin-left:5px;" target="_blank"
                                                       href="map_api_mongo_auth_places.php?id=<?php echo safeArrayAccess($currentRow, 'vServiceId'); ?>"
                                                       class="add-btn-sub">Add/View (<?php echo $authAccPlacesCount; ?>)</a>
                                                    <?php echo ($authAccPlacesCount == 0) ? $svgicon : '';
                                                }
                                                ?>
                                            </td>
                                            
                                            <?php if ($userObj->hasPermission('viewused-map-api-service-account')) { ?>
                                            <td style="vertical-align: middle;" align="center">
                                                <a class="add-btn-sub"
                                                   href="usage_report.php?oid=<?= $mongoId; ?>&sid=<?= safeArrayAccess($currentRow, 'vServiceId'); ?>">View</a>
                                            </td>
                                            <?php } ?>
                                            
                                            <td style="vertical-align: middle;" align="center">
                                                <?php
                                                $status = safeArrayAccess($currentRow, 'eStatus');
                                                $dis_img = "img/inactive-icon.png";
                                                if ($status == 'Active') {
                                                    $dis_img = "img/active-icon.png";
                                                } else if ($status == 'Deleted') {
                                                    $dis_img = "img/delete-icon.png";
                                                }
                                                ?>
                                                <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip"
                                                     title="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                            </td>
                                            
                                            <?php if (safeArrayAccess($currentRow, 'vServiceName') != 'Google') { ?>
                                                <?php if ($userObj->hasPermission(['edit-map-api-service-account','update-status-map-api-service-account'])) { ?>
                                                <td style="vertical-align: middle;" align="center" class="action-btn001">
                                                    <div class="share-button openHoverAction-class" style="display: block;">
                                                        <label class="entypo-export">
                                                            <span><img src="images/settings-icon.png" alt=""></span>
                                                        </label>
                                                        <div class="social show-moreOptions for-five openPops_<?= safeArrayAccess($currentRow, 'vUsageOrder'); ?>">
                                                            <ul>
                                                                <?php if (safeArrayAccess($currentRow, 'eDefault') != 'Yes') { ?>
                                                                    <?php if ($userObj->hasPermission('edit-map-api-service-account')) { ?>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a href="map_api_setting_action.php?id=<?= $mongoId; ?>&sid=<?= safeArrayAccess($currentRow, 'vServiceId'); ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    
                                                                    <?php if (safeArrayAccess($currentRow, 'vServiceName') != 'Google') { ?>
                                                                        <?php if ($userObj->hasPermission('update-status-map-api-service-account')) { ?>
                                                                            <li class="entypo-facebook" data-network="facebook">
                                                                                <a href="javascript:void(0);" 
                                                                                   <?php
                                                                                    if ($ActiveStatusAcountsCount > 0 && safeArrayAccess($currentRow, 'vServiceId') != 1) { ?>
                                                                                        onClick="changeStatusForMapAPI('<?php echo safeArrayAccess($currentRow, 'vServiceName'); ?>', 'Inactive','<?php echo $activeServices; ?>','<?php echo $i; ?>','<?php echo $site_type; ?>')"
                                                                                        title="Activate"
                                                                                    <?php } else if (safeArrayAccess($currentRow, 'vServiceId') == 1) { ?>
                                                                                        onClick="changeStatusForMapAPI('<?php echo safeArrayAccess($currentRow, 'vServiceName'); ?>', 'Inactive','<?php echo $activeServices; ?>','<?php echo $i; ?>','<?php echo $site_type; ?>')"
                                                                                        title="Activate"
                                                                                    <?php } else { ?>
                                                                                        title="No active account available. So you can not make it active."
                                                                                    <?php } ?>
                                                                                   data-toggle="tooltip">
                                                                                    <img src="img/active-icon.png" alt="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus" data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatusForMapAPI('<?php echo safeArrayAccess($currentRow, 'vServiceName'); ?>', 'Active','<?php echo $activeServices; ?>','<?php echo $i; ?>','<?php echo $site_type; ?>')"
                                                                                   data-toggle="tooltip" title="Deactivate">
                                                                                    <img src="img/inactive-icon.png" alt="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        
                                                                        <?php if (isset($_REQUEST['ENABLE_DELETE_ADMIN_XX4AT3LM']) && strtoupper($_REQUEST['ENABLE_DELETE_ADMIN_XX4AT3LM']) == "YES") { ?>
                                                                            <li class="entypo-gplus" data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatusForMapAPI('<?php echo safeArrayAccess($currentRow, 'vServiceName'); ?>', 'Delete','<?php echo $activeServices; ?>','<?php echo $i; ?>','<?php echo $site_type; ?>')"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png" alt="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                <?php } else { ?>
                                                                    --
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <?php if ($userObj->hasPermission(['edit-map-api-service-account','update-status-map-api-service-account'])) { ?>
                                                    <td align="center" class="action-btn001">
                                                        <div data-toggle="tooltip"
                                                             title="Google Maps API will remain as a base to provide backup support in the failure event of other Maps API services. So, it's not possible to alter existing configuration of Google Maps API services."
                                                             class="share-button openHoverAction-class" style="display: block;">
                                                            <label disabled style="cursor: no-drop;" class="entypo-export">
                                                                <span><img style="cursor: no-drop;" src="images/settings-icon.png" alt=""></span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="14">No Records Found.</td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include 'pagination_n.php'; ?>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
            
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li><strong>Geocoding:</strong> System is using this Service to convert location into address.</li>
                    <li><strong>Direction:</strong> System is using this Service to draw route b/w two locations on map.</li>
                    <li><strong>AutoComplete:</strong> System is using this Service to give suggestion of different places.</li>
                    <li><strong>Google Maps API:</strong> Google Maps API will remain as a base to provide backup support in the failure event of other Maps API services. So, it's not possible to alter existing configuration of Google Maps API services.</li>
                    <li><strong>Import and Export Feature:</strong> Use Import and Export feature when you are going to change the hosting server. This feature will help you to set the existing API configuration into the new hosting server.</li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<form name="pageForm" id="pageForm" action="action/map_api_setting.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page ?? ''; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages ?? ''; ?>">
    <input type="hidden" name="iMongoName" id="iMainId01" value="">
    <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?php echo $iCompanyId; ?>">
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>

<!-- Modals -->
<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_MAP_API_SETTING_TXT_ADMIN'] ?? 'Map API Settings' ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="import_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    Import All Map API Settings
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div>
                    <form name="_import_map_api_settings" id="_import_map_api_settings" enctype="multipart/form-data"
                          method="POST" onsubmit="return confirm('Are you sure to remove your current map api setting data?');">
                        <div style="color:#1fbad6;">
                            <b>Note:</b> Please note that all map api setting data will be removed.
                        </div>
                        <br>
                        <input type="file" name="import_file_map_api" id="import_file_map_api" required>
                        <br>
                        <input type="submit" value="Submit" class="btnalt button11" id="import_submit" name="Submit" title="Submit"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="services_config_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <?= $langage_lbl_admin['LBL_MAP_API_SERVICES_CONFIGURATION'] ?? 'Map API Services Configuration' ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                    </div>
                </div>
                <div id="services_config"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="driver_add_wallet_money" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;" class="fa fa-google-wallet"></i>
                    Add Balance
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <form class="form-horizontal" id="add_money_frm" method="POST" enctype="multipart/form-data" action="" name="add_money_frm">
                <input type="hidden" id="action" name="action" value="addmoney">
                <input type="hidden" name="eTransRequest" id="eTransRequest" value="">
                <input type="hidden" name="eType" id="eType" value="Credit">
                <input type="hidden" name="eFor" id="eFor" value="Deposit">
                <input type="hidden" name="iDriverId" id="iDriver-Id" value="">
                <input type="hidden" name="eUserType" id="eUserType" value="Driver">
                <div class="col-lg-12">
                    <div class="input-group input-append">
                        <h5><?= $langage_lbl['LBL_ADD_WALLET_DESC_TXT'] ?? 'Add wallet description'; ?></h5>
                        <div class="ddtt">
                            <h4><?= $langage_lbl['LBL_ENTER_AMOUNT'] ?? 'Enter Amount'; ?></h4>
                            <input type="text" name="iBalance" id="iBalance" class="form-control iBalance add-ibalance" onKeyup="checkzero(this.value);">
                        </div>
                        <div id="iLimitmsg"></div>
                    </div>
                </div>
                <div class="nimot-class-but">
                    <input type="button" onClick="check_add_money();" class="save" id="add_money"
                           name="<?= $langage_lbl['LBL_save'] ?? 'Save'; ?>" value="<?= $langage_lbl['LBL_Save'] ?? 'Save'; ?>">
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
                </div>
            </form>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
<script src="../assets/js/modal_alert.js"></script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        $("#exportall").on('click', function () {
            var action = "main_export.php";
            var section = "map_api";
            var formValus = $("#_export_form, #pageForm, #show_export_modal_form_json").serialize();
            window.location.href = action + '?section=' + section + '&' + formValus;
            $("#show_export_types_modal_json").modal('hide');
            return false;
        });
    });

    $('INPUT[type="file"]').change(function (e) {
        var ext = this.value.match(/\.(.+)$/)[1];
        switch (ext) {
            case 'json':
                $('#import_submit').attr('disabled', false);
                break;
            default:
                alert('Please upload json file.');
                this.value = '';
        }
        var fileName = e.target.files[0].name;
        var myresult = 'false';
    });

    function fail() {
        $('#import_submit').attr('disabled', true);
        $('#import_file_map_api').value = '';
        alert('Please upload valid content json file.');
    }

    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        } else {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                this.checked = '';
            });
        }
    });

    $("#Search").on('click', function () {
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });

    $('.entypo-export').click(function (e) {
        e.stopPropagation();
        var $this = $(this).parent().find('div');
        $(".openHoverAction-class div").not($this).removeClass('active');
        $this.toggleClass('active');
    });

    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
        }
    });

    function show_services_config(oid, row_id, status, countActiveServices) {
        $("#services_config").html('');
        $("#services_config_modal").modal('show');

        if (oid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?? '' ?>ajax_services_config.php',
                'AJAX_DATA': "iServiceOid=" + oid + "&row_id=" + row_id + "&status=" + status + "&countActiveServices=" + countActiveServices,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#services_config").html(data);
                } else {
                    console.log(response.result);
                }
            });
        }
    }

    function update_service_config() {
        var activeDataForCurrnetRowID = [];
        var status = $('#status').val();
        var row_id = $('#row_id').val();
        var countActiveServices = $('#countActiveServices').val();
        var serialdataAry = $("#service_config_frm").serializeArray();

        $.each(serialdataAry, function (i, field) {
            if (field.name == 'selectedval[]') {
                activeDataForCurrnetRowID.push(field.value);
            }
        });

        AllActiveServices[row_id] = [];
        for (var n = 0; n < activeDataForCurrnetRowID.length; n++) {
            AllActiveServices[row_id][n] = (activeDataForCurrnetRowID[n]);
        }

        var result = ValidateMeConfig(AllActiveServices, status, countActiveServices, row_id);
        if (result != false) {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?? '' ?>ajax_services_config_update.php',
                'AJAX_DATA': $('#service_config_frm').serialize(),
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    window.location.href = "<?= $tconfig["tsite_url_main_admin"] ?? '' ?>map_api_setting.php";
                } else {
                    console.log(response.result);
                }
            });
        }
    }

    function Add_money_driver(driverid) {
        $("#driver_add_wallet_money").modal('show');
        $(".add-ibalance").val("");
        if (driverid != "") {
            var setDriverId = $('#iDriver-Id').val(driverid);
        }
    }

    function changeOrder(iAdminId) {
        $('#is_dltSngl_modal').modal('show');
        $(".action_modal_submit").unbind().click(function () {
            var action = $("#pageForm").attr('action');
            var page = $("#pageId").val();
            $("#pageId01").val(page);
            $("#iMainId01").val(iAdminId);
            $("#method").val('delete');
            var formValus = $("#pageForm").serialize();
            window.location.href = action + "?" + formValus;
        });
    }

    function check_add_money() {
        var iBalance = $(".add-ibalance").val();
        if (iBalance == '') {
            alert("Please enter amount");
            return false;
        } else if (iBalance == 0) {
            alert("You Can Not Enter Zero Number");
            return false;
        } else {
            $("#add_money").val('Please wait ...').attr('disabled', 'disabled');
            $('#add_money_frm').submit();
        }
    }

    $(".iBalance").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode == 67 && e.ctrlKey === true) ||
            (e.keyCode == 88 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    function checkzero(userlimit) {
        if (userlimit != "") {
            if (userlimit == 0) {
                $('#iLimitmsg').html('<span class="red">You Can Not Enter Zero Number</span>');
            } else if (userlimit <= 0) {
                $('#iLimitmsg').html('<span class="red">You Can Not Enter Negative Number</span>');
            } else {
                $('#iLimitmsg').html('');
            }
        } else {
            $('#iLimitmsg').html('');
        }
    }

    function validateandconfirm() {
        var result = confirm("Are you sure? your map api data will be removed.");
        if (result == true || result == "Yes") {
            $().submit;
        }
    }
</script>
</body>
</html>
