/* God's View + Tabs */
var map_gods_view;
var markers = [];
var markers_profile_img = [];
var marker_sock = [];
var bounds;
var infowindow;
$(document).ready(function () {
    gridRemoveClass();

    if(IS_ENABLE_MASTER_SERVICES.isShowDashboardGodView) {
        google.maps.event.addDomListener(window, 'load', initMap);
    }
    
});


function initMap() {
    if(IS_ENABLE_MASTER_SERVICES.isShowDashboardGodView) {
        map_gods_view = GOOGLE_MAP_OBJ.init('god_view_map');
        GetProviderDataGodsView();
    }
}

function SetServiceType(service_type){
    
    SetIcons(service_type);
    $('#eServiceType').val(service_type);
    if(service_type == "Job")
    {
        $('#status-title-pickup').text('Way to Job Location');
        $('#status-title-ongoing').text('Job Started');
    }
    else if(service_type == "Delivery")
    {
        $('#status-title-pickup').text('Way to Pickup');
        $('#status-title-ongoing').text('Way to Deliver');
    }else {
        $('#status-title-pickup').text('Way to Pickup');
        $('#status-title-ongoing').text('Way to Dropoff');
    }
    GetProviderDataGodsView();
}

function SetIcons(service_type)
{
    $('.GodViewIcons').hide();
    $('.'+service_type+'_icons').show();
    
}

function SetServiceStatus(service_status) {
    $('#eServiceStatus').val(service_status);
    GetProviderDataGodsView();
}

function GetProviderDataGodsView() {
    var eServiceType = $('#eServiceType').val();
    var eServiceStatus = $('#eServiceStatus').val();
    showMapOverlay();
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'GodsView', 'eServiceStatus': eServiceStatus, 'eServiceType': eServiceType},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        hideMapOverlay();
        if (res.action == "1") {
            var response = res.result;
            if (response.Action == "1") {
                removeAllMarkersGodsView();
                // $('ul.god_view li').find('small div').remove();
                // $('ul.god_view').find('li.active small').append('<div style="margin-bottom: 10px">(' + response.total + ')</div>');
                
                // $('#all_count').html('(' + response.all_count + ')');
                $('#not_available_count').html('(' + response.not_available_count + ')');
                $('#available_count').html('(' + response.available_count + ')');
                $('#pickup_count').html('(' + response.pickup_count + ')');
                $('#arrived_count').html('(' + response.arrived_count + ')');
                $('#dropoff_count').html('(' + response.dropoff_count + ')');
                $('ul.god_view li').find('.provider-count').show();

                var responseArr = response.data;
                if (response.total > 0) {
                    bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < responseArr.length; i++) {
                        if (eServiceType == "Job") {
                            setMarkerSPGodsView(responseArr[i]);
                        } else {
                            setMarkerGodsView(responseArr[i]);
                        }
                        bounds.extend(new google.maps.LatLng(parseFloat(responseArr[i].vLatitude), parseFloat(responseArr[i].vLongitude)));
                    }
                    map_gods_view.fitBounds(bounds);
                } else {
                    if(eServiceStatus == "Available" && $('#page_loaded').val() == "Yes") {
                        $('#page_loaded').val("No");
                        $('li[status="all"]').trigger('click');
                    }
                }
            }
            gridRemoveClass();
            setTimeOut();
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
    });
    markers.push(marker);
    initSocketGodsView(details.iDriverId, marker);
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
    });
    markers.push(marker);
    initSocketGodsView(details.iDriverId, marker);
}

