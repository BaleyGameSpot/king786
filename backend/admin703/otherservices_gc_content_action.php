<?php
include_once('../common.php');
require_once("library/validation.class.php");

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iLanguageMasId = isset($_REQUEST['iLanguageMasId']) ? $_REQUEST['iLanguageMasId'] : '';
$homepage = isset($_REQUEST['homepage']) ? $_REQUEST['homepage'] : 'No';
$backlink = "service_section.php";

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = count($db_master);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$vMetaTitle = isset($_REQUEST['vMetaTitle']) ? $_REQUEST['vMetaTitle'] : '';
$tMetaKeyword = isset($_REQUEST['tMetaKeyword']) ? $_REQUEST['tMetaKeyword'] : '';
$tMetaDescription = isset($_REQUEST['tMetaDescription']) ? $_REQUEST['tMetaDescription'] : '';

$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$script = 'ServiceSection';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";


$tbl_name = getContentCMSHomeTable();
$content_data = $obj->MySQLSelect("SELECT id, lBannerSection, vMetaTitle, tMetaKeyword, tMetaDescription FROM $tbl_name WHERE id = '$id'");
$banner_section = json_decode($content_data[0]['lBannerSection'], true);
// echo "<pre>"; print_r($banner_section); exit;

if($iLanguageMasId > 0) {
    $db_lang_data = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE iLanguageMasId = '" . $iLanguageMasId . "'");    
} else {
    $db_lang_data = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE vCode = '" . $default_lang . "'");    
}

$vCode = $db_lang_data[0]['vCode'];
$title = $db_lang_data[0]['vTitle'];

