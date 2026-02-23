<?php
include_once('common.php');

$sql = "SELECT * FROM setup_info";
$db_setup_info = $obj->MySQLSelect($sql);
define('ADMIN_URL_CLIENT', 'admin');
$sql_vehicle_category_table_name = getVehicleCategoryTblName();

/* Give Below File Permission First
 * project/setup_info/uploads
 * project/webimages and it's all folder
 * Permission Folder For Auto Delete Files : Root,Admin,Admin/action folder
 * Changes In File
 * 1) project/assets/libraries/configuration_variables.php - Change Folder Name
 * 2) project/assets/libraries/db_info.php - Change Db Info
 */
$query = "SELECT * FROM service_categories WHERE eStatus='Active'";
$db_service_categories = $obj->MySQLSelect($query);
if(empty($db_service_categories)) {
    $db_service_categories = array();
}

$array_column = array_column($db_service_categories, 'iServiceId');
$matchresult = array_diff($array_column, $service_categories_ids_arr);
$matchresult2 = array_diff($service_categories_ids_arr, $array_column);
$errorcountsystemvalidation = $fileCount = $manualOrderFiles = 0;
$DONATION = $DRIVER_DESTINATION = $FAVOURITE_DRIVER = $FAVOURITE_STORE = $DRIVER_SUBSCRIPTION = $GOJEK_GOPAY = $MULTI_STOPOVER_POINTS = $MANUAL_STORE_ORDER_WEBSITE = $MANUAL_STORE_ORDER_STORE_PANEL = $MANUAL_STORE_ORDER_ADMIN_PANEL = $Deliverall = $UberX = $Fly = $FARE_MODEL_STRATEGY = $STORE_WISE_COMMISSION = $DELIVERY_TIP_MODULE = $DISTANCE_WISE_DELIVERY_CHARGES = $VOICE_INSTRUCTION = $STORE_UPLOAD_PHOTOS = $CANCEL_DRIVER_ORDER = $DELIVERY_HELPER = $CUSTOM_PUSH_NOTIFICATION = $OTP_VERIFICATION = $MULTIPLE_DELIVERY_IN_STORES = $REASSIGN_DRIVER_FEATURE = $MULTIPLE_OPTIONS_TOPPINGS_FEATURE = $STORE_MULTIPLE_CATEGORIES_FEATURE = $FREE_DELIVERY_PROMO_CODE_FEATURE = $FOOD_RATING_DETAIL_FEATURE = $SKU_ITEMS = $RESTRICT_DRIVER_FRAUD_FEATURE = $LOCATION_WISE_PROMO_CODE_FEATURE = $LOCATION_WISE_BANNER = $LOCATION_WISE_PUSH_NOTIFICATION = $COOKIE_CONSENT_FEATURE = $FIREBASE_VERIFICATION = $SMART_LOGIN_FEATURE = $DRIVER_REWARD_FEATURE = $VIDEO_CONSULTING_FEATURE = $SERVICE_BID_FEATURE = $BACK_TO_BACK_TRIPS_FEATURE = $VIDEO_CALL_OPTION_FEATURE = $FACEBOOK_PIXEL_SUPPORT_FEATURE = $FACEBOOK_ADS_FEATURE = $GOOGLE_ADS_FEATURE = $VIDEO_UPLOAD_SP_FEATURE = $MULTIPLE_IMAGES_VIDEOS_UPLOAD_DELIVERALL_FEATURE = $ACCOUNT_DELETE_FEATURE = $APP_HOME_SCREEN_SEARCH = $MEDICAL_SERVICES_FEATURE = $RENT_ITEM_FEATURE = $RENT_REALESTATE_FEATURE = $RENT_CARS_FEATURE = $RIDE_SHARE_FEATURE = $TRACK_SERVICE_FEATURE = $TRACK_ANY_SERVICE_FEATURE = $NEARBY_FEATURE = $GIFT_CARD_FEATURE = $USER_REWARD_FEATURE = $LIVE_ACTIVITY_FEATURE = $TAXI_BID_FEATURE = $PARKING_FEATURE = $INTERCITY_RIDES_FEATURE = $PICKUP_AREA_PHOTO_FEATURE = $FARE_TIME_ESTIMATION_FEATURE = "No"; // Added By HJ On 12-07-2019
?>
<style>
    ol.validation li {
        background: #cce5ff;
        margin: 5px;
        padding: 10px;
    }
