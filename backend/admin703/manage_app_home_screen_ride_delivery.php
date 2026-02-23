<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-app-home-screen-view')) {
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'ManageAppHomePage';
$tbl_name = "app_home_screen_view";
if(checkTableExists('app_home_screen_view_new', getAllTableArray())) {
    $tbl_name = "app_home_screen_view_new";
}
$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = scount($db_master);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$userEditDataArr = $db_data_arr = array();
$db_data = $obj->MySQLSelect("SELECT * FROM $tbl_name");
foreach ($db_data as $db_value) {
    $ViewType = !empty($db_value['eServiceType']) ? $db_value['eServiceType'] : $db_value['eViewType'];
    if($db_value['eServiceType'] == "Deliver") {
        $tServiceDetailsDB = json_decode($db_value['tServiceDetails'], true);
        $db_data_arr[$ViewType][$tServiceDetailsDB['eCatType']] = $db_value;
    } else {
        $db_data_arr[$ViewType] = $db_value;
    }
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

$taxiSql = "";
if($MODULES_OBJ->isEnableTaxiBidFeature()) {
    $taxiSql .= " OR eCatType IN ('TaxiBid', 'MotoBid') ";
}
if($MODULES_OBJ->isInterCityFeatureAvailable()) {
    $taxiSql .= " OR eCatType IN ('InterCity') ";
}
$rideData = $obj->MySQLSelect("SELECT iVehicleCategoryId, iDisplayOrder,vListLogo3, vCategory_" . $default_lang . " as vCategory FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND (eCatType IN ('Ride', 'MotoRide', 'Rental', 'MotoRental', 'RidePool', 'RideSchedule', 'CorporateRide', 'RideSomeoneElse') $taxiSql) AND eForMedicalService = 'No' AND iParentId = '0' ORDER BY iDisplayOrder, iVehicleCategoryId ASC ");

/* Single Delivery */
$vSingleDeliveryTitleArr = json_decode($db_data_arr['Deliver']['Delivery']['vTitle'], true);
foreach ($vSingleDeliveryTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vSingleDeliveryTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vSingleDeliverySubTitleArr = json_decode($db_data_arr['Deliver']['Delivery']['vSubtitle'], true);
foreach ($vSingleDeliverySubTitleArr as $key => $value) {
    $key = str_replace('vSubtitle_', 'vSingleDeliverySubTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vImageOldSingleDelivery = $db_data_arr['Deliver']['Delivery']['vImage'];

$SingleDeliveryLayoutDetails = json_decode($db_data_arr['Deliver']['Delivery']['tLayoutDetails'], true);
$vTitleColorSingleDelivery = $SingleDeliveryLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorSingleDelivery = $SingleDeliveryLayoutDetails['vTxtSubTitleColor']; 
$vBgColorSingleDelivery = $SingleDeliveryLayoutDetails['vBgColor']; 

/* Multi Delivery */
$vMultiDeliveryTitleArr = json_decode($db_data_arr['Deliver']['MultipleDelivery']['vTitle'], true);
foreach ($vMultiDeliveryTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vMultiDeliveryTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vMultiDeliverySubTitleArr = json_decode($db_data_arr['Deliver']['MultipleDelivery']['vSubtitle'], true);
foreach ($vMultiDeliverySubTitleArr as $key => $value) {
    $key = str_replace('vSubtitle_', 'vMultiDeliverySubTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vImageOldMultiDelivery = $db_data_arr['Deliver']['MultipleDelivery']['vImage'];

$MultiDeliveryLayoutDetails = json_decode($db_data_arr['Deliver']['MultipleDelivery']['tLayoutDetails'], true);
$vTitleColorMultiDelivery = $MultiDeliveryLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorMultiDelivery = $MultiDeliveryLayoutDetails['vTxtSubTitleColor']; 
$vBgColorMultiDelivery = $MultiDeliveryLayoutDetails['vBgColor']; 

/* Other Services */
$vOtherServiceTitleArr = json_decode($db_data_arr['Other']['vTitle'], true);
foreach ($vOtherServiceTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vOtherServiceTitle_', $key);
    $userEditDataArr[$key] = $value;
}

$labelsRide = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_TAXI_ADD_A_STOP', 'LBL_TAXI_ADD_A_STOP_DESC', 'LBL_TAXI_ADD_A_STOP_PAGE_TITLE', 'LBL_TAXI_ADD_A_STOP_PAGE_DESC', 'LBL_TAXI_POOL_TITLE', 'LBL_TAXI_POOL_DESC', 'LBL_TAXI_POOL_PAGE_TITLE', 'LBL_TAXI_POOL_PAGE_DESC', 'LBL_TAXI_BID_TITLE', 'LBL_TAXI_BID_DESC', 'LBL_TAXI_BID_PAGE_TITLE', 'LBL_TAXI_BID_PAGE_DESC', 'LBL_SHARE_YOUR_RIDE_TITLE', 'LBL_SHARE_YOUR_RIDE_DESC', 'LBL_SHARE_YOUR_RIDE_PAGE_TITLE', 'LBL_SHARE_YOUR_RIDE_PAGE_DESC', 'LBL_PARCEL_DELIVERY_HOME_SCREEN_TXT') ");

$AddStopTitleArr = $AddStopSubTitleArr = $AddStopPageTitleArr = $AddStopPageSubTitleArr = $TaxiPoolTitleArr = $TaxiPoolSubTitleArr = $TaxiPoolPageTitleArr = $TaxiPoolPageSubTitleArr = $TaxiBidTitleArr = $TaxiBidSubTitleArr = $TaxiBidPageTitleArr = $TaxiBidPageSubTitleArr = $ShareRideTitleArr = $ShareRideSubTitleArr = $ShareRidePageTitleArr = $ShareRidePageSubTitleArr = $DeliveryTitleArr = array();
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

    } elseif ($label['vLabel'] == 'LBL_PARCEL_DELIVERY_HOME_SCREEN_TXT') {
        $DeliveryTitleArr[$label['vCode']] = $label['vValue'];

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

$isAppHomeScreenV4 = true;
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
    <link rel="stylesheet" href="css/admin_new/admin_app_home_screen.css">
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
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

                    <?php if($isAppHomeScreenV4) { ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveRideServiceSection">Save</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <label>Services</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="manage-banner-section">
                                <div class="service-img-block" style="margin-bottom: 0">
                                    <?php $taxiCount = 0; foreach ($rideData as $taxiService) {
                                        if (!empty($taxiService['vListLogo3']) && $taxiCount < 4) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $taxiService['iVehicleCategoryId'] . "/" . $taxiService['vListLogo3'];
                                    ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $vServiceImg ?>">
                                        <div class="service-img-title"><?= $taxiService['vCategory'] ?></div>
                                    </div>
                                
                                    <?php $taxiCount++; } } ?>
                                </div>
                                <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#taxiservices_modal" style="margin-top: 25px;">Manage Services for App</button>
                            </div>
                            <div>
                                <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app. <br>The initial four services will be displayed in the app according to the specified display order.</strong>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="taxiservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
                                        The initial four services will be displayed in the app according to the specified display order.
                                    </p>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th style="text-align: center;">Icon</th>
                                            <th>Service Category</th>
                                            <th>Display Order</th>
                                            <th style="text-align: center;">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($rideData as $taxiService) {
                                            $vServiceDisplayOrder = $taxiService['iDisplayOrder'];
                                            $vServiceImg = "";
                                            if (!empty($taxiService['vListLogo3'])) {
                                                $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $taxiService['iVehicleCategoryId'] . "/" . $taxiService['vListLogo3'];
                                            }

                                            $editUrl = $tconfig['tsite_url_main_admin'] . 'vehicle_category_action.php?id=' . $taxiService['iVehicleCategoryId'] . '&eServiceType=Ride';
                                            ?>
                                            <tr>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <?php if (!empty($vServiceImg)) { ?>
                                                        <img src="<?= $vServiceImg ?>">
                                                    <?php } else { ?>
                                                        --
                                                    <?php } ?>
                                                </td>
                                                <td style="vertical-align: middle;"><?= $taxiService['vCategory'] ?></td>
                                                <td style="vertical-align: middle;">
                                                    <select class="form-control" name="iDisplayOrderTaxiServiceArr[]" data-serviceid="<?= $taxiService['iVehicleCategoryId'] ?>">
                                                        <?php for ($disp_order = 1; $disp_order <= scount($rideData); $disp_order++) { ?>
                                                            <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer" style="text-align: left">
                                    <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('Ride')">Save</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
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
                                    <button type="button" class="btn btn-default"
                                            onclick="saveRideServices('Yes')">Save
                                    </button>
                                    <button type="button" class="btn btn-default"
                                            onclick="saveRideServices('No')">Cancel
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
                    <?php } ?>

                    <hr />
                    <div class="show-help-section section-title">Parcel Delivery</div>
                    <div class="underline-section-title"></div>
                    <?php if (scount($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vDeliveryTitle_Default" name="vDeliveryTitle_Default" value="<?= $DeliveryTitleArr[$default_lang]; ?>" data-originalvalue="<?= $DeliveryTitleArr[$default_lang]; ?>" readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDeliveryTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="DeliveryTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="delivery_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryTitle_')">x</button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vDeliveryTitle_' . $vCode;
                                            $$vValue = $DeliveryTitleArr[$vCode];
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
                                                                        onClick="getAllLanguageCode('vDeliveryTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vDeliveryTitle_', '<?= $default_lang ?>');">
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
                                                    onclick="saveDeliveryTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vDeliveryTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                <input type="text" class="form-control" id="vDeliveryTitle_<?= $default_lang ?>" name="vDeliveryTitle_<?= $default_lang ?>" value="<?= $DeliveryTitleArr[$default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveDeliveryTitleSection">Save</button>
                        </div>
                    </div>

                    <div class="row paddingbottom-0">
                        <div class="col-lg-12">
                            <div class="tab">
                                <button class="tablinks manage-singledelivery-tab active" onclick="openTabContent(event, 'manage-singledelivery-content', 'tabcontent-delivery')"> Single Parcel Delivery
                                </button>
                                <button class="tablinks manage-multidelivery-tab" onclick="openTabContent(event, 'manage-multidelivery-content', 'tabcontent-delivery')"> Multiple Parcel Delivery
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="tabcontent tabcontent-delivery display-tab-content" id="manage-singledelivery-content">
                        <div class="col-lg-12">
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vSingleDeliveryTitle_Default"
                                               name="vSingleDeliveryTitle_Default"
                                               value="<?= $userEditDataArr['vSingleDeliveryTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vSingleDeliveryTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editSingleDeliveryTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="SingleDeliveryTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="singledelivery_title_modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vSingleDeliveryTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vSingleDeliveryTitle_' . $vCode;
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
                                                                                onClick="getAllLanguageCode('vSingleDeliveryTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vSingleDeliveryTitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveSingleDeliveryTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vSingleDeliveryTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vSingleDeliverySubTitle_Default"
                                               name="vSingleDeliverySubTitle_Default"
                                               value="<?= $userEditDataArr['vSingleDeliverySubTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vSingleDeliverySubTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editSingleDeliverySubTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="SingleDeliverySubTitle_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="singledelivery_subtitle_modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vSingleDeliverySubTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vSingleDeliverySubTitle_' . $vCode;
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
                                                                                onClick="getAllLanguageCode('vSingleDeliverySubTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vSingleDeliverySubTitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveSingleDeliverySubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vSingleDeliverySubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vSingleDeliveryTitle_<?= $default_lang ?>"
                                               name="vSingleDeliveryTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vSingleDeliveryTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vSingleDeliverySubTitle_<?= $default_lang ?>"
                                               name="vSingleDeliverySubTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vSingleDeliverySubTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vTitleColorSingleDelivery" class="form-control txt-color" value="<?= $vTitleColorSingleDelivery ?>"/>
                                    <input type="hidden" name="vTitleColorSingleDelivery" id="vTitleColorSingleDelivery" value="<?= $vTitleColorSingleDelivery ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vSubTitleColorSingleDelivery" class="form-control txt-color" value="<?= $vSubTitleColorSingleDelivery ?>"/>
                                    <input type="hidden" name="vSubTitleColorSingleDelivery" id="vSubTitleColorSingleDelivery" value="<?= $vSubTitleColorSingleDelivery ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Background Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vBgColorSingleDelivery" class="form-control bg-color" value="<?= $vBgColorSingleDelivery ?>"/>
                                    <input type="hidden" name="vBgColorSingleDelivery" id="vBgColorSingleDelivery" value="<?= $vBgColorSingleDelivery ?>">
                                </div>
                            </div>

                            <div class="row pb-10">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-4 col-sm-4 marginbottom-10">
                                    <?php if(!empty($vImageOldSingleDelivery)) { ?>
                                    <div class="marginbottom-10">
                                        <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldSingleDelivery; ?>" id="singledelivery_img">
                                    </div>
                                    <?php } ?>
                                    <input type="file" class="form-control" name="vImageSingleDelivery" id="vImageSingleDelivery" onchange="previewImage(this, event);" data-img="singledelivery_img">
                                    <input type="hidden" class="form-control" name="vImageOldSingleDelivery" id="vImageOldSingleDelivery" value="<?= $vImageOldSingleDelivery ?>">
                                    <strong class="img-note">Note: Upload only png image size of 512px X 506px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="saveSingleDeliverySection">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tabcontent tabcontent-delivery" id="manage-multidelivery-content">
                        <div class="col-lg-12">
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vMultiDeliveryTitle_Default"
                                               name="vMultiDeliveryTitle_Default"
                                               value="<?= $userEditDataArr['vMultiDeliveryTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vMultiDeliveryTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editMultiDeliveryTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="MultiDeliveryTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="multidelivery_title_modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vMultiDeliveryTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vMultiDeliveryTitle_' . $vCode;
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
                                                                                onClick="getAllLanguageCode('vMultiDeliveryTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vMultiDeliveryTitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveMultiDeliveryTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vMultiDeliveryTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vMultiDeliverySubTitle_Default"
                                               name="vMultiDeliverySubTitle_Default"
                                               value="<?= $userEditDataArr['vMultiDeliverySubTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vMultiDeliverySubTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editMultiDeliverySubTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="MultiDeliverySubTitle_Modal" tabindex="-1" role="dialog"
                                     aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="multidelivery_subtitle_modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vMultiDeliverySubTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vMultiDeliverySubTitle_' . $vCode;
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
                                                                                onClick="getAllLanguageCode('vMultiDeliverySubTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vMultiDeliverySubTitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveMultiDeliverySubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vMultiDeliverySubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vMultiDeliveryTitle_<?= $default_lang ?>"
                                               name="vMultiDeliveryTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vMultiDeliveryTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vMultiDeliverySubTitle_<?= $default_lang ?>"
                                               name="vMultiDeliverySubTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vMultiDeliverySubTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vTitleColorMultiDelivery" class="form-control txt-color" value="<?= $vTitleColorMultiDelivery ?>"/>
                                    <input type="hidden" name="vTitleColorMultiDelivery" id="vTitleColorMultiDelivery" value="<?= $vTitleColorMultiDelivery ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vSubTitleColorMultiDelivery" class="form-control txt-color" value="<?= $vSubTitleColorMultiDelivery ?>"/>
                                    <input type="hidden" name="vSubTitleColorMultiDelivery" id="vSubTitleColorMultiDelivery" value="<?= $vSubTitleColorMultiDelivery ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Background Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vBgColorMultiDelivery" class="form-control bg-color" value="<?= $vBgColorMultiDelivery ?>"/>
                                    <input type="hidden" name="vBgColorMultiDelivery" id="vBgColorMultiDelivery" value="<?= $vBgColorMultiDelivery ?>">
                                </div>
                            </div>

                            <div class="row pb-10">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-4 col-sm-4 marginbottom-10">
                                    <?php if(!empty($vImageOldMultiDelivery)) { ?>
                                    <div class="marginbottom-10">
                                        <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldMultiDelivery; ?>" id="multidelivery_img">
                                    </div>
                                    <?php } ?>
                                    <input type="file" class="form-control" name="vImageMultiDelivery" id="vImageMultiDelivery" onchange="previewImage(this, event);" data-img="multidelivery_img">
                                    <input type="hidden" class="form-control" name="vImageOldMultiDelivery" id="vImageOldMultiDelivery" value="<?= $vImageOldMultiDelivery ?>">
                                    <strong class="img-note">Note: Upload only png image size of 512px X 448px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="saveMultiDeliverySection">Save</button>
                                </div>
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
<?php include_once('footer.php'); ?>
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

        <?php if($isAppHomeScreenV4) { ?>
            var postData = {};
            postData['vTitleArr'] = vTitleArr;
            postData['ViewType'] = 'TitleView';
            postData['ServiceType'] = 'Ride';
            saveHomeScreenData('saveRideServiceSection', postData, 'No');
        <?php } else { ?>
            var postData = new FormData();
            postData.append('vTitleArr', JSON.stringify(vTitleArr));

            var saveRideServiceDisplay = $('#saveRideServiceDisplay').val();

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
            postData.append('saveRideServiceDisplay', saveRideServiceDisplay);
            postData.append('ServiceType', 'Ride');
            saveHomeScreenData('saveRideServiceSection', postData);
        <?php } ?>
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

    function editDeliveryTitle(action) {
        $('#delivery_title_modal_action').html(action);
        $('#DeliveryTitle_Modal').modal('show');
    }

    function saveDeliveryTitle() {
        if ($('#vDeliveryTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliveryTitle_<?= $default_lang ?>_error').show();
            $('#vDeliveryTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliveryTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliveryTitle_Default').val($('#vDeliveryTitle_<?= $default_lang ?>').val());
        $('#vDeliveryTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliveryTitle_Default-error').remove();
        $('#DeliveryTitle_Modal').modal('hide');
    }

    $('#saveDeliveryTitleSection').click(function() {
        var vDeliveryTitleArr = $('[name^="vDeliveryTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vDeliveryTitleArr, function(key, value) {
            if(value.name != "vDeliveryTitle_Default") {
                var name_key = value.name.replace('vDeliveryTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Deliver';
        saveHomeScreenData('saveDeliveryTitleSection', postData, 'No');
    });

    function editSingleDeliveryTitle(action) {
        $('#singledelivery_title_modal_action').html(action);
        $('#SingleDeliveryTitle_Modal').modal('show');
    }

    function saveSingleDeliveryTitle() {
        if ($('#vSingleDeliveryTitle_<?= $default_lang ?>').val() == "") {
            $('#vSingleDeliveryTitle_<?= $default_lang ?>_error').show();
            $('#vSingleDeliveryTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vSingleDeliveryTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vSingleDeliveryTitle_Default').val($('#vSingleDeliveryTitle_<?= $default_lang ?>').val());
        $('#vSingleDeliveryTitle_Default').closest('.row').removeClass('has-error');
        $('#vSingleDeliveryTitle_Default-error').remove();
        $('#SingleDeliveryTitle_Modal').modal('hide');
    }

    function editSingleDeliverySubTitle(action) {
        $('#singledelivery_subtitle_modal_action').html(action);
        $('#SingleDeliverySubTitle_Modal').modal('show');
    }

    function saveSingleDeliverySubTitle() {
        if ($('#vSingleDeliverySubTitle_<?= $default_lang ?>').val() == "") {
            $('#vSingleDeliverySubTitle_<?= $default_lang ?>_error').show();
            $('#vSingleDeliverySubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vSingleDeliverySubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vSingleDeliverySubTitle_Default').val($('#vSingleDeliverySubTitle_<?= $default_lang ?>').val());
        $('#vSingleDeliverySubTitle_Default').closest('.row').removeClass('has-error');
        $('#vSingleDeliverySubTitle_Default-error').remove();
        $('#SingleDeliverySubTitle_Modal').modal('hide');
    }

    $('#saveSingleDeliverySection').click(function() {
        var vSingleDeliveryTitleArr = $('[name^="vSingleDeliveryTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vSingleDeliveryTitleArr, function(key, value) {
            if(value.name != "vSingleDeliveryTitle_Default") {
                var name_key = value.name.replace('vSingleDeliveryTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vSingleDeliverySubTitleArr = $('[name^="vSingleDeliverySubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vSingleDeliverySubTitleArr, function(key, value) {
            if(value.name != "vSingleDeliverySubTitle_Default") {
                var name_key = value.name.replace('vSingleDeliverySubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });
        var vImageSingleDelivery = $('#vImageSingleDelivery')[0].files[0];
        var vImageOldSingleDelivery = $('#vImageOldSingleDelivery').val();
        var vTitleColorSingleDelivery = $('#vTitleColorSingleDelivery').val();
        var vSubTitleColorSingleDelivery = $('#vSubTitleColorSingleDelivery').val();
        var vBgColorSingleDelivery = $('#vBgColorSingleDelivery').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageSingleDelivery);
        postData.append('vImageOld', vImageOldSingleDelivery);
        postData.append('vTxtTitleColor', vTitleColorSingleDelivery);
        postData.append('vTxtSubTitleColor', vSubTitleColorSingleDelivery);
        postData.append('vBgColor', vBgColorSingleDelivery);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'Deliver');
        postData.append('ServiceTypeOther', 'Delivery');

        saveHomeScreenData('saveSingleDeliverySection', postData);
    });

    function editMultiDeliveryTitle(action) {
        $('#multidelivery_title_modal_action').html(action);
        $('#MultiDeliveryTitle_Modal').modal('show');
    }

    function saveMultiDeliveryTitle() {
        if ($('#vMultiDeliveryTitle_<?= $default_lang ?>').val() == "") {
            $('#vMultiDeliveryTitle_<?= $default_lang ?>_error').show();
            $('#vMultiDeliveryTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vMultiDeliveryTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vMultiDeliveryTitle_Default').val($('#vMultiDeliveryTitle_<?= $default_lang ?>').val());
        $('#vMultiDeliveryTitle_Default').closest('.row').removeClass('has-error');
        $('#vMultiDeliveryTitle_Default-error').remove();
        $('#MultiDeliveryTitle_Modal').modal('hide');
    }

    function editMultiDeliverySubTitle(action) {
        $('#multidelivery_subtitle_modal_action').html(action);
        $('#MultiDeliverySubTitle_Modal').modal('show');
    }

    function saveMultiDeliverySubTitle() {
        if ($('#vMultiDeliverySubTitle_<?= $default_lang ?>').val() == "") {
            $('#vMultiDeliverySubTitle_<?= $default_lang ?>_error').show();
            $('#vMultiDeliverySubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vMultiDeliverySubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vMultiDeliverySubTitle_Default').val($('#vMultiDeliverySubTitle_<?= $default_lang ?>').val());
        $('#vMultiDeliverySubTitle_Default').closest('.row').removeClass('has-error');
        $('#vMultiDeliverySubTitle_Default-error').remove();
        $('#MultiDeliverySubTitle_Modal').modal('hide');
    }

    $('#saveMultiDeliverySection').click(function() {
        var vMultiDeliveryTitleArr = $('[name^="vMultiDeliveryTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vMultiDeliveryTitleArr, function(key, value) {
            if(value.name != "vMultiDeliveryTitle_Default") {
                var name_key = value.name.replace('vMultiDeliveryTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vMultiDeliverySubTitleArr = $('[name^="vMultiDeliverySubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vMultiDeliverySubTitleArr, function(key, value) {
            if(value.name != "vMultiDeliverySubTitle_Default") {
                var name_key = value.name.replace('vMultiDeliverySubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });
        var vImageMultiDelivery = $('#vImageMultiDelivery')[0].files[0];
        var vImageOldMultiDelivery = $('#vImageOldMultiDelivery').val();
        var vTitleColorMultiDelivery = $('#vTitleColorMultiDelivery').val();
        var vSubTitleColorMultiDelivery = $('#vSubTitleColorMultiDelivery').val();
        var vBgColorMultiDelivery = $('#vBgColorMultiDelivery').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageMultiDelivery);
        postData.append('vImageOld', vImageOldMultiDelivery);
        postData.append('vTxtTitleColor', vTitleColorMultiDelivery);
        postData.append('vTxtSubTitleColor', vSubTitleColorMultiDelivery);
        postData.append('vBgColor', vBgColorMultiDelivery);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'Deliver');
        postData.append('ServiceTypeOther', 'MultipleDelivery');

        saveHomeScreenData('saveMultiDeliverySection', postData);
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

        var postData = new FormData();


        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>TitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>Title', 'vTitle');
                postData.append('vTitleArr['+name_key+']', value.value);
            }
        });

        var v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitleArr = $('[name^="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>SubTitle', 'vSubtitle');

                postData.append('vSubTitleArr['+name_key+']', value.value);
            }
        });
        var vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>')[0].files[0];
        var vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>').val();

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

        var postData = new FormData();

        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoTitle', 'vTitle');
                postData.append('vTitleArr['+name_key+']', value.value);
            }
        });

        var v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitleArr = $('[name^="v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitleArr, function(key, value) {
            if(value.name != "v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle_Default") {
                var name_key = value.name.replace('v<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSubTitle', 'vSubtitle');
                postData.append('vSubTitleArr['+name_key+']', CKEDITOR.instances[value.name].getData());
            }
        });
        var vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info = $('#vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info')[0].files[0];
        var vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info = $('#vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info').val();

      /*  var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));*/
        postData.append('vImage', vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info);
        postData.append('vImageOld', vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info);
        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'Other');
        postData.append('ServiceTypeOther', '<?= $OTHER_SERVICE['ManageServiceKey'] ?>Info');

        saveHomeScreenData('save<?= $OTHER_SERVICE['ManageServiceKey'] ?>InfoSection', postData);
    });
    <?php } ?>

    function saveDisplayOrderService(ServiceType) {
        var iDisplayOrderArr = {};

        var DisplayOrderElem = $('[name^="iDisplayOrderTaxiServiceArr"]');

        $.each(DisplayOrderElem, function(key, value) {
            var name_key = value.getAttribute('data-serviceid');
            iDisplayOrderArr[name_key] = value.value;
        });

        var postData = {};
        postData['ViewType'] = 'ServiceDisplayOrder';
        postData['ServiceType'] = ServiceType;
        postData['iDisplayOrderArr'] = iDisplayOrderArr;

        $('#loaderIcon span').hide();
        $('#loaderIcon').show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_manage_app_home_screen.php',
            'AJAX_DATA': postData
        };
        getDataFromAjaxCall(ajaxData, function(response) {
            if(response.action == "1") {
                location.reload();
            } else {
                
            }
        });
    }

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

    $(".txt-color").on("input", function () {
        var color = $(this).val();
        var input_id = $(this).data('id');
        $('#' + input_id).val(color);
    });


    $(".bg-color").on("input", function () {
        var color = $(this).val();
        var input_id = $(this).data('id');
        $('#' + input_id).val(color);
    });

    function openTabContent(evt, Pagename, tabcontent_hide) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = $('.' + tabcontent_hide).hide();

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = $(evt.currentTarget).closest('.tab').find('.tablinks').removeClass('active');

        // Show the current tab, and add an "active" class to the button that opened the tab
        $('#' + Pagename).show();
        $(evt.currentTarget).addClass('active');
    }
</script>
</body>
<!-- END BODY-->
</html>