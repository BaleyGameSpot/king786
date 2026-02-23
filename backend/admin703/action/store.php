<?php



include_once('../../common.php');



$date = Date('Y-m-d');

$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';



$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?', $reload);

$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';

$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start make deleted

$adminUrl = $tconfig["tsite_url_main_admin"];



function NotifyStoreAboutActivation($iCompanyId)

{

    global $langage_lbl_admin, $EVENT_MSG_OBJ, $COMM_MEDIA_OBJ, $obj;

    $row1 = $obj->MySQLSelect("SELECT vEmail, vCode , vPhone, eHmsDevice , iCompanyId, vCurrencyCompany, vLang, eDeviceType, iGcmRegId, eAppTerminate, eDebugMode, vCompany FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'");

    $NOTI_TEMPLATE = "";

    $alertMsg = $langage_lbl_admin["LBL_ADMIN_ACTIVE_STORE_ACCOUNT"];

    $alertMsg =str_replace("#NAME#", $row1[0]['vCompany'],  $alertMsg);

    $generalDataArr = $final_message = array();

    $message_arr = array();

    $message_arr['Message'] = $message_arr['MsgType'] = 'ActivateStore';

    $message_arr['vTitle'] = $alertMsg;

    $message_arr['uString'] = time();

    $generalDataArr[] = array('eDeviceType'   => $row1[0]['eDeviceType'],

                              'deviceToken'   => $row1[0]['iGcmRegId'],

                              'alertMsg'      => $alertMsg,

                              'eAppTerminate' => $row1[0]['eAppTerminate'],

                              'eDebugMode'    => $row1[0]['eDebugMode'],

                              'message'       => $message_arr,

                              'MsgType'       => 'ActivateStore',

                              'eHmsDevice' => $row1[0]['eHmsDevice']);

    $arr['NOTIFICATION'] = $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_COMPANY);



    $MAIL_TEMPLATE = 'ADMIN_ACTIVE_DRIVER_ACCOUNT';

    $mailArr = [];

    $mailArr['NAME'] = $row1[0]['vCompany'];

    $mailArr['vEmail'] = $row1[0]['vEmail'];

    $arr['MAIL'] = $COMM_MEDIA_OBJ->SendMailToMember($MAIL_TEMPLATE, $mailArr);

    return $arr;



}







if ($statusVal != '' && ($method == "eAutoaccept" || $method == "eAvailable")) {

    //echo "UPDATE company SET $method = '" . $statusVal . "' WHERE iCompanyId IN (" . $iCompanyId . ")";die;    

    $obj->sql_query("UPDATE company SET $method = '" . $statusVal . "' WHERE iCompanyId IN (" . $iCompanyId . ")");

    if ($iCompanyId > 0) {

        $successtype = "1";

        $successMsg = $langage_lbl_admin["LBL_DISABLE_AUTO_ACCEPT_ORDER_TXT"];

        if ($statusVal == "Yes") {

            $successtype = "1";

            $successMsg = $langage_lbl_admin["LBL_AUTO_ACCEPT_ORDER_TXT"];

        }

        if($method == "eAvailable"){

            $successtype = "1";

            $successMsg = $langage_lbl_admin["LBL_INFO_UPDATED_TXT"];

        }

        $_SESSION['success'] = $successtype;

        $_SESSION['var_msg'] = $successMsg;

    } else {

        $_SESSION['success'] = '2';

        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_ERROR_OCCURED"];

    }

    $data['status'] = "1";

    echo json_encode($data);

    die;

}

