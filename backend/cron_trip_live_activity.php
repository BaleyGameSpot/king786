<?php
include_once ('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_trip_live_activity_status.txt", "running");
/* Cron Log Update End */

$ssql = " AND ru.eDeviceType =  'Ios' ";
// $ssql = " AND (tr.iTripId = '686')";
$tripData = $obj->MySQLSelect("SELECT tr.iTripId, tr.iUserId, ru.iAppVersion, ru.eDeviceType, ru.iGcmRegId, ru.eAppTerminate, ru.eDebugMode, ru.eHmsDevice FROM trips as tr 
    LEFT JOIN register_user as ru ON ru.iUserId = tr.iUserId  
    WHERE tr.iActive IN ('Active', 'Arrived', 'On Going Trip') AND tr.eSystem = 'General' $ssql ");

if(!empty($tripData) && scount($tripData) > 0) {
    $generalDataArr = array();
    foreach ($tripData as $trip) {
        $message_arr = array();
        // $LiveActivityStep = "3";
        $message_arr['LiveActivityData'] = getTripLiveActivity($trip['iTripId']);
        $message_arr['LiveActivity'] = "Yes";
        // echo "<pre>"; print_r($LiveActivityStep); exit;
        $iAppVersion = $trip['iAppVersion'];
        $eDeviceType = $trip['eDeviceType'];
        $iGcmRegId = $trip['iGcmRegId'];
        $eAppTerminate = $trip['eAppTerminate'];
        $eDebugMode = $trip['eDebugMode'];
        $eHmsDevice = $trip['eHmsDevice'];
        if(strtoupper($eDeviceType) != "IOS"){
            continue;
        }
        if(strtoupper($eDeviceType) == "IOS") {
            $tDeviceLiveActivityToken = getLiveActivityDeviceToken($trip['iTripId'], 'Trip');    
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
        
        // echo "<pre>"; print_r($generalDataArr); exit;
        
    }

    $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_USER);
}

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_trip_live_activity_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true); 

foreach ($cron_logs as $ckey => $cfile) 
{
    if($cfile['filename'] == "cron_trip_live_activity.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
