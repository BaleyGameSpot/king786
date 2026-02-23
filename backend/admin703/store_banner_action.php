<?php
include_once('../common.php');
require_once(TPATH_CLASS . "Imagecrop.class.php");

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'banners';
$script = 'Banner';
// fetch all lang from language_master table 
$count_all = 1;
$vImage = isset($_POST['vImage_old']) ? $_POST['vImage_old'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();

$whereserviceId = " AND iServiceId != 0";
$ssqlbuyanyservice = "";
if(isset($_REQUEST['eBuyAnyService']) && in_array($_REQUEST['eBuyAnyService'], ['Genie', 'Runner', 'Anywhere']) && $MODULES_OBJ->isEnableAnywhereDeliveryFeature())
{
    $ssqlbuyanyservice = " AND eBuyAnyService = '".$_REQUEST['eBuyAnyService']."' ";
    if($_REQUEST['eBuyAnyService'] == "Genie" || $_REQUEST['eBuyAnyService'] == "Anywhere")
    {
        $ssqlbuyanyservice = " AND eBuyAnyService = 'Genie' ";
    }
}
else {
    $ssqlbuyanyservice = " AND eBuyAnyService = '' ";
}

/* to fetch max iDisplayOrder from table for insert */

$serviceCatArr = json_decode(serviceCategories, true);
$getLangData = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active'");

$iDisplayOrder = $_POST['iDisplayOrder'] ?? $iDisplayOrder ?? '';
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : 0;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
$eBuyAnyService = isset($_POST['eBuyAnyService']) ? $_POST['eBuyAnyService'] : "";
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '-1';
$iServiceIdNew = isset($_POST['iServiceId'])?$_POST['iServiceId']:'';
$vStatusBarColor = isset($_POST['vStatusBarColor']) ? $_POST['vStatusBarColor'] : ''; 
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '1';
$oldDisplayOrder = isset($_POST['oldDisplayOrder']) ? $_POST['oldDisplayOrder'] : '';
$iCopyForOther = isset($_POST['iCopyForOther']) ? $_POST['iCopyForOther'] : 'off';
$eBuyAnyServiceReq = "";

if(isset($_REQUEST['eBuyAnyService']) && in_array($_REQUEST['eBuyAnyService'], ['Genie', 'Runner', 'Anywhere']) && $MODULES_OBJ->isEnableAnywhereDeliveryFeature())
{
    $eBuyAnyServiceReq = '?eBuyAnyService='.$_REQUEST['eBuyAnyService'];
    if($action == "Add")
    {
        $eBuyAnyService = $_REQUEST['eBuyAnyService'];
    }
}
if (isset($_POST['submit'])) { //form submit

    $vCodeLang = isset($_POST['vCode']) ? $_POST['vCode'] : 0;
    if ($action == "Add" && !$userObj->hasPermission('create-banner-store')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create banner.';
        header("Location:store_banner.php".$eBuyAnyServiceReq);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-banner-store')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update banner.';
        header("Location:store_banner.php".$eBuyAnyServiceReq);
        exit;
    }
    // if (!empty($id) && SITE_TYPE == 'Demo') {
    //     $_SESSION['success'] = 2;
    //     header("Location:store_banner.php".$eBuyAnyServiceReq);
    //     exit;
    // }
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:store_banner.php".$eBuyAnyServiceReq);
        exit;
    }
    //echo "<pre>";print_r($_REQUEST);exit;
    /*if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE iDisplayOrder = " . $i.$whereserviceId." AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $setOrder = $i - 1;
            if ($i == 1) {
                $setOrder = $nxtDispNo;
            }
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . $setOrder . " WHERE iDisplayOrder = " . $i.$whereserviceId." AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");
        }
    }*/
    $display_order_cuisine = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) as max_display_order, MIN(iDisplayOrder) as min_display_order FROM banners WHERE iServiceId = '$iServiceId' "); 
    $max_display_order = $display_order_cuisine[0]['max_display_order'];
    $min_display_order = $display_order_cuisine[0]['min_display_order'];

    if($action == "Add") {
        if($iDisplayOrder < $max_display_order) {
            $obj->sql_query("UPDATE banners SET iDisplayOrder = (iDisplayOrder + 1) WHERE iDisplayOrder >= '$iDisplayOrder' AND iServiceId = '$iServiceIdNew' ");
        }
    } else {
        if(($iDisplayOrder < $max_display_order && $iDisplayOrder > $oldDisplayOrder) || ($iDisplayOrder == $max_display_order)) {
            $obj->sql_query("UPDATE banners SET iDisplayOrder = (iDisplayOrder - 1) WHERE iDisplayOrder <= '$iDisplayOrder' AND iDisplayOrder > '$oldDisplayOrder' AND iServiceId = '$iServiceIdNew' ");
        } elseif ($iDisplayOrder < $max_display_order && $iDisplayOrder < $oldDisplayOrder) {
            $obj->sql_query("UPDATE banners SET iDisplayOrder = (iDisplayOrder + 1) WHERE iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' AND iServiceId = '$iServiceIdNew' ");
        }
    }
    
    $select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM " . $tbl_name . " WHERE vCode = '" . $vCodeLang . "'");
    $iUniqueId = isset($select_order[0]['iUniqueId']) ? $select_order[0]['iUniqueId'] : 0;
    $iUniqueId = $iUniqueId + 1; // Maximum order number
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $q = "INSERT INTO ";
            $where = '';
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iUniqueId` = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
                $iUniqueId = $id;
            }
            if(!empty($id) && !empty($vCodeLang)) {
                $sqlrecord = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode,eBuyAnyService FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
                $db_records = $obj->MySQLSelect($sqlrecord);
                if(empty($db_records)) {
                    $q = "INSERT INTO ";
                    $where = '';
                }
            }
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            if ($image_name != "") {
                // $filecheck = basename($_FILES['vImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
                // }

                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vKioskImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                if ($error) {
                    $flag_error = 1;
                    $var_msg = $error;
                }



                $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
                $image_width = $image_info[0];
                $image_height = $image_info[1];
                if ($error) {
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = $var_msg;
                    header("Location:store_banner.php".$eBuyAnyServiceReq);
                    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
                    $vImage = $img[0];
                }
            }
            $query = $q . " `" . $tbl_name . "` SET     
                    `vTitle` = '" . $vTitle . "',
                    `vImage` = '" . $vImage . "',
                    `eStatus` = '" . $eStatus . "',
                    `iUniqueId` = '" . $iUniqueId . "',
                    `iDisplayOrder` = '" . $iDisplayOrder . "',
                    `vCode` = '" . $vCodeLang . "',
                    `iLocationid` = '" . $iLocationId . "',
                    `iServiceId`= '".$iServiceIdNew."',
                    `eBuyAnyService` = '" . $eBuyAnyService . "',
                    `vStatusBarColor` = '" . $vStatusBarColor . "'"
                    . $where;
            $obj->sql_query($query);
            if ($id != '') {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            }
        }
        if ($iCopyForOther == "on" && $action == "Add") {
            foreach ($getLangData as $lk => $lvalue) {
                if ($vCodeLang != $lvalue['vCode']) {
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

                    if(empty($where)) {
                        $obj->MySQLQueryPerform($tbl_name, $Data_banner, "insert");
                    } else {
                        $obj->MySQLQueryPerform($tbl_name, $Data_banner, "update", $where);
                    }
                }
            }
        }
        header("Location:store_banner.php".$eBuyAnyServiceReq);
        exit();
    }
}



// for Edit
if ($action == 'Edit') {
    //$vCodeLang = !empty($vCodeLang) ? $vCodeLang : $default_lang;
    $sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode,eBuyAnyService,iLocationid,vStatusBarColor FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '".$vCodeLang."'";
  
    $db_data = $obj->MySQLSelect($sql);
    $iUniqueId = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            //$vTitle           = 'vTitle_'.$value['vCode'];
            $vTitle = $value['vTitle'];
            $eStatus = $value['eStatus'];
            $vImage = $value['vImage'];
            $iDisplayOrder = $value['iDisplayOrder'];
            $iServiceId = $value['iServiceId'];
            $vCodeLang = $value['vCode'];
            $iLocationId = $value['iLocationid'];
            $iServiceIdNew = $value['iServiceId'];
            $eBuyAnyService = $value['eBuyAnyService'];
            $vStatusBarColor = $value['vStatusBarColor'];
        }
    }
}
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_".$default_lang." as servicename,eStatus FROM service_categories WHERE iServiceId IN (".$serviceIds.") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);

$sql_location = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'Banner' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sql_location);
?>
<!DOCTYPE html>
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Banner <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />   
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

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
                            <a href="store_banner.php<?= $eBuyAnyServiceReq ?>">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />  
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 0 && $_REQUEST['var_msg'] != "") { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <? echo $_REQUEST['var_msg']; ?>
                                </div><br/>
                            <?php } ?>
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } ?>
                            <?php if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" action="" enctype="multipart/form-data"  id="_store_banner_form">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                                <input type="hidden" name="eBuyAnyService" value="<?= $eBuyAnyService ?>">
                                <!-- <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select Service</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select  class="form-control" name = 'iServiceId'  id= 'iServiceId' >
                                            <option value="0">General</option>
                                            <?php for ($s = 0; $s < scount($serviceCatArr); $s++) { ?>
                                                <option <?php if ($iServiceId == $serviceCatArr[$s]['iServiceId']) { ?>selected=""<?php } ?> value = "<?= $serviceCatArr[$s]['iServiceId']; ?>"><?= $serviceCatArr[$s]['vServiceName']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div> -->
                                <!-- <div class="row">
                                    <?php if ($action == "Add") { ?>
                                    <div class="col-lg-12">
                                        <label>Select Language</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select  class="form-control" name = 'vCode'  id= 'vCode' onchange="bannerdata(this.value)">
                                            <?php for ($l = 0; $l < scount($getLangData); $l++) { ?>
                                                <option <?php if ($vCodeLang == $getLangData[$l]['vCode']) { ?>selected=""<?php } ?> value = "<?= $getLangData[$l]['vCode']; ?>"><?= $getLangData[$l]['vTitle']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <? } else { ?>
                                    <div class="col-lg-12">
                                        <label>Language: <?= $vCodeLang ?></label>
                                    </div>
                                    <input type="hidden" name="vCode" value="<?= $vCodeLang ?>">
                                    <? } ?>
                                </div> -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select Language</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select  class="form-control" name = 'vCode'  id= 'vCode' onchange="bannerdata(this.value)">
                                            <?php for ($l = 0; $l < scount($getLangData); $l++) { ?>
                                                <option <?php if ($vCodeLang == $getLangData[$l]['vCode']) { ?>selected=""<?php } ?> value = "<?= $getLangData[$l]['vCode']; ?>"><?= $getLangData[$l]['vTitle']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="bannerlang">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image<?= ($vImage == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($vImage != '') { ?>
                                            <!-- <img src="<?= $tconfig['tsite_upload_images'] . $vImage; ?>" style="width:200px;height:100px;"> -->

                                            <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=300&src='.$tconfig['tsite_upload_images'] . $vImage;   ?>">

                                            <input type="file" class="form-control" name="vImage" id="vImage" value="<?= $vImage; ?>"/>
                                        <?php } else { ?>
                                            <input type="file" class="form-control" name="vImage" id="vImage" value="<?= $vImage; ?>" required/>
                                        <?php } ?>
                                        <span class="notes">[Note: Recommended dimension for banner image is 2880 * 1620. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="vTitle" id="vTitle" value="<?= $vTitle ?>" class="form-control" />
                                    </div>
                                </div>
                                </div>
                                <?php if( scount($allservice_cat_data)<=1 ){?>
                                    <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId'];?>" onchange="changeDisplayOrder(this.value)"/>


                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Service Category<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select class="form-control" name = 'iServiceId' id="iServiceId" required onchange="changeDisplayOrder(this.value)">
                                               <option value="">Select</option>
                                               <?php /*<option value="0" <?= $iServiceIdNew == 0 ? "selected" : "" ?>>General</option>*/ ?>
                                               <? for($i=0;$i<scount($service_cat_list);$i++){ ?>
                                               <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?if($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?> selected <?php } else if($iServiceIdNew==$service_cat_list[$i]['iServiceId']){?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                               <? } ?>
                                            </select>
                                         </div>
                                    </div>
                                <?php } ?>
                                <? if($MODULES_OBJ->isEnableLocationwiseBanner()) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select Location <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Select the location in which you would like to appear this banner. For example banner to appear for any specific city or state or may be for whole country. You can define these locations from "Manage Locations >> Geo Fence Location" section'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? //echo "<pre>"; print_R($userObj->locations); ?>
                                        <select class="form-control" name = 'iLocationId' id="iLocationId" required="">
                                            <option value="">Select Location</option>
                                            <option value="-1" <? if ($iLocationId == "-1") { ?>selected<? } ?>>All</option>
                                            <?php
                                            foreach ($db_location as $i => $row) {
                                                //if (scount($userObj->locations) > 0 && !in_array($row['iLocationId'], $userObj->locations)) {
                                                //    continue;
                                                //}
                                                ?>
                                                <option value = "<?= $row['iLocationId'] ?>" <? if ($iLocationId == $row['iLocationId']) { ?>selected<? } ?>><?= $row['vLocationName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location</a>
                                    </div>
                                </div>
                                <? } ?>
                                <?php if($MODULES_OBJ->isEnableAppHomeScreenLayoutV2() && !$MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>App Status Bar Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" id="StatusBarColor" class="form-control" value="<?= $vStatusBarColor ?>" />
                                        <input type="hidden" name="vStatusBarColor" id="vStatusBarColor" value="<?= $vStatusBarColor ?>">
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                        </div>
                                    </div>
                                </div>
                                <?php /*<div class="row">
                                    <div class="col-lg-12">
                                        <label>Order</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <span id="orderdiv">
                                        <?php
                                        $temp = 1;
                                        $dataArray = array();
                                        $query1 = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE 1 $whereserviceId AND vCode = '$vCodeLang' $ssqlbuyanyservice ORDER BY iDisplayOrder";
                                        $data_order = $obj->MySQLSelect($query1);
                                        foreach ($data_order as $value) {
                                            $dataArray[] = $value['iDisplayOrder'];
                                            $temp = $iDisplayOrder;
                                        }
                                        ?>
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp ?>">
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php foreach ($dataArray as $arr): ?>
                                                <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >
                                                    -- <?= $arr ?> --
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if ($action == "Add") { ?>
                                                <option value="<?= $temp; ?>" >
                                                    -- <?= $temp ?> --
                                                </option>
                                            <?php } ?>
                                        </select>
                                        </span>
                                    </div>
                                </div>*/ ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Display Order <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6" id="showDisplayOrder001">
                                    </div>
                                    <input type="hidden" name="oldDisplayOrder" value="<?= $iDisplayOrder ?>">
                                </div>
                                <?php if ($action == "Add" && scount($getLangData) > 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label> Do you want to copy same banner for other languages also?</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="Yes"
                                                 data-off-label="No">
                                                <input type="checkbox" name="iCopyForOther"/>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <?php if (($action == 'Edit' && $userObj->hasPermission('edit-banner-store')) || ($action == 'Add' && $userObj->hasPermission('create-banner-store'))) { ?>
                                        <div class="col-lg-12">
                                            <input type="submit" class="save btn-info" name="submit" id="submit" value="<?= $action; ?> Banner">
                                            <a href="store_banner.php<?= $eBuyAnyServiceReq ?>" class="btn btn-default back_link">Cancel</a>
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
            $(document).ready(function () {
                $('#iServiceId').trigger('change');
            });

            function bannerdata(val) {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>banner_lang.php',
                    'AJAX_DATA': {vCode: val, id: '<?= $_REQUEST['id']; ?>',order:'Yes',eBuyAnyService:'<?= $eBuyAnyService ?>',serviceid:'Yes'},
                    'REQUEST_DATA_TYPE': 'html'
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var dataHtml2 = response.result;
                        if (dataHtml2 != "") {
                            $('#orderdiv').html(dataHtml2);
                            //$('.bannerlang').html(dataHtml2);
                        }
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }
            
            $("#StatusBarColor").on("input", function(){
                var color = $(this).val();
                $('#vStatusBarColor').val(color);
            });

            function changeDisplayOrder(iServiceId) {

                console.log('0000000');
                console.log(iServiceId);
                console.log('1111111');

                var vCodeLang = $('#vCode :selected').val();
                var vCodeLang =  $('[name="vCode"]').val();

                if(vCodeLang == ""){
                    vCodeLang = "<?=$vCodeLang?>";
                }
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_display_order.php',
                    'AJAX_DATA': {iServiceId: iServiceId, page: 'store_banner', method: '<?= $action ?>', iDisplayOrder: '<?= $iDisplayOrder ?>', vCode: vCodeLang},
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $("#hiddenParent001").hide();
                        $("#showDisplayOrder001").html('');
                        $("#showDisplayOrder001").html(data);
                    }
                    else {
                        console.log(response.result);
                    }
                });

            }


        var errormessage;
        if ($('#_store_banner_form').length !== 0) {
            $('#_store_banner_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImage: {extension: imageUploadingExtenstionjsrule},
                    iServiceId: {required: true},
                    iLocationId: {required: true},
                },
                messages: {
                    vImage:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
            });
        }


        </script>


        <script>

            var ALLSERVICE_CAT_DATA = "<?php echo scount($allservice_cat_data) ?>";
            if(ALLSERVICE_CAT_DATA <= 1) {
                var ISERVICEID = "<?php echo $service_cat_list[0]['iServiceId'] ?>";
                changeDisplayOrder(ISERVICEID);
            }
        </script>
    </body>
    <!-- END BODY-->    
</html>