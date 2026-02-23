<?php
include_once('../common.php');
require_once(TPATH_CLASS . "Imagecrop.class.php");

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'banners';
$script = 'Banners';
$count_all = 1;
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();
$whereserviceId = " AND iServiceId = 0";
$ssqlbuyanyservice = "";
$iVehicleCategoryIdSql = '';
if (isset($_REQUEST['eBuyAnyService']) && in_array($_REQUEST['eBuyAnyService'], [
        'Genie',
        'Runner',
        'Anywhere'
    ]) && $MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
    $ssqlbuyanyservice = " AND eBuyAnyService = '" . $_REQUEST['eBuyAnyService'] . "' ";
    if ($_REQUEST['eBuyAnyService'] == "Genie" || $_REQUEST['eBuyAnyService'] == "Anywhere") {
        $ssqlbuyanyservice = " AND eBuyAnyService = 'Genie' ";
    }
} elseif (isset($_REQUEST['eForService']) && in_array($_REQUEST['eForService'], ['MoreDelivery', 'ServiceProvider', 'Bidding'])) {
    $eForBanner = isset($_REQUEST['eFor']) ? $_REQUEST['eFor'] : '';
    $eTypeBanner = "";
    if ($eForBanner == "DeliveryCategory") {
        $eTypeBanner = "Deliver";
    } elseif ($eForBanner == "DeliverAllCategory") {
        $eTypeBanner = "DeliverAll";
    } elseif ($eForBanner == "UberX" || $eForBanner == "VideoConsult" || $eForBanner == "Bidding") {
        $eTypeBanner = $eForBanner;
        $iVehicleCategoryId = $_REQUEST['iVehicleCategoryId'];
        $iVehicleCategoryIdSql = " AND iVehicleCategoryId = '$iVehicleCategoryId' ";
    }
    $ssqlbuyanyservice = " AND eType = '$eTypeBanner' $iVehicleCategoryIdSql ";
    $script = 'VehicleCategory_' . $eTypeBanner;
    if($eTypeBanner == "Bidding") {
        $script = 'bidding';
    }
} else {
    $ssqlbuyanyservice = " AND eBuyAnyService = '' ";
}
/* to fetch max iDisplayOrder from table for insert */
$sql_1 = $sql_2 = $sql_3 = "";
if (isset($_REQUEST['eType']) && $_REQUEST['eType'] == 'NearBy') {
    $sql_1 = " AND eType IN ('NearBy')";
} elseif (isset($_REQUEST['eType']) && in_array($_REQUEST['eType'], ['Genie', 'Runner'])) {
    $sql_1 = " AND eType IN ('" . $_REQUEST['eType'] . "')";
} else {
    $sql_1 = " AND eType NOT IN ('NearBy', 'Genie', 'Runner')";
}

if (isset($_REQUEST['eFor'])) {
    if(!empty($_REQUEST['eFor'])) {
        if($_REQUEST['eFor'] != "DeliveryCategory") {
            $sql_2 = " AND eType IN ('" . $_REQUEST['eFor'] . "')";
        } else {
            $sql_2 = " AND eType IN ('Deliver')";
        }
    } else {
        $sql_2 = " AND eType IN ('General')";
    }
}

if(isset($_REQUEST['iVehicleCategoryId'])) {
    $sql_3 = " AND iVehicleCategoryId = '" . $_REQUEST['iVehicleCategoryId'] . "'";
}

$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name . " WHERE 1 AND eType NOT IN ('RentItem','RentEstate','RentCars')  " . $sql_1 . $sql_2 . $sql_3 . " $whereserviceId AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder = $nxtDispNo = $iDisplayOrder + 1; // Maximum order number
$serviceCatArr = json_decode(serviceCategories, true);
$getLangData = $obj->MySQLSelect("SELECT vCode,vTitle,eDefault FROM language_master ORDER BY `eDefault`");
$count_all = scount($getLangData);
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : 0;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
$eBuyAnyService = isset($_POST['eBuyAnyService']) ? $_POST['eBuyAnyService'] : "";
$eType = isset($_POST['eType']) ? $_POST['eType'] : "General";
$eForService = isset($_POST['eForService']) ? $_POST['eForService'] : "";
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '-1';
$vStatusBarColor = isset($_POST['vStatusBarColor']) ? $_POST['vStatusBarColor'] : '';
$iCopyForOther = isset($_POST['iCopyForOther']) ? $_POST['iCopyForOther'] : 'off';
$eBuyAnyServiceReq = "";

