<?php
$fav_icon_image = "favicon.ico";
if (file_exists($tconfig["tpanel_path"] . $logogpath . $fav_icon_image)) {
    $fav_icon_image = $tconfig["tsite_url"] . $logogpath . $fav_icon_image;
} else {
    $fav_icon_image = $tconfig["tsite_url"] . '' . ADMIN_URL_CLIENT . '/' . 'images/' . $fav_icon_image;
}
$siteUrl = $tconfig['tsite_url'];
$DEFAULT_COUNTRY_CENTER_LATITUDE = "";
$DEFAULT_COUNTRY_CENTER_LONGITUDE = "";
if (!empty($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]) && !empty($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLatitude']) && !empty($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLongitude'])) {
    $DEFAULT_COUNTRY_CENTER_LATITUDE = $country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLatitude'];
    $DEFAULT_COUNTRY_CENTER_LONGITUDE = $country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLongitude'];
}

$SYSTEM_MAP_STYLE = SYSTEM_MAP_STYLE;
?>
<!--[if IE]>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<![endif]-->
<!-- GLOBAL STYLES -->
<script>
    var GOOGLE_SEVER_API_KEY_WEB = "<?=$GOOGLE_SEVER_API_KEY_WEB?>";
    var GOOGLE_SEVER_GCM_API_KEY = "<?=$GOOGLE_SEVER_GCM_API_KEY?>";
    var DEFAULT_COUNTRY_CENTER_LATITUDE = "<?=$DEFAULT_COUNTRY_CENTER_LATITUDE?>";
    var DEFAULT_COUNTRY_CENTER_LONGITUDE = "<?=$DEFAULT_COUNTRY_CENTER_LONGITUDE?>";
    var sess_lang = "<?=$_SESSION['sess_lang'];?>";
    var strategy = "<?=$MAPS_API_REPLACEMENT_STRATEGY;?>";
    var MAPS_API_REPLACEMENT_STRATEGY = "<?=$MAPS_API_REPLACEMENT_STRATEGY;?>";
    var GOOGLE_API_REPLACEMENT_URL = "<?=GOOGLE_API_REPLACEMENT_URL;?>";
    var TSITE_DB = "<?=TSITE_DB;?>";
    var tsite_url_base = "<?=$tconfig['tsite_url'];?>";
    var WEBSERVICE_API_FILE_NAME = tsite_url_base + "<?=WEBSERVICE_API_FILE_NAME;?>";
    var SITE_PANEL_PATH = '<?= $tconfig["tsite_folder"] ?>';
    var SYSTEM_MAP_STYLE = <?= $SYSTEM_MAP_STYLE ?>;

    //MAPS_API_REPLACEMENT_STRATEGY = "Google";
    if (MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() != "ADVANCE") {
        MAPS_API_REPLACEMENT_STRATEGY = "None";
    }
    var site_default_lang = '<?= $default_lang ?>';

    function isRetinaDisplay() {
        if (window.matchMedia) {
            var mq = window.matchMedia("only screen and (min--moz-device-pixel-ratio: 1.3), only screen and (-o-min-device-pixel-ratio: 2.6/2), only screen and (-webkit-min-device-pixel-ratio: 1.3), only screen  and (min-device-pixel-ratio: 1.3), only screen and (min-resolution: 1.3dppx)");
            return (mq && mq.matches || (window.devicePixelRatio > 1));
        }
    }

    var isRatinaDisplay = isRetinaDisplay();
    var TSITE_SC_PROTOCOL = "<?php echo($tconfig['tsite_sc_protocol']); ?>";
    var TSITE_SC_HOST = "<?php echo($tconfig['tsite_sc_host']); ?>";
    var TSITE_HOST_SC_PATH = "<?php echo($tconfig['tsite_host_sc_path']); ?>";
    var TSITE_HOST_SC_PORT = "<?php echo($tconfig['tsite_host_sc_port']); ?>";
