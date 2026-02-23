<?php
include_once('../common.php');
	



$vCountryCode = isset($_REQUEST['vCountryCode']) ? $_REQUEST['vCountryCode'] : '';
if($vCountryCode != "") {
	$sql="SELECT eEnableToll,iCountryId FROM  `country` WHERE vCountryCode = '".$vCountryCode."'";
	$data = $obj->MySQLSelect($sql);
	echo $data[0]['eEnableToll'];exit;
}

?>