<!-- HEADER SECTION -->
<?php
include_once('../common.php');

$dashboardLink = (ONLYDELIVERALL == "Yes") ? 'dashboard.php' : 'dashboard.php'; //'store-dashboard.php'
/* Use For Demo [User can not delete the specify company id record] */
$DelCompanyIdArray = array(
    '36',
    '37'
);
$DEMO_NOT_DEL_COMPANY_ID = (SITE_TYPE == 'Demo') ? $DelCompanyIdArray : [];
/* Use For Demo */
//
/* ------- ride status ---------- */
$etypeSql = "";
if (!$MODULES_OBJ->isRideFeatureAvailable('Yes')) {
    $etypeSql .= " AND eType != 'Ride'";
}
if (!$MODULES_OBJ->isDeliveryFeatureAvailable('Yes')) {
    $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
}
if (!$MODULES_OBJ->isUberXFeatureAvailable('Yes')) {
    $etypeSql .= " AND eType != 'UberX'";
}
$newSql = "SELECT vValue FROM configurations WHERE vName='SET_MENU_ENABLE'";
$enable = $obj->MySQLSelect($newSql);
$sql = "SELECT t.tEndDate,t.vCancelReason,t.iCancelReasonId,t.fCancellationFare,t.fWalletDebit,t.iFromStationId,t.iToStationId,t.eCancelled,t.vRideNo,t.iTripId,rd.vImage,t.iDriverId,rd.vName,rd.vLastName,t.tEndDate,t.tSaddress,t.tDaddress,t.iActive,t.eType,t.isVideoCall FROM trips t JOIN register_driver rd ON t.iDriverId=rd.iDriverId WHERE t.eSystem = 'General' $etypeSql ORDER BY tEndDate DESC LIMIT 0,5";
$db_finished = $obj->MySQLSelect($sql);
/* ------------------ */
/* ------------------------------ latest order ------------------------------ */
$cancelDriverOrder = $MODULES_OBJ->isEnableCancelDriverOrder() ? "Yes" : "No";
$sql = "SELECT os.vStatus,o.eAskCodeToUser,o.vRandomCode,o.vCancelReasonDriver,o.eCancelledbyDriver,o.iOrderId, o.tOrderRequestDate , o.vOrderNo, o.fTotalGenerateFare, o.iStatusCode, o.eBuyAnyService, o.fCommision, o.iUserId, o.iDriverId, o.iCompanyId, o.fNetTotal, ru.vName, ru.vLastName FROM orders as o LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode JOIN register_user as ru  on o.iUserId=ru.iUserId GROUP BY o.iOrderId ORDER BY iOrderId DESC Limit 0,5";
$latest_order = $obj->MySQLSelect($sql);
/* ------------------------------ latest order ------------------------------ */
/* ---------------------------- latest contactus ---------------------------- */
$sql = "SELECT * FROM `contactus` ORDER BY `iContactusId` DESC LIMIT 0,5";
$latest_contactus = $obj->MySQLSelect($sql);
/* ---------------------------- latest contactus ---------------------------- */
/* ---------------------------- latest contactus ---------------------------- */
$sql = "SELECT CONCAT(u.vName,' ',u.vLastName) as userName, CONCAT(d.vName,' ',d.vLastName) as driverName, u.vEmail as useremail, d.vEmail as driveremail,CONCAT('(+',u.vPhoneCode,') ',u.vPhone) as userphone, CONCAT('(+',d.vCode,')',d.vPhone) as driverphone, ecd.iEmergencyId,ecd.vFromUserType,ecd.iTripId,t.vRideNo,t.eType,ecd.tRequestDate,ecd.iUserId,ecd.iDriverId FROM `emergency_contact_data` ecd
LEFT JOIN trips t ON t.iTripId = ecd.`iTripId`
LEFT JOIN register_user u ON u.iUserId = ecd.`iUserId`
LEFT JOIN register_driver d ON d.iDriverId = ecd.`iDriverId`
WHERE 1 = 1 GROUP BY ecd.iTripId,ecd.vFromUserType ORDER BY `iEmergencyContactId` DESC LIMIT 0,5";
$latest_sos = $obj->MySQLSelect($sql);
/* ---------------------------- latest contactus ---------------------------- */
/* ---------------------------- Payment Requests ---------------------------- */
$sql = "SELECT pr.*,rd.vName as firstname,rd.vLastName as lastname , ru.vName as user_name, ru.vLastName as user_lastname,rd.vEmail as driveremail,ru.vEmail as usermail FROM payment_requests as pr LEFT JOIN register_driver as rd on rd.iDriverId=pr.iDriverId LEFT JOIN register_user as ru on ru.iUserId=pr.iUserId WHERE 1 = 1 AND eMarkAsDone = '' ORDER BY iPaymentRequestsId DESC LIMIT 0, 5";
$latest_payment_requests = $obj->MySQLSelect($sql);
/* ---------------------------- Payment Requests ---------------------------- */
/* Order Status */
if (ONLYDELIVERALL == 'Yes') {
    $limit = 'LIMIT 0,4';
} else {
    $limit = 'LIMIT 0,2';
}
$sql = "SELECT c.vCompany,o.iOrderId,o.vOrderNo,c.vCaddress,c.vImage,os.vStatus,c.iCompanyId,o.tOrderRequestDate,vServiceAddress FROM orders o JOIN user_address as ua ON o.iUserAddressId=ua.iUserAddressId LEFT JOIN company c on o.iCompanyId=c.iCompanyId LEFT JOIN order_status as os on o.iStatusCode=os.iStatusCode  LEFT JOIN register_user ru on o.iUserId=ru.iUserId ORDER BY iOrderId DESC $limit";
$db_finished_orders = $obj->MySQLSelect($sql);
/* Order Status */

