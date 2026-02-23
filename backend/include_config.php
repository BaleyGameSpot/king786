<?php

$IS_CONTINUE_DELETE_PROCESS = empty($IS_INHOUSE_DOMAINS) || $IS_INHOUSE_DOMAINS == false ? true : false;


$IS_RIDE_MODULE_AVAIL = $MODULES_OBJ->isRideFeatureAvailable("Yes");
$IS_DELIVERY_MODULE_AVAIL = $MODULES_OBJ->isDeliveryFeatureAvailable("Yes");
$IS_UFX_MODULE_AVAIL= $MODULES_OBJ->isUberXFeatureAvailable("Yes");
$IS_DELIVERALL_MODULE_AVAIL = $MODULES_OBJ->isDeliverAllFeatureAvailable("Yes");

$isUfxAvailable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 04-06-2020 For Optimized Query
global $deleteAppFilesArr;
$deleteAppFilesArr = array();
//Added By HJ On 27-07-2020 For Store setup_info Data into Cache Start
if(empty($SETUP_INFO_DATA_ARR)) {
    $setupInfoApcKey = md5("setup_info");
    $getSetupCacheData = $oCache->getData($setupInfoApcKey);
    if(!empty($getSetupCacheData) && scount($getSetupCacheData) > 0){
       $addOnsDataArr = $getSetupCacheData;
    }else{
        $addOnsDataArr = $obj->MySQLSelect("SELECT * FROM setup_info LIMIT 0,1");
        $setSetupCacheData = $oCache->setData($setupInfoApcKey, $addOnsDataArr);
    }
}
else {
    $addOnsDataArr = $SETUP_INFO_DATA_ARR;
}
//Added By HJ On 27-07-2020 For Store setup_info Data into Cache End
//$addOnsDataArr = $obj->MySQLSelect("SELECT * FROM setup_info LIMIT 0,1");
//echo "<pre>";print_r($oCache);die;

$addOnsDataArr_orig = $addOnsDataArr;
$addOnData =$addOnsJSONObj= json_decode($addOnsDataArr[0]['lAddOnConfiguration'], true);
$eCubeX = $eCubejekX =$eRideX=$eDeliverallX= "No";

$IS_FLY_MODULE_AVAIL = (!empty($addOnsJSONObj['Fly']) && strtoupper($addOnsJSONObj['Fly']) == "YES") ? true : false;

if (isset($addOnsDataArr[0]['eCubeX']) && $addOnsDataArr[0]['eCubeX'] != "") {
    $eCubeX = $addOnsDataArr[0]['eCubeX'];
}
if (isset($addOnsDataArr[0]['eCubejekX']) && $addOnsDataArr[0]['eCubejekX'] != "") {
    $eCubejekX = $addOnsDataArr[0]['eCubejekX'];
}
if (isset($addOnsDataArr[0]['eRideX']) && $addOnsDataArr[0]['eRideX'] != "") {
    $eRideX = $addOnsDataArr[0]['eRideX'];
}
if (isset($addOnsDataArr[0]['eDeliverallX']) && $addOnsDataArr[0]['eDeliverallX'] != "") {
    $eDeliverallX = $addOnsDataArr[0]['eDeliverallX'];
}
//echo "<pre>";print_r($addOnsJSONObj);die;
$Deliverall = $Fly = $UberX =$Delivery=$Ride= "";
// if (strtoupper($eCubejekX) == "YES" || strtoupper($eCubeX) == "YES" || strtoupper($eDeliverallX) == "YES") {
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }
// }
//echo "<pre>";print_r($Deliverall);die;
//$IS_UFX_SERVICE_AVAIL = isUfxFeatureAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
$IS_UFX_SERVICE_AVAIL = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query


################################# Check Files Of Android Applications ##########################################

################################ Module Delete ############################################
if($IS_RIDE_MODULE_AVAIL == false){
    RefactorApp::addSafetyToDeliteList();
    RefactorApp::addTollFeatureToDeliteList();
    RefactorApp::addSafetyCheckListToDeliteList();
    RefactorApp::addSafetyFacemaskVerificationToDeliteList();
    RefactorApp::addRentalFeatureToDeliteList();
    RefactorApp::addPoolFeatureToDeliteList();
    RefactorApp::addBusinessProfileFeatureToDeliteList();
    RefactorApp::addBookForElseToDeliteList();
    RefactorApp::addEndOfDayTripToDeliteList();
    RefactorApp::addMultiStopOverPointsToDeliteList();
}
if($IS_FLY_MODULE_AVAIL == false){
    RefactorApp::addFlyFeatureToDeliteList();
}else{
    unset($_REQUEST['FLY_MODULE']);
    unset($_REQUEST['FLY_MODULE_FILES']);
} 
if($IS_DELIVERY_MODULE_AVAIL == false){
    RefactorApp::addMultiDeliveryToDeliteList();
    RefactorApp::addSingleDeliveryToDeliteList();
    
    if($APP_TYPE == "Ride-Delivery-UberX"){
        /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
        $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR = array();
        $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp = explode(",", $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);
        $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "No";
        for ($i = 0; $i < scount($COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp); $i++) {
            $item_tmp_file = $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp[$i];

            if ($item_tmp_file != "" && $item_tmp_file != "GeneralFiles/OpenCatType.swift" && $item_tmp_file != "com.general.files.OpenCatType" && endsWithSGF($item_tmp_file,"CommonDeliveryTypeSelectionActivity") && endsWithSGF($item_tmp_file,"activity_multi_type_selection")) {
                $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR[] = $item_tmp_file;
                $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "Yes";
            }
        }
        $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES'] = implode(",", $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR);
        /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
    }
    
    RefactorApp::addCommonDeliveryTypesToDeliteList();
}

