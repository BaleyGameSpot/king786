<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-insurance-idle-report')) {
    $userObj->redirect();
}

$script = 'Insurance_Idle_time_Report';

$tbl_driver_locations = "driver_locations";

$systemTimeZone = date_default_timezone_get();

date_default_timezone_set('UTC');

$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "0";
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : "";

$searchQuery = array();

if(!empty($startDate) && !empty($endDate)) {
    $startDateString = DateTime::createFromFormat('Y-m-d', $startDate);
    $startDateString = $startDateString->format('Y-m-d\T00:00:00\Z');

    $endDateString = DateTime::createFromFormat('Y-m-d', $endDate);
    $endDateString = str_replace('T00:00:00Z', 'T23:59:59Z', $endDateString->format('Y-m-d\T00:00:00\Z'));

    $dates['$gte'] = $startDateString;
    $dates['$lte'] = $endDateString;
    $searchQuery['OnlineDateTime'] = $dates;
}

if($searchDriver > 0) {
    $searchQuery['iDriverId'] = $searchDriver;
} else {
    $searchQuery['iDriverId'] = array('$ne' => '');
}

if(!empty($serachTripNo)) {
    $tripData = $obj->MySQLSelect("SELECT iTripId FROM trips WHERE vRideNo = '$serachTripNo'");
    if(!empty($tripData)) {
        $searchQuery['iTripId'] = $tripData[0]['iTripId'];
    } else {
        $searchQuery['iTripId'] = "";
    }
}

$options['OnlineDateTime'] = -1;
$driver_locations = $obj->fetchAllRecordsFromMongoDBWithSortParams(TSITE_DB, $tbl_driver_locations, $searchQuery, $options);
$driver_locations = json_decode(json_encode((array) json_decode(\MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($driver_locations)))), true);

$per_page = $DISPLAY_RECORD_NUMBER;
$total_results = scount($driver_locations);
$total_pages = ceil($total_results / $per_page);
$show_page = 1;

