<?php
include_once('../../common.php');
$AUTH_OBJ->checkMemberAuthentication();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iRentalPackageId = isset($_REQUEST['iRentalPackageId']) ? $_REQUEST['iRentalPackageId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';

//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iRentalPackageId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-rental-intercity-packages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete InterCity package';
    } else {
        if ($iRentalPackageId != "") {
            $typeIds = $iRentalPackageId;
        } else {
            $typeIds = $checkbox;
        }
        if (SITE_TYPE != 'Demo') {
            $query = "DELETE FROM rental_package WHERE iRentalPackageId IN (" . $typeIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."intercity_package.php?id=".$id."&".$parameters); exit;
}
//End make deleted
?>