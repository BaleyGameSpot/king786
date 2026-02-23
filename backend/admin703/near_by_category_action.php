<?php
include_once('../common.php');
if (!$userObj->hasPermission('edit-category-nearby')) {
    $userObj->redirect();
}

$script = 'nearbyCategory';
$tbl_name = "nearby_category";
$thumb = new thumbnail();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId
$parentid = isset($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
$vCategoryName = isset($_POST['vCategoryName']) ? $_POST['vCategoryName'] : '';
$vTextColor = isset($_POST['vTextColor']) ? $_POST['vTextColor'] : '#000000';
$vBgColor = isset($_POST['vBgColor']) ? $_POST['vBgColor'] : '#ffffff';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:near_by_category.php");
        exit;
    }

    if ($action == "Add" && !$userObj->hasPermission('create-category-nearby')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Near by Category.';
        header("Location:near_by_category.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-category-nearby')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Near by Category.';
        header("Location:near_by_category.php");
        exit;
    }

    $i = $iDisplayOrder;
    $temp_order = $_REQUEST['oldDisplayOrder'];
    $temp_order = (int)$temp_order; 
    $iDisplayOrder = (int)$iDisplayOrder;
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            $obj->sql_query("UPDATE $tbl_name SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "' ");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            $obj->sql_query("UPDATE $tbl_name SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "' ");
            $obj->sql_query($sql1);
        }
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
        //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
        // }
        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
        $image_width = $image_info[0];
        $image_height = $image_info[1];
        if ($error) {
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $error;
            header("Location:near_by_category.php");
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_images_nearby_item_path"];
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
            $vImage = $img[0];
            $query_p['vImage'] = $vImage;
            if (!empty($_POST['vImage_old']) && file_exists($Photo_Gallery_folder . $_POST['vImage_old'])) {
                // unlink($Photo_Gallery_folder . $_POST['vImage_old']);
            }
        }
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        $vCategoryName = "";
        if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
            $vCategoryName = $_POST['vTitle_' . $db_master[$i]['vCode']];
        }
        $vCategoryNameArr["vTitle_" . $db_master[$i]['vCode']] = $vCategoryName;
    }

    $jsonCategoryName = getJsonFromAnArr($vCategoryNameArr);
    for ($i = 0; $i < scount($db_master); $i++) {
        $tDescription = "";
        if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
            $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
        }
        $tDescriptionArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
    }

    $jsonDescription = getJsonFromAnArr($tDescriptionArr);
    $query_p['vTitle'] = $jsonCategoryName;
    $query_p['vTextColor'] = $vTextColor;
    $query_p['vBgColor'] = $vBgColor;
    $query_p['eStatus'] = $eStatus;
    $query_p['iDisplayOrder'] = $iDisplayOrder;

    if ($id != '') {
        $where = " iNearByCategoryId = '$id'";
        $obj->MySQLQueryPerform($tbl_name, $query_p, 'update', $where);
    } else {
        $obj->MySQLQueryPerform($tbl_name, $query_p, 'insert');
    }

    // $obj->sql_query($query);
    if ($id != '') {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }
    header("Location:near_by_category.php");
    exit();
}
// for Edit
$userEditDataArr = array();
$vDescriptionArr = array();
if ($action == 'Edit') {
    $nearbyCategory = $NEARBY_OBJ->getNearByCat('admin', $id);
        if(isset($nearbyCategory['vTitle_json'])){
            $vCategoryName = json_decode($nearbyCategory['vTitle_json'], true);
            foreach ($vCategoryName as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
        }
        if(isset($nearbyCategory['tDescription_json'])){
            $vDescription = json_decode($nearbyCategory['tDescription_json'], true);
            foreach ($vDescription as $key => $value) {
                $vDescriptionArr[$key] = $value;
            }
        }
        
    if(!empty($nearbyCategory)){
        $parentid = isset($nearbyCategory['iParentId']) ? $nearbyCategory['iParentId'] : '';
        $vIconImage = $nearbyCategory['vImage'];
        $vTextColor = $nearbyCategory['vTextColor'];
        $vBgColor = $nearbyCategory['vBgColor'];
        $eStatus = $nearbyCategory['eStatus'];
        $iDisplayOrder = $nearbyCategory['iDisplayOrder'];
    }

}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
$maxDisplayOrderData = $obj->MySQLSelect("SELECT max(iDisplayOrder) as maxDisplayOrder FROM $tbl_name ");
$maxDisplayOrder = $maxDisplayOrderData[0]['maxDisplayOrder'];
if ($action == 'Add') {
    $maxDisplayOrder = $maxDisplayOrder + 1;
}
?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | NearBy Category <?= $action; ?></title>
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
                    <h2><?php echo $action; ?> NearBy Category</h2>
                    <a href="near_by_category.php">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <? if ($success == 0 && $_REQUEST['var_msg'] != "") { ?>
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
                    <form method="post" action="" enctype="multipart/form-data" id="nearby_category_form">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="parentid" value="<?= $parentid; ?>"/>

                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Category Name<span class="red"> *</span></label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="vTitle_Default" name="vTitle_Default"
                                           value="<?= isset($userEditDataArr['vTitle_' . $default_lang]) ? $userEditDataArr['vTitle_' . $default_lang] : ''; ?>"
                                           data-originalvalue="<?= isset($userEditDataArr['vTitle_' . $default_lang]) ? $userEditDataArr['vTitle_' . $default_lang] : ''; ?>"
                                           readonly="readonly"
                                           required <?php if ($id == "") { ?> onclick="editCategoryName('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editCategoryName('Edit')"><span
                                                    class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="modal fade" id="Category_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Title
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
                                                        <label>Category Name (<?= $vTitle; ?>
                                                            ) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                               id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vTitle_', 'EN');">
                                                                        Convert To All
                                                                        Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vTitle_', '<?= $default_lang ?>');">
                                                                        Convert
                                                                        To All Language
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
                                                </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                            </h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveCategoryName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>



                        <div class="row">
                            <div class="col-lg-12">
                                <label>Category Name <span class="red"> *</span></label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input required type="text" class="form-control" id="vTitle_<?= $default_lang ?>"
                                       name="vTitle_<?= $default_lang ?>"
                                       value="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>">
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row">
                            <input type="hidden" name="vImage_old" id="vImage_old" value="<?= $vIconImage ?>">
                            <div class="col-lg-12">
                                <label>Icon
                                    <?= (!empty($vIconImage)) ? '<span class="red"> *</span>' : ''; ?></label>
                            </div>
                            <div class="col-lg-6">
                                <? if (!empty($vIconImage)) { ?>
                                    <div class="marginbottom-10">
                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=150&src=' . $tconfig['tsite_upload_images_nearby_item'] . $vIconImage; ?>">
                                    </div>
                                    <input type="hidden" class="form-control" name="vImage_upload" id="vImage_upload" value="1"/>
                                    <input type="file" class="form-control" name="vImage" id="vImage" value=""/>
                                <? } else { ?>
                                    <input type="file" class="form-control" name="vImage" id="vImage" value="" required/>
                                <? } ?>
                                <span class="notes">[Note: Upload only png image size of 360px X 360px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]</span>
                                </div>
                        </div>

                        <div style = "display:none" class="row">
                            <div class="col-lg-12">
                                <label>Title Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" id="TextColor" class="form-control" value="<?= $vTextColor ?>" />
                                <input type="hidden" name="vTextColor" id="vTextColor" value="<?= $vTextColor ?>">
                            </div>
                        </div>

                        <div style = "display:none" class="row">
                            <div class="col-lg-12">
                                <label>Background Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" id="BgColor" class="form-control" value="<?= $vBgColor ?>" />
                                <input type="hidden" name="vBgColor" id="vBgColor" value="<?= $vBgColor ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Display Order</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <select name="iDisplayOrder" class="form-control">
                                    <?php for ($i = 1; $i <= $maxDisplayOrder; $i++) { ?>
                                        <option value="<?= $i ?>" <?= $iDisplayOrder == $i ? "selected" : "" ?> <?php if(($action == 'Add') && ($i == $maxDisplayOrder)) { echo "selected"; }?>>
                                            <?= $i ?></option>
                                    <?php } ?>
                                </select>
                                <input type="hidden" name="oldDisplayOrder" id="oldDisplayOrder"
                                       value="<?= $iDisplayOrder ?>">
                            </div>
                        </div>
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
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class="save btn-info" name="submit" id="submit"
                                       value="<?= $action . ' ' . 'NearBy Category'; ?>"
                                       style="margin-right: 10px">
                                <a href="near_by_category.php"
                                   class="btn btn-default back_link">Cancel</a>
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
<script type="text/javascript">
    function editCategoryName(action) {
        $('#modal_action').html(action);
        $('#Category_Modal').modal('show');
    }

    function saveCategoryName() {
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
        $('#Category_Modal').modal('hide');
    }

    $("#TextColor").on("input", function(){
        var color = $(this).val();
        $('#vTextColor').val(color);
    });

    $("#BgColor").on("input", function(){
        var color = $(this).val();
        $('#vBgColor').val(color);
    });
</script>
</body>
<!-- END BODY-->
</html>