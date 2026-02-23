<?php
include_once('../common.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

if (!$userObj->hasPermission('edit-driver-subscription') && $action=='Edit') {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to update record';
    header("Location:" . $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION']);
    exit;
}
if (!$userObj->hasPermission('create-driver-subscription') && $action=='Add') {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to create record';
    header("Location:"  . $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION']);
    exit;
}

$script = 'DriverSubscriptionPlan';
$tblname = 'driver_subscription_plan';

$curDetails = $obj->MySQLSelect("SELECT vSymbol FROM `currency` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");
$CurrSymbol = $curDetails[0]['vSymbol'];

$vPlanName_arr = array();
$vPlanDesc_arr = array();

$sql = "SELECT * FROM `language_master` ORDER BY eDefault,iDispOrder";
$db_master = $obj->MySQLSelect($sql);

$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vPlanName_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vPlanDescription_' . $db_master[$i]['vCode'];

        array_push($vPlanName_arr, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';

        array_push($vPlanDesc_arr, $vValue_desc);
        $$vValue_desc = isset($_POST[$vValue_desc]) ? $_POST[$vValue_desc] : '';
    }
}


// set all variables with either post (when submit) either blank (when insert)
//$vPlanName = isset($_POST['vPlanName']) ? $_POST['vPlanName'] : '';
$ePlanValidity = isset($_POST['ePlanValidity']) ? $_POST['ePlanValidity'] : '';
$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$vPlanPeriod = isset($_POST['vPlanPeriod']) ? $_POST['vPlanPeriod'] : '';
//$vPlanDescription = isset($_POST['vPlanDescription']) ? $_POST['vPlanDescription'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submit'])) {

    if (SITE_TYPE == 'Demo') {
        header("Location:" . $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION_ACTION'] . "?id=" . $id . '&success=2');
        exit;
    }
    //Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    //$validobj->add_fields($_POST['vPlanName'], 'req', 'Plan Name is required');
    //$validobj->add_fields($_POST['ePlanValidity'], 'req', 'Email Address is required.');
    $validobj->add_fields($_POST['fPrice'], 'req', 'Price is required.');
    $error = $validobj->validate();
    if(!is_numeric($_POST['fPrice'])) {
        $error .= "Price must be in Proper Format";
    }
    if($_POST['vPlanPeriod']==0 || empty($_POST['vPlanPeriod'])) {
        $error .= "<br>Plan validity should not be zero";
    }
    if($_POST['ePlanValidity']=='Weekly' && $_POST['vPlanPeriod']>104) {
        $error .= "<br>Plan validity should not be greater than 104 weeks";
    }
    
    if($_POST['ePlanValidity']=='Monthly' && $_POST['vPlanPeriod']>24) {
        $error .= "<br>Plan validity should not be greater than 24 months";
    }
    
    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        
        for ($i = 0; $i < scount($vPlanName_arr); $i++) {

        $vValue = 'vPlanName_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vPlanDescription_' . $db_master[$i]['vCode'];
        
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Add') {
            $str = ",`tSubscribeDate` = '" . date("Y-m-d H:i:s") . "'";
        } else {
            $str = '';
        }
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverSubscriptionPlanId` = '" . $id . "'";
        }
        
                            
        $query = $q . " `" . $tblname . "` SET
        `vPlanName` = '" . $vPlanName . "',
        `vPlanPeriod` = '" . $vPlanPeriod . "',
        `ePlanValidity` = '" . $ePlanValidity . "',
        " . $vValue_desc . " = '" .$_POST[$vPlanDesc_arr[$i]] . "',
        " . $vValue . " = '" . $_POST[$vPlanName_arr[$i]] . "',
        `fPrice` = '" . $fPrice . "',
        `vPlanDescription` = '" . $vPlanDescription . "'
        $str "
        . $where;

        $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId();
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
}
// for Edit
$vPlanPeriod = 1;
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tblname . " WHERE iDriverSubscriptionPlanId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vPlanName_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_desc = 'vPlanDescription_' . $db_master[$i]['vCode'];
                $$vValue_desc = $value[$vValue_desc];
                //$vPlanName = $value['vPlanName_'.$default_lang];
                $vPlanPeriod = $value['vPlanPeriod'];
                $ePlanValidity = $value['ePlanValidity'];
                $fPrice = $value['fPrice'];
                //$vPlanDescription = $value['vPlanDescription_'.$default_lang];
                $iDriverSubscriptionPlanId = $value['iDriverSubscriptionPlanId'];
            }
        }
    }
    
    
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vPlanName = $value['vPlanName_'.$default_lang];
            $vPlanPeriod = $value['vPlanPeriod'];
            $ePlanValidity = $value['ePlanValidity'];
            $fPrice = $value['fPrice'];
            $vPlanDescription = $value['vPlanDescription_'.$default_lang];
            $iDriverSubscriptionPlanId = $value['iDriverSubscriptionPlanId'];
        }
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'] ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
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
                            <h2><?= $action; ?> Subscription <?= isset($vPlanName) ? $vPlanName: ''; ?></h2>
                            <a class="back_link" href="<?= $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION'] ?>">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>
                            <? if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php print_r($error); ?>
                                </div><br/>
                            <? } ?>
                            <form name="_subscription_form" id="_subscription_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="id" value="<?php echo isset($iDriverSubscriptionPlanId) ? $iDriverSubscriptionPlanId : ''; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="<?= $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION'] ?>"/>
                                    
                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME'] ?> <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set Plan Name"></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vPlanName_Default" name="vPlanName_Default" value="<?= isset($vPlanName) ? $vPlanName : ''; ?>" data-originalvalue="<?= isset($vPlanName) ? $vPlanName : ''; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editPlanName('Add')" <?php } ?> required>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editPlanName('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="plan_name_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME'] ?>
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPlanName_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vPlanName_' . $vCode;
                                                        
                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
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
                                                                <label><?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME'] ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
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
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPlanName_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPlanName_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="savePlanName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPlanName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_DESCRIPTION'] ?> <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Give brief details about the plan"></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vPlanDescription_Default" name="vPlanDescription_Default" value="<?= isset($vPlanDescription) ? $vPlanDescription: ''; ?>" data-originalvalue="<?= isset($vPlanDescription) ? $vPlanDescription: ''; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editPlanDesc('Add')" <?php } ?>>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editPlanDesc('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="plan_desc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_DESCRIPTION'] ?>
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPlanDescription_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vPlanDescription_' . $vCode;
                                                        
                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
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
                                                                <label><?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_DESCRIPTION'] ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
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
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPlanDescription_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPlanDescription_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="savePlanDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPlanDescription_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>

                                </div>

                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME'] ?> <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set Plan Name"></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vPlanName_<?= $default_lang ?>" name="vPlanName_<?= $default_lang ?>" value="<?= $vPlanName; ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_DESCRIPTION'] ?> <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Give brief details about the plan"></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vPlanDescription_<?= $default_lang ?>" name="vPlanDescription_<?= $default_lang ?>" value="<?= $vPlanDescription; ?>" required>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="">
                                    <div class="col-md-3 col-sm-3 pl-0">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_VALIDITY']; ?><span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the validity e.g. 1 Week, 3 Months.'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="number" class="form-control" name="vPlanPeriod"  id="vPlanPeriod" value="<?= $vPlanPeriod; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PERIOD']; ?>" required min="1">
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?><span class="red"> * </span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the Plans Duration'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select name="ePlanValidity" class="form-control" required>
                                                    <option value="">Select Plan</option>
                                                    <!--<option value="Daily" <?php if($ePlanValidity == 'Daily') echo "Selected"; ?>>Daily</option>-->
                                                    <option value="Weekly" <?php if($ePlanValidity == 'Weekly') echo "selected"; ?>><?php echo $langage_lbl_admin['LBL_SUB_WEEKS']; ?></option>
                                                    <option value="Monthly" <?php if($ePlanValidity == 'Monthly') echo "Selected"; ?>><?php echo $langage_lbl_admin['LBL_SUB_MONTH']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?> (<?php echo $CurrSymbol; ?>)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set plan price'></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="fPrice"  id="fPrice" value="<?= $fPrice; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                       <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> Subscription<?php } else { ?>Update<?php } ?>">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <a href="<?= $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION'] ?>" class="btn btn-default back_link">Cancel</a>
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
        
        <?
        include_once('footer.php');
        ?>
    </body>
    <!-- END BODY-->
</html>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "<?= $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION'] ?>";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
   

function editPlanName(action)
{
    $('#modal_action').html(action);
    $('#plan_name_Modal').modal('show');
}

function savePlanName()
{
    if($('#vPlanName_<?= $default_lang ?>').val() == "") {
        $('#vPlanName_<?= $default_lang ?>_error').show();
        $('#vPlanName_<?= $default_lang ?>').focus();
        clearInterval(langVar);
        langVar = setTimeout(function() {
            $('#vPlanName_<?= $default_lang ?>_error').hide();
        }, 5000);
        return false;
    }

    $('#vPlanName_Default').val($('#vPlanName_<?= $default_lang ?>').val());
    $('#vPlanName_Default').closest('.row').removeClass('has-error');
    $('#vPlanName_Default-error').remove();
    $('#plan_name_Modal').modal('hide');
}

function editPlanDesc(action)
{
    $('#modal_action').html(action);
    $('#plan_desc_Modal').modal('show');
}

function savePlanDesc()
{
    if($('#vPlanDescription_<?= $default_lang ?>').val() == "") {
        $('#vPlanDescription_<?= $default_lang ?>_error').show();
        $('#vPlanDescription_<?= $default_lang ?>').focus();
        clearInterval(langVar);
        langVar = setTimeout(function() {
            $('#vPlanDescription_<?= $default_lang ?>_error').hide();
        }, 5000);
        return false;
    }

    $('#vPlanDescription_Default').val($('#vPlanDescription_<?= $default_lang ?>').val());
    $('#vPlanDescription_Default').closest('.row').removeClass('has-error');
    $('#vPlanDescription_Default-error').remove();
    $('#plan_desc_Modal').modal('hide');
}

</script>
