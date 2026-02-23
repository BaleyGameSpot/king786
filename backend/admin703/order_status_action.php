<?php
include_once('../common.php');

require_once(TPATH_CLASS . "Imagecrop.class.php");
$thumb = new thumbnail();

//$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name = 'order_status';
$script = 'order_status';


// fetch all lang from language_master table 
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);

// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';

$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder = $iDisplayOrder + 1; // Maximum order number

$iOrderStatusId = isset($_POST['iOrderStatusId']) ? $_POST['iOrderStatusId'] : 0;
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vTitle = 'vStatus_' . $db_master[$i]['vCode'];
        $$vTitle = isset($_POST[$vTitle]) ? $_POST[$vTitle] : '';
        $vDesc = 'vStatus_Track_' . $db_master[$i]['vCode'];
        $$vDesc = isset($_POST[$vDesc]) ? $_POST[$vDesc] : '';
    }
}
  
if (isset($_POST['submit'])) { //form submit
    if ($action == "Add" && !$userObj->hasPermission('create-order-status')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create order status.';
        header("Location:order_status.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-order-status')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update order status.';
        header("Location:order_status.php");
        exit;
    }

    // if (!empty($iOrderStatusId)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:order_status_action.php?id=" . $id . "&success=2");
            exit;
        }
    // }

    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE iDisplayOrder = " . $i;
            $obj->sql_query($sql);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i;
            $obj->sql_query($sql);
        }
    }


    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iOrderStatusId` = '" . $id . "'";
    }
    $sql_str = '';
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vStatus_' . $db_master[$i]['vCode'];
            $vDesc = 'vStatus_Track_' . $db_master[$i]['vCode'];
            $sql_str .= $vTitle . " = '" . $$vTitle . "',";
            $sql_str .= $vDesc . " = '" . $$vDesc . "',";
        }
    }

    $query = $q . " `" . $tbl_name . "` SET  " . $sql_str . "
                `iDisplayOrder` = '" . $iDisplayOrder . "'"
            . $where;
    $obj->sql_query($query);
    // print_r($query);
    // exit;        

    $id = ($id != '') ? $id : $obj->GetInsertId();

    //header("Location:cancel_reason_action.php?id=".$id."&success=1");
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
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iOrderStatusId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vTitle = 'vStatus_' . $db_master[$i]['vCode'];
            $$vTitle = isset($db_data[0][$vTitle]) ? $db_data[0][$vTitle] : $$vTitle;
            $vDesc = 'vStatus_Track_' . $db_master[$i]['vCode'];
            $$vDesc = isset($db_data[0][$vDesc]) ? $db_data[0][$vDesc] : $$vDesc;
            $iDisplayOrder = $db_data[0]['iDisplayOrder'];

            $userEditDataArr[$vTitle] = $$vTitle;
            $userEditDataArr[$vDesc] = $$vDesc;
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
                <title>Admin | Order Status</title>
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
                                    <h2><?= $action; ?> Order Status</h2>
                                    <a href="order_status.php">
                                    <input type="button" value="Back to Listing" class="add-btn">
                                    </a>
                                </div>
                            </div>
                            <hr />
                            <div class="body-div">
                                <div class="form-group">
                                    <?php if ($success == 0 && !empty($_REQUEST['var_msg'])) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $_REQUEST['var_msg']; ?>
                                    </div>
                                    <br/>
                                    <?php } ?>
                                    <?php if ($success == 1) { ?>
                                    <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                    </div>
                                    <br/>
                                    <?php } ?>
                                    <?php if ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                    </div>
                                    <br/>
                                    <?php } ?>
                                    <form method="post" action="" enctype="multipart/form-data" id="order_status_action" name="order_status_action">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="temp_order" id="temp_order" value="1 ">
                                        <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                                        <input type="hidden" name="backlink" id="backlink" value="order_status.php"/>

                                        <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status Title <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vStatus_Default" name="vStatus_Default" value="<?= $userEditDataArr['vStatus_'.$default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vStatus_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editStatusTitle('Add')" <?php } ?>>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editStatusTitle('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <div  class="modal fade" id="status_title_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Status Title
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vStatus_')">x</button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vStatus_' . $vCode;
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
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vStatus_', 'EN');" >Convert To All Language</button>
                                                            </div>
                                                            <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vStatus_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveStatusTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vStatus_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status Description <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vStatus_Track_Default" name="vStatus_Track_Default" rows="4" readonly="readonly" <?php if($id == "") { ?> onclick="editStatusDesc('Add')" <?php } ?> data-originalvalue="<?= $userEditDataArr['vStatus_Track_'.$default_lang]; ?>"><?= $userEditDataArr['vStatus_Track_'.$default_lang]; ?></textarea>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editStatusDesc('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <div  class="modal fade" id="status_desc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Status Description
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vStatus_Track_')">x</button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                            for ($i = 0; $i < $count_all; $i++) 
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                            
                                                                $descVal = 'vStatus_Track_' . $vCode;
                                                                $$descVal = $userEditDataArr[$descVal];
                                                            
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
                                                                <label>Status Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
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
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vStatus_Track_', 'EN');" >Convert To All Language</button>
                                                            </div>
                                                            <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vStatus_Track_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveStatusDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vStatus_Track_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status Title <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" id="vStatus_<?= $default_lang ?>" name="vStatus_<?= $default_lang ?>" value="<?= $userEditDataArr['vStatus_'.$default_lang]; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Status Description <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" id="vStatus_Track_<?= $default_lang ?>" name="vStatus_Track_<?= $default_lang ?>" rows="4" required><?= $userEditDataArr['vStatus_Track_'.$default_lang]; ?></textarea>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php /*
                                            //echo '<pre>';print_r($db_data);exit;
                                            //echo '<pre>';print_r($db_master);exit;
                                            if ($count_all > 0) {
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                            
                                                    $vTitle_val = "vStatus_" . $vCode;
                                                    $vDesc_val = "vStatus_Track_" . $vCode;
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $label_title = "Status Title(" . $vTitle . ")";
                                                    $label_desc = "Status Description(" . $vTitle . ")";
                                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                            
                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                    ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $label_title; ?> <?= $required_msg; ?></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" name="<?= $vTitle_val; ?>"  id="<?= $vTitle_val; ?>" value="<?= $$vTitle_val; ?>" placeholder="<?= $label_title; ?>" <?= $required; ?>>
                                                <div class="text-danger" id="<?= $vTitle_val.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                            <?php
                                                if ($vCode == $default_lang) {
                                                    ?>
                                            <div class="col-md-6 col-sm-6">
                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vStatus_', '<?= $default_lang ?>');">Convert To All Language</button>
                                            </div>
                                            <?php
                                                }
                                                ?>
                                        </div>
                                        <?php
                                            }
                                            }
                                            
                                            if ($count_all > 0) {
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $vDesc_val = "vStatus_Track_" . $vCode;
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $label_title = "Status Title(" . $vTitle . ")";
                                                $label_desc = "Status Description(" . $vTitle . ")";
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                            
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $label_desc; ?> <?= $required_msg; ?></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea name="<?= $vDesc_val; ?>"  id="<?= $vDesc_val; ?>" class="form-control" rows="5" placeholder="<?= $label_desc; ?>"><?= $$vDesc_val; ?></textarea>
                                                <div class="text-danger" id="<?= $vDesc_val.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                            </div>
                                            <?php
                                                if ($vCode == $default_lang) {
                                                    ?>
                                            <div class="col-md-6 col-sm-6">
                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vStatus_Track_', '<?= $default_lang ?>');">Convert To All Language</button>
                                            </div>
                                            <?php
                                                }
                                                ?>
                                        </div>
                                        <?php }
                                            }*/
                                            ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-order-status')) || ($action == 'Add' && $userObj->hasPermission('create-order-status'))) { ?>
                                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Order Status" >
                                                <input type="reset" value="Reset" class="btn btn-default">
                                                <?php } ?>
                                                <!-- <a href="javascript:void(0);" onclick="reset_form('cancel_reason_action');" class="btn btn-default">Reset</a> -->
                                                <a href="order_status.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
                                        </div>
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

                    function editStatusTitle(action)
                    {
                        $('#modal_action').html(action);
                        $('#status_title_Modal').modal('show');
                    }

                    function saveStatusTitle()
                    {
                        if($('#vStatus_<?= $default_lang ?>').val() == "") {
                            $('#vStatus_<?= $default_lang ?>_error').show();
                            $('#vStatus_<?= $default_lang ?>').focus();
                            clearInterval(langVar);
                            langVar = setTimeout(function() {
                                $('#vStatus_<?= $default_lang ?>_error').hide();
                            }, 5000);
                            return false;
                        }

                        $('#vStatus_Default').val($('#vStatus_<?= $default_lang ?>').val());
                        $('#vStatus_Default').closest('.row').removeClass('has-error');
                        $('#vStatus_Default-error').remove();
                        $('#status_title_Modal').modal('hide');
                    }

                    function editStatusDesc(action)
                    {
                        $('#modal_action').html(action);
                        $('#status_desc_Modal').modal('show');
                    }

                    function saveStatusDesc()
                    {
                        if($('#vStatus_Track_<?= $default_lang ?>').val() == "") {
                            $('#vStatus_Track_<?= $default_lang ?>_error').show();
                            $('#vStatus_Track_<?= $default_lang ?>').focus();
                            clearInterval(langVar);
                            langVar = setTimeout(function() {
                                $('#vStatus_Track_<?= $default_lang ?>_error').hide();
                            }, 5000);
                            return false;
                        }

                        $('#vStatus_Track_Default').val($('#vStatus_Track_<?= $default_lang ?>').val());
                        $('#vStatus_Track_Default').closest('.row').removeClass('has-error');
                        $('#vStatus_Track_Default-error').remove();
                        $('#status_desc_Modal').modal('hide');
                    }
                    console.log(_system_script);
                </script>
            </body>
            <!-- END BODY-->    
        </html>