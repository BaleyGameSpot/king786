<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-organization-payment-report')) {
    $userObj->redirect();
}
$script = 'OrganizationPaymentReport';
function cleanNumber($num)
{
    return str_replace(',', '', $num);
}
function setTwoDecimalValue($amount)
{
    $amount = number_format($amount, 2);
    return $amount;
}
$org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany";
$db_organization = $obj->MySQLSelect($org_sql);
$orgNameArr = array();
for ($g = 0; $g < scount($db_organization); $g++) {
    $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];
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
$searchOrganization = isset($_REQUEST['searchOrganization']) ? $_REQUEST['searchOrganization'] : '';
$searchUser = isset($_REQUEST['searchUser']) ? $_REQUEST['searchUser'] : '';
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
        $ssql .= " AND tr.vRideNo ='" . $serachTripNo . "'";
    }
    if ($searchOrganization != '') {
        $ssql .= " AND tr.iOrganizationId ='" . $searchOrganization . "'";
    }
    if ($searchUser != '') {
        $ssql .= " AND tr.iUserId ='" . $searchUser . "'";
    }
    if ($searchDriverPayment != '') {
        $ssql .= " AND tr.eOrganizationPaymentStatus ='" . $searchDriverPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND tr.vTripPaymentMode ='" . $searchPaymentType . "'";
    }
    if ($eType != '') {
        if ($eType == 'Fly') {
            $ssql .= " AND tr.iFromStationId > 0 AND tr.iToStationId > 0";
        } else if ($eType == 'Ride') {
            $ssql .= " AND tr.eType ='" . $eType . "' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' AND  tr.iFromStationId = 0 AND tr.iToStationId = 0 ";
        } elseif ($eType == 'RentalRide') {
            $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
        } elseif ($eType == 'HailRide') {
            $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
        } else {
            $ssql .= " AND tr.eType ='" . $eType . "' ";
        }
    }
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$commonWhereCon = "if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iOrganizationId >0 AND tr.iActive ='Finished' AND tr.eSystem='General'";
$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePoolRide,tr.iOrganizationId,tr.iFare,tr.fTripGenerateFare,tr.fHotelCommision,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.fOutStandingAmount,tr.vTripPaymentMode,( SELECT COUNT(tr.iTripId) FROM trips AS tr WHERE $commonWhereCon $ssql $trp_ssql) AS Total FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE $commonWhereCon $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);

