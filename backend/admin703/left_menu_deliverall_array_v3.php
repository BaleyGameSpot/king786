<?php

global $allservice_cat_data;

//Added By HJ On 08-07-2019 For Config Popular Store (service_provider.php) Screen As Per Discuss With KS Sir Start
$enableServiceProvicer = 0;
$foodCategoryAvailable = "No";
if (scount($service_categories_ids_arr) >= 1 && in_array(1, $service_categories_ids_arr)) {
    $enableServiceProvicer = 1;
    $foodCategoryAvailable = "Yes";
}

//Added By HJ On 08-07-2019 For Config Popular Store (service_provider.php) Screen As Per Discuss With KS Sir End
//added by SP as discussed with bmam on 28-6-2019
$adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'];
if ($PACKAGE_TYPE == 'SHARK' && ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL == 'No') {
    $adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'] . '/Hotel';
}

$ChangeFile = ChangeFileCls::fileArray('LOCATION_FILE');
$menu = [
    [
        'title' => 'Dashboard',
        'url' => 'dashboard.php',
        "icon" => 'ri-dashboard-line',
        "active" => "dashboard",
    ], [
        'title' => 'Server Monitoring',
        'url' => "server_admin_dashboard.php",
        "icon" => 'ri-bar-chart-box-line',
        "active" => "server_dashboard",
        "visible" => ($userObj->hasPermission('manage-server-admin-dashboard') && $MODULES_OBJ->isEnableAppHomeScreenLayoutV2()),
    ], [
        'title' => 'Admin',
        'url' => "javascript:",
        "icon" => 'ri-admin-line',
        "visible" => ($userObj->hasRole(1) || $userObj->hasPermission('view-admin')),
        'children' => [
            [
                'title' => $adminUsersTxt,
                'url' => "admin.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Admin",
            ], [
                'title' => 'Admin Groups',
                'url' => "admin_groups.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "AdminGroups",
                "visible" => $userObj->hasRole(1) && $PACKAGE_TYPE == 'SHARK',
            ],
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
        "url" => "store.php",
        "icon" => "ri-store-2-line",
        "active" => "DeliverAllStore",
        "visible" => $userObj->hasPermission('view-store'),
    ], 
    [
        'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
        'url' => "driver.php",
        "icon" => 'ri-user-2-line',
        "active" => "Driver",
        "visible" => $userObj->hasPermission('view-providers'),
    ],[
        'title' => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url' => $ChangeFile['RIDER.PHP'],
        "icon" => 'ri-team-line',
        "active" => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ], [
        'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' Vehicles',
        "url" => "vehicles.php",
        "icon" => "ri-takeaway-line",
        "active" => "Vehicle",
        "visible" => $userObj->hasPermission('view-provider-taxis'),
    ], [
        'title' => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
        'url' => "store_vehicle_type.php",
        "icon" => 'ri-takeaway-line',
        "active" => "StoreVehicleType",
        "visible" => $userObj->hasPermission('view-vehicle-type'),
    ], [
        'title' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Items",
        "icon" => "ri-restaurant-line",
        "visible" => $userObj->hasPermission('manage-store-items'),
        'children' => [
            [
                'title' => "Import Bulk Items",
                "url" => "import_item_data.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "ImportItem",
                "visible" => (($userObj->hasPermission('view-item-categories') || $userObj->hasPermission('view-item')) && $MODULES_OBJ->isEnableBulkImportItems()),
            ],
            [
                'title' => "Item Category",
                "url" => "food_menu.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "FoodMenu",
                "visible" => $userObj->hasPermission('view-item-categories'),
            ], [
                'title' => "Items",
                "url" => "menu_item.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "MenuItems",
                "visible" => $userObj->hasPermission('view-item'),
            ], [
                'title' => "Item Type",
                "url" => "cuisine.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Cuisine",
                "visible" => $userObj->hasPermission('view-item-type'),
            ],
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_VEHICLE_TXT_ADMIN'],
        'url' => "vehicles.php",
        "icon" => "fa fa-taxi",
        "active" => "Vehicle",
        "visible" => ($userObj->hasPermission('view-provider-taxis') && $APP_TYPE != 'Ride-Delivery-UberX'),
    ], [
        'title' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Orders",
        "icon" => "ri-restaurant-2-line",
        "visible" => $userObj->hasPermission('manage-orders'),
        'children' => [
            [
                'title' => "Processing",
                "url" => "allorders.php?type=processing",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Processing Orders",
                "visible" => $userObj->hasPermission('view-processing-orders'),
            ], [
                'title' => "Cancelled",
                "url" => "cancelled_orders.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "CancelledOrders",
                "visible" => $userObj->hasPermission('view-cancelled-orders'),
            ], [
                'title' => "All Orders",
                "url" => "allorders.php?type=allorders",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "All Orders",
                "visible" => $userObj->hasPermission('view-all-orders'),
            ],
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
        'url' => "../user-order-information?order=admin",
        "icon" => 'ri-book-read-line',
        "active" => "store_order_book",
        "target" => "blank",
        "visible" => ($userObj->hasPermission('manage-restaurant-order') && DELIVERALL == "Yes" && $MANUAL_STORE_ORDER_ADMIN_PANEL=="Yes"),
    ], [
        'title' => "Manage " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Categories",
        "url" => "store_category.php",
        "icon" => "fa fa-certificate",
        "active" => "ManageStoreCategories",
        "visible" => ($userObj->hasPermission('view-store-categories') && $MODULES_OBJ->isStoreClassificationEnable()),
    ], [
        'title' => $langage_lbl_admin['LBL_DELIVERY_PREF'],
        'url' => "delivery_preferences.php",
        "icon" => 'ri-equalizer-line',
        "active" => "DeliveryPreferences",
        "visible" => ($userObj->hasPermission('view-delivery-preference') && $MODULES_OBJ->isDeliveryPreferenceEnable()),
    ], [
        'title' => "Manage OTP For Service Categories",
        'url' => "manage_otp_for_stores.php",
        "icon" => 'ri-smartphone-line',
        "active" => "otpservicecategory",
        "visible" => ($userObj->hasPermission('manage-otp-for-stores') && $MODULES_OBJ->isEnableOTPVerificationDeliverAll() && scount($service_categories_ids_arr) > 1),
    ], [
        'title' => "Rating Feedback Questions",
        'url' => "rating_feedback_ques.php",
        "icon" => "ri-questionnaire-line",
        "active" => "RatingFeedbackQuestions",
        "visible" => ($userObj->hasPermission('view-rating-feedback-ques') && $MODULES_OBJ->isEnableFoodRatingDetailFlow() && $foodCategoryAvailable == "Yes"),
    ], [
        'title' => "PromoCode",
        "url" => "coupon.php",
        "icon" => 'ri-coupon-line',
        "active" => "Coupon",
        "visible" => $userObj->hasPermission('view-promocode'),
    ], [
        'title' => "God's View",
        "url" => "map_godsview.php",
        "icon" => 'ri-road-map-line',
        "active" => "LiveMap",
        "visible" => $userObj->hasPermission('manage-gods-view'),
    ], [
        'title' => "Heat View",
        "url" => "heatmap.php",
        "icon" => 'ri-treasure-map-line',
        "active" => "Heat Map",
        "visible" => $userObj->hasPermission('manage-heat-view'),
    ], [
        'title' => "Reviews",
        "url" => "store_review.php",
        "icon" => 'ri-user-voice-line',
        "active" => "Store Review",
        "visible" => $userObj->hasPermission('manage-reviews'),
    ],
    [
        'title' => "Advertisement Banners",
        'url' => "advertise_banners.php",
        "icon" => "ri-advertisement-line",
        "active" => "Advertisement Banners",
        "visible" => $userObj->hasPermission('view-advertise-banner') && ($ADVERTISEMENT_TYPE != 'Disable' && $PACKAGE_TYPE == 'SHARK'),
    ], [
        'title' => 'Decline/Cancelled Alert',
        "icon" => "ri-user-unfollow-line",
        "visible" => ($userObj->hasPermission('view-blocked-driver') || $userObj->hasPermission('view-blocked-rider')) && $PACKAGE_TYPE == 'SHARK', //This Module Enable For Shark Package As Per Discss With KS Sir By HJ On 05-11-2019
        'children' => [
            [
                'title' => 'Alert For ' . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                'url' => "blocked_driver.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "blockeddriver",
                "visible" => $userObj->hasPermission('view-blocked-driver'),
            ], [
                'title' => 'Alert For ' . $langage_lbl_admin['LBL_RIDER'],
                'url' => "blocked_rider.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "blockedrider",
                "visible" => $userObj->hasPermission('view-blocked-rider'),
            ]
        ],
    ], [
        'title' => "Site Earning",
        "icon" => "ri-funds-box-line",
        "visible" => $userObj->hasPermission('manage-site-earning'),
        'children' => [
            [
                'title' => "Admin Earning",
                "url" => "admin_payment_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Admin Payment_Report",
                "visible" => $userObj->hasPermission('manage-admin-earning'),
            ], [
                'title' => "Payout to " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
                "url" => "restaurants_pay_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Restaurant Payment Report",
                "visible" => $userObj->hasPermission('manage-store-payment'),
            ], [
                'title' => "Payout to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                "url" => "store_driver_pay_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Deliverall Driver Payment Report",
                "visible" => $userObj->hasPermission('manage-provider-payment'),
            ], [
                'title' => "Cancelled / Refunded Order Report",
                "url" => "cancelled_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Cancelled Order Report",
                "visible" => $userObj->hasPermission('manage-cancelled-order-report'),
            ],
        ],
    ], [
        'title' => "Reports",
        "icon" => 'ri-numbers-line',
        "visible" => $userObj->hasPermission('manage-report'),
        'children' => [
            [
                'title' => "Referral Report",
                "url" => "referrer.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "referrer",
                "visible" => ($userObj->hasPermission('manage-referral-report') && $REFERRAL_SCHEME_ENABLE == 'Yes'),
            ], [
                'title' => "Wallet Report",
                "url" => "wallet_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Wallet Report",
                "visible" => ($userObj->hasPermission('manage-user-wallet-report') && $WALLET_ENABLE == 'Yes'),
            ], [
                'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Log Report",
                "url" => "driver_log_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Driver Log Report",
                "visible" => $userObj->hasPermission('manage-provider-log-report'),
            ], [
                'title' => "Insurance Report",
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => $MODULES_OBJ->isInsuranceReportEnable() && $userObj->hasPermission('manage-payment-report'),
                'children' => [
                    [
                        'title' => "Idle Time",
                        "url" => "insurance_idle_report.php",
                        "icon" => "",
                        "active" => "Insurance_Idle_time_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_IDLE_REPORT == 'Yes',
                    ], [
                        'title' => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Accept",
                        "url" => "insurance_accept_report.php",
                        "icon" => "",
                        "active" => "Insurance_accept_trip_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_ACCEPT_REPORT == 'Yes',
                    ], [
                        'title' => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Start",
                        "url" => "insurance_trip_report.php",
                        "icon" => "",
                        "active" => "Insurance_start_trip_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_TRIP_REPORT == 'Yes',
                    ]
                ]
            ],[
                'title' => "User Outstanding Report",
                "url" => "outstanding_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "outstanding_report",
                "visible" => $userObj->hasPermission('view-user-outstanding-report'),
            ],
        ],
    ], [
        'title' => "Support Requests",
        "icon" => 'ri-customer-service-2-line',
        "visible" => ($userObj->hasPermission('view-contactus-report') || $userObj->hasPermission('view-sos-request-report') || $userObj->hasPermission('view-order-help-request-report') || $userObj->hasPermission('view-payment-request-report') || $userObj->hasPermission('view-withdraw-request-report')),
        'children' => [
            [
                'title' => "Contact Us Form Requests",
                "url" => "contactus.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "contactus",
                "visible" => $userObj->hasPermission('view-contactus-report'),
            ], [
                'title' => "Order Help Requests",
                "url" => "order_help_details.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "order_help_details",
                "visible" => $userObj->hasPermission('view-order-help-request-report'),
            ], [
                'title' => "Payment Requests",
                "url" => "payment_requests_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "payment_requests",
                "visible" => $userObj->hasPermission('view-payment-request-report'),
            ], [
                'title' => "Withdraw Requests",
                "url" => "withdraw_requests_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "withdraw_requests",
                "visible" => $userObj->hasPermission('view-withdraw-request-report'),
            ]
        ]
    ], [
        'title' => "Manage Locations",
        "icon" => 'ri-map-pin-line',
        "visible" => $userObj->hasPermission('manage-locations'),
        'children' => [
            [
                'title' => "Geo Fence Location",
                "url" => "location.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Location",
                "visible" => $userObj->hasPermission('view-geo-fence-locations'),
            ],
            [
                'title' => "Restricted Area",
                "url" => "restricted_area.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Restricted Area",
                "visible" => $userObj->hasPermission('view-restricted-area'),
            ],
            [
                'title' => "Delivery Charges",
                "url" => "delivery_charges.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Delivery Charges",
                "visible" => $userObj->hasPermission('view-delivery-charges') && DELIVERALL == 'Yes',
            ],
            [
                'title' => "Distance wise Delivery Charges",
                "url" => "custom_delivery_charge_order.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Custom Delivery Charges",
                "visible" => $userObj->hasPermission('view-custom-delivery-charges') && $MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder(),
            ], 
            [
                'title' => "Locationwise Fare",
                "url" => "locationwise_fare.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "locationwise_fare",
                "visible" => $userObj->hasPermission('view-location-wise-fare') && $APP_TYPE != 'UberX' && $APP_TYPE != "Delivery" && ONLYDELIVERALL == 'No',
            ],
        ],
    ], [
        'title' => "Settings",
        "icon" => 'ri-settings-5-line',
        "visible" => $userObj->hasPermission('manage-settings'),
        'children' => [
            [
                'title' => "General",
                "url" => "general.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "General",
                "visible" => $userObj->hasPermission('view-general-label'),
            ],
            [
                'title' => "Referral Settings",
                "url" => "referral_settings.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Referral",
                "visible" => ($userObj->hasPermission('view-referral-settings') && $MODULES_OBJ->isEnableMultiLevelReferralSystem()),
            ],
            [
                'title' => "Email Templates",
                "url" => "email_template.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "email_templates",
                "visible" => $userObj->hasPermission('view-email-templates'),
            ],
            [
                'title' => "SMS Templates",
                "url" => "sms_template.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "sms_templates",
                "visible" => $userObj->hasPermission('view-sms-templates'),
            ],
            [
                'title' => "Manage Documents",
                "url" => "document_master_list.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Document Master",
                "visible" => $userObj->hasPermission('view-documents'),
            ], [
                'title' => "Language Label",
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('manage-language-label') && (DELIVERALL == 'Yes' || ONLYDELIVERALL == 'Yes'),
                'children' => function () {
                    global $allservice_cat_data, $userObj;
                    $languages_childs = [
                        [
                            'title' => "General Label",
                            "url" => "languages.php",
                            "icon" => "",
                            "active" => "language_label",
                            "visible" => $userObj->hasPermission('view-general-label'),
                        ],
                    ];
                    if (scount($allservice_cat_data) >= 1 && !empty($allservice_cat_data)) {
                        foreach ($allservice_cat_data as $key => $value) {
                            $languages_childs[] = [
                                'title' => $value['vServiceName'] . " Label",
                                'url' => "languages.php?selectedlanguage=" . $value['iServiceId'],
                                "icon" => '',
                                "active" => "language_label_" . $value['iServiceId'],
                            ];
                        }
                    }
                    return $languages_childs;
                }
            ], [
                'title' => "Currency",
                "url" => "currency.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Currency",
                "visible" => $userObj->hasPermission('manage-currency'),
            ], [
                'title' => "Language",
                "url" => "language.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Language",
                "visible" => $userObj->hasPermission('manage-language'),
            ], [
                'title' => "SEO Settings",
                "url" => "seo_setting.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "seo_setting",
                "visible" => $userObj->hasPermission('view-seo-setting'),
            ],[
                'title' => "Maps API Settings",
                "url" => "map_api_setting.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "map_api_setting",
                "visible" => $userObj->hasPermission('view-map-api-setting') && ($MODULES_OBJ->mapAPIreplacementAvailable() == true),
            ],
            [
                'title' => "Banner",
                "url" => "store_banner.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Banner",
                "visible" => $userObj->hasPermission('view-banner'),
            ],
        ],
    ], [
        'title' => "Utility",
        "icon" => "ri-tools-line",
        "visible" => $userObj->hasPermission('manage-utility'),
        'children' => [
            [
                'title' => "Localization",
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('manage-localization'),
                'children' => [
                    [
                        'title' => "Country",
                        "url" => "country.php",
                        "icon" => "",
                        "active" => "country",
                        "visible" => $userObj->hasPermission('view-country'),
                    ],
                    [
                        'title' => "State",
                        "url" => "state.php",
                        "icon" => "",
                        "active" => "state",
                        "visible" => $userObj->hasPermission('view-state'),
                    ],
                    [
                        'title' => "City",
                        "url" => "city.php",
                        "icon" => "",
                        "active" => "city",
                        "visible" => $userObj->hasPermission('view-city') && ($SHOW_CITY_FIELD=='Yes'),
                    ],
                ],
            ], [
                'title' => "Pages",
                "url" => "page.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "page",
                "visible" => $userObj->hasPermission('view-pages'),
            ], [
                'title' => "Edit Home Page",
                "url" => "homecontent.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE == 'Ride-Delivery-UberX') && $leftdeliverallxthemeon!=1,
            ], [
                'title' => "Edit Home Page",
                "url" => "home_content_deliverallx.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && $leftdeliverallxthemeon==1,
            ], [
                'title' => $langage_lbl_admin['LBL_CAR_MAKE_ADMIN'],
                "url" => "make.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Make",
                "visible" => $userObj->hasPermission('view-vehicle-make') && ($APP_TYPE != 'UberX'),
            ], [
                'title' => $langage_lbl_admin['LBL_CAR_MODEL_ADMIN'],
                "url" => "model.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Model",
                "visible" => $userObj->hasPermission('view-vehicle-model') && ($APP_TYPE != 'UberX'),
            ], [
                'title' => "Donation",
                "url" => "donation.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Donation",
                "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
            ], [
                'title' => "DeliveryAll Service Category",
                "url" => "service_category.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "service_category",
                "visible" => $userObj->hasPermission('view-service-category') && (scount($allservice_cat_data) > 1),
            ], [
                'title' => "Order Status",
                "url" => "order_status.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "order_status",
                "visible" => $userObj->hasPermission('view-order-status'),
            ], [
                'title' => "News",
                "url" => "news.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "news",
                "visible" => $userObj->hasPermission('view-news') && ($ENABLE_NEWS_SECTION == 'Yes'),
            ], [
                'title' => "Newsletter Subscribers",
                "url" => "newsletter.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "newsletters-subscribers",
                "visible" => $userObj->hasPermission('manage-newsletter') && ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == 'Yes'),
            ], [
                'title' => "Faq",
                "url" => "faq.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Faq",
                "visible" => $userObj->hasPermission('view-faq'),
            ], [
                'title' => "Faq Categories",
                "url" => "faq_categories.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "faq_categories",
                "visible" => $userObj->hasPermission('view-faq-categories'),
            ], [
                'title' => "Help Topics",
                "url" => "help_detail.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "help_detail",
                "visible" => $userObj->hasPermission('view-help-detail'),
            ], [
                'title' => "Help Topic Categories",
                "url" => "help_detail_categories.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "help_detail_categories",
                "visible" => $userObj->hasPermission('view-help-detail-category'),
            ], [
                'title' => "Cancel Reason",
                "url" => "cancellation_reason.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "cancel_reason",
                "visible" => $userObj->hasPermission('view-cancel-reasons'),
            ], [
                'title' => $langage_lbl_admin['LBL_PACKAGE_TYPE_ADMIN'],
                "url" => "package_type.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Package",
                "visible" => $userObj->hasPermission('view-package-type') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && ONLYDELIVERALL != "Yes" && $leftdeliveryEnable == "Yes", // ONLYDELIVERALL != "Yes Added By HJ On 05-07-2019 As Per Discuss With KS Sir
            ],
            [
                'title' => "Popular " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
                "url" => "service_provider.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "service_provider",
                "visible" => $userObj->hasPermission('view-popular-stores') && $enableServiceProvicer == 1,
            ], [
                'title' => "Send Push-Notification",
                "url" => "send_notifications.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Push Notification",
                "visible" => $userObj->hasPermission('manage-send-push-notification'),
            ], [
                'title' => "DB Backup",
                "url" => "backup.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Back-up",
                "visible" => $userObj->hasPermission('view-db-backup'),
            ],
            [
                "title" => "System Diagnostic",
                "url" => "system_diagnostic.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "site",
                "visible" => !($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live"),
            ],
            [
                'title' => 'App Launch Info',
                'url' => "app_launch_info.php",
                "icon" => 'ri-checkbox-blank-circle-line',
                "active" => "app_launch_info",
                "visible" => $userObj->hasPermission('manage-app-launch-info'),
            ],
        ],
    ], [
        'title' => "Manage Ride Profiles",
        "icon" => "ri-briefcase-line",
        "visible" => $userObj->hasPermission('manage-ride-profiles') && $MODULES_OBJ->isOrganizationModuleEnable() && $leftrideEnable == "Yes",
        'children' => [
            [
                'title' => "Organization",
                "url" => "organization.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Organization",
                "visible" => $userObj->hasPermission('view-organization'),
            ], [
                'title' => "Ride Profile Type",
                "url" => "user_profile_master.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "RideProfileType",
                "visible" => $userObj->hasPermission('view-user-profile'),
            ], [
                'title' => "Business Trip Reason",
                "url" => "trip_reason.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "BusinessTripReason",
                "visible" => $userObj->hasPermission('view-trip-reason'),
            ], [
                'title' => "Organization Payment Report",
                "url" => "org_payment_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "OrganizationPaymentReport",
                "visible" => $userObj->hasPermission('manage-organization-payment-report'),
            ]
        ],
    ], [
        'title' => "Logout",
        "url" => "logout.php",
        "icon" => 'ri-logout-box-r-line',
    ],
];
return $menu;