if (strtoupper($IS_UFX_SERVICE_AVAIL) != "YES" || $IS_UFX_MODULE_AVAIL == false) {
    $IS_UFX_SERVICE_AVAIL = "No";
    RefactorApp::addUberXServicesToDeliteList();
}

if ((!empty($Deliverall) && strtoupper($Deliverall) == "NO") || $IS_DELIVERALL_MODULE_AVAIL == false) {
    RefactorApp::addDeliverAllToDeliteList();
}

if($IS_DELIVERY_MODULE_AVAIL == false && $IS_UFX_MODULE_AVAIL == false){
    RefactorApp::addOnGoingJobsToDeliteList();
}

if($IS_RIDE_MODULE_AVAIL == false && $IS_DELIVERY_MODULE_AVAIL == false && $IS_UFX_MODULE_AVAIL == false){
    RefactorApp::addRideSectionToDeliteList();
    //addWayBillToDeliteList();
    // addRDUToDeliteList();
    RefactorApp::addFavDriverToDeliteList();
    RefactorApp::addDriverSubscriptionToDeliteList();
}
################################ Module Delete ############################################
if (empty($addOnsJSONObj['TIMESLOT_AVAILIBILITY']) || strtoupper($addOnsJSONObj['TIMESLOT_AVAILIBILITY']) != "YES") {
    RefactorApp::addTimeslotToDeliteList();
} else {
    unset($_REQUEST['STORE_INDIVIDUALDAY_AVAILABILITY_MODULE']);
    unset($_REQUEST['STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES']);
}

if (empty($addOnsJSONObj['STORE_SEARCH_BY_ITEM_NAME']) || strtoupper($addOnsJSONObj['STORE_SEARCH_BY_ITEM_NAME']) != "YES") {
    RefactorApp::addSearchByItemNameToDeliteList();
} else {
    unset($_REQUEST['STORE_SEARCH_BY_ITEM_NAME_MODULE']);
    unset($_REQUEST['STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES']);
}

if (empty($addOnsJSONObj['MANUAL_TOLL_FEATURE_MODULE']) || strtoupper($addOnsJSONObj['MANUAL_TOLL_FEATURE_MODULE']) != "YES") {
    RefactorApp::addTollFeatureToDeliteList();
} else {
    unset($_REQUEST['MANUAL_TOLL_FEATURE_MODULE']);
    unset($_REQUEST['MANUAL_TOLL_FEATURE_MODULE_FILES']);
}

if (empty($addOnsJSONObj['SAFETY_MODULE']) || strtoupper($addOnsJSONObj['SAFETY_MODULE']) != "YES") {
    RefactorApp::addSafetyToDeliteList();
} else {
    unset($_REQUEST['SAFETY_MODULE']);
    unset($_REQUEST['SAFETY_MODULE_FILES']);
}

if (empty($addOnsJSONObj['SAFETY_RATING_MODULE']) || strtoupper($addOnsJSONObj['SAFETY_RATING_MODULE']) != "YES") {
    RefactorApp::addSafetyRatingToDeliteList();
} else {
    unset($_REQUEST['SAFETY_RATING_MODULE']);
    unset($_REQUEST['SAFETY_RATING_MODULE_FILES']);
}

if (empty($addOnsJSONObj['SAFETY_CHECK_LIST_MODULE']) || strtoupper($addOnsJSONObj['SAFETY_CHECK_LIST_MODULE']) != "YES") {
    RefactorApp::addSafetyCheckListToDeliteList();
} else {
    unset($_REQUEST['SAFETY_CHECK_LIST_MODULE']);
    unset($_REQUEST['SAFETY_CHECK_LIST_MODULE_FILES']);
}

if (empty($addOnsJSONObj['SAFETY_FACEMASK_VERIFICATION_MODULE']) || strtoupper($addOnsJSONObj['SAFETY_FACEMASK_VERIFICATION_MODULE']) != "YES") {
    RefactorApp::addSafetyFacemaskVerificationToDeliteList();
} else {
    unset($_REQUEST['SAFETY_FACEMASK_VERIFICATION_MODULE']);
    unset($_REQUEST['SAFETY_FACEMASK_VERIFICATION_MODULE_FILES']);
}

if (empty($addOnsJSONObj['EIGHTEEN_PLUS_FEATURE_MODULE']) || strtoupper($addOnsJSONObj['EIGHTEEN_PLUS_FEATURE_MODULE']) != "YES") {
    RefactorApp::addEighteenPlusToDeliteList();
} else {
    unset($_REQUEST['EIGHTEEN_PLUS_FEATURE_MODULE']);
    unset($_REQUEST['EIGHTEEN_PLUS_FEATURE_MODULE_FILES']);
}

