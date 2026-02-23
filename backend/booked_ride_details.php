<?php
include_once('common.php');
$script = "BookedRide";
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$eUserType = $_SESSION['sess_user'];
$iBookingId = $_REQUEST['iBookingId'] = base64_decode(base64_decode(trim($_REQUEST['iBookingId'])));
if ($iBookingId != "") {
    $RIDE_SHARE_OBJ->WebCommonParam();
    $PublishedRides_DATA = $RIDE_SHARE_OBJ->fetchBookings();
    $rideData = [];
    if (isset($PublishedRides_DATA['message']) && !empty($PublishedRides_DATA['message'])) {
        $rideData = $PublishedRides_DATA['message'][0];

        $latitudes[] = $rideData['tStartLat'];
        $longitudes[] = $rideData['tStartLong'];
        $j_waypoints = [];
        if (isset($rideData['waypoints']) && !empty($rideData['waypoints'])) {
            $w = 1;
            foreach ($rideData['waypoints'] as $waypoints) {
                $latitudes[] = $waypoints['lat'];
                $longitudes[] = $waypoints['long'];
                $waypoint_temp['lat'] = $waypoints['lat'];
                $waypoint_temp['lng'] = $waypoints['long'];
                $j_waypoints[] = $waypoint_temp;
            }
        }
        $latitudes[] = $rideData['tEndLat'];
        $longitudes[] = $rideData['tEndLong'];
        $j_source['lat'] = $rideData['tStartLat'];
        $j_source['lng'] = $rideData['tStartLong'];
        $j_end['lat'] = $rideData['tEndLat'];
        $j_end['lng'] = $rideData['tEndLong'];

        if(isset($rideData['rating'])){
            $rating_width = ($rideData['rating'] * 100) / 5;
        } else {
            $rating_width = 0;
        }
    }
    if (scount($rideData) == 0) {
        header('Location:PublishedRide');
    }
} else {
    header('Location:PublishedRide');
}
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?></title>
    <meta name="keywords" value=""/>
    <meta name="description" value=""/>
    <?php include_once("top/top_script.php"); ?>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&libraries=geometry&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js"></script>

    <style type="text/css">
        #map-canvas ul[role="menu"],
        #map-canvas li[role="menuitemcheckbox"] {
            width: auto;
        }

        #map-canvas li[role="menuitemcheckbox"] {
            margin-bottom: 0;
        }

        .rideshare_userDetails {
            min-height: auto !important;
            display: block;
            margin-bottom: 20px;
        }

        .user-details-info {
            display: flex;
        }

        .user-image {
            width: 15%;
        }

        .documentbtn {
            width: 100%;
            justify-content: center;
            margin-bottom: 20px;
            background-color: white;
            border: 1px solid black;
            color: black;
        }

        .left-right .rideshare_userDetails ul.sub-block-paragraph b {
            font-size: 14px;
            color: #808080;
        }
    </style>
</head>

