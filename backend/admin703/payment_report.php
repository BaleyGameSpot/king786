<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-payment-report')) {
    $userObj->redirect();
}
$script = 'Payment_Report';
function cleanNumber($num)
{
    return str_replace(',', '', $num);
}

//data for select fields
$db_company = $obj->MySQLSelect("SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem = 'General' order by vCompany");
$db_drivers = $obj->MySQLSelect("SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName");
$db_rider = $obj->MySQLSelect("SELECT iUserId,CONCAT(vName,' ',vLastName) AS riderName,vEmail FROM register_user WHERE eStatus != 'Deleted' order by vName");
$db_curr_mst = $obj->MySQLSelect("select vSymbol from currency where eDefault='Yes'");
$vSymbol = "$";
if (scount($db_curr_mst) > 0) {
    $vSymbol = $db_curr_mst[0]['vSymbol'];
}
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY tr.iTripId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY rd.vName ASC"; else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY ru.vName ASC"; else
        $ord = " ORDER BY ru.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY tr.tTripRequestDate ASC"; else
        $ord = " ORDER BY tr.tTripRequestDate DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY d.vName ASC"; else
        $ord = " ORDER BY d.vName DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY u.vName ASC"; else
        $ord = " ORDER BY u.vName DESC";
}
if ($sortby == 6) {
    if ($order == 0) $ord = " ORDER BY tr.eType ASC"; else
        $ord = " ORDER BY tr.eType DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(tr.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tr.tTripRequestDate) <='" . $endDate . "'";
    }
    if ($serachTripNo != '') {
        if (strpos($serachTripNo, ',') !== false) {
            $serachTripNoArr = str_replace(",", "','", $serachTripNo);
            $ssql .= " AND tr.vRideNo IN ('" . $serachTripNoArr . "')";
        } else {
            $ssql .= " AND tr.vRideNo ='" . $serachTripNo . "'";
        }
    }
    if ($searchCompany != '') {
        $ssql .= " AND rd.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND tr.iUserId ='" . $searchRider . "'";
    }
    if ($searchDriverPayment != '') {
        $ssql .= " AND tr.eDriverPaymentStatus ='" . $searchDriverPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND tr.vTripPaymentMode ='" . $searchPaymentType . "'";
    }
    if ($eType != '') {

        if ($ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY == 'Yes') {
            if ($eType == 'Deliver') {
                $ssql .= " AND ((tr.eType ='Multi-Delivery' AND (SELECT COUNT(iTripDeliveryLocationId) FROM trips_delivery_locations as tdl WHERE tdl.iTripId=tr.iTripId)=1) OR tr.eType ='Deliver')";
            } else if ($eType == 'Multi-Delivery') {
                $ssql .= " AND (tr.eType ='Multi-Delivery' AND (SELECT COUNT(iTripDeliveryLocationId) FROM trips_delivery_locations as tdl WHERE tdl.iTripId=tr.iTripId)>1)";
            }else if ($eType == 'Fly') {
                $ssql .= " AND tr.iFromStationId > 0 AND tr.iToStationId > 0";
            } else if ($eType == 'Ride') {
                $ssql .= " AND tr.eType ='" . $eType . "' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' AND  tr.iFromStationId = 0 AND tr.iToStationId = 0 ";
            } elseif ($eType == 'InterCity') {
                $ssql .= " AND tr.eType ='Ride' AND tr.eIsInterCity = 'Yes'";
            } elseif ($eType == 'RentalRide') {
                $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
            }elseif ($eType == 'TaxiBid') {
                $ssql .= " AND tr.eType ='Ride' AND tr.isTaxiBid = 'Yes'";
            } elseif ($eType == 'HailRide') {
                $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
            } else if ($eType == "Pool") {
                $ssql .= " AND tr.eType ='Ride' AND tr.ePoolRide = 'Yes'";
            } else {
                $ssql .= " AND tr.eType ='" . $eType . "' ";
            }
        } else {
            if ($eType == 'Fly') {
                $ssql .= " AND tr.iFromStationId > 0 AND tr.iToStationId > 0";
            } else if ($eType == 'Ride') {
                $ssql .= " AND tr.eType ='" . $eType . "' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' AND  tr.iFromStationId = 0 AND tr.iToStationId = 0 ";
            } elseif ($eType == 'RentalRide') {
                $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
            } elseif ($eType == 'InterCity') {
                $ssql .= " AND tr.eType ='Ride' AND tr.eIsInterCity = 'Yes'";
            } elseif ($eType == 'TaxiBid') {
                $ssql .= " AND tr.eType ='Ride' AND tr.isTaxiBid = 'Yes'";
            } elseif ($eType == 'HailRide') {
                $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
            } else if ($eType == "Pool") {
                $ssql .= " AND tr.eType ='Ride' AND tr.ePoolRide = 'Yes'";
            } else {
                $ssql .= " AND tr.eType ='" . $eType . "' ";
            }
        }
    }
}

