<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-published-rides-rideshare')) {
    $userObj->redirect();
}
$script = 'PublishedRides';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY pr.iPublishedRideId  DESC';
//End Sorting
//For Currency
$sql = "select vSymbol from  currency where eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : "";
$searchRideNo = isset($_REQUEST['searchRideNo']) ? $_REQUEST['searchRideNo'] : "";
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$Document = isset($_REQUEST['eDocument']) ? $_REQUEST['eDocument'] : '';
$ssql = '';
//$ssql_date = " AND pr.dStartDate > '" . date('Y-m-d H:i:s') . "' ";
$ssql_date = "";
if ($searchRider != '') {
    $ssql .= " AND pr.iUserId = {$searchRider} ";
}
if ($eStatus != '') {
    if ($eStatus == "PastRides") {
        $ssql_date = " AND pr.dStartDate < '" . date('Y-m-d H:i:s') . "' ";
    } else if ($eStatus == "Active") {
        $ssql_date = " AND pr.dStartDate >= '" . date('Y-m-d H:i:s') . "' AND pr.eStatus = 'Active'";
    } else {
        $ssql .= " AND pr.eStatus = '{$eStatus}' ";
    }
}
if ($Document != '') {
    $ssql .= "AND riderDriver.eApproveDoc = '" . $Document . "' ";
}
if ($searchRideNo != '') {
    $ssql .= " AND pr.vPublishedRideNo = {$searchRideNo} ";
}
/*if ($startDate != '') {

    $ssql .= " AND Date(pr.dStartDate) >='" . $startDate . "'";
}
if ($endDate != '') {

    $ssql .= " AND Date(pr.dStartDate) <='" . $endDate . "'";
}*/
if ($startDate != '') {
    $ssql .= " AND Date(pr.dAddedDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(pr.dAddedDate) <='" . $endDate . "'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if($Document != '') {
    $sql = "SELECT COUNT(iPublishedRideId) AS Total 
    FROM published_rides pr 
    JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId) 
    WHERE 1 =1 $ssql $ssql_date ";
} else {
    $sql = "SELECT COUNT(iPublishedRideId) AS Total FROM published_rides pr WHERE 1 =1 $ssql $ssql_date ";
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
if ($page <= 0) {
    $page = 1;
}
//Pagination End
$TimeZoneOffset = date('P');
$EXPIRED = 2;
$EXPIRED_M = $EXPIRED * 60;
$isExpired = '';
$isExpired .= "CASE WHEN pr.eTrackingStatus ='Pending' THEN pr.dStartDate < ( (CONVERT_TZ(NOW(), 'SYSTEM', '" . $TimeZoneOffset . "')) - INTERVAL $EXPIRED_M MINUTE )  ELSE  '0' END  as isExpired,";
$sql = "SELECT pr.*, $isExpired  pr.iUserId AS driver_Id , CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name , riderDriver.eApproveDoc,riderDriver.vTimeZone FROM published_rides pr 
         JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)
         WHERE 1=1 $ssql $ssql_date $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);
/*------------------WayPoints-----------------*/
$iPublishedRideIdARR = array_column($data_drv, 'iPublishedRideId');
$iPublishedRideIdARR = implode(',', $iPublishedRideIdARR);
$waypoints_data = array();
$RIDE_WAYPOINTS = [];
$RIDE_WAYPOINTS_SUM = [];
if(!empty($iPublishedRideIdARR)) {
    $sql = "SELECT * FROM `published_rides_waypoints` WHERE iPublishedRideId IN ($iPublishedRideIdARR) ";
    $waypoints_data = $obj->MySQLSelect($sql);

    if(count($waypoints_data) > 0){
        foreach ($waypoints_data as $w) {
            $RIDE_WAYPOINTS[$w['iPublishedRideId']][] = $w;

            // Initialize the key if it doesn't exist
            if (!isset($RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']])) {
                $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] = 0;
            }


            $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] = $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] + $w['iBookedSeats'];
        }
    }
}

