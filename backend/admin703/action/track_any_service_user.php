<?php
include_once('../../common.php');
$AUTH_OBJ->checkMemberAuthentication();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$iTrackServiceUserId = isset($_REQUEST['iTrackServiceUserId']) ? $_REQUEST['iTrackServiceUserId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';

if ($method == 'delete' && $iTrackServiceUserId != '') {
    if (!$userObj->hasPermission('delete-users-trackanyservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE track_service_users SET eStatus = 'Deleted' WHERE iTrackServiceUserId = '" . $iTrackServiceUserId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        }
    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_any_service_user.php?" . $parameters);
    exit;
}
if ($iTrackServiceUserId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-users-trackanyservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE track_service_users SET eStatus = '" . $status . "' WHERE iTrackServiceUserId = '" . $iTrackServiceUserId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_any_service_user.php?" . $parameters);
    exit;
}

if ($checkbox != "" && $statusVal != "") {
  
    if (!$userObj->hasPermission(['update-status-users-trackanyservice', 'delete-users-trackanyservice'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE track_service_users SET eStatus = '" . $statusVal . "' WHERE iTrackServiceUserId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $explodeId = explode(",", $checkbox);
            for ($i = 0; $i < scount($explodeId); $i++) {
                if ($hardDelete == 1) {
                    removedRiderData($explodeId[$i]);
                }
            }
            $_SESSION['success'] = '1';

            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_any_service_user.php?" . $parameters);
    exit;
}

?>
