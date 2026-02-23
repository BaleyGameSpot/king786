<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-documents')) {
    $userObj->redirect();
}
$script = 'Document Master';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dm.doc_name ASC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY c.vCountry ASC"; else
        $ord = " ORDER BY c.vCountry DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY dm.doc_usertype ASC"; else
        $ord = " ORDER BY dm.doc_usertype DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY dm.doc_name ASC"; else
        $ord = " ORDER BY dm.doc_name DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY dm.status ASC"; else
        $ord = " ORDER BY dm.status DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY dm.eDocServiceType ASC"; else
        $ord = " ORDER BY dm.eDocServiceType DESC";
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType_value = isset($_REQUEST['eType_value']) ? stripslashes($_REQUEST['eType_value']) : "";
$doc_userTypeValue = isset($_REQUEST['doc_userTypeValue']) ? stripslashes($_REQUEST['doc_userTypeValue']) : "";
$eDocServiceType = isset($_REQUEST['eDocServiceType']) ? stripslashes($_REQUEST['eDocServiceType']) : "";
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? stripslashes($_REQUEST['iVehicleCategoryId']) : "";
$ssql = '';

if (!empty($keyword)) {
    if (!empty($option)) {
        if (!empty($eDocServiceType)) {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dm.eDocServiceType = '" . clean($eDocServiceType) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
        if (!empty($doc_userTypeValue)) {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dm.doc_usertype = '" . clean($doc_userTypeValue) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
        if (!empty($eDocServiceType)) {
            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%') AND dm.eDocServiceType = '" . clean($eDocServiceType) . "'";
        } else {
            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%')";
        }
        if (!empty($doc_userTypeValue)) {
            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%') AND dm.eDocServiceType = '" . clean($eDocServiceType) . "' AND dm.doc_usertype = '" . clean($doc_userTypeValue) . "'";
        } else {
            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%')";
        }
    }
} else if (!empty($eDocServiceType) && empty($keyword)) {
    $ssql .= " AND dm.eDocServiceType = '" . clean($eDocServiceType) . "'";
} else if (!empty($doc_userTypeValue) && empty($keyword)) {
    $ssql .= " AND dm.doc_usertype = '" . clean($doc_userTypeValue) . "'";
}
if (!empty($eDocServiceType)) {
    $ssql .= " AND dm.doc_usertype != 'company'";
}
//Added By HJ On 06-12-2019 For Solved Bug 594 Of Sheet Start
if ($APP_TYPE == "UberX") {
    $ssql .= " AND dm.doc_usertype != 'car'";
}
if (ONLYDELIVERALL == "Yes") {
    $ssql .= " AND dm.doc_usertype != 'company'";
}
if (DELIVERALL == "No") {
    $ssql .= " AND dm.doc_usertype != 'store'";
}
//Added By HJ On 06-12-2019 For Solved Bug 594 Of Sheet End
if (!empty($iVehicleCategoryId)) {
    $ssql .= " AND dm.iVehicleCategoryId = $iVehicleCategoryId ";
}
if ($option == "dm.status") {
    $eStatussql = " AND dm.status = '$keyword'";
} else {
    $eStatussql = " AND dm.status != 'Deleted'";
}
//Added By HJ On 14-05-2020 For Checked Ufx Service Exists Or Not Start
$ufxService = $MODULES_OBJ->isUfxFeatureAvailable();
if (strtoupper($ufxService) == "NO") {
    $ssql .= " AND dm.eDocServiceType != 'ServiceSpecific'";
}
if (!$MODULES_OBJ->isEnableBiddingWiseProviderDoc()) {
    $ssql .= " AND dm.eDocServiceType != 'BiddingSpecific'";
}
//Added By HJ On 14-05-2020 For Checked Ufx Service Exists Or Not End
// End Search Parameters
/* if($APP_TYPE == 'Ride-Delivery'){
  $eTypeQuery = " AND (dm.eType='Ride' OR dm.eType='Delivery')";
  } else if($APP_TYPE == 'Ride-Delivery-UberX'){
  $eTypeQuery = " AND (dm.eType='Ride' OR dm.eType='Delivery' OR dm.eType='UberX')";
  } else {
  $eTypeQuery = " AND dm.eType='".$APP_TYPE."'";
  } */
//Pagination Start
$sql_select_when_bid = $sql_join_bid = $sql_select_bid = '';
$sql_where_bid = "AND dm.eDocServiceType != 'BiddingSpecific'";
if ($MODULES_OBJ->isEnableBiddingServices()) {
    $sql_select_bid = "dm.iBiddingId iBiddingId,";
    $sql_join_bid = "LEFT JOIN `bidding_service` AS bs ON dm.iBiddingId=bs.iBiddingId";
    $sql_select_when_bid = "WHEN dm.eDocServiceType = 'BiddingSpecific' THEN JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "'))";
    $sql_where_bid = "";
}
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(dm.doc_masterid) AS Total FROM `document_master` AS dm LEFT JOIN `country` AS c ON c.vCountryCode=dm.country WHERE 1=1 $sql_where_bid $eStatussql $ssql";
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
if ($page <= 0) $page = 1;
//Pagination End
if ($default_lang == "") {
    $default_lang = "EN";
}
$sql = "SELECT $sql_select_bid (CASE WHEN dm.eDocServiceType = 'General' THEN '' WHEN dm.eDocServiceType = 'ServiceSpecific' THEN 'Service Specific' WHEN dm.eDocServiceType = 'BiddingSpecific' THEN 'Bidding Service Specific'   END) AS eDocServiceType_txt, (CASE WHEN dm.eDocServiceType = 'General' THEN 'General' WHEN dm.eDocServiceType = 'ServiceSpecific' THEN vc.vCategory_" . $default_lang . " $sql_select_when_bid  END) AS docServiceTypeName ,dm.doc_masterid, dm.doc_usertype , dm.doc_name, dm.status,dm.eDocServiceType , c.vCountry, dm.eDocServiceType,vc.vCategory_" . $default_lang . " as vCategory FROM `document_master` AS dm LEFT JOIN `country` AS c ON c.vCountryCode=dm.country LEFT JOIN `vehicle_category` AS vc ON dm.iVehicleCategoryId=vc.iVehicleCategoryId $sql_join_bid WHERE 1=1 $sql_where_bid $eStatussql $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$sql_cat = "SELECT iVehicleCategoryId,vCategory_$default_lang FROM vehicle_category WHERE iParentId = 0 AND eStatus='Active' AND eCatType='ServiceProvider'";
$db_catdata = $obj->MySQLSelect($sql_cat);
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Document List</title>
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
                        <h2>Manage Documents</h2>
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
			<td width="15%" class=" padding-right10">
			<select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="c.vCountry" <?php
                                if ($option == "c.vCountry") {
                                    echo "selected";
                                }
                                ?> >Country
                                </option>
                                <option value="dm.doc_name" <?php
                                if ($option == 'dm.doc_name') {
                                    echo "selected";
                                }
                                ?> >Document Name
                                </option>
                                <?php if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') { ?>
                                <option value="dm.doc_usertype" <?php
                                if ($option == 'dm.doc_usertype') {
                                    echo "selected";
                                }
                                ?> >Document For
                                </option>
                                <?php } ?>
                                <!--   <? if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                          <option value="dm.eType" <?php
                                    if ($option == 'dm.eDocServiceType') {
                                        echo "selected";
                                    }
                                    ?> >Service Type</option>
                                            <? } ?> -->
                                <option value="dm.status" <?php
                                if ($option == 'dm.status') {
                                    echo "selected";
                                }
                                ?> >Status
                                </option>
                            </select>
                        </td>
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword"
                                   value="<?php echo $keyword; ?>" class="form-control"/>
                        </td>
                        <?php if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') { ?>
                        <td width="15%" class="doc_userType" id="doc_userType">
                            <select name="doc_userTypeValue" id="doc_userTypeValue" class="form-control">
                                <option value="">Select Document For</option>
                                <?php if ($APP_TYPE != "UberX") { ?>
                                <option value='car' <?php
                                if ($doc_userTypeValue == 'car') {
                                    echo "selected";
                                }
                                ?>>Vehicle</option><?php } ?>
                                <option value="driver" <?php
                                if ($doc_userTypeValue == 'driver') {
                                    echo "selected";
                                }
                                ?>><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                <?php if (ONLYDELIVERALL == "No") { ?>
                                <option value="company" <?php
                                if ($doc_userTypeValue == 'company') {
                                    echo "selected";
                                }
                                ?>>Company</option><?php } ?>
                                <?php if (DELIVERALL == "Yes") { ?>
                                    <option value="store" <?php
                                    if ($doc_userTypeValue == 'store') {
                                        echo "selected";
                                    }
                                    ?>>Store
                                    </option>
                                <?php } ?>
                                <?php if ($MODULES_OBJ->isEnableRideShareService()) { ?>
                                    <option value="user" <?php
                                    if ($doc_userTypeValue == 'user') {
                                        echo "selected";
                                    }
                                    ?>>User (Ride Sharing)
                                    </option>
                                <?php } ?>
                                <?php if ($MODULES_OBJ->isEnableTrackServiceFeature()) { ?>
                                    <option value="trackcompany" <?php
                                    if ($doc_userTypeValue == 'trackcompany') {
                                        echo "selected";
                                    }
                                    ?>>Tracking Company
                                    </option>
                                <?php } ?>
                                <?php if ($MODULES_OBJ->isEnableParkingFeature()) { ?>
                                    <option value="user" <?php
                                    if ($doc_userTypeValue == 'user') {
                                        echo "selected";
                                    }
                                    ?>>User (Parking)
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                        <?php } ?>
                        <td width="12%" class="eDocServiceType" id="eDocServiceType">
                            <select name="eDocServiceType" id="eDocServiceTypeValue" class="form-control">
                                <option value="">Select Document Type</option>
                                <option value="General" <?php
                                if ($eDocServiceType == 'General') {
                                    echo "selected";
                                }
                                ?>> General
                                </option>
                                <option value="ServiceSpecific" <?php
                                if ($eDocServiceType == 'ServiceSpecific') {
                                    echo "selected";
                                }
                                ?>> Service Specific
                                </option>
                            </select>
                        </td>
                        <td width="12%" class="otherservice" id="otherservice">
                            <select class="form-control" name='iVehicleCategoryId'>
                                <option value="">Select Service</option>
                                <?php foreach ($db_catdata as $key_cat => $val_cat) { ?>
                                    <option value="<?= $val_cat['iVehicleCategoryId'] ?>" <?php if ($iVehicleCategoryId == $val_cat['iVehicleCategoryId']) echo 'selected="selected"'; ?> ><?php echo $val_cat['vCategory_'.$default_lang] ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <? if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
                            <td width="12%" class="eType_options" id="eType_options">
                                <select name="eType_value" id="eType_value" class="form-control">
                                    <option value="">Select Service Type</option>
                                    <option value='Ride' <?php
                                    if ($eType_value == 'Ride') {
                                        echo "selected";
                                    }
                                    ?> >Ride
                                    </option>
                                    <option value="Delivery" <?php
                                    if ($eType_value == 'Delivery') {
                                        echo "selected";
                                    }
                                    ?> >Delivery
                                    </option>
                                    <option value="UberX" <?php
                                    if ($eType_value == 'UberX') {
                                        echo "selected";
                                    }
                                    ?> >Other Services
                                    </option>
                                </select>
                            </td>
                        <? } ?>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = 'document_master_list.php'"/>
                        </td>
                        <?php if ($userObj->hasPermission('create-documents')) { ?>
                            <td width="30%">
                                <a class="add-btn" href="document_master_add.php"
                                   style="text-align: center;"> Add Document Name
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
                                            <?php if ($userObj->hasPermission(['update-status-documents', 'delete-documents'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onchange="ChangeStatusAll(this.value);">
                                                    <option value="">Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-documents')) { ?>
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
                                                    <?php if ($userObj->hasPermission('delete-documents')) { ?>
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
                                <!-- <div class="panel-heading">
                                        <form name="_export_form" id="_export_form" method="post" >
                                            <button type="button" onclick="showExportTypes('Document_Master')" >Export</button>
                                        </form>
                                    </div>-->
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if ($userObj->hasPermission(['update-status-documents', 'delete-documents'])) { ?>
                                        <th align="center" width="3%" style="text-align:center;">
                                            <input type="checkbox"
                                                   id="setAllCheck">
                                        </th>
                                        <?php } ?>
					<th width="15%">
					<a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Country <?php
                                                if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                    }
                                                } else {
						 ?>
						 <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
						</th>
						 <th width="30%">
						 <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Document Name <?php
                                                if ($sortby == 3) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                    }
                                                } else {
						  ?>
						  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
						</th>
						<th width="12%">
						<a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Document For <?php
                                                if ($sortby == 2) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if ($MODULES_OBJ->isEnableServiceTypeWiseProviderDocument() == "Yes") : ?>
                                            <th width="10%" align="center" style="text-align:center;">
					    <a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                if ($sortby == '5') {
                                                    echo $order;
                                                } else { ?>0<?php } ?>)">Document Type <?php if ($sortby == 5) {
                                                        if ($order == 0) { ?>
                                                            <i class="fa fa-sort-amount-asc"
                                                               aria-hidden="true"></i> <?php } else { ?>
                                                            <i class="fa fa-sort-amount-desc"
                                                               aria-hidden="true"></i><?php }
                                                    } else { ?>
                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                        <?php endif; ?>

                                        <th width="10%" align="center" style="text-align:center;">
					<a  
					href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 4) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                    }
                                                } else {
						 ?>
						 <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
						 </th>
                                        <?php if ($userObj->hasPermission(['edit-documents', 'update-status-documents', 'delete-documents'])) { ?>
                                        <th width="7%" class="align-center">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?
                                    if (!empty($data_drv)) {
                                        for ($i = 0; $i < scount($data_drv); $i++) { ?>
                                            <tr class="gradeA">
                                                <?php if ($userObj->hasPermission(['update-status-documents', 'delete-documents'])) { ?>
                                                <td align="center" style="text-align:center;">
						<input type="checkbox" 
						id="checkbox" 
						name="checkbox[]" 
						value="<?php echo $data_drv[$i]['doc_masterid']; ?>"/>&nbsp;
                                                </td>
                                                <?php } ?>
                                                <td><?= ($data_drv[$i]['vCountry'] == "") ? "All" : $data_drv[$i]['vCountry']; ?></td>
                                                <td><?= $data_drv[$i]['doc_name']; ?></td>
                                                <td><?php
                                                    if ($APP_TYPE == "UberX" && $data_drv[$i]['doc_usertype'] == "driver") {
                                                        $doc_usertype = $langage_lbl_admin['LBL_RIDER_DRIVER_RIDE_DETAIL'];
                                                    } else if ($data_drv[$i]['doc_usertype'] == "car") {
                                                        $doc_usertype = 'Vehicle';
                                                    } else {
                                                        $doc_usertype = $data_drv[$i]['doc_usertype'];
                                                    }
                                                    if (strtolower($data_drv[$i]['doc_usertype']) == "driver") {
                                                        $doc_usertype = $langage_lbl_admin['LBL_RIDER_DRIVER_RIDE_DETAIL'];
                                                    } else if (strtolower($data_drv[$i]['doc_usertype']) == "company") {
                                                        $doc_usertype = "Company";
                                                    } else if (strtolower($data_drv[$i]['doc_usertype']) == "store") {
                                                        $doc_usertype = "Store";
                                                    } else if (strtolower($data_drv[$i]['doc_usertype']) == "trackcompany") {
                                                        $doc_usertype = "Tracking Company";
                                                    } else if (strtolower($data_drv[$i]['doc_usertype']) == "user") {
                                                        $doc_usertype = "User (Ride Sharing)";
                                                    } else if (strtolower($data_drv[$i]['doc_usertype']) == "parking") {
                                                        $doc_usertype = "User (Parking)";
                                                    }
                                                    echo $doc_usertype;
                                                    ?></td>
                                                <?php if ($MODULES_OBJ->isEnableServiceTypeWiseProviderDocument() == "Yes") : ?>
                                                    <td align="center"> <?= $data_drv[$i]['doc_usertype'] == "driver" ? $data_drv[$i]['docServiceTypeName'] : "--"; ?>
                                                        <?php
                                                        if (isset($data_drv[$i]['eDocServiceType_txt']) && !empty($data_drv[$i]['eDocServiceType_txt'])) {
                                                            echo '</br>(' . $data_drv[$i]['eDocServiceType_txt'] . ')';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td align="center">
                                                    <?
                                                    if ($data_drv[$i]['status'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['status'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data_drv[$i]['status'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['status']; ?>"
                                                         data-toggle="tooltip" title="<?= $data_drv[$i]['status']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission(['edit-documents', 'update-status-documents', 'delete-documents'])) { ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export"><span><img
                                                                            src="images/settings-icon.png"
                                                                            alt=""></span>
                                                            </label>
                                                            <div class="social show-moreOptions openPops_<?= $data_drv[$i]['doc_masterid']; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission('edit-documents')) { ?>
                                                                        <li class="entypo-twitter"
                                                                            data-network="twitter">
                                                                            <a
                                                                                    href="document_master_add.php?id=<?= $data_drv[$i]['doc_masterid']; ?>"
                                                                                    data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php //if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                        <?php if ($userObj->hasPermission('update-status-documents')) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a
                                                                                        href="javascript:void(0);"
                                                                                        onclick="changeStatus('<?php echo $data_drv[$i]['doc_masterid']; ?>', 'Inactive')"
                                                                                        data-toggle="tooltip"
                                                                                        title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?php echo isset($data_drv[$i]['eStatus']) ? $data_drv[$i]['eStatus'] : ''; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['doc_masterid']; ?>', 'Active')"  data-toggle="tooltip" title="Deactivate">
                                                                                    <img src="img/inactive-icon.png" alt="<?php echo isset($data_drv[$i]['eStatus']) ? $data_drv[$i]['eStatus'] : ''; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission('delete-documents')) { ?>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a
                                                                                        href="javascript:void(0);"
                                                                                        onclick="changeStatusDelete('<?php echo $data_drv[$i]['doc_masterid']; ?>')"
                                                                                        data-toggle="tooltip"
                                                                                        title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php // } ?>
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
                                            <td colspan="7"> No Records Found.</td>
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
                    <li>
                        Document List module will list all document lists on this page.
                    </li>
                    <li>
                        Administrator can Activate / Deactivate / Delete any document list.
                    </li>
                    <!-- <li>
                        Administrator can export data in XLS format.
                    </li> -->
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/document_master_list.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="doc_masterid" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="eType_value" id="eType_value" value="<?php echo $eType_value; ?>">
    <input type="hidden" name="doc_userTypeValue" id="doc_userTypeValue" value="<?php echo $doc_userTypeValue; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<?php
include_once('footer.php');
?>
<script>
    var ENABLE_SERVICE_TYPE_WISE_PROVIDER_DOC = '<?= $MODULES_OBJ->isEnableServiceTypeWiseProviderDocument(); ?>';
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
    $(document).ready(function () {
        $('#eType_options').hide();
        $('#option').each(function () {
            if (this.value == 'dm.eDocServiceType') {
                $('#eType_options').show();
                $('.searchform').hide();
            }
        });
    });
    $(function () {
        $('#option').change(function () {
            if ($('#option').val() == 'dm.eDocServiceType') {
                $('#eType_options').show();
                //$("input[name=keyword]").val("");
                $.each($("input[name=keyword]"), function (obj, value) {
                    $(value).val("");
                });
                $('.searchform').hide();
            } else {
                $('#eType_options').hide();
                $("#eType_value").val("");
                $('.searchform').show();
            }
        });
    });
    $(document).ready(function () {
        $('#doc_userType').hide();
        $('#eDocServiceType').hide();
        $('#option').each(function () {
            if (this.value == 'dm.doc_usertype') {
                $('#doc_userType').show();
                $('.searchform').hide();
                $.each($("input[name=keyword]"), function (obj, value) {
                    $(value).val("");
                });
                $("input[name=option]").val("");
            }
        });
    });
    $(function () {
        $('#option').change(function () {
            if ($('#option').val() == 'dm.doc_usertype') {
                $('#doc_userType').show();
                $('.searchform').hide();
                $('#doc_userTypeValue').val('');
                $.each($("input[name=keyword]"), function (obj, value) {
                    $(value).val("");
                });
                $("input[name=option]").val("");
            } else {
                $('#doc_userType').hide();
                $('.searchform').show();
                $("input[name=keyword]").val("");
                $.each($("input[name=doc_userTypeValue]"), function (obj, value) {
                    $(value).val("");
                });
                $.each($("select[name=doc_userTypeValue]"), function (obj, value) {
                    $(value).val("");
                });
                $("input[name=option]").val("");
            }
        });
    });
    $(document).ready(function () {
        $('#eDocServiceType').hide();
        $('#doc_userTypeValue').each(function () {
            if (this.value == 'driver' && ENABLE_SERVICE_TYPE_WISE_PROVIDER_DOC == 'Yes') {
                $('#eDocServiceType').show();
                $('.searchform').hide();
            }
        });
    });
    $(function () {
        $('#doc_userTypeValue').change(function () {
            if ($('#doc_userTypeValue').val() == 'driver' && ENABLE_SERVICE_TYPE_WISE_PROVIDER_DOC == 'Yes') {
                $('#eDocServiceType').show();
                //$("input[name=keyword]").val("");
                /* $.each($("input[name=keyword]"), function(obj,value) {
                    $(value).val("");
                });
                $('.searchform').hide(); */
            } else {
                $('#eDocServiceType').hide();
                /*  $("#eType_value").val("");
                 $('.searchform').show(); */
            }
        });
    });
    $(document).ready(function () {
        $('#otherservice').hide();
        $('#eDocServiceTypeValue').each(function () {
            if (this.value == 'ServiceSpecific') {
                $('#otherservice').show();
                $('.searchform').hide();
            }
        });
    });
    $(function () {
        $('#eDocServiceType').change(function () {
            if ($('#eDocServiceTypeValue').val() == 'ServiceSpecific') {
                $('#otherservice').show();
            } else {
                $('#otherservice').hide();
            }
        });
    });
</script>
</body>
<!-- END BODY-->
</html>