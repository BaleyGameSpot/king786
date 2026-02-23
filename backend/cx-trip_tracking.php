<?php
include_once('common.php');

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
//$encodeTripId = base64_encode(base64_encode(991));
//echo $encodeTripId;die;
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$iTripId = base64_decode(base64_decode($iTripId));
$FETCH_TRIP_STATUS_MAX_TIME_INTERVAL = fetchtripstatustimeMAXinterval();
$sql = "select iUserId,iDriverId,iActive,eType From trips where iTripId=" . $iTripId;
$db_dtrip = $obj->MySQLSelect($sql);
$driverName = $riderName = $phone = $avgRating = $vehicle_number = $starHtml = $vMake = $vTitle = $vehicle_modal = "";
$vehicle_number = "Licence No.";
$driver_avatar = "assets/img/profile-user-img.png";
$driver_avatar_alt = "assets/img/profile-user-img.png";

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
$avgRatingR = round($avgRating);

$starRate = $starLoop = ($avgRating < 3.5) ? floor($avgRating) : round($avgRating);
$halfStart = 0;
if ($avgRatingR > $starRate) {
    $halfStart = 1;
    $starLoop += $halfStart;
}
$offStart = 5 - $starLoop;
for ($s = 0; $s < $starRate; $s++) {
    $starHtml .= '<img src="assets/img/star-on-big.png">';
}
if ($halfStart > 0) {
    $starHtml .= '<img src="assets/img/star-half-big.png">';
}
for ($d = 0; $d < $offStart; $d++) {
    $starHtml .= '<img src="assets/img/star-off-big.png">';
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <?php if ($PUBSUB_TECHNIQUE == "None") { ?>
            <meta http-equiv="refresh" content="<?= $FETCH_TRIP_STATUS_MAX_TIME_INTERVAL; ?>" >
        <?php } ?>
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_TRIP_TRACKING']; ?></title>
        <?php include_once("top/top_script.php"); ?>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <!--<script src="http://cdn.pubnub.com/pubnub.min.js"></script>-->
        <style type="text/css">
            .Msgbox{
                width:90%;margin: 0 auto;text-align: center;
            }
            .marker {
                transform: rotate(-180deg);
            }
        </style>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/top_script.php"); ?>
            <!--<link href="assets/css/checkbox.css" rel="stylesheet" type="text/css" />-->
            <!--<link href="assets/css/radio.css" rel="stylesheet" type="text/css" />-->
            <?php
            include_once("top/validation.php");
            include_once("top/header_topbar.php");
            ?>
            <!--<link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />-->
            <!-- End: Top Menu-->
            <!-- contact page-->
            <section class="profile-section">
               <div class="profile-section-inner">
                <div class="profile-caption _MB0_">
                    <div class="page-heading">
                        <h1>
                            <?php if ($riderName == "") { ?>
                                <?= $langage_lbl['LBL_RIDER']; ?> <?= $langage_lbl['LBL_NOT_FOUND']; ?>
                            <?php } else {
                                ?>
                                <?= $riderName; ?>'s <?= $langage_lbl['LBL_TRIP_TXT']; ?>
                            <?php } if (isset($db_dtrip[0]['iActive']) && $db_dtrip[0]['iActive'] == 'Active' || isset($db_dtrip[0]['iActive']) && ($db_dtrip[0]['iActive'] == 'On Going Trip' || $db_dtrip[0]['iActive'] == 'Arrived')) { ?>
                                <!--<font class="trip-start"><?= $langage_lbl['LBL_MY_ONGOING_TRIPS_HEADER_TXT']; ?></font>-->
                            <?php } ?>
                        </h1>
                    </div>
                </div>
               </div>
            </section>
            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <div class="left-block">
                    <?php if (isset($db_dtrip[0]['iActive']) && $db_dtrip[0]['iActive'] == 'Active' || isset($db_dtrip[0]['iActive']) && ($db_dtrip[0]['iActive'] == 'On Going Trip' || $db_dtrip[0]['iActive'] == 'Arrived')) { ?>
                        <div class="map-page _MB0_" style="display:none;" id="invoice_map">
                            <!--<div class="panel-heading location-heading">-->
                            <!--    <i class="icon-map-marker"></i>-->
                            <!--    <?= $langage_lbl['LBL_LOCATIONS_TXT']; ?>-->
                            <!--</div>-->
                            <div class="gmap-div gmap-div1"><div id="map-canvas" class="gmap3 google-map map-canvasnew" style="height:300px;"></div></div>   
                        </div>
                     </div>
                    <div class="left-right">
                    <div class="inv-destination-data flex-start">
                        
                        <?php if($db_dtrip[0]['eType'] != "UberX") { ?>
                            <div class="vehicle-capt">
                                <div class="vehicle-avatar">
                                    <?= $langage_lbl['LBL_DELIVER_DETAILS']; ?>
                                    <!--<img src="assets/img/car-img.png">-->
                                </div>
                                <div class="car-det">
                                    <strong><?= $vehicle_modal; ?></strong>
                                    <b><?= $vehicle_number; ?></b>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="profile-data">
                            <div class="profile-image">
                                <a href="javascript:void(0);">
                                    <img src="<?= $driver_avatar; ?>" title="<?= $driverName; ?>" alt="<?= $driverName; ?>" onerror="this.src = '<?= $driver_avatar_alt ?>'">
                                </a>
                            </div>
                            <ul class="value-listing">
                                <ul>
                                    <li><span><?= $driverName; ?></span></li>
                                    <li><a href="javascript:void(0);"><?= $starHtml; ?></a></li>
                                    <li><span><?= $phone; ?></span></li>
                                </ul>
                            </ul>
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
                                <?= $langage_lbl['LBL_TRIP_IS_CANCELLED']; ?>.
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
                    </div>
                </div>

            </section>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <?php include_once('top/footer_script.php'); ?>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/gmap3.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/jquery_easing.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js"></script>
        <script src="assets/js/modal_alert.js"></script>
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
                directionsService2 = new google.maps.DirectionsService();
                directionsDisplay2 = new google.maps.DirectionsRenderer();
                

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_getdirver_detail.php',
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
                            myOptions = {
                                zoom: 16,
                                center: latlng,
                            }
                            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
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
                                    url: 'webimages/upload/mapmarker/source_marker.png',
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

            interval3 = setInterval(function () {

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_getdirver_detail.php',
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

                        } else {
                            $('.map-page').hide();
                            clearInterval(interval3);
                            show_alert("<?= addslashes($langage_lbl_admin['LBL_ATTENTION']); ?>", "<?= addslashes($langage_lbl_admin['LBL_TRIP_IS_FINISHED']) ?>","","","<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']) ?>",undefined,true,true,true);
                            //alert('No Online Vehicle');
                        }
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }, 30000);


        var channel = 'ONLINE_DRIVER_LOC_<?php echo $iDriverId; ?>';

        SOCKET_OBJ.subscribe(channel, function (response) {
            //var response = JSON.parse(data);
            handleResponse(response);
        });
        </script>        
      </body>
    <!-- END BODY-->
</html>

