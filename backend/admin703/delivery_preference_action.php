<?php
include_once('../common.php');

if(!$MODULES_OBJ->isDeliveryPreferenceEnable()){
    $userObj->redirect();
}

$tbl_name = 'delivery_preferences';
$script = 'DeliveryPreferences';
    
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
$eImageUpload = isset($_POST['eImageUpload']) ? $_POST['eImageUpload'] : 'No';
$ePreferenceFor = isset($_POST['ePreferenceFor']) ? $_POST['ePreferenceFor'] : '';
$eContactLess = isset($_POST['eContactLess']) ? $_POST['eContactLess'] : 'No';
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$txtBoxNameArr = array("tTitle");
$lableArr = array("Title");

if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-delivery-preference')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create '. $langage_lbl_admin['LBL_DELIVERY_PREF'];
        header("Location:" . $redirectUrl);

        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-delivery-preference')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin['LBL_DELIVERY_PREF'];
        header("Location:delivery_preferences.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = '2';
        header("Location:delivery_preferences.php");
        exit;
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        $tCategoryName = $tDescription = "";
        if (isset($_POST['tTitle_' . $db_master[$i]['vCode']])) {
            $tCategoryName = $_POST['tTitle_' . $db_master[$i]['vCode']];
        }
        if (isset($_POST['tDescription_' . $db_master[$i]['vCode']])) {
            $tDescription = $_POST['tDescription_' . $db_master[$i]['vCode']];
        }
        $q = "INSERT INTO ";
        $where = '';

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iPreferenceId` = '" . $id . "'";
        }
        $vtitleArr["tTitle_" . $db_master[$i]['vCode']] = $tCategoryName;
        $descArr["tDescription_" . $db_master[$i]['vCode']] = $tDescription;
    }
    
    $str = '';
    /*if ($eImageUpload == '') {
        $str .= ", eImageUpload = 'No' ";
    } else {
        $str .= ", eImageUpload = '".$eImageUpload."'";
    }*/

    if($eContactLess == "No") {
        $str .= ", eImageUpload = 'No' ";
    }
    
    if ($eStatus == '') {
        $str .= ", eStatus = 'Inactive' ";
    } else {
        $str .= ", eStatus = 'Active'";
    }

    if (scount($vtitleArr) > 0) {
        $jsonTitle = getJsonFromAnArr($vtitleArr);
        $jsonDesc = getJsonFromAnArr($descArr);
        $query = $q . " `" . $tbl_name . "` SET `tTitle` = '" . $jsonTitle . "',`tDescription` = '" . $jsonDesc . "', `iDisplayOrder` = '" . $iDisplayOrder . "', `ePreferenceFor` = '" . $ePreferenceFor . "' $str " . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    $_SESSION['success'] = "1";
    header("location:" . $backlink);
    exit;
}

// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iPreferenceId = '" . $id . "'";
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
        $eImageUpload = $db_data[0]['eImageUpload'];
        $eStatus = $db_data[0]['eStatus'];
        $iDisplayOrder = $db_data[0]['iDisplayOrder'];
        $ePreferenceFor = $db_data[0]['ePreferenceFor'];
        $eContactLess = $db_data[0]['eContactLess'];
    }
}

$display_order = array();
$sqlall = "SELECT iDisplayOrder FROM " . $tbl_name;
$db_data_all = $obj->MySQLSelect($sqlall);
foreach ($db_data_all as $d) {
    $display_order[] = $d['iDisplayOrder'];
}

if ($action == 'Edit') {
    $max_usage_order = max($display_order);
} else {
    $max_usage_order = max($display_order) + 1;
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
                <title><?= $SITE_NAME ?> |  <?=  $langage_lbl_admin['LBL_DELIVERY_PREF'].' '.$action; ?></title>
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
                                    <h2> <?= $action.' '. $langage_lbl_admin['LBL_DELIVERY_PREF']; ?></h2>
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
                                    <form id="_delivery_preference" name="_delivery_preference" method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="eContactLess" value="<?= $eContactLess; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="delivery_preferences.php"/>
                                        <div class="row">
                                            <div class="col-lg-12" id="errorMessage"></div>
                                        </div>

                                        <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Delivery Preference Name <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="tTitle_Default" name="tTitle_Default" value="<?= isset($userEditDataArr['tTitle_'.$default_lang]) ? $userEditDataArr['tTitle_'.$default_lang] : '' ; ?>" data-originalvalue="<?= isset($userEditDataArr['tTitle_'.$default_lang]) ? $userEditDataArr['tTitle_'.$default_lang] : ''; ?>" readonly="readonly" required <?php if($id == "") { ?> onclick="editDeliveryPreference('Add')" <?php } ?>>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDeliveryPreference('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="DeliveryPreferenceName_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Delivery Preference Name
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
                                                                        <label>Delivery Preference Name (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        
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
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tTitle_', 'EN');">Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDeliveryPreference()"><?= $langage_lbl['LBL_Save']; ?></button>
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
                                                <textarea class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" name="tDescription_Default" id="tDescription_Default" data-originalvalue="<?= isset($userEditDataArr['tDescription_'.$default_lang]) ?  $userEditDataArr['tDescription_'.$default_lang] : ''; ?>" rows="4" readonly="readonly" <?php if($id == "") { ?> onclick="editDeliveryPreferenceDesc('Add')" <?php } ?>><?= isset($userEditDataArr['tDescription_'.$default_lang]) ?  $userEditDataArr['tDescription_'.$default_lang] : ''; ?></textarea>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDeliveryPreferenceDesc('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="DeliveryPreferenceDesc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Description
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
                                                                $$descVal = isset($userEditDataArr['tDescription_' . $vCode]) ? $userEditDataArr['tDescription_' . $vCode] : '';

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
                                                                        <label>Delivery Preference Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        
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
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', 'EN');">Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else { 
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDeliveryPreferenceDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
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
                                                <label>Delivery Preference Name <span class="red"> *</span></label>
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

                                        <?php /*if (scount($db_master) > 0) {
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
                                                    <div class="col-md-6 col-sm-6">
                                                        <!-- <?= $lableName; ?> -->
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
                                                    <textarea class="form-control" name="<?= $descVal; ?>" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" rows="4"><?= $userEditDataArr[$descVal]; ?></textarea>
                                                    <div class="text-danger pull-left" id="<?= $descVal.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    <div class="desc_counter pull-right" style="margin-top: 5px">250/250</div>
                                                </div>
                                                <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                <div class="col-md-6 col-sm-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        <?php }
                                            }*/
                                            ?>
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
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Deliver Preference For</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <select class="form-control" name="ePreferenceFor" id="ePreferenceFor">

                                                    <?php
                                                    $Store_txt = $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];
                                                    $Provider_txt =  $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];

                                                   /* if (ONLYDELIVERALL == 'Yes') {
                                                        $Store_txt = "Restaurant";
                                                        $Provider_txt = "Delivery Driver";
                                                    }*/
                                                    ?>


                                                    <option value="Store" <?= ($ePreferenceFor == "Store") ? "selected" : "" ?>><?= $Store_txt ?></option>
                                                    <option value="Provider" <?= ($ePreferenceFor == "Provider") ? "selected" : "" ?>><?= $Provider_txt ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php /*if($action == "Edit" && $eContactLess == "No") { ?>
                                        <div class="row" id="image_upload_pref" <?php if($ePreferenceFor != "Provider") { ?> style="display: none;" <?php } ?>>
                                            <div class="col-lg-12">
                                                <label>Image Upload</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eImageUpload" id="eImageUpload" <?= ($eImageUpload == 'Yes') ? 'checked' : ''; ?> value="Yes" />
                                                </div>
                                            </div>
                                        </div>
                                        <?php }*/ ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eStatus" id="eStatus" <?= ($eStatus == 'Active') ? 'checked' : ''; ?> value="Active" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Delivery Preference" >
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <a href="delivery_preferences.php" class="btn btn-default back_link">Cancel</a>
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
                            referrer = "delivery_preferences.php";
                        } else {
                            $("#backlink").val(referrer);
                        }
                        $(".back_link").attr('href', referrer);

                        $('textarea').trigger('keyup');
                    });

                    $('textarea').on('keyup change', function(e) {
                        var tval = $(this).val(),
                            tlength = tval.length,
                            set = 250,
                            remain = parseInt(set - tlength);                            
                        if(tlength > 0)
                        {
                            $(this).closest('.col-md-6 col-sm-6').find('.desc_counter').text(remain + "/250");
                            
                            if (remain <= 0) {
                                $(this).val((tval).substring(0, set));
                                $(this).closest('.col-md-6 col-sm-6').find('.desc_counter').text("0/250");
                                return false;
                            }
                        }
                        else{
                            $(this).closest('.col-md-6 col-sm-6').find('.desc_counter').text("250/250");
                            return false;
                        }
                    });

                    $('#ePreferenceFor').change(function() {
                        if($(this).val() == "Provider")
                        {
                            $('#image_upload_pref').show();
                        }
                        else{
                            $('#image_upload_pref').hide();
                            $('#eImageUpload').prop('checked', false);
                            $('#image_upload_pref').find('.switch-animate').removeClass('switch-on').addClass('switch-off');
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

                    function editDeliveryPreference(action)
                    {
                        $('#modal_action').html(action);
                        $('#DeliveryPreferenceName_Modal').modal('show');
                    }

                    function saveDeliveryPreference()
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
                        $('#DeliveryPreferenceName_Modal').modal('hide');
                    }

                    function editDeliveryPreferenceDesc(action)
                    {
                        $('#modal_action').html(action);
                        $('#DeliveryPreferenceDesc_Modal').modal('show');
                    }

                    function saveDeliveryPreferenceDesc()
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
                        $('#DeliveryPreferenceDesc_Modal').modal('hide');
                    }
                </script>
            </body>
            <!-- END BODY-->
        </html>