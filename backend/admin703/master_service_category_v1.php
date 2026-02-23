<?php

include_once('../common.php');





$script = 'VehicleCategory';

$tbl_name = "master_service_category_v1";



if (!$userObj->hasPermission('view-master-service-category')) {

    $userObj->redirect();

}





$sql_vehicle_category_table_name = getVehicleCategoryTblName();



$iMasterServiceCategoryId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";

$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";



if(isset($_POST['eMasterServiceCatType'])) {    

    // echo "<pre>"; print_r($_POST); print_r($_FILES); exit;

    $eMasterServiceCatType = $_POST['eMasterServiceCatType'];



    $service_details = $obj->MySQLSelect("SELECT tCategoryDetails FROM $tbl_name WHERE eType = '$eMasterServiceCatType' ");

    $tCategoryDetails = $service_details[0]['tCategoryDetails'];



    $Data_Update = array();



    if($eMasterServiceCatType == "TrackService") {

        $vImageTrackService = "";

        $image_object = $_FILES['vImageTrackService']['tmp_name'];  

        $image_name   = $_FILES['vImageTrackService']['name'];



        if($image_name != ""){

            $filecheck = basename($_FILES['vImageTrackService']['name']);

            $fileextarr = explode(".",$filecheck);

            $ext=strtolower($fileextarr[scount($fileextarr)-1]);

            $flag_error = 0;

            if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){

                $flag_error = 1;

                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";

            }

            $image_info = getimagesize($_FILES["vImageTrackService"]["tmp_name"]);

            $image_width = $image_info[0];

            $image_height = $image_info[1];



            if($flag_error == 1){

                $_SESSION['success'] = '3';

                $_SESSION['var_msg'] = $var_msg;

                header("Location:master_service_category.php");

                exit;

            } else {

                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];

                if(!is_dir($Photo_Gallery_folder)){

                    mkdir($Photo_Gallery_folder, 0777);

                    chmod($Photo_Gallery_folder, 0777);

                }  

                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');

                $vImageTrackService = $img[0];



                if(!empty($_POST['vImageTrackService_old']) && file_exists($Photo_Gallery_folder . $_POST['vImageTrackService_old'])) {

                    unlink($Photo_Gallery_folder . $_POST['vImageTrackService_old']);

                }

            }

        }



        $vImageTrackServiceAdd = "";

        $image_object = $_FILES['vImageTrackServiceAdd']['tmp_name'];  

        $image_name   = $_FILES['vImageTrackServiceAdd']['name'];



        if($image_name != ""){

            $filecheck = basename($_FILES['vImageTrackServiceAdd']['name']);                            

            $fileextarr = explode(".",$filecheck);

            $ext=strtolower($fileextarr[scount($fileextarr)-1]);

            $flag_error = 0;

            if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){

                $flag_error = 1;

                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";

            }

            $image_info = getimagesize($_FILES["vImageTrackServiceAdd"]["tmp_name"]);

            $image_width = $image_info[0];

            $image_height = $image_info[1];



            if($flag_error == 1){

                $_SESSION['success'] = '3';

                $_SESSION['var_msg'] = $var_msg;

                header("Location:master_service_category.php");

                exit;

            } else {

                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];

                if(!is_dir($Photo_Gallery_folder)){

                    mkdir($Photo_Gallery_folder, 0777);

                    chmod($Photo_Gallery_folder, 0777);

                }  

                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');

                $vImageTrackServiceAdd = $img[0];



                if(!empty($_POST['vImageTrackServiceAdd_old']) && file_exists($Photo_Gallery_folder . $_POST['vImageTrackServiceAdd_old'])) {

                    unlink($Photo_Gallery_folder . $_POST['vImageTrackServiceAdd_old']);

                }

            }

        }



        if(!empty($tCategoryDetails)) {

            $tCategoryDetails = json_decode($tCategoryDetails, true);

        } else {

            $tCategoryDetails = array();

        }



        if(!empty($vImageTrackService)) {

            $tCategoryDetails['TrackService']['vImage'] = $vImageTrackService;    

        }



        if(!empty($vImageTrackServiceAdd)) {

            $tCategoryDetails['TrackServiceAdd']['vImage'] = $vImageTrackServiceAdd;    

        }



        $Data_Update['tCategoryDetails'] = json_encode($tCategoryDetails, JSON_UNESCAPED_UNICODE);

        $where = " eType = 'TrackService' ";

        $obj->MySQLQueryPerform($tbl_name, $Data_Update, 'update', $where); 



    } else {

        $vImageRideSharePublish = "";

        $image_object = $_FILES['vImageRideSharePublish']['tmp_name'];  

        $image_name   = $_FILES['vImageRideSharePublish']['name'];



        if($image_name != ""){

            $filecheck = basename($_FILES['vImageRideSharePublish']['name']);

            $fileextarr = explode(".",$filecheck);

            $ext=strtolower($fileextarr[scount($fileextarr)-1]);

            $flag_error = 0;

            if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){

                $flag_error = 1;

                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";

            }

            $image_info = getimagesize($_FILES["vImageRideSharePublish"]["tmp_name"]);

            $image_width = $image_info[0];

            $image_height = $image_info[1];



            if($flag_error == 1){

                $_SESSION['success'] = '3';

                $_SESSION['var_msg'] = $var_msg;

                header("Location:master_service_category.php");

                exit;

            } else {

                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];

                if(!is_dir($Photo_Gallery_folder)){

                    mkdir($Photo_Gallery_folder, 0777);

                    chmod($Photo_Gallery_folder, 0777);

                }  

                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');

                $vImageRideSharePublish = $img[0];



                if(!empty($_POST['vImageRideSharePublish_old']) && file_exists($Photo_Gallery_folder . $_POST['vImageRideSharePublish_old'])) {

                    unlink($Photo_Gallery_folder . $_POST['vImageRideSharePublish_old']);

                }

            }

        }



        $vImageRideShareBook = "";

        $image_object = $_FILES['vImageRideShareBook']['tmp_name'];  

        $image_name   = $_FILES['vImageRideShareBook']['name'];



        if($image_name != ""){

            $filecheck = basename($_FILES['vImageRideShareBook']['name']);                            

            $fileextarr = explode(".",$filecheck);

            $ext=strtolower($fileextarr[scount($fileextarr)-1]);

            $flag_error = 0;

            if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){

                $flag_error = 1;

                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";

            }

            $image_info = getimagesize($_FILES["vImageRideShareBook"]["tmp_name"]);

            $image_width = $image_info[0];

            $image_height = $image_info[1];



            if($flag_error == 1){

                $_SESSION['success'] = '3';

                $_SESSION['var_msg'] = $var_msg;

                header("Location:master_service_category.php");

                exit;

            } else {

                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];

                if(!is_dir($Photo_Gallery_folder)){

                    mkdir($Photo_Gallery_folder, 0777);

                    chmod($Photo_Gallery_folder, 0777);

                }  

                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');

                $vImageRideShareBook = $img[0];



                if(!empty($_POST['vImageRideShareBook_old']) && file_exists($Photo_Gallery_folder . $_POST['vImageRideShareBook_old'])) {

                    unlink($Photo_Gallery_folder . $_POST['vImageRideShareBook_old']);

                }

            }

        }



        $vImageRideShareMyRides = "";

        $image_object = $_FILES['vImageRideShareMyRides']['tmp_name'];  

        $image_name   = $_FILES['vImageRideShareMyRides']['name'];



        if($image_name != ""){

            $filecheck = basename($_FILES['vImageRideShareMyRides']['name']);                            

            $fileextarr = explode(".",$filecheck);

            $ext=strtolower($fileextarr[scount($fileextarr)-1]);

            $flag_error = 0;

            if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){

                $flag_error = 1;

                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";

            }

            $image_info = getimagesize($_FILES["vImageRideShareMyRides"]["tmp_name"]);

            $image_width = $image_info[0];

            $image_height = $image_info[1];



            if($flag_error == 1){

                $_SESSION['success'] = '3';

                $_SESSION['var_msg'] = $var_msg;

                header("Location:master_service_category.php");

                exit;

            } else {

                $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];

                if(!is_dir($Photo_Gallery_folder)){

                    mkdir($Photo_Gallery_folder, 0777);

                    chmod($Photo_Gallery_folder, 0777);

                }  

                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');

                $vImageRideShareMyRides = $img[0];



                if(!empty($_POST['vImageRideShareMyRides_old']) && file_exists($Photo_Gallery_folder . $_POST['vImageRideShareMyRides_old'])) {

                    unlink($Photo_Gallery_folder . $_POST['vImageRideShareMyRides_old']);

                }

            }

        }



        if(!empty($tCategoryDetails)) {

            $tCategoryDetails = json_decode($tCategoryDetails, true);

        } else {

            $tCategoryDetails = array();

        }



        if(!empty($vImageRideSharePublish)) {

            $tCategoryDetails['RideSharePublish']['vImage'] = $vImageRideSharePublish;    

        }



        if(!empty($vImageRideShareBook)) {

            $tCategoryDetails['RideShareBook']['vImage'] = $vImageRideShareBook;    

        }



        if(!empty($vImageRideShareMyRides)) {

            $tCategoryDetails['RideShareMyRides']['vImage'] = $vImageRideShareMyRides;    

        }



        $Data_Update['tCategoryDetails'] = json_encode($tCategoryDetails, JSON_UNESCAPED_UNICODE);

        $where = " eType = 'RideShare' ";

        $obj->MySQLQueryPerform($tbl_name, $Data_Update, 'update', $where);

    } 

     



    $_SESSION['success'] = '1';

    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

    header("Location:master_service_category.php");

    exit;

}



