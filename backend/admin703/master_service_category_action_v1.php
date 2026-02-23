<?php
include_once('../common.php');
require_once(TPATH_CLASS."Imagecrop.class.php");

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId

if(!$userObj->hasPermission('edit-master-service-category') || empty($id)) {
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

$script = 'VehicleCategory';
$tbl_name = "master_service_category_v1";

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);

$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vTextColor = isset($_POST['vTextColor']) ? $_POST['vTextColor'] : '#ffffff';
$vBgColor = isset($_POST['vBgColor']) ? $_POST['vBgColor'] : '#ffffff';
$thumb = new thumbnail();

if(isset($_POST['submit'])) { //form submit
    
    if(SITE_TYPE =='Demo'){
        $_SESSION['success'] = 2;
        header("Location:master_service_category.php"); exit;
    }

    $q = "INSERT INTO ";
    $where = '';
    
    if($id != '' ){ 
        $q = "UPDATE ";
        $where = " WHERE `iMasterServiceCategoryId` = '".$id."'";
    }

    $eType = $_POST['eType'];
    $Data_Update = array();

    $image_object = $_FILES['vImage1']['tmp_name'];  
    $image_name   = $_FILES['vImage1']['name'];

    if($image_name != ""){
        $filecheck = basename($_FILES['vImage1']['name']);                            
        $fileextarr = explode(".",$filecheck);
        $ext=strtolower($fileextarr[scount($fileextarr)-1]);
        $flag_error = 0;
        if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
        }
        $image_info = getimagesize($_FILES["vImage1"]["tmp_name"]);
        $image_width = $image_info[0];
        $image_height = $image_info[1];

        if($flag_error == 1){
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $var_msg;
            header("Location:master_service_category.php");
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
            if(!is_dir($Photo_Gallery_folder)){
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }  
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');
            $vImage = $img[0];

            $Data_Update['vIconImage1'] = $vImage;

            if(!empty($_POST['vImage1_old']) && file_exists($Photo_Gallery_folder . $_POST['vImage1_old'])) {
                unlink($Photo_Gallery_folder . $_POST['vImage_old']);
            }
        }
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        $vCategoryName = "";
        if (isset($_POST['vCategoryName_' . $db_master[$i]['vCode']])) {
            $vCategoryName = $_POST['vCategoryName_' . $db_master[$i]['vCode']];
        }

        $vCategoryNameArr["vCategoryName_" . $db_master[$i]['vCode']] = $vCategoryName;

        $vCategoryDesc = "";
        if (isset($_POST['vCategoryDesc_' . $db_master[$i]['vCode']])) {
            $vCategoryDesc = $_POST['vCategoryDesc_' . $db_master[$i]['vCode']];
        }

        $vCategoryDescArr["vCategoryDesc_" . $db_master[$i]['vCode']] = $vCategoryDesc;
    }

    $jsonCategoryName = getJsonFromAnArr($vCategoryNameArr);

    if(in_array($eType, ['RideShare', 'TrackService'])) {
        $jsonCategoryDesc = getJsonFromAnArr($vCategoryDescArr);    
        $Data_Update['vCategoryDesc'] = $jsonCategoryDesc;
    }
    
    $Data_Update['vCategoryName'] = $jsonCategoryName;
    $Data_Update['eStatus'] = $eStatus;
    $Data_Update['vTextColor'] = $vTextColor;
    $Data_Update['vBgColor'] = $vBgColor;

    if($id != '' ){ 
        $where = " iMasterServiceCategoryId = '".$id."'";
        $id = $obj->MySQLQueryPerform($tbl_name, $Data_Update, 'update', $where);            
    } else {
        $id = $obj->MySQLQueryPerform($tbl_name, $Data_Update, 'insert');            
    }

    $obj->sql_query($query);
    if($id != '' ){ 
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }
    
    header("Location:master_service_category.php");
    exit();
}

