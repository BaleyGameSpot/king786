<?php

include_once '../../common.php';
global $userObj;

$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
$date = Date('Y-m-d');
$AUTH_OBJ->checkMemberAuthentication();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iMongoName = isset($_REQUEST['iMongoName']) ? $_REQUEST['iMongoName'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$DbName = TSITE_DB;
$TableName = "auth_master_accounts_places";
$uniqueFieldName = "vServiceName";
$uniqueFieldValue = $iMongoName;
$tempData['eStatus'] = $status;

if ($status == 'Active') {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_ACTIVATE_MSG"];
} elseif ($status == 'Inactive') {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_INACTIVATE_MSG"];
} else {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_DELETE_MSG"];
}

if ($checkbox != '') {
    if ($statusVal != '') {
        $tempData['eStatus'] = $statusVal;
        $checkbox = explode(",", $checkbox);
        for ($i = 0; $i < scount($checkbox); $i++) {
            if($statusVal != 'Delete') {
                $updated = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableName, $uniqueFieldName, $checkbox[$i], $tempData);    
            }
            else {
                $obj->deleteRecordsFromMongoDB($DbName, $TableName, [$uniqueFieldName => $checkbox[$i]]);
            }
        }

        $OPTIMIZE_DATA_OBJ->updateAppService();
        header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_setting.php?" . $parameters);
        exit;
    }
} else {
    if ($uniqueFieldValue != '') {
        if($status != "Delete") {
            $updated = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);

            $OPTIMIZE_DATA_OBJ->updateAppService();
            header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_setting.php?" . $parameters);
            exit;    
        }
        else {
            $obj->deleteRecordsFromMongoDB($DbName, $TableName, [$uniqueFieldName => $uniqueFieldValue]);

            $OPTIMIZE_DATA_OBJ->updateAppService();
            header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_setting.php?" . $parameters);
            exit;  
        }
    }
}
