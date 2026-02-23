<?php
include_once('../../common.php');
$date = Date('Y-m-d');
$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if ($method == 'delete' && $iCompanyId != '') {
    if (!$userObj->hasPermission('delete-company-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete company';
    } else {
        if (SITE_TYPE != 'Demo') {

            $qur2 = "UPDATE track_service_company SET eStatus = 'Deleted' WHERE iTrackServiceCompanyId  = '" . $iCompanyId . "'";
            $res2 = $obj->sql_query($qur2);

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_company.php?" . $parameters);
    exit;
}
if ($iCompanyId != '' && $status == 'Active') {
    if (!$userObj->hasPermission('update-status-company-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of company';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE track_service_company SET eStatus = '" . $status . "' WHERE iTrackServiceCompanyId  = '" . $iCompanyId . "'";
            $obj->sql_query($query);

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_company.php?" . $parameters);
    exit;
}

if ($iCompanyId != '' && $status == 'Inactive') {
    if (!$userObj->hasPermission('update-status-company-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of company';
    } else {
        if (SITE_TYPE != 'Demo') {

                $query = "UPDATE track_service_company SET eStatus = '" . $status . "' WHERE iTrackServiceCompanyId  = '" . $iCompanyId . "'";
                $obj->sql_query($query);

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_company.php?" . $parameters);
    exit;
}

if($checkbox != "" && $statusVal != "") {
    if(!$userObj->hasPermission(['update-status-company-trackservice', 'delete-company-trackservice'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of company';
    }else{
        if(SITE_TYPE !='Demo'){
            $query = "UPDATE track_service_company SET eStatus = '" . $statusVal . "' WHERE iTrackServiceCompanyId IN (" . $checkbox . ")";
            $obj->sql_query($query);



            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        else{
            $_SESSION['success']=2;
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."company.php?".$parameters);
    exit;
}
?>