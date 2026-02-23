<?php
include_once('common.php');

global $LANG_OBJ;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$user_type = isset($_POST['type_usr']) ? $_POST['type_usr'] : '';
$phoneCode = isset($_POST['phoneCode']) ? $_POST['phoneCode'] : '';
$CountryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
$CompSystem = isset($_POST['CompSystem']) ? $_POST['CompSystem'] : '';
$fromOrder = "guest";
$default_currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
$lCode = $LANG_OBJ->FetchDefaultLangData("vCode");

if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}

$eSystem = $countryCode = $id = "";
$npass = encrypt($pass);
$remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';

if ($action == 'driver') {

    unset($_SESSION['fareestimate_eType']);

    unset($_SESSION['fareestimate_from_lat']);

    unset($_SESSION['fareestimate_from_long']);

    unset($_SESSION['fareestimate_to_lat']);

    unset($_SESSION['fareestimate_to_long']);

    unset($_SESSION['fareestimate_iVehicleCategoryId']);


    $tbl_d = $tbl = '';
    $db_driver = $db_comp = array();
    if ($user_type == 'Driver') {
        $userType = "driver";
        $countryData = get_value('country', 'vPhoneCode', 'vCountryCode', $CountryCode);
        $data = AllowphoneNumWithZero($user_type, $email, 'vPhone', 'register_driver', $countryData[0]['vPhoneCode']);
        $trackService = '';
        if ($MODULES_OBJ->isEnableTrackServiceFeature()) {
            $trackService = "iTrackServiceCompanyId,";
        }
        $sql = "SELECT {$trackService}  iDriverId,vCode,vCompany, iCompanyId, vName, vLastName, vEmail, vPhone,eStatus, vCurrencyDriver,vPassword,vLang FROM register_driver WHERE ((vEmail = '" . $email . "') OR (vPhone = '" . $email . "' {$data} ))";
        $db_driver = $obj->MySQLSelect($sql);
    } else {

        $userType = "company";
        $sql = "SELECT iCompanyId,vCode,vCompany, vName, vLang, vLastName, vEmail,vPhone, eStatus,vPassword,eSystem,tSessionId from company WHERE ((vEmail = '" . $email . "') OR (vPhone = '" . $email . "' OR TRIM(LEADING 0 FROM vPhone) = '".ltrim($email, '0')."')) AND eSystem = '" . $CompSystem . "'";
        $db_comp = $obj->MySQLSelect($sql);

    }

    if (scount($db_driver) > 0) {
        //$hash = $db_driver[0]['vPassword'];
        //$checkValid = $AUTH_OBJ->VerifyPassword($pass, $hash);
        //$checkValid = validateMember($email, $pass,$userType);
        $countryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
        if ($_REQUEST['isEmailvEmail'] == 'Yes') {
            // $countryCode = '';
        }

        $checkValid = checkMemberDataInfo($email, $pass, $userType, $countryCode, $id, $eSystem);
        if ($checkValid['status'] == 1) {

            /*Unset Admin session variables*/
            unset($_SESSION['sess_iAdminUserId']);
            unset($_SESSION["sess_vAdminFirstName"]);
            unset($_SESSION["sess_vAdminLastName"]);
            unset($_SESSION["sess_vAdminEmail"]);
            unset($_SESSION["sess_iGroupId"]);
            /*Unset Admin session variables*/

            $db_driver = array();
            $db_driver[0] = $checkValid['USER_DATA'];
            //if ($checkValid == 1) {
            if ($db_driver[0]['eStatus'] != 'Deleted') {

                if($_SESSION["sess_currency"] != $default_currency && $db_driver[0]['vCurrencyDriver'] != $_SESSION["sess_currency"]){
                    $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                    $update_sql = "UPDATE register_driver set vCurrencyDriver='".$_SESSION["sess_currency"]."' WHERE iDriverId='" .  $db_driver[0]['iDriverId'] . "'";
                    $db_update = $obj->sql_query($update_sql);
                } else if (isset($_COOKIE['ChangeCurrencyToDefault']) && $_COOKIE['ChangeCurrencyToDefault'] == 'Yes') {
                    $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                    $update_sql = "UPDATE register_driver set vCurrencyDriver='".$_SESSION["sess_currency"]."' WHERE iDriverId='" .  $db_driver[0]['iDriverId'] . "'";
                    $db_update = $obj->sql_query($update_sql);
                    setcookie("ChangeCurrencyToDefault", "", 0, '/');
                    unset($_COOKIE['ChangeCurrencyToDefault']);
                } else {
                    $_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $db_driver[0]['vCurrencyDriver'];
                }

                $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
                $lang = $obj->MySQLSelect($sql);

                if($_SESSION["sess_lang"] != $lCode && $db_driver[0]['vLang'] != $_SESSION["sess_lang"]){
                    $update_sql1 = "UPDATE register_driver set vLang = '".$_SESSION["sess_lang"]."' WHERE iDriverId='" .  $db_driver[0]['iDriverId'] . "'";
                    $db_update = $obj->sql_query($update_sql1);
                } else if (isset($_COOKIE['ChangeCurrencyToDefault']) && $_COOKIE['ChangeLanguageToDefault'] == 'Yes'){
                    $update_sql1 = "UPDATE register_driver set vLang = '".$_SESSION["sess_lang"]."' WHERE iDriverId='" .  $db_driver[0]['iDriverId'] . "'";
                    $db_update = $obj->sql_query($update_sql1);
                    setcookie("ChangeLanguageToDefault", "", 0, '/');
                    unset($_COOKIE['ChangeLanguageToDefault']);
                } else  {
                    $_SESSION["sess_lang"] = $db_driver[0]['vLang'];
                }

                $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
                $_SESSION["sess_iUserId"] = $db_driver[0]['iDriverId'];
                $_SESSION["sess_iCompanyId"] = $db_driver[0]['iCompanyId'];
                $_SESSION["sess_ProfileName"] = $_SESSION["sess_vCompany"] = $db_driver[0]['vCompany'];
                $_SESSION["sess_vName"] = $db_driver[0]['vName'];
                $_SESSION["sess_vLastName"] = $db_driver[0]['vLastName'];
                $_SESSION["sess_vEmail"] = $db_driver[0]['vEmail'];
                $_SESSION["sess_vPhone"] = $db_driver[0]['vPhone'];
                $_SESSION["sess_user"] = "driver";
                $_SESSION["sess_ProfileName"] = $_SESSION["sess_vName"] .' '.$_SESSION["sess_vLastName"];
                $_SESSION["vAvgRating"] =  $db_driver[0]['vAvgRating'];
                /*------------------for the image-----------------*/

                $tsite_upload_images_driver_path = $tconfig["tsite_upload_images_driver_path"] . '/'  . $db_driver[0]['iDriverId'] . '/';
                $tsite_upload_images_driver = $tconfig["tsite_upload_images_driver"] . '/'  . $db_driver[0]['iDriverId'] . '/';
                $_SESSION["sess_vImage"] = getProfileImage($db_driver[0]['vImage'], $tconfig["tsite_url"],$tsite_upload_images_driver_path,$tsite_upload_images_driver);

                /*------------------for the image-----------------*/

                if ($MODULES_OBJ->isEnableTrackServiceFeature()) {
                    $_SESSION["IsTrackServiceDriver"] = $db_driver[0]['iTrackServiceCompanyId'] > 0 ? 'Yes' : 'No';
                }else{
                    $_SESSION["IsTrackServiceDriver"] =  'No';
                }
                if (SITE_TYPE == 'Demo') {
                    $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $_SESSION["sess_iUserId"] . "', 'Driver', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                    $obj->sql_query($login_sql);
                    $update_sql = "UPDATE register_driver set tRegistrationDate='" . date('Y-m-d H:i:s') . "' WHERE iDriverId='" . $_SESSION["sess_iUserId"] . "'";
                    $db_update = $obj->sql_query($update_sql);
                }

                if ($remember == "Yes") {
                    setcookie("member_login_cookie", $email, time() + 2592000);
                    setcookie("member_password_cookie", $pass, time() + 2592000);
                } else {
                    setcookie("member_login_cookie", "", time());
                    setcookie("member_password_cookie", "", time());
                }
                //save login log added by Rs start
                createUserLog('Driver', 'Yes', $db_driver[0]['iDriverId'], 'Web');
                $json_data = array('login_status' => 2);
                echo json_encode($json_data);
                exit;
            } else {
                $json_data = array('login_status' => 1);
                echo json_encode($json_data);
                exit;
            }
        }
        elseif ($checkValid['status'] == 2) {
            $json_data = array('login_status' => 5);
            echo json_encode($json_data);
            exit;
        }
        else {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
    }
    else {
        if (scount($db_comp) > 0) {
            $_SESSION['postDetail']['user_type'] = "company"; //addd by SP for cubex design, its moved from the login file to the here on 16-8-2019
            //$hash = $db_comp[0]['vPassword'];
            //$checkValid = $AUTH_OBJ->VerifyPassword($pass, $hash);
            $countryCode = $id = "";
            // $countryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
            $eSystem = $db_comp[0]['eSystem'];
            $tSessionId = $db_comp[0]['tSessionId'];
            $countryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
            if ($_REQUEST['isEmailvEmail'] == 'Yes') {
                // $countryCode = '';
            }
            $checkValid = checkMemberDataInfo($email, $pass, $userType, $countryCode, $id, $eSystem);
            if ($checkValid['status'] == 1) {
                /*Unset Admin session variables*/
                unset($_SESSION['sess_iAdminUserId']);
                unset($_SESSION["sess_vAdminFirstName"]);
                unset($_SESSION["sess_vAdminLastName"]);
                unset($_SESSION["sess_vAdminEmail"]);
                unset($_SESSION["sess_iGroupId"]);
                /*Unset Admin session variables*/
                $db_comp = array();
                $db_comp[0] = $checkValid['USER_DATA'];
                //if ($checkValid == 1) {
                if ($db_comp[0]['eStatus'] != 'Deleted') {
                    //$vLang = $db_comp[0]['vLang'];
                    //if(empty($_SESSION['eDirectionCode'])) {
                    //$sql = "select eDirectionCode from language_master where vCode='".$vLang."'";
                    $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
                    $lang = $obj->MySQLSelect($sql);
                    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
                    //}
                    $_SESSION["sess_iUserId"] = $db_comp[0]['iCompanyId'];
                    $_SESSION["sess_ProfileName"] = $_SESSION["sess_vCompany"] = $db_comp[0]['vCompany'];
                    $_SESSION["sess_iCompanyId"] = $db_comp[0]['iCompanyId'];
                    $_SESSION["sess_vName"] = $db_comp[0]['vName'];
                    $_SESSION["sess_vLastName"] = $db_comp[0]['vLastName'];
                    $_SESSION["sess_vEmail"] = $db_comp[0]['vEmail'];
                    $_SESSION["sess_vPhone"] = $db_comp[0]['vPhone'];
                    $_SESSION["sess_eSystem"] = $eSystem;
                    $_SESSION["sess_user"] = "company";


                    /*------------------for the image-----------------*/
                    $tsite_upload_images_compnay_path = $tconfig["tsite_upload_images_compnay_path"] . '/'  . $db_comp[0]['iCompanyId'] . '/';
                    $tsite_upload_images_compnay = $tconfig["tsite_upload_images_compnay"] . '/'  . $db_comp[0]['iCompanyId'] . '/';
                    $_SESSION["sess_vImage"] = getProfileImage($db_comp[0]['vImage'], $tconfig["tsite_url"],$tsite_upload_images_compnay_path,$tsite_upload_images_compnay);
                    /*------------------for the image-----------------*/

                    // comment currency cause we are not providing currency in store
                    if($eSystem != 'DeliverAll'){
                        if($_SESSION["sess_currency"] != $default_currency && $db_comp[0]['vCurrencyCompany'] != $_SESSION["sess_currency"]){
                            $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                            $update_sql = "UPDATE company set vCurrencyCompany='".$_SESSION["sess_currency"]."' WHERE iCompanyId='" .  $db_comp[0]['iCompanyId'] . "'";
                            $db_update = $obj->sql_query($update_sql);
                        } else if ($_COOKIE['ChangeCurrencyToDefault'] == 'Yes') {
                            $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                            $update_sql = "UPDATE company set vCurrencyCompany='".$_SESSION["sess_currency"]."' WHERE iCompanyId='" .  $db_comp[0]['iCompanyId'] . "'";
                            $db_update = $obj->sql_query($update_sql);
                            setcookie("ChangeCurrencyToDefault", "", 0, '/');
                            unset($_COOKIE['ChangeCurrencyToDefault']);
                        } else {
                            $_SESSION["sess_currency"] =  $_SESSION["sess_vCurrency"] = $db_comp[0]['vCurrencyCompany'];
                        }
                    } else {
                        $_SESSION["sess_currency"] =  $_SESSION["sess_vCurrency"] = $db_comp[0]['vCurrencyCompany'];
                    }

                    //$Data_update_passenger['tSessionId'] = session_id() . time();
                    $ssqlsession = "";
                    if (empty($tSessionId)) {
                        $ssqlsession = ",tSessionId='" . session_id() . time() . "'";
                    }

                    if($_SESSION["sess_lang"] != $lCode && $db_comp[0]['vLang'] != $_SESSION["sess_lang"]){
                        $update_sql = "UPDATE company set vLang='" . $_SESSION["sess_lang"] . "' $ssqlsession WHERE iCompanyId='" . $_SESSION["sess_iUserId"] . "'";
                        $db_update = $obj->sql_query($update_sql);
                    } else if ($_COOKIE['ChangeLanguageToDefault'] == 'Yes'){
                        $update_sql = "UPDATE company set vLang='" . $_SESSION["sess_lang"] . "' $ssqlsession WHERE iCompanyId='" . $_SESSION["sess_iUserId"] . "'";
                        $db_update = $obj->sql_query($update_sql);
                        setcookie("ChangeLanguageToDefault", "", 0, '/');
                        unset($_COOKIE['ChangeLanguageToDefault']);
                    } else {
                        $_SESSION["sess_lang"] = $db_comp[0]['vLang'];
                    }

                    if ($remember == "Yes") {
                        setcookie("member_login_cookie", $email, time() + 2592000);
                        setcookie("member_password_cookie", $pass, time() + 2592000);
                    }
                    else {
                        setcookie("member_login_cookie", "", time());
                        setcookie("member_password_cookie", "", time());
                    }
                    //User login log added by Rs start
                    if ($eSystem == 'DeliverAll')
                        createUserLog('Store', 'Yes', $db_comp[0]['iCompanyId'], 'Web');
                    else
                        createUserLog('Company', 'Yes', $db_comp[0]['iCompanyId'], 'Web');
                    $json_data = array('login_status' => 2, 'eSystem' => $eSystem);
                    echo json_encode($json_data);
                    exit;
                }
                else {
                    $json_data = array('login_status' => 1);
                    echo json_encode($json_data);
                    exit;
                }
            }
            elseif ($checkValid['status'] == 2) {
                $json_data = array('login_status' => 5);
                echo json_encode($json_data);
                exit;
            }
            else {
                $json_data = array('login_status' => 3);
                echo json_encode($json_data);
                exit;
            }
        }
        else {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
    }
}

if ($action == 'rider') {
    $tbl = 'register_user';
    $userType = $action;
    //$fields = 'iUserId, vName, vEmail, eStatus, vCurrencyPassenger, vPhone,vPassword,vLang,vCountry';
    //$sql = "SELECT $fields FROM $tbl WHERE (vEmail = '" . $email . "' OR vPhone = '" . $email . "')";
    //$db_logins = $obj->MySQLSelect($sql);
    //if (scount($db_logins) > 0) {
    //$hash = $db_logins[0]['vPassword'];
    /* 04-09-2019 Process to change mobile number as per country - one country has one number - same number can available as per different countries start */
    //$checkValid = validateMember($email, $pass,$userType);
    $countryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
    if ($_REQUEST['isEmailvEmail'] == 'Yes') {
        // $countryCode = '';
    }
    $checkValid = checkMemberDataInfo($email, $pass, $userType, $countryCode, $id, $eSystem);
    // $checkValid = checkMemberDataInfoLogin($email, $pass, $userType, $countryCode, $id, $eSystem);
    /* 04-09-2019 end */
    if ($checkValid['status'] == 1) {
        /*Unset Admin session variables*/
        unset($_SESSION['sess_iAdminUserId']);
        unset($_SESSION["sess_vAdminFirstName"]);
        unset($_SESSION["sess_vAdminLastName"]);
        unset($_SESSION["sess_vAdminEmail"]);
        unset($_SESSION["sess_iGroupId"]);
        /*Unset Admin session variables*/
        $db_login = array();
        $db_login[0] = $checkValid['USER_DATA'];
       
        if ($db_login[0]['eStatus'] != "Deleted" && $db_login[0]['eStatus'] != "Inactive") {

            $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
            $lang = $obj->MySQLSelect($sql);
            // to solve currency issue after login 
            if($_SESSION["sess_currency"] != $default_currency && $db_login[0]['vCurrencyPassenger'] != $_SESSION["sess_currency"]){
                $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                $update_sql = "UPDATE register_user set vCurrencyPassenger='".$_SESSION["sess_currency"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql);
            } else if ($_COOKIE['ChangeCurrencyToDefault'] == 'Yes') {
                $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                $update_sql = "UPDATE register_user set vCurrencyPassenger='".$_SESSION["sess_currency"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql);
                setcookie("ChangeCurrencyToDefault", "", 0, '/');
                unset($_COOKIE['ChangeCurrencyToDefault']);
            } else {
                $_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $db_login[0]['vCurrencyPassenger'];
            }
            // to solve currency issue after login 
            $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
            $_SESSION['sess_iUserId'] = $db_login[0]['iUserId'];
            $_SESSION["sess_vName"] = $db_login[0]['vName'];
            if($_SESSION["sess_lang"] != $lCode && $db_login[0]['vLang'] != $_SESSION["sess_lang"]){
                $update_sql1 = "UPDATE register_user set vLang = '".$_SESSION["sess_lang"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql1);
            } else if ($_COOKIE['ChangeLanguageToDefault'] == 'Yes'){
                $update_sql1 = "UPDATE register_user set vLang = '".$_SESSION["sess_lang"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql1);
                setcookie("ChangeLanguageToDefault", "", 0, '/');
                unset($_COOKIE['ChangeLanguageToDefault']);
            } else {
                $_SESSION["sess_lang"] = $db_login[0]['vLang'];
            }
            
            $_SESSION["sess_vEmail"] = $db_login[0]['vEmail'];
            $_SESSION["sess_vPhone"] = $db_login[0]['vPhone'];
            $_SESSION["sess_user"] = "rider";
            $_SESSION["sess_ProfileName"] =  $db_login[0]['vName'].' '.$db_login[0]['vLastName'];
            $_SESSION["vAvgRating"] =  $db_login[0]['vAvgRating'];


            /*------------------for the image-----------------*/

            $tsite_upload_images_user_path = $tconfig["tsite_upload_images_passenger_path"] . '/'  . $db_login[0]['iUserId'] . '/';
            $tsite_upload_images_user = $tconfig["tsite_upload_images_passenger"] . '/'  . $db_login[0]['iUserId'] . '/';
            $_SESSION["sess_vImage"] = getProfileImage($db_login[0]['vImgName'], $tconfig["tsite_url"],$tsite_upload_images_user_path,$tsite_upload_images_user);


            /*------------------for the image-----------------*/
            //$_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $db_login[0]['vCurrencyPassenger'];
            if (SITE_TYPE == 'Demo') {
                $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $_SESSION["sess_iUserId"] . "', 'Passenger', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                $obj->sql_query($login_sql);
                $update_sql = "UPDATE register_user set tRegistrationDate='" . date('Y-m-d H:i:s') . "' WHERE iUserId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
            }

            if ($remember == "Yes") {
                setcookie("member_login_cookie", $vEmail, time() + 2592000);
                setcookie("member_password_cookie", $vPassword, time() + 2592000);
            }
            else {
                setcookie("member_login_cookie", "", time());
                setcookie("member_password_cookie", "", time());
            }

            $genUserData = $obj->MySQLSelect("SELECT tSessionId FROM $tbl WHERE iUserId = '" . $db_login[0]['iUserId'] . "' ");
            $tSessionId = $genUserData[0]['tSessionId'];
            if(empty($tSessionId)) {
                $tSessionId = session_id() . time();
                $obj->sql_query("UPDATE $tbl SET tSessionId = '$tSessionId' WHERE iUserId = '" . $db_login[0]['iUserId'] . "' ");
            }

            createUserLog('Passenger', 'Yes', $db_login[0]['iUserId'], 'Web');
            $json_data = array('login_status' => 2);
            echo json_encode($json_data);
            exit;
        }
        else {
            if ($db_login[0]['eStatus'] == "Deleted") {
                $json_data = array('login_status' => 1);
                echo json_encode($json_data);
                exit;
            }
            else {
                $json_data = array('login_status' => 4);
                echo json_encode($json_data);
                exit;
            }
        }
    }
    elseif ($checkValid['status'] == 2) {
        $json_data = array('login_status' => 5);
        echo json_encode($json_data);
        exit;
    }
    else {
        $json_data = array('login_status' => 3);
        echo json_encode($json_data);
        exit;
    }
    /* } else {
      $json_data = array('login_status' => 3);
      echo json_encode($json_data);
      exit;
      } */
}

//added by SP for cubex changes 
if ($action == 'hotel') {
    //$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
    //$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
    $group_id = 4; //isset($_POST['group_id']) ? $_POST['group_id'] : '';
    $email = isset($_POST['vEmailh']) ? $_POST['vEmailh'] : '';
    $hdn_HTTP_REFERER = isset($_POST['hdn_HTTP_REFERER']) ? $_POST['hdn_HTTP_REFERER'] : '';
    $_SESSION['hdn_HTTP_REFERER'] = $hdn_HTTP_REFERER;
    $remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';
    $tbl = 'administrators';
    $fields = 'iAdminId, vFirstName,vLastName, vEmail, eStatus, iGroupId, vPassword';
    //Added By HJ On 31-01-2019 For Login All Admin From All Admin Tab As Per Discuss With CD,KL Sir and Also BM QA Mam Start
    if (isset($group_id) && !empty($group_id) && $group_id != '1') {
        $sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
        $db_login = $obj->MySQLSelect($sql);
        $sql = "SELECT vEmail from $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
        $db_mail = $obj->MySQLSelect($sql);
    }
    else {
        $sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "'";
        $db_login = $obj->MySQLSelect($sql);
        $sql = "SELECT vEmail from $tbl WHERE vEmail = '" . $email . "'";
        $db_mail = $obj->MySQLSelect($sql);
    }
    //Added By HJ On 31-01-2019 For Login All Admin From All Admin Tab As Per Discuss With CD,KL Sir and Also BM QA Mam End
    //Comment By HJ On 31-01-2019 As Per Discuss With CD,KL Sir and Also BM QA Mam Start - FOr Login Particular Admin Enabel This
    /* $sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
      $db_login = $obj->MySQLSelect($sql);
    
      $sql = "SELECT vEmail from $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
      $db_mail = $obj->MySQLSelect($sql); */
    //Comment By HJ On 31-01-2019 As Per Discuss With CD,KL Sir and Also BM QA Mam End - FOr Login Particular Admin Enabel This
    if (scount($db_login) == 0) {
        if (scount($db_mail) > 0) {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
        else {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
    }
    if (scount($db_login) > 0) {
        $hash = $db_login[0]['vPassword'];
        $checkValid = $AUTH_OBJ->VerifyPassword($pass, $hash);
        if ($checkValid == 0) {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
        if ($db_login[0]['eStatus'] != 'Active') {
            $json_data = array('login_status' => 4);
            echo json_encode($json_data);
            exit;
        }
        else {
            $_SESSION['sess_iAdminUserId'] = $db_login[0]['iAdminId'];
            $_SESSION['sess_iGroupId'] = $db_login[0]['iGroupId'];
            $_SESSION["sess_vAdminFirstName"] = $db_login[0]['vFirstName'];
            $_SESSION["sess_vAdminLastName"] = $db_login[0]['vLastName'];
            $_SESSION["sess_vAdminEmail"] = $db_login[0]['vEmail'];
            if ($db_login[0]['iGroupId'] == '4') {
                $_SESSION["SessionUserType"] = 'hotel';

                $sql = "SELECT * FROM `hotel` WHERE  iAdminId = '" . $db_login[0]['iAdminId'] . "'";
                $hotel_data = $obj->MySQLSelect($sql);
                if(isset($hotel_data) && !empty($hotel_data))
                {
                    $_SESSION["sess_iHotelId"] = $hotel_data[0]['iHotelId'];
                }
            }
            //save login log added by Rs start
            if ($db_login[0]['iGroupId'] == '4')
                $checkValid = createUserLog('Hotel', 'Yes', $db_login[0]['iAdminId'], 'Web');
            else
                $checkValid = createUserLog('Admin', 'Yes', $db_login[0]['iAdminId'], 'Web');
            //save login log added by Rs end
            if (SITE_TYPE == 'Demo') {
                $q = "UPDATE company SET `tRegistrationDate` = '" . date("Y-m-d H:i:s") . "' WHERE `iCompanyId` = '1'";
                $obj->sql_query($q);
            }
            if ($remember == "Yes") {
                setcookie("member_login_cookie", $email, time() + 2592000);
                setcookie("member_password_cookie", $pass, time() + 2592000);
            }
            else {
                setcookie("member_login_cookie", "", time());
                setcookie("member_password_cookie", "", time());
            }
            $_SESSION["SessionRedirectUserPanel"] = 'Yes';
            $json_data = array('login_status' => 2);
            echo json_encode($json_data);
            exit;
        }
    }
}

if($action == 'verify_otp'){
    
    if(strtolower($user_type) == 'driver'){
        $tblname = 'register_driver';
        $vPhoneCode = 'vCode';
        $memberId = 'iDriverId';
    } else if (strtolower($user_type) == 'company'){
        $tblname = 'company';
         $vPhoneCode = 'vCode';
         $memberId = 'iCompanyId';
    } else {
        $tblname = 'register_user';
         $vPhoneCode = 'vPhoneCode';
         $memberId = 'iUserId';
    }
    $row = $obj->MySQLSelect("SELECT * FROM $tblname WHERE vPhone='" . $email . "' AND $vPhoneCode = '".$CountryCode."'");

    if($row[0]['vOTP'] == $_REQUEST['otp']){
        $db_login = array();
        $db_login[0] = $row[0];

        /*Unset Admin session variables*/
        unset($_SESSION['sess_iAdminUserId']);
        unset($_SESSION["sess_vAdminFirstName"]);
        unset($_SESSION["sess_vAdminLastName"]);
        unset($_SESSION["sess_vAdminEmail"]);
        unset($_SESSION["sess_iGroupId"]);
        /*Unset Admin session variables*/

        if(strtolower($user_type) == 'driver'){
            $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
            $lang = $obj->MySQLSelect($sql);
            if($_SESSION["sess_currency"] != $default_currency && $db_login[0]['vCurrencyDriver'] != $_SESSION["sess_currency"]){
                $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                $update_sql = "UPDATE register_driver set vCurrencyDriver='".$_SESSION["sess_currency"]."' WHERE iDriverId='" .  $db_login[0]['iDriverId'] . "'";
                $db_update = $obj->sql_query($update_sql);
            } else if ($_COOKIE['ChangeCurrencyToDefault'] == 'Yes') {
                $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                $update_sql = "UPDATE register_driver set vCurrencyDriver='".$_SESSION["sess_currency"]."' WHERE iDriverId='" .  $db_login[0]['iDriverId'] . "'";
                $db_update = $obj->sql_query($update_sql);
                setcookie("ChangeCurrencyToDefault", "", 0, '/');
                unset($_COOKIE['ChangeCurrencyToDefault']);
            } else {
                $_SESSION["sess_vCurrency"]  = $_SESSION["sess_currency"] = $db_login[0]['vCurrencyDriver'];
            }

            $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
            if($_SESSION["sess_lang"] != $lCode && $db_login[0]['vLang'] != $_SESSION["sess_lang"]){
                $update_sql1 = "UPDATE register_driver set vLang = '".$_SESSION["sess_lang"]."' WHERE iDriverId='" .  $db_login[0]['iDriverId'] . "'";
                $db_update = $obj->sql_query($update_sql1);
            } else if ($_COOKIE['ChangeLanguageToDefault'] == 'Yes'){
                $update_sql1 = "UPDATE register_driver set vLang = '".$_SESSION["sess_lang"]."' WHERE iDriverId='" .  $db_login[0]['iDriverId'] . "'";
                $db_update = $obj->sql_query($update_sql1);
                setcookie("ChangeLanguageToDefault", "", 0, '/');
                unset($_COOKIE['ChangeLanguageToDefault']);
            } else {
                $_SESSION["sess_lang"] = $db_login[0]['vLang'];
            }
            $_SESSION["sess_iUserId"] = $db_login[0]['iDriverId'];
            $_SESSION["sess_iCompanyId"] = $db_login[0]['iCompanyId'];
            $_SESSION["sess_vCompany"] = $db_login[0]['vCompany'];
            $_SESSION["sess_vName"] = $db_login[0]['vName'];
            $_SESSION["sess_vLastName"] = $db_login[0]['vLastName'];
            $_SESSION["sess_vEmail"] = $db_login[0]['vEmail'];
            $_SESSION["sess_vPhone"] = $db_login[0]['vPhone'];
            $_SESSION["sess_user"] = "driver";
            $_SESSION["sess_ProfileName"] = $_SESSION["sess_vName"] .' '.$_SESSION["sess_vLastName"];
            $_SESSION["vAvgRating"] =  $db_login[0]['vAvgRating'];
            /*------------------for the image-----------------*/

            $tsite_upload_images_driver_path = $tconfig["tsite_upload_images_driver_path"] . '/'  . $db_login[0]['iDriverId'] . '/';
            $tsite_upload_images_driver = $tconfig["tsite_upload_images_driver"] . '/'  . $db_login[0]['iDriverId'] . '/';
            $_SESSION["sess_vImage"] = getProfileImage($db_login[0]['vImage'], $tconfig["tsite_url"],$tsite_upload_images_driver_path,$tsite_upload_images_driver);

            /*------------------for the image-----------------*/
            if ($MODULES_OBJ->isEnableTrackServiceFeature()) {
                $_SESSION["IsTrackServiceDriver"] = $db_login[0]['iTrackServiceCompanyId'] > 0 ? 'Yes' : 'No';
            }else{
                $_SESSION["IsTrackServiceDriver"] =  'No';
            }

            if (SITE_TYPE == 'Demo') {
                $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $_SESSION["sess_iUserId"] . "', 'Driver', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                $obj->sql_query($login_sql);
                $update_sql = "UPDATE register_driver set tRegistrationDate='" . date('Y-m-d H:i:s') . "' WHERE iDriverId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
            }

            if ($remember == "Yes") {
                setcookie("member_login_cookie", $email, time() + 2592000);
                setcookie("member_password_cookie", $pass, time() + 2592000);
            }
            else {
                setcookie("member_login_cookie", "", time());
                setcookie("member_password_cookie", "", time());
            }
            //save login log added by Rs start
            createUserLog('Driver', 'Yes', $db_login[0]['iDriverId'], 'Web');

            $genUserData = $obj->MySQLSelect("SELECT tSessionId FROM $tblname WHERE $memberId = '" . $db_login[0]['iDriverId'] . "' ");
            $tSessionId = $genUserData[0]['tSessionId'];
            if(empty($tSessionId)) {
                $tSessionId = session_id() . time();
                $obj->sql_query("UPDATE $tblname SET tSessionId = '$tSessionId' WHERE $memberId = '" . $db_login[0]['iDriverId'] . "' ");
            }

            $obj->sql_query("UPDATE $tblname SET ePhoneVerified = 'Yes' WHERE $memberId = '" . $db_login[0]['iDriverId'] . "' ");
        } else if (strtolower($user_type) == 'rider'){
            $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
            $lang = $obj->MySQLSelect($sql);
            if($_SESSION["sess_currency"] != $default_currency && $db_login[0]['vCurrencyPassenger'] != $_SESSION["sess_currency"]){
                $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                $update_sql = "UPDATE register_user set vCurrencyPassenger='".$_SESSION["sess_currency"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql);
            } else if ($_COOKIE['ChangeCurrencyToDefault'] == 'Yes') {
                $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                $update_sql = "UPDATE register_user set vCurrencyPassenger='".$_SESSION["sess_currency"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql);
                setcookie("ChangeCurrencyToDefault", "", 0, '/');
                unset($_COOKIE['ChangeCurrencyToDefault']);
            } else {
                $_SESSION["sess_vCurrency"]  = $_SESSION["sess_currency"] = $db_login[0]['vCurrencyPassenger'];
            }
            $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
            $_SESSION['sess_iUserId'] = $db_login[0]['iUserId'];
            $_SESSION["sess_vName"] = $db_login[0]['vName'];
            if($_SESSION["sess_lang"] != $lCode && $db_login[0]['vLang'] != $_SESSION["sess_lang"]){
                $update_sql1 = "UPDATE register_user set vLang = '".$_SESSION["sess_lang"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql1);
            } else if ($_COOKIE['ChangeLanguageToDefault'] == 'Yes'){
                $update_sql1 = "UPDATE register_user set vLang = '".$_SESSION["sess_lang"]."' WHERE iUserId='" .  $db_login[0]['iUserId'] . "'";
                $db_update = $obj->sql_query($update_sql1);
                setcookie("ChangeLanguageToDefault", "", 0, '/');
                unset($_COOKIE['ChangeLanguageToDefault']);
            } else {
                $_SESSION["sess_lang"] = $db_login[0]['vLang'];
            }
            
            $_SESSION["sess_vEmail"] = $db_login[0]['vEmail'];
            $_SESSION["sess_vPhone"] = $db_login[0]['vPhone'];
            $_SESSION["sess_user"] = "rider";
            $_SESSION["sess_ProfileName"] =  $db_login[0]['vName'].' '.$db_login[0]['vLastName'];
            $_SESSION["vAvgRating"] =  $db_login[0]['vAvgRating'];

            /*------------------for the image-----------------*/

            $tsite_upload_images_user_path = $tconfig["tsite_upload_images_passenger_path"] . '/'  . $db_login[0]['iUserId'] . '/';
            $tsite_upload_images_user = $tconfig["tsite_upload_images_passenger"] . '/'  . $db_login[0]['iUserId'] . '/';
            $_SESSION["sess_vImage"] = getProfileImage($db_login[0]['vImgName'], $tconfig["tsite_url"],$tsite_upload_images_user_path,$tsite_upload_images_user);
            /*------------------for the image-----------------*/
            if (SITE_TYPE == 'Demo') {
                $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $_SESSION["sess_iUserId"] . "', 'Passenger', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                $obj->sql_query($login_sql);
                $update_sql = "UPDATE register_user set tRegistrationDate='" . date('Y-m-d H:i:s') . "' WHERE iUserId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
            }

            if ($remember == "Yes") {
                setcookie("member_login_cookie", $vEmail, time() + 2592000);
                setcookie("member_password_cookie", $vPassword, time() + 2592000);
            }
            else {
                setcookie("member_login_cookie", "", time());
                setcookie("member_password_cookie", "", time());
            }
            $genUserData = $obj->MySQLSelect("SELECT tSessionId FROM $tblname WHERE iUserId = '" . $db_login[0]['iUserId'] . "' ");
            $tSessionId = $genUserData[0]['tSessionId'];
            if(empty($tSessionId)) {
                $tSessionId = session_id() . time();
                $obj->sql_query("UPDATE $tblname SET tSessionId = '$tSessionId' WHERE iUserId = '" . $db_login[0]['iUserId'] . "' ");
            }

            $obj->sql_query("UPDATE $tblname SET ePhoneVerified = 'Yes' WHERE iUserId = '" . $db_login[0]['iUserId'] . "' ");

            createUserLog('Passenger', 'Yes', $db_login[0]['iUserId'], 'Web');

        } else {
            $sql = "select eDirectionCode from language_master where vCode='" . $_SESSION["sess_lang"] . "'";
            $lang = $obj->MySQLSelect($sql);
            $eSystem = $db_login[0]['eSystem'];
            $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
            $_SESSION["sess_iUserId"] = $db_login[0]['iCompanyId'];
            $_SESSION["sess_ProfileName"] = $_SESSION["sess_vCompany"] = $db_login[0]['vCompany'];
            $_SESSION["sess_iCompanyId"] = $db_login[0]['iCompanyId'];
            $_SESSION["sess_vName"] = $db_login[0]['vName'];
            $_SESSION["sess_vLastName"] = $db_login[0]['vLastName'];
            $_SESSION["sess_vEmail"] = $db_login[0]['vEmail'];
            $_SESSION["sess_vPhone"] = $db_login[0]['vPhone'];
            $_SESSION["sess_eSystem"] = $eSystem;
            $_SESSION["sess_user"] = "company";

            /*------------------for the image-----------------*/
            $tsite_upload_images_compnay_path = $tconfig["tsite_upload_images_compnay_path"] . '/'  . $db_login[0]['iCompanyId'] . '/';
            $tsite_upload_images_compnay = $tconfig["tsite_upload_images_compnay"] . '/'  . $db_login[0]['iCompanyId'] . '/';
            $_SESSION["sess_vImage"] = getProfileImage($db_login[0]['vImage'], $tconfig["tsite_url"],$tsite_upload_images_compnay_path,$tsite_upload_images_compnay);
            /*------------------for the image-----------------*/
            if ($eSystem != 'DeliverAll'){
                if($_SESSION["sess_currency"] != $default_currency && $db_login[0]['vCurrencyCompany'] != $_SESSION["sess_currency"]){
                    $_SESSION["sess_vCurrency"] = $_SESSION['sess_currency'];
                    $update_sql = "UPDATE company set vCurrencyCompany='".$_SESSION["sess_currency"]."' WHERE iCompanyId='" .  $db_login[0]['iCompanyId'] . "'";
                    $db_update = $obj->sql_query($update_sql);
                } else if ($_COOKIE['ChangeCurrencyToDefault'] == 'Yes') {
                    $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
                    $update_sql = "UPDATE company set vCurrencyCompany='".$_SESSION["sess_currency"]."' WHERE iCompanyId='" .  $db_comp[0]['iCompanyId'] . "'";
                    $db_update = $obj->sql_query($update_sql);
                    setcookie("ChangeCurrencyToDefault", "", 0, '/');
                    unset($_COOKIE['ChangeCurrencyToDefault']);
                } else {
                    $_SESSION["sess_vCurrency"] = $_SESSION['sess_currency'] = $db_login[0]['vCurrencyCompany'];
                }
            } else {
                 $_SESSION["sess_vCurrency"] = $_SESSION['sess_currency'] = $db_login[0]['vCurrencyCompany'];
            }

            $ssqlsession = "";
            if (empty($tSessionId)) {
                $ssqlsession = ",tSessionId='" . session_id() . time() . "'";
            }

            if($_SESSION["sess_lang"] != $lCode && $db_login[0]['vLang'] != $_SESSION["sess_lang"]){
                $update_sql = "UPDATE company set vLang='" . $_SESSION["sess_lang"] . "' $ssqlsession WHERE iCompanyId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
            } else if ($_COOKIE['ChangeLanguageToDefault'] == 'Yes'){
                $update_sql = "UPDATE company set vLang='" . $_SESSION["sess_lang"] . "' $ssqlsession WHERE iCompanyId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
                setcookie("ChangeLanguageToDefault", "", 0, '/');
                unset($_COOKIE['ChangeLanguageToDefault']);
            } else {
                $_SESSION["sess_lang"] = $db_login[0]['vLang'];
            }


            if ($remember == "Yes") {
                setcookie("member_login_cookie", $email, time() + 2592000);
                setcookie("member_password_cookie", $pass, time() + 2592000);
            }
            else {
                setcookie("member_login_cookie", "", time());
                setcookie("member_password_cookie", "", time());
            }
            //User login log added by Rs start
            if ($eSystem == 'DeliverAll')
                createUserLog('Store', 'Yes', $db_login[0]['iCompanyId'], 'Web');
            else
                createUserLog('Company', 'Yes', $db_login[0]['iCompanyId'], 'Web');

            if(empty($tSessionId)) {
                $tSessionId = session_id() . time();
                $obj->sql_query("UPDATE $tblname SET tSessionId = '$tSessionId' WHERE $memberId = '" . $db_login[0]['iCompanyId'] . "' ");
            }

            $obj->sql_query("UPDATE $tblname SET ePhoneVerified = 'Yes' WHERE $memberId = '" . $db_login[0]['iCompanyId'] . "' ");

        }
        $json_data = array('login_status' => 2);
        echo json_encode($json_data);
        exit;
        
    } else {
        $json_data = array('login_status' => 3);
        echo json_encode($json_data);
        exit;
    }
}
exit;
?>