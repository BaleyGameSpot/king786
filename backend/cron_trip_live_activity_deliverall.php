<?php
include_once ('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_trip_live_activity_deliverall_status.txt", "running");
/* Cron Log Update End */

$ssql = " AND ru.eDeviceType =  'Ios' ";
// $ssql = " AND o.iOrderId = '313'";
$orderData = $obj->MySQLSelect("SELECT o.iOrderId, ru.iAppVersion, ru.eDeviceType, ru.iGcmRegId, ru.eAppTerminate, ru.eDebugMode, ru.eHmsDevice FROM orders as o 
    LEFT JOIN register_user as ru ON ru.iUserId = o.iUserId
    WHERE iStatusCode IN (2,4,5,13,14) AND o.eBuyAnyService = 'No' $ssql ");


if(!empty($orderData) && scount($orderData) > 0) {
    $generalDataArr = array();
    foreach ($orderData as $order) {
        

        $message_arr = array();
        $message_arr['LiveActivityData'] = getOrderLiveActivity($order['iOrderId']);
        $message_arr['LiveActivity'] = "Yes";
        // echo "<pre>"; print_r($LiveActivityStep); exit;
        $iAppVersion = $order['iAppVersion'];
        $eDeviceType = $order['eDeviceType'];
        $iGcmRegId = $order['iGcmRegId'];
        $eAppTerminate = $order['eAppTerminate'];
        $eDebugMode = $order['eDebugMode'];
        $eHmsDevice = $order['eHmsDevice'];

        if(strtoupper($eDeviceType) == "IOS") {
            $tDeviceLiveActivityToken = getLiveActivityDeviceToken($order['iOrderId'], 'Order');
        } else {
            // $tDeviceLiveActivityToken = $iGcmRegId;
            continue;
        }        

        if(!empty($tDeviceLiveActivityToken)) {
            $generalDataArr[] = array(
                'eDeviceType'       => $eDeviceType,
                'deviceToken'       => $tDeviceLiveActivityToken,
                'alertMsg'          => "",
                'eAppTerminate'     => $eAppTerminate,
                'eDebugMode'        => $eDebugMode,
                'eHmsDevice'        => $eHmsDevice,
                'message'           => $message_arr
            );
        }
        
        
    }
    // echo "<pre>"; print_r($generalDataArr); exit;
    $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_USER);
}

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_trip_live_activity_deliverall_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true); 

foreach ($cron_logs as $ckey => $cfile) 
{
    if($cfile['filename'] == "cron_trip_live_activity_deliverall.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
