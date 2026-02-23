<?php
include_once 'common.php';

$AUTH_OBJ->AuthMemberRedirect();

if (isset($_SESSION['sess_signin']) && $_SESSION['sess_signin'] == 'admin') { // it is becoz when from admin comes it have sess lang en so that take it default lang..
    $_SESSION['sess_lang'] = $default_lang;
    $_SESSION['sess_signin'] = '';
    $type = $_REQUEST['type'];
    header("location: sign-in?type=" . $type);
    exit;
}

$meta_arr = $STATIC_PAGE_OBJ->FetchSeoSetting(1);
$countryList = get_value('country', 'vCountryCode,vPhoneCode,vCountry', 'eStatus', 'Active', '', '');
$defaultcountryDataArr = get_value('configurations', 'vValue', 'vName', 'DEFAULT_COUNTRY_CODE_WEB', '', '');
$sql = "SELECT c.vValue,co.vPhoneCode from configurations as c LEFT JOIN country as co on co.vCountryCode=c.vValue where vName = 'DEFAULT_COUNTRY_CODE_WEB'";
$defaultcountryDataArr = $obj->MySQLSelect($sql);
$forpsw = isset($_REQUEST['forpsw']) ? $_REQUEST['forpsw'] : '';
$forgetPWd = isset($_REQUEST['forgetPWd']) ? $_REQUEST['forgetPWd'] : '';
$depart = '';
if (isset($_REQUEST['depart'])) {
    $_SESSION['sess_depart'] = $_REQUEST['depart'];
    $depart = $_SESSION['sess_depart'];
} else {
    if (isset($_REQUEST['depart'])) {
        unset($_SESSION['sess_depart']);
    }
}
//$_SESSION['sess_lang'] = 'EN';
$err_msg = "";
if (isset($_SESSION['sess_error_social'])) {
    $err_msg = $_SESSION['sess_error_social'];
    unset($_SESSION['sess_error_social']);
    unset($_SESSION['fb_user']);   //facebook
    unset($_SESSION['oauth_token']);  //twitter
    unset($_SESSION['oauth_token_secret']); //twitter
    unset($_SESSION['access_token']);  //google
}
$rider_email = $driver_email = $company_email = '';
//$host_system = 'doctor4';
if ($host_system == "carwash") {
    $rider_note = "If you have registered as a new user, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
} elseif ($host_system == "beautician" || $host_system == "beautician4" || $host_system == "carwash4" || $host_system == "dogwalking4" || $host_system == "towtruck4" || $host_system == "massage4" || $host_system == "ufxforall4" || (!empty($domain) && $domain == "cubejek")) {
    $rider_note = "If you have registered as a new user, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
} elseif ($host_system == "tutors") {
    $rider_note = "If you have registered as a new student, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
} elseif ($host_system == "doctor4") {
    $rider_note = "If you have registered as a new patient, use your registered Email Id and Password to view the detail of your Appointment.<br />To view the Standard Features of the Apps use below access detail";
} else {
    $rider_note = "If you have registered as a new Rider, use your registered Email Id and Password to view the detail of your Rides.<br />To view the Standard Features of the Apps use below access detail";
}

$rider_email = $driver_email = $company_email = $driver_note = $rider_note = $pwd = '';
$loginblockfooter = 0;

$db_forgot = $STATIC_PAGE_OBJ->FetchStaticPage(49, $_SESSION['sess_lang']);
if (empty($db_forgot['page_title'])) {
    $db_forgot = $STATIC_PAGE_OBJ->FetchStaticPage(49, 'EN');
}
$forgetpage_desc = $db_forgot[0]["tPageDesc_" . $_SESSION['sess_lang']];

$bg_img = "login-bg.jpg";
$left_img = "login-img.jpg";
if (!empty($db_forgot['vImage1'])) {
    $bg_img = $db_forgot['vImage1'];
}
if (!empty($db_forgot['vImage'])) {
    $left_img = $db_forgot['vImage'];
}
//if(empty($template)) $template = 'Cubex';
$bg_forgot_image = "assets/img/page/" . $bg_img;
$db_forgot_src = "assets/img/page/" . $left_img;
$bg_login_image = "assets/img/page/login-bg.jpg";
$db_login_src = "assets/img/page/login-img.jpg";

$db_signin = $STATIC_PAGE_OBJ->FetchStaticPage(48, $_SESSION['sess_lang']);
$pagesubtitle = json_decode($db_signin[0]['pageSubtitle'], true);
$pagesubtitle_lang = $pagesubtitle["pageSubtitle_" . $_SESSION['sess_lang']];
if (empty($pagesubtitle_lang)) {
    $db_signin = $STATIC_PAGE_OBJ->FetchStaticPage(48, 'EN');
    $pagesubtitle = json_decode($db_signin[0]['pageSubtitle'], true);
    $pagesubtitle_lang = $pagesubtitle["pageSubtitle_" . 'EN'];
}
$loginpage_title = json_decode($db_signin['page_title'], true);
$loginpage_desc = json_decode($db_signin['page_desc'], true);

if (!empty($db_signin['vImage1'])) {
    $bg_login_image = "assets/img/page/" . $db_signin['vImage1'];
}
if (!empty($db_signin['vImage'])) {
    $db_login_src = "assets/img/page/" . $db_signin['vImage'];
}

if (!empty($db_signin['vUserImage'])) $vUserImage = "assets/img/page/" . $db_signin['vUserImage'];
if (!empty($db_signin['vDriverImage'])) $vDriverImage = "assets/img/page/" . $db_signin['vDriverImage'];
if (!empty($db_signin['vCompanyImage'])) $vCompanyImage = "assets/img/page/" . $db_signin['vCompanyImage'];
if (!empty($db_signin['vStoreImage'])) $vStoreImage = "assets/img/page/" . $db_signin['vStoreImage'];
if (!empty($db_signin['vOrgImage'])) $vOrgImage = "assets/img/page/" . $db_signin['vOrgImage'];
if (!empty($db_signin['vTrackingImage'])) $vTrackingImage = "assets/img/page/" . $db_signin['vTrackingImage'];

