<?php
include_once('../../common.php');

$tbl_name = 'ride_share_driver_fields';

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$iFieldId = isset($_REQUEST['iFieldId']) ? $_REQUEST['iFieldId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iFieldId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-driver-detail-fields-rideshare')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete driver details field.';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iFieldId != "") {
            $fieldIds = $iFieldId;
        } else {
            $fieldIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE $tbl_name SET eStatus = 'Deleted' WHERE iFieldId IN (" . $fieldIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "driver_details_field.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iFieldId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-driver-detail-fields-rideshare')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of driver details field.';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE $tbl_name SET eStatus = '" . $status . "' WHERE iFieldId = '" . $iFieldId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "driver_details_field.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-driver-detail-fields-rideshare')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of package type';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE $tbl_name SET eStatus = '" . $statusVal . "' WHERE iFieldId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "driver_details_field.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>