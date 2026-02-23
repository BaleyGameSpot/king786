<?php
include_once '../common.php';

date_default_timezone_set('UTC');

$DbName = TSITE_DB;
$TableName = "request_log_data";

$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : date('Y-m-d');
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : date('Y-m-d');
$searchQuery = array();

$searchQuery = array();
if($startDate == $endDate) {
    $searchQuery['iDay'] = intval(date('d', strtotime($startDate)));
    $searchQuery['iMonth'] = intval(date('n', strtotime($startDate)));
    $searchQuery['iYear'] = intval(date('Y', strtotime($startDate)));    
} else {
    $dates['$lte'] = new \MongoDB\BSON\UTCDateTime(strtotime($endDate . '00:00:00') * 1000);
    $dates['$gte'] = new \MongoDB\BSON\UTCDateTime(strtotime($startDate . '00:00:00') * 1000);
    $searchQuery['vUsageDate'] = $dates;
}

$searchIP = isset($_REQUEST['ip']) ? $_REQUEST['ip'] : '';
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : '';
$mapApiService = isset($_REQUEST['mapApiService']) ? $_REQUEST['mapApiService'] : 'ReverseGeoCode';
if(!empty($searchIP) && !empty($searchDate)) {
    $searchIP = str_replace(".", "-", $searchIP);
    $searchQuery = array();
    $dates['$lte'] = new \MongoDB\BSON\UTCDateTime(strtotime($searchDate . '00:00:00') * 1000);
    $dates['$gte'] = new \MongoDB\BSON\UTCDateTime(strtotime($searchDate . '00:00:00') * 1000);
    $searchQuery['vUsageDate'] = $dates;
    $searchQuery['Data.' . $searchIP] = array('$exists' => true); 
    $projection = ['Data.' . $searchIP => true];

    $data_drv = $mongoDBObj->$TableName->findOne($searchQuery, ['projection' => $projection]);

    $log_report_arr = array();
    if(!empty($data_drv)) {
        $data_drv = json_decode(json_encode($data_drv), true);

        $log_report_arr = $data_drv['Data'][$searchIP];
        $ReverseGeoCodeArr = isset($log_report_arr['ReverseGeoCode']) ? $log_report_arr['ReverseGeoCode'] : [];
        $AutoCompleteArr = isset($log_report_arr['AutoComplete']) ? $log_report_arr['AutoComplete'] : [];
        $PlaceDetailsArr = isset($log_report_arr['PlaceDetails']) ? $log_report_arr['PlaceDetails'] : [];
        $DirectionArr = isset($log_report_arr['Direction']) ? $log_report_arr['Direction'] : [];
    }

} else {
    $data_drv = $obj->fetchAllRecordsFromMongoDB($TableName, $searchQuery);

    if(!empty($data_drv)) {
        $data_drv = json_decode(json_encode($data_drv), true);
    }

    $log_report_arr = array();
    $TotalApiRequestsRG = $TotalApiRequestsAC = $TotalApiRequestsPL = $TotalApiRequestsDIR = $TotalUniqueApiRequestsRG = $TotalUniqueApiRequestsAC = $TotalUniqueApiRequestsPL = $TotalUniqueApiRequestsDIR = 0;
    foreach ($data_drv as $data_log) {
        $ip_address_data = $data_log['Data'];
        $log_date = $data_log['iDay'] . '-' . $data_log['iMonth'] . '-' . $data_log['iYear'];
       
        foreach ($ip_address_data as $ipKey => $ip_data) {
            $ip = str_replace("-", ".", $ipKey);

            $DupRequestsPercentAC = $TotalRequestsAC = $UniqueRequestsAC = 0;
            if(!empty($ip_data['AutoComplete']['RequestParameters'])) {
                $AutoCompleteRequests = $ip_data['AutoComplete']['RequestParameters'];
                $TotalRequestsAC = scount($AutoCompleteRequests);
                $UniqueRequestsAC = array_map("unserialize", array_unique(array_map("serialize", $AutoCompleteRequests)));
                $UniqueRequestsAC = scount(array_values($UniqueRequestsAC));
                $DupRequestsPercentAC = round(((($TotalRequestsAC - $UniqueRequestsAC) / $TotalRequestsAC) * 100));
            }

            $DupPlaceDetailsPercentPL = $TotalRequestsPL = $UniqueRequestsPL = 0;
            if(!empty($ip_data['PlaceDetails']['RequestParameters'])) {
                $PlaceDetailsRequests = $ip_data['PlaceDetails']['RequestParameters'];
                $TotalRequestsPL = scount($PlaceDetailsRequests);
                $UniqueRequestsPL = array_map("unserialize", array_unique(array_map("serialize", $PlaceDetailsRequests)));
                $UniqueRequestsPL = scount(array_values($UniqueRequestsPL));
                $DupPlaceDetailsPercentPL = round(((($TotalRequestsPL - $UniqueRequestsPL) / $TotalRequestsPL) * 100));
            }

            $DupDirectionPercentDIR = $TotalRequestsDIR = $UniqueRequestsDIR = 0;
            if(!empty($ip_data['Direction']['RequestParameters'])) {
                $DirectionRequests = $ip_data['Direction']['RequestParameters'];
                $TotalRequestsDIR = scount($DirectionRequests);
                $UniqueRequestsDIR = array_map("unserialize", array_unique(array_map("serialize", $DirectionRequests)));
                $UniqueRequestsDIR = scount(array_values($UniqueRequestsDIR));
                $DupDirectionPercentDIR = round(((($TotalRequestsDIR - $UniqueRequestsDIR) / $TotalRequestsDIR) * 100));
            }
            
            $DupReverseGeoCodePercentRG = $TotalRequestsRG = $UniqueRequestsRG = 0;
            if(!empty($ip_data['ReverseGeoCode']['RequestParameters'])) {
                $ReverseGeoCodeRequests = $ip_data['ReverseGeoCode']['RequestParameters'];
                $TotalRequestsRG = scount($ReverseGeoCodeRequests);
                $UniqueRequestsRG = array_map("unserialize", array_unique(array_map("serialize", $ReverseGeoCodeRequests)));
                $UniqueRequestsRG = scount(array_values($UniqueRequestsRG));
                $DupReverseGeoCodePercentRG = round(((($TotalRequestsRG - $UniqueRequestsRG) / $TotalRequestsRG) * 100));
            }
            $log_report_arr[] = array(
                'IP' => $ip,
                'Date' => date('d-m-Y', strtotime($log_date)),
                'AutoComplete' => $ip_data['AutoComplete']['TotalCount'],
                'PlaceDetails' => $ip_data['PlaceDetails']['TotalCount'],
                'Direction' => $ip_data['Direction']['TotalCount'],
                'ReverseGeoCode' => $ip_data['ReverseGeoCode']['TotalCount'],
                'TotalRequestsAutoComplete' => $TotalRequestsAC,
                'TotalRequestsPlaceDetails' => $TotalRequestsPL,
                'TotalRequestsDirection' => $TotalRequestsDIR,
                'TotalRequestsReverseGeoCode' => $TotalRequestsRG,
                'UniqueRequestsAutoComplete' => $UniqueRequestsAC,
                'UniqueRequestsPlaceDetails' => $UniqueRequestsPL,
                'UniqueRequestsDirection' => $UniqueRequestsDIR,
                'UniqueRequestsReverseGeoCode' => $UniqueRequestsRG,
                'AutoCompletePercent' => $DupRequestsPercentAC,
                'PlaceDetailsPercent' => $DupPlaceDetailsPercentPL,
                'DirectionPercent' => $DupDirectionPercentDIR,
                'ReverseGeoCodePercent' => $DupReverseGeoCodePercentRG,
            );

            $TotalApiRequestsRG += $TotalRequestsRG;
            $TotalUniqueApiRequestsRG += $UniqueRequestsRG;

            $TotalApiRequestsAC += $TotalRequestsAC;
            $TotalUniqueApiRequestsAC += $UniqueRequestsAC;
            
            $TotalApiRequestsPL += $TotalRequestsPL;
            $TotalUniqueApiRequestsPL += $UniqueRequestsPL;
            
            $TotalApiRequestsDIR += $TotalRequestsDIR;
            $TotalUniqueApiRequestsDIR += $UniqueRequestsDIR;
            // echo "<pre>"; print_r($log_report_arr); exit;
        }
    }

    array_multisort(array_map('strtotime', array_column($log_report_arr, 'Date')), SORT_ASC, $log_report_arr);

    

    $per_page = $DISPLAY_RECORD_NUMBER;
    $total_results = scount($log_report_arr);
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
    $log_report_arr = array_slice($log_report_arr, $start_limit, $per_page);
}



