<?php
include_once('../common.php');
require_once(TPATH_CLASS."Imagecrop.class.php");

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id 		= $_REQUEST['id'] ?? '';
$success	= $_REQUEST['success'] ?? 0;
$action 	= ($id != '')?'Edit':'Add';

$tbl_name 		= 'travel_preferences_category';
$script 		= 'travel_preferences_category';

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);

$eStatus_check 	= isset($_POST['eStatus'])?$_POST['eStatus']:'off';
$eStatus 		= ($eStatus_check == 'on')?'Active':'Inactive';

$thumb = new thumbnail();


if(isset($_POST['submit'])) { //form submit

    if($action == "Add" && !$userObj->hasPermission('create-travel-preferences-category')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create TravelPreferencesCategory Categories.';
        header("Location:travel_preferences_category.php");
        exit;
    }
    if($action == "Edit" && !$userObj->hasPermission('edit-travel-preferences-category')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update TravelPreferencesCategory Categories.';
        header("Location:travel_preferences_category.php");
        exit;
    }
    if(SITE_TYPE=='Demo')
    {
        header("Location:travel_preferences_category_action.php?id=".$id.'&success=2');
        exit;
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        $vTitle = "";
        if (isset($_POST['vTitle_' . $db_master[$i]['vCode']])) {
            $vTitle = $_POST['vTitle_' . $db_master[$i]['vCode']];
        }
        $vTitleArr["vTitle_" . $db_master[$i]['vCode']] = $vTitle;
    }
    $jsonTitle = getJsonFromAnArr($vTitleArr);

    $query_p = [];
    $query_p['vTitle'] = $jsonTitle;
    $query_p['eUserType'] = 'Passenger';
    $query_p['eStatus'] = $eStatus;


    if ($id != '') {
        $where = " iTravelPreferencesCategoryId = '$id'";
        $data = $obj->MySQLQueryPerform($tbl_name, $query_p, 'update', $where);
    } else {
        $select_order	= $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM ".$tbl_name." WHERE eStatus != 'Deleted'");
        $iDisplayOrder	= $select_order[0]['iDisplayOrder'] ?? 0;
        $iDisplayOrder	= $iDisplayOrder + 1; // Maximum order number
        $iDisplayOrder	= $_POST['iDisplayOrder'] ?? $iDisplayOrder;
        $query_p['iDisplayOrder'] = $iDisplayOrder;


        $obj->MySQLQueryPerform($tbl_name, $query_p, 'insert');
    }

    //header("Location:travel_preferences_category_action.php?id=".$iUniqueId.'&success=1');
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    header("Location:travel_preferences_category.php?id=".$iUniqueId.'&success=1');
    exit;
}

// for Edit
if($action == 'Edit') {
    $sql = "SELECT * FROM ".$tbl_name." WHERE iTravelPreferencesCategoryId = '".$id."'";
    $db_data = $obj->MySQLSelect($sql);
    $db_data = $db_data[0];

    $iTravelPreferencesCategoryId = $db_data['iTravelPreferencesCategoryId'];
    $vTitle = $db_data['vTitle'];
    $Title = json_decode($vTitle,true);
    $iDisplayOrder_db = $db_data['iDisplayOrder'];
    $eStatus = $db_data['eStatus'];
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
    <title>Admin | Travel Preferences Category <?=$action;?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

    <?php include_once('global_files.php');?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
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
                    <h2><?=$action;?> Travel Preferences Category</h2>
                    <a href="travel_preferences_category.php" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr />
            <div class="body-div">
                <div class="form-group">
                    <?php if($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div><br/>
                    <?php } ?>
                    <?php if ($success == 2) {?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <?php } ?>
                    <form method="post" name="_TravelPreferencesCategory_cat_form" id="_TravelPreferencesCategory_cat_form"  action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?=$id;?>"/>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="travel_preferences_category.php"/>

                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Travel Preferences Category <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vTitle_Default" name="vTitle_Default" value="<?= $Title['vTitle_'.$default_lang]; ?>" data-originalvalue="<?= $Title['vTitle_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editTravelPreferencesCategoryCategory('Add')" <?php } ?>>
                                </div>
                                <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editTravelPreferencesCategoryCategory('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                <?php } ?>
                            </div>

                            <div  class="modal fade" id="TravelPreferencesCategory_cat_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg" >
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Travel Preferences Category
                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTitle_')">x</button>
                                            </h4>
                                        </div>

                                        <div class="modal-body">
                                            <?php

                                            for ($i = 0; $i < $count_all; $i++)
                                            {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vTitle_' . $vCode;

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
                                                        <label>Travel Preferences Category (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $Title[$vValue]; ?>" data-originalvalue="<?= $Title[$vValue]; ?>" placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if($EN_available) {
                                                            if($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', 'EN');" >Convert To All Language</button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTitle_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveTravelPreferencesCategoryCategory()"><?= $langage_lbl['LBL_Save']; ?></button>
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
                                    <label>Travel Preferences Category <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="vTitle_<?= $default_lang ?>" name="vTitle_<?= $default_lang ?>" value="<?= $arrLang['vTitle_'.$default_lang]; ?>" required>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($userObj->hasPermission('update-status-travel-preferences-category')) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?>/>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row TravelPreferencesCategory-but">
                            <div class="col-lg-12">
                                <?php if(($action == 'Edit' && $userObj->hasPermission('edit-travel-preferences-category')) || ($action == 'Add' &&  $userObj->hasPermission('create-travel-preferences-category'))){ ?>
                                    <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?=$action;?> Travel Preferences Category">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="travel_preferences_category.php" class="btn btn-default back_link">Cancel</a>
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

<?php include_once('footer.php');?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    $(document).ready(function() {
        var referrer;
        if($("#previousLink").val() == "" ){
            referrer =  document.referrer;
            // alert(referrer);
        }else {
            referrer = $("#previousLink").val();
        }
        if(referrer == "") {
            referrer = "travel_preferences_category.php";
        }else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href',referrer);
    });

    function editTravelPreferencesCategoryCategory(action)
    {
        $('#modal_action').html(action);
        $('#TravelPreferencesCategory_cat_Modal').modal('show');
    }

    function saveTravelPreferencesCategoryCategory()
    {
        if($('#vTitle_<?= $default_lang ?>').val() == "") {
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
        $('#TravelPreferencesCategory_cat_Modal').modal('hide');
    }

</script>

</body>
<!-- END BODY-->
</html>