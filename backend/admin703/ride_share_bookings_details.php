<?php
include_once('../common.php');
$tbl_name = 'trips';
$script = 'RideShareBookings';
$RIDE_SHARE_OBJ->AdminCommonParam();
$PublishedRides_DATA = $RIDE_SHARE_OBJ->fetchBookings();
if (isset($PublishedRides_DATA['message']) && !empty($PublishedRides_DATA['message'])) {
    $Booking_DATA = $PublishedRides_DATA['message'][0];
}

if (file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $Booking_DATA['UseriUserId'] . '/2_' . $Booking_DATA['RiderImgName'])) {
    $rider_img = $tconfig["tsite_upload_images_passenger_path"] . '/' . $Booking_DATA['UseriUserId'] . '/2_' . $Booking_DATA['RiderImgName'];
} else {
    $rider_img = $tconfig["tsite_url"] . "webimages/icons/help/taxi_passanger.png";
}
if (file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $Booking_DATA['iDriverId'] . '/2_' . $Booking_DATA['DriverImg'])) {
    $driver_img = $tconfig["tsite_upload_images_passenger_path"] . '/' . $Booking_DATA['iDriverId'] . '/2_' . $Booking_DATA['DriverImg'];
} else {
    $driver_img = $tconfig["tsite_url"] . "webimages/icons/help/driver.png";
}
$latitudes[] = $Booking_DATA['tStartLat'];
$longitudes[] = $Booking_DATA['tStartLong'];
$latitudes[] = $Booking_DATA['tEndLat'];
$longitudes[] = $Booking_DATA['tEndLong'];
$j_source['lat'] = $Booking_DATA['tStartLat'];
$j_source['lng'] = $Booking_DATA['tStartLong'];
$j_end['lat'] = $Booking_DATA['tEndLat'];
$j_end['lng'] = $Booking_DATA['tEndLong'];
$j_waypoints = [];

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Invoice</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <?php include_once('global_files.php'); ?>
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
            <?php } ?>

            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <b><?php echo $langage_lbl_admin['LBL_RIDE_SHARE_YOUR_RIDE_ADMIN_TEXT']; ?> </b>
                                <?php /*echo @date('h:i A', @strtotime($Booking_DATA['dStartDate'])); ?>
                                on <?= @date('d M Y', @strtotime($Booking_DATA['dStartDate']));*/
                                    echo $Booking_DATA['tDisplayDateTime'];
                                 ?>
                            </div>
                            <div class="panel-body rider-invoice-new">
                                <div class="row">
                                    <div class="col-sm-6 rider-invoice-new-left">
                                       <!--  <h4>Pick up Location </h4> -->
                                        <div id="map-canvas" class="gmap3"
                                             style="width:100%;height:300px;margin-bottom:10px;"></div>
                                        <span class="location-from">
                                            <i class="icon-map-marker"></i>
                                            <b>Pick-up Location</b>
                                            <p><?= $Booking_DATA['tStartLocation']; ?></p>
                                        </span>
                                        <span class="location-to"><i
                                                    class="icon-map-marker"></i> <b> Drop-off Location </b><p><?= $Booking_DATA['tEndLocation']; ?></p></span>

                                        <?php
                                        $class_name = 'col-sm-4';
                                        $style = '';
                                        ?>

                                        <div class="rider-invoice-bottom">
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="left">
                                                        <img src="<?php echo $driver_img; ?>"
                                                             style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                             align="left" height="45" width="45" class="CToWUd">
                                                    </div>
                                                    <div class="right col-sm-9" style="word-wrap: break-word;">
                                                        <div>
                                                            <b><?php echo $langage_lbl_admin['LBL_RIDE_SHARE_PUBLISHED_BY']; ?></b>
                                                        </div>
                                                        <div><?php echo clearName($Booking_DATA['DriverName']); ?></div>
                                                        <div><?php echo clearEmail($Booking_DATA['DriverPhone']); ?></div>
                                                        <br>

                                                        <?php
                                                        if (isset($Booking_DATA['rating']) && !empty($Booking_DATA['rating']) && $Booking_DATA['rating'] != 0) { ?>
                                                            <div><b>Rating</b></div>
                                                            <div>
                                                                <img src="<?php echo $tconfig["tsite_url"]?>assets/img/star.jpg"
                                                                     style="margin: 0 2px 4px 0"> <?php echo $Booking_DATA['rating']; ?>
                                                            </div>

                                                        <?php } ?>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="left">
                                                        <img src="<?php echo $rider_img; ?>"
                                                             style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                             align="left" height="45" width="45" class="CToWUd">
                                                    </div>
                                                    <div class="right col-sm-9" style="word-wrap: break-word;">
                                                        <div>
                                                            <b>Booked By</b>
                                                        </div>
                                                        <div><?php echo clearName($Booking_DATA['RiderName']); ?></div>
                                                        <div><?php echo clearEmail($Booking_DATA['RiderPhone']); ?></div>
                                                        <br>
                                                        <?php
                                                        if (isset($Booking_DATA['ratingFromDriver']) && !empty($Booking_DATA['ratingFromDriver']) && $Booking_DATA['ratingFromDriver'] != 0) { ?>
                                                            <div><b>Rating</b></div>
                                                            <div>
                                                                <img src="<?php echo $tconfig["tsite_url"]?>assets/img/star.jpg"
                                                                     style="margin: 0 2px 4px 0"> <?php echo $Booking_DATA['ratingFromDriver']; ?>
                                                            </div>

                                                        <?php } ?>

                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-sm-6 rider-invoice-new-right">
                                        <h4 style="text-align:center;">Fare Breakdown For Ride Booking No
                                            : <?= $Booking_DATA['vBookingNo']; ?></h4>
                                        <hr/>

                                        <table width="100%"style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                            <tbody>
                                            <?php if (isset($Booking_DATA['PriceBreakdown']) && !empty($Booking_DATA['PriceBreakdown'])) {
                                                foreach ($Booking_DATA['PriceBreakdown'] as $k => $PriceBreakdown) {
                                                    foreach ($PriceBreakdown as $Key => $BreakDown) { ?>

                                                        <?php if ($Key == "eDisplaySeperator") { ?>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <hr style="margin-bottom:0px">
                                                                </td >
                                                            </tr>

                                                        <?php } else if ($k == $Booking_DATA['TotalFareArrIndex']) { ?>
                                                            <tr>
                                                                <td><b><?php echo $Key; ?></b>
                                                                <td align="right"><b><?php echo $BreakDown; ?></b></td>
                                                            </tr>

                                                        <?php } else {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $Key; ?></td>
                                                                <td align="right"><?php echo $BreakDown; ?></td>
                                                            </tr>
                                                        <?php }
                                                    }
                                                }
                                            } ?>
                                            </tbody>
                                        </table>

                                        <?php if(isset($Booking_DATA['BOOKING_FEE_TEXT']) && !empty($Booking_DATA['BOOKING_FEE_TEXT'])){ ?>
                                        <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="2px" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td><b>Booking Fee</b></td>
                                                    <td align="right">
                                                        <b><?php echo $Booking_DATA['BOOKING_FEE_TEXT']; ?></b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <?php } ?>
                                        <br/>
                                         <table style="border:solid 1px #dddddd;" cellpadding="5px" cellspacing="2px" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td><b>Publish Ride No.</b></td>
                                                    <td align="right">
                                                        <a href="<?php echo $tconfig["tsite_url_main_admin"]?>prdetails.php?iPublishedRideId=<?= $Booking_DATA['iPublishedRideId']; ?>" target="_blank" ><?php echo $Booking_DATA['vPublishedRideNo']; ?></a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><b>Booking Status</b></td>
                                                    <td align="right">

                                                        <?php $data_eStatus =  $RIDE_SHARE_OBJ->getDisplayStatusForAdmin($Booking_DATA['eTrackingStatus'] , $Booking_DATA['eStatus'])['status'];
                                                       echo $data_eStatus;
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="clear"></div>

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
<?php include_once('footer.php'); ?>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places,geometry" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>

