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
$hardDelete = 0;
if ($_SERVER['HTTP_HOST'] == "192.168.1.131" || $_SERVER['HTTP_HOST'] == "mobileappsdemo.com" || $_SERVER['HTTP_HOST'] == "webprojectsdemo.com" || $_SERVER['HTTP_HOST'] == "192.168.1.141") {
    $hardDelete = 1;
}
$allTables = getAllTableArray();
if ((strtolower($status) == 'active' || strtolower($statusVal) == 'active') && SITE_TYPE != 'Demo') {
    if ($iDriverId != "") {
        $driverIds = $iDriverId;
    }
    else {
        $driverIds = $checkbox;
    }
    $sql = "SELECT register_driver.iDriverId from register_driver LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId WHERE driver_vehicle.eStatus='Active' AND eType = 'TrackService'  AND  register_driver.iDriverId IN (" . $driverIds . ") GROUP BY register_driver.iDriverId";
    $Data = $obj->MySQLSelect($sql);

    if (scount($Data) <= 0) {
        $_SESSION['success'] = '3';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because either ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' has not added any vehicle or his added vehicle is not activated yet. Please try again after adding and activating the vehicle.';
        header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver.php?" . $parameters);
        exit;
    }
}
if (($statusVal == 'Deleted' || $method == 'delete') && ($iDriverId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-driver-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete ' . strtolower($langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]);
    }
    else {
        if ($iDriverId != "") {
            $driverIds = $iDriverId;
        }
        else {
            $driverIds = $checkbox;
        }
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE register_driver SET eStatus = 'Deleted', vPhone = concat(vPhone, '(Deleted)') WHERE iDriverId IN (" . $driverIds . ")";
            $obj->sql_query($query);
            $explodeId = explode(",", $driverIds);
            for ($i = 0; $i < scount($explodeId); $i++) {
             
                $queryIn = "INSERT INTO user_status_logs SET iUserId = " . $explodeId[$i] . ", eUserType = 'driver', dDate = '" . $date . "', eStatus = 'Deleted', iUpdatedBy = " . $_SESSION['sess_iAdminUserId'] . ", vIP = '" . $ip . "'";
                $obj->sql_query($queryIn);
            }
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        }
        else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver.php?" . $parameters);
    exit;
}
$rquery = "SELECT rd.iTrackServiceCompanyId,c.eStatus FROM register_driver as rd LEFT JOIN track_service_company as c on c.iTrackServiceCompanyId=rd.iTrackServiceCompanyId WHERE rd.iDriverId = '" . $iDriverId . "'";
$drvcdata = $obj->MySQLSelect($rquery);

