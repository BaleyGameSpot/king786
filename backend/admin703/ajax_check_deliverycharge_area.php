<?php
include_once('../common.php');




$iLocationId = isset($_REQUEST['iLocationId']) ? $_REQUEST['iLocationId'] : '';
$deliverycharge_id = isset($_REQUEST['deliverycharge_id']) ? $_REQUEST['deliverycharge_id'] : ''; 
if($iLocationId != "" && empty($deliverycharge_id)) {
	$sql="SELECT count(iDeliveyChargeId) as totalselectedarea FROM delivery_charges WHERE iLocationId ='".$iLocationId."'";
	$data = $obj->MySQLSelect($sql);
	echo $data[0]['totalselectedarea'];
}

if($iLocationId != "" && $deliverycharge_id != "") {
	$sql="SELECT count(iDeliveyChargeId) as totalselectedarea FROM delivery_charges WHERE iLocationId ='".$iLocationId."' AND iDeliveyChargeId != '".$deliverycharge_id."'";
	$data = $obj->MySQLSelect($sql);
	echo $data[0]['totalselectedarea'];
}
?>