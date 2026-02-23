<?php
include_once('../../common.php');
$AUTH_OBJ->checkMemberAuthentication();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iVehicleSizeId = isset($_REQUEST['iVehicleSizeId']) ? $_REQUEST['iVehicleSizeId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$edit = "edit-vehicle-size-info";
//Start Change single Status
if ($iVehicleSizeId != '' && $status != '') {
    if (!$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of Vehicle Size';
    } else {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE vehicle_size_info SET eStatus = '" . $status . "' WHERE iVehicleSizeId = '" . $iVehicleSizeId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicle_size_info.php?" . $parameters);
    exit;
}
//End Change single Status
?>