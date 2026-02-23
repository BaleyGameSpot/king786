<?php

include_once('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_schedule_ride_new_status.txt", "running");
/* Cron Log Update End */

/* creating objects */
$thumb = new thumbnail;

include_once('include/include_webservice_enterprisefeatures.php');


$sql_ride = "";
if($MODULES_OBJ->isEnableScheduledRideFlow())
{
    $sql_ride = " AND eType != 'Ride' ";
}

$ToDate = date('Y-m-d');
$sql1 = "SELECT iCabBookingId,iCronStage,eAssigned,dBooking_date,vTimeZone FROM cab_booking WHERE eStatus='Pending' AND dBooking_date LIKE '%$ToDate%' AND eAutoAssign = 'Yes' AND iCronStage != '3' AND eAssigned='No' $sql_ride ";

$data_bks = $obj->MySQLSelect($sql1);


for ($i = 0; $i < scount($data_bks); $i++) {

    $FromDate = date('Y-m-d H:i:s');
    // $FromDate = date('2017-06-06 13:38:36');
    $ToDate = $data_bks[$i]['dBooking_date'];

    $datetime1 = strtotime($FromDate);
    $datetime2 = strtotime($ToDate);
    $interval = abs($datetime2 - $datetime1);

    $minutes = round($interval / 60);

    //$minutes = 8;
    if ($data_bks[$i]['iCronStage'] == 0) {
        if ($minutes <= 12 && $minutes >= 8) {
            sendRequest($data_bks[$i]['iCabBookingId']);
        }
    }

    if ($data_bks[$i]['iCronStage'] == 1 || $data_bks[$i]['iCronStage'] == 0) {
        if ($minutes <= 8 && $minutes >= 4) {
            sendRequest($data_bks[$i]['iCabBookingId']);
        }
    }

    if ($data_bks[$i]['iCronStage'] == 2 || $data_bks[$i]['iCronStage'] == 1 || $data_bks[$i]['iCronStage'] == 0) {
        if ($minutes <= 4 && $minutes >= 0) {
            sendRequest($data_bks[$i]['iCabBookingId']);
        }
    }
}

function sendRequest($cabId) {
    global $obj, $EVENT_MSG_OBJ, $LANG_OBJ, $COMM_MEDIA_OBJ, $CONFIG_OBJ, $isFromAdminPanel, $OPTIMIZE_DATA_OBJ, $MODULES_OBJ, $SHOW_SERVICE_EST, $currencyAssociateArr, $vTimeZone,$intervalmins;

    $sql = "SELECT cb.*,CONCAT(ru.vName,' ', ru.vLastName) as passengerName,ru.vFbId,ru.vImgName,ru.vAvgRating,ru.vPhoneCode,ru.vPhone,ru.eGender,ru.vLang FROM cab_booking as cb
        LEFT JOIN register_user as ru ON ru.iUserId = cb.iUserId
        WHERE cb.iCabBookingId='" . $cabId . "'";

    $data_booking = $obj->MySQLSelect($sql);

    if (scount($data_booking) > 0) {

        $iUserId = $data_booking[0]['iUserId'];
        $sql = "select iTripId,vTripStatus from register_user where iUserId='$iUserId'";
        $user_data = $obj->MySQLSelect($sql);
        $iTripId = $user_data[0]['iTripId'];
        if ($iTripId != "" && $iTripId != 0) {
            $status_trip = get_value("trips", 'iActive', "iTripId", $iTripId, '', 'true');
            // $cab_id = get_value("trips", 'iCabBookingId', "iTripId",$iTripId,'','true');
            if ($status_trip == "Active" || $status_trip == "On Going Trip") {
                $where1 = " iCabBookingId = '$cabId' ";
                $Data_update_cab_booking['eCancelBySystem'] = "Yes";
                $Data_update_cab_booking['eStatus'] = "Cancel";
                $Data_update_cab_booking['vCancelReason'] = "User on another trip.";
                $Data_update_cab_booking['eCancelBy'] = "Admin";
                $id = $obj->MySQLQueryPerform("cab_booking", $Data_update_cab_booking, 'update', $where1);
                return false;
                // break;
            }
        }
        $deviceTokens_arr_ios = array();
        $registation_ids_new = array();
        $vSourceLatitude = $data_booking[0]['vSourceLatitude'];
        $vSourceLongitude = $data_booking[0]['vSourceLongitude'];
        $vDestLatitude = $data_booking[0]['vDestLatitude'];
        $vDestLongitude = $data_booking[0]['vDestLongitude'];
        $eType = $data_booking[0]['eType'];
        $passengerId = $data_booking[0]['iUserId'];
        $passengerName = $data_booking[0]['passengerName'];
        $PPicName = $data_booking[0]['vImgName'];
        $vFbId = $data_booking[0]['vFbId'];
        $vAvgRating = $data_booking[0]['vAvgRating'];
        $vPhone = $data_booking[0]['vPhone'];
        $vPhoneCode = $data_booking[0]['vPhoneCode'];
        $iCronStage = $data_booking[0]['iCronStage'];
        $isVideoCall = $data_booking[0]['isVideoCall'];
        $_REQUEST['isVideoCall'] = $data_booking[0]['isVideoCall'];
        $_REQUEST['GeneralUserType'] = "Passenger";

        $sourceLoc = $vSourceLatitude . ',' . $vSourceLongitude;
        $destLoc = $vDestLatitude . ',' . $vDestLongitude;
        $sourceLocationArr = array($vSourceLatitude, $vSourceLongitude);
        $destinationLocationArr = array($vDestLatitude, $vDestLongitude);

        $PickUpAddress = $data_booking[0]['vSourceAddresss'];
        $DestAddress = $data_booking[0]['tDestAddress'];

        $vDistance = $data_booking[0]['vDistance'];
        $vDuration = $data_booking[0]['vDuration'];
        $selectedCarTypeID = $data_booking[0]['iVehicleTypeId'];
        $promoCode = $data_booking[0]['vCouponCode'];
        $eFlatTrip = $data_booking[0]['eFlatTrip'];
        $fFlatTripPrice = $data_booking[0]['fFlatTripPrice'];
        $vTimeZone = $data_booking[0]['vTimeZone'];

        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLangCode, "1");
        $userLanguageCode = $data_booking[0]['vLang'];
        if (empty($userLanguageCode)) {
            $userLanguageCode = $vLangCode;
            $userLanguageLabelsArr = $languageLabelsArr;
        } else {
            if ($userLanguageCode == $vLangCode) {
                $userLanguageLabelsArr = $languageLabelsArr;
            } else {
                $userLanguageLabelsArr = $LANG_OBJ->FetchLanguageLabels($userLanguageCode, "1");
            }
        }
        $labelsStoreArr = array();
        $labelsStoreArr[$userLanguageCode]['LBL_TRIP_USER_WAITING'] = $userLanguageLabelsArr['LBL_TRIP_USER_WAITING'];
        $labelsStoreArr[$userLanguageCode]['LBL_USER_WAITING'] = $userLanguageLabelsArr['LBL_USER_WAITING'];
        $labelsStoreArr[$userLanguageCode]['LBL_DELIVERY_SENDER_WAITING'] = $userLanguageLabelsArr['LBL_DELIVERY_SENDER_WAITING'];

        $isDestinationAdded = "No";
        if(!empty($vDestLatitude) && !empty($vDestLongitude)) {
            $isDestinationAdded = "Yes";
        }

        $messageArr['Message'] = "CabRequested";
        $messageArr['iBookingId'] = $data_booking[0]['iCabBookingId'];
        $messageArr['iCompanyId'] = $data_booking[0]['iCompanyId'];
        $messageArr['setCron'] = 'Yes';
        $messageArr['sourceLatitude'] = strval($vSourceLatitude);
        $messageArr['sourceLongitude'] = strval($vSourceLongitude);
        $messageArr['PassengerId'] = strval($passengerId);
        $messageArr['PName'] = $passengerName;
        $messageArr['PPicName'] = $PPicName;
        $messageArr['PFId'] = $vFbId;
        $messageArr['PRating'] = $vAvgRating;
        $messageArr['PPhone'] = $vPhone;
        $messageArr['PPhoneC'] = $vPhoneCode;
        $messageArr['REQUEST_TYPE'] = $eType;
        $messageArr['PACKAGE_TYPE'] = $eType == "Deliver" ? get_value('package_type', 'vName', 'iPackageTypeId', $iPackageTypeId, '', 'true') : '';
        $messageArr['destLatitude'] = strval($vDestLatitude);
        $messageArr['destLongitude'] = strval($vDestLongitude);
        $messageArr['MsgCode'] = strval(time() . mt_rand(1000, 9999));



        if ($iCronStage > 0) {
            $message = array();
            $addMsg = "Now trying to send another request.";
            if ($iCronStage == 2) {
                $addMsg = "Last time trying to send request to driver for the ride.";
            }
            $message['details'] = '<p>Dear Administrator,</p>
                            <p>Driver was not available / not accepted request for the following manual booking in stage ' . $iCronStage . '.' . $addMsg . ' </p>
                            <p>Name: ' . $passengerName . ',</p>
                            <p>Contact Number: +' . $vPhoneCode . $vPhone . '</p>';
            $mail = $COMM_MEDIA_OBJ->SendMailToMember('CRON_BOOKING_EMAIL', $message);
        }


        $where_cabid = " iCabBookingId = '" . $data_booking[0]['iCabBookingId'] . "'";
        $iCronStageNew = $iCronStage;
        $Data_update['iCronStage'] = $iCronStage + 1;
        $id = $obj->MySQLQueryPerform("cab_booking", $Data_update, 'update', $where_cabid);

        $Data = array();
        $_REQUEST["iUserId"] = $passengerId;
        $_REQUEST["iCompanyId"] = $messageArr['iCompanyId'];
        $iCompanyId = $messageArr['iCompanyId'];
        //$Data = FetchAvailableDrivers($vSourceLatitude, $vSourceLongitude, "", "", "Yes");
        $isFromAdminPanel = 'Yes';
        
        $Data = FetchAvailableDrivers($vSourceLatitude, $vSourceLongitude, array(), "", "Yes", "No", "", $vDestLatitude, $vDestLongitude);

        ### Checking For Female Driver Request ##
        if ($iCronStageNew == 0) {
            if (!empty($Data)) {
                $FavDriverArr = array();
                $favCount = 0;
                foreach ($Data['DriverList'] as $onlineDrirerkey => $onlineDrirerkeyValue) {
                    if (strtoupper($onlineDrirerkeyValue['eFavDriver']) == 'YES') {
                        $FavDriverArr[$favCount] = $onlineDrirerkeyValue;
                        $favCount++;
                    }
                }
            }
            if (!empty($FavDriverArr)) {
                $Datalist = array();
                $Datalist = $FavDriverArr;
                /* =======================Fav Driver Arr Start================ */
                $DatalistNewArr = array();
                $DatalistNewArr = $Datalist;
                for ($i = 0; $i < scount($Datalist); $i++) {
   
                    $isRemoveDriverIntoList = "No";
                    $iVehicleTypeId = $data_booking[0]['iVehicleTypeId'];
                    $iDriverVehicleId = $Datalist[$i]['iDriverVehicleId'];
                    $sql = "SELECT vCarType,eHandiCapAccessibility FROM `driver_vehicle` WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                    $rows_driver_vehicle = $obj->MySQLSelect($sql);
                    $DriverVehicleTypeArr = explode(",", $rows_driver_vehicle[0]['vCarType']);
                    if (!in_array($iVehicleTypeId, $DriverVehicleTypeArr)) {
                        $isRemoveDriverIntoList = "Yes";
                    }
       
                    if ($eType == "Ride") {
                        $eHandiCapAccessibility = $data_booking[0]['eHandiCapAccessibility'];
                        if ($eHandiCapAccessibility == "" || $eHandiCapAccessibility == NULL) {
                            $eHandiCapAccessibility = "No";
                        }
                        $DriverVehicleeHandiCapAccessibility = $rows_driver_vehicle[0]['eHandiCapAccessibility'];
                        if ($eHandiCapAccessibility == "Yes" && $DriverVehicleeHandiCapAccessibility != "Yes") {
                            $isRemoveDriverIntoList = "Yes";
                        }
                    }
             
                    if ($eType == "Ride") {
                        $DriverFemaleOnlyReqAccept = $Datalist[$i]['eFemaleOnlyReqAccept'];
                        if ($DriverFemaleOnlyReqAccept == "" || $DriverFemaleOnlyReqAccept == NULL) {
                            $DriverFemaleOnlyReqAccept = "No";
                        }
                        $RiderGender = $data_booking[0]['eGender'];
                        if ($DriverFemaleOnlyReqAccept == "Yes" && $RiderGender == "Male") {
                            $isRemoveDriverIntoList = "Yes";
                        }
                    }

                    if ($eType == "Ride") {
                        $eFemaleDriverRequest = $data_booking[0]['eFemaleDriverRequest'];
                        if ($eFemaleDriverRequest == "" || $eFemaleDriverRequest == NULL) {
                            $eFemaleDriverRequest = "No";
                        }
                        $DriverGender = $Datalist[$i]['eGender'];
                        if ($eFemaleDriverRequest == "Yes" && $DriverGender != "Female") {
                            $isRemoveDriverIntoList = "Yes";
                        }
                    }
              
                    $ePayType = $data_booking[0]['ePayType'];
                    $ACCEPT_CASH_TRIPS = $Datalist[$i]['ACCEPT_CASH_TRIPS'];
                    if ($eType != "UberX") {
                        if ($ePayType == "Cash" && $ACCEPT_CASH_TRIPS == "No") {
                            $isRemoveDriverIntoList = "Yes";
                        }
                    }
                  
                    if ($eType == "UberX") {
                        $APP_PAYMENT_MODE = $CONFIG_OBJ->getConfigurations("configurations", "APP_PAYMENT_MODE");
                        if ($APP_PAYMENT_MODE == "Cash" && $ACCEPT_CASH_TRIPS == "No") {
                            $isRemoveDriverIntoList = "Yes";
                        }
                    }

                    if ($isRemoveDriverIntoList == "Yes") {
                        unset($DatalistNewArr[$i]);
                    }
                }

                ### Checking For Female Driver Request ##
                $driversActive = array();
                $driversActive = array_values($DatalistNewArr);

                $Data['DriverList'] = $driversActive;
                //if(scount($Data) > 0){
                if (scount($driversActive) > 0) {
                    $iCabRequestId = get_value("cab_request_now", 'max(iCabRequestId)', "iUserId", $passengerId, '', 'true');
                    $eStatus_cab = get_value("cab_request_now", 'eStatus', "iCabRequestId", $iCabRequestId, '', 'true');
                    if ($eStatus_cab == "Requesting") {
                        $where1 = " iCabRequestId = '$iCabRequestId' ";
                        $Data_update_cab['eStatus'] = "Cancelled";
                        // $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab, 'update', $where1);
                    }

                    $Data_update_cab_now['iCabBookingId'] = $data_booking[0]['iCabBookingId'];
                    $Data_update_cab_now['fTollPrice'] = $data_booking[0]['fTollPrice'];
                    $Data_update_cab_now['vTollPriceCurrencyCode'] = $data_booking[0]['vTollPriceCurrencyCode'];
                    $Data_update_cab_now['eTollSkipped'] = $data_booking[0]['eTollSkipped'];
                    $Data_update_cab_now['iUserId'] = $passengerId;
                    $Data_update_cab_now['tMsgCode'] = $messageArr['MsgCode'];
                    $Data_update_cab_now['eStatus'] = 'Requesting';
                    $Data_update_cab_now['vSourceLatitude'] = $vSourceLatitude;
                    $Data_update_cab_now['vSourceLongitude'] = $vSourceLongitude;
                    $Data_update_cab_now['tSourceAddress'] = $data_booking[0]['vSourceAddresss'];
                    $Data_update_cab_now['vDestLatitude'] = $vDestLatitude;
                    $Data_update_cab_now['vDestLongitude'] = $vDestLongitude;
                    $Data_update_cab_now['tDestAddress'] = $data_booking[0]['tDestAddress'];
                    $Data_update_cab_now['iVehicleTypeId'] = $data_booking[0]['iVehicleTypeId'];
                    $Data_update_cab_now['fPickUpPrice'] = $data_booking[0]['fPickUpPrice'];
                    $Data_update_cab_now['fNightPrice'] = $data_booking[0]['fNightPrice'];
                    $Data_update_cab_now['eType'] = $eType;
                    $Data_update_cab_now['iPackageTypeId'] = $eType == "Deliver" ? $data_booking[0]['iPackageTypeId'] : '';
                    $Data_update_cab_now['vReceiverName'] = $eType == "Deliver" ? $data_booking[0]['vReceiverName'] : '';
                    $Data_update_cab_now['vReceiverMobile'] = $eType == "Deliver" ? $data_booking[0]['vReceiverMobile'] : '';
                    $Data_update_cab_now['tPickUpIns'] = $eType == "Deliver" ? $data_booking[0]['tPickUpIns'] : '';
                    $Data_update_cab_now['tDeliveryIns'] = $eType == "Deliver" ? $data_booking[0]['tDeliveryIns'] : '';
                    $Data_update_cab_now['tPackageDetails'] = $eType == "Deliver" ? $data_booking[0]['tPackageDetails'] : '';
                    $Data_update_cab_now['vCouponCode'] = $data_booking[0]['vCouponCode'];
                    $Data_update_cab_now['iQty'] = $data_booking[0]['iQty'];
                    $Data_update_cab_now['vRideCountry'] = $data_booking[0]['vRideCountry'];
                    $Data_update_cab_now['eFemaleDriverRequest'] = $data_booking[0]['eFemaleDriverRequest'];
                    $Data_update_cab_now['eHandiCapAccessibility'] = $data_booking[0]['eHandiCapAccessibility'];
                    $Data_update_cab_now['vTimeZone'] = $data_booking[0]['vTimeZone'];
                    $Data_update_cab_now['dAddedDate'] = date("Y-m-d H:i:s");
                    $Data_update_cab_now['eFromCronJob'] = "Yes";
                    $Data_update_cab_now['iFromStationId'] = $data_booking[0]['iFromStationId'];
                    $Data_update_cab_now['iToStationId'] = $data_booking[0]['iToStationId'];
                    $Data_update_cab_now['iOrganizationId'] = $data_booking[0]['iOrganizationId'];
                    $Data_update_cab_now['iUserProfileId'] = $data_booking[0]['iUserProfileId'];
                    $Data_update_cab_now['ePaymentBy'] = $data_booking[0]['ePaymentBy'];

                    $Data_update_cab_now['ePayWallet'] = $data_booking[0]['ePayWallet'];
                    $Data_update_cab_now['eWalletDebitAllow'] = $data_booking[0]['eWalletDebitAllow'];
                    $Data_update_cab_now['fWalletDebit'] = $data_booking[0]['fWalletDebit'];
                    $Data_update_cab_now['tUserWalletBalance'] = $data_booking[0]['tUserWalletBalance'];
                    $Data_update_cab_now['tEstimatedCharge'] = $data_booking[0]['tEstimatedCharge'];

                    ## Distance and Duration ##
                    $Data_update_cab_now['fDistance'] = $data_booking[0]['vDistance'];
                    $Data_update_cab_now['fDuration'] = $data_booking[0]['vDuration'];
                    $Data_update_cab_now['tTotalDuration'] = $data_booking[0]['tTotalDuration'];
                    $Data_update_cab_now['tTotalDistance'] = $data_booking[0]['tTotalDistance'];
                    ## Distance and Duration ##
                    /******For InterCity*****/
                    $Data_update_cab_now['eIsInterCity'] = $data_booking[0]['eIsInterCity'];
                    $Data_update_cab_now['eRoundTrip'] = $data_booking[0]['eRoundTrip'];
                    /******For InterCity*****/

                    $insert_id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'insert');
                    $messageArr['iCabRequestId'] = strval($insert_id);

                    //$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

                    //$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLangCode, "1");
                    /*$userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
                    if ($eType == "UberX") {
                        $alertMsg = $languageLabelsArr['LBL_USER_WAITING'];
                    } elseif ($eType == "Ride") {
                        $alertMsg = $userwaitinglabel;
                    } else {
                        $alertMsg = $languageLabelsArr['LBL_DELIVERY_SENDER_WAITING'];
                    }*/

                    
                    $generalDataArr = $DriverDataArr = array();

                    foreach ($driversActive as $item) {
                        if ($eType == "Ride") {
                            if (!empty($labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'])) {
                                $alertMsg = $labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'];
                            } else {
                                $alertMsg = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                                $labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'] = $alertMsg;
                            }
                        } elseif ($eType == "UberX") {
                            if (!empty($labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'])) {
                                $alertMsg = $labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'];
                            } else {
                                $alertMsg = get_value('language_label', 'vValue', 'vLabel', 'LBL_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                                $labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'] = $alertMsg;
                            }
                        } else {
                            if (!empty($labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'])) {
                                $alertMsg = $labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'];
                            } else {
                                $alertMsg = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY_SENDER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                                $labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'] = $alertMsg;
                            }
                        }
                        if ($item['vTripStatus'] == "On Going Trip") {
                            $messageArr['eAcceptTripRequest'] = "Yes";
                        } else {
                            $messageArr['eAcceptTripRequest'] = "No";
                        }
                        $messageArr['isBackToBackTrip'] = $item['isBackToBackTrip'];
                        $messageArr['isPoolRequest'] = "No";
                        $messageArr['eFavDriver'] = "Yes";
                        $messageArr['eIsPremium'] = $MODULES_OBJ->isEnablePremiumDriverPreference() ? $item['eIsPremium'] : 'No';
                        $messageArr['isMultipleOrderRequest'] = "No";

                        $item['iCabRequestId'] = $insert_id;
                        $item['eFavDriver'] = "Yes";
                        $item['eIsPremium'] = $MODULES_OBJ->isEnablePremiumDriverPreference() ? $item['eIsPremium'] : 'No';
                        $item['distance'] = $item['distance'];

                        if(strtoupper($SHOW_SERVICE_EST) == 'YES' && $eType == "Ride"){
                            $LBL_MIN_AWAY_TXT = $languageLabelsArr['LBL_MIN_AWAY_TXT'];

                            $item['vDistance'] = $messageArr['vDistance'] = $vDistance;
                            $item['vDuration'] = $messageArr['vDuration'] = $vDuration;
                            $DistanceAsperDriver = calcualteDistAsPerMember($item['iDriverId'], $vDistance, $item['vLang']);

                            $item['vPickupEst'] = $messageArr['vPickupEst'] = "--";
                            $item['vTripEst'] = $messageArr['vTripEst']   = $DistanceAsperDriver . ' | '. ceil($vDuration) .' '. $LBL_MIN_AWAY_TXT;

                            $Fare_data_ride = calculateApproximateFareGeneral($vDuration, $vDistance, $selectedCarTypeID, $passengerId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr, "Yes", $eType, "", "", "", "");

                            $fRatioDriver = $currencyAssociateArr[$item['vCurrencyDriver']]['Ratio'];

                            $fareAmountRide = setTwoDecimalPoint($Fare_data_ride[0]['iFare_Ori'] * $fRatioDriver);
                            $item['vFareEst'] = $final_message['vFareEst'] =formateNumAsPerCurrency($fareAmountRide, $item['vCurrencyDriver']);
                            $item['vDriverUnit'] =  getMemberCountryUnit($item['iDriverId'], "Driver");
                            $item['vMinAwayTxt'] = $LBL_MIN_AWAY_TXT;

                        } else {

                            $item['vDistance'] = $messageArr['vDistance'] = $vDistance;
                            $item['vDuration'] = $messageArr['vDuration'] = $vDuration;
                            $item['vPickupEst'] = $messageArr['vPickupEst'] = '';
                            $item['vTripEst'] = $messageArr['vTripEst'] = '';
                            $item['vFareEst'] = $messageArr['vFareEst'] = '';

                        }

                        $DriverDataArr[] = $item;

                        $generalDataArr[] = array(
                            'eDeviceType'       => $item['eDeviceType'],
                            'deviceToken'       => $item['iGcmRegId'],
                            'alertMsg'          => $alertMsg,
                            'eAppTerminate'     => $item['eAppTerminate'],
                            'eDebugMode'        => $item['eDebugMode'],
                            'eHmsDevice'        => $item['eHmsDevice'],
                            'message'           => $messageArr,
                            'channelName'       => "CAB_REQUEST_DRIVER_" . $item['iDriverId'],
                            'addRequestSentArr' => array(
                                'iUserId'       => $passengerId,
                                'iDriverId'     => $item['iDriverId'],
                                'tMessage'      => $messageArr,
                                'iMsgCode'      => $messageArr['MsgCode'],
                                'vStartLatlong' => $sourceLoc,
                                'vEndLatlong'   => $destLoc,
                                'tStartAddress' => $PickUpAddress,
                                'tEndAddress'   => $DestAddress,
                                'eType'         => $eType, 
                                'eFavDriver'    => $item['eFavDriver'], 
                                'eIsPremium'    => $item['eIsPremium'], 
                                'distance'      => $item['distance'],
                                'iCabBookingId' => $data_booking[0]['iCabBookingId']
                            )
                        );
                    }

                    $OPTIMIZE_DATA_OBJ->SetCabRequestAddress($DriverDataArr);

                    $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr, 'requestSent' => 'No'), RN_PROVIDER);  
                } else {
                    //Email to admin for Not assigned Driver
                    $message = array();
                    $message['details'] = '<p>Dear Administrator,</p>
                                        <p>Driver is not available for the following manual booking in stage ' . $iCronStage . '</p>
                                        <p>Name: ' . $passengerName . ',</p>
                                        <p>Contact Number: +' . $vPhoneCode . $vPhone . '</p>';
                    $mail = $COMM_MEDIA_OBJ->SendMailToMember('CRON_BOOKING_EMAIL', $message);
                    //Email to admin for Not assigned Driver
                }
                /* Fav Driver Arr END */
                sleep(30);
                $Datalist = array();
                $Datalist = $Data['DriverList'];
            } else {
                $Datalist = array();
                $Datalist = $Data['DriverList'];
            }
        } else {
            $Datalist = array();
            $Datalist = $Data['DriverList'];
        }
       
        ### Checking For Female Driver Request ##

        $DatalistNewArr = array();
        $DatalistNewArr = $Datalist;
        for ($i = 0; $i < scount($Datalist); $i++) {
            //echo $iDriverId=$Datalist[$i]['iDriverId'];echo "<br />";
            $isRemoveDriverIntoList = "No";
            $iVehicleTypeId = $data_booking[0]['iVehicleTypeId'];
            $iDriverVehicleId = $Datalist[$i]['iDriverVehicleId'];
            $sql = "SELECT vCarType,eHandiCapAccessibility FROM `driver_vehicle` WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
            $rows_driver_vehicle = $obj->MySQLSelect($sql);
            $DriverVehicleTypeArr = explode(",", $rows_driver_vehicle[0]['vCarType']);
            if (!in_array($iVehicleTypeId, $DriverVehicleTypeArr)) {
                $isRemoveDriverIntoList = "Yes";
            }
            //echo "Driver Id >> ".$Datalist[$i]['iDriverId']." >> Remove From Vehicle List >> ".$isRemoveDriverIntoList; echo "<br />";
            if ($eType == "Ride") {
                $eHandiCapAccessibility = $data_booking[0]['eHandiCapAccessibility'];
                if ($eHandiCapAccessibility == "" || $eHandiCapAccessibility == NULL) {
                    $eHandiCapAccessibility = "No";
                }
                $DriverVehicleeHandiCapAccessibility = $rows_driver_vehicle[0]['eHandiCapAccessibility'];
                if ($eHandiCapAccessibility == "Yes" && $DriverVehicleeHandiCapAccessibility != "Yes") {
                    $isRemoveDriverIntoList = "Yes";
                }
            }
            //echo "Driver Id >> ".$Datalist[$i]['iDriverId']." >> Remove From HandiCapAccessibility List >> ".$isRemoveDriverIntoList; echo "<br />";
            if ($eType == "Ride") {
                $DriverFemaleOnlyReqAccept = $Datalist[$i]['eFemaleOnlyReqAccept'];
                if ($DriverFemaleOnlyReqAccept == "" || $DriverFemaleOnlyReqAccept == NULL) {
                    $DriverFemaleOnlyReqAccept = "No";
                }
                $RiderGender = $data_booking[0]['eGender'];
                if ($DriverFemaleOnlyReqAccept == "Yes" && $RiderGender == "Male") {
                    $isRemoveDriverIntoList = "Yes";
                }
            }
            //echo "Driver Id >> ".$Datalist[$i]['iDriverId']." >> Remove From Driver Profile FemaleDriverRequest List >> ".$isRemoveDriverIntoList; echo "<br />";
            if ($eType == "Ride") {
                $eFemaleDriverRequest = $data_booking[0]['eFemaleDriverRequest'];
                if ($eFemaleDriverRequest == "" || $eFemaleDriverRequest == NULL) {
                    $eFemaleDriverRequest = "No";
                }
                $DriverGender = $Datalist[$i]['eGender'];
                if ($eFemaleDriverRequest == "Yes" && $DriverGender != "Female") {
                    $isRemoveDriverIntoList = "Yes";
                }
            }
            //echo "Driver Id >> ".$Datalist[$i]['iDriverId']." >> Remove From Cabbooking FemaleDriverRequest List >> ".$isRemoveDriverIntoList; echo "<br />";
            $ePayType = $data_booking[0]['ePayType'];
            $ACCEPT_CASH_TRIPS = $Datalist[$i]['ACCEPT_CASH_TRIPS'];
            if ($eType != "UberX") {
                if ($ePayType == "Cash" && $ACCEPT_CASH_TRIPS == "No") {
                    $isRemoveDriverIntoList = "Yes";
                }
            }

            if ($eType == "UberX") {
                $APP_PAYMENT_MODE = $CONFIG_OBJ->getConfigurations("configurations", "APP_PAYMENT_MODE");
                if ($APP_PAYMENT_MODE == "Cash" && $ACCEPT_CASH_TRIPS == "No") {
                    $isRemoveDriverIntoList = "Yes";
                }
            }

            if ($isRemoveDriverIntoList == "Yes") {
                unset($DatalistNewArr[$i]);
            }
        }

        ### Checking For Female Driver Request ##
        // $Data = array();
        $driversActive = array();
        $driversActive = array_values($DatalistNewArr);

        $Data['DriverList'] = $driversActive;
        //if(scount($Data) > 0){
        if (scount($driversActive) > 0) {
            $iCabRequestId = get_value("cab_request_now", 'max(iCabRequestId)', "iUserId", $passengerId, '', 'true');
            $eStatus_cab = get_value("cab_request_now", 'eStatus', "iCabRequestId", $iCabRequestId, '', 'true');
            if ($eStatus_cab == "Requesting") {
                $where1 = " iCabRequestId = '$iCabRequestId' ";
                $Data_update_cab['eStatus'] = "Cancelled";
                // $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab, 'update', $where1);
            }

            $Data_update_cab_now['iCabBookingId'] = $data_booking[0]['iCabBookingId'];
            $Data_update_cab_now['fTollPrice'] = $data_booking[0]['fTollPrice'];
            $Data_update_cab_now['vTollPriceCurrencyCode'] = $data_booking[0]['vTollPriceCurrencyCode'];
            $Data_update_cab_now['eTollSkipped'] = $data_booking[0]['eTollSkipped'];
            $Data_update_cab_now['iUserId'] = $passengerId;
            $Data_update_cab_now['tMsgCode'] = $messageArr['MsgCode'];
            $Data_update_cab_now['eStatus'] = 'Requesting';
            $Data_update_cab_now['vSourceLatitude'] = $vSourceLatitude;
            $Data_update_cab_now['vSourceLongitude'] = $vSourceLongitude;
            $Data_update_cab_now['tSourceAddress'] = $data_booking[0]['vSourceAddresss'];
            $Data_update_cab_now['vDestLatitude'] = $vDestLatitude;
            $Data_update_cab_now['vDestLongitude'] = $vDestLongitude;
            $Data_update_cab_now['tDestAddress'] = $data_booking[0]['tDestAddress'];
            $Data_update_cab_now['iVehicleTypeId'] = $data_booking[0]['iVehicleTypeId'];
            $Data_update_cab_now['fPickUpPrice'] = $data_booking[0]['fPickUpPrice'];
            $Data_update_cab_now['fNightPrice'] = $data_booking[0]['fNightPrice'];
            $Data_update_cab_now['eType'] = $eType;
            $Data_update_cab_now['iPackageTypeId'] = $eType == "Deliver" ? $data_booking[0]['iPackageTypeId'] : '';
            $Data_update_cab_now['vReceiverName'] = $eType == "Deliver" ? $data_booking[0]['vReceiverName'] : '';
            $Data_update_cab_now['vReceiverMobile'] = $eType == "Deliver" ? $data_booking[0]['vReceiverMobile'] : '';
            $Data_update_cab_now['tPickUpIns'] = $eType == "Deliver" ? $data_booking[0]['tPickUpIns'] : '';
            $Data_update_cab_now['tDeliveryIns'] = $eType == "Deliver" ? $data_booking[0]['tDeliveryIns'] : '';
            $Data_update_cab_now['tPackageDetails'] = $eType == "Deliver" ? $data_booking[0]['tPackageDetails'] : '';
            $Data_update_cab_now['vCouponCode'] = $data_booking[0]['vCouponCode'];
            $Data_update_cab_now['iQty'] = $data_booking[0]['iQty'];
            $Data_update_cab_now['vRideCountry'] = $data_booking[0]['vRideCountry'];
            $Data_update_cab_now['eFemaleDriverRequest'] = $data_booking[0]['eFemaleDriverRequest'];
            $Data_update_cab_now['eHandiCapAccessibility'] = $data_booking[0]['eHandiCapAccessibility'];
            $Data_update_cab_now['vTimeZone'] = $data_booking[0]['vTimeZone'];
            $Data_update_cab_now['dAddedDate'] = date("Y-m-d H:i:s");
            $Data_update_cab_now['eFromCronJob'] = "Yes";
            $Data_update_cab_now['iFromStationId'] = $data_booking[0]['iFromStationId'];
            $Data_update_cab_now['iToStationId'] = $data_booking[0]['iToStationId'];
            $Data_update_cab_now['iOrganizationId'] = $data_booking[0]['iOrganizationId'];
            $Data_update_cab_now['iUserProfileId'] = $data_booking[0]['iUserProfileId'];
            $Data_update_cab_now['ePaymentBy'] = $data_booking[0]['ePaymentBy'];

            $Data_update_cab_now['ePayWallet'] = $data_booking[0]['ePayWallet'];
            $Data_update_cab_now['eWalletDebitAllow'] = $data_booking[0]['eWalletDebitAllow'];
            $Data_update_cab_now['fWalletDebit'] = $data_booking[0]['fWalletDebit'];
            $Data_update_cab_now['tUserWalletBalance'] = $data_booking[0]['tUserWalletBalance'];
            $Data_update_cab_now['tEstimatedCharge'] = $data_booking[0]['tEstimatedCharge'];
                    
            ## Distance and Duration ##
            $Data_update_cab_now['fDistance'] = $data_booking[0]['vDistance'];
            $Data_update_cab_now['fDuration'] = $data_booking[0]['vDuration'];
            $Data_update_cab_now['tTotalDuration'] = $data_booking[0]['tTotalDuration'];
            $Data_update_cab_now['tTotalDistance'] = $data_booking[0]['tTotalDistance'];
            ## Distance and Duration ##
            /******For InterCity*****/
            $Data_update_cab_now['eIsInterCity'] = $data_booking[0]['eIsInterCity'];
            $Data_update_cab_now['eRoundTrip'] = $data_booking[0]['eRoundTrip'];
            /******For InterCity*****/

            $insert_id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'insert');
            $messageArr['iCabRequestId'] = strval($insert_id);

            /* ------------------------multi delivery details------------------- */
            $delivery_arr = $data_booking[0]['tDeliveryData'];
            // echo "dd".$delivery_arr;//exit;
            if ($delivery_arr != "" && $eType == "Multi-Delivery") {
                $details_arr = json_decode($delivery_arr, true);
                // echo "<pre>";print_r($details_arr);exit;
                $j = 0;
                $last_key = end(array_keys($details_arr));
                foreach ($details_arr as $key123 => $values1) {
                    $i = 0;
                    $insert_did = array();
                    foreach ($values1 as $key => $value) {
                        // echo "==>".$key."<br>";
                        if ($key == "vReceiverAddress" || $key == "vReceiverLatitude" || $key == "vReceiverLongitude" || $key == "ePaymentByReceiver") {
                            $Data_trip_locations[$key] = $value;
                            if ($key == "vReceiverLatitude") {
                                $Old_end_lat = $Data_trip_locations['tEndLat'];
                                $Data_trip_locations['tEndLat'] = $value;
                            }
                            else if ($key == "vReceiverLongitude") {
                                $Old_end_long = $Data_trip_locations['tEndLong'];
                                $Data_trip_locations['tEndLong'] = $value;
                            }
                            else if ($key == "vReceiverAddress") {
                                $Old_end_address = $Data_trip_locations['tDaddress'];
                                $Data_trip_locations['tDaddress'] = $value;
                            }
                            else if ($key == "ePaymentByReceiver") {
                                $Data_trip_locations['ePaymentByReceiver'] = $value;
                            }
                            if (($ePaymentBy == "Sender" || $ePaymentBy == "Receiver") && $key123 != 0) {
                                $Data_trip_locations['tStartLat'] = $Old_end_lat;
                                $Data_trip_locations['tStartLong'] = $Old_end_long;
                                $Data_trip_locations['tSaddress'] = $Old_end_address;
                            }
                            else {
                                $Data_trip_locations['tStartLat'] = $PickUpLatitude;
                                $Data_trip_locations['tStartLong'] = $PickUpLongitude;
                                $Data_trip_locations['tSaddress'] = $PickUpAddress;
                            }
                        }
                        else {
                            $Data_delivery['iDeliveryFieldId'] = $key;
                            $Data_delivery['iCabRequestId'] = $insert_id;
                            $Data_delivery['vValue'] = $value;
                            $insert_did[] = $obj->MySQLQueryPerform("trip_delivery_fields", $Data_delivery, 'insert');
                        }
                    }
                    $Data_trip_locations['iCabBookingId'] = $insert_id;
                    $Data_trip_locations['ePaymentBy'] = $ePaymentBy;
                    $insert_dfid = $obj->MySQLQueryPerform("trips_delivery_locations", $Data_trip_locations, 'insert');
                    $delivery_ids = implode("','", $insert_did);
                    $where = " iTripDeliveryFieldId in ('" . $delivery_ids . "')";
                    $data_update['iTripDeliveryLocationId'] = $insert_dfid;
                    $obj->MySQLQueryPerform("trip_delivery_fields", $data_update, 'update', $where);
                    if ($last_key == $key123) {
                        $where = " iCabRequestId='" . $insert_id . "'";
                        $data_update_cab['vDestLatitude'] = $Data_trip_locations['tEndLat'];
                        $data_update_cab['vDestLongitude'] = $Data_trip_locations['tEndLong'];
                        $data_update_cab['tDestAddress'] = $Data_trip_locations['tDaddress'];
                        $obj->MySQLQueryPerform("cab_request_now", $data_update_cab, 'update', $where);
                    }
                }
            }


            //$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

            //$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLangCode, "1");
            /*$userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
            if ($eType == "UberX") {
                $alertMsg = $languageLabelsArr['LBL_USER_WAITING'];
            } elseif ($eType == "Ride") {
                $alertMsg = $userwaitinglabel;
            } else {
                $alertMsg = $languageLabelsArr['LBL_DELIVERY_SENDER_WAITING'];
            }*/

            if($MODULES_OBJ->isInterCityFeatureAvailable())
            {
                $eIsInterCity =  $data_booking[0]['eIsInterCity'];
                if($eType == "Ride" && $eIsInterCity == "Yes"){
                    $item['eIsInterCity'] = $eIsInterCity;
                    $messageArr['eIsInterCity']  = $eIsInterCity;
                }

            }

            $generalDataArr = $DriverDataArr = array();
            foreach ($driversActive as $item) {
                if ($eType == "Ride") {
                    if (!empty($labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'])) {
                        $alertMsg = $labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'];
                    } else {
                        $alertMsg = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                        $labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'] = $alertMsg;
                    }
                } elseif ($eType == "UberX") {
                    if (!empty($labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'])) {
                        $alertMsg = $labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'];
                    } else {
                        $alertMsg = get_value('language_label', 'vValue', 'vLabel', 'LBL_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                        $labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'] = $alertMsg;
                    }
                } else {
                    if (!empty($labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'])) {
                        $alertMsg = $labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'];
                    } else {
                        $alertMsg = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY_SENDER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                        $labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'] = $alertMsg;
                    }
                }
                if ($item['vTripStatus'] == "On Going Trip") {
                    $messageArr['eAcceptTripRequest'] = "Yes";
                } else {
                    $messageArr['eAcceptTripRequest'] = "No";
                }
                $messageArr['isBackToBackTrip'] = $item['isBackToBackTrip'];
                $messageArr['isPoolRequest'] = "No";
                $messageArr['eFavDriver'] = "No";
                $messageArr['eIsPremium'] = $MODULES_OBJ->isEnablePremiumDriverPreference() ? $item['eIsPremium'] : 'No';
                $messageArr['isMultipleOrderRequest'] = "No";
                $messageArr['vTitle'] = $alertMsg;

                $item['iCabRequestId'] = $insert_id;
                $item['eFavDriver'] = "No";
                $item['eIsPremium'] = $MODULES_OBJ->isEnablePremiumDriverPreference() ? $item['eIsPremium'] : 'No';
                $item['distance'] = $item['distance'];

                if(strtoupper($SHOW_SERVICE_EST) == 'YES' && $eType == "Ride"){
                    $LBL_MIN_AWAY_TXT = $languageLabelsArr['LBL_MIN_AWAY_TXT'];

                    $item['vDistance'] = $messageArr['vDistance'] = $vDistance;
                    $item['vDuration'] = $messageArr['vDuration'] = $vDuration;
                    $DistanceAsperDriver = calcualteDistAsPerMember($item['iDriverId'], $vDistance, $item['vLang']);

                    $item['vPickupEst'] = $messageArr['vPickupEst'] = "--";
                    $item['vTripEst'] = $messageArr['vTripEst']   = $DistanceAsperDriver . ' | '. ceil($vDuration) .' '. $LBL_MIN_AWAY_TXT;

                    $Fare_data_ride = calculateApproximateFareGeneral($vDuration, $vDistance, $selectedCarTypeID, $passengerId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr, "Yes", $eType, "", "", "", "");

                    $fRatioDriver = $currencyAssociateArr[$item['vCurrencyDriver']]['Ratio'];
                    
                    $fareAmountRide = setTwoDecimalPoint($Fare_data_ride[0]['iFare_Ori'] * $fRatioDriver);
                    $item['vFareEst'] = $final_message['vFareEst'] =formateNumAsPerCurrency($fareAmountRide, $item['vCurrencyDriver']);
                    $item['vDriverUnit'] =  getMemberCountryUnit($item['iDriverId'], "Driver");
                    $item['vMinAwayTxt'] = $LBL_MIN_AWAY_TXT;

                } else {

                    $item['vDistance'] = $messageArr['vDistance'] = $vDistance;
                    $item['vDuration'] = $messageArr['vDuration'] = $vDuration;
                    $item['vPickupEst'] = $messageArr['vPickupEst'] = '';
                    $item['vTripEst'] = $messageArr['vTripEst'] = '';
                    $item['vFareEst'] = $messageArr['vFareEst'] = '';

                }

                $DriverDataArr[] = $item;

                $generalDataArr[] = array(
                    'eDeviceType'       => $item['eDeviceType'],
                    'deviceToken'       => $item['iGcmRegId'],
                    'alertMsg'          => $alertMsg,
                    'eAppTerminate'     => $item['eAppTerminate'],
                    'eDebugMode'        => $item['eDebugMode'],
                    'eHmsDevice'        => $item['eHmsDevice'],
                    'message'           => $messageArr,
                    'channelName'       => "CAB_REQUEST_DRIVER_" . $item['iDriverId'],
                    'addRequestSentArr' => array(
                        'iUserId'       => $passengerId,
                        'iDriverId'     => $item['iDriverId'],
                        'tMessage'      => $messageArr,
                        'iMsgCode'      => $messageArr['MsgCode'],
                        'vStartLatlong' => $sourceLoc,
                        'vEndLatlong'   => $destLoc,
                        'tStartAddress' => $PickUpAddress,
                        'tEndAddress'   => $DestAddress,
                        'eType'         => $eType, 
                        'eFavDriver'    => $item['eFavDriver'], 
                        'eIsPremium'    => $item['eIsPremium'], 
                        'distance'      => $item['distance'],
                        'iCabBookingId' => $data_booking[0]['iCabBookingId']
                    )
                );
            }

            $OPTIMIZE_DATA_OBJ->SetCabRequestAddress($DriverDataArr);

            $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr, 'requestSent' => 'No'), RN_PROVIDER);
        } else {
            //Email to admin for Not assigned Driver
            $message = array();
            $message['details'] = '<p>Dear Administrator,</p>
                            <p>Driver is not available for the following manual booking in stage ' . $iCronStage . '</p>
                            <p>Name: ' . $passengerName . ',</p>
                            <p>Contact Number: +' . $vPhoneCode . $vPhone . '</p>';
            $mail = $COMM_MEDIA_OBJ->SendMailToMember('CRON_BOOKING_EMAIL', $message);
            //Email to admin for Not assigned Driver
        }
    }
}


/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_schedule_ride_new_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true); 

foreach ($cron_logs as $ckey => $cfile) 
{
    if($cfile['filename'] == "cron_schedule_ride_new.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
?>