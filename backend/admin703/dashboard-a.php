<?php
include_once('../common.php');

$AUTH_OBJ->checkMemberAuthentication();
$script = 'site';
$currendat =  date('Y-m-d');
    
$endDate_5 = date('Y-m-d', strtotime($currendat.' last day of -5 month'))." 23:59:59";
$startDate_5 = date('Y-m-d', strtotime($currendat.' first day of -5 month'))." 00:00:00";

$endDate_4 = date('Y-m-d', strtotime($currendat.' last day of -4 month'))." 23:59:59";
$startDate_4 = date('Y-m-d', strtotime($currendat.' first day of -4 month'))." 00:00:00";

$endDate_3 = date('Y-m-d', strtotime($currendat.' last day of -3 month'))." 23:59:59";
$startDate_3 = date('Y-m-d', strtotime($currendat.' first day of -3 month'))." 00:00:00";

$endDate_2 = date('Y-m-d', strtotime($currendat.' last day of -2 month'))." 23:59:59";
 $startDate_2 = date('Y-m-d', strtotime($currendat.' first day of -2 month'))." 00:00:00";

$endDate_1 = date('Y-m-d', strtotime($currendat.' last day of -1 month'))." 23:59:59";
$startDate_1 = date('Y-m-d', strtotime($currendat.' first day of -1 month'))." 00:00:00";

$endDate_0 = date('Y-m-d', strtotime($currendat.' last day of'))." 23:59:59";
$startDate_0 = date('Y-m-d', strtotime($currendat.' first day of'))." 00:00:00";

$rider = getRiderCount("");
$driver =getDriverDetailsDashboard('');

$company = getCompanycount();
$totalEarns = getTotalEarns();

$totalRides = getTripStatescount('total');
$onRides = getTripStatescount('on ride');
$cancelRides = getTripStatescount('cancelled');
$finishRides = getTripStatescount('finished');

$actDrive = getDrivercount('active');
$inaDrive = getDrivercount('inactive'); 
$delDrive = getDrivercount('Deleted');
    
/*****************************/
$finishRides_1 = getTripStatescount('finished',date('Y-m-d',strtotime($startDate_2)),date('Y-m-d',strtotime($endDate_2)));
$finishRides_2 = getTripStatescount('finished',date('Y-m-d',strtotime($startDate_1)),date('Y-m-d',strtotime($endDate_1)));
$finishRides_3 = getTripStatescount('finished',date('Y-m-d',strtotime($startDate_0)),date('Y-m-d',strtotime($endDate_0)));
    
$cancelledRides_1 = getTripStatescount('cancelled',date('Y-m-d',strtotime($startDate_2)),date('Y-m-d',strtotime($endDate_2)));
$cancelledRides_2 = getTripStatescount('cancelled',date('Y-m-d',strtotime($startDate_1)),date('Y-m-d',strtotime($endDate_1)));
$cancelledRides_3 = getTripStatescount('cancelled',date('Y-m-d',strtotime($startDate_0)),date('Y-m-d',strtotime($endDate_0)));
    
/*****************************/

/*****************************/
$sql = "SELECT count(iDriverId) as TotalDriver FROM register_driver WHERE 1 AND (vEmail != '' OR vPhone != '') AND tRegistrationDate BETWEEN '".$startDate_2."' AND '".$endDate_2."'";
$driver_2 = $obj->MySQLSelect($sql);

$sql = "SELECT count(iDriverId) as TotalDriver1 FROM register_driver WHERE 1 AND (vEmail != '' OR vPhone != '') AND tRegistrationDate BETWEEN '".$startDate_1."' AND '".$endDate_1."'";
$driver_1 = $obj->MySQLSelect($sql);

$sql = "SELECT count(iDriverId) as TotalDriver2 FROM register_driver WHERE 1 AND (vEmail != '' OR vPhone != '') AND tRegistrationDate BETWEEN '".$startDate_0."' AND '".$endDate_0."'";
$driver_0 = $obj->MySQLSelect($sql);
/*****************************/

