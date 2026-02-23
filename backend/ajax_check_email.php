<?php
	
	include_once('common.php');
	
	$email = $_POST['emailNs'];

	$email = "SELECT * FROM `newsletter` WHERE eStatus = 'subscribe' && vEmail = '".$email."' ";
	$data = $obj->MySQLSelect($email);
	
	if(scount($data) >= 1){
		echo 'true';
	}

?>