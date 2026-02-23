<?php
include_once('../common.php');
require_once(TPATH_CLASS . "Imagecrop.class.php");
$mId = $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId
$eFor = isset($_REQUEST['eFor']) ? $_REQUEST['eFor'] : ''; // iUniqueId
$eBuyAnyService = isset($_REQUEST['eBuyAnyService']) ? $_REQUEST['eBuyAnyService'] : ''; // iUniqueId
$tbl_name = $master_service_category_tbl;
if($eFor=="AppHomeScreen" && $eBuyAnyService=="Genie"){
	$sql = "SELECT *,eServiceType as eType FROM app_home_screen_view  WHERE eServiceType = '" . $eBuyAnyService . "'";
	$permissionQuery = $obj->MySQLSelect($sql);
	$id=$permissionQuery[0]['iViewId'];
}else{
$sql = "SELECT eType FROM " . $tbl_name . " WHERE iMasterServiceCategoryId = '" . $mId . "'";
$permissionQuery = $obj->MySQLSelect($sql);
}
$titleTxt = " Master Service Category";
if ($permissionQuery[0]['eType'] == 'Ride') {
    $commonTxt .= 'taxi-service';
    $titleTxt = "Taxi Service";
}
if ($permissionQuery[0]['eType'] == 'Genie') {
    $commonTxt .= 'deliverall';
    $titleTxt = "Genie Service";
}
if ($permissionQuery[0]['eType'] == 'Deliver') {
    $commonTxt .= 'parcel-delivery';
    $titleTxt = "Parcel Delivery";
}
if ($permissionQuery[0]['eType'] == 'DeliverAll') {
    $commonTxt .= 'deliverall';
    $titleTxt = "Store Delivery";
}
if ($permissionQuery[0]['eType'] == 'VideoConsult') {
    $commonTxt .= 'video-consultation';
    $titleTxt = "Video Consultation";
}
if ($permissionQuery[0]['eType'] == 'Bidding') {
    $commonTxt .= 'bidding';
    $titleTxt = "Bidding";
}
if ($permissionQuery[0]['eType'] == 'UberX') {
    $commonTxt .= 'uberx';
    $titleTxt = "On-Demand Service";
}
if ($permissionQuery[0]['eType'] == 'RentEstate') {
    $commonTxt .= 'rentestate';
    $titleTxt = "Buy, Sell & Rent Real Estate";
}
if ($permissionQuery[0]['eType'] == 'RentCars') {
    $commonTxt .= 'rentcars';
    $titleTxt = "Buy,Sell & Rent Cars";
}
if ($permissionQuery[0]['eType'] == 'RentItem') {
    $commonTxt .= 'rentitem';
    $titleTxt = "Buy,Sell & Rent Items";
}
if ($permissionQuery[0]['eType'] == 'MedicalServices') {
    $commonTxt .= 'medical';
    $titleTxt = "Medical Services";
}
if ($permissionQuery[0]['eType'] == 'RideShare') {
    $commonTxt .= 'rideshare';
    $titleTxt = "Ride Share";
}
if ($permissionQuery[0]['eType'] == 'TrackAnyService') {
    $commonTxt .= 'trackanyservice';
    $titleTxt = "Tracking Service";
}
if ($permissionQuery[0]['eType'] == 'TrackService') {
    $commonTxt .= 'trackservice';
    $titleTxt = "Tracking Service";
}
if ($permissionQuery[0]['eType'] == 'NearBy') {
    $commonTxt .= 'nearby';
    $titleTxt = "NearBy";
}

if ($permissionQuery[0]['eType'] == 'Parking') {
    $commonTxt .= 'parking-service';
    $titleTxt = "Parking";
}

if ($permissionQuery[0]['eType'] == 'TaxiBid') {
    $commonTxt .= 'taxi-bid-service';
    $titleTxt = TAXI_BID;

    $labelsTaxiBid = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_TAXI_BID_PAGE_TITLE', 'LBL_TAXI_BID_PAGE_DESC') ");

    $TaxiBidPageTitleArr = $TaxiBidPageSubTitleArr = array();
    foreach ($labelsTaxiBid as $label) {
        if ($label['vLabel'] == 'LBL_TAXI_BID_PAGE_TITLE') {
            $TaxiBidPageTitleArr[$label['vCode']] = $label['vValue'];

        } elseif ($label['vLabel'] == 'LBL_TAXI_BID_PAGE_DESC') {
            $TaxiBidPageSubTitleArr[$label['vCode']] = $label['vValue'];

        }
    }

    $TaxiBidInfoImg = AppHomeScreenCls::getTaxiBidInfoImage();
    $vImageOldTaxiBidInfo = $TaxiBidInfoImg['TaxiBid']['vInfoImage'];
}

