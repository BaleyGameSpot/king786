<?php
include_once("common.php");
    $sessionUserId = $_SESSION['sess_iUserId'];
    $getDriverData = $obj->MySQLSelect("SELECT vImage,eStatus,vName,vLastName FROM register_driver WHERE iDriverId = '" . $sessionUserId . "'");

    $OldImageName = $getDriverData[0]['vImage'];
    $checkEditProfileStatus = getEditDriverProfileStatus($getDriverData[0]['eStatus']);
    $profileImgpath = $tconfig["tsite_upload_images_driver_path"];
    if(file_exists($profileImgpath . '/' . $sessionUserId . '/2_' . $OldImageName) && $checkEditProfileStatus == "No") {
        //$var_msg = $langage_lbl['LBL_EDIT_PROFILE_DISABLED'];
        /* header("location:profile.php?success=0"."&var_msg=" . $var_msg);
        exit; */
        echo $var_msg = '0';
        return $var_msg;
    }
    
?>