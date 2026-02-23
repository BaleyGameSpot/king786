<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");

if (!$userObj->hasPermission('manage-app-home-screen-view')) {
    $userObj->redirect();
}

$isAppHomeScreenV4 = $MODULES_OBJ->isEnableAppHomeScreenLayoutV4();

$thumb = new thumbnail();
$required_rule = "accept='image/*'";
$goback = $iServiceIdEdit = 0;

$id = $vLabel_Org = isset($_REQUEST['vLabel']) ? $_REQUEST['vLabel'] : '';
$service = isset($_REQUEST['eFor']) ? $_REQUEST['eFor'] : '';
if(empty($vLabel_Org) || empty($service)){
    $userObj->redirect();
}
$array = array('taxi_more_services','delivery_more_services','video_more_services','bid_more_services','nearby_more_services','ondemand_more_services','all_services_gc');
if(!in_array($service, $array)){
    $userObj->redirect();
}
$lbl_array = array('LBL_MORE_ONDEMAND_SERVICES','LBL_MORE_SERVICES','LBL_MORE','LBL_ALL_SERVICES_TXT');
if(!in_array($vLabel_Org, $lbl_array)){
    $userObj->redirect();
}
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
$action = ($id != '') ? 'Edit' : 'Add';
$actionSave = ($id != '') ? 'Update' : 'Add';
$script = 'ManageAppHomePage';


