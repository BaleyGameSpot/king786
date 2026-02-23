<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-provider-log-report')) {
    $userObj->redirect();
}
$script = "Driver Log Report";
/*$sql = "select iDriverId, CONCAT(vName,' ',vLastName) AS driverName ,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);
*/
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dlr.iDriverLogId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY rd.vName ASC"; else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY rd.vEmail ASC"; else
        $ord = " ORDER BY rd.vEmail DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY dlr.dLoginDateTime ASC"; else
        $ord = " ORDER BY dlr.dLoginDateTime DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY dlr.dLogoutDateTime ASC"; else
        $ord = " ORDER BY dlr.dLogoutDateTime DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vEmail = isset($_REQUEST['vEmail']) ? $_REQUEST['vEmail'] : '';
if ($startDate != '' && $endDate != '') {
    $search_startDate = $startDate . ' 00:00:00';
    $search_endDate = $endDate . ' 23:59:00';
    $ssql .= " AND dlr.dLoginDateTime BETWEEN '" . $search_startDate . "' AND '" . $search_endDate . "'";
}
if ($iDriverId != '') {
    $ssql .= " AND rd.iDriverId = '" . $iDriverId . "'";
}
if ($vEmail != '') {
    $ssql .= " AND rd.vEmail = '" . $vEmail . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(dlr.iDriverLogId) AS Total FROM driver_log_report AS dlr
LEFT JOIN register_driver AS rd ON rd.iDriverId = dlr.iDriverId where 1=1 AND rd.eStatus != 'Deleted' $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
$sql = "SELECT rd.iDriverId , rd.vName, rd.vLastName, rd.vEmail, dlr.dLoginDateTime, dlr.dLogoutDateTime
FROM driver_log_report AS dlr LEFT JOIN register_driver AS rd ON rd.iDriverId = dlr.iDriverId where 1=1 AND rd.eStatus != 'Deleted' $ssql $ord LIMIT $start, $per_page";
$db_log_report = $obj->MySQLSelect($sql);

$endRecord = scount($db_log_report);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
//echo "<pre>"; print_r($db_log_report); exit;
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
$sql1 = "SELECT dlr.dLoginDateTime, dlr.dLogoutDateTime FROM driver_log_report AS dlr LEFT JOIN register_driver AS rd ON rd.iDriverId = dlr.iDriverId where 1=1 AND rd.eStatus != 'Deleted' $ssql $ord";
$db_log_report_total_time = $obj->MySQLSelect($sql1);
function secToHR($seconds)
{
    $hours = floor($seconds / 3600);
    $mins = floor(($seconds / 60) % 60);
    $secs = $seconds % 60;
    if (strlen($hours) == 1) $hours = "0" . $hours;
    if (strlen($seconds) == 1) $seconds = "0" . $secs;
    if (strlen($minutes) == 1) $minutes = "0" . $mins;
    if ($hours == 0) {
        $mint = "";
        $secondss = "";
        if ($mins > 01) {
            $mint = "$mins mins";
        } else {
            $mint = "$mins min";
        }
        if ($secs > 01) {
            $secondss = "$secs seconds";
        } else {
            $secondss = "$secs second";
        }
        $ret = "$mint $secondss";
    } else {
        $mint = "";
        $secondss = "";
        if ($mins > 01) {
            $mint = "$mins mins";
        } else {
            $mint = "$mins min";
        }
        if ($secs > 01) {
            $secondss = "$secs seconds";
        } else {
            $secondss = "$secs second";
        }
        if ($hours > 01) {
            $ret = "$hours hrs $mint $secondss";
        } else {
            $ret = "$hours hr $mint $secondss";
        }
    }
    return $ret;
}
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
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN']; ?> Log
        Report<?php echo $langage_lbl_admin['LBL_DRIVER_LOG_REPORT_SMALL_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <? include_once('global_files.php'); ?>
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
                    <h2><?php echo $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN']; ?> Log Report</h2>
                </div>
            </div>
            <hr/>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                            <div class="Posted-date mytrip-page mytrip-page-select payment-report">
                                <input type="hidden" name="action" value="search"/>
                                <h3>Search by Date...</h3>
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
                                                   class="form-control" value="" readonly="">
                                            <input type="text" id="dp5" name="endDate" placeholder="To Date"
                                                   class="form-control" value="" readonly="">
                                            <div class="col-lg-4 select001">
                                                <select class="form-control filter-by-text" name='iDriverId'
                                                        data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>"
                                                        id="searchDriver">
                                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                                    <!-- <?php foreach ($db_drivers as $dbd) { ?>
                                                        <option value="<?php echo $dbd['iDriverId']; ?>" <?php
                                                        if ($iDriverId == $dbd['iDriverId']) {
                                                            echo "selected";
                                                        }
                                                        ?>> <?php echo clearName($dbd['driverName']); ?> - ( <?php echo clearEmail($dbd['vEmail']); ?> )</option>
                                                            <?php } ?> -->
                                                </select>
                                            </div>
                                        </span>
                            </div>
                            <div class="tripBtns001">
                                <b>
                                    <input type="submit" value="Search" class="btnalt button11" id="Search"
                                           name="Search" title="Search"/>
                                    <input type="button" value="Reset" class="btnalt button11"
                                           onClick="window.location.href = 'driver_log_report.php'"/>
                                    <?php if (!empty($db_log_report)) { ?>
                                    <button type="button" onClick="reportExportTypes('driver_log_report')"
                                            class="export-btn001">Export
                                    </button>
                                </b>
                                <?php } ?>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <form name="_list_form" id="_list_form" class="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                                    <thead>
                                    <tr>
                                        <th width="15%" >
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"> <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                                Name <?php
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
                                        <th width="10%" >
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Email <?php
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
                                        <th  width="8%"  style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Online Date & Time<?php
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
                                        <th width="8%" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Offline Date & Time<?php
                                                if ($sortby == 4) {
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
                                        <th  width="15%"  style="text-align:center;">Total Hours Login</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($db_log_report)) {
                                        for ($i = 0; $i < scount($db_log_report); $i++) {
                                            $dstart = $db_log_report[$i]['dLoginDateTime'];
                                            if ($db_log_report[$i]['dLogoutDateTime'] == '0000-00-00 00:00:00' || $db_log_report[$i]['dLogoutDateTime'] == '') {
                                                $dLogoutDateTime = '--';
                                                $totalTimecount = '--';
                                            } else {
                                                $dLogoutDateTime = $db_log_report[$i]['dLogoutDateTime'];
                                                $totalhours = get_left_days_jobsave($dLogoutDateTime, $dstart);
                                                $totalhours = ($totalhours / 60);
                                                //$totalTimecount = mediaTimeDeFormater($totalhours);
                                                $totalTimecount = mediaTimeDeFormater($totalhours, 'EN');
                                                //$totalTimecount = $totalTimecount.' hrs';
                                            }
                                            ?>
                                            <tr class="gradeA">
                                                <td>

                                                <a href="javascript:void(0);"
                                                   onClick="show_driver_details('<?= $db_log_report[$i]['iDriverId']; ?>')"
                                                   style="text-decoration: underline;"><? echo clearName($db_log_report[$i]['vName'] . ' ' . $db_log_report[$i]['vLastName']); ?></a>

                                                </td>
                                                <td><? echo clearEmail($db_log_report[$i]['vEmail']); ?></td>
                                                <td style="text-align:center;"><? echo DateTime($db_log_report[$i]['dLoginDateTime'], '21'); ?></td>
                                                <?php $logooutdate = ($db_log_report[$i]['dLogoutDateTime'] == '0000-00-00 00:00:00' || $db_log_report[$i]['dLogoutDateTime'] == '') ? '--' : DateTime($dLogoutDateTime, '21'); ?>
                                                <td align="center"><? echo $logooutdate; ?></td>
                                                <td align="center"><? echo $totalTimecount; ?></td>
                                            </tr>
                                        <? } ?>
                                        <?php if (!empty($db_log_report_total_time)) {
                                            for ($i = 0; $i < scount($db_log_report_total_time); $i++) {
                                                $dstart1 = $db_log_report_total_time[$i]['dLoginDateTime'];
                                                if ($db_log_report_total_time[$i]['dLogoutDateTime'] == '0000-00-00 00:00:00' || $db_log_report_total_time[$i]['dLogoutDateTime'] == '') {
                                                    // $totalsecondssum = '';
                                                } else {
                                                    $dLogoutDateTime1 = $db_log_report_total_time[$i]['dLogoutDateTime'];
                                                    $totalhours1 = get_left_days_jobsave($dLogoutDateTime1, $dstart1);
                                                    $totalsecondssum += $totalhours1;
                                                    //$totalTimecount = $totalTimecount.' hrs';
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td colspan="4" align="right">Grand Total Hours</td>
                                                <?php if ($totalsecondssum != '') { ?>
                                                    <td align="center"><?= secToHR($totalsecondssum); ?></td>
                                                <?php } else { ?>
                                                    <td align="center">--</td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr class="gradeA">
                                            <td colspan="5"> No Records Found.</td>
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
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="iDriverId" value="<?php echo $iDriverId; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>">
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
<? include_once('footer.php'); ?>
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

        /*$("select.filter-by-text").each(function () {
            $(this).select2({
                placeholder: $(this).attr('data-text'),
                allowClear: true
            }); //theme: 'classic'
        });*/
    });

    function setRideStatus(actionStatus) {
        window.location.href = "trip.php?type=" + actionStatus;
    }

    function todayDate() {
        //alert('sa');
        $("#dp4").val('<?= $Today; ?>');
        $("#dp5").val('<?= $Today; ?>');
    }

    function resetform() {
        //location.reload();
        document.search.reset();
        document.getElementById("iDriverId").value = " ";
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

    function checkvalid() {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        }
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
    var sId = '<?= $iDriverId;?>';
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
