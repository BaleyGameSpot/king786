<?php
    include_once('../common.php');
    require_once(TPATH_CLASS . "/Imagecrop.class.php");
    $thumb = new thumbnail();
    
    
    //error_reporting(E_ALL);
    define("DONATION", "donation");
    $script = 'donation';
    
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
    $action = ($id != '') ? 'Edit' : 'Add';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
    $tLink = isset($_POST['tLink']) ? $_POST['tLink'] : '';
    
    $temp_order     = isset($_POST['temp_order'])? $_POST['temp_order'] : "";
    
    //$eUserType = isset($_POST['eUserType']) ? $_POST['eUserType'] : '';
    
    /* to fetch max iDisplayOrder from table for insert */
    $select_order   = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . DONATION . "");
    $iDisplayOrder  = isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
    $iDisplayOrder_max  = $iDisplayOrder + 1; // Maximum order number
    
    
    $sql = "SELECT * FROM `language_master` ORDER BY `eDefault`";
    $db_master = $obj->MySQLSelect($sql);
    $count_all = scount($db_master);
    $txtBoxNameArr = array("tTitle");
    $lableArr = array("Title");
    
    $vImage = $welComeImg = "";
    $img_data = array();
    if (isset($_POST['btnsubmit'])) {
        if ($action == "Add" && !$userObj->hasPermission('create-donation')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin['LBL_DONATION_SMALL_TXT'];
            header("Location:donation.php");
            exit;
        }
        if ($action == "Edit" && !$userObj->hasPermission('edit-donation')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_DONATION_SMALL_TXT'];
            header("Location:donation.php");
            exit;
        }
        if (SITE_TYPE == 'Demo') {
            //header("Location:Donation_feed_action_new.php?id=" . $id . "&success=2");
            $_SESSION['success'] = '2';
            header("location:" . $backlink);
            exit;
        }
    //  for ordering
    $iDisplayOrder  = isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
    $temp_order     = isset($_POST['temp_order'])? $_POST['temp_order'] : "";
         if($temp_order == "1" && $action == "Add"){
            $temp_order = $iDisplayOrder_max;
        }
        if($temp_order > $iDisplayOrder) { 
            for($i = $temp_order-1; $i >= $iDisplayOrder; $i--) { 
               $sql="UPDATE " . DONATION . " SET iDisplayOrder = '".($i+1)."' WHERE iDisplayOrder = '".$i."'";
                $obj->sql_query($sql);
            }
        } else if($temp_order < $iDisplayOrder) {
            for($i = $temp_order+1; $i <= $iDisplayOrder; $i++) {
                $sql="UPDATE " . DONATION . " SET iDisplayOrder = '".($i-1)."' WHERE iDisplayOrder = '".$i."'";
                $obj->sql_query($sql);
            }
        }
    
        require_once("library/validation.class.php");
            $tCreatedDate = date("Y-m-d H:i:s");
            for ($i = 0; $i < scount($db_master); $i++) {
                $tTitle = $tDescription = "";
                if (isset($_POST['tTitle_' . $db_master[$i]['vCode']])) {
                    $tTitle = $_POST['tTitle_' . $db_master[$i]['vCode']];
                }
                if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
                    $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
                }

                $tTitleArr["tTitle_" . $db_master[$i]['vCode']] = $tTitle;
                $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
            }

            $time = time();
            
            // for image upload
            if ($_FILES['vDonationImage']['name'] != '') {
                $img_path = $tconfig["tsite_upload_images_donation_path"];
                $temp_gallery = $img_path . '/';
                $image_object = $_FILES['vDonationImage']['tmp_name'];
                $image_name = $_FILES['vDonationImage']['name'];
    
                // $filecheck = basename($_FILES['vDonationImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                //     $flag_error = 1;
                //     $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,gif,png";
                // }

                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vDonationImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
                if($error){
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("Location:donation_action.php?id=" . $_REQUEST['id']);
                        exit;
                }
    
                $dataimg = getimagesize($_FILES['vDonationImage']['tmp_name']);
                $imgwidth = $dataimg[0];
                $imgheight = $dataimg[1];
    
                if ($imgwidth < 2880) {
    
                    echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
                }
                
                if ($id != '') {
                    $check_file_query = "select vDonationImage from donation where iDonationId = $id";
                    
                    $check_file = $obj->sql_query($check_file_query);
                    $oldImage = $check_file[0]['vDonationImage'];
                    $check_file = $img_path . '/' . $oldImage;
                    if ($oldImage != '' && file_exists($check_file)) {
                        @unlink($img_path . '/' . $oldImage);
                    }
                }
    
    
                if ($flag_error == 1) {
    
                    if ($action == "Add") {
                        header("Location:donation_action.php?&var_msg=" . $var_msg);
                        exit;
                    }else{
                        header("Location:donation_action.php?id=" . $id . "&var_msg=" . $var_msg . "");
                        exit;
                    }
                } else {
                    if ($id != '') {
                        $Photo_Gallery_folder = $img_path . '/' . $iDonationId . '/';
                        if (!is_dir($Photo_Gallery_folder)) {
                            mkdir($Photo_Gallery_folder, 0777);
                        }
                        $img1 = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                        $vDonationImage = $img1[0];
        
                        // $sql1 = "UPDATE " . DONATION . " SET `vDonationImage` = '" . $vDonationImage . "' WHERE `iDonationId` = '" . $id . "'";
                        // $test = $obj->sql_query($sql1);
                    } else {
                        $Photo_Gallery_folder = $img_path . '/';
                        if (!is_dir($Photo_Gallery_folder)) {
                            mkdir($Photo_Gallery_folder, 0777);
                        }
                        $img1 = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                        $vDonationImage = $img1[0];
                    }
                }
            }
            if (scount($tTitleArr) > 0) {
                $updateProfileImg = "";
                $jsonTitle =  getJsonFromAnArr($tTitleArr);
                $jsonDesc = getJsonFromAnArr($descArr);
                
                $Data_update = array();
                $Data_update['iDisplayOrder'] = $iDisplayOrder;
                $Data_update['tTitle'] = $jsonTitle;
                $Data_update['tLink'] = $tLink;
                $Data_update['tCreatedDate'] = $tCreatedDate;
                $Data_update['tDescription'] = $jsonDesc;
                $Data_update['vDonationImage'] = $vDonationImage;

                if ($eStatus == '') {
                    $Data_update['eStatus'] = 'Inactive';
                } else {
                    $Data_update['eStatus'] = 'Active';
                }

                if ($id != '') {
                    $where = " iDonationId = '$id'";
                    $id1 = $obj->MySQLQueryPerform(DONATION, $Data_update, 'update', $where);
                } else {
                    $id1 = $obj->MySQLQueryPerform(DONATION, $Data_update, 'insert');
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
    // for Edit
    $userEditDataArr = array();
    if ($action == 'Edit') {
        $sql = "SELECT * FROM " . DONATION . " WHERE iDonationId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
    
        if (scount($db_data) > 0) {
            $tTitle = json_decode($db_data[0]['tTitle'], true);
            foreach ($tTitle as $key => $value) {
                $userEditDataArr[$key] = $value;
            }
            $tDescription = json_decode($db_data[0]['tDescription'], true);
            foreach ($tDescription as $key4 => $value4) {
                $userEditDataArr[$key4] = $value4;
            }
            if (scount($db_data) > 0) {
                foreach ($db_data as $key => $value) {
                    $vDonationImage = $value['vDonationImage'];
                    $eStatus = $value['eStatus'];
                    $tLink = $value['tLink'];
                    $iDisplayOrder_db = $value['iDisplayOrder'];
                }
            }
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
                <title><?= $SITE_NAME ?> | Donation <?= $action; ?></title>
                <meta content="width=device-width, initial-scale=1.0" name="viewport" />
                <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
                <?php include_once('global_files.php'); ?>
                <!-- On OFF switch -->
                <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
                <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
                <!-- PAGE LEVEL STYLES -->
                <style>
                    ul.wysihtml5-toolbar > li {
                    position: relative;
                    }
                </style>
            </head>
            <!-- END  HEAD-->
            <!-- BEGIN BODY-->
            <body class="padTop53 " >
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
                                    <h2> <?= $action; ?> Donation </h2>
                                    <a href="javascript:void(0);" class="back_link">
                                    <input type="button" value="Back to Listing" class="add-btn">
                                    </a>
                                </div>
                            </div>
                            <hr />
                            <div class="body-div">
                                <div class="form-group">
                                    <?php include('valid_msg.php'); ?>
                                    <?php if ($success == 1) { ?>
                                    <div class="alert alert-success alert-dismissable msgs_hide">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                        <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                    </div>
                                    <br/>
                                    <?php } elseif ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable ">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                        <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                    </div>
                                    <br/>
                                    <?php } else if ($success == 3) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                        <?php echo $_REQUEST['varmsg']; ?> 
                                    </div>
                                    <br/> 
                                    <?php } ?>
                                    <?php  if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != "") {
                                        ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $_REQUEST['var_msg']; ?>
                                    </div>
                                    <br/>  
                                    <?php }
                                        ?>
                                    <form id="_donation_form" name="_donation_form" method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="donation.php"/>
                                        <div class="row">
                                            <div class="col-lg-12" id="errorMessage"></div>
                                        </div>

                                        <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control <?= (empty($id)) ?  'readonly-custom' : '' ?>" id="tTitle_Default" name="tTitle_Default" value="<?= isset($userEditDataArr['tTitle_'.$default_lang]) ? $userEditDataArr['tTitle_'.$default_lang] : ''; ?>" data-originalvalue="<?= isset($userEditDataArr['tTitle_'.$default_lang]) ? $userEditDataArr['tTitle_'.$default_lang] : ''; ?>" readonly="readonly" <?php if(empty($id)) { ?> onclick="editDonationTitle('Add')" <?php } ?>>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDonationTitle('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="DonationTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Donation Title
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tTitle_')">x</button>
                                                        </h4>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <?php
                                                            
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'tTitle_' . $vCode;
                                                                $$vValue = $userEditDataArr[$vValue];

                                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';

                                                                $vCodeDefault = $default_lang;
                                                                if (scount($db_master) > 1) {
                                                                    if($EN_available) {
                                                                        $vCodeDefault = 'EN';
                                                                    }
                                                                    else {
                                                                        $vCodeDefault = $default_lang;
                                                                    }
                                                                }
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
                                                                        <label>Title (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        
                                                                    </div>
                                                                    <div class="<?= $page_title_class ?>">
                                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                        <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>
                                                                    <?php
                                                                    if (scount($db_master) > 1) {
                                                                        if($EN_available) {
                                                                            if($vCode == "EN") { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tTitle_', 'EN');" >Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tTitle_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDonationTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                <textarea class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" name="tDescription_Default" id="tDescription_Default" name="tDescription_Default" data-originalvalue="<?= isset($userEditDataArr['tDescription_'.$default_lang]) ? $userEditDataArr['tDescription_'.$default_lang] : ''; ?>" rows="4" readonly="readonly" <?php if($id == "") { ?> onclick="editDonationDesc('Add')" <?php } ?>><?= isset($userEditDataArr['tDescription_'.$default_lang]) ? $userEditDataArr['tDescription_'.$default_lang] : ''; ?></textarea>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDonationDesc('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="DonationDesc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Donation Description
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tDescription_')">x</button>
                                                        </h4>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <?php
                                                            
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];

                                                                $descVal = 'tDescription_' . $vCode;
                                                                $$descVal = $userEditDataArr['tDescription_' . $vCode];

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
                                                                        <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        
                                                                    </div>
                                                                    <div class="<?= $page_title_class ?>">
                                                                        <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" rows="4" data-originalvalue="<?= $$descVal; ?>"><?= $$descVal; ?></textarea>

                                                                        <div class="text-danger" id="<?= $descVal.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>
                                                                    <?php
                                                                    if (scount($db_master) > 1) {
                                                                        if($EN_available) {
                                                                            if($vCode == "EN") { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', 'EN');" >Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDonationDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tDescription_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                <input type="text" class="form-control" id="tTitle_<?= $default_lang ?>" name="tTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['tTitle_'.$default_lang]; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" name="tDescription_<?= $default_lang ?>" id="tDescription_<?= $default_lang ?>" rows="4"><?= $userEditDataArr['tDescription_'.$default_lang]; ?></textarea>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php
                                            /*if (scount($db_master) > 0) {
                                                ?>
                                        <?php
                                            for ($i = 0; $i < scount($db_master); $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $tTitle = $db_master[$i]['vTitle'];
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
                                                <label><?= $lableText; ?> (<?= $tTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <!-- <?= $lableName; ?> -->
                                                <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $tTitle; ?> Value" <?= $required; ?>>
                                                <div class="text-danger" id="<?= $lableName.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                            <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?php echo $txtBoxNameArr[$l].'_'; ?>', '<?= $default_lang ?>');">Convert To All Language</button>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description (<?= $tTitle; ?>) <?php echo $required_msg; ?></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $tTitle; ?> Value" <?= $required; ?>><?= $userEditDataArr[$descVal]; ?></textarea>
                                                <!-- ckeditor -->
                                                <div class="text-danger" id="<?= $descVal.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                            <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                            <div class="col-md-6 col-sm-6">
                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <?php
                                            }
                                            }*/
                                            ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Link <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <!-- <?= $lableName; ?> -->
                                                <input type="url" class="form-control" name="tLink" id="tLink" value="<?= $tLink; ?>"  required>
                                                <span class="notes">[Note: Add link with http:// or https://.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <?php if (!empty($vDonationImage)) { ?>                                               
                                                <!--  <img src="<?= $tconfig['tsite_upload_images_donation'] . "/" . $vDonationImage; ?>" style="width:100px;height:100px;"> -->
                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=200&h=200&src='.$tconfig['tsite_upload_images_donation'] . '/' . $vDonationImage; ?>" style="width:100px;height:100px;">
                                                <?php } ?>
                                                <!-- <input type="file" accept="image/jpg, image/jpeg, image/png image/gif" class="form-control" name="vDonationImage" id="vDonationImage" value="<?= $vDonationImage; ?>"> -->
                                                <input type="hidden" class="form-control" name="vImage_old" id="vImage_old" value="<?= $vDonationImage; ?>">
                                                <input type="file" class="form-control" name="vDonationImage" id="vDonationImage" value="<?= $vDonationImage; ?>">
                                                <span class="notes">[Note: Recommended dimension for banner image is 2880 * 1620.]</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Display Order</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <?php
                                                    $display_numbers = ($action=="Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                                    ?>
                                                <input type="hidden" name="temp_order" id="temp_order" value="<?=($action == 'Edit') ? $iDisplayOrder_db : $display_numbers;?>">
                                                <select name="iDisplayOrder" class="form-control">
                                                    <?php 
                                                        for($i=1; $i <= $display_numbers; $i++){ 
                                                            if($action=="Add"){
                                                             $iDisplayOrder_db = $display_numbers;
                                                            }
                                                            ?>
                                                    <option value="<?=$i?>" <?php if($i == $iDisplayOrder_db){echo "selected";}?>> -- <?=$i?> --</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <?php if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-donation')) || ($action == "Add" && $userObj->hasPermission('create-donation'))) { ?>
                                            <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Donation" >
                                            <input type="reset" value="Reset" class="btn btn-default">
                                            <?php } ?>
                                            <a href="donation.php" class="btn btn-default back_link">Cancel</a>
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
                <script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
                <script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
                <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
                <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
                <script type="text/javascript" src="js/moment.min.js"></script>
                <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
                <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Js-->
                <script type="text/javascript" src="js/bootstrap-clockpicker.min.js"></script>
                <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker End Js -->
                <!--For Faretype-->
                <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
                <script src="../assets/plugins/ckeditor/config.js"></script>
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
                            referrer = "donation.php";
                        } else {
                            $("#backlink").val(referrer);
                        }
                        $(".back_link").attr('href','donation.php');
                    });

                    function editDonationTitle(action)
                    {
                        $('#modal_action').html(action);
                        $('#DonationTitle_Modal').modal('show');
                    }

                    function saveDonationTitle()
                    {
                        if($('#tTitle_<?= $default_lang ?>').val() == "") {
                            $('#tTitle_<?= $default_lang ?>_error').show();
                            $('#tTitle_<?= $default_lang ?>').focus();
                            clearInterval(langVar);
                            langVar = setTimeout(function() {
                                $('#tTitle_<?= $default_lang ?>_error').hide();
                            }, 5000);
                            return false;
                        }

                        $('#tTitle_Default').val($('#tTitle_<?= $default_lang ?>').val());
                        $('#tTitle_Default').closest('.row').removeClass('has-error');
                        $('#tTitle_Default-error').remove();
                        $('#DonationTitle_Modal').modal('hide');
                    }

                    function editDonationDesc(action)
                    {
                        $('#modal_action').html(action);
                        $('#DonationDesc_Modal').modal('show');
                    }

                    function saveDonationDesc()
                    {
                        if($('#tDescription_<?= $default_lang ?>').val() == "") {
                            $('#tDescription_<?= $default_lang ?>_error').show();
                            $('#tDescription_<?= $default_lang ?>').focus();
                            clearInterval(langVar);
                            langVar = setTimeout(function() {
                                $('#tDescription_<?= $default_lang ?>_error').hide();
                            }, 5000);
                            return false;
                        }

                        $('#tDescription_Default').val($('#tDescription_<?= $default_lang ?>').val());
                        $('#DonationDesc_Modal').modal('hide');
                    }

                     $('#_donation_form').validate({

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
                            vDonationImage:{
                                    required: function () {
                                        if($("#vImage_old").val() != ""){
                                            return false;
                                        }else {
                                            return true;
                                        }
                                },
                                extension: imageUploadingExtenstionjsrule

                            }
                        },
                        messages: {
                            vDonationImage :{
                                required: requiredFieldMsg,
                                extension: imageUploadingExtenstionMsg
                            }
                        },
                        submitHandler: function (form) {
                            if ($(form).valid()) {
                                ShpSq6fAm7(form);
                                form.submit();
                            }
                            return false; // prevent normal form posting
                        }
                    });

                </script>
                <!--END MAIN WRAPPER -->
                <!-- GLOBAL SCRIPTS -->
                <!--<script src="../assets/plugins/jquery-2.0.3.min.js"></script>-->
                <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
                <script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
                <!-- END GLOBAL SCRIPTS -->
            </body>
            <!-- END BODY-->
        </html>