$view = "view-service-content-" . $commonTxt;
$update = "update-service-content-" . $commonTxt;
$updateStatus = "update-status-service-content-" . $commonTxt;
if (!$userObj->hasPermission($view) || empty($id)) {
    $userObj->redirect();
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
$script = 'VehicleCategory';
$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vTextColor = isset($_POST['vTextColor']) ? $_POST['vTextColor'] : '#ffffff';
$vBgColor = isset($_POST['vBgColor']) ? $_POST['vBgColor'] : '#ffffff';
$banner_lang = isset($_REQUEST['banner_lang']) ? $_REQUEST['banner_lang'] : $default_lang;
$thumb = new thumbnail();
if (isset($_POST['submit'])) { //form submit
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:master_service_category.php");
        exit;
    }

    $eType = $_POST['eType'];
    if (in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService','TaxiBid']) || strtoupper(IS_CUBEX_APP) == "YES") {
        $db_data_master = $obj->MySQLSelect("SELECT vCategoryImage FROM " . $tbl_name . " WHERE iMasterServiceCategoryId = '" . $id . "'");
        $tCatImagesArr = array();
        if (!empty($db_data_master[0]['vCategoryImage'])) {
            $tCatImagesArr = json_decode($db_data_master[0]['vCategoryImage'], true);
        } else {
            foreach ($db_master as $dbvalue) {
                $tCatImagesArr['vCategoryImage_' . $dbvalue['vCode']] = '';
            }
        }
    }
    $Data_Update = array();
    $image_object = $_FILES['vImage1']['tmp_name'];
    $image_name = $_FILES['vImage1']['name'];
    if ($image_name != "") {
        $filecheck = basename($_FILES['vImage1']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
        }
        $image_info = getimagesize($_FILES["vImage1"]["tmp_name"]);
        $image_width = $image_info[0];
        $image_height = $image_info[1];
        if ($flag_error == 1) {
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $var_msg;
            header("Location:master_service_category_action.php?id=" . $mId);
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
            $vImage = $img[0];
            if (in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService','TaxiBid']) || strtoupper(IS_CUBEX_APP) == "YES") {
                $tCatImagesArr['vCategoryImage_' . $banner_lang] = $vImage;
                $Data_Update['vCategoryImage'] = json_encode($tCatImagesArr);
                if ($banner_lang == $default_lang) {
                    $Data_Update['vIconImage1'] = $vImage;
                }
            } else {
                $Data_Update['vIconImage1'] = $vImage;
            }
            if (!empty($_POST['vImage1_old']) && file_exists($Photo_Gallery_folder . $_POST['vImage1_old']) && !in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService', 'Parking'])) {
                if(SITE_TYPE != "Demo") {
                    unlink($Photo_Gallery_folder . $_POST['vImage1_old']);    
                }
            }
        }
    }
    for ($i = 0; $i < scount($db_master); $i++) {
		if (!in_array($eType, ['Genie'])) {
            $vCategory = $vCategoryName = ""; 
            if (isset($_POST['vCategoryName_' . $db_master[$i]['vCode']])) {
                $vCategoryName = $_POST['vCategoryName_' . $db_master[$i]['vCode']];
            }
            $vCategoryNameArr["vCategoryName_" . $db_master[$i]['vCode']] = $vCategoryName;
            $vCategoryDesc = "";
            if (isset($_POST['vCategoryDesc_' . $db_master[$i]['vCode']])) {
                $vCategoryDesc = $_POST['vCategoryDesc_' . $db_master[$i]['vCode']];
            }
            $vCategoryDescArr["vCategoryDesc_" . $db_master[$i]['vCode']] = $vCategoryDesc;
		}
		$vDescription = "";
	        if (isset($_POST['vDescription_' . $db_master[$i]['vCode']])) {
	            $vDescription = $_POST['vDescription_' . $db_master[$i]['vCode']];
	        }
		$vDescriptionArr["vDescription_" . $db_master[$i]['vCode']] = $vDescription;

        
        if (isset($_POST['vCategory_' . $db_master[$i]['vCode']])) {
            $vCategory = $_POST['vCategory_' . $db_master[$i]['vCode']];
        }
        $vCategoryArr["vCategory_" . $db_master[$i]['vCode']] = $vCategory;

		$vTitle = "";
	        if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
	            $vTitle = $_POST['vTitle_' . $db_master[$i]['vCode']];
	        }
		$vTitleArr["vTitle_" . $db_master[$i]['vCode']] = $vTitle;
    }
    if (!in_array($eType, ['Genie'])) {
        $jsonCategoryName = getJsonFromAnArr($vCategoryNameArr);
        if (in_array($eType, ['RideShare', 'TrackService'])) {
            $jsonCategoryDesc = getJsonFromAnArr($vCategoryDescArr);
            $Data_Update['vCategoryDesc'] = $jsonCategoryDesc;
        }
        $Data_Update['vCategoryName'] = $jsonCategoryName;
	}
    $jsonDescription = getJsonFromAnArr($vDescriptionArr);
    $Data_Update['vDescription'] = $jsonDescription;
    $jsonTitle = getJsonFromAnArr($vTitleArr);
    $Data_Update['vTitle'] = $jsonTitle;
	if (!in_array($eType, ['Genie'])) {
        if ($userObj->hasPermission($updateStatus)) {
            $status = $Data_Update['eStatus'] = $eStatus;
            $ssql = getMasterServiceCategoryQuery($eType, 'Yes');
            if (!in_array($eType, ['Bidding', 'MedicalServices', 'TrackService', 'TrackAnyService', 'RideShare', 'RentEstate', 'RentCars', 'RentItem', 'NearBy', 'Parking'])) {
                //$sql_vehicle_category_table_name = getVehicleCategoryTblName();
                $vehicle_category_data = $obj->MySQLSelect("SELECT vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo,vc.vListLogo1,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType,  (select count(iVehicleCategoryId) from " . $sql_vehicle_category_table_name . " where iParentId = vc.iVehicleCategoryId AND eStatus != 'Deleted') as SubCategories FROM " . $sql_vehicle_category_table_name . " as vc WHERE eStatus != 'Deleted' AND vc.iParentId='0' $ssql");
                foreach ($vehicle_category_data as $vehicle_category) {
                    $statusNew = $status;
                    if ($status == "Active") {
                        $checkLog = $obj->MySQLSelect("SELECT eStatus FROM vehicle_category_status_log WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");
                        if (!empty($checkLog) && $checkLog > 0) {
                            $statusNew = $checkLog[0]['eStatus'];
                        }
                    }
                    $obj->sql_query("UPDATE vehicle_category SET eStatus = '" . $statusNew . "' WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");
                    $vehicle_category_new = $obj->MySQLSelect("SELECT iServiceId FROM vehicle_category WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");
                    if ($vehicle_category_new[0]['iServiceId'] > 0) {
                        $obj->sql_query("UPDATE service_categories SET eStatus = '$statusNew' WHERE iServiceId = '" . $vehicle_category_new[0]['iServiceId'] . "'");
                    }
                if(!empty($vCategoryArr) && in_array($eType, ['Deliver'])){
                    foreach($vCategoryArr as $k => $v){
                        $obj->sql_query("UPDATE vehicle_category SET $k = '" . $v . "' WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");
                    }
                }
                }
                if ($eType == "Ride") {
                    $statusVal = $status == "Active" ? "Yes" : "No";
                    $obj->sql_query("UPDATE configurations SET vValue = '$statusVal', eAdminDisplay = '$statusVal' WHERE vName = 'ENABLE_CORPORATE_PROFILE'");
                }
            }
            
            if(in_array($eType, ['TrackAnyService', 'NearBy', 'VideoConsult'])){
                $obj->sql_query("UPDATE $sql_vehicle_category_table_name SET eStatus = '$status' WHERE eCatType = '".$eType."'");
            }

            if($eType == 'Bidding'){
                $obj->sql_query("UPDATE $sql_vehicle_category_table_name SET eStatus = '$status' WHERE eCatType = 'ServiceBid'");
            }

            if($eType == 'MedicalServices'){
                $obj->sql_query("UPDATE $sql_vehicle_category_table_name SET eStatus = '$status' WHERE eCatType = 'MedicalService'");
            }

        }
        $Data_Update['vTextColor'] = $vTextColor;
        $Data_Update['vBgColor'] = $vBgColor;
	}
    if ($id != '') {
		if($eType == "Genie"){
			$tbl_name="app_home_screen_view";
			$where = " iViewId = '" . $id . "'";
		} else{
            $where = " iMasterServiceCategoryId = '" . $id . "'";
		}
        $id = $obj->MySQLQueryPerform($tbl_name, $Data_Update, 'update', $where);
    } else {
		if($eType=="Genie"){
			$tbl_name="app_home_screen_view";
 		}
        $id = $obj->MySQLQueryPerform($tbl_name, $Data_Update, 'insert');
    }
    $obj->sql_query($query);

    
    if ($eType == "Ride") {
        $config_val = $eStatus == "Active" ? "Yes" : "No";
        $obj->sql_query("UPDATE configurations SET vValue = '$config_val' WHERE vName = 'ENABLE_CORPORATE_PROFILE' ");
        $oCache->flushData();
        $GCS_OBJ->updateGCSData();
    }

    if($eType == "TaxiBid") {
        $vTitleArr = $_POST;
        foreach ($vTitleArr as $k => $vTitleVal) {
            if(startsWith($k, "vTaxiBidInfoTitle_")) {
                $vCode = explode('_', $k)[1];
                $Data_update_lbl = array();
                $Data_update_lbl['vValue'] = $vTitleVal;
                $where = " vCode = '$vCode' AND vLabel = 'LBL_TAXI_BID_PAGE_TITLE' ";
                $obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
            }            
        }

        $vSubTitleArr = $_POST;
        foreach ($vSubTitleArr as $k => $vSubTitleVal) {
            if(startsWith($k, "vTaxiBidInfoSubTitle_")) {
                $vCode = explode('_', $k)[1];
                $Data_update_lbl = array();
                $Data_update_lbl['vValue'] = $vSubTitleVal;
                $where = " vCode = '$vCode' AND vLabel = 'LBL_TAXI_BID_PAGE_DESC' ";
                $obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
            }
        }

        $image_name_taxibid = isset($_FILES['vImageTaxiBidInfo']['name']) ? $_FILES['vImageTaxiBidInfo']['name'] : '';
        $image_object_taxibid = isset($_FILES['vImageTaxiBidInfo']['tmp_name']) ? $_FILES['vImageTaxiBidInfo']['tmp_name'] : '';
        $vImageOldTaxiBid = isset($_REQUEST['vImageOldTaxiBidInfo']) ? $_REQUEST['vImageOldTaxiBidInfo'] : '';

        if ($image_name_taxibid != "") {
            $Data_Update_Category = array();

            $filecheck = basename($_FILES['vImageTaxiBidInfo']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
            }
            $image_info = getimagesize($_FILES["vImageTaxiBidInfo"]["tmp_name"]);
            $image_width = $image_info[0];
            $image_height = $image_info[1];
            if ($flag_error == 1) {
                $returnArr['Action'] = '0';
                $returnArr['message'] = $var_msg;
                echo json_encode($returnArr);
                exit;
            } else {
                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';

                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                    chmod($Photo_Gallery_folder, 0777);
                }
                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object_taxibid, $image_name, '', 'jpg,png,gif,jpeg,bmp');
                $vImageTaxiBid = $img[0];
                if (!empty($vImageOldTaxiBid) && file_exists($Photo_Gallery_folder . $vImageOldTaxiBid)) {
                    // unlink($Photo_Gallery_folder . $vImageOld);
                }
            }
        } else {
            $vImageTaxiBid = $vImageOldTaxiBid;
        }

        $db_data_taxibid = $obj->MySQLSelect("SELECT tServiceDetails FROM app_home_screen_view WHERE eServiceType IN ('TaxiBid', 'Other') ");
        $tServiceDetails = json_decode($db_data_taxibid[0]['tServiceDetails'], true);

        $tServiceDetails['AddStop']['vInfoImage'] = $vImageTaxiBid;
        $Data_update_taxibid = array();
        $Data_update_taxibid['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);
        $where = " eServiceType IN ('TaxiBid', 'Other') ";
        $obj->MySQLQueryPerform('app_home_screen_view', $Data_update_taxibid, 'update', $where);
    }

    $oCache->flushData();
    if ($id != '') {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }

    if (in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService']) || strtoupper(IS_CUBEX_APP) == "Yes") {
        header("Location:master_service_category_action.php?id=" . $mId . "&banner_lang=" . $banner_lang);
    } else {
		 if (in_array($eType,['Genie'])) {
					header("Location:master_service_category_action.php?id=" . $id . "&eFor=AppHomeScreen&eBuyAnyService=Genie");
		}else{
        header("Location:master_service_category_action.php?id=" . $mId);
		}
    }
    exit();
}
$display_banner = $display = "";
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
	if($eFor=="AppHomeScreen" && $eBuyAnyService=="Genie"){
		$sql = "SELECT *,eServiceType as eType FROM app_home_screen_view  WHERE eServiceType = '" . $eBuyAnyService . "'";
	}else{
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iMasterServiceCategoryId = '" . $id . "'";
	}
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) {
		if($eFor=="AppHomeScreen" && $eBuyAnyService=="Genie"){
			 $iViewId = $db_data[0]['iViewId'];
			 $vTitle = json_decode($db_data[0]['vTitle'], true);
			foreach ($vTitle as $key => $value) {
				$userEditDataArr[$key] = $value;
			}
			$vDescription = json_decode($db_data[0]['vDescription'], true);
			foreach ($vDescription as $key => $value) {
				$userEditDataArr[$key] = $value;
			}
			$eType = 'DeliverAll';
			$eType = $db_data[0]['eServiceType'];
			$vIconImage1 = $db_data[0]['vIconImage1'];
		}else{
            $vCategoryName = json_decode($db_data[0]['vCategoryName'], true);
            foreach ($vCategoryName as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
            $vCategoryDesc = json_decode($db_data[0]['vCategoryDesc'], true);
            foreach ($vCategoryDesc as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
			
            $vTitle = json_decode($db_data[0]['vTitle'], true);
			foreach ($vTitle as $key => $value) {
				$userEditDataArr[$key] = $value;
			}
			$vDescription = json_decode($db_data[0]['vDescription'], true);
			foreach ($vDescription as $key => $value) {
				$userEditDataArr[$key] = $value;
			}  
            $vIconImage1 = $db_data[0]['vIconImage1'];
            $vBgImage = $db_data[0]['vBgImage'];
            $vTextColor = $db_data[0]['vTextColor'];
            $vBgColor = $db_data[0]['vBgColor'];
            $eStatus = $db_data[0]['eStatus'];
            $eType = $db_data[0]['eType'];
            if (in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService','TaxiBid']) || strtoupper(IS_CUBEX_APP) == "YES") {
                $tCatImages = json_decode($db_data[0]['vCategoryImage'], true);
                $vIconImage1 = $tCatImages['vCategoryImage_' . $banner_lang];
			}
        }
        
        $vehicle_category_data = $obj->MySQLSelect("SELECT * FROM " . $sql_vehicle_category_table_name . " as vc WHERE eStatus != 'Deleted' AND vc.iParentId='0' AND (eCatType = 'Delivery' OR eCatType = 'MultipleDelivery' OR eCatType = 'MotoDelivery' OR eCatType = 'MoreDelivery')");
        if (scount($vehicle_category_data) > 0) {
            for ($i = 0; $i < scount($db_master); $i++) {
                foreach ($vehicle_category_data as $key => $value) {
                    $vValue = 'vCategory_' . $db_master[$i]['vCode'];
                    $userEditDataArrNew[$vValue] = $value[$vValue];
                }
            }
        }
    }
}
$script = 'mVehicleCategory_' . $eType;
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | <?php echo $titleTxt; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <? include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        .admin-notes ul li {
            padding-bottom: 0;
            font-size: 13px;
        }

        .underline-section-title {
            display: block;
            border-top: 5px solid #799FCB;
            width: 75px;
            margin: 0 0 15px 0;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php'); ?>

    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $action; ?> <?php echo $titleTxt; ?></h2>
                    <?php if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'No' && $THEME_OBJ->isPXTProThemeActive() == 'No') { ?>
                        <a href="master_service_category.php">
                            <input type="button" value="Back to Listing " class="add-btn">
                        </a>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <? if ($success == 0 && !empty($_REQUEST['var_msg'])) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <? echo $_REQUEST['var_msg']; ?>
                        </div>
                        <br/>
                    <? } ?>

                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                    <? } ?>

                    <? if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <? } ?>

                    <?php include('valid_msg.php'); ?>
                    <form method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="eType" value="<?= $eType ?>">
                        <input type="hidden" name="iViewId" value="<?= $iViewId ?>">  
                        <input type="hidden" name="eServiceType" value="<?= $eServiceType ?>">  
                        <input type="hidden" name="banner_lang" value="<?= $default_lang ?>">
                        
                        <div class="row">
                            <input type="hidden" name="vImage1_old" value="<?= $vIconImage1 ?>">
                            <div class="col-lg-12">
                                <?php if (in_array($eType, ['Ride', 'Deliver', 'DeliverAll']) && strtoupper(IS_CUBEX_APP) == "NO") { ?>
                                    <label>
                                        Icon <?= ($vIconImage1 == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                <?php } elseif (in_array($eType, ['RentItem', 'RentCars', 'RentEstate'])) { ?>
                                    <label>
                                        Image <?= ($vIconImage1 == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                <?php } elseif (in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService', 'Parking','TaxiBid']) || strtoupper(IS_CUBEX_APP) == "YES") { ?>
                                    <label>
                                        Banner <?= ($vIconImage1 == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <?php if ($vIconImage1 != '') { ?>
                                    <?php if (!in_array($eType, ['MedicalServices', 'NearBy','UberX'])) { ?>
                                        <div class="marginbottom-10">
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=150&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vIconImage1; ?>">
                                        </div>
                                        <div class="marginbottom-10">
                                            <input type="file" class="form-control" name="vImage1" id="vImage1"
                                                   value=""/>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <div class="marginbottom-10">
                                        <input type="file" class="form-control" name="vImage1" id="vImage1" value=""
                                               required/>
                                    </div>
                                <?php } ?>
                                <div style="margin: 0">
                                    <?php if (in_array($eType, ['Ride', 'Deliver', 'DeliverAll']) && strtoupper(IS_CUBEX_APP) == "NO") { ?>
                                        [Note: Recommended dimension for banner image(.png) is 360px X 360px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]
                                    <?php } elseif (in_array($eType, ['RentItem', 'RentCars', 'RentEstate'])) { ?>
                                        <strong>Note: Recommended dimension for Upload image(.png) is 1050px X 450px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>
                                        </strong>
                                    <?php } elseif (in_array($eType, ['VideoConsult', 'Bidding', 'RideShare', 'TrackService', 'TrackAnyService', 'Parking' , 'Parking','']) || strtoupper(IS_CUBEX_APP) == "YES") { ?>
                                        <?php if (in_array($eType, ['VideoConsult', 'Bidding'])) { ?>
                                            <strong>Note: Recommended dimension to Upload image (png/jpeg) is 1650px X
                                                900px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>
                                            </strong>
                                        <?php } else {
                                            if(strtoupper(IS_CUBEX_APP) == "YES") { ?>
                                                <strong>Note: Recommended dimension to Upload image(png/jpeg) is 3350px X
                                                990px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>
                                            </strong>
                                            <?php } else { ?>
                                            <strong>Note: Recommended dimension to Upload image(png/jpeg) is 1050px X
                                                520px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>
                                            </strong>
                                        <?php } } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!in_array($eType, ['Genie'])) { ?>
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Name</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text"
                                               class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                               id="vCategoryName_Default" name="vCategoryName_Default"
                                               value="<?= $userEditDataArr['vCategoryName_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vCategoryName_' . $default_lang]; ?>"
                                               readonly="readonly"
                                               required <?php if ($id == "") { ?> onclick="editCategoryName('Add')" <?php } ?>>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editCategoryName('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="modal fade" id="Category_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span>
                                                    Category Name
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vCategoryName_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vCategoryName_' . $vCode;
                                                    $$vValue = $userEditDataArr[$vValue];
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
                                                            <label>Title (<?= $vTitle; ?>
                                                                ) <?php echo $required_msg; ?></label>
                                                        </div>
                                                        <div class="<?= $page_title_class ?>">
                                                            <input type="text" class="form-control"
                                                                   name="<?= $vValue; ?>"
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
                                                                                onClick="getAllLanguageCode('vCategoryName_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vCategoryName_', '<?= $default_lang ?>');">
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
                                                            onclick="saveCategoryName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok"
                                                            data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vCategoryName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (in_array($eType, [])) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Category Description</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text"
                                                   class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                                   id="vCategoryDesc_Default" name="vCategoryDesc_Default"
                                                   value="<?= $userEditDataArr['vCategoryDesc_' . $default_lang]; ?>"
                                                   data-originalvalue="<?= $userEditDataArr['vCategoryDesc_' . $default_lang]; ?>"
                                                   readonly="readonly"
                                                   required <?php if ($id == "") { ?> onclick="editCategoryDesc('Add')" <?php } ?>>
                                        </div>
                                        <?php if ($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit" onclick="editCategoryDesc('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="modal fade" id="CategoryDesc_Modal" tabindex="-1" role="dialog"
                                         aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="desc_modal_action"></span>
                                                        Category Description
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vCategoryDesc_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vCategoryDesc_' . $vCode;
                                                        $$vValue = $userEditDataArr[$vValue];
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
                                                                <label>Title (<?= $vTitle; ?>
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
                                                                                    onClick="getAllLanguageCode('vCategoryDesc_', 'EN');">
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
                                                                                    onClick="getAllLanguageCode('vCategoryDesc_', '<?= $default_lang ?>');">
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
                                                                onclick="saveCategoryDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok"
                                                                data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vCategoryDesc_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                    </div>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Name</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vCategoryName_<?= $default_lang ?>"
                                               name="vCategoryName_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vCategoryName_' . $default_lang]; ?>">
                                    </div>
                                </div>
                                <?php if (in_array($eType, ['RideShare', 'TrackService'])) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Category Description</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" class="form-control"
                                                   id="vCategoryDesc_<?= $default_lang ?>"
                                                   name="vCategoryDesc_<?= $default_lang ?>"
                                                   value="<?= $userEditDataArr['vCategoryDesc_' . $default_lang]; ?>">
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                         <?php if (in_array($eType, ['Deliver']) && strtoupper(IS_CUBEX_APP) == "YES") { ?>
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Service Category Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text"
                                               class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                               id="vCategory_Default" name="vCategory_Default"
                                               value="<?= $userEditDataArrNew['vCategory_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArrNew['vCategory_' . $default_lang]; ?>"
                                               readonly="readonly"
                                               required <?php if ($id == "") { ?> onclick="editCategoryNameNew('Add')" <?php } ?>>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editCategoryNameNew('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="modal fade" id="vCategory_ModalName" tabindex="-1" role="dialog"
                                     aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="catmodal_action"></span>
                                                    Service Category Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vCategory_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vCategory_' . $vCode;
                                                    $$vValue = $userEditDataArrNew[$vValue];
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
                                                            <label>Title (<?= $vTitle; ?>
                                                                ) <?php echo $required_msg; ?></label>
                                                        </div>
                                                        <div class="<?= $page_title_class ?>">
                                                            <input type="text" class="form-control"
                                                                   name="<?= $vValue; ?>"
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
                                                                                onClick="getAllLanguageCode('vCategory_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vCategory_', '<?= $default_lang ?>');">
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
                                                            onclick="saveCategoryNameNew()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok"
                                                            data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vCategory_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Name</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vCategory_<?= $default_lang ?>"
                                               name="vCategory_<?= $default_lang ?>"
                                               value="<?= $userEditDataArrNew['vCategory_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>

                        <?php if (in_array($eType, ['VideoConsult', 'Genie', 'Bidding', 'TrackAnyService', 'RideShare'])) { ?>
                            <?php if (scount($db_master) > 1) { ?>
                        		<div class="row">
                        			<div class="col-lg-12">
                        				<label>Title</label>
                        			</div>
                        			<div class="col-md-6 col-sm-6">
                        				<input type="text"
                        					   class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                        					   id="vTitle_Default" name="vTitle_Default"
                        					   value="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>"
                        					   data-originalvalue="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>"
                        					   readonly="readonly"
                        					   required <?php if ($id == "") { ?> onclick="editTitleName('Add')" <?php } ?>>
                        			</div>
                        			<?php if ($id != "") { ?>
                        				<div class="col-lg-2">
                        					<button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editTitleName('Edit')">
                        						<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        					</button>
                        				</div>
                        			<?php } ?>
                        		</div>
                        		<div class="modal fade" id="Title_Modal" tabindex="-1" role="dialog"
                        			 aria-hidden="true"
                        			 data-backdrop="static" data-keyboard="false">
                        			<div class="modal-dialog modal-lg">
                        				<div class="modal-content nimot-class">
                        					<div class="modal-header">
                        						<h4>
                        							<span id="modal_action"></span>
                        							Title
                        							<button type="button" class="close" data-dismiss="modal"
                        									onclick="resetToOriginalValue(this, 'vTitle_')">x
                        							</button>
                        						</h4>
                        					</div>
                        					<div class="modal-body">
                        						<?php
                        						for ($i = 0; $i < $count_all; $i++) {
                        							$vCode = $db_master[$i]['vCode'];
                        							$vTitle = $db_master[$i]['vTitle'];
                        							$eDefault = $db_master[$i]['eDefault'];
                        							$vValue = 'vTitle_' . $vCode;
                        							$$vValue = $userEditDataArr[$vValue];
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
                        									<label>Title (<?= $vTitle; ?>
                        										) <?php echo $required_msg; ?></label>
                        								</div>
                        								<div class="<?= $page_title_class ?>">
                        									<input type="text" class="form-control"
                        										   name="<?= $vValue; ?>"
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
                        														onClick="getAllLanguageCode('vTitle_', 'EN');">
                        													Convert To All Language
                        												</button>
                        											</div>
                        										<?php }
                        									} else {
                        										if ($vCode == $default_lang) { ?>
                        											<div class="col-md-3 col-sm-3">
                        												<button type="button" name="allLanguage"
                        														id="allLanguage" class="btn btn-primary"
                        														onClick="getAllLanguageCode('vTitle_', '<?= $default_lang ?>');">
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
                        									onclick="saveTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                        							<button type="button" class="btn btn-danger btn-ok"
                        									data-dismiss="modal"
                        									onclick="resetToOriginalValue(this, 'vTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                        						</div>
                        					</div>
                        					<div style="clear:both;"></div>
                        				</div>
                        			</div>
                        		</div>
                        	 <div class="row">
                        			<div class="col-lg-12">
                        				<label>Description</label>
                        			</div>
                        			<div class="col-md-6 col-sm-6">
                        				<input type="text"
                        					   class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                        					   id="vDescription_Default" name="vDescription_Default"
                        					   value="<?= $userEditDataArr['vDescription_' . $default_lang]; ?>"
                        					   data-originalvalue="<?= $userEditDataArr['vDescription_' . $default_lang]; ?>"
                        					   readonly="readonly"
                        					   required <?php if ($id == "") { ?> onclick="editDescription('Add')" <?php } ?>>
                        			</div>
                        			<?php if ($id != "") { ?>
                        				<div class="col-lg-2">
                        					<button type="button" class="btn btn-info" data-toggle="tooltip"
                        							data-original-title="Edit" onclick="editDescription('Edit')">
                        						<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        					</button>
                        				</div>
                        			<?php } ?>
                        		</div>
                        			<div class="modal fade" id="Description_Modal" tabindex="-1" role="dialog"
                        				 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        				<div class="modal-dialog modal-lg">
                        					<div class="modal-content nimot-class">
                        						<div class="modal-header">
                        							<h4>
                        								<span id="desc_modal_action"></span>
                        								Description
                        								<button type="button" class="close" data-dismiss="modal"
                        										onclick="resetToOriginalValue(this, 'vDescription_')">x
                        								</button>
                        							</h4>
                        						</div>
                        						<div class="modal-body">
                        							<?php
                        							for ($i = 0; $i < $count_all; $i++) {
                        								$vCode = $db_master[$i]['vCode'];
                        								$vTitle = $db_master[$i]['vTitle'];
                        								$eDefault = $db_master[$i]['eDefault'];
                        								$vValue = 'vDescription_' . $vCode;
                        								$$vValue = $userEditDataArr[$vValue];
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
                        										<label>Title (<?= $vTitle; ?>
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
                        															onClick="getAllLanguageCode('vDescription_', 'EN');">
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
                        															onClick="getAllLanguageCode('vDescription_', '<?= $default_lang ?>');">
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
                        										onclick="saveDescription()"><?= $langage_lbl['LBL_Save']; ?></button>
                        								<button type="button" class="btn btn-danger btn-ok"
                        										data-dismiss="modal"
                        										onclick="resetToOriginalValue(this, 'vDescription_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                        							</div>
                        						</div>
                        						<div style="clear:both;"></div>
                        					</div>
                        				</div>
                        			</div>
                        	<?php } else { ?>
                        		<div class="row">
                        			<div class="col-lg-12">
                        				<label>Title</label>
                        			</div>
                        			<div class="col-md-6 col-sm-6">
                        				<input type="text" class="form-control" id="vTitle_<?= $default_lang ?>"
                        					   name="vTitle_<?= $default_lang ?>"
                        					   value="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>">
                        			</div>
                        		</div> 
                    			<div class="row">
                    				<div class="col-lg-12">
                    					<label>Description</label>
                    				</div>
                    				<div class="col-md-6 col-sm-6">
                    					<input type="text" class="form-control"
                    						   id="vDescription_<?= $default_lang ?>"
                    						   name="vDescription_<?= $default_lang ?>"
                    						   value="<?= $userEditDataArr['vDescription_' . $default_lang]; ?>">
                    				</div>
                    			</div>
                        	<?php } ?> 
                        <?php } ?>
                        <?php if (in_array($eType, ['RentItem', 'RentEstate', 'RentCars'])) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" id="TextColor" class="form-control" value="<?= $vTextColor ?>"/>
                                    <input type="hidden" name="vTextColor" id="vTextColor" value="<?= $vTextColor ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Background Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" id="BgColor" class="form-control" value="<?= $vBgColor ?>"/>
                                    <input type="hidden" name="vBgColor" id="vBgColor" value="<?= $vBgColor ?>">
                                </div>
                            </div>
                        <?php } ?>


                        <?php if ($userObj->hasPermission($updateStatus) && !in_array($eType, ['Genie'])) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Status</label>
                                </div>
                                <div class="col-lg-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox"
                                               name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>
                                               value="Active"/>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (in_array($eType, ['TaxiBid'])) { ?>
                            <hr />
                            <div class="row">
                                <div class="col-lg-12">
                                    <label style="font-size: 16px">Edit Info Screen</label>
                                    <div class="underline-section-title"></div>
                                </div>
                            </div>

                            <div class="row pb-10">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-6 col-sm-6 marginbottom-10">
                                    <?php if(!empty($vImageOldTaxiBidInfo)) { ?>
                                    <div class="marginbottom-10">
                                        <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=150&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldTaxiBidInfo; ?>" id="taxibidinfo_img">
                                    </div>
                                    <?php } ?>
                                    <input type="file" class="form-control" name="vImageTaxiBidInfo" id="vImageTaxiBidInfo" onchange="previewImage(this, event);" data-img="taxibidinfoinfo_img">
                                    <input type="hidden" class="form-control" name="vImageOldTaxiBidInfo" id="vImageOldTaxiBidInfo" value="<?= $vImageOldTaxiBidInfo ?>">
                                    <strong>Note: Upload only png image size of 1507px X 1242px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                </div>
                            </div>

                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Info Title</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vTaxiBidInfoTitle_Default" name="vTaxiBidInfoTitle_Default" value="<?= $TaxiBidPageTitleArr[$default_lang]; ?>" data-originalvalue="<?= $TaxiBidPageTitleArr[$default_lang]; ?>" readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editTaxiBidInfoTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="TaxiBidInfoTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="taxibid_infotitle_modal_action"></span>
                                                    Info Title
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTaxiBidInfoTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vTaxiBidInfoTitle_' . $vCode;
                                                    $$vValue = $TaxiBidPageTitleArr[$vCode];
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
                                                            <label>Title (<?= $vTitle; ?>
                                                                ) <?php echo $required_msg; ?></label>
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
                                                                                onClick="getAllLanguageCode('vTaxiBidInfoTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vTaxiBidInfoTitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveTaxiBidInfoTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTaxiBidInfoTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Info Description</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <textarea class="form-control ckeditor" rows="10" id="vTaxiBidInfoSubTitle_Default" name="vTaxiBidInfoSubTitle_Default" data-originalvalue="<?= $TaxiBidPageSubTitleArr[$default_lang] ?>" readonly="readonly"><?= $TaxiBidPageSubTitleArr[$default_lang] ?></textarea>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editTaxiBidInfoSubTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="TaxiBidInfoSubTitle_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="taxibid_infosubtitle_modal_action"></span>
                                                    Description
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTaxiBidInfoSubTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vTaxiBidInfoSubTitle_' . $vCode;
                                                    $$vValue = $TaxiBidPageSubTitleArr[$vCode];
                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                    <?php
                                                    $page_title_class = 'col-lg-12';
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                        </div>
                                                        <div class="<?= $page_title_class ?>">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $vValue; ?>" id="<?= $vValue; ?>" data-originalvalue="<?= $$vValue; ?>"><?= $$vValue; ?></textarea>
                                                            <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                        </div>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveTaxiBidInfoSubTitle('vTaxiBidInfoSubTitle_', 'TaxiBidInfoSubTitle_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTaxiBidInfoSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Info Title</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vTaxiBidInfoTitle_<?= $default_lang ?>" name="vTaxiBidInfoTitle_<?= $default_lang ?>"
                                               value="<?= $TaxiBidPageTitleArr[$default_lang] ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Info Subtitle</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <textarea class="form-control ckeditor" rows="10" id="vTaxiBidInfoSubTitle_<?= $default_lang ?>" name="vTaxiBidInfoSubTitle_<?= $default_lang ?>"> <?= $TaxiBidPageSubTitleArr[$default_lang] ?></textarea>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <?php if ($userObj->hasPermission($update)) { ?>
                                    <input type="submit" class="save btn-info" name="submit" id="submit"
                                           value="Update <?php echo $titleTxt; ?>" style="margin-right: 10px">
                                <?php } ?>

                                <?php if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == "No") { ?>
                                    <a href="master_service_category.php" class="btn btn-default back_link">Cancel</a>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
<? include_once('footer.php'); ?>

<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
<script src="../assets/plugins/ckeditor/config.js"></script>
<script type="text/javascript">

    function editCategoryName(action) {

        $('#modal_action').html(action);

        $('#Category_Modal').modal('show');

    }

    function editCategoryNameNew(action) {

        $('#catmodal_action').html(action);

        $('#vCategory_ModalName').modal('show');

    }

    function editTitleName(action) {
         $('#modal_action').html(action);
         $('#Title_Modal').modal('show');
    }


    function saveTitle() {
        if ($('#vTitle_<?= $default_lang ?>').val() == "") {
            $('#vTitle_<?= $default_lang ?>_error').show();
            $('#vTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vTitle_Default').val($('#vTitle_<?= $default_lang ?>').val());
        $('#vTitle_Default').closest('.row').removeClass('has-error');
        $('#vTitle_Default-error').remove();
        $('#Title_Modal').modal('hide');
    }
    function editDescription(action) {
        $('#desc_modal_action').html(action);
        $('#Description_Modal').modal('show');
    }
    function saveDescription() {
        if ($('#vDescription_<?= $default_lang ?>').val() == "") {
            $('#vDescription_<?= $default_lang ?>_error').show();
            $('#vDescription_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDescription_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vDescription_Default').val($('#vDescription_<?= $default_lang ?>').val());
        $('#vDescription_Default').closest('.row').removeClass('has-error');
        $('#vDescription_Default-error').remove();
        $('#Description_Modal').modal('hide');
    }
    function saveCategoryName() {

        if ($('#vCategoryName_<?= $default_lang ?>').val() == "") {

            $('#vCategoryName_<?= $default_lang ?>_error').show();

            $('#vCategoryName_<?= $default_lang ?>').focus();

            clearInterval(langVar);

            langVar = setTimeout(function () {

                $('#vCategoryName_<?= $default_lang ?>_error').hide();

            }, 5000);

            return false;

        }


        $('#vCategoryName_Default').val($('#vCategoryName_<?= $default_lang ?>').val());

        $('#vCategoryName_Default').closest('.row').removeClass('has-error');

        $('#vCategoryName_Default-error').remove();

        $('#Category_Modal').modal('hide');

    }

        function saveCategoryNameNew() {

        if ($('#vCategory_<?= $default_lang ?>').val() == "") {

            $('#vCategory_<?= $default_lang ?>_error').show();

            $('#vCategory_<?= $default_lang ?>').focus();

            clearInterval(langVar);

            langVar = setTimeout(function () {

                $('#vCategory_<?= $default_lang ?>_error').hide();

            }, 5000);

            return false;

        }


        $('#vCategory_Default').val($('#vCategory_<?= $default_lang ?>').val());

        $('#vCategory_Default').closest('.row').removeClass('has-error');

        $('#vCategory_Default-error').remove();

        $('#vCategory_ModalName').modal('hide');

    }


    function editCategoryDesc(action) {

        $('#desc_modal_action').html(action);

        $('#CategoryDesc_Modal').modal('show');

    }


    function saveCategoryDesc() {

        if ($('#vCategoryDesc_<?= $default_lang ?>').val() == "") {

            $('#vCategoryDesc_<?= $default_lang ?>_error').show();

            $('#vCategoryDesc_<?= $default_lang ?>').focus();

            clearInterval(langVar);

            langVar = setTimeout(function () {

                $('#vCategoryDesc_<?= $default_lang ?>_error').hide();

            }, 5000);

            return false;

        }


        $('#vCategoryDesc_Default').val($('#vCategoryDesc_<?= $default_lang ?>').val());

        $('#vCategoryDesc_Default').closest('.row').removeClass('has-error');

        $('#vCategoryDesc_Default-error').remove();

        $('#CategoryDesc_Modal').modal('hide');

    }


    $("#TextColor").on("input", function () {

        var color = $(this).val();

        $('#vTextColor').val(color);

    });


    $("#BgColor").on("input", function () {

        var color = $(this).val();

        $('#vBgColor').val(color);

    });


    $('#banner_lang').change(function () {

        var curr_lang = $(this).val();

        window.location.href = '<?= $tconfig['tsite_url_main_admin'] ?>master_service_category_action.php?id=<?= $id ?>&banner_lang=' + curr_lang;

    });


    function editTaxiBidInfoTitle(action) {
        $('#taxibid_infotitle_modal_action').html(action);
        $('#TaxiBidInfoTitle_Modal').modal('show');
    }

    function saveTaxiBidInfoTitle() {
        if ($('#vTaxiBidInfoTitle_<?= $default_lang ?>').val() == "") {
            $('#vTaxiBidInfoTitle_<?= $default_lang ?>_error').show();
            $('#vTaxiBidInfoTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vTaxiBidInfoTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vTaxiBidInfoTitle_Default').val($('#vTaxiBidInfoTitle_<?= $default_lang ?>').val());
        $('#vTaxiBidInfoTitle_Default').closest('.row').removeClass('has-error');
        $('#vTaxiBidInfoTitle_Default-error').remove();
        $('#TaxiBidInfoTitle_Modal').modal('hide');
    }

    function editTaxiBidInfoSubTitle(action) {
        $('#taxibid_infosubtitle_modal_action').html(action);
        $('#TaxiBidInfoSubTitle_Modal').modal('show');
    }

    function saveTaxiBidInfoSubTitle(input_id, modal_id) {
        var DescLength = CKEDITOR.instances[input_id+'<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
        if(!DescLength) {
            $('#'+input_id+'<?= $default_lang ?>_error').show();
            $('#'+input_id+'<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function() {
                $('#'+input_id+'<?= $default_lang ?>_error').hide();
            }, 5000);
            e.preventDefault();
            return false;
        }

        var DescHTML = CKEDITOR.instances[input_id + '<?= $default_lang ?>'].getData();
        CKEDITOR.instances[input_id+'Default'].setData(DescHTML);
        $('#'+modal_id).modal('hide');
    }


</script>
</body>
<!-- END BODY-->
</html>