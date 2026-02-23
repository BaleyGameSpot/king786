<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-cancelled-orders')) {
    $userObj->redirect();
}
$script = 'CancelledOrders';
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY o.iOrderId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY o.tOrderRequestDate ASC";
    else
        $ord = " ORDER BY o.tOrderRequestDate DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY o.eCancelledBy ASC";
    else
        $ord = " ORDER BY o.eCancelledBy DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY o.vCancelReason ASC";
    else
        $ord = " ORDER BY o.vCancelReason DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY d.vName ASC";
    else
        $ord = " ORDER BY d.vName DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$searchOrderNo = isset($_REQUEST['searchOrderNo']) ? $_REQUEST['searchOrderNo'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
    }
    if ($iDriverId != '') {
        $ssql .= " AND o.iDriverId ='" . $iDriverId . "'";
    }
    if ($searchOrderNo != '') {
        $ssql .= " AND o.vOrderNo ='" . $searchOrderNo . "'";
    }
    if ($searchServiceType != '' && !in_array($searchServiceType, [
            'Genie',
            'Runner',
            'Anywhere'
        ])
    ) {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "' AND o.eBuyAnyService ='No'";
    }
    if ($searchServiceType == "Genie") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
    }
    if ($searchServiceType == "Runner") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
    }
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}
$ssql .= " AND sc.iServiceId IN(" . $enablesevicescategory . ")";
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(o.iOrderId) AS Total FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId  LEFT JOIN service_categories as sc on sc.iServiceId=o.iServiceId WHERE 1=1 AND o.iStatusCode IN ('9','8') $ssql $trp_ssql";
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
$sql = "select vName,vSymbol from currency where eStatus='Active' AND eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
$default_currency = $db_currency[0]['vName'];
$vSymbol = $db_currency[0]['vSymbol'];
$sql = "SELECT o.iOrderId,o.iUserId, o.iStatusCode,o.iCompanyId, sc.vServiceName_" . $default_lang . " as vServiceName, o.tOrderRequestDate,o.fOutStandingAmount ,o.fTotalGenerateFare ,o.fNetTotal ,o.fCancellationCharge ,o.dDeliveryDate,o.iCancelledById, o.iReasonId, o.vCancelReason, d.iDriverId, o.vOrderNo,o.ePayWallet, o.ePaymentOption, o.fRefundAmount, o.eCancelledBy, o.fWalletDebit,o.eBuyAnyService, o.fDeliveryChargeCancelled, o.eForPickDropGenie, os.vStatus, o.fRatio_" . $default_currency . " as fRatio ,CONCAT(d.vName,' ',d.vLastName) AS dName,CONCAT(u.vName,' ',u.vLastName) AS UserName,c.vCompany,CONCAT(u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT(d.vCode,' ',d.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, o.vTimeZone FROM orders o LEFT JOIN register_driver d ON d.iDriverId =o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('9','8') $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = scount($db_trip);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$Pmonday = date('Y-m-d', strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d', strtotime('sunday this week -1 week'));
//print_r($_REQUEST);die;
$refundorderid = isset($_REQUEST['refundorderid']) ? $_REQUEST['refundorderid'] : '';
if ($action == 'refund' && $refundorderid != '') {
    //$fCancellationCharge = isset($_REQUEST['fCancellationCharge']) ? $_REQUEST['fCancellationCharge'] : '';
    $fRefundAmount = isset($_REQUEST['fRefundAmount']) ? $_REQUEST['fRefundAmount'] : '';
    $vIP = get_client_ip();
    $query = "UPDATE orders SET iStatusCode = '7' ,fRefundAmount='" . $fRefundAmount . "' WHERE iOrderId = '" . $refundorderid . "'";
    $obj->sql_query($query);
    $lquery = "INSERT INTO `order_status_logs`(`iOrderId`, `iStatusCode`, `dDate`, `vIp`) VALUES ('" . $refundorderid . "','7',Now(),'" . $vIP . "')";
    $obj->sql_query($lquery);
    echo "<script>location.href='cancelled_orders.php'</script>";
}
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
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
    <title><?= $SITE_NAME ?> | Cancelled <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <? include_once('global_files.php'); ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Cancelled <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?></h2>
                </div>
            </div>
            <hr/>
            <div class="">
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <?php include('valid_msg.php'); ?>
                                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" id="cancel_trip">
                                    <div class="Posted-date mytrip-page mytrip-page-select payment-report">
                                        <input type="hidden" name="action" value="search"/>
                                        <h3><?= $langage_lbl_admin['LBL_MYTRIP_SEARCH_RIDES_POSTED_BY_DATE']; ?></h3>
                                        <span>
                                                    <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                                    <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                                    <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                                    <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                                    <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                                    <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                                    <a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                                    <a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                                                </span>
                                        <span>
                                                    <input type="text" id="dp4" name="startDate" placeholder="From Date"
                                                           class="form-control" value=""/>
                                                    <input type="text" id="dp5" name="endDate" placeholder="To Date"
                                                           class="form-control" value=""/>
                                                    <div class="col-lg-2 select001">
                                                        <select class="form-control filter-by-text" name='iDriverId'
                                                                data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>"
                                                                id="searchDriver">
                                                            <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                                            <!--  <?php foreach ($db_drivers as $dbd) { ?>
                                                                <option value="<?= $dbd['iDriverId']; ?>" <?php
                                                                if ($iDriverId == $dbd['iDriverId']) {
                                                                    echo "selected";
                                                                }
                                                                ?>><?= clearName($dbd['driverName']); ?> - ( <?= clearEmail($dbd['vEmail']); ?> )</option>
                                                                    <?php } ?> -->
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <input type="text" id="searchOrderNo" name="searchOrderNo"
                                                               placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> Number"
                                                               class="form-control search-trip001"
                                                               value="<?= $searchOrderNo; ?>"/>
                                                    </div>
                                                </span>
                                    </div>
                                    <? if (scount($allservice_cat_data) > 1) { ?>
                                        <div class="mytrip-page payment-report payment-report1">
                                                    <span>
                                                        <div class="col-lg-2 select001" style="padding-right:15px;">
                                                            <select class="form-control filter-by-text"
                                                                    name="searchServiceType"
                                                                    data-text="Select Serivce Type"
                                                                    id="searchServiceType">
                                                                <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                                                <?php foreach ($allservice_cat_data as $value) { ?>
                                                                    <option value="<?= $value['iServiceId']; ?>" <?php
                                                                    if ($searchServiceType == $value['iServiceId']) {
                                                                        echo "selected";
                                                                    }
                                                                    ?>><?= clearName($value['vServiceName']); ?></option>
                                                                <?php } ?>
                                                                <?php if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                                                                    <option value="Genie" <?php
                                                                    if ($searchServiceType == "Genie") {
                                                                        echo "selected";
                                                                    }
                                                                    ?>><?= clearName($langage_lbl_admin['LBL_OTHER_DELIVERY']); ?></option>
                                                                    <option value="Runner" <?php
                                                                    if ($searchServiceType == "Runner") {
                                                                        echo "selected";
                                                                    }
                                                                    ?>><?= clearName($langage_lbl_admin['LBL_RUNNER']); ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </span>
                                        </div>
                                    <? } ?>
                                    <div class="tripBtns001">
                                        <b>
                                            <input type="submit" value="Search" class="btnalt button11" id="Search"
                                                   name="Search" title="Search"/>
                                            <input type="button" value="Reset" class="btnalt button11"
                                                   onClick="window.location.href = 'cancelled_orders.php'"/>
                                            <?php if (!empty($db_trip)) { ?>
                                            <button type="button" onclick="showExportTypes('cancelled_orders')" class="export-btn001">Export</button>
                                            <?php } ?>
                                        </b>
                                    </div>
                                </form>
                                <form name="_list_form" class="_list_form" id="_list_form" method="post"
                                      action="<?= $_SERVER['PHP_SELF'] ?>">
                                    <table class="table table-striped table-bordered table-hover"
                                           id="dataTables-example">
                                        <thead>
                                        <tr>
                                            <? if (scount($allservice_cat_data) > 1) { ?>
                                                <th class="text-center"><?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?>
                                                    Service Type
                                                </th>
                                            <? } ?>
                                            <th class="text-center"><?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?>
                                                No#
                                            </th>
                                            <th class="text-center">
                                                <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                if ($sortby == '1') {
                                                    echo $order;
                                                }
                                                else {
                                                    ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN_DL']; ?> <?php
                                                    if ($sortby == 1) {
                                                        if ($order == 0) {
                                                            ?><i class="fa fa-sort-amount-asc"
                                                                 aria-hidden="true"></i> <?php } else { ?><i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                        }
                                                    }
                                                    else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                            <th><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                if ($sortby == '3') {
                                                    echo $order;
                                                }
                                                else {
                                                    ?>0<?php } ?>)">User Name <?php
                                                    if ($sortby == 3) {
                                                        if ($order == 0) {
                                                            ?><i class="fa fa-sort-amount-asc"
                                                                 aria-hidden="true"></i> <?php } else { ?><i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                        }
                                                    }
                                                    else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                            <th><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                if ($sortby == '2') {
                                                    echo $order;
                                                }
                                                else {
                                                    ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>
                                                    Name <?php
                                                    if ($sortby == 2) {
                                                        if ($order == 0) {
                                                            ?><i class="fa fa-sort-amount-asc"
                                                                 aria-hidden="true"></i> <?php } else { ?><i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                        }
                                                    }
                                                    else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                            <th width="12%">
                                                <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                if ($sortby == '4') {
                                                    echo $order;
                                                }
                                                else {
                                                    ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                                    Name <?php
                                                    if ($sortby == 4) {
                                                        if ($order == 0) {
                                                            ?><i class="fa fa-sort-amount-asc"
                                                                 aria-hidden="true"></i> <?php } else { ?><i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php
                                                        }
                                                    }
                                                    else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                            <th class="text-right">Order Total</th>
                                            <!-- <th>Service Type</th> -->
                                            <th class="text-center">Order Status</th>
                                            <th class="text-center">Payment Mode</th>
                                            <th class="text-center">Cancellation &amp; Refund Details</th>
                                            <?php if ($userObj->hasPermission('manage-mark-as-refunded-orders-cancelled-orders')) { ?>
                                                <th class="text-center">Action</th>
                                            <?php } ?>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?
                                        if (!empty($db_trip)) {
                                            //Added By HJ On 21-09-2020 For Optimize For Loop Query Start
                                            $cancleReasonArr = $orderStatusCodeArr = array();
                                            $cancelReasonsData = $obj->MySQLSelect("SELECT vTitle_" . $default_lang . " AS cancelReason,iCancelReasonId FROM cancel_reason WHERE 1=1");
                                            for ($r = 0; $r < scount($cancelReasonsData); $r++) {
                                                $cancleReasonArr[$cancelReasonsData[$r]['iCancelReasonId']] = $cancelReasonsData[$r]['cancelReason'];
                                            }
                                            //echo "<pre>";print_r($cancleReasonArr);die;
                                            $orderStatusCodes = $obj->MySQLSelect("SELECT GROUP_CONCAT(iStatusCode) as statusCodes,iOrderId FROM order_status_logs WHERE 1=1 GROUP BY iOrderId");
                                            for ($s = 0; $s < scount($orderStatusCodes); $s++) {
                                                $orderStatusCodeArr[$orderStatusCodes[$s]['iOrderId']] = $orderStatusCodes[$s]['statusCodes'];
                                            }
                                            //echo "<pre>";print_r($orderStatusCodeArr);die;
                                            //Added By HJ On 21-09-2020 For Optimize For Loop Query End
                                            for ($i = 0; $i < scount($db_trip); $i++) {
                                                $vCancelReason = $db_trip[$i]['vCancelReason'];
                                                $iReasonId = $db_trip[$i]['iReasonId'];
                                                if ($vCancelReason == '') {
                                                    //$cancelReasons = $obj->MySQLSelect("SELECT vTitle_" . $default_lang . " FROM cancel_reason WHERE iCancelReasonId = '" . $iReasonId . "'");
                                                    //$trip_cancel = $cancelReasons[0]['vTitle_' . $default_lang];
                                                    if (isset($cancleReasonArr[$iReasonId])) {
                                                        $trip_cancel = $cancleReasonArr[$iReasonId];
                                                    }
                                                }
                                                else {
                                                    $trip_cancel = $vCancelReason;
                                                }
                                                $CanceledByPerson = $db_trip[$i]['eCancelledBy'];
                                                if ($CanceledByPerson == 'Passenger') {
                                                    if (!empty($db_trip[$i]['UserName'])) {
                                                        $CanceledBy = $CanceledByPerson . " ( " . $db_trip[$i]['UserName'] . " ) ";
                                                    }
                                                    else {
                                                        $CanceledBy = $CanceledByPerson;
                                                    }
                                                }
                                                else if ($CanceledByPerson == 'Company') {
                                                    if (!empty($db_trip[$i]['vCompany'])) {
                                                        if(ONLYDELIVERALL == "Yes"){
                                                            $store = "Restaurants";
                                                        }else{
                                                            $store = "Store";
                                                        }
                                                        $CanceledBy = $store . " ( " . $db_trip[$i]['vCompany'] . " ) ";
                                                    }
                                                    else {
                                                        $CanceledBy = $CanceledByPerson;
                                                    }
                                                }
                                                else if ($CanceledByPerson == 'Driver') {
                                                    if (!empty($db_trip[$i]['dName'])) {
                                                        $CanceledBy = $CanceledByPerson . " ( " . $db_trip[$i]['dName'] . " ) ";
                                                    }
                                                    else {
                                                        $CanceledBy = $CanceledByPerson;
                                                    }
                                                }
                                                else {
                                                    $CanceledBy = $CanceledByPerson;
                                                }
                                                $fNetTotal = $db_trip[$i]['fNetTotal'];
                                                $fWalletDebit = $db_trip[$i]['fWalletDebit'];
                                                $fCancellationCharge = $db_trip[$i]['fCancellationCharge'];
                                                $fOutStandingAmount = $db_trip[$i]['fOutStandingAmount'];
                                                if ($db_trip[$i]['eBuyAnyService'] == "Yes") {
                                                    //$orderStatusCodes = $obj->MySQLSelect("SELECT GROUP_CONCAT(iStatusCode) as statusCodes FROM order_status_logs WHERE iOrderId = ".$db_trip[$i]['iOrderId']);
                                                    $statusCodes = "";
                                                    if (isset($orderStatusCodeArr[$db_trip[$i]['iOrderId']])) {
                                                        $statusCodes = $orderStatusCodeArr[$db_trip[$i]['iOrderId']];
                                                    }
                                                    $order_status_codes = explode(',', $statusCodes);

                                                    if ($db_trip[$i]['eCancelledBy'] != 'Admin' && scount(array_intersect($order_status_codes, [ 1, 2 ])) > 0 && scount(array_intersect($order_status_codes, [ 13, 14, 5 ])) == 0
                                                    )
                                                    {


                                                        $fCancellationCharge = 0;
                                                    }
                                                    else {
                                                        $fCancellationCharge = $db_trip[$i]['fDeliveryChargeCancelled'];
                                                    }
                                                    $fOutStandingAmount = 0;
                                                }

                                                $refundedamt = $fNetTotal + $fWalletDebit - $fCancellationCharge - $fOutStandingAmount;
                                                $LBL_RESTAURANT_TXT = $langage_lbl_admin["LBL_RESTAURANT_TXT"];
                                                if (scount($allservice_cat_data) > 1) {
                                                    $vServiceName = $db_trip[$i]['vServiceName'];
                                                }
                                                if ($db_trip[$i]['eBuyAnyService'] == "Yes") {
                                                    $vServiceName = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
                                                    if ($db_trip[$i]['eForPickDropGenie'] == "Yes") {
                                                        $vServiceName = $langage_lbl_admin['LBL_RUNNER'];
                                                    }
                                                }
                                                ?>
                                                <tr class="gradeA">
                                                    <? if (scount($allservice_cat_data) > 1) { ?>
                                                        <td align="center"><?= $vServiceName; ?></td>
                                                    <? } ?>
                                                    <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                        <td align="center"><a
                                                                    href="order_invoice.php?iOrderId=<?= $db_trip[$i]['iOrderId'] ?>"
                                                                    target="_blank"><?= $db_trip[$i]['vOrderNo']; ?></a>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td align="center"><?= $db_trip[$i]['vOrderNo']; ?></td>
                                                    <?php } ?>
                                                    <td><?
                                                        $systemTimeZone = date_default_timezone_get();
                                                        $vTimeZone = $db_trip[$i]['vTimeZone'];
                                                        $date_format_data_array = array(
                                                            'tdate' => converToTz($db_trip[$i]['tOrderRequestDate'], $vTimeZone, $systemTimeZone),
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $get_order_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($vTimeZone,$date_format_data_array['tdate']).")";
                                                        echo $get_order_format['tDisplayDateTime'].$time_zone_difference_text;
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($db_trip[$i]['user_phone'] != '') {
                                                            if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['UserName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php }
                                                            // echo clearName($db_trip[$i]['UserName']);
                                                            echo '<br>';
                                                            echo '<b>Phone :</b> +' . clearPhone($db_trip[$i]['user_phone']);
                                                        }
                                                        else {
                                                            if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['UserName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php }
                                                            // echo clearName($db_trip[$i]['UserName']);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td data-order="<?= $db_trip[$i]['iOrderId'] ?>">
                                                        <?php
                                                        if (!empty($db_trip[$i]['resturant_phone'])) {
                                                            if ($userObj->hasPermission('view-store') && isset($DBProcessingOrders[$i]['eBuyAnyService']) && $DBProcessingOrders[$i]['eBuyAnyService'] == 'No') { ?>
                                                                <a href="javascript:void(0);" onClick="show_store_details('<?= $db_trip[$i]['iCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearCmpName($db_trip[$i]['vCompany']); ?><?php if ($userObj->hasPermission('view-store') && isset($DBProcessingOrders[$i]['eBuyAnyService']) && $DBProcessingOrders[$i]['eBuyAnyService'] == 'No') { ?></a>
                                                            <?php }
                                                            if(!empty(trim($db_trip[$i]['resturant_phone']))) {
                                                                //echo clearName($db_trip[$i]['vCompany']);
                                                                echo '<br>';
                                                                echo '<b>Phone :</b> +' . clearPhone($db_trip[$i]['resturant_phone']);
                                                            }
                                                        }
                                                        else {
                                                            if ($userObj->hasPermission('view-store') && isset($DBProcessingOrders[$i]['eBuyAnyService']) && $DBProcessingOrders[$i]['eBuyAnyService'] == 'No') { ?><a href="javascript:void(0);" onClick="show_store_details('<?= $db_trip[$i]['iCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearCmpName($db_trip[$i]['vCompany']); ?><?php if ($userObj->hasPermission('view-store') && $DBProcessingOrders[$i]['eBuyAnyService'] == 'No') { ?></a><?php }
                                                            //echo clearName($db_trip[$i]['vCompany']);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($db_trip[$i]['driver_phone'] != '') {
                                                            //echo clearName($db_trip[$i]['dName']);
                                                            if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['dName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php }
                                                            echo '<br>';
                                                            echo '<b>Phone :</b> +' . clearPhone($db_trip[$i]['driver_phone']);
                                                        }
                                                        else {
                                                            if ($db_trip[$i]['dName'] != '') {
                                                                //echo clearName($db_trip[$i]['dName']);
                                                                if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['dName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php }
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="right">
                                                    <?php
                                                    if ($db_trip[$i]['ePayWallet'] == "Yes") {
                                                        $fNetTotalwallet = $db_trip[$i]['fNetTotal'] + $db_trip[$i]['fWalletDebit'];
                                                        echo formateNumAsPerCurrency($fNetTotalwallet, '');
                                                    } else {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['fNetTotal'], '');
                                                    } ?> 
                                                </td>

                                                    <!-- <td class="text-center"><?= $db_trip[$i]['vServiceName'] ?></td>    -->

                                                    <td align="center"><?= str_replace("#STORE#", $langage_lbl_admin["LBL_RESTAURANT_TXT"], $db_trip[$i]['vStatus']) ?></td>
                                                    <?php
                                                    //Added By HJ On 09-08-2019 For Set Payment Method When Set System Payment Flow 2 Or 3 Start Bug - 6710
                                                    //echo "<pre>";print_r($db_trip[$i]);die;
                                                    $ePaymentOption = $db_trip[$i]['ePaymentOption'];
                                                    if ($db_trip[$i]['ePayWallet'] == "Yes") {
                                                        $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                                                    }
                                                    //Added By HJ On 09-08-2019 For Set Payment Method When Set System Payment Flow 2 Or 3 End Bug - 6710
                                                    ?>
                                                    <td align="center"><?= $ePaymentOption; ?></td>
                                                    <td class="text-center"><a href="#" class="btn btn-info"
                                                                               data-toggle="modal"
                                                                               data-target="#uiModal_<?= $db_trip[$i]['iOrderId']; ?>">View
                                                            Details</a>
                                                        <div class="modal fade text-left"
                                                             id="uiModal_<?= $db_trip[$i]['iOrderId']; ?>" tabindex="-1"
                                                             role="dialog" aria-labelledby="myModalLabel"
                                                             aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close"
                                                                                data-dismiss="modal">x
                                                                        </button>
                                                                        <h4 class="modal-title">Cancel &amp; Refund
                                                                            Details</h4>
                                                                    </div>
                                                                    <div class="modal-body">

                                                                        <div class="form-group col-lg-12"
                                                                             style="display: inline-block;">
                                                                            <div class="row"
                                                                                 style="padding-bottom: 10px">
                                                                                <label class="col-lg-6 control-label">Cancel
                                                                                    By :</label>
                                                                                <div class="col-lg-6">&nbsp;&nbsp;&nbsp;
                                                                                    <?php
                                                                                    if (!empty($CanceledBy)) {
                                                                                        echo $CanceledBy;
                                                                                    }
                                                                                    else {
                                                                                        echo '--';
                                                                                    }
                                                                                    ?>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row"
                                                                                 style="padding-bottom: 10px">
                                                                                <label class="col-lg-6 control-label">Cancel
                                                                                    Reason :</label>
                                                                                <div class="col-lg-6">&nbsp;&nbsp;&nbsp;
                                                                                    <?php
                                                                                    if (!empty($trip_cancel)) {
                                                                                        echo $trip_cancel;
                                                                                    }
                                                                                    else {
                                                                                        echo '--';
                                                                                    }
                                                                                    ?>
                                                                                </div>
                                                                            </div>

                                                                            <?php
                                                                            if ($db_trip[$i]['ePaymentOption'] == 'Cash' && $db_trip[$i]['fWalletDebit'] > 0) { ?>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <?php if ($db_trip[$i]['eBuyAnyService'] == 'No') { ?>
                                                                                        <label class="col-lg-6 control-label">Order
                                                                                            Total Amount :</label>
                                                                                        <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($db_trip[$i]['fNetTotal'], ''); ?>
                                                                                        </div>
                                                                                    <?php } else { ?>
                                                                                        <label class="col-lg-6 control-label">Order
                                                                                            Subtotal Amount :</label>
                                                                                        <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($db_trip[$i]['fNetTotal'] - $db_trip[$i]['fOutStandingAmount'], ''); ?>
                                                                                        </div>
                                                                                    <?php } ?>

                                                                                </div>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Wallet
                                                                                        Adjustment :</label>
                                                                                    <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($db_trip[$i]['fWalletDebit'], ''); ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Cancellation
                                                                                        Charges :</label>
                                                                                    <?php 
                                                                                    if ($db_trip[$i]['eBuyAnyService'] == 'No' || $db_trip[$i]['eCancelledBy'] == "Admin") { ?>
                                                                                        <div class="col-lg-6">
                                                                                            - <?= formateNumAsPerCurrency($db_trip[$i]['fCancellationCharge'], ''); ?>
                                                                                        </div>
                                                                                    <?php } else { ?>
                                                                                        <div class="col-lg-6">
                                                                                            - <?= formateNumAsPerCurrency($fCancellationCharge, ''); ?>
                                                                                        </div>
                                                                                    <?php } ?>
                                                                                </div>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Outstanding
                                                                                        Amount :</label>
                                                                                    <div class="col-lg-6"><?php if ($db_trip[$i]['eBuyAnyService'] == 'No') { ?> - <?php } else { ?>&nbsp;&nbsp;&nbsp;<?php } ?><?= formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], ''); ?>
                                                                                    </div>
                                                                                </div>
                                                                                <hr/>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Total
                                                                                        Amount To be Refunded :</label>
                                                                                    <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($refundedamt, ''); ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php }
                                                                            if ($db_trip[$i]['ePaymentOption'] == 'Cash' && $db_trip[$i]['fWalletDebit'] == 0) { ?>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Cancellation
                                                                                        Charges :</label>
                                                                                    <?php if ($db_trip[$i]['eBuyAnyService'] == 'No' || $db_trip[$i]['eCancelledBy'] == "Admin") { ?>
                                                                                        <div class="col-lg-6">&nbsp;&nbsp;&nbsp; <?= formateNumAsPerCurrency($db_trip[$i]['fCancellationCharge'], ''); ?>
                                                                                        </div>
                                                                                    <?php } else { ?>
                                                                                        <div class="col-lg-6">&nbsp;&nbsp;&nbsp; <?= formateNumAsPerCurrency($fCancellationCharge, ''); ?>
                                                                                        </div>
                                                                                    <?php } ?>
                                                                                </div>
                                                                            <? }
                                                                            if ($db_trip[$i]['ePaymentOption'] == 'Card') { ?>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <?php if ($db_trip[$i]['eBuyAnyService'] == 'No') { ?>
                                                                                        <label class="col-lg-6 control-label">Order
                                                                                            Total Amount :</label>
                                                                                        <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($db_trip[$i]['fNetTotal'], ''); ?>
                                                                                        </div>
                                                                                    <?php } else { ?>
                                                                                        <label class="col-lg-6 control-label">Order
                                                                                            Subtotal Amount :</label>
                                                                                        <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($db_trip[$i]['fNetTotal'] - $db_trip[$i]['fOutStandingAmount'], ''); ?>
                                                                                        </div>
                                                                                    <?php } ?>

                                                                                </div>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Wallet
                                                                                        Adjustment :</label>
                                                                                    <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($db_trip[$i]['fWalletDebit'], ''); ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Cancellation
                                                                                        Charges :</label>
                                                                                    <?php if ($db_trip[$i]['eBuyAnyService'] == 'No' || $db_trip[$i]['eCancelledBy'] == "Admin") { ?>
                                                                                        <div class="col-lg-6">
                                                                                            - <?= formateNumAsPerCurrency($db_trip[$i]['fCancellationCharge'], ''); ?>
                                                                                        </div>
                                                                                    <?php } else { ?>
                                                                                        <div class="col-lg-6">
                                                                                            - <?= formateNumAsPerCurrency($fCancellationCharge, ''); ?>
                                                                                        </div>
                                                                                    <?php } ?>
                                                                                </div>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Outstanding
                                                                                        Amount :</label>
                                                                                    <div class="col-lg-6"><?php if ($db_trip[$i]['eBuyAnyService'] == 'No') { ?> - <?php } else { ?>&nbsp;&nbsp;&nbsp;<?php } ?><?= formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], ''); ?>
                                                                                    </div>
                                                                                </div>
                                                                                <hr/>
                                                                                <div class="row"
                                                                                     style="padding-bottom: 10px">
                                                                                    <label class="col-lg-6 control-label">Total
                                                                                        Amount To be Refunded :</label>
                                                                                    <div class="col-lg-6">&nbsp;&nbsp;&nbsp;<?= formateNumAsPerCurrency($refundedamt, ''); ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php } ?>
                                                                        </div>

                                                                        <!-- <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="" name="frm6"> -->
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <!-- <button type="submit" class="btn btn-info" id="cnl_booking" title="Refund Booking">Refund Order</button> -->
                                                                        <button type="button" class="btn btn-default"
                                                                                data-dismiss="modal">Close
                                                                        </button>
                                                                        <!-- <input type="button" class="save" data-dismiss="modal" name="cancel" value="Close"> -->
                                                                    </div>
                                                                    <!-- </form> -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if ($db_trip[$i]['iStatusCode'] != '7') {
                                                            if ($db_trip[$i]['ePaymentOption'] == 'Card' || ($db_trip[$i]['fWalletDebit'] > 0 && $db_trip[$i]['eCancelledBy'] != 'Admin')) {
                                                                ?>
                                                                <?php if ($userObj->hasPermission('manage-mark-as-refunded-orders-cancelled-orders')) { ?>
                                                                    <button type="button" class="btn btn-info"
                                                                            data-toggle="modal"
                                                                            data-target="#refund_form<?= $db_trip[$i]['iOrderId']; ?>">
                                                                        Mark As Refunded
                                                                    </button>
                                                                <?php } ?>
                                                                <?php
                                                            }
                                                            else {
                                                                echo '--';
                                                            }
                                                        }
                                                        else {
                                                            ?>
                                                            Amount Refunded (<?= formateNumAsPerCurrency($db_trip[$i]['fRefundAmount'], ''); ?>)
                                                        <?php } ?>
                                                        <!-- Modal -->
                                                        <div id="refund_form<?= $db_trip[$i]['iOrderId']; ?>"
                                                             class="modal fade refund_form text-left" role="dialog">
                                                            <div class="modal-dialog">
                                                                <!-- Modal content-->
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close"
                                                                                data-dismiss="modal">x
                                                                        </button>
                                                                        <h4 class="modal-title">Refund
                                                                            to <?= $langage_lbl_admin['LBL_RIDER']; ?>
                                                                            ?</h4>
                                                                    </div>
                                                                    <form role="form" name="refund_form"
                                                                          id="refund_form1" method="post" action=""
                                                                          class="margin0">
                                                                        <div class="modal-body">
                                                                            <p>Once you have refunded to user, mark this
                                                                                order as refund. Are you sure you have
                                                                                refunded??</p>

                                                                            <input type="hidden" name="refundorderid"
                                                                                   id="refundorderid"
                                                                                   value="<?= $db_trip[$i]['iOrderId']; ?>">
                                                                            <input type="hidden" name="action"
                                                                                   id="action" value="refund">
                                                                            <!-- <input type="hidden" name="fCancellationCharge" id="fCancellationCharge" value="<?= $db_trip[$i]['fCancellationCharge']; ?>"> -->
                                                                            <input type="hidden" name="fRefundAmount"
                                                                                   id="fRefundAmount"
                                                                                   value="<?= $refundedamt; ?>">
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="submit" class="btn btn-info"
                                                                                    id="cnl_booking"
                                                                                    title="Refund Booking">Refund Order
                                                                            </button>
                                                                            <button type="button"
                                                                                    class="btn btn-default"
                                                                                    data-dismiss="modal">Not Now
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                                <!-- Modal content-->
                                                            </div>
                                                        </div>
                                                        <!-- Modal -->
                                                    </td>
                                                </tr>
                                                <?
                                            }
                                        }
                                        else {
                                            ?>
                                            <tr class="gradeA">
                                                <td colspan="12" style="text-align:center;"> No Records Found.</td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </form>
                                <?php include('pagination_n.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="action" value="<?= $action; ?>">
    <input type="hidden" name="searchOrderNo" value="<?= $searchOrderNo; ?>">
    <input type="hidden" name="searchServiceType" value="<?= $searchServiceType; ?>">
    <input type="hidden" name="iDriverId" value="<?= $iDriverId; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?= $vStatus; ?>">
    <input type="hidden" name="eType" value="<?= $eType; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<? include_once('footer.php'); ?>
<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png"
                                                          alt=""></i><?= $langage_lbl_admin['LBL_RIDER']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png"
                                                          alt=""></i><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>
                    Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons1" style="display:none">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detail_modal4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i aria-hidden="true" class="fa fa-building-o"
                       style="margin:2px 5px 0 2px;"></i><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons4" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="store_detail"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<link rel="stylesheet" href="css/select2/select2.min.css"/>
<script src="js/plugins/select2.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script>
    $('#dp4').datepicker()
        .on('changeDate', function (ev) {
            var endDate = $('#dp5').val();
            if (ev.date.valueOf() < endDate.valueOf()) {
                $('#alert').show().find('strong').text('The start date can not be greater then the end date');
            } else {
                $('#alert').hide();
                var startDate = new Date(ev.date);
                $('#startDate').text($('#dp4').data('date'));
            }
            $('#dp4').datepicker('hide');
        });
    $('#dp5').datepicker()
        .on('changeDate', function (ev) {
            var startDate = $('#dp4').val();
            if (ev.date.valueOf() < startDate.valueOf()) {
                $('#alert').show().find('strong').text('The end date can not be less then the start date');
            } else {
                $('#alert').hide();
                var endDate = new Date(ev.date);
                $('#endDate').text($('#dp5').data('date'));
            }
            $('#dp5').datepicker('hide');
        });
    $(document).ready(function () {
        $("#dp5").click(function () {
            $('#dp5').datepicker('show');
            $('#dp4').datepicker('hide');
        });
        $("#dp4").click(function () {
            $('#dp4').datepicker('show');
            $('#dp5').datepicker('hide');
        });
        if ('<?= $startDate ?>' != '') {
            $("#dp4").val('<?= $startDate ?>');
            $("#dp4").datepicker('update', '<?= $startDate ?>');
        }
        if ('<?= $endDate ?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate; ?>');
            $("#dp5").val('<?= $endDate; ?>');
        }
    });
    function setRideStatus(actionStatus) {
        window.location.href = "trip.php?type=" + actionStatus;
    }
    function todayDate() {
        $("#dp4").val('<?= $Today; ?>');
        $("#dp5").val('<?= $Today; ?>');
    }
    function reset() {
        location.reload();
    }
    function yesterdayDate() {
        $("#dp4").val('<?= $Yesterday; ?>');
        $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
        $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
        $("#dp4").change();
        $("#dp5").change();
        $("#dp5").val('<?= $Yesterday; ?>');
    }
    function currentweekDate(dt, df) {
        $("#dp4").val('<?= $monday; ?>');
        $("#dp4").datepicker('update', '<?= $monday; ?>');
        $("#dp5").datepicker('update', '<?= $sunday; ?>');
        $("#dp5").val('<?= $sunday; ?>');
    }
    function previousweekDate(dt, df) {
        $("#dp4").val('<?= $Pmonday; ?>');
        $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
        $("#dp5").datepicker('update', '<?= $Psunday; ?>');
        $("#dp5").val('<?= $Psunday; ?>');
    }
    function currentmonthDate(dt, df) {
        $("#dp4").val('<?= $currmonthFDate; ?>');
        $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
        $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
        $("#dp5").val('<?= $currmonthTDate; ?>');
    }
    function previousmonthDate(dt, df) {
        $("#dp4").val('<?= $prevmonthFDate; ?>');
        $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
        $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
        $("#dp5").val('<?= $prevmonthTDate; ?>');
    }
    function currentyearDate(dt, df) {
        $("#dp4").val('<?= $curryearFDate; ?>');
        $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
        $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
        $("#dp5").val('<?= $curryearTDate; ?>');
    }
    function previousyearDate(dt, df) {
        $("#dp4").val('<?= $prevyearFDate; ?>');
        $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
        $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
        $("#dp5").val('<?= $prevyearTDate; ?>');
    }
    $("#Search").on('click', function () {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        } else {
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action + "?" + formValus;
        }
    });
    $(function () {
        $("select.filter-by-text#searchServiceType").each(function () {
            $(this).select2({
                placeholder: $(this).attr('data-text'),
                allowClear: true
            }); //theme: 'classic'
        });
    });
    $(function () {
        $("#cnl_booking").on('click', function (e) {
            var fRefundAmount = $('#fRefundAmount');
            /* var cancelcharge = $('#fCancellationCharge');
             alert(cancelcharge.val());*/
            if (fRefundAmount.val() == "") {
                $(".cnl_error").html("This Field is required.");
                return false;
            }/* else if(!cancelcharge.val()){
                                                                                 $(".cancelcharge_error").html("This Field is required.");
                                                                                 return false;
                                                                                 }*/ else {
                $("#refund_form1")[0].submit();
            }
        });
    });
    /*$('.refund_form').on('hidden.bs.modal', function () {
     window.location.reload();
     });*/
    $('body').on('keyup', '.select2-search__field', function () {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ($(".select2-results__options").is(".select2-results__message")) {
            $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });
    function formatDesign(item) {
        //console.log(item.text);
        /*if(item.text == 'Searching…'){
            console.log('item1');
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }*/
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        //console.log(item);
        var selectionText = item.text.split("--");
        if (selectionText[2] != null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2] + '</span>');
        } else if (selectionText[2] == null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if (selectionText[2] != null && selectionText[1] == null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }
        //$(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        return $returnString;
    };
    function formatDesignnew(item) {
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }
    $(function () {
        $("select.filter-by-text#searchDriver").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Driver',
                            //company_id:$('#searchCompany option:selected').val()
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;
                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }
                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        return {
                            results: $.map(data, function (item) {
                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };
                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });
    // Fetch the preselected item, and add to the control
    <? if(!empty($iDriverId)) { ?>
    var sId = "<?= $iDriverId;?>";
    <? } else { ?>
    var sId = "";
    <? } ?>
    var sSelect = $('select.filter-by-text#searchDriver');
    var itemname;
    var itemid;
    if (sId != '') {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Driver',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelect.append(option).trigger('change');
            } else {
                console.log(response.result);
            }
        });
    }
</script>
</body>
<!-- END BODY-->
</html>