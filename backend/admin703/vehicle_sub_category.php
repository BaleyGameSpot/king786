<?php
include_once('../common.php');
$script = 'VehicleCategory';
/*if (!$userObj->hasPermission('view-vehicle-category')) {
    $userObj->redirect();
}*/

$eServiceType = isset($_GET['eServiceType']) ? $_GET['eServiceType'] : "";
if ($eServiceType == 'Deliver') {
    $commonTxt = 'parcel-delivery';
}
if ($eServiceType == 'VideoConsult') {
    $commonTxt = 'video-consultation';
}
if ($eServiceType == 'UberX') {
    $commonTxt = 'uberx';
}

if ($eServiceType == 'MedicalServices') {
    $commonTxt = 'medical';
}
$view = "view-service-category-" . $commonTxt;
$update = "update-service-category-" . $commonTxt;
$updateStatus = "update-status-service-category-" . $commonTxt;
$create = "create-service-category-" . $commonTxt;
$delete = "delete-service-category-" . $commonTxt;

if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}
if (isset($_REQUEST['subcat']) && $_REQUEST['subcat'] > 0) {
    $parent_ufx_catid = $_REQUEST['subcat'];
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDisplayOrder ASC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY vCategory_" . $default_lang . " ASC"; else
        $ord = " ORDER BY vCategory_" . $default_lang . " DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY eStatus ASC"; else
        $ord = " ORDER BY eStatus DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY Servicetypes ASC"; else
        $ord = " ORDER BY Servicetypes DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY iDisplayOrder ASC"; else
        $ord = " ORDER BY iDisplayOrder DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY fCommissionVideoConsult ASC"; else
        $ord = " ORDER BY fCommissionVideoConsult DESC";
}
//End Sorting
$rdr_ssql = "";
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : "";
$eServiceType = isset($_GET['eServiceType']) ? $_GET['eServiceType'] : "";
if ($THEME_OBJ->isProThemeActive() == "Yes" && $eServiceType != '') {
    $script = 'VehicleCategory_' . $eServiceType;
}
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
}
if (isset($_REQUEST['subcat']) && $_REQUEST['subcat'] > 0 && empty($sub_cid)) {
    $ssql .= " AND iParentId = '" . $_REQUEST['subcat'] . "'";
}
//Added By SP 
if ($eStatus != '') {
    $ssql .= "";
} else {
    $ssql .= " AND eStatus != 'Deleted'";
}
if ($eServiceType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()) {
    // $ssql .= " AND eVideoConsultEnable = 'Yes' ";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if ($parent_ufx_catid != "0") {
    $sql = "SELECT COUNT(iVehicleCategoryId) AS Total FROM " . $sql_vehicle_category_table_name . "  WHERE (iParentId='" . $sub_cid . "' || iVehicleCategoryId='" . $parent_ufx_catid . "') $ssql $rdr_ssql";
    $getDataCategory = $obj->MySQLSelect("SELECT iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " WHERE `eCatType` =  'MoreDelivery' AND  `eFor` =  'DeliveryCategory'");
    if (scount($getDataCategory) > 0 && !empty($getDataCategory) && $parent_ufx_catid == $getDataCategory[0]['iVehicleCategoryId']) {
        $sql = "SELECT COUNT(iVehicleCategoryId) AS Total FROM " . $sql_vehicle_category_table_name . "  WHERE iParentId='" . $getDataCategory[0]['iVehicleCategoryId'] . "' $ssql $rdr_ssql";
    }
} else {
    $sql = "SELECT COUNT(iVehicleCategoryId) AS Total FROM " . $sql_vehicle_category_table_name . "  WHERE iParentId='" . $sub_cid . "' $ssql $rdr_ssql";
}
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
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
if ($page <= 0) $page = 1;
//Pagination End
$sql2 = "SELECT iVehicleCategoryId,vCategory_" . $default_lang . ",eCatType, eFor FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId='" . $sub_cid . "'";
$data_cetegory_name = $obj->MySQLSelect($sql2);
if ($parent_ufx_catid != "0") {
    $sql = "SELECT iVehicleCategoryId,vCategory_" . $default_lang . ", ePriceType,iServiceId, iDisplayOrder, eBeforeUpload, eAfterUpload, vLogo,vListLogo2,vBannerImage, eStatus,eCatType, vLogo2, (select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = " . $sql_vehicle_category_table_name . ".iVehicleCategoryId AND vehicle_type.eStatus != 'Deleted') as Servicetypes FROM " . $sql_vehicle_category_table_name . " WHERE (iParentId='" . $sub_cid . "' || iVehicleCategoryId='" . $parent_ufx_catid . "') $ssql $rdr_ssql $ord LIMIT $start, $per_page";
    if (scount($getDataCategory) > 0 && !empty($getDataCategory) && $parent_ufx_catid == $getDataCategory[0]['iVehicleCategoryId']) {
        $sql = "SELECT iVehicleCategoryId,vCategory_" . $default_lang . ",ePriceType,iServiceId, iDisplayOrder, eBeforeUpload, eAfterUpload, vLogo,vListLogo2,vBannerImage, eStatus,eCatType, vLogo2,(select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = " . $sql_vehicle_category_table_name . ".iVehicleCategoryId AND vehicle_type.eStatus != 'Deleted') as Servicetypes FROM " . $sql_vehicle_category_table_name . " WHERE iParentId='" . $getDataCategory[0]['iVehicleCategoryId'] . "' $ssql $rdr_ssql $ord LIMIT $start, $per_page";
    }
} else {
    $sql = "SELECT iVehicleCategoryId,vCategory_" . $default_lang . ",ePriceType,iServiceId, iDisplayOrder, eBeforeUpload, eAfterUpload, vLogo,vListLogo2, vBannerImage, eStatus,eCatType, fCommissionVideoConsult, eVideoConsultEnable, vLogo2, (select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = " . $sql_vehicle_category_table_name . ".iVehicleCategoryId AND vehicle_type.eStatus != 'Deleted') as Servicetypes FROM " . $sql_vehicle_category_table_name . " WHERE iParentId='" . $sub_cid . "' $ssql $rdr_ssql $ord LIMIT $start, $per_page";
}
$data_drv = $obj->MySQLSelect($sql);
/* $vehicleCatCount = $obj->MySQLSelect("select count(iVehicleCategoryId) as Servicecat,iVehicleCategoryId from ".$sql_vehicle_category_table_name." WHERE iParentId > 0 GROUP BY iParentId");
  $catCountArr = $serviceType = array();
  for ($r = 0; $r < scount($vehicleCatCount); $r++) {
  $catCountArr[$vehicleCatCount[$r]['iVehicleCategoryId']] = $vehicleCatCount[$r]['Servicecat'];
  }
  echo "<pre>";print_R($catCountArr);die; */
$serviceType = array();
for ($c = 0; $c < scount($data_drv); $c++) {
    $db_data_count = 0;
    $csql = $obj->MySQLSelect("select count(iVehicleCategoryId) as Servicecat from " . $sql_vehicle_category_table_name . " where iParentId = '" . $data_drv[$c]['iVehicleCategoryId'] . "'");
    if (isset($csql[0]['Servicecat']) && $csql[0]['Servicecat'] > 0) {
        $db_data_count = $csql[0]['Servicecat'];
    }
    /* if (isset($catCountArr[$data_drv[$c]['iVehicleCategoryId']]) && $catCountArr[$data_drv[$c]['iVehicleCategoryId']] > 0) {
      $db_data_count = $catCountArr[$data_drv[$c]['iVehicleCategoryId']];
      } */
    $data_drv[$c]['catCount'] = $db_data_count;
    if (isset($data_cetegory_name[0]['eCatType']) && $data_cetegory_name[0]['eCatType'] == "MoreDelivery" && $db_data_count > 0) {
        $serviceType[] = 1;
    }
    if ($data_drv[$c]['eCatType'] == 'ServiceProvider' && $parent_ufx_catid != $data_drv[$c]['iVehicleCategoryId']) {
        $serviceType[] = 1;
    }
    //$serviceType[] = 1;
}
// echo "<pre>";print_r($data_drv);die;
$hideColumn = 1; // 1-Hide ,0Show
if (in_array(1, $serviceType)) {
    $hideColumn = 0;
}
if (($eServiceType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()) || strtoupper($APP_TYPE) == "DELIVERY") {
    $hideColumn = 1;
}
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
//Added By HJ On 20-06-2020 For Back List Page Link Start
$backPage = "vehicle_category.php" . (!empty($eServiceType) ? '?eType=' . $eServiceType : '');
if ($sub_cid > 0) {
    $chekParentCatId = $obj->MySQLSelect("SELECT iParentId FROM vehicle_category WHERE iVehicleCategoryId='" . $sub_cid . "'");
    if (scount($chekParentCatId) > 0) {
        $mainParentId = $chekParentCatId[0]['iParentId'];
    }
    if ($mainParentId > 0) {
        $backPage = "vehicle_sub_category.php?sub_cid=" . $mainParentId . (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '');
    }
}
//Added By HJ On 20-06-2020 For Back List Page Link End

$service_categories = array_values(array_filter(getMasterServiceCategories(), function ($item) use ($eServiceType) {
    return $item['eType'] == $eServiceType;
}));
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Sub Category</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
                        <h2><?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN'] . ' (' . $data_cetegory_name[0]['vCategory_' . $default_lang . ''] . ')'; ?>
                        </h2>
                        <?php if($parent_ufx_catid > 0) { ?>
                            <a class="add-btn" href="vehicle_category_action.php?id=<?php echo $parent_ufx_catid; ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>" style="text-align: center;">Settings
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <input type="hidden" name="sub_cid" value="<?php echo $sub_cid; ?>">
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <input type="hidden" name="option" id="option" value="">
                        <input type="hidden" name="eServiceType" id="eServiceType" value="<?= $eServiceType ?>">
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                   class="form-control"/>
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
                                <?php if ($userObj->hasPermission($delete)) { ?>
                                    <option value="Deleted" <?php
                                    if ($eStatus == 'Deleted') {
                                        echo "selected";
                                    }
                                    ?> >Deleted
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'vehicle_sub_category.php?sub_cid=<?php echo $sub_cid; ?><?php if ($parent_ufx_catid == "178") { ?>&subcat=<?= $sub_cid;
                                   }
                                   ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>'"/>
                        </td>
                        <?php if ($data_cetegory_name[0]['eCatType'] == 'ServiceProvider') { ?>
                            <?php if ($userObj->hasPermission($create)) { ?>
                                <td width="30%">
                                    <a class="add-btn"
                                       href="vehicle_category_action.php?sub_action=sub_category&sub_cid=<?php echo $sub_cid; ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>"
                                       style="text-align: center;">Add Sub Category
                                    </a>
                                    <?php if ($parent_ufx_catid == "0" && !in_array($eServiceType, ['Deliver'])) { ?>
                                    <a class="add-btn" href="<?= $backPage; ?>" style="text-align: center;">Back To List</a>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                            <? } else { ?>
                                <?php if ($parent_ufx_catid == "0" && !in_array($eServiceType, ['Deliver'])) { ?>
                                <td width="30%">
                                    <a class="add-btn" href="<?= $backPage; ?>" style="text-align: center;">Back To List</a>
                                </td>
                                <?php } ?>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export" style="display:flow-root;">
                           <?php /* <div class="changeStatus col-lg-12 option-box-left">
                                <?php if ($sub_cid != '185') { ?>
                                    <span class="col-lg-2 new-select001">
                                            <?php if ($userObj->hasPermission([
                                                $updateStatus,
                                                $delete
                                            ])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onChange="ChangeStatusAll(this.value);">
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
                                                    <?php if ($userObj->hasPermission($delete) && $eStatus != 'Deleted') { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                    <?php } ?> 
                                                </select>
                                            <?php } ?>
                                        </span>
                                <? } ?>
                            </div> */ ?>
                            <?php /*if ($parent_ufx_catid == "0" && !in_array($eServiceType, ['Deliver'])) { ?>
                                <a class="add-btn" href="<?= $backPage; ?>" style="text-align: center;">Back To List</a>
                                <?php
                            }
                            if ($data_cetegory_name[0]['eCatType'] == 'ServiceProvider') {
                                if (!empty($data_drv)) {
                                    ?>
                                    <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post">
                                            <button type="button" onClick="showExportTypes('sub_service_category')">
                                                Export
                                            </button>
                                        </form>
                                    </div>
                                    <?php
                                }
                            }*/
                            ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive1">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                               <?php /* <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if ($sub_cid != '185') { ?>
                                        <?php if ($userObj->hasPermission([
                                            $updateStatus,
                                            $delete
                                        ])) { ?>
                                            <th align="center" width="3%" style="text-align:center;">
                                                <input type="checkbox" id="setAllCheck">
                                            </th>

                                        <?php } } ?>
                                        <?php if ($data_cetegory_name[0]['eCatType'] != "MoreDelivery" || $THEME_OBJ->isPXCProThemeActive() == "Yes" || strtoupper($APP_TYPE) == "DELIVERY" || strtoupper($APP_TYPE) == "RIDE-DELIVERY") { ?>
                                            <th width="6%" style="text-align:center;">Icon</th>
                                        <?php } ?>
                                        <th width="18%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Name <?php
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
                                        </th> <? if ($hideColumn == 0) { ?>
                                            <th width="8%" align="center" style="text-align:center;">
                                                <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                if ($sortby == '3') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?> <?php
                                                    if ($sortby == 3) {
                                                        if ($order == 0) {
                                                            ?>
                                                            <i class="fa fa-sort-amount-asc"
                                                               aria-hidden="true"></i> <?php } else { ?>
                                                            <i class="fa fa-sort-amount-desc"
                                                               aria-hidden="true"></i><?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                        <? } ?>

                                        <?php if ($eServiceType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()) { ?>
                                            <th width="8%" align="center" style="text-align:center;">
                                                <a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                if ($sortby == '5') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)">Commission (%) <?php
                                                    if ($sortby == 5) {
                                                        if ($order == 0) {
                                                            ?>
                                                            <i class="fa fa-sort-amount-asc"
                                                               aria-hidden="true"></i> <?php } else { ?>
                                                            <i class="fa fa-sort-amount-desc"
                                                               aria-hidden="true"></i><?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                            <th width="10%" style="text-align:center;">Video Consultation</th>
                                        <?php } ?>
                                        <th width="8%" align="center" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Display Order <?php
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
                                        <th width="8%" align="center" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 2) {
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
                                        <?php if ($userObj->hasPermission([
                                            $update,
                                            $updateStatus,
                                            $delete
                                        ])) { ?>
                                        <th width="8%" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        $newTmpArr = $newTmpArr1 = array();
                                        for ($b = 0; $b < scount($data_drv); $b++) {
                                            if ($parent_ufx_catid != $data_drv[$b]['iVehicleCategoryId']) {
                                                $newTmpArr1[] = $data_drv[$b];
                                            } else {
                                                $newTmpArr[] = $data_drv[$b];
                                            }
                                        }
                                        $data_drv = array();
                                        $data_drv = array_merge($newTmpArr, $newTmpArr1);
                                        //echo "<pre>";print_r($data_drv);die;
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 Start
                                            $buttonText = "View";
                                            if ($data_drv[$i]['eCatType'] == "ServiceProvider") {
                                                $buttonText = "Add/ View";
                                            }
                                            //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 End
                                            if ($sub_cid == '185') {
                                                $iServiceIdEdit = $data_drv[$i]['iServiceId'];
                                            } else {
                                                $iServiceIdEdit = '';
                                            }
                                            if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
                                                $data_drv[$i]['vLogo'] = $data_drv[$i]['vLogo2'];
                                            }

                                            ?>
                                            <tr class="gradeA">


                                                <?php if ($sub_cid != '185'&& $userObj->hasPermission([$updateStatus,$delete]) ) {
                                                    if ($parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']  ){ ?>

                                                        <td align="center" style="text-align:center;">
                                                            <input type="checkbox" id="checkbox"
                                                                   name="checkbox[]" <?php echo $default; ?>
                                                                   value="<?php echo $data_drv[$i]['iVehicleCategoryId']; ?>"/>&nbsp;
                                                        </td>
                                                    <?php } else { ?>
                                                        <td align="center" style="text-align:center;"></td>
                                                    <?php }
                                                } ?>

                                                <? if ($data_cetegory_name[0]['eCatType'] != "MoreDelivery" || $THEME_OBJ->isPXCProThemeActive() == "Yes" || strtoupper($APP_TYPE) == "DELIVERY" || strtoupper($APP_TYPE) == "RIDE-DELIVERY") { ?>
                                                    <td align="center">
                                                        <?php if ($data_drv[$i]['vLogo'] != '' && $data_drv[$i]['iVehicleCategoryId'] != $parent_ufx_catid) { ?>
                                                            <img src="<?= $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/ios/3x_" . $data_drv[$i]['vLogo']; ?>"
                                                                 style="width:35px;height:35px;">
                                                        <? } ?>
                                                    </td>
                                                <? } ?>
                                                <td><? echo $data_drv[$i]['vCategory_' . $default_lang . '']; ?></td>
                                                <? if ($hideColumn == 0) { ?>
                                                    <td align="center">
                                                        <?
                                                        if ($data_drv[$i]['eCatType'] == 'ServiceProvider' && $parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']) {
                                                            if ($userObj->hasPermission($view)) {
                                                                ?>
                                                                <a class="add-btn-sub"
                                                                   href="service_type.php?iVehicleCategoryId=<?= $data_drv[$i]['iVehicleCategoryId'] ?>"
                                                                   target="_blank"><?= $buttonText; ?>
                                                                    (<?php echo $data_drv[$i]['Servicetypes']; ?>)
                                                                </a>
                                                            <?php } else { ?>
                                                                <?= $buttonText; ?> (<?php echo $data_drv[$i]['Servicetypes']; ?>)
                                                                <?php
                                                            }
                                                        } else {
                                                            //$csql = "select count(iVehicleCategoryId) as Servicecat from ".$sql_vehicle_category_table_name." where iParentId = '" . $data_drv[$i]['iVehicleCategoryId'] . "'";
                                                            //$db_data_count = $obj->MySQLSelect($csql);
                                                            if ($data_cetegory_name[0]['eCatType'] == "MoreDelivery" && $data_drv[$i]['catCount'] > 0) {
                                                                if ($parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']) {
                                                                    ?>
                                                                    <?php if ($userObj->hasPermission($view)) { ?>
                                                                        <a class="add-btn-sub"
                                                                           href="vehicle_sub_category.php?sub_cid=<?= $data_drv[$i]['iVehicleCategoryId'] ?><?php if ($parent_ufx_catid == "178") { ?>&subcat=<?= $data_drv[$i]['iVehicleCategoryId'];
                                                                           }
                                                                           ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>"
                                                                           target="_blank"><?= $buttonText; ?>
                                                                            (<?php echo $data_drv[$i]['catCount']; ?>)
                                                                        </a>
                                                                    <?php } else { ?>
                                                                        <?= $buttonText; ?> (<?php echo $data_drv[$i]['catCount']; ?>)
                                                                        <?php
                                                                    }
                                                                }
                                                            } else {
                                                                echo '---';
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                <? } ?>

                                                <?php if ($eServiceType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()) { ?>
                                                    <td align="center">
                                                        <?= $data_drv[$i]['fCommissionVideoConsult'] ?>
                                                    </td>
                                                    <td align="center" style="text-align:center;">
                                                        <?php
                                                        if ($data_drv[$i]['eVideoConsultEnable'] == 'Yes') {
                                                            $dis_img = "img/active-icon.png";
                                                            $eVideoConsultEnableStatus = "Enabled";
                                                        } else {
                                                            $dis_img = "img/inactive-icon.png";
                                                            $eVideoConsultEnableStatus = "Disabled";
                                                        }
                                                        ?>
                                                        <img src="<?= $dis_img; ?>" alt="" data-toggle="tooltip"
                                                             data-original-title="<?= $eVideoConsultEnableStatus ?>">
                                                    </td>
                                                <?php } ?>
                                                <td align="center">
                                                    <?php if ($data_drv[$i]['iVehicleCategoryId'] != $parent_ufx_catid) { ?><? echo $data_drv[$i]['iDisplayOrder']; ?>
                                                    <?php } else { ?>
                                                        ---
                                                    <?php } ?>
                                                </td>
                                                <td width="10%" align="center">
                                                    <?php if ($data_drv[$i]['iVehicleCategoryId'] != $parent_ufx_catid) { ?>
                                                        <?
                                                        if ($data_drv[$i]['eStatus'] == 'Active') {
                                                            $dis_img = "img/active-icon.png";
                                                        } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                            $dis_img = "img/inactive-icon.png";
                                                        } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                            $dis_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $dis_img; ?>"
                                                             alt="<?= $data_drv[$i]['eStatus'] ?>" data-toggle="tooltip"
                                                             title="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                    <?php } else { ?>---
                                                    <?php } ?>
                                                </td>
                                                <?php if ($parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']) { ?>
                                                    <?php if ($userObj->hasPermission([
                                                        $update,
                                                        $updateStatus,
                                                        $delete
                                                    ])) { ?>
                                                        <td align="center" style="text-align:center;"
                                                            class="action-btn001">
                                                            <div class="share-button openHoverAction-class"
                                                                 style="display: block;">
                                                                <label class="entypo-export">
                                                                    <span><img src="images/settings-icon.png"
                                                                               alt=""></span>
                                                                </label>
                                                                <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iVehicleCategoryId']; ?>">
                                                                    <ul>
                                                                        <?php if ($userObj->hasPermission($update)) { ?>
                                                                            <li class="entypo-twitter"
                                                                                data-network="twitter">
                                                                                <a href="vehicle_category_action.php?id=<?= $data_drv[$i]['iVehicleCategoryId']; ?>&sub_action=sub_category&sub_cid=<?php echo $sub_cid; ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>"
                                                                                   data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png"
                                                                                         alt="Edit">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeSubCatStatus('<?php echo $data_drv[$i]['iVehicleCategoryId']; ?>', 'Inactive','<?php echo $iServiceIdEdit; ?>')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeSubCatStatus('<?php echo $data_drv[$i]['iVehicleCategoryId']; ?>', 'Active','<?php echo $iServiceIdEdit; ?>')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Deactivate">
                                                                                    <img src="img/inactive-icon.png"
                                                                                         alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission($delete) && $eStatus != 'Deleted' && $sub_cid != "178") { ?>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatusDelete('<?php echo $data_drv[$i]['iVehicleCategoryId']; ?>')"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <?php if ($userObj->hasPermission([$update])) { ?>
                                                        <td align="center" style="text-align:center;"
                                                            class="action-btn001 dd-tt">
                                                            <a href="vehicle_category_action.php?id=<?= $data_drv[$i]['iVehicleCategoryId']; ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>"
                                                               data-toggle="tooltip" title="Edit">
                                                                <img src="img/edit-icon.png" alt="Edit">
                                                            </a>
                                                        </td>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tr>
                                            <?
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="8"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table> */?>

                                <div class="table table-striped  table-hover">
                                    <div class="profile-earning">
                                        <div class="partation">
                                            <ul style="padding-left: 0px;" class="setings-list">
                                                <?php
                                                if (!empty($data_drv)) {
                                                    $newTmpArr = $newTmpArr1 = array();
                                                    for ($b = 0; $b < scount($data_drv); $b++) {
                                                        if ($parent_ufx_catid != $data_drv[$b]['iVehicleCategoryId']) {
                                                            $newTmpArr1[] = $data_drv[$b];
                                                        } else {
                                                            $newTmpArr[] = $data_drv[$b];
                                                        }
                                                    }
                                                    $data_drv = array();
                                                    $data_drv = array_merge($newTmpArr, $newTmpArr1);
                                                    for ($i = 0; $i < scount($data_drv); $i++) {
                                                        //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 Start
                                                        $buttonText = "View";
                                                        if ($data_drv[$i]['eCatType'] == "ServiceProvider") {
                                                            $buttonText = "Add/ View";
                                                        }
                                                        //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 End
                                                        if ($sub_cid == '185') {
                                                            $iServiceIdEdit = $data_drv[$i]['iServiceId'];
                                                        } else {
                                                            $iServiceIdEdit = '';
                                                        }
                                                        if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
                                                            $data_drv[$i]['vLogo'] = $data_drv[$i]['vLogo2'];
                                                        }
                                                        $buttonStatus = $data_drv[$i]['eStatus'];
                                                        $btnChecked = 0;
                                                        if ($data_drv[$i]['eStatus'] == "Active") {
                                                            $btnChecked = 1;
                                                        }
                                                        //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 End
                                                        if ($parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']) {
                                                        ?>
                                                        <li>
                                                            <div class="toggle-list-inner">
                                                                <div class="toggle-combo">
                                                                    <label>
                                                                        <?php if ($data_cetegory_name[0]['eCatType'] != "MoreDelivery" || $THEME_OBJ->isPXCProThemeActive() == "Yes" || strtoupper($APP_TYPE) == "DELIVERY" || strtoupper($APP_TYPE) == "RIDE-DELIVERY") {  ?>
                                                                        <div align="center">
                                                                            <?php if ($data_drv[$i]['vLogo'] != '' && $data_drv[$i]['iVehicleCategoryId'] != $parent_ufx_catid) { ?>
                                                                            <img src="<?= $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/ios/3x_" . $data_drv[$i]['vLogo']; ?>" style="width:100px;">
                                                                            <?php } ?>
                                                                        </div>
                                                                        <?php } ?>

                                                                        <div style="margin: 0 0 0 10px;">
                                                                            <td><?= $data_drv[$i]['vCategory_' . $default_lang . '']; ?></td>
                                                                        </div>
                                                                    </label>
                                                                    <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                        <span class="toggle-switch">
                                                                            <input type="checkbox" <?php if ($btnChecked > 0) { ?> checked="" <?php } ?>  id="statusbutton" class="chk statusbutton" name="statusbutton"  onClick="changeSubCatStatus('<?php echo $data_drv[$i]['iVehicleCategoryId']; ?>', '<?php echo $buttonStatus; ?>','<?php echo $iServiceIdEdit; ?>')" data-toggle="tooltip"
                                                                                       title="<?= ($btnChecked > 0) ? "Active" : "Inactive" ?> " >
                                                                            <span class="toggle-base"></span>
                                                                        </span>
                                                                    <?php } ?>
                                                                </div>
                                                                <div class="check-combo">
                                                                    <label id="defaultText_246">
                                                                        <ul>
                                                                            <?php if ($userObj->hasPermission([$update,$updateStatus,$delete])) { ?>
                                                                            <?php if ($userObj->hasPermission($update)) { ?>
                                                                                <li class="entypo-twitter"
                                                                                    data-network="twitter">
                                                                                    <a href="vehicle_category_action.php?id=<?= $data_drv[$i]['iVehicleCategoryId']; ?>&sub_action=sub_category&sub_cid=<?php echo $sub_cid; ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>" data-toggle="tooltip" title="Edit">
                                                                                        <img src="img/edit-new.png" alt="Edit">
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                            <?php if ($userObj->hasPermission($delete) && $eStatus != 'Deleted' && $sub_cid != "178") { ?>
                                                                                <li class="entypo-facebook" data-network="facebook">
                                                                                    <a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iVehicleCategoryId']; ?>')"
                                                                                       data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-new.png" alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                            <?php } else { ?>
                                                                                <li class="entypo-twitter"
                                                                                    data-network="twitter">
                                                                                    <a href="vehicle_category_action.php?id=<?= $data_drv[$i]['iVehicleCategoryId']; ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>" data-toggle="tooltip" title="Edit">
                                                                                        <img src="img/edit-new.png" alt="Edit">
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>

                                                                            <?php if ($hideColumn == 0) { ?>
                                                                            <?php if ($data_drv[$i]['eCatType'] == 'ServiceProvider' && $parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']) {
                                                                                      if ($userObj->hasPermission($view)) { ?>
                                                                                        <?php if ($userObj->hasPermission($view)) { ?>
                                                                                            <li class="entypo-gplus"
                                                                                                data-network="gplus">
                                                                                               <a  href="service_type.php?iVehicleCategoryId=<?= $data_drv[$i]['iVehicleCategoryId'] ?>" target="_blank" data-toggle="tooltip" title="<?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?>">
                                                                                                    <img src="img/view-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                                </a>
                                                                                            </li>
                                                                                        <?php
                                                                                        }
                                                                                    }
                                                                                }
                                                                                else 
                                                                                {
                                                                                    if ($data_cetegory_name[0]['eCatType'] == "MoreDelivery" && $data_drv[$i]['catCount'] > 0) { 
                                                                                            if ($parent_ufx_catid != $data_drv[$i]['iVehicleCategoryId']) {  ?>
                                                                                                <?php if ($userObj->hasPermission($view)) { ?>
                                                                                                <li class="entypo-gplus" data-network="gplus">
                                                                                                   <a  href="vehicle_sub_category.php?sub_cid=<?= $data_drv[$i]['iVehicleCategoryId'] ?><?php if ($parent_ufx_catid == "178") { ?>&subcat=<?= $data_drv[$i]['iVehicleCategoryId']; } ?><?= (!empty($eServiceType) ? '&eServiceType=' . $eServiceType : '') ?>" data-toggle="tooltip" target="_blank" title="<?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']." ".$buttonText."(".$data_drv[$i]['catCount'] .")"; ?>">
                                                                                                        <img src="img/view-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                                    </a>
                                                                                                </li>
                                                                                                <?php }  ?>
                                                                                      <?php }
                                                                                    }
                                                                                }
                                                                            } ?>
                                                                        </ul>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php } } ?>
                                            </ul>
                                        </div>
                                    </div><?php
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="8"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                </div>
                            </form>
                        </div>
                        <?php include('pagination_n.php'); ?>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>
                        Sub Category module will list all Sub Categories on this page.
                    </li>
                    <?php if ($userObj->hasPermission($delete)) { ?>
                        <li>Administrator can Activate / Deactivate / Delete any Sub Category</li>
                    <?php } ?>
                    <!--<li>Administrator can export data in XLS or PDF format.</li>-->
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none; z-index: 99999">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/vehicle_sub_category.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iVehicleCategoryId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="sub_cid" id="sub_cid" value="<?php echo $sub_cid; ?>">
    <input type="hidden" name="subcat" id="subcat" value="<?php echo $parent_ufx_catid; ?>">
    <input type="hidden" name="iServiceIdEdit" id="iServiceIdEdit" value="">
    <input type="hidden" name="eServiceType" id="eServiceType" value="<?= $eServiceType ?>">
</form>
<?php include_once('footer.php'); ?>
<script>
    /* $(document).ready(function() {
     $('#eStatus_options').hide();
     $('#option').each(function(){
     if (this.value == 'eStatus') {
     $('#eStatus_options').show();
     $('.searchform').hide();
     }
     });
     });
     $(function() {
     $('#option').change(function(){
     if($('#option').val() == 'eStatus') {
     $('#eStatus_options').show();
     $("input[name=keyword]").val("");
     $('.searchform').hide();
     } else {
     $('#eStatus_options').hide();
     $("#estatus_value").val("");
     $('.searchform').show();
     }
     });
     });*/

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
        //$('html').addClass('loading');
        var action = $("#_list_form").attr('action');
        //alert(action);
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