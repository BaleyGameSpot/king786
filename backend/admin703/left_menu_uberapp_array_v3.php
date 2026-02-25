<?php

//added by SP as discussed with bmam on 28-6-2019
$adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'];
//Added By HJ On 16-06-2020 For Custome App Type CubejekX-Deliverall As Per Dicsuss With KS Start
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem();
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
// $deliverallModule = strtoupper(DELIVERALL);
$deliverallModule = $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') ? "YES" : "NO";
if ($cubeDeliverallOnly > 0) {
    $onlyDeliverallModule = "YES";
}
//Added By HJ On 16-06-2020 For Custome App Type CubejekX-Deliverall As Per Dicsuss With KS End
if ($PACKAGE_TYPE == 'SHARK' && ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery-UberX') && $onlyDeliverallModule == 'NO') {
    $adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'];
    if (strtoupper(ENABLEHOTELPANEL) == "YES") { //added by SP to chk hotel panel enable then only shown word at admin side.
        $adminUsersTxt .= '/Hotel';
    }
}
//Added By HJ On 15-06-2020 For Custome Setup - CubejekX-Deliverall As Per Discuss With KS - Manage Service Menu End
$addOnsDataArr = $obj->MySQLSelect("SELECT lAddOnConfiguration,eCubejekX,eCubeX,eRideX,eDeliverallX FROM setup_info LIMIT 0,1");
$addOnsDataArr_orig = $addOnsDataArr;
$addOnData = json_decode($addOnsDataArr[0]['lAddOnConfiguration'], true);
$eCubeX = $eCubejekX = $eRideX = $eDeliverallX = "No";
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
if (strtoupper($eCubejekX) == "YES" || strtoupper($eCubeX) == "YES" || strtoupper($eDeliverallX) == "YES") {
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }
}
//Added By HJ On 15-06-2020 For Custome Setup - CubejekX-Deliverall As Per Discuss With KS - Manage Service Menu End
//var_dump($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);
//var_dump($langage_lbl_admin);
$restaurantAdmin = "Store";
if (isset($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'])) {
    $restaurantAdmin = $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];
}
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel();
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel();
$rideEnabled = $MODULES_OBJ->isRideFeatureAvailable('Yes');
$deliveryEnabled = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes');
$foodCategoryAvailable = "No";
if (scount($service_categories_ids_arr) >= 1 && in_array(1, $service_categories_ids_arr)) {
    $foodCategoryAvailable = "Yes";
}
$menu = [
    [
        'title'   => 'Dashboard',
        'url'     => "dashboard.php",
        "icon"    => 'ri-dashboard-line',
        "active"  => "dashboard",
        "visible" => ($onlyDeliverallModule == "NO"),
    ],
    [
        'title'   => 'Dashboard',
        'url'     => "store-dashboard.php",
        "icon"    => 'ri-dashboard-line',
        "visible" => ($onlyDeliverallModule == "YES"),
    ],
    [
        'title'   => 'Server Monitoring',
        'url'     => "server_admin_dashboard.php",
        "icon"    => 'ri-bar-chart-box-line',
        "active"  => "server_dashboard",
        "visible" => ($userObj->hasPermission('manage-server-admin-dashboard') && $MODULES_OBJ->isEnableAppHomeScreenLayoutV2()),
    ],
    [
        'title'   => 'Profile',
        'url'     => "profile.php",
        "icon"    => "ri-profile-line",
        "active"  => "profile",
        "visible" => $userObj->hasPermission('manage-profile') && $_SESSION['sess_iGroupId'] != '1' /* && $APP_TYPE == 'Ride' */ && ($hotelPanel > 0 || $kioskPanel > 0),
    ],
    [
        'title'   => 'Create request',
        'url'     => "create_request.php",
        "icon"    => "ri-book-2-line",
        "active"  => "booking",
        "visible" => $userObj->hasPermission('manage-create-request') && $_SESSION['sess_iGroupId'] != '1' /* && $APP_TYPE == 'Ride' */ && ($hotelPanel > 0 || $kioskPanel > 0),
        "target"  => "blank",
    ],
    [
        'title'    => 'Admin',
        "icon"     => "ri-admin-line",
        "visible"  => ($userObj->hasRole(1) || $userObj->hasPermission('view-admin')),
        'children' => [
            [
                'title'  => $adminUsersTxt,
                'url'    => "admin.php",
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => "Admin",
            ],
            [
                'title'   => 'Admin Groups',
                'url'     => "admin_groups.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "AdminGroups",
                "visible" => $userObj->hasRole(1) && $PACKAGE_TYPE == 'SHARK',
            ]
        ],
    ],
    [
        'title'   => 'Company',
        'url'     => "company.php",
        "icon"    => "ri-building-4-line",
        "active"  => "Company",
        "visible" => $userObj->hasPermission('view-company'),
    ],
    [
        'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
        'url'     => "driver.php",
        "icon"    => "ri-user-2-line",
        "active"  => "Driver",
        "visible" => $userObj->hasPermission('view-providers'),
    ],
    [
        'title'    => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION'],
        "icon"     => "ri-price-tag-2-line",
        "visible"  => (($userObj->hasPermission('manage-driver-subscription') || $userObj->hasPermission('manage-driver-subscription-report')) && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && $onlyDeliverallModule != "YES"),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'],
                'url'     => "driver_subscription.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "DriverSubscriptionPlan",
                "visible" => ($userObj->hasPermission('manage-driver-subscription') && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && $onlyDeliverallModule != "YES"),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'],
                'url'     => "driver_subscription_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "DriverSubscriptionReport",
                "visible" => ($userObj->hasPermission('manage-driver-subscription-report') && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && $onlyDeliverallModule != "YES"),
            ],
        ],
    ],
    [
        'title'    => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Reward",
        "icon"     => "ri-medal-line",
        "visible"  => ($userObj->hasPermission('manage-driver-reward-setting') || $userObj->hasPermission('view-driver-reward-report')) && $MODULES_OBJ->isEnableDriverRewardModule(),
        'children' => [
            [
                'title'   => 'Reward Reports',
                'url'     => "reports.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Reports",
                "visible" => $userObj->hasPermission('view-driver-reward-report'),
            ],
            [
                'title'   => 'Reward Setting',
                'url'     => "reward.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Reward",
                "visible" => $userObj->hasPermission('manage-driver-reward-setting'),
            ],
        ],
    ],
    [
        'title'   => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url'     => "rider.php",
        "icon"    => "ri-team-line",
        "active"  => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ],
    [
        'title'    => "Manage Tracking Service",
        "icon"     => "ri-user-location-line",
        "visible"  => $userObj->hasPermission('manage-track-service') && $MODULES_OBJ->isEnableTrackServiceFeature(),
        'children' => [
            [
                'title'   => "Company",
                'url'     => "track_service_company.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceCompany",
                "visible" => $userObj->hasPermission('view-track-service-company')
            ],
            [
                'title'   => "User",
                'url'     => "track_service_user.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceUser",
                "visible" => $userObj->hasPermission('view-track-service-users')
            ],
            [
                'title'   => "Driver",
                'url'     => "track_service_driver.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceDriver",
                "visible" => $userObj->hasPermission('view-track-service-driver')
            ],
            [
                'title'   => "Driver Vehicles",
                'url'     => "track_service_driver_vehicle.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceDriverVehicle",
                "visible" => $userObj->hasPermission('view-track-service-driver-vehicle')
            ],
            [
                'title'   => "Trip Report",
                'url'     => "track_service_trips.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceTrips",
                "visible" => $userObj->hasPermission('view-track-service-trips')
            ]
        ],
    ],
    [
        'title'    => $langage_lbl_admin['LBL_RIDE_SHARE_TXT'],
        "icon"     => "ri-car-washing-line",
        "visible"  => $userObj->hasPermission('manage-ride-share-service') && $MODULES_OBJ->isEnableRideShareService(),
        'children' => [
            [
                'title'   => "Published Rides",
                'url'     => "published_rides.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "PublishedRides",
                "visible" => $userObj->hasPermission('view-ride-share-published-rides')
            ],
            [
                'title'   => "Bookings",
                'url'     => "ride_share_bookings.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideShareBookings",
                "visible" => $userObj->hasPermission('view-ride-share-bookings')
            ],
            [
                'title'   => "Payment Report",
                'url'     => "ride_share_payment_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideSharePaymentReport",
                "visible" => $userObj->hasPermission('view-ride-share-payment-report')
            ],/*[
                'title' => "Reviews",
                'url' => "ride_share_reviews.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "RideShareReviews",
                "visible" => $userObj->hasPermission('view-ride-share-reviews')
            ],*/
            [
                'title'   => "Driver Details Fields",
                'url'     => "driver_details_field.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideShareDriverFields",
                "visible" => $userObj->hasPermission('view-ride-share-driver-detail-fields')
            ],
        ],
    ],
    [
        'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Vehicles",
        'url'     => "vehicles.php",
        "icon"    => "ri-taxi-line",
        "active"  => "Vehicle",
        "visible" => ($userObj->hasPermission('view-provider-taxis') && ($APP_TYPE != 'Ride-Delivery-UberX' || $onlyDeliverallModule == "YES")),
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_RENTAL_TXT'],
        'url'     => "rental_vehicle_list.php",
        "icon"    => "ri-taxi-line",
        "active"  => "Rental Package",
        "visible" => ($userObj->hasPermission('view-rental-packages') && ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery') && ENABLE_RENTAL_OPTION == 'Yes' && $leftrideEnable == 'Yes'),
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
        'url'     => "vehicle_type.php",
        "icon"    => "ri-taxi-line",
        "active"  => "VehicleType",
        "visible" => ($userObj->hasPermission('view-vehicle-type') && $APP_TYPE != 'Ride-Delivery-UberX'),
    ],
    [
        'title'   => 'Service Category',
        "url"     => "vehicle_category.php",
        "icon"    => "fa fa-certificate",
        "active"  => "VehicleCategory",
        "visible" => ($userObj->hasPermission('view-vehicle-category') && $APP_TYPE != 'Ride-Delivery-UberX' && ($APP_TYPE != "Ride" && $onlyDeliverallModule != "YES")),
    ],
    [
        'title'    => "Manage Bidding",
        "icon"     => "ri-auction-line",
        "visible"  => $userObj->hasPermission('view-bidding-category') && $MODULES_OBJ->isEnableBiddingServices(),
        'children' => [
            [
                'title'   => 'Bidding Services',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-category'),
                "active"  => "bidding",
                "url"     => "bidding_master_category.php",
            ],
            [
                'title'   => 'Bidding Requests',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-category'),
                "active"  => "biddingDriverRequest",
                "url"     => "bidding_driver_request.php",
            ],
            [
                'title'   => 'Bidding Report',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('manage-bids-trip'),
                "active"  => "Bids",
                "url"     => "bidding_report.php",
            ],
            [
                'title'   => 'Bidding Review',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-review'),
                "active"  => "BidReviews",
                "url"     => "bidding_review.php",
            ],
        ],
    ],
    [
        'title'    => "Buy, Sell & Rent",
        "icon"     => "ri-auction-line",
        "visible"  => ($userObj->hasPermission('view-rentitem-category') && ($MODULES_OBJ->isEnableRentItemService() || $MODULES_OBJ->isEnableRentEstateService() || $MODULES_OBJ->isEnableRentCarsService())),
        'children' => [
            [
                'title'    => 'General Items',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => (($userObj->hasPermission('view-rentitem-category') || $userObj->hasPermission('rentitem-pending') || $userObj->hasPermission('rentitem-approved') || $userObj->hasPermission('all-rentitems') || $userObj->hasPermission('manage-rentitem-fields') || $userObj->hasPermission('view-payment-plan') || $userObj->hasPermission('rentitems-report')) && $MODULES_OBJ->isEnableRentItemService()),
                "active"   => "RentItem",
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => 'bsr_master_category.php?eType=GeneralItem',
                        "icon"    => "",
                        "active"  => "RentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('view-rentitem-category')),
                    ],
                    [
                        'title'   => 'Pending for Approval',
                        "url"     => "pending_item.php?eType=GeneralItem",
                        "icon"    => "",
                        "active"  => "PendingRentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('rentitem-pending')),
                    ],
                    [
                        'title'   => "Approved Items",
                        "url"     => "item_approved.php?eType=GeneralItem",
                        "icon"    => "",
                        "active"  => "ApprovedRentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('rentitem-approved')),
                    ],
                    [
                        'title'   => "All Items",
                        "url"     => "all_bsr_items.php?eType=GeneralItem",
                        "icon"    => "",
                        "active"  => "AllRentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('all-rentitems')),
                    ],
                    [
                        'title'   => "Data Fields",
                        "url"     => "data_fields.php?eType=RentItem",
                        "icon"    => "",
                        "active"  => "RentItemFields",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('manage-rentitem-fields')) && ENABLE_DATAFEILDS_ADMIN == 'Yes',
                    ],
                    [
                        'title'   => "Payment Plans",
                        "url"     => "item_payment_plans.php?eType=GeneralItem",
                        "icon"    => "",
                        "active"  => "RentItemPaymentPlan",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('view-payment-plan')),
                    ],
                    [
                        'title'   => "Payment Report",
                        "url"     => "bsr_item_payment_report.php?eType=GeneralItem",
                        "icon"    => "",
                        "active"  => "RentItemReport",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('rentitems-report')),
                    ],
                    [
                        'title'   => "Banner",
                        "url"     => "bsr_banner.php?eType=GeneralItem",
                        "icon"    => "",
                        "active"  => "RentItemBanner",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('view-banner')),
                    ],
                ],
            ],
            [
                'title'    => 'Properties',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => (($userObj->hasPermission('view-rentitem-category') || $userObj->hasPermission('rentitem-pending') || $userObj->hasPermission('rentitem-approved') || $userObj->hasPermission('all-rentitems') || $userObj->hasPermission('manage-rentitem-fields') || $userObj->hasPermission('view-payment-plan') || $userObj->hasPermission('rentitems-report')) && $MODULES_OBJ->isEnableRentEstateService()),
                "active"   => "RentEstate",
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => 'bsr_master_category.php?eType=RealEstate',
                        "icon"    => "",
                        "active"  => "RentEstate",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('view-rentitem-category')),
                    ],
                    [
                        'title'   => 'Pending for Approval',
                        "url"     => "pending_item.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "PendingRentEstate",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('rentitem-pending')),
                    ],
                    [
                        'title'   => "Approved Properties",
                        "url"     => "item_approved.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "ApprovedRentEstate",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('rentitem-approved')),
                    ],
                    [
                        'title'   => "All Properties",
                        "url"     => "all_bsr_items.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "AllRentEstate",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('all-rentitems')),
                    ],
                    [
                        'title'   => "Data Fields",
                        "url"     => "data_fields.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "RentEstateFields",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('manage-rentitem-fields')) && ENABLE_DATAFEILDS_ADMIN == 'Yes',
                    ],
                    [
                        'title'   => "Payment Plans",
                        "url"     => "item_payment_plans.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "RentEstatePaymentPlan",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('view-payment-plan')),
                    ],
                    [
                        'title'   => "Payment Report",
                        "url"     => "bsr_item_payment_report.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "RentEstateReport",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('rentitems-report')),
                    ],
                    [
                        'title'   => "Banner",
                        "url"     => "bsr_banner.php?eType=RealEstate",
                        "icon"    => "",
                        "active"  => "RentEstateBanner",
                        "visible" => ($MODULES_OBJ->isEnableRentEstateService() && $userObj->hasPermission('view-banner')),
                    ],
                ],
            ],
            [
                'title'    => 'Cars',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => (($userObj->hasPermission('view-rentitem-category') || $userObj->hasPermission('rentitem-pending') || $userObj->hasPermission('rentitem-approved') || $userObj->hasPermission('all-rentitems') || $userObj->hasPermission('manage-rentitem-fields') || $userObj->hasPermission('view-payment-plan') || $userObj->hasPermission('rentitems-report')) && $MODULES_OBJ->isEnableRentCarsService()),
                "active"   => "RentCars",
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => 'bsr_master_category.php?eType=Cars',
                        "icon"    => "",
                        "active"  => "RentCars",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('view-rentitem-category')),
                    ],
                    [
                        'title'   => 'Pending for Approval',
                        "url"     => "pending_item.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "PendingRentCars",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('rentitem-pending')),
                    ],
                    [
                        'title'   => "Approved Cars",
                        "url"     => "item_approved.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "ApprovedRentCars",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('rentitem-approved')),
                    ],
                    [
                        'title'   => "All Cars",
                        "url"     => "all_bsr_items.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "AllRentCars",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('all-rentitems')),
                    ],
                    [
                        'title'   => "Data Fields",
                        "url"     => "data_fields.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "RentCarsFields",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('manage-rentitem-fields')) && ENABLE_DATAFEILDS_ADMIN == 'Yes',
                    ],
                    [
                        'title'   => "Payment Plans",
                        "url"     => "item_payment_plans.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "RentCarsPaymentPlan",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('view-payment-plan')),
                    ],
                    [
                        'title'   => "Payment Report",
                        "url"     => "bsr_item_payment_report.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "RentCarsReport",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('rentitems-report')),
                    ],
                    [
                        'title'   => "Banner",
                        "url"     => "bsr_banner.php?eType=Cars",
                        "icon"    => "",
                        "active"  => "RentCarsBanner",
                        "visible" => ($MODULES_OBJ->isEnableRentCarsService() && $userObj->hasPermission('view-banner')),
                    ],
                ],
            ],
        ],
    ],
    [
        'title'    => "Nearby Management",
        "icon"     => "ri-pin-distance-line",
        "visible"  => ($MODULES_OBJ->isEnableNearByService() && $userObj->hasPermission('manage-nearby-places')),
        'children' => [
            [
                'title'   => "NearBy Category",
                "url"     => "near_by_category.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "nearbyCategory",
                "visible" => ($MODULES_OBJ->isEnableNearByService() && $userObj->hasPermission('manage-nearby-places')),
            ],
            [
                'title'   => "NearBy Places",
                "url"     => "near_by_places.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "nearbyPlaces",
                "visible" => ($MODULES_OBJ->isEnableNearByService() && $userObj->hasPermission('manage-nearby-places')),
            ],
        ],
    ],
    [
        'title'    => "Manage Services",
        "icon"     => "ri-list-settings-line",
        "visible"  => ($userObj->hasPermission('manage-services') && $APP_TYPE == 'Ride-Delivery-UberX'),
        'children' => [
            [
                'title'    => $langage_lbl_admin['LBL_RIDE_DELIVERY_VEHICLE_TXT'] . ' Vehicles',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-vehicles'),
                'children' => [
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' Vehicles',
                        "url"     => "vehicles.php",
                        "icon"    => "",
                        "active"  => "Vehicle",
                        "visible" => $userObj->hasPermission('view-provider-taxis'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                        "url"     => "vehicle_type.php",
                        "icon"    => "",
                        "active"  => "VehicleType",
                        "visible" => $userObj->hasPermission('view-vehicle-type') && ($rideEnabled || $deliveryEnabled),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_RENTAL_TXT'],
                        "url"     => "rental_vehicle_list.php",
                        "icon"    => "",
                        "active"  => "Rental Package",
                        "visible" => ($userObj->hasPermission('view-rental-packages') && ENABLE_RENTAL_OPTION == 'Yes' && $leftrideEnable == 'Yes'),
                    ],
                ],
            ],
            [
                'title'    => 'Manage Other Services',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-vehicle-category', 'view-service-type']),
                'children' => [
                    [
                        'title'   => 'Service Category',
                        "url"     => $MODULES_OBJ->isEnableAppHomeScreenLayout() && !$leftdeliverykingthemeon && !$leftmedicalservicethemeon ? "master_service_category.php" : "vehicle_category.php",
                        "icon"    => "",
                        "active"  => "VehicleCategory",
                        "visible" => $MODULES_OBJ->isEnableAppHomeScreenLayout() && !$leftdeliverykingthemeon ? $userObj->hasPermission('view-master-service-category') : $userObj->hasPermission('view-vehicle-category'),
                    ],
                    [
                        'title'   => "Service Type",
                        "url"     => "service_type.php",
                        "icon"    => "",
                        "active"  => "ServiceType",
                        "visible" => $userObj->hasPermission('view-service-type') && ($APP_TYPE == 'Ride-Delivery-UberX' && $leftufxserviceon == 1),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_SERVICE_REQUESTS_TXT'],
                        "url"     => "driver_service_request.php",
                        "icon"    => "",
                        "active"  => "DriverRequest",
                        "visible" => (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == "UberX") && $leftufxserviceon == 1),
                    ],
                ],
            ], /*[
                'title' => 'Manage Rent Item',
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission(['rentitem-pending', 'ApprovedRentItem', 'all-rentitems', 'view-payment-plan', 'manage-rentitem-fields'])),
                'children' => [
                    [
                        'title' => 'Pending for Approval',
                        "url" => "pending_item.php",
                        "icon" => "",
                        "active" => "PendingRentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('rentitem-pending')),
                    ], [
                        'title' => "Approved Rent Items",
                        "url" => "item_approved.php",
                        "icon" => "",
                        "active" => "ApprovedRentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('rentitem-approved')),
                    ],
                    [
                        'title' => "All Rent Items",
                        "url" => "all_bsr_items.php",
                        "icon" => "",
                        "active" => "AllRentItem",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('all-rentitems')),
                    ],
                    [
                        'title' => "Payment Plans",
                        "url" => "item_payment_plans.php",
                        "icon" => "",
                        "active" => "RentItemPaymentPlan",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('view-payment-plan')),
                    ],
                    [
                        'title' => "Rent Item Fields",
                        "url" => "data_fields.php",
                        "icon" => "",
                        "active" => "RentItem Fields",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('manage-rentitem-fields')),
                    ],
                    [
                        'title' => "Rent Items Report",
                        "url" => "bsr_item_payment_report.php",
                        "icon" => "",
                        "active" => "RentItemReport",
                        "visible" => ($MODULES_OBJ->isEnableRentItemService() && $userObj->hasPermission('rentitems-report')),
                    ],
                ],
            ],*/
        ],
    ],
    [
        'title'    => "Manage Fly Vehicles", //added by SP for fly stations on 13-08-2019
        "icon"     => "ri-flight-takeoff-line",
        "visible"  => $userObj->hasPermission('manage-fly-vehicles') && $ENABLE_FLY_VEHICLES == 'Yes' && $MODULES_OBJ->isAirFlightModuleAvailable('', 'Yes'),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_FLY_STATIONS'],
                "url"     => "fly_stations.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "fly_stations",
                "visible" => $userObj->hasPermission('view-fly-stations'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_FLY_VEHICLE_TYPE'],
                "url"     => "fly_vehicle_type.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "FlyVehicleType",
                "visible" => $userObj->hasPermission('view-fly-vehicle-type'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_FLY_LOCATION_WISE_FARE'],
                "url"     => "fly_locationwise_fare.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "fly_locationwise_fare",
                "visible" => $userObj->hasPermission('view-fly-fare'),
            ],
        ],
    ],
    /* [
    'title' => "App Main Screen Settings",
    'url' => "app_home_settings.php",
    "icon" => "fa fa-globe",
    "active" => "App Main Screen Settings",
    "visible" => ($userObj->hasPermission('manage-app-main-screen-settings') && $APP_TYPE == 'Ride-Delivery-UberX'),
    ], */
    [
        'title'   => "Manual Booking ",
        'url'     => "add_booking.php",
        "icon"    => "ri-book-2-line",
        "active"  => "booking",
        "visible" => $userObj->hasPermission('manage-manual-booking') && $onlyDeliverallModule == "NO" && ($_SESSION['SessionUserType'] == 'hotel' ? !$MODULES_OBJ->isManualBookingAvailable() : $_SESSION['SessionUserType'] != 'hotel'),
        "target"  => "blank",
        // && ($APP_TYPE != "Delivery" || ($APP_TYPE == "Delivery" && $APP_DELIVERY_MODE != "Multi"))
    ],
    [
        'title'   => $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'],
        'url'     => "cab_booking.php",
        "icon"    => "ri-file-list-line",
        "active"  => "CabBooking",
        "visible" => ($userObj->hasPermission('manage-ride-job-later-bookings') && $RIDE_LATER_BOOKING_ENABLED == 'Yes'),
    ],
    [
        'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
        'url'     => "trip.php",
        "icon"    => "ri-file-list-line",
        "active"  => "Trips",
        "visible" => ($userObj->hasPermission('manage-trip-jobs') && $onlyDeliverallModule == "NO"),
    ],
    [
        'title'    => "Manage Stores",
        "icon"     => "ri-store-2-line",
        "visible"  => ($userObj->hasPermission('manage-stores') && $deliverallModule == "YES" && $leftdeliverallEnable == "Yes"),
        'children' => [
            [
                'title'   => 'Store Dashboard',
                "url"     => "store_dashboard.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Store Dashboard",
                "visible" => ($userObj->hasPermission('manage-store-dashboard') && $onlyDeliverallModule == "NO" && !$MODULES_OBJ->isEnableAppHomeScreenLayoutV2()),
            ],
            [
                'title'   => $restaurantAdmin,
                "url"     => "store.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "DeliverAllStore",
                "visible" => $userObj->hasPermission('view-store'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "store_vehicle_type.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "StoreVehicleType",
                "visible" => $userObj->hasPermission('view-vehicle-type'),
            ],
            [
                'title'    => $restaurantAdmin . " Items",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-store-items'),
                'children' => [
                    [
                        'title'   => "Import Bulk Items",
                        "url"     => "import_item_data.php",
                        "icon"    => "",
                        "active"  => "ImportItem",
                        "visible" => (($userObj->hasPermission('view-item-categories') || $userObj->hasPermission('view-item')) && $MODULES_OBJ->isEnableBulkImportItems()),
                    ],
                    [
                        'title'   => "Item Category",
                        "url"     => "food_menu.php",
                        "icon"    => "",
                        "active"  => "FoodMenu",
                        "visible" => $userObj->hasPermission('view-item-categories'),
                    ],
                    [
                        'title'   => "Items",
                        "url"     => "menu_item.php",
                        "icon"    => "",
                        "active"  => "MenuItems",
                        "visible" => $userObj->hasPermission('view-item'),
                    ],
                    [
                        'title'   => "Item Type",
                        "url"     => "cuisine.php",
                        "icon"    => "",
                        "active"  => "Cuisine",
                        "visible" => $userObj->hasPermission('view-item-type'),
                    ],
                ],
            ],
            [
                'title'    => "Store Orders",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-orders'),
                'children' => [
                    [
                        'title'   => "Processing",
                        "url"     => "allorders.php?type=processing",
                        "icon"    => "",
                        "active"  => "Processing Orders",
                        "visible" => $userObj->hasPermission('view-processing-orders'),
                    ],
                    [
                        'title'   => "Cancelled",
                        "url"     => "cancelled_orders.php",
                        "icon"    => "",
                        "active"  => "CancelledOrders",
                        "visible" => $userObj->hasPermission('view-cancelled-orders'),
                    ],
                    [
                        'title'   => "All Orders",
                        "url"     => "allorders.php?type=allorders",
                        "icon"    => "",
                        "active"  => "All Orders",
                        "visible" => $userObj->hasPermission('view-all-orders'),
                    ],
                ],
            ],
            [
                'title'    => "Site Earning",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-site-earning'),
                'children' => [
                    [
                        'title'   => "Admin Earning",
                        "url"     => "admin_payment_report.php",
                        "icon"    => "",
                        "active"  => "Admin Payment_Report",
                        "visible" => $userObj->hasPermission('manage-admin-earning'),
                    ],
                    [
                        'title'   => "Payout to " . $restaurantAdmin,
                        "url"     => "restaurants_pay_report.php",
                        "icon"    => "",
                        "active"  => "Restaurant Payment Report",
                        "visible" => $userObj->hasPermission('manage-store-payment'),
                    ],
                    [
                        'title'   => "Payout to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                        "url"     => "store_driver_pay_report.php",
                        "icon"    => "",
                        "active"  => "Deliverall Driver Payment Report",
                        "visible" => $userObj->hasPermission('manage-provider-payment'),
                    ],
                    [
                        'title'   => "Cancelled / Refunded Order Report",
                        "url"     => "cancelled_report.php",
                        "icon"    => "",
                        "active"  => "Cancelled Order Report",
                        "visible" => $userObj->hasPermission('manage-cancelled-order-report'),
                    ],
                ],
            ],
            [
                'title'    => "Utility",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-store-utility'),
                'children' => [
                    [
                        'title'   => "DeliveryAll Service Category",
                        "url"     => "service_category.php",
                        "icon"    => "",
                        "active"  => "service_category",
                        "visible" => $userObj->hasPermission('view-service-category') && $onlyDeliverallModule == 'YES' && $leftdeliverallEnable == 'Yes',
                    ],
                    [
                        'title'   => "Order Status",
                        "url"     => "order_status.php",
                        "icon"    => "",
                        "active"  => "order_status",
                        "visible" => $userObj->hasPermission('view-order-status'),
                    ],
                    [
                        'title'   => "Delivery Charges",
                        "url"     => "delivery_charges.php",
                        "icon"    => "",
                        "active"  => "Delivery Charges",
                        "visible" => $userObj->hasPermission('view-delivery-charges'),
                    ],
                    [
                        'title'   => "Distance wise Delivery Charges",
                        "url"     => "custom_delivery_charge_order.php",
                        "icon"    => "",
                        "active"  => "Custom Delivery Charges",
                        "visible" => $userObj->hasPermission('view-custom-delivery-charges') && $MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder(),
                    ],
                    [
                        'title'   => "Banner",
                        "url"     => "store_banner.php",
                        "icon"    => "",
                        "active"  => "Store Banner",
                        "visible" => $userObj->hasPermission('view-banner'),
                    ],
                    [
                        'title'   => "Manage " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Categories",
                        "url"     => "store_category.php",
                        "icon"    => "",
                        "active"  => "ManageStoreCategories",
                        "visible" => ($userObj->hasPermission('view-store-categories') && $MODULES_OBJ->isStoreClassificationEnable()),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DELIVERY_PREF'],
                        'url'     => "delivery_preferences.php",
                        "icon"    => "",
                        "active"  => "DeliveryPreferences",
                        "visible" => ($userObj->hasPermission('view-delivery-preference') && $MODULES_OBJ->isDeliveryPreferenceEnable()),
                    ],
                    [
                        'title'   => "Manage OTP For Service Categories",
                        'url'     => "manage_otp_for_stores.php",
                        "icon"    => "",
                        "active"  => "otpservicecategory",
                        "visible" => ($userObj->hasPermission('manage-otp-for-stores') && $MODULES_OBJ->isEnableOTPVerificationDeliverAll()),
                    ],
                    [
                        'title'   => "Rating Feedback Questions",
                        'url'     => "rating_feedback_ques.php",
                        "icon"    => "",
                        "active"  => "RatingFeedbackQuestions",
                        "visible" => ($userObj->hasPermission('view-rating-feedback-ques') && $MODULES_OBJ->isEnableFoodRatingDetailFlow() && $foodCategoryAvailable == "Yes"),
                    ],
                    /* [

                'title'   => "Cancel Reason",

                "url"     => "store_cancel_reason.php",

                "icon"    => "fa fa-question",

                "active"  => "cancel_reason",

                "visible" => $userObj->hasPermission('view-cancel-reasons'),

                ], */
                ],
            ],
            [
                'title'   => "Orders Reviews",
                "url"     => "store_review.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Store Review",
                "visible" => $userObj->hasPermission('manage-store-reviews'),
            ],
        ],
    ],
    [
        'title'   => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
        'url'     => "../user-order-information?order=admin",
        "icon"    => "ri-book-read-line",
        "active"  => "store_order_book",
        "target"  => "blank",
        "visible" => ($userObj->hasPermission('manage-restaurant-order') && $deliverallModule == "YES" && $MANUAL_STORE_ORDER_ADMIN_PANEL == "Yes"),
    ],
    [
        'title'   => "PromoCode",
        "url"     => "coupon.php",
        "icon"    => "ri-coupon-line",
        "active"  => "Coupon",
        "visible" => $userObj->hasPermission('view-promocode'),
    ],
    [
        'title'    => "Manage Gift Cards",
        "icon"     => "ri-gift-line",
        "visible"  => $userObj->hasPermission('view-giftcard'),
        'children' => [
            [
                'title'   => "Gift Cards",
                'url'     => "gift_card.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "GiftCard",
                "visible" => $userObj->hasPermission('view-giftcard')
            ],
            [
                'title'   => "EGV Design Theme",
                'url'     => "gift_card_images.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "GiftCardImages",
                "visible" => $userObj->hasPermission('view-giftcard')
            ],
        ],
    ],
    [
        'title'   => "God's View",
        "url"     => "map_godsview.php",
        "icon"    => "ri-road-map-line",
        "active"  => "LiveMap",
        "visible" => $userObj->hasPermission('manage-gods-view'),
    ],
    [
        'title'   => "Heat View",
        "url"     => "heatmap.php",
        "icon"    => "ri-treasure-map-line",
        "active"  => "Heat Map",
        "visible" => $userObj->hasPermission('manage-heat-view'),
    ],
    [
        'title'   => "Reviews",
        "url"     => "review.php",
        "icon"    => "ri-user-voice-line",
        "active"  => "Review",
        "visible" => ($userObj->hasPermission('manage-reviews') && $onlyDeliverallModule == "NO"),
    ],
    [
        'title'   => "Advertisement Banners",
        'url'     => "advertise_banners.php",
        "icon"    => "ri-advertisement-line",
        "active"  => "Advertisement Banners",
        "visible" => $userObj->hasPermission('view-advertise-banner') && ($ADVERTISEMENT_TYPE != 'Disable' && $PACKAGE_TYPE == 'SHARK'),
    ],
    [
        'title'    => 'Decline/Cancelled Alert',
        'url'      => "blocked_driver.php",
        "icon"     => "ri-user-unfollow-line",
        "active"   => "blockeddriver",
        "visible"  => $userObj->hasPermission('view-blocked-driver') && $PACKAGE_TYPE == 'SHARK' && $onlyDeliverallModule != "YES",
        //This Module Enable For Shark Package As Per Discss With KS Sir By HJ On 05-11-2019
        'children' => [
            [
                'title'   => 'Alert For ' . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                'url'     => "blocked_driver.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "blockeddriver",
                "visible" => $userObj->hasPermission('view-blocked-driver'),
            ],
            [
                'title'   => 'Alert For ' . $langage_lbl_admin['LBL_RIDER'],
                'url'     => "blocked_rider.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "blockedrider",
                "visible" => $userObj->hasPermission('view-blocked-rider'),
            ],
        ],
    ],
    [
        'title'    => "Reports",
        "icon"     => "ri-numbers-line",
        "visible"  => $userObj->hasPermission('manage-report'),
        'children' => [
            [
                'title'   => "Payment Report",
                "url"     => "payment_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Payment_Report",
                "visible" => ($userObj->hasPermission('manage-payment-report') && $onlyDeliverallModule == "NO"),
            ],
            [
                'title'   => "Hotel Payment Report",
                "url"     => "hotel_payment_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "hotelPayment_Report",
                "visible" => $userObj->hasPermission('manage-hotel-payment-report') && ($hotelPanel > 0 || $kioskPanel > 0),
            ],
            [
                'title'   => "Referral Report",
                "url"     => "referrer.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "referrer",
                "visible" => ($userObj->hasPermission('manage-referral-report') && $REFERRAL_SCHEME_ENABLE == 'Yes'),
            ],
            [
                'title'   => "Wallet Report",
                "url"     => "wallet_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Wallet Report",
                "visible" => ($userObj->hasPermission('manage-user-wallet-report') && $WALLET_ENABLE == 'Yes'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Payment Report",
                "url"     => "driver_pay_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Driver Payment Report",
                "visible" => ($userObj->hasPermission('manage-provider-payment-report') && $onlyDeliverallModule == "NO"),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Log Report",
                "url"     => "driver_log_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Driver Log Report",
                "visible" => $userObj->hasPermission('manage-provider-log-report'),
            ],
            [
                'title'   => "Cancelled " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'],
                "url"     => "cancelled_trip.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "CancelledTrips",
                "visible" => ($userObj->hasPermission('manage-cancelled-trip-job-report') && $onlyDeliverallModule == "NO"),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Acceptance Report",
                "url"     => "ride_acceptance_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Driver Accept Report",
                "visible" => ($userObj->hasPermission('manage-trip-job-request-acceptance-report') && $onlyDeliverallModule == "NO"),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Time Variance",
                "url"     => "driver_trip_detail.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Driver Trip Detail",
                "visible" => ($userObj->hasPermission('manage-trip-job-time-variance-report') && $onlyDeliverallModule == "NO"),
            ],
            [
                'title'    => "Insurance Report",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $MODULES_OBJ->isInsuranceReportEnable() && $userObj->hasPermission('manage-payment-report'),
                'children' => [
                    [
                        'title'   => "Idle Time",
                        "url"     => "insurance_idle_report.php",
                        "icon"    => "",
                        "active"  => "Insurance_Idle_time_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_IDLE_REPORT == 'Yes',
                    ],
                    [
                        'title'   => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Accept",
                        "url"     => "insurance_accept_report.php",
                        "icon"    => "",
                        "active"  => "Insurance_accept_trip_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_ACCEPT_REPORT == 'Yes',
                    ],
                    [
                        'title'   => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Start",
                        "url"     => "insurance_trip_report.php",
                        "icon"    => "",
                        "active"  => "Insurance_start_trip_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_TRIP_REPORT == 'Yes',
                    ],
                ],
            ],
            [
                'title'   => "User Outstanding Report",
                "url"     => "outstanding_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "outstanding_report",
                "visible" => $userObj->hasPermission('view-user-outstanding-report'),
            ],
        ],
    ],
    [
        'title'    => "Support Requests",
        "icon"     => "ri-customer-service-2-line",
        "visible"  => ($userObj->hasPermission('view-contactus-report') || $userObj->hasPermission('view-sos-request-report') || $userObj->hasPermission('view-trip-job-help-request-report') || $userObj->hasPermission('view-order-help-request-report') || $userObj->hasPermission('view-payment-request-report') || $userObj->hasPermission('view-withdraw-request-report')),
        'children' => [
            [
                'title'   => "Contact Us Form Requests",
                "url"     => "contactus.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "contactus",
                "visible" => $userObj->hasPermission('view-contactus-report'),
            ],
            [
                'title'   => "SOS Requests",
                "url"     => "emergency_contact_data.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "emergency_contact_data",
                "visible" => $userObj->hasPermission('view-sos-request-report'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . " Help Requests",
                "url"     => "trip_help_details.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "trip_help_details",
                "visible" => $userObj->hasPermission('view-trip-job-help-request-report') && $onlyDeliverallModule == "NO",
            ],
            [
                'title'   => "Order Help Requests",
                "url"     => "order_help_details.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "order_help_details",
                "visible" => $userObj->hasPermission('view-order-help-request-report') && $deliverallModule == "YES",
            ],
            [
                'title'   => "Payment Requests",
                "url"     => "payment_requests_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "payment_requests",
                "visible" => $userObj->hasPermission('view-payment-request-report'),
            ],
            [
                'title'   => "Withdraw Requests",
                "url"     => "withdraw_requests_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "withdraw_requests",
                "visible" => $userObj->hasPermission('view-withdraw-request-report'),
            ]
        ]
    ],
    [
        'title'    => "Manage Locations",
        "icon"     => "ri-map-pin-line",
        "visible"  => $userObj->hasPermission('manage-locations'),
        'children' => [
            [
                'title'   => "Geo Fence Location",
                "url"     => "location.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Location",
                "visible" => $userObj->hasPermission('view-geo-fence-locations'),
            ],
            [
                'title'   => "Restricted Area",
                "url"     => "restricted_area.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Restricted Area",
                "visible" => $userObj->hasPermission('view-restricted-area'),
            ],
            [
                'title'   => "Locationwise Fare",
                "url"     => "locationwise_fare.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "locationwise_fare",
                "visible" => $userObj->hasPermission('view-location-wise-fare') && $PACKAGE_TYPE != 'STANDARD' && $APP_TYPE != 'Delivery' && $APP_TYPE != 'UberX' && $MODULES_OBJ->isRideFeatureAvailable('Yes'),
            ],
            [
                'title'   => "Airport Surcharge",
                "url"     => "airport_surcharge.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "airportsurcharge_fare",
                "visible" => $userObj->hasPermission('view-airport-surcharge') && $ENABLE_AIRPORT_SURCHARGE_SECTION == "Yes" && $APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery' && ($onlyDeliverallModule == 'NO' && $MODULES_OBJ->isRideFeatureAvailable('Yes')),
            ],
        ],
    ],
    [
        'title'    => "Settings",
        "icon"     => "ri-settings-5-line",
        "visible"  => $userObj->hasPermission('manage-settings'),
        'children' => [
            [
                'title'   => "General",
                "url"     => "general.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "General",
                "visible" => $userObj->hasPermission('manage-general-settings'),
            ],
            [
                'title'   => "Referral Settings",
                "url"     => "referral_settings.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Referral",
                "visible" => ($userObj->hasPermission('view-referral-settings') && $MODULES_OBJ->isEnableMultiLevelReferralSystem()),
            ],
            [
                'title'   => "Email Templates",
                "url"     => "email_template.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "email_templates",
                "visible" => $userObj->hasPermission('view-email-templates'),
            ],
            [
                'title'   => "SMS Templates",
                "url"     => "sms_template.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "sms_templates",
                "visible" => $userObj->hasPermission('view-sms-templates'),
            ],
            [
                'title'   => "Manage Documents",
                "url"     => "document_master_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Document Master",
                "visible" => $userObj->hasPermission('view-documents'),
            ],
            [
                'title'   => "Language Label - Pages",
                "url"     => "master_lng_pages.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "MasterLanguagePages",
                "visible" => $userObj->hasPermission('view-documents') && $IS_INHOUSE_DOMAINS && $displayInhousePage > 0,
            ],
            [
                'title'    => "Language Label",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-language-label') && ($deliverallModule == 'YES' || $onlyDeliverallModule == 'YES'),
                'children' => function () {
                    global $allservice_cat_data, $userObj;
                    $languages_childs = [
                        [
                            'title'   => "General Label",
                            "url"     => "languages.php",
                            "icon"    => "",
                            "active"  => "language_label",
                            "visible" => $userObj->hasPermission('view-general-label'),
                        ],
                        /*[
                    'title' => "Manage Screen Label",
                    "icon" => "fa fa-globe",
                    "visible" => $userObj->hasPermission('manage-screen-label'),
                    'children' => [
                    [
                    'title' => "Manage App Screen",
                    "url" => "app_screen.php",
                    "icon" => "fa fa-dot-circle-o",
                    "active" => "Manage Screen",
                    "visible" => $userObj->hasPermission('view-app-screen'),
                    ],
                    [
                    'title' => "Manage Screen Label",
                    "url" => "screen_label.php",
                    "icon" => "fa fa-dot-circle-o",
                    "active" => "Manage Label",
                    "visible" => $userObj->hasPermission('view-app-screen-label'),
                    ],
                    ],
                    ],*/
                    ];
                    if (scount($allservice_cat_data) >= 1 && !empty($allservice_cat_data)) {
                        foreach ($allservice_cat_data as $key => $value) {
                            $languages_childs[] = [
                                'title'   => $value['vServiceName'] . " Label",
                                'url'     => "languages.php?selectedlanguage=" . $value['iServiceId'],
                                "icon"    => '',
                                "active"  => "language_label_" . $value['iServiceId'],
                                "visible" => $userObj->hasPermission('view-general-label'),
                            ];
                        }
                    }
                    return $languages_childs;
                }
            ],
            [
                'title'   => "Language Label",
                "url"     => "languages.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "language_label",
                "visible" => $userObj->hasPermission('manage-language-label') && (!($deliverallModule == 'YES' || $onlyDeliverallModule == 'YES')),
            ],
            [
                'title'   => "Currency",
                "url"     => "currency.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Currency",
                "visible" => $userObj->hasPermission('manage-currency'),
            ],
            [
                'title'   => "Language",
                "url"     => "language.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Language",
                "visible" => $userObj->hasPermission('manage-language'),
            ],
            [
                'title'   => "SEO Settings",
                "url"     => "seo_setting.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "seo_setting",
                "visible" => $userObj->hasPermission('view-seo-setting'),
            ],
            [
                'title'   => "Maps API Settings",
                "url"     => "map_api_setting.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "map_api_setting",
                "visible" => $userObj->hasPermission('view-map-api-setting') && $MODULES_OBJ->mapAPIreplacementAvailable() == true && strtoupper(SITE_TYPE) == "LIVE",
            ],
            [
                'title'   => "Banner",
                "url"     => "banner.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Banner",
                "visible" => ($userObj->hasPermission('view-banner') && ($APP_TYPE == 'UberX' || $leftcubejekxthemeon == 1 || $leftcubexthemeon == 1 || $leftdeliverykingthemeon == 1 || $leftcubejekxv3themeon == 1 || $leftmedicalservicethemeon == 1)),
            ],
        ],
    ],
    [
        'title'    => "Utility",
        "icon"     => "ri-tools-line",
        "visible"  => $userObj->hasPermission('manage-utility'),
        'children' => [
            [
                'title'    => "Localization",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('manage-localization'),
                'children' => [
                    [
                        'title'   => "Country",
                        "url"     => "country.php",
                        "icon"    => "",
                        "active"  => "country",
                        "visible" => $userObj->hasPermission('view-country'),
                    ],
                    [
                        'title'   => "State",
                        "url"     => "state.php",
                        "icon"    => "",
                        "active"  => "state",
                        "visible" => $userObj->hasPermission('view-state'),
                    ],
                    [
                        'title'   => "City",
                        "url"     => "city.php",
                        "icon"    => "",
                        "active"  => "city",
                        "visible" => $userObj->hasPermission('view-city') && ($SHOW_CITY_FIELD == 'Yes'),
                    ],
                ],
            ],
            [
                'title'   => "Pages",
                "url"     => "page.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "page",
                "visible" => $userObj->hasPermission('view-pages'),
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_new.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                //"visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE == 'Ride-Delivery-UberX'),
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE == 'Ride-Delivery-UberX' && $leftcubexthemeon != 1 && $leftcubejekxthemeon != 1 && $leftdeliverykingthemeon != 1 && $leftcubejekxv3themeon != 1 && $leftmedicalservicethemeon != 1),
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_ridedeliveryx.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && $leftridedeliveryxthemeon == 1,
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_deliveryxv2.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && $leftdeliveryxthemeon == 1,
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_deliveryking.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && $leftdeliverykingthemeon == 1,
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_ridecx.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && $leftridecxthemeon == 1,
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_cubejekx.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent_cubejekx",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($leftcubejekxv3themeon == 1),
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_medical_service.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "home_content_medical_service",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($leftmedicalservicethemeon == 1),
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content_cubex.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE == 'Ride-Delivery-UberX' && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "Yes")),
            ],
            [
                'title'    => "Edit Home Page",
                "icon"     => "ri-checkbox-blank-circle-line",
                "active"   => "homecontent",
                "visible"  => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE == 'Ride-Delivery-UberX' && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No")),
                'children' => [
                    [
                        'title'   => "Main Page",
                        "url"     => "home_content_cubex.php",
                        "icon"    => "",
                        "active"  => "homecontent",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Taxi Page",
                        "url"     => "home_content_taxi.php",
                        "icon"    => "",
                        "active"  => "homecontentride",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Food Page",
                        "url"     => "home_content_food.php",
                        "icon"    => "",
                        "active"  => "homecontentfood",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Grocery Page",
                        "url"     => "home_content_grocery.php",
                        "icon"    => "",
                        "active"  => "homecontentgrocery",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Moto Page",
                        "url"     => "home_content_moto.php",
                        "icon"    => "",
                        "active"  => "homecontentmoto",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Delivery Page",
                        "url"     => "home_content_delivery.php",
                        "icon"    => "",
                        "active"  => "homecontentdelivery",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Fly Page",
                        "url"     => "home_content_fly.php",
                        "icon"    => "",
                        "active"  => "homecontentfly",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Earn Page",
                        "url"     => "home_content_earn.php",
                        "icon"    => "",
                        "active"  => "homecontentearn",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                    [
                        'title'   => "Business Page",
                        "url"     => "home_content_business.php",
                        "icon"    => "f",
                        "active"  => "homecontentbusiness",
                        "visible" => $userObj->hasPermission('view-pages') && ($leftcubexthemeon == 1 && ENABLE_DYNAMIC_CREATE_PAGE == "No"),
                    ],
                ],
            ],
            [
                'title'   => "Edit Home Page",
                "url"     => "home_content.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "home_content",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE != 'Ride-Delivery-UberX') && $leftridecxthemeon != 1 && $leftridedeliveryxthemeon != 1 & $leftdeliveryxthemeon != 1,
            ], /*[
                'title' => "Our " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                "url" => "home_driver.php",
                "icon" => "fa fa-users",
                "active" => "home_driver",
                "visible" => $userObj->hasPermission('view-our-provider') && ($APP_TYPE != 'Ride-Delivery-UberX' && $APP_TYPE != 'Ride' && $APP_TYPE != 'Ride-Delivery'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_CAR_MAKE_ADMIN'],
                "url"     => "make.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Make",
                "visible" => $userObj->hasPermission('view-vehicle-make') && ($APP_TYPE != 'UberX'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_CAR_MODEL_ADMIN'],
                "url"     => "model.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Model",
                "visible" => $userObj->hasPermission('view-vehicle-model') && ($APP_TYPE != 'UberX'),
            ],
            [
                'title'   => "Kiosk predefined destination",
                "url"     => "visit.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Visit",
                "visible" => $userObj->hasPermission('view-visit') && $kioskPanel > 0 && strtoupper($PACKAGE_TYPE) == 'SHARK',
                //added by SP on 1-7-2019 by asking with KS
            ],
            [
                'title'   => "Donation",
                "url"     => "donation.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "donation",
                "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
            ],
            [
                'title'   => "Hotel Banner",
                "url"     => "hotel_banner.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "hotel_banners",
                "visible" => $userObj->hasPermission('view-banner') && $kioskPanel > 0 && strtoupper($PACKAGE_TYPE) == 'SHARK',
                //added by SP on 1-7-2019 by asking with KS
            ],
            [
                'title'   => "News",
                "url"     => "news.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "news",
                "visible" => $userObj->hasPermission('view-news') && ($ENABLE_NEWS_SECTION == 'Yes'),
            ],
            [
                'title'   => "Newsletter Subscribers",
                "url"     => "newsletter.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "newsletters-subscribers",
                "visible" => $userObj->hasPermission('manage-newsletter') && ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == 'Yes'),
            ],
            [
                'title'   => "Faq",
                "url"     => "faq.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Faq",
                "visible" => $userObj->hasPermission('view-faq'),
            ],
            [
                'title'   => "Faq Categories",
                "url"     => "faq_categories.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "faq_categories",
                "visible" => $userObj->hasPermission('view-faq-categories'),
            ],
            [
                'title'   => "Help Topics",
                "url"     => "help_detail.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "help_detail",
                "visible" => $userObj->hasPermission('view-help-detail'),
            ],
            [
                'title'   => "Help Topic Categories",
                "url"     => "help_detail_categories.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "help_detail_categories",
                "visible" => $userObj->hasPermission('view-help-detail-category'),
            ],
            [
                'title'   => "Cancel Reason",
                "url"     => "cancellation_reason.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "cancel_reason",
                "visible" => $userObj->hasPermission('view-cancel-reasons'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_PACKAGE_TYPE_ADMIN'],
                "url"     => "package_type.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Package",
                "visible" => $userObj->hasPermission('view-package-type') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && $onlyDeliverallModule != "YES" && $leftdeliveryEnable == 'Yes',
                // $onlyDeliverallModule != "YES" Added By HJ On 05-07-2019 As Per Discuss With KS Sir
            ],
            /*[
                'title' => $langage_lbl_admin['LBL_MULTI_DELIVERY_FORM'],
                "url" => "delivery_fields.php",
                "icon" => "fa fa-globe",
                "active" => "delivery_package",
                "visible" => $userObj->hasPermission('view-delivery-field') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && $onlyDeliverallModule != "YES" && $leftdeliveryEnable == 'Yes', 
            ],*/
            [
                'title'   => "Send Push-Notification",
                "url"     => "send_notifications.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Push Notification",
                "visible" => $userObj->hasPermission('manage-send-push-notification'),
            ],
            [
                'title'   => "DB Backup",
                "url"     => "backup.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Back-up",
                "visible" => $userObj->hasPermission('view-db-backup'),
            ],
            [
                'title'   => 'System Diagnostic',
                'url'     => "system_diagnostic.php",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "site",
                "visible" => isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel' && !($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live"),
                //added by SP on 1-7-2019 for not showing this module to hotel
            ],
            [
                'title'   => 'Expired Documents',
                'url'     => "expired_documents.php",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "Expired Documents",
                //"visible" => $userObj->hasPermission('expired-documents') && $MODULES_OBJ->isDocumentExpiredFeatureEnable(),
                "visible" => $userObj->hasPermission('expired-documents'),
            ],
            [
                'title'   => 'App Launch Info',
                'url'     => "app_launch_info.php",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "app_launch_info",
                "visible" => $userObj->hasPermission('manage-app-launch-info'),
            ],
            [
                'title'   => 'App Banner Info',
                'url'     => "app_banner_info.php",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "app_banner_info",
                "visible" => $userObj->hasPermission('manage-app-banner-info') && $MODULES_OBJ->isEnableRideDeliveryV1(),
            ],
        ],
    ],
    [
        'title'    => "Manage Ride Profiles",
        "icon"     => "ri-briefcase-line",
        "visible"  => $userObj->hasPermission('manage-ride-profiles') && $MODULES_OBJ->isOrganizationModuleEnable() && $leftrideEnable == "Yes",
        'children' => [
            [
                'title'   => "Organization",
                "url"     => "organization.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Organization",
                "visible" => $userObj->hasPermission('view-organization'),
            ],
            [
                'title'   => "Ride Profile Type",
                "url"     => "user_profile_master.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideProfileType",
                "visible" => $userObj->hasPermission('view-user-profile'),
            ],
            [
                'title'   => "Business Trip Reason",
                "url"     => "trip_reason.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "BusinessTripReason",
                "visible" => $userObj->hasPermission('view-trip-reason'),
            ],
            [
                'title'   => "Organization Payment Report",
                "url"     => "org_payment_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "OrganizationPaymentReport",
                "visible" => $userObj->hasPermission('manage-organization-payment-report'),
            ],
            [
                'title'   => "Organization Outstanding Report",
                "url"     => "org_outstanding_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "org_outstanding_report",
                "visible" => $userObj->hasPermission('view-org-outstanding-report'),
            ]
        ],
    ],
    // =============================================
    // NEW FEATURES MENU (added by features module)
    // =============================================
    [
        'title'    => "Franchise Ecosystem",
        "icon"     => "ri-building-line",
        "visible"  => true,
        'children' => [
            [
                'title'   => "Franchise List",
                "url"     => "franchise_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "franchise",
                "visible" => true,
            ],
            [
                'title'   => "Add Franchise",
                "url"     => "franchise_add.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "franchise_add",
                "visible" => true,
            ],
        ],
    ],
    [
        'title'    => "Ride Features",
        "icon"     => "ri-car-line",
        "visible"  => true,
        'children' => [
            [
                'title'   => "No-Show Review",
                "url"     => "no_show_review.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "noShow",
                "visible" => true,
            ],
            [
                'title'   => "Lost & Found",
                "url"     => "lost_found_admin.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "lostFound",
                "visible" => true,
            ],
        ],
    ],
    [
        'title'    => "Smart Notifications",
        "icon"     => "ri-notification-3-line",
        "visible"  => true,
        'children' => [
            [
                'title'   => "Push Notifications",
                "url"     => "smart_notifications_admin.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "notifications",
                "visible" => true,
            ],
        ],
    ],
    // =============================================
    [
        'title' => "Logout",
        "url"   => "logout.php",
        "icon"  => "ri-logout-box-r-line",
    ],
];
return $menu;
