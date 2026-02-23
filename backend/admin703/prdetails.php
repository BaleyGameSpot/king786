<?php
include_once('../common.php');
$tbl_name = 'trips';
$script = 'PublishedRides';

/*if (!$userObj->hasPermission('view-invoice')) {
    $userObj->redirect();
}*/

$CAR_IMAGE_PATH = $tconfig["tsite_upload_images_driver_car_ride_share_path"];
$CAR_IMAGE_URL = $tconfig["tsite_upload_images_driver_car_ride_share"];

$iPublishedRideId = $_REQUEST['iPublishedRideId'];
$_REQUEST['vGeneralLang'] = "EN";
$PublishedRideUser = $RIDE_SHARE_OBJ->RidePaymentSummery();

$sql = "SELECT * FROM published_rides as pr WHERE pr.iPublishedRideId = '" . $iPublishedRideId . "'";
$published_rides_data = $obj->MySQLSelect($sql);
$published_rides_data = $published_rides_data[0];
$carDetails_data = $RIDE_SHARE_OBJ->carDetails($published_rides_data['tDriverDetails']);

/*------------------waypoints-----------------*/
$sql = "SELECT * FROM `published_rides_waypoints` WHERE iPublishedRideId IN ($iPublishedRideId) ";
$waypoints_data = $obj->MySQLSelect($sql);
$RIDE_WAYPOINTS = [];
foreach ($waypoints_data as $w) {
    $RIDE_WAYPOINTS[$w['iPublishedRideId']][] = $w;
}
$RIDE_WAYPOINTS_ARR = [];
if (isset($RIDE_WAYPOINTS) && !empty($RIDE_WAYPOINTS)) {
    foreach ($RIDE_WAYPOINTS as $key => $waypoint) {
        $wayPoints = $RIDE_SHARE_OBJ->wayPointDBToArray($waypoint);
        $RIDE_WAYPOINTS_ARR[$key]['wayPoint'] = $wayPoints['waypoint_data'];
    }
    $RIDE_WAYPOINTS_ARR = $RIDE_WAYPOINTS_ARR[$iPublishedRideId]['wayPoint'];
}
/*------------------waypoints-----------------*/
$sql = "SELECT vEmail,vLastName,vName,vPhone,iUserId,vImgName FROM register_user as pr WHERE pr.iUserId = '" . $published_rides_data['iUserId'] . "'";
$DriverDetailsData = $obj->MySQLSelect($sql)[0];
/*$DriverDetails = get_value('register_user', 'vPhone', 'iUserId', $published_rides_data['iUserId'], '', 'true');*/
if (file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $DriverDetailsData['iUserId'] . '/2_' . $DriverDetailsData['vImgName'])) {
    $img = $tconfig["tsite_upload_images_passenger"] . '/' . $DriverDetailsData['iUserId'] . '/2_' . $DriverDetailsData['vImgName'];
} else {
    $img = $tconfig["tsite_url"] . "webimages/icons/help/driver.png";
}
$latitudes[] = $published_rides_data['tStartLat'];
$longitudes[] = $published_rides_data['tStartLong'];
$j_waypoints = [];
if (isset($RIDE_WAYPOINTS_ARR) && !empty($RIDE_WAYPOINTS_ARR)) {
    $w = 1;
    foreach ($RIDE_WAYPOINTS_ARR as $waypoints) {
        $latitudes[] = $waypoints['lat'];
        $longitudes[] = $waypoints['long'];
        $waypoint_temp['lat'] = $waypoints['lat'];
        $waypoint_temp['lng'] = $waypoints['long'];
        $j_waypoints[] = $waypoint_temp;
    }
}
$latitudes[] = $published_rides_data['tEndLat'];
$longitudes[] = $published_rides_data['tEndLong'];
$j_source['lat'] = $published_rides_data['tStartLat'];
$j_source['lng'] = $published_rides_data['tStartLong'];
$j_end['lat'] = $published_rides_data['tEndLat'];
$j_end['lng'] = $published_rides_data['tEndLong'];


function isValidDate($date) {
    if(isset($date) && !empty($date)){
        list($year,$month,$day) = explode('-',$date);
        return checkdate((int)$month,(int)$day,(int)$year);
    }else{
        return false;
    }
}


/*------------------get Price-----------------*/
$currencyData = FetchDefaultCurrency();
$fRatioCurrency = 1.0000;
$vCurrencyPassenger = $currencyData[0]['vName'];
$fPrice = $published_rides_data['fPrice'];

$waypointData = $RIDE_SHARE_OBJ->getWayPointsFromTheDb($iPublishedRideId, $fRatioCurrency, $vCurrencyPassenger, $fPrice);
/*------------------get Price-----------------*/


