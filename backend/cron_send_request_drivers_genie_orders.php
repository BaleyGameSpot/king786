<?php
include_once ('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_send_request_drivers_genie_orders_status.txt", "running");
/* Cron Log Update End */

$currentdate = date("Y-m-d H:i:s");

$sql = "SELECT iOrderId,iDriverId,tOrderRequestDate,iStatusCode,eBuyAnyService, ADDTIME(tOrderRequestDate, '2000') as tOrderRequestDateEnd,CURRENT_TIMESTAMP() FROM orders WHERE iDriverId = '0' AND eBuyAnyService = 'Yes' AND iStatusCode NOT IN (7,8,11) HAVING tOrderRequestDateEnd > '$currentdate' ORDER BY tOrderRequestDate";
$all_orders = $obj->MySQLSelect($sql);

$all_orders_new = array();
foreach ($all_orders as $k => $order) {
    if($order['iDriverId'] == 0)
    {
        $all_orders_new[] = $all_orders[$k];
    }
}

$_REQUEST['eSystem'] = "DeliverAll";
foreach ($all_orders_new as $newOrder) {
    $iOrderId = $newOrder['iOrderId'];
    sendAutoRequestToDriver($iOrderId, "", "Yes");
}

// echo "<pre>"; print_r($all_orders_new); exit;
$sql = "SELECT iOrderId,vOrderNo,iUserId,fWalletDebit, iStatusCode,ePaymentOption,iDriverId,tOrderRequestDate, ADDTIME(tOrderRequestDate, '2000') as tOrderRequestDateEnd,CURRENT_TIMESTAMP() FROM orders WHERE iStatusCode IN (1,2) AND eBuyAnyService = 'Yes' AND iDriverId = '0' HAVING tOrderRequestDateEnd < '$currentdate'";

$pending_orders = $obj->MySQLSelect($sql);
// echo "<pre>"; print_r($pending_orders); exit;
$default_lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$languageLabelsArrDefault = $LANG_OBJ->FetchLanguageLabels($default_lang, "1", $iServiceId);

foreach ($pending_orders as $OrderData) {
    $iOrderId = $OrderData['iOrderId'];
    $where = " iOrderId = '$iOrderId'";
    $data['iStatusCode'] = '8';
    $data['eCancelledBy'] = "Admin";
    $data['vCancelReason'] = $languageLabelsArr['LBL_GENIE_CANCELLED_ADMIN'];
    $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
    createOrderLog($iOrderId, "8");

    ### Refund Wallet Amount Into User Wallet ##
    $fWalletDebit = $OrderData['fWalletDebit'];
    if ($fWalletDebit > 0) {
        $iUserId = $OrderData['iUserId'];
        $iBalance = $fWalletDebit;
        $vOrderNo = $OrderData['vOrderNo'];
        $eFor = 'Deposit';
        $eType = 'Credit';
        $tDescription = "#LBL_CREDITED_BOOKING_DL#" . $vOrderNo;
        $ePaymentStatus = 'Unsettelled';
        $dDate = Date('Y-m-d H:i:s');
        $eUserType = 'Rider';
        $WALLET_OBJ->PerformWalletTransaction($iUserId, $eUserType, $iBalance, $eType, $iOrderId, $eFor, $tDescription, $ePaymentStatus, $dDate);
    }
    ### Refund Wallet Amount Into User Wallet ##

    $user_data_order = $obj->MySQLSelect("SELECT iUserId,tSessionId,iAppVersion,eDeviceType,iGcmRegId,vLang,eAppTerminate,eDebugMode,eHmsDevice FROM register_user WHERE iUserId = " . $OrderData['iUserId']);

    $vLang = $user_data_order[0]['vLang'];

    if($vLang == "" || $vLang == NULL)
    {
        $vLang = $LANG_OBJ->FetchDefaultLangData("vCode");
    }

    $languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);

    ## Send Notification To User ## 
    $MessageUser = "OrderCancelByAdmin";
    $alertMsgUser = $languageLabelsArr['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $OrderData['vOrderNo'] . " " . $languageLabelsArr['LBL_REASON_TXT'] . " " . $languageLabelsArr['LBL_CANCELLED_BY_ADMIN'];
    $message_arrUser = array();
    $message_arrUser['Message'] = $MessageUser;
    $message_arrUser['iOrderId'] = $OrderData['iOrderId'];
    $message_arrUser['vOrderNo'] = $OrderData['vOrderNo'];
    $message_arrUser['vTitle'] = $alertMsgUser;
    $message_arrUser['tSessionId'] = $user_data_order[0]['tSessionId'];
    $message_arrUser['eSystem'] = 'DeliverAll';

    $message_arrUser['CustomNotification'] = $MODULES_OBJ->isEnableCustomNotification() ? "Yes" : "No";
    $message_arrUser['CustomViewBtn'] = "No";
    $message_arrUser['CustomTrackDetails'] = "No";
    $customNotiArray = GetCustomNotificationDetails($OrderData['iOrderId'],$message_arrUser,$vLang);

    $message_arrUser['CustomTitle'] = $customNotiArray[0]['vCurrentStatus'];
    $message_arrUser['CustomSubTitle'] = $customNotiArray[0]['vCurrentStatus_Track'];
    $message_arrUser['CustomMessage'] = $customNotiArray;

    $iAppVersionUser = $user_data_order[0]['iAppVersion'];
    $eDeviceTypeUser = $user_data_order[0]['eDeviceType'];
    $iGcmRegIdUser = $user_data_order[0]['iGcmRegId'];
    $iUserIdNew = $user_data_order[0]['iUserId'];
    $eAppTerminate = $user_data_order[0]['iUserId'];
    $eDebugMode = $user_data_order[0]['eDebugMode'];
    $eHmsDevice = $user_data_order[0]['eHmsDevice'];


    $channelNameUser = "PASSENGER_" . $iUserIdNew;

    $generalDataArr = array();
    $generalDataArr[] = array(
        'eDeviceType'       => $eDeviceTypeUser,
        'deviceToken'       => $iGcmRegIdUser,
        'alertMsg'          => $alertMsgUser,
        'eAppTerminate'     => $eAppTerminate,
        'eDebugMode'        => $eDebugMode,
        'eHmsDevice'        => $eHmsDevice,
        'message'           => $message_arrUser,
        'channelName'       => $channelNameUser,
    );

    // $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_USER);
    $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr, 'requestSent' => 'No'), RN_USER);
}


/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_send_request_drivers_genie_orders_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true); 

foreach ($cron_logs as $ckey => $cfile) 
{
    if($cfile['filename'] == "cron_send_request_drivers_genie_orders.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
