<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");

$script = '';
$view = "view-vehicle-size-info";
$edit = "edit-vehicle-size-info";
if (!$userObj->hasPermission($edit)) {
    $userObj->redirect();
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$page_action = $action = ($id != '') ? 'Edit' : 'Add';

if ($action == "Add") {
        header("Location:vehicle_size_info.php");
        exit;
}

$tbl_name = 'vehicle_size_info';

$vSizeName = isset($_POST['vSizeName']) ? $_POST['vSizeName'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

//  for ordering
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : 'Inactive';

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
		$db_vSizeName = 'vSizeName_' . $db_master[$i]['vCode'];
        $vSizeNameArr[$db_vSizeName] = isset($_POST[$db_vSizeName]) ? $_POST[$db_vSizeName] : '';
    }
}
if (isset($_POST['btnsubmit'])) {
    if ($action == "Edit" && !$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update vehicle size info.';
        header("Location:vehicle_size_info.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:vehicle_size_info_action.php?id=" . $id . "&success=2");
        exit;
    }

	if ($temp_order != $iDisplayOrder) {
        if ($temp_order < $iDisplayOrder) {

                $sql_update = "UPDATE vehicle_size_info as vs SET vs.iDisplayOrder = vs.iDisplayOrder - 1 WHERE vs.iDisplayOrder > $temp_order AND vs.iDisplayOrder <= $iDisplayOrder ";

            } else {
                $sql_update = "UPDATE vehicle_size_info as vs SET vs.iDisplayOrder = vs.iDisplayOrder + 1 WHERE vs.iDisplayOrder >= $iDisplayOrder AND vs.iDisplayOrder < $temp_order ";
            }
		// echo $sql_update; die;
            $obj->sql_query($sql_update);
    }

    $update = array();
    $where = " iVehicleSizeId = '" . $id . "'";

    $update['vSizeName'] = getJsonFromAnArr($vSizeNameArr);
    $update['iDisplayOrder'] = $iDisplayOrder;
    $update['eStatus'] = $eStatus;

    $obj->MySQLQueryPerform($tbl_name, $update, 'update', $where);
 

    $id = ($id != '') ? $id : $insert_id;

    //}
    // exit;

    // $obj->sql_query($query);

    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    $_SESSION['success'] = "1";

    // header("Location:" . $backlink);
    header("Location:vehicle_size_info.php");
    exit;
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleSizeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        if (isset($db_data[0]['vSizeName'])) {
            $vSizeName = (array)json_decode($db_data[0]['vSizeName']);
        }
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $iDisplayOrder_db = $value['iDisplayOrder'];
                $eStatus = $value['eStatus'];
				$vSizeName = (!empty($value['vSizeName'])) ? json_decode($value['vSizeName'], true) : "";
				$userEditDataArr['vSizeName_' . $db_master[$i]['vCode']] = $vSizeName['vSizeName_' . $db_master[$i]['vCode']];
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
    <meta charset="UTF-8"/>
    <title>Admin | <?= $action; ?> <?php echo $langage_lbl_admin['LBL_PARKING_VEHICLE_SIZE_TXT']; ?> Info</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Css-->
    <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap-clockpicker.min.css">
    <!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Css-->
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
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
                    <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_PARKING_VEHICLE_SIZE_TXT']; ?> Info </h2>
                    <!-- <a href="vehicle_type.php">
                                        <input type="button" value="Back to Listing" class="add-btn">
                                        </a> -->
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                    <?php } else if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $_REQUEST['varmsg']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if ($_REQUEST['var_msg'] != Null) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Record Not Updated .
                        </div>
                        <br/>
                    <?php } ?>
                    <div id="price1"></div>
                    <form id="_vehicleType_form" name="_vehicleType_form" method="post" action=""
                          enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="vehicle_size_info.php"/>
                        <div class="row">
                            <div class="col-lg-12" id="errorMessage">
                            </div>
                        </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Vehicle Size Name</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                                  name="vVehicleSizeName_Default" id="vVehicleSizeName_Default" readonly="readonly"
                                                  data-originalvalue="<?= $vSizeName['vSizeName_' . $default_lang]; ?>" value="<?= $vSizeName['vSizeName_' . $default_lang]; ?>">
                                    </div>
                                    <?php if ($id != "") { ?>
                                        <div class="col-lg-1">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit"
                                                    onclick="editVehicleSizeName('Edit', 'vSizeName_Modal')"><span
                                                        class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>

                            <div class="modal fade" id="vSizeName_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> <label>Vehicle Size Name</label>
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vSizeName_')">x
                                                </button>
                                            </h4>
                                        </div>

                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vLTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vSizeName = 'vSizeName_' . $vCode;
                                                $$vSizeName = $userEditDataArr[$vSizeName];
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
                                                        <label>Vehicle Size Name (<?= $vLTitle ?>)</label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?> desc-block">
                                                        <input type="text" class="form-control" name="<?= $vSizeName; ?>"
                                                                  id="<?= $vSizeName; ?>"
                                                                  data-originalvalue="<?= $$vSizeName; ?>"
                                                                  placeholder="<?= $vLTitle; ?> Value" value="<?= $$vSizeName; ?>"> 
                                                        <div class="text-danger" id="<?= $vSizeName . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                        <div class="text-danger" id="<?= $vSizeName . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vSizeName_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vSizeName_', '<?= $default_lang ?>');">
                                                                        Convert To All Language
                                                                    </button>
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
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>
                                                    : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveVehicleSizeName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'tInfoText_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>

                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Display Order</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="hidden" name="temp_order" id="temp_order"
                                           value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">
                                    <?php
                                    $temp = 1;
                                    $select_order = $obj->MySQLSelect("SELECT count(iVehicleSizeId) as maxnumber FROM vehicle_size_info");
                                    $maxnum = isset($select_order[0]['maxnumber']) ? $select_order[0]['maxnumber'] : 0;
                                    $dataArray = array();
                                    for ($i = 1; $i <= $maxnum; $i++) {
                                        $dataArray[] = $i;
                                        $temp = $maxnum + 1;
                                    }
                                    //$display_numbers = ($action == "Add") ? $iDisplayOrder_max : $iDisplayOrder; ?>
                                    <select name="iDisplayOrder" class="form-control" id="change_order">
                                        <?php foreach ($dataArray as $arr): ?>
                                            <option <?= $arr == $iDisplayOrder_db ? ' selected="selected"' : '' ?>
                                                    value="<?= $arr; ?>">
                                                -- <?= $arr ?> --
                                            </option>
                                        <?php endforeach; ?>
                                        <?php if ($action == "Add") { ?>
                                            <option value="<?= $temp; ?>">-- <?= $temp ?> --</option>
                                        <?php } ?>
                                    </select>
                                    <!--  <select name="iDisplayOrder" class="form-control" >
                                                    <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                    <option value="<?= $i ?>" <?php
                                        if ($i == $iDisplayOrder_db) {
                                            echo "selected";
                                        }
                                        ?>> -- <?= $i ?> --</option>
                                                    <?php } ?>
                                                </select> -->
                                </div>
                            </div>
							
							<div class="row">
                                <div class="col-lg-12">
                                    <label>Status</label>
                                </div>
                                <div class="col-lg-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" name="eStatus"
                                               id="eStatus" <?= ($eStatus == 'Inactive') ? '' : 'checked'; ?>
                                               value="1"/>
                                    </div>
                                </div>
                            </div>

                        <br/>
                        <div class="col-lg-12">
                            <?php


                            if (($page_action == 'Edit' && $userObj->hasPermission($edit))) { ?>
                                <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit"
                                       value="Update">


                                <input type="reset" value="Reset" class="btn btn-default">

                            <?php } ?>
                            <!-- <a href="javascript:void(0);" onclick="reset_form('_vehicleType_form');" class="btn btn-default">Reset</a> -->
                            <a href="vehicle_size_info.php" class="btn btn-default back_link">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <div style="clear:both;"></div>
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