<script>

    const lineSymbol = {
        path: "M 0,-1 0,1",
        strokeOpacity: 1,
        scale: 4,
    };
    var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';
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
        var curvedPath = generateCurvedPath(path, 0.05);
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
        var curvedDottedPath = [];
        for (var i = 0; i < waypoints.length - 1; i++) {
            var startPoint = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lng);
            var endPoint = new google.maps.LatLng(waypoints[i + 1].lat, waypoints[i + 1].lng);
            for (var t = 0; t <= 1; t += curvature) {
                var lat = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t).lat();
                var lng = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t).lng();
                curvedPath.push({lat: lat, lng: lng});
            }
        }

        var curvedDottedPath = [];

        dotFrequency = 100;
        for (var i = 0; i < waypoints.length - 1; i++) {
            var startPoint = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lng);
            var endPoint = new google.maps.LatLng(waypoints[i + 1].lat, waypoints[i + 1].lng);
            var numDots = Math.ceil(google.maps.geometry.spherical.computeDistanceBetween(startPoint, endPoint) / dotFrequency);

            for (var j = 0; j <= numDots; j++) {
                var t = j / numDots;
                var latLng = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t);

                // Calculate an offset to give the appearance of a curve (adjust the values)
                // Calculate an offset to give the appearance of a curve
                var curvatureFactor = 0.01; // Adjust this value to control the curvature
                var offsetLat = latLng.lat() + curvatureFactor * Math.sin(t * Math.PI);
                var offsetLng = latLng.lng();

                curvedDottedPath.push({ lat: offsetLat, lng: offsetLng });
            }
        }

        return curvedDottedPath;
    }

    function initialize() {//alert('<?= json_encode($latitudes) ?>');
        var thePoint = new google.maps.LatLng('<?php echo $db_trip_data['tStartLat']; ?>', '<?php echo $db_trip_data['tStartLong']; ?>');
        GOOGLE_MAP_OBJ.options.center = thePoint;
        map = GOOGLE_MAP_OBJ.init('map-canvas');

        var trafalgar = new google.maps.LatLng(lats[0], longs[0]);
        var rcbc = new google.maps.LatLng(lats[1], longs[1]);
        //drawCurve(trafalgar, rcbc, map);
        drawCurvedLine(map);
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
        setMarker(start, 'from_loc');
        for (var i = 0; i < middle_multi.length; i++) {
            weyPoints = new google.maps.LatLng(parseFloat(middle_multi[i][0]), parseFloat(middle_multi[i][1]));
            setMarker(weyPoints, i + 1);
        }
        //setMarker(middle, '');
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
            //newIcon = '../webimages/upload/mapmarker/Pin-middle.png';
            newIcon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' + valIcon + '|FF0000|000000';
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



    function generateCurvedDottedLine(map, waypoints, dotFrequency) {
        var curvedDottedPath = [];

        for (var i = 0; i < waypoints.length - 1; i++) {
            var startPoint = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lng);
            var endPoint = new google.maps.LatLng(waypoints[i + 1].lat, waypoints[i + 1].lng);
            var numDots = Math.ceil(google.maps.geometry.spherical.computeDistanceBetween(startPoint, endPoint) / dotFrequency);

            for (var j = 0; j < numDots; j++) {
                var t = j / numDots;
                var lat = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t).lat();
                var lng = google.maps.geometry.spherical.interpolate(startPoint, endPoint, t).lng();
                curvedDottedPath.push({ lat: lat, lng: lng });
            }
        }

        // Create a Polyline with the curved dotted path
        var dottedLine = new google.maps.Polyline({
            path: curvedDottedPath,
            strokeColor: '#0000FF', // Customize the color if needed
            strokeOpacity: 1,
            strokeWeight: 2, // Adjust the line width if needed
            icons: [
                {
                    icon: {
                        path: 'M 0,-1 0,1',
                        scale: 4,
                        strokeOpacity: 1,
                    },
                    offset: '0',
                    repeat: '20px', // Adjust this value to control the dot spacing
                },
            ],
            map: map,
        });

        return dottedLine;
    }

</script>
</body>
<!-- END BODY-->
</html>