if (isset($_POST['btnsubmit_homepage'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:otherservices_gc_content_action.php?id=" . $id . "&homepage=Yes&success=2");
        exit;
    }

    $vServiceImageOld = isset($_POST['vServiceImageOld']) ? $_POST['vServiceImageOld'] : '';
    $vServiceImage = $vServiceImageOld;
    if (isset($_FILES['vServiceImage']) && $_FILES['vServiceImage']['name'] != "") {
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####", $tconfig["tsite_upload_image_file_extensions_validation"], $langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vServiceImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        if ($error) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = $error;
            header("Location:otherservices_gc_content_action.php?id=" . $id . "&homepage=Yes");
            exit;
        }

        $img_path = $tconfig["tsite_upload_home_page_service_images_panel"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vServiceImage']['tmp_name'];
        $image_name = $_FILES['vServiceImage']['name'];

        $check_file = $img_path . '/' . $vServiceImageOld;
        if (file_exists($check_file) && SITE_TYPE != "Demo") {
            @unlink($check_file);
        }

        $Photo_Gallery_folder = $img_path . '/';
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
            chmod($Photo_Gallery_folder, 0777);
        }
        $img = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);

        if (!empty($img[0])) {
            $vServiceImage = $img[0];
        } else {
            $_SESSION['success'] = '0';
            $_SESSION['var_msg'] = $img[1];
            header("Location:otherservices_gc_content_action.php?id=" . $id . "&homepage=Yes");
            exit;
        }
    }

    $vServiceCatTitleHomepageArr = $vServiceCatSubTitleHomepageArr = array();
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $tTypeDesc = "";
            $banner_section["vServiceTitle_" . $db_master[$i]['vCode']] = $tTypeDesc;
            if (isset($_POST['vServiceCatTitleHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vServiceCatTitleHomepage_' . $db_master[$i]['vCode']];
            }
            $banner_section["vServiceTitle_" . $db_master[$i]['vCode']] = $tTypeDesc;

            $tTypeDesc = "";
            $banner_section["vServiceDesc_" . $db_master[$i]['vCode']] = $tTypeDesc;
            if (isset($_POST['vServiceCatSubTitleHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vServiceCatSubTitleHomepage_' . $db_master[$i]['vCode']];
            }
            $banner_section["vServiceDesc_" . $db_master[$i]['vCode']] = $tTypeDesc;
        }
    }
    $banner_section['vServiceImage'] = $vServiceImage;

    $Data_update = array();
    $Data_update['lBannerSection'] = getJsonFromAnArrWithoutClean($banner_section);
    $where = " `id` = '" . $id . "'";
    $obj->MySQLQueryPerform($tbl_name, $Data_update, 'update', $where);

    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

    header("Location:" . $backlink);
    exit;
}

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:otherservices_gc_content_action.php?id=" . $id . "&homepage=Yes&success=2");
        exit;
    }

    $banner_section_img_old = isset($_POST['banner_section_img_old']) ? $_POST['banner_section_img_old'] : '';
    $banner_section_img = $banner_section_img_old;
    if (isset($_FILES['banner_section_img']) && $_FILES['banner_section_img']['name'] != "") {
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####", $tconfig["tsite_upload_image_file_extensions_validation"], $langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['banner_section_img'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        if ($error) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = $error;
            header("Location:otherservices_gc_content_action.php?id=" . $id . "&homepage=Yes");
            exit;
        }

        $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
        $image_object = $_FILES['banner_section_img']['tmp_name'];
        $image_name = $_FILES['banner_section_img']['name'];

        if(!empty($image_name)) {
            $check_file = $img_path . '/' . $vServiceImageOld;
            if (file_exists($check_file) && SITE_TYPE != "Demo") {
                @unlink($check_file);
            }

            $Photo_Gallery_folder = $img_path . $template . "/";
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"], $vCode);

            if (!empty($img[0])) {
                $banner_section_img = $img[0];
            } else {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = $img[1];
                header("Location:otherservices_gc_content_action.php?id=" . $id . "&homepage=Yes");
                exit;
            }
        }
    }

    $sectionData = $banner_section;
    $banner_section_arr['title_' . $vCode] = isset($_POST['banner_section_title']) ? $_POST['banner_section_title'] : '';
    $banner_section_arr['sub_title_' . $vCode] = isset($_POST['banner_section_subtitle']) ? $_POST['banner_section_subtitle'] : '';
    $banner_section_arr['desc_' . $vCode] = isset($_POST['banner_section_desc']) ? $_POST['banner_section_desc'] : '';
    $banner_section_arr['img_alt_'.$vCode] = isset($_POST['banner_section_img_alt']) ? $_POST['banner_section_img_alt'] : '';
    $banner_section_arr['img_' . $vCode] = $banner_section_img;
    $banner_section_arr = !(empty($sectionData)) ? array_merge($sectionData, $banner_section_arr) : $banner_section_arr;
    $banner_section = getJsonFromAnArrWithoutClean($banner_section_arr); 

    $update_array = [];
    $update_array['lBannerSection'] = $banner_section;
    $update_array['vMetaTitle'] = $vMetaTitle;
    $update_array['tMetaKeyword'] = $tMetaKeyword;
    $update_array['tMetaDescription'] = $tMetaDescription;
    $where = "id = '$id'";
    $obj->MySQLQueryPerform($tbl_name, $update_array, 'update', $where);

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location: otherservices_gc_content_action.php?id=" . $id . "&iLanguageMasId=" . $iLanguageMasId);
    exit;
}
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Service Home Content <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <style>
        .body-div.innersection {
            box-shadow: -1px -2px 73px 2px #dedede;
            float: none;
        }

        .innerbg_image {
            width: auto;
            margin: 10px 0;
            height: 150px;
        }

        .notes {
            font-weight: 700;
            font-style: italic;
        }
    </style>
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
                    <?php if($homepage == "Yes") { ?>
                    <h2>Service Home Content</h2>
                    <a href="<?= $backlink ?>" class="back_link add-btn">Back to Listing</a>
                    <?php } else { ?>
                    <div class="col-lg-8" >
                        <h2>Service Page Content (<?php echo $title; ?>)</h2>
                    </div>
                    
                    <div class="col-lg-4 languageSelection">
                        <div class="col-lg-6" style="text-align: end;margin: auto;">
                            <p style="margin: 0; font-weight:700;" >Select Language:</p>
                        </div>
                        <select onchange="language_wise_page(this);" name="language" id="language" class="form-control">
                            <?php
                            foreach ($db_master as $dm) {
                                $selected = '';
                                if ($dm['iLanguageMasId'] == $iLanguageMasId) {
                                    $selected = 'selected';
                                }
                                ?>
                                <option <?php echo $selected; ?> value="<?php echo $dm['iLanguageMasId'] ?>"><?php echo $dm['vTitle'] ?> </option>
                            <?php }
                            ?>
                        </select>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <?php include('valid_msg.php'); ?>
            <div class="body-div">
                <div class="form-group">
                    <form method="post" name="_home_content_form" id="_home_content_form" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="<?= $backlink; ?>"/>
                        
                        <?php if($homepage == "Yes") { ?>
                            <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title (<?= $db_master[0]['vTitle']; ?>)</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text"
                                           class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="vServiceCatTitleHomepage_Default"
                                           value="<?= $banner_section['vServiceTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $banner_section['vServiceTitle_' . $default_lang]; ?>"
                                           readonly="readonly" <?php if ($id == "") { ?> onclick="editServiceCatTitleHomepage('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit"
                                                onclick="editServiceCatTitleHomepage('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>SubTitle (<?= $db_master[0]['vTitle']; ?>)</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="vServiceCatSubTitleHomepage_Default" value="<?= $banner_section['vServiceDesc_' . $default_lang]; ?>" data-originalvalue="<?= $banner_section['vServiceDesc_' . $default_lang]; ?>" readonly="readonly" <?php if ($id == "") { ?> onclick="editServiceCatSubTitleHomepage('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit"
                                                onclick="editServiceCatSubTitleHomepage('Edit')">
                                            <span class="glyphicon glyphicon-pencil"
                                                  aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="modal fade" id="ServiceCatTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="category_action"></span> Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vServiceCatTitleHomepage_')">
                                                    x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vServiceCatTitleHomepage_' . $vCode;
                                                $$vValue = $banner_section['vServiceTitle_' . $vCode];
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                        <div class="text-danger"
                                                             id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage"
                                                                            class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vServiceCatTitleHomepage_', 'EN');">
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
                                                                            onClick="getAllLanguageCode('vServiceCatTitleHomepage_', '<?= $default_lang ?>');">
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
                                            <h5 class="text-left"
                                                style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>
                                                    : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                            </h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" id="vCategory_btn"
                                                        style="margin-left: 0 !important"
                                                        onclick="saveServiceCatTitleHomepage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok"
                                                        data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vServiceCatTitleHomepage_')">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="ServiceCatSubTitle_Modal" tabindex="-1"
                                 role="dialog" aria-hidden="true" data-backdrop="static"
                                 data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="category_action"></span> SubTitle
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vServiceCatSubTitleHomepage_')">
                                                    x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vServiceCatSubTitleHomepage_' . $vCode;
                                                $$vValue = $banner_section['vServiceDesc_' . $vCode];
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                        <label>SubTitle (<?= $vTitle; ?>
                                                            ) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control"
                                                               name="<?= $vValue; ?>" id="<?= $vValue; ?>"
                                                               value="<?= $$vValue; ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger"
                                                             id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage"
                                                                            class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vServiceCatSubTitleHomepage_', 'EN');">
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
                                                                            onClick="getAllLanguageCode('vServiceCatSubTitleHomepage_', '<?= $default_lang ?>');">
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
                                            <h5 class="text-left"
                                                style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>
                                                    : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                            </h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" id="vCategory_btn"
                                                        style="margin-left: 0 !important"
                                                        onclick="saveServiceCatSubTitleHomepage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok"
                                                        data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vServiceCatSubTitleHomepage_')">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title (<?= $db_master[0]['vTitle']; ?>)</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="vServiceCatTitleHomepage_<?= $default_lang ?>" name="vServiceCatTitleHomepage_<?= $default_lang ?>" value="<?= $banner_section['vServiceTitle_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>SubTitle (<?= $db_master[0]['vSubTitle']; ?>)</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="vServiceCatSubTitleHomepage_<?= $default_lang ?>" name="vServiceCatSubTitleHomepage_<?= $default_lang ?>" value="<?= $banner_section['vServiceDesc_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="row imagebox">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <?php if (!empty($banner_section['vServiceImage'])) { ?>
                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=80&src=' . $tconfig['tsite_upload_home_page_service_images'] . "/" . $banner_section['vServiceImage']; ?>" style="margin-top: 10px;">
                                    <?php } ?>
                                    <input type="file" class="form-control" name="vServiceImage" id="vServiceImage" style="margin: 10px 0;">
                                    <input type="hidden" class="form-control" name="vServiceImageOld" value="<?= $banner_section['vServiceImage'] ?>">
                                    
                                    <div><span class="notes">[Note: Upload only png image size of 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]</span></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <input type="submit" class="save btn-info" name="btnsubmit_homepage" id="btnsubmit_homepage" value="Update">
                                    <a href="service_section.php" class="btn btn-default back_link" style="margin-left: 10px">Cancel</a>
                                </div>
                            </div>

                        <?php } else { ?>
                        <input type="hidden" name="vCode" value="<?= $vCode; ?>">

                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="banner_section_title"
                                               id="banner_section_title"
                                               value="<?= $banner_section['title_' . $vCode]; ?>" placeholder="Title"
                                               required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="banner_section_subtitle"
                                               id="banner_section_subtitle"
                                               value="<?= $banner_section['sub_title_' . $vCode]; ?>" placeholder="Subtitle" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">

                                        <textarea class="form-control ckeditor" rows="10" name="banner_section_desc"
                                                  id="banner_section_desc"
                                                  placeholder="Description"><?= $banner_section['desc_' . $vCode]; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($banner_section['img_' . $vCode] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $banner_section['img_' . $vCode]; ?>"
                                                 class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="banner_section_img"
                                               id="banner_section_img" accept=".png,.jpg,.jpeg,.gif,.svg">
                                               <input type="hidden" name="banner_section_img_old" value="<?= $banner_section['img_' . $vCode] ?>">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1024px * 684px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image alt</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="banner_section_img_alt" id="banner_section_img_alt" value="<?= $banner_section['img_alt_'.$vCode]; ?>" placeholder="Image alt Text">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="body-div innersection seo_section">
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Meta Title</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="vMetaTitle"  id="vMetaTitle" value="<?= htmlspecialchars($content_data[0]['vMetaTitle']); ?>" placeholder="Meta Title">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Meta Keyword</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="tMetaKeyword"  id="tMetaKeyword" value="<?= htmlspecialchars($content_data[0]['tMetaKeyword']); ?>" placeholder="Meta Keyword">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Meta Description</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <textarea class="form-control" rows="10" name="tMetaDescription" placeholder="Meta Description"><?= $content_data[0]['tMetaDescription']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="Edit Page Content">
                                <a href="<?= $backlink; ?>" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                        <?php } ?>                        
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
<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
<script src="../assets/plugins/ckeditor/config.js"></script>
<script>

    /**
     * This will reset the CKEDITOR using the input[type=reset] clicks.

     */

    $(function () {
        if (typeof CKEDITOR != 'undefined') {
            CKEDITOR.replace('ckeditor', {
                allowedContent: {
                    i: {
                        classes: 'fa*'
                    },
                    span: true
                }
            });
            $('form').on('reset', function (e) {
                if ($(CKEDITOR.instances).length) {
                    for (var key in CKEDITOR.instances) {
                        var instance = CKEDITOR.instances[key];
                        if ($(instance.element.$).closest('form').attr('name') == $(e.target).attr('name')) {
                            instance.setData(instance.element.$.defaultValue);
                        }
                    }
                }
            });
        }
    });

    $(".FilUploader").change(function () {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'svg'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            alert("Only formats are allowed : " + fileExtension.join(', '));
            $(this).val('');
            return false;
        }

    });

    function editServiceCatTitleHomepage(action) {
        $('#service_desc_action').html(action);
        $('#ServiceCatTitle_Modal').modal('show');
    }

    function saveServiceCatTitleHomepage() {
        if ($('#vServiceCatTitleHomepage_<?= $default_lang ?>').val() == "") {
            $('#vServiceCatTitleHomepage_<?= $default_lang ?>_error').show();
            $('#vServiceCatTitleHomepage_<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#vServiceCatTitleHomepage_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vServiceCatTitleHomepage_Default').val($('#vServiceCatTitleHomepage_<?= $default_lang ?>').val());
        $('#ServiceCatTitle_Modal').modal('hide');
    }

    function editServiceCatSubTitleHomepage(action) {
        $('#ServiceCatSubTitle_Modal').modal('show');
    }

    function saveServiceCatSubTitleHomepage() {
        if ($('#vServiceCatSubTitleHomepage_<?= $default_lang ?>').val() == "") {
            $('#vServiceCatSubTitleHomepage_<?= $default_lang ?>_error').show();
            $('#vServiceCatSubTitleHomepage_<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#vServiceCatSubTitleHomepage_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vServiceCatSubTitleHomepage_Default').val($('#vServiceCatSubTitleHomepage_<?= $default_lang ?>').val());
        $('#ServiceCatSubTitle_Modal').modal('hide');
    }

    function language_wise_page(sel) {
        $("#loaderIcon").show();
        var url = window.location.href;
        url = new URL(url);
        url.searchParams.set("iLanguageMasId", sel.value);
        window.location.href = url.href;
    }
</script>
</body>
<!-- END BODY-->
</html>