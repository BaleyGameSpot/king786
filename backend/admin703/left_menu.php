<?php
//$APP_DELIVERY_MODE = $CONFIG_OBJ->getConfigurations("configurations", "APP_DELIVERY_MODE");
//$RIDE_LATER_BOOKING_ENABLED = $CONFIG_OBJ->getConfigurations("configurations", "RIDE_LATER_BOOKING_ENABLED");
//$DRIVER_SUBSCRIPTION_ENABLE = $CONFIG_OBJ->getConfigurations("configurations", "DRIVER_SUBSCRIPTION_ENABLE");
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);

//Added By HJ On 16-10-2019 For Get New Feature Configuratio  Start
$getSetupData = $obj->MySQLSelect("SELECT lAddOnConfiguration FROM setup_info");

$DONATION = $DRIVER_DESTINATION = $FAVOURITE_DRIVER = $FAVOURITE_STORE = $DRIVER_SUBSCRIPTION = $GOJEK_GOPAY = $MULTI_STOPOVER_POINTS = $MANUAL_STORE_ORDER_WEBSITE = $MANUAL_STORE_ORDER_STORE_PANEL = $MANUAL_STORE_ORDER_ADMIN_PANEL = "No";
if (isset($getSetupData[0]['lAddOnConfiguration'])) {
    $addOnData = json_decode($getSetupData[0]['lAddOnConfiguration'], true);
    foreach ($addOnData as $key => $val) {
        $$key = $val;
    }
}
$leftcubexthemeon = $leftcubejekxthemeon = $leftufxserviceon = $leftdeliverallxthemeon = $leftridedeliveryxthemeon = $leftdeliveryxthemeon = $leftservicexthemeon = $leftridecxthemeon = $leftdeliverykingthemeon = $leftcubejekxv3themeon = $leftmedicalservicethemeon = 0;
if ($THEME_OBJ->isCubexThemeActive() == 'Yes' || $THEME_OBJ->isCubeXv2ThemeActive() == 'Yes') {
    $leftcubexthemeon = 1;
}
if ($THEME_OBJ->isCubeJekXThemeActive() == 'Yes' || $THEME_OBJ->isCubeJekXv2ThemeActive() == 'Yes' || $THEME_OBJ->isCJXDoctorv2ThemeActive() == 'Yes') {
    $leftcubejekxthemeon = 1;
}
if ($THEME_OBJ->isCubeJekXv3ThemeActive() == 'Yes' || $THEME_OBJ->isCubeJekXv3ProThemeActive() == 'Yes') {
    $leftcubejekxv3themeon = 1;
}
if ($THEME_OBJ->isRideCXThemeActive() == 'Yes' || $THEME_OBJ->isRideCXv2ThemeActive() == 'Yes') {
    $leftridecxthemeon = 1;
}
if ($THEME_OBJ->isDeliverallXThemeActive() == 'Yes') {
    $leftdeliverallxthemeon = 1;
}
if ($THEME_OBJ->isRideDeliveryXThemeActive() == 'Yes') {
    $leftridedeliveryxthemeon = 1;
}
if ($THEME_OBJ->isDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXv2ThemeActive() == 'Yes') {
    $leftdeliveryxthemeon = 1;
}
if ($THEME_OBJ->isDeliveryKingThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryKingXv2ThemeActive() == 'Yes') {
    $leftdeliverykingthemeon = 1;
}
if ($THEME_OBJ->isServiceXThemeActive() == 'Yes' || $THEME_OBJ->isServiceXv2ThemeActive() == 'Yes') {
    $leftservicexthemeon = 1;
}
if ($THEME_OBJ->isMedicalServicev2ThemeActive() == 'Yes') {
    $leftmedicalservicethemeon = 1;
}
$leftufxserviceon = $MODULES_OBJ->isUberXFeatureAvailable('Yes') ? 1 : 0; //add function to modules availibility
$leftrideEnable = $MODULES_OBJ->isRideFeatureAvailable('Yes') ? "Yes" : "No";
$leftdeliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable('Yes') ? "Yes" : "No";
$leftdeliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes') ? "Yes" : "No";

