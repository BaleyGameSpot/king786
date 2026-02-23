<?php

include_once('../common.php');

$script = "dashboard";
/* ------------------------------ org outstanding report ----------------------------- */
/* ------------------------------ org outstanding report end ----------------------------- */
/* -------------------------- TOtal Ride/Job chart last 5 day -------------------------- */
$ridetotaldate = [];
$rideTotalbydate = [];
for ($i = 4; $i > 0; $i--) {
    $startdate = date("Y-m-d 00:00:00", strtotime("-$i days"));
    $enddate = date("Y-m-d 23:59:59", strtotime("-$i days"));
    $totalRides = getTripStates('finished', $startdate, $enddate, '1');
    $ridetotaldate[] = date("Y-m-d", strtotime("-$i days"));
    $rideTotalbydate[] = $totalRides;
}
$totalRides = getTripStates('finished', date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"), '1');
$ridetotaldate[] = date("Y-m-d");
$rideTotalbydate[] = $totalRides;
/* ---------------------------- Total order chart last 5 day--------------------------- */
$ordertotaldate = [];
$orderTotalbydate = [];
for ($i = 4; $i > 0; $i--) {
    $startdate = date("Y-m-d 00:00:00", strtotime("-$i days"));
    $enddate = date("Y-m-d 23:59:59", strtotime("-$i days"));
    $totalRides = getStoreTripStates('finished', $startdate, $enddate, '1');
    $ordertotaldate[] = date("Y-m-d", strtotime("-$i days"));
    $orderTotalbydate[] = $totalRides;
}
$totalRides = getStoreTripStates('finished', date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"), '1');
$ordertotaldate[] = date("Y-m-d");
$orderTotalbydate[] = $totalRides;
/* ---------------------- month wise order and ride total get --------------------- */
$totalMonthdate = [];
$OrdertotalByMonth = [];
$RidetotalByMonth = [];
for ($i = 11; $i > 0; $i--) {
    $startdate = date('Y-m-01 00:00:00', strtotime("-$i month"));
    $enddate = date('Y-m-t 23:59:59', strtotime("-$i month"));
    $totalMonthdate[] = date('M', strtotime("-$i month"));
    $OrdertotalByMonth[] = getStoreTripStates('finished', $startdate, $enddate, '1');
    $RidetotalByMonth[] = getTripStates('finished', $startdate, $enddate, '1');
}
$totalMonthdate[] = date('M');
$OrdertotalByMonth[] = getStoreTripStates('finished', date("Y-m-01 00:00:00"), date("Y-m-t 23:59:59"), '1');
$RidetotalByMonth[] = getTripStates('finished', date("Y-m-01 00:00:00"), date("Y-m-t 23:59:59"), '1');
/* ---------------------- month wise order and ride total get end--------------------- */
/* ---------------------- month wise finished and cancelled ride get--------------------- */
$month = [];
$finishRidetotalByMonth = [];
$cancelledRidetotalByMonth = [];
/* ----------------------  month wise  finished and cancelled ride get end --------------------- */
/* ---------------------- month wise finished and cancelled order get --------------------- */
$order_month = [];
$finishOrdertotalByMonth = [];
$cancelledOrdertotalByMonth = [];
/* ----------------------  month wise  finished and cancelled order get end --------------------- */
/* --------------------------- month wise earning --------------------------- */
$earning_month = [];
$total_Earns = [];
/* --------------------------- month wise earning --------------------------- */
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
$company = getCompanyDetailsDashboard();
$driver = getDriverDetailsDashboard('');
$rider_count = getRiderCount();
$rider = $rider_count[0]['count(iUserId)'];
$totalEarns = getTotalEarns();
$org_outstanding_report = org_outstanding_report();
$outstanding_report = outstanding_report();
$UpcomingRide = getUpcomingRideDashboard();
$vehicleTypeArr = array();
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType , vc.vCategory_" . $default_lang . " AS subService, vcc.vCategory_" . $default_lang . " AS Service FROM vehicle_type left join " . $sql_vehicle_category_table_name . " as vc on vehicle_type.iVehicleCategoryId = vc.iVehicleCategoryId left join " . $sql_vehicle_category_table_name . " as vcc on vc.iParentId = vcc.iVehicleCategoryId WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
    $vehicleTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
    $vehicleTypeArr[$getVehicleTypes[$r]['iVehicleTypeId'] . "_subService"] = $getVehicleTypes[$r]['subService'];
    $vehicleTypeArr[$getVehicleTypes[$r]['iVehicleTypeId'] . "_service"] = $getVehicleTypes[$r]['Service'];
}
$totalOrganization = getOrganizationCount();
$totalRides = getTripStates('total');
$onRides = getTripStates('on ride');
$finishRides = getTripStates('finished');
$cancelRides = getTripStates('cancelled');
$actDrive = getDriverDetailsDashboard('active');
$inaDrive = getDriverDetailsDashboard('inactive');
/* ---------------------------------- store --------------------------------- */
$store_company = getStoreDetailsDashboard();
$store_driver = getDriverDetailsDashboard('');
$store_rider_count = getRiderCount();
$store_rider = $store_rider_count[0]['count(iUserId)'];
$store_totalEarns = getStoreTotalEarns();
$store_totalRides = getStoreTripStates('total');
$store_onRides = getStoreTripStates('on going order');
$store_finishRides = getStoreTripStates('Delivered');
$store_cancelRides = getStoreTripStates('Cancelled');
$store_actDrive = getStoreDetailsDashboard('active');
$store_inaDrive = getStoreDetailsDashboard('inactive');
/* ---------------------------------- store end--------------------------------- */

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
// echo "<pre>"; print_r($dSetupYear); exit;
$permissions_row_1 = $permissions_row_col_1 = $permissions_row_col_2 = "No";
if ($userObj->hasPermission('view-users') || $userObj->hasPermission('view-providers') || $userObj->hasPermission('view-company') || ($userObj->hasPermission('view-store') && $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) || ($userObj->hasPermission('view-organization') && $MODULES_OBJ->isOrganizationModuleEnable()) || $userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('dashboard-total-orders')) {
    $permissions_row_1 = "Yes";
    if ($userObj->hasPermission('view-users') || $userObj->hasPermission('view-providers') || $userObj->hasPermission('view-company') || ($userObj->hasPermission('view-store') && $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) || ($userObj->hasPermission('view-organization') && $MODULES_OBJ->isOrganizationModuleEnable())) {
        $permissions_row_col_1 = "Yes";
    }
    if ($userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('dashboard-total-orders')) {
        $permissions_row_col_2 = "Yes";
    }
}
$org_enable = "No";
if ($MODULES_OBJ->isRideFeatureAvailable('Yes') && $MODULES_OBJ->isOrganizationModuleEnable()) {
    $org_enable = "Yes";
}
$style1 = "style = 'height:200px'";
$style2 = "style = 'height:115px'";
$style3 = "style = 'min-height:430px'";
$chartLoader = $tconfig['tsite_url_main_admin'] . "images/page-loader.gif";

$sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate,c.iCompanyId,rd.iDriverId FROM `document_list` AS dl LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid LEFT JOIN company AS c ON ( c.iCompanyId = dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store')) LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver') LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car') LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId AND dm.doc_name_EN != '' HAVING dm.doc_name_" . $default_lang . " != '' ORDER BY dl.edate  DESC LIMIT 0,5";
$db_notification = $obj->MySQLSelect($sql);
if (isset($_REQUEST['allnotification'])) {
    $sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate FROM `document_list` AS dl
        LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid
        LEFT JOIN company AS c ON (c.iCompanyId=dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store'))
        LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver')
        LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car')
        LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId ORDER BY dl.edate DESC";
    $db_notification = $obj->MySQLSelect($sql);
}

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


$GOOGLE_KEYS_IOS_USER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['USER'], 'vValue');
$IOSUserValues = array_filter($GOOGLE_KEYS_IOS_USER_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_IOS_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['DRIVER'], 'vValue');
$IOSDriverValues = array_filter($GOOGLE_KEYS_IOS_DRIVER_VAL, function ($value) {
    return empty($value);
});
$GOOGLE_KEYS_IOS_STORE_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['STORE'], 'vValue');