$display_banner = $display = "";
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM ".$tbl_name." WHERE iMasterServiceCategoryId = '".$id."'";
    $db_data = $obj->MySQLSelect($sql);

    if (scount($db_data) > 0) {
        $vCategoryName = json_decode($db_data[0]['vCategoryName'], true);
        foreach ($vCategoryName as $key => $value) {
            $userEditDataArr[$key] = $value;
        }

        $vCategoryDesc = json_decode($db_data[0]['vCategoryDesc'], true);
        foreach ($vCategoryDesc as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        
        $vIconImage1 = $db_data[0]['vIconImage1'];
        $vBgImage = $db_data[0]['vBgImage'];
        $vTextColor = $db_data[0]['vTextColor'];
        $vBgColor = $db_data[0]['vBgColor'];
        $eStatus = $db_data[0]['eStatus'];
        $eType = $db_data[0]['eType'];
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<!--[if !IE]><!--> 
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Master Service Category <?=$action;?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php');?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style type="text/css">
            .admin-notes ul li {
                padding-bottom: 0;
                font-size: 13px;
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
                            <h2><?=$action;?> Master Service Category</h2>
                            <a href="master_service_category.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 0 && !empty($_REQUEST['var_msg'])) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <? echo $_REQUEST['var_msg']; ?>
                            </div>
                            <br/>
                            <?} ?>
                            <? if($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div>
                            <br/>
                            <? } ?>
                            <? if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                            <br/>
                            <? } ?>
                            <form method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?=$id;?>"/>
                                <input type="hidden" name="eType" value="<?= $eType ?>">
                                <div class="row">
                                    <input type="hidden" name="vImage1_old" value="<?= $vIconImage1 ?>">
                                    <div class="col-lg-12">
                                        <?php if(in_array($eType, ['Ride', 'Deliver', 'DeliverAll', 'VideoConsult', 'ServiceBid', 'UberX', 'TrackService'])) { ?>
                                        <label>Icon <?=($vIconImage1 == '') ? '<span class="red"> *</span>' : '';?></label>
                                        <?php } elseif (in_array($eType, ['RentItem', 'RentCars', 'RentEstate'])) { ?>
                                        <label>Image <?=($vIconImage1 == '') ? '<span class="red"> *</span>' : '';?></label>
                                        <?php } elseif (in_array($eType, ['RideShare'])) { ?>
                                        <label>Banner <?=($vIconImage1 == '') ? '<span class="red"> *</span>' : '';?></label>
                                        <?php } ?>
                                    </div>
                                    <div class="col-lg-4">
                                        <?php if($vIconImage1 != '') { ?>
                                        <div class="marginbottom-10">
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=150&src='.$tconfig['tsite_upload_app_home_screen_images'].$vIconImage1;?>">
                                        </div>
                                        <div class="marginbottom-10">
                                            <input type="file" class="form-control" name="vImage1" id="vImage1" value=""/>
                                        </div>
                                        <?php } else { ?>
                                        <div class="marginbottom-10">
                                            <input type="file" class="form-control" name="vImage1" id="vImage1" value="" required/>
                                        </div>
                                        <?php } ?>

                                        <div class="admin-notes" style="margin: 0">
                                            <?php if(in_array($eType, ['Ride', 'Deliver', 'DeliverAll', 'VideoConsult', 'Bidding', 'UberX', 'TrackService'])) { ?>
                                            <strong>Note: Upload only png image size of 360px X 360px.</strong>
                                            <?php } elseif (in_array($eType, ['RentItem', 'RentCars', 'RentEstate'])) { ?>
                                            <strong>Note: Upload only png image size of 1050px X 450px.</strong>
                                            <?php } elseif (in_array($eType, ['RideShare'])) { ?>
                                            <strong>Note: Upload only png / jpeg image size of 1050px X 520px.</strong>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Name</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vCategoryName_Default" name="vCategoryName_Default" value="<?= $userEditDataArr['vCategoryName_'.$default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vCategoryName_'.$default_lang]; ?>" readonly="readonly" required <?php if($id == "") { ?> onclick="editCategoryName('Add')" <?php } ?>>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCategoryName('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="Category_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Category Name
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vCategoryName_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vCategoryName_' . $vCode;
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
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCategoryName_', 'EN');">Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCategoryName_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveCategoryName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vCategoryName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>

                                <?php if(in_array($eType, ['RideShare', 'TrackService'])) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Description</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vCategoryDesc_Default" name="vCategoryDesc_Default" value="<?= $userEditDataArr['vCategoryDesc_'.$default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vCategoryDesc_'.$default_lang]; ?>" readonly="readonly" required <?php if($id == "") { ?> onclick="editCategoryDesc('Add')" <?php } ?>>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCategoryDesc('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="CategoryDesc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="desc_modal_action"></span> Category Description
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vCategoryDesc_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vCategoryDesc_' . $vCode;
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
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCategoryDesc_', 'EN');">Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCategoryDesc_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveCategoryDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vCategoryDesc_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Name</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vCategoryName_<?= $default_lang ?>" name="vCategoryName_<?= $default_lang ?>" value="<?= $userEditDataArr['vCategoryName_'.$default_lang]; ?>">
                                    </div>
                                </div>

                                <?php if(in_array($eType, ['RideShare', 'TrackService'])) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category Description</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vCategoryDesc_<?= $default_lang ?>" name="vCategoryDesc_<?= $default_lang ?>" value="<?= $userEditDataArr['vCategoryDesc_'.$default_lang]; ?>">
                                    </div>
                                </div>
                                <?php } ?>
                                <?php } ?>

                                <?php if(!in_array($eType, ['Ride', 'Deliver', 'DeliverAll', 'VideoConsult', 'Bidding', 'UberX'])) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" id="TextColor" class="form-control" value="<?= $vTextColor ?>" />
                                        <input type="hidden" name="vTextColor" id="vTextColor" value="<?= $vTextColor ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Background Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" id="BgColor" class="form-control" value="<?= $vBgColor ?>" />
                                        <input type="hidden" name="vBgColor" id="vBgColor" value="<?= $vBgColor ?>">
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?> value="Active"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="save btn-info" name="submit" id="submit" value="<?=$action;?> Master Service Category" style="margin-right: 10px">
                                        <a href="master_service_category.php" class="btn btn-default back_link">Cancel</a>
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
        <? include_once('footer.php');?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript">
            function editCategoryName(action)
            {
                $('#modal_action').html(action);
                $('#Category_Modal').modal('show');
            }

            function saveCategoryName()
            {
                if($('#vCategoryName_<?= $default_lang ?>').val() == "") {
                    $('#vCategoryName_<?= $default_lang ?>_error').show();
                    $('#vCategoryName_<?= $default_lang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        $('#vCategoryName_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#vCategoryName_Default').val($('#vCategoryName_<?= $default_lang ?>').val());
                $('#vCategoryName_Default').closest('.row').removeClass('has-error');
                $('#vCategoryName_Default-error').remove();
                $('#Category_Modal').modal('hide');
            }

            function editCategoryDesc(action)
            {
                $('#desc_modal_action').html(action);
                $('#CategoryDesc_Modal').modal('show');
            }

            function saveCategoryDesc()
            {
                if($('#vCategoryDesc_<?= $default_lang ?>').val() == "") {
                    $('#vCategoryDesc_<?= $default_lang ?>_error').show();
                    $('#vCategoryDesc_<?= $default_lang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        $('#vCategoryDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#vCategoryDesc_Default').val($('#vCategoryDesc_<?= $default_lang ?>').val());
                $('#vCategoryDesc_Default').closest('.row').removeClass('has-error');
                $('#vCategoryDesc_Default-error').remove();
                $('#CategoryDesc_Modal').modal('hide');
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