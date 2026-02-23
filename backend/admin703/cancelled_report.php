<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-cancelled-order-report')) {
    $userObj->redirect();
}

$script = 'Cancelled Order Report';
function cleanNumber($num) {
    return str_replace(',', '', $num);
}
//data for select fields
$db_company = $obj->MySQLSelect("SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem='DeliverAll' order by vCompany");
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY o.iOrderId DESC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY ru.vName ASC";
    else
        $ord = " ORDER BY ru.vName DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY o.tOrderRequestDate ASC";
    else
        $ord = " ORDER BY o.tOrderRequestDate DESC";
}

if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY o.ePaymentOption ASC";
    else
        $ord = " ORDER BY o.ePaymentOption DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$serachOrderNo = isset($_REQUEST['serachOrderNo']) ? $_REQUEST['serachOrderNo'] : '';
$searchRestaurantPayment = isset($_REQUEST['searchRestaurantPayment']) ? $_REQUEST['searchRestaurantPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';

if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
    }
    if ($serachOrderNo != '') {
        $ssql .= " AND o.vOrderNo ='" . $serachOrderNo . "'";
    }
    if ($searchCompany != '') {
        $ssql .= " AND c.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchRestaurantPayment != '') {
        $ssql .= " AND o.eRestaurantPaymentStatus ='" . $searchRestaurantPayment . "'";
    }
    if ($searchServiceType != '' && !in_array($searchServiceType, ['Genie', 'Runner', 'Anywhere'])) {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "' AND o.eBuyAnyService ='No'";
    }
    if ($searchServiceType == "Genie") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
    }
    if ($searchServiceType == "Runner") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND o.ePaymentOption ='" . $searchPaymentType . "'";
    }
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}
$ssql .= " AND sc.iServiceId IN(".$enablesevicescategory.")";

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql1 = "SELECT DISTINCT(o.iOrderId),o.vOrderNo,sc.vServiceName_" . $default_lang . " as vServiceName,o.tOrderRequestDate,o.fTotalGenerateFare,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fDriverPaidAmount,o.eAdminPaymentStatus,o.ePaymentOption,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.iStatusCode,os.vStatus,t.fDeliveryCharge as driverearning,o.fCancellationCharge,oa.fCancellationFare,oa.ePaidByPassenger,oa.vOrderAdjusmentId FROM orders AS o LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN trip_outstanding_amount as oa on oa.iOrderId=o.iOrderId LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('7','8') $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql1);

//$total_results = $totalData[0]['Total'];
$total_results = scount($totalData);
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
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT DISTINCT(o.iOrderId),o.fTipAmount,o.vOrderNo,sc.vServiceName_" . $default_lang . " as vServiceName,o.tOrderRequestDate,o.fTotalGenerateFare,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fDriverPaidAmount,o.eAdminPaymentStatus,o.ePaymentOption,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.iStatusCode, os.vStatus,t.fDeliveryCharge as driverearning, o.fCancellationCharge, oa.fCancellationFare, oa.ePaidByPassenger, oa.vOrderAdjusmentId, oa.vTripAdjusmentId, o.eBuyAnyService, o.fDeliveryChargeCancelled, o.eForPickDropGenie, o.vTimeZone,o.eCancelledBy FROM orders AS o LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN trip_outstanding_amount as oa on oa.iOrderId=o.iOrderId LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('7','8') $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);


$endRecord = scount($db_trip);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
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

$settlementorderid = isset($_REQUEST['settlementorderid']) ? $_REQUEST['settlementorderid'] : '';
if ($action == 'settelled' && $settlementorderid != '') {
    $fDriverPaidAmount = isset($_REQUEST['fDeliveryCharge']) ? $_REQUEST['fDeliveryCharge'] : '';
    $fRestaurantPaidAmount = isset($_REQUEST['fRestaurantPayAmount']) ? $_REQUEST['fRestaurantPayAmount'] : '';

    $query = "UPDATE orders SET fRestaurantPaidAmount = '" . $fRestaurantPaidAmount . "' ,fDriverPaidAmount='" . $fDriverPaidAmount . "',eAdminPaymentStatus = 'Settled',eRestaurantPaymentStatus = 'Settled' WHERE iOrderId = '" . $settlementorderid . "'";
    $obj->sql_query($query);

    $tQuery = "UPDATE trips SET eDriverPaymentStatus = 'Settled' WHERE iOrderId = '" . $settlementorderid . "'";
    $obj->sql_query($tQuery);
    echo "<script>location.href='cancelled_report.php'</script>";
}
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
$cardText = "Card";