$cStatus = $drvcdata[0]['eStatus'];
if ($iDriverId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-driver-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status ' . strtolower($langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]);
    }
    else {
        if (SITE_TYPE != 'Demo') {
            if ($cStatus == 'Inactive' && strtolower($status) == 'active') {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because company is not activated. Please try again after activating the company.';
               
            }
            else {
                /*--------------------- driver deleted duplicate check --------------------*/
                $checkUserDeleted = $obj->MySQLSelect("SELECT vPhone FROM register_driver WHERE eStatus = 'Deleted' AND iDriverId='" . $iDriverId . "'");
                if (!empty($checkUserDeleted)) {
                    $mobile = clearPhone($checkUserDeleted[0]['vPhone']);
                    $checkUserDeleted = $obj->MySQLSelect("SELECT vPhone FROM register_driver WHERE eStatus != 'Deleted' AND vPhone='" . $mobile . "' AND iDriverId !='" . $iDriverId . "' ");
                    if (!empty($checkUserDeleted)) {
                        $_SESSION['success'] = 2;
                        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_ADMIN_NOT_ABLE_ACTIVE_TEXT'];
                        header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver.php?" . $parameters);
                        exit;
                    }
                    else {
                        $query = "UPDATE register_driver SET vPhone = '" . $mobile . "' WHERE iDriverId = '" . $iDriverId . "'";
                        $checkUserDeleted = $obj->MySQLSelect($query);
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
                    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_ACTIVATE_MSG"];
                }
                else {
                    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_INACTIVATE_MSG"];
                }
            }
        }
        else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver.php?" . $parameters);
    exit;
}
$rquery1 = "SELECT rd.iCompanyId,c.eStatus,rd.iDriverId FROM register_driver as rd LEFT JOIN company as c on c.iCompanyId=rd.iCompanyId WHERE rd.iDriverId IN (" . $driverIds . ") AND c.eStatus='Active'";
$drvmultidata1 = $obj->MySQLSelect($rquery1);
$serviceDriverCompArr1 = array();
for ($h = 0; $h < scount($drvmultidata1); $h++) {
    $serviceDriverCompArr1[] = $drvmultidata1[$h]['iDriverId'];
}
$explodeData = explode(",", $driverIds);
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-driver-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status ' . strtolower($langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]);
    }
    else {
        if (scount($serviceDriverCompArr1) != scount($explodeData)) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because company is not activated. Please try again after activating the company.';
        }
        else {
            if (SITE_TYPE != 'Demo') {
                $query = "UPDATE register_driver SET eStatus = '" . $statusVal . "' WHERE iDriverId IN (" . $checkbox . ")";
                $obj->sql_query($query);
                $checkbox = explode(",", $checkbox);
                for ($i = 0; $i < scount($checkbox); $i++) {
                    /* Insert status log on user_log table */
                    $queryIn = "INSERT INTO user_status_logs SET iUserId = " . $checkbox[$i] . ", eUserType = 'driver', dDate = '" . $date . "', eStatus = '" . $statusVal . "', iUpdatedBy = " . $_SESSION['sess_iAdminUserId'] . ", vIP = '" . $ip . "'";
                    $obj->sql_query($queryIn);
                }
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            }
            else {
                $_SESSION['success'] = 2;
            }
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver.php?" . $parameters);
    exit;
}
if ($method == 'reset' && $iDriverId != '') {
    $q = "SELECT iTripId,vTripStatus FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
    $drvdata = $obj->MySQLSelect($q);
    if (!empty($drvdata) && $drvdata[0]['iTripId'] != '0') {
        $sql = "SELECT iTripId,iActive,iDriverId,iUserId FROM trips WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
        $TripData = $obj->MySQLSelect($sql);
        $userquery = "SELECT iTripId,vTripStatus FROM register_user WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
        $useData = $obj->MySQLSelect($userquery);
        if ($TripData[0]['iActive'] == 'On Going Trip') {
            $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $iDriverId . "'";
            $obj->sql_query($query);
            $query1 = "UPDATE trips SET iActive='Finished',tEndDate = NOW() WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
            $obj->sql_query($query1);
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver' AND vRating1 != '' ";
            $TripRateDatadriver = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatadriver)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
                $obj->sql_query($rateq);
            }
            else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                $obj->sql_query($rateq);
            }
            if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                $obj->sql_query($uquery);
                $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger' AND vRating1 != '' ";
                $TripRateDatapass = $obj->MySQLSelect($checkrate);
                if (!empty($TripRateDatapass)) {
                    $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                    $obj->sql_query($rateq);
                }
                else {
                    $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                    $obj->sql_query($rateq);
                }
            }
        }
        else if ($TripData[0]['iActive'] == 'Active') {
            $aquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
            $obj->sql_query($aquery);
            $qu1 = "UPDATE trips SET iActive = 'Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='Driver', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "'";
            $obj->sql_query($qu1);
            if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                $uquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                $obj->sql_query($uquery);
            }
        }
        else {
            if ($TripData[0]['iActive'] == 'Canceled') {
                if ($drvdata[0]['vTripStatus'] != 'Cancelled' && $drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $dquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
                    $obj->sql_query($dquery);
                }
                if ($useData[0]['vTripStatus'] != 'Cancelled' && $useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $rquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                    $obj->sql_query($rquery);
                }
            }
            else {
                if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $iDriverId . "'";
                    $obj->sql_query($query);
                    $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver' AND vRating1 != '' ";
                    $TripRateDatadriver = $obj->MySQLSelect($checkrate);
                    if (!empty($TripRateDatadriver)) {
                        $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Driver'";
                        $obj->sql_query($rateq);
                    }
                    else {
                        $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                        $obj->sql_query($rateq);
                    }
                }
                if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
                    $obj->sql_query($uquery);
                    $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger' AND vRating1 != '' ";
                    $TripRateDatapass = $obj->MySQLSelect($checkrate);
                    if (!empty($TripRateDatapass)) {
                        $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $drvdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                        $obj->sql_query($rateq);
                    }
                    else {
                        $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $drvdata[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                        $obj->sql_query($rateq);
                    }
                }
            }
        }
    }
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . 'reset successfully';
    header("Location:" . $tconfig["tsite_url_main_admin"] . "track_service_driver.php");
    exit;
}


?>