if (empty($addOnsJSONObj['GENIE_FEATURE_MODULE']) || strtoupper($addOnsJSONObj['GENIE_FEATURE_MODULE']) != "YES") {
    RefactorApp::addGenieFeatureToDeliteList();
} else {
    unset($_REQUEST['GENIE_FEATURE_MODULE']);
    unset($_REQUEST['GENIE_FEATURE_MODULE_FILES']);
}

if (empty($addOnsJSONObj['CONTACTLESS_DELIVERY_MODULE']) || strtoupper($addOnsJSONObj['CONTACTLESS_DELIVERY_MODULE']) != "YES") {
    RefactorApp::addContactlessFeatureToDeliteList();
} else {
    unset($_REQUEST['CONTACTLESS_DELIVERY_MODULE']);
    unset($_REQUEST['CONTACTLESS_DELIVERY_MODULE_FILES']);
}

if (empty($addOnsJSONObj['MULTI_SINGLE_STORE_MODULE']) || strtoupper($addOnsJSONObj['MULTI_SINGLE_STORE_MODULE']) != "YES") {
    RefactorApp::addMultiSingleStoreToDeliteList();
} else {
    unset($_REQUEST['MULTI_SINGLE_STORE_MODULE']);
    unset($_REQUEST['MULTI_SINGLE_STORE_MODULE_FILES']);
}

if (empty($addOnsJSONObj['CATEGORY_WISE_STORE_MODULE']) || strtoupper($addOnsJSONObj['CATEGORY_WISE_STORE_MODULE']) != "YES") {
    RefactorApp::addCategorywiseStoeToDeliteList();
} else {
    unset($_REQUEST['CATEGORY_WISE_STORE_MODULE']);
    unset($_REQUEST['CATEGORY_WISE_STORE_MODULE_FILES']);
}

if (strtoupper(PACKAGE_TYPE) == "SHARK") {
    unset($_REQUEST['THERMAL_PRINT_MODULE']);
    unset($_REQUEST['THERMAL_PRINT_MODULE_FILES']);
} else {
    RefactorApp::addThermalPrintToDeliteList();
}

//Added By HJ On 01-10-2019 For Removed File of Thermal Print End
############################################################ Dynamic Features ############################################################

if (empty($addOnsJSONObj['DONATION']) || strtoupper($addOnsJSONObj['DONATION']) != "YES") {
    RefactorApp::addDonationToDeliteList();
} else {
    unset($_REQUEST['DONATION_SECTION']);
    unset($_REQUEST['DONATION_SECTION_FILES']);
}

if (empty($addOnsJSONObj['DRIVER_DESTINATION']) || strtoupper($addOnsJSONObj['DRIVER_DESTINATION']) != "YES") {
    RefactorApp::addEndOfDayTripToDeliteList();
} else {
    unset($_REQUEST['END_OF_DAY_TRIP_SECTION']);
    unset($_REQUEST['END_OF_DAY_TRIP_SECTION_FILES']);
}

if (empty($addOnsJSONObj['FAVOURITE_DRIVER']) || strtoupper($addOnsJSONObj['FAVOURITE_DRIVER']) != "YES") {
    RefactorApp::addFavDriverToDeliteList();
} else {
    unset($_REQUEST['FAV_DRIVER_SECTION']);
    unset($_REQUEST['FAV_DRIVER_SECTION_FILES']);
}

if (empty($addOnsJSONObj['DRIVER_SUBSCRIPTION']) || strtoupper($addOnsJSONObj['DRIVER_SUBSCRIPTION']) != "YES") {
    RefactorApp::addDriverSubscriptionToDeliteList();
} else {
    unset($_REQUEST['DRIVER_SUBSCRIPTION_SECTION']);
    unset($_REQUEST['DRIVER_SUBSCRIPTION_SECTION_FILES']);
}

if (empty($addOnsJSONObj['MULTI_STOPOVER_POINTS']) || strtoupper($addOnsJSONObj['MULTI_STOPOVER_POINTS']) != "YES") {
    RefactorApp::addMultiStopOverPointsToDeliteList();
} else {
    unset($_REQUEST['STOP_OVER_POINT_SECTION']);
    unset($_REQUEST['STOP_OVER_POINT_SECTION_FILES']);
}

if (empty($addOnsJSONObj['GOJEK_GOPAY']) || strtoupper($addOnsJSONObj['GOJEK_GOPAY']) != "YES") {
    RefactorApp::addGoJekGoPayToDeliteList();
} else {
    unset($_REQUEST['GO_PAY_SECTION']);
    unset($_REQUEST['GO_PAY_SECTION_FILES']);
}

if (empty($addOnsJSONObj['APPLE_WATCH_APP']) || strtoupper($addOnsJSONObj['APPLE_WATCH_APP']) != "YES") {
    if(isset($_REQUEST['GeneralDeviceType']) && strtoupper($_REQUEST['GeneralDeviceType']) == "IOS") {
        RefactorApp::addAppleWatchAppToDeliteList();
    }
} else {
    unset($_REQUEST['APPLE_WATCH_MODULE']);
    unset($_REQUEST['APPLE_WATCH_APP_FILES']);
}

