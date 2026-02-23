<?php
include_once '../common.php';
$FILEARRAY = ChangeFileCls::fileArray($SOURCE_FILE = "SOURCE_FILE");
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';

if($eType == "Ride") {
    include_once($FILEARRAY['MASTER_CATEGORY_RIDE']);
}

if($eType == "VideoConsult") {
    include_once($FILEARRAY['MASTER_CATEGORY_VIDEO-CONSULT']);
}

if($eType == "UberX") {
    include_once($FILEARRAY['MASTER_CATEGORY_UBERX']);
}

if($eType == "DeliverAll") {
    include_once($FILEARRAY['MASTER_CATEGORY_DELIVERALL']);
}
exit;
?>