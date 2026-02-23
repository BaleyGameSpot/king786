<?php
include_once('../common.php');
require_once(TPATH_CLASS . "Imagecrop.class.php");

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iFaqcategoryId
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$faq_cat_id = isset($_REQUEST['faq_cat_id']) ? $_REQUEST['faq_cat_id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

if ($action == "Add" && !$userObj->hasPermission('create-faq')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to create FAQ.';
    header("Location:faq.php");
    exit;
}

if ($action == "Edit" && !$userObj->hasPermission('edit-faq')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to update FAQ.';
    header("Location:faq.php");
    exit;
}


//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name = 'faqs';
$script = 'Faq';
// fetch all lang from language_master table 
// $sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder_max = $iDisplayOrder + 1; // Maximum order number

$iFaqcategoryId = isset($_POST['iFaqcategoryId']) ? $_POST['iFaqcategoryId'] : $faq_cat_id;
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
        $$vTitle = isset($_POST[$vTitle]) ? $_POST[$vTitle] : '';
        $tAnswer = 'tAnswer_' . $db_master[$i]['vCode'];
        $$tAnswer = isset($_POST[$tAnswer]) ? $_POST[$tAnswer] : '';
    }
}


if (isset($_POST['submit'])) { //form submit

    if (SITE_TYPE == 'Demo') {
        header("Location:faq_action.php?id=" . $id . "&faq_cat_id=" . $faq_cat_id . "&success=2");
        exit;
    }
    
    if ($temp_order == "1" && $action == "Add") {
        $temp_order = $iDisplayOrder_max;
    }
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "'";
            $obj->sql_query($sql);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "'";
            $obj->sql_query($sql);
        }
    }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iFaqId` = '" . $id . "'";
    }
    $sql_str = '';
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
            $sql_str .= $vTitle . " = '" . $$vTitle . "',";
            $tAnswer = 'tAnswer_' . $db_master[$i]['vCode'];
            $sql_str .= $tAnswer . " = '" . $$tAnswer . "',";
        }
    }

    $query = $q . " `" . $tbl_name . "` SET 	
				" . $sql_str . "
				`eStatus` = '" . $eStatus . "',
				`iFaqcategoryId` = '" . $iFaqcategoryId . "',
				`iDisplayOrder` = '" . $iDisplayOrder . "'"
            . $where;
    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('SetFaqs');
    }
    updateSystemData();
    
    header("location:" . $backlink);
    exit;
}


// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iFaqId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);


    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vTitle_' . $db_master[$i]['vCode'];
            $$vTitle = isset($db_data[0][$vTitle]) ? $db_data[0][$vTitle] : $$vTitle;
            $tAnswer = 'tAnswer_' . $db_master[$i]['vCode'];
            $$tAnswer = isset($db_data[0][$tAnswer]) ? $db_data[0][$tAnswer] : $$tAnswer;

            $eStatus = $db_data[0]['eStatus'];
            $iDisplayOrder_db = $db_data[0]['iDisplayOrder'];
        }
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | FAQ  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

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
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>       
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> FAQ </h2>
                            <a href="faq.php" class="back_link">
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
                            <?php } ?>

                            <?php if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>

                            <form method="post" name="_faq_form" id="_faq_form" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="faq.php"/>
                                <?php
                                $sql = "SELECT * FROM faq_categories WHERE vCode = '" . $default_lang . "' AND eStatus = 'Active' ORDER BY  vTitle ASC ";
                                $db_cat = $obj->MySQLSelect($sql);

                                if (scount($db_cat) > 0) {
                                    if($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes'){
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Category</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <select name="iFaqcategoryId" id="iFaqcategoryId" class="form-control">
                                                <?php for ($i = 0; $i < scount($db_cat); $i++) { ?>
                                                    <option value="<?= $db_cat[$i]['iUniqueId']; ?>" <?= ($db_cat[$i]['iUniqueId'] == $faq_cat_id) ? 'selected' : ''; ?>>
                                                        -- <?= $db_cat[$i]['vTitle'] ?> --
                                                    </option>
                                                <?php } ?>
                                            </select>
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
                                    <?php /* if($action == 'Edit') { */ ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Order</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">

                                            <input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">
                                            <?php
                                            $display_numbers = ($action == "Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                            ?>
                                            <select name="iDisplayOrder" class="form-control">
                                                <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                    <option value="<?= $i ?>" <?php
                                                    if (isset($iDisplayOrder_db) && $i == $iDisplayOrder_db) {
                                                        echo "selected";
                                                    }
                                                    ?>> -- <?= $i ?> --</option>
                                                <?php } ?>
                                            </select>

                                        </div>
                                    </div>

                                    <?php if (scount($db_master) > 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Question <span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vTitle_Default" name="vTitle_Default" value="<?= isset($db_data[0]['vTitle_'.$default_lang]) ? $db_data[0]['vTitle_'.$default_lang] : ''; ?>" data-originalvalue="<?= isset($db_data[0]['vTitle_'.$default_lang]) ? $db_data[0]['vTitle_'.$default_lang] : ''; ?>" readonly="readonly" required <?php if(empty($id)) { ?> onclick="editFaq('Add')" <?php } ?>>
                                        </div>
                                        <?php if($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editFaq('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                        </div>
                                        <?php } ?>
                                    </div>

                                    

                                    <div  class="modal fade" id="Faq_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg" >
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="modal_action"></span> FAQ - Question
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTitle_')">x</button>
                                                    </h4>
                                                </div>
                                                
                                                <div class="modal-body">
                                                    <?php
                                                        
                                                        for ($i = 0; $i < $count_all; $i++) 
                                                        {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vLTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $vTitle = 'vTitle_' . $vCode;

                                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Question (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                    
                                                                </div>
                                                                <?php
                                                                $page_title_class = 'col-lg-12';
                                                                if (scount($db_master) > 1) {
                                                                    if($EN_available) {
                                                                        if($vCode == "EN") { 
                                                                            $page_title_class = 'col-lg-9';
                                                                        }
                                                                    } else { 
                                                                        if($vCode == $default_lang) {
                                                                            $page_title_class = 'col-lg-9';
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                <div class="<?= $page_title_class ?>">
                                                                    <input type="text" class="form-control" name="<?= $vTitle; ?>" id="<?= $vTitle; ?>" value="<?= $$vTitle; ?>" data-originalvalue="<?= $$vTitle; ?>" placeholder="<?= $vLTitle; ?> Value">
                                                                    <div class="text-danger" id="<?= $vTitle.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>

                                                                <?php
                                                                if (scount($db_master) > 1) {
                                                                    if($EN_available) {
                                                                        if($vCode == "EN") { ?>
                                                                        <div class="col-lg-3">
                                                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', 'EN');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                    } else { 
                                                                        if($vCode == $default_lang) { ?>
                                                                        <div class="col-lg-3">
                                                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveFaq()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTitle_')">Cancel</button>
                                                    </div>
                                                </div>
                                                
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                    </div>                                    
                                    <?php } else { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Question <span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" class="form-control" id="vTitle_<?= $default_lang ?>" name="vTitle_<?= $default_lang ?>" value="<?= $db_data[0]['vTitle_'.$default_lang]; ?>" required>
                                        </div>
                                    </div>

                                    <?php /*<div class="row">
                                        <div class="col-lg-12">
                                            <label>Answer <span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <textarea class="form-control wysihtml5" rows="10" id="tAnswer_<?= $default_lang ?>" name="tAnswer_<?= $default_lang ?>" required><?= $db_data[0]['tAnswer_'.$default_lang]; ?></textarea>
                                        </div>
                                    </div>*/?>
                                    <?php } ?>

                                    <?php if (scount($db_master) > 0) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Answer <span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <textarea name = "tAnswer_Default" class="form-control wysihtml5" rows="10" id="tAnswer_Default" onclick="e.preventDefault()" readonly="readonly"><?= isset($db_data[0]['tAnswer_'.$default_lang]) ? $db_data[0]['tAnswer_'.$default_lang] : ''; ?></textarea>
                                        </div>
                                        <?php //if($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDetails('Edit', 'FaqAns_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                        </div>
                                        <?php //} ?>
                                    </div>
                                    <div  class="modal fade" id="FaqAns_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg" >
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="modal_action"></span> FAQ - Answer
                                                        <button type="button" class="close" data-dismiss="modal">x</button>
                                                    </h4>
                                                </div>
                                                
                                                <div class="modal-body">
                                                    <?php
                                                        
                                                        for ($i = 0; $i < $count_all; $i++) 
                                                        {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vLTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];

                                                            $tAnswer = 'tAnswer_' . $vCode;
                                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Answer (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                    
                                                                </div>
                                                                <div class="col-lg-12">
                                                                    <textarea class="form-control wysihtml5" rows="10" name="<?= $tAnswer; ?>"  id="<?= $tAnswer; ?>"  placeholder="<?= $vLTitle; ?> Value"> <?= $$tAnswer; ?></textarea>
                                                                    <div class="text-danger" id="<?= $tAnswer.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                            </div> 
                                                        <?php 
                                                        }
                                                    ?>
                                                </div>
                                                <div class="modal-footer" style="margin-top: 0">
                                                    <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                    <div class="nimot-class-but" style="margin-bottom: 0">
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDetails('tAnswer_', 'FaqAns_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                    </div>
                                                </div>
                                                
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>

                                    </div>
                                    <?php } ?>
                                    <!-- <?php
                                    if ($count_all > 0) {
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];

                                            $vTitle_val = "vTitle_" . $vCode;
                                            $tAnswer_val = "tAnswer_" . $vCode;

                                            $eDefault = $db_master[$i]['eDefault'];

                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $vTitle; ?> Question <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <input type="text" class="form-control" name="<?= $vTitle_val; ?>"  id="<?= $vTitle_val; ?>" value="<?= $$vTitle_val; ?>" placeholder="FAQ" <?= $required; ?>>
                                                    <div class="text-danger" id="<?= $vTitle_val.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                </div>
                                                <?php if($vCode == $default_lang  && scount($db_master) > 1){ ?>
                                                    <div class="col-md-6 col-sm-6">
                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', '<?= $default_lang ?>');"> Convert To All Language</button>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $vTitle; ?> Answer <?= $required_msg; ?></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control wysihtml5" name="<?= $tAnswer_val; ?>"  id="<?= $tAnswer_val; ?>" placeholder="Answer" <?= $required; ?>><?= $$tAnswer_val; ?></textarea>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    }
                                    ?> -->
                                    <div class="row">
                                        <div class="col-lg-12">		
                                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-faq')) || ($action == 'Add' && $userObj->hasPermission('create-faq'))) { ?>					
                                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> FAQ">
                                                <input type="reset" value="Reset" class="btn btn-default">
                                            <?php } ?>
                                            <!-- <a href="javascript:void(0);" onclick="reset_form('_faq_form');" class="btn btn-default">Reset</a> -->
                                            <a href="faq.php" class="btn btn-default back_link">Cancel</a>
                                        </div>
                                    </div>
                                    <?php } else { ?>
                                    Please enter FAQ Catgory
                                    <?php } ?>
                            </form>
                        </div>
                    </div>
                    <div class="clear"></div>
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
</script>
    </body>
    <!-- END BODY--> 
    
    <script>
        $(document).ready(function () {
            var referrer;
            if ($("#previousLink").val() == "") {
                alert(referrer);
                referrer = document.referrer;

            } else {
                referrer = $("#previousLink").val();
            }
            if (referrer == "") {
                referrer = "faq.php";
            } else {
                $("#backlink").val(referrer);
            }
            $(".back_link").attr('href', referrer);
        });
        /**
         * This will reset the CKEDITOR using the input[type=reset] clicks.
         */
        /*$(function () {
            if (typeof CKEDITOR != 'undefined') {
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
        });*/

        function editFaq(action)
        {
            $('#modal_action').html(action);
            $('#Faq_Modal').modal('show');
        }

        function saveFaq()
        {
            // var editorObj1 = $("#tAnswer_<?= $default_lang ?>").data('wysihtml5');
            // var editorElem1 = editorObj1.editor;

            // var editorObj2 = $("#tAnswer_Default").data('wysihtml5');
            // var editorElem2 = editorObj2.editor;

            // var tAnswerLength = editorElem1.getValue().length;

            $('#vTitle_<?= $default_lang ?>_error, #tAnswer_<?= $default_lang ?>_error').hide();
            if($('#vTitle_<?= $default_lang ?>').val() == "") {
                $('#vTitle_<?= $default_lang ?>_error').show();
                $('#vTitle_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function() {
                    $('#vTitle_<?= $default_lang ?>_error').hide();
                }, 5000);
                return false;
            }
            /*else if(!tAnswerLength) {
                $('#tAnswer_<?= $default_lang ?>_error').show();
                $('#tAnswer_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function() {
                    $('#tAnswer_<?= $default_lang ?>_error').hide();
                }, 5000);
                e.preventDefault();
                return false;
            }*/

            $('#vTitle_Default').val($('#vTitle_<?= $default_lang ?>').val());
            $('#vTitle_Default').closest('.row').removeClass('has-error');
            $('#vTitle_Default-error').remove();
            
            // var tAnswerHTML = editorElem1.getValue();
            // editorElem2.setValue(tAnswerHTML);
            
            $('#Faq_Modal').modal('hide');
        }

        function editDetails(action, modal_id)
        {
            $('#'+modal_id).find('#modal_action').html(action);
            $('#'+modal_id).modal('show');
        }

        function saveDetails(input_id, modal_id)
        {
            var editorObj1 = $('#'+input_id+'<?= $default_lang ?>').data('wysihtml5');
            var editorElem1 = editorObj1.editor;

            var editorObj2 = $('#'+input_id+'Default').data('wysihtml5');
            var editorElem2 = editorObj2.editor;

            var tAnswerLength = editorElem1.getValue().length;

            if(!tAnswerLength) {
                $('#'+input_id+'<?= $default_lang ?>_error').show();
                $('#'+input_id+'<?= $default_lang ?>').focus();
                clearInterval(myVar);
                myVar = setTimeout(function() {
                    $('#'+input_id+'<?= $default_lang ?>_error').hide();
                }, 5000);
                e.preventDefault();
                return false;
            }

            var tAnswerHTML = editorElem1.getValue();
            editorElem2.setValue(tAnswerHTML);
            $('#'+modal_id).modal('hide');

            $("#_faq_form").validate().element("#tAnswer_Default");
        }

        /*------------------for field verification-----------------*/
        var DEFAULT_LANG = "<?= $default_lang ?>";
        var default_lang_field = $("#tAnswer_"+DEFAULT_LANG);
        var default_lang_field_error = $("#tAnswer_"+DEFAULT_LANG+'_error');
        default_lang_field.on("keyup  change", function() {

            if(default_lang_field.val() === "")
            {
                default_lang_field_error.show();
                default_lang_field.focus();
            }else{
                default_lang_field_error.hide();
            }

        });
        /*------------------for field verification-----------------*/
    </script>	
</html>