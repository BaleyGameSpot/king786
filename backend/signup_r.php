<?php

include_once("common.php");

/*if($THEME_OBJ->isCubexThemeActive() == 'Yes') 

    $url = "cx-sign-up.php?type=restaurant&";

else*/

$url = "sign-up-restaurant.php?";


if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {

    /* Check Recaptch is valied or not */

    $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);

    if ($valiedRecaptch) {

        //if (isset($_POST['SUBMIT'])) {
            if($THEME_OBJ->isXThemeActive() == 'Yes') {

              $_POST['vEmail'] = $_POST['vEmailc'];

            }
			
			$check = validateformfields();

			if (isset($check['vCompany']) || isset($check['vPhone']) || isset($check['vEmail']) || isset($check['vContactName']) || isset($check['vCountry']) || isset($check['vPhoneCode']) || isset($check['vCode'])) {
				$_SESSION['postDetail'] = $_REQUEST;
				$_SESSION['cnameErr'] = $check['vCompany'];
				$_SESSION['cphoneErr'] = $check['vPhone'];
				$_SESSION['cemailErr'] = $check['vEmail'];
				$_SESSION['ccontactnameErr'] = $check['vContactName'];
				$_SESSION['ccountryErr'] = $check['vCountry'];
				$_SESSION['cphonecodeErr'] = $check['vCode'];
				header("Location:" . $tconfig["tsite_url"] . "sign-up.php?type=store");
				exit;
			}


            $eSystem = "";

            if (isset($_POST['eSystem']) && $_POST['eSystem'] != "") {

                $eSystem = $_POST['eSystem'];

            }

            $Data = array();

            $table_name = "company";

            /*if($_POST['vEmailc'] != ''){

          $msg = checkDuplicateFront('vEmail', 'company', Array('vEmail'), $tconfig["tsite_url"] . "sign-up-restaurant.php?error=1&var_msg=Email already Exists", "Email already Exists", "", "");

            }*/

            if($_POST['vEmail'] != ""){

                //$msg = checkDuplicateFront('vEmail', "company", Array('vEmail'), $tconfig["tsite_url"] . "sign-up-restaurant.php?error=1&var_msg=Email already Exists", "Email already Exists", "", "");

            }

                

            $checPhoneExist = checkMemberDataInfo($_POST['vPhone'], "", 'company', $_POST['vCountry'], "", $eSystem);
       
            if ($checPhoneExist['status'] == 0) {

                $_SESSION['postDetail'] = $_REQUEST;
                
                header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=". $langage_lbl['LBL_PHONE_EXIST_MSG']);

                exit;

            } else if ($checPhoneExist['status'] == 2) {

                header("Location:" . $tconfig["tsite_url"] . $url. "error=1&var_msg=" . $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);

                exit;

            }

            if(!empty($_POST['vCountry'])){
                $_SESSION['postDetail'] = $_REQUEST;
                $checkCountryExist = checkCountryExistOrNot($_POST['vCountry']);
                if($checkCountryExist == 'No'){
                    header("Location:" . $tconfig["tsite_url"] . $url."error=1&var_msg=".$langage_lbl['LBL_NO_COUNTRY_AVAIL_MSG']);
                    exit;
                }
            }



            $Data['vLang'] = $_SESSION['sess_lang'];

            $Data['vPassword'] = encrypt_bycrypt($_REQUEST['vPassword']);

            $Data['vEmail'] = $_POST['vEmail'];

            $Data['vPhone'] = $_POST['vPhone'];

            $Data['vCaddress'] = $_POST['vCaddress'];

            $Data['vCity'] = $_POST['vCity'];

            $Data['vCountry'] = $_POST['vCountry'];

            $Data['vState'] = $_POST['vState'];

            $Data['vZip'] = $_POST['vZip'];

           // $Data['vCode'] = $_POST['vCode'];

            $Data['vCompany'] = $_POST['vCompany'];

            $Data['tRegistrationDate'] = Date('Y-m-d H:i:s');

            $Data['vContactName'] = $_POST['vContactName'];

            $Data['iServiceId'] = $_POST['iServiceId'];

            //$Data['eSystem'] = $_POST['eSystem'];

            $Data['eSystem'] = 'DeliverAll';//according to new theme

            $Data['eStatus'] = 'Inactive';
             $Data['vTimeZone'] = get_value('country', 'vTimeZone', 'vCountryCode', $Data['vCountry'], '', 'true');


        $csql = "SELECT eZeroAllowed,vCountryCode,vPhoneCode FROM `country` WHERE vCountryCode = '" . $_POST['vCountry'] . "'";

            $CountryData = $obj->MySQLSelect($csql);

            //$eZeroAllowed = $CountryData[0]['eZeroAllowed'];
            
            $Data['vCode'] = $CountryData[0]['vPhoneCode'];
            /*if ($eZeroAllowed == 'Yes') {

                $Data['vPhone'] = $Data['vPhone'];

            } else {

                $first = substr($Data['vPhone'], 0, 1);

                if ($first == "0") {

                    $Data['vPhone'] = substr($Data['vPhone'], 1);

                }

            }*/

            $table =$user_type= 'company';

            if (SITE_TYPE == 'Demo') {

                $Data['eStatus'] = 'Active';

            }

            if($ENABLE_EMAIL_OPTIONAL != "Yes") {

            $checkValid = checkMemberDataInfo($_POST['vEmail'], "", $user_type, $_POST['vCountry'], "", $eSystem);

            } else {

                $checkValid = checkMemberDataInfo($_POST['vPhone'], "", 'company', $_POST['vCountry'], "", $eSystem);

            }

            if ($checkValid['status'] == 1) {

                $id = $obj->MySQLQueryPerform($table, $Data, 'insert');

                createUserLog('Store', 'No', $id, 'Web','WebLogin','SignUp');

                if ($id != "") {

                    $_SESSION['sess_iUserId'] = $id;

                    $_SESSION['sess_iCompanyId'] = $id;

                    $_SESSION["sess_vName"] = $Data['vCompany'];

                    $_SESSION["sess_ProfileName"] = $_SESSION["sess_company"] = $Data['vCompany'];

                    $_SESSION["sess_vEmail"] = $Data['vEmail'];

                    $_SESSION["sess_user"] = $user_type;

                    $_SESSION["sess_new"] = 1;

                    $_SESSION["sess_from"] = "web";

                    $_SESSION["sess_eSystem"] = $Data['eSystem'];

                    $_SESSION["sess_vImage"] = getProfileImage('', $tconfig["tsite_url"],'','');

                    $maildata['NAME'] = $_SESSION["sess_vName"];
                    $maildata['EMAIL_NAME'] = $_SESSION["sess_vName"];
                    $maildata['EMAILORG'] = $_SESSION["sess_vEmail"];

                    if(isset($email) && !empty($email)){
                        $maildata['EMAIL'] = $langage_lbl['LBL_EMAIL_TEXT'].": ".$_SESSION["sess_vEmail"];
                    }

                    $maildata['PHONE'] = $langage_lbl['LBL_PHONE'] . ": + " . $Data['vCode'] . $Data['vPhone'];
                    $maildata['PASSWORD'] = $langage_lbl['LBL_PASSWORD'] . ": " . $_REQUEST['vPassword'];
                    $maildata['SOCIALNOTES'] = '';

                   

                    $COMM_MEDIA_OBJ->SendMailToMember("STORE_REGISTRATION_USER", $maildata);

                    $COMM_MEDIA_OBJ->SendMailToMember("STORE_REGISTRATION_ADMIN", $maildata);

                    //User login log added by Rs start

                    createUserLog('Store', 'Yes', $id, 'Web');

                    if($THEME_OBJ->isXThemeActive() == 'Yes') {

                         header("Location:profile?first=yes");

                         exit;

                    } else {

                        header("Location:dashboard.php?first=yes");

                        exit;

                    }

                }

            } else if ($checkValid['status'] == 2) {

                $_SESSION['postDetail'] = $_REQUEST;

                header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=" . $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);

                exit;

            }

        //}

    } else {

        $_SESSION['postDetail'] = $_REQUEST;

        header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=" . $langage_lbl['LBL_CAPTCHA_MATCH_MSG']);

        exit;

    }

} else {

    $_SESSION['postDetail'] = $_REQUEST;

    header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=Please check reCAPTCHA box.");

    exit;

}

/* } else {

  $_SESSION['postDetail'] = $_REQUEST;

  header("Location:".$tconfig["tsite_url"]."sign-up-restaurant.php?error=1&var_msg=Captcha did not match.");

  exit;

  } */

?>

