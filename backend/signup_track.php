<?php
include_once("common.php");
$table = 'track_service_company';
$user_type = $_POST['user_type'];
$user_type = "tracking_company";

if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
    $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);
    if ($valiedRecaptch) {
        $url = "sign-up.php?type=" . $user_type . "&";

        $Data['vPhone'] = $_POST['vPhone'];
        $Data['vCode'] = $_POST['vCode'];
        $Data['vPassword'] = encrypt_bycrypt($_REQUEST['vPassword']);
        $Data['vVat'] = $_POST['vVat'];
        $Data['vState'] = $_POST['vState'];
        $Data['vZip'] = $_POST['vZip'];
        $Data['vEmail'] = $_REQUEST['vEmail'] = $_POST['vEmailc'];
        $Data['tRegistrationDate'] = Date('Y-m-d H:i:s');
        $Data['vLang'] = $_SESSION['sess_lang'];
        $Data['vCountry'] = $_POST['vCountry'];
        $Data['vCompany'] = $_POST['vCompany'];

        $Data['vLocation'] = $_POST['vLocation'];
        $Data['vLatitude'] = $_POST['vLatitude'];
        $Data['vLongitude'] = $_POST['vLongitude'];
        /*$msg = checkDuplicateFront('vEmail', 'track_service_company', Array('vEmail'), $tconfig["tsite_url"] . $url ."error=1&var_msg=Email already Exists", "Email already Exists", "", "");
        */
        $eSystem = "";
        $checPhoneExist = checkMemberDataInfo($_POST['vPhone'], "", $user_type, $_POST['vCountry'], "", $eSystem);
        if ($checPhoneExist['status'] == 0) {
            $_SESSION['postDetail'] = $_REQUEST;
            header("Location:" . $tconfig["tsite_url"] . $url . "error=1&var_msg=" . $langage_lbl['LBL_PHONE_EXIST_MSG']);
            exit;
        } else if ($checPhoneExist['status'] == 2) {
            header("Location:" . $tconfig["tsite_url"] . $url . "error=1&var_msg=" . $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
            exit;
        }
        $id = $obj->MySQLQueryPerform($table, $Data, 'insert');
        createUserLog('TrackServiceCompany', 'No', $id, 'Web','WebLogin','SignUp');
        if ($id != '') {
            $_SESSION['postDetail'] = $_REQUEST;
            $_SESSION['sess_eSystem'] = $Data['eSystem'];
            $_SESSION['sess_iUserId'] = '';
            $_SESSION['sess_iCompanyId'] = '';
            $_SESSION['sess_iTrackServiceCompanyId'] = $id;
            $_SESSION["sess_iUserId"] = $id;
            $_SESSION["sess_vName"] = $Data['vCompany'];
            $_SESSION["eSystem"] = $eSystem;
            $_SESSION['postDetail']['user_type'] = $user_type;
            $_SESSION["sess_company"] = $Data['vCompany'];
            $_SESSION["sess_vEmail"] = $Data['vEmail'];
            $_SESSION["sess_user"] = $user_type;
            $_SESSION["sess_new"] = 1;

            $maildata['EMAILORG'] = $_SESSION["sess_vEmail"];
            $maildata['EMAIL'] = $langage_lbl['LBL_EMAIL_TEXT'] . " : " . $_SESSION["sess_vEmail"];
            $maildata['PHONE'] = $langage_lbl['LBL_PHONE'] . " : + ". $Data['vCode'] .  $Data['vPhone'];

            $maildata['NAME'] = $_SESSION["sess_vName"];
            $COMM_MEDIA_OBJ->SendMailToMember("TRACKING_COMPANY_REGISTRATION_ADMIN", $maildata);
            $COMM_MEDIA_OBJ->SendMailToMember("TRACKING_COMPANY_REGISTRATION_USER", $maildata);

            header("Location:profile?first=yes");
            exit;
        }
    }
} else {
    $_SESSION['postDetail'] = $_REQUEST;
    header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=Please check reCAPTCHA box.");
    exit;
}
?>