</style>
<?php if (isset($filePanel) && $filePanel == "Admin") { ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <?php
}
$deleteFileArr = $mianWebserviceArr = array();
if (scount($db_setup_info) > 0) {
    $ePackageType = $db_setup_info[0]['ePackageType'];
    $eProductType = $db_setup_info[0]['eProductType'];
    $eDeliveryType = $db_setup_info[0]['eDeliveryType'];
    $eEnableKiosk = $eEnableHotel = $eCubeX = $eCubejekX = $eRideX = $eDeliverallX = $eFoodX =$eDeliveryX=$eServiceX=$eRideDeliveryX=$eDeliverykingx=$eCubeDoctorX = $eKingX = $ePlusXTaxi = $eSuperXSP = $ePlusXDeliveryKing = $ePlusXDeliverAll = $ePlusXCubeX = $ePlusXDelivery = $eKingXPro = $eProRideParcel = $eProCubeX = $eProTaxi = $eProSP = $eProDeliverAll = $eProDelivery = $eProDeliveryKing = $eKingXProRideSP = $eProCarPool = $eProKingX = $eProMedicalAllinone = $eProCubeXGC = "No";
    $eConfigurationApplied = "Yes";
    if (isset($db_setup_info[0]['eEnableKiosk'])) {
        $eEnableKiosk = $db_setup_info[0]['eEnableKiosk'];
    }
    if (isset($db_setup_info[0]['eEnableHotel'])) {
        $eEnableHotel = $db_setup_info[0]['eEnableHotel'];
    }
    if (isset($db_setup_info[0]['eConfigurationApplied'])) {
        $eConfigurationApplied = $db_setup_info[0]['eConfigurationApplied'];
    }
    $vAppTypeX = "";
    if (isset($db_setup_info[0]['vAppTypeX'])) {
        $vAppTypeX = $db_setup_info[0]['vAppTypeX'];
    }
    if (isset($db_setup_info[0]['eCubeX'])) {
        $eCubeX = $db_setup_info[0]['eCubeX'];
    }
    if (isset($db_setup_info[0]['eCubejekX'])) {
        $eCubejekX = $db_setup_info[0]['eCubejekX'];
    }
    if (isset($db_setup_info[0]['eRideX'])) {
        $eRideX = $db_setup_info[0]['eRideX'];
    }
    if (isset($db_setup_info[0]['eDeliverallX'])) {
        $eDeliverallX = $db_setup_info[0]['eDeliverallX'];
    }
    if (isset($db_setup_info[0]['eFoodX'])) {
        $eFoodX = $db_setup_info[0]['eFoodX'];
    }
    if(isset($db_setup_info[0]['eKingX'])){
        $eKingX = $db_setup_info[0]['eKingX'];
    }
    if(strtoupper($vAppTypeX) == "DELIVERYX"){
        $eDeliveryX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "SERVICEX"){
        $eServiceX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "RIDEDELIVERYX"){
        $eRideDeliveryX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "DELIVERYKINGX"){
        $eDeliverykingx = "Yes";
    }
    if(strtoupper($vAppTypeX) == "CUBEDOCTORX"){
        $eCubeDoctorX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PLUSXTAXI"){
        $ePlusXTaxi = "Yes";
    }
    if(strtoupper($vAppTypeX) == "SUPERXSP"){
        $eSuperXSP = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PLUSXDELIVERYKING"){
        $ePlusXDeliveryKing = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PLUSXDELIVERALL"){
        $ePlusXDeliverAll = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PLUSXCUBEX"){
        $ePlusXCubeX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PLUSXDELIVERY"){
        $ePlusXDelivery = "Yes";
    }
    if(strtoupper($vAppTypeX) == "KINGXPRO" || strtoupper($vAppTypeX) == "KINGXPRO_RIDE_SP"){
        $eKingXPro = "Yes";
        if(strtoupper($vAppTypeX) == "KINGXPRO_RIDE_SP") {
            $eKingXProRideSP = "Yes";
        }
    }
    if(strtoupper($vAppTypeX) == "PRORIDEPARCEL"){
        $eProRideParcel = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROCUBEX"){
        $eProCubeX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROTAXI"){
        $eProTaxi = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROSP"){
        $eProSP = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PRODELIVERALL"){
        $eProDeliverAll = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PRODY"){
        $eProDelivery = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PRODK"){
        $eProDeliveryKing = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROCARPOOL"){
        $eProCarPool = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROKINGX"){
        $eProKingX = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROMEDICALALLINONE"){
        $eProMedicalAllinone = "Yes";
    }
    if(strtoupper($vAppTypeX) == "PROCUBEXGC"){
        $eProCubeXGC = "Yes";
    }

    $kingx_package = $kingxpro_package = "No";
    if(strtoupper($eKingX) == "YES" || strtoupper($ePlusXTaxi) == "YES" || strtoupper($eSuperXSP) == "YES" || strtoupper($ePlusXDeliveryKing) == "YES" || strtoupper($ePlusXDeliverAll) == "YES" || strtoupper($ePlusXCubeX) == "YES" || strtoupper($ePlusXDelivery) == "YES") {
        $kingx_package = "Yes";
    }
    if(strtoupper($eKingXPro) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProDeliverAll) == "YES" || strtoupper($eProDelivery) == "YES" || strtoupper($eProDeliveryKing) == "YES" || strtoupper($eProCarPool) == "YES" || strtoupper($eProKingX) == "YES" || strtoupper($eProMedicalAllinone) == "YES" || strtoupper($eProCubeXGC) == "YES") {
        $kingxpro_package = "Yes";   
    }
    //Added By HJ On 12-07-2019 For Get New Addon Configuration Start
    $addOnData = json_decode($db_setup_info[0]['lAddOnConfiguration'], true);
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }

    $payment_gateways = !empty($db_setup_info[0]['tPaymentGateways']) ? json_decode($db_setup_info[0]['tPaymentGateways'], true) : "";

    if((strtoupper($kingx_package) == "YES" || strtoupper($kingxpro_package) == "YES") && !empty($payment_gateways)) {
        $project_pg = implode(",", $payment_gateways);
        $project_APP_PAYMENT_METHOD = $payment_gateways[0];
        $obj->sql_query("UPDATE configurations_payment SET tSelectVal = '$project_pg', vValue = '$project_APP_PAYMENT_METHOD' WHERE vName = 'APP_PAYMENT_METHOD'");

        $payment_gateways_arr = array_map('strtoupper', $payment_gateways);
        $all_payment_gateways = array('STRIPE', 'OMISE', 'PAYMAYA', 'XENDIT', 'FLUTTERWAVE', 'MPESA', 'ORANGEMOBILEMONEY', 'PAYFORT', 'PAYULATAM', 'PAYMENTEZ', 'SENANGPAY', 'SERFINSA', 'IUGU', 'IYZICO');
        foreach ($all_payment_gateways as $payment_gateway) {
            if(!in_array($payment_gateway, $payment_gateways_arr)) {
                $obj->sql_query("DELETE FROM configurations_payment WHERE vName LIKE '" . $payment_gateway . "_%'");
            }
        }

        $obj->sql_query("UPDATE company SET `vCurrencyCompany` = '$vSystemDefaultCurrencyName' WHERE 1 = 1");

        if($eSuperXSP == "Yes") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType NOT IN ('UberX', 'VideoConsult', 'Bidding') ");
            $obj->sql_query("UPDATE $master_service_category_tbl SET vIconImage1 = '20220311165820784.jpg' WHERE eType = 'VideoConsult' ");
            $obj->sql_query("UPDATE $master_service_category_tbl SET vIconImage1 = '20220311165845425.jpg' WHERE eType = 'Bidding' ");
        }
        if($ePlusXCubeX == "Yes" ) {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType NOT IN ('Ride', 'Deliver') ");
        }

        if(strtoupper($eProCubeX) == "YES") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType NOT IN ('Ride', 'Deliver' , 'DeliverAll') ");
        }

        if(strtoupper($eProSP) == "YES") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET iDisplayOrder = '1' WHERE eType = 'UberX' ");
            $obj->sql_query("UPDATE $master_service_category_tbl SET vIconImage1 = '20230501105152138.png', vCategoryImage = '{\"vCategoryImage_EN\":\"20230501105152138.png\"}', iDisplayOrder = '2' WHERE eType = 'VideoConsult' ");
            $obj->sql_query("UPDATE $master_service_category_tbl SET vIconImage1 = '20230501105537189.png', vCategoryImage = '{\"vCategoryImage_EN\":\"20230501105537189.png\"}', iDisplayOrder = '3' WHERE eType = 'Bidding' ");
        }

        if(strtoupper(RIDE_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('Ride') ");   
        }
        if($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
            if(strtoupper(DELIVERY_ENABLED) == "NO" && strtoupper(GENIE_ENABLED) == "NO" && strtoupper(RUNNER_ENABLED) == "NO") {
                $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('Deliver') ");   
            }

            if(strtoupper(DELIVERALL_ENABLED) == "NO") {
                $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('DeliverAll') ");   
            }
        } else {
            if(strtoupper(DELIVERY_ENABLED) == "NO" && strtoupper(DELIVERALL_ENABLED) == "NO" && strtoupper(GENIE_ENABLED) == "NO" && strtoupper(RUNNER_ENABLED) == "NO") {
                $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('Deliver') ");   
            }
        }
        
        if(strtoupper(UFX_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('UberX') ");   
        }
        if(strtoupper(BIDDING_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('Bidding') ");   
        }
        if(strtoupper(VC_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('VideoConsult') ");   
        }
        if(strtoupper(MED_UFX_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('MedicalServices') ");   
        }
        if(strtoupper(RENT_ITEM_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('RentItem') ");   
        }
        if(strtoupper(RENT_ESTATE_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('RentEstate') ");   
        }
        if(strtoupper(RENT_CARS_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('RentCars') ");   
        }
        if(strtoupper(NEARBY_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('NearBy') ");   
        }
        if(strtoupper(TRACK_SERVICE_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('TrackService') ");   
        }
        if(strtoupper(TRACK_ANY_SERVICE_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('TrackAnyService') ");   
        }
        if(strtoupper(RIDE_SHARE_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('RideShare') ");   
        }
        if(strtoupper(PARKING_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('Parking') ");   
        }
        if(strtoupper(TAXI_BID_ENABLED) == "NO") {
            $obj->sql_query("UPDATE $master_service_category_tbl SET eStatus = 'Deleted' WHERE eType IN ('TaxiBid') ");   
        }

        if(strtoupper(ONLY_ENABLE_DOCTOR_APP) == "YES") {
            $obj->sql_query("UPDATE app_home_screen_view SET eStatus = 'Inactive' WHERE eServiceType IN ('VideoConsult', 'Other') ");   
        }
    }
    //Added By HJ On 12-07-2019 For Get New Addon Configuration End
    $applyConfiguration = 0; //0-Not Run Configuration Setting,1-Set Default Configuration Setting As Per Product and Package
    if ($eConfigurationApplied == "No") {
        $applyConfiguration = 1; //0-Not Run Configuration Setting,1-Set Default Configuration Setting As Per Product and Package
    }
    $setupShFile = dirname(__FILE__) . "/setup_info/setup.sh";
    $shFileCommand = "sh " . $setupShFile;
    $permissionTxt = "";
    if (!is_writable($setupShFile)) {
        $permissionTxt = "Note: Please assign 777 Permission to " . $setupShFile . " File";
    }
    //Added By HJ On 17-12-2019 For Google Setting Update Start
    $tsite_url = $tconfig['tsite_url'];
    $obj->sql_query("UPDATE `configurations` SET `vValue` = '$tsite_url' WHERE vName = 'GOOGLE_PLUS_SITE_NAME'");
    $obj->sql_query("UPDATE `configurations` SET `vValue` = '" . $tsite_url . "gpconnect.php' WHERE vName = 'GOOGLE_PLUS_OAUTH_REDIRECT_URI'");
    //Added By HJ On 17-12-2019 For Google Setting Update End
    //echo $permissionTxt;die;
    echo "<div style='background:#ff0000;padding:20px;color:#ffffff;font-size:25px;text-align:center;display:none;' id='permissionmsg'>Please tell Chirag sir or Anurag sir to run below command from command prompt to delete below files.<br><b><u>" . $shFileCommand . "</u></b><br>" . $permissionTxt . "</div>";
    echo '<ol class="validation">';
    /* if(ENABLEHOTELPANEL == 'Yes'){ 
      $errorcountsystemvalidation +=1;
      echo "<li>Please Update ENABLEHOTELPANEL value as 'No' in configuration_variables file.</li>";
      } */
    if (strtoupper($ePackageType) != strtoupper($PACKAGE_TYPE)) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Set Project Package Type : " . $ePackageType . "</li>";
    }
    $configAppType = "";
    $getAppType = $obj->MySQLSelect("SELECT vValue FROM configurations WHERE vName='APP_TYPE'");
    if (scount($getAppType) > 0) {
        $configAppType = $getAppType[0]['vValue'];
    }
    $eProductTypeCheck = $eProductType;
    if ($eProductType == "Deliverall") {
        $eProductTypeCheck = "Ride-Delivery-UberX";
    }
    // Added BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue Start
    if ($eProductTypeCheck == "Foodonly" && $APP_TYPE == "Ride-Delivery-UberX") {
        //Foodonly's App Type Always Ride-Delivery-UberX
    } else if (strtoupper($eProductTypeCheck) != strtoupper($APP_TYPE)) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Set Project App Type : " . $eProductType . "</li>";
    }
    // Added BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue End
    // Commented BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue Start
    /* if (strtoupper($eProductTypeCheck) != strtoupper($APP_TYPE)) {
      $errorcountsystemvalidation += 1;
      echo "<li>Please Set Project App Type : " . $eProductType . "</li>";
      } */
    // Commented BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue End
    if (strtoupper($configAppType) != strtoupper($APP_TYPE)) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Set Project App Type In Configuration Table : " . $eProductType . "</li>";
    }
    //Added By HJ On 02-03-2019 For Remove Main Webservice File Start
    if ($eProductType == "Ride" || $eProductType == "Delivery" || $eProductType == "UberX" || $eProductType == "Ride-Delivery") {
        $mianWebserviceArr = array("webservice_dl_shark.php", "generalFunctions_dl_shark.php");
    } else if ($eProductType == "Foodonly" || $eProductType == "Deliverall" || ONLYDELIVERALL == "Yes") {
        $mianWebserviceArr = array("include_webservice_shark.php", "include_generalFunctions_shark.php");
    }
    $unUsefulFiles = array("test_socket.php", "chkR.php", "test_socket.php", "resizeImg - Copy.php", "trip_tracking - Copy.php", "expired_documents.php", "dummy_data_insert.php", "dummy_data_insert-14052019.php", "dummy_data_insert_gojek.php", "dummy_data_insert_taxi.php", "set_demo_store_img.php", "profile_190718.php", "cron_notification_email07082018.php", "=index.php", "=sign-in.php", "1.php", "test.php", "test_socket_orderdata.php", "test_socket_publish.php", "test_socket_watch.php", "test_socket1.php", "testa.php", "trip_tracking_test.php", "knowledgebase.php", "knowledgebase_design.php", "knowledgebase_detail.php", "knowledgebase_search.php", "knowledgebase_search_list.php", "knowledgebase2.php", "labelsDemo.php", "labelsDemo1.php", "labelsupdate.php", "language_popup.php", "language_save.php", "mobiconfiguration.php", "driver_action_new.php", "dummy_data_insert_04112019.php", "dummy_data_insert_bk_121219.php", "dummy_data_insert_final.php", "faq_new.php", "flag_files.php", "index-new.php", "captcha_code_news_file.php", "1.html", "2.php", "admin/map_tracking_old.php", "admin/home_content_cubejekx_action.php-14-12", "admin/invoicekp1.php", "settings_orig.php", "faq_categories_action_cd943.php", "cx-fareestimate1.php", "create_demo_acc_v3c.php", ADMIN_URL_CLIENT . "help_detail_categories_action_cd943.php", ADMIN_URL_CLIENT . "faq_categories_action_cd943.php");
    $finalRootFileArr = array_merge($mianWebserviceArr, $unUsefulFiles);
    foreach ($finalRootFileArr as $key => $filename) {
        if (file_exists(dirname(__FILE__) . "/" . $filename)) {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Delete File From root folder : " . $filename . "</li>";
            $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
        }
    }
    //Added By HJ On 02-03-2019 For Remove Main Webservice File End
    if ($eProductType == 'Ride') {
        $ridewebservicefilearray = array("include/uberx/include_webservice_uberx.php", "include/uberx/action_booking_admin.php", "include/delivery/include_webservice_delivery.php", "include/delivery/add_booking_admin4.php", "include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/add_booking_admin3.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php");
        foreach ($ridewebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
        if($kingxpro_package == "No") {
            $obj->sql_query("DELETE FROM vehicle_type WHERE eIconType!='Car'");    
        }
    } else if ($eProductType == 'Delivery') {
        $deliverywebservicefilearray = array("include/uberx/include_webservice_uberx.php", "include/uberx/action_booking_admin.php", "include/ride/include_webservice_ride.php", "include/ride/add_booking_admin6.php", "include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/add_booking_admin3.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php");
        foreach ($deliverywebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
        $obj->sql_query("DELETE FROM admin_groups WHERE iGroupId ='4'");
    } else if ($eProductType == 'UberX') {
        $ufxwebservicefilearray = array("include/delivery/include_webservice_delivery.php", "include/delivery/add_booking_admin4.php", "include/ride/include_webservice_ride.php", "include/ride/add_booking_admin6.php", "include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/add_booking_admin3.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php");
        foreach ($ufxwebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    } else if ($eProductType == 'Ride-Delivery') {
        $ridedeliverywebservicefilearray = array("include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/uberx/action_booking_admin.php", "include/uberx/include_webservice_uberx.php");
        foreach ($ridedeliverywebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }

    if ($eProductType != 'Delivery' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        if (ENABLE_MULTI_DELIVERY == 'Yes') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
        }
    }
    //Added By HJ On 14-10-2019 For Remove SP App Files Start
    if ($eProductType != "Ride-Delivery-UberX" && $eProductType != "UberX" && $eProductType == 'Ride-Delivery-UberX-Shark') {
        $uberxAdmnFilesArr = array(ADMIN_URL_CLIENT . "/action_driver_service_request.php", ADMIN_URL_CLIENT . "/driver_service_request.php");
        foreach ($uberxAdmnFilesArr as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From admin folder " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    //Added By HJ On 14-10-2019 For Remove SP App Files End
    if ($eProductType == 'Delivery' || $eProductType == 'Ride-Delivery-UberX' || $eProductType == 'Ride-Delivery-UberX-Shark' || $eProductType == 'Ride-Delivery') {
        if ($eDeliveryType == 'Multi') {
            if (ENABLE_MULTI_DELIVERY == 'No') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'Yes' in configuration_variables file.</li>";
            }
        }
    }

    if ($eProductType == 'UberX') {
        //$sql1 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName = 'DRIVER_REQUEST_METHOD' || vName = 'ENABLE_HAIL_RIDES' || vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='DELIVERY_VERIFICATION_METHOD' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI' || vName='MAX_ALLOW_NUM_DESTINATION_MULTI')";
        //$obj->sql_query($sql1); // By HJ On 07-03-2019
    }

    //if ($eProductType != 'UberX' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') { // Commented By HJ On 14-03-2019 As Per Discuss With KS Sir
    if ($eProductType == 'Ride' || ONLYDELIVERALL == "Yes") { // Added By HJ On 14-03-2019 As Per Discuss With KS Sir
        $uberxfilearray = array(ADMIN_URL_CLIENT . "/service_type.php", ADMIN_URL_CLIENT . "/left_menu_ufx_array.php", ADMIN_URL_CLIENT . "/left_menu_ufx.php", ADMIN_URL_CLIENT . "/left_menu_ufx_n.php", ADMIN_URL_CLIENT . "/vehicle_sub_category.php", ADMIN_URL_CLIENT . "/service_type_action.php", ADMIN_URL_CLIENT . "/action/service_type.php", ADMIN_URL_CLIENT . "/add_availability.php", ADMIN_URL_CLIENT . "/manage_service_type.php", ADMIN_URL_CLIENT . "/action/vehicle_sub_category.php");

        if(strtoupper($eProTaxi) == "NO" && strtoupper($eProDeliverAll) == "NO") {
            $uberxfilearray[] = ADMIN_URL_CLIENT . "/vehicle_category.php";
            $uberxfilearray[] = ADMIN_URL_CLIENT . "/vehicle_category_action.php";
            $uberxfilearray[] = ADMIN_URL_CLIENT . "/action/vehicle_category.php";
        }
        foreach ($uberxfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($eProductType != 'Delivery' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        if ($eDeliveryType != 'Multi') {
            $multifilearray = array(ADMIN_URL_CLIENT . "/invoice_multi_delivery.php");
            foreach ($multifilearray as $key => $filename) {
                if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                }
            }
        }
        $deliveryfilearray = array(ADMIN_URL_CLIENT . "/package_type.php", ADMIN_URL_CLIENT . "/package_type_action.php", ADMIN_URL_CLIENT . "/action/package_type.php");
        foreach ($deliveryfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($ePackageType != "shark") {
        $blockDriverFiled = array(ADMIN_URL_CLIENT . "/blocked_driver.php", ADMIN_URL_CLIENT . "/blocked_rider.php", ADMIN_URL_CLIENT . "/action/blocked_driver.php", ADMIN_URL_CLIENT . "/action/blocked_rider.php");
        foreach ($blockDriverFiled as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    /* if (($eProductType == "Ride" || $eProductType == "Ride-Delivery" || $eProductType == "Ride-Delivery-UberX") && ENABLEKIOSKPANEL == "Yes") {
      $errorcountsystemvalidation += 1;
      echo "<li>Please Update ENABLEKIOSKPANEL value as 'No' in configuration_variables file.</li>";
      } */
    $hotelPanel = $MODULES_OBJ->isEnableHotelPanel();
    $kioskPanel = $MODULES_OBJ->isEnableKioskPanel();
    $hotelPanelEnable = $kioskPanelEnable = "No";
    if ($hotelPanel > 0) {
        $hotelPanelEnable = "Yes";
    }
    if ($kioskPanel > 0) {
        $kioskPanelEnable = "Yes";
    }
    if ($kioskPanelEnable != $eEnableKiosk) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Update ENABLEKIOSKPANEL value as '$eEnableKiosk' in configuration_variables file.</li>";
    }
    if ($hotelPanelEnable != $eEnableHotel) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Update ENABLEHOTELPANEL value as '$eEnableHotel' in configuration_variables file.</li>";
    }
    if ($hotelPanelEnable == 'No') {
        $hotelfilearray = array(ADMIN_URL_CLIENT . "/hotel_rider.php", ADMIN_URL_CLIENT . "/hotel_rider_action.php", ADMIN_URL_CLIENT . "/hotel_payment_report.php", ADMIN_URL_CLIENT . "/hotel_index.php", ADMIN_URL_CLIENT . "/hotel_booking.php", ADMIN_URL_CLIENT . "/hotel_banner_action.php", ADMIN_URL_CLIENT . "/hotel_banner.php", ADMIN_URL_CLIENT . "/export_hotel_pay_details.php", ADMIN_URL_CLIENT . "/create_request.php", ADMIN_URL_CLIENT . "/action/hotel_payment_report.php", ADMIN_URL_CLIENT . "/action/hotel_rider.php");
        foreach ($hotelfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($kioskPanelEnable == "No") {
        $kioskFileArr = array(ADMIN_URL_CLIENT . "/visit.php", "/visit_address_action.php", "/action/visit.php");
        foreach ($kioskFileArr as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        $cubejekfilearray = array(ADMIN_URL_CLIENT . "/home_content_new.php", ADMIN_URL_CLIENT . "/home_content_action_new.php", ADMIN_URL_CLIENT . "/app_home_settings.php", ADMIN_URL_CLIENT . "/app_home_settings_action.php");
        foreach ($cubejekfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }

    if ($eProductType != 'Ride' && $eProductType != 'Ride-Delivery' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        $ridefilearray = array(ADMIN_URL_CLIENT . "/user_profile_master_action.php", ADMIN_URL_CLIENT . "/user_profile_master.php", ADMIN_URL_CLIENT . "/trip_reason.php", ADMIN_URL_CLIENT . "/trip_reason_action.php", ADMIN_URL_CLIENT . "/profile.php", ADMIN_URL_CLIENT . "/organization_document_fetch.php", ADMIN_URL_CLIENT . "/organization.php", ADMIN_URL_CLIENT . "/organization_action.php", ADMIN_URL_CLIENT . "/org_payment_report.php", ADMIN_URL_CLIENT . "/org_cancellation_payment_report.php", ADMIN_URL_CLIENT . "/location-airport.php", ADMIN_URL_CLIENT . "/location_action_airport.php", ADMIN_URL_CLIENT . "/action/location-airport.php", ADMIN_URL_CLIENT . "/action/trip_reason.php", ADMIN_URL_CLIENT . "/action/user_profile_master.php");
        foreach ($ridefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($eProductType == 'Foodonly') {
        /* $sql2 = "UPDATE configurations_cubejek SET `eStatus` = 'Inactive' WHERE (vName = 'GROCERY_APP_SHOW_SELECTION' || vName = 'GROCERY_APP_GRID_ICON_NAME' || vName = 'GROCERY_APP_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || vName = 'GROCERY_APP_PACKAGE_NAME' || vName = 'GROCERY_APP_IOS_APP_ID' || vName = 'GROCERY_APP_IOS_PACKAGE_NAME' || vName = 'GROCERY_APP_SERVICE_ID' || vName = 'DELIVER_ALL_APP_IOS_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_IOS_APP_ID' || vName = 'DELIVER_ALL_APP_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || vName = 'DELIVER_ALL_APP_BANNER_IMG_NAME' || vName='DELIVER_ALL_APP_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_SHOW_SELECTION' )";
          $obj->sql_query($sql2); */
    }

    if ($eProductType != 'Foodonly' && $eProductType != 'Deliverall') {
        if ($ePackageType == 'standard' || $ePackageType == 'enterprise') {
            if ($eProductType != 'Ride-Delivery-UberX-Shark' && $eProductType != 'Ride-Delivery-UberX') {
                /* $sql2 = "UPDATE configurations_cubejek SET `eStatus` = 'Inactive' WHERE (vName = 'FOOD_APP_SHOW_SELECTION' || vName = 'FOOD_APP_GRID_ICON_NAME' || vName = 'FOOD_APP_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_GRID_ICON_NAME' || vName = 'FOOD_APP_PACKAGE_NAME' || vName = 'FOOD_APP_IOS_APP_ID' || vName = 'FOOD_APP_IOS_PACKAGE_NAME'  || vName = 'FOOD_APP_SERVICE_ID' || vName = 'GROCERY_APP_SHOW_SELECTION' || vName = 'GROCERY_APP_GRID_ICON_NAME' || vName = 'GROCERY_APP_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || vName = 'GROCERY_APP_PACKAGE_NAME' || vName = 'GROCERY_APP_IOS_APP_ID' || vName = 'GROCERY_APP_IOS_PACKAGE_NAME' || vName = 'GROCERY_APP_SERVICE_ID' || vName = 'DELIVER_ALL_APP_IOS_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_IOS_APP_ID' || vName = 'DELIVER_ALL_APP_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || vName = 'DELIVER_ALL_APP_BANNER_IMG_NAME' || vName='DELIVER_ALL_APP_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_SHOW_SELECTION' )";
                  $obj->sql_query($sql2);
                 */
                $s1 = "DROP TABLE IF EXISTS `cuisine`, `food_menu`, `service_categories`, `food_menu_images`, `orders`, `order_details`, `order_later`, `order_status`, `order_status_logs`, `menuitem_options`, `menu_items`, `language_label_6`, `language_label_5`";
                $obj->sql_query($s1);
            }

            $foodfilearray = array(ADMIN_URL_CLIENT . "/cuisine.php", ADMIN_URL_CLIENT . "/cuisine_action.php", ADMIN_URL_CLIENT . "/delivery_charges_action.php", ADMIN_URL_CLIENT . "/delivery_charges.php", ADMIN_URL_CLIENT . "/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/food_menu.php", ADMIN_URL_CLIENT . "/food_menu_action.php", ADMIN_URL_CLIENT . "/store.php", ADMIN_URL_CLIENT . "/store_action.php", ADMIN_URL_CLIENT . "/store_banner_action.php", ADMIN_URL_CLIENT . "/store_banner.php", ADMIN_URL_CLIENT . "/store_cancel_reason.php", ADMIN_URL_CLIENT . "/store_cancel_reason_action.php", ADMIN_URL_CLIENT . "/store_dashboard.php", ADMIN_URL_CLIENT . "/store_document_action.php", ADMIN_URL_CLIENT . "/store_document_fetch.php", ADMIN_URL_CLIENT . "/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/store_payment_report.php", ADMIN_URL_CLIENT . "/store_review.php", ADMIN_URL_CLIENT . "/store_vehicle_type.php", ADMIN_URL_CLIENT . "/store_vehicle_type_action.php", ADMIN_URL_CLIENT . "/store-dashboard.php", ADMIN_URL_CLIENT . "/order_invoice.php", ADMIN_URL_CLIENT . "/order_status.php", ADMIN_URL_CLIENT . "/menu_item_action.php", ADMIN_URL_CLIENT . "/order_status_action.php", ADMIN_URL_CLIENT . "/menu_item.php", ADMIN_URL_CLIENT . "/action/food_menu.php", ADMIN_URL_CLIENT . "/action/ordar_status_type.php", ADMIN_URL_CLIENT . "/action/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/action/store.php", ADMIN_URL_CLIENT . "/action/store_cancel_reason.php", ADMIN_URL_CLIENT . "/action/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/action/store_payment_report.php", ADMIN_URL_CLIENT . "/action/store_review.php", ADMIN_URL_CLIENT . "/action/store_vehicle_type.php", ADMIN_URL_CLIENT . "/action/menu_item.php", ADMIN_URL_CLIENT . "/action/cuisine.php", ADMIN_URL_CLIENT . "/action/delivery_charges.php", ADMIN_URL_CLIENT . "/service_provider.php", ADMIN_URL_CLIENT . "/service_provider_action.php", ADMIN_URL_CLIENT . "/service_category.php", ADMIN_URL_CLIENT . "/service_category_action.php", ADMIN_URL_CLIENT . "/left_menu_deliverall_array.php", ADMIN_URL_CLIENT . "/homecontent.php", ADMIN_URL_CLIENT . "/homecontent_action.php", ADMIN_URL_CLIENT . "/cancelled_orders.php", ADMIN_URL_CLIENT . "/allorders.php", ADMIN_URL_CLIENT . "/ajax_get_cuisine.php", ADMIN_URL_CLIENT . "/ajax_get_food_category.php", ADMIN_URL_CLIENT . "/ajax_get_restorantcat_filter.php", ADMIN_URL_CLIENT . "/ajax_check_deliverycharge_area.php", ADMIN_URL_CLIENT . "/ajax_store_details.php", ADMIN_URL_CLIENT . "/advertise_banners.php", ADMIN_URL_CLIENT . "/advertise_banner_action.php", ADMIN_URL_CLIENT . "/groups.php", ADMIN_URL_CLIENT . "/group_action.php", ADMIN_URL_CLIENT . "/permissions.php", ADMIN_URL_CLIENT . "/permission_action.php", ADMIN_URL_CLIENT . "/action/permissions.php", ADMIN_URL_CLIENT . "/action/groups.php", ADMIN_URL_CLIENT . "/action/banner_impression.php", ADMIN_URL_CLIENT . "/action/advertise_banners.php", ADMIN_URL_CLIENT . "/action/news.php", ADMIN_URL_CLIENT . "/action/newsletter.php", ADMIN_URL_CLIENT . "/action/organization.php", ADMIN_URL_CLIENT . "/action/airport_surcharge.php", ADMIN_URL_CLIENT . "/airport_surcharge_action.php", ADMIN_URL_CLIENT . "/airport_surcharge.php", ADMIN_URL_CLIENT . "/news.php", ADMIN_URL_CLIENT . "/news_action.php", ADMIN_URL_CLIENT . "/newsletter.php", ADMIN_URL_CLIENT . "/newsletter---.php", ADMIN_URL_CLIENT . "/action/service_provider.php");
            foreach ($foodfilearray as $key => $filename) {
                if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                }
            }
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Normal' WHERE vName='RIDE_DRIVER_CALLING_METHOD'"); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE' || vName = 'ADMIN_COMMISSION' || vName = 'MIN_ORDER_CANCELLATION_CHARGES' || vName = 'COMPANY_EMAIL_VERIFICATION' || vName = 'COMPANY_PHONE_VERIFICATION')"); // By HJ On 07-03-2019
            //Added By HJ On 28-02-2019 For Disable New Features Start
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'No',`eAdminDisplay` = 'No' WHERE (vName = 'ENABLE_INSURANCE_TRIP_REPORT'||  vName = 'ENABLE_INSURANCE_ACCEPT_REPORT' || vName = 'ENABLE_INSURANCE_IDLE_REPORT' ||  vName = 'ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION' || vName = 'ENABLE_INTRANSIT_SHOPPING_SYSTEM' || vName = 'ENABLE_AIRPORT_SURCHARGE_SECTION' || vName = 'ENABLE_NEWS_SECTION' || vName = 'ENABLE_LIVE_CHAT' || vName = 'ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER' || vName = 'ENABLE_DRIVER_ADVERTISEMENT_BANNER' || vName = 'ENABLE_RIDER_ADVERTISEMENT_BANNER' || vName = 'BOOK_FOR_ELSE_ENABLE' || vName = 'CHILD_SEAT_ACCESSIBILITY_OPTION' || vName = 'POOL_ENABLE' || vName = 'ENABLE_CORPORATE_PROFILE' || vName = 'PASSENGER_LINKEDIN_LOGIN' || vName = 'DRIVER_LINKEDIN_LOGIN' || vName = 'PASSENGER_LINKEDIN_LOGIN')"); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Disable',`eAdminDisplay` = 'No' WHERE vName='ADVERTISEMENT_TYPE'"); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `vValue` = '',`eAdminDisplay` = 'No' WHERE (vName='LINKEDIN_APP_SECRET_KEY' || vName='LINKEDIN_APP_ID')"); // By HJ On 07-03-2019
            //Added By HJ On 28-02-2019 For Disable New Features End
            $frontfoodfilesarray = array("food_menu.php", "food_menu_action.php", "invoice_deliverall.php", "menu_item_action.php", "menuitems.php", "myorder.php", "order_invoice.php", "settings.php", "orderdetails_mail_format.php", "processing_orders.php", "sign-up-restaurant.php", "organization_login.php", "organization_trip.php", "organization_users_trip.php", "organization_profile_action.php", "organization-profile.php", "my_users.php", "ajax_organization_login_action.php", "signup_action_organization.php", "organization-logout.php", "sign-up-organization.php");
            if ($eProductType != 'Ride-Delivery-UberX' && $eProductType != 'UberX') {
                $frontfoodfilesarray[] = "provider_images.php";
            }
            foreach ($frontfoodfilesarray as $key => $filesname) {
                if (file_exists((dirname(__FILE__) . "/" . $filesname))) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From root folder " . $filesname . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filesname;
                }
            }
            $webserviceIncludeFilesArr = array("include/include_webservice_sharkfeatures.php", "include/livechat.php");
            foreach ($webserviceIncludeFilesArr as $key => $filesname) {
                if (file_exists((dirname(__FILE__) . "/" . $filesname))) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From include folder " . $filesname . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filesname;
                }
            }
            if ($eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') {
                /* $que1 = 'TRUNCATE TABLE configurations_cubejek';
                  $obj->sql_query($que1); */
            }
        } else {
            if ($eProductType != 'Ride-Delivery-UberX-Shark' && ($eProductType != 'Ride-Delivery-UberX' && $ePackageType != 'shark')) {
                $foodfilearray = array(ADMIN_URL_CLIENT . "/cuisine.php", ADMIN_URL_CLIENT . "/cuisine_action.php", ADMIN_URL_CLIENT . "/delivery_charges_action.php", ADMIN_URL_CLIENT . "/delivery_charges.php", ADMIN_URL_CLIENT . "/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/food_menu.php", ADMIN_URL_CLIENT . "/food_menu_action.php", ADMIN_URL_CLIENT . "/store.php", ADMIN_URL_CLIENT . "/store_action.php", ADMIN_URL_CLIENT . "/store_banner_action.php", ADMIN_URL_CLIENT . "/store_banner.php", ADMIN_URL_CLIENT . "/store_cancel_reason.php", ADMIN_URL_CLIENT . "/store_cancel_reason_action.php", ADMIN_URL_CLIENT . "/store_dashboard.php", ADMIN_URL_CLIENT . "/store_document_action.php", ADMIN_URL_CLIENT . "/store_document_fetch.php", ADMIN_URL_CLIENT . "/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/store_payment_report.php", ADMIN_URL_CLIENT . "/store_review.php", ADMIN_URL_CLIENT . "/store_vehicle_type.php", ADMIN_URL_CLIENT . "/store_vehicle_type_action.php", ADMIN_URL_CLIENT . "/store-dashboard.php", ADMIN_URL_CLIENT . "/order_invoice.php", ADMIN_URL_CLIENT . "/order_status.php", ADMIN_URL_CLIENT . "/menu_item_action.php", ADMIN_URL_CLIENT . "/order_status_action.php", ADMIN_URL_CLIENT . "/menu_item.php", ADMIN_URL_CLIENT . "/action/food_menu.php", ADMIN_URL_CLIENT . "/action/ordar_status_type.php", ADMIN_URL_CLIENT . "/action/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/action/store.php", ADMIN_URL_CLIENT . "/action/store_cancel_reason.php", ADMIN_URL_CLIENT . "/action/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/action/store_payment_report.php", ADMIN_URL_CLIENT . "/action/store_review.php", ADMIN_URL_CLIENT . "/action/store_vehicle_type.php", ADMIN_URL_CLIENT . "/action/menu_item.php", ADMIN_URL_CLIENT . "/action/cuisine.php", ADMIN_URL_CLIENT . "/action/delivery_charges.php", ADMIN_URL_CLIENT . "/service_provider.php", ADMIN_URL_CLIENT . "/service_provider_action.php", ADMIN_URL_CLIENT . "/service_category.php", ADMIN_URL_CLIENT . "/service_category_action.php", ADMIN_URL_CLIENT . "/left_menu_deliverall_array.php", ADMIN_URL_CLIENT . "/homecontent.php", ADMIN_URL_CLIENT . "/homecontent_action.php", ADMIN_URL_CLIENT . "/cancelled_orders.php", ADMIN_URL_CLIENT . "/allorders.php", ADMIN_URL_CLIENT . "/ajax_get_cuisine.php", ADMIN_URL_CLIENT . "/ajax_get_food_category.php", ADMIN_URL_CLIENT . "/ajax_get_restorantcat_filter.php", ADMIN_URL_CLIENT . "/ajax_check_deliverycharge_area.php", ADMIN_URL_CLIENT . "/ajax_store_details.php");
                foreach ($foodfilearray as $key => $filename) {
                    if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                        $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                    }
                }
            }
        }
    }
    //Added By HJ On 24-01-2020 For Removed Unwanted Filed of Cubex and CubejekX Setup Start
    $cubejekXCubeXFileArr = array();
    if (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES" || strtoupper($eRideX) == "YES" || strtoupper($eDeliveryX) == "YES" || strtoupper($eServiceX) == "YES" || strtoupper($eRideDeliveryX) == "YES" || strtoupper($eDeliverykingx) == "YES" || strtoupper($eCubeDoctorX) == "YES" || strtoupper($eKingX) == "YES" || strtoupper($ePlusXTaxi) == "YES" || strtoupper($eSuperXSP) == "YES" || strtoupper($ePlusXDeliveryKing) == "YES" || strtoupper($ePlusXDeliverAll) == "YES" || strtoupper($ePlusXCubeX) == "YES" || strtoupper($ePlusXDelivery) == "YES" || strtoupper($eKingXPro) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProDeliverAll) == "YES" || strtoupper($eProDelivery) == "YES" || strtoupper($eProDeliveryKing) == "YES" || strtoupper($eProCarPool) == "YES" || strtoupper($eProKingX) == "YES" || strtoupper($eProMedicalAllinone) == "YES" || strtoupper($eProCubeXGC) == "YES") {
        $delAllFileArr = $flyFileArr = $ufxFileArr = $cubejekXCubeXFileArr = $rideFileArr = $deliveryFileArr = array();
        //echo $Deliverall."===".$Fly."===".$UberX."===".$Ride."===".$Delivery."==".ONLYDELIVERALL."<br>";
        if (strtoupper($Deliverall) == "NO") {
            //echo "Deliverall<br>";
            $manualOrderFiles = 1;
            $delAllFileArr = array(ADMIN_URL_CLIENT . "/cuisine.php", ADMIN_URL_CLIENT . "/cuisine_action.php", ADMIN_URL_CLIENT . "/delivery_charges_action.php", ADMIN_URL_CLIENT . "/delivery_charges.php", ADMIN_URL_CLIENT . "/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/food_menu.php", ADMIN_URL_CLIENT . "/food_menu_action.php", ADMIN_URL_CLIENT . "/store.php", ADMIN_URL_CLIENT . "/store_action.php", ADMIN_URL_CLIENT . "/store_banner_action.php", ADMIN_URL_CLIENT . "/store_banner.php", ADMIN_URL_CLIENT . "/store_cancel_reason.php", ADMIN_URL_CLIENT . "/store_cancel_reason_action.php", ADMIN_URL_CLIENT . "/store_dashboard.php", ADMIN_URL_CLIENT . "/store_document_action.php", ADMIN_URL_CLIENT . "/store_document_fetch.php", ADMIN_URL_CLIENT . "/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/store_payment_report.php", ADMIN_URL_CLIENT . "/store_review.php", ADMIN_URL_CLIENT . "/store_vehicle_type.php", ADMIN_URL_CLIENT . "/store_vehicle_type_action.php", ADMIN_URL_CLIENT . "/store-dashboard.php", ADMIN_URL_CLIENT . "/order_invoice.php", ADMIN_URL_CLIENT . "/order_status.php", ADMIN_URL_CLIENT . "/menu_item_action.php", ADMIN_URL_CLIENT . "/order_status_action.php", ADMIN_URL_CLIENT . "/menu_item.php", ADMIN_URL_CLIENT . "/action/food_menu.php", ADMIN_URL_CLIENT . "/action/ordar_status_type.php", ADMIN_URL_CLIENT . "/action/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/action/store.php", ADMIN_URL_CLIENT . "/action/store_cancel_reason.php", ADMIN_URL_CLIENT . "/action/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/action/store_payment_report.php", ADMIN_URL_CLIENT . "/action/store_review.php", ADMIN_URL_CLIENT . "/action/store_vehicle_type.php", ADMIN_URL_CLIENT . "/action/menu_item.php", ADMIN_URL_CLIENT . "/action/cuisine.php", ADMIN_URL_CLIENT . "/action/delivery_charges.php", ADMIN_URL_CLIENT . "/service_provider.php", ADMIN_URL_CLIENT . "/service_provider_action.php", ADMIN_URL_CLIENT . "/service_category.php", ADMIN_URL_CLIENT . "/service_category_action.php", ADMIN_URL_CLIENT . "/left_menu_deliverall_array.php", ADMIN_URL_CLIENT . "/homecontent.php", ADMIN_URL_CLIENT . "/homecontent_action.php", ADMIN_URL_CLIENT . "/cancelled_orders.php", ADMIN_URL_CLIENT . "/allorders.php", ADMIN_URL_CLIENT . "/ajax_get_cuisine.php", ADMIN_URL_CLIENT . "/ajax_get_food_category.php", ADMIN_URL_CLIENT . "/ajax_get_restorantcat_filter.php", ADMIN_URL_CLIENT . "/ajax_check_deliverycharge_area.php", ADMIN_URL_CLIENT . "/ajax_store_details.php", "food_menu.php", "cx-food_menu.php", "food_menu_action.php", "cx-food_menu_action.php", "invoice_deliverall.php", "cx-invoice_deliverall.php", "menu_item_action.php", "cx-menu_item_action.php", "menuitems.php", "cx-menuitems.php", "myorder.php", "cx-myorder.php", "order_invoice.php", "settings.php", "cx-settings.php", "orderdetails_mail_format.php", "processing_orders.php", "cx-processing_orders.php", "sign-up-restaurant.php", "cx-grocery.php", "cx-food.php", ADMIN_URL_CLIENT . "home_content_grocery_action.php", ADMIN_URL_CLIENT . "home_content_grocery.php", ADMIN_URL_CLIENT . "home_content_food_action.php", ADMIN_URL_CLIENT . "home_content_food.php", ADMIN_URL_CLIENT . "home_content_deliverallx_action.php", ADMIN_URL_CLIENT . "home_content_deliverallx.php");
        }
        if (strtoupper($Fly) == "NO") {
            //echo "Fly<br>";
            $flyFileArr = array("home_content_fly.php", ADMIN_URL_CLIENT . "/fly_stations.php", ADMIN_URL_CLIENT . "/fly_stations_action.php", ADMIN_URL_CLIENT . "/fly_vehicle_type.php", ADMIN_URL_CLIENT . "/action/fly_vehicle_type_action.php", ADMIN_URL_CLIENT . "/fly_locationwise_fare.php", ADMIN_URL_CLIENT . "/fly_location_wise_fare_action.php", ADMIN_URL_CLIENT . "/action/fly_locationwise_fare.php", ADMIN_URL_CLIENT . "/action/fly_stations.php", ADMIN_URL_CLIENT . "/action/fly_vehicle_type.php", ADMIN_URL_CLIENT . "home_content_fly_action.php", ADMIN_URL_CLIENT . "home_content_fly.php", "include/features/include_fly_stations.php");
        }
        $cubejekXCubeXFileArr = array_merge($delAllFileArr, $flyFileArr);
        if (strtoupper($UberX) == "NO") {
            //echo "Uberx<br>";
            $ufxFileArr = array(ADMIN_URL_CLIENT . "/service_type.php", ADMIN_URL_CLIENT . "/left_menu_ufx_array.php", ADMIN_URL_CLIENT . "/left_menu_ufx.php", ADMIN_URL_CLIENT . "/left_menu_ufx_n.php", ADMIN_URL_CLIENT . "/add_availability.php", ADMIN_URL_CLIENT . "/service_type_action.php", ADMIN_URL_CLIENT . "/action/service_type.php", ADMIN_URL_CLIENT . "/action_driver_service_request.php", ADMIN_URL_CLIENT . "/driver_service_request.php", ADMIN_URL_CLIENT . "/manage_service_type.php", "include/uberx/action_booking_admin.php", "include/uberx/include_webservice_uberx.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $ufxFileArr);
        }
        if (strtoupper($Ride) == "NO") {
            //echo "Ride<br>";
            $ridefilearray = array("include/ride/include_webservice_ride.php", "include/ride/add_booking_admin6.php", ADMIN_URL_CLIENT . "/user_profile_master_action.php", ADMIN_URL_CLIENT . "/user_profile_master.php", ADMIN_URL_CLIENT . "/trip_reason.php", ADMIN_URL_CLIENT . "/trip_reason_action.php", ADMIN_URL_CLIENT . "/profile.php", ADMIN_URL_CLIENT . "/organization_document_fetch.php", ADMIN_URL_CLIENT . "/organization.php", ADMIN_URL_CLIENT . "/organization_action.php", ADMIN_URL_CLIENT . "/org_payment_report.php", ADMIN_URL_CLIENT . "/org_cancellation_payment_report.php", ADMIN_URL_CLIENT . "/location-airport.php", ADMIN_URL_CLIENT . "/location_action_airport.php", ADMIN_URL_CLIENT . "/action/location-airport.php", ADMIN_URL_CLIENT . "/action/trip_reason.php", ADMIN_URL_CLIENT . "/action/user_profile_master.php", ADMIN_URL_CLIENT . "home_content_taxi_action.php", ADMIN_URL_CLIENT . "home_content_taxi.php", ADMIN_URL_CLIENT . "home_content_ridecx_action.php", ADMIN_URL_CLIENT . "home_content_ridecx.php", ADMIN_URL_CLIENT . "home_content_action_ride.php","organization_login.php", "organization_trip.php", "cx-organization_trip.php", "organization_users_trip.php", "cx-organization_users_trip.php", "organization_profile_action.php", "organization-profile.php", "cx-profile_organization.php", "ajax_organization_login_action.php", "signup_action_organization.php", "organization-logout.php", "sign-up-organization.php","cx-sign-up-org.php", "include/features/include_fare_strategy_model.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $ridefilearray);
        }
        if(strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO")
        {
            $rideDeliveryArr = array("include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/add_booking_admin3.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $rideDeliveryArr);
        }
        if(strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO" && strtoupper($UberX) == "NO")
        {
            $rideDeliveryUfxArr = array("include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin2.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $rideDeliveryUfxArr);
        }
        if ($eDeliveryType != 'Multi') {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/invoice_multi_delivery.php";
            $cubejekXCubeXFileArr[] = "invoice_multi_delivery.php";
            $cubejekXCubeXFileArr[] = "cx-invoice_multi_delivery.php";
        }
        if (strtoupper($eDeliveryType) == 'NONE' || trim($eDeliveryType) == "") {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/package_type.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/package_type_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/action/package_type.php";
        }
        if (strtoupper($Delivery) == "NO") {
            //echo "Delivery<br>";
            $deliveryFileArr = array("include/delivery/include_webservice_delivery.php", "include/delivery/add_booking_admin4.php", ADMIN_URL_CLIENT . "home_content_deliveryx_action.php", ADMIN_URL_CLIENT . "home_content_deliveryx.php", ADMIN_URL_CLIENT . "home_content_delivery_action.php", ADMIN_URL_CLIENT . "home_content_delivery.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $deliveryFileArr);
        }
        if (strtoupper(ONLYDELIVERALL) == "YES") {
            //echo "ONLYDELIVERALL<br>";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/left_menu_ufx_array.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/left_menu_uberapp_array.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridedeliveryx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_taxi_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_taxi.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridedeliveryx_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridedeliveryx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridecx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_deliveryx_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_deliveryx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_delivery_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_delivery.php";
        }
        if (strtoupper($eCubeX) == "NO" && strtoupper($ePlusXCubeX) == "NO") {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubex_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubex.php";
        }
        if (strtoupper($eCubejekX) == "NO" && strtoupper($eKingX) == "NO") {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubejekx_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubejekx.php";
        }
    }
    //echo "<pre>";print_r($cubejekXCubeXFileArr);die;
    foreach ($cubejekXCubeXFileArr as $key => $filename) {
        if (file_exists(dirname(__FILE__) . "/" . $filename)) {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
            $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
        }
    }
    //Added By HJ On 24-01-2020 For Removed Unwanted Filed of Cubex and CubejekX Setup End
    //Added By HJ On 12-02-2020 For Check Sample Image Water Mark Status Start
    
    $sampleImgTableName = "sample_image_master";
    $dbAllTablesArr = getAllTableArray(); // For Get Current Db's All Table Arr
    $sample_image_master = checkTableExists($sampleImgTableName, $dbAllTablesArr);
    //echo "<pre>";print_r($sample_image_master);die;
    if ($sample_image_master > 0 && (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES" || strtoupper($eRideX) == "YES" || strtoupper($eDeliveryX) == "YES" || strtoupper($eServiceX) == "YES" || strtoupper($eRideDeliveryX) == "YES" || strtoupper($eDeliverykingx) == "YES" || strtoupper($eCubeDoctorX) == "YES" || strtoupper($eKingX) == "YES" || strtoupper($ePlusXTaxi) == "YES" || strtoupper($eSuperXSP) == "YES" || strtoupper($ePlusXDeliveryKing) == "YES" || strtoupper($ePlusXDeliverAll) == "YES" || strtoupper($ePlusXCubeX) == "YES" || strtoupper($ePlusXDelivery) == "YES" || strtoupper($eKingXPro) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProDeliverAll) == "YES" || strtoupper($eProDelivery) == "YES" || strtoupper($eProDeliveryKing) == "YES" || strtoupper($eProCarPool) == "YES" || strtoupper($eProKingX) == "YES" || strtoupper($eProMedicalAllinone) == "YES" || strtoupper($eProCubeXGC) == "YES")) {
        if ($eCubeX == "Yes") {
            $eTypeApp = "Cubex";
        } else if ($eCubejekX == "Yes") {
            $eTypeApp = "CubeJekX";
        } else if (strtoupper($eDeliverallX) == "YES") {
            $eTypeApp = "DeliverallX";
        } else if (strtoupper($eRideX) == "YES") {
            $eTypeApp = "RideX";
        } else if (strtoupper($eDeliveryX) == "YES") {
            $eTypeApp = "DeliveryX";
        } else if (strtoupper($eServiceX) == "YES") {
            $eTypeApp = "ServiceX";
        } else if (strtoupper($eRideDeliveryX) == "YES") {
            $eTypeApp = "Ride-DeliveryX";
        } else if (strtoupper($eDeliverykingx) == "YES") {
            $eTypeApp = "DeliveryKingX";
        } else if (strtoupper($eCubeDoctorX) == "YES") {
            $eTypeApp = "CubeDoctorX";
        } else if (strtoupper($eKingX) == "YES") {
            $eTypeApp = "KingX";
        } else if (strtoupper($ePlusXTaxi) == "YES") {
            $eTypeApp = "PlusXTaxi";
        } else if (strtoupper($eSuperXSP) == "YES") {
            $eTypeApp = "SuperXSP";
        } else if (strtoupper($ePlusXDeliveryKing) == "YES") {
            $eTypeApp = "PlusXDeliveryKing";
        } else if (strtoupper($ePlusXDeliverAll) == "YES") {
            $eTypeApp = "PlusXDeliverAll";
        } else if (strtoupper($ePlusXCubeX) == "YES") {
            $eTypeApp = "PlusXCubeX";
        } else if (strtoupper($ePlusXDelivery) == "YES") {
            $eTypeApp = "PlusXDelivery";
        } else if (strtoupper($eKingXPro) == "YES") {
            $eTypeApp = "KingXPro";
        } else if (strtoupper($eProRideParcel) == "YES") {
            $eTypeApp = "PXRDPro";
        } elseif (strtoupper($eProCubeX) == "YES") {
            $eAppType == "PXCPro";
        } elseif (strtoupper($eProTaxi) == "YES") {
            $eAppType == "PXTPro";
        } elseif (strtoupper($eProSP) == "YES") {
            $eAppType == "ProSP";
        } elseif (strtoupper($eProDeliverAll) == "YES") {
            $eAppType == "ProDAX";
        } elseif (strtoupper($eProDelivery) == "YES") {
            $eAppType == "ProDY";
        } elseif (strtoupper($eProDeliveryKing) == "YES") {
            $eAppType == "ProDK";
        } elseif (strtoupper($eProCarPool) == "YES") {
            $eAppType == "ProRS";
        } elseif (strtoupper($eProKingX) == "YES") {
            $eAppType == "ProKX";
        } elseif (strtoupper($eProMedicalAllinone) == "YES") {
            $eAppType == "ProMS";
        } elseif (strtoupper($eProCubeXGC) == "YES") {
            $eAppType == "ProCubeXGC";
        } else {
            $eTypeApp = "";
            $obj->sql_query("TRUNCATE " . $sampleImgTableName);
        }
        if ($eTypeApp != "") {
            $sampleImages = $obj->MysqlSelect("SELECT iSamleId,vImagePath,vImageName FROM " . $sampleImgTableName . " WHERE eIsSample='Yes' AND eType='" . $eTypeApp . "' AND eStatus='Active'");
            if (scount($sampleImages) > 0) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Convert Sample Image Into Water Mark</li>";
            }
            //echo "<pre>";print_r($sampleImages);die;
        }
    }
    //Added By HJ On 12-02-2020 For Check Sample Image Water Mark Status End
    if (DELIVERALL == "Yes" && ($eProductType == "Ride" || $eProductType == "Ride-Delivery" || $eProductType == "Delivery" || $eProductType == "UberX")) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Update DELIVERALL value as 'No' in configuration_variables file.</li>";
    }
    if ($eProductType == 'Foodonly' || $eProductType == 'Deliverall') {

        if (DELIVERALL == 'No') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update DELIVERALL value as 'Yes' in configuration_variables file.</li>";
        }

        if (ONLYDELIVERALL == 'No') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ONLYDELIVERALL value as 'Yes' in configuration_variables file.</li>";
        }

        if (ENABLE_RENTAL_OPTION == 'Yes') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'No' in configuration_variables file.</li>";
        }

        if ($eDeliveryType != 'Multi') {
            if (ENABLE_MULTI_DELIVERY == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
            }
        }

        foreach ($db_service_categories as $key => $value) {
            $iServiceId = $value['iServiceId'];
            if (!empty($iServiceId)) {
                $q1 = "show tables like 'language_label_" . $iServiceId . "'";
                $dbchecktabel = $obj->MySQLSelect($q1);
                if (scount($dbchecktabel) <= 0) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Create 'language_label_" . $iServiceId . "' tabel using language_label_2 tabel.</li>";
                }
            }
        }

        //if (!empty($matchresult) || !empty($matchresult2)) { // Commented By HJ On 08-02-2020 As Per Discuss With KS Sir
        if (!empty($matchresult2)) { // Added By HJ On 08-02-2020 As Per Discuss With KS Sir
            $errorcountsystemvalidation += 1;
            echo "<li>Please Add or Remove 'Service Ids' in configuration file in array 'service_categories_ids_arr'.</li>";
        }

        //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName='DRIVER_REQUEST_METHOD' || vName = 'ENABLE_TOLL_COST' || vName = 'TOLL_COST_APP_ID' || vName = 'TOLL_COST_APP_CODE' || vName = 'ENABLE_HAIL_RIDES' ||  vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL')";
        //$obj->sql_query($sql3); // By HJ On 07-03-2019
        //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'Yes' WHERE (vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE' || vName = 'ADMIN_COMMISSION' || vName = 'MIN_ORDER_CANCELLATION_CHARGES' || vName = 'COMPANY_EMAIL_VERIFICATION' || vName = 'COMPANY_PHONE_VERIFICATION')";
        //$obj->sql_query($sql3); // By HJ On 07-03-2019

        /* $query2 = 'TRUNCATE TABLE configurations_cubejek';
          $obj->sql_query($query2); */

        //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL'|| vName = 'ENABLE_HAIL_RIDES' || vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE' || vName =  'BOOKING_LATER_ACCEPT_BEFORE_INTERVAL' || vName =  'BOOKING_LATER_ACCEPT_AFTER_INTERVAL')";
        //$obj->sql_query($sql5); // By HJ On 07-03-2019
        //$sql4 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'DELIVERY_VERIFICATION_METHOD')";
        //$obj->sql_query($sql4); // By HJ On 07-03-2019
    } else {
        if ($ePackageType == 'standard') {
            if (ENABLE_RENTAL_OPTION == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'No' in configuration_variables file.</li>";
            }
            /* if($eDeliveryType != 'Multi'){
              if(ENABLE_MULTI_DELIVERY == 'Yes'){
              $errorcountsystemvalidation +=1;
              echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
              }
              } */
            if (DELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update DELIVERALL value as 'No' in configuration_variables file.</li>";
            }
            if (ONLYDELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ONLYDELIVERALL value as 'No' in configuration_variables file.</li>";
            }
            //$sql4 = "UPDATE configurations SET `vValue` = 'No' WHERE (vName = 'ENABLE_TOLL_COST'||  vName = 'CALLMASKING_ENABLED' || vName = 'ENABLE_HAIL_RIDES' ||  vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL' || vName = 'WAYBILL_ENABLE')";
            //$obj->sql_query($sql4); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'WAYBILL_ENABLE')"); // By HJ On 07-03-2019
            if ($eProductType == 'UberX' || $eProductType == 'Delivery' || $eProductType == 'Foodonly' || $eProductType == 'Deliverall') {
                //$sql8 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')";
                //$obj->sql_query($sql8); // By HJ On 07-03-2019
            }
            //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName = 'ENABLE_TOLL_COST' || vName = 'TOLL_COST_APP_ID' || vName = 'TOLL_COST_APP_CODE' || vName = 'CALLMASKING_ENABLED' || vName = 'ENABLE_HAIL_RIDES' ||  vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL')";
            //$obj->sql_query($sql3); // By HJ On 07-03-2019
            $standardfilearray = array(ADMIN_URL_CLIENT . "/masking_numbers.php", ADMIN_URL_CLIENT . "/masking_numbers_action.php", ADMIN_URL_CLIENT . "/location_wise_fare.php", ADMIN_URL_CLIENT . "/location_wise_fare_action.php", ADMIN_URL_CLIENT . "/locationwise_fare.php", ADMIN_URL_CLIENT . "/rental_package.php", ADMIN_URL_CLIENT . "/action/masking_numbers.php", ADMIN_URL_CLIENT . "/action/locationwise_fare.php", ADMIN_URL_CLIENT . "/action/rental_package.php");
            foreach ($standardfilearray as $key => $filename) {
                if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                }
            }
            $frontFilesarray = array("call.php", "callmask.php", "tollroute.php");
            foreach ($frontFilesarray as $filenamefront) {
                if (file_exists(dirname(__FILE__) . "/" . $filenamefront)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Root " . $filenamefront . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filenamefront;
                }
            }
            $foldersArray = array("assets/libraries/adyen", "assets/libraries/MPesa", "assets/libraries/omise", "assets/libraries/paymaya", "assets/libraries/xendit", "assets/libraries/webview/flutterwave", "assets/libraries/webview/flutterwave_old", "assets/libraries/webview/hyper_pay", "assets/libraries/webview/hyperpay", "assets/libraries/webview/iugu", "assets/libraries/webview/payu_ro", "assets/libraries/webview/payubiz", "assets/libraries/webview/payulatam", "assets/libraries/webview/payzen", "assets/libraries/webview/mpesa", "assets/libraries/webview/final_payu_live_update", "assets/libraries/webview/alu-client-php-master", "assets/libraries/webview/static_google_map", "assets/libraries/webview/mcp");
            foreach ($foldersArray as $k => $val) {
                if (is_dir(dirname(__FILE__) . "/" . $val)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete Folders From assets/libraries : " . $val . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $val;
                }
            }
            $webserviceIncludeFilesArr = array("include/include_webservice_dl_enterprisefeatures.php", "include/include_webservice_enterprisefeatures.php");
            foreach ($webserviceIncludeFilesArr as $key => $filesname) {
                if (file_exists((dirname(__FILE__) . "/" . $filesname))) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From include folder " . $filesname . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filesname;
                }
            }
            if ($eProductType != 'Foodonly' || $eProductType != 'Deliverall') {
                //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE' || vName = 'ADMIN_COMMISSION' || vName = 'MIN_ORDER_CANCELLATION_CHARGES' || vName = 'COMPANY_EMAIL_VERIFICATION' || vName = 'COMPANY_PHONE_VERIFICATION')";
                //$obj->sql_query($sql3); // By HJ On 07-03-2019
            }

            if ($eDeliveryType != 'Multi') {
                //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName =  'DELIVERY_VERIFICATION_METHOD')";
                //$obj->sql_query($sql5); // By HJ On 07-03-2019
            }
        } else if ($ePackageType == 'enterprise') {
            if (ENABLE_MULTI_DELIVERY == 'No') {
                /* $sql3 = "UPDATE configurations_cubejek SET `eStatus` = 'Inactive' WHERE (vName = 'MULTI_DELIVERY_SHOW_SELECTION' || vName = 'MULTI_DELIVERY_GRID_ICON_NAME' || vName = 'MULTI_DELIVERY_BANNER_IMG_NAME')";
                  $obj->sql_query($sql3); */
            }
            if ($eProductType != 'Foodonly' || $eProductType != 'Deliverall') {
                $enterpriseOrderFilesarray = array("include/include_webservice_dl_enterprisefeatures.php");
                foreach ($enterpriseOrderFilesarray as $filenameEnterprise) {
                    if (file_exists(dirname(__FILE__) . "/" . $filenameEnterprise)) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Delete File From Include " . $filenameEnterprise . "</li>";
                        $deleteFileArr[] = dirname(__FILE__) . "/" . $filenameEnterprise;
                    }
                }
            }

            if ($eProductType != 'Delivery' && $eProductType != 'UberX') {
                if (ENABLE_RENTAL_OPTION == 'No') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'Yes' in configuration_variables file.</li>";
                }
            } else {
                if (ENABLE_RENTAL_OPTION == 'Yes') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'No' in configuration_variables file.</li>";
                }
            }

            if ($eDeliveryType != 'Multi') {
                if (ENABLE_MULTI_DELIVERY == 'Yes') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
                }
            }

            if (DELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update DELIVERALL value as 'No' in configuration_variables file.</li>";
            }

            if (ONLYDELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ONLYDELIVERALL value as 'No' in configuration_variables file.</li>";
            }
            //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'Yes' WHERE (vName='CALLMASKING_ENABLED' || vName = 'ENABLE_TOLL_COST' || vName ='TOLL_COST_APP_ID' ||  vName ='TOLL_COST_APP_CODE')";
            //$obj->sql_query($sql3); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No',`vValue`='Yes' WHERE vName =  'WAYBILL_ENABLE'"); // Added By HJ On 02-03-2019
            if ($eProductType == 'Ride' || $eProductType == 'Ride-Delivery-UberX' || $eProductType == 'Ride-Delivery') {
                //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'Yes' WHERE ( vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL'|| vName = 'ENABLE_HAIL_RIDES' || vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')";
                //$obj->sql_query($sql5);
            } else {
                //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL'|| vName = 'ENABLE_HAIL_RIDES' || vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')";
                //$obj->sql_query($sql5);
                $enterpriseFilesarray = array(ADMIN_URL_CLIENT . "/rental_package.php", ADMIN_URL_CLIENT . "/rental_vehicle_list.php", ADMIN_URL_CLIENT . "/action/rental_package.php");
                foreach ($enterpriseFilesarray as $filenameEnterprise) {
                    if (file_exists(dirname(__FILE__) . "/" . $filenameEnterprise)) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Delete File From Admin " . $filenameEnterprise . "</li>";
                        $deleteFileArr[] = dirname(__FILE__) . "/" . $filenameEnterprise;
                    }
                }
            }
            ?>
        <?php } else { ?>
            <?php
            if (DELIVERALL == 'No' && ($eProductType == "Ride-Delivery-UberX" || $eProductType == 'Foodonly' || $eProductType == 'Deliverall')) {
                $deliverAllFlag = 1;
                if ($eCubejekX == "Yes" && $Deliverall == "No") {
                    $deliverAllFlag = 0;
                }
                if ($deliverAllFlag > 0) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update DELIVERALL value as 'Yes' in configuration_variables file.</li>";
                }
            }
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Voip' WHERE vName='RIDE_DRIVER_CALLING_METHOD'");
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No',`vValue`='Yes' WHERE vName =  'WAYBILL_ENABLE'"); // Added By HJ On 02-03-2019

            if ($eDeliveryType != 'Multi') {
                if (ENABLE_MULTI_DELIVERY == 'Yes') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
                }
            }
            /* $sql2 = "UPDATE configurations_cubejek SET `eStatus` = 'Active' WHERE (vName = 'FOOD_APP_SHOW_SELECTION' || vName = 'FOOD_APP_GRID_ICON_NAME' || vName = 'FOOD_APP_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_GRID_ICON_NAME' || vName = 'FOOD_APP_PACKAGE_NAME' || vName = 'FOOD_APP_IOS_APP_ID' || vName = 'FOOD_APP_IOS_PACKAGE_NAME'  || vName = 'FOOD_APP_SERVICE_ID' || vName = 'GROCERY_APP_SHOW_SELECTION' || vName = 'GROCERY_APP_GRID_ICON_NAME' || vName = 'GROCERY_APP_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || vName = 'GROCERY_APP_PACKAGE_NAME' || vName = 'GROCERY_APP_IOS_APP_ID' || vName = 'GROCERY_APP_IOS_PACKAGE_NAME' || vName = 'GROCERY_APP_SERVICE_ID' || vName = 'DELIVER_ALL_APP_IOS_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_IOS_APP_ID' || vName = 'DELIVER_ALL_APP_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || vName = 'DELIVER_ALL_APP_BANNER_IMG_NAME' || vName='DELIVER_ALL_APP_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_SHOW_SELECTION' )";
              $obj->sql_query($sql2); */
            foreach ($db_service_categories as $key => $value) {
                $iServiceId = $value['iServiceId'];
                if (!empty($iServiceId)) {
                    $q1 = "show tables like 'language_label_" . $iServiceId . "'";
                    $dbchecktabel = $obj->MySQLSelect($q1);
                    if (scount($dbchecktabel) <= 0) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Create 'language_label_" . $iServiceId . "' tabel using language_label_2 tabel.</li>";
                    }
                }
            }
            //if (!empty($matchresult) || !empty($matchresult2)) { // Commented By HJ On 08-02-2020 As Per Discuss With KS Sir
            if (!empty($matchresult2)) { // Added By HJ On 08-02-2020 As Per Discuss With KS Sir
                $errorcountsystemvalidation += 1;
                echo "<li>Please Add 'Service Ids' in configuration file in array 'service_categories_ids_arr'.</li>";
            }
        }
    }
    //Added By HJ On 12-07-2019 For Removed New Addon Files As Per Selection Start
    $addonFilesArr = $permissionArr = $emailTemplateArr = $smsTemplateArr = array();
    // $permissionArr[] = "expired-documents"; //Added By HJ On 08-02-2020 For Removed Expired Document Feature As Per Discuss With KS Sir
    if ($DONATION == "No" || $DONATION != "Yes") {
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/donation.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/action/donation.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/donation_action.php";
        $addonFilesArr[] = "include/features/include_donation.php";
        $permissionArr[] = "create-donation";
        $permissionArr[] = "delete-donation";
        $permissionArr[] = "edit-donation";
        $permissionArr[] = "update-status-donation";
        $permissionArr[] = "view-donation";
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DONATION_ENABLE'");
        $obj->sql_query("UPDATE " . $sql_vehicle_category_table_name . " SET eStatus='Deleted' WHERE eCatType='Donation'");
    }
    if (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES" || strtoupper($eRideX) == "YES" || strtoupper($eDeliveryX) == "YES" || strtoupper($eServiceX) == "YES" || strtoupper($eRideDeliveryX) == "YES" || strtoupper($eDeliverykingx) == "YES" || strtoupper($eCubeDoctorX) == "YES" || strtoupper($eKingX) == "YES" || strtoupper($ePlusXTaxi) == "YES" || strtoupper($eSuperXSP) == "YES" || strtoupper($ePlusXDeliveryKing) == "YES" || strtoupper($ePlusXDeliverAll) == "YES" || strtoupper($ePlusXCubeX) == "YES" || strtoupper($ePlusXDelivery) == "YES" || strtoupper($eKingXPro) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProDeliverAll) == "YES" || strtoupper($eProDelivery) == "YES" || strtoupper($eProDeliveryKing) == "YES" || strtoupper($eProCarPool) == "YES" || strtoupper($eProKingX) == "YES" || strtoupper($eProMedicalAllinone) == "YES" || strtoupper($eProCubeXGC) == "YES") {

        $getAllPermission = $obj->MySQLSelect("SELECT * FROM admin_pro_permissions WHERE eFor != 'General'");
        $adminPermissionArr = array();
        for($p=0;$p<scount($getAllPermission);$p++){
            $adminPermissionArr[$getAllPermission[$p]['eFor']][] = $getAllPermission[$p]['permission_name'];
        }
        //echo "<pre>";print_r($adminPermissionArr);die;
        if (strtoupper($kioskPanelEnable) == "NO") {
            if(isset($adminPermissionArr['Kiosk'])){
                $kioskPermission = $adminPermissionArr['Kiosk'];
                foreach($kioskPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if(strtoupper(DELIVERALL) == 'NO'){
            if(isset($adminPermissionArr['DeliverAll'])){
                $deliverallPermission = $adminPermissionArr['DeliverAll'];
                foreach($deliverallPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if (strtoupper($Fly) == "NO") {
            if(isset($adminPermissionArr['Fly'])){
                $flyPermission = $adminPermissionArr['Fly'];
                foreach($flyPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
            /*$permissionArr[] = "manage-fly-vehicles";
            $permissionArr[] = "view-fly-stations";
            $permissionArr[] = "view-fly-vehicle-type";
            $permissionArr[] = "view-fly-fare";
            $permissionArr[] = "create-fly-fare";
            $permissionArr[] = "create-fly-stations";
            $permissionArr[] = "delete-fly-fare";
            $permissionArr[] = "delete-fly-stations";
            $permissionArr[] = "edit-fly-fare";
            $permissionArr[] = "edit-fly-stations";
            $permissionArr[] = "update-status-fly-fare";
            $permissionArr[] = "update-status-fly-stations";
            $permissionArr[] = "create-fly-vehicle-type";
            $permissionArr[] = "view-fly-vehicle-type";
            $permissionArr[] = "edit-fly-vehicle-type";
            $permissionArr[] = "update-status-fly-vehicle-type";
            $permissionArr[] = "delete-fly-vehicle-type";*/
        }
        if (strtoupper($UberX) == "NO") {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_DRIVER_SERVICE_REQUEST_MODULE'");
            if(isset($adminPermissionArr['UberX'])){
                $uberXPermission = $adminPermissionArr['UberX'];
                foreach($uberXPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
            //$permissionArr[] = "create-service-type";
            //$permissionArr[] = "delete-service-type";
            //$permissionArr[] = "edit-service-type";
            //$permissionArr[] = "update-status-service-type";
            //$permissionArr[] = "view-driver-service-request";
            //$permissionArr[] = "view-service-type";
        }
        if (strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO") {
            if(isset($adminPermissionArr['Ride,Delivery'])){
                $rideDeliveryPermission = $adminPermissionArr['Ride,Delivery'];
                foreach($rideDeliveryPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if (strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO" && strtoupper($UberX) == "NO") {
            if(isset($adminPermissionArr['Ride,Delivery,UberX'])){
                $rideDeliveryUfxPermission = $adminPermissionArr['Ride,Delivery,UberX'];
                foreach($rideDeliveryUfxPermission as $key=>$val){
                    if(strtoupper($CUSTOME_APP_TYPE) == "CUBEDELIVERALLX" && ($val == "view-vehicle-category" || $val == "delete-vehicle-category" || $val == "update-status-vehicle-category" || $val == "edit-vehicle-category")){
                        // Service Category Here As Per Discuss with KS For Custome Req. CubejekX-Deliverall Setup
                    }else{
                        $permissionArr[] = $val;
                    }
                }
            }
        }
        if (strtoupper($Ride) == "NO"){
            if(isset($adminPermissionArr['Ride'])){
                $ridePermission = $adminPermissionArr['Ride'];
                foreach($ridePermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if(strtoupper($Delivery) == "NO"){
            if(isset($adminPermissionArr['Delivery'])){
                $deliveryPermission = $adminPermissionArr['Delivery'];
                foreach($deliveryPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
            if(isset($adminPermissionArr['Multi-Delivery'])){
                $mulDeliveryPermission = $adminPermissionArr['Multi-Delivery'];
                foreach($mulDeliveryPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }   
    }
    if ($DRIVER_SUBSCRIPTION == "No" || $DRIVER_SUBSCRIPTION != "Yes") {
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/driver_subscription.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/driver_subscription_action.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/driver_subscription_report.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/action/driver_subscription.php";
        $addonFilesArr[] = "cron_driver_subscription.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/ajax_driver_subscription.php";
        $addonFilesArr[] = "booking/ajax_driver_subscription.php";
        $addonFilesArr[] = "include/features/include_driver_subscription.php";
        $permissionArr[] = "create-driver-subscription";
        $permissionArr[] = "delete-driver-subscription";
        $permissionArr[] = "edit-driver-subscription";
        $permissionArr[] = "manage-driver-subscription";
        $permissionArr[] = "manage-driver-subscription-report";
        $permissionArr[] = "update-status-driver-subscription";
        $permissionArr[] = "view-driver-subscription";
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('DRIVER_SUBSCRIPTION_CANCEL','DRIVER_SUBSCRIPTION_SUCCESS')");
        $emailTemplateArr[] = "DRIVER_SUBSCRIPTION_CANCEL";
        $emailTemplateArr[] = "DRIVER_SUBSCRIPTION_SUCCESS";
        $emailTemplateArr[] = "CRON_SUBSCRIBE_REMAIN_DAYS";
    }
    if ($FAVOURITE_DRIVER == "No" || $FAVOURITE_DRIVER != "Yes") {
        $addonFilesArr[] = "include/features/include_fav_driver.php";
    }
    if ($FAVOURITE_STORE == "No" || $FAVOURITE_STORE != "Yes") {
        $addonFilesArr[] = "include/features/include_fav_store.php";
    }
    if ($GOJEK_GOPAY == "No" || $GOJEK_GOPAY != "Yes") {
        $addonFilesArr[] = "include/features/include_gojek_gopay.php";
        $emailTemplateArr[] = "WALLET_AMOUNT_TRANSFER";
        $emailTemplateArr[] = "OTP_TRANSFER_MONEY";
        $smsTemplateArr[] = "WALLET_AMOUNT_TRANSFER";
        $smsTemplateArr[] = "OTP_TRANSFER_MONEY";
    }
    if ($DRIVER_DESTINATION == "No" || $DRIVER_DESTINATION != "Yes") {
        $addonFilesArr[] = "include/features/include_destinations_driver.php";
    }
    if ($MULTI_STOPOVER_POINTS == "No" || $MULTI_STOPOVER_POINTS != "Yes") {
        $addonFilesArr[] = "include/features/include_stop_over_point.php";
    }
    if (($MANUAL_STORE_ORDER_WEBSITE == "No" && $MANUAL_STORE_ORDER_STORE_PANEL == "No" && $MANUAL_STORE_ORDER_ADMIN_PANEL == "No") || $manualOrderFiles > 0) {
        $addonFilesArr[] = "customer_info.php";
        $addonFilesArr[] = "include_generalFunctions_dl.php";
        //$addonFilesArr[] = "generalFunctions.php"; // Removed
        $addonFilesArr[] = "customer_info_action.php";
        $addonFilesArr[] = "ajax_find_rider_by_number.php";
        $addonFilesArr[] = "restaurant_listing.php";
        $addonFilesArr[] = "include/features/include_fav_store.php";
        $addonFilesArr[] = "ajax_load_store.php";
        $addonFilesArr[] = "ajax_load_fav_store.php";
        $addonFilesArr[] = "ajax_fav_manual_store.php";
        $addonFilesArr[] = "ajax_get_values_cart_to_restaurant.php";
        $addonFilesArr[] = "update_qty_item_cart_restaurant.php";
        $addonFilesArr[] = "add_cart_to_restaurant.php";
        $addonFilesArr[] = "ajax_view_cart_to_restaurant.php";
        $addonFilesArr[] = "update_item_cart_restaurant.php";
        $addonFilesArr[] = "ajax_filter_restaurant_menu_item.php";
        $addonFilesArr[] = "ajax_load_model_cart.php";
        $addonFilesArr[] = "ajax_checkout_order_details.php";
        $addonFilesArr[] = "ajax_checkout_cart_to_restaurant.php";
        $addonFilesArr[] = "ajax_check_promocode_cart_to_restaurant.php";
        $addonFilesArr[] = "remove_item_cart_to_restaurant.php";
        $addonFilesArr[] = "ajax_check_address_store.php";
        $addonFilesArr[] = "ajax_add_delivery_address.php";
        //$addonFilesArr[] = "ajax_fpass_action.php";
        $addonFilesArr[] = "restaurant_menu.php";
        $addonFilesArr[] = "restaurant_place-order.php";
        $addonFilesArr[] = "thanks.php";
        $addonFilesArr[] = "change_check_code.php";
        $addonFilesArr[] = "user_info.php";
        $addonFilesArr[] = "user_info_action.php";
        $addonFilesArr[] = "user_info_action_all.php";
        $permissionArr[] = "manage-restaurant-order";
    }
    if (($MANUAL_STORE_ORDER_WEBSITE == "No") || $manualOrderFiles > 0) {
        $addonFilesArr[] = "user_info.php";
        $addonFilesArr[] = "user_info_action.php";
        $addonFilesArr[] = "user_info_action_all.php";
    }
    if (($MANUAL_STORE_ORDER_ADMIN_PANEL == "No" && $MANUAL_STORE_ORDER_STORE_PANEL == "No") || $manualOrderFiles > 0) {
        $addonFilesArr[] = "customer_info.php";
        $addonFilesArr[] = "customer_info_action.php";
        $permissionArr[] = "manage-restaurant-order";
    }

    if((strtoupper($eDeliverallX) == "YES" || strtoupper($ePlusXDeliverAll) == "YES" || strtoupper($eProDeliverAll) == "YES") && strtoupper(ONLYDELIVERALL) == "YES") {
        if (($pkey1 = array_search('view-blocked-rider', $permissionArr)) !== false) {
            unset($permissionArr[$pkey1]);
        }
        if (($pkey2 = array_search('update-status-blocked-rider', $permissionArr)) !== false) {
            unset($permissionArr[$pkey2]);
        }
        if (($pkey3 = array_search('delete-blocked-rider', $permissionArr)) !== false) {
            unset($permissionArr[$pkey3]);
        }
        if (($pkey4 = array_search('view-blocked-driver', $permissionArr)) !== false) {
            unset($permissionArr[$pkey4]);
        }
        if (($pkey5 = array_search('update-status-blocked-driver', $permissionArr)) !== false) {
            unset($permissionArr[$pkey5]);
        }
        if (($pkey6 = array_search('delete-blocked-driver', $permissionArr)) !== false) {
            unset($permissionArr[$pkey6]);
        }

        $obj->sql_query("UPDATE admin_pro_permissions SET eFor = 'DeliverAll' WHERE permission_name IN ('view-blocked-rider', 'update-status-blocked-rider', 'delete-blocked-rider', 'view-blocked-driver', 'update-status-blocked-driver', 'delete-blocked-driver')");
    }
    
    if (scount($permissionArr) > 0) {
        $delPermission = "'" . implode("','", $permissionArr) . "'";
        $obj->sql_query("DELETE FROM admin_pro_permissions WHERE permission_name IN ($delPermission)");
    }
    foreach ($addonFilesArr as $key => $addOnFile) {
        if (file_exists(dirname(__FILE__) . "/" . $addOnFile)) {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Delete File Of New Addon " . $addOnFile . "</li>";
            $deleteFileArr[] = dirname(__FILE__) . "/" . $addOnFile;
        }
    }
    //Added By HJ On 12-07-2019 For Removed New Addon Files As Per Selection End
    //Added By HJ On 30-07-2019 For Removed New Addon's SMS and Email Template Start
    for ($e = 0; $e < scount($emailTemplateArr); $e++) {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code='" . $emailTemplateArr[$e] . "'"); // By HJ On 20-03-2019 For Solved Bug - 6403
    }
    for ($s = 0; $s < scount($emailTemplateArr); $s++) {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code='" . $emailTemplateArr[$s] . "'"); // By HJ On 20-03-2019 For Solved Bug - 6403
    }
    //Added By HJ On 30-07-2019 For Removed New Addon's SMS and Email Template End
    //Added By HJ On 20-03-2019 For Remove SMS and Email Templates Start Bug - 6406
    if (strtoupper($ePackageType) != "SHARK") {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('USER_REGISTRATION_ORGANIZATION','ORGANIZATION_UPDATE_USERPROFILESTATUS_TO_USER','ORGANIZATION_REGISTRATION_ADMIN','ORGANIZATION_REGISTRATION_USER','ADMIN_UPDATE_USERPROFILESTATUS_TO_ORGANIZATION','STORE_REGISTRATION_USER','STORE_REGISTRATION_ADMIN','MEMBER_BLOCKED_INACTIVE_DRIVER','MEMBER_BLOCKED_ACTIVE_DRIVER','MEMBER_BLOCKED_INACTIVE_USER','MEMBER_BLOCKED_ACTIVE_USER','MEMBER_NEWS_SUBSCRIBE_USER','MEMBER_NEWS_UNSUBSCRIBE_USER')");
    }
    //Added By HJ On 01-07-2019 As Per Discuss With KS Sir Start
    if ($hotelPanelEnable == "No") {
        $obj->sql_query("DELETE FROM administrators WHERE iGroupId='4'"); // By HJ On 20-03-2019 For Solved Bug - 6403
        $obj->sql_query("DELETE FROM admin_groups WHERE iGroupId='4'"); // By HJ On 20-03-2019 For Solved Bug - 6403
        $obj->sql_query("DELETE FROM admin_group_permission WHERE group_id='4'"); // By HJ On 20-03-2019 For Solved Bug - 6403
        $obj->sql_query("DELETE FROM admin_pro_permissions WHERE `permission_name` LIKE 'manage-hotel-payment-report'"); // By HJ On 20-03-2019 For Solved Bug - 6403
    }
    //Added By HJ On 01-07-2019 As Per Discuss With KS Sir End
    if ($eProductType == "Delivery" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('USER_REGISTRATION_ORGANIZATION','ORGANIZATION_UPDATE_USERPROFILESTATUS_TO_USER','ORGANIZATION_REGISTRATION_ADMIN','ORGANIZATION_REGISTRATION_USER','ADMIN_UPDATE_USERPROFILESTATUS_TO_ORGANIZATION')");
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('EMERGENCY_SMS_FOR_USER_RIDE','EMERGENCY_SMS_FOR_DRIVER_RIDE')");
    }
    if ($eProductType == "Ride" || $eProductType == "Delivery" || $eProductType == "Ride-Delivery" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('MANUAL_TAXI_DISPATCH_DRIVER_APP_SP','MANUAL_BOOKING_ACCEPT_BYDRIVER_SP','MANUAL_BOOKING_DECLINED_BYDRIVER_SP','MANUAL_BOOKING_CANCEL_BYDRIVER_SP','MANUAL_BOOKING_CANCEL_BYRIDER_SP')");
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('EMERGENCY_SMS_FOR_USER_SP','EMERGENCY_SMS_FOR_DRIVER_SP','DRIVER_SEND_MESSAGE_SP','BOOKING_ACCEPT_BYDRIVER_MESSAGE_SP','BOOKING_DECLINED_BYDRIVER_MESSAGE_SP','BOOKING_CANCEL_BYRIDER_MESSAGE_SP','BOOKING_CANCEL_BYDRIVER_MESSAGE_SP')");
    }
    if (strtoupper($ePackageType) != "SHARK" || $eProductType == "Delivery" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('BOOK_FOR_SOMEONE_ELSE_SMS')");
    }
    if ($kioskPanelEnable != "Yes") {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('BOOKING_IN_KIOSK')");
    }
    if ($eProductType == "Ride" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('EMERGENCY_SMS_FOR_USER_DELIVERY','EMERGENCY_SMS_FOR_DRIVER_DELIVERY')");
    }
    if (strtoupper($ePackageType) == "STANDARD" && ($eProductType == "Delivery" || $eProductType == "Ride-Delivery")) {
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE eDeliveryType='Multi'");
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE eFor='DeliveryCategory' AND eCatType='MultipleDelivery'");
        if (ENABLE_MULTI_DELIVERY == 'Yes') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
        }
    }
    if ($eProductType == "Ride-Delivery-UberX" && strtoupper($ePackageType) != "SHARK") {
        $getCatId = $obj->MySQLSelect("SELECT VC.iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " AS VC WHERE VC.eCatType='MoreDelivery' AND VC.eFor='DeliveryCategory'");
        $catIds = "";
        for ($fr = 0; $fr < scount($getCatId); $fr++) {
            $catIds .= ",'" . $getCatId[$fr]['iVehicleCategoryId'] . "'";
        }
        if ($catIds != "") {
            $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE iParentId=" . trim($catIds, ","));
        }
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE (((eFor='DeliveryCategory' OR eFor='DeliverAllCategory') AND (eCatType='MoreDelivery' OR eCatType='MultipleDelivery')) OR (eCatType='DeliverAll') OR eDeliveryType='Multi')");
        $obj->sql_query("UPDATE " . $sql_vehicle_category_table_name . " SET iParentId='0',eShowType='Icon' WHERE eCatType='Delivery' OR eCatType='MotoDelivery'");
        $obj->sql_query("DELETE FROM cancel_reason WHERE eType='Deliverall'"); // For Solved Bug - 6467
        $obj->sql_query("DELETE FROM document_master WHERE doc_usertype='store'"); // For Solved Bug - 6465
        $obj->sql_query("DELETE FROM help_detail WHERE eSystem='DeliverAll'"); // For Solved Bug - 6466
        $obj->sql_query("DELETE FROM help_detail_categories WHERE eSystem='DeliverAll'"); // For Solved Bug - 6466
    }
    if (strtoupper($ePackageType) == "STANDARD") {
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE eCatType='Rental' OR eCatType='MotoRental'");
    }
    //Added By HJ On 20-03-2019 For Remove SMS and Email Templates End Bug - 6406
    //Added By HJ On 11-03-2019 For Set Default configurations As Per KS Sir Start
    if ($applyConfiguration == 1) {
        //Added By HJ On 12-07-2019 For Set New Addon Configuration Based On Selection Start
        if ($DONATION == "No" || $DONATION != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DONATION_ENABLE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DONATION_ENABLE'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_SUBSCRIPTION_ENABLE'"); // Added By HJ ON 19-01-2020 FOr Default Set No As Per Discuss WIth KS and CD sir
        //Added By HJ On 19-02-2020 For Set CubeX and CubeJekX Configuration Setting Start 
        
        $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'No',eAdminDisplay='No',eStatus='Active' WHERE vName='CREDIT_TO_WALLET_ENABLE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Twilio',eAdminDisplay='No',eStatus='Active' WHERE vName='MOBILE_NO_VERIFICATION_METHOD'");
        if ($GOOGLE_PLAN > 0 || !empty($GOOGLE_PLAN)) { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Advance',eAdminDisplay='No',eStatus='Active' WHERE vName='MAPS_API_REPLACEMENT_STRATEGY'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Active' WHERE vName='ENABLE_MAPS_API_REPLACEMENT'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'None',eAdminDisplay='No',eStatus='Active' WHERE vName='MAPS_API_REPLACEMENT_STRATEGY'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Active' WHERE vName='ENABLE_MAPS_API_REPLACEMENT'");
        }
        //Added By HJ On 19-02-2020 For Set CubeX and CubeJekX Configuration Setting End
        if ($DRIVER_SUBSCRIPTION == "No" || $DRIVER_SUBSCRIPTION != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE vName='DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS'");
        } else {
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_SUBSCRIPTION_ENABLE'"); // Commented By HJ ON 19-01-2020 FOr Default Set No As Per Discuss WIth KS and CD sir
            $obj->sql_query("UPDATE configurations SET `vValue` = '3',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS'");
        }
        if ($FAVOURITE_DRIVER == "No" || $FAVOURITE_DRIVER != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_FAVORITE_DRIVER_MODULE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_FAVORITE_DRIVER_MODULE'");
        }
        if (($FAVOURITE_STORE == "No" || $FAVOURITE_STORE != "Yes") || $manualOrderFiles > 0) { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_FAVORITE_STORE_MODULE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_FAVORITE_STORE_MODULE'");
        }
        if ($GOJEK_GOPAY == "No" || $GOJEK_GOPAY != "Yes") { // Common
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_GOPAY'");
            $obj->sql_query("UPDATE configurations_payment SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='WALLET_MINIMUM_BALANCE_GOPAY' || vName='GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION' || vName='GOPAY_MAXIMUM_LIMIT_PER_DAY')");
        } else {
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_GOPAY'");
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = '15',eAdminDisplay='Yes',eStatus='Active' WHERE vName='WALLET_MINIMUM_BALANCE_GOPAY'");
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = '100',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION' || vName='GOPAY_MAXIMUM_LIMIT_PER_DAY')");
        }
        if ($DRIVER_DESTINATION == "No" || $DRIVER_DESTINATION != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_DRIVER_DESTINATIONS'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='MAX_DRIVER_DESTINATIONS' || vName='DRIVER_DESTINATIONS_RESET_TIME' || vName='RESTRICTION_KM_NEAREST_DESTINATION_DRIVER')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_DRIVER_DESTINATIONS'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '3',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAX_DRIVER_DESTINATIONS'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '13:16',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_DESTINATIONS_RESET_TIME'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='Yes',eStatus='Active' WHERE vName='RESTRICTION_KM_NEAREST_DESTINATION_DRIVER'");
        }
        if ($MULTI_STOPOVER_POINTS == "No" || $MULTI_STOPOVER_POINTS != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_STOPOVER_POINT'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE vName='MAX_NUMBER_STOP_OVER_POINTS'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_STOPOVER_POINT'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '3',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAX_NUMBER_STOP_OVER_POINTS'");
        }
        if(strtoupper($MANUAL_TOLL_FEATURE_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_MANUAL_TOLL_FEATURE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"] = array("vValue" => "Approval", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_MANUAL_TOLL_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"] = array("vValue" => "Approval", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($SAFETY_MODULE) == "YES"){
             $cubejekXConfigArr["ENABLE_SAFETY_PRACTICE"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_SAFETY_PRACTICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($SAFETY_RATING_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_SAFETY_RATING"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_SAFETY_RATING"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($SAFETY_CHECK_LIST_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_SAFETY_CHECKLIST"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            
            if($eCubeX == "Yes" || $eCubejekX == "Yes" || $eRideX == "Yes" || $eRideDeliveryX == "Yes" || $eCubeDoctorX == "Yes" || $eKingX == "Yes" || $ePlusXTaxi == "Yes" || $ePlusXCubeX == "Yes" || $eKingXPro == "Yes" || $eProRideParcel == "Yes" || $eProCubeX == "Yes" || $eProTaxi == "Yes" || $eProKingX == "Yes" || $eProMedicalAllinone == "Yes" || $eProCubeXGC == "Yes") {
                $cubejekXConfigArr["ENABLE_SAFETY_FEATURE_RIDE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
            }else{
                $cubejekXConfigArr["ENABLE_SAFETY_FEATURE_RIDE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }

            if($eCubeX == "Yes" || $eCubejekX == "Yes" || $eDeliveryX == "Yes" || $eRideDeliveryX == "Yes" || $eDeliverykingx == "Yes" || $eKingX == "Yes" || $ePlusXDeliveryKing == "Yes" || $ePlusXCubeX == "Yes" || $ePlusXDelivery == "Yes" || $eKingXPro == "Yes" || $eProRideParcel == "Yes" || $eProCubeX == "Yes" || $eProDelivery == "Yes" || $eProDeliveryKing == "Yes" || $eProKingX == "Yes" || $eProCubeXGC == "Yes") {
                $cubejekXConfigArr["ENABLE_SAFETY_FEATURE_DELIVERY"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
            }else{
                $cubejekXConfigArr["ENABLE_SAFETY_FEATURE_DELIVERY"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }

            if($eCubejekX == "Yes" || $eServiceX == "Yes" || $eCubeDoctorX == "Yes" || $eKingX == "Yes" || $eSuperXSP == "Yes" || $eKingXPro == "Yes" || $eProKingX == "Yes" || $eProMedicalAllinone == "Yes") {
                $cubejekXConfigArr["ENABLE_SAFETY_FEATURE_UFX"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
            }else{
                $cubejekXConfigArr["ENABLE_SAFETY_FEATURE_UFX"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
        }else{
            $cubejekXConfigArr["ENABLE_SAFETY_CHECKLIST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($SAFETY_FACEMASK_VERIFICATION_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_FACE_MASK_VERIFICATION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_FACE_MASK_VERIFICATION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($TIMESLOT_AVAILIBILITY) == "YES"){
            $cubejekXConfigArr["ENABLE_TIMESLOT_ADDON"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_TIMESLOT_ADDON"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($STORE_SEARCH_BY_ITEM_NAME) == "YES"){
            $cubejekXConfigArr["ENABLE_ITEM_SEARCH_STORE_ORDER"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_ITEM_SEARCH_STORE_ORDER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($MULTI_SINGLE_STORE_MODULE) == "YES"){
            $cubejekXConfigArr["IS_SINGLE_STORE_SELECTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["IS_SINGLE_STORE_SELECTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($EIGHTEEN_PLUS_FEATURE_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_TERMS_SERVICE_CATEGORIES"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_TERMS_SERVICE_CATEGORIES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($CONTACTLESS_DELIVERY_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_DELIVERY_PREFERENCE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_DELIVERY_PREFERENCE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($CATEGORY_WISE_STORE_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_STORE_CATEGORIES_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_STORE_CATEGORIES_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($FARE_MODEL_STRATEGY) == "YES"){
            $cubejekXConfigArr["ENABLE_FARE_MODEL_STRATEGY"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_FARE_MODEL_STRATEGY"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($STORE_WISE_COMMISSION) == "YES"){
            $cubejekXConfigArr["ENABLE_STORE_WISE_COMMISSION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_STORE_WISE_COMMISSION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($DELIVERY_TIP_MODULE) == "YES"){
            $cubejekXConfigArr["ENABLE_TIP_MODULE_DELIVERALL"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_TIP_MODULE_DELIVERALL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($DISTANCE_WISE_DELIVERY_CHARGES) == "YES"){
            $cubejekXConfigArr["ENABLE_CUSTOM_DELIVERY_CHARGE_ORDER"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_CUSTOM_DELIVERY_CHARGE_ORDER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($VOICE_INSTRUCTION) == "YES"){
            $cubejekXConfigArr["ENABLE_DELIVERY_INSTRUCTIONS_ORDERS"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_DELIVERY_INSTRUCTIONS_ORDERS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($STORE_UPLOAD_PHOTOS) == "YES"){
            $cubejekXConfigArr["ENABLE_STORE_WISE_BANNER"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_STORE_WISE_BANNER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($CANCEL_DRIVER_ORDER) == "YES"){
            $cubejekXConfigArr["ENABLE_CANCEL_DRIVER_ORDER"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_CANCEL_DRIVER_ORDER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($MULTIPLE_DELIVERY_IN_STORES) == "YES"){
            $cubejekXConfigArr["ENABLE_ACCEPT_MULTIPLE_ORDERS"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MULTIPLE_ORDER_PICKUP_RANGE"] = array("vValue" => "3", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MULTIPLE_ORDER_DELIVERY_RANGE"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MAXIMUM_MULTIPLE_ORDERS_COUNT"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_ACCEPT_MULTIPLE_ORDERS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MULTIPLE_ORDER_PICKUP_RANGE"] = array("eAdminDisplay" => "No", "eStatus" => "Active");
            $cubejekXConfigArr["MULTIPLE_ORDER_DELIVERY_RANGE"] = array("eAdminDisplay" => "No", "eStatus" => "Active");
            $cubejekXConfigArr["MAXIMUM_MULTIPLE_ORDERS_COUNT"] = array("eAdminDisplay" => "No", "eStatus" => "Active");
        }
        if(strtoupper($DELIVERY_HELPER) == "YES"){
            $cubejekXConfigArr["ENABLE_DELIVERY_HELPER"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_DELIVERY_HELPER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($CUSTOM_PUSH_NOTIFICATION) == "YES"){
            $cubejekXConfigArr["ENABLE_CUSTOM_NOTIFICATION"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_CUSTOM_NOTIFICATION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($OTP_VERIFICATION) == "YES" && ($eCubeX == "Yes" || $eCubejekX == "Yes" || $eDeliverallX == "Yes" || $eDeliverykingx == "Yes" || $eCubeDoctorX == "Yes" || $eKingX == "Yes" || $ePlusXDeliveryKing == "Yes" || $ePlusXDeliverAll == "Yes" || $ePlusXCubeX == "Yes" || $eKingXPro == "Yes" || $eProCubeX == "Yes" || $eProDeliverAll == "Yes" || $eProDeliveryKing == "Yes" || $eProKingX == "Yes" || $eProMedicalAllinone == "Yes" || $eProCubeXGC == "Yes")) {
            $cubejekXConfigArr["ENABLE_OTP_DELIVERALL"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_OTP_DELIVERALL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($OTP_VERIFICATION) == "YES" && ($eCubeX == "Yes" || $eCubejekX == "Yes" || $eRideX == "Yes" || $eRideDeliveryX == "Yes" || $eKingX == "Yes" || $ePlusXTaxi == "Yes" || $ePlusXCubeX == "Yes" || $eKingXPro == "Yes" || $eProRideParcel == "Yes" || $eProCubeX == "Yes" || $eProTaxi == "Yes" || $eProKingX == "Yes" || $eProMedicalAllinone == "Yes" || $eProCubeXGC == "Yes")) {
            $cubejekXConfigArr["ENABLE_OTP_RIDE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_OTP_RIDE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($OTP_VERIFICATION) == "YES" && ($eCubeX == "Yes" || $eCubejekX == "Yes" || $eDeliveryX == "Yes" || $eRideDeliveryX == "Yes" || $eDeliverykingx == "Yes" || $eKingX == "Yes" || $ePlusXDeliveryKing == "Yes" || $ePlusXCubeX == "Yes" || $ePlusXDelivery == "Yes" || $eKingXPro == "Yes" || $eProRideParcel == "Yes" || $eProCubeX == "Yes" || $eProDelivery == "Yes" || $eProDeliveryKing == "Yes" || $eProKingX == "Yes" || $eProCubeXGC == "Yes")) {
            $cubejekXConfigArr["ENABLE_OTP_DELIVERY"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_OTP_DELIVERY"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($OTP_VERIFICATION) == "YES" && ($eCubejekX == "Yes" || $eServiceX == "Yes" || $eKingX == "Yes" || $eSuperXSP == "Yes" || $eKingXPro == "Yes" || $eProKingX == "Yes" || $eProMedicalAllinone == "Yes")) {
            $cubejekXConfigArr["ENABLE_OTP_UFX"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_OTP_UFX"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($MLM_FEATURE) == "YES") {
            $cubejekXConfigArr["MULTI_LEVEL_REFERRAL_SCHEME_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["REFERRAL_AMOUNT_EARN_STRATEGY"] = array("eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["REFERRAL_LEVEL"] = array("vValue" => "15", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["MULTI_LEVEL_REFERRAL_SCHEME_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["REFERRAL_AMOUNT_EARN_STRATEGY"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["REFERRAL_LEVEL"] = array("vValue" => "15", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($REASSIGN_DRIVER_FEATURE) == "YES"){
            $cubejekXConfigArr["REASSIGN_DRIVER_AFTER_ACCEPTING_REQUEST"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["REASSIGN_DRIVER_AFTER_ACCEPTING_REQUEST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($MULTIPLE_OPTIONS_TOPPINGS_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_MULTI_OPTIONS_ADDONS"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_MULTI_OPTIONS_ADDONS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($STORE_MULTIPLE_CATEGORIES_FEATURE) == "YES" && scount($db_service_categories) > 1){
            $cubejekXConfigArr["ENABLE_STORE_MULTI_SERVICE_CATEGORIES"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_STORE_MULTI_SERVICE_CATEGORIES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($FREE_DELIVERY_PROMO_CODE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_FREE_DELIVERY_OR_STORE_SPECIFIC_PROMO_CODE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_FREE_DELIVERY_OR_STORE_SPECIFIC_PROMO_CODE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($FOOD_RATING_DETAIL_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_FOOD_RATING_DETAIL_FLOW"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_FOOD_RATING_DETAIL_FLOW"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($SKU_ITEMS) == "YES"){
            $cubejekXConfigArr["ENABLE_REQUIRE_MENU_ITEM_SKU"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_REQUIRE_MENU_ITEM_SKU"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($RESTRICT_DRIVER_FRAUD_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_DRIVER_ARRIVAL_DISTANCE_TO_USER_PICKUP_ADDRESS"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["DRIVER_ARRIVAL_DISTANCE_TO_USER_PICKUP_ADDRESS"] = array("vValue" => "500", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_DRIVER_ARRIVAL_DISTANCE_TO_USER_PICKUP_ADDRESS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["DRIVER_ARRIVAL_DISTANCE_TO_USER_PICKUP_ADDRESS"] = array("vValue" => "500", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($LOCATION_WISE_PROMO_CODE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_LOCATION_WISE_PROMO_CODE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_LOCATION_WISE_PROMO_CODE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($LOCATION_WISE_BANNER) == "YES"){
            $cubejekXConfigArr["ENABLE_LOCATION_WISE_BANNER"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_LOCATION_WISE_BANNER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($LOCATION_WISE_PUSH_NOTIFICATION) == "YES"){
            $cubejekXConfigArr["ENABLE_COUNTRY_WISE_PUSH_NOTIFICATION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_COUNTRY_WISE_PUSH_NOTIFICATION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($COOKIE_CONSENT_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_COOKIE_CONSENT"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_COOKIE_CONSENT"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($FIREBASE_VERIFICATION) == "YES"){
            $cubejekXConfigArr["MOBILE_NO_VERIFICATION_METHOD"] = array("vValue" => "Firebase", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["MOBILE_NO_VERIFICATION_METHOD"] = array("vValue" => "Firebase", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($FIREBASE_VERIFICATION) == "YES"){
            $cubejekXConfigArr["ENABLE_SEARCH_UFX_SERVICES"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_SEARCH_UFX_SERVICES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        //Added By HJ On 12-07-2019 For Set New Addon Configuration Based On Selection End

        /* Addon - KingX - Added on 16-05-2022  */
        if(strtoupper($SMART_LOGIN_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_SMART_LOGIN"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_SMART_LOGIN"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($DRIVER_REWARD_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_DRIVER_REWARD_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_DRIVER_REWARD_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($VIDEO_CONSULTING_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_VIDEO_CONSULTING_SERVICE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_VIDEO_CONSULTING_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($SERVICE_BID_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_BIDDING_SERVICES"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
            $cubejekXConfigArr["MINIMUM_HOURS_LATER_BIDDING"] = array("vValue" => "1", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MAXIMUM_HOURS_LATER_BIDDING"] = array("vValue" => "672", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["ENABLE_OTP_AFTER_BIDDING"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["BIDDING_LATER_ACCEPT_BEFORE_INTERVAL"] = array("vValue" => "30", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_BIDDING_SERVICES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MINIMUM_HOURS_LATER_BIDDING"] = array("vValue" => "1", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MAXIMUM_HOURS_LATER_BIDDING"] = array("vValue" => "672", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["ENABLE_OTP_AFTER_BIDDING"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["BIDDING_LATER_ACCEPT_BEFORE_INTERVAL"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($BACK_TO_BACK_TRIPS_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_SEND_REQUEST_BEFORE_TRIP_END"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["SEND_REQUEST_BEFORE_TRIP_DISTANCE"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["SEND_REQUEST_BEFORE_TRIP_MINUTES"] = array("vValue" => "10", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_SEND_REQUEST_BEFORE_TRIP_END"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["SEND_REQUEST_BEFORE_TRIP_DISTANCE"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["SEND_REQUEST_BEFORE_TRIP_MINUTES"] = array("vValue" => "10", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($VIDEO_CALL_OPTION_FEATURE) == "YES"){
            $cubejekXConfigArr["RIDE_DRIVER_CALLING_METHOD"] = array("vValue" => "Voip-VideoCall", "eAdminDisplay" => "Yes", "eStatus" => "Active", "tSelectVal" => "Normal,Voip,VideoCall,Voip-VideoCall");
        }else{
            $cubejekXConfigArr["RIDE_DRIVER_CALLING_METHOD"] = array("vValue" => "Voip", "eAdminDisplay" => "Yes", "eStatus" => "Active", "tSelectVal" => "Normal,Voip");
        }
        if(strtoupper($FACEBOOK_PIXEL_SUPPORT_FEATURE) == "YES"){
            $cubejekXConfigArr["FACEBOOK_PIXEL_ID"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["FACEBOOK_PIXEL_ID"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($FACEBOOK_ADS_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_FACEBOOK_ADS"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["FACEBOOK_PLACEMENT_ID"] = array("vValue" => "", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["FACEBOOK_PLACEMENT_ID_IOS"] = array("vValue" => "", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_FACEBOOK_ADS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["FACEBOOK_PLACEMENT_ID"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["FACEBOOK_PLACEMENT_ID_IOS"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($GOOGLE_ADS_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_GOOGLE_ADS"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["GOOGLE_ADMOB_ID"] = array("vValue" => "", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["GOOGLE_ADSENSE_AD_SLOT_ID"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["GOOGLE_ADSENSE_CLIENT_ID"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }else{
            $cubejekXConfigArr["ENABLE_GOOGLE_ADS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["GOOGLE_ADMOB_ID"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["GOOGLE_ADSENSE_AD_SLOT_ID"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["GOOGLE_ADSENSE_CLIENT_ID"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($VIDEO_UPLOAD_SP_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_VIDEO_UPLOAD_SP"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_VIDEO_UPLOAD_SP"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($MULTIPLE_IMAGES_VIDEOS_UPLOAD_DELIVERALL_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_ITEM_MULTIPLE_IMAGE_VIDEO_UPLOAD"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_ITEM_MULTIPLE_IMAGE_VIDEO_UPLOAD"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($ACCOUNT_DELETE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_ACCOUNT_DELETION"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_ACCOUNT_DELETION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        if(strtoupper($APP_HOME_SCREEN_SEARCH) == "YES"){
            $cubejekXConfigArr["ENABLE_APP_HOME_SCREEN_SEARCH"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_APP_HOME_SCREEN_SEARCH"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        /* Addon - KingX - Added on 16-05-2022 END */

        /* Addon - KingXPro - Added on 07-01-2023 */
        if(strtoupper($RENT_ITEM_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_RENT_ITEM_SERVICES"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_RENT_ITEM_SERVICES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($RENT_REALESTATE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_RENT_ESTATE_SERVICES"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_RENT_ESTATE_SERVICES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($RENT_CARS_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_RENT_CARS_SERVICES"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_RENT_CARS_SERVICES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($RENT_ITEM_FEATURE) == "YES" || strtoupper($RENT_REALESTATE_FEATURE) == "YES" || strtoupper($RENT_CARS_FEATURE) == "YES") {
            $cubejekXConfigArr["RENT_ITEM_REJECT_POST_AUTO_EXPIRE_DAYS_LIMIT"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["RENT_ITEM_RADIUS_TO_SHOW"] = array("vValue" => "20", "eAdminDisplay" => "Yes", "eStatus" => "Active", "eConfigRequired" => "Yes");
            $cubejekXConfigArr["ENABLE_BUY_SELL_DETAIL_VIEW_HOME_SCREEN"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        } else {
            $cubejekXConfigArr["RENT_ITEM_REJECT_POST_AUTO_EXPIRE_DAYS_LIMIT"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["RENT_ITEM_RADIUS_TO_SHOW"] = array("vValue" => "20", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["ENABLE_BUY_SELL_DETAIL_VIEW_HOME_SCREEN"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($RIDE_SHARE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_RIDE_SHARE_SERVICE"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
            $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_SOURCE_DISTANCE"] = array("vValue" => "10", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_DEST_DISTANCE"] = array("vValue" => "10", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["RIDE_SHARE_BOOKING_FEE"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["RIDE_SHARE_RECOMNENDED_PRICE"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            if(strtoupper(ONLY_ENABLE_RIDE_SHARING_PRO) == "YES") {
                $cubejekXConfigArr["START_TRIP_BTN_SHOW_BEFORE_SPECIFIC_TIME"] = array("vValue" => "30", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_TAX_FOR_RIDE_SHARE_SERVICE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_DISTANCE"] = array("vValue" => "80", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            } else {
                $cubejekXConfigArr["START_TRIP_BTN_SHOW_BEFORE_SPECIFIC_TIME"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_TAX_FOR_RIDE_SHARE_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_DISTANCE"] = array("vValue" => "80", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
            
        }else{
            $cubejekXConfigArr["ENABLE_RIDE_SHARE_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_SOURCE_DISTANCE"] = array("vValue" => "10", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_DEST_DISTANCE"] = array("vValue" => "10", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["RIDE_SHARE_BOOKING_FEE"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["RIDE_SHARE_RECOMNENDED_PRICE"] = array("vValue" => "2", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["START_TRIP_BTN_SHOW_BEFORE_SPECIFIC_TIME"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["ENABLE_TAX_FOR_RIDE_SHARE_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["LIST_RIDES_LIMIT_BY_DISTANCE"] = array("vValue" => "80", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($TRACK_SERVICE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_TRACK_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_TRACK_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            
        }

        $cubejekXConfigArr["DRIVER_START_DISTANCE_FROM_START_END_LOCATION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");

        if(strtoupper($TRACK_ANY_SERVICE_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_ANY_TRACK_SERVICE"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_ANY_TRACK_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($NEARBY_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_NEARBY_SERVICES"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
            $cubejekXConfigArr["LIST_PLACES_LIMIT_BY_DISTANCE"] = array("vValue" => "80", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_NEARBY_SERVICES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["LIST_PLACES_LIMIT_BY_DISTANCE"] = array("vValue" => "80", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($GIFT_CARD_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_GIFT_CARD_FEATURE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["GIFT_CARD_MAX_AMOUNT"] = array("vValue" => "1000", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_GIFT_CARD_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["GIFT_CARD_MAX_AMOUNT"] = array("vValue" => "1000", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($USER_REWARD_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_RIDER_REWARD_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["USER_REWARD_COINS_FOR_DISTANCE"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["USER_REWARD_AMOUNT_FOR_COINS"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MAXIMUM_USER_REWARD_AMOUNT_PER_TRIP"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MAXIMUM_NO_TRIPS_ALLOWED_USER_REWARD"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        } else{
            $cubejekXConfigArr["ENABLE_RIDER_REWARD_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["USER_REWARD_COINS_FOR_DISTANCE"] = array("vValue" => "2", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["USER_REWARD_AMOUNT_FOR_COINS"] = array("vValue" => "2", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MAXIMUM_USER_REWARD_AMOUNT_PER_TRIP"] = array("vValue" => "2", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MAXIMUM_NO_TRIPS_ALLOWED_USER_REWARD"] = array("vValue" => "2", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($LIVE_ACTIVITY_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_NOTIFICATION_LIVE_ACTIVITY"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["ENABLE_NOTIFICATION_LIVE_ACTIVITY"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($TAXI_BID_FEATURE) == "YES"){
            $cubejekXConfigArr["MINMUM_FARE_AMOUNT_TAXI_BID_TEXT"] = array("vValue" => "10", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["RIDER_REQUEST_ACCEPT_TIME_BID_TAXI"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Active");
            $cubejekXConfigArr["DRIVER_QUOTATION_ACCEPT_TIME_BID_TAXI"] = array("vValue" => "30", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["TAXI_BID_CONTINUOUS_REQUEST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
        }else{
            $cubejekXConfigArr["MINMUM_FARE_AMOUNT_TAXI_BID_TEXT"] = array("vValue" => "10", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["RIDER_REQUEST_ACCEPT_TIME_BID_TAXI"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["DRIVER_QUOTATION_ACCEPT_TIME_BID_TAXI"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["TAXI_BID_CONTINUOUS_REQUEST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        /* Addon - KingXPro - Added on 07-01-2023 END */

        /* Addon - KingXPro - Added on 29-01-2024 */
        if(strtoupper($INTERCITY_RIDES_FEATURE) == "YES"){
            $cubejekXConfigArr["INTERCITY_RADIUS"] = array("vValue" => "50", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MAX_INTERCITY_DAYS"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY"] = array("vValue" => "90", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            $cubejekXConfigArr["INTERCITY_MINIMUM_HOURS_LATER_BOOKING"] = array("vValue" => "2", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        } else{
            $cubejekXConfigArr["INTERCITY_RADIUS"] = array("vValue" => "50", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MAX_INTERCITY_DAYS"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY"] = array("vValue" => "90", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            $cubejekXConfigArr["INTERCITY_MINIMUM_HOURS_LATER_BOOKING"] = array("vValue" => "2", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($PICKUP_AREA_PHOTO_FEATURE) == "YES"){
            $cubejekXConfigArr["ENABLE_PICKUP_AREA_PHOTO_UPLOAD"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        } else{
            $cubejekXConfigArr["ENABLE_PICKUP_AREA_PHOTO_UPLOAD"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }

        if(strtoupper($FARE_TIME_ESTIMATION_FEATURE) == "YES"){
            $cubejekXConfigArr["SHOW_SERVICE_EST"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
        } else{
            $cubejekXConfigArr["SHOW_SERVICE_EST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
        }
        /* Addon - KingXPro - Added on 29-01-2024 END */

        if ($ePackageType == 'standard') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='WAYBILL_ENABLE'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Normal',eAdminDisplay='No',eStatus='Inactive' WHERE vName='RIDE_DRIVER_CALLING_METHOD'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='SINCH_APP_ENVIRONMENT_HOST' || vName='SINCH_APP_SECRET_KEY' || vName='SINCH_APP_KEY')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='WAYBILL_ENABLE'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Voip',eAdminDisplay='Yes',eStatus='Active' WHERE vName='RIDE_DRIVER_CALLING_METHOD'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'sandbox.sinch.com',eAdminDisplay='No',eStatus='Active' WHERE vName='SINCH_APP_ENVIRONMENT_HOST'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Active' WHERE (vName='SINCH_APP_SECRET_KEY' || vName='SINCH_APP_KEY')");
        }
        $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'Method-1',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SYSTEM_PAYMENT_FLOW'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'NonStrict',eAdminDisplay='No',eStatus='Inactive' WHERE vName='APP_DESTINATION_MODE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='PUBNUB_DISABLED' || vName='ENABLE_PUBNUB' || vName='ENABLE_DELIVERY_MODULE')");
        if (strtoupper($eDeliveryType) == 'NONE' || trim($eDeliveryType) == "") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_DELIVERY_MODULE'");
            $obj->sql_query("UPDATE `configurations` SET `vValue`='NONE' WHERE vName = 'APP_DELIVERY_MODE'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE eFor='Delivery' || eFor='Multi-Delivery'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAILGUN_ENABLE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='MAILGUN_USER' || vName='MAILGUN_KEY')");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Provider',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SERVICE_PROVIDER_FLOW'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='CALLMASKING_ENABLED'"); // Added By HJ On 29-07-2019 As Per Discuss With KS Sir
        if ($ePackageType == 'standard' || $ePackageType == 'enterprise') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SHOW_ADVERTISE_AFTER_MINUTES'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '24',eAdminDisplay='No',eStatus='Inactive' WHERE vName='CANCEL_DECLINE_TRIPS_IN_HOURS'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='LINKEDIN_APP_SECRET_KEY' || vName='LINKEDIN_APP_ID')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Disable',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ADVERTISEMENT_TYPE'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION' || vName='ENABLE_NEWS_SECTION' || vName='ENABLE_LIVE_CHAT' || vName='ENABLE_DRIVER_ADVERTISEMENT_BANNER' || vName='ENABLE_RIDER_ADVERTISEMENT_BANNER' || vName='LIVE_CHAT_LICENCE_NUMBER' || vName='PASSENGER_LINKEDIN_LOGIN' || vName='DRIVER_LINKEDIN_LOGIN')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION' || vName='ENABLE_NEWS_SECTION' || vName='ENABLE_LIVE_CHAT' || vName='ENABLE_DRIVER_ADVERTISEMENT_BANNER' || vName='ENABLE_RIDER_ADVERTISEMENT_BANNER')");
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'sandbox.sinch.com',eAdminDisplay='Yes',eStatus='Active' WHERE vName='SINCH_APP_ENVIRONMENT_HOST'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE vName='SHOW_ADVERTISE_AFTER_MINUTES'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '24',eAdminDisplay='Yes',eStatus='Active' WHERE vName='CANCEL_DECLINE_TRIPS_IN_HOURS'");
            //$obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='SINCH_APP_SECRET_KEY' || vName='SINCH_APP_KEY')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Sequential',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ADVERTISEMENT_TYPE'");
        }
        if (DELIVERALL == "Yes" && strtoupper($ePackageType) == 'SHARK') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER')");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_INSURANCE_ACCEPT_REPORT' || vName='ENABLE_INSURANCE_TRIP_REPORT' || vName='ENABLE_INSURANCE_IDLE_REPORT' || vName='WHEEL_CHAIR_ACCESSIBILITY_OPTION' || vName='PASSENGER_TWITTER_LOGIN' || vName='DRIVER_TWITTER_LOGIN' || vName='PASSENGER_LINKEDIN_LOGIN' || vName='DRIVER_LINKEDIN_LOGIN')");
        if (($eProductType == 'Delivery' || $eProductType == 'UberX') || ($ePackageType == 'standard' || $ePackageType == 'enterprise') || ONLYDELIVERALL == "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_INTRANSIT_SHOPPING_SYSTEM' || vName='ENABLE_AIRPORT_SURCHARGE_SECTION' || vName='BOOK_FOR_ELSE_ENABLE' || vName='CHILD_SEAT_ACCESSIBILITY_OPTION' || vName='POOL_ENABLE' || vName='ENABLE_CORPORATE_PROFILE' || vName='FEMALE_RIDE_REQ_ENABLE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='RESTRICTION_KM_NEAREST_DESTINATION_POOL' || vName='RESTRICTION_KM_NEAREST_TAXI_POOL' || vName='BOOK_FOR_ELSE_SHOW_NO_CONTACT')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_INTRANSIT_SHOPPING_SYSTEM' || vName='ENABLE_AIRPORT_SURCHARGE_SECTION' || vName='BOOK_FOR_ELSE_ENABLE' || vName='CHILD_SEAT_ACCESSIBILITY_OPTION' || vName='POOL_ENABLE' || vName='ENABLE_CORPORATE_PROFILE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='RESTRICTION_KM_NEAREST_DESTINATION_POOL' || vName='RESTRICTION_KM_NEAREST_TAXI_POOL' || vName='BOOK_FOR_ELSE_SHOW_NO_CONTACT')");
        }
        if (($eProductType == "Ride-Delivery-UberX" || $eProductType == "UberX") && ONLYDELIVERALL != "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='Yes',eStatus='Active' WHERE vName='GRID_TILES_MAX_COUNT'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '9',eAdminDisplay='No',eStatus='Inactive' WHERE vName='GRID_TILES_MAX_COUNT'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Single',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DELIVERY_MODULE_MODE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE vName='YALGAAR_CLIENT_KEY'");
        if (strtoupper($ePackageType) != 'SHARK') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE vName='EXCHANGE_CURRENCY_RATES_APP_ID'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Inactive' WHERE vName='EXCHANGE_CURRENCY_RATES_APP_ID'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'SocketCluster',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBSUB_TECHNIQUE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '2',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '30-180',eAdminDisplay='No',eStatus='Inactive' WHERE vName='FETCH_TRIP_STATUS_TIME_INTERVAL'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '100',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SITE_POLICE_CONTROL_NUMBER'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='Yes',eStatus='Inactive' WHERE vName='DESTINATION_UPDATE_TIME_INTERVAL'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'sec-c-KoPMtUgEL2QPdViKFr88UiKlOlReQWSyRGE6IJFROvgbLbKY',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_SECRET_KEY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'sub-c-9r3u6k8c-h9kl-66s9-b85h-d8e695euy20k',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_SUBSCRIBE_KEY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'pub-c-49394564-gr96-95g7-8530-96f5f2dv9w53',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_PUBLISH_KEY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'fg5k3i7i7l5ghgk1jcv43w0j41',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_UUID'");
        if ($ePackageType != 'standard' && $eProductType != "UberX" && $eProductType != "Ride" && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAX_ALLOW_NUM_DESTINATION_MULTI'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='No',eStatus='Inactive' WHERE vName='MAX_ALLOW_NUM_DESTINATION_MULTI'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI')");
        }
        if ($kioskPanelEnable == "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = '45',eAdminDisplay='Yes',eStatus='Active' WHERE vName='KIOSK_BOOKING_CONFIRM_TIME_IN_SECONDS'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_KIOSK_DEVICE_ADMINISTRATION')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '45',eAdminDisplay='No',eStatus='Inactive' WHERE vName='KIOSK_BOOKING_CONFIRM_TIME_IN_SECONDS'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_KIOSK_DEVICE_ADMINISTRATION')");
        }
        if (ONLYDELIVERALL == "Yes" || DELIVERALL == "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='COMPANY_PHONE_VERIFICATION' || vName='COMPANY_EMAIL_VERIFICATION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '10',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='MIN_ORDER_CANCELLATION_CHARGES' || vName='ADMIN_COMMISSION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE'");
            //Added By HJ On 04-09-2020 For Set New Deliverall Configuration Start
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_TERMS_SERVICE_CATEGORIES' || vName='ENABLE_PROOF_UPLOAD_SERVICE_CATEGORIES' || vName='ENABLE_TAKE_AWAY' || vName='ENABLE_ADD_PROVIDER_FROM_STORE' || vName='ENABLE_TIP_MODULE_DELIVERALL' || vName='ENABLE_CUSTOM_DELIVERY_CHARGE_ORDER')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Percentage',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '10',eAdminDisplay='Yes',eStatus='Active' WHERE vName='TIP_AMOUNT_1'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '15',eAdminDisplay='Yes',eStatus='Active' WHERE vName='TIP_AMOUNT_2'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '20',eAdminDisplay='Yes',eStatus='Active' WHERE vName='TIP_AMOUNT_3'");
            if(strtoupper($eCubeDoctorX) == "YES") {
                $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_PROOF_UPLOAD_SERVICE_CATEGORIES'");
            }
            //Added By HJ On 04-09-2020 For Set New Deliverall Configuration End
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='COMPANY_PHONE_VERIFICATION' || vName='COMPANY_EMAIL_VERIFICATION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '10',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='MIN_ORDER_CANCELLATION_CHARGES' || vName='ADMIN_COMMISSION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE'");
            //Added By HJ On 04-09-2020 For Set New Deliverall Configuration Start
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_TERMS_SERVICE_CATEGORIES' || vName='ENABLE_PROOF_UPLOAD_SERVICE_CATEGORIES' || vName='ENABLE_TAKE_AWAY' || vName='ENABLE_ADD_PROVIDER_FROM_STORE' || vName='ENABLE_TIP_MODULE_DELIVERALL' || vName='ENABLE_CUSTOM_DELIVERY_CHARGE_ORDER')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Percentage',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '10',eAdminDisplay='No',eStatus='Inactive' WHERE vName='TIP_AMOUNT_1'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '15',eAdminDisplay='No',eStatus='Inactive' WHERE vName='TIP_AMOUNT_2'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '20',eAdminDisplay='No',eStatus='Inactive' WHERE vName='TIP_AMOUNT_3'");
            //Added By HJ On 04-09-2020 For Set New Deliverall Configuration End
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_SOCKET_CLUSTER'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '15',eAdminDisplay='Yes',eStatus='Active' WHERE vName='VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '30',eAdminDisplay='Yes',eStatus='Active' WHERE vName='VERIFICATION_CODE_RESEND_TIME_IN_SECONDS'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='VERIFICATION_CODE_RESEND_COUNT_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_COUNT_RESTRICTION' || vName='VERIFICATION_CODE_RESEND_COUNT')");
        $obj->sql_query("UPDATE configurations SET `vValue` = '125-300',eAdminDisplay='No',eStatus='Inactive' WHERE vName='AIRPORT_TIME_INTERVAL'");
        if ($ePackageType != 'standard' && $eProductType != 'Delivery' && $eProductType != 'UberX' && ONLYDELIVERALL != "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_WAITING_CHARGE_FLAT_TRIP' || vName='ENABLE_WAITING_CHARGE_RENTAL' || vName='ENABLE_SURGE_CHARGE_RENTAL')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_WAITING_CHARGE_FLAT_TRIP' || vName='ENABLE_WAITING_CHARGE_RENTAL' || vName='ENABLE_SURGE_CHARGE_RENTAL')");
        }
        if (($eProductType == "UberX" || $eProductType == "Ride-Delivery-UberX") && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='PROVIDER_AVAIL_LOC_CUSTOMIZE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '30',eAdminDisplay='Yes',eStatus='Active' WHERE vName='BOOKING_LATER_ACCEPT_AFTER_INTERVAL'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '120',eAdminDisplay='Yes',eStatus='Active' WHERE vName='BOOKING_LATER_ACCEPT_BEFORE_INTERVAL'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='PROVIDER_AVAIL_LOC_CUSTOMIZE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '30',eAdminDisplay='No',eStatus='Inactive' WHERE vName='BOOKING_LATER_ACCEPT_AFTER_INTERVAL'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '120',eAdminDisplay='No',eStatus='Inactive' WHERE vName='BOOKING_LATER_ACCEPT_BEFORE_INTERVAL'");
        }
        $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE vName='ALLOW_SERVICE_PROVIDER_AMOUNT'");
        if ($eProductType != 'Delivery' && $eProductType != 'UberX' && ONLYDELIVERALL == "No" && $ePackageType != "standard") { //Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='APPLY_SURGE_ON_FLAT_FARE' || vName='ENABLE_HAIL_RIDES')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE  vName='ENABLE_TOLL_COST'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='APPLY_SURGE_ON_FLAT_FARE' || vName='ENABLE_HAIL_RIDES' || vName='ENABLE_TOLL_COST')");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = '#',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='TOLL_COST_APP_CODE' || vName='TOLL_COST_APP_ID' || vName='TOLL_COST_API_KEY')");
        if ($eProductType == 'UberX' || $eProductType == 'Delivery' || $eProductType == 'Foodonly' || $eProductType == 'Deliverall') { // Done
            $sql8 = $obj->sql_query("UPDATE configurations SET `vValue` = 'No',`eAdminDisplay` = 'No',eStatus='Inactive' WHERE (vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')");
        }
        if ($eProductType == "UberX") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_EDIT_DRIVER_VEHICLE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_EDIT_DRIVER_VEHICLE'");
        }
        if ($eProductType == "UberX" || ONLYDELIVERALL == "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'All',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DRIVER_REQUEST_METHOD'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'All',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_REQUEST_METHOD'");
        }
        if ($eProductType != 'Delivery' && $eProductType != 'UberX' && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='IS_DEST_ANYTIME_CHANGE' || vName='ENABLE_TIP_MODULE')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='IS_DEST_ANYTIME_CHANGE' || vName='ENABLE_TIP_MODULE')");
        }
        if ($eDeliveryType == 'Multi' && $eProductType != 'Ride' && $eProductType != 'UberX' && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Code',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DELIVERY_VERIFICATION_METHOD'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'None',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DELIVERY_VERIFICATION_METHOD'");
        }
        if ($eProductType == "Delivery") {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI')");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_SURGE_CHARGE_RENTAL' || vName='ENABLE_WAITING_CHARGE_RENTAL' || vName='ENABLE_WAITING_CHARGE_FLAT_TRIP' || vName='KIOSK_BOOKING_CONFIRM_TIME_IN_SECONDS' || vName='PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL')");
        }
        if($eDeliveryType == 'Multi'){
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Active' WHERE vName='ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY'");
        }else{
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY'");
        }
        if ($eProductType == "Ride-Delivery-UberX" && strtoupper($ePackageType) != "SHARK") { //Common
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='DELIVERY_VERIFICATION_METHOD' || vName='GRID_TILES_MAX_COUNT' || vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI' || vName='MAX_ALLOW_NUM_DESTINATION_MULTI')");
        }
        if (ONLYDELIVERALL == "Yes") { //Common
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='COMMISION_DEDUCT_ENABLE'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='RIDE_LATER_BOOKING_ENABLED' || vName='DRIVER_REQUEST_METHOD' || vName='VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_COUNT_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY' || vName='WAYBILL_ENABLE' || vName='PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL' || vName='CANCEL_DECLINE_TRIPS_IN_HOURS')");
        }
        //Added By HJ On 14-10-2019 For Remove SP App Files Start
        if (($eProductType == "UberX" || $eProductType == "Ride-Delivery-UberX" || $eProductType == "Ride-Delivery-UberX-Shark") && $eCubeX != "Yes" && $ePlusXCubeX != "Yes" && $eProCubeX != "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_DRIVER_SERVICE_REQUEST_MODULE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_DRIVER_SERVICE_REQUEST_MODULE' || vName='ENABLE_EDIT_DRIVER_SERVICE')");
        }
        //Added By HJ On 14-10-2019 For Remove SP App Files End
        if ($eProductType == "UberX") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='WAYBILL_ENABLE'");
        }
        if ($eProductType == "Foodonly") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_EDIT_DRIVER_SERVICE'");
        }
        // $cubejekXConfigArr= array();
        if (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES" || strtoupper($eRideX) == "YES" || strtoupper($eDeliveryX) == "YES" || strtoupper($eServiceX) == "YES" || strtoupper($eRideDeliveryX) == "YES" || strtoupper($eDeliverykingx) == "YES" || strtoupper($eCubeDoctorX) == "YES" || strtoupper($eKingX) == "YES" || strtoupper($ePlusXTaxi) == "YES" || strtoupper($eSuperXSP) == "YES" || strtoupper($ePlusXDeliveryKing) == "YES" || strtoupper($ePlusXDeliverAll) == "YES" || strtoupper($ePlusXCubeX) == "YES" || strtoupper($ePlusXDelivery) == "YES" || strtoupper($eKingXPro) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProDeliverAll) == "YES" || strtoupper($eProDelivery) == "YES" || strtoupper($eProDeliveryKing) == "YES" || strtoupper($eProCarPool) == "YES" || strtoupper($eProKingX) == "YES" || strtoupper($eProMedicalAllinone) == "YES" || strtoupper($eProCubeXGC) == "YES") {
            $cubejekXConfigArr["CREDIT_TO_WALLET_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            if (strtoupper($Ride) == "NO") {
                $cubejekXConfigArr["ENABLE_INTRANSIT_SHOPPING_SYSTEM"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_AIRPORT_SURCHARGE_SECTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOK_FOR_ELSE_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["CHILD_SEAT_ACCESSIBILITY_OPTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["POOL_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_CORPORATE_PROFILE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["FEMALE_RIDE_REQ_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_FLAT_TRIP"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_RENTAL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_SURGE_CHARGE_RENTAL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_DESTINATION_POOL"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_TAXI_POOL"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOK_FOR_ELSE_SHOW_NO_CONTACT"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["APPLY_SURGE_ON_FLAT_FARE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_HAIL_RIDES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_TOLL_COST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["HANDICAP_ACCESSIBILITY_OPTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["IS_DEST_ANYTIME_CHANGE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_TIP_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_MANUAL_TOLL_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"] = array("vValue" => "Approval", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["ENABLE_INTRANSIT_SHOPPING_SYSTEM"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_AIRPORT_SURCHARGE_SECTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOK_FOR_ELSE_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["CHILD_SEAT_ACCESSIBILITY_OPTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["POOL_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_CORPORATE_PROFILE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["FEMALE_RIDE_REQ_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_FLAT_TRIP"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_RENTAL"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_SURGE_CHARGE_RENTAL"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_DESTINATION_POOL"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_TAXI_POOL"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOK_FOR_ELSE_SHOW_NO_CONTACT"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["APPLY_SURGE_ON_FLAT_FARE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_HAIL_RIDES"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_TOLL_COST"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["HANDICAP_ACCESSIBILITY_OPTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["IS_DEST_ANYTIME_CHANGE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_TIP_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_MANUAL_TOLL_FEATURE"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"] = array("vValue" => "Approval", "eAdminDisplay" => "No", "eStatus" => "Inactive");

                if (strtoupper($eCubeDoctorX) == "YES" || strtoupper($eProMedicalAllinone) == "YES") {
                    $cubejekXConfigArr["ENABLE_INTRANSIT_SHOPPING_SYSTEM"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_AIRPORT_SURCHARGE_SECTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["CHILD_SEAT_ACCESSIBILITY_OPTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["POOL_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_CORPORATE_PROFILE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["FEMALE_RIDE_REQ_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_WAITING_CHARGE_FLAT_TRIP"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_WAITING_CHARGE_RENTAL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_SURGE_CHARGE_RENTAL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RESTRICTION_KM_NEAREST_DESTINATION_POOL"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RESTRICTION_KM_NEAREST_TAXI_POOL"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["APPLY_SURGE_ON_FLAT_FARE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_TOLL_COST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["HANDICAP_ACCESSIBILITY_OPTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_MANUAL_TOLL_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_OTHER_CHARGES_FEATURE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"] = array("vValue" => "Approval", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                }
            }
            if (strtoupper($Fly) == "NO") {
                $cubejekXConfigArr["FLY_RADIUS"] = array("vValue" => "500", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_FLY_VEHICLES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
            if (strtoupper($UberX) == "NO" && strtoupper($Ride) == "NO" && strtoupper($Delivery) == "YES") {
                $cubejekXConfigArr["MAX_ALLOW_NUM_DESTINATION_MULTI"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_ROUTE_CALCULATION_MULTI"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_ROUTE_OPTIMIZE_MULTI"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Delivery) == "NO") {
                $cubejekXConfigArr["MAX_ALLOW_NUM_DESTINATION_MULTI"] = array("vValue" => "1", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_ROUTE_CALCULATION_MULTI"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_ROUTE_OPTIMIZE_MULTI"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["DELIVERY_VERIFICATION_METHOD"] = array("vValue" => "None", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["DELIVERY_VERIFICATION_METHOD"] = array("vValue" => "Code", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($UberX) == "NO") {
                $cubejekXConfigArr["ENABLE_DRIVER_SERVICE_REQUEST_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_EDIT_DRIVER_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("vValue" => "9", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["PROVIDER_AVAIL_LOC_CUSTOMIZE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_AFTER_INTERVAL"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_BEFORE_INTERVAL"] = array("vValue" => "120", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["ENABLE_DRIVER_SERVICE_REQUEST_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("vValue" => "9", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["PROVIDER_AVAIL_LOC_CUSTOMIZE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_AFTER_INTERVAL"] = array("vValue" => "30", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_BEFORE_INTERVAL"] = array("vValue" => "120", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($UberX) == "YES" && strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO") {
                $cubejekXConfigArr["WAYBILL_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["WAYBILL_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Ride) == "YES" || strtoupper($Delivery) == "YES") {
                $cubejekXConfigArr["ENABLE_EDIT_DRIVER_VEHICLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["DRIVER_REQUEST_METHOD"] = array("vValue" => "All", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO") {
                $cubejekXConfigArr["ENABLE_EDIT_DRIVER_VEHICLE"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["DRIVER_REQUEST_METHOD"] = array("vValue" => "All", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
            //Added By HJ On 10-10-2020 For Enable/Disable Genie Feature Based On Selection Start
            if (strtoupper($GENIE_FEATURE_MODULE) == "YES") {
                $cubejekXConfigArr["ENABLE_BUY_ANY_SERVICE_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["DELIVERY_ITEM_LIMIT"] = array("vValue" => "15", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }else{
                $cubejekXConfigArr["ENABLE_BUY_ANY_SERVICE_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["DELIVERY_ITEM_LIMIT"] = array("vValue" => "15", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
            //Added By HJ On 10-10-2020 For Enable/Disable Genie Feature Based On Selection End

            // Added by HV on 21-01-2021 to enable Multi view in single for DeliveryX and DeliveryKing
            if (strtoupper($eDeliveryX) == "YES" || strtoupper($eDeliverykingx) == "YES" || strtoupper($eRideDeliveryX) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProDeliveryKing) == "YES") {
                $cubejekXConfigArr["ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["DELIVERY_LATER_BOOKING_ENABLED"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_ROUTE_OPTIMIZE_MULTI"] = array(
                    "tDescription"  => "Enable route optimization in Parcel Delivery.", 
                    "tHelp"         => "If both the fields \"ENABLE ROUTE CALCULATION IN PARCEL DELIVERY\" & \"ENABLE ROUTE OPTIMIZE IN PARCEL DELIVERY is kept to \"Yes\" then final Google call will be done only once and app will consider it.\n\nIf the \"ENABLE ROUTE CALCULATION DELIVERY\"  is kept to \"No\" then the app will work normally without considering the Google Call.");
                $cubejekXConfigArr["ENABLE_ROUTE_CALCULATION_MULTI"] = array(
                    "vValue"        => "No",
                    "eAdminDisplay" => "No",
                    "eStatus"       => "Inactive",
                    "tDescription"  => "Enable route calculation in Parcel Delivery.", 
                    "tHelp"         => "If set to \"Yes\" the application would avoid google calls while adding destination each time, the app will do manual calculation for destination sequence according direct distance & Route Draw will be skipped.");

                // Added by HV on 10-05-2021 for RideDeliveryX App Main screen design
                if(strtoupper($eRideDeliveryX) == "YES" && $APP_MAIN_SCREEN_DESIGN == "new_design") {
                    $cubejekXConfigArr["ENABLE_RIDE_DELIVERY_NEW_FLOW"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");

                    $obj->sql_query("UPDATE vehicle_category SET `eStatus` = 'Inactive' WHERE iVehicleCategoryId IN (174,176,177,192)");
                    $obj->sql_query("UPDATE vehicle_category SET `iParentId` = '0' WHERE iVehicleCategoryId IN (178,192)");
                    $obj->sql_query("UPDATE vehicle_category SET `iParentId` = '193' WHERE iVehicleCategoryId = 174");
                    $obj->sql_query("UPDATE vehicle_category SET `vCategory_EN` = 'Package', `vLogo` = 'ic_car_EN_1620206398.png', `iDisplayOrder` = 2 WHERE iVehicleCategoryId = '178'");

                    $obj->sql_query("INSERT INTO `vehicle_category` (`iVehicleCategoryId`, `vCategory_EN`, `vCategoryTitle_EN`, `tCategoryDesc_EN`, `iParentId`, `vLogo`, `vLogo1`, `ePriceType`, `eBeforeUpload`, `eAfterUpload`, `iDisplayOrder`, `eStatus`, `eShowType`, `vBannerImage`, `eCatType`, `eSubCatType`, `eFor`, `eDeliveryType`, `iServiceId`, `tBannerButtonText`, `eDetailPageView`, `vHomepageLogo`, `eMaterialCommision`, `fCancellationFare`, `fCommision`, `fWaitingFees`, `iCancellationTimeLimit`, `iMasterVehicleCategoryId`, `iDisplayOrderHomepage`, `lCatDescHomepage`, `vCatDescbtnHomepage`, `vCatNameHomepage`, `vCatSloganHomepage`, `vCatTitleHomepage`, `vHomepageBanner`, `vServiceCatTitleHomepage`, `vServiceHomepageBanner`, `eCatViewType`, `tListDescription`, `vListLogo`, `eOTPCodeEnable`, `iWaitingFeeTimeLimit`) VALUES
                        
                        (193, 'Taxi Ride', '', '', 0, 'ic_car_EN_1620206380.png', 'ic_car_EN_1620206380.png', 'Service', '', '', 1, 'Active', 'Icon', '15529086789688.jpg', 'Ride', '', 'RideCategory', '', 0, '{\"tBannerButtonText_EN\":\"Book Now\"}', '', '', 'No', 0.00, 0.00, 0.00, 0, 0, 0, '', '', '', '', '', '', '', '', '', '{\"tListDescription_EN\":\"\"}', '', 'No', 0),
                        
                        (194, 'Taxi Rental', '', '', 0, 'ic_car_EN_1620206411.png', 'ic_car_EN_1620206411.png', 'Service', '', '', 3, 'Active', 'Icon', '15529086787258.jpg', 'Rental', '', '', '', 0, '{\"tBannerButtonText_EN\":\"Book Now\"}', '', '', 'No', 0.00, 0.00, 0.00, 0, 0, 0, '', '', '', '', '', '', '', '', '', '{\"tListDescription_EN\":\"\"}', '', 'No', 0);");
                }
            }else{
                $cubejekXConfigArr["ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
            }
            // Added by HV on 21-01-2021 to enable Multi view in single for DeliveryX and DeliveryKing End

            // Added by HV on 31-05-2021 For CJX user app new home screen design
            if(strtoupper($eCubejekX) == "YES") {
                if($USER_APP_HOME_SCREEN_DESIGN_CJX == "new_design") {
                    $cubejekXConfigArr["ENABLE_NEW_HOME_SCREEN_LAYOUT_APP"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                    $cubejekXConfigArr["APP_HOME_PAGE_LIST_VIEW_ENABLED"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                    $cubejekXConfigArr["HOME_SCREEN_DESIGN_HEADER_VIEW"] = array("vValue" => "Profile", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                    $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                }
                else {
                    $cubejekXConfigArr["ENABLE_NEW_HOME_SCREEN_LAYOUT_APP"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
                    $cubejekXConfigArr["APP_HOME_PAGE_LIST_VIEW_ENABLED"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
                    $cubejekXConfigArr["HOME_SCREEN_DESIGN_HEADER_VIEW"] = array("vValue" => "Profile", "eAdminDisplay" => "No", "eStatus" => "Active");
                }
            }
            if(strtoupper($kingx_package) == "YES" || strtoupper($kingxpro_package) == "YES") {
                $cubejekXConfigArr["ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_NEW_HOME_SCREEN_LAYOUT_APP"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["APP_HOME_PAGE_LIST_VIEW_ENABLED"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_FLY_VEHICLES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");

                $cubejekXConfigArr["DELIVERY_LATER_BOOKING_ENABLED"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_ROUTE_OPTIMIZE_MULTI"] = array(
                    "tDescription"  => "Enable route optimization in Parcel Delivery.", 
                    "tHelp"         => "If both the fields \"ENABLE ROUTE CALCULATION IN PARCEL DELIVERY\" & \"ENABLE ROUTE OPTIMIZE IN PARCEL DELIVERY is kept to \"Yes\" then final Google call will be done only once and app will consider it.\n\nIf the \"ENABLE ROUTE CALCULATION DELIVERY\"  is kept to \"No\" then the app will work normally without considering the Google Call.");
                $cubejekXConfigArr["ENABLE_ROUTE_CALCULATION_MULTI"] = array(
                    "vValue"        => "No",
                    "eAdminDisplay" => "No",
                    "eStatus"       => "Inactive",
                    "tDescription"  => "Enable route calculation in Parcel Delivery.", 
                    "tHelp"         => "If set to \"Yes\" the application would avoid google calls while adding destination each time, the app will do manual calculation for destination sequence according direct distance & Route Draw will be skipped.");

                if(strtoupper($ePlusXDeliveryKing) == "YES" || strtoupper($eProDeliveryKing) == "YES") {
                    $cubejekXConfigArr["APP_HOME_PAGE_LIST_VIEW_ENABLED"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Active");
                }

                if(strtoupper($eSuperXSP) == "YES") {
                    $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("vValue" => "9", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                }

                if(strtoupper($kingxpro_package) == "YES") {
                    $cubejekXConfigArr["ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                    $cubejekXConfigArr["ENABLE_BUY_SELL_DETAIL_VIEW_HOME_SCREEN"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");

                    $cubejekXConfigArr["ENABLE_SCHEDULED_RIDE_FLOW"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_RIDE_BOOK_TIME_INTERVAL_FOR_RIDER"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_START_TRIP_BEFORE_TRIP_TIME"] = array("vValue" => "120", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_SEND_REQUEST_TOTAL_NUMBER_OF_DRIVER"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_SEND_NOTIFICATION_BEFORE_RIDE_START"] = array("vValue" => "60", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_PROVIDER_ACCEPT_TIME_INTERVAL"] = array("vValue" => "60", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_ON_GOING_TRIP_PROVIDER_RECEIVED_REQUEST_TIME_INTERVAL"] = array("vValue" => "120", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    $cubejekXConfigArr["RIDE_LATER_HIDE_CANCEL_BTN_BEFORE_TRIP_TIME"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");

                    $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (12, 18, 22, 28, 31, 36, 39, 47, 55, 66, 71, 78, 135, 147, 152, 162, 167)");

                    $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('create-travel-preferences', 'delete-travel-preferences', 'create-travel-preferences-category','delete-travel-preferences-category','update-status-travel-preferences-category','update-status-travel-preferences')");

                    if(!(strtoupper($eKingXPro) == "YES" || strtoupper($eKingXProRideSP) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProDeliveryKing) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProKingX) == "YES" || strtoupper($eProCubeXGC) == "YES")) {
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (173)");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('view-master-service-category', 'update-status-master-service-category')");
                    }

                    if(strtoupper($eProKingX) == "YES" || strtoupper($eProMedicalAllinone) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('dashboard-earning-report', 'dashboard-total-ride-jobs', 'dashboard-total-orders', 'dashboard-order-statistics', 'dashboard-latest-order', 'dashboard-server-statistics', 'dashboard-sos-requests', 'dashboard-payment-requests', 'userproviderstore-dashboard', 'tripdetail-earning-dashboard')");
                    }

                    if((strtoupper($eKingXPro) == "NO" && strtoupper($eProKingX) == "NO" && strtoupper($eProMedicalAllinone) == "NO") || strtoupper($eKingXProRideSP) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `eStatus` = 'Deleted' WHERE id IN (16)");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (12, 22) OR iGroupsId IN (16)");
                    }

                    if(strtoupper($eKingXProRideSP) == "YES") {
                        $obj->sql_query("UPDATE homecontent SET `booking_ids` = '174,338'");
                        $obj->sql_query("UPDATE homecontent SET lBookServiceSection = JSON_SET(lBookServiceSection, '$.title_first', 'Driver & Service Providers')");
                        $obj->sql_query("UPDATE homecontent SET lBookServiceSection = JSON_SET(lBookServiceSection, '$.img_alt', 'Driver & Service Providers')");
                        $obj->sql_query("UPDATE homecontent SET lBookServiceSection = JSON_SET(lBookServiceSection, '$.title_sec', 'Users')");
                        $obj->sql_query("UPDATE homecontent SET lBookServiceSection = JSON_SET(lBookServiceSection, '$.img_alt2', 'Users')");
                        $obj->sql_query("UPDATE homecontent SET lBookServiceSection = REPLACE(lBookServiceSection, '\": \"', '\":\"') ");
                        $obj->sql_query("UPDATE homecontent SET lBookServiceSection = REPLACE(lBookServiceSection, '\", \"', '\",\"') ");

                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('view-providers-videoconsult-service-requests', 'update-providers-videoconsult-service-requests')");
                    }
                    
                    if(strtoupper($eProCubeX) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProCubeXGC) == "YES") {
                        if(strtoupper($eProCubeXGC) != "YES") {
                            $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('manage-our-service-menu', 'view-app-home-screen-banner', 'create-app-home-screen-banner', 'edit-app-home-screen-banner', 'delete-app-home-screen-banner', 'update-status-app-home-screen-banner')");
                        }
                        
                        if(strtoupper($eProRideParcel) == "YES") {
                            $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (135) ");
                        }

                        if(strtoupper($eProCubeX) == "YES" || strtoupper($eProCubeXGC) == "YES") {
                            $service_categories_ids_arr_all = [1, 2, 3, 4, 5, 7, 8 , 11];
                            if(strtoupper($eProCubeXGC) == "YES") {
                                $service_categories_ids_arr_all = [1, 2, 5, 8, 10, 12];

                                $cubejekXConfigArr["ANDROID_APP_LINK_DRIVER"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                                $cubejekXConfigArr["IPHONE_APP_LINK_DRIVER"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                                $cubejekXConfigArr["ANDROID_APP_LINK_STORE"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                                $cubejekXConfigArr["IPHONE_APP_LINK_STORE"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                                $cubejekXConfigArr["ANDROID_APP_LINK_KIOSK"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                                $cubejekXConfigArr["ANDROID_APP_LINK_KIOSK_STORE"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                            }
                            foreach ($service_categories_ids_arr_all as $lbl_serviceId) {
                                if(!in_array($lbl_serviceId, $service_categories_ids_arr)) {
                                    $obj->sql_query("DROP TABLE language_label_" . $lbl_serviceId);
                                }
                            }

                            $obj->sql_query("DELETE FROM service_categories WHERE iServiceId NOT IN ($enablesevicescategory)");

                            $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Drivers / Delivery Drivers' WHERE id = 3");
                            $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Drivers / Delivery Drivers' WHERE id = 3");
                            $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Driver Subscription' WHERE id = 134");
                        }
                    }

                    if(strtoupper($eProRideParcel) == "YES" || strtoupper($eProTaxi) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Bookings' WHERE id = 20");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Driver Subscription' WHERE id = 134");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = 'Users/Drivers' WHERE id = 715");

                        if(!$MODULES_OBJ->isEnableTaxiBidFeature('Yes')) {
                            $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (173)");
                            $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('view-master-service-category', 'update-status-master-service-category')");
                        }
                    }

                    if(strtoupper($eProTaxi) == "YES") {
                        
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Ride/Job', 'Ride') ");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Trips/Jobs', 'Trips') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Ride/Job', 'Ride') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Rides/Jobs', 'Rides') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trips/Jobs', 'Trips') ");

                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (167) ");
                    }

                    if(strtoupper($eProDeliverAll) == "YES") {
                        
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Delivery Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Orders' WHERE id = 20");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Delivery Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Driver', 'Delivery Driver') ");

                        $STORE_WORD = "Store";
                        if(scount($service_categories_ids_arr) == 1) {
                            $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (23) ");

                            if($service_categories_ids_arr[0] == 1) {
                                $STORE_WORD = "Restaurant";
                            }
                        }
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (116) ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('manage-passenger-app-launch-info', 'create-passenger-app-launch-info', 'edit-passenger-app-launch-info', 'delete-passenger-app-launch-info', 'update-status-passenger-app-launch-info')");

                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = REPLACE(name, 'Store', '$STORE_WORD') ");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Store', '$STORE_WORD') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Store', '$STORE_WORD') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'store', '$STORE_WORD') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = 'Users/Delivery Drivers/" . $STORE_WORD . "s' WHERE id = 715");
                    }

                    if(strtoupper($eProSP) == "YES") {
                        $obj->sql_query("UPDATE master_service_menu SET `iDisplayOrder` = '1' WHERE eType = 'UberX' AND iParentId = 0;");

                        $DRIVER_WORD = "Service Provider";
                        if($parent_ufx_catid > 0) {
                            $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `eStatus` = 'Deleted' WHERE id IN (10,16)");
                            $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (10,36,83,173) ");
                            $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE id IN (48,58,571,615,671) OR display_sub_group_id IN (36) OR permission_name IN ('dashboard-video-consultation', 'view-vehicle-make', 'view-vehicle-model') ");

                            if($parent_ufx_catid == 1) {
                                $DRIVER_WORD = "Car Washer";
                            } elseif ($parent_ufx_catid == 3) {
                                $DRIVER_WORD = "Beautician";
                            } elseif ($parent_ufx_catid == 5) {
                                $DRIVER_WORD = "Masseur";
                            } elseif ($parent_ufx_catid == 17) {
                                $DRIVER_WORD = "Maid";
                            }
                        }
                        
                        
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = '" . $DRIVER_WORD . "s' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Bookings' WHERE id = 20");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = '" . $DRIVER_WORD . "s' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = '$DRIVER_WORD Subscription' WHERE id = 134");

                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Driver', '$DRIVER_WORD') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Provider', '$DRIVER_WORD') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Rides/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trip', 'Job') ");
                    }

                    if(strtoupper($eProDelivery) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (83,84,135,140) ");

                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('manage-app-home-screen-view', 'edit-app-home-screen-view') ");

                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Delivery Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Bookings' WHERE id = 20");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Delivery Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Delivery Driver Subscription' WHERE id = 134");

                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = 'Users/Delivery Drivers' WHERE id = 715");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Driver', 'Delivery Driver') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Rides/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trip', 'Job') ");
                    }

                    if(strtoupper($eProDeliveryKing) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (83,84) ");

                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('manage-app-home-screen-view', 'edit-app-home-screen-view') ");

                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Delivery Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Delivery Drivers' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Delivery Driver Subscription' WHERE id = 134");

                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Driver', 'Delivery Driver') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Provider', 'Delivery Driver') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Rides/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trip', 'Job') ");
                    }

                    if(strtoupper($eProCarPool) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `eStatus` = 'Deleted' WHERE id IN (3,20,22,23)");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (66,87,88,95,96,99,101,104,107,108,110,116,160,173) ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('export-cancelled-trip-job-report', 'manage-earn-page-content', 'manage-driver-app-launch-info', 'create-driver-app-launch-info', 'edit-driver-app-launch-info', 'delete-driver-app-launch-info', 'update-status-driver-app-launch-info', 'dashboard-sos-requests', 'dashboard-payment-requests', 'dashboard-notifications-alerts-panel', 'tripdetail-earning-dashboard','view-promocode','create-promocode','edit-promocode','delete-promocode','update-status-promocode','export-promocode') ");

                        $obj->sql_query("UPDATE `admin_pro_permissions` SET `status` = 'Deleted' WHERE `display_sub_group_id` IN (87,88,89,90)");

                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = 'Users' WHERE id = 715");

                        $obj->sql_query("DELETE FROM admin_groups WHERE iGroupId IN (2,3)");

                        $obj->sql_query("UPDATE notification_sound SET `eAdminDisplay` = 'No' WHERE eSoundFor ='Provider'");

                        $obj->sql_query("UPDATE `configurations_payment` SET `eAdminDisplay` = 'No' WHERE `vName` IN ('COMMISION_DEDUCT_ENABLE','WALLET_MIN_BALANCE')");


                        $cubejekXConfigArr["DRIVER_EMAIL_VERIFICATION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["DRIVER_PHONE_VERIFICATION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["DRIVER_FACEBOOK_LOGIN"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["DRIVER_GOOGLE_LOGIN"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_APPLE_LOGIN_FOR_PROVIDER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["GOOGLE_SERVER_IOS_DRIVER_APP_KEY"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["GOOGLE_IOS_DRIVER_APP_GEO_KEY"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["COMMISION_DEDUCT_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["WALLET_MIN_BALANCE"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["CREDIT_TO_WALLET_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["RIDE_DRIVER_CALLING_METHOD"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["DRIVER_REQUEST_METHOD"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["LIST_DRIVER_LIMIT_BY_DISTANCE"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["DRIVER_ARRIVED_MIN_TIME_PER_MINUTE"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_DRIVER_ADVERTISEMENT_BANNER"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["DRIVER_SUBSCRIPTION_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_EDIT_DRIVER_PROFILE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_WALLET_WITHDRAWAL_REQUEST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_WALLET_WITHDRAWAL_REQUEST_RESTRICTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["RIDER_REQUEST_ACCEPT_TIME"] = array("eStatus" => "Inactive");
                        $cubejekXConfigArr["IS_SHOW_ROUTE_ON_REQUEST"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_SKIP_RATING_RIDE"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["RATING_DURATION_ON_RECEIPT"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["TIP_DURATION_ON_RECEIPT"] = array("eAdminDisplay" => "No", "eStatus" => "Inactive");
                    }

                    if(strtoupper($eProMedicalAllinone) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `eStatus` = 'Deleted' WHERE id IN (7, 5, 10, 12)");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Deleted' WHERE id IN (5,83,90,160) ");

                        $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Medical Experts' WHERE id = 3");
                        
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Medical Experts' WHERE id = 3");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = 'Medical Expert Subscription' WHERE id = 134");

                        $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('dashboard-delivery-genie-runner', 'manage-organization-payment-report', 'export-organization-payment-report', 'manage-org-cancellation-payment-report', 'manage-earn-page-content') ");

                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Driver', 'Medical Expert') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Provider', 'Medical Expert') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Ride/Job', 'Job') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Rides/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trips/Jobs', 'Jobs') ");
                        $obj->sql_query("UPDATE admin_pro_permissions SET `name` = REPLACE(name, 'Trip', 'Job') ");

                        if(strtoupper(ONLY_ENABLE_DOCTOR_APP) == "YES") {
                            $obj->sql_query("UPDATE admin_pro_permission_display_groups SET `name` = 'Bookings' WHERE id = 20"); 
                            $obj->sql_query("UPDATE admin_pro_permissions SET `status` = 'Deleted' WHERE permission_name IN ('dashboard-video-consultation', 'view-providers-videoconsult-service-requests', 'update-providers-videoconsult-service-requests', 'manage-app-home-screen-view', 'edit-app-home-screen-view') ");   
                        }
                    }

                    if(strtoupper($eProKingX) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProMedicalAllinone) == "YES" || strtoupper($eProSP) == "YES" || strtoupper($eProCubeXGC) == "YES") {
                        $cubejekXConfigArr["ENABLE_APP_HOME_SCREEN_24"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                        $cubejekXConfigArr["ENABLE_SAFETY_TOOLS"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                        $cubejekXConfigArr["ENABLE_HOMESCREEN_REWARDS_BUTTON"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Active");
                    } else {
                        $cubejekXConfigArr["ENABLE_APP_HOME_SCREEN_24"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_SAFETY_TOOLS"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ENABLE_HOMESCREEN_REWARDS_BUTTON"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    }

                    if(strtoupper($eKingXPro) == "YES" || strtoupper($eProCubeX) == "YES" || strtoupper($eProKingX) == "YES") {
                        $cubejekXConfigArr["IPHONE_APP_LINK_EARN_PAGE"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                        $cubejekXConfigArr["ANDROID_APP_LINK_EARN_PAGE"] = array("vValue" => "#", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                    } else {
                        $cubejekXConfigArr["IPHONE_APP_LINK_EARN_PAGE"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                        $cubejekXConfigArr["ANDROID_APP_LINK_EARN_PAGE"] = array("vValue" => "", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                    }

                    if(strtoupper(IS_DELIVERYKING_APP) == "YES") {
                        $obj->sql_query("UPDATE admin_pro_permission_display_sub_groups SET `eStatus` = 'Active' WHERE id IN (135)");
                    }
                }
                
            }
            // Added by HV on 31-05-2021 For CJX user app new home screen design end
            if (strtoupper($eRideX) == "YES" || strtoupper($eDeliveryX) == "YES" || strtoupper($eRideDeliveryX) == "YES" || strtoupper($ePlusXTaxi) == "YES" || strtoupper($ePlusXDelivery) == "YES" || strtoupper($eProRideParcel) == "YES" || strtoupper($eProTaxi) == "YES" || strtoupper($eProDelivery) == "YES" || strtoupper($eProDeliveryKing) == "YES") {
                $cubejekXConfigArr["PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL"] = array("eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            else {
                $cubejekXConfigArr["PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL"] = array("eAdminDisplay" => "No", "eStatus" => "Active");   
            }    
        }

        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_REVERSE_FORMATTING')");
        
        foreach($cubejekXConfigArr as $vname=>$values){
            $tableName = "configurations";
            if($vname == "CREDIT_TO_WALLET_ENABLE"){
                $tableName = "configurations_payment";
            }
            $vValue = $values['vValue'];
            $eAdminDisplay = $values['eAdminDisplay'];
            $eStatus = $values['eStatus'];

            if($vname == "ENABLE_ROUTE_OPTIMIZE_MULTI" || $vname == "ENABLE_ROUTE_OPTIMIZE_MULTI") {
                $tDescription = $values['tDescription'];
                $tHelp = $values['tHelp'];
                $obj->sql_query("UPDATE ".$tableName." SET `tDescription` = '".$tDescription."',tHelp='".$tHelp."' WHERE vName='".$vname."'");
            }
            else {
                $obj->sql_query("UPDATE ".$tableName." SET `vValue` = '".$vValue."',eAdminDisplay='".$eAdminDisplay."',eStatus='".$eStatus."' WHERE vName='".$vname."'");    
            }
        }
        $obj->sql_query("UPDATE setup_info SET `eConfigurationApplied` = 'Yes'");
    }
    //Added By HJ On 11-03-2019 For Set Default configurations As Per KS Sir End
    //Added By HJ On 20-03-2019 For Solved Bug - 6403 As Per Discuss with BM Mam QA. Start
    if (strtoupper($ePackageType) != "SHARK" || $eProductType == "Delivery" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM vehicle_type WHERE ePoolStatus='Yes'");
    }
    $obj->sql_query("UPDATE `language_label` SET `vValue` = 'Tax' WHERE `vLabel` = 'LBL_TAX1_TXT' || `vLabel` = 'LBL_TAX2_TXT'");

    //Added By HJ On 20-03-2019 For Solved Bug - 6403 As Per Discuss with BM Mam QA. End
    //echo "<pre>";print_r($deleteFileArr);die;
    //Added BY HJ On 11-03-2019 For Auto Remove File Start
    $fileCount = scount($deleteFileArr);
    if (scount($deleteFileArr) > 0) {
        $shFilePath = dirname(__FILE__) . "/setup_info/setup.sh";
        if(file_exists($shFilePath)) {
            $fp = fopen($shFilePath, "w");
            fwrite($fp, "#!/bin/bash");
            fwrite($fp, "\n");
            for ($g = 0; $g < scount($deleteFileArr); $g++) {
                $filename = $deleteFileArr[$g] . "\n";
                //fwrite($fp, "rm -f " . $filename);
                fwrite($fp, "svn delete --force " . $filename);
            }
        }
        
    }
    //Added BY HJ On 11-03-2019 For Auto Remove File End
    //Added BY HJ On 12-03-2019 For Check webimages Folder Permission for Upload Image Start
    $webImagePermission = dirname(__FILE__) . "/webimages";
    if (!is_writable($webImagePermission)) {
        echo "<li>Please Set write permission to folder " . $webImagePermission . "</li>";
        $errorcountsystemvalidation += 1;
    }
    //Added BY HJ On 12-03-2019 For Check webimages Folder Permission for Upload Image Start
    //Added BY HJ On 12-03-2019 For Set Default Configuration in php.ini file Start
    $memory_limit = trim(ini_get('memory_limit'), "M"); // Must Be -1
    if ($memory_limit != "-1") {
        echo "<li>Please Set memory_limit '-1' in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    $upload_max_filesize = getUploadSizeInMB(ini_get('upload_max_filesize')); // Must Be Min 100
    if ($upload_max_filesize < 100) {
        echo "<li>Please Set upload_max_filesize minimum 100 in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    $post_max_size = getUploadSizeInMB(ini_get('post_max_size')); // Must Be Min 100
    if ($post_max_size < 100) {
        echo "<li>Please Set post_max_size minimum 100 in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    $post_max_execution_time = getUploadSizeInMB(ini_get('max_execution_time')); // Must Be 0
    if ($post_max_execution_time != 0) {
        echo "<li>Please Set max_execution_time 0 in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    //Added BY HJ On 12-03-2019 For Set Default Configuration in php.ini file End
    echo '</ol>';
    if ($errorcountsystemvalidation > 0) {
        echo '<ol class="validation">';
        $notUsedFolder = dirname(__FILE__) . "/" . ADMIN_URL_CLIENT . "/NOTUSED";
        $translateFolder = dirname(__FILE__) . "/translate";
        if (file_exists($notUsedFolder) || file_exists($translateFolder)) {
            echo "<li>[Note :Please Remove Below Folder in all APP Type.] <br/> ";
        }
        $number = 0;
        if (file_exists($notUsedFolder)) {
            $number += 1;
            echo $number . ". Remove admin/NOTUSED folder in admin panel. <br/>";
        }
        if (file_exists($translateFolder)) {
            $number += 1;
            echo $number . ". Remove 'translate' folder in root.";
        }
        if ($eProductType != 'Foodonly' && $eProductType != 'Deliverall') {
            if ($ePackageType == 'standard') {
                ?>
                [Note : Please Remove location wise fare menu From admin left menu and geo location files.]
                <?php
            }
        }
        echo "<br>Set Cron File.";
        echo "<li>Set Cron for Later Ride Booking File Name in root folder : cron_schedule_ride_new_parent.php</li>";
        echo "<li>Set Cron for Send Notification File Name in root folder: cron_notification_email_parent.php</li>";
        if ($ePackageType == "shark") {
            echo "<li>Set Cron for Auto Update Currency rate File Name in root folder: cron_update_currency.php</li>";
        }
        echo '</ol>';
    }
}

function getUploadSizeInMB($sSize) {
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix, array('P', 'T', 'G', 'M', 'K'))) {
        return (int) $sSize;
    }
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P':
            $iValue = $iValue * 1024 * 1024 * 1024;
            break;
        case 'T':
            $iValue = $iValue * 1024 * 1024;
            break;
        case 'G':
            $iValue = $iValue * 1024;
            break;
        case 'M':
            $iValue = $iValue;
            break;
        case 'K':
            $iValue = $iValue / 1024;
            break;
    }
    return (int) $iValue;
}
?>
<script>
    var fileCount = '<?= $fileCount; ?>';
    $(document).ready(function () {
        if (fileCount > 0) {
            $("#permissionmsg").show();
        } else {
            $("#permissionmsg").hide();
        }
    });
</script>