function showProviderDetailsGodsView(marker) {
    hideInfoWindow();
    var provider_email;
    if(marker.data.email != "") {
        provider_email = marker.data.email;
    } else {
        provider_email = "--";
    }
    var content = "<table><tr><td rowspan='5'><img src=" + marker.data.image + " height='70' width='auto'/></td>";
    content += "<tr><td>&nbsp;&nbsp;Name: </td><td><b>" + marker.data.name + "</b></td></tr>";
    content += "<tr><td>&nbsp;&nbsp;Mobile: </td><td><b>" + marker.data.phone_no + "</b></td></tr>";
    content += "<tr><td>&nbsp;&nbsp;Email: </td><td><b>" + provider_email + "</b></td></tr>";
    if (marker.data.tracking_url != "") {
        content += "<tr><td></td><td><a href='" + marker.data.tracking_url + "' target='_blank'><b>Live tracking</b></a></td></tr>";
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

function initSocketGodsView(iDriverId, marker) {
    marker_sock = marker;
    var channel = 'ONLINE_DRIVER_LOC_' + iDriverId;
    SOCKET_OBJ.subscribe(channel, function (res) {

        var result = JSON.parse(res);
        var LatLng = new google.maps.LatLng(result.vLatitude, result.vLongitude);
        var duration = parseInt(950);
        if (duration < 0) {
            duration = 1;
        }
        setTimeout(function () {
            // marker_sock.animateTo(LatLng, {easing: 'linear', duration: duration});
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
$(document).on("click", "#earning_object .tab_new li", function () {
    $("#earning_object .tab_new li").removeClass('active');
    $(this).addClass('active');
    $('.earning_block').removeClass('active');
    $(document).find('#' + $(this).attr('data-id') + '').addClass('active');
});

$(document).on("click", "#recentTripJob .tab_new li", function () {
    $("#recentTripJob .tab_new li").removeClass('active');
    $(this).addClass('active');
    $('.recentTrip_block').removeClass('active');
    $(document).find('#' + $(this).attr('data-id') + '').addClass('active');
});
/* God's View + Tabs */

series_Data = seriesData;
var SelectedClickId = 'Trip';
if(APP_TYPE == "UBERX")
{
    var SelectedClickId = 'UBERX';
}


function DefultATotalChartLoad(ATTR_TOTAL) {
    
    setTimeout(function () {
        ATTR_TOTAL.click();
    }, 2000);
}

function abbreviateNumber(number) {
    
    //number = 100000;
    const abbreviations = { 12: 'T', 9: 'B', 6: 'M', 3: 'K', 0: '' };
    
    for (const exponent in abbreviations) {
        if (number >= Math.pow(10, exponent)) {
            const abbreviatedNumber = number / Math.pow(10, exponent);
            return parseFloat(abbreviatedNumber.toFixed(1)) + abbreviations[exponent];
        }
    }
    
    return number;
}

function createChartOptions(containerId, seriesData, seriesLabels, seriesColor, valueText) {
    return {
        exploded: {
            enabled: true,
            index: 1
        },
        series: seriesData,
        labels: seriesLabels,
        colors: seriesColor,
        chart: {
            type: 'donut',
            /*events: {
                animationEnd: function(ctx) {
                     ctx.toggleDataPointSelection(1)
                }
            },*/
            height: '100%'
        },
        noData: {
            text: 'No data available',  // Set the text to be displayed when there is no data
            align: 'center',  // Set the alignment of the text
            verticalAlign: 'middle',  // Set the vertical alignment of the text
            offsetX: 0,  // Set the horizontal offset of the text
            offsetY: 0,  // Set the vertical offset of the text
            style: {
                color: '#333',  // Set the text color
                fontSize: '14px',  // Set the font size of the text
            },
        },
        dataLabels: {
            enabled: false,
        }, 
        legend: {
            position: 'bottom', 
            horizontalAlign: 'center'
        }, 
        plotOptions: {
            pie: {
                exploded: true,
                ignoreZeroValues: false,
                donut: {
                    size: '65%',
                    background: 'transparent',
                    labels: {
                        show: true, 
                        name: {
                            show: true,
                            fontSize: '15px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: undefined,
                            offsetY: -10,
                            formatter: function (val) {
                                return val
                            },
                        }, 
                        value: {
                            show: true,
                            fontSize: '15px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: undefined,
                            offsetY: 10,
                            formatter: function (val) {
                                return val + ' ' + valueText
                            },
                        }, 
                        total: {
                            show: true,
                            showAlways: false,
                            label: 'Total',
                            fontSize: '15px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: '#373d3f',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => {
                                    return a + b
                                }, 0)
                            },
                        },
                    },
                },
            },
        }, 
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " " +valueText;
                },
            },
        }
    };
}

function createChartOptionsForNoData(containerId, seriesData, seriesLabels, seriesColor, valueText) {
    return {
        exploded: {
            enabled: true,
            index: 1
        },
        series: seriesData,
        labels: seriesLabels,
        colors: seriesColor,
        chart: {
            type: 'donut',
            height: '100%' 
        },
        noData: {
            text: 'No data available',  // Set the text to be displayed when there is no data
            align: 'center',  // Set the alignment of the text
            verticalAlign: 'middle',  // Set the vertical alignment of the text
            offsetX: 0,  // Set the horizontal offset of the text
            offsetY: 0,  // Set the vertical offset of the text
            style: {
                color: '#333',  // Set the text color
                fontSize: '14px',  // Set the font size of the text
            },
        },
        dataLabels: {
            enabled: false,
        }, 
        legend: {
            position: 'bottom', 
            horizontalAlign: 'center'
        }, 
        plotOptions: {
            pie: {
                exploded: true,
                ignoreZeroValues: false,
                donut: {
                    size: '65%',
                    background: 'transparent',
                    labels: {
                        show: true, 
                        name: {
                            show: true,
                            fontSize: '15px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: undefined,
                            offsetY: -10,
                            formatter: function (val) {
                                return val
                            },
                        }, value: {
                            show: true,
                            fontSize: '15px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: undefined,
                            offsetY: 10,
                            formatter: function (val) {
                                return 0
                            },
                        }, total: {
                            show: true,
                            showAlways: false,
                            label: 'Total',
                            fontSize: '15px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            color: '#373d3f',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => {
                                    return 0
                                }, 0)
                            },
                        },
                    },
                },
            },
        },
        tooltip: {
            enabled: false,
        
            y: {
                formatter: function (val) {
                    return val + " " +valueText;
                },
            },
        }
    };
}

