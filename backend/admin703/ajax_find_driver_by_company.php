<?php
include_once("../common.php");


$company_id = isset($_REQUEST['company_id'])?$_REQUEST['company_id']:'';
if(isset($company_id) && !empty($company_id)) {
	$sql = 	"select iDriverId,vEmail,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted' AND iCompanyId = '".$company_id."' ";
	$db_drivers = $obj->MySQLSelect($sql);
	echo "<option value=''>Select ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."</option>";
	foreach($db_drivers as $dbd) { 
	   echo "<option value='".$dbd["iDriverId"]."'>".clearName($dbd['driverName']). " - ( " .$dbd['vEmail']." ) </option>";
	}
} else {
	$sql = 	"select iDriverId,vEmail,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted'";
	$db_drivers = $obj->MySQLSelect($sql);
	echo "<option value=''>Select ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."</option>";
	foreach($db_drivers as $dbd) { 
		echo "<option value='".$dbd["iDriverId"]."'>".clearName($dbd['driverName']). " - ( " .$dbd['vEmail']." )</option>";
	}
}