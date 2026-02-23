<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-booking-rideshare')) {
    $userObj->redirect();
}
$script = 'RideShareBookings';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$iPublishedRideId = isset($_REQUEST['iPublishedRideId']) ? $_REQUEST['iPublishedRideId'] : '';
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
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchRideNo = isset($_REQUEST['searchRideNo']) ? $_REQUEST['searchRideNo'] : "";
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$isRideShare = isset($_REQUEST['rideShare']) ? $_REQUEST['rideShare'] : '';
$ssql = '';
if ($searchRider != '') {
    $ssql .= " AND rsb.iUserId = {$searchRider} ";
}
if ($searchDriver != '') {
    $ssql .= " AND pr.iUserId = {$searchDriver} ";
}
if ($eStatus != '') {
    $ssql .= " AND rsb.eStatus = '{$eStatus}' ";
}
if ($searchRideNo != '') {
    $ssql .= " AND pr.vPublishedRideNo = '{$searchRideNo}' ";
}
/*if ($startDate != '') {
    $ssql .= " AND Date(pr.dStartDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(pr.dStartDate) <='" . $endDate . "'";
}*/
if (isset($iPublishedRideId) && !empty($iPublishedRideId)) {
    $ssql .= " AND rsb.iPublishedRideId = " . $iPublishedRideId." AND rsb.eStatus = 'Approved'";
}


