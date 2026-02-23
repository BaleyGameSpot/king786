<?php
include_once('../common.php');
require_once(TPATH_CLASS."Imagecrop.class.php");

if(!$MODULES_OBJ->isEnableStorePhotoUploadFacility()){
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:''; // iUniqueId
$success	= isset($_REQUEST['success'])?$_REQUEST['success']:'';
$action 	= ($id != '')?'Edit':'Add';

//$temp_gallery = $tconfig["tpanel_path"];
$tbl_name 	= 'store_wise_banners';
$script 	= 'Store Wise Banner';

$vTitle = isset($_POST['vTitle'])?$_POST['vTitle']:'';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : '';
if(!empty($sid))
{
    $iCompanyId = $sid;
}
$vImage_old = isset($_POST['vImage_old']) ? $_POST['vImage_old'] : '';
$thumb = new thumbnail();
/* to fetch max iDisplayOrder from table for insert */
$select_order	= $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM ".$tbl_name." WHERE iCompanyId = '".$iCompanyId."'");
$iDisplayOrder	= isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
$iDisplayOrder	= $iDisplayOrder + 1; // Maximum order number

$iDisplayOrder	= isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
$temp_order 	= isset($_POST['temp_order'])? $_POST['temp_order'] : "";
$iServiceIdNew = isset($_POST['iServiceId'])?$_POST['iServiceId']:'';
    
$sid = (!empty($sid)) ? 'sid='.$sid : '';
if(isset($_POST['submit'])) { //form submit

   if(SITE_TYPE =='Demo'){
		$_SESSION['success'] = 2;
		header("Location:store_images.php".(($sid != "") ? "?".$sid : ""));exit;
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
	
	$select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM ".$tbl_name." WHERE iCompanyId = '".$iCompanyId."'");
	$iUniqueId = isset($select_order[0]['iUniqueId'])?$select_order[0]['iUniqueId']:0;
	$iUniqueId = $iUniqueId + 1; // Maximum order number
	
    $image_object = $_FILES['vImage']['tmp_name'];  
    $image_name   = $_FILES['vImage']['name'];

    $vImage = $vImage_old;
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

        if($flag_error == 1){

            $_SESSION['success'] = '3';
            $_SESSION['var_msg'] = $var_msg;
            header("Location:store_images.php".(($sid != "") ? "?".$sid : ""));
            exit;
            /*getPostForm($_POST,$var_msg,"banner_action.php?success=0&var_msg=".$var_msg);
            exit;*/
           } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"].'/';
            if(!is_dir($Photo_Gallery_folder)){
                mkdir($Photo_Gallery_folder, 0777);
            }  
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg');
            $vImage = $img[0];

            if(!empty($vImage_old) && file_exists($Photo_Gallery_folder . $vImage_old)) {
                unlink($Photo_Gallery_folder . $vImage_old);
            }
        }
    }


    $q = "INSERT INTO ";
    $where = '';
    
    if($id != '' ){ 
        $q = "UPDATE ";
        $where = " WHERE `iUniqueId` = '".$id."' AND iCompanyId = '".$iCompanyId."'";
        $iUniqueId = $id;
    }
    
    $query = $q ." `".$tbl_name."` SET  
        `vTitle` = '".$vTitle."',
        `vImage` = '".$vImage."',
        `eStatus` = '".$eStatus."',
        `iUniqueId` = '".$iUniqueId."',
        `iDisplayOrder` = '".$iDisplayOrder."',
        `iServiceId`= '".$iServiceIdNew."',
        `iCompanyId`= '".$iCompanyId."'"
    .$where;

    $obj->sql_query($query);
    if($id != '' ){ 
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }

    header("Location:store_images.php".(($sid != "") ? "?".$sid : ""));
    exit();
}
    
// for Edit
if($action == 'Edit') {

	$sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,iCompanyId FROM ".$tbl_name." WHERE iUniqueId = '".$id."' AND iCompanyId = '" . $iCompanyId . "'";
		
	$db_data = $obj->MySQLSelect($sql);

	$iUniqueId = $id;
	foreach($db_data as $key => $value) {
        // $vTitle             = $value['vTitle'];             
        $eStatus            = $value['eStatus'];
        $vImage             = $value['vImage'];
        $iDisplayOrder      = $value['iDisplayOrder'];
        $iServiceIdNew      = $value['iServiceId'];
        $iCompanyId         = $value['iCompanyId'];
    }
}

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);
foreach ($allservice_cat_data as $k => $val) {
   $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_".$default_lang." as servicename,eStatus FROM service_categories WHERE iServiceId IN (".$serviceIds.") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);

