<?php 
include_once('../common.php');


require_once(TPATH_CLASS."Imagecrop.class.php");
$thumb = new thumbnail();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$tbl_name = 'genie_package_types';

$eBuyAnyService = isset($_REQUEST['eBuyAnyService']) ? $_REQUEST['eBuyAnyService'] : '';

if(empty($eBuyAnyService) || !in_array($eBuyAnyService, ['Genie', 'Anywhere', 'Runner'])) {
    $userObj->redirect();
}

$vehicle_category = $obj->MySQLSelect("SELECT vCategory_$default_lang as vCategory FROM vehicle_category WHERE eCatType = '$eBuyAnyService'");
$ServiceName = $vehicle_category[0]['vCategory'];

// set all variables with either post (when submit) either blank (when insert)
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : "";
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);
 

if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-genie-package-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create package type.';
        header("Location:genie_package_type.php?eBuyAnyService=" . $eBuyAnyService);
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-genie-package-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update package type.';
        header("Location:genie_package_type.php?eBuyAnyService=" . $eBuyAnyService);
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:genie_package_type_action.php?id=" . $id . '&success=2&eBuyAnyService=' . $eBuyAnyService);
        exit;
    }

    if($temp_order > $iDisplayOrder) {
        for($i = $temp_order; $i >= $iDisplayOrder; $i--) { 
            $obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i+1)." WHERE iDisplayOrder = ".$i);
        }
    } else if($temp_order < $iDisplayOrder) {
        for($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i-1)." WHERE iDisplayOrder = ".$i);
        }
    }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iPackageTypeId` = '" . $id . "'";
    }

    $image_object = $_FILES['vImage']['tmp_name'];  
    $image_name   = $_FILES['vImage']['name'];
    $image_update = "";

    if($image_name != ""){
        $filecheck = basename($_FILES['vImage']['name']);                            
        $fileextarr = explode(".",$filecheck);
        $ext=strtolower($fileextarr[scount($fileextarr)-1]);
        $flag_error = 0;
        if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
        }
        $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
        $image_width = $image_info[0];
        $image_height = $image_info[1];

        if($flag_error == 1) {
            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $var_msg;
            header("Location:genie_package_type.php?eBuyAnyService=".$eBuyAnyService);
            exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_genie_package_type_images_path"].'/';
            if(!is_dir($Photo_Gallery_folder)){
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }  
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg');
            $vImage = $img[0];

            $image_update = "`vImage` = '".$vImage."',";
        }
    }

    for ($i = 0; $i < scount($db_master); $i++) {
        $tTitle = "";
        if (isset($_POST['tTitle_' . $db_master[$i]['vCode']])) {
            $tTitle = $_POST['tTitle_' . $db_master[$i]['vCode']];
        }

        $tTitleArr["tTitle_" . $db_master[$i]['vCode']] = $tTitle;
    }
    

    $jsonTitle = getJsonFromAnArr($tTitleArr);

    $query = $q . " `" . $tbl_name . "` SET
        `tTitle` = '" . $jsonTitle . "',
        $image_update
        `eFor` = '" . $eBuyAnyService . "',
        `eStatus` = '" . $eStatus . "',
        `iDisplayOrder` = '".$iDisplayOrder."'"
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
    header("location:" . $backlink);
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iPackageTypeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (scount($db_data) > 0) {
         $tTitle = json_decode($db_data[0]['tTitle'], true);
        foreach ($tTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        
        $eStatus = $db_data[0]['eStatus'];
        $vImage = $db_data[0]['vImage'];
        $iDisplayOrder = $db_data[0]['iDisplayOrder'];
    }
}

$data_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM $tbl_name WHERE eFor = '$eBuyAnyService' ORDER BY iDisplayOrder");

$temp = 1;
foreach($data_order as $value)
{
    $dataArray[] = $value['iDisplayOrder'];
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

        <? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Package Type (<?= $ServiceName ?>)</h2>
                            <a href="genie_package_type.php?eBuyAnyService=<?= $eBuyAnyService ?>" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>
                            <form method="post" name="_package_type" id="_package_type" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="genie_package_type.php?eBuyAnyService=<?= $eBuyAnyService ?>"/>
                                <div class="col-lg-12" id="errorMessage"></div>

                                <input type="hidden" name="vImage_old" value="<?=$vImage?>">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image<?=($vImage == '')?'<span class="red"> *</span>':'';?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if($vImage != '') { ?>
                                        <img src="<?=$tconfig["tsite_url"].'resizeImg.php?w=100&src='.$tconfig['tsite_upload_genie_package_type_images'].$vImage;?>" style="margin-bottom: 10px;">
                                        <input type="file" class="form-control" name="vImage" id="vImage" value="<?=$vImage;?>" style="margin-bottom: 10px;"/>
                                        <? } else { ?>
                                        <input type="file" class="form-control" name="vImage" id="vImage" value="<?=$vImage;?>" required/>
                                        <? } ?>
                                        <b>[Note: Recommended dimension is 500 * 500.]</b>
                                    </div>
                                </div>

                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Package Type <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="tTitle_Default" name="tTitle_Default" value="<?= $userEditDataArr['tTitle_'.$default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['tTitle_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editPackageType('Add')" <?php } ?>>
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
                                                        $$vValue = $userEditDataArr['tTitle_' . $vCode];
                                                        
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
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tTitle_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tTitle_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="tTitle_<?= $default_lang ?>" name="tTitle_<?= $default_lang ?>" value="<?= $arrLang['tTitle_'.$default_lang]; ?>" required>
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

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Order</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?=$temp?>">
                                        <select name="iDisplayOrder" id="iDisplayOrder" class="form-control">
                                            
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-genie-package-type')) || ($action == 'Add' && $userObj->hasPermission('create-genie-package-type'))) { ?>
                                            <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Package">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>

                                        <a href="genie_package_type.php?eBuyAnyService=<?= $eBuyAnyService ?>" class="btn btn-default back_link">Cancel</a>
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

        <? include_once('footer.php'); ?>
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
        referrer = "genie_package_type.php?eBuyAnyService=<?= $eBuyAnyService ?>";
    } else {
        $("#backlink").val(referrer);
    }
    $(".back_link").attr('href', referrer);

    getUserTypeDisplayOrder();
});

function editPackageType(action)
{
    $('#modal_action').html(action);
    $('#package_type_Modal').modal('show');
}

function savePackageType()
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
    $('#package_type_Modal').modal('hide');
}

function getUserTypeDisplayOrder() {
    var iDisplayOrder = '<?= $iDisplayOrder ?>';
    var page_action = '<?= $action ?>';

    var displayOrderArr = '<?= json_encode($dataArray) ?>';
    displayOrderArr = JSON.parse(displayOrderArr);

    var DisplayOrderArr = displayOrderArr;
    
    var select_html = "";
    
    for(var i=0; i < DisplayOrderArr.length; i++) {
        var selected = "";
        if(DisplayOrderArr[i] == iDisplayOrder) {
            selected = "selected";
        }
        select_html += '<option value="' + DisplayOrderArr[i] + '" ' + selected + '>-- ' + DisplayOrderArr[i] + ' --</option>';    
    }

    var last = iDisplayOrder;
    if(page_action == "Add") {
        var last = 0;
        if(DisplayOrderArr.length > 0) {
            var last = DisplayOrderArr[DisplayOrderArr.length - 1];    
        }
        
        last = parseInt(last) + 1;
        select_html += '<option value="' + last + '" ' + selected + '>-- ' + last + ' --</option>';       
    }
    // console.log(select_html);
    $('#iDisplayOrder').html(select_html);
    $('#temp_order').val(last);
}
</script>