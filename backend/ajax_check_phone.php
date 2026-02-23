<?php
include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$user_type = isset($_POST['type_usr']) ? $_POST['type_usr'] : '';
$phoneCode = isset($_POST['phoneCode']) ? $_POST['phoneCode'] : '';
$CountryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
$CompSystem = isset($_POST['CompSystem']) ? $_POST['CompSystem'] : '';
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}

$eSystem = $countryCode = $id = "";

if ($action == 'driver') {
    if (strtolower($user_type) == 'driver') {
        $userType = "driver";
        $countryData = get_value('country', 'vPhoneCode', 'vCountryCode', $CountryCode);
        $data = AllowphoneNumWithZero($user_type, $email, 'vPhone', 'register_driver', $countryData[0]['vPhoneCode']);

        $sql = "SELECT iDriverId,vCode,vCompany, iCompanyId, vName, vLastName, vEmail, vPhone,eStatus, vCurrencyDriver,vPassword,vLang FROM register_driver WHERE (vPhone = '" . $email . "' {$data} ) AND vCode='".$CountryCode."'";
        $db_driver = $obj->MySQLSelect($sql);
    }
    else {
        $userType = "company";
        $sql = "SELECT iCompanyId,vCode,vCompany, vName, vLang, vLastName, vEmail,vPhone, eStatus,vPassword,eSystem,tSessionId from company WHERE  (vPhone = '" . $email . "') AND vCode='".$CountryCode."' AND eSystem = '" . $CompSystem . "'";
        $db_comp = $obj->MySQLSelect($sql);
    }

    if (scount($db_driver) > 0) {
        if (scount($db_driver) == 1) {
            $db_driver = array();
            $db_driver[0] = $checkValid[0];
            if ($db_driver[0]['eStatus'] != 'Deleted') {
                $json_data = array('login_status' => 2);
                echo json_encode($json_data);
                exit;
            } else {
                $json_data = array('login_status' => 1);
                echo json_encode($json_data);
                exit;
            }
        } else {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
    } else {
        if (scount($db_comp) > 0) {

            $eSystem = $db_comp[0]['eSystem'];
            $tSessionId = $db_comp[0]['tSessionId'];
            $countryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';

            if (scount($db_comp) == 1) {
                $db_comp = array();
                $db_comp[0] = $checkValid['USER_DATA'];

                if ($db_comp[0]['eStatus'] != 'Deleted') {
                    $json_data = array('login_status' => 2, 'eSystem' => $eSystem);
                    echo json_encode($json_data);
                    exit;
                }
                else {
                    $json_data = array('login_status' => 1);
                    echo json_encode($json_data);
                    exit;
                }
            } else {
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
    $data = $obj->MySQLSelect("SELECT iUserId, vName, vEmail, eStatus, vCurrencyPassenger, vPhone,vPassword,vLang,vCountry FROM register_user WHERE vPhone = '".$email."' AND vPhoneCode='".$CountryCode."'");

    /* 04-09-2019 end */
    if (scount($data) == 1) {
        $db_login = array();
        $db_login[0] = $data[0];
        if ($db_login[0]['eStatus'] != "Deleted" && $db_login[0]['eStatus'] != "Inactive") {
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
    } else {
        $json_data = array('login_status' => 3);
        echo json_encode($json_data);
        exit;
    }

}
