<?php
include_once('common.php');
/*------------------ajax-----------------*/

$_REQUEST['ENABLE_DEBUG'] = 1;
$GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
$GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : '';
$CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
$AuthenticateMember = isset($_REQUEST["AuthenticateMember"]) ? $_REQUEST["AuthenticateMember"] : '';
$signIn = isset($_REQUEST["signIn"]) ? $_REQUEST["signIn"] : '';
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';

$otpVerification = isset($_REQUEST["otpVerification"]) ? $_REQUEST["otpVerification"] : '';
$isOtpVerifyDone = isset($_REQUEST["isOtpVerifyDone"]) ? $_REQUEST["isOtpVerifyDone"] : '';
if (isset($otpVerification) && !empty($otpVerification)) {
    $otp = isset($_REQUEST["otp"]) ? $_REQUEST["otp"] : '';
    $data = $DELETE_ACCOUNT_OBJ->AuthenticateMemberWithOtp($GeneralMemberId, $GeneralUserType, $email, $CountryCode, $otp , $isOtpVerifyDone);
    if ($data['Action'] == 1) {
        $_SESSION['DELETE_ACCOUNT_VERIFY'] = 1;
        $arr['screen'] = 'deleteAccountConform';
        $arr['Details'] = $data['Details'];
    } else {
        $arr['screen'] = 'OTP';
        $arr['error'] =  $langage_lbl[$data['message']];
        $arr['error_code'] = 1;
    }
    echo json_encode($arr);
    exit;
}
if (isset($signIn) && !empty($signIn)) {
    $vPhoneCode = '';
    $phone = '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $phone = $email;
        $email = '';
    }
    $data = $DELETE_ACCOUNT_OBJ->signIn($GeneralMemberId, $GeneralUserType, $phone, $CountryCode, $email);
    if ($data['Action'] == 1 && $data['showEnterOTP'] == 'Yes') {
        $arr['screen'] = 'OTP';
    } else {
        $arr['error'] = $langage_lbl[$data['message']];
        $arr['error_code'] = 1;
    }
    echo json_encode($arr);
    exit;
}
if (isset($AuthenticateMember) && !empty($AuthenticateMember)) {
    $vPhoneCode = '';
    $password = isset($_REQUEST["member_verify_password"]) ? $_REQUEST["member_verify_password"] : '';
    $phone = '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $phone = $email;
        $email = '';
    }
    $data = $DELETE_ACCOUNT_OBJ->AuthenticateMember($GeneralMemberId, $GeneralUserType, $phone, $CountryCode, $email, $password);
    if ($data['Action'] == 1) {
        unset($data['Details']['vPassword']);
        $arr['screen'] = 'deleteAccountConform';
        $arr['Details'] = $data['Details'];
        $_SESSION['DELETE_ACCOUNT_VERIFY'] = 1;
    } else {
        $arr['screen'] = 'Password';
        $arr['error'] = $langage_lbl[$data['message']];
        $arr['error_code'] = 1;
    }

    echo json_encode($arr);
    exit;
}

if (isset($action) && !empty($action) && $action == 'Continue') {
    if($_SESSION['DELETE_ACCOUNT_VERIFY'] == 1) {
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
    }
    $arr['screen'] = 'DeleteSuccess';
    $arr['DeleteSuc'] = 1;
    $arr['DELETE_ACCOUNT_VERIFY'] = $_SESSION['DELETE_ACCOUNT_VERIFY'];
    echo json_encode($arr);
    exit;
    // header('Location: '.$tconfig['tsite_url'].'/success.php?success=1&account_deleted=Yes');
    // exit;
}
/*------------------ajax-----------------*/
if (strtoupper($_SESSION['sess_user']) == strtoupper("rider")) {
    $GeneralUserType = "passenger";
    $GeneralMemberId = $_SESSION['sess_iUserId'];

    $memberData = $obj->MySQLSelect("SELECT vCountry,iUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM register_user WHERE iUserId = '$GeneralMemberId'");

    $email = $memberData[0]['vPhone'];
    $vPhoneCode = $memberData[0]['vPhoneCode'];
    $CountryCode = $memberData[0]['vCountry'];
}
if (strtoupper($_SESSION['sess_user']) == strtoupper("Driver")) {
    $GeneralUserType = "Driver";

    $GeneralMemberId = $_SESSION['sess_iUserId'];

    $memberData = $obj->MySQLSelect("SELECT vCountry, iDriverId, vLang, vPhone, vCode, vEmail, vPassword FROM register_driver WHERE iDriverId = '$GeneralMemberId'");
    $email = $memberData[0]['vPhone'];
    $vPhoneCode = $memberData[0]['vCode'];
    $CountryCode = $memberData[0]['vCountry'];
}

