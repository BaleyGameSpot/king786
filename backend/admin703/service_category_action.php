<?php
    include_once('../common.php');
    
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
    //$action   = ($id != '')?'Edit':'Add';
    $action = 'Edit';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $tbl_name = 'service_categories';
    $script = 'service_category';
    $vTitle_store = $tDescriptionArr = array();
    $sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
    $db_master = $obj->MySQLSelect($sql);
    $iDisplayOrder = 'iDisplayOrder';
    $$iDisplayOrder = isset($_POST[$iDisplayOrder]) ? $_POST[$iDisplayOrder] : '';
    $count_all = scount($db_master);
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
            array_push($vTitle_store, $vValue);
            $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
        }
    }
    // set all variables with either post (when submit) either blank (when insert)
    $eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
    $eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
    if ($id != "") {
        $sql = "SELECT iDisplayOrder FROM `service_categories` where iServiceId = '$id'";
        $displayOld = $obj->MySQLSelect($sql);
        $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
        if ($oldDisplayOrder > $iDisplayOrder) {
            $sql = "SELECT * FROM `service_categories` where iServiceId = '$id' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder + 1;
                for ($i = 0; $i < scount($db_orders); $i++) {
                    $query = "UPDATE service_categories SET iDisplayOrder = '$j' WHERE iServiceId = '" . $db_orders[$i]['iServiceId'] . "'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        } else if ($oldDisplayOrder < $iDisplayOrder) {
            $sql = "SELECT * FROM `service_categories` where iServiceId = '$iServiceId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $oldDisplayOrder;
                for ($i = 0; $i < scount($db_orders); $i++) {
                    $query = "UPDATE service_categories SET iDisplayOrder = '$j' WHERE iServiceId = '" . $db_orders[$i]['iServiceId'] . "'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        }
    } else {
        $sql = "SELECT * FROM `service_categories` WHERE iServiceId = '$iServiceId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);
    
        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < scount($db_orders); $i++) {
                $query = "UPDATE service_categories SET iDisplayOrder = '$j' WHERE iServiceId = '" . $db_orders[$i]['iServiceId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }
    if (isset($_POST['submit'])) {
        //echo "<pre>";print_r($_POST);die;
        if ($action == "Add" && !$userObj->hasPermission('create-service-category')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to create service category.';
            header("Location:service_category.php");
            exit;
        }
        if ($action == "Edit" && !$userObj->hasPermission('edit-service-category')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to update service category.';
            header("Location:service_category.php");
            exit;
        }
        if (SITE_TYPE == 'Demo') {
            header("Location:service_category_action.php?id=" . $id . '&success=2');
            exit;
        }
        $img_arr = $_FILES;
        for ($d = 0; $d < scount($db_master); $d++) {
            $tDescription = "";
            if (isset($_POST['tDescription_' . $db_master[$d]['vCode']])) {
                $tDescription = $_POST['tDescription_' . $db_master[$d]['vCode']];
            }
            $tDescriptionArr["tDescription_" . $db_master[$d]['vCode']] = $tDescription;
        }
        $tDescriptionArr = array();
        if (!empty($img_arr)) {
            foreach ($img_arr as $key => $value) {
                if (!empty($value['name'])) {
                    $img_path = $tconfig["tsite_upload_service_categories_images_path"];
                    $temp_gallery = $img_path . '/';
                    $image_object = $value['tmp_name'];
                    $image_name = $value['name'];
                    $check_file_query = "SELECT " . $key . " FROM service_categories where  iServiceId='" . $id . "'";
                    $check_file = $obj->MySQLSelect($check_file_query);
                    if ($message_print_id != "") {
                        $check_file = $img_path . '/' . $check_file[0][$key];
                        if ($check_file != '' && file_exists($check_file[0][$key])) {
                            // @unlink($check_file);
                        }
                    }
                    $Photo_Gallery_folder = $img_path . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif');
                    
                    if ($img[2] == "1") {
                        $_SESSION['success'] = '0';
                        $_SESSION['var_msg'] = $img[1];
                        header("location:" . $backlink);
                    }
                    if (!empty($img[0])) {
                        $sql = "UPDATE service_categories SET " . $key . " = '" . $img[0] . "' WHERE iServiceId = '" . $id . "'";
                        $obj->sql_query($sql);

                        if($THEME_OBJ->isDeliverallXv2ThemeActive() == "Yes") {
                            $obj->sql_query("UPDATE vehicle_category SET vListLogo2 = '" . $img[0] . "' WHERE iServiceId = '" . $id . "'");
                        }
                    }
                }
            }
        }

        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iServiceId` = '" . $id . "'";
        }
        $serviceName = "";
        for ($i = 0; $i < scount($vTitle_store); $i++) {
            $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
            $serviceName .= ",`" . $vValue . "`='" . $_POST[$vTitle_store[$i]] . "'";
        }
        $jsonServiceDesc = $obj->cleanQuery(json_encode($tDescriptionArr));
        $query = $q . " `" . $tbl_name . "` SET " . trim($serviceName, ",") . ",`tDescription`='" . $jsonServiceDesc . "',`iDisplayOrder` = '" . $iDisplayOrder . "'" . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();

        // Added by HV on 11-11-2020 for 18+ age verfication
        $eShowTerms = isset($_POST['eShowTerms']) ? $_POST['eShowTerms'] : 'No';
        if($MODULES_OBJ->isEnableTermsServiceCategories())
        {
            $update_service = "UPDATE `service_categories` SET eShowTerms = '" . $eShowTerms . "' WHERE iServiceId=" . $id;
            $obj->sql_query($update_service);
        }
        
        // Added by HV on 11-11-2020 for 18+ proof upload
        if($MODULES_OBJ->isEnableProofUploadServiceCategories())
        {
            $eProofUpload = isset($_POST['eProofUpload']) ? $_POST['eProofUpload'] : "No";
            $tProofNote = isset($_POST['tProofNote']) ? $_POST['tProofNote'] : "";
            $tProofNoteDriver = isset($_POST['tProofNoteDriver']) ? $_POST['tProofNoteDriver'] : "";
            $tProofNoteStore = isset($_POST['tProofNoteStore']) ? $_POST['tProofNoteStore'] : "";

            $Data_proof['eProofUpload'] = $eProofUpload;
            $Data_proof['tProofNote'] = $tProofNote;
            $Data_proof['tProofNoteDriver'] = $tProofNoteDriver;
            $Data_proof['tProofNoteStore'] = $tProofNoteStore;

            $where_proof = " iServiceId = $id";
            $obj->MySQLQueryPerform("service_categories", $Data_proof, 'update', $where_proof);    
        }

        // Added by HV on 12-02-2021 for OTP verfication feature
        $eOTPCodeEnable = isset($_POST['eOTPCodeEnable']) ? $_POST['eOTPCodeEnable'] : 'No';
        if($MODULES_OBJ->isEnableOTPVerificationDeliverAll())
        {
            $update_service = "UPDATE `service_categories` SET eOTPCodeEnable = '" . $eOTPCodeEnable . "' WHERE iServiceId=" . $id;
            $obj->sql_query($update_service);
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
    
    // for Edit
    if ($action == 'Edit') {
        $sql = "SELECT * FROM " . $tbl_name . " WHERE iServiceId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        //print_r($db_data);die;
        $vLabel = $id;
        if (scount($db_data) > 0) {
            $tDescription = (array) json_decode($db_data[0]['tDescription']);
            foreach ($tDescription as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
            for ($i = 0; $i < scount($db_master); $i++) {
                foreach ($db_data as $key => $value) {
                    $iServiceId = $value['iServiceId'];
                    $vValue = 'vServiceName_' . $db_master[$i]['vCode'];
                    $$vValue = $value[$vValue];
                    $eStatus = $value['eStatus'];
                    $iDisplayOrder = $value['iDisplayOrder'];
                    $Image = $value['vImage'];
                    $eOTPCodeEnable = $value['eOTPCodeEnable'];

                    $arrLang[$vValue] = $$vValue;
                }
            }
        }

        if($MODULES_OBJ->isEnableTermsServiceCategories())
        {
            $scsql = "select eShowTerms,eProofUpload,tProofNote,tProofNoteDriver,tProofNoteStore from service_categories WHERE iServiceId = ".$id;
            $scsqlData = $obj->MySQLSelect($scsql);
            $eShowTerms = $scsqlData[0]['eShowTerms'];
            $eProofUpload = $scsqlData[0]['eProofUpload'];
            
            $tProofNote = $scsqlData[0]['tProofNote'];
            $tProofNoteLang = json_decode($tProofNote, true);
            $tProofNoteLang = $tProofNoteLang['tProofNote_'.$default_lang];

            $tProofNoteDriver = $scsqlData[0]['tProofNoteDriver'];
            $tProofNoteDriverLang = json_decode($tProofNoteDriver, true);
            $tProofNoteDriverLang = $tProofNoteDriverLang['tProofNoteDriver_'.$default_lang];

            $tProofNoteStore = $scsqlData[0]['tProofNoteStore'];
            $tProofNoteStoreLang = json_decode($tProofNoteStore, true);
            $tProofNoteStoreLang = $tProofNoteStoreLang['tProofNoteStore_'.$default_lang];
        }
    }

    $EN_available = $LANG_OBJ->checkLanguageExist();
    $db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
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
                <title>Admin | DeliveryAll Service Category <?= $action; ?></title>
                <meta content="width=device-width, initial-scale=1.0" name="viewport" />
                <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
                <?php include_once('global_files.php'); ?>
                <!-- On OFF switch -->
                <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
                <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
                <style type="text/css">
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
                    }

                    .modal-body .form-group:last-child {
                        margin-bottom: 0
                    }

                    .loding-action {
                        left: 0;
                        margin: auto;
                        position: fixed;
                        right: 0;
                        top: 0;
                        bottom: 0;
                        height: 100%;
                        z-index: 9999;
                    }

                    .loding-action div {
                        left: 50%;
                        position: absolute;
                        top: 50%;
                        transform: translate(-50%, -50%);
                    }

                    #id_proof_note_lang .form-group {
                        padding-bottom: 0;
                    }

                    body.modal-open {
                        overflow: hidden;
                    }

                    textarea {
                        resize: vertical;
                    }
                </style>
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
                                    <h2><?= $action; ?> DeliveryAll Service Category</h2>
                                    <a href="service_category.php" class="back_link">
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
                                    </div>
                                    <br/>
                                    <?php } elseif ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                    </div>
                                    <br/>
                                    <?php } ?>
                                    <form method="post" name="_service_category_form" id="_service_category_form" action="" enctype='multipart/form-data'>
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="service_category.php"/>

                                        <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>DeliveryAll Service Category Name <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vServiceName_Default" value="<?= $arrLang['vServiceName_'.$default_lang]; ?>" data-originalvalue="<?= $arrLang['vServiceName_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editServiceCategory('Add')" <?php } ?>>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editServiceCategory('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="service_cat_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> DeliveryAll Service Category Name
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vServiceName_')">x</button>
                                                        </h4>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <?php
                                                            
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vServiceName_' . $vCode;
                                                                
                                                                $required = ($eDefault == 'Yes') ? 'required' : '';
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
                                                                        <label>DeliveryAll Service Category Name (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        
                                                                    </div>
                                                                    <div class="<?= $page_title_class ?>">
                                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                                        <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>
                                                                    <?php
                                                                    if (scount($db_master) > 1) {
                                                                        if($EN_available) {
                                                                            if($vCode == "EN") { ?>
                                                                            <div class="col-lg-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vServiceName_', 'EN');" >Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-lg-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vServiceName_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveServiceCategory()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vServiceName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>

                                        </div>
                                        <?php } else { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>DeliveryAll Service Category Name <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" id="vServiceName_<?= $default_lang ?>" name="vServiceName_<?= $default_lang ?>" value="<?= $arrLang['vServiceName_'.$default_lang]; ?>"  required>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php /*
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vServiceName_' . $vCode;
                                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    $tDescription = 'tDescription_' . $vCode;
                                                    $serviceDescValue = "";
                                                    if (isset($userEditDataArr[$tDescription])) {
                                                        $serviceDescValue = $userEditDataArr[$tDescription];
                                                    }
                                                    ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>DeliveryAll Service Category Name  (<?= $vTitle; ?>)<?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                            <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                            <div class="col-lg-6">
                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vServiceName_', '<?= $default_lang ?>');">Convert To All Language</button>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <!--<div class="row">
                                            <div class="col-lg-12">
                                                <label>DeliveryAll  Service Category Description (<?= $vTitle; ?>) </label>
                                            </div>
                                            <div class="col-lg-6">
                                                <textarea <?= $required; ?> class="form-control" name="<?= $tDescription; ?>" id="<?= $tDescription; ?>" placeholder="<?= $vTitle; ?> Value"><?= $serviceDescValue; ?></textarea>                                              
                                            </div>
                                            <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                </div>
                                            <?php } ?>
                                            </div>-->
                                        <?php
                                            }
                                            }*/
                                            ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <?php
                                                    if (!empty($Image)) {
                                                        ?>
                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=500&src='.$tconfig["tsite_upload_service_categories_images"] . '' . $Image; ?>" class="thumbnail" style="max-width: 250px; max-height: 250px"/>
                                                <?php } ?>
                                            </div>
                                            <div class="classfixed">&nbsp;</div>
                                            <div class="col-lg-6">
                                                <input type="file" class="form-control" name="vImage"  id="vImage" accept=".png,.jpg,.jpeg,.gif">
                                            </div>
                                        </div>
                                        <?php if($MODULES_OBJ->isEnableOTPVerificationDeliverAll()) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Ask OTP/Confirmation code at Products/Items Delivery <!-- <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Ask OTP/Confirmation code at Products/Items Delivery"></i> --></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <div class="make-switch" data-on="success" data-off="warning">
                                                        <input type="checkbox" id="eOTPCodeEnable" name="eOTPCodeEnable" <?= ($id != '' && $eOTPCodeEnable == 'Yes') ? 'checked' : ''; ?> value="Yes"/>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php 
                                            if($MODULES_OBJ->isEnableTermsServiceCategories()) { 
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Enable Age Verification Feature<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select  class="form-control" name="eShowTerms"  id="eShowTerms" required="required">
                                                    <option value="Yes" <?= ($eShowTerms == 'Yes') ? "selected" : "" ?>>Yes</option>
                                                    <option value="No" <?= ($eShowTerms == 'No') ? "selected" : "" ?>>No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } ?>

                                        <?php 
                                            if($MODULES_OBJ->isEnableProofUploadServiceCategories()) { 
                                                $proof_note_section_display = ($eProofUpload == "No") ? 'style="display: none"' : '';
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Enable ID Proof Upload for Age Verification Feature<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <select  class="form-control" name="eProofUpload"  id="eProofUpload" required="required" onchange="displayProofNoteSection(this);">
                                                    <option value="Yes" <?= ($eProofUpload == 'Yes') ? "selected" : "" ?>>Yes</option>
                                                    <option value="No" <?= ($eProofUpload == 'No') ? "selected" : "" ?>>No</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div id="proof_note_section" <?= $proof_note_section_display ?>>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>ID Proof Note For User</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <textarea id="tProofNote" class="form-control" rows="3" readonly="readonly"><?= $tProofNoteLang ?></textarea>
                                                    <textarea name="tProofNote" id="tProofNoteUserHidden" style="display: none;"><?= $tProofNote ?></textarea>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editProofNote('user')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>ID Proof Note For Driver</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <textarea id="tProofNoteDriver" class="form-control" rows="3" readonly="readonly"><?= $tProofNoteDriverLang ?></textarea>
                                                    <textarea name="tProofNoteDriver" id="tProofNoteDriverHidden" style="display: none;"><?= $tProofNoteDriver ?></textarea>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editProofNote('driver')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>ID Proof Note For Store</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <textarea id="tProofNoteStore" class="form-control" rows="3" readonly="readonly"><?= $tProofNoteStoreLang ?></textarea>
                                                    <textarea name="tProofNoteStore" id="tProofNoteStoreHidden" style="display: none;"><?= $tProofNoteStore ?></textarea>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editProofNote('store')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div  class="modal fade" id="id_proof_note_lang" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                                            <div class="modal-dialog" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="id_proof_note_lang_title" style="text-transform: capitalize;"></span>
                                                            <button type="button" class="close" data-dismiss="modal">x</button>
                                                        </h4>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_proof_note_for" id="id_proof_note_for">
                                                        <?php
                                                            if ($count_all > 0) 
                                                            {
                                                                for ($i = 0; $i < $count_all; $i++) 
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];
                                                            
                                                                    $vValue = 'tProofNoteValue_' . $vCode;
                                                                    $vValueName = 'tProofNoteTitle_' . $vCode;
                                                            
                                                                    $required = ($eDefault == 'Yes') ? '' : '';
                                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                                <?php if($vCode == $default_lang  && scount($db_master) > 1) { ?>
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <label><span id="<?= $vValueName ?>">ID Proof Note</span> (<?= $vTitle ?>)</label>
                                                                        <textarea class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" data-lang="<?= $vCode ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?> rows="3"></textarea>
                                                                        <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>
                                                                </div>
                                                                <?php } else { ?>
                                                                <div class="form-group row">
                                                                    <div class="col-md-12">
                                                                        <label>ID Proof Note (<?= $vTitle ?>)</label>
                                                                        <textarea class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" data-lang="<?= $vCode ?>" placeholder="<?= $vTitle; ?> Value" rows="3"></textarea>

                                                                    </div>
                                                                </div>
                                                                <?php } ?>

                                                                <?php if($vCode == $default_lang  && scount($db_master) > 1) { ?>
                                                                <div class="form-group">
                                                                    <button type="button" class="btn btn-primary" onclick="getAllLanguageCode('tProofNoteValue_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                </div>
                                                                <?php } 
                                                                }
                                                            }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" id="id_proof_note_lang_btn"  style="margin-left: 0 !important" onclick="saveProofNote()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>

                                        </div>
                                        <?php } ?>

                                        <?php
                                            $count1 = "select iServiceId from service_categories";
                                            $cnt = $obj->MySQLSelect($count1);
                                            $count = scount($cnt);
                                            ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'] ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6" id="showDisplayOrder001">
                                                <?php if ($action == 'EDIT') { ?>
                                                <input type="hidden" name="total" value="<?php echo $count; ?>" >
                                                <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                    <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                    <option value="<?php echo $i ?>" 
                                                        <?php
                                                            if ($i == $count)
                                                                echo 'selected';
                                                            ?>> <?php echo $i ?> </option>
                                                    <?php } ?>
                                                </select>
                                                <?php }else { ?>
                                                <input type="hidden" name="total" value="<?php echo $iDisplayOrder; ?>">
                                                <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                    <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                    <option value="<?php echo $i ?>"
                                                        <?php if ($i == $iDisplayOrder) echo 'selected'; ?>
                                                        > <?php echo $i ?> </option>
                                                    <?php } ?>
                                                </select>
                                                <?php } ?>
                                            </div>
                                        </div>
                                </div>
                                <!--                                <div class="row">
                                    <div class="col-lg-12">
                                            <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                            </div>
                                    </div>
                                    </div> -->
                                <div class="row">
                                <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-service-category')) || ($action == 'Add' && $userObj->hasPermission('create-service-category'))) { ?>
                                <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> DeliveryAll Service Category">
                                <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="service_category.php" class="btn btn-default back_link">Cancel</a>
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
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script>
            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
            
                if (referrer == "") {
                    referrer = "service_category.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });

            function editProofNote(user_type) {
                $('#id_proof_note_lang_title').text("ID Proof Note For " + user_type);
                $('#id_proof_note_for').val(user_type);
                if(user_type == "user") {
                    var tProofNote = $('#tProofNoteUserHidden').text();
                    var tProofNoteName = "tProofNote_";
                } else if(user_type == "driver") {
                    var tProofNote = $('#tProofNoteDriverHidden').text();   
                    var tProofNoteName = "tProofNoteDriver_";
                } else {
                    var tProofNote = $('#tProofNoteStoreHidden').text();
                    var tProofNoteName = "tProofNoteStore_";
                }

                if(tProofNote.trim() != "") {
                    tProofNote = JSON.parse(tProofNote);
                    $('[name^=tProofNoteValue_]').each(function() {
                        var lang_code = $(this).data('lang');
                        $(this).val(tProofNote[tProofNoteName+lang_code]);
                    });     
                }
                $('#id_proof_note_lang .modal-body, #id_proof_note_lang textarea').animate({ scrollTop: 0 }, 'fast');
                $('#id_proof_note_lang').modal('show');
            }

            function saveProofNote() {
                if($('#tProofNoteValue_<?= $default_lang ?>').val() == "") {
                    $('#tProofNoteValue_<?= $default_lang ?>_error').show();
                    $('#tProofNoteValue_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tProofNoteValue_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }
                var user_type = $('#id_proof_note_for').val();
                if(user_type == "user") {
                    var tProofNoteMain = $('#tProofNote');
                    var tProofNote = $('#tProofNoteUserHidden');
                    var tProofNoteName = "tProofNote_";
                    var tProofNoteNameLang = "tProofNote_<?= $default_lang ?>";
                } else if(user_type == "driver") {
                    var tProofNoteMain = $('#tProofNoteDriver');
                    var tProofNote = $('#tProofNoteDriverHidden');   
                    var tProofNoteName = "tProofNoteDriver_";
                    var tProofNoteNameLang = "tProofNoteDriver_<?= $default_lang ?>";
                } else {
                    var tProofNoteMain = $('#tProofNoteStore');
                    var tProofNote = $('#tProofNoteStoreHidden');
                    var tProofNoteName = "tProofNoteStore_";
                    var tProofNoteNameLang = "tProofNoteStore_<?= $default_lang ?>";
                }
                jsonObj = {};
                $('[name^=tProofNoteValue_]').each(function() {
                    var lang_code = $(this).data('lang');
                    jsonObj[tProofNoteName+lang_code] = $(this).val();
                });
                tProofNoteMain.text(jsonObj[tProofNoteNameLang]);
                tProofNote.text(JSON.stringify(jsonObj));
                $('#id_proof_note_lang').modal('hide');
            }

            function displayProofNoteSection(elem)
            {
                if(elem.value == "Yes")
                {
                    $('#proof_note_section').show();
                }
                else {
                    $('#proof_note_section').hide();   
                }
            }

            function editServiceCategory(action)
            {
                $('#modal_action').html(action);
                $('#service_cat_Modal').modal('show');
            }

            function saveServiceCategory()
            {
                if($('#vServiceName_<?= $default_lang ?>').val() == "") {
                    $('#vServiceName_<?= $default_lang ?>_error').show();
                    $('#vServiceName_<?= $default_lang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        $('#vServiceName_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#vServiceName_Default').val($('#vServiceName_<?= $default_lang ?>').val());
                $('#vServiceName_Default').closest('.row').removeClass('has-error');
                $('#vServiceName_Default-error').remove();
                $('#service_cat_Modal').modal('hide');
            }
            
        </script>