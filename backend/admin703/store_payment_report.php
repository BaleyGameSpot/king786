<?php
include_once('../common.php');

$script = 'Restaurant Payment Report';
$eSystem = " AND eSystem = 'DeliverAll'";

function cleanNumber($num) {
    return str_replace(',', '', $num);
}

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
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
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

    if ($searchServiceType != '') {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "'";
    }

    if ($searchPaymentType != '') {
        $ssql .= " AND o.ePaymentOption ='" . $searchPaymentType . "'";
    }
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT o.iOrderId,o.vOrderNo,o.fSubTotal,o.fTax,o.fPackingCharge, sc.vServiceName_" . $default_lang . " as vServiceName,o.iCompanyId,o.iDriverId,o.iUserId,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fOutStandingAmount,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.iStatusCode,os.vStatus, o.fTipAmount, o.eOrderplaced_by, ( SELECT COUNT(o.iOrderId) FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId WHERE 1=1  AND (o.iStatusCode = '6' OR o.fRestaurantPayAmount > 0) $ssql $trp_ssql) AS Total FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company AS c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status AS os ON os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND (o.iStatusCode = '6') $ssql $trp_ssql";
//OR o.fRestaurantPayAmount > 0

$totalData = $obj->MySQLSelect($sql);

$tot_order_amount =$tot_site_commission=$tot_delivery_charges=$tot_offer_discount=$tot_restaurant_payment=$expected_rest_payment=$tot_outstanding_amount= 0.00;
foreach ($totalData as $dtps) {
    $totalfare = $dtps['fTotalGenerateFare'];
    $fOffersDiscount = $dtps['fOffersDiscount'];
    $fDeliveryCharge = $dtps['fDeliveryCharge'];
    $site_commission = $dtps['fCommision'];
    $fRestaurantPayAmount = $dtps['fRestaurantPayAmount'];
    $fRestaurantPaidAmount = $dtps['fRestaurantPaidAmount'];
    $fOutStandingAmount = $dtps['fOutStandingAmount'];
    $fTipAmount = $dtps['fTipAmount'];
    $subtotal  = $dtps['fSubTotal'];
    $fPackingCharge  = $dtps['fPackingCharge'];
    $fTax  = $dtps['fTax'];

    if ($dtps['iStatusCode'] == '7' || $dtps['iStatusCode'] == '8') {
        $fRestexpectedearning = $fRestaurantPayAmount;
    } else {
        $fRestexpectedearning = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);
    }

    if ($dtps['iStatusCode'] == '7' || $dtps['iStatusCode'] == '8') {
        $restaurant_payment = $fRestaurantPaidAmount;
    } else {
	//to solve issue in free delivery
        /*$restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);
        $restaurant_payment = $restaurant_payment - cleanNumber($fTipAmount);*/
	$restaurant_payment = ($subtotal + $fPackingCharge + $fTax) - ($dtps['fCommision'] + $fOffersDiscount);
         //to solve issue in free delivery
        if (strtolower($dtps['eOrderplaced_by']) == 'kiosk') {
            $restaurant_payment = - cleanNumber($site_commission);
        }
    }



    $tot_order_amount = $tot_order_amount + cleanNumber($totalfare);
    $tot_offer_discount = $tot_offer_discount + cleanNumber($fOffersDiscount);
    $tot_delivery_charges = $tot_delivery_charges + cleanNumber($fDeliveryCharge);
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $expected_rest_payment = $expected_rest_payment + cleanNumber($fRestexpectedearning);
    $tot_restaurant_payment = $tot_restaurant_payment + cleanNumber($restaurant_payment);
    $tot_outstanding_amount = $tot_outstanding_amount + cleanNumber($fOutStandingAmount);
}

