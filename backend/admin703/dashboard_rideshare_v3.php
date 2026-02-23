<?php
include_once('../common.php');

$script = "dashboard";

/* --------------------------- six month wise earning --------------------------- */
$six_earning_month = [];
$six_total_Earns = [];
/* --------------------------- six month wise earning --------------------------- */
/* ------------------------------ for the order ----------------------------- */
$processing_status_array = array('1', '2', '4', '5', '12');
$all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');
if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature('Yes')) {
    $processing_status_array = array('1', '2', '4', '5', '12', '13', '14');
    $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12', '13', '14');
}
/* ------------------------------ for the order ----------------------------- */

$rider_count = getRiderCount();
$rider = $rider_count[0]['count(iUserId)'];
$totalEarns = getTotalEarns();

$active_rider_count = getRiderCount('active');
$active_rider_count_no = $active_rider_count[0]['count(iUserId)'];
$inactive_rider_count = getRiderCount('inactive');
$inactive_rider_count_no = $inactive_rider_count[0]['count(iUserId)'];


$user_status = ['Active', 'Inactive'];
$user_number = [(int)$active_rider_count_no,(int)$inactive_rider_count_no];

/*$user_status = ['Active', 'Inactive'];
$user_number = [50, 50];*/

$SystemDiagnosticData = $DASHBOARD_OBJ->getSystemDiagnosticData();
$working = $missing = 0;
foreach ($SystemDiagnosticData as $SysData) {
    if ($SysData['value']) {
        $working++;
    } else {
        $missing++;
    }
}
$alerts = 3;
$server_status = ['Working', 'Errors', 'Alerts'];
$server_number = [$working, $missing, $alerts];
$server_working = $working;
$server_missing = $missing;

$currencyData = FetchDefaultCurrency();
$DefaultCurrencySymbol = $currencyData[0]['vSymbol'];
$dSetupYear = date('Y', strtotime($SETUP_INFO_DATA_ARR[0]['dSetupDate']));

$style1 = "style = 'height:200px'";
$style2 = "style = 'height:115px'";
$style3 = "style = 'min-height:430px'";
$chartLoader = $tconfig['tsite_url_main_admin'] . "images/page-loader.gif";


$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';

$prsql = "SELECT pr.*, pr.iUserId AS driver_Id , CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name , riderDriver.eApproveDoc FROM published_rides pr JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId) WHERE 1=1 ORDER BY pr.iPublishedRideId  DESC LIMIT 0,5";

$db_finished_ridesharebookings = $obj->MySQLSelect($prsql);

/*------------------WayPoints-----------------*/
$iPublishedRideIdARR = array_column($db_finished_ridesharebookings,'iPublishedRideId');
$iPublishedRideIdARR = implode(',',$iPublishedRideIdARR);
$sql = "SELECT * FROM `published_rides_waypoints` WHERE iPublishedRideId IN ($iPublishedRideIdARR) ";
$waypoints_data = $obj->MySQLSelect($sql);


$RIDE_WAYPOINTS = [];
$RIDE_WAYPOINTS_SUM = [];
foreach ($waypoints_data as $w){
    $RIDE_WAYPOINTS[$w['iPublishedRideId']][]  = $w;
    $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] = $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] + $w['iBookedSeats'];
}

$RIDE_WAYPOINTS_ARR = [];
if(isset($RIDE_WAYPOINTS) && !empty($RIDE_WAYPOINTS))
{
    foreach ($RIDE_WAYPOINTS as $key => $waypoint){
        $wayPoints = $RIDE_SHARE_OBJ->wayPointDBToArray($waypoint);
        $wayPoints['iBookedSeats'] = $waypoint['iBookedSeats'];
        $RIDE_WAYPOINTS_ARR[$key] = $wayPoints['waypoint_data'];
    }
}

