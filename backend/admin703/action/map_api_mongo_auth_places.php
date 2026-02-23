<?php
include_once '../../common.php';
global $userObj;
?>
<script src="<?php echo $tconfig['tsite_url'] ?>assets/plugins/jquery-2.0.3.min.js"></script>
<?php

$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
$date = Date('Y-m-d');

$AUTH_OBJ->checkMemberAuthentication();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iOid = isset($_REQUEST['iOid']) ? $_REQUEST['iOid'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';



$DbName = TSITE_DB;
$TableName = "auth_accounts_places";

$uniqueFieldName = '_id';
$uniqueFieldValue = trim($iOid);
// $tempData['eStatus'] = $status;

if ($status == 'Active') {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_ACTIVATE_MSG"];
} else {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_INACTIVATE_MSG"];
}

$searchQuerySid['vServiceId'] = $id;
$AuthMaster_table = "auth_master_accounts_places";
$TableNameAuthMaster = "auth_master_accounts_places";
$requiredServicesAry = array("Geocoding", "AutoComplete", "Direction");
$activeRecordsResult = $obj->fetchAllCollectionFromMongoDB($DbName, $TableNameAuthMaster);

foreach ($activeRecordsResult as $key => $activeRecordsResult) {
    if ($activeRecordsResult['eStatus'] == "Active") {
        $AllactiveServices[$key + 1] = $activeRecordsResult['vActiveServices'];
    }
}
unset($AllactiveServices[$id]);
$AllactiveServices = array_values($AllactiveServices);
for ($i = 0; $i <= scount($AllactiveServices); $i++) {
    $explodeData = explode(",", $AllactiveServices[$i]);
    foreach ($explodeData as $Row) {
        if ($Row != '') {
            $RowAry[] = $Row;
        }
    }
}
$result = array_diff($requiredServicesAry, $RowAry);


$data_by_serviceID = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $AuthMaster_table, $searchQuerySid);

if ($method == "delete") {
    $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, ['vServiceId' => intval($id)]);
    $vUsageOrderArr = array_column($data_drv, 'vUsageOrder');
    rsort($vUsageOrderArr);
    $max_usage_order = $vUsageOrderArr[0] + 1;

	if($data_by_serviceID[0]['eStatus'] == "Active"){
		$activeoids = [];
			$serchactiveData['eStatus'] = "Active";
			$serchactiveData['vServiceId'] = intVal($id);
			$serchactiveDataAry = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $serchactiveData);
			foreach($serchactiveDataAry as $valAry){
				$activeoids[]=$valAry['_id']['$oid'];
			}
			$remove_account_count = 0;
			if (in_array($iOid, $activeoids)) {
				$remove_account_count = 1;
			}
			$active_accounts = scount($activeoids);
		if (($active_accounts - $remove_account_count) < 1) {
			confirmToDelete($result,$id,$tconfig["tsite_url_main_admin"],$iOid);
		}else{
			
			$DbName = TSITE_DB;
			$TableName = "auth_accounts_places";
			$searchQuery = [];
			if ($iOid != '') {
				$searchQuery['_id'] = new MongoDB\BSON\ObjectID($iOid);
			}
			$deleted = $obj->deleteRecordsFromMongoDB($DbName, $TableName, $searchQuery);
			
            $Data_insert = array();
            $Data_insert['vTitle'] = "Key " . ($max_usage_order + 1);
            $Data_insert['vServiceId'] = intval($id);
            $Data_insert['auth_key'] = $GOOGLE_SEVER_GCM_API_KEY;
            $Data_insert['auth_key_inactive'] = "";
            $Data_insert['vUsageOrder'] = intval($max_usage_order + 1);
            $Data_insert['eStatus'] = "Active";
            $Data_insert['eDefault'] = "Yes";

            $obj->insertRecordsToMongoDBWithDBName(TSITE_DB, "auth_accounts_places", $Data_insert);

			header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?id=" . $id);
			exit;
			
		}
	}

    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $searchQuery = [];
    if ($iOid != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($iOid);
    }
    $deleted = $obj->deleteRecordsFromMongoDB($DbName, $TableName, $searchQuery);

    $Data_insert = array();
    $Data_insert['vTitle'] = "Key " . ($max_usage_order + 1);
    $Data_insert['vServiceId'] = intval($id);
    $Data_insert['auth_key'] = $GOOGLE_SEVER_GCM_API_KEY;
    $Data_insert['auth_key_inactive'] = "";
    $Data_insert['vUsageOrder'] = intval($max_usage_order + 1);
    $Data_insert['eStatus'] = "Active";
    $Data_insert['eDefault'] = "Yes";

    $obj->insertRecordsToMongoDBWithDBName(TSITE_DB, "auth_accounts_places", $Data_insert);

    $OPTIMIZE_DATA_OBJ->updateAppService();
    header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
    exit;
}