if(isset($_REQUEST['eType']) && !empty($_REQUEST['eType'])){
    $script = $_REQUEST['eType']."_banner";
}
$permission_banner = "app-home-screen-banner";
if (isset($_REQUEST['eType']) && in_array($_REQUEST['eType'], ['Genie','Runner','Anywhere']) && $MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
    $eBuyAnyServiceReq = '?eType=' . $_REQUEST['eType'];
    if ($action == "Add") {
        $eType = $_REQUEST['eType'];
    }

    $permission_banner = "banner-genie-delivery";
    if($_REQUEST['eType'] == "Runner") {
        $permission_banner = "banner-runner-delivery";
    }

} elseif (isset($_REQUEST['eForService']) && in_array($_REQUEST['eForService'], ['MoreDelivery', 'ServiceProvider', 'Bidding'])) {
    
    $eForBanner = isset($_REQUEST['eFor']) ? $_REQUEST['eFor'] : '';
    $eType = "";
    $script = "MoreDelivery_banner";
    if ($eForBanner == "DeliveryCategory") {
        $eType = "Deliver";
        $permission_banner = "banner-parcel-delivery";
    } elseif ($eForBanner == "DeliverAllCategory") {
        $eType = "DeliverAll";
        $permission_banner = "banner-store";
    } elseif ($eForBanner == "UberX" || $eForBanner == "VideoConsult" || $eForBanner == "Bidding") {
        $eTypeBanner = $eType = $eForBanner;
        $script = 'VehicleCategory_' . $eType;
        $iVehicleCategoryId = $_REQUEST['iVehicleCategoryId'];
        $iVehicleCategoryIdSql = " AND iVehicleCategoryId = '$iVehicleCategoryId' ";
        $eForBanner .= "&iVehicleCategoryId=" . $iVehicleCategoryId;
        $_REQUEST['eFor'] .= "&iVehicleCategoryId=" . $iVehicleCategoryId;
        // $permission_banner = "banner-uberx";
    }
    
    $eBuyAnyServiceReq = '?eForService=' . $_REQUEST['eForService'] . '&eFor=' . $_REQUEST['eFor'];
} elseif (isset($_REQUEST['eType']) && in_array($_REQUEST['eType'], ['NearBy'])) {
    $eType = 'NearBy';
    $eBuyAnyServiceReq = '?eType=' . $_REQUEST['eType'];

    $permission_banner = "banners-nearby";
}

if ($_REQUEST['vCode'] != "") {
    $searchvCode = "?langSearch=" . $_REQUEST['vCode'] . "&vCode=" . $_REQUEST['vCode'];
    if ($eBuyAnyServiceReq != "") {
        $searchvCode = "&langSearch=" . $_REQUEST['vCode'] . "&vCode=" . $_REQUEST['vCode'];
    }
    $eBuyAnyServiceReq = $eBuyAnyServiceReq . $searchvCode;
}

$permission_banner_view = "view-".$permission_banner;
$permission_banner_create = "create-".$permission_banner;
$permission_banner_edit = "edit-".$permission_banner;
$permission_banner_delete = "delete-".$permission_banner;
$permission_banner_update_status = "update-status-".$permission_banner;

if (!$userObj->hasPermission($permission_banner_view)) {
    $userObj->redirect();
}

