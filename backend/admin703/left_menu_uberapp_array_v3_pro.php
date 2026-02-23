<?php

//added by SP as discussed with bmam on 28-6-2019
$adminUsersTxt = $langage_lbl_admin['LBL_ADMINISTRATOR_TXT'];
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
    $adminUsersTxt = $langage_lbl_admin['LBL_ADMINISTRATOR_TXT'];
    if (strtoupper(ENABLEHOTELPANEL) == "YES") { //added by SP to chk hotel panel enable then only shown word at admin side.
        //$adminUsersTxt .= '/Hotel';
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

$data_Service_names = $obj->fetchAllRecordsFromMongoDBWithDBName(TSITE_DB, "auth_master_accounts_places", []);
$data_Service_names = json_decode(json_encode($data_Service_names), true);
$ServiceKey = array_search('Google', array_column($data_Service_names, 'vServiceName'));
$data_Service_Google = $data_Service_names[$ServiceKey];

/********************* search ********************/
function multiSearch(array $array, array $pairs, $child = 0)
{
    $found = array();
    foreach ($array as $aKey => $aVal) {
        $coincidences = 0;
        $in = 0;
        foreach ($pairs as $pKey => $pVal) {
            if (array_key_exists($pKey, $aVal) && strpos(strtoupper($aVal[$pKey]), strtoupper($pVal)) !== false) {
                $in = 1;
                $coincidences++;
            }
        }
        if (isset($array[$aKey]['children']) && !empty($array[$aKey]['children'])) {
            if (is_array($array[$aKey]['children'])) {
                $childs = 1;
                $result = multiSearch($array[$aKey]['children'], $pairs, $childs);
                if (isset($result) && !empty($result)) {
                    unset($array[$aKey]['children']);
                    $found[$aKey] = $aVal;
                    $found[$aKey]['children'] = $result;
                }
            }
        }
        if ($coincidences == scount($pairs) && ($in == 1 || $child == 1)) {
            $found[$aKey] = $aVal;
        }
    }
    return $found;
}
/********************* search ********************/

//$APP_TYPE = 'Delivery';

$MCategory = getMasterServiceCategoryId();
$VehicleCategory = getVehicleCategoryId();

$menu = [
    [
        'title'   => 'Dashboard',
        'url'     => "dashboard.php",
        "icon"    => 'ri-dashboard-line',
        "active"  => "dashboard",
        "visible" => true,
    ],
    [
        'title'   => 'Server Monitoring',
        'url'     => "server_admin_dashboard.php",
        "icon"    => 'ri-bar-chart-box-line',
        "active"  => "server_dashboard",
        "visible" => $userObj->hasPermission('manage-server-admin-dashboard') && ($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live"),
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
                "visible"  => ($userObj->hasPermission('view-admin')),
            ],
            [
                'title'   => 'Admin Groups',
                'url'     => "admin_groups.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "AdminGroups",
                //"visible" => $userObj->hasRole(1) && $PACKAGE_TYPE == 'SHARK',
                "visible" => $userObj->hasPermission('view-admin-group') && $PACKAGE_TYPE == 'SHARK',
            ]
        ],
    ],
    [
        'title'   => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        //'url'     => "rider.php",
        'url'     => $LOCATION_FILE_ARRAY['RIDER.PHP'],
        "icon"    => "ri-team-line",
        "active"  => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ],
    [
        'title'    => $langage_lbl_admin['LBL_DRIVERS_SERVICE_PROVIDERS'],
        'url'      => "driver.php",
        "icon"     => "ri-user-2-line",
        "active"   => "Driver",
        "visible"  => $userObj->hasPermission(['view-providers', 'view-providers-bidding-requests', 'view-providers-on-demand-service-requests', 'view-providers-videoconsult-service-requests', 'view-provider-vehicles']),
        'children' => [
            [
                'title'     => $langage_lbl_admin['LBL_MANAGE_SERVICE_PROVIDER_ADMIN_TXT'],
                'url'       => $LOCATION_FILE_ARRAY['DRIVER.PHP'],
                "icon"      => "ri-checkbox-blank-circle-line",
                "active"    => "Driver",
                "visible"   => $userObj->hasPermission('view-providers'),
            ],
            [
                'title'   => 'Manage '.$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].' Vehicles',
                "url"     => "vehicles.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Vehicle_",
                "visible" => $userObj->hasPermission('view-provider-vehicles') && !in_array($APP_TYPE, ['UberX']),
            ],
            [
                'title'    => "Service Requests",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-providers-bidding-requests', 'view-providers-on-demand-service-requests', 'view-providers-videoconsult-service-requests']),
                'children' => [
                    [
                        'title'   => 'Bidding Requests',
                        "icon"    => "",
                        "visible" => $userObj->hasPermission('view-providers-bidding-requests'),
                        "active"  => "biddingDriverRequest",
                        "url"     => "bidding_driver_request.php",
                    ],
                    [
                        'title'   => 'On Demand Service Requests',
                        "url"     => $LOCATION_FILE_ARRAY['DRIVER_SERVICE_REQUEST'],
                        "icon"    => "",
                        "active"  => "DriverRequest",
                        "visible" => $userObj->hasPermission('view-providers-on-demand-service-requests'),
                    ],
                    [
                        'title'   => 'VideoConsult Service Requests',
                        "url"     => $LOCATION_FILE_ARRAY['DRIVER_SERVICE_REQUEST'] . "?eType=VideoConsult",
                        "icon"    => "",
                        "active"  => "DriverRequest_VideoConsult",
                        "visible" => $userObj->hasPermission('view-providers-videoconsult-service-requests'),
                    ],
                ],
            ],
            [
                'title'    => "Manage Reward",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => ($userObj->hasPermission(['view-driver-reward-setting', 'view-driver-reward-report'])) && strtoupper($DRIVER_REWARD_FEATURE) == "YES",
                'children' => [
                    [
                        'title'   => 'Report',
                        'url'     => "reports.php",
                        "icon"    => "",
                        "active"  => "Reports",
                        "visible" => $userObj->hasPermission(['view-driver-reward-report' ]),
                    ],
                    [
                        'title'   => 'Setting',
                        'url'     => "reward.php",
                        "icon"    => "",
                        "active"  => "Reward",
                        "visible" => $userObj->hasPermission(['view-driver-reward-setting', 'view-driver-reward-campaign']),
                    ],
                ],
            ],
            [
                'title'     => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION'],
                "icon"      => "ri-price-tag-2-line",
                "icon"      => "ri-checkbox-blank-circle-line",
                "visible"   => $userObj->hasPermission(['view-driver-subscription', 'manage-driver-subscription-report']) && strtoupper($DRIVER_SUBSCRIPTION) == "YES",
                'children'  => [
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'],
                        'url'     => $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION'],
                        "active"  => "DriverSubscriptionPlan",
                        "visible" => $userObj->hasPermission('view-driver-subscription'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'],
                        'url'     => $LOCATION_FILE_ARRAY['DRIVER_SUBSCRIPTION_REPORT'],
                        "active"  => "DriverSubscriptionReport",
                        "visible" => $userObj->hasPermission('manage-driver-subscription-report'),
                    ],
                ],
            ],
        ],
    ],
    [
        'title'   => $langage_lbl_admin['LBL_COMPANY_ADMIN_TXT'],
        'url'     => "company.php",
        "icon"    => "ri-building-4-line",
        "active"  => "Company",
        "visible" => $userObj->hasPermission('view-company'),
    ],
    [
        'title'    => 'Hotels',
        "icon"     => "ri-hotel-line",
        "visible"  => $userObj->hasPermission(['view-hotel', 'view-hotel-banner', 'view-visit']),
        'children' => [
            [
                'title'   => 'Hotels',
                'url'     => "admin.php?admin=hotels",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Hotels",
                "visible" => $userObj->hasPermission('view-hotel'),
            ],
            [
                'title'   => "Hotel Banner",
                "url"     => "hotel_banner.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "hotel_banners",
                "visible" => $userObj->hasPermission('view-hotel-banner'),
            ],
            [
                'title'   => "Kiosk predefined destination",
                "url"     => "visit.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Visit",
                "visible" => $userObj->hasPermission('view-visit'),
            ],
        ],
    ],
    [
        'title'   => $restaurantAdmin,
        'url'     => $LOCATION_FILE_ARRAY['STORE.PHP'],
        //"url"     => "store.php",
        "icon"    => "ri-store-2-line",
        "active"  => "DeliverAllStore",
        "visible" => $userObj->hasPermission('view-store'),
    ],
    /*[
        'title'   => 'Master Service Categories',
        'url'     => "master_service_category.php",
        "active"  => "MasterServiceCategory",
        "icon"    => 'ri-apps-line',
        "visible" => $userObj->hasPermission(['view-vehicle-category']),
    ],*/
    /*--------------------- App type only Ride start ------------------*/
    [
        'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
        "url"    => "vehicle_category.php?eType=Ride",
        "icon"   => "ri-function-line",
        "active" => "VehicleCategory_Ride",
        "visible" => $userObj->hasPermission('view-service-category-taxi-service') && $APP_TYPE == 'Ride',
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
        "url"     => "vehicle_type.php?eType=Ride",
        "icon"    => "ri-taxi-line",
        "active"  => "VehicleType_Ride",
        "visible" => $userObj->hasPermission('view-vehicle-type-taxi-service')  && $APP_TYPE == 'Ride',
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_RENTAL_TXT'],
        "url"     => "rental_vehicle_list.php",
        "icon"    => "ri-red-packet-line",
        "active"  => "Rental Package",
        "visible" => ($userObj->hasPermission('view-rental-packages') && strtoupper(ENABLE_RENTAL_OPTION) == 'YES')  && $APP_TYPE == 'Ride',
    ],
    [
        'title'    => "Manage Ride Profiles",
        "icon"     => "ri-briefcase-line",
        "visible"  => $userObj->hasPermission(['view-organization', 'view-profile-taxi-service', 'view-trip-reason-taxi-service'])  && $APP_TYPE == 'Ride',
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
                "visible" => $userObj->hasPermission('view-profile-taxi-service'),
            ],
            [
                'title'   => "Business Trip Reason",
                "url"     => "trip_reason.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "BusinessTripReason",
                "visible" => $userObj->hasPermission('view-trip-reason-taxi-service'),
            ],
        ],
    ],
    /*--------------------- App type only Ride end------------------*/

    [
        'title'    => $langage_lbl_admin['LBL_RIDE_SERVICE_TITLE'],
        "icon"     => "ri-taxi-line",
        "visible"  => $userObj->hasPermission(['view-service-content-taxi-service', 'view-service-category-taxi-service', 'view-provider-vehicles-taxi-service', 'view-vehicle-type-taxi-service', 'view-rental-packages-taxi-service', 'view-organization']) && $APP_TYPE != 'Ride' && strtoupper(ONLY_MEDICAL_SERVICE) == "NO",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['Ride'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_Ride",
                "visible" => $userObj->hasPermission('view-service-content-taxi-service') && IS_CUBEX_APP == "No",
            ],*/
            [
                'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"    => "vehicle_category.php?eType=Ride",
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => "VehicleCategory_Ride",
                "visible" => $userObj->hasPermission('view-service-category-taxi-service'),
            ],
            /*[
                'title'   => 'Driver Vehicles',
                "url"     => "vehicles.php?eType=Ride",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Vehicle_Ride",
                "visible" => $userObj->hasPermission('view-provider-vehicles-taxi-service'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "vehicle_type.php?eType=Ride",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleType_Ride",
                "visible" => $userObj->hasPermission('view-vehicle-type-taxi-service'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_RENTAL_TXT'],
                "url"     => "rental_vehicle_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Rental Package",
                "visible" => ($userObj->hasPermission('view-rental-packages') && strtoupper(ENABLE_RENTAL_OPTION) == 'YES'),
            ],

            [
                'title'    => "Manage Ride Profiles",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-organization', 'view-profile-taxi-service', 'view-trip-reason-taxi-service']),
                'children' => [
                    [
                        'title'   => "Organization",
                        "url"     => "organization.php",
                        "icon"    => "",
                        "active"  => "Organization",
                        "visible" => $userObj->hasPermission('view-organization'),
                    ],
                    [
                        'title'   => "Ride Profile Type",
                        "url"     => "user_profile_master.php",
                        "icon"    => "",
                        "active"  => "RideProfileType",
                        "visible" => $userObj->hasPermission('view-profile-taxi-service'),
                    ],
                    [
                        'title'   => "Business Trip Reason",
                        "url"     => "trip_reason.php",
                        "icon"    => "",
                        "active"  => "BusinessTripReason",
                        "visible" => $userObj->hasPermission('view-trip-reason-taxi-service'),
                    ],
                ],
            ],

            [
                'title'    => "Taxi InterCity",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-vehicle-type-intercity-service','view-rental-intercity-packages']) && $MODULES_OBJ->isInterCityFeatureAvailable(),
                'children' => [
                    [
                        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                        "url"     => "vehicle_type.php?eType=InterCity",
                        "icon"    => "ri-checkbox-circle-line",
                        "active"  => "VehicleType_InterCity",
                        "visible" => $userObj->hasPermission('view-vehicle-type-intercity-service'),
                    ],
                    [
                        'title'   => "InterCity Packages",
                        "url"     => "intercity_vehicle_list.php",
                        "icon"    => "ri-checkbox-circle-line",
                        "active"  => "InterCity Rental Package",
                        "visible" => ($userObj->hasPermission('view-rental-intercity-packages') && strtoupper(ENABLE_RENTAL_OPTION) == 'YES'),
                    ],
                ]
            ],

            [
                'title'    => "Taxi Bid Service",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-service-content-taxi-bid-service', 'view-service-category-taxi-bid-service', 'view-provider-vehicles-taxi-bid-service', 'view-vehicle-type-taxi-bid-service']) && $MODULES_OBJ->isEnableTaxiBidFeature(),
                'children' => [
                    /*[
                        'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                        "url"     => "master_service_category_action.php?id=" . $MCategory['TaxiBid'],
                        "icon"    => "ri-checkbox-blank-circle-line",
                        "active"  => "mVehicleCategory_TaxiBid",
                        "visible" => $userObj->hasPermission('view-service-content-taxi-bid-service'),
                    ],
                    [
                        'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                        "url"    => "vehicle_category.php?eType=TaxiBid",
                        "icon"   => "ri-checkbox-blank-circle-line",
                        "active" => "VehicleCategory_TaxiBid",
                        "visible" => $userObj->hasPermission('view-service-category-taxi-bid-service') ,
                    ],*/
                    [
                        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                        "url"     => "vehicle_type.php?eType=TaxiBid",
                        "icon"    => "ri-checkbox-blank-circle-line",
                        "active"  => "VehicleType_TaxiBid",
                        "visible" => $userObj->hasPermission('view-vehicle-type-taxi-bid-service'),
                    ],
                ],
            ],


        ],
    ],
