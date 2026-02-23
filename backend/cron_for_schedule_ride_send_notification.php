<?php
include_once('common.php');

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_for_schedule_ride_send_notification.txt", "running");
/* Cron Log Update End */
/*------------------language_label-----------------*/
$sql1 = "SELECT * FROM `language_label` WHERE vLabel IN ('LBL_RIDE_LATER_SEND_NOTI_TO_DRIVER_TEXT' , 'LBL_RIDE_LATER_SEND_NOTI_TO_USER_TEXT')";
$language_label = $obj->MySQLSelect($sql1);
$LANGUAGE_LABEL_ARR = [];


foreach($language_label as $Arr){

    $LANGUAGE_LABEL_ARR[$Arr['vLabel']][$Arr['vCode']] = $Arr['vValue'];

}

$Default_Language_Code = $LANG_OBJ->FetchDefaultLangData("vCode");
$systemTimeZone = date_default_timezone_get();
/*------------------language_label-----------------*/



$SEND_NOTIFICATION_BEFORE_RIDE_START = ($RIDE_LATER_SEND_NOTIFICATION_BEFORE_RIDE_START * 60);

$CurrentDate = date('Y-m-d H:i:s');
$MinusDate = date("Y-m-d H:i:s", strtotime($CurrentDate) + $SEND_NOTIFICATION_BEFORE_RIDE_START);

$sql1 = "SELECT 

        ru.iGcmRegId as User_iGcmRegId,ru.eDeviceType as User_eDeviceType,ru.iUserId as User_iUserId,ru.tSessionId as User_tSessionId,ru.iAppVersion as User_iAppVersion,
        ru.eAppTerminate as User_eAppTerminate ,ru.eDebugMode as User_eDebugMode ,ru.eHmsDevice as User_eHmsDevice,ru.vCurrencyPassenger as User_vCurrencyPassenger,
        ru.vEmail as User_vEmail,ru.vPhone as User_vPhone ,ru.vPhoneCode as User_vPhoneCode,ru.vLang as User_vLang,ru.vTimeZone as User_vTimeZone,
        
        rd.vLang as Driver_vLang ,rd.iGcmRegId as Driver_iGcmRegId,rd.eDeviceType as Driver_eDeviceType,rd.iDriverId as Driver_iDriverId ,
        rd.tSessionId as Driver_tSessionId,rd.iAppVersion as Driver_iAppVersion,rd.eAppTerminate as Driver_eAppTerminate ,rd.eDebugMode as Driver_eDebugMode,
        rd.eHmsDevice as Driver_eHmsDevice ,rd.vCurrencyDriver as Driver_vCurrencyDriver,rd.vTimeZone as Driver_vTimeZone,
        cb.iDriverId,cb.vBookingNo,cb.dBooking_date,cb.iCabBookingId 

        FROM cab_booking cb

        JOIN register_driver rd ON (cb.iDriverId = rd.iDriverId)
        JOIN register_user ru ON (cb.iUserId = ru.iUserId)
        WHERE cb.eStatus='Accepted' 
        
        AND cb.iReminderSend = 0 AND cb.dBooking_date < '".$MinusDate."' AND cb.eAutoAssign = 'Yes' AND cb.eAssigned='Yes'";
$cab_booking_data = $obj->MySQLSelect($sql1);

