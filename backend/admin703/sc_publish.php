<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

require_once ('../common.php');

$channelNames = "SOCKET_CLUSTER_STATUS_CHECK";
$messageArr['message'] = "Socket Cluster PubSub PHP working";
$messageArr['type'] = "PHP";
$messageArr['ChannelName'] = $channelNames;
$message = json_encode($messageArr);


echo $EVENT_MSG_OBJ->executeRequest($channelNames, $message);

?>