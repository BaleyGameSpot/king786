<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$org_eType = $eType = isset($_GET['eType']) ? stripslashes($_GET['eType']) : "";

if ($eType == 'Ride') {
    $commonTxt = 'taxi-service';
}
if ($eType == 'Deliver') {
    $commonTxt = 'parcel-delivery';
}
if ($eType == 'Ambulance') {
    $commonTxt = 'medical';
}
if ($eType == 'TaxiBid') {
    $commonTxt = 'taxi-bid-service';
}
if($eType == 'InterCity'){
    $commonTxt = 'intercity-service';
}

$VehicleTypeBaseOnService = getVehicleTypeBaseOnService($org_eType);

$view = "view-vehicle-type-" . $commonTxt;
$create = "create-vehicle-type-" . $commonTxt;
$edit = "edit-vehicle-type-" . $commonTxt;
$delete = "delete-vehicle-type-" . $commonTxt;
$updateStatus = "update-status-vehicle-type-" . $commonTxt;

if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}
$thumb = new thumbnail();
$ENABLE_EDIT_DRIVER_VEHICLE = $CONFIG_OBJ->getConfigurations("configurations", "ENABLE_EDIT_DRIVER_VEHICLE");
$rideTypeEnabled = $deliveryTypeEnabled = $vehicleFareModelStrategy = "No";
$rideModuleAvailable = $MODULES_OBJ->isRideFeatureAvailable();
$deliveryModuleAvailable = $MODULES_OBJ->isDeliveryFeatureAvailable();
$fareModelStrategy = $MODULES_OBJ->isEnableRideFareModelStrategy();
if ($rideModuleAvailable == 1) {
    $rideTypeEnabled = "Yes";
}
if ($deliveryModuleAvailable == 1) {
    $deliveryTypeEnabled = "Yes";
}
if ($fareModelStrategy > 0 && strtoupper($rideTypeEnabled) == "YES") {
    $vehicleFareModelStrategy = "Yes";
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$POOL_ENABLE = $CONFIG_OBJ->getConfigurations("configurations", "POOL_ENABLE");

$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

$sql_location = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sql_location);

$sql = "SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

$enableDeliveryHelper = $MODULES_OBJ->isEnableDeliveryHelper() ? "Yes" : "No";
$enableOTPVerificationRide = $MODULES_OBJ->isEnableOTPVerificationRide() ? "Yes" : "No";
$enableOTPVerificationDelivery = $MODULES_OBJ->isEnableOTPVerificationDelivery() ? "Yes" : "No";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$REQUEST_eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";

$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'vehicle_type';
$script = 'VehicleType';
$queryString = '';
if (in_array($REQUEST_eType, [
        'Ride',
        'Deliver',
        'Ambulance',
        'TaxiBid',
        'InterCity'
    ])
) {
   $queryString = '?eType=' . $REQUEST_eType;
    $script = 'VehicleType_' . $REQUEST_eType;
} 


if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $app_type_service = 'Ride-Delivery';
} else if ($APP_TYPE == 'Delivery') {
    $app_type_service = 'Deliver';
} else {
    $app_type_service = $APP_TYPE;
}
/* to fetch max iDisplayOrder from table for insert */



/*if ($app_type_service == 'Ride-Delivery') {
    $select_order = $obj->MySQLSelect("SELECT max(iDisplayOrder) AS iDisplayOrder FROM vehicle_type where eType ='Ride' OR  eType ='Deliver'");
} else {
    $select_order = $obj->MySQLSelect("SELECT max(iDisplayOrder) AS iDisplayOrder FROM vehicle_type where eType ='" . $app_type_service . "'");
}*/
//$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;

$iDisplayOrder = $VehicleTypeBaseOnService['VEHICLE_TYPE_COUNT'];

if($action == "Edit"){
    $iDisplayOrder_max = $iDisplayOrder;
}else{
    $iDisplayOrder_max = $iDisplayOrder + 1; // Maximum order number
}




$vVehicleType = isset($_POST['vVehicleType']) ? $_POST['vVehicleType'] : '';
$iVehicleCategoryId = isset($_POST['iVehicleCategoryId']) ? $_POST['iVehicleCategoryId'] : '';
$fPricePerKM = isset($_POST['fPricePerKM']) ? $_POST['fPricePerKM'] : '';
$fPricePerMin = isset($_POST['fPricePerMin']) ? $_POST['fPricePerMin'] : '';
$iBaseFare = isset($_POST['iBaseFare']) ? $_POST['iBaseFare'] : '';
$iMinFare = isset($_POST['iMinFare']) ? $_POST['iMinFare'] : '';
$fCommision = isset($_POST['fCommision']) ? $_POST['fCommision'] : '';
$iPersonSize = isset($_POST['iPersonSize']) ? $_POST['iPersonSize'] : '1';
$fBufferAmount = isset($_POST['fBufferAmount']) ? $_POST['fBufferAmount'] : '0';
$fNightPrice = isset($_POST['fNightPrice']) ? $_POST['fNightPrice'] : '';
if ($iPersonSize <= 0) {
    $iPersonSize = 1;
}
$tMonPickStartTime = isset($_POST['tMonPickStartTime']) ? $_POST['tMonPickStartTime'] : '';
$tMonPickEndTime = isset($_POST['tMonPickEndTime']) ? $_POST['tMonPickEndTime'] : '';
$fMonPickUpPrice = isset($_POST['fMonPickUpPrice']) ? $_POST['fMonPickUpPrice'] : '';
$tTuePickStartTime = isset($_POST['tTuePickStartTime']) ? $_POST['tTuePickStartTime'] : '';
$tTuePickEndTime = isset($_POST['tTuePickEndTime']) ? $_POST['tTuePickEndTime'] : '';
$fTuePickUpPrice = isset($_POST['fTuePickUpPrice']) ? $_POST['fTuePickUpPrice'] : '';
$tWedPickStartTime = isset($_POST['tWedPickStartTime']) ? $_POST['tWedPickStartTime'] : '';
$tWedPickEndTime = isset($_POST['tWedPickEndTime']) ? $_POST['tWedPickEndTime'] : '';
$fWedPickUpPrice = isset($_POST['fWedPickUpPrice']) ? $_POST['fWedPickUpPrice'] : '';
$tThuPickStartTime = isset($_POST['tThuPickStartTime']) ? $_POST['tThuPickStartTime'] : '';
$tThuPickEndTime = isset($_POST['tThuPickEndTime']) ? $_POST['tThuPickEndTime'] : '';
$fThuPickUpPrice = isset($_POST['fThuPickUpPrice']) ? $_POST['fThuPickUpPrice'] : '';
$tFriPickStartTime = isset($_POST['tFriPickStartTime']) ? $_POST['tFriPickStartTime'] : '';
$tFriPickEndTime = isset($_POST['tFriPickEndTime']) ? $_POST['tFriPickEndTime'] : '';
$fFriPickUpPrice = isset($_POST['fFriPickUpPrice']) ? $_POST['fFriPickUpPrice'] : '';
$tSatPickStartTime = isset($_POST['tSatPickStartTime']) ? $_POST['tSatPickStartTime'] : '';
$tSatPickEndTime = isset($_POST['tSatPickEndTime']) ? $_POST['tSatPickEndTime'] : '';
$fSatPickUpPrice = isset($_POST['fSatPickUpPrice']) ? $_POST['fSatPickUpPrice'] : '';
$tSunPickStartTime = isset($_POST['tSunPickStartTime']) ? $_POST['tSunPickStartTime'] : '';
$tSunPickEndTime = isset($_POST['tSunPickEndTime']) ? $_POST['tSunPickEndTime'] : '';
$fSunPickUpPrice = isset($_POST['fSunPickUpPrice']) ? $_POST['fSunPickUpPrice'] : '';
//$tPickEndTime = isset($_POST['tPickEndTime']) ? $_POST['tPickEndTime'] : '';
$tNightStartTime = isset($_POST['tNightStartTime']) ? $_POST['tNightStartTime'] : '';
$tNightEndTime = isset($_POST['tNightEndTime']) ? $_POST['tNightEndTime'] : '';
$eStatus_picktime = isset($_POST['ePickStatus']) ? $_POST['ePickStatus'] : 'off';
$ePickStatus = ($eStatus_picktime == 'on') ? 'Active' : 'Inactive';
$eStatus_nighttime = isset($_POST['eNightStatus']) ? $_POST['eNightStatus'] : 'off';
$eNightStatus = ($eStatus_nighttime == 'on') ? 'Active' : 'Inactive';
$ePoolStatus = isset($_POST['ePoolStatus']) ? $_POST['ePoolStatus'] : 'off';
$ePoolStatus = ($ePoolStatus == 'on') ? 'Yes' : 'No';
//echo $ePoolStatus;die;
$eType = isset($_POST['eType']) ? $_POST['eType'] : '';
$fPoolPercentage = isset($_POST['fPoolPercentage']) ? $_POST['fPoolPercentage'] : '';
$eFareType = isset($_POST['eFareType']) ? $_POST['eFareType'] : '';
$fFixedFare = isset($_POST['fFixedFare']) ? $_POST['fFixedFare'] : '';
$eAllowQty = isset($_POST['eAllowQty']) ? $_POST['eAllowQty'] : '';
$iMaxQty = isset($_POST['iMaxQty']) ? $_POST['iMaxQty'] : '';
$fPricePerHour = isset($_POST['fPricePerHour']) ? $_POST['fPricePerHour'] : '';
//$fVisitFee = isset($_POST['fVisitFee']) ? $_POST['fVisitFee'] : '';
$iCancellationTimeLimit = isset($_POST['iCancellationTimeLimit']) ? $_POST['iCancellationTimeLimit'] : '';
$fCancellationFare = isset($_POST['fCancellationFare']) ? $_POST['fCancellationFare'] : '';
$iWaitingFeeTimeLimit = isset($_POST['iWaitingFeeTimeLimit']) ? $_POST['iWaitingFeeTimeLimit'] : 0;
$fWaitingFees = isset($_POST['fWaitingFees']) ? $_POST['fWaitingFees'] : 0;
$fTripHoldFees = isset($_POST['fTripHoldFees']) ? $_POST['fTripHoldFees'] : '';
$eBidStatus = isset($_POST['eBidStatus']) ? $_POST['eBidStatus'] : '';
$eInterCityStatus = isset($_POST['eInterCityStatus']) ? $_POST['eInterCityStatus'] : '';
 