$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = $MODULES_OBJ->isRideFeatureAvailable() ? "Yes" : "No";
$deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable() ? "Yes" : "No";
$deliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable() ? "Yes" : "No";
if ($ufxEnable != "Yes") {
    $ssql .= " AND tr.eType != 'UberX'";
}
if (!$MODULES_OBJ->isAirFlightModuleAvailable()) {
    $ssql .= " AND tr.iFromStationId = '0' AND tr.iToStationId = '0'";
}
if ($rideEnable != "Yes" && $deliverallEnable != "Yes") {
    $ssql .= " AND tr.eType != 'Ride'";
}
if ($deliveryEnable != "Yes") {
    $ssql .= " AND tr.eType != 'Deliver' AND tr.eType != 'Multi-Delivery'";
}
//$locations_where = "";
//if (scount($userObj->locations) > 0) {
//    $locations = implode(', ', $userObj->locations);
//    $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE tr.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
//}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";
}
$db_organization = $obj->MySQLSelect("SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany");
$orgNameArr = array();
for ($g = 0; $g < scount($db_organization); $g++) {
    $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
//Added By HJ On 30-07-2020 For Get Order Data Of Driver Start - As Per Discuss With KS Sir
$etypeSql = " AND tr.eSystem = 'General'";
if ($deliverallEnable == "Yes") {
    $etypeSql = " AND (tr.eSystem = 'General' OR tr.eSystem = 'DeliverAll') AND tr.iServiceId = '0'";
}
//Added By HJ On 30-07-2020 For Get Order Data Of Driver End As Per Discuss With KS Sir

$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iFare,tr.fTripGenerateFare,tr.fHotelCommision,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.fOutStandingAmount,tr.vTripPaymentMode FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE (tr.iActive ='Finished' OR (tr.iActive ='Canceled' AND tr.iFare > 0) OR (tr.iActive ='Canceled' AND tr.fWalletDebit > 0 AND tr.iFare = 0)) $etypeSql $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);

$sql = "SELECT COUNT(tr.iTripId) as Total FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId WHERE (tr.iActive ='Finished' OR (tr.iActive ='Canceled' AND tr.iFare > 0)  OR (tr.iActive ='Canceled' AND tr.fWalletDebit > 0 AND tr.iFare = 0)) $etypeSql $ssql $trp_ssql";
$totalDataCount = $obj->MySQLSelect($sql);


$driver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_ifare = $tot_tax = 0.00;
//Added By HJ On 08-08-2019 For Get Driver Wallet Debit Amount Start As Per Discuss With KS Sir
$tripWalletArr = array();
$getWalletData = $obj->MySQLSelect("SELECT iBalance,iTripId FROM user_wallet WHERE eType ='Debit' AND iTripId > 0");
for ($d = 0; $d < scount($getWalletData); $d++) {
    $tripWalletArr[$getWalletData[$d]['iTripId']] = $getWalletData[$d]['iBalance'];
}
//Added By HJ On 08-08-2019 For Get Driver Wallet Debit Amount End As Per Discuss With KS Sir

$enableCashReceivedCol = $enableTipCol = array();
foreach ($totalData as $dtps) {
    $fTipPrice = $dtps['fTipPrice'];
    //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start
    if ($dtps['vTripPaymentMode'] == "Cash") {
        $enableCashReceivedCol[] = 1;
    }
    //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start
    if ($fTipPrice > 0) {
        $enableTipCol[] = 1;
    }
}
$total_results = $totalDataCount[0]['Total'];

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
$sql = "SELECT tr.isTaxiBid,tr.eIsInterCity,tr.fCancellationFare,tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.iFare, tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate,tr.tStartDate,tr.tEndDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eDriverPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE (tr.iActive ='Finished' OR (tr.iActive ='Canceled' AND tr.iFare > 0) OR (tr.iActive ='Canceled' AND tr.fWalletDebit > 0 AND tr.iFare = 0)) $etypeSql $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = scount($db_trip);

// for total records sum
$sql1 = "SELECT tr.fCancellationFare,tr.eIsInterCity,tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.iFare, tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate,tr.tStartDate,tr.tEndDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eDriverPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE (tr.iActive ='Finished' OR (tr.iActive ='Canceled' AND tr.iFare > 0) OR (tr.iActive ='Canceled' AND tr.fWalletDebit > 0 AND tr.iFare = 0)) $etypeSql $ssql $trp_ssql $ord";
$totaltrips = $obj->MySQLSelect($sql1);
if (scount($totaltrips) > 0) {
    $ndriver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_ifare = $tot_tax = $totSiteEarning = $TotaliNewFare = 0.00;
    for ($i = 0; $i < scount($totaltrips); $i++) {
        $niFare = $niFareOrg = setTwoDecimalPoint($totaltrips[$i]['iFare']);
        $ntotTax = setTwoDecimalPoint($totaltrips[$i]['fTax1'] + $totaltrips[$i]['fTax2']);
        if (isset($orgNameArr[$totaltrips[$i]['iOrganizationId']]) && $orgNameArr[$totaltrips[$i]['iOrganizationId']] != "") {
            $niFare = 0;
        }
        $ntotalfareNew = setTwoDecimalPoint($totaltrips[$i]['fTripGenerateFare']);
        $nsite_commission = setTwoDecimalPoint($totaltrips[$i]['fCommision']);
        $npromocodediscount = setTwoDecimalPoint($totaltrips[$i]['fDiscount']);
        $nwallentPayment = setTwoDecimalPoint($totaltrips[$i]['fWalletDebit']);
        $nfOutStandingAmount = setTwoDecimalPoint($totaltrips[$i]['fOutStandingAmount']);
        $nfHotelCommision = setTwoDecimalPoint($totaltrips[$i]['fHotelCommision']);
        $nfTipPrice = setTwoDecimalPoint($totaltrips[$i]['fTipPrice']);
        $tipPayment = 0;
        $siteEarning = $nsite_commission + $ntotTax + $nfOutStandingAmount + $nfHotelCommision;
        $ndriver_payment = $dispay_driver_payment = setTwoDecimalPoint(($ntotalfareNew + $nfTipPrice) - ($nsite_commission + $ntotTax + $nfOutStandingAmount + $nfHotelCommision));
        if ($totaltrips[$i]['vTripPaymentMode'] == "Cash") {
            $ndriver_payment = $dispay_driver_payment = setTwoDecimalPoint($ndriver_payment - $niFare);
            $tot_ifare += $niFare;
        }

        if ($totaltrips[$i]['iActive'] == "Canceled") {
            $niFare = $totaltrips[$i]['fCancellationFare'] - $niFare;
            $ndriver_payment = $niFareOrg - ($nsite_commission + $ntotTax + $nfOutStandingAmount + $nfHotelCommision);
            if ($totaltrips[$i]['vTripPaymentMode'] == "Cash" || $totaltrips[$i]['vTripPaymentMode'] == "Organization") {
                $niFare = 0;
                $ndriver_payment = ($niFareOrg + $nwallentPayment)-($nsite_commission + $ntotTax + $nfOutStandingAmount + $nfHotelCommision);
                $dispay_driver_payment = setTwoDecimalPoint($ndriver_payment - $niFare);
            }
        }

        /*echo $totaltrips[$i]['iTripId']." : " .$dispay_driver_payment;
        echo "";
        echo "<br>";*/

        $tot_fare += $ntotalfareNew;
        $tot_site_commission += $nsite_commission;
        $tot_hotel_commision += $nfHotelCommision;
        $tot_promo_discount += $npromocodediscount;
        $tot_wallentPayment += $nwallentPayment;
        $tot_tax += $ntotTax;
        $total_tip += $nfTipPrice;
        $tot_driver_refund += $dispay_driver_payment;
        $totSiteEarning += $siteEarning; // Added By HJ On 26-09-2020 As Per Discuss With KS Sir = Total = C+D+F+G
        $tot_outstandingAmount += $nfOutStandingAmount;
        if ($totaltrips[$i]['vTripPaymentMode'] == "Cash") {
            $TotaliNewFare = $TotaliNewFare + $ntotalfareNew;
        }
    }
}