/*    [
        'title'    => "Taxi Bid Service",
        "icon"     => "ri-taxi-line",
        "visible"  => $userObj->hasPermission(['view-service-content-taxi-bid-service', 'view-service-category-taxi-bid-service', 'view-provider-vehicles-taxi-bid-service', 'view-vehicle-type-taxi-bid-service']) && $MODULES_OBJ->isEnableTaxiBidFeature(),
        'children' => [

            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "vehicle_type.php?eType=TaxiBid",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleType_TaxiBid",
                "visible" => $userObj->hasPermission('view-vehicle-type-taxi-bid-service'),
            ],
        ],
    ],*/
    [
        'title'    => $langage_lbl_admin['LBL_PARCEL_DELIVERY_ADMIN_TXT'],
        "icon"     => "ri-truck-line",
        "visible" => $userObj->hasPermission(['view-service-content-parcel-delivery', 'view-service-category-parcel-delivery', 'view-provider-vehicles-parcel-delivery', 'view-package-type-parcel-delivery', 'view-banner-parcel-delivery']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => $VehicleCategory['MoreDelivery']['url'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => $VehicleCategory['MoreDelivery']['active'],
                "visible" => $userObj->hasPermission('view-service-content-parcel-delivery') && strtoupper(IS_DELIVERYKING_APP) == "YES",
            ],
            [
                'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"    => $VehicleCategory['MoreDelivery']['sub_category_url'],
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => $VehicleCategory['MoreDelivery']['sub_category_action'],
                "visible" => $userObj->hasPermission('view-service-category-parcel-delivery'),
            ],
            /*[
                'title'   => 'Driver Vehicles',
                "url"     => "vehicles.php?eType=Deliver",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Vehicle_Deliver",
                "visible" => $userObj->hasPermission('view-provider-vehicles-parcel-delivery'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "vehicle_type.php?eType=Deliver",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleType_Deliver",
                "visible" => $userObj->hasPermission('view-vehicle-type-parcel-delivery'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_PACKAGE_TYPE_ADMIN'],
                "url"     => "package_type.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Package",
                "visible" => $userObj->hasPermission('view-package-type-parcel-delivery'),
            ],
            [
                'title'   => "Item Size Details",
                "url"     => "parcel_delivery_items_size.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ParcelDeliveryItemSize",
                "visible" => $userObj->hasPermission('view-item-size-parcel-delivery') && strtoupper($APP_TYPE) == "DELIVERY",
            ],
            [
                'title'   => $langage_lbl_admin['LBL_BANNER_ADMIN_TXT'],
                "url"     => $VehicleCategory['MoreDelivery']['banner_url'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "MoreDelivery_banner",
                "visible" => $userObj->hasPermission('view-banner-parcel-delivery') && strtoupper($APP_TYPE) != "RIDE-DELIVERY"
            ],
        ],
    ],
    [
        'title'    => $langage_lbl_admin['LBL_DELIVERY_GENIE_ADMIN_TXT'],
        "icon"     => "ri-e-bike-2-line",
        "visible"  => $userObj->hasPermission(['view-service-content-genie-delivery', 'view-banner-genie-delivery']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => $VehicleCategory['Genie']['url'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => $VehicleCategory['Genie']['active'],
                "visible" => $userObj->hasPermission('view-service-content-genie-delivery'),
            ],
            [
                'title'  => $langage_lbl_admin['LBL_BANNER_ADMIN_TXT'],
                "url"    => $VehicleCategory['Genie']['banner_url'],
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => "Genie_banner",
                "visible" => $userObj->hasPermission('view-banner-genie-delivery'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "store_vehicle_type.php?eType=genie",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "GenieVehicleType",
                "visible" => $userObj->hasPermission('view-vehicle-type-genie-delivery'),
            ],
            [
                'title'   => "Delivery Charges",
                "url"     => "delivery_charges.php?eType=genie",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "GenieDeliveryCharges",
                "visible" => $userObj->hasPermission('view-delivery-charges-genie-delivery'),
            ],
            /*[
                'title'   => "Distance wise Delivery Charges",
                "url"     => "custom_delivery_charge_order.php?eType=genie",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "GenieCustomDeliveryCharges",
                "visible" => $userObj->hasPermission('view-custom-delivery-charges-genie-delivery') && $MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder(),
            ],*/
        ],
    ],
    [
        'title'    => $langage_lbl_admin['LBL_DELIVERY_RUNNER_ADMIN_TXT'],
        "icon"     => "ri-takeaway-line",
        "visible"  => $userObj->hasPermission(['view-service-content-runner-delivery', 'view-banner-runner-delivery']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => $VehicleCategory['Runner']['url'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => $VehicleCategory['Runner']['active'],
                "visible" => $userObj->hasPermission('view-service-content-runner-delivery'),
            ],
            [
                'title'  => $langage_lbl_admin['LBL_BANNER_ADMIN_TXT'],
                "url"    => $VehicleCategory['Runner']['banner_url'],
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => "Runner_banner",
                "visible" => $userObj->hasPermission('view-banner-runner-delivery'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "store_vehicle_type.php?eType=runner",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RunnerVehicleType",
                "visible" => $userObj->hasPermission('view-vehicle-type-runner-delivery'),
            ],
            [
                'title'   => "Delivery Charges",
                "url"     => "delivery_charges.php?eType=runner",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RunnerDeliveryCharges",
                "visible" => $userObj->hasPermission('view-delivery-charges-runner-delivery'),
            ],
            /*[
                'title'   => "Distance wise Delivery Charges",
                "url"     => "custom_delivery_charge_order.php?eType=runner",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RunnerCustomDeliveryCharges",
                "visible" => $userObj->hasPermission('view-custom-delivery-charges-runner-delivery') && $MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder(),
            ],*/

        ],
    ],
    /*--------------------- app type deliver all ------------------*/
    [
        'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
        //"url"     => "vehicle_category.php?eType=DeliverAll",
        "url"     => $LOCATION_FILE_ARRAY['MASTER_CATEGORY_DELIVERALL'],
        "icon"    => "fa fa-certificate",
        "active"  => "VehicleCategory_DeliverAll",
        "visible" => $userObj->hasPermission('view-service-category-deliverall') && $APP_TYPE == "Delivery",
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
        "url"     =>  "store_vehicle_type.php",
        "icon"    => "ri-steering-line",
        "active"  => "StoreVehicleType",
        "visible" => $userObj->hasPermission('view-vehicle-type-deliverall') && $APP_TYPE == "Delivery",
    ],
    [
        'title'    => $restaurantAdmin . " Items",
        "icon"     => "ri-checkbox-blank-circle-line",
        "visible"  => $userObj->hasPermission(['view-item-categories', 'view-item', 'view-item-type', 'manage-import-bulk-items']) && $APP_TYPE == "Delivery",
        'children' => [
            [
                'title'   => "Import Bulk Items",
                "url"     => "import_item_data.php",
                "icon"    => "",
                "active"  => "ImportItem",
                "visible" => $userObj->hasPermission('manage-import-bulk-items') && strtoupper(ENABLE_BULK_ITEM_DATA) == "YES",
            ],
            [
                'title'   => "Item Categories",
                //"url"     => "food_menu.php",
                "url"     => $LOCATION_FILE_ARRAY['FOOD_MENU.PHP'],
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
        'title'    => $restaurantAdmin . " Orders",
        "icon"     => "ri-bill-line",
        "visible"  => $userObj->hasPermission(['view-processing-orders', 'view-all-orders', 'view-cancelled-orders']) && $APP_TYPE == "Delivery",
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
        'title'    => "Utility",
        "icon"     => "ri-tools-fill",
        "visible"  => $userObj->hasPermission(['view-order-status', 'view-delivery-charges', 'view-custom-delivery-charges', 'view-banner-store', 'view-store-categories', 'view-delivery-preference', 'view-rating-feedback-ques', 'manage-otp-for-stores']) && $APP_TYPE == "Delivery",
        'children' => [
            /*[
                'title'   => "DeliveryAll Service Category",
                "url"     => "service_category.php",
                "icon"    => "",
                "active"  => "service_category",
                "visible" => $userObj->hasPermission('view-service-category') && $onlyDeliverallModule == 'YES' && $leftdeliverallEnable == 'Yes',
            ],*/
            [
                'title'   => "Order Status",
                "url"     => "order_status.php",
                "icon"    => "",
                "active"  => "order_status",
                "visible" => $userObj->hasPermission('view-order-status'),
            ],
            [
                'title'   => "User Delivery Charges",
                "url"     => "delivery_charges.php",
                "icon"    => "",
                "active"  => "Delivery Charges",
                "visible" => $userObj->hasPermission('view-delivery-charges'),
            ],
            [
                'title'   => "Driver Delivery Charges",
                "url"     => "custom_delivery_charge_order.php",
                "icon"    => "",
                "active"  => "Custom Delivery Charges",
                "visible" => $userObj->hasPermission('view-custom-delivery-charges') && $MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder(),
            ],
            [
                'title'   => "Banners",
                "url"     => "store_banner.php",
                "icon"    => "",
                "active"  => "Store Banner",
                "visible" => $userObj->hasPermission('view-banner-store'),
            ],
            [
                'title'   => "Manage " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Categories",
                "url"     => "store_category.php",
                "icon"    => "",
                "active"  => "ManageStoreCategories",
                "visible" => $userObj->hasPermission('view-store-categories'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_DELIVERY_PREF'],
                'url'     => "delivery_preferences.php",
                "icon"    => "",
                "active"  => "DeliveryPreferences",
                "visible" => $userObj->hasPermission('view-delivery-preference') && strtoupper($CONTACTLESS_DELIVERY_MODULE) == "YES",
            ],
            [
                'title'   => "Manage OTP For Service Categories",
                'url'     => "manage_otp_for_stores.php",
                "icon"    => "",
                "active"  => "otpservicecategory",
                "visible" => $userObj->hasPermission('manage-otp-for-stores') && strtoupper($OTP_VERIFICATION) == "YES"  && scount($service_categories_ids_arr) > 1,
            ],
            [
                'title'   => "Rating Feedback Questions",
                'url'     => "rating_feedback_ques.php",
                "icon"    => "",
                "active"  => "RatingFeedbackQuestions",
                "visible" => $userObj->hasPermission('view-rating-feedback-ques') && strtoupper($FOOD_RATING_DETAIL_FEATURE) == "YES",
            ],
        ],
    ],

    /*--------------------- app type deliver all ------------------*/

    [
        'title'    => $restaurantAdmin . " Delivery Services",
        "icon"     => "ri-store-2-line",
        "visible"  => $userObj->hasPermission(['view-service-content-deliverall', 'view-service-category-deliverall', 'view-vehicle-type', 'view-item-categories', 'view-item', 'view-item-type', 'manage-import-bulk-items', 'view-processing-orders', 'view-all-orders', 'view-cancelled-orders', 'view-order-status', 'view-delivery-charges', 'view-custom-delivery-charges', 'view-banner-store', 'view-store-categories', 'view-delivery-preference', 'view-rating-feedback-ques', 'manage-otp-for-stores']) && $APP_TYPE != "Delivery",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['DeliverAll'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_DeliverAll",
                "visible" => $userObj->hasPermission('view-service-content-deliverall') && $onlyDeliverallModule == "NO" && IS_CUBEX_APP == "No",
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
               // "url"     => "vehicle_category.php?eType=DeliverAll",
                "url"     => $LOCATION_FILE_ARRAY['MASTER_CATEGORY_DELIVERALL'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleCategory_DeliverAll",
                "visible" => $userObj->hasPermission('view-service-category-deliverall') && scount($service_categories_ids_arr) > 1,
            ],
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => $LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "StoreVehicleType",
                "visible" => $userObj->hasPermission('view-vehicle-type-deliverall'),
            ],
            [
                'title'    => $restaurantAdmin . " Items",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-item-categories', 'view-item', 'view-item-type', 'manage-import-bulk-items']),
                'children' => [
                    [
                        'title'   => "Import Bulk Items",
                        "url"     => "import_item_data.php",
                        "icon"    => "",
                        "active"  => "ImportItem",
                        "visible" => $userObj->hasPermission('manage-import-bulk-items') && strtoupper(ENABLE_BULK_ITEM_DATA) == "YES",
                    ],
                    [
                        'title'   => "Item Categories",
                        //"url"     => "food_menu.php",
                        "url"     => $LOCATION_FILE_ARRAY['FOOD_MENU.PHP'],
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
                'title'    => $restaurantAdmin . " Orders",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-processing-orders', 'view-all-orders', 'view-cancelled-orders']),
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
                'title'    => "Utility",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-order-status', 'view-delivery-charges', 'view-custom-delivery-charges', 'view-banner-store', 'view-store-categories', 'view-delivery-preference', 'view-rating-feedback-ques', 'manage-otp-for-stores']),
                'children' => [
                    /*[
                        'title'   => "DeliveryAll Service Category",
                        "url"     => "service_category.php",
                        "icon"    => "",
                        "active"  => "service_category",
                        "visible" => $userObj->hasPermission('view-service-category') && $onlyDeliverallModule == 'YES' && $leftdeliverallEnable == 'Yes',
                    ],*/
                    [
                        'title'   => "Order Status",
                        "url"     => "order_status.php",
                        "icon"    => "",
                        "active"  => "order_status",
                        "visible" => $userObj->hasPermission('view-order-status'),
                    ],
                    [
                        'title'   => "User Delivery Charges",
                        "url"     => "delivery_charges.php",
                        "icon"    => "",
                        "active"  => "Delivery Charges",
                        "visible" => $userObj->hasPermission('view-delivery-charges'),
                    ],
                    [
                        'title'   => "Driver Delivery Charges",
                        "url"     => "custom_delivery_charge_order.php",
                        "icon"    => "",
                        "active"  => "Custom Delivery Charges",
                        "visible" => $userObj->hasPermission('view-custom-delivery-charges') && $MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder(),
                    ],
                    [
                        'title'   => "Banners",
                        "url"     => "store_banner.php",
                        "icon"    => "",
                        "active"  => "Store Banner",
                        "visible" => $userObj->hasPermission('view-banner-store'),
                    ],
                    [
                        'title'   => "Manage " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Categories",
                        "url"     => "store_category.php",
                        "icon"    => "",
                        "active"  => "ManageStoreCategories",
                        "visible" => $userObj->hasPermission('view-store-categories'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DELIVERY_PREF'],
                        'url'     => "delivery_preferences.php",
                        "icon"    => "",
                        "active"  => "DeliveryPreferences",
                        "visible" => $userObj->hasPermission('view-delivery-preference') && strtoupper($CONTACTLESS_DELIVERY_MODULE) == "YES",
                    ],
                    [
                        'title'   => "Manage OTP For Service Categories",
                        'url'     => "manage_otp_for_stores.php",
                        "icon"    => "",
                        "active"  => "otpservicecategory",
                        "visible" => $userObj->hasPermission('manage-otp-for-stores') && strtoupper($OTP_VERIFICATION) == "YES" && scount($service_categories_ids_arr) > 1,
                    ],
                    [
                        'title'   => "Rating Feedback Questions",
                        'url'     => "rating_feedback_ques.php",
                        "icon"    => "",
                        "active"  => "RatingFeedbackQuestions",
                        "visible" => $userObj->hasPermission('view-rating-feedback-ques') && strtoupper($FOOD_RATING_DETAIL_FEATURE) == "YES",
                    ],
                ],
            ],
        ],
    ],


    [
        'title'    => 'Manage Parking',
        "icon"     => "ri-parking-box-line",
        "visible"  => $userObj->hasPermission(['view-service-content-parking','view-list-booking-parking','view-list-rented-parking','view-list-pending-parking','view-payment-report-parking']),
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['Parking'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_Parking",
                "visible" => $userObj->hasPermission('view-service-content-parking')
            ],*/
            [
                'title'   => 'Booking List',
                "url"     => "parking_booking_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "",
                "visible" => $userObj->hasPermission('view-list-booking-parking')
            ],
            [
                'title'   => 'Rented Parking List',
                "url"     => "parking_rented_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "",
                "visible" => $userObj->hasPermission('view-list-rented-parking')
            ],
            [
                'title'   => 'Pending For Approval',
                "url"     => "parking_approval.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "",
                "visible" => $userObj->hasPermission('view-list-pending-parking')
            ],
            [
                'title'   => 'Payment Report',
                "url"     => "parking_payment_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "",
                "visible" => $userObj->hasPermission('view-payment-report-parking')
            ],
        ],
    ],


    [
        'title'    => $langage_lbl_admin['LBL_VIDEO_CONSULTATION_TXT'],
        "icon"     => "ri-live-line",
        "visible"  => $userObj->hasPermission(['view-service-content-video-consultation','view-service-category-video-consultation']) && strtoupper($VIDEO_CONSULTING_FEATURE) == "YES" && strtoupper(ONLY_MEDICAL_SERVICE) == "NO",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['VideoConsult'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_VideoConsult",
                "visible" => $userObj->hasPermission('view-service-content-video-consultation'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"     => $LOCATION_FILE_ARRAY['MASTER_CATEGORY_VIDEO-CONSULT'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleCategory_VideoConsult",
                "visible" => $userObj->hasPermission('view-service-category-video-consultation'),
            ],
        ],
    ],
    [
        'title'    => $langage_lbl_admin['LBL_MANANGE_BIDDING_SERVICES'],
        "icon"     => "ri-auction-line",
        "visible"  => $userObj->hasPermission(['view-service-content-bidding', 'view-bidding-category', 'manage-bids-report']),
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['Bidding'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_Bidding",
                "visible" => $userObj->hasPermission('view-service-content-bidding'),
            ],*/
            [
                'title'   => 'Bidding Services',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-category'),
                "active"  => "bidding",
                "url"     => "bidding_master_category.php",
            ],
            [
                'title'   => 'Bidding Report',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('manage-bids-report'),
                "active"  => "Bids",
                "url"     => "bidding_report.php",
            ],
            /*[
                'title'   => 'Bidding Review',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-review'),
                "active"  => "BidReviews",
                "url"     => "bidding_review.php",
            ],*/
        ],
    ],
    [
        'title'    => 'On-Demand Services',
        "icon"     => "ri-function-line",
        "visible"  => $userObj->hasPermission(['view-service-content-uberx', 'view-service-category-uberx', 'view-service-type']) && strtoupper(ONLY_MEDICAL_SERVICE) == "NO",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['UberX'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_UberX",
                "visible" => $userObj->hasPermission('view-service-content-uberx'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                //"url"     => "vehicle_category.php?eType=UberX",
                "url"     => $LOCATION_FILE_ARRAY['MASTER_CATEGORY_UBERX'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleCategory_UberX",
                "visible" => $userObj->hasPermission('view-service-category-uberx'),
            ],
            [
                'title'   => "Service Type",
                "url"     => "service_type.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ServiceType",
                "visible" => $userObj->hasPermission('view-service-type'),
            ],
        ],
    ],
    [
        'title'    => 'Buy,Sell & Rent Properties',
        "icon"     => "ri-community-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rentestate', 'view-service-category-rentestate', 'view-pending-rentestate', 'view-approved-rentestate', 'view-all-rentestate', 'manage-rentestate-fields', 'view-payment-plan-rentestate', 'report-rentestate', 'view-banner-rentestate']),
        "active"   => "RentEstate",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RentEstate'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RentEstate",
                "visible" => $userObj->hasPermission('view-service-content-rentestate'),
            ],*/
            [
                'title'   => "Categories",
                "url"     => 'bsr_master_category.php?eType=RealEstate',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentEstate",
                "visible" => $userObj->hasPermission('view-service-category-rentestate'),
            ],
            [
                'title'   => 'Pending for Approval',
                "url"     => "pending_item.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "PendingRentEstate",
                "visible" => $userObj->hasPermission('view-pending-rentestate'),
            ],
            [
                'title'   => "Approved Properties",
                "url"     => "item_approved.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ApprovedRentEstate",
                "visible" => $userObj->hasPermission('view-approved-rentestate'),
            ],
            [
                'title'   => "All Properties",
                "url"     => "all_bsr_items.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "AllRentEstate",
                "visible" => $userObj->hasPermission('view-all-rentestate'),
            ],
            [
                'title'   => "Manage Data Fields",
                "url"     => "data_fields.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentEstateFields",
                "visible" => $userObj->hasPermission('manage-rentestate-fields') && ENABLE_DATAFEILDS_ADMIN == 'Yes',
            ],
            [
                'title'   => "Payment Plans",
                "url"     => "item_payment_plans.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentEstatePaymentPlan",
                "visible" => $userObj->hasPermission('view-payment-plan-rentestate'),
            ],
            [
                'title'   => "Payment Report",
                "url"     => "bsr_item_payment_report.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentEstateReport",
                "visible" => $userObj->hasPermission('report-rentestate'),
            ],
            [
                'title'   => "Banners",
                "url"     => "bsr_banner.php?eType=RealEstate",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentEstateBanner",
                "visible" => $userObj->hasPermission('view-banner-rentestate'),
            ],
        ],
    ],
    [
        'title'    => 'Buy, Sell & Rent Cars',
        "icon"     => "ri-car-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rentcars', 'view-service-category-rentcars', 'view-pending-rentcars', 'view-approved-rentcars', 'view-all-rentcars', 'manage-rentcars-fields', 'view-payment-plan-rentcars', 'report-rentcars', 'view-banner-rentcars']),
        "active"   => "RentCars",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RentCars'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RentCars",
                "visible" => $userObj->hasPermission('view-service-content-rentcars'),
            ],*/
            [
                'title'   => "Categories",
                "url"     => 'bsr_master_category.php?eType=Cars',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentCars",
                "visible" => $userObj->hasPermission('view-service-category-rentcars'),
            ],
            [
                'title'   => 'Pending for Approval',
                "url"     => "pending_item.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "PendingRentCars",
                "visible" => $userObj->hasPermission('view-pending-rentcars'),
            ],
            [
                'title'   => "Approved Cars",
                "url"     => "item_approved.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ApprovedRentCars",
                "visible" => $userObj->hasPermission('view-approved-rentcars'),
            ],
            [
                'title'   => "All Cars",
                "url"     => "all_bsr_items.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "AllRentCars",
                "visible" => $userObj->hasPermission('view-all-rentcars'),
            ],
            [
                'title'   => "Manage Data Fields",
                "url"     => "data_fields.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentCarsFields",
                "visible" => $userObj->hasPermission('manage-rentcars-fields') && ENABLE_DATAFEILDS_ADMIN == 'Yes',
            ],
            [
                'title'   => "Payment Plans",
                "url"     => "item_payment_plans.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentCarsPaymentPlan",
                "visible" => $userObj->hasPermission('view-payment-plan-rentcars'),
            ],
            [
                'title'   => "Payment Report",
                "url"     => "bsr_item_payment_report.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentCarsReport",
                "visible" => $userObj->hasPermission('report-rentcars'),
            ],
            [
                'title'   => "Banners",
                "url"     => "bsr_banner.php?eType=Cars",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentCarsBanner",
                "visible" => $userObj->hasPermission('view-banner-rentcars'),
            ],
        ],
    ],
    [
        'title'    => 'Buy, Sell & Rent General Items',
        "icon"     => "ri-luggage-cart-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rentitem', 'view-service-category-rentitem', 'view-pending-rentitem', 'view-approved-rentitem', 'view-all-rentitem', 'manage-rentitem-fields', 'view-payment-plan-rentitem', 'report-rentitem', 'view-banner-rentitem']),
        "active"   => "RentItem",
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RentItem'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RentItem",
                "visible" => $userObj->hasPermission('view-service-content-rentitem'),
            ],*/
            [
                'title'   => "Categories",
                "url"     => 'bsr_master_category.php?eType=GeneralItem',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentItem",
                "visible" => $userObj->hasPermission('view-service-category-rentitem'),
            ],
            [
                'title'   => 'Pending for Approval',
                "url"     => "pending_item.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "PendingRentItem",
                "visible" => $userObj->hasPermission('view-pending-rentitem'),
            ],
            [
                'title'   => "Approved Items",
                "url"     => "item_approved.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ApprovedRentItem",
                "visible" => $userObj->hasPermission('view-approved-rentitem'),
            ],
            [
                'title'   => "All Items",
                "url"     => "all_bsr_items.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "AllRentItem",
                "visible" => $userObj->hasPermission('view-all-rentitem'),
            ],
            [
                'title'   => "Manage Data Fields",
                "url"     => "data_fields.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentItemFields",
                "visible" => $userObj->hasPermission('manage-rentitem-fields') && ENABLE_DATAFEILDS_ADMIN == 'Yes',
            ],
            [
                'title'   => "Payment Plans",
                "url"     => "item_payment_plans.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentItemPaymentPlan",
                "visible" => $userObj->hasPermission('view-payment-plan-rentitem'),
            ],
            [
                'title'   => "Payment Report",
                "url"     => "bsr_item_payment_report.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentItemReport",
                "visible" => $userObj->hasPermission('report-rentitem'),
            ],
            [
                'title'   => "Banners",
                "url"     => "bsr_banner.php?eType=GeneralItem",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RentItemBanner",
                "visible" => $userObj->hasPermission('view-banner-rentitem'),
            ],
        ],
    ],
    [
        'title'    => $langage_lbl_admin['LBL_MEDICAL_SERVICES_ADMIN_TXT'],
        "icon"     => "ri-hospital-line",
        "visible"  => $userObj->hasPermission(['view-service-content-medical', 'view-service-category-medical', 'view-provider-vehicles-medical', 'view-vehicle-type-medical']) && MED_UFX_ENABLED == 'Yes',
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['MedicalServices'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_MedicalServices",
                "visible" => $userObj->hasPermission('view-service-content-medical'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"     => "vehicle_category.php?eType=MedicalServices",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleCategory_MedicalServices",
                "visible" => $userObj->hasPermission('view-service-category-medical'),
            ],
            /*[
                'title'   => 'Driver Vehicles',
                "url"     => "vehicles.php?eType=Ambulance",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Vehicle_Ambulance",
                "visible" => $userObj->hasPermission('view-provider-vehicles-medical'),
            ],*/
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "vehicle_type.php?eType=Ambulance",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleType_Ambulance",
                "visible" => $userObj->hasPermission('view-vehicle-type-medical'),
            ],
            [
                'title'   => "Service Type",
                "url"     => "service_type.php?eType=MedicalServices",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ServiceType_MedicalServices",
                "visible" => $userObj->hasPermission('view-service-type-medical'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_SETTINGS'],
                "url"     => "vehicle_category_action.php?id=3&eServiceType=MedicalServices&eServiceSettings=MedicalServices",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleCategory_setting",
                "visible" => $userObj->hasPermission('view-service-category-medical'),
            ],
        ],
    ],
    [
        'title'    => "Fly Service",
        //added by SP for fly stations on 13-08-2019
        "icon"     => "ri-flight-takeoff-line",
        "visible"  => $userObj->hasPermission(['view-fly-stations', 'view-fly-vehicle-type', 'view-fly-fare']),
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
    [
        'title'    => $langage_lbl_admin['LBL_RIDE_SHARE_TXT'],
        "icon"     => "ri-car-washing-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rideshare', 'view-published-rides-rideshare', 'view-booking-rideshare', 'view-payment-report-rideshare', 'view-driver-detail-fields-rideshare']),
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RideShare'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RideShare",
                "visible" => $userObj->hasPermission('view-service-content-rideshare'),
            ],*/
            [
                'title'   => "Published Rides",
                'url'     => "published_rides.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "PublishedRides",
                "visible" => $userObj->hasPermission('view-published-rides-rideshare')
            ],
            [
                'title'   => "Bookings",
                'url'     => "ride_share_bookings.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideShareBookings",
                "visible" => $userObj->hasPermission('view-booking-rideshare')
            ],
            /*[
                'title'   => "Reviews",
                'url'     => "ride_share_reviews.php",
                "icon"    => "ri-user-voice-line",
                "active"  => "ride-share-review",
                "visible" => $userObj->hasPermission('view-booking-rideshare')
            ],*/
            [
                'title'   => "Payment Report",
                'url'     => "ride_share_payment_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideSharePaymentReport",
                "visible" => $userObj->hasPermission('view-payment-report-rideshare')
            ],
            [
                'title'   => "Travel Preferences",
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission(['view-travel-preferences-category','view-travel-preferences']),
                'children' => [
                    [
                        'title'   => "Category",
                        "url"     => "travel_preferences_category.php",
                        "icon"    => "ri-checkbox-blank-circle-line",
                        "active"  => "travel_preferences_category",
                        "visible" => $userObj->hasPermission('view-travel-preferences-category'),
                    ],
                    [
                        'title'   => "Option",
                        'url'     => "travel_preferences.php",
                        "icon"    => "ri-checkbox-blank-circle-line",
                        "active"  => "Travel_Preferences",
                        "visible" => $userObj->hasPermission('view-travel-preferences')
                    ],
                ]
            ],
            [
                'title'   => "Driver Details Fields",
                'url'     => "driver_details_field.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "RideShareDriverFields",
                "visible" => $userObj->hasPermission('view-driver-detail-fields-rideshare')
            ],
        ],
    ],
    [
        'title'    => "Nearby Management",
        "icon"     => "ri-pin-distance-line",
        "visible"  => $userObj->hasPermission(['view-service-content-nearby', 'view-category-nearby', 'view-places-nearby', 'view-banners-nearby']),
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['NearBy'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_NearBy",
                "visible" => $userObj->hasPermission('view-service-content-nearby'),
            ],*/
            [
                'title'   => "NearBy Category",
                "url"     => "near_by_category.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "nearbyCategory",
                "visible" => $userObj->hasPermission('view-category-nearby'),
            ],
            [
                'title'   => "NearBy Places",
                "url"     => "near_by_places.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "nearbyPlaces",
                "visible" => $userObj->hasPermission('view-places-nearby'),
            ],
            [
                'title'   => "Banners",
                "url"     => "banner.php?eType=NearBy&vCode=EN",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "NearBy_banner",
                "visible" => $userObj->hasPermission('view-banners-nearby'),
            ],
        ],
    ],
    /*[
        'title'    => "Manage Tracking Service",
        "icon"     => "ri-user-location-line",
        "visible"  => $userObj->hasPermission(['view-service-content-trackservice', 'view-company-trackservice', 'view-users-trackservice', 'view-driver-trackservice', 'view-driver-vehicle-trackservice', 'view-trip-trackservice']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . (isset($MCategory['TrackService']) ? $MCategory['TrackService'] : ''),
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_TrackService",
                "visible" => $userObj->hasPermission('view-service-content-trackservice'),
            ],
            [
                'title'   => "Company",
                'url'     => "track_service_company.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceCompany",
                "visible" => $userObj->hasPermission('view-company-trackservice')
            ],
            [
                'title'   => "User",
                'url'     => "track_service_user.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceUser",
                "visible" => $userObj->hasPermission('view-users-trackservice')
            ],
            [
                'title'   => "Driver",
                'url'     => "track_service_driver.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceDriver",
                "visible" => $userObj->hasPermission('view-driver-trackservice')
            ],
            [
                'title'   => "Driver Vehicles",
                'url'     => "track_service_driver_vehicle.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceDriverVehicle",
                "visible" => $userObj->hasPermission('view-driver-vehicle-trackservice')
            ],
            [
                'title'   => "Trip Report",
                'url'     => "track_service_trips.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackServiceTrips",
                "visible" => $userObj->hasPermission('view-trip-trackservice')
            ]
        ],
    ],*/
    [
        'title'    => "Manage Tracking Service",
        "icon"     => "ri-user-location-line",
        "visible"  => $userObj->hasPermission(['view-service-content-trackanyservice', 'view-users-trackanyservice']),
        'children' => [
            /*[
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['TrackAnyService'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_TrackAnyService",
                "visible" => $userObj->hasPermission('view-service-content-trackanyservice'),
            ],*/
            [
                'title'   => "Users",
                'url'     => "track_any_service_user.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "TrackAnyServiceUser",
                "visible" => $userObj->hasPermission('view-users-trackanyservice')
            ]
        ],
    ],
    [
        'title'   => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
        'url'     => $tconfig['tsite_url'] . "user-order-information?order=admin",
        "icon"    => "ri-file-list-line",
        "active"  => "store_order_book",
        "target"  => "blank",
        "visible" => ($userObj->hasPermission('manage-restaurant-order') && $MANUAL_STORE_ORDER_ADMIN_PANEL == "Yes") && $onlyDeliverallModule == "YES",
    ],

    [
        'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
        //'url'     => "trip.php",
        'url'     => $LOCATION_FILE_ARRAY['TRIP'],
        "icon"    => "ri-checkbox-blank-circle-line",
        "active"  => "Trips",
        "visible" => $userObj->hasPermission('view-trip-jobs') && $APP_TYPE == 'UberX',
    ],
    [
        'title'   => $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'],
        //'url'     => "cab_booking.php",
        'url'     => $LOCATION_FILE_ARRAY['LATER_BOOKING'],
        "icon"    => "ri-checkbox-blank-circle-line",
        "active"  => "CabBooking",
        "visible" => $userObj->hasPermission('view-ride-job-later-bookings') && $APP_TYPE == 'UberX',
    ],

    [
        'title'    => strtoupper(DELIVERALL_ENABLED) == "YES" ? "Bookings / Orders" : "Bookings",
        "icon"     => "ri-file-list-line",
        "visible"  => $userObj->hasPermission(['create-manage-manual-booking', 'view-ride-job-later-bookings', 'view-trip-jobs', 'manage-restaurant-order']) && $onlyDeliverallModule == "NO" && $APP_TYPE != 'UberX',
        'children' => [
            [
                'title'   => "Manual Booking ",
                'url'     => "add_booking.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "booking",
                "visible" => $userObj->hasPermission('create-manage-manual-booking') && (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == 'hotel' ? !$MODULES_OBJ->isManualBookingAvailable() : $_SESSION['SessionUserType'] != 'hotel') && $MODULES_OBJ->isManualBookingAvailable(),
                "target"  => "blank",
            ],
            [
                'title'   => $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'],
                //'url'     => "cab_booking.php",
                'url'     => $LOCATION_FILE_ARRAY['LATER_BOOKING'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "CabBooking",
                "visible" => ($userObj->hasPermission('view-ride-job-later-bookings') && $RIDE_LATER_BOOKING_ENABLED == 'Yes'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
                //'url'     => "trip.php",
                'url'     => $LOCATION_FILE_ARRAY['TRIP'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Trips",
                "visible" => $userObj->hasPermission('view-trip-jobs'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
                'url'     => $tconfig['tsite_url'] . "user-order-information?order=admin",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "store_order_book",
                "target"  => "blank",
                "visible" => ($userObj->hasPermission('manage-restaurant-order') && $MANUAL_STORE_ORDER_ADMIN_PANEL == "Yes"),
            ],
        ],
    ],
    [
        'title'    => "Manage Locations",
        "icon"     => "ri-map-pin-line",
        "visible"  => $userObj->hasPermission(['view-geo-fence-locations', 'view-restricted-area', 'view-location-wise-fare', 'view-airport-surcharge', 'view-country', 'view-state', 'view-city']),
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
                "visible" => $userObj->hasPermission('view-location-wise-fare')  && $APP_TYPE != 'Delivery' && $APP_TYPE != 'UberX' && $MODULES_OBJ->isRideFeatureAvailable('Yes'),
            ],
            [
                'title'   => "Airport Surcharge",
                "url"     => "airport_surcharge.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "airportsurcharge_fare",
                "visible" => $userObj->hasPermission('view-airport-surcharge') && $ENABLE_AIRPORT_SURCHARGE_SECTION == "Yes",
            ],
            [
                'title'   => "Country",
                "url"     => "country.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "country",
                "visible" => $userObj->hasPermission('view-country'),
            ],
            [
                'title'   => "State",
                "url"     => "state.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "state",
                "visible" => $userObj->hasPermission('view-state'),
            ],
            [
                'title'   => "City",
                "url"     => "city.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "city",
                "visible" => ($userObj->hasPermission('view-city') && $SHOW_CITY_FIELD == 'Yes'),
            ],
        ],
    ],
    [
        'title'   => "God's View",
        "url"     => "map_godsview.php",
        "icon"    => "ri-road-map-line",
        "active"  => "LiveMap",
        "visible" => $userObj->hasPermission('view-god-view'),
    ],
    [
        'title'   => "Heat View",
        "url"     => "heatmap.php",
        "icon"    => "ri-treasure-map-line",
        "active"  => "Heat Map",
        "visible" => $userObj->hasPermission('manage-heatmap'),
    ],

    /*--------------------- App type only Ride start ------------------*/
    [
        'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']." Reviews",
        "url"     => "review.php",
        "icon"    => "ri-user-voice-line",
        "active"  => "Review",
        "visible" => $userObj->hasPermission('manage-reviews') && DELIVERALL_ENABLED == 'No',
    ],
    [
        'title'   => 'Bidding Review',
        "icon"    => "ri-checkbox-blank-circle-line",
        "visible" => $userObj->hasPermission('view-bidding-review') && DELIVERALL_ENABLED == 'No',
        "active"  => "BidReviews",
        "url"     => "bidding_review.php",
    ],
    [
        'title'   => "RideShare Reviews",
        'url'     => "ride_share_reviews.php",
        "icon"    => "ri-user-voice-line",
        "active"  => "ride-share-review",
        "visible" => $userObj->hasPermission('view-booking-rideshare') && DELIVERALL_ENABLED == 'No',
    ],
    [
        'title'   => "Orders Reviews",
        "url"     => "store_review.php",
        "icon"    => "ri-user-voice-line",
        "active"  => "Store Review",
        "visible" => $userObj->hasPermission('manage-store-reviews') && $onlyDeliverallModule == 'YES',
    ],
    /*--------------------- App type only Ride start ------------------*/
    [
        'title'    => "Reviews",
        "icon"     => "ri-user-voice-line",
        "visible"  => $userObj->hasPermission(['manage-reviews', 'manage-store-reviews','view-bidding-review','view-booking-rideshare']) && DELIVERALL_ENABLED == 'Yes' && $onlyDeliverallModule == "NO",
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']. " Reviews",
                "url"     => "review.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Review",
                "visible" => $userObj->hasPermission('manage-reviews'),
            ],
            [
                'title'   => 'Bidding Review',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-review'),
                "active"  => "BidReviews",
                "url"     => "bidding_review.php",
            ],
            [
                'title'   => "RideShare Reviews",
                'url'     => "ride_share_reviews.php",
                "icon"    => "ri-user-voice-line",
                "active"  => "ride-share-review",
                "visible" => $userObj->hasPermission('view-booking-rideshare')
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
        'title'    => "Reports",
        "icon"     => "ri-numbers-line",
        "visible"  => $userObj->hasPermission(['manage-payment-report', 'manage-admin-earning', 'manage-provider-payment-report', 'manage-store-payment', 'manage-provider-payment', 'manage-hotel-payment-report', 'manage-organization-payment-report' , 'view-user-outstanding-report', 'view-org-outstanding-report', 'manage-trip-job-request-acceptance-report ', 'manage-trip-job-time-variance-report', 'manage-provider-log-report', 'manage-cancelled-trip-job-report', 'manage-cancelled-order-report', 'manage-referral-report', 'manage-user-wallet-report', 'manage-insurance-trip-report', 'manage-insurance-accept-report', 'manage-insurance-idle-report', 'view-blocked-driver', 'view-blocked-rider']),
        'children' => [
            [
                'title'    => "Earning Report",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-payment-report', 'manage-admin-earning']),
                'children' => [
                    [
                        'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
                        "url"     => "payment_report.php",
                        "icon"    => "",
                        "active"  => "Payment_Report",
                        "visible" => $userObj->hasPermission('manage-payment-report'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Deliveries",
                        "url"     => "admin_payment_report.php",
                        "icon"    => "",
                        "active"  => "Admin Payment_Report",
                        "visible" => $userObj->hasPermission('manage-admin-earning'),
                    ],
                ]
            ],
            [
                'title'    => "Payout Report",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-provider-payment-report', 'manage-store-payment', 'manage-provider-payment', 'manage-hotel-payment-report', 'manage-organization-payment-report' , 'manage-provider-bidding-payment-report']),
                'children' => [
                    [
                        'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
                        "url"     => "driver_pay_report.php",
                        "icon"    => "",
                        "active"  => "Driver Payment Report",
                        "visible" => $userObj->hasPermission('manage-provider-payment-report'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
                        //"url"     => "restaurants_pay_report.php",
                        "url"     => $LOCATION_FILE_ARRAY['RESTAURANTS_PAY_REPORT.PHP'],
                        "icon"    => "",
                        "active"  => "Restaurant Payment Report",
                        "visible" => $userObj->hasPermission('manage-store-payment'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT'],
                        "url"     => "store_driver_pay_report.php",
                        "icon"    => "",
                        "active"  => "Deliverall Driver Payment Report",
                        "visible" => $userObj->hasPermission('manage-provider-payment'),
                    ],
                    [
                        'title'   => "Bidding",
                        "url"     => "driver_bidding_pay_report.php",
                        "icon"    => "",
                        "active"  => "Bidding_Payment_Report",
                        "visible" => $userObj->hasPermission('manage-provider-bidding-payment-report'),
                    ],
                    [
                        'title'   => "Hotel",
                        "url"     => "hotel_payment_report.php",
                        "icon"    => "",
                        "active"  => "hotelPayment_Report",
                        "visible" => $userObj->hasPermission('manage-hotel-payment-report'),
                    ],
                    [
                        'title'   => "Organization",
                        "url"     => "org_payment_report.php",
                        "icon"    => "",
                        "active"  => "OrganizationPaymentReport",
                        "visible" => $userObj->hasPermission('manage-organization-payment-report'),
                    ],
                ]
            ],
            [
                'title'    => "Outstanding Report",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-user-outstanding-report', 'view-org-outstanding-report']),
                'children' => [
                    [
                        'title'   => "User",
                        "url"     => "outstanding_report.php",
                        "icon"    => "",
                        "active"  => "outstanding_report",
                        "visible" => $userObj->hasPermission('view-user-outstanding-report'),
                    ],
                    [
                        'title'   => "Organization",
                        "url"     => "org_outstanding_report.php",
                        "icon"    => "",
                        "active"  => "org_outstanding_report",
                        "visible" => $userObj->hasPermission('view-org-outstanding-report'),
                    ]
                ]
            ],
            [
                'title'    => 'Decline/Cancelled Alerts',
                'url'      => "blocked_driver.php",
                "icon"     => "ri-checkbox-blank-circle-line",
                "active"   => "blockeddriver",
                "visible"  => $userObj->hasPermission(['view-blocked-driver', 'view-blocked-rider']),
                'children' => [
                    [
                        'title'   => 'Alert For ' . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                        'url'     => $LOCATION_FILE_ARRAY['BLOCKED_DRIVER'],
                        "icon"    => "",
                        "active"  => "blockeddriver",
                        "visible" => $userObj->hasPermission('view-blocked-driver'),
                    ],
                    [
                        'title'   => 'Alert For ' . $langage_lbl_admin['LBL_RIDER'],
                        'url'     => $LOCATION_FILE_ARRAY['BLOCKED_RIDER'],
                        "icon"    => "",
                        "active"  => "blockedrider",
                        "visible" => $userObj->hasPermission('view-blocked-rider'),
                    ],
                ],
            ],
            [
                'title'    => "Other Reports",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-trip-job-request-acceptance-report ', 'manage-trip-job-time-variance-report', 'manage-provider-log-report', 'manage-cancelled-trip-job-report', 'manage-cancelled-order-report', 'manage-referral-report', 'manage-user-wallet-report', 'manage-insurance-trip-report', 'manage-insurance-accept-report', 'manage-insurance-idle-report']),
                'children' => [
                    /*[
                        'title'   => $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Acceptance Report",
                        "url"     => "ride_acceptance_report.php",
                        "icon"    => "",
                        "active"  => "Driver Accept Report",
                        "visible" => $userObj->hasPermission('manage-trip-job-request-acceptance-report'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Time Variance",
                        "url"     => "driver_trip_detail.php",
                        "icon"    => "",
                        "active"  => "Driver Trip Detail",
                        "visible" => $userObj->hasPermission('manage-trip-job-time-variance-report'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Log Report",
                        "url"     => "driver_log_report.php",
                        "icon"    => "",
                        "active"  => "Driver Log Report",
                        "visible" => $userObj->hasPermission('manage-provider-log-report'),
                    ],*/
                    [
                        'title'   => "Cancelled " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'],
                        "url"     => $LOCATION_FILE_ARRAY['CANCELLED_TRIP'],
                        "icon"    => "",
                        "active"  => "CancelledTrips",
                        "visible" => $userObj->hasPermission('manage-cancelled-trip-job-report'),
                    ],
                    [
                        'title'   => "Cancelled / Refunded Order Report",
                        "url"     => "cancelled_report.php",
                        "icon"    => "",
                        "active"  => "Cancelled Order Report",
                        "visible" => $userObj->hasPermission('manage-cancelled-order-report'),
                    ],
                    [
                        'title'   => "MLM Referral Report",
                        "url"     => "referrer.php",
                        "icon"    => "",
                        "active"  => "referrer",
                        "visible" => ($userObj->hasPermission('manage-referral-report') && strtoupper($MLM_FEATURE) == 'YES'),
                    ],
                    [
                        'title'   => "Wallet Report",
                        "url"     => "wallet_report.php",
                        "icon"    => "",
                        "active"  => "Wallet Report",
                        "visible" => ($userObj->hasPermission('manage-user-wallet-report') && strtoupper($WALLET_ENABLE) == 'YES'),
                    ],
                    [
                        'title'   => "User Reward Report",
                        "url"     => "user_reward_report.php",
                        "icon"    => "",
                        "active"  => "UserRewardReport",
                        "visible" => ($userObj->hasPermission('manage-user-reward-report') && strtoupper($USER_REWARD_FEATURE) == "YES"),
                    ],
                    [
                        'title'    => "Insurance Report",
                        "icon"     => "",
                        "visible"  => $userObj->hasPermission(['manage-insurance-trip-report', 'manage-insurance-accept-report', 'manage-insurance-idle-report']),
                        "children" => [
                            [
                                'title'   => "Idle Time",
                                "url"     => "insurance_idle_report.php",
                                "icon"    => "",
                                "active"  => "Insurance_Idle_time_Report",
                                "visible" => $userObj->hasPermission('manage-insurance-trip-report'),
                            ],
                            [
                                'title'   => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Accept",
                                "url"     => "insurance_accept_report.php",
                                "icon"    => "",
                                "active"  => "Insurance_accept_trip_Report",
                                "visible" => $userObj->hasPermission('manage-insurance-accept-report'),
                            ],
                            [
                                'title'   => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Start",
                                "url"     => "insurance_trip_report.php",
                                "icon"    => "",
                                "active"  => "Insurance_start_trip_Report",
                                "visible" => $userObj->hasPermission('manage-insurance-idle-report'),
                            ],
                        ],
                    ],
                ]
            ],

        ],
    ],
    [
        'title'    => "Support Requests",
        "icon"     => "ri-customer-service-2-line",
        "visible"  => $userObj->hasPermission(['view-contactus-report', 'view-sos-request-report', 'view-trip-job-help-request-report', 'view-order-help-request-report', 'view-payment-request-report', 'view-withdraw-request-report']),
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
                "visible" => $userObj->hasPermission('view-sos-request-report') && $onlyDeliverallModule == "NO",
            ],
            [
                'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . " Help Requests",
                "url"     => $LOCATION_FILE_ARRAY['TRIP_HELP_DETAILS'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "trip_help_details",
                "visible" => $userObj->hasPermission('view-trip-job-help-request-report'),
            ],
            [
                'title'   => "Order Help Requests",
                "url"     => "order_help_details.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "order_help_details",
                "visible" => $userObj->hasPermission('view-order-help-request-report'),
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
        'title'    => "Marketing Tools",
        "icon"     => "ri-pages-line",
        "visible"  => $userObj->hasPermission(['view-referral-settings', 'view-advertise-banner', 'view-promocode', 'view-giftcard', 'view-giftcard-image', 'view-news', 'manage-newsletter', 'manage-send-push-notification']),
        'children' => [
            [
                'title'   => "MLM Referral Settings",
                "url"     => "referral_settings.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Referral",
                "visible" => ($userObj->hasPermission('view-referral-settings') && strtoupper($MLM_FEATURE) == "YES"),
            ],
            [
                'title'   => "Advertisement Banners",
                'url'     => "advertise_banners.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Advertisement Banners",
                "visible" => ($userObj->hasPermission('view-advertise-banner') && $ADVERTISEMENT_TYPE != 'Disable'),
            ],
            [
                'title'   => "Promocode",
                "url"     => "coupon.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Coupon",
                "visible" => $userObj->hasPermission('view-promocode'),
            ],
            [
                'title'    => "Manage Gift Cards",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-giftcard', 'view-giftcard-image']) && strtoupper($GIFT_CARD_FEATURE) == 'YES',
                'children' => [
                    [
                        'title'   => "Gift Cards",
                        'url'     => "gift_card.php",
                        "icon"    => "",
                        "active"  => "GiftCard",
                        "visible" => $userObj->hasPermission('view-giftcard')
                    ],
                    [
                        'title'   => "EGV Design Theme",
                        'url'     => "gift_card_images.php",
                        "icon"    => "",
                        "active"  => "GiftCardImages",
                        "visible" => $userObj->hasPermission('view-giftcard-image')
                    ],
                ],
            ],
            [
                'title'   => "News",
                "url"     => "news.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "news",
                "visible" => ($userObj->hasPermission('view-news') && strtoupper($ENABLE_NEWS_SECTION) == 'YES'),
            ],
            [
                'title'   => "Newsletter Subscribers",
                "url"     => "newsletter.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "newsletters-subscribers",
                "visible" => ($userObj->hasPermission('manage-newsletter') && strtoupper($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION) == 'YES'),
            ],
            [
                'title'   => "Send Push-Notification",
                "url"     => "send_notifications.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Push Notification",
                "visible" => $userObj->hasPermission('manage-send-push-notification'),
            ],
        ]
    ],
    [
        'title'    => "CMS",
        "icon"     => "ri-pages-line",
        "visible"  => $userObj->hasPermission(['manage-home-page-content', 'manage-our-service-menu', 'manage-app-home-screen-view', 'view-app-home-screen-banner', 'manage-general-app-launch-info', 'manage-passenger-app-launch-info', 'manage-driver-app-launch-info', 'manage-company-app-launch-info', 'view-general-label', 'view-email-templates', 'view-sms-templates', 'view-cancel-reasons', 'view-faq-categories', 'view-faq', 'view-help-detail', 'view-help-detail-category', 'manage-general-settings', 'manage-currency', 'manage-language', 'view-seo-setting', 'view-documents', 'expired-documents']),
        'children' => [
            /*--------------------- home page ------------------*/
            [
                'title'    => "Website All Pages",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-home-page-content', 'manage-our-service-menu']) && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0)) && IS_CUBEX_APP == "No" && $onlyDeliverallModule == "NO",
                'children' => [
                    [
                        'title'   => "Manage Web Home Page",
                        "url"     => "homepage_content.php",
                        "icon"    => "",
                        "active"  => "homecontent",
                        "visible" => $userObj->hasPermission('manage-home-page-content'),
                    ],
                    [
                        'title'   => "Manage Our Service Menu",
                        "url"     => "master_service_menu.php",
                        "icon"    => "",
                        "active"  => "masterServiceMenu",
                        "visible" => $userObj->hasPermission('manage-our-service-menu') && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0)) && ENABLE_OUR_SERVICES_MENU == "Yes",
                    ],
                    [
                        'title'   => "Service Section",
                        "url"     => "service_section.php",
                        "icon"    => "",
                        "active"  => "ServiceSection",
                        "visible" => $userObj->hasPermission('manage-home-page-content') && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0)),
                    ],
                    [
                        'title'   => "Manage Earn Page",
                        "url"     => "earn_content_action.php",
                        "icon"    => "",
                        "active"  => "earncontent",
                        "visible" => $userObj->hasPermission('manage-earn-page-content') &&  ENABLE_EARN_PAGE == 'Yes',
                    ],
                    [
                        'title'   => "Other Pages",
                        "url"     => "page.php",
                        "icon"    => "",//"ri-checkbox-blank-circle-line"
                        "active"  => "page",
                        "visible" => $userObj->hasPermission('view-pages'),
                    ],
                ]
            ],
            [
                'title'   => "Manage Web Home Page",
                "url"     => "homepage_content.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('manage-home-page-content') && (in_array($APP_TYPE , ['Ride-Delivery','Ride', 'Delivery']) || IS_CUBEX_APP == "Yes" || $onlyDeliverallModule == "YES" || ($APP_TYPE == "UberX") && $parent_ufx_catid > 0),
            ],
            [
                'title'   => "Other Pages",
                "url"     => "page.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "page",
                "visible" => $userObj->hasPermission('view-pages') && ((($APP_TYPE == "UberX") && $parent_ufx_catid > 0) || in_array($APP_TYPE , ['Delivery'])) ,
            ],
            /*--------------------- home page ------------------*/
            [
                'title'    => "User App Home Screen",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-app-home-screen-view', 'view-app-home-screen-banner']) && strtoupper(IS_CUBEX_APP) == "NO" && strtoupper($APP_TYPE) != "RIDE-DELIVERY",
                'children' => [
                    [
                        'title'  => 'Home Page',
                        'url'    => "manage_app_home_screen.php",
                        "icon"   => '',
                        "active" => "ManageAppHomePage",
                        "visible" => $userObj->hasPermission('manage-app-home-screen-view')
                    ],
                    [
                        'title'   => "Home Page Banners",
                        "url"     => "banner.php",
                        "icon"    => "",
                        "active"  => "Banners",
                        "visible" => $userObj->hasPermission('view-app-home-screen-banner')
                    ],
                ],
            ],
            [
                "title"    => "User App Home Screen",
                "icon"     => "ri-checkbox-blank-circle-line",
                "url"   => "manage_app_home_screen.php",
                "active" => "ManageAppHomePage",
                "visible"  => $userObj->hasPermission('manage-app-home-screen-view') && (strtoupper(IS_CUBEX_APP) == "YES" || strtoupper($APP_TYPE) == "RIDE-DELIVERY"),
            ],
            /*[
                'title'   => "Home Page Banners",
                "url"     => "banner.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Banners",
                "visible" => $userObj->hasPermission('view-app-home-screen-banner')
            ],*/
            [
                'title'    => "Manage App Intro Screen",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-general-app-launch-info', 'manage-passenger-app-launch-info', 'manage-driver-app-launch-info', 'manage-company-app-launch-info']),
                'children' => [
                    [
                        'title'  => 'General',
                        'url'    => "app_launch_info.php?option=General",
                        "icon"   => '',
                        "active" => "app_launch_info_General",
                        "visible" => $userObj->hasPermission('manage-general-app-launch-info')
                    ],
                    [
                        'title'  => 'User App',
                        'url'    => $LOCATION_FILE_ARRAY['APP_LAUNCH_INFO_PASSENGER'],
                        "icon"   => '',
                        "active" => "app_launch_info_Passenger",
                        "visible" => $userObj->hasPermission('manage-passenger-app-launch-info')
                    ],
                    [
                        'title'  => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' App',
                        'url'    =>  $LOCATION_FILE_ARRAY['APP_LAUNCH_INFO_DRIVER'],
                        "icon"   => '',
                        "active" => "app_launch_info_Driver",
                        "visible" => $userObj->hasPermission('manage-driver-app-launch-info')
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . ' App',
                        'url'     => "app_launch_info.php?option=Company",
                        "icon"    => '',
                        "active"  => "app_launch_info_Company",
                        "visible" => $userObj->hasPermission('manage-company-app-launch-info')
                    ],
                    [
                        'title'   => 'Tracking App',
                        'url'     => "app_launch_info.php?option=TrackServiceUser",
                        "icon"    => '',
                        "active"  => "app_launch_info_TrackServiceUser",
                        "visible" => $userObj->hasPermission('manage-trackserviceuser-app-launch-info')
                    ],
                ],
            ],
            /*--------------------- languages ------------------*/
            [
                'title'    => "Manage Language Labels",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('view-general-label') && ($deliverallModule == 'YES' || $onlyDeliverallModule == 'YES'),
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
                'title'   => "Manage Language Labels",
                "url"     => "languages.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "language_label",
                "visible" => $userObj->hasPermission('view-general-label') && (!($deliverallModule == 'YES' || $onlyDeliverallModule == 'YES')),
            ],
            /*--------------------- languages ------------------*/
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
                'title'   => "Cancel Reason",
                "url"     => "cancellation_reason.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "cancel_reason",
                "visible" => $userObj->hasPermission('view-cancel-reasons'),
            ],
            [
                'title'    => "FAQs",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-faq-categories', 'view-faq']),
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => "faq_categories.php",
                        "icon"    => "",
                        "active"  => "faq_categories",
                        "visible" => $userObj->hasPermission('view-faq-categories'),
                    ],
                    [
                        'title'   => "All FAQs",
                        "url"     => "faq.php",
                        "icon"    => "",
                        "active"  => "Faq",
                        "visible" => $userObj->hasPermission('view-faq'),
                    ],
                ]
            ],
            [
                'title'    => "Help",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-help-detail', 'view-help-detail-category']),
                'children' => [
                    [
                        'title'   => "Help Topics",
                        "url"     => "help_detail.php",
                        "icon"    => "",
                        "active"  => "help_detail",
                        "visible" => $userObj->hasPermission('view-help-detail'),
                    ],
                    [
                        'title'   => "Help Topic Categories",
                        "url"     => "help_detail_categories.php",
                        "icon"    => "",
                        "active"  => "help_detail_categories",
                        "visible" => $userObj->hasPermission('view-help-detail-category'),
                    ],
                ]
            ],

        ],
    ],
    [
        'title'    => "Settings",
        "icon"     => "ri-settings-5-line",
        "visible"  => $userObj->hasPermission(['manage-general-settings', 'manage-currency', 'manage-language', 'view-seo-setting', 'view-map-api-service-account','view-documents','expired-documents']),
        'children' => [
            [
                'title'   => "General",
                "url"     => "general.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "General",
                "visible" => $userObj->hasPermission('manage-general-settings'),
            ],
            [
                'title'   => "Master Services",
                "url"     => "master_service_category.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "MasterServiceCategory",
                "visible" => $userObj->hasPermission('view-master-service-category'),
            ],
            /*[
                'title'   => "Language Label - Pages",
                "url"     => "master_lng_pages.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "MasterLanguagePages",
                "visible" => $userObj->hasPermission('view-documents') && $IS_INHOUSE_DOMAINS && $displayInhousePage > 0,
            ],*/
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
                // "url"     => "map_api_setting.php",
                "url"     => "map_api_mongo_auth_places.php?id=" . $data_Service_Google['vServiceId'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "map_api_setting",
                // "visible" => $userObj->hasPermission('view-map-api-service-account') && $MODULES_OBJ->mapAPIreplacementAvailable() == true && strtoupper(SITE_TYPE) == "LIVE",
                "visible" => (strtoupper(ENABLE_MAP_API_SETTING) == "YES") ? true : false
            ],
            [
                'title'    => "Documents",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-documents', 'expired-documents']),
                'children' => [
                    [
                        'title'   => "Manage Documents",
                        "url"     => "document_master_list.php",
                        "icon"    => "",
                        "active"  => "Document Master",
                        "visible" => $userObj->hasPermission('view-documents'),
                    ],
                    [
                        'title'   => 'Expired Documents',
                        'url'     => "expired_documents.php",
                        "icon"    => '',
                        "active"  => "Expired Documents",
                        "visible" => $userObj->hasPermission('expired-documents'),
                    ],
                ],
            ],
        ],
    ],
    [
        'title'    => "Utility",
        "icon"     => "ri-tools-line",
        "visible"  => $userObj->hasPermission(['view-vehicle-make', 'view-vehicle-model', 'view-db-backup']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_CAR_MAKE_ADMIN'],
                "url"     => "make.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Make",
                "visible" => $userObj->hasPermission('view-vehicle-make'),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_CAR_MODEL_ADMIN'],
                "url"     => "model.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Model",
                "visible" => $userObj->hasPermission('view-vehicle-model'),
            ],
            [
                'title'   => "Kiosk predefined destination",
                "url"     => "visit.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Visit",
                "visible" => $userObj->hasPermission('view-visit') && $kioskPanel > 0 && isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == 'hotel',
            ],
            [
                'title'   => "Donation",
                "url"     => "donation.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "donation",
                "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
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
            ]
        ],
    ],
    [
        'title' => "Logout",
        "url"   => "logout.php",
        "icon"  => "ri-logout-box-r-line",
    ],
];
if (isset($_REQUEST['menu_search'])) {
    $menu = multiSearch($menu, array('title' => $_REQUEST['menu_search']), 0);
}
return $menu;