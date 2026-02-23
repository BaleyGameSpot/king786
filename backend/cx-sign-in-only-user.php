
<div class="login-block-heading login-newblock">
    <div class="login-block-heading-inner">
        <label id="loginlabel" class="loginlabel"><?= $langage_lbl['LBL_LOGIN'] ?></label>
        <label id="forgotlabel" style="display:none"><?= $db_forgot['page_title']; ?></label>
    </div>
</div>
<div class="login-main parallax-window">
    <div class="login-inner">
        <div class="login-block">
 
            <div class="login-left">
                <img src="<?php echo $vUserImage; ?>" alt="">
                <div class="login-caption active" id="user">
                    <?= $loginpage_desc['user_pages']; ?>
                </div>
            </div>
            <div class="login-right" id="login_div">

                <div class="login-data-inner">

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
                        $action_url = 'profile';
                    } else {
                        $action_url = 'dashboard.php';
                    }
                    if (!empty($_SESSION["navigatedPage"])) {
                        $action_url = 'userbooking';
                    }
                    ?>
                    <form method="post" action="#" id="login_box" name="login_form">
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
                                    <input style="width: 100%" tabindex="1" type="text" name="vEmail" id="vPhoneNumber" value=""
                                           class="hotelhide phoneinput" readonly
                                           onfocus="this.removeAttribute('readonly');"/>
                                </div>
                                <div class="button-block">
                                    <div class="btn-hold">
                                        <button type="button" class="btnSubmit" id="sendOTP"
                                                data-loading-text="<?= $langage_lbl['LBL_SENDING_OTP_TXT'] ?>"><?= $langage_lbl['LBL_SEND_OTP_TXT'] ?>
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
                                <div class="form-group1">
                                    <label><?= $langage_lbl['LBL_MOBILE_VERIFICATION_CODE']; ?></label>
                                </div>
                                <br/>
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
                                <div class="button-block">
                                    <div class="btn-hold">
                                        <input tabindex="3" type="submit" class="btnVerify" id="verify"
                                               value="<?= $langage_lbl['LBL_BTN_VERIFY_TXT']; ?>">
                                        <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                    </div>
                                    <div class="countdown"> <?= $langage_lbl['LBL_RESEND_OTP_SIGNIN']; ?> : <span
                                                id="countdown"></span></div>
                                    <div class="member-txt resendcode">
                                        <?= $langage_lbl['LBL_DONT_RECEIVE_CODE_TXT']; ?>
                                        <a href="#" tabindex="5" id="signinlink"
                                           onclick="sendOTP();return false;"> <?= $langage_lbl['LBL_RESEND_OTP_TXT']; ?></a>
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
                                    <a href="javascript:void(0)" onClick="change_heading('forgot');" tabindex="4"
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
                   
                        <?php  if ($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes" || $PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
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
                        <div class="form-group">
                            <label><?= ($ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') ? $langage_lbl['LBL_EMAIL_MOBILE_NO_TXT_MSG'] : 'Email'; ?></label>
                            <input type="<?= ($ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') ? 'text' : 'email' ?>"
                                   name="femail" tabindex="1" id="femail" class="femail" required/>

                        </div>
                        <div class="form-group  captcha-column newrow">
                            <?php include_once("recaptcha.php"); ?>
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
    
        </div>
    </div>
</div>