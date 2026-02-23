<?php
function scount($value) {
    if (is_array($value) || $value instanceof Countable) {
        return count($value);
    }

    return 0;
}

$svn_url = "";
if (isset($_POST['svnurl'])) {
    $svn_url = $_POST['svnurl'];
}
if (isset($_POST['APP_CONFIG_PARAMS_PACKAGE']) && !empty($_POST['APP_CONFIG_PARAMS_PACKAGE'])) {
    $APP_CONFIG_PARAMS_PACKAGE = json_decode(urldecode($_POST['APP_CONFIG_PARAMS_PACKAGE']));

    // echo "<pre>";print_r($APP_CONFIG_PARAMS_PACKAGE);die;
    if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GeneralDeviceType')) {
        $PACKAGE_NAME = $GeneralDeviceType = $UserType = "";
        if (isset($APP_CONFIG_PARAMS_PACKAGE->GeneralDeviceType)) {
            $GeneralDeviceType = $APP_CONFIG_PARAMS_PACKAGE->GeneralDeviceType;
        }
        if (isset($APP_CONFIG_PARAMS_PACKAGE->PACKAGE_NAME)) {
            $PACKAGE_NAME = $APP_CONFIG_PARAMS_PACKAGE->PACKAGE_NAME;
        }
        if (isset($APP_CONFIG_PARAMS_PACKAGE->UserType)) {
            $UserType = $APP_CONFIG_PARAMS_PACKAGE->UserType;
        }
        //$svn_url = "http://192.168.1.120:8080/svn/v3c_prodution_test_v5_workspace/Android/Taxi/CubeRideEnterprise/PassengerApp";
        $svnUsername = $_POST['svnUsername'];
        $svnPassword = $_POST['svnPassword'];
        $arrOfGeneratedLinks = $arrOfLinksToDelete = array();
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'WAYBILL_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->WAYBILL_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'WAYBILL_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->WAYBILL_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->WAYBILL_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'VOIP_SERVICE') && $APP_CONFIG_PARAMS_PACKAGE->VOIP_SERVICE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'VOIP_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->VOIP_SERVICE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->VOIP_SERVICE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ADVERTISEMENT_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->ADVERTISEMENT_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ADVERTISEMENT_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->ADVERTISEMENT_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->ADVERTISEMENT_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LINKEDIN_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->LINKEDIN_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LINKEDIN_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->LINKEDIN_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->LINKEDIN_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'POOL_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->POOL_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'POOL_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->POOL_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->POOL_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CARD_IO') && $APP_CONFIG_PARAMS_PACKAGE->CARD_IO == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CARD_IO_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->CARD_IO_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->CARD_IO_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LIVE_CHAT') && $APP_CONFIG_PARAMS_PACKAGE->LIVE_CHAT == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LIVE_CHAT_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->LIVE_CHAT_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->LIVE_CHAT_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVER_ALL') && $APP_CONFIG_PARAMS_PACKAGE->DELIVER_ALL == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVER_ALL_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DELIVER_ALL_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DELIVER_ALL_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MULTI_DELIVERY') && $APP_CONFIG_PARAMS_PACKAGE->MULTI_DELIVERY == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MULTI_DELIVERY_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->MULTI_DELIVERY_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->MULTI_DELIVERY_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'UBERX_SERVICE') && $APP_CONFIG_PARAMS_PACKAGE->UBERX_SERVICE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'UBERX_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->UBERX_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->UBERX_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ON_GOING_JOB_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->ON_GOING_JOB_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ON_GOING_JOB_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->ON_GOING_JOB_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->ON_GOING_JOB_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'COMMON_DELIVERY_TYPE_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->COMMON_DELIVERY_TYPE_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'COMMON_DELIVERY_TYPE_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->COMMON_DELIVERY_TYPE_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->COMMON_DELIVERY_TYPE_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'NEWS_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->NEWS_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'NEWS_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->NEWS_SERVICE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->NEWS_SERVICE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RENTAL_FEATURE') && $APP_CONFIG_PARAMS_PACKAGE->RENTAL_FEATURE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RENTAL_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RENTAL_SERVICE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RENTAL_SERVICE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BUSINESS_PROFILE_FEATURE') && $APP_CONFIG_PARAMS_PACKAGE->BUSINESS_PROFILE_FEATURE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BUSINESS_PROFILE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->BUSINESS_PROFILE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->BUSINESS_PROFILE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVERY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->DELIVERY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVERY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DELIVERY_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DELIVERY_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RIDE_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->RIDE_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RIDE_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RIDE_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RIDE_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RDU_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->RDU_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RDU_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RDU_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RDU_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'FAV_DRIVER_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->FAV_DRIVER_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'FAV_DRIVER_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->FAV_DRIVER_SECTION_FILES)) {
                $FAV_DRIVER_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->FAV_DRIVER_SECTION_FILES);
                foreach ($FAV_DRIVER_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BOOK_FOR_ELSE_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->BOOK_FOR_ELSE_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BOOK_FOR_ELSE_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->BOOK_FOR_ELSE_SECTION_FILES)) {
                $BOOK_FOR_ELSE_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->BOOK_FOR_ELSE_SECTION_FILES);
                foreach ($BOOK_FOR_ELSE_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'END_OF_DAY_TRIP_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->END_OF_DAY_TRIP_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'END_OF_DAY_TRIP_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->END_OF_DAY_TRIP_SECTION_FILES)) {
                $END_OF_DAY_TRIP_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->END_OF_DAY_TRIP_SECTION_FILES);
                foreach ($END_OF_DAY_TRIP_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STOP_OVER_POINT_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->STOP_OVER_POINT_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STOP_OVER_POINT_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->STOP_OVER_POINT_SECTION_FILES)) {
                $STOP_OVER_POINT_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->STOP_OVER_POINT_SECTION_FILES);
                foreach ($STOP_OVER_POINT_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DRIVER_SUBSCRIPTION_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->DRIVER_SUBSCRIPTION_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DRIVER_SUBSCRIPTION_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DRIVER_SUBSCRIPTION_SECTION_FILES)) {
                $DRIVER_SUBSCRIPTION_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DRIVER_SUBSCRIPTION_SECTION_FILES);
                foreach ($DRIVER_SUBSCRIPTION_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GO_PAY_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->GO_PAY_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GO_PAY_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->GO_PAY_SECTION_FILES)) {
                $GO_PAY_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->GO_PAY_SECTION_FILES);
                foreach ($GO_PAY_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DONATION_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->DONATION_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DONATION_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DONATION_SECTION_FILES)) {
                $DONATION_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DONATION_SECTION_FILES);
                foreach ($DONATION_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'THERMAL_PRINT_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->THERMAL_PRINT_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'THERMAL_PRINT_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->THERMAL_PRINT_MODULE_FILES)) {
                $THERMAL_PRINT_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->THERMAL_PRINT_MODULE_FILES);
                foreach ($THERMAL_PRINT_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'FLY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->FLY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'FLY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->FLY_MODULE_FILES)) {
                $FLY_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->FLY_MODULE_FILES);
                foreach ($FLY_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STORE_INDIVIDUALDAY_AVAILABILITY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->STORE_INDIVIDUALDAY_AVAILABILITY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES)) {
                $STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES);
                foreach ($STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STORE_SEARCH_BY_ITEM_NAME_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->STORE_SEARCH_BY_ITEM_NAME_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES)) {
                $STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES);
                foreach ($STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MANUAL_TOLL_FEATURE_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->MANUAL_TOLL_FEATURE_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MANUAL_TOLL_FEATURE_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->MANUAL_TOLL_FEATURE_MODULE_FILES)) {
                $MANUAL_TOLL_FEATURE_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->MANUAL_TOLL_FEATURE_MODULE_FILES);
                foreach ($MANUAL_TOLL_FEATURE_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->SAFETY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->SAFETY_MODULE_FILES)) {
                $SAFETY_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->SAFETY_MODULE_FILES);
                foreach ($SAFETY_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_RATING_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->SAFETY_RATING_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_RATING_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->SAFETY_RATING_MODULE_FILES)) {
                $SAFETY_RATING_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->SAFETY_RATING_MODULE_FILES);
                foreach ($SAFETY_RATING_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_CHECK_LIST_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->SAFETY_CHECK_LIST_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_CHECK_LIST_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->SAFETY_CHECK_LIST_MODULE_FILES)) {
                $SAFETY_CHECK_LIST_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->SAFETY_CHECK_LIST_MODULE_FILES);
                foreach ($SAFETY_CHECK_LIST_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_FACEMASK_VERIFICATION_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->SAFETY_FACEMASK_VERIFICATION_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'SAFETY_FACEMASK_VERIFICATION_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->SAFETY_FACEMASK_VERIFICATION_MODULE_FILES)) {
                $SAFETY_FACEMASK_VERIFICATION_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->SAFETY_FACEMASK_VERIFICATION_MODULE_FILES);
                foreach ($SAFETY_FACEMASK_VERIFICATION_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'EIGHTEEN_PLUS_FEATURE_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->EIGHTEEN_PLUS_FEATURE_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'EIGHTEEN_PLUS_FEATURE_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->EIGHTEEN_PLUS_FEATURE_MODULE_FILES)) {
                $EIGHTEEN_PLUS_FEATURE_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->EIGHTEEN_PLUS_FEATURE_MODULE_FILES);
                foreach ($EIGHTEEN_PLUS_FEATURE_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GENIE_FEATURE_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->GENIE_FEATURE_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GENIE_FEATURE_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->GENIE_FEATURE_MODULE_FILES)) {
                $GENIE_FEATURE_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->GENIE_FEATURE_MODULE_FILES);
                foreach ($GENIE_FEATURE_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CONTACTLESS_DELIVERY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->CONTACTLESS_DELIVERY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CONTACTLESS_DELIVERY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->CONTACTLESS_DELIVERY_MODULE_FILES)) {
                $CONTACTLESS_DELIVERY_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->CONTACTLESS_DELIVERY_MODULE_FILES);
                foreach ($CONTACTLESS_DELIVERY_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MULTI_SINGLE_STORE_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->MULTI_SINGLE_STORE_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MULTI_SINGLE_STORE_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->MULTI_SINGLE_STORE_MODULE_FILES)) {
                $MULTI_SINGLE_STORE_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->MULTI_SINGLE_STORE_MODULE_FILES);
                foreach ($MULTI_SINGLE_STORE_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CATEGORY_WISE_STORE_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->CATEGORY_WISE_STORE_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CATEGORY_WISE_STORE_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->CATEGORY_WISE_STORE_MODULE_FILES)) {
                $CATEGORY_WISE_STORE_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->CATEGORY_WISE_STORE_MODULE_FILES);
                foreach ($CATEGORY_WISE_STORE_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BID_SERVICE') && $APP_CONFIG_PARAMS_PACKAGE->BID_SERVICE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BID_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->BID_SERVICE_FILES)) {
                $BID_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->BID_SERVICE_FILES);
                foreach ($BID_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LIVE_ACTIVITY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->LIVE_ACTIVITY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LIVE_ACTIVITY_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->LIVE_ACTIVITY_FILES)) {
                $LIVE_ACTIVITY_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->LIVE_ACTIVITY_FILES);
                foreach ($LIVE_ACTIVITY_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RENT_ITEM_SERVICE') && $APP_CONFIG_PARAMS_PACKAGE->RENT_ITEM_SERVICE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RENT_ITEM_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RENT_ITEM_SERVICE_FILES)) {
                $RENT_ITEM_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RENT_ITEM_SERVICE_FILES);
                foreach ($RENT_ITEM_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'TRACKING_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->TRACKING_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'TRACKING_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->TRACKING_MODULE_FILES)) {
                $TRACKING_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->TRACKING_MODULE_FILES);
                foreach ($TRACKING_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RIDE_SHARE_PRO_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->RIDE_SHARE_PRO_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RIDE_SHARE_PRO_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RIDE_SHARE_PRO_MODULE_FILES)) {
                $RIDE_SHARE_PRO_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RIDE_SHARE_PRO_MODULE_FILES);
                foreach ($RIDE_SHARE_PRO_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'NEARBY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->NEARBY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'NEARBY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->NEARBY_MODULE_FILES)) {
                $NEARBY_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->NEARBY_MODULE_FILES);
                foreach ($NEARBY_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GIFTCARD_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->GIFTCARD_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GIFTCARD_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->GIFTCARD_MODULE_FILES)) {
                $GIFTCARD_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->GIFTCARD_MODULE_FILES);
                foreach ($GIFTCARD_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'PARKING_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->PARKING_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'PARKING_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->PARKING_MODULE_FILES)) {
                $PARKING_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->PARKING_MODULE_FILES);
                foreach ($PARKING_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (strtoupper($GeneralDeviceType) == "ANDROID") {
            foreach ($arrOfGeneratedLinks as $value) {
                if (strpos($value, "res/layout") === 0) {
                    $resourceFileURL = $svn_url . "/app/src/main/" . $value . ".xml";
                    $arrOfLinksToDelete[] = $resourceFileURL;
                } else if (strpos($value, "libs/") === 0) {
                    $resourceFileURL = $svn_url . "/app/" . $value;
                    $arrOfLinksToDelete[] = $resourceFileURL;
                } else {
                    $filePath = "";
                    $filePathArr = explode(".", $value);
                    for ($i = 0; $i < scount($filePathArr); $i++) {
                        $filePath = $filePath . "/" . $filePathArr[$i];
                    }
                    
                    /* $filePath = $svn_url . "/app/src/main/java" . $filePath . ".java";
                    $arrOfLinksToDelete[] = $filePath;
                    $filePath = $svn_url . "/app/src/main/java" . $filePath . ".kt";
                    $arrOfLinksToDelete[] = $filePath; */
                    $arrOfLinksToDelete[] = $svn_url . "/app/src/main/java" . $filePath . ".java";
                    $arrOfLinksToDelete[] = $svn_url . "/app/src/main/java" . $filePath . ".kt";
                }
            }
        } else if (strtoupper($GeneralDeviceType) == "IOS") {
            foreach ($arrOfGeneratedLinks as $value) {
                $filePath = $svn_url . "/" . $value;
                $arrOfLinksToDelete[] = $filePath;
            }
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'APPLE_WATCH_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->APPLE_WATCH_MODULE == "Yes") {
                if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'APPLE_WATCH_APP_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->APPLE_WATCH_APP_FILES)) {
                    $APPLE_WATCH_APP_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->APPLE_WATCH_APP_FILES);
                    $svn_url_new = explode("/", $svn_url);
                    array_pop($svn_url_new);
                    $svn_url_new = implode("/", $svn_url_new);
                    foreach ($APPLE_WATCH_APP_FILES_arr as $value) {
                        $filePath = $svn_url_new . "/" . str_replace(" ", "%20", $value);
                        $arrOfLinksToDelete[] = $filePath;
                    }
                }
            }
        }

        if (scount($arrOfLinksToDelete) > 0) {
            foreach ($arrOfLinksToDelete as $value) {
                exec("/usr/bin/svn delete --no-auth-cache -m 'FilterCode' --username '" . $svnUsername . "' --password '" . $svnPassword . "' " . $value, $o, $m);
            }
        }
    }
}
?>
<html>
    <body>
        <div>
            <h1>Process Done! Files has been deleted as per your project and package type</h1>
            <p><strong>Please take update in your project and clean a project with mentioned URL: "<?= $svn_url; ?>"</strong> <br>If you have any trobule on project setup, please consult develpment team.</p>
        </div>
    </body>
</html>