$WalletText = "";
if ($SYSTEM_PAYMENT_FLOW == 'Method-1'){
    $WalletText = "Wallet";
}

if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    $cardText = "Wallet";
}


//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 End
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Cancelled / Refunded Order Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <style>
            .setteled-class{
                background-color:#bddac5
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main Loading -->
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
                                <h2>Cancelled / Refunded Order Report</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <h3>Search by Date...</h3>
                            <span>
                                <a style="cursor:pointer" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                <a style="cursor:pointer" onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                <a style="cursor:pointer" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                <a style="cursor:pointer" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                <a style="cursor:pointer" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                <a style="cursor:pointer" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                            </span> 
                            <span>
                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />
                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                                <div class="col-lg-3 select001">
                                    <select class="form-control" name='searchPaymentType' data-text="Select <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                        <option value="">Select Payment Type</option>
                                        <option value="Cash" <?php if ($searchPaymentType == "Cash") { ?>selected <?php } ?>>Cash</option>
                                        <option value="Card" <?php if ($searchPaymentType == "Card") { ?>selected <?php } ?>><?= $cardText; ?></option>
                                        <?php if($WalletText != "") { ?>
                                        <option value="Wallet" <?php if ($searchPaymentType == "Wallet") { ?>selected <?php } ?>><?= $WalletText; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <input type="text" id="serachOrderNo" name="serachOrderNo" placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> Number" class="form-control search-trip001" value="<?= $serachOrderNo; ?>"/>
                                </div>
                            </span>
                        </div>
                        <?php if (scount($allservice_cat_data) > 1) { ?>
                            <div class="row payment-report payment-report1 payment-report2">
                                <span>
                                    <div class="col-lg-2 select001" style="padding-right:15px;">
                                        <select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type">
                                            <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                            <?php foreach ($allservice_cat_data as $value) { ?>
                                                <option value="<?= $value['iServiceId']; ?>" <?php
                                                if ($searchServiceType == $value['iServiceId']) {
                                                    echo "selected";
                                                }
                                                ?>><?= clearName($value['vServiceName']); ?></option>
                                                    <?php } ?>
                                            <?php if($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
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
                        <?php } ?>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'cancelled_report.php'"/>
                             <?php if (scount($db_trip) > 0 && SITE_TYPE != 'Demo' && $userObj->hasPermission('export-cancelled-order-report')) { ?>
                                        <button type="button" onClick="exportlist()" class="export-btn001" >Export</button>
                            <?php } ?>
                            </b>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_restaurant">
                                        <input type="hidden"  name="iOrderId" id="iOrderId" value="">
                                        <input type="hidden"  name="ePayRestaurant" id="ePayRestaurant" value="">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <?php if (scount($allservice_cat_data) > 1) { ?>
                                                        <th width="8%"  style="text-align:center;">Service Type</th>
                                                    <?php } ?>
                                                    <th width="10%"  style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_NO_ADMIN_DL']; ?># </th>
                                                    <th width="10%" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Date <?php
                                                               if ($sortby == 4) {
                                                                   if ($order == 0) {
                                                                       ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="10%" style="text-align:right;">PayOut To <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></th>
                                                    <th width="15%" style="text-align:right;">Payout to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></th>
                                                    <th width="15%" style="text-align:right;">Cancellation Charges For <?= $langage_lbl_admin['LBL_RIDER'] ?></th>
                                                    <th width="8%" style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Status</th>
                                                    <th width="10%" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Payment method<?php
                                                            if ($sortby == 5) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="10%" style="text-align:center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $set_unsetarray = array();
                                                if (scount($db_trip) > 0) {
                                                    //Added By HJ On 23-09-2020 For Optimize For Loop Query Start
                                                    $orderIdArr = array_column($db_trip, 'iOrderId');
                                                    $payment_to_driverArr = GetDriverPayment($orderIdArr);
                                                    //echo "<pre>";print_r($payment_to_driverArr);die;
                                                    //Added By HJ On 23-09-2020 For Optimize For Loop Query End
                                                    for ($i = 0; $i < scount($db_trip); $i++) {
                                                        $class_setteled = "";
                                                        if ($db_trip[$i]['eAdminPaymentStatus'] == 'Settled') {
                                                            $class_setteled = "setteled-class";
                                                        }
                                                        $set_unsetarray[] = $db_trip[$i]['eAdminPaymentStatus'];
                                                        //Added By HJ On 23-09-2020 For Optimize For Loop Query Start
                                                        $payment_to_driver = 0;
                                                        if(isset($payment_to_driverArr[$db_trip[$i]['iOrderId']])){
                                                            $payment_to_driver = $payment_to_driverArr[$db_trip[$i]['iOrderId']];
                                                        }
                                                        //$payment_to_driver = GetDriverPayment($db_trip[$i]['iOrderId']);
                                                        //Added By HJ On 23-09-2020 For Optimize For Loop Query End
                                                        if (scount($allservice_cat_data) > 1) {
                                                            $vServiceName = $db_trip[$i]['vServiceName'];
                                                        }

                                                        if($db_trip[$i]['eBuyAnyService'] == "Yes")
                                                        {
                                                            $vServiceName = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
                                                            if($db_trip[$i]['eForPickDropGenie'] == "Yes")
                                                            {
                                                                $vServiceName = $langage_lbl_admin['LBL_RUNNER'];
                                                            }
                                                        }
                                                        ?>
                                                        <tr class="gradeA <?= $class_setteled ?>">
                                                            <?php if (scount($allservice_cat_data) > 1) { ?>
                                                                <td align="center"><?php echo $vServiceName; ?></td>
                                                            <?php } ?>
                                                            <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                <td align="center"><a href="order_invoice.php?iOrderId=<?= $db_trip[$i]['iOrderId'] ?>" target="_blank"><?php echo $db_trip[$i]['vOrderNo']; ?></a></td>
        <?php } else { ?>
                                                                <td align="center"><?php echo $db_trip[$i]['vOrderNo']; ?></td>
        <?php } ?>


                                                            <td align="center"><?php
                                                            $systemTimeZone = date_default_timezone_get();
                                                            $vTimeZone = $db_trip[$i]['vTimeZone'];
                                                            //echo DateTime(converToTz($db_trip[$i]['tOrderRequestDate'], $vTimeZone,$systemTimeZone), 'yes');
                                                            $date_format_data_array = array(
                                                                'tdate' => (!empty($vTimeZone)) ? converToTz($db_trip[$i]['tOrderRequestDate'], $vTimeZone, $systemTimeZone) : $db_trip[$i]['tOrderRequestDate'],
                                                                'langCode' => $default_lang,
                                                                'DateFormatForWeb' => 1
                                                            );
                                                            $get_tOrderRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($vTimeZone,$date_format_data_array['tdate']).")";
                                                            echo $get_tOrderRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;

                                                             ?>
                                                            </td>
                                                            
                                                            <td align="right">
                                                                <?php if($db_trip[$i]['eBuyAnyService'] == "No") { ?>
                                                                Actual Amount : <?= formateNumAsPerCurrency($db_trip[$i]['fRestaurantPayAmount'],''); ?><br/>
                                                                You Paid : <?= formateNumAsPerCurrency($db_trip[$i]['fRestaurantPaidAmount'],''); ?>
                                                                <?php } ?>
                                                            </td>
                                                            <td align="right">
                                                                <?php if ($payment_to_driver == 0) { ?>
                                                                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> not Assign
        <?php } else { ?>
                                                                    Actual Amount : <?= formateNumAsPerCurrency($db_trip[$i]['driverearning']+$db_trip[$i]['fTipAmount'],''); ?><br/>
                                                                    You Paid : <?= formateNumAsPerCurrency($db_trip[$i]['fDriverPaidAmount'],''); ?>
                                                                <?php } ?>
                                                            </td>
                                                            <td align="right">
                                                                <?php

                                                                    if($db_trip[$i]['eBuyAnyService'] == "Yes" && $db_trip[$i]['eCancelledBy'] != "Admin") {  
                                                                        echo formateNumAsPerCurrency($db_trip[$i]['fDeliveryChargeCancelled'],''); 
                                                                    }
                                                                    else {
                                                                        echo formateNumAsPerCurrency($db_trip[$i]['fCancellationCharge'],''); 
                                                                    }
                                                                ?>
                                                                <br/>
                                                                <?php 
                                                                if (!empty($db_trip[$i]['vTripAdjusmentId'])) {
                                                                    $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $db_trip[$i]['vTripAdjusmentId'], '', 'true');
                                                                } else {
                                                                    $vRideNo = $db_trip[$i]['vOrderAdjusmentId'];
                                                                }

                                                                if ($db_trip[$i]['ePaymentOption'] == 'Cash' && $db_trip[$i]['ePaidByPassenger'] == 'Yes' && !empty($vRideNo)) { ?>
                                                                    ( Paid In Order No# : <?= $vRideNo ?>)
                                                                <?php } else if ($db_trip[$i]['ePaymentOption'] == 'Cash' && $db_trip[$i]['ePaidByPassenger'] == 'No') { ?>
                                                                    ( Outstanding )
        <?php } else if ($db_trip[$i]['ePaymentOption'] == 'Card') { ?>
                                                                    ( Paid )
                                                                <?php } ?>
                                                            </td>
                                                            <td align="center"><?= $db_trip[$i]['vStatus']; ?></td>
                                                            <td align="center">
                                                                <?php
                                                                $ePaymentOption = $db_trip[$i]['ePaymentOption'];
                                                                if ($db_trip[$i]['ePaymentOption'] == 'Card') {
                                                                    $ePaymentOption = $cardText;
                                                                }
                                                                ?>
                                                                <?= $ePaymentOption; ?></td>
                                                            <td align="center">
        <?php if ($db_trip[$i]['eAdminPaymentStatus'] == 'Settled') { ?>
                                                                    Setteled
        <?php } else { ?>
                                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#settlement_form<?= $db_trip[$i]['iOrderId']; ?>">Mark As Settled</button>
                                                                    <!-- Modal -->
                                                                    <div id="settlement_form<?= $db_trip[$i]['iOrderId']; ?>" class="modal fade settlement_form text-left" role="dialog">
                                                                        <div class="modal-dialog">
                                                                            <!-- Modal content-->
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                    <h4 class="modal-title">Payout Amount</h4>
                                                                                </div>
                                                                                <form role="form" name="settlement_form" id="settlement_form1" method="post" action="" class="margin0">
                                                                                    <div class="modal-body">
                                                                                        <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                            <label class="col-lg-5 control-label">Enter Amount You Paid To <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <span class="red">*</span></label>
                                                                                            <div class="col-lg-7"> 
                                                                                                <?php if ($payment_to_driver == 0): ?>
                                                                                                    <input type="text" name="fDeliveryCharge" id="fDeliveryCharge" value="<?= $payment_to_driver; ?>" disabled><br>
                                                                                                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> not Assign 
            <?php else: ?>
                                                                                                    <input type="text" name="fDeliveryCharge" id="fDeliveryCharge" value="<?= $payment_to_driver; ?>" required>
                <?= $db_trip[$i]['driverName']; ?>
            <?php endif; ?>
                                                                                            </div>
                                                                                            <div class="fDeliveryChargeError error red"></div>
                                                                                        </div>
                                                                                        <?php if($db_trip[$i]['eBuyAnyService'] == "No") { ?>
                                                                                        <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                            <label class="col-lg-5 control-label">Enter Amount You Paid To Store<span class="red">*</span></label>

            <?php $payment_to_restaurant = GetStorePayment($db_trip[$i]['iOrderId']);
            //$eConfirm = checkOrderStatus($db_trip[$i]['iOrderId'], "2");
            //$payment_to_restaurant is 0 means store not confirmed order..getPaymentToRestaurant in this fun put checkOrderStatus so here not need..it commented bc in case in future if need open comment
            ?>

                                                                                            <div class="col-lg-7">
                                                                                                <?php if($payment_to_restaurant == 0) { ?>
                                                                                                <input type="text" name="fRestaurantPayAmount" id="fRestaurantPayAmount" value="<?= $payment_to_restaurant; ?>" disabled><br>
                                                                                                <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." has not confirmed order"; ?>
                                                                                                <?php } else { ?>
                                                                                                <input type="text" name="fRestaurantPayAmount" id="fRestaurantPayAmount" value="<?= $payment_to_restaurant; ?>" required>
                                                                                                <?php } ?>
                                                                                            </div>
                                                                                            <div class="fRestaurantPayAmountError error red"></div>
                                                                                        </div>
                                                                                    <?php } ?>
                                                                                        <input type="hidden" name="settlementorderid" id="settlementorderid" value="<?= $db_trip[$i]['iOrderId']; ?>">
                                                                                        <input type="hidden" name="action" id="action" value="settelled">
                                                                                        <input type="hidden" name="eBuyAnyService" id="eBuyAnyService" value="<?= $db_trip[$i]['eBuyAnyService']; ?>">

                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="submit" class="btn btn-info" id="cnl_booking" title="Payout Amount">Payout Amount</button>
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
                                                                                    </div>
                                                                                </form> 
                                                                            </div>
                                                                            <!-- Modal content-->  
                                                                        </div>
                                                                    </div>
                                                                    <!-- Modal -->
                                                                    <script>
                                                                        $('#settlement_form<?= $db_trip[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
                                                                            $("#fDeliveryCharge").val("<?= $payment_to_driver; ?>");
                                                                            $("#fRestaurantPayAmount").val("<?= $payment_to_restaurant; ?>");
                                                                            $(".fDeliveryChargeError").html("");
                                                                            $(".fRestaurantPayAmountError").html("");
                                                                        });
                                                                    </script>
                                                        <?php } ?>
                                                            </td>
                                                        </tr>
        <?php
    }
} else {
    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="9" style="text-align:center;">No Payment Details Found.</td>
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
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/cancelled_report.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?= $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?= $order; ?>" >
            <input type="hidden" name="action" value="<?= $action; ?>" >
            <input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>" >
            <input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>" >
            <input type="hidden" name="searchRider" value="<?= $searchRider; ?>" >
            <input type="hidden" name="serachOrderNo" value="<?= $serachOrderNo; ?>" >
            <input type="hidden" name="searchPaymentType" value="<?= $searchPaymentType; ?>" >
            <input type="hidden" name="searchRestaurantPayment" value="<?= $searchRestaurantPayment; ?>" >
            <input type="hidden" name="startDate" value="<?= $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?= $endDate; ?>" >
            <input type="hidden" name="vStatus" value="<?= $vStatus; ?>" >
            <input type="hidden" name="searchServiceType" value="<?= $searchServiceType; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>
        
<?php include_once('footer.php'); ?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
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

            function todayDate() {
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
            }
            function reset() {
                location.reload();
            }
            function yesterdayDate()
            {
                $("#dp4").val('<?= $Yesterday; ?>');
                $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                $("#dp4").change();
                $("#dp5").change();
                $("#dp5").val('<?= $Yesterday; ?>');
            }
            function currentweekDate(dt, df)
            {
                $("#dp4").val('<?= $monday; ?>');
                $("#dp4").datepicker('update', '<?= $monday; ?>');
                $("#dp5").datepicker('update', '<?= $sunday; ?>');
                $("#dp5").val('<?= $sunday; ?>');
            }
            function previousweekDate(dt, df)
            {
                $("#dp4").val('<?= $Pmonday; ?>');
                $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                $("#dp5").datepicker('update', '<?= $Psunday; ?>');
                $("#dp5").val('<?= $Psunday; ?>');
            }
            function currentmonthDate(dt, df)
            {
                $("#dp4").val('<?= $currmonthFDate; ?>');
                $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                $("#dp5").val('<?= $currmonthTDate; ?>');
            }
            function previousmonthDate(dt, df)
            {
                $("#dp4").val('<?= $prevmonthFDate; ?>');
                $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                $("#dp5").val('<?= $prevmonthTDate; ?>');
            }
            function currentyearDate(dt, df)
            {
                $("#dp4").val('<?= $curryearFDate; ?>');
                $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                $("#dp5").val('<?= $curryearTDate; ?>');
            }
            function previousyearDate(dt, df)
            {
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
                $("select.filter-by-text").each(function () {
                    $(this).select2({
                        placeholder: $(this).attr('data-text'),
                        allowClear: true
                    }); //theme: 'classic'
                });
            });

            $(function () {

                $("#cnl_booking").on('click', function (e) {
                    var fDeliveryCharge = $('#fDeliveryCharge').val()
                    var fRestaurantPayAmount = $('#fRestaurantPayAmount').val();
                    var eBuyAnyService = $('#eBuyAnyService').val();

                    $(".fDeliveryChargeError").html("");
                    $(".fRestaurantPayAmountError").html("");

                    if (fDeliveryCharge == '' || (fRestaurantPayAmount == '' && eBuyAnyService == "No")) {
                        if (fDeliveryCharge == '') {
                            $(".fDeliveryChargeError").html("This Field is required.");
                        }
                        if (fRestaurantPayAmount == '') {
                            $(".fRestaurantPayAmountError").html("This Field is required.");
                        }
                        return false;
                    } else {
                        $(".fDeliveryChargeError").html("");
                        $(".fRestaurantPayAmountError").html("");
                        ShpSq6fAm7($("#settlement_form1"));
                        $("#settlement_form1")[0].submit();
                    }

                });
            });
            function exportlist(){
                $("#actionpay").val("export");
                $("#pageForm").attr("action","export_cancelled_report.php");
                ShpSq6fAm7($("#pageForm"));
                document.pageForm.submit();
            }
        </script>
    </body>
    <!-- END BODY-->
</html>