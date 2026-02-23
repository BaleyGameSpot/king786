<?php

include_once('common.php');


$MessageCode = isset($_REQUEST['MessageCode']) ? $_REQUEST['MessageCode'] : '';
$Message = isset($_REQUEST['Message']) ? $_REQUEST['Message'] : '';
$MessageType = isset($_REQUEST['MessageType']) ? $_REQUEST['MessageType'] : '';


if(isset($Message) && !empty($Message) ){

    if($MessageType == "LBL"){
        $Message = $langage_lbl[$Message];
    }

    $_SESSION['message_code'] = $MessageCode;
    $_SESSION['session_msg'] = $Message;
    exit;

}

?>