<?php
include_once 'common.php';

if(!empty($_REQUEST) && !empty($_REQUEST['urlToVisit'])){
	$urlToVisit = $_REQUEST['urlToVisit'];
	unset($_REQUEST['urlToVisit']);

	if(strtoupper($ENABLE_MAPS_API_REPLACEMENT) == "YES" && strtoupper($MAPS_API_REPLACEMENT_STRATEGY) == "ADVANCE") {
		$postData = $_REQUEST;

		$response = SysCurlPostAuth($postData, $urlToVisit);
	} else {
		$url_visit = $urlToVisit."?".http_build_query($_REQUEST);

		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);

		if (filter_var($url_visit, FILTER_VALIDATE_URL) && ((substr($url_visit, 0, strlen('https://')) === 'https://') || (substr($url_visit, 0, strlen('http://')) === 'http://'))) {
			$response = file_get_contents($url_visit, false, stream_context_create($arrContextOptions));
		} else {
			echo "<h1>Bad Request</h1>";

			http_response_code(400);
		}
		
	}
	
    echo $response;
	exit;
}

?>
