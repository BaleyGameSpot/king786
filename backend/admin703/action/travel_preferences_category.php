<?php
include_once('../../common.php');

$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$TravelPreferencesCategoryId = isset($_REQUEST['iTravelPreferencesCategoryId']) ? $_REQUEST['iTravelPreferencesCategoryId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if ($method == 'delete' && $TravelPreferencesCategoryId != '') {
    if(!$userObj->hasPermission('delete-travel-preferences-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete Travel Preferences';
    }else{
        if(SITE_TYPE !='Demo')
        {
            $selTravelPrefData = $obj->MySQLSelect("SELECT TravelPreferencesId FROM travel_preferences WHERE iTravelPreferencesCategoryId ='" . $TravelPreferencesCategoryId . "'");
           
            if(scount($selTravelPrefData) > 0){
                $optionsarray= array();
                foreach ($selTravelPrefData as $selTravelkey => $selTravelvalue) {
                    $optionsarray[] = implode(",",$selTravelvalue);
                }
                $optionsstring = implode(",", $optionsarray);
                $query1 = "UPDATE travel_preferences SET eStatus = 'Deleted' WHERE TravelPreferencesId IN (" . $optionsstring . ")";
                $obj->sql_query($query1);
            }

            $query = "UPDATE travel_preferences_category SET eStatus = 'Deleted' WHERE iTravelPreferencesCategoryId = '" . $TravelPreferencesCategoryId . "'";
            $obj->sql_query($query);

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        }
        else{
            $_SESSION['success'] = '2';
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."travel_preferences_category.php?".$parameters); exit;
}

if ($TravelPreferencesCategoryId != '' && $status != '') {
    if(!$userObj->hasPermission('update-status-travel-preferences-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of Travel Preferences';
    } else {
        if(SITE_TYPE !='Demo'){
            $query = "UPDATE travel_preferences_category SET eStatus = '" . $status . "' WHERE iTravelPreferencesCategoryId = '" . $TravelPreferencesCategoryId . "'";
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
    header("Location:".$tconfig["tsite_url_main_admin"]."travel_preferences_category.php?".$parameters);
    exit;
}