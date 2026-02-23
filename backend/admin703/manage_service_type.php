<?php
include_once('../common.php');
$start = @date("Y");
$end = '1970';
$tbl_name = 'driver_vehicle';
$tbl_name1 = 'service_pro_amount';
$script = 'Driver';
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
$sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '".$iDriverId."' AND eType='UberX'";
$db_drv_veh = $obj->MySQLSelect($sql);
$id = isset($_POST['id'])?$_POST['id']:$db_drv_veh[0]['iDriverVehicleId'];
$action = ($id != '')?'Edit':'Add';
$success = isset($_REQUEST['success'])?$_REQUEST['success']:0;
$backlink = isset($_POST['backlink'])?$_POST['backlink']:'';
$redirectToDocumentPage = isset($_POST['redirectToDocumentPage'])?$_POST['redirectToDocumentPage']:'';
$previousLink = isset($_POST['backlink'])?$_POST['backlink']:'';
$vLicencePlate = isset($_POST['vLicencePlate'])?$_POST['vLicencePlate']:'';
$iCompanyId = isset($_POST['iCompanyId'])?$_POST['iCompanyId']:'';
$iMakeId = isset($_POST['iMakeId'])?$_POST['iMakeId']:'3';
$iModelId = isset($_POST['iModelId'])?$_POST['iModelId']:'1';
$fAmount = isset($_POST['fAmount'])?$_POST['fAmount']:'';

$iYear = isset($_POST['iYear'])?$_POST['iYear']:Date('Y');
$eStatus_check = isset($_POST['eStatus'])?$_POST['eStatus']:'off';
$vCarType = isset($_POST['vCarType'])?$_POST['vCarType']:'';
$eStatus = ($eStatus_check == 'on')?'Active':'Inactive';
$eType = isset($_POST['eType'])?$_POST['eType']:'UberX';
$content = isset($_REQUEST['content'])?$_REQUEST['content']:'manageservicecontent';
$sql = "select * from driver_vehicle where iDriverVehicleId = '".$id."' ";
$db_mdl = $obj->MySQLSelect($sql);
$sql = "select iCompanyId from `register_driver` where iDriverId = '".$iDriverId."'";
$db_usr = $obj->MySQLSelect($sql);
$iCompanyId = $db_usr[0]['iCompanyId'];
$sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
$db_usrcurrency = $obj->MySQLSelect($sql1);
$db_currency[0]['vName'] = $db_usrcurrency[0]['vName'];
$vSymbol = $db_usrcurrency[0]['vSymbol'];
$ration = $db_usrcurrency[0]['Ratio'];
$sql = "select iCompanyId, vCurrencyDriver, eStatus, vWorkLocationLatitude, vWorkLocation, vWorkLocationLongitude from `register_driver` where iDriverId = '".$iDriverId."'";
$dbDriver = $obj->MySQLSelect($sql);
$vWorkLocation = $dbDriver[0]['vWorkLocation'];
$vWorkLocationLatitude = $dbDriver[0]['vWorkLocationLatitude'];
$vWorkLocationLongitude = $dbDriver[0]['vWorkLocationLongitude'];
/* $sql = "SELECT * from make WHERE eStatus='Active' ORDER By vMake ASC";
  $db_make = $obj->MySQLSelect($sql);

  $sql = "SELECT * from company WHERE eStatus='Active'";
  $db_company = $obj->MySQLSelect($sql); */
