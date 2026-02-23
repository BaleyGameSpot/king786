<?php
include 'common.php';
$GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
$GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : '';
$screen = isset($_REQUEST["screen"]) ? $_REQUEST["screen"] : 'mainSignIn';

if ($SIGN_IN_OPTION == 'Password') {
    $screen = 'Password';
}
$signIn = isset($_REQUEST["signIn"]) ? $_REQUEST["signIn"] : '';
$AuthenticateMember = isset($_REQUEST["AuthenticateMember"]) ? $_REQUEST["AuthenticateMember"] : '';
$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
$CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
$otpVerification = isset($_REQUEST["otpVerification"]) ? $_REQUEST["otpVerification"] : '';
$isOtpVerifyDone = isset($_REQUEST["isOtpVerifyDone"]) ? $_REQUEST["isOtpVerifyDone"] : '';
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
$emailError = '';
$pagename = 'accountdeleteprocess.php';
$ajaxpagename = 'ajax_account_delete_process.php';
if (strtoupper($GeneralUserType) == "PASSENGER") {
    $memberData = $obj->MySQLSelect("SELECT vCountry,iUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM register_user WHERE iUserId = '$GeneralMemberId'");
} elseif (strtoupper($GeneralUserType) == "DRIVER") {
    $memberData = $obj->MySQLSelect("SELECT vCountry, iDriverId, vLang, vPhone, vCode as vPhoneCode, vEmail, vPassword FROM register_driver WHERE iDriverId = '$GeneralMemberId'");
} elseif (strtoupper($GeneralUserType) == "COMPANY") {
    $memberData = $obj->MySQLSelect("SELECT vCountry, iCompanyId, vLang, vPhone, vCode as vPhoneCode , vEmail, vPassword FROM company WHERE iCompanyId = '$GeneralMemberId'");
}elseif (strtoupper($GeneralUserType) == "TRACKING") {
    $memberData = $obj->MySQLSelect("SELECT vCountry,iTrackServiceUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM track_service_users WHERE iTrackServiceUserId = '$GeneralMemberId'");
}
$vPhoneC = '';
if (isset($CountryCode) && !empty($CountryCode)) {
    $vPhoneC_ = $obj->MySQLSelect("SELECT vPhoneCode FROM `country` WHERE vCountryCode = '$CountryCode'");
    $vPhoneC = $vPhoneC_[0]['vPhoneCode'];
}
$vLang = $memberData[0]['vLang'];

/*------------------2024 changes-----------------*/
$vPhoneC = $memberData[0]['vPhoneCode'];
$email = $memberData[0]['vPhone'];
$CountryCode = $memberData[0]['vCountry'];

/*------------------2024 changes-----------------*/
$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);
if (isset($signIn) && !empty($signIn)) {
    $vPhoneCode = '';
    $phone = '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $phone = $email;
        $email = '';
    }
    $data = $DELETE_ACCOUNT_OBJ->signIn($GeneralMemberId, $GeneralUserType, $phone, $CountryCode, $email);
    if ($data['Action'] == 1 && $data['showEnterPassword'] == 'Yes') {
        $screen = 'Password';
    }
    if ($data['Action'] == 1 && $data['showEnterOTP'] == 'Yes') {
        $screen = 'OTP';
    } else {
        $emailError = $languageLabelsArr[$data['message']];
    }
}
if (isset($AuthenticateMember) && !empty($AuthenticateMember)) {
    $vPhoneCode = '';
    $password = isset($_REQUEST["password"]) ? $_REQUEST["password"] : '';
    $phone = '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $phone = $email;
        $email = '';
    }
    $data = $DELETE_ACCOUNT_OBJ->AuthenticateMember($GeneralMemberId, $GeneralUserType, $phone, $CountryCode, $email, $password);
    if ($data['Action'] == 1) {
        $screen = 'deleteAccountConform';
        $Details = $data['Details'];
    } else {
        $screen = 'Password';
        $emailError = $languageLabelsArr[$data['message']];
    }
}
if (isset($otpVerification) && !empty($otpVerification)) {
    $otp = isset($_REQUEST["otp"]) ? $_REQUEST["otp"] : '';
    $data = $DELETE_ACCOUNT_OBJ->AuthenticateMemberWithOtp($GeneralMemberId, $GeneralUserType, $email, $CountryCode, $otp , $isOtpVerifyDone);
    if ($data['Action'] == 1) {
        $screen = 'deleteAccountConform';
        $Details = $data['Details'];
    } else {
        $screen = 'OTP';
        $emailError = $languageLabelsArr[$data['message']];
    }
}
if (isset($action) && !empty($action) && $action == 'Continue') {
    if ($GeneralUserType == "Driver") {
        $DELETE_ACCOUNT_OBJ->updateDriver($GeneralMemberId);
    }
    if ($GeneralUserType == "Company") {
        $DELETE_ACCOUNT_OBJ->updateCompany($GeneralMemberId);
    }
    if ($GeneralUserType == "Tracking") {
        $DELETE_ACCOUNT_OBJ->updateTrackingUser($GeneralMemberId);
    } else {
        $DELETE_ACCOUNT_OBJ->updateUser($GeneralMemberId);
    }
    $screen = 'DeleteSuccess';
    echo 1;
    exit;
    // header('Location: '.$tconfig['tsite_url'].'/success.php?success=1&account_deleted=Yes');
    // exit;
}
/*------------------2024 changes-----------------*/
$vPhoneC = $memberData[0]['vPhoneCode'];
$email = $memberData[0]['vPhone'];
$CountryCode = $memberData[0]['vCountry'];

