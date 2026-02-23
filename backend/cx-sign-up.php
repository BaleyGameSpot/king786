<?php
include_once("common.php");

$AUTH_OBJ->AuthMemberRedirect();
$script = "Driver Sign-Up";
$sql = "SELECT * FROM currency WHERE eStatus='Active' ORDER BY iDispOrder ASC";
$db_currency = $obj->MySQLSelect($sql);
$sql = "SELECT * FROM country WHERE eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
$sql = "SELECT * from language_master where eStatus = 'Active' ORDER BY iDispOrder ASC ";
$db_lang = $obj->MySQLSelect($sql);
$sqlUserProfileMaster = "SELECT iUserProfileMasterId,vProfileName FROM user_profile_master WHERE eStatus = 'Active' order by vProfileName  asc";
$dbUserProfileMaster = $obj->MySQLSelect($sqlUserProfileMaster);
if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $CONFIG_OBJ->getConfigurations("configurations", "SHOW_CITY_FIELD");
}
$profileName = "vProfileName_" . $_SESSION['sess_lang'];
$vlangCode = $_SESSION['sess_lang'];
$meta_arr = $STATIC_PAGE_OBJ->FetchSeoSetting(5);
$Mobile = $MOBILE_VERIFICATION_ENABLE;
$error = isset($_REQUEST['error']) ? $_REQUEST['error'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$sql = "SELECT * FROM country WHERE eStatus = 'Active' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);
$vEmail = $vCountry = $vCode = $vPhone = $vFirstName = $vName = $vLastName = $vCompany = $vContactName = $vCaddress = $vCadress2 = $vState = $vCity = $vZip = $vVat = $vCurrencyPassenger = $vContactName = "";
if (isset($_SESSION['postDetail'])) {
    $_REQUEST = $_SESSION['postDetail'];
    $user_type = isset($_REQUEST['user_type']) ? $_REQUEST['user_type'] : 'driver';
    $vEmail = isset($_REQUEST['vEmail']) ? $_REQUEST['vEmail'] : '';
    $vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
    $vCode = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : '';
    $vPhone = isset($_REQUEST['vPhone']) ? $_REQUEST['vPhone'] : '';
    $vFirstName = isset($_REQUEST['vFirstName']) ? $_REQUEST['vFirstName'] : '';
    $vName = isset($_REQUEST['vName']) ? $_REQUEST['vName'] : '';
    $vLastName = isset($_REQUEST['vLastName']) ? $_REQUEST['vLastName'] : '';
    $vCompany = isset($_REQUEST['vCompany']) ? $_REQUEST['vCompany'] : '';
	$vContactName = isset($_REQUEST['vContactName']) ? $_REQUEST['vContactName'] : '';
    $vCaddress = isset($_REQUEST['vCaddress']) ? $_REQUEST['vCaddress'] : '';
    $vCadress2 = isset($_REQUEST['vCadress2']) ? $_REQUEST['vCadress2'] : '';
    $vState = isset($_REQUEST['vState']) ? $_REQUEST['vState'] : '';
    $vCity = isset($_REQUEST['vCity']) ? $_REQUEST['vCity'] : '';
    $vZip = isset($_REQUEST['vZip']) ? $_REQUEST['vZip'] : '';
    $vVat = isset($_REQUEST['vVat']) ? $_REQUEST['vVat'] : '';
    $vCurrencyPassenger = isset($_REQUEST['vCurrencyPassenger']) ? $_REQUEST['vCurrencyPassenger'] : '';
    /*$error = isset($_REQUEST['error']) ? $_REQUEST['error'] : '';
    $var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
     $vDay = isset($_REQUEST['vDay']) ? $_REQUEST['vDay'] : '';
      $vMonth = isset($_REQUEST['vMonth']) ? $_REQUEST['vMonth'] : '';
      $vYear = isset($_REQUEST['vYear']) ? $_REQUEST['vYear'] : ''; */
    unset($_SESSION['postDetail']);
}
if(isset($_SESSION['nameErr'])) {
	$nameErr = isset($_SESSION['nameErr']) ? $_SESSION['nameErr'] : '';
	unset($_SESSION['nameErr']);
}
if(isset($_SESSION['pnameErr'])) {
	$pnameErr = isset($_SESSION['pnameErr']) ? $_SESSION['pnameErr'] : '';
	unset($_SESSION['pnameErr']);
}
if(isset($_SESSION['cnameErr'])) {
	$cnameErr = isset($_SESSION['cnameErr']) ? $_SESSION['cnameErr'] : '';
	unset($_SESSION['cnameErr']);
}
if(isset($_SESSION['onameErr'])) {
	$orgErr = isset($_SESSION['onameErr']) ? $_SESSION['onameErr'] : '';
	unset($_SESSION['onameErr']);
}
if(isset($_SESSION['ophoneErr'])) {
	$orgphoneErr = isset($_SESSION['ophoneErr']) ? $_SESSION['ophoneErr'] : '';
	unset($_SESSION['ophoneErr']);
}
if(isset($_SESSION['lastnameErr'])) {
	$lastnameErr = isset($_SESSION['lastnameErr']) ? $_SESSION['lastnameErr'] : '';
	unset($_SESSION['lastnameErr']);
}
if(isset($_SESSION['plastnameErr'])) {
	$plastnameErr = isset($_SESSION['plastnameErr']) ? $_SESSION['plastnameErr'] : '';
	unset($_SESSION['plastnameErr']);
}
if(isset($_SESSION['phoneErr'])) {
	$phoneErr = isset($_SESSION['phoneErr']) ? $_SESSION['phoneErr'] : '';
	unset($_SESSION['phoneErr']);
}
if(isset($_SESSION['pphoneErr'])) {
	$pphoneErr = isset($_SESSION['pphoneErr']) ? $_SESSION['pphoneErr'] : '';
	unset($_SESSION['pphoneErr']);
}
if(isset($_SESSION['cphoneErr'])) {
	$cphoneErr = isset($_SESSION['cphoneErr']) ? $_SESSION['cphoneErr'] : '';
	unset($_SESSION['cphoneErr']);
}
if(isset($_SESSION['emailErr'])) {
	$emailErr = isset($_SESSION['emailErr']) ? $_SESSION['emailErr'] : '';
	unset($_SESSION['emailErr']);
}
if(isset($_SESSION['pemailErr'])) {
	$pemailErr = isset($_SESSION['pemailErr']) ? $_SESSION['pemailErr'] : '';
	unset($_SESSION['pemailErr']);
}
if(isset($_SESSION['cemailErr'])) {
	$cemailErr = isset($_SESSION['cemailErr']) ? $_SESSION['cemailErr'] : '';
	unset($_SESSION['cemailErr']);
}
if(isset($_SESSION['oemailErr'])) {
	$oemailErr = isset($_SESSION['oemailErr']) ? $_SESSION['oemailErr'] : '';
	unset($_SESSION['oemailErr']);
}
if(isset($_SESSION['ccontactnameErr'])) {
	$ccontactnameErr = isset($_SESSION['ccontactnameErr']) ? $_SESSION['ccontactnameErr'] : '';
	unset($_SESSION['ccontactnameErr']);
}
if(isset($_SESSION['countryErr'])) {
	$countryErr = isset($_SESSION['countryErr']) ? $_SESSION['countryErr'] : '';
	unset($_SESSION['countryErr']);
}
if(isset($_SESSION['pcountryErr'])) {
	$pcountryErr = isset($_SESSION['pcountryErr']) ? $_SESSION['pcountryErr'] : '';
	unset($_SESSION['pcountryErr']);
}
if(isset($_SESSION['ccountryErr'])) {
	$ccountryErr = isset($_SESSION['ccountryErr']) ? $_SESSION['ccountryErr'] : '';
	unset($_SESSION['ccountryErr']);
}
if(isset($_SESSION['ocountryErr'])) {
	$ocountryErr = isset($_SESSION['ocountryErr']) ? $_SESSION['ocountryErr'] : '';
	unset($_SESSION['ocountryErr']);
}
if(isset($_SESSION['phonecodeErr'])) {
	$phonecodeErr = isset($_SESSION['phonecodeErr']) ? $_SESSION['phonecodeErr'] : '';
	unset($_SESSION['phonecodeErr']);
}
if(isset($_SESSION['pphonecodeErr'])) {
	$pphonecodeErr = isset($_SESSION['pphonecodeErr']) ? $_SESSION['pphonecodeErr'] : '';
	unset($_SESSION['pphonecodeErr']);
}
if(isset($_SESSION['cphonecodeErr'])) {
	$cphonecodeErr = isset($_SESSION['cphonecodeErr']) ? $_SESSION['cphonecodeErr'] : '';
	unset($_SESSION['cphonecodeErr']);
}
if(isset($_SESSION['ophonecodeErr'])) {
	$ophonecodeErr = isset($_SESSION['ophonecodeErr']) ? $_SESSION['ophonecodeErr'] : '';
	unset($_SESSION['ophonecodeErr']);
}
$vRefCode = isset($_REQUEST['vRefCode']) ? $_REQUEST['vRefCode'] : '';
if (!empty($_COOKIE['vUserDeviceTimeZone'])) {
    $vUserDeviceTimeZone = $_COOKIE['vUserDeviceTimeZone'];
    $sql = "SELECT vCountryCode,vCurrency FROM country WHERE vTimeZone LIKE '%" . $vUserDeviceTimeZone . "%' AND eStatus = 'Active' ORDER BY  vCountry ASC";
    $db_country_code = $obj->MySQLSelect($sql);
    $db_country_currency = !empty($db_country_code[0]['vCurrency']) ? $db_country_code[0]['vCurrency'] : '';
    if (!empty($db_country_code[0]['vCountryCode'])) {
        $DEFAULT_COUNTRY_CODE_WEB = $db_country_code[0]['vCountryCode'];
    }
}
$sql1 = "select vName from  currency where eStatus='Active' && vName='" . $db_country_currency . "' ORDER BY  iDispOrder ASC";
$dbcountrycurrency = $obj->MySQLSelect($sql1);
$sqldef = "select * from  currency where eStatus='Active' && eDefault='Yes' ORDER BY  iDispOrder ASC";
$db_defcurrency = $obj->MySQLSelect($sqldef);
if (!empty($dbcountrycurrency[0]['vName'])) {
    $defaultCurrency = $dbcountrycurrency[0]['vName'];
}
else {
    $defaultCurrency = $db_defcurrency[0]['vName'];
}
//if(empty($template)) $template = 'Cubex';
$bg_reg_image = "assets/img/page/login-bg.jpg";
$db_reg_src = "assets/img/page/login-img.jpg";
$db_signup = $STATIC_PAGE_OBJ->FetchStaticPage(50, $_SESSION['sess_lang']);
$regpage_title = json_decode($db_signup['page_title'], true);
$regpage_desc = json_decode($db_signup['page_desc'], true);
if (empty($regpage_desc['user_pages']) && empty($regpage_title['user_pages'])) {
    $db_signup = $STATIC_PAGE_OBJ->FetchStaticPage(50, 'EN');
    $regpage_title = json_decode($db_signup['page_title'], true);
    $regpage_desc = json_decode($db_signup['page_desc'], true);
}
if (!empty($db_signup['vImage1'])) $bg_reg_image = "assets/img/page/" . $db_signup['vImage1'];

if (!empty($db_signup['vUserImage'])) $vUserImage = "assets/img/page/" . $db_signup['vUserImage'];
if (!empty($db_signup['vDriverImage'])) $vDriverImage = "assets/img/page/" . $db_signup['vDriverImage'];
if (!empty($db_signup['vCompanyImage'])) $vCompanyImage = "assets/img/page/" . $db_signup['vCompanyImage'];
if (!empty($db_signup['vStoreImage'])) $vStoreImage = "assets/img/page/" . $db_signup['vStoreImage'];
if (!empty($db_signup['vOrgImage'])) $vOrgImage = "assets/img/page/" . $db_signup['vOrgImage'];
if(!empty($db_signup['vTrackingImage'])) $vTrackingImage = "assets/img/page/" . $db_signup['vTrackingImage'];

// if(!empty($db_signup['vImage'])) $db_reg_src = "assets/img/page/".$db_signup['vImage'];
$catdata = serviceCategories;
if(!empty($catdata)){
$service_cat_list = json_decode($catdata, true);
    if(!empty($service_cat_list)){
foreach ($service_cat_list as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "select iServiceId,vServiceName_" . $_SESSION['sess_lang'] . " as servicename from service_categories where iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
$db_service_category = $obj->MySQLSelect($service_category);
    }
}
$company_register_count = 1;
$become_restaurant = '';
if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    if (scount($iServiceIdArr) == 1) {
        if($iServiceIdArr[0] == 1) {
            $become_restaurant = $langage_lbl['LBL_RESTAURANT_TXT'];
        } else {
            $become_restaurant = $langage_lbl['LBL_STORE'];
        }
        $restaurantName = $langage_lbl['LBL_SIGNUP_STORE_NAME'];
    }
    else {
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
$site_type = 0;
if (SITE_TYPE == 'Demo') {
    $site_type = 1;
}
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem(); // Added By HJ On 16-06-2020 For Custome Setup CubejekX Deliverall
if(!(isset($_REQUEST['type']) && in_array($_REQUEST['type'], ['user', 'rider', 'sender', 'provider', 'driver', 'carrier', 'restaurant', 'store', 'company_accounts','company','organization']))) {
    $_REQUEST['type'] = 'user';
}

$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?php echo $meta_arr['meta_title']; ?></title>
    <meta name="keywords" content="<?= $meta_arr['meta_keyword']; ?>"/>
    <meta name="description" content="<?= $meta_arr['meta_desc']; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-social.css">
    <style type="text/css">
        #remember-me-error {
            min-width: 250px;
        }
    </style>

<script>    var SIGN_IN_OPTION = '<?= $SIGN_IN_OPTION ?>';</script>
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
    <?php //include_once("top/header.php"); ?>
    <!-- End: First Section -->
  
    <?php if($onlyRideShareEnable == 'Yes' || $onlyBSREnable == 'Yes'){ 
        include_once("cx-sign-up-only-user.php");
    } else { ?>

        <div class="login-main parallax-window">
            <div class="login-block-heading  login-newblock">
                <div class="login-block-heading-inner">
                    <label class="loginlabel"><?= $langage_lbl['LBL_REGISTER_SMALL'] ?></label>
                    <div class="tabholder login-tabholder">
                        <ul class="tab-switch">
                            <li class="active" data-id="user" data-desc="<?= $regpage_title['user_pages']; ?>">
                                <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_RIDER'] ?></a>
                            </li>
                            <li data-id="provider" data-desc="<?= $regpage_title['provider_pages'] ?>">
                                <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_DRIVER'] ?></a>
                            </li>

                            <?php if (in_array($APP_TYPE, ['Delivery', 'UberX']) || $company_register_count == 1) {  
                                if(strtoupper(ONLYDELIVERALL) != "YES"){?>
                                <li data-id="company" data-desc="<?= $regpage_title['company_pages'] ?>">
                                    <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_COMPANY_SIGNIN'] ?></a>
                                </li>
                            <?php } } ?>
                            <?php if (strtoupper(ONLYDELIVERALL) != "YES" && !$cubeDeliverallOnly && !in_array($APP_TYPE, ['Delivery', 'UberX']) && $company_register_count > 1) { ?>
                                <li data-id="company_accounts" data-desc="<?= $regpage_title['company_pages'] ?>">
                                    <a href="JavaScript:void(0);"><?= $langage_lbl['LBL_COMPANY_SIGNIN'] ?></a>
                                </li>
                            <?php } ?>

                            <?php if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) { ?>
                                <li data-id="restaurant1" data-desc="<?= $regpage_title['company_pages'] ?>">
                                    <a href="JavaScript:void(0);"><?= $become_restaurant; ?></a>
                                </li>
                            <?php } ?>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="login-inner">
                <div class="login-block">
                    <div class="company-register-container">
                        <div class="company-register-title"><?= $langage_lbl['LBL_FIND_RIGHT_ACCOUNT_FOR_YOUR_NEED'] ?></div>
                        <div class="company-register-subtitle"><?= $langage_lbl['LBL_SELECT_OPTION_THAT_DESCRIBES_YOU_THE_BEST'] ?></div>
                        <div class="company-register-section">
                            <a href="javascript:void(0);" id-attr="company" class="company-register-block"
                               style="width: calc(100% / <?= $company_register_count ?>);">
                                <div class="company-register-card">
                                    <img src="<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/page/<?= $db_signup[0]['vSignImage1']; ?>">
                                    <div class="company-register-content">
                                        <div class="company-reg-title"><?= $langage_lbl['LBL_SERVICE_PROVIDER_COMPANY_TXT']; ?></div>
                                        <div class="company-reg-desc">
                                            <?php echo $regpage_title['company_pages']; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) { ?>
                                <!-- <a href="javascript:void(0);" id-attr="store" class="company-register-block"
                                   style="width: calc(100% / <?php //= $company_register_count ?>);">
                                    <div class="company-register-card">
                                        <img src="<?php //= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?php //= $tconfig['tsite_url'] ?>assets/img/store-register.png">
                                        <div class="company-register-content">
                                            <div class="company-register-content">
                                                <div class="company-reg-title"><?php //= $langage_lbl['LBL_RESTAURANT_GROCERY_ETC_STORE_TXT']; ?></div>
                                                <div class="company-reg-desc">
                                                    <?php // echo $regpage_title['restaurant_pages']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a> -->
                            <?php }
                            if ($MODULES_OBJ->isOrganizationModuleEnable()) { ?>
                                <a href="javascript:void(0);" id-attr="organization" class="company-register-block"
                                   style="width: calc(100% / <?= $company_register_count ?>);">
                                    <div class="company-register-card">
                                        <img src="<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/page/<?= $db_signup[0]['vSignImage2']; ?>">
                                        <div class="company-register-content">
                                            <div class="company-reg-title"><?= $langage_lbl['LBL_CORPORATE_ORGANIZATION']; ?></div>
                                            <div class="company-reg-desc">
                                                <?php echo $regpage_title['org_pages']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if ($MODULES_OBJ->isEnableTrackServiceFeature()) { ?>
                                <a href="javascript:void(0);" id-attr="tracking_company" class="company-register-block"
                                   style="width: calc(100% / <?= $company_register_count ?>);">
                                    <div class="company-register-card">
                                        <img src="<?= $tconfig['tsite_url'] ?>resizeImg.php?w=100&src=<?= $tconfig['tsite_url'] ?>assets/img/page/tracking-company-register.png">
                                        <div class="company-register-content">
                                            <div class="company-reg-title"><?= $langage_lbl['LBL_TRACKING_COMPANY']; ?></div>
                                            <div class="company-reg-desc">
                                                <?php echo $regpage_title['trackservice_pages']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="login-left for_reg">
                        <!-- <img src="<?php echo $bg_reg_image; ?>" alt=""> -->
                        <div class=" login-block-footer for-registration">
                            <div class="setImageRatioJS login-caption active" id="user">
                                <?php if (!empty($vUserImage)) { ?>
                                    <img class="resizeImageWithJs"  attr-image-src="<?php echo $vUserImage; ?>" alt="">
                                <?php } ?>
                                <?= $regpage_desc['user_pages']; ?>
                                <p><?= $regpage_title['user_pages']; ?></p>
                            </div>
                            <div class="setImageRatioJS login-caption" id="provider">
                                <?php if (!empty($vDriverImage)) { ?>
                                    <img class="resizeImageWithJs"  attr-image-src="<?php echo $vDriverImage; ?>" alt="">
                                <?php } ?>
                                <?= $regpage_desc['provider_pages']; ?>
                                <p><?= $regpage_title['provider_pages']; ?></p>
                            </div>
                            <div class="setImageRatioJS login-caption" id="company">
                                <?php if (!empty($vCompanyImage)) { ?>
                                    <img class="resizeImageWithJs"  attr-image-src="<?php echo $vCompanyImage; ?>" alt="">
                                <?php } ?>
                                <?= $regpage_desc['company_pages']; ?>
                                <p><?= $regpage_title['company_pages']; ?></p>
                            </div>
                            <div class="setImageRatioJS login-caption" id="restaurant1">
                                <?php if (!empty($vStoreImage)) { ?>
                                    <img class="resizeImageWithJs"  attr-image-src="<?php echo $vStoreImage; ?>" alt="">
                                <?php } ?>
                                <?= $regpage_desc['restaurant_pages']; ?>
                                <p><?= $regpage_title['restaurant_pages']; ?></p>
                            </div>
                            <div class="setImageRatioJS login-caption" id="organization">
                                <?php if (!empty($vOrgImage)) { ?>
                                    <img class="resizeImageWithJs"  attr-image-src="<?php echo $vOrgImage; ?>" alt="">
                                <?php } ?>
                                <?= $regpage_desc['org_pages']; ?>
                                <p><?= $regpage_title['org_pages']; ?></p>
                            </div>
                            <div class="setImageRatioJS login-caption" id="tracking_company1">
                                <?php if (!empty($vTrackingImage)) { ?>
                                    <img class="resizeImageWithJs"  attr-image-src="<?php echo $vTrackingImage; ?>" alt="">
                                <?php } ?>
                                <?= $regpage_desc['trackservice_pages']; ?>
                                <p><?= $regpage_title['trackservice_pages']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="login-right full-width">
                        <div class="login-data-inner">
                            <input type="hidden" placeholder="" name="userType" id="userType" class="create-account-input"
                                   value="user"/>
                            <!-- <p>Lorem Ipsum is simply dummy text of the printing and type setting industry. Lorem Ipsum has been the industry's.</p> -->
                            <div class="gen-forms user active">
                                <form name="frmsignup" id="frmsignup" action="signuprider_a.php" method="POST">
                                    <?php if ($error != "" && ($_REQUEST['type'] == 'user' || empty($_REQUEST['type']))) { ?>
                                        <div class="row">
                                            <div class="col-sm-12 alert alert-danger">
                                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">
                                                    ×
                                                </button>
                                                <?= $var_msg; ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="partation">
                                        <h1><?= $langage_lbl['LBL_ACC_INFO'] ?></h1>
                                        <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                                            <div class="form-group half newrow">
                                                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?>
                                                    <span class="red">*</span>
                                                </label>
                                                <input type="email" name="vEmail" class="create-account-input"
                                                       id="vEmail_verify" value="<?php echo $vEmail; ?>" Required/>
													   <span id="" style="color: Red; font-size: 11px;"><?php echo $emailErr;?></span>
                                            </div>
                                        <?php } else { ?>
                                            <div class="form-group half phone-column newrow">
                                                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>
                                                    <span class="red">*</span>
                                                </label>
                                                <!--<select name="vPhoneCode" id="code">
                                                    <option value="91">+91</option>
                                                </select>-->
                                                <input type="text" name="vPhoneCode" readonly id="code" class="phonecode"/>
												
                                                <input required type="text" id="vPhone" value="<?php echo $vPhone; ?>"
                                                       class="create-account-input create-account-input1 vPhone_verify"
                                                       name="vPhone"/>
													   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($phonecodeErr) ? $phonecodeErr : '';?></span>
													   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($phoneErr) ? $phoneErr : '';?></span>
                                            </div>
                                        <?php } ?>
                                        <div class="form-group half newrow">
                                            <div class="relative_ele">
                                                <label><?= $langage_lbl['LBL_PASSWORD']; ?>
                                                    <span class="red">*</span>
                                                </label>
                                                <input autocomplete="new-password" id="pass" type="password"
                                                       name="vPassword" class="create-account-input create-account-input1 "
                                                       required value=""/>
                                                <!--<button type="button" onclick="showHidePassword('pass')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
                                            </div>
                                        </div>
                                        <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                                            <div class="form-group half newrow">
                                                <div id="refercodeCheck">
                                                    <label id="referlbl"><?= $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?></label>
                                                    <input id="vRefCode" type="text" name="vRefCode"
                                                           class="create-account-input create-account-input1 vRefCode_verify"
                                                           value="<?php echo $vRefCode; ?>"
                                                    />
                                                    <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"
                                                           class="create-account-input" value=""/>
                                                    <input type="hidden" placeholder="" name="eRefType" id="eRefType"
                                                           class="create-account-input" value=""/>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="partation">
                                        <h1><?= $langage_lbl['LBL_BASIC_INFO'] ?></h1>
                                        <div class="form-group half newrow">
                                            <label><?= $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?>
                                                <span class="red">*</span>
                                            </label>
                                            <input name="vName" type="text" class="create-account-input" id="vName"
                                                   value="<?php echo $vName; ?>" required/>
												   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($nameErr) ? $nameErr : '';?></span>
                                            <!-- onkeypress="return IsAlphaNumeric(event, this.id);" -->
                                            <span id="vName_spaveerror" style="color: Red; display: none;font-size: 11px;">*
                                                White space not allowed</span>
                                        </div>
                                        <div class="form-group half newrow">
                                            <label><?= $langage_lbl['LBL_SIGN_UP_LAST_NAME_HEADER_TXT']; ?>
                                                <span class="red">*</span>
                                            </label>
                                            <input name="vLastName" type="text"
                                                   class="create-account-input create-account-input1" id="vLastName"
                                                   value="<?php echo $vLastName; ?>" required/>
												   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($lastnameErr) ? $lastnameErr : '';?></span>
                                            <!-- onkeypress="return IsAlphaNumeric(event, this.id);"  -->
                                            <span id="vLastName_spaveerror"
                                                  style="color: Red; display: none;font-size: 11px;">*
                                                White space not allowed</span>
                                        </div>
                                        <?php if (count($db_country) > 1) { ?>
                                        <div class="form-group half newrow floating">
                                            <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?>
                                                <span class="red">*</span>
                                            </label>
                                            <select class="" required name='vCountry' id="vCountry"
                                                    onChange="setState(this.value, '');changeCurrency(this.value);">
                                                <!--  <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option> -->
                                                <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                                    <option value="<?= $db_country[$i]['vCountryCode'] ?>"
                                                            <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                <?php } ?>
                                            </select>
											<span id="" style="color: Red; font-size: 11px;"><?php echo isset($countryErr) ? $countryErr : '';?></span>
                                        </div>
                                        <?php } else { ?>
                                            <input type="hidden" name="vCountry" id="vCountry" value="<?php echo $db_country[0]['vCountryCode']?>">
                                        <?php } ?>
                                        <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                                            <div class="form-group half phone-column newrow">
                                                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>
                                                    <span class="red">*</span>
                                                </label>
                                                <!--<select name="vPhoneCode" id="code">
                                                    <option value="91">+91</option>
                                                </select>-->
                                                <input type="text" name="vPhoneCode" readonly id="code" class="phonecode"/>
												
                                                <input required type="text" id="vPhone" value="<?php echo $vPhone; ?>"
                                                       class="create-account-input create-account-input1 vPhone_verify"
                                                       name="vPhone"/>
													   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($phonecodeErr) ? $phonecodeErr : '';?></span>
													   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($phoneErr) ? $phoneErr : '';?></span>
                                            </div>
                                        <?php } else { ?>
                                            <div class="form-group half newrow">
                                                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?></label>
                                                <input type="email" name="vEmail" class="create-account-input"
                                                       id="vEmail_verify" value="<?php echo $vEmail; ?>"/>
													   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($emailErr) ? $emailErr : '';?></span>
                                            </div>
                                        <?php } ?>
                                        <?php if (scount($db_lang) > 1) { ?>
                                        <div class="form-group half newrow floating">
                                            <label><?= $langage_lbl['LBL_SELECT_LANGUAGE_TXT']; ?></label>
                                            <select name="vLang" class="">
                                                <?php for ($i = 0; $i < scount($db_lang); $i++) { ?>
                                                    <option value="<?= $db_lang[$i]['vCode'] ?>" <?php
                                                    if ($db_lang[$i]['eDefault'] == 'Yes') {
                                                        echo 'selected';
                                                    }
                                                    ?>>
                                                        <?= $db_lang[$i]['vTitle'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } else { ?>
                                            <input type="hidden" name="vLang" value="<?php echo $db_lang[0]['vCode']?>">
                                        <?php } ?>
                                        <?php if (scount($db_currency) > 1) { ?>
                                        <div class="form-group half newrow floating selectcurrency">
                                            <label><?= $langage_lbl['LBL_SELECT_CURRENCY_SIGNUP']; ?></label>
                                            <select class="" required name='vCurrencyPassenger'>
                                                <?php for ($i = 0; $i < scount($db_currency); $i++) { ?>
                                                    <option value="<?= $db_currency[$i]['vName'] ?>"
                                                            <?php if ($defaultCurrency == $db_currency[$i]['vName']) { ?>selected<?php } ?>>
                                                        <?= $db_currency[$i]['vName'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } else { ?>
                                            <input type="hidden" name="vCurrencyPassenger" value="<?php echo $db_currency[0]['vName']?>">
                                        <?php } ?>
                                        <div class="form-group  captcha-column newrow">
                                            <?php include_once("recaptcha.php"); ?>
                                            <!--<span id="recaptcha-msg" style="display: none;" class="error">This field is required.</span>-->
                                        </div>
                                        <div class="onethird check-combo">
                                            <div class="check-main newrow">
                                                <span class="check-hold">
                                                    <input type="checkbox" name="remember-me" id="c1" value="remember">
                                                    <span class="check-button"></span> </span>
                                            </div>
                                            <label for="c1"><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?>
                                                <a href="terms-condition"
                                                   target="_blank"><?= $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a>
                                            </label>
                                        </div>
                                        <div class="button-block">
                                            <div class="btn-hold">
                                                <input type="submit" name="SUBMIT"
                                                       value="<?= $langage_lbl['LBL_REGISTER_SMALL']; ?>"/>
                                                <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                            </div>
                                            <div class="member-txt">
                                                <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?>
                                                <a href="sign-in" tabindex="5"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes") { ?>
                                        <div class="aternate-login" data-name="OR"></div>
                                        <div class="soc-login-row">
                                            <label><?= $langage_lbl['LBL_REGISTER_WITH_SOCIAL_ACC']; ?></label>
                                            <ul class="social-list" id="rider-social">
                                                <?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes") { ?>
                                                    <!-- <li><a target="_blank" href="facebook-rider/rider" tabindex="6"><img src="assets/img/page/facebook-new.png" alt="Facebook"></a></li> -->
                                                    <li>
                                                        <a target="_blank" href="facebook-rider/rider" tabindex="6"
                                                           class="btn-facebook">
                                                            <img src="assets/img/link-icon/facebook.svg" alt="Facebook"
                                                                 width="25px"><?= $langage_lbl['LBL_CONTINUE_FACEBOOK']; ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                                    <!-- <li><a target="_blank" href="linkedin-rider/rider" tabindex="7"><img src="assets/img/page/linkedin-new.png" alt="Linkedin"></a></li> -->
                                                    <li>
                                                        <a target="_blank" href="linkedin-rider/rider" tabindex="7"
                                                           class="btn-linkedin">
                                                            <img src="assets/img/link-icon/linkedin.svg" alt="Linkdin"
                                                                 width="25px"><?= $langage_lbl['LBL_CONTINUE_LINKEDIN']; ?>
                                                        </a>
                                                    </li>
                                                <?php }
                                                if ($PASSENGER_GOOGLE_LOGIN == "Yes") { ?>
                                                    <!-- <li><a target="_blank" href="google/rider" tabindex="8"><img src="assets/img/page/google-new.png" alt="Google Plus"></a></li> -->
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
                                    <?php } ?>
                                    <input type='reset' class='resetform' value='reset' style="display:none"/>
                                </form>
                            </div>
                            <div class="gen-forms provider"><?php include("cx-sign-up-provider.php"); ?></div>
                            <div class="gen-forms tracking_company company restaurant1"><?php include("cx-sign-up-company.php"); ?></div>
                            <!--<div class="gen-forms restaurant1"><?php //include("cx-sign-up-company.php");   ?></div>-->
                            <div class="gen-forms organization"><?php include("cx-sign-up-org.php"); ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    <?php } ?>
   
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
    <?php if ($template != 'taxishark') { ?>
</div>
<?php } ?>
<!-- footer part end -->
<!-- Footer Script -->
<?php
include_once('top/footer_script.php');
$lang = $LANG_OBJ->getLanguageData($vlangCode)['vLangCode'];
?>
<!--<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>-->
<?php if ($lang != 'en') {
    ?>
    <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
    <?php //include_once('otherlang_validation.php');?><?php } ?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script>


    var usertype = $("input[type=hidden][name=userType]").val();
    var APP_TYPE = '<?= $APP_TYPE; ?>';
    var company_register_count = '<?= $company_register_count ?>';

    $(document).ready(function () {
        setState('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
        setStated('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
        setStatec('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
        setStateo('<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>');
        var refcode = $('#vRefCode').val();
        if (refcode != "") {
            validate_refercode(refcode);
        }
        type = '<?php echo !empty($_REQUEST['type']) ? $_REQUEST['type'] : $_REQUEST['user_type'];?>';
        company_register_count = '<?php echo $company_register_count?>';
        if (type != '') {
            if (type == 'restaurant' || type == 'store') {
                $('.tab-switch li[data-id="restaurant1"]').trigger('click', [{etype: "restaurant1"}]);
            } else if (type == 'rider' || type == 'user' || type == 'sender') {
                $('.tab-switch li[data-id="user"]').get(0).click();
            } else if (type == 'provider' || type == 'driver' || type == 'carrier') {
                $('.tab-switch li[data-id="provider"]').get(0).click();
            } else if (type == 'company' && APP_TYPE == 'UberX') {
                $('.tab-switch li[data-id="company"]').get(0).click();
            } else if (type == "company" && company_register_count > 1) {
                $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "company"}]);
            } else if (type == "company" && company_register_count == 1) {
                $('.tab-switch li[data-id="company"]').trigger('click', [{etype: "company"}]);
            } else if (type == "organization" || type == "org") {
                $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "organization"}]);
            } else if (type == "tracking_company" || type == "tc") {
                $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "tracking_company"}]);
            } else {
                $('.tab-switch li[data-id="' + type + '"]').get(0).click();
            }
        }
        //$(".resetform").click();
        $(".error").html('');
    });
    var specialKeys = [];

    function IsAlphaNumeric(e, inputId) {
        var keyCode = e.keyCode == 0 ? e.charCode : e.keyCode;
        if (keyCode == 32) {
            var ret = ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 65 && keyCode <= 90) || (keyCode >= 97 && keyCode <= 122) || (specialKeys.indexOf(e.keyCode) != -1 && e.charCode != e.keyCode));
            $("#" + inputId + "_spaveerror").show();
            setTimeout(function () {
                $("#" + inputId + "_spaveerror").hide();
            }, 5000);
            return ret;
        }
    }

    $("#company-register-block-back-btn ").on("click", function () {
        //console.log(', #company-register-block-back-btn');
        $('.tab-switch li[data-id="company_accounts"]').trigger('click');
    })
    $(".company-register-block ").on("click", function () {
        $('.captchauser').html('');
        var idattar = $(this).attr("id-attr");
        if (idattar == "store") {
            $('.tab-switch li[data-id="restaurant1"]').trigger('click', [{etype: "restaurant1"}]);
        }
        if (idattar == "company") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "company"}]);
        }
        if (idattar == "organization") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "organization"}]);
        }
        if (idattar == "tracking_company") {
            $('.tab-switch li[data-id="company_accounts"]').trigger('click', [{etype: "tracking_company"}]);
        }
    });
    $(".tab-switch li").on("click", function (e, data) {

        $('.for_reg').show();
        var dataId = $(this).attr("data-id");
        var companyOptionsub = 0;
        try {
            if (data != 'undefined') {
                if (typeof data != '') {
                    dataId = data.etype;
                    companyOptionsub = 1;
                }
            }
        } catch (e) {
        }
      

        $(".company-register-container , .autoCompleteAddress").hide();
        $('.extraAddress').show();
        if (dataId == "company_accounts") {
            $(".company-register-container").show();
            $('.for_reg').hide();
        }
        $("#signinlink").attr("href", dataId);
        if (dataId == 'provider') {
            $("input[type=hidden][name=userType]").val('driver');
            $("input[type=hidden][name=user_type]").val('driver');
            //added to validate referral code on tab change by SP
            var refcode = $('#vRefCoded').val();
            if (refcode != "") {
                validate_refercoded(refcode);
            }

        } else {
            $("input[type=hidden][name=userType]").val(dataId);
            $("input[type=hidden][name=user_type]").val(dataId);
            //added to validate referral code on tab change by SP
            var refcode = $('#vRefCode').val();
            if (refcode != "") {
                validate_refercode(refcode);
            }

        }
        //$(".resetform").click();
        $(".error").html('');
        if (dataId == 'company') {
            //document.getElementById("2").checked=true;
            //$('input[name="user_type"]:checked').trigger('click');
            //$("input:radio:second").click();
            //$("#2").prop("checked", true);

            $('.form-header-back-btn').show();
            if (APP_TYPE == 'UberX' || APP_TYPE == 'Delivery' || company_register_count == 1) {
                $('.form-header-back-btn').hide();
            }
            $('.for_reg').show();
            $('#company_store').click();
            $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_SERVICE_PROVIDER_COMPANY_TXT']); ?>');
            $("#signinlink").attr("href", "sign-in?type=company");
            //added becoz phone number and pwd field large in store only
            $(".restaurant1 .partation:first-child .form-group").addClass("half");
            $(".restaurant1 .partation:first-child .form-group").removeClass("onethird");

        }
        if (dataId == 'tracking_company') {
            $('.form-header-back-btn').show();
            $('#tracking_company').click();
            setTimeout(function () {
                $('#tracking_company1').addClass('active');
            }, 500);

            $('.autoCompleteAddress').show();
            $('.extraAddress').hide();
            $("#signinlink").attr("href", "sign-in?type=tracking_company");
            $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_TRACKING_COMPANY']); ?>');
            $(".restaurant1 .partation:first-child .form-group").addClass("half");
            $(".restaurant1 .partation:first-child .form-group").removeClass("onethird");

        }
        if (dataId == 'restaurant1') {

            $('.form-header-back-btn').hide();
            $('#company_store1').click();
            $("#company_store1").attr('checked', 'checked');
            $("#company_store1").prop("checked", true);
            $("#signinlink").attr("href", "sign-in?type=restaurant");
            //added becoz phone number and pwd field large in store only
            $('#form-header-title').text('<?= addslashes($langage_lbl['LBL_RESTAURANT_GROCERY_ETC_STORE_TXT']); ?>');
            //$(".restaurant1 .partation:first-child .form-group").removeClass("half");
            $(".restaurant1 .partation:first-child .form-group").addClass("half");

        }

        if ((dataId != "company_accounts") || (dataId == "company_accounts" && companyOptionsub == 1)) {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>recaptcha.php?type=1234',
                'AJAX_DATA': "",
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var dataHtml2 = response.result;
                    if (dataHtml2 != "") {
                        var usertype = $("input[type=hidden][name=userType]").val();
                        $('.captchauser').html(dataHtml2);
                    }
                } else {
                    // console.log(response.result);
                }
            });
        }
        var newUser1 = $("input[name=user_type]").val();
        if (newUser1 == 'company') {
            $("#vRefCoded").hide();
            $("#referlbld").hide();
            $('#div-phoned').show();
        } else if (newUser1 == 'driver') {
            $("#vRefCoded").show();
            $("#referlbld").show();
            $('#div-phoned').hide();
        }
        if (dataId == 'restaurant1') {
            <?php if($iServiceIdArr[0] == 1 && scount($iServiceIdArr) == 1) { ?>
            ChangeUrl('Sign-Up', 'sign-up?type=restaurant');
            <?php } else { ?>
            ChangeUrl('Sign-Up', 'sign-up?type=store');
            <?php } ?>
        } else {
            <?php if(($iServiceIdArr[0] == 1 && scount($iServiceIdArr) == 1) || ($THEME_OBJ->isProDeliverallThemeActive() == 'Yes')) { ?>
            if(newUser1 == 'driver'){
                ChangeUrl('Sign-Up', 'sign-up?type=driver');
            } else {
                ChangeUrl('Sign-Up', 'sign-up?type=' + dataId);
            }
            <?php } else { ?>
            ChangeUrl('Sign-Up', 'sign-up?type=' + dataId);
            <?php } ?>

        }
    });

    function ChangeUrl(title, url) {
        if (typeof (history.pushState) != "undefined") {
            var obj = {Title: title, Url: url};
            history.pushState(obj, obj.Title, obj.Url);
        } else {
            alert("Browser does not support HTML5.");
        }
    }

    function show_company_store(user) {
        $("input[type=hidden][name=userType]").val(user);
        $("input[type=hidden][name=user_type]").val(user);
        if (user == 'company') {
            $(".storedata").hide();
            $(".comdata").show();
            $("#frmsignupc").attr('action', 'signup_a.php');
        } else if (user == 'store') {
            $(".storedata").show();
            $(".comdata").hide();
            $("#frmsignupc").attr('action', 'signup_r.php');
        } else if (user == 'tracking_company') {
            $(".storedata").hide();
            $(".comdata").show();
            $("#frmsignupc").attr('action', 'signup_track.php');
        }
    }

    var errormessage;
    // point number 2769 add preventXss method to prevent html code in input field -- SP (01-03-2022)
    $.validator.addMethod("preventXss", function (value, element) {
        if (/<(br|basefont|hr|input|source|frame|param|area|meta|!--|col|link|option|base|img|wbr|!DOCTYPE|a|abbr|acronym|address|applet|article|aside|audio|b|bdi|bdo|big|blockquote|body|button|canvas|caption|center|cite|code|colgroup|command|datalist|dd|del|details|dfn|dialog|dir|div|dl|dt|em|embed|fieldset|figcaption|figure|font|footer|form|frameset|head|header|hgroup|h1|h2|h3|h4|h5|h6|html|i|iframe|ins|kbd|keygen|label|legend|li|map|mark|menu|meter|nav|noframes|noscript|object|ol|optgroup|output|p|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|small|span|strike|strong|style|sub|summary|sup|table|tbody|td|textarea|tfoot|th|thead|time|title|tr|track|tt|u|ul|var|video).*?>|<(video).*?<\/\2>/i.test(value) == true) {
            return false
            e.preventDefault();
        } else {
            return true;
        }
    }, "<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB']); ?>");
    $('#frmsignup').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            vEmail: {
                <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") {?>
                required: true,
                <?php } ?>
                email: true,
                // pattern: /^\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i,//solve issue of 1898 issue sheet,number not allowed after ., if any change then change in this pattern
                remote: {
                    url: 'ajax_validate_email_new.php',
                    type: "post",
                    data: {
                        iDriverId: '',
                        usertype: 'user'
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        //response = response.trim();
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
            vPhone: {
                required: true, minlength: 3, digits: true,
                remote: {
                    url: 'ajax_driver_mobile_new.php',
                    type: "post",
                    data: {
                        iDriverId: '',
                        usertype: function (e) {
                            return $('input[name=userType]').val();
                        },
                        vCountry: function (e) {
                            return $('#vCountry option:selected').val();
                        },
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vRefCode: {
                remote: {
                    url: 'ajax_validate_refercode.php',
                    type: "post",
                    data: {
                        refcode: function (e) {
                            return $('input[name=vRefCode]').val();
                        },
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        vRefCode = $('input[name=refcode]').val();
                        if (vRefCode != '') {

                            if (response == '0') {
                                errormessage = "Invalid referral code";
                                //console.log(response);
                                return false;
                            } else {
                                //console.log(response);
                                var resultdata = response.split('|');
                                $('#iRefUserId').val(resultdata[0]);
                                $('#eRefType').val(resultdata[1]);
                                return true;
                            }
                        } else {
                            return true;

                        }
                    },
                }
            },
            vName: {required: true, minlength: 1, maxlength: 30, preventXss: true, notEmpty: true},
            vLastName: {required: true, minlength: 1, maxlength: 30, preventXss: true, notEmpty: true},
            'g-recaptcha-response': {
              required: function() {
                return grecaptcha.getResponse() == '';
                }
            },
            'remember-me': {required: true},
        },
        messages: {
            vPassword: {
                minlength: '<?= addslashes($langage_lbl['LBL_ERROR_PASS_LENGTH_PREFIX'] . " 6 " . $langage_lbl['LBL_ERROR_PASS_LENGTH_SUFFIX']); ?>'
            },
            vEmail: {
                remote: function () {
                    return errormessage;
                }
            },
            vRefCode: {
                remote: function () {
                    return errormessage;
                }
            },
            'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
            vPhone: {
                minlength: '<?= addslashes($langage_lbl['LBL_INVALID_MOBILE_NO']); ?>',
                //digits: 'Please enter proper mobile number.',
                remote: function () {
                    return errormessage;
                }
            },
            vCompany: {
                //required: 'This field is required.',
                //minlength: 'Company Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            vFirstName: {
                //required: 'This field is required.',
                // minlength: 'First Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            vLastName: {
                //required: 'This field is required.',
                //minlength: 'Last Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
        }
    });

    $.validator.addMethod("notEmpty", function(value, element) {
        return value.trim() !== '';
    }, "This field is required.");

    $('#verification').bind('keydown', function (e) {
        if (e.which == 13) {
            check_verification('verify');
            return false;
        }
    });

    function changeCurrency(id) {
        var userType = 'user';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_currency.php',
            'AJAX_DATA': {id: id, user: userType},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                //document.getElementById("vCurrencyPassenger").value = data;
                $(".selectcurrency").html(dataHtml);
            } else {
                // console.log(response.result);
            }
        });
    }

    function changeCurrencyDriver(id) {
        var userType = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_currency.php',
            'AJAX_DATA': {id: id, user: userType},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                //document.getElementById("vCurrencyPassenger").value = data;
                $(".selectcurrencyDriver").html(dataHtml);
            } else {
                // console.log(response.result);
            }
        });
    }

    function changeCode(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            } else {
                // console.log(response.result);
            }
        });
    }

    /*ajax for unique username*/
    $(document).ready(function () {
        $.validator.addMethod("noSpace", function (value, element) {
            return this.optional(element) || /^\S+$/i.test(value);
        }, "<?= addslashes($langage_lbl['LBL_NO_SPACE_ERROR_MSG']); ?>");
        $("#radio_1").prop("checked", true)
        $("#company_name").removeClass("required");
        var newUser = $("input[name=user_type]:checked").val();
        if (newUser == 'company') {
            //$(".company").show();
            //$(".driver").hide();
            /*$("#li_dob").hide();*/
            $("#vRefCode").hide();
            $("#referlbl").hide();
            $('#div-phone').show();
        } else if (newUser == 'driver') {
            //$(".company").hide();
            //$(".driver").show();
            /*$("#li_dob").show();*/
            $("#vRefCode").show();
            $("#referlbl").show();
            $('#div-phone').hide();
        }
    });

    function validate_refercode(id) {
        if (id == "") {
            return true;
        } else {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_validate_refercode.php',
                'AJAX_DATA': 'refcode=' + id,
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    if (data == 0) {
                        $("#referCheck").remove();
                        $(".vRefCode_verify").addClass('required-active');
                        $('#refercodeCheck').append('<div class="required-label help-block error" id="referCheck" ><?= addslashes($langage_lbl['LBL_INVITE_CODE_INVALID']); ?></div>');
                        $('#vRefCode').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                        // $('#vRefCode').val("");
                        return false;
                    } else {
                        var resultdata = data.split('|');
                        $('#iRefUserId').val(resultdata[0]);
                        $('#eRefType').val(resultdata[1]);
                    }
                } else {
                    // console.log(response.result);
                }
            });
        }
    }

    function refreshCaptcha() {
        var img = document.images['captchaimg'];
        img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
    }

    function setState(id, selected) {
        changeCode(id);
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vState").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                // console.log(response.result);
            }
        });
    }

    function setCity(id, selected) {
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCity").html(dataHtml);
            } else {
                // console.log(response.result);
            }
        });
    }
