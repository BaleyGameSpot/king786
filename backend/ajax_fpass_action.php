<?php
include_once('common.php');
$email = isset($_REQUEST['femail']) ? $_REQUEST['femail'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iscompany = isset($_REQUEST['iscompany']) ? $_REQUEST['iscompany'] : '0';
//$fphone = isset($_REQUEST['fphone']) ? $_REQUEST['fphone'] : '';
$isEmail = isset($_REQUEST['isEmailfemail']) ? $_REQUEST['isEmailfemail'] : 'Yes';
$isEmail = ($isEmail != '') ? $isEmail : $_REQUEST['isEmailfemail'];
$phoneCode = isset($_REQUEST['CountryCodeForgt']) ? $_REQUEST['CountryCodeForgt'] : '';
$group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : '';

$countrycode=get_value('country', 'vPhoneCode', 'vCountryCode',$phoneCode,'','true');


if ($action == 'changecurrency') {
    $currency = $_POST['q'];
    $tbl = 'register_user';

    $sql = "select eDefault from currency where vName='" . $currency . "'";
    $currencydata = $obj->MySQLSelect($sql);
    if($currencydata[0]['eDefault'] == 'Yes'){
        setcookie('ChangeCurrencyToDefault', 'Yes', time() + 3600, "/");
        $_SESSION['ChangeCurrencyToDefault'] = 'Yes';
    }

    unset($_SESSION['sess_currency']);
    $_SESSION['sess_currency'] = $currency;
    unset($_SESSION['sess_vCurrency']);
    $_SESSION['sess_vCurrency'] = $currency;
    $iUserId = $_SESSION['sess_iUserId'];
    $iCompanyId = $_SESSION['sess_iCompanyId'];


    /*if (isset($_SESSION['sess_user'])) {
        $iUserId = $_SESSION['sess_iUserId'];
        $where = " WHERE `iUserId` = '" . $iUserId . "'";
        $query = "UPDATE  `" . $tbl . "` SET `vCurrencyPassenger`='" . $currency . "'" . $where; //exit;
        $obj->sql_query($query);
    }*/
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') {
        $where = " WHERE `iUserId` = '" . $iUserId . "'";
        $query = "UPDATE  `register_user` SET `vCurrencyPassenger`='" . $currency . "'" . $where; //exit;
        $obj->sql_query($query);
    } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
        $where = " WHERE `iDriverId` = '" . $iUserId . "'";
        $query = "UPDATE  `register_driver` SET `vCurrencyDriver`='" . $currency . "'" . $where; //exit;
        $obj->sql_query($query);
    } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'company' && $_SESSION['sess_eSystem'] != 'DeliverAll') {
        $where = " WHERE `iCompanyId` = '" . $iCompanyId . "'";
        $query = "UPDATE  `company` SET `vCurrencyCompany`='" . $currency . "'" . $where; //exit;
        $obj->sql_query($query);
    } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'organization') {
        $where = " WHERE `iOrganizationId` = '" . $_SESSION['sess_iOrganizationId'] . "'";
        $query = "UPDATE  `organization` SET `vCurrency`='" . $currency . "'" . $where; //exit;
        $obj->sql_query($query);
    }
    $var_msg = $langage_lbl['LBL_PROFILE_UPDATE_SUCCESS'];
    $error_msg = "1";
    $data['msg'] = $var_msg;
    $data['status'] = $error_msg;
    echo json_encode($data);
    exit();
}

