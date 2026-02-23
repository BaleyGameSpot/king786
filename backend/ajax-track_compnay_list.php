<?php
include_once('common.php');
$tracking_company_trip_user_list = isset($_REQUEST['tracking_company_trip_user_list']) ? $_REQUEST['tracking_company_trip_user_list'] : '';
$iTrackServiceTripId = isset($_REQUEST['tripId']) ? $_REQUEST['tripId'] : '';
if (!empty($tracking_company_trip_user_list)) {
    // it takes list of driver how many driver have cancel order
    $sql = "SELECT iUserIds FROM track_service_trips  WHERE iTrackServiceTripId = " . $iTrackServiceTripId;
    $iUserIds = $obj->MySQLSelect($sql);
    $iUserIds = $iUserIds[0]['iUserIds'];
    $sql = "SELECT * FROM track_service_users  WHERE iTrackServiceUserId IN ($iUserIds)";
    $iUserData = $obj->MySQLSelect($sql);
    if (!empty($iUserData)) {
        $driverList = "";
        $driverList .= " <table class='table table-bordered track-company' width = '100%' align = 'center' > ";
        $driverList .= "<tr> ";
        $driverList .= "<td> " . $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER'] . " Name </td ><td > E - mail</td ><td width = '30%' > Phone number </td > ";
        $driverList .= "</tr > ";
        foreach ($iUserData as $key => $value) {
            $driverList .= "<tr > ";
            $driverList .= "<td > " . clearName($value['vName'] . ' ' . $value['vLastName']) . " </td ><td > " . clearEmail($value['vEmail']) . " </td ><td > " .'+' . $value['vPhoneCode'] . clearPhone($value['vPhone']) . " </td > ";
            $driverList .= "</tr > ";
        }
        $driverList .= "</table > ";
        $returnData['Action'] = 1;
        $returnData['message'] = $driverList;
        echo json_encode($returnData);
        exit;
    } else {
        $returnData['Action'] = 0;
        $returnData['message'] = " <h1>" . $langage_lbl['LBL_NO_USER_FOUND'] . " </h1> ";
        echo json_encode($returnData);
        exit;
    }
}
?>