function createAreaChartOptions(SeriesArr, colors, DateTimeArr) {
    return {
        series: SeriesArr,
        chart: {
            height: 260,
            type: 'area',
            offsetX: 10,
            offsetY: 0,
            toolbar: {
                show: false,
                offsetX: 0,
                offsetY: 0,
            },
            zoom: {
                enabled: false
            },
        },
        grid: {
            show: true,
        },
        colors: colors,
        legend: {
            show: true,
            showForSingleSeries: false,
            showForNullSeries: true,
            showForZeroSeries: true,
            position: 'bottom',
            horizontalAlign: 'center',
            floating: false,
            fontSize: '12px',
            fontWeight: 400,
            formatter: undefined,
            inverseOrder: false,
            width: undefined,
            height: undefined,
            tooltipHoverFormatter: undefined,
            customLegendItems: [],
            offsetX: -10,
            offsetY: -10,
            labels: {
                colors: undefined, 
                useSeriesColors: false
            },
            markers: {
                width: 15,
                height: 10,
                strokeWidth: 0,
                strokeColor: '#fff',
                fillColors: undefined,
                radius: 12,
                customHTML: undefined,
                onClick: undefined,
                offsetX: 0,
                offsetY: 0
            },
            itemMargin: {
                horizontal: 5,
                vertical: 5
            },
            onItemClick: {
                toggleDataSeries: true
            },
            onItemHover: {
                highlightDataSeries: true
            },
        },
        noData: {
            text: 'No data available',
            align: 'center',
            verticalAlign: 'middle',
            offsetX: 0,
            offsetY: 0,
            style: {
                color: '#333',
                fontSize: '14px',
            },
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            type: 'category',
            categories: DateTimeArr,
            labels: {
                show: true,
                offsetX: 0,
                offsetY: 0,
                rotate: 0,
               /* style: {
                    fontSize: '14px',
                    fontFamily: 'proxima-nova,helvetica,arial,sans-seri',
                    whiteSpace: 'nowrap',
                    paddingLeft: '10px',
                    paddingRight: '10px',
                    paddingTop: '10px',
                    paddingBottom: '10px',
                }*/
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            /*tickAmount: 4,*/
            /*range: 7,*/
        },
        yaxis: {
            show: false, labels: {
                show: false
            }, axisBorder: {
                show: false
            }, axisTicks: {
                show: false
            }
        }, 
        stroke: {
            show: true, 
            curve: 'smooth', 
            lineCap: 'butt', 
            colors: undefined, 
            width: 2, 
            dashArray: 0,
        },      
        tooltip: {
            x: {
                format: 'M'
            },
            y: {
                formatter: function (val) {
                    return abbreviateNumber(val.toFixed(2));
                }
                
            },
            enabled: true,
            /*custom: function({series, seriesIndex, dataPointIndex, w}) {
    
                console.log(w.globals.categoryLabels);
                return (
                    '<div class="arrow_box">' +
                    "<span>" +
                    w.globals.categoryLabels[dataPointIndex] +
                    ": " +
                    series[seriesIndex][dataPointIndex] +
                    "</span>" +
                    "</div>"
                );
            }*/
        },
    };
}

function removeActiveElement() {
    SERVICE_BOX.forEach(function (item) {
        item.classList.remove('service_active');
    });
    SERVICE_CHART.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_today_element_service_options() {
    SERVICE_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_service_options() {
    SERVICE_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_service_options() {
    SERVICE_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_service_options() {
    SERVICE_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}

function serviceChartByDefult() {
    
    console.log(SelectedClickId.toUpperCase());
    var chart = document.getElementById(SelectedClickId.toUpperCase() + "_CHART");
    chart.style.display = 'block';
    window[SelectedClickId.toUpperCase() + "_CHART"]();
}

//---------------------Chart 1
var chart1 = '';
var TRIP_CHART = () => {
    if (chart1) {
        var chartContainer = document.querySelector("#TRIP_CHART");
        chartContainer.innerHTML = '';
        chart1.destroy();
    }
    var options1 = '';
    
    if(series_Data.Trip_Show == "NotFoundData" || series_Data.Total_Trip_Show === "NotFoundData")
    {
        var options1 = createChartOptionsForNoData("TRIP_CHART", series_Data.Trip, series_Data.Trip_Status, series_Data.Trip_Color, 'Trips');
    }else{
        var options1 = createChartOptions("TRIP_CHART", series_Data.Trip, series_Data.Trip_Status, series_Data.Trip_Color, 'Trips');
    }
    chart1 = new ApexCharts(document.querySelector("#TRIP_CHART"), options1);
    chart1.render();
    
    //gridRemoveClass();
    
}
//---------------------Chart 2
var chart2 = '';
var MULTI_DELIVERY_CHART = () => {
    if (chart2) {
        var chartContainer = document.querySelector("#MULTI_DELIVERY_CHART");
        chartContainer.innerHTML = '';
        chart2.destroy();
    }
    
    if(series_Data.Multi_Delivery_Show == "NotFoundData" || series_Data.Total_Multi_Delivery_Show == "NotFoundData")
    {
        var options2 = createChartOptionsForNoData("MULTI_DELIVERY_CHART", series_Data.Multi_Delivery, series_Data.Multi_Delivery_Status, series_Data.Multi_Delivery_Color, 'Deliveries');
    }else {
        var options2 = createChartOptions("MULTI_DELIVERY_CHART", series_Data.Multi_Delivery, series_Data.Multi_Delivery_Status, series_Data.Multi_Delivery_Color, 'Deliveries');
    }
    
    
    chart2 = new ApexCharts(document.querySelector("#MULTI_DELIVERY_CHART"), options2);
    chart2.render();
    //gridRemoveClass();
    
}
//---------------------Chart 3
var chart3 = '';
var UBERX_CHART = () => {
    if (chart3) {
        var chartContainer = document.querySelector("#UBERX_CHART");
        chartContainer.innerHTML = '';
        chart3.destroy();
    }
    if(series_Data.UberX_Show == "NotFoundData" || series_Data.Total_UberX_Show == "NotFoundData") {
        var options3 = createChartOptionsForNoData("UBERX_CHART", series_Data.UberX, series_Data.UberX_Status, series_Data.UberX_Color, 'Jobs');
    }else{
        var options3 = createChartOptions("UBERX_CHART", series_Data.UberX, series_Data.UberX_Status, series_Data.UberX_Color, 'Jobs');
    }
    chart3 = new ApexCharts(document.querySelector("#UBERX_CHART"), options3);
    chart3.render();
    //gridRemoveClass();
    
}
//--------------------- service
var SERVICE_BOX = document.querySelectorAll('.service_box');
var SERVICE_OPTIONS = document.querySelectorAll('.Service_Options');
var SERVICE_CHART = document.querySelectorAll('.service_chart');
var SERVICE_OPTIONS_TODAY = document.querySelectorAll('.Service_Options_Today');
var SERVICE_OPTIONS_TOTAL = document.querySelectorAll('.Service_Options_Total');
var REDIRECT_URL = document.querySelectorAll('.RedirectUrl');
var SERVICE_OPTIONS_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].Service_Options');



SERVICE_BOX.forEach(function (li) {
    li.addEventListener('click', function () {
    
        if (!$(this).hasClass("ClickNotAllow")) {
            removeActiveElement();
            var clickedId = this.id;
            SelectedClickId = this.id;
            this.classList.add('service_active');
            var chart = document.getElementById(clickedId.toUpperCase() + "_CHART");
            chart.style.display = 'block';
            window[clickedId.toUpperCase() + "_CHART"]();
        }
    });
});
SERVICE_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function () {
        var options = this.getAttribute('attr-options');
        if (options == "TOTAL") {
            series_Data = seriesDataTotal;
            remove_today_element_service_options();
            show_total_element_service_options();
        } else {
            series_Data = seriesData;
            remove_total_element_service_options();
            show_today_element_service_options();
        }
        serviceChartByDefult();
    });
});
REDIRECT_URL.forEach(function (element) {
    element.addEventListener('click', function (event) {
        event.preventDefault();
        var url = this.getAttribute('link-target');
        
        window.open(url, '_blank');
    });
});
//--------------------- service
//--------------------- Ride Share
RideShare_Data = RideShareData;
var RIDESHARE_OPTIONS = document.querySelectorAll('.RideShare_Options');
var RIDESHARE_OPTIONS_TODAY = document.querySelectorAll('.RideShare_Options_Today');
var RIDESHARE_OPTIONS_TOTAL = document.querySelectorAll('.RideShare_Options_Total');
var RIDESHARE_OPTIONS_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].RideShare_Options');