$serviceStoreArr = array();
$getStoreList = $obj->MySQLSelect("SELECT iServiceId,iCompanyId,vCompany,eStatus FROM company WHERE eStatus = 'Active' AND vCompany != '' AND iServiceId > 0 ORDER BY vCompany ASC");
for($g=0;$g<scount($getStoreList);$g++){
   if($iCompanyId == $getStoreList[$g]['iCompanyId']){
       $selectedServiceId = $getStoreList[$g]['iServiceId'];
   }
   $serviceStoreArr[$getStoreList[$g]['iServiceId']][] = $getStoreList[$g];
}

?>
<!DOCTYPE html>
<!--[if !IE]><!--> 
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Store Images <?=$action;?></title>
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
                            <h2><?=$action;?> Image</h2>
                            <a href="store_images.php<?= ($sid != "") ? '?'.$sid : '' ?>">
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
                            </div>
                            <br/>
                            <?} ?>
                            <? if($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div>
                            <br/>
                            <? } ?>
                            <? if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                            <br/>
                            <? } ?>
                            <form method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?=$id;?>"/>
                                <input type="hidden" name="vImage_old" value="<?=$vImage?>">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image <?= (empty($vImage)) ? '<span class="red"> *</span>' : '';?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if(!empty($vImage)) { ?>
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?w=400&h=200&src='.$tconfig['tsite_upload_images']. $vImage;?>" style="width:200px;height:100px;">
                                            <input type="file" name="vImage" id="vImage" value="<?=$vImage;?>"/>
                                        <? } else { ?>
                                            <input type="file" name="vImage" id="vImage" value="<?=$vImage;?>" required/>
                                        <? } ?>
                                        <b>[Note: Recommended dimension is 2880 * 1620.]</b>
                                    </div>
                                </div>
                                <?php /*<div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="vTitle" id="vTitle" value="<?=$vTitle?>" class="form-control" />
                                    </div>
                                </div>*/ ?>
                                <?php
                                    $service_store_selection = ""; 
                                    if(isset($_REQUEST['sid']) && !empty($_REQUEST['sid'])) {
                                        $service_store_selection = "disabled"; 
                                    } 
                                ?>
                                <?php if(scount($allservice_cat_data)<=1){?>
                                <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId'];?>"/>
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Service Category<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'iServiceId' id="iServiceId" onchange="displayStoreList(this.value);" required <?= $service_store_selection ?>>
                                            <option value="">Select</option>
                                            <? for($i=0;$i<scount($service_cat_list);$i++){ ?>
                                            <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?if($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?> selected <?php } else if($iServiceIdNew==$service_cat_list[$i]['iServiceId']){?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="row" id="storelisthtml" style="display: none;">
                                    <div class="col-lg-12">
                                        <label>Store Selection<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name="iCompanyId"  id="iCompanyIdhtml" required="required" <?= $service_store_selection ?>>
                                        </select>
                                    </div>
                                </div>
                                <? if(isset($_REQUEST['sid']) && !empty($_REQUEST['sid'])) { ?>
                                	<input type="hidden" name="iServiceId" value="<?= $selectedServiceId ?>">
                                	<input type="hidden" name="iCompanyId" value="<?= $iCompanyId ?>">
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?> value="Active"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Order</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?
                                            $temp = 1;
                                            
                                            $dataArray = array();
                                            
                                            $query1 = "SELECT DISTINCT iDisplayOrder FROM ".$tbl_name." WHERE iCompanyId = '".$iCompanyId."' ORDER BY iDisplayOrder";
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
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="save btn-info" name="submit" id="submit" value="<?=$action;?> Image">
                                        <a href="store_images.php<?= ($sid != "") ? '?'.$sid : '' ?>" class="btn btn-default back_link">Cancel</a>
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
        <? include_once('footer.php');?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript">
        	var serviceStoreArr = <?= json_encode($serviceStoreArr); ?>;
        	var selCompanyId = '<?= $iCompanyId; ?>';
            var selServiceId = '<?= $selectedServiceId; ?>';
            displayStoreList(selServiceId);
            function displayStoreList(serviceId){
                $("#iServiceId").val(serviceId);
                if(serviceId > 0){
                    $("#storelisthtml").show();
                    var optionhtml = '<option value="">--select--</option>';
                    var serviceData = serviceStoreArr[serviceId];
                    for(var h=0;h<serviceData.length;h++){
                        var selectionhtml = "";
                        if(selCompanyId == serviceData[h]['iCompanyId']){
                            selectionhtml = "selected='selected'";
                        }
                        optionhtml += "<option "+selectionhtml+" value='"+serviceData[h]['iCompanyId']+"'>"+serviceData[h]['vCompany']+"</option>"
                    }
                    $("#iCompanyIdhtml").html(optionhtml);
                }else{
                    $("#storelisthtml").hide();
                }
            }
        </script>
    </body>
    <!-- END BODY-->    
</html>