if (empty($addOnsJSONObj['SERVICE_BID_FEATURE']) || strtoupper($addOnsJSONObj['SERVICE_BID_FEATURE']) != "YES") {
    RefactorApp::addBidFeatureToDeliteList();
} else {
    unset($_REQUEST['BID_SERVICE']);
    unset($_REQUEST['BID_SERVICE_FILES']);
}

if (empty($addOnsJSONObj['LIVE_ACTIVITY_FEATURE']) || strtoupper($addOnsJSONObj['LIVE_ACTIVITY_FEATURE']) != "YES") {
    if(isset($_REQUEST['GeneralDeviceType']) && strtoupper($_REQUEST['GeneralDeviceType']) == "IOS") {
        RefactorApp::addLiveActivityFeatureToDeliteList();
    }
} else {
    unset($_REQUEST['LIVE_ACTIVITY_MODULE']);
    unset($_REQUEST['LIVE_ACTIVITY_FILES']);
}

if ((empty($addOnsJSONObj['RENT_ITEM_FEATURE']) || strtoupper($addOnsJSONObj['RENT_ITEM_FEATURE']) != "YES") 
    && (empty($addOnsJSONObj['RENT_REALESTATE_FEATURE']) || strtoupper($addOnsJSONObj['RENT_REALESTATE_FEATURE']) != "YES") 
    && (empty($addOnsJSONObj['RENT_CARS_FEATURE']) || strtoupper($addOnsJSONObj['RENT_CARS_FEATURE']) != "YES")) {
    RefactorApp::addBuySellRentFeatureToDeliteList();
} else {
    unset($_REQUEST['RENT_ITEM_SERVICE']);
    unset($_REQUEST['RENT_ITEM_SERVICE_FILES']);
}

if (empty($addOnsJSONObj['TRACK_SERVICE_FEATURE']) || strtoupper($addOnsJSONObj['TRACK_SERVICE_FEATURE']) != "YES") {
    RefactorApp::addTrackingFeatureToDeliteList();
} else {
    unset($_REQUEST['TRACKING_MODULE']);
    unset($_REQUEST['TRACKING_MODULE_FILES']);
}

if (empty($addOnsJSONObj['RIDE_SHARE_FEATURE']) || strtoupper($addOnsJSONObj['RIDE_SHARE_FEATURE']) != "YES") {
    RefactorApp::addRideShareFeatureToDeliteList();
} else {
    unset($_REQUEST['RIDE_SHARE_PRO_MODULE']);
    unset($_REQUEST['RIDE_SHARE_PRO_MODULE_FILES']);
}

if (empty($addOnsJSONObj['NEARBY_FEATURE']) || strtoupper($addOnsJSONObj['NEARBY_FEATURE']) != "YES") {
    RefactorApp::addNearByFeatureToDeliteList();
} else {
    unset($_REQUEST['NEARBY_MODULE']);
    unset($_REQUEST['NEARBY_MODULE_FILES']);
}

if (empty($addOnsJSONObj['GIFT_CARD_FEATURE']) || strtoupper($addOnsJSONObj['GIFT_CARD_FEATURE']) != "YES") {
    RefactorApp::addGiftCardFeatureToDeliteList();
} else {
    unset($_REQUEST['GIFTCARD_MODULE']);
    unset($_REQUEST['GIFTCARD_MODULE_FILES']);
}

if (empty($addOnsJSONObj['PARKING_FEATURE']) || strtoupper($addOnsJSONObj['PARKING_FEATURE']) != "YES") {
    RefactorApp::addParkingFeatureToDeliteList();
} else {
    unset($_REQUEST['PARKING_MODULE']);
    unset($_REQUEST['PARKING_MODULE_FILES']);
}
############################################################ Dynamic Features ############################################################

if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
    RefactorApp::addVOIPToDeliteList();
} else {
    unset($_REQUEST['VOIP_SERVICE']);
    unset($_REQUEST['VOIP_SERVICE_FILES']);
}


