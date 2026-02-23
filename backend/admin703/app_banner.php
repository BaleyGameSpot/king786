<?php
include_once '../common.php';

if (!$userObj->hasPermission(['view-banner-genie-delivery', 'view-banner-runner-delivery']) && !(isset($_REQUEST['eFor']) && in_array($_REQUEST['eFor'], ["AppHomeScreen", "Promotion"]))) {
    $userObj->redirect();
}

$eBuyAnyService = isset($_REQUEST['eBuyAnyService']) ? $_REQUEST['eBuyAnyService'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '0';
$eFor = $_REQUEST['eFor'];

$redirect = "";
if($eFor == "AppHomeScreen") {
    $redirect .= "&eBuyAnyService=" . $eBuyAnyService;
} elseif ($eFor == "Promotion") {
    $redirect .= "&iVehicleCategoryId=" . $iVehicleCategoryId;
}
if(isset($_POST['submitbtn'])) {
    $select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM banners WHERE eFor = '" . $eFor . "'");
    $iUniqueId = isset($select_order[0]['iUniqueId']) ? $select_order[0]['iUniqueId'] : 0;
    $iUniqueId = $iUniqueId + 1;

    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    if ($image_name != "") {
        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
        }

        if ($flag_error == 1) {
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $var_msg;
            header("Location:app_banner.php?eFor=" . $eFor . $redirect);
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
            $vImage = $img[0];

            
            $Data_update['vImage'] = $vImage;
            $Data_update['eStatus'] = 'Active';
            $Data_update['eBuyAnyService'] = $eBuyAnyService;
            $Data_update['iVehicleCategoryId'] = $iVehicleCategoryId;
            $Data_update['eFor'] = $eFor;
            $Data_update['eType'] = $eFor;
            $Data_update['vCode'] = $_POST['vCode'];
            if(empty($_POST['iUniqueId'])) {
                $Data_update['iUniqueId'] = $iUniqueId;
                $obj->MySQLQueryPerform("banners", $Data_update, "insert");
            } else {
                $where_banner = " iUniqueId = '" . $_POST['iUniqueId'] . "' AND vCode = '" . $_POST['vCode'] . "'";
                $obj->MySQLQueryPerform("banners", $Data_update, "update", $where_banner);
            }
        }
    }

    header("Location:app_banner.php?eFor=" . $eFor . $redirect);
    exit;
}

if(isset($_POST['savePromoBanner'])) {
    $ePromoteBanner = isset($_POST['ePromoteBanner']) ? $_POST['ePromoteBanner'] : 'No';
    if($ePromoteBanner == "Yes") {
        $obj->sql_query("UPDATE " . getVehicleCategoryTblName() . " SET ePromoteBanner = 'No' WHERE iVehicleCategoryId != '$iVehicleCategoryId'");
    }

    $obj->sql_query("UPDATE " . getVehicleCategoryTblName() . " SET ePromoteBanner = '$ePromoteBanner' WHERE iVehicleCategoryId = '$iVehicleCategoryId'");

    header("Location:app_banner.php?eFor=" . $eFor . $redirect);
    exit;
}

$all_lang_data = $obj->MySQLSelect("SELECT vCode FROM language_master ORDER BY iDispOrder ASC");
$display = '';

