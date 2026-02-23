<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
define("NEWSFEED", "newsfeed");
$script = 'news';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$eUserType = isset($_POST['eUserType']) ? $_POST['eUserType'] : '';
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$txtBoxNameArr = array("vTitle");
$lableArr = array("Title");
$vImage = $welComeImg = "";
$img_data = array();
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-news')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
        header("Location:news.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-news')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
        header("Location:news.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        //header("Location:news_feed_action_new.php?id=" . $id . "&success=2");
        $_SESSION['success'] = '2';
        header("location:" . $backlink);
        exit;
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['eUserType'], 'req', 'User Type is required');
    $vtitleArr = $descArr = array();
    $error = $validobj->validate();
    if ($error) {
        $success = 3;
        $newError = $error;
        $_SESSION['var_msg'] = $newError;
        $_SESSION['success'] = "3";
        exit;
    } else {
        $tPublishdate = date("Y-m-d H:i:s");
        for ($i = 0; $i < scount($db_master); $i++) {
            $vTitle = $tDescription = "";
            if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
                $vTitle = $_POST['vTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
                $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
            }
            $q = "INSERT INTO ";
            $where = '';
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iNewsfeedId` = '" . $id . "'";
            }
            $vtitleArr["vTitle_" . $db_master[$i]['vCode']] = $vTitle;
            $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
        }
        if ($eStatus == '') {
            $str = ", eStatus = 'Inactive' ";
        } else {
            $str = ", eStatus = 'Active'";
        }
        $time = time();
        if (scount($vtitleArr) > 0) {
            $updateProfileImg = "";
            /*$jsonTitle = $obj->cleanQuery(json_encode($vtitleArr));
            $jsonDesc = $obj->cleanQuery(json_encode($descArr));*/
            $jsonTitle = getJsonFromAnArr($vtitleArr);
            $jsonDesc = getJsonFromAnArr($descArr);
            $query = $q . " `" . NEWSFEED . "` SET `vTitle` = '" . $jsonTitle . "',`eUserType` = '" . $eUserType . "',`tPublishdate` = '" . $tPublishdate . "',`tDescription` = '" . $jsonDesc . "' $str $updateProfileImg" . $where;
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
        }
        // for image upload
        if ($_FILES['vNewfeedImage']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_news_feed_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vNewfeedImage']['tmp_name'];
            $image_name = $_FILES['vNewfeedImage']['name'];
            // $filecheck = basename($_FILES['vNewfeedImage']['name']);
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
            $error = $validobj->validateFileType($_FILES['vNewfeedImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

            if ($error) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $error;
                header("Location:news.php");
                exit;
            }

            $dataimg = getimagesize($_FILES['vNewfeedImage']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];
            if ($imgwidth < 1024) {
                echo "<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }
            $check_file_query = "select vNewfeedImage from newsfeed where iNewsfeedId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            $oldImage = $check_file[0]['vNewfeedImage'];
            $check_file = $img_path . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $oldImage);
            }
            if ($flag_error == 1) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;
                header("Location:news.php");
            } else {
                $Photo_Gallery_folder = $img_path . '/' . $iNewsfeedId . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img1 = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $vNewfeedImage = $img1[0];
                $sql1 = "UPDATE newsfeed SET `vNewfeedImage` = '" . $vNewfeedImage . "' WHERE `iNewsfeedId` = '" . $id . "'";
                $obj->sql_query($sql1);
            }
        }
        if ($action == "Add") {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            $_SESSION['success'] = "1";
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            $_SESSION['success'] = "1";
        }
        header("location:" . $backlink);
        exit;
    }
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . NEWSFEED . " WHERE iNewsfeedId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) {
        $vTitle = json_decode($db_data[0]['vTitle'], true);
        foreach ($vTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $tDescription = json_decode($db_data[0]['tDescription'], true);
        foreach ($tDescription as $key4 => $value4) {
            $userEditDataArr[$key4] = $value4;
        }
        if (scount($db_data) > 0) {
            foreach ($db_data as $key => $value) {
                $vNewfeedImage = $value['vNewfeedImage'];
                $eStatus = $value['eStatus'];
                $eUserType = $value['eUserType'];
            }
        }
    }
}
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
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
    <title><?= $SITE_NAME ?> | News <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>

    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>

    <!-- PAGE LEVEL STYLES -->
    <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css"/>
    <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css"/>
    <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css"/>
    <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css"/>
    <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css"/>
    <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css"/>
    <style>
        ul.wysihtml5-toolbar > li {
            position: relative;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2> <?= $action; ?> News </h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div><br/>
                    <?php } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable ">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <?php } else if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $_REQUEST['varmsg']; ?>
                        </div><br/>
                    <?php } ?>
                    <?php if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Record Not Updated .
                        </div><br/>
                    <?php } ?>
                    <form id="_news_form" name="_news_form" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="news.php"/>
                        <div class="row">
                            <div class="col-lg-12" id="errorMessage"></div>
                        </div>

                        <?php if (scount($db_master) > 0) {
                            if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select User Type<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <select class="form-control" name='eUserType' id="eUserType" required="">
                                            <option value="">Select User Type</option>
                                            <option value="all" <?php if ($eUserType == 'all') {
                                                echo 'selected';
                                            } ?>>All
                                            </option>
                                            <option value="driver" <?php if ($eUserType == 'driver') {
                                                echo 'selected';
                                            } ?>><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                            <option value="rider" <?php if ($eUserType == 'rider') {
                                                echo 'selected';
                                            } ?>><?php echo $langage_lbl_admin['LBL_RIDER']; ?></option>
                                            <?php if (DELIVERALL == "Yes") { ?>
                                                <option value="company" <?php if ($eUserType == 'company') {
                                                    echo 'selected';
                                                } ?>>

                                                    <?php if (ONLY_MEDICAL_SERVICE == "Yes") { ?>
                                                        Store
                                                    <?php } else { ?>
                                                        Store/Restaurant
                                                    <?php } ?>

                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <?php if (!empty($vNewfeedImage)) { ?>
                                        <!-- <img src="<?= $tconfig['tsite_upload_images_news_feed'] . "/" . $vNewfeedImage; ?>" style="width:100px;height:100px;"> -->

                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=200&h=200&src=' . $tconfig['tsite_upload_images_news_feed'] . "/" . $vNewfeedImage; ?>"
                                             style="width:100px;height:100px;">

                                    <?php } ?>
                                    <input type="file" class="form-control" name="vNewfeedImage" id="vNewfeedImage"
                                           value="<?= isset($vNewfeedImage) ? $vNewfeedImage : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Status</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" name="eStatus"
                                               id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
                                    </div>
                                </div>
                            </div>

                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text"
                                               class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                               id="vTitle_Default" name="vTitle_Default"
                                               value="<?= isset($userEditDataArr['vTitle_' . $default_lang]) ? $userEditDataArr['vTitle_' . $default_lang] : ''; ?>"
                                               data-originalvalue="<?= isset($userEditDataArr['vTitle_' . $default_lang]) ? $userEditDataArr['vTitle_' . $default_lang] : ''; ?>"
                                               readonly="readonly" <?php if ($id == "") { ?> onclick="editNewsTitle('Add')" <?php } ?>>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editNewsTitle('Edit')"><span
                                                        class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="modal fade" id="news_action_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> News Title
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
                                                    $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '';
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
                                                    <strong><?= $langage_lbl['LBL_NOTE']; ?>
                                                        : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                    <button type="button" class="save" style="margin-left: 0 !important"
                                                            onclick="saveNewsTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
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
                                        <textarea class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                                  name="tDescription_Default" id="tDescription_Default" rows="4"
                                                  readonly="readonly" <?php if ($id == "") { ?> onclick="editNewsDesc('Add')" <?php } ?> data-originalvalue="<?= isset($userEditDataArr['tDescription_' . $default_lang]) ? $userEditDataArr['tDescription_' . $default_lang] : ''; ?>"><?= isset($userEditDataArr['tDescription_' . $default_lang]) ? $userEditDataArr['tDescription_' . $default_lang] : ''; ?></textarea>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editNewsDesc('Edit')"><span
                                                        class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="modal fade" id="news_desc_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> News Description
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'tDescription_')">x
                                                    </button>
                                                </h4>
                                            </div>

                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $descVal = 'tDescription_' . $vCode;
                                                    $$descVal = isset($userEditDataArr['tDescription_' . $vCode]) ? $userEditDataArr['tDescription_' . $vCode] : '';
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
                                                        <div class="<?= $page_title_class ?>">
                                                            <textarea class="form-control" name="<?= $descVal; ?>"
                                                                      id="<?= $descVal; ?>"
                                                                      placeholder="<?= $vTitle; ?> Value" rows="4"
                                                                      data-originalvalue="<?= $$descVal; ?>"><?= $$descVal; ?></textarea>

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
                                                                                onClick="getAllLanguageCode('tDescription_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">
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
                                                            onclick="saveNewsDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok"
                                                            data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'tDescription_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>

                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vTitle_<?= $default_lang ?>"
                                               name="vTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <textarea class="form-control" name="tDescription_<?= $default_lang ?>"
                                                  id="tDescription_<?= $default_lang ?>"
                                                  rows="4"><?= $userEditDataArr['tDescription_' . $default_lang]; ?></textarea>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php /*
                                    for ($i = 0; $i < scount($db_master); $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];
                                        $descVal = 'tDescription_' . $vCode;
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                        for ($l = 0; $l < scount($txtBoxNameArr); $l++) {
                                            $lableText = $lableArr[$l];
                                            $lableName = $txtBoxNameArr[$l] . '_' . $vCode;
                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $lableText; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6"><!-- <?= $lableName; ?> -->
                                                    <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                    <div class="text-danger" id="<?= $lableName.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                </div>
            <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?php echo $txtBoxNameArr[$l].'_'; ?>', '<?= $default_lang ?>');">Convert To All Language</button>
            <?php } ?>
                                            </div>
        <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>><?= $userEditDataArr[$descVal]; ?></textarea>
                                                <div class="text-danger" id="<?= $descVal.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                        <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                <div class="col-md-6 col-sm-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                </div>
                                        <?php } ?>
                                        </div>

        <?php
    }*/
                        }
                        ?>
                        <div class="col-lg-12">
                            <?php if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-news')) || ($action == "Add" && $userObj->hasPermission('create-news'))) { ?>
                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit"
                                       value="<?= $action; ?> News">
                                <input type="reset" value="Reset" class="btn btn-default">
                            <?php } ?>
                            <a href="news.php" class="btn btn-default back_link">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<!--END PAGE CONTENT -->
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<?php
include_once('footer.php');
?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

<script>
    $('[data-toggle="tooltip"]').tooltip();
    var successMSG1 = '<?php echo $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }
</script>
<!--For Faretype End-->
<script type="text/javascript" language="javascript">

    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "news.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
</script>
<!--END MAIN WRAPPER -->

<!-- GLOBAL SCRIPTS -->
<!--<script src="../assets/plugins/jquery-2.0.3.min.js"></script>-->
<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
<!-- END GLOBAL SCRIPTS -->

<!-- PAGE LEVEL SCRIPTS -->
<script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
<script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>
<script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
<script src="../assets/plugins/pagedown/Markdown.Converter.js"></script>
<script src="../assets/plugins/pagedown/Markdown.Sanitizer.js"></script>
<script src="../assets/plugins/Markdown.Editor-hack.js"></script>
<script src="../assets/js/editorInit.js"></script>
<script>
    $(function () {
        formWysiwyg();
    });

    function editNewsTitle(action) {
        $('#modal_action').html(action);
        $('#news_action_Modal').modal('show');
    }

    function saveNewsTitle() {
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
        $('#news_action_Modal').modal('hide');
    }

    function editNewsDesc(action) {
        $('#modal_action').html(action);
        $('#news_desc_Modal').modal('show');
    }

    function saveNewsDesc() {
        if ($('#tDescription_<?= $default_lang ?>').val() == "") {
            $('#tDescription_<?= $default_lang ?>_error').show();
            $('#tDescription_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#tDescription_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#tDescription_Default').val($('#tDescription_<?= $default_lang ?>').val());
        $('#news_desc_Modal').modal('hide');
    }
</script>
</body>
<!-- END BODY-->
</html>