if($action == 'changeLangCode'){

    $langcode = $_POST['langcode'];

    $sql = "select eDirectionCode,eDefault from language_master where vCode='" . $langcode . "'";
    $lang = $obj->MySQLSelect($sql);
    if($lang[0]['eDefault'] == 'Yes'){
        setcookie('ChangeLanguageToDefault', 'Yes', time() + 3600, "/");
        $_SESSION['ChangeLanguageToDefault'] = 'Yes';
    }

    $tbl = 'register_user';
    $iUserId = $_SESSION['sess_iUserId'];
    $iCompanyId = $_SESSION['sess_iCompanyId'];

    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') {
        $where = " WHERE `iUserId` = '" . $iUserId . "'";
        $query = "UPDATE  `register_user` SET `vLang`='" . $langcode . "'" . $where; //exit;
        $obj->sql_query($query);
    } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
        $where = " WHERE `iDriverId` = '" . $iUserId . "'";
        $query = "UPDATE  `register_driver` SET `vLang`='" . $langcode . "'" . $where; //exit;
        $obj->sql_query($query);
    } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'company' && $_SESSION['sess_eSystem'] != 'DeliverAll') {
        $where = " WHERE `iCompanyId` = '" . $iCompanyId . "'";
        $query = "UPDATE  `company` SET `vLang`='" . $langcode . "'" . $where; //exit;
        $obj->sql_query($query);
    } else if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'organization') {
        $where = " WHERE `iOrganizationId` = '" . $_SESSION['sess_iOrganizationId'] . "'";
        $query = "UPDATE  `organization` SET `vLang`='" . $langcode . "'" . $where; //exit;
        $obj->sql_query($query);
    }

    $var_msg = $langage_lbl['LBL_PROFILE_UPDATE_SUCCESS'];
    $error_msg = "1";
    $data['msg'] = $var_msg;
    $data['status'] = $error_msg;
    echo json_encode($data);
    exit();
}


