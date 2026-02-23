<?php



include_once("common.php");
if(SITE_TYPE=='Demo') {
    header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1");
    exit;
}
if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
    $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);

    if ($valiedRecaptch) {
        if ($_POST) {
            /* if($_POST['vPassword'] != $_POST['vRPassword'])
              {
              getPostForm($_POST,"Password doesn't match","SignUp");
              exit;
              } */
            $check = validateformfields();
            if (isset($check['vName']) || isset($check['vLastName']) || isset($check['vPhone']) || isset($check['vCountry']) || isset($check['vPhoneCode']) || isset($check['vCode']) || isset($check['vEmail'])) {
                $_SESSION['postDetail'] = $_REQUEST;
                $_SESSION['nameErr'] = $check['vName'];
                $_SESSION['phoneErr'] = $check['vPhone'];
                $_SESSION['lastnameErr'] = $check['vLastName'];
                $_SESSION['emailErr'] = $check['vEmail'];
                $_SESSION['countryErr'] = $check['vCountry'];
                $_SESSION['phonecodeErr'] = $check['vPhoneCode'];
                header("Location:" . $tconfig["tsite_url"] . "sign-up.php?type=user");
                exit;
            }
            if($_POST['vEmail'] != ""){
                $msg = checkDuplicateFront('vEmail', "register_user", Array('vEmail'), $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=Email already Exists", "Email already Exists", "", "");

            }

            $eSystem = "";
            $checPhoneExist = checkMemberDataInfo($_POST['vPhone'],"",'Rider',$_POST['vCountry'],"",$eSystem);

            if($checPhoneExist['status'] == 0){
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=". $langage_lbl['LBL_PHONE_EXIST_MSG']);
                exit;
            }else if($checPhoneExist['status'] == 2){
                header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }

            if(!empty($_POST['vCountry'])){
                $_SESSION['postDetail'] = $_REQUEST;
                $checkCountryExist = checkCountryExistOrNot($_POST['vCountry']);
                if($checkCountryExist == 'No'){
                    header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=". $langage_lbl['LBL_NO_COUNTRY_AVAIL_MSG']);
                    exit;
                }
            }


            if(!empty($_POST['vCurrencyPassenger'])){
                $checkCurrencyExist = checkCurrencyExistOrNot($_POST['vCurrencyPassenger']);
                if($checkCurrencyExist == 'No'){
                    header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=". $langage_lbl['LBL_NO_CURRENCY_AVAIL_MSG']);
                    exit;
                }
            }

            $eReftype = "Rider";
            $Data['vRefCode'] = $REFERRAL_OBJ->GenerateReferralCode($eReftype);
            $Data['iRefUserId'] = $_POST['iRefUserId'];
            $Data['eRefType'] = $_POST['eRefType'];
            $Data['vName'] = $_POST['vName'];
            $Data['vLang'] = $_POST['vLang'];
            $_SESSION["sess_lang"]=$_POST['vLang'];
            $Data['vLastName'] = $_POST['vLastName'];

            $Data['vPassword'] = encrypt_bycrypt($_REQUEST['vPassword']);
            $Data['vEmail'] = $_POST['vEmail'];
            $Data['vPhone'] = $_REQUEST['vPhone'];
            $Data['vCountry'] = $_POST['vCountry'];
            $Data['vZip'] = $_POST['vZip'];

            $Data['vInviteCode'] = $_POST['vInviteCode'];

            $Data['vCurrencyPassenger'] = $_POST['vCurrencyPassenger'];
            //$Data['eGender'] = $_POST['eGender'];
            $Data['dRefDate'] = Date('Y-m-d H:i:s');
            $Data['tRegistrationDate'] = Date('Y-m-d H:i:s');
            $Data['tSessionId'] = session_id() . time();

            $csql = "SELECT eZeroAllowed,vCountryCode,vPhoneCode,vTimeZone  FROM `country` WHERE vCountryCode = '" . $_POST['vCountry'] . "'";
            $CountryData = $obj->MySQLSelect($csql);
            /*$eZeroAllowed = $CountryData[0]['eZeroAllowed'];

            if ($eZeroAllowed == 'Yes') {
                $Data['vPhone'] = $Data['vPhone'];
            } else {
                $first = substr($Data['vPhone'], 0, 1);

                if ($first == "0") {
                    $Data['vPhone'] = substr($Data['vPhone'], 1);
                }
            }*/
    
            $Data['vTimeZone'] = $CountryData[0]['vTimeZone'];
            $Data['vPhoneCode'] = $CountryData[0]['vPhoneCode'];

            if (SITE_TYPE == 'Demo') {
                $Data['eStatus'] = 'Active';
                //Added By HJ On 17-07-2019 For Auto Verify Email and Phone Driver When Register Start
                $Data['eEmailVerified'] = $Data['ePhoneVerified'] = 'Yes';
                //Added By HJ On 17-07-2019 For Auto Verify Email and Phone Driver When Register End
            }
            $eSystem = "";
            if($ENABLE_EMAIL_OPTIONAL != "Yes") {
                $checkValid = checkMemberDataInfo($_POST['vEmail'], "", $eReftype,$_POST['vCountry'],"",$eSystem);
            } else {
                $checkValid = checkMemberDataInfo($_POST['vPhone'],"",'Rider',$_POST['vCountry'],"",$eSystem);
            }
            
            if ($checkValid['status'] == 1) {
                $id = $obj->MySQLQueryPerform("register_user", $Data, 'insert');
                createUserLog('Passenger', 'No', $id, 'Web','WebLogin','SignUp');
                /* Multi Level Referral */
                if($_POST['iRefUserId'] != "" && $_POST['iRefUserId'] > 0)
                {
                    $refSql = "SELECT * FROM user_referrer_transaction WHERE iMemberId = ".$_POST['iRefUserId']." AND eUserType = '".$_POST['eRefType']."'";
                    $refData = $obj->MySQLSelect($refSql);
                    
                    if($refData[0]['tReferrerInfo'] == "")
                    {
                        $referrerInfo[] = array('Position of Referrer' => 1, 'iMemberId' => $_POST['iRefUserId'], 'eUserType' => $_POST['eRefType']);
                    }
                    else {
                        $referrerInfo = json_decode($refData[0]['tReferrerInfo'], true);
                        $referrerInfo[] = array('Position of Referrer' => (scount($referrerInfo)+1), 'iMemberId' => $_POST['iRefUserId'], 'eUserType' => $_POST['eRefType']);
                    }
                    $Data_Referrer['tReferrerInfo'] = json_encode($referrerInfo);
                }

                $Data_Referrer['iMemberId'] = $id;
                $Data_Referrer['eUserType'] = 'Rider';
        
                $obj->MySQLQueryPerform("user_referrer_transaction", $Data_Referrer, 'insert');

                /* Multi Level Referral End */

                if ($id != "") {
                    if (SITE_TYPE == 'Demo') {
                        insertCorporateUserProfile($id, $Data['vEmail']); // Added By HJ On 31-07-2018 As Per Discuss With BM QA and CD Sir
                    }
                    $_SESSION['sess_iUserId'] = $id;
                    $_SESSION["sess_ProfileName"] = $_SESSION["sess_vName"] = $Data['vName'] . ' ' . $Data['vLastName'];
                    $_SESSION["sess_company"] = " ";
                    $_SESSION["sess_vEmail"] = $Data['vEmail'];
                    $_SESSION["sess_user"] = "rider";
                    $_SESSION["sess_vPhone"] =$Data['vPhone'];
                    $_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $Data['vCurrencyPassenger'];
                    $_SESSION["sess_vImage"] = getProfileImage('', $tconfig["tsite_url"],'','');

                    $maildata['NAME'] = $_SESSION["sess_vName"];
                    $maildata['EMAIL_NAME'] = $_SESSION["sess_vName"];
                    $maildata['EMAILORG'] = $_SESSION["sess_vEmail"];

                    $emailtxt = $langage_lbl["LBL_EMAIL_TEXT"] . ": " .  $_SESSION["sess_vEmail"];
                    $maildata['EMAIL'] =  !empty($_SESSION["sess_vEmail"]) ? $emailtxt : '';

                    $phonetxt = $langage_lbl["LBL_PHONE"] . ": + " . $Data['vPhoneCode']. $Data['vPhone'];
                    $maildata['PHONE'] = !empty($_SESSION["vPhone"]) ? $phonetxt : '';
                    
                    //$maildata['PASSWORD'] = $langage_lbl["LBL_PASSWORD"] . ": " . $_REQUEST['vPassword']; //Commented By HJ On 11-01-2019 For Hide Password As Per Discuss With QA BM
                    
                    $maildata['SOCIALNOTES'] = '';
                    $COMM_MEDIA_OBJ->SendMailToMember("MEMBER_REGISTRATION_USER", $maildata);
                    //User login log added by Rs
                    createUserLog('Passenger', 'Yes', $id, 'Web');
                    
                    if (!empty($_SESSION['sess_currentpage_url_mr']) && isset($_SESSION['sess_currentpage_url_mr'])) {
                        $redirect = $_SESSION['sess_currentpage_url_mr'];
                        unset($_SESSION['sess_currentpage_url_mr']);
                        header("Location:" . $redirect);
                        exit;
                    }
                    if ($_REQUEST['depart'] != "" && $_REQUEST['depart'] == 'mobi') {
                        header("Location:mobi");
                        exit;
                    }
                    header("Location:profile_rider.php");
                    exit;
                }
            } else if ($checkValid['status'] == 2) {
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }
        }
    } else {
        $_SESSION['postDetail'] = $_REQUEST;
        header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=".$langage_lbl['LBL_CAPTCHA_MATCH_MSG']);
        exit;
    }
} else {
    $_SESSION['postDetail'] = $_REQUEST;
    header("Location:" . $tconfig["tsite_url"] . "sign-up_rider.php?error=1&var_msg=Please check reCAPTCHA box.");
    exit;
}


?>
