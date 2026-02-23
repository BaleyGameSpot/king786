<?php
include_once('../../common.php');
global $userObj;
$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
$date = Date('Y-m-d');
$AUTH_OBJ->checkMemberAuthentication();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start make deleted


function NotifyDriverAboutActivation($iDriverId)
{
    global $langage_lbl_admin, $EVENT_MSG_OBJ, $COMM_MEDIA_OBJ, $obj;
    $row1 = $obj->MySQLSelect("SELECT tSessionId,vEmail, vCode , vPhone, eHmsDevice , iDriverId, vCurrencyDriver, vLang, eDeviceType, iGcmRegId, eAppTerminate, eDebugMode, vName, vLastName FROM `register_driver` WHERE iDriverId = '" . $iDriverId . "'");
    $NOTI_TEMPLATE = "";
    $alertMsg = $langage_lbl_admin["LBL_ADMIN_ACTIVE_DRIVER_ACCOUNT"];
    $alertMsg =str_replace("#NAME#", $row1[0]['vName'] .' '.$row1[0]['vLastName'],  $alertMsg);
    $generalDataArr = $final_message = array();
    $message_arr = array();
    $message_arr['Message'] = $message_arr['MsgType'] = 'ActivateServiceProvider';
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['uString'] = time();
    $message_arr['tSessionId'] = $row1[0]['tSessionId'];
    $generalDataArr[] = array('eDeviceType'   => $row1[0]['eDeviceType'],
                              'deviceToken'   => $row1[0]['iGcmRegId'],
                              'alertMsg'      => $alertMsg,
                              'eAppTerminate' => $row1[0]['eAppTerminate'],
                              'eDebugMode'    => $row1[0]['eDebugMode'],
                              'message'       => $message_arr,
                              'MsgType'       => 'ActivateServiceProvider',
                              'eHmsDevice' => $row1[0]['eHmsDevice']);

    $arr['NOTIFICATION'] = $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_PROVIDER);

    $MAIL_TEMPLATE = 'ADMIN_ACTIVE_DRIVER_ACCOUNT';
    $mailArr = [];
    $mailArr['NAME'] = $row1[0]['vName'] .' '.$row1[0]['vLastName'];
    $mailArr['vEmail'] = $row1[0]['vEmail'];
    $arr['MAIL'] = $COMM_MEDIA_OBJ->SendMailToMember($MAIL_TEMPLATE, $mailArr);
    return $arr;

}
$hardDelete = 0; //0-Soft Delete,1-Hard Delete
if ($_SERVER['HTTP_HOST'] == "192.168.1.131" || $_SERVER['HTTP_HOST'] == "mobileappsdemo.com" || $_SERVER['HTTP_HOST'] == "webprojectsdemo.com" || $_SERVER['HTTP_HOST'] == "192.168.1.141") {
    $hardDelete = 1; //0-Soft Delete,1-Hard Delete
}
$allTables = getAllTableArray();
//Added By HJ On 03-03-2020 For Check Driver Service/Vehicle When Active As Per Discuss With KS Sir Start
if ((strtolower($status) == 'active' || strtolower($statusVal) == 'active') && SITE_TYPE != 'Demo') {
    if ($iDriverId != "") {
        $driverIds = $iDriverId;
    } else {
        $driverIds = $checkbox;
    }
    //$whereType = " AND eType='Ride'";
    //if ($APP_TYPE == 'Ride-Delivery-UberX') {
    //    $whereType = " AND (eType='Ride' OR eType='UberX')";
    //} elseif ($APP_TYPE == 'UberX') {
    //    $whereType = " AND eType='UberX'";
    //}
    //$sql = "SELECT register_driver.iDriverId from register_driver LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId WHERE driver_vehicle.eStatus='Active' AND driver_vehicle.vCarType != '' AND register_driver.iDriverId IN (" . $driverIds . ") GROUP BY register_driver.iDriverId";
    $sql = "SELECT register_driver.iDriverId from register_driver LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId WHERE driver_vehicle.eStatus='Active' AND driver_vehicle.vCarType != '' AND register_driver.iDriverId IN (" . $driverIds . ") GROUP BY register_driver.iDriverId
            UNION 
            SELECT register_driver.iDriverId from register_driver LEFT JOIN bidding_driver_service on bidding_driver_service.iDriverId=register_driver.iDriverId WHERE bidding_driver_service.vBiddingId!= '' AND register_driver.iDriverId IN (" . $driverIds . ") GROUP BY register_driver.iDriverId
            UNION
            SELECT register_driver.iDriverId from register_driver LEFT JOIN driver_services_video_consult_charges on driver_services_video_consult_charges.iDriverId=register_driver.iDriverId WHERE driver_services_video_consult_charges.eStatus='Active' AND driver_services_video_consult_charges.eApproved = 'Yes' AND register_driver.iDriverId IN (" . $driverIds . ")  GROUP BY register_driver.iDriverId";

    
    
    $Data = $obj->MySQLSelect($sql);
    $serviceDriverArr = array();
    for ($h = 0; $h < scount($Data); $h++) {
        $serviceDriverArr[] = $Data[$h]['iDriverId'];
    }
    $explodeData = explode(",", $driverIds);
    //echo scount($serviceDriverArr)."====".scount($explodeData);die;
    //echo "<pre>";print_r($serviceDriverArr);die;
    if (scount($serviceDriverArr) != scount($explodeData)) {
        $_SESSION['success'] = '3';
        if ($APP_TYPE == 'Ride-Delivery-UberX') {
            $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because either ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' has not added any vehicle or his added vehicle is not activated yet or not selected any services. Please try again after adding and activating the vehicle/services.';
        } elseif ($APP_TYPE == 'UberX') {
            $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because either ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' has not selected any services. Please try again after adding and activating the services.';
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because either ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' has not added any vehicle or his added vehicle is not activated yet. Please try again after adding and activating the vehicle.';
        }
        header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP'] ."?" . $parameters);
        exit;
    }
}
//Added By HJ On 03-03-2020 For Check Driver Service/Vehicle When Active As Per Discuss With KS Sir End
if (($statusVal == 'Deleted' || $method == 'delete') && ($iDriverId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete ' . strtolower($langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]);
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iDriverId != "") {
            $driverIds = $iDriverId;
        } else {
            $driverIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE register_driver SET 
                        eStatus = 'Deleted', 
                        vPhone = concat(vPhone, '(Deleted)') ,  
                        vFbId = CASE 
                                    WHEN vFbId != 0 OR vFbId != '' THEN CONCAT(vFbId, '(Deleted)') 
                                    ELSE vFbId 
                                END  
                        WHERE iDriverId IN (" . $driverIds . ")";
            $obj->sql_query($query);
            $explodeId = explode(",", $driverIds);
            for ($i = 0; $i < scount($explodeId); $i++) {
                /* Insert status log on user_log table */
                $queryIn = "INSERT INTO user_status_logs SET iUserId = " . $explodeId[$i] . ", eUserType = 'driver', dDate = '" . $date . "', eStatus = 'Deleted', iUpdatedBy = " . $_SESSION['sess_iAdminUserId'] . ", vIP = '" . $ip . "'";
                $obj->sql_query($queryIn);
                if ($hardDelete == 1) {
                    removedDriverData($explodeId[$i]);
                }
            }
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']."?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
$rquery = "SELECT rd.iCompanyId,c.eStatus FROM register_driver as rd LEFT JOIN company as c on c.iCompanyId=rd.iCompanyId WHERE rd.iDriverId = '" . $iDriverId . "'";
$drvcdata = $obj->MySQLSelect($rquery);
$cStatus = $drvcdata[0]['eStatus'];
if ($iDriverId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status ' . strtolower($langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]);
    } else {
        if (SITE_TYPE != 'Demo') {
            if ($cStatus == 'Inactive' && strtolower($status) == 'active') {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because company is not activated. Please try again after activating the company.';
            } else {
                /*--------------------- driver deleted duplicate check --------------------*/
                $checkUserDeleted = $obj->MySQLSelect("SELECT vPhone,vFbId FROM register_driver WHERE eStatus = 'Deleted' AND iDriverId='" . $iDriverId . "'");
                if (!empty($checkUserDeleted)) {
                    $mobile = clearPhone($checkUserDeleted[0]['vPhone']);
                    $vFbId = str_replace('(Deleted)', '', $checkUserDeleted[0]['vFbId']);
                    $checkUserDeleted = $obj->MySQLSelect("SELECT vPhone FROM register_driver WHERE eStatus != 'Deleted' AND vPhone='" . $mobile . "' AND iDriverId !='" . $iDriverId . "' ");
                    if (!empty($checkUserDeleted)) {
                        $_SESSION['success'] = 2;
                        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_ADMIN_NOT_ABLE_ACTIVE_TEXT'];
                        header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']."?" . $parameters);
                        exit;
                    } else {
                        if(!empty($vFbId) && $vFbId != 0){
                            $query = "UPDATE register_driver SET vPhone = '" . $mobile . "', vFbId = '" . $vFbId . "' WHERE iDriverId = '" . $iDriverId . "'";
                        } else {
                            $query = "UPDATE register_driver SET vPhone = '" . $mobile . "' WHERE iDriverId = '" . $iDriverId . "'";
                        }
                        $checkUserDeleted = $obj->sql_query($query);
                    }
                }
                /*--------------------- driver deleted duplicate check --------------------*/
                $query = "UPDATE register_driver SET eStatus = '" . $status . "' WHERE iDriverId = '" . $iDriverId . "'";
                $obj->sql_query($query);
                /* Insert status log on user_log table */
                $queryIn = "INSERT INTO user_status_logs SET iUserId = " . $iDriverId . ", eUserType = 'driver', dDate = '" . $date . "', eStatus = '" . $status . "', iUpdatedBy = " . $_SESSION['sess_iAdminUserId'] . ", vIP = '" . $ip . "'";
                $obj->sql_query($queryIn);
                $_SESSION['success'] = '1';
                if ($status == 'Active') {
                    NotifyDriverAboutActivation($iDriverId);
                    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_ACTIVATE_MSG"];
                } else {
                    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_INACTIVATE_MSG"];
                }
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']."?" . $parameters);
    exit;
}
//End Change single Status
if(!empty($driverIds)){
    $rquery1 = "SELECT rd.iCompanyId,c.eStatus,rd.iDriverId FROM register_driver as rd LEFT JOIN company as c on c.iCompanyId=rd.iCompanyId WHERE rd.iDriverId IN (" . $driverIds . ") AND c.eStatus='Active'";
    $drvmultidata1 = $obj->MySQLSelect($rquery1);
    $serviceDriverCompArr1 = array();
    for ($h = 0; $h < scount($drvmultidata1); $h++) {
        $serviceDriverCompArr1[] = $drvmultidata1[$h]['iDriverId'];
    }
    $explodeData = explode(",", $driverIds);
}
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status ' . strtolower($langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]);
    } else {
        if (scount($serviceDriverCompArr1) != scount($explodeData)) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because company is not activated. Please try again after activating the company.';
        } else {
            if (SITE_TYPE != 'Demo') {
                $query = "UPDATE register_driver SET eStatus = '" . $statusVal . "' WHERE iDriverId IN (" . $checkbox . ")";
                $obj->sql_query($query);
                $checkbox = explode(",", $checkbox);
                for ($i = 0; $i < scount($checkbox); $i++) {
                    /* Insert status log on user_log table */
                    $queryIn = "INSERT INTO user_status_logs SET iUserId = " . $checkbox[$i] . ", eUserType = 'driver', dDate = '" . $date . "', eStatus = '" . $statusVal . "', iUpdatedBy = " . $_SESSION['sess_iAdminUserId'] . ", vIP = '" . $ip . "'";
                    $obj->sql_query($queryIn);

                    if($statusVal == 'Active'){
                        NotifyDriverAboutActivation($checkbox[$i]);
                    }
                }
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = 2;
            }
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']."?" . $parameters);
    exit;
}
/* if ($method == 'reset' && $iDriverId != '') {

  if(SITE_TYPE !='Demo'){

  $query = "UPDATE register_driver SET vCreditCard='NULL',iTripId='0',vTripStatus='NONE',vStripeToken='',vStripeCusId='' WHERE iDriverId = '" . $iDriverId . "'";

  $obj->sql_query($query);

  $_SESSION['success'] = '1';

  $_SESSION['var_msg'] =  $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'Reset successfully';

  }

  else{

  $_SESSION['success'] = '2';

  }

  header("Location:".$tconfig["tsite_url_main_admin"]."driver.php"); exit;

  } */
if ($method == 'reset' && $iDriverId != '') {
    $q = "SELECT iTripId,vTripStatus FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
    $drvdata = $obj->MySQLSelect($q);
    if (!empty($drvdata) && $drvdata[0]['iTripId'] != '0') {
        $sql = "SELECT iTripId,iActive,iDriverId,iUserId FROM trips WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
        $TripData = $obj->MySQLSelect($sql);
        // user
        $userquery = "SELECT iTripId,vTripStatus FROM register_user WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
        $useData = $obj->MySQLSelect($userquery);
        if ($TripData[0]['iActive'] == 'On Going Trip') {
            // driver
            $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $iDriverId . "'";
            $obj->sql_query($query);
            // trip
            $query1 = "UPDATE trips SET iActive='Finished',tEndDate = NOW() WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
            $obj->sql_query($query1);
            // rating
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver' AND vRating1 != '' ";
            $TripRateDatadriver = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatadriver)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                $obj->sql_query($rateq);
            }
            // rating
            if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                // user
                $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                $obj->sql_query($uquery);
                // rating
                $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger' AND vRating1 != '' ";
                $TripRateDatapass = $obj->MySQLSelect($checkrate);
                if (!empty($TripRateDatapass)) {
                    $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                    $obj->sql_query($rateq);
                } else {
                    $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                    $obj->sql_query($rateq);
                }
            }
        } else if ($TripData[0]['iActive'] == 'Active') {
            // driver
            $aquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
            $obj->sql_query($aquery);
            // trip
            $qu1 = "UPDATE trips SET iActive = 'Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='Driver', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
            $obj->sql_query($qu1);
            // user
            if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                // user
                $uquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                $obj->sql_query($uquery);
            }
        } else {
            if ($TripData[0]['iActive'] == 'Canceled') {
                // Driver
                if ($drvdata[0]['vTripStatus'] != 'Cancelled' && $drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $dquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
                    $obj->sql_query($dquery);
                }
                // Rider
                if ($useData[0]['vTripStatus'] != 'Cancelled' && $useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $rquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                    $obj->sql_query($rquery);
                }
            } else {
                // Driver
                if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $iDriverId . "'";
                    $obj->sql_query($query);
                    // rating
                    $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver' AND vRating1 != '' ";
                    $TripRateDatadriver = $obj->MySQLSelect($checkrate);
                    if (!empty($TripRateDatadriver)) {
                        $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
                        $obj->sql_query($rateq);
                    } else {
                        $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                        $obj->sql_query($rateq);
                    }
                }
                // Rider
                if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                    // user
                    $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                    $obj->sql_query($uquery);
                    // rating
                    $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger' AND vRating1 != '' ";
                    $TripRateDatapass = $obj->MySQLSelect($checkrate);
                    if (!empty($TripRateDatapass)) {
                        $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                        $obj->sql_query($rateq);
                    } else {
                        $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                        $obj->sql_query($rateq);
                    }
                }
            }
        }
    }
    /*    $query = "UPDATE register_driver SET vCreditCard='',iTripId='0',vTripStatus='NONE',vStripeToken='',vStripeCusId='' WHERE iDriverId = '" . $iDriverId . "'";

      $obj->sql_query($query); */
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . 'reset successfully';
    header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']);
    exit;
}
//End Change All Selected Status
//Added By Hasmukh On 05-12-2018 For Hard Remove Driver all Data Start
function removedDriverData($driverId)
{
    global $obj, $allTables;
    //echo "<pre>";
    $tripIds = $cabIds = $cabRequestIds = "";
    $deleteTableArr = array();
    $deleteTableArr[] = array("table" => "register_driver", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_doc", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_location_airport", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_log_report", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_manage_timing", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_preferences", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_request", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_manage_timing", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_vehicle", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "home_driver", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "passenger_requests", "field" => "iDriverId", "ids" => $driverId);
    $deleteTableArr[] = array("table" => "driver_subscription_details", "field" => "iDriverId", "ids" => $driverId);
    $getTrips = $obj->MySQLSelect("SELECT iTripId FROM trips WHERE iDriverId='" . $driverId . "'");
    for ($t = 0; $t < scount($getTrips); $t++) {
        $tripIds .= ",'" . $getTrips[$t]['iTripId'] . "'";
    }
    if ($tripIds != "") {
        $tripIds = trim($tripIds, ",");
        $deleteTableArr[] = array("table" => "trips", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "temp_trips_delivery_locations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "temp_trip_order_details", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trips_delivery_locations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trips_locations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_call_masking", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_delivery_fields", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_destinations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_help_detail", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_messages", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_order_details", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_outstanding_amount", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_status_messages", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_times", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "driver_user_messages", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "ratings_user_driver", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "user_wallet", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "payments", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "driver_request", "field" => "iTripId", "ids" => $tripIds);
    }
    $getCabBooking = $obj->MySQLSelect("SELECT iCabBookingId FROM cab_booking WHERE iDriverId='" . $driverId . "'");
    for ($c = 0; $c < scount($getCabBooking); $c++) {
        $cabIds .= ",'" . $getCabBooking[$c]['iCabBookingId'] . "'";
    }
    if ($cabIds != "" || $driverId > 0) {
        $cabIds = trim($cabIds, ",");
        $cabIdWhere = "1 AND ";
        if ($cabIds != "") {
            $deleteTableArr[] = array("table" => "cab_booking", "field" => "iCabBookingId", "ids" => $cabIds);
            $cabIdWhere = " iCabBookingId IN($cabIds) OR";
        }
        $getCabRequest = $obj->MySQLSelect("SELECT iCabRequestId FROM cab_request_now WHERE $cabIdWhere iDriverId='" . $driverId . "'");
        for ($r = 0; $r < scount($getCabRequest); $r++) {
            $cabRequestIds .= ",'" . $getCabRequest[$r]['iCabRequestId'] . "'";
        }
        if ($cabRequestIds != "") {
            $cabRequestIds = trim($cabRequestIds, ",");
            $deleteTableArr[] = array("table" => "cab_request_now", "field" => "iCabRequestId", "ids" => $cabRequestIds);
        }
    }
    for ($j = 0; $j < scount($deleteTableArr); $j++) {
        $idsW = $deleteTableArr[$j]['ids'];
        $checkTable = checkTableExists($deleteTableArr[$j]['table'], $allTables);
        if ($checkTable == "1") {
            //echo "DELETE FROM " . $deleteTableArr[$j]['table'] . " WHERE " . $deleteTableArr[$j]['field'] . " IN($idsW)<br>";
            $obj->sql_query("DELETE FROM " . $deleteTableArr[$j]['table'] . " WHERE " . $deleteTableArr[$j]['field'] . " IN($idsW)");
        }
    }
}
//Added By Hasmukh On 05-12-2018 For Hard Remove Driver all Data End
?>