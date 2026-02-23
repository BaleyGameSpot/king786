<?php
include_once('../common.php');
require_once(TPATH_CLASS."Imagecrop.class.php");

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:''; // iUniqueId
$success	= isset($_REQUEST['success'])?$_REQUEST['success']:'';
$action 	= ($id != '')?'Edit':'Add';	
//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name 	= 'hotel_banners';
$script 	= 'hotel_banners';


// fetch all lang from language_master table 
$getLangData = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active'");

$vImage = isset($_POST['vImage_old']) ? $_POST['vImage_old'] : '';
$eStatus_check 	= isset($_POST['eStatus'])?$_POST['eStatus']:'off';
$vTitle = isset($_POST['vTitle'])?$_POST['vTitle']:'';
$eStatus 		= ($eStatus_check == 'on')?'Active':'Inactive';

$iCopyForOther = isset($_POST['iCopyForOther']) ? $_POST['iCopyForOther'] : 'off';

$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order	= $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM ".$tbl_name." WHERE vCode = '".$default_lang."'");
$iDisplayOrder	= isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
$iDisplayOrder	= $iDisplayOrder + 1; // Maximum order number

$iDisplayOrder	= isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
$temp_order 	= isset($_POST['temp_order'])? $_POST['temp_order'] : "";

if ($_REQUEST['vCode'] != "") {
    $searchvCode = "?langSearch=" . $_REQUEST['vCode'] . "&vCode=" . $_REQUEST['vCode'];
}	
if(isset($_POST['submit'])) { //form submit
	 $vCodeLang = isset($_POST['vCode']) ? $_POST['vCode'] : 0;
	if($action == "Add" && !$userObj->hasPermission('create-hotel-banner')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create hotel banner.';
        header("Location:hotel_banner.php". $searchvCode);
        exit;
    }

    if($action == "Edit" && !$userObj->hasPermission('edit-hotel-banner')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update hotel banner.';
        header("Location:hotel_banner.php". $searchvCode);
        exit;
    }


    if(SITE_TYPE =='Demo'){
		$_SESSION['success'] = 2;
		header("Location:hotel_banner.php". $searchvCode);exit;
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
	
	$select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM ".$tbl_name." WHERE vCode = '".$vCodeLang."'");
	$iUniqueId = isset($select_order[0]['iUniqueId'])?$select_order[0]['iUniqueId']:0;
	$iUniqueId = $iUniqueId + 1; // Maximum order number
	
	$q = "INSERT INTO ";
	$where = '';
	
	if($id != '' ){ 
		$q = "UPDATE ";
		$where = " WHERE `iUniqueId` = '".$id."' AND vCode = '".$vCodeLang."'";
		$iUniqueId = $id;
	}

	 if(!empty($id) && !empty($vCodeLang)) {
        $sqlrecord = "SELECT vTitle,eStatus,vImage,iDisplayOrder,vCode FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
        $db_records = $obj->MySQLSelect($sqlrecord);
        if(empty($db_records)) {
            $q = "INSERT INTO ";
            $where = '';
        }
    }

	$image_object = $_FILES['vImage']['tmp_name'];  
	$image_name   = $_FILES['vImage']['name'];
	
	if($image_name != ""){
		$filecheck = basename($_FILES['vImage']['name']);                            
		$fileextarr = explode(".",$filecheck);
		$ext=strtolower($fileextarr[scount($fileextarr)-1]);
		$flag_error = 0;
		// if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
		// 	$flag_error = 1;
		// 	$var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,gif,png,bmp.";
		// }

		require_once("library/validation.class.php");
		$validobj = new validation();
		$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
		$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
		if($error){
			$_SESSION['success'] = '3';
			$_SESSION['var_msg'] = $error;
			header("Location:hotel_banner.php". $searchvCode);
			exit;
		}

		$image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
		$image_width = $image_info[0];
		$image_height = $image_info[1];
		
		if($flag_error == 1){
			$_SESSION['success'] = '3';
			$_SESSION['var_msg'] = $var_msg;
			header("Location:hotel_banner.php". $searchvCode);
			exit;
        } else {
			$Photo_Gallery_folder = $tconfig["tsite_upload_images_hotel_banner_path"].'/';
			if(!is_dir($Photo_Gallery_folder)){
				mkdir($Photo_Gallery_folder, 0777);
			}  
			$img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg');
			$vImage = $img[0];
		}
	}
	
	$query = $q ." `".$tbl_name."` SET 	
		`vTitle` = '".$vTitle."',
		`vImage` = '".$vImage."',
		`eStatus` = '".$eStatus."',
		`iUniqueId` = '".$iUniqueId."',
		`iDisplayOrder` = '".$iDisplayOrder."',
		`vCode` = '".$vCodeLang."'"
	.$where;
	$obj->sql_query($query);

	if ($iCopyForOther == "on" && $action == "Add") {
		foreach ($getLangData as $lk => $lvalue) {
		    if ($vCodeLang != $lvalue['vCode']) {
		        $Data_banner = array();
		        $Data_banner['vTitle'] = $vTitle;
		        $Data_banner['vImage'] = $vImage;
		        $Data_banner['eStatus'] = $eStatus;
		        $Data_banner['iUniqueId'] = $iUniqueId;
		        $Data_banner['iDisplayOrder'] = $iDisplayOrder;
		        $Data_banner['vCode'] = $lvalue['vCode'];


		        if(empty($where)) {
		            $obj->MySQLQueryPerform($tbl_name, $Data_banner, "insert");
		        } else {
		            $obj->MySQLQueryPerform($tbl_name, $Data_banner, "update", $where);
		        }
		    }
		}
	}

	if($id != '' ){ 
		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
	} else {
		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
	}

	header("Location:hotel_banner.php". $searchvCode);
	exit();
	
}

// for Edit
if($action == 'Edit') {
	$sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder,vCode FROM ".$tbl_name." WHERE iUniqueId = '".$id."' and vCode = '".$vCodeLang."'";
	$db_data = $obj->MySQLSelect($sql);
	$iUniqueId = $id;
	if(scount($db_data) > 0) {
		foreach($db_data as $key => $value) {
			//$vTitle 			= 'vTitle_'.$value['vCode'];
			$vTitle 			= $value['vTitle'];				
			$eStatus 			= $value['eStatus'];
			$vImage 			= $value['vImage'];
			$iDisplayOrder 		= $value['iDisplayOrder'];
			 $vCodeLang = $value['vCode'];
		}
	}
}
?>
<!DOCTYPE html>
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>Admin | Hotel Banner <?=$action;?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		
		<? include_once('global_files.php');?>
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
							<h2><?=$action;?> Hotel Banner</h2>
							<a href="hotel_banner.php">
								<input type="button" value="Back to Listing" class="add-btn">
							</a>
						</div>
					</div>
					<hr />	
					<div class="body-div">
						<div class="form-group">
						<? if ($success == 0 && $_REQUEST['var_msg'] != "") {?>
							<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								<? echo $_REQUEST['var_msg']; ?>
							</div><br/>
						<?} ?>
						<? if($success == 1) { ?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									<?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
							</div><br/>
						<? } ?>
						<? if ($success == 2) {?>
		                 <div class="alert alert-danger alert-dismissable">
		                      <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
		                      <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
								</div><br/>
							<? } ?>
							<form method="post" id="banner" action="" enctype="multipart/form-data">
								<input type="hidden" name="id" value="<?=$id;?>"/>
								<input type="hidden" name="vImage_old" value="<?=$vImage?>">
								<div class="row">
                                    <?php if ($action == "Add") { ?>
                                    <div class="col-lg-12">
                                        <label>Select Language</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select  class="form-control" name = 'vCode'  id= 'vCode' onchange="bannerdata(this.value)">
                                            <?php for ($l = 0; $l < scount($getLangData); $l++) { ?>
                                                <option <?php if ($vCodeLang == $getLangData[$l]['vCode']) { ?>selected=""<?php } ?> value = "<?= $getLangData[$l]['vCode']; ?>"><?= $getLangData[$l]['vTitle']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <? } else { ?>
                                    <div class="col-lg-12">
                                        <label>Language: <?= $vCodeLang ?></label>
                                    </div>
                                    <input type="hidden" name="vCode" value="<?= $vCodeLang ?>">
                                    <? } ?>
                                </div>
								<div class="row">
									<div class="col-lg-12">
										<label>Image<?=($vImage == '')?'<span class="red"> *</span>':'';?></label>
									</div>
									<div class="col-lg-6">
										<? if($vImage != '') { ?>
											<!-- <img src="<?=$tconfig['tsite_upload_images_hotel_banner'].'/'.$vImage;?>" style="width:200px;height:100px;"> -->

											<img src="<?=$tconfig['tsite_url'].'resizeImg.php?w=400&src='.$tconfig['tsite_upload_images_hotel_banner'] . '/' . $vImage ?>" style="width:200px;"> 

											<input type="file" class="form-control" name="vImage" id="vImage" value="<?=$vImage;?>"/>
										<? } else { ?>
											<input type="file" class="form-control" name="vImage" id="vImage" value="<?=$vImage;?>" required/>
										<? } ?>
										<br/>
										[Note: Recommended dimension for Hotel banner image is 2880 X 1440.]
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>Title</label>
									</div>
									<div class="col-lg-6">
										<input type="text" name="vTitle" id="vTitle" value="<?=$vTitle?>" class="form-control" />
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>Status</label>
									</div>
									<div class="col-lg-6">
										<div class="make-switch" data-on="success" data-off="warning">
											<input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?>/>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>Display Order</label>
									</div>
									<div class="col-lg-6">
										<span id="orderdiv">
											<?
												$temp = 1;
												
												$dataArray = array();
												
												$query1 = "SELECT iDisplayOrder FROM ".$tbl_name." WHERE vCode = '".$vCodeLang."' ORDER BY iDisplayOrder";
												$data_order = $obj->MySQLSelect($query1);
												
												foreach($data_order as $value)
												{
													$dataArray[] = $value['iDisplayOrder'];
													$temp = $iDisplayOrder;
												}
											?>
											<input type="hidden" name="temp_order" id="temp_order" value="<?=$temp?>">
											<select name="iDisplayOrder" class="form-control">
												<? foreach($dataArray as $arr):?>
												<option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?=$arr;?>" >
													-- <?= $arr ?> --
												</option>
												<? endforeach; ?>
												<?if($action=="Add") {?>
													<option value="<?=$temp;?>" >
														-- <?= $temp ?> --
													</option>
												<? }?>
											</select>
										</span>
									</div>
								</div>
                                <?php if ($action == "Add") { ?>
		                            <div class="row">
		                                <div class="col-lg-12">
		                                    <label> Do you want to copy same banner for other languages also?</label>
		                                </div>
		                                <div class="col-lg-6">
		                                    <div class="make-switch" data-on="success" data-off="warning" data-on-label="Yes"
		                                         data-off-label="No">
		                                        <input type="checkbox" name="iCopyForOther"/>
		                                    </div>
		                                </div>
		                            </div>
		                        <?php } ?>                                 
								<div class="row">
									<?php if(($action == 'Edit' && $userObj->hasPermission('edit-hotel-banner')) || ($action == 'Add' &&  $userObj->hasPermission('create-hotel-banner'))){ ?>
										<div class="col-lg-12">
											<input type="submit" class="save btn-info" name="submit" id="submit" value="<?=$action;?> Hotel Banner">
										</div>
									<?php } ?>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->
		<? include_once('footer.php');?>
		<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
function bannerdata(val) {

    var ajaxData = {
        'URL': '<?= $tconfig['tsite_url_main_admin'] ?>banner_lang.php',
        'AJAX_DATA': {
            vCode: val,
            id: '<?= $_REQUEST['id']; ?>',
            order: 'Yes',
            eForhotel: 'Yes'
        },
        'REQUEST_DATA_TYPE': 'html'
    };
    getDataFromAjaxCall(ajaxData, function (response) {
        if (response.action == "1") {
            var dataHtml2 = response.result;
            if (dataHtml2 != "") {
                $('#orderdiv').html(dataHtml2);
                //$('.bannerlang').html(dataHtml2);
            }
        } else {
            console.log(response.result);
        }
    });
}

$('#banner').validate({
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
                vImage: {extension: imageUploadingExtenstionjsrule}
            },
            messages: {
                vImage: {
                    extension: imageUploadingExtenstionMsg,
                }
                
            },
            submitHandler: function (form) {                
                if ($(form).valid()) {
	                ShpSq6fAm7(form);
	                form.submit();
	            }
                // return false; // prevent normal form posting
            }
        });

</script>
	</body>
	<!-- END BODY-->    
</html>