<?php
include_once('../common.php');

if(!$userObj->hasPermission('view-store-categories')){
    $userObj->redirect();
}

$tbl_name = 'store_categories';
$script = 'ManageStoreCategories';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iDaysRange = isset($_POST['iDaysRange']) ? $_POST['iDaysRange'] : '';
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder = $nxtDispNo = $iDisplayOrder + 1; // Maximum order number

$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$iServiceIdDB = isset($_POST['iServiceIdDB']) ? $_POST['iServiceIdDB'] : '';

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$txtBoxNameArr = array("tCategoryName");
$lableArr = array("Category Name");

if (isset($_POST['btnsubmit'])) {
    if ($action == "Edit" && !$userObj->hasPermission('edit-store-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
        header("Location:store_category.php");
        exit;
    }
    if (SITE_TYPE == 'Demo' || $id == '') {
        $_SESSION['success'] = '2';
        header("Location:store_category.php");
        exit;
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        $tCategoryName = $tDescription = "";
        if (isset($_POST['tCategoryName_' . $db_master[$i]['vCode']])) {
            $tCategoryName = $_POST['tCategoryName_' . $db_master[$i]['vCode']];
        }
        if (isset($_POST['tCategoryDescription_' . $db_master[$i]['vCode']])) {
            $tDescription = $_POST['tCategoryDescription_' . $db_master[$i]['vCode']];
        }
        $q = "INSERT INTO ";
        $where = '';

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iCategoryId` = '" . $id . "'";
        }
        $vtitleArr["tCategoryName_" . $db_master[$i]['vCode']] = $tCategoryName;
        $descArr["tCategoryDescription_" . $db_master[$i]['vCode']] = $tDescription;
    }
    if ($eStatus == '') {
        $str = ", eStatus = 'Inactive' ";
    } else {
        $str = ", eStatus = 'Active'";
    }
    // Display order related start
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE iDisplayOrder = " . $i." AND iServiceId = '$iServiceIdDB'");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $setOrder = $i - 1;
            if ($i == 1) {
                $setOrder = $nxtDispNo;
            }
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . $setOrder . " WHERE iDisplayOrder = " . $i." AND iServiceId = '$iServiceIdDB'");
        }
    }
    // Display order related end

    if (scount($vtitleArr) > 0) {
        $jsonTitle = getJsonFromAnArr($vtitleArr);
        $jsonDesc = getJsonFromAnArr($descArr);
        $query = $q . " `" . $tbl_name . "` SET `tCategoryName` = '" . $jsonTitle . "',`tCategoryDescription` = '" . $jsonDesc . "', `iDisplayOrder` = '" . $iDisplayOrder . "', `iDaysRange` = '" . $iDaysRange . "' $str " . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    // for image upload
    if ($_FILES['tCategoryImage']['name'] != '') {
        $img_path = $tconfig["tsite_upload_images_store_categories_path"];
        
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['tCategoryImage']['tmp_name'];
        $image_name = $_FILES['tCategoryImage']['name'];

        $filecheck = basename($_FILES['tCategoryImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
        }

        $dataimg = getimagesize($_FILES['tCategoryImage']['tmp_name']);
        $imgwidth = $dataimg[0];
        $imgheight = $dataimg[1];
        if ($imgwidth < 1024) {
            echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
        }
        $check_file_query = "select tCategoryImage from store_categories where iCategoryId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        $oldImage = $check_file[0]['tCategoryImage'];
        $check_file = $img_path . '/' . $oldImage;
        if ($oldImage != '' && file_exists($check_file)) {
            @unlink($img_path . '/' . $oldImage);
        }



        if ($flag_error == 1) {

            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $var_msg;

            header("location:store_category.php");
        } else {
            // echo "here"; exit;
            $Photo_Gallery_folder = $img_path . '/' . $iCategoryId . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
            $tCategoryImage = $img1[0];

            $sql1 = "UPDATE store_categories SET `tCategoryImage` = '" . $tCategoryImage . "' WHERE `iCategoryId` = '" . $id . "'";
            $obj->sql_query($sql1);
        }
    }
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    $_SESSION['success'] = "1";
    header("location:" . $backlink);
    exit;
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCategoryId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (scount($db_data) > 0) {
        $tCategoryName = json_decode($db_data[0]['tCategoryName'], true);
        foreach ($tCategoryName as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $tDescription = json_decode($db_data[0]['tCategoryDescription'], true);
        foreach ($tDescription as $key4 => $value4) {
            $userEditDataArr[$key4] = $value4;
        }
        $tCategoryImage = $db_data[0]['tCategoryImage'];
        $eStatus = $db_data[0]['eStatus'];
        $iDisplayOrder = $db_data[0]['iDisplayOrder'];
        $eType = $db_data[0]['eType'];
        $iDaysRange = ($db_data[0]['iDaysRange'] != "") ? $db_data[0]['iDaysRange'] : 30;
        $iServiceIdDB = $db_data[0]['iServiceId'];
    }

    $display_order = array();
    $sqlall = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iServiceId = '$iServiceIdDB' ";
    $db_data_all = $obj->MySQLSelect($sqlall);
    foreach ($db_data_all as $d) {
        $display_order[] = $d['iDisplayOrder'];
    }
    // $max_usage_order = max($display_order) + 1;
    $max_usage_order = max($display_order);
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
                <title><?= $SITE_NAME ?> | Store Category <?= $action; ?></title>
                <meta content="width=device-width, initial-scale=1.0" name="viewport" />
                <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
                <?php include_once('global_files.php'); ?>
                <!-- On OFF switch -->
                <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
                <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
                <!-- PAGE LEVEL STYLES -->
                <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
                <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
                <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
                <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
                <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
                <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
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
                                    <h2> <?= $action; ?> Store Category </h2>
                                    <a href="javascript:void(0);" class="back_link">
                                    <input type="button" value="Back to Listing" class="add-btn">
                                    </a>
                                </div>
                            </div>
                            <hr />
                            <div class="body-div">
                                <div class="form-group">
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
                                    <?php if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                                        <div class="alert alert-danger alert-dismissable">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                            Record  Not Updated .
                                        </div>
                                        <br/>
                                    <?php } ?>                   
                                    <form id="_vehicleType_form" name="_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="store_category.php"/>
                                        <input type="hidden" name="iServiceIdDB" value="<?= $iServiceIdDB ?>">
                                        <div class="row">
                                            <div class="col-lg-12" id="errorMessage"></div>
                                        </div>
                                        
                                        <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Category Name <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="tCategoryName_Default" value="<?= $userEditDataArr['tCategoryName_'.$default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['tCategoryName_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editCategoryName('Add')" <?php } ?>>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCategoryName('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="cat_name_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Category Name
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tCategoryName_')">x</button>
                                                        </h4>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <?php
                                                            
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'tCategoryName_' . $vCode;
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
                                                                        <label>Category Name (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        
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
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tCategoryName_', 'EN');" >Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tCategoryName_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tCategoryName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                <textarea class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" name="tDescription_Default" id="tCategoryDescription_Default" rows="4" readonly="readonly" <?php if($id == "") { ?> onclick="editCategoryDesc('Add')" <?php } ?> data-originalvalue="<?= isset($userEditDataArr['tCategoryDescription_'.$default_lang]) ? $userEditDataArr['tCategoryDescription_'.$default_lang] : '' ; ?>"><?= isset($userEditDataArr['tCategoryDescription_'.$default_lang]) ? $userEditDataArr['tCategoryDescription_'.$default_lang] : ''; ?></textarea>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCategoryDesc('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="cat_desc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Description
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tCategoryDescription_')">x</button>
                                                        </h4>
                                                    </div>
                                                    
                                                    <div class="modal-body">
                                                        <?php
                                                            
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];

                                                                $descVal = 'tCategoryDescription_' . $vCode;
                                                                $$descVal = isset($userEditDataArr[$descVal]) ? $userEditDataArr[$descVal] : '';

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
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tCategoryDescription_', 'EN');">Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tCategoryDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tCategoryDescription_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" id="tCategoryName_<?= $default_lang ?>" name="tCategoryName_<?= $default_lang ?>" value="<?= $userEditDataArr['tCategoryName_'.$default_lang]; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" name="tCategoryDescription_<?= $default_lang ?>" id="tCategoryDescription_<?= $default_lang ?>" rows="4"><?= $userEditDataArr['tCategoryDescription_'.$default_lang]; ?></textarea>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Display Order<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">

                                                <select class="form-control" name='iDisplayOrder' id="iDisplayOrder" >
                                                <?php
                                                    $html = '';
                                                    for ($i = 1; $i <= $max_usage_order; $i++) {
                                                        if ($action == "Add") {
                                                            if ($i == $max_usage_order) {
                                                                $selected = " selected";
                                                            } else {
                                                                $selected = " ";
                                                            }
                                                        } else {
                                                            if ($iDisplayOrder == $i) {
                                                                $selected = " selected";
                                                            } else {
                                                                $selected = " ";
                                                            }
                                                        }
                                                        $html .= '<option value = "' . $i . '" ' . $selected . '>' . $i . '</option>';
                                                    }
                                                    $html .= '</select>';
                                                    echo $html;

                                                    ?>
                                            </div>
                                            <input type="hidden" name="temp_order" id="temp_order" value="<?= $iDisplayOrder ?>">
                                        </div>
                                        <?php if($eType == "newly_open") { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Select Days<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <select class="form-control" name='iDaysRange' id="iDaysRange" >
                                                <?php for ($i = 1; $i <= 60; $i++) { ?>
                                                    <option value="<?= $i ?>" <?= ($i == $iDaysRange) ? 'selected' : '' ?>><?= $i ?> Day<?= ($i > 1) ? 's' : '' ?></option>
                                                <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php /*<div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <?php if ($tCategoryImage != '') { ?>
                                                    <img src="<?= $tconfig['tsite_upload_images_store_categories'] . "/" . $tCategoryImage; ?>" style="width:100px;height:100px;">
                                                <?php } ?>
                                                <input type="file" class="form-control" name="tCategoryImage" id="tCategoryImage" value="<?= $tCategoryImage; ?>">
                                            </div>
                                        </div>*/ ?>
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
                                                <?php if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission('edit-store-categories'))) { ?>
                                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Store Category" >
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <?php } ?>
                                                <a href="store_category.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
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
                            referrer = "store_category.php";
                        } else {
                            $("#backlink").val(referrer);
                        }
                        $(".back_link").attr('href', referrer);
                    });

                    $('textarea').keyup(function(e) {
                        var tval = $(this).val(),
                        tlength = tval.length,
                        set = 100,
                        remain = parseInt(set - tlength);
                            
                        if(tlength > 0)
                        {
                            $(this).closest('.col-md-6 col-sm-6').find('.desc_counter').text(remain + "/100");
                            
                            if (remain <= 0) {
                                $(this).val((tval).substring(0, set));
                                $(this).closest('.col-md-6 col-sm-6').find('.desc_counter').text("0/100");
                                return false;
                            }
                        }
                        else{
                            $(this).closest('.col-md-6 col-sm-6').find('.desc_counter').text("100/100");
                            return false;
                        }
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

                    function editCategoryName(action)
                    {
                        $('#modal_action').html(action);
                        $('#cat_name_Modal').modal('show');
                    }

                    function saveCategoryName()
                    {
                        if($('#tCategoryName_<?= $default_lang ?>').val() == "") {
                            $('#tCategoryName_<?= $default_lang ?>_error').show();
                            $('#tCategoryName_<?= $default_lang ?>').focus();
                            clearInterval(langVar);
                            langVar = setTimeout(function() {
                                $('#tCategoryName_<?= $default_lang ?>_error').hide();
                            }, 5000);
                            return false;
                        }

                        $('#tCategoryName_Default').val($('#tCategoryName_<?= $default_lang ?>').val());
                        $('#tCategoryName_Default').closest('.row').removeClass('has-error');
                        $('#tCategoryName_Default-error').remove();
                        $('#cat_name_Modal').modal('hide');
                    }

                    function editCategoryDesc(action)
                    {
                        $('#modal_action').html(action);
                        $('#cat_desc_Modal').modal('show');
                    }

                    function saveCategoryDesc()
                    {
                        if($('#tCategoryDescription_<?= $default_lang ?>').val() == "") {
                            $('#tCategoryDescription_<?= $default_lang ?>_error').show();
                            $('#tCategoryDescription_<?= $default_lang ?>').focus();
                            clearInterval(langVar);
                            langVar = setTimeout(function() {
                                $('#tCategoryDescription_<?= $default_lang ?>_error').hide();
                            }, 5000);
                            return false;
                        }

                        $('#tCategoryDescription_Default').val($('#tCategoryDescription_<?= $default_lang ?>').val());
                        $('#cat_desc_Modal').modal('hide');
                    }
                </script>
            </body>
            <!-- END BODY-->
        </html>