/*****************************/
$sql = "SELECT count(iUserId) as TotalRider FROM register_user WHERE 1 AND (vEmail != '' OR vPhone != '') AND tRegistrationDate BETWEEN '".$startDate_2."' AND '".$endDate_2."'";
$pass_2 = $obj->MySQLSelect($sql);

$sql = "SELECT count(iUserId) as TotalRider1 FROM register_user WHERE 1 AND (vEmail != '' OR vPhone != '') AND tRegistrationDate BETWEEN '".$startDate_1."' AND '".$endDate_1."'";
$pass_1 = $obj->MySQLSelect($sql);

$sql = "SELECT count(iUserId) as TotalRider2 FROM register_user WHERE 1 AND (vEmail != '' OR vPhone != '') AND tRegistrationDate BETWEEN '".$startDate_0."' AND '".$endDate_0."'";
$pass_0 = $obj->MySQLSelect($sql);
/*****************************/

/*****************************/
//$startDate = date('Y-m', strtotime(date('Y-m-d')." -5 month"))."-"."01"." 00:00:00";
//$endDate = date('Y-m', strtotime(date('Y-m-d')." -5 month"))."-"."31"." 23:59:59";
$trip_amt5 = FetchTripGeneratedFare($startDate_5,$endDate_5);
$fTripGenerateFare5 =$fDiscount5=$fWalletDebit5=$iFare5=$Cash5=$Card5= 0;
$cashPayment = FetchTripGeneratedFare($startDate_5,$endDate_5,"Cash");
$cashPayment5 = FetchTripGeneratedFare($startDate_5,$endDate_5,"Cash");
$cardPayment5 = FetchTripGeneratedFare($startDate_5,$endDate_5,"Card");
for($i=0;$i<scount($cashPayment5);$i++) {
    $Cash5 += $cashPayment5[$i]['iFare'];
}

for($i=0;$i<scount($cardPayment5);$i++) {
    $Card5 += $cardPayment5[$i]['iFare'];
}
for($i=0;$i<scount($trip_amt5);$i++) {
    if($trip_amt5[$i]['fTripGenerateFare']) {
        $fTripGenerateFare5 += $trip_amt5[$i]['fTripGenerateFare'];
        $fDiscount5 += $trip_amt5[$i]['fDiscount'];
        $fWalletDebit5 += $trip_amt5[$i]['fWalletDebit'];
        $iFare5 += $trip_amt5[$i]['iFare'];
    }
}
$trip_amt4 = FetchTripGeneratedFare($startDate_4,$endDate_4);
$fTripGenerateFare4 =$fDiscount4=$fWalletDebit4=$iFare4=$Cash4=$Card4= 0;
$cashPayment = FetchTripGeneratedFare($startDate_4,$endDate_4,"Cash");
$cashPayment4 = FetchTripGeneratedFare($startDate_4,$endDate_4,"Cash");
$cardPayment4 = FetchTripGeneratedFare($startDate_4,$endDate_4,"Card");
for($i=0;$i<scount($cashPayment4);$i++) {
    $Cash4 += $cashPayment4[$i]['iFare'];
}
for($i=0;$i<scount($cardPayment4);$i++) {
    $Card4 += $cardPayment4[$i]['iFare'];
}
for($i=0;$i<scount($trip_amt4);$i++) {
    if($trip_amt4[$i]['fTripGenerateFare']) {
        $fTripGenerateFare4 += $trip_amt4[$i]['fTripGenerateFare'];
        $fDiscount4 += $trip_amt4[$i]['fDiscount'];
        $fWalletDebit4 += $trip_amt4[$i]['fWalletDebit'];
        $iFare4 += $trip_amt4[$i]['iFare'];
    }
}

