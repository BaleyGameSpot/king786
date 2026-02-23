<?php
include_once('../common.php');
// Verifica se é um admin do tipo franquia
$isFranchiseAdmin = ($_SESSION['sess_iGroupId'] == 6); // 6 é o ID do grupo de franqueados

// Filtro de localização para franquia
$sql1 = ""; // Esta variável já é usada em várias queries mais abaixo

if ($isFranchiseAdmin) {
    $adminId = $_SESSION['sess_iAdminUserId']; // ID do admin logado
    $locationData = $obj->MySQLSelect("SELECT location_id FROM admin_locations WHERE admin_id = '$adminId'");

    if (!empty($locationData) && !empty($locationData[0]['location_id'])) {
        $locationId = $locationData[0]['location_id'];
        $sql1 .= " AND iLocationId = '" . $locationId . "'";
    } else {
        // Se não houver localização, evitar mostrar dados
        $sql1 .= " AND 0=1";
    }
}

$serverTimeZone = date_default_timezone_get();
[$SHOW_DASHBOARD, $PERMISSIONS_COUNT, $GROUP_DATA]  = checkPermissionForOtherAdmin();
$isRideFeatureAvailable = $MODULES_OBJ->isRideFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-ride-job-statistics');
$isUberXFeatureAvailable = $MODULES_OBJ->isUberXFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-ride-job-statistics');
$isDeliveryFeatureAvailable = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-ride-job-statistics');
$isRideFeatureAvailableForRecentTrip = $MODULES_OBJ->isRideFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-latest-ride-job');
$isUberXFeatureAvailableForRecentTrip = $MODULES_OBJ->isUberXFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-latest-ride-job');
$isDeliveryFeatureAvailableForRecentTrip = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-latest-ride-job');
$isEnableAnywhereDeliveryFeature = $MODULES_OBJ->isEnableAnywhereDeliveryFeature('Yes') && $userObj->hasPermission('dashboard-delivery-genie-runner');
$isDeliverAllFeatureAvailable = $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') && $userObj->hasPermission('dashboard-store-deliveries');
$isEnableVideoConsultingServices = $MODULES_OBJ->isEnableVideoConsultingService('Yes') && $userObj->hasPermission('dashboard-video-consultation');
$isEnableBiddingServices = $MODULES_OBJ->isEnableBiddingServices('Yes') && $userObj->hasPermission('dashboard-bid-services');
$isEnableRentItemService = $MODULES_OBJ->isEnableRentItemService('Yes') && $userObj->hasPermission('dashboard-buy-sell-rent');
$isEnableRentCarsService = $MODULES_OBJ->isEnableRentCarsService('Yes') && $userObj->hasPermission('dashboard-buy-sell-rent');
$isEnableRentEstateService = $MODULES_OBJ->isEnableRentEstateService('Yes') && $userObj->hasPermission('dashboard-buy-sell-rent');
$isEnableMedicalServices = $MODULES_OBJ->isEnableMedicalServices('Yes');
$isEnableRideShareService = $MODULES_OBJ->isEnableRideShareService('Yes') && $userObj->hasPermission('dashboard-ride-share');
$isEnableTrackServiceFeature = $MODULES_OBJ->isEnableTrackServiceFeature('Yes');
$isEnableTrackAnyServiceFeature = $MODULES_OBJ->isEnableTrackAnyServiceFeature('Yes');
$isAirFlightModuleAvailable = $MODULES_OBJ->isAirFlightModuleAvailable('Yes');
$isEnableParkingFeature = $MODULES_OBJ->isEnableParkingFeature('Yes');
$isEnableTaxiBidFeature = $MODULES_OBJ->isEnableTaxiBidFeature('Yes');
$isEnableNearByService = $MODULES_OBJ->isEnableNearByService('Yes');
$isEnableGenieFeature = $MODULES_OBJ->isEnableGenieFeature('Yes') && $userObj->hasPermission('dashboard-delivery-genie-runner');
$isEnableRunnerFeature = $MODULES_OBJ->isEnableRunnerFeature('Yes') && $userObj->hasPermission('dashboard-delivery-genie-runner');
$isInterCityFeatureAvailable = $MODULES_OBJ->isInterCityFeatureAvailable();
$isEnableScheduledRideFlow = ($RIDE_LATER_BOOKING_ENABLED == 'Yes') && $userObj->hasPermission('later-bookings-dashboard');
$isEnableAdminEarningDashboard = $userObj->hasPermission('admin-earning-dashboard');
$isEnableServerSection = $userObj->hasPermission('manage-server-admin-dashboard');
$isShowDashboardGodView = $userObj->hasPermission('dashboard-god-view');
$IS_ENABLE_MASTER_SERVICES = [];
$IS_ENABLE_MASTER_SERVICES = ['isRideFeatureAvailable'          => $isRideFeatureAvailable,
                              'isUberXFeatureAvailable'         => $isUberXFeatureAvailable,
                              'isDeliveryFeatureAvailable'      => $isDeliveryFeatureAvailable,
                              'isEnableAnywhereDeliveryFeature' => $isEnableAnywhereDeliveryFeature,
                              'isDeliverAllFeatureAvailable'    => $isDeliverAllFeatureAvailable,
                              'isEnableVideoConsultingServices' => $isEnableVideoConsultingServices,
                              'isEnableBiddingServices'         => $isEnableBiddingServices,
                              'isEnableRentItemService'         => $isEnableRentItemService,
                              'isEnableRentCarsService'         => $isEnableRentCarsService,
                              'isEnableRentEstateService'       => $isEnableRentEstateService,
                              'isEnableMedicalServices'         => $isEnableMedicalServices,
                              'isEnableRideShareService'        => $isEnableRideShareService,
                              'isEnableTrackServiceFeature'     => $isEnableTrackServiceFeature,
                              'isEnableTrackAnyServiceFeature'  => $isEnableTrackAnyServiceFeature,
                              'isAirFlightModuleAvailable'      => $isAirFlightModuleAvailable,
                              'isEnableParkingFeature'          => $isEnableParkingFeature,
                              'isEnableTaxiBidFeature'          => $isEnableTaxiBidFeature,
                              'isEnableNearByService'           => $isEnableNearByService,
                              'isEnableGenieFeature'            => $isEnableGenieFeature,
                              'isEnableRunnerFeature'           => $isEnableRunnerFeature,
                              'isInterCityFeatureAvailable'     => $isInterCityFeatureAvailable,
                              'isEnableScheduledRideFlow'       => $isEnableScheduledRideFlow,
                              'isEnableAdminEarningDashboard'   => $isEnableAdminEarningDashboard,
                              'isEnableServerSection'           => $isEnableServerSection,
                              'isShowDashboardGodView'          => $isShowDashboardGodView];
if (ONLY_MEDICAL_SERVICE == "Yes"){
    $TRIP_TEXT_FOR_ONLY_MEDICAL_SERVICE = $langage_lbl_admin['LBL_TEXI_ADMIN']." Services";
}
$TODAY = " ";
$script = "dashboard";
$date = date("Y-m-d");
$TODAY_LINK = "&startDate=".$date."&endDate=".$date;
//$sql1 = " AND DATE(tTripRequestDate) = '".$date."'";
$serverTimeZone = date_default_timezone_get();
$sql1 = " AND DATE(CONVERT_TZ(tTripRequestDate, '".$serverTimeZone."', vTimeZone)) = '".$date."'";
$FINAL_SQL = "SELECT
    (SELECT COUNT(iUserId) FROM `register_user` WHERE eStatus != 'Deleted' AND eHail = 'No') as total_users,
    (SELECT COUNT(iDriverId) FROM `register_driver` WHERE eStatus != 'Deleted' AND iTrackServiceCompanyId = 0) as total_providers,
    (SELECT COUNT(iDriverId) FROM `register_driver` WHERE eStatus = 'active' AND iTrackServiceCompanyId = 0) as total_active_providers,
    (SELECT COUNT(iDriverId) FROM `register_driver` WHERE eStatus = 'inactive' AND iTrackServiceCompanyId = 0) as total_inactive_providers,

    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND eType = 'Ride' AND eSystem = 'General') as total_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Active', 'Arrived', 'On Going Trip', 'Inactive') AND eType = 'Ride' AND eSystem = 'General') as total_inprocess_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Canceled') AND eType = 'Ride' AND eSystem = 'General') as total_canceled_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Finished') AND eType = 'Ride' AND eSystem = 'General') as total_finished_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND eType = 'UberX' AND eSystem = 'General') as total_jobs,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Active', 'Arrived', 'On Going Trip') AND eType = 'UberX' AND eSystem = 'General') as total_inprocess_jobs,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND ( iActive IN ('Canceled') OR  (iActive IN ('Finished')  AND eCancelled = 'Yes' ) )  AND eType = 'UberX' AND eSystem = 'General') as total_canceled_jobs,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Finished') AND eCancelled = 'No' AND eType = 'UberX' AND eSystem = 'General') as total_finished_jobs";
if ($MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')){
    $FINAL_SQL .= ", (SELECT COUNT(iCompanyId) FROM `company` WHERE eStatus != 'Deleted' AND eSystem = 'DeliverAll' AND eBuyAnyService = 'No' AND iServiceId IN ($enablesevicescategory)) as total_stores";
}
if ($MODULES_OBJ->isDeliveryFeatureAvailable('Yes')){
    $FINAL_SQL .= ",
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Active', 'Arrived', 'On Going Trip') AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_inprocess_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Canceled') AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_canceled_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Finished') AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_finished_deliveries";
}
$systemStats = $obj->MySQLSelect($FINAL_SQL);
/*------------------Total-----------------*/
$sql1 = '';
$FINAL_SQL = "SELECT
    (SELECT COUNT(iUserId) FROM `register_user` WHERE eStatus != 'Deleted') as total_users,
    (SELECT COUNT(iDriverId) FROM `register_driver` WHERE eStatus != 'Deleted') as total_providers,
    (SELECT COUNT(iCompanyId) FROM `company` WHERE eStatus != 'Deleted' AND iServiceId > 0) as total_stores,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND eType = 'Ride' AND eSystem = 'General') as total_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('On Going Trip' ,'Arrived','Active' ) AND eType = 'Ride' AND eSystem = 'General') as total_inprocess_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND ( iActive IN ('Canceled') OR  (iActive IN ('Finished')  AND eCancelled = 'Yes')) AND eType = 'Ride' AND eSystem = 'General') as total_canceled_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Finished') AND eCancelled = 'No' AND eType = 'Ride' AND eSystem = 'General') as total_finished_rides,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('On Going Trip' ,'Arrived','Active' ) AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_inprocess_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND ( iActive IN ('Canceled') OR  (iActive IN ('Finished')  AND eCancelled = 'Yes')) AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_canceled_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Finished') AND eCancelled = 'No' AND eType = 'Multi-Delivery' AND eSystem = 'General') as total_finished_deliveries,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND eType = 'UberX' AND eSystem = 'General') as total_jobs,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('On Going Trip' ,'Arrived','Active' ) AND eType = 'UberX' AND eSystem = 'General') as total_inprocess_jobs,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND ( iActive IN ('Canceled') OR  (iActive IN ('Finished')  AND eCancelled = 'Yes'))  AND eType = 'UberX' AND eSystem = 'General') as total_canceled_jobs,
    (SELECT COUNT(iTripId) FROM `trips` WHERE 1 = 1 {$sql1} AND iActive IN ('Finished') AND eCancelled = 'No' AND eType = 'UberX' AND eSystem = 'General') as total_finished_jobs";
$systemStatsTotal = $obj->MySQLSelect($FINAL_SQL);
$memberStatsArr = array();
$memberStatsArr[] = array('vImage' => "user.svg",
                          'vTitle' => $langage_lbl_admin['LBL_USER'],
                          /* 'Total'  => abbreviateNumber($systemStats[0]['total_users']),*/
                          'Total'  => $systemStats[0]['total_users'] > 0?abbreviateNumber($systemStats[0]['total_users']):$systemStats[0]['total_users'],
                          'Link'   => $LOCATION_FILE_ARRAY['RIDER.PHP']);