if (isset($_POST['submit'])) { //form submit
    $vCodeLang = isset($_POST['vCode']) ? $_POST['vCode'] : 0;
    if ($action == "Add" && !$userObj->hasPermission($permission_banner_create)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create banner.';
        header("Location:banner.php" . $eBuyAnyServiceReq);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission($permission_banner_edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update banner.';
        header("Location:banner.php" . $eBuyAnyServiceReq);
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:banner.php" . $eBuyAnyServiceReq);
        exit;
    }
    $updateSql = $updateSql1 = $updateSql2 = '';
    if ($_REQUEST['eType'] == 'NearBy') {
        $updateSql = " AND eType IN ('NearBy')";
    } elseif (in_array($_REQUEST['eType'], ['Genie', 'Runner'])) {
        $updateSql = " AND eType IN ('" . $_REQUEST['eType'] . "')";
    } else {
        $updateSql = " AND eType NOT IN ('NearBy', 'Genie', 'Runner')";
    }

    if (isset($_REQUEST['eFor'])) {
        if(!empty($_REQUEST['eFor'])) {
            if($_REQUEST['eFor'] != "DeliveryCategory") {
                $updateSql1 = " AND eType IN ('" . $_REQUEST['eFor'] . "')";
            } else {
                $updateSql1 = " AND eType IN ('Deliver')";
            }
        } else {
            $updateSql1 = " AND eType IN ('General')";
        }
    }

    if(isset($_REQUEST['iVehicleCategoryId'])) {
        $updateSql2 = " AND iVehicleCategoryId = '" . $_REQUEST['iVehicleCategoryId'] . "'";
    }
    
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            echo "<pre>";print_r("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE eType NOT IN('RentItem','RentEstate','RentCars') " . $updateSql . $updateSql1 . $updateSql2 . " AND iDisplayOrder = " . $i . $whereserviceId . " AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE eType NOT IN('RentItem','RentEstate','RentCars') " . $updateSql . $updateSql1 . $updateSql2 . " AND iDisplayOrder = " . $i . $whereserviceId . " AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $setOrder = $i - 1;
            if ($i == 1) {
                $setOrder = $nxtDispNo;
            }
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . $setOrder . " WHERE eType NOT IN('RentItem','RentEstate','RentCars') " . $updateSql . $updateSql1 . $updateSql2 . " AND  iDisplayOrder = " . $i . $whereserviceId . " AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");
        }
    }
    $select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM " . $tbl_name . " WHERE vCode = '" . $vCodeLang . "'");
    $iUniqueId = isset($select_order[0]['iUniqueId']) ? $select_order[0]['iUniqueId'] : 0;
    $iUniqueId = $iUniqueId + 1; // Maximum order number
    $default_image = array();
    $test = false;

    if($action == 'Edit'){
        $sql1 = "SELECT vImage FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $default_lang . "'";
        $db_data1 = $obj->MySQLSelect($sql1);
        $old_name = $db_data1[0]['vImage'];
    }
    if ($count_all > 0) {
        foreach ($getLangData as $lk => $lvalue) {
            $langName = 'vImage_'.$lvalue['vCode'];
            $image_object = $_FILES[$langName]['tmp_name'];
            $image_name = $_FILES[$langName]['name'];

            if ($image_name != "") {
                $filecheck = basename($_FILES[$langName]['name']);
                $fileextarr = explode(".", $filecheck);
                $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                $flag_error = 0;
                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES[$langName], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                $image_info = getimagesize($_FILES[$langName]["tmp_name"]);
                $image_width = $image_info[0];
                $image_height = $image_info[1];
                if ($error) {
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = $error;
                    header("Location:banner.php" . $eBuyAnyServiceReq);
                    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
                    $vImage = $img[0];
                    if($lvalue['vCode'] == $default_lang){
                        array_push($default_image, $vImage);
                    }
                }
            } else {
                if(!empty($id)){
                    $sql2 = "SELECT vImage FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $lvalue['vCode'] . "'";
                    $db_data2 = $obj->MySQLSelect($sql2);
                    $old_name2 = $db_data2[0]['vImage'];

                    if(!empty($default_image)){
                        if($old_name == $old_name2) {
                            $vImage = $default_image[0];
                        } else {
                            $sql = "SELECT vImage FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $lvalue['vCode'] . "'";
                            $db_data = $obj->MySQLSelect($sql);
                            if(!empty($db_data)){
                                $vImage = $db_data[0]['vImage'];
                            }
                        } 
                    } else {
                        $sql = "SELECT vImage FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $lvalue['vCode'] . "'";
                        $db_data = $obj->MySQLSelect($sql);
                        if(!empty($db_data)){
                            $vImage = $db_data[0]['vImage'];
                        }
                    }
                } else {
                    if(!empty($default_image)){
                        $vImage = $default_image[0];
                    }
                }
            } 
            $Data_banner = array();
            $Data_banner['vTitle'] = $vTitle;
            $Data_banner['vImage'] = $vImage;
            $Data_banner['eStatus'] = $eStatus;
            $Data_banner['iUniqueId'] = $iUniqueId;
            $Data_banner['iDisplayOrder'] = $iDisplayOrder;
            $Data_banner['iServiceId'] = $iServiceId;
            $Data_banner['vCode'] = $lvalue['vCode'];
            $Data_banner['iLocationid'] = $iLocationId;
            $Data_banner['eType'] = $eType;
            $Data_banner['vStatusBarColor'] = $vStatusBarColor;
            $Data_banner['iVehicleCategoryId'] = !empty($_REQUEST['iVehicleCategoryId']) ? $iVehicleCategoryId : 0;

            $where = "";
            if ($id != '') {
                $where = " `iUniqueId` = '" . $id . "' AND vCode = '" . $lvalue['vCode'] . "'";
                $iUniqueId = $id;
            }
            if (!empty($id) && !empty($lvalue['vCode'])) {
                $sqlrecord = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode,eBuyAnyService FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' AND vCode = '" . $lvalue['vCode'] . "'";
                $db_records = $obj->MySQLSelect($sqlrecord);
                if (empty($db_records)) {
                    $q = "INSERT INTO ";
                    $where = '';
                }
            }

            if(empty($where)) {
                $obj->MySQLQueryPerform($tbl_name, $Data_banner, "insert");
            } else {
                $obj->MySQLQueryPerform($tbl_name, $Data_banner, "update", $where);
            }
            if ($id != '') {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            }
        }
        header("Location:banner.php" . $eBuyAnyServiceReq);
        exit();
    }
}
// for Edit
if ($action == 'Edit') {
    //$vCodeLang = !empty($vCodeLang) ? $vCodeLang : $default_lang;
    $sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode,eBuyAnyService,iLocationid,eType,vStatusBarColor FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $vCodeLang . "'";
    $db_data = $obj->MySQLSelect($sql);
    $iUniqueId = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vTitle = $value['vTitle'];
            $eStatus = $value['eStatus'];
            $vImage = $value['vImage'];
            $iDisplayOrder = $value['iDisplayOrder'];
            $iServiceId = $value['iServiceId'];
            $vCodeLang = $value['vCode'];
            $iLocationId = $value['iLocationid'];
            $eBuyAnyService = $value['eBuyAnyService'];
            $vStatusBarColor = $value['vStatusBarColor'];
            $eType = $value['eType'];
        }
    }
}
$sql_location = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'Banner' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sql_location);