<body id="wrapper">
<!-- home page -->
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- First Section -->
    <?php include_once("top/header.php"); ?>
    <!-- End: First Section -->
    <section class="profile-section">
        <div class="profile-section-inner">
            <div class="profile-caption _MB0_">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_Invoice']; ?></h1>
                </div>
                <ul class="overview-detail">
                    <li>
                        <div class="overview-data">
                            <strong><?= $langage_lbl['LBL_RIDE_SHARE_PUBLISH_NO']; ?></strong>
                            <span><?= !empty($rideData['vBookingNo']) ? "#" . $rideData['vBookingNo'] : "&nbsp;"; ?></span>
                        </div>
                    </li>
                    <li>

                        <div class="overview-data <? echo $subclass; ?> ">
                            <strong><?= $langage_lbl['LBL_VEHICLE_TITLE']; ?></strong>
                            <span>
                                <?php
                                if (isset($rideData['carDetails']['cModel']) && !empty($rideData['carDetails']['cModel'])) {
                                    echo $rideData['carDetails']['cModel'];
                                }
                                ?>
                            </span>
                        </div>
                    </li>

                    <li>
                        <div class="overview-data">
                            <strong><?= $langage_lbl['LBL_Trip_time']; ?></strong>
                            <span><?= !empty($rideData['StartDate']) ? $rideData['StartDate'] : "&nbsp;"; ?></span>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="left-block">

                <div class="trip-detail-map" id="invoice_map">
                    <div id="map-canvas" class="gmap3" style="width:100%;height:300px;margin-bottom:10px;"></div>
                </div>

                <?php if (isset($rideData['PriceBreakdown']) && !empty($rideData['PriceBreakdown'])) { ?>

                    <div class="invoice-data-holder rideshare_userDetails add-rating">

                        <!--  -->
                        <div class="Payment-detailss">
                            <div class="Payment-mode">
                                <strong class="sub-block-title"><?php echo $langage_lbl['LBL_PYMENT_DETAILS']; ?></strong>
                                <span> <?php echo $rideData['PaymentModeTitle'] . ' ' . $rideData['PaymentModeLabel']; ?> </span>
                            </div>
                            <div class="inv-data">
                                <ul class="Payment-Details">
                                    <?php
                                    ?>
                                    <?php
                                    foreach ($rideData['PriceBreakdown'] as $BookingList) {
                                        foreach ($BookingList as $key => $Breakdown) { ?>

                                            <?php if ($key == "eDisplaySeperator") { ?>
                                                <tr>
                                                    <td colspan="2">
                                                        <hr style="margin-bottom:0px">
                                                    </td>
                                                </tr>
                                            <?php } else { ?>
                                                <li>
                                                    <b class="total-Price-text"><?php echo $key; ?></b>
                                                    <p class="total-Price"><?php echo $Breakdown; ?></p>
                                                </li>
                                            <?php }
                                        }
                                    }
                                    ?>

                                </ul>
                            </div>
                        </div>
                        <!--  -->


                        <?php if($rating_width > 0){ ?>
                        <div class="inv-rating"><strong>TRIP RATING:</strong>
                            <?php $TripRating = '<span class="rating_img" style="display: block; width: 68px; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);">
									<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);"></span>
									</span>'; ?>
                            <?= $TripRating; ?>
                        </div>

                        <?php  } ?>
                    </div>
                    <?php
                } else { ?>
                    <div> <?php echo $langage_lbl['LBL_RIDE_SHARE_NO_REQUESTS_MSG']; ?> </div>
                <?php } ?>

            </div>
            <div class="left-right">

                <div class="inv-destination-data rideshare_userDetails">
                    <div>

                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_ROUTE_DETAILS']; ?></strong>
                        <ul class="sub-block-paragraph">
                            <li><i>A<?php echo isset($rideData['SourceLocationPoint']) ? $rideData['SourceLocationPoint'] : ''; ?></i>
                                <div class="location-point">
                                    <strong><?php echo $langage_lbl['LBL_RIDE_SHARE_DETAILS_START_LOC_TXT']; ?></strong>
                                    <p><?php echo $rideData['StartTime']; ?></p>
                                </div>

                                <b><?php echo $rideData['tStartLocation']; ?></b>
                            </li>
                            <?php
                            if (isset($rideData['waypoints']) && !empty($rideData['waypoints'])) {
                                foreach ($rideData['waypoints'] as $waypoints) { ?>
                                    <li>
                                        <i><?php echo $waypoints['letterPoint']; ?></i>
                                        <b><?php echo $waypoints['address']; ?></b>

                                        <br>
                                        <p><?php echo $waypoints['WPDate']; ?></p>
                                    </li>
                                <?php }
                            }
                            ?>
                            <li>
                                <i>B<?php echo isset($rideData['DestLocationPoint']) ? $rideData['DestLocationPoint'] : ''; ?></i>
                                <div class="location-point">
                                    <strong><?php echo $langage_lbl['LBL_RIDE_SHARE_DETAILS_END_LOC_TXT']; ?></strong>
                                    <p><?php echo $rideData['EndTime']; ?></p>
                                </div>

                                <b><?php echo $rideData['tEndLocation']; ?></b>
                            </li>
                        </ul>

                    </div>
                </div>
                <div class="inv-destination-data rideshare_userDetails">
                    <div>

                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_CAR_DETAILS_TITLE']; ?></strong>
                        <div class="driver_info">
                            <?php if (isset($rideData['carDetails']) && !empty($rideData['carDetails'])) {
                                $carDetails = $rideData['carDetails'];
                                ?>
                                <div class="driver_info-img">

                                    <?php if (isset($carDetails['cImage']) && !empty($carDetails['cImage'])) { ?>
                                        <img style="width: 55px" src="<?php echo $carDetails['cImage']; ?>">
                                    <?php } else { ?>

                                    <?php } ?>
                                </div>
                                <ul class="car-details">

                                    <li>
                                        <b><?php echo $carDetails['cMake']; ?></b>

                                    </li>
                                    <li>
                                        <b><?php echo $carDetails['cModel']; ?></b>

                                    </li>
                                    <li>
                                        <b><?php echo $carDetails['cNumberPlate']; ?></b>
                                    </li>

                                </ul>
                                <?php
                            }
                            ?>
                        </div>

                    </div>
                </div>

                <div class="inv-destination-data rideshare_userDetails">
                    <div>

                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_DRIVER_DETAILS_TITLE']; ?></strong>

                        <div class="driver_deatils">
                            <div class="driver_info">
                                <div class="driver_info-img">

                                    <?php if (isset($rideData['DriverImg']) && !empty($rideData['DriverImg'])) { ?>
                                        <img style="width: 55px" src="<?php echo $rideData['DriverImg']; ?>">
                                    <?php } else { ?>
                                        <img style="width: 55px" src="assets/img/profile-user-img.png" alt="">
                                    <?php } ?>
                                </div>
                                <div class="">
                                    <p><?php echo $rideData['DriverName']; ?></p>
                                    <p><?php echo $rideData['DriverPhone']; ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="inv-destination-data rideshare_userDetails">
                    <div>

                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_ADDITIONAL_NOTES_TXT']; ?></strong>

                        <div class="additional_details sdfsdfsfsadfasdf">
                            <?php echo $carDetails['cNote']; ?>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
