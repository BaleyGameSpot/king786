<?php
    include_once('../common.php');
    
    $script = 'MasterLanguagePages';
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
    $var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
    $action = ($id != '') ? 'Edit' : 'Add';
    $tbl_name = 'master_lng_pages';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    
    $vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
    $tFileName = isset($_POST['tFileName']) ? $_POST['tFileName'] : '';
    $tFilePath = isset($_POST['tFilePath']) ? $_POST['tFilePath'] : '';
    $ePlatformType = isset($_POST['ePlatformType']) ? $_POST['ePlatformType'] : '';
    $eFor = isset($_POST['eFor']) ? $_POST['eFor'] : '';
    
    if (isset($_POST['submit'])) {
        //echo "<pre>"; print_r($_FILES); exit;
        if ($action == "Edit" && !$userObj->hasPermission('edit-general-label')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to update Language Label.';
            header("Location:language_page_action.php");
            exit;
        }
        
        if ($id == '') {
            $db_label_check = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE tFilePath = '" . $tFilePath . "'");
            if (scount($db_label_check) > 0) {
                $var_msg = "Language page already exists in master language pages.";
                header("Location:language_page_action.php?var_msg=" . $var_msg . '&success=0');
                exit;
            }
        }
        if (SITE_TYPE == 'Demo') {
            header("Location:language_page_action.php?id=" . $id . '&success=2');
            exit;
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iPageId` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET
            `vTitle` = '" . $vTitle . "',
            `tFileName` = '" . $tFileName . "',
            `tFilePath` = '" . $tFilePath . "',
            `ePlatformType` = '" . $ePlatformType . "',
            `eFor` = '" . $eFor . "'"
                . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
        // for image upload
        if ($_FILES['vImage']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_lng_page_path"];

            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $filecheck = basename($_FILES['vImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            }

            $dataimg = getimagesize($_FILES['vImage']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];
            if ($imgwidth < 1024) {
                //echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }
            $check_file = $obj->sql_query("SELECT vImage FROM ".$tbl_name." WHERE iPageId=" . $id);
            $oldImage = $check_file[0]['vImage'];
            $check_file = $img_path . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $oldImage);
            }
            //echo $flag_error;die;
            if ($flag_error == 1) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;
                header("location:language_page_action.php?id=".$id);
            } else {
                if (!is_dir($temp_gallery)) {
                    mkdir($temp_gallery, 0777);
                }
                $img1 = $UPLOAD_OBJ->GeneralFileUpload($temp_gallery, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $vImage = $img1[0];
                $obj->sql_query("UPDATE $tbl_name SET `vImage` = '" . $vImage . "' WHERE `iPageId` = '" . $id . "'");
            }
        }
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        header("location:" . $backlink);
    }
    // Add Label
    if(isset($_POST['submitAddLabel']))
    {
        //echo "<pre>"; print_r($_POST); exit;
        $lPage_id = $id;
        $vLabel = $_POST['vLabel'];
        $eAppType = $_POST['eAppType'];
        $eServiceCategory = $_POST['eServiceCategory'];
        $vValue = isset($_POST['vValue']) ? $_POST['vValue'] : '';
        $iServiceId = 0;

        if(startsWith($vLabel,"LBL_") == false){
            $_SESSION['success'] = '0';
            $_SESSION['var_msg'] = "Lable must be start with 'LBL_'";
            header("Location:language_page_action.php?id=" . $id);
            exit;
        }
        
        if(!preg_match('/^[A-Z_]+$/', $vLabel)){
            $_SESSION['success'] = '0';
            $_SESSION['var_msg'] = "Only Capital Letters and Underscores are allowed.";
            header("Location:language_page_action.php?id=" . $id);
            exit;
        }

        $table_name = "app_screen_language_label";
        $lbl_ins_arr = array();
        $lbl_ins_arr['lPage_id'] = $lPage_id;
        $lbl_ins_arr['vCode'] = "EN";
        $lbl_ins_arr['vLabel'] = $vLabel;
        $lbl_ins_arr['vValue'] = $vValue;
        $lbl_ins_arr['eAppType'] = $eAppType;
        $lbl_ins_arr['iServiceId'] = 0;

        if($eAppType == "DeliverAll")
        {
            if($eServiceCategory == "Others")
            {
                $vValueServiceCategories = $_POST['vValueServiceCategory'];
                foreach ($vValueServiceCategories as $key => $scValue) 
                {
                    $iServiceId = $key;
                    $sqlLngExistData = $obj->MySQLSelect("SELECT vLabel FROM app_screen_language_label WHERE vLabel LIKE '".$vLabel."' AND vCode LIKE 'EN' AND iServiceId = ".$iServiceId);
                    if(scount($sqlLngExistData) > 0)
                    {
                        $_SESSION['success'] = '0';
                        $_SESSION['var_msg'] = "Language label already exists.";
                        header("Location:language_page_action.php?id=" . $id);
                        exit;
                    }
                    $lbl_ins_arr['vValue'] = $scValue;
                    $lbl_ins_arr['iServiceId'] = $iServiceId;
                    $obj->MySQLQueryPerform($table_name, $lbl_ins_arr, 'insert');
                }
            }
            else{
                $sqlLngExistData = $obj->MySQLSelect("SELECT vLabel FROM app_screen_language_label WHERE vLabel LIKE '".$vLabel."' AND vCode LIKE 'EN' AND iServiceId = ".$iServiceId);
                if(scount($sqlLngExistData) > 0)
                {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = "Language label already exists.";
                    header("Location:language_page_action.php?id=" . $id);
                    exit;
                }

                $lbl_ins_arr['vValue'] = $vValue;
                $lbl_ins_arr['eAppType'] = "General";
                $obj->MySQLQueryPerform($table_name, $lbl_ins_arr, 'insert');

                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
                header("Location:language_page_action.php?id=" . $id); 
            }
        }
        else{
            $sqlLngExistData = $obj->MySQLSelect("SELECT vLabel FROM app_screen_language_label WHERE vLabel LIKE '".$vLabel."' AND vCode LIKE 'EN' AND iServiceId = ".$iServiceId);
            if(scount($sqlLngExistData) > 0)
            {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = "Language label already exists.";
                header("Location:language_page_action.php?id=" . $id);
                exit;
            }
            $lbl_ins_arr['vValue'] = $vValue;
            $lbl_ins_arr['eAppType'] = "General";
            $obj->MySQLQueryPerform($table_name, $lbl_ins_arr, 'insert');
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            header("Location:language_page_action.php?id=" . $id);   
        }
    }
    // for Edit
    if ($action == 'Edit') {
        $db_data = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE iPageId = '" . $id . "'");
        $vTitle = $db_data[0]['vTitle'];
        $tFileName = $db_data[0]['tFileName'];
        $tFilePath = $db_data[0]['tFilePath'];
        $ePlatformType = $db_data[0]['ePlatformType'];
        $eFor = $db_data[0]['eFor'];
        $vImage = $db_data[0]['vImage'];
        $db_data_lng = $obj->MySQLSelect("SELECT LanguageLabelId,lPage_id,vLabel,vValue,eFor,eAppType FROM app_screen_language_label WHERE lPage_id = ".$id);
        $sqlScData = $obj->MySQLSelect("SELECT iServiceId, vServiceName_EN FROM service_categories WHERE eStatus = 'Active'");
    }
    $_SESSION['success'] == isset($_SESSION['success']) ? $_SESSION['success'] : '';
    $_SESSION['var_msg'] == isset($_SESSION['var_msg']) ? $_SESSION['var_msg'] : '';
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
                <title>Admin | Language Label Page <?= $action; ?></title>
                <meta content="width=device-width, initial-scale=1.0" name="viewport" />
                <? include_once('global_files.php'); ?>
                <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
                <script type="text/javascript" src="js/plugins/select2.min.js"></script>
                <style type="text/css">
                    .lng-page-img {
                        width: 100%;
                        height: 267px;
                        object-fit: cover;
                        margin-top: 20px
                    }

                    .input-group-btn button {
                        padding: 7px 12px
                    }

                    .input-group input {
                        border-right: none;
                    }

                    .close-modal {
                        position: absolute;
                        right: -5px;
                        z-index: 8;
                        top: 15px;
                        cursor: pointer;
                    }
                    .form-control[readonly] {
                        cursor: default;
                        background-color: #ffffff; 
                    }

                    .modal #allLanguage i {
                        font-size: 18px;
                        margin: -1px 0 0 5px;
                        vertical-align: middle;
                    }
                    .nav-pills > li > a {
                        color: #000000;
                        border-radius: 0; 
                    }
                    .nav-pills a:hover {
                        background-color: #eeeeee;
                    }

                    .nav-pills > li.active > a {
                        color: #000000;
                        background-color: rgba(0,0,0,.2);
                    }

                    .nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
                        background-color: rgba(0,0,0,.2);
                        color: #000000;
                    }

                    .modal-body-form {
                        overflow-y: auto;
                        overflow-x: hidden;
                    }

                    body.modal-open {
                        overflow: hidden;
                    }

                    .modal {
                      text-align: center;
                    }

                    @media screen and (min-width: 768px) { 
                      .modal:before {
                        display: inline-block;
                        vertical-align: middle;
                        content: " ";
                        height: 100%;
                      }
                    }

                    .modal-dialog {
                      display: inline-block;
                      text-align: left;
                      vertical-align: middle;
                    }

                    .modal-body {
                        max-height: calc(100vh - 200px);
                        overflow-y: auto;
                        margin-right: 0;
                        padding-right: 10px;
                        overflow-x: hidden;
                        margin-bottom: 20px
                    }

                    .modal-body .modal-body-form .row:last-child {
                        padding-bottom: 0
                    }

                    .loding-action, .loding-action-new {
                        left: 0;
                        margin: auto;
                        position: fixed;
                        right: 0;
                        top: 0;
                        bottom: 0;
                        height: 100%;
                    }

                    .loding-action div, .loding-action-new div {
                        left: 50%;
                        position: absolute;
                        top: 50%;
                        transform: translate(-50%, -50%);
                    }
                </style>
            </head>
            <!-- END  HEAD-->
            <!-- BEGIN BODY-->
            <body class="padTop53 " >
                <!-- MAIN WRAPPER -->
                <div id="wrap">
                    <? include_once('header.php'); ?>
                    <? include_once('left_menu.php'); ?>
                    <!--PAGE CONTENT -->
                    <div id="content">
                        <div class="inner">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h2><?= $action; ?> Language - Pages</h2>
                                    <a href="master_lng_pages.php" class="back_link">
                                    <input type="button" value="Back to Listing" class="add-btn">
                                    </a>
                                </div>
                            </div>
                            <hr />
                            <div class="body-div">
                                <div class="form-group">
                                    <?php if($_SESSION['success'] != "") { ?>
                                    <? if ($_SESSION['success'] == 1) { ?>
                                    <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                    </div>
                                    <br/>
                                    <? } elseif ($_SESSION['success'] == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                    </div>
                                    <br/>
                                    <? } elseif ($_SESSION['success'] == 0 && $_SESSION['var_msg'] != '') { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?= stripslashes($var_msg); ?>
                                    </div>
                                    <? } unset($_SESSION['success']); unset($_SESSION['var_msg']); } ?>
                                    <form method="post" name="_languages_pages_form" id="_languages_pages_form" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="master_lng_pages.php"/>
                                        
                                        <?php if($action == "Edit") { ?>
                                        <a href="javascript:void(0);" class="add-btn" id="add_label_btn" data-toggle="modal" data-target="#add_label_modal">Add Label</a>

                                        <ul class="nav nav-tabs">
                                            <li class="active"><a data-toggle="tab" href="#languageLabels">Language Labels</a></li>
                                            <li><a data-toggle="tab" href="#General">General</a></li>
                                        </ul>
                                        <div class="tab-content" style="margin-bottom: 20px">
                                            <div id="languageLabels" class="tab-pane active">
                                                <div class="row">
                                                    <?php 
                                                        if(scount($db_data_lng) > 0) { 
                                                            $eForArr = array();
                                                            foreach ($db_data_lng as $dvalue) {
                                                                if(!in_array($dvalue['eAppType'], $eForArr))
                                                                {
                                                                    $eForArr[] = $dvalue['eAppType'];
                                                                }
                                                            }
                                                    ?>

                                                    <div class="col-lg-12 pull-left">
                                                        <ul class="nav nav-pills pull-right">
                                                            <li class="lngEfor active" data-value="All">
                                                                <a href="javascript:void(0);">All</a>
                                                            </li>
                                                            <?php foreach ($eForArr as $eForVal) { ?>
                                                            <li class="lngEfor" data-value="<?= $eForVal ?>">
                                                                <a href="javascript:void(0);"><?= $eForVal ?></a>
                                                            </li>    
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <h3>Language Code</h3>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <h3>Language Code Value</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <?php foreach ($db_data_lng as $dblng_value) { ?>
                                                        <div class="row lngLabels" data-type="<?= $dblng_value['eAppType'] ?>">
                                                            <div class="col-lg-6">
                                                                <input type="text" class="form-control"  value="<?= $dblng_value['vLabel']; ?>" placeholder="Language Label" disabled>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control edit_lng_page_btn" value="<?= $dblng_value['vValue']; ?>" readonly data-id="<?= $dblng_value['LanguageLabelId'] ?>">
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-primary edit_lng_page_btn" type="button" data-id="<?= $dblng_value['LanguageLabelId'] ?>">Edit</button>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    </div>
                                                    <?php } else {
                                                    ?>
                                                    <div class="text-center">No Language Code found.</div>
                                                    <?php } ?>
                                                </div>
                                                <div class="modal fade" tabindex="-1" role="dialog" id="lang_code_modal" data-backdrop="static">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="close-modal" data-dismiss="modal">
                                                            <img src="img/cancel-icon.png">
                                                        </div>
                                                        <div class="modal-content">
                                                            <div class="modal-body">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div id="General" class="tab-pane">
                                                <?php } ?>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Page Title<span class="red"> *</span></label>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <input type="text" class="form-control" name="vTitle"  id="vTitle" value="<?= $vTitle; ?>" placeholder="Language Page Title" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>File Name<span class="red"> *</span></label>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <input type="text" class="form-control" name="tFileName"  id="tFileName" value="<?= $tFileName; ?>" placeholder="File Name" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>File Path</label>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <input type="text" class="form-control" name="tFilePath"  id="tFilePath" value="<?= $tFilePath; ?>" placeholder="File Path">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Platform Type</label>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <select class="form-control" name="ePlatformType" id="ePlatformType" required>
                                                                    <option value="Web" <?= ($ePlatformType == "Web") ? "selected" : "" ?>>Web</option>
                                                                    <option value="App" <?= ($ePlatformType == "App") ? "selected" : "" ?>>App</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Applicable Module<span class="red"> *</span></label>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <select class="form-control" name="eFor" id="eFor" required>
                                                                    <option value="" <?= ($eFor == "") ? "selected" : "" ?>>Select App Type</option>
                                                                    <option value="General" <?= ($eFor == "General") ? "selected" : "" ?>>General</option>
                                                                    <option value="Ride" <?= ($eFor == "Ride") ? "selected" : "" ?>>Ride</option>
                                                                    <option value="Delivery" <?= ($eFor == "Delivery") ? "selected" : "" ?>>Delivery</option>
                                                                    <option value="Ride,Delivery" <?= ($eFor == "Ride,Delivery") ? "selected" : "" ?>>Ride,Delivery</option>
                                                                    <option value="UberX" <?= ($eFor == "UberX") ? "selected" : "" ?>>UberX</option>
                                                                    <option value="Ride,Delivery,UberX" <?= ($eFor == "Ride,Delivery,UberX") ? "selected" : "" ?>>Ride,Delivery,UberX</option>
                                                                    <option value="Ride-Delivery-UberX" <?= ($eFor == "Ride-Delivery-UberX") ? "selected" : "" ?>>Ride-Delivery-UberX</option>
                                                                    <option value="Multi-Delivery" <?= ($eFor == "Multi-Delivery") ? "selected" : "" ?>>Multi-Delivery</option>
                                                                    <option value="DeliverAll" <?= ($eFor == "DeliverAll") ? "selected" : "" ?>>DeliverAll</option>
                                                                    <option value="Kiosk" <?= ($eFor == "Kiosk") ? "selected" : "" ?>>Kiosk</option>
                                                                    <option value="Fly" <?= ($eFor == "Fly") ? "selected" : "" ?>>Fly</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <?php if($vImage != "") { ?>
                                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=750&src='.$tconfig['tsite_upload_images_lng_page'] . "/" . $vImage; ?>" alt="" class="lng-page-img">
                                                                <?php } else { ?>
                                                                <img src="../assets/img/placeholder-img.png" alt="" class="lng-page-img">
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Page Screenshot</label>
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <input type="file" class="form-control" name="vImage" id="vImage">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if($action == "Edit") { ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="row" style="padding-bottom: 0">
                                            <div class="col-lg-12">
                                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-general-label')) || ($action == 'Add')) { ?>
                                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Page">
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <?php } ?>
                                                <a href="master_lng_pages.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="modal fade" tabindex="-1" role="dialog" id="add_label_modal">
                        <div class="modal-dialog" role="document">
                            <div class="close-modal" data-dismiss="modal">
                                <img src="img/cancel-icon.png">
                            </div>
                            <div class="modal-content">
                                <div class="modal-body">
                                    <form method="post" name="_languages_form" id="_languages_form" action="" class="form-group">
                                        <div class="row" id="errorMessageRowAdd" style="display: none;">
                                            <div class="col-lg-12" id="errorMessageAdd">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label for="vLabel">Language Label <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vLabel"  id="vLabel" placeholder="Language Label">
                                                <small class="text-danger" style="display: none;">Required</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label for="eAppType">Lable For<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select name="eAppType" id="eAppType" class="form-control" required="required">
                                                    <option value="General">General</option>
                                                    <?php if($eFor == "Ride") { ?>
                                                    <option value="Ride">Ride</option>
                                                    <?php } if($eFor == "Delivery") { ?>
                                                    <option value="Delivery">Delivery</option>
                                                    <?php } if($eFor == "UberX") { ?>
                                                    <option value="UberX">UberX</option>
                                                    <?php } if($eFor == "DeliverAll") { ?>
                                                    <option value="DeliverAll">DeliverAll</option>
                                                    <?php } if($eFor == "Kiosk") { ?>
                                                    <option value="Kiosk">Kiosk</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row" id="eServiceCategoryRow" style="display: none;">
                                            <div class="col-lg-12">
                                                <label for="eServiceCategory">Lable For Service Category<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select name="eServiceCategory" id="eServiceCategory" class="form-control">
                                                    <option value="General">General</option>
                                                    <option value="Others">Others</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row" id="vValueRow">
                                            <div class="col-lg-12">
                                                <label for="vValue">Label value (English)<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vValue" id="vValue" placeholder="Label value (English)">
                                                <small class="text-danger" style="display: none;">Required</small>
                                            </div>
                                        </div>

                                        <div id="serviceCategoriesRow" style="display: none;">
                                            <?php foreach ($sqlScData as $sc) { ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label for="vValue_<?= $sc['iServiceId'] ?>">Label value for <?= $sc['vServiceName_EN'] ?><span class="red"> *</span></label>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="vValueServiceCategory[<?= $sc['iServiceId'] ?>]" id="vValue_<?= $sc['iServiceId'] ?>" placeholder="Label value for <?= $sc['vServiceName_EN'] ?>">
                                                        <small class="text-danger" style="display: none;">Required</small>
                                                    </div>
                                                </div>    
                                            <?php } ?>
                                            
                                        </div>
                                        <input type="hidden" name="submitAddLabel" value="1">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-default" id="submitAddLabel">Add Label</button>
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row loding-action" id="loaderIcon" style="display:none;z-index: 9999">
                        <div align="center">                            
                            <img src="default.gif">                                              
                            <span>Language Translation is in Process. Please Wait...</span>
                        </div>                                                                                 
                    </div>
                    <div class="row loding-action-new" id="loadingIcon" style="display:none;z-index: 9999">
                        <div align="center">                            
                            <img src="default.gif">
                        </div>                                                                                 
                    </div>
                    <!--END PAGE CONTENT -->
                </div>
                <!--END MAIN WRAPPER -->
                <? include_once('footer.php'); ?>
            </body>
            <!-- END BODY-->
        </html>
        <script type="text/javascript" language="javascript">
            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;    
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "master_lng_pages.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
                $('#service_categories').select2();
            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('.lng-page-img').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#vImage").change(function() {
                readURL(this);
            });

            $('.edit_lng_page_btn').click(function() {
                var curr_elem = $(this);
                var id = curr_elem.data('id');
                $('#loadingIcon').show();
                $('body').css('overflow', 'hidden');

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_master_lng_pages.php',
                    'AJAX_DATA': {id: id},
                    'REQUEST_DATA_TYPE': 'html'
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $('#loadingIcon').hide();
                        $('body').css('overflow', 'auto');
                        $('#lang_code_modal').find('.modal-body').html(data);
                        $('#lang_code_modal').modal('show');
                    }
                    else {
                        console.log(response.result);
                        $('#loadingIcon').hide();
                    }
                });
            });


            $('.lngEfor').click(function() {
                $('.lngEfor').removeClass('active');
                $(this).addClass('active');
                var eForVal = $(this).data('value');
                $('.lngLabels').hide();
                if(eForVal == "All")
                {
                    $('.lngLabels').show();
                }
                else{
                    $('.lngLabels').each(function(key, value) {
                        if($(this).data('type') == eForVal)
                        {
                            $(this).show();
                        }
                        else{
                            $(this).hide();   
                        }
                    });
                }
            });

            $('#eAppType').change(function() {
                if($(this).val() == "DeliverAll")
                {
                    $('#eServiceCategoryRow').show();
                }
                else{
                    $('#vValueRow').show();
                    $('#serviceCategoriesRow, #eServiceCategoryRow').hide();
                }
            });

            $('#eServiceCategory').change(function() {
                if($(this).val() == "Others")
                {
                    $('#serviceCategoriesRow').show();
                    $('#vValueRow').hide();
                }
                else{
                    $('#vValueRow').show();
                    $('#serviceCategoriesRow').hide();   
                }
            });

            $('#submitAddLabel').click(function() {
                var error = "";
                if($('#vLabel').val() == "")
                {
                    $('#vLabel').closest('div.col-lg-12').find('small').show();
                    error = "1";
                }
                else{
                    $('#vLabel').closest('div.col-lg-12').find('small').hide();
                }

                if($('#eAppType').val() == "DeliverAll")
                {
                    if($('#eServiceCategory').val() == "Others")
                    {
                        $('[name="vValueServiceCategory[]"]').each(function () {
                            if($(this).val() == "")
                            {
                                $(this).closest('div.col-lg-12').find('small').show();
                                error += "1";
                            }
                            else{
                                $(this).closest('div.col-lg-12').find('small').hide();
                            }
                        })
                    }
                    else{
                        if($('#vValue').val() == "")
                        {
                            $(this).closest('div.col-lg-12').find('small').show();
                            error += "1";
                        }
                        else{
                            $(this).closest('div.col-lg-12').find('small').hide();
                        }
                    }
                }
                else{
                    if($('#vValue').val() == "")
                    {
                        $('#vValue').closest('div.col-lg-12').find('small').show();
                        error += "1";
                    }
                    else{
                        $('#vValue').closest('div.col-lg-12').find('small').hide();
                    }
                }

                if(error == "")
                {
                    $('#submitAddLabel').closest('form').submit();
                }
                else{
                    return false;
                }
            });

            $('#vLabel, #vValue, [name="vValueServiceCategory[]"]').keyup(function() {
                if($(this).val() != "")
                {
                    $(this).closest('div.col-lg-12').find('small').hide();
                }
                else{
                    $(this).closest('div.col-lg-12').find('small').show();   
                }
            });
        </script>