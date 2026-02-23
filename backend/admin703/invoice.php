<?php
include_once('../common.php');
$tbl_name = 'trips';
//$ENABLE_TIP_MODULE = $CONFIG_OBJ->getConfigurations("configurations", "ENABLE_TIP_MODULE");
//$APP_DELIVERY_MODE = $CONFIG_OBJ->getConfigurations("configurations", "APP_DELIVERY_MODE");
include_once('../send_invoice_receipt.php');
if (!$userObj->hasPermission('view-invoice')) {
    $userObj->redirect();
}
if (isset($_REQUEST['iJobId'])) {
    $_REQUEST['iTripId'] = $_REQUEST['iJobId'];
}
$iTripId = !empty($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : 0;
$script = "Trips";
/* Start original route */
$data_locations = $obj->MySQLSelect("select tPlatitudes,tPlongitudes from trips_locations where iTripId = '" . $iTripId . "'");
$lat_array = $long_array = $latitudes = $longitudes = array();
if (!empty($data_locations) && scount($data_locations) > 0) {
    $lat_array = explode(",", str_replace(" ", "", $data_locations[0]['tPlatitudes']));
    $long_array = explode(",", str_replace(" ", "", $data_locations[0]['tPlongitudes']));
}
$lat_array = array_values(array_filter($lat_array));
$long_array = array_values(array_filter($long_array));
// echo "<pre>";print_r($long_array);die;
$total_ele = scount($lat_array);
$inc = 1;
if ($total_ele > 200) {
    $inc = round($total_ele / 200);
}
// echo $inc=5;
if ($total_ele > 0) {
    for ($i = 0; $i < $total_ele; $i += $inc) {
        $latitudes[] = $lat_array[$i];
        $longitudes[] = $long_array[$i];
    }
    array_push($latitudes, $lat_array[$total_ele - 1]);
    array_push($longitudes, $long_array[$total_ele - 1]);
}
$multi_location = 0;
$getAllTrip = $data_locations = $obj->MySQLSelect("SELECT tStartLat,tStartLong,tEndLat,tEndLong,iTripId,iPoolParentId FROM trips WHERE iTripId=" . $iTripId . "");
if (empty($latitudes[0]) && strtoupper($ENABLE_STOPOVER_POINT) == 'YES') {
    $latitudes = $longitudes = array();
    //$data_locations = $obj->MySQLSelect("select tStartLat,tStartLong,tEndLat,tEndLong from trips where iTripId = '" . $iTripId . "'");
    $sql = "select tActualDestLatitude,tActualDestLongitude,tDestLatitude,tDestLongitude from trips_stopoverpoint_location where iTripId = '" . $iTripId . "'";
    $data_locations_mul = $obj->MySQLSelect($sql);
    if(!empty($data_locations_mul)){
        $latitudes[] = $data_locations[0]['tStartLat'];
        $longitudes[] = $data_locations[0]['tStartLong'];
    }
    foreach ($data_locations_mul as $key => $value) {
        $latitudes[] = (!empty($value['tActualDestLatitude'])) ? $value['tActualDestLatitude'] : $value['tDestLatitude'];
        $longitudes[] = (!empty($value['tActualDestLongitude'])) ? $value['tActualDestLongitude'] : $value['tDestLongitude'];
    }
    $total_ele = scount($latitudes);
    $multi_location = 1;
}
//Commented By HJ On 17-02-2020 As Per Discuss With KS For Solved Issu Demo Server Mantis #9935 Start
//open by SP because map is not shown in cancel trip Mantis #21352
//if (empty($latitudes[0]) || empty($data_locations_mul)) {
if (empty($latitudes[0])) {
    $latitudes = array();
    $longitudes = array();
    $sql = "select tStartLat,tStartLong,tEndLat,tEndLong from trips where iTripId = '" . $iTripId . "'";
    $data_locations = $obj->MySQLSelect($sql);
    $lat_array[0] = $data_locations[0]['tStartLat'];
    $lat_array[1] = $data_locations[0]['tEndLat'];
    $long_array[0] = $data_locations[0]['tStartLong'];
    $long_array[1] = $data_locations[0]['tEndLong'];
    $total_ele = scount($lat_array);
    $inc = 1;
    if ($total_ele > 200) {
        $inc = round($total_ele / 200);
    }
    // echo $inc=5;
    $latitudes = $longitudes = array();
    for ($i = 0; $i < $total_ele; $i += $inc) {
        $latitudes[] = $lat_array[$i];
        $longitudes[] = $long_array[$i];
    }
    array_push($latitudes, $lat_array[$total_ele - 1]);
    array_push($longitudes, $long_array[$total_ele - 1]);
}
//Commented By HJ On 17-02-2020 As Per Discuss With KS For Solved Issu Demo Server Mantis #9935 Start
/* End original route */
$db_trip_data = FetchTripFareDetailsWeb($iTripId, '', '','','','Yes');
$db_reci_data = FetchDeliveryRecepientDetails($iTripId, '', '');

$emailAvailable = "Yes";
if (empty($db_trip_data['DriverDetails']['vEmail'])) {
    $emailAvailable = "No";
}
if (!isset($db_trip_data['iTripId'])) {
    header("location: trip.php");
}
$strpOverPointData = getTripStopOverPointData($iTripId);
$orgDataArr = array();
$orgData = $obj->MySQLSelect("SELECT vCompany,iOrganizationId FROM organization ORDER BY iOrganizationId ASC");
for ($g = 0; $g < scount($orgData); $g++) {
    $orgDataArr[$orgData[$g]['iOrganizationId']] = $orgData[$g]['vCompany'];
}
$organizationName = "";
if (isset($orgDataArr[$db_trip_data['iOrganizationId']]) && $orgDataArr[$db_trip_data['iOrganizationId']] != "") {
    $organizationName = $orgDataArr[$db_trip_data['iOrganizationId']];
}
if (isset($db_trip_data['DriverDetails']['iDriverId']) && file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'])) {
    $img = $tconfig["tsite_upload_images_driver"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'];
} else {
    if ($db_trip_data['eType'] == 'Ride') {
        $img = $tconfig["tsite_url"] . "webimages/icons/help/driver.png";
    } else {
        $img = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
    }
}
if (file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
    $img1 = $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'];
} else {
    if ($db_trip_data['eType'] == 'Ride') {
        $img1 = $tconfig["tsite_url"] . "webimages/icons/help/taxi_passanger.png";
    } else {
        $img1 = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
    }
}
$parentId = $iTripId;
if (scount($getAllTrip) > 0) {
    if (isset($getAllTrip[0]['iPoolParentId']) && $getAllTrip[0]['iPoolParentId'] > 0) {
        $parentId = $getAllTrip[0]['iPoolParentId'];
    }
}
//echo "<pre>";
$getAllTrip = $obj->MySQLSelect("SELECT vRideNo,iTripId,iPoolParentId,iActive FROM trips WHERE (iPoolParentId=" . $parentId . " OR iTripId='" . $parentId . "') AND iTripId !='" . $iTripId . "'");
//echo "<pre>";
//Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected Start
if (isset($db_trip_data['tVehicleTypeFareData']) && $db_trip_data['tVehicleTypeFareData'] != "") {
    $decodeTypeData = (array)json_decode($db_trip_data['tVehicleTypeFareData']);
    $decodeTypeData = (array)$decodeTypeData['FareData'];
    $db_trip_data['videoCategory'] = $db_trip_data['vCategory'];
    $db_trip_data['vCategory'] = "";
}
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}
//Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected End
//added by SP on 09-09-2020 as done by sunita on 04-04-2020 in delyvr project that outstanding shown to the admin in which trip it is generated and not paid by user start
function getUserOutstandingAmountweb($iUserId, $tripId = 0)
{
    global $obj, $data_trips;
    $whereCondi = "AND eAuthoriseIdName='No' AND iAuthoriseId=0";
    $sql = "SELECT iTripOutstandId,fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND iTripId='" . $tripId . "' AND ePaidByPassenger = 'No' AND ePaymentBy = 'Passenger' AND fPendingAmount >0 $whereCondi";
    $getOutStandingAmt = $obj->MySQLSelect($sql);
    $returnArr['iTripOutstandId'] = $returnArr['fPendingAmount'] = 0;
    if (!empty($getOutStandingAmt) && scount($getOutStandingAmt) > 0) {
        $returnArr['iTripOutstandId'] = $getOutStandingAmt[0]['iTripOutstandId'];
        $returnArr['fPendingAmount'] = $getOutStandingAmt[0]['fPendingAmount'];
    }
    return $returnArr;
}

$outstandingamount = getUserOutstandingAmountweb($db_trip_data['iUserId'], $db_trip_data['iTripId']);
//added by SP on 09-09-2020 as done by sunita on 04-04-2020 in delyvr project that outstanding shown to the admin in which trip it is generated and not paid by user end
// Added by HV on 16-10-2020 for Safety Rating
if ($ENABLE_SAFETY_RATING == "Yes") {
    $vSafetyRating = "";
    $safety_rating = $obj->MySQLSelect("SELECT vSafetyRating FROM `ratings_user_driver` WHERE iTripId = '" . $db_trip_data['iTripId'] . "' and eUserType = 'Passenger' AND vSafetyRating != ''");
    if (scount($safety_rating) > 0) {
        $vSafetyRating = $safety_rating[0]['vSafetyRating'];
    }
}
$driverRating = "";
$dRating = $obj->MySQLSelect("SELECT vRating1 FROM `ratings_user_driver` WHERE iTripId = '" . $db_trip_data['iTripId'] . "' and eFromUserType = 'Passenger' AND eToUserType = 'Driver' AND vRating1 != ''");
if (scount($dRating) > 0) {
    $driverRating = $dRating[0]['vRating1'];
}
$userRating = "";
$uRating = $obj->MySQLSelect("SELECT vRating1 FROM `ratings_user_driver` WHERE iTripId = '" . $db_trip_data['iTripId'] . "' and eFromUserType = 'Driver' AND eToUserType = 'Passenger' AND vRating1 != ''");
if (scount($uRating) > 0) {
    $userRating = $uRating[0]['vRating1'];
}
// Added by HV on 16-10-2020 for Safety Rating End

$safety_audio_count = 0;
if($MODULES_OBJ->isEnableSafetyTools() && $MODULES_OBJ->isRideFeatureAvailable('Yes')) {
    $safety_audio = $obj->MySQLSelect("SELECT COUNT(ssm.iAudioFileId) as audio_count FROM service_safety_media as ssm WHERE ssm.iTripId = '" . $db_trip_data['iTripId'] . "'");
    $safety_audio_count = $safety_audio[0]['audio_count'];
}

$videoRecording = "";
if(strtoupper($ENABLE_PROVIDER_CAMERA_REC) == "YES") {
    $videoRecording = $MEDIA_SERVER_OBJ->fetchTripVideoRecording($iTripId, $db_trip_data['vRideNo']);
}
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

    <title>Admin | Invoice</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <meta content="" name="keywords"/>

    <meta content="" name="description"/>

    <meta content="" name="author"/>

    <?php include_once('global_files.php'); ?>

    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>

</head>

<style type="text/css">

    .tg {

        border-collapse: collapse;

        border-spacing: 0;

    }


    .tg td {

        font-family: Arial, sans-serif;

        font-size: 14px;

        padding: 10px 5px;

        border-style: solid;

        border-width: 1px;

        overflow: hidden;

        word-break: normal;

        border-color: black;

    }


    .tg th {

        font-family: Arial, sans-serif;

        font-size: 14px;

        font-weight: normal;

        padding: 10px 5px;

        border-style: solid;

        border-width: 1px;

        overflow: hidden;

        word-break: normal;

        border-color: black;

    }


    .tg .tg-0lax {

        text-align: left;

        vertical-align: top

    }

</style>

<!-- END  HEAD-->

<!-- BEGIN BODY-->

<body class="padTop53 ">

<!-- MAIN WRAPPER -->

<div id="wrap">

    <?php include_once('header.php'); ?>

    <?php include_once('left_menu.php'); ?>

    <!--PAGE CONTENT -->

    <div id="content">

        <div class="inner" id="page_height" style="">

            <div class="row">

                <div class="col-lg-12">

                    <h2>Invoice</h2>

                    <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">

                    <div style="clear:both;"></div>

                </div>

            </div>

            <hr/>

            <?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1) { ?>

                <div class="alert alert-success paddiing-10">

                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                    Email has been sent successfully to the respective E-mail address.

                </div>

            <?php } else if (isset($_REQUEST['fail']) && $_REQUEST['fail'] == 0) { ?>

                <div class="alert alert-danger paddiing-10">

                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                    It seems you doesn't added email in profile so we can't proceed to send email.

                </div>

            <?php } ?>

            <?php
            //echo "<pre>";print_r($db_trip_data);die;
            $systemTimeZone = date_default_timezone_get();
            if ($db_trip_data['fCancellationFare'] > 0 && $db_trip_data['vTimeZone'] != "") {
                $dBookingDate = $endDate = converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
            } else if ($db_trip_data['tStartDateOrig'] != "" && $db_trip_data['tStartDateOrig'] != "0000-00-00 00:00:00" && $db_trip_data['vTimeZone'] != "") {
                $dBookingDate = converToTz($db_trip_data['tStartDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
                $endDate = converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
            } else {
                if (!empty($db_trip_data['tStartDateOrig']) && $db_trip_data['tStartDateOrig'] != "0000-00-00 00:00:00") {
                    $dBookingDate = $db_trip_data['tStartDateOrig'];
                } else {
                    $dBookingDate = $db_trip_data['tTripRequestDateOrig'];
                }
                $endDate = $db_trip_data['tEndDateOrig'];
            }

            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $dBookingDate;
            $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

            $date_format_data_array['tdate'] = $endDate;
            $get_endDate_format = DateformatCls::getNewDateFormat($date_format_data_array);


            ?>

            <div class="table-list">

                <div class="row">

                    <div class="col-lg-12">

                        <div class="panel panel-default">

                            <div class="panel-heading">

                                <b>Your <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> </b>

                                <?php
                                if (($db_trip_data['tTripRequestDateOrig'] == "0000-00-00 00:00:00")) {
                                    echo "Was Cancelled.";
                                } else {
                                    /*echo @date('h:i A', @strtotime($dBookingDate));
                                    ?> on <?= @date('d M Y', @strtotime($dBookingDate));*/
                                    echo $get_dBookingDate_format['tDisplayDateTime'];
                                }
                                ?>

                            </div>

                            <div class="panel-body rider-invoice-new">

                                <div class="row">

                                    <div class="col-sm-6 rider-invoice-new-left">

                                        <?php if ($db_trip_data['eType'] != 'UberX') { ?>

                                            <div id="map-canvas" class="gmap3"

                                                 style="width:100%;height:300px;margin-bottom:10px;"></div>

                                        <?php } ?>

                                        <?php if ($db_trip_data['isVideoCall'] == 'No') { ?>

                                            <span class="location-from"><i class="icon-map-marker"></i>
                                                <?php if($MODULES_OBJ->isInterCityFeatureAvailable() && $db_trip_data['eIsInterCity'] == 'Yes' && $db_trip_data['eRoundTrip'] == 'Yes') { ?>
                                                    <b><?= $get_dBookingDate_format['tDisplayDateTime'];//@date('d M Y h:i A', @strtotime($dBookingDate)); ?>
                                                <?php } else { ?>
                                                    <b><?= $get_dBookingDate_format['tDisplayDateTime'];//@date('h:i A', @strtotime($dBookingDate)); ?>
                                                <?php } ?>

                                                <p><?= $db_trip_data['tSaddress']; ?></p></b>

                                            </span>

                                        <?php } else { ?>

                                            <span class="location-from"><b>Video Consultation Start Time <?= $get_dBookingDate_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($dBookingDate)); ?></b> </span>

                                        <?php } ?>



                                        <?php
                                        if ($db_trip_data['eType'] == 'Ride') {

                                            if($MODULES_OBJ->isInterCityFeatureAvailable() && $db_trip_data['eIsInterCity'] == 'Yes' && $db_trip_data['eRoundTrip'] == 'Yes' && !empty($db_trip_data['tReachedDateTime'])){ ?>
                                                <span class="location-to"><i class="icon-map-marker" style="color: #000;"></i>

                                                    <b><?= @date('d M Y  h:i A', strtotime(converToTz($db_trip_data['tReachedDateTime'], $db_trip_data['vTimeZone'], $systemTimeZone))); ?>

                                                        <p><?= $db_trip_data['tReturnAddress']; ?></p></b>

                                                </span>
                                                <span class="location-to"><i class="icon-map-marker"></i> <b><?= $get_endDate_format['tDisplayDateTime'];//@date('d M Y  h:i A', @strtotime($endDate)); ?> <p><?php  echo $db_trip_data['tDaddress']; ?></p></b></span>

                                            <?php } else {
                                                if (isset($strpOverPointData) && !empty($strpOverPointData)) {
                                                    foreach ($strpOverPointData as $strpOverPointDatakey => $strpOverPointDatavalue) {
                                                        $strpOverPointDatavalue['tReachedTime'] = converToTz($strpOverPointDatavalue['tReachedTime'], $db_trip_data['vTimeZone'], $systemTimeZone);

                                                        $date_format_data_array['tdate'] = $strpOverPointDatavalue['tReachedTime'];
                                                        $get_tReachedTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        ?>

                                                        <span class="location-to"><i class="icon-map-marker"></i> <b><?= $get_tReachedTime_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($strpOverPointDatavalue['tReachedTime'])); ?>

                                                        <p><?php if (!empty($strpOverPointDatavalue['tActualDAddress'])) {
                                                                echo $strpOverPointDatavalue['tActualDAddress'];
                                                            } else {
                                                                echo $strpOverPointDatavalue['tDAddress'];
                                                            } ?></p></b></span>

                                            <?php   }
                                                }

                                                if ($db_trip_data['eType'] != 'UberX' && empty($strpOverPointData) || ($db_trip_data['eType'] == 'UberX' && $db_trip_data['eFareType'] == 'Regular')) { ?>

                                                    <span class="location-to"><i class="icon-map-marker"></i> <b><?= $get_endDate_format['tDisplayDateTime'];//@date('h:i A', @strtotime($endDate)); ?><p><?= $db_trip_data['tDaddress']; ?></p></b></span>

                                                <?php }
                                            }
                                        } ?>

                                        <?php
                                        if ($db_trip_data['eType'] == 'UberX') {
                                            $class_name = 'col-sm-6';
                                            $style = "style='text-align:center;width:100%;'";
                                        } else {
                                            $class_name = 'col-sm-4';
                                            $style = '';
                                        }
                                        ?>

                                        <?php if ($db_trip_data['eType'] != 'UberX') { ?>
                                        <div class="rider-invoice-bottom">


                                            <?php if ($db_trip_data['eType'] != 'UberX') { ?>
                                                <div class="<?= $class_name; ?>" <?= $style; ?>>
                                                    <?php echo $db_trip_data['eIconType'];  ?>
                                                    <br/>
                                                    <b> <?php echo $db_trip_data['carTypeName']; ?> </b>
                                                </div>

                                                <div class="<?= $class_name; ?>">

                                                    Distance<br/>

                                                    <b><?= $db_trip_data['fDistance'] . $db_trip_data['DisplayDistanceTxt']; ?></b>

                                                    <br/>

                                                </div>

                                                <div class="<?= $class_name; ?>">

                                                    <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> time<br/>

                                                    <b><?php echo $db_trip_data['TripTimeInMinutes']; ?></b>

                                                </div>

                                            <?php } ?>

                                            <?php if ((!empty($db_trip_data['vSignImage'])) && $APP_DELIVERY_MODE == 'Multi' && $db_trip_data['eType'] == "Deliver") { ?>

                                                <div class="rider-invoice-bottom">

                                                    <div class="col-sm-6">

                                                        <b><?= $langage_lbl_admin['LBL_SENDER_SIGN']; ?></b>

                                                    </div>

                                                    <?php
                                                    if (file_exists($tconfig["tsite_upload_trip_signature_images_path"] . '/' . $db_trip_data['vSignImage'])) {
                                                        $img123 = $tconfig["tsite_upload_trip_signature_images"] . '/' . $db_trip_data['vSignImage'];
                                                    }
                                                    ?>

                                                    <div class="col-sm-6">

                                                        <img src="<?= $img123; ?>" align="left" style="width: 100px;">

                                                    </div>

                                                </div>

                                            <?php } ?>

                                        </div>

                                        <?php  } ?>

                                        <?php if ($APP_DELIVERY_MODE != 'Multi') { ?>

                                            <div class="rider-invoice-bottom " style="padding:10px 0"><!--row-->

                                                <div class="col-sm-6" style="padding: 0 5px;">

                                                    <div class="row">

                                                        <div class="left col-sm-3">

                                                            <img src="<?= $img; ?>"

                                                                 style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"

                                                                 align="left" height="45" width="45" class="CToWUd">

                                                        </div>

                                                        <div class="right col-sm-9" style="word-wrap: break-word;">

                                                            <div>

                                                                <b><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b>

                                                            </div>
                                                            <?php if(isset($db_trip_data['DriverDetails']['vName'])) {?>
                                                                <div>
                                                                    <?= clearName($db_trip_data['DriverDetails']['vName']) . " " . clearName($db_trip_data['DriverDetails']['vLastName']); ?>
                                                                </div>
                                                            <?php } ?> 
                                                            <?php if(isset($db_trip_data['DriverDetails']['vEmail'])) {?>
                                                                <div><?= clearEmail($db_trip_data['DriverDetails']['vEmail']); ?></div>
                                                            <?php } ?>    
                                                            <?php if (!empty($driverRating) && $driverRating > 0) { ?>
                                                                <br>
                                                                <div><b>Rating</b></div>
                                                                <div>
                                                                    <img src="<?= $tconfig['tsite_url'] . 'assets/img/star.jpg'; ?>"

                                                                         style="margin: 0 2px 4px 0"> <?= $driverRating ?>

                                                                </div>

                                                            <?php } ?>

                                                            <?php if ($ENABLE_SAFETY_RATING == "Yes" && $vSafetyRating != "") { ?>

                                                                <br>

                                                                <div><b>Safety Rating</b></div>

                                                                <div>

                                                                    <img src="<?= $tconfig['tsite_url'] . 'assets/img/star.jpg'; ?>"

                                                                         style="margin: 0 2px 4px 0"> <?= $vSafetyRating ?>

                                                                </div>

                                                            <?php } ?>

                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-sm-6" style="padding: 0 5px;">

                                                    <div class="row">

                                                        <div class="left col-sm-3">

                                                            <img src="<?= $img1; ?>"

                                                                 style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"

                                                                 align="left" height="45" width="45" class="CToWUd">

                                                        </div>

                                                        <div class="right col-sm-9" style="word-wrap: break-word;">

                                                            <div><b><?= $langage_lbl_admin['LBL_RIDER']; ?></b></div>

                                                            <div><?= clearName($db_trip_data['PassengerDetails']['vName'] . " " . $db_trip_data['PassengerDetails']['vLastName']); ?></div>

                                                            <div><?= clearEmail($db_trip_data['PassengerDetails']['vEmail']); ?></div>

                                                            <?php if (!empty($userRating) && $userRating > 0) { ?>

                                                                <br>

                                                                <div><b>Rating</b></div>

                                                                <div>

                                                                    <img src="<?= $tconfig['tsite_url'] . 'assets/img/star.jpg'; ?>"

                                                                         style="margin: 0 2px 4px 0"> <?= $userRating ?>

                                                                </div>

                                                            <?php } ?>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        <?php } ?>

                                        <?php if ($db_trip_data['eType'] == 'UberX') { ?>
                                        <div class="rider-invoice-bottom">

                                            <div class="<?= $class_name; ?>" <?= $style; ?>>

                                                <a class="btn btn-success"
                                                        onclick="return getServiceDetails('<?= $iTripId ?>','','showServiceModalV2');">
                                                    <i class="fa fa-certificate icon-white">
                                                        <b>View Services</b>
                                                    </i>
                                                </a>

                                            </div>
                                        </div>
                                        <?php } ?>

                                        <?php
                                        $orgReason = "";
                                        if ($db_trip_data['eTripReason'] == 'Yes' && $db_trip_data['iTripReasonId'] > 0) {
                                            $tripreasonData = $obj->MySQLSelect("SELECT if(vReasonTitle != '',JSON_UNQUOTE(JSON_VALUE(`vReasonTitle`, '$.vReasonTitle_EN')),'') AS vReasonTitle FROM `trip_reason` where iTripReasonId = '" . $db_trip_data['iTripReasonId'] . "'");
                                            if (!empty($tripreasonData[0]['vReasonTitle'])) {
                                                $orgReason = $tripreasonData[0]['vReasonTitle'];
                                            }
                                        }
                                        if ($db_trip_data['eTripReason'] == 'Yes' && $db_trip_data['vReasonTitle'] != "") {
                                            $orgReason = $db_trip_data['vReasonTitle'];
                                        }
                                        if (!empty($orgReason)) {
                                            ?>

                                            <div class="rider-invoice-bottom">

                                                    <span>

                                                        <b><?= $langage_lbl['LBL_ORGANIZATION_TRIP_REASON']; ?></b>

                                                        <p><?= $orgReason; ?></p>

                                                    </span>

                                            </div>

                                        <?php } ?>

                                    </div>

                                    <div class="col-sm-6 rider-invoice-new-right">

                                        <h4 style="text-align:center;"> <?= $langage_lbl_admin['LBL_FARE_BREAKDOWN_RIDE_NO_TXT']; ?>

                                            #<?= $db_trip_data['vRideNo']; ?></h4>

                                        <hr/>

                                        <table style="width:100%" cellpadding="5" cellspacing="0" border="0">

                                            <tbody>

                                            <?php
                                            $userlangcode = $_SESSION['sess_lang'];
                                            $languageLabelsArr = $LANG_OBJ->FetchLanguageLabelsWeb($userlangcode, "1");
                                            foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value) {
                                                foreach ($value as $k => $val) {
                                                    if ($k == $langage_lbl_admin['LBL_EARNED_AMOUNT']) {
                                                        continue;
                                                    } else if ($k == $langage_lbl_admin['LBL_SUBTOTAL_TXT']) {
                                                        continue;
                                                    } else if ($k == "eDisplaySeperator") {
                                                        echo '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';
                                                    } else {
                                                        ?>

                                                        <tr>

                                                            <td><?= $k; ?></td>

                                                            <td align="right"><?= $val; ?></td>

                                                        </tr>

                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>

                                            <tr>

                                                <td colspan="2">

                                                    <hr style="margin-bottom:0px"/>

                                                </td>

                                            </tr>

                                            <tr>

                                                <td><b>

                                                        <?= $langage_lbl_admin['LBL_Total_Fare_TXT']; ?>

                                                        <?php
                                                        if ($db_trip_data['vTripPaymentMode'] == "Card" && $db_trip_data['ePayWallet'] == 'Yes') {
                                                            echo "(" . ucwords($langage_lbl_admin['LBL_VIA_TXT']) . ' ' . $langage_lbl_admin['LBL_WALLET_TXT'] . ')';
                                                        } else {
                                                            if (strtolower($db_trip_data['vTripPaymentMode']) == "organization") {
                                                                if (strtolower($db_trip_data['ePaymentBy']) == "passenger") {
                                                                    echo $langage_lbl_admin['LBL_PAYMENT_DONE_BY_USER'];
                                                                } else {
                                                                    echo $langage_lbl_admin['LBL_PAYMENT_DONE_BY_ORG'];
                                                                }
                                                            } else {
                                                                echo "(" . ucwords($langage_lbl_admin['LBL_VIA_TXT']) . ' ' . $db_trip_data['vTripPaymentMode'] . ')';
                                                            }
                                                        }
                                                        ?>

                                                        <?php if ($organizationName != "") { ?><br>Organization : <?php
                                                            echo $organizationName;
                                                        }
                                                        ?>

                                                    </b>

                                                </td>

                                                <td align="right">

                                                    <b>

                                                        <?= $db_trip_data['FareSubTotal']; ?>

                                                    </b>

                                                </td>

                                            </tr>

                                            </tbody>

                                        </table>

                                        <?php if (($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)) {
                                            ?>

                                            <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="0"

                                                   width="100%">

                                                <tr>

                                                    <td>

                                                        <b>

                                                            <?php
                                                            if ($db_trip_data['eCancelledBy'] == 'Driver') {
                                                                if (
                                                                    in_array($db_trip_data['eType'], [
                                                                        'Deliver',
                                                                        'Multi-Delivery'
                                                                    ])
                                                                ) {
                                                                    echo $langage_lbl_admin['LBL_DELIVERY_CANCELLED_BY_DRIVER_ADMIN'];
                                                                    echo "<br/>";
                                                                } else {
                                                                    echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                    echo "<br/>";
                                                                }
                                                                /*echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                echo "<br/>";*/
                                                                if (!empty($db_trip_data['vCancelReason'])) {
                                                                    echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                }
                                                            } else if ($db_trip_data['eCancelledBy'] == 'Passenger') {

                                                                if (
                                                                    in_array($db_trip_data['eType'], [
                                                                        'Deliver',
                                                                        'Multi-Delivery'
                                                                    ])
                                                                ) {
                                                                    echo $langage_lbl_admin['LBL_DELIVERY_CANCELLED_BY_PASSANGER_ADMIN_TEXT'];
                                                                    echo "<br/>";
                                                                } else {
                                                                    echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
                                                                    echo "<br/>";
                                                                }
                                                                if (!empty($db_trip_data['vCancelReason'])) {
                                                                    echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                }
                                                            } else {
                                                                echo $langage_lbl_admin['LBL_CANCELED_TRIP_ADMIN_TXT'];
                                                            }
                                                            ?>

                                                        </b>

                                                    </td>

                                                </tr>

                                            </table><br>

                                        <?php } ?>

                                        <?php
                                        $comminsionDisp = 0;
                                        if ($db_trip_data['fTipPrice'] != "" && $db_trip_data['fTipPrice'] != "0" && $db_trip_data['fTipPrice'] != "0.00") {
                                            $comminsionDisp = 1;
                                            ?>

                                            <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="2px" width="100%">
                                                <tr>
                                                    <td><b>Tip given to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b></td>
                                                    <td align="right"><b><?= $db_trip_data['fTipPrice']; ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td><b><?= $langage_lbl_admin['LBL_Commision']; ?></b></td>
                                                    <td align="right">
                                                        <b><?= formateNumAsPerCurrency($db_trip_data['fCommision'], ''); ?></b>
                                                    </td>
                                                </tr>
                                            </table><br>
                                            <?php
                                        }
                                        if (isset($db_trip_data['fCommision']) && $db_trip_data['fCommision'] > 0 && $comminsionDisp == 0) {
                                            ?>

                                            <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="2px" width="100%">
                                                <tr>
                                                    <td><b><?= $langage_lbl_admin['LBL_Commision']; ?></b></td>
                                                    <td align="right">
                                                        <b><?= formateNumAsPerCurrency($db_trip_data['fCommision'], ''); ?></b>
                                                    </td>
                                                </tr>
                                            </table><br>

                                        <?php }
                                        if (!empty($printCategory) && !empty($db_trip_data['tUserCommentNew'])) { ?>
                                            <table style="border:dotted 1px #000000;" cellpadding="5px"
                                                   cellspacing="2px" width="100%">
                                                <tr>
                                                    <td><b>Special Instruction</b></td>
                                                    <td align="right"><?php echo $db_trip_data['tUserCommentNew']; ?></td>
                                                </tr>
                                            </table><br>

                                        <?php }
                                        if (isset($outstandingamount['fPendingAmount']) && $outstandingamount['fPendingAmount'] > 0) { ?>

                                            <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="2px" width="100%">
                                                <tr>
                                                    <td>
                                                        <b><?php echo $langage_lbl_admin['LBL_OUTSTANDING_AMOUNT_TXT']; ?></b>
                                                    </td>
                                                    <td align="right">
                                                        <b><?= $db_trip_data['CurrencySymbol'] . " " . $outstandingamount['fPendingAmount']; ?></b>
                                                    </td>
                                                </tr>
                                            </table><br>

                                        <?php } ?>


                                        <?php if ($safety_audio_count > 0) { ?>
                                            <table style="border:dotted 2px #ff0000;" cellpadding="5px"cellspacing="2px" width="100%">
                                                <tr>
                                                    <td>
                                                        <b style="color: #ff0000">Safety Audio Recordings</b>
                                                    </td>
                                                    <td align="right">
                                                        <a href="javascript:void(0);" onclick="fetchSafetyAudioRecordings('<?= $db_trip_data['iTripId'] ?>')"><b>View Audio Recording</b></a>
                                                    </td>
                                                </tr>
                                            </table><br>
                                        <?php } ?>

                                        <div style="clear:both;"></div>

                                        <?php if (!empty($videoRecording)) { ?>
                                            <table style="border:dotted 2px #000000;" cellpadding="5px"cellspacing="2px" width="100%">
                                                <tr>
                                                    <td>
                                                        <b style="color: #000000">Video Recording (<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>)</b>
                                                    </td>
                                                    <td align="right">
                                                        <a href="<?= $tconfig['tsite_url_main_admin'] . 'trip_video.php?' . rawurlencode(encrypt('iTripId=' . $iTripId . '&vRideNo=' . $db_trip_data['vRideNo'])) ?>" target="_blank"><b>Play Video Recording</b></a>
                                                    </td>
                                                </tr>
                                            </table><br>
                                            <div style="clear:both;"></div>
                                        <?php } ?>

                                        <?php
                                        if (scount($getAllTrip) > 0) {
                                            $tableHaed = "Trips connected with this Pool Trip";
                                            if (scount($getAllTrip) == 1) {
                                                $tableHaed = "Trip connected with this Pool Trip";
                                            }
                                            echo "<h4>" . $tableHaed . "</h4>";
                                            ?>

                                            <br><br><br>

                                            <hr>

                                            <table border="1" width="100%">

                                                <tr>

                                                    <th>#Sr No.</th>

                                                    <th>Booking No</th>

                                                    <th>Status</th>

                                                </tr>

                                                <?php
                                                for ($t = 0; $t < scount($getAllTrip); $t++) {
                                                    $srNO = $t + 1;
                                                    ?>

                                                    <tr>

                                                        <td width="10%"><?= $srNO; ?></td>

                                                        <td width="45%">
                                                            <?php
                                                            if ($APP_TYPE == 'UberX') {
                                                                $id_pro = 'iJobId';
                                                            } else {
                                                                $id_pro = 'iTripId';
                                                            }
                                                            ?>
                                                            <a target="_blank"
                                                               href="invoice.php?<?= $id_pro ?>=<?= $getAllTrip[$t]['iTripId'] ?>"><?= $getAllTrip[$t]['vRideNo']; ?></a>

                                                        </td>

                                                        <td width="45%"><?= $getAllTrip[$t]['iActive']; ?></td>

                                                    </tr>

                                                <?php } ?>

                                            </table>

                                        <?php } ?>

                                        <?php if ($db_trip_data['eType'] == 'Deliver') { ?>

                                            <h4 style="text-align:center;"><?= $langage_lbl_admin['LBL_DELIVERY_DETAILS_TXT_ADMIN']; ?></h4>

                                            <hr/>

                                            <table style="width:100%" cellpadding="5" cellspacing="0" border="0">

                                                <tr>

                                                    <td><?= $langage_lbl_admin['LBL_RECEIVER_NAME']; ?></td>

                                                    <td><?= $db_trip_data['vReceiverName']; ?></td>

                                                </tr>

                                                <tr>

                                                    <td><?= $langage_lbl_admin['LBL_RECEIVER_MOBILE']; ?></td>

                                                    <td><?= $db_trip_data['vReceiverMobile']; ?></td>

                                                </tr>

                                                <tr>

                                                    <td><?= $langage_lbl_admin['LBL_PICK_UP_INS']; ?></td>

                                                    <td><?= $db_trip_data['tPickUpIns']; ?></td>

                                                </tr>

                                                <tr>

                                                    <td><?= $langage_lbl_admin['LBL_DELIVERY_INS']; ?></td>

                                                    <td><?= $db_trip_data['tDeliveryIns']; ?></td>

                                                </tr>

                                                <tr>

                                                    <td><?= $langage_lbl_admin['LBL_PACKAGE_DETAILS']; ?></td>

                                                    <td><?= $db_trip_data['tPackageDetails']; ?></td>

                                                </tr>

                                                <?php
                                                if ((!empty($db_trip_data['vDeliveryConfirmCode']) && ($db_trip_data['eType'] == 'Deliver') || ($db_trip_data['eType'] == 'Multi-Delivery' && $DELIVERY_VERIFICATION_METHOD == "Code")) && ($db_trip_data['iActive'] != 'Finished' && $db_trip_data['iActive'] != 'Canceled')) {
                                                    // if (!empty($db_trip_data['vDeliveryConfirmCode'])) {
                                                    ?>

                                                    <tr>

                                                        <td><?= $langage_lbl_admin['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></td>

                                                        <td><?= $db_trip_data['vDeliveryConfirmCode']; ?></td>

                                                    </tr>

                                                <?php } ?>

                                            </table>

                                        <?php } ?>



                                        <?php
                                        // echo $db_trip_data['vBeforeImage'];exit;
                                        if ($db_trip_data['eType'] == 'UberX' && ($db_trip_data['vBeforeImage'] != '' || $db_trip_data['vAfterImage'] != '')) {
                                            $img_path = $tconfig["tsite_upload_trip_images"];
                                            ?>

                                            <h4 style="text-align:center;"><?= $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT']; ?></h4>

                                            <hr/>

                                            <div class="invoice-right-bottom-img" style="margin-bottom: 20px">

                                                <?php if ($db_trip_data['vBeforeImage'] != '') { ?>

                                                    <div class="col-sm-6">

                                                        <h3><?= $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN']; ?></h3>

                                                        <b><a href="<?= $db_trip_data['vBeforeImage']; ?>"

                                                              target="_blank"><img

                                                                        src="<?= $db_trip_data['vBeforeImage'] ?>"

                                                                        style="width:200px;"

                                                                        alt="Before Images"/></a></b>

                                                    </div>

                                                <?php } ?>

                                                <?php if ($db_trip_data['vAfterImage'] != '') { ?>

                                                    <div class="col-sm-6">

                                                        <h3><?= $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN']; ?></h3>

                                                        <b><a href="<?= $db_trip_data['vAfterImage']; ?>"

                                                              target="_blank"><img

                                                                        src="<?= $db_trip_data['vAfterImage']; ?>"

                                                                        style="width:200px;"

                                                                        alt="After Images"/></a></b>

                                                    </div>

                                                <?php } ?>

                                            </div>

                                        <?php } ?>

                                        <?php
                                        if (
                                            in_array($db_trip_data['eType'], [
                                                'Ride',
                                                'Deliver',
                                                'UberX'
                                            ]) && $db_trip_data['vFaceMaskVerifyImage'] != ''
                                        ) {
                                            $img_path = $tconfig["tsite_upload_face_mask_verify_images"];
                                            ?>

                                            <h4 style="text-align:center;"><?= $langage_lbl_admin['LBL_FACE_MASK_VERIFICATION']; ?></h4>

                                            <hr/>

                                            <div class="invoice-right-bottom-img">

                                                <div class="col-sm-6">

                                                    <b><a href="<?= $img_path . $db_trip_data['vFaceMaskVerifyImage']; ?>"

                                                          target="_blank"><img

                                                                    src="<?= $img_path . $db_trip_data['vFaceMaskVerifyImage']; ?>"

                                                                    style="width:200px;"

                                                                    alt="Face Mask Verification Image"/></a></b>

                                                </div>

                                            </div>

                                        <?php } ?>

                                    </div>

                                    <div class="clear"></div>

                                    <?php if (isset($db_trip[0]['eType']) && $db_trip[0]['eType'] == 'Deliver') { ?>

                                        <div class="invoice-table">

                                            <?php
                                            $db_trips_locations = $obj->MySQLSelect("SELECT * FROM trips_delivery_locations AS tdl WHERE iTripId = '" . $iTripId . "'");
                                            ?>

                                            <?php
                                            $i = 1;
                                            if (!empty($db_trips_locations)) {
                                                foreach ($db_trips_locations as $dtls) {
                                                    $class = (!empty($dtls['vSignImage'])) ? 'sign-img' : '';
                                                    ?>

                                                    <div class="col-sm-6 <?= $class; ?>">

                                                        <h4><?= $langage_lbl_admin['LBL_RECIPIENT_LIST_TXT'] . '&nbsp;' . $i; ?></h4>

                                                        <hr/>

                                                        <table style="width:100%" cellpadding="5" cellspacing="0"

                                                               border="0">

                                                            <tr>

                                                                <td class="label_left"><?= $langage_lbl_admin['LBL_RECIPIENT_NAME_HEADER_TXT']; ?></td>

                                                                <td class="detail_right"><?= $dtls['vReceiverName']; ?></td>

                                                            </tr>

                                                            <tr>

                                                                <td class="label_left"><?= $langage_lbl_admin['LBL_DROP_OFF_LOCATION_RIDE_DETAIL']; ?></td>

                                                                <td class="detail_right"><?= $dtls['tPickUpIns'] . "," . $dtls['tDaddress']; ?></td>

                                                            </tr>

                                                            <tr>

                                                                <td class="label_left"><?= $langage_lbl_admin['LBL_DELIVERY_INS']; ?></td>

                                                                <td class="detail_right"><?= $dtls['tDeliveryIns']; ?></td>

                                                            </tr>

                                                            <tr>

                                                                <td class="label_left"><?= $langage_lbl_admin['LBL_PACKAGE_DETAILS']; ?></td>

                                                                <td class="detail_right"><?= $dtls['tPackageDetails']; ?></td>

                                                            </tr>

                                                            <tr>

                                                                <td class="label_left"><?= $langage_lbl_admin['LBL_DELIVERY_STATUS_TXT']; ?></td>

                                                                <td class="detail_right"><b><?= $dtls['iActive']; ?></b>

                                                                </td>

                                                            </tr>

                                                            <?php if (!empty($dtls['vSignImage'])) { ?>

                                                                <tr>

                                                                    <td class="label_left"><?= $langage_lbl_admin['LBL_RECEIVER_SIGN']; ?></td>

                                                                    <td class="detail_right">

                                                                        <?php
                                                                        if (file_exists($tconfig["tsite_upload_trip_signature_images_path"] . '/' . $dtls['vSignImage'])) {
                                                                            $img1 = $tconfig["tsite_upload_trip_signature_images"] . '/' . $dtls['vSignImage'];
                                                                        }
                                                                        ?>

                                                                        <img src="<?= $img1; ?>" align="left">

                                                                    </td>

                                                                </tr>

                                                            <?php } ?>

                                                        </table>

                                                    </div>

                                                    <?php
                                                    $i++;
                                                }
                                            }
                                            ?>

                                        </div>

                                    <?php } ?>

                                    <?php if (($db_trip_data['PassengerDetails']['eHail'] != "Yes")) { ?>

                                        <div class="row invoice-email-but">

                                                <span>

                                                    <a href="../send_invoice_receipt.php?action_from=mail&iTripId=<?= $db_trip_data['iTripId'] ?>"><button

                                                                class="btn btn-primary ">E-mail</button></a>

                                                </span>

                                        </div>

                                    <?php } ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="clear"></div>

            </div>

        </div>

    </div>

    <!--END PAGE CONTENT -->

</div>

<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<!--END MAIN WRAPPER -->

<div class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"

     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4 id="servicetitle">

                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>

                    Service Details

                    <button type="button" class="close" data-dismiss="modal">x</button>

                </h4>

            </div>

            <div class="modal-body" style="max-height: 450px;overflow: auto;">

                <div id="service_detail"></div>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="safety_media_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="width: 900px">
        <div class="modal-content">
            <div class="modal-header" style="float: left; width: 100%">
                <h4 class="pull-left">
                    Safety Audio Recordings
                </h4>
                <button type="button" class="close pull-right" data-dismiss="modal">x</button>
            </div>
            <div class="modal-body" style="overflow: auto;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Audio Recording</th>
                            </tr>
                        </thead>
                        <tbody id="safety-media-details">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>
<?php
include_once 'service_details.php';
?>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>

<script>

    var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';
    h = window.innerHeight;
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    // var waypts = [];
    var arr1 = [];
    var lats = [];
    var longs = [];
    var markers = [];
    var map;

    function initialize() { //alert('<?= json_encode($latitudes) ?>');
        var thePoint = new google.maps.LatLng('<?= $db_trip_data['tStartLat']; ?>', '<?= $db_trip_data['tStartLong']; ?>');

        GOOGLE_MAP_OBJ.options.center = new google.maps.LatLng(parseFloat('<?= $db_trip_data['tStartLat']; ?>'), parseFloat('<?= $db_trip_data['tStartLong']; ?>'));
        map = GOOGLE_MAP_OBJ.init('map-canvas');

        from_to_polyline();
    }

    var tPlatitudes = '<?= json_encode($latitudes) ?>';
    lats = JSON.parse(tPlatitudes);
    var tPlongitudes = '<?= json_encode($longitudes) ?>';
    longs = JSON.parse(tPlongitudes);


    var multi_loc = '<?= $multi_location; ?>';
    var pts = [];
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < lats.length; i++) {
        var latlongs = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        pts.push(latlongs);
        var point = latlongs;
        bounds.extend(point);
        if (i == 0) {
            var start = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        } else if (i == lats.length - 1) {
            var end = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        } else {
            if (multi_loc == 1) {
                var middle = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
            }
        }
    }
    var directionsService = new google.maps.DirectionsService();
    var directionsOptions = { // For Polyline Route line options on map
        polylineOptions: {
            path: pts,
            strokeColor: '#f35e2f',
            strokeOpacity: 1.0,
            strokeWeight: 4
        }
    };
    var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
    

    function from_to() {
        var request = {
            origin: start, // From locations latlongs
            destination: end, // To locations latlongs
            travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
        };
        directionsService.route(request, function (response, status) {
            directionsDisplay.setMap(map);
            directionsDisplay.setDirections(response);
        });
    }

    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);
    });

    function from_to_polyline() {
        DeleteMarkers('from_loc');
        DeleteMarkers('to_loc');
        setMarker(start, 'from_loc');
        setMarker(middle, '');
        setMarker(end, 'to_loc');
        var flightPath = '';
        var flightPath = new google.maps.Polyline({
            path: pts,
            geodesic: true,
            strokeColor: '#f35e2f',
            strokeOpacity: 1.0,
            strokeWeight: 4
        });
        map.fitBounds(bounds);
        flightPath.setMap(map);
    }

    function setMarker(postitions, valIcon) {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = '../webimages/upload/mapmarker/PinFrom.png';
        } else if (valIcon == 'to_loc') {
            newIcon = '../webimages/upload/mapmarker/PinTo.png';
        } else {
            newIcon = '../webimages/upload/mapmarker/Pin-middle.png';
        }
        marker = new google.maps.Marker({
            map: map,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon
        });
        marker.id = valIcon;
        markers.push(marker);
    }

    function DeleteMarkers(newId) {
        for (var i = 0; i < markers.length; i++) {
            if (newId != '') {
                if (markers[i].id == newId) {
                    markers[i].setMap(null);
                }
            } else {
                markers[i].setMap(null);
                markers = [];
            }
        }
    }

    function showServiceModal(elem) {
        var tripJson = JSON.parse($(elem).attr("data-json"));
        var tripJsonVehicleTypeData = JSON.parse($(elem).attr("data-jsonVehicleTypeData"));
        var rideNo = $(elem).attr("data-trip");
        var typeNameArr = JSON.parse(typeArr);
        var serviceHtml = "";
        var srno = 1;
        // added by sunita
        for (var g = 0; g < tripJson.length; g++) {
            //serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?= $langage_lbl_admin['LBL_QTY_TXT'] ?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
            serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['id']] + "&nbsp;&nbsp;&nbsp;&nbsp;  ";
            if (tripJson[g]['eAllowQty'] == 'Yes') {
                serviceHtml += "<?= $langage_lbl_admin['LBL_QTY_TXT'] ?>: <b>" + [tripJson[g]['qty']] + "</b>";
            }
            serviceHtml += "</p>";
            if (tripJsonVehicleTypeData[g]['tUserComment'] != "" && tripJsonVehicleTypeData[g]['tUserComment'] != "undefined") {
                serviceHtml += "<p> Special Instruction: " + tripJsonVehicleTypeData[g]['tUserComment'] + "</p>";
            }
            srno++;
        }
        $("#service_detail").html(serviceHtml);
        $("#servicetitle").text("Service Details : " + rideNo);
        $("#service_modal").modal('show');
        return false;
    }

    function fetchSafetyAudioRecordings(iTripId) {
        $('#loaderIcon').show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_fetch_safety_audio_recordings.php',
            'AJAX_DATA': "iTripId=" + iTripId,
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').hide();
            if (response.action == "1") {
                var data = response.result;
                $("#safety-media-details").html(data);
                $('#safety_media_modal').find('.table-responsive .mCSB_container').css('padding-bottom', '0');
                $('#safety_media_modal').modal('show');
            } else {

            }
        });
    }

</script>

</body>

<!-- END BODY-->

</html>