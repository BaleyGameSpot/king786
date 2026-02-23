<?php
include_once('../common.php');

 

$script="LiveMap";
//For Set Trip Tracking Old Method
//1) Simple Refresh - Enable TRIP_TRACKING_METHOD Simple Refresh From Configuration Then Set PUBSUB_TECHNIQUE = None
//2) Pubnub - Enable TRIP_TRACKING_METHOD Pubnub From Configuration Then Set PUBSUB_TECHNIQUE = PubNub
//3) SocketCluster - Enable TRIP_TRACKING_METHOD SocketCluster From Configuration Then Set PUBSUB_TECHNIQUE = SocketCluster
//For Set Trip Tracking New Method As Per Discuss With KS Sir On 12-01-2019
//1) Simple Refresh - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = None
//2) Pubnub - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = PubNub
//3) SocketCluster - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = SocketCluster
//4) Yalgaar - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = Yalgaar
$getConfig = $obj->MySQLSelect("SELECT vName,vValue FROM configurations WHERE vName='PUBSUB_TECHNIQUE' OR vName='YALGAAR_CLIENT_KEY'");
$PUBSUB_TECHNIQUE = "None";
$YALGAAR_CLIENT_KEY = "";
for ($r = 0; $r < scount($getConfig); $r++) {
    if (isset($getConfig[$r]['vName']) && $getConfig[$r]['vName'] == "PUBSUB_TECHNIQUE") {
        $PUBSUB_TECHNIQUE = $getConfig[$r]['vValue'];
    }
    if (isset($getConfig[$r]['vName']) && $getConfig[$r]['vName'] == "YALGAAR_CLIENT_KEY") {
        $YALGAAR_CLIENT_KEY = $getConfig[$r]['vValue'];
    }
}
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
// $iTripId = decrypt($iTripId);
 $iTripId = base64_decode(base64_decode($iTripId));

$FETCH_TRIP_STATUS_MAX_TIME_INTERVAL = fetchtripstatustimeMAXinterval();
$sql = "select iUserId,iDriverId,iActive From trips where iTripId=" . $iTripId;
$db_dtrip = $obj->MySQLSelect($sql);

$driverName = $riderName = $phone = $avgRating = $vehicle_number = $starHtml = $vMake = $vTitle = $vehicle_modal = "";
$vehicle_number = "Licence No.";
$driver_avatar =  $tconfig['tsite_url']."assets/img/profile-user-img.png";

$tsite_sc_host = $tconfig['tsite_sc_host'];
$tsite_host_sc_port = $tconfig['tsite_host_sc_port'];
$tsite_yalgaar_url = $tconfig['tsite_yalgaar_url'];

if (scount($db_dtrip) > 0) {
    $iDriverId = $db_dtrip[0]['iDriverId'];

    $iUserId = $db_dtrip[0]['iUserId'];
    $get_driver = $obj->MySQLSelect("SELECT vName,vLastName,vImage,vAvgRating,vCode,vPhone,iDriverVehicleId FROM register_driver WHERE iDriverId='" . $iDriverId . "'");

    if (scount($get_driver) > 0) {
        $driverName = $get_driver[0]['vName'] . " " . $get_driver[0]['vLastName'];
        $phone = "+" . $get_driver[0]['vCode'] . "-" . $get_driver[0]['vPhone'];
        $avgRating = $get_driver[0]['vAvgRating'];
        $vehicleId = $get_driver[0]['iDriverVehicleId'];
        if ($vehicleId > 0) {
            $get_vehicle_data = $obj->MySQLSelect("SELECT iYear,iMakeId,iModelId,vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId='" . $vehicleId . "'");
            if (scount($get_vehicle_data) > 0) {
                $iMakeId = $get_vehicle_data[0]['iMakeId'];
                $iModelId = $get_vehicle_data[0]['iModelId'];
                $vehicle_number = $get_vehicle_data[0]['vLicencePlate'];
                $get_make_data = $obj->MySQLSelect("SELECT vMake FROM make WHERE iMakeId='" . $iMakeId . "'");
                if (scount($get_make_data) > 0) {
                    $vMake = $get_make_data[0]['vMake'];
                    $vehicle_modal = $vMake;
                }
                $get_model_data = $obj->MySQLSelect("SELECT vTitle FROM model WHERE iModelId='" . $iModelId . "'");
                if (scount($get_model_data) > 0) {
                    $vTitle = $get_model_data[0]['vTitle'];
                    if ($vehicle_modal == "") {
                        $vehicle_modal = $vTitle;
                    } else {
                        $vehicle_modal .= " " . $vTitle;
                    }
                }
            }
        }
        if ($get_driver[0]['vImage'] != "") {
            $driver_avatar = $tconfig["tsite_upload_images_driver"] . '/' . $iDriverId . '/2_' . $get_driver[0]['vImage'];
        }
    }
    $get_rider = $obj->MySQLSelect("SELECT vName,vLastName FROM register_user WHERE iUserId='" . $iUserId . "'");
    if (scount($get_rider) > 0) {
        $riderName = $get_rider[0]['vName'] . " " . $get_rider[0]['vLastName'];
    }
}
if ($vehicle_modal == "") {
    $vehicle_modal = "Modal";
}

