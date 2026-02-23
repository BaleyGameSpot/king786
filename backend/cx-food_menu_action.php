<?php
include_once('common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
    
if($_REQUEST['id'] != '' && $_SESSION['sess_iCompanyId'] != ''){
    $sql = "select * from food_menu where iFoodMenuId = '".$_REQUEST['id']."' AND iCompanyId = '".$_SESSION['sess_iCompanyId']."'";
    $db_cmp_id = $obj->MySQLSelect($sql);
  
    if(!scount($db_cmp_id) > 0) {
        header("Location:food_menu.php?success=0&var_msg=".$langage_lbl['LBL_NOT_YOUR_FOOD']);
    }
}

/*$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';*/
$success = '';
if(isset($_SESSION['message_code']) && !empty($_SESSION['message_code']))
{
    $success = $_SESSION['message_code'];
    $_SESSION['message_code'] = '';
}
$var_msg = '';
if(isset($_SESSION['session_msg']) && !empty($_SESSION['session_msg']))
{
    $var_msg = $_SESSION['session_msg'];
    $_SESSION['session_msg'] = '';
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
$sessioniCompanyId = $_SESSION['sess_iUserId'];

$tbl_name = 'food_menu';
$script = 'FoodMenu';
    
// For Restaurants
$db_company = $obj->MySQLSelect("SELECT * FROM `company` WHERE iCompanyId = '$sessioniCompanyId'");

// For Languages
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);


// set all variables with either post (when submit) either blank (when insert)
$iCompanyId   = isset($_POST['iCompanyId'])?$_POST['iCompanyId']:'';
$iDisplayOrder = isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:'';
$eStatus = isset($_POST['eStatus'])?$_POST['eStatus']:'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';

$vMenu_store =array();
    //$vMenuDesc_store =array();
    $count_all = scount($db_master);
    if($count_all > 0) {
        for($i=0;$i<$count_all;$i++) {
            $vValue = 'vMenu_'.$db_master[$i]['vCode'];
            array_push($vMenu_store ,$vValue);   
            $$vValue  = isset($_POST[$vValue])?$_POST[$vValue]:'';
        }
    }
    
      
    if (isset($_POST['submit'])) { 
    /*  if(!empty($id) && SITE_TYPE =='Demo'){
        $_SESSION['success'] = 2;
        header("Location:food_menu.php?id=".$id);exit;
      }*/
    
        if($id != "") {
            $sql = "SELECT iDisplayOrder FROM `food_menu` where iFoodMenuId = '$id'";
            $displayOld = $obj->MySQLSelect($sql);
            $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
     
            if($oldDisplayOrder > $iDisplayOrder) {
                $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
                $db_orders = $obj->MySQLSelect($sql);
                if(!empty($db_orders)){
                    $j = $iDisplayOrder+1;
                    for($i=0;$i<scount($db_orders);$i++){
                        $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
                        
                        $obj->sql_query($query);
                        $j++;
                    }
                }
            }else if($oldDisplayOrder < $iDisplayOrder) {
                $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
                $db_orders = $obj->MySQLSelect($sql);
              
                if(!empty($db_orders)){
                    if($oldDisplayOrder == 0){
                        $j = $oldDisplayOrder + 1;
                    } else {
                        $j = $oldDisplayOrder;
                    }
                    for($i=0;$i<scount($db_orders);$i++){
                        $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
                        $obj->sql_query($query);
                        $j++;
                    }
                }
            }
        } else {
            $sql = "SELECT * FROM `food_menu` WHERE iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            
            if(!empty($db_orders)){
                $j = $iDisplayOrder+1;
                for($i=0;$i<scount($db_orders);$i++){
                    $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
                    $obj->sql_query($query);
                    $j++;
                }
            }
        }
        
        $image_object = $_FILES['vImage']['tmp_name'];  
        $image_name   = $_FILES['vImage']['name'];
        $image_update = "";
        if($image_name != ""){
            // $filecheck = basename($_FILES['vImage']['name']);                            
            // $fileextarr = explode(".",$filecheck);
            // $ext=strtolower($fileextarr[scount($fileextarr)-1]);
            // $flag_error = 0;
            // if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
            //     $flag_error = 1;
            //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            // }
            require_once(SITE_ADMIN_URL."/library/validation.class.php");
            $validobjNew1 = new validation();
            $docUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
            $error = $validobjNew1->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $docUploadingExtenstionMsg);

            $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
            $image_width = $image_info[0];
            $image_height = $image_info[1];
            
            if($error){
                $_SESSION['message_code'] = '3';
                $_SESSION['session_msg'] = $error;
                header("Location:food_menu_action.php");
                exit;
            } else {
                $oldImage = $_POST['oldImage'];
                $check_file = $tconfig["tsite_upload_images_menu_category_path"] .'/' . $oldImage;
                if ($oldImage != '' && file_exists($check_file)) {
                    @unlink($tconfig["tsite_upload_images_menu_category_path"] .'/' . $oldImage);
                }
                $Photo_Gallery_folder = $tconfig["tsite_upload_images_menu_category_path"].'/';
                if(!is_dir($Photo_Gallery_folder)){
                    mkdir($Photo_Gallery_folder, 0777);
                    chmod($Photo_Gallery_folder, 0777);
                }  
                $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '',$tconfig["tsite_upload_image_file_extensions"]);
                $vImage = $img[0];

                $image_update = "`vImage` = '".$vImage."',";
            }
        }
        
        for($i=0;$i<scount($vMenu_store);$i++) {   
            $q = "INSERT INTO ";
            $where = '';
            
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iFoodMenuId` = '" . $id . "'";
            }
        
            $eStatus_query = '';
            if($action == "Add"){
              $eStatus_query = "  `eStatus` = '" . $eStatus . "',";
            }
        
            $vValue = 'vMenu_'.$db_master[$i]['vCode'];
            //$vValue_desc = 'vMenuDesc_'.$db_master[$i]['vCode'];
            $query = $q . " `" . $tbl_name . "` SET
                `iCompanyId` = '" . $iCompanyId . "',
                `iDisplayOrder` = '" . $iDisplayOrder . "',
                `iServiceId` = '" . $iServiceId . "',
                $eStatus_query
                $image_update
                ".$vValue." = '" .$_POST[$vMenu_store[$i]]. "'"
                . $where;
            
            $obj->sql_query($query);
            $id = ($id != '') ? $id : $obj->GetInsertId(); 
        }
              
        if ($action == "Add") {
            $var_msg = $langage_lbl["LBL_FOOD_CATEGORY_INSERT_MSG"];
        } else {
            $var_msg = $langage_lbl["LBL_FOOD_CATEGORY_UPDATE_MSG"];
        }

        header("Location:food_menu.php?success=1&var_msg=".$var_msg);
        //End :: Upload Image Script
       // header("Location:".$backlink);exit;
    }
      
    // for Edit
    $EditServiceId = 0;
    if ($action == 'Edit') {
        $sql = "SELECT * FROM " . $tbl_name . " WHERE iFoodMenuId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
    
        if (scount($db_data) > 0) {
            for($i=0;$i<scount($db_master);$i++)
            {
                foreach($db_data as $key => $value) {
                    $vValue = 'vMenu_'.$db_master[$i]['vCode'];
                    $$vValue = $value[$vValue];
                    $iCompanyId = $value['iCompanyId'];
                    $iDisplayOrder = $value['iDisplayOrder'];
                    $eStatus = $value['eStatus'];
                    $iFoodMenuId = $value['iFoodMenuId'];
                    $oldImage = $value['vImage'];
            
                    $arrLang[$vValue] = $$vValue;  
                    $EditServiceId = $value['iServiceId'];       
                }
            }
        } 
    
    }
        
    if($action == 'Add'){
        $action_lbl = $langage_lbl['LBL_ACTION_ADD'];
    } elseif($action == 'Edit') {
        $action_lbl = $langage_lbl['LBL_ACTION_EDIT'];
    }
    
    $default_lang_sess = $default_lang;
    if(isset($_SESSION['sess_lang']))
    {
        $default_lang_sess = $_SESSION['sess_lang'];
    }

    $sqlLang = "SELECT vCode FROM language_master WHERE eDefault = 'Yes'";
    $DefaultLanguageDB = $obj->MySQLSelect($sqlLang);
    $defaultLang = $DefaultLanguageDB[0]['vCode'];

    $EN_available = $LANG_OBJ->checkLanguageExist();
    $db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

    $catdata = serviceCategories;
    $allservice_cat_data = json_decode($catdata, true);
    foreach ($allservice_cat_data as $k => $val) {
        $iServiceIdArr[] = $val['iServiceId'];
    }
    $serviceIds = implode(",", $iServiceIdArr);
    $service_category = "SELECT iServiceId,vServiceName_" . $default_lang_sess . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
    $service_cat_list = $obj->MySQLSelect($service_category);

    if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
        $fsql = " IF(iServiceIdMulti != '', iServiceIdMulti, iServiceId) as iServiceId ";
    }
    else {
        $fsql = " iServiceId";
    }
    $qry_cat = "SELECT $fsql FROM `company` WHERE iCompanyId = '" . $sessioniCompanyId . "'";
    $db_chk = $obj->MySQLSelect($qry_cat);
    $EditServiceIdNew = $db_chk[0]['iServiceId'];
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_FOOD_CATEGORY_FRONT']; ?> <?= $action; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php");?>
        <!-- End: Default Top Script and css-->
        <style>
            .btn-convert-all{
            padding: 3px;
            color: #ffffff;
            background-color: #428bca;
            border-color: #357ebd;
            display: inline-block;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: normal;
            line-height: 1.428571429;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            background-image: none;
            border: 1px solid transparent;
            border-radius: 4px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select: none;
            user-select: none;
            }
            
            .text-danger {
            color: #ff0000;
            font-size: 14px;
            margin: 5px 0 0 8px;
            }

            .loding-action {
                left: 0;
                margin: auto;
                position: fixed;
                right: 0;
                top: 0;
                bottom: 0;
                height: 100%;
                z-index: 999999999;

            }

            .loding-action div {
                left: 50%;
                position: absolute;
                top: 50%;
                transform: translate(-50%, -50%);
            }

            .text-danger {
                color: #ff0000;
                font-size: 14px;
                margin: 5px 0 0 8px
            }

            .modal-body {
                max-height: calc(100vh - 300px);
                overflow-y: auto;
                margin-right: 0;
                overflow-x: hidden;
                padding: 30px
            }

            #menu_cat_Modal .model-footer {
                border-top: 1px solid #cccccc;
            }

            .modal-input {
                width: 100%;
                float: left;
                position: relative;
            }

            #menu_cat_Modal .newrow {
                float: left;
                width: 100%;
            }

            #menu_cat_Modal .text-danger {
                color: #ff0000;
                font-size: 14px;
            }

            #menu_cat_Modal .custom-modal {
                height: auto;
            }

            #menu_cat_Modal .modal-content {
                height: 100%;
            }

            .readonly-custom {
                background-color: #ffffff !important;
            }
            
            #menu_cat_Modal .general-form .form-group:last-child {
                margin-bottom: 0
            }

            #menu_cat_Modal .model-footer .button-block .gen-btn {
                margin-bottom: 0
            }

            .item-cat-button button {
                padding: 17px;
                margin-left: 15px;
            }

            .item-cat-button button span {
                font-size: 20px !important;
            }
        </style>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php");?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php");?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?=$langage_lbl['LBL_FOOD_CATEGORY_FRONT']; ?></h1>
                        </div>
                        <div class="button-block end">
                            <a href="food_menu.php" class="gen-btn" ><?=$langage_lbl['LBL_BACK_To_Listing_WEB']; ?></a>
                        </div>
                    </div>
                </div>
            </section>
            <div class="profile-section">
                <div class="profile-section-inner ">
                    <!-- login in page -->
                    <div class="food-action-page" style="width:100%">
                        <? if ($success == 1) {?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <?} else if($success == 2){ ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <?php } else if ($var_msg != ""){ ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $var_msg; ?>
                            </div>
                       <?php } ?>
                        <div style="clear:both;"></div>
                        <form id="food_category_form" class="general-form" name="food_category_form" method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="iFoodMenuId" value="<?= $id; ?>"/>
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="food_menu.php"/>
                            <div class="partation">
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'];?> <span class="red"> *</span></strong>
                                    <span id="showDisplayOrder001">
                                        <?php if($action == 'Add') { ?>
                                        <select name="iDisplayOrder" id="iDisplayOrder">
                                            <?php for($i=1;$i<=$count+1;$i++) {?>
                                            <option value="<?php echo $i?>" 
                                                <?php if($i == $count+1)
                                                    echo 'selected';?>> <?php echo $i?> </option>
                                            <?php }?>
                                        </select>
                                        <?php }else { ?>
                                        <select name="iDisplayOrder" id="iDisplayOrder">
                                            <?php for($i=1;$i<=$count;$i++) { ?>
                                            <option value="<?php echo $i?>"
                                                <?php
                                                    if($i == $iDisplayOrder)
                                                    echo 'selected';
                                                    ?>
                                                > <?php echo $i?> </option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                    </span>
                                </div>
                                <?php if($_SESSION['sess_user'] != 'company'){ ?>
                                <div class="form-group half">
                                    <label>Restaurant<span class="red"> *</span></label>
                                    <select name="iCompanyId" id="iCompanyId" required  onchange="changeDisplayOrderCompany(this.value,'<?php  echo $id; ?>')">
                                        <option value="" >Select Restaurant</option>
                                        <?php foreach($db_company as $dbc) { ?>
                                        <option value="<?php echo $dbc['iCompanyId']; ?>"<?if($dbc['iCompanyId'] == $iCompanyId){?>selected<? } ?>><?php echo $dbc['vCompany'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php } else { ?>
                                <input type="hidden" id="iCompanyId" name="iCompanyId" value="<?php echo $sessioniCompanyId; ?>" />

                                <?php if (scount($allservice_cat_data) > 1 && $MODULES_OBJ->isEnableStoreMultiServiceCategories()) { 
                                        $EditServiceIdNewArr = explode(",", $EditServiceIdNew);
                                    ?>
                                    <div class="form-group half">
                                        <strong><?= $langage_lbl['LBL_SERVICES_TYPE'] ?><span class="red"> *</span></strong>
                                    
                                        <select name="iServiceId" id="iServiceId" required="">
                                            <option value="">Select</option>
                                            <?php 
                                                for ($i = 0; $i < scount($service_cat_list); $i++) { 
                                                    if(in_array($service_cat_list[$i]['iServiceId'], $EditServiceIdNewArr)) {
                                            ?>
                                            <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?php if ($EditServiceId == $service_cat_list[$i]['iServiceId']) { ?>selected<?php } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                            <?php } 
                                            } ?>
                                        </select>
                                    </div>
                                <?php } else { ?>
                                    <input type="hidden" name="iServiceId" id="iServiceId" value="<?php echo $EditServiceIdNew;?>">
                                <?php } ?>

                                <?php if(scount($db_master) > 1) { ?>
                                <div class="half-column">
                                    <strong>&nbsp;</strong>
                                    <div class="form-group" <?php if($id != "") { ?> style="display: flex;" <?php } ?>>
                                        <label><?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'];?> <span class="red"> *</span></label>  
                                        <input type="text" id="vMenu_Default" value="<?= $arrLang['vMenu_'.$default_lang_sess]; ?>" data-originalvalue="<?= $arrLang['vMenu_'.$default_lang_sess]; ?>" readonly="readonly" class="<?= ($id == "") ?  'readonly-custom' : '' ?>" <?php if($id == "") { ?> onclick="editMenuCategory('Add')" <?php } ?>>
                                        <div class="help-block error" id="vMenu_Default_error" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>

                                        <?php if($id != "") { ?>
                                        <div class="item-cat-button">
                                            <button type="button" class="gen-btn" onclick="editMenuCategory('Edit');"><span class="icon-edit" aria-hidden="true"></span></button>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                               

                                <div class="custom-modal-main in  fade" id="menu_cat_Modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="custom-modal">
                                        <div class="modal-content">
                                            <div class="model-header">
                                                <h4><span id="modal_action"></span> <?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'];?></h4>
                                                <i class="icon-close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vMenu_')"></i>
                                            </div>
                                            <div class="modal-body">
                                                <div class="general-form">
                                                    <?php
                                                    if ($count_all > 0) 
                                                    {
                                                        for ($i = 0; $i < $count_all; $i++) 
                                                        {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                    
                                                            $vValue = 'vMenu_' . $vCode;
                                                    
                                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';

                                                            $vCodeDefault = $default_lang;
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    $vCodeDefault = 'EN';
                                                                }
                                                                else {
                                                                    $vCodeDefault = $default_lang;
                                                                }
                                                            }
                                                    ?>
                                                        
                                                            <div class="form-group newrow">
                                                                <div class="modal-input">
                                                                    <label><?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'];?> (<?= $vTitle ?>)</label>
                                                                    <input type="text" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" >
                                                                    <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="form-group newrow">
                                                                        <button type="button" class="gen-btn" onclick="getAllLanguageCode('vMenu_', 'EN');" style="margin: 0"><?= $langage_lbl['LBL_CONVERT_ALL_TXT']; ?></button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <button type="button" class="gen-btn" onclick="getAllLanguageCode('vMenu_', '<?= $default_lang ?>');" style="margin: 0"><?= $langage_lbl['LBL_CONVERT_ALL_TXT']; ?></button>
                                                                <?php }
                                                                }
                                                            }
                                                            
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="model-footer">
                                                <div class="button-block">
                                                    <button type="button" class="gen-btn" onclick="saveMenuCategory()"><?= $langage_lbl['LBL_ADD']; ?></button>
                                                    <button type="button" class="gen-btn" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vMenu_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <div class="half-column">
                                    <strong>&nbsp;</strong>
                                    <div class="form-group" <?php if($id != "") { ?> style="display: flex;" <?php } ?>>
                                        <label><?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'];?> <span class="red"> *</span></label>  
                                        <input type="text" id="vMenu_<?= $default_lang_sess ?>" name="vMenu_<?= $default_lang_sess ?>" value="<?= $arrLang['vMenu_'.$default_lang_sess]; ?>" class="">
                                        <div class="help-block error" id="vMenu_<?= $default_lang_sess ?>_error" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                    </div>
                                    
                                </div>
                                <?php } ?>
                                <?php } ?>

                                <?php if(strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES" && $db_company[0]['iServiceId'] == 1) { ?>
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_ITEM_CATEGORY_IMG_TXT'] ?></strong>
                                    <div class="imageupload">
                                        <div class="file-tab">
                                            <span id="single_img001">
                                            <?php
                                                $imgpth = $tconfig["tsite_upload_images_menu_category_path"] . '/' . $oldImage;
                                                
                                                $imgUrl = $tconfig["tsite_upload_images_menu_category"] . '/' . $oldImage;
                                                
                                                if ($oldImage != "" && file_exists($imgpth)) {
                                                
                                                    ?>
                                            <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                            <?php } ?>
                                            </span>
                                            <div>
                                                <input type="hidden" name="oldImage" value="<?= trim($oldImage); ?>">
                                                <div class="fileUploading" filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                    <!-- <input name="vImage" onchange="preview_mainImg(event);" type="file"> -->
                                                    <input name="vImage" onChange="validate_fileextension(this.value);" type="file" >
                                                </div>
                                                <div class="fileerror help-block error"></div>
                                                
                                                <!--added by SP for required validation add in menu item image when recommended is on on 26-07-2019 -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="button-block">
                                <input type="submit" class="gen-btn" name="submit" id="submit" value="<?= $action_lbl; ?> <?php echo $langage_lbl['LBL_FOOD_ADMIN'];?>" >
                            </div>
                        </form>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">
                <img src="default.gif">   
                <p></p>
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>
        </div>
        <!-- footer part -->
        <?php include_once('footer/footer_home.php');?>
        <!-- footer part end -->
        <!-- End:contact page-->
        <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php');
            $lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];?>
        <style>
            span.help-block{
            margin:0;
            padding: 0;
            }
        </style>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"]?>js/validation/jquery.validate.min.js" ></script>
        <?php if($lang != 'en') { ?>
        <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
        <? include_once('otherlang_validation.php');?>
        <?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <script>
            function changeDisplayOrderCompany(companyId,foodId)
            {
               var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_display_order.php',
                    'AJAX_DATA': {iFoodMenuId: foodId},
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $("#showDisplayOrder001").html('');
                        $("#showDisplayOrder001").html(data);
                    }
                    else {
                        console.log(response.result);
                    }
                });
               
            }
            
            $(document).ready(function(){
             changeDisplayOrderCompany('<?php echo $iCompanyId; ?>','<?php echo $id; ?>');
            });
            
        </script>
        <script type="text/javascript" language="javascript">
            function validate_fileextension(filename) {
               
                var fileExtension = $.parseJSON(imageUploadingExtenstionJson);
                if ($.inArray(filename.split('.').pop().toLowerCase(), fileExtension) == -1) {
                    $(".fileerror").html(imageUploadingExtenstionMsg);
                    $('#submit').prop("disabled", true);
                    return false;
                }
                else {
                    $('#submit').prop("disabled", false);
                    $(".fileerror").html("");
                }
            }
            var action_lbl;
            function general_label() {
                $(document).on('focusin','.form-group input,.form-group textarea',function(){
                    $(this).closest('.form-group').addClass('floating');
                });
                $(document).on('focusout','.form-group input,.form-group textarea',function(){
                    if($(this).val() == ""){
                        $(this).closest('.form-group').removeClass('floating');
                    }
                });
            
                $(document).on('focusin','.form-group input,.form-group textarea',function(){
                    $(this).parent('relation-parent').closest('.form-group').addClass('floating');
                });
                $(document).on('focusout','.form-group input,.form-group textarea',function(){
                    if($(this).val() == ""){
                        $(this).parent('relation-parent').closest('.form-group').removeClass('floating');
                    }
                });
            
                $( ".general-form .form-group" ).each(function( index ) {
                    $this = $(this).find('input');
                    if($this.val() == ""){
                        $this.closest('.form-group').removeClass('floating');
                    }else {
                        $this.closest('.form-group').addClass('floating');   
                    }
                })
                $( ".gen-from .form-group" ).each(function( index ) {
                    $this = $(this).find('input');
                    if($this.val() == ""){
                        $this.closest('.form-group').removeClass('floating');
                    }else {
                        $this.closest('.form-group').addClass('floating');   
                    }
                })
                $( ".general-form .form-group" ).each(function( index ) {
                    $this = $(this).find('textarea');
                    if($this.val() == ""){
                        $this.closest('.form-group').removeClass('floating');
                    }else {
                        $this.closest('.form-group').addClass('floating');   
                    }
                });
            }

            function editMenuCategory(action)
            {
                if(action == 'Add'){
                    action_lbl = '<?php echo $langage_lbl['LBL_ACTION_ADD'];?>';
                } else {
                    actionlbl = '<?php echo $langage_lbl['LBL_ACTION_EDIT'];?>';
                }
                $('#modal_action').html(action_lbl);
                $("#menu_cat_Modal").find(".modal-body").scrollTop(0);
                $('#menu_cat_Modal').addClass('active'); 
            }
        
            function saveMenuCategory()
            {
                //console.log($('#vMenu_<?= $defaultLang ?>').val());
                if($.trim($('#vMenu_<?= $defaultLang ?>').val()) == "") {
                    $('#vMenu_<?= $defaultLang ?>_error').show();
                    $('#vMenu_<?= $defaultLang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        $('#vMenu_<?= $defaultLang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                //var letterNumber = /[^a-zA-Z0-9 àâäèéêëîïôœùûüÿçÀÂÄÈÉÊËÎÏÔŒÙÛÜŸÇ]/;
                //if($('#vMenu_<?= $default_lang ?>').val().match(letterNumber))  {

                    $('#vMenu_Default').val($('#vMenu_<?= $default_lang ?>').val());
                    if($('#vMenu_<?= $default_lang ?>').val() == "")
                    {
                        $('#vMenu_Default').val($('#vMenu_<?= $defaultLang ?>').val());
                    }
                    $('#menu_cat_Modal').removeClass('active'); 
                    general_label();

                //} else { 
                   //alert("<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB'])?>"); 
                   //return false; 
                //}

            }

            $('#submit').click(function() {
                if($('#vMenu_Default').length > 0) {
                    $('#vMenu_Default_error').hide();
                    if($('#vMenu_Default').val() == "")
                    {
                        $('#vMenu_Default_error').show();
                        clearInterval(langVar);
                        langVar = setTimeout(function() {
                            $('#vMenu_Default_error').hide();
                        }, 5000);
                        return false;
                    }
                }
                else {
                    $('#vMenu_<?= $default_lang_sess ?>_error').hide();
                    if($('#vMenu_<?= $default_lang_sess ?>').val() == "")
                    {
                        $('#vMenu_<?= $default_lang_sess ?>_error').show();
                        clearInterval(langVar);
                        langVar = setTimeout(function() {
                            $('#vMenu_<?= $default_lang_sess ?>_error').hide();
                        }, 5000);
                        return false;
                    }
                }
            })

            function preview_mainImg(event)
            {

                $("#single_img001").html('');
                $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
                $(".changeImg001").text('Change');
                $(".remove_main").show();

            }
        </script>
    </body>
</html>