if (strtoupper($_SESSION['sess_user']) == strtoupper("company")) {
    $GeneralUserType = "Company";

    $GeneralMemberId = $_SESSION['sess_iUserId'];

    $memberData = $obj->MySQLSelect("SELECT vCountry, iCompanyId, vLang, vPhone, vCode, vEmail, vPassword FROM company WHERE iCompanyId = '$GeneralMemberId'");
    $email = $memberData[0]['vPhone'];
    $vPhoneCode = $memberData[0]['vCode'];
    $CountryCode = $memberData[0]['vCountry'];
}

$_SESSION['DELETE_ACCOUNT_VERIFY'] = 0;

if (strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE") {
    $OTP_LENGTH = 4;
}else{
    $OTP_LENGTH = 6;
}

?>

<link rel="stylesheet" href="assets/css/account_delete_process_web.css">

<div class="custom-modal-main <?php if($_SESSION['FORM_ACCOUNT_DELETE_URL'] == 1) { echo "active";   } ?>  " id="AccountDelete" tabindex="-1" role="dialog"
     aria-labelledby="receive_driver_counter_offers"
     aria-hidden="true">

    <?php $_SESSION['FORM_ACCOUNT_DELETE_URL'] = 0; ?>
    <div class="custom-modal" role="document">
        <div class="">
            <div class="model-header">
                <h4 class="modal-title white-color"
                    id="inactiveModalLabel"><?php echo $langage_lbl['LBL_DELETE_ACCOUNT_TXT']; ?></h4>
                <i id = "icon-close" class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">

                <?php
                if ($SIGN_IN_OPTION == "Password") {
                    $id = '_password-section'
                    ?>

                    <div id="forPassword">
                        <p style="display: none" id="alert-danger" class="alert-danger"></p>
                        <form  id="<?php echo $id; ?>" method="post" action="javascript:void(0);" class="general-form forPassword">
                            <p class="need-verify-text"> <?php echo $langage_lbl['LBL_ACCOUNT_DELETE_VERIDY_PHONE_NUMBER_TEXT'] ?> ( +<?= $vPhoneCode ?>
                                ) <?= $email ?> .</p>
                            <div class="form-group newrow">
                                <label><?= $langage_lbl['LBL_ENTER_PASSWORD_TXT'] ?> <span class="red">*</span></label>

                                <input type="password" id="member_password" value="" name="member_verify_password" required="">

                            </div>

                            <span style="display: none" class="help-block"></span>
                            <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                            <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                            <input name="email" type="hidden" value="<?php echo $email; ?>">
                            <input name="CountryCode" type="hidden" value="<?php echo $CountryCode; ?>">
                            <input name="AuthenticateMember" type="hidden" value="1">
                            <button onclick="formsubmit('<?php echo $id; ?>');"
                               class="btn gen-btn gen-button"><?= $langage_lbl['LBL_BTN_NEXT_TXT'] ?> <span><img
                                            src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                                            alt=""></span></button>
                        </form>
                    </div>

                <?php } ?>

                <?php
                if ($SIGN_IN_OPTION == "OTP") {
                    /*-----------------forOTP_Sent_Confirm------------------*/
                    $Fromid = '_signin-section';
                    $styleDisplayForOtp = "display: none";
                    if (isset($emailError) && !empty($emailError)) {
                        $styleDisplayForOtp = "display: block";
                    }
                    ?>
                    <div class="alert alert-danger" role="alert" id="error_signing_page"
                         style="<?php echo $styleDisplayForOtp; ?>">
                        <span id="txt_error_signing_page"><?php echo $emailError; ?></span>
                        <span class="close-btn" onclick="closeAlert('error_signing_page')">×</span>
                    </div>
                    <p style="display: none" id="alert-danger" class="alert-danger"></p>
                    <div id="otpForm"></div>
                    <div id="forOTP_Sent_Confirm" >

                        <form  id="<?php echo $Fromid; ?>" method="post" action="javascript:void(0);"
                              class="general-form forOTP_Sent_Confirm">
                            <p class="need-verify-text">
                                <?php
                                echo str_replace(['#PHONE_NO#'],[ '(+ ' . $vPhoneCode .') '. $email ] , $langage_lbl['LBL_DELETE_ACCOUNT_WILL_SEND_OTP_TO_MEMBER_NUMBER_TEXT'])
                                ?>

                            </p>

                            <input type="hidden" value="<?php echo '+' . $vPhoneCode . $email; ?>" name="phoneNumber"
                                   id="phoneNumber" class="form-control">
                            <input name="email" type="hidden" value="<?php echo $email; ?>">
                            <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                            <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                            <input name="CountryCode" type="hidden" value="<?php echo $CountryCode; ?>">
                            <input name="signIn" type="hidden" value="1">

                            <?php
                            if (strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE") {
                                $result = "formsubmit('" . $Fromid . "');";
                            } else {
                                $result = "OTPSend('" . $Fromid . "');";
                            }
                            ?>
                            <button onclick="<?php echo $result; ?>"
                               class=" btn gen-btn gen-button"><?= $langage_lbl['LBL_BTN_NEXT_TXT'] ?> <span><img
                                            src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                                            alt=""></span></button>
                        </form>
                    </div>
                    <!-----------------forOTP_Sent_Confirm------------------>
                    <!------------------OTP----------------->
                    <?php $Fromid = '_verification-section'; ?>
                    <div id="forOTP_Verify" style="display: none">

                        <form id="<?php echo $Fromid; ?>" method="post" action="javascript:void(0);"
                              class="general-form forOTP_Verify">
                            <p class="need-verify-text">
                                <?php echo str_replace(['#PHONE_NO#'],[ '(+ ' . $vPhoneCode .') '. $email ] , $langage_lbl['LBL_DELETE_ACCOUNT_ADD_OTP_SENT_TO_MEMBER_NUMBER_TEXT']);  ?>
                            </p>


                            <div class="form-group newrow">
                               <!-- <label>OTP <span class="red">*</span></label>-->


                                <div class="otp-container"  oninput="getOtpValue()">
                                    <?php
                                    for ($i = 1; $i <= $OTP_LENGTH; $i++) {
                                        echo '<input  onkeydown="handleBackspace(event, ' . $i . ')" oninput="moveToNext(this, ' . $i . ')" type="integer" class="otp-input" maxlength="1" id="otp' . $i . '" />';
                                    }
                                    ?>
                                </div>
                                <input type="hidden" name="otp" id="otp" class="form-control"
                                       maxlength="15">

                            </div>
                            <span style="display: none" class="help-block"></span>
                            <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                            <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                            <input name="email" type="hidden" value="<?php echo $email; ?>">
                            <input name="CountryCode" type="hidden" value="<?php echo $CountryCode; ?>">
                            <input name="otpVerification" type="hidden" value="1">
                            <input id="isOtpVerifyDone" name="isOtpVerifyDone" type="hidden" value="0">

                            <?php
                            if (strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE") {
                                $result = "formsubmit('".$Fromid."');";
                            } else {
                                $result = "VerifyOTP('".$Fromid."');";
                            }
                            ?>
                            <button onclick="<?php echo $result; ?>"
                               class="btn gen-btn gen-button"><?= $langage_lbl['LBL_BTN_NEXT_TXT'] ?> <span><img
                                            src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                                            alt=""></span></button>
                        </form>
                    </div>
                    <!------------------OTP----------------->

                <?php } ?>

                <div style="display: none" id="comfirm-delete-section">
                    <form id="_comfirm-delete-section" name="comfirm-delete-section">
                        <p class="sitename-text"><?= $SITE_NAME ?></p>
                        <div class="profile-section-delete-account">
                            <img id="memberProfileImage" src="">
                            <div class="profile-info">
                                <strong><?= $langage_lbl['LBL_PROFILE_NAME_TXT'] ?></strong>
                                <span id="memberUserName"></span>
                            </div>
                            <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                            <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                        </div>
                        <div class="del-info">
                            <?= str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", $langage_lbl['LBL_ACCOUNT_DELETE_DESC']) ?>

                        </div>
                        <div class="del-info">
                            <?= str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", $langage_lbl['LBL_ACCOUNT_DELETE_RETAIN_INFO']) ?>
                        </div>
                        <div class = "continue_section_btn" >
                            <a onclick="formsubmit('_comfirm-delete-section','Continue');" style="color:white"
                                    class="btn gen-btn"><?= $langage_lbl['LBL_CONTINUE_BTN'] ?></a>
                            <a onclick="formsubmit('_comfirm-delete-section','Cancel');"
                                    class=" btn gen-btn"><?= $langage_lbl['LBL_BTN_CANCEL_TXT'] ?>
                                <span></a>
                        </div>
                    </form>
                </div>

                <div style="display: none" id="delete-account-success">
                    <p><?= $langage_lbl['LBL_ACCOUNT_DELETED_SUCCESS_MSG'] ?> </p>

                    <a id = "ACCOUNT_DELETED_SUCCESS_OK" class="btn gen-btn ok-btn"><?= $langage_lbl['LBL_OK'] ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row loding-action" id="loaderIcon" style="display: none">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<?php
