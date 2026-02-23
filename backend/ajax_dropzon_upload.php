<?php

/*
 *
 * Created By : HJ.
 * Date : 31-Jan-2019.
 * File : ajax_dropzon_upload.
 * File Type : .php.
 * Purpose : For Upload and Removed Provider Images By Dropzon
 * */
include_once('common.php');
session_start();
$driverId = $_SESSION['sess_iUserId'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$providerImgName = isset($_REQUEST['imgname']) ? $_REQUEST['imgname'] : '';
$img_path = $tconfig["tsite_upload_provider_image_path"];
$dateTime = date("Y-m-d H:is");

if($_SESSION['sess_user'] == "driver") 
{
    if (isset($_FILES['file']) && $_FILES['file'] != "" && $action == "upload" && $driverId > 0) {
        
        $time_val = time();
        $currrent_upload_time = time();
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['file']['tmp_name'];
        $image_name = $_FILES['file']['name'];
        if ($image_name != "") {
            $Photo_Gallery_folder = $img_path . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';
            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            //$image_name = $image_name
            $filecheck = basename($_FILES['file']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $image_name = "provider_" . $driverId . "_" . $time_val . "." . $ext;
            
            
            if(in_array($ext , ['mp4','mov','wmv','avi','flv','mkv','webm']))
            {
                $target_file = $Photo_Gallery_folder . basename($image_name);
                if (move_uploaded_file($image_object, $target_file)) {
                    
                } else {
                }
                $img = $image_name;
            }else{
                $img = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder, $vBannerTitle, NULL);
            }
            

           
            //print_r($img);die;
            //$img_time = explode("_", $img);
            //$time_val = $img_time[0];
            $provider_image = array();
            $provider_image['vImage'] = $img;
            $provider_image['iDriverId'] = $driverId;
            $provider_image['tAddedDate'] = $provider_image['tModifiedDate'] = $dateTime;
            $id = $obj->MySQLQueryPerform("provider_images", $provider_image, 'insert');
            echo $img;
            exit;

        }
    } else if ($action == "delete" && $providerImgName != "") {

        $providerImgName = json_decode(stripcslashes($providerImgName), true);

        if(empty($providerImgName)){
            $providerImgName = $_REQUEST['imgname'];
        }
        $deleteImg = $obj->sql_query("DELETE FROM provider_images WHERE `vImage`='" . $providerImgName . "'");
        $unlinkFilePath = $img_path . '/' . $providerImgName;
        if ($unlinkFilePath != '' && file_exists($unlinkFilePath)) {
            @unlink($unlinkFilePath);
        }
        echo $langage_lbl['LBL_IMAGE_DELETE_SUCCESS_NOTE'];die;
        //echo "Image Removed Successfully";die;
        /* $whereCondition = "vImage='" . $providerImgName . "'";
          $provider_image = array();
          $provider_image['eStatus'] = $img;
          $provider_image['tModifiedDate'] = $dateTime;
          $id = $obj->MySQLQueryPerform("provider_images", $provider_image, 'update', $whereCondition); */
    }else{
        //echo $langage_lbl['LBL_IMAGE_DELETE_SUCCESS_NOTE'];die;
        echo "Sorry, Image data not found";die;
    }
}
elseif ($_SESSION['sess_user'] == "company") {

    $iCompanyId = $_SESSION['sess_iUserId'];
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $storeImgName = isset($_REQUEST['imgname']) ? trim(str_replace('\"', "", $_REQUEST['imgname'])) : '';
    $img_path = $tconfig["tsite_upload_images_panel"];

    $tbl_name   = 'store_wise_banners';

    $company_data = $obj->MySQLSelect("SELECT iServiceId, vLang, eStatus, eSafetyPractices FROM company WHERE iCompanyId = $iCompanyId");
    $iServiceId = $company_data[0]['iServiceId'] ;
    $vLang = $company_data[0]['vLang'] ;
    $eStatus = $company_data[0]['eStatus'];
    $eSafetyPractices = $company_data[0]['eSafetyPractices'];


    $select_order   = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM ".$tbl_name." WHERE 1=1 AND iCompanyId = ".$iCompanyId);
    $iDisplayOrder  = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
    $iDisplayOrder  = $iDisplayOrder + 1;


    if ($action == "delete" && $storeImgName != "") {
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
        
        echo $langage_lbl['LBL_IMG_DELETE_SUCCESS_NOTE'];
        exit;
    }else{
        echo $langage_lbl['LBL_IMG_DELETE_SUCCESS_NOTE'];
        exit;
    }
}


?>