// echo "<pre>"; print_r($data_drv); exit;

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

$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));

$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME ?> | Maps API Usage Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
        <style type="text/css">
            .ip-col {
                /*letter-spacing: 2px;*/
            }

            .Posted-date h3 {
                margin: 0;
            }

            .Posted-date span input[type='text'] {
                background: url(<?= $tconfig['tsite_url_main_admin'] ?>img/calander.png) no-repeat scroll right 11px top 6px;
                height: 37px;
            }

            .map-service {
                margin-bottom: 20px;
            }

            .map-service > label {
                font-size: 14px;
                display: inline-block;
                margin-right: 10px;
            }

            .map-service > span {
                display: inline-block;
            }
        </style>      
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>

            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Maps API Usage Report</h2>
                        </div>
                    </div>
                    <hr />
                    <?php if(!empty($searchIP) && !empty($searchDate)) { ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="Posted-date mytrip-page">
                                    <input type="hidden" name="action" value="search" />
                                    <h3>IP Address: <?= str_replace("-", ".", $searchIP) ?> (Date: <?= date('d-m-Y', strtotime($searchDate)) ?>)</h3>
                                </div>
                            </div>
                            <div class="col-lg-12 map-service">
                                <label>Select Service: </label>
                                <span>
                                    <select class="form-control" name="mapApiService" id="mapApiService">
                                        <option value="ReverseGeoCode" <?= $mapApiService == "ReverseGeoCode" ? 'selected="selected"' : '' ?> >ReverseGeoCode</option>
                                        <option value="AutoComplete" <?= $mapApiService == "AutoComplete" ? 'selected="selected"' : '' ?>>AutoComplete</option>
                                        <option value="PlaceDetails" <?= $mapApiService == "PlaceDetails" ? 'selected="selected"' : '' ?>>PlaceDetails</option>
                                        <option value="Direction" <?= $mapApiService == "Direction" ? 'selected="selected"' : '' ?>>Direction</option>
                                    </select>
                                </span>                                
                            </div>
                            <div class="col-lg-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Request Parameters</div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="mapApiServiceTable">
                                                <thead>
                                                    <tr>
                                                        <th width="100%">Request Parameters</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(!empty($log_report_arr[$mapApiService]['RequestParameters']) && scount($log_report_arr[$mapApiService]['RequestParameters']) > 0) { foreach ($log_report_arr[$mapApiService]['RequestParameters'] as $ReverseGeoCodeData) { ?>
                                                        <tr>
                                                            <td><pre><code><?= json_encode($ReverseGeoCodeData, JSON_UNESCAPED_UNICODE) ?></code></pre></td>
                                                        </tr>
                                                    <?php } } else { ?>
                                                        <tr><td>No records found.</td></tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>

                                            <?php include('pagination_n.php'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                                    <div class="Posted-date mytrip-page">
                                        <input type="hidden" name="action" value="search" />
                                        <h3>Search by Date...</h3>
                                        <span>
                                            <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                            <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                            <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                            <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                            <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                            <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Month']; ?></a>
                                        </span> 
                                        <span>
                                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" autocomplete="off" />
                                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" autocomplete="off" />
                                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'maps_api_report.php'"/>
                                        </span>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>IP Address</th>
                                                    <th>ReverseGeoCode</th>
                                                    <th>AutoComplete</th>
                                                    <th>PlaceDetails</th>
                                                    <th>Direction</th>                                 
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(!empty($log_report_arr) && scount($log_report_arr) > 0) { foreach ($log_report_arr as $ipKey => $log_report) { ?>
                                                    <tr>
                                                        <td><?= $log_report['Date'] ?></td>
                                                        <td class="ip-col"><?= $log_report['IP'] ?></td>
                                                        <td>
                                                        <?php if(!empty($log_report['ReverseGeoCode'])) { ?>
                                                            Total Requests: <?= $log_report['ReverseGeoCode'] ?><br>
                                                            Unique Requests: <?= $log_report['UniqueRequestsReverseGeoCode'] ?><br>
                                                            Duplicate Requests: <?= $log_report['ReverseGeoCodePercent'] ?>%
                                                        <?php } ?>
                                                        </td>
                                                        <td>
                                                        <?php if(!empty($log_report['AutoComplete'])) { ?>
                                                            Total Requests: <?= $log_report['AutoComplete'] ?><br>
                                                            Unique Requests: <?= $log_report['UniqueRequestsAutoComplete'] ?><br>
                                                            Duplicate Requests: <?= $log_report['AutoCompletePercent'] ?>%
                                                        <?php } ?>
                                                        </td>
                                                        <td>
                                                        <?php if(!empty($log_report['PlaceDetails'])) { ?>
                                                            Total Requests: <?= $log_report['PlaceDetails'] ?><br>
                                                            Unique Requests: <?= $log_report['UniqueRequestsPlaceDetails'] ?><br>
                                                            Duplicate Requests: <?= $log_report['PlaceDetailsPercent'] ?>%
                                                        <?php } ?>
                                                        </td>
                                                        <td>
                                                        <?php if(!empty($log_report['Direction'])) { ?>
                                                            Total Requests: <?= $log_report['Direction'] ?><br>
                                                            Unique Requests: <?= $log_report['UniqueRequestsDirection'] ?><br>
                                                            Duplicate Requests: <?= $log_report['DirectionPercent'] ?>%
                                                        <?php } ?>
                                                        </td>
                                                        <td><a href="maps_api_report.php?ip=<?= $log_report['IP'] ?>&searchDate=<?= $log_report['Date'] ?>" type="button" class="btn btn-primary" target="_blank">View</a></td>
                                                    </tr>
                                                <?php } } else { ?>
                                                    <tr><td colspan="7">No records found.</td></tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>

                                    <?php include('pagination_n.php'); ?>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="admin-notes">
                                <h4>Total Requests:</h4>
                                <ul>
                                    <li>
                                        <strong>ReverseGeoCode: </strong><?= $TotalApiRequestsRG; ?><br>
                                        <strong>Duplicate Requests: </strong><?= round(((($TotalApiRequestsRG - $TotalUniqueApiRequestsRG) / $TotalApiRequestsRG) * 100)); ?>%
                                    </li>
                                    <li>
                                        <strong>AutoComplete: </strong><?= $TotalApiRequestsAC; ?><br>
                                        <strong>Duplicate Requests: </strong><?= round(((($TotalApiRequestsAC - $TotalUniqueApiRequestsAC) / $TotalApiRequestsAC) * 100)); ?>%
                                    </li>
                                    <li>
                                        <strong>PlaceDetails: </strong><?= $TotalApiRequestsPL; ?><br>
                                        <strong>Duplicate Requests: </strong><?= round(((($TotalApiRequestsPL - $TotalUniqueApiRequestsPL) / $TotalApiRequestsPL) * 100)); ?>%
                                    </li>
                                    <li>
                                        <strong>Direction: </strong><?= $TotalApiRequestsDIR; ?><br>
                                        <strong>Duplicate Requests: </strong><?= round(((($TotalApiRequestsDIR - $TotalUniqueApiRequestsDIR) / $TotalApiRequestsDIR) * 100)); ?>%
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <?php include_once('footer.php'); ?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script>
            $(document).ready(function () {
                if ('<?= $startDate ?>' != '') {
                    $("#dp4").val('<?= $startDate ?>');
                    $("#dp4").datepicker('update', '<?= $startDate ?>');
                }
                if ('<?= $endDate ?>' != '') {
                    $("#dp5").datepicker('update', '<?= $endDate; ?>');
                    $("#dp5").val('<?= $endDate; ?>');
                }

                $('#mapApiServiceTable').dataTable({
                    "ordering": false
                });
            });

            $('#dp4').datepicker().on('changeDate', function (ev) {
                if (ev.date.valueOf() < endDate.valueOf()) {
                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    startDate = new Date(ev.date);
                    $('#startDate').text($('#dp4').data('date'));
                }
                $('#dp4').datepicker('hide');
            });

            $('#dp5').datepicker().on('changeDate', function (ev) {
                if (ev.date.valueOf() < startDate.valueOf()) {
                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    endDate = new Date(ev.date);
                    $('#endDate').text($('#dp5').data('date'));
                }
                $('#dp5').datepicker('hide');
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

            function todayDate()
            {
                //alert('sa');
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
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

            $('#mapApiService').change(function() {
                var curr_val = $(this).val();
                window.location.href = '<?= $tconfig['tsite_url_main_admin'] ?>maps_api_report.php?ip=<?= $_REQUEST['ip'] ?>&searchDate=<?= $_REQUEST['searchDate'] ?>&mapApiService=' + curr_val;
            });
        </script>
    </body>
</html>