$catdata = serviceCategories;
$service_cat_list = json_decode($catdata, true);
foreach ($service_cat_list as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$become_restaurant = '';
// if(strtoupper(DELIVERALL) == "YES") {
$company_register_count = 1;
if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    if (scount($iServiceIdArr) == 1) {
        if($iServiceIdArr[0] == 1) {
            $become_restaurant = $langage_lbl['LBL_RESTAURANT_TXT'];
        } else {
            $become_restaurant = $langage_lbl['LBL_STORE'];
        }
        $restaurantName = $langage_lbl['LBL_SIGNUP_STORE_NAME'];
    } else {
        $become_restaurant = $langage_lbl['LBL_RESTAURANT_GROCERY_ETC_STORE_TXT'];
        $restaurantName = $langage_lbl['LBL_STORE_NAME'];
    }
    //   $company_register_count += 1;
}
if ($MODULES_OBJ->isOrganizationModuleEnable()) {
    $company_register_count += 1;
}
if ($MODULES_OBJ->isEnableTrackServiceFeature()) {
    $company_register_count += 1;
}
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel();
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem(); // Added By HJ On 16-06-2020 For Custome Setup CubejekX Deliverall
$SITEPATH = $tconfig['tsite_url'];
$msite = !empty($MODULES_OBJ->isEnableMsiteFacility()) ? 'Yes' : 'No';
$msiteVar = 'No';
if ($msite == 'Yes') {
    $msiteVar = isset($_SESSION['is_msite']) ? $_SESSION['is_msite'] : 'No';
}
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?php echo $meta_arr['meta_title']; ?></title>
    <meta name="keywords" content="<?= $meta_arr['meta_keyword']; ?>"/>
    <meta name="description" content="<?= $meta_arr['meta_desc']; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <script type="text/javascript" src="<?php echo $SITEPATH; ?>assets/js/add_country_code_dropdown.js?<?php echo time(); ?>"></script>
    <!-- End: Default Top Script and css-->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-social.css">
    <style type="text/css">
        .inputfield {
            width: 100%;
            display: flex;
            justify-content: space-around;
        }
        .input {
            height: 4em;
            width: 4em !important;
            border: 2px solid #dad9df;
            outline: none;
            text-align: center;
            font-size: 1.5em;
            border-radius: 0.3em;
            background-color: #ffffff;
            outline: none;
            /*Hide number field arrows*/
            -moz-appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .input:disabled {
            color: #89888b;
        }
        .input:focus {
            border: 3px solid #ffb800;
        }
    </style>
    <script>
        function floatingStatus()
        {
            var GOON = 1;
            var current_active_tab = $('.login-tab-switch li.active').attr('data-id');
            if(current_active_tab !== 'undefined' && SIGN_IN_OPTION == "OTP" && jQuery.inArray(current_active_tab, ['hotel','company','company_accounts','organization']) === -1){
                $(this).closest('.form-group').addClass('floating');
                GOON = 0;
            }
            
            return GOON;
        }
    </script>
</head>
<body id="wrapper">
<!-- home page -->
<!-- home page -->
<?php if ($template != 'taxishark') { ?>
<div id="main-uber-page">
    <?php } ?>
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- First Section -->
    <?php include_once("top/header.php"); ?>
    <!-- End: First Section -->
        
    <?php if($onlyRideShareEnable == 'Yes' || $onlyBSREnable == 'Yes'){
        
        include_once("cx-sign-in-only-user.php");

    } else { ?>
   
        <div class="login-block-heading login-newblock">
            <div class="login-block-heading-inner">
                <label id="loginlabel" class="loginlabel"><?= $langage_lbl['LBL_LOGIN'] ?></label>
                <label id="forgotlabel" style="display:none"><?= $db_forgot['page_title']; ?></label>
                <div class="tabholder login-tabholder">
                    <ul class="tab-switch login-tab-switch">
                        <li <?php if ((isset($_REQUEST['type']) && ($_REQUEST['type'] == 'user' || $_REQUEST['type'] == 'rider' || $_REQUEST['type'] == 'sender' || $_REQUEST['type'] == '')) || empty($_REQUEST['type'])) { ?>class="active" <?php } ?>
                            data-id="user" data-desc="<?= $loginpage_title['user_pages'] ?>">
                            <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_RIDER'] ?></a>
                        </li>
                        <? if ($msiteVar == 'No') { ?>
                            <li <?php if (isset($_REQUEST['type']) && ($_REQUEST['type'] == 'provider' || $_REQUEST['type'] == 'driver' || $_REQUEST['type'] == 'carrier')) { ?>class="active" <?php } ?>
                                data-id="provider" data-desc="<?= $loginpage_title['provider_pages'] ?>">
                                <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_DRIVER'] ?></a>
                            </li>
                            <?php if (
                                in_array($APP_TYPE, [
                                    'Delivery',
                                    'UberX'
                                ]) || $company_register_count == 1
                            ) {
                                if(strtoupper(ONLYDELIVERALL) != "YES"){
                                    ?>
                                    <li data-id="company" data-desc="<?= $regpage_title['company_pages'] ?>">
                                        <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_COMPANY_SIGNIN'] ?></a>
                                    </li>
                                <?php }
                            } ?>
                            <? if (
                                strtoupper(ONLYDELIVERALL) != "YES" && $cubeDeliverallOnly == false && !in_array($APP_TYPE, [
                                    'Delivery',
                                    'UberX'
                                ]) && $company_register_count > 1
                            ) { ?>
                                <li <?php if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'company_accounts') { ?>class="active" <?php } ?>
                                    data-id="company_accounts">
                                    <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_COMPANY_SIGNIN'] ?></a>
                                </li>
                            <? }
                            if (!empty($become_restaurant) && $cubeDeliverallOnly) { ?>
                                <!--  <li <?php /*if (isset($_REQUEST['type']) && ($_REQUEST['type'] == 'restaurant' || $_REQUEST['type'] == 'store')) { */?>class="active" <?php /*} */?>
                                    data-id="restaurant" data-desc="<?/*= $loginpage_title['restaurant_pages'] */?>">
                                    <a href="JavaScript:void(0);"><?/*= $become_restaurant */?></a>
                                </li>-->
                            <? }
                            if ($hotelPanel > 0) { ?>
                            <li <?php if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'hotel') { ?>class="active" <?php } ?>
                                data-id="hotel" data-desc="<?= $loginpage_title['hotel_pages'] ?>">
                                <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_HOTEL_LOGIN'] ?></a></li><?php }
                        } ?>
                        <?php if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) { ?>
                            <li <?php if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'store') { ?>class="active" <?php } ?>
                                data-id="restaurant">
                                <a href="JavaScript:void(0);"><?= $become_restaurant ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="login-main parallax-window">
            <div class="login-inner">
                <div class="login-block">
                    <div class="company-register-container">
                        <div class="company-register-title"><?= $langage_lbl['LBL_FIND_RIGHT_ACCOUNT_FOR_YOUR_NEED'] ?></div>
                        <div class="company-register-subtitle"><?= $langage_lbl['LBL_SELECT_OPTION_THAT_DESCRIBES_YOU_THE_BEST'] ?></div>
                        <div class="company-register-section">
                            <a href="javascript:void(0);" data-desc="<?= $loginpage_title['company_pages'] ?>"
                               id-attr="company" class="company-register-block"
                               style="width: calc(100% / <?= $company_register_count ?>);">
                                <div class="company-register-card">
                                    <img src="<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/page/<?= $db_signin[0]['vSignImage1']; ?>">
                                    <!--<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/company-register.png-->
                                    <div class="company-register-content">
                                        <div class="company-reg-title"><?= $langage_lbl['LBL_SERVICE_PROVIDER_COMPANY_TXT'] ?></div>
                                    </div>
                                </div>
                            </a>


                            <?php if ($MODULES_OBJ->isOrganizationModuleEnable()) { ?>
                                <a href="javascript:void(0);" data-desc="<?= $loginpage_title['org_pages'] ?>"
                                   id-attr="organization" class="company-register-block"
                                   style="width: calc(100% / <?= $company_register_count ?>);">
                                    <div class="company-register-card">
                                        <img src="<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/page/<?= $db_signin[0]['vSignImage2']; ?>">
                                        <!--<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/organization-register.png-->
                                        <div class="company-register-content">
                                            <div class="company-reg-title"><?= $langage_lbl['LBL_CORPORATE_ORGANIZATION'] ?></div>
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if ($MODULES_OBJ->isEnableTrackServiceFeature()) { ?>
                                <a href="javascript:void(0);" data-desc="<?= $loginpage_title['trackservice_pages'] ?>"
                                   id-attr="tracking_company" class="company-register-block"
                                   style="width: calc(100% / <?= $company_register_count ?>);">
                                    <div class="company-register-card">
                                        <img src="<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/page/tracking-company-register.png">
                                        <div class="company-register-content">
                                            <div class="company-reg-title"><?= $langage_lbl['LBL_TRACKING_COMPANY'] ?></div>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="login-left">
                        
                        <div class="setImageRatioJS login-caption active" id="user">
                            <?php if (!empty($vUserImage)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $vUserImage; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['user_pages']; ?>
                        </div>
                        <div class="setImageRatioJS login-caption" id="provider">
                            <?php if (!empty($vDriverImage)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $vDriverImage; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['provider_pages']; ?>
                        </div>
                        <div class="setImageRatioJS login-caption" id="company">
                            <?php if (!empty($vCompanyImage)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $vCompanyImage; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['company_pages']; ?>
                        </div>
                        <div class="setImageRatioJS login-caption" id="restaurant">
                            <?php if (!empty($vStoreImage)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $vStoreImage; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['restaurant_pages']; ?>
                        </div>
                        <div class="setImageRatioJS login-caption" id="organization">
                            <?php if (!empty($vOrgImage)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $vOrgImage; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['org_pages']; ?>
                        </div>
                        <div class="setImageRatioJS login-caption" id="tracking_company">
                            <?php if (!empty($vTrackingImage)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $vTrackingImage; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['trackservice_pages']; ?>
                        </div>
                        <div class="setImageRatioJS login-caption" id="hotel">
                            <?php if (!empty($db_signin['vImage'])) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $db_login_src; ?>" alt="">
                            <?php } ?>
                            <?= $loginpage_desc['hotel_pages']; ?>
                        </div>
                         <div class="setImageRatioJS forget_password 858585"  style="display: none;">
                            <?php if (!empty($db_login_src)) { ?>
                                <img class="resizeImageWithJs"  attr-image-src="<?php echo $db_login_src; ?>" alt="" style="border: none;">
                            <?php } ?>
                                <!-- <?= $forgetpage_desc; ?> -->
                        </div>
                    </div>
                    <div class="login-right" id="login_div">

                        <div class="form-header form-header-back-btn">
                            <h1 id="form-header-title"></h1>
                            <div class="btn-hold">
                                <input id="company-register-block-back-btn" type="button" name="SUBMIT" class="submit" value="Back">
                            </div>
                        </div>

                        <div class="login-data-inner">
                            <!-- <h1><?//= $pagesubtitle_lang ?></h1> -->
                            <!-- <p><?//= $loginpage_title['user_pages']; ?></p> -->
                            <div class="form-err">
                                <span style="display:none;" id="msg_close" class="msg_close error-login-v">&#10005;</span>
                                <p id="errmsg" style="display:none;"
                                   class="text-muted btn-block btn btn-danger btn-rect error-login-v"></p>
                                <p style="display:none;background-color: #14b368;"
                                   class="btn-block btn btn-rect btn-success error-login-v" id="success"></p>
                            </div>
                            <?php
                            if (isset($action) && $action == 'rider') {
                                $action_url = 'mytrip.php';
                            } else if (isset($action) && $action == 'driver' && $iscompany != "1") {
                                //$action_url = 'profile.php';
                                //$action_url = 'driver-profile';
                                $action_url = 'profile';
                            } else {
                                $action_url = 'dashboard.php';
                            }
                            if (!empty($_SESSION["navigatedPage"]) && ONLY_MEDICAL_SERVICE != 'Yes') {
                                $action_url = 'userbooking';
                            }
                            ?>
                            <form action="#" id="login_box" name="login_form">
                                <input type="hidden" name="action" class="action" value="rider"/>
                                <input type="hidden" name="action_url" id="action_url" value="dashboard.php"/>
                                <input type="hidden" name="iscompany" class="iscompany" value="0"/>
                                <input type="hidden" name="CompSystem" class="CompSystem" value="0"/>
                                <input type="hidden" name="type_usr" id="type_usr" value="Rider"/>
                                <input type="hidden" name="type" id="type" value="signIn"/>

                                <?php if ($SIGN_IN_OPTION == "OTP") { ?>
                                    <div id="mobile-otp-form" style="display: none;">
                                        <div class="form-group floating">
                                            <label><?= $langage_lbl['LBL_MOBILE_NUMBER_HINT_TXT'] ?></label>
                                            <input style="width: 100%" tabindex="1" type="text" name="vEmail" id="vPhoneNumber" value="" class="hotelhide phoneinput" readonly onfocus="this.removeAttribute('readonly');"/>
                                        </div>
                                    <div id="recaptcha-container-new" style="margin-bottom: 10px"></div>
                                        <div class="button-block">
                                            <div class="btn-hold">
                                                <button type="button" class="btnSubmit" id="sendOTP" data-loading-text="<?= $langage_lbl['LBL_SENDING_OTP_TXT'] ?>"><?= $langage_lbl['LBL_SEND_OTP_TXT'] ?>
                                                    <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                                </button>                                               
                                            </div>
                                            <div class="member-txt hotelhide">
                                                <?= $langage_lbl['LBL_DONT_HAVE_AN_ACCOUNT'] ?>
                                                <a href="<?= $link_user ?>" tabindex="5"
                                                   id="signinlink"><?= $langage_lbl['LBL_SIGNUP'] ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="mobile-otp-add-form" style="display: none;">
                                        <?php if(strtoupper($ENABLE_WHATSAPP_OTP_VERIFICATION) == "YES") { ?>
                                        <div class="form-header">
                                            <h1><?= $langage_lbl['LBL_MOBILE_VERIFICATION_CODE']; ?></h1>
                                            <div class="btn-hold">
                                                <input id="otp-verification-back-btn" type="button" name="SUBMIT" class="submit" value="Back">
                                            </div>
                                        </div>
                                        
                                        <?php } else { ?>
                                        <div class="form-group1">
                                            <label><?= $langage_lbl['LBL_MOBILE_VERIFICATION_CODE']; ?></label>
                                        </div>
                                        <br/>
                                        <?php } ?>
                                        <div id="otp-input-sms">
                                        <?php if (strtolower($MOBILE_NO_VERIFICATION_METHOD) == 'firebase') { ?>
                                            <div class="form-group">
                                                <input type="number" name="mobileOtp" id="mobileOtp" class="neglect">
                                            </div>
                                        <?php } else { ?>
                                            <div class="form-group OTPInput" id="OTPInput">
                                                <input type="number" maxlength="1" class="input mobileOtp neglect">
                                                <input type="number" maxlength="1" class="input mobileOtp neglect" disabled>
                                                <input type="number" maxlength="1" class="input mobileOtp neglect" disabled>
                                                <input type="number" maxlength="1" class="input mobileOtp neglect" disabled>
                                                <input type="hidden" name="mobileOtp" id="mobileOtp">
                                            </div>
                                        <?php } ?>
                                        </div>

                                        <div id="otp-input-whatsapp" style="display: none;">
                                            <div class="form-group OTPInput" id="OTPInputWhatsApp">
                                                <input type="number" maxlength="1" class="input mobileOtp neglect">
                                                <input type="number" maxlength="1" class="input mobileOtp neglect" disabled>
                                                <input type="number" maxlength="1" class="input mobileOtp neglect" disabled>
                                                <input type="number" maxlength="1" class="input mobileOtp neglect" disabled>
                                                <input type="hidden" name="mobileOtpWhatsApp" id="mobileOtpWhatsApp">
                                            </div>
                                        </div>

                                        <div class="button-block">
                                            <div class="btn-hold">
                                                <input tabindex="3" type="submit" class="btnVerify" id="verify"
                                                       value="<?= $langage_lbl['LBL_BTN_VERIFY_TXT']; ?>">
                                                <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                            </div>
                                            <div class="countdown"> <?= $langage_lbl['LBL_RESEND_OTP_SIGNIN']; ?>: <span id="countdown"></span></div>
                                            <div class="member-txt resendcode">
                                                <?= $langage_lbl['LBL_DONT_RECEIVE_CODE_TXT']; ?>
                                                <a href="#" tabindex="5" id="resendcode" onclick="sendOTP(); return false;"> <?= $langage_lbl['LBL_RESEND_OTP_TXT']; ?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div id="passwordform">
                                    <div class="form-group">
                                        <label class="hotelshow"
                                               style="display:none"><?= $langage_lbl['LBL_EMAIL']; ?></label>
                                        <label class="hotelhide"><?= $langage_lbl['LBL_EMAIL_MOBILE_NO_TXT_MSG']; ?></label>
                                        <input tabindex="1" type="text" name="vEmailh" id="vEmailh" value=""
                                               class="hotelshow" style="display:none" readonly
                                               onfocus="this.removeAttribute('readonly');"/>
                                        <input tabindex="1" type="text" name="vEmail" id="vEmail" value="" class="hotelhide"
                                               readonly onfocus="this.removeAttribute('readonly');"/>
                                    </div>
                                    <div class="mobile-info"
                                         style="margin: -8px 0 20px 0; font-size: 11px;"><?= $langage_lbl['LBL_SIGN_IN_MOBILE_EMAIL_HELPER']; ?></div>
                                    <div class="form-group">
                                        <div class="relative_ele">
                                            <label><?= $langage_lbl['LBL_PASSWORD_LBL_TXT']; ?></label>
                                            <input autocomplete="new-password" tabindex="2" type="password" name="vPassword"
                                                   id="vPassword" value="<?= (SITE_TYPE == 'Demo') ? '123456' : '' ?>"
                                                   readonly onfocus="this.removeAttribute('readonly');"/>
                                        </div>
                                        <div class="button-block end PT5">
                                        <a href="javascript:void(0)" onClick="change_heading('forgot');resetCaptcha();" tabindex="4"
                                               class="hotelhide"><?= $langage_lbl['LBL_FORGET_PASS_TXT']; ?></a>
                                        </div>
                                    </div>
                                    <div class="button-block">
                                        <div class="btn-hold">
                                            <input tabindex="3" type="submit" value="<?= $langage_lbl['LBL_LOGIN']; ?>"/
                                            onClick="chkValid();return false;">
                                            <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                        </div>
                                        <div class="member-txt hotelhide">
                                            <?= $langage_lbl['LBL_DONT_HAVE_AN_ACCOUNT'] ?>
                                            <a href="<?= $link_user ?>" tabindex="5"
                                               id="signinlink"><?= $langage_lbl['LBL_SIGNUP'] ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($DRIVER_GOOGLE_LOGIN == "Yes" || $DRIVER_FACEBOOK_LOGIN == "Yes" || $DRIVER_LINKEDIN_LOGIN == "Yes") { ?>
                                    <span id="driver-social" style="display:none">
                                        <div class="aternate-login" data-name="OR"></div>
                                        <div class="soc-login-row">
                                            <label><?= $langage_lbl['LBL_LOGIN_WITH_SOCIAL_ACC']; ?></label>
                                            <ul class="social-list">
                                                <?php if ($DRIVER_FACEBOOK_LOGIN == "Yes") { ?>
                                                    <li>
                                                        <a target="_blank" href="facebook/driver" tabindex="6"
                                                           id="facebook-button" class="btn-facebook">
                                                            <img src="assets/img/link-icon/facebook.svg" alt="Facebook"
                                                                 width="25px"><?= $langage_lbl['LBL_CONTINUE_FACEBOOK']; ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($DRIVER_LINKEDIN_LOGIN == "Yes") { ?>
                                                    <li>
                                                        <a target="_blank" href="linkedin/driver" tabindex="7"
                                                           class="btn-linkedin">
                                                            <img src="assets/img/link-icon/linkedin.svg" alt="Linkdin"
                                                                 width="25px"><?= $langage_lbl['LBL_CONTINUE_LINKEDIN']; ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($DRIVER_GOOGLE_LOGIN == "Yes") { ?>
                                                    <li>
                                                        <a href="google/driver" tabindex="8" class="btn-google1">
                                                            <img src="assets/img/link-icon/btn_google_light_normal_ios.svg"
                                                                 alt="google">
                                                            <span class="buttonText"><?= $langage_lbl['LBL_CONTINUE_GOOGLE']; ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </span>
                                <?php }
                                if ($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes" || $PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                    <span id="rider-social">
                                        <div class="aternate-login" data-name="OR"></div>
                                        <div class="soc-login-row">
                                            <label><?= $langage_lbl['LBL_LOGIN_WITH_SOCIAL_ACC']; ?></label>
                                            <ul class="social-list">
                                                <?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes") { ?>
                                                    <li>
                                                        <a target="_blank" href="facebook-rider/rider" tabindex="6"
                                                           class="btn-facebook">
                                                            <img src="assets/img/link-icon/facebook.svg" alt="Facebook"
                                                                 width="25px"><?= $langage_lbl['LBL_CONTINUE_FACEBOOK']; ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                                    <li>
                                                        <a target="_blank" href="linkedin-rider/rider" tabindex="7"
                                                           class="btn-linkedin">
                                                            <img src="assets/img/link-icon/linkedin.svg" alt="Linkdin"
                                                                 width="25px"><?= $langage_lbl['LBL_CONTINUE_LINKEDIN']; ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($PASSENGER_GOOGLE_LOGIN == "Yes") { ?>
                                                    <li>
                                                        <a href="google/rider" tabindex="8" class="btn-google1">
                                                            <img src="assets/img/link-icon/btn_google_light_normal_ios.svg"
                                                                 alt="google">
                                                            <span class="buttonText"><?= $langage_lbl['LBL_CONTINUE_GOOGLE']; ?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </span>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                    <div class="login-right" id="forgot_div" style="display:none">
                        <div class="login-data-inner">
                            <h1 id="forgot-user-label"></h1>
                            <span id="forgot_div_desc"><?= $db_forgot['page_desc']; ?></span>
                            <div class="form-err">
                                <span id="msg_closef" style="display:none;" class="msg_close error-login-v">&#10005;</span>
                                <p id="errmsgf" style="display:none;"
                                   class="text-muted btn-block btn btn-danger btn-rect error-login-v"></p>
                                <p style="display:none;background-color: #14b368;"
                                   class="btn-block btn btn-rect btn-success error-login-v" id="successf"></p>
                            </div>
                            <form action="" method="post" class="form-signin" id="frmforget"
                                  onSubmit="return forgotPass();">
                                <input type="hidden" name="action" class="action" value="rider">
                                <input type="hidden" name="iscompany" class="iscompany" value="0">
                                <div class="form-group newrow">
                                    <label><?= ($ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') ? $langage_lbl['LBL_EMAIL_MOBILE_NO_TXT_MSG'] : 'Email'; ?></label>
                                    <input type="<?= ($ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') ? 'text' : 'email' ?>"
                                           name="femail" tabindex="1" id="femail" class="femail" required/>

                                </div>
				                <div class="form-group">
                                 	<div class="mobile-info1" style="font-size: 11px;"><?= $langage_lbl['LBL_SIGN_IN_MOBILE_EMAIL_HELPER']; ?></div>
                            	</div>
                                <div class="form-group newrow">
                                    <div class="captchauser"> <?php include_once("recaptcha.php"); ?></div>
                                </div>
                                <div class="button-block">
                                    <div class="btn-hold">
                                        <input type="submit" id="btn_submit" tabindex="2"
                                               value="<?= $langage_lbl['LBL_Recover_Password']; ?>"/>
                                        <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                    </div>
                                </div>
                                <div class="aternate-login" data-name="OR"></div>
                                <div class="member-txt">
                                    <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?>
                                    <a href="javascript:void(0)"
                                       onClick="change_heading('login');"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="login-block-footer" <?php if ($loginblockfooter == 0) { ?>style="display:none"<?php } ?>>
                        <div class="note-holder active user" id="user">
                            <b>
                                <i class="fa fa-sticky-note"></i>
                                Note :
                            </b>
                            <p><?= $rider_note ?></p>
                            <b>
                                <i class="fa fa-user"></i>
                                Rider :
                            </b>
                            <p>Username: <?= $rider_email ?>
                                <br>
                                Password: 123456
                            </p>
                        </div>
                        <div class="note-holder provider">
                            <b>
                                <i class="fa fa-sticky-note"></i>
                                Note :
                            </b>
                            <p><?= $driver_note ?></p>
                            <b>
                                <i class="fa fa-user"></i>
                                Provider :
                            </b>
                            <p>Username: <?= $driver_email ?>
                                <br>
                                Password: 123456
                            </p>
                        </div>
                        <div class="note-holder company">
                            <b>
                                <i class="fa fa-sticky-note"></i>
                                Note :
                            </b>
                            <p>If you have registered as a new driver, use your registered Email Id and Password to view the
                                detail of your Rides. To view the Standard Features of the Apps use below access
                                details.
                            </p>
                            <b>
                                <i class="fa fa-building"></i>
                                Company :
                            </b>
                            <p>Username: <?= $company_email ?>
                                <br>
                                Password: 123456
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <!-- home page end-->
    <?php if(isset($ENABLE_WHATSAPP_OTP_VERIFICATION) && strtoupper($ENABLE_WHATSAPP_OTP_VERIFICATION) == "YES") { ?>
    <div class="loading-action" id="loaderIcon" style="display:none;">
        <div align="center">
            <img src="<?= $tconfig['tsite_url'] ?>assets/img/loader-action.gif" alt="">
        </div>
    </div>
    <div class="custom-modal-main" id="otp_verification_method">
        <div class="custom-modal">
            <div class="model-header">
                <h4><?= $langage_lbl['LBL_CHOOSE_VERIFICATION_METHOD_OTP_TXT']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">                
                <div class="otp-verification-method-section">
                    <div class="otp-verification-method sms-verify" onclick="sendOTPMethod('SMS')">
                        <div class="otp-verification-image">
                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] . 'assets/img/sms.png' ?>">
                        </div>
                        <div class="otp-verification-name">
                            <span class="otp-verification-subtitle"><?= $langage_lbl['LBL_OTP_VERIFICATION_BY_TXT']; ?></span>
                            <?= $langage_lbl['LBL_OTP_VERIFICATION_METHOD_SMS_TXT']; ?>
                        </div>
                    </div>
                    <div class="otp-verification-method whatsapp-verify" onclick="sendOTPMethod('WHATSAPP')">
                        <div class="otp-verification-image">
                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] . 'assets/img/whatsapp.png' ?>">
                        </div>
                        <div class="otp-verification-name">
                            <span class="otp-verification-subtitle"><?= $langage_lbl['LBL_OTP_VERIFICATION_BY_TXT']; ?></span>
                            <?= $langage_lbl['LBL_OTP_VERIFICATION_METHOD_WHATSAPP_TXT']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
    <?php if ($template != 'taxishark') { ?>
</div>
<?php } ?>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<?php if (strtolower($MOBILE_NO_VERIFICATION_METHOD) == 'firebase' && $SIGN_IN_OPTION == 'OTP') {  ?>
    <? include_once('firebasephoneverify.php'); ?>
<?php } ?>


<script>
    var APP_TYPE = '<?= $APP_TYPE; ?>';
    var company_register_count = '<?= $company_register_count ?>';
    var SIGN_IN_OPTION = '<?= $SIGN_IN_OPTION ?>';
    var ONLY_MEDICAL_SERVICE  = '<?= ONLY_MEDICAL_SERVICE; ?>';
    var otp_verification_method = '';
    $("document").ready(function () {
        if (SIGN_IN_OPTION == "OTP") {
            $("#mobile-otp-form").show();
            $("#passwordform").hide();
            $("#mobile-otp-add-form").hide();
        } else {
            $("#mobile-otp-form").hide();
            $("#passwordform").show();
            $("#mobile-otp-add-form").hide();
        }
        $(".form-header-back-btn").hide();
        $(".company-register-container").hide();
        $('.hotelhide,.hotelshow,.femail').on('keyup', function (e) {
            $(this).val($(this).val().replace(/\s/g, ''));
        });
        type = '<?php echo isset($_REQUEST['type']) ? $_REQUEST['type'] : '' ?>';

        if (type != '') {
            if (type == 'restaurant' || type == 'store') {
                $('.tab-switch li[data-id="restaurant"]').get(0).click();
                if (SIGN_IN_OPTION == 'OTP') {
                    $("#mobile-otp-form").show();
                    $("#passwordform").hide();
                    $("#mobile-otp-add-form").hide();
                }
            } else if (type == 'rider' || type == 'user') {
                $('.tab-switch li[data-id="user"]').get(0).click();
                if (SIGN_IN_OPTION == 'OTP') {
                    $("#mobile-otp-form").show();
                    $("#passwordform").hide();
                    $("#mobile-otp-add-form").hide();
                }
            } else if (type == 'provider' || type == 'driver' || type == 'carrier') {
                $('.tab-switch li[data-id="provider"]').get(0).click();
                if (SIGN_IN_OPTION == 'OTP') {
                    $("#mobile-otp-form").show();
                    $("#passwordform").hide();
                    $("#mobile-otp-add-form").hide();
                }
            }  else if (type == 'company' && (APP_TYPE == 'UberX' || ONLY_MEDICAL_SERVICE == 'Yes') ) {
                $('.tab-switch li[data-id="company"]').get(0).click();
            } else if (type == "company" && $('.tab-switch li[data-id="company_accounts"]').length > 0) {
                $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "company"}]);
            } else if (type == "organization" || type == "org") {
                $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "organization"}]);
            } else if (type == "tracking_company" || type == "tc") {
                $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "tracking_company"}]);
            } else {
                $('.tab-switch li[data-id="' + type + '"]').get(0).click();
            }
            if (type != "hotel") {
            }
        } else {
            //getPhoneCodeInTextBox('vEmail','CountryCode');
            //// getPhoneCodeInTextBox('vNirmal','vNirmalCode');
            //getPhoneCodeInTextBox('femail','CountryCodeForgt');
        }
        getPhoneCodeInTextBox('vEmail', 'CountryCode', function () {
            countryCodeByDefultShow();
        });
        getPhoneCodeInTextBox('femail', 'CountryCodeForgt', function () {
            countryCodeByDefultShow();
        });
    });
    $(".company-register-block").on("click", function () {
        var idattar = $(this).attr("id-attr");
        var DATADESC = $(this).attr('data-desc');
        if (idattar == "store") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{
                etype: "restaurant",
                DATADESC: DATADESC
            }]);
        }
        if (idattar == "company") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "company", DATADESC: DATADESC}]);
        }
        if (idattar == "organization") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{
                etype: "organization",
                DATADESC: DATADESC
            }]);
        }
        if (idattar == "tracking_company") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{
                etype: "tracking_company",
                DATADESC: DATADESC
            }]);
        }
    });
    $("#company-register-block-back-btn").on("click", function () {
        $('.tab-switch li[data-id="company_accounts"]').trigger('click');
    });

    $("#otp-verification-back-btn").on("click", function () {
        $('.tab-switch li.active').trigger('click');
    });

    $(".tab-switch li").on("click", function (e, data) {
        $(".form-header-back-btn").hide();
        $("#frmforget")[0].reset();
        $("#sendOTP").button('reset');
        timerOn = false;
        otp_verification_method = '';
        var dataId = $(this).attr("data-id");
        try {
            if (data != 'undefined') {
                if (typeof data != '') {
                    dataId = data.etype;
                }
            }
        } catch (e) {
        }
        if (dataId != "hotel") {
            $('.mobile-info').show();
        } else {
            $('.mobile-info').hide();
        }
        if (dataId == 'restaurant1') {
            $("#signinlink").attr("href", "sign-up?type=store");
        } else {
            $("#signinlink").attr("href", "sign-up?type=" + dataId);
        }
        $(".company-register-container").hide();
        $(".login-left , .login-right#login_div").show();
        if (dataId == "company_accounts") {
            $(".company-register-container").show();

            $(".login-left , .login-right#login_div , .form-header-back-btn").hide();
        }
        if (dataId == 'user') {
            action_dataId = 'rider';
            action_url = 'mytrip.php';
            $(".iscompany").val(0);
            $("#type_usr").val('Rider');
            $("#rider-social").show();
            $(".form-header-back-btn").hide();
            $("#driver-social, #company-social").hide();
            $("#vEmail").val('<?php echo $rider_email; ?>');
            $("#vPassword").val('<?php echo $pwd; ?>');

            <?php if ($loginblockfooter == 1) { ?>
            $(".login-block-footer").show();
            <?php } ?>
            $("#vEmail").attr("required");
            $("#vEmailh").removeAttr("required");
            $(".CompSystem").val("");
            if (SIGN_IN_OPTION == 'OTP') {
                $("#mobile-otp-form").show();
                $("#passwordform").hide();
                $("#mobile-otp-add-form").hide();
            }
        } else if (dataId == 'provider') {
            action_dataId = 'driver';
            action_url = 'profile';
            //action_url = 'profile.php';
            $(".iscompany").val(0);
            $("#type_usr").val('Driver');
            $("#rider-social").hide();
            $("#driver-social").show();
            $("#vEmail").val('<?php echo $driver_email; ?>');
            $("#vPassword").val('<?php echo $pwd; ?>');
            $(".form-header-back-btn").hide();
            <?php if ($loginblockfooter == 1) { ?>
            $(".login-block-footer").show();
            <?php } ?>
            $("#vEmail").attr("required");
            $("#vEmailh").removeAttr("required");
            $(".CompSystem").val("");
            if (SIGN_IN_OPTION == 'OTP') {
                $("#mobile-otp-form").show();
                $("#passwordform").hide();
                $("#mobile-otp-add-form").hide();
            }
        } else if (dataId == 'company' || dataId == 'restaurant') {
            action_dataId = 'driver';
            action_url = 'dashboard.php';
            $(".iscompany").val(1);
            $("#type_usr").val('Company');
            $("#rider-social,#driver-social").hide();
            //$("#company-social").show();
            $("#vEmail").val('<?php echo $company_email; ?>');
            $("#vPassword").val('<?php echo $pwd; ?>');

            <?php if ($loginblockfooter == 1) { ?>
            $(".login-block-footer").show();
            <?php } ?>
            $("#vEmail").attr("required");
            $("#vEmailh").removeAttr("required");
            if (dataId == 'company') {
                $(".form-header-back-btn").show();
                if (APP_TYPE == 'UberX' || APP_TYPE == 'Delivery' || company_register_count == 1) {
                    $('.form-header-back-btn').hide();
                }
                $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_SERVICE_PROVIDER_COMPANY_TXT']); ?>');
                $(".CompSystem").val("General");
            } else {
                $(".form-header-back-btn").hide();
                $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_RESTAURANT_GROCERY_ETC_STORE_TXT']); ?>');
                $(".CompSystem").val("DeliverAll");
            }
            if (SIGN_IN_OPTION == 'OTP' && dataId == 'restaurant') {
                $("#mobile-otp-form").show();
                $("#passwordform").hide();
                $("#mobile-otp-add-form").hide();
            } else {
                $("#mobile-otp-form").hide();
                $("#passwordform").show();
                $("#mobile-otp-add-form").hide();
            }
        } else if (dataId == 'tracking_company') {
            $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_TRACKING_COMPANY']); ?>');
            action_dataId = 'tracking_company';
            action_url = 'dashboard.php';
            $(".iscompany").val(1);
            $("#type_usr").val('tracking_company');
            $("#rider-social,#driver-social").hide();
            //$("#company-social").show();
            $("#vEmail").val('<?php echo $company_email; ?>');
            $("#vPassword").val('<?php echo $pwd; ?>');

            <?php if ($loginblockfooter == 1) { ?>
            $(".login-block-footer").show();
            <?php } ?>
            $("#vEmail").attr("required");
            $("#vEmailh").removeAttr("required");
            $(".CompSystem").val("General");
            $(".form-header-back-btn").show();
            if (SIGN_IN_OPTION == 'OTP') {
                $("#mobile-otp-form").hide();
                $("#passwordform").show();
                $("#mobile-otp-add-form").hide();
            }
        } else if (dataId == 'organization') {
            $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_CORPORATE_ORGANIZATION']); ?>');
            action_dataId = 'organization';
            action_url = 'organization-profile';
            $(".iscompany").val(0);
            $("#type_usr").val('organization');
            $("#rider-social,#driver-social").hide();
            //$("#company-social").show();
            $("#vEmail,#vPassword").val('');
            $(".login-block-footer").hide();
            $("#vEmail").attr("required");
            $("#vEmailh").removeAttr("required");
            $(".CompSystem").val("Organization");
            $(".form-header-back-btn").show();
            if (SIGN_IN_OPTION == 'OTP') {
                $("#mobile-otp-form").hide();
                $("#passwordform").show();
                $("#mobile-otp-add-form").hide();
            }
        } else if (dataId == 'hotel') {
            action_dataId = 'hotel';
            action_url = 'dashboard.php';
            $("#type_usr").val('hotel');
            $("#rider-social,#driver-social").hide();
            $("#vEmailh").val('<?php echo $company_email; ?>');
            $("#vPassword").val('<?php echo $pwd; ?>');
            $("#vEmailh").attr("required");
            $("#vEmail").removeAttr("required");
            $(".form-header-back-btn").hide();
            <?php if ($loginblockfooter == 1) { ?>
            $(".login-block-footer").show();
            <?php } ?>
            $(".CompSystem").val("");
            if (SIGN_IN_OPTION == 'OTP') {
                $("#mobile-otp-form").hide();
                $("#passwordform").show();
                $("#mobile-otp-add-form").hide();
            }
        } else {
            action_dataId = 'rider';
            action_url = 'dashboard.php';
        }
        if (dataId == 'hotel') {
            $(".form-header-back-btn").hide();
            $(".hotelhide").hide();
            $(".hotelshow").show();
        } else {
            $(".hotelhide").show();
            $(".hotelshow").hide();
        }
        if (SIGN_IN_OPTION == 'OTP' && (jQuery.inArray(dataId, ['hotel','company','company_accounts','organization']) === -1)) {
            $(".countryPhoneSelectWrapper").show();
            $(".phoneinput").val('');
            $(".vPhoneNumber").addClass('phoneinput');
        }else{
            $(".countryPhoneSelectWrapper").hide();
        }
        //$(".countryPhoneSelectWrapper").hide();
        $(".action").val(action_dataId);
        $("#action_url").val(action_url);
        //errmsg
        document.getElementById("errmsg").innerHTML = '';
        document.getElementById("errmsg").style.display = 'None';
        document.getElementById("msg_close").style.display = 'None';

        /*------------------for delete account-----------------*/
        var fullUrl = window.location.href;
        if (fullUrl.includes('deleteaccount')) {
            ChangeUrl('Login', 'sign-in?type='+dataId+'&deleteaccount=1');
        } else {
            ChangeUrl('Login', 'sign-in?type='+dataId);
        }

    });
    function ChangeUrl(title, url) {
        if (typeof (history.pushState) != "undefined") {
            var obj = { Title: title, Url: url };
            history.pushState(obj, obj.Title, obj.Url);
        } else {
            alert("Browser does not support HTML5.");
        }
    }
    $(document).ready(function () {

        var err_msg = '<?= $err_msg ?>';
        if (err_msg != "") {
            document.getElementById("errmsg").innerHTML = err_msg;
            document.getElementById("errmsg").style.display = '';
            document.getElementById("msg_close").style.display = '';
            return false;
        }

        $("#vEmail").val('<?php echo $rider_email; ?>');
        $("#vPassword").val('<?php echo $pwd; ?>');
        
        <?php if (strtolower($MOBILE_NO_VERIFICATION_METHOD) == 'firebase' && $SIGN_IN_OPTION == 'OTP') {  ?>
            initReCaptchaLogin();
        <?php } ?>

        resizeImage("setImageRatioJS", "attr-image-src" , "imageSrc", "invisible" ,"No");

    });

    function parallaxReinit() {
        setTimeout(function () {
            $('.parallax-window').parallax('destroy');
            $('.parallax-window').parallax();
        }, 100);
    }

    function validatePhone(vPhoneNumber) {
        var numbers = /^[0-9]+$/;
        var val = vPhoneNumber;
        if (val.match(numbers)) {
            return true;
        } else {
            return false;
        }
    }
    function chkValid() {
        //parallaxReinit();
        login_type = $(".action").val();
        iscompany = $(".iscompany").val();
        var selTabType = $("#type_usr").val();
        var id = document.getElementById("vEmail").value;
        var CompSystem = $(".CompSystem").val();
        if (login_type == 'hotel') {
            var id = document.getElementById("vEmailh").value;
        }
        var myarray = ["Rider", "Driver"];
        
        if ((CompSystem == "DeliverAll" && selTabType == "Company" && SIGN_IN_OPTION == "OTP") || (SIGN_IN_OPTION == "OTP" && jQuery.inArray(selTabType, myarray) !== -1)) {
            var id = document.getElementById("vPhoneNumber").value;
            
            if (id == '') {
                $("#sendOTP").button('reset');
                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
                return false;
            } else {
                if (validatePhone(id)) {
                    var number = $("#vPhoneNumber").val();
                    var vPhoneCode = $("#CountryCode").find(':selected').attr('data-code');
                    vPhoneCode = vPhoneCode.replace('+', '');
                    var GeneralUserType = $(".action").val();
                    var type_usr = $("#type_usr").val();
                    var inputData = {
                        "type_usr": type_usr,
                        "vEmail": number,
                        "CountryCode": vPhoneCode,
                        "action": GeneralUserType,
                        "CompSystem": CompSystem
                    };
                    var ajaxData = {
                        'URL': 'ajax_check_phone.php',
                        'AJAX_DATA': inputData,
                        'REQUEST_DATA_TYPE': 'json',
                        'REQUEST_TYPE': 'POST',
                    };
                    getDataFromAjaxCall(ajaxData, function (response) {
                        $('#loaderIcon').hide();
                        if (response.action == "1") {
                            var jsonParseData = response.result;
                            login_status = jsonParseData.login_status;
                            eSystem = jsonParseData.eSystem;
                            if (login_status == 1) {
                                $("#sendOTP").button('reset');
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACC_DELETE_TXT']); ?>';
                                document.getElementById("errmsg").style.display = '';
                                document.getElementById("msg_close").style.display = '';
                            } else if (login_status == 2) {
                                sendOTP();
                                document.getElementById("errmsg").style.display = 'none';
                                document.getElementById("msg_close").style.display = 'none';
                                return false;
                            } else if (login_status == 3) {
                                $("#sendOTP").button('reset');
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
                                document.getElementById("errmsg").style.display = '';
                                document.getElementById("msg_close").style.display = '';
                            } else if (login_status == 4) {
                                $("#sendOTP").button('reset');
                                <?php if(SITE_TYPE == "Demo") { ?>
                                document.getElementById("errmsg").innerHTML = 'Your Account has been Inactivated. Please contact the Sales Team to re-activate it and to continue testing the System.';
                                <?php } else { ?>
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']); ?>';
                                <?php } ?>
                                document.getElementById("errmsg").style.display = '';
                                document.getElementById("msg_close").style.display = '';
                            } else {
                                $("#sendOTP").button('reset');
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
                                document.getElementById("errmsg").style.display = '';
                                document.getElementById("msg_close").style.display = '';
                            }
                            return false;
                        } else {
                            $("#sendOTP").button('reset');
                            //console.log(response.result);
                            return false;
                        }
                    });
                } else {
                    $("#sendOTP").button('reset');
                    document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
                    document.getElementById("errmsg").style.display = '';
                    document.getElementById("msg_close").style.display = '';
                    return false;
                }
            }
        } else {
            var pass = document.getElementById("vPassword").value;
            var selTabType = $("#type_usr").val();
            if (id == '' || pass == '') {
                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_EMAIL_PASS_ERROR_MSG']); ?>';
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
                return false;
            }else if(pass.includes(' ')){
                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_NO_SPACE_ERROR_MSG']); ?>';
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
            }else {
                var url;
                if (login_type == 'organization') {
                    url = 'ajax_organization_login_action.php';
                } else if (login_type == 'tracking_company') {
                    url = 'ajax_track_comapny_login_action.php';
                } else {
                    url = 'ajax_login_action.php';
                }
                $(".neglect").remove();
                var ajaxData = {
                    'URL': url,
                    'AJAX_DATA': $("#login_box").serialize(),
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var data = response.result;
                        jsonParseData = JSON.parse(data);
                        login_status = jsonParseData.login_status;
                        eSystem = jsonParseData.eSystem;
                        if (login_status == 1) {
                            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACC_DELETE_TXT']); ?>';
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                        } else if (login_status == 2) {
                            <?php
                            $RedirectUrl = "";
                            if (isset($_COOKIE['login_redirect_url_user']) && !empty($_COOKIE['login_redirect_url_user']) && isset($_SESSION['sess_iUserId'])) {
                                $RedirectUrl = $_COOKIE['login_redirect_url_user'];
                            }
                            if (!empty($_SESSION["navigatedPage"]) && ONLY_MEDICAL_SERVICE != 'Yes') {
                                $RedirectUrl = 'userbooking';
                            }
                            ?>

                            var redirecturl = '<?= $RedirectUrl ?>';
                            document.getElementById("errmsg").style.display = 'none';
                            document.getElementById("msg_close").style.display = 'none';
                            departType = '<?php echo $depart; ?>';
                            if (redirecturl != "") {
                                window.location = redirecturl;
                                return false;
                            }
                            if (login_type == 'rider' && departType == 'mobi')
                                window.location = "mobi";
                            else if (login_type == 'driver' && iscompany == "1" && eSystem == "DeliverAll")
                                //window.location = "dashboard.php";
                                window.location = "profile"; // Redirect to company profile page if logged in as store
                            else if ((login_type == 'driver' && iscompany == "1") || login_type == "tracking_company")
                                window.location = "profile";
                            else if (login_type == 'driver')
                                // window.location = "profile.php";
                                //window.location = "driver-profile"; // New Profile design URL
                                window.location = "profile"; // New Profile design URL
                            else if (login_type == 'rider') {
                                var url = getCookie('ManualBookingURL');
                                <? if(isset($_SESSION['is_msite']) && $_SESSION['is_msite'] == 'Yes') { ?>
                                window.location = "msite";
                                <? } else { ?>
                                if (url != null) {
                                    setCookie('ManualBookingURL', "");
                                    window.location = url;
                                } else {

                                    <? if(isset($_SESSION['fareestimate_redirect']) && $_SESSION['fareestimate_redirect'] == "Yes" && ONLY_MEDICAL_SERVICE != 'Yes'){ ?>
                                        window.location = "userbooking";
                                    <? } else { ?>
                                        window.location = "profile-user";
                                    <? } ?>

                                }
                                <? } ?>
                            } else if (login_type == 'organization') {
                                window.location = "organization-profile";
                            } else if (login_type == 'hotel') {
                                window.location = "<?= $tconfig["tsite_url_main_admin"] ?>dashboard.php";
                            }
                            return true; // success registration
                        }
                        else if (login_status == 3) {

                            if (selTabType == "hotel") {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_PASS_ERROR_MSG']); ?>';
                            } else {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']); ?>';
                            }
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                        }
                        else if (login_status == 4) {
                            <?php if(SITE_TYPE == "Demo") { ?>
                            document.getElementById("errmsg").innerHTML = 'Your Account has been Inactivated. Please contact the Sales Team to re-activate it and to continue testing the System.';
                            <?php } else { ?>
                            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']); ?>';
                            <?php } ?>
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                        }
                        else {
                            if (selTabType == "hotel") {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_PASS_ERROR_MSG']); ?>';
                            } else {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']); ?>';
                            }
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                        }
                    } else {
                        //console.log(response.result);
                    }
                });
                return false;
            }
        }
    }

    function setCookie(key, value) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (1 * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
    }

    function getCookie(key) {
        var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }

    function change_heading(type) {
        $('.error-login-v').hide();
        if (type == 'forgot') {
            $('#forgot_div').show();
            $("#frmforget .form-group, #frmforget .button-block,.forget_password").show();
            $('#login_div').hide();
            $('.login-caption, .login-block-footer .note-holder').removeClass('active');
            $('.login-block-footer, .tab-switch').hide();
            $('.login-block-heading').addClass('forget_label');
            lbl = $('ul.tab-switch li.active a').text();
            //$('.forget_label label').text(lbl);
            $("#forgot-user-label").text(lbl);
            $("#forgot_div_desc").html("<?php echo getProperDataValue($db_forgot['page_desc']); ?>"); //getProperDataValue used bc when in desc-editor inline css applied with double quotes then here string will be broken so put it discussed with KS

            /* $(".login-left img").attr("src", "<?php //echo $db_forgot_src; ?>");*/

            $(".login-left img").addClass('imageVisibilityHidden').attr("attr-image-src-forgotpassword", "<?php echo $db_forgot_src; ?>");
            resizeImage("login-left", "attr-image-src-forgotpassword" , "imageSrc", "invisible" ,"No");

            $('.login-block').addClass('for-forgot');
            $("#forgotlabel").show();
            $("#loginlabel").hide();
            $('.tab-switch').removeClass('login-tab-switch');
            $('.tabholder').removeClass('login-tabholder');
        } else {
            $('#forgot_div,.forget_password').hide();
            $('#login_div').show();
            $('.login-block').removeClass('for-forgot');

            <?php if ($loginblockfooter == 1) { ?>
            $(".login-block-footer").show();
            <?php } ?>

            $('.tab-switch').show();
            $('ul.tab-switch li.active').trigger("click"); //its bc active tab data appears when again comeback to signin from diff tab
            $('.login-block-heading').removeClass('forget_label');

            $(".login-left img").addClass('imageVisibilityHidden');
            resizeImage("login-left", "attr-image-src" , "imageSrc", "invisible" ,"No");

            $("#loginlabel").show();
            $("#forgotlabel").hide();
            $('.tab-switch').addClass('login-tab-switch');
            $('.tabholder').addClass('login-tabholder');
        }
    }


    /*------------------forgot password validation-----------------*/
    $('#frmforget').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow , .form-group').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow , .form-group').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input , .form-group input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow , .form-group').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            'femail': {required: true},
            'g-recaptcha-response': {
              required: function() {
                return grecaptcha.getResponse() == '';
                    }
            },
        },
        messages: {}

    });
    /*------------------forgot password validation-----------------*/
    function forgotPass() {

        var isvalidate = $("#frmforget")[0].checkValidity();
      
        if(isvalidate) {
            $('.error-login-v').hide();
            $("#btn_submit").val("<?= addslashes($langage_lbl['LBL_PLEASE_WAIT']) ?> ...").attr('disabled', 'disabled');
            var site_type = '<? echo SITE_TYPE; ?>';
            var id = document.getElementById("femail").value;
            if (id == '') {
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']); ?>';
            } else {

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_fpass_action.php',
                    'AJAX_DATA': $("#frmforget").serialize(),
                    'REQUEST_DATA_TYPE': 'json'
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var data = response.result;
                        grecaptcha.reset();
                        if (data.status == 1) {
                            //change_heading('login');
                            document.getElementById("successf").innerHTML = data.msg;
                            document.getElementById("successf").style.display = '';
                            $("#frmforget .form-group, #frmforget .button-block").hide();
                             $("#btn_submit").val("<?= addslashes($langage_lbl['LBL_Recover_Password']) ?>").removeAttr('disabled');
                        } else {
                            document.getElementById("errmsgf").innerHTML = data.msg;
                            document.getElementById("errmsgf").style.display = '';
                            document.getElementById("msg_closef").style.display = '';
                            //$("#btn_submit").val("<?= addslashes($langage_lbl['LBL_PLEASE_WAIT']) ?> ...").attr('disabled', 'disabled');
                             $("#btn_submit").val("<?= addslashes($langage_lbl['LBL_Recover_Password']) ?>").removeAttr('disabled');
                        }
                    } else {
                       // console.log(response.result);
                    }
                });
            }
            return false;
        }
    }


    /*------------------forgot password-----------------*/
    var ajaxData = {
        'URL': '<?= $tconfig['tsite_url'] ?>recaptcha.php?type=1234',
        'AJAX_DATA': "",
        'REQUEST_DATA_TYPE': 'html'
    };
    getDataFromAjaxCall(ajaxData, function(response) {
        if(response.action == "1") {
            var dataHtml2 = response.result;
            if (dataHtml2 != "") {
                $('.captchauser').html(dataHtml2);
            }
        } else {
            //console.log(response.result);
        }
    });
    /*------------------forgot password-----------------*/
    function countryCodeByDefultShow(){
        const otpFrom = document.getElementById('mobile-otp-form');
        $this = $('.countryPhoneSelectWrapper');
    
        $.each($this, function (i, v) {
            if (otpFrom && otpFrom.contains(this)) {
                $(this).show();
                $(this).closest(".vPhoneNumber").addClass('phoneinput');
            }
        })
    }
    <?php if($SIGN_IN_OPTION == 'OTP') {?>
    $("#mobile-otp-add-form,.resendcode,#verify").hide();
    $("#verify").attr('disabled', true);
    let timerOn = true;
    getPhoneCodeInTextBox('vPhoneNumber', 'CountryCode', function () {
        countryCodeByDefultShow();
    });
    var userphoneNumber;
    var vPhoneCode;
    var number;
    function sendOTP(verification_method = "") {
        if(verification_method == "" && otp_verification_method == "") {
            <?php if(strtoupper($ENABLE_WHATSAPP_OTP_VERIFICATION) == "YES") { ?>
            $('#otp_verification_method').addClass('active');
            return false;
            <?php } ?>
        }
        number = $("#vPhoneNumber").val();
        vPhoneCode = $("#CountryCode").find(':selected').attr('data-code');
        vPhoneCode = vPhoneCode.replace('+', '');
        var vGeneralLang = "<?php echo $_SESSION['sess_lang']?>";
        var GeneralUserType = $("#type_usr").val();
        if (number != null) {
            if(verification_method.toUpperCase() == "WHATSAPP" || otp_verification_method.toUpperCase() == "WHATSAPP") {
                $('#sendOTP').button('loading'); 

                var data = {
                    "MobileNo": number,
                    "vPhoneCode": vPhoneCode,
                    "vGeneralLang": vGeneralLang,
                    "type": "sendAuthOtp",
                    "UserType": GeneralUserType,
                    "SendRequestWeb": 'Yes',
                    "verificationMethod": 'WHATSAPP',
                    "async_request": false
                };
                data = $.param(data);
                getDataFromApi(data, function (response) {
                    $("#sendOTP").button('reset');
                    var response = JSON.parse(response);
                    if (response.Action == '1') {
                        $("#mobile-otp-add-form,.countdown,#verify, #otp-input-whatsapp").show();
                        $("#verify").attr('disabled', false);
                        $("#mobile-otp-form,.resendcode,#passwordform, #otp-input-sms").hide();
                        timerOn = true;
                
                        timer(<?php echo $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS ?>);
                        return false;
                    } else {
                        if(response.message == "LBL_FAILED_SEND_AUTH_OTP"){
                            document.getElementById("errmsg").innerHTML =  '<?= addslashes($langage_lbl['LBL_FAILED_SEND_AUTH_OTP']); ?>';
                        } else {
                            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
                        }
                        document.getElementById("errmsg").style.display = '';
                        document.getElementById("msg_close").style.display = '';
                        return false;
                    }
                });
            } else {
                <?php if (strtolower($MOBILE_NO_VERIFICATION_METHOD) == 'firebase') { ?>
                    submitPhoneNumberAuthLogin(vPhoneCode,number);
                    return false;
                <?php } else { ?>
                    var data = {
                        "MobileNo": number,
                        "vPhoneCode": vPhoneCode,
                        "vGeneralLang": vGeneralLang,
                        "type": "sendAuthOtp",
                        "UserType": GeneralUserType,
                        "async_request": false,
                        "SendRequestWeb": 'Yes'
                    };
                    data = $.param(data);
                    getDataFromApi(data, function (response) {
                        $("#sendOTP").button('reset');
                        var response = JSON.parse(response);
                        if (response.Action == '1') {
                            $("#mobile-otp-add-form,.countdown,#verify, #otp-input-sms").show();
                            $("#verify").attr('disabled', false);
                            $("#mobile-otp-form,.resendcode,#passwordform, #otp-input-hide").hide();
                            timerOn = true;
                    
                            timer(<?php echo $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS ?>);
                            return false;
                        } else {
                            if(response.message == "LBL_FAILED_SEND_AUTH_OTP"){
                                document.getElementById("errmsg").innerHTML =  '<?= addslashes($langage_lbl['LBL_FAILED_SEND_AUTH_OTP']); ?>';
                            } else {
                            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
                            }
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                            return false;
                        }
                    });
                <?php } ?>
            }
            
        } else {
            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>';
            document.getElementById("errmsg").style.display = '';
            document.getElementById("msg_close").style.display = '';
            return false;
        }
    }
    function verifyOTP() {
        let compiledOtp = '';
        if(otp_verification_method.toUpperCase() == "WHATSAPP") {
            const OTPinputs = document.querySelectorAll('#OTPInputWhatsApp > *.mobileOtp');
            for (let i = 0; i < OTPinputs.length; i++) {
                compiledOtp += OTPinputs[i].value;
            }
            $('#mobileOtpWhatsApp').val(compiledOtp);
            loginaction(compiledOtp);
        } else {
            <?php if (strtolower($MOBILE_NO_VERIFICATION_METHOD) == 'firebase') {  ?>
                compiledOtp = document.getElementById('mobileOtp').value;
                if(compiledOtp != ""){
                    confirmationResult.confirm(compiledOtp).then(function(result) {
                        var number = $("#vPhoneNumber").val();
                        var vPhoneCode = $("#CountryCode").find(':selected').attr('data-code');
                        vPhoneCode = vPhoneCode.replace('+', '');
                        //var GeneralUserType = $(".action").val();
                        var GeneralUserType = $("#type_usr").val();
                        var inputData = {
                            "otp": compiledOtp,
                            "userType": GeneralUserType,
                            "mobileNo": number,
                            "vPhoneCode": vPhoneCode,
                            "action":'updateOTP'
                        };
                        var ajaxData = {
                            'URL': 'firebasephoneverify.php',
                            'AJAX_DATA': inputData,
                            'REQUEST_DATA_TYPE': 'json',
                            'REQUEST_TYPE': 'POST',
                            'async_request': false
                        };
                        getDataFromAjaxCall(ajaxData, function (response) {
                            //console.log(response);
                            if (response.action == "1") {
                                var data = response.result;
                                loginaction(compiledOtp);
                            } else {
                               /* document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_OTP_INCORRECT_MSG']); ?>';
                                document.getElementById("errmsg").style.display = '';
                                document.getElementById("msg_close").style.display = '';*/
                                return false;
                            }
                        });
                    }) .catch(function(error) {  
                        document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_OTP_INCORRECT_MSG']); ?>';
                        document.getElementById("errmsg").style.display = '';
                        document.getElementById("msg_close").style.display = '';
                        return false;
                    });
                } else {
                    document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ENTER_OTP_TXT']); ?>';
                    document.getElementById("errmsg").style.display = '';
                    document.getElementById("msg_close").style.display = '';
                    return false;
                }
            <?php } else { ?>
                const OTPinputs = document.querySelectorAll('#OTPInput > *.mobileOtp');
                for (let i = 0; i < OTPinputs.length; i++) {
                    compiledOtp += OTPinputs[i].value;
                }
                document.getElementById('mobileOtp').value = compiledOtp;
                loginaction(compiledOtp);
            <?php } ?>
        }
        
    }
    function loginaction(compiledOtp){
        var number = $("#vPhoneNumber").val();
        var vPhoneCode = $("#CountryCode").find(':selected').attr('data-code');
        vPhoneCode = vPhoneCode.replace('+', '');
        //var GeneralUserType = $(".action").val();
        var GeneralUserType = $("#type_usr").val();
        var inputData = {
            "otp": compiledOtp,
            "type_usr": GeneralUserType,
            "vEmail": number,
            "CountryCode": vPhoneCode,
            "action": "verify_otp",
             "async_request": false
        };
        var ajaxData = {
            'URL': 'ajax_login_action.php',
            'AJAX_DATA': inputData,
            'REQUEST_DATA_TYPE': 'json',
            'REQUEST_TYPE': 'POST',
        };
        if (compiledOtp != null) {
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    jsonParseData = JSON.parse(JSON.stringify(data));
                    login_status = jsonParseData.login_status;
                    eSystem = jsonParseData.eSystem;
                    if (login_status == 1) {
                        document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACC_DELETE_TXT']); ?>';
                        document.getElementById("errmsg").style.display = '';
                        document.getElementById("msg_close").style.display = '';
                    } else if (login_status == 2) {
                        <?php
                        $RedirectUrl = "";
                        if (isset($_COOKIE['login_redirect_url_user']) && !empty($_COOKIE['login_redirect_url_user']) && isset($_SESSION['sess_iUserId'])) {
                            $RedirectUrl = $_COOKIE['login_redirect_url_user'];
                        }
                        if (!empty($_SESSION["navigatedPage"])) {
                            $RedirectUrl = 'userbooking';
                        }
                        ?>
                        var redirecturl = '<?= $RedirectUrl ?>';
                        document.getElementById("errmsg").style.display = 'none';
                        document.getElementById("msg_close").style.display = 'none';
                        departType = '<?php echo $depart; ?>';
                        if (redirecturl != "") {
                            window.location = redirecturl;
                            return false;
                        }
                        if (login_type == 'rider' && departType == 'mobi')
                            window.location = "mobi";
                        else if (login_type == 'driver' && iscompany == "1" && eSystem == "DeliverAll")
                            window.location = "profile"; // Redirect to company profile page if logged in as store
                        else if ((login_type == 'driver' && iscompany == "1") || login_type == "tracking_company")
                            window.location = "profile";
                        else if (login_type == 'driver')
                            window.location = "profile"; // New Profile design URL
                        else if (login_type == 'rider') {
                            var url = getCookie('ManualBookingURL');
                            <? if(isset($_SESSION['is_msite']) && $_SESSION['is_msite'] == 'Yes') { ?>
                            window.location = "msite";
                            <? } else { ?>
                            if (url != null) {
                                setCookie('ManualBookingURL', "");
                                window.location = url;
                            } else {
                                <? if($_SESSION['fareestimate_redirect'] == "Yes" ){ ?>
                                window.location = "userbooking";
                                <? }else{ ?>
                                window.location = "profile-user";
                                <? } ?>
                            }
                            <? } ?>
                        } else if (login_type == 'organization') {
                            window.location = "organization-profile";
                        } else if (login_type == 'hotel') {
                            window.location = "<?= $tconfig["tsite_url_main_admin"] ?>dashboard.php";
                        }
                        return true; // success registration
                    } else if (login_status == 3) {
                        document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_OTP_INCORRECT_MSG']); ?>';
                        document.getElementById("errmsg").style.display = '';
                        document.getElementById("msg_close").style.display = '';
                    } else if (login_status == 4) {
                        <?php if(SITE_TYPE == "Demo") { ?>
                        document.getElementById("errmsg").innerHTML = 'Your Account has been Inactivated. Please contact the Sales Team to re-activate it and to continue testing the System.';
                        <?php } else { ?>
                        document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']); ?>';
                        <?php } ?>
                        document.getElementById("errmsg").style.display = '';
                        document.getElementById("msg_close").style.display = '';
                    } else {
                        document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']); ?>';
                        document.getElementById("errmsg").style.display = '';
                        document.getElementById("msg_close").style.display = '';
                    }
                } else {
                    document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_OTP_INCORRECT_MSG']); ?>';
                    document.getElementById("errmsg").style.display = '';
                    document.getElementById("msg_close").style.display = '';
                    return false;
                }
            });
        } else {
            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ENTER_OTP_TXT']); ?>';
            document.getElementById("errmsg").style.display = '';
            document.getElementById("msg_close").style.display = '';
            return false;
        }
    }
    const inputs = document.querySelectorAll("input.mobileOtp"),
        button = document.querySelector("#verify");
    if (inputs[0]) {
        // iterate over all inputs
        inputs.forEach((input, index1) => {
            input.addEventListener("keyup", (e) => {
                // This code gets the current input element and stores it in the currentInput variable
                // This code gets the next sibling element of the current input element and stores it in the nextInput variable
                // This code gets the previous sibling element of the current input element and stores it in the prevInput variable
                const currentInput = input,
                    nextInput = input.nextElementSibling,
                    prevInput = input.previousElementSibling;
                // if the value has more than one character then clear it
                if (currentInput.value.length > 1) {
                    currentInput.value = "";
                    return;
                }
                // if the next input is disabled and the current value is not empty
                //  enable the next input and focus on it
                if (nextInput && nextInput.hasAttribute("disabled") && currentInput.value !== "") {
                    nextInput.removeAttribute("disabled");
                    nextInput.focus();
                }
                // if the backspace key is pressed
                if (e.key === "Backspace") {
                    // iterate over all inputs again
                    inputs.forEach((input, index2) => {
                        // if the index1 of the current input is less than or equal to the index2 of the input in the outer loop
                        // and the previous element exists, set the disabled attribute on the input and focus on the previous element
                        if (index1 <= index2 && prevInput) {
                            input.setAttribute("disabled", true);
                            input.value = "";
                            prevInput.focus();
                        }
                    });
                }
                //if the fourth input( which index number is 3) is not empty and has not disable attribute then
                //add active class if not then remove the active class.
                if (!inputs[3].disabled && inputs[3].value !== "") {
                    button.classList.add("active");
                    return;
                }
                button.classList.remove("active");
            });
        });
        //focus the first input which index is 0 on window load
        window.addEventListener("load", () => inputs[0].focus());
    }
    function timer(remaining) {
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        m = m < 10 ? '0' + m : m;
        s = s < 10 ? '0' + s : s;
        document.getElementById('countdown').innerHTML = m + ':' + s;
        remaining -= 1;
        if (remaining >= 0 && timerOn) {
            setTimeout(function () {
                timer(remaining);
            }, 1000);
            return;
        }

        if (!timerOn) {
            // Do validate stuff here
            return;
        }
        // Do timeout stuff here
        //alert('Timeout for otp');
        $(".resendcode").show();
        $(".countdown").hide();
    }
    <?php } ?>

    $("#sendOTP").click(function () {
        <?php if(isset($ENABLE_WHATSAPP_OTP_VERIFICATION) && strtoupper($ENABLE_WHATSAPP_OTP_VERIFICATION) == "YES") { ?>
        $('#loaderIcon').show();
        <?php } else { ?>
        var $btn = $(this);
        $btn.button('loading');
        <?php } ?>
        chkValid();
        return false;
    });
    $("#verify").click(function () {
        verifyOTP();
        return false;
    });
    /* $('.login_form').keypress((e) => {
         if (e.which === 13) {
             console.log('.login form');
             //chkValid();
         }
     })*/
    var pressed = false;
    $('#login_box').on('keyup', function (event) {
        if (event.key === 'Enter' &&  !pressed) {
            pressed = true;
            if ($('#sendOTP').is(':visible')) {
                $('#sendOTP').click();
            }
            /*if ($('#verify').is(':visible')) {
                $('#verify').click();
            }*/
           /* $("#sendOTP").button('loading');
            chkValid();*/
            i = setTimeout(function() {
                pressed = false;
            }, 2000);
            return false;
        }
    });
    function resetCaptcha() {
        var count = 0;
        $(".g-recaptcha").each(function () {
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset(count);
                count++;
            }
        });
    }

    function sendOTPMethod(method) {
        $('#otp_verification_method').removeClass('active');
        otp_verification_method = method;
        sendOTP(method); 
    }
</script>
</body>
</html>