/*------------------2024 changes-----------------*/

if ($screen == 'mainSignIn') {
    ?>

    <!------------------2023 changes----------------->
    <!--<div id="signin-section">
    <form id="_signin-section" name="signin-section">
        <strong><?php /*= $languageLabelsArr['LBL_SIGN_IN'] */ ?></strong>
        <div class="form-group">
            <label><?php /*= $languageLabelsArr['LBL_ENTER_MOBILE_NO'] */ ?></label>
            <div class="phone-input">
				<input type="text" name="email" id="email" class="form-control" pattern="^[0-9]*$" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" maxlength="15">
				<input name="GeneralUserType" type="hidden" value="<?php /*echo $GeneralUserType; */ ?>">
				<input name="GeneralMemberId" type="hidden" value="<?php /*echo $GeneralMemberId; */ ?>">
				<input name="signIn" type="hidden" value="1">
			</div>
        </div>
        <span> <?php /*echo  $emailError */ ?> </span>
        <a onclick="formsubmit('_signin-section');" class="gen-button"><?php /*= $languageLabelsArr['LBL_BTN_NEXT_TXT'] */ ?> <span><img src="<?php /*= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" */ ?>" alt=""></span></a>
    </form>
</div>-->
    <!------------------2023 changes----------------->
    <!------------------2024 changes----------------->
    <div id="signin-section">
        <form id="_signin-section" name="signin-section">
            <!-- <strong><?php /*= $languageLabelsArr['LBL_SIGN_IN'] */ ?></strong>-->
            <div class="alert alert-danger" role="alert" id="error_signing_page" style="display: none">
                <span id="txt_error_signing_page"></span>
                <span class="close-btn" onclick="closeAlert('error_signing_page')">×</span>
            </div>
            <div class="form-group">
                <label>
                    <?= $languageLabelsArr['LBL_DELETE_ACCOUNT_PROCESS_OTP_TEXT'] ?>
                </label>
                <div class="phone-input">
                    <input readonly disabled type="text"
                           value="<?php echo '( +' . $vPhoneC . ')'; ?> <?php echo $email; ?>" name="emailStatic"
                           id="emailStatic" class="form-control" pattern="^[0-9]*$"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                           maxlength="15">
                    <input type="hidden" value="<?php echo '+' . $vPhoneC . $email; ?>" name="phoneNumber"
                           id="phoneNumber" class="form-control">
                    <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                    <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                    <input name="signIn" type="hidden" value="1">
                </div>
            </div>
            <span> <?php echo $emailError ?> </span>



            <?php

            if (strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE") {
                $result = "formsubmit('_signin-section');";
            }
            else {
                $result = "OTPSend('_signin-section');";

            }

            ?>

            <a  onclick="<?php echo $result; ?>" class="gen-button"><?= $languageLabelsArr['LBL_BTN_NEXT_TXT'] ?>
                <span><img src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>" alt=""></span></a>



        </form>
    </div>
    <!------------------2024 changes----------------->

<?php }
if ($screen == 'Password') { ?>
    <div id="password-section">
        <form id="_password-section" name="password-section">

            <?php
            $styleDisplayForOtp = "display: none";

            if(isset($emailError) && !empty($emailError)){
                $styleDisplayForOtp = "display: block";
            }

            ?>

            <div class="alert alert-danger" role="alert" id="error_signing_page" style="<?php echo $styleDisplayForOtp; ?>">
                <span id="txt_error_signing_page"><?php echo $emailError ?></span>
                <span class="close-btn" onclick="closeAlert('error_signing_page')">×</span>
            </div>

            <p class="email-text"><?= $languageLabelsArr['LBL_ACCOUNT_DELETE_VERIDY_PHONE_NUMBER_TEXT'] ?>
                : <?php echo '( +' . $vPhoneC . ')'; ?> <?php echo $email; ?></p>
            <div class="form-group">
                <label><?= $languageLabelsArr['LBL_ENTER_PASSWORD_TXT'] ?></label>
                <div class="phone-input">
                    <input type="password" name="password" id="password" class="form-control">
                    <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                    <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                    <input name="email" type="hidden" value="<?php echo $email; ?>">
                    <input name="CountryCode" type="hidden" value="<?php echo $CountryCode; ?>">
                    <input name="AuthenticateMember" type="hidden" value="1">

                </div>
            </div>

            <a onclick="formsubmit('_password-section');"
               class="gen-button"><?= $languageLabelsArr['LBL_BTN_NEXT_TXT'] ?> <span><img
                            src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                            alt=""></span></a>
        </form>
    </div>
<?php }
if ($screen == 'OTP') { ?>
    <div id="verification-section">
        <form id="_verification-section" name="verification-section">
            <strong><?= $languageLabelsArr['LBL_TWO_STEP_VERIFICATION_TXT'] ?></strong>

            <?php
            $styleDisplayForOtp = "display: none";

            if(isset($emailError) && !empty($emailError)){
                $styleDisplayForOtp = "display: block";
            }

            ?>

            <div class="alert alert-danger" role="alert" id="error_signing_page" style="<?php echo $styleDisplayForOtp; ?>">
                <span id="txt_error_signing_page"><?php echo $emailError ?></span>
                <span class="close-btn" onclick="closeAlert('error_signing_page')">×</span>
            </div>

            <div class="form-group">
                <label>
                    <?php echo str_replace(['#PHONE_NO#'],[ '(+ ' . $vPhoneC .') '. $email ] , $languageLabelsArr['LBL_DELETE_ACCOUNT_ADD_OTP_SENT_TO_MEMBER_NUMBER_TEXT']);  ?>

                  </label>
                <div class="phone-input">
                    <input placeholder="OTP" type="text" name="otp" id="otp" class="form-control" pattern="^[0-9]*$"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                           maxlength="15">
                    <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                    <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                    <input name="email" type="hidden" value="<?php echo $email; ?>">
                    <input name="CountryCode" type="hidden" value="<?php echo $vPhoneC; ?>">
                    <input name="otpVerification" type="hidden" value="1">
                    <input id="isOtpVerifyDone" name="isOtpVerifyDone" type="hidden" value="0">
                </div>
            </div>

            <?php
            if (strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE") {
                $result = "formsubmit('_verification-section');";

            }
            else {
                $result = "VerifyOTP('_verification-section');";

            }

            ?>

            <a onclick="<?php echo $result; ?>"
               class="gen-button"><?= $languageLabelsArr['LBL_BTN_VERIFY_TXT'] ?> <span><img
                            src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                            alt=""></span></a>
        </form>
    </div>
<?php }
if ($screen == 'deleteAccountConform') { ?>
    <div id="comfirm-delete-section">
        <form id="_comfirm-delete-section" name="comfirm-delete-section">
            <p class="sitename-text"><?= $SITE_NAME ?></p>
            <div class="profile-section">
                <img src="<?php echo $Details['userImage'] ?>">
                <div class="profile-info">
                    <strong><?= $languageLabelsArr['LBL_PROFILE_NAME_TXT'] ?></strong>
                    <span>(<?php echo $Details['userName'] ?> )</span>
                </div>
                <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
            </div>
            <div class="del-info">
                <?= str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", $languageLabelsArr['LBL_ACCOUNT_DELETE_DESC']) ?>
            </div>

            <small><?= str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", $languageLabelsArr['LBL_ACCOUNT_DELETE_RETAIN_INFO']) ?></small>
            <a onclick="formsubmit('_comfirm-delete-section','Continue');" style="color:white"
               class="gen-button justify-center"><?= $languageLabelsArr['LBL_CONTINUE_BTN'] ?></a>
            <a onclick="formsubmit('_comfirm-delete-section','cancel');"
               class="gen-button-white gen-button-negative justify-center"><?= $languageLabelsArr['LBL_BTN_CANCEL_TXT'] ?>
                <span></a>
        </form>
    </div>

<?php }
if ($screen == 'DeleteSuccess') { ?>
    <div id="delete-success"> Your account has been successfully deleted. you will be logged out</div>
<?php } ?>