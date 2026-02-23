<div class="login-main parallax-window">

    <div class="login-block-heading  login-newblock">
        <div class="login-block-heading-inner">
            <label class="loginlabel"><?= $langage_lbl['LBL_REGISTER_SMALL'] ?></label>

        </div>
    </div>
    <div class="login-inner">
        <div class="login-block">
            <div class="login-left for_reg">
                <img src="<?php echo $vUserImage; ?>" alt="">
                <div class="login-block-footer for-registration">
                    <div class="login-caption active" id="user">
                        <?= $regpage_desc['user_pages']; ?>
                        <p><?= $regpage_title['user_pages']; ?></p>
                    </div>
                </div>
            </div>
            <div class="login-right full-width">
                <div class="login-data-inner">
                    <input type="hidden" placeholder="" name="userType" id="userType" class="create-account-input"
                           value="user"/>
                    <div class="gen-forms user active">
                        <form name="frmsignup" id="frmsignup" action="signuprider_a.php" method="POST">
                            <?php if ($error != "" && ($_REQUEST['type'] == 'user' || empty($_REQUEST['type']))) { ?>
                                <div class="row">
                                    <div class="col-sm-12 alert alert-danger">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
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
                                    </div>
                                <?php } else { ?>
                                    <div class="form-group half phone-column newrow">
                                        <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>
                                            <span class="red">*</span>
                                        </label>
              
                                        <input type="text" name="vPhoneCode" readonly id="code" class="phonecode"/>
                                        <input required type="text" id="vPhone" value="<?php echo $vPhone; ?>"  class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
                                    </div>
                                <?php } ?>
                                <div class="form-group half newrow">
                                    <div class="relative_ele">
                                        <label><?= $langage_lbl['LBL_PASSWORD']; ?>
                                            <span class="red">*</span>
                                        </label>
                                        <input autocomplete="new-password" id="pass" type="password" name="vPassword" class="create-account-input create-account-input1 "
                                               required value=""/>
                                    </div>
                                </div>
                                <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                                    <div class="form-group half newrow">
                                        <strong id="refercodeCheck">
                                            <label id="referlbl"><?= $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?></label>
                                            <input id="vRefCode" type="text" name="vRefCode"
                                                   class="create-account-input create-account-input1 vRefCode_verify"
                                                   value="<?php echo $vRefCode; ?>"
                                            />
                                            <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"
                                                   class="create-account-input" value=""/>
                                            <input type="hidden" placeholder="" name="eRefType" id="eRefType"
                                                   class="create-account-input" value=""/>
                                        </strong>
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
                                           value="<?php echo $vFirstName; ?>" required/>
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

                                    <span id="vLastName_spaveerror"
                                          style="color: Red; display: none;font-size: 11px;">*
                                        White space not allowed</span>
                                </div>
                                <div class="form-group half newrow floating">
                                    <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?>
                                        <span class="red">*</span>
                                    </label>
                                    <select class="" required name='vCountry' id="vCountry"
                                            onChange="setState(this.value, '');changeCurrency(this.value);">
                                        <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                            <option value="<?= $db_country[$i]['vCountryCode'] ?>"
                                                    <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                                    <div class="form-group half phone-column newrow">
                                        <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>
                                            <span class="red">*</span>
                                        </label>
                    
                                        <input type="text" name="vPhoneCode" readonly id="code" class="phonecode"/>
                                        <input required type="text" id="vPhone" value="<?php echo $vPhone; ?>"
                                               class="create-account-input create-account-input1 vPhone_verify"
                                               name="vPhone"/>
                                    </div>
                                <?php } else { ?>
                                    <div class="form-group half newrow">
                                        <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?></label>
                                        <input type="email" name="vEmail" class="create-account-input"
                                               id="vEmail_verify" value="<?php echo $vEmail; ?>"/>
                                    </div>
                                <?php } ?>
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
                                <div class="form-group  captcha-column newrow">
                                    <?php include_once("recaptcha.php"); ?>
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
                            <?php } ?>
                            <input type='reset' class='resetform' value='reset' style="display:none"/>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>