if (in_array($APP_TYPE,['Ride','Ride-Delivery']) || (in_array($APP_TYPE,['UberX']) && !empty($parent_ufx_catid))){
    $memberStatsArr[] = array('vImage' => "provider.svg",
                              'vTitle' => "Active ".$langage_lbl_admin['LBL_PROVIDER'],
                              'Total'  => abbreviateNumber($systemStats[0]['total_active_providers']),
                              'Total'  => $systemStats[0]['total_active_providers'] > 0?abbreviateNumber($systemStats[0]['total_active_providers']):$systemStats[0]['total_active_providers'],
                              'Link'   => $LOCATION_FILE_ARRAY['DRIVER.PHP']."?eStatus=Active");
    $memberStatsArr[] = array('vImage' => "provider.svg",
                              'vTitle' => "Inactive ".$langage_lbl_admin['LBL_PROVIDER'],
                              'Total'  => $systemStats[0]['total_inactive_providers'] > 0?abbreviateNumber($systemStats[0]['total_inactive_providers']):$systemStats[0]['total_inactive_providers'],
                              'Link'   => $LOCATION_FILE_ARRAY['DRIVER.PHP']."?eStatus=Inactive");
}else{
    $memberStatsArr[] = array('vImage' => "provider.svg",
                              'vTitle' => $langage_lbl_admin['LBL_PROVIDER'],
                              'Total'  => $systemStats[0]['total_providers'] > 0?abbreviateNumber($systemStats[0]['total_providers']):$systemStats[0]['total_providers'],
                              'Link'   => $LOCATION_FILE_ARRAY['DRIVER.PHP']);
}
if ($MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')){
    $memberStatsArr[] = array('vImage' => "store.svg",
                              'vTitle' => "Stores",
                              'Total'  => abbreviateNumber($systemStats[0]['total_stores']),
                              'Total'  => $systemStats[0]['total_stores'] > 0?abbreviateNumber($systemStats[0]['total_stores']):$systemStats[0]['total_stores'],
                              'Link'   => $LOCATION_FILE_ARRAY['STORE.PHP']);
}
$tripsJobsStatsArr = array();
$ON_DEMAND_SERVICE_LINK_SHOW = "Yes";
$ON_DEMAND_SERVICE_CLICK_LOAD_CHART = "Yes";
$SERVICES_STATISTICS = "On Demand Services";
if ($MODULES_OBJ->isRideFeatureAvailable('Yes') && $MODULES_OBJ->isDeliveryFeatureAvailable('Yes') && !$MODULES_OBJ->isUberXFeatureAvailable('Yes')){
    $SERVICES_STATISTICS = "Services Statistics";
}
if ($isRideFeatureAvailable){
    $tripsJobsStatsArr[] = array('vImage'     => "parcel-delivery.svg",
                                 'vTitle'     => ONLY_MEDICAL_SERVICE == "Yes"?'Total '.$TRIP_TEXT_FOR_ONLY_MEDICAL_SERVICE:'Total Trips',
                                 'Today'      => $systemStats[0]['total_rides'] > 0?abbreviateNumber($systemStats[0]['total_rides']):$systemStats[0]['total_rides'],
                                 'Total'      => $systemStatsTotal[0]['total_rides'] > 0?abbreviateNumber($systemStatsTotal[0]['total_rides']):$systemStatsTotal[0]['total_rides'],
                                 'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?eType=Ride",
                                 'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?eType=Ride".$TODAY_LINK,
                                 'Type'       => 'Trip',);
}
$onlyOneService = "No";
if ($APP_TYPE == 'Ride'){
    $SERVICES_STATISTICS = "Trip Statistics";
    $ON_DEMAND_SERVICE_LINK_SHOW = "No";
    $ON_DEMAND_SERVICE_CLICK_LOAD_CHART = "No";
    $onlyOneService = "Yes";
    if ($isRideFeatureAvailable){
        $tripsJobsStatsArr[] = array('vImage'     => "panding-trip.svg",
                                     'vTitle'     => "Inprocess Trips ",
                                     'Today'      => $systemStats[0]['total_inprocess_rides'] > 0?abbreviateNumber($systemStats[0]['total_inprocess_rides']):$systemStats[0]['total_inprocess_rides'],
                                     'Total'      => $systemStatsTotal[0]['total_inprocess_rides'] > 0?abbreviateNumber($systemStatsTotal[0]['total_inprocess_rides']):$systemStatsTotal[0]['total_inprocess_rides'],
                                     'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=onRide",
                                     'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=onRide&eType=Ride".$TODAY_LINK,
                                     'Type'       => 'Trip',);
        $tripsJobsStatsArr[] = array('vImage'     => "cancel-trip.svg",
                                     'vTitle'     => "Cancelled Trips",
                                     'Today'      => $systemStats[0]['total_canceled_rides'] > 0?abbreviateNumber($systemStats[0]['total_canceled_rides']):$systemStats[0]['total_canceled_rides'],
                                     'Total'      => $systemStatsTotal[0]['total_canceled_rides'] > 0?abbreviateNumber($systemStatsTotal[0]['total_canceled_rides']):$systemStatsTotal[0]['total_canceled_rides'],
                                     'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=cancel",
                                     'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=cancel&eType=Ride".$TODAY_LINK,
                                     'Type'       => 'Trip',);
    }
}
if ($isDeliveryFeatureAvailable){
    $tripsJobsStatsArr[] = array('vImage'     => "parcel.svg",
                                 'vTitle'     => "Total Parcel Deliveries",
                                 'Today'      => $systemStats[0]['total_deliveries'] > 0?abbreviateNumber($systemStats[0]['total_deliveries']):$systemStats[0]['total_deliveries'],
                                 'Total'      => $systemStatsTotal[0]['total_deliveries'] > 0?abbreviateNumber($systemStatsTotal[0]['total_deliveries']):$systemStatsTotal[0]['total_deliveries'],
                                 'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?&eType=Deliver",
                                 'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?&eType=Deliver".$TODAY_LINK,
                                 'Type'       => 'Multi_Delivery',);
}
if ($isUberXFeatureAvailable){
    $tripsJobsStatsArr[] = array('vImage'     => "job-search.svg",
                                 'vTitle'     => "Total On Demand Jobs",
                                 'Today'      => $systemStats[0]['total_jobs'] > 0?abbreviateNumber($systemStats[0]['total_jobs']):$systemStats[0]['total_jobs'],
                                 'Total'      => $systemStatsTotal[0]['total_jobs'] > 0?abbreviateNumber($systemStatsTotal[0]['total_jobs']):$systemStatsTotal[0]['total_jobs'],
                                 'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?eType=UberX",
                                 'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?eType=UberX".$TODAY_LINK,
                                 'Type'       => 'UberX',);
}
if ($APP_TYPE == 'UberX'){
    $onlyOneService = "Yes";
    $ON_DEMAND_SERVICE_LINK_SHOW = "No";
    $ON_DEMAND_SERVICE_CLICK_LOAD_CHART = "No";
    if ($isUberXFeatureAvailable){
        $tripsJobsStatsArr[] = array('vImage'     => "inprocess-job.svg",
                                     'vTitle'     => "Inprocess Jobs ",
                                     'Today'      => $systemStats[0]['total_inprocess_jobs'] > 0?abbreviateNumber($systemStats[0]['total_inprocess_jobs']):$systemStats[0]['total_inprocess_jobs'],
                                     'Total'      => $systemStatsTotal[0]['total_inprocess_jobs'] > 0?abbreviateNumber($systemStatsTotal[0]['total_inprocess_jobs']):$systemStatsTotal[0]['total_inprocess_jobs'],
                                     'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=onRide",
                                     'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=onRide".$TODAY_LINK,
                                     'Type'       => 'UberX',);
        $tripsJobsStatsArr[] = array('vImage'     => "cancel-job.svg",
                                     'vTitle'     => "Cancelled Jobs",
                                     'Today'      => $systemStats[0]['total_canceled_jobs'] > 0?abbreviateNumber($systemStats[0]['total_canceled_jobs']):$systemStats[0]['total_canceled_jobs'],
                                     'Total'      => $systemStatsTotal[0]['total_canceled_jobs'] > 0?abbreviateNumber($systemStatsTotal[0]['total_canceled_jobs']):$systemStatsTotal[0]['total_canceled_jobs'],
                                     'Link'       => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=cancel",
                                     'Today_Link' => $LOCATION_FILE_ARRAY['TRIP']."?vStatus=cancel".$TODAY_LINK,
                                     'Type'       => 'UberX',);
    }
}
/*------------------Total-----------------*/
$scriptArr['DEFAULT_CHART'] = '';
if ($isRideFeatureAvailable){
    $scriptArr['Trip_Total'] = (int)$systemStats[0]['total_rides'];
    $scriptArr['Trip'] = [(int)$systemStats[0]['total_inprocess_rides'],
                          (int)$systemStats[0]['total_finished_rides'],
                          (int)$systemStats[0]['total_canceled_rides']];
    $scriptArr['Trip_Status'] = ['In Process','Completed','Cancelled'];
    $scriptArr['Trip_Color'] = ['#17c653','#174feb','#f31a41'];
    if (empty($scriptArr['DEFAULT_CHART'])){
        $scriptArr['DEFAULT_CHART'] = "Trip";
    }
}
if ($isDeliveryFeatureAvailable){
    $scriptArr['Multi_Delivery_Total'] = (int)$systemStats[0]['total_deliveries'];
    $scriptArr['Multi_Delivery'] = [(int)$systemStats[0]['total_inprocess_deliveries'],
                                    (int)$systemStats[0]['total_finished_deliveries'],
                                    (int)$systemStats[0]['total_canceled_deliveries']];
    $scriptArr['Multi_Delivery_Status'] = ['In Process','Completed','Cancelled'];
    $scriptArr['Multi_Delivery_Color'] = ['#17c653','#174feb','#f31a41'];
    if (empty($scriptArr['DEFAULT_CHART'])){
        $scriptArr['DEFAULT_CHART'] = "Multi_Delivery";
    }
}
if ($isUberXFeatureAvailable){
    $scriptArr['UberX_Total'] = (int)$systemStats[0]['total_jobs'];
    $scriptArr['UberX'] = [(int)$systemStats[0]['total_inprocess_jobs'],
                           (int)$systemStats[0]['total_finished_jobs'],
                           (int)$systemStats[0]['total_canceled_jobs']];
    $scriptArr['UberX_Status'] = ['In Process','Completed','Cancelled'];
    $scriptArr['UberX_Color'] = ['#17c653','#174feb','#f31a41'];
    if (empty($scriptArr['DEFAULT_CHART'])){
        $scriptArr['DEFAULT_CHART'] = "UberX";
    }
}
if (isset($systemStats[0]['total_rides']) && $systemStats[0]['total_rides'] == 0){
    $scriptArr['Trip'] = [1,1,1];
    $scriptArr['Trip_Show'] = "NotFoundData";
}
if (isset($systemStats[0]['total_deliveries']) && $systemStats[0]['total_deliveries'] == 0){
    $scriptArr['Multi_Delivery'] = [1,1,1];
    $scriptArr['Multi_Delivery_Show'] = "NotFoundData";
}
if (isset($systemStats[0]['total_jobs']) &&  $systemStats[0]['total_jobs'] == 0){
    $scriptArr['UberX'] = [1,1,1];
    $scriptArr['UberX_Show'] = "NotFoundData";
}
$scriptArrTotal['Trip_Total'] = (int)$systemStatsTotal[0]['total_rides'];
$scriptArrTotal['Trip'] = [(int)$systemStatsTotal[0]['total_inprocess_rides'],
                           (int)$systemStatsTotal[0]['total_finished_rides'],
                           (int)$systemStatsTotal[0]['total_canceled_rides']];
$scriptArrTotal['Trip_Status'] = ['In Process','Completed','Cancelled'];
$scriptArrTotal['Trip_Color'] = ['#ffc300','#174feb','#f31a41'];
$scriptArrTotal['Multi_Delivery_Total'] = (int)$systemStatsTotal[0]['total_deliveries'];
$scriptArrTotal['Multi_Delivery'] = [(int)$systemStatsTotal[0]['total_inprocess_deliveries'],
                                     (int)$systemStatsTotal[0]['total_finished_deliveries'],
                                     (int)$systemStatsTotal[0]['total_canceled_deliveries']];
$scriptArrTotal['Multi_Delivery_Status'] = ['In Process','Completed','Cancelled'];
$scriptArrTotal['Multi_Delivery_Color'] = ['#ffc300','#174feb','#f31a41'];
$scriptArrTotal['UberX_Total'] = (int)$systemStatsTotal[0]['total_jobs'];
$scriptArrTotal['UberX'] = [(int)$systemStatsTotal[0]['total_inprocess_jobs'],
                            (int)$systemStatsTotal[0]['total_finished_jobs'],
                            (int)$systemStatsTotal[0]['total_canceled_jobs']];
