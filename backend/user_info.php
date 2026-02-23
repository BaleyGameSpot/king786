<?php
include_once "common.php";
GetPagewiseSessionMemberType('user_info');
$_REQUEST["vLang"] = $_SESSION['sess_lang'];
//include_once 'assets/libraries/configuration.php'; // commented cause this type of file does not exists.
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
//if(($fromOrder == "guest" || $fromOrder == "user") && $_SESSION['sess_user'] != "rider")
//{
//    header('Location:profile');
//}
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
if ($MODULES_OBJ->isSingleStoreSelection()) {
    $service_categories = array();
    if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
        $service_categories = $serviceCategoriesTmp;
    }
    $cnt_sc = scount($service_categories);
    if ($cnt_sc == 1) {
        session_start();
        $store_data = getStoreDataForSystemStoreSelection($service_categories[0]['iServiceId']);
        //$iCompanyId = $store_data[0]['iCompanyId'];
        $iCompanyId = $store_data['iCompanyId'];
        $_SESSION[$orderLongitudeSession] = $store_data['vRestuarantLocationLat'];
        $_SESSION[$orderLatitudeSession] = $store_data['vRestuarantLocationLong'];
        $_SESSION[$orderServiceSession] = $store_data['iServiceId'];
        $_SESSION[$orderAddressSession] = $store_data['vCaddress'];
        $_SESSION[$orderServiceNameSession] = $service_categories[($store_data['iServiceId'] - 1)]['vServiceName'];
        header("location: store-items?id=" . $iCompanyId . "&order=" . $fromOrder);
        exit;
    }
}
$service_categories = array();
if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
    $service_categories = $serviceCategoriesTmp;
}
$sql1 = "SELECT iServiceId FROM `service_categories` WHERE iServiceId != '1' AND eStatus='Active' ORDER BY iServiceId LIMIT 1";
$servicecatid = $obj->MySQLSelect($sql1);
$langage_lblDataOther = $LANG_OBJ->FetchLanguageLabels($_SESSION['sess_lang'], '1', $servicecatid[0]['iServiceId']);
$siteUrl = $tconfig['tsite_url'];
$foodButtonName = $langage_lbl['LBL_MANUAL_SHOW_RESTAURANTS'];
$otherButtonName = $langage_lbl['LBL_MANUAL_SHOW_STORE'];
$pageHead = $SITE_NAME . " | " . $langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'];
//echo $SITE_NAME;die;
$db_orderimage = $STATIC_PAGE_OBJ->FetchStaticPage(53, $_SESSION['sess_lang']);
$vImage = $db_orderimage[0]['vImage'];
$script = "order-items";
foreach ($service_categories as $key => $value) {
    if (isset($_SESSION["navigatedPage"]) && $value['vService'] == strtolower($_SESSION["navigatedPage"])) {
        $selectedServiceIdOrderItems = $value['iServiceId'];
    }
}

