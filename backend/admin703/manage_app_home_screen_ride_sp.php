<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-app-home-screen-view')) {
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'ManageAppHomePage';
$tbl_name = "app_home_screen_view";
$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$userEditDataArr = $db_data_arr = array();
$db_data = $obj->MySQLSelect("SELECT * FROM $tbl_name");
foreach ($db_data as $db_value) {
    $ViewType = !empty($db_value['eServiceType']) ? $db_value['eServiceType'] : $db_value['eViewType'];
    $db_data_arr[$ViewType] = $db_value;
}

/* Taxi Services */
$vRideServiceTitleArr = json_decode($db_data_arr['Ride']['vTitle'], true);
foreach ($vRideServiceTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vRideServiceTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$tServiceDetailsRide = $db_data_arr['Ride']['tServiceDetails'];
$tServiceDetailsRideArr = array();
if (!empty($tServiceDetailsRide)) {
    $tServiceDetailsRideArr = json_decode($tServiceDetailsRide, true);
}
$rideData = $obj->MySQLSelect("SELECT iVehicleCategoryId, iDisplayOrder,vCategory_" . $default_lang . " as vCategory FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND eCatType IN ('Ride', 'Rental', 'RidePool', 'RideSchedule', 'MotoRide') ORDER BY iDisplayOrder, iVehicleCategoryId ASC ");

/* General Banners */
$bannerData = $obj->MySQLSelect("SELECT * FROM banners WHERE iServiceId = 0 AND vCode = '$default_lang' AND eType = 'General' AND eFor = 'General' AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 0,3");

/* On-Demand Services */
if ($MODULES_OBJ->isUberXFeatureAvailable()) {
    $vOnDemandServiceTitleArr = json_decode($db_data_arr['UberX']['vTitle'], true);
    foreach ($vOnDemandServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vOnDemandServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vOnDemandServiceSubTitleArr = json_decode($db_data_arr['UberX']['vSubtitle'], true);
    foreach ($vOnDemandServiceSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vOnDemandServiceSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $tServiceDetails = $db_data_arr['UberX']['tServiceDetails'];
    $tServiceDetailsUberXArr = array();
    if (!empty($tServiceDetails)) {
        $tServiceDetailsUberXArr = json_decode($tServiceDetails, true);
    }
    $ufxData = $obj->MySQLSelect("SELECT iVehicleCategoryId, vCategory_$default_lang as vCategoryName FROM " . $sql_vehicle_category_table_name . " WHERE eCatType = 'ServiceProvider' AND eVideoConsultEnable = 'No' AND iParentId='0' AND eStatus = 'Active' ORDER BY vCategoryName");
}

/* Promotional Banner */
$promotionalBanner = $obj->MySQLSelect("SELECT iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " WHERE ePromoteBanner = 'Yes' AND eStatus = 'Active' ");
if (!empty($promotionalBanner) && scount($promotionalBanner) > 0) {
    $promotionalCategoryId = $promotionalBanner[0]['iVehicleCategoryId'];
} else {
    $promotionalBanner = $obj->MySQLSelect("SELECT iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 1");
    $promotionalCategoryId = $promotionalBanner[0]['iVehicleCategoryId'];
}

$promotional_banner_data = $obj->MySQLSelect("SELECT vImage FROM banners WHERE vCode = '$default_lang' AND iVehicleCategoryId = '" . $promotionalCategoryId . "' AND eType = 'Promotion'");

/* Other Services */
$vOtherServiceTitleArr = json_decode($db_data_arr['Other']['vTitle'], true);
foreach ($vOtherServiceTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vOtherServiceTitle_', $key);
    $userEditDataArr[$key] = $value;
}

$labelsRide = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_TAXI_ADD_A_STOP', 'LBL_TAXI_ADD_A_STOP_DESC', 'LBL_TAXI_ADD_A_STOP_PAGE_TITLE', 'LBL_TAXI_ADD_A_STOP_PAGE_DESC', 'LBL_TAXI_POOL_TITLE', 'LBL_TAXI_POOL_DESC', 'LBL_TAXI_POOL_PAGE_TITLE', 'LBL_TAXI_POOL_PAGE_DESC', 'LBL_TAXI_BID_TITLE', 'LBL_TAXI_BID_DESC', 'LBL_TAXI_BID_PAGE_TITLE', 'LBL_TAXI_BID_PAGE_DESC', 'LBL_SHARE_YOUR_RIDE_TITLE', 'LBL_SHARE_YOUR_RIDE_DESC', 'LBL_SHARE_YOUR_RIDE_PAGE_TITLE', 'LBL_SHARE_YOUR_RIDE_PAGE_DESC') ");

$AddStopTitleArr = $AddStopSubTitleArr = $AddStopPageTitleArr = $AddStopPageSubTitleArr = $TaxiPoolTitleArr = $TaxiPoolSubTitleArr = $TaxiPoolPageTitleArr = $TaxiPoolPageSubTitleArr = $TaxiBidTitleArr = $TaxiBidSubTitleArr = $TaxiBidPageTitleArr = $TaxiBidPageSubTitleArr = $ShareRideTitleArr = $ShareRideSubTitleArr = $ShareRidePageTitleArr = $ShareRidePageSubTitleArr = array();
foreach ($labelsRide as $label) {
    if($label['vLabel'] == 'LBL_TAXI_ADD_A_STOP') {
        $AddStopTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_ADD_A_STOP_DESC') {
        $AddStopSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_ADD_A_STOP_PAGE_TITLE') {
        $AddStopPageTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_ADD_A_STOP_PAGE_DESC') {
        $AddStopPageSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_POOL_TITLE') {
        $TaxiPoolTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_POOL_DESC') {
        $TaxiPoolSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_POOL_PAGE_TITLE') {
        $TaxiPoolPageTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_POOL_PAGE_DESC') {
        $TaxiPoolPageSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_BID_TITLE') {
        $TaxiBidTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_BID_DESC') {
        $TaxiBidSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_BID_PAGE_TITLE') {
        $TaxiBidPageTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_TAXI_BID_PAGE_DESC') {
        $TaxiBidPageSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_SHARE_YOUR_RIDE_TITLE') {
        $ShareRideTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_SHARE_YOUR_RIDE_DESC') {
        $ShareRideSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_SHARE_YOUR_RIDE_PAGE_TITLE') {
        $ShareRidePageTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_SHARE_YOUR_RIDE_PAGE_DESC') {
        $ShareRidePageSubTitleArr[$label['vCode']] = $label['vValue'];

    }
}

$OTHER_SERVICES_ARR = array(
    array(
        'ServiceTitle' => $AddStopTitleArr, 
        'ServiceDesc' => $AddStopSubTitleArr, 
        'ServicePageTitle' => $AddStopPageTitleArr, 
        'ServicePageDesc' => $AddStopPageSubTitleArr, 
        'ManageServiceKey' => 'AddStop', 
        'HiddenInput' => 'saveAddStop'
    ), 
    array(
        'ServiceTitle' => $TaxiPoolTitleArr, 
        'ServiceDesc' => $TaxiPoolSubTitleArr, 
        'ServicePageTitle' => $TaxiPoolPageTitleArr, 
        'ServicePageDesc' => $TaxiPoolPageSubTitleArr, 
        'ManageServiceKey' => 'TaxiPool', 
        'HiddenInput' => 'saveTaxiPool'
    )
);

if($MODULES_OBJ->isEnableTaxiBidFeature()) {
    $OTHER_SERVICES_ARR[] = array(
        'ServiceTitle' => $TaxiBidTitleArr, 
        'ServiceDesc' => $TaxiBidSubTitleArr, 
        'ServicePageTitle' => $TaxiBidPageTitleArr, 
        'ServicePageDesc' => $TaxiBidPageSubTitleArr, 
        'ManageServiceKey' => 'TaxiBid', 
        'HiddenInput' => 'saveTaxiBid'
    );
} else {
    $OTHER_SERVICES_ARR[] = array(
        'ServiceTitle' => $ShareRideTitleArr, 
        'ServiceDesc' => $ShareRideSubTitleArr, 
        'ServicePageTitle' => $ShareRidePageTitleArr, 
        'ServicePageDesc' => $ShareRidePageSubTitleArr, 
        'ManageServiceKey' => 'ShareRide', 
        'HiddenInput' => 'saveShareRide'
    );
}

$vImageOld['AddStop'] = $vImageOld['ShareRide'] = $vImageOld['TaxiPool'] = $vImageOld['TaxiBid'] = $vInfoImageOld['AddStop'] = $vInfoImageOld['ShareRide'] = $vInfoImageOld['TaxiPool'] = $vInfoImageOld['TaxiBid'] = "";
if (!empty($db_data_arr['Other']['tServiceDetails'])) {
    $tServiceDetailsArr = json_decode($db_data_arr['Other']['tServiceDetails'], true);

    $vImageOld['AddStop'] = $tServiceDetailsArr['AddStop']['vImage'];
    $vImageOld['ShareRide'] = $tServiceDetailsArr['ShareRide']['vImage'];
    $vImageOld['TaxiPool'] = $tServiceDetailsArr['TaxiPool']['vImage'];
    $vImageOld['TaxiBid'] = $tServiceDetailsArr['TaxiBid']['vImage'];


    $vInfoImageOld['AddStop'] = $tServiceDetailsArr['AddStop']['vInfoImage'];
    $vInfoImageOld['ShareRide'] = $tServiceDetailsArr['ShareRide']['vInfoImage'];
    $vInfoImageOld['TaxiPool'] = $tServiceDetailsArr['TaxiPool']['vInfoImage'];
    $vInfoImageOld['TaxiBid'] = $tServiceDetailsArr['TaxiBid']['vInfoImage'];
}
?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Manage App Home Screen</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/fancybox.css"/>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <style>
        .section-title {
            font-size: 24px;
            font-weight: 600;
        }

        .underline-section-title {
            display: block;
            border-top: 5px solid #799FCB;
            width: 75px;
            margin: 0 0 15px 0;
        }

        .save-section-btn {
            background-color: #000000;
            border-color: #000000;
            font-size: 18px;
            min-width: 120px;
            outline: none !important;
        }

        .save-section-btn:hover, .save-section-btn:focus, .save-section-btn:active, .save-section-btn:disabled {
            background-color: #000000;
            border-color: #000000;
        }

        .paddingbottom-10 {
            padding-bottom: 10px !important;
        }

        .paddingbottom-0 {
            padding-bottom: 0 !important;   
        }

        .promo-banner .banner-img-block {
            justify-content: center;
            grid-template-columns: auto;
        }

        /* Style the tab */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        /* Style the buttons that are used to open the tab content */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
            font-weight: 500;
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #dddddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #cccccc;
        }

        /* Style the tab content */
        .tabcontent {
            display: none;
            padding-top: 15px;
        }

        .display-tab-content {
            display: block;
        }

        .manage-banner-section .service-img-block {
            display: inline-block;
            justify-content: center;
            background-color: #ffffff;
            padding: 15px 0 10px 15px;
            margin-bottom: 15px;
        }

        .service-preview-img {
            width: auto;
            display: inline-block;
            margin-right: 15px;
            vertical-align: top;
        }

        .manage-banner-section .manage-icon-btn {
            display: block;
            margin: auto;
        }

        .service-img-title {
            font-size: 12px;
            font-weight: 600;
            word-break: break-word;
            width: 60px;
            margin-top: 5px;
        }

        .manage-banner-section .manage-banner-btn {
            margin-top: 10px;
        }

        .img-note {
            display: block;
            margin-top: 10px;
            width: max-content;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Manage App Home Screen</h2>
                </div>
            </div>
            <hr/>
            <?php include('valid_msg.php'); ?>
            <div class="body-div">
                <div class="form-group">
                    <div class="show-help-section section-title">General Banners</div>
                    <div class="underline-section-title"></div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="manage-banner-section">
                                <?php if (scount($bannerData) > 0) { ?>
                                    <div class="banner-img-block">
                                        <?php foreach ($bannerData as $app_banner_img) { ?>
                                            <div class="banner-img">
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=400&src=' . $tconfig['tsite_upload_images'] . $app_banner_img['vImage']; ?>">
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="no-banner">
                                        No Banner Found.
                                    </div>
                                <?php } ?>
                                <a href="<?= $tconfig['tsite_url_main_admin'] ?>banner.php" class="manage-banner-btn" target="_blank">Manage Banners for App Home Screen</a>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <div class="show-help-section section-title">Taxi Services</div>
                    <div class="underline-section-title"></div>
                    <?php if (scount($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vRideServiceTitle_Default"
                                       name="vRideServiceTitle_Default"
                                       value="<?= $userEditDataArr['vRideServiceTitle_' . $default_lang]; ?>"
                                       data-originalvalue="<?= $userEditDataArr['vRideServiceTitle_' . $default_lang]; ?>"
                                       readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editRideServiceTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="RideServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="rideservice_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vRideServiceTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vRideServiceTitle_' . $vCode;
                                            $$vValue = $userEditDataArr[$vValue];
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <?php
                                            $page_title_class = 'col-lg-12';
                                            if (scount($db_master) > 1) {
                                                if ($EN_available) {
                                                    if ($vCode == "EN") {
                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                    }
                                                } else {
                                                    if ($vCode == $default_lang) {
                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title (<?= $vTitle; ?>
                                                        ) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="<?= $page_title_class ?>">
                                                    <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                           id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                           data-originalvalue="<?= $$vValue; ?>"
                                                           placeholder="<?= $vTitle; ?> Value">
                                                    <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                </div>
                                                <?php
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vRideServiceTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vRideServiceTitle_', '<?= $default_lang ?>');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="modal-footer" style="margin-top: 0">
                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                            <button type="button" class="save" style="margin-left: 0 !important"
                                                    onclick="saveRideServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vRideServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>

                    <?php } else { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vRideServiceTitle_<?= $default_lang ?>"
                                       name="vRideServiceTitle_<?= $default_lang ?>"
                                       value="<?= $userEditDataArr['vRideServiceTitle_' . $default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <label>Services</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="manage-banner-section">
                                <div class="service-img-block">
                                <?php foreach ($rideData as $rideArr) {
                                    if (isset($tServiceDetailsRideArr['iVehicleCategoryId_' . $rideArr['iVehicleCategoryId']])) {
                                        $tServiceDetails = $tServiceDetailsRideArr['iVehicleCategoryId_' . $rideArr['iVehicleCategoryId']];
                                        if (!empty($tServiceDetails['vImage'])) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=60&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $tServiceDetails['vImage'];
                                        }
                                        $vServiceImgOld = $tServiceDetails['vImage'];
                                        if ($tServiceDetails['eStatus'] == "Active") { 
                                    
                                ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $vServiceImg ?>">
                                        <div class="service-img-title"><?= $rideArr['vCategory'] ?></div>
                                    </div>
                                
                                <?php }} } ?>
                                </div>
                                <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#rideservices_modal">Manage Services for App Home Screen</button>
                            </div>
                        </div>                            
                    </div>

                    <div class="modal fade" id="rideservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content nimot-class">
                                <div class="modal-header">
                                    <h4>
                                        Taxi Services
                                        <button type="button" class="close" data-dismiss="modal">x</button>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        <strong>Note:</strong>
                                        Enable any 4 service categories from below list to be shown on App
                                        home screen. All other service categories will be shown under "See All".
                                        <br>
                                        Icons uploaded will only be shown on App home screen.
                                        <br><br>
                                        <strong>Upload only png image size of 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?></strong>
                                    </p>
                                    <input type="hidden" name="saveRideServiceDisplay" id="saveRideServiceDisplay" value="No">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th style="text-align: center;">Icon</th>
                                            <th>Service Category</th>
                                            <th>Display Order</th>
                                            <th>Upload Icon</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($rideData as $rideArr) {
                                                $vServiceImg = "";
                                                $vServiceStatus = "";
                                                $vServiceImgOld = "";
                                                $vServiceDisplay = 'style="display: none"';
                                                $vServiceDisplayOrder = "1";
                                                if (isset($tServiceDetailsRideArr['iVehicleCategoryId_' . $rideArr['iVehicleCategoryId']])) {
                                                    $tServiceDetails = $tServiceDetailsRideArr['iVehicleCategoryId_' . $rideArr['iVehicleCategoryId']];
                                                    if (!empty($tServiceDetails['vImage'])) {
                                                        $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $tServiceDetails['vImage'];
                                                    }
                                                    $vServiceImgOld = $tServiceDetails['vImage'];
                                                    if ($tServiceDetails['eStatus'] == "Active") {
                                                        $vServiceStatus = "checked";
                                                        $vServiceDisplay = "";
                                                        $vServiceDisplayOrder = $tServiceDetails['iDisplayOrder'];
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServiceImg)) { ?>
                                                            <img src="<?= $vServiceImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $rideArr['vCategory'] ?></td>
                                                    <td>
                                                        <select class="form-control" name="iDisplayOrderRideArr[]" <?= $vServiceDisplay ?>>
                                                            <?php for ($disp_order = 1; $disp_order <= scount($rideData); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="file" class="form-control" name="vRideImage[]" <?= $vServiceDisplay ?>>
                                                        <input type="hidden" class="form-control" name="vRideImageOld[]" value="<?= $vServiceImgOld ?>">
                                                    </td>
                                                    <td>
                                                        <div class="make-switch" data-on="success"
                                                             data-off="warning">
                                                            <input type="checkbox" name="iVehicleCategoryId[]" value="<?= $rideArr['iVehicleCategoryId'] ?>" <?= $vServiceStatus ?> />
                                                        </div>
                                                        <input type="hidden" name="iVehicleCategoryIdVal[]" value="<?= $rideArr['iVehicleCategoryId'] ?>">
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer" style="text-align: left">
                                    <button type="button" class="btn btn-default" onclick="saveRideServices('Yes')">Save
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="saveRideServices('No')">Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveRideServiceSection">Save</button>
                        </div>
                    </div>

                    <?php if ($MODULES_OBJ->isUberXFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">On-Demand Services</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOnDemandServiceTitle_Default"
                                           name="vOnDemandServiceTitle_Default"
                                           value="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editOnDemandServiceTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="OnDemandServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="ondemandservice_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOnDemandServiceTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vOnDemandServiceTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    } else {
                                                        if ($vCode == $default_lang) {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    }
                                                }
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Title (<?= $vTitle; ?>
                                                            ) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                               id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOnDemandServiceTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOnDemandServiceTitle_', '<?= $default_lang ?>');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="modal-footer" style="margin-top: 0">
                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveOnDemandServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOnDemandServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOnDemandServiceSubTitle_Default"
                                           name="vOnDemandServiceSubTitle_Default"
                                           value="<?= $userEditDataArr['vOnDemandServiceSubTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vOnDemandServiceSubTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editOnDemandServiceSubTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="OnDemandServiceSubTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="ondemandservice_subtitle_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOnDemandServiceSubTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vOnDemandServiceSubTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    } else {
                                                        if ($vCode == $default_lang) {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    }
                                                }
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Subtitle (<?= $vTitle; ?>
                                                            ) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                               id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOnDemandServiceSubTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOnDemandServiceSubTitle_', '<?= $default_lang ?>');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="modal-footer" style="margin-top: 0">
                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveOnDemandServiceSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOnDemandServiceSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOnDemandServiceTitle_<?= $default_lang ?>"
                                           name="vOnDemandServiceTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOnDemandServiceSubTitle_<?= $default_lang ?>" name="vOnDemandServiceSubTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vOnDemandServiceSubTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block">
                                    <?php foreach ($ufxData as $ufxService) {
                                        if (isset($tServiceDetailsUberXArr['iVehicleCategoryId_' . $ufxService['iVehicleCategoryId']])) {
                                            $tServiceDetails = $tServiceDetailsUberXArr['iVehicleCategoryId_' . $ufxService['iVehicleCategoryId']];
                                            if (!empty($tServiceDetails['vImage'])) {
                                                $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=60&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $tServiceDetails['vImage'];
                                            }
                                            $vServiceImgOld = $tServiceDetails['vImage'];
                                            if ($tServiceDetails['eStatus'] == "Active") { 
                                        
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ufxService['vCategoryName'] ?></div>
                                        </div>
                                    
                                    <?php }} } ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=60&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/ic_more_other_services.png" ?>">
                                        <div class="service-img-title">50+ More Services</div>
                                    </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#ondemanservices_modal">Manage Services for App Home Screen</button>
                                </div>
                            </div>                            
                        </div>

                        <div class="modal fade" id="ondemanservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            On-Demand Services
                                            <button type="button" class="close" data-dismiss="modal">x</button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            <strong>Note:</strong>
                                            Enable any 3 service categories from below list to be shown on App
                                            home screen. All other service categories will be shown under more.
                                            <br>
                                            Icons uploaded will only be shown on App home screen and not under
                                            more section.
                                            <br><br>
                                            <strong>Upload only png image size of 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?></strong>
                                        </p>
                                        <input type="hidden" name="saveOnDemandDisplay" id="saveOnDemandDisplay" value="No">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th style="text-align: center;">Icon</th>
                                                <th>Service Category</th>
                                                <th>Display Order</th>
                                                <th>Upload Icon</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($ufxData as $ufxService) {
                                                $vServiceImg = "";
                                                $vServiceStatus = "";
                                                $vServiceImgOld = "";
                                                $vServiceDisplay = 'style="display: none"';
                                                $vServiceDisplayOrder = "1";
                                                if (isset($tServiceDetailsUberXArr['iVehicleCategoryId_' . $ufxService['iVehicleCategoryId']])) {
                                                    $tServiceDetails = $tServiceDetailsUberXArr['iVehicleCategoryId_' . $ufxService['iVehicleCategoryId']];
                                                    if (!empty($tServiceDetails['vImage'])) {
                                                        $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $tServiceDetails['vImage'];
                                                    }
                                                    $vServiceImgOld = $tServiceDetails['vImage'];
                                                    if ($tServiceDetails['eStatus'] == "Active") {
                                                        $vServiceStatus = "checked";
                                                        $vServiceDisplay = "";
                                                        $vServiceDisplayOrder = $tServiceDetails['iDisplayOrder'];
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServiceImg)) { ?>
                                                            <img src="<?= $vServiceImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $ufxService['vCategoryName'] ?></td>
                                                    <td>
                                                        <select class="form-control" name="iDisplayOrderOnDemandServiceArr[]" <?= $vServiceDisplay ?>>
                                                            <?php for ($disp_order = 1; $disp_order <= scount($ufxData); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="file" class="form-control" name="vOnDemandServiceImage[]" <?= $vServiceDisplay ?>>
                                                        <input type="hidden" class="form-control" name="vOnDemandServiceImageOld[]" value="<?= $vServiceImgOld ?>">
                                                    </td>
                                                    <td>
                                                        <div class="make-switch" data-on="success" data-off="warning">
                                                            <input type="checkbox" name="iVehicleCategoryIdSP[]" value="<?= $ufxService['iVehicleCategoryId'] ?>" <?= $vServiceStatus ?> />
                                                        </div>
                                                        <input type="hidden" name="iVehicleCategoryIdValSP[]" value="<?= $ufxService['iVehicleCategoryId'] ?>">
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer" style="text-align: left">
                                        <button type="button" class="btn btn-default" onclick="saveOnDemandServices('Yes')">Save
                                        </button>
                                        <button type="button" class="btn btn-default" onclick="saveOnDemandServices('No')">Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveOnDemandServiceSection">Save</button>
                            </div>
                        </div>
                    <?php } ?>

                    <hr/>
                    <div class="show-help-section section-title">Promotional Banner</div>
                    <div class="underline-section-title"></div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="manage-banner-section promo-banner">
                                <?php if (!empty($promotional_banner_data)) { ?>
                                    <div class="banner-img-block">
                                        <?php foreach ($promotional_banner_data as $app_promot_banner) { ?>
                                            <div class="banner-img">
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=400&src=' . $tconfig['tsite_upload_images'] . $app_promot_banner['vImage']; ?>">
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <a href="<?= $tconfig['tsite_url_main_admin'] ?>app_banner.php?iVehicleCategoryId=<?= $promotionalCategoryId ?>&eFor=Promotion" class="manage-banner-btn" target="_blank">Manage Promotional Banner for App Home Screen</a>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <div class="show-help-section section-title">Other Services</div>
                    <div class="underline-section-title"></div>
                    <?php if (scount($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vOtherServiceTitle_Default"
                                       name="vOtherServiceTitle_Default"
                                       value="<?= $userEditDataArr['vOtherServiceTitle_' . $default_lang]; ?>"
                                       data-originalvalue="<?= $userEditDataArr['vOtherServiceTitle_' . $default_lang]; ?>"
                                       readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editOtherServiceTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="OtherServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="otherservice_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vOtherServiceTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vOtherServiceTitle_' . $vCode;
                                            $$vValue = $userEditDataArr[$vValue];
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <?php
                                            $page_title_class = 'col-lg-12';
                                            if (scount($db_master) > 1) {
                                                if ($EN_available) {
                                                    if ($vCode == "EN") {
                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                    }
                                                } else {
                                                    if ($vCode == $default_lang) {
                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title (<?= $vTitle; ?>
                                                        ) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="<?= $page_title_class ?>">
                                                    <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                           id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                           data-originalvalue="<?= $$vValue; ?>"
                                                           placeholder="<?= $vTitle; ?> Value">
                                                    <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                </div>
                                                <?php
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vOtherServiceTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vOtherServiceTitle_', '<?= $default_lang ?>');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="modal-footer" style="margin-top: 0">
                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                            <button type="button" class="save" style="margin-left: 0 !important"
                                                    onclick="saveOtherServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vOtherServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>

                    <?php } else { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vOtherServiceTitle_<?= $default_lang ?>"
                                       name="vOtherServiceTitle_<?= $default_lang ?>"
                                       value="<?= $userEditDataArr['vOtherServiceTitle_' . $default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveOtherServiceTitleSection">Save</button>
                        </div>
                    </div>

                    <div class="row paddingbottom-0">
                        <div class="col-lg-12">
                            <div class="tab">
                                <?php $os_count = 1; foreach ($OTHER_SERVICES_ARR as $OTHER_SERVICE) { ?>
                                <button class="tablinks manage-<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-tab <?= $os_count == 1 ? "active" : "" ?>" onclick="openTabContent(event, 'manage-<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-content', 'tabcontent-other')"> <?= $OTHER_SERVICE['ServiceTitle']['EN'] ?>
                                </button>
                                <?php $os_count++; } ?>
                            </div>
                        </div>
                    </div>

                    <?php $os_count = 1; foreach ($OTHER_SERVICES_ARR as $OTHER_SERVICE) { ?>
                    <div class="tabcontent tabcontent-other <?= $os_count == 1 ? "display-tab-content" : "" ?>" id="manage-<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-content">
                        <div class="col-lg-12">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a data-toggle="tab" href="#<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-home-screen">Home Screen</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-info-screen">Info Screen</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-home-screen" class="tab-pane active">
                                    <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <input type="text" class="form-control" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default" value="<?= $OTHER_SERVICE['ServiceTitle'][$default_lang]; ?>" data-originalvalue="<?= $OTHER_SERVICE['ServiceTitle'][$default_lang]; ?>" readonly="readonly" required>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit" onclick="edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                             data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_title_modal_action"></span>
                                                            Title
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_')">x
                                                            </button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        for ($i = 0; $i < $count_all; $i++) {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $vValue = 'v' . $OTHER_SERVICE['ManageServiceKey'] . 'Title_' . $vCode;
                                                            $$vValue = $OTHER_SERVICE['ServiceTitle'][$vCode];
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                            ?>
                                                            <?php
                                                            $page_title_class = 'col-lg-12';
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") {
                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                    }
                                                                } else {
                                                                    if ($vCode == $default_lang) {
                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Title (<?= $vTitle; ?>
                                                                        ) <?php echo $required_msg; ?></label>
                                                                </div>
                                                                <div class="<?= $page_title_class ?>">
                                                                    <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                                           id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                                           data-originalvalue="<?= $$vValue; ?>"
                                                                           placeholder="<?= $vTitle; ?> Value">
                                                                    <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                                <?php
                                                                if (scount($db_master) > 1) {
                                                                    if ($EN_available) {
                                                                        if ($vCode == "EN") { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type="button" name="allLanguage"
                                                                                        id="allLanguage" class="btn btn-primary"
                                                                                        onClick="getAllLanguageCode('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_', 'EN');">
                                                                                    Convert To All Language
                                                                                </button>
                                                                            </div>
                                                                        <?php }
                                                                    } else {
                                                                        if ($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type="button" name="allLanguage"
                                                                                        id="allLanguage" class="btn btn-primary"
                                                                                        onClick="getAllLanguageCode('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_', '<?= $default_lang ?>');">
                                                                                    Convert To All Language
                                                                                </button>
                                                                            </div>
                                                                        <?php }
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" style="margin-left: 0 !important"
                                                                    onclick="save<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Subtitle</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <input type="text" class="form-control" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default" value="<?= $OTHER_SERVICE['ServiceDesc'][$default_lang] ?>"  data-originalvalue="<?= $OTHER_SERVICE['ServiceDesc'][$default_lang] ?>" readonly="readonly" required>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit" onclick="edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Modal" tabindex="-1" role="dialog"
                                             aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_subtitle_modal_action"></span>
                                                            Title
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_')">x
                                                            </button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        for ($i = 0; $i < $count_all; $i++) {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $vValue = 'v' . $OTHER_SERVICE['ManageServiceKey'] . 'SubTitle_' . $vCode;
                                                            $$vValue = $OTHER_SERVICE['ServiceDesc'][$vCode];
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                            ?>
                                                            <?php
                                                            $page_title_class = 'col-lg-12';
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") {
                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                    }
                                                                } else {
                                                                    if ($vCode == $default_lang) {
                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Subtitle (<?= $vTitle; ?>
                                                                        ) <?php echo $required_msg; ?></label>
                                                                </div>
                                                                <div class="<?= $page_title_class ?>">
                                                                    <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                                           id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                                           data-originalvalue="<?= $$vValue; ?>"
                                                                           placeholder="<?= $vTitle; ?> Value">
                                                                    <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                                <?php
                                                                if (scount($db_master) > 1) {
                                                                    if ($EN_available) {
                                                                        if ($vCode == "EN") { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type="button" name="allLanguage"
                                                                                        id="allLanguage" class="btn btn-primary"
                                                                                        onClick="getAllLanguageCode('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_', 'EN');">
                                                                                    Convert To All Language
                                                                                </button>
                                                                            </div>
                                                                        <?php }
                                                                    } else {
                                                                        if ($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type="button" name="allLanguage"
                                                                                        id="allLanguage" class="btn btn-primary"
                                                                                        onClick="getAllLanguageCode('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_', '<?= $default_lang ?>');">
                                                                                    Convert To All Language
                                                                                </button>
                                                                            </div>
                                                                        <?php }
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" style="margin-left: 0 !important"
                                                                    onclick="save<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <input type="text" class="form-control" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>"
                                                       value="<?= $OTHER_SERVICE['ServiceTitle'][$default_lang] ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Subtitle</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <input type="text" class="form-control" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>" value="<?= $OTHER_SERVICE['ServiceDesc'][$default_lang] ?>">
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div class="row pb-10">
                                        <div class="col-lg-12">
                                            <label>Image</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4 marginbottom-10">
                                            <?php if(!empty($vImageOld[$OTHER_SERVICE['ManageServiceKey']])) { ?>
                                            <div class="marginbottom-10">
                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vImageOld[$OTHER_SERVICE['ManageServiceKey']]; ?>" id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_img">
                                            </div>
                                            <?php } ?>
                                            <input type="file" class="form-control" name="vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>" id="vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>" onchange="previewImage(this, event);" data-img="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_img">
                                            <input type="hidden" class="form-control" name="vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>" id="vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>" value="<?= $vImageOld[$OTHER_SERVICE['ManageServiceKey']] ?>">
                                            <strong class="img-note">Note: Upload only png image size of 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <button type="button" class="btn btn-primary save-section-btn" id="save<?= $OTHER_SERVICE['ManageServiceKey'] ?>Section">Save</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>-info-screen" class="tab-pane">
                                    <?php if (scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Info Title</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <input type="text" class="form-control" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default" value="<?= $OTHER_SERVICE['ServicePageTitle'][$default_lang]; ?>" data-originalvalue="<?= $OTHER_SERVICE['ServicePageTitle'][$default_lang]; ?>" readonly="readonly" required>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                             data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_infotitle_modal_action"></span>
                                                            Info Title
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_')">x
                                                            </button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        for ($i = 0; $i < $count_all; $i++) {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $vValue = 'v' . $OTHER_SERVICE['ManageServiceKey'] . 'InfoTitle_' . $vCode;
                                                            $$vValue = $OTHER_SERVICE['ServicePageTitle'][$vCode];
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                            ?>
                                                            <?php
                                                            $page_title_class = 'col-lg-12';
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") {
                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                    }
                                                                } else {
                                                                    if ($vCode == $default_lang) {
                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Title (<?= $vTitle; ?>
                                                                        ) <?php echo $required_msg; ?></label>
                                                                </div>
                                                                <div class="<?= $page_title_class ?>">
                                                                    <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                                           id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                                           data-originalvalue="<?= $$vValue; ?>"
                                                                           placeholder="<?= $vTitle; ?> Value">
                                                                    <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                                         style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                                <?php
                                                                if (scount($db_master) > 1) {
                                                                    if ($EN_available) {
                                                                        if ($vCode == "EN") { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type="button" name="allLanguage"
                                                                                        id="allLanguage" class="btn btn-primary"
                                                                                        onClick="getAllLanguageCode('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_', 'EN');">
                                                                                    Convert To All Language
                                                                                </button>
                                                                            </div>
                                                                        <?php }
                                                                    } else {
                                                                        if ($vCode == $default_lang) { ?>
                                                                            <div class="col-md-3 col-sm-3">
                                                                                <button type="button" name="allLanguage"
                                                                                        id="allLanguage" class="btn btn-primary"
                                                                                        onClick="getAllLanguageCode('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_', '<?= $default_lang ?>');">
                                                                                    Convert To All Language
                                                                                </button>
                                                                            </div>
                                                                        <?php }
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" style="margin-left: 0 !important"
                                                                    onclick="save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Info Description</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <textarea class="form-control ckeditor" rows="10" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Default" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Default" data-originalvalue="<?= $OTHER_SERVICE['ServicePageDesc'][$default_lang] ?>" readonly="readonly"><?= $OTHER_SERVICE['ServicePageDesc'][$default_lang] ?></textarea>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                        data-original-title="Edit" onclick="edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Modal" tabindex="-1" role="dialog"
                                             aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_infosubtitle_modal_action"></span>
                                                            Description
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_')">x
                                                            </button>
                                                        </h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        for ($i = 0; $i < $count_all; $i++) {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                            $vValue = 'v' . $OTHER_SERVICE['ManageServiceKey'] . 'InfoSubTitle_' . $vCode;
                                                            $$vValue = $OTHER_SERVICE['ServicePageDesc'][$vCode];
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                            ?>
                                                            <?php
                                                            $page_title_class = 'col-lg-12';
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                </div>
                                                                <div class="<?= $page_title_class ?>">
                                                                    <textarea class="form-control ckeditor" rows="10" name="<?= $vValue; ?>" id="<?= $vValue; ?>" data-originalvalue="<?= $$vValue; ?>"><?= $$vValue; ?></textarea>
                                                                    <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                            <strong><?= $langage_lbl['LBL_NOTE']; ?>:
                                                            </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_', '<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                        </div>
                                                    </div>
                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Info Title</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <input type="text" class="form-control" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>"
                                                       value="<?= $OTHER_SERVICE['ServicePageTitle'][$default_lang] ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Info Subtitle</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4">
                                                <textarea class="form-control ckeditor" rows="10" id="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_<?= $default_lang ?>" name="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_<?= $default_lang ?>"> <?= $OTHER_SERVICE['ServicePageDesc'][$default_lang] ?></textarea>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div class="row pb-10">
                                        <div class="col-lg-12">
                                            <label>Image</label>
                                        </div>
                                        <div class="col-lg-12 marginbottom-10">
                                            <?php if(!empty($vInfoImageOld[$OTHER_SERVICE['ManageServiceKey']])) { ?>
                                            <div class="marginbottom-10">
                                                <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vInfoImageOld[$OTHER_SERVICE['ManageServiceKey']]; ?>" id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>info_img">
                                            </div>
                                            <?php } ?>
                                            <input type="file" class="form-control" name="vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info" id="vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info" onchange="previewImage(this, event);" data-img="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>info_img">
                                            <input type="hidden" class="form-control" name="vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info" id="vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info" value="<?= $vInfoImageOld[$OTHER_SERVICE['ManageServiceKey']] ?>">
                                        </div>
                                        <div class="col-lg-12">
                                            <strong>Note: Upload only png image size of 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <button type="button" class="btn btn-primary save-section-btn" id="save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSection">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $os_count++; } ?>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>


<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div>
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<? include_once('footer.php'); ?>
<script type="text/javascript" src="js/fancybox.umd.js"></script>
<script type="text/javascript" src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
<script src="../assets/js/modal_alert.js"></script>

<script type="text/javascript">
    $('.ckeditor').each(function(e){
        CKEDITOR.replace(this.id, {
            toolbarGroups: [
                { name: 'insert'},
                { name: 'paragraph',   groups: [ 'list', 'align' ] },
            ]
        });
    });

    function editRideServiceTitle(action) {
        $('#rideservice_title_modal_action').html(action);
        $('#RideServiceTitle_Modal').modal('show');
    }

    function saveRideServiceTitle() {
        if ($('#vRideServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vRideServiceTitle_<?= $default_lang ?>_error').show();
            $('#vRideServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRideServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRideServiceTitle_Default').val($('#vRideServiceTitle_<?= $default_lang ?>').val());
        $('#vRideServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vRideServiceTitle_Default-error').remove();
        $('#RideServiceTitle_Modal').modal('hide');
    }

    $('#saveRideServiceSection').click(function() {
        var vRideServiceTitleArr = $('[name^="vRideServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRideServiceTitleArr, function(key, value) {
            if(value.name != "vRideServiceTitle_Default") {
                var name_key = value.name.replace('vRideServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vRideServiceSubTitleArr = $('[name^="vRideServiceSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vRideServiceSubTitleArr, function(key, value) {
            if(value.name != "vRideServiceSubTitle_Default") {
                var name_key = value.name.replace('vRideServiceSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var saveRideServiceDisplay = $('#saveRideServiceDisplay').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));

        $('[name="vRideImage[]"]').each(function(i) {
            postData.append('vImage['+i+']', $(this)[0].files[0]);
        });

        $('[name="vRideImageOld[]"]').each(function(i) {
            postData.append('vImageOld['+i+']', $(this).val());
        });

        $('[name="iVehicleCategoryId[]"]').each(function(i) {
            if($(this).is(':checked')) {
                postData.append('iVehicleCategoryId['+i+']', $(this).val());
            }   
        });

        $('[name="iVehicleCategoryIdVal[]"]').each(function(i) {
            postData.append('iVehicleCategoryIdVal['+i+']', $(this).val());
        });

        $('[name="iDisplayOrderRideArr[]"]').each(function(i) {
            postData.append('iDisplayOrderRideArr['+i+']', $(this).val());
        });

        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'Ride');
        postData.append('saveRideServiceDisplay', saveRideServiceDisplay);

        saveHomeScreenData('saveRideServiceSection', postData);
    });

    function saveRideServices(eStatus) {
        $('#saveRideServiceDisplay').val(eStatus);
        $('#rideservices_modal').modal('hide');
    }

    $('[name="iVehicleCategoryId[]"]').change(function (e) {
        if ($(this).is(':checked')) {
            if ($('[name="iVehicleCategoryId[]"]:checked').length > 4) {
                alert("You can only enable 4 services to be shown on App home screen.");
                $(this).prop('checked', false);
                e.stopPropagation();
                e.preventDefault();
            } else {
                $(this).closest('tr').find('select, input[type="file"]').show();
            }
        } else {
            $(this).closest('tr').find('select, input[type="file"]').hide();
            $(this).closest('tr').find('select').val('1');
            $(this).closest('tr').find('input[type="file"]').val('').bootstrapSwitch();
        }
    });

    function editOnDemandServiceTitle(action) {
        $('#ondemandservice_title_modal_action').html(action);
        $('#OnDemandServiceTitle_Modal').modal('show');
    }

    function saveOnDemandServiceTitle() {
        if ($('#vOnDemandServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vOnDemandServiceTitle_<?= $default_lang ?>_error').show();
            $('#vOnDemandServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vOnDemandServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vOnDemandServiceTitle_Default').val($('#vOnDemandServiceTitle_<?= $default_lang ?>').val());
        $('#vOnDemandServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vOnDemandServiceTitle_Default-error').remove();
        $('#OnDemandServiceTitle_Modal').modal('hide');
    }

    function editOnDemandServiceSubTitle(action) {
        $('#ondemandservice_subtitle_modal_action').html(action);
        $('#OnDemandServiceSubTitle_Modal').modal('show');
    }

    function saveOnDemandServiceSubTitle() {
        if ($('#vOnDemandServiceSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vOnDemandServiceSubTitle_<?= $default_lang ?>_error').show();
            $('#vOnDemandServiceSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vOnDemandServiceSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vOnDemandServiceSubTitle_Default').val($('#vOnDemandServiceSubTitle_<?= $default_lang ?>').val());
        $('#vOnDemandServiceSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vOnDemandServiceSubTitle_Default-error').remove();
        $('#OnDemandServiceSubTitle_Modal').modal('hide');
    }

    $('#saveOnDemandServiceSection').click(function() {
        var vOnDemandServiceTitleArr = $('[name^="vOnDemandServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vOnDemandServiceTitleArr, function(key, value) {
            if(value.name != "vOnDemandServiceTitle_Default") {
                var name_key = value.name.replace('vOnDemandServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vOnDemandServiceSubTitleArr = $('[name^="vOnDemandServiceSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vOnDemandServiceSubTitleArr, function(key, value) {
            if(value.name != "vOnDemandServiceSubTitle_Default") {
                var name_key = value.name.replace('vOnDemandServiceSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var saveOnDemandDisplay = $('#saveOnDemandDisplay').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));

        $('[name="vOnDemandServiceImage[]"]').each(function(i) {
            postData.append('vImage['+i+']', $(this)[0].files[0]);
        });

        $('[name="vOnDemandServiceImageOld[]"]').each(function(i) {
            postData.append('vImageOld['+i+']', $(this).val());
        });

        $('[name="iVehicleCategoryIdSP[]"]').each(function(i) {
            if($(this).is(':checked')) {
                postData.append('iVehicleCategoryId['+i+']', $(this).val());
            }   
        });

        $('[name="iVehicleCategoryIdValSP[]"]').each(function(i) {
            postData.append('iVehicleCategoryIdVal['+i+']', $(this).val());
        });

        $('[name="iDisplayOrderOnDemandServiceArr[]"]').each(function(i) {
            postData.append('iDisplayOrderOnDemandServiceArr['+i+']', $(this).val());
        });

        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'UberX');
        postData.append('saveOnDemandDisplay', saveOnDemandDisplay);

        saveHomeScreenData('saveOnDemandServiceSection', postData);
    });

    function saveOnDemandServices(eStatus) {
        $('#saveOnDemandDisplay').val(eStatus);
        $('#ondemanservices_modal').modal('hide');
    }

    $('[name="iVehicleCategoryIdSP[]"]').change(function (e) {
        if ($(this).is(':checked')) {
            if ($('[name="iVehicleCategoryIdSP[]"]:checked').length > 3) {
                alert("You can only enable 3 service categories to be shown on App home screen.");
                $(this).prop('checked', false);
                e.stopPropagation();
                e.preventDefault();
            } else {
                $(this).closest('tr').find('select, input[type="file"]').show();
            }
        } else {
            $(this).closest('tr').find('select, input[type="file"]').hide();
            $(this).closest('tr').find('select').val('1');
            $(this).closest('tr').find('input[type="file"]').val('').bootstrapSwitch();
        }
    });

    function editOtherServiceTitle(action) {
        $('#otherservice_title_modal_action').html(action);
        $('#OtherServiceTitle_Modal').modal('show');
    }

    function saveOtherServiceTitle() {
        if ($('#vOtherServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vOtherServiceTitle_<?= $default_lang ?>_error').show();
            $('#vOtherServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vOtherServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vOtherServiceTitle_Default').val($('#vOtherServiceTitle_<?= $default_lang ?>').val());
        $('#vOtherServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vOtherServiceTitle_Default-error').remove();
        $('#OtherServiceTitle_Modal').modal('hide');
    }

    $('#saveOtherServiceTitleSection').click(function() {
        var vOtherServiceTitleArr = $('[name^="vOtherServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vOtherServiceTitleArr, function(key, value) {
            if(value.name != "vOtherServiceTitle_Default") {
                var name_key = value.name.replace('vOtherServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Other';
        saveHomeScreenData('saveOtherServiceTitleSection', postData, 'No');
    });

    <?php foreach ($OTHER_SERVICES_ARR as $OTHER_SERVICE) { ?>
    function edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title(action) {
        $('#<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_title_modal_action').html(action);
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Modal').modal('show');
    }

    function save<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title() {
        if ($('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>').val() == "") {
            $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>_error').show();
            $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default').val($('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_<?= $default_lang ?>').val());
        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default').closest('.row').removeClass('has-error');
        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default-error').remove();
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Modal').modal('hide');
    }

    function edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle(action) {
        $('#<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_subtitle_modal_action').html(action);
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Modal').modal('show');
    }

    function save<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle() {
        if ($('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>').val() == "") {
            $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>_error').show();
            $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default').val($('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_<?= $default_lang ?>').val());
        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default').closest('.row').removeClass('has-error');
        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default-error').remove();
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Modal').modal('hide');
    }

    $('#save<?= $OTHER_SERVICE['ManageServiceKey'] ?>Section').click(function() {
        var v<?= $OTHER_SERVICE['ManageServiceKey'] ?>TitleArr = $('[name^="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_"]').serializeArray();
        var vTitleArr = {};
        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>TitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitleArr = $('[name^="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });
        var vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>')[0].files[0];
        var vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('vImageOld', vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'Other');
        postData.append('ServiceTypeOther', '<?= $OTHER_SERVICE['ManageServiceKey'] ?>');

        saveHomeScreenData('save<?= $OTHER_SERVICE['ManageServiceKey'] ?>Section', postData);
    });

    // Info Screen
    function edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle(action) {
        $('#<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_infotitle_modal_action').html(action);
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Modal').modal('show');
    }

    function save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle() {
        if ($('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>').val() == "") {
            $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>_error').show();
            $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default').val($('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_<?= $default_lang ?>').val());
        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default').closest('.row').removeClass('has-error');
        $('#v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default-error').remove();
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Modal').modal('hide');
    }

    function edit<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle(action) {
        $('#<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_infosubtitle_modal_action').html(action);
        $('#<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Modal').modal('show');
    }

    function save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle(input_id, modal_id) {
        var DescLength = CKEDITOR.instances[input_id+'<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
        if(!DescLength) {
            $('#'+input_id+'<?= $default_lang ?>_error').show();
            $('#'+input_id+'<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function() {
                $('#'+input_id+'<?= $default_lang ?>_error').hide();
            }, 5000);
            e.preventDefault();
            return false;
        }

        var DescHTML = CKEDITOR.instances[input_id + '<?= $default_lang ?>'].getData();
        CKEDITOR.instances[input_id+'Default'].setData(DescHTML);
        $('#'+modal_id).modal('hide');
    }

    $('#save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSection').click(function() {
        var v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitleArr = $('[name^="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitleArr = $('[name^="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = CKEDITOR.instances[value.name].getData();;
            }
        });
        var vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info = $('#vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info')[0].files[0];
        var vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info = $('#vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info);
        postData.append('vImageOld', vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info);
        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'Other');
        postData.append('ServiceTypeOther', '<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info');

        saveHomeScreenData('save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSection', postData);
    });
    <?php } ?>

    function saveHomeScreenData(saveBtnId, postData, isImageUpload = 'Yes') {
        $('#' + saveBtnId).prop('disabled', true);
        $('#' + saveBtnId).append(' <i class="fa fa-spinner fa-spin"></i>');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_manage_app_home_screen.php',
            'AJAX_DATA': postData
        };

        if(isImageUpload == "Yes") {
            ajaxData.REQUEST_CONTENT_TYPE = false;
            ajaxData.REQUEST_PROCESS_DATA = false;
        }
        getDataFromAjaxCall(ajaxData, function(response) {
            $('#' + saveBtnId).prop('disabled', false);
            if(response.action == "1") {
                var responseData = JSON.parse(response.result);
                if(responseData.Action == "1") {
                    $('#' + saveBtnId).find('i').remove();
                    $('#' + saveBtnId).append(' <i class="fa fa-check"></i>');
                    setTimeout(function() {
                        $('#' + saveBtnId).find('i').remove();
                    }, 3000);
                } else {
                    $('#' + saveBtnId).find('i').remove();
                    $('#' + saveBtnId).append(' <i class="fa fa-times"></i>');
                    setTimeout(function() {
                        $('#' + saveBtnId).find('i').remove();
                    }, 3000);
                    show_alert("", responseData.message, "", "Ok", "", function (btn_id) {}, true, true, true);
                }
            }
            else {
                $('#' + saveBtnId).find('i').remove();
                $('#' + saveBtnId).append(' <i class="fa fa-times"></i>');
                setTimeout(function() {
                    $('#' + saveBtnId).find('i').remove();
                }, 3000);
                show_alert("", "Something went wrong.", "", "Ok", "", function (btn_id) {}, true, true, true);
            }
        });
    }

    function previewImage(elem, event) {
        var img_id = $(elem).data('img');
        $('#' + img_id).attr('src', URL.createObjectURL(event.target.files[0]));
        $('#' + img_id).css('height', '100px');
    }

    function openTabContent(evt, Pagename, tabcontent_hide) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = $('.' + tabcontent_hide).hide();

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = $(evt.currentTarget).closest('.tab').find('.tablinks').removeClass('active');

        // Show the current tab, and add an "active" class to the button that opened the tab
        console.log(Pagename);
        $('#' + Pagename).show();
        $(evt.currentTarget).addClass('active');
    }
</script>
</body>
<!-- END BODY-->
</html>