$date_format_data_array = array(
    'langCode' => $default_lang,
    'DateFormatForWeb' => 1
);
$date_format_data_array['tdate'] = $published_rides_data['dStartDate'];
$get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

$date_format_data_array['tdate'] = $published_rides_data['dEndDate'];
$get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
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
    <title>Admin | Publish Ride</title>
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
                    <h2>Publish Ride</h2>
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
                                <b>Your <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> </b>
                                <?php
                                if (($published_rides_data['dStartDate'] == "0000-00-00 00:00:00")) {
                                    echo "Was Cancelled.";
                                } else {
                                    /*echo @date('h:i A', @strtotime($published_rides_data['dStartDate']));
                                    ?> on <?= @date('d M Y', @strtotime($published_rides_data['dStartDate']));*/
                                    echo $get_dStartDate_format['tDisplayDateTime'];
                                }
                                ?>
                            </div>
                            <div class="panel-body rider-invoice-new">
                                <div class="row">
                                    <div class="col-sm-6 rider-invoice-new-left">
                                        <!-- <h4>Pick up Location </h4> -->
                                        <div id="map-canvas" class="gmap3"
                                             style="width:100%;height:300px;margin-bottom:10px;"></div>
                                        <span class="location-from"><i class="icon-map-marker"></i>
                                                    <b><?= $get_dStartDate_format['tDisplayTimeAbbr'];//@date('h:i A', strtotime($published_rides_data['dStartDate'])); ?> ( Pick-up Location

                                                         <?php if(isset($waypointData['SourceLocationPoint']) && !empty($waypointData['SourceLocationPoint'])) {
                                                           echo  '- ' .$waypointData['SourceLocationPoint']; }  ?>



                                                        )<p><?= $published_rides_data['tStartLocation']; ?></p></b></span>

                                        <?php if (isset($RIDE_WAYPOINTS_ARR) && !empty($RIDE_WAYPOINTS_ARR)) {
                                            $w = 1;
                                            foreach ($RIDE_WAYPOINTS_ARR as $waypoints) { 
                                                $date_format_data_array['tdate'] = $waypoints['WPDate'];
                                                $get_StartDate_format = DateformatCls::getNewDateFormat($date_format_data_array); ?>

                                                <span class="location-from"><i class="icon-circle"></i>
                                                    <b><?= $get_StartDate_format['tDisplayTimeAbbr'];
						    //@date('h:i A', strtotime($waypoints['WPDate'])); ?> ( Stop - <?= $waypoints['letterPoint']; ?> )<p><?= $waypoints['address']; ?></p></b></span>
                                                <?php
                                                $w++;
                                            }
                                        } ?>

                                        <span class="location-to"><i
                                                    class="icon-map-marker"></i> <b><?= $get_dEndDate_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($published_rides_data['dEndDate'])); ?> ( Drop-off Location



                                                <?php if(isset($waypointData['DestLocationPoint']) && !empty($waypointData['DestLocationPoint'])) {
                                                   echo '- ' .$waypointData['DestLocationPoint']; }  ?>


                                                ) <p><?= $published_rides_data['tEndLocation']; ?></p></b></span>

                                        <div class="right" style="word-wrap: break-word;">

                                            <h4 style="text-align:center;">Ride Statistics</h4>
                                            <hr/>
                                            <table class="table table-striped table-bordered table-hover">

                                                <tr>
                                                    <th width="40%">Ride Start Date</th>
                                                    <?php if (isValidDate($published_rides_data['dTripStartDate']) && $published_rides_data['dTripStartDate'] != '') { 
                                                            $date_format_data_array['tdate'] = $published_rides_data['dTripStartDate'];
                                                            $get_dTripStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);  
                                                    ?>
                                                        <td><?= $get_dTripStartDate_format['tDisplayDateTime'];//DateTime($published_rides_data['dTripStartDate'], '7'); //@date('Y-m-d h:i A', @strtotime($published_rides_data['dTripStartDate'])); ?> </td>
                                                    <?php } else { ?>
                                                        <td>-</td>
                                                    <?php } ?>
                                                </tr>

                                                <tr>
                                                    <th>Pick Up All Passenger</th>
                                                    <?php if (isValidDate($published_rides_data['markAsPickupDate']) && $published_rides_data['markAsPickupDate'] != '') { 
                                                            $date_format_data_array['tdate'] = $published_rides_data['markAsPickupDate'];
                                                            $get_markAsPickupDate_format = DateformatCls::getNewDateFormat($date_format_data_array); 
                                                    ?>
                                                        <td><?= $get_markAsPickupDate_format['tDisplayDateTime'];//DateTime($published_rides_data['markAsPickupDate'], '7');//@date('Y-m-d h:i A', @strtotime($published_rides_data['markAsPickupDate'])); ?></td>
                                                    <?php } else { ?>
                                                        <td>-</td>
                                                        <?php
                                                    } ?>
                                                </tr>

                                                <tr>
                                                    <th>End Ride Time</th>
                                                    <?php if (isValidDate($published_rides_data['dTripEndDate']) && $published_rides_data['dTripEndDate'] != '') { 
                                                            $date_format_data_array['tdate'] = $published_rides_data['dTripEndDate'];
                                                            $get_markAsPickupDate_format = DateformatCls::getNewDateFormat($date_format_data_array); 

                                                    ?>
                                                        <td><?= $get_markAsPickupDate_format['tDisplayDateTime'];//DateTime($published_rides_data['dTripEndDate'], '7'); //@date('Y-m-d h:i A', @strtotime($published_rides_data['dTripEndDate'])); ?></td>
                                                    <?php } else { ?>
                                                        <td>-</td>
                                                        <?php
                                                    } ?>
                                                </tr>

                                            </table>
                                        </div>

                                        <div class="right" style="word-wrap: break-word;">

                                            <h4 style="text-align:center;">Car Details</h4>
                                            <hr/>
                                            <table class="table table-striped table-bordered table-hover">

                                                <tr>
                                                    <th width="40%">Car Image</th>
                                                    <td>

                                                        <?php
                                                        if(file_exists($CAR_IMAGE_PATH .'/'. basename($carDetails_data['cImage']))){ ?>
                                                        <img style="width: 55px" src="<?php echo $carDetails_data['cImage']; ?>">
                                                        <?php }else { echo "--";} ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="40%">Model</th>
                                                    <td><?php echo $carDetails_data['cModel'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="40%">Make</th>
                                                    <td><?php echo $carDetails_data['cMake'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="40%">Vehicle No.</th>
                                                    <td><?php echo $carDetails_data['cNumberPlate'] ?></td>
                                                </tr>
                                                <tr>
                                                    <th width="40%">Note.</th>
                                                    <td><?php echo $carDetails_data['cNote'] ?></td>
                                                </tr>
                                            </table>
                                        </div>

                                        <?php if(isset($waypointData['WayPointPrice']) && !empty($waypointData['WayPointPrice'])){ ?>
                                        <div class="right" style="word-wrap: break-word;">

                                            <h4 style="text-align:center;">Stop Over Point Price</h4>
                                            <hr/>
                                            <table class="table table-striped table-bordered table-hover">

                                                <?php foreach ($waypointData['WayPointPrice'] as $key => $price){ ?>
                                                    <?php foreach ($price as $key => $p){ ?>
                                                        <tr>
                                                            <th width="40%"><?php echo $key ?></th>
                                                            <td><?php echo $p ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } ?>
                                            </table>
                                        </div>

                                        <?php } ?>

                                        <?php
                                        $class_name = 'col-sm-4';
                                        $style = '';
                                        ?>

                                        <div class="rider-invoice-bottom">
                                            <div class="">
                                                <div class="left">
                                                    <img src="<?php echo $img; ?>"
                                                         style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                         align="left" height="45" width="45" class="CToWUd">
                                                </div>
                                                <div class="right col-sm-9" style="word-wrap: break-word;">
                                                    <div>
                                                        <b><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b>
                                                    </div>
                                                    <!--<div><?php echo clearName($DriverDetails['vName']) . "&nbsp;" . clearName($DriverDetails['vLastName']); ?></div>-->
                                                    <div><?php echo clearName($DriverDetailsData['vName'] . " " . $DriverDetailsData['vLastName']); ?></div>
                                                    <div><?php echo clearEmail($DriverDetailsData['vEmail']); ?></div>
                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-sm-6 rider-invoice-new-right">

                                        <h4 style="text-align:center;">Publish Ride Users List :
                                            #<?= $published_rides_data['vPublishedRideNo']; ?></h4>
                                        <hr/>
                                        <?php if (isset($PublishedRideUser) && !empty($PublishedRideUser)) { ?>
                                            <?php foreach ($PublishedRideUser as $user) { ?>
                                                <h4><?= $user['RiderLabel']; ?></h4>
                                                <hr/>
                                                <table class="table table-striped table-bordered table-hover">
                                                    <tr>
                                                        <th width="24%">Name</th>
                                                        <td><?php echo $user['rider_Name']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $user['iBookedSeatsLabel']; ?></th>
                                                        <td><?php echo $user['iBookedSeats']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $user['PaymentModeTitle']; ?></th>
                                                        <td><?php echo $user['PaymentModeLabel']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>PickUp Location</th>
                                                        <td><?php echo $user['tStartLocation']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Drop Location</th>
                                                        <td><?php echo $user['tEndLocation']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Booking No:</th>
                                                        <td><a href="<?= $tconfig['tsite_url_main_admin'];?>ride_share_bookings_details.php?iBookingId=<?php echo $user['iBookingId']?>" target="_blank"><?php echo $user['vBookingNoTxt']; ?></a></td>
                                                    </tr>

                                                    <tr>
                                                        <?php if (isset($user['rating']) && !empty($user['rating'])) { ?>
                                                            <th>Driver receive Rating:</th>
                                                            <?php
                                                            $tMessage = '';
                                                            if (isset($user['tMessage']) && !empty($user['tMessage'])) {
                                                                $tMessage = ' ( ' . $user['tMessage'] . ' ) ';
                                                            }
                                                            ?>

                                                            <td>
                                                                <?php if (isset($user['rating']) && !empty($user['rating'])) { ?>
                                                                    <img src="<?= $tconfig['tsite_url'] . 'assets/img/star.jpg'; ?>"
                                                                         style="margin: 0 2px 4px 0"> <?php echo $user['rating'] . $tMessage; ?>
                                                                <?php } else {
                                                                    echo "--";
                                                                } ?>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>

                                                    <tr>
                                                        <?php if (isset($user['UserRating']) && !empty($user['UserRating'])) { ?>
                                                            <th>User receive Rating:</th>
                                                            <?php
                                                            $tMessage = '';
                                                            if (isset($user['UserMessage']) && !empty($user['UserMessage'])) {
                                                                $tMessage = ' ( ' . $user['UserMessage'] . ' ) ';
                                                            }
                                                            ?>

                                                            <td>
                                                                <?php if (isset($user['UserRating']) && !empty($user['UserRating'])) { ?>
                                                                    <img src="<?= $tconfig['tsite_url'] . 'assets/img/star.jpg'; ?>"
                                                                         style="margin: 0 2px 4px 0"> <?php echo $user['UserRating'] . $tMessage; ?>
                                                                <?php } else {
                                                                    echo "--";
                                                                } ?>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>

                                                </table>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <h4 style="text-align: center;">Nobody has booked yet.</h4>
                                        <?php } ?>

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
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
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
    var bounds;
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

    function initialize() {//alert('<?= json_encode($latitudes) ?>');
        var thePoint = new google.maps.LatLng('<?php echo $db_trip_data['tStartLat']; ?>', '<?php echo $db_trip_data['tStartLong']; ?>');

        GOOGLE_MAP_OBJ.options.center = thePoint;
        map = GOOGLE_MAP_OBJ.init('map-canvas');

        var trafalgar = new google.maps.LatLng(lats[0], longs[0]);
        var rcbc = new google.maps.LatLng(lats[1], longs[1]);
        //drawCurve(trafalgar, rcbc, map);
       // drawCurvedLine(map);
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
        IconUrl = '../webimages/upload/mapmarker/startmarker.png';
        setMarker(start, 0 ,IconUrl);
        for (var i = 0; i < middle_multi.length; i++) {
            weyPoints = new google.maps.LatLng(parseFloat(middle_multi[i][0]), parseFloat(middle_multi[i][1]));
            IconUrl = '../webimages/upload/mapmarker/waypointmarker.png';
            setMarker(weyPoints, i + 1 ,IconUrl );
        }

        IconUrl = '../webimages/upload/mapmarker/endmarker.png';
        setMarker(end, (middle_multi.length + 1) , IconUrl);
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

    function setMarker(postitions, valIcon , IconUrl = '') {
       /* var newIcon;
        if (valIcon == 0) {

        } else if (valIcon == 'to_loc') {
            newIcon = '../webimages/upload/mapmarker/PinTo.png';
        } else {
            //newIcon = '../webimages/upload/mapmarker/Pin-middle.png';
            newIcon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' + valIcon + '|FF0000|000000';
        }*/

        var valIcon_txt = getLabelFromNumber(valIcon);
        var newIcon1 = {
            url: IconUrl, // URL to your custom icon image
            scaledSize: new google.maps.Size(60, 60), // Size of the icon
            labelOrigin: new google.maps.Point(30, 15) // Anchor point for the label
        };

        /*marker = new google.maps.Marker({
            map: map,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon1
        });*/

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
</script>
</body>
<!-- END BODY-->
</html>

