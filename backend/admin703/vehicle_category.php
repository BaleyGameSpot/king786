<?php
include_once('../common.php');
$MASTER_CATEGORY = $LOCATION_FILE_ARRAY['MASTER_CATEGORY'];
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";

$commonTxt = "";
if ($eType == 'Ride') {
    $commonTxt .= 'taxi-service';
}
if ($eType == 'DeliverAll') {
    $commonTxt .= 'deliverall';
}
if ($eType == 'VideoConsult') {
    $commonTxt .= 'video-consultation';
}
if ($eType == 'Bidding') {
    $commonTxt .= 'bidding';
}
if ($eType == 'UberX') {
    $commonTxt .= 'uberx';
}
if ($eType == 'RentEstate') {
    $commonTxt .= 'rentestate';
}
if ($eType == 'RentCars') {
    $commonTxt .= 'rentcars';
}
if ($eType == 'RentItem') {
    $commonTxt .= 'rentitem';
}
if ($eType == 'MedicalServices') {
    $commonTxt .= 'medical';
}
if ($eType == 'RideShare') {
    $commonTxt .= 'rideshare';
}
if ($eType == 'TaxiBid') {
    $commonTxt .= 'taxi-bid-service';
}
$view = "view-service-category-" . $commonTxt;
$update = "update-service-category-" . $commonTxt;
$updateStatus = "update-status-service-category-" . $commonTxt;
$create = "create-service-category-" . $commonTxt;
$delete = "delete-service-category-" . $commonTxt;
$manage_inner_page = "manage-inner-page-content-" . $commonTxt;
$manage_service_category = "manage-service-category-" . $commonTxt;
if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}
$script = 'VehicleCategory';
$eServiceType = !empty($eType) ? '&eServiceType=' . $eType : '';
if ($parent_ufx_catid != '0') {
    header("Location:vehicle_sub_category.php?sub_cid=" . $parent_ufx_catid . $eServiceType);
    exit;
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDisplayOrder ASC';
if ($sortby == 1) {
    if ($order == 0) {
        $ord = " ORDER BY vCategory_" . $default_lang . " ASC";
    } else {
        $ord = " ORDER BY vCategory_" . $default_lang . " DESC";
    }
}
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY eStatus ASC";
    } else {
        $ord = " ORDER BY eStatus DESC";
    }
}
if ($sortby == 3) {
    if ($order == 0) {
        $ord = " ORDER BY SubCategories ASC";
    } else {
        $ord = " ORDER BY SubCategories DESC";
    }
}
if ($sortby == 4) {
    if ($order == 0) {
        $ord = " ORDER BY iDisplayOrder ASC";
    } else {
        $ord = " ORDER BY iDisplayOrder DESC";
    }
}
//End Sorting
$rdr_ssql = "";
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
if ($THEME_OBJ->isProThemeActive() == 'Yes') {
    $script = 'VehicleCategory_' . $eType;
}
if ($MODULES_OBJ->isEnableAppHomeScreenLayout() && empty($eType) && $THEME_OBJ->isDeliveryKingXv2ThemeActive() == "No" && $THEME_OBJ->isMedicalServicev2ThemeActive() == "No") {
    header('Location: ' . $tconfig['tsite_admin_url'] . 'master_service_category.php');
}
$ssql = $ssqlSearch = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssqlSearch .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssqlSearch .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssqlSearch .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssqlSearch .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssqlSearch .= " AND eStatus = '" . clean($eStatus) . "'";
}
$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable('Yes') ? "Yes" : "No"; //add function to modules availibility
if ($THEME_OBJ->isCubexThemeActive() == 'Yes' || $THEME_OBJ->isCubeXv2ThemeActive() == 'Yes' || $THEME_OBJ->isPXCProThemeActive() == 'Yes') {
    $ssql1 = $ssql2 = $ssql3 = '';
    if ($ufxEnable != 'Yes') {
        $ssql1 .= " AND eCatType!='ServiceProvider'";
    } else {
        $ssql2 .= " OR eCatType='ServiceProvider'";
    }
    $ssql .= " AND (iServiceId IN ($enablesevicescategory) OR eCatType IN ('Ride', 'MotoRide', 'Fly', 'Donation') OR (eFor = 'DeliveryCategory' AND eCatType = 'MoreDelivery')  $ssql2 )  $ssql1";
}
if ($ufxEnable != 'Yes') {
    $ssql .= " AND eCatType!='ServiceProvider'";
}
if (!$MODULES_OBJ->isAirFlightModuleAvailable(1, 'Yes')) {
    $ssql .= " AND eCatType != 'Fly'";
}
if (!$MODULES_OBJ->isDonationFeatureAvailable()) {
    $ssql .= " AND eCatType != 'Donation'";
}
if (!$MODULES_OBJ->isRideFeatureAvailable('Yes')) {
    $ssql .= " AND eCatType != 'Ride' AND eCatType != 'MotoRide' AND eCatType != 'Rental' AND eCatType != 'MotoRental'";
}
if (!$MODULES_OBJ->isInterCityFeatureAvailable('Yes')) {
    $ssql .= " AND eCatType != 'InterCity'";
}
if (!$MODULES_OBJ->isDeliveryFeatureAvailable('Yes')) {
    $ssql .= " AND eCatType != 'Delivery' AND eCatType != 'MultipleDelivery' AND eCatType != 'MotoDelivery' AND eCatType != 'MoreDelivery'";
}
if (!$MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) {
    $ssql .= " AND eCatType != 'DeliverAll'";
}
if (!$MODULES_OBJ->isEnableAnywhereDeliveryFeature('Yes')) {
    $ssql .= " AND eCatType!='Genie' AND eCatType!='Runner' AND eCatType!='Anywhere'";
}
$MasterServiceCategory = $medical_service_details = "";
if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
    $medical_service_details = $obj->MySQLSelect("SELECT tCategoryDetails FROM $master_service_category_tbl WHERE eType = 'MedicalServices' ");
    $tCategoryDetails = $medical_service_details[0]['tCategoryDetails'];
    if (isset($_POST['eMedicalServiceCatEdit'])) {
        $Data_Update = array();
        $eMedicalServiceCatEdit = $_POST['eMedicalServiceCatEdit'];
        $vImage = "";
        $image_object = $_FILES['vImage']['tmp_name'];
        $image_name = $_FILES['vImage']['name'];
        if ($image_name != "") {
            $filecheck = basename($_FILES['vImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
            }
            $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
            $image_width = $image_info[0];
            $image_height = $image_info[1];
            if ($flag_error == 1) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;
                header("Location:master_service_category.php");
                exit;
            } else {
                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                    chmod($Photo_Gallery_folder, 0777);
                }
                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
                $vImage = $img[0];
                if (!empty($_POST['vImage_old']) && file_exists($Photo_Gallery_folder . $_POST['vImage_old'])) {
                    unlink($Photo_Gallery_folder . $_POST['vImage_old']);
                }
            }
        }
        if (!empty($tCategoryDetails)) {
            $tCategoryDetails = json_decode($tCategoryDetails, true);
        } else {
            $tCategoryDetails = array();
        }
        if ($eMedicalServiceCatEdit == "BookService") {
            if (!empty($vImage)) {
                $tCategoryDetails['BookService']['vImage'] = $vImage;
            }
            $tCategoryDetails['BookService']['vTextColor'] = $_POST['vTextColor'];
            $tCategoryDetails['BookService']['vBgColor'] = $_POST['vBgColor'];
        } elseif ($eMedicalServiceCatEdit == "VideoConsult") {
            if (!empty($vImage)) {
                $tCategoryDetails['VideoConsult']['vImage'] = $vImage;
            }
            $tCategoryDetails['VideoConsult']['vTextColor'] = $_POST['vTextColor'];
            $tCategoryDetails['VideoConsult']['vBgColor'] = $_POST['vBgColor'];
        } else {
            if (!empty($vImage)) {
                $tCategoryDetails['MoreService']['vImage'] = $vImage;
            }
            $tCategoryDetails['MoreService']['vTextColor'] = $_POST['vTextColor'];
            $tCategoryDetails['MoreService']['vBgColor'] = $_POST['vBgColor'];
        }
        $Data_Update['tCategoryDetails'] = json_encode($tCategoryDetails, JSON_UNESCAPED_UNICODE);
        $where = " eType = 'MedicalServices' ";
        $obj->MySQLQueryPerform($master_service_category_tbl, $Data_Update, 'update', $where);
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        header("Location:" . $MASTER_CATEGORY . "?eType=MedicalServices");
        exit;
    }
}
$MasterServiceCategory = "";
if (!empty($eType)) {

    /*$master_service_categories = getMasterServiceCategories();
    $master_service_categories = array_filter($master_service_categories, function ($item) use ($eType) {
        return $item['eType'] == $eType;
    });
    $master_service_categories =  array_values($master_service_categories);*/

    $master_service_categories = array_values(array_filter(getMasterServiceCategories(), function ($item) use ($eType) {
        return $item['eType'] == $eType;
    }));


    $master_service_category = $obj->MySQLSelect("SELECT JSON_UNQUOTE(JSON_EXTRACT(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vCategoryName FROM $master_service_category_tbl WHERE eType = '$eType' ");
    $MasterServiceCategory = "(" . $master_service_categories[0]['vCategoryName'] . ")";

    $ssql = getMasterServiceCategoryQuery($eType, 'Yes');

    if ($eType == "Ride") {
        $ssql .= " AND eForMedicalService = 'No' ";
    } elseif ($eType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()) {
        $ssql = getMasterServiceCategoryQuery($eType);
        $vc_data = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT(iParentId)) as ParentIds FROM vehicle_category WHERE eVideoConsultEnable = 'Yes'");
        $ssql .= " AND iVehicleCategoryId IN (" . $vc_data[0]['ParentIds'] . ")";
    } elseif ($eType == "MedicalServices") {
        $ssql .= " AND eForMedicalService = 'Yes' ";
    }
}
$ssql .= $ssqlSearch;
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
//Added By HJ On 14-11-2019 For Manage Service Category Row As Per Design Start
$calSize = 3;
for ($f = $per_page; $f < ($per_page + $calSize); $f++) {
    $checkZero = $f / $calSize;
    $checkZero = is_numeric($checkZero) && floor($checkZero) != $checkZero;
    if (empty($checkZero)) {
        $per_page = $f;
        break;
    }
}
//Added By HJ On 14-11-2019 For Manage Service Category Row As Per Design End
if (strtoupper(ONLY_MEDICAL_SERVICE) == "YES") {
    $eType = "MedicalServices";
}
if ($eStatus != '') {
    $estatusquery = "";
} else {
    $estatusquery = " AND eStatus != 'Deleted'";
}
$not_sql = " AND iVehicleCategoryId NOT IN (297,339) ";
$parent_id_sql = " AND iParentId='0' ";
if ($eType == "MedicalServices") {
    $parent_id_sql = " AND (iParentId='0' OR iParentId = '3') ";
}
if (
    in_array($eType, [
        'UberX',
        'VideoConsult'
    ]) && $MODULES_OBJ->isEnableMedicalServices('Yes')
) {
    $ssql .= " AND iVehicleCategoryId NOT IN (3,22,26,158) ";
}
if ($eType == "DeliverAll" && $MODULES_OBJ->isEnableMedicalServices('Yes')) {
    $ssql .= " AND iServiceId NOT IN (5, 11) ";
}
if ($eType == "DeliverAll") {
    $ssql .= " AND iServiceId IN ($enablesevicescategory) ";
}
$sql = "SELECT COUNT(iVehicleCategoryId) AS Total FROM " . $sql_vehicle_category_table_name . "  WHERE  1 = 1 $parent_id_sql $estatusquery $ssql $rdr_ssql $not_sql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) {
    $page = 1;
}
//Pagination End
$parent_id_sql = " AND vc.iParentId='0' ";
if ($eType == "MedicalServices") {
    $parent_id_sql = " AND (vc.iParentId='0' OR vc.iParentId = '3') ";
}
if (
    in_array($eType, [
        'UberX',
        'VideoConsult'
    ]) && $MODULES_OBJ->isEnableMedicalServices('Yes')
) {
    $ssql .= " AND vc.iVehicleCategoryId NOT IN (3,22,26,158) ";
}
if ($eType == "DeliverAll" && $MODULES_OBJ->isEnableMedicalServices('Yes')) {
    $ssql .= " AND vc.iServiceId NOT IN (5, 11) ";
}
if ($eType == "DeliverAll") {
    $ssql .= " AND vc.iServiceId IN ($enablesevicescategory) ";
}
if (!empty($eStatus)) {
    $sql = "SELECT vc.iParentId, vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo, vc.vLogo2, vc.vListLogo1,vc.vListLogo2,vc.vListLogo3,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType,vc.eForMedicalService, vc.eVideoConsultEnable, vc.tMedicalServiceInfo, vc.vIconDetails, (select count(iVehicleCategoryId) FROM vehicle_category AS sub  WHERE vc.iVehicleCategoryId = sub.iParentId  AND sub.eStatus != 'Deleted') as SubCategories FROM " . $sql_vehicle_category_table_name . " as vc WHERE 1 = 1 $parent_id_sql $ssql $rdr_ssql $not_sql $ord LIMIT $start, $per_page";
} else {
    $sql = "SELECT vc.iParentId,vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo, vc.vLogo2, vc.vListLogo1,vc.vListLogo2,vc.vListLogo3,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType,vc.eForMedicalService, vc.eVideoConsultEnable, vc.tMedicalServiceInfo, vc.vIconDetails, (select count(iVehicleCategoryId) FROM vehicle_category AS sub  WHERE vc.iVehicleCategoryId = sub.iParentId  AND sub.eStatus != 'Deleted') as SubCategories FROM " . $sql_vehicle_category_table_name . " as vc WHERE eStatus != 'Deleted' $parent_id_sql $ssql $rdr_ssql $not_sql $ord LIMIT $start, $per_page";
}

