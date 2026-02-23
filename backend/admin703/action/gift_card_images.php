<?php
include_once('../../common.php');
$AUTH_OBJ->checkMemberAuthentication();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$iGiftCardImageId = isset($_REQUEST['iGiftCardImageId']) ? $_REQUEST['iGiftCardImageId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if ($iGiftCardImageId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-giftcard-image')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE gift_card_images SET eStatus = '" . $status . "' WHERE iGiftCardImageId = '" . $iGiftCardImageId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if ($status == 'Active') {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
            } else {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }

    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
    }

    updateSystemData();
    header("Location:" . $tconfig["tsite_url_main_admin"] . "gift_card_images.php?" . $parameters);
    exit;
}

if ($method == 'delete' && $iGiftCardImageId != '') {
    if (!$userObj->hasPermission('update-status-giftcard-image')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete gift card images';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE gift_card_images SET eStatus = 'Deleted' WHERE iGiftCardImageId = '" . $iGiftCardImageId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        }
    }

    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
    }

    updateSystemData();

    header("Location:" . $tconfig["tsite_url_main_admin"] . "gift_card_images.php?" . $parameters);
    exit;
}
?>