if($MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) {
    $AndroidStoreValues = array_filter($GOOGLE_KEYS_ANDROID_STORE_VAL, function ($value) {
        return empty($value);
    });
    
    $IOSStoreValues = array_filter($GOOGLE_KEYS_IOS_STORE_VAL, function ($value) {
        return empty($value);
    }); 
}


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
    <? include_once('global_files.php'); ?>
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
        <?php if($isMapApiSettingError == "Yes" &&  $_SESSION['sess_iGroupId'] == 1 ) { ?>
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

        <?php if (isset($_SESSION['sess_iGroupId']) && (($_SESSION['sess_iGroupId'] != 4 && SITE_TYPE == "Live") || ($_SESSION['sess_iGroupId'] == 1 && SITE_TYPE == "Demo"))  && ($userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('dashboard-total-orders') || $userObj->hasPermission('dashboard-member-statistics') || $userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('admin-earning-dashboard')  || $userObj->hasPermission('dashboard-earning-report') || $userObj->hasPermission('later-bookings-dashboard') || $userObj->hasPermission('dashboard-server-statistics') || $userObj->hasPermission('dashboard-latest-ride-job') || $userObj->hasPermission('dashboard-contact-us-form Requests') || $userObj->hasPermission('dashboard-sos-requests') || $userObj->hasPermission('dashboard-payment-requests') || $userObj->hasPermission('dashboard-notifications-alerts-panel') || $userObj->hasPermission('userproviderstore-dashboard') || $userObj->hasPermission('dashboard-order-statistics') )) { ?>

            <div class="row clearfix d-flex">
                <?php if ($userObj->hasPermission('dashboard-member-statistics')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-8">
                        <div class="card d-flex">
                            <div class="card-body">
                                <div class="card-head">
                                    <strong>Member Statistics</strong>
                                </div>
                                <div class="valignline">
                                    <ul class="statlist dynamic-devide">
                                        
                                            <li>
                                                <a href="<?php if ($userObj->hasPermission('view-users')) { echo $LOCATION_FILE_ARRAY['RIDER.PHP']; } else { echo "javascript:void(0);"; } ?>">
                                                <i class="icon-color1 ri-team-line"></i>
                                                <div class="stat-block">
                                                    <b><?= number_format($rider); ?></b>
                                                    <span><?= $langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN']; ?></span>
                                                </div>
                                                </a>
                                            </li>
                                        

                                        
                                            <li>
                                                <a href="<?php if ($userObj->hasPermission('view-providers')) { echo $LOCATION_FILE_ARRAY['DRIVER.PHP']; ?>?type=approve <?php } else { echo "javascript:void(0);"; } ?> ">
                                                    <i class="icon-color2 ri-user-2-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= number_format($driver); ?></b>
                                                        <span><?= $langage_lbl_admin['LBL_DASHBOARD_DRIVERS_ADMIN']; ?></span>
                                                    </div>
                                                </a>
                                            </li>
                                        
                                        
                                        <?php if (!$MODULES_OBJ->isOnlyDeliverAllSystem()) { ?>
                                            <li>
                                                <a href="<?php if ($userObj->hasPermission('view-company')) { ?> company.php <?php } else { echo "javascript:void(0);"; } ?>">
                                                    <i class="icon-color3 ri-building-4-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= number_format($company); ?></b>
                                                        <span>Companies</span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } ?>
                                     

                                        <?php if ($MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) { ?>
                                            <li>
                                                <a href="<?php if ($userObj->hasPermission('view-store')) { ?> store.php <?php } else { echo "javascript:void(0);"; } ?>">
                                                    <i class="icon-color4 ri-store-2-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= number_format($store_company); ?></b>
                                                        <span><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];?> </span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } ?>

                                        <?php if ($MODULES_OBJ->isOrganizationModuleEnable()) { ?>
                                            <li>
                                                <a href="<?php if ($userObj->hasPermission('view-organization')) { ?> organization.php <?php } else { echo "javascript:void(0);"; } ?>">
                                                    <i class="icon-color5 ri-building-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= number_format($totalOrganization[0]['count']); ?></b>
                                                        <span>Organization</span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }
                if ($permissions_row_col_2 == "Yes") { ?>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-4">
                        <div class="row d-flex clearfix">
                            <?php if ($userObj->hasPermission('dashboard-total-ride-jobs')) { ?>
                                <div <?php if (!$userObj->hasPermission('dashboard-total-orders') && !$MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) { ?> class="col-sm-12" <?php } else { ?> class="col-sm-12 col-md-12 col-lg-6" <? } ?>>
                                    <div class="card">
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                            <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>">
                                        <?php } ?>
                                            <div class="card-body pb0">
                                                <div class="card-head">
                                                    <strong><?= $langage_lbl_admin['LBL_TOTAL_RIDES_ADMIN']; ?></strong>
                                                    <span class="count"><?= number_format($totalRides); ?></span>
                                                    <img id="chart0_loader" class="chart_loader"
                                                         src="<?php echo $chartLoader; ?>">
                                                    <div id="chart" <?php echo $style2; ?> ></div>
                                                </div>
                                            </div>
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>    
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php }
                            if ($userObj->hasPermission('dashboard-total-orders')) { ?>
                                <div <?php if ($userObj->hasPermission('dashboard-total-ride-jobs')) { ?> class="col-sm-12 col-md-12 col-lg-6" <?php } else { ?> class="col-sm-12" <? } ?>>
                                    <div class="card">
                                        <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                            <a href="allorders.php?type=allorders">
                                        <?php } ?>
                                            <div class="card-body pb0">
                                                <div class="card-head">
                                                    <strong><?php echo $langage_lbl_admin['LBL_TOTAL_ORDER_ADMIN']; ?></strong>
                                                    <span class="count"><?= number_format($store_totalRides); ?></span>
                                                    <img id="chart2_loader" class="chart_loader"
                                                         src="<?php echo $chartLoader; ?>">
                                                    <div id="chart2" <?php echo $style2; ?> ></div>
                                                </div>
                                            </div>
                                        <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="row clearfix d-flex dashboard-stats">
                <?php if ($userObj->hasPermission('admin-earning-dashboard')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head">
                                    <strong>Admin Earning</strong>
                                    <img id="chart8_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                    <div id="chart8" <?php echo $style1; ?>></div>
                                </div>
                                <!-- Ride -->
                            </div>
                            <div class="jobsrow">
                                <div class="jobscol" <?= $org_enable == "No" ? 'style="width: 50%"' : '' ?>>
                                    <?php if ($userObj->hasPermission('tripdetail-earning-dashboard')) { ?>
                                      <?php if (ONLYDELIVERALL == "Yes") { 
                                        $tripearningurl = "admin_payment_report.php";
                                      } else {
                                         $tripearningurl = $LOCATION_FILE_ARRAY['TOTAL_TRIP_DETAIL'];
                                      }?>   
                                    <a href="<?php echo $tripearningurl;?>">
                                    <?php } ?>
                                        <div class="card-foot">
                                            <strong>Total Earning</strong>
                                            <?php if (ONLYDELIVERALL == "Yes") { ?>
                                                <span class="count success-color"><?= formateNumAsPerCurrency($store_totalEarns, ''); ?></span>
                                            <?php } else { ?>
                                                <span class="count success-color"><?= formateNumAsPerCurrency($totalEarns, ''); ?></span>
                                            <?php } ?>
                                            
                                        </div>
                                    <?php if ($userObj->hasPermission('tripdetail-earning-dashboard')) { ?>
                                    </a>
                                    <?php } ?>
                                </div>
                                <div class="jobscol" <?= $org_enable == "No" ? 'style="width: 50%"' : '' ?>>
                                    <?php if ($userObj->hasPermission('view-user-outstanding-report')) { ?>
                                    <a href="outstanding_report.php">
                                    <?php } ?>
                                        <div class="card-foot">
                                            <strong>Outstanding Amount</strong>
                                            <span class="count pending-color"><?= formateNumAsPerCurrency($outstanding_report, ''); ?></span>
                                        </div>
                                    <?php if ($userObj->hasPermission('view-user-outstanding-report')) { ?>
                                    </a>
                                    <?php } ?>
                                </div>
                                <?php if ($org_enable == "Yes") { ?>
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-org-outstanding-reportt')) { ?>
                                        <a href="org_outstanding_report.php">
                                        <?php } ?>
                                            <div class="card-foot">
                                                <strong>Org. Outstanding Amount</strong>
                                                <span class="count proccess-color"><?= formateNumAsPerCurrency($org_outstanding_report, ''); ?></span>
                                            </div>
                                        <?php if ($userObj->hasPermission('view-org-outstanding-reportt')) { ?>
                                        </a>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
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


                <?php if ($userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('dashboard-total-orders') || $userObj->hasPermission('dashboard-ride-job-statistics') || $userObj->hasPermission('dashboard-order-statistics')) { ?>

                   <?php if ($userObj->hasPermission('dashboard-total-ride-jobs')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong><?= $langage_lbl_admin['LBL_TOTAL_RIDES_ADMIN']; ?></strong>
                                        <img id="ridepiechart_loader" class="chart_loader"
                                             src="<?php echo $chartLoader; ?>">
                                        <div id="ridepiechart" <?php echo $style1; ?> ></div>
                                    </div>
                                    <!-- Ride -->
                                </div>
                                <div class="jobsrow">
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                        <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>?vStatus=cancel">
                                            <?php } ?>
                                            <div class="card-foot">
                                                <strong><?= $langage_lbl_admin['LBL_CANCELLED_RIDES_ADMIN']; ?></strong>
                                                <span class="count pending-color"><?= number_format($cancelRides); ?></span>
                                            </div>
                                            <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                        </a>
                                    <?php } ?>
                                    </div>
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                        <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>?vStatus=complete">
                                            <?php } ?>
                                            <div class="card-foot">
                                                <strong><?= $langage_lbl_admin['LBL_COMPLETED_RIDES_ADMIN']; ?></strong>
                                                <span class="count success-color"><?= number_format($finishRides); ?></span>
                                            </div>
                                            <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                        </a>
                                    <?php } ?>
                                    </div>
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) {
                                        $status = "onRide";

                                        if(in_array($APP_TYPE, ['UberX']))
                                        {
                                            $status = "onJob";
                                        }

                                        ?>
                                        <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>?vStatus=<?= $status; ?>">
                                            <?php } ?>
                                            <div class="card-foot">
                                                <strong><?= $langage_lbl_admin['LBL_ON_RIDES_ADMIN']; ?></strong>
                                                <span class="count proccess-color"><?= number_format($onRides); ?></span>
                                            </div>
                                            <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                        </a>
                                    <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php  if ($userObj->hasPermission('dashboard-total-orders')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong><?php echo $langage_lbl_admin['LBL_TOTAL_ORDER_ADMIN']; ?></strong>
                                    </div>
                                    <img id="orderchart_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                    <div id="orderchart" <?php echo $style1; ?> ></div>
                                </div>
                                <div class="jobsrow">
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-cancelled-orders')) { ?>
                                        <a href="cancelled_orders.php">
                                            <?php } ?>
                                            <div class="card-foot">
                                                <strong><?= $langage_lbl_admin['LBL_CANCELLED_ORDERS_ADMIN']; ?></strong>
                                                <span class="count pending-color"><?= number_format($store_cancelRides); ?></span>
                                            </div>
                                            <?php if ($userObj->hasPermission('view-cancelled-orders')) { ?>
                                        </a>
                                    <?php } ?>
                                    </div>
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                        <a href="allorders.php?type=allorders&iStatusCode=6">
                                            <?php } ?>
                                            <div class="card-foot">
                                                <strong><?= $langage_lbl_admin['LBL_COMPLETED_ORDERS_ADMIN']; ?></strong>
                                                <span class="count success-color"><?= number_format($store_finishRides); ?></span>
                                            </div>
                                            <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                        </a>
                                    <?php } ?>
                                    </div>
                                    <div class="jobscol">
                                        <?php if ($userObj->hasPermission('view-processing-orders')) { ?>
                                        <a href="allorders.php?type=processing">
                                            <?php } ?>
                                            <div class="card-foot">
                                                <strong><?= $langage_lbl_admin['LBL_ON_ORDERS_ADMIN']; ?></strong>
                                                <span class="count proccess-color"><?= number_format($store_onRides); ?></span>
                                            </div>
                                            <?php if ($userObj->hasPermission('view-processing-orders')) { ?>
                                        </a>
                                    <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (!in_array($APP_TYPE, [
                        'Ride',
                        'UberX'
                    ])) { ?>
                    <?php } ?>

                    <?php if ($userObj->hasPermission('dashboard-ride-job-statistics')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong><?= $langage_lbl_admin['LBL_RIDE_STATISTICS_ADMIN']; ?></strong>
                                        <div class="combo-element">
                                            <label>Year:</label>
                                            <select class="gen-custom-select"
                                                    onchange="getyear(this,'Total_Ride_jobs',11);" name="year">
                                                <?php for ($i = date('Y'); $i >= $dSetupYear; $i--) { ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <img id="chart3_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                    <div id="chart3" <?php echo $style1; ?> ></div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($userObj->hasPermission('dashboard-order-statistics')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space flex-start">
                                        <strong><?= $langage_lbl_admin['LBL_ORDER_STATISTICS_ADMIN']; ?></strong>
                                        <div class="combo-element">
                                            <label>Year:</label>
                                            <select class="gen-custom-select" onchange="getyear(this,'Total_Order',11);"
                                                    name="year">
                                                <?php for ($i = date('Y'); $i >= $dSetupYear; $i--) { ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <img id="chart4_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                    <div id="chart4" <?php echo $style1; ?> ></div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                <?php } ?>

                <?php if (($userObj->hasPermission('later-bookings-dashboard') && $RIDE_LATER_BOOKING_ENABLED == 'Yes') || ($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live" && isset($_SESSION['sess_iGroupId']) && $_SESSION['sess_iGroupId'] != 4)) { ?>
                    <?php if ($userObj->hasPermission('later-bookings-dashboard') && $RIDE_LATER_BOOKING_ENABLED == 'Yes') { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <?php if (!empty($UpcomingRide)) {
                                if ($UpcomingRide[0]['dBooking_date'] != "" && $UpcomingRide[0]['vTimeZone'] != "") {
                                    $dBookingDate = converToTz($UpcomingRide[0]['dBooking_date'], $UpcomingRide[0]['vTimeZone'], date_default_timezone_get());
                                } else {
                                    $dBookingDate = $UpcomingRide[0]['dBooking_date'];
                                }
                                $viewService = 0;
                                if ($UpcomingRide[0]['iRentalPackageId'] > 0) {
                                    if (!empty($UpcomingRide[0]['vRentalVehicleTypeName'])) {
                                        $vehicleTypeName = $UpcomingRide[0]['vRentalVehicleTypeName'];
                                    } else {
                                        $vehicleTypeName = $UpcomingRide[0]['vVehicleType'];
                                    }
                                } else {
                                    $vehicleTypeName = $UpcomingRide[0]['vVehicleType'];
                                }
                                if (!empty($UpcomingRide[0]['tVehicleTypeFareData']) && empty($vehicleTypeName)) {
                                    $viewService = 1;
                                    $serviceJson = json_decode($UpcomingRide[0]['tVehicleTypeFareData'], true);
                                    $serviceJsonData = json_encode($serviceJson['FareData']);
                                }
                                $eType_new = $trip_type = $UpcomingRide[0]['eType'];
                                if ($eType_new == 'Ride' && $UpcomingRide[0]['iRentalPackageId'] > 0) {
                                    $trip_type = 'Rental Ride';
                                } else if ($eType_new == 'Ride') {
                                    $trip_type = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH'];
                                } else if ($eType_new == 'UberX') {
                                    $trip_type = 'Other Services';
                                } else if ($eType_new == 'Deliver') {
                                    $trip_type = 'Delivery';
                                }
                                if ($eType_new == 'Multi-Delivery' && $ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY == 'Yes') {
                                    $db_deliveryloc = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE `iCabBookingId` = " . $UpcomingRide[0]['iCabBookingId']);
                                    if (scount($db_deliveryloc) == 1) {
                                        $trip_type = $langage_lbl_admin["LBL_DELIVERY_TXT"];
                                    }
                                }
                                if (!empty($UpcomingRide[0]['iFromStationId']) && !empty($UpcomingRide[0]['iToStationId'])) {
                                    $trip_type = 'Fly';
                                }
                                ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="date-format">
                                            <div class="meetup-day">
                                                <h6> <?= strtoupper(date('D', strtotime($dBookingDate))) ?> </h6>
                                                <h3> <?= date('d', strtotime($dBookingDate)) ?> </h3>
                                            </div>
                                            <div class="card-head d-flex justify-space align-start"
                                                 style="width: calc(100% - 70px);">

                                            <span>

                                                <strong id="booking_no"
                                                        data-bookingno="<?= $UpcomingRide[0]['vBookingNo'] ?>">Booking No: <?= $UpcomingRide[0]['vBookingNo'] ?></strong>

                                                <p>Booked By: <?= !empty($UpcomingRide[0]['eBookingFrom']) ? $UpcomingRide[0]['eBookingFrom'] : $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?></p>

                                            </span>
                                                <span>
                                                <?php if ($userObj->hasPermission('view-ride-job-later-bookings')) { ?>
                                                    <a href="<?= $tconfig['tsite_url_main_admin']. $LOCATION_FILE_ARRAY['LATER_BOOKING']?>?keyword=<?= $UpcomingRide[0]['vBookingNo'] ?>" class="viewsmall" target="_blank">View</a>
                                                <?php }  ?>  

                                            </span>
                                            </div>
                                        </div>
                                        <ul class="statlist vertical newstyle">
                                            <li>
                                                <div class="d-flex justify-start">
                                                    <i class="icon-color1 ri-user-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= $UpcomingRide[0]['rider'] ?></b>
                                                        <span><?= $UpcomingRide[0]['vAvgRating'] ?> ★</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="d-flex justify-start">
                                                    <i class="icon-color2 ri-calendar-2-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= date('D, M d, Y', strtotime($dBookingDate)) ?></b>
                                                        <span><?= date('h:i A', strtotime($dBookingDate)) ?></span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="d-flex justify-start">
                                                    <i class="icon-color3 ri-map-pin-line"></i>
                                                    <div class="stat-block">
                                                        <b><?= $UpcomingRide[0]['vSourceAddresss'] . (!empty($UpcomingRide[0]['tDestAddress']) ? ' ➜ ' . $UpcomingRide[0]['tDestAddress'] : '') ?></b>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="d-flex justify-start">
                                                    <i class="icon-color4 ri-list-settings-line"></i>
                                                    <div class="stat-block">
                                                        <?php if ($viewService == 1) { ?>
                                                            <b><?= $vehicleTypeArr[$serviceJson['FareData'][0]['id'] . '_service'] . ' - ' . $vehicleTypeArr[$serviceJson['FareData'][0]['id'] . '_subService'] ?></b>
                                                            <textarea id="serviceArr"
                                                                      style="display: none;"><?= $serviceJsonData; ?></textarea>
                                                        <?php } else {
                                                            if ($UpcomingRide[0]['iRentalPackageId'] > 0) {
                                                                if (!empty($UpcomingRide[0]['vRentalVehicleTypeName'])) { ?>
                                                                    <b><?= $vehicleTypeName ?></b>
                                                                <?php } else { ?>
                                                                    <b><?= $vehicleTypeName ?></b>
                                                                <?php }
                                                            } else { ?>
                                                                <b><?= $vehicleTypeName ?></b>
                                                            <?php }
                                                        }
                                                        ?>
                                                        <span>Service Type (<?= $trip_type ?>)</span>
                                                    </div>
                                                    
                                                </div>
                                            </li>
                                        </ul>
                                        <?php if ($viewService == 1) { ?>
                                            <div class="card-head">
                                                <a href="javascript:void(0);" class="viewsmall"
                                                   onclick="showServiceModal();">View Services
                                                </a>
                                            </div>
                                        <?php } ?>



                                        <?php /*if(scount($UpcomingRide) > 1) { ?>

                                        <div class="more-booking align-center">

                                            <a href="cab_booking.php" target="_blank"><i class="ri-more-fill" data-toggle="tooltip" title="View <?= $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'] ?>"></i></a>

                                        </div>

                                    <?php }*/ ?>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="card d-flex">
                                    <div class="card-body">
                                        <div class="d-flex justify-space align-start">
                                            <span>&nbsp;</span>
                                            <?php if ($userObj->hasPermission('view-ride-job-later-bookings')) { ?>
                                            <a href="<?php echo $LOCATION_FILE_ARRAY['LATER_BOOKING'];?>" class="viewsmall" target="_blank">
                                                View <?= $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'] ?></a>
                                            <?php } ?>
                                        </div>
                                        <div class="no-data-found">
                                            <strong>No Upcoming <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] ?>.
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
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
                <?php } ?>



                <?php if (($userObj->hasPermission('dashboard-latest-ride-job') && $onlyDeliverallModule == "NO") || $userObj->hasPermission('dashboard-latest-order')) { ?>
                    <?php if ($userObj->hasPermission('dashboard-latest-ride-job') && $onlyDeliverallModule == "NO") { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section ">
                        <?php if (scount($db_finished) > 0) { 

                            ?>
                            <div class="card"  <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Latest <?= $langage_lbl_admin['LBL_RIDES_NAME_ADMIN'] ?></strong>
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                            <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="common-table">
                                        <!-- Table -->
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th class="text-center">Booking No</th>
                                                <th class="text-center">Address</th>
                                                <th class="text-center">User</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($x = 0; $x < scount($db_finished); $x++) { 

                                                $date_format_data_array = array(
                                                    'langCode' => $default_lang,
                                                    'DateFormatForWeb' => 1
                                                );
                                                $date_format_data_array['tdate'] = $db_finished[$x]['tEndDate'];
                                                $get_tEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                ?>
                                                <tr>
                                                    <td><?php echo $x + 1 ?></td>
                                                    <td>
                                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { 
                                                            if($APP_TYPE == "UberX"){?>
                                                                <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>?action=search&serachJobNo=<?php echo $db_finished[$x]['vRideNo'] ?>"> <?php echo $db_finished[$x]['vRideNo'] ?></a>
                                                            <?php  } else { ?>
                                                                <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>?action=search&serachTripNo=<?php echo $db_finished[$x]['vRideNo'] ?>"> <?php echo $db_finished[$x]['vRideNo'] ?></a>
                                                            <?php }

                                                        }  else { ?>
                                                            <?php echo $db_finished[$x]['vRideNo'] ?>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="overflow">

                                                        <span title="<?php echo $db_finished[$x]['tSaddress'] ?> ➜ <?php echo $db_finished[$x]['tDaddress'] ?>"
                                                              data-toggle="tooltip" <?php if ($x == 0) { ?> data-placement="bottom"
                                                              <?php } ?>class="text-ellipse"><?php echo $db_finished[$x]['tSaddress'] ?> ➜ <?php echo $db_finished[$x]['tDaddress'] ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="nowrap"><?php echo clearName($db_finished[$x]['vName'] . ' ' . $db_finished[$x]['vLastName']) ?></span>
                                                    </td>
                                                    <td class="normalfont">
                                                        <span class="nowrap"><?= $get_tEndDate_format['tDisplayDateTime'];//date('jS M Y', strtotime($db_finished[$x]['tEndDate'])); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (($db_finished[$x]['iActive'] == 'Finished' && $db_finished[$x]['eCancelled'] == "Yes") || ($db_finished[$x]['fCancellationFare'] > 0) || ($db_finished[$x]['iActive'] == 'Canceled' && $db_finished[$x]['fWalletDebit'] > 0)) { ?>
                                                            <?php if ($db_finished[$x]['eCancelled'] != "Yes") { ?>
                                                                <p class="mb0">Finished</p>
                                                            <?php } ?>
                                                            <?php if ($db_finished[$x]['eCancelled'] == "Yes") { ?>
                                                                <p class="mb0">Cancelled</p>
                                                            <?php } ?>
                                                            <?php if ($userObj->hasPermission('view-invoice')) { 
                                                             if($APP_TYPE == "UberX"){
                                                                $invoiceurl = "invoice.php?iJobId=".$db_finished[$x]['iTripId'];
                                                             } else {
                                                                $invoiceurl = "invoice.php?iTripId=".$db_finished[$x]['iTripId'];
                                                             }?>
                                                            <button class="btn btn-primary"
                                                                    onclick='return !window.open("<?php echo $invoiceurl;?>", "_blank");'>
                                                                <b>View Invoice</b>
                                                            </button>
                                                            <?php } ?>
                                                        <?php } else if ($db_finished[$x]['iActive'] == 'Finished') { 
                                                            if($APP_TYPE == "UberX"){
                                                                $invoiceurl = "invoice.php?iJobId=".$db_finished[$x]['iTripId'];
                                                             } else {
                                                                if($db_finished[$x]['eType'] == "Multi-Delivery"){
                                                                    $invoiceurl = "invoice_multi_delivery.php?iTripId=".$db_finished[$x]['iTripId'];
                                                                }else{
                                                                    $invoiceurl = "invoice.php?iTripId=".$db_finished[$x]['iTripId'];
                                                                }

                                                             }
                                                             ?>
                                                            <p class="mb0">Finished</p>
                                                            <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                            <button class="btn btn-primary"
                                                                    onclick='return !window.open("<?php echo $invoiceurl;?>", "_blank");'>
                                                                <b>View Invoice</b>
                                                            </button>
                                                            <?php } ?>
                                                            <?php
                                                        } else {
                                                            if ($db_finished[$x]['iActive'] == "Active" or $db_finished[$x]['iActive'] == "On Going Trip" or ($db_finished[$x]['iActive'] == "Arrived" and !empty($db_finished[$x]['iFromStationId']) and !empty($db_finished[$x]['iToStationId']))) {
                                                                if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
                                                                    echo "On Job";
                                                                } else {
                                                                    echo "On Ride";
                                                                }
                                                                ?>
                                                                <br/> <!-- Commented By HJ On 11-01-2019 As Per Discuss withQA BM  -->
                                                                <a href="javascript:void(0);"
                                                                   onClick="resetOnlyTripStatus('<?= $db_finished[$x]['iTripId']; ?>')"
                                                                   data-toggle="tooltip" title="Reset">
                                                                    Reset Trip
                                                                </a>
                                                                <?php
                                                            } else if ($db_finished[$x]['iActive'] == "Canceled" && ($db_finished[$x]['iCancelReasonId'] > 0 || $db_finished[$x]['vCancelReason'] != '')) {
                                                                ?>
                                                                <p class="mb0">Cancelled</p>
                                                                <a href="javascript:void(0);" class="btn btn-info"
                                                                   data-toggle="modal"
                                                                   data-target="#uiModal1_<?= $db_finished[$x]['iTripId']; ?>">
                                                                    View Reason
                                                                </a>
                                                                <?php
                                                            } else if ($db_finished[$x]['iActive'] == "Canceled" && $db_finished[$x]['fWalletDebit'] < 0) {
                                                                echo "Cancelled";
                                                                ?>
                                                            <?php }
                                                        } ?>
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
                                        <strong>Latest Rides/Jobs</strong>
                                        <?php if ($userObj->hasPermission('view-trip-jobs')) { ?>
                                            <a href="<?php echo $LOCATION_FILE_ARRAY['TRIP'];?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Latest Rides/Jobs.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if ($userObj->hasPermission('dashboard-latest-order')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <?php
                            if (scount($latest_order) > 0) { ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-head d-flex justify-space align-start">
                                            <strong>Latest Order</strong>
                                            <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                <a href="allorders.php?type=allorders" class="viewsmall">View All</a>
                                            <?php } ?>
                                        </div>
                                        <div class="common-table">
                                            <!-- Table -->
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th class="text-center">Order No</th>
                                                    <th class="text-center">User Name</th>
                                                    <th class="text-center">Order Status</th>
                                                    <th class="text-center">Date</th>
                                                    <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                    <th class="text-center">Action</th>
                                                    <?php } ?>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php for ($x = 0; $x < scount($latest_order); $x++) { ?>
                                                    <tr>
                                                        <td><?php echo $x + 1 ?></td>
                                                        <td>
                                                            <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                            <a href="allorders.php?action=search&searchOrderNo=<?php echo $latest_order[$x]['vOrderNo'] ?>">
                                                            <?php } ?>
                                                                <?php echo $latest_order[$x]['vOrderNo'] ?>
                                                            <?php if ($userObj->hasPermission('view-all-orders')) { ?>        
                                                            </a>
                                                            <?php } ?>
                                                        </td>
                                                        <td>
                                                            <span class="nowrap"><?php echo clearName($latest_order[$x]['vName'] . ' ' . $latest_order[$x]['vLastName']) ?></span>
                                                        </td>
                                                        <td>

                                                        <span class="nowrap">

                                                            <?php if ($cancelDriverOrder == "Yes" && $latest_order[$x]['eCancelledbyDriver'] == "Yes" && $latest_order[$x]['iStatusCode'] == 4) { ?>
                                                                <p><?= $langage_lbl_admin['LBL_CANCELLED_BY_DRIVER'] ?></p>
                                                                <!-- <button type="button" onclick="viewDriverCancelReason(this);" class="btn btn-info" data-reason="<?= $latest_order[$x]['vCancelReasonDriver'] ?>"><?= $langage_lbl_admin['LBL_VIEW_REASON']; ?></button> -->
                                                            <?php } else { ?>

                                                                <?= $latest_order[$x]['vStatus'] ?>
                                                                <?php if ($MODULES_OBJ->isEnableOTPVerificationDeliverAll() && $latest_order[$x]['iStatusCode'] == 5 && $latest_order[$x]['eAskCodeToUser'] == "Yes") {
                                                                    echo "<br><br>" . $langage_lbl['LBL_OTP_TXT'] . ' ' . $latest_order[$x]['vRandomCode'];
                                                                } ?>
                                                            <?php } ?>

                                                        </span>
                                                        </td>
                                                        <td>
                                                            <span class="nowrap"><?= date('jS M Y', strtotime($latest_order[$x]['tOrderRequestDate'])); ?></span>
                                                        </td>
                                                        <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                        <td class="text-center">
                                                            <?php if (in_array($latest_order[$x]['iStatusCode'], $processing_status_array)) { ?>
                                                                --
                                                            <?php } else { ?>
                                                                <?php if ($userObj->hasPermission('view-order-invoice')) { ?>
                                                                    <a class="btn btn-primary"
                                                                       href="order_invoice.php?iOrderId=<?= $latest_order[$x]['iOrderId'] ?>"
                                                                       target="_blank">
                                                                        <b>View Invoice</b>
                                                                    </a>
                                                                <?php }  ?>
                                                            <?php } ?>
                                                        </td>
                                                    <?php } ?>
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
                                            <strong>Latest Order</strong>
                                             <?php if ($userObj->hasPermission('view-all-orders')) { ?>
                                                <a href="allorders.php?type=allorders" class="viewsmall">View All</a>
                                            <?php } ?>
                                        </div>
                                        <div class="no-data-found">
                                            <strong>No Latest Order.</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>



                <?php if (($userObj->hasPermission('dashboard-contact-us-form Requests')) || ($userObj->hasPermission('dashboard-sos-requests') )) { 
                    //$userObj->hasPermission('view-contactus-report') &&  
                    // $userObj->hasPermission('view-sos-request-report') &&
                ?>
                    <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
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
                                                       // $queryString = "?action=search&searchUser=&searchDriver=&iContactusId=" . $latest_contactus[$i]['iContactusId'];
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
                    <?php if ($userObj->hasPermission('dashboard-sos-requests')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <?php if (scount($latest_sos) > 0) { ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-head d-flex justify-space align-start">
                                            <strong>SOS Requests</strong>
                                            <?php if ($userObj->hasPermission('view-sos-request-report')) { ?>
                                                <a href="emergency_contact_data.php" class="viewsmall">View All</a>
                                            <?php } ?>
                                        </div>
                                        <ul class="statlist vertical">
                                            <?php $icon_color = 1; ?>

                                            <?php if (scount($latest_sos) > 0 && !empty($latest_sos)) {
                                                for ($i = 0; $i < scount($latest_sos); $i++) {
                                                    ?>
                                                    <li>
                                                        <?php if ($userObj->hasPermission('view-sos-request-report')) { if($APP_TYPE == "UberX"){ ?>
                                                                <a href="emergency_contact_data.php?action=search&serachJobNo=<?php echo $latest_sos[$i]['vRideNo'] ?>" class="list-group-item" target="_blank">
                                                            <?php } else {?>
                                                            <a href="emergency_contact_data.php?action=search&serachTripNo=<?php echo $latest_sos[$i]['vRideNo'] ?>" class="list-group-item" target="_blank">
                                                            <?php } 
                                                        } else { ?>
                                                            <a href="#" class="list-group-item">
                                                        <?php } ?>
                                                            <?php if ($icon_color == 5) {
                                                                $icon_color = 1;
                                                            } ?>
                                                            <div>
                                                                <i class="icon-color<?= $icon_color ?> ri-notification-line"></i>
                                                                <?php if($latest_sos[$i]['vFromUserType']=='Passenger') { ?>
                                                                <div class="stat-block">
                                                                        <b><?php echo clearName($latest_sos[$i]['userName']) ?> </b>
                                                                        <span> <?= clearEmail($latest_sos[$i]['useremail']); ?></span>
                                                                        <span> <?= clearPhone($latest_sos[$i]['userphone']); ?></span>
                                                                    </div>
                                                                <?php } else if($latest_sos[$i]['vFromUserType']=='Driver') { ?>
                                                                    <div class="stat-block">
                                                                    <b><?php echo clearName($latest_sos[$i]['driverName']) ?> </b>
                                                                    <span> <?= clearEmail($latest_sos[$i]['driveremail']); ?></span>
                                                                    <span> <?= clearPhone($latest_sos[$i]['driverphone']); ?></span>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                            <small class="text-color<?= $icon_color ?> normalfont">
                                                                <?= humanReadableTimingDashboard($latest_sos[$i]['tRequestDate']); ?></small>
                                                        <?php if ($userObj->hasPermission('view-sos-request-report')) { ?>
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
                                            <strong>SOS Requests</strong>
                                            <?php if ($userObj->hasPermission('view-sos-request-report')) { ?>
                                                <a href="emergency_contact_data.php" class="viewsmall">View All</a>
                                            <?php } ?>
                                        </div>
                                        <div class="no-data-found">
                                            <strong>No SOS Requests.</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>



                <?php if (($userObj->hasPermission('dashboard-payment-requests'))|| ( $userObj->hasPermission('dashboard-notifications-alerts-panel'))) { ?>
                    <?php if ($userObj->hasPermission('dashboard-payment-requests')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <?php if (scount($latest_payment_requests) > 0) { ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-head d-flex justify-space align-start">
                                            <strong>Payment Requests</strong>
                                            <?php if ($userObj->hasPermission('view-payment-request-report')) { ?>
                                                <a href="payment_requests_report.php" class="viewsmall">View All</a>
                                            <?php } ?>
                                        </div>
                                        <ul class="statlist vertical">
                                            <?php $icon_color = 1; ?>

                                            <?php if (scount($latest_payment_requests) > 0 && !empty($latest_payment_requests)) {
                                                for ($i = 0; $i < scount($latest_payment_requests); $i++) {
                                                    ?>
                                                    <li>
                                                        <!-- /* ------------------------- @todo payment vertical ------------------------- */ -->
                                                        <?php $startDate = date("Y-m-d", strtotime($latest_payment_requests[$i]['tRequestDate'])); ?>

                                                        <?php $endDate = date("Y-m-d"); ?>
                                                        <?php if ($userObj->hasPermission('view-payment-request-report')) { ?>
                                                            <a href="payment_requests_report.php?action=search&serachTripNo=<?php echo $latest_payment_requests[$i]['vRideNo'] ?>"
                                                           class="list-group-item" target="_blank">
                                                        <?php } else { ?>
                                                            <a href="#" class="list-group-item">
                                                        <?php } ?>
                                                            <?php if ($icon_color == 5) {
                                                                $icon_color = 1;
                                                            } ?>
                                                            <div>
                                                                <i class="icon-color<?= $icon_color ?> ri-notification-line"></i>
                                                                <div class="stat-block">
                                                                    <b><?php if(!empty($latest_payment_requests[$i]['vRideNo'])){ 
                                                                            echo clearName($latest_payment_requests[$i]['firstname'] . ' ' . $latest_payment_requests[$i]['lastname']);
                                                                        } else {
                                                                            echo clearName($latest_payment_requests[$i]['user_name'] . ' ' . $latest_payment_requests[$i]['user_lastname']);
                                                                        } ?> </b>
                                                                    <span> <? 
                                                                    if(!empty($latest_payment_requests[$i]['vRideNo'])){  
                                                                            echo clearEmail($latest_payment_requests[$i]['driveremail']); 
                                                                    } else {
                                                                            echo clearEmail($latest_payment_requests[$i]['usermail']); 
                                                                    } ?></span>
                                                                  <!--   <span> <?= clearPhone($latest_payment_requests[$i]['vPhone']); ?></span> -->
                                                                </div>
                                                            </div>
                                                            <small class="text-color<?= $icon_color ?> normalfont">
                                                                <?= humanReadableTimingDashboard($latest_payment_requests[$i]['tRequestDate']); ?>
                                                            </small>
                                                        </a>
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
                                            <strong>Payment Requests</strong>
                                            <a href="payment_requests_report.php" class="viewsmall">View All</a>
                                        </div>
                                        <div class="no-data-found">
                                            <strong>No Payment Requests.</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if ($userObj->hasPermission('dashboard-notifications-alerts-panel')) { ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                            <?php if (scount($db_notification) > 0 && !empty($db_notification)) {
                                ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-head d-flex justify-space align-start">
                                            <strong>Notifications Alerts
                                                Panel
                                            </strong>
                                            <?php if ($userObj->hasPermission('dashboard-notifications-alerts-panel')) { ?>
                                                <a href="notificationlist.php" class="viewsmall">View All</a>
                                            <?php } ?>
                                        </div>
                                        <ul class="statlist vertical">
                                            <?php $icon_color = 1; ?>

                                            <?php if (scount($db_notification) > 0 && !empty($db_notification)) {
                                                for ($i = 0; $i < scount($db_notification); $i++) {
                                                    if ($db_notification[$i]['doc_name_' . $default_lang] != '') {
                                                        ?>
                                                        <li>
                                                            <!-- // icon-color2

                                                                // icon-color3

                                                                // icon-color4 -->
                                                            <?php if ($icon_color == 5) {
                                                                $icon_color = 1;
                                                            } ?>

                                                            <?php
                                                            $url = "#";
                                                            if ($db_notification[$i]['doc_usertype'] == 'driver') {
                                                                $url = $LOCATION_FILE_ARRAY['DRIVER_DOCUMENT_ACTION'];
                                                                $viewpermission = "view-providers";
                                                                $id = $db_notification[$i]['iDriverId'];
                                                                if ($db_notification[$i]['doc_name_' . $default_lang] != '') {
                                                                    $msg = strtoupper($db_notification[$i]['doc_name_' . $default_lang]) . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                                    $name = clearName($db_notification[$i]['Driver']);
                                                                } else {
                                                                    $msg = $db_notification[$i]['doc_name_' . $default_lang] . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                                    $name = clearName($db_notification[$i]['Driver']);
                                                                }
                                                            } else if ($db_notification[$i]['doc_usertype'] == 'company') {
                                                                $url = "company_document_action.php";
                                                                $viewpermission = "view-company";
                                                                $id = $db_notification[$i]['iCompanyId'];
                                                                if ($db_notification[$i]['doc_name_' . $default_lang] != '') {
                                                                    $msg = strtoupper($db_notification[$i]['doc_name_' . $default_lang]) . " uploaded by " . $db_notification[$i]['doc_usertype'] . "";
                                                                    $name = clearCmpName($db_notification[$i]['vCompany']);
                                                                } else {
                                                                    $msg = $db_notification[$i]['doc_name_' . $default_lang] . " uploaded by " . $db_notification[$i]['doc_usertype'] . "";
                                                                    $name = clearCmpName($db_notification[$i]['vCompany']);
                                                                }
                                                            } else if ($db_notification[$i]['doc_usertype'] == 'car') {
                                                                $url = "vehicle_document_action.php";
                                                                $viewpermission = "edit-provider-vehicles-document";
                                                                $id = $db_notification[$i]['iDriverVehicleId'];
                                                                if ($db_notification[$i]['doc_name_' . $default_lang] != '') {
                                                                    $msg = strtoupper($db_notification[$i]['doc_name_' . $default_lang]) . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                                    $name = clearName($db_notification[$i]['DriverName']);
                                                                } else {
                                                                    $msg = $db_notification[$i]['doc_name_' . $default_lang] . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                                    $name = clearName($db_notification[$i]['DriverName']);
                                                                }
                                                            } else if ($db_notification[$i]['doc_usertype'] == 'store') {
                                                                $url = "store_document_action.php";
                                                                $viewpermission = "edit-store";
                                                                $id = $db_notification[$i]['iCompanyId'];
                                                                if ($db_notification[$i]['doc_name_' . $default_lang] != '') {
                                                                    $msg = strtoupper($db_notification[$i]['doc_name_' . $default_lang]) . " uploaded by " . $db_notification[$i]['doc_usertype'] . "";
                                                                    $name = clearCmpName($db_notification[$i]['vCompany']);
                                                                } else {
                                                                    $msg = $db_notification[$i]['doc_name_' . $default_lang] . " uploaded by " . $db_notification[$i]['doc_usertype'] . "";
                                                                    $name = clearCmpName($db_notification[$i]['vCompany']);
                                                                }
                                                            }
                                                            ?>
                                                            <?php if($userObj->hasPermission($viewpermission)) { ?>
                                                                <a href="<?= $url; ?>?id=<? echo $id; ?>&action=edit" class="list-group-item" target="_blank">
                                                            <?php } else { ?>
                                                                <a href="javascript:void(0)" class="list-group-item">
                                                            <?php } ?>
                                                                <div>
                                                                    <i class="icon-color<?= $icon_color ?> ri-notification-line"></i>
                                                                    <div class="stat-block">
                                                                        <b><?php echo $name; ?> </b>
                                                                        <span> <?= $msg; ?></span>
                                                                    </div>
                                                                </div>
                                                                <small class="text-color<?= $icon_color ?> normalfont">
                                                                    <?= humanReadableTimingDashboard($db_notification[$i]['edate']); ?>
                                                                </small>
                                                                </a>
                                                            
                                                        </li>
                                                        <?php $icon_color = $icon_color + 1 ?>
                                                    <?php }
                                                }
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="card d-flex" <?php echo $style3; ?>>
                                    <div class="card-body">
                                        <div class="d-flex justify-space align-start">
                                            <strong>Notifications Alerts</strong>
                                        </div>
                                        <div class="no-data-found">
                                            <strong>No Notifications Alerts.</strong>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php if ($userObj->hasPermission('userproviderstore-dashboard')) { 
                //&& (($userObj->hasPermission('view-users') && $userObj->hasPermission('view-providers')) || $userObj->hasPermission('view-store') )
            ?>
                <div class="row clearfix d-flex">
                    <div class="col-sm-12 formob767">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head d-flex justify-space align-start">
                                    <strong>
                                        <?php
                                        if (in_array($APP_TYPE, [
                                                'Ride',
                                                'UberX'
                                            ]) || (!$userObj->hasPermission('view-store') && !$MODULES_OBJ->isDeliverAllFeatureAvailable('Yes'))) {
                                            echo $langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN'] . '/' . $langage_lbl_admin['LBL_DASHBOARD_DRIVERS_ADMIN'];
                                        } else {
                                            echo $langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN'] . '/' . $langage_lbl_admin['LBL_DASHBOARD_DRIVERS_ADMIN'] . '/' . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];
                                        }
                                        ?>
                                    </strong>
                                    <div class="combo-element">
                                        <label>Year:</label>
                                        <select class="gen-custom-select"
                                                onchange="getyear(this,'user_and_provider',11);" name="year">
                                            <?php for ($i = date('Y'); $i >= $dSetupYear; $i--) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <img id="chart13_loader" class="chart_loader" src="<?php echo $chartLoader; ?>">
                                <div id="chart13" <?php echo $style1; ?> ></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php } else { ?>
            <div class="row clearfix d-flex">
                <div class="col-sm-12">
                    <div class="card d-flex">
                        <div class="card-body">
                            <div class="no-data-found other-admin">
                                <strong><?= $_SESSION['SessionUserType'] == 'hotel' ? $langage_lbl['LBL_WELCOME_TO'] . ' ' . $langage_lbl['LBL_HOTEL_LOGIN'] . ' ' . $langage_lbl['LBL_PANEL_TXT'] : $langage_lbl_admin['LBL_WELCOME_TO'] . ' ' . $langage_lbl_admin['LBL_ADMIN'] . ' ' . $langage_lbl_admin['LBL_PANEL_TXT'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php include_once('footer.php'); ?>
        <!--END PAGE CONTENT -->
    </div>
    <?php for ($x = 0; $x < scount($db_finished); $x++) { ?>
        <div class="modal fade" id="uiModal1_<?= $db_finished[$x]['iTripId']; ?>" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-content image-upload-1" style="width:400px;">
                <div class="upload-content" style="width:350px;">
                    <h3><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Cancel Reason</h3>
                    <h4>Cancel Reason:
                        <b style="font-size: 15px;font-weight: normal;">
                            <?php
                            if ($db_finished[$x]['iCancelReasonId'] > 0) {
                                $cancelreasonarray = getCancelReason($db_finished[$x]['iCancelReasonId'], $default_lang);
                                $db_finished[$x]['vCancelReason'] = $cancelreasonarray['vCancelReason'];
                            }
                            ?>

                            <?= stripcslashes($db_finished[$x]['vCancelReason']); ?></b>
                    </h4>
                    <?php if (!empty($db_finished[$x]['eCancelledBy'])) {
                        $eCancelledBy = $langage_lbl_admin['LBL_ADMIN'];
                        if ($db_finished[$x]['eCancelledBy'] == "Passenger") {
                            $eCancelledBy = $langage_lbl_admin['LBL_RIDER'];
                        } else if ($db_finished[$x]['eCancelledBy'] == "Driver") {
                            $eCancelledBy = $langage_lbl_admin['LBL_DRIVER'];
                        } ?>
                        <h4>Cancel By:
                            <b style="font-size: 15px;font-weight: normal;"><?= stripcslashes($eCancelledBy); ?></b>
                        </h4>
                    <?php } else { ?>
                        <h4>
                            <b style="font-size: 15px;font-weight: normal;"></b>
                        </h4>
                    <?php } ?>
                    <input type="button" class="save" data-dismiss="modal" name="cancel" value="Close">
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if ($viewService == 1) { ?>
        <div class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"
                         style="float: left; width: 100%">
                        <h4 id="servicetitle" class="pull-left">
                            <i style="margin:2px 5px 0 2px;">
                                <img src="images/icon/driver-icon.png"
                                     alt="">
                            </i>
                            Service Details
                        </h4>
                        <button type="button" class="close pull-right"
                                data-dismiss="modal">x
                        </button>
                    </div>
                    <div class="modal-body"
                         style="max-height: 450px;overflow: auto;">
                        <div id="service_detail"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if (isset($_SESSION['sess_iGroupId']) && $_SESSION['sess_iGroupId'] != 4) {
        ?>
        <script>
            /* --------------------------- for last five days --------------------------- */
            var ridetotaldate = <?php echo json_encode($ridetotaldate); ?>;
            var rideTotalbydate = <?php echo json_encode($rideTotalbydate); ?>;

            var ordertotaldate = <?php echo json_encode($ordertotaldate); ?>;
            var orderTotalbydate = <?php echo json_encode($orderTotalbydate); ?>;

            var server_status = <?php echo json_encode($server_status); ?>;
            var server_number = <?php echo json_encode($server_number); ?>;
            /* --------------------------- for last five days --------------------------- */

            /* --------------------------------- server_status_chart -------------------------------- */
            $(document).ready(function() {
                setTimeout(function() {
                    getyear('', 'server_status_chart', '');

                    /* --------------------------------- server_status_chart -------------------------------- */
                    /* ------------------------------- year months order and ride ------------------------------ */

                    getyear('', 'Total_Ride_jobs', 5);


                    getyear('', 'Total_Order', 5);

                    /* ------------------------------- year months order and ride ------------------------------ */


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



                    /* ---------------------------- user and provider --------------------------- */

                    var months = [];
                    var user = [];
                    var provider = [];
         
                    getyear('', 'user_and_provider', '');
                }, 5000);
            });

            /* ---------------------------- user and provider --------------------------- */
            function getyear(year, chart_type, getMonth) {
                var curr_elem = year;
                $(curr_elem).closest('.card-body').find('.chart_loader').show();
                var ridetotaldate = [];
                var rideTotalbydate = [];
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

                if (chart_type == 'Total_Ride_jobs') {
                    $("#chart3").html('');
                }

                if (chart_type == 'Total_Order') {
                    $("#chart4").html('');
                }

                if (chart_type == 'user_and_provider') {
                    $("#chart13").html('');
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

                        if (chart_type == 'Total_Ride_jobs') {
                            month = response.data.month;
                            cancelledRidetotalByMonth = response.data.cancelledRidetotalByMonth;
                            finishRidetotalByMonth = response.data.finishRidetotalByMonth;
                            $("#chart3_loader").hide();
                            reShowYearChangeRideJobs(month, cancelledRidetotalByMonth, finishRidetotalByMonth);
                        }

                        if (chart_type == 'Total_Order') {

                            order_month = response.data.order_month;
                            cancelledOrdertotalByMonth = response.data.cancelledOrdertotalByMonth;
                            finishOrdertotalByMonth = response.data.finishOrdertotalByMonth;
                            $("#chart4_loader").hide();
                            reShowYearChangeOrder(order_month, cancelledOrdertotalByMonth, finishOrdertotalByMonth);
                        }

                        if (chart_type == 'user_and_provider') {
                            months = response.data.months;
                            user = response.data.user;
                            provider = response.data.provider;
                            store = response.data.store;
                            $("#chart13_loader").hide();
                            <?php if(in_array($APP_TYPE, ['Ride', 'UberX'])) { ?>
                            reShowUserProvider(months, user, provider);
                            <?php } else { ?>
                            reShowUserProvider(months, user, provider, store);
                            <?php } ?>
                        }
                    }
                    else {
                        // console.log(res.result);
                    }
                });
            }

            var rides = {
                series: [{
                    name: '<?= $langage_lbl_admin["LBL_TOTAL_RIDES_ADMIN"] ?>',
                    data: rideTotalbydate
                }],

                chart: {
                    height: 100,
                    type: 'line',
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
                    width: 3
                },
                grid: {
                    show: false,
                },
                markers: {
                    size: 2,
                    colors: ['#00cfe8'],
                    strokeColors: ['#00cfe8'],
                    strokeWidth: 2,
                    strokeOpacity: 1,
                    strokeDashArray: 0,
                    fillOpacity: 1,
                    discrete: [{
                        seriesIndex: 0,
                        dataPointIndex: 5,
                        fillColor: "#ffffff",
                        strokeColor: ['#00cfe8'],
                        size: 5
                    }],
                    shape: "circle",
                    radius: 2,
                    hover: {
                        size: 3
                    }
                },
                xaxis: {
                    show: false,
                    labels: {
                        show: false,
                        style: {
                            fontSize: "0px",
                        }
                    },
                    categories: ridetotaldate,
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    show: false,
                    labels: {
                        show: false,
                        style: {
                            fontSize: "0px",
                        }
                    },
                },
                legend: {
                    show: false
                },
                colors: ['#00cfe8'],
            };

            var orders = {

                series: [{
                    name: 'Total Order',
                    data: orderTotalbydate
                }],

                chart: {
                    height: 100,
                    type: 'line',
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
                    width: 3,
                },

                grid: {
                    show: false,
                },
                markers: {
                    size: 2,
                    colors: ['#28c76f'],
                    strokeColors: ['#28c76f'],
                    strokeWidth: 2,
                    strokeOpacity: 1,
                    strokeDashArray: 0,
                    fillOpacity: 1,
                    discrete: [{
                        seriesIndex: 0,
                        dataPointIndex: 5,
                        fillColor: "#ffffff",
                        strokeColor: ['#28c76f'],
                        size: 5
                    }],
                    shape: "circle",
                    radius: 2,
                    hover: {
                        size: 3
                    }
                },
                xaxis: {
                    show: false,
                    labels: {
                        show: false,
                        style: {
                            fontSize: "0px",
                        }
                    },
                    categories: ordertotaldate,
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                    padding: {
                        top: -30,
                        bottom: -10
                    }
                },
                yaxis: {
                    show: false,
                    labels: {
                        show: false,
                        style: {
                            fontSize: "0px",
                        }
                    },
                },
                legend: {
                    show: false
                },
                colors: ['#28c76f'],
            };

            var ridepiechart = {
                series: [<?= $cancelRides; ?>, <?= $finishRides; ?>, <?= $onRides; ?>],
                labels: ["<?= $langage_lbl_admin["LBL_CANCELLED_RIDES_ADMIN"] ?>", "<?= $langage_lbl_admin["LBL_COMPLETED_RIDES_ADMIN"] ?>", "<?= $langage_lbl_admin["LBL_ON_RIDES_ADMIN"] ?>"],
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
                    colors: ['#ea5455', '#28c76f', '#ff9900']
                },
                legend: {
                    show: true,

                    markers: {
                        fillColors: ['#ea5455', '#28c76f', '#ff9900'],
                    },
                },
                tooltip: {
                    colors: ['#ea5455', '#28c76f', '#ff9900']
                },
                colors: ['#ea5455', '#28c76f', '#ff9900']
            };

            var orderchart = {
                series: [<?= $store_cancelRides; ?>, <?= $store_finishRides; ?>, <?= $store_onRides; ?>],
                labels: ["Cancelled Orders", "Completed Orders", "Processing Orders"],
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
                    colors: ['#ea5455', '#28c76f', '#ff9900']
                },
                legend: {
                    show: true,

                    markers: {
                        fillColors: ['#ea5455', '#28c76f', '#ff9900'],
                    },
                },
                tooltip: {
                    colors: ['#ea5455', '#28c76f', '#ff9900']
                },
                colors: ['#ea5455', '#28c76f', '#ff9900']
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
                    //console.log(orders);

                    var chart = new ApexCharts(document.querySelector("#chart"), rides);
                    chart.render();
                    $("#chart0_loader").hide();

                    var chart2 = new ApexCharts(document.querySelector("#chart2"), orders);
                    chart2.render();
                    $("#chart2_loader").hide();
                    var chart11 = new ApexCharts(document.querySelector("#ridepiechart"), ridepiechart);
                    chart11.render();
                    $("#ridepiechart_loader").hide();
                    var chart7 = new ApexCharts(document.querySelector("#orderchart"), orderchart);
                    chart7.render();
                    $("#orderchart_loader").hide();
                    var chart12 = new ApexCharts(document.querySelector("#serverStatuschart"), serverStatuschart);
                    chart12.render();
                    $("#serverStatuschart_loader").hide();

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

            function reShowYearChangeRideJobs(month, cancelledRidetotalByMonth, finishRidetotalByMonth) {
                var job_order_year = {
                    series: [{
                        name: 'Completed',
                        data: finishRidetotalByMonth
                    }, {
                        name: 'Cancelled',
                        data: cancelledRidetotalByMonth
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
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
                        categories: month,
                    },
                    yaxis: {
                        title: {
                            text: 'Total'
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        markers: {
                            fillColors: ['#00cfe8', '#EA5455'],
                        },
                    },
                    fill: {
                        opacity: 1,
                        colors: ['#00cfe8', '#EA5455'],
                    },
                };
                $("#chart3").html('');
                var chart3 = new ApexCharts(document.querySelector("#chart3"), job_order_year);
                chart3.render();
            }

            function reShowYearChangeOrder(order_month, cancelledOrdertotalByMonth, finishOrdertotalByMonth) {

                var order_year = {
                    series: [{
                        name: 'Completed',
                        data: finishOrdertotalByMonth
                    }, {
                        name: 'Cancelled',
                        data: cancelledOrdertotalByMonth
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
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
                        categories: order_month,
                    },
                    yaxis: {
                        title: {
                            text: 'Total'
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        markers: {
                            fillColors: ['#00cfe8', '#EA5455'],
                        },
                    },
                    fill: {
                        opacity: 1,
                        colors: ['#00cfe8', '#EA5455'],
                    },
                };
                $("#chart4").html('');
                var chart4 = new ApexCharts(document.querySelector("#chart4"), order_year);
                chart4.render();
            }

            <?php if(in_array($APP_TYPE, ['Ride', 'UberX']) || (!$userObj->hasPermission('view-store') && !$MODULES_OBJ->isDeliverAllFeatureAvailable('Yes'))) { ?>
            function reShowUserProvider(months, user, provider) {
                var UserProvider = {
                    series: [{
                        name: '<?= addslashes($langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN']); ?>',
                        data: user
                    }, {
                        name: '<?= addslashes($langage_lbl_admin['LBL_DASHBOARD_DRIVERS_ADMIN']); ?>',
                        data: provider
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
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
                        categories: months,
                    },
                    yaxis: {
                        title: {
                            text: 'Total'
                        },
                        labels: {
                            formatter: function (val) {
                                return val.toFixed(0);
                            }
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        markers: {
                            fillColors: ['#7367f0', '#00cfe8'],
                        },
                    },
                    fill: {
                        opacity: 1,
                        colors: ['#7367f0', '#00cfe8'],
                    },
                };
                $("#chart13").html('');
                var chart13 = new ApexCharts(document.querySelector("#chart13"), UserProvider);
                chart13.render();
            }
            <?php } else { ?>
            function reShowUserProvider(months, user, provider, store) {
                var UserProvider = {
                    series: [{
                        name: '<?= addslashes($langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN']); ?>',
                        data: user
                    }, {
                        name: '<?= addslashes($langage_lbl_admin['LBL_DASHBOARD_DRIVERS_ADMIN']); ?>',
                        data: provider
                    }, {
                        name: '<?= addslashes($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);?>',
                        data: store
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
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
                        categories: months,
                    },
                    yaxis: {
                        title: {
                            text: 'Total'
                        },
                        labels: {
                            formatter: function (val) {
                                return val.toFixed(0);
                            }
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        markers: {
                            fillColors: ['#7367f0', '#00cfe8', '#28c76f'],
                        },
                    },
                    fill: {
                        opacity: 1,
                        colors: ['#7367f0', '#00cfe8', '#28c76f'],
                    },
                };
                $("#chart13").html('');
                var chart13 = new ApexCharts(document.querySelector("#chart13"), UserProvider);
                chart13.render();
            }
            <?php } ?>

            function showServiceModal() {
                var typeArr = '<?= getJsonFromAnArr($vehicleTypeArr); ?>';

                var tripJson = JSON.parse($('#serviceArr').text().replace(/\s\s+/g, ' '));
                var rideNo = $('#booking_no').data("bookingno");
                var typeNameArr = JSON.parse(typeArr)
                var serviceHtml = "<ul class='dashboard-view-services'>";
                var srno = 1;

                for (var g = 0; g < tripJson.length; g++) {
                    serviceHtml += "<li>" + srno + ") " + typeNameArr[tripJson[g]['id']] + " (" + typeNameArr[tripJson[g]['id'] + "_service"] + " - " + typeNameArr[tripJson[g]['id'] + "_subService"] + ")<br>";
                    if (tripJson[g]['eAllowQty'] == 'Yes') {
                        serviceHtml += "<?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>" + [tripJson[g]['qty']] + "</b>";
                    }
                    serviceHtml += "</li>";
                    srno++;
                }
                serviceHtml += "</ul>";
                $("#service_detail").html(serviceHtml);
                $("#servicetitle").text("Service Details : " + rideNo);
                $("#service_modal").modal('show');
                return false;
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
    <?php } ?>
</body>
</html>