if ($startDate != '') {

    $ssql .= " AND Date(rsb.dBookingDate) >='" . $startDate . "'";
}
if ($endDate != '') {

    $ssql .= " AND Date(rsb.dBookingDate) <='" . $endDate . "'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iBookingId) AS Total FROM ride_share_bookings rsb  JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId) WHERE 1 =1 $ssql ";
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
if ($page <= 0) $page = 1;
//Pagination End
$sql = "SELECT  CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name,  CONCAT(riderUser.vName,' ',riderUser.vLastName) AS  rider_Name, 
                riderUser.vImgName as rider_ProfileImg, riderUser.iUserId as rider_iUserId, riderDriver.iUserId as driver_iUserId,
                pr.tStartLocation,pr.tStartLat,pr.tStartLong,pr.eTrackingStatus,riderUser.vTimeZone,
                
                COALESCE(prw.tStartLocation, pr.tStartLocation) AS tStartLocation,
                COALESCE(prw.tStartLat, pr.tStartLat) AS tStartLat,
                COALESCE(prw.tStartLong, pr.tStartLong) AS tStartLong,
                COALESCE(prw.dStartDate, pr.dStartDate) AS dStartDate,
                
                
                COALESCE(prw.tEndLocation, pr.tEndLocation) AS tEndLocation,
                COALESCE(prw.tEndLat, pr.tEndLat) AS tEndLat,
                COALESCE(prw.tEndLong, pr.tEndLong) AS tEndLong,
                COALESCE(prw.dEndDate, pr.dEndDate) AS dEndDate,
                
                pr.tPriceRatio,pr.vPublishedRideNo,
                pr.tEndCity,pr.tStartCity,rsb.dBookingDate,pr.fDuration,
                rsb.iPublishedRideId,rsb.eStatus,rsb.fTotal,rsb.iBookedSeats,pr.tDriverDetails,rsb.iBookingId,rsb.iCancelReasonId,rsb.tCancelReason,rsb.vBookingNo
                FROM ride_share_bookings rsb 
                JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId)
                 LEFT JOIN published_rides_waypoints prw ON (prw.iPublishedRideWayPointId = rsb.iPublishedRideWayPointId)
                JOIN register_user riderUser  ON (riderUser.iUserId = rsb.iUserId)
                JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)  WHERE 1=1 $ssql $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
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
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Ride Share Bookings</title>
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
                        <h2>Bookings</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>

            <?php if (empty($iPublishedRideId)) { ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page">
                    <input type="hidden" name="action" value="search"/>
                    <input type="hidden" name="rideShare" id="rideShare" value="1"/>
                    <h3>Search Booking ...</h3>
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
                <div class="Posted-date form-group">
                    <div class="row">
                        <div class="col-lg-3">
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control" name="eStatus">
                                <option value="">All Status</option>
                                <option value="Approved" <?php
                                if ($eStatus == "Approved") {
                                    echo "selected";
                                }
                                ?>>Approved
                                </option>
                                 <option value="Declined" <?php
                                if ($eStatus == "Declined") {
                                    echo "selected";
                                }
                                ?>>Declined
                                </option>
                                <option value="Pending" <?php
                                if ($eStatus == "Pending") {
                                    echo "selected";
                                }
                                ?>>Pending
                                </option>
                                <option value="Cancelled" <?php
                                if ($eStatus == "Cancelled") {
                                    echo "selected";
                                }
                                ?>>Cancelled
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name="searchRider"
                                    data-text="Booked By(Select User)" id="searchRider">
                                <option value="">Booked By(Select User)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <select class="form-control filter-by-text driver_container" name="searchDriver"
                                data-text="Published By(Select User)" id="searchDriver">
                            <option value="">Published By(Select User)</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <input type="text" id="searchRideNo" name="searchRideNo" placeholder="Publish Ride Number"
                               class="form-control search-trip001" value="<?= $searchRideNo ?>">
                    </div>
                </div>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'ride_share_bookings.php'"/>
                        <?php  if (!empty($data_drv)) { ?>
                            <button type="button" onClick="reportExportTypes('PublishedRidesBooking')" class="export-btn001" style="float:none;">Export
                            </button>
                        <?php }  ?>
                    </b>
                </div>
            </form>

            <?php } ?>
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
                                        <th>Booking No.</th>
                                        <th>Publish Ride No.</th>
                                        <th>Booked By</th>
                                        <th>Published By</th>
                                        <th>Ride Start & End Time</th>
                                        <th>Start & End Location</th>
                                        <th style="text-align: center" >Booked Seats</th>
                                        <th>Booking Date</th>
                                        <th>Booking Status</th>
                                        <th>Details</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        $systemTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            $time = "";
                                            if($data_drv[$i]['fDuration'] != ""){
                                                $time = $RIDE_SHARE_OBJ->convertSecToMin(floor($data_drv[$i]['fDuration']));
                                            }
                                            $date_format_data_array = array(
                                                //'tdate' => (!empty($value['vTimeZone'])) ? converToTz($value['dRentItemPostDate'],$value['vTimeZone'], $systemTimeZone) : $value['dRentItemPostDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dStartDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['dStartDate'];
                                            $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                            $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dEndDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['dEndDate'];
                                            $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                            $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dBookingDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['dBookingDate'];
                                            $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text ="<br>(UTC:". DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr class="gradeA">
                                                <td><?= $data_drv[$i]['vBookingNo']; ?></td>
                                                <td><?php if (!in_array($data_drv[$i]['eStatus'] , ['Declined','Cancelled'])) { ?>
                                                    <a target="_blank" href="<?php echo $tconfig["tsite_url_main_admin"]?>prdetails.php?iPublishedRideId=<?= $data_drv[$i]['iPublishedRideId']; ?>"><?= $data_drv[$i]['vPublishedRideNo']; ?></a> <?php } else { ?>
                                                        <?= $data_drv[$i]['vPublishedRideNo']; ?>
                                                    <?php } ?></td>
                                                 <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $data_drv[$i]['rider_iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['rider_Name']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>

                                                    <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_publisher_details('<?= $data_drv[$i]['driver_iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['driver_Name']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>
                                                
                                                <td>
                                                    <div class="lableCombineData">
                                                        <label>Start Time</label>
                                                        <br>
                                                        <span><?= $get_dStartDate_format['tDisplayDateTime'];//date('M d, Y  h:i A', strtotime($data_drv[$i]['dStartDate'])); ?> </span>
                                                        <br>
                                                        <label>End Time</label>
                                                        <br>
                                                        <span><?= $get_dEndDate_format['tDisplayDateTime'];//date('M d, Y  h:i A', strtotime($data_drv[$i]['dEndDate'])); ?></span>
                                                    </div>
                                                </td>
                                                <td width="35%">
                                                    <div class="lableCombineData">
                                                        <label>Start Location</label>
                                                        <br>
                                                        <span><?= $data_drv[$i]['tStartLocation']; ?> </span>
                                                        <br>
                                                        <label>End Location</label>
                                                        <br>
                                                        <span>  <?= $data_drv[$i]['tEndLocation']; ?></span>
                                                    </div>
                                                </td>
                                                <td style="text-align: center" ><?= $data_drv[$i]['iBookedSeats']; ?></td>
                                                <td width="8%"><?= $get_dBookingDate_format['tDisplayDateTime'].$time_zone_difference_text ;//date('M d, Y  h:i A', strtotime($data_drv[$i]['dBookingDate'])); ?></td>
                                                <td>

                                                    <?php $data_eStatus =  $RIDE_SHARE_OBJ->getDisplayStatusForAdmin($data_drv[$i]['eTrackingStatus'] , $data_drv[$i]['eStatus'])['status'];
                                                        ?>
                                                    <?= $data_eStatus; ?>
                                                </td>
                                                <td>
                                                    <?php  $link_page = "ride_share_bookings_details.php"; ?>
                                                    <button class="btn btn-primary" onclick='return !window.open("<?= $link_page ?>?iBookingId=<?= $data_drv[$i]['iBookingId'] ?>", "_blank")' >
                                                        <i class="icon-th-list icon-white"><b>View Ride Details</b></i>
                                                    </button>
                                                </td>
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
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iGiftCardId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="status01" value="<?=$eStatus;?>">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="searchRideNo" value="<?= $searchRideNo; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
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
include_once('searchfunctions.php');
?>

<div class="modal fade " id="driver_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>Publisher Details<button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="driver_imageIcons">
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
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
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
         if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        } else {
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action + "?" + formValus;
        }
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
