<?php
include_once('../common.php');
// For Languages
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'food_menu';
$script = 'FoodMenu';
// set all variables with either post (when submit) either blank (when insert)
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';
$vMenu_store = array();
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vMenu_' . $db_master[$i]['vCode'];
        array_push($vMenu_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-item-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Item Category.';
        header("Location:". $LOCATION_FILE_ARRAY['FOOD_MENU.PHP']);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-item-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Item Category.';
        header("Location:". $LOCATION_FILE_ARRAY['FOOD_MENU.PHP']);
        exit;
    }
    if (!empty($id) && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:". $LOCATION_FILE_ARRAY['FOOD_MENU.PHP']."?id=" . $id);
        exit;
    }
    if ($id != "") {
        $sql = "SELECT iDisplayOrder FROM `food_menu` where iFoodMenuId = '$id'";
        $displayOld = $obj->MySQLSelect($sql);
        $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
        if ($oldDisplayOrder > $iDisplayOrder) {
            $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder + 1;
                for ($i = 0; $i < scount($db_orders); $i++) {
                    $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '" . $db_orders[$i]['iFoodMenuId'] . "'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        } else if ($oldDisplayOrder < $iDisplayOrder) {
            $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $oldDisplayOrder;
                for ($i = 0; $i < scount($db_orders); $i++) {
                    $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '" . $db_orders[$i]['iFoodMenuId'] . "'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        }
    } else {
        $sql = "SELECT * FROM `food_menu` WHERE iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);
        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < scount($db_orders); $i++) {
                $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '" . $db_orders[$i]['iFoodMenuId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }
    for ($i = 0; $i < scount($vMenu_store); $i++) {
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iFoodMenuId` = '" . $id . "'";
        }
        $eStatus_query = '';
        if ($action == "Add") {
            $eStatus_query = "  `eStatus` = '" . $eStatus . "',";
        }
        $image_object = $_FILES['vImage']['tmp_name'];
        $image_name = $_FILES['vImage']['name'];
        $image_update = "";
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
            $error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
            $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
            $image_width = $image_info[0];
            $image_height = $image_info[1];
           if($error){
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $error;
                header("Location:food_menu.php". (($sid != "") ? "?" . $sid : ""));
                exit;
            } else {
                $oldImage = $_POST['oldImage'];
                $check_file = $tconfig["tsite_upload_images_menu_category_path"] . '/' . $oldImage;
                if ($oldImage != '' && file_exists($check_file)) {
                    @unlink($tconfig["tsite_upload_images_menu_category_path"] . '/' . $oldImage);
                }
                $Photo_Gallery_folder = $tconfig["tsite_upload_images_menu_category_path"] . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                    chmod($Photo_Gallery_folder, 0777);
                }
                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
                $vImage = $img[0];
                $image_update = "`vImage` = '" . $vImage . "',";
            }
        }
        $vValue = 'vMenu_' . $db_master[$i]['vCode'];
        //$vValue_desc = 'vMenuDesc_'.$db_master[$i]['vCode'];
        $query = $q . " `" . $tbl_name . "` SET
        `iCompanyId` = '" . $iCompanyId . "',
        `iDisplayOrder` = '" . $iDisplayOrder . "',
        `iServiceId` = '" . $iServiceId . "',
        $eStatus_query
        $image_update
        `" . $vValue . "` = '" . $_POST[$vMenu_store[$i]] . "'" . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("Location:" . $backlink);
    exit;
}
// for Edit
$EditServiceId = 0;
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iFoodMenuId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vMenu_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $iCompanyId = $value['iCompanyId'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $eStatus = $value['eStatus'];
                $iFoodMenuId = $value['iFoodMenuId'];
                $oldImage = $value['vImage'];
                $arrLang[$vValue] = $$vValue;
                $EditServiceId = $value['iServiceId'];
            }
        }
    }
}
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
    $fsql = " IF(c.iServiceIdMulti != '', c.iServiceIdMulti, c.iServiceId) as iServiceId ";
} else {
    $fsql = " c.iServiceId";
}
$qry_cat = "SELECT $fsql FROM `food_menu` AS f LEFT JOIN company AS c ON c.iCompanyId = f.iCompanyId WHERE c.iCompanyId = '" . $iCompanyId . "' and  c.eStatus!='Deleted'";
$db_chk = $obj->MySQLSelect($qry_cat);
if(!empty($db_chk)){
    $EditServiceIdNew = $db_chk[0]['iServiceId'];
}
$catdata = ServiceData;
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_" . $default_lang . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus !='Deleted'";
$service_cat_list = $obj->MySQLSelect($service_category);
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
    /* $ssql = " AND iServiceId IN(".$enablesevicescategory.")";
     $enablesevicescategory = str_replace(",", "|", $enablesevicescategory);
     $ssql .= " OR iServiceIdMulti REGEXP '(^|,)(" . $enablesevicescategory . ")(,|$)') ";*/
    $ssql = " AND iServiceId IN (" . $serviceIds . ")";
    $serviceIds = str_replace(",", "|", $serviceIds);
    $ssql .= " OR iServiceIdMulti REGEXP ('(^|,)(" . $serviceIds . ")(,|$)') ";
} else {
    $ssql = " AND iServiceId IN (" . $enablesevicescategory . ")";
}
$sql = "SELECT * FROM `company` where eStatus !='Deleted' AND eBuyAnyService = 'No' $ssql ORDER BY `vCompany`";
$db_company = $obj->MySQLSelect($sql);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Item Category <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <? include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $action; ?> Item Category</h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <form id="food_category_form" name="food_category_form" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="iFoodMenuId" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="<?= $LOCATION_FILE_ARRAY['FOOD_MENU.PHP']; ?>"/>
                        <?php
                        if ($action == 'Add') {
                            if (scount($allservice_cat_data) <= 1) {
                                ?>
                                <input name="iServiceId" type="hidden" class="create-account-input"
                                       value="<?php echo $service_cat_list[0]['iServiceId']; ?>"/>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Service Type
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <select class="form-control" name='iServiceId' id="iServiceId" required
                                                onchange="changeserviceCategory(this.value)">
                                            <option value="">Select</option>
                                            <?php //foreach($db_company as $dbcm) { ?>
                                            <? for ($i = 0; $i < scount($service_cat_list); $i++) { ?>
                                                <option value="<?= $service_cat_list[$i]['iServiceId'] ?>"><?= $service_cat_list[$i]['servicename'] ?></option>
                                            <? } ?>
                                            <?php //} ?>
                                        </select>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select name="iCompanyId" class="form-control" id="iCompanyId" required
                                        onchange="changeDisplayOrderCompany(this.value, '<?php echo $id; ?>')" <?php if ($action == 'Edit') { ?> readonly style="pointer-events:none;"<?php } ?> >
                                    <option value="">
                                        Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></option>
                                    <?php foreach ($db_company as $dbc) { ?>
                                        <option value="<?php echo $dbc['iCompanyId']; ?>"
                                                <? if ($dbc['iCompanyId'] == $iCompanyId) { ?>selected<? } ?>><?php echo clearName($dbc['vCompany']);
                                            if ($dbc['vEmail'] != '') { ?> - ( <?php echo clearEmail($dbc['vEmail']); ?> ) <? } else {
                                                echo '--';
                                            } ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php if (scount($allservice_cat_data) > 1 && $MODULES_OBJ->isEnableStoreMultiServiceCategories() && $action == "Edit") {
                            $EditServiceIdNewArr = explode(",", $EditServiceIdNew);
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Service Type
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" name="iServiceId" id="iServiceId" required="">
                                        <option value="">Select</option>
                                        <?php
                                        for ($i = 0; $i < scount($service_cat_list); $i++) {
                                            if (in_array($service_cat_list[$i]['iServiceId'], $EditServiceIdNewArr)) {
                                                ?>
                                                <option value="<?= $service_cat_list[$i]['iServiceId'] ?>"
                                                        <?php if ($EditServiceId == $service_cat_list[$i]['iServiceId']) { ?>selected<?php } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Menu Category
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="vMenu_Default" name="vMenu_Default"
                                           value="<?= isset($arrLang['vMenu_' . $default_lang]) ? $arrLang['vMenu_' . $default_lang] : '' ; ?>"
                                           data-originalvalue="<?= isset($arrLang['vMenu_' . $default_lang]) ? $arrLang['vMenu_' . $default_lang] : ''; ?>"
                                           readonly="readonly" <?php if ($id == "") { ?> onclick="editMenuCategory('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editMenuCategory('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="modal fade" id="menu_cat_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span>
                                                Menu Category
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vMenu_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vMenu_' . $vCode;
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
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
                                                        <label>Menu Category (<?= $vTitle; ?>
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
                                                                            onClick="getAllLanguageCode('vMenu_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vMenu_', '<?= $default_lang ?>');">
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
                                                        onclick="saveMenuCategory()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vMenu_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Menu Category
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="vMenu_<?= $default_lang ?>"
                                           name="vMenu_<?= $default_lang ?>"
                                           value="<?= $arrLang['vMenu_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                            <div class="row" id="kiosk_menu_category_img" style="display: none;">
                                <div class="col-md-12 col-sm-12">
                                    <label>Menu Category Image</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="imageupload">
                                        <div class="file-tab">
                                                <span id="single_img001">
                                                    <?php
                                                    $imgpth = $tconfig["tsite_upload_images_menu_category_path"] . '/' . $oldImage;
                                                    $imgUrl = $tconfig["tsite_upload_images_menu_category"] . '/' . $oldImage;
                                                    if ($oldImage != "" && file_exists($imgpth)) {
                                                        ?>
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=250&h=250&src=' . $imgUrl; ?>"
                                                             alt="Image preview" class="thumbnail"
                                                             style="max-width: 250px; max-height: 250px">
                                                    <?php } ?>
                                                </span>
                                            <div>
                                                <input type="hidden" name="oldImage" value="<?= trim($oldImage); ?>">
                                                <!-- <input name="vImage" onchange="preview_mainImg(event);" type="file" class="form-control" accept="image/*" > -->
                                                <input name="vImage" id="vImage" onchange="preview_mainImg(event);" type="file" class="form-control" >
                                                <span class="notes">[Note: This is only applicable for Kiosk order Apps only.]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Display Order
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6" id="showDisplayOrder001">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-item-categories')) || ($action == 'Add' && $userObj->hasPermission('create-item-categories'))) { ?>
                                    <input type="submit" class="btn btn-default" name="submit" id="submit"
                                           value="<?= $action; ?> Item Category">
                                    <input type="reset" id="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="<?= $LOCATION_FILE_ARRAY['FOOD_MENU.PHP']; ?>" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    let resetDisplayOrder = () => {
        var str = "<select name='iDisplayOrder' id='iDisplayOrder' class='form-control'><option>1</option></select>"
        $("#showDisplayOrder001").html('');
        $("#showDisplayOrder001").html(str);
        $("#single_img001").html('');
        return "done";
    }

    let resetFrom = () => {
        var data = resetDisplayOrder();
    }

    var reset = document.getElementById("reset");
    reset.addEventListener("click", resetFrom)


    function changeDisplayOrderCompany(companyId, foodId) {

        if (companyId != 'undefined' && companyId != '') {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_display_order.php',
                'AJAX_DATA': {iCompanyId: companyId, method: 'getOrder', iFoodMenuId: foodId, iParentId: '0'},
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#hiddenParent001").hide();
                    $("#showDisplayOrder001").html('');
                    $("#showDisplayOrder001").html(data);
                } else {
                    console.log(response.result);
                }
            });
        } else {
            resetDisplayOrder();
        }
    }

    $(document).ready(function () {
        $('#imageIcon').hide();
        changeDisplayOrderCompany('<?php echo $iCompanyId; ?>', '<?php echo $id; ?>');
        var action = "<?= $action ?>";
        if (action == 'Add') {
            var iServiceIdNew = $("#iServiceId").val();
        } else {
            var iServiceIdNew = "<?= $EditServiceIdNew ?>";
        }
        var servicecounts = '<? echo scount($service_cat_list) ?>';
        if (servicecounts > '1') {
            changeserviceCategory(iServiceIdNew);
        }

        if ($('#kiosk_menu_category_img').length > 0) {
            $('[name="vImage"]').val("");
            if (iServiceIdNew == 1) {
                $('#kiosk_menu_category_img').show();
            } else {
                $('#kiosk_menu_category_img').hide();
            }
        }
    });

    function changeserviceCategory(iServiceId) {

        var iCompanyId = '<?php echo $iCompanyId; ?>';

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_restorantcat_filter.php',
            'AJAX_DATA': {iServiceIdNew: iServiceId, iCompanyId: iCompanyId},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#iCompanyId").html('');
                $("#iCompanyId").html(data);
            } else {
                //console.log(response.result);
            }
        });

        if ($('#kiosk_menu_category_img').length > 0) {
            $('[name="vImage"]').val("");
            //if(iServiceId == 1) {
            if ($("#iServiceId").val() == 1) {
                $('#kiosk_menu_category_img').show();
            } else {
                $('#kiosk_menu_category_img').hide();
            }
        }
    }


    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "<?= $LOCATION_FILE_ARRAY['FOOD_MENU.PHP']; ?>";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
        var date = new Date();
        var currentMonth = date.getMonth();
        var currentDate = date.getDate();
        var currentYear = date.getFullYear();
    });

    function preview_mainImg(event) {
        $("#single_img001").html('');
        $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
        $(".changeImg001").text('Change');
        $(".remove_main").show();

        $(".thumbnail").on( "error", function() {
            $( this ).attr( "src", "../webimages/upload/ItemCategory/20240417173557371.png" );
        });
    }

    function editMenuCategory(action) {
        $('#modal_action').html(action);
        $('#menu_cat_Modal').modal('show');
    }

    function saveMenuCategory() {
        if ($('#vMenu_<?= $default_lang ?>').val() == "") {
            $('#vMenu_<?= $default_lang ?>_error').show();
            $('#vMenu_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vMenu_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vMenu_Default').val($('#vMenu_<?= $default_lang ?>').val());
        $('#vMenu_Default').closest('.row').removeClass('has-error');
        $('#vMenu_Default-error').remove();
        $('#menu_cat_Modal').modal('hide');
    }
</script>
</body>
<!-- END BODY-->
</html>
