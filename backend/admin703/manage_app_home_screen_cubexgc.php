<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-app-home-screen-view')) {
    $userObj->redirect();
}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'ManageAppHomePage';

$tbl_name = "app_home_screen_view";

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = count($db_master);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$userEditDataArr = $db_data_arr = array();
$db_data = $obj->MySQLSelect("SELECT * FROM $tbl_name");
foreach ($db_data as $db_value) {
    $ViewType = !empty($db_value['eServiceType']) ? $db_value['eServiceType'] : $db_value['eViewType'];
    if($db_value['eViewType'] == "StoreItemView") {
        $tServiceDetails = json_decode($db_value['tServiceDetails'], true);
        $db_data_arr['StoreItemView'][$db_value['eServiceType']]['iServiceId_' . $tServiceDetails['iServiceId']] = $db_value;
    } else {
        $db_data_arr[$ViewType] = $db_value;
    }
}

// echo "<pre>"; print_r($db_data_arr); exit;
/* All Services */
$allServiceData = $obj->MySQLSelect("SELECT iVehicleCategoryId, iDisplayOrder,vListLogo3, vCategory_" . $default_lang . " as vCategory, eStatus, iDisplayOrder FROM " . $sql_vehicle_category_table_name . " WHERE eStatus != 'Deleted' AND iParentId = '0' AND eCatType NOT IN ('Donation') ORDER BY iDisplayOrder, iVehicleCategoryId ASC");

/* Wallet, Gift Card & Cart */
$tLayoutDetailsOther = json_decode($db_data_arr['CardIconTextView']['tLayoutDetails'], true);

$labelsOther = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_WALLET_TITLE_HOME_SCREEN_TXT', 'LBL_WALLET_SUBTITLE_HOME_SCREEN_TXT', 'LBL_GIFT_CARD_TITLE_HOME_SCREEN_TXT', 'LBL_GIFT_CARD_SUBTITLE_HOME_SCREEN_TXT', 'LBL_CART_TITLE_HOME_SCREEN_TXT', 'LBL_CART_SUBTITLE_HOME_SCREEN_TXT') ");

$WalletTitleArr = $WalletSubTitleArr = $GiftCardTitleArr = $GiftCardSubTitleArr = $CartTitleArr = $CartSubTitleArr = array();
foreach ($labelsOther as $label) {
    if($label['vLabel'] == 'LBL_WALLET_TITLE_HOME_SCREEN_TXT') {
        $WalletTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_WALLET_SUBTITLE_HOME_SCREEN_TXT') {
        $WalletSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_GIFT_CARD_TITLE_HOME_SCREEN_TXT') {
        $GiftCardTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_GIFT_CARD_SUBTITLE_HOME_SCREEN_TXT') {
        $GiftCardSubTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_CART_TITLE_HOME_SCREEN_TXT') {
        $CartTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_CART_SUBTITLE_HOME_SCREEN_TXT') {
        $CartSubTitleArr[$label['vCode']] = $label['vValue'];

    }
}

$OTHER_SERVICES_ARR = array();
$OTHER_SERVICES_ARR[] = array(
    'ServiceTitle' => $WalletTitleArr, 
    'ServiceDesc' => $WalletSubTitleArr,
    'vTxtTitleColor' => $tLayoutDetailsOther['Wallet']['vTxtTitleColor'],
    'vTxtSubTitleColor' => $tLayoutDetailsOther['Wallet']['vTxtSubTitleColor'],
    'vBgColor' => $tLayoutDetailsOther['Wallet']['vBgColor'],
    'ManageServiceKey' => 'Wallet', 
    'HiddenInput' => 'saveWallet'
);

if($MODULES_OBJ->isEnableGiftCardFeature()) {
    $OTHER_SERVICES_ARR[] = array(
        'ServiceTitle' => $GiftCardTitleArr, 
        'ServiceDesc' => $GiftCardSubTitleArr,
        'vTxtTitleColor' => $tLayoutDetailsOther['GiftCard']['vTxtTitleColor'],
        'vTxtSubTitleColor' => $tLayoutDetailsOther['GiftCard']['vTxtSubTitleColor'],
        'vBgColor' => $tLayoutDetailsOther['GiftCard']['vBgColor'],
        'ManageServiceKey' => 'GiftCard', 
        'HiddenInput' => 'saveGiftCard'
    );
}

if($MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    $OTHER_SERVICES_ARR[] = array(
        'ServiceTitle' => $CartTitleArr, 
        'ServiceDesc' => $CartSubTitleArr,
        'vTxtTitleColor' => $tLayoutDetailsOther['Cart']['vTxtTitleColor'],
        'vTxtSubTitleColor' => $tLayoutDetailsOther['Cart']['vTxtSubTitleColor'],
        'vBgColor' => $tLayoutDetailsOther['Cart']['vBgColor'],
        'ManageServiceKey' => 'Cart', 
        'HiddenInput' => 'saveCart'
    );
}

$vImageOld['Wallet'] = getMoreServicesIconName('ic_wallet_topup.png');
$vImageOld['GiftCard'] = getMoreServicesIconName('ic_gift_card.png');
$vImageOld['Cart'] = getMoreServicesIconName('ic_cart.png');