</div>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<?php
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
?>
<?php if ($lang != 'en') { ?>
    <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
    <? include_once('otherlang_validation.php'); ?>
<?php } ?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<!-- home page end-->
<div class="custom-modal-main" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="custom-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="upload-content">
                    <div class="model-header">
                        <h4 id="servicetitle">
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                            Service Details
                            <!-- <button type="button" class="close" data-dismiss="modal">x</button> -->
                        </h4>
                        <i class="icon-close" data-dismiss="modal"></i>
                    </div>
                    <div class="model-body" style="max-height: 450px;overflow: auto;">
                        <div id="service_detail"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer Script -->
<?php //include_once('top/footer_script.php');
?>
<script src="assets/js/gmap3.js"></script>
<script type="text/javascript">

    var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';
    h = window.innerHeight;
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    var arr1 = [];
    var lats = [];
    var longs = [];
    var markers = [];
    var map;
    var tPlatitudes = '<?= json_encode($latitudes) ?>';
    lats = JSON.parse(tPlatitudes);
    var tPlongitudes = '<?= json_encode($longitudes) ?>';
    longs = JSON.parse(tPlongitudes);
    var source = '<?= json_encode($j_source) ?>';
    source = JSON.parse(source);
    var end = '<?= json_encode($j_end) ?>';
    endd = JSON.parse(end);
    var waypoints = '<?= json_encode($j_waypoints) ?>';
    waypoints = JSON.parse(waypoints);

    function drawCurvedLine(map) {
        var path = [source];
        for (var i = 0; i < waypoints.length; i++) {
            path.push(waypoints[i]);
        }
        path.push(endd);
        console.log(path);
        var curvedPath = generateCurvedPath(path, 0.01);
        var polyline = new google.maps.Polyline({
            path: curvedPath,
            strokeOpacity: 0,
            strokeWeight: 2,
            icons: [{
                icon: {
                    path: 'M 0,-1 0,1',
                    strokeOpacity: 1,
                },
                offset: '0',
                repeat: '10px'
            }],
        });
        polyline.setMap(map);
    }

    function getCurvedPath1(path, curvature) {
        var curvedPath = [];
        for (var i = 0; i < path.length - 1; i++) {
            var startPoint = path[i];
            var endPoint = path[i + 1];
            var numPoints = 1000;
            for (var j = 0; j < numPoints; j++) {
                var t = j / numPoints;
                var lat = (1 - t) * startPoint.lat + t * endPoint.lat;
                var lng = (1 - t) * startPoint.lng + t * endPoint.lng;
                curvedPath.push({lat: lat, lng: lng});
            }
        }
        return curvedPath;
    }

    function generateCurvedPath(waypoints, curvature) {
        var curvedPath = [];
        for (var i = 0; i < waypoints.length - 1; i++) {
            var startPoint = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lng);
            var endPoint = new google.maps.LatLng(waypoints[i + 1].lat, waypoints[i + 1].lng);
            for (var t = 0; t <= 1; t += curvature) {
                var lat = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t).lat();
                var lng = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t).lng();
                curvedPath.push({lat: lat, lng: lng});
            }
        }
        return curvedPath;
    }

    function initialize() {
        var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
        var mapOptions = {
            zoom: 4,
            center: thePoint
        };
        map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);
        //drawCurvedLine(map);
        from_to_polyline();
    }

    var pts = [];
    var middle_multi = []
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < lats.length; i++) {
        var latlongs = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        // pts.push(latlongs);
        var point = latlongs;
        bounds.extend(point);
        if (i == 0) {
            var start = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        } else if (i == lats.length - 1) {
            var end = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        } else {
            var WayPointArr = [lats[i], longs[i]];
            middle_multi.push(WayPointArr);
            var middle = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
        }
    }
    console.log(middle_multi);
    var directionsService = new google.maps.DirectionsService();
    var directionsOptions = { // For Polyline Route line options on map
        polylineOptions: {
            path: pts,
            strokeColor: '#f35e2f',
            strokeOpacity: 3.0,
            strokeWeight: 6
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
    <?php if (!empty($iBookingId)) { ?>
    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);
    });
    <?php } ?>

    function from_to_polyline() {
        DeleteMarkers('from_loc');
        DeleteMarkers('to_loc');
        IconUrl = '../webimages/upload/mapmarker/startmarker.png';
        setMarker(start,0 ,IconUrl);
        IconUrl = '../webimages/upload/mapmarker/endmarker.png';
        setMarker(end,1,IconUrl);
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

   /* function setMarker(postitions, valIcon) {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = 'webimages/upload/mapmarker/PinFrom.png';
        } else if (valIcon == 'to_loc') {
            newIcon = 'webimages/upload/mapmarker/PinTo.png';
        } else {
            newIcon = 'webimages/upload/mapmarker/PinTo.png';
        }
        marker = new google.maps.Marker({
            map: map,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon
        });
        marker.id = valIcon;
        markers.push(marker);
    }*/

    function getLabelFromNumber(number) {
        var label = '';
        while (number >= 0) {
            label = String.fromCharCode(65 + (number % 26)) + label;
            number = Math.floor(number / 26) - 1;
        }
        return label;
    }

    function setMarker(postitions, valIcon ,IconUrl = '') {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = '../webimages/upload/mapmarker/PinFrom.png';
        } else if (valIcon == 'to_loc') {
            newIcon = '../webimages/upload/mapmarker/PinTo.png';
        } else {
            //newIcon = '../webimages/upload/mapmarker/Pin-middle.png';
            newIcon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' + (valIcon + 10).toString(36) + '|FF0000|000000';
        }
        var valIcon_txt = getLabelFromNumber(valIcon);
        var newIcon1 = {
            url: IconUrl, // URL to your custom icon image
            scaledSize: new google.maps.Size(60, 60), // Size of the icon
            labelOrigin: new google.maps.Point(30, 15) // Anchor point for the label
        };
        marker = new google.maps.Marker({
            map: map,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon1,
            label: {
                text: valIcon_txt,
                color: 'black',
            }
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

</script>
<!-- End: Footer Script -->
</body>

</html>