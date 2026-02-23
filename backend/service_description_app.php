<?php
include 'common.php';

$lang = isset($_REQUEST['vLang']) ? $_REQUEST['vLang'] : '';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$eForVideoConsultation = isset($_REQUEST['eForVideoConsultation']) ? $_REQUEST['eForVideoConsultation'] : 'No';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

if(empty($lang)) {
	$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
}

if($eForVideoConsultation == "Yes") {
	$video_consult_data = $VIDEO_CONSULT_OBJ->getServiceDetails($iDriverId, $iVehicleTypeId);
	$tTypeDescription = $video_consult_data['eVideoServiceDescription'];
} else {
	$vehicle_type_data = $obj->MySQLSelect("SELECT tTypeDesc FROM `vehicle_type` WHERE `iVehicleTypeId` = '$iVehicleTypeId' ");
	$tTypeDesc = json_decode($vehicle_type_data[0]['tTypeDesc'], true);
	$tTypeDescription = $tTypeDesc['tTypeDesc_' . $lang];
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap" rel="stylesheet">
	<style type="text/css">
		html, body {
			font-family: 'Roboto', sans-serif;
		}

		* { box-sizing: border-box; }

		html, body {
		    margin: 0;
		    padding: 0;
		    border: 0;
		    vertical-align: baseline;
		}

		body {
			background-color: #FFFFFF;
			overflow: hidden;

		}
		img {
		    width: auto;
		}

		.container {
			width: 100%;
		}
	</style>
</head>
<body>
	<div class="container">
		<?= html_entity_decode($tTypeDescription) ?>
	</div>
</body>
</html>