if (!$userObj->hasPermission('manage-provider-services')){
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to manage service.';
    header("Location:driver.php?iDriverId=".$iDriverId);
    exit;
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
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_ADDRESS_UPDATE_MSG'];
    header("Location:driver.php?success=1&iDriverId=".$iDriverId."");
    exit;
}
if (isset($_POST['biddingsubmit'])){
    if (SITE_TYPE == 'Demo' && $id != ''){
        $_SESSION['success'] = 2;
        header("Location:driver.php?iDriverId=".$iDriverId);
        exit;
    }
    if (empty($vWorkLocationLatitude) && empty($vWorkLocationLongitude)){
        /*  $error_msg = $langage_lbl['LBL_ENTER_LOC_HINT_TXT'];*/
        $_SESSION['success'] = '3';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_ENTER_LOC_HINT_TXT'];
        header("Location:driver.php?iDriverId=".$iDriverId."&success=3&content=biddingcontent");
        exit;
    }
    $vBiddingId = implode(",",$_REQUEST['selectedbiddingdriverservice']);
    $data = $BIDDING_OBJ->biddingDriverService('webservice',$iDriverId);
    if (scount($data) == 0){
        $creDataArr['vBiddingId'] = $vBiddingId;
        $creDataArr['iDriverId'] = $iDriverId;
        $biddingid = $obj->MySQLQueryPerform('bidding_driver_service',$creDataArr,'insert');
    }else{
        $creDataArr['vBiddingId'] = $vBiddingId;
        $where = 'iDriverId = "'.$iDriverId.'"';
        $biddingid = $BIDDING_OBJ->updatebiddingDriverService('webservice',$creDataArr,$where);
    }
    if ($action == "Add"){
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }else{
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    if ($redirectToDocumentPage != ""){
        header("location:".$redirectToDocumentPage);
    }else{
        header("location:".$backlink);
    }
}
if (isset($_POST['submit'])){
    if (SITE_TYPE == 'Demo' && $id != ''){
        $_SESSION['success'] = 2;
        header("Location:driver.php?iDriverId=".$iDriverId);
        exit;
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    //Commented By HJ On 10-12-2019 As Per Discuss With KS B'coz in app not checked this validation start
    /*if (empty($_REQUEST['vCarType'])) {
        $validobj->add_fields($_POST['vCarType'], 'req', 'You must select at least one service type.');
    }*/
    //Commented By HJ On 10-12-2019 As Per Discuss With KS B'coz in app not checked this validation End
    $error = $validobj->validate();
    if ($error){
        $success = 3;
        $newError = $error;
        //exit;
    }else{
        if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
            $vLicencePlate = 'My Services';
        }else{
            $vLicencePlate = $vLicencePlate;
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Edit'){
            $str = ' ';
        }else{
            $eStatus = 'Active';
        }
        if (is_array($_REQUEST['vCarType'])){
            $cartype = implode(",",$_REQUEST['vCarType']);
        }
        if ($id != ''){
            $q = "UPDATE ";
            $where = " WHERE `iDriverId` = '".$iDriverId."' AND `iDriverVehicleId` = '".$id."'";
        }
        $query = $q." `".$tbl_name."` SET
        `iModelId` = '".$iModelId."',
        `vLicencePlate` = '".$vLicencePlate."',
        `iYear` = '".$iYear."',
        `iMakeId` = '".$iMakeId."',
        `iCompanyId` = '".$iCompanyId."',
        `iDriverId` = '".$iDriverId."',
        `eStatus` = 'Active',
        `eType` = '".$eType."',
        `vCarType` = '".$cartype."' $str".$where;
        $obj->sql_query($query);
        $id = ($id != '')?$id:$obj->GetInsertId();
        if ($id != "" && $db_mdl[0]['eStatus'] != $eStatus){
            if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes'){
                $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName
                    FROM driver_vehicle dv, register_driver rd, make m, model md, company c
                    WHERE dv.eStatus != 'Deleted' AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '".$id."'";
                $data_email_drv = $obj->MySQLSelect($sql23);
                $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
                $maildata['NAME'] = $data_email_drv[0]['vName'];
                $maildata['EMAIL_NAME'] = $data_email_drv[0]['vName'];
                $maildata['DETAIL'] = "Your ".$langage_lbl_admin['LBL_TEXI_ADMIN']." ".$data_email_drv[0]['vTitle']." For COMPANY ".$data_email_drv[0]['companyFirstName']." is temporarly ".$eStatus;
                $COMM_MEDIA_OBJ->SendMailToMember("ACCOUNT_STATUS",$maildata);
            }
        }
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
            $maildata['NAME'] = $db_status[0]['vName']." ".$db_status[0]['vLastName'];
            $maildata['EMAIL_NAME'] = $db_status[0]['vName']." ".$db_status[0]['vLastName'];
            $maildata['DETAIL'] = "Thanks for adding your ".$langage_lbl_admin['LBL_TEXI_ADMIN'].".<br />We will soon verify and check it's documentation and proceed ahead with activating your account.<br />We will notify you once your account become active and you can then take ".$langage_lbl_admin['LBL_RIDE_TXT_ADMIN']." with ".$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'].".";
            $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING",$maildata);
        }
        if ($action == "Add"){
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        }else{
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        if ($redirectToDocumentPage != ""){
            header("location:".$redirectToDocumentPage);
        }else{
            header("location:".$backlink);
        }
    }
}
$fAmount = array();
// for Edit
if ($action == 'Edit'){
    $sql = "SELECT t.*,t1.fAmount,t1.iServProAmntId,t1.iVehicleTypeId AS `VehicleId`,t1.iDriverVehicleId AS `DriverVehilceId` FROM $tbl_name AS t LEFT JOIN $tbl_name1 t1 ON t.iDriverVehicleId=t1.iDriverVehicleId
            WHERE t.iDriverId = '".$iDriverId."' AND t.iDriverVehicleId = '".$db_drv_veh[0]['iDriverVehicleId']."'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_data);die;
    $vLabel = $id;
    if (scount($db_data) > 0){
        foreach ($db_data as $key => $value){
            $iMakeId = $value['iMakeId'];
            $iModelId = $value['iModelId'];
            $vLicencePlate = $value['vLicencePlate'];
            $iYear = $value['iYear'];
            $eCarX = $value['eCarX'];
            $eCarGo = $value['eCarGo'];
            $iDriverId = $value['iDriverId'];
            $vCarType = $value['vCarType'];
            $iCompanyId = $value['iCompanyId'];
            $eStatus = $value['eStatus'];
            $iDriverVehicleId = $value['iDriverVehicleId'];
            $eType = $value['eType'];
            $amt = $value['fAmount'] * $ration;
            $fAmount[$value['VehicleId']] = $amt;
            //echo "<pre>";print_r($fAmount);die;
        }
    }
    if ($MODULES_OBJ->isEnableBiddingServices()){
        $biddingdriverservice = $BIDDING_OBJ->biddingDriverService('webservice',$iDriverId);
        $selectedbiddingdriverservice = explode(',',$biddingdriverservice[0]['vBiddingId']);
    }
}
$vCarTyp = explode(",",$vCarType);
//$Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;
if ($APP_TYPE == 'Delivery'){
    $Vehicle_type_name = 'Deliver';
}else if ($APP_TYPE == 'Ride-Delivery-UberX'){
    $Vehicle_type_name = 'UberX';
}else{
    $Vehicle_type_name = $APP_TYPE;
}
if ($Vehicle_type_name == "Ride-Delivery"){
    $vehicle_type_sql = "SELECT * FROM  vehicle_type WHERE(eType ='Ride' OR eType ='Deliver') AND iCountryId='-1'";
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
            $whereParentId = " AND vc.iVehicleCategoryId = '".$parent_ufx_catid."'";
        }
        $getvehiclecat = "SELECT vc.iVehicleCategoryId, vc.vCategory_".$default_lang." AS main_cat FROM ".$sql_vehicle_category_table_name." AS vc WHERE vc.eStatus='Active' AND vc.iParentId='0' $whereParentId $ssql";
        $vehicle_type_data = $obj->MySQLSelect($getvehiclecat);
        $i = 0;
        foreach ($vehicle_type_data as $key => $val){
            $vehicle_type_sql = "SELECT vc.eVideoConsultEnable, vt.vVehicleType,vc.iParentId,vc.vCategory_".$_SESSION['sess_lang'].",vc.iVehicleCategoryId from  vehicle_type as vt  left join ".$sql_vehicle_category_table_name." as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='".$Vehicle_type_name."' AND vc.iParentId ='".$val['iVehicleCategoryId']."'  AND vc.eStatus='Active' GROUP BY vc.iVehicleCategoryId";
            $vehicle_type_dataOld = $obj->MySQLSelect($vehicle_type_sql);
            if ($MODULES_OBJ->isEnableVideoConsultingService()){
                $vehicle_type_dataOld = $obj->MySQLSelect("SELECT * FROM vehicle_category WHERE iParentId = '".$val['iVehicleCategoryId']."' AND eStatus = 'Active' ");
            }
            $vehicle_type_data[$i]['SubCategory'] = $vehicle_type_dataOld;
            $j = 0;
            foreach ($vehicle_type_dataOld as $subkey => $subvalue){
                $vehicle_type_sql1 = "SELECT vt.*,vc.*,lm.vLocationName FROM vehicle_type AS vt LEFT JOIN ".$sql_vehicle_category_table_name." AS vc ON vt.iVehicleCategoryId = vc.iVehicleCategoryId LEFT JOIN location_master AS lm ON lm.iLocationId = vt.iLocationid WHERE vt.eType='".$Vehicle_type_name."' AND vc.iVehicleCategoryId = '".$subvalue['iVehicleCategoryId']."' AND (lm.iCountryId='".$iCountryId."' || vt.iLocationid='-1') AND vt.eStatus='Active'";
                $vehicle_type_dataNew = $obj->MySQLSelect($vehicle_type_sql1);
                $vehicle_type_data[$i]['SubCategory'][$j]['VehicleType'] = $vehicle_type_dataNew;
                $j++;
            }
            $i++;
        }
    }else{
        $vehicle_type_sql = "SELECT * FROM vehicle_type WHERE eType='".$Vehicle_type_name."' AND iCountryId='-1'";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    }
}
$isEnableServiceTypeWiseProviderDocument = $MODULES_OBJ->isEnableServiceTypeWiseProviderDocument();
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
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?=$SITE_NAME?> | <?php echo $langage_lbl_admin['LBL_SERVICE_ADMIN']; ?> <?=$action;?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="../assets/validation/validatrix.css"/>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <style type="text/css">
        .main-cat {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 0;
            background-color: #EEEEEE;
            padding: 10px;
            width: 100%;
            border: 1px solid #dedede;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .later-clock {
            position: relative;
        }

        .w-95 {
            width: 95% !important;
            cursor: text;
        }

        .error {
            position: initial;
            display: none;
        }

        .custom-model-header {
            background-color: #171717;
        }

        .consult_price_symbol {
            float: left;
            font-weight: 600;
            padding: 5px 5px 5px 0;
        }

        .video_consult_status {
            font-weight: normal;
            cursor: pointer;
        }

        /* Style the tab */
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

        .price-info-table {
            width: 100% !important;
            display: block;
            margin: 5px 0 10px 0 !important;
        }

        .service-textbox {
            width: inherit !important;
        }

        .fareBox {
            display: inline-flex;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?=$action." ".$langage_lbl_admin['LBL_SERVICE_ADMIN'];?></h2>
                    <a href="driver.php" class="back_link">
                        <input type="button" value="<?=$langage_lbl_admin['LBL_BACK_SERVICE_LISTING_ADMIN'];?>"
                                class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <?php if ($MODULES_OBJ->isEnableBiddingServices()){ ?>
                <div class="tab">
                    <button class="tablinks manageservicetab" onclick="openTabContent(event, 'manageservicecontent')"
                            id="defaultOpen"> Manage Services
                    </button>
                    <button class="tablinks biddingtab" onclick="openTabContent(event, 'biddingcontent')"> Manage
                        Bidding Services
                    </button>
                </div>
            <?php } ?>
            <!-- First page content-->
            <div class="body-div tabcontent" id="manageservicecontent">
                <div class="form-group">
                    <?php if ($success == 3){ ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?=$error;?>
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if (!empty($vehicle_type_data)){ ?>
                        <div class="panel panel-info ">
                            <?php if ($MODULES_OBJ->isEnableSearchUfxServices()){ ?>
                                <div class="panel-heading clearfix">
                                    <div class="row" style="padding: 0;">
                                        <div class="col-sm-12 input-group">
                                            <input type="text" class="serach_services form-control" name=""
                                                    placeholder="Search Services">
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div id="ajaxHTML" class="panel-body">
                                <form name="vehicle_form" id="vehicle_form" method="post" action="">
                                    <input type="hidden" name="iDriverId" value="<?=$iDriverId?>"/>
                                    <input type="hidden" name="iCompanyId" value="<?=$iCompanyId?>"/>
                                    <input type="hidden" name="iMakeId" value="<?=$iMakeId?>"/>
                                    <input type="hidden" name="iModelId" value="<?=$iModelId?>"/>
                                    <input type="hidden" name="iYear" value="<?=$iYear?>"/>
                                    <input type="hidden" name="vLicencePlate" value="<?=$vLicencePlate;?>"/>
                                    <input type="hidden" name="id" value="<?=$iDriverVehicleId;?>"/>
                                    <input type="hidden" name="eType" value="<?=$eType;?>"/>
                                    <input type="hidden" name="previousLink" id="previousLink"
                                            value="<?php echo $previousLink; ?>"/>
                                    <input type="hidden" name="backlink" id="backlink" value="driver.php"/>
                                    <input type="hidden" name="redirectToDocumentPage" id="redirectToDocumentPage" value="<?=$redirectToDocumentPage;?>"/>

                                    <div class="checkbox-group required add-services-hatch car-type-custom col-md-12 pull-left"
                                            style="margin-top: 20px">
                                        <ul style="padding-left: 0;">
                                            <?php
                                            foreach ($vehicle_type_data as $key => $value){
                                                foreach ($value['SubCategory'] as $Vehicle_Type){
                                                    if (!empty($Vehicle_Type['VehicleType']) || ($MODULES_OBJ->isEnableVideoConsultingService() && $Vehicle_Type['eVideoConsultEnable'] == "Yes")){
                                                        if ($Vehicle_type_name == 'UberX'){
                                                            $vname = $Vehicle_Type['vCategory_'.$_SESSION['sess_lang']];
                                                            $vehicle_Name = $Vehicle_Type['vVehicleType'];
                                                        }else{
                                                            $vname = $Vehicle_Type['vVehicleType'];
                                                        }
                                                        $iParentcatId = $Vehicle_Type['iParentId'];
                                                        $sql_query = "SELECT ePriceType FROM ".$sql_vehicle_category_table_name." WHERE iVehicleCategoryId = '".$iParentcatId."' ";
                                                        $ePricetype_data = $obj->MySQLSelect($sql_query);
                                                        $ePricetype = $ePricetype_data[0]['ePriceType'];
                                                        ?>
                                                        <li class="panel panel-default"
                                                                style="list-style: outside none none;">
                                                            <div class="main-cat">
                                                                <?php echo $value['main_cat']." - ".$vname; ?>
                                                                <?php if ($MODULES_OBJ->isEnableVideoConsultingService() && $Vehicle_Type['eVideoConsultEnable'] == 'Yes'){ ?>
                                                                    <div class="later-clock left"
                                                                            onclick="manageVideoConsultService('<?=$Vehicle_Type['iVehicleCategoryId']?>', '<?=addslashes($value['main_cat'].' - '.$vname)?>', '<?=$ePricetype?>')">
                                                                        <button type="button" class="add-btn"> Manage
                                                                            Video Consult Service
                                                                        </button>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                            <?php
                                                            if (!empty($Vehicle_Type['VehicleType']) && scount($Vehicle_Type['VehicleType']) > 0){ ?>
                                                                <div class="panel-body">
                                                                    <fieldset class="col-sm-3">
                                                                        <?php foreach ($Vehicle_Type['VehicleType'] as $val){
                                                                            if ($val['eFareType'] == 'Fixed'){
                                                                                $eFareType = 'Fixed';
                                                                                $fAmount_old = $val['fFixedFare'] * $ration;
                                                                            }else if ($val['eFareType'] == 'Hourly'){
                                                                                $eFareType = 'Per hour';
                                                                                $fAmount_old = $val['fPricePerHour'] * $ration;
                                                                            }else{
                                                                                $eFareType = '';
                                                                                $fAmount_old = '';
                                                                            }
                                                                            $vehicle_typeName = $val['vVehicleType_'.$_SESSION['sess_lang']];
                                                                            if (!empty($val['vLocationName'])){
                                                                                $localization = '(Location : '.$val["vLocationName"].')';
                                                                            }else{
                                                                                $localization = '';
                                                                            }
                                                                            ?>
                                                                            <div class="permitions-item">
                                                                                <!-- <label> -->
                                                                                <b><?php echo $vehicle_typeName; ?>
                                                                                    <br/>
                                                                                    <span style="font-size: 12px;"><?php echo $localization; ?></span>
                                                                                </b>
                                                                                <div class="make-switch"
                                                                                        data-on="success"
                                                                                        data-off="warning">
                                                                                    <input type="checkbox"
                                                                                            class="chk vCarTypeClass"
                                                                                            name="vCarType[]"
                                                                                            id="vCarType_<?=$val['iVehicleTypeId']?>"
                                                                                        <?php if ($ePricetype == "Provider") { ?>onchange="check_box_value(this.value);" <?php } ?>
                                                                                        <?php if (in_array($val['iVehicleTypeId'],$vCarTyp)) { ?>checked<?php } ?>
                                                                                            value="<?=$val['iVehicleTypeId']?>"/>
                                                                                </div>
                                                                                <?php
                                                                                if ($ePricetype == "Provider"){
                                                                                    $p001 = "style='display:none;'";
                                                                                    if (in_array($val['iVehicleTypeId'],$vCarTyp)){
                                                                                        $p001 = "style='display:block;'";
                                                                                    }
                                                                                    $fAmount_new = $fAmount[$val['iVehicleTypeId']];
                                                                                      $famount_val = (empty($fAmount_new))?round(cFloat($fAmount_old),2):round(cFloat($fAmount_new),2);
                                                                                    $price_info_class = '';
                                                                                    if ($MODULES_OBJ->isEnableCarSizeServiceTypeAmount()){
                                                                                        $price_info_class = 'price-info-table';
                                                                                    }
                                                                                    ?>
                                                                                    <div class="hatchback-search <?=$price_info_class?>" id="amt1_<?=$val['iVehicleTypeId']?>" <?php echo $p001; ?>>
                                                                                        <?php if ($MODULES_OBJ->isEnableCarSizeServiceTypeAmount()){
                                                                                            $_POST['eFareType'] = $val['eFareType'];
                                                                                            $_POST['iVehicleTypeId'] = $val['iVehicleTypeId'];
                                                                                            $_POST['data_action'] = 'GET';
                                                                                            include "vehicle_type_size_price_info.php";
                                                                                        }else{ ?>
                                                                                            <input type="hidden" name="desc" id="desc_<?=$val['iVehicleTypeId']?>" value="<?=$val['vVehicleType_'.$default_lang]?>">
                                                                                            <?php if ($val['eFareType'] != 'Regular'){ ?>

                                                                                                <div class="fareBox">
                                                                                                    <div class="input-group col-md-12">

                                                                                                        <?php if ($currencyData['eReverseSymbolEnable'] == "No"){ ?>
                                                                                                            <div class="input-group-addon">
                                                                                                                <span class="input-group-text" id="basic-addon1"><?=$vSymbol;?></span>
                                                                                                            </div>
                                                                                                        <?php } ?>

                                                                                                        <input class="numberPlay form-control service-textbox" type="text" name="fAmount[<?=$val['iVehicleTypeId']?>]" value="<?=$famount_val;?>" placeholder="Enter Amount for <?=$val['vVehicleType_'.$default_lang]?>" id="fAmount_<?=$val['iVehicleTypeId']?>" maxlength="10">

                                                                                                        <?php if ($currencyData['eReverseSymbolEnable'] == "Yes"){ ?>
                                                                                                            <div class="input-group-addon">
                                                                                                                <span class="input-group-text" id="basic-addon1"><?=$vSymbol;?></span>
                                                                                                            </div>
                                                                                                        <?php } ?>

                                                                                                    </div>
                                                                                                    <label class="fare_type"><?php echo $eFareType; ?></label>
                                                                                                </div>

                                                                                                <?php
                                                                                            }
                                                                                        } ?>
                                                                                    </div> <?php
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                        <?php } ?>
                                                                    </fieldset>
                                                                </div>
                                                            <?php }else{ ?>
                                                                <div class="panel-body" style="padding: 0">
                                                                    <fieldset class="col-sm-3">
                                                                        <div class="permitions-item"></div>
                                                                    </fieldset>
                                                                </div>
                                                            <?php } ?>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="row" style="display: none;">
                                        <div class="col-lg-12">
                                            <label>Status</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" name="eStatus"
                                                        id="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?> />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <input type="submit" class="btn btn-default" name="submit" id="submit"
                                                    value="<?=$action." ".$langage_lbl_admin['LBL_SERVICE_ADMIN'];?>"
                                                    onclick="return check_empty();">
                                            <a href="javascript:void(0);" onclick="reset_form('vehicle_form');"
                                                    class="btn btn-default">Reset
                                            </a>
                                            <a href="vehicles.php" class="btn btn-default back_link">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php }else{ ?>
                        <div> <?=$langage_lbl_admin['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                    <?php } ?>
                </div>
            </div>
            <!-- End First page content-->
            <?php if ($MODULES_OBJ->isEnableBiddingServices()){ ?>
                <!-- Second page content-->
                <div class="body-div tabcontent" id="biddingcontent">
                    <div class="form-group">
                        <?php if ($success == 3){ ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?=$error;?>
                            </div>
                            <br/>
                        <?php } ?>
                        <?php if (!empty($biddingServices)){ ?>
                            <div class="panel panel-info">
                                <div class="panel-heading clearfix">
                                    <div class="serarch-part-box">
                                        <form name="ufx_service_address" id="ufx_service_address" method="post"
                                                action="" enctype="multipart/form-data">
                                            <div>
                                                <input type="text" class="form-control" id="vWorkLocation" name="vWorkLocation" value="<?php echo $vWorkLocation; ?>" placeholder="<?=$langage_lbl_admin['LBL_ENTER_WORK_LOC_TXT']?>">
                                            </div>
                                            <input type="hidden" name="vWorkLocationLatitude" id="vWorkLocationLatitude"
                                                    value="<?php echo $vWorkLocationLatitude; ?>">
                                            <input type="hidden" name="vWorkLocationLongitude"
                                                    id="vWorkLocationLongitude"
                                                    value="<?php echo $vWorkLocationLongitude; ?>">
                                            <input type="hidden" name="iDriverIdNew" value="<?=$iDriverIdNew?>"/>
                                            <input type="submit" class="btn btn-default gen-btn" name="submitaddress"
                                                    id="submitaddress" value="Submit">
                                        </form>
                                    </div>
                                </div>
                                <br/>
                                <div class="panel-heading clearfix">
                                    <div class="row" style="padding: 0;">
                                        <div class="col-sm-12 input-group">
                                            <input type="text" class="serach_bids form-control" name=""
                                                    placeholder="Search Bids">
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form name="bidding_service_form" id="bidding_service_form" method="post" action="">
                                        <input type="hidden" name="iDriverId" value="<?=$iDriverId?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink"
                                                value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="driver.php"/>
                                        <div class="checkbox-group required add-services-hatch car-type-custom col-md-12 pull-left"
                                                style="margin-top: 20px">
                                            <div id="ajaxBidsHTML">
                                                <ul style="padding-left: 0;">
                                                    <?php foreach ($biddingServices as $key => $value) { 
                                                        if(!empty($value['SubCategory'])){?>
                                                        <li class="panel panel-default"
                                                                style="list-style: outside none none;">
                                                            <div class="main-cat 01">
                                                                <?php echo $value['vCategory']; ?>
                                                            </div>
                                                            <div class="panel-body">
                                                                <fieldset class="col-sm-3">
                                                                    <?php foreach ($value['SubCategory'] as $SubCategoryval){ ?>
                                                                        <div class="permitions-item-bids">
                                                                            <b><?php echo $SubCategoryval['vTitle']; ?></b>
                                                                            <div class="make-switch" data-on="success"
                                                                                    data-off="warning">
                                                                                <input type="checkbox"
                                                                                        class="chk vCarTypeClass"
                                                                                        name="selectedbiddingdriverservice[]"
                                                                                        id="selectedbiddingdriverservice<?=$SubCategoryval['iBiddingId']?>"
                                                                                    <?php if (in_array($SubCategoryval['iBiddingId'],$selectedbiddingdriverservice)) { ?>checked<?php } ?>
                                                                                        value="<?=$SubCategoryval['iBiddingId']?>"/>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>
                                                                </fieldset>
                                                            </div>
                                                        </li>
                                                    <?php } } ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <input type="submit" class="btn btn-default" name="biddingsubmit"
                                                        id="biddingsubmit"
                                                        value="<?=$action." ".$langage_lbl_admin['LBL_BIDDING_TXT'];?>">
                                                <a href="driver.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php }else{ ?>
                            <div> <?=$langage_lbl_admin['LBL_NO_SERVICE_AVAIL_WEB'];?></div>
                        <?php } ?>
                    </div>
                </div>
                <!-- End Second page content-->
            <?php } ?>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<!--END PAGE CONTENT -->
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="../assets/js/modal_alert.js"></script>
<script src="https://maps.google.com/maps/api/js?key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&libraries=places" type="text/javascript" async></script>
</body>
<!-- END BODY-->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
</html>
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
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
            //alert(referrer);
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "vehicles.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
        $('.serach_services').keyup(function () {
            var value = $(this).val();
            var items = $(this).closest('.panel').find('.permitions-item');
            if (value != "" && value != undefined && value != null) {
                items.hide();
                items.each(function () {
                    /*console.log(maincat);*/
                    var text = $(this).find('b').text().toLowerCase();
                    value = value.toLowerCase();
                    if (text.search(value) >= 0) {
                        $(this).show();
                    }
                    var maincat = $(this).closest('.panel').find('.main-cat');
                    var titletext = maincat.text().toLowerCase();
                    value = value.toLowerCase();
                    if (titletext.search(value) >= 0) {
                        $(this).show();
                    }
                });
            } else {
                items.show();
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
            if (value != "" && value != undefined && value != null) {
                items.hide();
                items.each(function () {
                    /*console.log(maincat);*/
                    var text = $(this).find('b').text().toLowerCase();
                    value = value.toLowerCase();
                    if (text.search(value) >= 0) {
                        $(this).show();
                    }
                    var maincat = $(this).closest('.panel').find('.main-cat');
                    var titletext = maincat.text().toLowerCase();
                    value = value.toLowerCase();
                    if (titletext.search(value) >= 0) {
                        $(this).show();
                    }
                });
            } else {
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
        $('#vWorkLocation').keyup(function (e) {
            buildAutoComplete("vWorkLocation", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>", function (latitude, longitude, address) {
                $('#vWorkLocationLatitude').val(latitude);
                $('#vWorkLocationLongitude').val(longitude);
            });
        });


        <?php if ($content == 'biddingcontent'){ ?>
        openTabContent(event, 'biddingcontent');
        $('.biddingtab').addClass('active');
        <?php } else if ($content == 'manageservicecontent'){  ?>
        openTabContent(event, 'manageservicecontent');
        $('.manageservicetab').addClass('active');
        <?php }?>

    });
    $("#ufx_service_address").submit(function () {
        var vWorkLocation = $('#vWorkLocation').val();
        if (vWorkLocation === '') {
            alert('<?php echo addslashes($langage_lbl_admin['LBL_ENTER_WORK_LOC_TXT']);?>');
            return false;
        }
    });

    function check_box_value(val1) {
        if ($('#vCarType_' + val1).is(':checked')) {
            $("#amt1_" + val1).show();
            $("#fAmount_" + val1).focus();
        } else {
            $("#amt1_" + val1).hide();
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
            isEnableServiceTypeWiseProviderDocument = '<?= $isEnableServiceTypeWiseProviderDocument; ?>';
            var matches = [];
            $(".vCarTypeClass:checked").each(function () {
                matches.push(this.value);
            });
            if (isEnableServiceTypeWiseProviderDocument == "Yes" && matches != "") {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>getDocumentServiceTypeWise.php',
                    'AJAX_DATA': {
                        serviceIds: matches
                    },
                    'REQUEST_ASYNC': false
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var responseData = response.result;
                        var returnedData = JSON.parse(responseData);
                        documentCount = returnedData.documentCount;
                        if (documentCount > 0) {
                            returnValue = confirm('Please Upload Document for selected services');
                            if (returnValue == true) {
                                $("#redirectToDocumentPage").val("driver_document_action.php?id=<?= $iDriverId; ?>&action=edit&user_type=driver");
                                //window.location.replace("driver_document_action.php?id=<?= $iDriverId; ?>&action=edit&user_type=driver");
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            document.vehicle_form.submit();
                        }
                    } else {
                        console.log(response.result);
                    }
                });
            }
            //document.vehicle_form.submit();
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
                addNotePopupContent += '<div style="margin-bottom: 5px">Video Consult Service Charge</div>';

                const numberCharges = response.eVideoConsultServiceCharge;
                if (ePriceType == "Provider") {
                    /*  if(currencyDataJson.eReverseSymbolEnable && currencyDataJson.eReverseSymbolEnable === "Yes"){
                          addNotePopupContent += '<div class="input-group"><input  type="text" value = "' + response.eVideoConsultServiceCharge + '" class="form-control" id="eVideoConsultServiceCharge"><div class="input-group-addon"><span class="input-group-text" id="basic-addon1"><?php echo $vSymbol ?></span></div></div>';
                    }else {
                        addNotePopupContent += '<div class="input-group" ><div class="input-group-addon"><span class="input-group-text" id="basic-addon1"><?php echo $vSymbol ?></span></div><input  type="text" value = "' + response.eVideoConsultServiceCharge + '" class="form-control w-95" id="eVideoConsultServiceCharge"></div>';
                    }*/
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
                addNotePopupContent += '<span class="error" id="eVideoConsultServiceChargeError"><?= $langage_lbl_admin['LBL_FEILD_REQUIRD'] ?></span>';
                addNotePopupContent += '<span> <br> Status: &emsp; </span>';
                addNotePopupContent += '<label class="video_consult_status"><input ' + $yes + ' value="Yes" type="radio" name="eVideoConsultEnableProvider"  id="eVideoConsultEnableProvider"> On &emsp;</label>';
                addNotePopupContent += '<label class="video_consult_status"><input ' + $no + ' value="No" type="radio" name="eVideoConsultEnableProvider"  id="eVideoConsultEnableProvider"> Off</label>';
                addNotePopupContent += '<div style="margin: 10px 0 5px">Video Consult Service Description</div>';
                addNotePopupContent += '<div><textarea  type="text" class="form-control w-95" name = "eVideoServiceDescription" id="eVideoServiceDescription">' + response.eVideoServiceDescription + '</textarea></div>';
                addNotePopupContent += '<span class="error" id="eVideoServiceDescriptionError"><?= $langage_lbl_admin['LBL_FEILD_REQUIRD'] ?></span>';
                addNotePopupContent += '<input type="hidden" id="iVehicleCategoryId" value="' + iVehicleCategoryId + '">';
                show_alert(title, addNotePopupContent, "<?= $langage_lbl_admin['LBL_BTN_SUBMIT_TXT'] ?>", "<?= $langage_lbl_admin['LBL_BTN_CANCEL_TXT'] ?>", "", function (btn_id) {
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
        console.log(eVideoConsultEnableProvider);
        if (eVideoConsultServiceCharge.trim() == "") {
            $('#eVideoConsultServiceCharge').val("").focus();
            $("#eVideoConsultServiceChargeError").html("<?= $langage_lbl_admin['LBL_FEILD_REQUIRD'] ?>").show();
            return false;
        }
        if (eVideoConsultServiceCharge.trim() <= 0) {
            $('#eVideoConsultServiceCharge').val("").focus();
            $("#eVideoConsultServiceChargeError").html("<?= $langage_lbl_admin['LBL_VALUE_GREATER_THAN_ZERO_MSG'] ?>").show();
            return false;
        }
        if (eVideoConsultEnableProvider == 'Yes') {
            if (eVideoServiceDescription.trim() == "") {
                $('#eVideoServiceDescription').val("").focus();
                $("#eVideoServiceDescriptionError").html("<?= $langage_lbl_admin['LBL_FEILD_REQUIRD'] ?>").show();
                return false;
            }
        }
        closeAlertPopup();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_manage_provider_charges.php',
            'AJAX_DATA': {
                isAdmin: 1,
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

    // Get the element with id="defaultOpen" and click on it


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
</script>