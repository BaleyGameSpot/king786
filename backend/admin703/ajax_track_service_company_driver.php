<?php
include_once('../common.php');
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$module = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';

if ($module == 'track_service') {
    /*$sql = "SELECT CONCAT(rd.vName ,' ',rd.vLastName ) as driverName, rd.iDriverId FROM register_driver rd WHERE  rd.iTrackServiceCompanyId = $iCompanyId";
    $driver = $obj->MySQLSelect($sql);

    $company_option_html .= "<option value=''>Select Driver</option>";
    if (scount($driver) > 0) {
        for ($i = 0; $i < scount($driver); $i++) {

            $selected = '';
            if ($driver[$i]['iDriverId'] == $iDriverId) {
                $selected = "selected=selected";
            }

            $company_option_html .= "<option value=" . $driver[$i]['iDriverId'] . " " . $disabled . " " . $selected . ">" . clearName($driver[$i]['driverName']) . " </option>";
        }
    }*/

    $db_vehicles = $obj->MySQLSelect("SELECT dv.iDriverVehicleId, dv.vLicencePlate, dv.iDriverId, m.vMake, md.vTitle, CONCAT(rd.vName,' ',rd.vLastName) AS driverName FROM driver_vehicle as dv LEFT JOIN register_driver as rd ON rd.iDriverVehicleId = dv.iDriverVehicleId LEFT JOIN make as m ON m.iMakeId = dv.iMakeId LEFT JOIN model as md ON md.iModelId = dv.iModelId WHERE rd.iTrackServiceCompanyId = '" . $iCompanyId . "' AND dv.eStatus = 'Active' AND rd.eStatus = 'active' ");

    $company_option_html .= "<option value=''>Select Vehicle</option>";
    if (scount($db_vehicles) > 0) {
        for ($i = 0; $i < scount($db_vehicles); $i++) {

            $selected = '';
            if ($db_vehicles[$i]['iDriverId'] == $iDriverId) {
                $selected = "selected=selected";
            }

            $company_option_html .= "<option value=" . $db_vehicles[$i]['iDriverId'] . " " . $disabled . " " . $selected . ">" . $db_vehicles[$i]['vMake'] . ' ' . $db_vehicles[$i]['vTitle'] . ' (' . $db_vehicles[$i]['vLicencePlate'] . ')' . " </option>";
        }
    }

    $arr['company_option_html'] = $company_option_html;
}

if($module == 'track_service_send_invite_code'){
    $iTrackServiceUserId = isset($_REQUEST['iTrackServiceUserId']) ? $_REQUEST['iTrackServiceUserId'] : '';
    $sql = "SELECT vInviteCode,vLastName,vName,vPhone,vPhoneCode,vEmail FROM track_service_users WHERE  iTrackServiceUserId = $iTrackServiceUserId";
    $data_drv = $obj->MySQLSelect($sql);

    $vEmail = $data_drv[0]['vEmail'];
    $vPhoneCode = $data_drv[0]['vPhoneCode'];
    $vPhone = $data_drv[0]['vPhone'];
    $vName = $data_drv[0]['vName'];
    $vLastName = $data_drv[0]['vLastName'];
    $vInviteCode = $data_drv[0]['vInviteCode'];
    if (!empty($vEmail)) {
        $maildata['vEmail'] = $vEmail;
        $maildata['NAME'] = $vName . ' ' . $vLastName;
        $maildata['INVITECODE'] = $vInviteCode;
        $result = $COMM_MEDIA_OBJ->SendMailToMember("TRACK_COMPANY_USER_INVITECODE_SEND", $maildata);
    }
    $vLangCode = $LANG_OBJ->FetchDefaultLangData("vCode");
    $dataArraySMSNew['NAME'] = $vName . ' ' . $vLastName;
    $dataArraySMSNew['INVITECODE'] = $vInviteCode;
    $message = $COMM_MEDIA_OBJ->GetSMSTemplate('TRACK_COMPANY_USER_INVITECODE_SEND', $dataArraySMSNew, "", $vLangCode);
    $result = $COMM_MEDIA_OBJ->SendSystemSMS($vPhone, $vPhoneCode, $message);
    $arr = [];
}

if($module == "fetch_linked_members") {
    $tUserIds = isset($_REQUEST['tUserIds']) ? $_REQUEST['tUserIds'] : '';

    $userData = $obj->MySQLSelect("SELECT iUserId, CONCAT(vName, ' ', vLastName) as MemberName, vEmail, CONCAT('+', vPhoneCode, vPhone) as MemberPhone FROM register_user WHERE iUserId IN ($tUserIds) AND eStatus = 'Active' ");

    if(!empty($userData)) {
        $memberList = "<table class='table table-bordered' width='100%' align='center'>";
        $memberList .= "<tr>";
        $memberList .= "<th>Member Name</th><th>E-mail</th><th width='30%'>Phone number</th>";
        $memberList .= "</tr>";
        foreach($userData as $value) {
            $memberList .= "<tr>";
            $memberList .= '<td><a href="javascript:void(0);" onclick="show_rider_details(' . $value['iUserId'] . ')" style="text-decoration: underline;">' .clearName($value['MemberName'])."</a></td><td>".clearEmail($value['vEmail'])."</td><td>".clearPhone($value['MemberPhone'])."</td>";
            $memberList .= "</tr>"; 
        }
        $memberList .= "</table>";
        $returnData['Action'] = 1;
        $returnData['message'] = $memberList;
        echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $returnData['Action'] = 0;
        $returnData['message'] = "<h1>No linked members found.</h1>";
        echo json_encode($returnData);
        exit;
    }
}
echo json_encode($arr);
exit;
?>