$trip_amt3 = FetchTripGeneratedFare($startDate_3,$endDate_3);
$fTripGenerateFare3 =$fDiscount3=$fWalletDebit3=$iFare3=$Cash3=$Card3= 0;
$cashPayment = FetchTripGeneratedFare($startDate_3,$endDate_3,"Cash");
$cashPayment3 = FetchTripGeneratedFare($startDate_3,$endDate_3,"Cash");
$cardPayment3 = FetchTripGeneratedFare($startDate_3,$endDate_3,"Card");
for($i=0;$i<scount($cashPayment3);$i++){
    $Cash3 += $cashPayment3[$i]['iFare'];
}
for($i=0;$i<scount($cardPayment3);$i++){
    $Card3 += $cardPayment3[$i]['iFare'];
}
for($i=0;$i<scount($trip_amt3);$i++) {
    if($trip_amt3[$i]['fTripGenerateFare']) {
        $fTripGenerateFare3 += $trip_amt3[$i]['fTripGenerateFare'];
        $fDiscount3 += $trip_amt3[$i]['fDiscount'];
        $fWalletDebit3 += $trip_amt3[$i]['fWalletDebit'];
        $iFare3 += $trip_amt3[$i]['iFare'];
    }
}

$trip_amt2 = FetchTripGeneratedFare($startDate_2,$endDate_2);
$fTripGenerateFare2 =$fDiscount2=$fWalletDebit2=$iFare2=$Cash2=$Card2= 0;
$cashPayment = FetchTripGeneratedFare($startDate_2,$endDate_2,"Cash");
$cashPayment2 = FetchTripGeneratedFare($startDate_2,$endDate_2,"Cash");
$cardPayment2 = FetchTripGeneratedFare($startDate_2,$endDate_2,"Card");
for($i=0;$i<scount($cashPayment2);$i++){
    $Cash2 += $cashPayment2[$i]['iFare'];
}

for($i=0;$i<scount($cardPayment2);$i++) {
    $Card2 += $cardPayment2[$i]['iFare'];
}

for($i=0;$i<scount($trip_amt2);$i++) {
    if($trip_amt2[$i]['fTripGenerateFare']) {
        $fTripGenerateFare2 += $trip_amt2[$i]['fTripGenerateFare'];
        $fDiscount2 += $trip_amt2[$i]['fDiscount'];
        $fWalletDebit2 += $trip_amt2[$i]['fWalletDebit'];
        $iFare2 += $trip_amt2[$i]['iFare'];
    }
}
$trip_amt1 = FetchTripGeneratedFare($startDate_1,$endDate_1);
$fTripGenerateFare1 =$fDiscount1=$fWalletDebit1=$iFare1=$Cash1=$Card1= 0;
$cashPayment = FetchTripGeneratedFare($startDate_1,$endDate_1,"Cash");
$cashPayment1 = FetchTripGeneratedFare($startDate_1,$endDate_1,"Cash");
$cardPayment1 = FetchTripGeneratedFare($startDate_1,$endDate_1,"Card");
for($i=0;$i<scount($cashPayment1);$i++){
    $Cash1 += $cashPayment1[$i]['iFare'];
}

for($i=0;$i<scount($cardPayment1);$i++){
    $Card1 += $cardPayment1[$i]['iFare'];
}
for($i=0;$i<scount($trip_amt1);$i++) {
    if($trip_amt1[$i]['fTripGenerateFare']) {
        $fTripGenerateFare1 += $trip_amt1[$i]['fTripGenerateFare'];
        $fDiscount1 += $trip_amt1[$i]['fDiscount'];
        $fWalletDebit1 += $trip_amt1[$i]['fWalletDebit'];
        $iFare1 += $trip_amt1[$i]['iFare'];
    }
}

$trip_amt0 = FetchTripGeneratedFare($startDate_0,$endDate_0);
$fTripGenerateFare0 =$fDiscount0=$fWalletDebit0=$iFare0=$Cash0=$Card0= 0;
$cashPayment = FetchTripGeneratedFare($startDate_0,$endDate_0,"Cash");
$cashPayment0 = FetchTripGeneratedFare($startDate_0,$endDate_0,"Cash");
$cardPayment0 = FetchTripGeneratedFare($startDate_0,$endDate_0,"Card");
for($i=0;$i<scount($cashPayment0);$i++){
    $Cash0 += $cashPayment0[$i]['iFare'];
}
for($i=0;$i<scount($cardPayment0);$i++){
    $Card0 += $cardPayment0[$i]['iFare'];
}
for($i=0;$i<scount($trip_amt0);$i++) {
    if($trip_amt0[$i]['fTripGenerateFare']) {
        $fTripGenerateFare0 += $trip_amt0[$i]['fTripGenerateFare'];
        $fDiscount0 += $trip_amt0[$i]['fDiscount'];
        $fWalletDebit0 += $trip_amt0[$i]['fWalletDebit'];
        $iFare0 += $trip_amt0[$i]['iFare'];
    }
}
$vDefaultName = FetchDefaultCurrency();
$vDefaultName = $vDefaultName[0]['vName'];
/*****************************/
/*****************************/

