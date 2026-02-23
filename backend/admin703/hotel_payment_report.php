<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-hotel-payment-report')) {
    $userObj->redirect();
}
$script = 'hotelPayment_Report';
function cleanNumber($num)
{
    return str_replace(',', '', $num);
}

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
$searchHotel = isset($_REQUEST['searchHotel']) ? $_REQUEST['searchHotel'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$searchKiosk = isset($_REQUEST['searchKiosk']) ? $_REQUEST['searchKiosk'] : '';
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
    if ($searchCompany != '') {
        $ssql .= " AND rd.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND tr.iUserId ='" . $searchRider . "'";
    }
    if ($searchHotel != '') {
        $ssql .= " AND a.iAdminId ='" . $searchHotel . "'";
    }
    if ($searchDriverPayment != '') {
        $ssql .= " AND tr.eHotelPaymentStatus ='" . $searchDriverPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND tr.vTripPaymentMode ='" . $searchPaymentType . "'";
    }
    if ($searchKiosk != '') {
        $ssql .= " AND tr.iHotelId ='" . $searchKiosk . "'";
    }
    if ($eType != '') {
        if ($eType == 'Ride') {
            $ssql .= " AND tr.eType ='" . $eType . "' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' ";
        } elseif ($eType == 'RentalRide') {
            $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
        } elseif ($eType == 'HailRide') {
            $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
        } else {
            $ssql .= " AND tr.eType ='" . $eType . "' ";
        }
    }
}
$locations_where = "";
if (scount($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE tr.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";
}
$hotelQuery = "";
if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == 'hotel') {
    $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
    $hotelQuery = " And tr.iHotelBookingId = '" . $iHotelBookingId . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT tr.iUserId,tr.iDriverId,tr.iFare,tr.fTripGenerateFare,tr.fCommision,tr.iBaseFare,a.vFirstName,a.vLastName,a.fHotelServiceCharge,tr.fHotelBookingChargePercentage, tr.fDiscount, tr.fWalletDebit,tr.fHotelCommision, tr.fTipPrice,tr.fOutStandingAmount,tr.vTripPaymentMode,( SELECT COUNT(tr.iTripId) FROM trips AS tr LEFT JOIN hotel as h on h.iHotelId=tr.iHotelId LEFT JOIN administrators as a on a.iAdminId=tr.iHotelBookingId WHERE if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND (tr.eBookingFrom = 'Hotel' || tr.eBookingFrom = 'Kiosk') $ssql $trp_ssql $hotelQuery) AS Total FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId LEFT JOIN hotel as h on h.iHotelId=tr.iHotelId LEFT JOIN administrators as a on a.iAdminId=tr.iHotelBookingId WHERE if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND (tr.eBookingFrom = 'Hotel' || tr.eBookingFrom = 'Kiosk')AND tr.eSystem = 'General' $hotelQuery $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);