$scriptArrTotal['UberX_Status'] = ['In Process','Completed','Cancelled'];
$scriptArrTotal['UberX_Color'] = ['#ffc300','#174feb','#f31a41'];
if ($systemStatsTotal[0]['total_rides'] == 0){
    $scriptArrTotal['Trip'] = [1,1,1];
    $scriptArrTotal['Total_Trip_Show'] = "NotFoundData";
}
if ($systemStatsTotal[0]['total_deliveries'] == 0){
    $scriptArrTotal['Multi_Delivery'] = [1,1,1];
    $scriptArrTotal['Total_Multi_Delivery_Show'] = "NotFoundData";
}
if ($systemStatsTotal[0]['total_jobs'] == 0){
    $scriptArrTotal['UberX'] = [1,1,1];
    $scriptArrTotal['Total_UberX_Show'] = "NotFoundData";
}
/*------------------Ride Share-----------------*/
if ($isEnableRideShareService){
    $sql1 = '';
    $sql1 = " AND Date(dAddedDate) ='".$date."'";
    $FINAL_SQL = "SELECT
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} ) as total_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eTrackingStatus IN ('Start', 'MarkAsPickupALL','End')  AND eStatus = 'Active' ) as inprocess_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eTrackingStatus = 'PaymentCollect' AND eStatus = 'Active' ) as Completed_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eTrackingStatus IN ('Pending')  AND eStatus = 'Active' ) as pending_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eStatus = 'Cancelled' ) as cancelled_published_rides ";
    $RIDE_SHARE_SYSTEM_STATS_TODAY = $obj->MySQLSelect($FINAL_SQL);
    $sql1 = '';
    $FINAL_SQL = "SELECT
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} ) as total_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eTrackingStatus = 'PaymentCollect' AND eStatus = 'Active' ) as Completed_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eTrackingStatus IN ('Start', 'MarkAsPickupALL','End')  AND eStatus = 'Active' ) as inprocess_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND dStartDate >= '".date('Y-m-d H:i:s')."' AND eTrackingStatus IN ('Pending')  AND eStatus = 'Active' ) as pending_published_rides,
        (Select COUNT(iPublishedRideId) FROM published_rides WHERE 1=1 {$sql1} AND eStatus = 'Cancelled' ) as cancelled_published_rides ";
    $RIDE_SHARE_SYSTEM_STATS_TOTAL = $obj->MySQLSelect($FINAL_SQL);
    $RideShareStatsArr = array();
    $RideShareStatsArr[] = array('vImage' => "car_.svg",
                                 'vTitle' => "Total Rides",
                                 'Today'  => $RIDE_SHARE_SYSTEM_STATS_TODAY[0]['total_published_rides'],
                                 'Total'  => $RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['total_published_rides'],
                                 'Link'   => "published_rides.php",
                                 'Type'   => 'Publish_Ride_Total',
                                 'color'  => 'color1');
    $RideShareStatsArr[] = array('vImage' => "parcel.svg",
                                 'vTitle' => "Pending Rides",
                                 'Today'  => $RIDE_SHARE_SYSTEM_STATS_TODAY[0]['pending_published_rides'],
                                 'Total'  => $RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['pending_published_rides'],
                                 'Link'   => "published_rides.php?eStatus=Active",
                                 'Type'   => 'Publish_Ride_Pending',
                                 'color'  => 'color3');
    $RideShareStatsArr[] = array('vImage' => "job-search.svg",
                                 'vTitle' => "Cancelled Rides",
                                 'Today'  => $RIDE_SHARE_SYSTEM_STATS_TODAY[0]['cancelled_published_rides'],
                                 'Total'  => $RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['cancelled_published_rides'],
                                 'Link'   => "published_rides.php?eStatus=Cancelled",
                                 'Type'   => 'Publish_Ride_Cancelled',
                                 'color'  => 'color4');
    $RideSharescriptArr = array('Ride_Share_Total'  => (int)$RIDE_SHARE_SYSTEM_STATS_TODAY[0]['total_published_rides'],
                                'Ride_Share'        => [(int)$RIDE_SHARE_SYSTEM_STATS_TODAY[0]['Completed_published_rides'],
                                                        (int)$RIDE_SHARE_SYSTEM_STATS_TODAY[0]['pending_published_rides'],
                                                        (int)$RIDE_SHARE_SYSTEM_STATS_TODAY[0]['cancelled_published_rides'],],
                                'Ride_Share_Status' => ['Completed','Pending','Cancelled'],
                                'Ride_Share_Color'  => ['#17c653','#ffc300','#f31a41']);
    if ($RIDE_SHARE_SYSTEM_STATS_TODAY[0]['total_published_rides'] == 0){
        $RideSharescriptArr['Ride_Share'] = [1,1,1];
        $RideSharescriptArr['Show'] = "NotFoundData";
    }
    $RideShareArrTotal = array('Ride_Share_Total'  => (int)$RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['total_published_rides'],
                               'Ride_Share'        => [(int)$RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['Completed_published_rides'],
                                                       (int)$RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['cancelled_published_rides'],
                                                       (int)$RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['pending_published_rides'],],
                               'Ride_Share_Status' => ['Completed','Cancelled','Pending'],
                               'Ride_Share_Color'  => ['#17c653','#f31a41','#ffc300']);
    if ($RIDE_SHARE_SYSTEM_STATS_TOTAL[0]['total_published_rides'] == 0){
        $RideShareArrTotal['Ride_Share'] = [1,1,1];
        $RideShareArrTotal['Show'] = "NotFoundData";
    }
}
/*------------------Ride Share-----------------*/
/*------------------Admin Earning -----------------*/
$startdate = date('Y-m-d 00:00:00');
$enddate = date('Y-m-d 23:59:59');
$TodayEarns = getStoreTotalEarns($startdate,$enddate);
$TodayEarns += getRideShareTotalEarns($startdate,$enddate);
$TodayEarns += getTotalEarns($startdate,$enddate);
$TotalEarns = getStoreTotalEarns();
$TotalEarns += getRideShareTotalEarns();
$TotalEarns += getTotalEarns();
$today_org_outstanding_report = org_outstanding_reports($startdate,$enddate);
$today_outstanding_report = outstanding_reports($startdate,$enddate);
$outstanding_report = outstanding_report();
$org_outstanding_report = org_outstanding_report();
$AdminEarningStatsArr = array();
$AdminEarningStatsArr[] = array('vImage'         => "total_earning.svg",
                                'vTitle'         => "Total Earning",
                                'vTitleSubTitle' => "Total earned Amount.",
                                'Today'          => abbreviateNumber($TodayEarns),
                                'Total'          => abbreviateNumber($TotalEarns),
                                'Link'           => "payment_report.php",
                                'Link2'          => "admin_payment_report.php",
                                'Type'           => 'Total_Earning',);
$AdminEarningStatsArr[] = array('vImage'         => "outstanding_amount.svg",
                                'vTitle'         => "Outstanding Amount",
                                'vTitleSubTitle' => "Pending from Users to make payment.",
                                'Today'          => abbreviateNumber($today_outstanding_report),
                                'Total'          => abbreviateNumber($outstanding_report),
                                'Link'           => "outstanding_report.php",
                                'Type'           => 'Outstanding_Amount',);
if ($isRideFeatureAvailable && ONLY_MEDICAL_SERVICE != 'Yes'){
    $AdminEarningStatsArr[] = array('vImage'         => "org.svg",
                                    'vTitle'         => "Org. Outstanding Amount",
                                    'vTitleSubTitle' => "Pending from different organization to make payment.",
                                    'Today'          => abbreviateNumber($today_org_outstanding_report),
                                    'Total'          => abbreviateNumber($org_outstanding_report),
                                    'Link'           => "org_payment_report.php",
                                    'Type'           => 'Org_Outstanding_Amount',);
}
$AdminEarning['Title'] = "Admin Earnings";
$AdminEarning['SubTitle'] = "";
$AdminEarning['Link'] = "";
//$isRideFeatureAvailable = false;
//$TodayEarns = 0;
//$today_outstanding_report = 0;
if (($TodayEarns <= 0) && ($today_outstanding_report <= 0) && (($today_org_outstanding_report <= 0 && $isRideFeatureAvailable) || !$isRideFeatureAvailable)){
    $scriptArr['adminEarningShow'] = "NotFoundData";
}
/*------------------Admin Earning -----------------*/
/*------------------Recent Ride -----------------*/
$Recent_Ride = "SELECT CONCAT(rd.vName, ' ', rd.vLastName) as Driver_UserName, CONCAT(ru.vName, ' ', ru.vLastName) as User_UserName, ru.vAvgRating as User_vAvgRating, rd.vAvgRating as Driver_vAvgRating, t.vRideNo, t.tSaddress, t.tDaddress, t.iActive, t.iTripId, t.tTripRequestDate, t.iRentalPackageId, t.eHailTrip, t.eIsInterCity, t.ePoolRide,t.vTimeZone
    FROM trips as t
    JOIN register_user as ru ON (ru.iUserId =  t.iUserId)
    LEFT JOIN register_driver as rd ON (rd.iDriverId =  t.iDriverId)
    WHERE t.eType = 'Ride' ORDER BY t.iTripId DESC LIMIT 0, 5";
$Recent_Ride_data1 = $obj->MySQLSelect($Recent_Ride);
$Recent_Ride_data = [];
if (isset($Recent_Ride_data1) && !empty($Recent_Ride_data1)){
    $i = 0;
    foreach ($Recent_Ride_data1 as $data){
        $class = "";
        $data['service_status'] = $data['iActive'];
        if (in_array($data['iActive'],["Active","Inactive"])){
            $data['service_status'] = "Way to Pickup";
            $class = "status-pickup";
        }elseif ($data['iActive'] == "On Going Trip"){
            $data['service_status'] = "Way to Dropoff";
            $class = "status-dropoff";
        }elseif ($data['iActive'] == "Arrived"){
            $class = "status-arrived";
        }elseif ($data['iActive'] == "Finished"){
            $data['service_status'] = "Completed";
            $class = "status-finished";
        }elseif ($data['iActive'] == "Canceled"){
            $class = "status-cancelled";
        }
        $data['class'] = $class;
        $data['Link'] = $tconfig['tsite_url_main_admin'].'invoice.php?iTripId='.$data['iTripId'];
        $data['Image'] = ['user_profile' => 'user.svg',
                          'star'         => 'star.png',
                          'document'     => 'document.svg',
                          'setting'      => 'setting.svg',
                          'date'         => 'date.svg'];
        $data['dBooking_date_time'] = date('H:i A',strtotime($data['tTripRequestDate']));
        $data['dBooking_date'] = date('D, M d, Y',strtotime($data['tTripRequestDate']));
        $date_format_data_array = array('tdate' => (!empty($data['vTimeZone']))?converToTz($data['tTripRequestDate'],$data['vTimeZone'],$serverTimeZone):$data['tTripRequestDate'],'langCode' => $default_lang,'DateFormatForWeb' => 1);
        $get_TripRequestDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $data['dBooking_time_format'] = $get_TripRequestDate_date_format['tDisplayTime'];
        $data['dBooking_date_format'] = $get_TripRequestDate_date_format['tDisplayDate'];
        $data['provider_title'] = "Driver";
        $data['service_type'] = "Ride";
        if (ONLY_MEDICAL_SERVICE == "Yes" && $data['service_type'] == "Ride"){
            $data['service_type'] = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH'];
        }else if ($data['iRentalPackageId'] > 0 && $data['eIsInterCity'] == "No"){
            $data['service_type'] = "Rental Ride";
        }elseif ($data['ePoolRide'] == "Yes"){
            $data['service_type'] = "Pool Ride";
        }elseif ($data['eHailTrip'] == "Yes"){
            $data['service_type'] = "Hail Ride";
        }elseif ($data['eIsInterCity'] == "Yes"){
            $data['service_type'] = "Intercity Ride";
        }
        $Recent_Ride_data[$i] = $data;
        $i++;
    }
}
$Recent_UberX = "SELECT t.vTimeZone ,t.eCancelled , CONCAT(rd.vName, ' ', rd.vLastName) as Driver_UserName, CONCAT(ru.vName, ' ', ru.vLastName) as User_UserName, ru.vAvgRating as User_vAvgRating, rd.vAvgRating as Driver_vAvgRating, t.vRideNo, t.tSaddress, t.tDaddress, t.iActive, t.iTripId, t.tTripRequestDate, (CASE WHEN t.isVideoCall = 'Yes' THEN
    (SELECT vc2.vCategory_$default_lang FROM `vehicle_category` as vc1 JOIN `vehicle_category` as vc2 ON vc2.iVehicleCategoryId = vc1.iParentId WHERE vc1.iVehicleCategoryId = REPLACE(JSON_UNQUOTE(JSON_VALUE(t.tVehicleTypeFareData, '$.FareData[0].id')), '\"', ''))
    ELSE
    (SELECT vc2.vCategory_$default_lang FROM `vehicle_type` as vt JOIN `vehicle_category` as vc1 ON vt.iVehicleCategoryId = vc1.iVehicleCategoryId JOIN `vehicle_category` as vc2 ON vc1.iParentId = vc2.iVehicleCategoryId WHERE vt.iVehicleTypeId = t.iVehicleTypeId)
    END) as service_type
    FROM trips as t
    JOIN register_user as ru ON (ru.iUserId =  t.iUserId)
    LEFT JOIN register_driver as rd ON (rd.iDriverId =  t.iDriverId)
    WHERE t.eType = 'UberX' ORDER BY t.iTripId DESC LIMIT 0, 5";
