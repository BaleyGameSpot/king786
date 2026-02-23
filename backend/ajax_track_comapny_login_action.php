<?php

include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$user_type = isset($_POST['type_usr']) ? $_POST['type_usr'] : '';
$countryCode = $id = $eSystem = "";
$npass = encrypt($pass);
$remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';
$db_comp_org = array();
$userType = 'tracking_company';
$countryCode = isset($_POST['CountryCode']) ? $_POST['CountryCode'] : '';
if($_REQUEST['isEmailvEmail'] == 'Yes'){
}


$checkValid = checkMemberDataInfo($email, $pass, $userType, $countryCode,$id,$eSystem);

if ($checkValid['status'] == 1) {
    $db_comp_org = array();
    $db_comp_org[0] = $checkValid['USER_DATA'];

    if ($db_comp_org[0]['eStatus'] != 'Deleted') {
        $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
        $lang = $obj->MySQLSelect($sql);
  
        $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
        $_SESSION["sess_iUserId"] = $db_comp_org[0]['iTrackServiceCompanyId'];
        $_SESSION["sess_vCompany"] = $db_comp_org[0]['vCompany'];
        $_SESSION["sess_iTrackServiceCompanyId"] = $db_comp_org[0]['iTrackServiceCompanyId'];
        $_SESSION["sess_vEmail"] = $db_comp_org[0]['vEmail'];
        $_SESSION["sess_vPhone"] = $db_comp_org[0]['vPhone'];
        $_SESSION["sess_user"] = $user_type;
        createUserLog('TrackServiceCompany', 'Yes', $db_comp_org[0]['iTrackServiceCompanyId'], 'Web');

        $update_sql = "UPDATE track_service_company set vLang='".$_SESSION["sess_lang"]."' WHERE iTrackServiceCompanyId='" . $_SESSION["sess_iUserId"] . "'";
        $db_update = $obj->sql_query($update_sql);

        if ($remember == "Yes") {
            setcookie("member_login_cookie", $email, time() + 2592000);
            setcookie("member_password_cookie", $pass, time() + 2592000);
        } else {
            setcookie("member_login_cookie", "", time());
            setcookie("member_password_cookie", "", time());
        }

        $json_data = array('login_status' => 2);
        echo json_encode($json_data);
        //echo 2;
        exit;
    } else {
        $json_data = array('login_status' => 1);
        echo json_encode($json_data);
        //echo 1;
        exit;
    }
} elseif ($checkValid['status'] == 2) {
    $json_data = array('login_status' => 5);
    echo json_encode($json_data);
    exit;
} else {
    $json_data = array('login_status' => 3);
    echo json_encode($json_data);
    exit;
}
exit;
?>