</script>
<style>
    /* Progress bar start */
    .progress-indeterminate {
        height: 2px;
        position: relative;
        bottom: 0;
        padding: 0;
        margin-bottom: 0px !important;
        overflow: hidden;
        box-shadow: none !important;
        background-color: transparent;
    }
    .progress-bar {
        background-color: #219201;
    }
    .progress-bar.indeterminate {
        box-shadow: none !important;
        position: relative;
        /* animation: progress-indeterminate 2s linear infinite; */
        animation: progress-indeterminate 2s cubic-bezier(.6, .04, .98, .34) infinite;
        background-image: -webkit-linear-gradient(-60deg, transparent 33%, rgba(0, 0, 0, .1) 66%, rgba(0, 0, 0, 0) 33%, transparent 33%),
        -webkit-linear-gradient(top, rgba(255, 255, 255, .25), rgba(241, 234, 234, 0.55)),
        -webkit-linear-gradient(left, #ffff, #1fbad6);
        border-radius: 50px;
        background-size: 35px 20px, 100% 100%, 100% 100%;
    }
    @keyframes progress-indeterminate {
        from {
            left: -85%;
            width: 85%;
        }
        to {
            left: 100%;
            width: 85%;
        }
    }
    .box_in_map {
        position: relative;
    }
    /* box_in_map use for textbox in google map  */
    .box_in_map .progress-indeterminate {
        position: absolute;
        bottom: 0px;
        height: 2px;
        width: 21.50% !important;
        top: 27px;
        right: 0px;
    }

    .box_in_map {
        position: relative !important;
    }
    .box_in_map .progress-indeterminate {
        position: absolute !important;
        left: auto;
        top: 26px;
        width: 21.50% !important;
    }
    .box_in_map .progress-indeterminate .progress-bar {
        float: right !important;
    }
    .form-column-full .progress-indeterminate {
        width: 100% !important;
    }
    .half.newrow .progress-indeterminate {
        width: 100% !important;
    }
    .map_to_createrequest .progress-indeterminate {
        position: absolute;
        top: 45px !important;
        height: 2px;
        width: 44.50% !important;
        right: 25px !important;
    }
    .drop-location .progress-indeterminate {
        height: 2px;
        width: 97% !important;
        z-index: 1;
        margin-left: 10px;
        bottom: 0px;
        right: auto;
    }
    @media screen and (max-width: 1024px) {
        .box_in_map .progress-indeterminate {
            width: 21.50% !important;
        }
        .box_in_map .progress-indeterminate {
            position: absolute !important;
            left: auto;
            top: 28px;
            width: 10.7% !important;
            right: 0 !important;
        }
    }
    @media screen and (max-width: 768px) {
        .box_in_map .progress-indeterminate {
            width: 21.50% !important;
        }
        .box_in_map .progress-indeterminate {
            position: absolute !important;
            left: auto;
            top: 28px;
            width: 10.7% !important;
            right: 0 !important;
        }
    }
    @media screen and (max-width: 480px) {
        .box_in_map .progress-indeterminate {
            width: 21.50% !important;
        }
    }
    .ui-autocomplete .ui-menu-item {
        list-style-image: none !important;
    }
    /* Progress bar end */

    .loding-action div {
        left: 50%;
        position: absolute;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    <?php if(SITE_TYPE == "Demo") { ?>
    .custom-model-body {
        word-break: break-all;
    }
    <?php } ?>
</style>
<link href="fonts/poppins/poppins.css" rel="stylesheet">
<script src="../assets/plugins/jquery-2.0.3.min.js"></script>
<script type='text/javascript' src='<?= $siteUrl; ?>assets/js/jquery-ui.min.js'></script>
<script type='text/javascript' src='<?= $siteUrl; ?>assets/js/bootbox.min.js'></script>
<script type='text/javascript' src='<?= $siteUrl; ?>assets/js/ajax_for_advance_strategy.js'></script>
<script type='text/javascript' src='<?= $siteUrl; ?>assets/js/network_js.js'></script>
<script type='text/javascript' src='<?= $siteUrl; ?>assets/js/reverse_geo_code.js'></script>
<script type='text/javascript' src='<?= $siteUrl; ?>assets/js/reverse_geo_direction_code.js'></script>
<script type="text/javascript" src="<?= $siteUrl; ?>assets/js/getDataFromApi.js"></script>
<script type="text/javascript" src="<?= $siteUrl; ?>assets/js/convertToAllLanguage.js"></script>
<link href="<?= $siteUrl; ?>assets/css/autocomplete_box.css" rel="stylesheet" type="text/css"/>
<link rel="icon" href="<?php echo $fav_icon_image; ?>" type="image/x-icon">
<link rel="stylesheet" href="../assets/plugins/bootstrap/css/bootstrap.css"/>
<?php if ($MODULES_OBJ->isEnableAdminPanelV2()) { ?>
    <link rel="stylesheet" href="css/main_v3.css"/>
<?php } else { ?>
    <link rel="stylesheet" href="css/main.css"/>
<?php } ?>
<link rel="stylesheet" href="../assets/css/theme.css"/>
<link rel="stylesheet" href="../assets/css/MoneAdmin.css"/>
<link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css"/>
<link rel="stylesheet" href="../assets/plugins/font-awesome-4.6.3/css/font-awesome.min.css"/>
<script type="text/javascript" src="<?= $siteUrl; ?>assets/js/resizeimageplace.js"></script>

<?php if ($MODULES_OBJ->isEnableAdminPanelV2()) { ?>
    <link rel="stylesheet" href="css/style_v3.css"/>
    <script src="<?= $siteUrl ?><?= $templatePath; ?>assets/js/jquery.mCustomScrollbar.js"></script>
    <link rel="stylesheet" href="<?= $siteUrl ?>assets/css/apptype/<?= $template; ?>/jquery.mCustomScrollbar.css" type="text/css">
    <?php if ($MODULES_OBJ->isEnableAdminPanelV4()) { ?>
    <link type="text/css" href="css/admin_new/admin_style_v4.css?<?php echo time(); ?>" rel="stylesheet"/>
    <?php } else { ?>
    <link type="text/css" href="css/admin_new/admin_style_new.css?<?php echo time(); ?>" rel="stylesheet"/>
    <link type="text/less" href="css/admin_new/admin_style_new.less" rel="stylesheet"/>
    <?php } ?>
    <link rel="stylesheet" href="css/admin_new/remixicon.css">
<?php } else { ?>
    <link rel="stylesheet" href="css/style.css"/>
<?php }
//it is used for langugae translation popup
$ignore_files_arr = array(
    'dashboard.php',
    'store-dashboard.php',
    'driver_document_action.php',
    'trip.php',
    'home_content_cubejekx_action.php',
    'vehicle_document_action.php',
    'home_content_servicex_action.php',
    'cab_booking.php',
    'home_content_cubejekxv3_action.php',
    'app_banner.php',
    'company_document_action.php'
);
if (stripos_arr($_SERVER['REQUEST_URI'], $ignore_files_arr) === false) { ?>
    <style type="text/css">
        .modal {
            text-align: center;
        }

        @media screen and (min-width: 768px) {
            .modal:before {
                display: inline-block;
                vertical-align: middle;
                content: " ";
                height: 100%;
            }
        }

        .modal-dialog {
            display: inline-block;
            text-align: left;
            vertical-align: middle;
        }

        .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            margin-right: 0;
            overflow-x: hidden;
        }

        .modal-body .form-group:last-child {
            margin-bottom: 0
        }

        @media (min-width: 992px) {
            .modal-lg {
                width: 900px;
            }
        }

        .modal-header h4 {
            font-weight: 600;
        }
        .setImageRatio{
            object-fit: contain !important;
            aspect-ratio: 4 / 3 !important;
            min-height: auto !important;
        }
        .resize_video , .resize_image{
            object-fit: cover !important;
            aspect-ratio: 4 / 3 !important;
            height: auto !important;
        }
    </style>
<?php } ?>
<!--END GLOBAL STYLES -->
<!-- PAGE LEVEL STYLES -->
<!-- END PAGE LEVEL  STYLES -->
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
<?= $GOOGLE_ANALYTICS ?>
<?php include_once 'main_functions.php'; ?>
<?php include_once 'main_modals.php'; ?>
<?php include_once 'common_function.php'; ?>
<?php include_once '../script_validation_message_variables.php'; ?>

<h1 style="height: 0;margin: 0;padding: 0;pointer-events: none;visibility: hidden; font-size: 0;">
    8CAnbE
</h1>