if (($statusVal == 'Deleted' || $method == 'delete') && ($iCompanyId != '' || $checkbox != "")) {

    if (!$userObj->hasPermission('delete-store')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to delete ' . strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);

    } else {

        //Added By Hasmukh On 05-10-2018 For Solved Bug Start

        if ($iCompanyId != "") {

            $storeIds = $iCompanyId;

        } else {

            $storeIds = $checkbox;

        }

        //Added By Hasmukh On 05-10-2018 For Solved Bug End

        if (SITE_TYPE != 'Demo') {

            $qur2 = "UPDATE company SET eStatus = 'Deleted'  , vPhone = concat(vPhone, '(Deleted)')  WHERE iCompanyId IN (" . $storeIds . ")";

            $res2 = $obj->sql_query($qur2);



            $storeIds = explode(",", $storeIds);

            for ($i = 0; $i < scount($storeIds); $i++) {



                /* Insert status log on user_log table*/

                $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$storeIds[$i].", eUserType = 'store', dDate = '".$date."', eStatus = 'Deleted', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";

                $obj->sql_query($queryIn);

            }



            $_SESSION['success'] = '1';

            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];

        } else {

            $_SESSION['success'] = '2';

        }

    }

    header("Location:" . $adminUrl . "store.php?" . $parameters);

    exit;

}

//End make deleted

//Start Change single Status

if ($iCompanyId != '' && $status != '') {

    if (!$userObj->hasPermission('update-status-store')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to change status of ' . strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);

    } else {

        if (SITE_TYPE != 'Demo') {



            /*--------------------- store deleted duplicate check --------------------*/

            $checkUserDeleted = $obj->MySQLSelect("SELECT vPhone FROM company WHERE eStatus = 'Deleted' AND iCompanyId='" . $iCompanyId . "'");



            if(!empty($checkUserDeleted)){

                $mobile = clearPhone($checkUserDeleted[0]['vPhone']);

                $checkUserDeleted = $obj->MySQLSelect("SELECT vPhone FROM company WHERE eStatus != 'Deleted' AND vPhone='" . $mobile . "' AND iCompanyId !='" . $iCompanyId . "' ");



                if(!empty($checkUserDeleted)){

                    $_SESSION['success'] = 2;

                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_ADMIN_NOT_ABLE_ACTIVE_TEXT'];

                    header("Location:" . $tconfig["tsite_url_main_admin"] . "store.php?" . $parameters);

                    exit;

                }else{



                    $query = "UPDATE company SET vPhone = '" . $mobile . "' WHERE iCompanyId = '" . $iCompanyId . "'";

                    $checkUserDeleted = $obj->sql_query($query);

                }



            }



            /*--------------------- store deleted duplicate check --------------------*/





            $acceptSql = '';

            if($status!='Active') {

                $acceptSql = " ,eAutoaccept = 'No', eAvailable = 'No'";

            }

            $query = "UPDATE company SET eStatus = '" . $status . "' $acceptSql WHERE iCompanyId = '" . $iCompanyId . "'";

            $obj->sql_query($query);



            /* Insert status log on user_log table*/

            $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$iCompanyId.", eUserType = 'store', dDate = '".$date."', eStatus = '".$status."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";

            $obj->sql_query($queryIn);





            $_SESSION['success'] = '1';

            if ($status == 'Active') {

                NotifyStoreAboutActivation($iCompanyId);

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];

            } else {

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];

            }

        } else {

            $_SESSION['success'] = 2;

        }

    }

    header("Location:" . $adminUrl . "store.php?" . $parameters);

    exit;

}

//End Change single Status

//Start Change All Selected Status

if ($checkbox != "" && $statusVal != "") {

    if (!$userObj->hasPermission('update-status-store')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to change status of ' . strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);

    } else {

        if (SITE_TYPE != 'Demo') {

            $query = "UPDATE company SET eStatus = '" . $statusVal . "' WHERE iCompanyId IN (" . $checkbox . ")";

            $obj->sql_query($query);



            $checkbox = explode(",", $checkbox);

            for ($i = 0; $i < scount($checkbox); $i++) {



                /* Insert status log on user_log table*/

                $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$checkbox[$i].", eUserType = 'store', dDate = '".$date."', eStatus = '".$statusVal."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";

                $obj->sql_query($queryIn);



                if($statusVal == 'Active'){

                    NotifyStoreAboutActivation($checkbox[$i]);

                }

            }

            

            

            $_SESSION['success'] = '1';

            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

        } else {

            $_SESSION['success'] = 2;

        }

    }

    header("Location:" . $adminUrl . "store.php?" . $parameters);

    exit;

}

//End Change All Selected Status

?>