<div class="row loding-action" id="loaderIcon1" style="display:none;">
    <div align="center">
        <img src="default.gif">

    </div>
</div>
<?php include_once('footer.php'); ?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen"
      href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<!--Added By Hasmukh On 11-10-2018 For Clock Time Picker Start Js-->
<script type="text/javascript" src="js/bootstrap-clockpicker.min.js"></script>
<!--Added By Hasmukh On 11-10-2018 For Clock Time Picker End Js -->
<!--For Faretype-->
<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
<script src="../assets/plugins/ckeditor/config.js"></script>
<script type="text/javascript" language="javascript">

    var myVar;
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "vehicle_size_info.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
  
</script>
<script type="text/javascript">

    function editVehicleSizeName(action) {
        $('#modal_action').html(action);
        $('#vSizeName_Modal').modal('show');
    }

    function saveVehicleSizeName() {
        if ($('#vSizeName_<?= $default_lang ?>').val().trim() == "") {
            $('#vSizeName_<?= $default_lang ?>_error').show();
            $('#vSizeName_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vSizeName_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vVehicleSizeName_Default').val($('#vSizeName_<?= $default_lang ?>').val());
        $('#vVehicleSizeName_Default').closest('.row').removeClass('has-error');
        $('#vVehicleSizeName_Default-error').remove();
        $('#vSizeName_Modal').modal('hide');
    }

    $(document).on('keyup paste', 'textarea:not([name^=tTypeDesc_], .cke_source)', function (e) {
        var tval = $(this).val(),
            tlength = tval.length,
            set = 100,
            remain = parseInt(set - tlength);
        if (tlength > 0) {
            $(this).closest('.desc-block').find('.desc_counter').text(remain + "/120");
            if (remain <= 0) {
                $(this).val((tval).substring(0, set));
                $(this).closest('.desc-block').find('.desc_counter').text("0/120");
                return false;
            }
        } else {
            $(this).closest('.desc-block').find('.desc_counter').text("120/120");
            return false;
        }
    });
</script>
</body>
<!-- END BODY-->
</html>