<?php
include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
//global generalobj;
$iCompanyId = $_SESSION['sess_iCompanyId'];
$iDriverId = $_SESSION['sess_iUserId'];
$iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
$str = $userType = '';
$memberId = 0;
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
    $tbl = 'register_driver';
    $where = " WHERE `iDriverId` = '" . $iDriverId . "'";
    $memberId = $iDriverId;
    $userType = "DRIVER";
}
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'company') {
    $tbl = 'company';
    $where = " WHERE `iCompanyId` = '" . $iCompanyId . "'";
    $memberId = $iCompanyId;
    $userType = "COMPANY";
}

if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'tracking_company') {
    $tbl = 'track_service_company';
    $where = "  `iTrackServiceCompanyId` = '" . $iTrackServiceCompanyId . "'";
    $memberId = $iTrackServiceCompanyId;
    $userType = "TRACKING_COMPANY";
}

if ($action == 'login') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
        echo $var_msg = '2';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    /*  if ($ENABLE_EDIT_DRIVER_PROFILE == "No") {
         echo $var_msg = '3';
         return $var_msg;
         exit;
     } else {
  */
    $phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $tProfileDescription = isset($_POST['tProfileDescription']) ? $_POST['tProfileDescription'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
    $vCurrencyDriver = isset($_POST['vCurrencyDriver']) ? $_POST['vCurrencyDriver'] : '';
    $vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
    $vWorkLocationLatitude = isset($_POST['vWorkLocationLatitude']) ? $_POST['vWorkLocationLatitude'] : '';
    $vWorkLocationLongitude = isset($_POST['vWorkLocationLongitude']) ? $_POST['vWorkLocationLongitude'] : '';
    $vWorkLocation = isset($_POST['vWorkLocation']) ? $_POST['vWorkLocation'] : '';
    $vWorkLocationRadius = isset($_POST['vWorkLocationRadius']) ? $_POST['vWorkLocationRadius'] : '';
    $vVatNum = isset($_POST['vVatNum']) ? $_POST['vVatNum'] : '';
    $_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $vCurrencyDriver;
    $getDriverDetails = "select vName,vLastName,eStatus From register_driver where iDriverId=" . $iDriverId;
    $result_data = $obj->sql_query($getDriverDetails);
    if (scount($result_data) > 0) {
        $vName_db = $result_data[0]['vName'];
        $vLastName_db = $result_data[0]['vLastName'];
        $eStatus_db = $result_data[0]['eStatus'];
    }
    $message = $langage_lbl['LBL_EDIT_PROFILE_DISABLED'];
    $var_msg = '3';
    $_SESSION['message_code'] = $var_msg;
    $checkEditProfileStatus = getEditDriverProfileStatus($eStatus_db);
    if (($vName_db != $name || $vLastName_db != $lname) && $checkEditProfileStatus == "No") {
        $message = $langage_lbl['LBL_PROFILE_EDIT_BLOCK_TXT'];
        $checkEditProfileStatus = "No";
        $var_msg = '4';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    /*  if ($checkEditProfileStatus == "No") {
         echo $var_msg;
         return $var_msg;
         exit;
     } */
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
        //$str = ",`vCurrencyDriver`='" . $vCurrencyDriver . "', eStatus = 'active'";
        $str = ",`tProfileDescription` = '" . $tProfileDescription . "',`vWorkLocation` = '" . $vWorkLocation . "',`vWorkLocationLatitude` = '" . $vWorkLocationLatitude . "',`vWorkLocationLongitude` = '" . $vWorkLocationLongitude . "',`vWorkLocationRadius` = '" . $vWorkLocationRadius . "',`vCurrencyDriver`='" . $vCurrencyDriver . "'";
    } else {
        $str = ",`vCompany`='" . $vCompany . "',`vVat`='" . $vVatNum . "'";
    }
    $csql = "SELECT eZeroAllowed,vCountryCode,vPhoneCode FROM `country` WHERE vCountryCode = '" . $vCountry . "'";
    $CountryData = $obj->MySQLSelect($csql);
    //$eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    $vCode = $CountryData[0]['vPhoneCode'];
    /*if ($eZeroAllowed == 'Yes') {
        $phone = $phone;
    } else {
        $first = substr($phone, 0, 1);
        if ($first == "0") {
            $phone = substr($phone, 1);
        }
    }*/
    $eSystem = "";
    if ($userType == "COMPANY") {
        $eSystem = "General";
    }
    $checEmailExist = checkMemberDataInfo($phone, "", $userType, $vCountry, $memberId, $eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 0) {
        $var_msg = $langage_lbl['LBL_MOBILE_EXIST'];
        $action = 0;
    } else if ($checEmailExist['status'] == 2) {
        $var_msg = $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        $action = 2;
    }
    else {
        $q = "UPDATE ";
        $sql = "select * from " . $tbl . $where;
        $edit_data = $obj->sql_query($sql);
        if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $_REQUEST['email'] != $edit_data[0]['vEmail']) {
            $query = $q . " `" . $tbl . "` SET `eEmailVerified` = 'No' " . $where;
            $obj->sql_query($query);
        }
        if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $_REQUEST['phone'] != $edit_data[0]['vPhone']) {
            $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
            $obj->sql_query($query);
        }
        if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $_REQUEST['vCountry'] != $edit_data[0]['vCountry']) {
            $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
            $obj->sql_query($query);
        }
        if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $APP_TYPE == 'UberX') {
            $query = $q . " `" . $tbl . "` SET `tProfileDescription` = 'No' " . $where;
            $obj->sql_query($query);
        }
        $query = $q . " `" . $tbl . "` SET
            `vEmail` = '" . $email . "',
            `vLoginId` = '" . $username . "',
            `vName` = '" . $name . "' ,
            `vLastName` = '" . $lname . "',
            `vCountry` = '" . $vCountry . "',
            `vCode` = '" . $vCode . "',
            `vPhone` = '" . $phone . "' $str" . $where;
        $obj->sql_query($query);
        $var_msg = $langage_lbl['LBL_PORFILE_UPDATE_MSG'];
        $action = 1;
    }
    echo $action;
    $_SESSION['session_msg'] = $var_msg;
    $_SESSION['message_code'] = $action;
    die;
    return $var_msg;
    exit;
    //}
}
if ($action == 'address') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
        echo $var_msg = '2';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    $address1 = isset($_REQUEST['address1']) ? $_REQUEST['address1'] : '';
    $address2 = isset($_POST['address2']) ? $_POST['address2'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
    $vState = isset($_POST['vState']) ? $_POST['vState'] : '';
    $zipcode = isset($_POST['vZipcode']) ? $_POST['vZipcode'] : '';
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
            `vCaddress` = '" . $address1 . "',
            `vCadress2` = '" . $address2 . "',
            `vCity` = '" . $vCity . "',
            `vCountry` = '" . $vCountry . "',
            `vState` = '" . $vState . "',
            `vZip` = '" . $zipcode . "' $str" . $where;
    $obj->sql_query($query);
    echo $var_msg = $langage_lbl['LBL_ADDRESS_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'pass') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
        echo $var_msg = '2';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
    $npass = encrypt_bycrypt($npass);
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
            `vPassword` = '" . $npass . "' $str" . $where;
    $obj->sql_query($query);
    echo $var_msg = $langage_lbl['LBL_PASS_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'lang1') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
        echo $var_msg = '2';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    $lang = isset($_REQUEST['lang1']) ? $_REQUEST['lang1'] : '';
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
            `vLang` = '" . $lang . "' $str" . $where;
    $obj->sql_query($query);
    $_SESSION["sess_lang"] = $lang;
    $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
    $lang = $obj->MySQLSelect($sql);
    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
    echo $var_msg = $langage_lbl['LBL_LANG_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'vat') {
    $vat = isset($_REQUEST['vat']) ? $_REQUEST['vat'] : '';
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
            `vVat` = '" . $vat . "' $str" . $where;
    $obj->sql_query($query);
    echo $var_msg = $langage_lbl['LBL_VAT_UPDATE_MESSAGE'];
    $_SESSION['session_msg'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'access') {
    $access = isset($_REQUEST['access']) ? $_REQUEST['access'] : '';
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
            `eAccess` = '" . $access . "' $str" . $where;
    $obj->sql_query($query);
    echo $var_msg = $langage_lbl['LBL_ACCESSIBILITY_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'bankdetail') {
    $vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
    $vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';
    $vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
    $vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
    $vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
    $vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
        echo $var_msg = '2';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
            `vAccountNumber` = '" . $vAccountNumber . "',
            `vBIC_SWIFT_Code` = '" . $vBIC_SWIFT_Code . "',
            `vBankAccountHolderName` = '" . $vBankAccountHolderName . "' ,
            `vBankLocation` = '" . $vBankLocation . "',
            `vBankName` = '" . $vBankName . "',         
            `vPaymentEmail` = '" . $vPaymentEmail . "' $str" . $where;
    $id = $obj->sql_query($query);
    if ($id > 0) {
        $sql_query = "select vName,vLastName,vEmail,vCode,vPhone From register_driver" . $where;
        $result_data = $obj->sql_query($sql_query);
        if (scount($result_data) > 0) {
            $DriverName = $result_data[0]['vName'] . ' ' . $result_data[0]['vLastName'];
            $vPhonenumber = $result_data[0]['vCode'] . ' ' . $result_data[0]['vPhone'];
            $email = $result_data[0]['vEmail'];
            $maildata['NAME'] = $DriverName;
            $maildata['EMAIL'] = $email;
            $maildata['PHONE'] = $vPhonenumber;
            $COMM_MEDIA_OBJ->SendMailToMember("BANK_DETAIL_NOTIFY_ADMIN", $maildata);
        }
    }
    echo $var_msg = $langage_lbl['LBL_BANK_DETAIL_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'allInOne') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@demo.com' || $_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
        echo $var_msg = '2';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }
    /*  if ($ENABLE_EDIT_DRIVER_PROFILE == "No" &&  $userType == "DRIVER") {
         echo $var_msg = '3';
         return $var_msg;
         exit;
     } else { */
    $phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $tProfileDescription = isset($_POST['tProfileDescription']) ? $_POST['tProfileDescription'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
    $vCurrencyDriver = isset($_POST['vCurrencyDriver']) ? $_POST['vCurrencyDriver'] : '';
    $vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
    $vWorkLocationLatitude = isset($_POST['vWorkLocationLatitude']) ? $_POST['vWorkLocationLatitude'] : '';
    $vWorkLocationLongitude = isset($_POST['vWorkLocationLongitude']) ? $_POST['vWorkLocationLongitude'] : '';
    $vWorkLocation = isset($_POST['vWorkLocation']) ? $_POST['vWorkLocation'] : '';
    $vWorkLocationRadius = isset($_POST['vWorkLocationRadius']) ? $_POST['vWorkLocationRadius'] : '';
    $vVatNum = isset($_POST['vVatNum']) ? $_POST['vVatNum'] : '';
    $usertype = isset($_POST['user_type']) ? $_POST['user_type'] : '';
    $_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $vCurrencyDriver;
    $getDriverDetails = "select vName,vLastName,eStatus,vPassword,vPassword_token From register_driver where iDriverId=" . $iDriverId;
    $result_data = $obj->sql_query($getDriverDetails);
    if (scount($result_data) > 0) {
        $vName_db = $result_data[0]['vName'];
        $vLastName_db = $result_data[0]['vLastName'];
        $eStatus_db = $result_data[0]['eStatus'];
        $vPassword = $result_data[0]['vPassword'];
        $vPassword_token = $result_data[0]['vPassword_token'];
    }
    //$message = $langage_lbl['LBL_EDIT_PROFILE_DISABLED'];
    //$var_msg = '3';
    $checkEditProfileStatus = getEditDriverProfileStatus($eStatus_db);
    if (($vName_db != $name || $vLastName_db != $lname) && $checkEditProfileStatus == "No" && $_SESSION['sess_user'] == 'driver') {
        $message = $langage_lbl['LBL_PROFILE_EDIT_BLOCK_TXT'];
        //$checkEditProfileStatus = "No";
        $var_msg = '3';
        $_SESSION['message_code'] = $var_msg;
        return $var_msg;
        exit;
    }

    if ($usertype == 'company' && in_array($_SESSION['sess_eSystem'] , ['General','DeliverAll'])){
        $eSystem = $_SESSION['sess_eSystem'];
    }

    $checPhoneExist = checkMemberDataInfo($phone, "", $userType, $vCountry, $memberId, $eSystem);
    if ($checPhoneExist['status'] == 0) {
        //$res['message'] = $langage_lbl['LBL_PHONE_EXIST_MSG'];
        $var_msg = '4';
        $_SESSION['message_code'] = $var_msg;
        echo $var_msg;
        exit;
    } else if ($checPhoneExist['status'] == 2) {
        //$message = $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        $var_msg = '5';
        $_SESSION['message_code'] = $var_msg;
        echo $var_msg;
        exit;
    }
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
        //$str = ",`vCurrencyDriver`='" . $vCurrencyDriver . "', eStatus = 'active'";
        $str = ",`tProfileDescription` = '" . $tProfileDescription . "',`vWorkLocation` = '" . $vWorkLocation . "',`vWorkLocationLatitude` = '" . $vWorkLocationLatitude . "',`vWorkLocationLongitude` = '" . $vWorkLocationLongitude . "',`vWorkLocationRadius` = '" . $vWorkLocationRadius . "',`vCurrencyDriver`='" . $vCurrencyDriver . "'";
        if (isset($vWorkLocationRadius) && !empty($vWorkLocationRadius)) {
            $str .= ",`vWorkLocationRadius` = '" . $vWorkLocationRadius . "'";
        }
    } else {
        $str = ",`vCompany`='" . $vCompany . "',`vVat`='" . $vVatNum . "'";

        /*------------------for left menu name-----------------*/
        $_SESSION["sess_ProfileName"] = $vCompany;
        /*------------------for left menu name-----------------*/
    }
    $csql = "SELECT eZeroAllowed,vCountryCode,vPhoneCode FROM `country` WHERE vCountryCode = '" . $vCountry . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    $vCode = $CountryData[0]['vPhoneCode'];
    if ($eZeroAllowed == 'Yes') {
        $phone = $phone;
    } else {
        $first = substr($phone, 0, 1);
        if ($first == "0") {
            $phone = substr($phone, 1);
        }
    }
    $q = "UPDATE ";
    $sql = "select * from " . $tbl . $where;
    $edit_data = $obj->sql_query($sql);
    $pwdstr = '';
    if (!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])) {
        if ($_REQUEST['cpass'] != '') {
            $hash = $edit_data[0]['vPassword'];
            $checkValid = $AUTH_OBJ->VerifyPassword($_REQUEST['cpass'], $hash);
            if ($checkValid == 0) {
                echo "0";
                $_SESSION['message_code'] = 0;
                exit;
            }
            //$pwdstr = ",`vPassword` = '".$_REQUEST['npass']."'";
        }
    }
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $_REQUEST['email'] != $edit_data[0]['vEmail']) {
        $query = $q . " `" . $tbl . "` SET `eEmailVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $_REQUEST['phone'] != $edit_data[0]['vPhone']) {
        $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $_REQUEST['vCountry'] != $edit_data[0]['vCountry']) {
        $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver' && $APP_TYPE == 'UberX') {
        $query = $q . " `" . $tbl . "` SET `tProfileDescription` = 'No' " . $where;
        $obj->sql_query($query);
    }
    $query = $q . " `" . $tbl . "` SET
            `vEmail` = '" . $email . "',
            `vLoginId` = '" . $username . "',
            `vName` = '" . $name . "' ,
            `vLastName` = '" . $lname . "',
            `vCountry` = '" . $vCountry . "',
            `vCode` = '" . $vCode . "',
            `vPhone` = '" . $phone . "' $str" . $where;
    $obj->sql_query($query);
    /*------------------for left menu name-----------------*/
    if(in_array($_SESSION["sess_user"] , ['driver','rider'])) {
        $_SESSION["sess_ProfileName"] = $name . ' ' . $lname;
    }
    /*------------------for left menu name-----------------*/
    // Save Address
    $address1 = isset($_REQUEST['address1']) ? $_REQUEST['address1'] : '';
    $address2 = isset($_POST['address2']) ? $_POST['address2'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
    $vState = isset($_POST['vState']) ? $_POST['vState'] : '';
    $zipcode = isset($_POST['vZipcode']) ? $_POST['vZipcode'] : '';
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
                `vCaddress` = '" . $address1 . "',
                `vCadress2` = '" . $address2 . "',
                `vCity` = '" . $vCity . "',
                `vCountry` = '" . $vCountry . "',
                `vState` = '" . $vState . "',
                `vZip` = '" . $zipcode . "' $str" . $where;
    $obj->sql_query($query);
    // echo $var_msg = $langage_lbl['LBL_ADDRESS_UPDATE_MSG'];
    /* Password */
    if (!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])) {
        $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
        $npass = encrypt_bycrypt($npass);
        $q = "UPDATE ";
        $query = $q . " `" . $tbl . "` SET
                    `vPassword` = '" . $npass . "' $str" . $where;
        $obj->sql_query($query);
        // echo $var_msg = $langage_lbl['LBL_PASS_UPDATE_MSG'];
    }
    if (empty($edit_data[0]['vPassword']) && (empty($edit_data[0]['vPassword_token']) || !empty($edit_data[0]['vFbId'])) && (!empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass']) && $_SESSION['sess_user'] == 'driver')) {
        $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
        $npass = encrypt_bycrypt($npass);
        $q = "UPDATE ";
        $query = $q . " `" . $tbl . "` SET
                    `vPassword` = '" . $npass . "' $str" . $where;
        $obj->sql_query($query);
    }
    // Language
    $lang = isset($_REQUEST['lang1']) ? $_REQUEST['lang1'] : '';
    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
                `vLang` = '" . $lang . "' $str" . $where;
    $obj->sql_query($query);
    $_SESSION["sess_lang"] = $lang;
    $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
    $lang = $obj->MySQLSelect($sql);
    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
    // echo $var_msg = $langage_lbl['LBL_LANG_UPDATE_MSG'];
    // Vat
    $vat = isset($_REQUEST['vat']) ? $_REQUEST['vat'] : '';
    if ($vat) {
        $q = "UPDATE ";
        $query = $q . " `" . $tbl . "` SET
                    `vVat` = '" . $vat . "' $str" . $where;
        $obj->sql_query($query);
        // echo $var_msg = $langage_lbl['LBL_VAT_UPDATE_MESSAGE'];
    }
    // Access
    $access = isset($_REQUEST['access']) ? $_REQUEST['access'] : '';
    if ($access) {
        $q = "UPDATE ";
        $query = $q . " `" . $tbl . "` SET
                    `eAccess` = '" . $access . "' $str" . $where;
        $obj->sql_query($query);
        // echo $var_msg = $langage_lbl['LBL_ACCESSIBILITY_UPDATE_MSG'];
    }
    // Bank Details
    if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
        $vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
        $vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';
        $vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
        $vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
        $vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
        $vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
        if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'company@gmail.com' || $_SESSION['sess_vEmail'] == 'provider@demo.com')) {
            echo $var_msg = '2';
            $_SESSION['message_code'] = $var_msg;
            return $var_msg;
            exit;
        }
        $q = "UPDATE ";
        $query = $q . " `" . $tbl . "` SET
                        `vAccountNumber` = '" . $vAccountNumber . "',
                        `vBIC_SWIFT_Code` = '" . $vBIC_SWIFT_Code . "',
                        `vBankAccountHolderName` = '" . $vBankAccountHolderName . "' ,
                        `vBankLocation` = '" . $vBankLocation . "',
                        `vBankName` = '" . $vBankName . "',         
                        `vPaymentEmail` = '" . $vPaymentEmail . "' $str" . $where;
        $id = $obj->sql_query($query);
        if ($id > 0) {
            $sql_query = "select vName,vLastName,vEmail,vCode,vPhone From register_driver" . $where;
            $result_data = $obj->sql_query($sql_query);
            if (scount($result_data) > 0) {
                $DriverName = $result_data[0]['vName'] . ' ' . $result_data[0]['vLastName'];
                $vPhonenumber = $result_data[0]['vCode'] . ' ' . $result_data[0]['vPhone'];
                $email = $result_data[0]['vEmail'];
                $maildata['NAME'] = $DriverName;
                $maildata['EMAIL'] = $email;
                $maildata['PHONE'] = $vPhonenumber;
                $COMM_MEDIA_OBJ->SendMailToMember("BANK_DETAIL_NOTIFY_ADMIN", $maildata);
            }
        }
    }
    $var_msg = $langage_lbl['LBL_PORFILE_UPDATE_MSG'];
    // echo $var_msg = $langage_lbl['LBL_BANK_DETAIL_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    $var_msg = '1';
    $_SESSION['message_code'] = '1';
    echo $var_msg;
    exit;
    //}
}