if (strtoupper(PACKAGE_TYPE) == "STANDARD" || strtoupper(PACKAGE_TYPE) == "ENTERPRISE") {
    /*     * ************* Remove Shark Features ************** */

    if (!empty($_REQUEST['ADVERTISEMENT_MODULE']) && ($_REQUEST['ADVERTISEMENT_MODULE'] == "Yes" || $_REQUEST['ADVERTISEMENT_MODULE'] == "YES")) {
        $deleteAppFilesArr['Advertisement Feature'] = $_REQUEST['ADVERTISEMENT_MODULE_FILES'];
    }

    if (!empty($_REQUEST['LINKEDIN_MODULE']) && ($_REQUEST['LINKEDIN_MODULE'] == "Yes" || $_REQUEST['LINKEDIN_MODULE'] == "YES")) {
        $deleteAppFilesArr['LinkedIn Feature'] = $_REQUEST['LINKEDIN_MODULE_FILES'];
    }

    RefactorApp::addPoolFeatureToDeliteList();

    if (!empty($_REQUEST['CARD_IO']) && ($_REQUEST['CARD_IO'] == "Yes" || $_REQUEST['CARD_IO'] == "YES")) {
        $deleteAppFilesArr['CardIO Feature'] = $_REQUEST['CARD_IO_FILES'];
    }

    if (!empty($_REQUEST['LIVE_CHAT']) && ($_REQUEST['LIVE_CHAT'] == "Yes" || $_REQUEST['LIVE_CHAT'] == "YES")) {
        $deleteAppFilesArr['LiveChat Feature'] = $_REQUEST['LIVE_CHAT_FILES'];
    }

    RefactorApp::addBusinessProfileFeatureToDeliteList();

    if (!empty($_REQUEST['NEWS_SECTION']) && ($_REQUEST['NEWS_SECTION'] == "Yes" || $_REQUEST['NEWS_SECTION'] == "YES")) {
        $deleteAppFilesArr['News Feature'] = $_REQUEST['NEWS_SERVICE_FILES'];
    }

    RefactorApp::addFavDriverToDeliteList();
    RefactorApp::addBookForElseToDeliteList();
    RefactorApp::addEndOfDayTripToDeliteList();
    RefactorApp::addMultiStopOverPointsToDeliteList();
} else {
// Unset common features

    unset($_REQUEST['ADVERTISEMENT_MODULE']);
    unset($_REQUEST['ADVERTISEMENT_MODULE_FILES']);

    unset($_REQUEST['LINKEDIN_MODULE']);
    unset($_REQUEST['LINKEDIN_MODULE_FILES']);

    unset($_REQUEST['CARD_IO']);
    unset($_REQUEST['CARD_IO_FILES']);

    unset($_REQUEST['LIVE_CHAT']);
    unset($_REQUEST['LIVE_CHAT_FILES']);

    unset($_REQUEST['NEWS_SECTION']);
    unset($_REQUEST['NEWS_SERVICE_FILES']);
}