$start = 0;
$end = $per_page;
if (isset($_REQUEST['page'])) {
    $show_page = $_REQUEST['page'];
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;

$start_limit = ($page - 1) * $per_page;
$driver_locations = array_slice($driver_locations, $start_limit, $per_page);
$endRecord = scount($driver_locations);

$driverDataArr = $tripDataArr = array();
if(!empty($driver_locations) && scount($driver_locations) > 0) {
    $iDriverIdArr = array_values(array_unique(array_column($driver_locations, 'iDriverId')));
    $iDriverIds = implode(",", array_filter($iDriverIdArr));

    $driverData = $obj->MySQLSelect("SELECT iDriverId, vName, vLastName, vPhone, vCode FROM register_driver WHERE iDriverId IN ($iDriverIds)");
    foreach ($driverData as $drv) {
        $driverDataArr[$drv['iDriverId']] = $drv;
    }

    $iTripIdArr = array_values(array_unique(array_column($driver_locations, 'iTripId')));
    $iTripIds = implode(",", $iTripIdArr);

    if(!empty($iTripIds)) {
        $tripsData = $obj->MySQLSelect("SELECT iTripId, vRideNo, iActive, eType, iOrderId FROM trips WHERE iTripId IN ($iTripIds)");
        foreach ($tripsData as $tr) {
            $tripDataArr[$tr['iTripId']] = $tr;
        }
    }
}


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
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Insurance Report (Idle Time)</title>
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
                        <h2>Insurance Report (Idle Time)</h2>
                        <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search"/>
                    <h3>Search <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>...</h3>
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
                        <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                        <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control"
                               value="" readonly="" style="cursor:default; background-color: #fff"/>
                       
                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text driver_container" name="searchDriver" data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                            </select>
                        </div>
                        
                        <div class="col-lg-2">
                            <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
                        </div>
                    </span>
                </div>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'insurance_idle_report.php'"/>
                        <?php if (scount($driver_locations) > 0 && $userObj->hasPermission('export-insurance-idle-report')) { ?>
                            <button type="button" onClick="reportExportTypes('insurance_report')" class="export-btn001">
                                Export
                            </button>
                        <?php } ?>
                    </b>
                </div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-bordered" id="dataTables-example123">
                                    <thead>
                                    <tr>
                                        <th width="6%" style="text-align:center;"><?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number</th>
                                        <th width="10%"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</th>
                                        <th width="8%"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                            Phone
                                        </th>
                                        <th width="8%" style="text-align:center;">Online Time</th>
                                        <th width="8%" style="text-align:center;"><?= $langage_lbl_admin['LBL_TRIP_TXT']; ?>
                                                Accepted/Offline Time</th>
                                        <th width="8%" style="text-align:center;">Approx Distance Travelled</th>
                                        <th width="8%" style="text-align:center;">Time Taken to Distance Travelled</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($driver_locations) && scount($driver_locations) > 0) {
                                        foreach ($driver_locations as $drv_loc) {

                                            $date_format_data_array = array(
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );

                                            $OnlineDateTime = date('Y-m-d H:i:s', strtotime($drv_loc['OnlineDateTime']));
                                            $OnlineDateTime = converToTz($OnlineDateTime, $systemTimeZone, "UTC");

                                            $date_format_data_array['tdate'] = $OnlineDateTime; 
                                            $get_OnlineDateTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $Start_time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($systemTimeZone, $date_format_data_array['tdate']).")";
                                            if(isset($drv_loc['OfflineDateTime'])) {
                                                $OfflineDateTime = date('Y-m-d H:i:s', strtotime($drv_loc['OfflineDateTime']));
                                                $OfflineDateTime = converToTz($OfflineDateTime, $systemTimeZone, "UTC");

                                                $date_format_data_array['tdate'] = $OfflineDateTime; 
                                                $get_OfflineDateTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($systemTimeZone, $date_format_data_array['tdate']).")";

                                            } else {
                                                $get_OfflineDateTime_format['tDisplayDateTime'] = "--";
                                                $time_zone_difference_text = "";
                                            }

                                            $totalDistance = 0;
                                            if(isset($drv_loc['Locations'])) {
                                                $totalDistance = calculateTotalDistance($drv_loc['Locations']);
                                            }
                                            ?>
                                            <tr class="gradeA ">
                                                <td align="center"><?
                                                    if (isset($drv_loc['iTripId']) && isset($tripDataArr[$drv_loc['iTripId']])) {
                                                        if ($tripDataArr[$drv_loc['iTripId']]['iActive'] == "Canceled") {
                                                            echo "<a href='trip.php?action=search&serachTripNo=" . $tripDataArr[$drv_loc['iTripId']]['vRideNo'] . "' target='_blank'>" . $tripDataArr[$drv_loc['iTripId']]['vRideNo'] . "</a><br> Canceled";
                                                        } else {
                                                            $link = "invoice.php?iTripId=" . $tripDataArr[$drv_loc['iTripId']]['iTripId'];
                                                            if ($tripDataArr[$drv_loc['iTripId']]['eType'] == "Multi-Delivery") {
                                                                $link = "invoice_multi_delivery.php?iTripId=" . $tripDataArr[$drv_loc['iTripId']]['iTripId'];
                                                            } else if ($tripDataArr[$drv_loc['iTripId']]['eType'] == "Ride" && $tripDataArr[$drv_loc['iTripId']]['iOrderId'] > 0) {
                                                                $link = "order_invoice.php?iOrderId=" . $tripDataArr[$drv_loc['iTripId']]['iOrderId'];
                                                            }
                                                            echo "<a href='$link' target='_blank'>" . $tripDataArr[$drv_loc['iTripId']]['vRideNo'] . "</a>";
                                                        }
                                                    } else {
                                                        echo "--";
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $drv_loc['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($driverDataArr[$drv_loc['iDriverId']]['vName'] . ' ' . $driverDataArr[$drv_loc['iDriverId']]['vLastName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?>
                                                </td>
                                                <td><?= '(+' . $driverDataArr[$drv_loc['iDriverId']]['vCode'] . ') ' . clearPhone($driverDataArr[$drv_loc['iDriverId']]['vPhone']); ?></td>
                                                <td align="center"><?= $get_OnlineDateTime_format['tDisplayDateTime'].$Start_time_zone_difference_text; ?></td>
                                                <td align="center"><?= $get_OfflineDateTime_format['tDisplayDateTime'].$time_zone_difference_text; ?></td>
                                                <td align="center">
                                                    <?php 
                                                    $vDistance = number_format($totalDistance, 2);
                                                    if ($DEFAULT_DISTANCE_UNIT == "Miles") {
                                                        $vDistance1 = str_replace(",", "", $vDistance);
                                                        $vDistance = number_format($vDistance1 * KM_TO_MILES_RATIO, 2);
                                                    }
                                                    echo $vDistance . " " . $DEFAULT_DISTANCE_UNIT;
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php 
                                                    $a = isset($drv_loc['OnlineDateTime']) ? strtotime($drv_loc['OnlineDateTime']) : 0;
                                                    $b = isset($drv_loc['OfflineDateTime']) ? strtotime($drv_loc['OfflineDateTime']) : 0;
                                                    $diff_time = ($b - $a);
                                                    $ans_diff = set_hour_min($diff_time);

                                                    if ($ans_diff['hour'] != 0) {
                                                        echo $ans_diff['hour'] . " Hours " . $ans_diff['minute'] . " Minutes";
                                                    } else {
                                                        if ($ans_diff['minute'] != 0) {
                                                            echo $ans_diff['minute'] . " Minutes ";
                                                        }
                                                        if ($ans_diff['second'] < 0) {
                                                            echo "---";
                                                        } else {
                                                            echo $ans_diff['second'] . " Seconds";
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="8" style="text-align:center;">No Details Found.</td>
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
                <h4>Notes:</h4>
                <ul>
                    <li>
                        This module will list all entries of <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                        Online and Offline/Trip Accept Time.
                    </li>
                    <!-- li>
                        The Time display as per the application state when the App is in foreground, however, in circumstance the app may gets killed the time display as blank in the report.
                    </li -->
                    <li>
                        Administrator can export data in XLS format.
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
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>">
    <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="eAddedFor" value="Available">
    <input type="hidden" name="export_file_name" value="idle_time_insurance_report">
    <input type="hidden" name="method" id="method" value="">
</form>

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

        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }

        var selectionText = item.text.split("--");
        if (selectionText[2] != null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2] + '</span>');
        } else if (selectionText[2] == null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if (selectionText[2] != null && selectionText[1] == null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }

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
                            company_id: $('#searchCompany option:selected').val(),
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
    var sId = '<?= $searchDriver;?>';
    var sSelect = $('select.filter-by-text#searchDriver');
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