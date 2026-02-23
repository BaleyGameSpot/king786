<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-org-cancellation-payment-report')) {
    $userObj->redirect();
}
$script = 'Org_Cancellation_Payment_Report';

function cleanNumber($num) {
    return str_replace(',', '', $num);
}

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY tr.iTripId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY ru.vName ASC";
    else
        $ord = " ORDER BY ru.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY trp.tTripRequestDate ASC";
    else
        $ord = " ORDER BY trp.tTripRequestDate DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY d.vName ASC";
    else
        $ord = " ORDER BY d.vName DESC";
}
if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY u.vName ASC";
    else
        $ord = " ORDER BY u.vName DESC";
}
if ($sortby == 6) {
    if ($order == 0)
        $ord = " ORDER BY trp.eType ASC";
    else
        $ord = " ORDER BY trp.eType DESC";
}
//End Sorting
// Start Search Parameters
$ssql = "";
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchOrganization = isset($_REQUEST['searchOrganization']) ? $_REQUEST['searchOrganization'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$searchPaymentByUser = isset($_REQUEST['searchPaymentByUser']) ? $_REQUEST['searchPaymentByUser'] : '';

if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(trp.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(trp.tTripRequestDate) <='" . $endDate . "'";
    }
    if ($serachTripNo != '') {
        $ssql .= " AND trp.vRideNo ='" . $serachTripNo . "'";
    }
    if ($searchOrganization != '') {
        $ssql .= " AND tr.iOrganizationId ='" . $searchOrganization . "'";
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
        $ssql .= " AND tr.ePaidToDriver ='" . $searchDriverPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND tr.vTripPaymentMode ='" . $searchPaymentType . "'";
    }
    if ($eType != '') {
        if ($eType == 'Fly') {
            $ssql .= " AND trp.iFromStationId > 0 AND trp.iToStationId > 0";
        } else if ($eType == 'Ride') {
            //$ssql .= " AND trp.eType ='" . $eType . "' AND trp.iRentalPackageId = 0 AND trp.eHailTrip = 'No' ";
            $ssql .= " AND trp.eType ='" . $eType . "' AND trp.iRentalPackageId = 0 AND trp.eHailTrip = 'No' AND  trp.iFromStationId = 0 AND trp.iToStationId = 0 ";
        } elseif ($eType == 'RentalRide') {
            $ssql .= " AND trp.eType ='Ride' AND trp.iRentalPackageId > 0";
        } elseif ($eType == 'HailRide') {
            $ssql .= " AND trp.eType ='Ride' AND trp.eHailTrip = 'Yes'";
        } else {
            $ssql .= " AND trp.eType ='" . $eType . "' ";
        }
    }
}
if ($searchPaymentByUser != '') {
    $ssql .= " AND tr.ePaidByPassenger ='" . $searchPaymentByUser . "'";
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And trp.tTripRequestDate > '" . WEEK_DATE . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT trp.ePoolRide,org.vCompany AS Organization,tr.iTripId,tr.iTripOutstandId,tr.iDriverId,tr.iUserId, tr.fCommision, tr.fDriverPendingAmount,tr.ePaidByPassenger,tr.ePaidToDriver,tr.vTripPaymentMode,trp.eType,trp.vRideNo,trp.tTripRequestDate,trp.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName FROM trip_outstanding_amount AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN trips AS trp ON trp.iTripId = tr.iTripId  LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId INNER JOIN organization org ON org.iOrganizationId=tr.iOrganizationId WHERE 1 = 1 AND tr.fDriverPendingAmount > 0 AND trp.eSystem = 'General' AND tr.ePaymentBy='Organization' $ssql $trp_ssql ";
$totalData = $obj->MySQLSelect($sql);
$driver_payment = $tot_site_commission = $tot_driver_refund = 0.00;
foreach ($totalData as $dtps) {
    $driver_payment = $dtps['fDriverPendingAmount'];
    $site_commission = $dtps['fCommision'];
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $tot_driver_refund = $tot_driver_refund + cleanNumber($driver_payment);
}
$total_results = scount($totalData);
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
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT trp.ePoolRide,org.vCompany AS Organization,tr.iTripId,tr.iTripOutstandId,tr.iDriverId,tr.iUserId, tr.fCommision, tr.fPendingAmount, tr.fDriverPendingAmount, tr.fWalletDebit,tr.ePaidByPassenger,tr.ePaidToDriver,tr.vTripPaymentMode,trp.iRentalPackageId,trp.eType,trp.vRideNo,trp.tTripRequestDate,tr.vTripAdjusmentId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.ePaymentBy,trp.vTimeZone FROM trip_outstanding_amount AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN trips AS trp ON trp.iTripId = tr.iTripId  LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId INNER JOIN organization org ON org.iOrganizationId=tr.iOrganizationId WHERE  1 = 1 AND tr.fDriverPendingAmount > 0 AND trp.eSystem = 'General' AND tr.ePaymentBy='Organization' $ssql $trp_ssql $ord LIMIT $start, $per_page";

$db_trip = $obj->MySQLSelect($sql);

$endRecord = scount($db_trip);

//Added By HJ On 22-07-2019 For Solved Bug - 5946 Start
$tripNumArr = array();
$getTripData = $obj->MySQLSelect("SELECT iTripId,vRideNo FROM trips");
for ($r = 0; $r < scount($getTripData); $r++) {
    $tripNumArr[$getTripData[$r]['iTripId']] = $getTripData[$r]['vRideNo'];
}
//Added By HJ On 22-07-2019 For Solved Bug - 5946 End
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
$org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization WHERE eStatus != 'Deleted' order by vCompany";
$db_organization = $obj->MySQLSelect($org_sql);
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Organization Cancellation Payment Report</title>
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
                                <h2>Organization Cancellation Payment Report</h2>
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
                                    <select class="form-control filter-by-text" name = 'searchCompany' data-text="Select Company" id="searchCompany">
                                        <option value="">Select Company</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                    </select>
                                </div>
                            </span>
                        </div>

                        <div class="row payment-report payment-report1 payment-report2">
                            <div class="col-lg-3">
                                <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>" id="searchRider">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                </select>
                            </div>
                            <div class="col-lg-3" style="display: none;">
                                <select class="form-control" name='searchPaymentType' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select Payment Type</option>
                                    <option value="Cash" <?php if ($searchPaymentType == "Cash") { ?>selected <?php } ?>>Cash</option>
                                    <option value="Card" <?php if ($searchPaymentType == "Card") { ?>selected <?php } ?>>Card</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name='searchDriverPayment' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</option>
                                    <option value="Yes" <?php if ($searchDriverPayment == "Yes") { ?>selected <?php } ?>>Settelled</option>
                                    <option value="No" <?php if ($searchDriverPayment == "No") { ?>selected <?php } ?>>Unsettelled</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control filter-by-text" name = 'searchOrganization' data-text="Select Organization" id="searchOrganization">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_ORGANIZATION']; ?></option>
                                    <?php foreach ($db_organization as $dbd) { ?>
                                        <option value="<?php echo $dbd['iOrganizationId']; ?>" <?php
                                        if ($searchOrganization == $dbd['iOrganizationId']) {
                                            echo "selected";
                                        }
                                        ?>><?php echo clearName($dbd['driverName']); ?> --  <?php echo 'Email:'. clearEmail($dbd['vEmail']);  ?> </option>
                                            <?php } ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
                            </div>
                        </div>

                        <div class="row payment-report payment-report1 payment-report2" style="margin-top: 21px;">
                            <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') { ?>
                                <div class="col-lg-2">
                                    <select class="form-control" name = 'eType' >
                                        <option value="">Service Type</option>
                                        <option value="Ride" <?php
                                        if ($eType == "Ride") {
                                            echo "selected";
                                        }
                                        ?>><?php echo $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                      
                                        <?php if (ENABLE_RENTAL_OPTION == 'Yes') { ?>
                                            <option value="RentalRide" <?php
                                            if ($eType == "RentalRide") {
                                                echo "selected";
                                            }

                                            ?>>Taxi Rental</option>
                                                <?php } ?>
                                        <option value="Pool" <?php
                                        if ($eType == "Pool") {
                                            echo "selected";
                                        }
                                        ?>><?php echo "Taxi " . $langage_lbl_admin['LBL_POOL']; ?> </option>
                                        <?php if ($MODULES_OBJ->isAirFlightModuleAvailable(1)) { ?>
                                        <option value="Fly" <?php
                                        if ($eType == "Fly") {
                                            echo "selected";
                                        }
                                        ?>><?php echo $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE']; ?> </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                            <div class="col-lg-2">
                                <select class="form-control" name='searchPaymentByUser' data-text="Paid By <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Payment Status</option>
                                    <option value="Yes" <?php if ($searchPaymentByUser == "Yes") { ?>selected <?php } ?>>Paid By <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> - Yes</option>
                                    <option value="No" <?php if ($searchPaymentByUser == "No") { ?>selected <?php } ?>>Paid By <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> -No</option>
                                </select>
                            </div>
                        </div>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'org_cancellation_payment_report.php'"/>
                                <?php if (scount($db_trip) > 0) { ?>
                                    <button type="button" onClick="reportExportTypes('cancellation_org_driver_payment')" class="export-btn001" >Export</button></b>
                            <?php } ?>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                                        <input type="hidden" name="iTripId" id="iTripId" value="">
                                        <input type="hidden" name="ePayDriver" id="ePayDriver" value="">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                                        <th><a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                            if ($sortby == '6') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']; ?> <?php
                                                                   if ($sortby == 6) {
                                                                       if ($order == 0) {
                                                                           ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <?php } ?>
                                                    <th>Cancelled <?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>
                                                    <th>Organization</th>
                                                    <th><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php
                                                               if ($sortby == 1) {
                                                                   if ($order == 0) {
                                                                       ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php
                                                               if ($sortby == 2) {
                                                                   if ($order == 0) {
                                                                       ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                    </th>
                                                    <th><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php
                                                               if ($sortby == 3) {
                                                                   if ($order == 0) {
                                                                       ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="8%">Total Cancellation Fees</th>
                                                    <th width="10%">Organization Payment Status</th>
                                                    <th width="10%">Provider Payment Status</th>
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
                                                        if ($db_trip[$i]['ePaidToDriver'] == 'Yes' && $db_trip[$i]['ePaidByPassenger'] == "Yes") {
                                                            $class_setteled = "setteled-class";
                                                        }
                                                        $poolTxt = "";

                                                        if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                            $poolTxt = " (Pool)";
                                                        }
                                                        $set_unsetarray[] = $db_trip[$i]['ePaidToDriver'];
                                                        $eTypenew = $db_trip[$i]['eType'];
                                                        if ($eTypenew == 'Ride') {
                                                            $trip_type = 'Ride';
                                                        } else if ($eTypenew == 'UberX') {
                                                            $trip_type = 'Other Services';
                                                        } else {
                                                            $trip_type = 'Delivery';
                                                        }
                                                        $trip_type .= $poolTxt;
                                                        //Added By HJ On 22-07-2019 For Solved Bug - 5946 Start
                                                        $db_bookingno = "";
                                                        if (isset($tripNumArr[$db_trip[$i]['vTripAdjusmentId']])) {
                                                            $db_bookingno = $tripNumArr[$db_trip[$i]['vTripAdjusmentId']];
                                                        }
                                                        //Added By HJ On 22-07-2019 For Solved Bug - 5946 End
                                     
                                                        $date_format_data_array = array(
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $date_format_data_array['tdate'] = (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['tTripRequestDate'],$db_trip[$i]['vTimeZone'],$serverTimeZone) : $db_trip[$i]['tTripRequestDate'];
                                                        $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                        ?>
                                                        <tr class="gradeA <?= $class_setteled ?>">
                                                            <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?> 
                                                                <td align="left">
                                                                    <?php
                                                                    if (isset($db_trip[$i]['eHailTrip']) && $db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                                        echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                                    } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                                        echo "Rental " . $trip_type;
                                                                    } else if (isset($db_trip[$i]['eHailTrip']) && $db_trip[$i]['eHailTrip'] == "Yes") {
                                                                        echo "Hail " . $trip_type;
                                                                    } else {
                                                                        echo $trip_type;
                                                                    }
                                                                    ?>
                                                                </td>
                                                            <?php } ?>

                                                            <td> 
                                                                <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                    <a href="invoice.php?iTripId=<?= $db_trip[$i]['iTripId'] ?>" target="_blank">
                                                                    <?php } ?>
                                                                    <?= $db_trip[$i]['vRideNo']; ?>

                                                                    <?php if ($userObj->hasPermission('view-invoice')) { ?>   
                                                                    </a>
                                                                <?php } ?>
                                                            </td>
                                                            <td><?= clearName($db_trip[$i]['Organization']); ?></td>

                                                            <td><?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['drivername']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?> </td>

															<td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['riderName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td> 
														
                                                            <td><?= $get_tTripRequestDate_format['tDisplayDate']."<br/>".$get_tTripRequestDate_format['tDisplayTime'].$time_zone_difference_text; ?></td>
                                                            <td><?php
                                                                $TotalCancelledprice = $db_trip[$i]['fPendingAmount'] > $db_trip[$i]['fWalletDebit'] ? $db_trip[$i]['fPendingAmount'] : $db_trip[$i]['fWalletDebit'];
                                                                echo formateNumAsPerCurrency($TotalCancelledprice,'');
                                                                ?></td>
                                                            <td><?php if (!empty($db_bookingno)) { ?> Paid in Trip #<?php if ($userObj->hasPermission('view-invoice')) { ?><a href="invoice.php?iTripId=<?= $db_trip[$i]['vTripAdjusmentId'] ?>" target="_blank"><?php } ?><?= $db_bookingno; ?>
                                                                        <?php if ($userObj->hasPermission('view-invoice')) { ?>   
                                                                        </a>
                                                                    <?php } ?>
                                                                    <?php
                                                                } else if ($db_trip[$i]['ePaidByPassenger'] == 'No') {
                                                                    echo"<b>Not Paid</b>";
                                                                } else {
                                                                    echo"Paid By Card";
                                                                }
                                                                ?></td>

                                                            <td> 
                                                                <?php
                                                                if ($db_trip[$i]['ePaidToDriver'] == 'No') {
                                                                    echo "Unsettelled";
                                                                } else {
                                                                    echo "settelled";
                                                                }
                                                                ?>
                                                            </td>

                                                            <td>
                                                                <?php
                                                                if ($db_trip[$i]['ePaidToDriver'] == 'No') {
                                                                    ?>
                                                                    <input class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iTripId'] ?>" id="iTripId_<?= $db_trip[$i]['iTripId'] ?>" name="iTripId[]">
                                                                    <?php
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    <tr class="gradeA">
                                                        <td colspan="9" align="right">Total Platform Fees</td>
                                                        <td colspan="2"  align="right" colspan="2"><?= formateNumAsPerCurrency($tot_site_commission,''); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="9" align="right">Total <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment</td>
                                                        <td colspan="2" align="right" colspan="2"><?= formateNumAsPerCurrency($tot_driver_refund,''); ?></td>
                                                    </tr>
                                                    <?php if (scount($db_trip) > 0) { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="11" align="right">
                                                                <a onClick="PaytodriverforCancel()" href="javascript:void(0);" class="btn btn-primary">Mark As Settelled</a>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="11" style="text-align:center;">No Payment Details Found.</td>
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
        <form name="pageForm" id="pageForm" action="action/cancellation_payment_report.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="action" value="<?php echo $action; ?>" >
            <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
            <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>" >
            <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>" >
            <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>" >
            <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>" >
            <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>" >
            <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
            <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >
            <input type="hidden" name="eType" value="<?php echo $eType; ?>" >
            <input type="hidden" name="organization" value="1" >
            <input type="hidden" name="searchPaymentByUser" value="<?php echo $searchPaymentByUser; ?>" >
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
	
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <!-- <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script> -->
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <?php include_once('searchfunctions.php'); ?>
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
    /*$('#searchCompany').change(function () {
        var company_id = $(this).val(); //get the current value's option
        $.ajax({
            type: 'POST',
            url: 'ajax_find_driver_by_company.php',
            data: {'company_id': company_id},
            cache: false,
            success: function (data) {
                $(".driver_container").html(data);
            }
        });
    });*/
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