$Recent_UberX_Data1 = $obj->MySQLSelect($Recent_UberX);
$Recent_UberX_Data = [];
if (isset($Recent_UberX_Data1) && !empty($Recent_UberX_Data1)){
    $i = 0;
    foreach ($Recent_UberX_Data1 as $data){
        $class = "";
        $data['service_status'] = $data['iActive'];
        if (in_array($data['iActive'],["Active","Inactive"])){
            $data['service_status'] = "Way to Pickup";
            $class = "status-pickup";
        }elseif ($data['iActive'] == "On Going Trip"){
            $data['service_status'] = "Way to Dropoff";
            $class = "status-dropoff";
        }elseif ($data['iActive'] == "Arrived"){
            $class = "status-arrived";
        }elseif ($data['iActive'] == "Finished" && $data['eCancelled'] == "No"){
            $data['service_status'] = "Completed";
            $class = "status-finished";
        }elseif ($data['iActive'] == "Canceled" || ($data['eCancelled'] == "Yes" && $data['iActive'] == "Finished")){
            $class = "status-cancelled";
            $data['service_status'] = "Cancelled";
        }
        $data['class'] = $class;
        $data['Link'] = $tconfig['tsite_url_main_admin'].'invoice.php?iTripId='.$data['iTripId'];
        $data['Image'] = ['user_profile' => 'user.svg',
                          'star'         => 'star.png',
                          'document'     => 'document.svg',
                          'setting'      => 'setting.svg',
                          'date'         => 'date.svg'];
        $data['dBooking_date_time'] = date('H:i A',strtotime($data['tTripRequestDate']));
        $data['dBooking_date'] = date('D, M d, Y',strtotime($data['tTripRequestDate']));
        $date_format_data_array = array('tdate'            => (!empty($data['vTimeZone']))?converToTz($data['tTripRequestDate'],$data['vTimeZone'],$serverTimeZone):$data['tTripRequestDate'],
                                        'langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $get_TripRequestDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $data['dBooking_time_format'] = $get_TripRequestDate_date_format['tDisplayTime'];
        $data['dBooking_date_format'] = $get_TripRequestDate_date_format['tDisplayDate'];
        $data['provider_title'] = "Service Provider";
        $Recent_UberX_Data[$i] = $data;
        $i++;
    }
}
$Recent_Multi_Delivery = "SELECT t.eCancelled , CONCAT(rd.vName, ' ', rd.vLastName) as Driver_UserName, CONCAT(ru.vName, ' ', ru.vLastName) as User_UserName, ru.vAvgRating as User_vAvgRating, rd.vAvgRating as Driver_vAvgRating, t.vRideNo, t.tSaddress, t.tDaddress, t.iActive, t.iTripId, t.tTripRequestDate, (SELECT COUNT(tl.iTripDeliveryLocationId) AS Total FROM trips_delivery_locations as tl WHERE 1=1 AND tl.iActive = 'Finished' AND t.iTripId = tl.iTripId) as totalDeliveryTrips
    FROM trips as t
    JOIN register_user as ru ON (ru.iUserId =  t.iUserId)
    LEFT JOIN register_driver as rd ON (rd.iDriverId =  t.iDriverId)
    WHERE t.eType = 'Multi-Delivery' ORDER BY t.iTripId DESC LIMIT 0, 5";
$Recent_Multi_Delivery_Data1 = $obj->MySQLSelect($Recent_Multi_Delivery);
$Recent_Multi_Delivery_Data = [];
if (isset($Recent_Multi_Delivery_Data1) && !empty($Recent_Multi_Delivery_Data1)){
    $i = 0;
    foreach ($Recent_Multi_Delivery_Data1 as $data){

        $class = "";
        $data['service_status'] = $data['iActive'];
        if (in_array($data['iActive'],["Active","Inactive"])){
            $data['service_status'] = "Way to Pickup";
            $class = "status-pickup";
        }elseif ($data['iActive'] == "On Going Trip"){
            $data['service_status'] = "Way to Dropoff";
            $class = "status-dropoff";
        }elseif ($data['iActive'] == "Arrived"){
            $class = "status-arrived";
        }elseif ($data['iActive'] == "Finished" && $data['eCancelled'] == "No"){
            $data['service_status'] = "Completed";
            $class = "status-finished";
        }elseif ($data['iActive'] == "Canceled" || ($data['eCancelled'] == "Yes" && $data['iActive'] == "Finished")){
            $class = "status-cancelled";
        }
        $data['class'] = $class;
        $data['Link'] = $tconfig['tsite_url_main_admin'].'invoice_multi_delivery.php?iTripId='.$data['iTripId'];
        $data['Image'] = ['user_profile' => 'user.svg',
                          'star'         => 'star.png',
                          'document'     => 'document.svg',
                          'setting'      => 'setting.svg',
                          'date'         => 'date.svg'];
        $data['dBooking_date_time'] = date('H:i A',strtotime($data['tTripRequestDate']));
        $data['dBooking_date'] = date('D, M d, Y',strtotime($data['tTripRequestDate']));
        $date_format_data_array = array('tdate'            => (!empty($data['vTimeZone']))?converToTz($data['tTripRequestDate'],$data['vTimeZone'],$serverTimeZone):$data['tTripRequestDate'],
                                        'langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $get_TripRequestDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $data['dBooking_time_format'] = $get_TripRequestDate_date_format['tDisplayTime'];
        $data['dBooking_date_format'] = $get_TripRequestDate_date_format['tDisplayDate'];
        $data['provider_title'] = "Delivery Driver";
        $data['service_type'] = "Single Delivery";
        if ($data['totalDeliveryTrips'] > 1){
            $data['service_type'] = "Multi-Delivery";
        }
        $Recent_Multi_Delivery_Data[$i] = $data;
        $i++;
    }
}
$IN_PROCESS_RIDE_SQL = "SELECT COUNT(t.iTripId) as TotalInProcessTrip FROM trips as t WHERE t.iActive IN ('Active','Arrived','On Going Trip') ";
$IN_PROCESS_RIDE = $obj->MySQLSelect($IN_PROCESS_RIDE_SQL);
$TotalInProcessTrip = $IN_PROCESS_RIDE[0]['TotalInProcessTrip'];
$RecentRideArr = [];
$trip = $MODULES_OBJ->isRideFeatureAvailable('Yes');
$job = $MODULES_OBJ->isUberXFeatureAvailable('Yes');
$Delivery = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes');
$resultString = "";
if ($trip && ONLY_MEDICAL_SERVICE != "Yes"){
    $resultString .= "Rides";
}
if ($Delivery){
    if ($trip){
        $resultString .= "/";
    }
    $resultString .= "Deliveries";
}
if ($job){
    if (($trip || $Delivery) && ONLY_MEDICAL_SERVICE != "Yes"){
        $resultString .= "/";
    }
    $resultString .= "Jobs";
}
$RecentRideArr['title'] = "Recent ".$resultString;
$RecentRideArr['Subtitle'] = $TotalInProcessTrip." ".$resultString." in progress";
$RecentRideArr['Link'] = "trip.php";
if ($isRideFeatureAvailableForRecentTrip){
    $RecentRideArr['Tab'][] = array("type"  => 'Rides',
                                    "Title" => ONLY_MEDICAL_SERVICE == "Yes"?$TRIP_TEXT_FOR_ONLY_MEDICAL_SERVICE:'Rides');
}
if ($isDeliveryFeatureAvailableForRecentTrip){
    $RecentRideArr['Tab'][] = array("type"  => 'Deliveries',
                                    "Title" => 'Deliveries',);
}
if ($isUberXFeatureAvailableForRecentTrip){
    $RecentRideArr['Tab'][] = array("type"  => 'Jobs',
                                    "Title" => 'Jobs',);
}
if ($isRideFeatureAvailableForRecentTrip){
    $RecentRideArr['Data']['Rides'] = $Recent_Ride_data;
}
if ($isDeliveryFeatureAvailableForRecentTrip){
    $RecentRideArr['Data']['Deliveries'] = $Recent_Multi_Delivery_Data;
}
if ($isUberXFeatureAvailableForRecentTrip){
    $RecentRideArr['Data']['Jobs'] = $Recent_UberX_Data;
}
/*------------------Recent Ride -----------------*/
/*------------------Scheduled Bookings -----------------*/
if ($isEnableScheduledRideFlow){
    $Scheduled_Bookings = "SELECT COUNT(iCabBookingId) as count
                FROM cab_booking cb WHERE  DATE( NOW( ) ) <= DATE_ADD( DATE( cb.dBooking_date ) , INTERVAL 10 MINUTE )";
    $Scheduled_Bookings_Total = $obj->MySQLSelect($Scheduled_Bookings);
    $Scheduled_Bookings = "SELECT cb.iCabBookingId,cb.vBookingNo, cb.dBooking_date , cb.iVehicleTypeId, cb.vSourceAddresss,
                cb.eStatus,cb.eType,cb.vTimeZone,
                CONCAT(ru.vName ,' ',ru.vLastName ) as User_UserName, ru.vAvgRating as User_vAvgRating ,
                CONCAT(rd.vName ,' ',rd.vLastName ) as Driver_UserName, rd.vAvgRating as Driver_vAvgRating
                FROM cab_booking cb
                JOIN register_user as ru ON (ru.iUserId =  cb.iUserId)
                Left JOIN register_driver as rd ON (rd.iDriverId =  cb.iDriverId)
                WHERE  NOW() < cb.dBooking_date
                ORDER BY cb.iCabBookingId DESC
                LIMIT 0,5";
    $Scheduled_Bookings_data1 = $obj->MySQLSelect($Scheduled_Bookings);
    $Scheduled_Bookings_data = [];
    if (isset($Scheduled_Bookings_data1) && !empty($Scheduled_Bookings_data1)){
        $i = 0;
        foreach ($Scheduled_Bookings_data1 as $data){

            $Scheduled_Bookings = "SELECT vc2.vCategory_$default_lang , vt.vVehicleType_$default_lang
                FROM `vehicle_type` vt
                LEFT JOIN `vehicle_category` vc1 ON vt.iVehicleCategoryId = vc1.iVehicleCategoryId
                LEFT JOIN `vehicle_category` vc2 ON vc1.iParentId = vc2.iVehicleCategoryId
                WHERE vt.iVehicleTypeId = {$data['iVehicleTypeId']}";
            $Scheduled_Bookings_data1 = $obj->MySQLSelect($Scheduled_Bookings);
            $data['iVehicleTypeId'] = $Scheduled_Bookings_data1[0]['vCategory_'.$default_lang];
            if (empty($data['iVehicleTypeId'])){
                $data['iVehicleTypeId'] = $Scheduled_Bookings_data1[0]['vVehicleType_'.$default_lang].' ('.$data['eType'].')';
            }
            $data['dBooking_date_time'] = date('H:i A',strtotime($data['dBooking_date']));
            $data['dBooking_date'] = date('D, M d, Y',strtotime($data['dBooking_date']));
            $date_format_data_array = array('tdate'            => (!empty($data['vTimeZone']))?converToTz($data['dBooking_date'],$data['vTimeZone'],$serverTimeZone):$data['dBooking_date'],
                                            'langCode'         => $default_lang,
                                            'DateFormatForWeb' => 1);
            $get_dBooking_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $data['dBooking_time_format'] = $get_dBooking_date_format['tDisplayTime'];
            $data['dBooking_date_format'] = $get_dBooking_date_format['tDisplayDate'];
            $data['link'] = $tconfig['tsite_url_main_admin'].'cab_booking.php?keyword='.$data['vBookingNo'];
            $data['eStatus_class'] = "status-finished";
            if ($data['eStatus'] == "Cancel"){
                $data['eStatus_class'] = "status-cancelled";
            }
            $Scheduled_Bookings_data[] = $data;
        }
    }
    $ScheduledRideArr = [];
    $ScheduledRideArr['title'] = "Scheduled Bookings";
    $ScheduledRideArr['Link'] = $tconfig['tsite_url_main_admin'].'cab_booking.php';
    $ScheduledRideArr['Subtitle'] = "Recent ".$Scheduled_Bookings_Total[0]['count']." scheduled bookings";
    $ScheduledRideArr['Data'] = $Scheduled_Bookings_data;
    $ScheduledRideArr['Image'] = ['user_profile' => 'user.svg',
                                  'star'         => 'star.png',
                                  'document'     => 'document.svg',
                                  'setting'      => 'setting.svg',
                                  'date'         => 'date.svg',
                                  'location'     => 'location.svg',];
}
/*------------------Scheduled Bookings -----------------*/
/*------------------BUY Sell Rent -----------------*/
if ($isEnableRentItemService || $isEnableRentCarsService || $isEnableRentEstateService){
    $sql1 = " AND DATE(r.dRentItemPostDate) = '".$date."'";
    $car_sql = " SELECT
          (SELECT COUNT(r.iRentItemPostId) AS Total FROM rentitem_post r LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1   And rc.iMasterServiceCategoryId = '10'  $sql1 ) as CarCount,
          (SELECT COUNT(r.iRentItemPostId) AS Total FROM rentitem_post r LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1   And rc.iMasterServiceCategoryId = '9'  $sql1 ) as EstateCount ,
          (SELECT COUNT(r.iRentItemPostId) AS Total FROM rentitem_post r LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1   And rc.iMasterServiceCategoryId = '8'  $sql1 ) as ItemCount ";
    $BUY_SELL_RENT_DATA_TODAY = $obj->MySQLSelect($car_sql);
    $sql1 = "";
    $car_sql = " SELECT
          (SELECT COUNT(r.iRentItemPostId) AS Total FROM rentitem_post r LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1   And rc.iMasterServiceCategoryId = '10'  $sql1 ) as CarCount,
          (SELECT COUNT(r.iRentItemPostId) AS Total FROM rentitem_post r LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1   And rc.iMasterServiceCategoryId = '9'  $sql1 ) as EstateCount ,
          (SELECT COUNT(r.iRentItemPostId) AS Total FROM rentitem_post r LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1   And rc.iMasterServiceCategoryId = '8'  $sql1 ) as ItemCount ";
    $BUY_SELL_RENT_DATA_TOTAL = $obj->MySQLSelect($car_sql);
    $BuySellRentStatsArr = array();
    if ($isEnableRentCarsService){
        $BuySellRentStatsArr[] = array('vImage'     => "car_.svg",
                                       'vTitle'     => "Cars",
                                       'Today'      => $BUY_SELL_RENT_DATA_TODAY[0]['CarCount'],
                                       'Total'      => $BUY_SELL_RENT_DATA_TOTAL[0]['CarCount'],
                                       'Link'       => "all_bsr_items.php?eType=Cars",
                                       'Today_Link' => "all_bsr_items.php?eType=Cars".$TODAY_LINK,
                                       'Type'       => 'Cars',);
    }
    if ($isEnableRentItemService){
        $BuySellRentStatsArr[] = array('vImage'     => "parcel.svg",
                                       'vTitle'     => "General Items",
                                       'Today'      => $BUY_SELL_RENT_DATA_TODAY[0]['ItemCount'],
                                       'Total'      => $BUY_SELL_RENT_DATA_TOTAL[0]['ItemCount'],
                                       'Link'       => "all_bsr_items.php?eType=GeneralItem",
                                       'Today_Link' => "all_bsr_items.php?eType=GeneralItem".$TODAY_LINK,
                                       'Type'       => 'General_item',);
    }
    if ($isEnableRentEstateService){
        $BuySellRentStatsArr[] = array('vImage'     => "job-search.svg",
                                       'vTitle'     => "Real Estate Properties",
                                       'Today'      => $BUY_SELL_RENT_DATA_TODAY[0]['EstateCount'],
                                       'Total'      => $BUY_SELL_RENT_DATA_TOTAL[0]['EstateCount'],
                                       'Link'       => "all_bsr_items.php?eType=RealEstate",
                                       'Today_Link' => "all_bsr_items.php?eType=RealEstate".$TODAY_LINK,
                                       'Type'       => 'RealEstate',);
    }
    $BuySellRentscriptArr = array('Buy_Sell_Rent_Total'  => (int)($BUY_SELL_RENT_DATA_TODAY[0]['CarCount'] + $BUY_SELL_RENT_DATA_TODAY[0]['EstateCount'] + $BUY_SELL_RENT_DATA_TODAY[0]['ItemCount']),
                                  'Buy_Sell_Rent'        => [(int)$BUY_SELL_RENT_DATA_TODAY[0]['CarCount'],
                                                             (int)$BUY_SELL_RENT_DATA_TODAY[0]['ItemCount'],
                                                             (int)$BUY_SELL_RENT_DATA_TODAY[0]['EstateCount'],],
                                  'Buy_Sell_Rent_Status' => ['Cars','General Items','Real Estate Properties'],
                                  'Buy_Sell_Rent_Color'  => ['#174feb','#17c653','#ffc300']);
    if ($BuySellRentscriptArr['Buy_Sell_Rent_Total'] == 0){
        $BuySellRentscriptArr['Buy_Sell_Rent'] = [1,1,1];
        $BuySellRentscriptArr['Show'] = "NotFoundData";
    }
    $BuySellRentArrTotal = array('Buy_Sell_Rent_Total'  => (int)($BUY_SELL_RENT_DATA_TOTAL[0]['CarCount'] + $BUY_SELL_RENT_DATA_TOTAL[0]['EstateCount'] + $BUY_SELL_RENT_DATA_TOTAL[0]['ItemCount']),
                                 'Buy_Sell_Rent'        => [(int)$BUY_SELL_RENT_DATA_TOTAL[0]['CarCount'],
                                                            (int)$BUY_SELL_RENT_DATA_TOTAL[0]['ItemCount'],
                                                            (int)$BUY_SELL_RENT_DATA_TOTAL[0]['EstateCount'],],
                                 'Buy_Sell_Rent_Status' => ['Cars','General Items','Real Estate Properties'],
                                 'Buy_Sell_Rent_Color'  => ['#174feb','#17c653','#ffc300']);
    if ($BuySellRentArrTotal['Buy_Sell_Rent_Total'] == 0){
        $BuySellRentArrTotal['Buy_Sell_Rent'] = [1,1,1];
        $BuySellRentArrTotal['Show'] = "NotFoundData";
    }
}
/*------------------BUY Sell Rent -----------------*/
/*------------------Bidding -----------------*/
if ($isEnableBiddingServices){
    $sql1 = " AND DATE(dBiddingDate) = '".$date."'";
    $sql = " SELECT (SELECT COUNT(iBiddingPostId) as tot FROM bidding_post WHERE 1 = 1 {$sql1}) as Today_data,
(SELECT COUNT(iBiddingPostId) as tot FROM bidding_post WHERE 1 = 1) as Total_data ";
    $bidding_post_data = $obj->MySQLSelect($sql);
    $Bidding_Data['Title'] = 'Bid Services';
    $Bidding_Data['Link'] = "bidding_report.php";
    $Bidding_Data['Today_Link'] = "bidding_report.php?".$TODAY_LINK;
    $Bidding_Data['data'] = array('Today' => $bidding_post_data[0]['Today_data'],
                                  'Total' => $bidding_post_data[0]['Total_data'],);
}
/*------------------Bidding -----------------*/
/*------------------ Video consult -----------------*/
if ($isEnableVideoConsultingServices){
    $sql1 = " AND Date(tTripRequestDate) = '".$date."'";
    $sql = " SELECT (SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND isVideoCall = 'Yes' AND eSystem = 'General' {$sql1}) as Today_data,
(SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND isVideoCall = 'Yes' AND eSystem = 'General') as Total_data ";
    $video_consult_DB = $obj->MySQLSelect($sql);
    $Video_Consult_Data['Title'] = 'Video Consultation';
    $Video_Consult_Data['Link'] = $LOCATION_FILE_ARRAY['TRIP']."?eType=VideoConsultation";
    $Video_Consult_Data['Today_Link'] = $LOCATION_FILE_ARRAY['TRIP']."?eType=VideoConsultation".$TODAY_LINK;
    $Video_Consult_Data['data'] = array('Today' => $video_consult_DB[0]['Today_data'],
                                        'Total' => $video_consult_DB[0]['Total_data'],);
}
/*------------------Video consult -----------------*/
/*------------------ Store Deliveries -----------------*/
if ($isDeliverAllFeatureAvailable){
    $sql1 = " AND Date(tOrderRequestDate) = '".$date."'";
    $sql = " SELECT (SELECT COUNT(iOrderId) as tot FROM orders WHERE 1 = 1 AND eBuyAnyService = 'No' {$sql1}) as Today_data,
(SELECT COUNT(iOrderId) as tot FROM orders WHERE 1 = 1 AND eBuyAnyService = 'No') as Total_data ";
    $ORDERS_DB = $obj->MySQLSelect($sql);
    $Store_Data['Title'] = $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'].' Deliveries';
    $Store_Data['Link'] = "allorders.php?type=allorders&ServiceType=AllOrder";
    $Store_Data['Today_Link'] = "allorders.php?type=allorders&ServiceType=AllOrder".$TODAY_LINK;
    $Store_Data['data'] = array('Today' => $ORDERS_DB[0]['Today_data'],
                                'Total' => $ORDERS_DB[0]['Total_data'],);
}
/*------------------ Store Deliveries -----------------*/
/*------------------Genie Runner-----------------*/
if ($isEnableGenieFeature || $isEnableRunnerFeature){
    $sql1 = " AND Date(tOrderRequestDate) = '".$date."'";
    $sql = " SELECT (SELECT COUNT(iOrderId) as tot FROM orders WHERE 1 = 1 AND eBuyAnyService = 'Yes' {$sql1}) as Today_data,
(SELECT COUNT(iOrderId) as tot FROM orders WHERE 1 = 1 AND eBuyAnyService = 'Yes') as Total_data ";
    $Genie_Runner_DB = $obj->MySQLSelect($sql);
    $Genie_Runner_Data['Title'] = 'Delivery Genie / Runner';
    $Genie_Runner_Data['Link'] = "allorders.php?type=Genie&ServiceType=GenieRunner";
    $Genie_Runner_Data['Today_Link'] = "allorders.php?type=Genie&ServiceType=GenieRunner".$TODAY_LINK;
    $Genie_Runner_Data['data'] = array('Today' => $Genie_Runner_DB[0]['Today_data'],
                                       'Total' => $Genie_Runner_DB[0]['Total_data'],);
}
/*------------------Genie Runner-----------------*/
/*------------------BUY Sell Rent -----------------*/
$dashboardIcon = $tconfig['tsite_url_main_admin']."img/icon/";
$chartLoader = $tconfig['tsite_url_main_admin']."images/page-loader.gif";
/*****************************/
$sql = "SELECT dm.doc_name_".$default_lang.",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate,c.iCompanyId,rd.iDriverId FROM `document_list` AS dl LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid LEFT JOIN company AS c ON ( c.iCompanyId = dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store')) LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver') LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car') LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId AND dm.doc_name_$default_lang != '' HAVING dm.doc_name_".$default_lang." != '' ORDER BY dl.edate  DESC LIMIT 0, 8";
$db_notification = $obj->MySQLSelect($sql);
/* Maps Api Settings */
$GOOGLE_KEYS_WEB = array('GOOGLE_SEVER_GCM_API_KEY',
                         'GOOGLE_SEVER_API_KEY_WEB');
$GOOGLE_KEYS_ANDROID['USER'] = array('GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY');
$GOOGLE_KEYS_ANDROID['DRIVER'] = array('GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY');
$GOOGLE_KEYS_ANDROID['STORE'] = array('GOOGLE_SERVER_ANDROID_COMPANY_APP_KEY');
$GOOGLE_KEYS_IOS['USER'] = array('GOOGLE_SERVER_IOS_PASSENGER_APP_KEY',
                                 'GOOGLE_IOS_PASSENGER_APP_GEO_KEY');
$GOOGLE_KEYS_IOS['DRIVER'] = array('GOOGLE_SERVER_IOS_DRIVER_APP_KEY',
                                   'GOOGLE_IOS_DRIVER_APP_GEO_KEY');
$GOOGLE_KEYS_IOS['STORE'] = array('GOOGLE_SERVER_IOS_COMPANY_APP_KEY',
                                  'GOOGLE_IOS_COMPANY_APP_GEO_KEY');
$googleKeysConfigData = $obj->MySQLSelect("SELECT vName, vValue, tDescription, tHelp FROM configurations WHERE vName IN ('GOOGLE_SEVER_GCM_API_KEY', 'GOOGLE_SEVER_API_KEY_WEB', 'GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY', 'GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY', 'GOOGLE_SERVER_ANDROID_COMPANY_APP_KEY', 'GOOGLE_SERVER_IOS_PASSENGER_APP_KEY', 'GOOGLE_IOS_PASSENGER_APP_GEO_KEY', 'GOOGLE_SERVER_IOS_DRIVER_APP_KEY', 'GOOGLE_IOS_DRIVER_APP_GEO_KEY', 'GOOGLE_SERVER_IOS_COMPANY_APP_KEY', 'GOOGLE_IOS_COMPANY_APP_GEO_KEY') ORDER BY vOrder ");
$googleKeysPrimary = array_unique(array_column($googleKeysConfigData,'vValue'));
$GOOGLE_KEYS_ARR = array();
foreach ($googleKeysConfigData as $ConfigData){
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_WEB)){
        $GOOGLE_KEYS_ARR['WEB'][] = $ConfigData;
    }
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_ANDROID['USER'])){
        $GOOGLE_KEYS_ARR['ANDROID']['USER'][] = $ConfigData;
    }
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_ANDROID['DRIVER'])){
        $GOOGLE_KEYS_ARR['ANDROID']['DRIVER'][] = $ConfigData;
    }
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_ANDROID['STORE'])){
        $GOOGLE_KEYS_ARR['ANDROID']['STORE'][] = $ConfigData;
    }
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_IOS['USER'])){
        $GOOGLE_KEYS_ARR['IOS']['USER'][] = $ConfigData;
    }
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_IOS['DRIVER'])){
        $GOOGLE_KEYS_ARR['IOS']['DRIVER'][] = $ConfigData;
    }
    if (in_array($ConfigData['vName'],$GOOGLE_KEYS_IOS['STORE'])){
        $GOOGLE_KEYS_ARR['IOS']['STORE'][] = $ConfigData;
    }
}
$GOOGLE_KEYS_WEB_VAL = array_column($GOOGLE_KEYS_ARR['WEB'],'vValue');
$WebValues = array_filter($GOOGLE_KEYS_WEB_VAL,function ($value){
    return empty($value);
});
$GOOGLE_KEYS_ANDROID_USER_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['USER'],'vValue');
$AndroidUserValues = array_filter($GOOGLE_KEYS_ANDROID_USER_VAL,function ($value){
    return empty($value);
});
$GOOGLE_KEYS_ANDROID_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['DRIVER'],'vValue');
$AndroidDriverValues = array_filter($GOOGLE_KEYS_ANDROID_DRIVER_VAL,function ($value){
    return empty($value);
});
$GOOGLE_KEYS_ANDROID_STORE_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['STORE'],'vValue');
$GOOGLE_KEYS_IOS_USER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['USER'],'vValue');
$IOSUserValues = array_filter($GOOGLE_KEYS_IOS_USER_VAL,function ($value){
    return empty($value);
});
$GOOGLE_KEYS_IOS_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['DRIVER'],'vValue');
$IOSDriverValues = array_filter($GOOGLE_KEYS_IOS_DRIVER_VAL,function ($value){
    return empty($value);
});
$GOOGLE_KEYS_IOS_STORE_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['STORE'],'vValue');
$AndroidStoreValues = $IOSStoreValues = array();
if ($MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')){
    $AndroidStoreValues = array_filter($GOOGLE_KEYS_ANDROID_STORE_VAL,function ($value){
        return empty($value);
    });
    $IOSStoreValues = array_filter($GOOGLE_KEYS_IOS_STORE_VAL,function ($value){
        return empty($value);
    });
}
$isMapApiSettingError = $isMapApiKeyError = "No";
if (scount($WebValues) > 0 || scount($AndroidUserValues) > 0 || scount($AndroidDriverValues) > 0 || scount($AndroidStoreValues) > 0 || scount($IOSUserValues) > 0 || scount($IOSDriverValues) > 0 || scount($IOSStoreValues) > 0){
    $isMapApiSettingError = "Yes";
    $MapApiSettingUrl = $tconfig['tsite_url_main_admin'].'general.php?tab=MapsApiSettings';
}else{
    $data_Service_names = $obj->fetchAllRecordsFromMongoDBWithDBName(TSITE_DB,"auth_master_accounts_places",[]);
    $ServiceKey = array_search('Google',array_column($data_Service_names,'vServiceName'));
    $data_Service_Google = $data_Service_names[$ServiceKey];
    $result = addDefaultApiKeysToMongoDB($GOOGLE_SEVER_GCM_API_KEY);
    if ($result['Action'] == "0"){
        $isMapApiSettingError = $isMapApiKeyError = "Yes";
        $MapApiSettingUrl = $tconfig['tsite_url_main_admin'].'map_api_mongo_auth_places.php?id='.$data_Service_Google['vServiceId'];
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
$SHOW_COLUMN_1 = ($userObj->hasPermission('dashboard-god-view') || $userObj->hasPermission('admin-earning-dashboard') || ($userObj->hasPermission('dashboard-ride-job-statistics') && ($isRideFeatureAvailable || $isDeliveryFeatureAvailable || $isUberXFeatureAvailable)) || $userObj->hasPermission('manage-server-admin-dashboard') || $userObj->hasPermission('dashboard-contact-us-form Requests'));
$SHOW_COLUMN_2 = ($userObj->hasPermission('dashboard-member-statistics') || $isRideFeatureAvailable || $isUberXFeatureAvailable || $isDeliveryFeatureAvailable || $isEnableVideoConsultingServices || $isEnableBiddingServices || $isEnableRideShareService || $isDeliverAllFeatureAvailable || $isEnableGenieFeature || $isEnableRunnerFeature || $isEnableRentItemService || $isEnableRentCarsService || $isEnableRentEstateService || $isEnableScheduledRideFlow || $userObj->hasPermission('dashboard-notifications-alerts-panel'));
$defaultActiveService = "Ride";
if ($APP_TYPE == "UberX"){
    $defaultActiveService = "Job";
}elseif ($APP_TYPE == "Delivery" || $MODULES_OBJ->isOnlyDeliverAllSystem()){
    $defaultActiveService = "Delivery";
}

//Crie uma variável com a localização do franqueado para filtrar
$franchiseLocationId = 0;
if ($isFranchiseAdmin) {
    $sql = "SELECT location_id FROM admin_locations WHERE admin_id = '".$_SESSION['sess_iAdminId']."'";
    $locData = $obj->MySQLSelect($sql);
    if (!empty($locData)) {
        $franchiseLocationId = $locData[0]['location_id'];
    }
}

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
    <title><?=$SITE_NAME;?> | Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <![endif]-->
    <!-- GLOBAL STYLES -->
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet"
            href="<?=$tconfig['tsite_url_main_admin']?>css/admin_new/dashboard_v4.css?<? echo time(); ?>">
    <script src="<?=$tconfig['tsite_url_main_admin']?>js/apexcharts_v2.js"></script>

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
<body>

<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <div id="content" class="content_right">

        <?php if ($SHOW_DASHBOARD){ ?>

            <div class="cintainerinner">
                <?php if ($isMapApiSettingError == "Yes"){ ?>
                    <div class="admin_card_row map-setting-alert">
                        <div class="admin_column">
                            <div class="card_body">
                                <div class="card_heading_row">
                                    <strong><i class="ri-alert-line"></i> There are some misconfigurations in Maps API
                                        Settings. <?php if($isMapApiKeyError == "Yes") { ?>Please contact technical team.<?php } ?></strong>
                                    <?php if($isMapApiKeyError == "No") { ?>
                                    <a href="javascript:void(0);" link-target="<?=$MapApiSettingUrl?>" class="common_button RedirectUrl pointer">Check Now</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php
                if ($CONFIG_OBJ->isOnlyCashPaymentModeAvailable() &&  ($isEnableRentItemService || $isEnableRentCarsService || $isEnableRentEstateService || $isEnableVideoConsultingServices) ){ ?>
                    <div class="admin_card_row map-setting-alert">
                        <div class="admin_column">
                            <div class="card_body" style="padding: 10px;">
                                <div class="card_heading_row_cash">
                                    <button class="cashaccordion cash_title active">Important Information: <i class="fa fa-caret-down accordion-icon"></i></button>
                                    <div class="cashpanel" style="display:block;">
                                        <p class="onlycash"><i class="ri-alert-line"></i> Payment mode is only available as cash in the system, so the following modules are not working properly. Please disable them manually.</p>
                                        <ul class="onlycashlisting">
                                            <li>Video Consulting</li>
                                            <li>Buy, Sell and Rent</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>


                <div class="grid" id="masonry">

                    <div class="grid-sizer"></div>

                    <?php if ($userObj->hasPermission('dashboard-god-view')){ ?>
                        <div class="card_body grid-item">
                            <div class="card_heading_row god_view have_three">
                                <strong>God's View</strong>

                                <?php if (!in_array($APP_TYPE,['UberX','Ride','Delivery'])){ ?>
                                    <ul class="tab_row">
                                        <?php if ($MODULES_OBJ->isRideFeatureAvailable('Yes') && strtoupper($APP_TYPE) != "RIDE"){ ?>
                                            <li class="active" onclick="SetServiceType('Ride')">Rides</li>

                                        <?php }
                                        if (($MODULES_OBJ->isDeliveryFeatureAvailable('Yes') || $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') || $MODULES_OBJ->isEnableGenieFeature('Yes') || $MODULES_OBJ->isEnableRunnerFeature()) && strtoupper($APP_TYPE) != "RIDE" && !$MODULES_OBJ->isOnlyDeliverAllSystem()){ ?>
                                            <li onclick="SetServiceType('Delivery')">Deliveries</li>

                                        <?php }
                                        if ($MODULES_OBJ->isUberXFeatureAvailable('Yes') && strtoupper($APP_TYPE) != "UBERX"){ ?>
                                            <li onclick="SetServiceType('Job')">Jobs</li>

                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </div>
                            <ul class="admin_card_row god_view five_box">

                                <li class="active" status="available" onclick="SetServiceStatus('Available')">
                                    <!------------------icons----------------->
                                    <i class="Ride_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/bus.svg" alt="">
                                    </i>
                                    <i class="Delivery_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/Available_Deliver.svg" alt="">
                                    </i>

                                    <i class="Job_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/Available_Job.svg" alt="">
                                    </i>
                                    <!------------------icons----------------->
                                    <small>
                                        <span>Available</span>
                                        <div class="provider-count" id="available_count"></div>
                                    </small>
                                </li>

                                <li status="all" onclick="SetServiceStatus('Not Available')">

                                    <!------------------icons----------------->
                                    <i class="Ride_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/truck.svg" alt="">
                                    </i>
                                    <i class="Delivery_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/All_Deliver.svg" alt="">
                                    </i>

                                    <i class="Job_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/All_Job.svg" alt="">
                                    </i>
                                    <!------------------icons----------------->
                                    <small>
                                        <span>Not Available</span>
                                        <div class="provider-count" id="not_available_count"></div>
                                    </small>
                                </li>

                                <li status="pickup" onclick="SetServiceStatus('Pickup')">
                                    <!------------------icons----------------->
                                    <i class="Ride_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/destination.svg" alt="">
                                    </i>
                                    <i class="Delivery_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/PickUp_Deliver.svg" alt="">
                                    </i>

                                    <i class="Job_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/PickUp_Job.svg" alt="">
                                    </i>
                                    <!------------------icons----------------->

                                    <small>
                                        <span id="status-title-pickup">Way to Pickup</span>
                                        <div class="provider-count" id="pickup_count"></div>
                                    </small>
                                </li>
                                <li status="arrived" onclick="SetServiceStatus('Arrived')">

                                    <i>
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/arrived-destination.svg" alt="">
                                    </i>
                                    <small>
                                        <span>Arrived / Reached Pickup</span>
                                        <div class="provider-count" id="arrived_count"></div>
                                    </small>
                                </li>
                                <li status="ongoing" onclick="SetServiceStatus('OnGoing')">

                                    <!------------------icons----------------->
                                    <i class="Ride_icons GodViewIcons">
                                        <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/destination.svg" alt=""></i>
                                    </i>
                                    <i class="Delivery_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/WayToPickUp_Deliver.svg" alt="">
                                    </i>

                                    <i class="Job_icons GodViewIcons">
                                        <img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/OnGoing_Job.svg" alt="">
                                    </i>
                                    <!------------------icons----------------->
                                    <small>
                                        <span id="status-title-ongoing">Way to Dropoff</span>
                                        <div class="provider-count" id="dropoff_count"></div>
                                    </small>
                                </li>
                            </ul>
                            <div class="overlay-map-helper">
                                <div class="overlay-map">
                                    <div class="overlay-map-content">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                                <div id="god_view_map"></div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($userObj->hasPermission('dashboard-member-statistics')){ ?>
                        <div class="admin_card_row statistics flex_row grid-item">
                            <?php foreach ($memberStatsArr as $k => $memberStat){ ?>
                                <div class="card_body pointer RedirectUrl"
                                        link-target="<?=$tconfig['tsite_url_main_admin']?><?=$memberStat['Link'];?>">

                                    <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/<?=$memberStat['vImage'];?>"
                                                alt=""></i>

                                    <small class="color<?=$k + 1;?>"><?=$memberStat['vTitle'];?></small>
                                    <strong class="color<?=$k + 1;?>"><?=$memberStat['Total'];?></strong>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if ($isRideFeatureAvailable || $isUberXFeatureAvailable || $isDeliveryFeatureAvailable){ ?>
                        <div class="grid-item card_body">
                            <div class="card_heading_row">

                                <strong><?php echo $SERVICES_STATISTICS; ?></strong>

                                <ul class="tab_row  ">
                                    <li attr-options="TODAY" class="active Service_Options">Today</li>
                                    <li attr-options="TOTAL" class="Service_Options">Total</li>
                                </ul>
                            </div>
                            <div class="admin_card_row _50_50" id="Service_Total">
                                <div class="admin_column">
                                    <ul class="listing_style1">
                                        <?php foreach ($tripsJobsStatsArr as $k => $tripsJobsStat){ ?>
                                            <li

                                                <?php if ($ON_DEMAND_SERVICE_LINK_SHOW == "No" && $onlyOneService == "No"){ ?>  link-target="<?=$tconfig['tsite_url_main_admin'].$tripsJobsStat['Link']?>"  <?php } ?>
                                                    class="service_box   pointer

                                                    <?php if ($onlyOneService == "Yes"){ ?> ClickNotAllow  <?php } ?>
                                                    <?php if ($ON_DEMAND_SERVICE_CLICK_LOAD_CHART == "No" && $onlyOneService == "No"){ ?> ClickNotAllow RedirectUrl  <?php } ?>

                                <?php if ($k == 0 && $ON_DEMAND_SERVICE_LINK_SHOW == "Yes"){
                                                        echo "service_active";
                                                    } ?> "
                                                    id="<?=$tripsJobsStat['Type'];?>">
                                                <div>
                                                    <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/<?=$tripsJobsStat['vImage'];?>"
                                                                alt=""></i>
                                                    <b><?=$tripsJobsStat['vTitle'];?></b>
                                                </div>

                                                <span
                                                <?php
                                                if ($ON_DEMAND_SERVICE_LINK_SHOW == "Yes" || $onlyOneService == "Yes"){ ?>
                                                    class="span-flex"
                                                <?php } ?> >

                                                <strong class="color<?=$k + 1;?> Service_Options_Today"><?=$tripsJobsStat['Today'];?></strong>
                                                <strong style="display:none"
                                                        class="color<?=$k + 1;?> Service_Options_Total "><?=$tripsJobsStat['Total'];?></strong>


                                                <?php if ($ON_DEMAND_SERVICE_LINK_SHOW == "Yes" || $onlyOneService == "Yes"){ ?>
                                                    <a link-target="<?=$tconfig['tsite_url_main_admin'].$tripsJobsStat['Today_Link']?>"
                                                            class="common_button RedirectUrl pointer Service_Options_Today">View All</a>

                                                    <a style="display:none"
                                                            link-target="<?=$tconfig['tsite_url_main_admin'].$tripsJobsStat['Link']?>"
                                                            class="common_button RedirectUrl pointer Service_Options_Total">View All</a>
                                                <?php } ?>
                                            </span>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="admin_column">
                                    <?php foreach ($tripsJobsStatsArr as $k => $tripsJobsStat){ ?>
                                        <div class="service_chart" <?php if ($k != 0){
                                            echo 'style="display:none"';
                                        } ?> id="<?php echo strtoupper($tripsJobsStat['Type']); ?>_CHART">
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                    <?php } ?>


                    <?php if ($userObj->hasPermission('admin-earning-dashboard')){ ?>
                        <div class="card_body grid-item">
                            <div class="card_heading_row">
                                <strong><?php echo $AdminEarning['Title']; ?></strong>
                                <ul class="tab_row">
                                    <li attr-options="TODAY" class="active Admin_Earning_Options">Today</li>
                                    <li attr-options="TOTAL" class="Admin_Earning_Options">Total</li>
                                </ul>
                            </div>

                            <div id="ADMIN_EARNINGS_CHART" class="admin_earnings_chart chart_v4"></div>
                            <ul class="listing_style1 margin-top-15">
                                <?php foreach ($AdminEarningStatsArr as $k => $AdminEarningStats){ ?>
                                    <li

                                            link-target="<?=$tconfig['tsite_url_main_admin'].$AdminEarningStats['Link']?>"

                                            class="  <?php if ($k == 0){
                                                echo "";
                                            } ?> " id="<?=$AdminEarningStats['Type'];?>">

                                        <div>
                                            <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/<?=$AdminEarningStats['vImage'];?>"
                                                        alt=""></i>
                                            <b class="f-w-600"><?=$AdminEarningStats['vTitle'];?>
                                                <small><?=$AdminEarningStats['vTitleSubTitle'];?> </small></b>

                                        </div>

                                        <strong class="color<?=$k + 1;?> admin_earning_options_today"><?=$AdminEarningStats['Today'];?>

                                        </strong>

                                        <strong style="display:none"
                                                class="color<?=$k + 1;?> admin_earning_options_total "><?=$AdminEarningStats['Total'];?></strong>

                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <?php if ($isEnableVideoConsultingServices || $isEnableBiddingServices){ ?>
                        <div class="admin_card_row flex_row  grid-item">

                            <?php if ($userObj->hasPermission('dashboard-video-consultation') && $isEnableVideoConsultingServices){ ?>
                                <div class="card_body">
                                    <div class="card_heading_row min-height-66">
                                        <strong><?php echo $Video_Consult_Data['Title']; ?></strong>

                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Video_Consult_Data['Link']?>"
                                                class="video_consultation_total common_button RedirectUrl pointer">View All</a>

                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Video_Consult_Data['Today_Link']?>"
                                                class="video_consultation_today common_button RedirectUrl pointer">View All</a>

                                    </div>
                                    <strong class="common-stat color6 video_consultation_today"><?php echo $Video_Consult_Data['data']['Today']; ?></strong>
                                    <strong class="common-stat color6 video_consultation_total"><?php echo $Video_Consult_Data['data']['Total']; ?></strong>
                                    <div id="VIDEO_CONSULTATION_CHART" class="chart_v4"></div>
                                    <ul class="tab_row justify_content_center">
                                        <li attr-options="TODAY" class="active video_consultation_options">
                                            Today
                                        </li>
                                        <li attr-options="TOTAL" class="video_consultation_options">Total</li>
                                    </ul>
                                </div>
                            <?php } ?>

                            <?php if ($userObj->hasPermission('dashboard-bid-services') && $isEnableBiddingServices){ ?>
                                <div class="card_body">
                                    <div class="card_heading_row min-height-66">
                                        <strong><?php echo $Bidding_Data['Title']; ?></strong>
                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Bidding_Data['Link']?>"
                                                class="bid_post_total common_button RedirectUrl pointer">View All</a>
                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Bidding_Data['Today_Link']?>"
                                                class="bid_post_today common_button RedirectUrl pointer">View All</a>
                                    </div>
                                    <strong class="common-stat color5 bid_post_today ">
                                        <?php echo $Bidding_Data['data']['Today']; ?>
                                    </strong>
                                    <strong class="common-stat color5 bid_post_total ">
                                        <?php echo $Bidding_Data['data']['Total']; ?>
                                    </strong>
                                    <div id="BID_POST_CHART">

                                    </div>
                                    <ul class="tab_row justify_content_center">
                                        <li attr-options="TODAY" class="active bid_post_options">Today</li>
                                        <li attr-options="TOTAL" class="bid_post_options">Total</li>
                                    </ul>
                                </div>
                            <?php } ?>

                        </div>
                    <?php } ?>
                    <?php if ($isEnableRideShareService){ ?>
                        <div class="grid-item card_body">
                            <div class="card_heading_row">
                                <strong>Ride Share <small>No. of Rides</small></strong>
                                <ul class="tab_row">
                                    <li attr-options="TODAY" class="active RideShare_Options ">Today</li>
                                    <li attr-options="TOTAL" class="RideShare_Options">Total</li>
                                </ul>
                            </div>
                            <div class="admin_card_row _50_50">
                                <div class="admin_column">
                                    <ul class="listing_style1">
                                        <?php foreach ($RideShareStatsArr as $k => $RideShareStats){ ?>
                                            <li link-target="<?=$tconfig['tsite_url_main_admin'].$RideShareStats['Link']?>"
                                                    class="RedirectUrl RideShare_box  pointer  <?php if ($k == 0){
                                                        echo "RideShare_active";
                                                    } ?> " id="<?=$RideShareStats['Type'];?>">
                                                <div>
                                                    <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/<?=$RideShareStats['vImage'];?>"
                                                                alt=""></i>
                                                    <b><?=$RideShareStats['vTitle'];?></b>
                                                </div>
                                                <strong class="<?=$RideShareStats['color']?> RideShare_Options_Today"><?=$RideShareStats['Today'];?></strong>
                                                <strong style="display:none"
                                                        class="<?=$RideShareStats['color']?> RideShare_Options_Total "><?=$RideShareStats['Total'];?></strong>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="admin_column">
                                    <div class="RideShare_chart" id="RIDE_SHARE_CHART"></div>

                                </div>
                            </div>
                        </div>

                    <?php } ?>
                    <?php if ($isRideFeatureAvailableForRecentTrip || $isDeliveryFeatureAvailableForRecentTrip || $isUberXFeatureAvailableForRecentTrip){ ?>

                        <div class="card_body grid-item" id="recentTripJob">
                            <div class="card_heading_row">
                                <strong>
                                    <?php echo $RecentRideArr['title']; ?> <small>
                                        <?php echo $RecentRideArr['Subtitle']; ?> </small>
                                </strong>
                                <a link-target="<?=$tconfig['tsite_url_main_admin'].$RecentRideArr['Link']?>"
                                        class="common_button RedirectUrl pointer">View All</a>
                            </div>

                            <?php if (scount($RecentRideArr['Tab']) > 1){ ?>
                                <ul class="tab_new" <?php if (ONLY_MEDICAL_SERVICE == 'Yes'){ ?>

                                    style="grid-template-columns: 2fr 2fr;"
                                <?php }
                                ?> >
                                    <?php foreach ($RecentRideArr['Tab'] as $key => $Tab){ ?>
                                        <li class="<?php echo ($key == 0)?"active":""; ?>"
                                                data-id="<?php echo $Tab['type']; ?>"><?php echo $Tab['Title']; ?> </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>

                            <?php $i = 0;
                            foreach ($RecentRideArr['Data'] as $key => $datas){

                                ?>
                                <div class="earning_block recentTrip_block <?php echo ($i == 0) ? "active" : "" ; ?>"
                                        id="<?php echo $key; ?>">
                                    <?php if (scount($datas) > 0){
                                        foreach ($datas as $sKey => $data){ ?>
                                            <div class="card_booking_row">
                                                <div>
                                                    <small>Booking ID</small>
                                                    <a href="<?=$tconfig['tsite_url_main_admin']?>trip.php?serachTripNo=<?=$data['vRideNo']?>"
                                                            target="_blank"><strong>#<?php echo $data['vRideNo']; ?></strong></a>
                                                </div>

                                                <a href="javascript:void(0)"
                                                        class="status_button <?php echo $data['class']; ?> "> <?php echo $data['service_status']; ?></a>
                                            </div>
                                            <ul class="listing_style1 two_column recentTrip___list">
                                                <li>
                                                    <div>
                                                        <i><img src="<?php echo $dashboardIcon.$data['Image']['user_profile'] ?>"
                                                                    alt=""></i>
                                                        <b>User Name
                                                            <br>
                                                            <small title="<?php echo clearName($data['User_UserName']); ?>"
                                                                    data-toggle="tooltip" data-placement="top">
                                                                <?php echo clearName($data['User_UserName']); ?>
                                                            </small> <small> |</small>
                                                            <span>  <label
                                                                        class="ri-star-fill"></label> <?php echo $data['User_vAvgRating']; ?></span>
                                                        </b>

                                                    </div>
                                                </li>
                                                <li>
                                                    <div>
                                                        <i><img src="<?php echo $dashboardIcon.$data['Image']['document'] ?>"
                                                                    alt="">
                                                        </i>
                                                        <b><?=$data['provider_title']?> Name
                                                            <br>
                                                            <small title="<?php echo clearName($data['Driver_UserName']); ?>"
                                                                    data-toggle="tooltip" data-placement="top">
                                                                <?php echo clearName($data['Driver_UserName']); ?>
                                                            </small>
                                                            <small> |</small>
                                                            <span><label
                                                                        class="ri-star-fill"></label> <?php echo $data['Driver_vAvgRating']; ?></span>
                                                        </b>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div>
                                                        <i><img src="<?php echo $dashboardIcon.$data['Image']['setting'] ?>"
                                                                    alt=""></i>
                                                        <b>Service Type

                                                            <br>

                                                            <?php
                                                            if ($key == "Jobs"){ ?>

                                                                <a class="common_button"
                                                                        onclick="return getServiceDetails(<?=$data['iTripId'];?>,0,'showServiceModalV2');">
                                                                    View Services
                                                                </a>
                                                                <?php
                                                            }else{ ?>
                                                                <small><?php echo $data['service_type']; ?></small>
                                                            <?php } ?>
                                                        </b>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div>
                                                        <i><img src="<?php echo $dashboardIcon.$data['Image']['date'] ?>"
                                                                    alt=""></i>
                                                        <b>
                                                            <?php echo $data['dBooking_date_format']; ?>
                                                            <br>
                                                            <small> <?php echo $data['dBooking_time_format']; ?> </small>
                                                        </b>
                                                    </div>
                                                </li>
                                            </ul>
                                            <?php if ($sKey < scount($datas) - 1){ ?>
                                                <hr>
                                            <?php } ?>
                                        <?php }
                                    } else { ?>
                                        <div class="no_data_div"><p>You will find the latest <?=$key?>here</p>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php $i++;
                            } ?>
                        </div>
                    <?php } ?>

                    <?php if ($isDeliverAllFeatureAvailable || $isEnableGenieFeature || $isEnableRunnerFeature){ ?>
                        <div class="admin_card_row flex_row  grid-item">
                            <?php if ($isDeliverAllFeatureAvailable){ ?>
                                <div class="card_body">
                                    <div class="card_heading_row">
                                        <strong><?php echo $Store_Data['Title']; ?></strong>
                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Store_Data['Link']?>"
                                                class="common_button RedirectUrl pointer store_deliveries_total ">View All</a>

                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Store_Data['Today_Link']?>"
                                                class="common_button RedirectUrl pointer store_deliveries_today ">View All</a>

                                    </div>
                                    <strong class="common-stat color4 store_deliveries_today "><?php echo $Store_Data['data']['Today']; ?></strong>
                                    <strong class="common-stat color4 store_deliveries_total "><?php echo $Store_Data['data']['Total']; ?></strong>
                                    <div id="STORE_DELIVERIES_CHART" class="store_deliveries_chart chart_v4"></div>
                                    <ul class="tab_row justify_content_center">
                                        <li attr-options="TODAY" class="active store_deliveries_options">Today
                                        </li>
                                        <li attr-options="TOTAL" class="store_deliveries_options">Total</li>
                                    </ul>
                                </div>
                            <?php } ?>

                            <?php if ($isEnableGenieFeature || $isEnableRunnerFeature){ ?>
                                <div class="card_body">
                                    <div class="card_heading_row">
                                        <strong><?php echo $Genie_Runner_Data['Title']; ?></strong>
                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Genie_Runner_Data['Link']?>"
                                                class="common_button RedirectUrl pointer genie_runner_deliveries_total ">View All</a>

                                        <a link-target="<?=$tconfig['tsite_url_main_admin'].$Genie_Runner_Data['Today_Link']?>"
                                                class="common_button RedirectUrl pointer genie_runner_deliveries_today ">View All</a>

                                    </div>
                                    <strong class="common-stat color1 genie_runner_deliveries_today ">
                                        <?php echo $Genie_Runner_Data['data']['Today']; ?>
                                    </strong>
                                    <strong class="common-stat color1 genie_runner_deliveries_total ">
                                        <?php echo $Genie_Runner_Data['data']['Total']; ?>
                                    </strong>
                                    <div id="GENIE_RUNNER_DELIVERIES_CHART"
                                            class="genie_runner_deliveries_chart chart_v4"></div>
                                    <ul class="tab_row justify_content_center">
                                        <li attr-options="TODAY" class="active genie_runner_deliveries_options">
                                            Today
                                        </li>
                                        <li attr-options="TOTAL" class="genie_runner_deliveries_options">Total
                                        </li>
                                    </ul>
                                </div>
                            <?php } ?>

                        </div>
                    <?php } ?>

                    <?php if (($isEnableRentItemService || $isEnableRentCarsService || $isEnableRentEstateService)){ ?>
                        <div class="grid-item card_body">
                            <div class="card_heading_row">
                                <strong>Buy, Sell & Rent <small>No. of posts</small></strong>
                                <ul class="tab_row">

                                    <li attr-options="TODAY" class="active buy_sell_rent_options">Today</li>
                                    <li attr-options="TOTAL" class="buy_sell_rent_options">Total</li>
                                </ul>
                            </div>
                            <div class="admin_card_row _50_50">
                                <div class="admin_column">
                                    <ul class="listing_style1">

                                        <?php foreach ($BuySellRentStatsArr as $k => $BuySellRentStats){ ?>
                                            <li
                                                    link-target="<?=$BuySellRentStats['Link'];?>"

                                                    class="pointer RedirectUrl" id="<?=$BuySellRentStats['Type'];?>">

                                                <div>
                                                    <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/<?=$BuySellRentStats['vImage'];?>"
                                                                alt=""></i>
                                                    <b><?=$BuySellRentStats['vTitle'];?></b>
                                                </div>
                                                <strong class="color<?=$k + 1;?> buy_sell_rent_options_Today"><?=$BuySellRentStats['Today'];?></strong>
                                                <strong style="display:none"
                                                        class="color<?=$k + 1;?> buy_sell_rent_options_Total "><?=$BuySellRentStats['Total'];?></strong>

                                            </li>
                                        <?php } ?>

                                    </ul>
                                </div>
                                <div class="admin_column">
                                    <div class="BuySellRent_chart" id="BUY_SELL_RENT_CHART">
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                    <?php if ($userObj->hasPermission('manage-server-admin-dashboard')){ ?>
                        <div class="grid-item card_body server_statistics">
                            <div class="card_heading_row">
                                <strong>
                                    Server Statistics
                                    <small>Last Updated: <?=date('d M Y')." AT ".date('h:i A')?> </small>
                                </strong>
                                <?php if (SITE_TYPE != "Demo"){ ?>
                                    <a link-target="<?=$tconfig['tsite_url_main_admin'].'server_admin_dashboard.php'?>"
                                            class="common_button RedirectUrl pointer">View</a>
                                <?php } ?>
                            </div>
                            <div id="server_loader" class="no_data_div">
                                <img class="chart_loader" src="<?php echo $chartLoader; ?>">
                            </div>
                            <div id="server_main" class=" admin_card_row align_data_senter _50_50">
                                <div class="admin_column">
                                    <ul class="listing_style1">
                                        <li class="hover_remove">
                                            <div>
                                                <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/check.svg"
                                                            alt=""></i>
                                                <b>Working</b>
                                            </div>
                                            <strong id="server_working" class="color2"></strong>
                                        </li>
                                        <li class="hover_remove">
                                            <div>
                                                <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/error.svg"
                                                            alt=""></i>
                                                <b>Errors</b>
                                            </div>
                                            <strong id="server_missing" class="color4"></strong>
                                        </li>
                                        <li class="hover_remove">
                                            <div>
                                                <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/alert.svg"
                                                            alt=""></i>
                                                <b>Alerts</b>
                                            </div>
                                            <strong class="color3" id="server_alerts"></strong>
                                        </li>
                                    </ul>
                                </div>
                                <div class="admin_column">
                                    <div class=" server_chart" id="SERVER_CHART">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($isEnableScheduledRideFlow && $userObj->hasPermission('later-bookings-dashboard')){ ?>
                        <div class="grid-item card_body scheduled__body">
                            <div class="card_heading_row">
                                <strong><?php echo $ScheduledRideArr['title']; ?></strong>
                                <a link-target="<?php echo $ScheduledRideArr['Link']; ?>"
                                        class="common_button pointer RedirectUrl">View All</a>
                            </div>
                            <?php
                            if (isset($ScheduledRideArr['Data']) && !empty($ScheduledRideArr['Data'])){
                                $i = 0;
                                foreach ($ScheduledRideArr['Data'] as $Scheduled_Booking){ ?>

                                    <div class="scheduled_booking_recoard" <?php if ($i != 0){
                                        echo 'style="display:none"';
                                    } ?> id="<?php echo $Scheduled_Booking['vBookingNo']; ?>">
                                        <div class="card_booking_row">
                                            <div>
                                                <small>Booking ID</small>
                                                <a href="<?=$Scheduled_Booking['link']?>"
                                                        target="_blank"><strong>#<?php echo $Scheduled_Booking['vBookingNo']; ?> </strong></a>
                                            </div>
                                            <a href="javascript:void(0);"
                                                    class="status_button <?php echo $Scheduled_Booking['eStatus_class']; ?>"><?php echo $Scheduled_Booking['eStatus']; ?></a>
                                        </div>
                                        <ul class="listing_style1 two_column recentTrip___list schedule__list">
                                            <li>
                                                <div>
                                                    <i><img src="<?php echo $dashboardIcon.$ScheduledRideArr['Image']['user_profile'] ?>"
                                                                alt=""></i>
                                                    <b>User Name <br>
                                                        <small title="<?php echo clearName($Scheduled_Booking['User_UserName']); ?>"
                                                                data-toggle="tooltip"
                                                                data-placement="top"><?php echo clearName($Scheduled_Booking['User_UserName']); ?>
                                                            |</small>
                                                        <span><label
                                                                    class="ri-star-fill"></label> <?php echo $Scheduled_Booking['User_vAvgRating']; ?></span>
                                                    </b>
                                                </div>
                                            </li>
                                            <li>
                                                <div>
                                                    <i><img src="<?php echo $dashboardIcon.$ScheduledRideArr['Image']['document'] ?>"
                                                                alt="">
                                                    </i>
                                                    <b>Provider Name
                                                        <br>
                                                        <?php if (isset($Scheduled_Booking['Driver_UserName']) && !empty($Scheduled_Booking['Driver_UserName'])){ ?>
                                                            <small title="<?php echo clearName($Scheduled_Booking['Driver_UserName']); ?>"
                                                                    data-toggle="tooltip" data-placement="top">
                                                                <?php echo clearName($Scheduled_Booking['Driver_UserName']); ?>
                                                                |
                                                            </small>
                                                            <span><label class="ri-star-fill"></label>
                                                <?php echo $Scheduled_Booking['Driver_vAvgRating']; ?></span>

                                                        <?php }else{ ?>
                                                            <small>
                                                                Not Assign Yet.
                                                            </small>
                                                        <?php } ?>

                                                    </b>
                                                </div>
                                            </li>
                                            <li>
                                                <div>
                                                    <i><img src="<?php echo $dashboardIcon.$ScheduledRideArr['Image']['setting'] ?>"
                                                                alt=""></i>
                                                    <b>Service Type
                                                        <br>

                                                        <?php if ($Scheduled_Booking['eType'] == 'UberX'){ ?>

                                                            <a class="common_button"
                                                                    onclick="return getServiceDetails(0,'<?=$Scheduled_Booking['iCabBookingId'];?>','showServiceModalV2');">
                                                                View Services
                                                            </a>
                                                        <?php }else{ ?>

                                                            <small> <?php echo $Scheduled_Booking['iVehicleTypeId']; ?> </small>

                                                        <?php } ?>

                                                    </b>
                                                </div>
                                            </li>
                                            <li>
                                                <div>
                                                    <i><img src="<?php echo $dashboardIcon.$ScheduledRideArr['Image']['date'] ?>"
                                                                alt=""></i>
                                                    <b>
                                                        <?php echo $Scheduled_Booking['dBooking_date_format']; ?>
                                                        <br>
                                                        <small> <?php echo $Scheduled_Booking['dBooking_time_format']; ?> </small>
                                                    </b>
                                                </div>
                                            </li>
                                            <li>
                                                <div>
                                                    <i><img src="<?php echo $dashboardIcon.$ScheduledRideArr['Image']['location'] ?>"
                                                                alt="">
                                                    </i><b>Address
                                                        <br>
                                                        <small class="d_block"
                                                                title="<?php echo $Scheduled_Booking['vSourceAddresss']; ?>"
                                                                data-toggle="tooltip"
                                                                data-placement="top"> <?php echo $Scheduled_Booking['vSourceAddresss']; ?></small></b>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <?php
                                    $i++;
                                }
                                ?>
                                <div class="scheduled_booking_pagination_main">
                                    <?php
                                    $i = 0;
                                    foreach ($ScheduledRideArr['Data'] as $Scheduled_Booking){ ?>

                                        <span class=" scheduled_booking_pagination pointer <?php if ($i == 0){
                                            echo 'color2';
                                        } ?> "
                                                attr-vBookingNo="<?php echo $Scheduled_Booking['vBookingNo']; ?>"><i
                                                    class="ri-checkbox-blank-circle-fill"
                                                    aria-hidden="true"></i></span>
                                        <?php $i++;
                                    } ?>

                                </div>

                            <?php }else{ ?>
                                <div class="no_data_div"><p>No Scheduled Booking Request</p></div>
                            <?php }
                            ?>

                        </div>
                    <?php } ?>

                    <?php if ($userObj->hasPermission('dashboard-notifications-alerts-panel')){ ?>
                        <div class="grid-item card_body">
                            <div class="card_heading_row">
                                <strong>Notification Alerts Panel</strong>
                                <?php if ($userObj->hasPermission('dashboard-notifications-alerts-panel') && scount($db_notification) > 0 && !empty($db_notification)){ ?>
                                    <a href="notificationlist.php" target="_blank"
                                            class="common_button pointer ">View All</a>
                                <?php } ?>
                            </div>
                            <ul class="listing_style1 have_a_tag notifications_list">
                                <?php $text_color = 0;
                                if (scount($db_notification) > 0 && !empty($db_notification)){
                                    for ($i = 0;$i < scount($db_notification);$i++){
                                        if ($db_notification[$i]['doc_name_'.$default_lang] != ''){
                                            $text_color++;
                                            ?>
                                            <li>
                                                <?php
                                                $url = "#";
                                                if ($db_notification[$i]['doc_usertype'] == 'driver'){
                                                    $url = $LOCATION_FILE_ARRAY['DRIVER_DOCUMENT_ACTION'];
                                                    $viewpermission = "view-providers";
                                                    $id = $db_notification[$i]['iDriverId'];
                                                    if ($db_notification[$i]['doc_name_'.$default_lang] != ''){
                                                        $msg = strtoupper($db_notification[$i]['doc_name_'.$default_lang])." uploaded by ".$langage_lbl['LBL_DRIVER_TXT_ADMIN']."";
                                                        $name = clearName($db_notification[$i]['Driver']);
                                                    }else{
                                                        $msg = $db_notification[$i]['doc_name_'.$default_lang]." uploaded by ".$langage_lbl['LBL_DRIVER_TXT_ADMIN']."";
                                                        $name = clearName($db_notification[$i]['Driver']);
                                                    }
                                                }else if ($db_notification[$i]['doc_usertype'] == 'company'){
                                                    $url = "company_document_action.php";
                                                    $viewpermission = "view-company";
                                                    $id = $db_notification[$i]['iCompanyId'];
                                                    if ($db_notification[$i]['doc_name_'.$default_lang] != ''){
                                                        $msg = strtoupper($db_notification[$i]['doc_name_'.$default_lang])." uploaded by ".$db_notification[$i]['doc_usertype']."";
                                                        $name = clearCmpName($db_notification[$i]['vCompany']);
                                                    }else{
                                                        $msg = $db_notification[$i]['doc_name_'.$default_lang]." uploaded by ".$db_notification[$i]['doc_usertype']."";
                                                        $name = clearCmpName($db_notification[$i]['vCompany']);
                                                    }
                                                }else if ($db_notification[$i]['doc_usertype'] == 'car'){
                                                    $url = "vehicle_document_action.php";
                                                    $viewpermission = "edit-provider-vehicles-document";
                                                    $id = $db_notification[$i]['iDriverVehicleId'];
                                                    if ($db_notification[$i]['doc_name_'.$default_lang] != ''){
                                                        $msg = strtoupper($db_notification[$i]['doc_name_'.$default_lang])." uploaded by ".$langage_lbl['LBL_DRIVER_TXT_ADMIN']."";
                                                        $name = clearName($db_notification[$i]['DriverName']);
                                                    }else{
                                                        $msg = $db_notification[$i]['doc_name_'.$default_lang]." uploaded by ".$langage_lbl['LBL_DRIVER_TXT_ADMIN']."";
                                                        $name = clearName($db_notification[$i]['DriverName']);
                                                    }
                                                }else if ($db_notification[$i]['doc_usertype'] == 'store'){
                                                    $url = "store_document_action.php";
                                                    $viewpermission = "edit-store";
                                                    $id = $db_notification[$i]['iCompanyId'];
                                                    if ($db_notification[$i]['doc_name_'.$default_lang] != ''){
                                                        $msg = strtoupper($db_notification[$i]['doc_name_'.$default_lang])." uploaded by ".$db_notification[$i]['doc_usertype']."";
                                                        $name = clearCmpName($db_notification[$i]['vCompany']);
                                                    }else{
                                                        $msg = $db_notification[$i]['doc_name_'.$default_lang]." uploaded by ".$db_notification[$i]['doc_usertype']."";
                                                        $name = clearCmpName($db_notification[$i]['vCompany']);
                                                    }
                                                }
                                                ?>
                                                <?php if ($userObj->hasPermission($viewpermission)) { ?>
                                                <a href="<?=$url;?>?id=<? echo $id; ?>&action=edit"
                                                        target="_blank">
                                                    <?php } else { ?>
                                                    <a href="javascript:void(0)">
                                                        <?php } ?>
                                                        <div>
                                                            <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/notification.svg"
                                                                        alt=""></i>
                                                            <b><?php echo $name; ?> <br> <small
                                                                        title="<?=$msg;?>"
                                                                        data-toggle="tooltip"
                                                                        data-placement="top"><?=$msg;?></small></b>
                                                        </div>
                                                        <strong class="color<?=$text_color?>"><?=humanReadableTimingDashboard($db_notification[$i]['edate']);?></strong>
                                                    </a>

                                            </li>
                                        <?php }
                                    }
                                }else{ ?>
                                    <div class="no_data_div">
                                        <p>You will find the most recent notifications regarding documents
                                            uploaded by members here.</p>
                                    </div>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')){ ?>
                        <div class="grid-item card_body min-height-400">
                            <div class="card_heading_row">
                                <strong>Contact Us Form Requests</strong>
                                <?php if ($userObj->hasPermission('view-contactus-report') && scount($latest_contactus) > 0 && !empty($latest_contactus)){ ?>
                                    <a target="_blank" href="contactus.php" class="common_button">View All</a>
                                <?php } ?>
                            </div>
                            <ul class="listing_style1 have_a_tag Contact_Us contact_us_list">
                                <?php $text_color = 0; ?>
                                <?php if (scount($latest_contactus) > 0 && !empty($latest_contactus)){
                                    for ($i = 0;$i < scount($latest_contactus);$i++){
                                        $text_color++;
                                        ?>
                                        <li>
                                            <?php $tRequestDate = date("Y-m-d",strtotime($latest_contactus[$i]['tRequestDate']));
                                            $queryString = "?action=search&iContactusId=".$latest_contactus[$i]['iContactusId'];
                                            ?>
                                            <?php if ($userObj->hasPermission('view-contactus-report')) { ?>
                                            <a href="contactus.php<?=$queryString?>" target="_blank">
                                                <?php } else { ?>
                                                <a href="#">
                                                    <?php } ?>
                                                    <div>
                                                        <i><img src="<?=$tconfig['tsite_url_main_admin']?>img/icon/notification.svg"
                                                                    alt=""></i>
                                                        <b><?php echo clearName(validName($latest_contactus[$i]['vFirstname'].' '.$latest_contactus[$i]['vLastname'])); ?>
                                                            <br>
                                                            <small title="<?=clearGeneralText(removehtml($latest_contactus[$i]['tDescription']));?>"
                                                                    data-toggle="tooltip"
                                                                    data-placement="top"> <?=clearGeneralText(removehtml($latest_contactus[$i]['tDescription']));?></small>
                                                        </b>
                                                    </div>
                                                    <strong class="color<?php echo $text_color; ?>"><?=humanReadableTimingDashboard($latest_contactus[$i]['tRequestDate']);?></strong>
                                                    <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')) { ?>
                                                </a>
                                                <?php } else { ?>
                                            </a>
                                        <?php } ?>
                                        </li>
                                    <?php }
                                }else{ ?>
                                    <div class="no_data_div"><p>You will find the latest inquiries from your
                                            customers here.</p></div>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>

            </div>

        <?php }else{ ?>
            <div class="cintainerinner">
                <div class="WelcomeBox"><h1>Welcome To <?php echo $GROUP_DATA['GroupName']; ?></h1></div>
            </div>
        <?php } ?>

        <?php include_once('footer.php'); ?>
    </div>
</div>

<?php
include_once 'service_details.php';
?>

<?php if ($MODULES_OBJ->isRideFeatureAvailable('Yes')){ ?>
    <input type="hidden" name="eServiceType" id="eServiceType" value="Ride">
<?php }elseif ($MODULES_OBJ->isDeliveryFeatureAvailable('Yes') || $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')){ ?>
    <input type="hidden" name="eServiceType" id="eServiceType" value="Delivery">
<?php }else{ ?>
    <input type="hidden" name="eServiceType" id="eServiceType" value="Job">
<?php } ?>
<input type="hidden" name="eServiceStatus" id="eServiceStatus" value="Available">
<input type="hidden" name="page_loaded" id="page_loaded" value="Yes">
<style>
    .chart_v4 {
        height: 275px !important;
    }

    .no_data_div {
        height: 328px !important;
    }

    .provider-count {
        height: 21px !important;
    }
</style>
<script type="text/javascript" src="<?=$tconfig["tsite_url"];?>assets/libraries/scClient-js/socketcluster-client.js"></script>
<script type="text/javascript" src="<?=$tconfig["tsite_url"];?>assets/js/socketclustercls.js"></script>
<script src="https://maps.google.com/maps/api/js?key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&libraries=geometry" type="text/javascript"></script>

<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/jquery_easing.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js" defer></script>

<script>
    var seriesData = <?php echo  isset($scriptArr) ? json_encode($scriptArr) : '[]'; ?>;
    var seriesDataTotal = <?php echo isset($scriptArrTotal) ? json_encode($scriptArrTotal) : '[]'; ?>;
    var RideShareData = <?php echo isset($RideSharescriptArr) ?  json_encode($RideSharescriptArr) : '[]'; ?>;
    var RideShareDataTotal = <?php echo isset($RideShareArrTotal) ? json_encode($RideShareArrTotal) : '[]'; ?>;
    var BuySellRentData = <?php echo isset($BuySellRentscriptArr) ? json_encode($BuySellRentscriptArr) : '[]'; ?>;
    var BuySellRentDataTotal = <?php echo isset($BuySellRentArrTotal) ?  json_encode($BuySellRentArrTotal) : '[]'; ?>;
    var ServerScriptArr = <?php echo isset($ServerScriptArr) ? json_encode($ServerScriptArr) : '{}'; ?>;
    var IS_ENABLE_MASTER_SERVICES = <?php echo isset($IS_ENABLE_MASTER_SERVICES) ? json_encode($IS_ENABLE_MASTER_SERVICES) : '[]'; ?>;
    var tsite_url_main_admin = "<?php echo $tconfig['tsite_url_main_admin'] ?>";
    var SERVICETYPE_ICONS_TEXT = "<?php echo $defaultActiveService ?>";
    var APP_TYPE = "<?php echo strtoupper($APP_TYPE); ?>";
</script>
<script src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/masonry.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/dashboard_v4.js"></script>
<script>
    const accordions = document.querySelectorAll('.cashaccordion');
    accordions.forEach((accordion) => {
        accordion.addEventListener('click', function() {
            this.classList.toggle('active');
            const cashpanel = this.nextElementSibling;
            if (cashpanel.style.display == 'block') {
                cashpanel.style.display = 'none';
            } else {
                cashpanel.style.display = 'block';
            }
        });
    });
</script>
</body>
</html>