if (!empty($iMasterServiceCategoryId) && !empty($status)) {

    if (SITE_TYPE != 'Demo') {

        $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'");





        $ssql = getMasterServiceCategoryQuery($eType, 'Yes');



        if($eType == 'Bidding' || $eType == "MedicalServices")

        {

            header("Location:master_service_category.php");

            exit;

        }

        

        $vehicle_category_data = $obj->MySQLSelect("SELECT vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo,vc.vListLogo1,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType,  (select count(iVehicleCategoryId) from ".$sql_vehicle_category_table_name." where iParentId = vc.iVehicleCategoryId AND eStatus != 'Deleted') as SubCategories FROM ".$sql_vehicle_category_table_name." as vc WHERE eStatus != 'Deleted' AND vc.iParentId='0' $ssql");



        

        foreach($vehicle_category_data as $vehicle_category) {

            $statusNew = $status;

            if($status == "Active") {

                $checkLog = $obj->MySQLSelect("SELECT eStatus FROM vehicle_category_status_log WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");



                if(!empty($checkLog) && $checkLog > 0) {

                    $statusNew = $checkLog[0]['eStatus'];

                }

            }



            $obj->sql_query("UPDATE vehicle_category SET eStatus = '" . $statusNew . "' WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");



            $vehicle_category_new = $obj->MySQLSelect("SELECT iServiceId FROM vehicle_category WHERE iVehicleCategoryId = '" . $vehicle_category['iVehicleCategoryId'] . "'");

            if($vehicle_category_new[0]['iServiceId'] > 0) {

                $obj->sql_query("UPDATE service_categories SET eStatus = '$statusNew' WHERE iServiceId = '" . $vehicle_category_new[0]['iServiceId'] . "'");    

            }

        }



        if($eType == "Ride") {

            $statusVal = $status == "Active" ? "Yes" : "No";

            $obj->sql_query("UPDATE configurations SET vValue = '$statusVal', eAdminDisplay = '$statusVal' WHERE vName = 'ENABLE_CORPORATE_PROFILE'");

        }



        header("Location:master_service_category.php");

        exit;

    } else {

        $_SESSION['success'] = '2';

        header("Location:master_service_category.php");

        exit();

    }

}



