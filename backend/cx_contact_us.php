<?
include_once("common.php");

$meta_arr = $STATIC_PAGE_OBJ->FetchSeoSetting(2);
$sql = "SELECT * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
$sql = "SELECT * from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
$sql = "SELECT c.vValue,co.vPhoneCode from configurations as c LEFT JOIN country as co on co.vCountryCode=c.vValue where vName = 'DEFAULT_COUNTRY_CODE_WEB'";
$defaultcountryArry = $obj->MySQLSelect($sql);
$DefaultvPhoneCode = $defaultcountryArry[0]['vPhoneCode'];
//$Data['vFirstName'] = validName($_POST['vName']);



$script = "Contact Us";


if (isset($_POST['form_submit']) && $_POST['form_submit'] != "") {

    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);
        if ($valiedRecaptch) {

			$check = validateformfields();
			if (isset($check['vName']) || isset($check['vLastName']) || isset($check['vPhone']) || isset($check['vCountry']) || isset($check['vPhoneCode']) || isset($check['vCode']) || isset($check['vEmail'])) {
				$_SESSION['postDetail'] = $_REQUEST;
				$_SESSION['nameErr'] = $check['vName'];
				$_SESSION['phoneErr'] = $check['vPhone'];
				$_SESSION['lastnameErr'] = $check['vLastName'];
				$_SESSION['emailErr'] = $check['vEmail'];
				$_SESSION['countryErr'] = $check['vCountry'];
				$_SESSION['phonecodeErr'] = $check['vPhoneCode'];
				header("Location:" . $tconfig["tsite_url"] . "cx_contact_us.php");
				exit;
			}

            $Data['vFirstName'] = validName($_POST['vName']);
            $Data['vLastName'] = stripcslashes($_POST['vLastName']);
            $Data['EMAIL_NAME'] = $Data['vFirstName'] . ' ' . $Data['vLastName'];
            $Data['eSubject'] = stripcslashes($_POST['vSubject']);
            $Data['tSubject'] = nl2br(stripcslashes($_POST['vDetail']));
            $Data['vEmail'] = $_POST['vEmail'];
            $Data['cellno'] = $_POST['phone_code']." ".$_POST['vPhone'];



            /*------------------not allow text-----------------*/
            $AllowToSendMail = 1;
            $disallowedFields = [$Data['vEmail'], $Data['EMAIL_NAME'], $Data['eSubject'], $Data['tSubject']];
            if(disallowWordArr($disallowedFields)){
                $AllowToSendMail = 0;
            }
            /*------------------not allow text-----------------*/

            if (strtoupper(SITE_TYPE) == "LIVE" && $AllowToSendMail == 1) {
                $return = $COMM_MEDIA_OBJ->SendMailToMember("CONTACTUS", $Data);
            } else {
                $return = true;
             }
            if ($return) {
                $success = 1;
                $var_msg = $langage_lbl['LBL_SENT_CONTACT_QUERY_SUCCESS_TXT'];
                //added by SP on 22-01-2021 to save data of contactus
                $datainsert = array();
                $datainsert["vFirstname"] = $Data['vFirstName'];
                $datainsert["vLastname"] = $Data['vLastName'];
                $datainsert["vEmail"] = $Data['vEmail'];
                $datainsert["vPhone"] = $Data['cellno'];
                $datainsert["vSubject"] = $Data['eSubject'];
                $datainsert["tDescription"] = $Data['tSubject'];
                if (!empty($_SESSION['sess_iUserId'])) {
                    $datainsert["iMemberId"] = $_SESSION['sess_iUserId'];
                    if ($_SESSION['sess_user'] == 'rider') $usertype = "Passenger";
                    else if ($_SESSION['sess_user'] == 'driver') $usertype = "Driver";
                    else if ($_SESSION['sess_user'] == 'company') $usertype = "Company";
                    $datainsert["eUserType"] = $usertype;
                    $datainsert["eSystem"] = $_SESSION['sess_eSystem'];
                }
                $datainsert["tRequestDate"] = @date("Y-m-d H:i:s");

                if($AllowToSendMail == 1)
                {
                    $id = $obj->MySQLQueryPerform("contactus",$datainsert,'insert');
                }

            } else {
                $error = 1;
                $var_msg = $langage_lbl['LBL_ERROR_OCCURED'];
            }
        } else {
            $error = 1;
            $var_msg = 'Recaptch verification failed, please try again.';
        }
    } else {
        $error = 1;
        $var_msg = 'Please check reCAPTCHA box.';
    }
    header("Location:contact-us?msg=" . $var_msg . "&success=" . $success . "&error=" . $error);
    exit;
}