if ($avgRating > 5) {
    $avgRating = 5;
}
$starRate = $starLoop = floor($avgRating);
$halfStart = 0;
if ($avgRating > $starRate) {
    $halfStart = 1;
    $starLoop += $halfStart;
}
$offStart = 5 - $starLoop;
for ($s = 0; $s < $starRate; $s++) {
    $starHtml .= '<img src="../assets/img/star-on-big.png">';
}
if ($halfStart > 0) {
    $starHtml .= '<img src="../assets/img/star-half-big.png">';
}
for ($d = 0; $d < $offStart; $d++) {
    $starHtml .= '<img src="../assets/img/star-off-big.png">';
}

$fav_icon_image = "favicon.ico";
if (file_exists($tconfig["tpanel_path"] . $logogpath . $fav_icon_image)) {
    $fav_icon_image = $tconfig["tsite_url"] . $logogpath . $fav_icon_image;
} else {
    $fav_icon_image = $tconfig["tsite_url"] . '' . ADMIN_URL_CLIENT . '/' . 'images/' . $fav_icon_image;
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Live tracking </title>
         <?php if ($trackingMethod == "Simple Refresh" && $enablePubnub == 0 && $enableSocketCluster == 0) { ?>
            <meta http-equiv="refresh" content="120" >
        <?php } ?>
		<?php include_once('global_files.php'); ?>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <script src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.21.6.js"></script>
        <!--<script src="http://cdn.pubnub.com/pubnub.min.js"></script>-->
        <link rel="icon" href="<?php echo $fav_icon_image; ?>" type="image/x-icon">
           
        <link href="<?php echo $tconfig["tsite_url"] ?>assets/css/checkbox.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $tconfig["tsite_url"] ?>assets/css/radio.css" rel="stylesheet" type="text/css" />
			
		 <link href="<?php echo $tconfig["tsite_url"] ?>assets/css/style_v5_cubejek.css" rel="stylesheet" type="text/css" />
		 
	 
		<link rel="stylesheet" href="<?php echo $tconfig["tsite_url"] ?>assets/css/style_v5_color_cubejek.css">
		<link rel="stylesheet" href="<?php echo $tconfig["tsite_url"] ?>assets/css/design_v5_cubejek.css">
		<link rel="stylesheet" href="<?php echo $tconfig["tsite_url"] ?>assets/css/fa-icon.css">
		<link href="<?php echo $tconfig["tsite_url"] ?>assets/css/initcarousel.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="<?php echo $tconfig["tsite_url"] ?>assets/css/media_cubejek.css">
		<style type="text/css">
            .Msgbox{
                width:90%;padding-left:50px;text-align: center;
            }
            .marker {
                transform: rotate(-180deg);
            }
        </style> 
		 <link rel="stylesheet" href="<?php echo $tconfig["tsite_url"] ?>assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
		 <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		 <link rel="stylesheet" href="<?php echo $tconfig["tsite_url"] ?>assets/validation/validatrix.css">
		 <script src="<?php echo $tconfig["tsite_url"] ?>assets/validation/validatrix.js"></script> 
    </head>
    <!-- END  HEAD-->

    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
		
        <div id="wrap">
		<div class="inner" width="100%">

			<div class="row">

            <!--PAGE CONTENT -->
              <div class="page-contant page-contant trip-tracking-main">
                <div class="page-contant-inner trip-tracking">	
                    <h2 class="header-page add-car-vehicle">
                        <?= clearName($riderName); ?>'s Trip
                        <font class="trip-start">On Trip</font>
                    </h2>


                    <?php

                    if (isset($db_dtrip[0]['iActive']) && ( $db_dtrip[0]['iActive'] == 'Arrived' || $db_dtrip[0]['iActive'] == 'Active' ||  $db_dtrip[0]['iActive'] == 'On Going Trip' ))  { ?>
                        <div class="map-page" style="display:none; width: 100%">
                            <div class="panel-heading location-heading">
                                <i class="icon-map-marker"></i>
                                <?= $langage_lbl['LBL_LOCATIONS_TXT']; ?>
                            </div>
                            <div class="panel-heading location-map" style="background:none;">
                                <div class="google-map-wrap" >
                                    <div class="gmap-div gmap-div1"><div id="map-canvas" class="gmap3 google-map" style="height:720px;"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="trip-track-parent">
                            <div class="trip-track-cub">
                                <div class="trip-track-cub-left">
                                    <div class="trip-track-cub-left-l1">
                                        <img src="../assets/img/car-img.png">
                                    </div>
                                    <div class="trip-track-cub-left-l-sec">
                                        <div class="trip-track-cub-left-l-ab-11"><h2><?= $vehicle_modal; ?></h2></div>
                                        <div class="trip-track-cub-left-l-bb-11"><b><?= $vehicle_number; ?></b></div>
                                    </div>
                                </div>
                                <div class="trip-track-cub-right-11"> 
                                    <div class="trip-track-lrft-text">
                                        <b><?= clearName($driverName); ?></b>
                                        <a href="javascript:void(0);">
                                            <?= $starHtml; ?>
                                        </a>
                                        <span><?= clearPhone($phone);  ?></span>
                                    </div>
                                    <div class="trip-track-right-img">
                                        <div class="driver-profile-img">
                                            <a href="javascript:void(0);">
                                                <img src="<?= $driver_avatar; ?>" title="<?= clearName($driverName); ?>" alt="<?= clearName($driverName); ?>">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                        </div>
                    <?php } else if (isset($db_dtrip[0]['iActive']) && $db_dtrip[0]['iActive'] == 'Finished') { ?>
                        <br><br><br><br>
                        <div class="row Msgbox">
                            <div class="alert alert-danger paddiing-10">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_TRIP_IS_FINISHED']; ?>.
                            </div>
                        </div>
                    <?php } else if (isset($db_dtrip[0]['iActive']) && $db_dtrip[0]['iActive'] == 'Canceled') { ?> 
                        <br><br><br><br>
                        <div class="row Msgbox">
                            <div class="alert alert-danger paddiing-10">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_CANCELED_TRIP_TXT']; ?>.
                            </div>
                        </div>
                    <?php } else { ?>
                        <br><br><br><br>
                        <div class="row Msgbox">
                            <div class="alert alert-danger paddiing-10">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_TRIP_TXT']." ".$langage_lbl['LBL_NOT_FOUND']; ?>.
                            </div>
                        </div>
                    <?php } ?>

                </div>

            </div>
          
            <div style="clear:both;"></div>
         </div>
            <!--END PAGE CONTENT -->
        </div>
	</div>
            <!--END PAGE CONTENT -->
        </div> 
        <!--END MAIN WRAPPER -->

        <script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>" type="text/javascript"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/jquery_easing.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/getDataFromApi.js"></script>
        <script type="text/javascript" src="<?= $tconfig["tsite_url"];?>assets/libraries/scClient-js/socketcluster-client.js"></script>
        <script type="text/javascript" src="<?= $tconfig["tsite_url"];?>assets/js/socketclustercls.js"></script>
        <script>
            var iTripId = '<?php echo $iTripId; ?>';
            var latlng;
            var locallat;
            var locallang;
            var map;
            var interval3;
            var marker = [];
            var myOptions = [];
            function moveToLocation(lat, lng) {
                var center = new google.maps.LatLng(lat, lng);
                // using global variable:
                map.panTo(center);
            }
            function handleResponse(response) {
                //var response = JSON.parse(response.message);
                //var response = response.message;
                //console.log(response);
                if (response.vLatitude != "" && response.vLongitude != "") {
                    $('.map-page').show();
                    latlng = new google.maps.LatLng(response.vLatitude, response.vLongitude);
                    myOptions = {
                        zoom: 4,
                        center: latlng,
                    }
                    var duration = parseInt(950);
                    if (duration < 0) {
                        duration = 1;
                    }
                    setTimeout(function () {
                        //marker.setAnimation(null)
                        marker.animateTo(latlng, {easing: 'linear', duration: duration});
                        map.panTo(latlng); // For Move Google Map By Animate
                    }, 2000);
                    //map.setCenter(latlng); // For Set Center Location of Google Map Marker
                    //changeMarker(90);
                }
            }
            function changeMarker(deg) {
                //var deg = 90
                //document.getElementById("#markerLayer img").style.transform = 'rotate(' + deg + 'deg)';
                //document.querySelector('#markerLayer img').style.transform = 'rotate(' + deg + 'deg)'
                google.maps.event.clearListeners(map, 'idle');
            }
            function initialize() {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail.php',
                    'AJAX_DATA': {iTripId: iTripId},
                    'REQUEST_DATA_TYPE': 'json'
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var driverdetail = response.result;
                        if (driverdetail != 1) {
                            $('.map-page').show();
                            var latdrv = driverdetail.vLatitude;
                            var longdrv = driverdetail.vLongitude;
                            latlng = new google.maps.LatLng(latdrv, longdrv);
                            locallat = new google.maps.LatLng(driverdetail.tStartLat, driverdetail.tStartLong);
                            locallang = new google.maps.LatLng(driverdetail.tEndLat, driverdetail.tEndLong);
                            fromLatlongs = driverdetail.tStartLat + ", " + driverdetail.tStartLong;
                            toLatlongs = driverdetail.tEndLat + ", " + driverdetail.tEndLong;
                            //toLatlongs = '23.0146207'+", "+'72.5284118';

                            GOOGLE_MAP_OBJ.options.zoom = 16;
                            GOOGLE_MAP_OBJ.options.center = latlng;
                            map = GOOGLE_MAP_OBJ.init('map-canvas');

                            var overlay = new google.maps.OverlayView()
                            overlay.draw = function () {
                                this.getPanes().markerLayer.id = 'markerLayer'
                            }
                            marker = new google.maps.Marker({
                                position: latlng,
                                map: map,
                                //animation:google.maps.Animation.BOUNCE,
                                //icon: "webimages/upload/mapmarker/car_driver.png",
                                icon: {
                                    url: '../webimages/upload/mapmarker/source_marker.png',
                                    // This marker is 20 pixels wide by 32 pixels high.
                                    scaledSize: new google.maps.Size(50, 50),
                                    rotation: 90
                                },
                                id: 'marker'
                            });
                        } else {
                            $('.map-page').hide();
                        }
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }
            google.maps.event.addDomListener(window, 'load', initialize);
			
			var channel = 'ONLINE_DRIVER_LOC_<?php echo $iDriverId; ?>';
            SOCKET_OBJ.subscribe(channel, function (response) {
                //var response = JSON.parse(data);
                handleResponse(response);
            });
        </script>
           
      </body>
    <!-- END BODY-->
</html>