if(empty($eType)){
    $eType =  "General";
}

?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Banner <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
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
                    <h2><?= $action; ?> Banner</h2>
                    <a href="banner.php<?= $eBuyAnyServiceReq ?>">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 0 && $_REQUEST['var_msg'] != "") { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <? echo $_REQUEST['var_msg']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <form method="post" id="banner_action" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="eBuyAnyService" value="<?= $eBuyAnyService ?>">
                        <input type="hidden" name="eType" value="<?= $eType ?>">
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="vTitle" id="vTitle" value="<?= $vTitle ?>"
                                       class="form-control"/>
                            </div>
                        </div>
                        <? if ($MODULES_OBJ->isEnableLocationwiseBanner()) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Select Location
                                        <span class="red"> *</span>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                           data-original-title='Select the location in which you would like to appear this banner. For example banner to appear for any specific city or state or may be for whole country. You can define these locations from "Manage Locations >> Geo Fence Location" section'></i>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name='iLocationId' id="iLocationId" required="">
                                        <option value="">Select Location</option>
                                        <option value="-1" <? if ($iLocationId == "-1") { ?>selected<? } ?>>All</option>
                                        <?php
                                        foreach ($db_location as $i => $row) { ?>
                                            <option value="<?= $row['iLocationId'] ?>" <? if ($iLocationId == $row['iLocationId']) { ?>selected<? } ?>><?= $row['vLocationName'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location
                                    </a>
                                </div>
                            </div>
                        <? } ?>
                        <div class="bannerlang">
                            <input type="hidden" name="vCode" value="<?= $vCodeLang ?>">
                            <?php for ($i = 0; $i < $count_all; $i++){ 
                                $vCode = $getLangData[$i]['vCode'];
                                $Title = $getLangData[$i]['vTitle'];
                                $eDefault = $getLangData[$i]['eDefault'];
                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                $name = "vImage_".$vCode;
                                $sql = "SELECT vImage FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $vCode . "'";
                                $db_data = $obj->MySQLSelect($sql);
                                if(!empty($db_data)){
                                    $vImage = $db_data[0]['vImage'];
                                }
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Image (<?= $Title; ?>) <?php echo $required_msg; ?></label>
                                </div>
                                <div class="col-lg-6">
                                    <?php if (!empty($vImage)) { ?>
                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=300&src=' . $tconfig['tsite_upload_images'] . $vImage; ?>" style="margin-bottom: 10px;">
                                        <input class="form-control" type="file" name="<?=$name;?>" id="<?=$name;?>" value="<?= $vImage; ?>"/>
                                    <?php } else { ?>
                                        <input class="form-control"  type="file" name="<?=$name;?>" id="<?=$name;?>" value="<?= $vImage; ?>"/>
                                    <?php } ?>
                                    <div style="margin-top: 10px">
                                    <?php if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) { ?>
                                        [Note: Recommended dimension for banner image is 3150px X 1350px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]
                                    <?php } else { ?>
                                        [Note: Recommended dimension for banner image is 2880px X 1620px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]
                                    <?php } ?>
                                    </div>
                                </div>
                            </div><?php } ?>
                        </div>
                        <?php if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV2()) { ?>
                            <div style="display: none" class="row">
                                <div class="col-lg-12">
                                    <label>App Status Bar Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" id="StatusBarColor" class="form-control"
                                           value="<?= $vStatusBarColor ?>"/>
                                    <input type="hidden" name="vStatusBarColor" id="vStatusBarColor"
                                           value="<?= $vStatusBarColor ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox"
                                           name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Order</label>
                            </div>
                            <div class="col-lg-6">
                                <span id="orderdiv">
                                <?php
                                $temp = 1;
                                $dataArray = array();
                                $sql_1 = $sql_2 = $sql_3 = "";
                                if (isset($_REQUEST['eType']) && $_REQUEST['eType'] == 'NearBy') {
                                    $sql_1 = " AND eType  IN ('NearBy')";
                                } elseif (isset($_REQUEST['eType']) && in_array($_REQUEST['eType'], ['Genie', 'Runner'])) {
                                    $sql_1 = " AND eType IN ('" . $_REQUEST['eType'] . "')";
                                } else {
                                    $sql_1 = " AND eType NOT IN ('NearBy', 'Genie', 'Runner')";
                                }

                                if (isset($_REQUEST['eFor'])) {
                                    if(!empty($eTypeBanner)) {
                                        $sql_2 = " AND eType IN ('$eTypeBanner')";
                                    } else {
                                        $sql_2 = " AND eType IN ('General')";
                                    }
                                }

                                if(isset($_REQUEST['iVehicleCategoryId'])) {
                                    $sql_3 = " AND iVehicleCategoryId = '" . $_REQUEST['iVehicleCategoryId'] . "'";
                                }

                                $query1 = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE eType NOT IN ('RentItem','RentEstate','RentCars') " . $sql_1 . $sql_2 . $sql_3 . "  $whereserviceId AND vCode = '$vCodeLang' $ssqlbuyanyservice ORDER BY iDisplayOrder";
                                $data_order = $obj->MySQLSelect($query1);                                
                                
                                $uniqueOrders = [];
                                foreach ($data_order as $item) {
                                    $uniqueOrders[$item['iDisplayOrder']] = $item;
                                }
                                $data_order = array_values($uniqueOrders);
                                foreach ($data_order as $k=>$value) {
                                    $dataArray[] = $k+1;
                                    $temp = $iDisplayOrder;
                                }
                                ?>
                                <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp ?>">
                                <select name="iDisplayOrder" class="form-control">
                                    <?php foreach ($dataArray as $arr): ?>
                                        <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>">
                                            -- <?= $arr ?> --
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if ($action == "Add") { ?>
                                        <option value="<?= $temp; ?>" selected="selected">
                                            -- <?= $temp ?> --
                                        </option>
                                    <?php } ?>
                                </select>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <?php if (($action == 'Edit' && $userObj->hasPermission($permission_banner_edit)) || ($action == 'Add' && $userObj->hasPermission($permission_banner_create))) { ?>
                                <div class="col-lg-12">
                                    <input type="submit" class="save btn-info" name="submit" id="submit"
                                           value="<?= $action; ?> Banner">
                                    <a href="banner.php<?= $eBuyAnyServiceReq ?>" class="btn btn-default back_link">
                                        Cancel
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    function bannerdata(val) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>banner_lang.php',
            'AJAX_DATA': {
                vCode: val,
                id: '<?= $_REQUEST['id']; ?>',
                order: 'Yes',
                eBuyAnyService: '<?= $eBuyAnyService ?>',
                eForService: '<?= $eForService ?>'
            },
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml2 = response.result;
                if (dataHtml2 != "") {
                    $('#orderdiv').html(dataHtml2);
                    //$('.bannerlang').html(dataHtml2);
                }
            } else {
                console.log(response.result);
            }
        });
    }

    $("#StatusBarColor").on("input", function () {
        var color = $(this).val();
        $('#vStatusBarColor').val(color);
    });
    var valid = '<?= $id; ?>';
    if(valid == "") {
        if ($('#banner_action').length !== 0) {
            var name = 'vImage_<?= $default_lang ?>';
            $("#banner_action").validate({
                rules: {
                    vImage_<?= $default_lang ?>: {
                        required : true,
                        extension: imageUploadingExtenstionjsrule
                    }
                },
                messages: {
                    vImage:{
                        required: requiredFieldMsg,
                        extension: imageUploadingExtenstionMsg
                    }
                },
            });
        }
    }
</script>
</body>
<!-- END BODY-->
</html>