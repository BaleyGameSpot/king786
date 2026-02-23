<?php
// include('../server_monitor_demo/servercheck.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// include_once('../common.php');
// if (file_exists($tconfig['tpanel_path'] . 'assets/libraries/models/class.dashboard.php')) {
    
//     include_once $tconfig['tpanel_path'] . 'assets/libraries/models/class.dashboard.php';
//     $Dashboard_OBJ = new Dashboard;
// }
// $server_data = array();
// $server_data['cpuload'] = $Dashboard_OBJ->servercheck();

// $server_data['memused'] = $memused;
// $server_data['diskused'] = $diskused;

$returnArr['Action'] = 1;
$returnArr['data'] = $server_data;
echo json_encode($returnArr);
exit();
