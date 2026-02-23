<?php
include_once('../common.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$tbl_name = 'parcel_delivery_items_size_info';
$script = 'ParcelDeliveryItemSize';

$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);      

if (isset($_POST['submit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-item-size-parcel-delivery')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create item size.';
        header("Location:state.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-item-size-parcel-delivery')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update item size.';
        header("Location:state.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:parcel_delivery_items_size_action.php?id=" . $id . '&success=2');
        exit;
    }

    $Data_update = array();

    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    $image_update = "";
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
            header("Location:parcel_delivery_items_size.php");
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_images_parcel_delivery_items_size_path"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
            $vImage = $img[0];
            $Data_update['vImage'] = $vImage;
        }
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        if (isset($_POST['tTitle_' . $db_master[$i]['vCode']])) {
            $tTitle = $_POST['tTitle_' . $db_master[$i]['vCode']];
        }
        if (isset($_POST['tSubtitle_' . $db_master[$i]['vCode']])) {
            $tSubtitle = htmlspecialchars($_POST['tSubtitle_' . $db_master[$i]['vCode']], ENT_IGNORE);
        }
        $tTitleArr["tTitle_" . $db_master[$i]['vCode']] = $tTitle;
        $tSubtitleArr["tSubtitle_" . $db_master[$i]['vCode']] = $tSubtitle;
    }
    $jsonTitle = getJsonFromAnArr($tTitleArr);
    $jsonSubtitle = getJsonFromAnArr($tSubtitleArr);

    $Data_update['tTitle'] = $jsonTitle;
    $Data_update['tSubtitle'] = $jsonSubtitle;
    $Data_update['eStatus'] = $eStatus;
    if ($id != '') {
        $where = " `iItemSizeCategoryId` = '" . $id . "'";
        $obj->MySQLQueryPerform($tbl_name, $Data_update, 'update', $where);
    } else {
        $id = $obj->MySQLQueryPerform($tbl_name, $Data_update, 'insert');
    }

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:" . $backlink);
    exit;
}

// for Edit
if ($action == 'Edit') {
    $db_data = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE iItemSizeCategoryId = '" . $id . "'");

    $vLabel = $id;
    if (scount($db_data) > 0) {
        $tTitle = json_decode($db_data[0]['tTitle'], true);
        foreach ($tTitle as $key => $value) {
            $userEditDataArr[$key] = htmlspecialchars($value);
        }
        $tSubtitle = json_decode($db_data[0]['tSubtitle'], true);
        foreach ($tSubtitle as $key4 => $value4) {
            $userEditDataArr[$key4] = htmlspecialchars($value4);
        }
        $vImage = $db_data[0]['vImage'];
        $eStatus = $db_data[0]['eStatus'];
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Item Size Detail <?= $action; ?></title>
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
                            <h2><?= $action; ?> Item Size Detail</h2>
                            <a href="parcel_delivery_items_size.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" name="_items_size" id="_items_size" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="parcel_delivery_items_size.php"/>
                                <div class="col-lg-12" id="errorMessage"></div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image<?= ($vImage == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($vImage != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=150&src=' . $tconfig['tsite_upload_images_parcel_delivery_items_size'] . '/' .  $vImage; ?>">
                                            <input type="file" class="form-control" name="vImage" id="vImage"
                                                   value="<?= $vImage; ?>"/>
                                        <?php } else { ?>
                                            <input type="file" class="form-control" name="vImage" id="vImage"
                                                   value="<?= $vImage; ?>" required/>
                                        <?php } ?>
                                        <b>[Note: Recommended dimension is 512px X 512px.]</b>
                                    </div>
                                </div>

                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">

                                        <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                               id="tTitle_Default" name="tTitle_Default"
                                               value="<?= $userEditDataArr['tTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['tTitle_' . $default_lang]; ?>"
                                               readonly="readonly"
                                               required <?php if ($id == "") { ?> onclick="editTitle('Add')" <?php } ?>>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editTitle('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="modal fade" id="Title_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'tTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'tTitle_' . $vCode;
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
                                                                                onClick="getAllLanguageCode('tTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('tTitle_', '<?= $default_lang ?>');">
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
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'tTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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


                                    <input type="hidden"
                                           value="<?= htmlspecialchars( $userEditDataArr['tSubtitle_' . $default_lang]); ?>">

                                    <div class="col-md-6 col-sm-6">
                                        <input type="text"
                                               class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                               id="tSubtitle_Default" name="tSubtitle_Default"
                                               value="<?= $userEditDataArr['tSubtitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['tSubtitle_' . $default_lang]; ?>"
                                               readonly="readonly"
                                               required <?php if ($id == "") { ?> onclick="editSubtitle('Add')" <?php } ?>>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editSubtitle('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="modal fade" id="Subtitle_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span>
                                                    Description
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'tSubtitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $descVal = 'tSubtitle_' . $vCode;
                                                    $$descVal = $userEditDataArr['tSubtitle_' . $vCode];
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
                                                            <label>Description (<?= $vTitle; ?>
                                                                ) <?php echo $required_msg; ?></label>
                                                        </div>
                                                        <div class="<?= $page_title_class ?> desc-block">

                                                            <input type="text" class="form-control subtitle-txt"
                                                                   name="<?= $descVal; ?>" id="<?= $descVal; ?>"
                                                                   value="<?= $$descVal; ?>"
                                                                   data-originalvalue="<?= $$descVal; ?>"
                                                                   placeholder="<?= $vTitle; ?> Value">

                                                            <div class="text-danger" id="<?= $descVal . '_error'; ?>"
                                                                 style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                        </div>
                                                        <?php
                                                        if (scount($db_master) > 1) {
                                                            if ($EN_available) {
                                                                if ($vCode == "EN") { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('tSubtitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('tSubtitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveSubtitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok"
                                                            data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'tSubtitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="tTitle_<?= $default_lang ?>"
                                               name="tTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['tTitle_' . $default_lang]; ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="tSubtitle_<?= $default_lang ?>"
                                               name="tSubtitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['tSubtitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-item-size-parcel-delivery')) || ($action == 'Add' && $userObj->hasPermission('create-item-size-parcel-delivery'))) { ?>
                                            <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Item Size Detail">
                                        <?php } ?>
                                        <a href="parcel_delivery_items_size.php" class="btn btn-default back_link">Cancel</a>
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

        <?php include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
    </body>
    <!-- END BODY-->
</html>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") { //alert('pre1');
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }

        if (referrer == "") {
            referrer = "package_type.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });

    function editTitle(action) {
        $('#modal_action').html(action);
        $('#Title_Modal').modal('show');
    }

    function saveTitle() {
        if ($('#tTitle_<?= $default_lang ?>').val() == "") {
            $('#tTitle_<?= $default_lang ?>_error').show();
            $('#tTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#tTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#tTitle_Default').val($('#tTitle_<?= $default_lang ?>').val());
        $('#tTitle_Default').closest('.row').removeClass('has-error');
        $('#tTitle_Default-error').remove();
        $('#Title_Modal').modal('hide');
    }

    function editSubtitle(action) {
        $('#modal_action').html(action);
        $('#Subtitle_Modal').modal('show');
    }

    function saveSubtitle() {
        if ($('#tSubtitle_<?= $default_lang ?>').val() == "") {
            $('#tSubtitle_<?= $default_lang ?>_error').show();
            $('#tSubtitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#tSubtitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#tSubtitle_Default').val($('#tSubtitle_<?= $default_lang ?>').val());
        $('#Subtitle_Modal').modal('hide');
    }
</script>