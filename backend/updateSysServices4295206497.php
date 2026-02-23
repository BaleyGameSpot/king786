<?php
include 'common.php';

$MEDIA_SERVER = isset($_REQUEST['MEDIA_SERVER']) ? $_REQUEST['MEDIA_SERVER'] : 'No';
$options = array(
	'MEDIA_SERVER' => $MEDIA_SERVER
);
if(strtoupper($MAINTENANCE_WEBSITE) == "NO") {
	$OPTIMIZE_DATA_OBJ->RebuildMongoData($options);	
}

echo "Success";
?>