<?php
include_once 'common.php';

if(isset($_POST['cbIcuKhGdR_XXXXXXXX']) && strtoupper($_POST['cbIcuKhGdR_XXXXXXXX']) == "YES") {
	$tokens = $AUTH_MEMBER_OBJ->initAuth();
}

?>