$logo = "logo.png";
$logosmall = "admin-logo-small.png";
$adminUrl = $tconfig["tsite_url_main_admin"];
if (file_exists($tconfig["tpanel_path"] . $logogpath . $logo)) {
    $logo = $tconfig["tsite_url"] . $logogpath . $logo;
} else {
    $logo = $adminUrl . 'images/' . $logo;
}
if (file_exists($tconfig["tpanel_path"] . $logogpath . $logosmall)) {
    $logosmall = $tconfig["tsite_url"] . $logogpath . $logosmall;
} else {
    $logosmall = $adminUrl . 'images/' . $logosmall;
}
$vGroup = "";
if (isset($_SESSION['sess_iGroupId'])) {
    $admin_group = $obj->MySQLSelect("SELECT vGroup FROM admin_groups WHERE iGroupId = '" . $_SESSION['sess_iGroupId'] . "'");
    $vGroup = $admin_group[0]['vGroup'];
}
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';

/*------------------for server admin-----------------*/


list($SERVER_ADMIN, $SCRIPT_ARRAY_FOR_PERMISSION_LEFT_MENU) = enable_left_menu();
/*------------------for server admin-----------------*/


?>
<script>
    var _system_admin_url = '<?php echo $tconfig["tsite_url_main_admin"]; ?>';
    console.log(_system_admin_url);