if(isset($cab_booking_data) && !empty($cab_booking_data)) {


    foreach ($cab_booking_data as $data)
    {

        $dBooking_date = $data['dBooking_date'];

        $date2 = strtotime($dBooking_date);
        $date1 = strtotime($CurrentDate);

        $minutesDifference = round(($date2 - $date1) / 60);

        //"LBL_RIDE_LATER_SEND_NOTI_TO_DRIVER_TEXT"
        //"LBL_RIDE_LATER_SEND_NOTI_TO_USER_TEXT"

        /*------------------User-----------------*/


        $User_vLang = $data['User_vLang'] ?? $Default_Language_Code;
        $User_vTimeZone = $data['User_vTimeZone'] ?? $systemTimeZone;
        //$User_vTimeZone = "Europe/London";

        if (!empty($User_vTimeZone)) {
            $dBooking_date = converToTz($dBooking_date, $User_vTimeZone, $systemTimeZone);
        }
        $dBooking_date = DateTime($dBooking_date, 25);
        $message_layout_user = $LANGUAGE_LABEL_ARR['LBL_RIDE_LATER_SEND_NOTI_TO_USER_TEXT'][$User_vLang];


        $alertMsg_db =  str_replace(['#BOOKING_NO#' , '#TIME#'],['#'.$data['vBookingNo'] ,$dBooking_date ],$message_layout_user);
        $NOTI_TEMPLATE = 'rideLaterBookingRequestAccept';
        $final_message['Message'] = $NOTI_TEMPLATE;
        $final_message['MsgType'] = $NOTI_TEMPLATE;
        $final_message['time'] = time();
        $final_message['eType'] = "Ride";
        $final_message['vTitle'] = $alertMsg_db;
        $generalDataArr_user[] = array(
            'eDeviceType' => $data['User_eDeviceType'],
            'deviceToken' => $data['User_iGcmRegId'],
            'alertMsg' => $alertMsg_db,
            'eAppTerminate' => $data['User_eAppTerminate'],
            'eDebugMode' => $data['User_eDebugMode'],
            'message' => $final_message,
            'eHmsDevice' => $data['User_eHmsDevice'],
            'channelName' => "PASSENGER_" . $data['User_iUserId']
        );
        /*------------------User-----------------*/
        /*------------------Driver-----------------*/
        $Driver_vLang = $data['Driver_vLang'] ?? $Default_Language_Code;
        $message_layout_Driver = $LANGUAGE_LABEL_ARR['LBL_RIDE_LATER_SEND_NOTI_TO_DRIVER_TEXT'][$Driver_vLang];

        $time = convertMinToHoursToDays(round($minutesDifference), 'Minutes', 1);

        $alertMsg_db =  str_replace(['#BOOKING_NO#' , '#TIME#'],['#'.$data['vBookingNo'] ,$time ],$message_layout_Driver);

        $NOTI_TEMPLATE = 'rideLaterBookingRequestAccept';
        $final_message['Message'] = $NOTI_TEMPLATE;
        $final_message['MsgType'] = $NOTI_TEMPLATE;
        $final_message['time'] = time();
        $final_message['eType'] = "Ride";
        $final_message['vTitle'] = $alertMsg_db;
        $generalDataArr_Driver[] = array(
            'eDeviceType' => $data['Driver_eDeviceType'],
            'deviceToken' => $data['Driver_iGcmRegId'],
            'alertMsg' => $alertMsg_db,
            'eAppTerminate' => $data['Driver_eAppTerminate'],
            'eDebugMode' => $data['Driver_eDebugMode'],
            'message' => $final_message,
            'eHmsDevice' => $data['Driver_eHmsDevice'],
            'channelName' => "DRIVER_" . $data['Driver_iDriverId']
        );
        /*------------------Driver-----------------*/

        $CAB_BOOKING_UPDATE[] = $data['iCabBookingId'];
    }

    $dataUser =  $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr_user), RN_USER);
    $dataDriver =  $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr_Driver), RN_PROVIDER);

    $Id = implode(',' , $CAB_BOOKING_UPDATE);
    $data_update = [];
    $where = " iCabBookingId IN ($Id) ";
    $data_update['iReminderSend'] = 1;
    $obj->MySQLQueryPerform("cab_booking", $data_update, 'update', $where);



}


/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_for_schedule_ride_send_notification.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true);

foreach ($cron_logs as $ckey => $cfile)
{
    if($cfile['filename'] == "cron_schedule_ride_new.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}
WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
?>