/*$nameErr = isset($_SESSION['nameErr']) ? $_SESSION['nameErr'] : '';
if(isset($_SESSION['nameErr'])) {
	unset($_SESSION['nameErr']);
}

$lastnameErr = isset($_SESSION['lastnameErr']) ? $_SESSION['lastnameErr'] : '';
if(isset($_SESSION['lastnameErr'])) {
	unset($_SESSION['lastnameErr']);
}

$phoneErr = isset($_SESSION['phoneErr']) ? $_SESSION['phoneErr'] : '';
if(isset($_SESSION['phoneErr'])) {
	unset($_SESSION['phoneErr']);
}

$emailErr = isset($_SESSION['emailErr']) ? $_SESSION['emailErr'] : '';
if(isset($_SESSION['emailErr'])) {
	unset($_SESSION['emailErr']);
}

$phonecodeErr = isset($_SESSION['phonecodeErr']) ? $_SESSION['phonecodeErr'] : '';
if(isset($_SESSION['phonecodeErr'])) {
	unset($_SESSION['phonecodeErr']);
}*/

// Define an array of session variable names for errors
$errorFields = ['nameErr', 'lastnameErr', 'phoneErr', 'emailErr', 'phonecodeErr'];

// Loop through each error field, set the variable, and unset the session variable
foreach ($errorFields as $field) {
    $$field = isset($_SESSION[$field]) ? $_SESSION[$field] : '';
    if (isset($_SESSION[$field])) {
        unset($_SESSION[$field]);
    }
}


if (isset($_REQUEST['msg']) && $_REQUEST['msg'] != "") {
    $success = $_REQUEST['success'];
    $error = $_REQUEST['error'];
    $var_msg = $_REQUEST['msg'];
}

//echo $var_msg;die;
if (isset($_SESSION['sess_user'])) {
    $userid = $_SESSION['sess_iUserId'];
    $rider_query = "SELECT * from register_user where iUserId = $userid";

    $rider_data = $obj->MySQLSelect($rider_query);
    $driver_query = "SELECT * from register_driver where iDriverId = $userid";
    $driver_data = $obj->MySQLSelect($driver_query);
}