$subquery = "";

/*if(!$MODULES_OBJ->isRideFeatureAvailable("Yes")) {

    $subquery .= " AND eType != 'Ride'";

}

if(!$MODULES_OBJ->isDeliveryFeatureAvailable("Yes")) {

    $subquery .= " AND eType != 'Deliver'";

}

if(!$MODULES_OBJ->isDeliverAllFeatureAvailable("Yes")) {

    $subquery .= " AND eType != 'DeliverAll'";

}

if(!$MODULES_OBJ->isUberXFeatureAvailable("Yes")) {

    $subquery .= " AND eType != 'UberX'";  

}

if(!$MODULES_OBJ->isEnableVideoConsultingService()) {

    $subquery .= " AND eType != 'VideoConsult'";  

}

if(!$MODULES_OBJ->isEnableBiddingServices()) {

    $subquery .= " AND eType != 'Bidding'";  

}

if(!$MODULES_OBJ->isEnableMedicalServices()) {

    $subquery .= " AND eType != 'MedicalServices'";  

}

if(!$MODULES_OBJ->isEnableRentItemService()) {

    $subquery .= " AND eType != 'RentItem'";  

}

if(!$MODULES_OBJ->isEnableTrackServiceFeature()) {

    $subquery .= " AND eType != 'TrackService'";  

}

if(!$MODULES_OBJ->isEnableRideShareService()) {

    $subquery .= " AND eType != 'RideShare'";  

}

if (!$MODULES_OBJ->isEnableRentEstateService()) {

    $subquery .= " AND eType != 'RentEstate'";

}

if (!$MODULES_OBJ->isEnableRentCarsService()) {

    $subquery .= " AND eType != 'RentCars'";

}

if (!$MODULES_OBJ->isEnableNearByService()) {

    $subquery .= " AND eType != 'NearBy'";

}
*/
$master_service_categories = $obj->MySQLSelect("SELECT *, JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vCategoryName FROM $tbl_name WHERE 1 = 1 $subquery AND eStatus != 'Deleted'");