</script>
<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
<!-- <script src="js/New/perfect-scrollbar.js"></script> -->
<!-- END GLOBAL SCRIPTS -->
<!-- END HEADER SECTION -->
<?php if (!$MODULES_OBJ->isEnableAdminPanelV2()) { ?>
    <link type="text/css" href="css/admin_new/admin_style.css" rel="stylesheet"/>
<?php } ?>
<!--<link type="text/css" href="css/adminLTE/AdminLTE.min.css" rel="stylesheet" />-->
<input type="hidden" name="baseurl" id="baseurl" value="">
<div class="wrapper1">
    <div class="new-mobile001">
        <nav class="navbar navbar-inverse navbar-fixed-top" style="padding:7px 0;">
            <a data-original-title="Show/Hide Menu" data-placement="bottom" data-tooltip="tooltip"
               class="accordion-toggle btn btn-primary btn-sm visible-xs" data-toggle="collapse" href="#sidebar"
               id="menu-toggle">
                <i class="icon-align-justify"></i>
            </a>
        </nav>
    </div>
    <?php if (!$MODULES_OBJ->isEnableAdminPanelV2()) { ?>
        <header class="main_header">
            <div class="header clearfix">
                <a href="<?= $dashboardLink; ?>" title="" class="logo">
                    <span class="logo-mini"> <img src="<?php echo $logosmall; ?>" alt=""/> </span>
                    <span class="logo-lg minus"> <img src="<?php echo $logo; ?>" alt=""/> </span>
                </a>
                <nav class="navbar-static-top">
                    <a <?php echo $SERVER_ADMIN; ?> class="sidebar-toggle" href="javascript:void(0);" data-toggle="tooltip" data-placement="right"
                       title="Toggle Menu"></a>
                    <span style="margin: 26px 0 0 20px;float: left;"><?php echo clearName($_SESSION['sess_vAdminFirstName'] . " " . $_SESSION['sess_vAdminLastName']); ?></span>
                </nav>
                <div>
                    <a href="logout.php" title="Logout" class="header-top-button">
                        <img src="images/logout-icon1.png" alt=""/>
                        Logout
                    </a>
                    <!-- <div id="google_translate_element" class="header-top-translate-button"></div> -->
                </div>
            </div>
        </header>
        <div class="main-sidebar">
            <?php include('left_menu.php'); ?>
        </div>
    <?php }
    else { ?>
        <header class="main_header">
            <div class="header">
                <div class="actionlist">

                    <?php if (isset($SCRIPT_ARRAY_FOR_PERMISSION_LEFT_MENU['MENU_MINIZE'])) { ?>
                        <a href="<?= $dashboardLink; ?>" title="" class="logo header-logo">
                            <span class="logo-lg minus small_logo"> <img src="<?php echo $logosmall; ?>"
                                                                         alt=""/> </span>
                        </a>
                    <?php } else { ?>
                        <a <?php echo $SERVER_ADMIN; ?> class="sidebar-toggle" href="javascript:void(0);"
                                                        data-toggle="tooltip" data-placement="bottom"
                                                        title="Toggle Sidebar"></a>
                    <?php } ?>

                    <span class="adminname"><?php echo clearName($_SESSION['sess_vAdminFirstName'] . " " . $_SESSION['sess_vAdminLastName']); ?><span><?= $vGroup ?></span></span>
                    <?php /*<a href="#"><i data-v-3fe659be="" class="ri-calendar-line"></i></a>
                <a href="#"><i data-v-3fe659be="" class="ri-message-line"></i></a>
                <a href="#"><i data-v-3fe659be="" class="ri-mail-line"></i></a>
                <a href="#"><i data-v-3fe659be="" class="ri-checkbox-line"></i></a>
                <a href="#"><i data-v-3fe659be="" class="ri-star-line"></i></a>*/ ?>
                </div>
                <div class="d-flex align-center flex-start">
                    <div class="actionlist">
                        <?php if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] == 'hotel') { ?>
                            <a href="profile.php" data-toggle="tooltip" title="Profile">
                                <i class="ri-user-line"></i>
                            </a>
                        <?php } else { ?>
                            <?php if ($userObj->hasPermission('manage-profile')) { ?>
                                <a href="admin_action.php?id=<?= $_SESSION['sess_iAdminUserId'] ?>"
                                   data-toggle="tooltip" title="Profile">
                                    <i class="ri-user-line"></i>
                                </a>
                            <?php } ?>
                        <?php } ?>
                        <?php if (isset($_SESSION['sess_iGroupId'])) { ?>
                            <?php if ($userObj->hasPermission('view-sos-request-report') &&  $onlyBSREnable != 'Yes') { ?>
                                <a href="emergency_contact_data.php" data-toggle="tooltip" title="SOS Requests">
                                    <i class="ri-alert-line"></i>
                                </a>
                            <?php } ?>
                            <?php if ($userObj->hasPermission('view-contactus-report')) { ?>
                                <a href="contactus.php" data-toggle="tooltip" title="Contact Us Requests">
                                    <i class="ri-draft-line"></i>
                                </a>
                            <?php } ?>
                            <?php if ($userObj->hasPermission('manage-general-settings')) { ?>
                                <a href="general.php" data-toggle="tooltip" title="Settings">
                                    <i class="ri-settings-4-line"></i>
                                </a>
                            <?php } ?>
                        <?php } ?>
                        <a href="logout.php" data-toggle="tooltip" title="Logout">
                            <i class="ri-shut-down-line"></i>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <div <?php echo $SERVER_ADMIN; ?> class="main-sidebar">
            <a href="<?= $dashboardLink; ?>" title="" class="logo">
                <span class="logo-lg minus big_logo"> <img src="<?php echo $logo; ?>" alt=""/> </span>
                <span class="logo-lg minus small_logo"> <img src="<?php echo $logosmall; ?>" alt=""/> </span>
            </a>
            <?php include('left_menu.php'); ?>
        </div>
    <?php } ?>
    <div class="loader-default"></div>
    <script>

        var DEFAULT_LANG = "<?= $default_lang ?>";
        var IS_ENABLE_ADMIN_PANEL_V4 = "<?= $MODULES_OBJ->isEnableAdminPanelV4() ?>";
        /*------------------for server admin-----------------*/
        if(IS_ENABLE_ADMIN_PANEL_V4) {
        var SCRIPT_ARRAY_FOR_PERMISSION_LEFT_MENU = <?php echo json_encode($SCRIPT_ARRAY_FOR_PERMISSION_LEFT_MENU); ?>;
        const MENU_MINIZE = SCRIPT_ARRAY_FOR_PERMISSION_LEFT_MENU.MENU_MINIZE;
        $(window).on('load', function() {
            if(MENU_MINIZE) {
                setMenuEnable(0)
                $("#content").attr('style', 'margin-left: 0% !important');
                $(".main_header").css('left', '0');
            }
        });
        }
        /*------------------for server admin-----------------*/



        function setMenuEnable(id) {
            /*var ajaxData = {
                'URL': _system_admin_url + "setMenuEnable.php",
                'AJAX_DATA': "data=" + id,
                'REQUEST_DATA_TYPE': 'html',
                'REQUEST_CACHE': false
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                } else {

                }
            });*/

            localStorage.setItem("menu-toggle", id);
            $("#search-menu").trigger("input");
        }

        $(document).ready(function () {
            $.sidebarMenu($('.sidebar-menu'));
            if(localStorage.getItem("menu-toggle") == 0) {
            $("body").addClass("sidebar-minize");
            $("body").addClass("sidebar_hide");
            $("body").addClass("sidebar-collapse");
            } else {
            $("body").removeClass("sidebar_hide");
            $("body").removeClass("sidebar-minize");
            $("body").removeClass("sidebar-collapse");
            }
        });
        $.sidebarMenu = function (menu) {
            var animationSpeed = 300;
            $(menu).on('click', 'li a', function (e) {
                if($('.desktopmod').length > 0) {
                    var $this = $(this);
                    var checkElement = $this.next();
                    if (checkElement.is('.treeview-menu') && checkElement.is(':visible')) {
                        checkElement.slideUp(animationSpeed, function () {
                            checkElement.removeClass('menu-open');
                        });
                        checkElement.parent("li").removeClass("active");
                    }
                    //If the menu is not visible
                    else if ((checkElement.is('.treeview-menu')) && (!checkElement.is(':visible'))) {
                        //Get the parent menu
                        var parent = $this.parents('ul').first();
                        //Close all open menus within the parent
                        var ul = parent.find('ul:visible').slideUp(animationSpeed);
                        //Remove the menu-open class from the parent
                        ul.removeClass('menu-open');
                        //Get the parent li
                        var parent_li = $this.parent("li");
                        //Open the target menu and add the menu-open class
                        checkElement.slideDown(animationSpeed, function () {
                            //Add the class active to the parent li
                            checkElement.addClass('menu-open');
                            parent.find('li.active').removeClass('active');
                            parent_li.addClass('active');
                        });
                    }
                    //if this isn't a link, prevent the page from being redirected
                    if (checkElement.is('.treeview-menu')) {
                        e.preventDefault();
                    }
                }
            });
        }
    </script>
    <!-- /footer -->
