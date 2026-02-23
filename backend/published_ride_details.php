<?php
include_once('common.php');
$script = "rideShare";
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
$eUserType = $_SESSION['sess_user'];
$iPublishedRideId = $_REQUEST['iPublishedRideId'] = base64_decode(base64_decode(trim($_REQUEST['iPublishedRideId'])));
if ($iPublishedRideId != ""){
    $RIDE_SHARE_OBJ->WebCommonParam();
    $PublishedRides_DATA = $RIDE_SHARE_OBJ->fetchPublishedRides();
    $rideData = [];
    if (isset($PublishedRides_DATA['message']) && !empty($PublishedRides_DATA['message'])){
        $rideData = $PublishedRides_DATA['message'][0];
        $latitudes[] = $rideData['tStartLat'];
        $longitudes[] = $rideData['tStartLong'];
        $j_waypoints = [];
        if (isset($rideData['waypoints']) && !empty($rideData['waypoints'])){
            $w = 1;
            foreach ($rideData['waypoints'] as $waypoints){
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
    }
    if (scount($rideData) == 0){
        header('Location:PublishedRide');
    }
}else{
    header('Location:PublishedRide');
}
?>
<!DOCTYPE html>
<html lang="en"
        dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_RIDESHARE_PUBLISH_RIDE_TXT'];?></title>
    <meta name="keywords" value=""/>
    <meta name="description" value=""/>
    <?php include_once("top/top_script.php"); ?>

    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&libraries=geometry&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>"></script>
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
            margin-bottom: 20px !important;
        }

        .user-image {
            width: 15%;
        }

        .user-image img {
            border-radius: 4px;
        }

        .documentbtn {
            width: 100%;
            justify-content: start;
            background-color: white;
            border: 1px solid #000;
            color: black;
        }

        button.btn.gen-btn.documentbtn:hover {
            background-color: #007AFF;
            border: 1px solid #007AFF;
            color: #fff;
        }

        .upload-block {
            width: 31%;
            margin-right: 15px;
            margin-bottom: 15px;
        }

        .info-buttons div:last-child {
            margin-right: 0;
        }

        .info-buttons {
            display: flex;
            width: 100%;
            flex-wrap: wrap;
        }

        .left-right .rideshare_userDetails b {
            font-size: 14px;
            color: #808080;
        }


        .gen-btn:focus {
            background-color: transparent;
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
                    <h1><?=$langage_lbl['LBL_RIDESHARE_PUBLISH_RIDE_TXT'];?></h1>
                </div>
                <ul class="overview-detail">
                    <li>
                        <div class="overview-data">
                            <strong><?=$langage_lbl['LBL_RIDE_SHARE_PUBLISH_NO'];?></strong>
                            <span><?=!empty($rideData['vPublishedRideNo'])?"#".$rideData['vPublishedRideNo']:"&nbsp;";?></span>
                        </div>
                    </li>
                    <li>

                        <div class="overview-data <?php echo $subclass; ?> ">
                            <strong><?=$langage_lbl['LBL_VEHICLE_TITLE'];?></strong>
                            <span>
                                <?php
                                if (isset($rideData['carDetails']['cModel']) && !empty($rideData['carDetails']['cModel'])){
                                    echo $rideData['carDetails']['cModel'];
                                }
                                ?>
                            </span>
                        </div>
                    </li>

                    <li>
                        <div class="overview-data">
                            <strong><?=$langage_lbl['LBL_Trip_time'];?></strong>
                            <span><?=!empty($rideData['StartDate'])?$rideData['StartDate']:"&nbsp;";?></span>
                        </div>
                    </li>
                    <li>
                        <div class="overview-data">
                            <strong><?=$langage_lbl['LBL_RIDE_SHARE_DURATION'];?> Duration </strong>
                            <span><?=!empty($rideData['fDuration'])?$rideData['fDuration']:"&nbsp;";?></span>
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

                <?php if (isset($rideData['tDocumentIds']) && !empty($rideData['tDocumentIds'])){ ?>
                    <div class="invoice-data-holder rideshare_userDetails">
                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_DOCUMET']; ?></strong>

                        <div class="info-buttons">
                            <?php foreach ($rideData['tDocumentIds'] as $tDocumentIds){
                                ?>
                                <!-- ohter-button-start -->
                                <div class="upload-block">
                                    <input type="hidden" id="ex_status" value="yes">
                                    <strong><?php echo $tDocumentIds['doc_name'] ?></strong>
                                    <input type="hidden" id="doc_id" value="">

                                    <?php if ($tDocumentIds['doc_file']){ ?>
                                        <div class="doc-image-block">
                                            <a href="<?php echo $tDocumentIds['doc_file'] ?>" target="_blank">
                                                <img src="<?php echo $tDocumentIds['doc_file'] ?>" style="cursor:pointer;"
                                                        alt="Driving License Image">
                                            </a>

                                        </div>
                                        <div class="button-block">
                                            <button attr-docFile="<?php echo $tDocumentIds['doc_file'] ?>"
                                                    onclick='return !window.open("<?php echo $tDocumentIds['doc_file'] ?>", "_blank")'
                                                    class="btn gen-btn documentbtn "><?php echo $langage_lbl['LBL_VIEW']; ?></button>
                                        </div>
                                    <?php }else{ ?>
                                        <div class="doc-image-block">
                                            <p><?php echo $langage_lbl['LBL_NOT_FOUND']; ?></p>
                                        </div>
                                    <?php } ?>

                                </div>
                                <!-- ohter-button-end -->
                            <?php } ?>
                        </div>

                    </div> <?php
                } ?>

                <!-- invoice-data-holder rideshare_userDetails-start -->
                <!-- invoice-data-holder rideshare_userDetails-end -->

                <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_PASSENGER_DETAILS']; ?></strong>
                <?php if (isset($rideData['BookingList']) && !empty($rideData['BookingList'])){ ?>
                    <?php
                    $i = 0;
                    foreach ($rideData['BookingList'] as $BookingList){
                        ?>
                        <div class="invoice-data-holder rideshare_userDetails">
                            <div>
                                <div class="user-main-card">
                                    <div class="user-details-info">
                                        <div class="user-image">
                                            <?php if (isset($BookingList['rider_ProfileImg']) && !empty($BookingList['rider_ProfileImg'])){
                                                $user_img = $BookingList['rider_ProfileImg'];
                                            }else{
                                                $user_img = "assets/img/profile-user-img.png";
                                            } ?>
                                            <img style="width: 100px" src="<?php echo $user_img; ?>" alt="">
                                        </div>
                                        <div>
                                            <p class="name"><?php echo $BookingList['rider_Name']; ?></p>
                                            <p class="payment Mode"><?php echo $BookingList['PaymentModeTitle']; ?>
                                                : <?php echo $BookingList['PaymentModeLabel']; ?> </p>
                                            <p class="message"><?php echo $BookingList['PaymentLabel']; ?></p>
                                            <p class="message"><?php echo $BookingList['tLocation']; ?></p>

                                            <?php if ($BookingList['IS_RATING_SHOW'] == "Yes" && $BookingList['rating'] > 0){ ?>
                                                <img src="assets/img/star.jpg"> <?php echo $BookingList['rating']; ?>

                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="user-booking-details">
                                        <div class="passenger">
                                            <p class="total-passenger"><?php echo $BookingList['BookedSeatsTxt']; ?></p>
                                            <p class="total-Price"><?php echo $BookingList['TotalFare']; ?>
                                                <a data-target="#Booking_Amount_<?=$i;?>" data-toggle="modal" class="MainNavText" id="MainNavHelp"><i class="ri-information-line"></i></a>
                                            </p>
                                            <!------------------Fare Breakdown----------------->
                                            <div class="custom-modal-main" id="Booking_Amount_<?=$i;?>">
                                                <div class="custom-modal">
                                                    <div class="model-header">
                                                        <h4><?php echo $langage_lbl['LBL_FARE_BREAKDOWN']; ?></h4>
                                                        <i class="icon-close" data-dismiss="modal"></i>
                                                    </div>
                                                    <div class="model-body">
                                                        <div class="ps_details">
                                                            <div class="ps_details_box">
                                                                <ul class="ps_details_list">

                                                                    <?php
                                                                    foreach ($BookingList['PriceBreakdown'] as $PriceBreakdown){
                                                                        foreach ($PriceBreakdown as $key => $Breakdown){ ?>

                                                                            <?php if ($key == "eDisplaySeperator"){ ?>

                                                                                <li class="line">

                                                                                </li>
                                                                            <?php }else{ ?>
                                                                                <li>
                                                                                    <strong><?php echo $key ?></strong>
                                                                                    <label><?php echo $Breakdown ?></label>
                                                                                </li>
                                                                            <?php }
                                                                        }
                                                                    }
                                                                    $i++;
                                                                    ?>
                                                                </ul>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="model-footer">
                                                        <div class="button-block">
                                                            <button type="button" class="gen-btn" data-dismiss="modal"><?=$langage_lbl['LBL_CLOSE_TXT'];?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!------------------Fare Breakdown----------------->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                }else{ ?>
                    <div> <?php echo $langage_lbl['LBL_RIDE_SHARE_NO_REQUESTS_MSG']; ?> </div>
                <?php } ?>

            </div>
            <div class="left-right">
                <div class="inv-destination-data rideshare_userDetails ridesharelocations">
                    <div>

                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_ROUTE_DETAILS']; ?></strong>
                        <ul>
                            <li>

                                <?php if (isset($rideData['SourceLocationPoint']) && !empty($rideData['SourceLocationPoint'])){ ?>
                                    <i><?php echo $rideData['SourceLocationPoint']; ?></i>

                                <?php } ?>

                                <div class="location-point">
                                    <strong><?php echo $langage_lbl['LBL_RIDE_SHARE_DETAILS_START_LOC_TXT']; ?></strong>
                                    <p><?php echo $rideData['StartTime']; ?></p>
                                    <b><?php echo $rideData['tStartLocation']; ?></b>
                                </div>

                            </li>
                            <?php
                            if (isset($rideData['waypoints']) && !empty($rideData['waypoints'])){
                                foreach ($rideData['waypoints'] as $waypoints){ ?>
                                    <li>

                                        <i><?php echo $waypoints['letterPoint']; ?></i>
                                        <div class="location-point">
                                            <b><?php echo $waypoints['address']; ?></b>
                                            <p><?php echo $waypoints['WPDate']; ?></p>
                                        </div>
                                        <!-- <b><?php echo $waypoints['address']; ?></b> -->

                                    </li>
                                <?php }
                            }
                            ?>
                            <li>
                                <?php if (isset($rideData['SourceLocationPoint']) && !empty($rideData['SourceLocationPoint'])){ ?>
                                    <i><?php echo $rideData['DestLocationPoint']; ?></i>
                                <?php } ?>
                                <div class="location-point">
                                    <strong><?php echo $langage_lbl['LBL_RIDE_SHARE_DETAILS_END_LOC_TXT']; ?></strong>
                                    <p><?php echo $rideData['EndTime']; ?></p>
                                    <b><?php echo $rideData['tEndLocation']; ?></b>
                                </div>

                            </li>
                        </ul>

                    </div>
                </div>
                <?php
                if (isset($rideData['waypointFare']) && !empty($rideData['waypointFare'])){
                    ?>

                    <div class="inv-destination-data rideshare_userDetails">
                        <div>

                            <strong class="sub-block-title"><?php echo $langage_lbl['LBL_EDIT_STOP_OVER_POINT_PRICE_RIDE_SHARE_TEXT']; ?></strong>
                            <div class="Stop-Over-Point-Price">
                                <ul>
                                    <?php
                                    if (isset($rideData['waypointFare']) && !empty($rideData['waypointFare'])){
                                        foreach ($rideData['waypointFare'] as $key => $waypoints){
                                            foreach ($waypoints as $location => $price){
                                                ?>

                                                <li>
                                                    <b><?php echo $location; ?></b>
                                                    <b><?php echo $price; ?></b>
                                                </li>
                                            <?php }
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>

                        </div>
                    </div>
                <?php } ?>

                <div class="inv-destination-data rideshare_userDetails">
                    <div>

                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_RIDE_SHARE_CAR_DETAILS_TITLE']; ?></strong>
                        <div class="driver_info">
                            <?php if (isset($rideData['carDetails']) && !empty($rideData['carDetails'])){
                                $carDetails = $rideData['carDetails'];
                                ?>
                                <div class="driver_info-img">

                                    <?php if (isset($carDetails['cImage']) && !empty($carDetails['cImage'])){ ?>
                                        <img style="width: 55px" src="<?php echo $carDetails['cImage']; ?>">
                                    <?php }else{ ?>

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
<?php if ($lang != 'en'){ ?>
    <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?=$lang;?>.js" ></script> -->
    <?php include_once('otherlang_validation.php'); ?>
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
    const lineSymbol = {
        path: "M 0,-1 0,1",
        strokeOpacity: 1,
        scale: 4,
    };
    var typeArr = '<?= json_encode($vehilceTypeArr,JSON_HEX_APOS); ?>';
    h = window.innerHeight;
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    // var waypts = [];
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

    /*function drawCurve(P1, P2, map) {
        var lineLength = google.maps.geometry.spherical.computeDistanceBetween(P1, P2);
        var lineHeading = google.maps.geometry.spherical.computeHeading(P1, P2);
        if (lineHeading < 0) {
            var lineHeading1 = lineHeading + 45;
            var lineHeading2 = lineHeading + 135;
        } else {
            var lineHeading1 = lineHeading + -45;
            var lineHeading2 = lineHeading + -135;
        }
        var pA = google.maps.geometry.spherical.computeOffset(P1, lineLength / 2.2, lineHeading1);
        var pB = google.maps.geometry.spherical.computeOffset(P2, lineLength / 2.2, lineHeading2);

        var curvedLine = new GmapsCubicBezier(P1, pA, pB, P2, 0.01, map);
    }

    var GmapsCubicBezier = function(latlong1, latlong2, latlong3, latlong4, resolution, map) {
        var lat1 = latlong1.lat();
        var long1 = latlong1.lng();
        var lat2 = latlong2.lat();
        var long2 = latlong2.lng();
        var lat3 = latlong3.lat();
        var long3 = latlong3.lng();
        var lat4 = latlong4.lat();
        var long4 = latlong4.lng();

        var points = [];

        for (it = 0; it <= 1; it += resolution) {
            points.push(this.getBezier({
                x: lat1,
                y: long1
            }, {
                x: lat2,
                y: long2
            }, {
                x: lat3,
                y: long3
            }, {
                x: lat4,
                y: long4
            }, it));
        }
        var path = [];
        for (var i = 0; i < points.length - 1; i++) {
            path.push(new google.maps.LatLng(points[i].x, points[i].y));
            path.push(new google.maps.LatLng(points[i + 1].x, points[i + 1].y, false));
        }



        var Line = new google.maps.Polyline({
            path: path,
            /!*geodesic: true,
            strokeColor: "##35495e",
            strokeOpacity: 0.8,
            strokeWeight: 3,*!/
            strokeOpacity: 0,
            icons: [{
                icon: {
                    path: 'M 0,-1 0,1',
                    strokeOpacity: 1,
                    scale: 4
                },
                offset: '0',
                repeat: '20px'
            }],
        });

        Line.setMap(map);

        return Line;
    };

    GmapsCubicBezier.prototype = {

        B1: function(t) {
            return t * t * t;
        },
        B2: function(t) {
            return 3 * t * t * (1 - t);
        },
        B3: function(t) {
            return 3 * t * (1 - t) * (1 - t);
        },
        B4: function(t) {
            return (1 - t) * (1 - t) * (1 - t);
        },
        getBezier: function(C1, C2, C3, C4, percent) {
            var pos = {};
            pos.x = C1.x * this.B1(percent) + C2.x * this.B2(percent) + C3.x * this.B3(percent) + C4.x * this.B4(percent);
            pos.y = C1.y * this.B1(percent) + C2.y * this.B2(percent) + C3.y * this.B3(percent) + C4.y * this.B4(percent);
            return pos;
        }
    };*/
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

    function initialize() {//alert('<?= json_encode($latitudes) ?>');
        var thePoint = new google.maps.LatLng('<?php echo $db_trip_data['tStartLat']; ?>', '<?php echo $db_trip_data['tStartLong']; ?>');
        var mapOptions = {
            zoom: 4,
            center: thePoint
        };
        map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);
        var trafalgar = new google.maps.LatLng(lats[0], longs[0]);
        var rcbc = new google.maps.LatLng(lats[1], longs[1]);
        //drawCurve(trafalgar, rcbc, map);
        //drawCurvedLine(map);
        from_to_polyline();
    }

    var tPlatitudes = '<?= json_encode($latitudes) ?>';
    lats = JSON.parse(tPlatitudes);
    var tPlongitudes = '<?= json_encode($longitudes) ?>';
    longs = JSON.parse(tPlongitudes);
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
    /* var directionsService = new google.maps.DirectionsService();
     var directionsOptions = {// For Polyline Route line options on map
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
     }*/
    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);
    });

    function from_to_polyline() {
        DeleteMarkers('from_loc');
        DeleteMarkers('to_loc');
        //setMarker(start, 'from_loc');
        IconUrl = '<?php echo $tconfig["tsite_url"]?>webimages/upload/mapmarker/startmarker.png';
        setMarker(start, 0, IconUrl);
        for (var i = 0; i < middle_multi.length; i++) {
            weyPoints = new google.maps.LatLng(parseFloat(middle_multi[i][0]), parseFloat(middle_multi[i][1]));
            IconUrl = '<?php echo $tconfig["tsite_url"]?>webimages/upload/mapmarker/waypointmarker.png';
            setMarker(weyPoints, i + 1, IconUrl);
        }
        //setMarker(middle, '');
        //setMarker(end, 'to_loc');
        IconUrl = '<?php echo $tconfig["tsite_url"]?>webimages/upload/mapmarker/endmarker.png';
        setMarker(end, (middle_multi.length + 1), IconUrl);
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

    function getLabelFromNumber(number) {
        var label = '';
        while (number >= 0) {
            label = String.fromCharCode(65 + (number % 26)) + label;
            number = Math.floor(number / 26) - 1;
        }
        return label;
    }

    function setMarker(postitions, valIcon, IconUrl = '') {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = '<?php echo $tconfig["tsite_url"]?>webimages/upload/mapmarker/PinFrom.png';
        } else if (valIcon == 'to_loc') {
            newIcon = '<?php echo $tconfig["tsite_url"]?>webimages/upload/mapmarker/PinTo.png';
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

    function showServiceModal(elem) {
        var tripJson = JSON.parse($(elem).attr("data-json"));
        var rideNo = $(elem).attr("data-trip");
        var typeNameArr = JSON.parse(typeArr);
        var serviceHtml = "";
        var srno = 1;
        // added by sunita
        for (var g = 0; g < tripJson.length; g++) {
            serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>" + [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
            srno++;
        }
        $("#service_detail").html(serviceHtml);
        $("#servicetitle").text("Service Details : " + rideNo);
        $("#service_modal").modal('show');
        return false;
    }

    $('.documentbtn').on('click mouseup', function (event) {
        /*var buttonToTrigger = $(this);
        buttonToTrigger.trigger('click');*/
        var docFile = $(this).attr('attr-docFile');
        window.open(docFile, '_blank');
        return false;
    });
</script>
<!-- End: Footer Script -->
</body>

</html>