function remove_today_element_rideshare_options() {
    RIDESHARE_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}


function remove_total_element_rideshare_options() {
    RIDESHARE_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_rideshare_options() {
    RIDESHARE_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_rideshare_options() {
    RIDESHARE_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}




//---------------------Chart 1
var chart4 = '';
var RIDE_SHARE_CHART = () => {
    if (chart4) {
        var chartContainer = document.querySelector("#RIDE_SHARE_CHART");
        chartContainer.innerHTML = '';
        chart4.destroy();
    }
  
    
    
    if(RideShare_Data.Show == "NotFoundData") {
      
        var options1 = createChartOptionsForNoData("RIDE_SHARE_CHART", RideShare_Data.Ride_Share, RideShare_Data.Ride_Share_Status, RideShare_Data.Ride_Share_Color, 'Trips');
    }else{
        var options1 = createChartOptions("RIDE_SHARE_CHART", RideShare_Data.Ride_Share, RideShare_Data.Ride_Share_Status, RideShare_Data.Ride_Share_Color, 'Trips');
    }
    chart4 = new ApexCharts(document.querySelector("#RIDE_SHARE_CHART"), options1);
    chart4.render();
    
    //gridRemoveClass();
}
RIDESHARE_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function () {
        var options = this.getAttribute('attr-options');
        if (options == "TOTAL") {
            RideShare_Data = RideShareDataTotal;
            remove_today_element_rideshare_options();
            show_total_element_rideshare_options();
            RIDE_SHARE_CHART();
        } else {
            RideShare_Data = RideShareData;
            remove_total_element_rideshare_options();
            show_today_element_rideshare_options();
            RIDE_SHARE_CHART();
        }
    });
});

/*if(RideShare_Data.Show == "NotFoundData") {
    RIDESHARE_OPTIONS_ATTR_TOTAL.click();
}*/


//--------------------- Ride Share
//--------------------- Admin earning
var ADMIN_EARNING_OPTIONS = document.querySelectorAll('.Admin_Earning_Options');
var ADMIN_EARNING_OPTIONS_TODAY = document.querySelectorAll('.admin_earning_options_today');
var ADMIN_EARNING_OPTIONS_TOTAL = document.querySelectorAll('.admin_earning_options_total');
var ADMIN_EARNING_OPTIONS_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].Admin_Earning_Options');
var admin_earning_data = [];
var admin_earning_data_today = [];
var admin_earning_data_total = [];

function remove_today_element_admin_earning_options() {
    ADMIN_EARNING_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_admin_earning_options() {
    ADMIN_EARNING_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_admin_earning_options() {
    ADMIN_EARNING_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_admin_earning_options() {
    ADMIN_EARNING_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}

function getAdminTodayEarnings() {
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'admin_earnings_today'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if (res.action == "1") {
            var response = res.result;
            admin_earning_data_today = admin_earning_data = response.data;
            ADMIN_EARNINGS_CHART();
        }
    })
}

function getAdminTotalEarnings() {
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'admin_earnings_total'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if (res.action == "1") {
            var response = res.result;
            admin_earning_data_total = admin_earning_data = response.data;
            if(admin_earning_data_today.adminEarning == "NotFoundData")
            {
                DefultATotalChartLoad(ADMIN_EARNING_OPTIONS_ATTR_TOTAL);
            }
        }
    })
}