if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']) ) {

    $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);
    if ($valiedRecaptch || 1 == 1 ) {
        if ($action == 'driver') {
            if ($iscompany == '1') {
                // if($isEmail == "Yes"){
                //     $sql = "SELECT * from company where vEmail = '" . $email . "' and eStatus != 'Deleted'";
                // } else {
                //      $sql = "SELECT * from company where vCode = '" . $group_id . "' and vPhone = '" . $email . "' and eStatus != 'Deleted'";
                // }
                $sql = "SELECT * from company where vEmail = '" . $email . "' and eStatus != 'Deleted'";
                if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                    $sql = "SELECT * from company where vCountry = '" . $phoneCode . "' and (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
                }
                $db_login = $obj->MySQLSelect($sql);
                if (scount($db_login) > 0) {
                    if (SITE_TYPE != 'Demo') {
                        $milliseconds = time();
                        $tempGenrateCode = substr($milliseconds, 1);
                        //$url = $tconfig["tsite_url"].'reset_password.php?type='.$action.'&generatepsw='.$tempGenrateCode;
                        $Today = Date('Y-m-d');
                        $vLang1 = $db_login[0]['vLang'];
                        if ($vLang1 == "" || $vLang1 == NULL) {
                            $vLang1 = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                        }
                        $type = base64_encode(base64_encode('company'));
                        $id = encrypt($db_login[0]["iCompanyId"]);
                        $today = base64_encode(base64_encode($Today));
                        $newToken = RandomString(32);
                        $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
                        $link = get_tiny_url($url);
                        $maildata['EMAIL'] = $db_login[0]["vEmail"];
                        $maildata['NAME'] = $db_login[0]["vCompany"];
                        $maildata['EMAIL_NAME'] = $db_login[0]["vCompany"];
                        // if($isEmail == "Yes"){
                        //     $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                        //     $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                        // } else {
                        //     $maildata['LINK'] = $link;
                        //     $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                        //     $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$phoneCode,$message_layout);
                        // }
                        if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                            $maildata['LINK'] = $link;
                            $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                    $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$countrycode,$message_layout);
                        } else {
                            $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                            $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                        }
                    }
                    else {
                        $status = 1;
                    }
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $sql = "UPDATE company set vPassword_token='" . $newToken . "' WHERE vCountry = '" . $phoneCode . "' and (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
                        $obj->sql_query($sql);
                    } else {
                        $sql = "UPDATE company set vPassword_token='" . $newToken . "' WHERE vEmail='" . $email . "' and eStatus != 'Deleted'";
                        $obj->sql_query($sql);
                    }
                    if ($status == 1) {
                        if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                            $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT_SMS'];
                        } else {
                            $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT'];
                        }
                        $error_msg = "1";
                    }
                    else {
                        $var_msg = $langage_lbl['LBL_ERROR_PASSWORD_MAIL'];
                        $error_msg = "0";
                    }
                } else {
                    // if($isEmail == "Yes"){
                    //     $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                    // } else {
                    //     $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                    // }
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                    } else {
                        $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                    }
                    $error_msg = "0";
                }
            } else {
                // if($isEmail == "Yes"){
                //     $sql = "SELECT * from register_driver where vEmail = '" . $email . "' and eStatus != 'Deleted'";
                // } else {
                //     $sql = "SELECT * from register_driver where vCode = '" . $phoneCode . "' AND vPhone = '" . $email . "' and eStatus != 'Deleted'";
                // }
                $sql = "SELECT * from register_driver where vEmail = '" . $email . "' and eStatus != 'Deleted'";
                if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                    $sql = "SELECT * from register_driver where vCountry = '" . $phoneCode . "' AND (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
                }
                $db_login = $obj->MySQLSelect($sql);
                if (scount($db_login) > 0) {
                    if (SITE_TYPE != 'Demo') {
                        $tempGenrateCode = substr($milliseconds, 1);
                        $Today = Date('Y-m-d H:i:s');
                        $vLang1 = $db_login[0]['vLang'];
                        if ($vLang1 == "" || $vLang1 == NULL) {
                            $vLang1 = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                        }
                        $type = base64_encode(base64_encode($action));
                        $newToken = RandomString(32);
                        $id = encrypt($db_login[0]["iDriverId"]);
                        $today = base64_encode(base64_encode($Today));
                        $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
                        $link = get_tiny_url($url);
                        $maildata['EMAIL'] = $db_login[0]["vEmail"];
                        $maildata['NAME'] = $db_login[0]["vName"] . " " . $db_login[0]["vLastName"];
                        $maildata['EMAIL_NAME'] = $db_login[0]["vName"] . " " . $db_login[0]["vLastName"];
                        // if($isEmail == "Yes"){
                        //     $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                        //     $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                        // } else {
                        //    $maildata['LINK'] = $link;
                        //    $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                        //     $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$phoneCode,$message_layout);
                        // }
                        if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                            $maildata['LINK'] = $link;
                            $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                            $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$countrycode,$message_layout);
                        } else {
                            $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                            $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                        }
                    } else {
                        $status = 1;
                    }
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $sql = "UPDATE register_driver set vPassword_token='" . $newToken . "' WHERE vCountry = '" . $phoneCode . "' AND (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
                    } else {
                        $sql = "UPDATE register_driver set vPassword_token='" . $newToken . "' WHERE vEmail='" . $email . "' and eStatus != 'Deleted'";
                    }
                    $obj->sql_query($sql);
                    if ($status == 1) {
                        if (isOnlyDigitsStrSGF($email) && !empty($phoneCode)) {
                            $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT_SMS'];
                        } else {
                            $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT'];
                        }
                        $error_msg = "1";
                    } else {
                        $var_msg = $langage_lbl['LBL_ERROR_PASSWORD_MAIL'];
                        $error_msg = "0";
                    }
                } else {
                    // if($isEmail == "Yes"){
                    //     $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                    // } else {
                    //     $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                    // }
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                    } else {
                        $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                    }
                    $error_msg = "0";
                }
            }
        }

        if ($action == 'rider') {
            // if($isEmail == "Yes"){
            //     $sql = "SELECT * from register_user where vEmail = '" . $email . "' and eStatus != 'Deleted'";
            // } else {
            //     $sql = "SELECT * from register_user where vPhoneCode = '" . $phoneCode . "' AND vPhone = '" . $email . "' and eStatus != 'Deleted'";
            // }
            $sql = "SELECT * from register_user where vEmail = '" . $email . "' and eStatus != 'Deleted'";
            if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                $sql = "SELECT * from register_user where vCountry = '" . $phoneCode . "' AND (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
            }
            //echo  $sql;exit;
            $db_login = $obj->MySQLSelect($sql);
            if (scount($db_login) > 0) {

                if (SITE_TYPE != 'Demo') {
                    $milliseconds = time();
                    $vLang1 = $db_login[0]['vLang'];
                    if ($vLang1 == "" || $vLang1 == NULL) {
                        $vLang1 = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                    }
                    $id = encrypt($db_login[0]["iUserId"]);
                    $tempGenrateCode = substr($milliseconds, 1);
                    $newToken = RandomString(32);
                    $type = base64_encode(base64_encode($action));
                    $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
                    $link = get_tiny_url($url);
                    $maildata['EMAIL'] = $db_login[0]["vEmail"];
                    $maildata['NAME'] = $db_login[0]["vName"] . " " . $db_login[0]["vLastName"];
                    $maildata['EMAIL_NAME'] = $db_login[0]["vName"] . " " . $db_login[0]["vLastName"];
                    // if($isEmail == "Yes"){
                    //      $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                    //     $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                    // } else {
                    //     $maildata['LINK'] = $link;
                    //     $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                    //     $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$phoneCode,$message_layout);
                    // }
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $maildata['LINK'] = $link;
                        $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                        $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$countrycode,$message_layout);
                    } else {
                        $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                        $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                    }
                } else {
                    $status = 1;
                }
                if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                    $sql = "UPDATE register_user set vPassword_token='" . $newToken . "' WHERE vCountry = '" . $phoneCode . "' AND (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
                } else {
                    $sql = "UPDATE register_user set vPassword_token='" . $newToken . "' WHERE vEmail='" . $email . "' and eStatus != 'Deleted'";
                }
                $obj->sql_query($sql);
                if ($status == 1) {
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT_SMS'];
                    } else {
                        $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT'];
                    }
                    $error_msg = "1";
                } else {
                    $var_msg = $langage_lbl['LBL_ERROR_PASSWORD_MAIL'];
                    $error_msg = "0";
                }
            } else {
                // if($isEmail == "Yes"){
                //     $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                // } else {
                //     $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                // }
                if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                    $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                } else {
                    $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                }
                $error_msg = "3";
            }
        }
   
        /* Use For Organization Module */
        if ($action == 'organization') {
            // if($isEmail == "Yes"){
            //         $sql = "SELECT * from organization where vEmail = '" . $email . "' and eStatus != 'Deleted'";
            //     } else {
            //         $sql = "SELECT * from organization where vCode = '" . $phoneCode . "' AND vPhone = '" . $email . "' and eStatus != 'Deleted'";
            //     }
            $sql = "SELECT * from organization where vEmail = '" . $email . "' and eStatus != 'Deleted'";
            if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                $sql = "SELECT * from organization where vCountry = '" . $phoneCode . "' AND (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
            }
            // $sql = "SELECT * from organization where vEmail = '" . $email . "' and eStatus != 'Deleted'";
            $db_login = $obj->MySQLSelect($sql);
            $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
            $error_msg = "0";
            if (scount($db_login) > 0) {
                if (SITE_TYPE != 'Demo') {
                    $milliseconds = time();
                    $tempGenrateCode = substr($milliseconds, 1);
                    //$url = $tconfig["tsite_url"].'reset_password.php?type='.$action.'&generatepsw='.$tempGenrateCode;
                    $Today = Date('Y-m-d');
                    $type = base64_encode(base64_encode('organization'));
                    $id = encrypt($db_login[0]["iOrganizationId"]);
                    $today = base64_encode(base64_encode($Today));
                    $newToken = RandomString(32);
                    $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
                    $link = get_tiny_url($url);
                    $maildata['EMAIL'] = $db_login[0]["vEmail"];
                    $maildata['NAME'] = $db_login[0]["vCompany"];
                    $maildata['EMAIL_NAME'] = $db_login[0]["vCompany"];
                    // $maildata['LINK'] = '<a href="' . $url . '" target="_blank">Clicking here</a>';
                    // $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                    // if($isEmail == "Yes"){
                    //         $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                    //         $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                    //     } else {
                    //         $maildata['LINK'] = $link;
                    //         $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                    //         $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$phoneCode,$message_layout);
                    //     }
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $maildata['LINK'] = $link;
                        $message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("CUSTOMER_RESET_PASSWORD", $maildata, "", $vLang1);
                $status = $COMM_MEDIA_OBJ->SendSystemSMS($email,$countrycode,$message_layout);
                    } else {
                        $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                        $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                    }
                } else {
                    $status = 1;
                }
                if ($status == 1) {
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $sql = "UPDATE organization set vPassword_token='" . $newToken . "' WHERE vCountry = '" . $phoneCode . "' AND (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."') and eStatus != 'Deleted'";
                    } else {
                        $sql = "UPDATE organization set vPassword_token='" . $newToken . "' WHERE vEmail='" . $email . "' and eStatus != 'Deleted'";
                    }
                    $obj->sql_query($sql);
                    // $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT'];
                    // $error_msg = "1";
                    if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                        $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT_SMS'];
                    } else {
                        $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT'];
                    }
                    $error_msg = "1";
                } else {
                    $var_msg = $langage_lbl['LBL_ERROR_PASSWORD_MAIL'];
                    $error_msg = "0";
                }
            } else {
                // if($isEmail == "Yes"){
                //     $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                // } else {
                //     $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                // }
                if (isOnlyDigitsStrSGF($email) && !empty($phoneCode) && $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
                    $var_msg = $langage_lbl['LBL_INVALID_PHONE_NUMBER'];
                } else {
                    $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                }
                $error_msg = "3";
            }
        }
        /* Use For Organization Module */
        /* Use For Admin Panel */
        if ($action == 'admin') {
            if (isset($group_id) && !empty($group_id) && $group_id != '1') {
                $ssql1 = " AND iGroupId = '" . $group_id . "'";
            } else {
                $ssql1 = "";
            }
            if ($isEmail == "Yes") {
                $sql = "SELECT * from administrators where vEmail = '" . $email . "' and eStatus != 'Deleted' $ssql1";
            } else {
                $sql = "SELECT * from administrators where vCode = '" . $phoneCode . "' and vContactNo = '" . $email . "' and eStatus != 'Deleted' $ssql1";
            }
            $db_login = $obj->MySQLSelect($sql);
            if (scount($db_login) > 0) {
                if (SITE_TYPE != 'Demo') {
                    $milliseconds = time();
                    $tempGenrateCode = substr($milliseconds, 1);
                    $Today = Date('Y-m-d');
                    $vLang1 = $db_login[0]['vLang'];
                    if ($vLang1 == "" || $vLang1 == NULL) {
                        $vLang1 = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                    }
                    $type = base64_encode(base64_encode('admin'));
                    $id = encrypt($db_login[0]["iAdminId"]);
                    $today = base64_encode(base64_encode($Today));
                    $newToken = RandomString(32);
                    $url = $tconfig["tsite_url"] . 'reset_admin_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
                    //$url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
                    $link = get_tiny_url($url);
                    $maildata['EMAIL'] = $db_login[0]["vEmail"];
                    $maildata['NAME'] = $db_login[0]["vFirstName"] . " " . $db_login[0]["vLastName"];
                    $maildata['EMAIL_NAME'] = $db_login[0]["vFirstName"] . " " . $db_login[0]["vLastName"];
                    if (isset($email) && !empty($email)) {
                        $maildata['LINK'] = '<a href="' . $link . '" target="_blank">Clicking here</a>';
                        $status = $COMM_MEDIA_OBJ->SendMailToMember("CUSTOMER_RESET_PASSWORD", $maildata);
                    }
                } else {
                    $status = 1;
                }
                if (isset($email) && !empty($email)) {
                    $sql = "UPDATE administrators set vPassword_token='" . $newToken . "' WHERE vEmail='" . $email . "' and eStatus != 'Deleted'";
                    $obj->sql_query($sql);
                }
                if ($status == 1) {
                    if (isset($email) && !empty($email)) {
                        $var_msg = $langage_lbl['LBL_PASSWORD_SENT_TXT'];
                    }
                    $error_msg = "1";
                } else {
                    $var_msg = $langage_lbl['LBL_ERROR_PASSWORD_MAIL'];
                    $error_msg = "0";
                }
            } else {
                if (isset($email) && !empty($email)) {
                    $var_msg = $langage_lbl['LBL_EMAIL_NOT_FOUND'];
                }
                $error_msg = "0";
            }
        }
        /* Use For Admin Panel */
        $data['msg'] = $var_msg;
        $data['status'] = $error_msg;
        echo json_encode($data);

    } else {

        if($action == 'rider'){
            $user_type1 = 'rider';
        }
        else if($action == 'driver' && $iscompany == '1'){
            $user_type1 = 'restaurant';
        }else if($action == 'driver'){
            $user_type1 = 'provider';
        }else if($action == 'organization'){
            $user_type1 = 'organization';
        }

        $data['msg'] = $langage_lbl['LBL_CAPTCHA_MATCH_MSG'];
        $data['status'] = 4;
        $data['user_type'] = $user_type1;
        echo json_encode($data);
    }
}
else {

    $data['msg'] = 'Please check reCAPTCHA box.';
    $data['status'] = 5;
    echo json_encode($data);
}


?>