<?php
include_once('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_schedule_bidding_status.txt", "running");
/* Cron Log Update End */
/*$date_ = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")));
$serverTimeZone = date_default_timezone_get();
$TimeZoneOffset = converToTz($date_, $serverTimeZone, $serverTimeZone, "P");*/


$TimeZoneOffset = date("P", strtotime(date("Y-m-d H:i:s")));

$sql = "SELECT ADDTIME((CONVERT_TZ(NOW(), 'SYSTEM', '".$TimeZoneOffset."')), '3600') as dBidding_Date , bp.iBiddingPostId , bp.vBiddingPostNo,bp.dBiddingDate,ua.vServiceAddress,

         rd.vTimeZone as driverVTimeZone , rd.vLang as driverVLang , rd.vEmail as driverVEmail ,rd.vName as driverName ,rd.vLastName as driverLastName,rd.vPhone as drivervPhone,rd.vCode as drivervCode,

         ru.vName as userName,ru.vLastName as userLastName, ru.vLang as uservLang FROM `bidding_post` as bp 

         JOIN register_driver as rd ON (rd.iDriverId = bp.iDriverId)

         JOIN register_user as ru ON (ru.iUserId = bp.iUserId )

         JOIN user_address as ua ON (ua.iUserAddressId = bp.iAddressId )

         WHERE bp.cronCount = 0 AND bp.eStatus = 'Accepted' AND bp.vTaskStatus ='Pending'  
         AND bp.dBiddingDate > (CONVERT_TZ(NOW(), 'SYSTEM', '" . $TimeZoneOffset . "'))
         
         HAVING dBidding_Date >= bp.dBiddingDate  ";
$bidding_post = $obj->MySQLSelect($sql);

$cron_logs_id['inQuery'] = [];
$cron_logs_id['sendnoti'] = [];
foreach ($bidding_post as $post) {

    $cron_logs_id['inQuery'][] = $post['iBiddingPostId'];

    $driverFullName = $post['driverName'] . " " . $post['driverLastName'];
    $userFullName = $post['userName'] . " " . $post['userLastName'];
    $Data_Mail['driverName'] = $driverFullName;
    $Data_Mail['userFullName'] = $userFullName;
    $Data_Mail['PostNo'] = $post['vBiddingPostNo'];



    /*------------------Email DateTime Format-----------------*/
    $dBiddingDateArr = commonDateFormat($post['dBiddingDate'],$post['driverVTimeZone'],$post['driverVLang'],1,'No');
    /*------------------Email DateTime Format-----------------*/

    $Data_Mail['Ddate'] = $dBiddingDateArr['DATE_TIME'];
    $Data_Mail['SourceAddress'] = $post['vServiceAddress'];
    $Data_Mail['vEmail'] = $post['driverVEmail'];
    $Data_Mail['EMAIL_NAME'] = $driverFullName;
    $sendemail = $COMM_MEDIA_OBJ->SendMailToMember("DRIVER_NOTIFY_BID_TASK", $Data_Mail);


    /*$BOOKING_DATE = date("Y-m-d", strtotime($post['dBiddingDate']));
    $BOOKING_TIME = date("H:i", strtotime($post['dBiddingDate']));*/


    $Data_SMS['PASSENGER_NAME'] = $userFullName;
    $Data_SMS['BOOKING_DATE'] = $dBiddingDateArr['DATE'];
    $Data_SMS['BOOKING_TIME'] = $dBiddingDateArr['TIME'];
    $Data_SMS['TASK_NO'] = $post['vBiddingPostNo'];
    $passangervPhone = $post['drivervPhone'];
    $passangerPhoneCode = $post['drivervCode'];

    $sms_message_layout = $COMM_MEDIA_OBJ->GetSMSTemplate("DRIVER_NOTIFY_BID_TASK", $Data_SMS, "", $post['uservLang']);
    $result_sms = $COMM_MEDIA_OBJ->SendSystemSMS($passangervPhone, $passangerPhoneCode, $sms_message_layout);

    if($sendemail == 1 || $result_sms == 1) {
        $cron_logs_id['sendnoti'][] = $post['iBiddingPostId'];
        $iBiddingPostId = $post['iBiddingPostId'];
        $Data_update['cronCount'] = 1;
        $where = " iBiddingPostId = '$iBiddingPostId'";
        $obj->MySQLQueryPerform('bidding_post', $Data_update, 'update', $where);
    }
}


WriteToFile($tconfig['tsite_script_file_path'] . "cron_schedule_bidding.txt", json_encode($cron_logs_id));
?>