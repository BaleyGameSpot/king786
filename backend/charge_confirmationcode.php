<?php
include_once('common.php');

$tbl_name = 'trips';
$script = "Trips";

$APP_DELIVERY_MODE = $CONFIG_OBJ->getConfigurations("configurations", "APP_DELIVERY_MODE");
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
    $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
    $_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
}
$eUserType = $_SESSION['sess_user'];
$iTripId = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));
$iTripId = isset($_REQUEST['iTripId']) ? intVal($iTripId) : '';


/* Start original route */
$data_locations = $obj->MySQLSelect("select tPlatitudes,tPlongitudes from trips_locations where iTripId = '" . $iTripId . "'");
$lat_array = explode(",", $data_locations[0]['tPlatitudes']);
$long_array = explode(",", $data_locations[0]['tPlongitudes']);
$data_trip_locations = $obj->MySQLSelect("select eType,iVehicleTypeId,tStartLat,tStartLong,tEndLat,tEndLong from trips where iTripId = '" . $iTripId . "'");
$eType = $data_trip_locations[0]['eType'];
if (empty($data_locations)) {
    $data_locations = $data_trip_locations;
    $lat_array[0] = $data_locations[0]['tStartLat'];
    $lat_array[1] = $data_locations[0]['tEndLat'];
    $long_array[0] = $data_locations[0]['tStartLong'];
    $long_array[1] = $data_locations[0]['tEndLong'];
}
$total_ele = scount($lat_array);
$inc = 1;
if ($total_ele > 200) {
    $inc = round($total_ele / 200);
}
// echo $inc=5;
for ($i = 0; $i < $total_ele; $i += $inc) {
    $latitudes[] = $lat_array[$i];
    $longitudes[] = $long_array[$i];
}
$orgDataArr = array();
$orgData = $obj->MySQLSelect("SELECT vCompany,iOrganizationId FROM organization ORDER BY iOrganizationId ASC");

for ($g = 0; $g < scount($orgData); $g++) {
    $orgDataArr[$orgData[$g]['iOrganizationId']] = $orgData[$g]['vCompany'];
}
array_push($latitudes, $lat_array[$total_ele - 1]);
array_push($longitudes, $long_array[$total_ele - 1]);

