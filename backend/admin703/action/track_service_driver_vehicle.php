<?php

include_once('../../common.php');


$AUTH_OBJ->checkMemberAuthentication();

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$getUberXVehicles = $obj->MySQLSelect("SELECT * FROM driver_vehicle WHERE eType='UberX'");
$driverVehicleIdArr = array();
for ($d = 0; $d < scount($getUberXVehicles); $d++) {
    $driverVehicleIdArr[] = $getUberXVehicles[$d]['iDriverVehicleId'];
}

//Start make deleted
if ($method == 'delete' && $iDriverVehicleId != '') {
    if (!$userObj->hasPermission('delete-driver-vehicle-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete vehicle';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iDriverVehicleId != "") {
            $vehicleIds = $iDriverVehicleId;
        } else {
            $vehicleIds = $checkbox;
        }
        $explodeIds = explode(",", $vehicleIds);
        
        if (SITE_TYPE != 'Demo') {
            $sql1 = "SELECT * FROM track_service_trips WHERE iDriverVehicleId = '" . $iDriverVehicleId . "' AND iActive IN ('Active',  'OnGoingTrip', 'Onboarding')";
            $current_active_trip = $obj->MySQLSelect($sql1);

            if (empty($current_active_trip)) {
                $query = "UPDATE driver_vehicle SET eStatus = 'Deleted' WHERE iDriverVehicleId IN (" . $vehicleIds . ")";
                $obj->sql_query($query);

                for ($re = 0; $re < scount($explodeIds); $re++) {
                    if (!in_array($explodeIds[$re], $driverVehicleIdArr)) {
                        $sql_update = "UPDATE register_driver SET vAvailability = 'Not Available', `iDriverVehicleId`= '0' WHERE iDriverId = '" . $iDriverId . "' AND iDriverVehicleId IN (" . $vehicleIds . ") AND vAvailability = 'Available'";
                        $obj->sql_query($sql_update);
                    }
                }
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            } else {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = "Vehicle can't delete because of " . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . " has on trip.";
            }
        } else {
            $_SESSION['success'] = '2';
        }
    }
    if ($method == 'delete') {
        $parameters = '';
        foreach ($_REQUEST as $key => $val) {
            if ($key == "iDriverId") {
                $parameters .= "&$key=";
            } else {
                $parameters .= "&$key=" . $val;
            }
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver_vehicle.php?" . $parameters);
    exit;
}
//End make deleted

//Start Change single Status
// For active or inactive
if ($iDriverVehicleId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-driver-vehicle-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update vehicle status';
    } else {
        if (SITE_TYPE != 'Demo') {
            if ($status == 'Inactive') {
                $sql1 = "SELECT * FROM track_service_trips WHERE iDriverVehicleId = '" . $iDriverVehicleId . "' AND iActive IN ('Active',  'OnGoingTrip', 'Onboarding')";
                $current_active_trip = $obj->MySQLSelect($sql1);
                if (empty($current_active_trip)) {
                    $query = "UPDATE driver_vehicle SET eStatus = '" . $status . "' WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                    $obj->sql_query($query);


                    if (!in_array($iDriverVehicleId, $driverVehicleIdArr)) {
                        $sql_update = "UPDATE register_driver SET vAvailability = 'Not Available', `iDriverVehicleId`= '0' WHERE iDriverVehicleId = '" . $iDriverVehicleId . "' AND vAvailability = 'Available'";
                        $obj->sql_query($sql_update);
                    }

                    $_SESSION['success'] = '1';
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
                } else {
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = "Vehicle can't inactive because of " . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . " has on trip.";
                }
            } else {
                $sqldr = "SELECT iDriverId,iTrackServiceCompanyId FROM driver_vehicle WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                $DriverDetails = $obj->MySQLSelect($sqldr);

                $querydr = "SELECT iDriverVehicleId FROM register_driver WHERE iDriverId = '" . $DriverDetails[0]['iDriverId'] . "' ";
                $DriverVehicleDetails = $obj->MySQLSelect($querydr);
                if ($DriverVehicleDetails[0]['iDriverVehicleId'] == '' || $DriverVehicleDetails[0]['iDriverVehicleId'] == '0') {
                    $query = "UPDATE register_driver SET iDriverVehicleId = '" . $iDriverVehicleId . "' WHERE iDriverId = '" . $DriverDetails[0]['iDriverId'] . "'";
                    $obj->sql_query($query);
                }

                $query = "UPDATE driver_vehicle SET eStatus = '" . $status . "' WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                $obj->sql_query($query);

                $_SESSION['success'] = '1';
                if ($status == 'Active') {
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
                } else {
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
                }
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver_vehicle.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if (!empty($checkbox) && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-driver-vehicle-trackservice', 'delete-driver-vehicle-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update or delete vehicle status';
    } else {
        $checkbox_values = implode(',', $_REQUEST['checkbox']);
        //print_r($_REQUEST['checkbox']);die;
        if (SITE_TYPE != 'Demo') {
            $current_active_trip = "";
            if (($statusVal == "Deleted") || ($statusVal == "Inactive")) {
                $sql = "SELECT iDriverId,iDriverVehicleId FROM driver_vehicle WHERE iDriverVehicleId IN (" . $checkbox_values . ")";
                $driverids = $obj->MySQLSelect($sql);
                //echo "<pre>";print_r($driverids);die;
                $vehicleIdArr = $data = array();
                foreach ($driverids as $key => $value) {
                    $data[$value['iDriverId']] = $value['iDriverId'];
                    if (!in_array($value['iDriverVehicleId'], $driverVehicleIdArr)) {
                        $vehicleIdArr[$value['iDriverId']] = $value['iDriverId'];
                    }
                }
                $driverid = implode(",", $data);
                $driverIds = implode(",", $vehicleIdArr);
                $sql1 = "SELECT * FROM register_driver as d LEFT JOIN trips as t ON t.iDriverId = d.iDriverId WHERE t.iDriverId IN (" . $driverid . ") AND  t.iDriverVehicleId  IN (" . $checkbox_values . ") AND  t.iActive IN ('Active',  'On Going Trip') ";
                $current_active_trip = $obj->MySQLSelect($sql1);
            }
            if (empty($current_active_trip)) {
                $query = "UPDATE driver_vehicle SET eStatus = '" . $statusVal . "' WHERE iDriverVehicleId IN (" . $checkbox_values . ")";
                $obj->sql_query($query);

                if ($statusVal == "Deleted") {
                    $sql_update = "UPDATE register_driver SET vAvailability = 'Not Available', `iDriverVehicleId`= '0' WHERE iDriverId IN (" . $driverIds . ") AND vAvailability = 'Available'";
                    $obj->sql_query($sql_update);
                }

                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = "Record can't " . $statusVal . " because one of " . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . " has on trip.";
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver_vehicle.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>