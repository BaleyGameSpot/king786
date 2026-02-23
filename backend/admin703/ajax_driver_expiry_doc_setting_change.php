<?php
include_once("../common.php");




$ckVal = isset($_REQUEST['ckVal']) ? $_REQUEST['ckVal'] : '';


$ckVal = $ckVal == 'true' ? 'Yes' : 'No';

$sql1 = "UPDATE configurations SET vValue = '".$ckVal."' WHERE vName = 'SET_DRIVER_OFFLINE_AS_DOC_EXPIRED'";
$db_company = $obj->sql_query($sql1);

if($db_company){
    echo 'Setting Updated.'; 
}else{
    echo 'Something went wrong.';
}

?>
