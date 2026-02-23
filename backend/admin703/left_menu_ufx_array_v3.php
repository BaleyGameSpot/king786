<?php

//added by SP as discussed with bmam on 28-6-2019
$adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'];
if ($PACKAGE_TYPE == 'SHARK' && ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL == 'No') {
    $adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'] . '/Hotel';
}

$menu = [
    [
        'title' => 'Dashboard',
        'url' => "dashboard.php",
        "icon" => 'ri-dashboard-line',
        "active" => "dashboard",
        "visible" => true,
    ], [
        'title' => 'Server Monitoring',
        'url' => "server_admin_dashboard.php",
        "icon" => 'ri-bar-chart-box-line',
        "active" => "server_dashboard",
        "visible" => ($userObj->hasPermission('manage-server-admin-dashboard') && $MODULES_OBJ->isEnableAppHomeScreenLayoutV2()),
    ], [
        'title' => 'Admin',
        'url' => "javascript:",
        "icon" => "ri-admin-line",
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
            ]
        ],
    ], [
        'title' => 'Company',
        'url' => "company.php",
        "icon" => "ri-building-4-line",
        "active" => "Company",
        "visible" => $userObj->hasPermission('view-company'),
    ], [
        'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
        'url' => "driver.php",
        "icon" => "ri-user-2-line",
        "active" => "Driver",
        "visible" => $userObj->hasPermission('view-providers'),
    ], [
        'title' => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url' => "rider.php",
        "icon" => "ri-team-line",
        "active" => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ], [
        'title' => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION'],
        'url' => "javascript:",
        "icon" => "ri-price-tag-2-line",
        "visible" => (($userObj->hasPermission('manage-driver-subscription') || $userObj->hasPermission('manage-driver-subscription-report')) && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && ONLYDELIVERALL != "Yes"),
        'children' => [
            [
                'title' => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'],
                'url' => "driver_subscription.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "DriverSubscriptionPlan",
                "visible" => ($userObj->hasPermission('manage-driver-subscription') && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && ONLYDELIVERALL != "Yes"),
            ],
            [
                'title' => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'],
                'url' => "driver_subscription_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "DriverSubscriptionReport",
                "visible" => ($userObj->hasPermission('manage-driver-subscription-report') && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && ONLYDELIVERALL != "Yes"),
            ],
        ]
    ], [
        'title' => "Manage Bidding",
        "icon" => "ri-auction-line",
        "visible" => $userObj->hasPermission('view-bidding-category') && $MODULES_OBJ->isEnableBiddingServices(),
        'children' => [
            [
                'title' => 'Bidding Services',
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-category'),
                "active" => "bidding",
                "url" => "bidding_master_category.php",
                
            ], [
                'title' => 'Bidding Requests',
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('view-bidding-category'),
                "active" => "biddingDriverRequest",
                "url" => "bidding_driver_request.php",
            ], [
                'title' => 'Bidding Report',
                "icon" => "ri-checkbox-blank-circle-line",
                "visible" => $userObj->hasPermission('manage-bids-trip'),
                "active" => "Bids",
                "url" => "bidding_report.php",
            ],
        ],
    ], [
        'title' => "Manage Services",
        "icon" => "ri-list-settings-line",
        "visible" => ($userObj->hasPermission('manage-services') && $APP_TYPE != 'Ride-Delivery-UberX'),
        'children' => [
            [
                'title' => 'Service Category',
                "url" => "vehicle_category.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "VehicleCategory",
                "visible" => $userObj->hasPermission('view-provider-taxis'),
            ], [
                'title' => "Service Type",
                "url" => "service_type.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "ServiceType",
                "visible" => $userObj->hasPermission('view-provider-taxis'),
            ], [
                'title' => "App Main Screen Settings",
                'url' => "app_home_settings.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "App Main Screen Settings",
                "visible" => ($userObj->hasPermission('manage-app-main-screen-settings') && $APP_TYPE == 'Ride-Delivery-UberX'),
            ], [
                'title' => $langage_lbl_admin['LBL_DRIVER_SERVICE_REQUESTS_TXT'],
                "url" => "driver_service_request.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "DriverRequest",
                "visible" => $userObj->hasPermission('view-driver-service-request') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == "UberX"),
            ],
        ],
    ], [
        'title' => "Manual Booking",
        'url' => "add_booking.php",
        "icon" => "ri-book-2-line",
        "active" => "booking",
        "visible" => ($userObj->hasPermission('manage-manual-booking')),
        "target" => "blank",
    ], [
        'title' => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
        'url' => "trip.php",
        "icon" => "ri-file-list-line",
        "active" => "Trips",
        "visible" => $userObj->hasPermission('manage-trip-jobs'),
    ], [
        'title' => $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'],
        'url' => "cab_booking.php",
        "icon" => "ri-file-list-line",
        "active" => "CabBooking",
        "visible" => ($userObj->hasPermission('manage-ride-job-later-bookings') && $RIDE_LATER_BOOKING_ENABLED == 'Yes'),
    ], [
        'title' => "PromoCode",
        "url" => "coupon.php",
        "icon" => "ri-coupon-line",
        "active" => "Coupon",
        "visible" => $userObj->hasPermission('view-promocode'),
    ], [
        'title' => "God's View",
        "url" => "map_godsview.php",
        "icon" => "ri-road-map-line",
        "active" => "LiveMap",
        "visible" => $userObj->hasPermission('manage-gods-view'),
    ], [
        'title' => "Heat View",
        "url" => "heatmap.php",
        "icon" => "ri-treasure-map-line",
        "active" => "Heat Map",
        "visible" => $userObj->hasPermission('manage-heat-view'),
    ], [
        'title' => "Reviews",
        "url" => "review.php",
        "icon" => "ri-user-voice-line",
        "active" => "Review",
        "visible" => $userObj->hasPermission('manage-reviews'),
    ], [
        'title' => "Advertisement Banners",
        'url' => "advertise_banners.php",
        "icon" => "ri-advertisement-line",
        "active" => "Advertisement Banners",
        "visible" => $userObj->hasPermission('view-advertise-banner') && ($ADVERTISEMENT_TYPE != 'Disable' && $PACKAGE_TYPE == 'SHARK'),
    ], [
        'title' => 'Decline/Cancelled Alert',
        'url' => "blocked_driver.php",
        "icon" => "ri-user-unfollow-line",
        "active" => "blockeddriver",
        "visible" => $userObj->hasPermission('view-blocked-driver')&& $PACKAGE_TYPE == 'SHARK' && strtoupper(ONLYDELIVERALL) != "YES", //This Module Enable For Shark Package As Per Discss With KS Sir By HJ On 05-11-2019
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
        'title' => "Reports",
        "icon" => "ri-numbers-line",
        "visible" => $userObj->hasPermission('manage-report'),
        'children' => [
            [
                'title' => "Payment Report",
                "url" => "payment_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Payment_Report",
                "visible" => $userObj->hasPermission('manage-payment-report'),
            ], [
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
                'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Payment Report",
                "url" => "driver_pay_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Driver Payment Report",
                "visible" => $userObj->hasPermission('manage-provider-payment-report'),
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
        "icon" => "ri-customer-service-2-line",
        "visible" => ($userObj->hasPermission('view-contactus-report') || $userObj->hasPermission('view-sos-request-report') || $userObj->hasPermission('view-trip-job-help-request-report') || $userObj->hasPermission('view-payment-request-report') || $userObj->hasPermission('view-withdraw-request-report')),
        'children' => [
            [
                'title' => "Contact Us Form Requests",
                "url" => "contactus.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "contactus",
                "visible" => $userObj->hasPermission('view-contactus-report'),
            ], [
                'title' => "SOS Requests",
                "url" => "emergency_contact_data.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "emergency_contact_data",
                "visible" => $userObj->hasPermission('view-sos-request-report'),
            ], [
                'title' => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']." Help Requests",
                "url" => "trip_help_details.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "trip_help_details",
                "visible" => $userObj->hasPermission('view-trip-job-help-request-report'),
            ],[
                'title' => "Payment Requests",
                "url" => "payment_requests_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "payment_requests",
                "visible" => $userObj->hasPermission('view-payment-request-report'),
            ],[
                'title' => "Withdraw Requests",
                "url" => "withdraw_requests_report.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "withdraw_requests",
                "visible" => $userObj->hasPermission('view-withdraw-request-report'),
            ]
        ]
    ], [
        'title' => "Manage Locations",
        "icon" => "ri-map-pin-line",
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
            ]
        ],
    ], [
        'title' => "Settings",
        "icon" => "ri-settings-5-line",
        "visible" => $userObj->hasPermission('manage-settings'),
        'children' => [
            [
                'title' => "General",
                "url" => "general.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "General",
                "visible" => $userObj->hasPermission('manage-general-settings'),
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
                "active" => "Email Templates",
                "visible" => $userObj->hasPermission('view-email-templates'),
            ],
            [
                'title' => "SMS Templates",
                "url" => "sms_template.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "SMS Templates",
                "visible" => $userObj->hasPermission('view-sms-templates'),
            ],
            [
                'title' => "Manage Documents",
                "url" => "document_master_list.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Manage Documents",
                "visible" => $userObj->hasPermission('view-documents'),
            ], [
                'title' => "Language Label",
                "url" => "languages.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "language_label",
                "visible" => $userObj->hasPermission('manage-language-label'),
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
            ],
            [
                'title' => "Maps API Settings",
                "url" => "map_api_setting.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "map_api_setting",
                "visible" => $userObj->hasPermission('view-map-api-setting') && $MODULES_OBJ->mapAPIreplacementAvailable() && strtoupper(SITE_TYPE) == "LIVE",
            ],
            [
                'title' => "Banner",
                "url" => "banner.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Banner",
                "visible" => ($userObj->hasPermission('view-banner') && ($APP_TYPE == 'UberX' || $leftcubejekxthemeon == 1 || $leftcubexthemeon == 1 || $leftdeliverykingthemeon == 1)),
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
                        "visible" => $userObj->hasPermission('view-city') && ($SHOW_CITY_FIELD == 'Yes'),
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
                "url" => "home_content.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "home_content",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE != 'Ride-Delivery-UberX') && $leftservicexthemeon != 1,
            ],
            [
                'title' => "Edit Home Page",
                "url" => "home_content_servicex.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "home_content_service",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE != 'Ride-Delivery-UberX') && $leftservicexthemeon == 1,
            ],
            [
                'title' => "Edit Other Service Page",
                "url" => "home_content_otherservices.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "homecontentotherservices",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE != 'Ride-Delivery-UberX') && $parent_ufx_catid == 0,
            ],
            [
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
                'title' => "Donation",
                "url" => "donation.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Donation",
                "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
            ],
            [
                'title' => $langage_lbl_admin['LBL_PACKAGE_TYPE_ADMIN'],
                "url" => "package_type.php",
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "Package",
                "visible" => $userObj->hasPermission('view-package-type') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && ONLYDELIVERALL != "Yes" && $leftdeliveryEnable == "Yes", // ONLYDELIVERALL != "Yes Added By HJ On 05-07-2019 As Per Discuss With KS Sir
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
                "icon" => "ri-checkbox-blank-circle-line",
                "active" => "app_launch_info",
                "visible" => $userObj->hasPermission('manage-app-launch-info'),
            ],
        ],
    ], [
        'title' => "Logout",
        "url" => "logout.php",
        "icon" => "ri-logout-box-r-line",
    ]
];
return $menu;
