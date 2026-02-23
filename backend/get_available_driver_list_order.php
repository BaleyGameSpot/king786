<?php
include_once('common.php');

$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
$dBooking_date = isset($_REQUEST['dBooking_date']) ? $_REQUEST['dBooking_date'] : '';
$AppeType = isset($_REQUEST['AppeType']) ? $_REQUEST['AppeType'] : '';

$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
	
$ssql = " AND rd.eStatus='Active'";
 
  	
if ($keyword != "") {
    $ssql .= " AND CONCAT(rd.vName,' ',rd.vLastName) like '%$keyword%'";
}
$eLadiesRide = isset($_REQUEST['eLadiesRide']) ? $_REQUEST['eLadiesRide'] : '';
$eHandicaps = isset($_REQUEST['eHandicaps']) ? $_REQUEST['eHandicaps'] : '';
$eChildSeat = isset($_REQUEST['eChildSeat']) ? $_REQUEST['eChildSeat'] : '';
$eWheelChair = isset($_REQUEST['eWheelChair']) ? $_REQUEST['eWheelChair'] : '';

if ($eLadiesRide == 'Yes') {
    $ssql .= " AND (rd.eFemaleOnlyReqAccept = 'Yes' OR rd.eGender = 'Female')";
}
if ($eHandicaps == 'Yes') {
    $ssql .= " AND dv.eHandiCapAccessibility = 'Yes'";
}
if ($eChildSeat == 'Yes') {
    $ssql .= " AND dv.eChildSeatAvailable = 'Yes'";
	}
if ($eWheelChair == 'Yes') {
    $ssql .= " AND dv.eWheelChairAvailable = 'Yes'";
	}
if (!empty($vCountry)) {
    $ssql .= " AND rd.vCountry LIKE '" . $vCountry . "'";
}
$driverid = isset($_REQUEST['driverid']) ? $_REQUEST['driverid'] : '';
$orderId = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$requestsent = isset($_REQUEST['requestsent']) ? $_REQUEST['requestsent'] : '';
if(!empty($requestsent)) {
    //$where = " iOrderId  = '" . $orderId . "'";
    //$Data_update_orders['iDriverId'] = $driverid;
    //$id = $obj->MySQLQueryPerform("orders", $Data_update_orders, 'update', $where);
    $sql = "SELECT iUserId,iOrderId,iCompanyId FROM orders where iOrderId = $orderId";
    $db_records = $obj->MySQLSelect($sql);
   
    $sql_general = "SELECT iUserId,tSessionId FROM register_user WHERE tSessionId!='' AND vFirebaseDeviceToken!='' ORDER BY iUserId ASC limit 1";
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
if ($AppeType == "UberX" && !empty($dBooking_date)) {
    $vday = date('l', strtotime($dBooking_date));
    $curr_hour = date('H', strtotime($dBooking_date));
    $next_hour = $curr_hour + 01;
    $next_hour = sprintf("%02d", $next_hour);
    if ($curr_hour == "00") {
        $curr_hour = "12";
        $next_hour = "01";
            }
    $selected_time = $curr_hour . "-" . $next_hour;
    $ssql .= "AND vDay LIKE '%" . $vday . "%' AND dmt.vAvailableTimes LIKE '%" . $selected_time . "%'";
            }
$ssql .= " AND vAvailability = 'Available'";
if ($AppeType == "UberX") {
    $sql = "SELECT rd.iDriverId,rd.vEmail,rd.iCompanyId, CONCAT(rd.vName,' ',rd.vLastName) AS FULLNAME,rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone,rd.tLocationUpdateDate FROM register_driver AS rd RIGHT JOIN driver_manage_timing  AS dmt ON rd.iDriverId = dmt.iDriverId  WHERE rd.vLatitude !='' AND rd.vLongitude !='' " . $ssql . " GROUP BY dmt.iDriverId";
    $db_records = $obj->MySQLSelect($sql);
    foreach ($db_records as $key => $value) {
        $sql_vehicle = "SELECT vCarType FROM `driver_vehicle` WHERE iDriverId = '" . $value['iDriverId'] . "' AND eType='UberX'";
        $dbvehicle_records = $obj->MySQLSelect($sql_vehicle);
        $db_records[$key]['vCarType'] = $dbvehicle_records[0]['vCarType'];
        }
} else {
    $sql = "SELECT rd.iDriverId,rd.vEmail,rd.iCompanyId, CONCAT(rd.vName,' ',rd.vLastName) AS FULLNAME,rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline,rd.tLocationUpdateDate, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' " . $ssql;
    $db_records = $obj->MySQLSelect($sql);
}

/* 
 * $enableCommisionDeduct = $MODULES_OBJ->autoDeductDriverCommision("Ride"); // Added By HJ On 16-10-2020 For get Auto Deduct Driver Commision Configuration As Per eSystem
 * if($enableCommisionDeduct == 'Yes') {
  $j=0;
  for($i=0;$i<scount($db_records);$i++){
  $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($db_records[$i]['iDriverId'],"Driver");
  if($user_available_balance > $WALLET_MIN_BALANCE){
  $db_records_new[$j] = $db_records[$i];
  $db_records_new[$j]['user_available_balance'] = $user_available_balance;
  $j++;
        }

    }  
  $db_records = $db_records_new;
  } */

$dbDrivers = array();
	for ($i = 0; $i < scount($db_records); $i++) {
			$newArray = array();
			$newArray = explode(',', $db_records[$i]['vCarType']);
			
			if ($iVehicleTypeId == '' || (!empty($newArray) && in_array($iVehicleTypeId, $newArray))) {
        if ($db_records[$i]['vImage'] != 'NONE' && $db_records[$i]['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_records[$i]['iDriverId'] . '/2_' . $db_records[$i]['vImage'])) {
            $DriverImage = $tconfig["tsite_upload_images_driver"] . '/' . $db_records[$i]['iDriverId'] . '/2_' . $db_records[$i]['vImage'];
        } else {
				$DriverImage = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
				}
				$db_records[$i]['vImageDriver'] = $DriverImage;
				$time = time();
				$last_online_time = strtotime($db_records[$i]['tLastOnline']);
				$time_difference = $time - $last_online_time;
        $vTripStatus = $db_records[$i]['vTripStatus'];
				
				if ($vTripStatus != 'Active' && $db_records[$i]['vAvailability'] == "Available" && $db_records[$i]['tLocationUpdateDate'] > $str_date) {
				 $db_records[$i]['vAvailability'] = "Available";
						$dbDrivers[$i] = $db_records[$i];
				} else {
					if ($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived') {
						$db_records[$i]['vAvailability'] = $vTripStatus;
					} else {
						$db_records[$i]['vAvailability'] = "Not Available";
					}
						$dbDrivers[$i] = $db_records[$i];
					}
				}


}
//echo "<pre>"; print_r($dbDrivers); die;
#marker Add

$map = isset($_REQUEST['map']) ? $_REQUEST['map'] : '';

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
        $con .= '<li onclick="putDriverId(' . $value['iDriverId'] . ');"><input type="radio" name="driverid" value='.$value['iDriverId'].'>' . clearName($value['FULLNAME']) . ' <b>+' . clearPhone($value['vCode'] . $value['vPhone']) . '</b></li>';
    }
    $con .= "</ul>";
} else {
    $con = '';
}
echo $con;
exit;
?>