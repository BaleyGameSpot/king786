<?php
include_once('../common.php');

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = $_REQUEST['id'] ?? '';
$success = $_REQUEST['success'] ?? 0;
$cat_id = $_REQUEST['cat_id'] ?? '';
$action = ($id != '') ? 'Edit' : 'Add';

if ($action == "Add" && !$userObj->hasPermission('create-travel-preferences')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to create Travel Preferences.';
    header("Location:travel_preferences.php");
    exit;
}

if ($action == "Edit" && !$userObj->hasPermission('edit-travel-preferences')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to update Travel Preferences.';
    header("Location:travel_preferences.php");
    exit;
}

$tbl_name = 'travel_preferences';
$script = 'Travel_Preferences';

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);

$eStatus_check = $_POST['eStatus'] ?? 'off';
$backlink = $_POST['backlink'] ?? '';
$previousLink = $_POST['backlink'] ?? '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$thumb = new thumbnail();

$select_order = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = $select_order[0]['iDisplayOrder'] ?? 0;
$iDisplayOrder_max = $iDisplayOrder + 1;

$icategoryId = $_POST['iCategoryId'] ?? $cat_id;
$iDisplayOrder = $_POST['iDisplayOrder'] ?? $iDisplayOrder;
$temp_order = $_POST['temp_order'] ?? "";

if (isset($_POST['submit'])) {

    if (SITE_TYPE == 'Demo') {
        header("Location:travel_preferences_action.php.php?id=" . $id . "&cat_id=" . $cat_id . "&success=2");
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

    $_REQUEST['test1'] =1;
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
    $query_p['eStatus'] = 'Active';
    $query_p['iDisplayOrder'] = $iDisplayOrder;
    $query_p['iTravelPreferencesCategoryId'] = $icategoryId;

    if ($id != '') {
        $where = " TravelPreferencesId = '$id'";
        $data = $obj->MySQLQueryPerform($tbl_name, $query_p, 'update', $where);
    } else {
        $obj->MySQLQueryPerform($tbl_name, $query_p, 'insert');
    }

    $id = ($id != '') ? $id : $obj->GetInsertId();

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    header("location:" . $backlink);
    exit;
}

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE TravelPreferencesId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $db_data = $db_data[0];

    $TravelPreferencesId = $db_data['TravelPreferencesId'];
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
    <title>Admin | Travel Preferences  <?= $action; ?></title>
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
        .readonly-select-custom{
            pointer-events: none;
            background-color: #f4f4f4;
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
                    <h2><?= $action; ?> Travel Preferences </h2>
                    <a href="travel_preferences.php" class="back_link">
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

                    <form method="post" name="_travel_preferences_form" id="_travel_preferences_form" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="travel_preferences.php"/>
                        <?php
                        $sql = "SELECT *,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vCategory FROM travel_preferences_category WHERE eStatus = 'Active' ORDER BY  vTitle ASC ";
                        $db_cat = $obj->MySQLSelect($sql);

                        if (scount($db_cat) > 0) {
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Category</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select name="iCategoryId" id="iCategoryId" class="form-control readonly-select-custom">
                                        <?php for ($i = 0; $i < scount($db_cat); $i++) { ?>
                                            <option value="<?= $db_cat[$i]['iTravelPreferencesCategoryId']; ?>" <?= ($db_cat[$i]['iTravelPreferencesCategoryId'] == $iTravelPreferencesCategoryId) ? 'selected' : ''; ?>>
                                                <?= $db_cat[$i]['vCategory'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Travel Preferences Option <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vTitle_Default" name="vTitle_Default" value="<?= $Title['vTitle_'.$default_lang]; ?>" data-originalvalue="<?= $Title['vTitle_'.$default_lang]; ?>" readonly="readonly" required <?php if($id == "") { ?> onclick="editTravelPreferences('Add')" <?php } ?>>
                                    </div>
                                    <?php if($id != "") { ?>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editTravelPreferences('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div  class="modal fade" id="Travel_Preferences_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Travel Preferences - Option
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
                                                            <label>Option (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>

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
                                                            <input type="text" class="form-control" name="<?= $vTitle; ?>" id="<?= $vTitle; ?>" value="<?= $Title[$vTitle]; ?>" data-originalvalue="<?= $Title[$vTitle]; ?>" placeholder="<?= $vLTitle; ?> Value">
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveTravelPreferences()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTitle_')">Cancel</button>
                                                </div>
                                            </div>

                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Category <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vTitle_<?= $default_lang ?>" name="vTitle_<?= $default_lang ?>" value="<?= $db_data[0]['vTitle_'.$default_lang]; ?>" required>
                                    </div>
                                </div>

                            <?php } ?>
                            <?php if ($userObj->hasPermission('update-status-travel-preferences')) { ?>
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
                            <?php } ?>
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
                                            if ($i == $iDisplayOrder_db) {
                                                echo "selected";
                                            }
                                            ?>> -- <?= $i ?> --</option>
                                        <?php } ?>
                                    </select>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php if (($action == 'Edit' && $userObj->hasPermission('edit-travel-preferences')) || ($action == 'Add' && $userObj->hasPermission('create-travel-preferences'))) { ?>
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Travel Preferences">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                    <?php } ?>
                                    <!-- <a href="javascript:void(0);" onclick="reset_form('_travel_preferences_form');" class="btn btn-default">Reset</a> -->
                                    <a href="travel_preferences.php" class="btn btn-default back_link">Cancel</a>
                                </div>
                            </div>
                        <?php } else { ?>
                            Please enter Travel Preferences Catgory
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
            referrer = "travel_preferences.php";
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

    function editTravelPreferences(action)
    {
        $('#modal_action').html(action);
        $('#Travel_Preferences_Modal').modal('show');
    }

    function saveTravelPreferences()
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

        $('#Travel_Preferences_Modal').modal('hide');
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
    }
</script>
</html>