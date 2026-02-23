<?php
header('X-XSS-Protection:0');
include_once('../common.php');

define("CONFIGURATIONS_PAYMENT", "configurations_payment");
define("CONFIGURATIONS", "configurations");
define("NOTIFICATION_SOUND", "notification_sound");

if (!$userObj->hasPermission('manage-general-settings')) {
    $userObj->redirect();
}

$script = $activeTab = 'General';
$msgType = isset($_REQUEST['msgType']) ? $_REQUEST['msgType'] : '';
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$projectname = isset($_REQUEST['projectname']) ? trim($_REQUEST['projectname']) : '';

/* Maps Api Settings */
$tabReq = isset($_REQUEST['tab']) ? trim($_REQUEST['tab']) : '';
if(!empty($tabReq) && $tabReq == "MapsApiSettings") {
    $activeTab = "Maps_Api_Settings";
}
if(isset($_SESSION['success']) && $_SESSION['success'] == "0") {
    $msgType = 0;
    $msg = $_SESSION['var_msg'];
    $activeTab = "Maps_Api_Settings";

    unset($_SESSION['success']);
    unset($_SESSION['var_msg']);
}
/* Maps Api Settings */

if(isset($_SESSION['msgType'])) {
    $msgType = $_SESSION['msgType'];
    $msg = $_SESSION['msg'];

    unset($_SESSION['msgType']);
    unset($_SESSION['msg']);
}

if(isset($_SESSION['activeTab']) && !empty($_SESSION['activeTab'])) {
    $activeTab = $_SESSION['activeTab'];
    unset($_SESSION['activeTab']);
}

