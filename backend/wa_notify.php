<?php
if(isset($_GET['hub_challenge'])) {
	echo $_GET['hub_challenge'];
	exit;
}

include 'common.php';

$payload = @file_get_contents('php://input');
$notifyData = json_decode($payload, true);

if($notifyData['object'] == "whatsapp_business_account") {
	$WA_OPS_OBJ->updateMessageTemplate($notifyData);
}
?>