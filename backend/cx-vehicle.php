<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$TRACKING_COMPANY = '';
if ($_SESSION['sess_user'] == 'tracking_company'){
    $TRACKING_COMPANY = 1;
}
require_once(TPATH_CLASS."/Imagecrop.class.php");
$thumb = new thumbnail();
$script = "Vehicle";
$abc = 'driver,company';
if ($TRACKING_COMPANY == 1){
    $abc = 'tracking_company';
    $iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
}
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
$tbl_name = 'driver_vehicle';
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$id = isset($_GET['id'])?$_GET['id']:'';
$driverid = isset($_GET['driverid'])?$_GET['driverid']:'';
$error = isset($_GET['success']) && $_GET['success'] == 0?1:'';
$default_lang = isset($_SESSION['sess_lang'])?$_SESSION['sess_lang']:$default_lang;
$Status = isset($_REQUEST["Status"])?$_REQUEST["Status"]:'';
$hdn_del_id = isset($_REQUEST["hdn_del_id"])?$_REQUEST["hdn_del_id"]:'';
/*$success = isset($_GET['success']) ? $_GET['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';*/
$success = '';
if (isset($_SESSION['message_code']) && !empty($_SESSION['message_code'])){
    $success = $_SESSION['message_code'];
    $_SESSION['message_code'] = '';
}
$error = '';
if (isset($_SESSION['message_code']) && !empty($_SESSION['message_code'])){
    $error = isset($_SESSION['message_code']) && $_SESSION['message_code'] == 0?1:'';
    $_SESSION['message_code'] = '';
}
$var_msg = '';
if (isset($_SESSION['session_msg']) && !empty($_SESSION['session_msg'])){
    $var_msg = $_SESSION['session_msg'];
    $_SESSION['session_msg'] = '';
}
if ($id != '' && $Status != ''){
    if (SITE_TYPE != 'Demo'){
        $vehicleId = base64_decode(base64_decode($id));
        if ($Status == 'Inactive'){

            $query = "UPDATE driver_vehicle SET eStatus = '{$Status}' WHERE iDriverVehicleId = '{$vehicleId}'";
            $obj->sql_query($query);
            $var_msg = $langage_lbl['LBL_RECORD_INACTIVATE_MSG'];
            $success = 1;
            $_SESSION['message_code'] = "1";
            $_SESSION['session_msg'] = $var_msg;
        }else{
            $query = "UPDATE driver_vehicle SET eStatus = '{$Status}' WHERE iDriverVehicleId = '{$vehicleId}'";
            $obj->sql_query($query);
            $success = '1';
            if ($Status == 'Active'){
                $var_msg = $langage_lbl['LBL_RECORD_ACTIVATE_MSG'];
            }else{
                $var_msg = $langage_lbl['LBL_RECORD_INACTIVATE_MSG'];
            }
            $_SESSION['message_code'] = "1";
            $_SESSION['session_msg'] = $var_msg;
        }
    }
    //header("Location:vehicle?success=1&var_msg=" . $var_msg);
    header("Location:vehicle");
    exit;
}
if (isset($_POST['Submit'])){
    $var_msg = $langage_lbl['LBL_EDIT_VEHICLE_DISABLED'];
    if (strtoupper($ENABLE_EDIT_DRIVER_VEHICLE) == "NO" && $action == 'Edit'){
        $_SESSION['message_code'] = "0";
        $_SESSION['session_msg'] = $var_msg;
        header("location:vehicle");
        //header("location:vehicle?success=0&var_msg=" . $var_msg);
        exit;
    }
    $iVehicleId = isset($_REQUEST['iVehicleId1'])?$_REQUEST['iVehicleId1']:'';
    $doc_name = $_POST['doc_name'];
    $doc_path = $_POST['doc_path2'];
    $expDate = $_POST['dLicenceExp'];
    $image = $_FILES['file']['name'];
    $image_object = $_FILES['file']['tmp_name'];
    $masterid = $_POST['doc_id'];
    if ($expDate != ""){
        $sql = "select ex_date from document_list where doc_userid='".$iVehicleId."' and doc_masterid='".$masterid."'";
        $query = $obj->MySQLSelect($sql);
        $fetch = $query[0];
        if ($fetch['ex_date'] != $expDate || $image_name == ""){
            $sql = "UPDATE `document_list` SET  ex_date='".$expDate."' WHERE doc_userid='".$iVehicleId."' and doc_masterid='".$masterid."'";
            $query = $obj->sql_query($sql);
        }else{
            if ($image_name == ""){
                $var_msg = $langage_lbl['LBL_DOC_UPLOAD_ERROR_'];
                $_SESSION['message_code'] = "0";
                $_SESSION['session_msg'] = $var_msg;
                header("location:vehicle?success=0&id=".$iVehicleId."&var_msg=".$var_msg);
                exit();
            }
        }
    }
    if ($image != ''){

        require_once(SITE_ADMIN_URL."/library/validation.class.php");
        $validobj = new validation();

        $docUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_docs_file_extensions_validation_extensions"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['file'], $tconfig["tsite_upload_docs_file_extensions"], $docUploadingExtenstionMsg);

         if ($error) {
                $var_msg = $error;
                $_SESSION['message_code'] = "0";
                $_SESSION['session_msg'] = $var_msg;
                // header("location:vehicle?success=0&var_msg=".$var_msg);

                $_SESSION['message_code'] = "1";
                $_SESSION['session_msg'] = $var_msg;
                header("location:vehicle"); 
            exit;
        } else {
                $Photo_Gallery_folder = $doc_path.'/'.$iVehicleId.'/';
                if (!is_dir($Photo_Gallery_folder)){
                    mkdir($Photo_Gallery_folder,0777);
                    chmod($Photo_Gallery_folder,0777);
                }
                //$img = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                // $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image,$prefix = '',$vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
                $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image,$prefix = '',$vaildExt = $tconfig["tsite_upload_docs_file_extensions"]);
                $vImage = $vFile[0];
                if ($vFile[2] == "1"){
                    $var_msg = $vFile[1];
                    $_SESSION['message_code'] = "0";
                    $_SESSION['session_msg'] = $var_msg;
                    header("location:vehicle?success=0&var_msg=".$var_msg);
                }else{
                    $var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
                }
                $tbl = 'document_list';
                $sql = "select doc_id from  ".$tbl."  where doc_userid='".$iVehicleId."' and doc_usertype='car'  and doc_masterid='".$masterid."'";
                $db_data = $obj->MySQLSelect($sql);
                if (scount($db_data) > 0){
                    $query = "UPDATE `".$tbl."` SET `doc_file`='".$vImage."' WHERE doc_userid='".$iVehicleId."' and doc_usertype='car'  and doc_masterid='".$masterid."'";
                    $obj->sql_query($query);
                }else{
                    $query = " INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`,`ex_date`,`doc_file`, `status`, `edate`) "."VALUES "."( '".$masterid."', 'car', '".$iVehicleId."','".$expDate."','".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
                    $obj->sql_query($query);
                }
                ###### Email #######
                if ($_SESSION['sess_user'] == 'company'){
                    $maildata['NAME'] = $_SESSION['sess_vCompany']." (".$langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'].")";
                }else{
                    $maildata['NAME'] = $_SESSION['sess_vName']." ".$_SESSION['sess_vLastName']." (".$langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'].")";
                }


                /*-----------------------------------*/
                $sql_user = "SELECT rd.vCode as rdvCode,rd.vPhone as rdvPhone,rd.iDriverId,rd.vName,rd.vLastName,rd.vEmail as rdemail,c.vCompany,c.vEmail as cemail  FROM `register_driver` as rd join company as c on c.iCompanyId =rd.iCompanyId WHERE rd.iDriverId='" . $_SESSION['sess_iUserId'] . "'";
                $userdetails = $obj->MySQLSelect($sql_user);


                 if(isset($userdetails[0]['rdemail']) && !empty($userdetails[0]['rdemail']))
                 {
                     $maildata['EMAIL'] = $langage_lbl['LBL_EMAIL_TEXT'].': '.$userdetails[0]['rdemail'];
                 }

                 if(isset($userdetails[0]['rdvPhone']) && !empty($userdetails[0]['rdvPhone']))
                 {
                     $maildata['PHONE'] = $langage_lbl['LBL_PHONE'].': '."+".$userdetails[0]['rdvCode']." ".$userdetails[0]['rdvPhone'];
                 }
                 /*-----------------------------------*/

                $docname_SQL = "SELECT doc_name_".$default_lang." as docname FROM document_master WHERE doc_masterid = '".$masterid."'";
                $docname_data = $obj->MySQLSelect($docname_SQL);
                $maildata['DOCUMENTTYPE'] = $docname_data[0]['docname'];
                if ($_SESSION['sess_user'] == 'company'){
                    $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'];
                }else{
                    $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'];
                }
                $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD_WEB",$maildata);
                if ($_SESSION['sess_user'] == 'driver' || $storeDriver > 0){
                    $sqlquery = "SELECT vEmail,vCompany FROM company WHERE iCompanyId = '".$_SESSION['sess_iCompanyId']."'";
                    $Companydata = $obj->MySQLSelect($sqlquery);
                    $maildata['COMPANYEMAIL'] = $Companydata[0]['vEmail'];
                    $maildata['COMPANYNAME'] = $Companydata[0]['vCompany'];
                    $maildata['EMAIL_NAME'] = $Companydata[0]['vCompany'];
                    $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD_WEB_COMPANY",$maildata);
                }
                #######Email ##########
        }
    }
    $_SESSION['message_code'] = "1";
    $_SESSION['session_msg'] = $var_msg;
    header("location:vehicle");
    exit;
}
$dri_ssql = "";
if (SITE_TYPE == 'Demo'){
    $dri_ssql = " And rd.tRegistrationDate > '".WEEK_DATE."'";
}
$RideDeliveryIconArrStatus = CheckRideDeliveryFeatureDisableWeb();
$eShowRideVehicles = $RideDeliveryIconArrStatus['eShowRideVehicles'];
$eShowDeliveryVehicles = $RideDeliveryIconArrStatus['eShowDeliveryVehicles'];
$esql = '';
$storeDriver = 0;
$vehicleDriverId = $_SESSION['sess_iUserId'];
if (isset($_REQUEST['driverid']) && $_REQUEST['driverid'] > 0){
    $storeDriver = 1;
    $vehicleDriverId = $_REQUEST['driverid'];
}
$driverFname = "";
if ($_SESSION['sess_user'] == 'driver' || $storeDriver > 0){
    $sql = "select iCompanyId,vName from `register_driver` where iDriverId = '".$vehicleDriverId."'";
    $db_usr = $obj->MySQLSelect($sql);
    if (scount($db_usr) > 0){
        $driverFname = $db_usr[0]['vName'];
    }
    $iCompanyId = $db_usr[0]['iCompanyId'];

    /*------------------allow only the store driver vehicle on the store panel.-----------------*/
    if ($_SESSION['sess_user'] == 'company' && $storeDriver == 1)
    {
        if ($_SESSION['sess_iCompanyId'] != $iCompanyId)
        {
            $var_msg = $langage_lbl["LBL_SOMETHING_WENT_WRONG_MSG"];
            $_SESSION['message_code'] = "3";
            $_SESSION['session_msg'] = $var_msg;
            header("location:providerlist");
            exit();
        }
    }
    /*------------------allow only the store driver vehicle on the store panel.-----------------*/


    // $sql = "SELECT * FROM " . $tbl_name . " where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $vehicleDriverId . "' and eStatus != 'Deleted'";
    // $db_driver_vehicle = $obj->MySQLSelect($sql);
    if ($APP_TYPE == 'UberX'){
        $sql = "SELECT * FROM ".$tbl_name." dv  where iCompanyId = '".$iCompanyId."'  and dv.eType == 'UberX' and dv.iDriverId = '".$vehicleDriverId."' and dv.eStatus != 'Deleted' ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    }else{
        $sql = "SELECT dv.*,rd.vCountry,rd.vName,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,m.vTitle, mk.vMake,dv.vLicencePlate,dv.eStatus,dv.eType  FROM ".$tbl_name." dv  JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON  dv.iMakeId=mk.iMakeId JOIN register_driver as rd ON rd.iDriverId = dv.iDriverId where dv.iCompanyId = '".$iCompanyId."' and dv.iDriverId = '".$vehicleDriverId."' AND dv.eType != 'UberX'  and dv.eStatus != 'Deleted' $esql  $dri_ssql ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    }
}
if ($_SESSION['sess_user'] == 'company' && $storeDriver == 0){
    $iCompanyId = $vehicleDriverId = $_SESSION['sess_iUserId'];
    // $sql = "SELECT * FROM " . $tbl_name . " where iCompanyId = '" . $iCompanyId . "' and eStatus != 'Deleted'";
    // $db_driver_vehicle = $obj->MySQLSelect($sql);
    if ($APP_TYPE == 'UberX'){
        $sql = "SELECT * FROM ".$tbl_name." dv  where iCompanyId = '".$iCompanyId."' and dv.eType == 'UberX' and dv.eStatus != 'Deleted' ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    }else{
        $sql = "SELECT dv.*,rd.vCountry,rd.vName,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,m.vTitle, mk.vMake,dv.vLicencePlate,dv.eStatus,dv.eType  FROM ".$tbl_name." dv  JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON  dv.iMakeId=mk.iMakeId JOIN register_driver as rd ON rd.iDriverId = dv.iDriverId where dv.iCompanyId = '".$iCompanyId."' AND dv.eType != 'UberX' and rd.eStatus != 'Deleted' and dv.eStatus != 'Deleted' $esql $dri_ssql ORDER BY dv.iDriverVehicleId DESC";
        $db_driver_vehicle = $obj->MySQLSelect($sql);
    }
}
if ($TRACKING_COMPANY == 1){
    $sql = "SELECT dv.*,rd.vCountry,rd.vName,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,m.vTitle, mk.vMake,dv.vLicencePlate,dv.eStatus,dv.eType  FROM ".$tbl_name." dv  JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON  dv.iMakeId=mk.iMakeId JOIN register_driver as rd ON rd.iDriverId = dv.iDriverId where dv.iTrackServiceCompanyId = '".$iTrackServiceCompanyId."' AND dv.eType != 'UberX' and dv.eStatus != 'Deleted' $esql $dri_ssql ORDER BY dv.iDriverVehicleId DESC";
    $db_driver_vehicle = $obj->MySQLSelect($sql);
}
if (isset($iDriverVehicleId)){
    $sql = "select * from register_driver where iDriverVehicleId = '".$iDriverVehicleId."'";
    $db_data = $obj->MySQLSelect($sql);
}
if ($action == 'delete'){
    // to check user is valid or not to delete vehicle
    $valid_user = false;
    foreach ($db_driver_vehicle as $val){
        if ($val['iDriverVehicleId'] == $id){
            $valid_user = true;
        }
    }
    if (!$valid_user){
        $var_msg = $langage_lbl['LBL_VEHICLE_DELETE_ERROR_MSG'];
        $_SESSION['message_code'] = "0";
        $_SESSION['session_msg'] = $var_msg;
        header("Location:vehicle");
        // header("Location:vehicle?success=0&var_msg=" . $var_msg);
    }else{
        $sql = "select count(*) as trip_cnt from trips where iDriverVehicleId = '".$id."' AND  iActive IN ('Active', 'On Going Trip')";
        $db_usr = $obj->MySQLSelect($sql);
        $getDriverData = $obj->MySQLSelect("SELECT iDriverVehicleId FROM register_driver WHERE iDriverId='".$driverid."'");
        $iDriverVehicleId = $getDriverData[0]['iDriverVehicleId'];
        if (scount($db_usr) > 0 && $db_usr[0]['trip_cnt'] > 0){
            $varmsg = $langage_lbl['LBL_TRIP_VEHICLE_DELETE_ERROR_MSG'];
            $_SESSION['message_code'] = "0";
            $_SESSION['session_msg'] = $var_msg;
            header("Location:vehicle");
            //header("Location:vehicle?success=0&var_msg=" . $varmsg);
            exit;
            //} elseif (scount($db_driver_data) > 0 && $db_driver_data[0]['drivers'] > 0) {
        }else if ($iDriverVehicleId == $id){ //added by SP when vehicle is active then it can not be deleted on 28-09-2019
            $varmsg = $langage_lbl['LBL_ACTIVE_VEHICLE_NOT_DELETE'];
            $_SESSION['message_code'] = "0";
            $_SESSION['session_msg'] = $var_msg;
            header("Location:vehicle");
            // header("Location:vehicle?success=0&var_msg=" . $varmsg);
            exit;
        }else{

            $query = "UPDATE `driver_vehicle` SET eStatus = 'Deleted' WHERE iDriverVehicleId = '".$id."'";
            $obj->sql_query($query);
            $var_msg = $langage_lbl['LBL_DELETE_VEHICLE'];
            $_SESSION['message_code'] = "1";
            $_SESSION['session_msg'] = $var_msg;
            header("Location:vehicle");
            //header("Location:vehicle?success=1&var_msg=" . $var_msg);
            exit;
        }
    }
}
$getVehicleModal = $obj->MySQLSelect("SELECT * FROM model WHERE vTitle !=''");
$modalArr = $makeArr = array();
for ($g = 0;$g < scount($getVehicleModal);$g++){
    $modalArr[$getVehicleModal[$g]['iModelId']] = $getVehicleModal[$g]['vTitle'];
}
$getVehicleMake = $obj->MySQLSelect("SELECT * FROM make WHERE vMake !=''");
for ($h = 0;$h < scount($getVehicleMake);$h++){
    $makeArr[$getVehicleMake[$h]['iMakeId']] = $getVehicleMake[$h]['vMake'];
}
for ($i = 0;$i < scount($db_driver_vehicle);$i++){
    $madalName = $makeName = "";
    if (isset($modalArr[$db_driver_vehicle[$i]['iModelId']])){
        $madalName = $modalArr[$db_driver_vehicle[$i]['iModelId']];
    }
    if (isset($makeArr[$db_driver_vehicle[$i]['iMakeId']])){
        $makeName = $makeArr[$db_driver_vehicle[$i]['iMakeId']];
    }
    $db_msk[$i] = $makeName.' '.$madalName;
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?php if ($storeDriver > 0){
            echo $langage_lbl['LBL_VEHICLE_TITLE'];
        }else{
            echo $langage_lbl['LBL_VEHICLES'];
        } ?></title>
    <!-- Default Top Script and css -->
    <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css"/>
    <?php include_once("top/top_script.php"); ?>
    <?php //if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
    <!-- <link rel="stylesheet" type="text/css" href="assets/css/vehicles_cubejek.css"> -->
    <?php //} else { ?>
    <!--<link rel="stylesheet" type="text/css" href="assets/css/vehicles.css">-->
    <?php //} ?>
    <style>
        .fileupload-preview {
            line-height: 150px;
            width: 300px !important;
        }

        .accordian-data .bootstrap-datetimepicker-widget table td,
        .accordian-data .bootstrap-datetimepicker-widget table th {
            padding: 6px !important;
            background-color: #dfdfdf;
        }
    </style>
    <!-- End: Default Top Script and css-->
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Top Menu -->
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?php
                        if ($storeDriver > 0){
                            $headName = $langage_lbl['LBL_LEFT_MENU_VEHICLES'];
                            if ($driverFname != ""){
                                $headName = ucwords($driverFname)."'s ".$headName;
                            }
                            echo $headName;
                        }else{
                            echo $langage_lbl['LBL_HEADER_TOPBAR_VEHICLES'];
                        }
                        ?></h1>
                </div>
                <div class="button-block end">
                    <?php if ($APP_TYPE != 'UberX'){ ?><?php if ($storeDriver > 0){ ?>
                        <a class="gen-btn" href="vehicle-add?driverid=<?=$vehicleDriverId;?>&vehicle=store"><?=$langage_lbl['LBL_ADD_VEHICLE'];?></a>
                    <?php }else{ ?>
                        <a class="gen-btn" href="vehicle-add"><?=$langage_lbl['LBL_ADD_VEHICLE'];?></a>
                    <?php }
                    } ?>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="table-holder">
                <div class="page-contant-inner">
                    <?php
                    if (SITE_TYPE == 'Demo'){
                        ?>
                        <div class="demo-warning">
                            <p><?=$langage_lbl['LBL_SINCE_THIS'];?></p>
                        </div>
                        <?php
                    }
                    ?>
                    <!-- driver vehicles page -->
                    <div class="VehicleList">
                        <?php
                        if ($error){
                            ?>
                            <div class="alert alert-danger">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                </button><?=$var_msg?>
                            </div>
                            <?php
                        }
                        if ($success == 1){
                            ?>
                            <div class="row">
                                <div class="alert alert-success paddiing-10">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                    </button>
                                    <?=$var_msg?>
                                </div>
                            </div>
                            <?php
                        }else if ($success == 2){
                            ?>
                            <div class="row">
                                <div class="alert alert-danger paddiing-10">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                    </button>
                                    <?=$langage_lbl['LBL_VEHICLE_EDIT_DELETE_RECORD'];?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="vehicles-page">
                            <div class="accordion">
                                <table id="my-trips-data" class="ui celled table custom-table dataTable no-footer" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th data-text="init"><?=$langage_lbl['LBL_Vehicle']?></th>
                                        <th><?=$langage_lbl['LBL_Status']?></th>
                                        <th><?=$langage_lbl['LBL_EDIT']?></th>
                                        <th><?=$langage_lbl['LBL_DELETE']?></th>
                                        <th><?=$langage_lbl['LBL_OPEN_CLOSE']?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (scount($db_driver_vehicle) > 0){
                                        for ($i = 0;$i < scount($db_driver_vehicle);$i++){
                                            ?>
                                            <tr>
                                                <input type="hidden" name="iVehicleId" value="<?php echo $db_driver_vehicle[$i]['iDriverVehicleId']; ?>"/>
                                                <div class="accordion-section">
                                                    <div class="accordionheading">
                                                        <?php
                                                        if ($APP_TYPE == 'UberX'){
                                                            $displayname = $db_driver_vehicle[$i]['vLicencePlate'];
                                                        }else{
                                                            $displayname = $db_driver_vehicle[$i]['vMake']."   ".$db_driver_vehicle[$i]['vTitle']."  ".$db_driver_vehicle[$i]['vLicencePlate']."  ";
                                                        }
                                                        ?>
                                                        <td data-text="init">
                                                            <span class="vehicle_name"><?php echo $displayname; ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if ($TRACKING_COMPANY == 1){ ?>
                                                                <a href="cx-vehicle.php?id=<?=base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId']));?>&Status=<?=($db_driver_vehicle[$i]['eStatus'] == "Active")?'Inactive':'Active'?>">
                                                                    <?php if (strtolower($db_driver_vehicle[$i]['eStatus']) == "active"){
                                                                        $statusLabel = $langage_lbl['LBL_ACTIVE'];
                                                                    }else{
                                                                        $statusLabel = $langage_lbl['LBL_INACTIVE'];
                                                                    } ?><?php
                                                                    if ($db_driver_vehicle[$i]['eStatus'] == "Active"){
                                                                        echo '<i class="icon-verified"></i>';
                                                                    }else{
                                                                        echo '<i class="icon-cancel"></i>';
                                                                    }
                                                                    ?>
                                                                </a>
                                                            <?php }else{
                                                                if ($db_driver_vehicle[$i]['eStatus'] == "Active"){
                                                                    echo '<i class="icon-verified"></i>';
                                                                }else{
                                                                    echo '<i class="icon-cancel"></i>';
                                                                }
                                                            } ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($storeDriver > 0){ ?>
                                                                <a href="cx-vehicle_add_form.php?id=<?=base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId']))?>&driverid=<?=$vehicleDriverId;?>&vehicle=store" class="action-click icon-edit"></a>
                                                            <?php }else{ ?>
                                                                <a href="cx-vehicle_add_form.php?id=<?=base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId']))?>" class="action-click icon-edit"></a>
                                                            <?php } ?>
                                                            <!-- <a href ="vehicle_add_form.php?id=<?=base64_encode(base64_encode($db_driver_vehicle[$i]['iDriverVehicleId']))?>" class="action-click icon-edit"><?=$langage_lbl['LBL_VEHICLE_EDIT'];?></a> -->
                                                        </td>
                                                        <td>
                                                            <?php if ($APP_TYPE != 'UberX'){ ?>
                                                                <!-- <a class="active active2" onClick="confirm_delete('<?=$db_driver_vehicle[$i]['iDriverVehicleId']?>', '<?=$db_driver_vehicle[$i]['iDriverId']?>');" href="javascript:void(0);"><?=$langage_lbl['LBL_DELETE'];?></a> -->
                                                                <a class="action-click icon-delete" onClick="confirm_delete('<?=$db_driver_vehicle[$i]['iDriverVehicleId']?>', '<?=$db_driver_vehicle[$i]['iDriverId']?>');" href="javascript:void(0);"></a>
                                                            <?php } ?>
                                                        </td>
                                                        <td class="details-control">
                                                            <i class="icon-arrow-down-avigate expanding"></i>
                                                            <?php
                                                            /* if($APP_TYPE == 'Ride-Delivery') {

                                                              $eTypeQuery = " AND (eType='".$db_driver_vehicle[$i]['eType']."')";
                                                              } else if($APP_TYPE == 'Ride-Delivery-UberX'){
                                                              $eTypeQuery = " AND (eType='".$db_driver_vehicle[$i]['eType']."')";
                                                              } else {
                                                              $eTypeQuery = " AND eType='".$APP_TYPE."'";
                                                              } */
                                                            $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,  dm.doc_name_".$default_lang." as doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='".$db_driver_vehicle[$i]['iDriverVehicleId']."') dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='car' and dm.status='Active' and (dm.country ='".$db_driver_vehicle[$i]['vCountry']."' OR dm.country ='All') ORDER BY dm.iDisplayOrder";
                                                            $db_userdoc = $obj->MySQLSelect($sql1);
                                                            if ($APP_TYPE != 'UberX' && $db_userdoc > 0){
                                                                ?>
                                                                <!-- <a class="accordion-section-title" href="#accordion-<?php echo $i; ?>">&nbsp;</a> -->
                                                            <?php } ?>




                                                            <?php if (($_SESSION['sess_user'] == 'company' && $storeDriver == 0) || $TRACKING_COMPANY == 1){ ?>
                                                                <div style=" clear: both;margin: 8px 0 0 10px;font-size: 10px;"><?php echo $langage_lbl['LBL_DRIVER_COMPANY_TXT']; ?>
                                                                    :<?php
                                                                    echo clearName($db_driver_vehicle[$i]['driverName']);
                                                                    /*  if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){
                                                                      ?>
                                                                      <strong style="font-size: 15px;">(<?= $db_driver_vehicle[$i]['eType']?>)</strong>

                                                                      <?php } */
                                                                    ?>
                                                                </div>
                                                                <?php
                                                            }
                                                            /* else { */
                                                            ?>
                                                            <!--  <div style=" clear: both;margin: 8px 0 0 10px;font-size: 10px;">
                                                        <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){ ?>
                                                                           <strong style="font-size: 15px;"><?=$db_driver_vehicle[$i]['eType']?></strong>
                                                        <?php }
                                                            ?>
                                                         </div> -->
                                                            <?php //}  ?>
                                                    </div>
                                                </div>
                                                </td>
                                            </tr>
                                            <tr class="accordian-data">
                                                <td colspan="5">
                                                    <div id="accordion-<?php echo $i; ?>" class="">
                                                        <div class="driver-vehicles-page-new">
                                                            <!-- <h2><?=$langage_lbl['LBL_DOCUMENTS'];?></h2>      -->
                                                            <?php
                                                            if (!empty($db_userdoc) && scount($db_userdoc) > 0){ ?>
                                                                <ul>
                                                                    <?php for ($s = 0;$s < scount($db_userdoc);$s++){
                                                                        if ($db_userdoc[$s]['eType'] == 'UberX'){
                                                                            $etypeName = 'Service';
                                                                        }else{
                                                                            $etypeName = $db_userdoc[$s]['eType'];
                                                                        }
                                                                        $file_ext = "";
                                                                        if ($db_userdoc[$s]['doc_file'] != ''){
                                                                            $file_ext = $UPLOAD_OBJ->GetFileExtension($db_userdoc[$s]['doc_file']);
                                                                        }
                                                                        ?>
                                                                        <li>
                                                                            <form id="<?=$db_driver_vehicle[$i]['iDriverVehicleId'].$s?>" class="upload_docform upload-block" data-provides="fileupload" method="post" action="" enctype="multipart/form-data">
                                                                                <input type="hidden" name="iVehicleId1" value="<?php echo $db_driver_vehicle[$i]['iDriverVehicleId']; ?>"/>
                                                                                <input type="hidden" name="doc_name" value="<?php echo $db_userdoc[$s]['doc_name']; ?>">
                                                                                <input type="hidden" name="doc_id" value="<?php echo $db_userdoc[$s]['masterid']; ?>">
                                                                                <input type="hidden" name="doc_path2" value="<?php echo $tconfig["tsite_upload_vehicle_doc"]; ?>">
                                                                                <strong>
                                                                                    <?php echo $db_userdoc[$s]['doc_name']; ?>
                                                                                    <?php if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX'){ ?>
                                                                                        <!--  <div style="font-size: 10px;font-weight: normal;color:#362f2d">(For <?=$etypeName;?>)</div> -->
                                                                                        <div style="font-size: 10px;font-weight: normal;color:#362f2d"></div>
                                                                                    <?php } ?>
                                                                                </strong>
                                                                                <div class="fileupload fileupload-new upload-block-inner">
                                                                                    <div class="doc-image-block <?php if ($file_ext == 'is_image'){
                                                                                        echo 'has-data';
                                                                                    } ?>">
                                                                                        <i href="#" class="fileupload-exists icon-close dismissUpload" data-dismiss="fileupload"></i>
                                                                                        <div class="fileupload-preview thumbnail" style="padding-left: 10px;
    padding-right: 10px;">
                                                                                            <?php if ($db_userdoc[$s]['doc_file'] != ''){ ?><?php
                                                                                                if ($file_ext == 'is_image'){
                                                                                                    $imgUrl = $tconfig["tsite_upload_vehicle_doc_panel"].'/'.$db_driver_vehicle[$i]['iDriverVehicleId'].'/'.$db_userdoc[$s]['doc_file'];
                                                                                                    ?>
                                                                                                    <a href="<?=$tconfig["tsite_upload_vehicle_doc_panel"].'/'.$db_driver_vehicle[$i]['iDriverVehicleId'].'/'.$db_userdoc[$s]['doc_file'];?>" target="_blank">
                                                                                                        <img src="<?=$tconfig["tsite_upload_vehicle_doc_panel"].'/'.$db_driver_vehicle[$i]['iDriverVehicleId'].'/'.$db_userdoc[$s]['doc_file'];?>" alt="<?php echo $db_userdoc[$s]['doc_name']; ?>" style="cursor:pointer;">
                                                                                                        <!-- <img src="<?=$tconfig["tsite_url"].'resizeImg.php?w=300&src='.$imgUrl;?>" alt="<?=$db_userdoc[$s]['doc_name'];?>" style="cursor:pointer;"> -->
                                                                                                    </a>
                                                                                                <?php }else{ ?>
                                                                                                    <a href="<?=$tconfig["tsite_upload_vehicle_doc_panel"].'/'.$db_driver_vehicle[$i]['iDriverVehicleId'].'/'.$db_userdoc[$s]['doc_file'];?>" target="_blank"><?php echo $db_userdoc[$s]['doc_name']; ?></a>
                                                                                                <?php } ?><?php
                                                                                            }else{
                                                                                                echo '<p>'.$db_userdoc[$s]['doc_name'].' <span  class="no-record">'.$langage_lbl['LBL_NOT_FOUND'].'</span></p>';
                                                                                            }
                                                                                            ?>
                                                                                            </b>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="fileerror"></div>

                                                                                    <?php if ($db_userdoc[$s]['ex_status'] == 'yes'){ ?>
                                                                                        <div class="col-lg-13 exp-date">
                                                                                            <div class="input-group input-append date filter-form">
                                                                                                <div class="filters-column" style="margin-bottom: 10px;">
                                                                                                    <label><?=$langage_lbl['LBL_EXPIRY_DATE'];?></label>
                                                                                                    <input class="form-control readonlyNew dp123" type="text" name="dLicenceExp" value="<?php echo ($db_userdoc[$s]['ex_date'] != "")?$db_userdoc[$s]['ex_date']:''; ?>" required/>
                                                                                                    <i class="icon-cal" id="from-date"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php } ?>
                                                                                </div>
                                                                                <div class="button-block">
                                                                                    <div class="upload-doc-button">
                                                                                        <!-- <input type="file" name="file" <?php if ($db_userdoc[$s]['doc_file'] == "") { ?>required<?php } ?> class="ins" accept="image/*,.doc,.docx,.pdf" onChange="validate_fileextension(<?php echo $db_driver_vehicle[$i]['iDriverVehicleId'].$s; ?>, this.value)"/> -->

                                                                                        <input type="file" name="file" <?php if ($db_userdoc[$s]['doc_file'] == "") { ?>required<?php } ?> class="ins" onChange="validate_fileextension(<?php echo $db_driver_vehicle[$i]['iDriverVehicleId'].$s; ?>, this.value)"/>
                                                                                        <button class="gen-btn"><?=$langage_lbl['LBL_UPLOAD'];?> </button>
                                                                                    </div>
                                                                                    <input type="submit" name="Submit" class="save-document gen-btn" value="<?=$langage_lbl['LBL_Save_Documents'];?>">
                                                                                </div>
                                                                            </form>
                                                                        </li>
                                                                    <?php } ?>
                                                                </ul>
                                                            <?php }else{ ?>
                                                                <div class="card-block"><?=$langage_lbl['LBL_NO_DOC_AVAIL']?></div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                    <!--end .accordion-section-content-->
                                                </td>
                                                <td style="display: none;"></td>
                                                <td style="display: none;"></td>
                                                <td style="display: none;"></td>
                                                <td style="display: none;"></td>
                                            </tr><!--end .accordion-section-->
                                            <?php
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<script type="text/javascript" src="assets/js/accordion.js"></script>
<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<!-- <link rel="stylesheet" type="text/css" media="screen" href="admin/css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css"> -->
<script type="text/javascript" src="<?=$tconfig["tsite_url_main_admin"]?>js/moment.min.js"></script>
<!-- <script type="text/javascript" src="admin/js/bootstrap-datetimepicker.min.js"></script> -->
<script type="text/javascript">

    var TSITE_UPLOAD_DOCS_FILE_EXTENSIONS_org = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS = "<?php echo  $tconfig["tsite_upload_docs_file_extensions"]; ?>";
    var TSITE_UPLOAD_DOCS_FILE_EXTENSIONS = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS.split(',');

    function confirm_delete(id, driverid) {
        //alert('sdf');
        var tsite_url = '<?php echo $tconfig["tsite_url"]; ?>';
        if (id != '') {
            var confirm_ans = confirm('<?= addslashes($langage_lbl['LBL_DELETE_VEHICLE_CONFIRM_MSG']); ?>');
            if (confirm_ans == true) {
                window.location.href = "vehicle?action=delete&id=" + id + "&driverid=" + driverid;
            }
        }
        //document.getElementById(id).submit();
    }

    function del_veh_doc(id, type, img) {
        ans = confirm('<?= addslashes($langage_lbl['LBL_CONFIRM_DELETE_DOC']); ?>');
        if (ans == true) {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_delete_docimage.php',
                'AJAX_DATA': "veh_id=" + id + "&type=" + type + "&img=" + img + "&doc_type=veh_doc",
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    var url = window.location.href;
                    $("#" + type + "_" + id).load(url + " #" + type + "_" + id);
                } else {
                    console.log(response.result);
                }
            });
        } else {
            return false;
        }
    }

    $(function () {
        newDate = new Date('Y-M-D');
        $('.dp123').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            ignoreReadonly: true,
            beforeShow: function (input, inst) {
                inst.dpDiv.css({
                    marginTop: -(input.offsetHeight) + 'px',
                });
            }
        });
    });

    function validate_fileextension(formid, filename) {
        //var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'doc', 'docx', 'pdf'];
        var fileExtension = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS;
        if(filename != ""){
        if ($.inArray(filename.split('.').pop().toLowerCase(), fileExtension) == -1)
        {
            // $("#" + formid + " .fileerror").html("Only formats are allowed : " + fileExtension.join(', '));
            $("#" + formid + " .fileerror").html(docUploadingExtenstionMsg);
            $('.save-document').prop("disabled", true);
            return false;
        } else {
            $('.save-document').prop("disabled", false);
            $("#" + formid + " .fileerror").html("");
            }
        } else {
            $("#" + formid + " .fileupload-preview.thumbnail").html('<?= $langage_lbl['LBL_STEP_UPLOAD_FILE_TXT'] ?>');
        }
    }

    $(".readonlyNew").on('keydown paste', function (e) {
        e.preventDefault();
    });
    if ($('#my-trips-data').length > 0) {
        $('#my-trips-data').DataTable({
            "oLanguage": langData,
            "bSort": false,
            "bFilter": false,
            "bLengthChange": false,
            "bPaginate": false,
            "bInfo": false, // hide showing entries
        });
    }
    $('.expanding').on('click', function (e) {
        $(this).toggleClass('active-menu');
        $(this).closest('tr').next('.accordian-data').slideToggle(0);
    })
    $(document).on('click', '.dismissUpload', function () {
        $(this).next('.fileupload-preview.thumbnail').html('<?= $langage_lbl['LBL_STEP_UPLOAD_FILE_TXT'] ?>');
    });
</script>
</body>
</html>

