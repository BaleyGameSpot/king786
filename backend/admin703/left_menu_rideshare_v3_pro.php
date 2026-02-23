<?php
$adminUsersTxt = $langage_lbl_admin['LBL_ADMINISTRATOR_TXT'];

//Added By HJ On 16-06-2020 For Custome App Type CubejekX-Deliverall As Per Dicsuss With KS Start
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem();
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
// $deliverallModule = strtoupper(DELIVERALL);
$deliverallModule = $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') ? "YES" : "NO";
if ($cubeDeliverallOnly > 0) {
    $onlyDeliverallModule = "YES";
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
        'title'   => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url'     => $LOCATION_FILE_ARRAY['RIDER.PHP'],
        "icon"    => "ri-team-line",
        "active"  => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ],


    /*[
        'title'   => 'Manage '.$langage_lbl_admin['LBL_RIDE_SHARE_TXT'],
        "url"     => "master_service_category_action.php?id=" . $MCategory['RideShare'],
        "icon"    => "ri-checkbox-blank-circle-line",
        "active"  => "mVehicleCategory_RideShare",
        "visible" => $userObj->hasPermission('view-service-content-rideshare'),
    ],*/
    [
        'title'   => "Published Rides",
        'url'     => "published_rides.php",
        "icon"    => "ri-car-washing-line",
        "active"  => "PublishedRides",
        "visible" => $userObj->hasPermission('view-published-rides-rideshare')
    ],
    [
        'title'   => "Bookings",
        'url'     => "ride_share_bookings.php",
        "icon"    => "ri-file-list-line",
        "active"  => "RideShareBookings",
        "visible" => $userObj->hasPermission('view-booking-rideshare')
    ],
    [
        'title'   => "Reviews",
        'url'     => "ride_share_reviews.php",
        "icon"    => "ri-user-voice-line",
        "active"  => "ride-share-review",
        "visible" => $userObj->hasPermission('view-booking-rideshare')
    ],
    [
        'title'   => "Payment Report",
        'url'     => "ride_share_payment_report.php",
        "icon"    => "ri-numbers-line",
        "active"  => "RideSharePaymentReport",
        "visible" => $userObj->hasPermission('view-payment-report-rideshare')
    ],
    [
        'title'   => "Travel Preferences",
        "icon"    => "ri-user-location-line",
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

    [
        'title'    => "Manage Locations",
        "icon"     => "ri-map-pin-line",
        "visible"  => $userObj->hasPermission(['view-country', 'view-state', 'view-city']),
        'children' => [
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
        'title'    => "Reports",
        "icon"     => "ri-numbers-line",
        "visible"  => $userObj->hasPermission([ 'view-user-outstanding-report', 'manage-referral-report', 'manage-user-wallet-report', 'view-blocked-rider']),
        'children' => [

            /*  [
                'title'    => "User Outstanding Report",
                 "url"     => "outstanding_report.php",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission('view-user-outstanding-report'),
                "active"  => "outstanding_report",
            ],
            [
                'title'   => 'Alert For ' . $langage_lbl_admin['LBL_RIDER'],
                'url'     => $LOCATION_FILE_ARRAY['BLOCKED_RIDER'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "blockedrider",
                "visible" => $userObj->hasPermission('view-blocked-rider'),
            ],*/
            [
                'title'   => "MLM Referral Report",
                "url"     => "referrer.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "referrer",
                "visible" => ($userObj->hasPermission('manage-referral-report') && strtoupper($MLM_FEATURE) == 'YES'),
            ],
            [
                'title'   => "Wallet Report",
                "url"     => "wallet_report.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "Wallet Report",
                "visible" => ($userObj->hasPermission('manage-user-wallet-report') && strtoupper($WALLET_ENABLE) == 'YES'),
            ]
        ],
    ],



    [
        'title'    => "Support Requests",
        "icon"     => "ri-customer-service-2-line",
        "visible"  => $userObj->hasPermission(['view-contactus-report', 'view-sos-request-report']),
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
            ]
        ]
    ],

    [
        'title'    => "Marketing Tools",
        "icon"     => "ri-pages-line",
        "visible"  => $userObj->hasPermission(['view-referral-settings', 'view-advertise-banner', 'view-giftcard', 'view-giftcard-image', 'view-news', 'manage-newsletter', 'manage-send-push-notification']),
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
        "visible"  => $userObj->hasPermission(['manage-home-page-content', 'manage-passenger-app-launch-info', 'view-general-label', 'view-email-templates', 'view-sms-templates', 'view-cancel-reasons', 'view-faq-categories', 'view-faq', 'view-help-detail', 'view-help-detail-category', 'manage-general-settings', 'manage-currency', 'manage-language', 'view-seo-setting', 'view-documents', 'expired-documents']),
        'children' => [
            /*--------------------- home page ------------------*/

            [
                'title'   => "Manage Web Home Page",
                "url"     => "homepage_content.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "homecontent",
                "visible" => $userObj->hasPermission('manage-home-page-content'),
            ],
            /*--------------------- home page ------------------*/
            [
                'title'  => 'Mange App Intro Screen',
                'url'    => $LOCATION_FILE_ARRAY['APP_LAUNCH_INFO_PASSENGER'],
                "icon"   => 'ri-checkbox-blank-circle-line',
                 "active" => "app_launch_info_Passenger",
                        "visible" => $userObj->hasPermission('manage-passenger-app-launch-info') && $onlyDeliverallModule == "NO"
            ],
            /*--------------------- languages ------------------*/
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
                "url"     => "faq.php",
                "icon"     => "ri-checkbox-blank-circle-line",
                "active"  => "Faq",
                "visible"  => $userObj->hasPermission('view-faq'),
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
            [
                'title'   => "Other Pages",
                "url"     => "page.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "page",
                "visible" => $userObj->hasPermission('view-pages'),
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
            /*[
                'title'   => "Maps API Settings",
                "url"     => "map_api_setting.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "map_api_setting",
                "visible" => $userObj->hasPermission('view-map-api-service-account') && $MODULES_OBJ->mapAPIreplacementAvailable() == true && strtoupper(SITE_TYPE) == "LIVE",
            ],*/
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