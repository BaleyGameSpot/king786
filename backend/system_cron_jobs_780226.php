<?php
include 'common.php';

$script_file_name = basename($_SERVER["SCRIPT_NAME"]);
$session_val = GenerateCronJobSession($script_file_name);

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_jobs_last_executed.txt", date('Y-m-d H:i:s'));

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');

$cron_files = array(
	array(
		'filename'		=> 'cron_notification_email.php',
		'time_interval'	=> 'every_30_minutes',
		'status_file'	=> 'cron_notification_email_status.txt',
		'purpose'		=> 'Email notifications'
	),
	array(
		'filename'		=> 'cron_update_currency.php',
		'time_interval'	=> 'every_day_once',
		'status_file'	=> 'cron_update_currency_status.txt',
		'purpose'		=> 'Update Currency'
	)
);

if(file_exists($tconfig['tpanel_path'].'cron_schedule_ride_new.php'))
{
	$cron_files[] = array(
		'filename'		=> 'cron_schedule_ride_new.php',
		'time_interval'	=> 'every_4_minutes',
		'status_file'	=> 'cron_schedule_ride_new_status.txt',
		'purpose'		=> 'Schedule Ride'
	);
}

if(file_exists($tconfig['tpanel_path'].'cron_referral_amount_credit_to_user.php'))
{
    $cron_files[] = array(
        'filename'		=> 'cron_referral_amount_credit_to_user.php',
        'time_interval'	=> 'every_4_minutes',
        'status_file'	=> 'cron_referral_amount_credit_to_user_status.txt',
        'purpose'		=> 'User Referral Credit'
    );
}

if(file_exists($tconfig['tpanel_path'].'cron_driver_subscription.php'))
{
	$cron_files[] = array(
		'filename'		=> 'cron_driver_subscription.php',
		'time_interval'	=> 'every_day_once',
		'status_file'	=> 'cron_driver_subscription_status.txt',
		'purpose'		=> 'Driver Subscription'
	);
}

if(file_exists($tconfig['tpanel_path'].'cron_update_rentitem_status.php') && ($MODULES_OBJ->isEnableRentItemService() || $MODULES_OBJ->isEnableRentCarsService() || $MODULES_OBJ->isEnableRentEstateService()))
{
	$cron_files[] = array(
		'filename'		=> 'cron_update_rentitem_status.php',
		'time_interval'	=> 'every_day_once',
		'status_file'	=> 'cron_update_rentitem_status.txt',
		'purpose'		=> 'Rent Item Status'
	);
}

if(file_exists($tconfig['tpanel_path'].'cron_send_request_drivers_genie_orders.php') && $MODULES_OBJ->isEnableAnywhereDeliveryFeature())
{
	$cron_files[] = array(
		'filename'		=> 'cron_send_request_drivers_genie_orders.php',
		'time_interval'	=> 'every_4_minutes',
		'status_file'	=> 'cron_send_request_drivers_genie_orders_status.txt',
		'purpose'		=> 'Send Request to Drivers for Delivery Genie/Runner Orders'
	);
}

if(file_exists($tconfig['tpanel_path'].'cron_for_schedule_ride_send_notification.php'))
{
    $cron_files[] = array(
        'filename'		=> 'cron_for_schedule_ride_send_notification.php',
        'time_interval'	=> 'every_4_minutes',
        'status_file'	=> 'cron_for_schedule_ride_send_notification.txt',
        'purpose'		=> 'Send Notification to member for Schedule Ride'
    );
}

if(strtoupper($ENABLE_NOTIFICATION_LIVE_ACTIVITY) == "YES") {
	if(file_exists($tconfig['tpanel_path'].'cron_trip_live_activity.php'))
	{
		$cron_files[] = array(
			'filename'		=> 'cron_trip_live_activity.php',
			'time_interval'	=> 'every_1_minute',
			'status_file'	=> 'cron_trip_live_activity_status.txt',
			'purpose'		=> 'Send Live Activity Notification for Trips'
		);
	}

	if(file_exists($tconfig['tpanel_path'].'cron_trip_live_activity_deliverall.php') && $MODULES_OBJ->isDeliverAllFeatureAvailable())
	{
		$cron_files[] = array(
			'filename'		=> 'cron_trip_live_activity_deliverall.php',
			'time_interval'	=> 'every_1_minute',
			'status_file'	=> 'cron_trip_live_activity_deliverall_status.txt',
			'purpose'		=> 'Send Live Activity Notification for Orders'
		);
	}
}

if(file_exists($tconfig['tpanel_path'].'cron_update_driver_availability_status.php'))
{
    $cron_files[] = array(
        'filename'		=> 'cron_update_driver_availability_status.php',
        'time_interval'	=> 'every_4_minutes',
        'status_file'	=> 'cron_update_driver_availability_status.txt',
        'purpose'		=> 'Set Driver Offline if idle for more than 24hrs'
    );
}

