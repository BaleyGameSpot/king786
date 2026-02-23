<?php
include_once('../../common.php');

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iItemSizeCategoryId = isset($_REQUEST['iItemSizeCategoryId']) ? $_REQUEST['iItemSizeCategoryId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$tablename = "parcel_delivery_items_size_info";

//Start Change single Status
if ($iItemSizeCategoryId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-item-size-parcel-delivery')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status.';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE $tablename SET eStatus = '" . $status . "' WHERE iItemSizeCategoryId = '" . $iItemSizeCategoryId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "parcel_delivery_items_size.php?" . $parameters);
    exit;
}
//End Change single Status
?>