?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?= $SITE_NAME ?></title>-->
    <title><?php echo $meta_arr['meta_title']; ?></title>
    <meta name="keywords" content="<?= $meta_arr['meta_keyword']; ?>"/>
    <meta name="description" content="<?= $meta_arr['meta_desc']; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <?php include_once("top/validation.php"); ?>
    <script type="text/javascript" src="<?php echo $tconfig['tsite_url']; ?>assets/js/add_country_code_dropdown.js"></script>
    <!-- End: Default Top Script and css-->
    <style type="text/css">
        .contact-section .countryPhoneSelectWrapper{
            height: 54px;
        }
        .contact-section form .form-group.floating label {
            z-index: 9;
        }
    </style>
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
    <div class="about-main">
        <div class="gen-cms-page" style="padding-bottom: 0;">
            <div class="gen-cms-page-inner" style="min-height: auto;">
                <h2 class="header-page" style="margin-bottom:0"> <?= $langage_lbl['LBL_CONTACT_US_HEADER_TXT']; ?></h2>
            </div>
        </div>
        <div class="main-page-wrap">
            <section class="contact-section">
                <div class="contact-inner">
                    <div class="contact-left">
                        <!-- <h1><?= $langage_lbl['LBL_GET_IN_TOUCH_TXT']; ?></h1> -->
                        <p><?= $langage_lbl['LBL_CONTACT_US_DESC_TXT']; ?></p>
                        <p><?= $langage_lbl['LBL_WELCOME_TO']; ?> <?= $SITE_NAME ?>, <?= $langage_lbl['LBL_CONTACT_US_SECOND_TXT']; ?></p>
                        <div style="clear:both;"></div>
                        <?php if (isset($success) && $success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $var_msg ?>
                            </div>
                            <!--<div class="form-err contactmsg">
                                        <span class="msg_close">&#10005;</span>
                                        <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="successf" ><?= $var_msg ?></p>
                                    </div>-->
                            <?php
                        } else if (isset($error) && $error == 1) {
                            ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= isset($_REQUEST['msg']) ? $_REQUEST['msg'] : ' '; ?>
                            </div>
                        <?php } ?>
                        <div style="clear:both;"></div>
                        <form class="contact-form" name="contactform" id="contactform" method="post" action="">

                            <?php
                            if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') {
                                foreach ($rider_data as $rider_datas) {
                                    ?>

                                    <input value="<?php echo(isset($rider_datas['vPhoneCode']) ? '+'.$rider_datas['vPhoneCode'] : '') ?>" type="hidden" name="phone_code" id="selected_phone_code">
                                    <input value="<?php echo(isset($rider_datas['vCountry']) ? $rider_datas['vCountry'] : '') ?>" type="hidden" name="vCountry_code" id="vCountry_code">
                                    <div class="partation">
                                        <div class="form-group half">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?></label><input
                                                    type="text" name="vName"
                                                    value="<?php echo(isset($rider_datas['vName']) ? $rider_datas['vName'] : '') ?>"
                                                    id="vName">
                                            <!--  onkeypress="return IsAlphaNumeric(event, this.id);" -->
											<span id="" style="color: Red; font-size: 11px;"><?php echo $nameErr;?></span>
                                            <span id="vName_spaveerror"
                                                  style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                        </div>

                                        <div class="form-group half">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?></label><input
                                                    type="text" name="vLastName"
                                                    value="<?php echo(isset($rider_datas['vLastName']) ? $rider_datas['vLastName'] : '') ?>"
                                                    id="vLastName">
                                            <!-- onkeypress="return IsAlphaNumeric(event, this.id);"  -->
											<span id="" style="color: Red; font-size: 11px;"><?php echo $lastnameErr;?></span>
                                            <span id="vLastName_spaveerror"
                                                  style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                        </div>

                                        <div class="form-group half">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?></label><input
                                                    type="email" name="vEmail" autocomplete="off"
                                                    value="<?php echo(isset($rider_datas['vEmail']) ? $rider_datas['vEmail'] : '') ?>">
													<span id="" style="color: Red; font-size: 11px;"><?php echo $emailErr;?></span>
                                        </div>
                                        <div class="form-group half" id="mobile-form">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_777-777-7777'] ?></label><input
                                                    type="text" name="vPhone" id="vPhone"  class="contact_number"
                                                    value="<?php echo(isset($rider_datas['vPhone']) ? $rider_datas['vPhone'] : '') ?>">
													<span id="" style="color: Red; font-size: 11px;"><?php echo $phonecodeErr;?></span>
													  <span id="" style="color: Red; font-size: 11px;"><?php echo $phoneErr;?></span>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
                                foreach ($driver_data as $driver_datas) {
                                    ?>

                                    <input value="<?php echo(isset($driver_datas['vCode']) ?  '+'.$driver_datas['vCode'] : '') ?>" type="hidden" name="phone_code" id="selected_phone_code">

                                    <input value="<?php echo(isset($driver_datas['vCountry']) ? $driver_datas['vCountry'] : '') ?>" type="hidden" name="vCountry_code" id="vCountry_code">
                                    <div class="partation">
                                        <div class="form-group half">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?></label><input
                                                    type="text" name="vName"
                                                    value="<?php echo(isset($driver_datas['vName']) ? $driver_datas['vName'] : '') ?>"
                                                    id="vNameD">
                                            <!-- onkeypress="return IsAlphaNumeric(event, this.id);" -->
											<span id="" style="color: Red; font-size: 11px;"><?php echo $nameErr;?></span>
                                            <span id="vNameD_spaveerror"
                                                  style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                        </div>
                                        <div class="form-group half">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?></label><input
                                                    type="text" name="vLastName"
                                                    value="<?php echo(isset($driver_datas['vLastName']) ? $driver_datas['vLastName'] : '') ?>"
                                                    id="vLastNameD">
                                            <!--  onkeypress="return IsAlphaNumeric(event, this.id);" -->
											<span id="" style="color: Red; font-size: 11px;"><?php echo $lastnameErr;?></span>
                                            <span id="vLastNameD_spaveerror"
                                                  style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                        </div>
                                        <div class="form-group half newrow">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?><span
                                                        class="red">*</span></label>
                                            <input type="email" name="vEmail"
                                                    autocomplete="off">
                                            <span id="" style="color: Red; font-size: 11px;">
                                            <?php echo $emailErr;?></span>
                                        </div>
                                        <div class="form-group half" id="mobile-form">
                                            <label><?= $langage_lbl['LBL_CONTECT_US_777-777-7777'] ?></label><input

                                                    class="contact_number"
                                                    type="text" name="vPhone" id ="vPhone"
                                                    value="<?php echo(isset($driver_datas['vPhone']) ? $driver_datas['vPhone'] : '') ?>">
													<span id="" style="color: Red; font-size: 11px;"><?php echo $phonecodeErr;?></span>
													  <span id="" style="color: Red; font-size: 11px;"><?php echo $phoneErr;?></span>
                                           <!-- <p>Please enter the mobile number with a leading +.</p>-->
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <input value="<?php echo  '+'.$DefaultvPhoneCode; ?>" type="hidden" name="phone_code" id="selected_phone_code">
                                <div class="partation">
                                    <div class="form-group half newrow">
                                        <label><?= $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?><span
                                                    class="red">*</span></label><input type="text" name="vName"
                                                                                       id="vNameO">
                                        <!-- onkeypress="return IsAlphaNumeric(event, this.id);" -->
										<span id="" style="color: Red; font-size: 11px;"><?php echo $nameErr;?></span>
                                        <span id="vNameO_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                    </div>
                                    <div class="form-group half newrow">
                                        <label><?= $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?><span
                                                    class="red">*</span></label><input type="text" name="vLastName"
                                                                                       id="vLastNameO">
                                        <!-- onkeypress="return IsAlphaNumeric(event, this.id);" -->
										<span id="" style="color: Red; font-size: 11px;"><?php echo $lastnameErr;?></span>
                                        <span id="vLastNameO_spaveerror"
                                              style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                    </div>
                                    <div class="form-group half newrow">
                                        <label><?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?><span
                                                    class="red">*</span></label>
                                        <input type="email" name="vEmail"
                                                                                       autocomplete="off">
										<span id="" style="color: Red; font-size: 11px;">
                                            <?php echo $emailErr;?></span>
																					   </div>
                                    <div class="form-group half newrow">
                                        <label><?= $langage_lbl['LBL_CONTECT_US_777-777-7777'] ?><span
                                                    class="red">*</span></label><input type="text"  class="contact_number" name="vPhone" id="vPhone">
												<span id="" style="color: Red; font-size: 11px;"><?php echo $phonecodeErr;?></span>
													  <span id="" style="color: Red; font-size: 11px;"><?php echo $phoneErr;?></span>
												</div>
                                </div>
                            <?php } ?>
                            <div class="form-group newrow">
                                <label><?= $langage_lbl['LBL_ADD_SUBJECT_HINT_CONTACT_TXT']; ?><span
                                            class="red">*</span></label><input type="text" name="vSubject" id="Subject"><span
                                        id="Subject_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                            </div>
                            <div class="form-group textarea newrow">
                                <label><?= $langage_lbl['LBL_ENTER_DETAILS_TXT']; ?><span class="red">*</span></label>
                                <textarea name="vDetail" cols="61" rows="5" id="tDetails"></textarea>
                                <span id="tDetails_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                            </div>
                            <div class="form-group  captcha-column newrow">
                                <?php include_once("recaptcha.php"); ?>
                            </div>
                            <!--<div class="captcha-column">-->
                            <!--    <div class="form-group newrow">-->
                            <!--        <label>Captcha</label>-->
                            <!--        <input type="text" size="5" maxlength="5" id="POST_CAPTCHA" name="POST_CAPTCHA">-->
                            <!--    </div>-->
                            <!--    <span>-->
                            <!--        <img src="captcha_code_file.php?rand=<?php echo rand(); ?>" id='captchaimg' alt=""> <?= $langage_lbl['LBL_CAPTCHA_CANT_READ_SIGNUP']; ?> <a href="javascript: refreshCaptcha();"><?= $langage_lbl['LBL_CLICKHERE_SIGNUP']; ?></a>-->
                            <!--    </span>-->
                            <!--</div>-->
                            <div class="button-block">
                                <div class="btn-hold">
                                    <input type="submit" value="<?= $langage_lbl['LBL_BTN_CONTECT_US_SUBMIT_TXT']; ?>"
                                           name="form_submit" id="form_submit"/>
                                    <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="contact-right">

                        <?php if(isset($COMPANY_ADDRESS) && !empty($COMPANY_ADDRESS)){ ?>
                        <div class="cont-det-block">
                            <strong><?= $langage_lbl['LBL_ADDRESS_SIGNUP']; ?></strong>
                            <address>
                                <i class="fa fa-map-marker"></i><?= $COMPANY_ADDRESS ?>
                            </address>
                        </div>
                        <?php } ?>

                        <?php if ( (isset($SUPPORT_PHONE) && !empty($SUPPORT_PHONE)) || (isset($SUPPORT_MAIL) && !empty($SUPPORT_MAIL)) ){ ?>
                        <div class="cont-det-block">
                            <strong><?= $langage_lbl['LBL_PHONE_FRONT_FOOTER']; ?>
                                &amp; <?= $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?></strong>
                            <ul>
                                <?php if( !empty($SUPPORT_PHONE) ){ ?>
                                <li>
                                    <i class="fa fa-phone"></i>
                                    <a href="tel:+<?= $SUPPORT_PHONE; ?>"><?= $SUPPORT_PHONE; ?></a>
                                </li>
                                <?php } ?>
                                <!--<li><a href="tel:18001234567">1-800-1234-567</a></li>-->
                                <?php if( !empty($SUPPORT_MAIL) ){ ?>
                                <li><i class="fa fa-envelope"></i><a
                                            href="mailto:<?= $SUPPORT_MAIL; ?>"><?= $SUPPORT_MAIL; ?></a></li>
                                <?php } ?>

                            </ul>
                        </div>

                        <?php } ?>

                    </div>
                </div>
            </section>
            <!-- home page end-->
            <!-- footer part -->

        </div>
    </div>
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
    <?php if ($template != 'taxishark') { ?>
</div>
<?php } ?>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<?php
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script type="text/javascript">
    getPhoneCodeInTextBox('vPhone', 'CountryCode', function () {
        countryCodeByDefultShow();
    });

    $('#vPhone').on('keyup', function (e) {
        $(this).val($(this).val().replace(/\D/g, ''));
    });

    $('#contactform').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
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
            e.parent().find('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            vName: {
                required: true,
                ValidText: true,
                normalizer: function (value) {
                    return $.trim(value);
                }
            },
                            vLastName: {required: true,ValidText:true,
                normalizer: function (value) {
                    return $.trim(value);
                }
            },
            vSubject: {required: true,ValidText:true,notEmpty: true},
            vDetail: {required: true,ValidText:true,notEmpty: true},
            vEmail: {required: true, email: true},
            vPassword: {required: true, minlength: 6},
            vPhone: {required: true, minlength: 3},
            'g-recaptcha-response': {
                required: function (e) {
                    if (grecaptcha.getResponse() == '') {
                        $('#recaptcha-msg').css('display', 'block');
                        return true;
                    } else {
                        $('#recaptcha-msg').css('display', 'none');
                        return false;
                    }
                }
            },
        },
        messages: {
            vName:{ValidText: '<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB']); ?>'},
            vLastName:{ValidText: '<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB']); ?>'},
            vSubject:{ValidText: '<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB']); ?>'},
            vDetail:{ValidText: '<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB']); ?>'},
            //POST_CAPTCHA: {remote: '<?= addslashes($langage_lbl['LBL_CAPTCHA_MATCH_MSG']); ?>'}
        },
        submitHandler: function (form) {

            if($("#CountryCode").find(':selected').attr('data-code')) {
                 $("#selected_phone_code").val($("#CountryCode").find(':selected').attr('data-code'));
            }

            if ($(form).valid()) {
                ShpSq6fAm7(form);
                form.submit();
            }
            return false; // prevent normal form posting
        }
    });

    $.validator.addMethod("notEmpty", function(value, element) {
        return value.trim() !== '';
    }, "This field is required.");
    
    function countryCodeByDefultShow(){
        const mobileFrom = document.getElementById('mobile-form');
        $this = $('.countryPhoneSelectWrapper');
        $.each($this, function (i, v) {
            if (mobileFrom && mobileFrom.contains(this)) {
                $(this).show();
                $(this).closest(".vPhoneNumber").addClass('phoneinput');
            }
        });


        $("#CountryCode").val($("#vCountry_code").val());


    }

    function floatingStatus()
    {
        var GOON = 1;

        if($('#vPhone').val() != "")
        {
            $(this).closest('.form-group').addClass('floating');
            GOON = 0;
        }
        return GOON;
    }

    function validate_email(id) {
        var eml = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        result = eml.test(id);
        if (result == true) {
            $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
            return false;
        }
    }

    function changeCode(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': "id=" + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            } else {
                //console.log(response.result);
            }
        });
    }

    function validate_mobile(mobile) {
        var eml = /^[0-9]+$/;
        result = eml.test(mobile);
        if (result == true) {
            $('#mobileCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('#mobileCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Mobile No</i>');
            $('input[type="submit"]').attr('disabled', 'disabled');
            return false;
        }
    }

    function refreshCaptcha() {
        var img = document.images['captchaimg'];
        img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
        $('#POST_CAPTCHA').val('');
    }

    $(document).ready(function () {
        if ($('.alert').html() != '') {
            setTimeout(function () {
                $('.alert').fadeOut();
            }, 4000);
        }
    });
    var specialKeys = new Array();

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

   /* function triggerValidation(){
        $('#g-recaptcha-response-error').remove();
    }*/
</script>
<!-- End: Footer Script -->
<!-- Powered by V3Cube.com -->
</body>
</html>