if ((($statusVal == "Inactive") || ($status == "Inactive")) && ($data_by_serviceID[0]['eStatus'] == "Active")) {
    // if ($data_by_serviceID[0]['eStatus'] == "Active") {
    $curr_record = array();

	$remove_account_count = 0;
	$serchactiveData['eStatus'] = "Active";
	$serchactiveData['vServiceId'] = intVal($id);
	$serchactiveDataAry = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $serchactiveData);
	foreach($serchactiveDataAry as $valAry){
		$activeoids[] = $valAry['_id']['$oid'];

        if($valAry['_id']['$oid'] == $iOid) {
            $curr_record = $valAry;
        }
	}

	if (in_array($iOid, $activeoids)) {
		$remove_account_count = 1;
	}    
	
    if ($checkbox != '') {
        $checkboxExplode = explode(",", $checkbox);
        $remove_account_count = scount($checkboxExplode);
    }
    $serchactiveData['eStatus'] = "Active";
    $serchactiveData['vServiceId'] = intVal($id);
    $serchactiveDataAry = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $serchactiveData);

    $active_accounts = scount($serchactiveDataAry);
    $redirect = $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters;
    // $active_accounts = 1;
    if (($active_accounts - $remove_account_count) < 1) {
        $info = [];
        $info['changestatusbyajx'] = "Y";
        $info['checkbox'] = $checkbox;
        $info['parameters'] = $parameters;
        $info['ioid'] = $iOid;
        $info['vServiceId'] = intVal($id);
        
        $json_format = json_encode($info);
        echo "<script language='JavaScript' type='text/javascript' >
        function goback(){
            window.location.href ='$redirect';
        }
        var countResult = " . scount($result) . ";
            if (confirm('Your service will be inactive. Do you like to inactive service?')) {
                if(countResult > 0){
                    alert('Keep atleast one service active.');
                    goback();
                }else{
                    $.ajax({
                            type: 'POST',
                            data: {info:'$json_format'},
                            url: 'map_api_mongo_auth_places_ajax.php',
                            success: function(msg){
                                window.location.href ='$redirect';
                            }
                        });
            }
        }else{
            window.location.href ='$redirect';
        }
        </script>";
    } else {
        if ($checkbox != '') {
            if ($statusVal != '') {
                $tempData['eStatus'] = $statusVal;
                $checkbox = explode(",", $checkbox);
                for ($i = 0; $i < scount($checkbox); $i++) {
                    $updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $checkbox[$i], $tempData);
                }

                $OPTIMIZE_DATA_OBJ->updateAppService();
                header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
                exit;
            }
        } else {
            if($status == "Inactive") {
                $tempData["auth_key_inactive"] = $curr_record['auth_key'];
                $tempData["auth_key"] = $GOOGLE_SEVER_GCM_API_KEY;
            } else {
                if(!empty($curr_record['auth_key_inactive'])) {
                    $tempData["auth_key"] = $curr_record['auth_key_inactive'];
                }
                $tempData["auth_key_inactive"] = "";
            }

            if ($uniqueFieldValue != '') {
                $updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);

                $OPTIMIZE_DATA_OBJ->updateAppService();
                header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
                exit;
            }
        }
    }
    // }
} else {
    if ($checkbox != '') {
        if ($statusVal != '') {
            $tempData['eStatus'] = $statusVal;
            $checkbox = explode(",", $checkbox);
            for ($i = 0; $i < scount($checkbox); $i++) {
                $updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $checkbox[$i], $tempData);
            }

            $OPTIMIZE_DATA_OBJ->updateAppService();
            header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
            exit;
        }
    } else {
        $curr_record = $serchactiveData = array();

        $serchactiveData['vServiceId'] = intVal($id);
        $serchactiveDataAry = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $serchactiveData);
        foreach($serchactiveDataAry as $valAry){

            if($valAry['_id']['$oid'] == $iOid) {
                $curr_record = $valAry;
            }
        }

        if($status == "Inactive") {
            $tempData["auth_key_inactive"] = $curr_record['auth_key'];
            $tempData["auth_key"] = $GOOGLE_SEVER_GCM_API_KEY;
        } else {
            if(!empty($curr_record['auth_key_inactive'])) {
                $tempData["auth_key"] = $curr_record['auth_key_inactive'];
            }
            $tempData["auth_key_inactive"] = "";
        }

        if ($uniqueFieldValue != '') {
            $updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);
            
            $OPTIMIZE_DATA_OBJ->updateAppService();
            header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
            exit;
        }
    }
}
function confirmToDelete($result,$id,$adminUrl,$iOid){
	$redirect2 = $adminUrl . "map_api_mongo_auth_places.php?id=".$id."";
	$info = [];
        $info['changestatusbyajx'] = "Y";
        $info['deleteme'] = 'Y';
        $info['parameters'] = $parameters;
        $info['ioid'] = $iOid;
        $info['vServiceId'] = intVal($id);
        
        $json_format = json_encode($info);
        echo "<script language='JavaScript' type='text/javascript' >
		
        function gobackme(){
            window.location.href ='$redirect2';
        }
        var countResult = " . scount($result) . ";
		
            if (confirm('Your service will be inactive. Do you like to inactive service?')) {
                if(countResult > 0){
                    alert('Keep atleast one service active.');
                    gobackme();
                }else{
                    $.ajax({
                            type: 'POST',
                            data: {info:'$json_format'},
                            url: 'map_api_mongo_auth_places_ajax.php',
                            success: function(msg){
								alert(msg);
                                window.location.href ='$redirect2';
                            }
                        });
            }
        }else{
            window.location.href ='$redirect2';
        }
        </script>";
		exit;
}