</script>
<script type="text/javascript">
    $(document).ready(function () {
        var refcode = $('#vRefCoded').val();
        if (refcode != "") {
            validate_refercoded(refcode);
        }
    });
    var errormessage;
    //alert($('input[name=user_type]:checked').val());
    $('#frmsignupd').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            vEmaild: {
                <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") {?>
                required: true,
                <?php } ?>
                email: true,
                // pattern: /^\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i,//solve issue of 1898 issue sheet,number not allowed after ., if any change then change in this pattern
                remote: {
                    url: 'ajax_validate_email_new.php',
                    type: "post",
                    data: {
                        iDriverId: '',
                        usertype: 'driver'
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
            vPhone: {
                required: true, minlength: 3, digits: true,
                remote: {
                    url: 'ajax_driver_mobile_new.php',
                    type: "post",
                    data: {
                        iDriverId: '',
                        usertype: function (e) {
                            //console.log($('input[name=userType]').val());
                            return $('input[name=userType]').val();
                        },
                        vCountry: function (e) {
                            return $('#vCountryd option:selected').val();
                        },
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vFirstName: {required: true, minlength: 1, maxlength: 30, preventXss: true, notEmpty: true},
            vLastName: {required: true, minlength: 1, maxlength: 30, preventXss: true, notEmpty: true},
            vZip: {alphanumeric: true},
            'g-recaptcha-response': {
              required: function() {
                return grecaptcha.getResponse() == '';
                }
            },
            'remember-me': {required: true},
        },
        messages: {
            vPassword: {
                minlength: '<?= addslashes($langage_lbl['LBL_ERROR_PASS_LENGTH_PREFIX'] . " 6 " . $langage_lbl['LBL_ERROR_PASS_LENGTH_SUFFIX']); ?>'
            },
            vEmaild: {
                remote: function () {
                    return errormessage;
                }
            },
            'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
            vPhone: {
                minlength: '<?= addslashes($langage_lbl['LBL_INVALID_MOBILE_NO']); ?>',
                //digits: 'Please enter proper mobile number.',
                remote: function () {
                    return errormessage;
                }
            },
            vCompany: {
                //required: 'This field is required.',
                //minlength: 'Company Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            vFirstName: {
                //required: 'This field is required.',
                //minlength: 'First Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            vLastName: {
                //required: 'This field is required.',
                //minlength: 'Last Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            }
        }
    });
    $('#verificationd').bind('keydown', function (e) {
        if (e.which == 13) {
            check_verification('verify');
            return false;
        }
    });

    function changeCoded(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("coded").value = data;
            } else {
                // console.log(response.result);
            }
        });
    }

    /*ajax for unique username*/
    $(document).ready(function () {
        $("#companyd").hide();
        $("#radio_1d").prop("checked", true)
        $("#company_named").removeClass("required");
        var newUser = $("input[name=user_type]").val();
        $("input[type=hidden][name=userType]").val(newUser);
        if (newUser == 'company') {
            //$(".company").show();
            //$(".driver").hide();
            /*$("#li_dob").hide();*/
            $("#vRefCoded").hide();
            $("#referlbld").hide();
            $('#div-phoned').show();
        } else if (newUser == 'driver') {
            //$(".company").hide();
            //$(".driver").show();
            /*$("#li_dob").show();*/
            $("#vRefCoded").show();
            $("#referlbld").show();
            $('#div-phoned').hide();
        }
        //$("input[type=hidden][name=userType]").val('user');
    });

    function validate_refercoded(id) {
        if (id == "") {
            return true;
        } else {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_validate_refercode.php',
                'AJAX_DATA': 'refcode=' + id,
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    if (data == 0) {
                        $("#referCheckd").remove();
                        $(".vRefCode_verify").addClass('required-active');
                        $('#refercodeCheckd').append('<div class="required-label help-block error" id="referCheckd" >* <?= addslashes($langage_lbl['LBL_INVITE_CODE_INVALID']); ?></div>');
                        //$('#vRefCoded').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                        //$('#vRefCoded').val("");
                        return false;
                    } else {
                        var reponse = data.split('|');
                        $('#iRefUserIdd').val(reponse[0]);
                        $('#eRefTyped').val(reponse[1]);
                    }
                } else {
                    // console.log(response.result);
                }
            });
        }
    }

    function refreshCaptchad() {
        var img = document.images['captchaimgd'];
        img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
    }

    function setStated(id, selected) {
        changeCoded(id);
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vStated").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                // console.log(response.result);
            }
        });
    }

    function setCityd(id, selected) {
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCityd").html(dataHtml);
            } else {
                // console.log(response.result);
            }
        });
    }

    $(document).ready(function () {
        var refcode = $('#vRefCodec').val();
        if (refcode != "") {
            validate_refercodec(refcode);
        }
    });
    var errormessage;
    $('#frmsignupc').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            vEmailc: {
                <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") {?>
                required: true,
                <?php } ?>
                email: true,
                //pattern: /^\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i,//solve issue of 1898 issue sheet,number not allowed after ., if any change then change in this pattern
                remote: {
                    url: 'ajax_validate_email_new.php',
                    type: "post",
                    data: {
                        iDriverId: '',
                        //usertype: ($('input[name=user_type]').val() == 'tracking_company') ? 'tracking_company' : 'company',
                        usertype: function (e) {
                            return ($('input[name=user_type]').val() == 'tracking_company') ? 'tracking_company' : 'company';
                        },
                        usertype_store: function (e) {
                            return $('input[name=user_type]').val();
                        }
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
            vPhone: {
                required: true, minlength: 3, digits: true,
                remote: {
                    url: 'ajax_driver_mobile_new.php',
                    type: "post",
                    data: {
                        iDriverId: '',
                        usertype: function (e) {
                            return $('input[name=user_type]').val();
                        },
                        vCountry: function (e) {
                            return $('#vCountryc option:selected').val();
                        },
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vCompany: {
                required: function (e) {
                    return $('input[name=user_type]').val() == 'company' || $('input[name=user_type]').val() == 'store';
                },
                // minlength: function (e) {
                //     if ($('input[name=user_type]').val() == 'company' || $('input[name=user_type]').val() == 'store') {
                //         return 2;
                //     } else {
                //         return false;
                //     }
                // }, maxlength: function (e) {
                //     //console.log($('input[name=user_type]').val() + "AaaaA");
                //     if ($('input[name=user_type]').val() == 'company' || $('input[name=user_type]').val() == 'store') {
                //         return 30;
                //     } else {
                //         return false;
                //     }
                // },
                preventXss: true,
                notEmpty: true
            },
            vContactName: {
                preventXss: true,
            },
            iServiceId: {
                required: function (e) {
                    if ($('input[name=user_type]').val() == 'store') {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            vCaddress: {
                required: function (e) {
                    if ($('input[name=user_type]').val() != 'tracking_company') {
                        return true;
                    } else {
                        return false;
                    }
                },notEmpty: true

            },
            vLocation: {
                required: function (e) {
                    if ($('input[name=user_type]').val() == 'tracking_company') {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            //vCity: {required: true},
            vZip: {
                /*required: function (e) {
                    if ($('input[name=user_type]').val() == 'company') {
                        return true;
                    } else {
                        return false;
                    }
                },*/
                alphanumeric: true
            },
            // eGender: {required: true},
            'g-recaptcha-response': {
              required: function() {
                return grecaptcha.getResponse() == '';
                }
            },
            'remember-me': {required: true},
        },
        messages: {
            vPassword: {
                //maxlength: 'Please enter less than 16 characters.'
                minlength: '<?= addslashes($langage_lbl['LBL_ERROR_PASS_LENGTH_PREFIX'] . " 6 " . $langage_lbl['LBL_ERROR_PASS_LENGTH_SUFFIX']); ?>'
            },
            vEmailc: {
                remote: function () {
                    return errormessage;
                }
            },
            'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
            vPhone: {
                minlength: '<?= addslashes($langage_lbl['LBL_INVALID_MOBILE_NO']); ?>',
                //digits: 'Please enter proper mobile number.',
                remote: function () {
                    return errormessage;
                }
            },
            vCompany: {
                // required: 'This field is required.',
                // minlength: 'Company Name at least 2 characters long.',
                // maxlength: 'Please enter less than 30 characters.'
            },
            vFirstName: {
                //required: 'This field is required.',
                //minlength: 'First Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            vLastName: {
                // required: 'This field is required.',
                //minlength: 'Last Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            }
        }
    });
    $('#verificationc').bind('keydown', function (e) {
        if (e.which == 13) {
            check_verification('verify');
            return false;
        }
    });

    function changeCodec(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("codec").value = data;
            } else {
                // console.log(response.result);
            }
        });
    }

    /*ajax for unique username*/
    $(document).ready(function () {
        $("#companyc").hide();
        $("#radio_1c").prop("checked", true)
        $("#company_namec").removeClass("required");
        //show_companyd('driver');
        var newUser = $("input[name=user_type]:checked").val();
        //$("input[type=hidden][name=userType]").val(newUser);
        if (newUser == 'company') {
            //$(".company").show();
            //$(".driver").hide();
            /*$("#li_dob").hide();*/
            $("#vRefCodec").hide();
            $("#referlblc").hide();
            $('#div-phonec').show();
        } else if (newUser == 'driver') {
            //$(".company").hide();
            //$(".driver").show();
            /*$("#li_dob").show();*/
            $("#vRefCodec").show();
            $("#referlblc").show();
            $('#div-phonec').hide();
        }

        resizeImage("setImageRatioJS", "attr-image-src" , "imageSrc" , 'invisible' ,'No');

    });

    function validate_refercodec(id) {
        if (id == "") {
            return true;
        } else {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_validate_refercode.php',
                'AJAX_DATA': 'refcode=' + id,
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    if (data == 0) {
                        $("#referCheckc").remove();
                        $(".vRefCode_verify").addClass('required-active');
                        $('#refercodeCheckc').append('<div class="required-label help-block error" id="referCheck" >* <?= addslashes($langage_lbl['LBL_INVITE_CODE_INVALID']); ?></div>');
                        $('#vRefCodec').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                        //$('#vRefCodec').val("");
                        return false;
                    } else {
                        var reponse = data.split('|');
                        $('#iRefUserIdc').val(reponse[0]);
                        $('#eRefTypec').val(reponse[1]);
                    }
                } else {
                    // console.log(response.result);
                }
            });
        }
    }

    function refreshCaptchac() {
        var img = document.images['captchaimgc'];
        img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
    }

    function setStatec(id, selected) {
        changeCodec(id);
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vStatec").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                console.log(response.result);
            }
        });
    }

    function setCityc(id, selected) {
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCityc").html(dataHtml);
            } else {
                // console.log(response.result);
            }
        });
    }

    $('#frmsignupo').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            vEmailo: {
                <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") {?>
                required: true,
                <?php } ?>
                email: true,
                // pattern: /^\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i,//solve issue of 1898 issue sheet,number not allowed after ., if any change then change in this pattern
                remote: {
                    url: 'ajax_validate_email_new.php',
                    type: "post",
                    data: {
                        iOrganizationId: '',
                        usertype: 'organization',
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},
            vPhone: {
                required: true, minlength: 3, digits: true,
                remote: {
                    url: 'ajax_driver_mobile_new.php',
                    type: "post",
                    data: {
                        iOrganizationId: '',
                        usertype: function (e) {
                            return $('input[name=user_type]').val();
                        },
                        vCountry: function (e) {
                            return $('#vCountryo option:selected').val();
                        },
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            vCompany: {required: true, minlength: 1, maxlength: 30, preventXss: true, notEmpty: true},
            iUserProfileMasterId: {required: true},
            vCaddress: {required: true, minlength: 2, notEmpty: true},
            // vZip: {required: true, alphanumeric: true},
            'g-recaptcha-response': {
              required: function() {
                return grecaptcha.getResponse() == '';
                }
            },
            'remember-me': {required: true},
        },
        messages: {
            vPassword: {
                minlength: '<?= addslashes($langage_lbl['LBL_ERROR_PASS_LENGTH_PREFIX'] . " 6 " . $langage_lbl['LBL_ERROR_PASS_LENGTH_SUFFIX']); ?>'
            },
            vEmailo: {
                remote: function () {
                    return errormessage;
                }
            },
            'remember-me': {required: '<?= addslashes($langage_lbl['LBL_AGREE_TERMS_MSG']); ?>'},
            vPhone: {
                minlength: '<?= addslashes($langage_lbl['LBL_INVALID_MOBILE_NO']); ?>',
                //digits: 'Please enter proper mobile number.',
                remote: function () {
                    return errormessage;
                }
            },
            vCompany: {
                //required: 'This field is required.',
                //minlength: 'Organization Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            iUserProfileMasterId: {
                //required: 'This field is required.'
            }
        }
    });
    $('#verificationo').bind('keydown', function (e) {
        if (e.which == 13) {
            check_verification('verify');
            return false;
        }
    });

    function changeCodeo(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("codeo").value = data;
            } else {
                // console.log(response.result);
            }
        });
    }

    /*ajax for unique username*/
    function refreshCaptchao() {
        var img = document.images['captchaimgo'];
        img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
    }

    function setStateo(id, selected) {
        changeCodeo(id);
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vStateo").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                // console.log(response.result);
            }
        });
    }

    function setCityo(id, selected) {
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCityo").html(dataHtml);
            } else {
                // console.log(response.result);
            }
        });
    }

    var site_type = '<?= $site_type ?>';
    var alert_title = '<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>';
    var alert_content = '<?= addslashes($langage_lbl['LBL_SIGNUP_DEMO_CONTENT']); ?>';
    var okbtn = '<?= addslashes($langage_lbl['LBL_OK']); ?>';
    $("form[name='frmsignup']").submit(function () {
        if (site_type == 1) {
            show_alert(alert_title, alert_content, okbtn, '', '', function (btn_id) {
                $("#custom-alert").removeClass("active");
                return false;
            }, false);
            return false;
        }
    });