ADMIN_EARNING_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function () {
        var options = this.getAttribute('attr-options');
        if (options == "TOTAL") {
            admin_earning_data = admin_earning_data_total;
            remove_today_element_admin_earning_options();
            show_total_element_admin_earning_options();
            ADMIN_EARNINGS_CHART();
        } else {
            admin_earning_data = admin_earning_data_today;
            remove_total_element_admin_earning_options();
            show_today_element_admin_earning_options();
            ADMIN_EARNINGS_CHART();
        }
    });
});
var chart5 = '';
var ADMIN_EARNINGS_CHART = () => {
    if (chart5) {
        var chartContainer = document.querySelector("#ADMIN_EARNINGS_CHART");
        chartContainer.innerHTML = '';
        chart5.destroy();
    }
    options = createAreaChartOptions(admin_earning_data.SeriesArr, admin_earning_data.colors, admin_earning_data.DateTimeArr);
    chart5 = new ApexCharts(document.querySelector("#ADMIN_EARNINGS_CHART"), options);
    chart5.render();
    //gridRemoveClass();
    
    
}
//--------------------- Admin earning
//--------------------- store deliveries
var STORE_DELIVERIES_OPTIONS = document.querySelectorAll('.store_deliveries_options');
var STORE_DELIVERIES_TODAY = document.querySelectorAll('.store_deliveries_today');
var STORE_DELIVERIES_TOTAL = document.querySelectorAll('.store_deliveries_total');
var STORE_DELIVERIES_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].store_deliveries_options');

function remove_today_element_store_deliveries_options() {
    STORE_DELIVERIES_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_store_deliveries_options() {
    STORE_DELIVERIES_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_store_deliveries_options() {
    STORE_DELIVERIES_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_store_deliveries_options() {
    STORE_DELIVERIES_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}



var store_deliveries_data = [];
var store_deliveries_data_today = [];
var store_deliveries_data_total = [];
STORE_DELIVERIES_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function () {
        var options = this.getAttribute('attr-options');
        
        if (options == "TOTAL") {
            store_deliveries_data = store_deliveries_data_total;
    
            remove_today_element_store_deliveries_options();
            show_total_element_store_deliveries_options();
            STORE_DELIVERIES_CHART();
        } else {
            remove_total_element_store_deliveries_options();
            show_today_element_store_deliveries_options();
            store_deliveries_data = store_deliveries_data_today;
            STORE_DELIVERIES_CHART();
        }
    });
});

function getTodayStoreDeliveries() {
    remove_total_element_store_deliveries_options();
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'store_deliveries_today'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if (res.action == "1") {
            var response = res.result;
            store_deliveries_data_today = store_deliveries_data = response.data;
            STORE_DELIVERIES_CHART();
            
        }
    })
}



function getTotalStoreDeliveries() {
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'store_deliveries_total'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if (res.action == "1") {
            var response = res.result;
            store_deliveries_data_total = response.data;
            if(store_deliveries_data_today.storeOrder == "NotFoundData")
            {
                DefultATotalChartLoad(STORE_DELIVERIES_ATTR_TOTAL);
            }
        }
    })
}

var chart6 = '';
var STORE_DELIVERIES_CHART = () => {
    if (chart6) {
        var chartContainer = document.querySelector("#STORE_DELIVERIES_CHART");
        chartContainer.innerHTML = '';
        chart6.destroy();
    }
    options = createAreaChartOptions(store_deliveries_data.SeriesArr, store_deliveries_data.colors, store_deliveries_data.DateTimeArr);
    chart6 = new ApexCharts(document.querySelector("#STORE_DELIVERIES_CHART"), options);
    chart6.render();
    //gridRemoveClass();
    
    
    
}
//--------------------- store deliveries
//--------------------- genie_runner
var GENIE_RUNNER_DELIVERIES_OPTIONS = document.querySelectorAll('.genie_runner_deliveries_options');
var GENIE_RUNNER_DELIVERIES_TODAY = document.querySelectorAll('.genie_runner_deliveries_today');
var GENIE_RUNNER_DELIVERIES_TOTAL = document.querySelectorAll('.genie_runner_deliveries_total');

var GENIE_RUNNER_DELIVERIES_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].genie_runner_deliveries_options');

function remove_today_element_genie_runner_deliveries_options() {
    GENIE_RUNNER_DELIVERIES_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_genie_runner_deliveries_options() {
    GENIE_RUNNER_DELIVERIES_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_genie_runner_deliveries_options() {
    GENIE_RUNNER_DELIVERIES_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_genie_runner_deliveries_options() {
    GENIE_RUNNER_DELIVERIES_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}


var genie_runner_deliveries_data = [];
var genie_runner_deliveries_data_today = [];
var genie_runner_deliveries_data_total = [];
GENIE_RUNNER_DELIVERIES_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function () {
        var options = this.getAttribute('attr-options');
        if (options == "TOTAL") {
            genie_runner_deliveries_data = genie_runner_deliveries_data_total;
            remove_today_element_genie_runner_deliveries_options();
            show_total_element_genie_runner_deliveries_options();
            GENIE_RUNNER_DELIVERIES_CHART();
        } else {
            genie_runner_deliveries_data = genie_runner_deliveries_data_today;
            remove_total_element_genie_runner_deliveries_options();
            show_today_element_genie_runner_deliveries_options();
            GENIE_RUNNER_DELIVERIES_CHART();
        }
    });
});

function getTodayGenieRunnerDeliveries() {
    remove_total_element_genie_runner_deliveries_options();
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'genie_runner_deliveries_today'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if (res.action == "1") {
            var response = res.result;
            genie_runner_deliveries_data_today = genie_runner_deliveries_data = response.data;
            GENIE_RUNNER_DELIVERIES_CHART();
            
        }
    })
}