/*if(file_exists($tconfig['tpanel_path'].'cron_update_item_image.php'))
{
	$cron_files[] = array(
		'filename'		=> 'cron_update_item_image.php',
		'time_interval'	=> 'every_4_minutes',
		'status_file'	=> 'cron_update_item_image.txt',
		'purpose'		=> 'Update Item Image'
	);
}*/
if($cron_logs == "")
{
	foreach ($cron_files as $fkey => $file) 
	{
		$cron_files[$fkey]['last_executed'] = date('Y-m-d H:i:s');

		WriteToFile($tconfig['tsite_script_file_path'] . $file['status_file'], 'executed');
	}

	$cron_logs = json_encode($cron_files);
	WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", $cron_logs);
}

$cron_logs = json_decode($cron_logs, true);	
$log_filenames = array_column($cron_logs, 'filename');
$cron_filenames = array_column($cron_files, 'filename');
foreach ($cron_logs as $ckey => $cfile) 
{
	if(!in_array($cfile['filename'], $cron_filenames))
	{
		unset($cron_logs[$ckey]);

		unlink($tconfig['tsite_script_file_path'] . $cfile['status_file']);
	}
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));

foreach ($cron_files as $fkey => $file) 
{
	if(!in_array($file['filename'], $log_filenames))
	{
		$cron_logs[] = array(
			'filename'		=> $file['filename'],
			'time_interval'	=> $file['time_interval'],
			'status_file'	=> $file['status_file'],
			'purpose'		=> $file['purpose'],
			'last_executed'	=> date('Y-m-d H:i:s')
		);

		WriteToFile($tconfig['tsite_script_file_path'] . $file['status_file'], "executed");
	}
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));

$cron_urls = array();
foreach ($cron_logs as $log) 
{
	$time_diff = round(((strtotime(date('Y-m-d H:i:s')) - strtotime($log['last_executed'])) / 60), 2);

	$status = GetFileData($tconfig['tsite_script_file_path'] . $log['status_file']);

	if($log['time_interval'] == "every_4_minutes" && (date('i') % 4) == 0 && $status == "executed")
	{
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	}
	elseif ($log['time_interval'] == "every_30_minutes" && (date('i') % 30) == 0 && $status == "executed") {
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	}
	elseif ($log['time_interval'] == "every_day_once" && date('Y-m-d', strtotime($log['last_executed'])) != date('Y-m-d')  && $status == "executed") {
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	} elseif ($log['time_interval'] == "every_1_minute" && (date('i') % 1) == 0 && $status == "executed") {
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	}
}

// echo "<pre>"; print_r($cron_logs); exit();
//An array that will contain all of the information relating to each request.
$requests = array();
 
 
//Initiate a multiple cURL handle
$mh = curl_multi_init();
 
//Loop through each URL.
foreach($cron_urls as $k => $url){
    $requests[$k] = array();
    $requests[$k]['url'] = $url['url'];
    $requests[$k]['purpose'] = $url['purpose'];

    //Create a normal cURL handle for this particular request.
    $requests[$k]['curl_handle'] = curl_init($url['url'] . '?SESSION_CRON_JOB=' . $session_val . '&SCRIPT_FILE=' . $script_file_name);
    //Configure the options for this request.
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_HEADER, true);
    //Add our normal / single cURL handle to the cURL multi handle.
    curl_multi_add_handle($mh, $requests[$k]['curl_handle']);
}
 
//Execute our requests using curl_multi_exec.
$stillRunning = false;
do {
    $status = curl_multi_exec($mh, $stillRunning);
    if ($stillRunning) {
        curl_multi_select($mh);
    }
} while ($stillRunning && $status == CURLM_OK);
 
 
//close the handles
$error = 0;
$result = array();
foreach($requests as $k => $request){
	$http_code = curl_getinfo($request['curl_handle'], CURLINFO_HTTP_CODE);
	if($http_code != "200")
	{
		$result[] = array(
			'url'		=> $request['url'],
			'http_code'	=> $http_code,
			'purpose'	=> $request['purpose'],
			'date'		=> date('Y-m-d H:i:s')
		);
		$error = 1;
	}
    curl_multi_remove_handle($mh, $request['curl_handle']);
    curl_close($requests[$k]['curl_handle']);
}

curl_multi_close($mh);

// echo "<pre>"; print_r($result); exit();
if($error == 1)
{
	WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_jobs_error_log.txt", json_encode($result));

	WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_jobs_status.txt", "error");
}
else {
	WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_jobs_error_log.txt", "");

	WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_jobs_status.txt", "running");
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_jobs_last_executed.txt", date('Y-m-d H:i:s'));

DestroyCronJobSession(basename($script_file_name));
?>