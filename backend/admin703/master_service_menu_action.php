<?php
include_once('../common.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId
$parentid = isset($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;


if (!$userObj->hasPermission('manage-our-service-menu')) {
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

$script = 'masterServiceMenu';
$tbl_name = "master_service_menu";

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);

$vCategoryName = isset($_POST['vCategoryName']) ? $_POST['vCategoryName'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Active';
$fCommission = isset($_POST['fCommission']) ? $_POST['fCommission'] : '0';

$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';


$thumb = new thumbnail();

if (isset($_POST['submit'])) { //form submit
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = "2";
        header("Location:master_service_menu.php");
        exit;
    }
    
    $i = $iDisplayOrder;
    $temp_order = $_REQUEST['oldDisplayOrder'];

    
    
    if ($temp_order > $iDisplayOrder) {
    
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            $obj->sql_query("UPDATE master_service_menu SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "' ");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            $obj->sql_query("UPDATE master_service_menu SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "'");
            
        }
    }

    //exit;

    for ($i = 0; $i < scount($db_master); $i++) {
        $vCategoryName = "";
        if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
            $vCategoryName = $_POST['vTitle_' . $db_master[$i]['vCode']];
        }
        $vCategoryNameArr["vTitle_" . $db_master[$i]['vCode']] = $vCategoryName;
    }


    $jsonCategoryName = getJsonFromAnArr($vCategoryNameArr);

    $query_p['vTitle'] = $jsonCategoryName;
    $query_p['iDisplayOrder'] = $iDisplayOrder;
    $query_p['eStatus'] = $eStatus;
    
    if ($id != '') {
       
        $where = "iServiceMenuId  = '$id'";
        $obj->MySQLQueryPerform($tbl_name, $query_p, 'update', $where);
    } else {

        $obj->MySQLQueryPerform($tbl_name, $query_p, 'insert');
    }
    // $obj->sql_query($query);

    if ($id != '') {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }

    header("Location:master_service_menu.php");
    exit();
}

// for Edit
$userEditDataArr = array();
$vDescriptionArr = array();
if ($action == 'Edit') {

    
    $sql_1 = "SELECT *,vTitle as vTitle_json FROM `master_service_menu` WHERE iServiceMenuId  = " . $id;
    $master_service_menu = $obj->MySQLSelect($sql_1);
   
    $vCategoryName = json_decode($master_service_menu[0]['vTitle_json'], true);
    foreach ($vCategoryName as $key => $value) {
        $userEditDataArr[$key] = $value;
    }
}



$bidding = $BIDDING_OBJ->getBiddingMaster('admin');

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$maxDisplayOrderData = $obj->MySQLSelect("SELECT max(iDisplayOrder) as maxDisplayOrder FROM $tbl_name WHERE iParentId = '$parentid' AND eStatus != 'Deleted'");
$maxDisplayOrder = $maxDisplayOrderData[0]['maxDisplayOrder'];
if ($action == 'Add') {
    $maxDisplayOrder = $maxDisplayOrder + 1;
}

?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->

<head>
    <meta charset="UTF-8" />
    <title>Admin | Service Menu <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->

<body class="padTop53 ">
    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <?php include_once('header.php'); ?>
        <?php include_once('left_menu.php'); ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?= $action; ?> Service Menu</h2>
                        <a href="master_service_menu.php">
                            <input type="button" value="Back to Listing" class="add-btn">
                        </a>
                    </div>
                </div>
                <hr />
                <div class="body-div">
                    <div class="form-group">
                        <?php if ($success == 0 && $_REQUEST['var_msg'] != "") { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $_REQUEST['var_msg']; ?>
                            </div>
                            <br />
                        <?php } ?>
                        <?php if ($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div>
                            <br />
                        <?php } ?>
                        <?php if ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                            <br />
                        <?php } ?>
                        <form method="post" action="" enctype="multipart/form-data" id="bid_category_form">
                            <input type="hidden" name="id" value="<?= $id; ?>" />
                            <input type="hidden" name="parentid" value="<?= $parentid; ?>" />

                            <?php if ($parentid > 0) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Parent category</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <select name="iParentId" class="form-control">
                                            <option value="">--Select--</option>
                                            <?php for ($i = 0; $i <= scount($bidding) - 1; $i++) {
                                                if ($bidding[$i]['iBiddingId'] == $BIDDING_OBJ->other_id) {
                                                    continue;
                                                }
                                            ?>


                                                <option value="<?= $bidding[$i]['iBiddingId'] ?>" <?= $bidding[$i]['iBiddingId'] == $parentid ? "selected" : "" ?>>
                                                    <?= $bidding[$i]['vTitle'] ?></option>
                                            <?php  } ?>
                                        </select>
                                        <input type="hidden" name="oldDisplayOrder" id="oldDisplayOrder" value="<?= $iDisplayOrder ?>">
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vTitle_Default" name="vTitle_Default" value="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>" readonly="readonly" required <?php if ($id == "") { ?> onclick="editCategoryName('Add')" <?php } ?>>
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCategoryName('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="modal fade" id="Category_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Title
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTitle_')">x</button>
                                                </h4>
                                            </div>

                                            <div class="modal-body">
                                                <?php

                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vTitle_' . $vCode;
                                                    $$vValue = $userEditDataArr[$vValue];
                                                    $iDisplayOrder = $master_service_menu[0]['iDisplayOrder'];
                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                    <?php
                                                    $page_title_class = 'col-lg-12';
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") {
                                                                $page_title_class = 'col-md-9 col-sm-9';
                                                            }
                                                        } else {
                                                            if ($vCode == $default_lang) {
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
                                                            <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        if (scount($db_master) > 1) {
                                                            if ($EN_available) {
                                                                if ($vCode == "EN") { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', 'EN');">Convert To All
                                                                            Language</button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', '<?= $default_lang ?>');">Convert
                                                                            To All Language</button>
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
                                                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                    <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                    </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                                </h5>
                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveCategoryName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>

                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vTitle_<?= $default_lang ?>" name="vTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>


                            

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Display Order</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <select name="iDisplayOrder" class="form-control">
                                        <?php for ($i = 1; $i <= $maxDisplayOrder; $i++) { ?>
                                            <option value="<?= $i ?>" <?= $iDisplayOrder == $i ? "selected" : "" ?>>
                                                <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="hidden" name="oldDisplayOrder" id="oldDisplayOrder" value="<?= $iDisplayOrder ?>">
				    <!-- <input type="checkbox" name="eStatus_" id="eStatus_" value="Inactive"> -->
                                </div>
                            </div>


                            


                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
						<!-- <input type="checkbox"  id="eStatus"   name="eStatus"  value="Inactive" />  -->
                                            <input type="checkbox" id="eStatus"   name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> value="Active" />
                                        </div>
                                    </div>
                                </div>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <input type="submit" class="save btn-info" name="submit" id="submit" value="<?= $action . ' ' . ($parentid == 0 ? ' Service Menu' : 'Service'); ?>" style="margin-right: 10px">
                                    <a href="master_service_menu.php" class="btn btn-default back_link">Cancel</a>
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
    <script type="text/javascript">
        function editCategoryName(action) {
            $('#modal_action').html(action);
            $('#Category_Modal').modal('show');
        }

        function saveCategoryName() {
            if ($('#vTitle_<?= $default_lang ?>').val() == "") {
                $('#vTitle_<?= $default_lang ?>_error').show();
                $('#vTitle_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function() {
                    $('#vTitle_<?= $default_lang ?>_error').hide();
                }, 5000);
                return false;
            }

            $('#vTitle_Default').val($('#vTitle_<?= $default_lang ?>').val());
            $('#vTitle_Default').closest('.row').removeClass('has-error');
            $('#vTitle_Default-error').remove();
            $('#Category_Modal').modal('hide');
        }

        function editDescription(action) {
            $('#tDescriptionmodal_action').html(action);
            $('#tDescription_Modal').modal('show');
        }

        function saveDescription() {
            if ($('#tDescription_<?= $default_lang ?>').val() == "") {
                $('#tDescription_<?= $default_lang ?>_error').show();
                $('#tDescription_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function() {
                    $('#tDescription_<?= $default_lang ?>_error').hide();
                }, 5000);
                return false;
            }

            $('#tDescription_Default').val($('#tDescription_<?= $default_lang ?>').val());
            $('#tDescription_Default').closest('.row').removeClass('has-error');
            $('#tDescription_Default-error').remove();
            $('#tDescription_Modal').modal('hide');
        }

        $('#iListMaxCount').keyup(function(e) {
            if (/\D/g.test(this.value)) {
                this.value = this.value.replace(/\D/g, '');
            }
        });

        $(document).ready(function () {
            $('#bid_category_form').validate({
                rules: {
                    fCommission: {
                        required: true,
                        number: true
                    }
                },
            });
        });
    </script>
</body>
<!-- END BODY-->

</html>