<?php
include_once('../../common.php');

$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$TravelPreferencesId = isset($_REQUEST['TravelPreferencesId']) ? $_REQUEST['TravelPreferencesId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if ($method == 'delete' && $TravelPreferencesId != '') {
    if(!$userObj->hasPermission('delete-faq')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete FAQ';
    }else{
        if(SITE_TYPE !='Demo')
        {
            $query = "UPDATE travel_preferences SET eStatus = 'Deleted' WHERE TravelPreferencesId = '" . $TravelPreferencesId . "'";
            $obj->sql_query($query);

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        }
        else{
            $_SESSION['success'] = '2';
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."travel_preferences.php?".$parameters); exit;
}

if ($TravelPreferencesId != '' && $status != '') {
    if(!$userObj->hasPermission('update-status-faq')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of FAQ';
    }else
    {
        if(SITE_TYPE !='Demo'){
            $query = "UPDATE travel_preferences SET eStatus = '" . $status . "' WHERE TravelPreferencesId = '" . $TravelPreferencesId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if($status == 'Active') {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
            }else {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            }
        }
        else{
            $_SESSION['success']=2;
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."travel_preferences.php?".$parameters);
    exit;
}