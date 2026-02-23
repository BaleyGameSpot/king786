<?php
include_once('common.php');
	
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : '';
$isRatinaDisplay = isset($_REQUEST['isRatinaDisplay']) ? $_REQUEST['isRatinaDisplay'] : '';
if(!empty($isRatinaDisplay)) {
	$_COOKIE['isRatinaDisplay'] = $isRatinaDisplay;
	echo "success";
	exit;
}

if($userType == 'rider'){
	$table = "register_user";
}else{
	$table = "register_driver";
}

if(isset($_REQUEST['vPhone']))
{
		$vPhone=$_REQUEST['vPhone'];
		$sql = "SELECT vPhone FROM $table WHERE (vPhone = '".$vPhone."' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($vPhone, '0')."')  AND eStatus != 'Deleted'";
		$db_comp = $obj->MySQLSelect($sql);
		
	if(scount($db_comp)>0) {
		echo 'false';
	} else {	
		echo 'true';
	}
	exit;
}
?>