if (isset($_POST['submitbutton']) && $_POST['submitbutton'] != "") {
    if (SITE_TYPE == 'Demo') {
        $msgType = 0;
        $msg = $langage_lbl_admin['LBL_EDIT_DELETE_RECORD'];
        header("Location:general.php?msgType=" . $msgType . "&msg=" . $msg);
        exit;
    }

    $activeTab = str_replace(" ", "_", $_REQUEST['frm_type']);
    $configTable = CONFIGURATIONS;
    if ($activeTab == "Payment") {
        $configTable = CONFIGURATIONS_PAYMENT;
    }
    
    foreach ($_REQUEST['Data'] as $key => $value) {
        unset($updateData);
        //Added By HJ On 11-01-2019 For Solved Bug - 6178 As Per Discuss With CD Sir Start
        if ($key == "POOL_ENABLE" && $value == "No") {
            //$obj->sql_query("UPDATE vehicle_type SET eStatus='Inactive' WHERE ePoolStatus='Yes'");
        } else if ($key == "APP_PAYMENT_MODE") {
            // $value = str_replace("Wallet", "Card", $value);
            $value = implode(",", $value);
            if($value == 'Cash'){
                $obj->sql_query("UPDATE " . CONFIGURATIONS . " SET `vValue`='Yes' WHERE vName ='ENABLE_OUTSTANDING_RESTRICTION'");
            }
            
        } else if ($key == "SITE_NAME") {
            //Added BY HJ On 25-06-2019 For Replace Project Name In All Table when Changed From Configuration Screen Start
            if ($projectname != "" && $projectname != $value) {
                searchnReplaceWord($projectname, trim($value));
            }


            //Added BY HJ On 25-06-2019 For Replace Project Name In All Table when Changed From Configuration Screen End
        }else if($key == "ENABLE_MANUAL_TOLL_VERIFICATION_METHOD" && strtoupper($value) == "OTP"){
            $value = "Verification";
        }else if($key == "ENABLE_MANUAL_TOLL_VERIFICATION_METHOD" && $value != "OTP"){
            $value = "Approval";
        }

        if($key == "RESTRICTION_KM_NEAREST_TAXI"){
            $obj->sql_query("UPDATE " . CONFIGURATIONS . " SET `vValue`='" . $value . "' WHERE vName ='LIST_DRIVER_LIMIT_BY_DISTANCE'");
        }

        if($key == "RIDER_REQUEST_ACCEPT_TIME"){
            $obj->sql_query("UPDATE " . CONFIGURATIONS . " SET `vValue`='" . $value . "' WHERE vName ='RIDER_REQUEST_ACCEPT_TIME_BID_TAXI'");
        }
        //Added By HJ On 11-01-2019 For Solved Bug - 6178 As Per Discuss With CD Sir End
        $updateData['vValue'] = trim($value);
        $where = " vName = '" . $key . "' AND eType = '" . $_REQUEST['frm_type'] . "'";
        if($key == "ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"){
            
        }

        /* Maps Api Settings */
        if($key == "GOOGLE_SEVER_GCM_API_KEY") {
            $result = addDefaultApiKeysToMongoDB($value);
            if($result['Action'] == "0") {
                $_SESSION['success'] = "0";
                $_SESSION['var_msg'] = $result['message'];
                header('Location: general.php');
                exit;
            }
        }

        if($key == "ENABLE_OPEN_STREET_MAP_SERVICE") {
            if($value == "Yes") {
                $OpenMapStatus = "Active";
            } else {
                $OpenMapStatus = "Inactive";
            }

            $obj->updateRecordsToMongoDBWithDBName(TSITE_DB, "auth_master_accounts_places", "vServiceName", "OpenMap", ['eStatus' => $OpenMapStatus]);
        }
        /* Maps Api Settings */

        ## Add on For OTP Confirmation Code ##
        if($_REQUEST['frm_type'] == "App Settings"){
            $sql = "SELECT vName,vValue FROM " . CONFIGURATIONS . " WHERE vName IN ('ENABLE_OTP_RIDE', 'ENABLE_OTP_DELIVERY', 'ENABLE_OTP_UFX', 'ENABLE_OTP_DELIVERALL')";
            $data_gen_otp = $obj->MySQLSelect($sql);
            $result = array(); 
            foreach ($data_gen_otp as $dbkey => $dbvalue){
                $result[$dbvalue['vName']] = $dbvalue['vValue'];
            }
            $ENABLE_OTP_RIDE_OLD = $result['ENABLE_OTP_RIDE'];
            $ENABLE_OTP_DELIVERY_OLD = $result['ENABLE_OTP_DELIVERY'];
            $ENABLE_OTP_UFX_OLD = $result['ENABLE_OTP_UFX'];
            $ENABLE_OTP_DELIVERALL_OLD = $result['ENABLE_OTP_DELIVERALL'];    
            $ENABLE_OTP_RIDE_NEW = $_REQUEST['Data']['ENABLE_OTP_RIDE'];
            $ENABLE_OTP_DELIVERY_NEW = $_REQUEST['Data']['ENABLE_OTP_DELIVERY'];
            $ENABLE_OTP_UFX_NEW = $_REQUEST['Data']['ENABLE_OTP_UFX'];
            $ENABLE_OTP_DELIVERALL_NEW = $_REQUEST['Data']['ENABLE_OTP_DELIVERALL'];            
        }
        ## Add on For OTP Confirmation Code ##
        $res = $obj->MySQLQueryPerform($configTable, $updateData, 'update', $where);

        ## Add on For OTP Confirmation Code To Update Ride / Delivery Vehicle Type, Parent UFX Category , Service Category##
        if($ENABLE_OTP_RIDE_OLD != $ENABLE_OTP_RIDE_NEW){
            $Fsql = "UPDATE `vehicle_type` SET `eOTPCodeEnable`='" . $ENABLE_OTP_RIDE_NEW . "' WHERE eType ='Ride'";
            $obj->sql_query($Fsql);
        }
        if($ENABLE_OTP_DELIVERY_OLD != $ENABLE_OTP_DELIVERY_NEW){
            $Fsql = "UPDATE `vehicle_type` SET `eOTPCodeEnable`='" . $ENABLE_OTP_DELIVERY_NEW . "' WHERE eType ='Deliver'";
            $obj->sql_query($Fsql);
        }
        if($ENABLE_OTP_UFX_OLD != $ENABLE_OTP_UFX_NEW){
            $Usql = "UPDATE `vehicle_category` SET `eOTPCodeEnable`='" . $ENABLE_OTP_UFX_NEW . "' WHERE iParentId ='0' AND eCatType = 'ServiceProvider'";
            $obj->sql_query($Usql);
        }
        if($ENABLE_OTP_DELIVERALL_OLD != $ENABLE_OTP_DELIVERALL_NEW){
            if(checkTableExistsDatabase('service_categories')) {
                $Dsql = "UPDATE `service_categories` SET `eOTPCodeEnable`='" . $ENABLE_OTP_DELIVERALL_NEW . "' WHERE 1=1";
                $obj->sql_query($Dsql);    
            }

            $Dsql1 = "UPDATE `vehicle_category` SET `eOTPCodeEnable`='" . $ENABLE_OTP_DELIVERALL_NEW . "' WHERE eCatType IN ('Genie', 'Runner', 'Anywhere')";
            $obj->sql_query($Dsql1);
        }
        ## Add on For OTP Confirmation Code To Update Ride / Delivery Vehicle Type, Parent UFX Category , Service Category##
    }
    
    if ($res) {
        $msgType = 1;
        $msg = $langage_lbl_admin['LBL_Record_Updated_successfully'];

        $_SESSION['msgType'] = "1";
        $_SESSION['msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $msgType = 0;
        $msg = "Error in update configuration";

        $_SESSION['msgType'] = "0";
        $_SESSION['msg'] = "Error in update configuration";
    }

    /* ADDED BY PJ FOR REMOVE CLEAR SERVICE REQUESTS */
    if ($ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes' && $_REQUEST['Data']['ENABLE_DRIVER_SERVICE_REQUEST_MODULE'] == 'No') {
        $qry = "TRUNCATE TABLE driver_service_request";
        $serviceRequests = $obj->sql_query($qry);
    }
    /* END REMOVE CLEAR SERVICE REQUESTS */

    $_SESSION['activeTab'] = $activeTab;

    updateSystemData();
    header('Location: general.php');
    exit;
}

if (isset($_POST['notificationbutton'])) {
    $userFile = isset($_POST['User']) ? $_POST['User'] : '0';
    $storeFile = isset($_POST['Store']) ? $_POST['Store'] : '0';
    $providerFile = isset($_POST['Provider']) ? $_POST['Provider'] : '0';
    $dialFile = isset($_POST['Dial']) ? $_POST['Dial'] : '0';
    $voipFile = isset($_POST['Voip']) ? $_POST['Voip'] : '0';

    $selSql = $soundIds = "";
    if (scount($userFile) > 0) {
        $selSql .= "'User'";
        $soundIds .= "'" . $userFile[0] . "'";
    }
    if (scount($storeFile) > 0) {
        $selSql .= ",'Store'";
        $soundIds .= ",'" . $storeFile[0] . "'";
    }
    if (scount($providerFile) > 0) {
        $selSql .= ",'Provider'";
        $soundIds .= ",'" . $providerFile[0] . "'";
    }
    if (scount($dialFile) > 0) {
        $selSql .= ",'Dial'";
        $soundIds .= ",'" . $dialFile[0] . "'";
    }
    if (scount($voipFile) > 0) {
        $selSql .= ",'Voip'";
        $soundIds .= ",'" . $voipFile[0] . "'";
    }
    if ($selSql != "") {
        $remTrim = trim($selSql, ",");
        $remTrimIds = trim($soundIds, ",");
            //echo "<pre>";print_r($remTrimIds);die;
        $obj->sql_query("UPDATE " . NOTIFICATION_SOUND . " SET eIsSelected='No' WHERE eSoundFor IN ($remTrim)");
        $obj->sql_query("UPDATE " . NOTIFICATION_SOUND . " SET eIsSelected='Yes' WHERE iSoundId IN ($remTrimIds)");
    }

    $_SESSION['msgType'] = "1";
    $_SESSION['msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

    updateSystemData();
    header('Location: general.php');
    exit;
}

$ssql_config = "";

$flymodule = 'No';
if ($MODULES_OBJ->isAirFlightModuleAvailable('', 'Yes')) {
    $flymodule = 'Yes';
}

$uberxService = $MODULES_OBJ->isUberXFeatureAvailable('Yes') ? "Yes" : "No";
$rideEnable = $MODULES_OBJ->isRideFeatureAvailable('Yes') ? "Yes" : "No";
$deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes') ? "Yes" : "No";
$deliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') ? "Yes" : "No";
$biddingEnable = $MODULES_OBJ->isEnableBiddingServices('Yes') ? "Yes" : "No";
$nearbyEnable = $MODULES_OBJ->isEnableNearByService('Yes') ? "Yes" : "No";
$trackServiceEnable = $MODULES_OBJ->isEnableTrackServiceFeature('Yes') ? "Yes" : "No";
$trackAnyServiceEnable = $MODULES_OBJ->isEnableTrackAnyServiceFeature('Yes') ? "Yes" : "No";
$rideShareEnable = $MODULES_OBJ->isEnableRideShareService('Yes') ? "Yes" : "No";
$buySellRent = ($MODULES_OBJ->isEnableRentItemService('Yes') || $MODULES_OBJ->isEnableRentEstateService('Yes') || $MODULES_OBJ->isEnableRentCarsService('Yes')) ? "Yes" : "No";

$sql = "SELECT * FROM " . CONFIGURATIONS . " WHERE eAdminDisplay = 'Yes' " . $ssql_config . " ORDER BY eType, vOrder";
$data_gen = $obj->MySQLSelect($sql);

$sql1 = "SELECT * FROM country WHERE eStatus = 'Active' ";
$country_name = $obj->MySQLSelect($sql1);

foreach ($data_gen as $key => $value) {
    $eForConfig = explode(",", $value['eFor']);
    if(scount($eForConfig) == 1) {
        $eForConfig = $eForConfig[0];
    }

    $eForConfigArr = is_array($eForConfig) ? $eForConfig : [$eForConfig];

    if(($eForConfig == '' || $eForConfig == 'General')
       || ($flymodule == 'Yes' && $eForConfig == "Fly")
       || (ENABLEKIOSKPANEL == 'Yes' && $eForConfig == "Kiosk")
       || (strtoupper($deliverallEnable) == "YES" && $eForConfig == "DeliverAll")
       || (strtoupper($rideEnable) == "YES" && ($eForConfig == "Ride" || in_array("Ride", $eForConfigArr)))
       || (strtoupper($deliveryEnable) == "YES" && ($eForConfig == "Delivery" || $eForConfig == "Multi-Delivery" || in_array("Delivery", $eForConfigArr)))
       || (strtoupper($uberxService) == "YES" && ($eForConfig == "UberX" || in_array("UberX", $eForConfigArr)))
       || (strtoupper($biddingEnable) == "YES" && $eForConfig == "Bidding")
       || (strtoupper($nearbyEnable) == "YES" && $eForConfig == "NearBy")
       || (strtoupper($trackServiceEnable) == "YES" && $eForConfig == "TrackService")
       || (strtoupper($trackAnyServiceEnable) == "YES" && $eForConfig == "TrackAnyService")
       || (strtoupper($rideShareEnable) == "YES" && $eForConfig == "RideShare")
       || (strtoupper($buySellRent) == "YES" && $eForConfig == "BuySellRent")
    ) {
        $db_gen[$value['eType']][$key]['iSettingId'] = $value['iSettingId'];

        $value['tDescription'] = str_replace(["Provider", "Driver", "provider", "driver"], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tDescription']);
        $value['tDescription'] = str_replace($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."/".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tDescription']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : #680
        if (strpos($value['tDescription'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'/'.$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) !== false) {
            $value['tDescription'] = str_replace($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."/".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tDescription']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : #680
        }
        $db_gen[$value['eType']][$key]['tDescription'] = str_replace("User", $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'], $value['tDescription']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : 250 BY HS Mam
        $db_gen[$value['eType']][$key]['vValue'] = $value['vValue'];

        $value['tHelp'] = str_replace(["Provider", "Driver", "provider", "driver"], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tHelp']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : 250 BY HS Mam
    
        if (strpos($value['tHelp'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'/'.$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) !== false) {
            $value['tHelp'] = str_replace($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."/".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tHelp']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : #680
        }
        //Added BY HJ On 25-06-2019 For Replace Project Name In All Table when Changed From Configuration Screen Start
        if($value['vName'] == "ENABLE_MANUAL_TOLL_VERIFICATION_METHOD"){
            $changeWordApproval =$valueReplace= "Approval (via Application)";
            $changeWordVerification = "OTP";
            if($value['vValue'] == "Verification"){
                $valueReplace = "OTP";
            }
            $value['tSelectVal'] = str_replace("Approval",$changeWordApproval,$value['tSelectVal']);
            $value['tSelectVal'] = str_replace("Verification",$changeWordVerification,$value['tSelectVal']);
            $value['vValue'] = str_replace($value['vValue'],$valueReplace,$value['vValue']);
            $db_gen[$value['eType']][$key]['vValue'] = $value['vValue'];
            //echo "<pre>";print_r($value);die;
        }
        //Added BY HJ On 25-06-2019 For Replace Project Name In All Table when Changed From Configuration Screen End
        $db_gen[$value['eType']][$key]['tHelp'] = str_replace("User", $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'], $value['tHelp']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : 250 BY HS Mam
        $db_gen[$value['eType']][$key]['vName'] = $value['vName'];
        $db_gen[$value['eType']][$key]['eInputType'] = $value['eInputType'];
        $db_gen[$value['eType']][$key]['tSelectVal'] = $value['tSelectVal'];
        $db_gen[$value['eType']][$key]['eZeroAllowed'] = $value['eZeroAllowed'];
        $db_gen[$value['eType']][$key]['eDoubleValueAllowed'] = $value['eDoubleValueAllowed'];
        $db_gen[$value['eType']][$key]['eSpaceAllowed'] = $value['eSpaceAllowed'];
        $db_gen[$value['eType']][$key]['eConfigRequired'] = $value['eConfigRequired'];
        $db_gen[$value['eType']][$key]['iMaxVal'] = $value['iMaxVal'];
            //Added By HJ On 03-11-2020 For Mask Sensitive key On SITE_TYPE Demo Start
        if(strtoupper($value['eSensitive']) == "YES"){
                //echo "<pre>";print_r($value);die;
            $db_gen[$value['eType']][$key]['vValue'] = clearName($value['vValue']);
        }
            //Added By HJ On 03-11-2020 For Mask Sensitive key On SITE_TYPE Demo End
    }
}

$getPayDataQuery = "SELECT *, '0' as iMaxVal FROM " . CONFIGURATIONS_PAYMENT . " WHERE eAdminDisplay = 'Yes' ORDER BY eType, vOrder";
$fetchData = $obj->MySQLSelect($getPayDataQuery);

$cardTxt = $cardTxt1 = "Card";
foreach ($fetchData as $payKey => $payValue) {
    $eForConfig = explode(",", $value['eFor']);
    if(scount($eForConfig) == 1) {
        $eForConfig = $eForConfig[0];
    }
    if(($payValue['eFor']=='' || $payValue['eFor']=='General')
       || ($flymodule == 'Yes' && $payValue['eFor'] == "Fly")
       || (ENABLEKIOSKPANEL == 'Yes' && $payValue['eFor'] == "Kiosk")
       || (strtoupper($deliverallEnable) == "YES" && $eForConfig == "DeliverAll")
       || (strtoupper($rideEnable) == "YES" && ($eForConfig == "Ride" || in_array("Ride", $eForConfig)))
       || (strtoupper($deliveryEnable) == "YES" && ($eForConfig == "Delivery" || $eForConfig == "Multi-Delivery" || in_array("Delivery", $eForConfig)))
       || (strtoupper($uberxService) == "YES" && ($eForConfig == "UberX" || in_array("UberX", $eForConfig)))
   ) {
        /*if (isset($payValue['vName']) && $payValue['vName'] == "APP_PAYMENT_MODE" && ($eSystemPayFlow == "Method-2" || $eSystemPayFlow == "Method-3")) {
            $walletTxt = "Wallet";
            $payValue['vValue'] = str_replace($cardTxt1, $walletTxt, $payValue['vValue']);
            $payValue['tSelectVal'] = str_replace($cardTxt1, $walletTxt, $payValue['tSelectVal']);
            $cardTxt = $walletTxt;
        }*/

        if(strtoupper($payValue['eSensitive']) == "YES"){
            $payValue['vValue'] = clearName($payValue['vValue']);
        }
        $db_gen[$payValue['eType']][$payKey] = $payValue;
    }
}

//Added BY HJ On 05-08-2019 For Get Notification Sound Data Start
$soundSql = " AND eSoundFor != 'Store'";
if (strtoupper($deliverallEnable) == "YES") {
    $soundSql = "";
}
$soundData = $obj->MySQLSelect("SELECT * FROM " . NOTIFICATION_SOUND . " WHERE eStatus = 'Active' AND eAdminDisplay='Yes' $soundSql");
$useNotificationFile = $providerNotificationFile = $dialNotificationFile = $storeNotificationFile = "default.mp3";
$mp3Url = $tconfig['tsite_url'];
$mp3path = $tconfig["tpanel_path"] . "webimages/notification_sound/";

$userSoundDataArr = array();
for ($r = 0; $r < scount($soundData); $r++) {
    $vFileName = $soundData[$r]['vFileName'];
    $eSoundFor = $soundData[$r]['eSoundFor'];
    $eDefault = $soundData[$r]['eDefault'];
    $checkFile = $mp3path . strtolower($eSoundFor) . "/" . $vFileName;
    
    if (file_exists($checkFile)) {
        $userSoundDataArr[$eSoundFor][] = $soundData[$r];
    } else if ($eDefault == "Yes") {
        $userSoundDataArr[$eSoundFor][] = $soundData[$r];
    }
}

$APP_PAYMENT_METHOD_DETAILS = array();
$APP_PAYMENT_METHOD_DETAILS_KEYS = array();
$APP_PAYMENT_METHOD_STATUS_ARR = array();
foreach ($db_gen['Payment'] as $PaymentConfigArr) {
    if($PaymentConfigArr['vName'] == "APP_PAYMENT_METHOD") {
        $APP_PAYMENT_METHOD_DETAILS['APP_PAYMENT_METHOD'] = $PaymentConfigArr;
        $app_payment_methods = explode(",", $PaymentConfigArr['tSelectVal']);
        $APP_PAYMENT_METHOD_DETAILS_KEYS[] = $PaymentConfigArr['vName'];
    }

    if(!empty($app_payment_methods)) {
        foreach ($app_payment_methods as $payment_method) {
            if(startsWith($PaymentConfigArr['vName'], strtoupper($payment_method)."_")) {
                $APP_PAYMENT_METHOD_DETAILS[$payment_method][] = $PaymentConfigArr;
                $APP_PAYMENT_METHOD_DETAILS_KEYS[] = $PaymentConfigArr['vName'];
            }

            if($PaymentConfigArr['vName'] == strtoupper($payment_method)."_STATUS") {
                $APP_PAYMENT_METHOD_STATUS_ARR[$PaymentConfigArr['vName']] = $PaymentConfigArr;
            }
        }
    }    
}

$restaurantAdmin = "Store";
if (isset($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'])) {
    $restaurantAdmin = $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];
}

$timezoneOptionArr = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

/* Maps Api Settings */
$GOOGLE_KEYS_WEB = array('GOOGLE_SEVER_GCM_API_KEY', 'GOOGLE_SEVER_API_KEY_WEB', 'ENABLE_OPEN_STREET_MAP_SERVICE', 'GOOGLE_MOBILITY_BILLABLE_EVENT_API_KEY');

$GOOGLE_KEYS_ANDROID['USER'] = array('GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY');
$GOOGLE_KEYS_ANDROID['DRIVER'] = array('GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY');
$GOOGLE_KEYS_ANDROID['STORE'] = array('GOOGLE_SERVER_ANDROID_COMPANY_APP_KEY');

$GOOGLE_KEYS_IOS['USER'] = array('GOOGLE_SERVER_IOS_PASSENGER_APP_KEY', 'GOOGLE_IOS_PASSENGER_APP_GEO_KEY');
$GOOGLE_KEYS_IOS['DRIVER'] = array('GOOGLE_SERVER_IOS_DRIVER_APP_KEY', 'GOOGLE_IOS_DRIVER_APP_GEO_KEY');
$GOOGLE_KEYS_IOS['STORE'] = array('GOOGLE_SERVER_IOS_COMPANY_APP_KEY', 'GOOGLE_IOS_COMPANY_APP_GEO_KEY');

$GOOGLE_KEYS_ARR = array();
$AndroidStoreValues = $IOSStoreValues = '';
foreach ($db_gen['Maps Api Settings'] as $ConfigData) {
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

if(isset($GOOGLE_KEYS_ARR['WEB'])){
    $GOOGLE_KEYS_WEB_VAL = array_column($GOOGLE_KEYS_ARR['WEB'], 'vValue');
    $WebValues = array_filter($GOOGLE_KEYS_WEB_VAL, function ($value) {
        return empty($value);
    });
}

if(isset($GOOGLE_KEYS_ARR['ANDROID']['USER'])){
    $GOOGLE_KEYS_ANDROID_USER_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['USER'], 'vValue');
    $AndroidUserValues = array_filter($GOOGLE_KEYS_ANDROID_USER_VAL, function ($value) {
        return empty($value);
    });
}

if(isset($GOOGLE_KEYS_ARR['ANDROID']['DRIVER'])){
    $GOOGLE_KEYS_ANDROID_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['DRIVER'], 'vValue');
    $AndroidDriverValues = array_filter($GOOGLE_KEYS_ANDROID_DRIVER_VAL, function ($value) {
        return empty($value);
    });
}

if(isset($GOOGLE_KEYS_ARR['ANDROID']['STORE'])){
    $GOOGLE_KEYS_ANDROID_STORE_VAL = array_column($GOOGLE_KEYS_ARR['ANDROID']['STORE'], 'vValue');
    $AndroidStoreValues = array_filter($GOOGLE_KEYS_ANDROID_STORE_VAL, function ($value) {
        return empty($value);
    });
}

if(isset($GOOGLE_KEYS_ARR['IOS']['USER'])){
    $GOOGLE_KEYS_IOS_USER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['USER'], 'vValue');
    $IOSUserValues = array_filter($GOOGLE_KEYS_IOS_USER_VAL, function ($value) {
        return empty($value);
    });
}

if(isset($GOOGLE_KEYS_ARR['IOS']['DRIVER'])){
    $GOOGLE_KEYS_IOS_DRIVER_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['DRIVER'], 'vValue');
    $IOSDriverValues = array_filter($GOOGLE_KEYS_IOS_DRIVER_VAL, function ($value) {
        return empty($value);
    });
}

if(isset($GOOGLE_KEYS_ARR['IOS']['STORE'])){
    $GOOGLE_KEYS_IOS_STORE_VAL = array_column($GOOGLE_KEYS_ARR['IOS']['STORE'], 'vValue');
    $IOSStoreValues = array_filter($GOOGLE_KEYS_IOS_STORE_VAL, function ($value) {
        return empty($value);
    });
}
/* Maps Api Settings */
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
    <!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
        <!--[if !IE]><!--> 
<html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title><?= $SITE_NAME; ?> | Configuration</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
    <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <!-- added by gaurang at 13-08-2020 becoz in some dropdown some injected style is added in chrome for some pc only -->
    <style>
        .panel select.form-control {
            display: block;
            width: 100%;
            height: 36px;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.428571429;
            color: #555555;
            vertical-align: middle;
            background-color: #ffffff;
            background-image: none;
            border: 1px solid #cccccc;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
            transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        }

        .panel .form-control[disabled], .panel .form-control[readonly] {
            background-color: #eeeeee;
        }

        .payment-checkbox {
            display: block;
        }

        .payment-checkbox .checkbox {
            margin: 0 20px 0 0;
            display: inline-block;
        }

        .payment-checkbox input[type="checkbox"] {
            margin-top: 1px;
        }

        .has-switch label {
            margin-bottom: 0
        }

        .pg-table tbody td {
            vertical-align: middle !important;
        }

        .pg-table tbody td .radio {
            display: flex;
            justify-content: center;
        }

        /* Maps Api Settings - Style the tab */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        /* Style the buttons that are used to open the tab content */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
            font-weight: 500;
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #dddddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #cccccc;
        }

        /* Style the tab content */
        .tabcontent {
            display: none;
            padding-top: 15px;
        }

        .display-tab-content {
            display: block;
        }
        /* Maps Api Settings */
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >
    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <?php include_once('header.php'); ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
                <div id="add-hide-show-div">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2> General Settings </h2>
                        </div>

                        <div class="table-list">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">General  Settings</div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <?php if ($msgType == '1') { ?> 
                                                        <div class="alert alert-success alert-dismissable">
                                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button><?= $msg ?>
                                                        </div>
                                                    <?php } elseif ($msgType == '0') {
                                                        ?>
                                                        <div class="alert alert-danger alert-dismissable">
                                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>  <?= $msg ?> 
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <ul class="nav nav-tabs">
                                                <?php
                                                foreach ($db_gen as $key => $value) {
                                                    $newKey = str_replace(" ", "_", $key);
                                                    ?>
                                                    <li class="<?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                        <a data-toggle="tab" href="#<?= $newKey ?>">
                                                            <?php
                                                            if ($key == "Apperance"){
                                                                echo "Appearance";
                                                            }
                                                            else if ($key == "Store Settings") {
                                                                echo $restaurantAdmin." Settings";
                                                            }
                                                            else{
                                                                    echo $key;
                                                                }
                                                            ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                ?>
                                                <li>
                                                    <a data-toggle="tab" href="#soundsetting">
                                                        Notification Sound
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <?php
                                                $paymentEnvMode = "";
                                                foreach ($db_gen as $key => $value) {
                                                    if($key != ""){
                                                        $value = array_values($value);
                                                        $cnt = scount($value);
                                                        $tab1 = ceil(scount($value) / 2);
                                                        $tab2 = $cnt - $tab1;
                                                        $newKey = str_replace(" ", "_", $key);
                                                        if (!in_array($key, ["Payment", "Maps Api Settings"])) {
                                                            ?>
                                                            <div id="<?= $newKey ?>" class="tab-pane <?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                                <form method="POST" action="" name="frm_<?= $key ?>">
                                                                    <input type="hidden" name="frm_type" value="<?= $key ?>">
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <?php
                                                                            $i = 0;
                                                                            $temp = true;
                                                                            foreach ($value as $key1 => $value1) {
                                                                                $i++;
                                                                                if ($tab1 < $i && $temp) {
                                                                                    $temp = false;
                                                                                    ?>
                                                                                </div>
                                                                                <div class="col-lg-6">
                                                                                    <?php
                                                                                }

                                                                                if (isset($value1['vName']) && $value1['vName'] == "SITE_NAME") {
                                                                                    ?>
                                                                                    <input type="hidden" value="<?= $value1['vValue'] ?>" name="projectname">
                                                                                <?php }
                                                                                ?>
                                                                                <div class="form-group">
                                                                                    <?php
                                                                                    if ($value1['vName'] == 'RIDER_EMAIL_VERIFICATION') {
                                                                                        if (ONLYDELIVERALL != "Yes") {
                                                                                            ?>
                                                                                            <label><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                                            <?php
                                                                                        }
                                                                                    } else {
                                                                                        ?>  
                                                                                        <label><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                                    <?php } ?>
                                                                                    <?php if ($value1['eInputType'] == 'Textarea') { ?>
                                                                                        <textarea class="form-control" rows="5" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> ><?= $value1['vValue'] ?></textarea>
                                                                                        <?php
                                                                                    } elseif ($value1['eInputType'] == 'Select') {
                                                                                        $optionArr = explode(',', $value1['tSelectVal']);
                                                                                        if ($value1['vName'] == 'DEFAULT_COUNTRY_CODE_WEB') {
                                                                                            ?>
                                                                                            <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                                            >
                                                                                            <?php
                                                                                            foreach ($country_name as $Value) {
                                                                                                $selected = $value1['vValue'] == $Value['vCountryCode'] ? 'selected' : '';
                                                                                                ?>
                                                                                                <option value="<?= $Value['vCountryCode'] ?>" <?= $selected ?>><?= $Value['vCountry'] . ' (' . $Value['vCountryCode'] . ')'; ?></option>
                                                                                                <?php
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    <?php } else if ($value1['vName'] == 'ENABLE_HAIL_RIDES') { ?>
                                                                                        <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                                        >
                                                                                        <?php
                                                                                        foreach ($optionArr as $oKey => $oValue) {
                                                                                            $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                            ?>
                                                                                            <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                            <?php
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                    <div> [Note: This option will not work if you have selected payment mode "<?= $cardTxt; ?>"] </div>
                                                                                <?php } else if ($value1['vName'] == 'DRIVER_REQUEST_METHOD') {
                                                                                    ?>
                                                                                    <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                                    >
                                                                                    <?php
                                                                                    foreach ($optionArr as $oKey => $oValue) {
                                                                                        $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                        if ($oValue == 'All') {
                                                                                            $oValuenew = $oValue . " (COMPETITIVE ALGORITHM)";
                                                                                        } else if ($oValue == 'Distance') {
                                                                                            $oValuenew = $oValue . " (Nearest 1st Algorithm)";
                                                                                        } else if ($oValue == 'Time') {
                                                                                            $oValuenew = $oValue . " (FIFO Algorithm)";
                                                                                        } else {
                                                                                            $oValuenew = $oValue;
                                                                                        }
                                                                                        ?>
                                                                                        <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValuenew ?></option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                                <?php
                                                                            } else if ($value1['vName'] == 'RIDER_EMAIL_VERIFICATION') {
                                                                                if (ONLYDELIVERALL != "Yes") {
                                                                                    ?>
                                                                                    <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                                    >
                                                                                    <?php
                                                                                    foreach ($optionArr as $oKey => $oValue) {
                                                                                        $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                        ?>
                                                                                        <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                        <?php
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                                <?php
                                                                            }
                                                                        } elseif ($value1['vName'] == 'SYSTEM_TIME_ZONE') { ?>
                                                                            <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                                    >
                                                                                    <?php
                                                                                    foreach ($timezoneOptionArr as $oKey => $oValue) {
                                                                                        $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                        ?>
                                                                                        <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                        <?php
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                        <?php } else {
                                                                            $onChangeEvent = "";
                                                                            if ($value1['vName'] == 'TRIP_TRACKING_METHOD') {
                                                                                $onChangeEvent = 'onchange="showConfimbox(this.value);"';
                                                                            }
                                                                            ?>
                                                                            <select <?= $onChangeEvent; ?> class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                            >
                                                                            <?php
                                                                            foreach ($optionArr as $oKey => $oValue) {
                                                                                $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                ?>
                                                                                <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    <?php } ?>
                                                                    <?php
                                                                } else {
                                                                    if ($value1['eInputType'] == 'Number') {
                                                                        if ($value1['vName'] == 'MAX_NUMBER_STOP_OVER_POINTS') {
                                                                            ?>
                                                                            <input type="number" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control numberfield <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> <?php if ($value1['eZeroAllowed'] == 'Yes') { ?> min="0" <?php } else { ?> min="1" <?php } ?>  <?php if ($value1['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <?php } else { ?> step = 0.01 <?php } ?> >    
                                                                            <?php } else { ?>
                                                                                <input type="number" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control numberfield <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> <?php if ($value1['eZeroAllowed'] == 'Yes') { ?> min="0" <?php } else { ?> min="1" <?php } ?>  <?php if ($value1['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <?php } else { ?> step = 0.01 <?php } ?> <?php if($value1['iMaxVal'] > 0) { ?> max="<?= $value1['iMaxVal'] ?>" <?php } ?> >
                                                                                    <?php
                                                                                }
                                                                            } elseif ($value1['eInputType'] == 'Time') {
                                                                                ?>  
                                                                                <input type="time" name="Data[<?= $value1['vName'] ?>]" class="form-control date" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>>
                                                                            <?php } else { ?>
                                                                                <input type="text" name="Data[<?= $value1['vName'] ?>]" class="form-control date" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> <?php if ($value1['eSpaceAllowed'] == 'No') { ?> onkeyup="nospaces(this)" <?php } ?> >
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="form-group" style="text-align: center;">
                                                                    <input type="submit" name="submitbutton" class="btn btn-primary save-configuration" value="Save Changes">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php } elseif ($key == "Maps Api Settings") { ?>
                                                <div id="<?= $newKey ?>" class="row tab-pane <?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                    <form id="map-setting-account-form" method="POST" action="">
                                                        <input type="hidden" name="frm_type" value="<?= $key ?>">
                                                        <div class="col-lg-12">
                                                            <div class="tab">
                                                                <button type="button" class="tablinks map-setting-web-tab active" onclick="openTabContent(event, 'map-setting-web-content', 'tabcontent-map-setting')">General
                                                                    <?php if(scount($WebValues) > 0) { ?>
                                                                        <span data-toggle="tooltip" title="" data-original-title="Some keys are not added. Please add it."><img style="width: 15px; margin-left: 5px" src="<?= $tconfig['tsite_url'] ?>assets/img/danger-new.svg"></span>
                                                                    <?php } ?>
                                                                </button>
                                                                <button type="button" class="tablinks map-setting-android-tab" onclick="openTabContent(event, 'map-setting-android-content', 'tabcontent-map-setting')">Android App
                                                                    <?php if(scount($AndroidUserValues) > 0 || scount($AndroidDriverValues) > 0 || (scount($AndroidStoreValues) > 0 && $deliverallEnable == "Yes")) { ?>
                                                                        <span data-toggle="tooltip" title="" data-original-title="Some keys are not added. Please add it."><img style="width: 15px; margin-left: 5px" src="<?= $tconfig['tsite_url'] ?>assets/img/danger-new.svg"></span>
                                                                    <?php } ?>
                                                                </button>
                                                                <button type="button" class="tablinks map-setting-ios-tab" onclick="openTabContent(event, 'map-setting-ios-content', 'tabcontent-map-setting')">IOS App
                                                                    <?php if(scount($IOSUserValues) > 0 || scount($IOSDriverValues) > 0 || (scount($IOSStoreValues) > 0 && $deliverallEnable == "Yes")) { ?>
                                                                        <span data-toggle="tooltip" title="" data-original-title="Some keys are not added. Please add it."><img style="width: 15px; margin-left: 5px" src="<?= $tconfig['tsite_url'] ?>assets/img/danger-new.svg"></span>
                                                                    <?php } ?>
                                                                </button>
                                                            </div>

                                                            <div class="tabcontent tabcontent-map-setting display-tab-content" id="map-setting-web-content">
                                                                <?php foreach ($GOOGLE_KEYS_ARR['WEB'] as $GOOGLE_KEY_CONFIG) { ?>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <div class="col-lg-12">
                                                                            <label>
                                                                                <?= $GOOGLE_KEY_CONFIG['tDescription'] ?>
                                                                                <?php if($GOOGLE_KEY_CONFIG['vName'] == "ENABLE_OPEN_STREET_MAP_SERVICE") { ?>
                                                                                <i class="fa fa-question-circle" style="cursor: pointer;" onclick="showOpenStreetMapDetails()"></i>
                                                                                <?php } elseif (!empty($GOOGLE_KEY_CONFIG['tHelp'])) { ?>
                                                                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" data-original-title="<p style='word-wrap: break-word'><?= htmlspecialchars($GOOGLE_KEY_CONFIG['tHelp']) ?></p>"></i>
                                                                                <?php } ?>
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <?php if ($GOOGLE_KEY_CONFIG['eInputType'] == 'Select') { $optionArr = explode(',', $GOOGLE_KEY_CONFIG['tSelectVal']); ?>

                                                                            <select class="form-control" name="Data[<?= $GOOGLE_KEY_CONFIG['vName'] ?>]" <?php if ($GOOGLE_KEY_CONFIG['eConfigRequired'] == 'Yes') { ?> required <?php } ?>
                                                                            >
                                                                                <?php
                                                                                foreach ($optionArr as $oKey => $oValue) {
                                                                                    $selected = $oValue == $GOOGLE_KEY_CONFIG['vValue'] ? 'selected' : '';
                                                                                    ?>
                                                                                    <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                            <?php } else { ?>

                                                                            <input type="text" class="form-control" id="<?= $GOOGLE_KEY_CONFIG['vName'] ?>" name="Data[<?= $GOOGLE_KEY_CONFIG['vName'] ?>]" value="<?= $GOOGLE_KEY_CONFIG['vValue'] ?>" required onkeyup="nospaces(this)">
                                                                            <?php } ?>

                                                                            <?php if($GOOGLE_KEY_CONFIG['vName'] == "GOOGLE_SEVER_GCM_API_KEY") { ?>
                                                                                <label id="vAuthKey-error" style="color:#b94a48; display: none;"></label>
                                                                            <?php } ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php } ?>                                
                                                            </div>
                                                            <div class="tabcontent tabcontent-map-setting" id="map-setting-android-content">
                                                                <?php foreach ($GOOGLE_KEYS_ARR['ANDROID'] as $GOOGLE_KEYS_CONFIG_ANDROID) {
                                                                    foreach ($GOOGLE_KEYS_CONFIG_ANDROID as $GOOGLE_KEY_CONFIG) { ?>
                                                                     
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group row">
                                                                            <div class="col-lg-12">
                                                                                <label>
                                                                                    <?= $GOOGLE_KEY_CONFIG['tDescription'] ?>
                                                                                    <?php if(!empty($GOOGLE_KEY_CONFIG['tHelp'])) { ?>
                                                                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="<?= $GOOGLE_KEY_CONFIG['tHelp'] ?>"></i>
                                                                                    <?php } ?>
                                                                                </label>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <input type="text" class="form-control" id="<?= $GOOGLE_KEY_CONFIG['vName'] ?>" name="Data[<?= $GOOGLE_KEY_CONFIG['vName'] ?>]" value="<?= $GOOGLE_KEY_CONFIG['vValue'] ?>" required onkeyup="nospaces(this)">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php } } ?>
                                                            </div>
                                                            <div class="tabcontent tabcontent-map-setting" id="map-setting-ios-content">
                                                                <?php foreach ($GOOGLE_KEYS_ARR['IOS'] as $GOOGLE_KEYS_CONFIG_IOS) {
                                                                    foreach ($GOOGLE_KEYS_CONFIG_IOS as $GOOGLE_KEY_CONFIG) { ?>
                                                                     
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group row">
                                                                            <div class="col-lg-12">
                                                                                <label>
                                                                                    <?= $GOOGLE_KEY_CONFIG['tDescription'] ?>
                                                                                    <?php if(!empty($GOOGLE_KEY_CONFIG['tHelp'])) { ?>
                                                                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="<?= $GOOGLE_KEY_CONFIG['tHelp'] ?>"></i>
                                                                                    <?php } ?>
                                                                                </label>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <input type="text" class="form-control" id="<?= $GOOGLE_KEY_CONFIG['vName'] ?>" name="Data[<?= $GOOGLE_KEY_CONFIG['vName'] ?>]" value="<?= $GOOGLE_KEY_CONFIG['vValue'] ?>" required onkeyup="nospaces(this)">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php } } ?>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="col-lg-12">
                                                            <div class="form-group" style="text-align: center;">
                                                                <input type="submit" id="submit-map-settings" name="submitbutton" class="btn btn-primary" value="Save Changes">
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php } else { ?>
                                                <div id="<?= $newKey ?>" class="tab-pane <?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                    <form method="POST" action="" name="frm_<?= $key ?>">
                                                        <input type="hidden" name="frm_type" value="<?= $key ?>">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <?php
                                                                $i = 0;
                                                                $temp = true;
                                                                foreach ($value as $key1 => $value1) {
                                                                    $i++;
                                                                    if ($tab1 < $i && $temp) {
                                                                        $temp = false;
                                                                    }
                                                                    ?>
                                                                    <?php if ($value1['vName'] == "APP_PAYMENT_METHOD") { ?>
                                                                        <div id="APP_PAYMENT_METHOD">
                                                                            <div class="form-group">
                                                                                <label><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                            </div>
                                                                            <div class="table-responsive">
                                                                                <table class="table table-striped table-bordered table-hover pg-table">
                                                                                    <tr>
                                                                                        <th>Payment Gateway</th>
                                                                                        <th class="text-center">Details</th>
                                                                                        <?php if(scount($app_payment_methods) > 1) { ?>
                                                                                        <th class="text-center">Default</th>
                                                                                        <th class="text-center">Status</th>
                                                                                        <?php } ?>
                                                                                    </tr>
                                                                                    <tbody>
                                                                                        <?php 
                                                                                        foreach ($APP_PAYMENT_METHOD_DETAILS as $akey => $app_payment_method_details) {
                                                                                            if(!in_array($akey, ["APP_PAYMENT_METHOD"])) { ?>
                                                                                                <tr>
                                                                                                    <td style="vertical-align: top !important;"><?= $akey ?></td>
                                                                                                    <td class="text-center">
                                                                                                        <a class="btn btn-primary" href="javascript:void(0);" data-toggle="modal" data-target="#APP_PAYMENT_METHOD_<?= strtoupper($akey) ?>">Edit Details</a>
                                                                                                    </td>
                                                                                                    <?php if(scount($app_payment_methods) > 1) { ?>
                                                                                                    <td class="text-center">
                                                                                                        <div class="radio">
                                                                                                            <label>
                                                                                                                <input type="radio" name="Data[APP_PAYMENT_METHOD]" value="<?= $akey ?>" <?= (strtoupper($akey) == strtoupper($APP_PAYMENT_METHOD_DETAILS['APP_PAYMENT_METHOD']['vValue'])) ? 'checked' : '' ?> class="payment-method-default">
                                                                                                            </label>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                    <td class="text-center">
                                                                                                        <div class="make-switch" data-on="success" data-off="warning">
                                                                                                            <input type="checkbox" name="Data[<?= strtoupper($akey) ?>_STATUS]" id="<?= strtoupper($akey) ?>_STATUS" <?= $APP_PAYMENT_METHOD_STATUS_ARR[strtoupper($akey).'_STATUS']['vValue'] == "Active" ? 'checked' : '' ?> value="<?= $APP_PAYMENT_METHOD_STATUS_ARR[strtoupper($akey).'_STATUS']['vValue'] ?>" onchange="changeStatus(this)" data-paymentmethod="<?= strtoupper($akey) ?>" class="payment-method-status" />
                                                                                                            <?php if($APP_PAYMENT_METHOD_STATUS_ARR[strtoupper($akey).'_STATUS']['vValue'] == "Inactive") { ?>
                                                                                                                <input type="hidden" name="Data[<?= strtoupper($akey) ?>_STATUS]" id="<?= strtoupper($akey) ?>_STATUS_HIDDEN" value="<?= $APP_PAYMENT_METHOD_STATUS_ARR[strtoupper($akey).'_STATUS']['vValue'] ?>">
                                                                                                            <?php } ?>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                    <?php } ?>
                                                                                                    <div  class="modal fade" id="APP_PAYMENT_METHOD_<?= strtoupper($akey) ?>" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                                                                                                        <div class="modal-dialog modal-lg" >
                                                                                                            <div class="modal-content nimot-class">
                                                                                                                <div class="modal-header">
                                                                                                                    <h4>
                                                                                                                        <?= strtoupper($akey) ?> Details
                                                                                                                        <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                                                    </h4>
                                                                                                                </div>

                                                                                                                <div class="modal-body">
                                                                                                                    <?php foreach ($app_payment_method_details as $bkey => $pay_method) { 
                                                                                                                        if($pay_method['vName'] != strtoupper($akey)."_STATUS") { ?>
                                                                                                                            <div class="form-group">
                                                                                                                                <label class="<?= $pay_method['vName'] ?>"><?= $pay_method['tDescription'] ?><?php if ($pay_method['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($pay_method['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>

                                                                                                                                <?php
                                                                                                                                if ($pay_method['eInputType'] == 'Textarea') {
                                                                                                                                    ?>
                                                                                                                                    <textarea class="form-control" rows="5" name="Data[<?= $pay_method['vName'] ?>]" <?php if ($pay_method['eConfigRequired'] == 'Yes') { ?> required="required" <?php } ?>><?= $pay_method['vValue'] ?></textarea>
                                                                                                                                    <?php
                                                                                                                                } elseif ($pay_method['eInputType'] == 'Select') {
                                                                                                                                    $optionArr = explode(',', $pay_method['tSelectVal']);
                                                                                                                                    ?>
                                                                                                                                    <select class="form-control <?= $pay_method['vName'] ?>" name="Data[<?= $pay_method['vName'] ?>]" id="<?= $pay_method['vName'] ?>" <?php if ($pay_method['eConfigRequired'] == 'Yes') { ?> required <?php } ?>>
                                                                                                                                        <?php
                                                                                                                                        foreach ($optionArr as $oKey => $oValue) {
                                                                                                                                            $selected = $oValue == $pay_method['vValue'] ? 'selected' : '';
                                                                                                                                            ?>
                                                                                                                                            <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                                                                            <?php
                                                                                                                                        }
                                                                                                                                        ?>
                                                                                                                                    </select>
                                                                                                                                <?php } elseif ($pay_method['eInputType'] == 'Number') {
                                                                                                                                    ?>
                                                                                                                                        <input type="number" <?php if ($pay_method['eConfigRequired'] == 'Yes') { ?> required <?php } ?> name="Data[<?= $pay_method['vName'] ?>]"<?php if ($pay_method['eZeroAllowed'] == 'Yes') { ?> min="0" <?php } else { ?> min="1" <?php } ?>  id = "<?= $pay_method['vName'] ?>" class="form-control numberfield <?= $pay_method['vName'] ?>" value="<?= $pay_method['vValue'] ?>" <?php if ($pay_method['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <?php } ?> <?php if($pay_method['iMaxVal'] > 0) { ?> max="<?= $pay_method['iMaxVal'] ?>" <?php } ?>>
                                                                                                                                    <?php } else {
                                                                                                                                        ?>
                                                                                                                                        <input type="text" name="Data[<?= $pay_method['vName'] ?>]" id = "<?= $pay_method['vName'] ?>" class="form-control <?= $pay_method['vName'] ?>" value="<?= $pay_method['vValue'] ?>" <?php if ($pay_method['eConfigRequired'] == 'Yes') { ?> required <?php } ?> <?php if ($pay_method['eSpaceAllowed'] == 'No') { ?>onkeyup="nospaces(this)" <?php } ?> >
                                                                                                                                        <?php
                                                                                                                                    }
                                                                                                                                    ?>
                                                                                                                                </div>
                                                                                                                            <?php } } ?>
                                                                                                                        </div>
                                                                                                                        <div class="modal-footer" style="margin-top: 0">
                                                                                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                                                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                                                                                <button type="button" class="btn btn-info" data-dismiss="modal" ><?= $langage_lbl['LBL_CLOSE_TXT']; ?></button>
                                                                                                                            </div>
                                                                                                                        </div>

                                                                                                                        <div style="clear:both;"></div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </tr>
                                                                                                        <?php
                                                                                                    } 
                                                                                                }  ?>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>

                                                                                <?php continue; } elseif (in_array($value1['vName'], $APP_PAYMENT_METHOD_DETAILS_KEYS)) {
                                                                                    continue;
                                                                                } ?>
                                                                                <div class="form-group PAYMENT_METHOD_CONFIG" data-paymentmethodconfig="<?= $value1['ePaymentMethodConfig'] ?>">
                                                                                    <label class="<?= $value1['vName'] ?>"><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                                    <?php
                                                                                    if ($value1['eInputType'] == 'Textarea') {
                                                                                        ?>
                                                                                        <textarea class="form-control" rows="5" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required="required" <?php } ?>><?= $value1['vValue'] ?></textarea>
                                                                                        <?php
                                                                                    } elseif ($value1['eInputType'] == 'Select') {
                                                                                        $optionArr = explode(',', $value1['tSelectVal']);
                                                                                        $onChangedEvent = "";

                                                                                        if ($value1['vName'] == "SYSTEM_PAYMENT_ENVIRONMENT") {
                                                                                            $onChangedEvent = 'onchange="changePayEnv();"';
                                                                                            $paymentEnvMode = $value1['vValue'];
                                                                                        }
                                                                                        ?>
                                                                                        <select class="form-control <?= $value1['vName'] ?>" name="Data[<?= $value1['vName'] ?>]" id="<?= $value1['vName'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> <?= $onChangedEvent ?>>
                                                                                            <?php
                                                                                            foreach ($optionArr as $oKey => $oValue) {
                                                                                                $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                                ?>
                                                                                                <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                                <?php
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                        <?php
                                                                                    } elseif ($value1['eInputType'] == 'Number') {
                                                                                        ?>
                                                                                        <input type="number" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> name="Data[<?= $value1['vName'] ?>]"<?php if ($value1['eZeroAllowed'] == 'Yes') { ?> min="0" <?php } else { ?> min="1" <?php } ?>  id = "<?= $value1['vName'] ?>" class="form-control numberfield <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <?php } ?> <?php if($value1['iMaxVal'] > 0) { ?> max="<?= $value1['iMaxVal'] ?>" <?php } ?> >
                                                                                        <?php } elseif ($value1['eInputType'] == 'Time') {
                                                                                            ?>
                                                                                            <input type="time" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> >
                                                                                        <?php } elseif ($value1['eInputType'] == 'Checkbox') {
                                                                                            $optionArr = explode(',', $value1['tSelectVal']);
                                                                                            $valueArr = explode(',', $value1['vValue']);
                                                                                            $onChangedEvent = "";
                                                                                            if ($value1['vName'] == "APP_PAYMENT_METHOD") {
                                                                                                $onChangedEvent = 'onchange="managePaymentMethodConfig();"';
                                                                                            }                                                          
                                                                                            ?>
                                                                                            <div class="payment-checkbox">
                                                                                                <?php
                                                                                                foreach ($optionArr as $oKey => $oValue) {
                                                                                                    $checked = in_array($oValue, $valueArr) ? 'checked' : '';
                                                                                                    $input_id = $value1['vName'].'_'.strtoupper($oValue);
                                                                                                    ?>
                                                                                                    <div class="checkbox">
                                                                                                        <label>
                                                                                                            <input type="checkbox" name="Data[<?= $value1['vName'] ?>][]" id="<?= $input_id ?>" value="<?= $oValue ?>" <?= $checked ?> <?= $onChangedEvent ?>> <?= $oValue ?>
                                                                                                        </label>
                                                                                                    </div>
                                                                                                    <?php
                                                                                                } if(!isset($optionArr['Card'])) { ?>
                                                                                                    <label style="display: none;">
                                                                                                        <input type="checkbox" name="Data[APP_PAYMENT_MODE][]" id="APP_PAYMENT_MODE_CARD" value="Card">
                                                                                                    </label>
                                                                                                <?php } ?>
                                                                                            </div>
                                                                                        <?php } else {
                                                                                            ?>
                                                                                            <input type="text" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?> <?php if ($value1['eSpaceAllowed'] == 'No') { ?>onkeyup="nospaces(this)" <?php } ?> >
                                                                                            <?php
                                                                                        }
                                                                                        ?>
                                                                                    </div>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <div class="form-group" style="text-align: center;">
                                                                                    <input type="submit" name="submitbutton" class="btn btn-primary save-configuration" value="Save Changes">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <div id="soundsetting" class="tab-pane">
                                                        <form method="POST" action="" name="frm_soundsetting" novalidate>
                                                            <input type="hidden" name="frm_type" value="soundsetting">
                                                            <?php

                                                            foreach ($userSoundDataArr as $for => $data) {
                                                                $headName = $for . " App";
                                                                $helpTxt = "";
                                                                if ($for == "Dial") {
                                                                    $headName = "New ".$langage_lbl_admin['LBL_TRIP_TXT']." Request (i.e 30 second Dial) Screen";
                                                                    $helpTxt = "Selected notification sound will be played when ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." receives new service request. If you select Phone's default Notification Sound option then it will play your phone's default tone.";
                                                                } else if ($for == "Voip") {
                                                                    $headName = "Voip Calling";
                                                                    $helpTxt = "Selected notification sound will be played when user and ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." receives In App/VOIP based calls as a part of call masking . If you select Phone's default Notification Sound option then it will play your phone's default tone.";
                                                                } else if ($for == "Provider") {
                                                                    $headName = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " App";
                                                                    $helpTxt = "Selected notification sound will be played when ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." receives rest of notifications apart from new ".$langage_lbl_admin['LBL_TRIP_TXT']." notification. If you select Phone's default Notification Sound option then it will play your phone's default tone. ";
                                                                } else if ($for == "Store") {
                                                                    $headName = $restaurantAdmin . " App";
                                                                    $helpTxt = "Selected notification sound will be played when ".$restaurantAdmin." app receives notifications for events like new order request and all other kind of push notifications. If you select Phone's default Notification Sound option then it will play your phone's default tone.";
                                                                } else if ($for == "User") {
                                                                    $helpTxt = "Selected notification sound will be played when user app receives notifications for events like service start, service end and all other kind of push notifications. If you select Phone's default Notification Sound option then it will play your phone's default tone.";
                                                                }
                                                                ?>
                                                                <div class="row">
                                                                    <div class="col-lg-8">
                                                                        <div class="form-group">
                                                                            <h3>Notification Sound For <?= $headName; ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($helpTxt, ENT_QUOTES, 'UTF-8') ?>'></i></h3>
                                                                        </div>
                                                                        <?php
                                                                        for ($s = 0; $s < scount($data); $s++) {
                                                                            $iSoundId = $data[$s]['iSoundId'];
                                                                            $eIsSelected = $data[$s]['eIsSelected'];
                                                                            $eSoundFor = strtolower($data[$s]['eSoundFor']);
                                                                            $vFileName = $data[$s]['vFileName'];
                                                                            $eDefault = $data[$s]['eDefault'];
                                                                            ?>
                                                                            <div class="form-group notificationcls">
                                                                                <input class="mp3checkbox" type="radio" value="<?= $iSoundId; ?>" name="<?= $for; ?>[]" <?php if ($eIsSelected == "Yes") { ?>checked=""<?php } ?>>
                                                                                <input class="form-control mp3text" type="text" disabled="disabled" name="user_mp3" value="<?= $vFileName; ?>">
                                                                                <?php if ($eDefault == "No") { ?>
                                                                                    <audio controls controlslist="noplaybackrate">
                                                                                        <source src="<?= $mp3Url; ?>webimages/notification_sound/<?= $eSoundFor; ?>/<?= $vFileName; ?>" type="audio/mpeg">
                                                                                        </audio>
                                                                                    <?php } ?>
                                                                                </div>
                                                                            <?php } ?>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group" style="text-align: center;">
                                                                            <input type="submit" name="notificationbutton" class="btn btn-primary save-configuration" value="Save Changes">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--TABLE-END-->
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <?php if (SITE_TYPE != 'Demo') { ?>
                                    <div class="admin-notes">
                                        <h4>Notes:</h4>
                                        <ul>
                                            <li>
                                                Please close the application and open it again to see the settings reflected after saving the new setting values above.
                                            </li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <!--END PAGE CONTENT -->
                    </div>
                    <!--END MAIN WRAPPER -->

                    <?php if(isset($_SESSION['gcs_error'])) { ?>
                    <div class="modal fade" tabindex="-1" role="dialog" id="gcs_error_modal" data-keyboard="true" data-backdrop="static" style="font-size: 15px">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content" style="width: 100%">
                                <div class="modal-body">
                                    <p>Something went wrong while clearing the cache.</p>
                                    <?php if(isJsonTextGT($_SESSION['gcs_error'])) { ?>
                                    <pre><?= json_encode(json_decode($_SESSION['gcs_error'], true), JSON_PRETTY_PRINT); ?></pre>
                                    <?php } else { ?>
                                    <pre><?= $_SESSION['gcs_error'] ?></pre>
                                    <?php } ?>
                                    <div class="text-center">
                                        <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" data-dismiss="modal">Close</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php include_once('footer.php'); ?>

                    <script type="text/javascript" src="js/moment.min.js"></script>
                    <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
                    <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
                    <script src="../assets/js/modal_alert.js"></script>
                    <script>                                                                                    
                        $(document).ready(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                            manageWalletPayment();
                            managePaymentMethodConfig();

                            <?php if(isset($_SESSION['gcs_error'])) { ?>
                                $('#gcs_error_modal').modal('show');
                            <?php unset($_SESSION['gcs_error']); } ?>
                        });

                        $("form").submit(function () {
                            $('<input>').attr({
                                type: 'hidden',
                                name: '_csrf_tok',
                                value: SOrToKnDoE()
                            }).appendTo(this);
    //Added By HJ On 11-06-2019 For Reset User Data When Change Payment Environment Mode Start
    var clearUserData = 0;
    if (prevEnvMode != $('#SYSTEM_PAYMENT_ENVIRONMENT').val() && $('#SYSTEM_PAYMENT_ENVIRONMENT').val() != undefined) {
        var clearUserData = 1;
    }
    //Added By HJ On 11-06-2019 For Reset User Data When Change Payment Environment Mode End
    if ((previous != '' && $('#APP_PAYMENT_MODE').val() != 'Cash') || clearUserData == 1) {
        var status = confirm("Please note that changing payment gateway will reset all your <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>'s saved credit <?= $cardTxt; ?> details through last set payment gateway. <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN']; ?> will have to re-enter credit <?= $cardTxt; ?> details for new payment gateway once they make a first transaction.Click OK to continue?");
        if (status == false) {
            return false;
        } else {

            var ajaxData = {
                'URL': "ajax_payment_method.php",
                'AJAX_DATA': {paymentmethod: previous, envmode: clearUserData},
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if (response.action == "1") {
                    return false;
                } else {
                }
            });
        }
    } else {
        return true;
    }
});

//added by SP for maximum transaction limit on 29-07-2019
$("#GOPAY_MAXIMUM_LIMIT_PER_DAY").on('blur', function () {
    if (Number($("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val()) < Number($("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val())) {
        alert("Maximum amount limit per day should be greater than or equal to maximum limit per transaction for money transfer");
        $("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val('');
    }
});

$("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").on('blur', function () {
    if (Number($("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val()) < Number($("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val()) && $("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val() != '') {
        alert("Maximum amount limit per day should be greater than or equal to maximum limit per transaction for money transfer");
        $("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val('');
    }
});
    </script>

    <script type="text/javascript">
        function nospaces(t) {
            if (t.value.match(/\s/g)) {
                alert('Sorry, you are not allowed to enter any spaces');
                t.value = t.value.replace(/\s/g, '');
            }
        }
        function showConfimbox(type) {
            if (type == "Pubnub") {
                alert("This option will increase Pubnub.com usage and so increase overall billing. Are you sure you want to select it..?");
            }
        }
        $('input[type="time"]').datetimepicker({
            format: 'HH:mm',
            ignoreReadonly: true,
            useCurrent: false
        });

        function manageWalletPayment() {
            if($('#APP_PAYMENT_MODE_WALLET').prop('checked') == true || $('#APP_PAYMENT_MODE_CARD').prop('checked') == true) {
                if($('#APP_PAYMENT_MODE_WALLET').prop('checked') == true) {
                    $('#PAYMENT_MODE_RESTRICT_TO_WALLET').closest('.PAYMENT_METHOD_CONFIG').show();
                }
                else {
                    $('#PAYMENT_MODE_RESTRICT_TO_WALLET').closest('.PAYMENT_METHOD_CONFIG').hide();
                }
                $('#EXTRA_MONEY_CASH_OR_OUTSTANDING').closest('.PAYMENT_METHOD_CONFIG').show();
            }
            else {
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET, #EXTRA_MONEY_CASH_OR_OUTSTANDING').closest('.PAYMENT_METHOD_CONFIG').hide();
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET').val("No");
            }

            if(($('#APP_PAYMENT_MODE_WALLET').prop('checked') == false && $('#APP_PAYMENT_MODE_CARD').prop('checked') == false) || ($('#APP_PAYMENT_MODE_WALLET').prop('checked') == undefined && $('#APP_PAYMENT_MODE_CARD').prop('checked') == undefined) || ($('#APP_PAYMENT_MODE_WALLET').prop('checked') == false && $('#APP_PAYMENT_MODE_CARD').prop('checked') == undefined) || ($('#APP_PAYMENT_MODE_WALLET').prop('checked') == undefined && $('#APP_PAYMENT_MODE_CARD').prop('checked') == false)) {
                $('#APP_PAYMENT_METHOD').hide();
                $('.PAYMENT_METHOD_CONFIG').each(function() {
                    if($(this).data('paymentmethodconfig') == "Yes") {
                        $(this).hide();
                    }
                });
            }
            else {
                $('#APP_PAYMENT_METHOD').show();
                managePaymentMethodConfig();
            }

            if($('#APP_PAYMENT_MODE_CASH').prop('checked') == false && $('#APP_PAYMENT_MODE_WALLET').prop('checked') == true) {
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET').val("Yes");
                $('#EXTRA_MONEY_CASH_OR_OUTSTANDING').val("Outstanding");
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET, #EXTRA_MONEY_CASH_OR_OUTSTANDING').attr('disabled', true);
                $('<input>').attr({
                    type: 'hidden',
                    name: 'Data[PAYMENT_MODE_RESTRICT_TO_WALLET]',
                    value: 'Yes'
                }).appendTo('form[name="frm_Payment"]');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'Data[EXTRA_MONEY_CASH_OR_OUTSTANDING]',
                    value: 'Outstanding'
                }).appendTo('form[name="frm_Payment"]');
            }
            else if($('#APP_PAYMENT_MODE_CASH').prop('checked') == false && $('#APP_PAYMENT_MODE_WALLET').prop('checked') == false) {
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET').val("No");
                $('#EXTRA_MONEY_CASH_OR_OUTSTANDING').val("Outstanding");
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET, #EXTRA_MONEY_CASH_OR_OUTSTANDING').attr('disabled', true);
                $('<input>').attr({
                    type: 'hidden',
                    name: 'Data[PAYMENT_MODE_RESTRICT_TO_WALLET]',
                    value: 'No'
                }).appendTo('form[name="frm_Payment"]');

                $('<input>').attr({
                    type: 'hidden',
                    name: 'Data[EXTRA_MONEY_CASH_OR_OUTSTANDING]',
                    value: 'Outstanding'
                }).appendTo('form[name="frm_Payment"]');
            }
            else {
                $('#PAYMENT_MODE_RESTRICT_TO_WALLET, #EXTRA_MONEY_CASH_OR_OUTSTANDING').attr('disabled', false);
                $('input[name="Data[PAYMENT_MODE_RESTRICT_TO_WALLET]"], input[name="Data[EXTRA_MONEY_CASH_OR_OUTSTANDING]"]').remove();
            }
        }

        $('#APP_PAYMENT_MODE_WALLET, #APP_PAYMENT_MODE_CARD, #APP_PAYMENT_MODE_CASH').change(function() {
            manageWalletPayment();
        });

        function managePaymentMethodConfig() {
            $('.PAYMENT_METHOD_CONFIG').each(function() {
                if($(this).data('paymentmethodconfig') == "Yes") {
                    $(this).hide();
                }
            });
        }

        function changeStatus(elem) {
            if($(elem).prop('checked') == true) {
                $(elem).val("Active");
                $('#'+$(elem).data('paymentmethod')+'_STATUS_HIDDEN').remove();
            }
            else {
                var APP_PAYMENT_METHOD = "";
                $('.payment-method-default').each(function() {
                    if($(this).prop('checked') == true) {
                        APP_PAYMENT_METHOD = $(this).val();
                    }
                });
                var payment_method_default_error = 0;
                if($(elem).data('paymentmethod') == APP_PAYMENT_METHOD.toUpperCase()) {
                    $('.payment-method-default').each(function() {
                        if($(this).prop('checked') == true) {
                            alert('Unable to change status. Please select another default payment gateway inorder change the status to "Inactive"');
                            payment_method_default_error = 1;
                        }
                    });
                }

                if(payment_method_default_error == 1) {
                    $(elem).closest('.switch-animate').removeClass('switch-off').addClass('switch-on');
                    $(elem).prop('checked', true);
                }
                else {
                    $(elem).val("Inactive");
                    $('<input>').attr({
                        type: 'hidden',
                        name: $(elem).attr('name'),
                        id: $(elem).data('paymentmethod')+'_STATUS_HIDDEN',
                        value: 'Inactive'
                    }).appendTo('form[name="frm_Payment"]');
                }
            }
        }


        $('.payment-method-default').click(function(e) {
            var elem = $(this);
            var payment_method_status_error = 0;
            $('.payment-method-status').each(function() {
                if($(this).prop('checked') == false && $(elem).val().toUpperCase() == $(this).data('paymentmethod')) {
                    alert('Unable to set default payment gateway. Please enable payment gateway inorder set this as default payment gateway.');
                    payment_method_status_error = 1;
                }
            });

            if(payment_method_status_error == 1) {
                e.stopPropagation();
                return false;
            }
        });

        /* Maps Api Settings */
        function openTabContent(evt, Pagename, tabcontent_hide) {
            // Declare all variables
            var i, tabcontent, tablinks;

            // Get all elements with class="tabcontent" and hide them
            tabcontent = $('.' + tabcontent_hide).hide();

            // Get all elements with class="tablinks" and remove the class "active"
            tablinks = $(evt.currentTarget).closest('.tab').find('.tablinks').removeClass('active');

            // Show the current tab, and add an "active" class to the button that opened the tab
            $('#' + Pagename).show();
            $(evt.currentTarget).addClass('active');
        }

        function showOpenStreetMapDetails() {
            show_alert("OpenStreetMap Service", '<p>OpenStreetMap (OSM - <a href="https://www.openstreetmap.org/" target="_blank">https://www.openstreetmap.org/</a>) provides Free and Open Geographic Database Service.</p><ul style="list-style: disc"><li style="margin-bottom: 10px;">OSM is Free Service. Thus is not always Perfect and won\'t give Precise Results. The Results are Less accurate compared to Paid Google Maps.</li><li style="margin-bottom: 10px;">OSM is deployed to Reduce Usage of Google\'s Paid API Service like Geocoding & Direction. So you save on Google Paid API\'s.</li><li style="margin-bottom: 10px;">The System uses Geocoding & Direction API service from OSM.</li><li style="margin-bottom: 10px;">We have Coded System in a way that 1st OSM Maps API\'s are called. If Perfect Results are not achieved then Google Maps Paid API are called.</li><li style="margin-bottom: 10px;">If you prefer not to use OSM Service at any time, you can Disable it. And just Use Google Maps Paid API\'s.</li></ul>', "Ok", "", "");
        }

        <?php if(!empty($tabReq) && $tabReq == "MapsApiSettings") { ?>
            if (typeof (history.pushState) != "undefined") {
                var obj = { Title: 'Settings', Url: 'general.php' };
                window.history.replaceState(obj, obj.Title, obj.Url);
            }

            <?php if(scount($AndroidUserValues) > 0 || scount($AndroidDriverValues) > 0 || (scount($AndroidStoreValues) > 0 && $deliverallEnable == "Yes")) { ?>
                $('.map-setting-android-tab').trigger('click');
                $('.tablinks').removeClass('active');
                $('.map-setting-android-tab').addClass('active');
            <?php } elseif (scount($IOSUserValues) > 0 || scount($IOSDriverValues) > 0 || (scount($IOSStoreValues) > 0 && $deliverallEnable == "Yes")) { ?>
                $('.map-setting-ios-tab').trigger('click');
                $('.tablinks').removeClass('active');
                $('.map-setting-ios-tab').addClass('active');
            <?php } ?>
        <?php } ?>
        /* Maps Api Settings */
    </script>
</body>
<!-- END BODY-->
</html>