$leftrideshareserviceon = $MODULES_OBJ->isOnlyEnableRideSharingPro('Yes') ? "Yes" : "No";

$leftOnlyBuySellRentEnable = $MODULES_OBJ->isOnlyEnableBuySellRentPro('Yes') ? "Yes" : "No";
//Added By HJ On 16-10-2019 For Get New Feature Configuratio  End
if ($APP_TYPE == 'UberX') {

    if ($MODULES_OBJ->isEnableAdminPanelV4()) {
        $menu = include 'left_menu_uberapp_array_v4_pro.php';
    } else if ($MODULES_OBJ->isEnableAdminPanelV3Pro()) {
        $menu = include 'left_menu_uberapp_array_v3_pro.php';
    } else if ($MODULES_OBJ->isEnableAdminPanelV2()) {
        $menu = include 'left_menu_ufx_array_v3.php';
    } else {
        $menu = include 'left_menu_ufx_array.php';
    }
} else if (ONLYDELIVERALL == "Yes") {
    if ($MODULES_OBJ->isEnableAdminPanelV4()) {
        $menu = include 'left_menu_uberapp_array_v4_pro.php';
    } else if ($MODULES_OBJ->isEnableAdminPanelV3Pro()) {
        $menu = include 'left_menu_uberapp_array_v3_pro.php';
    } else if ($MODULES_OBJ->isEnableAdminPanelV2()) {
        $menu = include 'left_menu_deliverall_array_v3.php';
    } else {
        $menu = include 'left_menu_deliverall_array.php';
    }
} else if ($leftrideshareserviceon == 'Yes' && !$MODULES_OBJ->isEnableAdminPanelV4()) {
    $menu = include 'left_menu_rideshare_v3_pro.php';
} else if ($leftOnlyBuySellRentEnable == 'Yes' && !$MODULES_OBJ->isEnableAdminPanelV4()) {
	$menu = include 'left_menu_bsr_v3_pro.php';
} else {
    if ($MODULES_OBJ->isEnableAdminPanelV4()) {
        $menu = include 'left_menu_uberapp_array_v4_pro.php';
    } elseif ($MODULES_OBJ->isEnableAdminPanelV3Pro()) {
        if($_REQUEST['test2023'] == 1){
            $menu = include 'left_menu_uberapp_array_v3_pro_v2.php';
        } else{
            $menu = include 'left_menu_uberapp_array_v3_pro.php';
        }
    } elseif ($MODULES_OBJ->isEnableAdminPanelV2()) {
        $menu = include 'left_menu_uberapp_array_v3.php';
    } else {
        $menu = include 'left_menu_uberapp_array.php';
    }
}






?>
<?php if ($MODULES_OBJ->isEnableAdminPanelV4()) { ?>
<div <?php echo $SERVER_ADMIN; ?> class="search-menu">
    <input type="text" class="search-menu-input" id="search-menu" placeholder="Search" autocomplete="off">
    <i id = "clear_search_text" class="ri-close-line"></i>
</div>
<?php } ?>


<section   <?php echo $SERVER_ADMIN; ?> class="sidebar">
    <!-- Sidebar -->
    <div id="sidebar">
        <nav class="menu">
            <?php echo get_admin_nav($menu); ?>
        </nav>
    </div>