if ($action == 'allInOne_tracking_company') {

    $phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
    $vCompany = isset($_REQUEST['vCompany']) ? $_REQUEST['vCompany'] : '';
    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $vVatNum = isset($_REQUEST['vVatNum']) ? $_REQUEST['vVatNum'] : '';
    $vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
    $vState = isset($_REQUEST['vState']) ? $_REQUEST['vState'] : '';
    $vZipcode = isset($_REQUEST['vZipcode']) ? $_REQUEST['vZipcode'] : '';
    $address1 = isset($_REQUEST['address1']) ? $_REQUEST['address1'] : '';
    $lang = isset($_REQUEST['lang1']) ? $_REQUEST['lang1'] : '';

    $vLocation = isset($_REQUEST['vLocation']) ? $_REQUEST['vLocation'] : '';
    $vLatitude = isset($_REQUEST['vLatitude']) ? $_REQUEST['vLatitude'] : '';
    $vLongitude = isset($_REQUEST['vLongitude']) ? $_REQUEST['vLongitude'] : '';

    $sql = "select * from " . $tbl . " WHERE " . $where;
    $edit_data = $obj->sql_query($sql);

    if (!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])) {
        if ($_REQUEST['cpass'] != '') {
            $hash = $edit_data[0]['vPassword'];
            $checkValid = $AUTH_OBJ->VerifyPassword($_REQUEST['cpass'], $hash);
            if ($checkValid == 0) {
                echo "0";
                 $_SESSION['message_code'] = 0;
                exit;
            }
        }
    }
    $update_data = [];
    if (!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])) {
        $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
        $npass = encrypt_bycrypt($npass);
        $update_data['vPassword'] = $npass;
    }

    $update_data['vCompany'] = $vCompany;
    $update_data['vEmail'] = $email;
    $update_data['vPhone'] = $phone;
    $update_data['vLang'] = $lang;
    $update_data['vVat'] = $vVatNum;
    $update_data['vAddress'] = $address1;
    $update_data['vState'] = $vState;
    $update_data['vCountry'] = $vCountry;
    $update_data['vZip'] = $vZipcode;

    $update_data['vLocation'] = $vLocation;
    $update_data['vLatitude'] = $vLatitude;
    $update_data['vLongitude'] = $vLongitude;

    $id = $obj->MySQLQueryPerform("track_service_company", $update_data, 'update', $where);
    $var_msg = $langage_lbl['LBL_PORFILE_UPDATE_MSG'];
    $_SESSION['session_msg'] = $var_msg;
    echo $var_msg;
    exit;
}

?>