$fdata = GetFileData($tconfig['tsite_script_file_path'] . 'firebase_config.json');
?>
<?php
if ($SIGN_IN_OPTION == "OTP") {
    ?>

    <!--  Firebase  -->
    <script src="https://www.gstatic.com/firebasejs/6.3.3/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/6.3.3/firebase-auth.js"></script>

    <script>
        var LBL_VERIFICATION_CODE_INVALID = "<?php echo $langage_lbl['LBL_VERIFICATION_CODE_INVALID']; ?>"
        const firebaseConfig = <?php echo ($fdata != '') ? $fdata : '{}';?>;
    </script>
    <script src="<?= $tconfig['tsite_url'] ?>assets/js/firebase_phone_verify_account_delete_process.js "
            type="text/javascript"></script>

    <?php
}
?>
<script>

    var tsite_url = '<?php echo $tconfig['tsite_url']; ?>';
    var SIGN_IN_OPTION = '<?php echo $SIGN_IN_OPTION; ?>';
    var OTP_LENGTH = '<?php echo $OTP_LENGTH; ?>';
    var COMPLETE_OTP = "";
    ajaxpagename = 'account_delete_process_web.php';
    var alert = document.getElementById('alert-danger');
    var FORPASSWORD = document.getElementById('forPassword');
    var ALL_ALERT =  document.getElementsByClassName("alert-danger");
    var ICON_CLOSE =  document.getElementById("icon-close");


    var FOROTP_SENT_CONFIRM = document.getElementById('forOTP_Sent_Confirm');
    var FOROTP_VERIFY = document.getElementById('forOTP_Verify');

    var FORPASSWORD_FORM = document.getElementsByClassName('forPassword')[0];
    var FOROTP_SENT_CONFIRM_FORM = document.getElementsByClassName('forOTP_Sent_Confirm')[0];
    var FOROTP_VERIFY_FORM = document.getElementsByClassName('forOTP_Verify')[0];

    var MEMBER_PASSWORD = document.getElementById('member_password');
    var OTP = document.getElementById('otp');


    var COMFIRM_DELETE_SECTION = document.getElementById('comfirm-delete-section');
    var DELETE_ACCOUNT_SUCCESS = document.getElementById('delete-account-success');
    var MEMBER_PROFILE_IMAGE = document.getElementById('memberProfileImage');
    var MEMBER_USERNAME = document.getElementById('memberUserName');
    var AccountDeleteMainDIV = document.getElementById('AccountDelete');
    var PROFILE_DELETE_BTN = document.getElementById('profile_Delete_btn');
    var LOADER_ICON = document.getElementById('loaderIcon');
    var ACCOUNT_DELETED_SUCCESS_OK = document.getElementById('ACCOUNT_DELETED_SUCCESS_OK');
    COMFIRM_DELETE_SECTION.style.display = 'none';

    if (SIGN_IN_OPTION == "Password") {
        MEMBER_PASSWORD.addEventListener("keyup", MEMBER_PASSWORD_CHECK);
    }else{
        OTP.addEventListener("keyup", OTP_CHECK);
    }
    function MEMBER_PASSWORD_CHECK() {

        var helpblock = MEMBER_PASSWORD.parentNode.nextElementSibling;
        allAlertHide();
        if (MEMBER_PASSWORD.value === '') {
            helpblock.innerText = "Please Enter Password."
            helpblock.style.display = 'block';
            enableFrom();
            return false;
        } else {
            helpblock.innerText = ""
            helpblock.style.display = 'none';
        }
        alert.innerText = ""
        alert.style.display = 'none';
    }

    function allAlertHide() {
        var ALLALERT = Array.from(ALL_ALERT);
        ALLALERT.forEach(function (alert) {
            alert.style.display = 'none';
        });
    }

    function OTP_CHECK(FormSubmit) {

        var helpblock = OTP.parentNode.nextElementSibling;

        console.log(OTP.value.length);
        console.log ('-------');
        console.log(OTP_LENGTH);
        allAlertHide();
        if (OTP.value === ''  ) {
            helpblock.innerText = "Please Enter OTP."
            helpblock.style.display = 'block';
            enableFrom();
            return false;
        }else if(FormSubmit == 1 &&  OTP.value.length != OTP_LENGTH ){
            helpblock.innerText = "Please Enter OTP."
            helpblock.style.display = 'block';
            enableFrom();
            return false;
        } else {
            helpblock.innerText = ""
            helpblock.style.display = 'none';
        }


    }

    function AccountDelDivShow() {
        if (SIGN_IN_OPTION == "Password") {
            FORPASSWORD.style.display = 'block';
        } else {
            FOROTP_SENT_CONFIRM.style.display = 'block';
        }
        COMFIRM_DELETE_SECTION.style.display = 'none';
        AccountDeleteMainDIV.classList.add("active");
        enableFrom();
    }

    function AccountDelDivHide() {

        allAlertHide();
        enableFrom();
        if (SIGN_IN_OPTION == "Password") {
            FORPASSWORD.style.display = 'none';
            FORPASSWORD_FORM.reset();

        } else {
            FOROTP_SENT_CONFIRM.style.display = 'none';
            FOROTP_VERIFY.style.display = 'none';
            FOROTP_SENT_CONFIRM_FORM.reset();
            FOROTP_VERIFY_FORM.reset();
        }
        COMFIRM_DELETE_SECTION.style.display = 'none';
        AccountDeleteMainDIV.classList.remove("active");
    }

    function disableFrom() {
        LOADER_ICON.style.display = 'block';
    }

    function enableFrom() {
        LOADER_ICON.style.display = 'none';
    }

    if (PROFILE_DELETE_BTN) {
    PROFILE_DELETE_BTN.onclick = function () {
        enableFrom();
        AccountDelDivShow();
        enableFrom();
    };
    }

    if (ACCOUNT_DELETED_SUCCESS_OK) {
    ACCOUNT_DELETED_SUCCESS_OK.onclick =function (){
        location.reload();
        }
    }

    if (ICON_CLOSE) {
    ICON_CLOSE.onclick = function (){
        AccountDelDivHide()
        }
    }
    //Firebase 
    function OTPSend(formName, action = '') {
        disableFrom();
        sendOTP(formName, action, function () {
            formsubmit(formName, action);
        });
        enableFrom();
    }

    function VerifyOTP(formName, action = '') {

        disableFrom();
        checkVerificationStatus(formName, action, function () {
            $("#isOtpVerifyDone").val('1');
            formsubmit(formName, action);
        });
        enableFrom();
    }

    function closeAlert(id) {
        $('#' + id).hide();
        enableFrom();
    }

    <!--  Firebase  -->
    function formsubmit(formName, action = '') {
        disableFrom();
        var formdata = $("#" + formName).serialize();
        if (action != '') {
            formdata = formdata + '&action=' + action;
            if (action == "Cancel") {
                AccountDelDivHide()
            }
        }
        /*------------------validate-----------------*/

        if(formName === "_password-section" && MEMBER_PASSWORD.value === ''){
            MEMBER_PASSWORD_CHECK();
            return false;
        }else if(formName === "_verification-section" &&  ( OTP.value === '' ||  OTP.value.length != OTP_LENGTH )){
            OTP_CHECK(1);
            return false;
        }else{
            alert.innerText = ""
            alert.style.display = 'none';
        }
        /*------------------validate-----------------*/


        var ajaxData = {
            'URL': tsite_url + ajaxpagename,
            'AJAX_DATA': formdata,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var result = JSON.parse(response.result);
                if (result.error_code == 1) {
                    alert.innerText = result.error
                    alert.style.display = 'block';
                } else if (result.screen == 'deleteAccountConform') {

                    Details = result.Details;

                    alert.style.display = 'none';
                    alert.innerText = '';
                    COMFIRM_DELETE_SECTION.style.display = 'block';
                    if (SIGN_IN_OPTION == "Password") {
                        FORPASSWORD.style.display = 'none';
                    }else{
                        FOROTP_VERIFY.style.display = 'none';
                    }
                    MEMBER_PROFILE_IMAGE.src = Details.userImage;
                    MEMBER_USERNAME.innerText = Details.userName;

                } else if (result.screen == 'OTP') {
                    FOROTP_VERIFY.style.display = 'block';
                    FOROTP_SENT_CONFIRM.style.display = 'none';

                }else if(result.DeleteSuc == 1)
                {
                    COMFIRM_DELETE_SECTION.style.display = 'none';
                    DELETE_ACCOUNT_SUCCESS.style.display = 'block';
                    ICON_CLOSE.style.display = 'none';
                }
            } else {
                console.log(response.result);
            }
            enableFrom();
        });
        return false;
    }


    /*------------------otp get-----------------*/
    function getOtpValue() {



        var completeOtp = '';
        for (var i = 1; i <= OTP_LENGTH; i++) {
            var currentInput = document.getElementById("otp" + i);
            completeOtp += currentInput.value;
        }

        OTP.value = completeOtp;
        OTP_CHECK();
    }

    function moveToNext(input, currentBox) {

        validateNumericInput(input);

        var inputValue = input.value;


        if (inputValue.length === 1) {
           // var nextBox = currentBox % 4 + 1;
            var nextBox = currentBox + 1;
            document.getElementById("otp" + nextBox).focus();
        }
    }
    function handleBackspace(event, currentBox) {
        if (event.key === "Backspace") {
            var currentInput = document.getElementById("otp" + currentBox);

            if (currentInput.value === "") {
                //var prevBox = (currentBox - 2 + 4) % 4 + 1;
                var prevBox = (currentBox - 1);
                if(prevBox != 0) {
                    document.getElementById("otp" + prevBox).focus();
                }
            } else {
                currentInput.value = "";
            }
        }
    }

    function validateNumericInput(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
    }
    /*------------------otp get-----------------*/

</script>