</section>
<script type="text/javascript">
    function checkHotelAddress(elem) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>checkhoteladdress.php',
            'AJAX_DATA': {adminId: '<?php echo $_SESSION["sess_iAdminUserId"] ?>'},
        };

        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                if (data != "") {
                    data = JSON.parse(data);
                    if (data[0].vAddress == "" || data[0].vAddressLat == "" || data[0].vAddressLong == "") {
                        alert('<?php echo $langage_lbl_admin["LBL_ADD_ADDRESS_NOTE"] ?>');
                        return false;
                    }
                } else {
                    alert("Hotel not found.");
                    return false;
                }
            } else {

            }
        });
    }

    /*------------------menu search-----------------*/
    search_menu('');
    function search_menu(searchText) {

        if(localStorage.getItem("menu-toggle") == 0){
            searchText = '';
        }

        if (searchText) {
            $('#clear_search_text').show();
            $(".sidebar-menu li").hide();
            $("li").removeAttr("data-parentname-search");
            $(".sidebar-menu ul").removeClass('menu-open');
            $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").show();
            $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").parents("li").show();

            console.log(localStorage.getItem("menu-toggle"));

            if(localStorage.getItem("menu-toggle") != 0)
            {
                $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").parents("ul").addClass('menu-open');
                $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").parents("ul").show();
            }else{

                $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").parents("ul").removeClass('menu-open');
                $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").children("li").children("ul").hide();

                $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").each(function () {
                    $(this).parents("ul.treeview-menu").css('display', 'none');
                    $(this).children("ul.treeview-menu").css('display', 'none');
                });

            }

            $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").children("ul").each(function () {
                $(this).children("li").css('display', 'block');
                $(this).children("li").children("ul").children("li").css('display', 'block');
            });
            var dataParentnameSearch = [];
            $(".sidebar-menu li[data-menutitle-search*='" + searchText + "']").each(function () {
                var dataParentname = $(this).attr('data-parentmenu');
                var addinParent = 0;
                if (dataParentname === undefined) {
                    addinParent = 1;
                    dataParentname = $(this).parents("li[data-parentmenu]").attr('data-parentmenu');
                }
                var isItIn = dataParentnameSearch.includes(dataParentname)
                if (isItIn) {
                } else {
                    dataParentnameSearch.push(dataParentname);
                    if (addinParent === 1) {
                        $(this).parents("li[data-parentmenu]").attr('data-parentname-search', dataParentname);
                    } else {
                        $(this).attr('data-parentname-search', dataParentname);
                    }
                }
            });
        }
        else {
            $('#clear_search_text').hide();
            $(".sidebar-menu li").removeAttr("data-parentname-search");
            $(".sidebar-menu ul").removeClass('menu-open');
           // $(".sidebar-menu ul").hide();
            $(".sidebar-menu li").show();

            var dataParentnameSearch = [];

            $(".sidebar-menu li").each(function () {
                if (!$(this).hasClass("active")) {
                    $(this).children("ul").css('display', 'none');
                }
            });
            $(".sidebar-menu li").each(function () {
                var dataParentname = $(this).attr('data-parentmenu');
                var addinParent = 0;
                if (dataParentname === undefined) {
                    addinParent = 1;
                    dataParentname = $(this).parents("li[data-parentmenu]").attr('data-parentmenu');
                }
                var isItIn = dataParentnameSearch.includes(dataParentname)
                if (isItIn) {
                } else {
                    dataParentnameSearch.push(dataParentname);
                    if (addinParent === 1) {
                        $(this).parents("li[data-parentmenu]").attr('data-parentname-search', dataParentname);
                    } else {
                        $(this).attr('data-parentname-search', dataParentname);
                    }
                }
            });
        }
    }

    /*  var SEARCH_MENU = document.getElementById("search-menu");
        function setDefaultSearchTerm(value)
        {
            SEARCH_MENU.value = value;

        }
        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i].trim();
                if (cookie.indexOf(nameEQ) === 0) {
                    return decodeURIComponent(cookie.substring(nameEQ.length, cookie.length));
                }
            }
            return null;
        }

        var storedCookieValue = getCookie('cookie_searchText');

        if (storedCookieValue) {
            var childHtmlArray = storedCookieValue.split("; ");
            if(childHtmlArray.length > 0){
                setDefaultSearchTerm(childHtmlArray[0]);
            }
        } else {
            console.log('Cookie data not found. 0111');
        }*/
    $("#search-menu").on("input", function() {
        var searchText = $(this).val().toLowerCase().trim();

        //setCookie("cookie_searchText", searchText, 1); // Expires in 1 day

        search_menu(searchText);
    });
    /*------------------menu search-----------------*/
    $(document).on("click", "#clear_search_text", function() {
        $(this).prev('#search-menu').val("");
        $("#search-menu").trigger("input");
    });
</script>