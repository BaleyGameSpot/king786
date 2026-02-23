<?php
include_once '../common.php';

$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';

$service_safety_data = $obj->MySQLSelect("SELECT ssm.*, CONCAT(ru.vName, ' ', ru.vLastName) as user_name, CONCAT(rd.vName, ' ', rd.vLastName) as driver_name,tr.vTimeZone FROM service_safety_media as ssm LEFT JOIN register_user as ru ON (ssm.eUserType = 'Passenger' AND ru.iUserId = ssm.iMemberId) LEFT JOIN register_driver as rd ON (ssm.eUserType = 'Driver' AND rd.iDriverId = ssm.iMemberId)  LEFT JOIN trips as tr ON tr.iTripId = ssm.iTripId WHERE ssm.iTripId = '$iTripId'");

$response_html = "";
$systemTimeZone = date_default_timezone_get();
foreach ($service_safety_data as $media) {
	if($media['eUserType'] == "Driver") {
		$userTypeTxt = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
		$member_name = 	$media['driver_name'];
	} else {
		$userTypeTxt = $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];
		$member_name = 	$media['user_name'];
	}
	
	/*$date = date('d-m-Y', strtotime($media['dAddedDate'])) . '<br>' . date('h:i A', strtotime($media['dAddedDate']));
	if(strtoupper($ENABLE_24_HOUR_FORMAT) == "YES") {
		$date = date('d-m-Y', strtotime($media['dAddedDate'])) . '<br>' . date('H:i', strtotime($media['dAddedDate']));
	}*/

	 $date_format_data_array = array(
        'tdate' => converToTz($media['dAddedDate'], $media['vTimeZone'], $systemTimeZone),
        'langCode' => $default_lang,
        'DateFormatForWeb' => 1
    );
    $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);

	$tMessage = (!empty($media['tMessage'])) ? $media['tMessage'] : "--";

	$audio_url = $tconfig["tsite_upload_service_safety_media"] . '/' . $media['vFileName'];
	$response_html .= '<tr>';
	$response_html .= '<td>' . $member_name . ' (' . $userTypeTxt . ')</td>';
	$response_html .= '<td>' . $tMessage . '</td>';
	$response_html .= '<td style="white-space: nowrap">' . $get_date_format['tDisplayDateTime']. '</td>';
	$response_html .= '<td><audio controls controlslist="noplaybackrate"><source src="' . $audio_url . '" type="audio/wav"></audio></td>';
	$response_html .= '</tr>';
}

echo $response_html;

?>