$ssql = "";
if($eFor == "AppHomeScreen") {
    $ssql .= " AND eBuyAnyService = '$eBuyAnyService' ";
    $page_heading = $langage_lbl_admin['LBL_DELIVERY_RUNNER_ADMIN_TXT'];
    if($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
        $page_heading = $langage_lbl_admin['LBL_DELIVERY_GENIE_ADMIN_TXT'];
    } 
} elseif ($eFor == "Promotion") {
    if(empty($iVehicleCategoryId)) {
        $display = 'style="display: none;"';
    }

    $ssql .= " AND iVehicleCategoryId = '$iVehicleCategoryId' ";
    $page_heading = 'Promotional';

    $ePromoteBannerStatus = '';
    $promote_cat_data = $obj->MySQLSelect("SELECT ePromoteBanner FROM " . getVehicleCategoryTblName() . "  WHERE iVehicleCategoryId = '$iVehicleCategoryId' AND ePromoteBanner = 'Yes' ");
    if(!empty($promote_cat_data) && scount($promote_cat_data) > 0) {
        $ePromoteBannerStatus = 'checked';
    }

    $master_service_categories = $obj->MySQLSelect("SELECT JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vCategoryName, eType FROM $master_service_category_tbl WHERE 1 = 1 AND eType IN ('Ride', 'Deliver', 'DeliverAll', 'UberX') AND eStatus = 'Active'");

    $mServiceCategoryArr = array();
    $not_sql = " AND iVehicleCategoryId != 297";
    foreach ($master_service_categories as $mServiceCategory) {
        $subquery = getMasterServiceCategoryQuery($mServiceCategory['eType']);

        $parent_id_sql = " AND iParentId='0' ";

        if($mServiceCategory['eType'] == "Ride") {
            $subquery .= " AND eForMedicalService = 'No' ";
        } elseif ($mServiceCategory['eType'] == "UberX" && $MODULES_OBJ->isEnableMedicalServices('Yes')) {
            $subquery .= " AND iVehicleCategoryId NOT IN (3) ";
        } elseif ($mServiceCategory['eType'] == "DeliverAll" && $MODULES_OBJ->isEnableMedicalServices('Yes')) {
            $subquery .= " AND iServiceId NOT IN (5, 11) ";
        }
        $category_data = $obj->MySQLSelect("SELECT iVehicleCategoryId, vCategory_" . $default_lang . " as vCategory, ePromoteBanner FROM " . getVehicleCategoryTblName() . "  WHERE  1 = 1 $parent_id_sql AND eStatus = 'Active' $subquery $not_sql ORDER BY iDisplayOrder ASC");

        $mServiceCategoryArr[$mServiceCategory['vCategoryName']] = $category_data;
    }
    // echo "<pre>"; print_r($mServiceCategoryArr); exit;
}
$app_banners = $obj->MySQLSelect("SELECT vCode,vImage,iUniqueId FROM banners WHERE eFor = '" . $eFor . "' $ssql");

$app_banners_arr = array();
foreach ($app_banners as $banner) {
    $app_banners_arr[$banner['vCode']] = $banner;
}

