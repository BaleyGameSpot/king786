<?php
include_once('../common.php');
	


	
	$vEmail=isset($_REQUEST['vEmail'])?$_REQUEST['vEmail']:'';
	$sql1 = "SELECT eStatus FROM register_user WHERE vEmail = '".$vEmail."'";
	$db_user = $obj->MySQLSelect($sql1);
	echo $db_user[0]['eStatus']; exit;
?>