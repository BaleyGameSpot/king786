<?php //print_r($_REQUEST);?>

<form name="frmsignup" id="frmsignupc" method="post" action="signup_a.php" class="clearfix">

    <!--------------------------------------------->

 <!--   <?php /*echo $_REQUEST['type']; */?>

    <?php /*if(in_array($_REQUEST['type'] , ['company' , 'tracking_company'])){ */?>

    <div class="partation form-header">

        <h1 id = "form-header-title" >Compnay</h1>

        <div class="btn-hold">

            <input id = "company-register-block-back-btn" type="button" name="SUBMIT" class="submit" value="Back">

        </div>

    </div>

    <hr>



        <div class="form-header form-header-back-btn">

            <h1 id="form-header-title"></h1>

            <div class="btn-hold">

                <input id="company-register-block-back-btn" type="button" name="SUBMIT" class="submit" value="Back">

            </div>

        </div>

    --><?php /*} */?>



    <div class="form-header form-header-back-btn">

        <h1 id="form-header-title"></h1>

        <div class="btn-hold">

            <input id="company-register-block-back-btn" type="button" name="SUBMIT" class="submit" value="Back">

        </div>

    </div>

    <!--------------------------------------------->
    <?php if ($error != "" && ($_REQUEST['user_type'] == 'company' || $_REQUEST['user_type'] == 'restaurant1' || $_REQUEST['user_type'] == 'restaurant' || $_REQUEST['user_type'] == 'store' || $_REQUEST['user_type'] == 'tracking_company')) { ?>

        <div class="row">

            <div class="col-sm-12 alert alert-danger">

                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                <?= $var_msg; ?>

            </div>

        </div>

    <?php } ?>
    <div class="partation">
        
        <h1><?= $langage_lbl['LBL_ACC_INFO']; ?></h1>

        <!--<div class="radio-holding-row">

            <strong><?= $langage_lbl['LBL_ARE_YOU_AN_INDIVIDUAL']; ?></strong>

            <div class="flexible-row">

                <label><?= $langage_lbl['LBL_Member_Type:']; ?>:</label>

                <div class="label-data-hold">

                    <span class="radio-holder">

                        <input type="radio" id="11" name="user_type" value="driver" onChange="show_companyc(this.value);" >

                        <span class="radio-box"></span>

                    </span><label for="1"><?= $langage_lbl['LBL_SIGNUP_INDIVIDUAL_DRIVER']; ?></label>

                </div>

                <div class="label-data-hold">

                    <span class="radio-holder">

                        <input type="radio" id="22" name="user_type" value="company" onChange="show_companyc(this.value);" class="" checked="checked">

                        <span class="radio-box"></span>

                    </span><label for="2"><?= $langage_lbl['LBL_COMPANY_SIGNIN']; ?></label>

                </div>

            </div>

        </div>-->

        <input type="hidden" id="22" name="user_type" value="company">

        <div class="radio-holding-row" style="display:none">

            <div class="flexible-row">

                <label><?= $langage_lbl['LBL_Member_Type:']; ?>:</label>

                <div class="label-data-hold">

                    <span class="radio-holder">

                        <input type="radio" id="company_store" name="company_store" value="company"

                               onChange="show_company_store(this.value);" class="" checked="checked">

                        <span class="radio-box"></span> </span>

                    <label for="2"><?= $langage_lbl['LBL_COMPANY_SIGNIN']; ?></label>

                </div>

                <div class="label-data-hold">

                    <span class="radio-holder">

                        <input type="radio" id="company_store1" name="company_store" value="store"

                               onChange="show_company_store(this.value);">

                        <span class="radio-box"></span> </span>

                    <label for="1"><?= $langage_lbl['LBL_RESTAURANT_TXT_ADMIN']; ?></label>

                </div>

                <div class="label-data-hold">

                    <span class="radio-holder">

                        <input type="radio" id="tracking_company" name="company_store" value="tracking_company"

                               onChange="show_company_store(this.value);">

                        <span class="radio-box"></span> </span>

                    <label for="1"><?= $langage_lbl['LBL_RESTAURANT_TXT_ADMIN']; ?></label>

                </div>

            </div>

        </div>

        <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>

            <div class="form-group half newrow">

                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?>

                    <span class="red">*</span>

                </label>

                <input type="email" name="vEmailc" class="create-account-input " id="vEmail_verifyc"

                       value="<?php echo $vEmail; ?>" Required/>
					   <span id="" style="color: Red; font-size: 11px;"><?php echo $cemailErr; ?></span>

            </div>

        <? } else { ?>

            <div class="form-group half phone-column newrow ">

                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>

                    <span class="red">*</span>

                </label>

                <!--<select name="vCode">

                    <option value="91">+91</option>

                </select>-->

                <input type="text" name="vCode" readonly class="phonecode testooqq" value="<?php echo $vCode; ?>" id="codec"

                       class="phonecode"/>

                <input required type="text" id="vPhonec" value="<?php echo $vPhone; ?>" placeholder=""

                       class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
					   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($cphonecodeErr) ? $cphonecodeErr : ''; ?></span>
					   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($cphoneErr) ? $cphoneErr : ''; ?></span>

            </div>

        <? } ?>

        <div class="form-group half newrow">

            <div class="relative_ele">

                <label><?= $langage_lbl['LBL_PASSWORD']; ?>

                    <span class="red">*</span>

                </label>

                <input autocomplete="new-password" id="passc" type="password" name="vPassword"

                       class="create-account-input create-account-input1 " required value=""/>

                <!--<button type="button" onclick="showHidePassword('passc')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->

            </div>

        </div>

        <?php if (scount($service_cat_list) <= 1) { ?>

            <input name="iServiceId" type="hidden" class="create-account-input"

                   value="<?php echo $db_service_category[0]['iServiceId']; ?>"/>

        <?php } else { ?>

            <div class="form-group half newrow storedata floating" id="AvilableServiceCategory"  style="display:none">

                <label><?= $langage_lbl['LBL_SELECT_SERVICE_TYPE']; ?>

                    <span class="red">*</span>

                </label>

                <select name="iServiceId">

                    <!--  <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option> -->

                    <?php foreach ($db_service_category as $service_category) { ?>

                        <option name="<?= $service_category['iServiceId'] ?>"

                                value="<?= $service_category['iServiceId'] ?>" <?php echo (isset($iServiceId) && $service_category['iServiceId'] == $iServiceId) ? 'selected="selected"' : ""; ?>><?= $service_category['servicename'] ?></option>

                    <?php } ?>

                </select>

                <input type="hidden" name="eSystem" value="DeliverAll">

            </div>

        <?php } ?>

    </div>

    <div class="partation">

        <h1><?= $langage_lbl['LBL_BASIC_INFO']; ?></h1>

        <div class="form-group half newrow ">

            <label class="comdata"><?= $langage_lbl['LBL_COMPANY_SIGNUP']; ?>

                <span class="red">*</span>

            </label>

            <label class="storedata" style="display:none"><?= $restaurantName; ?>

                <span class="red">*</span>

            </label>

            <!--<input type="text" onkeypress="return IsAlphaNumeric(event, this.id);" id="company_name" name="vCompany" value="<?php echo $vCompany; ?>"  />-->

            <!--<span id="company_name_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>-->

            <input type="text" id="company_name" name="vCompany" value="<?php echo $vCompany; ?>"/>
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($cnameErr) ? $cnameErr : ''; ?></span>

        </div>

        <div class="form-group half newrow comdata">

            <label><?= $langage_lbl['LBL_VAT_NUMBER_SIGNUP']; ?></label>

            <input name="vVat" type="text" class="create-account-input" value="<?php echo $vVat; ?>"/>

        </div>

        <!--<div class="form-group half newrow storedata" style="display:none">

        <label><?= $langage_lbl['LBL_SIGNUP_STORE_NAME']; ?></label>

        <input type="text" id="company_name" class="create-account-input" name="vCompany" value="<?php echo $vCompany; ?>"  />

        </div>-->

        <div class="form-group half newrow storedata" style="display:none">

            <label><?= $langage_lbl['LBL_RES_CONTACT_PERSON_NAME']; ?></label>

            <input name="vContactName" type="text" class="create-account-input" value="<?php echo $vContactName; ?>"/>
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($ccontactnameErr) ? $ccontactnameErr : ''; ?></span>

        </div>
        <?php if (scount($db_country) > 1) { ?>
            <div class="form-group half newrow floating">

                <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?>

                    <span class="red">*</span>

                </label>

                <select required name='vCountry' id="vCountryc" onChange="setStatec(this.value, '');">

                    <!-- <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option> -->

                    <? for ($i = 0; $i < scount($db_country); $i++) { ?>

                        <option value="<?= $db_country[$i]['vCountryCode'] ?>"

                                <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>

                    <? } ?>

                </select>
			<span id="" style="color: Red; font-size: 11px;"><?php echo isset($ccountryErr) ? $ccountryErr : ''; ?></span>

            </div>
        <?php } else { ?>
            <input type="hidden" name="vCountry" id="vCountryc" value="<?php echo $db_country[0]['vCountryCode']?>">
        <?php } ?>
        <div class="form-group half newrow floating">

            <label id="selectstatelblc"></label>

            <select name='vState' id="vStatec" onChange="setCityc(this.value, '');">

                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_STATE_TXT']; ?></option>

            </select>

        </div>

        <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>

            <div class="form-group half newrow floating">

                <label><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_CITY_TXT']; ?> </label>

                <select name='vCity' id="vCityc">

                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option>

                </select>

            </div>

        <?php } ?>

        <div class="form-group half newrow extraAddress">

            <label><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?>

                <span class="red">*</span>

            </label>

            <input name="vCaddress" type="text" class="create-account-input" value="<?php echo $vCaddress; ?>"/>

        </div>

        <div style="display: none" class="form-group half newrow autoCompleteAddress">

            <label><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?>

                <span class="red">*</span>

            </label>

            <input id="vLocation" name="vLocation" type="text" class="create-account-input" value=""/>

            <input type="hidden" name="vLatitude" id="vLatitude" value="<?php echo $vLatitude; ?>">

            <input type="hidden" name="vLongitude" id="vLongitude" value="<?php echo $vLongitude; ?>">

        </div>

        <div class="form-group half newrow" style="display:none">

            <label class="comdata"><?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?>

                <!-- <span class="red">*</span> -->

            </label>

            <label class="storedata" style="display:none"><?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?></label>

            <input name="vZip" type="text" class="create-account-input create-account-input1"

                   value="<?php echo $vZip; ?>"/>

        </div>

        <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>

            <div class="form-group half phone-column newrow">

                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>

                    <span class="red">*</span>

                </label>

                <!--<select name="vCode">

                    <option value="91">+91</option>

                </select>-->

                <input type="text" name="vCode" readonly class="phonecode" value="<?php echo $vCode; ?>" id="codec"

                       class="phonecode"/>

                <input required type="text" id="vPhonec" value="<?php echo $vPhone; ?>" placeholder=""

                       class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
                       <span id="" style="color: Red; font-size: 11px;"><?php echo isset($cphonecodeErr) ? $cphonecodeErr : ''; ?></span>
                       <span id="" style="color: Red; font-size: 11px;"><?php echo isset($cphoneErr) ? $cphoneErr : ''; ?></span>
            </div>

        <? } else { ?>

            <div class="form-group half newrow">

                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?></label>

                <input type="email" name="vEmailc" class="create-account-input " id="vEmail_verifyc"

                       value="<?php echo $vEmail; ?>"/>
					   <span id="" style="color: Red; font-size: 11px;"><?php echo isset($cemailErr) ? $cemailErr : ''; ?></span>

            </div>

        <? } ?>

        <div class="form-group  captcha-column newrow captchauser">

            <?php //include_once("recaptcha.php"); ?>

        </div>

        <div class="onethird check-combo">

            <div class="check-main newrow">

                <span class="check-hold">

                    <input type="checkbox" name="remember-me" id="c1" value="remember">

                    <span class="check-button"></span> </span>

            </div>

            <label for="c1"><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?>

                <a href="terms-condition" target="_blank"><?= $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a>

            </label>

        </div>

        <div class="button-block">

            <div class="btn-hold">

                <input type="submit" name="SUBMIT" class="submit" value="<?= $langage_lbl['LBL_REGISTER_SMALL']; ?>"/>

                <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">

            </div>

            <div class="member-txt">

                <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?>

                <a href="sign-in?type=company" tabindex="5" id="signinlink"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>

            </div>
        </div>

    </div>


    <input type='reset' class='resetform' value='reset' style="display:none"/>

</form>