<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-trip-jobs')) {
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'Trips';
$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';
$ord = ' ORDER BY t.iTripId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY t.eType ASC"; else

        $ord = " ORDER BY t.eType DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY t.tTripRequestDate ASC"; else

        $ord = " ORDER BY t.tTripRequestDate DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY c.vCompany ASC"; else

        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY d.vName ASC"; else

        $ord = " ORDER BY d.vName DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY u.vName ASC"; else

        $ord = " ORDER BY u.vName DESC";
}
//End Sorting
if(isset($_REQUEST['serachJobNo'])) {
    $_REQUEST['serachTripNo'] = $_REQUEST['serachJobNo'];
}
$serverTimeZone = date_default_timezone_get();
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchKiosk = isset($_REQUEST['searchKiosk']) ? $_REQUEST['searchKiosk'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$subType = isset($_REQUEST['subType']) ? $_REQUEST['subType'] : '';

if($vStatus == "onJob")
{
    $vStatus = "onRide";
}

if ($startDate != '') {
    //$ssql .= " AND Date(t.tTripRequestDate) >='" . $startDate . "'";
   $ssql .= " AND DATE(CONVERT_TZ(t.tTripRequestDate, '".$serverTimeZone."', t.vTimeZone)) >= '" . $startDate . "'";
}
if ($endDate != '') {
   // $ssql .= " AND Date(t.tTripRequestDate) <='" . $endDate . "'";
     $ssql .= " AND DATE(CONVERT_TZ(t.tTripRequestDate, '".$serverTimeZone."', t.vTimeZone)) <= '" . $endDate . "'";
}
if ($serachTripNo != '') {
    $ssql .= " AND t.vRideNo ='" . $serachTripNo . "'";
}
if ($searchCompany != '') {
    $ssql .= " AND t.iCompanyId ='" . $searchCompany . "'";
}
if ($searchDriver != '') {
    $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";
}
if ($searchRider != '') {
    $ssql .= " AND t.iUserId ='" . $searchRider . "'";
}
if ($searchKiosk != '') {
    $ssql .= " AND t.iHotelId ='" . $searchKiosk . "'";
}
if ($vStatus == "onRide") {
    $ssql .= " AND (t.iActive = 'On Going Trip' OR t.iActive = 'Active' OR t.iActive = 'Arrived' ) AND t.eCancelled='No'";
} else if ($vStatus == "cancel") {
    $ssql .= " AND (t.iActive = 'Canceled' OR t.eCancelled='yes')";
} else if ($vStatus == "complete") {
    $ssql .= " AND t.iActive = 'Finished' AND t.eCancelled='No'";
}

//$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable();
$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = $MODULES_OBJ->isRideFeatureAvailable() ? "Yes" : "No";
$deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable() ? "Yes" : "No";
$isEnableSafetyTools = $MODULES_OBJ->isEnableSafetyTools();

if ($ufxEnable != 'Yes') {
    $ssql .= " AND t.eType != 'UberX'";
}
if (!$MODULES_OBJ->isAirFlightModuleAvailable(1)) {
    $ssql .= " AND t.iFromStationId = '0' AND t.iToStationId = '0'";
}
if ($rideEnable != "Yes") {
    $ssql .= " AND t.eType != 'Ride'";
}
if ($deliveryEnable != "Yes") {
    $ssql .= " AND t.eType != 'Deliver' AND t.eType != 'Multi-Delivery'";
}
if (!empty($promocode) && isset($promocode)) {
    $ssql .= " AND t.vCouponCode LIKE '" . $promocode . "'";
}
if (scount($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
}
if ($eType != '') {

    if ($eType == 'Ride' && $subType == '') {
        $subquery = "";
        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
            //$subquery = " AND t.iRentalPackageId = 0 AND t.eHailTrip = 'No'";
        }
        $ssql .= " AND t.eType ='" . $eType . "' $subquery";
        //if($MODULES_OBJ->isAirFlightModuleAvailable(1)) {
        $ssql .= " AND  t.iFromStationId = 0 AND t.iToStationId = 0 ";
        //}
    }
    else if ($eType == 'VideoConsultation') {
        $ssql .= " AND t.eType ='UberX' AND t.isVideoCall = 'Yes'";
    }
    elseif ($eType == 'Ride' && $subType == 'RentalRide') {
        $ssql .= " AND t.eType ='Ride' AND t.iRentalPackageId > 0 AND t.eIsInterCity = 'No'";
    }  elseif ($eType == 'Ride' && $subType == 'InterCity') {
        $ssql .= " AND t.eType ='Ride' AND t.eIsInterCity = 'Yes'";
    }elseif ($eType == 'Ride' && $subType == 'TaxiBid') {
        $ssql .= " AND t.eType ='Ride' AND t.isTaxiBid = 'Yes'";
    }elseif ($eType == 'Ride' && $subType == 'HailRide') {
        $ssql .= " AND t.eType ='Ride' AND t.eHailTrip = 'Yes'";
    } else if ($eType == 'Ride' && $subType == "Pool") {
        $ssql .= " AND t.eType ='Ride' AND t.ePoolRide = 'Yes'";
    } else if ($eType == "Fly") {
        $ssql .= " AND t.eType ='Ride' AND t.iFromStationId != 0 AND t.iToStationId != 0 AND t.iRentalPackageId = 0 ";
    } else if($eType == "Deliver" && empty($subType))
    {
        $ssql .= " AND t.eType ='Multi-Delivery'";
    }

    else if ($eType == "Deliver" && $subType == "Single-Delivery") {
        $ssql .= " AND t.eType ='Multi-Delivery' HAVING totalDeliveryTrips = 1";
    } else if ($eType == "Deliver" && $subType == "Multi-Delivery") {
        $ssql .= " AND t.eType ='Multi-Delivery' HAVING totalDeliveryTrips > 1";
    } else {
        $ssql .= " AND t.eType ='" . $eType . "'";
    }
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And t.tTripRequestDate > '" . WEEK_DATE . "'";
}
$hotelQuery = "";
if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == 'hotel') {

    $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
    $hotelQuery = " AND (t.eBookingFrom = 'Hotel' || t.eBookingFrom = 'Kiosk') AND t.iHotelBookingId = '" . $iHotelBookingId . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$totalData = $obj->MySQLSelect("SELECT count(t.iTripId) as trips_total,(SELECT COUNT(tl.iTripDeliveryLocationId) AS Total FROM trips_delivery_locations as tl WHERE 1=1 AND tl.iActive='Finished' AND t.iTripId=tl.iTripId) as totalDeliveryTrips FROM trips t WHERE 1=1 AND t.eSystem = 'General' $trp_ssql $hotelQuery $ssql "); // New Query By HJ On 09-09-2020

$total_results = $totalData[0]['trips_total'];




$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End






$audio_sql = "";
if($isEnableSafetyTools && $MODULES_OBJ->isRideFeatureAvailable('Yes')) {
    $audio_sql = ", (SELECT COUNT(ssm.iAudioFileId) FROM service_safety_media as ssm WHERE ssm.iTripId = t.iTripId) as totalAudioRecordings";
}
$sql1 = '(SELECT (SELECT vcs.vCategory_' . $default_lang . ' FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc2.iParentId) as ParentCategoryName FROM vehicle_category as vc2 WHERE vc2.iVehicleCategoryId = REPLACE(JSON_UNQUOTE(JSON_VALUE(t.tVehicleTypeFareData, "$.FareData[0].id")), "\"", "") ) ';
$sql = "SELECT (CASE WHEN t.eType = 'UberX' THEN {$sql1} ELSE '' END) as vCategory, t.isTaxiBid,t.iFromStationId, t.iToStationId,c.iCompanyId,t.ePoolRide,t.tStartDate,t.eIsInterCity,t.tEndDate,t.vTimeZone, t.tTripRequestDate,t.eBookingFrom,t.vCancelReason,t.vCancelComment,t.iCancelReasonId, t.eHailTrip, t.iUserId, t.iFare, t.eType, t.iDriverId, t.tSaddress, t.vRideNo, t.tDaddress,  t.fWalletDebit, t.eCarType, t.iTripId, t.iActive, t.fCancellationFare, t.eCancelledBy, t.eCancelled,    t.iRentalPackageId, t.iHotelBookingId , CONCAT(u.vName,' ',u.vLastName) AS riderName, CONCAT(d.vName,' ',d.vLastName) AS driverName, d.vAvgRating,t.vDeliveryConfirmCode, c.vCompany, vt.vVehicleType_{$default_lang} as vVehicleType, vt.vRentalAlias_{$default_lang} as vRentalVehicleTypeName,t.tVehicleTypeData,t.tVehicleTypeFareData,t.eAskCodeToUser,t.vRandomCode,t.fTax1,t.fTax2,t.isVideoCall,(SELECT COUNT(tl.iTripDeliveryLocationId) AS Total FROM trips_delivery_locations as tl WHERE 1=1 AND tl.iActive='Finished' AND t.iTripId=tl.iTripId) as totalDeliveryTrips $audio_sql
    

        FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId 

        LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId

        LEFT JOIN vehicle_category vc ON vt.iVehicleCategoryId = vc.iVehicleCategoryId

        LEFT JOIN  register_user u ON t.iUserId = u.iUserId 

        LEFT JOIN company c ON c.iCompanyId=d.iCompanyId 

        WHERE 1=1 AND t.eSystem = 'General' {$ssql} {$trp_ssql} {$hotelQuery} {$ord} LIMIT {$start}, {$per_page}";


$db_trip = $obj->MySQLSelect($sql);

$endRecord = scount($db_trip);
$driverIdArr = $userIdArr = array();
$userIdArrHotel = $driverIdArrHotel = '';
if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == 'hotel') {
    foreach ($db_trip as $dtps) {
        $driverIdArr[] = $dtps['iDriverId'];
        $userIdArr[] = $dtps['iUserId'];
    }
    $userIdArrHotel = implode(",", $userIdArr);
    $driverIdArrHotel = implode(",", $driverIdArr);
}
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$Pmonday = date('Y-m-d', strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d', strtotime('sunday this week -1 week'));
//Added By HJ On 31-08-2019 For Reset Trip Functionality Start
if ($method == 'reset' && $iTripId != '') {
    $TripData = $obj->MySQLSelect("SELECT iTripId,iActive,iDriverId,iUserId,vRideNo,eType FROM trips WHERE iTripId = '" . $iTripId . "'");
    $iDriverId = $TripData[0]['iDriverId'];
    $iUserId = $TripData[0]['iUserId'];
    $vRideNo = $TripData[0]['vRideNo'];
    //$vTripStatus = "Not Active"; // Commented By HJ On 06-02-2020 For Solved User all Crash Issue
    $vTripStatus = "NONE"; // Added By HJ On 06-02-2020 For Solved User all Crash Issue
    if ($TripData[0]['iActive'] == 'Active') {
        $vTripStatus = "Cancelled";
    }
    //Get Driver Data For Send Notification Start
    $drvdata = $obj->MySQLSelect("SELECT iTripId,vTripStatus,tSessionId,iGcmRegId,eDeviceType,CONCAT(vName,' ',vLastName) AS driverName,eAppTerminate,eDebugMode,eHmsDevice,vLang FROM register_driver WHERE iDriverId = '" . $iDriverId . "' AND iTripId = '" . $iTripId . "'");
    $tSessionId = $drvdata[0]['tSessionId'];
    $iGcmRegId = $drvdata[0]['iGcmRegId'];
    $eDeviceType = $drvdata[0]['eDeviceType'];
    $eAppTerminate = $drvdata[0]['eAppTerminate'];
    $eDebugMode = $drvdata[0]['eDebugMode'];
    $eHmsDevice = $drvdata[0]['eHmsDevice'];
    //Get Driver Data For Send Notification End
    //Get User Data For Send Notification Start
    $userdata = $obj->MySQLSelect("SELECT tSessionId,iGcmRegId,eDeviceType,vLang,eAppTerminate,eDebugMode,eHmsDevice FROM register_user WHERE iUserId = '" . $iUserId . "'");
    $iGcmRegIdUser = $userdata[0]['iGcmRegId'];
    $eDeviceTypeUser = $userdata[0]['eDeviceType'];
    $eAppTerminateUser = $userdata[0]['eAppTerminate'];
    $eDebugModeUser = $userdata[0]['eDebugMode'];
    $eHmsDeviceUser = $userdata[0]['eHmsDevice'];

    //Get User Data For Send Notification End
    if ($TripData[0]['iActive'] != 'Finished' && $TripData[0]['iActive'] != 'Canceled') {
        $date = date("Y:m:d H:i:s");
        $obj->sql_query("UPDATE trips SET iActive='Canceled',tEndDate = '" . $date . "',eCancelled = 'Yes', eCancelledBy='Admin', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $iTripId . "'");
        $obj->sql_query("UPDATE register_driver SET vTripStatus='" . $vTripStatus . "',iTripId=0 WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'  AND iTripId = '" . $iTripId . "'");
        $obj->sql_query("UPDATE register_user SET vTripStatus='" . $vTripStatus . "',iTripId=0 WHERE iUserId = '" . $iUserId . "'  AND iTripId = '" . $iTripId . "'");
        /* Added by HV on 07-01-2021 related to mantis issue #15741 comment following */
        // added by SP on 19-01-2021 related to mantis #0017905 so uncomment it..
        $whereTrip = " iTripId = '$iTripId'";
        $Data_update_trips['iActive'] = 'Canceled';
        $Data_update_trips['tEndDate'] = @date("Y-m-d H:i:s");
        if ($tStartDate == "0000-00-00 00:00:00") {
            $Data_update_trips['tStartDate'] = @date("Y-m-d H:i:s");
        }
        if ($tDriverArrivedDate == "0000-00-00 00:00:00") {
            $Data_update_trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
        }
        $Data_update_trips['fCancellationFare'] = 0;
        $Data_update_trips['fTax1'] = 0;
        $Data_update_trips['fTax1Percentage'] = 0;
        $Data_update_trips['fTax2'] = 0;
        $Data_update_trips['fTax2Percentage'] = 0;
        $Data_update_trips['fTripGenerateFare'] = $Data_update_trips['iFare'] = $Data_update_trips['iBaseFare'] = $Data_update_trips['fCommision'] = 0;
        $Data_update_trips['fDiscount'] = $Data_update_trips['fSurgePriceDiff'] = 0;
        $Data_update_trips['vCouponCode'] = "";
        if ($TripData[0]['eType'] == "Multi-Delivery") {
            $endtime = @date("Y-m-d H:i:s");
            $updateQuery = "UPDATE trips_delivery_locations set iActive='Canceled',tEndTime='" . $endtime . "'  WHERE iTripId = '" . $iTripId . "'";
            $obj->sql_query($updateQuery);
        }
        $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $whereTrip);
        /* Added by HV on 07-01-2021 related to mantis issue #15741 End */
        $dbAllTableArr = getAllTableArray();
        $tripDeliveryLocationTable = 0;
        if (in_array("trips_delivery_locations", $dbAllTableArr)) {
            $tripDeliveryLocationTable = 1;
        }
        if ($tripDeliveryLocationTable == 1) {
            $obj->sql_query("UPDATE trips_delivery_locations SET iActive='Canceled' WHERE iTripId = '" . $iTripId . "'");
        }
        if ($TripData[0]['iActive'] != 'Active') {
            $TripRateDatadriver = $obj->MySQLSelect("SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver' AND vRating1 != '' ");
            if (scount($TripRateDatadriver) > 0) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver'";
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Driver','')";
            }
            $obj->sql_query($rateq);
            $TripRateDatapass = $obj->MySQLSelect("SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger' AND vRating1 != '' ");
            if (scount($TripRateDatapass) > 0) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger'";
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Passenger','')";
            }
            $obj->sql_query($rateq);
        }
        $tMessage = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . ' Reset successfully.';
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . ' Reset successfully.';
        $var_filterd = "";
        foreach ($_REQUEST as $key => $val) {
            if ($key == "method" || $key == 'iTripId' || $key == 'iServiceId') {
                $var_filterd .= "&$key=";
            } else {
                $var_filterd .= "&$key=" . $val;
            }
        }
        $driverLanguageCode = $drvdata[0]['vLang'];
        if (empty($driverLanguageCode)) {
            $DriverLanguageLabelsArr = $langage_lbl_admin;
        } else {
                $DriverLanguageLabelsArr = $LANG_OBJ->FetchLanguageLabels($driverLanguageCode, "1");
        }

        //Added BY HJ On 30-09-2019 For Send Notification to Driver When Cancel/Reset Trip From hotel Panel Start
        $tMessageNotification = "#" . $vRideNo . "" . str_replace("This", "", $DriverLanguageLabelsArr['LBL_CANCELED_TRIP_ADMIN_TXT']) . " " . $DriverLanguageLabelsArr['LBL_CANCELLED_BY_ADMIN'];
        $MsgType = "TripCancelled";
        $message_arr = array();
        $message_arr['Message'] = $message_arr['MsgType'] = $MsgType;
        $message_arr['vTitle'] = $tMessageNotification;
        $message_arr['eType'] = $eType;
        $message_arr['iTripId'] = $iTripId;
        $message_arr['uString'] = time();
        $alertMsg = $message_arr['vTitle'];
        $generalDataArr = array();
        $generalDataArr[] = array(
            'eDeviceType'   => $eDeviceType,
            'deviceToken'   => $iGcmRegId,
            'alertMsg'      => $alertMsg,
            'eAppTerminate' => $eAppTerminate,
            'eDebugMode'    => $eDebugMode,
            'eHmsDevice'    => $eHmsDevice,
            'message'       => $message_arr,
        );
       
        $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_PROVIDER);
        //For Send To Driver App End
        //For send To User App Start
        $userLanguageCode = $userdata[0]['vLang'];
        if (empty($userLanguageCode)) {
            $userLanguageLabelsArr = $langage_lbl_admin;
        } else {
                $userLanguageLabelsArr = $LANG_OBJ->FetchLanguageLabels($userLanguageCode, "1");
        }
        $tMessageNotification = "#" . $vRideNo . "" . str_replace("This", "", $userLanguageLabelsArr['LBL_CANCELED_TRIP_ADMIN_TXT']) . " " . $userLanguageLabelsArr['LBL_CANCELLED_BY_ADMIN'];
        $message_arr = array();
        $message_arr['Message'] = $message_arr['MsgType'] = $MsgType;
        $message_arr['vTitle'] = $tMessageNotification;
        $message_arr['eType'] = $eType;
        $message_arr['iTripId'] = $iTripId;
        $message_arr['uString'] = time();
        $message_arr['tSessionId'] = $userdata[0]['tSessionId'];
        $message_arr['time'] = strval(time());
        $message_arr['eSystem'] = "";
        //added by SP on 17-02-2021 for custom notification
        $message_arr['CustomNotification'] = $MODULES_OBJ->isEnableCustomNotification() ? "Yes" : "No";
        //these two btn CustomViewBtn,CustomTrackDetails whether shown in app or not
        $message_arr['CustomViewBtn'] = "No";
        $message_arr['CustomTrackDetails'] = "No";
        $message_arr['LBL_VIEW_DETAILS'] = $langage_lbl_admin['LBL_VIEW_DETAILS'];
        $message_arr['LBL_TRACK_ORDER'] = $langage_lbl_admin['LBL_TRACK_ORDER'];
        //here change name of function beocz in include_generalfunction_dl same fun name for order so i have change name here..
        $customNotiArray = GetCustomNotificationDetailsRDU($iTripId, $message_arr['vTitle'], $userdata[0]['vLang']);
        //title and sub description shown in custom notification
        $message_arr['CustomTitle'] = $customNotiArray[0]['vCurrentStatus'];
        $message_arr['CustomSubTitle'] = $customNotiArray[0]['vCurrentStatus_Track'];
        $message_arr['CustomMessage'] = $customNotiArray;
        $alertMsg = $message_arr['vTitle'];
        $generalDataArr = array();
        $generalDataArr[] = array(
            'eDeviceType'   => $eDeviceTypeUser,
            'deviceToken'   => $iGcmRegIdUser,
            'alertMsg'      => $alertMsg,
            'eAppTerminate' => $eAppTerminateUser,
            'eDebugMode'    => $eDebugModeUser,
            'eHmsDevice'    => $eHmsDeviceUser,
            'message'       => $message_arr,
        );

        $message_arr['LiveActivityData'] = getTripLiveActivity($iTripId);
        $message_arr['LiveActivity'] = "Yes";
        $message_arr['LiveActivityEnd'] = "Yes";

       /* $generalDataArr[] = array(
            'eDeviceType'   => $eDeviceTypeUser,
            'deviceToken'   => $tDeviceLiveActivityToken,
            'alertMsg'      => $alertMsg,
            'eAppTerminate' => $eAppTerminateUser,
            'eDebugMode'    => $eDebugModeUser,
            'eHmsDevice'    => $eHmsDeviceUser,
            'message'       => $message_arr,
        );*/
        //echo"<pre>";print_R($generalDataArr);die;
        $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_USER);
        //For send To User App End
        //Added BY HJ On 30-09-2019 For Send Notification to Driver When Cancel/Reset Trip From hotel Panel End
        if ($MODULES_OBJ->isSendRequestToDriverBeforFinishTripModule()) {
            sendRequestToDriverNextTrip($iDriverId);
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "trip.php?" . $var_filterd);
    exit;
}
//Added By HJ On 31-08-2019 For Reset Trip Functionality End
if ($method == 'reset_old' && $iTripId != '') { // Rename reset to reset_old BY HJ On 31-08-2019 For Optimized Code As Per Above Condition
    $sql = "SELECT iTripId,iActive,iDriverId,iUserId FROM trips WHERE iTripId = '" . $iTripId . "'";
    $TripData = $obj->MySQLSelect($sql);
    $userquery = "SELECT iTripId,vTripStatus FROM register_user WHERE iUserId = '" . $TripData[0]['iUserId'] . "' AND iTripId = '" . $iTripId . "'";
    $useData = $obj->MySQLSelect($userquery);
    // driver
    $q = "SELECT iTripId,vTripStatus FROM register_driver WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "' AND iTripId = '" . $iTripId . "'";
    $drvdata = $obj->MySQLSelect($q);
    if ($TripData[0]['iActive'] == 'On Going Trip') {
        // trip
        $query1 = "UPDATE trips SET iActive='Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='Admin', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $iTripId . "'";
        $obj->sql_query($query1);
        if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
            // driver
            $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'  AND iTripId = '" . $iTripId . "'";
            $obj->sql_query($query);
            // rating
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver' AND vRating1 != '' ";
            $TripRateDatadriver = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatadriver)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Driver','')";
                $obj->sql_query($rateq);
            }
            // rating
        }
        if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
            // user
            $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
            $obj->sql_query($uquery);
            // rating
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger' AND vRating1 != '' ";
            $TripRateDatapass = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatapass)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Passenger','')";
                $obj->sql_query($rateq);
            }
        }
    } else if ($TripData[0]['iActive'] == 'Active') {
        // trip
        $qu1 = "UPDATE trips SET iActive = 'Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='Admin', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $iTripId . "'";
        $obj->sql_query($qu1);
        if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
            // driver
            $aquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
            $obj->sql_query($aquery);
        }
        // user
        if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
            // user
            $uquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
            $obj->sql_query($uquery);
        }
    } else {
        // Driver
        if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
            $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
            $obj->sql_query($query);
            // rating
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Driver' AND vRating1 != '' ";
            $TripRateDatadriver = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatadriver)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Driver'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $TripData[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                $obj->sql_query($rateq);
            }
        }
        // Rider
        if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
            // user
            $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
            $obj->sql_query($uquery);
            // rating
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Passenger' AND vRating1 != '' ";
            $TripRateDatapass = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatapass)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Passenger'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $TripData[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                $obj->sql_query($rateq);
            }
        }
    }
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . '  Reset successfully.';
    foreach ($_REQUEST as $key => $val) {
        if ($key == "method" || $key == 'iTripId' || $key == 'iServiceId') {
            $var_filterd .= "&$key=";
        } else {
            $var_filterd .= "&$key=" . $val;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "trip.php?" . $var_filterd);
    exit;
}
$vehilceTypeArr = array();
// [HP] 25-10-2021 #region  : on "view service" click popup display type, sub service ,service
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType , vc.vCategory_" . $default_lang . " AS subService, vcc.vCategory_" . $default_lang . " AS Service FROM vehicle_type left join " . $sql_vehicle_category_table_name . " as vc on vehicle_type.iVehicleCategoryId = vc.iVehicleCategoryId left join " . $sql_vehicle_category_table_name . " as vcc on vc.iParentId = vcc.iVehicleCategoryId WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId'] . "_subService"] = $getVehicleTypes[$r]['subService'];
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId'] . "_service"] = $getVehicleTypes[$r]['Service'];
}
//$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');
$kioskupdatedAry = array();
if ($hotelPanel > 0 || $kioskPanel > 0) {
    $kioskAry = $obj->MySQLSelect("SELECT iAdminId, concat(vFirstName,' ',vLastName) as kioskname FROM `administrators` WHERE iGroupId='4'");
    foreach($kioskAry as $key => $value){
        $kioskupdatedAry[$value['iAdminId']] = $value['kioskname'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!-- <meta http-equiv="refresh" content="60"/> -->
    <?php include_once('global_files.php'); ?>
    <style type="text/css">
        .form-group .row {

            padding: 0;

        }

        .pending-trip {

            cursor: pointer;

            position: absolute;

            margin: 2px 0 0 5px;

        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>

    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> </h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page">
                    <input type="hidden" name="action" value="search"/>
                    <h3>Search <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> ...</h3>
                    <span>

                            <a style="cursor:pointer"
                               onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>

                            <a style="cursor:pointer"
                               onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>

                        </span>
                </div>
                <div class="Posted-date form-group">
                    <div class="row">
                        <div class="col-lg-3">
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control" name='vStatus'>
                                <option value="">All Status</option>
                                <option value="onRide" <?php
                                if ($vStatus == "onRide") {
                                    echo "selected";
                                }
                                ?>>On Going <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> </option>
                                <option value="complete" <?php
                                if ($vStatus == "complete") {
                                    echo "selected";
                                }
                                ?>>Completed
                                </option>
                                <option value="cancel" <?php
                                if ($vStatus == "cancel") {
                                    echo "selected";
                                }
                                ?>>Cancelled
                                </option>
                            </select>
                        </div>
                        <!-- <div class="col-lg-3">

                                <?php
                        //echo getSelect($user_locations_select_data, 'locations', ['class' => "form-control"]);
                        ?>

                                </div> -->
                        <div class="col-lg-3">
                            <input type="text" id="serachTripNo" name="serachTripNo"
                                   placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number"
                                   class="form-control search-trip001" value="<?= $serachTripNo; ?>"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name='searchCompany' id="searchCompany"
                                    data-text="Select Company">
                                <option value="">Select Company</option>
                            </select>
                        </div>
                    <?php } ?>
                    <div class="col-lg-3">
                        <select class="form-control filter-by-text driver_container" name='searchDriver'
                                data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">
                            <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control filter-by-text" name='searchRider'
                                data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>"
                                id="searchRider">
                            <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                        </select>
                    </div>

                    <?php if($MODULES_OBJ->isRideFeatureAvailable('Yes')){ ?>
                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name='searchKiosk'
                                    data-text="Select <?= $langage_lbl_admin['LBL_KIOSK']; ?>"
                                    id="searchKiosk">
                                <option value="">Select <?= $langage_lbl_admin['LBL_KIOSK']; ?></option>
                            </select>
                        </div>
                    <?php } ?>

                    <input type="hidden" name="searchDriverHotel" id="searchDriverHotel"
                           value="<?= $driverIdArrHotel ?>">
                    <input type="hidden" name="searchRiderHotel" id="searchRiderHotel" value="<?= $userIdArrHotel ?>">
                    <?php
                    if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                        if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') {
                            ?>


                            <div class="col-lg-3">
                                <select id = "eTypeSearch" class="form-control" name='eType'>
                                    <option value="">Service Type</option>
                                    <?php if ($rideEnable == 'Yes') { ?>
                                        <option value="Ride" <?php
                                        if ($eType == "Ride") {
                                            echo "selected";
                                        }
                                        ?>><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                        <?php
                                    }
                                    if ($deliveryEnable == "Yes") { ?>
                                        <option value="Deliver" <?php
                                        if ($eType == "Deliver") {
                                            echo "selected";
                                        }
                                        ?>>Delivery
                                        </option>

                                        <?php
                                    }
                                    if ($ufxEnable == "Yes") { ?>
                                        <option value="UberX" <?php if ($eType == "UberX") { echo "selected"; } ?> >Other Services
                                        </option>

                                        <option value="VideoConsultation" <?php if ($eType == "VideoConsultation") { echo "selected"; } ?> >video consultation</option>
                                    <?php } ?>


                                    <?php
                                    if ($MODULES_OBJ->isAirFlightModuleAvailable(1)) { ?>
                                        <option value="Fly" <?php
                                        if ($eType == "Fly") {
                                            echo "selected";
                                        }
                                        ?>><?= $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE']; ?> </option>
                                    <? } ?>
                                </select>
                            </div>

                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
                    if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                        if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') {
                            ?>    
                <div class="row margin-top-15">
                    <div class="col-lg-3">
                        <select id = "subType" class="form-control" name="subType">
                            <option  value=""> Select Services</option>

                            <?php if ($ENABLE_HAIL_RIDES == "Yes" && $APP_TYPE != 'Delivery') { ?>


                            <option <?php if ($subType == "HailRide") { echo "selected"; } ?>
                                    class="subFilter" attr-eType = "Ride" value="HailRide"> Hail <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                            <?php } ?>

                            <?php  if (ENABLE_RENTAL_OPTION == 'Yes' && $APP_TYPE != 'Delivery') { ?>
                            <option  <?php if ($subType == "RentalRide") {  echo "selected"; } ?>
                                    class="subFilter" attr-eType = "Ride" value="RentalRide">Rental Ride</option>
                            <?php } ?>


                            <?php if ($MODULES_OBJ->isInterCityFeatureAvailable() && $APP_TYPE != 'Delivery') { ?>

                            <option <?php if ($subType == "InterCity") { echo "selected"; } ?> class="subFilter" attr-eType = "Ride" value="InterCity">InterCity Ride
                            </option>

                            <?php } ?>

                            <?php if ($rideEnable == 'Yes') { ?>
                                <?php if ($MODULES_OBJ->isEnableTaxiBidFeature()) { ?>
                                    <option <?php if ($subType == "TaxiBid") { echo "selected"; } ?> class="subFilter" attr-eType = "Ride" value="TaxiBid">Taxi Bid Ride</option>
                                <?php } ?>

                                <?php if ($rideEnable == "Yes" && $PACKAGE_TYPE == "SHARK" &&  $MODULES_OBJ->isEnableRideShareService()) { ?>
                                    <option <?php if ($subType == "Pool") { echo "selected"; } ?>
                                            class="subFilter" attr-eType = "Ride" value="Pool">
                                        Ride Pool</option>
                                <?php } ?>
                            <?php } ?>

                            <?php if ($deliveryEnable == "Yes") { ?>

                                <?php if (ENABLE_MULTI_DELIVERY == "Yes") { ?>
                                    <option
                                        <?php if ($subType == "Multi-Delivery") {
                                            echo "selected";
                                        } ?>
                                            class="subFilter" attr-eType="Deliver" value="Multi-Delivery">Multi-Delivery
                                    </option>
                                <?php } ?>
                                <option
                                    <?php if ($subType == "Single-Delivery") {
                                        echo "selected";
                                    } ?>
                                        class="subFilter" attr-eType="Deliver" value="Single-Delivery">Delivery(Single)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            <?php } } ?>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'trip.php'"/>
                        <?php if (!empty($db_trip) && $userObj->hasPermission('export-trip-jobs')) { ?>
                            <button type="button" onClick="reportExportTypes('triplist')" class="export-btn001"
                                    style="float:none;">Export
                            </button>
                        <?php } ?>
                    </b>
                </div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
						 
                            <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php

                                        if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
                                            if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                                <th width="10%" class="align-left">
                                                    <a href="javascript:void(0);"
                                                       onClick="Redirect(1,<?php
                                                       if ($sortby == '1') {
                                                           echo $order;
                                                       } else {
                                                           ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']; ?> <?php
                                                        if ($sortby == 1) {
                                                            if ($order == 0) {
                                                                ?>
                                                                <i class="fa fa-sort-amount-asc"
                                                                   aria-hidden="true"></i> <?php } else { ?>
                                                                <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                    </a>
                                                </th>
                                                <?php
                                            }
                                        }
                                        ?>

                                        <?php
                                        if ($hotelPanel > 0 || $kioskPanel > 0) {
                                            ?>
                                            <th class="align-center">Booked By</th>
                                            <?php
                                        }
                                        ?>
                                        <th class="align-center"><?= $langage_lbl_admin['LBL_TRIP_NO_ADMIN']; ?></th>
                                        <th>Address</th>
                                        <th width="8%" class="align-left">
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(2, <?php
                                               if ($sortby == '2') {
                                                   echo $order;
                                               } else {
                                                   ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?>

                                                <?php if ($sortby == 2) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i  class="fa fa-sort-amount-desc"  aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                            <th width="12%">
                                                <a href="javascript:void(0);" onClick="Redirect(3,

                                                <?php
                                                if ($sortby == '3') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)">Company <?php if ($sortby == 3) {
                                                        if ($order == 0) { ?>
                                                            <i class="fa fa-sort-amount-asc"
                                                               aria-hidden="true"></i> <?php } else { ?>
                                                            <i class="fa fa-sort-amount-desc"
                                                               aria-hidden="true"></i><?php }
                                                    } else { ?>
                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                </a>
                                            </th>
                                        <?php } ?>
                                        <th width="12%">
                                            <a href="javascript:void(0);" onClick="Redirect(4,

                                            <?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>

                                                <?php if ($sortby == 4) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="12%">
                                            <a href="javascript:void(0);" onClick="Redirect(5,

                                            <?php
                                            if ($sortby == '5') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>

                                                <?php
                                                if ($sortby == 5) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i
                                                                class="fa fa-sort-amount-desc"
                                                                aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>

                                        <?php if($isEnableSafetyTools && $MODULES_OBJ->isRideFeatureAvailable('Yes')) { ?>
                                        <th>Safety Audio Recordings</th>
                                        <?php } ?>
                                        <th width="8%" class="align-right"><?= $langage_lbl_admin['LBL_DRIVER_TRIP_FARE_TXT']; ?></th>
                                        <th class="align-center">Type</th>
                                        <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                            <th class="align-center">View Invoice</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($db_trip)) {

                                        for ($i = 0; $i < scount($db_trip); $i++) {
                                            $poolTxt = $seriveJson = "";
                                            if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                $poolTxt = " (Pool)";
                                            }
                                            $eTypenew = $db_trip[$i]['eType'];
                                            $link_page = "invoice.php";
                                            if ($eTypenew == 'Ride') {
                                                $trip_type = 'Ride';
                                            } else if ($eTypenew == 'UberX') {
                                                $trip_type = 'Other Services';
                                            } else if ($eTypenew == 'Multi-Delivery' && $db_trip[$i]['totalDeliveryTrips'] > 1) {
                                                $trip_type = 'Multi-Delivery';
                                                $link_page = "invoice_multi_delivery.php";
                                            } else if ($ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY == 'Yes' && $eTypenew == 'Multi-Delivery' && $db_trip[$i]['totalDeliveryTrips'] == 1){
                                                $trip_type = 'Delivery';
                                                $link_page = "invoice_multi_delivery.php";
                                            } else {
                                                $trip_type = 'Delivery';
                                            }
                                            $trip_type .= $poolTxt;
                                            $viewService = 0;
                                            if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                // added by sunita
                                                if (!empty($db_trip[$i]['vRentalVehicleTypeName'])) {
                                                    $vehicleTypeName = $db_trip[$i]['vRentalVehicleTypeName'];
                                                } else {
                                                    $vehicleTypeName = $db_trip[$i]['vVehicleType'];
                                                }
                                            } else {
                                                $vehicleTypeName = $db_trip[$i]['vVehicleType'];
                                            }
                                            //if (isset($db_trip[$i]['tVehicleTypeData']) && $db_trip[$i]['tVehicleTypeData'] != "" && $vehicleTypeName == "") {
                                            //    $viewService = 1;
                                            //    $seriveJson = $db_trip[$i]['tVehicleTypeData'];
                                            //}
                                            if (isset($db_trip[$i]['tVehicleTypeFareData']) && $db_trip[$i]['tVehicleTypeFareData'] != "" && $vehicleTypeName == "") {
                                                $viewService = 1;
                                                $seriveJson = json_decode($db_trip[$i]['tVehicleTypeFareData']);
                                                $seriveJson = json_encode($seriveJson->FareData);
                                            }
                                            $systemTimeZone = date_default_timezone_get();
                                          
                                            if ($db_trip[$i]['fCancellationFare'] > 0 && !empty($db_trip[$i]['vTimeZone'])) {
                                                $dBookingDate = converToTz($db_trip[$i]['tEndDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone);
                                            } else if (!empty($db_trip[$i]['tStartDate']) && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00" && !empty($db_trip[$i]['vTimeZone']) != "") {
                                                $dBookingDate = converToTz($db_trip[$i]['tStartDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone); //$db_trip[$i]['tStartDate']

                                            } else {
                                                if (!empty($db_trip[$i]['tStartDate']) && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00") {
                                                    $dBookingDate = $db_trip[$i]['tStartDate'];
                                                } else {
                                                    $dBookingDate = $db_trip[$i]['tTripRequestDate'];
                                                }
                                            }

                                            $date_format_data_array = array(
                                                'tdate' => $dBookingDate,
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr class="gradeA">
                                                <?php
                                              
                                                if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
                                                    if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') {
                                                        ?>
                                                        <td align="left">
                                                            <?php
                                                            if (ONLY_MEDICAL_SERVICE == "Yes" && $db_trip[$i]['eType'] == "Ride") {
                                                                echo $langage_lbl_admin['LBL_TEXI_ADMIN'];
                                                            }else if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                                echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                            } else if ($db_trip[$i]['iRentalPackageId'] > 0 && $db_trip[$i]['eIsInterCity'] != "Yes") {
                                                                echo "Rental " . $trip_type;
                                                            } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                                echo "Hail " . $trip_type;
                                                            } else if ($db_trip[$i]['eIsInterCity'] == "Yes") {
                                                                echo "InterCity " . $trip_type;
                                                            } else if ($db_trip[$i]['isTaxiBid'] == "Yes") {
                                                                echo "Taxi Bid " . $trip_type;
                                                            } else {
                                                                if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])) {
                                                                    $trip_type = 'Fly';
                                                                }
                                                                echo $trip_type;
                                                            }
                                                            ?>
                                                        </td>
                                                        <?php
                                                    }
                                                    if (!empty($db_trip[$i]['eBookingFrom'])) {
                                                        $eBookingFrom = $db_trip[$i]['eBookingFrom'];
                                                    } else {
                                                        $eBookingFrom = $langage_lbl_admin['LBL_RIDER'];
                                                    }
                                                }
                                                if ($hotelPanel > 0 || $kioskPanel > 0) {
                                                        if (!empty($db_trip[$i]['eBookingFrom'])) { // Add kiosk details
                                                            // if($_REQUEST['test'] != ""){
                                                                // $kioskupdatedAry
                                                    ?>

                                                                <td class="align-center"><?= $eBookingFrom;?> <br> <a href="javascript:void(0);" onClick="show_kiosk_details('<?= $db_trip[$i]["iHotelBookingId"]?>')"  style="text-decoration: underline;"> <?= isset($kioskupdatedAry[$db_trip[$i]['iHotelBookingId']]) ? $kioskupdatedAry[$db_trip[$i]['iHotelBookingId']] : '' ?></td>
                                                            <? //}
                                                        ?>
                                                            <!-- <td class="align-center"><?= $eBookingFrom; ?></td>  -->
                                                        <?php
                                                        }else{ ?>
                                                    <td class="align-center"><?= $eBookingFrom; ?></td>
                                                        <? }
                                                }
                                                ?>
                                                <td align="center">
                                                    <?= $db_trip[$i]['vRideNo']; ?>
                                                </td>
                                                <td width="30%"><?php
                                                    if ($db_trip[$i]['isVideoCall'] == 'Yes') {
                                                        echo '--';
                                                    } else {
                                                        echo $db_trip[$i]['tSaddress'];
                                                        if ($APP_TYPE != "UberX" && $eTypenew != "Multi-Delivery" && $eTypenew != "UberX" && !empty($db_trip[$i]['tDaddress'])) {
                                                            echo ' -> ' . $db_trip[$i]['tDaddress'];
                                                        }
                                                    }
                                                    ?></td>
                                                <td align="center"><?= $get_dBookingDate_format['tDisplayDateTime']. $time_zone_difference_text;
                                                //DateTime($dBookingDate, '7')//DateTime($db_trip[$i]['tTripRequestDate']); ?></td>
                                                <?php if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>
                                                    <td>  
                                                        <?php if ($userObj->hasPermission('view-company')) { ?>
                                                            <a href="javascript:void(0);" onClick="show_company_details('<?= $db_trip[$i]['iCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearCmpName($db_trip[$i]['vCompany']); ?><?php if ($userObj->hasPermission('view-company')) { ?></a>
                                                        <?php } ?>  
                                                    </td>
                                                <?php } ?>
                                                <td>
                                                   <?php if ($userObj->hasPermission('view-providers')) { ?>
                                                        <a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;">
                                                    <?php } ?>
                                                        <?= clearName($db_trip[$i]['driverName']); ?>
                                                    <?php if ($userObj->hasPermission('view-providers')) { ?>
                                                        </a> 
                                                    <?php } ?>
                                            </td>
                                                <td>
                                                <?php if ($db_trip[$i]['eHailTrip'] != "Yes") { ?>
                                                        <?php if ($userObj->hasPermission('view-users')) { ?>
                                                            <a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')"  style="text-decoration: underline;">
                                                        <?php } ?>

                                                        <?= clearName($db_trip[$i]['riderName']); ?>

                                                        <?php if ($userObj->hasPermission('view-users')) { ?>
                                                            </a>
                                                        <?php } ?>
                                                    <?php
                                                    } else {
                                                        echo " ---- ";
                                                    }
                                                ?>
                                                </td>
                                                <?php if($isEnableSafetyTools && $MODULES_OBJ->isRideFeatureAvailable('Yes')) { ?>
                                                <td>
                                                    <?php if($db_trip[$i]['totalAudioRecordings'] > 0) { ?>
                                                    <a href="javascript:void(0);" onclick="fetchSafetyAudioRecordings('<?= $db_trip[$i]['iTripId'] ?>')">View Audio Recordings</a>
                                                    <?php } else { ?>
                                                    --
                                                    <?php } ?>
                                                </td>
                                                <?php } ?>
                                                <td align="right">
                                                    <?php
                                                    $cancelprice = $db_trip[$i]['fCancellationFare'] + $db_trip[$i]['fWalletDebit'];
                                                    if ($db_trip[$i]['fCancellationFare'] > 0) {
                                                        $db_trip[$i]['fCancellationFare'] = $db_trip[$i]['fCancellationFare'] + $db_trip[$i]['fTax1'] + $db_trip[$i]['fTax2'];
                                                        echo formateNumAsPerCurrency($db_trip[$i]['fCancellationFare'], '');
                                                    } else {
                                                        echo formateNumAsPerCurrency($db_trip[$i]['iFare'] + $db_trip[$i]['fWalletDebit'], '');
                                                    }
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <?php if ($eTypenew == 'UberX' && $db_trip[$i]['isVideoCall'] == 'No') { ?>
                                                        <a class="btn btn-success"
                                                                onclick="return getServiceDetails('<?= $db_trip[$i]['iTripId'] ?>','','showServiceModalV2');">
                                                            <i class="fa fa-certificate icon-white">
                                                                <b>View Services</b>
                                                            </i>
                                                        </a>


                                                        <?php } else if ($db_trip[$i]['isVideoCall'] == 'Yes') { ?>

                                                        <a class="btn btn-success"
                                                                onclick="return getServiceDetails('<?= $db_trip[$i]['iTripId'] ?>','','showServiceModalV2');">
                                                            <i class="fa fa-certificate icon-white">
                                                                <b>View Services</b>
                                                            </i>
                                                        </a>

                                                        <br>
                                                        (Video Consulting)



                                                    <?php } else {
                                                        if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                            // added by sunita
                                                            if (!empty($db_trip[$i]['vRentalVehicleTypeName'])) {
                                                                echo $db_trip[$i]['vRentalVehicleTypeName'];
                                                            } else {
                                                                echo $db_trip[$i]['vVehicleType'];
                                                            }
                                                        } else {
                                                            echo $db_trip[$i]['vVehicleType'];
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                    <td align="center" width="10%">

                                                        <?php
                                                        if ($APP_TYPE == 'UberX') {
                                                            $id_pro = 'iJobId';
                                                        }
                                                        else {
                                                            $id_pro = 'iTripId';
                                                        }
                                                        ?>


                                                        <?php if (($db_trip[$i]['iActive'] == 'Finished' && $db_trip[$i]['eCancelled'] == "Yes") || ($db_trip[$i]['fCancellationFare'] > 0) || ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fWalletDebit'] > 0)) { ?>
                                                            <button class="btn btn-primary"
                                                                    onclick='return !window.open("<?= $link_page ?>?<?=$id_pro?>=<?= $db_trip[$i]['iTripId'] ?>", "_blank")'
                                                                    ;">
                                                            <i class="icon-th-list icon-white">
                                                                <b>View Invoice</b>
                                                            </i>
                                                            </button>
                                                            <div style="font-size: 12px;">Cancelled</div>
                                                        <?php } else if ($db_trip[$i]['iActive'] == 'Finished') { ?>
                                                            <button class="btn btn-primary"
                                                                    onclick='return !window.open("<?= $link_page ?>?<?=$id_pro?>=<?= $db_trip[$i]['iTripId'] ?>", "_blank")'
                                                                    ;">
                                                            <i class="icon-th-list icon-white">
                                                                <b>View Invoice</b>
                                                            </i>
                                                            </button>
                                                            <?php
                                                        } else {
                                                            if ($db_trip[$i]['iActive'] == "Active" or $db_trip[$i]['iActive'] == "On Going Trip" or $db_trip[$i]['iActive'] == "Inactive" or ($db_trip[$i]['iActive'] == "Arrived" and !empty($db_trip[$i]['iFromStationId']) and !empty($db_trip[$i]['iToStationId']))) {
                                                                if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
                                                                    echo "On Job";
                                                                } else {
                                                                    echo "On Ride";
                                                                }
                                                                ?>
                                                                <br/> <!-- Commented By HJ On 11-01-2019 As Per Discuss withQA BM  -->
                                                                <?php if ($db_trip[$i]['iActive'] == "Inactive") { ?>
                                                                    Pending
                                                                    <i
                                                                            class="fa fa-exclamation-circle pending-trip"
                                                                            data-toggle="tooltip"
                                                                            data-placement="bottom"
                                                                            data-original-title='This trip is in pending state as the driver is on another trip. The driver will start this trip after completing the previous ones. This is a "Back-to-back Trips" feature where driver can get new ride requests while he is near to complete existing trip.'></i>
                                                                <?php } else { ?>
                                                                    <?php if ($userObj->hasPermission('reset-trip-jobs')) { ?>
                                                                        <a href="javascript:void(0);"
                                                                           onClick="resetOnlyTripStatus('<?= $db_trip[$i]['iTripId']; ?>')"
                                                                           data-toggle="tooltip" title="Reset">
                                                                            Reset <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?>
                                                                        </a>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                                <?php if ($db_trip[$i]['eType'] == 'Multi-Delivery' || $db_trip[$i]['eType'] == 'Deliver') { ?>
                                                                    <br/>
                                                                    <br/>
                                                                    <button class="btn btn-primary"
                                                                            onclick='return !window.open("<?= $link_page ?>?<?=$id_pro?>=<?= $db_trip[$i]['iTripId'] ?>", "_blank")'
                                                                            ;">
                                                                    <i class="icon-th-list icon-white">
                                                                        <b>View
                                                                            Invoice
                                                                        </b>
                                                                    </i>
                                                                    </button><?php } ?>
                                                                <?php
                                                            } else if ($db_trip[$i]['iActive'] == "Canceled" && ($db_trip[$i]['iCancelReasonId'] > 0 || $db_trip[$i]['vCancelReason'] != '')) {
                                                                ?>
                                                                <a href="javascript:void(0);" class="btn btn-info"
                                                                   data-toggle="modal"
                                                                   data-target="#uiModal1_<?= $db_trip[$i]['iTripId']; ?>">
                                                                    Cancel
                                                                    Reason
                                                                </a>
                                                                <?php
                                                            } else if ($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['fWalletDebit'] < 0) {
                                                                echo "Cancelled";
                                                            } else {
                                                                echo $db_trip[$i]['iActive'];
                                                            }
                                                        }
                                                        if ((!empty($db_trip[$i]['vDeliveryConfirmCode']) && (($db_trip[$i]['eType'] == 'Deliver') || ($db_trip[$i]['eType'] == 'Multi-Delivery' && $DELIVERY_VERIFICATION_METHOD == "Code"))) && ($db_trip[$i]['iActive'] != 'Finished' && $db_trip[$i]['iActive'] != 'Canceled')) {
                                                            echo '<div style="margin-top:15px;">Delivery Confirmation Code: ' . $db_trip[$i]['vDeliveryConfirmCode'] . '</div>';
                                                        }
                                                        if ($db_trip[$i]['eAskCodeToUser'] == "Yes" && !empty($db_trip[$i]['vRandomCode']) && $db_trip[$i]['iActive'] != "Canceled" && $db_trip[$i]['iActive'] != "Inactive") {
                                                            echo '<div style="margin-top:15px;">OTP: ' . $db_trip[$i]['vRandomCode'] . '</div>';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <div class="clear"></div>
                                            <div class="modal fade" id="uiModal1_<?= $db_trip[$i]['iTripId']; ?>"
                                                 tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                 aria-hidden="true">
                                                <div class="modal-content image-upload-1" style="width:400px;">
                                                    <div class="upload-content" style="width:350px;">
                                                        <h3><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Cancel
                                                            Reason
                                                        </h3>
                                                        <h4>Cancel Reason:
                                                            <b
                                                                    style="font-size: 15px;font-weight: normal;">
                                                                <?php
                                                                if ($db_trip[$i]['iCancelReasonId'] > 0) {
                                                                    $cancelreasonarray = getCancelReason($db_trip[$i]['iCancelReasonId'], $default_lang);
                                                                    $db_trip[$i]['vCancelReason'] = $cancelreasonarray['vCancelReason'];
                                                                }
                                                                ?>



                                                                <?= stripcslashes($db_trip[$i]['vCancelReason']); ?></b>
                                                        </h4>
                                                        <?php if (!empty($db_trip[$i]['eCancelledBy'])) {
                                                            $eCancelledBy = $langage_lbl_admin['LBL_ADMIN'];
                                                            if ($db_trip[$i]['eCancelledBy'] == "Passenger") {
                                                                $eCancelledBy = $langage_lbl_admin['LBL_RIDER'];
                                                            } else if ($db_trip[$i]['eCancelledBy'] == "Driver") {
                                                                $eCancelledBy = $langage_lbl_admin['LBL_DRIVER'];
                                                            } ?>
                                                            <h4>Cancel By:
                                                                <b
                                                                        style="font-size: 15px;font-weight: normal;"><?= stripcslashes($eCancelledBy); ?></b>
                                                            </h4>
                                                        <?php } else { ?>
                                                            <h4>
                                                                <b style="font-size: 15px;font-weight: normal;"></b>
                                                            </h4>
                                                        <?php } ?>
                                                        <input type="button" class="save" data-dismiss="modal"
                                                               name="cancel" value="Close">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="11"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<?php
include_once 'service_details.php';
?>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="action" value="<?= $action; ?>">
    <input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>">
    <input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="searchKiosk" value="<?= $searchKiosk; ?>">
    <input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?= $vStatus; ?>">
    <input type="hidden" name="eType" value="<?= $eType; ?>">
    <input type="hidden" name="promocode" value="<?= $promocode; ?>">
    <!-- for reset -->
    <input type="hidden" name="iTripId" id="iMainId01" value="">
    <input type="hidden" name="method" id="method" value="">
</form>
<div class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="float: left; width: 100%">
                <h4 id="servicetitle" class="pull-left">
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    Service Details
                </h4>
                <button type="button" class="close pull-right" data-dismiss="modal">x</button>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="service_detail"></div>
            </div>
        </div>
    </div>
</div>
 

<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_DRIVER_TXT']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">                                                                       
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>                       
                    </div>    
                </div>
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade " id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/rider-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_RIDER']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons1">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade " id="detail_modal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/rider-icon.png" alt="">
                    </i>
                    Hotel Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons2">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="kiosk_detail"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detail_modal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>
                    Company Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="comp_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detail_modal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>
                    Company Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="comp_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detail_modal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>
                    Company Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="comp_detail"></div>
            </div>
        </div>
    </div>
</div>
<!-- Reset Trip Status Modal -->
<div data-backdrop="static" data-keyboard="false" class="modal fade" id="is_resetTrip_modal_trip" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Reset Record(s) ?</h4>
            </div>
            <div class="modal-body">
                <p>Resetting <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> will end
                    the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> and release
                    the <?= strtolower($langage_lbl_admin['LBL_RIDER']); ?>
                    and <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>. Confirm to
                    reset <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?>?
                </p>
                <br/>
                <?php /* 1. <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> will be marked as cancelled and fare of <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> will set to 0.Please use this feature only when rider & driver are stuck in a trip. */ ?>
                <p>Note:
                    <br/>
                    <br/>
                    1. <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> status will be marked as Cancelled and
                    Fare of <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> will set to 0. Please use this feature only
                    when <?= $langage_lbl_admin['LBL_RIDER'] ?> & <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> are
                    stuck in the application and under any circumstances, they want to end
                    the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?>.
                    <br/>
                    <br/>
                    2. Please restart the <?= $langage_lbl_admin['LBL_RIDER'] ?>
                    and <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> application once
                    the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> is reset.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
                <a class="btn btn-success btn-ok action_modal_submit">Yes</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="safety_media_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="width: 900px">
        <div class="modal-content">
            <div class="modal-header" style="float: left; width: 100%">
                <h4 class="pull-left">
                    Safety Audio Recordings
                </h4>
                <button type="button" class="close pull-right" data-dismiss="modal">x</button>
            </div>
            <div class="modal-body" style="overflow: auto;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Audio Recording</th>
                            </tr>
                        </thead>
                        <tbody id="safety-media-details">
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<? include_once('footer.php'); ?>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<!--<link rel="stylesheet" href="css/select2/select2.min.css" />

    <script src="js/plugins/select2.min.js"></script> -->
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<? include_once('searchfunctions.php'); ?>
<script>
    var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';

    $('#dp4').datepicker().on('changeDate', function (ev) {
        var endDate = $('#dp5').val();
        if (ev.date.valueOf() < endDate.valueOf()) {
            $('#alert').show().find('strong').text('The start date can not be greater then the end date');
        } else {
            $('#alert').hide();
            var startDate = new Date(ev.date);
            $('#startDate').text($('#dp4').data('date'));
        }
        $('#dp4').datepicker('hide');
    });
    $('#dp5').datepicker().on('changeDate', function (ev) {
        var startDate = $('#dp4').val();
        if (ev.date.valueOf() < startDate.valueOf()) {
            $('#alert').show().find('strong').text('The end date can not be less then the start date');
        } else {
            $('#alert').hide();
            var endDate = new Date(ev.date);
            $('#endDate').text($('#dp5').data('date'));
        }
        $('#dp5').datepicker('hide');
    });
    $(document).ready(function () {
        $("#dp5").click(function () {
            $('#dp5').datepicker('show');
            $('#dp4').datepicker('hide');
        });
        $("#dp4").click(function () {
            $('#dp4').datepicker('show');
            $('#dp5').datepicker('hide');
        });
        if ('<?= $startDate ?>' != '') {
            $("#dp4").val('<?= $startDate ?>');
            $("#dp4").datepicker('update', '<?= $startDate ?>');
        }
        if ('<?= $endDate ?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate; ?>');
            $("#dp5").val('<?= $endDate; ?>');
        }
    });

    function showServiceModal(elem) {

        // var tripJson = JSON.parse($(elem).attr("data-json"));
        var tripJson = JSON.parse($(elem).next("textarea").val().replace(/\s\s+/g, ' '));
        var rideNo = $(elem).attr("data-trip");
        var typeNameArr = JSON.parse(typeArr)
        var serviceHtml = "";
        var srno = 1;
        // added by sunita
        for (var g = 0; g < tripJson.length; g++) {

            //serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?= $langage_lbl_admin['LBL_QTY_TXT'] ?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
            serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['id']] + " (" + typeNameArr[tripJson[g]['id'] + "_service"] + " - " + typeNameArr[tripJson[g]['id'] + "_subService"] + ")<br>";
            if (tripJson[g]['eAllowQty'] == 'Yes') {
                serviceHtml += "<?= $langage_lbl_admin['LBL_QTY_TXT'] ?>: <b>" + [tripJson[g]['qty']] + "</b>";
            }
            serviceHtml += "</p>";
            srno++;
        }
        $("#service_detail").html(serviceHtml);
        $("#servicetitle").text("Service Details : " + rideNo);
        $("#service_modal").modal('show');
        return false;
    }

    function setRideStatus(actionStatus) {
        window.location.href = "trip.php?type=" + actionStatus;
    }

    function todayDate() {
        $("#dp4").val('<?= $Today; ?>');
        $("#dp5").val('<?= $Today; ?>');
    }

    function reset() {
        location.reload();
    }

    function yesterdayDate() {
        $("#dp4").val('<?= $Yesterday; ?>');
        $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
        $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
        $("#dp4").change();
        $("#dp5").change();
        $("#dp5").val('<?= $Yesterday; ?>');
    }

    function currentweekDate(dt, df) {
        $("#dp4").val('<?= $monday; ?>');
        $("#dp4").datepicker('update', '<?= $monday; ?>');
        $("#dp5").datepicker('update', '<?= $sunday; ?>');
        $("#dp5").val('<?= $sunday; ?>');
    }

    function previousweekDate(dt, df) {
        $("#dp4").val('<?= $Pmonday; ?>');
        $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
        $("#dp5").datepicker('update', '<?= $Psunday; ?>');
        $("#dp5").val('<?= $Psunday; ?>');
    }

    function currentmonthDate(dt, df) {
        $("#dp4").val('<?= $currmonthFDate; ?>');
        $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
        $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
        $("#dp5").val('<?= $currmonthTDate; ?>');
    }

    function previousmonthDate(dt, df) {
        $("#dp4").val('<?= $prevmonthFDate; ?>');
        $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
        $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
        $("#dp5").val('<?= $prevmonthTDate; ?>');
    }

    function currentyearDate(dt, df) {
        $("#dp4").val('<?= $curryearFDate; ?>');
        $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
        $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
        $("#dp5").val('<?= $curryearTDate; ?>');
    }

    function previousyearDate(dt, df) {
        $("#dp4").val('<?= $prevyearFDate; ?>');
        $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
        $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
        $("#dp5").val('<?= $prevyearTDate; ?>');
    }

    $("#Search").on('click', function () {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        } else {
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action + "?" + formValus;
        }
    });
   
    function show_driver_details(driverid) {
        $("#driver_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');
        if (driverid != "") {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': "iDriverId=" + driverid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons").hide();
                }
            });
        }
    }
    function show_rider_details(userid) {
        $("#rider_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal1").modal('show');
        if (userid != "") {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': "iUserId=" + userid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#rider_detail").html(response.result);
                    $("#imageIcons1").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons1").hide();
                }
            });
        }
    }

     function show_kiosk_details(kioskid) {
        $("#kiosk_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal2").modal('show');
        if (kioskid != "") {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_admin_details.php',
                'AJAX_DATA': "iAdminId=" + kioskid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {

                if (response.action == "1") {
                    var data = response.result;
                    $("#kiosk_detail").html(response.result);
                    $("#imageIcons2").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons2").hide();
                }
            });
        }
    }

    function resetOnlyTripStatus(iAdminId) {
        $('#is_resetTrip_modal_trip').modal('show');
        $(".action_modal_submit").unbind().click(function () {
            var action = $("#pageForm").attr('action');
            var page = $("#pageId").val();
            $("#pageId01").val(page);
            $("#iMainId01").val(iAdminId);
            $("#method").val('reset');
            var formValus = $("#pageForm").serialize();
            window.location.href = action + "?" + formValus;
        });
    }
    function show_company_details(companyid) {
        $("#comp_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal3").modal('show');
        if (companyid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_company_details.php',
                'AJAX_DATA': "iCompanyId=" + companyid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#comp_detail").html(data);
                    $("#imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons").hide();
                }
            });
        }
    }

    function fetchSafetyAudioRecordings(iTripId) {
        $('#loaderIcon').show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_fetch_safety_audio_recordings.php',
            'AJAX_DATA': "iTripId=" + iTripId,
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').hide();
            if (response.action == "1") {
                var data = response.result;
                $("#safety-media-details").html(data);
                $('#safety_media_modal').find('.table-responsive .mCSB_container').css('padding-bottom', '0');
                $('#safety_media_modal').modal('show');
            } else {
                
            }
        });
    }


    /*------------------trip filter-----------------*/
    var eType_Filter = $('#eTypeSearch'); //main filter
    var subType = $('#subType'); // sub filter
    var subFilter = $('.subFilter'); // options
    var eTypeVal = '<?php echo $eType; ?>'; // options
    var ONLY_MEDICAL_SERVICE = '<?php echo ONLY_MEDICAL_SERVICE; ?>';

    console.log(ONLY_MEDICAL_SERVICE);
    var eType = ['Ride' , 'Deliver'];
    if(eTypeVal.trim() != '' && $.inArray(eTypeVal, eType) !== -1  && ONLY_MEDICAL_SERVICE != 'Yes'){
        $(subType).show();
        TriggerSubTypeDropDown(eTypeVal,0);
    }else{
        $(subType).hide();
        $(subFilter).hide();
    }

    function TriggerSubTypeDropDown(eType_Filter_val, removeValue) {

        if(removeValue == 1) {
            $(subType).val('');
        }

        $(subFilter).hide();
        $("[attr-eType='" + eType_Filter_val + "']").show();
        var isInArray = $.inArray(eType_Filter_val, eType);
        if (isInArray !== -1 &&  ONLY_MEDICAL_SERVICE != 'Yes') {
            $(subType).show();
        } else {
            $(subType).hide();
        }
    }

    eType_Filter.change(function() {
        var eType_Filter_val = $(this).val();
        TriggerSubTypeDropDown(eType_Filter_val , 1);

    });


</script>
</body>
<!-- END BODY-->
</html>