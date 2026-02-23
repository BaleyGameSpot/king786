<?php
$_REQUEST['ENABLE_DEBUG'] = 1;
include_once('../common.php');

$queryData = decrypt(rawurldecode($_SERVER['QUERY_STRING']));
$queryData_arr = explode("&", $queryData);

$_REQUEST = array();
foreach ($queryData_arr as $value) {
    $array = explode('=', $value);
    $array[1] = trim($array[1], '"');
    $_REQUEST[$array[0]] = urldecode($array[1]);
}

$tbl_name = 'trips';
if (!$userObj->hasPermission('view-invoice')) {
    $userObj->redirect();
}

$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$vRideNo = isset($_REQUEST['vRideNo']) ? $_REQUEST['vRideNo'] : '';

$videoRecordingFound = "No";
$videoRecording = $MEDIA_SERVER_OBJ->fetchTripVideoRecording($iTripId, $vRideNo);
if(!empty($videoRecording)) {
	$file_size = filesize($videoRecording);
    if($file_size > 0) {
        $videoRecordingFound = "Yes";
        $file_pointer = fopen($videoRecording, "rb");
        $data = fread($file_pointer, $file_size);
        header("Content-type: video/mp4");

        echo $data;
    }	
}

if($videoRecordingFound == "No") {
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Trip Video Rec.</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <?php include_once('global_files.php'); ?>
</head>
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>

    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner" id="page_height" style="">
            <div class="row">
                <div class="col-lg-12">
                	<p style="font-size: 24px">Video Recording not found.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once('footer.php'); ?>
</body>
</html>
<?php } ?>