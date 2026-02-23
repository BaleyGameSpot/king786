<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'driver,company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
$start = @date("Y");
$end = '1970';
$script = "My Availability";
$tbl_name = 'driver_vehicle';
$tbl_name1 = 'service_pro_amount';
$tbl_dsr = 'driver_service_request';
if (isset($_REQUEST['iProviderId']) && !empty($_REQUEST['iProviderId'])){
    $_REQUEST['iDriverId'] = $_REQUEST['iProviderId'];
}
$iDriverId = isset($_REQUEST['iDriverId'])?base64_decode(base64_decode(trim($_REQUEST['iDriverId']))):'';
$sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '".$iDriverId."' AND eType='UberX'";
$db_drv_veh = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id'])?$_REQUEST['id']:$db_drv_veh[0]['iDriverVehicleId'];
$action = ($id != '')?'Edit':'Add';
/*$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$error_msg = isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : '';
$var_msg1 = isset($_REQUEST['var_msg1']) ? $_REQUEST['var_msg1'] : '';*/
$success = '';
if (isset($_SESSION['message_code']) && !empty($_SESSION['message_code'])){
    $success = $_SESSION['message_code'];
    $_SESSION['message_code'] = '';
}
$var_msg1 = '';
if (isset($_SESSION['session_msg']) && !empty($_SESSION['session_msg'])){
    $var_msg1 = $_SESSION['session_msg'];
    $_SESSION['session_msg'] = '';
}
$error_msg = '';
if (isset($_SESSION['session_err_msg']) && !empty($_SESSION['session_err_msg'])){
    $error_msg = $_SESSION['session_err_msg'];
    $_SESSION['session_err_msg'] = '';
}
$vLicencePlate = isset($_POST['vLicencePlate'])?$_POST['vLicencePlate']:'';
$iMakeId = isset($_POST['iMakeId'])?$_POST['iMakeId']:'3';
$iModelId = isset($_POST['iModelId'])?$_POST['iModelId']:'1';
$fAmount = isset($_POST['fAmount'])?$_POST['fAmount']:'';
$iYear = isset($_POST['iYear'])?$_POST['iYear']:Date('Y');
$eStatus_check = isset($_POST['eStatus'])?$_POST['eStatus']:'off';
$vCarType = isset($_POST['vCarType'])?$_POST['vCarType']:'';
$eStatus = ($eStatus_check == 'on')?'Active':'Inactive';
$eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'UberX';
$isRedirectToDocumentUploadPage = isset($_REQUEST['isRedirectToDocumentUploadPage'])?$_REQUEST['isRedirectToDocumentUploadPage']:'';
$content = isset($_REQUEST['content'])?$_REQUEST['content']:'manageservicecontent';
$sql = "select iCompanyId, vCurrencyDriver, eStatus, vWorkLocationLatitude, vWorkLocation, vWorkLocationLongitude from `register_driver` where iDriverId = '".$iDriverId."'";
$dbDriver = $obj->MySQLSelect($sql);
$vWorkLocation = $dbDriver[0]['vWorkLocation'];
$vWorkLocationLatitude = $dbDriver[0]['vWorkLocationLatitude'];
$vWorkLocationLongitude = $dbDriver[0]['vWorkLocationLongitude'];
if ($_SESSION['sess_user'] == 'driver'){
    $sql = "select iCompanyId, vCurrencyDriver, eStatus from `register_driver` where iDriverId = '".$_SESSION['sess_iUserId']."'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    $vCurrencyDriver = $db_usr[0]['vCurrencyDriver'];
    if ($vCurrencyDriver != ''){
        $sql1 = "select Ratio,vSymbol from `currency` where vName = '".$vCurrencyDriver."'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }else{
        $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }
    $vSymbol = $db_usrcurrency[0]['vSymbol'];
    $ration = $db_usrcurrency[0]['Ratio'];
    $iDriverIdNew = $_SESSION['sess_iUserId'];
}
if ($_SESSION['sess_user'] == 'company'){
    $iCompanyId = $_SESSION['sess_iCompanyId'];
    $sql = "select * from register_driver where iCompanyId = '".$_SESSION['sess_iCompanyId']."'";
    $db_drvr = $obj->MySQLSelect($sql);
    //$sql = "select iCompanyId,vCurrencyDriver from `register_driver` where iDriverId = '" . $iDriverId . "'";
    $sql = "select iCompanyId,vCurrencyCompany,eStatus from `company` where iCompanyId = '".$_SESSION['sess_iCompanyId']."'";
    $db_usr = $obj->MySQLSelect($sql);
    $vCurrencyDriver = $db_usr[0]['vCurrencyCompany'];
    if ($vCurrencyDriver != ''){
        $sql1 = "select Ratio,vSymbol from `currency` where vName = '".$vCurrencyDriver."'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }else{
        $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }
    $vSymbol = $db_usrcurrency[0]['vSymbol'];
    $ration = $db_usrcurrency[0]['Ratio'];
}
/* Replace with ePricetype */
$chngamt = "Disabled";
if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes"){
    $chngamt = "Enabled";
}
if (isset($_POST['submitaddress'])){
    $vWorkLocationLatitude = isset($_REQUEST["vWorkLocationLatitude"])?$_REQUEST["vWorkLocationLatitude"]:'';
    $vWorkLocationLongitude = isset($_REQUEST["vWorkLocationLongitude"])?$_REQUEST["vWorkLocationLongitude"]:'';
    $vWorkLocation = isset($_REQUEST["vWorkLocation"])?$_REQUEST["vWorkLocation"]:'';
    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vWorkLocationLatitude'] = $vWorkLocationLatitude;
    $Data_update_driver['vWorkLocationLongitude'] = $vWorkLocationLongitude;
    $Data_update_driver['vWorkLocation'] = $vWorkLocation;
    $Did = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
    $var_msg = $langage_lbl['LBL_ADDRESS_UPDATE_MSG'];
    $_SESSION['message_code'] = '3';
    $_SESSION['session_msg'] = $var_msg;
    //success=3&var_msg1=" . $var_msg . "
    header("Location:cx-add_services.php?iDriverId=".base64_encode(base64_encode($iDriverId))."&content=biddingcontent");
    exit;
}
if (isset($_POST['submitbid'])){
    if ($ENABLE_EDIT_DRIVER_SERVICE == "No"){
        $error_msg = $langage_lbl['LBL_EDIT_SERVICE_DISABLED'];
        $_SESSION['message_code'] = '2';
        $_SESSION['session_err_msg'] = $error_msg;
        header("Location:cx-add_services.php?iDriverId=".base64_encode(base64_encode($iDriverId))."&content=biddingcontent");
        //header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2&content=biddingcontent");
        exit;
    }
    $sql = "select iCompanyId, vCurrencyDriver, eStatus, vWorkLocationLatitude, vWorkLocation, vWorkLocationLongitude, vName, vLastName, vEmail, vCode ,vPhone from `register_driver` where iDriverId = '".$iDriverId."'";
    $dbDriver = $obj->MySQLSelect($sql);
    $vWorkLocationLatitude = $dbDriver[0]['vWorkLocationLatitude'];
    $vWorkLocationLongitude = $dbDriver[0]['vWorkLocationLongitude'];
    if (empty($vWorkLocationLatitude) && empty($vWorkLocationLongitude)){
        $error_msg = $langage_lbl['LBL_ENTER_LOC_HINT_TXT'];
        $_SESSION['message_code'] = '2';
        $_SESSION['session_err_msg'] = $error_msg;
        header("Location:cx-add_services.php?iDriverId=".base64_encode(base64_encode($iDriverId))."&content=biddingcontent");
        //header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2&content=biddingcontent");
        exit;
    }
    $iReqBiddingId = implode(",",$_REQUEST['selectedbiddingdriverservice']);
    $newReqBiddingId = explode(',',$iReqBiddingId);
    $biddingdriverservice = $BIDDING_OBJ->biddingDriverService('webservice',$iDriverId);
    $biddingdriverservice = explode(',',$biddingdriverservice[0]['vBiddingId']);
    $remainingCats = array_diff($newReqBiddingId,$biddingdriverservice);
    if (scount($biddingdriverservice > 0)){
        $updatebiddingDriverService = [];
        $isdeleted = [];
        foreach ($biddingdriverservice as $exit){
            if (in_array($exit,$newReqBiddingId)){
                $updatebiddingDriverService[] = $exit;
            }else{
                $isdeleted[] = 1;
            }
        }
        if (scount($isdeleted) > 0){
            $data['vBiddingId'] = implode(',',$updatebiddingDriverService);
            $where = 'iDriverId = "'.$iDriverId.'"';
            $id = $BIDDING_OBJ->updatebiddingDriverService('webservice',$data,$where);
        }else{
            $id = 1;
        }
    }
    foreach ($remainingCats as $key => $Bidding){
        if (!empty($Bidding)){
            $existRequest = $BIDDING_OBJ->biddingdriverrequestcount('webservice',$iDriverId,$Bidding);
            if ($existRequest == 0){
                $creDataArr['iBiddingId'] = $Bidding;
                $creDataArr['iDriverId'] = $iDriverId;
                $id = $BIDDING_OBJ->createbiddingdriverrequest('webservice',$creDataArr);
                $creDataArrs['iDriverId'] = $iDriverId;
                $idd = $BIDDING_OBJ->createbiddingDriverService('webservice',$creDataArrs);
            }else{
                $id = 1;
            }
        }
    }
    if ($id > 0){
        $remainingCatsString = implode(",",$remainingCats);
        $vehicle_type_sql = "SELECT JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_".$_SESSION['sess_lang']."')) as vTitle from  bidding_service  where iBiddingId IN (".$remainingCatsString.") ";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
        $services = array();
        foreach ($vehicle_type_data as $Vk => $Vval){
            $services[] = $Vval["vTitle"];
        }
        $requestedservice = implode(", ",$services);
        $getMaildata['name'] = $dbDriver[0]['vName']." ".$dbDriver[0]['vLastName'];
        if (isset($dbDriver[0]['vEmail']) && !empty($dbDriver[0]['vEmail'])){
            $getMaildata['email'] = $langage_lbl['LBL_EMAIL_TEXT'].': '.$dbDriver[0]['vEmail'];
        }
        if (isset($dbDriver[0]['vPhone']) && !empty($dbDriver[0]['vPhone'])){
            $getMaildata['phone'] = $langage_lbl['LBL_PHONE'].': '."+".$dbDriver[0]['vCode']." ".$dbDriver[0]['vPhone'];
        }
        $getMaildata['Service'] = $langage_lbl['LBL_SERVICE_BIDDING']."- ".$requestedservice;
        $mail = $COMM_MEDIA_OBJ->SendMailToMember('SERVICE_REQUEST_FROM_PROVIDER',$getMaildata);
        if ($isRedirectToDocumentUploadPage == "Yes"){
            $var_msg = $langage_lbl['LBL_Record_Updated_successfully'];
            $_SESSION['message_code'] = '1';
            $_SESSION['session_msg'] = $var_msg;
            header("Location:profile.php?iDriverId=".base64_encode(base64_encode($iDriverId))."&content=biddingcontent");
            //header("Location:profile.php?success=1&var_msg=" . $var_msg . "&iDriverId=" . base64_encode(base64_encode($iDriverId))."&content=biddingcontent");
            exit;
        }else{
            $_SESSION['message_code'] = '1';
            header("Location:add_services.php?iDriverId=".base64_encode(base64_encode($iDriverId))."&content=biddingcontent"); // success=1
            exit;
        }
    }
}
if (isset($_POST['submit1'])){
    // if (SITE_TYPE == 'Demo' && $action == 'Edit') {  // commented After Change given by KS sir Done by NModi on 11-12-20
    //     $error_msg = $langage_lbl['LBL_EDIT_DELETE_RECORD'];
    //     header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
    //     exit;
    // }
    if (strtoupper($db_usr[0]['eStatus']) != 'INACTIVE'){
        if ($ENABLE_EDIT_DRIVER_SERVICE == "No"){
            $error_msg = $langage_lbl['LBL_EDIT_SERVICE_DISABLED'];
            $_SESSION['message_code'] = '2';
            $_SESSION['session_err_msg'] = $error_msg;
            header("Location:cx-add_services.php?iDriverId=".base64_encode(base64_encode($iDriverId)));
            //header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
            exit;
        }
    }
    if (!isset($_REQUEST['vCarType'])){
        $error_msg = $langage_lbl['LBL_SELECT_CAR_TYPE'];
        $_SESSION['message_code'] = '2';
        $_SESSION['session_err_msg'] = $error_msg;
        header("Location:cx-add_services.php?iDriverId=".base64_encode(base64_encode($iDriverId)));
        // header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }
    if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
        $vLicencePlate = 'My Services';
    }else{
        $vLicencePlate = $vLicencePlate;
    }
    if (SITE_TYPE == 'Demo'){
        $str = ", eStatus = 'Active' ";
    }else{
        $str = ", eStatus = 'Active' ";
    }
    $cartype = implode(",",$_REQUEST['vCarType']);
    $driverstatusQuery = "SELECT eStatus FROM register_driver WHERE iDriverId = '".$iDriverId."'";
    $iDriverStatus = $obj->MySQLSelect($driverstatusQuery);
    $eStatus = $iDriverStatus[0]['eStatus'];
    /* ------------------------------ */
    /* Request Service for Activation */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes'){
        //if ($eStatus != 'inactive') {
        // $sql = "SELECT vCarType from driver_vehicle where iDriverId = '" . $iDriverId . "' AND vLicencePlate = 'My Services'";
        $sql = 'SELECT dv.vCarType, rd.vEmail, rd.vName ,rd.vLastName ,rd.vCode ,rd.vPhone FROM driver_vehicle AS dv JOIN register_driver AS rd ON rd.iDriverId = dv.iDriverId WHERE dv.iDriverId = '.$iDriverId.' AND dv.vLicencePlate = "My Services"';
        $existRequestdb = $obj->MySQLSelect($sql);
        $existServices = explode(',',$existRequestdb[0]['vCarType']);
        $remainingCats = array_diff($_REQUEST['vCarType'],$existServices);
        foreach ($remainingCats as $key => $catVal){
            if (!empty($catVal)){
                $sql = "SELECT iDriverId from driver_service_request where iDriverId = '".$iDriverId."' AND iVehicleCategoryId = '".$catVal."'";
                $existRequest = $obj->MySQLSelect($sql);
                if (scount($existRequest) == 0){
                    $q = "INSERT INTO ";
                    $wheredrs = '';
                    $query = $q." `".$tbl_dsr."` SET
                                `iVehicleCategoryId` = '".$catVal."',
                                `iDriverId` = '".$iDriverId."',
                                `cRequestStatus` = 'Pending'".$wheredrs;
                    $obj->sql_query($query);
                }
            }
        }
        if (!empty($remainingCats)){
            /* Send Email to Driver */
            $getMaildata['name'] = $existRequestdb[0]['vName']." ".$existRequestdb[0]['vLastName'];
            $emailtxt = $langage_lbl['LBL_DRIVER_TXT']." ".$langage_lbl['LBL_EMAIL_TEXT']." : ".$existRequestdb[0]['vEmail'];
            /*$getMaildata['email'] = !empty($existRequestdb[0]['vEmail']) ? $emailtxt : '';
            $getMaildata['phone'] = "+" . $existRequestdb[0]['vCode'] . " " . $existRequestdb[0]['vPhone'];*/
            if (isset($existRequestdb[0]['vEmail']) && !empty($existRequestdb[0]['vEmail'])){
                $getMaildata['email'] = $langage_lbl['LBL_EMAIL_TEXT'].': '.$existRequestdb[0]['vEmail'];
            }
            if (isset($existRequestdb[0]['vPhone']) && !empty($existRequestdb[0]['vPhone'])){
                $getMaildata['phone'] = $langage_lbl['LBL_PHONE'].': '."+".$existRequestdb[0]['vCode']." ".$existRequestdb[0]['vPhone'];
            }
            $getMaildata['Service'] = $langage_lbl['LBL_SERVICE_ON_DEMAND'];
            $mail = $COMM_MEDIA_OBJ->SendMailToMember('SERVICE_REQUEST_FROM_PROVIDER',$getMaildata);
        }
        // }
    }
    /* End Request Service for Activation */
    /* ------------------------------ */
    $q = "INSERT INTO ";
    $where = '';
    if ($id != ''){
        $q = "UPDATE ";
        $where = " WHERE `iDriverId` = '".$iDriverId."' AND `iDriverVehicleId` = '".$id."' ";
    }
    $query = $q." `".$tbl_name."` SET
        `vLicencePlate` = '".$vLicencePlate."',
        `iYear` = '".$iYear."',
        `iCompanyId` = '".$iCompanyId."',
        `iDriverId` = '".$iDriverId."',
        `eType` = '".$eType."',
        `vCarType` = '".$cartype."' $str".$where;
    $obj->sql_query($query);
    $id = ($id != '')?$id:$obj->GetInsertId();
    /* --------------------------------------- */
    /* This is for Reverse operation for new added services as it should be approve first */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes'){
        if ($eStatus != 'inactive'){
            $sql = "SELECT vCarType from driver_vehicle where iDriverId = '".$iDriverId."' AND vLicencePlate = 'My Services'";
            $existRequest = $obj->MySQLSelect($sql);
            $existServices = explode(',',$existRequest[0]['vCarType']);
            $existServices = implode(',',array_diff($existServices,$remainingCats));
            $sqlu = 'UPDATE driver_vehicle SET vCarType = "'.$existServices.'" WHERE iDriverId = "'.$iDriverId.'" AND vLicencePlate = "My Services"';
            $existingServices = $obj->sql_query($sqlu);
        }
    }
    /* End for Reverse operation for new added services */
    /* --------------------------------------- */
    if (!empty($fAmount)){
        //$amt_man=$fAmount;
        $amt_man = array();
        foreach ($fAmount as $key => $value){
            $amt_man[$key] = $value / $ration;
        }
        $sql = "select iServProAmntId,iDriverVehicleId from ".$tbl_name1." where iDriverVehicleId = '".$id."' ";
        $db_drv_price = $obj->MySQLSelect($sql);
        if (scount($db_drv_price) > 0){
            $sql = "delete from ".$tbl_name1." where iDriverVehicleId='".$db_drv_price[0]['iDriverVehicleId']."'";
            $obj->sql_query($sql);
        }
        foreach ($amt_man as $key => $value){
            if ($value != ""){
                $q = "Insert Into ";
                $query = $q." `".$tbl_name1."` SET
                    `iDriverVehicleId` = '".$id."',
                    `iVehicleTypeId` = '".$key."',
                    `fAmount` = '".$value."'";
                $db_parti_price = $obj->sql_query($query);
            }
        }
    }
    /*-----------------------------------*/
    if ($MODULES_OBJ->isEnableCarSizeServiceTypeAmount()){
        include_once "vehicle_type_size_price_info.php";
        $iDriverVehicleId = $db_drv_veh[0]['iDriverVehicleId'];
        updateSizeInfoData();
    }
    /*-----------------------------------*/
    if ($action == "Add"){
        $sql = "SELECT * FROM company WHERE iCompanyId = '".$iCompanyId."'";
        $db_compny = $obj->MySQLSelect($sql);
        $sql = "SELECT * FROM register_driver WHERE iDriverId = '".$iDriverId."'";
        $db_status = $obj->MySQLSelect($sql);
        $maildata['EMAIL'] = $db_status[0]['vEmail'];
        $maildata['NAME'] = $db_status[0]['vName'];
        $maildata['EMAIL_NAME'] = $db_status[0]['vName'];
        $maildata['DETAIL'] = "Your Services is Added For ".$db_compny[0]['vName']." and will process your document and activate your account ";
        $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING",$maildata);
    }
    $iDriverId_pro = "iDriverId";
    if ($APP_TYPE == 'UberX'){
        $iDriverId_pro = "iProviderId";
    }
    if ($isRedirectToDocumentUploadPage == "Yes"){
        $var_msg = $langage_lbl['LBL_Record_Updated_successfully'];
        $_SESSION['message_code'] = '1';
        $_SESSION['session_err_msg'] = $var_msg;
        $_SESSION['session_msg'] = $var_msg;
        header("Location:profile.php?iDriverId=".base64_encode(base64_encode($iDriverId)));
        exit;
    }else{
        $_SESSION['message_code'] = '1';
        header("Location:add_services.php?".$iDriverId_pro."=".base64_encode(base64_encode($iDriverId)));
        exit;
    }
    //$var_msg = $langage_lbl['LBL_SERVICE_UPDATE_SUCCESS'];
    //header("Location:cx-add_services.php?success=1&var_msg=" . $var_msg . "&iDriverId=" . base64_encode(base64_encode($iDriverId)));
}
// for Edit
$sql = "SELECT t.*,t1.fAmount,t1.iServProAmntId,t1.iVehicleTypeId AS `VehicleId`,t1.iDriverVehicleId AS `DriverVehilceId` from  $tbl_name as t left join $tbl_name1 t1 on t.iDriverVehicleId=t1.iDriverVehicleId where t.iDriverId = '".$iDriverId."' AND t.iDriverVehicleId = '".$db_drv_veh[0]['iDriverVehicleId']."' ";
$db_data = $obj->MySQLSelect($sql);
$vLabel = $id;
$fAmount = array();
if (scount($db_data) > 0){
    foreach ($db_data as $key => $value){
        $vLicencePlate = $value['vLicencePlate'];
        $iYear = $value['iYear'];
        $eCarX = $value['eCarX'];
        $eType = $value['eType'];
        $eCarGo = $value['eCarGo'];
        $iDriverId = $value['iDriverId'];
        $vCarType = $value['vCarType'];
        $iCompanyId = $value['iCompanyId'];
        $eStatus = $value['eStatus'];
        $iDriverVehicleId = $value['iDriverVehicleId'];
        $amt = $value['fAmount'] * $ration;
        $fAmount[$value['VehicleId']] = $amt;
    }
}
$vCarTyp = explode(",",$vCarType);
if ($MODULES_OBJ->isEnableBiddingServices()){
    $biddingdriverservice = $BIDDING_OBJ->biddingDriverService('webservice',$iDriverId);
    $selectedbiddingdriverservice = explode(',',$biddingdriverservice[0]['vBiddingId']);
    $biddingdriverrequest = $BIDDING_OBJ->biddingdriverrequest('webservice',$iDriverId);
    $biddingdriverrequest = $BIDDING_OBJ->multiToSingle($biddingdriverrequest,'iBiddingId');
}
if ($APP_TYPE == 'Delivery'){
    $Vehicle_type_name = 'Deliver';
}else if ($APP_TYPE == 'Ride-Delivery-UberX'){
    $Vehicle_type_name = 'UberX';
}else{
    $Vehicle_type_name = $APP_TYPE;
}
if ($Vehicle_type_name == "Ride-Delivery"){
    $vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver')";
    $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
}else{
    if ($Vehicle_type_name == 'UberX'){
        $userSQL = "SELECT c.iCountryId from register_driver AS rd LEFT JOIN country AS c ON c.vCountryCode=rd.vCountry where rd.iDriverId='".$iDriverId."'";
        $drivers = $obj->MySQLSelect($userSQL);
        $iCountryId = $drivers[0]['iCountryId'];
        $whereParentId = $ssql = "";
        if (
            in_array($eType,['UberX',
                             'VideoConsult']) && $MODULES_OBJ->isEnableMedicalServices('Yes')
        ){
            $ssql .= " AND vc.iVehicleCategoryId NOT IN (22,26,158) ";
        }
        if ($parent_ufx_catid > 0){
            $whereParentId = " AND vc.iVehicleCategoryId='".$parent_ufx_catid."'";
        }
        $getvehiclecat = "SELECT vc.iVehicleCategoryId, vc.vCategory_".$_SESSION['sess_lang']." as main_cat FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.iParentId='0' $whereParentId $ssql";
        $vehicle_type_data = $obj->MySQLSelect($getvehiclecat);
        $i = 0;
        //Added By HJ On 30-11-2020 For Optimize Query Start
        $getVehicleTypeData = $obj->MySQLSelect("SELECT vc.eVideoConsultEnable, vt.vVehicleType,vc.iParentId,vc.vCategory_".$_SESSION['sess_lang'].",vc.iVehicleCategoryId from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='".$Vehicle_type_name."'  AND vc.eStatus='Active' GROUP BY vc.iVehicleCategoryId");
        $vehicleTypeDataArr = $vehicleTypeDataArr1 = array();
        for ($vt = 0;$vt < scount($getVehicleTypeData);$vt++){
            $vehicleTypeDataArr[$getVehicleTypeData[$vt]['iParentId']][] = $getVehicleTypeData[$vt];
        }
        $vehicle_type_dataNew = $obj->MySQLSelect("SELECT vt.*,vc.*,lm.vLocationName from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='".$Vehicle_type_name."' AND (lm.iCountryId='".$iCountryId."' || vt.iLocationid='-1') AND vt.eStatus='Active'");
        for ($vr = 0;$vr < scount($vehicle_type_dataNew);$vr++){
            $vehicleTypeDataArr1[$vehicle_type_dataNew[$vr]['iVehicleCategoryId']][] = $vehicle_type_dataNew[$vr];
        }
        //Added By HJ On 30-11-2020 For Optimize Query End
        foreach ($vehicle_type_data as $key => $val){
            //Added By HJ On 30-11-2020 For Optimize Query Start
            //$vehicle_type_sql = "SELECT vt.vVehicleType,vc.iParentId,vc.vCategory_" . $_SESSION['sess_lang'] . ",vc.iVehicleCategoryId from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' AND vc.iParentId ='" . $val['iVehicleCategoryId'] . "'  AND vc.eStatus='Active' GROUP BY vc.iVehicleCategoryId";
            //$vehicle_type_dataOld = $obj->MySQLSelect($vehicle_type_sql);
            $vehicle_type_dataOld = array();
            if (isset($vehicleTypeDataArr[$val['iVehicleCategoryId']])){
                $vehicle_type_dataOld = $vehicleTypeDataArr[$val['iVehicleCategoryId']];
            }
            if ($MODULES_OBJ->isEnableVideoConsultingService()){
                $vehicle_type_dataOld = $obj->MySQLSelect("SELECT * FROM vehicle_category WHERE iParentId = '".$val['iVehicleCategoryId']."' AND eStatus = 'Active' ");
            }
            //echo "<pre>";print_r($vehicle_type_dataOld);die;
            //Added By HJ On 30-11-2020 For Optimize Query End
            $vehicle_type_data[$i]['SubCategory'] = $vehicle_type_dataOld;
            $j = 0;
            foreach ($vehicle_type_dataOld as $subkey => $subvalue){
                //$vehicle_type_sql1 = "SELECT vt.*,vc.*,lm.vLocationName from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' and vc.iVehicleCategoryId = '" . $subvalue['iVehicleCategoryId'] . "' AND (lm.iCountryId='" . $iCountryId . "' || vt.iLocationid='-1') AND vt.eStatus='Active'";
                //$vehicle_type_dataNew = $obj->MySQLSelect($vehicle_type_sql1);
                //echo "<pre>";print_r($vehicle_type_dataNew);die;
                $vehicle_type_dataNew = array();
                if (isset($vehicleTypeDataArr1[$subvalue['iVehicleCategoryId']])){
                    $vehicle_type_dataNew = $vehicleTypeDataArr1[$subvalue['iVehicleCategoryId']];
                    //echo "<pre>";print_r($vehicle_type_dataNew);die;
                }
                $vehicle_type_data[$i]['SubCategory'][$j]['VehicleType'] = $vehicle_type_dataNew;
                $j++;
            }
            $i++;
        }
    }else{
        $vehicle_type_sql = "SELECT * from  vehicle_type  where eType='".$Vehicle_type_name."' ";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    }
}
$sql = 'SELECT iVehicleCategoryId FROM driver_service_request WHERE iDriverId = "'.$iDriverId.'" ';
$ReqServices = $obj->MySQLSelect($sql);
$requestedServices = [];
foreach ($ReqServices as $key => $ReqService){
    $requestedServices[] = $ReqService['iVehicleCategoryId'];
}
$isEnableServiceTypeWiseProviderDocument = $MODULES_OBJ->isEnableServiceTypeWiseProviderDocument();
// For Bidding Service
$lang = $_SESSION['sess_lang'];
if ($lang == "" || $lang == NULL){
    $lang = $LANG_OBJ->FetchDefaultLangData("vCode");
}
$reqArr = ['vCategory','iBiddingId'];
if ($MODULES_OBJ->isEnableBiddingServices()){
    $biddingServices = $BIDDING_OBJ->getBiddingMaster('webservice','','','',$lang,'',$reqArr);
    if (scount($biddingServices) > 0){
        $reqArr = ['vTitle','iBiddingId'];
        for ($i = 0;$i < scount($biddingServices);$i++){
            if ($biddingServices[$i]['iBiddingId'] != $BIDDING_OBJ->other_id){
                $SubCategory = $BIDDING_OBJ->getBiddingSubCategory('webservice',$biddingServices[$i]['iBiddingId'],'','','',$lang,'',$reqArr);
                $biddingServices[$i]['SubCategory'] = $SubCategory;
            }
        }
    }
}
$currencyData = checkCurrencyWiseFormatting();
$currencyDataJson = json_encode($currencyData);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_HEADER_MY_SERVICES'];?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
    <style>
        .serarch-part-box {
            max-width: 1280px;
            margin: 0 auto;
            display: block;
        }

        .serarch-part-box .serarch-input-box {
            width: 100%;
            min-height: 40px;
            border-radius: 6px;
            border: 1px solid #d7d7d7;
            text-align: center;
            font-size: 18px;
            font-weight: 500;
            cursor: text;
        }

        .card-block .panel.panel-default {
            width: 100%;
        }

        .card-block .panel.panel-default .panel-body, #ajaxHTML.panel-body, .profile-earning-inner.panel-body {
            padding: 0;
            border: 0;
        }

        .card-block.no-services-available {
            padding: 20px;
            min-height: auto;
        }

        .loding-action {
            left: 0;
            margin: auto;
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            height: 100%;
            z-index: 99999;
        }

        .loding-action div {
            left: 50%;
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .main-cat {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            background-color: #EEEEEE;
            padding: 10px;
            width: 100%;
            border: 1px solid #dedede;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-block h1 {
            border-top: 1px solid #e0e0e0;
            padding: 10px 0;
        }

        .w-95 {
            width: 95% !important;
            cursor: text;
        }

        .error {
            position: initial;
            display: none;
        }

        .video_consult_status {
            font-weight: normal;
            cursor: pointer;
        }

        .consult_price_symbol {
            padding-right: 5px;
        }

        /* Style the tab */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 5px;
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
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #ddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #ccc;
        }

        /* Style the tab content */
        .tabcontent {
            display: none;
        }

        .price-info-list ul {
            margin: 0;
        }

        .price-info-list li {
            width: 100% !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .price-info-list li .price-input input {
            margin: 0;
            width: 100px;
            cursor: auto;
        }

        .hatchback-searc {
            position: relative;
            display: table;
            border-collapse: separate;
        }

        .input-group{
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .hatchback-search .form-control,
        .form-control{
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
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            margin-right: 0;
        }
        .input-group .form-control:not(:first-child):not(:last-child) {
            border-radius: 0;
        }
        .form-control,
        .hatchback-search .form-control.service-textbox {
            width: inherit;
        }
        .input-group-addon {
            padding: 10px 10px;
            background-color: #eeeeee;
            border: 1px solid #cccccc;
            border-radius: 4px;
            text-align: center;
            width: auto;
            white-space: nowrap;
            vertical-align: middle;
            display: flex;
            align-items: center;
            justify-content: center;
            border-left: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .input-group-addon:first-child {
            border-left: 1px solid #cccccc;
            border-right: 0;
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group-addon span{
            font-size: 14px;
            font-weight: normal;
            line-height: 1;
        }
        .hatchback-search > .input-group > span {
            font-weight: normal;
            margin: 0 0 0 15px;
            font-weight: 600;
            color: #555555;
            text-align: center;
            font-size: 13px;
        }
        /* [dir="rtl"] .hatchback-search .form-control{
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .hatchback-search .form-control.service-textbox {
            width: inherit;
        }
        [dir="rtl"] .input-group-addon {
            border-bottom-right-radius: 0;
            border-top-right-radius: 0;
            border-right: 0;
            border-left:1px solid #cccccc;
        }
        [dir="rtl"]   .hatchback-search > .input-group > span{
            margin: 0 15px 0 0;
        } */
        @media (max-width:480px) {
            .hatchback-search .form-control.service-textbox {
                width: calc(100% - 80px);
            }
        }
    </style>
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="assets/css/modal_alert.css"/>
    <!-- End: Top Menu-->
    <!-- Add Service page-->
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?=$langage_lbl['LBL_HEADER_MY_SERVICES'];?> </h1>
                </div>

            </div>
        </div>
    </section>
    <section class="profile-earning cx-addServices">
        <div class="profile-earning-inner">
            <?php if ($MODULES_OBJ->isEnableBiddingServices() && !empty($biddingServices)){ ?>
                <div class="innertab">
                    <button class="tablinks manageservicetab" onclick="openTabContent(event, 'manageservicecontent')" id="defaultOpen"><?=$langage_lbl['LBL_MANANGE_SERVICES'];?></button>
                    <?php if (!empty($biddingServices)){ ?>
                        <button class="tablinks biddingtab" onclick="openTabContent(event, 'biddingcontent')"> <?=$langage_lbl['LBL_MANANGE_BIDDING_SERVICES'];?></button>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="tabcontent" id="manageservicecontent">
                <!-- <?php if ($MODULES_OBJ->isEnableSearchUfxServices()){ ?>
                        <div class="serarch-part-box">
                            <input class="serarch-input-box" type="text" id="SearchService" name="SearchService" placeholder="<?=$langage_lbl['LBL_SEARCH_SERVICES']?>" value="">
                        </div>
                    <?php } ?> -->
                <div class="panel panel-info ">
                    <?php if ($MODULES_OBJ->isEnableSearchUfxServices()){ ?>
                        <div class="panel-heading clearfix">
                            <div class="serarch-part-box">
                                <input type="text" class="serach_services serarch-input-box form-control" name="" placeholder="<?=$langage_lbl['LBL_SEARCH_SERVICES'];?>">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="general-form" id="searching_div" style="display: none; min-height: auto;">
                        <div class="driver-add-vehicle">
                            <div class="add-car-services-hatch add-services-hatch add-services-taxi">
                                <div class="card-block no-services-available" style="min-height: 280px">
                                    <div> <?=$langage_lbl['LBL_SEARCHING_TXT'];?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ajaxHTML" class="panel-body">
                        <form name="frm1" method="post" action="" id="frm1">
                            <div class="general-form">
                                <!-- Service detail page -->
                                <div class="driver-add-vehicle">
                                    <?php if ($success == 1 && $content != 'biddingcontent'){ ?>
                                        <div class="form-err">
                                            <span class="msg_close">✕</span>
                                            <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="success"> <?=$langage_lbl['LBL_SERVICE_UPDATE_SUCCESS'];?></p>
                                        </div>
                                    <?php }else if ($success == 2 && $content != 'biddingcontent'){ ?>
                                        <div class="form-err">
                                            <span class="msg_close">✕</span>
                                            <p id="errmsg" class="text-muted btn-block btn btn-danger btn-rect error-login-v"> <?=$error_msg;?></p>
                                        </div>
                                    <?php }else if ($success == 3 && $content != 'biddingcontent'){ ?>
                                        <div class="form-err">
                                            <span class="msg_close">✕</span>
                                            <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="success"><?=$var_msg1;?></p>
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($vehicle_type_data)){ ?>
                                        <!--  <form name="frm1" method="post" action="" > -->
                                        <input type="hidden" name="iDriverIdNew" value="<?=isset($iDriverIdNew)?$iDriverIdNew:''?>"/>
                                        <input type="hidden" name="iCompanyId" value="<?=$iCompanyId?>"/>
                                        <input type="hidden" name="id" value="<?=$iDriverVehicleId;?>"/>
                                        <input type="hidden" name="vLicencePlate" value="<?=$vLicencePlate;?>"/>
                                        <input type="hidden" name="eType" value="<?=$eType;?>"/>
                                        <div class="add-car-services-hatch add-services-hatch add-services-taxi">
                                            <div id="InData" class="card-block ">
                                                <?php
                                                $emptySubCatData = '0';
                                                $getVehicleCatData = $obj->MySQLSelect("SELECT iVehicleCategoryId,ePriceType FROM vehicle_category");
                                                $categoryDataArr = array();
                                                for ($f = 0;$f < scount($getVehicleCatData);$f++){
                                                    $categoryDataArr[$getVehicleCatData[$f]['iVehicleCategoryId']] = $getVehicleCatData[$f]['ePriceType'];
                                                }
                                                foreach ($vehicle_type_data as $value1){
                                                    if (scount($value1['SubCategory']) > 0){ // condition added NM "no service available issue"
                                                        $emptySubCatData = empty($value1['SubCategory'])?'0':'1 ';
                                                    }
                                                    foreach ($value1['SubCategory'] as $Vehicle_Type){
                                                        if (!empty($Vehicle_Type['VehicleType']) || ($MODULES_OBJ->isEnableVideoConsultingService() && $Vehicle_Type['eVideoConsultEnable'] == "Yes")){
                                                            if ($Vehicle_type_name == 'UberX'){
                                                                $vName = 'vCategory_'.$_SESSION['sess_lang'];
                                                                $vehicleName = $Vehicle_Type[$vName];
                                                            }else{
                                                                $vehicle_typeName = $Vehicle_Type['vVehicleType'];
                                                            }
                                                            $iParentcatId = $Vehicle_Type['iParentId'];
                                                            //$sql_query = "SELECT ePriceType FROM vehicle_category WHERE iVehicleCategoryId = '" . $iParentcatId . "' ";
                                                            //$ePricetype_data = $obj->MySQLSelect($sql_query);
                                                            $ePricetype = "Service";
                                                            if (isset($categoryDataArr[$iParentcatId])){
                                                                $ePricetype = $categoryDataArr[$iParentcatId];
                                                            }
                                                            ?>
                                                            <div class="panel panel-default">
                                                                <div class="main-cat">
                                                                    <span><?=$value1['main_cat']." - ".$vehicleName;?></span>
                                                                    <?php if ($MODULES_OBJ->isEnableVideoConsultingService() && $Vehicle_Type['eVideoConsultEnable'] == 'Yes'){ ?>
                                                                        <a href="javascript:void(0);" class="gen-btn" onclick="manageVideoConsultService('<?=$Vehicle_Type['iVehicleCategoryId']?>', '<?=$value1['main_cat'].' - '.$vehicleName?>', '<?=$ePricetype?>')"><?=$langage_lbl['LBL_MANAGE_VIDEO_CONSULT_SERVICE_TXT']?></a>
                                                                    <?php } ?>
                                                                </div>
                                                                <div class="partation panel-body">
                                                                    <ul class="setings-list">
                                                                        <?php
                                                                        if (!empty($Vehicle_Type['VehicleType']) && scount($Vehicle_Type['VehicleType']) > 0){
                                                                            foreach ($Vehicle_Type['VehicleType'] as $val){
                                                                                $VehicleName1 = 'vVehicleType_'.$_SESSION['sess_lang'];
                                                                                if ($val['eFareType'] == 'Fixed'){
                                                                                    $eFareType = 'Fixed';
                                                                                    $amt_old = $val['fFixedFare'] * $ration;
                                                                                    $fAmount_old = $amt_old;
                                                                                }else if ($val['eFareType'] == 'Hourly'){
                                                                                    $eFareType = 'Per hour';
                                                                                    $amt_old1 = $val['fPricePerHour'] * $ration;
                                                                                    $fAmount_old = $amt_old1;
                                                                                }else{
                                                                                    $eFareType = '';
                                                                                    $amt_old2 = $val['fFixedFare'] * $ration;
                                                                                    $fAmount_old = $amt_old2;
                                                                                }
                                                                                $vehicle_typeName = $val[$VehicleName1];
                                                                                if (!empty($val['vLocationName'])){
                                                                                    $localization = '(Location : '.$val["vLocationName"].')';
                                                                                }else{
                                                                                    $localization = '';
                                                                                }
                                                                                $disStat = '';
                                                                                if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
                                                                                    if (in_array($val['iVehicleTypeId'],$requestedServices)){
                                                                                        $disStat = 'disabled';
                                                                                    }
                                                                                }
                                                                                ?>
                                                                                <li class="permitions-item">
                                                                                    <div class="toggle-list-inner">
                                                                                        <div class="toggle-combo">
                                                                                            <label><?php echo $vehicle_typeName; ?>
                                                                                                <div style="font-size: 12px;"><?php echo $localization; ?></div>
                                                                                            </label>
                                                                                            <span class="toggle-switch">
                                                                                        <input type="checkbox" <?php if ($ePricetype == "Provider") { ?>onchange="check_box_value(this.value);" <?php } else { ?>onchange="cTrig('vCarType1_<?=$val['iVehicleTypeId']?>')" <?php } ?> id="vCarType1_<?=$val['iVehicleTypeId']?>" class="chk vCarTypeClass" name="vCarType[]" <?php if (in_array($val['iVehicleTypeId'],$vCarTyp)) { ?>checked<?php } ?> value="<?=$val['iVehicleTypeId']?>" <?=$disStat?> />
                                                                                        <span class="toggle-base"></span>
                                                                                    </span>
                                                                                        </div>
                                                                                        <div class="check-combo">

                                                                                            <?php
                                                                                            if ($ePricetype == "Provider"){
                                                                                                $p001 = "style='display:none;'";
                                                                                                $p001Lbl = "style='display:block;'";
                                                                                                if (in_array($val['iVehicleTypeId'],$vCarTyp)){
                                                                                                    $p001 = "style='display:block;'";
                                                                                                    $p001Lbl = "style='display:none;'";
                                                                                                }
                                                                                                $fAmount_new = isset($fAmount[$val['iVehicleTypeId']])?$fAmount[$val['iVehicleTypeId']]:'';
                                                                                                $famount_val = (empty($fAmount_new))?round($fAmount_old,2):round($fAmount_new,2);
                                                                                                $price_info_class = '';
                                                                                                if ($MODULES_OBJ->isEnableCarSizeServiceTypeAmount()){
                                                                                                    $price_info_class = 'price-info-list';
                                                                                                }
                                                                                                ?>
                                                                                                <div class="hatchback-search <?=$price_info_class?>" id="amt1_<?=$val['iVehicleTypeId']?>" <?php echo $p001; ?>>
                                                                                                    <?php if ($MODULES_OBJ->isEnableCarSizeServiceTypeAmount()){
                                                                                                        $_POST['eFareType'] = $val['eFareType'];
                                                                                                        $_POST['iVehicleTypeId'] = $val['iVehicleTypeId'];
                                                                                                        $_POST['data_action'] = 'GET';
                                                                                                        include "vehicle_type_size_price_info.php";
                                                                                                    }else{ ?>
                                                                                                        <input type="hidden" name="desc" id="desc_<?=$val['iVehicleTypeId']?>" value="<?=$val[$VehicleName1]?>">
                                                                                                        <?php if ($val['eFareType'] != 'Regular'){ ?>

                                                                                                            <div class="input-group col-md-12">
                                                                                                                <?php if ($currencyData['eReverseSymbolEnable'] == "No"){ ?>
                                                                                                                    <div class="input-group-addon">
                                                                                                                        <span class="input-group-text" id="basic-addon1"><?=$vSymbol;?></span>
                                                                                                                    </div>
                                                                                                                <?php } ?>

                                                                                                                <input class=" numberPlay form-control service-textbox" type="text" name="fAmount[<?=$val['iVehicleTypeId']?>]" value="<?=$famount_val;?>" placeholder="Enter Amount for <?=$val[$VehicleName1]?>" id="fAmount_<?=$val['iVehicleTypeId']?>" maxlength="10">


                                                                                                                <?php if ($currencyData['eReverseSymbolEnable'] == "Yes"){ ?>
                                                                                                                    <div class="input-group-addon">
                                                                                                                        <span class="input-group-text" id="basic-addon1"><?=$vSymbol;?></span>
                                                                                                                    </div>
                                                                                                                <?php } ?>
                                                                                                                <span><?php echo $eFareType; ?></span>
                                                                                                            </div>
                                                                                                            <label id="ServiceText_<?=$val['iVehicleTypeId']?>" <?php echo $p001Lbl; ?> class="ServiceText"><?=$langage_lbl['LBL_ENABLE_SERVICE_PRICE_TXT'];?></label>

                                                                                                        <?php }
                                                                                                    } ?>
                                                                                                </div>
                                                                                            <?php }else{ ?>
                                                                                                <label id="defaultText_<?=$val['iVehicleTypeId']?>"><?=$langage_lbl['LBL_PRICE_FEATURE_APPLICABLE_SERVICE_TXT'];?></label>
                                                                                            <?php }
                                                                                            if (!empty($disStat) && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes' && $ePricetype == "Provider"){ ?>
                                                                                                <br>
                                                                                                <label><?=$langage_lbl['LBL_SERVICE_REQUEST_PENDING'];?></label>
                                                                                            <?php }
                                                                                            ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </li>
                                                                            <?php }
                                                                        }else{ ?>
                                                                            <li class="permitions-item" style="margin: 0"></li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                if ($emptySubCatData == '0'){ ?>
                                                    <div> <?=$langage_lbl['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                                                <?php } ?>
                                            </div>
                                            <div class="card-block" id="NoData" style="display: none"> <?=$langage_lbl['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                                        </div>
                                        <!-- -->
                                        <!-- </form> -->
                                    <?php }else{ ?>
                                        <div> <?=$langage_lbl['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="button-block justify-left">
                                <input type="submit" class="save-vehicle gen-btn" name="submit1" id="submit1" value="<?=$langage_lbl['LBL_SUBMIT_BUTTON_TXT'];?>" onclick="return check_empty();">
                                <!--<a href="#" onclick="add_driver_form();" class="gen-btn">Cancel</a>-->
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="tabcontent" id="biddingcontent">
                <!-- <?php if ($MODULES_OBJ->isEnableSearchUfxServices()){ ?>
                    <div class="serarch-part-box">
                       <input class="serarch-input-box" type="text" id="SearchBids" name="SearchBids" placeholder="<?=$langage_lbl['LBL_Search']." ".$langage_lbl['LBL_BIDDING_TXT']?>" value="">
                    </div>
                <?php } ?> -->

                <div class="panel panel-info">
                    <?php if ($success == 1 && $content == 'biddingcontent'){ ?>
                        <div class="form-err">
                            <span class="msg_close">✕</span>
                            <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="success"> <?=$langage_lbl['LBL_SERVICE_UPDATE_SUCCESS'];?></p>
                        </div>
                    <?php }else if ($success == 2 && $content == 'biddingcontent'){ ?>
                        <div class="form-err">
                            <span class="msg_close">✕</span>
                            <p id="errmsg" class="text-muted btn-block btn btn-danger btn-rect error-login-v"> <?=$error_msg;?></p>
                        </div>
                    <?php }else if ($success == 3 && $content == 'biddingcontent'){ ?>
                        <div class="form-err">
                            <span class="msg_close">✕</span>
                            <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="success"><?=$var_msg1;?></p>
                        </div>
                    <?php } ?>
                    <div class="panel-heading clearfix">
                        <div class="serarch-part-box">
                            <form name="ufx_service_address" id="ufx_service_address" method="post" action="" enctype="multipart/form-data">
                                <input type="text" class="form-control" id="vWorkLocation" name="vWorkLocation" value="<?php echo $vWorkLocation; ?>" style="width: 25%">
                                <input type="hidden" name="vWorkLocationLatitude" id="vWorkLocationLatitude" value="<?php echo $vWorkLocationLatitude; ?>">
                                <input type="hidden" name="vWorkLocationLongitude" id="vWorkLocationLongitude" value="<?php echo $vWorkLocationLongitude; ?>">
                                <input type="hidden" name="iDriverIdNew" value="<?=isset($iDriverIdNew)?$iDriverIdNew:''?>"/>
                                <input type="submit" class="save-vehicle gen-btn" name="submitaddress" id="submitaddress" value="Submit">
                            </form>
                        </div>
                    </div>
                    <br/>
                    <div class="panel-heading clearfix">
                        <div class="serarch-part-box">
                            <input type="text" class="serach_bids form-control serarch-input-box" name="" placeholder="<?=$langage_lbl['LBL_SEARCH_BIDS'];?>">
                        </div>
                    </div>
                    <div class="general-form" id="searching_div_bid" style="display: none; min-height: auto;">
                        <div class="driver-add-vehicle">
                            <div class="add-car-services-hatch add-services-hatch add-services-taxi">
                                <div class="card-block no-services-available" style="min-height: 280px">
                                    <div> <?=$langage_lbl['LBL_SEARCHING_TXT'];?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--   <div id="ajaxBidHTML"> -->
                    <!-- <form name="frm2" method="post" action="" id="frm2"> -->
                    <div class="profile-earning-inner panel-body">
                        <div class="general-form">
                            <!-- Service detail page -->
                            <div class="driver-add-vehicle">
                                <?php if (!empty($biddingServices)){ ?>
                                    <form name="frm2" method="post" action="" id="frm2">
                                        <div id="ajaxBidHTML">
                                            <div class="add-car-services-hatch add-services-hatch add-services-taxi">
                                                <input type="hidden" name="iDriverIdNew" value="<?=isset($iDriverIdNew)?$iDriverIdNew:''?>"/>
                                                <div class="card-block" id="InDataBid">
                                                    <?php
                                                    $emptySubCatData = '0';
                                                    foreach ($biddingServices as $value1){
                                                        if (scount($value1['SubCategory']) > 0){
                                                            $emptySubCatData = empty($value1['SubCategory'])?'0':'1 ';
                                                        }
                                                        ?>
                                                        <div class="panel panel-default">
                                                            <div class="main-cat">
                                                                <span><?=$value1['vCategory'];?></span>
                                                            </div>
                                                            <div class="partation panel-body">
                                                                <ul class="setings-list">
                                                                    <?php foreach ($value1['SubCategory'] as $SubCategoryval){
                                                                        $disStat = '';
                                                                        if (in_array($SubCategoryval['iBiddingId'],$biddingdriverrequest)){
                                                                            $disStat = 'disabled';
                                                                        } ?>
                                                                        <li class="permitions-item-bids">
                                                                            <div class="toggle-list-inner">
                                                                                <div class="toggle-combo">
                                                                                    <label><?php echo $SubCategoryval['vTitle']; ?>
                                                                                    </label>
                                                                                    <span class="toggle-switch">
                                                                            <input type="checkbox" id="selectedbiddingdriverservice<?=$SubCategoryval['iBiddingId']?>" class="chk vCarTypeClass" name="selectedbiddingdriverservice[]" <?php if (in_array($SubCategoryval['iBiddingId'],$selectedbiddingdriverservice)) { ?>checked<?php } ?> value="<?=$SubCategoryval['iBiddingId']?>" <?=$disStat?> onchange="cTrigBid('selectedbiddingdriverservice<?=$SubCategoryval['iBiddingId']?>')"/>
                                                                            <span class="toggle-base"></span>
                                                                        </span>
                                                                                </div>
                                                                                <div class="check-combo">
                                                                                    <?php if (!empty($disStat)){ ?>
                                                                                        <br><br>
                                                                                        <small><?=$langage_lbl['LBL_SERVICE_REQUEST_PENDING'];?></small>
                                                                                        <br>
                                                                                    <?php } ?>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <!-- <div class="button-block justify-left">
                                                        <input type="submit" class="save-vehicle gen-btn" name="submitbid" id="submitbid" value="<?=$langage_lbl['LBL_SUBMIT_BUTTON_TXT'];?>" >
                                                    </div> -->
                                                    <?php if ($emptySubCatData == '0'){ ?>
                                                        <div> <?=$langage_lbl['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                                                    <?php } ?>
                                                </div>
                                                <div class="card-block" id="NoDataBid" style="display: none"> <?=$langage_lbl['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                                            </div>
                                            <div class="button-block justify-left">
                                                <input type="submit" class="save-vehicle gen-btn" name="submitbid" id="submitbid" value="<?=$langage_lbl['LBL_SUBMIT_BUTTON_TXT'];?>">
                                            </div>
                                        </div>
                                    </form>
                                <?php }else{ ?>
                                    <div class="add-car-services-hatch add-services-hatch add-services-taxi"><?=$langage_lbl['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <!-- </form> -->
                    <!--  </div> -->
                </div>
            </div>
        </div>

    </section>
    <!-- footer part -->
    <!-- <div class="row loding-action" id="loaderIcon" style="display:none;">
        <div align="center">
            <img src="default.gif">
            <p></p>
        </div>
    </div> -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="assets/js/modal_alert.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>"></script>
<script>

    var currencyData = '<?php echo $currencyDataJson; ?>';
    let currencyDataJson;
    if (currencyData) {
        currencyDataJson = JSON.parse(currencyData);
    }

    const decimalSeparator = currencyDataJson.eReverseformattingEnable === "Yes" ? '.' : ',';
    const thousandSeparator = currencyDataJson.eReverseformattingEnable === "Yes" ? ',' : '.';
    const numberFormatter = new NumberFormatter(decimalSeparator, thousandSeparator, 2,15);

    numberFormatter.initializeNumberFormatting('.numberPlay');


    var phonedetailAjaxAbort;
    var driverReqModule = '<?php echo $ENABLE_DRIVER_SERVICE_REQUEST_MODULE; ?>';

    function check_box_value(val1) {
        if ($('#vCarType1_' + val1).is(':checked')) {
            $("#amt1_" + val1).show();
            $("#fAmount_" + val1).focus();
        } else {
            if (driverReqModule == 'Yes') {
                //alert('<?= addslashes($langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']); ?>');
                window.val1 = val1;
                show_alert("<?= addslashes($langage_lbl_admin['LBL_ATTENTION']); ?>", "<?= addslashes($langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']); ?>", "<?= addslashes($langage_lbl_admin['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                        console.log("#amt1_" + val1);
                        $("#amt1_" + val1).hide();
                    }
                }, true, true, true);
            }
            // $("#amt1_" + val1).hide();
        }
    }

    function cTrig(clickedid) {
        if ($('#' + clickedid).is(':checked')) {
            return true;
        } else {
            if (driverReqModule == 'Yes') {
                show_alert("<?= addslashes($langage_lbl_admin['LBL_ATTENTION']); ?>", "<?= addslashes($langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']); ?>", "<?= addslashes($langage_lbl_admin['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                        //location.reload();
                    }
                }, true, true, true);
            }
        }
    }

    function cTrigBid(clickedid) {
        //console.log(clickedid);
        if ($('#' + clickedid).is(':checked')) {
            return true;
        } else {
            show_alert("<?= addslashes($langage_lbl_admin['LBL_ATTENTION']); ?>", "<?= addslashes($langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']); ?>", "<?= addslashes($langage_lbl_admin['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                if (btn_id == 0) {
                    //location.reload();
                }
            }, true, true, true);
            // alert('<?= addslashes($langage_lbl['LBL_UNSELECT_CHECKBOX_FOR_SERVICE']); ?>');
        }
    }

    function check_empty() {
        var err = 0;
        $("input[type=checkbox]:checked").each(function () {
            var tmp = "fAmount_" + $(this).val();
            var tmp1 = "desc_" + $(this).val();
            var tmp1_val = $("#" + tmp1).val();
            if ($("#" + tmp).val() == "") {
                alert('Please Enter Amount for ' + tmp1_val + '.');
                $("#" + tmp).focus();
                err = 1;
                return false;
            }
        });
        if (err == 1) {
            return false;
        } else {
            //event.preventDefault();
            isEnableServiceTypeWiseProviderDocument = '<?= $isEnableServiceTypeWiseProviderDocument; ?>';
            var matches = [];
            $(".vCarTypeClass:checked").each(function () {
                matches.push(this.value);
            });
            var isRedirectToDocumentUploadPage = "No";
            if (matches != "" && isEnableServiceTypeWiseProviderDocument == "Yes") {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>getDocumentServiceTypeWise.php',
                    'AJAX_DATA': {
                        serviceIds: matches
                    },
                    'REQUEST_ASYNC': false
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    // console.log(response);
                    if (response.action == "1") {
                        var responseData = response.result;
                        var returnedData = JSON.parse(responseData);
                        documentCount = returnedData.documentCount
                        if (documentCount > 0) {
                            if (confirm('<?= addslashes($langage_lbl['LBL_SERVICE_ADD_SUCCESS_NOTE_TWO']); ?>')) {
                                isRedirectToDocumentUploadPage = "Yes";
                            }
                        }
                    } else {
                        console.log(response.result);
                    }
                });
            }
            $('#frm1').append('<input type="hidden" name="isRedirectToDocumentUploadPage" id="isRedirectToDocumentUploadPage" value="" />');
            document.getElementById('isRedirectToDocumentUploadPage').value = isRedirectToDocumentUploadPage;
            jQuery('#frm1').submit();
        }
    }

    function manageVideoConsultService(iVehicleCategoryId, title, ePriceType) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_manage_provider_charges.php',
            'AJAX_DATA': {iDriverId: '<?= $iDriverId ?>', iVehicleCategoryId: iVehicleCategoryId, method: 'GET_DATA'},
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (res) {
            if (res.action == "1") {
                var response = res.result;
                $no = '';
                if (response.eVideoConsultEnableProvider == 'No') {
                    $no = "checked";
                }
                $yes = '';
                if (response.eVideoConsultEnableProvider == 'Yes' && response.eStatus == 'Active') {
                    $yes = "checked";
                }
                if ($yes == '' && $no == '') {
                    $no = "checked";
                }
                var addNotePopupContent = '';
                addNotePopupContent += '<div style="margin-bottom: 5px"><?= $langage_lbl['LBL_VIDEO_CONSULT_SERVICE_CHARGE_TXT'] ?></div>';

                const numberCharges = response.eVideoConsultServiceCharge;
                if (ePriceType == "Provider") {
                    /*addNotePopupContent += '<div><span class = "consult_price_symbol"><?php echo $vSymbol ?></span><input  type="text" value = "' + response.eVideoConsultServiceCharge + '" class="form-control w-95" id="eVideoConsultServiceCharge"></div>';*/

                    let symbolHTML = '<div class="input-group-addon"><span class="input-group-text" id="basic-addon1"><?php echo $vSymbol ?></span></div>';
                    let inputHTML = '<input type="text" value="' + numberCharges + '" class=" numberPlay form-control" id="eVideoConsultServiceCharge">';
                    if (currencyDataJson.eReverseSymbolEnable === "Yes") {
                        addNotePopupContent += `<div class="input-group">${inputHTML}${symbolHTML}</div>`;
                    } else {
                        addNotePopupContent += `<div class="input-group">${symbolHTML}${inputHTML}</div>`;
                    }


                } else {
                    addNotePopupContent += '<div><span class = "consult_price_symbol"><?php echo $vSymbol ?></span><input type="text" value="' + response.eVideoConsultServiceCharge + '" class="form-control w-95" disabled><input type="hidden" value="' + response.eVideoConsultServiceCharge + '" id="eVideoConsultServiceCharge"></div>';
                }
                addNotePopupContent += '<span class="error" id="eVideoConsultServiceChargeError"><?= $langage_lbl['LBL_FEILD_REQUIRD'] ?></span>';
                addNotePopupContent += '<span> <br> <?= $langage_lbl['LBL_Status'] ?>: &emsp;</span>';
                addNotePopupContent += '<label class="video_consult_status"><input ' + $yes + ' value = "Yes" type="radio" name = "eVideoConsultEnableProvider"  id="eVideoConsultEnableProvider"> On &emsp;</label>';
                addNotePopupContent += '<label class="video_consult_status"><input ' + $no + ' value = "No" type="radio" name = "eVideoConsultEnableProvider"  id="eVideoConsultEnableProvider"> Off</label>';
                addNotePopupContent += '<div style="margin: 15px 0 5px"><?= $langage_lbl['LBL_VIDEO_CONSULT_SERVICE_DESC_TXT'] ?></div>';
                addNotePopupContent += '<div><textarea  type="text" class="form-control w-95" name = "eVideoServiceDescription" id="eVideoServiceDescription">' + response.eVideoServiceDescription + '</textarea></div>';
                addNotePopupContent += '<span class="error" id="eVideoServiceDescriptionError"><?= $langage_lbl['LBL_FEILD_REQUIRD'] ?></span>';
                addNotePopupContent += '<input type="hidden" id="iVehicleCategoryId" value="' + iVehicleCategoryId + '">';
                show_alert(title, addNotePopupContent, "<?= $langage_lbl['LBL_BTN_SUBMIT_TXT'] ?>", "<?= $langage_lbl['LBL_BTN_CANCEL_TXT'] ?>", "", function (btn_id) {
                    if (btn_id == 0) {
                        updateVideoConsultService();
                    } else if (btn_id == 1) {
                        closeAlertPopup();
                    }
                }, false, false);

                setTimeout(function () {
                    numberFormatter.initializeNumberFormatting('.numberPlay');
                }, 100);
            } else {
            }
        });
    }

    function updateVideoConsultService() {
        var iVehicleCategoryId = $('#iVehicleCategoryId').val();
        var eVideoConsultServiceCharge = $('#eVideoConsultServiceCharge').val();
        var eVideoServiceDescription = $('#eVideoServiceDescription').val();
        var eVideoConsultEnableProvider = $('input[name="eVideoConsultEnableProvider"]:checked').val();
        if (eVideoConsultServiceCharge.trim() == "") {
            $('#eVideoConsultServiceCharge').val("").focus();
            $("#eVideoConsultServiceChargeError").html("<?= $langage_lbl['LBL_FEILD_REQUIRD'] ?>").show();
            return false;
        }
        if (eVideoConsultServiceCharge.trim() <= 0) {
            $('#eVideoConsultServiceCharge').val("").focus();
            $("#eVideoConsultServiceChargeError").html("<?= isset($langage_lbl['LBL_VALUE_GREATER_THAN_ZERO_MSG'])?$langage_lbl['LBL_VALUE_GREATER_THAN_ZERO_MSG']:'' ?>").show();
            return false;
        }
        if (eVideoConsultEnableProvider == 'Yes') {
            if (eVideoServiceDescription.trim() == "") {
                $('#eVideoServiceDescription').val("").focus();
                $("#eVideoServiceDescriptionError").html("<?= $langage_lbl['LBL_FEILD_REQUIRD'] ?>").show();
                return false;
            }
        }
        closeAlertPopup();
        <?php if(!empty($ration)) { ?>
        var eVideoConsultServiceCharge = eVideoConsultServiceCharge / <?= $ration ?>;
        <?php } else { ?>
        var eVideoConsultServiceCharge = eVideoConsultServiceCharge;
        <?php } ?>
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_manage_provider_charges.php',
            'AJAX_DATA': {
                iDriverId: '<?= $iDriverId ?>',
                iVehicleCategoryId: iVehicleCategoryId,
                eVideoConsultServiceCharge: eVideoConsultServiceCharge,
                eVideoConsultEnableProvider: eVideoConsultEnableProvider,
                eVideoServiceDescription: eVideoServiceDescription,
                method: 'UPDATE_DATA'
            },
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (res) {
            if (res.action == "1") {
                closeAlertPopup();
            } else {
            }
        });
    }

    function closeAlertPopup() {
        $('.custom-modal-first-div').removeClass('active');
    }

    // Tab Script
    <?php if (empty($biddingServices) || $ENABLE_BIDDING_SERVICES == 'No') { ?>
    document.getElementById("manageservicecontent").style.display = "block";
    <?php } else { ?>
    // Get the element with id="defaultOpen" and click on it
    document.getElementById("defaultOpen").click();
    <?php } ?>
    function openTabContent(evt, Pagename) {
        // Declare all variables
        var i, tabcontent, tablinks;
        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(Pagename).style.display = "block";
        evt.currentTarget.className += " active";
    }

    $("#ufx_service_address").submit(function () {
        var vWorkLocation = $('#vWorkLocation').val();
        if (vWorkLocation === '') {
            alert('<?php echo addslashes($langage_lbl['LBL_ENTER_LOC_HINT_TXT']);?>');
            return false;
        }
    });
    $(document).ready(function () {
        var from = document.getElementById('vWorkLocation');
        autocomplete_from1 = new google.maps.places.Autocomplete(from);
        google.maps.event.addListener(autocomplete_from1, 'place_changed', function () {
            var placeaddress = autocomplete_from1.getPlace();
            $('#vWorkLocationLatitude').val(placeaddress.geometry.location.lat());
            $('#vWorkLocationLongitude').val(placeaddress.geometry.location.lng());
        });
        <?php if ($content == 'biddingcontent'){ ?>
        openTabContent(event, 'biddingcontent');
        $('.biddingtab').addClass('active');
        <?php } else if ($content == 'manageservicecontent'){  ?>
        openTabContent(event, 'manageservicecontent');
        $('.manageservicetab').addClass('active');
        <?php }?>

        $('.serach_services').keyup(function () {
            var value = $(this).val();
            var items = $(this).closest('.panel').find('.permitions-item');
            const NoData = document.getElementById("NoData");
            const InData = document.getElementById("InData");
            if (value != "" && value != undefined && value != null) {
                items.hide();
                let i = 0;
                items.each(function () {
                    var text = $(this).find('label').text().toLowerCase();
                    value = value.toLowerCase();
                    //console.log(value);
                    //console.log(text.search(value));
                    if (text.search(value) >= 0) {
                        $(this).show();
                        i++;
                    }
                    var maincat = $(this).closest('.panel').find('.main-cat');
                    var titletext = maincat.text().toLowerCase();
                    value = value.toLowerCase();
                    if (titletext.search(value) >= 0) {
                        $(this).show();
                        i++;
                    }
                });
                const NoData = document.getElementById("NoData");
                const InData = document.getElementById("InData");
                if (i == 0) {
                    NoData.style.display = "flex";
                    NoData.style.justifyContent = "center";
                    InData.style.display = "none";
                } else {
                    InData.style.display = "block";
                    NoData.style.display = "none";
                }
            } else {
                items.show();
                InData.style.display = "block";
                NoData.style.display = "none";
            }
            if ($(this).closest('.panel').find('.panel').length > 0) {
                $(this).closest('.panel').find('.panel').find('.serach_services').val("");
                $(this).closest('.panel').find('.panel').show();
                $(this).closest('.panel').find('.panel').each(function () {
                    if ($(this).find(".permitions-item:visible").length == 0) {
                        $(this).hide();
                    }
                });
            }
        });
        $('.serach_bids').keyup(function () {
            var value = $(this).val();
            var items = $(this).closest('.panel').find('.permitions-item-bids');
            const NoData = document.getElementById("NoDataBid");
            const InData = document.getElementById("InDataBid");
            if (value != "" && value != undefined && value != null) {
                items.hide();
                let i = 0;
                items.each(function () {
                    /*console.log(maincat);*/
                    var text = $(this).find('label').text().toLowerCase();
                    value = value.toLowerCase();
                    if (text.search(value) >= 0) {
                        $(this).show();
                        i++;
                    }
                    var maincat = $(this).closest('.panel').find('.main-cat');
                    var titletext = maincat.text().toLowerCase();
                    value = value.toLowerCase();
                    if (titletext.search(value) >= 0) {
                        $(this).show();
                        i++;
                    }
                });
                if (i == 0) {
                    NoData.style.display = "flex";
                    NoData.style.justifyContent = "center";
                    InData.style.display = "none";
                } else {
                    InData.style.display = "block";
                    NoData.style.display = "none";
                }
            } else {
                InData.style.display = "block";
                NoData.style.display = "none";
                items.show();
            }
            if ($(this).closest('.panel').find('.panel').length > 0) {
                $(this).closest('.panel').find('.panel').find('.serach_services').val("");
                $(this).closest('.panel').find('.panel').show();
                $(this).closest('.panel').find('.panel').each(function () {
                    if ($(this).find(".permitions-item-bids:visible").length == 0) {
                        $(this).hide();
                    }
                });
            }
        });
    });
</script>
<!-- End: Footer Script -->
</body>
</html>