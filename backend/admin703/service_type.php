<?php
include_once('../common.php');

$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
$eServiceType = isset($_REQUEST['eServiceType']) ? $_REQUEST['eServiceType'] : "";

$commonTxt = "";
if ($eType == 'MedicalServices' || $eServiceType == 'MedicalServices') {
    $commonTxt = '-medical';
}

$view = "view-service-type" . $commonTxt;
$create = "create-service-type" . $commonTxt;
$edit = "edit-service-type" . $commonTxt;
$delete = "delete-service-type" . $commonTxt;
$updateStatus = "update-status-service-type" . $commonTxt;

if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}

//$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'delete';
//$success  = isset($_REQUEST['success'])?$_REQUEST['success']:'';
$eServiceType = isset($_GET['eServiceType']) ? $_GET['eServiceType'] : "";
if(empty($eServiceType)) {
    $eServiceType = isset($_GET['eType']) ? $_GET['eType'] : "";
}
$eServiceType = isset($_GET['eServiceType']) ? $_GET['eServiceType'] : "";
if(empty($eServiceType)) {
    $eServiceType = isset($_GET['eType']) ? $_GET['eType'] : "";
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
if ($default_lang == "") {
    $default_lang = "EN";
}
$script = 'ServiceType';
if ($eServiceType == 'MedicalServices') {
    $script = 'ServiceType_' . $eServiceType;
}
if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $app_type_service = 'UberX';
} else {
    $app_type_service = $APP_TYPE;
}
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$ord = ' ORDER BY vt.iVehicleCategoryId ASC';
if ($sortby == 1) {
    if ($order == 0) {
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
    } else {
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
    }
}
if ($sortby == 4) {
    if ($order == 0) {
        $ord = " ORDER BY vt.eStatus ASC";
    } else {
        $ord = " ORDER BY vt.eStatus DESC";
    }
}
if ($sortby == 5) {
    if ($order == 0) {
        $ord = " ORDER BY vt.iDisplayOrder ASC";
    } else {
        $ord = " ORDER BY vt.iDisplayOrder DESC";
    }
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$ssql = '';
if ($parent_ufx_catid > 0) {
    $getSubCat = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT CONCAT('''',iVehicleCategoryId, '''')) SUB_CAT FROM " . $sql_vehicle_category_table_name . " WHERE iParentId='" . $parent_ufx_catid . "'");
    if (scount($getSubCat) > 0) {
        $ssql .= " AND vt.iVehicleCategoryId IN (" . $getSubCat[0]['SUB_CAT'] . ")";
    }
}
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            if ($iVehicleCategoryId != '') { //changed by me
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";
            }
        } else {
            if ($iVehicleCategoryId != '') { //changed by me
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";
        } else {
            $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
        }
    }
    //echo $ssql;
} else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus != '') {
    $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus='" . $eStatus . "'";
} else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus == '') {
    $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
} else if ($iVehicleCategoryId == '' && $keyword == '' && $eStatus != '') {
    $ssql .= " AND vt.eStatus='" . $eStatus . "'";
}

if((strtoupper(ONLY_MEDICAL_SERVICE) == "YES" || $eType == "MedicalServices") && empty($iVehicleCategoryId)) {
    $db_subcat = $obj->MySQLSelect("SELECT GROUP_CONCAT(iVehicleCategoryId) as iVehicleCategoryIds FROM " . $sql_vehicle_category_table_name . " WHERE eStatus != 'Deleted' AND eCatType = 'ServiceProvider' AND iParentId = 3");

    $ssql .= " AND vt.iVehicleCategoryId IN (" . $db_subcat[0]['iVehicleCategoryIds'] . ")";
}
/* $locations_where = "";
  if(scount($userObj->locations) > 0){
  $locations = implode(', ', $userObj->locations);
  $locations_where = " AND vt.iLocationid IN(-1, {$locations}) ";
  $ssql .= $locations_where;
  } */