$pageId = '';
if (isset($_REQUEST['pageId']) && !empty($_REQUEST['pageId'])) {
    $pageId = $_REQUEST['pageId'];
    $vehicle_category = $obj->MySQLSelect("SELECT iServiceId  FROM `vehicle_category` WHERE  iVehicleCategoryId = " . $pageId);

    if(isset($vehicle_category[0]['iServiceId']) && !empty($vehicle_category[0]['iServiceId'])){
        $selectedServiceIdOrderItems = $vehicle_category[0]['iServiceId'];
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $pageHead; ?></title>
    <meta name="keywords" content="<?= !empty($meta_arr['meta_keyword']) ? $meta_arr['meta_keyword'] : ""; ?>"/>
    <meta name="description" content="<?= !empty($meta_arr['meta_desc']) ? $meta_arr['meta_desc'] : ""; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once "top/top_script.php"; ?>
    <?php include_once "top/validation.php"; ?>
    <!-- End: Default Top Script and css-->
    <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
    <script type='text/javascript' src='<?= $siteUrl; ?>assets/map/gmaps.js'></script>
    <script type='text/javascript' src='<?= $siteUrl; ?>assets/js/jquery-ui.min.js'></script>
    <script type='text/javascript' src='<?= $siteUrl; ?>assets/js/bootbox.min.js'></script>
    <link href="<?= $siteUrl; ?>assets/css/radio.css" rel="stylesheet" type="text/css"/>
    <?php include_once "store_css_include.php"; ?>
</head>
<body id="order-pages">
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once "top/left_menu.php"; ?>
    <!-- End: Left Menu-->
    <!-- home page -->
    <!-- Top Menu --> 
    <?php include_once "top/header_topbar.php"; ?> 
    <!-- End: Top Menu-->
    <!-- contact page-->
    <div class="page-contant _MB0_ GRAYBG mainof-searchpage">
        <div class="search-banner">
            <div class="page-contant-inner set-min-height clearfix">
                <!-- trips detail page -->
                <div class="search-page-wrap">
                    <?php
                    if (isset($_REQUEST['error']) && $_REQUEST['error'] != "") {
                        ?>
                        <div class="row">
                            <div class="col-sm-12 alert alert-danger">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $_REQUEST['var_msg']; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div>
                        <h3 class="search-head" id="search-head"><?= $langage_lbl['LBL_MANUAL_STORE_USER_MAIN_TEXT']; ?></h3>
                        <p><?= $langage_lbl['LBL_MANUAL_STORE_USER_MAIN_DESCRIPTION']; ?></p>
                        <form action="<?= $siteUrl; ?>user_info_action.php" class="search-main-form" name="CustomerForm" id="CustomerForm" method="post" class="clearfix">
                            <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?= !empty($from_lat_long) ? $from_lat_long : ''; ?>">
                            <input type="hidden" name="from_lat" id="from_lat" value="<?= !empty($latitude) ? $latitude : ''; ?>" class="from_lat">
                            <input type="hidden" name="from_long" id="from_long" value="<?= !empty($longitude) ? $longitude : ''; ?>">
                            <input type="hidden" name="fromOrder" id="fromOrder" value="<?= !empty($fromOrder) ? $fromOrder : ''; ?>">
                            <?php
                            $service_categories_classs = "";
                            if (scount($service_categories) > 0) {
                                if (scount($service_categories) == 1) {
                                    $service_categories_classs = 'has-not-categories';
                                }
                            }
                            ?>
                            <div id="DeliveryAddress" class="deliver-address user_info_address newrow <?php echo $service_categories_classs; ?>">

                                <!-- <div class="delivery-click-event"></div> -->
                                <!-- <div class="progress" style="width:100%;height:5px;">
<div class="progress-bar progress-bar-striped progress-bar-animated active bg-light" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">    
</div>
</div> -->
                                <?php /*<span class="col-clear" style="display: none;z-index:9999999;" id="clearbutton">
                                            <img src="<?= $siteUrl; ?>assets/img/cancel.svg" alt="" onClick="clearSearchBox();" class="close_ico">
                                        </span>*/?>
                                <?php
                                if (scount($service_categories) > 0) {
                                    if (scount($service_categories) == 1) {
                                        ?>
                                        <input type="hidden" name="serviceid" id="serviceid" value="<?= $service_categories[0]['iServiceId']; ?>">
                                        <?php
                                    }
                                    else {
                                        ?>
                                        <select name="serviceid" id="serviceid" onchange="renameButtonName(this.value);">
                                        <?php
                                        for ($i = 0; $i < scount($service_categories); $i++) {
                                            $iServiceId = $service_categories[$i]['iServiceId'];
                                            if ($service_categories[$i]['vImage'] == "") {
                                                $service_categories[$i]['vImage'] = $siteUrl . 'assets/img/burger.jpg';
                                            }
                                            ?>
                                            <option value="<?php echo $iServiceId; ?>" data-servicename="<?php echo $service_categories[$i]['vServiceName']; ?>" <?php if (!empty($selectedServiceIdOrderItems) && $iServiceId == $selectedServiceIdOrderItems) echo "selected"; ?>><?php echo $service_categories[$i]['vServiceName']; ?> </option>
                                        <?php }
                                        ?></select><?php
                                    }
                                }
                                ?>
                                <ul id="suggestions"></ul>

                                <div class="user_info_input">
                                    <input class="delivery-input" type="text" name="vServiceAddress" id="vServiceAddress" autocomplete="off" placeholder="<?= $langage_lbl['LBL_MANUAL_SEARCH_BOX_PLACEHOLDER']; ?>" required="" autocorrect="off" spellcheck="false" autocomplete="off"/>
                                    <img src="<?= $siteUrl ?>assets/img/detect_loc.svg" class="detect-loc" onclick="fetchLocation()" title="<?= $langage_lbl['LBL_FETCH_LOCATION_HINT'] ?>">
                                </div>
                            </div>
                            <button type="submit" id="submitbtn" name="SUBMIT"><?= $foodButtonName; ?></button>
                        </form>
                    </div>
                </div>
                <div class="order-image-holder servicePage1 shine">
                    <img class = "imageVisibilityHidden resizeImageWithJs " attr-image-src="<?= $siteUrl; ?>assets/img/apptype/<?= $template; ?>/delivered.png" alt="">
                </div>
            </div>
        </div>
        <?php
        $oddEven = 1;
        //$allCat = $service_categories;
        $allCat = array();
        for ($b = 0; $b < scount($allCat); $b++) {
            $oddEvenClass = "";
            if ($oddEven % 2 == 0) {
                $oddEvenClass = "reverse";
            }
            $oddEven++;
            $categoryImg = $allCat[$b]['vImage'];
            if ($categoryImg == "") {
                $categoryImg = $siteUrl . 'assets/img/food-cat.jpg';
            }
            ?>
            <div class="delivery-category <?= $oddEvenClass; ?>">
                <div class="delivery-category-data" style="background-image:url(<?= $categoryImg; ?>)">
                    <div class="delivery-category-caption">
                        <div class="caption-data">
                            <strong><?= strtoupper($allCat[$b]['vServiceName']); ?></strong>
                            <p><?= $allCat[$b]['tDescription']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!--<div class="delivery-category">
            <div class="delivery-category-data" style="background-image:url(assets/img/food-cat.jpg)">
                <div class="delivery-category-caption">
                    <div class="caption-data">
                        <strong>FOOD DELIVERY</strong>
                        <p>Let your customers order food online and get it delivered right at their doorstep instantly!</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="delivery-category reverse">
            <div class="delivery-category-data" style="background-image:url(assets/img/alchohol-delivery.jpg)">
                <div class="delivery-category-caption">
                    <div class="caption-data">
                        <strong>Alcohol Delivery</strong>
                        <p>Let your customers order food online and get it delivered right at their doorstep instantly!</p>
                    </div>
                </div>
            </div>
        </div>-->
    </div>
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once 'footer/footer_home.php'; ?>
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<?php include_once 'top/footer_script.php'; ?>
<script src="<?= $siteUrl; ?>assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"] ?>js/moment.min.js"></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"] ?>js/plugins/select2.min.js"></script>
<script type="text/javascript" src="<?= $siteUrl; ?>assets/js/validation/jquery.validate.min.js"></script>
<script>
    $(document).ready(function () {
        var e = $("#serviceid");
        if (e.prop('type') == 'select-one') {
            var serviceId = e[0].options[e[0].selectedIndex].value;
            renameButtonName(serviceId);
        }

        /*setTimeout(function () {
            $("#serviceid")[0].selectedIndex = 0;
        }, 300);*/
    });
    $("#reset").on("click", function () {
        $('#vServiceAddress,#from_lat,#from_long,#from_lat_long').val('');
    });
    var errormessage;
    $('#CustomerForm').validate({
        ignore: '',
        errorClass: 'help-block error',
        errorElement: 'span',
        errorPlacement: function (error, e) {
            if (e.hasClass('from_lat') == true) {
                $('#vServiceAddress').after(error);
            } else {
                $('#vServiceAddress').after('');
            }
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow strong input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            from_lat: {required: true},

        },
        messages: {
            from_lat: {required: '<?php echo $langage_lbl["LBL_MANUAL_STORE_REQUIRED_DELIVERY_ADDRESS"] ?>'},
        }

    });
    var eFlatTrip = 'No';
    var eTypeQ11 = 'yes';
    var map;
    // var geocoder;
    var circle;
    var markers = [];
    var driverMarkers = [];
    var bounds = [];
    var newLocations = "";
    var autocomplete_from;
    var autocomplete_to;
    // var geocoder = new google.maps.Geocoder();
    var directionsService = new google.maps.DirectionsService(); // For Route Services on map
    var directionsOptions = {// For Polyline Route line options on map
        polylineOptions: {
            strokeColor: '#FF7E00',
            strokeWeight: 5
        }
    };
    var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
    var showsurgemodal = "Yes";
    var status;
    var eType = "";
    var APP_DELIVERY_MODE = '<?= $APP_DELIVERY_MODE ?>';
    var ENABLE_TOLL_COST = "<?= $ENABLE_TOLL_COST ?>";
    // alert(APP_DELIVERY_MODE);
    switch ("<?php echo $APP_TYPE; ?>") {
        case "Ride-Delivery":
            if (APP_DELIVERY_MODE == "Multi") {
                eType = 'Ride';
            }
            break;
        case "Ride-Delivery-UberX":
            if (APP_DELIVERY_MODE == "Multi") {
                eType = 'Ride';
            }
            break;
        case "Delivery":
            eType = 'Deliver';
            break;

        case "UberX":
            eType = 'UberX';
            break;

        default:
            eType = 'Ride';
    }

    function initialize() {
        var bounds = new google.maps.LatLngBounds();
        var thePoint = new google.maps.LatLng(from_lat, from_long);
        bounds.extend(thePoint);
        var mapOptions = {
            zoom: 4,
            center: thePoint
        };
        map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);

        map.fitBounds(bounds);
        zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {
            if (this.getZoom()) {
                this.setZoom(12);
            }
        });
        if (eType == "Deliver") {
            show_type(eType);
        }
    }

    function clearSearchBox() {
        // var previousValue;
        $("#vServiceAddress").val('');
        $('#clearbutton').hide();
        $('.progress-indeterminate').hide();
        // $("#suggestions").html('');
        // $(".advance-pac-container").html('');
        // $( ".advance-pac-container" ).remove();
        $(".ui-helper-hidden-accessible").html('');
        $(".ui-autocomplete").hide();
        $(".ui-menu-item").remove();
        sessionStorage.setItem("session_token", '');
        $('#hidden_' + newfieldId).val('');
        previousValue = '';
    }

    $(document).ready(function () {
        $('#vServiceAddress').keyup(function (e) {
            buildAutoComplete("vServiceAddress", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>", function (latitude, longitude, address) {
                $("#from_lat").val(latitude);
                $("#from_long").val(longitude);
                $("#from_lat_long").val("(" + latitude + "," + longitude + ")");
                $('.help-block').remove();
            });
        });

        if(typeof $.fn.tooltip.noConflict === "function") {
            var bootstrapTooltip = $.fn.tooltip.noConflict();
            $.fn.bootstrapTooltip = bootstrapTooltip;    
        }
        
        $(document).tooltip({
            position: {
                my: "center bottom-20",
                at: "center top",
                using: function(position, feedback) {
                  $(this).css( position );
                  $("<div>")
                    .addClass("arrow")
                    .addClass(feedback.vertical)
                    .addClass(feedback.horizontal)
                    .appendTo(this);
                }
            }
        });

        checkNavigatorPermissionStatus();

        setInterval(function() {
            checkNavigatorPermissionStatus();
        }, 1000);

        resizeImage("servicePage1", "attr-image-src" , "imageSrc");

    });

    function change_lang(lang) {
        document.location = 'common.php?lang=' + lang;
    }

    function renameButtonName(serviceId) {
        var toptxt = '<?= addslashes($langage_lblDataOther['LBL_MANUAL_STORE_USER_MAIN_TEXT']);?>';

        var buttonTxt = '<?= $otherButtonName; ?>';
        if (serviceId == 1) {
            buttonTxt = '<?= $foodButtonName; ?>';
            toptxt = '<?= addslashes($langage_lbl['LBL_MANUAL_STORE_USER_MAIN_TEXT']); ?>';
        }

        $('#search-head').html(toptxt);
        $("#submitbtn").text(buttonTxt);
    }

    function checkNavigatorPermissionStatus() {
        navigator.permissions && navigator.permissions.query({name: 'geolocation'}).then(function(PermissionStatus) {
            if (PermissionStatus.state == 'granted') {
                $('.detect-loc').attr('title', '<?= addslashes($langage_lbl['LBL_FETCH_LOCATION_HINT']) ?>');
            } else if (PermissionStatus.state == 'prompt') {
                // prompt - not yet grated or denied
            } else {
                $('.detect-loc').attr('title', '<?= str_replace("#SITE_NAME#", $SITE_NAME, addslashes($langage_lbl['LBL_LOCATION_BLOCKED_MSG'])) ?>');
            }
        });
    }

    function fetchLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError, {maximumAge: 0, timeout:1000, enableHighAccuracy: true});
        }
    }

    function showPosition(position) {
        var geo_latitude = position.coords.latitude;
        var geo_longitude = position.coords.longitude;
        var geo_lat_lng = "("+geo_latitude+","+geo_longitude+")";

        var oldlat = "";
        var oldlong = "";
        var oldlatlong = "";
        var oldAddress = "";
        SetGeoCookie('GEO_LATITUDE', geo_latitude, 1);
        SetGeoCookie('GEO_LONGITUDE', geo_longitude, 1);
        SetGeoCookie('GEO_LATLNG', geo_lat_lng, 1);

        $("#from_lat").val(geo_latitude);
        $("#from_long").val(geo_longitude);
        $("#from_lat_long").val(geo_lat_lng);

        getReverseGeoCode('vServiceAddress', 'from_lat_long',"<?=$_SESSION['sess_lang'];?>", geo_latitude, geo_longitude, oldlat, oldlong, oldlatlong, oldAddress, function(latitude, longitude, address){
            $('#vServiceAddress').trigger('blur');
            $('.help-block').remove();
        });
    }

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                $('.detect-loc').attr('title', '<?= str_replace("#SITE_NAME#", $SITE_NAME, addslashes($langage_lbl['LBL_LOCATION_BLOCKED_MSG'])) ?>');
                break;
            case error.POSITION_UNAVAILABLE:
                $('.detect-loc').attr('title', '<?= addslashes($langage_lbl['LBL_NO_LOCATION_FOUND_TXT']) ?>');
                break;
            case error.TIMEOUT:
                $('.detect-loc').attr('title', '<?= str_replace("#SITE_NAME#", $SITE_NAME, addslashes($langage_lbl['LBL_LOCATION_BLOCKED_MSG'])) ?>');
                break;
            case error.UNKNOWN_ERROR:
                $('.detect-loc').attr('title', '<?= str_replace("#SITE_NAME#", $SITE_NAME, addslashes($langage_lbl['LBL_LOCATION_BLOCKED_MSG'])) ?>');
                break;
        }
    }

    $('#vServiceAddress').on('keyup', function() {
        if($(this).val() == '') {
            $("#from_lat").val('');
            $("#from_long").val('');
            $("#from_lat_long").val('');
        }
    });
</script>
</body>
</html>
