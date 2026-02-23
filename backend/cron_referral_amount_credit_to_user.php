<?php
include_once('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_referral_amount_credit_to_user_status.txt", "running");
/* Cron Log Update End */

$sql = "SELECT * FROM `wallet_money_referrer_email` WHERE  eSent = 'No' AND JSON_UNQUOTE(JSON_VALUE(tMailInfo,'$.vEmail')) != '' ORDER BY `iEmailId` DESC";
$data = $obj->MySQLSelect($sql);

for ($i = 0; $i < scount($data); $i++) {

    $tMailInfo = json_decode($data[$i]['tMailInfo'] , true);

    $maildatadeliverd['vEmail'] = $tMailInfo['vEmail'];
    $maildatadeliverd['UserName'] = $tMailInfo['username'];
    $maildatadeliverd['EMAIL_NAME'] = $tMailInfo['username'];
    $maildatadeliverd['TripUserName'] = $tMailInfo['tripusername'];
    $maildatadeliverd['CompanyName'] = $COMPANY_NAME;
    $maildatadeliverd['amount'] = $tMailInfo['amount'];

    $mailResponse = $COMM_MEDIA_OBJ->SendMailToMember("REFERRAL_AMOUNT_CREDIT_TO_USER", $maildatadeliverd);
    if($mailResponse){
        $where = " iEmailId = '" . $data[$i]['iEmailId'] . "'";
        $Data_Update['eSent'] = "Yes";
        $id = $obj->MySQLQueryPerform("wallet_money_referrer_email", $Data_Update, 'update', $where);
    }
}

/* Cron Log Update */

WriteToFile($tconfig['tsite_script_file_path'] . "cron_referral_amount_credit_to_user_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true);

foreach ($cron_logs as $ckey => $cfile)
{
    if($cfile['filename'] == "cron_referral_amount_credit_to_user.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */

?>