</div>
<!-- END HEADER SECTION -->
<script type="text/javascript">
    $(document).ready(function () {
        if ($('#messagedisplay')) {
            $('#messagedisplay').animate({opacity: 1.0}, 2000)
            $('#messagedisplay').fadeOut('slow');
        }
        //for side bar menu
        $(".content-wrapper").css({'min-height': ($(".wrapper .main-sidebar").height() + 'px')});

        if($('.sidebar_hide').length > 0) {
            $("body").removeClass("desktopmod");
            $('.small_logo').show();
            $('.big_logo').hide();
        } else {
            $("body").addClass("desktopmod");
            $('.big_logo').show();
            $('.small_logo').hide();
        }

        $('.sidebar-toggle').click(function () {
            $("body").toggleClass("sidebar_hide");
            if ($("body").hasClass("sidebar_hide")) {
                $("body").addClass("sidebar-minize");
                $("body").addClass("sidebar-collapse");
                $("body").removeClass("desktopmod");
                $('.big_logo').fadeOut(100, function() {
                    $('.small_logo').fadeIn(300);
                });
                setMenuEnable(0);
            } else {
                $("body").removeClass("sidebar-minize");
                $("body").removeClass("sidebar-collapse");
                $("body").addClass("desktopmod");
                $('.big_logo').fadeIn(300);
                $('.small_logo').hide();
                setMenuEnable(1);
            }
        });
        $("#content").addClass('content_right');
        if ($(window).width() < 800) {
            $('.sidebar-toggle').click(function () {
                $("body").toggleClass("sidebar_hide");
                if ($("body").hasClass("sidebar_hide")) {
                    $("body").addClass("sidebar-open");
                    $("body").removeClass("sidebar-collapse");
                    $('.small_logo').show();
                   $('.big_logo').hide()
                    setMenuEnable(0);
                } else {
                    $("body").removeClass("sidebar-open");
                    $("body").removeClass("sidebar-collapse");
                    $('.big_logo').show()
                    $('.small_logo').hide();
                    setMenuEnable(1);
                }
            });
        }
        if ($(window).width() < 900) {
            $("body").removeClass("sidebar-collapse");
            $('.sidebar-toggle').click(function () {
                $('body').toggleClass('sidebar-open');
                if (sessionStorage.sidebarin == 0) {
                    $("body").addClass("sidebar-minize");
                    $("body").removeClass("sidebar-collapse");
                } else {
                    $("body").removeClass("sidebar-minize");
                    $("body").removeClass("sidebar-collapse");
                }
            });
        }
    });
</script>
<script type="text/javascript">
    //===== Hide/show Menubar =====//
    $('.fullview').click(function () {
        $("body").toggleClass("clean");
        $('#sidebar').toggleClass("show-sidebar mobile-sidebar");
        $('#content').toggleClass("full-content");
    });
    $(window).resize(function () {
        if ($(window).width() < 900) {
            if (sessionStorage.sidebarin == 0) {
                $("body").addClass("sidebar-minize");
                $("body").removeClass("sidebar-collapse");
            } else {
                $("body").removeClass("sidebar-minize");
                $("body").removeClass("sidebar-collapse");
            }
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
    $(window).load(function () {
        //setTimeout(function() {
            $(".loader-default").fadeOut("slow");
        //}, 5000);
    });
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script type="text/javascript">
    function googleTranslateElementInit() {
        //new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
    }
</script>

