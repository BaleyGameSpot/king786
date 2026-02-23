<?php
$url = $_REQUEST['url'];
if (filter_var($url, FILTER_VALIDATE_URL) && ((substr($url, 0, strlen('https://')) === 'https://') || (substr($url, 0, strlen('http://')) === 'http://'))) {
	// get place data using place id from google
	echo $response = json_encode(file_get_contents($url));
	exit;
}

echo "<h1>Bad Request</h1>";

http_response_code(400);

?>