function getTotalGenieRunnerDeliveries() {
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'genie_runner_deliveries_total'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if (res.action == "1") {
            var response = res.result;
            genie_runner_deliveries_data_total = response.data;
            
            if(genie_runner_deliveries_data_today.GenieRunnerOrder == "NotFoundData")
            {
                DefultATotalChartLoad(GENIE_RUNNER_DELIVERIES_ATTR_TOTAL);
            }
            
        }
    })
}

var chart7 = '';
var GENIE_RUNNER_DELIVERIES_CHART = () => {
    if (chart7) {
        var chartContainer = document.querySelector("#GENIE_RUNNER_DELIVERIES_CHART");
        chartContainer.innerHTML = '';
        chart7.destroy();
    }
    options = createAreaChartOptions(genie_runner_deliveries_data.SeriesArr, genie_runner_deliveries_data.colors, genie_runner_deliveries_data.DateTimeArr);
    chart7 = new ApexCharts(document.querySelector("#GENIE_RUNNER_DELIVERIES_CHART"), options);
    chart7.render();
    //gridRemoveClass();
    
}
//--------------------- genie_runner
//--------------------- Buy Sell Rent
BuySellRent_Data = BuySellRentData;
var BUY_SELL_RENT_OPTIONS = document.querySelectorAll('.buy_sell_rent_options');
var BUY_SELL_RENT_OPTIONS_TODAY = document.querySelectorAll('.buy_sell_rent_options_Today');
var BUY_SELL_RENT_OPTIONS_TOTAL = document.querySelectorAll('.buy_sell_rent_options_Total');
var BUY_SELL_RENT_OPTIONS_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].buy_sell_rent_options');

function remove_today_element_buy_sell_rent_options() {
    BUY_SELL_RENT_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_buy_sell_rent_options() {
    BUY_SELL_RENT_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_buy_sell_rent_options() {
    BUY_SELL_RENT_OPTIONS_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_buy_sell_rent_options() {
    BUY_SELL_RENT_OPTIONS_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}

BUY_SELL_RENT_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function () {
        var options = this.getAttribute('attr-options');
        if (options == "TOTAL") {
            BuySellRent_Data = BuySellRentDataTotal;
            remove_today_element_buy_sell_rent_options();
            show_total_element_buy_sell_rent_options();
            BUY_SELL_RENT_CHART();
        } else {
            BuySellRent_Data = BuySellRentData;
            remove_total_element_buy_sell_rent_options();
            show_today_element_buy_sell_rent_options();
            BUY_SELL_RENT_CHART();
        }
    });
});

var chart8 = '';
var BUY_SELL_RENT_CHART = () => {
    if (chart8) {
        var chartContainer = document.querySelector("#BUY_SELL_RENT_CHART");
        chartContainer.innerHTML = '';
        chart8.destroy();
    }
    
    if(BuySellRent_Data.Show == "NotFoundData")
    {
        var options1 = createChartOptionsForNoData("BUY_SELL_RENT_CHART", BuySellRent_Data.Buy_Sell_Rent, BuySellRent_Data.Buy_Sell_Rent_Status, BuySellRent_Data.Buy_Sell_Rent_Color, '');
    }
    else{
        var options1 = createChartOptions("BUY_SELL_RENT_CHART", BuySellRent_Data.Buy_Sell_Rent, BuySellRent_Data.Buy_Sell_Rent_Status, BuySellRent_Data.Buy_Sell_Rent_Color, '');
    }
    chart8 = new ApexCharts(document.querySelector("#BUY_SELL_RENT_CHART"), options1);
    chart8.render();
    //gridRemoveClass();
}
//--------------------- Buy Sell Rent

//--------------------- Video consulate


var VIDEO_CONSULTATION_OPTIONS = document.querySelectorAll('.video_consultation_options');

var VIDEO_CONSULTATION_TODAY = document.querySelectorAll('.video_consultation_today');
var VIDEO_CONSULTATION_TOTAL = document.querySelectorAll('.video_consultation_total');

var VIDEO_CONSULTATION_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].video_consultation_options');

function remove_today_element_video_consultation_options() {
    VIDEO_CONSULTATION_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_video_consultation_options() {
    VIDEO_CONSULTATION_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_video_consultation_options() {
    VIDEO_CONSULTATION_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_video_consultation_options() {
    VIDEO_CONSULTATION_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}
var video_consultation_data = [];
var video_consultation_data_today = [];
var video_consultation_data_total = [];
VIDEO_CONSULTATION_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function ()
    {
        var options = this.getAttribute('attr-options');
        
        if(options == "TOTAL")
        {
            video_consultation_data = video_consultation_data_total;
            remove_today_element_video_consultation_options();
            show_total_element_video_consultation_options();
            VIDEO_CONSULTATION_CHART();
            
        }else{
            video_consultation_data = video_consultation_data_today;
            remove_total_element_video_consultation_options();
            show_today_element_video_consultation_options();
            VIDEO_CONSULTATION_CHART();
        }
    });
});


function getTodayVideoConsulateService() {
    remove_total_element_video_consultation_options();
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'video_Consultation_today'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if(res.action == "1") {
            var response = res.result;
            video_consultation_data_today = video_consultation_data = response.data;
            VIDEO_CONSULTATION_CHART();
            
            
        }
    })
}