$tbl_name = 'language_label';
$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` WHERE eStatus = 'Active'  ORDER BY `iDispOrder`");

$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vValue_' . $db_master[$i]['vCode'];
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
		
    }
}
if ($service == 'taxi_more_services') {
    $img_name = 'ic_more_taxi_services.png';
} elseif ($service == 'delivery_more_services') {
    $img_name = 'ic_more_delivery_services.png';
} elseif ($service == 'video_more_services') {
    $img_name = $isAppHomeScreenV4 && strtoupper($APP_TYPE) == "UBERX" ? 'ic_more_vc_sp.png' : 'ic_more_other_services_vc.png';
} elseif ($service == 'bid_more_services') {
    $img_name = $isAppHomeScreenV4 && strtoupper($APP_TYPE) == "UBERX" ? 'ic_more_bid_sp.png' : 'ic_more_service_bid.png';
} elseif ($service == 'nearby_more_services') {
    $img_name = 'ic_more_nearby.png';
} elseif ($service == 'ondemand_more_services') {
    $img_name = $isAppHomeScreenV4 && strtoupper($APP_TYPE) == "UBERX" ? 'ic_more_sp.png' : 'ic_more_other_services_sp.png';
} elseif ($service == 'all_services_gc') {
    $img_name = 'ic_all_gbx.png';
}

$img_name = getMoreServicesIconName($img_name);

$isAppHomeScreenV4 = $MODULES_OBJ->isEnableAppHomeScreenLayoutV4();

$vCatNameHomepageArr = $vCatTitleHomepageArr = $vCatSloganHomepageArr = $lCatDescHomepage = $vCatDescbtnHomepage = $vServiceCatTitleHomepageArr = array();

if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-general-label')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Language Label.';
        header("Location:languages.php");
        exit;
	}
	if ($action == "Edit" && !$userObj->hasPermission('edit-general-label')) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to update Language Label.';
		header("Location:languages.php");
		exit;
	}
	
	if ($count_all > 0) {
		for ($i = 0; $i < $count_all; $i++) {
			$q = "INSERT INTO ";
			$where = '';
			if ($id != '') {
				$q = "UPDATE ";
				$sql = "SELECT vLabel FROM " . $tbl_name . " WHERE vLabel = '" . $id . "'";
				$db_data = $obj->MySQLSelect($sql);
				$sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $db_data[0]['vLabel'] . "'";
				$db_data = $obj->MySQLSelect($sql);
				$vLabel = $db_data[0]['vLabel'];
				$where = " WHERE `vLabel` = '" . $vLabel . "' AND vCode = '" . $db_master[$i]['vCode'] . "'";
			}
			$vValue = 'vValue_' . $db_master[$i]['vCode'];
			$query = $q . " `" . $tbl_name . "` SET					
				`vValue` = '" . $$vValue . "'" . $where;
			
			$obj->sql_query($query);
		}			
	}
    
	if (isset($_FILES['vListLogo3']) && $_FILES['vListLogo3']['name'] != "") {
        
        $bannerImage = $_FILES['vListLogo3'];
        $img_path = $tconfig["tpanel_path"].'webimages/icons/DefaultImg';
        $temp_gallery = $img_path . '/';
        $image_object = $bannerImage['tmp_name'];
        $image_name = $bannerImage['name'];
        
        $Photo_Gallery_folder = $img_path . '/';
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
            chmod($Photo_Gallery_folder, 0777);
        }
		
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $image_name = pathinfo($img_name, PATHINFO_FILENAME) . '_' . date("YmdHis") . substr(rand(),0,3) . '.' . $ext;

		//checking if file exsists
		if(file_exists("$Photo_Gallery_folder/$image_name") && SITE_TYPE != "Demo") {
            unlink("$Photo_Gallery_folder/$image_name");
        }
		
		if(move_uploaded_file($image_object, "$Photo_Gallery_folder/$image_name")) {
            //echo "Success";
        } else {
            //echo "Failed";
        }
    }
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
	
    $oCache->flushData();
    header("Location:manage_app_home_screen.php");
    exit;
    
}

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel_Org . "'";
    $db_data = $obj->MySQLSelect($sql);
	$vLabel = $db_data[0]['vLabel'];
    foreach ($db_data as $key => $value) {				
        $vValue = 'vValue_'. $value['vCode'];
        $$vValue = $value['vValue'];         
		$arrLang[$vValue] = $$vValue;
	}
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

?>
<!DOCTYPE html><!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | More Icon <?= $actionSave; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <? include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
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
					<h2>Edit More Service Icon</h2>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php include('valid_msg.php'); ?>
                    <form id="vtype" class="categoryform" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <?php if ($count_all > 0) { ?><?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Label For More Icon <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text"  class="form-control <?= ($id != "") ? 'readonly-custom' : '' ?>"  id="vCategory_Default" name="vCategory_Default" value="<?= htmlspecialchars($arrLang['vValue_' . $default_lang], ENT_QUOTES, 'UTF-8'); ?>"
                               data-originalvalue="<?= htmlspecialchars($arrLang['vValue_' . $default_lang], ENT_QUOTES, 'UTF-8'); ?>"  readonly="readonly" <?php if ($id != "") { ?> onclick="editCategory('Add')" <?php } ?> required>
                                </div>
                               
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCategory('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                
                            </div>
                            <div class="modal fade" id="vCategory_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="category_action"></span> Label For More Icon
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vValue_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vValue_' . $vCode;
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?><?php
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
                                                        <label>Label For More Icon (<?= $vTitle; ?>
                                                            ) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control"
                                                               name="<?= $vValue; ?>" id="<?= $vValue; ?>"
                                                               value="<?= htmlspecialchars($$vValue, ENT_QUOTES, 'UTF-8'); ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage"
                                                                            class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vValue_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage"
                                                                            class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vValue_', '<?= $default_lang ?>');">
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
                                                    : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                            </h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" id="vValue_btn" style="margin-left: 0 !important" onclick="saveCategory()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vValue_')">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>>Label For More Icon <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="vValue_<?= $default_lang; ?>"  id="vValue_<?= $default_lang; ?>" value="<?= $db_data[0]['vValue_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                        <?php }                                     
                        }
                        if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3() || $isAppHomeScreenV4) { ?>

                            <div class="row Icon imagebox">
                                <div class="col-lg-12">
                                    <label>Icon</label>
                                </div>
                                <div class="col-md-6 col-sm-6">                                                
                                    <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/$img_name" ?>">
                                    <input type="file" class="form-control" name="vListLogo3" id="vListLogo3" <?php echo $required_rule; ?>>
                                    
                                    <span class="notes">
                                    <?php if(strtoupper($APP_TYPE) == "RIDE" || strtoupper($APP_TYPE) == "RIDE-DELIVERY" || strtoupper(IS_CUBEX_APP) == "YES") {
                                        if($isAppHomeScreenV4) { ?>
                                        [<strong>Note:</strong> Recommended dimension for banner image(.png) is 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?><br><strong>Shown in App Home Screen</strong>]
                                        <?php } else { ?>
                                        [<strong>Note:</strong> Recommended dimension for banner image(.png) is <?= $vIconDetails ?>. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]
                                        <?php } ?>
                                    <?php } else { ?>
                                        [<strong>Note:</strong> Recommended dimension for banner image(.png) is 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]
                                    <?php } ?> </span>
                                </div>
                            </div>
                        <?php }  ?>
                        <input type="hidden" name="iServiceIdEdit" value="<?= $iServiceIdEdit; ?>">
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit" value="Update">
                            </div>
                        </div>
                    </form>
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
    </div>
<? include_once('footer.php'); ?>

<script type="text/javascript">
    function editCategory(action) {
        $('#category_action').html(action);
        $('#vCategory_Modal').modal('show');
    }
    function saveCategory() {
        if ($('#vValue_<?= $default_lang ?>').val() == "" || $('#vValue_<?= $default_lang ?>').val().trim() == "") {
            $('#vValue_<?= $default_lang ?>_error').show();
            $('#vValue_<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#vValue_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vCategory_Default').val($('#vValue_<?= $default_lang ?>').val());
        $('#vCategory_Modal').modal('hide');
    }
    if ($('.categoryform').length !== 0) {
        $(".categoryform").validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
            },
            rules: {
               vCategory_Default:{
                    required:true
                },
                vListLogo3:{
                    extension: imageUploadingExtenstionjsrule
                },
                vLogo2:{
                    extension: imageUploadingExtenstionjsrule
                }
            },
            messages: {
                    vCategory_Default:{
                        required: requiredFieldMsg
                    },
                    vListLogo3:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vLogo2:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
        });
    }
</script>
</body>
<!-- END BODY-->
</html>