if (strtoupper(ONLYDELIVERALL) == "YES") {
    RefactorApp::addMultiDeliveryToDeliteList();
    RefactorApp::addSingleDeliveryToDeliteList();
    RefactorApp::addUberXServicesToDeliteList();
    RefactorApp::addOnGoingJobsToDeliteList();
    RefactorApp::addCommonDeliveryTypesToDeliteList();
    RefactorApp::addTollFeatureToDeliteList();
    RefactorApp::addSafetyToDeliteList();
    RefactorApp::addSafetyCheckListToDeliteList();
    RefactorApp::addSafetyFacemaskVerificationToDeliteList();
    RefactorApp::addRentalFeatureToDeliteList();
    RefactorApp::addPoolFeatureToDeliteList();
    RefactorApp::addBusinessProfileFeatureToDeliteList();
    RefactorApp::addRideSectionToDeliteList();
    RefactorApp::addRDUToDeliteList();

    RefactorApp::addFavDriverToDeliteList();
    RefactorApp::addBookForElseToDeliteList();
    RefactorApp::addEndOfDayTripToDeliteList();
    RefactorApp::addMultiStopOverPointsToDeliteList();
    RefactorApp::addDriverSubscriptionToDeliteList();

    if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
        RefactorApp::addWayBillToDeliteList();
    } else {
        unset($_REQUEST['WAYBILL_MODULE']);
        unset($_REQUEST['WAYBILL_MODULE_FILES']);
    }
    if ((!empty($Deliverall) && strtoupper($Deliverall) == "NO") == false) {
        unset($_REQUEST['DELIVER_ALL']);
        unset($_REQUEST['DELIVER_ALL_FILES']);
    }
} else {
    unset($_REQUEST['RDU_SECTION']);
    unset($_REQUEST['RDU_SECTION_FILES']);

    if ($APP_TYPE == "Ride") {
        RefactorApp::addEighteenPlusToDeliteList();
        RefactorApp::addGenieFeatureToDeliteList();
        RefactorApp::addContactlessFeatureToDeliteList();
        RefactorApp::addMultiSingleStoreToDeliteList();
        RefactorApp::addCategorywiseStoeToDeliteList(); 
        RefactorApp::addSearchByItemNameToDeliteList();
        RefactorApp::addTimeslotToDeliteList();
        RefactorApp::addDeliverAllToDeliteList();
        RefactorApp::addMultiDeliveryToDeliteList();
        RefactorApp::addSingleDeliveryToDeliteList();
        RefactorApp::addUberXServicesToDeliteList();
        RefactorApp::addOnGoingJobsToDeliteList();
        RefactorApp::addCommonDeliveryTypesToDeliteList();
        RefactorApp::addEndOfDayTripToDeliteList();
        RefactorApp::addMultiStopOverPointsToDeliteList();
        RefactorApp::addFavDriverToDeliteList();
        RefactorApp::addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            RefactorApp::addSafetyToDeliteList();
            RefactorApp::addTollFeatureToDeliteList();
            RefactorApp::addSafetyCheckListToDeliteList();
            RefactorApp::addSafetyFacemaskVerificationToDeliteList();
            RefactorApp::addRentalFeatureToDeliteList();
            RefactorApp::addWayBillToDeliteList();
        } else {
            unset($_REQUEST['RENTAL_FEATURE']);
            unset($_REQUEST['RENTAL_SERVICE_FILES']);

            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);
        }

        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            unset($_REQUEST['POOL_MODULE']);
            unset($_REQUEST['POOL_MODULE_FILES']);

            unset($_REQUEST['BUSINESS_PROFILE_FEATURE']);
            unset($_REQUEST['BUSINESS_PROFILE_FILES']);

            unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
            unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);
        }
        unset($_REQUEST['RIDE_SECTION']);
        unset($_REQUEST['RIDE_SECTION_FILES']);
    }

    if ($APP_TYPE == "Delivery") {
        RefactorApp::addSearchByItemNameToDeliteList();
        RefactorApp::addEighteenPlusToDeliteList();
        RefactorApp::addGenieFeatureToDeliteList();
        RefactorApp::addContactlessFeatureToDeliteList();
        RefactorApp::addMultiSingleStoreToDeliteList();
        RefactorApp::addCategorywiseStoeToDeliteList(); 
        RefactorApp::addTimeslotToDeliteList();
        RefactorApp::addDeliverAllToDeliteList();
        RefactorApp::addUberXServicesToDeliteList();
        RefactorApp::addSafetyToDeliteList();
        RefactorApp::addTollFeatureToDeliteList();
        RefactorApp::addSafetyCheckListToDeliteList();
        RefactorApp::addSafetyFacemaskVerificationToDeliteList();
        RefactorApp::addRentalFeatureToDeliteList();
        RefactorApp::addPoolFeatureToDeliteList();
        RefactorApp::addBusinessProfileFeatureToDeliteList();
        RefactorApp::addBookForElseToDeliteList();
        RefactorApp::addEndOfDayTripToDeliteList();
        RefactorApp::addMultiStopOverPointsToDeliteList();
        RefactorApp::addFavDriverToDeliteList();
        RefactorApp::addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            RefactorApp::addWayBillToDeliteList();
            RefactorApp::addMultiDeliveryToDeliteList();
            RefactorApp::addCommonDeliveryTypesToDeliteList();
            RefactorApp::addOnGoingJobsToDeliteList();
        } else {

            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);

            unset($_REQUEST['MULTI_DELIVERY']);
            unset($_REQUEST['MULTI_DELIVERY_FILES']);

            unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']);
            unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);

            unset($_REQUEST['ON_GOING_JOB_SECTION']);
            unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
        }

        unset($_REQUEST['DELIVERY_MODULE']);
        unset($_REQUEST['DELIVERY_MODULE_FILES']);

        unset($_REQUEST['RIDE_SECTION']);
        unset($_REQUEST['RIDE_SECTION_FILES']);
    }

    if ($APP_TYPE == "UberX") {
        RefactorApp::addSearchByItemNameToDeliteList();
        RefactorApp::addEighteenPlusToDeliteList();
        RefactorApp::addGenieFeatureToDeliteList();
        RefactorApp::addContactlessFeatureToDeliteList();
        RefactorApp::addMultiSingleStoreToDeliteList();
        RefactorApp::addCategorywiseStoeToDeliteList(); 
        RefactorApp::addTimeslotToDeliteList();
        RefactorApp::addDeliverAllToDeliteList();
        RefactorApp::addSafetyToDeliteList();
        RefactorApp::addTollFeatureToDeliteList();
        RefactorApp::addSafetyCheckListToDeliteList();
        RefactorApp::addSafetyFacemaskVerificationToDeliteList();
        RefactorApp::addRentalFeatureToDeliteList();
        RefactorApp::addPoolFeatureToDeliteList();
        RefactorApp::addBusinessProfileFeatureToDeliteList();
        RefactorApp::addSingleDeliveryToDeliteList();
        RefactorApp::addMultiDeliveryToDeliteList();
        RefactorApp::addRideSectionToDeliteList();
        RefactorApp::addCommonDeliveryTypesToDeliteList();
        RefactorApp::addBookForElseToDeliteList();
        RefactorApp::addEndOfDayTripToDeliteList();
        RefactorApp::addMultiStopOverPointsToDeliteList();
        RefactorApp::addFavDriverToDeliteList();
        RefactorApp::addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            RefactorApp::addWayBillToDeliteList();
        } else {
            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);
        }

        if (strtoupper($IS_UFX_SERVICE_AVAIL) == "YES") {
            unset($_REQUEST['UBERX_SERVICE']);
            unset($_REQUEST['UBERX_FILES']);
        }

        unset($_REQUEST['ON_GOING_JOB_SECTION']);
        unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
    }
    if ($APP_TYPE == "Ride-Delivery") {
        RefactorApp::addSearchByItemNameToDeliteList();
        RefactorApp::addEighteenPlusToDeliteList();
        RefactorApp::addGenieFeatureToDeliteList();
        RefactorApp::addContactlessFeatureToDeliteList();
        RefactorApp::addMultiSingleStoreToDeliteList();
        RefactorApp::addCategorywiseStoeToDeliteList(); 
        RefactorApp::addTimeslotToDeliteList();
        RefactorApp::addDeliverAllToDeliteList();
        RefactorApp::addUberXServicesToDeliteList();
        RefactorApp::addEndOfDayTripToDeliteList();
        RefactorApp::addMultiStopOverPointsToDeliteList();
        RefactorApp::addFavDriverToDeliteList();
        RefactorApp::addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            RefactorApp::addSafetyToDeliteList();
            RefactorApp::addTollFeatureToDeliteList();
            RefactorApp::addSafetyCheckListToDeliteList();
            RefactorApp::addSafetyFacemaskVerificationToDeliteList();
            RefactorApp::addRentalFeatureToDeliteList();
            RefactorApp::addWayBillToDeliteList();

            RefactorApp::addMultiDeliveryToDeliteList();
            RefactorApp::addOnGoingJobsToDeliteList();
        } else {
            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);

            unset($_REQUEST['RENTAL_FEATURE']);
            unset($_REQUEST['RENTAL_SERVICE_FILES']);

            unset($_REQUEST['MULTI_DELIVERY']);
            unset($_REQUEST['MULTI_DELIVERY_FILES']);

            unset($_REQUEST['ON_GOING_JOB_SECTION']);
            unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
        }

        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            unset($_REQUEST['POOL_MODULE']);
            unset($_REQUEST['POOL_MODULE_FILES']);

            unset($_REQUEST['BUSINESS_PROFILE_FEATURE']);
            unset($_REQUEST['BUSINESS_PROFILE_FILES']);

            unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
            unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);
        } else {
            RefactorApp::addPoolFeatureToDeliteList();
            RefactorApp::addBusinessProfileFeatureToDeliteList();

            RefactorApp::addBookForElseToDeliteList();
            RefactorApp::addEndOfDayTripToDeliteList();
            RefactorApp::addMultiStopOverPointsToDeliteList();
        }

        unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']);
        unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);

        unset($_REQUEST['DELIVERY_MODULE']);
        unset($_REQUEST['DELIVERY_MODULE_FILES']);

        unset($_REQUEST['RIDE_SECTION']);
        unset($_REQUEST['RIDE_SECTION_FILES']);
    }

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        // addEndOfDayTripToDeliteList();
        // addMultiStopOverPointsToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            RefactorApp::addSafetyToDeliteList();
            RefactorApp::addTollFeatureToDeliteList();
            RefactorApp::addSafetyCheckListToDeliteList();
            RefactorApp::addSafetyFacemaskVerificationToDeliteList();
            RefactorApp::addRentalFeatureToDeliteList();
            RefactorApp::addWayBillToDeliteList();
        } else {
            if($IS_RIDE_MODULE_AVAIL || $IS_DELIVERY_MODULE_AVAIL || $IS_UFX_MODULE_AVAIL){
                unset($_REQUEST['WAYBILL_MODULE']);
                unset($_REQUEST['WAYBILL_MODULE_FILES']);
            }
            
            if($IS_RIDE_MODULE_AVAIL){
                unset($_REQUEST['RENTAL_FEATURE']);
                unset($_REQUEST['RENTAL_SERVICE_FILES']);   
            }
        }
        if (strtoupper(PACKAGE_TYPE) != "SHARK") {
            RefactorApp::addSearchByItemNameToDeliteList();
            RefactorApp::addEighteenPlusToDeliteList();
            RefactorApp::addGenieFeatureToDeliteList();
            RefactorApp::addContactlessFeatureToDeliteList();
            RefactorApp::addMultiSingleStoreToDeliteList();
            RefactorApp::addCategorywiseStoeToDeliteList(); 
            RefactorApp::addTimeslotToDeliteList();
            RefactorApp::addDeliverAllToDeliteList();
            RefactorApp::addPoolFeatureToDeliteList();
            RefactorApp::addBusinessProfileFeatureToDeliteList();

            RefactorApp::addMultiDeliveryToDeliteList();
            RefactorApp::addBookForElseToDeliteList();
            RefactorApp::addEndOfDayTripToDeliteList();
            RefactorApp::addMultiStopOverPointsToDeliteList();

            RefactorApp::addFavDriverToDeliteList();
            RefactorApp::addDriverSubscriptionToDeliteList();
            RefactorApp::addDonationToDeliteList();

            /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
            $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR = array();
            $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp = explode(",", $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);
            $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "No";
            for ($i = 0; $i < scount($COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp); $i++) {
                $item_tmp_file = $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp[$i];

                if ($item_tmp_file != "" && $item_tmp_file != "GeneralFiles/OpenCatType.swift" && $item_tmp_file != "com.general.files.OpenCatType") {
                    $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR[] = $item_tmp_file;
                    $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "Yes";
                }
            }
            $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES'] = implode(",", $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR);
            /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
            RefactorApp::addCommonDeliveryTypesToDeliteList();
        } else {
            if ((!empty($Deliverall) && strtoupper($Deliverall) == "NO") == false) {
                unset($_REQUEST['DELIVER_ALL']);
                unset($_REQUEST['DELIVER_ALL_FILES']);
            }
            
            if($IS_RIDE_MODULE_AVAIL){
                    
                unset($_REQUEST['POOL_MODULE']);
                unset($_REQUEST['POOL_MODULE_FILES']);
    
                unset($_REQUEST['BUSINESS_PROFILE_FEATURE']);
                unset($_REQUEST['BUSINESS_PROFILE_FILES']);
                
                unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
                unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);

            }
            
            if($IS_DELIVERY_MODULE_AVAIL){
                unset($_REQUEST['MULTI_DELIVERY']);
                unset($_REQUEST['MULTI_DELIVERY_FILES']);
                
                unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']);
                unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);
            }
        }

        if (strtoupper($IS_UFX_SERVICE_AVAIL) == "YES") {
            unset($_REQUEST['UBERX_SERVICE']);
            unset($_REQUEST['UBERX_FILES']);
        }

        if($IS_RIDE_MODULE_AVAIL){
            unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
            unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);
        }

        if($IS_DELIVERY_MODULE_AVAIL || $IS_UFX_MODULE_AVAIL){
            unset($_REQUEST['ON_GOING_JOB_SECTION']);
            unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
        }

        if($IS_DELIVERY_MODULE_AVAIL){
            unset($_REQUEST['DELIVERY_MODULE']);
            unset($_REQUEST['DELIVERY_MODULE_FILES']);  
        }

        if($IS_RIDE_MODULE_AVAIL || $IS_DELIVERY_MODULE_AVAIL || $IS_UFX_MODULE_AVAIL){
            unset($_REQUEST['RIDE_SECTION']);
            unset($_REQUEST['RIDE_SECTION_FILES']);   
        }
    }
}
//echo "<pre>";print_r($deleteAppFilesArr);die;
if (!empty($deleteAppFilesArr) && $IS_CONTINUE_DELETE_PROCESS == true && false) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script>
            function openSvnModal() {
                $("#appdatamodal").modal('show');
            }
            function confirmPassword() {
                var svnUrl = $("#svnurl").val();
                var svnUsername = $("#svnUsername").val();
                var svnPassword = $("#svnPassword").val();
                if (svnUrl == "") {
                    alert("Please enter SVN URl");
                    return false;
                }
                if (svnUsername == "") {
                    alert("Please enter SVN Username");
                    return false;
                }
                if (svnPassword == "") {
                    alert("Please enter SVN Password");
                    return false;
                }
                var retVal = confirm("Do you want to continue ?");
                if (retVal == true) {
                    $('#appdatamodal').modal('hide');
                    document.getElementById("svnform").submit();
                    return true;
                } else {
                    return false;
                }
            }
        </script>
        <?php
        $svnData = "";
        if (isset($_REQUEST)) {
            $svnData = json_encode($_REQUEST);
            $svnData = urlencode($svnData);
        }
        ?>
        <div id="appdatamodal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">SVN Details</h4>
                    </div>
                    <form action='app_configuration_file_action.php' method='post' id="svnform">
                        <input type='hidden' value='<?= $svnData; ?>' name='APP_CONFIG_PARAMS_PACKAGE'>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>SVN Url :</label>
                                    <input type="text" required="" class="form-control" name="svnurl" placeholder="SVN URL">
                                </div><br><br><br><br>
                                <div class="col-lg-6">
                                    <label>SVN Username :</label>
                                    <input type="text" required="" class="form-control" name="svnUsername" id="svnUsername" placeholder="Username">
                                </div>
                                <div class="col-lg-6">
                                    <label>SVN Password :</label>
                                    <input type="text" required="" class="form-control" id="svnPassword" name="svnPassword" placeholder="Password">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" formtarget="_blank" onclick="return confirmPassword();" class="btn btn-primary">Next >></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    $total_count = 0;
    foreach ($deleteAppFilesArr as $key => $value) {
        $total_count += scount(explode(",",$value));
    }
    $headerPortionHtml_str_prefix = "<!DOCTYPE html><html><head><style> table {font-family: arial, sans-serif; border-collapse: collapse; width: 100%; padding: 15px;} td, th { border: 2px solid #5a5a5a; text-align: left; padding: 8px;} tr:nth-child(even) { background-color: #dddddd;} </style> </head> <body style=\"width:100%; padding: 15px;\"> <h2>Remove Files From App's Code<button type='button' onClick='openSvnModal();' class='btn btn-primary' data-toggle='modal' style=\"position: absolute; right: 15px;\" data-target='#myModal'>Delete Files From SVN</button></h2><table> <tr> <th text-align=\"center\">Feature Name</th> <th width=\"200px\">Number of Files (".$total_count.")</th> <th>List of files Or Libraries to Delete</th> </tr>";
    foreach ($deleteAppFilesArr as $key => $value) {
        $str_tr = "";
        $data_arr_count = scount(explode(",",$value));
        $str_value = "<tr><td>" . $key . "</td><td>".$data_arr_count."</td><td>" . str_replace(",", "<BR/>", $value) . "</td></tr>";
        $headerPortionHtml_str_prefix = $headerPortionHtml_str_prefix . $str_value;
    }
    $headerPortionHtml_str_postfix = "</table></body></html>";
    $files_delete_str_html = $headerPortionHtml_str_prefix . $headerPortionHtml_str_postfix;
    echo $files_delete_str_html;
    exit;
}

################################# Check Files Of Android Applications ##########################################
?>