?>
<!DOCTYPE html>
<!--[if IE 8]> 
<html lang="en" class="ie8">
<![endif]-->
<!--[if IE 9]> 
<html lang="en" class="ie9">
<![endif]-->
<!--[if !IE]><!--> 
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title>Admin | Banners</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once 'global_files.php'; ?>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        .app-home-screen-service > label {
            font-size: 14px;
            display: inline-block;
            margin-right: 10px;
        }

        .app-home-screen-service > span {
            display: inline-block;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53" >
    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <?php include_once 'header.php'; ?>
        <?php include_once 'left_menu.php'; ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>App Home Screen Banner (<?= $page_heading ?>)</h2>
                    </div>
                </div>
                <hr />
                <?php include 'valid_msg.php'; ?>

                <?php if($eFor == "Promotion") { ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-lg-12 app-home-screen-service">
                            
                            <label>Select Service: </label>
                            <span>
                                <select class="form-control" name="iVehicleCategoryId" id="iVehicleCategoryId">
                                    <!-- <option value="">Select</option> -->
                                    <?php foreach ($mServiceCategoryArr as $key => $serviceArr) { ?>
                                        <optgroup label="<?= $key ?>">
                                        <?php foreach ($serviceArr as $value) { ?>
                                            <option value="<?= $value['iVehicleCategoryId'] ?>" <?= $value['iVehicleCategoryId'] == $iVehicleCategoryId ? 'selected' : '' ?>><?= $value['vCategory'] ?></option>
                                        <?php } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                            </span>

                            <label style="margin-left: 30px">Status: </label>
                            <span>
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" name="ePromoteBanner" value="Yes" <?= $ePromoteBannerStatus ?> />
                                </div>
                            </span>
                            <button type="submit" class="btn btn-default" name="savePromoBanner" style="margin-left: 30px;">Save</button>
                            
                        </div>
                    </div>
                </form>
                <div class="admin-notes" style="margin: 15px 0 0 0"><strong>Note: </strong>This service will be shown as banner in app home screen. Enabling this will disable banner for all other services if enabled.</div>
                <?php } ?>

                <div class="table-list" <?= $display ?>>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th width="20%">Image</th>
                                                    <th>Language</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($all_lang_data as $lang) { ?>
                                                    <tr>
                                                        <td>
                                                        <?php if(isset($app_banners_arr[$lang['vCode']])) { ?>
                                                            <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=70&src='.$tconfig['tsite_upload_images'] . $app_banners_arr[$lang['vCode']]['vImage']; ?>" >
                                                        <?php } ?>
                                                        </td>
                                                        <td><?= $lang['vCode'] ?></td>
                                                        <?php if ($userObj->hasPermission(['edit-banner-genie-delivery', 'edit-banner-runner-delivery']) || (isset($_REQUEST['eFor']) && in_array($_REQUEST['eFor'], ["AppHomeScreen", "Promotion"]))) { ?>
                                                            <td width="10%" align="center">
                                                                <button class="btn btn-primary" data-toggle="modal" data-target="#upload_banner_<?= $lang['vCode'] ?>">
                                                                    <i class="icon-pencil icon-white"></i> Edit
                                                                </button>
                                                                

                                                                <div class="modal fade" id="upload_banner_<?= $lang['vCode'] ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                    <form method="POST" action="" enctype='multipart/form-data'>
                                                                        <input type="hidden" name="iUniqueId" value="<?= $app_banners_arr[$lang['vCode']]['iUniqueId'] ?>" />
                                                                        <input type="hidden" name="vCode" value="<?= $lang['vCode'] ?>" />
                                                                        <div class="modal-dialog" role="document">
                                                                            <div class="modal-content" style="text-align: left;">
                                                                                <div class="modal-header">
                                                                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                    <h4 class="modal-title">Banner (<?= $lang['vCode'] ?>)</h4>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="row">
                                                                                        <div class="col-lg-12">
                                                                                            <?php if(isset($app_banners_arr[$lang['vCode']])) { ?>
                                                                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=300&src='.$tconfig['tsite_upload_images'] . $app_banners_arr[$lang['vCode']]['vImage']; ?>" >
                                                                                            <?php } ?>
                                                                                        </div>
                                                                                        <div class="clearfix">&nbsp;</div>
                                                                                        <div class="col-lg-12">
                                                                                            <input type="file" class="form-control FilUploader" name="vImage" id="vImage" accept=".png,.jpg,.jpeg,.gif" required>
                                                                                        </div>
                                                                                        <br />
                                                                                        <div class="col-lg-12">
                                                                                            <?php if($eFor == "AppHomeScreen") { ?>
                                                                                            <span>Note: Recommended dimension for banner image is 2880 X 1305. <br> <?= IMAGE_INSTRUCTION_NOTES ?></span>
                                                                                            <?php } else if ($eFor == "Promotion") { ?>
                                                                                            <?php if($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) { ?>
                                                                                            <span>Note: Recommended dimension for banner image is 1200px X 510px. <br> <?= IMAGE_INSTRUCTION_NOTES ?></span>
                                                                                            <?php } else { ?>
                                                                                            <span>Note: Recommended dimension for banner image is 2880px X 990px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </span>
                                                                                            <?php } ?>
                                                                                            
                                                                                            <?php } ?>
                                                                                        </div>
                                                                                        <br />

                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="submit" name="submitbtn" value="1" class="btn btn-primary">Save changes</button>
                                                                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--TABLE-END-->
                    </div>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
    <?php include_once 'footer.php'; ?>
    <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
    <script type="text/javascript">
        $('#iVehicleCategoryId').change(function() {
            var curr_val = $(this).val();
            window.location.href = '<?= $tconfig['tsite_url_main_admin'] ?>app_banner.php?iVehicleCategoryId=' + curr_val + '&eFor=Promotion';
        });
    </script>
</body>
    <!-- END BODY-->
</html>