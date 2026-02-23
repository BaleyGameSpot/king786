<?php
include_once('../common.php');
$intervalmins = INTERVAL_SECONDS;
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
$AppeType = isset($_REQUEST['AppeType']) ? $_REQUEST['AppeType'] : '';
$orderId = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$requestsent = isset($_REQUEST['requestsent']) ? $_REQUEST['requestsent'] : '';
$cancelOrderDriver = isset($_REQUEST['cancelOrderDriver']) ? $_REQUEST['cancelOrderDriver'] : '';
$_REQUEST['eSystem'] = "DeliverAll";
if(!empty($requestsent)) {
    $sql = "SELECT iUserId,iOrderId,iCompanyId FROM orders where iOrderId = $orderId";
    $db_records = $obj->MySQLSelect($sql);
    
    $sql_general = "SELECT iUserId,tSessionId FROM register_user WHERE tSessionId != '' AND vFirebaseDeviceToken != '' AND eStatus = 'Active' ORDER BY iUserId ASC limit 1";
    $db_generalrecords = $obj->MySQLSelect($sql_general);
    
    $sql_company = "SELECT iGcmRegId FROM company WHERE iCompanyId = ".$db_records[0]['iCompanyId'];
    $db_company = $obj->MySQLSelect($sql_company);
    
    $dataArray = array();
    $dataArray['tSessionId'] = $db_generalrecords[0]['tSessionId'];
    $dataArray['iUserId'] = $db_records[0]['iUserId'];
    $dataArray['GeneralMemberId'] = $db_generalrecords[0]['iUserId'];
    $dataArray['vDeviceToken'] = $db_company[0]['iGcmRegId'];
    $dataArray['iOrderId'] = $db_records[0]['iOrderId'];
    $dataArray['eSystem'] = 'DeliverAll';
    echo json_encode($dataArray);
    exit;
}
if(!empty($cancelOrderDriver)) { // it takes list of driver how many driver have cancel order
    $CancelOrderDriver = $obj->MySQLSelect("SELECT DISTINCT(o.iDriverId),CONCAT(d.vName,' ',d.vLastName) as driverName, d.vEmail, CONCAT(d.vCode,' ',d.vPhone) as driverphone FROM order_driver_log o INNER JOIN register_driver d ON o.iDriverId = d.iDriverId WHERE iOrderId = ".$orderId);
    if(!empty($CancelOrderDriver)) {
        $driverList = "<table class='table table-bordered' width='100%' align='center'>";
        $driverList .= "<tr>";
        $driverList .= "<td>".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name</td><td>E-mail</td><td width='30%'>Phone number</td>";
        $driverList .= "</tr>";
        foreach($CancelOrderDriver as $key=>$value) {
            $driverList .= "<tr>";
            $driverList .= "<td>".clearName($value['driverName'])."</td><td>".clearEmail($value['vEmail'])."</td><td>".clearPhone($value['driverphone'])."</td>";
            $driverList .= "</tr>"; 
        }
        $driverList .= "</table>";
        $returnData['Action'] = 1;
        $returnData['message'] = $driverList;
        echo json_encode($returnData);
        exit;
    } else {
        $returnData['Action'] = 0;
        $returnData['message'] = "<h1>".$langage_lbl_admin['LBL_NO_DRIVERS_FOUND']."</h1>";
        echo json_encode($returnData);
        exit;
    }
}
if(!empty($orderId)) { //it takes driver list when select manual assign driver.
    $sql = "SELECT iUserId,iOrderId,iCompanyId,iUserAddressId,ePaymentOption FROM orders where iOrderId = $orderId";
    $db_order = $obj->MySQLSelect($sql);

    $Data_cab_requestcompany = $obj->MySQLSelect("SELECT vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,eDriverOption FROM company WHERE iCompanyId = ".$db_order[0]['iCompanyId']);

    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $iUserId = $db_order[0]['iUserId'];
    $UserSelectedAddressArr = FetchMemberAddressData($iUserId, "Passenger", $iUserAddressId);

    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $eDriverType = $Data_cab_requestcompany[0]['eDriverOption'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $address_data['eDriverType'] = $eDriverType;
    $address_data['iCompanyId'] = $db_order[0]['iCompanyId'];
    $address_data['iOrderId'] = $orderId;

    $online_drivers = FetchAvailableDrivers($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude, $iUserId);
    $dbDrivers = array();
    if(!empty($online_drivers['DriverList']) && scount($online_drivers['DriverList']) > 0) {
        foreach ($online_drivers['DriverList'] as $key => $value) {
            $online_drivers['DriverList'][$key]['FULLNAME'] = $value['vName'].' '.$value['vLastName'];
            if($db_order[0]['ePaymentOption'] == 'Cash' && $value['ACCEPT_CASH_TRIPS'] == "No"){
                 unset($online_drivers['DriverList'][$key]);
            }
        }

        $distance = array_column($online_drivers['DriverList'], 'distance');

        array_multisort($distance, SORT_ASC, $online_drivers['DriverList']);
        $dbDrivers = $online_drivers['DriverList'];
        if(!empty($dbDrivers)){
        $con = "<ul>";
        foreach ($dbDrivers as $key => $value) {
            if ($value['vAvailability'] == "Available") {
                $statusIcon = "../assets/img/green-icon.png";
            } else if ($value['vAvailability'] == "Active") {
                $statusIcon = "../assets/img/red.png";
            } else if ($value['vAvailability'] == "On Going Trip") {
                $statusIcon = "../assets/img/yellow.png";
            } else if ($value['vAvailability'] == "Arrived") {
                $statusIcon = "../assets/img/blue.png";
            } else {
                $statusIcon = "../assets/img/offline-icon.png";
            }
            $con .= '<li onclick="putDriverId(' . $value['iDriverId'] . ');"><input type="radio" name="driverid" value='.$value['iDriverId'].'>' . clearName($value['FULLNAME']) . ' <b>' . clearPhone($value['vPhone']) . '</b></li>';
        }
        $con .= "</ul>";
        $returnArr['Action'] = 1;
        $returnArr['message'] = $con;
        echo json_encode($returnArr);
        exit;
        } else {
           $returnArr['Action'] = 0;
            $returnArr['message'] = $langage_lbl['LBL_NO_DRIVERS_FOUND'];
            echo json_encode($returnArr);
            exit; 
        }
    }
    else {
        $returnArr['Action'] = 0;
        $returnArr['message'] = $langage_lbl['LBL_NO_DRIVERS_FOUND'];
        echo json_encode($returnArr);
        exit;
    }
}
?>
