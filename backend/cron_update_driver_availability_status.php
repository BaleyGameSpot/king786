<?php
include_once ('common.php');
include_once('include/include_webservice_sharkfeatures.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
// CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_update_driver_availability_status.txt", "running");
/* Cron Log Update End */

$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$available_drivers = $obj->MySQLSelect("SELECT iDriverId, vLatitude, vLongitude, vTimeZone FROM register_driver WHERE vAvailability = 'Available' AND tLocationUpdateDate < '$str_date' AND eStatus = 'Active'");

if(!empty($available_drivers)) {
    foreach ($available_drivers as $driverData) {
        $iDriverId = $driverData['iDriverId'];
        $latitude_driver = $driverData['vLatitude'];
        $longitude_driver = $driverData['vLongitude'];
        $vTimeZone = $driverData['vTimeZone'];

        $where = " iDriverId='" . $iDriverId . "'";
        $Data_Update = array();
        $Data_Update['vAvailability'] = "Not Available";
        $Data_Update['tLastOnline'] = date("Y-m-d H:i:s");
        $id = $obj->MySQLQueryPerform("register_driver", $Data_Update, 'update', $where);

        /* update insurance log */
        $curr_date = date('Y-m-d H:i:s');
        $get_data_log = $obj->MySQLSelect("SELECT * FROM driver_log_report WHERE iDriverId = '" . $iDriverId . "' ORDER BY `iDriverLogId` DESC LIMIT 0,1");

        if(!empty($get_data_log)) {
            $obj->sql_query("UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'");
        }
        
        
        $details_arr = array();
        $details_arr['iTripId'] = "0";
        $details_arr['LatLngArr']['vLatitude'] = $latitude_driver;
        $details_arr['LatLngArr']['vLongitude'] = $longitude_driver;
        update_driver_insurance_status($iDriverId, "Available", $details_arr, "updateDriverStatus", "Offline");
        /* update insurance log */

        Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);

        if(!empty($ENABLE_PROVIDER_INSURANCE_LOCATIONS) && strtoupper($ENABLE_PROVIDER_INSURANCE_LOCATIONS) == "YES") {
            $MONGO_OP_OBJ->updateDriverAvailabilityStatus($iDriverId);
        }
    }    
}

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_update_driver_availability_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true); 

foreach ($cron_logs as $ckey => $cfile) 
{
    if($cfile['filename'] == "cron_update_driver_availability_status.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
