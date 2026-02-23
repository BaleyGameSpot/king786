<?php if ($error != "" && $_REQUEST['type'] == 'provider') { ?>
    <div class="row">
        <div class="col-sm-12 alert alert-danger">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?= $var_msg; ?>
        </div>
    </div>
<?php } ?>
<form name="frmsignup" id="frmsignupd" method="post" action="signup_a.php" class="clearfix">
    <div class="partation">
        <h1><?= $langage_lbl['LBL_ACC_INFO']; ?></h1>
        <!--<div class="radio-holding-row">
            <strong><?= $langage_lbl['LBL_ARE_YOU_AN_INDIVIDUAL']; ?></strong>
            <div class="flexible-row">
                <label><?= $langage_lbl['LBL_Member_Type:']; ?>:</label>
                <div class="label-data-hold">
                    <span class="radio-holder">
                        <input type="radio" id="1" name="user_type" value="driver"  onChange="show_companyd(this.value);" checked="checked">
                        <span class="radio-box"></span>
                    </span><label for="1"><?= $langage_lbl['LBL_SIGNUP_INDIVIDUAL_DRIVER']; ?></label>
                </div>
                <div class="label-data-hold">
                    <span class="radio-holder">
                        <input type="radio" id="2" name="user_type" value="company" onChange="show_companyd(this.value);" class="">
                        <span class="radio-box"></span>
                    </span><label for="2"><?= $langage_lbl['LBL_COMPANY_SIGNIN']; ?></label>
                </div>
            </div>
        </div>-->
        <input type="hidden" id="1" name="user_type" value="driver">
        <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
        <div class="form-group half newrow">
                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> <span class="red">*</span></label>
                <input type="email" name="vEmaild" class="create-account-input " id="vEmail_verifyd" value="<?php echo $vEmail; ?>"  Required />
				<span id="" style="color: Red; font-size: 11px;"><?php echo $pemailErr;?></span>
        </div>
         <? } else { ?> 
            <div class="form-group half phone-column newrow">
                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>  <span class="red">*</span></label>
                <!--<select name="vCode">
                    <option value="91">+91</option>
                </select>-->
                <input type="text"  name="vCode" readonly value="<?php echo $vCode; ?>" id="coded" class="phonecode"/>
				
                <input required type="text"  id="vPhoned" value="<?php echo $vPhone; ?>" placeholder="" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
				<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pphonecodeErr) ? $pphonecodeErr : '';?></span>
				<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pphoneErr) ? $pphoneErr : '';?></span>
            </div> 
        <? } ?>
            <div class="form-group half newrow">
                <div class="relative_ele">
                <label><?= $langage_lbl['LBL_PASSWORD']; ?> <span class="red">*</span></label>
                <input autocomplete="new-password" id="passd" type="password" name="vPassword" class="create-account-input create-account-input1 " required value="" />
                <!--<button type="button" onclick="showHidePassword('passd')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
            </div>
        </div>
        <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserIdd"  class="create-account-input" value="" />
        <input type="hidden" placeholder="" name="eRefType" id="eRefTyped" class="create-account-input" value=""  />

        <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
            <div class="form-group half newrow"><div id="refercodeCheckd">
                    <label id="referlbld"><?= $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?></label>
                    <input id="vRefCoded" type="text" name="vRefCode" class="create-account-input create-account-input1 vRefCode_verify" value="<?php echo $vRefCode; ?>" onBlur="validate_refercoded(this.value)"/></div></div>
        <?php } ?>
    </div> 
    <div class="partation">
        <h1><?= $langage_lbl['LBL_BASIC_INFO']; ?></h1>
        <div class="form-group half newrow">
            <label><?= $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?>  <span class="red">*</span></label>
            <!--<input name="vFirstName" onkeypress="return IsAlphaNumeric(event, this.id);" type="text" class="create-account-input" id="vFirstNamed" value="<?php echo $vFirstName; ?>" />-->
            <!--<span id="vFirstNamed_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>-->
            <input name="vFirstName" type="text" class="create-account-input" id="vFirstNamed" value="<?php echo $vFirstName; ?>" />
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pnameErr) ? $pnameErr : '';?></span>
        </div>
        <div class="form-group half newrow">
            <label><?= $langage_lbl['LBL_SIGN_UP_LAST_NAME_HEADER_TXT']; ?>  <span class="red">*</span></label>
            <!--<input name="vLastName" onkeypress="return IsAlphaNumeric(event, this.id);" type="text" class="create-account-input create-account-input1" id="vLastNamed" value="<?php echo $vLastName; ?>" />-->
            <!--<span id="vLastNamed_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>-->
            <input name="vLastName" type="text" class="create-account-input create-account-input1" id="vLastNamed" value="<?php echo $vLastName; ?>" />
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($plastnameErr) ? $plastnameErr : '';?></span>
        </div>
        <?php if (scount($db_country) > 1) { ?>
            <div class="form-group half newrow floating">
                <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?> <span class="red">*</span></label>
                <select required name='vCountry' id="vCountryd" onChange="setStated(this.value, '');changeCurrencyDriver(this.value);" >
                   <!--  <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option> -->
                    <? for ($i = 0; $i < scount($db_country); $i++) { ?>
                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                    <? } ?>
                </select>
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pcountryErr) ? $pcountryErr : '';?></span>
        </div>
        <?php } else { ?>
            <input type="hidden" name="vCountry" id="vCountryd" value="<?php echo $db_country[0]['vCountryCode']?>">
        <?php } ?>
        <div class="form-group half newrow floating">
            <label id="selectstatelbld"></label>
            <select name = 'vState' id="vStated" onChange="setCityd(this.value, '');" >
                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_STATE_TXT']; ?> </option>
            </select>
        </div>
        <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
            <div class="form-group half newrow floating">
                <label><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_CITY_TXT']; ?> </label>
                <select name = 'vCity' id="vCityd">
                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option>
                </select>
            </div>
        <?php } ?>
        <div class="form-group half newrow">
            <label><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?></label>
            <input name="vCaddress" type="text" class="create-account-input" value="<?php echo $vCaddress; ?>" />
        </div>
        <div class="form-group half newrow">
            <label><?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?></label>
            <input name="vZip" type="text" class="create-account-input create-account-input1" value="<?php echo $vZip; ?>" />
        </div>
        <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
        <div class="form-group half phone-column newrow">
            <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>  <span class="red">*</span></label>
            <!--<select name="vCode">
                <option value="91">+91</option>
            </select>-->
            <input type="text"  name="vCode" readonly value="<?php echo $vCode; ?>" id="coded" class="phonecode"/>
			
            <input required type="text"  id="vPhoned" value="<?php echo $vPhone; ?>" placeholder="" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pphonecodeErr) ? $pphonecodeErr : '';?></span>
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pphoneErr) ? $pphoneErr : '';?></span>
        </div>
        <? } else { ?>
        <div class="form-group half newrow">
            <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> </label>
            <input type="email" name="vEmaild" class="create-account-input " id="vEmail_verifyd" value="<?php echo $vEmail; ?>"  />
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($pemailErr) ? $pemailErr : '';?></span>
        </div>
        <? } ?>
        <?php if (scount($db_currency) > 1) { ?>
        <div class="form-group half newrow floating selectcurrencyDriver">
         <label><?= $langage_lbl['LBL_SELECT_CURRENCY_SIGNUP']; ?></label>
            <select required name = 'vCurrencyDriver'>
                <?php for ($i = 0; $i < scount($db_currency); $i++) { ?>
                    <option value = "<?= $db_currency[$i]['vName'] ?>" <? if ($defaultCurrency == $db_currency[$i]['vName']) { ?>selected<? } ?>>
                        <?= $db_currency[$i]['vName'] ?>
                    </option>
                <? } ?>
            </select>
        </div>
        <?php } else { ?>
            <input type="hidden" name="vCurrencyDriver" value="<?php echo $db_currency[0]['vName']?>">
        <?php } ?>
        <div class="form-group  captcha-column newrow captchauser">
            <?php //include_once("recaptcha.php"); ?>
        </div>
        <div class="onethird check-combo">
            <div class="check-main newrow">
                <span class="check-hold">
                    <input type="checkbox" name="remember-me" id="c1" value="remember">
                    <span class="check-button"></span>
                </span>
            </div><label for="c1"><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="terms-condition" target="_blank"><?= $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a></label>
        </div>
        <div class="button-block">
            <div class="btn-hold">
                <input type="submit" name="SUBMIT" class="submit" value="<?= $langage_lbl['LBL_REGISTER_SMALL']; ?>"/>
                <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
            </div>
            <div class="member-txt">
                <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="sign-in?type=provider" tabindex="5"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
            </div>
        </div>
    </div>
    <?php if ($DRIVER_GOOGLE_LOGIN == "Yes" || $DRIVER_FACEBOOK_LOGIN == "Yes") { ?>
    <div class="aternate-login" data-name="OR"></div>
    <div class="soc-login-row">
        <label><?= $langage_lbl['LBL_REGISTER_WITH_SOCIAL_ACC']; ?></label>
        <ul class="social-list" id="driver-social">
            <?php if ($DRIVER_FACEBOOK_LOGIN == "Yes") { ?>
            <li><a target="_blank" href="facebook/driver" tabindex="6"  id="facebook-button" class="btn-facebook"><img src="assets/img/link-icon/facebook.svg" alt="Facebook" width="25px"><?= $langage_lbl['LBL_CONTINUE_FACEBOOK']; ?></a></li>
                <!-- <li><a target="_blank" href="facebook/driver" tabindex="6"><img src="assets/img/page/facebook-new.png" alt="Facebook"></a></li> -->
            <?php } if ($DRIVER_LINKEDIN_LOGIN == "Yes") { ?>
             <li><a target="_blank" href="linkedin/driver" tabindex="7"  class="btn-linkedin"><img src="assets/img/link-icon/linkedin.svg" alt="Linkdin" width="25px"><?= $langage_lbl['LBL_CONTINUE_LINKEDIN']; ?></a></li>
                <!-- <li><a target="_blank" href="linkedin/driver" tabindex="7"><img src="assets/img/page/linkedin-new.png" alt="Linkedin"></a></li> -->
            <?php } if ($DRIVER_GOOGLE_LOGIN == "Yes") { ?>
            <li><a href="google/driver" tabindex="8" class="btn-google1"><img src="assets/img/link-icon/btn_google_light_normal_ios.svg" alt="google" ><span class="buttonText"><?= $langage_lbl['LBL_CONTINUE_GOOGLE']; ?></span></a></li>
                <!-- <li><a target="_blank" href="google/driver" tabindex="8"><img src="assets/img/page/google-new.png" alt="Google Plus"></a></li> -->
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    <input type = 'reset' class = 'resetform' value = 'reset' style="display:none"/>
</form>