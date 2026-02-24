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

$SessionUserTypeCheck = isset($_SESSION['SessionUserType']) ? $_SESSION['SessionUserType'] : '';
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
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');

$rideEnabled = $MODULES_OBJ->isRideFeatureAvailable('Yes');
$deliveryEnabled = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes');
$foodCategoryAvailable = "No";
if (scount($service_categories_ids_arr) >= 1 && in_array(1, $service_categories_ids_arr)) {
    $foodCategoryAvailable = "Yes";
}


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


  function langLabelMenu() {
    global $allservice_cat_data, $userObj;
    $languages_childs = [
        [
            'title'   => "General Label",
            "url"     => "languages.php",
            "icon"    => "ri-checkbox-blank-circle-line",
            "active"  => "language_label",
            "visible" => $userObj->hasPermission('view-general-label'),
        ],
    ];


    if (scount($allservice_cat_data) >= 1 && !empty($allservice_cat_data)) {
        foreach ($allservice_cat_data as $key => $value) {
            $languages_childs[] = [
                'title'   => $value['vServiceName'] . " Label",
                'url'     => "languages.php?selectedlanguage=" . $value['iServiceId'],
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "language_label_" . $value['iServiceId'],
                "visible" => $userObj->hasPermission('view-general-label'),
            ];
        }
    }
    return $languages_childs;
}
$bookingReportsTitle = (strtoupper($onlyDeliverallModule) == "NO") ? 'Bookings & Reports' : 'Orders & Reports';

$MCategory = getMasterServiceCategoryId();
$VehicleCategory = getVehicleCategoryId();

$data_Service_names = $obj->fetchAllRecordsFromMongoDBWithDBName(TSITE_DB, "auth_master_accounts_places", []);
$data_Service_names = json_decode(json_encode($data_Service_names), true);
$ServiceKey = array_search('Google', array_column($data_Service_names, 'vServiceName'));
$data_Service_Google = $data_Service_names[$ServiceKey];
$MapApiSettingUrl = $tconfig['tsite_url_main_admin'].'map_api_mongo_auth_places.php?id='.$data_Service_Google['vServiceId'];