$total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $total_results = $start = 0;
if (isset($totalData[0]['Total'])) {
    $total_results = $totalData[0]['Total'];
}
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
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
$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePoolRide,tr.iOrganizationId,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eOrganizationPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,tr.ePaymentBy,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE $commonWhereCon $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = scount($db_trip);
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
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Organization Payment Report</title>
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
                        <h2>Organization Payment Report</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search"/>
                    <div>
                        <div style="float: left;">
                            <h3>Search by Date...</h3>
                        </div>
                        <?php if ($userObj->hasPermission('manage-org-cancellation-payment-report')) { ?>
                            <div style="text-align: right;font-size:15px;">
                                <a href="org_cancellation_payment_report.php" target="_blank" class="btn btn-primary">
                                    View Cancelled <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Payment Report
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                    <span>
                        <a style="cursor:pointer"
                           onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                    </span>
                    <span>
                        <input type="text" id="dp4" name="startDate" placeholder="From Date"
                               class="form-control" value="" readonly=""
                               style="cursor:default; background-color: #fff"/>
                        <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control"
                               value="" readonly="" style="cursor:default; background-color: #fff"/>

                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text driver_container"
                                    name='searchOrganization' data-text="Select Organization"
                                    id="searchOrganization">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_ORGANIZATION']; ?></option>
                                <?php foreach ($db_organization as $dbd) { ?>
                                    <option value="<?php echo $dbd['iOrganizationId']; ?>" <?php
                                    if ($searchOrganization == $dbd['iOrganizationId']) {
                                        echo "selected";
                                    }
                                    ?>><?php echo clearName($dbd['driverName']); ?> --   <?php echo 'Email: ' . clearEmail($dbd['vEmail']); ?> </option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') { ?>
                            <div class="col-lg-3 select001">
                                <select class="form-control" name='eType'>
                                    <option value="">Service Type</option>
                                    <?php if ($APP_TYPE == "Ride" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") { ?>
                                        <option value="Ride" <?php
                                        if ($eType == "Ride") {
                                            echo "selected";
                                        }
                                        ?>><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                    <?php } ?>
                                    <?php if (ENABLE_RENTAL_OPTION == 'Yes' && $APP_TYPE != 'Delivery') { ?>
                                        <option value="RentalRide" <?php
                                        if ($eType == "RentalRide") {
                                            echo "selected";
                                        }
                                        ?>>Taxi Rental</option>
                                    <?php } ?>
                                    <?php if (($APP_TYPE == "Ride" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") && $PACKAGE_TYPE == "SHARK") { ?>
                                        <option value="Pool" <?php
                                        if ($eType == "Pool") {
                                            echo "selected";
                                        }
                                        ?>><?php echo"Taxi Pool"; ?> </option>
                                    <?php }
                                    if ($MODULES_OBJ->isAirFlightModuleAvailable(1)) { ?>
                                        <option value="Fly" <?php
                                        if ($eType == "Fly") {
                                            echo "selected";
                                        }
                                        ?>><?php echo $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE']; ?> </option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </span>
                </div>
                <div class="row payment-report payment-report1 payment-report2">
                    <div class="col-lg-3">
                        <select class="form-control filter-by-text" name='searchUser'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>"
                                id="searchRider">
                            <option value="">
                                Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                            
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name='searchPaymentType'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select Payment Type</option>
                            <option value="Cash" <?php if ($searchPaymentType == "Cash") { ?>selected <?php } ?>>Cash</option>
                            <option value="Card" <?php if ($searchPaymentType == "Card") { ?>selected <?php } ?>>Card</option>
                            <option value="Organization"
                                    <?php if ($searchPaymentType == "Organization") { ?>selected <?php } ?>>Organization
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name='searchDriverPayment'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select Payment Status</option>
                            <option value="Settelled"
                                    <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Settled
                            </option>
                            <option value="Unsettelled"
                                    <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Unsettled
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <input type="text" id="serachTripNo" name="serachTripNo"
                               placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number"
                               class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
                    </div>
                </div>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'org_payment_report.php'"/>
                        <?php if (scount($db_trip) > 0 && $userObj->hasPermission('export-organization-payment-report')) { ?>
                        <button type="button" onClick="reportExportTypes('organization_payment')" class="export-btn001">
                            Export
                        </button>
                    </b>
                    <?php } ?>
                </div>
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
                                    <tr>
                                        <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                            <th width="10%">
                                                <a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                if ($sortby == '6') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']; ?> <?php
                                                    if ($sortby == 6) {
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
                                        <?php } ?>
                                        <th style="text-align:center;"><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>

                                        <th width="10%">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php
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
                                        <th width="10%" style="text-align:center;">
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
                                        <th style="text-align:center;">B=Platform Fees</th>
                                        <th style="text-align:center;">C = Wallet Debit</th>
                                        <?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
                                            <th>D=Tip</th>
                                            <th>E=<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Outstanding Amount
                                            </th>
                                        <?php } else { ?>
                                            <th>F=<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Outstanding Amount
                                            </th>
                                        <?php } ?>
                                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_ORGANIZATION']; ?> pay
                                            Amount
                                        </th>
                                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Status</th>
                                        <th style="text-align:center;">Payment method</th>
                                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_ORGANIZATION']; ?> Payment Status</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $set_unsetarray = array();
                                    
                                    if (scount($db_trip) > 0) {
                                        $systemTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($db_trip); $i++) {
                                            $orfName = "";
                                            if (isset($orgNameArr[$db_trip[$i]['iOrganizationId']]) && $orgNameArr[$db_trip[$i]['iOrganizationId']] != "") {
                                                $orfName = "(Organization: " . $orgNameArr[$db_trip[$i]['iOrganizationId']] . ")";
                                            }
                                            $totalfare = trip_currency_payment($db_trip[$i]['fTripGenerateFare']);
                                            $site_commission = trip_currency_payment($db_trip[$i]['fCommision']);
                                            $hotel_commision = trip_currency_payment($db_trip[$i]['fHotelCommision']);
                                            $promocodediscount = trip_currency_payment($db_trip[$i]['fDiscount']);
                                            $wallentPayment = trip_currency_payment($db_trip[$i]['fWalletDebit']);
                                            $fTipPrice = trip_currency_payment($db_trip[$i]['fTipPrice']);
                                            $fOutStandingAmount = trip_currency_payment($db_trip[$i]['fOutStandingAmount']);
                                            if ($db_trip[$i]['vTripPaymentMode'] == "Cash") {
                                                //$driver_payment = ($promocodediscount + $wallentPayment) - ($site_commission + $fOutStandingAmount + $hotel_commision);
                                            } else {
                                                //$driver_payment = ($fTipPrice + $totalfare) - ($site_commission + $fOutStandingAmount + $hotel_commision);
                                            }
                                            $driver_payment = ($fTipPrice + $totalfare) - ($site_commission + $fOutStandingAmount + $hotel_commision);
                                            // $orgPayAmount = $totalfare - $promocodediscount;
                                            $orgPayAmount = 0;
                                            $tot_fare += $totalfare;
                                            $tot_site_commission += $site_commission;
                                            $tot_hotel_commision += $hotel_commision;
                                            $tot_promo_discount += $promocodediscount;
                                            $tot_wallentPayment += $wallentPayment;
                                            $total_tip += $fTipPrice;
                                            $tot_driver_refund += $driver_payment;
                                            $cashPayment = $site_commission;
                                            $cardPayment = $orgPayAmount - $site_commission;
                                            $tot_outstandingAmount += $fOutStandingAmount;
                                            $class_setteled = "";
                                            if ($db_trip[$i]['eOrganizationPaymentStatus'] == 'Settelled') {
                                                $class_setteled = "setteled-class";
                                            }
                                            $poolTxt = "";
                                            if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                $poolTxt = " (Pool)";
                                            }
                                            $tipPayment = 0;
                                            if ($ENABLE_TIP_MODULE == "Yes") {
                                                $tipPayment = $fTipPrice;
                                            }
                                            $set_unsetarray[] = $db_trip[$i]['eOrganizationPaymentStatus'];
                                            $eTypenew = $db_trip[$i]['eType'];
                                            if ($eTypenew == 'Ride') {
                                                $trip_type = 'Ride';
                                            } else if ($eTypenew == 'UberX') {
                                                $trip_type = 'Other Services';
                                            } else {
                                                $trip_type = 'Delivery';
                                            }
                                            $trip_type .= $poolTxt;
                                            if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])) {
                                                $trip_type = 'Fly';
                                            }

                                            $date_format_data_array = array(
                                                'tdate' => (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['tTripRequestDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone) : $db_trip[$i]['tTripRequestDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr class="gradeA <?= $class_setteled ?>">
                                                <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                                    <td align="left">
                                                        <?php
                                                        if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                            echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                        } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                            echo "Rental " . $trip_type;
                                                        } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                            echo "Hail " . $trip_type;
                                                        } else {
                                                            echo $trip_type;
                                                        }
                                                        echo "<br>" . $orfName;
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                                <td align="center"><?= $db_trip[$i]['vRideNo']; ?></td>
                                                <!--<td><?= clearName($db_trip[$i]['drivername']); ?></td>-->
												<td>
                                                   <?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['riderName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
											     </td>
                                                <td  align="center"><?= $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($db_trip[$i]['tTripRequestDate']); ?></td>
                                                <td align="center">
                                                    <?php
                                                    if ($totalfare != "" && $totalfare != 0) {
                                                        // echo $totalfare;
                                                        echo formateNumAsPerCurrency($totalfare, '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center"><?php
                                                    if ($site_commission != "" && $site_commission != 0) {
                                                        //echo $promocodediscount;
                                                        echo formateNumAsPerCurrency($site_commission, '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?></td>
                                                <td align="center"><?php
                                                    if ($wallentPayment != "" && $wallentPayment != 0) {
                                                        // echo $wallentPayment;
                                                        echo formateNumAsPerCurrency($wallentPayment, '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?></td>
                                                <?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
                                                    <td align="center">
                                                        <?php
                                                        if ($fTipPrice != "0") {
                                                            // echo $fTipPrice;
                                                            echo formateNumAsPerCurrency($fTipPrice, '');
                                                        } else {
                                                            echo "0";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <?php
                                                        if ($fOutStandingAmount != "" && $fOutStandingAmount != 0) {
                                                            // echo $fOutStandingAmount;
                                                            echo formateNumAsPerCurrency($fOutStandingAmount, '');
                                                        } else {
                                                            echo "0";
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } else { ?>
                                                    <td align="center">
                                                        <?php
                                                        if ($fOutStandingAmount != "" && $fOutStandingAmount != 0) {
                                                            // echo $fOutStandingAmount;
                                                            echo formateNumAsPerCurrency($fOutStandingAmount, '');
                                                        } else {
                                                            echo "0";
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                                <td align="center">
                                                    <?php
                                                    if ($orgPayAmount != "" && $orgPayAmount != 0) {
                                                        // echo $orgPayAmount;
                                                        echo formateNumAsPerCurrency($orgPayAmount, '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center"><?= $db_trip[$i]['iActive']; ?></td>
                                                <td align="center">
                                                    <?php if (strtolower($db_trip[$i]['vTripPaymentMode']) == "organization") {
                                                        echo $db_trip[$i]['vTripPaymentMode'];
                                                        if (strtolower($db_trip[$i]['ePaymentBy']) == "passenger") {
                                                            echo "<br>" . $langage_lbl_admin['LBL_PAYMENT_DONE_BY_USER'];
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center"><?php
                                                    if ($db_trip[$i]['eOrganizationPaymentStatus'] == "Settelled") {
                                                        echo "Settled";
                                                    } else if ($db_trip[$i]['eOrganizationPaymentStatus'] == "Unsettelled") {
                                                        echo "Unsettled";
                                                    } else {
                                                        echo $db_trip[$i]['eOrganizationPaymentStatus'];
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php
                                                    if ($db_trip[$i]['eOrganizationPaymentStatus'] == 'Unsettelled') {
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
                                        <?php } ?>
                                        <tr class="gradeA">
                                            <td colspan="12" align="right">Total Fare</td>
                                            <td align="right"
                                                colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_fare), ''); ?></td>
                                        </tr>
                                        <tr class="gradeA">
                                            <td colspan="12" align="right">Total Platform Fees</td>
                                            <td align="right"
                                                colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_site_commission), '') ?></td>
                                        </tr>
                                        <tr class="gradeA">
                                            <td colspan="12" align="right">Total Promo Discount</td>
                                            <td align="right"
                                                colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_promo_discount), '') ?></td>
                                        </tr>
                                        <tr class="gradeA">
                                            <td colspan="12" align="right">Total Wallet Debit</td>
                                            <td align="right"
                                                colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_wallentPayment), '') ?></td>
                                        </tr>
                                        <?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
                                            <tr class="gradeA">
                                                <td colspan="12" align="right">Total Tip Amount</td>
                                                <td align="right"
                                                    colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($total_tip), '') ?></td>
                                            </tr>
                                            <tr class="gradeA">
                                                <td colspan="12" align="right">Total Trip Outstanding Amount</td>
                                                <td align="right"
                                                    colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_outstandingAmount), '') ?></td>
                                            </tr>
                                            <tr class="gradeA">
                                                <td colspan="12" align="right">Total Booking Fees</td>
                                                <td align="right"
                                                    colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_hotel_commision), '') ?></td>
                                            </tr>
                                            <tr class="gradeA">
                                                <td colspan="12" align="right">Total Payment Amount</td>
                                                <td align="right"
                                                    colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_driver_refund), '') ?></td>
                                            </tr>
                                        <?php } else { ?>
                                            <tr class="gradeA">
                                                <td colspan="12" align="right">Total Trip Outstanding Amount</td>
                                                <td align="right"
                                                    colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_outstandingAmount), '') ?></td>
                                            </tr>
                                            <tr class="gradeA">
                                                <td colspan="12" align="right">Total Payment Amount</td>
                                                <td align="right"
                                                    colspan="3"><?= formateNumAsPerCurrency(setTwoDecimalValue($tot_driver_refund), '') ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if (in_array("Unsettelled", $set_unsetarray)) { ?>
                                            <tr class="gradeA">
                                                <td colspan="14" align="right">
                                                    <a onClick="PaytoorganizationforCancel()" href="javascript:void(0);" class="btn btn-primary">Mark As Settled</a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="14" style="text-align:center;">No Payment Details Found.</td>
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
            <div class="admin-notes">
                <h4>Note:</h4>
                <ul>
                    <li>
                        Total Payment Amount = (Total Tip Amount + Total Fare + Total Promo Discount) - (Total Platform
                        Fees + Total Trip Outstanding Amount + Total Booking Fees)
                    </li>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/payment_report.php" method="post">
    <input type="hidden" name="orgpay" id="orgpay" value="1">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="searchDriver" value="<?php echo $searchOrganization; ?>">
    <input type="hidden" name="searchRider" value="<?php echo $searchUser; ?>">
    <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>">
    <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>">
    <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="eType" value="<?php echo $eType; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>

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
                <div id="imageIcons">
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
<?php include_once('footer.php'); ?>
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
 
 
    $('body').on('keyup', '.select2-search__field', function () {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ($(".select2-results__options").is(".select2-results__message")) {
            $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });
    function formatDesign(item) {
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
    }

    function formatDesignnew(item) {
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#searchRider").each(function () {
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
                            usertype: 'Rider',
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

                    cache: false

                }

            }); //theme: 'classic'

        });

    });


    var sIdRider = '<?= $searchUser;?>';

    var sSelectRider = $('select.filter-by-text#searchRider');

    if (sIdRider != '') {

        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdRider + '&usertype=Rider',

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

                sSelectRider.append(option).trigger('change');

            } else {

                console.log(response.result);

            }

        });

    }


    $(function () {

        $("select.filter-by-text#searchOrganization").each(function () {

            $(this).select2({

                placeholder: $(this).attr('data-text'),

                allowClear: true,

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
                            usertype: 'Organization',
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

                    cache: false

                }


            }); //theme: 'classic'

        });

    });

    var sIdOrganization = '<?= $searchOrganization;?>';

    var sSelectOrganization = $('select.filter-by-text#searchOrganization');

    if (sIdOrganization != '') {

        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdOrganization + '&usertype=Organization',

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

                sSelectOrganization.append(option).trigger('change');

            } else {

                console.log(response.result);

            }

        });

    }
</script>
</body>
<!-- END BODY-->
</html>