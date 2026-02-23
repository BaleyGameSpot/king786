<?php
include_once('../common.php');

$iServiceId = 1;
$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLangCode, "1", $iServiceId);

function checkOrderRequestStatusAdmin($iOrderId) {
    global $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "'";
    $db_driver_request = $obj->MySQLSelect($sql);
    if (scount($db_driver_request) > 0) {
        $sql = "SELECT iDriverId from orders WHERE iOrderId ='" . $iOrderId . "'";
        $db_order_driver = $obj->MySQLSelect($sql);
        $iDriverId = $db_order_driver[0]['iDriverId'];
        if ($iDriverId > 0) {
            $returnArr['Action'] = "1";
            $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
            $returnArr["message1"] = "DRIVER_ASSIGN";
        } else {
            $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL + 5;
            $currentdate = @date("Y-m-d H:i:s");
            $checkdate = date('Y-m-d H:i:s', strtotime("+" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $checkdate1 = date('Y-m-d H:i:s', strtotime("-" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $sql = "SELECT iDriverRequestId from driver_request WHERE iOrderId ='" . $iOrderId . "' AND ( dAddedDate > '" . $checkdate1 . "' AND dAddedDate < '" . $checkdate . "')";
            $db_status = $obj->MySQLSelect($sql);
            if (scount($db_status) > 0) {
                $returnArr['Action'] = "0";
                $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
                $returnArr["message1"] = "REQ_PROCESS";
            } else {
                $returnArr['Action'] = "1";
                $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
                $returnArr["message1"] = "REQ_FAILED";
            }
        }
    } else {
        $returnArr['Action'] = "1";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        $returnArr["message1"] = "REQ_NOT_FOUND";
    }

    return $returnArr;
}

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
$tpages = isset($_REQUEST["tpages"]) ? $_REQUEST["tpages"] : '';
$sortby = isset($_REQUEST["sortby"]) ? $_REQUEST["sortby"] : '';
$order = isset($_REQUEST["order"]) ? $_REQUEST["order"] : '';
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
$searchCompany = isset($_REQUEST["searchCompany"]) ? $_REQUEST["searchCompany"] : '';
$searchDriver = isset($_REQUEST["searchDriver"]) ? $_REQUEST["searchDriver"] : '';
$searchRider = isset($_REQUEST["searchRider"]) ? $_REQUEST["searchRider"] : '';
$searchServiceType = isset($_REQUEST["searchServiceType"]) ? $_REQUEST["searchServiceType"] : '';
$startDate = isset($_REQUEST["startDate"]) ? $_REQUEST["startDate"] : '';
$endDate = isset($_REQUEST["endDate"]) ? $_REQUEST["endDate"] : '';
$vStatus = isset($_REQUEST["vStatus"]) ? $_REQUEST["vStatus"] : '';
//echo "<pre>";print_r($_REQUEST);die;
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$eAutoAssign = isset($_POST['eAutoAssign']) ? $_POST['eAutoAssign'] : 'No';
$eStatus1 = ($eAutoAssign == 'Yes') ? 'Pending' : 'Assign';
$iOrderId = isset($_POST['iOrderId']) ? $_POST['iOrderId'] : '';
$sql = 'select * from orders where iOrderId="' . $iOrderId . '" and iStatusCode="2"';
$db_order = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($db_order);die;
if (scount($db_order) == 0) {
    header("location:" . $backlink);
    exit;
}
//phpinfo();die;
//echo $eStatus1;die;
if ($eStatus1 == 'Pending') {
    $trip_status = "Requesting";
    $checkOrderRequestStatusArr = checkOrderRequestStatusAdmin($iOrderId);
    //echo "<pre>";print_r($checkOrderRequestStatusArr);die;
    $action = $checkOrderRequestStatusArr['Action'];
    if ($action == 0) {
        header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
        exit;
    }
    // isMemberEmailPhoneVerified($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,iGcmRegId,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = FetchMemberAddressData($iUserId, "Passenger", $iUserAddressId);

    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
    $alertMsg = $userwaitinglabel;
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = FetchAvailableDrivers($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude);
    $Data = $DataArr['DriverList'];
    $driver_id_auto = "";
    if (isset($DataArr['driver_id_auto'])) {
        $driver_id_auto = $DataArr['driver_id_auto'];
    }
    //echo "<pre>";print_r($isFullWalletCharge);die;
    $fWalletDebit = $db_order[0]['fWalletDebit'];
    $fNetTotal = $db_order[0]['fNetTotal'];
    $isFullWalletCharge = "No";
    if ($fWalletDebit > 0 && $fNetTotal == 0) {
        $isFullWalletCharge = "Yes";
    }
    //echo "<pre>";print_r($isFullWalletCharge);die;
    // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    if ($ePaymentOption == "Cash" && $isFullWalletCharge == "No") {
        $Data_new = array();
        $Data_new = $Data;
        for ($i = 0; $i < scount($Data); $i++) {
            $isRemoveFromList = "No";
            $ACCEPT_CASH_TRIPS = $Data[$i]['ACCEPT_CASH_TRIPS'];
            if ($ACCEPT_CASH_TRIPS == "No") {
                $isRemoveFromList = "Yes";
            }
            if ($isRemoveFromList == "Yes") {
                unset($Data_new[$i]);
            }
        }
        $Data = array_values($Data_new);
        for ($j = 0; $j < scount($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = trim($driver_id_auto, ",");
    } else {
        for ($j = 0; $j < scount($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = trim($driver_id_auto, ",");
    }
    // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    // echo "<pre>";print_r($Data);exit;
    //$sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    //$passengerData = $obj->MySQLSelect($sqlp);
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
    $passengerFName = $Data_cab_requestcompany[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $Data_cab_requestcompany[0]['vImgName'];
    $final_message['PRating'] = $Data_cab_requestcompany[0]['vAvgRating'];
    $final_message['PPhone'] = $Data_cab_requestcompany[0]['vPhone'];
    $final_message['PPhoneC'] = $Data_cab_requestcompany[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    $final_message['eSystem'] = "DeliverAll";
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion,vCountry,eAppTerminate,eDebugMode,eHmsDevice FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLocationUpdateDate > '$str_date' AND vAvailability='Available' AND vCountry LIKE '" . $vCountry . "'";
    $result = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($result);die;
    if (scount($result) == 0 || $driver_id_auto == "" || scount($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        $_SESSION['messagealert'] = "NO_CARS";
        header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
        exit;
    }
    $where = "";

    $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
    $destLoc = $DestLatitude . ',' . $DestLongitude;
    $deviceTokens_arr_ios = $generalDataArr = array();
    foreach ($result as $item) {
        if ($item['eDeviceType'] == "Android") {
            array_push($registation_ids_new, $item['iGcmRegId']);
        } else {
            array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
        }
        $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $item['vLang'] . "'", 'true');
        $tSessionId = $item['tSessionId'];
        $final_message['tSessionId'] = $tSessionId;
        $final_message['vTitle'] = $alertMsg_db;

        $generalDataArr[] = array(
            'eDeviceType'       => $item['eDeviceType'],
            'deviceToken'       => $item['iGcmRegId'],
            'alertMsg'          => $alertMsg_db,
            'eAppTerminate'     => $item['eAppTerminate'],
            'eDebugMode'        => $item['eDebugMode'],
            'eHmsDevice'        => $item['eHmsDevice'],
            'message'           => $final_message,
            'addRequestSentArr' => array(
                'iUserId'       => $iUserId,
                'iDriverId'     => $item['iDriverId'],
                'tMessage'      => $final_message,
                'iMsgCode'      => $final_message['MsgCode'],
                'vStartLatlong' => $sourceLoc,
                'vEndLatlong'   => $destLoc,
                'tStartAddress' => $PickUpAddress,
                'tEndAddress'   => $DestAddress,
                'iOrderId'      => $iOrderId
            ),
            'channelName'       => "CAB_REQUEST_DRIVER_" . $item['iDriverId']
        );
    }

    $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_PROVIDER);
    
    header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
    exit;
} else {
    $trip_status = "Requesting";
    $checkOrderRequestStatusArr = checkOrderRequestStatusAdmin($iOrderId);
    $action = $checkOrderRequestStatusArr['Action'];
    if ($action == 0) {
        header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
        exit;
    }
    // isMemberEmailPhoneVerified($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,iGcmRegId,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = FetchMemberAddressData($iUserId, "Passenger", $iUserAddressId);
    // echo "<pre>";print_r($UserSelectedAddressArr);exit;
    //$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
    $alertMsg = $userwaitinglabel;
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = FetchAvailableDrivers($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude);
    $Data = $DataArr['DriverList'];
    $fWalletDebit = $db_order[0]['fWalletDebit'];
    $fNetTotal = $db_order[0]['fNetTotal'];
    $isFullWalletCharge = "No";
    if ($fWalletDebit > 0 && $fNetTotal == 0) {
        $isFullWalletCharge = "Yes";
    }

    $driver_id_auto = isset($_POST['assign_driver']) ? $_POST['assign_driver'] : '';
    if ($driver_id_auto != '') {
        // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
        // echo "<pre>";print_r($Data);exit;
        //$sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        //$passengerData = $obj->MySQLSelect($sqlp);
        //echo "<pre>";print_r($Data_cab_requestcompany);die;
        $final_message['Message'] = "CabRequested";
        $final_message['sourceLatitude'] = strval($PickUpLatitude);
        $final_message['sourceLongitude'] = strval($PickUpLongitude);
        $final_message['PassengerId'] = strval($iUserId);
        $final_message['iCompanyId'] = strval($iCompanyId);
        $final_message['iOrderId'] = strval($iOrderId);
        $passengerFName = $Data_cab_requestcompany[0]['vCompany'];
        $final_message['PName'] = $passengerFName;
        $final_message['PPicName'] = $Data_cab_requestcompany[0]['vImgName'];
        $final_message['PRating'] = $Data_cab_requestcompany[0]['vAvgRating'];
        $final_message['PPhone'] = $Data_cab_requestcompany[0]['vPhone'];
        $final_message['PPhoneC'] = $Data_cab_requestcompany[0]['vPhoneCode'];
        $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
        $final_message['destLatitude'] = strval($DestLatitude);
        $final_message['destLongitude'] = strval($DestLongitude);
        $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
        $final_message['vTitle'] = $alertMsg;
        $final_message['eSystem'] = "DeliverAll";
        // $final_message['Time']= strval(date('Y-m-d'));
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
        $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion,eAppTerminate,eDebugMode,eHmsDevice FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLocationUpdateDate > '$str_date' AND vAvailability='Available'";
        $result = $obj->MySQLSelect($sql);
        //echo "Res:count:".scount($result);exit;
        if (scount($result) == 0 || $driver_id_auto == "") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "NO_CARS";
            $_SESSION['messagealert'] = "NO_CARS";
            header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
            exit;
        }
        // $where = " iUserId = '$passengerId'";
        $where = "";

        $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
        $destLoc = $DestLatitude . ',' . $DestLongitude;
        
        $generalDataArr = array();
        foreach ($result as $item) {
            $alertMsg_db = get_value('language_label_1', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $item['vLang'] . "'", 'true');
            $tSessionId = $item['tSessionId'];
            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;

            $generalDataArr[] = array(
                'eDeviceType'       => $item['eDeviceType'],
                'deviceToken'       => $item['iGcmRegId'],
                'alertMsg'          => $alertMsg_db,
                'eAppTerminate'     => $item['eAppTerminate'],
                'eDebugMode'        => $item['eDebugMode'],
                'eHmsDevice'        => $item['eHmsDevice'],
                'message'           => $final_message,
                'addRequestSentArr' => array(
                    'iUserId'       => $iUserId,
                    'iDriverId'     => $item['iDriverId'],
                    'tMessage'      => $final_message,
                    'iMsgCode'      => $final_message['MsgCode'],
                    'vStartLatlong' => $sourceLoc,
                    'vEndLatlong'   => $destLoc,
                    'tStartAddress' => $PickUpAddress,
                    'tEndAddress'   => $DestAddress,
                    'iOrderId'      => $iOrderId
                ),
                'channelName'       => "CAB_REQUEST_DRIVER_" . $item['iDriverId']
            );
        }

        $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_PROVIDER);
    }
}
$returnArr['Action'] = "1";
/*   echo json_encode($returnArr); */
if ($returnArr['Action'] == '1') {
    header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
    exit;
} else {
    header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
    exit;
}
?>