if ($ePoolStatus == "Yes" || $eType != "Ride") {
    $iWaitingFeeTimeLimit == 0;
    $fWaitingFees = $fTripHoldFees = 0;
}
if ($eType == "Deliver") {
    $fWaitingFees = isset($_POST['fWaitingFees']) ? $_POST['fWaitingFees'] : 0;
}
$iCountryId = isset($_POST['iCountryId']) ? $_POST['iCountryId'] : '';
$iStateId = isset($_POST['iStateId']) ? $_POST['iStateId'] : '';
$iCityId = isset($_POST['iCityId']) ? $_POST['iCityId'] : '';
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '';
$eIconType = isset($_POST['eIconType']) ? $_POST['eIconType'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
$eFareCalcModel = isset($_POST['eFareCalcModel']) ? $_POST['eFareCalcModel'] : 'Incremental';
$eDeliveryHelper = isset($_POST['eDeliveryHelper']) ? $_POST['eDeliveryHelper'] : 'No';
$eOTPCodeEnable = isset($_POST['eOTPCodeEnable']) ? $_POST['eOTPCodeEnable'] : 'No';

if($ePoolStatus == "Yes"){
    $eFareCalcModel = "Fixed";
}
$vTitle_store = array();
$vTitle_rental_store = array();
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
        $vValue_rental = 'vRentalAlias_' . $db_master[$i]['vCode'];
        array_push($vTitle_rental_store, $vValue_rental);
        $$vValue_rental = isset($_POST[$vValue_rental]) ? $_POST[$vValue_rental] : '';
        if ($enableDeliveryHelper == "Yes") {
            $db_tDeliveryHelperNoteDriver = 'tDeliveryHelperNoteDriver_' . $db_master[$i]['vCode'];
            $tDeliveryHelperNoteDriverArr[$db_tDeliveryHelperNoteDriver] = isset($_POST[$db_tDeliveryHelperNoteDriver]) ? $_POST[$db_tDeliveryHelperNoteDriver] : '';
            $db_tDeliveryHelperNoteUser = 'tDeliveryHelperNoteUser_' . $db_master[$i]['vCode'];
            $tDeliveryHelperNoteUserArr[$db_tDeliveryHelperNoteUser] = isset($_POST[$db_tDeliveryHelperNoteUser]) ? $_POST[$db_tDeliveryHelperNoteUser] : '';
        }
        $db_tInfoText = 'tInfoText_' . $db_master[$i]['vCode'];
        $tInfoTextUserArr[$db_tInfoText] = isset($_POST[$db_tInfoText]) ? $_POST[$db_tInfoText] : '';
    }
}
$weekDaysArr = array(
    "Monday"    => "Mon",
    "Tuesday"   => "Tue",
    "Wednesday" => "Wed",
    "Thursday"  => "Thu",
    "Friday"    => "Fri",
    "Saturday"  => "Sat",
    "Sunday"    => "Sun"
);
$nightTimeArr = $nightSurgeDataArr = array();
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission($create)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create vehicle type.';
        header("Location:vehicle_type.php" . $queryString);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update vehicle type.';
        header("Location:vehicle_type.php".$queryString);
        exit;
    }
    if ($POOL_ENABLE != "Yes") {
        $ePoolStatus = "No";
        $fPoolPercentage = "0.00";
    }
    foreach ($weekDaysArr as $day => $dval) {
        $dayStartIndex = "t" . $dval . "NightStartTime";
        $dayEndIndex = "t" . $dval . "NightEndTime";
        $priceIndex = "f" . $dval . "NightPrice";
        $nStartTime = isset($_POST[$dayStartIndex]) ? $_POST[$dayStartIndex] : '00:00:00';
        $nEndTime = isset($_POST[$dayEndIndex]) ? $_POST[$dayEndIndex] : '';
        $nPrice = isset($_POST[$priceIndex]) ? $_POST[$priceIndex] : '1.00';
        if ($nStartTime == "") {
            //$nStartTime = '00:00:00';
        }
        if ($nEndTime == "") {
            //$nEndTime = '00:00:00';
        }
        if ($nPrice == "") {
            $nPrice = '1.00';
        }
        $nightTimeArr[$dayStartIndex] = $nStartTime;
        $nightTimeArr[$dayEndIndex] = $nEndTime;
        $nightTimeArr[$priceIndex] = $nPrice;
    }
    if ($eFareType == "Fixed") {
        $ePickStatus = "Inactive";
        $eNightStatus = "Inactive";
    } else {
        $ePickStatus = $ePickStatus;
        $eNightStatus = $eNightStatus;
    }
    if ($eFareType == "Regular" || $eFareType == "Hourly") {
        $eAllowQty = "No";
    }
    $insertUpdateNightJson = "";
    if (scount($nightTimeArr) > 0 && $eNightStatus == "Active") {
        $insertUpdateNightJson = ",tNightSurgeData='" . json_encode($nightTimeArr) . "'";
    }
    $images_ext_arr = explode(",", $tconfig["tsite_upload_image_file_extensions"]);
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        // $filecheck = basename($_FILES['vLogo']['name']);
        // $fileextarr = explode(".", $filecheck);
        // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);

        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vLogo'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        $flag_error = 0;
        $data = getimagesize($_FILES['vLogo']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        // if (!in_array($ext, $images_ext_arr)) {
        //     $flag_error = 1;
        //     $_SESSION['var_msg'] = $langage_lbl_admin['LBL_FILE_EXT_VALID_ERROR_MSG'] . " " . $tconfig["tsite_upload_image_file_extensions"];
        //     $_SESSION['success'] = "3";
        // }
         if ($error) {
            $flag_error = 1;
            $_SESSION['var_msg'] = $error;
            $_SESSION['success'] = "3";
        }
        /*if ($width != 360 && $height != 360) {
            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }*/
        if ($flag_error == 1) {
            if ($action == "Add") {
                $queryString1 = '&eType=' . $REQUEST_eType;
                header("Location:vehicle_type_action.php".$queryString);
                exit;
            } else {
                $queryString1 = '&eType=' . $REQUEST_eType;
                header("Location:vehicle_type_action.php?id=" . $id .$queryString1);
                exit;
            }
           
        }
    }
    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        // $filecheck = basename($_FILES['vLogo1']['name']);
        // $fileextarr = explode(".", $filecheck);
        // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);

        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vLogo1'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        $flag_error = 0;
        $data = getimagesize($_FILES['vLogo1']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        // if (!in_array($ext, $images_ext_arr)) {
        //     $flag_error = 1;
        //     $_SESSION['var_msg'] = $langage_lbl_admin['LBL_FILE_EXT_VALID_ERROR_MSG'] . " " . $tconfig["tsite_upload_image_file_extensions"];
        //     $_SESSION['success'] = "3";
        // }
        if ($error) {
            $flag_error = 1;
            $_SESSION['var_msg'] = $error;
            $_SESSION['success'] = "3";
        }
        /*if ($width != 360 && $height != 360) {
            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }*/
        if ($flag_error == 1) {
            if ($action == "Add") {
                header("Location:vehicle_type_action.php".$queryString);
                exit;
            } else {
                $queryString1 = '&eType=' . $REQUEST_eType;
                header("Location:vehicle_type_action.php?id=" . $id .$queryString1);
                exit;
            }
           
        }
    }
    $vVehicleType = $_POST['vVehicleType_' . $default_lang];
    if ($ePickStatus == "Active") {
    }
    if (SITE_TYPE == 'Demo') {
        $queryString1 = '&eType=' . $REQUEST_eType;
        header("Location:vehicle_type_action.php?id=" . $id . "&success=2".$queryString1);
        exit;
    }
    if ($temp_order == "1" && $action == "Add") {
        $temp_order = $iDisplayOrder_max;
    }



    $VehicleTypeBaseOnService = getVehicleTypeBaseOnService($org_eType, 'EDIT', 'No' , $temp_order , $iDisplayOrder);
    /*$i = $iDisplayOrder;
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            if ($app_type_service != 'Ride-Delivery') {
                $sql1 = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "' AND eType ='" . $app_type_service . "'";
            } else {
                $sql1 = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "' AND (eType ='Ride' OR  eType ='Deliver')";
            }
            $obj->sql_query($sql1);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            if ($app_type_service != 'Ride-Delivery') {
                $sql1 = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "' AND eType ='" . $app_type_service . "'";
            } else {
                $sql1 = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "' AND (eType ='Ride' OR  eType ='Deliver')";
            }
            $obj->sql_query($sql1);
        }
    }*/

    $Data_Update_Vehicle = array();
    $Data_Update_Vehicle['vVehicleType'] = $vVehicleType;
    $Data_Update_Vehicle['iVehicleCategoryId'] = $iVehicleCategoryId;
    $Data_Update_Vehicle['eFareType'] = $eFareType;
    $Data_Update_Vehicle['eIconType'] = $eIconType;
    $Data_Update_Vehicle['fFixedFare'] = $fFixedFare;
    $Data_Update_Vehicle['fPricePerKM'] = $fPricePerKM;
    $Data_Update_Vehicle['fPricePerMin'] = $fPricePerMin;
    $Data_Update_Vehicle['iBaseFare'] = $iBaseFare;
    $Data_Update_Vehicle['iMinFare'] = $iMinFare;
    $Data_Update_Vehicle['fCommision'] = $fCommision;
    $Data_Update_Vehicle['iPersonSize'] = $iPersonSize;
    $Data_Update_Vehicle['fBufferAmount'] = $fBufferAmount;
    $Data_Update_Vehicle['fNightPrice'] = $fNightPrice;
    $Data_Update_Vehicle['tNightStartTime'] = $tNightStartTime;
    $Data_Update_Vehicle['tNightEndTime'] = $tNightEndTime;
    $Data_Update_Vehicle['ePickStatus'] = $ePickStatus;
    $Data_Update_Vehicle['eAllowQty'] = $eAllowQty;
    $Data_Update_Vehicle['fPricePerHour'] = $fPricePerHour;
    $Data_Update_Vehicle['iMaxQty'] = $iMaxQty;
    $Data_Update_Vehicle['eType'] = $eType;
    $Data_Update_Vehicle['iCountryId'] = $iCountryId;
    $Data_Update_Vehicle['iLocationid'] = $iLocationId;
    $Data_Update_Vehicle['iStateId'] = $iStateId;
    $Data_Update_Vehicle['iCityId'] = $iCityId;
    $Data_Update_Vehicle['eNightStatus'] = $eNightStatus;
    $Data_Update_Vehicle['ePoolStatus'] = $ePoolStatus;
    $Data_Update_Vehicle['fPoolPercentage'] = $fPoolPercentage;
    $Data_Update_Vehicle['tMonPickStartTime'] = $tMonPickStartTime;
    $Data_Update_Vehicle['tMonPickEndTime'] = $tMonPickEndTime;
    $Data_Update_Vehicle['fMonPickUpPrice'] = $fMonPickUpPrice;
    $Data_Update_Vehicle['tTuePickStartTime'] = $tTuePickStartTime;
    $Data_Update_Vehicle['tTuePickEndTime'] = $tTuePickEndTime;
    $Data_Update_Vehicle['fTuePickUpPrice'] = $fTuePickUpPrice;
    $Data_Update_Vehicle['tWedPickStartTime'] = $tWedPickStartTime;
    $Data_Update_Vehicle['tWedPickEndTime'] = $tWedPickEndTime;
    $Data_Update_Vehicle['fWedPickUpPrice'] = $fWedPickUpPrice;
    $Data_Update_Vehicle['tThuPickStartTime'] = $tThuPickStartTime;
    $Data_Update_Vehicle['tThuPickEndTime'] = $tThuPickEndTime;
    $Data_Update_Vehicle['fThuPickUpPrice'] = $fThuPickUpPrice;
    $Data_Update_Vehicle['tFriPickStartTime'] = $tFriPickStartTime;
    $Data_Update_Vehicle['tFriPickEndTime'] = $tFriPickEndTime;
    $Data_Update_Vehicle['fFriPickUpPrice'] = $fFriPickUpPrice;
    $Data_Update_Vehicle['tSatPickStartTime'] = $tSatPickStartTime;
    $Data_Update_Vehicle['tSatPickEndTime'] = $tSatPickEndTime;
    $Data_Update_Vehicle['fSatPickUpPrice'] = $fSatPickUpPrice;
    $Data_Update_Vehicle['tSunPickStartTime'] = $tSunPickStartTime;
    $Data_Update_Vehicle['tSunPickEndTime'] = $tSunPickEndTime;
    $Data_Update_Vehicle['fSunPickUpPrice'] = $fSunPickUpPrice;
    $Data_Update_Vehicle['iCancellationTimeLimit'] = $iCancellationTimeLimit;
    $Data_Update_Vehicle['fCancellationFare'] = $fCancellationFare;
    $Data_Update_Vehicle['iWaitingFeeTimeLimit'] = $iWaitingFeeTimeLimit;
    $Data_Update_Vehicle['fWaitingFees'] = $fWaitingFees;
    $Data_Update_Vehicle['fTripHoldFees'] = $fTripHoldFees;
    $Data_Update_Vehicle['eFareCalcModel'] = $eFareCalcModel;
    $Data_Update_Vehicle['eDeliveryHelper'] = $eDeliveryHelper;
    $Data_Update_Vehicle['eOTPCodeEnable'] = $eOTPCodeEnable;
    $Data_Update_Vehicle['iDisplayOrder'] = $iDisplayOrder;
    $Data_Update_Vehicle['eBidStatus'] = $eBidStatus;
    $Data_Update_Vehicle['eInterCityStatus'] = $eInterCityStatus;
    $Data_Update_Vehicle['tInfoText'] = getJsonFromAnArr($tInfoTextUserArr);
    if (scount($nightTimeArr) > 0 && $eNightStatus == "Active") {
        $Data_Update_Vehicle['tNightSurgeData'] = json_encode($nightTimeArr);
    }
    if (scount($vTitle_store) > 0) {
        for ($i = 0; $i < scount($vTitle_store); $i++) {
            $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
            $vValue_rental = 'vRentalAlias_' . $db_master[$i]['vCode'];
            $Data_Update_Vehicle[$vValue] = $_POST[$vTitle_store[$i]];
            $Data_Update_Vehicle[$vValue_rental] = $_POST[$vTitle_rental_store[$i]];
        }
    }
    if ($enableDeliveryHelper == "Yes") {
        $Data_Update_Vehicle['tDeliveryHelperNoteDriver'] = getJsonFromAnArrWithoutClean($tDeliveryHelperNoteDriverArr); //getJsonFromAnArr($tDeliveryHelperNoteDriverArr);
        $Data_Update_Vehicle['tDeliveryHelperNoteUser'] = getJsonFromAnArrWithoutClean($tDeliveryHelperNoteUserArr); // getJsonFromAnArr($tDeliveryHelperNoteUserArr);
    }

    if ($id != '') {
        $where = " iVehicleTypeId = '" . $id . "'";
        $obj->MySQLQueryPerform($tbl_name, $Data_Update_Vehicle, 'update', $where);
    } else {
        $id = $obj->MySQLQueryPerform($tbl_name, $Data_Update_Vehicle, 'insert');
    }
    $message_print_id = $id;
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        $currrent_upload_time = time();
        $img_path = $tconfig["tsite_upload_images_vehicle_type_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo']['tmp_name'];
        $image_name = $_FILES['vLogo']['name'];
        $check_file_query = "select iVehicleTypeId,vLogo from vehicle_type where iVehicleTypeId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        $fileextarr = explode(".", $image_name);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        if ($image_name != "") {
            $img = $UPLOAD_OBJ->GeneralImageUploadVehicleType($message_print_id, $image_name, $image_object, $check_file[0]['vLogo']);
            $img_time = explode("_", $img);
            $time_val = $img_time[0];
            $vImage = $time_val . "." . $ext;
            $sql = "UPDATE " . $tbl_name . " SET `vLogo` = '" . addslashes($vImage) . "' WHERE `iVehicleTypeId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }
    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        $currrent_upload_time = time();
        $currrent_upload_time += 10;
        $img_path = $tconfig["tsite_upload_images_vehicle_type_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo1']['tmp_name'];
        $image_name = $_FILES['vLogo1']['name'];
        $check_file_query = "select iVehicleTypeId,vLogo1 from vehicle_type where iVehicleTypeId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        $fileextarr = explode(".", $image_name);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        if ($image_name != "") {
            $img = $UPLOAD_OBJ->GeneralImageUploadVehicleType($message_print_id, $image_name, $image_object, $check_file[0]['vLogo1']);
            $img_time = explode("_", $img);
            $time_val = $img_time[0];
            $vImage1 = $time_val . "." . $ext;
            $sql = "UPDATE " . $tbl_name . " SET `vLogo1` = '" . addslashes($vImage1) . "' WHERE `iVehicleTypeId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }
    if ($action == "Add") {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        $_SESSION['success'] = "1";
    } else {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
    }
    header("Location:" . $backlink);
    exit;
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleTypeid = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        if (isset($db_data[0]['tNightSurgeData'])) {
            $nightSurgeDataArr = (array)json_decode($db_data[0]['tNightSurgeData']);
        }
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_rental = 'vRentalAlias_' . $db_master[$i]['vCode'];
                $$vValue_rental = $value[$vValue_rental];
                $vVehicleType = $value['vVehicleType'];
                $iVehicleCategoryId = $value['iVehicleCategoryId'];
                $fPricePerKM = $value['fPricePerKM'];
                $fPricePerMin = $value['fPricePerMin'];
                $iBaseFare = $value['iBaseFare'];
                $iMinFare = $value['iMinFare'];
                $fCommision = $value['fCommision'];
                $iPersonSize = $value['iPersonSize'];
                $fBufferAmount = $value['fBufferAmount'];
                $fPricePerHour = $value['fPricePerHour'];
                $fNightPrice = ($value['fNightPrice'] == 0) ? '' : $value['fNightPrice'];
                $tNightStartTime = $value['tNightStartTime'];
                $tNightEndTime = $value['tNightEndTime'];
                $ePickStatus = $value['ePickStatus'];
                $eNightStatus = $value['eNightStatus'];
                $tMonPickStartTime = $value['tMonPickStartTime'];
                $tMonPickEndTime = $value['tMonPickEndTime'];
                $fMonPickUpPrice = ($value['fMonPickUpPrice'] == 0) ? '' : $value['fMonPickUpPrice'];
                $tTuePickStartTime = $value['tTuePickStartTime'];
                $tTuePickEndTime = $value['tTuePickEndTime'];
                $fTuePickUpPrice = ($value['fTuePickUpPrice'] == 0) ? '' : $value['fTuePickUpPrice'];
                $tWedPickStartTime = $value['tWedPickStartTime'];
                $tWedPickEndTime = $value['tWedPickEndTime'];
                $fWedPickUpPrice = ($value['fWedPickUpPrice'] == 0) ? '' : $value['fWedPickUpPrice'];
                $tThuPickStartTime = $value['tThuPickStartTime'];
                $tThuPickEndTime = $value['tThuPickEndTime'];
                $fThuPickUpPrice = ($value['fThuPickUpPrice'] == 0) ? '' : $value['fThuPickUpPrice'];
                $tFriPickStartTime = $value['tFriPickStartTime'];
                $tFriPickEndTime = $value['tFriPickEndTime'];
                $fFriPickUpPrice = ($value['fFriPickUpPrice'] == 0) ? '' : $value['fFriPickUpPrice'];
                $tSatPickStartTime = $value['tSatPickStartTime'];
                $tSatPickEndTime = $value['tSatPickEndTime'];
                $fSatPickUpPrice = ($value['fSatPickUpPrice'] == 0) ? '' : $value['fSatPickUpPrice'];
                $tSunPickStartTime = $value['tSunPickStartTime'];
                $tSunPickEndTime = $value['tSunPickEndTime'];
                $fSunPickUpPrice = ($value['fSunPickUpPrice'] == 0) ? '' : $value['fSunPickUpPrice'];
                $vLogo = $value['vLogo'];
                $vLogo1 = $value['vLogo1'];
                $eType = $value['eType'];
                $fFixedFare = $value['fFixedFare'];
                $eFareType = $value['eFareType'];
                $eIconType = $value['eIconType'];
                $eAllowQty = $value['eAllowQty'];
                $iMaxQty = $value['iMaxQty'];
                $iCancellationTimeLimit = ($value['iCancellationTimeLimit'] == 0) ? '' : $value['iCancellationTimeLimit'];
                $fCancellationFare = ($value['fCancellationFare'] == 0) ? '' : $value['fCancellationFare'];
                $iWaitingFeeTimeLimit = ($value['iWaitingFeeTimeLimit'] == 0) ? '' : $value['iWaitingFeeTimeLimit'];
                $fWaitingFees = ($value['fWaitingFees'] == 0) ? '' : $value['fWaitingFees'];
                $fTripHoldFees = ($value['fTripHoldFees'] == 0) ? '' : $value['fTripHoldFees'];
                $iCountryId = $value['iCountryId'];
                $iStateId = $value['iStateId'];
                $iCityId = $value['iCityId'];
                $iLocationId = $value['iLocationid'];
                $iDisplayOrder_db = $value['iDisplayOrder'];
                $ePoolStatus = $value['ePoolStatus'];
                $fPoolPercentage = $value['fPoolPercentage'];
                $eFareCalcModel = $value['eFareCalcModel'];
                $eDeliveryHelper = $value['eDeliveryHelper'];
                $tDeliveryHelperNoteUser = (!empty($value['tDeliveryHelperNoteUser'])) ? json_decode($value['tDeliveryHelperNoteUser'], true) : array();
                $tDeliveryHelperNoteDriver = (!empty($value['tDeliveryHelperNoteDriver'])) ? json_decode($value['tDeliveryHelperNoteDriver'], true) : array();
                $eOTPCodeEnable = $value['eOTPCodeEnable'];
                $tInfoText = (!empty($value['tInfoText'])) ? json_decode($value['tInfoText'], true) :  array();
                $userEditDataArr[$vValue] = $$vValue;
                $userEditDataArr[$vValue_rental] = $$vValue_rental;
                $userEditDataArr['tDeliveryHelperNoteUser_' . $db_master[$i]['vCode']] = isset($tDeliveryHelperNoteUser['tDeliveryHelperNoteUser_' . $db_master[$i]['vCode']]) ? $tDeliveryHelperNoteUser['tDeliveryHelperNoteUser_' . $db_master[$i]['vCode']] : '' ;
                $userEditDataArr['tDeliveryHelperNoteDriver_' . $db_master[$i]['vCode']] = isset($tDeliveryHelperNoteDriver['tDeliveryHelperNoteDriver_' . $db_master[$i]['vCode']]) ? $tDeliveryHelperNoteDriver['tDeliveryHelperNoteDriver_' . $db_master[$i]['vCode']] : '';
                $userEditDataArr['tInfoText_' . $db_master[$i]['vCode']] = isset($tInfoText['tInfoText_' . $db_master[$i]['vCode']]) ? $tInfoText['tInfoText_' . $db_master[$i]['vCode']] : '';
            }
        }
    }
}
if ($APP_TYPE == 'UberX') {
    $sql_cat = "SELECT *  FROM " . $sql_vehicle_category_table_name . " WHERE iParentId='0'";
    $db_data_cat = $obj->MySQLSelect($sql_cat);
}
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);


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
    <title>Admin | <?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Css-->
    <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap-clockpicker.min.css">
    <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Css-->
    <style type="text/css">.cke_contents {

            height: 200px !important

        }</style>

    <style>
        .help-block {
            display: block;
            margin-top: 5px;
            margin-bottom: 10px;
            color: #ff0000;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2> <?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> </h2>
                    <!-- <a href="vehicle_type.php">

                                <input type="button" value="Back to Listing" class="add-btn">

                            </a> -->
                    <a href="vehicle_type.php<?= $queryString; ?>" class="back_link_">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> Updated successfully.
                        </div>
                        <br/>
                    <? } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable ">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will
                            be enabled on the main script we will provide you.
                        </div>
                        <br/>
                    <? } else if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?= $_REQUEST['varmsg']; ?>
                        </div>
                        <br/>
                    <? } else if ($success == 4) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            "Please Select Night Start Time less than Night End Time."
                        </div>
                        <br/>
                    <? } ?>

                    <? if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Record Not Updated.
                        </div>
                        <br/>
                    <? } ?>
                    <?php include('valid_msg.php'); ?>
                    <div id="price1"></div>
                    <form id="_vehicleType_form" name="_vehicleType_form" method="post" action=""
                          enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <?php if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
                            <input type="hidden" name="APP_TYPE" value="<?= $app_type_service; ?>"/>
                        <?php } else { ?>
                            <input type="hidden" name="APP_TYPE" value="<?= $APP_TYPE; ?>"/>
                        <?php } ?>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?= $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink"
                               value="vehicle_type.php<?= $queryString; ?>"/>
                        <div class="row">
                            <div class="col-lg-12" id="errorMessage"></div>
                        </div>

                        <?php
                        $eType_value = $REQUEST_eType;
                        $eBidStatus = 'No';
                        $eInterCityStatus = 'No';
                        if($REQUEST_eType == "TaxiBid"){
                            $eType_value = 'Ride';
                            $eBidStatus = 'Yes';
                        } else if ($REQUEST_eType == "InterCity"){
                            $eType_value = 'Ride';
                            $eInterCityStatus = 'Yes';
                        } else if ($REQUEST_eType == "Ambulance"){
                            $eType_value = 'Ride';
                        } ?>
                        <input type="hidden" name="eBidStatus" value="<?= $eBidStatus; ?>" id='eBidStatus' />
                        <input type="hidden" name="eInterCityStatus" value="<?= $eInterCityStatus; ?>" id='eInterCityStatus' />       
                        <?php if ($APP_TYPE == 'UberX') { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?>
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" name='iVehicleCategoryId' required>
                                        <option value="">--select--</option>
                                        <? for ($i = 0; $i < scount($db_data_cat); $i++) { ?>
                                            <optgroup label="<?= $db_data_cat[$i]['vCategory_' . $default_lang]; ?>">
                                                
                                                <?php
                                                $sql = "SELECT * FROM  `" . $sql_vehicle_category_table_name . "` WHERE  `iParentId` = '" . $db_data_cat[$i]['iVehicleCategoryId'] . "' ";
                                                $db_data2 = $obj->MySQLSelect($sql);
                                                for ($j = 0; $j < scount($db_data2); $j++) {
                                                    ?>
                                                    <option value="<?= $db_data2[$j]['iVehicleCategoryId'] ?>"
                                                        <?php
                                                        if ($db_data2[$j]['iVehicleCategoryId'] == $iVehicleCategoryId) {
                                                            echo 'selected';
                                                        }
                                                        ?>
                                                    >
                                                        <?= "&nbsp;&nbsp;|-- " . $db_data2[$j]['vCategory_' . $default_lang]; ?></option>
                                                <? } ?>
                                            </optgroup>
                                        <? } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        <?php
                        if (($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && $app_type_service != 'UberX') {
                            if (
                                $action == 'Add' && !in_array($REQUEST_eType, [
                                    'Ride',
                                    'Deliver',
                                    'Ambulance',
                                    'TaxiBid',
                                    'InterCity'
                                ])
                            ) {
                      
                                if ($rideTypeEnabled == 'Yes' && $deliveryTypeEnabled == 'Yes') {
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Vehicle Category Type
                                                <span class="red"> *</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">  <?php echo $eType; ?>
                                            <select class="form-control" name='eType' required id='etypedelivery'>
                                                <? if ($rideTypeEnabled == 'Yes') { ?>
                                                    <option value="Ride"
                                                            data-vtype="Ride" <?php if ($eType == "Ride") {
                                                        echo 'selected="selected"';
                                                    } ?> ><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?></option>
                                                <? }
                                                if ($deliveryTypeEnabled == 'Yes') { ?>
                                                    <option value="Deliver"
                                                            data-vtype="Delivery" <?php if ($eType == "Deliver") {
                                                        echo 'selected="selected"';
                                                    } ?>>
                                                        Delivery
                                                    </option>
                                                <? }
                                                if ($MODULES_OBJ->isEnableMedicalServices()) { ?>
                                                    <option value="Ride"
                                                            data-vtype="Ambulance" <?php if ($eType == "Ride" && $eIconType == "Ambulance") {
                                                        echo 'selected="selected"';
                                                    } ?>>
                                                        Ambulance
                                                    </option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } else if ($rideTypeEnabled == 'Yes' && $deliveryTypeEnabled == 'No') { ?>
                                    <input type="hidden" name="eType" value="Ride"/>
                                <?php } else if ($rideTypeEnabled == 'No' && $deliveryTypeEnabled == 'Yes') { ?>
                                    <input type="hidden" name="eType" value="Deliver"/>
                                <?php }
                            } else if (
                                $action == 'Add' && in_array($REQUEST_eType, [
                                    'Ride',
                                    'Deliver',
                                    'Ambulance',
                                    'TaxiBid',
                                    'InterCity'
                                ])
                            ) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Vehicle Category Type
                                            :
                                            <?php if ($REQUEST_eType == "TaxiBid") {
                                                echo TAXI_BID;
                                            } else if ($REQUEST_eType == "InterCity") {
                                                echo 'InterCity';
                                            } else { 
                                                echo $REQUEST_eType;
                                            } ?>

                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">

                                        <input type="hidden" name="eType" value="<?= $eType_value; ?>"
                                               id='etypedelivery'  data-vtype="<?= $REQUEST_eType ?>"/>

                                    </div>
                                </div>
                            <?php } else {?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if ($REQUEST_eType == "TaxiBid") {
                                               $eTypeDisplay = TAXI_BID;
                                        }  else if ($REQUEST_eType == "InterCity") {
                                            $eTypeDisplay = $REQUEST_eType;
                                        } else {
                                            $eTypeDisplay = $eType;
                                        }?>
                                        <label>Vehicle Category Type : <?= $eIconType == "Ambulance" ? "Ambulance" : $eTypeDisplay; ?></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">

                                        <input type="hidden" name="eType" value="<?= $eType; ?>" id='etypedelivery' data-vtype="<?= $eIconType == "Ambulance" ? "Ambulance" : $eType ?>"/>

                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            $Vehicle_type_name = ($APP_TYPE == 'Delivery') ? 'Deliver' : $APP_TYPE;
                            ?>
                            <input type="hidden" name="eType" value="<?= $Vehicle_type_name; ?>"/>
                        <?php } ?>



                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="vVehicleType_Default" name="vVehicleType_Default"
                                           value="<?= isset($userEditDataArr['vVehicleType_' . $default_lang]) ? $userEditDataArr['vVehicleType_' . $default_lang] : ''; ?>"
                                           data-originalvalue="<?= isset($userEditDataArr['vVehicleType_' . $default_lang]) ? $userEditDataArr['vVehicleType_' . $default_lang] : ''; ?>"
                                           readonly="readonly"
                                           required <?php if ($id == "") { ?> onclick="editVehicleType('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editVehicleType('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="modal fade" id="vehicle_type_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVehicleType_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vVehicleType_' . $vCode;
                                                $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '' ;
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    } else {
                                                        if ($vCode == $default_lang) {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    }
                                                }
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                                                            (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                               id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vVehicleType_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vVehicleType_', '<?= $default_lang ?>');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="modal-footer" style="margin-top: 0">
                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveVehicleType()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVehicleType_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php if (ENABLE_RENTAL_OPTION == 'Yes') { ?>
                                <div id="rentalOption">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Vehicle Type Name For Rental <span class="red"> *</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text"
                                                   class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                                   id="vRentalAlias_Default"
                                                   value="<?= isset($userEditDataArr['vRentalAlias_' . $default_lang]) ? $userEditDataArr['vRentalAlias_' . $default_lang] : ''; ?>"
                                                   data-originalvalue="<?= isset($userEditDataArr['vRentalAlias_' . $default_lang]) ? $userEditDataArr['vRentalAlias_' . $default_lang] : ''; ?>"
                                                   readonly="readonly"
                                                   required <?php if ($id == "") { ?> onclick="editVehicleRental('Add')" <?php } ?>>
                                        </div>
                                        <?php if ($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit" onclick="editVehicleRental('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="modal fade" id="vehicle_rental_Modal" tabindex="-1" role="dialog"
                                         aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="modal_action"></span>
                                                        Vehicle Type Name For Rental
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vRentalAlias_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vRentalAlias_' . $vCode;
                                                        $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '';
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if ($EN_available) {
                                                                if ($vCode == "EN") {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            } else {
                                                                if ($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Vehicle Type Name For Rental (<?= $vTitle; ?>
                                                                    ) <?php echo $required_msg; ?></label>
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <input type="text" class="form-control"
                                                                       name="<?= $vValue; ?>" id="<?= $vValue; ?>"
                                                                       value="<?= $$vValue; ?>"
                                                                       data-originalvalue="<?= $$vValue; ?>"
                                                                       placeholder="<?= $vTitle; ?> Value">
                                                                <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                                     style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage"
                                                                                    class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vRentalAlias_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage"
                                                                                    class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vRentalAlias_', '<?= $default_lang ?>');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="modal-footer" style="margin-top: 0">
                                                    <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                        <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                        </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                    <div class="nimot-class-but" style="margin-bottom: 0">
                                                        <button type="button" class="save"
                                                                style="margin-left: 0 !important"
                                                                onclick="saveVehicleRental()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok"
                                                                data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vRentalAlias_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                    </div>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="vVehicleType_<?= $default_lang ?>"
                                           name="vVehicleType_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vVehicleType_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                            <?php if (ENABLE_RENTAL_OPTION == 'Yes') { ?>
                                <div id="rentalOption">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Vehicle Type Name For Rental
                                                <span class="red"> *</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" class="form-control"
                                                   id="vRentalAlias_<?= $default_lang ?>"
                                                   name="vRentalAlias_<?= $default_lang ?>"
                                                   value="<?= $userEditDataArr['vRentalAlias_' . $default_lang]; ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($action == "Add" || $eType == "Ride" || $eType == "Deliver") { ?>
                            <div id="infoText">
                                <?php if (scount($db_master) > 0) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Vehicle Type Info
                                                <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                                   data-original-title="<?= $langage_lbl_admin['LBL_VEHICLE_INFO'] ?>"></i>
                                            </label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">

                                            <textarea class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                                      name="tInfoText_Default" id="tInfoText_Default"
                                                      readonly="readonly"
                                                      data-originalvalue="<?= isset($tInfoText['tInfoText_' . $default_lang]) ? $tInfoText['tInfoText_' . $default_lang] : ''; ?>" <?php if ($id == "") { ?> onclick="editVehicleDescInfo('Add')" <?php } ?>> <?= isset($tInfoText['tInfoText_' . $default_lang]) ? $tInfoText['tInfoText_' . $default_lang] : ''; ?></textarea>
                                        </div>
                                        <?php if ($id != "") { ?>
                                            <div class="col-lg-1">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit"
                                                        onclick="editVehicleDescInfo('Edit', 'tInfoText_Modal')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="modal fade" id="tInfoText_Modal" tabindex="-1" role="dialog"
                                         aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="modal_action"></span>
                                                        <label>Vehicle Type Info</label>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'tInfoText_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vLTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $tInfoText = 'tInfoText_' . $vCode;
                                                        $$tInfoText = $userEditDataArr[$tInfoText];
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if ($EN_available) {
                                                                if ($vCode == "EN") {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            } else {
                                                                if ($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Vehicle Type Info (<?= $vLTitle ?>)</label>
                                                            </div>
                                                            <div class="<?= $page_title_class ?> desc-block">

                                                                <textarea class="form-control" name="<?= $tInfoText; ?>"
                                                                          id="<?= $tInfoText; ?>"
                                                                          data-originalvalue="<?= $$tInfoText; ?>"
                                                                          placeholder="<?= $vLTitle; ?> Value"> <?= $$tInfoText; ?></textarea>
                                                                <div class="desc_counter pull-right"
                                                                     style="margin-top: 5px">100/100
                                                                </div>
                                                                <div class="text-danger"
                                                                     id="<?= $tInfoText . '_error'; ?>"
                                                                     style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage"
                                                                                    class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('tInfoText_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage"
                                                                                    class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('tInfoText_', '<?= $default_lang ?>');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="modal-footer" style="margin-top: 0">
                                                    <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                        <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                        </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                    <div class="nimot-class-but" style="margin-bottom: 0">
                                                        <button type="button" class="save"
                                                                style="margin-left: 0 !important"
                                                                onclick="saveVehicleDescInfo()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok"
                                                                data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'tInfoText_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                    </div>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Vehicle Type Info</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6 desc-block">

                                            <textarea class="form-control" name="tInfoText_<?= $default_lang ?>"
                                                      id="tInfoText_<?= $default_lang ?>"> <?= $tInfoText['tInfoText_' . $default_lang]; ?></textarea>
                                            <div class="desc_counter pull-right" style="margin-top: 5px">100/100</div>
                                            <div class="text-danger" id="tInfoText_<?= $default_lang ?>"
                                                 style="display: none;">This field is required.
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>



                        <?php if ($THEME_OBJ->isCJXDoctorv2ThemeActive() == "No") { ?>
                            <?php if ($action == 'Edit') { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label> Vehicle Category / Map Icon Type
                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                               data-original-title='The icon on the map would be shown as per the option you select here. e.g.: If you select "Bike" as a value from the dropdown below then Bike icon would be shown on the map.'></i>
                                            : <?= $eIconType; ?></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="hidden" name="eIconType" value="<?= $eIconType; ?>"/>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <?php if ($MODULES_OBJ->isEnableMedicalServices() && in_array($REQUEST_eType, ['Ambulance']) ) { ?>
                                     <input type="hidden" name="eIconType" value="Ambulance"/>
                                <?php } else {?>
                                <div class="row" id="VehicleIconType">
        <div class="col-lg-12">
            <label> Vehicle Category / Map Icon Type
                <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                   data-original-title='The icon on the map would be shown as per the option you select here. e.g.: If you select "Bike" as a value from the dropdown below then Bike icon would be shown on the map.'></i>
            </label>
        </div>
        <div class="col-md-6 col-sm-6">
            <select class="form-control" name='eIconType' id="eIconType" required>
                <?php
                // Get dynamic categories from database
                $iconTypesSql = "SELECT * FROM vehicle_icon_types WHERE eStatus = 'Active' 
                               AND (eType = 'Ride' OR eType = 'Both') 
                               ORDER BY iDispOrder ASC";
                $iconTypes = $obj->MySQLSelect($iconTypesSql);
                
                // Default categories (fallback if table doesn't exist or is empty)
                $defaultCategories = array(
                    array('vIconValue' => 'Car', 'vIconName' => 'Car'),
                    array('vIconValue' => 'Bike', 'vIconName' => 'Moto-Bike'),
                    array('vIconValue' => 'Cycle', 'vIconName' => 'Cycle')
                );
                
                // Use dynamic categories if available, otherwise use default
                $categoriesToShow = ($iconTypes && count($iconTypes) > 0) ? $iconTypes : $defaultCategories;
                
                // Display categories
                foreach ($categoriesToShow as $icon) {
                    $iconValue = isset($icon['vIconValue']) ? $icon['vIconValue'] : '';
                    $iconName = isset($icon['vIconName']) ? $icon['vIconName'] : '';
                    $selected = ($eIconType == $iconValue) ? 'selected="selected"' : '';
                    
                    echo '<option id="'.$iconValue.'" value="'.$iconValue.'" '.$selected.'>';
                    echo htmlspecialchars($iconName);
                    echo '</option>';
                }
                ?>
                
                <?php if (($APP_TYPE == "Delivery" || $APP_TYPE == "Ride-Delivery" || ($APP_TYPE == "Ride-Delivery-UberX" && ONLYDELIVERALL == "No" )) && !in_array($REQUEST_eType, ['TaxiBid','InterCity']) ){ ?>
                    <option id="Truck" value="Truck"<?php
                    if ($eIconType == "Truck") {
                        echo ' selected="selected"';
                    }
                    ?>>Truck</option>
                <?php } ?>
            </select>
            
            <!-- IMPORTANT: Ye section add karo -->
            <div style="margin-top: 10px;">
                <small class="text-muted">
                    Note: Select type "Moto-Bike" if you want to set this vehicle for "Moto" Service
                </small>
                <br>
                <a href="vehicle_category_manage.php" target="_blank" class="btn btn-sm btn-primary" style="margin-top: 5px;">
                    <i class="fa fa-plus-circle"></i> Add New Vehicle Category
                </a>
            </div>
        </div>
    </div>
                            <?php } } ?>
                        <?php } else { ?>
                            <input type="hidden" name="eIconType" value="Car"/>
                        <?php } ?>

                        <?php if( !in_array($REQUEST_eType, ['TaxiBid','InterCity'])){ ?>

                        <div class="row" id="poolenable">
                            <div class="col-lg-12">
                                <label>Enable Pool - Shared Ride
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='Enable it as On, if you want your <?= strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?> to share the ride with this vehicle type'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" id="ePoolStatus" onChange="showpoolpercentage();"
                                           name="ePoolStatus" <?= ($id != '' && $ePoolStatus == 'Yes') ? 'checked' : ''; ?>/>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($enableDeliveryHelper == "Yes") { ?>
                            <?php if ($action == "Add" || $eType == "Deliver") { ?>
                                <div class="row" id="deliveryHelper">
                                    <div class="col-lg-12">
                                        <label>Enable Delivery Helper
                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                               data-original-title="<?= $langage_lbl_admin['LBL_DELIVERY_HELPER_INFO'] ?>"></i>
                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" id="eDeliveryHelper"
                                                   name="eDeliveryHelper" <?= ($id != '' && $eDeliveryHelper == 'Yes') ? 'checked' : ''; ?>
                                                   value="Yes"/>
                                        </div>
                                    </div>
                                </div>
                                <div id="deliveryHelperNote">
                                    <?php if (scount($db_master) > 0) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Delivery Helper Note
                                                    for <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>
                                                    <span class="red"> *</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">

                                                <textarea class="form-control ckeditor" rows="10"
                                                          name="tDeliveryHelperNoteDriver_Default"
                                                          id="tDeliveryHelperNoteDriver_Default" readonly="readonly"
                                                          required=""> <?= $tDeliveryHelperNoteDriver['tDeliveryHelperNoteDriver_' . $default_lang]; ?></textarea>
                                                <div class="text-danger" id="tDeliveryHelperNoteDriver_Default_error"
                                                     style="display: none;">This field is required.
                                                </div>
                                            </div>
                                            <div class="col-lg-1">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit"
                                                        onclick="editDeliveryHelperNote('Edit', 'tDeliveryHelperDriver_Modal')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Delivery Helper Note
                                                    for <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN'] ?>
                                                    <span class="red"> *</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">

                                                <textarea class="form-control ckeditor" rows="10"
                                                          name="tDeliveryHelperNoteUser_Default"
                                                          id="tDeliveryHelperNoteUser_Default"
                                                          readonly="readonly"> <?= $tDeliveryHelperNoteUser['tDeliveryHelperNoteUser_' . $default_lang]; ?></textarea>
                                                <div class="text-danger" id="tDeliveryHelperNoteUser_Default_error"
                                                     style="display: none;">This field is required.
                                                </div>
                                            </div>
                                            <div class="col-lg-1">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit"
                                                        onclick="editDeliveryHelperNote('Edit', 'tDeliveryHelperUser_Modal')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="tDeliveryHelperDriver_Modal" tabindex="-1"
                                             role="dialog" aria-hidden="true" data-backdrop="static"
                                             data-keyboard="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span>
                                                            <label>Delivery Helper Note
                                                                for <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></label>
                                                            <button type="button" class="close" data-dismiss="modal">x
                                                            </button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        for ($i = 0; $i < $count_all; $i++) {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vLTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $tDeliveryHelperNoteDriver = 'tDeliveryHelperNoteDriver_' . $vCode;
                                                            $$tDeliveryHelperNoteDriver = $userEditDataArr[$tDeliveryHelperNoteDriver];
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Delivery Helper Note
                                                                        for <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>
                                                                        (<?= $vLTitle ?>)
                                                                    </label>
                                                                </div>
                                                                <div class="col-lg-12">

                                                                    <textarea class="form-control ckeditor" rows="10"
                                                                              name="<?= $tDeliveryHelperNoteDriver; ?>"
                                                                              id="<?= $tDeliveryHelperNoteDriver; ?>"> <?= $$tDeliveryHelperNoteDriver; ?></textarea>
                                                                    <div class="text-danger"
                                                                         id="<?= $tDeliveryHelperNoteDriver . '_error'; ?>"
                                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left"
                                                            style="margin-bottom: 15px; margin-top: 0;">
                                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" id="tPageDesc_btn"
                                                                    style="margin-left: 0 !important"
                                                                    onclick="saveDeliveryHelperNote('tDeliveryHelperNoteDriver_', 'tDeliveryHelperDriver_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok"
                                                                    data-dismiss="modal"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="tDeliveryHelperUser_Modal" tabindex="-1"
                                             role="dialog" aria-hidden="true" data-backdrop="static"
                                             data-keyboard="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span>
                                                            <label>Delivery Helper Note
                                                                for <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN'] ?></label>
                                                            <button type="button" class="close" data-dismiss="modal">x
                                                            </button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        for ($i = 0; $i < $count_all; $i++) {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vLTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $tDeliveryHelperNoteUser = 'tDeliveryHelperNoteUser_' . $vCode;
                                                            $$tDeliveryHelperNoteUser = $userEditDataArr[$tDeliveryHelperNoteUser];
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Delivery Helper Note
                                                                        for <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN'] ?>
                                                                        (<?= $vLTitle ?>)
                                                                    </label>
                                                                </div>
                                                                <div class="col-lg-12">

                                                                    <textarea class="form-control ckeditor" rows="10"
                                                                              name="<?= $tDeliveryHelperNoteUser; ?>"
                                                                              id="<?= $tDeliveryHelperNoteUser; ?>"> <?= $$tDeliveryHelperNoteUser; ?></textarea>
                                                                    <div class="text-danger"
                                                                         id="<?= $tDeliveryHelperNoteUser . '_error'; ?>"
                                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left"
                                                            style="margin-bottom: 15px; margin-top: 0;">
                                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" id="tPageDesc_btn"
                                                                    style="margin-left: 0 !important"
                                                                    onclick="saveDeliveryHelperNote('tDeliveryHelperNoteUser_', 'tDeliveryHelperUser_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok"
                                                                    data-dismiss="modal"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Delivery Helper Note
                                                    for <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>
                                                    <span class="red"> *</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">

                                                <textarea class="form-control ckeditor" rows="10"
                                                          name="tDeliveryHelperNoteDriver_<?= $default_lang ?>"
                                                          id="tDeliveryHelperNoteDriver_<?= $default_lang ?>"
                                                          required=""> <?= $tDeliveryHelperNoteDriver['tDeliveryHelperNoteDriver_' . $default_lang]; ?></textarea>
                                                <div class="text-danger"
                                                     id="tDeliveryHelperNoteDriver_<?= $default_lang ?>"
                                                     style="display: none;">This field is required.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Delivery Helper Note
                                                    for <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN'] ?>
                                                    <span class="red"> *</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">

                                                <textarea class="form-control ckeditor" rows="10"
                                                          name="tDeliveryHelperNoteUser_<?= $default_lang ?>"
                                                          id="tDeliveryHelperNoteUser_<?= $default_lang ?>"> <?= $tDeliveryHelperNoteUser['tDeliveryHelperNoteUser_' . $default_lang]; ?></textarea>
                                                <div class="text-danger"
                                                     id="tDeliveryHelperNoteUser_<?= $default_lang ?>_error"
                                                     style="display: none;">This field is required.
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php
                            }
                        }
                        ?>



                        <?php if ($action == "Add" && ($enableOTPVerificationRide == "Yes" || $enableOTPVerificationDelivery == "Yes")) { ?>
                            <div class="row" id="OTPVerification">
                                <div class="col-lg-12">
                                    <label id="otpTitleRide">Ask OTP/Confirmation code before starting Ride
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title="Ask OTP/Confirmation code before starting Ride"></i>
                                    </label>
                                    <label id="otpTitleDelivery" style="display: none;">Ask OTP/Confirmation code before
                                        starting Parcel Delivery
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title="Ask OTP/Confirmation code before starting Parcel Delivery"></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" id="eOTPCodeEnable"
                                               name="eOTPCodeEnable" <?= ($id != '' && $eOTPCodeEnable == 'Yes') ? 'checked' : ''; ?>
                                               value="Yes"/>
                                    </div>
                                </div>
                            </div>
                        <?php } elseif ($action == "Edit" && $enableOTPVerificationRide == "Yes" && $eType == "Ride") { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Ask OTP/Confirmation code before starting Ride
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title="Ask OTP/Confirmation code before starting Ride"></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" id="eOTPCodeEnable"
                                               name="eOTPCodeEnable" <?= ($id != '' && $eOTPCodeEnable == 'Yes') ? 'checked' : ''; ?>
                                               value="Yes"/>
                                    </div>
                                </div>
                            </div>
                        <?php } elseif ($action == "Edit" && $enableOTPVerificationDelivery == "Yes" && $eType == "Deliver") { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Ask OTP/Confirmation code before starting Parcel Delivery
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title="Ask OTP/Confirmation code before starting Parcel Delivery"></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" id="eOTPCodeEnable"
                                               name="eOTPCodeEnable" <?= ($id != '' && $eOTPCodeEnable == 'Yes') ? 'checked' : ''; ?>
                                               value="Yes"/>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>


                        <?php if(in_array($REQUEST_eType, ['TaxiBid','InterCity'])){ ?>
                            <input type="hidden" name="eFareCalcModel" value="Fixed" />
                        <?php }else{ ?>
                            <div class="row" id="farestrategymodel">
                                <div class="col-lg-12">
                                    <label> Fare Model Strategy
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='Fix fare strategy will creates a charge as estimated fare. Incremental Fare strategy will generates fare as per time & distance of the trip.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select <?= ($id != '' && $ePoolStatus == 'Yes') ? 'disabled' : ''; ?> class="form-control" name='eFareCalcModel' id="eFareCalcModel" required>
                                        <option id="Incremental" value="Incremental" <?
                                        if ($eFareCalcModel == "Incremental") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>Incremental
                                        </option>
                                        <option id="Fixed" value="Fixed" <?
                                        if ($eFareCalcModel == "Fixed") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>Fixed
                                        </option>
                                    </select>
                                    <p> If the pool is activated, then only "Fixed" Fare Model Strategy apply.</p>
                                </div>
                            </div>
                         <?php } ?>

                        <div class="row" style="display: none;">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                                    <span class="red"> *</span>
                                    <? if ($APP_TYPE != "UberX") { ?>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='Please add type of your vehicle like "Mini/Large MPVs", "Luxury vehicles", "SUVs" etc.'></i>
                                    <? } ?>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="vVehicleType" id="vVehicleType"
                                       value="<?= $vVehicleType; ?>"
                                       placeholder="<?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Select Location
                                    <span class="red"> *</span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='Select the location in which you would like to appear this vehicle type. For example "Luxurious" vehicle type to appear for any specific city or state or may be for whole country. You can define these locations from "Manage Locations >> Geo Fence Location" section'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <? //echo "<pre>"; print_R($userObj->locations); ?>
                                <select class="form-control" name='iLocationId' id="iLocationId" required=""
                                        onchange="changeCode_distance(this.value);">
                                    <option value="">Select Location</option>
                                    <option value="-1" <? if ($iLocationId == "-1") { ?>selected<? } ?>>All</option>
                                    <?php
                                    foreach ($db_location as $i => $row) {
                                        if (scount($userObj->locations) > 0 && !in_array($row['iLocationId'], $userObj->locations)) {
                                            continue;
                                        }
                                        ?>
                                        <option value="<?= $row['iLocationId'] ?>"
                                                <? if ($iLocationId == $row['iLocationId']) { ?>selected<? } ?>><?= $row['vLocationName'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php if ($userObj->hasPermission('view-geo-fence-locations')) { ?>
                                <div class="col-md-6 col-sm-6">
                                    <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if ($APP_TYPE == 'UberX') { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_FARE_TYPE_TXT_ADMIN']; ?>
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" name='eFareType' id="eFareType" required
                                            onchange="get_faretype(this.value)">
                                        <option value="Fixed"<?
                                        if ($eFareType == "Fixed") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>Fixed
                                        </option>
                                        <option value="Hourly"<?
                                        if ($eFareType == "Hourly") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>Hourly
                                        </option>
                                        <option value="Regular"<?
                                        if ($eFareType == "Regular") {
                                            echo 'selected="selected"';
                                        }
                                        ?>>Time And Distance
                                        </option>
                                    </select>
                                </div>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="eFareType" value="Regular"/>
                        <?php } ?>
                        <div class="row" id="fixed_div" style="display:none;">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl_admin['LBL_FIXED_FARE_TXT_ADMIN']; ?>
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="fFixedFare" id="fFixedFare"
                                       value="<?= $fFixedFare; ?>" onChange="getpriceCheck(this.value)">
                            </div>
                        </div>
                        <div id="Regular_div1">
                            <div class="row" id="hide-km">
                                <div class="col-lg-12">
                                    <label> Price Per
                                        <em id="change_eUnit"
                                            style="font-style: normal"><?= $DEFAULT_DISTANCE_UNIT; ?></em>
                                        (Price In <?= $db_currency[0]['vName'] ?>)
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="fPricePerKM" id="fPricePerKM"
                                           value="<?= $fPricePerKM; ?>">
                                </div>
                            </div>
                            <div class="row" id="hide-price">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_PRICE_MIN_TXT_ADMIN']; ?> (Price
                                        In <?= $db_currency[0]['vName'] ?>)
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="fPricePerMin" id="fPricePerMin"
                                           value="<?= $fPricePerMin; ?>">
                                </div>
                            </div>
                            <div class="row" id="hide-priceHour">
                                <div class="col-lg-12">
                                    <label>Price Per Hour (Price In <?= $db_currency[0]['vName'] ?>)
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="fPricePerHour" id="fPricePerHour"
                                           value="<?= $fPricePerHour; ?>">
                                </div>
                            </div>
                            <div class="row" id="hide-minimumfare">
                                <div class="col-lg-12">
                                    <label>Minimum Fare (Price In <?= $db_currency[0]['vName'] ?>)
                                        <span class="red"> *</span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='The minimum fare is the least amount you have to pay. For eg : if you travel a distance of 1 km  , the actual fare will be $10 (base fare $6 + $2/km + $2/min) assuming that it takes 1 min to travel but still you are liable to pay the minimum fare which is $15 for example.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="iMinFare" id="iMinFare"
                                           value="<?= $iMinFare; ?>">
                                </div>
                            </div>
                            <div class="row" id="hide-basefare">
                                <div class="col-lg-12">
                                    <label> Base Fare (Price In <?= $db_currency[0]['vName'] ?>)
                                        <span class="red"> *</span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='Base fare is the price that the taxi meter will start at a certain point. Let say if you set base fare $3 then the meter will be set at $3 to begin, and not $0.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="iBaseFare" id="iBaseFare"
                                           value="<?= $iBaseFare; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label> Commission (%)
                                    <span class="red"> *</span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='This would be the amount which you are willing to charge from the <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> in form of commission for each ride.'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="fCommision" id="fCommision"
                                       value="<?= $fCommision; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label> <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> Cancellation Time Limit (
                                    in minute )
                                    <span class="red"></span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title="This is the timelimit based on which the <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> would be charged if he/she cancel's the ride after the specified period limit."></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="iCancellationTimeLimit"
                                       id="iCancellationTimeLimit" value="<?= $iCancellationTimeLimit; ?>"
                                       onblur="checkblanktimelimit('iCancellationTimeLimit', 'fCancellationFare');">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label> <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> Cancellation Charges
                                    (Price In <?= $db_currency[0]['vName'] ?>)
                                    <span class="red"></span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='Below mentioned charges would be applied to the <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> when the <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> cancels the ride after the specific period of time.'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="fCancellationFare" id="fCancellationFare"
                                       value="<?= $fCancellationFare; ?>"
                                       onfocus="checkcancellationfare('iCancellationTimeLimit');">
                                <span class="error" id="showerrorlimit"></span>
                            </div>
                        </div>
                        <div class="row" id="iWaitingFeeTimeLimitDiv">
                            <div class="col-lg-12">
                                <label> Waiting Time Limit ( in minute )
                                    <span class="red"></span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title="Waiting charge will be applied if duration exceeds than the defined.e.g.: Let's say that the 'Waiting Time Limit' has set to 5 Minutes. From the app, the '<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>' has marked as arrived and if the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> has to wait for 8 minutes which is more than 5 minutes(Waiting Time Limit) then in that case the <?= strtolower($langage_lbl_admin['LBL_RIDER']); ?> has to pay for the exceeded 3 minutes based on defined 'Waiting Charges' fees."></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="iWaitingFeeTimeLimit"
                                       id="iWaitingFeeTimeLimit"
                                       value="<?= $iWaitingFeeTimeLimit == 0 ? '' : $iWaitingFeeTimeLimit; ?>"
                                       onblur="checkblanktimelimit('iWaitingFeeTimeLimit', 'fWaitingFees');">
                            </div>
                        </div>
                        <div id="fWaitingFeesDiv">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> Waiting Charges (Price In <?= $db_currency[0]['vName'] ?>)
                                        <span class="red"></span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title="The defined charges would be applied to the invoice into the total fare when the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> has to wait for more than the specific defined waiting time prior to starting the <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?>"></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="fWaitingFees" id="fWaitingFees"
                                           value="<?= $fWaitingFees; ?>"
                                           onfocus="checkcancellationfare('iWaitingFeeTimeLimit');">
                                </div>
                            </div>
                            <? if (($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride") && $ENABLE_INTRANSIT_SHOPPING_SYSTEM == "Yes") { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label> InTransit Waiting Fee per minute (Price
                                            In <?= $db_currency[0]['vName'] ?>)
                                            <span class="red"></span>
                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                               data-original-title='The charge defined here will be applied in total fare when <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> has to hold trip as <?= strtolower($langage_lbl_admin['LBL_RIDER']); ?> requests for it and wait for <?= strtolower($langage_lbl_admin['LBL_RIDER']); ?>.'></i>
                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="fTripHoldFees" id="fTripHoldFees"
                                               value="<?= $fTripHoldFees; ?>">

                                        <span class="notes">[ Note: The InTransit Waiting Fee is calculated based on per minute. For example, if the waiting time is 2 minutes and 20 seconds, it will be rounded up to 3 minutes during calculation.]</span>
                                    </div>


                                </div>
                            <? } ?>
                        </div>
                        <div class="row" id="pool_div">
                            <div class="col-lg-12">
                                <label> Pool Percentage
                                    <span class="red"> *</span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='Say the 1st seat cost $10 and if you want to charge lesser on the 2nd seat, say $8, then set the Pool Percentage ratio as ‘80 %’ for the 2nd Seat. So, the 2 seat will not cost $10, but it will be for $8.Thus, 1 Seat Booking Cost = $10 and for 2 Seat Booking Cost = $18'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="number" min="0" max="100" class="form-control" name="fPoolPercentage"
                                       id="fPoolPercentage" value="<?= $fPoolPercentage; ?>">
                            </div>
                            <div id="digit"></div>
                        </div>
                        <?php
                        $enableBufferAmt = 0;
                        if ($SYSTEM_PAYMENT_FLOW == "Method-3" || $SYSTEM_PAYMENT_FLOW == "Method-2") {
                            $enableBufferAmt = 1;
                            if ($SYSTEM_PAYMENT_FLOW == "Method-2" && $APP_TYPE == "Delivery") {
                                $enableBufferAmt = 0;
                            }
                        }
                        if ($enableBufferAmt == 1) {
                            $helfTxtBufferAmt = "This will be used when your payment method is based on `Wallet payment`. When partial payment via cash is not available in your payment method then user needs to top up their wallet. User will be charged as `Estimated Fare + Buffer Amount`. When partial payment via cash is available then user will be charged as `Estimated Fare + Buffer Amount` in specific cases (Like - destination later) only.Note: This will be applied when user selects `Pay by wallet` method.";
                            if ($SYSTEM_PAYMENT_FLOW == "Method-2") {
                                $helfTxtBufferAmt = "This will be used when your payment method is based on `Wallet payment`. When partial payment via cash is not available in your payment method then user needs to top up their wallet. User will be charged as `Estimated Fare + Buffer Amount`. When partial payment via cash is available then user will be charged as `Estimated Fare + Buffer Amount` in specific cases (Like - destination later) only.Note: This will be applied when user selects `Pay by wallet` method.";
                            }
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> Buffer Amount
                                        <span class="red"> *</span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='<?= $helfTxtBufferAmt; ?>'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" min="0" onkeypress="return isNumberKey(event)"
                                           class="form-control" name="fBufferAmount" id="fBufferAmount"
                                           value="<?= $fBufferAmount; ?>" required="">
                                </div>
                            </div>
                        <?php } ?>
                        <div id="Regular_div2">
                            <?php if (in_array($REQUEST_eType, ['Ride','TaxiBid','InterCity'])) { ?>
                            <div class="row" id="Regular_subdiv">
                                    <div class="col-lg-12">
                                        <label> Available Seats/Person capacity
                                            <span class="red"> *</span>
                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                               data-original-title='Mention the total number of seats available in the vehicle for the <?= strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?>.'></i>
                                        </label>
                                    </div>

                                <?php if (in_array($REQUEST_eType, ['Ride','TaxiBid','InterCity'])) { ?>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="number" class="form-control" min="1" required="" name="iPersonSize"
                                               id="iPersonSize" value="<?= $iPersonSize; ?>">
                                        Note : Excluding
                                        the <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>
                                    </div>
                                <?php } ?>
                                <div id="digit"></div>
                            </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Peak Time Surcharge On/Off
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='This is a multiplier X  to the standard fares causing the fare to be higher than the standard fare during certain times the day; i.e. if X is 1.2 during some point of time then the standard fare will be multiplied by 1.2 to get the final fare.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" id="ePickStatus" onChange="showhidepickuptime();"
                                               name="ePickStatus" <?= ($id != '' && $ePickStatus == 'Active') ? 'checked' : ''; ?>/>
                                    </div>
                                </div>
                            </div>
                            <div id="showpickuptime" style="display:none;">
                                <div class="row">
                                    <div class="col-lg-12 main-table001">
                                        <div class="main-table001">
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Monday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tMonPickStartTime" id="tMonPickStartTime"
                                                                   value="<?php
                                                                   if ($tMonPickStartTime != "00:00:00") {
                                                                       echo $tMonPickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tMonPickEndTime" id="tMonPickEndTime"
                                                                   value="<?php
                                                                   if ($tMonPickEndTime != "00:00:00") {
                                                                       echo $tMonPickEndTime;
                                                                   }
                                                                   ?>" placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control surgeChargePrice" name="fMonPickUpPrice"
                                                               id="fMonPickUpPrice" value="<?= $fMonPickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RMonday" id="RMonday" value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Tuesday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tTuePickStartTime" id="tTuePickStartTime"
                                                                   value="<?php
                                                                   if ($tTuePickStartTime != "00:00:00") {
                                                                       echo $tTuePickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tTuePickEndTime" id="tTuePickEndTime"
                                                                   value="<?php
                                                                   if ($tTuePickEndTime != "00:00:00") {
                                                                       echo $tTuePickEndTime;
                                                                   }
                                                                   ?>" placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control surgeChargePrice" name="fTuePickUpPrice"
                                                               id="fTuePickUpPrice" value="<?= $fTuePickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RTuesday" id="RTuesday"
                                                               value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Wednesday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tWedPickStartTime" id="tWedPickStartTime"
                                                                   value="<?php
                                                                   if ($tWedPickStartTime != "00:00:00") {
                                                                       echo $tWedPickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tWedPickEndTime" id="tWedPickEndTime"
                                                                   value="<?php
                                                                   if ($tWedPickEndTime != "00:00:00") {
                                                                       echo $tWedPickEndTime;
                                                                   }
                                                                   ?>" placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control surgeChargePrice" name="fWedPickUpPrice"
                                                               id="fWedPickUpPrice" value="<?= $fWedPickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RWednesday" id="RWednesday"
                                                               value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Thursday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tThuPickStartTime" id="tThuPickStartTime"
                                                                   value="<?php
                                                                   if ($tThuPickStartTime != "00:00:00") {
                                                                       echo $tThuPickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tThuPickEndTime" id="tThuPickEndTime"
                                                                   value="<?php
                                                                   if ($tThuPickEndTime != "00:00:00") {
                                                                       echo $tThuPickEndTime;
                                                                   }
                                                                   ?>" placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control surgeChargePrice" name="fThuPickUpPrice"
                                                               id="fThuPickUpPrice" value="<?= $fThuPickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RThursday" id="RThursday"
                                                               value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Friday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tFriPickStartTime" id="tFriPickStartTime"
                                                                   value="<?php
                                                                   if ($tFriPickStartTime != "00:00:00") {
                                                                       echo $tFriPickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tFriPickEndTime" id="tFriPickEndTime"
                                                                   value="<?php
                                                                   if ($tFriPickEndTime != "00:00:00") {
                                                                       echo $tFriPickEndTime;
                                                                   }
                                                                   ?>" placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control surgeChargePrice" name="fFriPickUpPrice"
                                                               id="fFriPickUpPrice" value="<?= $fFriPickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RFriday" id="RFriday" value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Saturday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tSatPickStartTime" id="tSatPickStartTime"
                                                                   value="<?php
                                                                   if ($tSatPickStartTime != "00:00:00") {
                                                                       echo $tSatPickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tSatPickEndTime" id="tSatPickEndTime"
                                                                   value="<?php
                                                                   if ($tSatPickEndTime != "00:00:00") {
                                                                       echo $tSatPickEndTime;
                                                                   }
                                                                   ?>" placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control surgeChargePrice" name="fSatPickUpPrice"
                                                               id="fSatPickUpPrice" value="<?= $fSatPickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RSaturday" id="RSaturday"
                                                               value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                            <table class="col-lg-2">
                                                <tr>
                                                    <td align="center">
                                                        <b>Sunday</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Start Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tSunPickStartTime" id="tSunPickStartTime"
                                                                   value="<?php
                                                                   if ($tSunPickStartTime != "00:00:00") {
                                                                       echo $tSunPickStartTime;
                                                                   }
                                                                   ?>" placeholder="Pickup Start Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> End Time</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="input-group clockpicker-with-callbacks">
                                                            <input type="text" class="form-control"
                                                                   name="tSunPickEndTime" id="tSunPickEndTime"
                                                                   value="<?= $tSunPickEndTime; ?>"
                                                                   placeholder="Pickup End Time" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td> Price</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input  type="text" class="form-control surgeChargePrice" name="fSunPickUpPrice"
                                                               id="fSunPickUpPrice" value="<?= $fSunPickUpPrice; ?>"
                                                               placeholder="Enter Price">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="button" name="RSunday" id="RSunday" value="reset"/>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> Night Charges On/Off
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='This is a multiplier X  to the standard fares causing the fare to be higher than the standard fare during night time; i.e. if X is 1.2 during some point of time then the standard fare will be multiplied by 1.2 to get the final fare.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" id="eNightStatus" onChange="showhidenighttime();"
                                               name="eNightStatus" <?= ($id != '' && $eNightStatus == 'Active') ? 'checked' : ''; ?>/>
                                    </div>
                                </div>
                            </div>
                            <!--Added By Hasmukh On 10-10-2018 For Store Night Charges Details In Json Start !-->
                            <div id="shownighttime" style="display:none;">
                                <div class="row">
                                    <div class="col-lg-12 main-table001">
                                        <div class="main-table001">
                                            <?php
                                            foreach ($weekDaysArr as $dayKey => $dayVal) {
                                                $dayStartId = "t" . $dayVal . "NightStartTime";
                                                $dayEndId = "t" . $dayVal . "NightEndTime";
                                                $priceId = "f" . $dayVal . "NightPrice";
                                                ?>
                                                <table class="col-lg-2">
                                                    <tr>
                                                        <td align="center">
                                                            <b><?= $dayKey; ?></b>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td> Start Time</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="input-group clockpicker-with-callbacks">
                                                                <input type="text" class="form-control"
                                                                       name="<?= $dayStartId; ?>"
                                                                       id="<?= $dayStartId; ?>" value="<?
                                                                if (isset($nightSurgeDataArr[$dayStartId])) {
                                                                    echo $nightSurgeDataArr[$dayStartId];
                                                                }
                                                                ?>" placeholder="Night Start Time">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td> End Time</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="input-group clockpicker-with-callbacks">
                                                                <input type="text" class="form-control"
                                                                       name="<?= $dayEndId; ?>" id="<?= $dayEndId; ?>"
                                                                       value="<?
                                                                       if (isset($nightSurgeDataArr[$dayEndId])) {
                                                                           echo $nightSurgeDataArr[$dayEndId];
                                                                       }
                                                                       ?>" placeholder="Night End Time">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td> Price</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control surgeChargePrice"
                                                                   name="<?= $priceId; ?>" id="<?= $priceId; ?>"
                                                                   value="<?
                                                                   if (isset($nightSurgeDataArr[$priceId])) {
                                                                       echo $nightSurgeDataArr[$priceId];
                                                                   }
                                                                   ?>" placeholder="Enter Price">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input type="button" name="RNight<?= $dayKey ?>"
                                                                   id="RNight<?= $dayKey ?>" value="reset"/>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--Added By Hasmukh On 10-10-2018 For Store Night Charges Details In Json End !-->
                            <!--<div id="shownighttime" style="display:none;">

                                        <div class="row">

                                            <div class="col-lg-12">

                                                <label> Night Charges Start Time</label>

                                            </div>

                                            <div class="col-md-6 col-sm-6">

                                                <input type="text" readonly class=" form-control" name="tNightStartTime"  id="tNightStartTime" value="<?= $tNightStartTime; ?>" placeholder="Select Night Start Time"  >

                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-lg-12">

                                                <label> Night Charges End Time</label>

                                            </div>

                                            <div class="col-md-6 col-sm-6">

                                                <input type="text" readonly class=" form-control" name="tNightEndTime"  id="tNightEndTime" value="<?= $tNightEndTime; ?>" placeholder="Select Night End Time" >

                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-lg-12">

                                                <label> Night Time Surcharge (X)</label>

                                            </div>

                                            <div class="col-md-6 col-sm-6">

                                                <input type="text" class="form-control" name="fNightPrice"  id="fNightPrice" value="<?= $fNightPrice; ?>" placeholder="Enter Price" >



                                            </div>

                                        </div>

                                    </div> -->
                            <?php //}                ?>
                        </div>
                        <?php if ($APP_TYPE != 'UberX') { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> Picture
                                        (Non-selected image) <span class="red"> *</span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='This is used to represent the vehicle type as a icon in application.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <?
                                    $rand = rand(1000, 9999);
                                    if (!empty($vLogo) && file_exists($tconfig['tsite_upload_images_vehicle_type_path'] . "/" . $id . "/ios/3x_" . $vLogo) ) {
                                        ?>
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images_vehicle_type'] . "/" . $id . "/ios/3x_" . $vLogo; ?>">
                                    <? } ?>
                                    <input type="file" class="form-control" name="vLogo" id="vLogo" placeholder=""
                                           style="padding-bottom: 4%; height:5%;" <?php if (empty($vLogo)) { ?> required <?php } ?>>
                                    <span class="notes">[Note: Recommended dimension for Upload image(.png) is 360px X 360px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> Picture (Selected
                                        image) <span class="red"> *</span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='This is used to represent the vehicle type as a icon in application. This icon is used to represent the vehicle type as a selected.'></i>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <? if (!empty($vLogo1) && file_exists($tconfig['tsite_upload_images_vehicle_type_path'] . "/" . $id . "/ios/3x_" . $vLogo1) ) { ?>
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images_vehicle_type'] . "/" . $id . "/ios/3x_" . $vLogo1; ?>">
                                    <? } ?>
                                    <input type="file" class="form-control" name="vLogo1" id="vLogo1" placeholder=""
                                           style="padding-bottom: 4%; height: 5%;" <?php if (empty($vLogo1)) { ?> required <?php } ?>>
                                    <span class="notes">[Note: Recommended dimension for Upload image(.png) is 360px X 360px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]</span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($APP_TYPE == 'UberX') { ?>
                            <div id="show-in-fixed">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Allow Quantity
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <select class="form-control" name='eAllowQty' id="AllowQty"
                                                onchange="get_AllowQty(this.value)">
                                            <option value="Yes"<?
                                            if ($eAllowQty == "Yes") {
                                                echo 'selected="selected"';
                                            }
                                            ?>>Yes
                                            </option>
                                            <option value="No"<?
                                            if ($eAllowQty == "No") {
                                                echo 'selected="selected"';
                                            }
                                            ?>>No
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" id="iMaxQty-div">
                                    <div class="col-lg-12">
                                        <label>Maximum Quantity
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="iMaxQty" id="iMaxQty"
                                               value="<?= $iMaxQty; ?>" onchange="getpriceCheck(this.value)">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div id="price" style="margin: 10px;"></div>
                        <br/>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Order</label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <!--<input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">-->
                                <input type="hidden" name="temp_order" id="temp_order"
                                       value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">
                                <?php
                               // $display_numbers = ($action == "Add") ? $iDisplayOrder_max : $iDisplayOrder_db;
                                $display_numbers = $iDisplayOrder_max;
                                ?>
                                <select name="iDisplayOrder" class="form-control">
                                    <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                        <option value="<?= $i ?>" <?
                                        if (isset($iDisplayOrder_db) && $i == $iDisplayOrder_db) {
                                            echo "selected";
                                        }
                                        ?>> -- <?= $i ?> --
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php
                                if (($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' && $userObj->hasPermission($create))) { ?>
                                    <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit"
                                           value="<?php if ($action == 'Add') { ?><?= $action; ?> Vehicle Type<?php } else { ?>Update<?php } ?>">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <!-- <a href="javascript:void(0);" onclick="reset_form('_vehicleType_form');" class="btn btn-default">Reset</a> -->
                                <a href="vehicle_type.php<?= $queryString; ?>" class="btn btn-default back_link">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<?php
//include_once('footer_vehicleType.php');
include_once('footer.php');
?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Js-->
<script type="text/javascript" src="js/bootstrap-clockpicker.min.js"></script>
<!--Added By Hasmukh On 11-10-2018 For Clock Time Picker End Js -->
<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
<script src="../assets/plugins/ckeditor/config.js"></script>
<!--For Faretype-->
<script>
    document.getElementById('RMonday').onclick = function () {
        var tMonPickStartTime = document.getElementById('tMonPickStartTime');
        var tMonPickEndTime = document.getElementById('tMonPickEndTime');
        var fMonPickUpPrice = document.getElementById('fMonPickUpPrice');
        tMonPickStartTime.value = tMonPickEndTime.value = fMonPickUpPrice.value = '';
        //fMonPickUpPrice.value= fMonPickUpPrice.defaultValue;
    };
    document.getElementById('RTuesday').onclick = function () {
        var tTuePickStartTime = document.getElementById('tTuePickStartTime');
        var tTuePickEndTime = document.getElementById('tTuePickEndTime');
        var fTuePickUpPrice = document.getElementById('fTuePickUpPrice');
        tTuePickStartTime.value = tTuePickEndTime.value = fTuePickUpPrice.value = '';
        //fTuePickUpPrice.value= fTuePickUpPrice.defaultValue;
    };
    document.getElementById('RWednesday').onclick = function () {
        var tWedPickStartTime = document.getElementById('tWedPickStartTime');
        var tWedPickEndTime = document.getElementById('tWedPickEndTime');
        var fWedPickUpPrice = document.getElementById('fWedPickUpPrice');
        tWedPickStartTime.value = tWedPickEndTime.value = fWedPickUpPrice.value = '';
        //fWedPickUpPrice.value= fWedPickUpPrice.defaultValue;
    };
    document.getElementById('RThursday').onclick = function () {
        var tThuPickStartTime = document.getElementById('tThuPickStartTime');
        var tThuPickEndTime = document.getElementById('tThuPickEndTime');
        var fThuPickUpPrice = document.getElementById('fThuPickUpPrice');
        tThuPickStartTime.value = tThuPickEndTime.value = fThuPickUpPrice.value = '';
        //fThuPickUpPrice.value= fThuPickUpPrice.defaultValue;
    };
    document.getElementById('RFriday').onclick = function () {
        var tFriPickStartTime = document.getElementById('tFriPickStartTime');
        var tFriPickEndTime = document.getElementById('tFriPickEndTime');
        var fFriPickUpPrice = document.getElementById('fFriPickUpPrice');
        tFriPickStartTime.value = tFriPickEndTime.value = fFriPickUpPrice.value = '';
        //fFriPickUpPrice.value= fFriPickUpPrice.defaultValue;
    };
    document.getElementById('RSaturday').onclick = function () {
        var tSatPickStartTime = document.getElementById('tSatPickStartTime');
        var tSatPickEndTime = document.getElementById('tSatPickEndTime');
        var fSatPickUpPrice = document.getElementById('fSatPickUpPrice');
        tSatPickStartTime.value = tSatPickEndTime.value = fSatPickUpPrice.value = '';
        //fSatPickUpPrice.value= fSatPickUpPrice.defaultValue;
    };
    document.getElementById('RSunday').onclick = function () {
        var tSunPickStartTime = document.getElementById('tSunPickStartTime');
        var tSunPickEndTime = document.getElementById('tSunPickEndTime');
        var fSunPickUpPrice = document.getElementById('fSunPickUpPrice');
        tSunPickStartTime.value = tSunPickEndTime.value = fSunPickUpPrice.value = '';
        //fSunPickUpPrice.value= fSunPickUpPrice.defaultValue;
    };
    $.validator.addMethod('minStrict', function (value, el, param) {

        var eNightStatus = $('input[name="eNightStatus"]:checked').val()

        if(eNightStatus == "on"){
            return this.optional(el) || value > param;
        }else{
            return  true;
        }

    }, "please enter more than 1");

    $.validator.addMethod('minStrictForPick', function (value, el, param) {

        var ePickStatus = $('input[name="ePickStatus"]:checked').val()

        if(ePickStatus == "on")
        {
            return this.optional(el) || value > param;
        }else{
            return  true;
        }

    }, "please enter more than 1");


    var myVar;
    // just for the demos, avoids form submit
    if (_system_script == '<?= $script?>') {
        if ($('#_vehicleType_form').length !== 0) {
            var minPersonSize = 1;
            if ($('input[name=ePoolStatus]').is(':checked')) {
                var minPersonSize = 2;
            }
           
            $("#_vehicleType_form").validate({
               // ignore: 'input[type=hidden]',
                ignore: ':hidden',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {

                    if( $(e).hasClass('surgeChargePrice')){
                        $(e).after(error);
                    }else{
                        e.parents('.row > div').append(error);
                    }


                },
                highlight: function (e) {

                    if( $(e).hasClass('surgeChargePrice')){
                        $(e).removeClass('has-success has-error').addClass('has-error');
                        $(e).closest('.help-block').remove();

                    }else{
                        $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                        $(e).closest('.help-block').remove();
                    }


                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    fPricePerKM: {
                        number: true,
                        min: 0
                    },
                    fPricePerMin: {
                        number: true,
                        min: 0
                    },
                    fPricePerHour: {
                        number: true,
                        min: 0
                    },
                    iMinFare: {
                        number: true,
                        min: 0
                    },
                    iBaseFare: {
                        number: true,
                        min: 0
                    },
                    fCommision: {
                        number: true,
                        min: 0
                    },
                    iCancellationTimeLimit: {
                        number: true,
                        min: 1
                    },
                    fCancellationFare: {
                        number: true,
                        min: 0
                    },
                    iWaitingFeeTimeLimit: {
                        number: true,
                        min: 1
                    },
                    fWaitingFees: {
                        number: true,
                        min: 0
                    },
                    fTripHoldFees: {
                        number: true,
                        min: 0
                    },
                    fMonPickUpPrice: {
                        required: function () {
                            var tMonPickStartTime = $("#tMonPickStartTime").val();
                            var tMonPickEndTime = $("#tMonPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tMonPickStartTime != '' && tMonPickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tMonPickStartTime = $("#tMonPickStartTime").val();
                            var tMonPickEndTime = $("#tMonPickEndTime").val();

                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            return ePickStatus == "on" && tMonPickStartTime !== '' && tMonPickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fTuePickUpPrice: {
                        required: function () {
                            var tTuePickStartTime = $("#tTuePickStartTime").val();
                            var tTuePickEndTime = $("#tTuePickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tTuePickStartTime != '' && tTuePickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tTuePickStartTime = $("#tTuePickStartTime").val();
                            var tTuePickEndTime = $("#tTuePickEndTime").val();
                            return tTuePickStartTime !== '' && tTuePickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fWedPickUpPrice: {
                        required: function () {
                            var tWedPickStartTime = $("#tWedPickStartTime").val();
                            var tWedPickEndTime = $("#tWedPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tWedPickStartTime != '' && tWedPickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tWedPickStartTime = $("#tWedPickStartTime").val();
                            var tWedPickEndTime = $("#tWedPickEndTime").val();
                            return tWedPickStartTime !== '' && tWedPickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fThuPickUpPrice: {
                        required: function () {
                            var tThuPickStartTime = $("#tThuPickStartTime").val();
                            var tThuPickEndTime = $("#tThuPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tThuPickStartTime != '' && tThuPickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tThuPickStartTime = $("#tThuPickStartTime").val();
                            var tThuPickEndTime = $("#tThuPickEndTime").val();
                            return tThuPickStartTime !== '' && tThuPickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fFriPickUpPrice: {
                        required: function () {
                            var tFriPickStartTime = $("#tFriPickStartTime").val();
                            var tFriPickEndTime = $("#tFriPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tFriPickStartTime != '' && tFriPickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tFriPickStartTime = $("#tFriPickStartTime").val();
                            var tFriPickEndTime = $("#tFriPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            return ePickStatus == "on" && tFriPickStartTime !== '' && tFriPickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fSatPickUpPrice: {
                        required: function () {
                            var tSatPickStartTime = $("#tSatPickStartTime").val();
                            var tSatPickEndTime = $("#tSatPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tSatPickStartTime != '' && tSatPickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tSatPickStartTime = $("#tSatPickStartTime").val();
                            var tSatPickEndTime = $("#tSatPickEndTime").val();
                            return tSatPickStartTime !== '' && tSatPickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fSunPickUpPrice: {
                        required: function () {
                            var tSunPickStartTime = $("#tSunPickStartTime").val();
                            var tSunPickEndTime = $("#tSunPickEndTime").val();
                            var ePickStatus = $('input[name="ePickStatus"]:checked').val();
                            if (ePickStatus == "on" && tSunPickStartTime != '' && tSunPickEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrictForPick: function () {
                            var tSunPickStartTime = $("#tSunPickStartTime").val();
                            var tSunPickEndTime = $("#tSunPickEndTime").val();
                            return tSunPickStartTime !== '' && tSunPickEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fMonNightPrice: {
                        required: function () {
                            var tMonNightStartTime = $("#tMonNightStartTime").val();
                            var tMonNightEndTime = $("#tMonNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tMonNightStartTime != '' && tMonNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tMonNightStartTime = $("#tMonNightStartTime").val();
                            var tMonNightEndTime = $("#tMonNightEndTime").val();
                            return tMonNightStartTime !== '' && tMonNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fTueNightPrice: {
                        required: function () {
                            var tTueNightStartTime = $("#tTueNightStartTime").val();
                            var tTueNightEndTime = $("#tTueNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tTueNightStartTime != '' && tTueNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tTueNightStartTime = $("#tTueNightStartTime").val();
                            var tTueNightEndTime = $("#tTueNightEndTime").val();
                            return tTueNightStartTime !== '' && tTueNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fWedNightPrice: {
                        required: function () {
                            var tWedNightStartTime = $("#tWedNightStartTime").val();
                            var tWedNightEndTime = $("#tWedNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tWedNightStartTime != '' && tWedNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tWedNightStartTime = $("#tWedNightStartTime").val();
                            var tWedNightEndTime = $("#tWedNightEndTime").val();
                            return tWedNightStartTime !== '' && tWedNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fThuNightPrice: {
                        required: function () {
                            var tThuNightStartTime = $("#tThuNightStartTime").val();
                            var tThuNightEndTime = $("#tThuNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tThuNightStartTime != '' && tThuNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tThuNightStartTime = $("#tThuNightStartTime").val();
                            var tThuNightEndTime = $("#tThuNightEndTime").val();
                            return tThuNightStartTime !== '' && tThuNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fFriNightPrice: {
                        required: function () {
                            var tFriNightStartTime = $("#tFriNightStartTime").val();
                            var tFriNightEndTime = $("#tFriNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tFriNightStartTime != '' && tFriNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tFriNightStartTime = $("#tFriNightStartTime").val();
                            var tFriNightEndTime = $("#tFriNightEndTime").val();

                            return tFriNightStartTime !== '' && tFriNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fSatNightPrice: {
                        required: function () {
                            var tSatNightStartTime = $("#tSatNightStartTime").val();
                            var tSatNightEndTime = $("#tSatNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tSatNightStartTime != '' && tSatNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tSatNightStartTime = $("#tSatNightStartTime").val();
                            var tSatNightEndTime = $("#tSatNightEndTime").val();
                            return tSatNightStartTime !== '' && tSatNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    fSunNightPrice: {
                        required: function () {
                            var tSunNightStartTime = $("#tSunNightStartTime").val();
                            var tSunNightEndTime = $("#tSunNightEndTime").val();
                            var eNightStatus = $('input[name="eNightStatus"]:checked').val();
                            if (eNightStatus == "on" && tSunNightStartTime != '' && tSunNightEndTime != '') {
                                return true;
                            } else {
                                return false;
                            }
                        },
                        number: true,
                        minStrict: function () {
                            var tSunNightStartTime = $("#tSunNightStartTime").val();
                            var tSunNightEndTime = $("#tSunNightEndTime").val();
                            return tSunNightStartTime !== '' && tSunNightEndTime !== '' ? 1 : false;
                        },
                        maxlength: 4
                    },
                    iPersonSize: {
                        digits: true,
                        min: minPersonSize,
                    },
                    vLogo: {
                        extension: imageUploadingExtenstionjsrule
                    },
                    vLogo1: {
                        extension: imageUploadingExtenstionjsrule
                    }

                },
                 messages: {
                    vLogo:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vLogo1:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
                submitHandler: function (form) {
                    var DELIVERY_HELPER_ENABLE = "<?= $enableDeliveryHelper ?>";
                    if (DELIVERY_HELPER_ENABLE == "Yes") {
                        if ($('#eDeliveryHelper').prop('checked') == true) {
                            if ($('#tDeliveryHelperNoteDriver_Default').length > 0) {
                                var error_desc = 0;
                                var DescDefaultLength = CKEDITOR.instances['tDeliveryHelperNoteDriver_Default'].getData().replace(/<[^>]*>/gi, '').length;
                                //alert(DescDefaultLength);
                                if (!DescDefaultLength && DescDefaultLength == 0) {
                                    $('#tDeliveryHelperNoteDriver_Default_error').show();
                                    $('html, body').animate({
                                        scrollTop: $("#deliveryHelperNote").offset().top
                                    }, 0);
                                    clearInterval(myVar);
                                    myVar = setTimeout(function () {
                                        $('#tDeliveryHelperNoteDriver_Default_error').hide();
                                    }, 5000);
                                    error_desc = 1;
                                }
                                var DescDefaultLength1 = CKEDITOR.instances['tDeliveryHelperNoteUser_Default'].getData().replace(/<[^>]*>/gi, '').length;
                                if (!DescDefaultLength1 && DescDefaultLength1 == 0) {
                                    $('#tDeliveryHelperNoteUser_Default_error').show();
                                    $('html, body').animate({
                                        scrollTop: $("#deliveryHelperNote").offset().top
                                    }, 0);
                                    clearInterval(myVar);
                                    myVar = setTimeout(function () {
                                        $('#tDeliveryHelperNoteUser_Default_error').hide();
                                    }, 5000);
                                    error_desc = 1;
                                }
                                if (error_desc == 1)
                                    return false;
                            } else {
                                var DescDefaultLength = CKEDITOR.instances['tDeliveryHelperNoteDriver_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
                                if (!DescDefaultLength) {
                                    $('#tDeliveryHelperNoteDriver_<?= $default_lang ?>_error').show();
                                    $('html, body').animate({
                                        scrollTop: $("#eDeliveryHelper").offset().top
                                    }, 0);
                                    clearInterval(myVar);
                                    myVar = setTimeout(function () {
                                        $('#tDeliveryHelperNoteDriver_<?= $default_lang ?>_error').hide();
                                    }, 5000);
                                    error_desc = 1;
                                }
                                var DescDefaultLength1 = CKEDITOR.instances['tDeliveryHelperNoteUser_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
                                if (!DescDefaultLength1) {
                                    $('#tDeliveryHelperNoteUser_<?= $default_lang ?>_error').show();
                                    $('html, body').animate({
                                        scrollTop: $("#deliveryHelperNote").offset().top
                                    }, 0);
                                    clearInterval(myVar);
                                    myVar = setTimeout(function () {
                                        $('#tDeliveryHelperNoteUser_<?= $default_lang ?>_error').hide();
                                    }, 5000);
                                    error_desc = 1;
                                }
                                if (error_desc == 1)
                                    return false;
                            }
                        }
                    }
                    ShpSq6fAm7(form);
                    form.submit();
                }

            });
        }
    }
    jQuery.extend(jQuery.validator.messages, {
        number: "Please enter a valid number.",
        min: jQuery.validator.format("Please enter a value greater than 0.")
    });

    $('[data-toggle="tooltip"]').tooltip();
    function hideShowFareCalcModel($value , A = 0)
    {

        document.getElementById("eFareCalcModel").disabled = $value;
        if (A == 0) {
            setTimeout(() => {
                document.getElementById("eFareCalcModel").disabled = $value;
            }, "1500");
        }

    }
    window.onload = function () {
        var vid = $("#vid").val();
        var eFareType = $("#eFareType").val();
        var AllowQty = $("#AllowQty").val();
        if (vid == '') {
            get_faretype('Regular');
        } else {
            get_faretype(eFareType);
        }
        if (AllowQty == 'Yes') {
            $("#iMaxQty-div").show();
            $("#iMaxQty").attr('required', 'required');
        } else {
            $("#iMaxQty-div").hide();
            $("#iMaxQty").removeAttr('required');
        }
        var AppTypenew = '<?= $APP_TYPE; ?>';
        var editTypeId = '<?= $id; ?>';
        if (AppTypenew == 'Ride-Delivery-UberX') {
            var appTYpe = '<?= $app_type_service; ?>';
        } else {
            var appTYpe = '<?= $APP_TYPE; ?>';
        }
        /*appTYpe == 'UberX' && eFareType == 'Regular'*/
        if (appTYpe == 'UberX' && eFareType == 'Regular') {
            $("#Regular_div2,#Regular_div1").show();
        } else if (appTYpe == 'Ride' || appTYpe == 'Delivery' || appTYpe == 'Ride-Delivery') {
            $("#Regular_div2,#Regular_div1").show();
        } else {
            $("#Regular_div2").hide();
            $("#Regular_div1").show();
        }
        var aapTypeCheck = '<?= $eType; ?>';
        if (editTypeId == "") {
            aapTypeCheck = "Ride";
        }
        var POOL_ENABLE = '<?= $POOL_ENABLE; ?>';
        var poolStatus = '<?= $ePoolStatus; ?>';
        var eInterCityStatus = '<?= $eInterCityStatus; ?>';
        var ENABLE_FARE_MODEL_STRATEGY = '<?= $vehicleFareModelStrategy; ?>';
        var DELIVERY_HELPER_ENABLE = '<?= $enableDeliveryHelper ?>';
        var OTP_RIDE_ENABLE = '<?= $enableOTPVerificationRide ?>';
        var OTP_DELIVERY_ENABLE = '<?= $enableOTPVerificationDelivery ?>';

        if (POOL_ENABLE == "Yes" && (appTYpe == 'Ride' || appTYpe == 'Ride-Delivery') && (aapTypeCheck == 'Ride' || aapTypeCheck == 'Ride-Delivery')) {
            $("#poolenable,#pool_div").show();
            $("#fPoolPercentage").prop('required', true);
            if ($('input[name=ePoolStatus]').is(':checked')) {
                $("#pool_div").show();
                $("#fPoolPercentage").prop('required', true);
                $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
            }
        } else {
            $("#poolenable,#pool_div").hide();
            $("#fPoolPercentage").prop('required', false);
            $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
        }
        if (ENABLE_FARE_MODEL_STRATEGY == "Yes" && (appTYpe == 'Ride' || appTYpe == 'Ride-Delivery') && (aapTypeCheck == 'Ride' || aapTypeCheck == 'Ride-Delivery')) {
            $("#farestrategymodel").show();
        } else {
            $("#farestrategymodel").hide();
        }
        if ($('input[name=ePoolStatus]').is(':checked') && POOL_ENABLE == "Yes" && (appTYpe == 'Ride' || appTYpe == 'Ride-Delivery') && (aapTypeCheck == 'Ride' || aapTypeCheck == 'Ride-Delivery')) {
            $("#pool_div").show();
            $("#fPoolPercentage").prop('required', true);
            $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
        } else {
            $("#pool_div").hide();
            $("#fPoolPercentage").prop('required', false);
            $("#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
        }
        if (appTYpe == 'Delivery') {
            $("#Regular_subdiv,#farestrategymodel").hide();
            $(".RentalAlias").hide();
        } else if (appTYpe == 'Ride-Delivery') {
            var eTypedeliverval = $('#etypedelivery').val();

            <?php if($action == "Add") { ?>
                var vType = $('#etypedelivery').find(':selected').data('vtype');
                var vType = $('#etypedelivery').data('vtype');
            <?php } else { ?>
                var vType = $('#etypedelivery').data('vtype');
            <?php } ?>

            if (eTypedeliverval == 'Deliver') {
                $("#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#farestrategymodel, #rentalOption").hide();
                $(".RentalAlias").hide();
                if (DELIVERY_HELPER_ENABLE == "Yes") {
                    $('#deliveryHelper').show();
                }
            } else {
                $("#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#farestrategymodel, #rentalOption").show();
                $(".RentalAlias").show();
                if (DELIVERY_HELPER_ENABLE == "Yes") {
                    $('#deliveryHelper').hide();
                }
                if (vType == "Ambulance") {
                    $("#rentalOption, #poolenable").hide();
                }
            }
            $('#etypedelivery').on('change', function () {
                eTypedeliver = this.value;
                vType = $(this).find(':selected').data('vtype');
                if (eTypedeliver == 'Deliver') {
                    $("#Regular_subdiv, #rentalOption").hide();
                    $("#poolenable,#pool_div,#farestrategymodel").hide();
                    $("#fPoolPercentage").prop('required', false);
                    $(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                    $("#MotoBike,#Cycle,#Truck,#VehicleIconType").show();
                    if (DELIVERY_HELPER_ENABLE == "Yes") {
                        $('#deliveryHelper').show();
                    }
                    if (OTP_DELIVERY_ENABLE == "Yes") {
                        $('#OTPVerification').show();
                    } else {
                        $('#OTPVerification').hide();
                        $('#OTPVerification').find('.switch-animate').removeClass('switch-on switch-off').addClass('switch-off');
                        $('#eOTPCodeEnable').prop('checked', false);
                    }
                    if ($('#otpTitleDelivery').length > 0) {
                        $('#otpTitleDelivery').show();
                        $('#otpTitleRide').hide();
                    }
                    $("#eIconType").prop("selectedIndex", 0);
                } else {
                    $("#Regular_subdiv,#farestrategymodel, #rentalOption").show();
                    $(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").show();
                    if (POOL_ENABLE == "Yes") {
                        $("#poolenable").show();
                        if ($('input[name=ePoolStatus]').is(':checked')) {
                            $("#pool_div").show();
                            $("#fPoolPercentage").prop('required', true);
                            $(".RentalAlias,#rentalOption").hide();
                            $("#MotoBike,#Cycle,#Truck,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv").hide();
                        }
                    }
                    if (DELIVERY_HELPER_ENABLE == "Yes") {
                        $('#deliveryHelper').hide();
                    }
                    if (OTP_RIDE_ENABLE == "Yes") {
                        $('#OTPVerification').show();
                    } else {
                        $('#OTPVerification').hide();
                        $('#OTPVerification').find('.switch-animate').removeClass('switch-on switch-off').addClass('switch-off');
                        $('#eOTPCodeEnable').prop('checked', false);
                    }
                    if ($('#otpTitleDelivery').length > 0) {
                        $('#otpTitleDelivery').hide();
                        $('#otpTitleRide').show();
                    }
                    if (vType == "Ambulance") {
                        $('#eIconType').val('Ambulance');
                        $('#ePoolStatus').prop('checked', false);
                        $('#ePoolStatus').closest('.switch-animate').removeClass('switch-on').addClass('switch-off');
                        $("#rentalOption, #VehicleIconType, #poolenable").hide();
                    } else {
                        $('#eIconType').val('Car');
                        $("#rentalOption, #VehicleIconType, #poolenable").show();
                    }
                }
            });
            if (DELIVERY_HELPER_ENABLE == "Yes") {
                var eDeliveryHelper = $('[name="eDeliveryHelper"]:checked').val();
                if (eDeliveryHelper == "Yes") {
                    $('#deliveryHelperNote').show();
                } else {
                    $('#deliveryHelperNote').hide();
                }
                $('#eDeliveryHelper').on('change', function () {
                    if ($(this).prop('checked') == true) {
                        $('#deliveryHelperNote').show();
                    } else {
                        $('#deliveryHelperNote').hide();
                    }
                });
            }
        } else {
            $(".RentalAlias,#Regular_subdiv,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#rentalOption").show();
        }
        if (poolStatus == "Yes") {
            $(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#rentalOption").hide();
            hideShowFareCalcModel('disabled');
        }

        if(eInterCityStatus == 'Yes'){
            $(".RentalAlias,#rentalOption").hide();
        }
    };
    var successMSG1 = '<?= $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }

    function get_faretype(val) {
        var AppTypenew = '<?= $APP_TYPE; ?>';
        if (AppTypenew == 'Ride-Delivery-UberX') {
            var appTYpe = '<?= $app_type_service; ?>';
        } else {
            var appTYpe = '<?= $APP_TYPE; ?>';
        }
        //var appTYpe = '<?= $APP_TYPE; ?>';
        if (appTYpe == 'UberX') {
            if (val == "Fixed") {
                $("#fixed_div").show();
                $("#Regular_div1").hide();
                $("#Regular_div2").hide();
                $("#hide-priceHour").hide();
                $("#hide-basefare").hide();
                $("#hide-minimumfare").hide();
                $("#hide-price").hide();
                $("#hide-km").hide();
                $("#show-in-fixed").show();
                $("#fFixedFare").attr('required', 'required');
                $("#iMaxQty").attr('required', 'required');
                $("#fPricePerKM").removeAttr('required');
                $("#fPricePerMin").removeAttr('required');
                $("#iBaseFare").removeAttr('required');
                $("#iPersonSize").removeAttr('required');
                $("#fPickUpPrice").removeAttr('required');
                $("#tPickStartTime").removeAttr('required');
                $("#tPickEndTime").removeAttr('required');
                $("#tNightStartTime").removeAttr('required');
                $("#tNightEndTime").removeAttr('required');
                $("#fPricePerHour").removeAttr('required');
                $("#iMinFare").removeAttr('required');
                //$("#fVisitFee_div").show();
                //$("#fVisitFee").attr('required', 'required');
            } else if (val == "Regular") {
                $("#fixed_div").hide();
                $("#Regular_div2").show();
                $("#Regular_div1").show();
                $("#show-in-fixed").hide();
                $("#hide-priceHour").hide();
                $("#hide-km").show();
                $("#hide-basefare").show();
                $("#hide-minimumfare").show();
                $("#hide-price").show();
                $("#fPricePerHour").removeAttr('required');
                $("#iMaxQty").removeAttr('required');
                $("#fFixedFare").removeAttr('required');
                $("#fPricePerKM").attr('required', 'required');
                $("#iMinFare").attr('required', 'required');
                $("#fPricePerMin").attr('required', 'required');
                $("#iBaseFare").attr('required', 'required');
                $("#iPersonSize").attr('required', 'required');
                $("#fPickUpPrice").attr('required', 'required');
                $("#tPickStartTime").attr('required', 'required');
                $("#tPickEndTime").attr('required', 'required');
                $("#tNightStartTime").attr('required', 'required');
                $("#tNightEndTime").attr('required', 'required');
                //$("#fVisitFee_div").hide();
                //$("#fVisitFee").removeAttr('required');
            } else {
                $("#fixed_div").hide();
                $("#Regular_div1").show();
                $("#Regular_div2").hide();
                $("#hide-basefare").hide();
                $("#hide-minimumfare").hide();
                $("#hide-price").hide();
                $("#hide-km").hide();
                $("#hide-priceHour").show();
                $("#show-in-fixed").hide();
                $("#fFixedFare").removeAttr('required');
                $("#iMaxQty").removeAttr('required');
                $("#iMinFare").removeAttr('required');
                $("#fPricePerHour").attr('required', 'required');
                //$("#fVisitFee_div").hide();
                //$("#fVisitFee").removeAttr('required');
                /* $("#fPricePerKM").attr('required','required');

             $("#fPricePerMin").attr('required','required');

             $("#iBaseFare").attr('required','required');

             $("#iPersonSize").attr('required','required');

             $("#fPickUpPrice").attr('required','required');

             $("#tPickStartTime").attr('required','required');

             $("#tPickEndTime").attr('required','required');

             $("#tNightStartTime").attr('required','required');

             $("#tNightEndTime").attr('required','required'); */
                $("#iBaseFare").removeAttr('required');
                $("#fPricePerKM").removeAttr('required');
                $("#fPricePerMin").removeAttr('required');
                $("#iPersonSize").removeAttr('required');
                $("#fPickUpPrice").removeAttr('required');
                $("#tPickStartTime").removeAttr('required');
                $("#tPickEndTime").removeAttr('required');
                $("#tNightStartTime").removeAttr('required');
                $("#tNightEndTime").removeAttr('required');
            }
        } else {
            $("#Regular_div1").show();
            $("#Regular_div2").show();
            $("#fFixedFare").hide();
            $("#show-in-fixed").hide();
            $("#hide-priceHour").hide();
            $("#fFixedFare").removeAttr('required');
            $("#iMaxQty").removeAttr('required');
            $("#fPricePerHour").removeAttr('required');
            $("#fPricePerKM").attr('required', 'required');
            $("#iMinFare").attr('required', 'required');
            $("#fPricePerMin").attr('required', 'required');
            $("#iBaseFare").attr('required', 'required');
            $("#iPersonSize").attr('required', 'required');
            $("#fPickUpPrice").attr('required', 'required');
            $("#tPickStartTime").attr('required', 'required');
            $("#tPickEndTime").attr('required', 'required');
            /*                  $("#tNightStartTime").attr('required', 'required');

                     $("#tNightEndTime").attr('required', 'required');*/
        }
    }

    function get_AllowQty(val) {
        if (val == "Yes") {
            $("#iMaxQty-div").show();
            $("#iMaxQty").attr('required', 'required');
        } else {
            $("#iMaxQty-div").hide();
            $("#iMaxQty").removeAttr('required');
        }
    }

    function changeCode(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            } else {
                console.log(response.result);
            }
        });
    }

    function getpriceCheck(id) {
        /*var km_rs=document.getElementById('fPricePerKM').value;

                 var min_rs=document.getElementById('fPricePerMin').value;

                 var base_rs=document.getElementById('iBaseFare').value;

                 var com_rs=document.getElementById('fCommision').value;

                 if(km_rs != 0 && min_rs !=0 && base_rs != 0 && com_rs != 0)

                 {

                 }*/
        if (id > 0) {
            $('#price').html('');
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#price').html('<i class="alert-danger alert"> You can not enter any price as Zero or Letter.</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
    }

    function getpriceCheck_digit(id) {
        var check = isNaN(id);
        if (check === false) {
            $('#price').html('');
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#price').html('<i class="alert-danger alert"> You can not enter any price as Zero or Letter.</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
    }

    function onlydigit(id) {
        var digi = /^[1-9]{1}$/;
        result = digi.test(id);
        if (result == true) {
            $('#digit').html('');
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#digit').html('<i class="alert-danger alert">Only Decimal Number less Than 10</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
    }

    $(function () {
        newDate = new Date('Y-M-D');
        $('#tNightStartTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            //sideBySide: true,
        });
        $('#tNightEndTime').datetimepicker({
            format: 'HH:mm:ss',
            //minDate: moment().format('l'),
            ignoreReadonly: true,
            useCurrent: false

            //sideBySide: true,
        });
    });

    function showhidepickuptime() {
        if ($('input[name=ePickStatus]').is(':checked')) {
            //alert('Checked');
            $("#showpickuptime").show();
        } else {
            //alert('Not checked');
            $("#showpickuptime").hide();
        }
    }

    function showhidenighttime() {
        if ($('input[name=eNightStatus]').is(':checked')) {
            $("#shownighttime").show();
            $("#tNightStartTime").attr('required');
            $("#tNightEndTime").attr('required');
        } else {
            //alert('Not checked');
            $("#shownighttime").hide();
            $("#tNightStartTime").removeAttr('required');
            $("#tNightEndTime").removeAttr('required');
        }
    }

    function showpoolpercentage() {
        if ($('input[name=ePoolStatus]').is(':checked')) {
            hideShowFareCalcModel('disabled' , 1);

            $("#pool_div").show();
            $(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#MotoBike,#Cycle,#Truck,#rentalOption").hide();
            $("#eIconType").val("Car");
            $("#fPoolPercentage").prop('required', true);
            $("#iPersonSize").val("2");
        } else {
            hideShowFareCalcModel("" , 1);
            //alert('Not checked');
            $("#pool_div").hide();
            $(".RentalAlias,#fWaitingFeesDiv,#iWaitingFeeTimeLimitDiv,#MotoBike,#Cycle,#Truck,#rentalOption").show();
            $("#fPoolPercentage").prop('required', false);
            $("#iPersonSize").val("1");
        }
    }

    function setCity(id, selected) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#iCityId").html(dataHtml);
            } else {
                console.log(response.result);
            }
        });
    }

    function setState(id, selected) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#iStateId").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                console.log(response.result);
            }
        });
        changeCode(id);
    }

    function changeCode(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_code.php',
            'AJAX_DATA': {id: id, eUnit: 'yes'},
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHTML2 = response.result;
                if (dataHTML2 != null)
                    $("#change_eUnit").text(dataHTML2.eUnit);
            } else {
                console.log(response.result);
            }
        });
    }

    function changeCode_distance(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_unit.php',
            'AJAX_DATA': {id: id},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHTML2 = response.result;
                if (dataHTML2 != null)
                    $("#change_eUnit").text(dataHTML2);
            } else {
                console.log(response.result);
            }
        });
    }

    setState('<?= $iCountryId; ?>', '<?= $iStateId; ?>');
    setCity('<?= $iStateId; ?>', '<?= $iCityId; ?>');
    showhidepickuptime();
    showhidenighttime();
    changeCode_distance('<?= $iLocationId ?>');

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 47 && charCode < 58 || charCode == 46 || charCode == 127 || charCode == 8)
            return true;
        return false;
    }

    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
            //alert(referrer);
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "vehicle_type.php<?= $queryString;  ?>";
        } else {
            $("#backlink").val(referrer);
        }
        //console.log(referrer);
        $(".back_link").attr('href', referrer);
    });

    var input = $('.clockpicker-with-callbacks').clockpicker({
        donetext: 'Done',
        init: function () {
            //console.log("colorpicker initiated");
        },
        beforeShow: function () {
            //console.log("before show");
        },
        afterShow: function () {
           // console.log("after show");
        },
        beforeHide: function () {
            //console.log("before hide");
        },
        afterHide: function () {
           // console.log("after hide");
        },
        beforeHourSelect: function () {
            //console.log("before hour selected");
        },
        afterHourSelect: function () {
           // console.log("after hour selected");
        },
        beforeDone: function () {
            //console.log("before done");
        },
        afterDone: function () {
           // console.log("after done");
        }
    });
    document.getElementById('RNightMonday').onclick = function () {
        var tMonNightStartTime = document.getElementById('tMonNightStartTime');
        var tMonNightEndTime = document.getElementById('tMonNightEndTime');
        var fMonNightPrice = document.getElementById('fMonNightPrice');
        tMonNightStartTime.value = tMonNightEndTime.value = fMonNightPrice.value = '';
    };
    document.getElementById('RNightTuesday').onclick = function () {
        var tTueNightStartTime = document.getElementById('tTueNightStartTime');
        var tTueNightEndTime = document.getElementById('tTueNightEndTime');
        var fTueNightPrice = document.getElementById('fTueNightPrice');
        tTueNightStartTime.value = tTueNightEndTime.value = fTueNightPrice.value = '';
    };
    document.getElementById('RNightWednesday').onclick = function () {
        var tWedNightStartTime = document.getElementById('tWedNightStartTime');
        var tWedNightEndTime = document.getElementById('tWedNightEndTime');
        var fWedNightPrice = document.getElementById('fWedNightPrice');
        tWedNightStartTime.value = tWedNightEndTime.value = fWedNightPrice.value = '';
    };
    document.getElementById('RNightThursday').onclick = function () {
        var tThuNightStartTime = document.getElementById('tThuNightStartTime');
        var tThuNightEndTime = document.getElementById('tThuNightEndTime');
        var fThuNightPrice = document.getElementById('fThuNightPrice');
        tThuNightStartTime.value = tThuNightEndTime.value = fThuNightPrice.value = '';
    };
    document.getElementById('RNightFriday').onclick = function () {
        var tFriNightStartTime = document.getElementById('tFriNightStartTime');
        var tFriNightEndTime = document.getElementById('tFriNightEndTime');
        var fFriNightPrice = document.getElementById('fFriNightPrice');
        tFriNightStartTime.value = tFriNightEndTime.value = fFriNightPrice.value = '';
    };
    document.getElementById('RNightSaturday').onclick = function () {
        var tSatNightStartTime = document.getElementById('tSatNightStartTime');
        var tSatNightEndTime = document.getElementById('tSatNightEndTime');
        var fSatNightPrice = document.getElementById('fSatNightPrice');
        tSatNightStartTime.value = tSatNightEndTime.value = fSatNightPrice.value = '';
    };
    document.getElementById('RNightSunday').onclick = function () {
        var tSunNightStartTime = document.getElementById('tSunNightStartTime');
        var tSunNightEndTime = document.getElementById('tSunNightEndTime');
        var fSunNightPrice = document.getElementById('fSunNightPrice');
        tSunNightStartTime.value = tSunNightEndTime.value = fSunNightPrice.value = '';
    };
    //added by SP 27-06-2019 start
    var timeLimit = $("#iCancellationTimeLimit").val();
    if (timeLimit.trim() == '') {
        $("#fCancellationFare").val('');
    }
    var timeLimit = $("#iWaitingFeeTimeLimit").val();
    if (timeLimit.trim() == '') {
        $("#fWaitingFees").val('');
    }

    function checkcancellationfare(idval) {
        var timeLimit = $("#" + idval).val();
        if (timeLimit.trim() == '') {
            document.getElementById(idval).focus();
            $("#" + idval).val('');
            return false;
        }
    }

    function checkblanktimelimit(idval, idcanval) {
        var timeLimit = $("#" + idval).val();
        if (timeLimit.trim() == '') {
            $("#" + idcanval).val('');
        }
    }

    //added by SP 27-06-2019 end
    function editVehicleType(action) {
        $('#modal_action').html(action);
        $('#vehicle_type_Modal').modal('show');
    }

    function saveVehicleType() {
        if ($('#vVehicleType_<?= $default_lang ?>').val() == "") {
            $('#vVehicleType_<?= $default_lang ?>_error').show();
            $('#vVehicleType_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vVehicleType_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vVehicleType_Default').val($('#vVehicleType_<?= $default_lang ?>').val());
        $('#vVehicleType_Default').closest('.row').removeClass('has-error');
        $('#vVehicleType_Default-error').remove();
        $('#vehicle_type_Modal').modal('hide');
    }

    function editVehicleRental(action) {
        $('#modal_action').html(action);
        $('#vehicle_rental_Modal').modal('show');
    }

    function saveVehicleRental() {
        if ($('#vRentalAlias_<?= $default_lang ?>').val() == "") {
            $('#vRentalAlias_<?= $default_lang ?>_error').show();
            $('#vRentalAlias_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentalAlias_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vRentalAlias_Default').val($('#vRentalAlias_<?= $default_lang ?>').val());
        $('#vRentalAlias_Default').closest('.row').removeClass('has-error');
        $('#vRentalAlias_Default-error').remove();
        $('#vehicle_rental_Modal').modal('hide');
    }

    function editDeliveryHelperNote(action, modal_id) {
        $('#' + modal_id).find('#modal_action').html(action);
        $('#' + modal_id).modal('show');
    }

    function saveDeliveryHelperNote(input_id, modal_id) {
        var DescLength = CKEDITOR.instances[input_id + '<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
        if (!DescLength) {
            $('#' + input_id + '<?= $default_lang ?>_error').show();
            $('#' + input_id + '<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#' + input_id + '<?= $default_lang ?>_error').hide();
            }, 5000);
            e.preventDefault();
            return false;
        }
        var DescHTML = CKEDITOR.instances[input_id + '<?= $default_lang ?>'].getData();
        CKEDITOR.instances[input_id + 'Default'].setData(DescHTML);
        $('#' + modal_id).modal('hide');
    }

    function editVehicleDescInfo(action) {
        $('#modal_action').html(action);
        $('#tInfoText_Modal').modal('show');
    }

    function saveVehicleDescInfo() {
        if ($('#tInfoText_<?= $default_lang ?>').val().trim() == "") {
            $('#tInfoText_<?= $default_lang ?>_error').show();
            $('#tInfoText_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#tInfoText_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#tInfoText_Default').val($('#tInfoText_<?= $default_lang ?>').val());
        $('#tInfoText_Default').closest('.row').removeClass('has-error');
        $('#tInfoText_Default-error').remove();
        $('#tInfoText_Modal').modal('hide');
    }

    $(document).on('keyup', 'textarea:not([name^=tInfoText_],[name^=tDeliveryHelperNoteDriver_], [name^=tDeliveryHelperNoteUser_])', function (e) {
        var tval = $(this).val(),
            tlength = tval.length,
            set = 100,
            remain = parseInt(set - tlength);
        if (tlength > 0) {
            $(this).closest('.desc-block').find('.desc_counter').text(remain + "/100");
            if (remain <= 0) {
                $(this).val((tval).substring(0, set));
                $(this).closest('.desc-block').find('.desc_counter').text("0/100");
                return false;
            }
        } else {
            $(this).closest('.desc-block').find('.desc_counter').text("100/100");
            return false;
        }
    });
</script>
</body>
<!-- END BODY-->
</html>