$menu = [
    [
        'parent'  => 'HOME',
        'parent_menu' => 'HOME',
        'title'   => 'Dashboard',
        'url'     => "dashboard.php",
        "icon"    => 'ri-dashboard-line',
        "active"  => "dashboard",
        "visible" => ($userObj->hasRole(5) && $userObj->hasPermission(['dashboard-video-consultation','dashboard-bid-services','dashboard-ride-share','dashboard-store-deliveries','dashboard-delivery-genie-runner','dashboard-buy-sell-rent','dashboard-god-view','dashboard-member-statistics','dashboard-ride-job-statistics','dashboard-latest-ride-job','dashboard-contact-us-form Requests','dashboard-notifications-alerts-panel','admin-earning-dashboard','later-bookings-dashboard']) ) || true,
    ],
    [
        'parent_menu' => 'HOME',
        'title'   => 'Server Monitoring',
        'url'     => "server_admin_dashboard.php",
        "icon"    => 'ri-bar-chart-box-line',
        "active"  => "server_dashboard",
        "visible" => $userObj->hasPermission('manage-server-admin-dashboard') && ($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live"),
    ],
    [
        'parent'   => 'Members',
        'parent_menu' => 'Members',
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
                "visible" => $userObj->hasPermission('view-admin-group') && $PACKAGE_TYPE == 'SHARK',
            ]
        ],
    ],
    [
        'parent_menu' => 'Members',
        'title'   => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url'     => $LOCATION_FILE_ARRAY['RIDER.PHP'],
        "icon"    => "ri-team-line",
        "active"  => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ],
    [
        'parent_menu' => 'Members',
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "visible" => $userObj->hasPermission('view-providers-bidding-requests'),
                        "active"  => "biddingDriverRequest",
                        "url"     => "bidding_driver_request.php",
                    ],
                    [
                        'title'   => 'On Demand Service Requests',
                        "url"     => "driver_service_request.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "DriverRequest",
                        "visible" => $userObj->hasPermission('view-providers-on-demand-service-requests'),
                    ],
                    [
                        'title'   => 'Video Consult Service Requests',
                        "url"     => "driver_service_request.php?eType=VideoConsult",
                        "icon"    => "ri-checkbox-blank-circle-fill",
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Reports",
                        "visible" => $userObj->hasPermission(['view-driver-reward-report' ]),
                    ],
                    [
                        'title'   => 'Setting',
                        'url'     => "reward.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
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
                        'url'     => "driver_subscription.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "DriverSubscriptionPlan",
                        "visible" => $userObj->hasPermission('view-driver-subscription'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'],
                        'url'     => "driver_subscription_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "DriverSubscriptionReport",
                        "visible" => $userObj->hasPermission('manage-driver-subscription-report'),
                    ],
                ],
            ],
        ],
    ],
    [
        'parent_menu' => 'Members',
        'title'   => $langage_lbl_admin['LBL_COMPANY_ADMIN_TXT'],
        'url'     => "company.php",
        "icon"    => "ri-building-4-line",
        "active"  => "Company",
        "visible" => $userObj->hasPermission('view-company'),
    ],            
    [
        'parent_menu' => 'Members',
        'title'   => $restaurantAdmin,
        'url'     => $LOCATION_FILE_ARRAY['STORE.PHP'],
        "icon"    => "ri-store-2-line",
        "active"  => "DeliverAllStore",
        "visible" => $userObj->hasPermission('view-store'),
    ],

    [
        'parent_menu' => 'Members',
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
            [
                'title'   => 'Create request',
                'title111'   => 'Create request',
                'url'     => "create_request.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "booking",
                "visible" => $userObj->hasPermission('manage-create-request') && $_SESSION['sess_iGroupId'] != '1' /* && $APP_TYPE == 'Ride' */ && ($hotelPanel > 0 || $kioskPanel > 0),
                "target"  => "blank",
            ],
        ],
    ],
    [
        'parent_menu' => 'Members',
        'title'   => "Organization",
        "url"     => "organization.php",
        "icon"    => "ri-building-line",
        "active"  => "Organization",
        "visible" => $userObj->hasPermission('view-organization') && ONLY_MEDICAL_SERVICE != 'Yes',
    ],
    /*--------------------- App type only Ride start ------------------*/

    [
        'parent'   => "Services",
        'parent_menu' => 'Services',
        'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
        "url"    => "vehicle_category.php?eType=Ride",
        "icon"   => "ri-function-line",
        "active" => "VehicleCategory_Ride",
        "visible" => $userObj->hasPermission('view-service-category-taxi-service') && $APP_TYPE == 'Ride' ,
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
        "url"     => "vehicle_type.php?eType=Ride",
        "icon"    => "ri-taxi-line",
        "active"  => "VehicleType_Ride",
        "visible" => $userObj->hasPermission('view-vehicle-type-taxi-service') && $APP_TYPE == 'Ride' ,
    ],
    [
        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_RENTAL_TXT'],
        "url"     => "rental_vehicle_list.php",
        "icon"    => "ri-red-packet-line",
        "active"  => "Rental Package",
        "visible" => ($userObj->hasPermission('view-rental-packages') && strtoupper(ENABLE_RENTAL_OPTION) == 'YES') && $APP_TYPE == 'Ride',
    ],
    [
        'title'    => "Manage Ride Profiles",
        "icon"     => "ri-briefcase-line",
        "visible"  => $userObj->hasPermission(['view-organization', 'view-profile-taxi-service', 'view-trip-reason-taxi-service']) && $APP_TYPE == 'Ride',
        'children' => [
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
    [
        'title'    => "Intercity Rides",
        "icon"     => "ri-attachment-line",
        "visible"  => $userObj->hasPermission(['view-vehicle-type-intercity-service','view-rental-intercity-packages']) && $MODULES_OBJ->isInterCityFeatureAvailable() && $APP_TYPE == 'Ride',
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                "url"     => "vehicle_type.php?eType=InterCity",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleType_InterCity",
                "visible" => $userObj->hasPermission('view-vehicle-type-intercity-service'),
            ],
            [
                'title'   => 'Intercity Packages',
                "url"     => "intercity_vehicle_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "InterCity Rental Package",
                "visible" => ($userObj->hasPermission('view-rental-intercity-packages')),
            ],
        ]
    ],

    [
        'title'    => "Taxi Bid Service",
        "icon"     => "ri-taxi-wifi-line",
        "visible"  => $userObj->hasPermission(['view-service-content-taxi-bid-service', 'view-service-category-taxi-bid-service', 'view-provider-vehicles-taxi-bid-service', 'view-vehicle-type-taxi-bid-service']) && $MODULES_OBJ->isEnableTaxiBidFeature() && $APP_TYPE == 'Ride',
        'children' => [
           /* [
                'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"    => "vehicle_category.php?eType=TaxiBid",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active" => "VehicleCategory_TaxiBid",
                "visible" => $userObj->hasPermission('view-service-category-taxi-bid-service'),
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

    /*--------------------- App type only Ride start ------------------*/
    /*--------------------- App type only UberX start ------------------*/

    [
        'parent'   => "Services",
        'parent_menu' => 'Services',
        'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
        "url"     => $LOCATION_FILE_ARRAY['MASTER_CATEGORY_UBERX'],
        "icon"    => "ri-function-line",
        "active"  => "VehicleCategory_UberX",
        "visible" => $userObj->hasPermission('view-service-category-uberx') && $APP_TYPE == 'UberX' && $parent_ufx_catid > 0,
    ],
    [
        'title'   => "Service Type",
        "url"     => "service_type.php",
        "icon"    => "ri-pages-line",
        "active"  => "ServiceType",
        "visible" => $userObj->hasPermission('view-service-type') && $APP_TYPE == 'UberX' && $parent_ufx_catid > 0,
    ],
    [
        'title'   => "Vehicle Size Info",
        "url"     => "vehicle_size_info.php",
        "icon"    => "ri-car-washing-line",
        "active"  => "vehicle_size_info",
        "visible" => $userObj->hasPermission('view-service-type') && $APP_TYPE == 'UberX' && $parent_ufx_catid > 0 && $MODULES_OBJ->isEnableCarSizeServiceTypeAmount(),
    ],
    /*--------------------- App type only UberX start ------------------*/



    [
        'parent'   => "Services",
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_RIDE_SERVICE_TITLE'],
        "icon"     => "ri-taxi-line",
        "visible"  => $userObj->hasPermission(['view-service-content-taxi-service', 'view-service-category-taxi-service', 'view-provider-vehicles-taxi-service', 'view-vehicle-type-taxi-service', 'view-rental-packages-taxi-service', 'view-organization'])   && $APP_TYPE != 'Ride' && strtoupper(ONLY_MEDICAL_SERVICE) == "NO",
        'children' => [
            [
                'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"    => "vehicle_category.php?eType=Ride",
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => "VehicleCategory_Ride",
                "visible" => $userObj->hasPermission('view-service-category-taxi-service'),
            ],
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
                        'title'   => "Ride Profile Type",
                        "url"     => "user_profile_master.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RideProfileType",
                        "visible" => $userObj->hasPermission('view-profile-taxi-service'),
                    ],
                    [
                        'title'   => "Business Trip Reason",
                        "url"     => "trip_reason.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "BusinessTripReason",
                        "visible" => $userObj->hasPermission('view-trip-reason-taxi-service'),
                    ],
                ],
            ],
            [
                'title'    => "Intercity Rides",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-vehicle-type-intercity-service','view-rental-intercity-packages']) && $MODULES_OBJ->isInterCityFeatureAvailable(),
                'children' => [
                    [
                        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                        "url"     => "vehicle_type.php?eType=InterCity",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "VehicleType_InterCity",
                        "visible" => $userObj->hasPermission('view-vehicle-type-intercity-service'),
                    ],
                    [
                        'title'   => 'Intercity Packages',
                        "url"     => "intercity_vehicle_list.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "InterCity Rental Package",
                        "visible" => ($userObj->hasPermission('view-rental-intercity-packages')),
                    ],
                ]
            ],

            [
                'title'    => "Taxi Bid Service",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-service-content-taxi-bid-service', 'view-service-category-taxi-bid-service', 'view-provider-vehicles-taxi-bid-service', 'view-vehicle-type-taxi-bid-service']) && $MODULES_OBJ->isEnableTaxiBidFeature(),
                'children' => [
                    /*[
                        'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                        "url"    => "vehicle_category.php?eType=TaxiBid",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active" => "VehicleCategory_TaxiBid",
                        "visible" => $userObj->hasPermission('view-service-category-taxi-bid-service'),
                    ],*/
                    [
                        'title'   => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                        "url"     => "vehicle_type.php?eType=TaxiBid",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "VehicleType_TaxiBid",
                        "visible" => $userObj->hasPermission('view-vehicle-type-taxi-bid-service'),
                    ],
                ],
            ],

        ],
    ],
    /*[
        'parent_menu' => 'Services',
        'title'    => "Taxi Bid Service",
        "icon"     => "ri-taxi-line",
        "visible"  => $userObj->hasPermission(['view-service-content-taxi-bid-service', 'view-service-category-taxi-bid-service', 'view-provider-vehicles-taxi-bid-service', 'view-vehicle-type-taxi-bid-service']) && $MODULES_OBJ->isEnableTaxiBidFeature(),
        'children' => [
            [
                'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"    => "vehicle_category.php?eType=TaxiBid",
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => "VehicleCategory_TaxiBid",
                "visible" => $userObj->hasPermission('view-service-category-taxi-bid-service'),
            ],
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
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_PARCEL_DELIVERY_ADMIN_TXT'],
        "icon"     => "ri-truck-line",
        "visible" => $userObj->hasPermission(['view-service-content-parcel-delivery', 'view-service-category-parcel-delivery', 'view-provider-vehicles-parcel-delivery', 'view-package-type-parcel-delivery', 'view-banner-parcel-delivery']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => isset($VehicleCategory['MoreDelivery']['url']) ? $VehicleCategory['MoreDelivery']['url'] : '',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => isset($VehicleCategory['MoreDelivery']['active']) ? $VehicleCategory['MoreDelivery']['active'] : '',
                "visible" => $userObj->hasPermission('view-service-content-parcel-delivery') && strtoupper(IS_DELIVERYKING_APP) == "YES",
            ],
            [
                'title'  => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"    => isset($VehicleCategory['MoreDelivery']['sub_category_url']) ? $VehicleCategory['MoreDelivery']['sub_category_url'] : '',
                "icon"   => "ri-checkbox-blank-circle-line",
                "active" => isset($VehicleCategory['MoreDelivery']['sub_category_action']) ? $VehicleCategory['MoreDelivery']['sub_category_action'] : '',
                "visible" => $userObj->hasPermission('view-service-category-parcel-delivery'),
            ],
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
                "url"     => isset($VehicleCategory['MoreDelivery']['banner_url']) ? $VehicleCategory['MoreDelivery']['banner_url'] : '',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "MoreDelivery_banner",
                "visible" => $userObj->hasPermission('view-banner-parcel-delivery') && strtoupper($APP_TYPE) != "RIDE-DELIVERY"
            ],
        ],
    ],
    [
        'parent_menu' => 'Services',
        'title'    => $restaurantAdmin . " Delivery Services",
        "icon"     => "ri-store-2-line",
        "visible"  => $userObj->hasPermission(['view-service-content-deliverall', 'view-service-category-deliverall', 'view-vehicle-type', 'view-item-categories', 'view-item', 'view-item-type', 'manage-import-bulk-items', 'view-processing-orders', 'view-all-orders', 'view-cancelled-orders', 'view-order-status', 'view-delivery-charges', 'view-custom-delivery-charges', 'view-banner-store', 'view-store-categories', 'view-delivery-preference', 'view-rating-feedback-ques', 'manage-otp-for-stores']) && $APP_TYPE != "Delivery",
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "ImportItem",
                        "visible" => $userObj->hasPermission('manage-import-bulk-items') && strtoupper(ENABLE_BULK_ITEM_DATA) == "YES",
                    ],
                    [
                        'title'   => "Item Categories",
                        "url"     => $LOCATION_FILE_ARRAY['FOOD_MENU.PHP'],
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "FoodMenu",
                        "visible" => $userObj->hasPermission('view-item-categories'),
                    ],
                    [
                        'title'   => "Items",
                        "url"     => "menu_item.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "MenuItems",
                        "visible" => $userObj->hasPermission('view-item'),
                    ],
                    [
                        'title'   => "Item Type",
                        "url"     => "cuisine.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Processing Orders",
                        "visible" => $userObj->hasPermission('view-processing-orders'),
                    ],
                    [
                        'title'   => "Cancelled",
                        "url"     => "cancelled_orders.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "CancelledOrders",
                        "visible" => $userObj->hasPermission('view-cancelled-orders'),
                    ],
                    [
                        'title'   => "All Orders",
                        "url"     => "allorders.php?type=allorders",
                        "icon"    => "ri-checkbox-blank-circle-fill",
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
                    [
                        'title'   => "Order Status",
                        "url"     => "order_status.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "order_status",
                        "visible" => $userObj->hasPermission('view-order-status'),
                    ],
                    [
                        'title'   => "User Delivery Charges",
                        "url"     => "delivery_charges.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Delivery Charges",
                        "visible" => $userObj->hasPermission('view-delivery-charges'),
                    ],
                    [
                        'title'   => "Driver Delivery Charges",
                        "url"     => "custom_delivery_charge_order.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Custom Delivery Charges",
                        "visible" => $userObj->hasPermission('view-custom-delivery-charges') && strtoupper($DISTANCE_WISE_DELIVERY_CHARGES) == "YES",
                    ],
                    [
                        'title'   => "Manage " . $restaurantAdmin . " Categories",
                        "url"     => "store_category.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "ManageStoreCategories",
                        "visible" => $userObj->hasPermission('view-store-categories'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DELIVERY_PREF'],
                        'url'     => "delivery_preferences.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "DeliveryPreferences",
                        "visible" => $userObj->hasPermission('view-delivery-preference') && strtoupper($CONTACTLESS_DELIVERY_MODULE) == "YES",
                    ],
                    [
                        'title'   => "Manage OTP For Service Categories",
                        'url'     => "manage_otp_for_stores.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "otpservicecategory",
                        "visible" => $userObj->hasPermission('manage-otp-for-stores') && strtoupper($OTP_VERIFICATION) == "YES" && scount($service_categories_ids_arr) > 1,
                    ],
                    [
                        'title'   => "Rating Feedback Questions",
                        'url'     => "rating_feedback_ques.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RatingFeedbackQuestions",
                        "visible" => $userObj->hasPermission('view-rating-feedback-ques') && strtoupper($FOOD_RATING_DETAIL_FEATURE) == "YES",
                    ],
                ],
            ],
            [
                'title'   => "Banners",
                "url"     => "store_banner.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Store Banner",
                "visible" => $userObj->hasPermission('view-banner-store'),
            ],
        ],
    ],
    [
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_DELIVERY_GENIE_ADMIN_TXT'],
        "icon"     => "ri-e-bike-2-line",
        "visible"  => $userObj->hasPermission(['view-service-content-genie-delivery', 'view-banner-genie-delivery']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => isset($VehicleCategory['Genie']['url']) ? $VehicleCategory['Genie']['url'] : '',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => isset($VehicleCategory['Genie']['active']) ? $VehicleCategory['Genie']['active'] : '',
                "visible" => $userObj->hasPermission('view-service-content-genie-delivery'),
            ],
            [
                'title'  => $langage_lbl_admin['LBL_BANNER_ADMIN_TXT'],
                "url"    => isset($VehicleCategory['Genie']['banner_url']) ? $VehicleCategory['Genie']['banner_url'] : '',
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
            ]
        ],
    ],
    [
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_DELIVERY_RUNNER_ADMIN_TXT'],
        "icon"     => "ri-takeaway-line",
        "visible"  => $userObj->hasPermission(['view-service-content-runner-delivery', 'view-banner-runner-delivery']),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => isset($VehicleCategory['Runner']['url']) ? $VehicleCategory['Runner']['url'] : '',
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => isset($VehicleCategory['Runner']['active']) ? $VehicleCategory['Runner']['active'] : '',
                "visible" => $userObj->hasPermission('view-service-content-runner-delivery'),
            ],
            [
                'title'  => $langage_lbl_admin['LBL_BANNER_ADMIN_TXT'],
                "url"    => isset($VehicleCategory['Runner']['banner_url']) ? $VehicleCategory['Runner']['banner_url'] : '',
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
            ]
        ],
    ],
    [
        'parent_menu' => 'Services',
        'title'    => 'On-Demand Services',
        "icon"     => "ri-function-line",
        "visible"  => $userObj->hasPermission(['view-service-content-uberx', 'view-service-category-uberx', 'view-service-type']) && strtoupper(ONLY_MEDICAL_SERVICE) == "NO" && $APP_TYPE != 'UberX' || ($APP_TYPE == 'UberX' && $parent_ufx_catid == 0),
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
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
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_VIDEO_CONSULTATION_TXT'],
        "icon"     => "ri-live-line",
        "visible"  => $userObj->hasPermission(['view-service-content-video-consultation','view-service-category-video-consultation']) && strtoupper($VIDEO_CONSULTING_FEATURE) == "YES" && strtoupper(ONLY_MEDICAL_SERVICE) == "NO",
        'children' => [
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
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_MANANGE_BIDDING_SERVICES'],
        "icon"     => "ri-auction-line",
        "visible"  => $userObj->hasPermission(['view-service-content-bidding', 'view-bidding-category', 'manage-bids-report']),
        'children' => [
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
            [
                'title'   => 'Bidding Review',
                "icon"    => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-review') && ($APP_TYPE == 'UberX' && $parent_ufx_catid == 0),
                "active"  => "BidReviews",
                "url"     => "bidding_review.php",
            ]
        ],
    ],
    [
        'parent_menu' => 'Services',
        'title'    => "Buy, Sell & Rent",
        "icon"     => "ri-auction-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rentestate', 'view-service-category-rentestate', 'view-pending-rentestate', 'view-approved-rentestate', 'view-all-rentestate', 'manage-rentestate-fields', 'view-payment-plan-rentestate', 'report-rentestate', 'view-banner-rentestate' ,'view-service-content-rentcars', 'view-service-category-rentcars', 'view-pending-rentcars', 'view-approved-rentcars', 'view-all-rentcars', 'manage-rentcars-fields', 'view-payment-plan-rentcars', 'report-rentcars', 'view-banner-rentcars' ,'view-service-content-rentitem', 'view-service-category-rentitem', 'view-pending-rentitem', 'view-approved-rentitem', 'view-all-rentitem', 'manage-rentitem-fields', 'view-payment-plan-rentitem', 'report-rentitem', 'view-banner-rentitem' ]) && ($MODULES_OBJ->isEnableRentEstateService() || $MODULES_OBJ->isEnableRentCarsService() || $MODULES_OBJ->isEnableRentItemService() ),
        'children' => [
            [
                'title'    => 'Real Estate',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-service-content-rentestate', 'view-service-category-rentestate', 'view-pending-rentestate', 'view-approved-rentestate', 'view-all-rentestate', 'manage-rentestate-fields', 'view-payment-plan-rentestate', 'report-rentestate', 'view-banner-rentestate']),
                "active"   => "RentEstate",
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => 'bsr_master_category.php?eType=RealEstate',
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentEstate",
                        "visible" => $userObj->hasPermission('view-service-category-rentestate'),
                    ],
                    [
                        'title'   => 'Pending for Approval',
                        "url"     => "pending_item.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "PendingRentEstate",
                        "visible" => $userObj->hasPermission('view-pending-rentestate'),
                    ],
                    [
                        'title'   => "Approved Properties",
                        "url"     => "item_approved.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "ApprovedRentEstate",
                        "visible" => $userObj->hasPermission('view-approved-rentestate'),
                    ],
                    [
                        'title'   => "All Properties",
                        "url"     => "all_bsr_items.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "AllRentEstate",
                        "visible" => $userObj->hasPermission('view-all-rentestate'),
                    ],
                    [
                        'title'   => "Manage Form Fields",
                        "url"     => "data_fields.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentEstateFields",
                        "visible" => $userObj->hasPermission('manage-rentestate-fields') && ENABLE_DATAFEILDS_ADMIN == 'Yes',
                    ],
                    [
                        'title'   => "Payment Plans",
                        "url"     => "item_payment_plans.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentEstatePaymentPlan",
                        "visible" => $userObj->hasPermission('view-payment-plan-rentestate'),
                    ],
                    [
                        'title'   => "Payment Report",
                        "url"     => "bsr_item_payment_report.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentEstateReport",
                        "visible" => $userObj->hasPermission('report-rentestate'),
                    ],
                    [
                        'title'   => "Banners",
                        "url"     => "bsr_banner.php?eType=RealEstate",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentEstateBanner",
                        "visible" => $userObj->hasPermission('view-banner-rentestate'),
                    ],
                ],
            ],
            [
                'title'    => 'Cars',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-service-content-rentcars', 'view-service-category-rentcars', 'view-pending-rentcars', 'view-approved-rentcars', 'view-all-rentcars', 'manage-rentcars-fields', 'view-payment-plan-rentcars', 'report-rentcars', 'view-banner-rentcars']),
                "active"   => "RentCars",
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => 'bsr_master_category.php?eType=Cars',
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentCars",
                        "visible" => $userObj->hasPermission('view-service-category-rentcars'),
                    ],
                    [
                        'title'   => 'Pending for Approval',
                        "url"     => "pending_item.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "PendingRentCars",
                        "visible" => $userObj->hasPermission('view-pending-rentcars'),
                    ],
                    [
                        'title'   => "Approved Cars",
                        "url"     => "item_approved.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "ApprovedRentCars",
                        "visible" => $userObj->hasPermission('view-approved-rentcars'),
                    ],
                    [
                        'title'   => "All Cars",
                        "url"     => "all_bsr_items.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "AllRentCars",
                        "visible" => $userObj->hasPermission('view-all-rentcars'),
                    ],
                    [
                        'title'   => "Manage Form Fields",
                        "url"     => "data_fields.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentCarsFields",
                        "visible" => $userObj->hasPermission('manage-rentcars-fields') && ENABLE_DATAFEILDS_ADMIN == 'Yes',
                    ],
                    [
                        'title'   => "Payment Plans",
                        "url"     => "item_payment_plans.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentCarsPaymentPlan",
                        "visible" => $userObj->hasPermission('view-payment-plan-rentcars'),
                    ],
                    [
                        'title'   => "Payment Report",
                        "url"     => "bsr_item_payment_report.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentCarsReport",
                        "visible" => $userObj->hasPermission('report-rentcars'),
                    ],
                    [
                        'title'   => "Banners",
                        "url"     => "bsr_banner.php?eType=Cars",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentCarsBanner",
                        "visible" => $userObj->hasPermission('view-banner-rentcars'),
                    ],
                ],
            ],
            [
                'title'    => 'General Items',
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['view-service-content-rentitem', 'view-service-category-rentitem', 'view-pending-rentitem', 'view-approved-rentitem', 'view-all-rentitem', 'manage-rentitem-fields', 'view-payment-plan-rentitem', 'report-rentitem', 'view-banner-rentitem']),
                "active"   => "RentItem",
                'children' => [
                    [
                        'title'   => "Categories",
                        "url"     => 'bsr_master_category.php?eType=GeneralItem',
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentItem",
                        "visible" => $userObj->hasPermission('view-service-category-rentitem'),
                    ],
                    [
                        'title'   => 'Pending for Approval',
                        "url"     => "pending_item.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "PendingRentItem",
                        "visible" => $userObj->hasPermission('view-pending-rentitem'),
                    ],
                    [
                        'title'   => "Approved Items",
                        "url"     => "item_approved.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "ApprovedRentItem",
                        "visible" => $userObj->hasPermission('view-approved-rentitem'),
                    ],
                    [
                        'title'   => "All Items",
                        "url"     => "all_bsr_items.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "AllRentItem",
                        "visible" => $userObj->hasPermission('view-all-rentitem'),
                    ],
                    [
                        'title'   => "Manage Form Fields",
                        "url"     => "data_fields.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentItemFields",
                        "visible" => $userObj->hasPermission('manage-rentitem-fields') && ENABLE_DATAFEILDS_ADMIN == 'Yes',
                    ],
                    [
                        'title'   => "Payment Plans",
                        "url"     => "item_payment_plans.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentItemPaymentPlan",
                        "visible" => $userObj->hasPermission('view-payment-plan-rentitem'),
                    ],
                    [
                        'title'   => "Payment Report",
                        "url"     => "bsr_item_payment_report.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentItemReport",
                        "visible" => $userObj->hasPermission('report-rentitem'),
                    ],
                    [
                        'title'   => "Banners",
                        "url"     => "bsr_banner.php?eType=GeneralItem",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "RentItemBanner",
                        "visible" => $userObj->hasPermission('view-banner-rentitem'),
                    ],
                ],
            ],
        ]
    ],
    [
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_MEDICAL_SERVICES_ADMIN_TXT'],
        "icon"     => "ri-hospital-line",
        "visible"  => $userObj->hasPermission(['view-service-content-medical', 'view-service-category-medical', 'view-provider-vehicles-medical', 'view-vehicle-type-medical']) && MED_UFX_ENABLED == 'Yes',
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SUB_SERVICES_ADMIN_TXT'],
                "url"     => "vehicle_category.php?eType=MedicalServices",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "VehicleCategory_MedicalServices",
                "visible" => $userObj->hasPermission('view-service-category-medical'),
            ],
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
        'parent_menu' => 'Services',
        'title'    => $langage_lbl_admin['LBL_RIDE_SHARE_TXT'],
        "icon"     => "ri-car-washing-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rideshare', 'view-published-rides-rideshare', 'view-booking-rideshare', 'view-payment-report-rideshare', 'view-driver-detail-fields-rideshare']),
        'children' => [
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
           /* [
                'title'   => "Reviews",
                'url'     => "ride_share_reviews.php",
                "icon"    => "ri-checkbox-blank-circle-line",
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
        'parent_menu' => 'Services',
        'title'    => "Nearby Management",
        "icon"     => "ri-pin-distance-line",
        "visible"  => $userObj->hasPermission(['view-service-content-nearby', 'view-category-nearby', 'view-places-nearby', 'view-banners-nearby']),
        'children' => [
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
    [
        'parent_menu' => 'Services',
        'title'    => "FET Tracking Service",
        "icon"     => "ri-user-location-line",
        "visible"  => $userObj->hasPermission(['view-service-content-trackanyservice', 'view-users-trackanyservice']),
        'children' => [
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
        'parent'   => $bookingReportsTitle,
        'parent_menu' => $bookingReportsTitle,
        'title'    => strtoupper(DELIVERALL_ENABLED) == "YES" ? "Bookings / Orders" : "Bookings",
        "icon"     => "ri-file-list-line",
        "visible"  => $userObj->hasPermission(['create-manage-manual-booking', 'view-ride-job-later-bookings', 'view-trip-jobs', 'manage-restaurant-order']) && $onlyDeliverallModule == "NO",
        'children' => [
            [
                'title'   => "Manual Booking",
                'url'     => "add_booking.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "booking",
                "visible" => $userObj->hasPermission('create-manage-manual-booking') && ($SessionUserTypeCheck == 'hotel' ? !$MODULES_OBJ->isManualBookingAvailable() : $SessionUserTypeCheck != 'hotel') && $MODULES_OBJ->isManualBookingAvailable() && $APP_TYPE != 'UberX',
                "target"  => "blank",
            ],
            [
                'title'   => "Manual Booking",
                'url'     => "add_booking.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "booking",
                "visible" => $userObj->hasPermission('create-manage-manual-booking') && $MODULES_OBJ->isManualBookingAvailable() && $APP_TYPE == 'UberX' && $THEME_OBJ->isProBTYAIOThemeActive() == "Yes",
                "target"  => "blank",
            ],
            [
                'title'   => $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'],
                'url'     => $LOCATION_FILE_ARRAY['LATER_BOOKING'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "CabBooking",
                "visible" => ($userObj->hasPermission('view-ride-job-later-bookings') && $RIDE_LATER_BOOKING_ENABLED == 'Yes' && (isset($SessionUserTypeCheck) && $SessionUserTypeCheck != 'hotel')),
            ],
            [
                'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
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
                "visible" => ($userObj->hasPermission('manage-restaurant-order') && $MANUAL_STORE_ORDER_ADMIN_PANEL == "Yes") && $APP_TYPE != 'UberX',
            ],
        ],
    ],
    [
        'parent_menu' => $bookingReportsTitle,
        'title'    => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
        'url'     => $tconfig['tsite_url'] . "user-order-information?order=admin",
        "icon"     => "ri-file-list-line",
        "active"  => "store_order_book",
        "visible"  => $userObj->hasPermission(['manage-restaurant-order']) && $onlyDeliverallModule == "YES",
    ],
    [
        'parent_menu' => $bookingReportsTitle,
        'title'   => "Reviews",
        "url"     => "review.php",
        "icon"    => "ri-user-voice-line",
        "active"  => "Review",
        "visible" => $userObj->hasPermission('manage-reviews') && DELIVERALL_ENABLED == 'No',
    ],
    [
        'parent_menu' => $bookingReportsTitle,
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
                "icon"    => "ri-checkbox-blank-circle-line",
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
        'parent_menu' => $bookingReportsTitle,
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Payment_Report",
                        "visible" => $userObj->hasPermission('manage-payment-report'),
                    ],
                    [
                        'title'   => $restaurantAdmin . " Deliveries",
                        "url"     => "admin_payment_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Driver Payment Report",
                        "visible" => $userObj->hasPermission('manage-provider-payment-report'),
                    ],
                    [
                        'title'   => $restaurantAdmin,
                        "url"     => $LOCATION_FILE_ARRAY['RESTAURANTS_PAY_REPORT.PHP'],
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Restaurant Payment Report",
                        "visible" => $userObj->hasPermission('manage-store-payment'),
                    ],
                    [
                        'title'   => $restaurantAdmin." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT'],
                        "url"     => "store_driver_pay_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Deliverall Driver Payment Report",
                        "visible" => $userObj->hasPermission('manage-provider-payment'),
                    ],
                    [
                        'title'   => "Bidding",
                        "url"     => "driver_bidding_pay_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Bidding_Payment_Report",
                        "visible" => $userObj->hasPermission('manage-provider-bidding-payment-report'),
                    ],
                    [
                        'title'   => "Hotel",
                        "url"     => "hotel_payment_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "hotelPayment_Report",
                        "visible" => $userObj->hasPermission('manage-hotel-payment-report'),
                    ],
                    [
                        'title'   => "Organization",
                        "url"     => "org_payment_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "OrganizationPaymentReport",
                        "visible" => $userObj->hasPermission('manage-organization-payment-report') && ONLY_MEDICAL_SERVICE != 'Yes',
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "outstanding_report",
                        "visible" => $userObj->hasPermission('view-user-outstanding-report'),
                    ],
                    [
                        'title'   => "Organization",
                        "url"     => "org_outstanding_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "org_outstanding_report",
                        "visible" => $userObj->hasPermission('view-org-outstanding-report') && ONLY_MEDICAL_SERVICE != 'Yes',
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "blockeddriver",
                        "visible" => $userObj->hasPermission('view-blocked-driver'),
                    ],
                    [
                        'title'   => 'Alert For ' . $langage_lbl_admin['LBL_RIDER'],
                        'url'     => $LOCATION_FILE_ARRAY['BLOCKED_RIDER'],
                        "icon"    => "ri-checkbox-blank-circle-fill",
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
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Driver Accept Report",
                        "visible" => $userObj->hasPermission('manage-trip-job-request-acceptance-report'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Time Variance",
                        "url"     => "driver_trip_detail.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Driver Trip Detail",
                        "visible" => $userObj->hasPermission('manage-trip-job-time-variance-report'),
                    ],
                    [
                        'title'   => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Log Report",
                        "url"     => "driver_log_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Driver Log Report",
                        "visible" => $userObj->hasPermission('manage-provider-log-report'),
                    ],*/
                    [
                        'title'   => "Cancelled " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'],
                        "url"     => $LOCATION_FILE_ARRAY['CANCELLED_TRIP'],
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "CancelledTrips",
                        "visible" => $userObj->hasPermission('manage-cancelled-trip-job-report'),
                    ],
                    [
                        'title'   => "Cancelled / Refunded Order Report",
                        "url"     => "cancelled_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Cancelled Order Report",
                        "visible" => $userObj->hasPermission('manage-cancelled-order-report'),
                    ],
                    [
                        'title'   => "MLM Referral Report",
                        "url"     => "referrer.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "referrer",
                        "visible" => ($userObj->hasPermission('manage-referral-report') && strtoupper($MLM_FEATURE) == 'YES'),
                    ],
                    [
                        'title'   => "Wallet Report",
                        "url"     => "wallet_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "Wallet Report",
                        "visible" => ($userObj->hasPermission('manage-user-wallet-report') && strtoupper($WALLET_ENABLE) == 'YES'),
                    ],
                    [
                        'title'   => "User Reward Report",
                        "url"     => "user_reward_report.php",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "active"  => "UserRewardReport",
                        "visible" => ($userObj->hasPermission('manage-user-reward-report') && strtoupper($USER_REWARD_FEATURE) == "YES"),
                    ],
                    [
                        'title'    => "Insurance Report",
                        "icon"    => "ri-checkbox-blank-circle-fill",
                        "visible"  => $userObj->hasPermission(['manage-insurance-trip-report', 'manage-insurance-accept-report', 'manage-insurance-idle-report']),
                        "children" => [
                            [
                                'title'   => "Idle Time",
                                "url"     => "insurance_idle_report.php",
                                "icon"    => "ri-checkbox-blank-circle-fill",
                                "active"  => "Insurance_Idle_time_Report",
                                "visible" => $userObj->hasPermission('manage-insurance-trip-report'),
                            ],
                            [
                                'title'   => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Accept",
                                "url"     => "insurance_accept_report.php",
                                "icon"    => "ri-checkbox-blank-circle-fill",
                                "active"  => "Insurance_accept_trip_Report",
                                "visible" => $userObj->hasPermission('manage-insurance-accept-report'),
                            ],
                            [
                                'title'   => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Start",
                                "url"     => "insurance_trip_report.php",
                                "icon"    => "ri-checkbox-blank-circle-fill",
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
        "parent"  => "Location",
        'parent_menu' => "Location",
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
        'parent_menu' => "Location",
        'title'   => "God's View",
        "url"     => "map_godsview.php",
        "icon"    => "ri-road-map-line",
        "active"  => "GodsView",
        "visible" => $userObj->hasPermission('view-god-view'),
    ],
    [
        'parent_menu' => "Location",
        'title'   => "Heat View",
        "url"     => "heatmap.php",
        "icon"    => "ri-treasure-map-line",
        "active"  => "Heat Map",
        "visible" => $userObj->hasPermission('manage-heatmap'),
    ],
    [
        "parent"  => "Promotions & Marketing Tools",
        'parent_menu' => 'Promotions & Marketing Tools',
        'title'   => "Promocode",
        "url"     => "coupon.php",
        "icon"    => "ri-coupon-line",
        "active"  => "Coupon",
        "visible" => $userObj->hasPermission('view-promocode'),
    ],
    [
        'parent_menu' => 'Promotions & Marketing Tools',
        'title'    => "Manage Gift Cards",
        "icon"     => "ri-gift-line",
        "visible"  => $userObj->hasPermission(['view-giftcard', 'view-giftcard-image']) && strtoupper($GIFT_CARD_FEATURE) == 'YES',
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
                "visible" => $userObj->hasPermission('view-giftcard-image')
            ],
        ],
    ],
    [
        'parent_menu' => 'Promotions & Marketing Tools',
        'title'   => "MLM Referral Settings",
        "url"     => "referral_settings.php",
        "icon"    => "ri-organization-chart",
        "active"  => "Referral",
        "visible" => ($userObj->hasPermission('view-referral-settings') && strtoupper($MLM_FEATURE) == "YES"),
    ],
    [
        'parent_menu' => 'Promotions & Marketing Tools',
        'title'   => "Advertisement Banners",
        'url'     => "advertise_banners.php",
        "icon"    => "ri-advertisement-line",
        "active"  => "Advertisement Banners",
        "visible" => $userObj->hasPermission('view-advertise-banner'),
    ],
    [
        'parent_menu' => 'Promotions & Marketing Tools',
        'title'   => "News",
        "url"     => "news.php",
        "icon"    => "ri-news-line",
        "active"  => "news",
        "visible" => ($userObj->hasPermission('view-news') && strtoupper($ENABLE_NEWS_SECTION) == 'YES'),
    ],
    [
        'parent_menu' => 'Promotions & Marketing Tools',
        'title'   => "Newsletter Subscribers",
        "url"     => "newsletter.php",
        "icon"    => "ri-newspaper-line",
        "active"  => "newsletters-subscribers",
        "visible" => ($userObj->hasPermission('manage-newsletter') && strtoupper($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION) == 'YES'),
    ],
    /*--------------------- home page ------------------*/
    [
        'parent'   => 'CMS',
        'parent_menu' => 'CMS',
        'title'    => "Website All Pages",
        "icon"     => "ri-pages-line",
        "visible"  => $userObj->hasPermission(['manage-home-page-content', 'manage-our-service-menu']) && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0)) && IS_CUBEX_APP == "No" && $onlyDeliverallModule == "NO",
        'children' => [
            [
                'title'   => "Manage Web Home Page",
                "url"     => "homepage_content.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('manage-home-page-content'),
            ],
            [
                'title'   => "Manage Our Service Menu",
                "url"     => "master_service_menu.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "masterServiceMenu",
                "visible" => $userObj->hasPermission('manage-our-service-menu') && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0)) && ENABLE_OUR_SERVICES_MENU == "Yes",
            ],
            [
                'title'   => "Service Section",
                "url"     => "service_section.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "ServiceSection",
                "visible" => $userObj->hasPermission('manage-home-page-content') && strtoupper(ONLY_MEDICAL_SERVICE) != "YES" && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0)),
            ],
            [
                'title'   => "Manage Earn Page",
                "url"     => "earn_content_action.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "earncontent",
                "visible" => $userObj->hasPermission('manage-earn-page-content') &&  ENABLE_EARN_PAGE == 'Yes',
            ],
            [
                //'parent_menu' => 'CMS',
                'title'   => "Other Pages",
                "url"     => "page.php",
                "icon"    => "ri-checkbox-blank-circle-line",//ri-pages-line
                "active"  => "page",
                "visible" => $userObj->hasPermission('view-pages'),
            ],
        ]
    ],
    [
        'parent' => 'CMS',
        'parent_menu' => 'CMS',
        'title'   => "Website All Pages",
        "icon"    => "ri-window-line",
        "visible" => $userObj->hasPermission('manage-home-page-content') && (in_array($APP_TYPE , ['Ride-Delivery','Ride', 'Delivery']) || IS_CUBEX_APP == "Yes" || $onlyDeliverallModule == "YES" || ($APP_TYPE == "UberX") && $parent_ufx_catid > 0),
        'children' => [
            [
                'title'   => "Manage Web Home Page",
                "url"     => "homepage_content.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('manage-home-page-content'),
            ],
            [
                //'parent_menu' => 'CMS',
                'title'   => "Other Pages",
                "url"     => "page.php",
                "icon"    => "ri-checkbox-blank-circle-line",//ri-pages-line
                "active"  => "page",
                "visible" => $userObj->hasPermission('view-pages'),
            ]
        ]
    ],
    /*--------------------- home page ------------------*/
    [
        'parent_menu' => 'CMS',
        'title'    => "User App Home Screen",
        "icon"     => "ri-smartphone-line",
        "visible"  => $userObj->hasPermission(['manage-app-home-screen-view', 'view-app-home-screen-banner']) && strtoupper(IS_CUBEX_APP) == "NO" && strtoupper($APP_TYPE) != "RIDE-DELIVERY",
        'children' => [
            [
                'title'  => 'Home Page',
                'url'    => "manage_app_home_screen.php",
                "icon"   => 'ri-checkbox-blank-circle-line',
                "active" => "ManageAppHomePage",
                "visible" => $userObj->hasPermission('manage-app-home-screen-view')
            ],
            [
                'title'   => "Home Page Banners",
                "url"     => "banner.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Banners",
                "visible" => $userObj->hasPermission('view-app-home-screen-banner')
            ],
        ],
    ],
    [
        'parent_menu' => 'CMS',
        "title"    => "User App Home Screen",
        "icon"     => "ri-smartphone-line",
        "url"   => "manage_app_home_screen.php",
        "active" => "ManageAppHomePage",
        "visible"  => $userObj->hasPermission('manage-app-home-screen-view') && (strtoupper(IS_CUBEX_APP) == "YES" || strtoupper($APP_TYPE) == "RIDE-DELIVERY"),
    ],
    [
        'parent_menu' => 'CMS',
        'title'    => "Manage App Intro Screen",
        "icon"     => "ri-article-line",
        "visible"  => $userObj->hasPermission(['manage-general-app-launch-info', 'manage-passenger-app-launch-info', 'manage-driver-app-launch-info', 'manage-company-app-launch-info']),
        'children' => [
            [
                'title'  => 'General',
                'url'    => "app_launch_info.php?option=General",
                "icon"   => 'ri-checkbox-blank-circle-line',
                "active" => "app_launch_info_General",
                "visible" => $userObj->hasPermission('manage-general-app-launch-info')
            ],
            [
                'title'  => 'User App',
                'url'    => $LOCATION_FILE_ARRAY['APP_LAUNCH_INFO_PASSENGER'],
                "icon"   => 'ri-checkbox-blank-circle-line',
                "active" => "app_launch_info_Passenger",
                "visible" => $userObj->hasPermission('manage-passenger-app-launch-info')
            ],
            [
                'title'  => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' App',
                'url'    =>  $LOCATION_FILE_ARRAY['APP_LAUNCH_INFO_DRIVER'],
                "icon"   => 'ri-checkbox-blank-circle-line',
                "active" => "app_launch_info_Driver",
                "visible" => $userObj->hasPermission('manage-driver-app-launch-info')
            ],
            [
                'title'   => $restaurantAdmin . ' App',
                'url'     => "app_launch_info.php?option=Company",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "app_launch_info_Company",
                "visible" => $userObj->hasPermission('manage-company-app-launch-info')
            ],
            [
                'title'   => 'Tracking App',
                'url'     => "app_launch_info.php?option=TrackServiceUser",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "app_launch_info_TrackServiceUser",
                "visible" => $userObj->hasPermission('manage-trackserviceuser-app-launch-info')
            ],
        ],
    ],
    /*--------------------- languages ------------------*/
    [
        'parent_menu' => 'CMS',
        'title'    => "Manage Language Labels",
        "icon"     => "ri-translate",
        "visible"  => $userObj->hasPermission('view-general-label') && ($deliverallModule == 'YES' || $onlyDeliverallModule == 'YES'),
        'children' => langLabelMenu(),
    ],
    [
        'parent_menu' => 'CMS',
        'title'   => "Manage Language Labels",
        "url"     => "languages.php",
        "icon"    => "ri-translate",
        "active"  => "language_label",
        "visible" => $userObj->hasPermission('view-general-label') && (!($deliverallModule == 'YES' || $onlyDeliverallModule == 'YES')),
    ],
    /*--------------------- languages ------------------*/
    [
        'parent_menu' => 'CMS',
        'title'   => "Email Templates",
        "url"     => "email_template.php",
        "icon"    => "ri-mail-line",
        "active"  => "email_templates",
        "visible" => $userObj->hasPermission('view-email-templates'),
    ],
    [
        'parent_menu' => 'CMS',
        'title'   => "SMS Templates",
        "url"     => "sms_template.php",
        "icon"    => "ri-message-2-line",
        "active"  => "sms_templates",
        "visible" => $userObj->hasPermission('view-sms-templates'),
    ],
    [
        'parent_menu' => 'CMS',
        'title'    => "Cancellation",
        "icon"     => "ri-chat-delete-line",
        "visible"  => $userObj->hasPermission(['view-cancel-reasons', 'manage-cancel-reasons']),
        'children' => [
            [
                'title'   => "Cancel Reasons",
                "url"     => "cancellation_reason.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "cancel_reason",
                "visible" => $userObj->hasPermission('view-cancel-reasons'),
            ],
            [
                'title'   => "Proportional Fee Settings",
                "url"     => "proportional_cancellation_settings.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Proportional_Cancellation_Fee",
                "visible" => $userObj->hasPermission('manage-cancel-reasons'),
            ],
            [
                'title'   => "No-Show Fee",
                "url"     => "no_show_fee.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "No_Show_Fee",
                "visible" => $userObj->hasPermission('manage-cancel-reasons'),
            ],
        ]
    ],
    [
        'parent_menu' => 'CMS',
        'title'    => "FAQs",
        "icon"     => "ri-questionnaire-line",
        "visible"  => $userObj->hasPermission(['view-faq-categories', 'view-faq']),
        'children' => [
            [
                'title'   => "Categories",
                "url"     => "faq_categories.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "faq_categories",
                "visible" => $userObj->hasPermission('view-faq-categories'),
            ],
            [
                'title'   => "All FAQs",
                "url"     => "faq.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Faq",
                "visible" => $userObj->hasPermission('view-faq'),
            ],
        ]
    ],
    [
        'parent_menu' => 'CMS',
        'title'    => "Help",
        "icon"     => "ri-question-answer-line",
        "visible"  => $userObj->hasPermission(['view-help-detail', 'view-help-detail-category']),
        'children' => [
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
        ]
    ],

    [
        'parent'  => "Support",
        'parent_menu' => 'Support',
        'title'   => "Contact Us Form Requests",
        "url"     => "contactus.php",
        "icon"    => "ri-draft-line",
        "active"  => "contactus",
        "visible" => $userObj->hasPermission('view-contactus-report'),
    ],
    [
        'parent_menu' => 'Support',
        'title'   => "SOS Requests",
        "url"     => "emergency_contact_data.php",
        "icon"    => "ri-alert-line",
        "active"  => "emergency_contact_data",
        "visible" => $userObj->hasPermission('view-sos-request-report') && $onlyDeliverallModule == "NO",
    ],
    [
        'parent_menu' => 'Support',
        'title'   => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . " Help Requests",
        "url"     => $LOCATION_FILE_ARRAY['TRIP_HELP_DETAILS'],
        "icon"    => "ri-draft-line",
        "active"  => "trip_help_details",
        "visible" => $userObj->hasPermission('view-trip-job-help-request-report'),
    ],
    [
        'parent_menu' => 'Support',
        'title'   => "Order Help Requests",
        "url"     => "order_help_details.php",
        "icon"    => "ri-draft-line",
        "active"  => "order_help_details",
        "visible" => $userObj->hasPermission('view-order-help-request-report'),
    ],
    [
        'parent_menu' => 'Support',
        'title'   => "Payment Requests",
        "url"     => "payment_requests_report.php",
        "icon"    => "ri-hand-coin-line",
        "active"  => "payment_requests",
        "visible" => $userObj->hasPermission('view-payment-request-report'),
    ],
    [
        'parent_menu' => 'Support',
        'title'   => "Withdraw Requests",
        "url"     => "withdraw_requests_report.php",
        "icon"    => "ri-hand-coin-line",
        "active"  => "withdraw_requests",
        "visible" => $userObj->hasPermission('view-withdraw-request-report'),
    ],
    [
        'parent'  => "Settings & Utilities",
        'parent_menu' => 'Settings & Utilities',
        'title'   => "General",
        "url"     => "general.php",
        "icon"    => "ri-settings-line",
        "active"  => "General",
        "visible" => $userObj->hasPermission('manage-general-settings'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "Master Services",
        "url"     => "master_service_category.php",
        "icon"    => "ri-apps-line",
        "active"  => "MasterServiceCategory",
        "visible" => $userObj->hasPermission('view-master-service-category') && !in_array($APP_TYPE, ['Ride']) && ONLYDELIVERALL != 'Yes' && ($APP_TYPE == "Ride-Delivery-UberX" || ($APP_TYPE == "UberX" && $parent_ufx_catid == 0) || 
            $APP_TYPE == "Ride-Delivery"),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "Currency",
        "url"     => "currency.php",
        "icon"    => "ri-currency-line",
        "active"  => "Currency",
        "visible" => $userObj->hasPermission('manage-currency'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "Language",
        "url"     => "language.php",
        "icon"    => "ri-translate-2",
        "active"  => "Language",
        "visible" => $userObj->hasPermission('manage-language'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "SEO Settings",
        "url"     => "seo_setting.php",
        "icon"    => "ri-list-settings-line",
        "active"  => "seo_setting",
        "visible" => $userObj->hasPermission('view-seo-setting'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "Maps/Geo Service API Settings",
        "url"     => $MapApiSettingUrl,
        "icon"    => "ri-map-2-line",
        "active"  => "map_api_setting",
        // "visible" => $userObj->hasPermission('view-map-api-service-account') && $MODULES_OBJ->mapAPIreplacementAvailable() == true && strtoupper(SITE_TYPE) == "LIVE",
        "visible" => $userObj->hasPermission('view-map-api-service-account') && strtoupper(ENABLE_MAP_API_SETTING) == "YES" ? true : false
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "Send Push-Notification",
        "url"     => "send_notifications.php",
        "icon"    => "ri-send-plane-fill",
        "active"  => "Push Notification",
        "visible" => $userObj->hasPermission('manage-send-push-notification'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'    => "Documents",
        "icon"     => "ri-file-list-line",
        "visible"  => $userObj->hasPermission(['view-documents', 'expired-documents']),
        'children' => [
            [
                'title'   => "Manage Documents",
                "url"     => "document_master_list.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Document Master",
                "visible" => $userObj->hasPermission('view-documents'),
            ],
            [
                'title'   => 'Expired Documents',
                'url'     => "expired_documents.php",
                "icon"    => 'ri-checkbox-blank-circle-line',
                "active"  => "Expired Documents",
                "visible" => $userObj->hasPermission('expired-documents'),
            ],
        ],
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => $langage_lbl_admin['LBL_CAR_MAKE_ADMIN'],
        "url"     => "make.php",
        "icon"    => "ri-car-line",
        "active"  => "Make",
        "visible" => $userObj->hasPermission('view-vehicle-make'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => $langage_lbl_admin['LBL_CAR_MODEL_ADMIN'],
        "url"     => "model.php",
        "icon"    => "ri-roadster-line",
        "active"  => "Model",
        "visible" => $userObj->hasPermission('view-vehicle-model'),
    ],

    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "Donation",
        "url"     => "donation.php",
        "icon"    => "ri-hand-heart-line",
        "active"  => "donation",
        "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => "DB Backup",
        "url"     => "backup.php",
        "icon"    => "ri-database-2-line",
        "active"  => "Back-up",
        "visible" => $userObj->hasPermission('view-db-backup'),
    ],
    [
        'parent_menu' => 'Settings & Utilities',
        'title'   => 'System Diagnostic',
        'url'     => "system_diagnostic.php",
        "icon"    => 'ri-checkbox-blank-circle-line',
        "active"  => "site",
        "visible" => isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel' && !($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live"),
    ]
];

if (isset($_REQUEST['menu_search'])) {
    $menu = multiSearch($menu, array('title' => $_REQUEST['menu_search']), 0);
}
return $menu;