$driver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_driver_refund = 0.00;
$driverIdArr = $userIdArr = array();
foreach ($totalData as $dtps) {
    $driverIdArr[] = $dtps['iDriverId'];
    $userIdArr[] = $dtps['iUserId'];
    $totalfare = $dtps['fTripGenerateFare'];
    $site_commission = $dtps['fHotelCommision'];
    $driver_payment = cleanNumber($site_commission);
    $tot_fare = $tot_fare + cleanNumber($totalfare);
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $tot_driver_refund = $tot_driver_refund + cleanNumber($driver_payment);
    $cashPayment = $site_commission;
    $cardPayment = $totalfare - $site_commission;
}
if (scount($userIdArr) > 0) $userIdArrHotel = implode(",", $userIdArr);
if (scount($driverIdArr) > 0) $driverIdArrHotel = implode(",", $driverIdArr);
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
$sql = "SELECT tr.iTripId,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.iBaseFare,tr.tTripRequestDate,concat(a.vFirstName,' ',a.vLastName) as hotelname,a.fHotelServiceCharge,tr.fHotelBookingChargePercentage,tr.fHotelCommision, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eHotelPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId, tr.eBookingFrom,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId LEFT JOIN hotel as h on h.iHotelId=tr.iHotelId LEFT JOIN administrators as a on a.iAdminId=tr.iHotelBookingId  WHERE  if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND (tr.eBookingFrom = 'Hotel' || tr.eBookingFrom = 'Kiosk') AND tr.eSystem = 'General' $hotelQuery $ssql $trp_ssql $ord LIMIT $start, $per_page";
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
    <title><?= $SITE_NAME ?> | Hotel Payment Report</title>
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
                        <h2>Hotel Payment Report</h2>
                        <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
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
                            <select class="form-control filter-by-text driver_container" name='searchDriver'
                                    data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>"
                                    id="searchDriver">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                            </select>
                        </div>
                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text" name='searchRider'
                                    data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>"
                                    id="searchRider">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                            </select>
                        </div>
                        <input type="hidden" name="searchDriverHotel" id="searchDriverHotel"
                               value="<?= $driverIdArrHotel ?>">
                        <input type="hidden" name="searchRiderHotel" id="searchRiderHotel"
                               value="<?= $userIdArrHotel ?>">
                    
                    </span>
                </div>
                <div class="row payment-report payment-report1 payment-report2">
                    
                    <div class="col-lg-3">
                        <select class="form-control" name='searchDriverPayment'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment
                                Status
                            </option>
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

                    <div class="col-lg-3">
                        <select class="form-control filter-by-text" name='searchKiosk'
                                data-text="Select <?= $langage_lbl_admin['LBL_KIOSK']; ?>"
                                id="searchKiosk">
                            <option value="">Select <?= $langage_lbl_admin['LBL_KIOSK']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'hotel_payment_report.php'"/>
                        <?php if (scount($db_trip) > 0 && $userObj->hasPermission('export-hotel-payment-report')) { ?>
                        <button type="button" onClick="exportlist()" class="export-btn001">Export</button>
                    </b>
                    <?php } ?>
                    </b>
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
                                <input type="hidden" name="prev_start" id="prev_start" value="<?= $startDate ?>">
                                <input type="hidden" name="prev_end" id="prev_end" value="<?= $endDate ?>">
                                <input type="hidden" name="prev_order" id="prev_order" value="<?= $order ?>">
                                <input type="hidden" name="prev_sortby" id="prev_sortby" value="<?= $sortby ?>">
                                <input type="hidden" name="prevsearchDriver" id="prevsearchDriver" value="<?= $searchDriver ?>">
                                <input type="hidden" name="prevsearchCompany" id="prevsearchCompany" value="<?= $searchCompany ?>">
                                <input type="hidden" name="prevsearchRider" id="prevsearchRider" value="<?= $searchRider ?>">
                                <input type="hidden" name="prevsearchHotel" id="prevsearchHotel" value="<?= $searchHotel ?>">
                                <input type="hidden" name="serachTripNo" id="serachTripNo" value="<?= $serachTripNo ?>">
                                <input type="hidden" name="searchKiosk" value="<?= $searchKiosk; ?>">
                                <input type="hidden" name="searchDriverPayment" id="searchDriverPayment"
                                       value="<?= $searchDriverPayment ?>">
                                <table class="table table-bordered" id="dataTables-example123">
                                    <thead>
                                    <tr>
                                        <th><?php echo 'Booked By'; ?></th>
                                        <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?></th>
                                        <?php //if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                            <th>Hotel Name</th>
                                        <?php //} ?>
                                        <th width="10%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php
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
                                        <th style="text-align:right;">A=Total Fare</th>
                                        <th style="text-align:right;">B=Base Fare</th>
                                        <th>C=Hotel Booking Charge (in %)</th>
                                        <th style="text-align:right;">D=Booking Fees (B*C/100)</th>
                                        <th style="text-align:right;">E=Hotel pay Amount</th>
                                        <th><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Status</th>
                                        <th>Payment method</th>
                                        <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</th>
                                        <?php
                                        if (isset($_SESSION['SessionUserType']) &&  $_SESSION['SessionUserType'] != 'hotel') { ?>
                                            <th></th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?
                                    $set_unsetarray = array();
                                    if (scount($db_trip) > 0) {
                                        for ($i = 0; $i < scount($db_trip); $i++) {
                                            $class_setteled = "";
                                            if ($db_trip[$i]['eHotelPaymentStatus'] == 'Settelled') {
                                                $class_setteled = "setteled-class";
                                            }
                                            $totalfare = $db_trip[$i]['fTripGenerateFare'];
                                            $site_commission = $db_trip[$i]['fHotelCommision'];
                                            $driver_payment = cleanNumber($site_commission);
                                            $set_unsetarray[] = $db_trip[$i]['eHotelPaymentStatus'];
                                            $eTypenew = $db_trip[$i]['eType'];
                                            if ($eTypenew == 'Ride') {
                                                $trip_type = 'Ride';
                                            } else if ($eTypenew == 'UberX') {
                                                $trip_type = 'Other Services';
                                            } else {
                                                $trip_type = 'Delivery';
                                            }
                                            $link_page = "invoice.php?iTripId=" . $db_trip[$i]['iTripId'];
                                            ?>
                                            <tr class="gradeA <?= $class_setteled ?>">
                                                <td><?= $db_trip[$i]['eBookingFrom']; ?></td>
                                                <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                    <td>
                                                        <!-- <a href="javascript:void(0);" onclick='javascript:window.open("<?= $link_page ?>", "_blank")'; ><?= $db_trip[$i]['vRideNo']; ?></a> -->
                                                        <a href="<?= $link_page ?>"
                                                           target="_blank"><?= $db_trip[$i]['vRideNo']; ?></a>
                                                    </td>
                                                <?php } else { ?>
                                                    <td><?= $db_trip[$i]['vRideNo']; ?></td>
                                                <?php } ?>
                                                <?php //if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                                    <td><?= clearName($db_trip[$i]['hotelname']); ?></td>
                                                <?php //} ?>
                                                <td>

                                                    <a href="javascript:void(0);"
                                                       onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')"
                                                       style="text-decoration: underline;"><?= clearName($db_trip[$i]['drivername']); ?></a>

                                                </td>
                                                <td>

                                                    <a href="javascript:void(0);"
                                                       onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')"
                                                       style="text-decoration: underline;"><?= clearName($db_trip[$i]['riderName']); ?></a>

                                                </td>
                                                <?
                                                $systemTimeZone = date_default_timezone_get();
                                                $db_trip[$i]['tTripRequestDate'] = (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['tTripRequestDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone) : $db_trip[$i]['tTripRequestDate'];

                                                $date_format_data_array = array(
                                                    'tdate' => $db_trip[$i]['tTripRequestDate'],
                                                    'langCode' => $default_lang,
                                                    'DateFormatForWeb' => 1
                                                );
                                                $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                ?>
                                                <td><?= $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($db_trip[$i]['tTripRequestDate']); ?></td>
                                                <td align="right">
                                                    <?php
                                                    if ($db_trip[$i]['fTripGenerateFare'] != "" && $db_trip[$i]['fTripGenerateFare'] != 0) {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['fTripGenerateFare'], '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="right">
                                                    <?php
                                                    if ($db_trip[$i]['iBaseFare'] != "" && $db_trip[$i]['iBaseFare'] != 0) {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['iBaseFare'], '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>
                                                </td>
                                                <td align="right"><?php
                                                    if ($db_trip[$i]['fHotelBookingChargePercentage'] != '' && $db_trip[$i]['fHotelBookingChargePercentage'] != 0) {
                                                        echo $db_trip[$i]['fHotelBookingChargePercentage'] . "%";
                                                    } else {
                                                        //echo $db_trip[$i]['fHotelServiceCharge']."%";
                                                        echo "0";
                                                    }
                                                    ?></td>
                                                <td align="right"><?php
                                                    if ($db_trip[$i]['fHotelCommision'] != "" && $db_trip[$i]['fHotelCommision'] != 0) {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['fHotelCommision'], '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?></td>
                                                <td align="right">
                                                    <?php
                                                    if ($driver_payment != "" && $driver_payment != 0) {
                                                        echo formateNumAsPerCurrency($driver_payment, '');
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= $db_trip[$i]['iActive']; ?></td>
                                                <td><?= $db_trip[$i]['vTripPaymentMode']; ?></td>
                                                <td><?
                                                    if ($db_trip[$i]['eHotelPaymentStatus'] == "Settelled") {
                                                        echo "Settled";
                                                    } else if ($db_trip[$i]['eHotelPaymentStatus'] == "Unsettelled") {
                                                        echo "Unsettled";
                                                    } else {
                                                        echo $db_trip[$i]['eHotelPaymentStatus'];
                                                    }
                                                    ?>
                                                </td>
                                                <?php if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                                    <td>
                                                        <?
                                                        if ($db_trip[$i]['eHotelPaymentStatus'] == 'Unsettelled') {
                                                            ?>
                                                            <input class="validate[required]" type="checkbox"
                                                                   value="<?= $db_trip[$i]['iTripId'] ?>"
                                                                   id="iTripId_<?= $db_trip[$i]['iTripId'] ?>"
                                                                   name="iTripId[]">
                                                            <?
                                                        }
                                                        ?>
                                                    </td>
                                                <? } ?>
                                            </tr>
                                        <? } ?>
                                        <?
                                        if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') {
                                            $colspan = "3";
                                            $colspan1 = "12";
                                        } else {
                                            $colspan = "2";
                                            $colspan1 = "11";
                                        }
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="<?= $colspan1 ?>" align="right">Total Fare</td>
                                            <td align="right" colspan="<?= $colspan ?>"><?= formateNumAsPerCurrency($tot_fare, ''); ?></td>
                                        </tr>
                                        <tr class="gradeA">
                                            <td colspan="<?= $colspan1 ?>" align="right">Total Booking Fees</td>
                                            <td align="right" colspan="<?= $colspan ?>"><?= formateNumAsPerCurrency($tot_site_commission, ''); ?></td>
                                        </tr>
                                        <tr class="gradeA">
                                            <td colspan="<?= $colspan1 ?>" align="right">Total Hotel Payment</td>
                                            <td align="right"
                                                colspan="<?= $colspan ?>"><?= formateNumAsPerCurrency($tot_driver_refund, ''); ?></td>
                                        </tr>
                                        <? if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                            <?php if (in_array("Unsettelled", $set_unsetarray)) { ?>
                                                <tr class="gradeA">
                                                    <td colspan="15" align="right">
                                                        <a onClick="Paytodriver()" href="javascript:void(0);" class="btn btn-primary">Mark As Settled</a>
                                                    </td>
                                                </tr>
                                                <?
                                            }
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="15" style="text-align:center;">No Payment Details Found.</td>
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
<form name="pageForm" id="pageForm" action="action/hotel_payment_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>">
    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>">
    <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>">
    <input type="hidden" name="searchHotel" value="<?php echo $searchHotel; ?>">
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
<!-- <link rel="stylesheet" href="css/select2/select2.min.css" />
<script src="js/plugins/select2.min.js"></script> -->
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<? include_once('searchfunctions.php'); ?>
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

    /* $(window).ready(function() {
     $("body").addClass("sidebar-minize");
     $("body").addClass("sidebar_hide");
     $("body").addClass("sidebar-collapse");
     });*/

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


    function exportlist() {
        $("#actionpayment").val("export");
        var act = $("#_list_form").attr("action");
        $("#_list_form").attr("action", "export_hotel_pay_details.php");
        ShpSq6fAm7($("#_list_form"));
        document._list_form.submit();
        $("#_list_form").attr("action", act);
        return true;
    }
</script>
</body>
<!-- END BODY-->
</html>