if ($eStatus != '') {
    $eStatussql = "";
} else {
    $eStatussql = " AND vt.eStatus != 'Deleted'";
}
// End Search Parameters
if ($APP_TYPE == 'Delivery') {
    $Vehicle_type_name = 'Deliver';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $Vehicle_type_name = 'UberX';
} else {
    $Vehicle_type_name = $APP_TYPE;
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "";
$sql = "SELECT count(vt.iVehicleTypeId) as Total,vc.iVehicleCategoryId,vc.vCategory_" . $default_lang . " from  vehicle_type as vt left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' $ssql $eStatussql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) {
    $page = 1;
}
//Pagination End
$sql = "SELECT vt.*,vc.iVehicleCategoryId,vc.vCategory_" . $default_lang . ",vc.iParentId,lm.vLocationName from  vehicle_type as vt left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' $ssql $eStatussql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$eTypeQueryString = $eType = $sql1 = $eTypeQuery = '';
if ($Vehicle_type_name == 'UberX') {
    $ufxServiceId = $ufxParentServiceId = "";
    if ($parent_ufx_catid > 0) {
        $ufxServiceId = "AND iVehicleCategoryId='" . $parent_ufx_catid . "'";
        $ufxParentServiceId = "AND iParentId='" . $parent_ufx_catid . "'";
    }
    $parent_id_sql = "AND iParentId='0' ";
    $subsql = "";
    if (
        in_array($Vehicle_type_name, [
            'UberX',
            'VideoConsult'
        ]) && $MODULES_OBJ->isEnableMedicalServices('Yes')
    ) {
        $subsql .= " AND iVehicleCategoryId NOT IN (3,22,26,158) ";
    }
    $db_data_cat = $obj->MySQLSelect("select *  from " . $sql_vehicle_category_table_name . " where 1 = 1 AND eStatus != 'Deleted' AND eCatType='ServiceProvider' $ufxServiceId $parent_id_sql $subsql");

    if ($eServiceType == "MedicalServices") {
        $sql1 = " AND tMedicalServiceInfo != '' ";
        $eTypeQueryString = '&eType=MedicalServices';
        $eTypeQuery = '?eType=MedicalServices';
        $db_data_cat = $obj->MySQLSelect("select *  from " . $sql_vehicle_category_table_name . " where eStatus != 'Deleted' AND eCatType='ServiceProvider' $sql1 $ufxServiceId");
    }
}
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME; ?> | <?= $langage_lbl_admin['LBL_SERVICE_TXT']; ?> Type</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?= $langage_lbl_admin['LBL_SERVICE_TXT']; ?> Type</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <input type="hidden" name="option" id="option" value="vVehicleType_<?= $default_lang ?>">
                        <input type="hidden" name="eServiceType" value="<?php echo $eServiceType; ?>" >
                        <td width="12%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?= $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td width="25%">
                            <select class="form-control" name='iVehicleCategoryId'>
                                <option value="">Select Subcategory</option>
                                <?php
                                $subCatDataArr = array();
                                $subVehicleData = $obj->MySQLSelect("SELECT * FROM  `" . $sql_vehicle_category_table_name . "` WHERE eStatus != 'Deleted' $sql1 $ufxParentServiceId");
                                for ($s = 0; $s < scount($subVehicleData); $s++) {
                                    $subCatDataArr[$subVehicleData[$s]['iParentId']][] = $subVehicleData[$s];
                                }
                                if ($eServiceType == "MedicalServices") {
                                    for ($i = 0; $i < scount($db_data_cat); $i++) { ?>
                                        <option value="<?= $db_data_cat[$i]['iVehicleCategoryId'] ?>"  label="<?= $db_data_cat[$i]['vCategory_' . $default_lang]; ?>"

                                            <?php
                                            if ($db_data_cat[$i]['iVehicleCategoryId'] == $iVehicleCategoryId) {
                                                echo 'selected';
                                            }
                                            ?>
                                        >
                                            <?php
                                            $db_data2 = array();
                                            if (isset($subCatDataArr[$db_data_cat[$i]['iVehicleCategoryId']])) {
                                                $db_data2 = $subCatDataArr[$db_data_cat[$i]['iVehicleCategoryId']];
                                            }
                                          ?>
                                        </option>
                                    <?php }
                                } else {
                                    for ($i = 0; $i < scount($db_data_cat); $i++) { ?>
                                        <optgroup label="<?= $db_data_cat[$i]['vCategory_' . $default_lang]; ?>">
                                            <?php
                                            //$db_data2 = $obj->MySQLSelect("SELECT * FROM  `".$sql_vehicle_category_table_name."` WHERE  `iParentId` = '" . $db_data_cat[$i]['iVehicleCategoryId'] . "' AND eStatus != 'Deleted'");
                                            $db_data2 = array();
                                            if (isset($subCatDataArr[$db_data_cat[$i]['iVehicleCategoryId']])) {
                                                $db_data2 = $subCatDataArr[$db_data_cat[$i]['iVehicleCategoryId']];
                                            }
                                            //echo "<pre>";print_r($db_data2);die;
                                            for ($j = 0; $j < scount($db_data2); $j++) {
                                                ?>
                                                <option value="<?= $db_data2[$j]['iVehicleCategoryId'] ?>"
                                                    <?php
                                                    if ($db_data2[$j]['iVehicleCategoryId'] == $iVehicleCategoryId) {
                                                        echo 'selected';
                                                    }
                                                    ?> >
                                                    <?= "&nbsp;&nbsp;|-- " . $db_data2[$j]['vCategory_' . $default_lang]; ?></option>
                                            <?php } ?>
                                        </optgroup>

                                    <?php }
                                } ?>
                            </select>
                        </td>
                        <td width="12%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?> >Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?> >Inactive
                                </option>
                                <option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?> >Delete
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = 'service_type.php<?php echo $eTypeQuery;?>'"/>
                        </td>
                        <?php
                        if ($userObj->hasPermission($create)) { ?>
                            <td width="20%">
                                <a class="add-btn" href="service_type_action.php?parent=<?= $iVehicleCategoryId . $eTypeQueryString; ?>"
                                   style="text-align: center;">Add <?= $langage_lbl_admin['LBL_SERVICE_TXT']; ?> Type
                                </a>
                            </td>
                        <?php } ?>
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
                                            <?php if (
                                                $userObj->hasPermission([$updateStatus, $delete])
                                            ) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onchange="ChangeStatusAll(this.value);">
                                                    <option value="">Select Action</option>
                                                    <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                        <option value='Active' <?php
                                                        if ($option == 'Active') {
                                                            echo "selected";
                                                        }
                                                        ?> >Activate</option>
                                                        <option value="Inactive" <?php
                                                        if ($option == 'Inactive') {
                                                            echo "selected";
                                                        }
                                                        ?> >Deactivate</option>
                                                    <?php } ?>
                                                    <?php if ($eStatus != 'Deleted') { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                    <?php } ?>
                                                </select>
                                            <?php } ?>
                                        </span>
                            </div>
                            <?php if (!empty($data_drv)) { ?>
                                <!--  <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onclick="showExportTypes('service_type')" >Export</button>
                                        </form>
                                    </div>-->
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">


                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if (
                                            $userObj->hasPermission([$updateStatus, $delete])
                                        ) { ?>
                                            <th align="center" width="3%" style="text-align:center;">
                                                <input type="checkbox" id="setAllCheck">
                                            </th>
                                        <?php } ?>
                                        <th width="12%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Type<?php
                                                if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="18%">Subcategory</th>
                                        <th width="6%" align="center" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(5,<?php
                                            if ($sortby == '5') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"> Display Order <?php
                                                if ($sortby == 5) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="8%">Localization</th>
                                        <th width="4%" align="center" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"> Status <?php
                                                if ($sortby == 4) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if (
                                            $userObj->hasPermission([$edit, $updateStatus, $delete])
                                        ) { ?>
                                            <th width="4%" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        //Added By HJ On 21-09-2020 For Optimize Foor Loop Query Start
                                        $vehicleParentData = array();
                                        $getVehicleCatData = $obj->MySQLSelect("SELECT vCategory_$default_lang AS catname,iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " WHERE 1=1");
                                        for ($d = 0; $d < scount($getVehicleCatData); $d++) {
                                            $vehicleParentData[$getVehicleCatData[$d]['iVehicleCategoryId']] = $getVehicleCatData[$d]['catname'];
                                        }
                                        //echo "<pre>";print_r($vehicleParentData);die;
                                        //Added By HJ On 21-09-2020 For Optimize Foor Loop Query End
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            ?>
                                            <tr class="gradeA">
                                                <?php if (
                                                    $userObj->hasPermission([$updateStatus, $delete])
                                                ) { ?>
                                                    <td align="center" style="text-align:center;">
                                                        <input type="checkbox" id="checkbox" name="checkbox[]"
                                                               value="<?= $data_drv[$i]['iVehicleTypeId']; ?>"/>&nbsp;
                                                    </td>
                                                <?php } ?>
                                                <td><?= $data_drv[$i]['vVehicleType_' . $default_lang] ?></td>
                                                <?php
                                                //echo "<pre>";print_r($data_drv);die;
                                                $parentCatName = "";
                                                if (isset($vehicleParentData[$data_drv[$i]['iParentId']])) {
                                                    $parentCatName = $vehicleParentData[$data_drv[$i]['iParentId']];
                                                }
                                                //$data_parentcat = $obj->MySQLSelect("SELECT vCategory_$default_lang FROM ".$sql_vehicle_category_table_name." WHERE iVehicleCategoryId = '".$data_drv[$i]['iParentId']."'"); ?>
                                                <td><?= $parentCatName . ' - ' . $data_drv[$i]['vCategory_' . $default_lang] ?></td>
                                                <td style="text-align: center;"><?= $data_drv[$i]['iDisplayOrder'] ?></td>
                                                <?php if (($data_drv[$i]['iLocationid'] == "-1")) { ?>
                                                    <td>All Locations</td>
                                                <?php } else { ?>
                                                    <td style="text-transform: capitalize;"><?= $data_drv[$i]['vLocationName']; ?></td>
                                                <?php } ?>
                                                <td align="center">
                                                    <?php
                                                    if ($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip"
                                                         title="<?= $data_drv[$i]['eStatus']; ?>">
                                                </td>

                                                <?php if (
                                                    $userObj->hasPermission([$edit, $updateStatus, $delete])
                                                ) { ?>

                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export">
                                                                <span><img src="images/settings-icon.png" alt=""></span>
                                                            </label>
                                                            <div class="social show-moreOptions for-two openPops_<?= $data_drv[$i]['iVehicleTypeId']; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission($edit)) { ?>
                                                                        <li class="entypo-twitter"
                                                                            data-network="twitter">
                                                                            <a href="service_type_action.php?id=<?= $data_drv[$i]['iVehicleTypeId'].$eTypeQueryString; ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                        <li class="entypo-facebook"
                                                                            data-network="facebook">
                                                                            <a href="javascript:void(0);"
                                                                               onClick="changeStatus('<?= $data_drv[$i]['iVehicleTypeId']; ?>', 'Inactive')"
                                                                               data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png"
                                                                                     alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                            </a>
                                                                        </li>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);"
                                                                               onClick="changeStatus('<?= $data_drv[$i]['iVehicleTypeId']; ?>', 'Active')"
                                                                               data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png"
                                                                                     alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($eStatus != 'Deleted') { ?>
                                                                        <?php if ($userObj->hasPermission($delete)) { ?>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatusDelete('<?= $data_drv[$i]['iVehicleTypeId']; ?>')"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>

                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="12"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li> <?= $langage_lbl_admin['LBL_SERVICE_TXT']; ?> Type module will list
                        all <?= $langage_lbl_admin['LBL_SERVICE_TXT']; ?> types on this page.
                    </li>
                    <li> Administrator can Edit / Delete any <?= $langage_lbl_admin['LBL_SERVICE_TXT']; ?> type.</li>
                    <!-- <li> Administrator can export data in XLS or PDF format.</li> -->
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/service_type.php" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="iVehicleTypeId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>">
    <input type="hidden" name="option" value="<?= $option; ?>">
    <input type="hidden" name="keyword" value="<?= $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="eServiceType" id="eServiceType" value="<?= $eServiceType ?>">
    <?php if ($app_type_service == 'UberX') { ?>
        <input type="hidden" name="iVehicleCategoryId" id="iVehicleCategoryId" value="<?= $iVehicleCategoryId; ?>">
    <?php } ?>
</form>
<?php include_once('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#eType_options').hide();
        $('#option').each(function () {
            if (this.value == 'vt.eType') {
                $('#eType_options').show();
                $('.searchform').hide();
            }
        });
    });
    $(function () {
        $('#option').change(function () {
            if ($('#option').val() == 'vt.eType') {
                $('#eType_options').show();
                $("input[name=keyword]").val("");
                $('.searchform').hide();
            } else {
                $('#eType_options').hide();
                $("#eType_value").val("");
                $('.searchform').show();
            }
        });
    });
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
</script>
</body>
<!-- END BODY-->
</html>