function secToHR($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    return "$hours:$minutes:$seconds";
}

$sql = "SELECT rd.iDriverId, rd.vName, rd.vLastName, dlr.dLoginDateTime, dlr.dLogoutDateTime FROM driver_log_report AS dlr
LEFT JOIN register_driver AS rd ON rd.iDriverId = dlr.iDriverId where 1=1 AND dlr.dLoginDateTime BETWEEN '".date('Y-m', strtotime(date('Y-m-d')))."-"."01"." 00:00:00' AND '".date('Y-m', strtotime(date('Y-m-d')))."-"."31"." 23:59:59' order by dlr.iDriverLogId DESC";
$db_log_report = $obj->MySQLSelect($sql); 
$log_report = array();
for($i=0;$i<scount($db_log_report);$i++) {
    $dstart = $db_log_report[$i]['dLoginDateTime'];
    if( $db_log_report[$i]['dLogoutDateTime'] == '0000-00-00 00:00:00' || $db_log_report[$i]['dLogoutDateTime'] == '') {
        $dLogoutDateTime =$totalTimecount=$totalhours= '--';
    } else {
        $dLogoutDateTime = $db_log_report[$i]['dLogoutDateTime'];
        $totalhours = get_left_days_jobsave ($dLogoutDateTime,$dstart);
        $totalTimecount = mediaTimeDeFormater ($totalhours, $default_lang);
        $db_log_report[$i]['totalTimecount'] = $totalTimecount;
        $db_log_report[$i]['totalHourTimecount'] = $totalhours;
        if($db_log_report[$i]['iDriverId']) {
            if($db_log_report[$i]['totalTimecount'] > 0) {
                //$log_report[$db_log_report[$i]['iDriverId']] += $db_log_report[$i]['totalTimecount'];
                if(isset($log_report[$db_log_report[$i]['iDriverId']]['totalHourTimecount'])){
                    $log_report[$db_log_report[$i]['iDriverId']]['totalHourTimecount'] += $db_log_report[$i]['totalHourTimecount'];
                }else{
                    $log_report[$db_log_report[$i]['iDriverId']]['totalHourTimecount'] = $db_log_report[$i]['totalHourTimecount'];
                }
                if(isset($log_report[$db_log_report[$i]['iDriverId']]['totalTimecount'])){
                    $log_report[$db_log_report[$i]['iDriverId']]['totalTimecount']+= $db_log_report[$i]['totalTimecount'];
                }else{
                    $log_report[$db_log_report[$i]['iDriverId']]['totalTimecount'] = $db_log_report[$i]['totalTimecount'];
                }
                $log_report[$db_log_report[$i]['iDriverId']]['Name']= $db_log_report[$i]['vName'] . " " . $db_log_report[$i]['vLastName'];
                $log_report[$db_log_report[$i]['iDriverId']]['iDriverId']= $db_log_report[$i]['iDriverId'];
            }   
        }
    }
}
/*****************************/
if(!empty($log_report)) {
    arsort($log_report);
}
$iii=0;
if(!empty($log_report)) {
    foreach($log_report as $log_report_key => $log_report_val) {
        $tmp_log_report[$iii]['totalHourTimecount'] = $log_report_val['totalHourTimecount'];
        $tmp_log_report[$iii]['totalTimecount'] = $log_report_val['totalTimecount'];
        $tmp_log_report[$iii]['iDriverId'] = $log_report_val['iDriverId'];
        $tmp_log_report[$iii]['Name'] = $log_report_val['Name'];
        $iii++;
    }
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
            <meta charset="UTF-8" />
            <title>Admin | Dashboard</title>
            <meta content="width=device-width, initial-scale=1.0" name="viewport" />
            <!--[if IE]>
                    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
            <![endif]-->
            <!-- GLOBAL STYLES -->
            <?php include_once('global_files.php');?>
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="css/adminLTE/AdminLTE.min.css" />
            <script type="text/javascript" src="js/plugins/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="js/plugins/morris/raphael-min.js"></script>
            <script type="text/javascript" src="js/plugins/morris/morris.min.js"></script> 
            <script type="text/javascript" src="js/actions.js"></script>
            <!-- END THIS PAGE PLUGINS-->
            <!--END GLOBAL STYLES -->
            <!-- PAGE LEVEL STYLES -->
            <!-- END PAGE LEVEL  STYLES -->
            <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
            <!--[if lt IE 9]>
                    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
            <![endif]-->
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
                    <div class="inner" style="min-height: 700px;">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1>Site Statistics</h1>
                            </div>
                        </div>
                        <hr />
            <div class="row">
                            <div class="col-lg-6">
                                <div class="panel-heading">
                                    <div class="panel-title-box">
                    <i class="fa fa-bar-chart"></i> <?= $langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?> For last 3 Months
                                    </div>                                  
                </div>
                <div class="panel-body padding-0">
                                    <div id="last-6-rides"></div>
                </div>
                            </div>
                            <div class="col-lg-6">
                <div class="panel-heading">
                                    <div class="panel-title-box">
                    <i class="fa fa-bar-chart"></i> Registered Users For last 3 Months
                                    </div>                                  
                                </div>
                <div class="panel-body padding-0">
                                    <div id="total-users"></div>
                </div>
                            </div>
                        </div>
            <hr />
            <div class="row">
                            <div class="col-lg-6">
                                <div class="panel panel-primary bg-gray-light">
                                    <div class="panel-heading">
                    <div class="panel-title-box">
                                            <i class="fa fa-bar-chart"></i> <?= $langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>
                                        </div>                                  
                                    </div>
                                    <div class="panel-body padding-0">
                                        <div class="col-lg-6">
                                                <div class="chart-holder" id="dashboard-rides" style="height: 200px;"></div>
                                        </div>
                                        <div class="col-lg-6">
                                            <h3><?= $langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>  Count : <?=(number_format($totalRides[0]['tot_trip']));?></h3>
                                            <p>Today : <b><?php $today = getTripDateStates('today');echo number_format($today); ?></b></p>
                                            <p>This Month : <b><?=number_format(getTripDateStates('month'));?></b></p>
                                            <p>This Year : <b><?=number_format(getTripDateStates('year'));?></b></p>
                                            <br />
                                            <br />
                                            <p>
                                                    * This is count for all <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?> (Finished, ongoing, cancelled.)
                                            </p>
                                        </div>
                                    </div>
                </div> 
                            </div>
                            <div class="col-lg-6">
                <div class="panel panel-primary bg-gray-light">
                                    <div class="panel-heading">
                                        <div class="panel-title-box">
                                            <i class="fa fa-bar-chart"></i> <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'];?>
                    </div>                                  
                                    </div>
                                    <div class="panel-body padding-0">
                                        <div class="col-lg-6">
                                            <div class="chart-holder" id="dashboard-drivers" style="height: 200px;"></div>
                    </div>
                    <div class="col-lg-6">
                                            <h3><?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'];?>  Count : <?=number_format($driver);?></h3>
                                            <p>Today : <b><?=number_format(scount(getDriverDateStatus('today')));?></b></p>
                                            <p>This Month : <b><?=number_format(scount(getDriverDateStatus('month')));?></b></p>
                                            <p>This Year : <b><?=number_format(scount(getDriverDateStatus('year')));?></b></p>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
            <hr /> 
            <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-primary bg-gray-light">                                                                         <div class="panel-heading">
                    <div class="panel-title-box">
                                            <i class="fa fa-bar-chart"></i> Total Generated fare for Last 6 months (In <?=$vDefaultName?>) 
                    </div>                                  
                                    </div>
                                    <div class="panel-body padding-0">
                                        <div id="line-example"></div>
                                    </div>                          
                </div>
                <!-- END VISITORS BLOCK -->
                            </div>
                        </div>
            <hr />
            <div class="row">
                            <div class="col-lg-12">
                <div class="panel panel-primary bg-gray-light">
                                    <div class="panel-heading">
                    <div class="panel-title-box">
                                            <i class="fa fa-bar-chart"></i> <?= $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN'];?> Log Report Of Current Month (In Hours)
                    </div>                                  
                                    </div>
                                    <div class="panel-body padding-0">
                                        <div id="driver-log"></div>
                                    </div>                          
                                </div>
                <!-- END VISITORS BLOCK -->
                            </div>
                        </div>
                    </div>
        </div>
        <!--END PAGE CONTENT -->
            </div>
            <?php include_once('footer.php'); ?>
        </body>
    <!-- END BODY-->
</html>
<script>
    $(document).ready(function(){
        /* Donut dashboard chart */
        var total_ride = '<?=$totalRides[0]['tot_trip'];?>';
        var complete_ride = '<?=$finishRides[0]['tot_trip'];?>';
        var cancel_ride = '<?=$cancelRides[0]['tot_trip'];?>';
        var on_ride = '<?=$onRides[0]['tot_trip'];?>';
        if(complete_ride > 0 || cancel_ride > 0 || total_ride > 0 ) 
        {
            Morris.Donut({
                element: 'dashboard-rides',
                data: [
                {label: "On Going <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>", value: on_ride},
                {label: "Completed <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>", value: complete_ride},
                {label: "Cancelled <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>", value: cancel_ride}
                ],
                formatter: function (x) { return (x/total_ride *100).toFixed(2)+'%'+ ' ('+x+')'; },
                colors: ['#ee3324', '#f39c12', '#2baab1'],
                resize: true
            });
        } else {
            Morris.Donut({
                element: 'dashboard-rides',
                data: [
                {label: "On Going <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>", value: on_ride},
                {label: "Completed <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>", value: complete_ride},
                {label: "Cancelled <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>", value: cancel_ride}
                ],
                formatter: function (x) { return (0)+'%'+ ' ('+x+')'; },
                colors: ['#ee3324', '#f39c12', '#2baab1'],
                resize: true
            });
        }
        var active_drive = '<?=$actDrive[0]['tot_driver'];?>';
        var inactive_drive = '<?=$inaDrive[0]['tot_driver'];?>';        
        var total_drive = '<?=$driver;?>';

        Morris.Donut({
            element: 'dashboard-drivers',
            data: [
            {label: "Active <?=$langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'];?>", value: active_drive},
            {label: "Pending <?=$langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'];?>", value: inactive_drive}
            ],
            formatter: function (x) { return (x/total_drive *100).toFixed(2)+'%'+ '('+x+')'; },
            colors: ['#ee3324', '#f39c12'], 
            resize: true
        });
        /* END Donut dashboard chart  '#2baab1'*/
    });
</script>
<script>
    $(document).ready(function(){
        /* Donut chart */
        Morris.Bar({
            element: 'last-6-rides',
            data: [
            { y: '<?=date('M - y', strtotime(date('Y-m')." -2 month"));?>', a: <?=($finishRides_1[0]['tot_trip'])?>, b: <?=($cancelledRides_1[0]['tot_trip'])?>},
            { y: '<?=date('M - y', strtotime(date('Y-m')." -1 month"));?>', a: <?=($finishRides_2[0]['tot_trip'])?>, b: <?=($cancelledRides_2[0]['tot_trip'])?>},
            { y: '<?=date('M - y', strtotime(date('Y-m').""));?>', a: <?=($finishRides_3[0]['tot_trip'])?>, b: <?=($cancelledRides_3[0]['tot_trip'])?>},

            ],
            xkey: 'y',
            ykeys: ['a','b'],
            barColors: ['#0088cc', '#e36159'],
            gridTextColor: '#ee3324',
            labels: ['Finished <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>','Cancelled <?=$langage_lbl_admin['LBL_RIDES_NAME_ADMIN'];?>']
        });
        /* END Donut chart */

        /* Donut chart */
        Morris.Bar({
            element: 'total-users',
            data: [
                    { yy: '<?=date('M - y', strtotime(date('Y-m')." -2 month"));?>', aa: <?=$driver_2[0]['TotalDriver'];?>, bb: <?=$pass_2[0]['TotalRider'];?>},

                    { yy: '<?=date('M - y', strtotime(date('Y-m')." -1 month"));?>', aa: <?=$driver_1[0]['TotalDriver1'];?>,  bb: <?=$pass_1[0]['TotalRider1'];?> },
                    { yy: '<?=date('M - y', strtotime(date('Y-m').""));?>', aa: <?=$driver_0[0]['TotalDriver2'];?>,  bb: <?=$pass_0[0]['TotalRider2'];?> },
            ],
            xkey: 'yy',
            ykeys: ['aa', 'bb'],
            barColors: ['#ee3324', '#2baab1'],
            gridTextColor: '#ee3324',
            labels: ['<?=$langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'];?>', '<?=$langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN'];?>']
        });
        /* END Donut chart */

        Morris.Bar({
            element: 'line-example',
            data: [
            { y: '<?=date('M - y', strtotime(date('Y-m')." -5 month"));?>', a: <?=$fTripGenerateFare5?>, b: <?=$fDiscount5?> , c: <?=$fWalletDebit5?>, d : <?=$iFare5?> , e : <?=$Cash5?> , f : <?=$Card5?>},
            { y: '<?=date('M - y', strtotime(date('Y-m')." -4 month"));?>', a: <?=$fTripGenerateFare4?>, b: <?=$fDiscount4?> , c: <?=$fWalletDebit4?>, d : <?=$iFare4?>  , e : <?=$Cash4?> , f : <?=$Card4?>},
            { y: '<?=date('M - y', strtotime(date('Y-m')." -3 month"));?>', a: <?=$fTripGenerateFare3?>, b: <?=$fDiscount3?> , c: <?=$fWalletDebit3?> , d : <?=$iFare3?>  , e : <?=$Cash3?> , f : <?=$Card3?> },
            { y: '<?=date('M - y', strtotime(date('Y-m')." -2 month"));?>', a: <?=$fTripGenerateFare2?>, b: <?=$fDiscount2?> , c: <?=$fWalletDebit2?> , d : <?=$iFare2?>  , e : <?=$Cash2?> , f : <?=$Card2?> },
            { y: '<?=date('M - y', strtotime(date('Y-m')." -1 month"));?>', a: <?=$fTripGenerateFare1?>,  b: <?=$fDiscount1?> , c: <?=$fWalletDebit1?> , d : <?=$iFare1?> , e : <?=$Cash1?> , f : <?=$Card1?> },
            { y: '<?=date('M - y', strtotime(date('Y-m').""));?>', a: <?=$fTripGenerateFare0?>,  b: <?=$fDiscount0?> , c: <?=$fWalletDebit0?> , d : <?=$iFare0?> , e : <?=$Cash0?> , f : <?=$Card0?>}
            ],
            xkey: 'y',
            gridTextColor: '#000000',
            ykeys: ['a', 'b','c','d','e','f'],
            gridTextColor: '#ee3324',
            barColors: ['#ee3324', '#2baab1', '#8fa928', '#0088cc','#f39c12', '#6fba25'],
            labels: ['Generated Fare', 'Discount','Wallet','Paid By User','Paid In Cash','Paid In Card']
        });
        Morris.Bar({
            element: 'driver-log',
            data: [
            //totalTimecount
    <?php for($i = 0; $i < 5; $i ++ ) {
            if(isset($tmp_log_report[$i]['Name'])) { ?>
            { y: "<?=clearName($tmp_log_report[$i]['Name'])?>", a: '<?= secToHR($tmp_log_report[$i]['totalHourTimecount']);?>'},
    <?php } } ?>

            ],
            xkey: 'y',
            gridTextColor: '#ee3324',
            ykeys: ['a'],
            barColors: ['#2baab1'],
            labels: ['Hours']
        });
    });
</script>