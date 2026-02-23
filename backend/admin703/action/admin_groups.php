<?php



include_once('../../common.php');



$reload = $_SERVER['REQUEST_URI'];



$urlparts = explode('?', $reload);

$parameters = $urlparts[1];



$iGroupId = isset($_REQUEST['iGroupId']) ? $_REQUEST['iGroupId'] : '';

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';

$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';



//Start make deleted

$messageCheck = "Please remove active record associated with this user group from administrator module.";

if ($method == 'delete' && $iGroupId != '') {

    if (!$userObj->hasRole(1) || $iGroupId <= 4) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permissions to deleted record.';

    } else {

        $checkUserGroup = checkUserGroup($iGroupId);

        $checkUserGroup = trim($checkUserGroup);

        if (!empty($checkUserGroup)) {

            $_SESSION['success'] = 3;

            $_SESSION['var_msg'] = $messageCheck;

        } else {

            if (SITE_TYPE != 'Demo') {

                $query = "UPDATE admin_groups SET eStatus = 'Deleted' WHERE iGroupId = '" . $iGroupId . "'";



                $obj->sql_query($query);

                $_SESSION['success'] = '1';

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];

            } else {

                $_SESSION['success'] = '2';

            }

        }

    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "admin_groups.php?" . $parameters);

    exit;

}

//End make deleted

//Start Change single Status

if ($iGroupId != '' && $status != '') {

    if (!$userObj->hasRole(1) || $iGroupId <= 4) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permissions to change status of record.';

    } else {

        $checkUserGroup = checkUserGroup($iGroupId);

        $checkUserGroup = trim($checkUserGroup);

        if (!empty($checkUserGroup) && ($status == "Deleted" || $status == "Inactive")) {

            $_SESSION['success'] = 3;

            $_SESSION['var_msg'] = $messageCheck;

        } else {

            if (SITE_TYPE != 'Demo') {

                $query = "UPDATE admin_groups SET eStatus = '" . $status . "' WHERE iGroupId = '" . $iGroupId . "'";

                $obj->sql_query($query);

                $_SESSION['success'] = '1';

                if ($status == 'Active') {

                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];

                } else {

                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];

                }

            } else {

                $_SESSION['success'] = 2;

            }

        }

    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "admin_groups.php?" . $parameters);

    exit;

}

//End Change single Status

//Start Change All Selected Status

if ($checkbox != "" && $statusVal != "") {

    //Added By Hasmukh On 04-10-2018 For Solved Bug Start

    $explodeGrpId = explode(",", $checkbox);

    $flagArr = array();

    for ($r = 1; $r < 5; $r++) {

        if (in_array($r, $explodeGrpId)) {

            $flagArr[] = 1;

        }

    }

    //Added By Hasmukh On 04-10-2018 For Solved Bug End

    if (!$userObj->hasRole(1) || in_array(1, $flagArr)) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permissions to change status of record.';

    } else {
        if(isset($iGroupId) && !empty($iGroupId)){
            $checkUserGroup = checkUserGroup($iGroupId);

            $checkUserGroup = trim($checkUserGroup);

            if (!empty($checkUserGroup) && ($statusVal == "Deleted" || $statusVal == "Inactive")) {

                $_SESSION['success'] = 3;

                $_SESSION['var_msg'] = $messageCheck;

            }

        } else {

            if (SITE_TYPE != 'Demo') {

                $query = "UPDATE admin_groups SET eStatus = '" . $statusVal . "' WHERE iGroupId IN (" . $checkbox . ")";

                $obj->sql_query($query);

                $_SESSION['success'] = '1';

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

            } else {

                $_SESSION['success'] = 2;

            }

        }

    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "admin_groups.php?" . $parameters);

    exit;

}



//End Change All Selected Status

//if ($iGroupId != '' && $status != '') {

//    if (SITE_TYPE != 'Demo') {

//        $query = "UPDATE admin_groups SET eStatus = '" . $status . "' WHERE iGroupId = '" . $iGroupId . "'";

//        $obj->sql_query($query);

//        $_SESSION['success'] = '1';

//        $_SESSION['var_msg'] = "Admin " . $status . " Successfully.";

//        header("Location:".$tconfig["tsite_url_main_admin"]."admin_groups.php?".$parameters);

//        exit;

//    } else {

//        $_SESSION['success']=2;

//        header("Location:".$tconfig["tsite_url_main_admin"]."admin_groups.php?".$parameters);

//        exit;

//    }

//}

function checkUserGroup($groupId) {

    global $obj;

    

    //echo "SELECT * FROM administrators WHERE iGroupId='".$groupId."'";die;

    $getUserList = $obj->MySQLSelect("SELECT vFirstName,vLastName FROM administrators WHERE iGroupId IN($groupId) AND eStatus != 'Deleted'");

    //print_R($getUserList);die;

    $userName = $userNames = "";

    if (scount($getUserList) > 0) {

        for ($r = 0; $r < scount($getUserList); $r++) {

            $userName .= "," . $getUserList[$r]['vFirstName']." ".$getUserList[$r]['vLastName'];

        }

    }

    if ($userName != "") {

        $userNames = trim($userName, ',');

    }

    return $userNames;

}



?>