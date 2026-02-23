<?php
include_once('../common.php');
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = $_REQUEST['success'] ?? $_SESSION['success'] ?? '';
$var_msg = $_REQUEST['var_msg'] ?? $_SESSION['var_msg'] ?? '';
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iServiceIdNew = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';
$tbl_name = 'cuisine';
$script = 'Cuisine';
$vTitle_store = array();
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'cuisineName_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}
// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '1';
$oldDisplayOrder = isset($_POST['oldDisplayOrder']) ? $_POST['oldDisplayOrder'] : '';
$vBgColor = isset($_POST['vBgColor']) ? $_POST['vBgColor'] : '#ffffff';
$vTextColor = isset($_POST['vTextColor']) ? $_POST['vTextColor'] : '#ffffff';
$vBorderColor = isset($_POST['vBorderColor']) ? $_POST['vBorderColor'] : '#ffffff';
$eDefaultType = isset($_POST['eDefaultType']) ? $_POST['eDefaultType'] : 'No';
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-item-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Item Type.';
        header("Location:cuisine.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-item-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Item Type.';
        header("Location:cuisine.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:cuisine_action.php?id=" . $id . '&success=2');
        exit;
    }
    $saveImage = '';
    if (isset($_FILES['vImage']) && $_FILES['vImage']['name'] != "") {
        if (SITE_TYPE == 'Demo') {
            header("Location:cuisine_action.php?id=" . $id . '&success=2');
            exit;
        }

        

        $img_path = $tconfig["tsite_upload_page_images_panel"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vImage']['tmp_name'];
        $image_name = $_FILES['vImage']['name'];
        //$Photo_Gallery_folder = $img_path . "/home/";
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        $sql = "SELECT * FROM " . $tbl_name . " WHERE cuisineId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        $old_img = $db_data[0]['vImage'];
        $img_path = $tconfig["tsite_upload_images_menu_item_type_path"];

        if (!empty($old_img)) {
            $old_img_path = $img_path . '/' . $old_img;
            if ($old_img != '' && file_exists($old_img_path) && SITE_TYPE != "Demo") {
                unlink($img_path . '/' . $old_img);
            }
        }

        // $filecheck = basename($_FILES['vImage']['name']);
        // $fileextarr = explode(".", $filecheck);
        // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        // $flag_error = 0;
        // $tsite_upload_image_file_extensions = explode(",", $tconfig["tsite_upload_image_file_extensions"]);

        // if (!in_array($ext, $tsite_upload_image_file_extensions)) {
        //     $flag_error = 1;
        //     $var_msg = "Not valid image extension of ". $tsite_upload_image_file_extensions;
        // }

        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        if ($error) {
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $error;
            header("Location:cuisine_action.php".(($id != "") ? "?id=".$id : ""));
            exit();;
        } else {
            $Photo_Gallery_folder = $img_path . '/';
            //$img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder,$image_object, $image_name, '', 'png,jpg,jpeg,gif','EN');
            $img = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            //$img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder,$image_object, $image_name, '', 'png,jpg,jpeg,gif',$vCode);
            /* if($img == "1") {
                 $_SESSION['success'] = '0';
                 $_SESSION['var_msg'] = $img[1];
                 header("location:".$backlink);
             }*/
            if (!empty($img)) {
                $saveImage = " , vImage = '" . $img . "'";
            }
        }

    }
    if ($eDefaultType == "No") {
        $display_order_cuisine = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) as max_display_order, MIN(iDisplayOrder) as min_display_order FROM cuisine WHERE iServiceId = '$iServiceId' ");
        $max_display_order = $display_order_cuisine[0]['max_display_order'];
        $min_display_order = $display_order_cuisine[0]['min_display_order'];
        if ($action == "Add") {
            if ($iDisplayOrder < $max_display_order) {
                $obj->sql_query("UPDATE cuisine SET iDisplayOrder = (iDisplayOrder + 1) WHERE iDisplayOrder >= '$iDisplayOrder' AND iServiceId = '$iServiceIdNew' ");
            }
        } else {
            if (($iDisplayOrder < $max_display_order && $iDisplayOrder > $oldDisplayOrder) || ($iDisplayOrder == $max_display_order)) {
                $obj->sql_query("UPDATE cuisine SET iDisplayOrder = (iDisplayOrder - 1) WHERE iDisplayOrder <= '$iDisplayOrder' AND iDisplayOrder > '$oldDisplayOrder' AND iServiceId = '$iServiceIdNew' ");
            } elseif ($iDisplayOrder < $max_display_order && $iDisplayOrder < $oldDisplayOrder) {
                $obj->sql_query("UPDATE cuisine SET iDisplayOrder = (iDisplayOrder + 1) WHERE iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' AND iServiceId = '$iServiceIdNew' ");
            }
        }
    }
    for ($i = 0; $i < scount($vTitle_store); $i++) {
        $vValue = 'cuisineName_' . $db_master[$i]['vCode'];
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `cuisineId` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET
            `iServiceId`= '" . $iServiceIdNew . "',
            `iDisplayOrder`= '" . $iDisplayOrder . "',
            `vBgColor`= '" . $vBgColor . "',
            `vTextColor`= '" . $vTextColor . "',
            `vBorderColor`= '" . $vBorderColor . "',
            " . $vValue . " = '" . $_POST[$vTitle_store[$i]] . "'" . $saveImage . $where;
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
    header("location:" . $backlink);
}
$vBgColor = $vTextColor = $vBorderColor = "#ffffff";
$eDefaultType = "No";
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE cuisineId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $cuisineId = $value['cuisineId'];
                $vValue = 'cuisineName_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $eStatus = $value['eStatus'];
                $iServiceIdNew = $value['iServiceId'];
                $vImage = $value['vImage'];
                $arrLang[$vValue] = $$vValue;
                $vBgColor = $value['vBgColor'];
                $vTextColor = $value['vTextColor'];
                $vBorderColor = $value['vBorderColor'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $eDefaultType = $value['eDefault'];
            }
        }
    }
}
$catdata = ServiceData;
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_" . $default_lang . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus != 'Deleted' ";
$service_cat_list = $obj->MySQLSelect($service_category);
if ($action == 'Edit' && $iServiceIdNew == 0) {
    $service_cat_list[0]['iServiceId'] = 0;
}
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
$enableColorInput = "Yes";
if ($THEME_OBJ->isProThemeActive() == "Yes") {
    $enableColorInput = "No";
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
    <meta charset="UTF-8"/>
    <title>Admin | <?= $action; ?> Item Type </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
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
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $action; ?> Item Type</h2>
                    <a href="cuisine.php" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                    <? } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <? }  elseif ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $var_msg; ?>
                        </div>
                        <br/>
                    <? }?>
                    
                    <form method="post" name="_cuisine_form" id="_cuisine_form" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="cuisine.php"/>
                        <input type="hidden" name="eDefaultType" value="<?= $eDefaultType ?>">

                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Item Type <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="cuisineName_Default" name="cuisineName_Default"
                                           value="<?= isset($arrLang['cuisineName_' . $default_lang]) ? $arrLang['cuisineName_' . $default_lang] : ''; ?>"
                                           data-originalvalue="<?= isset($arrLang['cuisineName_' . $default_lang]) ? $arrLang['cuisineName_' . $default_lang] : ''; ?>"
                                           data-value="<?= isset($arrLang['cuisineName_' . $default_lang]) ? $arrLang['cuisineName_' . $default_lang] : ''; ?>"
                                           readonly="readonly"
                                           required <?php if ($id == "") { ?> onclick="editItemType('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editItemType('Edit')"><span
                                                    class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="modal fade" id="item_type_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Item Type Label
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'cuisineName_')">x
                                                </button>
                                            </h4>
                                        </div>

                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'cuisineName_' . $vCode;
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                $vCodeDefault = $default_lang;
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        $vCodeDefault = 'EN';
                                                    } else {
                                                        $vCodeDefault = $default_lang;
                                                    }
                                                }
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
                                                        <label>Item Type (<?= $vTitle; ?>
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
                                                                            onClick="getAllLanguageCode('cuisineName_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('cuisineName_', '<?= $default_lang ?>');">
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
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>
                                                    : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveItemType()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'cuisineName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>

                                        <div style="clear:both;"></div>
                                    </div>
                                </div>

                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Item Type <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="cuisineName_<?= $default_lang ?>"
                                           id="cuisineName_<?= $default_lang ?>"
                                           value="<?= $arrLang['cuisineName_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (scount($allservice_cat_data) <= 1 || ($action == 'Edit' && $iServiceIdNew == 0)) { ?>
                            <input name="iServiceId" type="hidden" class="create-account-input"
                                   value="<?php echo $service_cat_list[0]['iServiceId']; ?>"/>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Service Category<span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" name='iServiceId' id="iServiceId" required
                                            onchange="changeDisplayOrder(this.value)">
                                        <option value="">Select</option>
                                        <? for ($i = 0; $i < scount($service_cat_list); $i++) { ?>
                                            <option value="<?= $service_cat_list[$i]['iServiceId'] ?>" <? if ($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?> selected <?php } else if ($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                        <? } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (APP_TYPE == 'Ride-Delivery-UberX') {
                            //added by SP for cubex on 11-10-2019 to show image on app side... app type condition put after discussed with CD sir ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?php echo $langage_lbl_admin['LBL_MENU_ITEM_IMAGE'] ?></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <?php
                                    if (!empty($vImage)) {
                                        $imgUrl = $tconfig['tsite_url'] . 'resizeImg.php?w=100&src=' . $tconfig["tsite_upload_images_menu_item_type"] . '/' . $vImage;
                                        ?>
                                        <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail"
                                             style="max-width: 250px; max-height: 250px">
                                    <?php } ?>
                                    </span>
                                    <div class="vImageSection">
                                        <input name="vImage" id="vImage" type="file" class="form-control">
                                        <span class="notes">[Note: Recommended dimension is 2048px * 2048px. Recommended format is png, jpg, jpeg]</span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($enableColorInput == "Yes") { ?>
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Background Color</label>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="color" id="bgBolor" class="form-control"
                                                   value="<?= $vBgColor ?>"/>
                                            <input type="hidden" name="vBgColor" id="vBgColor" value="<?= $vBgColor ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Title Text Color</label>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="color" id="TextColor" class="form-control"
                                                   value="<?= $vTextColor ?>"/>
                                            <input type="hidden" name="vTextColor" id="vTextColor"
                                                   value="<?= $vTextColor ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Border Color</label>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="color" id="borderColor" class="form-control"
                                                   value="<?= $vBorderColor ?>"/>
                                            <input type="hidden" name="vBorderColor" id="vBorderColor"
                                                   value="<?= $vBorderColor ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($eDefaultType == "No") { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Display Order </label>
                                </div>
                                <div class="col-md-6 col-sm-6" id="showDisplayOrder001">
                                </div>
                                <input type="hidden" name="oldDisplayOrder" value="<?= $iDisplayOrder ?>">
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="iDisplayOrder" value="0">
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-item-type')) || ($action == 'Add' && $userObj->hasPermission('create-item-type'))) { ?>
                                    <input type="submit" class=" btn btn-default" name="submit" id="submit"
                                           value="<?= $action; ?> Item Type">
                                    <input type="reset" value="Reset" id="reset" class="btn btn-default">
                                <?php } ?>
                                <a href="cuisine.php" class="btn btn-default back_link">Cancel</a>
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
<script>
    $(document).ready(function () {
        $('#loaderIcon').hide();
        changeDisplayOrder('<?php echo $service_cat_list[0]['iServiceId']; ?>');
    });
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "cuisine.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
        $('#iServiceId').trigger('change');
    });

    function editItemType(action) {
        $('#modal_action').html(action);
        $('#item_type_Modal').modal('show');
    }

    function saveItemType() {
        if ($('#cuisineName_<?= $default_lang ?>').val() == "") {
            $('#cuisineName_<?= $default_lang ?>_error').show();
            $('#cuisineName_<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#cuisineName_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#cuisineName_Default').val($('#cuisineName_<?= $default_lang ?>').val());
        $('#cuisineName_Default').closest('.row').removeClass('has-error');
        $('#cuisineName_Default-error').remove();
        $('#item_type_Modal').modal('hide');
    }

    $("#borderColor, #bgBolor, #TextColor").on("input", function () {
        var color = $(this).val();
        if ($(this).attr('id') == "borderColor") {
            $('#vBorderColor').val(color);
        } else if ($(this).attr('id') == "bgBolor") {
            $('#vBgColor').val(color);
        } else {
            $('#vTextColor').val(color);
        }
    });
    let resetDisplayOrder = () => {
        var str = "<select name='iDisplayOrder' id='iDisplayOrder' class='form-control'><option>1</option></select>"
        $("#showDisplayOrder001").html('');
        $("#showDisplayOrder001").html(str);
        return "done";
    }
    let resetFrom = () => {
        var data = resetDisplayOrder();
    }
    var reset = document.getElementById("reset");
    reset.addEventListener("click", resetFrom)

    function changeDisplayOrder(iServiceId) {
        if (iServiceId != 'undefined' && iServiceId != '') {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_display_order.php',
                'AJAX_DATA': {
                    iServiceId: iServiceId,
                    page: 'cuisine',
                    method: '<?= $action ?>',
                    iDisplayOrder: '<?= $iDisplayOrder ?>'
                },
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
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


    /*------------------for field verification-----------------*/
    var DEFAULT_LANG = "<?= $default_lang ?>";
    var default_lang_field = $("#cuisineName_"+DEFAULT_LANG);
    var default_lang_field_error = $("#cuisineName_"+DEFAULT_LANG+'_error');
    default_lang_field.on("keyup  change", function() {

        if(default_lang_field.val() === "")
        {
            default_lang_field_error.show();
            default_lang_field.focus();
        }else{
            default_lang_field_error.hide();
        }

    });
    /*------------------for field verification-----------------*/

    if ($('#_cuisine_form').length !== 0) {
        $("#_cuisine_form").validate({
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
                    vImage:{
                        extension: imageUploadingExtenstionjsrule
                    }
            },
            messages: {
                    vImage:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
        });
    }

</script>
</body>
<!-- END BODY-->
</html>