function getTotalVideoConsultationService() {
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'video_Consultation_total'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if(res.action == "1") {
            var response = res.result;
            video_consultation_data_total = response.data;
            if(video_consultation_data_today.VCService == "NotFoundData")
            {
                DefultATotalChartLoad(VIDEO_CONSULTATION_ATTR_TOTAL);
            }
            
        }
    })
}


var chart9 = '';
var VIDEO_CONSULTATION_CHART = () => {
    if (chart9) {
        var chartContainer = document.querySelector("#VIDEO_CONSULTATION_CHART");
        chartContainer.innerHTML = '';
        chart9.destroy();
    }
    options = createAreaChartOptions(video_consultation_data.SeriesArr, video_consultation_data.colors, video_consultation_data.DateTimeArr);
    chart9 = new ApexCharts(document.querySelector("#VIDEO_CONSULTATION_CHART"), options);
    chart9.render();
    //gridRemoveClass();
    
}
//--------------------- Video consulate

//--------------------- BidPost


var BID_POST_OPTIONS = document.querySelectorAll('.bid_post_options');

var BID_POST_TODAY = document.querySelectorAll('.bid_post_today');
var BID_POST_TOTAL = document.querySelectorAll('.bid_post_total');
var BID_POST_ATTR_TOTAL = document.querySelector('[attr-options="TOTAL"].bid_post_options');


function remove_today_element_bid_post_options() {
    BID_POST_TODAY.forEach(function (item) {
        item.style.display = 'none';
    });
}

function remove_total_element_bid_post_options() {
    BID_POST_TOTAL.forEach(function (item) {
        item.style.display = 'none';
    });
}

function show_today_element_bid_post_options() {
    BID_POST_TODAY.forEach(function (item) {
        item.style.display = 'block';
    });
}

function show_total_element_bid_post_options() {
    BID_POST_TOTAL.forEach(function (item) {
        item.style.display = 'block';
    });
}



var bid_post_data = [];
var bid_post_data_today = [];
var bid_post_data_total = [];
BID_POST_OPTIONS.forEach(function (li) {
    li.addEventListener('click', function ()
    {
        var options = this.getAttribute('attr-options');
        
        if(options == "TOTAL")
        {
            bid_post_data = bid_post_data_total;
            remove_today_element_bid_post_options();
            show_total_element_bid_post_options();
            BID_POST_CHART();
            
        }else{
            bid_post_data = bid_post_data_today;
            remove_total_element_bid_post_options();
            show_today_element_bid_post_options();
            BID_POST_CHART();
        }
    });
});


function getTodayBidPostTotal() {
    remove_total_element_bid_post_options();
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'bid_post_today'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if(res.action == "1") {
            var response = res.result;
            bid_post_data_today = bid_post_data = response.data;
            BID_POST_CHART();
        }
    })
}



function getTotalBidPostTotal() {
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'bid_post_total'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if(res.action == "1") {
            var response = res.result;
            bid_post_data_total = response.data;
            if(bid_post_data_today.BiddingService == "NotFoundData")
            {
                DefultATotalChartLoad(BID_POST_ATTR_TOTAL);
            }
        }
    })
}


var chart10 = '';
var BID_POST_CHART = () => {
    if (chart10) {
        var chartContainer = document.querySelector("#BID_POST_CHART");
        chartContainer.innerHTML = '';
        chart10.destroy();
    }
    options = createAreaChartOptions(bid_post_data.SeriesArr, bid_post_data.colors, bid_post_data.DateTimeArr);
    chart10 = new ApexCharts(document.querySelector("#BID_POST_CHART"), options);
    chart10.render();
    //gridRemoveClass();
    
}
//--------------------- BidPost
//--------------------- Server
var SERVER_WORKING = document.getElementById('server_working');
var SERVER_MISSING = document.getElementById('server_missing');
var SERVER_ALERTS = document.getElementById('server_alerts');
var SERVER_LOADER = document.getElementById('server_loader');
var SERVER_MAIN = document.getElementById('server_main');


function getServerData() {
    
    
   SERVER_MAIN.style.display = 'none';
    
    var ajaxData = {
        'URL': tsite_url_main_admin + 'ajax_dashboard.php',
        'AJAX_DATA': {'chart_type': 'server_status'},
        'REQUEST_DATA_TYPE': 'json'
    };
    getDataFromAjaxCall(ajaxData, function (res) {
        if(res.action == "1") {
            SERVER_LOADER.style.display = 'none';
            SERVER_MAIN.style.display = 'grid';
            
            var response = res.result;
            ServerScriptArr = response.data;
            SERVER_WORKING.innerText = response.data.server_working
            SERVER_MISSING.innerText = response.data.server_missing
            SERVER_ALERTS.innerText = response.data.alerts
            SERVER_CHART();
           
        }
    })
}


var chart11 = '';
var SERVER_CHART = () => {
    if (chart11) {
        var chartContainer = document.querySelector("#SERVER_CHART");
        chartContainer.innerHTML = '';
        chart11.destroy();
    }
    options = createChartOptions("SERVER_CHART", ServerScriptArr.server, ServerScriptArr.server_status, ServerScriptArr.server_color, '');
    chart11 = new ApexCharts(document.querySelector("#SERVER_CHART"), options);
    chart11.render();
    
    gridRemoveClass();
    setTimeOut();
}
//--------------------- Server
//--------------------- on screen load first data show

$(window).resize(async function () {
    gridRemoveClass();
    setTimeOutwithLoader();
});

