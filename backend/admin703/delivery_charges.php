<?php
include_once('../common.php');

$script = 'Delivery Charges';

$view = 'view-delivery-charges';
$edit  = 'edit-delivery-charges';
$delete  = 'delete-delivery-charges';
$updateStatus  = 'update-status-delivery-charges';
$create  = 'create-delivery-charges';

$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$queryString = '';
if ($eType == 'runner') {
    $commonTxt = '-runner-delivery';
    $script = "RunnerDeliveryCharges";
    $queryString = 'eType=' . $eType;
} else if ($eType == 'genie') {
    $commonTxt = '-genie-delivery';
    $script = "GenieDeliveryCharges";
    $queryString = 'eType=' . $eType;
}

if(in_array($eType,['runner','genie'])){
    $view = $view.$commonTxt;
    $edit  = $edit.$commonTxt;
    $delete  =  $delete.$commonTxt;
    $updateStatus  =  $updateStatus.$commonTxt;
    $create  =  $create.$commonTxt;
}
if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$iDeliveyChargeId = isset($_REQUEST['iDeliveyChargeId']) ? $_REQUEST['iDeliveyChargeId'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$tbl_name = 'delivery_charges';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dc.iDeliveyChargeId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY lm1.vLocationName ASC"; else
        $ord = " ORDER BY lm1.vLocationName DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY dc.fOrderPriceValue ASC"; else
        $ord = " ORDER BY dc.fOrderPriceValue DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY dc.fDeliveryChargeAbove ASC"; else
        $ord = " ORDER BY dc.fDeliveryChargeAbove DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY dc.fDeliveryChargeBelow ASC"; else
        $ord = " ORDER BY dc.fDeliveryChargeBelow DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY dc.fFreeOrderPriceSubtotal ASC"; else
        $ord = " ORDER BY dc.fFreeOrderPriceSubtotal DESC";
}
if ($sortby == 6) {
    if ($order == 0) $ord = " ORDER BY dc.iFreeDeliveryRadius ASC"; else
        $ord = " ORDER BY dc.iFreeDeliveryRadius DESC";
}
if ($sortby == 7) {
    if ($order == 0) $ord = " ORDER BY dc.eStatus ASC"; else
        $ord = " ORDER BY dc.eStatus DESC";
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$ssql = $eStatussql = $ssql1 = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND dc.eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND dc.eStatus != 'Deleted'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (lm1.vLocationName LIKE '%" . $keyword . "%' OR dc.fOrderPriceValue LIKE '%" . $keyword . "%' OR dc.fDeliveryChargeAbove LIKE '%" . $keyword . "%' OR dc.fDeliveryChargeBelow LIKE '%" . $keyword . "%' OR dc.fFreeOrderPriceSubtotal LIKE '%" . $keyword . "%' OR dc.iFreeDeliveryRadius LIKE '%" . $keyword . "%') AND dc.eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND (lm1.vLocationName LIKE '%" . $keyword . "%' OR dc.fOrderPriceValue LIKE '%" . $keyword . "%' OR dc.fDeliveryChargeAbove LIKE '%" . $keyword . "%' OR dc.fDeliveryChargeBelow LIKE '%" . $keyword . "%' OR dc.fFreeOrderPriceSubtotal LIKE '%" . $keyword . "%' OR dc.iFreeDeliveryRadius LIKE '%" . $keyword . "%') AND dc.eStatus != 'Deleted'";
        }
    }
} else {
    if ($eStatus != '') {
        $ssql .= " AND dc.eStatus = '" . clean($eStatus) . "'";
    } else {
        $eStatussql = " AND dc.eStatus != 'Deleted'";
    }
}
// End Search Parameters
if (!$MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) {
    $ssql1 = " GROUP BY iLocationId ";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(dc.iLocationId) as Total FROM `delivery_charges` dc left join location_master lm1 on dc.iLocationId = lm1.iLocationId WHERE 1 = 1 $eStatussql $ssql $ssql1";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
$sql = "SELECT dc.*,lm1.vLocationName FROM `delivery_charges` dc left join location_master lm1 on dc.iLocationId = lm1.iLocationId WHERE 1 = 1 $eStatussql $ssql $ssql1 $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox') {
		$var_filter .= "&$key=" . stripslashes($val);
	}
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) ? "User" : "" ?> Delivery
        Charges
    </title>
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
                        <h2><?= ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) ? "User" : "" ?> Delivery
                            Charges
                        </h2>
                    </div>
                </div>
                <hr/>
                <?php if($MODULES_OBJ->isEnableGenieFeature('Yes') || $MODULES_OBJ->isEnableGenieFeature('Yes')) { ?>
                <div class="row">
                    <div class="col-lg-12" style="color: red;margin-bottom: 19px;">
                        Please note that delivery charges defined here will be applied into Delivery Genie, Delivery
                        Runner & Store Deliveries.
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <input type="hidden" name="eType" value="<?php echo $eType;?>">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="10%" class=" padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="lm1.vLocationName" <?php if ($option == "lm1.vLocationName") {
                                    echo "selected";
                                } ?> >Location
                                </option>
                                <option value="dc.fOrderPriceValue" <?php if ($option == "dc.fOrderPriceValue") {
                                    echo "selected";
                                } ?> >Order Amount
                                </option>
                            </select>
                        </td>
                        <td width="15%">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td width="12%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php if ($eStatus == 'Active') {
                                    echo "selected";
                                } ?> >Active
                                </option>
                                <option value="Inactive" <?php if ($eStatus == 'Inactive') {
                                    echo "selected";
                                } ?> >Inactive
                                </option>
                                <option value="Deleted" <?php if ($eStatus == 'Deleted') {
                                    echo "selected";
                                } ?> >Delete
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <?php $reloadurl = !empty($eType) ? "delivery_charges.php?eType=".$eType : "delivery_charges.php"; ?>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href='<?= $reloadurl;?>'"/>
                        </td>
                        <?php if ($userObj->hasPermission($create)) { ?>
                            <td width="20%">
                                <a class="add-btn" href="delivery_charges_action.php?<?php echo $queryString; ?>" style="text-align: center;">Add
                                    Delivery Charges
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
                                            <?php if ($userObj->hasPermission([$updateStatus, $delete])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onchange="ChangeStatusAll(this.value);">
                                                <option value="">Select Action</option>
                                                <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                    <option value='Active' <?php if ($option == 'Active') {
                                                        echo "selected";
                                                    } ?> >Activate</option>
                                                    <option value="Inactive" <?php if ($option == 'Inactive') {
                                                        echo "selected";
                                                    } ?> >Deactivate</option>
                                                <?php } ?>
                                                    <?php if ($userObj->hasPermission($delete)) { ?>
                                                        <option value="Deleted" <?php if ($option == 'Delete') {
                                                            echo "selected";
                                                        } ?> >Delete</option>
                                                    <?php } ?>
                                            </select>
                                            <?php } ?>
                                        </span>
                            </div>
                            <!--  <?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onclick="showExportTypes('delivery_charges')" >Export</button>
                                            </form>
                                        </div>
                                        <?php } ?> -->
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" 
				    action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                        <?php if ($userObj->hasPermission([$updateStatus, $delete])) { ?>
                                                    <th align="center" width="3%" style="text-align:center;">
                                                    <input type="checkbox" id="setAllCheck" >
						    </th>
                                        <?php } ?>
						    <th width="16%">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(1,<?php if($sortby == '1'){ 
						    echo $order; 
						    }else { ?>0<?php } ?>)">Location Name<?php if ($sortby == 1) { 
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    }else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
						    </th>
                                                    <?php if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?>
                                            <th width="7%">Distance Range (<?php echo $DEFAULT_DISTANCE_UNIT; ?>)</th>
                                                    <?php } ?>
                                        <th width="10%">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(2,<?php if($sortby == '2'){ 
						    echo $order; 
						    }else { ?>0<?php } ?>)">Order Price <?php if ($sortby == 2) { 
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    }else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
						</th>	
                                        <th width="10%">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(3,<?php if($sortby == '3'){ 
						    echo $order; 
						    } else { ?>0<?php } ?>)">Order Delivery Charges Above 
						    Amount<?php if ($sortby == 3) { 
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    }else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
							</th>
                                        <th width="10%">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(4,<?php if($sortby == '4'){ 
						    echo $order; 
						    } else { ?>0<?php } ?>)">Order Delivery Charges Below 
						    Amount<?php if ($sortby == 4) { 
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    } else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
							</th>
                                        <th width="10%">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(5,<?php if($sortby == '5'){ 
						    echo $order; 
						    }else { ?>0<?php } ?>)">Free Order Delivery 
						    Charges<?php if ($sortby == 5) { 
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    }else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
							</th>
                                        <th width="10%">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(6,<?php if($sortby == '6'){ 
						    echo $order; 
						    }else { ?>0<?php } ?>)"> Free Delivery Radius<?php if ($sortby == 6) {
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    }else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
							</th>
                            <?php if($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                                <th width="12%">Delivery Charges for Delivery Genie/Runner Feature for Completed Orders</th>

                                <th width="12%">Delivery Charges for Delivery Genie/Runner Feature for Cancelled  Orders </th>

                            <?php } ?>
                            <th width="5%" align="center" style="text-align:center;">
						    <a href="javascript:void(0);" 
						    onClick="Redirect(7,<?php if($sortby == '7'){ 
						    echo $order; 
						    }else { ?>0<?php } ?>)">Status <?php if ($sortby == 7) { 
						    if($order == 0) { ?>
						    <i class="fa fa-sort-amount-asc" 
						    aria-hidden="true"></i> <?php } else { ?>
						    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } 
						    }else { ?>
						    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
							</th>
                                        <?php if ($userObj->hasPermission([$edit, $updateStatus,$delete])) { ?>
                                                    <th width="5%" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    if(!empty($data_drv)) {
                                                    for ($i = 0; $i < scount($data_drv); $i++) {
                                                    
                                                        if($data_drv[$i]['iDistanceRangeTo'] == '100000000') {
                                                            $data_drv[$i]['iDistanceRangeTo'] = "&#8734"; 
                                                            $data_drv[$i]['iDistanceRangeTo'] = $data_drv[$i]['iDistanceRangeTo'];
                                                        } 
                                                        
                                                    ?>
                                                <tr class="gradeA">
                                                <?php if ($userObj->hasPermission([$updateStatus, $delete])) { ?>
                                                <td align="center" style="text-align:center;">
                                                    <input type="checkbox" id="checkbox" name="checkbox[]" value="<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>"/>&nbsp;
                                                </td>
                                                <?php } ?>
                                                <td>
                                                    <?php if ($data_drv[$i]['iLocationId'] == '0') {
                                                        echo 'All Location';
                                                    } else {
                                                        echo $data_drv[$i]['vLocationName'];
                                                    } ?>
                                                </td>
                                                <?php if ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?>
                                                    <td><?= $data_drv[$i]['iDistanceRangeFrom'] . ' - ' . $data_drv[$i]['iDistanceRangeTo']; ?></td>
                                                <?php } ?>
                                                <td><?= formateNumAsPerCurrency($data_drv[$i]['fOrderPriceValue'], ''); ?></td>
                                                <td><?= formateNumAsPerCurrency($data_drv[$i]['fDeliveryChargeAbove'], ''); ?></td>
                                                <td><?= formateNumAsPerCurrency($data_drv[$i]['fDeliveryChargeBelow'], ''); ?></td>
                                                <?php //added by SP 27-06-2019 for remove validation to leave blank ?>
                                                <td><?php if (!empty($data_drv[$i]['fFreeOrderPriceSubtotal']) && $data_drv[$i]['fFreeOrderPriceSubtotal'] != 0) echo formateNumAsPerCurrency($data_drv[$i]['fFreeOrderPriceSubtotal'], ''); else echo ''; ?></td>
                                                <td><?php if (!empty($data_drv[$i]['iFreeDeliveryRadius']) && $data_drv[$i]['iFreeDeliveryRadius'] != 0) echo $data_drv[$i]['iFreeDeliveryRadius']; else echo ''; ?></td>
                                                <?php if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                                                    <td><?= formateNumAsPerCurrency($data_drv[$i]['fDeliveryChargeBuyAnyService'], ''); ?></td>
                                                    <td><?= formateNumAsPerCurrency($data_drv[$i]['fDeliveryChargeBuyAnyServiceCancelledOrder'], ''); ?></td>
                                                <?php } ?>
                                                <td align="center" style="text-align:center;">
                                                    <?php if ($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    } ?>
                                                    <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus']; ?>"
                                                         data-toggle="tooltip" title="<?= $data_drv[$i]['eStatus']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission([$edit, $updateStatus, $delete])) { ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export">
                                                                <span><img src="images/settings-icon.png" alt=""></span>
                                                            </label>
                                                            <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iDeliveyChargeId']; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission($edit)) { ?>
                                                                        <li class="entypo-twitter"
                                                                            data-network="twitter">
                                                                            <a href="delivery_charges_action.php?id=<?= $data_drv[$i]['iDeliveyChargeId']; ?>&<?php echo $queryString; ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                        <li class="entypo-facebook"
                                                                            data-network="facebook">
                                                                            <a href="javascript:void(0);"
                                                                               onclick="changeStatus('<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>','Inactive')"
                                                                               data-toggle="tooltip"
                                                                               title="Activate">
                                                                                <img src="img/active-icon.png"
                                                                                     alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                            </a>
                                                                        </li>
                                                                        <li class="entypo-gplus"
                                                                            data-network="gplus">
                                                                            <a href="javascript:void(0);"
                                                                               onclick="changeStatus('<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>','Active')"
                                                                               data-toggle="tooltip"
                                                                               title="Deactivate">
                                                                                <img src="img/inactive-icon.png"
                                                                                     alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($userObj->hasPermission($delete)) { ?>
                                                                        <li class="entypo-gplus"
                                                                            data-network="gplus">
                                                                            <a href="javascript:void(0);"
                                                                               onclick="changeStatusDelete('<?php echo $data_drv[$i]['iDeliveyChargeId']; ?>')"
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
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr class="gradeA">
                                            <?php if ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?>
                                                <td colspan="11"> No Records Found.</td>
                                            <?php } else { ?>
                                                <td colspan="9"> No Records Found.</td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>1. Administrator can Activate / Deactivate / Modify / Delete any delivery charge.</li>
                    <li>
                        2. Set delivery charges as per the location. Ex. delivery charges for city California. You can
                        define the location from Manage location -> Geo fence location.
                    </li>
                    <li>
                        3. You can define the order range for delivery charges from this module. Ex. delivery charges $5
                        will apply on all orders below $20. Or delivery charges $3 will apply on all orders above $20.
                    </li>
                    <li>
                        4. You can also define free delivery based on order amount. Say, free delivery on all orders
                        above $100.
                    </li>
                    <li>
                        5. You can also define the free delivery radius. Ex. distance
                        from <?php echo strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']); ?>
                        to <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>'s location up to 1 KM will be
                        free.
                    </li>
                    <li>
                        6. Make sure you define the delivery charges for all the areas in which you are going to provide
                        the service.
                    </li>
                    <?php if ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?>
                        <li>7. You can define the distance range for delivery charges as per locations.
                            <br>
                            Ex. 0 <?php echo $DEFAULT_DISTANCE_UNIT; ?> - 5 <?php echo $DEFAULT_DISTANCE_UNIT; ?> for
                            city California, delivery charges $5 will apply on all orders below $20. Or delivery charges
                            $3 will apply on all orders above $20.
                            <br>
                            5 <?php echo $DEFAULT_DISTANCE_UNIT; ?> - 15 <?php echo $DEFAULT_DISTANCE_UNIT; ?> for city
                            California, delivery charges $10 will apply on all orders below $50. Or delivery charges $5
                            will apply on all orders above $50.
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/delivery_charges.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iDeliveyChargeId" id="iMainId01" value="">
    <input type="hidden" name="iLocationId" id="iMainId02" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="eType" value="<?php echo $eType; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<?php
include_once('footer.php');
?>
<script>
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
        // alert(action);
        var formValus = $("#frmsearch").serialize();
        //               alert(action+formValus);
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