foreach ($master_service_categories as $key => $value) {

    $category_data = array();

    $not_sql = " AND iVehicleCategoryId != 297";

    $ssql = getMasterServiceCategoryQuery($value['eType'], 'Yes');

    

    if($value['eType'] == "Ride") {

        $ssql .= " AND eForMedicalService = 'No' ";

    } elseif($value['eType'] == "VideoConsult") {

        $ssql = getMasterServiceCategoryQuery($value['eType']);

        $vc_data = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT(iParentId)) as ParentIds FROM vehicle_category WHERE eVideoConsultEnable = 'Yes'");

        $ssql .= " AND iVehicleCategoryId IN (" . $vc_data[0]['ParentIds'] . ")";

    } elseif ($value['eType'] == "Bidding") {

        $category_data[0]['Total'] = $BIDDING_OBJ->getBiddingTotalCount('admin');

    } elseif ($value['eType'] == "RentItem") {

        $RentCatId = base64_encode(base64_encode($value['iMasterServiceCategoryId']));    

        $category_data[0]['Total'] = $RENTITEM_OBJ->getRentItemTotalCount('admin',  "" ,$RentCatId);

    } elseif ($value['eType'] == "RentEstate") {

        $EstateCatId = base64_encode(base64_encode($value['iMasterServiceCategoryId']));

        $category_data[0]['Total'] = $RENTITEM_OBJ->getRentItemTotalCount('admin', "" , $EstateCatId);

    } elseif ($value['eType'] == "RentCars") {

        $CarCatId = base64_encode(base64_encode($value['iMasterServiceCategoryId']));

        $category_data[0]['Total'] = $RENTITEM_OBJ->getRentItemTotalCount('admin', "" , $CarCatId);

    } elseif ($value['eType'] == "NearBy") {

        $category_data[0]['Total'] = $NEARBY_OBJ->getNearByCategoryTotalCount('admin');



    } elseif ($value['eType'] == "MedicalServices") {

        $ssql .= " AND eForMedicalService = 'Yes' ";

    }

    if (!in_array($value['eType'], ['Bidding', 'TrackService', 'RideShare', 'RentItem', 'RentEstate', 'RentCars','NearBy'])) {

        $parent_id_sql = " AND iParentId='0' ";

        if ($value['eType'] == "MedicalServices") {

            $parent_id_sql = " AND (iParentId='0' OR iParentId = '3') ";

        }

        $category_data = $obj->MySQLSelect("SELECT COUNT(iVehicleCategoryId) AS Total FROM ".$sql_vehicle_category_table_name."  WHERE  1 = 1 $parent_id_sql AND eStatus!='Deleted' $ssql $not_sql");

    }



    $master_service_categories[$key]['SubCategories'] = $category_data[0]['Total'];

}



?>

<!DOCTYPE html>