/* End original route */
$getAllTrip = array();
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <?php
            if ($_SESSION['sess_user'] == 'driver') {
                $db_trip_data = FetchTripFareDetailsWeb($iTripId, $_SESSION['sess_iUserId'], 'Driver');
            } else if ($_SESSION['sess_user'] == 'rider') {
                $db_trip_data = FetchTripFareDetailsWeb($iTripId, $_SESSION['sess_iUserId'], 'Passenger');
            } else if ($_SESSION['sess_user'] == 'organization') {
                $db_trip_data = FetchTripFareDetailsWeb($iTripId, $_SESSION['sess_iUserId'], 'Passenger', '', 'Yes');
            } else {
                $db_trip_data = FetchTripFareDetailsWeb($iTripId, '', 'Driver');
            }
            $organizationName = "";
            //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected Start
            if (isset($db_trip_data['tVehicleTypeFareData']) && $db_trip_data['tVehicleTypeFareData'] != "") {
                $decodeTypeData = (array) json_decode($db_trip_data['tVehicleTypeFareData']);
                $decodeTypeData = $decodeTypeData['FareData'];
                $db_trip_data['vCategory'] = $db_trip_data['vVehicleCategory'] = $decodeTypeData[0]->vVehicleCategory;
            }
            //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected End
            if (isset($orgDataArr[$db_trip_data['iOrganizationId']]) && $orgDataArr[$db_trip_data['iOrganizationId']] != "" && $db_trip_data['ePaymentBy'] == "Organization" && $eUserType == "rider") {
                $organizationName = $orgDataArr[$db_trip_data['iOrganizationId']];
            }
            $jobWord = "Job";
            if (strtoupper($eType) == "RIDE") {
                $jobWord = "Trip";
            }
            ?>
            <div class="page-contant">
                <div class="page-contant-inner page-trip-detail clearfix">
                    <h2 class="header-page trip-detail">Extra Charges on <?= $jobWord; ?> Number <?= $db_trip_data['vRideNo']; ?> 				
                        <!-- <a href="<?= $tconfig['tsite_url'] ?>"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>					 -->

                    </h2>
                    <?php
                    //$iVehicleTypeId = get_value('trips', 'iVehicleTypeId', 'iTripId', $iTripId, '', 'true');
                    $iVehicleTypeId = $data_trip_locations[0]['iVehicleTypeId'];
                    $serviceCost = get_value('vehicle_type', 'fFixedFare', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
                    $serviceCost = setTwoDecimalPoint($serviceCost);
                    if (!empty($db_trip_data)) {

                        $resultChargeDetails = json_decode($db_trip_data['vChargesDetailData']);
                        //Added By HJ On 29-08-2020 For Manual Toll and Other Charges Related Changes Start
                        if (strtoupper($eType) == "RIDE") {
                            $fTollPrice = $resultChargeDetails->fTollPrice;
                            $fOtherCharges = $resultChargeDetails->fOtherCharges;
                        } else {
                            $fMaterialFee = $resultChargeDetails->fMaterialFee;
                            $fMiscFee = $resultChargeDetails->fMiscFee;
                            $serviceCost = $resultChargeDetails->serviceCost;
                        }
                        $fDriverDiscount = $resultChargeDetails->fDriverDiscount;
                        $vConfirmationCode = $resultChargeDetails->vConfirmationCode;

                        if (strtoupper($eType) == "RIDE") {
                            $totalCharges = $totalAmount = ($fTollPrice + $fOtherCharges - $fDriverDiscount);
                        } else {
                            $totalAmount = ($serviceCost + $fMaterialFee + $fMiscFee - $fDriverDiscount);
                        }
                        //Added By HJ On 29-08-2020 For Manual Toll and Other Charges Related Changes End
                        $iDriverId = $db_trip_data['iDriverId'];
                        $iUserId = $db_trip_data['iUserId'];
                        $row = $obj->MySQLSelect("SELECT * FROM `register_user` WHERE iUserId='$iUserId'");
                        $Data['vEmail'] = $row[0]['vEmail'];
                        $userName = $row[0]['vName'] . ' ' . $UserEmail = $row[0]['vLastName'];
                        $Data['vPhone'] = $row[0]['vPhone'];
                        $vLanguage = $row[0]['vLang'];
                        $vCurrency =$vCurrencyPassenger= $row[0]['vCurrencyPassenger'];
                        $vCurrency = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrency);
                        $currencyratio = $vCurrency[0]['Ratio'];
                        $vSymbol = $vCurrency[0]['vSymbol'];

                        $fMaterialFee = formateNumAsPerCurrency($fMaterialFee * $currencyratio,$vCurrencyPassenger);
                        $fMiscFee = formateNumAsPerCurrency($fMiscFee * $currencyratio,$vCurrencyPassenger);
                        $fDriverDiscount = formateNumAsPerCurrency($fDriverDiscount * $currencyratio,$vCurrencyPassenger);
                        $totalAmount = formateNumAsPerCurrency($totalAmount * $currencyratio,$vCurrencyPassenger);
                        $serviceCost = formateNumAsPerCurrency($serviceCost * $currencyratio,$vCurrencyPassenger);
                        //Added By HJ On 29-08-2020 For Manual Toll and Other Charges Related Changes Start
                        $fTollPrice = formateNumAsPerCurrency($fTollPrice * $currencyratio, $vCurrencyPassenger);
                        $fOtherCharges = formateNumAsPerCurrency($fOtherCharges * $currencyratio, $vCurrencyPassenger);
                        //Added By HJ On 29-08-2020 For Manual Toll and Other Charges Related Changes End
                        $rowD = $obj->MySQLSelect("SELECT * FROM `register_driver` WHERE iDriverId='$iDriverId'");
                        $ProviderName = $rowD[0]['vName'];
                        $Data['ProviderEmail'] = $rowD[0]['vEmail'];
                        ?>
                        <div class="trip-detail-page">
                            <div class="trip-detail-page-inner clearfix">

                                <div class="trip-detail-page-right1" style="font-weight: 400;font-size: 15px">
                                    <?php if (strtoupper($eType) == "RIDE") { ?>
                                        Dear <?= $userName ?>,<br><br>Driver <?= $ProviderName; ?> has added an extra charges for <?= $jobWord; ?> number <b><?= $db_trip_data['vRideNo']; ?></b>.  Please refer below added charges and provide a verification code to the driver to confirm added charges.<br><br><span>Toll Charges : <b><?= $fTollPrice ?></b></span><br><span>Other Charges : <b><?= $fOtherCharges ?></b></span><br>
                                        <br><br><span>Verification Code: <b><?= $vConfirmationCode; ?></b></span>
                                    <?php } else { ?>
                                        Dear <?= $userName ?>,<br><br>Provider <?= $ProviderName; ?> has added an extra charges for <?= $jobWord; ?> number <b><?= $db_trip_data['vRideNo']; ?></b>.  Please refer below added charges and provide a verification code to the provider to confirm added charges.<br><br><span>Service Cost: <b><?= $serviceCost ?></b></span><br><span>Material Fee: <b><?= $fMaterialFee ?></b></span><br><span>Misc Fee: <b><?= $fMiscFee ?></b></span><br><span>Provider discount: <b><?= $fDriverDiscount ?></b></span><br><br><span>Verification Code: <b><?= $vConfirmationCode; ?></b></span>
                                    <?php } ?>
                                    <br><span>Total Amount: <b><?= $totalAmount ?></b></span>
                                    <br><span><br></span><span><br></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>

                    <?php } ?> 
                </div>
            </div>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <div  class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="servicetitle">
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                            Service Details
                            <!-- <button type="button" class="close" data-dismiss="modal">x</button> -->
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="service_detail"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
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
            function initialize() {
                var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
                var mapOptions = {
                    zoom: 4,
                    center: thePoint
                };
                map = new google.maps.Map(document.getElementById('map-canvas'),
                        mapOptions);
                from_to_polyline();
            }
            var tPlatitudes = '<?= json_encode($latitudes) ?>';
            lats = JSON.parse(tPlatitudes);
            var tPlongitudes = '<?= json_encode($longitudes) ?>';
            longs = JSON.parse(tPlongitudes);
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
                }
            }
            var directionsService = new google.maps.DirectionsService();
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
            }
            $(document).ready(function () {
                google.maps.event.addDomListener(window, 'load', initialize);
            });
            function from_to_polyline() {
                DeleteMarkers('from_loc');
                DeleteMarkers('to_loc');
                setMarker(start, 'from_loc');
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
                var typeNameArr = JSON.parse(typeArr)
                var serviceHtml = "";
                var srno = 1;
                for (var g = 0; g < tripJson.length; g++) {
                    serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "</p>";
                    srno++;
                }
                $("#service_detail").html(serviceHtml);
                $("#servicetitle").text("Service Details : " + rideNo);
                $("#service_modal").modal('show');
                return false;
            }
        </script>
        <!-- End: Footer Script -->
    </body>
</html>