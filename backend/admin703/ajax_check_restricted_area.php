<?php
include_once('../common.php');




$iLocationId = isset($_REQUEST['iLocationId']) ? $_REQUEST['iLocationId'] : '';
$restricted_id = isset($_REQUEST['restricted_id']) ? $_REQUEST['restricted_id'] : ''; 
if($iLocationId != "" && empty($restricted_id)) {
	$sql="SELECT count(iRestrictedNegativeId) as totalrestrictarea FROM restricted_negative_area WHERE iLocationId ='".$iLocationId."'";
	$data = $obj->MySQLSelect($sql);
	echo $data[0]['totalrestrictarea'];
}

if($iLocationId != "" && $restricted_id != "") {
	$sql="SELECT count(iRestrictedNegativeId) as totalrestrictarea FROM restricted_negative_area WHERE iLocationId ='".$iLocationId."' AND iRestrictedNegativeId != '".$restricted_id."'";
	$data = $obj->MySQLSelect($sql);
	echo $data[0]['totalrestrictarea'];
}
?>