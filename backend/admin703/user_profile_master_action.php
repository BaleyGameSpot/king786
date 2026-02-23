<?php
include_once('../common.php');

$eSystem = "DeliverAll";
define("USER_PROFILE_MASTER", "user_profile_master");
$script = 'RideProfileType';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);

$vImage = $welComeImg = "";
$img_data = array();
if (isset($_POST['btnsubmit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-profile-taxi-service')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:user_profile_master.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-profile-taxi-service')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:user_profile_master.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:user_profile_master_action.php?id=" . $id . "&success=2");
        exit;
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    $vtitleArr = $vSubTitleArr = $vHeadArr = $vScreenTitleArr = $descArr = $buttonTxtArr = $profileNameArr = $profileShortNameArr = array();
    
    // $error = $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Profile Icon file is not valid.');

    $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
    $error .= $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

    // $error .= $validobj->validateFileType($_FILES['vWelcomeImage'], 'jpg,jpeg,png,gif,bmp', '* Welcome Picture file is not valid.');
    $error .= $validobj->validateFileType($_FILES['vWelcomeImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
    //print_R();die;
    if ($error) {
        $success = 3;
        $newError = $error;
        $_SESSION['var_msg'] = $newError;
        $_SESSION['success'] = "3";
        header("Location:user_profile_master.php");
        exit;
    } else {
        for ($i = 0; $i < scount($db_master); $i++) {
            $vTitle = $vSubTitle = $vScreenHeading = $vScreenTitle = $tDescription = $vScreenButtonText = $vProfileName = $vShortProfileName = "";
            if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
                $vTitle = $_POST['vTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['vSubTitle_' . $db_master[$i]['vCode']])) {
                $vSubTitle = $_POST['vSubTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['vScreenHeading_' . $db_master[$i]['vCode']])) {
                $vScreenHeading = $_POST['vScreenHeading_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['vScreenTitle_' . $db_master[$i]['vCode']])) {
                $vScreenTitle = $_POST['vScreenTitle_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
                $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['vScreenButtonText_' . $db_master[$i]['vCode']])) {
                $vScreenButtonText = $_POST['vScreenButtonText_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['vProfileName_' . $db_master[$i]['vCode']])) {
                $vProfileName = $_POST['vProfileName_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['vShortProfileName_' . $db_master[$i]['vCode']])) {
                $vShortProfileName = $_POST['vShortProfileName_' . $db_master[$i]['vCode']];
            }
            $q = "INSERT INTO ";
            $where = '';
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iUserProfileMasterId` = '" . $id . "'";
            }
            $vtitleArr["vTitle_" . $db_master[$i]['vCode']] = $vTitle;
            $vSubTitleArr["vSubTitle_" . $db_master[$i]['vCode']] = $vSubTitle;
            $vHeadArr["vScreenHeading_" . $db_master[$i]['vCode']] = $vScreenHeading;
            $vScreenTitleArr["vScreenTitle_" . $db_master[$i]['vCode']] = $vScreenTitle;
            $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
            $buttonTxtArr["vScreenButtonText_" . $db_master[$i]['vCode']] = $vScreenButtonText;
            $profileNameArr["vProfileName_" . $db_master[$i]['vCode']] = $vProfileName;
            $profileShortNameArr["vShortProfileName_" . $db_master[$i]['vCode']] = $vShortProfileName;
        }
        $time = time();
        if (scount($vtitleArr) > 0) {
            $updateProfileImg = "";
            $img_path = $tconfig["tsite_upload_profile_master_path"];
            $Photo_Gallery_folder = $img_path . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            if ($where != "") {
                $sql = "SELECT vImage,vWelcomeImage FROM " . USER_PROFILE_MASTER . " $where";
                $img_data = $obj->MySQLSelect($sql);
            }
            if (isset($_FILES['vImage']) && $_FILES['vImage']['name'] != "") {
                $image_object = $_FILES['vImage']['tmp_name'];
                $image_name = $time . "_vImage_" . $_FILES['vImage']['name'];
                $vImage = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                if (scount($img_data) > 0) {
                    if ($img_data[0]['vImage'] != "") {
                        $vImagePath = $Photo_Gallery_folder . $img_data[0]['vImage'];
                        unlink($vImagePath);
                    }
                }
                $updateProfileImg .= ",`vImage` = '" . $vImage . "'";
            }
            if (isset($_FILES['vWelcomeImage']) && $_FILES['vWelcomeImage']['name'] != "") {
                if (scount($img_data) > 0) {
                    if ($img_data[0]['vWelcomeImage'] != "") {
                        $welComeImgPath = $Photo_Gallery_folder . $img_data[0]['vWelcomeImage'];
                        unlink($welComeImgPath);
                    }
                }
                $image_object1 = $_FILES['vWelcomeImage']['tmp_name'];
                $wel_image_name = $time . "_vWelcomeImage_" . $_FILES['vWelcomeImage']['name'];
                $welComeImg = $UPLOAD_OBJ->GeneralUploadImage($image_object1, $wel_image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                $updateProfileImg .= ",`vWelcomeImage` = '" . $welComeImg . "'";
            }
            // changes by sunita
            /*$jsonTitle = $obj->cleanQuery(json_encode($vtitleArr));
            $jsonSubTitle = $obj->cleanQuery(json_encode($vSubTitleArr));
            $jsonHead = $obj->cleanQuery(json_encode($vHeadArr));
            $jsonScreenTitle = $obj->cleanQuery(json_encode($vScreenTitleArr));
            $jsonDesc = $obj->cleanQuery(json_encode($descArr));
            $jsonButtonTxt = $obj->cleanQuery(json_encode($buttonTxtArr));
            $jsonProfile = $obj->cleanQuery(json_encode($profileNameArr));
            $jsonProfileShort = $obj->cleanQuery(json_encode($profileShortNameArr));*/

            $jsonTitle = getJsonFromAnArr($vtitleArr);
            $jsonSubTitle = getJsonFromAnArr($vSubTitleArr);
            $jsonHead = getJsonFromAnArr($vHeadArr);
            $jsonScreenTitle = getJsonFromAnArr($vScreenTitleArr);
            $jsonDesc = getJsonFromAnArr($descArr);
            $jsonButtonTxt = getJsonFromAnArr($buttonTxtArr);
            $jsonProfile = getJsonFromAnArr($profileNameArr);
            $jsonProfileShort = getJsonFromAnArr($profileShortNameArr);

            $query = $q . " `" . USER_PROFILE_MASTER . "` SET `vTitle` = '" . $jsonTitle . "',`vSubTitle` = '" . $jsonSubTitle . "',`vScreenHeading` = '" . $jsonHead . "',`vScreenTitle` = '" . $jsonScreenTitle . "',`tDescription` = '" . $jsonDesc . "',`vScreenButtonText` = '" . $jsonButtonTxt . "',`vProfileName` = '" . $jsonProfile . "',`vShortProfileName` = '" . $jsonProfileShort . "' $updateProfileImg" . $where;
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
        }
        if ($action == "Add") {
            $_SESSION['var_msg'] = $langage_lbl['LBL_RECORD_INSERT_MSG'];
            $_SESSION['success'] = "1";
            header("Location:user_profile_master.php");
            exit;
        } else {
            $_SESSION['var_msg'] = $langage_lbl['LBL_Record_Updated_successfully'];
            $_SESSION['success'] = "1";
            header("Location:user_profile_master.php");
            exit;
        }
    }
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . USER_PROFILE_MASTER . " WHERE iUserProfileMasterId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";
    //print_R($db_data);die;
    if (scount($db_data) > 0) {
        $vTitle = (array) json_decode($db_data[0]['vTitle']);
        foreach ($vTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $vSubTitle = (array) json_decode($db_data[0]['vSubTitle']);
        foreach ($vSubTitle as $key1 => $value1) {
            $userEditDataArr[$key1] = $value1;
        }
        $vScreenHeading = (array) json_decode($db_data[0]['vScreenHeading']);
        foreach ($vScreenHeading as $key2 => $value2) {
            $userEditDataArr[$key2] = $value2;
        }
        $vScreenTitle = (array) json_decode($db_data[0]['vScreenTitle']);
        foreach ($vScreenTitle as $key3 => $value3) {
            $userEditDataArr[$key3] = $value3;
        }
        $tDescription = (array) json_decode($db_data[0]['tDescription']);
        foreach ($tDescription as $key4 => $value4) {
            $userEditDataArr[$key4] = $value4;
        }
        $vScreenButtonText = (array) json_decode($db_data[0]['vScreenButtonText']);
        foreach ($vScreenButtonText as $key5 => $value5) {
            $userEditDataArr[$key5] = $value5;
        }
        $vProfileName = (array) json_decode($db_data[0]['vProfileName']);
        foreach ($vProfileName as $key6 => $value6) {
            $userEditDataArr[$key6] = $value6;
        }
        $vShortProfileName = (array) json_decode($db_data[0]['vShortProfileName']);
        foreach ($vShortProfileName as $key7 => $value7) {
            $userEditDataArr[$key7] = $value7;
        }
        if (isset($db_data[0]['vImage'])) {
            $vImage = $db_data[0]['vImage'];
        }
        if (isset($db_data[0]['vWelcomeImage'])) {
            $welComeImg = $db_data[0]['vWelcomeImage'];
        }
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$txtBoxNameArr = array("vProfileName", "vTitle", "vSubTitle", "vScreenHeading", "vScreenTitle", "vScreenButtonText", "vShortProfileName");
$lableArr = array("Organization type", "Profile Title", "Title Description", "Screen Heading", "Screen Title", "Button Text", "Profile Short Name");

$rideProfileFieldArr = array(
    array(
        'label'         => 'Organization Type',
        'field_name'    => 'vProfileName_',
        'modal_id'      => 'org_type_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Profile Title',
        'field_name'    => 'vTitle_',
        'modal_id'      => 'profile_title_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Title Description',
        'field_name'    => 'vSubTitle_',
        'modal_id'      => 'profile_subtitle_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Screen Heading',
        'field_name'    => 'vScreenHeading_',
        'modal_id'      => 'screen_heading_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Screen Title',
        'field_name'    => 'vScreenTitle_',
        'modal_id'      => 'screen_title_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Button Text',
        'field_name'    => 'vScreenButtonText_',
        'modal_id'      => 'button_text_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Profile Short Name',
        'field_name'    => 'vShortProfileName_',
        'modal_id'      => 'profile_shortname_Modal',
        'field_type'    => 'input_text'
    ),
    array(
        'label'         => 'Description',
        'field_name'    => 'tDescription_',
        'modal_id'      => 'desc_Modal',
        'field_type'    => 'textarea'
    ),
);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Ride Profile Type <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                            <h2> Ride Profile Type </h2>
                            <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable ">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } else if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $_REQUEST['varmsg']; ?> 
                                </div><br/>	
                            <? } ?>
                            <? if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record  Not Updated .
                                </div><br/>
                            <? } ?>                   
                            <form id="_rideProfile_form" name="_rideProfile_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="user_profile_master.php"/>
                                <div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>

                                <?php if (scount($db_master) > 1) { ?>
                                <?php 
                                foreach ($rideProfileFieldArr as $rideProfileField) { 
                                    $fieldLabel = $rideProfileField['label'];
                                    $fieldName = $rideProfileField['field_name'];
                                    $modal_id = $rideProfileField['modal_id'];
                                    $fieldType = $rideProfileField['field_type'];
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $fieldLabel ?> <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <?php if($fieldType == "input_text") { ?>
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="<?= $fieldName ?>Default" name="<?= $fieldName ?>Default" value="<?= isset($userEditDataArr[$fieldName.$default_lang]) ? $userEditDataArr[$fieldName.$default_lang] : ''; ?>" data-originalvalue="<?= isset($userEditDataArr[$fieldName.$default_lang]) ? $userEditDataArr[$fieldName.$default_lang] : ''; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editRideProfile('Add', '<?= $modal_id ?>')" <?php } ?>>
                                        <?php } else { ?>
                                        <textarea class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="<?= $fieldName ?>Default" name="<?= $fieldName ?>Default" readonly="readonly" <?php if($id == "") { ?> onclick="editRideProfile('Add', '<?= $modal_id ?>')" <?php } ?> data-originalvalue="<?= isset($userEditDataArr[$fieldName.$default_lang]) ? $userEditDataArr[$fieldName.$default_lang] : ''; ?>"><?= isset($userEditDataArr[$fieldName.$default_lang]) ? $userEditDataArr[$fieldName.$default_lang] : ''; ?></textarea>
                                        <?php } ?>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editRideProfile('Edit', '<?= $modal_id ?>')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="<?= $modal_id ?>" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> <?= $fieldLabel ?>
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, '<?= $fieldName ?>')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = $fieldName . $vCode;
                                                        $$vValue = $userEditDataArr[$vValue];

                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if($EN_available) {
                                                                if($vCode == "EN") { 
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            } else { 
                                                                if($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label><?= $fieldLabel ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <?php if($fieldType == "input_text") { ?>
                                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                <?php } else { ?>
                                                                <textarea class="form-control" name="<?= $vValue; ?>" id="<?= $vValue ?>" placeholder="<?= $vTitle; ?> Value" data-originalvalue="<?= $$vValue; ?>"><?= $$vValue; ?></textarea>
                                                                <?php } ?>
                                                                <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="col-lg-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?= $fieldName; ?>', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-lg-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?= $fieldName; ?>', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveRideProfile('<?= $fieldName; ?>', '<?= $modal_id; ?>')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, '<?= $fieldName ?>')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php } else { ?>
                                    <?php 
                                        foreach ($rideProfileFieldArr as $rideProfileField) { 
                                            $fieldLabel = $rideProfileField['label'];
                                            $fieldName = $rideProfileField['field_name'];
                                            $modal_id = $rideProfileField['modal_id'];
                                            $fieldType = $rideProfileField['field_type'];
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $fieldLabel ?> <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <?php if($fieldType == "input_text") { ?>
                                                <input type="text" class="form-control" id="<?= $fieldName.$default_lang ?>" name="<?= $fieldName.$default_lang ?>" value="<?= $userEditDataArr[$fieldName.$default_lang]; ?>" required>
                                                <?php } else { ?>
                                                <textarea class="form-control" id="<?= $fieldName.$default_lang ?>" name="<?= $fieldName.$default_lang ?>" required><?= $userEditDataArr[$fieldName.$default_lang]; ?></textarea>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } 
                                } ?>
                                <?/*
                                if (scount($db_master) > 0) {
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
                                                    <span data-toggle="modal" data-target="#myModal"><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Click to See,Where it is used?" ></i></span>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                    <div class="text-danger" id="<?= $lableName.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                </div>
                                                <? if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                    <div class="col-md-6 col-sm-6">
                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<? echo $txtBoxNameArr[$l].'_'; ?>', '<?= $default_lang ?>');">Convert To All Language</button>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <? } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                <span data-toggle="modal" data-target="#myModal"><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Click to See,Where it is used?" ></i></span>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>><?= $userEditDataArr[$descVal]; ?></textarea>
                                                <div class="text-danger" id="<?= $descVal.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                            <? if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                <div class="col-md-6 col-sm-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <?
                                    }
                                }*/
                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Profile Icon</label>
                                        <span data-toggle="modal" data-target="#myModal"><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Click to See,Where it is used?" ></i></span>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <?
                                        $rand = rand(1000, 9999);
                                        if ($vImage != '') {
                                            ?>
                                            <img src="<?= $tconfig['tsite_upload_images_profile_master'] . "/" . $vImage . "?dm=$rand"; ?>" style="width:100px;height:100px;">
                                        <? } ?>
                                        <!-- <input type="file" accept="image/jpg, image/jpeg, image/png, image/gif, image/bmp" class="form-control" name="vImage"  id="vImage" placeholder="Name Label" style="padding-bottom: 39px;"> -->
                                        <input type="file" class="form-control" name="vImage"  id="vImage" placeholder="Name Label" style="padding-bottom: 39px;">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>WelCome Picture</label>
                                        <span data-toggle="modal" data-target="#myModal"><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Click to See,Where it is used?" ></i></span>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <?
                                        $rand = rand(1000, 9999);
                                        if ($welComeImg != '') {
                                            ?>
                                            <img src="<?= $tconfig['tsite_upload_images_profile_master'] . "/" . $welComeImg . "?dm=$rand"; ?>" style="width:100px;height:100px;">
                                        <? } ?>
                                        <!-- <input type="file" accept="image/jpg, image/jpeg, image/png, image/gif, image/bmp" class="form-control" name="vWelcomeImage"  id="vWelcomeImage" placeholder="Name Label" style="padding-bottom: 39px;"> -->
                                        <input type="file" class="form-control" name="vWelcomeImage"  id="vWelcomeImage" placeholder="Name Label" style="padding-bottom: 39px;">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <?php if (($action == 'Edit' && $userObj->hasPermission('edit-profile-taxi-service')) || ($action == 'Add' && $userObj->hasPermission('create-profile-taxi-service'))) { ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Profile" >
                                        <input type="reset" value="Reset" class="btn btn-default">
                                    <?php } ?>
                                    <a href="user_profile_master.php" class="btn btn-default back_link">Cancel</a>
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
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-large">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel"> Where it used?</h4>
                    </div>
                    <div class="modal-body">
                        <b>
                            <img src="images/org_img1.png" align="center">
                            <img style="margin:0 0 0 30px" src="images/org_img2.png" align="center">
                            <img style="margin:10px 0 0 0" src="images/org_img3.png" align="center">
                        </b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <? include_once('footer.php'); ?>
        <script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
        <script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
        <script type="text/javascript" src="js/form-validation.js" ></script>
        <script>
                                            // just for the demos, avoids form submit
                                            if (_system_script == 'VehicleType') {
                                                if ($('#_vehicleType_form').length !== 0) {
                                                    $("#_vehicleType_form").validate({
                                                        rules: {
                                                            fDeliveryCharge: {
                                                                required: true,
                                                                number: true,
                                                                min: 0
                                                            },
                                                            fDeliveryChargeCancelOrder: {
                                                                required: true,
                                                                number: true,
                                                                min: 0
                                                            },
                                                            fRadius: {
                                                                required: true,
                                                                number: true,
                                                                min: 0
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                            jQuery.extend(jQuery.validator.messages, {
                                                number: "Please enter a valid number.",
                                                min: jQuery.validator.format("Please enter a value greater than 0.")
                                            });
        </script>		
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
                    referrer = "user_profile_master.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });

            function editRideProfile(action, modal_id)
            {
                $('#modal_action').html(action);
                $('#'+modal_id).modal('show');
            }

            function saveRideProfile(field_name, modal_id)
            {
                if($('#'+field_name+'<?= $default_lang ?>').val() == "") {
                    $('#'+field_name+'<?= $default_lang ?>_error').show();
                    $('#'+field_name+'<?= $default_lang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        $('#'+field_name+'<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#'+field_name+'Default').val($('#'+field_name+'<?= $default_lang ?>').val());
                $('#'+field_name+'Default').closest('.row').removeClass('has-error');
                $('#'+field_name+'Default-error').remove();
                $('#'+modal_id).modal('hide');
            }
        </script>
    </body>
    <!-- END BODY-->
</html>