$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable('Yes'); // Added By HJ On 28-11-2019 For Check UberX Service Status
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Manage <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        .medical-service-title {
            padding: 10px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 5px;
            margin: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        hr.medical-service-line {
            border: 1px solid;
            width: calc(100% - 20px);
            margin: 0 0 20px 10px;
        }

        .medical-service-note {
            margin-top: 10px;
            text-align: justify;
        }

        .pb-10 {
            padding-bottom: 10px !important;
        }

        .ms-category-status {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div" class="vehicleCategorylist">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?> <?= $MasterServiceCategory ?></h2>
                    </div>
                    <?php
                    if ($userObj->hasPermission($create)) {
                        if (strtoupper(ONLY_MEDICAL_SERVICE) == "YES" || $eType == "MedicalServices") { ?>
                            <div class="col-lg-12">
                                <a class="add-btn"
                                   href="vehicle_category_action.php?sub_action=sub_category&sub_cid=3&eServiceType=UberX"
                                   style="text-align: center;">
                                    Add <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?></a>
                            </div>
                        <?php }
                    } ?>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <?php if ($eType != "MedicalServices") { ?>
                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                        <tr>
                            <td width="5%">
                                <label for="textfield">
                                    <strong>Search:</strong>
                                </label>
                            </td>
                            <td width="15%" class="searchform">
                                <input type="Text" id="keyword" name="keyword"
                                       value="<?= $keyword; ?>" class="form-control"/>
                            </td>
                            <td width="12%" class="estatus_options" id="eStatus_options">
                                <select name="eStatus" id="estatus_value" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value='Active' <?php
                                    if ($eStatus == 'Active') {
                                        echo "selected";
                                    }
                                    ?> >Active
                                    </option>
                                    <option value="Inactive" <?php
                                    if ($eStatus == 'Inactive') {
                                        echo "selected";
                                    }
                                    ?> >Inactive
                                    </option>
                                    <?php if ($userObj->hasPermission($delete)) { ?>
                                        <option value="Deleted" <?php
                                        if ($eStatus == 'Deleted') {
                                            echo "selected";
                                        }
                                        ?>>Deleted
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" value="<?= $_REQUEST['eType'] ?>" id="eType" name="eType"
                                       title="eType"/>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                       title="Search"/>
                                <input type="button" value="Reset" class="btnalt button11"
                                       onClick="window.location.href = '<?= $MASTER_CATEGORY; ?><?= (!empty($eType) ? '?eType=' . $eType : '') ?>'"/>
                            </td>
                            <?php if (!empty($eType)) {
                                if ($eType == "UberX") { ?>
                                    <?php if ($userObj->hasPermission($create)) { ?>
                                        <td width="30%">
                                            <a class="add-btn"
                                               href="vehicle_category_action.php<?= (!empty($eType) ? '?eServiceType=' . $eType : '') ?>"
                                               style="text-align: center;">
                                                Add <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?></a>
                                        </td>
                                    <?php } ?>
                                <?php }
                            } else {
                                if ($parent_ufx_catid == "0" && $ufxEnable == "Yes") { ?>
                                    <?php if ($userObj->hasPermission($create)) { ?>
                                        <td width="30%">
                                            <a class="add-btn"
                                               href="vehicle_category_action.php<?= (!empty($eType) ? '?eServiceType=' . $eType : '') ?>"
                                               style="text-align: center;">
                                                Add <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?></a>
                                        </td>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        </tr>
                        </tbody>
                    </table>
                </form>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="admin-nir-export vehicle-cat">
                                <div style="clear:both;"></div>
                                <div class="table-responsive1">
                                    <form class="_list_form" id="_list_form" method="post"
                                          action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <div class="table table-striped  table-hover">
                                            <div class="profile-earning">
                                                <div class="partation">
                                                    <ul style="padding-left: 0px;" class="setings-list">
                                                        <?php
                                                        if (!empty($data_drv)) {
                                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                                            $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/ios/3x_" . $data_drv[$i]['vLogo'];
                                                            $bannerPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/" . $data_drv[$i]['vBannerImage'];
                                                            if ($data_drv[$i]['vLogo'] == "") {
                                                                $logoPath = $bannerPath;
                                                            }
                                                            if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV2()) {
                                                                $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/" . $data_drv[$i]['vListLogo2'];
                                                            }
                                                            if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3() || $MODULES_OBJ->isEnableAppHomeScreenLayoutV4()) {
                                                                $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/" . $data_drv[$i]['vListLogo3'];
                                                            }
                                                            if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV4() && $eType == "VideoConsult") {
                                                                $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/" . $data_drv[$i]['vIconDetails'];
                                                            }
                                                            if (empty($data_drv[$i]['vListLogo3'])) {
                                                                $logoPath = "";
                                                            }
                                                            $eSubService = "";
                                                            if ($eType == "MedicalServices" && $data_drv[$i]['eCatType'] == "ServiceProvider" && $data_drv[$i]['iParentId'] > 0) {
                                                                $logoPath = ($data_drv[$i]['vLogo'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $data_drv[$i]['iVehicleCategoryId'] . "/ios/3x_" . $data_drv[$i]['vLogo'] : "";
                                                                if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
                                                                    $logoPath = ($data_drv[$i]['vLogo2'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $data_drv[$i]['iVehicleCategoryId'] . "/ios/3x_" . $data_drv[$i]['vLogo2'] : "";
                                                                }
                                                                $eSubService = "&sub_action=sub_category&sub_cid=" . $data_drv[$i]['iParentId'];
                                                            }
                                                            //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 Start
                                                            $buttonText = "View";
                                                            if ($data_drv[$i]['eCatType'] == "ServiceProvider") {
                                                                $buttonText = "Add/ View";
                                                            }
                                                            //echo "<pre>";print_r($data_drv[$i]['eStatus']);die;
                                                            //$buttonStatus = "Active";
                                                            $buttonStatus = $data_drv[$i]['eStatus'];
                                                            $btnChecked = 0;
                                                            if ($data_drv[$i]['eStatus'] == "Active") {
                                                                $btnChecked = 1;
                                                                //$buttonStatus = "Inactive";
                                                            }
                                                            $SubCategoriescount  = $data_drv[$i]['SubCategories']; 
                                                            //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 End
                                                            ?>
                                                            <li>
                                                                <div class="toggle-list-inner">
                                                                    <div class="toggle-combo">
                                                                        <label>
                                                                            <div align="center">
                                                                                <img src="<?= $logoPath; ?>"
                                                                                     style="width:100px;">
                                                                            </div>
                                                                            <div style="margin: 0 0 0 10px;">
                                                                                <td><?= $data_drv[$i]['vCategory']; ?></td>
                                                                            </div>
                                                                        </label>
                                                                        <?php if ($userObj->hasPermission($updateStatus) && $eType != "MedicalServices") { ?>
                                                                            <span class="toggle-switch">
                                                                                <input type="checkbox"
                                                                                       <?php if ($btnChecked > 0) { ?> checked="" <?php } ?> onClick="changeStatus('<?= $data_drv[$i]['iVehicleCategoryId']; ?>', '<?= $buttonStatus; ?>');displayNote('<?= $data_drv[$i]['iVehicleCategoryId']; ?>', '<?= $buttonStatus; ?>','<?php echo $SubCategoriescount;?>');"
                                                                                       id="statusbutton" class="chk"
                                                                                       name="statusbutton" value="246">
                                                                                <span class="toggle-base"></span>
                                                                            </span>
                                                                        <?php } ?>
                                                                    </div>
                                                                    <div class="check-combo">
                                                                        <label id="defaultText_246">
                                                                            <ul>
                                                                                <?php if ($userObj->hasPermission($update)) { ?>
                                                                                    <li class="entypo-twitter"
                                                                                        data-network="twitter">
                                                                                        <a href="vehicle_category_action.php?id=<?= $data_drv[$i]['iVehicleCategoryId'] . $eServiceType . $eSubService; ?>"
                                                                                           data-toggle="tooltip"
                                                                                           title="Edit">
                                                                                            <img src="img/edit-new.png"
                                                                                                 alt="Edit">
                                                                                        </a>
                                                                                    </li>
                                                                                <?php } ?>
                                                                                <?php if ($eType != "MedicalServices") { ?>
                                                                                    <?php if ($eStatus != 'Deleted' && $userObj->hasPermission($delete) && $data_drv[$i]['eCatType'] == "ServiceProvider") { ?>
                                                                                        <li class="entypo-facebook"
                                                                                            data-network="facebook">
                                                                                            <a href="javascript:void(0);"
                                                                                               onClick="changeStatusDelete('<?= $data_drv[$i]['iVehicleCategoryId']; ?>')"
                                                                                               data-toggle="tooltip"
                                                                                               title="Delete">
                                                                                                <img src="img/delete-new.png"
                                                                                                     alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                            </a>
                                                                                        </li>
                                                                                    <?php }
                                                                                    if (($data_drv[$i]['eCatType'] != "ServiceProvider" && $data_drv[$i]['eCatType'] != "MoreDelivery") && $data_drv[$i]['iParentId'] == 0) { ?>
                                                                                    <?php } else { ?>
                                                                                        <?php if ($userObj->hasPermission($view)) { ?>
                                                                                            <li class="entypo-gplus"
                                                                                                data-network="gplus">
                                                                                                <a href="vehicle_sub_category.php?sub_cid=<?= $data_drv[$i]['iVehicleCategoryId'] . $eServiceType ?>"
                                                                                                   target="_blank"
                                                                                                   data-toggle="tooltip"
                                                                                                   title="View Subcategories">
                                                                                                    <img src="img/view-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                                </a>
                                                                                            </li>
                                                                                            <?php
                                                                                        }
                                                                                    }
                                                                                } 

                                                                                if ($eType == "UberX" || $eType == "VideoConsult") {
                                                                                ?>
                                                                                <li class="entypo-gplus"
                                                                                    data-network="gplus">
                                                                                    <a href="banner.php?eForService=ServiceProvider&eFor=<?= $eType ?>&iVehicleCategoryId=<?= $data_drv[$i]['iVehicleCategoryId'] ?>" target="_blank" data-toggle="tooltip" title="Manage Banners">
                                                                                        <img src="img/banner-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                    </a>
                                                                                </li>
                                                                                <?php } ?>
                                                                            </ul>
                                                                        </label>
                                                                        <?php if ($eType == "UberX") { ?>
                                                                         <div class="ufx-service-note" id="ufx-service-note_<?= $data_drv[$i]['iVehicleCategoryId']; ?>"  <?php if ($btnChecked > 0 && $SubCategoriescount == 0) { ?> style="display: block;" <?php } else {?> style="display: none;" <?php }?> >
                                                                                NOTE: To display categories in the app, subcategories must be added.
                                                                        </div>
                                                                        <?php } ?>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?php }
                                                        ?></ul>
                                                </div>
                                            </div><?php
                                            } else {
                                                ?>
                                                <tr class="gradeA">
                                                    <td colspan="8"> No Records Found.</td>
                                                </tr>
                                            <?php } ?>
                                        </div>
                                    </form>
                                </div>
                                <?php include('pagination_n.php'); ?>
                            </div>
                        </div> <!--TABLE-END-->
                    </div>
                </div>
            <?php } else {
                $OnDemandServicesArr = $VideoConsultServicesArr = $MoreServicesArr = array();
                /*--------------------- inner page ------------------*/
                $innerPageIcon = "img/edit-doc.png";
                $lang_data = $LANG_OBJ->getLanguageData($default_lang);
                //$lang_data_id = "&id=" . $lang_data['iLanguageMasId'] . "&vehicle_category_page=1&back_link=vehicle_category.php?eType=MedicalServices";
                $lang_data_id = "&id=" . $lang_data['iLanguageMasId'] . "&vehicle_category_page=1&back_link=" . $MASTER_CATEGORY . "&eType=MedicalServices";
                /*--------------------- inner page ------------------*/
                if (!empty($data_drv)) {
                    foreach ($data_drv as $med_service) {
                        if (!empty($med_service['tMedicalServiceInfo'])) {
                            $tMedicalServiceInfoArr = json_decode($med_service['tMedicalServiceInfo'], true);
                            if ($tMedicalServiceInfoArr['BookService'] == "Yes") {
                                $med_service['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderBS'];
                                $OnDemandServicesArr[] = $med_service;
                            }
                            if ($med_service['eVideoConsultEnable'] == "Yes" && $tMedicalServiceInfoArr['VideoConsult'] == "Yes") {
                                $med_service['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderVC'];
                                $VideoConsultServicesArr[] = $med_service;
                            }
                            if ($tMedicalServiceInfoArr['MoreService'] == "Yes") {
                                $med_service['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderMS'];
                                $MoreServicesArr[] = $med_service;
                            }
                        }
                    }
                    $ms_display_order = array_column($OnDemandServicesArr, 'ms_display_order');
                    array_multisort($ms_display_order, SORT_ASC, $OnDemandServicesArr);
                    $ms_display_order = array_column($VideoConsultServicesArr, 'ms_display_order');
                    array_multisort($ms_display_order, SORT_ASC, $VideoConsultServicesArr);
                    $ms_display_order = array_column($MoreServicesArr, 'ms_display_order');
                    array_multisort($ms_display_order, SORT_ASC, $MoreServicesArr);
                }
                $MEDICAL_SERVICES_ARR = array(
                    array(
                        'ServiceTitle'      => $langage_lbl_admin['LBL_ON_DEMAND_MEDICAL_SERVICES_TITLE'],
                        'ServiceDesc'       => $langage_lbl_admin['LBL_ON_DEMAND_MEDICAL_SERVICES_DESC'],
                        'ManageBtn'         => 'Yes',
                        'EditDetailsBtn'    => 'No',
                        'ManageServiceKey'  => 'BookService',
                        'ModalKey'          => 'BookServiceModal',
                        'ServiceTitleLabel' => 'LBL_ON_DEMAND_MEDICAL_SERVICES_TITLE',
                        'ServiceDescLabel'  => 'LBL_ON_DEMAND_MEDICAL_SERVICES_DESC',
                        'ServicesArr'       => $OnDemandServicesArr,
                    ),
                    array(
                        'ServiceTitle'      => $langage_lbl_admin['LBL_VIDEO_CONSULT_MEDICAL_SERVICES_TITLE'],
                        'ServiceDesc'       => $langage_lbl_admin['LBL_VIDEO_CONSULT_MEDICAL_SERVICES_DESC'],
                        'ManageBtn'         => 'Yes',
                        'EditDetailsBtn'    => 'No',
                        'ManageServiceKey'  => 'VideoConsult',
                        'ModalKey'          => 'VideoConsultModal',
                        'ServiceTitleLabel' => 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_TITLE',
                        'ServiceDescLabel'  => 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_DESC',
                        'ServicesArr'       => $VideoConsultServicesArr
                    ),
                    array(
                        'ServiceTitle'      => $langage_lbl_admin['LBL_MEDICAL_MORE_SERVICES_TITLE'],
                        'ServiceDesc'       => $langage_lbl_admin['LBL_MEDICAL_MORE_SERVICES_DESC'],
                        'ManageBtn'         => 'Yes',
                        'EditDetailsBtn'    => 'No',
                        'ManageServiceKey'  => 'MoreService',
                        'ModalKey'          => 'MoreServiceModal',
                        'ServiceTitleLabel' => 'LBL_MEDICAL_MORE_SERVICES_TITLE',
                        'ServiceDescLabel'  => 'LBL_MEDICAL_MORE_SERVICES_DESC',
                        'ServicesArr'       => $MoreServicesArr
                    )
                );
                $TextColor['BookService'] = $TextColor['VideoConsult'] = $TextColor['MoreService'] = "#000000";
                $BgColor['BookService'] = $BgColor['VideoConsult'] = $BgColor['MoreService'] = "#ffffff";
                $vImageOld['BookService'] = $vImageOld['VideoConsult'] = $vImageOld['MoreService'] = "";
                if (!empty($medical_service_details)) {
                    $tCategoryDetails = $medical_service_details[0]['tCategoryDetails'];
                    if (!empty($tCategoryDetails)) {
                        $tCategoryDetails = json_decode($tCategoryDetails, true);
                        $TextColor['BookService'] = $tCategoryDetails['BookService']['vTextColor'];
                        $BgColor['BookService'] = $tCategoryDetails['BookService']['vBgColor'];
                        $vImageOld['BookService'] = $tCategoryDetails['BookService']['vImage'];
                        $TextColor['VideoConsult'] = $tCategoryDetails['VideoConsult']['vTextColor'];
                        $BgColor['VideoConsult'] = $tCategoryDetails['VideoConsult']['vBgColor'];
                        $vImageOld['VideoConsult'] = $tCategoryDetails['VideoConsult']['vImage'];
                        $TextColor['MoreService'] = $tCategoryDetails['MoreService']['vTextColor'];
                        $BgColor['MoreService'] = $tCategoryDetails['MoreService']['vBgColor'];
                        $vImageOld['MoreService'] = $tCategoryDetails['MoreService']['vImage'];
                    }
                }
                ?>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="admin-nir-export vehicle-cat">
                                <div style="clear:both;"></div>
                                <div class="table-responsive1">
                                    <div class="table table-striped  table-hover">
                                        <div class="profile-earning">
                                            <?php foreach ($MEDICAL_SERVICES_ARR as $MEDICAL_SERVICE) { ?>
                                                <div class="partation">
                                                    <div class="medical-service-title">
                                                        <span><?= $MEDICAL_SERVICE['ServiceTitle'] ?></span>
                                                        <?php if ($MEDICAL_SERVICE['ManageBtn'] == "Yes" && $userObj->hasPermission($manage_service_category)) { ?>
                                                            <div>
                                                                <div class="toggle-combo ms-category-status">
                                                                    <label>Show in User App Home Screen</label>
                                                                    <span class="toggle-switch">
                                                                        <input type="checkbox" class="chk" name="ms_category_status" value="<?= $MEDICAL_SERVICE["ManageServiceKey"] ?>" checked="checked">
                                                                        <span class="toggle-base"></span>
                                                                    </span>
                                                                </div>
                                                                <button type="button" class="add-btn" onclick="getMedicalServices('<?= $MEDICAL_SERVICE["ManageServiceKey"] ?>', '<?= addslashes($MEDICAL_SERVICE["ServiceTitle"]) ?>')">Manage Services</button>
                                                                <?php if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3() && $MEDICAL_SERVICE['EditDetailsBtn'] == "Yes") { ?>
                                                                    <button type="button" class="add-btn" data-toggle="modal" data-target="#<?= $MEDICAL_SERVICE["ModalKey"] ?>" style="margin-right: 15px;">Edit Details</button>
                                                                    <div class="modal fade" id="<?= $MEDICAL_SERVICE["ModalKey"] ?>" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content nimot-class">
                                                                            <div class="modal-header">
                                                                                <h4>
                                                                                    Medical Services - <?= $MEDICAL_SERVICE["ServiceTitle"] ?>
                                                                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                </h4>
                                                                            </div>
                                                                            <form action="" method="POST"
                                                                                  enctype="multipart/form-data">
                                                                                <div class="modal-body">
                                                                                    <div class="form-group">
                                                                                        <input type="hidden"
                                                                                               name="eMedicalServiceCatEdit"
                                                                                               id="eMedicalServiceCatEdit"
                                                                                               value="<?= $MEDICAL_SERVICE["ManageServiceKey"] ?>">
                                                                                        <div class="row pb-10">
                                                                                            <div class="col-lg-12">
                                                                                                <label style="font-size: 13px">Title</label>
                                                                                            </div>
                                                                                            <div class="col-lg-10">
                                                                                                <input type="text"
                                                                                                       class="form-control"
                                                                                                       value="<?= $MEDICAL_SERVICE["ServiceTitle"] ?>"
                                                                                                       readonly
                                                                                                       disabled>
                                                                                            </div>
                                                                                            <div class="col-lg-2">
                                                                                                <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $MEDICAL_SERVICE["ServiceTitleLabel"] ?>"
                                                                                                   class="btn btn-info"
                                                                                                   target="_blank">
                                                                                                    <span class="glyphicon glyphicon-pencil"
                                                                                                          aria-hidden="true"></span>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="row pb-10">
                                                                                            <div class="col-lg-12">
                                                                                                <label style="font-size: 13px">Description</label>
                                                                                            </div>
                                                                                            <div class="col-lg-10">
                                                                                                <input type="text"
                                                                                                       class="form-control"
                                                                                                       value="<?= $MEDICAL_SERVICE["ServiceDesc"] ?>"
                                                                                                       readonly
                                                                                                       disabled>
                                                                                            </div>
                                                                                            <div class="col-lg-2">
                                                                                                <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $MEDICAL_SERVICE["ServiceDescLabel"] ?>"
                                                                                                   class="btn btn-info"
                                                                                                   target="_blank"><span
                                                                                                            class="glyphicon glyphicon-pencil"
                                                                                                            aria-hidden="true"></span></a>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="row pb-10">
                                                                                            <div class="col-lg-12">
                                                                                                <label style="font-size: 13px">Image</label>
                                                                                            </div>
                                                                                            <div class="col-lg-12">
                                                                                                <?php if (!empty($vImageOld[$MEDICAL_SERVICE["ManageServiceKey"]])) { ?>
                                                                                                    <div class="marginbottom-10">
                                                                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vImageOld[$MEDICAL_SERVICE["ManageServiceKey"]]; ?>">
                                                                                                </div>
                                                                                                <?php } ?>
                                                                                                <input type="file"
                                                                                                       class="form-control"
                                                                                                       name="vImage">
                                                                                                <input type="hidden"
                                                                                                       class="form-control"
                                                                                                       name="vImage_old"
                                                                                                       value="<?= $vImageOld[$MEDICAL_SERVICE["ManageServiceKey"]] ?>">
                                                                                            </div>
                                                                                            <div class="col-lg-12">
                                                                                                <strong style="font-size: 13px">Note: Upload only png image size of 360px X 360px.</strong>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="row pb-10">
                                                                                            <div class="col-lg-12">
                                                                                                <label style="font-size: 13px">Text Color</label>
                                                                                            </div>
                                                                                            <div class="col-md-3 col-sm-3">
                                                                                                <input type="color"
                                                                                                       class="form-control TextColor"
                                                                                                       value="<?= $TextColor[$MEDICAL_SERVICE["ManageServiceKey"]] ?>"/>
                                                                                                <input type="hidden"
                                                                                                       name="vTextColor"
                                                                                                       data-id="vTextColor"
                                                                                                       value="<?= $TextColor[$MEDICAL_SERVICE["ManageServiceKey"]] ?>">
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="row pb-10">
                                                                                            <div class="col-lg-12">
                                                                                                <label style="font-size: 13px">Background Color</label>
                                                                                            </div>
                                                                                            <div class="col-md-3 col-sm-3">
                                                                                                <input type="color"
                                                                                                       class="form-control BgColor"
                                                                                                       value="<?= $BgColor[$MEDICAL_SERVICE["ManageServiceKey"]] ?>"/>
                                                                                                <input type="hidden"
                                                                                                       name="vBgColor"
                                                                                                       data-id="vBgColor"
                                                                                                       value="<?= $BgColor[$MEDICAL_SERVICE["ManageServiceKey"]] ?>">
                                                                                            </div>
                                                                                        </div>
                                                                                    
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer"
                                                                                     style="text-align: left">
                                                                                    <button type="submit"
                                                                                            name="submitbtn"
                                                                                            class="btn btn-default">Save
                                                                                    </button>
                                                                                    <button type="button"
                                                                                            class="btn btn-default"
                                                                                            data-dismiss="modal">Cancel</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <hr class="medical-service-line"/>
                                                    <ul style="padding-left: 0px;" class="setings-list">
                                                        <?php
                                                        if (!empty($MEDICAL_SERVICE['ServicesArr'])) {
                                                            foreach ($MEDICAL_SERVICE['ServicesArr'] as $MedService) {
                                                                $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $MedService['iVehicleCategoryId'] . "/ios/3x_" . $MedService['vLogo'];
                                                                $bannerPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $MedService['iVehicleCategoryId'] . "/" . $MedService['vBannerImage'];
                                                                if ($MedService['vLogo'] == "") {
                                                                    $logoPath = $bannerPath;
                                                                }
                                                                if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV2()) {
                                                                    $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $MedService['iVehicleCategoryId'] . "/" . $MedService['vListLogo2'];
                                                                }
                                                                if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
                                                                    $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $MedService['iVehicleCategoryId'] . "/" . $MedService['vListLogo3'];
                                                                }
                                                                if (empty($MedService['vListLogo3'])) {
                                                                    $logoPath = "";
                                                                }
                                                                $eSubService = "";
                                                                if ($MedService['eCatType'] == "ServiceProvider" && $MedService['iParentId'] > 0) {
                                                                    $logoPath = ($MedService['vLogo'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $MedService['iVehicleCategoryId'] . "/ios/3x_" . $MedService['vLogo'] : "";
                                                                    $eSubService = "&sub_action=sub_category&sub_cid=" . $MedService['iParentId'];
                                                                    if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
                                                                        $logoPath = ($MedService['vLogo2'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $MedService['iVehicleCategoryId'] . "/ios/3x_" . $MedService['vLogo2'] : "";
                                                                    }
                                                                }
                                                                $buttonStatus = $MedService['eStatus'];
                                                                $btnChecked = 0;
                                                                if ($MedService['eStatus'] == "Active") {
                                                                    $btnChecked = 1;
                                                                }
                                                                ?>
                                                                <li>
                                                                    <div class="toggle-list-inner">
                                                                        <div class="toggle-combo">
                                                                            <label>
                                                                                <div align="center">
                                                                                    <img src="<?= $logoPath; ?>"
                                                                                         style="width:100px;">
                                                                                </div>
                                                                                <div style="margin: 0 0 0 10px;">
                                                                                    <td><?= $MedService['vCategory']; ?></td>
                                                                                </div>
                                                                            </label>
                                                                            <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                                <span class="toggle-switch">
                                                                                <input type="checkbox"
                                                                                       <?php if ($btnChecked > 0) { ?>checked=""<?php } ?> onClick="changeStatus('<?= $MedService['iVehicleCategoryId']; ?>', '<?= $buttonStatus; ?>')"
                                                                                       id="statusbutton" class="chk"
                                                                                       name="statusbutton" value="246">
                                                                                <span class="toggle-base"></span>
                                                                            </span>
                                                                            <?php } ?>
                                                                        </div>
                                                                        <div class="check-combo">
                                                                            <label id="defaultText_246">

                                                                                <ul>
                                                                                    <?php if ($userObj->hasPermission($update)) { ?>
                                                                                        <li class="entypo-twitter"
                                                                                            data-network="twitter">
                                                                                            <a href="vehicle_category_action.php?id=<?= $MedService['iVehicleCategoryId'] . $eServiceType . $eSubService; ?>"
                                                                                               data-toggle="tooltip"
                                                                                               title="Edit">
                                                                                                <img src="img/edit-new.png"
                                                                                                     alt="Edit">
                                                                                            </a>
                                                                                        </li>
                                                                                    <?php }
                                                                                    if (strtoupper(ENABLE_SUB_PAGES) == 'YES' && $userObj->hasPermission($manage_inner_page)) { ?>
                                                                                        <?php if ($MEDICAL_SERVICE["ManageServiceKey"] == 'VideoConsult') { ?>
                                                                                            <li class="entypo-twitter"
                                                                                                data-network="twitter">
                                                                                                <?php if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'Yes' || $THEME_OBJ->isProMSThemeActive() == 'Yes') { ?>
                                                                                                    <a href="videoconsult_content_action.php?iVehicleCategoryId=<?= $MedService['iVehicleCategoryId'] . '&eCatType=' . $MedService['eCatType'] . $lang_data_id; ?>"
                                                                                                       data-toggle="tooltip"
                                                                                                       title="Edit Inner Page">
                                                                                                        <img src="<?php echo $innerPageIcon ?>"
                                                                                                             alt="Edit">
                                                                                                    </a>
                                                                                                <?php } else { ?>
                                                                                                    <a href="home_content_videoconsult_action.php?iVehicleCategoryId=<?= $MedService['iVehicleCategoryId'] . '&eCatType=' . $MedService['eCatType'] . $lang_data_id; ?>"
                                                                                                       data-toggle="tooltip"
                                                                                                       title="Edit Inner Page">
                                                                                                        <img src="<?php echo $innerPageIcon ?>"
                                                                                                             alt="Edit">
                                                                                                    </a>
                                                                                                <?php } ?>
                                                                                            </li>
                                                                                        <?php } else { ?>
                                                                                            <li class="entypo-twitter"
                                                                                                data-network="twitter">


                                                                                                <?php if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'Yes' || $THEME_OBJ->isProMSThemeActive() == 'Yes' || $THEME_OBJ->isProKXThemeActive() == "Yes") { ?>
                                                                                                    <?php
                                                                                                    $file = "medical_content_services_action.php";
                                                                                                    if ($MedService['eCatType'] == "DeliverAll") {
                                                                                                        $file = "deliverall_dynamic_page.php";
                                                                                                    }
                                                                                                    if ($MedService['eCatType'] == "Ride") {
                                                                                                        $file = "taxi_content_action.php";
                                                                                                    } ?>
                                                                                                    <a href="<?= $file; ?>?iVehicleCategoryId=<?= $MedService['iVehicleCategoryId'] . '&eCatType=' . $MedService['eCatType'] . $lang_data_id; ?>"
                                                                                                       data-toggle="tooltip"
                                                                                                       title="Edit Inner Page">
                                                                                                        <img src="<?php echo $innerPageIcon ?>"
                                                                                                             alt="Edit">
                                                                                                    </a>
                                                                                                <?php } else { ?>
                                                                                                    <a href="home_content_medical_services_action.php?iVehicleCategoryId=<?= $MedService['iVehicleCategoryId'] . '&eCatType=' . $MedService['eCatType'] . $lang_data_id; ?>"
                                                                                                       data-toggle="tooltip"
                                                                                                       title="Edit Inner Page">
                                                                                                        <img src="<?php echo $innerPageIcon ?>"
                                                                                                             alt="Edit">
                                                                                                    </a>
                                                                                                <?php } ?>
                                                                                            </li>
                                                                                        <?php } ?>
                                                                                    <?php } ?>


                                                                                    <?php if (($MedService['eCatType'] != "ServiceProvider" && $MedService['eCatType'] != "MoreDelivery") && $MedService['iParentId'] == 0) { ?>
                                                                                    <?php } else { ?>
                                                                                        <?php if ($userObj->hasPermission($view)) {

                                                                                            ?>
                                                                                            <li class="entypo-gplus"
                                                                                                data-network="gplus">
                                                                                                <a href="service_type.php?iVehicleCategoryId=<?= $MedService['iVehicleCategoryId'] . $eServiceType ?>"
                                                                                                   target="_blank"
                                                                                                   data-toggle="tooltip"
                                                                                                   title="View Subcategories">
                                                                                                    <img src="img/view-icon.png" alt="<?= $MedService['eStatus']; ?>">
                                                                                                </a>
                                                                                            </li>
                                                                                            <?php
                                                                                        }
                                                                                    } ?>
                                                                                </ul>
                                                                                <div class="medical-service-note">
                                                                                    <?php if ($MedService['eCatType'] == "Ride") { ?>
                                                                                        This feature will work only if the Taxi Component is present in the system.
                                                                                    <?php } else if ($MedService['eCatType'] == "ServiceProvider") { ?>
                                                                                        This feature will work only if the Other Services Component is present in the system.
                                                                                    <?php } else if ($MedService['eCatType'] == "DeliverAll") { ?>
                                                                                        This feature will work only if the Store Delivery (i.e Food, Grocery, etc.) Component is present in the system.
                                                                                    <?php } ?>
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php }
                                                        } else {
                                                            ?>
                                                            <li style="font-size: 16px">No Services Found.</li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div> <!--TABLE-END-->
                    </div>
                </div>
                <div class="modal fade" id="medical_services_modal" tabindex="-1" role="dialog" aria-hidden="true"
                     data-backdrop="static" data-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content nimot-class">
                            <div class="modal-header">
                                <h4>
                                    Medical Services -
                                    <span id="ms_title_section">Book An Appointment/Service</span>
                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                </h4>
                            </div>
                            <div class="modal-body">
                                <form method="post" action="">
                                    <input type="hidden" name="eMedicalServiceCat" id="eMedicalServiceCat">
                                    <table class="table table-striped table-bordered table-hover"
                                           id="medical-service-table">
                                        <thead>
                                        <tr>
                                            <th>Service Category</th>
                                            <th>Display Order</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody id="medical-service-list"></tbody>
                                    </table>
                                </form>
                            </div>
                            <div class="modal-footer" style="text-align: left">
                                <button type="button" class="btn btn-default" onclick="saveMedicalServices()">Save
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>
                        Main Category module will list all Main Category on this page.
                    </li>
                    <?php if ($userObj->hasPermission($delete)) { ?>
                        <li>Administrator can Delete / View Subcategories / Delete any Main Category</li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/vehicle_category.php" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="iVehicleCategoryId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?= $option; ?>">
    <input type="hidden" name="keyword" value="<?= $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="eType" value="<?= $eType ?>">
</form>
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        } else {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                this.checked = '';
            });
        }
    });
    $("#Search").on('click', function () {
        //$('html').addClass('loading');
        var action = $("#_list_form").attr('action');
        //alert(action);
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });
    $('.entypo-export').click(function (e) {
        e.stopPropagation();
        var $this = $(this).parent().find('div');
        $(".openHoverAction-class div").not($this).removeClass('active');
        $this.toggleClass('active');
    });
    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
        }
    });

    function getMedicalServices(eMedicalServiceCat, eMedicalServiceCatTitle) {
        $('#eMedicalServiceCat').val(eMedicalServiceCat);
        $('#ms_title_section').text(eMedicalServiceCatTitle);
        $('#medical_services_modal').modal('show');
        $('#medical-service-table').hide();
        $('#medical-service-list').html("");
        $("#loaderIcon").show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'ajax_get_medical_services.php' ?>',
            'AJAX_DATA': {eMedicalServiceCat: eMedicalServiceCat, action: 'GET'},
            'REQUEST_DATA_TYPE': 'html',
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $("#loaderIcon").hide();
            if (response.action == "1") {
                var responseData = response.result;
                $('#medical-service-table').show();
                $('#medical-service-list').html(responseData);
                $('.make-switch')['bootstrapSwitch']();
            }
        });
    }

    function saveMedicalServices() {
        $("#loaderIcon").show();
        var eMedicalServiceCat = $('#eMedicalServiceCat').val();
        var iVehicleCategoryIdArr = [];
        var iDisplayOrderArr = [];
        for (var i = 0; i < $('input[name="iVehicleCategoryId[]"]').length; i++) {
            if ($('input[name="iVehicleCategoryId[]"]').eq(i).is(":checked")) {
                iVehicleCategoryIdArr.push($('input[name="iVehicleCategoryId[]"]').eq(i).val());
            }
        }
        for (var i = 0; i < $('select[name="ms_display_order[]"]').length; i++) {
            iDisplayOrderArr.push($('select[name="ms_display_order[]"]').eq(i).val());
        }
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'ajax_get_medical_services.php' ?>',
            'AJAX_DATA': {
                eMedicalServiceCat: eMedicalServiceCat,
                action: 'SAVE',
                iVehicleCategoryIdArr: iVehicleCategoryIdArr.toString(),
                iDisplayOrderArr: iDisplayOrderArr.toString()
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                location.reload();
            } else {
                $("#loaderIcon").hide();
            }
        });
    }

    $(".TextColor").on("input", function () {
        var color = $(this).val();
        $(this).next().val(color);
    });
    $(".BgColor").on("input", function () {
        var color = $(this).val();
        $(this).next().val(color);
    });
   
    function displayNote(iVehicleCategoryId,eStatus,SubCategoriescount) {
        if(eStatus == 'Active' && SubCategoriescount == 0){
            $("#ufx-service-note_"+iVehicleCategoryId).css("display", "none"); 
        } else if(eStatus == 'Inactive' && SubCategoriescount == 0){
            $("#ufx-service-note_"+iVehicleCategoryId).css("display", "block"); 
        }
    }

    $('[name="ms_category_status"]').change(function() {
        $("#loaderIcon").show();

        var eMedicalServiceCat = $(this).val();
        var status = $(this).is(':checked') ? 'Active' : 'Inactive';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'ajax_get_medical_services.php' ?>',
            'AJAX_DATA': {eMedicalServiceCat: eMedicalServiceCat, status: status, action: 'STATUS'}
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $("#loaderIcon").hide();
            if (response.action == "1") {
                
            }
        });
    });
</script>
</body>
<!-- END BODY-->
</html>
