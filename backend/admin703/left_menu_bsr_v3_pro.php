<?php
$adminUsersTxt = $langage_lbl_admin['LBL_ADMINISTRATOR_TXT'];

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

    [
        'title'    => 'Buy,Sell & Rent Properties',
        "icon"     => "ri-community-line",
        "visible"  => $userObj->hasPermission(['view-service-content-rentestate', 'view-service-category-rentestate', 'view-pending-rentestate', 'view-approved-rentestate', 'view-all-rentestate', 'manage-rentestate-fields', 'view-payment-plan-rentestate', 'report-rentestate', 'view-banner-rentestate']),
        "active"   => "RentEstate",
        'children' => [
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RentEstate'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RentEstate",
                "visible" => $userObj->hasPermission('view-service-content-rentestate'),
            ],
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
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RentCars'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RentCars",
                "visible" => $userObj->hasPermission('view-service-content-rentcars'),
            ],
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
            [
                'title'   => $langage_lbl_admin['LBL_MANAGE_SERVICE_CONTENT_ADMIN_TXT'],
                "url"     => "master_service_category_action.php?id=" . $MCategory['RentItem'],
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "mVehicleCategory_RentItem",
                "visible" => $userObj->hasPermission('view-service-content-rentitem'),
            ],
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
        "visible"  => $userObj->hasPermission(['manage-referral-report', 'manage-user-wallet-report', 'view-blocked-rider']),
        'children' => [
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
            ],

        ],
    ],
    [
        'title'   => "Contact Us Form Requests",
        "url"     => "contactus.php",
        "icon"    => "ri-customer-service-2-line",
        "active"  => "contactus",
        "visible" => $userObj->hasPermission('view-contactus-report'),
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
                'title'    => "User App Home Screen",
                "icon"     => "ri-checkbox-blank-circle-line",
                "visible"  => $userObj->hasPermission(['manage-app-home-screen-view', 'view-app-home-screen-banner']),
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
                'title'  => 'Manage App Intro Screen',
                'url'    => $LOCATION_FILE_ARRAY['APP_LAUNCH_INFO_PASSENGER'],
                "icon"   => 'ri-checkbox-blank-circle-line',
                "active" => "app_launch_info_Passenger",
                "visible" => $userObj->hasPermission('manage-passenger-app-launch-info')
            ],

            /*--------------------- languages ------------------*/

            [
                'title'   => "Manage Language Labels",
                "url"     => "languages.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "language_label",
                "visible" => $userObj->hasPermission('view-general-label'),
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
    /*        [
                'title'   => "Cancel Reason",
                "url"     => "cancellation_reason.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "cancel_reason",
                "visible" => $userObj->hasPermission('view-cancel-reasons'),
            ],*/
            [
                'title'    => "FAQs",
                "url"     => "faq.php",
                "icon"     => "ri-checkbox-blank-circle-line",
                        "active"  => "Faq",
                        "visible" => $userObj->hasPermission('view-faq'),
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
        "visible"  => $userObj->hasPermission(['manage-general-settings', 'manage-currency', 'manage-language', 'view-seo-setting', 'view-map-api-service-account']),
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
            [
                'title'   => "Maps API Settings",
                "url"     => "map_api_setting.php",
                "icon"    => "ri-checkbox-blank-circle-line",
                "active"  => "map_api_setting",
                "visible" => $userObj->hasPermission('view-map-api-service-account') && $MODULES_OBJ->mapAPIreplacementAvailable() == true && strtoupper(SITE_TYPE) == "LIVE",
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