$total_results = isset($totalData[0]['Total']) ? $totalData[0]['Total'] : 0;
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
$sql = "SELECT o.iOrderId,o.vOrderNo,o.fSubTotal,o.fTax,o.fPackingCharge,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fOutStandingAmount,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.iStatusCode,os.vStatus,o.eOrderplaced_by,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, o.fTipAmount,o.vTimeZone FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND (o.iStatusCode = '6') $ssql $trp_ssql $ord LIMIT $start, $per_page";
//OR o.fRestaurantPayAmount > 0
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

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
/*$cardText = "Card";
if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    $cardText = "Wallet";
}*/
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 End

$cardText = "Card";
if (strtoupper($CARD_AVAILABLE) == 'No') {
    $cardText = "Wallet";
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Payment Report</title>
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
                                <h2><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Payment Report</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <h3>Search <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>...</h3>
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
                                    <select class="form-control filter-by-text" name = 'searchCompany' data-text="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>" id="searchCompany">
                                        <option value="">Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <input type="text" id="serachOrderNo" name="serachOrderNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> Number" class="form-control search-trip001" value="<?php echo $serachOrderNo; ?>"/>
                                </div>
                            </span>
                        </div>

                        <div class="row payment-report payment-report1 payment-report2">
                            <div class="col-lg-3">
                                <select class="form-control" name='searchPaymentType' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select Payment Type</option>
                                    <option value="Cash" <?php if ($searchPaymentType == "Cash") { ?>selected <?php } ?>>Cash</option>
                                    <option value="Card" <?php if ($searchPaymentType == "Card") { ?>selected <?php } ?>><?= $cardText; ?></option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name='searchRestaurantPayment' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Payment Status</option>
                                    <option value="Settled" <?php if ($searchRestaurantPayment == "Settled") { ?>selected <?php } ?>>Settled</option>
                                    <option value="Unsettled" <?php if ($searchRestaurantPayment == "Unsettled") { ?>selected <?php } ?>>Unsettled</option>
                                </select>
                            </div>
                            <?php if (scount($allservice_cat_data) > 1) { ?>
                                <div class="col-lg-2 select001" style="padding-right:15px;">
                                    <select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type" id="searchServiceType">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                        <?php foreach ($allservice_cat_data as $value) { ?>
                                            <option value="<?php echo $value['iServiceId']; ?>" <?php if ($searchServiceType == $value['iServiceId']) {
                                        echo "selected";
                                    } ?>><?php echo clearName($value['vServiceName']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'store_payment_report.php'"/>
                            <?php if (scount($db_trip) > 0) { ?>
                                <button type="button" onClick="reportExportTypes('store_payment_report')" class="export-btn001" >Export</button>
                            <?php } ?></b>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_restaurant">
                                        <input type="hidden"  name="iOrderId" id="iOrderId" value="">
                                        <input type="hidden"  name="ePayRestaurant" id="ePayRestaurant" value="">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <?php if (scount($allservice_cat_data) > 1) { ?>
                                                        <th>Service Type</th>
                                                            <?php } ?>
                                                    <th width="6%"><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN_DL']; ?># </th>
                                                    <th><a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1') {
                                                                echo $order;
                                                            } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?><?php if ($sortby == 1) {
                                                                if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i><?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="10%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {
                                                                echo $order;
                                                            } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php if ($sortby == 2) {
                                                                if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i><?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="10%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {
                                                                echo $order;
                                                            } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php if ($sortby == 3) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th style="text-align:center;" width="12%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
                                                                echo $order;
                                                            } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Date <?php if ($sortby == 4) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th  width="6%" style="text-align:center;">A=Total Order Amount</th>
                                                    <th width="4%" style="text-align:center;">B=Site Commission</th>
                                                    <th  width="4%" style="text-align:center;">C=Delivery Charges</th>
                                                    <th  width="4%"style="text-align:center;">D=Offer Amount</th>
                                                    <th  width="4%"style="text-align:center;">E=Outstanding Amount</th>
                                                    <th  width="4%" style="text-align:center;">F=Delivery Tip Amount</th>

                                                    <th width="6%" style="text-align:center;">G=A-B-C-D-E-F <br/>Final <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Pay Amount</th>
                                                    <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Status</th>
                                                    <th style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') {
                                                    echo $order;
                                                    } else { ?>0<?php } ?>)">Payment method<?php if ($sortby == 5) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Payment Status</th> 
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $set_unsetarray = array();
                                                if (scount($db_trip) > 0) {
                                                    $serverTimeZone = date_default_timezone_get();
                                                    for ($i = 0; $i < scount($db_trip); $i++) {
                                                        $class_setteled = "";
                                                        if ($db_trip[$i]['eRestaurantPaymentStatus'] == 'Settled') {
                                                            $class_setteled = "setteled-class";
                                                        }
                                                        $totalfare = $db_trip[$i]['fTotalGenerateFare'];
                                                        $site_commission = $db_trip[$i]['fCommision'];
                                                        $subtotal  = $db_trip[$i]['fSubTotal'];
                                                        $fPackingCharge  = $db_trip[$i]['fPackingCharge'];
                                                        $fTax = $db_trip[$i]['fTax'];
                                                        $fOffersDiscount = $db_trip[$i]['fOffersDiscount'];
                                                        $fDeliveryCharge = $db_trip[$i]['fDeliveryCharge'];
                                                        $fOutStandingAmount = $db_trip[$i]['fOutStandingAmount'];
                                                        $fTipAmount = $db_trip[$i]['fTipAmount'];
                                                        if ($db_trip[$i]['iStatusCode'] == '7' || $db_trip[$i]['iStatusCode'] == '8') {
                                                            $restaurant_payment = $db_trip[$i]['fRestaurantPaidAmount'];
                                                        } else {
 
                                                            $restaurant_payment = ($subtotal + $fPackingCharge + $fTax) - ($db_trip[$i]['fCommision'] + $fOffersDiscount);
                                                            if(strtolower($db_trip[$i]['eOrderplaced_by']) == 'kiosk') {
                                                                $restaurant_payment = - cleanNumber($site_commission);
                                                            }
                                                        }
                                                        $set_unsetarray[] = $db_trip[$i]['eRestaurantPaymentStatus'];

                                                        if (!empty($db_trip[$i]['drivername'])) {
                                                            $drivername = $db_trip[$i]['drivername'];
                                                        } else {
                                                            $drivername = '--';
                                                        }
                                                        $date_format_data_array = array(
                                                            'tdate' => (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['tOrderRequestDate'],$db_trip[$i]['vTimeZone'],$serverTimeZone) : $db_trip[$i]['tOrderRequestDate'],
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $get_tOrderRequestDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                                        
                                                        $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                        ?>
                                                        <tr class="gradeA <?= $class_setteled ?>">
                                                                <?php if (scount($allservice_cat_data) > 1) { ?>
                                                                <td><?php echo $db_trip[$i]['vServiceName']; ?></td>
                                                                <?php } ?>
                                                                <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                <td align="center"><a href="order_invoice.php?iOrderId=<?= $db_trip[$i]['iOrderId'] ?>" target="_blank"><?php echo $db_trip[$i]['vOrderNo']; ?></a></td>
                                                                <?php } else { ?>
                                                                <td align="center"><?php echo $db_trip[$i]['vOrderNo']; ?></td>
                                                                <?php } ?>
                                                            <td>
                                                                <?php    if ($db_trip[$i]['resturant_phone'] != '') {   ?>
																 <?php if ($userObj->hasPermission('view-store')) { ?><a href="javascript:void(0);" onClick="show_store_details('<?= $db_trip[$i]['iCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName(stripslashes($db_trip[$i]['vCompany'])); ?><?php if ($userObj->hasPermission('view-store')) { ?></a><?php } ?>
																<?php
                                                                    echo '<br>';
                                                                    echo'<b>Phone: </b> +' . clearPhone($db_trip[$i]['resturant_phone']);
                                                                } else {
                                                                     ?>
															 <?php if ($userObj->hasPermission('view-store')) { ?><a href="javascript:void(0);" onClick="show_store_details('<?= $db_trip[$i]['iCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName(stripslashes($db_trip[$i]['vCompany'])); ?><?php if ($userObj->hasPermission('view-store')) { ?></a><?php } ?>
															<?php
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($db_trip[$i]['driver_phone'] != '') { ?>
																<?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($drivername); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?> 
																<?php
																	echo '<br>';
                                                                    echo'<b>Phone: </b> +' . clearPhone($db_trip[$i]['driver_phone']);
                                                                } else { ?>
																<?php if ($userObj->hasPermission('view-providers') &&  $db_trip[$i]['iDriverId'] > 0) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($drivername); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?> 
																<?php 
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($db_trip[$i]['user_phone'] != '') { ?>
																<?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> <?= clearName($db_trip[$i]['riderName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?> 
																<?php
                                                                     echo '<br>';
                                                                    echo'<b>Phone: </b> +' . clearPhone($db_trip[$i]['user_phone']);
                                                                } else { ?>
																<?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> <?= clearName($db_trip[$i]['riderName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
																<?php
                                                        
                                                                }
                                                                ?>
                                                            </td>
                                                            <td align="center"><?=  $get_tOrderRequestDate_date_format['tDisplayDate']."<br/>".$get_tOrderRequestDate_date_format['tDisplayTime'].$time_zone_difference_text ;//DateTime($db_trip[$i]['tOrderRequestDate']); ?></td>
                                                            <td align="center">
                                                            <?php
                                                            if ($db_trip[$i]['fTotalGenerateFare'] != "" && $db_trip[$i]['fTotalGenerateFare'] != 0) {
                                                                echo formateNumAsPerCurrency($db_trip[$i]['fTotalGenerateFare'],'');
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                            </td>
                                                            <td align="center"><?php
                                                                if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fCommision'],'');
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>
                                                            <td align="center"><?php
                                                        if ($db_trip[$i]['fDeliveryCharge'] != "" && $db_trip[$i]['fDeliveryCharge'] != 0) {
                                                            echo formateNumAsPerCurrency($db_trip[$i]['fDeliveryCharge'],'');
                                                        } else {
                                                            echo '-';
                                                        }
                                                                ?></td>
                                                            <td align="center"><?php
                                                                if ($db_trip[$i]['fOffersDiscount'] != "" && $db_trip[$i]['fOffersDiscount'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fOffersDiscount'],'');
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>                                              
                                                            <td align="center"><?php
                                                                if ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'],'');
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>
                                                                <td align="center"><?php
                                                                if ($db_trip[$i]['fTipAmount'] != "" && $db_trip[$i]['fTipAmount'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fTipAmount'],'');
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>
                                                            <td align="center">
                                                                <?php
                                                                if ($restaurant_payment != "" && $restaurant_payment != 0) {
                                                                    echo formateNumAsPerCurrency($restaurant_payment,'');
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
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
                                                            <td align="center"><?= $db_trip[$i]['eRestaurantPaymentStatus']; ?></td>
                                                            <td align="center">
                                                                <?php if ($db_trip[$i]['eRestaurantPaymentStatus'] == 'Unsettled') { ?>
                                                                    <input class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iOrderId'] ?>" id="iOrderId_<?= $db_trip[$i]['iOrderId'] ?>" name="iOrderId[]">
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    <tr class="gradeA">
                                                        <td colspan="15" align="right">Total Fare</td>
                                                        <td align="center" colspan="2"><?= formateNumAsPerCurrency($tot_order_amount,''); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="15" align="right">Total Site Commission</td>
                                                        <td  align="center" colspan="2"><?= formateNumAsPerCurrency($tot_site_commission,''); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="15" align="right">Total Delivery Charges</td>
                                                        <td  align="center" colspan="2"><?= formateNumAsPerCurrency($tot_delivery_charges,''); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="15" align="right">Total Offer Amount</td>
                                                        <td  align="center" colspan="2"><?= formateNumAsPerCurrency($tot_offer_discount,''); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="15" align="right">Total Outstanding Amount</td>
                                                        <td  align="center" colspan="2"><?= formateNumAsPerCurrency($tot_outstanding_amount,''); ?></td>
                                                    </tr>

                                                    <tr class="gradeA">
                                                        <td colspan="15" align="right">Total <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Payment</td>
                                                        <td  align="center" colspan="2"><?= formateNumAsPerCurrency($tot_restaurant_payment,''); ?></td>
                                                    </tr>

                                                    <?php if (in_array("Unsettled", $set_unsetarray)) { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="17" align="Right">
                                                                <a onClick="PaytoRestaurant()" href="javascript:void(0);" class="btn btn-primary">Mark As Settled</a>
                                                            </td>
                                                        </tr>
                                                    <?php } } else { ?>
                                                    <tr class="gradeA">
                                                        <td colspan="17" style="text-align:center;">No Payment Details Found.</td>
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

        <form name="pageForm" id="pageForm" action="action/store_payment_report.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="action" value="<?php echo $action; ?>" >
            <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
            <input type="hidden" name="serachOrderNo" value="<?php echo $serachOrderNo; ?>" >
            <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>" >
            <input type="hidden" name="searchServiceType" value="<?php echo $searchServiceType; ?>" >
            <input type="hidden" name="searchRestaurantPayment" value="<?php echo $searchRestaurantPayment; ?>" >
            <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

<?php include_once('footer.php'); ?>

<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i><?= $langage_lbl_admin['LBL_RIDER']; ?> Details<button type="button" class="close" data-dismiss="modal">x</button>
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

<div  class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog" >
		<div class="modal-content">
			<div class="modal-header">
				<h4>
					<i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details<button type="button" class="close" data-dismiss="modal">x</button>
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
	
<div class="modal fade" id="detail_modal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Details <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons2" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="comp_detail"></div>
            </div>
        </div>
    </div>
</div>
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
                                                                        $("select.filter-by-text#searchServiceType").each(function () {
                                                                            $(this).select2({
                                                                                placeholder: $(this).attr('data-text'),
                                                                                allowClear: true
                                                                            }); //theme: 'classic'
                                                                        });
                                                                    });
                                                                    

    $('body').on('keyup', '.select2-search__field', function() {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ( $( ".select2-results__options" ).is( ".select2-results__message" ) ) {
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });
    function formatDesign(item) {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        //console.log(item);
        var selectionText = item.text.split("--");
        if(selectionText[2] != null && selectionText[1] != null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2]+'</span>');
        } else if(selectionText[2] == null && selectionText[1] != null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if(selectionText[2] != null && selectionText[1] == null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }
        //$(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        return $returnString;
    };

    function formatDesignnew(item){
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#searchCompany").each(function () {
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
                            usertype:'Store',
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;
                    
                        if(data.length < 10){
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        
                        return {
                            results: $.map(data, function (item) {

                                if(item.Phoneno != '' && item.vEmail != ''){
                                    var textdata = item.fullName  + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if(item.Phoneno == '' && item.vEmail != ''){
                                    var textdata = item.fullName  + "--" + "Email: " + item.vEmail;
                                } else if(item.Phoneno != '' && item.vEmail == ''){
                                    var textdata = item.fullName  + "--" + "Phone: +" + item.Phoneno;
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
    var sIdCompany = '<?= $searchCompany;?>';
    var sSelectCompany = $('select.filter-by-text#searchCompany');
    var itemname;
    var itemid;
    if(sIdCompany != ''){

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompany + '&usertype=Store',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if(item.Phoneno != '' && item.vEmail != ''){
                        var textdata = item.fullName  + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if(item.Phoneno == '' && item.vEmail != ''){
                        var textdata = item.fullName  + "--" + "Email: " + item.vEmail;
                    } else if(item.Phoneno != '' && item.vEmail == ''){
                        var textdata = item.fullName  + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectCompany.append(option);
            } else {
                // console.log(response.result);
            }
        });
    }                                                            
        </script>
    </body>
    <!-- END BODY-->
</html>