<?php
include 'common.php';

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

if($type == "phone") {
	$db_data = $obj->MySQLSelect("SELECT vCountryCode as Code from country where vPhoneCode = '" . $id . "'");
}
else {
	$db_data = $obj->MySQLSelect("SELECT vPhoneCode as Code from country where vCountryCode = '" . $id . "'");	
}

if(!empty($db_data) && scount($db_data) > 0) {
	echo $db_data[0]['Code'];	
}

exit;
?>