function gridRemoveClass() {
    $('#masonry').masonry({
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        percentPosition: true
    });

    var GRID_ITEM_LEFT_SIDE =  $('.grid-item[style*="left: 0%"]');
    var GRID_ITEM =  $('.grid-item');

    GRID_ITEM.addClass("grid-item--width2");
    GRID_ITEM_LEFT_SIDE.removeClass("grid-item--width2");

    GRID_ITEM.removeClass("flex_column");
    GRID_ITEM_LEFT_SIDE.addClass("flex_column");
    if (GRID_ITEM.siblings('.grid-item').length === 0) {
        GRID_ITEM.addClass('grid-item-full');
    }
}
function setTimeOut() {

    var intervalId = setInterval(gridRemoveClass, 100);
    setTimeout(function () {
        clearInterval(intervalId);
    }, 2000);
}

function setTimeOutwithLoader() {
    $(".loader-default").show();
    var intervalId = setInterval(gridRemoveClass, 100);
    setTimeout(function () {
        clearInterval(intervalId);
        $(".loader-default").fadeOut("slow");
    }, 7000);
}

/*TRIP_CHART();*/

$(window).on("load", function() {
    gridRemoveClass();
    setTimeOutwithLoader();
    
   /* SetIcons('Ride');*/
    SetServiceType(SERVICETYPE_ICONS_TEXT);
    
    SERVICE_CHART_TYPE_ARR = ['Trip' ,'Multi_Delivery', 'UberX'];
    const isSeriesDataValueInArray = SERVICE_CHART_TYPE_ARR.includes(seriesData.DEFAULT_CHART);
    
    if(isSeriesDataValueInArray) {
        window[seriesData.DEFAULT_CHART.toUpperCase() + "_CHART"]();
    
        if( series_Data.Multi_Delivery_Show == "NotFoundData" && series_Data.UberX_Show == "NotFoundData" && series_Data.Trip_Show == "NotFoundData")
        {
            SERVICE_OPTIONS_ATTR_TOTAL.click();
        }
    }
    
    if(IS_ENABLE_MASTER_SERVICES.isEnableAdminEarningDashboard) {
        getAdminTodayEarnings();
        getAdminTotalEarnings();
        
        /*if(series_Data.adminEarningShow == "NotFoundData")
        {
            ADMIN_EARNING_OPTIONS_ATTR_TOTAL.click();
        }*/
    }
    if(IS_ENABLE_MASTER_SERVICES.isEnableVideoConsultingServices) {
        getTodayVideoConsulateService();
        getTotalVideoConsultationService();
    }
    if(IS_ENABLE_MASTER_SERVICES.isEnableBiddingServices) {
        getTodayBidPostTotal();
        getTotalBidPostTotal();
    }
    
    if(IS_ENABLE_MASTER_SERVICES.isEnableRentItemService || IS_ENABLE_MASTER_SERVICES.isEnableRentCarsService || IS_ENABLE_MASTER_SERVICES.isEnableRentEstateService)
    {
        BUY_SELL_RENT_CHART();
        if(BuySellRent_Data.Show == "NotFoundData")
        {
            BUY_SELL_RENT_OPTIONS_ATTR_TOTAL.click();
        }
    }
    if(IS_ENABLE_MASTER_SERVICES.isEnableRideShareService) {
        RIDE_SHARE_CHART();
        if (typeof RideShare_Data !== "undefined") {
            if (RideShare_Data.Show == "NotFoundData") {
                RIDESHARE_OPTIONS_ATTR_TOTAL.click();
            }
        
        }
    }
    
    
    
    
    if(IS_ENABLE_MASTER_SERVICES.isDeliverAllFeatureAvailable) {
        getTodayStoreDeliveries();
        getTotalStoreDeliveries();
    }
    if(IS_ENABLE_MASTER_SERVICES.isEnableAnywhereDeliveryFeature) {
        getTodayGenieRunnerDeliveries();
        getTotalGenieRunnerDeliveries();
      
    }
    
    if(IS_ENABLE_MASTER_SERVICES.isEnableServerSection) {
        getServerData();
    }

});

//--------------------- on screen load first data show
//---------------------  later booking

var SCHEDULED_BOOKING_PAGINATION = document.querySelectorAll('.scheduled_booking_pagination');
var SCHEDULED_BOOKING_RECOARD = document.querySelectorAll('.scheduled_booking_recoard');

function hide_later_booking_data() {
    SCHEDULED_BOOKING_RECOARD.forEach(function (item) {
        item.style.display = 'none';
    });
    
    SCHEDULED_BOOKING_PAGINATION.forEach(function (item) {
        item.classList.remove('color2');
    });

}

SCHEDULED_BOOKING_PAGINATION.forEach(function (span2) {
    span2.addEventListener('click', function () {
        
        var vBookingNo = this.getAttribute('attr-vBookingNo');
        hide_later_booking_data()
        this.classList.add('color2');
        document.getElementById(vBookingNo).style.display= "block";
        
    });
});
//---------------------  later booking




//-------------------- for a other admin jquery
/*$(window).ready(function() {
    if ($('.card_body').siblings().length === 0) {
        $('.card_body').parent('.admin_card_row').removeClass('_50_50');
    }
    
    $('.admin_column:not(:has(*))').parent().removeClass('_40_60');
    if($('.admin_column ._50_50:not(:has(*))').length > 0 && ( $('.column2 .card_body').length == 0  ||  $('.column2 .card_body:not(:has(*))').length > 0)) {
        $('.admin_column ._50_50:not(:has(*))').parent('.admin_column').parent('.admin_card_row').removeClass('_40_60');
    }
});*/


//-------------------- for a other admin jquery