$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'iTripId') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
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
$generalConfigPaymentArr = $CONFIG_OBJ->getGeneralVarAll_Payment_Array();
//echo "<pre>";print_r($generalConfigPaymentArr);die;
$SYSTEM_PAYMENT_FLOW = "Method-1";
if (isset($generalConfigPaymentArr['SYSTEM_PAYMENT_FLOW'])) {
    $SYSTEM_PAYMENT_FLOW = $generalConfigPaymentArr['SYSTEM_PAYMENT_FLOW'];
}
$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');


?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Payment Report</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style>
        .setteled-class {
            background-color: #bddac5
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
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
                        <h2>Payment Report (<?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?>)</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search"/>
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
                        <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                        <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text" name="searchCompany" data-text="Select Company" id="searchCompany">
                                <option value="">Select Company</option>
                            </select>
                        </div>
                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text driver_container" name="searchDriver" data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                            </select>
                        </div>
                    </span>
                </div>
                <div class="row payment-report payment-report1 payment-report2">
                    <div class="col-lg-3">
                        <select class="form-control filter-by-text" name="searchRider" data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>" id="searchRider">
                            <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name='searchPaymentType'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select Payment Types</option>
                            <option value="Cash" <?php if ($searchPaymentType == "Cash") { ?>selected <?php } ?>>Cash</option>
                            <?php
                            $payMethod = "Card";
                            if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
                                $payMethod = "Wallet";
                            } ?>
                            <option value="Card"
                                    <?php if ($searchPaymentType == "Card") { ?>selected <?php } ?>><?= $payMethod; ?></option>
                            <?php if ($ENABLE_CORPORATE_PROFILE == "Yes") { ?>
                                <option value="Organization"
                                        <?php if ($searchPaymentType == "Organization") { ?>selected <?php } ?>>Organization
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name="searchDriverPayment" data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment
                                Status
                            </option>
                            <option value="Settelled" <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Settled
                            </option>
                            <option value="Unsettelled" <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Unsettled
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
                    </div>
                </div>
                <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') { ?>
                    <div class="row payment-report payment-report1 payment-report2" style="margin-top: 21px;">
                        <div class="col-lg-2">
                            <select class="form-control" name='eType'>
                                <option value="">Service Type</option>
                                <?php if ($rideEnable == "Yes") { ?>
                                    <option value="Ride" <?php
                                    if ($eType == "Ride") {
                                        echo "selected";
                                    }
                                    ?>><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                    <?php if ($ENABLE_HAIL_RIDES == "Yes" && $APP_TYPE != 'Delivery') { ?>
                                        <option value="HailRide" <?php
                                        if ($eType == "HailRide") {
                                            echo "selected";
                                        }
                                        ?>> Hail <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                    <?php }
                                    if (ENABLE_RENTAL_OPTION == 'Yes' && $APP_TYPE != 'Delivery') { ?>
                                        <option value="RentalRide" <?php
                                        if ($eType == "RentalRide") {
                                            echo "selected";
                                        }
                                        ?>>Taxi Rental </option>
                                    <?php }
                                }
                                if ($deliveryEnable == "Yes") { ?>
                                    <option value="Deliver" <?php
                                    if ($eType == "Deliver") {
                                        echo "selected";
                                    }
                                    ?>>Delivery
                                    </option>
                                    <?php if (ENABLE_MULTI_DELIVERY == "Yes") { ?>
                                        <option value="Multi-Delivery" <?php
                                        if ($eType == "Multi-Delivery") {
                                            echo "selected";
                                        }
                                        ?>>Multi-Delivery
                                        </option>
                                    <?php }
                                }
                                if (($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") && $ufxEnable == "Yes") { ?>
                                    <option value="UberX" <?php
                                    if ($eType == "UberX") {
                                        echo "selected";
                                    }
                                    ?>>Other Services
                                    </option>
                                <?php }
                                if ($MODULES_OBJ->isEnableRideShareService()) {
                                if ($rideEnable == "Yes" && $PACKAGE_TYPE == "SHARK") { ?>
                                    <option value="Pool" <?php
                                    if ($eType == "Pool") {
                                        echo "selected";
                                    }
                                    ?>><?php echo "Taxi " . $langage_lbl_admin['LBL_POOL']; ?> </option>
                                <?php } }
                                if ($MODULES_OBJ->isAirFlightModuleAvailable()) { ?>
                                    <option value="Fly" <?php
                                    if ($eType == "Fly") {
                                        echo "selected";
                                    }
                                    ?>><?php echo $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE']; ?> </option>
                                <?php }
                                if ($MODULES_OBJ->isEnableTaxiBidFeature()) { ?>
                                    <option value="TaxiBid" <?php
                                    if ($eType == "TaxiBid") {
                                        echo "selected";
                                    }
                                    ?>> <?php echo  TAXI_BID; ?>  </option>
                                <?php } ?>

                                <?php if ($MODULES_OBJ->isInterCityFeatureAvailable() && $APP_TYPE != 'Delivery') { ?>

                                <option <?php if ($eType == "InterCity") { echo "selected"; } ?> value="InterCity">InterCity Ride
                                </option>

                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'payment_report.php'"/>
                        <?php if (scount($db_trip) > 0 && $userObj->hasPermission('export-payment-report')) { ?>
                            <button type="button" onClick="reportExportTypes('driver_payment')" class="export-btn001">
                                Export
                            </button>
                        <?php } ?>
                    </b>
                </div>

                <?php include_once "services_formula.php" ; ?>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form name="_list_form" id="_list_form" class="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                                <input type="hidden" name="iTripId" id="iTripId" value="">
                                <input type="hidden" name="ePayDriver" id="ePayDriver" value="">
                                <table class="table table-bordered" id="dataTables-example123">
                                    <thead>

                                        <?php if( 1 != 1) { ?>
                                        <?php $colspan_count = 11; ?>


                                        <tr>
                                            <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>
                                            <th width="10%">
                                                <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                if ($sortby == '1') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "/" . $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php
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
                                            <th width="10%">
                                                <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                if ($sortby == '3') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php
                                                    if ($sortby == 3) {
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
                                            <th style="text-align:center;">A=Total Fare</th>
                                            <?php $nxtChar = "B";
                                            $recAmt = "";
                                            if (in_array(1, $enableCashReceivedCol)) {
                                                $recAmt = $nxtChar;
                                                $colspan_count +=1;
                                                ?>
                                                <th style="text-align:center;"><?= $nxtChar; ?>=Cash Received</th>
                                                <?php
                                                $nxtChar = "C";
                                            }
                                            $pltAmt = $nxtChar; ?>
                                            <th style="text-align:center;"><?= $nxtChar; ?>=Commission Amount</th>
                                            <?php $nxtChar = "C";
                                            if ($nxtChar == "C") {
                                                $nxtChar = "D";
                                            }
                                            $ttaxAmt = $nxtChar; ?>
                                            <th style="text-align:center;"><?= $nxtChar; ?>=Total Tax</th>
                                            <?php $nxtChar = "D";
                                            if ($nxtChar == "D") {
                                                $nxtChar = "E";
                                            }
                                            $tipAmt = "";
                                            if (in_array(1, $enableTipCol)) {
                                                $tipAmt = $nxtChar;
                                                $colspan_count +=1; ?>

                                                <th style="text-align:center;"><?= $nxtChar; ?>=Tip</th>
                                            <?php }
                                            $nxtChar = "E";
                                            if ($nxtChar == "E") {
                                                $nxtChar = "F";
                                            }
                                            $outAmt = $nxtChar; ?>
                                            <th style="text-align:center;"><?= $nxtChar; ?>=<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?>
                                                Outstanding Amount
                                            </th>
                                            <?php $nxtChar = "F";
                                            if ($nxtChar == "F") {
                                                $nxtChar = "G";
                                            }
                                            $bookAmt = "";
                                            if ($hotelPanel > 0 || $kioskPanel > 0) {
                                                $bookAmt = $nxtChar;
                                                $colspan_count +=1; ?>
                                                <th style="text-align:center;"><?= $nxtChar; ?>=Booking Fees</th>
                                            <?php }
                                            $nxtChar = "G";
                                            if ($nxtChar == "G") {
                                                $nxtChar = "H";
                                            }
                                            $ppAmt = $nxtChar; ?>
                                            <th style="text-align:center;"><?= $nxtChar; ?>
                                                = <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> pay / Take Amount
                                            </th>
                                            <th style="text-align:center;">Site Earning</th>
                                            <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Status</th>
                                            <th  style="text-align:center;">Payment method</th>
                                            <th  style="text-align:center;"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</th>
                                            <th width="150px">Settle</th>
                                        </tr>

                                        <?php } ?>

                                        <?php $colspan_count = 11; ?>
                                        <tr>
                                        <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>
                                        <th width="10%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "/" . $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php
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

                                        <th width="10%">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php
                                                if ($sortby == 3) {
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

                                        <th style="text-align:center;">
                                            <?= $ATTR['TOTAL_FARE']['FILED_NAME']; ?>
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['TOTAL_FARE']['DISC']; ?>"></i>
                                        </th>

                                        <?php if (in_array(1, $enableCashReceivedCol)) {

                                            $colspan_count +=1;
                                            ?>
                                            <th style="text-align:center;"><?= $ATTR['CASH_RECEIVED']['FILED_NAME']; ?>
                                                <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['CASH_RECEIVED']['DISC']; ?>"></i></th>
                                        <?php  } ?>
                                        <th style="text-align:center;">
                                            <?= $ATTR['COMMISSION_AMOUNT']['FILED_NAME']; ?>
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['COMMISSION_AMOUNT']['DISC']; ?>"></i>
                                        </th>

                                        <th style="text-align:center;">
                                            <?= $ATTR['TOTAL_TAX']['FILED_NAME']; ?>
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['TOTAL_TAX']['DISC']; ?>"></i>
                                        </th>
                                        <?php if (in_array(1, $enableTipCol)) {
                                            $colspan_count +=1;

                                            ?>
                                            <th style="text-align:center;">
                                                <?= $ATTR['TIP']['FILED_NAME']; ?>
                                                <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['TIP']['DISC']; ?>"></i>
                                            </th>
                                        <?php } ?>
                                        <th style="text-align:center;">
                                            <?= $ATTR['TIP_OUTSTANDING_AMOUNT']['FILED_NAME']; ?>
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['TIP_OUTSTANDING_AMOUNT']['DISC']; ?>"></i>
                                        </th>
                                        <?php
                                        if ($hotelPanel > 0 || $kioskPanel > 0) {
                                            $colspan_count +=1; ?>
                                            <th style="text-align:center;">
                                                <?= $ATTR['BOOKING_FEES']['FILED_NAME']; ?>
                                                <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['BOOKING_FEES']['DISC']; ?>"></i></th>
                                        <?php } ?>
                                        <th style="text-align:center;">
                                            <?= $ATTR['PROVIDER_PAY_TAKE_AMOUNT']['FILED_NAME']; ?>
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="<?= $ATTR['PROVIDER_PAY_TAKE_AMOUNT']['DISC']; ?>"></i>
                                        </th>
                                        <th style="text-align:center;">Site Earning</th>
                                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Status</th>
                                        <th  style="text-align:center;">Payment method</th>
                                        <th  style="text-align:center;"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</th>
                                        <th width="150px">Settle</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $set_unsetarray = array();
                                    if (scount($db_trip) > 0) {

                                        for ($i = 0; $i < scount($db_trip); $i++) {
                                            $class_setteled = "";
                                            if ($db_trip[$i]['eDriverPaymentStatus'] == 'Settelled') {
                                                $class_setteled = "setteled-class";
                                                /*if($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['fCancellationFare'] > 0) {
                                                    $class_setteled = "";
                                                }*/
                                            }
                                            $iTripId = $db_trip[$i]['iTripId'];
                                            $iFare = $iFareOrg = setTwoDecimalPoint($db_trip[$i]['iFare']);
                                            $totTax = setTwoDecimalPoint($db_trip[$i]['fTax1'] + $db_trip[$i]['fTax2']);
                                            $orgName = "";
                                            if (isset($orgNameArr[$db_trip[$i]['iOrganizationId']]) && $orgNameArr[$db_trip[$i]['iOrganizationId']] != "") {
                                                $orgName = "(" . $orgNameArr[$db_trip[$i]['iOrganizationId']] . ")";
                                                $iFare = 0;
                                            }
                                            $poolTxt = "";
                                            if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                $poolTxt = " (Pool)";
                                            }
                                            $totalfare = setTwoDecimalPoint($db_trip[$i]['fTripGenerateFare']);
                                            $site_commission = setTwoDecimalPoint($db_trip[$i]['fCommision']);
                                            $promocodediscount = setTwoDecimalPoint($db_trip[$i]['fDiscount']);
                                            $wallentPayment = setTwoDecimalPoint($db_trip[$i]['fWalletDebit']);
                                            $fOutStandingAmount = setTwoDecimalPoint($db_trip[$i]['fOutStandingAmount']);
                                            $fHotelCommision = setTwoDecimalPoint($db_trip[$i]['fHotelCommision']);
                                            $fTipPrice = setTwoDecimalPoint($db_trip[$i]['fTipPrice']);
                                            $tipPayment = 0;
                                            $siteEarning = $site_commission + $totTax + $fOutStandingAmount + $fHotelCommision; // 
                                            $driver_payment = $dispay_driver_payment = setTwoDecimalPoint(($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision));
                                            if ($db_trip[$i]['vTripPaymentMode'] == "Cash") {
                                                $driver_payment = $dispay_driver_payment = setTwoDecimalPoint($driver_payment - $iFare);
                                                $tot_ifare += $iFare;
                                            }
                         
                                            if ($db_trip[$i]['iActive'] == "Canceled") {
                                                $iFare = $db_trip[$i]['fCancellationFare'] - $iFare;
                                                $driver_payment = $iFareOrg - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision);
                                                if ($db_trip[$i]['vTripPaymentMode'] == "Cash" || $db_trip[$i]['vTripPaymentMode'] == "Organization") {
                                                    $iFare = 0;
                                                    $driver_payment = ($iFareOrg + $wallentPayment) - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision);
                                                    $dispay_driver_payment = setTwoDecimalPoint($driver_payment - $iFare);
                                                }
                                            }
                         
                                            $driverDebitAmt = 0;
                                            if (isset($tripWalletArr[$iTripId]) && $tripWalletArr[$iTripId] > 0) {
                                                $driverDebitAmt = setTwoDecimalPoint($tripWalletArr[$iTripId]);
                                            }
                            
                                            $set_unsetarray[] = $db_trip[$i]['eDriverPaymentStatus'];
                                            $eTypenew = $db_trip[$i]['eType'];
                                            if ($eTypenew == 'Ride') {
                                                $trip_type = 'Ride';
                                            } else if ($eTypenew == 'UberX') {
                                                $trip_type = 'Other Services';
                                            } else if ($eTypenew == 'Multi-Delivery') {
                                                $trip_type = 'Multi-Delivery';
                                            } else {
                                                $trip_type = 'Delivery';
                                            }
                                            if ($eTypenew == 'Multi-Delivery' && $ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY == 'Yes') {
                                                $db_deliveryloc = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE `iTripId` = $iTripId");
                                                if (scount($db_deliveryloc) == 1) {
                                                    $trip_type = 'Delivery';
                                                }
                                            }
                                            if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])) {
                                                $trip_type = 'Fly';
                                            }
                                            $trip_type .= $poolTxt;
                                            if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                $tripTypeTxt = "Rental " . $trip_type . "<br/> ( Hail )";
                                            }else if ($db_trip[$i]['eIsInterCity'] == "Yes") {
                                                $tripTypeTxt = "InterCity " . $trip_type;
                                            } elseif ($db_trip[$i]['isTaxiBid'] == "Yes"){
                                                $tripTypeTxt = TAXI_BID;
                                            } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                $tripTypeTxt = "Rental " . $trip_type;
                                            } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                $tripTypeTxt = "Hail " . $trip_type;
                                            } else {
                                                $tripTypeTxt = $trip_type;
                                            }

                                            $link_page = "invoice.php?iTripId=" . $db_trip[$i]['iTripId'];
                                            if ($db_trip[$i]['eType'] == 'Multi-Delivery') {
                                                $link_page = "invoice_multi_delivery.php?iTripId=" . $db_trip[$i]['iTripId'];
                                            }
                                            $systemTimeZone = date_default_timezone_get();
                                            if ($db_trip[$i]['fCancellationFare'] > 0 && $db_trip[$i]['vTimeZone'] != "") {
                                                $dBookingDate = converToTz($db_trip[$i]['tEndDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone);
                                            } else if ($db_trip[$i]['tStartDate'] != "" && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00" && $db_trip[$i]['vTimeZone'] != "") {
                                                $dBookingDate = $db_trip[$i]['tStartDate'];
                                            } else {
                                                if (!empty($db_trip[$i]['tStartDate']) && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00") {
                                                    $dBookingDate = $db_trip[$i]['tStartDate'];
                                                } else {
                                                    $dBookingDate = $db_trip[$i]['tTripRequestDate'];
                                                }
                                            }
                                            $date_format_data_array = array(
                                                'tdate' => $dBookingDate,
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr class="gradeA <?= $class_setteled ?>">
                                                <td align="center">
                                                    <a href="<?= $link_page ?>"
                                                       target="_blank"><?= $db_trip[$i]['vRideNo']; ?></a>
                                                    <br>
                                                    (<?= $tripTypeTxt; ?>)
                                                </td>
                                                <td>
                                                    <b><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> </b>
                                                    <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['drivername']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?> 
                                                    <br>
                                                    <b><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?> </b>
                                                    <?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['riderName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?> 
                                                </td>
                                                <?php
                                                // $systemTimeZone = date_default_timezone_get();
                                                //$db_trip[$i]['tTripRequestDate'] = converToTz($db_trip[$i]['tTripRequestDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone);
                                                ?>
                                                <td><?= $get_dBookingDate_format['tDisplayDate']."<br/>".$get_dBookingDate_format['tDisplayTime'].$time_zone_difference_text;
                                                //DateTime($dBookingDate, '7'); ?></td>
                                                <td align="center">
                                                    <?php if ($db_trip[$i]['fTripGenerateFare'] != "" && $db_trip[$i]['fTripGenerateFare'] != 0) {
                                                        $totFareHtml = "<a href='javascript:void(0);' style='text-decoration: underline;' onClick='showTotalFareDetails(" . $db_trip[$i]['iUserId'] . "," . $db_trip[$i]['iTripId'] . "," . $db_trip[$i]['vRideNo'] . ",1);'>" . formateNumAsPerCurrency($db_trip[$i]['fTripGenerateFare'], '') . "</a>";
                                                    } else {
                                                        $totFareHtml = "-";
                                                    }
                                                    echo $totFareHtml;
                                                    ?>
                                                </td>
                                                <?php
                                                if (in_array(1, $enableCashReceivedCol)) {
                                                    if ($db_trip[$i]['vTripPaymentMode'] != "Card") {
                                                        ?>
                                                        <td align="center"><?= $totFareHtml = "<a href='javascript:void(0);' style='text-decoration: underline;' onClick='showTotalFareDetails(" . $db_trip[$i]['iUserId'] . "," . $db_trip[$i]['iTripId'] . "," . $db_trip[$i]['vRideNo'] . ",2);'>" . formateNumAsPerCurrency($iFare, '') . "</a>"; ?></td>
                                                        <?php // $TotaliNewFare = $TotaliNewFare + $iFare;
                                                    } else { ?>
                                                        <td align="center">-</td>
                                                    <?php } ?>
                                                <?php } ?>
                                                <td align="center"><?php
                                                    if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['fCommision'], '');
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?></td>
                                                <td align="center"><?= formateNumAsPerCurrency($totTax, ''); ?></td>
                                                <?php if (in_array(1, $enableTipCol)) { ?>
                                                    <td align="center">
                                                        <?php
                                                        if ($db_trip[$i]['fTipPrice'] != "0") {
                                                            echo formateNumAsPerCurrency($db_trip[$i]['fTipPrice'], '');
                                                            //echo $db_trip[$i]['fTipPrice'];
                                                        } else {
                                                            echo "-";
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                                <td align="center">
                                                    <?php
                                                    if ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0) {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], '');
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </td>
                                                <?php if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                                    <td align="center"><?php
                                                        if ($db_trip[$i]['fHotelCommision'] != "" && $db_trip[$i]['fHotelCommision'] != 0) {
                                                            echo formateNumAsPerCurrency($db_trip[$i]['fHotelCommision'], '');
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?></td>
                                                <?php } ?>
                                                <td align="center">
                                                    <?php
                                                    if ($driver_payment != "" && $driver_payment != 0) {
                                                        $tax_sign = ($totTax > 0) ? "-" : "";
                                                        $outstd_sign = ($db_trip[$i]['fOutStandingAmount'] > 0) ? "-" : "";
                                                        ?>
                                                        <a href='javascript:void(0);'
                                                           data-provideamt="<?= formateNumAsPerCurrency($dispay_driver_payment, ''); ?>"
                                                           data-symbol="<?= $vSymbol; ?>"
                                                           data-payMode="<?= $db_trip[$i]['vTripPaymentMode']; ?>"
                                                           data-totFare="<?= formateNumAsPerCurrency($db_trip[$i]['fTripGenerateFare'], '') ?>"
                                                           data-ifare="<?= formateNumAsPerCurrency($iFare, ''); ?>"
                                                           data-pfees=" <?= formateNumAsPerCurrency($db_trip[$i]['fCommision'], '') ?>"
                                                           data-tax="<?= $tax_sign . formateNumAsPerCurrency($totTax, '') ?>"
                                                           data-tip="<?= formateNumAsPerCurrency($db_trip[$i]['fTipPrice'], '') ?>"
                                                           data-out="<?= $outstd_sign . formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], ''); ?>"
                                                           data-book="<?= formateNumAsPerCurrency($db_trip[$i]['fHotelCommision'], ''); ?>"
                                                           style='text-decoration: underline;'
                                                           onClick='showProvicePayDetails(this);'><?= formateNumAsPerCurrency($dispay_driver_payment, ''); ?></a>
                                                    <?php } else {
                                                        echo "-";
                                                    } ?>

                                                </td>
                                                <td align="center"><?= formateNumAsPerCurrency($siteEarning, ''); ?></td>
                                                <td align="center"><?= $db_trip[$i]['iActive']; ?></td>
                                                <?php if ($db_trip[$i]['vTripPaymentMode'] == "Card" && $db_trip[$i]['ePayWallet'] == 'Yes') { ?>
                                                    <td align="center"><?= $langage_lbl_admin['LBL_WALLET_TXT']; ?></td>
                                                <?php } else { ?>
                                                    <td align="center"><?= $db_trip[$i]['vTripPaymentMode'] . "<br>" . $orgName; ?></td>
                                                <?php } ?>
                                                <td align="center"><?php
                                                    if ($db_trip[$i]['eDriverPaymentStatus'] == "Settelled") {
                                                        /*if($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['fCancellationFare'] > 0) {
                                                            echo "Unsettelled";
                                                        }
                                                        else {
                                                            echo "Settelled";
                                                        }*/
                                                        echo "Settled";
                                                    } else if ($db_trip[$i]['eDriverPaymentStatus'] == "Unsettelled") {
                                                        echo "Unsettled";
                                                    } else {
                                                        echo $db_trip[$i]['eDriverPaymentStatus'];
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($db_trip[$i]['eDriverPaymentStatus'] == "Settelled") {
                                                        /*if($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['fCancellationFare'] > 0) { ?>
                                                            <input class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iTripId'] ?>" id="iTripId_<?= $db_trip[$i]['iTripId'] ?>" name="iTripId[]">
                                                        <?php }*/
                                                    } else if ($db_trip[$i]['eDriverPaymentStatus'] == 'Unsettelled') {
                                                        ?>
                                                        <input class="validate[required]" type="checkbox"
                                                               value="<?= $db_trip[$i]['iTripId'] ?>"
                                                               id="iTripId_<?= $db_trip[$i]['iTripId'] ?>"
                                                               name="iTripId[]">
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } } else { ?>
                                            <tr class="gradeA">
                                                <td colspan="17" style="text-align:center;">No Payment Details Found.</td>
                                            </tr>
                                        <?php } ?>

                                        <tr>
                                            <td colspan="<?= $colspan_count ?>"></td>
                                            <?php if (in_array("Unsettelled", $set_unsetarray)) { ?>
                                                <td align="right">
                                                    <a onClick="Paytodriver()" href="javascript:void(0);" class="btn btn-primary">Mark As Settled</a>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-lg-offset-6">
                    <div class="admin-notes">
                        <h4>Summary:</h4>
                        <ul>
                            <li><strong>Total Fare: </strong><?= formateNumAsPerCurrency(cleanNumber($tot_fare), ''); ?></li>
                            <?php if (in_array(1, $enableCashReceivedCol)) { ?>
                                <li><strong>Total Cash Received: </strong><?= formateNumAsPerCurrency(cleanNumber($TotaliNewFare), ''); ?></li>
                            <?php } ?>
                            
                            <li><strong>Total Commission Amount (Total Earning): </strong><?= formateNumAsPerCurrency(cleanNumber($tot_site_commission), ''); ?></li>
                            <li><strong>Total Tax: </strong><?= formateNumAsPerCurrency(cleanNumber($tot_tax), ''); ?></li>
                            <?php if (in_array(1, $enableTipCol)) { ?>
                                <li><strong>Total Tip: </strong><?= formateNumAsPerCurrency(cleanNumber($total_tip), ''); ?></li>
                            <?php } ?>
                            
                            <li><strong>Total Trip/Job Outstanding Amount: </strong><?= formateNumAsPerCurrency(cleanNumber($tot_outstandingAmount), ''); ?></li>
                            <?php if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                <li><strong>Total Booking Fees: </strong><?= formateNumAsPerCurrency(cleanNumber($tot_hotel_commision), ''); ?></li>
                            <?php } ?>
                            
                            <li><strong>Total Driver pay / Take Amount: </strong><?= formateNumAsPerCurrency(cleanNumber($tot_driver_refund), ''); ?></li>
                            <li><strong>Total Site Earning: </strong><?= formateNumAsPerCurrency(cleanNumber($totSiteEarning), ''); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="admin-notes">
                <h4>Note:</h4>


                <?php  echo formula(); ?>
                <!--<ul>
                    <li>
                        Payment Mode : Card
                        <br><?php /*= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; */?> Pay / Take Amount (<?php /*= $ppAmt; */?>) =
                        A-<?php /*= $pltAmt; */?>-<?php /*= $ttaxAmt; */?><?php
/*                        if ($tipAmt != "") {
                            echo " + " . $tipAmt;
                        }
                        */?><?php /*= " - " . $outAmt; */?><?php
/*                        if ($bookAmt != "") {
                            echo " - " . $bookAmt;
                        }
                        */?>
                    </li>
                    <?php /*if ($recAmt != "") { */?>
                        <li>
                            Payment Mode : Cash
                            <br><?php /*= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; */?> Pay / Take Amount (<?php /*= $ppAmt; */?>) =
                            (A-<?php /*= $pltAmt; */?>-<?php /*= $ttaxAmt; */?><?php
/*                            if ($tipAmt != "") {
                                echo " + " . $tipAmt;
                            }
                            */?><?php /*= " - " . $outAmt; */?>



                            <?php
/*                            if ($bookAmt != "") {
                                echo " - " . $bookAmt . ")-";
                            } else {
                                echo ") - ";
                            }
                            */?><?php /*= $recAmt; */?>
                        </li>
                    <?php /*} */?>
                </ul>-->


            </div>
            
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/payment_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>">
    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>">
    <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>">
    <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>">
    <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>">
    <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="eType" value="<?php echo $eType; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<div class="modal fade" id="fare_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <span id="fareRideNo"></span>
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id='faredata'></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <!--<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>-->
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/rider-icon.png" alt="">
                    </i>
                    <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" class="imageIcons">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
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
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons1">
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
<?php include_once('footer.php'); ?>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<!--  <link rel="stylesheet" href="css/select2/select2.min.css" />

 <script src="js/plugins/select2.min.js"></script> -->
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<?php include_once('searchfunctions.php'); ?>
<script>
    $('#dp4').datepicker().on('changeDate', function (ev) {
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
    $('#dp5').datepicker().on('changeDate', function (ev) {
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

    function showProvicePayDetails(invelem) {
        //console.log(invelem);
        var provideAmt = $(invelem).attr("data-provideamt");
        var symbol = $(invelem).attr("data-symbol");
        var payMode = $(invelem).attr("data-paymode");
        var totFareAmt = $(invelem).attr("data-totfare");
        var cashReceivedAmt = $(invelem).attr("data-ifare");
        var platformFeeAmt = $(invelem).attr("data-pfees");
        var taxAmt = $(invelem).attr("data-tax");
        var tipAmt = $(invelem).attr("data-tip");
        var outStandingAmt = $(invelem).attr("data-out");
        var bookingFeeAmt = $(invelem).attr("data-book");
        bookingFeeAmt = (bookingFeeAmt < 0) ? '-' + bookingFeeAmt : bookingFeeAmt;
        var fareHtml = '<table style="width:100%" cellpadding="5" cellspacing="0" border="0"><tbody>';
        fareHtml += '<tr><td><b>Description</b></td><td align="right"><b>Amount</b></td></tr>';
        fareHtml += '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';
        fareHtml += '<tr><td><b>A</b>-Total Fare</td><td align="right">' + totFareAmt + '</td></tr>';
        fareHtml += '<tr><td><b><?= $pltAmt; ?></b>-Platform Fees</td><td align="right">' + '-' + platformFeeAmt + '</td></tr>';
        fareHtml += '<tr><td><b><?= $ttaxAmt; ?></b>-Total Tax</td><td align="right">' + taxAmt + '</td></tr>';
        <?php if (in_array(1, $enableTipCol)) { ?>
        fareHtml += '<tr><td><b><?= $tipAmt; ?></b>-Tip</td><td align="right">' + tipAmt + '</td></tr>';
        <?php } ?>
        fareHtml += '<tr><td><b><?= $outAmt; ?></b>-Outstanding Amount</td><td align="right">' + outStandingAmt + '</td></tr>';
        <?php if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
        fareHtml += '<tr><td><b><?= $bookAmt; ?></b>-Booking Fees</td><td align="right">' + bookingFeeAmt + '</td></tr>';
        <?php } ?>
        <?php if (in_array(1, $enableCashReceivedCol)) { ?>
        if (payMode == "Cash") {
            fareHtml += '<tr><td><b><?= $recAmt; ?></b>-Cash Received</td><td align="right">' + '-' + cashReceivedAmt + '</td></tr>';
        }
        <?php } ?>
        fareHtml += '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';
        fareHtml += '<tr><td><b><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Pay / Take Amount</b></td><td align="right"><b>' + provideAmt + '</b></td></tr>';
        $("#faredata").html("");
        $("#fare_detail_modal").modal('show');
        $("#fareRideNo").text("<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Pay / Take Amount Breakdown");
        $("#faredata").html(fareHtml);
    }

    function showTotalFareDetails(userId, tripId, rideNo, actionId) {
        if (actionId == 1) {
            var action = "totalFare";
            var headTxt = "Total Fare";
        } else if (actionId == 2) {
            var action = "cashreceived";
            var headTxt = "Cash Received";
        }
        // $.ajax({
        //     type: 'POST',
        //     url: 'ajax_get_fare_details.php',
        //     data: {'action': action, 'userId': userId, 'tripId': tripId},
        //     cache: false,
        //     success: function (data) {
        //         $("#faredata").html("");
        //         $("#fare_detail_modal").modal('show');
        //         $("#fareRideNo").text(headTxt + " Breakdown For <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> No :" + rideNo);
        //         $("#faredata").html(data);
        //     },
        //     error: function (e) {
        //         alert("Fare details not found");
        //         $("#fare_detail_modal").modal('hide');
        //         return false;
        //     }
        // });

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_fare_details.php',
            'AJAX_DATA': {'action': action, 'userId': userId, 'tripId': tripId},
            'REQUEST_CACHE': false
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#faredata").html("");
                $("#fare_detail_modal").modal('show');
                $("#fareRideNo").text(headTxt + " Breakdown For <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> No :" + rideNo);
                $("#faredata").html(data);
            } else {
                console.log(response.result);
                $("#fare_detail_modal").modal('hide');
                return false;
            }
        });
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
</script>
</body>
<!-- END BODY-->
</html>