$RIDE_WAYPOINTS_ARR = [];
if (isset($RIDE_WAYPOINTS) && !empty($RIDE_WAYPOINTS)) {
    foreach ($RIDE_WAYPOINTS as $key => $waypoint) {
        $wayPoints = $RIDE_SHARE_OBJ->wayPointDBToArray($waypoint);
        if (isset($waypoint['iBookedSeats'])) {
            $wayPoints['iBookedSeats'] = $waypoint['iBookedSeats'];
        } else {
            $wayPoints['iBookedSeats'] = 0; // Set a default value if the key doesn't exist
        }
        $RIDE_WAYPOINTS_ARR[$key] = $wayPoints['waypoint_data'];
    }
}
/*------------------WayPoints-----------------*/
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$langLabels = $langage_lbl_admin;
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

$showDocument = false;
if($RIDE_SHARE_OBJ->IsAnyDocumentActive()){
    $showDocument = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Published Rides</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <style type="text/css">
        .form-group .row {
            padding: 0;
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
                        <h2>Published Rides</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page">
                    <input type="hidden" name="action" value="search"/>
                    <h3>Search Publish Ride ...</h3>
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
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3">
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control" name="eStatus">
                                <option value="">All Status</option>
                                <option value="Active" <?php
                                if ($eStatus == "Active") {
                                    echo "selected";
                                }
                                ?>>Active
                                </option>
                                <option value="Cancelled" <?php
                                if ($eStatus == "Cancelled") {
                                    echo "selected";
                                }
                                ?>>Cancelled
                                </option>
                                <option value="PastRides" <?php
                                if ($eStatus == "PastRides") {
                                    echo "selected";
                                }
                                ?>>Past Rides
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <input type="text" id="searchRideNo" name="searchRideNo" placeholder="Ride Number"
                                   class="form-control search-trip001" value="<?= $searchRideNo ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <select class="form-control filter-by-text" name='searchRider'
                                data-text="Published By(Select User)" id="searchRider">
                            <option value="">Published By (Select User)</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <select class="form-control" name="eDocument">
                            <option value="">Document Approval Status</option>
                            <option value="No" <?php
                            if ($Document == "No") {
                                echo "selected";
                            }
                            ?>>Not Approved
                            </option>
                            <option value="Yes" <?php
                            if ($Document == "Yes") {
                                echo "selected";
                            }
                            ?>>Approved
                            </option>
                        </select>
                    </div>

                </div>

                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'published_rides.php'"/>
                        <?php if (!empty($data_drv)) { ?>
                            <button type="button" onClick="reportExportTypes('PublishedRides')" class="export-btn001"
                                    style="float:none;">Export
                            </button>
                        <?php } ?>
                    </b>
                </div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>Ride No.</th>
                                        <th width="">Published By</th>
                                        <th>Published Date</th>

                                        <th style="width: 15%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Ride Start & End Time <?php
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
                                        <th width="">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Duration <?php
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
                                        <th width="30%">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '9') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Start & End Location <?php
                                                if ($sortby == 9) {
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

                                        <th style="text-align: right">Price Per Seat <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="This price is related to the distance from the starting location to the destination. Please check the ride details page to see the price for any stopover point."></i></th>
                                        <th style="text-align: center">view Document(s)<i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title="Documents will only display here if there is an active document for ride share."></i></th>

                                        <th style="text-align: center">Total seats</th>
                                        <!-- <th style="text-align: center" >Occupied Seats</th>-->
                                        <th style="text-align: center">Total Booking</th>

                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        $systemTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            //$time = $RIDE_SHARE_OBJ->convertSecToMin(floor($data_drv[$i]['fDuration']));
                                            $time = $RIDE_SHARE_OBJ->convertSecToMin(floor((float)$data_drv[$i]['fDuration']));

                                            $date_format_data_array = array(
                                                //'tdate' => (!empty($value['vTimeZone'])) ? converToTz($value['dRentItemPostDate'],$value['vTimeZone'], $systemTimeZone) : $value['dRentItemPostDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dAddedDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['dAddedDate'];
                                            $get_dAddedDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";

                                            $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dStartDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['dStartDate'];
                                            $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                            $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dEndDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['dEndDate'];
                                            $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                            
                                            ?>
                                            <tr class="gradeA">
                                                <td><?= $data_drv[$i]['vPublishedRideNo'] ?></td>
                                                <td>
                                                    <?php if ($userObj->hasPermission('view-users')) { ?>
                                                    <a href="javascript:void(0);"
                                                       onClick="show_rider_details('<?= $data_drv[$i]['driver_Id']; ?>')"
                                                       style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['driver_Name']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
                                                </td>
                                                <td width="8%"><?= $get_dAddedDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($data_drv[$i]['dAddedDate'], '7'); //date('M d, Y h:i A', strtotime($data_drv[$i]['dAddedDate'])); ?></td>
                                                <td>
                                                    <div class="lableCombineData">
                                                        <label>Start Time</label>
                                                        <br>
                                                        <span><?=  $get_dStartDate_format['tDisplayDateTime'];//DateTime($data_drv[$i]['dStartDate'], '7'); //date('M d, Y  h:i A', strtotime($data_drv[$i]['dStartDate'])); ?> </span>
                                                        <br>
                                                        <br>
                                                        <label>End Time</label>
                                                        <br>
                                                        <span> <?= $get_dEndDate_format['tDisplayDateTime'];//DateTime($data_drv[$i]['dEndDate'], '7'); //date('M d, Y  h:i A', strtotime($data_drv[$i]['dEndDate'])); ?></span>
                                                    </div>
                                                </td>
                                                <td><?= $time; ?></td>
                                                <td>
                                                    <div class="lableCombineData">
                                                        <label>Start Location</label>
                                                        <br>
                                                        <span><?= $data_drv[$i]['tStartLocation']; ?> </span>
                                                        <?php
                                                        if (isset($RIDE_WAYPOINTS_ARR[$data_drv[$i]['iPublishedRideId']]) && !empty($RIDE_WAYPOINTS_ARR[$data_drv[$i]['iPublishedRideId']])) {
                                                            $RIDE_WAYPOINTS = @$RIDE_WAYPOINTS_ARR[$data_drv[$i]['iPublishedRideId']];
                                                            $j = 1;
                                                            foreach ($RIDE_WAYPOINTS as $key => $w) {
                                                                echo '<br> <label>STOP ' . $j . '</label> <br> ' . $w['address'];
                                                                '<br>';
                                                                $j++;
                                                            }
                                                        }
                                                        ?>
                                                        <br>

                                                        <label>End Location</label>
                                                        <br>
                                                        <span>  <?= $data_drv[$i]['tEndLocation']; ?></span>
                                                    </div>
                                                </td>

                                                <td style="text-align: right"><?= formateNumAsPerCurrency($data_drv[$i]['fPrice'], ''); ?></td>

                                                <td style="text-align: center">

                                                    <?php if($showDocument){ ?>
                                                    <a target="_blank"
                                                       href="user_document_action.php?iUserId=<?php echo $data_drv[$i]['iUserId'] ?>">
                                                        <img src="img/edit-doc.png" alt="Edit Document">
                                                    </a>

                                                    <?php if ($data_drv[$i]['eApproveDoc'] == "Yes") { ?>
                                                        <br>
                                                        <img src="img/active-icon-c.png" alt="Edit Document">
                                                    <?php } else {
                                                        // echo "<br><p>Pending for the approval.</p>";
                                                    } ?>

                                                    <?php }else { ?>

                                                        -
                                                   <?php } ?>
                                                </td>

                                                <td style="text-align: center"><?= $data_drv[$i]['iAvailableSeats']; ?></td>
                                                <td style="text-align: center">

                                                    <?php
                                                    $totalBookSeats = $waypointBookSeats = 0;
                                                    if (isset($RIDE_WAYPOINTS_SUM[$data_drv[$i]['iPublishedRideId']]) && !empty($RIDE_WAYPOINTS_SUM[$data_drv[$i]['iPublishedRideId']])) {
                                                        $waypointBookSeats = $RIDE_WAYPOINTS_SUM[$data_drv[$i]['iPublishedRideId']];
                                                    }
                                                    $totalBookSeats = $waypointBookSeats + $data_drv[$i]['iBookedSeats'];
                                                    ?>

                                                    <?php if ($totalBookSeats > 0 && $data_drv[$i]['eStatus'] != "Cancelled") { ?>
                                                        <a target="_blank"
                                                           href="ride_share_bookings.php?iPublishedRideId=<?= $data_drv[$i]['iPublishedRideId']; ?>"><?= $totalBookSeats; ?></a>
                                                    <?php } else {
                                                        echo "-";
                                                    } ?>

                                                </td>
                                                <td>
                                                    <?php
                                                    $link_page = "prdetails.php"; ?>
                                                    <button class="btn btn-primary"
                                                            onclick='return !window.open("<?= $link_page ?>?iPublishedRideId=<?= $data_drv[$i]['iPublishedRideId'] ?>", "_blank")'>
                                                        <i class="icon-th-list icon-white"><b>View Ride Details </b></i>
                                                    </button>
                                                    <br>
                                                    <br>
                                                    <?php
                                                    /*                                                        if ($data_drv[$i]['iBookedSeats'] > 0 && $data_drv[$i]['eTrackingStatus'] == "End") {
                                                        if($data_drv[$i]['eTrackingStatus'] == "End"){
                                                            echo "Finished";
                                                            }else{
                                                            echo "On Going";
                                                        }

                                                    } */ ?>

                                                    <?php

                                                    if ($data_drv[$i]['eStatus'] == "Cancelled") { ?>
                                                        <?= $data_drv[$i]['eStatus']; ?>

                                                    <?php } else if ($data_drv[$i]['isExpired'] == "1") {
                                                        echo "Expired";
                                                    } else { ?>
                                                        <?php $data_eStatus = $RIDE_SHARE_OBJ->getDisplayStatusForAdmin($data_drv[$i]['eTrackingStatus'], $data_drv[$i]['eStatus'])['status'];
                                                        ?>
                                                        <?= $data_eStatus; ?>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="11"> No Records Found.</td>
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
                    <li> Published Rides module will list all Published Rides on this page.</li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iGiftCardId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
    <input type="hidden" name="searchRideNo" value="<?= $searchRideNo ?>">
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
<?php
include_once('footer.php');
?>
<?php include_once('searchfunctions.php'); ?>
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script>
    var startDate;
    var endDate;
    $('#dp4').datepicker()
        .on('changeDate', function (ev) {
            startDate = new Date(ev.date);
            if (endDate != null) {
                if (ev.date.valueOf() < endDate.valueOf()) {
                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    $('#startDate').text($('#dp4').data('date'));
                }
            }
            $('#dp4').datepicker('hide');
        });
    $('#dp5').datepicker()
        .on('changeDate', function (ev) {
            endDate = new Date(ev.date);
            if (startDate != null) {
                if (ev.date.valueOf() < startDate.valueOf()) {
                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    $('#endDate').text($('#dp5').data('date'));
                }
            }
            $('#dp5').datepicker('hide');
        });
    $(document).ready(function () {
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

    function show_doc(userid) {
        $("#rider_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');
        $("#rider_text").text('Document');
        if (userid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_doc.php',
                'AJAX_DATA': "iUserId=" + userid + "&approveBtn=No",
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    console.log(response);
                    $("#rider_detail").html(response.result);
                    $("#imageIcons").hide();
                }
            });
        }
    }
</script>
</body>
<!-- END BODY-->
</html>
