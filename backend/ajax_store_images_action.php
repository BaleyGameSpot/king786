<?php

/*
 *
 * Created By : HJ.
 * Date : 31-Jan-2019.
 * File : ajax_dropzon_upload.
 * File Type : .php.
 * Purpose : For Upload and Remove Store Images By Dropzon
 * */
include_once('common.php');
session_start();
$iCompanyId = $_SESSION['sess_iUserId'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$storeImgName = isset($_REQUEST['imgname']) ? trim($_REQUEST['imgname']) : '';
$img_path = $tconfig["tsite_upload_images_panel"];

$tbl_name   = 'store_wise_banners';

$company_data = $obj->MySQLSelect("SELECT iServiceId, vLang, eStatus, eSafetyPractices FROM company WHERE iCompanyId = $iCompanyId");
$iServiceId = $company_data[0]['iServiceId'] ;
$vLang = $company_data[0]['vLang'] ;
$eStatus = $company_data[0]['eStatus'];
$eSafetyPractices = $company_data[0]['eSafetyPractices'];


$select_order   = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder, MAX(iUniqueId) AS iUniqueId FROM ".$tbl_name." WHERE iCompanyId = ".$iCompanyId);
$iDisplayOrder  = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder  = $iDisplayOrder + 1;

$iUniqueId = isset($select_order[0]['iUniqueId']) ? $select_order[0]['iUniqueId'] : 0;
$iUniqueId = $iUniqueId + 1;

$image_object = $_FILES['file']['tmp_name'][0];
$image_name = $_FILES['file']['name'][0];

if (isset($_FILES['file']) && $_FILES['file'] != "" && $action == "upload") {
    if($image_name != "") {
        $filecheck = basename($_FILES['file']['name'][0]);                            
        $fileextarr = explode(".",$filecheck);
        $ext=strtolower($fileextarr[scount($fileextarr)-1]);
        $flag_error = 0;
      
        if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
            $var_msg = $languageLabelsArr['LBL_FILE_EXT_VALID_ERROR_MSG']." .jpg, .jpeg, .gif, .png, .bmp";
            $returnArr['Action'] = "0";
            $returnArr['message'] = $var_msg;
            setDataResponse($returnArr);
        }

        $Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"].'/';
        if(!is_dir($Photo_Gallery_folder)){
            mkdir($Photo_Gallery_folder, 0777);
            chmod($Photo_Gallery_folder, 0777);
        }  
        $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');
        $vImage = $img[0];
    }

    $q = "INSERT INTO ";

    $query = $q ." `".$tbl_name."` SET  
        `vImage` = '".$vImage."',
        `eStatus` = 'Active',
        `iUniqueId` = '".$iUniqueId."',
        `iDisplayOrder` = '".$iDisplayOrder."',
        `iServiceId`= '".$iServiceId."',
        `iCompanyId`= '".$iCompanyId."'";

    $id = $obj->sql_query($query); 

    if ($id > 0) {
        $returnArr = array(
            'Action'    => 1,
            'filename'  => $vImage,
            'message'   => $langage_lbl['LBL_IMAGE_UPLOAD_SUCCESS_NOTE']
        );
        
        echo json_encode($returnArr);
        exit;
    } else {
        $returnArr = array(
            'Action'    => 0,
            'message'   => $langage_lbl['LBL_TRY_AGAIN_LATER_TXT']
        );
        
        echo json_encode($returnArr);
        exit;
    }
} else if ($action == "delete" && $storeImgName != "") {
    $Photo_Gallery_folder = $tconfig['tsite_upload_images_panel'] . '/';
        
    $OldImageName = $obj->MySQLSelect("SELECT vImage FROM ".$tbl_name." WHERE vImage = '" . $storeImgName . "' AND iCompanyId = ".$iCompanyId);

    foreach ($OldImageName as $value) {
        unlink($Photo_Gallery_folder . $value['vImage']);
    }

    $data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE vImage = '" . $storeImgName . "' AND iCompanyId = " . $iCompanyId);
    
    if (scount($data_logo) > 0) {
        $iDisplayOrder_db = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
        $id = $obj->sql_query("DELETE FROM `" . $tbl_name . "` WHERE vImage = '" . $storeImgName . "' AND iCompanyId = " . $iCompanyId);

        if ($iDisplayOrder_db < $iDisplayOrder)
            for ($i = $iDisplayOrder_db + 1; $i <= $iDisplayOrder; $i++)
                $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i . " AND iCompanyId = " . $iCompanyId);
    }
    
    if ($id > 0) {
        echo $langage_lbl['LBL_IMAGE_DELETE_SUCCESS_NOTE'];
        exit;
    } else {
        echo $langage_lbl['LBL_TRY_AGAIN_LATER_TXT'];
        exit;
    }
}else{
    echo $langage_lbl['LBL_TRY_AGAIN_LATER_TXT'];
    exit;
}
?>