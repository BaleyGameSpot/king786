<?php
include_once('../common.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$tbl_name = 'package_type';
$script = 'Package';


$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$vTitle_store = array();
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vName_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}

if ($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
        $sql = "select iDeliveryFieldId,vFieldName from delivery_fields where eStatus = 'Active' AND eInputType='Select'";
        $db_delivery_fields_data = $obj->MySQLSelect($sql);
}        

if (isset($_POST['submit'])) {
    if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && !empty($db_delivery_fields_data)) {
    $iDeliveryFieldId = isset($_POST['iDeliveryFieldId']) ? $_POST['iDeliveryFieldId'] : 0;
    }else{
        $iDeliveryFieldId = 0;
    }

    if ($action == "Add" && !$userObj->hasPermission('create-package-type-parcel-delivery')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create package type.';
        header("Location:state.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-package-type-parcel-delivery')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update package type.';
        header("Location:state.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:package_type_action.php?id=" . $id . '&success=2');
        exit;
    }

    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    $image_update = "";
    if ($image_name != "") {
        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;

        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
        //     $flag_error = 1;
        //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
        // }

        if ($error) {
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $error;
            header("Location:package_type.php");
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_images_parcel_delivery_items_path"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            // $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
            $vImage = $img[0];
            $image_update = "`vImage` = '" . $vImage . "',";
        }
    }

    for ($i = 0; $i < scount($vTitle_store); $i++) {
        $vValue = 'vName_' . $db_master[$i]['vCode'];

        $q = "INSERT INTO ";
        $where = '';

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iPackageTypeId` = '" . $id . "'";
        }

    
        $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $_POST['vName_' . $default_lang] . "',
			`eStatus` = '" . $eStatus . "',
            `iDeliveryFieldId` = '" . $iDeliveryFieldId . "',"
            . $image_update
            . $vValue . " = '" . $_POST[$vTitle_store[$i]] . "'"
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

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iPackageTypeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vName_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vName = $value['vName'];
                $eStatus = $value['eStatus'];
                $iDeliveryFieldId = $value['iDeliveryFieldId'];
                $vImage = $value['vImage'];

                $arrLang[$vValue] = $$vValue;
            }
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
        <title>Admin | Package <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

        <?php include_once('global_files.php'); ?>
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
                            <h2><?= $action; ?> Package Type</h2>
                            <a href="package_type.php" class="back_link">
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
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" name="_package_type" id="_package_type" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="package_type.php"/>
                                <div class="col-lg-12" id="errorMessage"></div>
                                
                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Package Type <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vName_Default" name="vName_Default" value="<?= $arrLang['vName_'.$default_lang]; ?>" data-originalvalue="<?= $arrLang['vName_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editPackageType('Add')" <?php } ?>>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editPackageType('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="package_type_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Package Type 
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vName_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vName_' . $vCode;
                                                        
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
                                                                <label>Package Type (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
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
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vName_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vName_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="savePackageType()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vName_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>

                                </div>
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Package Type <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vName_<?= $default_lang ?>" name="vName_<?= $default_lang ?>" value="<?= $arrLang['vName_'.$default_lang]; ?>" required>
                                    </div>
                                </div>
                                <?php } ?>
                                
                                <?php if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && !empty($db_delivery_fields_data)) {?>
                                <div class="row" style="display: none;">
                                    <div class="col-lg-12">
                                        <label>Delivery Field (Only for Multi Delivery)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                            <select class="form-control" name = 'iDeliveryFieldId' id="iDeliveryFieldId">
                                            <option value="">Select Delivery Field</option>
                                            <?php for ($i = 0; $i < scount($db_delivery_fields_data); $i++) { ?>
                                                <option <?php if($action == 'Edit' && ($db_delivery_fields_data[$i]['iDeliveryFieldId']==$iDeliveryFieldId) ){ echo "selected";}?> value = "<?= $db_delivery_fields_data[$i]['iDeliveryFieldId'] ?>"><?= $db_delivery_fields_data[$i]['vFieldName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image<?= ($vImage == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($vImage != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=100&src=' . $tconfig['tsite_upload_images_parcel_delivery_items'] . '/' . $vImage; ?>">
                                            <input type="file" class="form-control" name="vImage" id="vImage"
                                                   value="<?= $vImage; ?>"/>
                                        <?php } else { ?>
                                            <input type="file" class="form-control" name="vImage" id="vImage" required/>
                                        <?php } ?>
                                        <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                                        <b>[Note: Recommended dimension is 512px X 512px.]</b>
                                    </div>
                                </div>

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

                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-package-type-parcel-delivery')) || ($action == 'Add' && $userObj->hasPermission('create-package-type-parcel-delivery'))) { ?>
                                            <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Package">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <!-- <a href="javascript:void(0);" onclick="reset_form('_make_form');" class="btn btn-default">Reset</a> -->
                                        <a href="package_type.php" class="btn btn-default back_link">Cancel</a>
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
    </body>
    <!-- END BODY-->
</html>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") { //alert('pre1');
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }

        if (referrer == "") {
            referrer = "package_type.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });

 <?php if (($APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && !empty($db_delivery_fields_data)) {?>
    $('#_make_form').validate({
        rules: {
            /*iDeliveryFieldId: {
                required: true
            },*/
        }
    });
<?php }?>

function editPackageType(action)
{
    $('#modal_action').html(action);
    $('#package_type_Modal').modal('show');
}

function savePackageType()
{
    if($('#vName_<?= $default_lang ?>').val() == "") {
        $('#vName_<?= $default_lang ?>_error').show();
        $('#vName_<?= $default_lang ?>').focus();
        clearInterval(langVar);
        langVar = setTimeout(function() {
            $('#vName_<?= $default_lang ?>_error').hide();
        }, 5000);
        return false;
    }

    $('#vName_Default').val($('#vName_<?= $default_lang ?>').val());
    $('#vName_Default').closest('.row').removeClass('has-error');
    $('#vName_Default-error').remove();
    $('#package_type_Modal').modal('hide');
}
</script>