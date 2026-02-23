<?php
include 'common.php';
$GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
$GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : '';
$screen = isset($_REQUEST["screen"]) ? $_REQUEST["screen"] : 'mainSignIn';
$SIGN_IN_OPTION = 'OTP';
/*------------------2024 changes-----------------*/
/*$SIGN_IN_OPTION
$MOBILE_NO_VERIFICATION_METHOD */
if ($SIGN_IN_OPTION == 'Password') {
    $screen = 'Password';
}
/*------------------2024 changes-----------------*/
$signIn = isset($_REQUEST["signIn"]) ? $_REQUEST["signIn"] : '';
$AuthenticateMember = isset($_REQUEST["AuthenticateMember"]) ? $_REQUEST["AuthenticateMember"] : '';
$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
$CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
$otpVerification = isset($_REQUEST["otpVerification"]) ? $_REQUEST["otpVerification"] : '';
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
$emailError = '';
$pagename = 'accountdeleteprocess.php';
if (strtoupper($GeneralUserType) == "PASSENGER") {
    $memberData = $obj->MySQLSelect("SELECT iUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM register_user WHERE iUserId = '$GeneralMemberId'");
} elseif (strtoupper($GeneralUserType) == "DRIVER") {
    $memberData = $obj->MySQLSelect("SELECT iDriverId, vLang, vPhone, vCode, vEmail, vPassword FROM register_driver WHERE iDriverId = '$GeneralMemberId'");
} elseif (strtoupper($GeneralUserType) == "COMPANY") {
    $memberData = $obj->MySQLSelect("SELECT iCompanyId, vLang, vPhone, vCode, vEmail, vPassword FROM company WHERE iCompanyId = '$GeneralMemberId'");
}elseif (strtoupper($GeneralUserType) == "TRACKING") {
    $memberData = $obj->MySQLSelect("SELECT iTrackServiceUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM track_service_users WHERE iTrackServiceUserId = '$GeneralMemberId'");
}