<html lang="en">

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <title><?= $SITE_NAME ?> | Master Service Categories</title>

        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <?php include_once('global_files.php'); ?>

        <style type="text/css">

            .table > tbody > tr > td {

                vertical-align: middle;

            }



            .vc-info {

                font-size: 20px;

                margin: 0 0 0 10px;

                position: absolute;

                color: #ff0000;

                cursor: pointer;

            }

        </style>

    </head>

    <!-- END  HEAD-->



    <!-- BEGIN BODY-->

    <body class="padTop53 " >

        <!-- Main LOading -->

        <!-- MAIN WRAPPER -->

        <div id="wrap">

            <?php include_once('header.php'); ?>

            <?php include_once('left_menu.php'); ?>



            <!--PAGE CONTENT -->

            <div id="content">

                <div class="inner">

                    <div id="add-hide-show-div">

                        <div class="row">

                            <div class="col-lg-12">

                                <h2>Master Service Categories</h2>

                                <?php if($userObj->hasPermission('manage-app-home-screen-view')) { ?>

                                <a href="<?= $tconfig['tsite_url_main_admin'] ?>manage_app_home_screen.php" class="add-btn">Manage App Home Screen View</a>

                                <?php } ?>

                            </div>

                        </div>

                        <hr />

                    </div>

                    <?php include('valid_msg.php'); ?>



                    <div class="table-list">

                        <div class="row">

                            <div class="col-lg-12">

                                <div style="clear:both;"></div>

                                <div class="table-responsive">

                                    <table class="table table-striped table-bordered table-hover">

                                        <thead>

                                            <tr>

                                                <th style="width: 100px; text-align: center;">Icon / Banner / Image</th>

                                                <th>Category Name</th>

                                                <th style="width: 200px; text-align: center;">Service Category</th>

                                                <th style="text-align: center;">Status</th>

                                                <th style="text-align: center;">Action</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php if(!empty($master_service_categories) && scount($master_service_categories) > 0) {

                                                foreach ($master_service_categories as $service_category) { 

                                                    $iMasterServiceCategoryId = $service_category['iMasterServiceCategoryId'];

                                                    $eStatus = $service_category['eStatus'];

                                                    $eType = $service_category['eType'];

                                                    $vIconImage = $service_category['vIconImage'];

                                                    if($MODULES_OBJ->isEnableAppHomeScreenLayout()) {

                                                        $vIconImage = $service_category['vIconImage1'];

                                                    }



                                                    if($eType == "TrackService") {

                                                        $vImageOld[$eType]['TrackService'] = $vImageOld[$eType]['TrackServiceAdd'] = "";

                                                        $ServiceTitle[$eType]['TrackService'] = $langage_lbl_admin['LBL_TRACK_SERVICE_TRACK_MEMBER_TXT'];

                                                        $ServiceTitle[$eType]['TrackServiceAdd'] = $langage_lbl_admin['LBL_TRACK_SERVICE_SETUP_PROFILE_TXT'];

                                                        $ServiceTitle[$eType]['TrackServiceLabel'] = 'LBL_TRACK_SERVICE_TRACK_MEMBER_TXT';

                                                        $ServiceTitle[$eType]['TrackServiceAddLabel'] = 'LBL_TRACK_SERVICE_SETUP_PROFILE_TXT';



                                                        $vImageOld[$eType]['TrackService'] = $vImageOld[$eType]['TrackServiceAdd'] = "";

                                                        $tCategoryDetails = $service_category['tCategoryDetails'];

                                                        if(!empty($tCategoryDetails)) {

                                                            $tCategoryDetails = json_decode($tCategoryDetails, true);

                                                            $vImageOld[$eType]['TrackService'] = $tCategoryDetails['TrackService']['vImage'];

                                                            $vImageOld[$eType]['TrackServiceAdd'] = $tCategoryDetails['TrackServiceAdd']['vImage'];

                                                        }

                                                    }



                                                    if($eType == "RideShare") {

                                                        $vImageOld[$eType]['RideSharePublish'] = $vImageOld[$eType]['RideShareBook'] = $vImageOld[$eType]['RideShareMyRides'] = "";

                                                        $ServiceTitle[$eType]['RideSharePublish'] = $langage_lbl_admin['LBL_RIDE_SHARE_PUBLISH_TXT'];

                                                        $ServiceTitle[$eType]['RideShareBook'] = $langage_lbl_admin['LBL_RIDE_SHARE_BOOK_TXT'];

                                                        $ServiceTitle[$eType]['RideShareMyRides'] = $langage_lbl_admin['LBL_RIDE_SHARE_MY_RIDES_TXT'];

                                                        $ServiceTitle[$eType]['RideSharePublishLabel'] = 'LBL_RIDE_SHARE_PUBLISH_TXT';

                                                        $ServiceTitle[$eType]['RideShareBookLabel'] = 'LBL_RIDE_SHARE_BOOK_TXT';

                                                        $ServiceTitle[$eType]['RideShareMyRidesLabel'] = 'LBL_RIDE_SHARE_MY_RIDES_TXT';



                                                        $tCategoryDetails = $service_category['tCategoryDetails'];

                                                        if(!empty($tCategoryDetails)) {

                                                            $tCategoryDetails = json_decode($tCategoryDetails, true);

                                                            $vImageOld[$eType]['RideSharePublish'] = $tCategoryDetails['RideSharePublish']['vImage'];

                                                            $vImageOld[$eType]['RideShareBook'] = $tCategoryDetails['RideShareBook']['vImage'];

                                                            $vImageOld[$eType]['RideShareMyRides'] = $tCategoryDetails['RideShareMyRides']['vImage'];

                                                        }

                                                    }

                                                    

                                                    ?>

                                                <tr>

                                                    <td style="text-align: center;">

                                                        <?php if(!in_array($eType, ['MedicalServices', 'NearBy'])) { ?>

                                                        <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vIconImage ?>">

                                                        <?php } else { ?>

                                                        --

                                                        <?php } ?>

                                                    </td>

                                                    <td><?= $service_category['vCategoryName'] ?></td>

                                                    <td style="text-align: center;">

                                                        <?php if($eType == "Bidding") { ?>

                                                            <a class="add-btn-sub" href="bidding_master_category.php" target="_blank">Add/View (<?= $service_category['SubCategories']; ?>) </a>

                                                         <?php } else if($eType == "RentItem") { ?>

                                                             <a class="add-btn-sub" href="bsr_master_category.php?catid=<?= $RentCatId ?>" target="_blank"><?= $eType == "UberX" ? "Add/View" : $langage_lbl_admin['LBL_SERVICES'] ?> (<?= $service_category['SubCategories']; ?>) </a>

                                                         <?php } else if($eType == "RentEstate") { ?>

                                                             <a class="add-btn-sub" href="bsr_master_category.php?catid=<?= $EstateCatId ?>" target="_blank"><?= $eType == "UberX" ? "Add/View" : $langage_lbl_admin['LBL_SERVICES'] ?> (<?= $service_category['SubCategories']; ?>) </a>

                                                         <?php }  else if($eType == "RentCars") { ?>

                                                             <a class="add-btn-sub" href="bsr_master_category.php?catid=<?= $CarCatId ?>" target="_blank"><?= $eType == "UberX" ? "Add/View" : $langage_lbl_admin['LBL_SERVICES'] ?> (<?= $service_category['SubCategories']; ?>) </a>

                                                         <?php } else if ($eType == "NearBy") { ?>

                                                            <a class="add-btn-sub" href="near_by_category.php" target="_blank">Add/View (<?= $service_category['SubCategories']; ?>) </a>

                                                        <?php } elseif (in_array($eType, ['TrackService', 'RideShare'])) { ?>

                                                            <button type="button" class="add-btn-sub" data-toggle="modal" data-target="#<?= $eType ?>Modal">Edit Details</button>



                                                            <div class="modal fade" id="<?= $eType ?>Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">

                                                                <div class="modal-dialog">

                                                                    <div class="modal-content nimot-class">

                                                                        <div class="modal-header">

                                                                            <h4>

                                                                                <?= $service_category['vCategoryName'] ?>

                                                                                <button type="button" class="close" data-dismiss="modal">x</button>

                                                                            </h4>

                                                                        </div>

                                                                        <form action="" method="POST" enctype="multipart/form-data">

                                                                            <div class="modal-body">

                                                                                <div class="form-group">

                                                                                    <input type="hidden" name="eMasterServiceCatType" id="eMasterServiceCatType" value="<?= $eType ?>">



                                                                                    <?php if($eType == "TrackService") { ?>

                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Image</label>

                                                                                        </div>

                                                                                        <div class="col-lg-12 marginbottom-10">

                                                                                            <?php if(!empty($vImageOld[$eType]['TrackService'])) { ?>

                                                                                            <div class="marginbottom-10">

                                                                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src='.$tconfig['tsite_upload_app_home_screen_images'].$vImageOld[$eType]['TrackService']; ?>">

                                                                                            </div>

                                                                                            <?php } ?>

                                                                                            <input type="file" class="form-control" name="vImageTrackService">

                                                                                            <input type="hidden" class="form-control" name="vImageTrackService_old" value="<?= $vImageOld[$eType]['TrackService'] ?>">

                                                                                        </div>

                                                                                        <div class="col-lg-12">

                                                                                            <strong>Note: Upload only png image size of 360px X 360px.</strong>

                                                                                        </div>

                                                                                    </div>



                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Title</label>

                                                                                        </div>

                                                                                        <div class="col-lg-10">

                                                                                            <input type="text" class="form-control" value="<?= $ServiceTitle[$eType]['TrackService'] ?>" readonly disabled>

                                                                                        </div>

                                                                                        <div class="col-lg-2">

                                                                                            <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $ServiceTitle[$eType]["TrackServiceLabel"] ?>" class="btn btn-info" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>

                                                                                        </div>

                                                                                    </div>  

                                                                                    <hr />

                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Image</label>

                                                                                        </div>

                                                                                        <div class="col-lg-12 marginbottom-10">

                                                                                            <?php if(!empty($vImageOld[$eType]['TrackServiceAdd'])) { ?>

                                                                                            <div class="marginbottom-10">

                                                                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src='.$tconfig['tsite_upload_app_home_screen_images'].$vImageOld[$eType]['TrackServiceAdd']; ?>">

                                                                                            </div>

                                                                                            <?php } ?>

                                                                                            <input type="file" class="form-control" name="vImageTrackServiceAdd">

                                                                                            <input type="hidden" class="form-control" name="vImageTrackServiceAdd_old" value="<?= $vImageOld[$eType]['TrackServiceAdd'] ?>">

                                                                                        </div>

                                                                                        <div class="col-lg-12">

                                                                                            <strong>Note: Upload only png image size of 360px X 360px.</strong>

                                                                                        </div>

                                                                                    </div>



                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Title</label>

                                                                                        </div>

                                                                                        <div class="col-lg-10">

                                                                                            <input type="text" class="form-control" value="<?= $ServiceTitle[$eType]['TrackServiceAdd'] ?>" readonly disabled>

                                                                                        </div>

                                                                                        <div class="col-lg-2">

                                                                                            <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $ServiceTitle[$eType]["TrackServiceAddLabel"] ?>" class="btn btn-info" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>

                                                                                        </div>

                                                                                    </div>

                                                                                    <?php } else { ?>  



                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Image</label>

                                                                                        </div>

                                                                                        <div class="col-lg-12 marginbottom-10">

                                                                                            <?php if(!empty($vImageOld[$eType]['RideSharePublish'])) { ?>

                                                                                            <div class="marginbottom-10">

                                                                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src='.$tconfig['tsite_upload_app_home_screen_images'].$vImageOld[$eType]['RideSharePublish']; ?>">

                                                                                            </div>

                                                                                            <?php } ?>

                                                                                            <input type="file" class="form-control" name="vImageRideSharePublish">

                                                                                            <input type="hidden" class="form-control" name="vImageRideSharePublish_old" value="<?= $vImageOld[$eType]['RideSharePublish'] ?>">

                                                                                        </div>

                                                                                        <div class="col-lg-12">

                                                                                            <strong>Note: Upload only png image size of 360px X 360px.</strong>

                                                                                        </div>

                                                                                    </div>



                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Title</label>

                                                                                        </div>

                                                                                        <div class="col-lg-10">

                                                                                            <input type="text" class="form-control" value="<?= $ServiceTitle[$eType]['RideSharePublish'] ?>" readonly disabled>

                                                                                        </div>

                                                                                        <div class="col-lg-2">

                                                                                            <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $ServiceTitle[$eType]["RideSharePublishLabel"] ?>" class="btn btn-info" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>

                                                                                        </div>

                                                                                    </div>  

                                                                                    <hr />

                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Image</label>

                                                                                        </div>

                                                                                        <div class="col-lg-12 marginbottom-10">

                                                                                            <?php if(!empty($vImageOld[$eType]['RideShareBook'])) { ?>

                                                                                            <div class="marginbottom-10">

                                                                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src='.$tconfig['tsite_upload_app_home_screen_images'].$vImageOld[$eType]['RideShareBook']; ?>">

                                                                                            </div>

                                                                                            <?php } ?>

                                                                                            <input type="file" class="form-control" name="vImageRideShareBook">

                                                                                            <input type="hidden" class="form-control" name="vImageRideShareBook_old" value="<?= $vImageOld[$eType]['RideShareBook'] ?>">

                                                                                        </div>

                                                                                        <div class="col-lg-12">

                                                                                            <strong>Note: Upload only png image size of 360px X 360px.</strong>

                                                                                        </div>

                                                                                    </div>



                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Title</label>

                                                                                        </div>

                                                                                        <div class="col-lg-10">

                                                                                            <input type="text" class="form-control" value="<?= $ServiceTitle[$eType]['RideShareBook'] ?>" readonly disabled>

                                                                                        </div>

                                                                                        <div class="col-lg-2">

                                                                                            <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $ServiceTitle[$eType]["RideShareBookLabel"] ?>" class="btn btn-info" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>

                                                                                        </div>

                                                                                    </div>   



                                                                                    <hr />

                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Image</label>

                                                                                        </div>

                                                                                        <div class="col-lg-12 marginbottom-10">

                                                                                            <?php if(!empty($vImageOld[$eType]['RideShareMyRides'])) { ?>

                                                                                            <div class="marginbottom-10">

                                                                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src='.$tconfig['tsite_upload_app_home_screen_images'].$vImageOld[$eType]['RideShareMyRides']; ?>">

                                                                                            </div>

                                                                                            <?php } ?>

                                                                                            <input type="file" class="form-control" name="vImageRideShareMyRides">

                                                                                            <input type="hidden" class="form-control" name="vImageRideShareMyRides_old" value="<?= $vImageOld[$eType]['RideShareMyRides'] ?>">

                                                                                        </div>

                                                                                        <div class="col-lg-12">

                                                                                            <strong>Note: Upload only png image size of 360px X 360px.</strong>

                                                                                        </div>

                                                                                    </div>



                                                                                    <div class="row pb-10">

                                                                                        <div class="col-lg-12">

                                                                                            <label style="font-size: 13px">Title</label>

                                                                                        </div>

                                                                                        <div class="col-lg-10">

                                                                                            <input type="text" class="form-control" value="<?= $ServiceTitle[$eType]['RideShareMyRides'] ?>" readonly disabled>

                                                                                        </div>

                                                                                        <div class="col-lg-2">

                                                                                            <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages.php?option=&keyword=' . $ServiceTitle[$eType]["RideShareMyRidesLabel"] ?>" class="btn btn-info" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>

                                                                                        </div>

                                                                                    </div> 

                                                                                    <?php } ?>

                                                                                </div>

                                                                            </div>

                                                                            <div class="modal-footer" style="text-align: left">

                                                                                <button type="submit" name="submitbtn" class="btn btn-default">Save

                                                                                </button>

                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>

                                                                            </div>

                                                                        </form>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                        <?php } else { ?>

                                                            <a class="add-btn-sub" href="vehicle_category.php?eType=<?= $eType . $eVideoConsult ?>" target="_blank"><?= $eType == "UberX" ? "Add/View" : $langage_lbl_admin['LBL_SERVICES'] ?> (<?= $service_category['SubCategories']; ?>) </a>

                                                        <?php } ?>

                                                    </td>

                                                    <td style="text-align: center;">

                                                        <?php

                                                        if ($service_category['eStatus'] == 'Active') {

                                                            $status_img = "img/active-icon.png";

                                                        } else {

                                                            $status_img = "img/inactive-icon.png";

                                                        }

                                                        ?>

                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $service_category['eStatus']; ?>">        

                                                    </td>

                                                    <td align="center" style="text-align:center;" class="action-btn001">

                                                        <div class="share-button openHoverAction-class" style="display: block;">

                                                            <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>

                                                            <div class="social show-moreOptions for-two openPops_<?= $iMasterServiceCategoryId; ?>">

                                                                <ul>

                                                                    <?php if(!in_array($eType, ['MedicalServices', 'NearBy'])) { ?>

                                                                    <li class="entypo-twitter" data-network="twitter">

                                                                        <a href="master_service_category_action.php?id=<?= $iMasterServiceCategoryId; ?>" data-toggle="tooltip" title="Edit">

                                                                            <img src="img/edit-icon.png" alt="Edit">

                                                                        </a>

                                                                    </li>

                                                                    <?php } if ($userObj->hasPermission('update-status-master-service-category')) { ?>

                                                                        <li class="entypo-facebook" data-network="facebook">

                                                                            <a href="javascript:void(0);" onClick="window.location.href='master_service_category.php?id=<?= $iMasterServiceCategoryId; ?>&status=Active&eType=<?= $eType ?>'"  data-toggle="tooltip" title="Activate"><img src="img/active-icon.png" alt="<?= $eStatus; ?>" ></a>

                                                                        </li>

                                                                        <li class="entypo-gplus" data-network="gplus">

                                                                            <a href="javascript:void(0);" onClick="window.location.href='master_service_category.php?id=<?= $iMasterServiceCategoryId; ?>&status=Inactive&eType=<?= $eType ?>'" data-toggle="tooltip" title="Deactivate">

                                                                                <img src="img/inactive-icon.png" alt="<?= $eStatus; ?>" >

                                                                            </a>

                                                                        </li>

                                                                    <?php } ?> 

                                                                </ul>

                                                            </div>

                                                            <?php if($CONFIG_OBJ->isOnlyCashPaymentModeAvailable() && $MODULES_OBJ->isEnableVideoConsultingService() && $eType == "VideoConsult") { ?>

                                                            <span class="vc-info" data-toggle="tooltip" data-original-title="Video Consulting Service is not available in the applications as only Cash payment option is available in the system."><i class="fa fa-exclamation-triangle"></i></span>

                                                            <?php } ?>

                                                        </div>                         

                                                    </td>

                                                </tr>

                                            <?php } 

                                                } else { ?>

                                            <tr>

                                                <td colspan="5">No records found.</td>

                                            </tr>

                                            <?php } ?>

                                        </tbody>

                                    </table>

                                    <?php include('pagination_n.php'); ?>

                                </div>

                            </div> <!--TABLE-END-->

                        </div>

                    </div>

                    <div class="admin-notes">

                        <h4>Notes:</h4>

                        <ul>

                            <li>Administrator can Activate / Deactivate / Modify any Master Service Category.</li>

                            <?php if($CONFIG_OBJ->isOnlyCashPaymentModeAvailable() && $MODULES_OBJ->isEnableVideoConsultingService()) { ?>

                            <li><strong>Video Consulting</strong> Service is not available in the applications as only <strong>Cash</strong> payment option is available in the system.</li>

                            <?php } ?>

                        </ul>

                    </div>

                </div>

            </div>

            <!--END PAGE CONTENT -->

        </div>

        <!--END MAIN WRAPPER -->



        <?php include_once('footer.php'); ?>

        <script>

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

        </script>

    </body>

    <!-- END BODY-->

</html>