/* General Banners */
$vGeneralBannerTitleArr = json_decode($db_data_arr['GeneralBanner']['vTitle'], true);
foreach ($vGeneralBannerTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vGeneralBannerTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$bannerData = $obj->MySQLSelect("SELECT * FROM banners WHERE iServiceId = 0 AND vCode = '$default_lang' AND eType = 'General' AND eFor = 'General' AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 0,3");


/* Food Delivery - Nearby Restaurants */
$RestaurantsNearbyTitleArr = json_decode($db_data_arr['StoreItemView']['DeliverAllNearby']['iServiceId_1']['vTitle'], true);
foreach ($RestaurantsNearbyTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vRestaurantsNearbyTitle_', $key);
    $userEditDataArr[$key] = $value;
}

/* Food Delivery - Top Rated Restaurants */
$RestaurantsTopRatedTitleArr = json_decode($db_data_arr['StoreItemView']['DeliverAllTopRated']['iServiceId_1']['vTitle'], true);
foreach ($RestaurantsTopRatedTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vRestaurantsTopRatedTitle_', $key);
    $userEditDataArr[$key] = $value;
}

/* Food Delivery - Restaurant Items */
$RestaurantItemsTitleArr = json_decode($db_data_arr['StoreItemView']['DeliverAllItems']['iServiceId_1']['vTitle'], true);
foreach ($RestaurantItemsTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vRestaurantItemsTitle_', $key);
    $userEditDataArr[$key] = $value;
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


/* Parcel Delivery */
$vDeliverTitleArr = json_decode($db_data_arr['Deliver']['vTitle'], true);
foreach ($vDeliverTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vDeliverTitle_', $key);
    $userEditDataArr[$key] = $value;
}

$deliverData = $obj->MySQLSelect("SELECT * FROM " . $sql_vehicle_category_table_name . " WHERE iParentId = '178'");
$deliverDataArr = array();
foreach ($deliverData as $deliverSubData) {
    $deliverDataArr[$deliverSubData['iVehicleCategoryId']] = $deliverSubData;
}

$DeliveryLayoutDetails = json_decode($db_data_arr['Deliver']['tLayoutDetails'], true);

/* Single Delivery */
$SingleDeliveryId = 186;
foreach ($db_master as $db_lang) {
    $userEditDataArr['vSingleDeliveryTitle_' . $db_lang['vCode']] = $deliverDataArr[$SingleDeliveryId]['vCategory_' . $db_lang['vCode']];
    $userEditDataArr['vSingleDeliverySubTitle_' . $db_lang['vCode']] = $deliverDataArr[$SingleDeliveryId]['tCategoryDesc_' . $db_lang['vCode']];
}
$vImageOldSingleDelivery = $deliverDataArr[$SingleDeliveryId]['vLogo2'];

$SingleDeliveryLayoutDetails = $DeliveryLayoutDetails['iVehicleCategoryId_' . $SingleDeliveryId];
$vTitleColorSingleDelivery = $SingleDeliveryLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorSingleDelivery = $SingleDeliveryLayoutDetails['vTxtSubTitleColor']; 
$vBgColorSingleDelivery = $SingleDeliveryLayoutDetails['vBgColor']; 

/* Multi Delivery */
$MultiDeliveryId = 181;
foreach ($db_master as $db_lang) {
    $userEditDataArr['vMultiDeliveryTitle_' . $db_lang['vCode']] = $deliverDataArr[$MultiDeliveryId]['vCategory_' . $db_lang['vCode']];
    $userEditDataArr['vMultiDeliverySubTitle_' . $db_lang['vCode']] = $deliverDataArr[$MultiDeliveryId]['tCategoryDesc_' . $db_lang['vCode']];
}
$vImageOldMultiDelivery = $deliverDataArr[$MultiDeliveryId]['vLogo2'];

$MultiDeliveryLayoutDetails = $DeliveryLayoutDetails['iVehicleCategoryId_' . $MultiDeliveryId];
$vTitleColorMultiDelivery = $MultiDeliveryLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorMultiDelivery = $MultiDeliveryLayoutDetails['vTxtSubTitleColor']; 
$vBgColorMultiDelivery = $MultiDeliveryLayoutDetails['vBgColor']; 


/* Grocery Delivery - Nearby Stores */
$GroceryNearbyTitleArr = json_decode($db_data_arr['StoreItemView']['DeliverAllNearby']['iServiceId_2']['vTitle'], true);
foreach ($GroceryNearbyTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vGroceryNearbyTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$deliverallData = $obj->MySQLSelect("SELECT iVehicleCategoryId, iServiceId, iDisplayOrder, vCategory_" . $default_lang . " as vCategory FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND eCatType IN ('DeliverAll') AND iServiceId > 0 AND iServiceId NOT IN (1,2,5) AND iParentId = '0' ORDER BY iDisplayOrder, iVehicleCategoryId ASC ");

$tServiceDetailsDeliverAll = $db_data_arr['DeliverAll']['tServiceDetails'];
$tServiceDetailsDeliverAllArr = array();
if (!empty($tServiceDetailsDeliverAll)) {
    $tServiceDetailsDeliverAllArr = json_decode($tServiceDetailsDeliverAll, true);
}


/* Delivery Genie & Runner */
$genieRunnerData = $obj->MySQLSelect("SELECT * FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId IN (280,287)");
$genieRunnerDataArr = array();
foreach ($genieRunnerData as $genieRunnerSubData) {
    $genieRunnerDataArr[$genieRunnerSubData['iVehicleCategoryId']] = $genieRunnerSubData;
}

$genieRunnerLayoutDetails = json_decode($db_data_arr['BuyAnyService']['tLayoutDetails'], true);

/* Genie */
$DeliveryGenieId = 280;
foreach ($db_master as $db_lang) {
    $userEditDataArr['vDeliveryGenieTitle_' . $db_lang['vCode']] = $genieRunnerDataArr[$DeliveryGenieId]['vCategory_' . $db_lang['vCode']];
    $userEditDataArr['vDeliveryGenieSubTitle_' . $db_lang['vCode']] = $genieRunnerDataArr[$DeliveryGenieId]['tCategoryDesc_' . $db_lang['vCode']];
}
$vImageOldDeliveryGenie = $genieRunnerDataArr[$DeliveryGenieId]['vLogo2'];

$DeliveryGenieLayoutDetails = $genieRunnerLayoutDetails['iVehicleCategoryId_' . $DeliveryGenieId];
$vTitleColorDeliveryGenie = $DeliveryGenieLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorDeliveryGenie = $DeliveryGenieLayoutDetails['vTxtSubTitleColor']; 
$vBgColorDeliveryGenie = $DeliveryGenieLayoutDetails['vBgColor']; 

/* Runner */
$DeliveryRunnerId = 287;
foreach ($db_master as $db_lang) {
    $userEditDataArr['vDeliveryRunnerTitle_' . $db_lang['vCode']] = $genieRunnerDataArr[$DeliveryRunnerId]['vCategory_' . $db_lang['vCode']];
    $userEditDataArr['vDeliveryRunnerSubTitle_' . $db_lang['vCode']] = $genieRunnerDataArr[$DeliveryRunnerId]['tCategoryDesc_' . $db_lang['vCode']];
}
$vImageOldDeliveryRunner = $genieRunnerDataArr[$DeliveryRunnerId]['vLogo2'];

$DeliveryRunnerLayoutDetails = $genieRunnerLayoutDetails['iVehicleCategoryId_' . $DeliveryRunnerId];
$vTitleColorDeliveryRunner = $DeliveryRunnerLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorDeliveryRunner = $DeliveryRunnerLayoutDetails['vTxtSubTitleColor']; 
$vBgColorDeliveryRunner = $DeliveryRunnerLayoutDetails['vBgColor']; 


/* Pharmacy Delivery - Nearby Stores */
$PharmacyNearbyTitleArr = json_decode($db_data_arr['StoreItemView']['DeliverAllNearby']['iServiceId_5']['vTitle'], true);
foreach ($PharmacyNearbyTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vPharmacyNearbyTitle_', $key);
    $userEditDataArr[$key] = $value;
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
    <link rel="stylesheet" href="css/admin_new/admin_app_home_screen.css">
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
                    <div class="show-help-section section-title">All Services</div>
                    <div class="underline-section-title"></div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label>Services</label>
                        </div>
                        <div class="col-lg-6">
                            <div class="manage-banner-section">
                                <div class="service-img-block service-img-grid">
                                    <?php $ServiceCount = 0; foreach ($allServiceData as $ServiceData) {
                                        if (!empty($ServiceData['vListLogo3']) && $ServiceCount < 3) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ServiceData['iVehicleCategoryId'] . "/" . $ServiceData['vListLogo3'];
                                    ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $vServiceImg ?>">
                                        <div class="service-img-title"><?= $ServiceData['vCategory'] ?></div>
                                    </div>
                                
                                    <?php $ServiceCount++; } } ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_all_gbx.png'); ?>">
                                        <div class="service-img-title"><?= $langage_lbl_admin['LBL_ALL_SERVICES_TXT'] ?></div>
                                    </div>
                                </div>
                                <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#taxiservices_modal" style="margin-top: 25px;">Manage Services for App</button>
                            </div>
                            <div>
                                <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="taxiservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content nimot-class">
                                <div class="modal-header">
                                    <h4>
                                        All Services
                                        <button type="button" class="close" data-dismiss="modal">x</button>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th style="text-align: center;">Icon</th>
                                            <th>Service Category</th>
                                            <th style="text-align: center;">Status</th>
                                            <th>Display Order</th>
                                            <th style="text-align: center;">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($allServiceData as $ServiceData) {
                                            $vServiceDisplayOrder = $ServiceData['iDisplayOrder'];
                                            $vServiceImg = "";
                                            if (!empty($ServiceData['vListLogo3'])) {
                                                $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ServiceData['iVehicleCategoryId'] . "/" . $ServiceData['vListLogo3'];
                                            }

                                            $editUrl = $tconfig['tsite_url_main_admin'] . 'vehicle_category_action.php?id=' . $ServiceData['iVehicleCategoryId'] . '&eServiceType=Ride';
                                            ?>
                                            <tr>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <?php if (!empty($vServiceImg)) { ?>
                                                        <img src="<?= $vServiceImg ?>">
                                                    <?php } else { ?>
                                                        --
                                                    <?php } ?>
                                                </td>
                                                <td style="vertical-align: middle;"><?= $ServiceData['vCategory'] ?></td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <?php
                                                    if ($ServiceData['eStatus'] == 'Active') {
                                                        $status_img = "img/active-icon.png";
                                                    } else if ($ServiceData['eStatus'] == 'Inactive') {
                                                        $status_img = "img/inactive-icon.png";
                                                    } else {
                                                        $status_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $ServiceData['eStatus']; ?>">
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <select class="form-control" name="iDisplayOrderAllServiceArr[]" data-serviceid="<?= $ServiceData['iVehicleCategoryId'] ?>">
                                                        <?php for ($disp_order = 1; $disp_order <= count($allServiceData); $disp_order++) { ?>
                                                            <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                </td>
                                            </tr>
                                        <?php }                                              
                                                                                        
                                            $editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=all_services_gc&vLabel=LBL_ALL_SERVICES_TXT';

                                            $vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_all_gbx.png');
                                        
                                        ?>
                                        <tr>
                                            <td style="text-align: center; ">
                                                <?php if (!empty($vServicemoreImg)) { ?>
                                                    <img src="<?= $vServicemoreImg ?>">
                                                <?php } else { ?>
                                                    --
                                                <?php } ?>
                                            </td>
                                            <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_ALL_SERVICES_TXT'] ?></td>
                                            <td style="text-align: center;vertical-align: middle;">
                                                ---
                                            </td>
                                            <td style="text-align: center;vertical-align: middle;">
                                                ---
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <a href="<?= $editUrl_more ?>" class="btn btn-primary" target="_blank">Edit</a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer" style="text-align: left">
                                    <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('All')">Save</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <div class="show-help-section section-title">Other Features</div>
                    <div class="underline-section-title"></div>

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
                            <div class="tab-pane active">
                                <?php if (count($db_master) > 1) { ?>
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
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
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
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
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

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" class="form-control txt-color" value="<?= $OTHER_SERVICE['vTxtTitleColor'] ?>"/>
                                        <input type="hidden" name="vTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" id="vTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" value="<?= $OTHER_SERVICE['vTxtTitleColor'] ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vSubTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" class="form-control txt-color" value="<?= $OTHER_SERVICE['vTxtSubTitleColor'] ?>"/>
                                        <input type="hidden" name="vSubTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" id="vSubTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" value="<?= $OTHER_SERVICE['vTxtSubTitleColor'] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Background Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vBgColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" class="form-control bg-color" value="<?= $OTHER_SERVICE['vBgColor'] ?>"/>
                                        <input type="hidden" name="vBgColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" id="vBgColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>" value="<?= $OTHER_SERVICE['vBgColor'] ?>">
                                    </div>
                                </div>

                                <div class="row pb-10">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4 marginbottom-10">
                                        <?php if(!empty($vImageOld[$OTHER_SERVICE['ManageServiceKey']])) { ?>
                                        <div class="marginbottom-10">
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=70&src=' . $tconfig['tsite_url'] . 'webimages/icons/DefaultImg/' . $vImageOld[$OTHER_SERVICE['ManageServiceKey']]; ?>" id="<?= strtolower($OTHER_SERVICE['ManageServiceKey']) ?>_img">
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
                        </div>
                    </div>
                    <?php $os_count++; } ?>

                    <hr />
                    <div class="show-help-section section-title">General Banners</div>
                    <div class="underline-section-title"></div>
                    <?php if (count($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>General Banner Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vGeneralBannerTitle_Default"
                                       name="vGeneralBannerTitle_Default"
                                       value="<?= $userEditDataArr['vGeneralBannerTitle_' . $default_lang]; ?>"
                                       data-originalvalue="<?= $userEditDataArr['vGeneralBannerTitle_' . $default_lang]; ?>"
                                       readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editGeneralBannerTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="GeneralBannerTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="generalbanner_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vGeneralBannerTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vGeneralBannerTitle_' . $vCode;
                                            $$vValue = $userEditDataArr[$vValue];
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <?php
                                            $page_title_class = 'col-lg-12';
                                            if (count($db_master) > 1) {
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
                                                if (count($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vGeneralBannerTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vGeneralBannerTitle_', '<?= $default_lang ?>');">
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
                                                    onclick="saveGeneralBannerTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vGeneralBannerTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                <input type="text" class="form-control" id="vGeneralBannerTitle_<?= $default_lang ?>"
                                       name="vGeneralBannerTitle_<?= $default_lang ?>"
                                       value="<?= $userEditDataArr['vGeneralBannerTitle_' . $default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveGeneralBannerTitleSection">Save</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="manage-banner-section">
                                <?php if (count($bannerData) > 0) { ?>
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

                    <?php if($MODULES_OBJ->isDeliverAllFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Food Delivery - Nearby Restaurants</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vRestaurantsNearbyTitle_Default" name="vRestaurantsNearbyTitle_Default" value="<?= $userEditDataArr['vRestaurantsNearbyTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vRestaurantsNearbyTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editRestaurantsNearbyTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="RestaurantsNearbyTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="restaurantsnearby_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRestaurantsNearbyTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vRestaurantsNearbyTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRestaurantsNearbyTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRestaurantsNearbyTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveRestaurantsNearbyTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRestaurantsNearbyTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vRestaurantsNearbyTitle_<?= $default_lang ?>"
                                           name="vRestaurantsNearbyTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vRestaurantsNearbyTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveRestaurantsNearbyTitleSection">Save</button>
                                <strong class="img-note">Note: Food Delivery Service must be active to see changes in App Home Screen. <br>This section will display nearby restaurants based on the user's current location.</strong>
                            </div>
                        </div>

                        <hr />
                        <div class="show-help-section section-title">Food Delivery - Top Rated Restaurants</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vRestaurantsTopRatedTitle_Default" name="vRestaurantsTopRatedTitle_Default" value="<?= $userEditDataArr['vRestaurantsTopRatedTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vRestaurantsTopRatedTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editRestaurantsTopRatedTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="RestaurantsTopRatedTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="restaurantstoprated_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRestaurantsTopRatedTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vRestaurantsTopRatedTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRestaurantsTopRatedTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRestaurantsTopRatedTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveRestaurantsTopRatedTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRestaurantsTopRatedTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vRestaurantsTopRatedTitle_<?= $default_lang ?>" name="vRestaurantsTopRatedTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vRestaurantsTopRatedTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveRestaurantsTopRatedTitleSection">Save</button>
                                <strong class="img-note">Note: Food Delivery Service must be active to see changes in App Home Screen. <br>This section will display the top-rated restaurants near the user's current location.</strong>
                            </div>
                        </div>

                        <hr />
                        <div class="show-help-section section-title">Food Delivery - Restaurant Items</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vRestaurantItemsTitle_Default" name="vRestaurantItemsTitle_Default" value="<?= $userEditDataArr['vRestaurantItemsTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vRestaurantItemsTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editRestaurantItemsTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="RestaurantItemsTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="restaurantitems_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRestaurantItemsTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vRestaurantItemsTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRestaurantItemsTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRestaurantItemsTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveRestaurantItemsTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRestaurantItemsTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vRestaurantItemsTitle_<?= $default_lang ?>"
                                           name="vRestaurantItemsTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vRestaurantItemsTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveRestaurantItemsTitleSection">Save</button>
                                <strong class="img-note">
                                    Note: Food Delivery Service must be active to see changes in App Home Screen. 
                                    <br>This section will display food items from nearby restaurants based on the user's current location.
                                    <br>If a user has placed orders, this section will show food items from those nearby restaurants based on the user's current location. </strong>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if($MODULES_OBJ->isRideFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Taxi Services</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
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
                                                if (count($db_master) > 1) {
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
                                                    if (count($db_master) > 1) {
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
                                    <div class="service-img-block service-img-grid">
                                    <?php $RideServiceDataArr = array(); foreach ($rideData as $rideArr) {
                                        if (isset($tServiceDetailsRideArr['iVehicleCategoryId_' . $rideArr['iVehicleCategoryId']])) {
                                            $tServiceDetails = $tServiceDetailsRideArr['iVehicleCategoryId_' . $rideArr['iVehicleCategoryId']];
                                            if (!empty($tServiceDetails['vImage'])) {
                                                $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=60&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $tServiceDetails['vImage'];
                                            }
                                            $vServiceImgOld = $tServiceDetails['vImage'];
                                            if ($tServiceDetails['eStatus'] == "Active") {
                                                $rideArr['iDispOrderService'] = $tServiceDetails['iDisplayOrder'];
                                                $rideArr['vServiceImg'] = $vServiceImg;
                                                $RideServiceDataArr[] = $rideArr;
                                            }
                                        }
                                    }

                                    $sort_data = array_column($RideServiceDataArr, 'iDispOrderService');
                                    array_multisort($sort_data, SORT_ASC, $RideServiceDataArr);

                                    foreach ($RideServiceDataArr as $rideArr) {
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $rideArr['vServiceImg'] ?>">
                                            <div class="service-img-title"><?= $rideArr['vCategory'] ?></div>
                                        </div>
                                    
                                    <?php } ?>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#rideservices_modal" style="margin-top: 25px;">Manage Services for App Home Screen</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
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
                                            Icons uploaded here will only be shown on App home screen.
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
                                                                <?php for ($disp_order = 1; $disp_order <= count($rideData); $disp_order++) { ?>
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
                    <?php } ?>
                
                    <?php if ($MODULES_OBJ->isDeliveryFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Parcel Delivery</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vDeliverTitle_Default"
                                       name="vDeliverTitle_Default"
                                       value="<?= $userEditDataArr['vDeliverTitle_' . $default_lang]; ?>"
                                       data-originalvalue="<?= $userEditDataArr['vDeliverTitle_' . $default_lang]; ?>"
                                       readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editDeliverTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="DeliverTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="deliver_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vDeliverTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vDeliverTitle_' . $vCode;
                                            $$vValue = $userEditDataArr[$vValue];
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <?php
                                            $page_title_class = 'col-lg-12';
                                            if (count($db_master) > 1) {
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
                                                if (count($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vDeliverTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vDeliverTitle_', '<?= $default_lang ?>');">
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
                                                    onclick="saveDeliverTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vDeliverTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vDeliverTitle_<?= $default_lang ?>"
                                           name="vDeliverTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vDeliverTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveDeliverTitleSection">Save</button>
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
                                <?php if (count($db_master) > 1) { ?>
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
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
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
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
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
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images_vehicle_category'] . '/' . $SingleDeliveryId . '/android/' . $vImageOldSingleDelivery; ?>" id="singledelivery_img">
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
                                <?php if (count($db_master) > 1) { ?>
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
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
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
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
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
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images_vehicle_category'] . '/' . $MultiDeliveryId . '/android/' . $vImageOldMultiDelivery; ?>" id="multidelivery_img">
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
                    <?php } ?>

                    <?php if($MODULES_OBJ->isDeliverAllFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Grocery Delivery - Nearby Stores</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vGroceryNearbyTitle_Default" name="vGroceryNearbyTitle_Default" value="<?= $userEditDataArr['vGroceryNearbyTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vGroceryNearbyTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editGroceryNearbyTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="GroceryNearbyTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="grocerynearby_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vGroceryNearbyTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vGroceryNearbyTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vGroceryNearbyTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vGroceryNearbyTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveGroceryNearbyTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vGroceryNearbyTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vGroceryNearbyTitle_<?= $default_lang ?>"
                                           name="vGroceryNearbyTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vGroceryNearbyTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveGroceryNearbyTitleSection">Save</button>
                                <strong class="img-note">Note: Grocery Delivery Service must be active to see changes in App Home Screen. <br>This section will display nearby grocery stores based on the user's current location.</strong>
                            </div>
                        </div>

                        <hr />
                        <div class="show-help-section section-title">Store Delivery Services</div>
                        <div class="underline-section-title"></div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block service-img-grid" style="max-width: 410px; padding: 16px 10px 16px 20px !important;">
                                    <?php foreach ($deliverallData as $deliverallArr) {
                                        $ServiceImage = $tServiceDetailsDeliverAllArr['iServiceId_' . $deliverallArr['iServiceId']];
                                        if (!empty($ServiceImage)) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=60&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $ServiceImage;
                                        }
                                        $vServiceImgOld = $ServiceImage;
                                        
                                    ?>
                                        <div class="service-box-preview-img">
                                            <div class="service-img-title"><?= $deliverallArr['vCategory'] ?></div>
                                            <img src="<?= $vServiceImg ?>">
                                        </div>
                                    
                                    <?php }?>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#deliverallservices_modal" style="margin-top: 25px;">Manage Services for App Home Screen</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app. <br>The above services must be active to see changes in App Home Screen.</strong>
                                </div>
                            </div>                            
                        </div>

                        <div class="modal fade" id="deliverallservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            Store Delivery Services
                                            <button type="button" class="close" data-dismiss="modal">x</button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            <strong>Note:</strong>
                                            Icons uploaded here will only be shown on App home screen.
                                            <br><br>
                                            <strong>Upload only png image size of 512px X 512px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> <br/> Please make sure to save main page below after saving and closing this popup window to upload images.</strong>

                                        </p>
                                        <input type="hidden" name="saveDeliverAllServiceDisplay" id="saveDeliverAllServiceDisplay" value="No">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th style="text-align: center;">Icon</th>
                                                <th>Service Category</th>
                                                <th>Upload Icon</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($deliverallData as $deliverallArr) {
                                                $vServiceImg = "";
                                                $vServiceImgOld = "";
                                                $ServiceImage = $tServiceDetailsDeliverAllArr['iServiceId_' . $deliverallArr['iServiceId']];
                                                if (!empty($ServiceImage)) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?h=50&src=' . $tconfig["tsite_upload_app_home_screen_images"] . 'AppHomeScreen/' . $ServiceImage;
                                                }
                                                $vServiceImgOld = $ServiceImage;
                                                $editUrl = $tconfig['tsite_url_main_admin'] . 'vehicle_category_action.php?id=' . $deliverallArr['iVehicleCategoryId'] . '&eServiceType=DeliverAll';
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServiceImg)) { ?>
                                                            <img src="<?= $vServiceImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $deliverallArr['vCategory'] ?></td>

                                                    <td>
                                                        <input type="file" class="form-control" name="vDeliverAllImage[]">
                                                        <input type="hidden" class="form-control" name="vDeliverAllImageOld[]" value="<?= $vServiceImgOld ?>">
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                        <input type="hidden" name="iServiceIdVal[]" value="<?= $deliverallArr['iServiceId'] ?>">
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer" style="text-align: left">
                                        <button type="button" class="btn btn-default" onclick="saveDeliverAllServices('Yes')">Save
                                        </button>
                                        <button type="button" class="btn btn-default" onclick="saveDeliverAllServices('No')">Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveDeliverAllServiceSection">Save</button>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if($MODULES_OBJ->isEnableGenieFeature() || $MODULES_OBJ->isEnableRunnerFeature()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Delivery Genie & Runner</div>
                        <div class="underline-section-title"></div>

                        <div class="row paddingbottom-0">
                            <div class="col-lg-12">
                                <div class="tab">
                                    <button class="tablinks manage-deliverygenie-tab active" onclick="openTabContent(event, 'manage-deliverygenie-content', 'tabcontent-genierunner')"> Delivery Genie
                                    </button>
                                    <button class="tablinks manage-deliveryrunner-tab" onclick="openTabContent(event, 'manage-deliveryrunner-content', 'tabcontent-genierunner')"> Delivery Runner
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tabcontent tabcontent-genierunner display-tab-content" id="manage-deliverygenie-content">
                            <div class="col-lg-12">
                                <?php if (count($db_master) > 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Title</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input type="text" class="form-control" id="vDeliveryGenieTitle_Default"
                                                   name="vDeliveryGenieTitle_Default"
                                                   value="<?= $userEditDataArr['vDeliveryGenieTitle_' . $default_lang]; ?>"
                                                   data-originalvalue="<?= $userEditDataArr['vDeliveryGenieTitle_' . $default_lang]; ?>"
                                                   readonly="readonly" required>
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                    data-original-title="Edit" onclick="editDeliveryGenieTitle('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="DeliveryGenieTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                         data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="deliverygenie_title_modal_action"></span>
                                                        Title
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vDeliveryGenieTitle_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vDeliveryGenieTitle_' . $vCode;
                                                        $$vValue = $userEditDataArr[$vValue];
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryGenieTitle_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryGenieTitle_', '<?= $default_lang ?>');">
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
                                                                onclick="saveDeliveryGenieTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vDeliveryGenieTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                            <input type="text" class="form-control" id="vDeliveryGenieSubTitle_Default" name="vDeliveryGenieSubTitle_Default" value="<?= $userEditDataArr['vDeliveryGenieSubTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vDeliveryGenieSubTitle_' . $default_lang]; ?>"
                                                   readonly="readonly" required>
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDeliveryGenieSubTitle('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="DeliveryGenieSubTitle_Modal" tabindex="-1" role="dialog"
                                         aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="deliverygenie_subtitle_modal_action"></span>
                                                        Title
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryGenieSubTitle_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vDeliveryGenieSubTitle_' . $vCode;
                                                        $$vValue = $userEditDataArr[$vValue];
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryGenieSubTitle_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryGenieSubTitle_', '<?= $default_lang ?>');">
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
                                                                onclick="saveDeliveryGenieSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                                onclick="resetToOriginalValue(this, 'vDeliveryGenieSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                            <input type="text" class="form-control" id="vDeliveryGenieTitle_<?= $default_lang ?>" name="vDeliveryGenieTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vDeliveryGenieTitle_' . $default_lang]; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Subtitle</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input type="text" class="form-control" id="vDeliveryGenieSubTitle_<?= $default_lang ?>" name="vDeliveryGenieSubTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vDeliveryGenieSubTitle_' . $default_lang]; ?>">
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vTitleColorDeliveryGenie" class="form-control txt-color" value="<?= $vTitleColorDeliveryGenie ?>"/>
                                        <input type="hidden" name="vTitleColorDeliveryGenie" id="vTitleColorDeliveryGenie" value="<?= $vTitleColorDeliveryGenie ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vSubTitleColorDeliveryGenie" class="form-control txt-color" value="<?= $vSubTitleColorDeliveryGenie ?>"/>
                                        <input type="hidden" name="vSubTitleColorDeliveryGenie" id="vSubTitleColorDeliveryGenie" value="<?= $vSubTitleColorDeliveryGenie ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Background Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vBgColorDeliveryGenie" class="form-control bg-color" value="<?= $vBgColorDeliveryGenie ?>"/>
                                        <input type="hidden" name="vBgColorDeliveryGenie" id="vBgColorDeliveryGenie" value="<?= $vBgColorDeliveryGenie ?>">
                                    </div>
                                </div>

                                <div class="row pb-10">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4 marginbottom-10">
                                        <?php if(!empty($vImageOldDeliveryGenie)) { ?>
                                        <div class="marginbottom-10">
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images_vehicle_category'] . '/' . $DeliveryGenieId . '/android/' . $vImageOldDeliveryGenie; ?>" id="deliverygenie_img">
                                        </div>
                                        <?php } ?>
                                        <input type="file" class="form-control" name="vImageDeliveryGenie" id="vImageDeliveryGenie" onchange="previewImage(this, event);" data-img="deliverygenie_img">
                                        <input type="hidden" class="form-control" name="vImageOldDeliveryGenie" id="vImageOldDeliveryGenie" value="<?= $vImageOldDeliveryGenie ?>">
                                        <strong class="img-note">Note: Upload only png image size of 512px X 506px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <button type="button" class="btn btn-primary save-section-btn" id="saveDeliveryGenieSection">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tabcontent tabcontent-genierunner" id="manage-deliveryrunner-content">
                            <div class="col-lg-12">
                                <?php if (count($db_master) > 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Title</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input type="text" class="form-control" id="vDeliveryRunnerTitle_Default" name="vDeliveryRunnerTitle_Default" value="<?= $userEditDataArr['vDeliveryRunnerTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vDeliveryRunnerTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDeliveryRunnerTitle('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="DeliveryRunnerTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                         data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="deliveryrunner_title_modal_action"></span>
                                                        Title
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryRunnerTitle_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vDeliveryRunnerTitle_' . $vCode;
                                                        $$vValue = $userEditDataArr[$vValue];
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryRunnerTitle_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryRunnerTitle_', '<?= $default_lang ?>');">
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
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDeliveryRunnerTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryRunnerTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                            <input type="text" class="form-control" id="vDeliveryRunnerSubTitle_Default" name="vDeliveryRunnerSubTitle_Default" value="<?= $userEditDataArr['vDeliveryRunnerSubTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vDeliveryRunnerSubTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDeliveryRunnerSubTitle('Edit')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="DeliveryRunnerSubTitle_Modal" tabindex="-1" role="dialog"
                                         aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="deliveryrunner_subtitle_modal_action"></span>
                                                        Title
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryRunnerSubTitle_')">x
                                                        </button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vDeliveryRunnerSubTitle_' . $vCode;
                                                        $$vValue = $userEditDataArr[$vValue];
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (count($db_master) > 1) {
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
                                                            if (count($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryRunnerSubTitle_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage"
                                                                                    id="allLanguage" class="btn btn-primary"
                                                                                    onClick="getAllLanguageCode('vDeliveryRunnerSubTitle_', '<?= $default_lang ?>');">
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
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDeliveryRunnerSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryRunnerSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                            <input type="text" class="form-control" id="vDeliveryRunnerTitle_<?= $default_lang ?>" name="vDeliveryRunnerTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vDeliveryRunnerTitle_' . $default_lang]; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Subtitle</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input type="text" class="form-control" id="vDeliveryRunnerSubTitle_<?= $default_lang ?>" name="vDeliveryRunnerSubTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vDeliveryRunnerSubTitle_' . $default_lang]; ?>">
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vTitleColorDeliveryRunner" class="form-control txt-color" value="<?= $vTitleColorDeliveryRunner ?>"/>
                                        <input type="hidden" name="vTitleColorDeliveryRunner" id="vTitleColorDeliveryRunner" value="<?= $vTitleColorDeliveryRunner ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vSubTitleColorDeliveryRunner" class="form-control txt-color" value="<?= $vSubTitleColorDeliveryRunner ?>"/>
                                        <input type="hidden" name="vSubTitleColorDeliveryRunner" id="vSubTitleColorDeliveryRunner" value="<?= $vSubTitleColorDeliveryRunner ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Background Color</label>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <input type="color" data-id="vBgColorDeliveryRunner" class="form-control bg-color" value="<?= $vBgColorDeliveryRunner ?>"/>
                                        <input type="hidden" name="vBgColorDeliveryRunner" id="vBgColorDeliveryRunner" value="<?= $vBgColorDeliveryRunner ?>">
                                    </div>
                                </div>

                                <div class="row pb-10">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4 marginbottom-10">
                                        <?php if(!empty($vImageOldDeliveryRunner)) { ?>
                                        <div class="marginbottom-10">
                                            <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images_vehicle_category'] . '/' . $DeliveryRunnerId . '/android/' . $vImageOldDeliveryRunner; ?>" id="deliveryrunner_img">
                                        </div>
                                        <?php } ?>
                                        <input type="file" class="form-control" name="vImageDeliveryRunner" id="vImageDeliveryRunner" onchange="previewImage(this, event);" data-img="deliveryrunner_img">
                                        <input type="hidden" class="form-control" name="vImageOldDeliveryRunner" id="vImageOldDeliveryRunner" value="<?= $vImageOldDeliveryRunner ?>">
                                        <strong class="img-note">Note: Upload only png image size of 512px X 448px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <button type="button" class="btn btn-primary save-section-btn" id="saveDeliveryRunnerSection">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if($MODULES_OBJ->isDeliverAllFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Pharmacy Delivery - Nearby Stores</div>
                        <div class="underline-section-title"></div>
                        <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vPharmacyNearbyTitle_Default" name="vPharmacyNearbyTitle_Default" value="<?= $userEditDataArr['vPharmacyNearbyTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vPharmacyNearbyTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editPharmacyNearbyTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="PharmacyNearbyTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="pharmacynearby_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vPharmacyNearbyTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vPharmacyNearbyTitle_' . $vCode;
                                                $$vValue = $userEditDataArr[$vValue];
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (count($db_master) > 1) {
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
                                                    if (count($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vPharmacyNearbyTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vPharmacyNearbyTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="savePharmacyNearbyTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vPharmacyNearbyTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vPharmacyNearbyTitle_<?= $default_lang ?>"
                                           name="vPharmacyNearbyTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vPharmacyNearbyTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="savePharmacyNearbyTitleSection">Save</button>
                                <strong class="img-note">Note: Pharmacy Delivery Service must be active to see changes in App Home Screen. <br>This section will display nearby pharmacy stores based on the user's current location.</strong>
                            </div>
                        </div>
                    <?php } ?>
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

        var vTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>').val();
        var vSubTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vSubTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>').val();
        var vBgColor<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vBgColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>').val();
        var vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>')[0].files[0];
        var vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?> = $('#vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>').val();

        postData.append('vTxtTitleColor', vTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('vTxtSubTitleColor', vSubTitleColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('vBgColor', vBgColor<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('vImage', vImage<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('vImageOld', vImageOld<?= $OTHER_SERVICE['ManageServiceKey'] ?>);
        postData.append('ViewType', 'CardIconTextView');
        postData.append('ServiceType', 'Other');
        postData.append('ServiceTypeOther', '<?= $OTHER_SERVICE['ManageServiceKey'] ?>');

        saveHomeScreenData('save<?= $OTHER_SERVICE['ManageServiceKey'] ?>Section', postData);
    });
    <?php } ?>

    function editRestaurantsNearbyTitle(action) {
        $('#restaurantsnearby_title_modal_action').html(action);
        $('#RestaurantsNearbyTitle_Modal').modal('show');
    }
    function saveRestaurantsNearbyTitle() {
        if ($('#vRestaurantsNearbyTitle_<?= $default_lang ?>').val() == "") {
            $('#vRestaurantsNearbyTitle_<?= $default_lang ?>_error').show();
            $('#vRestaurantsNearbyTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRestaurantsNearbyTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vRestaurantsNearbyTitle_Default').val($('#vRestaurantsNearbyTitle_<?= $default_lang ?>').val());
        $('#vRestaurantsNearbyTitle_Default').closest('.row').removeClass('has-error');
        $('#vRestaurantsNearbyTitle_Default-error').remove();
        $('#RestaurantsNearbyTitle_Modal').modal('hide');
    }
    $('#saveRestaurantsNearbyTitleSection').click(function() {
        var vRestaurantsNearbyTitleArr = $('[name^="vRestaurantsNearbyTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRestaurantsNearbyTitleArr, function(key, value) {
            if(value.name != "vRestaurantsNearbyTitle_Default") {
                var name_key = value.name.replace('vRestaurantsNearbyTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'DeliverAllNearby';
        postData['ServiceId'] = '1';
        saveHomeScreenData('saveRestaurantsNearbyTitleSection', postData, 'No');
    });

    function editRestaurantsTopRatedTitle(action) {
        $('#restaurantstoprated_title_modal_action').html(action);
        $('#RestaurantsTopRatedTitle_Modal').modal('show');
    }
    function saveRestaurantsTopRatedTitle() {
        if ($('#vRestaurantsTopRatedTitle_<?= $default_lang ?>').val() == "") {
            $('#vRestaurantsTopRatedTitle_<?= $default_lang ?>_error').show();
            $('#vRestaurantsTopRatedTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRestaurantsTopRatedTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vRestaurantsTopRatedTitle_Default').val($('#vRestaurantsTopRatedTitle_<?= $default_lang ?>').val());
        $('#vRestaurantsTopRatedTitle_Default').closest('.row').removeClass('has-error');
        $('#vRestaurantsTopRatedTitle_Default-error').remove();
        $('#RestaurantsTopRatedTitle_Modal').modal('hide');
    }
    $('#saveRestaurantsTopRatedTitleSection').click(function() {
        var vRestaurantsTopRatedTitleArr = $('[name^="vRestaurantsTopRatedTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRestaurantsTopRatedTitleArr, function(key, value) {
            if(value.name != "vRestaurantsTopRatedTitle_Default") {
                var name_key = value.name.replace('vRestaurantsTopRatedTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'DeliverAllTopRated';
        postData['ServiceId'] = '1';
        saveHomeScreenData('saveRestaurantsTopRatedTitleSection', postData, 'No');
    });

    function editRestaurantItemsTitle(action) {
        $('#restaurantitems_title_modal_action').html(action);
        $('#RestaurantItemsTitle_Modal').modal('show');
    }
    function saveRestaurantItemsTitle() {
        if ($('#vRestaurantItemsTitle_<?= $default_lang ?>').val() == "") {
            $('#vRestaurantItemsTitle_<?= $default_lang ?>_error').show();
            $('#vRestaurantItemsTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRestaurantItemsTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vRestaurantItemsTitle_Default').val($('#vRestaurantItemsTitle_<?= $default_lang ?>').val());
        $('#vRestaurantItemsTitle_Default').closest('.row').removeClass('has-error');
        $('#vRestaurantItemsTitle_Default-error').remove();
        $('#RestaurantItemsTitle_Modal').modal('hide');
    }
    $('#saveRestaurantItemsTitleSection').click(function() {
        var vRestaurantItemsTitleArr = $('[name^="vRestaurantItemsTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRestaurantItemsTitleArr, function(key, value) {
            if(value.name != "vRestaurantItemsTitle_Default") {
                var name_key = value.name.replace('vRestaurantItemsTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'DeliverAllItems';
        postData['ServiceId'] = '1';
        saveHomeScreenData('saveRestaurantItemsTitleSection', postData, 'No');
    });

    function editGroceryNearbyTitle(action) {
        $('#grocerynearby_title_modal_action').html(action);
        $('#GroceryNearbyTitle_Modal').modal('show');
    }
    function saveGroceryNearbyTitle() {
        if ($('#vGroceryNearbyTitle_<?= $default_lang ?>').val() == "") {
            $('#vGroceryNearbyTitle_<?= $default_lang ?>_error').show();
            $('#vGroceryNearbyTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vGroceryNearbyTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vGroceryNearbyTitle_Default').val($('#vGroceryNearbyTitle_<?= $default_lang ?>').val());
        $('#vGroceryNearbyTitle_Default').closest('.row').removeClass('has-error');
        $('#vGroceryNearbyTitle_Default-error').remove();
        $('#GroceryNearbyTitle_Modal').modal('hide');
    }
    $('#saveGroceryNearbyTitleSection').click(function() {
        var vGroceryNearbyTitleArr = $('[name^="vGroceryNearbyTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vGroceryNearbyTitleArr, function(key, value) {
            if(value.name != "vGroceryNearbyTitle_Default") {
                var name_key = value.name.replace('vGroceryNearbyTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'DeliverAllNearby';
        postData['ServiceId'] = '2';
        saveHomeScreenData('saveGroceryNearbyTitleSection', postData, 'No');
    });

    function editPharmacyNearbyTitle(action) {
        $('#pharmacynearby_title_modal_action').html(action);
        $('#PharmacyNearbyTitle_Modal').modal('show');
    }
    function savePharmacyNearbyTitle() {
        if ($('#vPharmacyNearbyTitle_<?= $default_lang ?>').val() == "") {
            $('#vPharmacyNearbyTitle_<?= $default_lang ?>_error').show();
            $('#vPharmacyNearbyTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vPharmacyNearbyTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vPharmacyNearbyTitle_Default').val($('#vPharmacyNearbyTitle_<?= $default_lang ?>').val());
        $('#vPharmacyNearbyTitle_Default').closest('.row').removeClass('has-error');
        $('#vPharmacyNearbyTitle_Default-error').remove();
        $('#PharmacyNearbyTitle_Modal').modal('hide');
    }
    $('#savePharmacyNearbyTitleSection').click(function() {
        var vPharmacyNearbyTitleArr = $('[name^="vPharmacyNearbyTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vPharmacyNearbyTitleArr, function(key, value) {
            if(value.name != "vPharmacyNearbyTitle_Default") {
                var name_key = value.name.replace('vPharmacyNearbyTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'DeliverAllNearby';
        postData['ServiceId'] = '5';
        saveHomeScreenData('savePharmacyNearbyTitleSection', postData, 'No');
    });

    function saveDeliverAllServices(eStatus) {
        $('#saveDeliverAllServiceDisplay').val(eStatus);
        $('#deliverallservices_modal').modal('hide');
    }

    $('#saveDeliverAllServiceSection').click(function() {
        var postData = new FormData();

        var saveDeliverAllServiceDisplay = $('#saveDeliverAllServiceDisplay').val();

        $('[name="vDeliverAllImage[]"]').each(function(i) {
            postData.append('vImage['+i+']', $(this)[0].files[0]);
        });

        $('[name="vDeliverAllImageOld[]"]').each(function(i) {
            postData.append('vImageOld['+i+']', $(this).val());
        });

        $('[name="iServiceIdVal[]"]').each(function(i) {
            postData.append('iServiceIdVal['+i+']', $(this).val());
        });

        postData.append('ViewType', 'GridView');
        postData.append('saveDeliverAllServiceDisplay', saveDeliverAllServiceDisplay);
        postData.append('ServiceType', 'DeliverAll');
        saveHomeScreenData('saveDeliverAllServiceSection', postData);

    });


    function editGeneralBannerTitle(action) {
        $('#generalbanner_title_modal_action').html(action);
        $('#GeneralBannerTitle_Modal').modal('show');
    }
    function saveGeneralBannerTitle() {
        if ($('#vGeneralBannerTitle_<?= $default_lang ?>').val() == "") {
            $('#vGeneralBannerTitle_<?= $default_lang ?>_error').show();
            $('#vGeneralBannerTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vGeneralBannerTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vGeneralBannerTitle_Default').val($('#vGeneralBannerTitle_<?= $default_lang ?>').val());
        $('#vGeneralBannerTitle_Default').closest('.row').removeClass('has-error');
        $('#vGeneralBannerTitle_Default-error').remove();
        $('#GeneralBannerTitle_Modal').modal('hide');
    }
    $('#saveGeneralBannerTitleSection').click(function() {
        var vGeneralBannerTitleArr = $('[name^="vGeneralBannerTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vGeneralBannerTitleArr, function(key, value) {
            if(value.name != "vGeneralBannerTitle_Default") {
                var name_key = value.name.replace('vGeneralBannerTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'GeneralBanner';
        saveHomeScreenData('saveGeneralBannerTitleSection', postData, 'No');
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

    });

    function saveRideServices(eStatus) {
        $('#saveRideServiceDisplay').val(eStatus);
        $('#rideservices_modal').modal('hide');
    }

    $('[name="iVehicleCategoryId[]"]').change(function (e) {
        if ($(this).is(':checked')) {
            $(this).closest('tr').find('select, input[type="file"]').show();
        } else {
            $(this).closest('tr').find('select, input[type="file"]').hide();
            $(this).closest('tr').find('select').val('1');
            $(this).closest('tr').find('input[type="file"]').val('').bootstrapSwitch();
        }
    });

    function editDeliverTitle(action) {
        $('#deliver_title_modal_action').html(action);
        $('#DeliverTitle_Modal').modal('show');
    }

    function saveDeliverTitle() {
        if ($('#vDeliverTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliverTitle_<?= $default_lang ?>_error').show();
            $('#vDeliverTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliverTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliverTitle_Default').val($('#vDeliverTitle_<?= $default_lang ?>').val());
        $('#vDeliverTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliverTitle_Default-error').remove();
        $('#DeliverTitle_Modal').modal('hide');
    }

    $('#saveDeliverTitleSection').click(function() {
        var vDeliverTitleArr = $('[name^="vDeliverTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vDeliverTitleArr, function(key, value) {
            if(value.name != "vDeliverTitle_Default") {
                var name_key = value.name.replace('vDeliverTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Deliver';
        saveHomeScreenData('saveDeliverTitleSection', postData, 'No');
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
        var postData = new FormData();
        $.each(vSingleDeliveryTitleArr, function(key, value) {
            if(value.name != "vSingleDeliveryTitle_Default") {
                var name_key = value.name.replace('vSingleDeliveryTitle', 'vCategory');
                postData.append('vTitleArr['+name_key+']', value.value);
            }
        });

        var vSingleDeliverySubTitleArr = $('[name^="vSingleDeliverySubTitle_"]').serializeArray();
        $.each(vSingleDeliverySubTitleArr, function(key, value) {
            if(value.name != "vSingleDeliverySubTitle_Default") {
                var name_key = value.name.replace('vSingleDeliverySubTitle', 'tCategoryDesc');
                postData.append('vSubTitleArr['+name_key+']', value.value);
            }
        });
        var vImageSingleDelivery = $('#vImageSingleDelivery')[0].files[0];
        var vImageOldSingleDelivery = $('#vImageOldSingleDelivery').val();
        var vTitleColorSingleDelivery = $('#vTitleColorSingleDelivery').val();
        var vSubTitleColorSingleDelivery = $('#vSubTitleColorSingleDelivery').val();
        var vBgColorSingleDelivery = $('#vBgColorSingleDelivery').val();

        postData.append('vImage', vImageSingleDelivery);
        postData.append('vImageOld', vImageOldSingleDelivery);
        postData.append('vTxtTitleColor', vTitleColorSingleDelivery);
        postData.append('vTxtSubTitleColor', vSubTitleColorSingleDelivery);
        postData.append('vBgColor', vBgColorSingleDelivery);
        postData.append('ViewType', 'IconTextView');
        postData.append('ServiceType', 'Deliver');
        postData.append('iVehicleCategoryId', '186');

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
        var postData = new FormData();
        $.each(vMultiDeliveryTitleArr, function(key, value) {
            if(value.name != "vMultiDeliveryTitle_Default") {
                var name_key = value.name.replace('vMultiDeliveryTitle', 'vCategory');
                postData.append('vTitleArr['+name_key+']', value.value);
            }
        });

        var vMultiDeliverySubTitleArr = $('[name^="vMultiDeliverySubTitle_"]').serializeArray();
        $.each(vMultiDeliverySubTitleArr, function(key, value) {
            if(value.name != "vMultiDeliverySubTitle_Default") {
                var name_key = value.name.replace('vMultiDeliverySubTitle', 'tCategoryDesc');
                postData.append('vSubTitleArr['+name_key+']', value.value);
            }
        });
        var vImageMultiDelivery = $('#vImageMultiDelivery')[0].files[0];
        var vImageOldMultiDelivery = $('#vImageOldMultiDelivery').val();
        var vTitleColorMultiDelivery = $('#vTitleColorMultiDelivery').val();
        var vSubTitleColorMultiDelivery = $('#vSubTitleColorMultiDelivery').val();
        var vBgColorMultiDelivery = $('#vBgColorMultiDelivery').val();

        postData.append('vImage', vImageMultiDelivery);
        postData.append('vImageOld', vImageOldMultiDelivery);
        postData.append('vTxtTitleColor', vTitleColorMultiDelivery);
        postData.append('vTxtSubTitleColor', vSubTitleColorMultiDelivery);
        postData.append('vBgColor', vBgColorMultiDelivery);
        postData.append('ViewType', 'IconTextView');
        postData.append('ServiceType', 'Deliver');
        postData.append('iVehicleCategoryId', '181');

        saveHomeScreenData('saveMultiDeliverySection', postData);
    });

    function editDeliveryGenieTitle(action) {
        $('#deliverygenie_title_modal_action').html(action);
        $('#DeliveryGenieTitle_Modal').modal('show');
    }

    function saveDeliveryGenieTitle() {
        if ($('#vDeliveryGenieTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliveryGenieTitle_<?= $default_lang ?>_error').show();
            $('#vDeliveryGenieTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliveryGenieTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliveryGenieTitle_Default').val($('#vDeliveryGenieTitle_<?= $default_lang ?>').val());
        $('#vDeliveryGenieTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliveryGenieTitle_Default-error').remove();
        $('#DeliveryGenieTitle_Modal').modal('hide');
    }

    function editDeliveryGenieSubTitle(action) {
        $('#deliverygenie_subtitle_modal_action').html(action);
        $('#DeliveryGenieSubTitle_Modal').modal('show');
    }

    function saveDeliveryGenieSubTitle() {
        if ($('#vDeliveryGenieSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliveryGenieSubTitle_<?= $default_lang ?>_error').show();
            $('#vDeliveryGenieSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliveryGenieSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliveryGenieSubTitle_Default').val($('#vDeliveryGenieSubTitle_<?= $default_lang ?>').val());
        $('#vDeliveryGenieSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliveryGenieSubTitle_Default-error').remove();
        $('#DeliveryGenieSubTitle_Modal').modal('hide');
    }

    $('#saveDeliveryGenieSection').click(function() {
        var vDeliveryGenieTitleArr = $('[name^="vDeliveryGenieTitle_"]').serializeArray();
        var postData = new FormData();
        $.each(vDeliveryGenieTitleArr, function(key, value) {
            if(value.name != "vDeliveryGenieTitle_Default") {
                var name_key = value.name.replace('vDeliveryGenieTitle', 'vCategory');
                postData.append('vTitleArr['+name_key+']', value.value);
            }
        });

        var vDeliveryGenieSubTitleArr = $('[name^="vDeliveryGenieSubTitle_"]').serializeArray();
        $.each(vDeliveryGenieSubTitleArr, function(key, value) {
            if(value.name != "vDeliveryGenieSubTitle_Default") {
                var name_key = value.name.replace('vDeliveryGenieSubTitle', 'tCategoryDesc');
                postData.append('vSubTitleArr['+name_key+']', value.value);
            }
        });
        var vImageDeliveryGenie = $('#vImageDeliveryGenie')[0].files[0];
        var vImageOldDeliveryGenie = $('#vImageOldDeliveryGenie').val();
        var vTitleColorDeliveryGenie = $('#vTitleColorDeliveryGenie').val();
        var vSubTitleColorDeliveryGenie = $('#vSubTitleColorDeliveryGenie').val();
        var vBgColorDeliveryGenie = $('#vBgColorDeliveryGenie').val();

        postData.append('vImage', vImageDeliveryGenie);
        postData.append('vImageOld', vImageOldDeliveryGenie);
        postData.append('vTxtTitleColor', vTitleColorDeliveryGenie);
        postData.append('vTxtSubTitleColor', vSubTitleColorDeliveryGenie);
        postData.append('vBgColor', vBgColorDeliveryGenie);
        postData.append('ViewType', 'IconTextView');
        postData.append('ServiceType', 'BuyAnyService');
        postData.append('iVehicleCategoryId', '280');

        saveHomeScreenData('saveDeliveryGenieSection', postData);
    });

    function editDeliveryRunnerTitle(action) {
        $('#deliveryrunner_title_modal_action').html(action);
        $('#DeliveryRunnerTitle_Modal').modal('show');
    }

    function saveDeliveryRunnerTitle() {
        if ($('#vDeliveryRunnerTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliveryRunnerTitle_<?= $default_lang ?>_error').show();
            $('#vDeliveryRunnerTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliveryRunnerTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliveryRunnerTitle_Default').val($('#vDeliveryRunnerTitle_<?= $default_lang ?>').val());
        $('#vDeliveryRunnerTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliveryRunnerTitle_Default-error').remove();
        $('#DeliveryRunnerTitle_Modal').modal('hide');
    }

    function editDeliveryRunnerSubTitle(action) {
        $('#deliveryrunner_subtitle_modal_action').html(action);
        $('#DeliveryRunnerSubTitle_Modal').modal('show');
    }

    function saveDeliveryRunnerSubTitle() {
        if ($('#vDeliveryRunnerSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliveryRunnerSubTitle_<?= $default_lang ?>_error').show();
            $('#vDeliveryRunnerSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliveryRunnerSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliveryRunnerSubTitle_Default').val($('#vDeliveryRunnerSubTitle_<?= $default_lang ?>').val());
        $('#vDeliveryRunnerSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliveryRunnerSubTitle_Default-error').remove();
        $('#DeliveryRunnerSubTitle_Modal').modal('hide');
    }

    $('#saveDeliveryRunnerSection').click(function() {
        var vDeliveryRunnerTitleArr = $('[name^="vDeliveryRunnerTitle_"]').serializeArray();
          var postData = new FormData();
        $.each(vDeliveryRunnerTitleArr, function(key, value) {
            if(value.name != "vDeliveryRunnerTitle_Default") {
                var name_key = value.name.replace('vDeliveryRunnerTitle', 'vCategory');
                postData.append('vTitleArr['+name_key+']', value.value);
            }
        });

        var vDeliveryRunnerSubTitleArr = $('[name^="vDeliveryRunnerSubTitle_"]').serializeArray();
        $.each(vDeliveryRunnerSubTitleArr, function(key, value) {
            if(value.name != "vDeliveryRunnerSubTitle_Default") {
                var name_key = value.name.replace('vDeliveryRunnerSubTitle', 'tCategoryDesc');
                postData.append('vSubTitleArr['+name_key+']', value.value);
            }
        });
        var vImageDeliveryRunner = $('#vImageDeliveryRunner')[0].files[0];
        var vImageOldDeliveryRunner = $('#vImageOldDeliveryRunner').val();
        var vTitleColorDeliveryRunner = $('#vTitleColorDeliveryRunner').val();
        var vSubTitleColorDeliveryRunner = $('#vSubTitleColorDeliveryRunner').val();
        var vBgColorDeliveryRunner = $('#vBgColorDeliveryRunner').val();

        postData.append('vImage', vImageDeliveryRunner);
        postData.append('vImageOld', vImageOldDeliveryRunner);
        postData.append('vTxtTitleColor', vTitleColorDeliveryRunner);
        postData.append('vTxtSubTitleColor', vSubTitleColorDeliveryRunner);
        postData.append('vBgColor', vBgColorDeliveryRunner);
        postData.append('ViewType', 'IconTextView');
        postData.append('ServiceType', 'BuyAnyService');
        postData.append('iVehicleCategoryId', '287');

        saveHomeScreenData('saveDeliveryRunnerSection', postData);
    });

    function saveDisplayOrderService(ServiceType) {
        var iDisplayOrderArr = {};

        var DisplayOrderElem = $('[name^="iDisplayOrderAllServiceArr"]');

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