</script>
<script>
    var vStateo = $('#vStateo:selected').val();
    if (vStateo === undefined || vStateo === null) {
        $("#selectstatelbl").html('');
    } else {
        $("#selectstatelbl").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) . " " . addslashes($langage_lbl['LBL_STATE_TXT']); ?> ');
    }
    $('#vStateo').on('change', function () {
        if (this.value != '') {
            $("#selectstatelbl").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) . " " . addslashes($langage_lbl['LBL_STATE_TXT']); ?> ');
        } else {
            $("#selectstatelbl").html('');
        }
    });
    var vStated = $('#vStated:selected').val();
    if (vStated === undefined || vStated === null) {
        $("#selectstatelbld").html('');
    } else {
        $("#selectstatelbld").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) . " " . addslashes($langage_lbl['LBL_STATE_TXT']); ?> ');
    }
    $('#vStated').on('change', function () {
        if (this.value != '') {
            $("#selectstatelbld").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) . " " . addslashes($langage_lbl['LBL_STATE_TXT']); ?> ');
        } else {
            $("#selectstatelbld").html('');
        }
    });
    var vStatec = $('#vStatec:selected').val();
    if (vStatec === undefined || vStatec === null) {
        $("#selectstatelblc").html('');
    } else {
        $("#selectstatelblc").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) . " " . addslashes($langage_lbl['LBL_STATE_TXT']); ?> ');
    }
    $('#vStatec').on('change', function () {
        if (this.value != '') {
            $("#selectstatelblc").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) . " " . addslashes($langage_lbl['LBL_STATE_TXT']); ?> ');
        } else {
            $("#selectstatelblc").html('');
        }
    });
    /*--------------------- autoCompleteAddress location ------------------*/
    var selected_u = false;
    $(function () {
        $('#vLocation').keyup(function (e) {
            selected_u = false;
            buildAutoComplete("vLocation", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>", function (latitude, longitude, address) {
                $("#vLatitude").val(latitude);
                $("#vLongitude").val(longitude);
                selected_u = true;
            });
        });
    });
    $('#vLocation').on('focus', function () {
        if ($('#vLatitude').val() == "" || $('#vLongitude').val() == "") {
            selected_u = false;
        }
    }).on('blur', function () {
        setTimeout(function () {
            if (!selected_u) {
                $('#vLocation').val('');
                $('#vLatitude').val('');
                $('#vLongitude').val('');
            }
        }, 500);
    });
    /*--------------------- autoCompleteAddress location ------------------*/
</script>
<script type="text/javascript" src="assets/js/modal_alert.js"></script>
<!-- End: Footer Script -->
</body>
</html>
