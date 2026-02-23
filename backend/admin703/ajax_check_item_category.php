<?php
include_once('../common.php');





$iFoodMenuId = isset($_REQUEST['iFoodMenuId']) ? $_REQUEST['iFoodMenuId'] : '';

if(!empty($iFoodMenuId)){
	$db_food_menu = $obj->MySQLSelect("SELECT iServiceId FROM `food_menu` WHERE iFoodMenuId = '$iFoodMenuId'");
	echo $db_food_menu[0]['iServiceId'];
} else {
	echo "0"; exit;
}		
?>