$vLang = $memberData[0]['vLang'];
$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="expires" content="Sun, 01 Jan 2014 00:00:00 GMT"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <title><?= $languageLabelsArr['LBL_DELETE_ACCOUNT_TXT'] ?></title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,400,500,600,700,800,900&display=swap"
          rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?= $tconfig['tsite_url'] ?>assets/css/add_countrycode_dropdown.css">
    <link rel="stylesheet" type="text/css" href="<?= $tconfig['tsite_url'] ?>assets/css/account_delete_process.css">
    <link rel="stylesheet" href="<?= $tconfig['tsite_url'] ?>assets/css/apptype/<?= $template; ?>/style.less"
          type="text/less">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        document.write('<style type="text/css">body{display:none}</style>');
        jQuery(function ($) {
        <?php if(!empty($SYSTEM_THEME_COLORS)) { ?>
        document.documentElement.style.setProperty('--mainColor', '<?= $SYSTEM_THEME_COLORS['MAIN_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainColorSecond', '<?= $SYSTEM_THEME_COLORS['MAIN_COLOR_SECOND'] ?>');
        document.documentElement.style.setProperty('--mainTextColor', '<?= $SYSTEM_THEME_COLORS['MAIN_TEXT_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainTextColorHover', '<?= $SYSTEM_THEME_COLORS['MAIN_TEXT_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainTextMenuColorHoverDefault', '<?= $SYSTEM_THEME_COLORS['MAIN_TEXT_HOVER_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainColorHover', '<?= $SYSTEM_THEME_COLORS['MAIN_HOVER_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainfilterimg', '<?= $SYSTEM_THEME_COLORS['FILTER_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainfilterimgHover', '<?= $SYSTEM_THEME_COLORS['FILTER_HOVER_COLOR'] ?>');
        document.documentElement.style.setProperty('--buttonColor', '<?= $SYSTEM_THEME_COLORS['BUTTON_COLOR'] ?>');
        document.documentElement.style.setProperty('--mainColorLight', '<?= $SYSTEM_THEME_COLORS['MAIN_COLOR_LIGHT'] ?>');
        document.documentElement.style.setProperty('--gotopBGColor', '<?= $SYSTEM_THEME_COLORS['MAIN_COLOR'] ?>');
        document.documentElement.style.setProperty('--gotopHoverBGColor', '<?= $SYSTEM_THEME_COLORS['MAIN_HOVER_COLOR'] ?>');
        document.documentElement.style.setProperty('--gotopImgColor', '<?= $SYSTEM_THEME_COLORS['MAIN_IMG_COLOR'] ?>');
        document.documentElement.style.setProperty('--gotopHoverImgColor', '<?= $SYSTEM_THEME_COLORS['MAIN_IMG_HOVER_COLOR'] ?>');
        document.documentElement.style.setProperty('--Whiteimagefilter', '<?= $SYSTEM_THEME_COLORS['MAIN_IMG_HOVER_COLOR'] ?>');
        <?php } ?>
            $('body').css('display', 'block');
        });
    </script>

</head>

<body class="account-delete">
<div class="overlay" style="display: block;">
    <div class="overlay__inner" style="display: none;">
        <div class="overlay__content">
            <span class="spinner"></span>
        </div>
    </div>
</div>
<div id="otpForm" ></div>
<div class="container">

</div>
<?php
$fdata = GetFileData($tconfig['tsite_script_file_path'] . 'firebase_config.json');
?>


<script>
    var SITE_PANEL_PATH = '<?= $tconfig["tsite_folder"] ?>';
    function showOverlay(hideLoader = "No", hideContent = "No") {
        $('body').css('overflow', 'hidden');
        $('.overlay__inner').show();
        if (hideLoader == "Yes") {
            $('.overlay__content').hide();
            if (hideContent == "No") {
                $('.overlay').fadeIn();
            } else {
                $('.overlay').show();
            }
        } else {
            $('.overlay').show();
            $('.overlay__content').show();
        }
    }

    function hideOverlay() {
        $('.overlay').hide();
        $('.overlay__content').show();
        $('body').css('overflow', 'auto');
        $('.overlay').removeClass('bg-overlay');
    }
</script>
<?php
if ($SIGN_IN_OPTION == "OTP" && strtoupper($MOBILE_NO_VERIFICATION_METHOD) == "FIREBASE" ) {
    ?>

    <!--  Firebase  -->
    <script src="https://www.gstatic.com/firebasejs/6.3.3/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/6.3.3/firebase-auth.js"></script>

    <script>
        var LBL_VERIFICATION_CODE_INVALID = "<?php echo $languageLabelsArr['LBL_VERIFICATION_CODE_INVALID']; ?>"
        const firebaseConfig = <?php echo ($fdata != '') ? $fdata : '{}';?>;
    </script>
    <script src="<?= $tconfig['tsite_url'] ?>assets/js/firebase_phone_verify_account_delete_process.js "
            type="text/javascript"></script>

    <?php
}
?>
<!-- Firebase -->

<script type="text/javascript" src="<?= $tconfig['tsite_url'] ?>assets/js/add_country_code_dropdown.js"></script>
<script src="<?= $tconfig['tsite_url'] . "/templates/" . $template . "/assets/js/less.min.js" ?>"></script>
<script>
    less = {
        env: 'development'
    };
</script>
<script src="<?= $tconfig['tsite_url'] ?>assets/js/getDataFromApi.js" type="text/javascript"></script>

<script type="text/javascript">



    ajaxpagename = 'ajax_account_delete_process.php';
    ajaxpagename = 'ajax_account_delete_process.php';
    var tsite_url = '<?php echo $tconfig['tsite_url']; ?>';
    var pagename = '<?php echo $pagename; ?>';
    var screen = '<?php echo $screen; ?>';
    var GeneralMemberId = '<?php echo $GeneralMemberId; ?>';
    var GeneralUserType = '<?php echo $GeneralUserType; ?>';

    var MOBILE_NO_VERIFICATION_METHOD = '<?php echo strtoupper($MOBILE_NO_VERIFICATION_METHOD); ?>';
    reloadPage(screen, GeneralMemberId, GeneralUserType);

    function reloadPage(screen, GeneralMemberId, GeneralUserType) {
        var data = {
            data: 1,
            screen: screen,
            GeneralMemberId: GeneralMemberId,
            GeneralUserType: GeneralUserType,
        };
        var ajaxData = {
            'URL': tsite_url + ajaxpagename,
            'AJAX_DATA': data,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var result = response.result;
                getPhoneCodeInTextBox('email', 'CountryCode');
                $('.container').html(result);
            } else {
                // console.log(response.result);
            }
        });
    }

    <!--  Firebase  -->

    if(MOBILE_NO_VERIFICATION_METHOD == "FIREBASE") {
        function OTPSend(formName, action = '') {
            showOverlay();
            sendOTP(formName, action, function () {
                formsubmit(formName, action);
            });
            hideOverlay();
        }

        function VerifyOTP(formName, action = '') {
            var otp = document.getElementById('otp').value;
            if (otp != "") {
                showOverlay();
                checkVerificationStatus(formName, action, function () {
                    $("#isOtpVerifyDone").val('1');
                    formsubmit(formName, action);
                });
                hideOverlay();
            } else {
                showError('error_signing_page', 'txt_error_signing_page', "<?php echo $languageLabelsArr['LBL_ENTER_VERIFICATION_CODE']; ?>");
                // console.log(otp);
                return false;
            }
        }
    }
    <!--  Firebase  -->



    function formsubmit(formName, action = '') {
        showOverlay();
        var formdata = $("#" + formName).serialize();
        if (action != '') {
            formdata = formdata + '&action=' + action;
        }
        var ajaxData = {
            'URL': tsite_url + ajaxpagename,
            'AJAX_DATA': formdata,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var result = response.result;
                getPhoneCodeInTextBox('email', 'CountryCode');
                $('.container').html(result);
                hideOverlay();
                if (result == 1) {
                    redirectSuccess();
                }
            } else {
                // console.log(response.result);
            }
        });
        return false;
    }

    function redirectSuccess() {
        var url = "<?php echo $tconfig['tsite_url'] ?>success.php?success=1&account_deleted=Yes";
        window.location.href = url;
    }

    function closeAlert(id) {
        $('#' + id).hide();
    }

    hideOverlay();

</script>
</body>

</html>