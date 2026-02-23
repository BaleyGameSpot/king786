<?php
include_once('../common.php');

if(!$userObj->hasPermission('view-god-view')) {
    $userObj->redirect();
}

$script = "GodsView";


$defaultActiveService = "Ride";
if ($APP_TYPE == "UberX") {
    $defaultActiveService = "Job";
} elseif ($APP_TYPE == "Delivery" || $MODULES_OBJ->isOnlyDeliverAllSystem()) {
    $defaultActiveService = "Delivery";
}

?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME; ?> | God’s View</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!-- GLOBAL STYLES -->
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="css/admin_new/godview.css">
    <!--END GLOBAL STYLES -->
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner" id="godsview-content" style="min-height: 700px;">
            <div class="card_body">
                <div class="card_heading_row god_view have_three">
                    <strong>God’s View</strong>
                    <ul class="tab_row">
                        <?php if($MODULES_OBJ->isRideFeatureAvailable('Yes') && strtoupper($APP_TYPE) != "RIDE") { ?>
                        <li class="active" onclick="SetServiceType('Ride')">Rides</li>

                        <?php } if(($MODULES_OBJ->isDeliveryFeatureAvailable('Yes') || $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') || $MODULES_OBJ->isEnableGenieFeature('Yes') || $MODULES_OBJ->isEnableRunnerFeature()) && strtoupper($APP_TYPE) != "RIDE" && !$MODULES_OBJ->isOnlyDeliverAllSystem()) { ?>
                        <li onclick="SetServiceType('Delivery')">Deliveries</li>

                        <?php } if($MODULES_OBJ->isUberXFeatureAvailable('Yes') && strtoupper($APP_TYPE) != "UBERX") { ?>
                        <li onclick="SetServiceType('Job')">Jobs</li>
                        <?php } ?>
                    </ul>
                    <?php /*<span class="fullscreen-toggle" onclick="openFullscreen(this)"><i class="ri-fullscreen-line" aria-hidden="true"></i></span>*/ ?>
                </div>
                <div class="overlay-map-helper">
                    <div class="card_body god_view_operation_card">
                        <ul class="admin_card_row god_view five_box">
                            <li class="active" status="available" onclick="SetServiceStatus('Available')">
                                <!------------------icons----------------->
                                <i class="Ride_icons GodViewIcons">
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/bus.svg" alt="">
                                </i>
                                <i class="Delivery_icons GodViewIcons" >
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/Available_Deliver.svg" alt="">
                                </i>

                                <i class="Job_icons GodViewIcons">
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/Available_Job.svg" alt="">
                                </i>
                                <!------------------icons----------------->
                                <small>
                                    <span>Available</span>
                                    <div class="provider-count" id="available_count"></div>
                                </small>
                            </li>

                            <li status="all" onclick="SetServiceStatus('Not Available')">
                                <!------------------icons----------------->
                                <i class="Ride_icons GodViewIcons">
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/truck.svg" alt="">
                                </i>
                                <i class="Delivery_icons GodViewIcons" >
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/All_Deliver.svg" alt="">
                                </i>

                                <i class="Job_icons GodViewIcons">
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/All_Job.svg" alt="">
                                </i>
                                <!------------------icons----------------->
                                <small>
                                    <span>Not Available</span>
                                    <div class="provider-count" id="not_available_count"></div>
                                </small>
                            </li>
                            
                            <li status="pickup" onclick="SetServiceStatus('Pickup')">
                                <!------------------icons----------------->
                                <i class="Ride_icons GodViewIcons">
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/destination.svg" alt="">
                                </i>
                                <i class="Delivery_icons GodViewIcons" >
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/PickUp_Deliver.svg" alt="">
                                </i>

                                <i class="Job_icons GodViewIcons" >
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/PickUp_Job.svg" alt="">
                                </i>
                                <!------------------icons----------------->
                                <small>
                                    <span id="status-title-pickup">Way to Pickup</span>
                                    <div class="provider-count" id="pickup_count"></div>
                                </small>
                            </li>
                            <li status="arrived" onclick="SetServiceStatus('Arrived')">
                                <i>
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/arrived-destination.svg" alt="">
                                </i>
                                <small>
                                    <span>Arrived / Reached Pickup</span>
                                    <div class="provider-count" id="arrived_count"></div>
                                </small>
                            </li>
                            <li status="ongoing" onclick="SetServiceStatus('OnGoing')">

                                <!------------------icons----------------->
                                <i class="Ride_icons GodViewIcons">
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/destination.svg" alt="">
                                </i>
                                <i class="Delivery_icons GodViewIcons" >
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/WayToPickUp_Deliver.svg" alt="">
                                </i>

                                <i class="Job_icons GodViewIcons" >
                                    <img src="<?= $tconfig['tsite_url_main_admin'] ?>img/icon/OnGoing_Job.svg" alt="">
                                </i>
                                <!------------------icons----------------->

                                <small>
                                    <span id="status-title-ongoing">Way to Dropoff</span>
                                    <div class="provider-count" id="dropoff_count"></div>
                                </small>
                            </li>
                        </ul>
                        <div class="provider_search">
                            <input type="text" id="search-provider" placeholder="Search <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>">
                        </div>
                        <ul class="listing_style1" id="providerlist"></ul>
                        <div class="no-providers"><?= $langage_lbl_admin['LBL_NO_DRIVERS_FOUND'] ?></div>
                    </div>
                    <div>
                        <div class="overlay-map">
                            <div class="overlay-map-content">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <div id="google-map"></div>
                    </div>
                </div>
            </div>
            <?php if($MODULES_OBJ->isRideFeatureAvailable('Yes')) { ?>
            <input type="hidden" name="eServiceType" id="eServiceType" value="Ride">
            <?php } elseif ($MODULES_OBJ->isDeliveryFeatureAvailable('Yes') || $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) { ?>
            <input type="hidden" name="eServiceType" id="eServiceType" value="Delivery">
            <?php } else { ?>
            <input type="hidden" name="eServiceType" id="eServiceType" value="Job">
            <?php } ?>
            <input type="hidden" name="eServiceStatus" id="eServiceStatus" value="Available">
            <input type="hidden" name="page_loaded" id="page_loaded" value="Yes">
        <!--END PAGE CONTENT -->
        </div>
    </div>
    <?php include_once('footer.php'); ?>

</div>
<script type="text/javascript" src="<?= $tconfig["tsite_url"]; ?>assets/libraries/scClient-js/socketcluster-client.js"></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url"]; ?>assets/js/socketclustercls.js"></script>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=geometry" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/jquery_easing.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js" defer></script>
<script>
    var SERVICETYPE_ICONS_TEXT = "<?php echo $defaultActiveService ?>";

    var map_gods_view;
    var markers = [];
    var markers_profile_img = [];
    var bounds;
    var infowindow;

    $(document).ready(function () {

    });

    SetServiceType(SERVICETYPE_ICONS_TEXT);

    google.maps.event.addDomListener(window, 'load', initMap);
    
    function initMap() {
        map_gods_view = GOOGLE_MAP_OBJ.init('google-map');

        GetProviderDataGodsView();
    }

    function SetServiceType(service_type) {
        SetIcons(service_type);
        $('#eServiceType').val(service_type);
        $('#search-provider').val('');
        if(service_type == "Job") {
            $('#status-title-pickup').text('Way to Job Location');
            $('#status-title-ongoing').text('Job Started');
        }
        else if(service_type == "Delivery")
        {
            $('#status-title-pickup').text('Way to Pickup');
            $('#status-title-ongoing').text('Way to Deliver');
        }
        else {
            $('#status-title-pickup').text('Way to Pickup');
            $('#status-title-ongoing').text('Way to Dropoff');
        }
        GetProviderDataGodsView();
    }

    function SetIcons(service_type) {
        $('.GodViewIcons').hide();
        $('.'+service_type+'_icons').show();
    }

    function SetServiceStatus(service_status) {
        $('#eServiceStatus').val(service_status);
        $('#search-provider').val('');


        
        GetProviderDataGodsView();
    }

    function GetProviderDataGodsView() {

        var eServiceType = $('#eServiceType').val();
        var eServiceStatus = $('#eServiceStatus').val();
        showMapOverlay();

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_dashboard.php',
            'AJAX_DATA': {'chart_type': 'GodsView', 'eServiceStatus': eServiceStatus, 'eServiceType': eServiceType},
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (res) {
            hideMapOverlay();
            if (res.action == "1") {
                var response = res.result;
                if (response.Action == "1") {
                    removeAllMarkersGodsView();
                    var responseArr = response.data;
                    var provider_list_html = "";

                    // $('#all_count').html('(' + response.all_count + ')');
                    $('#not_available_count').html('(' + response.not_available_count + ')');
                    $('#available_count').html('(' + response.available_count + ')');
                    $('#pickup_count').html('(' + response.pickup_count + ')');
                    $('#arrived_count').html('(' + response.arrived_count + ')');
                    $('#dropoff_count').html('(' + response.dropoff_count + ')');
                    $('ul.god_view li').find('.provider-count').show();

                    if (response.total > 0) {
                        // $('ul.god_view li').find('small div').remove();
                        // $('ul.god_view').find('li.active small').append('<div style="margin-bottom: 10px">(' + response.total + ')</div>');

                        $('.no-providers').hide();
                        $('#providerlist').show();

                        bounds = new google.maps.LatLngBounds();

                        for (var i = 0; i < responseArr.length; i++) {
                            bounds.extend(new google.maps.LatLng(parseFloat(responseArr[i].vLatitude), parseFloat(responseArr[i].vLongitude)));

                            responseArr[i]['index_no'] = i;
                            if (eServiceType == "Job") {
                                setMarkerSPGodsView(responseArr[i]);
                            } else {
                                setMarkerGodsView(responseArr[i]);
                            }

                            var provider_status = responseArr[i].ServiceStatusIcon;
                            var ServiceStatusTitleHtml = "";
                            if(responseArr[i].ServiceStatusTitle != "All" && eServiceStatus == "All") {
                                ServiceStatusTitleHtml = '<a href="javascript:void(0)" class="status_button" status="' + provider_status + '">' + responseArr[i].ServiceStatusTitle +'</a>';
                            }
                            var VehicleDetailHtml = "";
                            if(eServiceType != "Job") {
                                var VehicleLicenseNo = responseArr[i].vehicle_license;
                                var VehicleModel = responseArr[i].vehicle_model;

                                VehicleDetailHtml = '<div class="vehicle-details"><span>' + VehicleLicenseNo + '</span><br />' + VehicleModel + '</div>';
                            }

                            provider_list_html += '<li class="provider-list-item provider-' + i + '" data-providername="' + responseArr[i].name + '|' + responseArr[i].phone_no + '" status="' + provider_status + '"><div><i style="background-image:url(\'' + responseArr[i].image + '\');"></i><b>' + responseArr[i].name + ' <small>' + responseArr[i].phone_no + '</small></b></div><div class="status-content">' + ServiceStatusTitleHtml + VehicleDetailHtml + '</div></li>';
                        }

                        let left_padding = $(".god_view_operation_card").outerWidth() + parseInt($(".god_view_operation_card").css("left"));
                        map_gods_view.fitBounds(bounds, {top: 5, right: 5, bottom: 5, left: left_padding});

                        if($('#providerlist').find('.mCSB_container').length > 0) {
                            $('#providerlist').find('.mCSB_container').html(provider_list_html);
                        } else {
                            $('#providerlist').html(provider_list_html);
                        }

                        var providerlist_container = $("#providerlist");
                        if (providerlist_container[0].scrollHeight > providerlist_container.outerHeight()) {
                            providerlist_container.mCustomScrollbar({
                                theme: "minimal-dark",
                                scrollInertia: 500,
                                mouseWheel: {
                                    scrollAmount: 200
                                }
                            });
                        }

                    } else {
                        if(eServiceStatus == "Available" && $('#page_loaded').val() == "Yes") {
                            $('#page_loaded').val("No");
                            $('li[status="all"]').trigger('click');
                        }
                        if($('#providerlist').find('.mCSB_container').length > 0) {
                            $('#providerlist').find('.mCSB_container').html("");
                        } else {
                            $('#providerlist').html("");
                        }

                        $('.no-providers').show();
                        $('#providerlist').hide();
                    }
                }
            } else {
            }
        });
    }

    function setMarkerGodsView(details) {
        var LatLng = new google.maps.LatLng(parseFloat(details.vLatitude), parseFloat(details.vLongitude));
        var map_icon = {
            url: details.map_icon
        };
        var marker = new google.maps.Marker({
            position: LatLng,
            map: map_gods_view,
            icon: map_icon,
            data: details
        });

        marker.addListener('click', function (e) {
            showProviderDetailsGodsView(this);
            $('.provider-list-item').removeClass('active');
            scrollToProvider('.provider-' + details.index_no);
        });

        $(document).on('click', '.provider-' + details.index_no, function() {
            $('.provider-list-item').removeClass('active');
            $(this).addClass('active');
            showProviderDetailsGodsView(marker);
        });

        markers.push(marker);
        initSocketGodsView(marker);
    }

    function setMarkerSPGodsView(details) {
        var LatLng = new google.maps.LatLng(parseFloat(details.vLatitude), parseFloat(details.vLongitude));
        var map_icon = {
            url: details.image,
            scaledSize: new google.maps.Size(35, 35),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(18, 49)
        };
        var marker = new google.maps.Marker({
            position: LatLng,
            map: map_gods_view,
            icon: map_icon
        });
        markers_profile_img.push(marker);
        var map_icon = {
            url: details.map_icon,
            scaledSize: new google.maps.Size(50, 57),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(25, 57)
        };
        var marker = new google.maps.Marker({
            position: LatLng,
            map: map_gods_view,
            icon: map_icon,
            data: details
        });
        marker.addListener('click', function (e) {
            showProviderDetailsGodsView(this);
            $('.provider-list-item').removeClass('active');
            scrollToProvider('.provider-' + details.index_no);
        });

        $(document).on('click', '.provider-' + details.index_no, function() {
            $('.provider-list-item').removeClass('active');
            $(this).addClass('active');
            showProviderDetailsGodsView(marker);
        });

        markers.push(marker);
        initSocketGodsView(marker);
    }

    function showProviderDetailsGodsView(marker) {
        hideInfoWindow();
        var provider_email;
        if(marker.data.email != "") {
            provider_email = marker.data.email;
        } else {
            provider_email = "--";
        }

        var content = "<table><tr><td rowspan='6'><img src=" + marker.data.image + " height='70' width='auto'/></td>";
        content += "<tr><td>&nbsp;&nbsp;Name: </td><td><b>" + marker.data.name + "</b></td></tr>";
        content += "<tr><td>&nbsp;&nbsp;Mobile: </td><td><b>" + marker.data.phone_no + "</b></td></tr>";
        content += "<tr><td>&nbsp;&nbsp;Email: </td><td><b>" + provider_email + "</b></td></tr>";
        if (marker.data.tracking_url != "") {
            content += "<tr><td colspan='2'>&nbsp;&nbsp;</td></tr>";
            content += "<tr><td colspan='2' align='center'><a href='" + marker.data.tracking_url + "' class='infowindow-live-track-btn' target='_blank'><b>Live tracking</b></a></td></tr>";
        }
        content += "</table>";

        infowindow = new google.maps.InfoWindow({
            content: content
        });
        infowindow.open(map_gods_view, marker);
        map_gods_view.setZoom(24);
        map_gods_view.setCenter(marker.getPosition());
    }

    function hideInfoWindow() {
        if (infowindow != undefined && infowindow != null) {
            infowindow.close();
        }
    }

    function removeAllMarkersGodsView() {
        for (var k = 0; k < markers_profile_img.length; k++) {
            markers_profile_img[k].setMap(null);
        }
        for (var j = 0; j < markers.length; j++) {
            markers[j].setMap(null);
        }
    }

    function showMapOverlay() {

        $('.overlay-map').show();
    }

    function hideMapOverlay() {
        $('.overlay-map').hide();
    }

    function scrollToProvider(selector) {
        $(selector).addClass('active');

        $('#providerlist').mCustomScrollbar("scrollTo", selector);
    }

    function initSocketGodsView(marker) {
        var marker_sock = marker;
        var channel = 'ONLINE_DRIVER_LOC_' + marker.data.iDriverId;

        SOCKET_OBJ.subscribe(channel, function (result) {
            // var result = JSON.parse(res);
            var LatLng = new google.maps.LatLng(result.vLatitude, result.vLongitude);
            var duration = parseInt(950);
            if (duration < 0) {
                duration = 1;
            }
            setTimeout(function () {
                marker_sock.animateTo(LatLng, {easing: 'linear', duration: duration, rotate: 'Yes'});
            }, 2000);
        });
    }

    $(document).on("click", ".tab_row li", function () {
        $(this).parent(".tab_row").find('li').removeClass('active');
        $(this).addClass('active');
    });
    $(document).on("click", ".admin_card_row.god_view li", function () {
        $(".admin_card_row.god_view li").removeClass('active');
        $(this).addClass('active');
    });

    $('#search-provider').keyup(function() {
        var search_keyword = $(this).val();

        if(search_keyword != "") {
            search_keyword = search_keyword.toLowerCase();
            $('#providerlist li').hide();

            var provider_found = 'No';
            $('#providerlist li').each(function(index, elem) {
                var provider_data = $(elem).data('providername').toLowerCase();

                if(provider_data.search(search_keyword) > -1) {
                    $(elem).show();
                    provider_found = "Yes";
                }
            });

            if(provider_found == "No") {
                $('.no-providers').show();
                $('#providerlist').hide();
            } else {
                $('.no-providers').hide();
                $('#providerlist').show();
            }
        } else {
            if($('#providerlist li').length > 0) {
                $('.no-providers').hide();
                $('#providerlist, #providerlist li').show();
            } else {
                $('.no-providers').show();
            }
        }
    });

</script>

</body>
<!-- END BODY-->
</html>