/*------------------WayPoints-----------------*/
/* Maps Api Settings */
$GOOGLE_KEYS_WEB = array('GOOGLE_SEVER_GCM_API_KEY', 'GOOGLE_SEVER_API_KEY_WEB');
$GOOGLE_KEYS_ANDROID['USER'] = array('GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY');
$GOOGLE_KEYS_ANDROID['DRIVER'] = array('GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY');
$GOOGLE_KEYS_ANDROID['STORE'] = array('GOOGLE_SERVER_ANDROID_COMPANY_APP_KEY');
$GOOGLE_KEYS_IOS['USER'] = array('GOOGLE_SERVER_IOS_PASSENGER_APP_KEY', 'GOOGLE_IOS_PASSENGER_APP_GEO_KEY');
$GOOGLE_KEYS_IOS['DRIVER'] = array('GOOGLE_SERVER_IOS_DRIVER_APP_KEY', 'GOOGLE_IOS_DRIVER_APP_GEO_KEY');
$GOOGLE_KEYS_IOS['STORE'] = array('GOOGLE_SERVER_IOS_COMPANY_APP_KEY', 'GOOGLE_IOS_COMPANY_APP_GEO_KEY');
$googleKeysConfigData = $obj->MySQLSelect("SELECT vName, vValue, tDescription, tHelp FROM configurations WHERE vName IN ('GOOGLE_SEVER_GCM_API_KEY', 'GOOGLE_SEVER_API_KEY_WEB', 'GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY', 'GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY', 'GOOGLE_SERVER_ANDROID_COMPANY_APP_KEY', 'GOOGLE_SERVER_IOS_PASSENGER_APP_KEY', 'GOOGLE_IOS_PASSENGER_APP_GEO_KEY', 'GOOGLE_SERVER_IOS_DRIVER_APP_KEY', 'GOOGLE_IOS_DRIVER_APP_GEO_KEY', 'GOOGLE_SERVER_IOS_COMPANY_APP_KEY', 'GOOGLE_IOS_COMPANY_APP_GEO_KEY') ORDER BY vOrder ");
$googleKeysPrimary = array_unique(array_column($googleKeysConfigData, 'vValue'));
$GOOGLE_KEYS_ARR = array();
foreach ($googleKeysConfigData as $ConfigData) {
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_WEB)) {
        $GOOGLE_KEYS_ARR['WEB'][] = $ConfigData;
    }
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_ANDROID['USER'])) {
        $GOOGLE_KEYS_ARR['ANDROID']['USER'][] = $ConfigData;
    }
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_ANDROID['DRIVER'])) {
        $GOOGLE_KEYS_ARR['ANDROID']['DRIVER'][] = $ConfigData;
    }
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_ANDROID['STORE'])) {
        $GOOGLE_KEYS_ARR['ANDROID']['STORE'][] = $ConfigData;
    }
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_IOS['USER'])) {
        $GOOGLE_KEYS_ARR['IOS']['USER'][] = $ConfigData;
    }
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_IOS['DRIVER'])) {
        $GOOGLE_KEYS_ARR['IOS']['DRIVER'][] = $ConfigData;
    }
    if(in_array($ConfigData['vName'], $GOOGLE_KEYS_IOS['STORE'])) {
        $GOOGLE_KEYS_ARR['IOS']['STORE'][] = $ConfigData;
    }
}
$GOOGLE_KEYS_WEB_VAL = array_column($GOOGLE_KEYS_ARR['WEB'], 'vValue');
$WebValues = array_filter($GOOGLE_KEYS_WEB_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_ANDROID_USER_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['USER'], 'vValue');
$AndroidUserValues = array_filter($GOOGLE_KEYS_ANDROID_USER_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_ANDROID_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['DRIVER'], 'vValue');
$AndroidDriverValues = array_filter($GOOGLE_KEYS_ANDROID_DRIVER_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_ANDROID_STORE_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['STORE'], 'vValue');
$AndroidStoreValues = array_filter($GOOGLE_KEYS_ANDROID_STORE_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_IOS_USER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['USER'], 'vValue');
$IOSUserValues = array_filter($GOOGLE_KEYS_IOS_USER_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_IOS_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['DRIVER'], 'vValue');
$IOSDriverValues = array_filter($GOOGLE_KEYS_IOS_DRIVER_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_IOS_STORE_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['STORE'], 'vValue');
$IOSStoreValues = array_filter($GOOGLE_KEYS_IOS_STORE_VAL, function ($value) {
    return empty($value);
});
$isMapApiSettingError = "No";
if(scount($WebValues) > 0 || scount($AndroidUserValues) > 0 || scount($AndroidDriverValues) > 0 || scount($AndroidStoreValues) > 0 || scount($IOSUserValues) > 0 || scount($IOSDriverValues) > 0 || scount($IOSStoreValues) > 0) {
    $isMapApiSettingError = "Yes";
    $MapApiSettingUrl = $tconfig['tsite_url_main_admin'] . 'general.php?tab=MapsApiSettings';
} else {
    $data_Service_names = $obj->fetchAllRecordsFromMongoDBWithDBName(TSITE_DB, "auth_master_accounts_places", []);
    $ServiceKey = array_search('Google', array_column($data_Service_names, 'vServiceName'));
    $data_Service_Google = $data_Service_names[$ServiceKey];
    $result = addDefaultApiKeysToMongoDB($GOOGLE_SEVER_GCM_API_KEY);
    if($result['Action'] == "0") {
        $isMapApiSettingError = "Yes";    
        $MapApiSettingUrl = $tconfig['tsite_url_main_admin'] . 'map_api_mongo_auth_places.php?id=' . $data_Service_Google['vServiceId'];
    } /*else {
        $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName(TSITE_DB, "auth_accounts_places", ['vServiceId' => intval($data_Service_Google['vServiceId'])]);
        if(!empty($data_drv) && scount($data_drv) > 0) {
            $GoogleKeys = array_column($data_drv, 'auth_key');
            $commonKeys = array_intersect($GoogleKeys, [$GOOGLE_SEVER_GCM_API_KEY]);
            if(scount($commonKeys) > 0) {
                $isMapApiSettingError = "Yes";
                $MapApiSettingUrl = $tconfig['tsite_url_main_admin'] . 'map_api_mongo_auth_places.php?id=' . $data_Service_Google['vServiceId'];
            }
        }
    }*/
}
/* Maps Api Settings */
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8">
<![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9">
<![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME; ?> | Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <![endif]-->
    <!-- GLOBAL STYLES -->
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/new_main.css"/>
    <link rel="stylesheet" href="css/admin_new/dashboard.css">
    <script src="<?= $tconfig['tsite_url_main_admin'] ?>js/apexcharts.js"></script>
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
<body class="padTop53 dasboard-main-responsive">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content" class="content_right">

        <div class="cintainerinner">
            <?php if($isMapApiSettingError == "Yes") { ?>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <div class="card d-flex">
                            <div class="card-body">
                                <h3 style="color: #ff0000; display: flex; align-items: center; justify-content: space-between; margin: 0;">
                                    <span><i class="fa fa-exclamation-triangle"></i> There are some misconfigurations in Maps API Settings.</span>
                                    <a href="<?= $MapApiSettingUrl ?>" class="viewsmall" style="padding: 10px 15px; font-size: 16px">Check Now</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="row clearfix d-flex dashboard-stats">

                <?php if ($onlyRideShareEnable == 'Yes' && $userObj->hasPermission('view-users')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head d-flex justify-space align-start">
                                    <strong>Users</strong>
                                    <?php if ($userObj->hasPermission('view-users')) {?>
                                        <a href="rider.php" class="viewsmall">View</a>
                                    <?php } ?>
                                </div>
                                <img id="UserStatuschart_loader" class="chart_loader"
                                     src="<?php echo $chartLoader; ?>">
                                <div id="UserStatuschart" <?php echo $style1; ?> ></div>
                            </div>
                            <div class="jobsrow">
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Today: </strong>
                                        <span class="count success-color"><?= number_format(scount(getUserDateStatus('today'))); ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>This Month: </strong>
                                        <span class="count pending-color"><?= number_format(scount(getUserDateStatus('month'))); ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>This Year:</strong>
                                        <span class="count proccess-color"><?= number_format(scount(getUserDateStatus('year'))); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($userObj->hasPermission('dashboard-server-statistics') && $MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live") { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head d-flex justify-space align-start">
                                    <strong>Server Statistics
                                        <small class="small-subtext">Last
                                            Updated: <?= date('d M Y') . " AT " . date('h:i A') ?></small>
                                    </strong>
                                    <?php if ($userObj->hasPermission('manage-server-admin-dashboard')) {?>
                                        <a href="server_admin_dashboard.php" class="viewsmall">View</a>
                                    <?php } ?>
                                </div>
                                <img id="serverStatuschart_loader" class="chart_loader"
                                     src="<?php echo $chartLoader; ?>">
                                <div id="serverStatuschart" <?php echo $style1; ?> ></div>
                            </div>
                            <div class="jobsrow">
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Working</strong>
                                        <span class="count success-color"><?= $server_working ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Errors</strong>
                                        <span class="count pending-color"><?= $server_missing ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Alerts</strong>
                                        <span class="count proccess-color"><?= $alerts ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($userObj->hasPermission('admin-earning-dashboard')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head">
                                    <strong>Admin Earning</strong>
                                    <img id="chart8_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                    <div id="chart8" <?php echo $style1; ?>></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($userObj->hasPermission('dashboard-earning-report')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head d-flex justify-space align-start">
                                    <strong>Earning Report</strong>
                                    <div class="combo-element">
                                        <label>Year:</label>
                                        <select class="gen-custom-select" onchange="getyear(this,'Earning_Report',12);"
                                                name="year">
                                            <?php for ($i = date('Y'); $i >= $dSetupYear; $i--) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <img id="chart6_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                <div id="chart6" <?php echo $style1; ?>></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
  


                <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-12 dashboard-stats-section">
                        <?php if (scount($latest_contactus) > 0) { ?>
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Contact Us Form Requests</strong>
                                        <?php if ($userObj->hasPermission('view-contactus-report')) { ?>
                                            <a href="contactus.php" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <ul class="statlist vertical Contact_Us">
                                        <?php $icon_color = 1; ?>

                                        <?php if (scount($latest_contactus) > 0 && !empty($latest_contactus)) {
                                            for ($i = 0; $i < scount($latest_contactus); $i++) {
                                                ?>
                                                <li>
                                                    <?php $tRequestDate = date("Y-m-d", strtotime($latest_contactus[$i]['tRequestDate']));
                                                    $queryString = "?action=search&iContactusId=" . $latest_contactus[$i]['iContactusId'];
                                                    ?>
                                                    <?php if ($userObj->hasPermission('view-contactus-report')) { ?>
                                                        <a href="contactus.php<?= $queryString ?>" class="list-group-item" target="_blank">
                                                    <?php } else { ?>
                                                        <a href="#" class="list-group-item">
                                                    <?php } ?>
                                                        <?php if ($icon_color == 5) {
                                                            $icon_color = 1;
                                                        } ?>
                                                        <div>
                                                            <i class="icon-color<?= $icon_color ?> ri-notification-line"></i>
                                                            <div class="stat-block">
                                                                <b><?php echo clearName(validName($latest_contactus[$i]['vFirstname'] . ' ' . $latest_contactus[$i]['vLastname'])); ?> </b>
                                                                <span class="text-ellipse fullwidth"
                                                                      title="<?= clearGeneralText(removehtml($latest_contactus[$i]['tDescription'])); ?>"
                                                                      data-toggle="tooltip"
                                                                      data-placement="top"> <?= clearGeneralText(removehtml($latest_contactus[$i]['tDescription'])); ?></span>
                                                            </div>
                                                        </div>
                                                        <small class="text-color<?= $icon_color ?> normalfont">
                                                            <?= humanReadableTimingDashboard($latest_contactus[$i]['tRequestDate']); ?>
                                                        </small>
                                                    <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')) { ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        </a>
                                                    <?php } ?>
                                                </li>
                                                <?php $icon_color = $icon_color + 1 ?>
                                            <?php }
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card d-flex" <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="d-flex justify-space align-start">
                                        <strong>Contact Us Form Requests</strong>
                                        <a href="contactus.php" class="viewsmall">View All</a>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Contact Us Form Requests.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>


                <?php if ($userObj->hasPermission('dashboard-latest-ride-job') && $onlyDeliverallModule == "NO") { ?>
                    <div class="col-sm-12 col-md-12 col-lg-12 dashboard-stats-section">
                        <?php if (scount($db_finished_ridesharebookings) > 0) { ?>
                            <div class="card"  <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Latest Rides</strong>
                                        <?php if ($userObj->hasPermission('view-published-rides-rideshare')) { ?>
                                            <a href="published_rides.php" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="common-table">
                                        <!-- Table -->
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th class="text-center">Ride No.</th>
                                                <th class="text-center">Published By</th>
                                                <th class="text-center">Published Date</th>
                                                <th class="text-center">Ride Start & End Time </th>
                                                <th class="text-center">Start & End Location</th> 
                                                <th class="text-center">Price Per Seat</th>
                                                <th class="text-center">Total Booking</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($x = 0; $x < scount($db_finished_ridesharebookings); $x++) { ?>
                                                <tr>
                                                    <td><?php echo $db_finished_ridesharebookings[$x]['vPublishedRideNo'] ?></td>
                                                    <td>
                                                        <?= clearName($db_finished_ridesharebookings[$x]['driver_Name']); ?>
                                                    </td>
                                                    <td>
                                                        <?= date('M d, Y h:i A', strtotime($db_finished_ridesharebookings[$x]['dAddedDate'])); ?>
                                                    </td>
                                                    <td class="normalfont">
                                                       <div class="lableCombineData">
                                                            <label>Start Time</label>
                                                            <br>
                                                            <span><?= date('M d, Y  h:i A', strtotime($db_finished_ridesharebookings[$x]['dStartDate'])); ?> </span>
                                                            <br>
                                                            <label>End Time</label>
                                                            <br>
                                                            <span> <?= date('M d, Y  h:i A', strtotime($db_finished_ridesharebookings[$x]['dEndDate'])); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="lableCombineData">
                                                            <label>Start Location</label>
                                                            <br>
                                                            <span><?= $db_finished_ridesharebookings[$x]['tStartLocation']; ?> </span>
                                                            <?php
                                                            if(isset($RIDE_WAYPOINTS_ARR[$db_finished_ridesharebookings[$x]['iPublishedRideId']]) && !empty($RIDE_WAYPOINTS_ARR[$db_finished_ridesharebookings[$x]['iPublishedRideId']])) {
                                                                $RIDE_WAYPOINTS = @$RIDE_WAYPOINTS_ARR[$db_finished_ridesharebookings[$x]['iPublishedRideId']];

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
                                                            <span>  <?= $db_finished_ridesharebookings[$x]['tEndLocation']; ?></span>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: right"><?= formateNumAsPerCurrency($db_finished_ridesharebookings[$x]['fPrice'], ''); ?></td>
                                                    <td style="text-align: center">
                                                        <?php
                                                        $totalBookSeats = $waypointBookSeats = 0;
                                                        if(isset($RIDE_WAYPOINTS_SUM[$db_finished_ridesharebookings[$x]['iPublishedRideId']]) && !empty($RIDE_WAYPOINTS_SUM[$db_finished_ridesharebookings[$x]['iPublishedRideId']]) )
                                                        {
                                                            $waypointBookSeats = $RIDE_WAYPOINTS_SUM[$db_finished_ridesharebookings[$x]['iPublishedRideId']];
                                                        }

                                                        $totalBookSeats =  $waypointBookSeats + $db_finished_ridesharebookings[$x]['iBookedSeats'];

                                                        ?>

                                                        <?php if($totalBookSeats > 0 && $db_finished_ridesharebookings[$x]['eStatus'] != "Cancelled"){ ?>
                                                        <a target="_blank"
                                                           href="ride_share_bookings.php?iPublishedRideId=<?= $db_finished_ridesharebookings[$x]['iPublishedRideId']; ?>"><?= $totalBookSeats; ?></a>
                                                        <?php }
                                                        else {
                                                            echo "-";
                                                        }?>

                                                    </td>
                                                    <td>
                                                        <?php

                                                            $link_page = "prdetails.php"; ?>
                                                            <button class="btn btn-primary" onclick='return !window.open("<?= $link_page ?>?iPublishedRideId=<?= $db_finished_ridesharebookings[$x]['iPublishedRideId'] ?>", "_blank")' >
                                                            <i class="icon-th-list icon-white"><b>View Ride Details </b></i>
                                                            </button>
                                                            <br/>
                                                            <br/>
                                                            <?php
                                                            if ($db_finished_ridesharebookings[$x]['iBookedSeats'] > 0 && $db_finished_ridesharebookings[$x]['eTrackingStatus'] == "End") {
                                                            if($db_finished_ridesharebookings[$x]['eTrackingStatus'] == "End"){
                                                                echo "Finished";
                                                            }else{
                                                                echo "On Going";
                                                            }

                                                        } ?>


                                                        <?php if(strtotime($db_finished_ridesharebookings[$x]['dStartDate']) > strtotime(date("Y-m-d H:i:s")) || $db_finished_ridesharebookings[$x]['eStatus'] == "Cancelled"){ ?>
                                                        <?= $db_finished_ridesharebookings[$x]['eStatus']; ?>


                                                        <?php }  else {
                                                            
                                                            if ($db_finished_ridesharebookings[$x]['eTrackingStatus'] == "Pending" || $db_finished_ridesharebookings[$x]['iBookedSeats'] == 0) {
                                                                echo "-";
                                                            }
                                                        }?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card d-flex <?php echo $style3; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-space align-start">
                                        <strong>Latest Rides</strong>
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                            <a href="published_rides.php" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Latest Rides.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

            </div>

        </div>
    </div>


<script>
    var user_status = <?php echo json_encode($user_status); ?>;
    var user_number = <?php echo json_encode($user_number); ?>;

    var server_status = <?php echo json_encode($server_status); ?>;
    var server_number = <?php echo json_encode($server_number); ?>;

    $(document).ready(function() {
        setTimeout(function() {

            /* --------------------------------- earning -------------------------------- */
            var earning_month = [];
            var total_Earns = [];


            getyear('', 'Earning_Report', 12);

            /* --------------------------------- earning -------------------------------- */

            /* --------------------------------- six earning -------------------------------- */
            var earning_month = [];
            var total_Earns = [];

            getyear('', 'Earning_Report_six', 6);

            /* --------------------------------- six earning -------------------------------- */

        }, 5000);
    });

    /* ---------------------------- user and provider --------------------------- */
    function getyear(year, chart_type, getMonth) {
        var curr_elem = year;
        $(curr_elem).closest('.card-body').find('.chart_loader').show();

        Y = '';
        if (year.value) {
            Y = year.value;
        }

        if (chart_type == 'Earning_Report') {
            $("#chart6").html('');
        }

        if (chart_type == 'Earning_Report_six') {
            $("#chart8").html('');
        }


        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_dashboard.php',
            'AJAX_DATA': {'chart_type': chart_type, 'year': Y, 'getMonth': getMonth}, 
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function(res) {
            if(res.action == "1") {
                var response = res.result;
                $(curr_elem).closest('.card-body').find('.chart_loader').hide();

                if (chart_type == 'Earning_Report') {
                    earning_month = response.data.earning_month;
                    total_Earns = response.data.total_Earns;
                    $("#chart6_loader").hide();
                    reShowYearChange(total_Earns, earning_month);
                }

                if (chart_type == 'Earning_Report_six') {
                    six_earning_month = response.data.earning_month;
                    six_total_Earns = response.data.total_Earns;

                    $("#chart8_loader").hide();
                    reShowMonthChangeEarning(six_total_Earns, six_earning_month);
                }


            }
            else {
                // console.log(res.result);
            }
        });
    }

  
    var UserStatuschart = {
        series: user_number,
        labels: user_status,
        chart: {
            type: 'donut',
            height: 200,
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }],
        fill: {
            colors: ['#28c76f', '#ea5455']
        },
        legend: {
            show: true,

            markers: {
                fillColors: ['#28c76f', '#ea5455']
            },
        },
        tooltip: {
            colors: ['#28c76f', '#ea5455']
        },
        colors: ['#28c76f', '#ea5455']
    };

    var serverStatuschart = {
        series: server_number,
        labels: server_status,
        chart: {
            type: 'donut',
            height: 200,
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }],
        fill: {
            colors: ['#28c76f', '#ea5455', '#ff9900']
        },
        legend: {
            show: true,

            markers: {
                fillColors: ['#28c76f', '#ea5455', '#ff9900']
            },
        },
        tooltip: {
            colors: ['#28c76f', '#ea5455', '#ff9900']
        },
        colors: ['#28c76f', '#ea5455', '#ff9900']
    };

    $(window).load(function () {
        setTimeout(function () {
            var chart12 = new ApexCharts(document.querySelector("#serverStatuschart"), serverStatuschart);
            chart12.render();
            $("#serverStatuschart_loader").hide();

            var chart20 = new ApexCharts(document.querySelector("#UserStatuschart"), UserStatuschart);
            chart20.render();
            $("#UserStatuschart_loader").hide();

        }, 1000);
    });

    function reShowYearChange(total_Earns, earning_month) {

        var earning_year = {
            series: [{
                name: 'Earnings',
                data: total_Earns
            }],
            chart: {
                type: 'bar',
                height: 285,
                toolbar: {
                    show: true,
                    tools: {
                        download: false
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: earning_month,
            },
            yaxis: {
                title: {
                    text: 'Total'
                }
            },
            legend: {
                show: true,
                position: 'top',
            },
            fill: {
                opacity: 3,
                colors: ['#7367F0'],
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "<?= $DefaultCurrencySymbol ?> " + val
                    }
                }
            }
        };
        $("#chart6").html('');
        var chart6 = new ApexCharts(document.querySelector("#chart6"), earning_year);
        chart6.render();
    }

    function reShowMonthChangeEarning(six_total_Earns, six_earning_month) {

        $("#chart8 , #chart8_loader").html('');
        var Earning_Report_Last_Six_month = {
            series: [{
                name: "Earnings",
                data: six_total_Earns
            }],

            noData: {
                text: undefined,
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: undefined,
                    fontSize: '14px',
                    fontFamily: undefined
                }
            },
            chart: {
                type: 'area',
                height: 200,
                toolbar: {
                    show: false,
                    tools: {
                        download: false
                    }
                }
            },

            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight',
                width: 2,
                colors: ['#28c76f'],
            },

            labels: six_earning_month,
            xaxis: {
                labels: {
                    rotate: -450,
                    style: {
                        fontSize: "0px",
                    }
                },
            },
            yaxis: {
                opposite: true,
                labels: {
                    rotate: -450,
                    style: {
                        fontSize: "0px",
                    }
                },
            },
            grid: {
                show: false,
            },

            legend: {
                labels: {
                    colors: ["#28c76f"],
                    useSeriesColors: true
                },
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        if (val != null) {
                            return "<?= $DefaultCurrencySymbol ?> " + val
                        }
                    }
                },
                onDatasetHover: {
                    highlightDataSeries: true,
                },
            },
            fill: {
                colors: ["#28c76f"],
            },
            colors: ["#28c76f"],
        };
        var chart8 = new ApexCharts(document.querySelector("#chart8"), Earning_Report_Last_Six_month);
        chart8.render();
    }


    $(".common-table").mCustomScrollbar({
        axis: "x",
        theme: "minimal-dark",
        scrollInertia: 200
    });

    function memberStatistics() {
        $('ul.statlist.dynamic-devide li').css('width', $('ul.statlist.dynamic-devide').innerWidth() / $('ul.statlist.dynamic-devide li').length - 1);
    }

    $(window).on("load resize", function (e) {
        memberStatistics()
    });

    $(document).ready(function () {
        if ($('.dashboard-stats-section').length % 2 == 1) {
            $('.dashboard-stats-section:last-child').removeClass('col-lg-6').addClass('col-lg-12');
        }
    });
</script>

</body>
</html>