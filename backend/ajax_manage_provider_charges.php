<?php
include 'common.php';

$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';
$eVideoConsultServiceCharge = isset($_REQUEST['eVideoConsultServiceCharge']) ? $_REQUEST['eVideoConsultServiceCharge'] : '0';
$eVideoConsultEnableProvider = isset($_REQUEST['eVideoConsultEnableProvider']) ? $_REQUEST['eVideoConsultEnableProvider'] : '0';
$eVideoServiceDescription = isset($_REQUEST['eVideoServiceDescription']) ? $_REQUEST['eVideoServiceDescription'] : '0';
$isAdmin = isset($_REQUEST['isAdmin']) ? $_REQUEST['isAdmin'] : '0';

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$sql = "select iCompanyId,vCurrencyDriver from `register_driver` where iDriverId = '" . $iDriverId . "'";
$db_usr = $obj->MySQLSelect($sql);
$iCompanyId = $db_usr[0]['iCompanyId'];
$vCurrencyDriver = $db_usr[0]['vCurrencyDriver'];
if ($vCurrencyDriver != '') {
    $sql1 = "select Ratio,vSymbol from `currency` where vName = '" . $vCurrencyDriver . "'";
    $db_usrcurrency = $obj->MySQLSelect($sql1);
} else {
    $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
    $db_usrcurrency = $obj->MySQLSelect($sql1);
}
$ration = $db_usrcurrency[0]['Ratio'];

$service_data = $obj->MySQLSelect("SELECT eVideoServiceDescription,eVideoConsultServiceCharge,eVideoConsultEnableProvider,eStatus FROM driver_services_video_consult_charges WHERE iDriverId = '$iDriverId' AND iVehicleCategoryId = '$iVehicleCategoryId'");

$VehicleCategory_data = $obj->MySQLSelect("SELECT vCategory_EN,eVideoConsultServiceCharge,eVideoServiceDescription,eVideoConsultEnable,iVehicleCategoryId  FROM vehicle_category WHERE iVehicleCategoryId = '$iVehicleCategoryId'");

if($method == "GET_DATA") {
    $DB_iBookLaterHours = "";
    if(!empty($service_data) && scount($service_data) > 0) {
       
        $eVideoConsultServiceCharge = $service_data[0]['eVideoConsultServiceCharge'];
        $eVideoConsultEnableProvider = $service_data[0]['eVideoConsultEnableProvider'];
        $eVideoServiceDescription =    $service_data[0]['eVideoServiceDescription'];
        $eStatus =    $service_data[0]['eStatus'];
    }else{
        $eVideoConsultServiceCharge = $VehicleCategory_data[0]['eVideoConsultServiceCharge'];
        $eVideoServiceDescription = $VehicleCategory_data[0]['eVideoServiceDescription'];
    }

    $returnArr['Action'] = "1";
    // $returnArr['eVideoConsultServiceCharge'] = setTwoDecimalPoint($eVideoConsultServiceCharge * $ration);
    $returnArr['eVideoConsultServiceCharge'] = setTwoDecimalPoint($eVideoConsultServiceCharge);
    $returnArr['eVideoConsultEnableProvider'] = $eVideoConsultEnableProvider;
    $returnArr['eVideoServiceDescription'] = $eVideoServiceDescription;
    $returnArr['eStatus'] = $eStatus;

    echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);
}
else {

    if(!empty($service_data) && scount($service_data) > 0) {

        
        $obj->sql_query("UPDATE driver_services_video_consult_charges SET eVideoServiceDescription = '".$eVideoServiceDescription."', eVideoConsultEnableProvider = '$eVideoConsultEnableProvider' , eVideoConsultServiceCharge = '".$eVideoConsultServiceCharge."' WHERE iDriverId = '$iDriverId' AND iVehicleCategoryId = '$iVehicleCategoryId'");
        
        if($isAdmin == 1){
            $obj->sql_query("UPDATE driver_services_video_consult_charges SET eStatus = 'Active', eApproved = 'Yes'  WHERE iDriverId = '$iDriverId' AND iVehicleCategoryId = '$iVehicleCategoryId'");
        }
    }
    else {
        $Data_service = array();
        $Data_service['iDriverId'] = $iDriverId;
        $Data_service['iVehicleCategoryId'] = $iVehicleCategoryId;
        $Data_service['eVideoConsultEnableProvider'] = $eVideoConsultEnableProvider;
        $Data_service['eVideoConsultServiceCharge'] = $eVideoConsultServiceCharge;
        $Data_service['eVideoServiceDescription'] = $eVideoServiceDescription;
        if($isAdmin == 1){
            $Data_service['eStatus'] = 'Active';
            $Data_service['eApproved'] = 'Yes';
        }

        $obj->MySQLQueryPerform("driver_services_video_consult_charges", $Data_service, "insert");
    }

    $returnArr['Action'] = "1";
    $returnArr['message'] = "LBL_Record_Updated_successfully";
    echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);
}
exit;
?>