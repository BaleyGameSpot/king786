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

$master_service_categories = $obj->MySQLSelect("SELECT vCategoryName, vCategoryDesc, JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vMasterCategoryName, eType, iMasterServiceCategoryId, vIconImage1, tCategoryDetails, vTextColor, vBgColor FROM $master_service_category_tbl WHERE eStatus = 'Active'");
$MasterCategoryArr = array();
foreach ($master_service_categories as $mCategory) {
    $MasterCategoryArr[$mCategory['eType']] = $mCategory;
}

$userEditDataArr = $db_data_arr = array();
$db_data = $obj->MySQLSelect("SELECT * FROM $tbl_name");
foreach ($db_data as $db_value) {
    $ViewType = !empty($db_value['eServiceType']) ? $db_value['eServiceType'] : $db_value['eViewType'];
    $db_data_arr[$ViewType] = $db_value;
}

/* $morelabel = $obj->MySQLSelect("SELECT LanguageLabelId, vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_MORE_SERVICES', 'LBL_MORE_ONDEMAND_SERVICES', 'LBL_MORE') AND vCode = '$default_lang'"); */

//print_r($MEDICAL_SERVICES_ARR); die;

/* General Banners */
$bannerData = $obj->MySQLSelect("SELECT * FROM banners WHERE iServiceId = 0 AND vCode = '$default_lang' AND eType = 'General' AND eFor = 'General' AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 0,3");

/* Search Bar */
$vSearchBarTitleArr = json_decode($db_data_arr['SearchBar']['vTitle'], true);
foreach ($vSearchBarTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vSearchBarTitle_', $key);
    $userEditDataArr[$key] = $value;
}

/* Taxi Booking | Taxi Bid */
if ($MODULES_OBJ->isRideFeatureAvailable()) {
    $vTaxiServiceTitleArr = json_decode($db_data_arr['Ride']['vTitle'], true);
    foreach ($vTaxiServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vTaxiServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $taxiSql = "";
    if($MODULES_OBJ->isEnableTaxiBidFeature()) {
        $taxiSql .= " OR eCatType IN ('TaxiBid', 'MotoBid') ";
    }
    if($MODULES_OBJ->isInterCityFeatureAvailable()) {
        $taxiSql .= " OR eCatType IN ('InterCity') ";
    }
    $taxiData = $obj->MySQLSelect("SELECT iVehicleCategoryId, vCategory_$default_lang as vCategoryName, vListLogo3, iDisplayOrder,eStatus FROM " . $sql_vehicle_category_table_name . " WHERE (eCatType IN ('Ride', 'MotoRide', 'Rental', 'MotoRental', 'RidePool', 'RideSchedule', 'CorporateRide', 'RideSomeoneElse') $taxiSql) AND eForMedicalService = 'No' AND iParentId = '0' AND eStatus != 'Deleted' ORDER BY iDisplayOrder");
}

/*************** Old ****************/
/* Parcel Delivery | Delivery Genie, Runner | Store Delivery */
/*if ($MODULES_OBJ->isDeliveryFeatureAvailable() || $MODULES_OBJ->isEnableGenieFeature() || $MODULES_OBJ->isEnableRunnerFeature() || $MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    $vDeliveryServiceTitleArr = json_decode($db_data_arr['DeliveryServices']['vTitle'], true);
    foreach ($vDeliveryServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vDeliveryServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $tServiceDetailsDelivery = json_decode($db_data_arr['DeliveryServices']['tServiceDetails'], true);

    $DeliveryServicesData = $obj->MySQLSelect("SELECT iVehicleCategoryId, vCategory_$default_lang as vCategoryName, eCatType, vListLogo3, iDisplayOrder,eStatus FROM $sql_vehicle_category_table_name WHERE iParentId = 0 AND (eCatType IN ('MoreDelivery', 'Genie', 'Runner') OR (eCatType = 'DeliverAll' AND iServiceId > 0)) AND eStatus != 'Deleted' ");

    $DeliveryServicesArr = array();
    foreach ($DeliveryServicesData as $DeliveryService) {
        if(($DeliveryService['eCatType'] == "MoreDelivery" && $MODULES_OBJ->isDeliveryFeatureAvailable())
            || ($DeliveryService['eCatType'] == "Genie" && $MODULES_OBJ->isEnableGenieFeature())
            || ($DeliveryService['eCatType'] == "Runner" && $MODULES_OBJ->isEnableRunnerFeature())
            || ($DeliveryService['eCatType'] == "DeliverAll" && $MODULES_OBJ->isDeliverAllFeatureAvailable())
        ) {
            $DeliveryServicesArr[] = $DeliveryService;
        }
    }
}*/
/*************** Old ****************/


/* ParcelDelivery */
if ($MODULES_OBJ->isDeliveryFeatureAvailable()) {
    $vDeliverTitleArr = json_decode($db_data_arr['Deliver']['vTitle'], true);
    foreach ($vDeliverTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vDeliverTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vDeliverSubTitleArr = json_decode($db_data_arr['Deliver']['vSubtitle'], true);
    foreach ($vDeliverSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vDeliverSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldDeliver = $db_data_arr['Deliver']['vImage'];

    $DeliverLayoutDetails = json_decode($db_data_arr['Deliver']['tLayoutDetails'], true);
    $vTitleColorDeliver = $DeliverLayoutDetails['vTxtTitleColor']; 
    $vSubTitleColorDeliver = $DeliverLayoutDetails['vTxtSubTitleColor']; 
    $vBgColorDeliver = $DeliverLayoutDetails['vBgColor']; 
}

/* Store Delivery Services */
if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    $vDeliveryServiceTitleArr = json_decode($db_data_arr['DeliverAll']['vTitle'], true);
    foreach ($vDeliveryServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vDeliveryServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $tServiceDetailsDelivery = json_decode($db_data_arr['DeliverAll']['tServiceDetails'], true);

    $DeliveryServicesData = $obj->MySQLSelect("SELECT iVehicleCategoryId, vCategory_$default_lang as vCategoryName, eCatType, vListLogo3, iDisplayOrder,eStatus FROM $sql_vehicle_category_table_name WHERE iParentId = 0 AND eCatType = 'DeliverAll' AND iServiceId > 0 AND eStatus != 'Deleted' AND iServiceId IN ($enablesevicescategory) ORDER BY iDisplayOrder ");

    $DeliveryServicesArr = array();
    foreach ($DeliveryServicesData as $DeliveryService) {
        $DeliveryServicesArr[] = $DeliveryService;
    }
}

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
}

/* Video Consult */
if ($MODULES_OBJ->isEnableVideoConsultingService()) {
    $vVideoConsultTitleArr = json_decode($db_data_arr['VideoConsult']['vTitle'], true);
    foreach ($vVideoConsultTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vVideoConsultTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vVideoConsultSubTitleArr = json_decode($db_data_arr['VideoConsult']['vSubtitle'], true);
    foreach ($vVideoConsultSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vVideoConsultSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldVideoConsult = $db_data_arr['VideoConsult']['vImage'];

    $VideoConsultLayoutDetails = json_decode($db_data_arr['VideoConsult']['tLayoutDetails'], true);
    $vTitleColorVideoConsult = $VideoConsultLayoutDetails['vTxtTitleColor']; 
    $vSubTitleColorVideoConsult = $VideoConsultLayoutDetails['vTxtSubTitleColor']; 
    $vBgColorVideoConsult = $VideoConsultLayoutDetails['vBgColor'];
}

$ufxSql = "";
if($MODULES_OBJ->isEnableMedicalServices('Yes')) {
    $ufxSql = " AND vc1.iVehicleCategoryId NOT IN (3,22,26,158) ";
}
$ufxData = $obj->MySQLSelect("SELECT vc1.iVehicleCategoryId,vc1.eStatus , vc1.vCategory_$default_lang as vCategoryName, vc1.vListLogo3, vc1.vIconDetails, vc1.iDisplayOrder, vc1.iDisplayOrderVC, (SELECT COUNT(vc2.iVehicleCategoryId) as count FROM $sql_vehicle_category_table_name as vc2 WHERE vc2.iParentId = vc1.iVehicleCategoryId AND vc2.eVideoConsultEnable = 'Yes') as eVideoConsultEnableCount FROM $sql_vehicle_category_table_name as vc1 WHERE vc1.eCatType = 'ServiceProvider' AND vc1.eVideoConsultEnable = 'No' AND vc1.iParentId = '0' AND eStatus != 'Deleted' $ufxSql ORDER BY vc1.iDisplayOrder");

$ufxDataVC = array();
foreach ($ufxData as $ufxService) {
    if($ufxService['eVideoConsultEnableCount'] > 0) {
        $ufxDataVC[] = $ufxService;
    }
}

if(!empty($ufxDataVC)) {
    foreach ($ufxDataVC as $key => $value) {
        $sort_data[$key] = $value['iDisplayOrderVC'];
    }
    array_multisort($sort_data, SORT_ASC, $ufxDataVC);
}

/* Buy, Sell & Rent */
$vBSRTitleArr = json_decode($db_data_arr['BuySellRent']['vTitle'], true);
foreach ($vBSRTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vBSRTitle_', $key);
    $userEditDataArr[$key] = $value;
}

if ($MODULES_OBJ->isEnableRentEstateService()) {
    $vRentEstateTitleArr = json_decode($MasterCategoryArr['RentEstate']['vCategoryName'], true);
    foreach ($vRentEstateTitleArr as $key => $value) {
        $key = str_replace('vCategoryName_', 'vRentEstateTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vRentEstateSubTitleArr = json_decode($MasterCategoryArr['RentEstate']['vCategoryDesc'], true);
    foreach ($vRentEstateSubTitleArr as $key => $value) {
        $key = str_replace('vCategoryDesc_', 'vRentEstateSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldRentEstate = $MasterCategoryArr['RentEstate']['vIconImage1'];

    $vTitleColorRentEstate = $MasterCategoryArr['RentEstate']['vTextColor']; 
    $vBgColorRentEstate = $MasterCategoryArr['RentEstate']['vBgColor']; 
}

if ($MODULES_OBJ->isEnableRentCarsService()) {
    $vRentCarsTitleArr = json_decode($MasterCategoryArr['RentCars']['vCategoryName'], true);
    foreach ($vRentCarsTitleArr as $key => $value) {
        $key = str_replace('vCategoryName_', 'vRentCarsTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vRentCarsSubTitleArr = json_decode($MasterCategoryArr['RentCars']['vCategoryDesc'], true);
    foreach ($vRentCarsSubTitleArr as $key => $value) {
        $key = str_replace('vCategoryDesc_', 'vRentCarsSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldRentCars = $MasterCategoryArr['RentCars']['vIconImage1'];

    $vTitleColorRentCars = $MasterCategoryArr['RentCars']['vTextColor']; 
    $vBgColorRentCars = $MasterCategoryArr['RentCars']['vBgColor']; 
}

if ($MODULES_OBJ->isEnableRentItemService()) {
    $vRentItemTitleArr = json_decode($MasterCategoryArr['RentItem']['vCategoryName'], true);
    foreach ($vRentItemTitleArr as $key => $value) {
        $key = str_replace('vCategoryName_', 'vRentItemTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vRentItemSubTitleArr = json_decode($MasterCategoryArr['RentItem']['vCategoryDesc'], true);
    foreach ($vRentItemSubTitleArr as $key => $value) {
        $key = str_replace('vCategoryDesc_', 'vRentItemSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldRentItem = $MasterCategoryArr['RentItem']['vIconImage1'];

    $vTitleColorRentItem = $MasterCategoryArr['RentItem']['vTextColor']; 
    $vBgColorRentItem = $MasterCategoryArr['RentItem']['vBgColor'];
}



/* Service Bid */
if ($MODULES_OBJ->isEnableBiddingServices()) {
    $vBiddingTitleArr = json_decode($db_data_arr['Bidding']['vTitle'], true);
    foreach ($vBiddingTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vBiddingTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vBiddingSubTitleArr = json_decode($db_data_arr['Bidding']['vSubtitle'], true);
    foreach ($vBiddingSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vBiddingSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldBidding = $db_data_arr['Bidding']['vImage'];

    $BiddingLayoutDetails = json_decode($db_data_arr['Bidding']['tLayoutDetails'], true);
    $vTitleColorBidding = $BiddingLayoutDetails['vTxtTitleColor']; 
    $vSubTitleColorBidding = $BiddingLayoutDetails['vTxtSubTitleColor']; 
    $vBgColorBidding = $BiddingLayoutDetails['vBgColor'];

    $ServiceBidData = $BIDDING_OBJ->getBiddingMaster('admin', '', 0, 0, $default_lang);
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


/* Medical Services */
if ($MODULES_OBJ->isEnableMedicalServices()) {
    $vMSTitleArr = json_decode($db_data_arr['MedicalServices']['vTitle'], true);
    foreach ($vMSTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vMSTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $labelsMS = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_ON_DEMAND_MEDICAL_SERVICES_TITLE', 'LBL_ON_DEMAND_MEDICAL_SERVICES_DESC', 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_TITLE', 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_DESC', 'LBL_MEDICAL_MORE_SERVICES_TITLE', 'LBL_MEDICAL_MORE_SERVICES_DESC') ");

    $BookServiceMSTitleArr = $BookServiceMSSubTitleArr = $VideoConsultMSTitleArr = $VideoConsultMSSubTitleArr = $MoreServiceMSTitleArr = $MoreServiceMSSubTitleArr = array();
    foreach ($labelsMS as $label) {
        if($label['vLabel'] == 'LBL_ON_DEMAND_MEDICAL_SERVICES_TITLE') {
            $BookServiceMSTitleArr[$label['vCode']] = $label['vValue'];

        } elseif ($label['vLabel'] == 'LBL_ON_DEMAND_MEDICAL_SERVICES_DESC') {
            $BookServiceMSSubTitleArr[$label['vCode']] = $label['vValue'];

        } elseif ($label['vLabel'] == 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_TITLE') {
            $VideoConsultMSTitleArr[$label['vCode']] = $label['vValue'];

        } elseif ($label['vLabel'] == 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_DESC') {
            $VideoConsultMSSubTitleArr[$label['vCode']] = $label['vValue'];

        } elseif ($label['vLabel'] == 'LBL_MEDICAL_MORE_SERVICES_TITLE') {
            $MoreServiceMSTitleArr[$label['vCode']] = $label['vValue'];

        } elseif ($label['vLabel'] == 'LBL_MEDICAL_MORE_SERVICES_DESC') {
            $MoreServiceMSSubTitleArr[$label['vCode']] = $label['vValue'];
        }
    }

    $medicalServiceDataArr = $obj->MySQLSelect("SELECT vc.iParentId,vc.iVehicleCategoryId,vc.vCategory_$default_lang as vCategoryName, vc.eStatus, vc.iDisplayOrder,vc.eCatType,vc.eForMedicalService, vc.eVideoConsultEnable, vc.tMedicalServiceInfo, (select count(iVehicleCategoryId) FROM " . $sql_vehicle_category_table_name . " WHERE vc.iParentId = vc.iVehicleCategoryId AND eStatus = 'Active') as SubCategories FROM " . $sql_vehicle_category_table_name . " as vc WHERE eStatus = 'Active' AND (vc.iParentId='0' OR vc.iParentId = '3') AND eForMedicalService = 'Yes' AND iVehicleCategoryId != 297 ORDER BY iDisplayOrder ASC");
    $OnDemandServicesArr = $VideoConsultServicesArr = $MoreServicesArr = array();
    foreach ($medicalServiceDataArr as $medicalService) {
        if (!empty($medicalService['tMedicalServiceInfo'])) {
            $tMedicalServiceInfoArr = json_decode($medicalService['tMedicalServiceInfo'], true);
            $medicalServiceData = $medicalService;
            if ($tMedicalServiceInfoArr['BookService'] == "Yes") {
                $medicalServiceData['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderBS'];
                $medicalServiceDataBS = array();
                $medicalServiceDataBS = $medicalServiceData;
                $medicalServiceDataBS['eVideoConsultEnable'] = "No";
                $OnDemandServicesArr[] = $medicalServiceDataBS;
            }
            if ($medicalService['eVideoConsultEnable'] == "Yes" && $tMedicalServiceInfoArr['VideoConsult'] == "Yes") {
                $medicalServiceData['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderVC'];
                $VideoConsultServicesArr[] = $medicalServiceData;
            }
            if ($tMedicalServiceInfoArr['MoreService'] == "Yes") {
                $medicalServiceData['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderMS'];
                $medicalServiceDataMS = array();
                $medicalServiceDataMS = $medicalServiceData;
                $medicalServiceDataMS['eVideoConsultEnable'] = "No";
                $MoreServicesArr[] = $medicalServiceDataMS;
            }
        }
    }
    $ms_display_order = array_column($OnDemandServicesArr, 'ms_display_order');
    array_multisort($ms_display_order, SORT_ASC, $OnDemandServicesArr);
    $ms_display_order = array_column($VideoConsultServicesArr, 'ms_display_order');
    array_multisort($ms_display_order, SORT_ASC, $VideoConsultServicesArr);
    $ms_display_order = array_column($MoreServicesArr, 'ms_display_order');
    array_multisort($ms_display_order, SORT_ASC, $MoreServicesArr);

    $tServiceDetailsMS = $db_data_arr['MedicalServices']['tServiceDetails'];
    $tServiceDetailsMSArr = array();
    if (!empty($tServiceDetailsMS)) {
        $tServiceDetailsMSArr = json_decode($tServiceDetailsMS, true);
    }

    $MEDICAL_SERVICES_ARR = array(
        array(
            'ServiceTitle' => $BookServiceMSTitleArr, 
            'ServiceDesc' => $BookServiceMSSubTitleArr, 
            'ManageServiceKey' => 'BookService', 
            'ManageServiceSuffix' => 'BS', 
            'HiddenInput' => 'saveBookServiceMS', 
            'ServicesArr' => $OnDemandServicesArr

        ), 
        array(
            'ServiceTitle' => $VideoConsultMSTitleArr, 
            'ServiceDesc' => $VideoConsultMSSubTitleArr, 
            'ManageServiceKey' => 'VideoConsult', 
            'ManageServiceSuffix' => 'VC',  
            'HiddenInput' => 'saveVideoConsultMS', 
            'ServicesArr' => $VideoConsultServicesArr
        ),

        array(
            'ServiceTitle' => $MoreServiceMSTitleArr, 
            'ServiceDesc' => $MoreServiceMSSubTitleArr, 
            'ManageServiceKey' => 'MoreService', 
            'ManageServiceSuffix' => 'MS', 
            'HiddenInput' => 'saveMoreServiceMS', 
            'ServicesArr' => $MoreServicesArr
        )
    );

    $TextColorMS['BookService'] = $TextColorMS['VideoConsult'] = $TextColorMS['MoreService'] = "#000000";
    $BgColorMS['BookService'] = $BgColorMS['VideoConsult'] = $BgColorMS['MoreService'] = "#ffffff";
    $vImageOldMS['BookService'] = $vImageOldMS['VideoConsult'] = $vImageOldMS['MoreService'] = "";

    $tCategoryDetailsMS = $MasterCategoryArr['MedicalServices']['tCategoryDetails'];
    if (!empty($tCategoryDetailsMS)) {
        $tCategoryDetails = $tCategoryDetailsMS;
        if (!empty($tCategoryDetails)) {
            $tCategoryDetails = json_decode($tCategoryDetails, true);
            $TextColorMS['BookService'] = $tCategoryDetails['BookService']['vTextColor'];
            $BgColorMS['BookService'] = $tCategoryDetails['BookService']['vBgColor'];
            $vImageOldMS['BookService'] = $tCategoryDetails['BookService']['vImage'];
            $TextColorMS['VideoConsult'] = $tCategoryDetails['VideoConsult']['vTextColor'];
            $BgColorMS['VideoConsult'] = $tCategoryDetails['VideoConsult']['vBgColor'];
            $vImageOldMS['VideoConsult'] = $tCategoryDetails['VideoConsult']['vImage'];
            $TextColorMS['MoreService'] = $tCategoryDetails['MoreService']['vTextColor'];
            $BgColorMS['MoreService'] = $tCategoryDetails['MoreService']['vBgColor'];
            $vImageOldMS['MoreService'] = $tCategoryDetails['MoreService']['vImage'];
        }
    }
}

/* Track Service */
if ($MODULES_OBJ->isEnableTrackAnyServiceFeature()) {
    $vTrackServiceTitleArr = json_decode($db_data_arr['TrackAnyService']['vTitle'], true);
    foreach ($vTrackServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vTrackServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $TrackServiceData = $obj->MySQLSelect("SELECT * FROM track_service_category WHERE eStatus = 'Active'");

    $TrackServiceDataArr = array();
    foreach ($TrackServiceData as $TrackService) {
        $TrackServiceDataArr[] = array(
            'vCategoryName' => json_decode($TrackService['vCategoryName'], true),
            'vCategoryDesc' => json_decode($TrackService['vCategoryDesc'], true),
            'vImage' => $TrackService['vImage'],
            'eMemberType' => $TrackService['eMemberType'],
            'vTextColor' => $TrackService['vTextColor'],
            'vBgColor' => $TrackService['vBgColor'],
            'MemberTypeKey' => $TrackService['eMemberType'] . 'Member'
        );
    }
}

/* Ride Share */
if ($MODULES_OBJ->isEnableRideShareService()) {
    $vRideShareTitleArr = json_decode($db_data_arr['RideShare']['vTitle'], true);
    foreach ($vRideShareTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vRideShareTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vRideShareSubTitleArr = json_decode($db_data_arr['RideShare']['vSubtitle'], true);
    foreach ($vRideShareSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vRideShareSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldRideShare = $db_data_arr['RideShare']['vImage'];

    $RideShareLayoutDetails = json_decode($db_data_arr['RideShare']['tLayoutDetails'], true);
    $vTitleColorRideShare = $RideShareLayoutDetails['vTxtTitleColor']; 
    $vSubTitleColorRideShare = $RideShareLayoutDetails['vTxtSubTitleColor']; 
    $vBgColorRideShare = $RideShareLayoutDetails['vBgColor'];
}

/* Nearby Services */
if ($MODULES_OBJ->isEnableNearByService()) {
    $vNearbyServiceTitleArr = json_decode($db_data_arr['NearBy']['vTitle'], true);
    foreach ($vNearbyServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vNearbyServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }

    $tServiceDetailsNearby = $db_data_arr['NearBy']['tServiceDetails'];
    $tServiceDetailsNearbyArr = array();
    if (!empty($tServiceDetailsNearby)) {
        $tServiceDetailsNearbyArr = json_decode($tServiceDetailsNearby, true);
    }
    $NearByData = $NEARBY_OBJ->getNearByCategory('admin', '', 0, 0, $default_lang);
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
                    <div class="show-help-section section-title">Search Bar</div>
                    <div class="underline-section-title"></div>

                    <?php if (scount($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vSearchBarTitle_Default" name="vSearchBarTitle_Default" value="<?= $userEditDataArr['vSearchBarTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vSearchBarTitle_' . $default_lang]; ?>" readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editSearchBarTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="SearchBarTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="searchbar_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vSearchBarTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vSearchBarTitle_' . $vCode;
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
                                                                        onClick="getAllLanguageCode('vSearchBarTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vSearchBarTitle_', '<?= $default_lang ?>');">
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
                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveSearchBarTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vSearchBarTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                <input type="text" class="form-control" id="vSearchBarTitle_<?= $default_lang ?>" name="vSearchBarTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vSearchBarTitle_' . $default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveSearchBarSection">Save</button>
                        </div>
                    </div>

                    <?php if ($MODULES_OBJ->isRideFeatureAvailable() || $MODULES_OBJ->isEnableTaxiBidFeature() || $MODULES_OBJ->isInterCityFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Taxi Services</div>
                        <div class="underline-section-title"></div>

                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vTaxiServiceTitle_Default" name="vTaxiServiceTitle_Default" value="<?= $userEditDataArr['vTaxiServiceTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vTaxiServiceTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editTaxiServiceTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="TaxiServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="taxiservice_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vTaxiServiceTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vTaxiServiceTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vTaxiServiceTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vTaxiServiceTitle_', '<?= $default_lang ?>');">
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveTaxiServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTaxiServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vTaxiServiceTitle_<?= $default_lang ?>" name="vTaxiServiceTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vTaxiServiceTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveTaxiServiceSection">Save</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block service-img-grid">
                                        <?php $taxiCount = 0; foreach ($taxiData as $taxiService) {
                                            if (!empty($taxiService['vListLogo3']) && $taxiCount < 7) {
                                                $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $taxiService['iVehicleCategoryId'] . "/" . $taxiService['vListLogo3'];
                                        ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $taxiService['vCategoryName'] ?></div>
                                        </div>
                                    
                                        <?php $taxiCount++; } } ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_taxi_services.png'); ?>">
                                            <div class="service-img-title"><?= $langage_lbl_admin['LBL_MORE_SERVICES'] ?></div>
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
                                            Taxi Services
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
                                            
                                            
                                            foreach ($taxiData as $taxiService) {
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
                                                    <td style="vertical-align: middle;"><?= $taxiService['vCategoryName'] ?></td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php
                                                        if ($taxiService['eStatus'] == 'Active') {
                                                            $status_img = "img/active-icon.png";
                                                        } else if ($taxiService['eStatus'] == 'Inactive') {
                                                            $status_img = "img/inactive-icon.png";
                                                        } else {
                                                            $status_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $taxiService['eStatus']; ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <select class="form-control" name="iDisplayOrderTaxiServiceArr[]" data-serviceid="<?= $taxiService['iVehicleCategoryId'] ?>">
                                                            <?php for ($disp_order = 1; $disp_order <= scount($taxiData); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php }                                              
                                                                                            
                                                $editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=taxi_more_services&vLabel=LBL_MORE_SERVICES';

                                                $vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_taxi_services.png');
                                            
                                            ?>
                                            <tr>
												<td style="text-align: center; ">
                                                        <?php if (!empty($vServicemoreImg)) { ?>
                                                            <img src="<?= $vServicemoreImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                </td>
                                                <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE_SERVICES'] ?></td>
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
                                        <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('Ride')">Save</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                     <?php if ($MODULES_OBJ->isDeliveryFeatureAvailable()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Parcel Delivery</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
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
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vDeliverSubTitle_Default"
                                           name="vDeliverSubTitle_Default"
                                           value="<?= $userEditDataArr['vDeliverSubTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vDeliverSubTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editDeliverSubTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="DeliverSubTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="deliver_subtitle_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vDeliverSubTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vDeliverSubTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vDeliverSubTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vDeliverSubTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveDeliverSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vDeliverSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vDeliverSubTitle_<?= $default_lang ?>"
                                           name="vDeliverSubTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vDeliverSubTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vTitleColorDeliver" class="form-control txt-color" value="<?= $vTitleColorDeliver ?>"/>
                                <input type="hidden" name="vTitleColorDeliver" id="vTitleColorDeliver" value="<?= $vTitleColorDeliver ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Subtitle Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vSubTitleColorDeliver" class="form-control txt-color" value="<?= $vSubTitleColorDeliver ?>"/>
                                <input type="hidden" name="vSubTitleColorDeliver" id="vSubTitleColorDeliver" value="<?= $vSubTitleColorDeliver ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Background Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vBgColorDeliver" class="form-control bg-color" value="<?= $vBgColorDeliver ?>"/>
                                <input type="hidden" name="vBgColorDeliver" id="vBgColorDeliver" value="<?= $vBgColorDeliver ?>">
                            </div>
                        </div>

                        <div class="row pb-10">
                            <div class="col-lg-12">
                                <label>Image</label>
                            </div>
                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                <?php if(!empty($vImageOldDeliver)) { ?>
                                <div class="marginbottom-10">
                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldDeliver; ?>" id="deliver_img">
                                </div>
                                <?php } ?>
                                <input type="file" class="form-control" name="vImageDeliver" id="vImageDeliver" onchange="previewImage(this, event);" data-img="deliver_img">
                                <input type="hidden" class="form-control" name="vImageOldDeliver" id="vImageOldDeliver" value="<?= $vImageOldDeliver ?>">
                                <strong class="img-note">Note: Upload only png image size of 450px X 508px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveDeliverSection">Save</button>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($MODULES_OBJ->isDeliverAllFeatureAvailable() /*|| $MODULES_OBJ->isDeliveryFeatureAvailable() || $MODULES_OBJ->isEnableGenieFeature() || $MODULES_OBJ->isEnableRunnerFeature()*/) { ?>
                        <hr />
                        <div class="show-help-section section-title">Delivery Services</div>
                        <div class="underline-section-title"></div>

                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vDeliveryServiceTitle_Default" name="vDeliveryServiceTitle_Default" value="<?= $userEditDataArr['vDeliveryServiceTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vDeliveryServiceTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editDeliveryServiceTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="DeliveryServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="deliveryservice_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vDeliveryServiceTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vDeliveryServiceTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vDeliveryServiceTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vDeliveryServiceTitle_', '<?= $default_lang ?>');">
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDeliveryServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vDeliveryServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vDeliveryServiceTitle_<?= $default_lang ?>" name="vDeliveryServiceTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vDeliveryServiceTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveDeliveryServiceSection">Save</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block service-img-grid">
                                    <?php $deliveryCount = 0; foreach ($DeliveryServicesArr as $DeliveryService) {
                                        if (!empty($DeliveryService['vListLogo3']) && $deliveryCount < 7) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $DeliveryService['iVehicleCategoryId'] . "/" . $DeliveryService['vListLogo3'];
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $DeliveryService['vCategoryName'] ?></div>
                                        </div>
                                    
                                    <?php $deliveryCount++; } } ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_delivery_services.png') ?>">
                                        <div class="service-img-title"><?= $langage_lbl_admin['LBL_MORE_SERVICES'] ?></div>
                                    </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#deliveryservices_modal" style="margin-top: 25px;">Manage Services for App</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deliveryservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            Delivery Services
                                            <button type="button" class="close" data-dismiss="modal">x</button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th style="text-align: center;">Icon</th>
                                                <th>Service Category</th>
                                                <th>Status</th>
                                                <th>Display Order</th>
                                                <th style="text-align: center;">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($DeliveryServicesArr as $DeliveryService) {
                                                $vServiceDisplayOrder = $DeliveryService['iDisplayOrder'];
                                                $vServiceImg = "";
                                                if (!empty($DeliveryService['vListLogo3'])) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $DeliveryService['iVehicleCategoryId'] . "/" . $DeliveryService['vListLogo3'];
                                                }

                                                $editUrl = $tconfig['tsite_url_main_admin'] . 'vehicle_category_action.php?id=' . $DeliveryService['iVehicleCategoryId'];
                                                if($DeliveryService['eCatType'] == "MoreDelivery") {
                                                    $editUrl .= "&eServiceType=Deliver";
                                                } else {
                                                    $editUrl .= "&eServiceType=" . $DeliveryService['eCatType'];
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
                                                    <td style="vertical-align: middle;"><?= $DeliveryService['vCategoryName'] ?></td>
                                                     <td style="text-align: center; vertical-align: middle;">
                                                        <?php
                                                        if ($DeliveryService['eStatus'] == 'Active') {
                                                            $status_img = "img/active-icon.png";
                                                        } else if ($DeliveryService['eStatus'] == 'Inactive') {
                                                            $status_img = "img/inactive-icon.png";
                                                        } else {
                                                            $status_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $DeliveryService['eStatus']; ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <select class="form-control" name="iDisplayOrderDeliveryServiceArr[]" data-serviceid="<?= $DeliveryService['iVehicleCategoryId'] ?>">
                                                            <?php for ($disp_order = 1; $disp_order <= scount($DeliveryServicesArr); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php } 
                                                                                            
                                                $editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=delivery_more_services&vLabel=LBL_MORE_SERVICES';

                                                $vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_delivery_services.png');
                                            
                                            ?>
                                            <tr>
												<td style="text-align: center; vertical-align: middle;">
                                                    <?php if (!empty($vServicemoreImg)) { ?>
                                                        <img src="<?= $vServicemoreImg ?>">
                                                    <?php } else { ?>
                                                        --
                                                    <?php } ?>
                                                </td>
                                                <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE_SERVICES'] ?></td>
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
                                        <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('Delivery')">Save</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

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
                                    <input type="text" class="form-control" id="vOnDemandServiceTitle_Default" name="vOnDemandServiceTitle_Default" value="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editOnDemandServiceTitle('Edit')">
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
                                <button type="button" class="btn btn-primary save-section-btn" id="saveOnDemandServiceSection">Save</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block" style="background-color: #E4FFFE">
                                    <?php $ufxCount = 0; foreach ($ufxData as $ufxService) {
                                        if (!empty($ufxService['vListLogo3']) && $ufxCount < 3) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ufxService['iVehicleCategoryId'] . "/" . $ufxService['vListLogo3'];
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ufxService['vCategoryName'] ?></div>
                                        </div>
                                    
                                        <?php $ufxCount++; } } ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_other_services_sp.png') ?>">
                                            <div class="service-img-title"><?= $langage_lbl_admin['LBL_MORE_ONDEMAND_SERVICES'] ?></div>
                                        </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#ondemandservices_modal">Manage Services for App</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="ondemandservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            On-Demand Services
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
                                            foreach ($ufxData as $ufxService) {
                                                $vServiceDisplayOrder = $ufxService['iDisplayOrder'];
                                                $vServiceImg = "";
                                                if (!empty($ufxService['vListLogo3'])) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ufxService['iVehicleCategoryId'] . "/" . $ufxService['vListLogo3'];
                                                }

                                                $editUrl = $tconfig['tsite_url_main_admin'] . 'vehicle_category_action.php?id=' . $ufxService['iVehicleCategoryId'] . '&eServiceType=UberX';
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
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php
                                                        if ($ufxService['eStatus'] == 'Active') {
                                                            $status_img = "img/active-icon.png";
                                                        } else if ($ufxService['eStatus'] == 'Inactive') {
                                                            $status_img = "img/inactive-icon.png";
                                                        } else {
                                                            $status_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $ufxService['eStatus']; ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <select class="form-control" name="iDisplayOrderUberXServiceArr[]" data-serviceid="<?= $ufxService['iVehicleCategoryId'] ?>">
                                                            <?php for ($disp_order = 1; $disp_order <= scount($ufxData); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php } 
                                                                                            
                                                $editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=ondemand_more_services&vLabel=LBL_MORE_ONDEMAND_SERVICES';

                                                $vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_other_services_sp.png');
                                            
                                            ?>
                                                <tr>
    												<td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServicemoreImg)) { ?>
                                                            <img src="<?= $vServicemoreImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE_ONDEMAND_SERVICES'] ?></td>
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
                                        <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('UberX')">Save</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($MODULES_OBJ->isEnableVideoConsultingService()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Video Consultation</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vVideoConsultTitle_Default" name="vVideoConsultTitle_Default" value="<?= $userEditDataArr['vVideoConsultTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vVideoConsultTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editVideoConsultTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="VideoConsultTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="videoconsult_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVideoConsultTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vVideoConsultTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vVideoConsultTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vVideoConsultTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveVideoConsultTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVideoConsultTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vVideoConsultSubTitle_Default"
                                           name="vVideoConsultSubTitle_Default"
                                           value="<?= $userEditDataArr['vVideoConsultSubTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vVideoConsultSubTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editVideoConsultSubTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="VideoConsultSubTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="videoconsult_subtitle_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVideoConsultSubTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vVideoConsultSubTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vVideoConsultSubTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vVideoConsultSubTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveVideoConsultSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVideoConsultSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vVideoConsultTitle_<?= $default_lang ?>"
                                           name="vVideoConsultTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vVideoConsultTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vVideoConsultSubTitle_<?= $default_lang ?>"
                                           name="vVideoConsultSubTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vVideoConsultSubTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveVideoConsultSection">Save</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block">
                                    <?php $ufxCountVC = 0; foreach ($ufxDataVC as $ufxServiceVC) {
                                        if (!empty($ufxServiceVC['vIconDetails']) && $ufxCountVC < 3) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ufxServiceVC['iVehicleCategoryId'] . "/" . $ufxServiceVC['vIconDetails'];
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ufxServiceVC['vCategoryName'] ?></div>
                                        </div>
                                    
                                        <?php $ufxCountVC++; } } ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_other_services_vc.png') ?>">
                                            <div class="service-img-title"><?= $langage_lbl_admin['LBL_MORE'] ?></div>
                                        </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#vcservices_modal">Manage Services for App</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="vcservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            Video Consultation Services
                                            <button type="button" class="close" data-dismiss="modal">x</button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th style="text-align: center;">Icon</th>
                                                <th>Service Category</th>
                                                <th>Status</th>
                                                <th>Display Order</th>
                                                <th style="text-align: center;">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($ufxDataVC as $ufxServiceVC) {
                                                $vServiceDisplayOrder = $ufxServiceVC['iDisplayOrderVC'];
                                                $vServiceImg = "";
                                                if (!empty($ufxServiceVC['vIconDetails'])) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ufxServiceVC['iVehicleCategoryId'] . "/" . $ufxServiceVC['vIconDetails'];
                                                }

                                                $editUrl = $tconfig['tsite_url_main_admin'] . 'vehicle_category_action.php?id=' . $ufxServiceVC['iVehicleCategoryId'] . '&eServiceType=VideoConsult';
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServiceImg)) { ?>
                                                            <img src="<?= $vServiceImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $ufxServiceVC['vCategoryName'] ?></td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php
                                                        if ($ufxServiceVC['eStatus'] == 'Active') {
                                                            $status_img = "img/active-icon.png";
                                                        } else if ($ufxServiceVC['eStatus'] == 'Inactive') {
                                                            $status_img = "img/inactive-icon.png";
                                                        } else {
                                                            $status_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $ufxServiceVC['eStatus']; ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <select class="form-control" name="iDisplayOrderVCServiceArr[]" data-serviceid="<?= $ufxServiceVC['iVehicleCategoryId'] ?>">
                                                            <?php for ($disp_order = 1; $disp_order <= scount($ufxDataVC); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php } 
																									
												$editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=video_more_services&vLabel=LBL_MORE';

												$vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_other_services_vc.png');
											
													?>
													<tr>
														<td style="text-align: center; vertical-align: middle;">
															<?php if (!empty($vServicemoreImg)) { ?>
																<img src="<?= $vServicemoreImg ?>">
															<?php } else { ?>
																--
															<?php } ?>
														</td>
														<td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE'] ?></td>
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
                                        <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('VideoConsult')">Save</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } 	?>

                    <?php if ($MODULES_OBJ->isEnableRentEstateService() || $MODULES_OBJ->isEnableRentCarsService() || $MODULES_OBJ->isEnableRentItemService()) { 
                        $show_rentestate_tab = $show_rentcars_tab = $show_rentitem_tab = "";
                        $show_rentestate_content = $show_rentcars_content = $show_rentitem_content = "";
                        ?>
                        <hr/>
                        <div class="show-help-section section-title">Buy, Sell & Rent</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vBSRTitle_Default"
                                           name="vBSRTitle_Default"
                                           value="<?= $userEditDataArr['vBSRTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vBSRTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editBSRTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="BSRTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="bsr_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vBSRTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vBSRTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vBSRTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vBSRTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveBSRTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vBSRTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vBSRTitle_<?= $default_lang ?>" name="vBSRTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vBSRTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveBSRTitleSection">Save</button>
                            </div>
                        </div>

                        <div class="row paddingbottom-0">
                            <div class="col-lg-12">
                                <div class="tab">
                                    <?php if ($MODULES_OBJ->isEnableRentEstateService()) { $show_rentestate_tab = "active"; $show_rentestate_content = "display-tab-content"; ?>
                                    <button class="tablinks manage-rentestate-tab <?= $show_rentestate_tab ?>" onclick="openTabContent(event, 'manage-rentestate-content', 'tabcontent-buysellrent')"> Buy, Sell & Rent Real Estate
                                    </button>
                                    <?php } if($MODULES_OBJ->isEnableRentCarsService()) {
                                        if(empty($show_rentestate_tab)) {
                                            $show_rentcars_tab = "active";
                                            $show_rentcars_content = "display-tab-content";
                                        }
                                        ?>
                                    <button class="tablinks manage-rentcars-tab <?= $show_rentcars_tab ?>" onclick="openTabContent(event, 'manage-rentcars-content', 'tabcontent-buysellrent')"> Buy, Sell & Rent Cars
                                    </button>
                                    <?php } if($MODULES_OBJ->isEnableRentItemService()) {
                                        if(empty($show_rentestate_tab) && empty($show_rentcars_tab)) {
                                            $show_rentitem_tab = "active";
                                            $show_rentitem_content = "display-tab-content";
                                        }
                                        ?>
                                    <button class="tablinks manage-rentitem-tab <?= $show_rentitem_tab ?>" onclick="openTabContent(event, 'manage-rentitem-content', 'tabcontent-buysellrent')"> Buy, Sell & Rent General Items
                                    </button>
                                    <?php } ?>
                                </div>

                                <?php if($MODULES_OBJ->isEnableRentEstateService()) { ?>
                                <div class="tabcontent tabcontent-buysellrent <?= $show_rentestate_content ?>" id="manage-rentestate-content">
                                    <div class="col-lg-12">
                                        <?php if (scount($db_master) > 1) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="vRentEstateTitle_Default"
                                                           name="vRentEstateTitle_Default"
                                                           value="<?= $userEditDataArr['vRentEstateTitle_' . $default_lang]; ?>"
                                                           data-originalvalue="<?= $userEditDataArr['vRentEstateTitle_' . $default_lang]; ?>"
                                                           readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editRentEstateTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RentEstateTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                                 data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="rentestate_title_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentEstateTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vRentEstateTitle_' . $vCode;
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
                                                                                            onClick="getAllLanguageCode('vRentEstateTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('vRentEstateTitle_', '<?= $default_lang ?>');">
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
                                                                        onclick="saveRentEstateTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentEstateTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="vRentEstateSubTitle_Default"
                                                           name="vRentEstateSubTitle_Default"
                                                           value="<?= $userEditDataArr['vRentEstateSubTitle_' . $default_lang]; ?>"
                                                           data-originalvalue="<?= $userEditDataArr['vRentEstateSubTitle_' . $default_lang]; ?>"
                                                           readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editRentEstateSubTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RentEstateSubTitle_Modal" tabindex="-1" role="dialog"
                                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="rentestate_subtitle_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentEstateSubTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vRentEstateSubTitle_' . $vCode;
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
                                                                                            onClick="getAllLanguageCode('vRentEstateSubTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('vRentEstateSubTitle_', '<?= $default_lang ?>');">
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
                                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveRentEstateSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRentEstateSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="vRentEstateTitle_<?= $default_lang ?>"
                                                           name="vRentEstateTitle_<?= $default_lang ?>"
                                                           value="<?= $userEditDataArr['vRentEstateTitle_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Subtitle</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="vRentEstateSubTitle_<?= $default_lang ?>"
                                                           name="vRentEstateSubTitle_<?= $default_lang ?>"
                                                           value="<?= $userEditDataArr['vRentEstateSubTitle_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Text Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vTitleColorRentEstate" class="form-control txt-color" value="<?= $vTitleColorRentEstate ?>"/>
                                                <input type="hidden" name="vTitleColorRentEstate" id="vTitleColorRentEstate" value="<?= $vTitleColorRentEstate ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vBgColorRentEstate" class="form-control bg-color" value="<?= $vBgColorRentEstate ?>"/>
                                                <input type="hidden" name="vBgColorRentEstate" id="vBgColorRentEstate" value="<?= $vBgColorRentEstate ?>">
                                            </div>
                                        </div>
                                        <form id="frm2">    
                                        <div class="row pb-10">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                                <?php if(!empty($vImageOldRentEstate)) { ?>
                                                <div class="marginbottom-10">
                                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vImageOldRentEstate; ?>" id="rentestate_img">
                                                </div>
                                                <?php } ?>
                                                <!-- <input type="file" class="form-control" name="vImageRentEstate" id="vImageRentEstate" onchange="previewImage(this, event);" data-img="rentestate_img"> -->

                                                <input type="file" class="form-control FilUploader" name="vImageRentEstate" id="vImageRentEstate" data-img="rentestate_img">

                                                <input type="hidden" class="form-control" name="vImageOldRentEstate" id="vImageOldRentEstate" value="<?= $vImageOldRentEstate ?>">
                                                <strong class="img-note">Note: Upload only png image size of 1024px X 618px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
                                        </form>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-primary save-section-btn" id="saveRentEstateSection">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>                        
                                <?php } ?>

                                <?php if($MODULES_OBJ->isEnableRentCarsService()) { ?>
                                <div class="tabcontent tabcontent-buysellrent <?= $show_rentcars_content ?>" id="manage-rentcars-content">
                                    <div class="col-lg-12">
                                        <?php if (scount($db_master) > 1) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="vRentCarsTitle_Default"
                                                           name="vRentCarsTitle_Default"
                                                           value="<?= $userEditDataArr['vRentCarsTitle_' . $default_lang]; ?>"
                                                           data-originalvalue="<?= $userEditDataArr['vRentCarsTitle_' . $default_lang]; ?>"
                                                           readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editRentCarsTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RentCarsTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                                 data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="rentcars_title_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentCarsTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vRentCarsTitle_' . $vCode;
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
                                                                                            onClick="getAllLanguageCode('vRentCarsTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('vRentCarsTitle_', '<?= $default_lang ?>');">
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
                                                                        onclick="saveRentCarsTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentCarsTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="vRentCarsSubTitle_Default"
                                                           name="vRentCarsSubTitle_Default"
                                                           value="<?= $userEditDataArr['vRentCarsSubTitle_' . $default_lang]; ?>"
                                                           data-originalvalue="<?= $userEditDataArr['vRentCarsSubTitle_' . $default_lang]; ?>"
                                                           readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editRentCarsSubTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RentCarsSubTitle_Modal" tabindex="-1" role="dialog"
                                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="rentcars_subtitle_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentCarsSubTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vRentCarsSubTitle_' . $vCode;
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
                                                                                            onClick="getAllLanguageCode('vRentCarsSubTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('vRentCarsSubTitle_', '<?= $default_lang ?>');">
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
                                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveRentCarsSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRentCarsSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="vRentCarsTitle_<?= $default_lang ?>"
                                                           name="vRentCarsTitle_<?= $default_lang ?>"
                                                           value="<?= $userEditDataArr['vRentCarsTitle_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Subtitle</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="vRentCarsSubTitle_<?= $default_lang ?>"
                                                           name="vRentCarsSubTitle_<?= $default_lang ?>"
                                                           value="<?= $userEditDataArr['vRentCarsSubTitle_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Text Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vTitleColorRentCars" class="form-control txt-color" value="<?= $vTitleColorRentCars ?>"/>
                                                <input type="hidden" name="vTitleColorRentCars" id="vTitleColorRentCars" value="<?= $vTitleColorRentCars ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vBgColorRentCars" class="form-control bg-color" value="<?= $vBgColorRentCars ?>"/>
                                                <input type="hidden" name="vBgColorRentCars" id="vBgColorRentCars" value="<?= $vBgColorRentCars ?>">
                                            </div>
                                        </div>
                                        <form id="frm3">
                                        <div class="row pb-10">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                                <?php if(!empty($vImageOldRentCars)) { ?>
                                                <div class="marginbottom-10">
                                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vImageOldRentCars; ?>" id="rentcars_img">
                                                </div>
                                                <?php } ?>
                                                <input type="file" class="form-control FilUploader" name="vImageRentCars" id="vImageRentCars" data-img="rentcars_img">
                                                <input type="hidden" class="form-control" name="vImageOldRentCars" id="vImageOldRentCars" value="<?= $vImageOldRentCars ?>">
                                                <strong class="img-note">Note: Upload only png image size of 1024px X 494px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
                                        </form>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-primary save-section-btn" id="saveRentCarsSection">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>                        
                                <?php } ?>

                                <?php if($MODULES_OBJ->isEnableRentItemService()) { ?>
                                <div class="tabcontent tabcontent-buysellrent <?= $show_rentitem_content ?>" id="manage-rentitem-content">
                                    <div class="col-lg-12">
                                        <?php if (scount($db_master) > 1) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="vRentItemTitle_Default"
                                                           name="vRentItemTitle_Default"
                                                           value="<?= $userEditDataArr['vRentItemTitle_' . $default_lang]; ?>"
                                                           data-originalvalue="<?= $userEditDataArr['vRentItemTitle_' . $default_lang]; ?>"
                                                           readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editRentItemTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RentItemTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                                 data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="rentitem_title_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentItemTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vRentItemTitle_' . $vCode;
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
                                                                                            onClick="getAllLanguageCode('vRentItemTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('vRentItemTitle_', '<?= $default_lang ?>');">
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
                                                                        onclick="saveRentItemTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentItemTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="vRentItemSubTitle_Default"
                                                           name="vRentItemSubTitle_Default"
                                                           value="<?= $userEditDataArr['vRentItemSubTitle_' . $default_lang]; ?>"
                                                           data-originalvalue="<?= $userEditDataArr['vRentItemSubTitle_' . $default_lang]; ?>"
                                                           readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editRentItemSubTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RentItemSubTitle_Modal" tabindex="-1" role="dialog"
                                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="rentitem_subtitle_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        onclick="resetToOriginalValue(this, 'vRentItemSubTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'vRentItemSubTitle_' . $vCode;
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
                                                                                            onClick="getAllLanguageCode('vRentItemSubTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('vRentItemSubTitle_', '<?= $default_lang ?>');">
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
                                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveRentItemSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRentItemSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="vRentItemTitle_<?= $default_lang ?>"
                                                           name="vRentItemTitle_<?= $default_lang ?>"
                                                           value="<?= $userEditDataArr['vRentItemTitle_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Subtitle</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="vRentItemSubTitle_<?= $default_lang ?>"
                                                           name="vRentItemSubTitle_<?= $default_lang ?>"
                                                           value="<?= $userEditDataArr['vRentItemSubTitle_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Text Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vTitleColorRentItem" class="form-control txt-color" value="<?= $vTitleColorRentItem ?>"/>
                                                <input type="hidden" name="vTitleColorRentItem" id="vTitleColorRentItem" value="<?= $vTitleColorRentItem ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vBgColorRentItem" class="form-control bg-color" value="<?= $vBgColorRentItem ?>"/>
                                                <input type="hidden" name="vBgColorRentItem" id="vBgColorRentItem" value="<?= $vBgColorRentItem ?>">
                                            </div>
                                        </div>
                                        <form id="frm4">
                                        <div class="row pb-10">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                                <?php if(!empty($vImageOldRentItem)) { ?>
                                                <div class="marginbottom-10">
                                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . $vImageOldRentItem; ?>" id="rentitem_img">
                                                </div>
                                                <?php } ?>
                                                <input type="file" class="form-control FilUploader" name="vImageRentItem" id="vImageRentItem" data-img="rentitem_img">
                                                <input type="hidden" class="form-control" name="vImageOldRentItem" id="vImageOldRentItem" value="<?= $vImageOldRentItem ?>">
                                                <strong class="img-note">Note: Upload only png image size of 973px X 748px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
                                        </form>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-primary save-section-btn" id="saveRentItemSection">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($MODULES_OBJ->isEnableBiddingServices()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Bid for Services</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vBiddingTitle_Default"
                                           name="vBiddingTitle_Default"
                                           value="<?= $userEditDataArr['vBiddingTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vBiddingTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editBiddingTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="BiddingTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="bidding_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vBiddingTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vBiddingTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vBiddingTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vBiddingTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveBiddingTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vBiddingTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vBiddingSubTitle_Default"
                                           name="vBiddingSubTitle_Default"
                                           value="<?= $userEditDataArr['vBiddingSubTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vBiddingSubTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editBiddingSubTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="BiddingSubTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="bidding_subtitle_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vBiddingSubTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vBiddingSubTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vBiddingSubTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vBiddingSubTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveBiddingSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vBiddingSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vBiddingTitle_<?= $default_lang ?>"
                                           name="vBiddingTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vBiddingTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vBiddingSubTitle_<?= $default_lang ?>"
                                           name="vBiddingSubTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vBiddingSubTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveBiddingSection">Save</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block">
                                    <?php $ServiceBidCount = 0; foreach ($ServiceBidData as $ServiceBid) {
                                        $vIconImage = $ServiceBid['vImage'];
                                        if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3() && $ServiceBid['iParentId'] == 0) {
                                            $vIconImage = $ServiceBid['vImage1'];
                                        }
                                        if (!empty($vIconImage) && $ServiceBidCount < 3) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_bidding'] .$vIconImage;
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ServiceBid['vTitle'] ?></div>
                                        </div>
                                    
                                        <?php $ServiceBidCount++; } } ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_service_bid.png') ?>">
                                            <div class="service-img-title"><?= $langage_lbl_admin ['LBL_MORE'] ?></div>
                                        </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#bidservices_modal">Manage Services for App</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="bidservices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            Bid Services
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
                                            foreach ($ServiceBidData as $ServiceBid) {
                                                $vServiceDisplayOrder = $ServiceBid['iDisplayOrder'];
                                                $vServiceImg = "";
                                                if (!empty($ServiceBid['vImage'])) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_bidding']. $ServiceBid['vImage'];
                                                }
                                                if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3() && $ServiceBid['iParentId']) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_bidding'].$ServiceBid['vImage1'];
                                                }

                                                $editUrl = $tconfig['tsite_url_main_admin'] . 'bidding_master_category_action.php?id=' . $ServiceBid['iBiddingId'];
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServiceImg)) { ?>
                                                            <img src="<?= $vServiceImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $ServiceBid['vTitle'] ?></td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php
                                                        if ($ServiceBid['eStatus'] == 'Active') {
                                                            $status_img = "img/active-icon.png";
                                                        } else if ($ServiceBid['eStatus'] == 'Inactive') {
                                                            $status_img = "img/inactive-icon.png";
                                                        } else {
                                                            $status_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $ServiceBid['eStatus']; ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <select class="form-control" name="iDisplayOrderBidServiceArr[]" data-serviceid="<?= $ServiceBid['iBiddingId'] ?>">
                                                            <?php for ($disp_order = 1; $disp_order <= scount($ServiceBidData); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php } 

												$editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=bid_more_services&vLabel=LBL_MORE';

												$vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_service_bid.png');
												
												?>
												<tr>
													<td style="text-align: center; vertical-align: middle;">
														<?php if (!empty($vServicemoreImg)) { ?>
															<img src="<?= $vServicemoreImg ?>">
														<?php } else { ?>
															--
														<?php } ?>
													</td>
													<td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE'] ?></td>
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
                                        <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('Bidding')">Save</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
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
                                        <strong class="img-note">Note: Upload only png image size of 183px X 183px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
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
                                        <strong class="img-note">Note: Upload only png image size of 183px X 183px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
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

                    <?php if ($MODULES_OBJ->isEnableMedicalServices()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Medical Services</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vMSTitle_Default"
                                           name="vMSTitle_Default"
                                           value="<?= $userEditDataArr['vMSTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vMSTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editMSTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="MSTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="ms_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vMSTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vMSTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vMSTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vMSTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveMSTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vMSTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vMSTitle_<?= $default_lang ?>" name="vMSTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vMSTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveMSTitleSection">Save</button>
                            </div>
                        </div>

                        <div class="row paddingbottom-0">
                            <div class="col-lg-12">
                                <div class="tab">
                                    <?php $ms_count = 1; foreach ($MEDICAL_SERVICES_ARR as $MEDICAL_SERVICE) { ?>
                                    <button class="tablinks manage-<?= strtolower($MEDICAL_SERVICE['ManageServiceKey']) ?>-tab <?= $ms_count == 1 ? "active" : "" ?>" onclick="openTabContent(event, 'manage-<?= strtolower($MEDICAL_SERVICE['ManageServiceKey']) ?>-content', 'tabcontent-ms')"> <?= $MEDICAL_SERVICE['ServiceTitle']['EN'] ?>
                                    </button>
                                    <?php $ms_count++; } ?>
                                </div>

                                <?php $ms_count = 1; foreach ($MEDICAL_SERVICES_ARR as $MEDICAL_SERVICE) { ?>
                                <div class="tabcontent tabcontent-ms <?= $ms_count == 1 ? "display-tab-content" : "" ?>" id="manage-<?= strtolower($MEDICAL_SERVICE['ManageServiceKey']) ?>-content">
                                    <div class="col-lg-12">
                                        <?php if (scount($db_master) > 1) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_Default" name="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_Default" value="<?= $MEDICAL_SERVICE['ServiceTitle'][$default_lang]; ?>" data-originalvalue="<?= $MEDICAL_SERVICE['ServiceTitle'][$default_lang]; ?>" readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="edit<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                                 data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="<?= strtolower($MEDICAL_SERVICE['ManageServiceKey']) ?>ms_title_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'v' . $MEDICAL_SERVICE['ManageServiceKey'] . 'MSTitle_' . $vCode;
                                                                $$vValue = $MEDICAL_SERVICE['ServiceTitle'][$vCode];
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
                                                                                            onClick="getAllLanguageCode('v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_', '<?= $default_lang ?>');">
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
                                                                        onclick="save<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_Default" name="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_Default" value="<?= $MEDICAL_SERVICE['ServiceDesc'][$default_lang] ?>"  data-originalvalue="<?= $MEDICAL_SERVICE['ServiceDesc'][$default_lang] ?>" readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="edit<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_Modal" tabindex="-1" role="dialog"
                                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="<?= strtolower($MEDICAL_SERVICE['ManageServiceKey']) ?>_subtitle_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'v' . $MEDICAL_SERVICE['ManageServiceKey'] . 'MSSubTitle_' . $vCode;
                                                                $$vValue = $MEDICAL_SERVICE['ServiceDesc'][$vCode];
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
                                                                                            onClick="getAllLanguageCode('v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage"
                                                                                            id="allLanguage" class="btn btn-primary"
                                                                                            onClick="getAllLanguageCode('v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_', '<?= $default_lang ?>');">
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
                                                                        onclick="save<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_<?= $default_lang ?>" name="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSTitle_<?= $default_lang ?>"
                                                           value="<?= $MEDICAL_SERVICE['ServiceTitle'][$default_lang] ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Subtitle</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_<?= $default_lang ?>" name="v<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSubTitle_<?= $default_lang ?>" value="<?= $MEDICAL_SERVICE['ServiceDesc'][$default_lang] ?>">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-primary save-section-btn" id="save<?= $MEDICAL_SERVICE['ManageServiceKey'] ?>MSSection">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $ms_count++; } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($MODULES_OBJ->isEnableTrackAnyServiceFeature()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Track Your Members</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vTrackServiceTitle_Default"
                                           name="vTrackServiceTitle_Default"
                                           value="<?= $userEditDataArr['vTrackServiceTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vTrackServiceTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editTrackServiceTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="TrackServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="trackservice_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vTrackServiceTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vTrackServiceTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vTrackServiceTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vTrackServiceTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveTrackServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vTrackServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vTrackServiceTitle_<?= $default_lang ?>" name="vTrackServiceTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vTrackServiceTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveTrackServiceTitleSection">Save</button>
                            </div>
                        </div>

                        <div class="row paddingbottom-0">
                            <div class="col-lg-12">
                                <div class="tab">
                                    <?php foreach ($TrackServiceDataArr as $k => $TrackService) { ?>
                                    <button class="tablinks manage-<?= $TrackService['eMemberType'] ?>-tab <?= ($k == 0) ? 'active' : '' ?>" onclick="openTabContent(event, 'manage-<?= $TrackService['eMemberType'] ?>-member-content', 'tabcontent-trackservice')"> <?= $TrackService['vCategoryName']['vCategoryName_' . $default_lang] ?>
                                    </button>
                                    <?php } ?>
                                </div>

                                <?php foreach ($TrackServiceDataArr as $k => $TrackService) { ?>
                                <div class="tabcontent tabcontent-trackservice <?= ($k == 0) ? 'display-tab-content' : '' ?>" id="manage-<?= $TrackService['eMemberType'] ?>-member-content">
                                    <div class="col-lg-12">
                                        <?php if (scount($db_master) > 1) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="v<?= $TrackService['MemberTypeKey'] ?>Title_Default" name="v<?= $TrackService['MemberTypeKey'] ?>Title_Default" value="<?= $TrackService['vCategoryName']['vCategoryName_' . $default_lang]; ?>" data-originalvalue="<?= $TrackService['vCategoryName']['vCategoryName_' . $default_lang]; ?>" readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="edit<?= $TrackService['MemberTypeKey'] ?>Title('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?= $TrackService['MemberTypeKey'] ?>Title_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                                 data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="<?= strtolower($TrackService['MemberTypeKey']) ?>_title_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $TrackService['MemberTypeKey'] ?>Title_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'v' . $TrackService['MemberTypeKey'] . 'Title_' . $vCode;
                                                                $$vValue = $TrackService['vCategoryName']['vCategoryName_' . $vCode];
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
                                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>
                                                                    <?php
                                                                    if (scount($db_master) > 1) {
                                                                        if ($EN_available) {
                                                                            if ($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('v<?= $TrackService['MemberTypeKey'] ?>Title_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('v<?= $TrackService['MemberTypeKey'] ?>Title_', '<?= $default_lang ?>');">
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
                                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="save<?= $TrackService['MemberTypeKey'] ?>Title()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $TrackService['MemberTypeKey'] ?>Title_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="v<?= $TrackService['MemberTypeKey'] ?>SubTitle_Default" name="v<?= $TrackService['MemberTypeKey'] ?>SubTitle_Default" value="<?= $TrackService['vCategoryDesc']['vCategoryDesc_' . $default_lang]; ?>" data-originalvalue="<?= $TrackService['vCategoryDesc']['vCategoryDesc_' . $default_lang]; ?>" readonly="readonly" required>
                                                </div>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="edit<?= $TrackService['MemberTypeKey'] ?>SubTitle('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?= $TrackService['MemberTypeKey'] ?>SubTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                                 data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="<?= strtolower($TrackService['MemberTypeKey']) ?>_subtitle_modal_action"></span>
                                                                Title
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $TrackService['MemberTypeKey'] ?>SubTitle_')">x
                                                                </button>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];
                                                                $vValue = 'v' . $TrackService['MemberTypeKey'] . 'SubTitle_' . $vCode;
                                                                $$vValue = $TrackService['vCategoryDesc']['vCategoryDesc_' . $vCode];
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
                                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>
                                                                    <?php
                                                                    if (scount($db_master) > 1) {
                                                                        if ($EN_available) {
                                                                            if ($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('v<?= $TrackService['MemberTypeKey'] ?>SubTitle_', 'EN');">
                                                                                        Convert To All Language
                                                                                    </button>
                                                                                </div>
                                                                            <?php }
                                                                        } else {
                                                                            if ($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('v<?= $TrackService['MemberTypeKey'] ?>SubTitle_', '<?= $default_lang ?>');">
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
                                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="save<?= $TrackService['MemberTypeKey'] ?>SubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $TrackService['MemberTypeKey'] ?>SubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <input type="text" class="form-control" id="v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>" name="v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>" value="<?= $TrackService['vCategoryName']['vCategoryName_' . $default_lang]; ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Subtitle</label>
                                                </div>
                                                <div class="col-md-4 col-sm-4">
                                                    <input type="text" class="form-control" id="v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>" name="v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>" value="<?= $TrackService['vCategoryDesc']['vCategoryDesc_' . $default_lang]; ?>">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Text Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vTitleColor<?= $TrackService['MemberTypeKey'] ?>" class="form-control txt-color" value="<?= $TrackService['vTextColor'] ?>"/>
                                                <input type="hidden" name="vTitleColor<?= $TrackService['MemberTypeKey'] ?>" id="vTitleColor<?= $TrackService['MemberTypeKey'] ?>" value="<?= $TrackService['vTextColor'] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Color</label>
                                            </div>
                                            <div class="col-md-1 col-sm-1">
                                                <input type="color" data-id="vBgColor<?= $TrackService['MemberTypeKey'] ?>" class="form-control bg-color" value="<?= $TrackService['vBgColor'] ?>"/>
                                                <input type="hidden" name="vBgColor<?= $TrackService['MemberTypeKey'] ?>" id="vBgColor<?= $TrackService['MemberTypeKey'] ?>" value="<?= $TrackService['vBgColor'] ?>">
                                            </div>
                                        </div>
                                        <form id="frmTrack<?= $TrackService['MemberTypeKey'] ?>">
                                        <div class="row pb-10">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                                <?php if(!empty($TrackService['vImage'])) { ?>
                                                <div class="marginbottom-10">
                                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig["tsite_upload_app_home_screen_images"] . '/AppHomeScreen/' . $TrackService['vImage']; ?>" id="<?= strtolower($TrackService['MemberTypeKey']) ?>_img">
                                                </div>
                                                <?php } ?>
                                                <input type="file" class="form-control FilUploader" name="vImage<?= $TrackService['MemberTypeKey'] ?>" id="vImage<?= $TrackService['MemberTypeKey'] ?>" data-img="<?= strtolower($TrackService['MemberTypeKey']) ?>_img">
                                                <input type="hidden" class="form-control" name="vImageOld<?= $TrackService['MemberTypeKey'] ?>" id="vImageOld<?= $TrackService['MemberTypeKey'] ?>" value="<?= $TrackService['vImage'] ?>">
                                                <strong class="img-note">Note: Upload only png image size of 183px X 183px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
                                        </form>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn btn-primary save-section-btn" id="save<?= $TrackService['MemberTypeKey'] ?>Section">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                                <?php } ?>
                            </div>
                        </div>

                    <?php } ?>

                    <?php if ($MODULES_OBJ->isEnableRideShareService()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Ride Sharing/Car Pool</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vRideShareTitle_Default" name="vRideShareTitle_Default" value="<?= $userEditDataArr['vRideShareTitle_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vRideShareTitle_' . $default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editRideShareTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="RideShareTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="rideshare_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRideShareTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vRideShareTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vRideShareTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRideShareTitle_', '<?= $default_lang ?>');">
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveRideShareTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRideShareTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vRideShareSubTitle_Default"
                                           name="vRideShareSubTitle_Default"
                                           value="<?= $userEditDataArr['vRideShareSubTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vRideShareSubTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editRideShareSubTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="RideShareSubTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="rideshare_subtitle_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vRideShareSubTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vRideShareSubTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vRideShareSubTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vRideShareSubTitle_', '<?= $default_lang ?>');">
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveRideShareSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>

                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRideShareSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vRideShareTitle_<?= $default_lang ?>"
                                           name="vRideShareTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vRideShareTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vRideShareSubTitle_<?= $default_lang ?>"
                                           name="vRideShareSubTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vRideShareSubTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vTitleColorRideShare" class="form-control txt-color" value="<?= $vTitleColorRideShare ?>"/>
                                <input type="hidden" name="vTitleColorRideShare" id="vTitleColorRideShare" value="<?= $vTitleColorRideShare ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Subtitle Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vSubTitleColorRideShare" class="form-control txt-color" value="<?= $vSubTitleColorRideShare ?>"/>
                                <input type="hidden" name="vSubTitleColorRideShare" id="vSubTitleColorRideShare" value="<?= $vSubTitleColorRideShare ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Background Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vBgColorRideShare" class="form-control bg-color" value="<?= $vBgColorRideShare ?>"/>
                                <input type="hidden" name="vBgColorRideShare" id="vBgColorRideShare" value="<?= $vBgColorRideShare ?>">
                            </div>
                        </div>
                        <form id="frm1">    
                        <div class="row pb-10">
                            <div class="col-lg-12">
                                <label>Image</label>
                            </div>
                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                <?php if(!empty($vImageOldRideShare)) { ?>
                                <div class="marginbottom-10">
                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldRideShare; ?>" id="rideshare_img">
                                </div>
                                <?php } ?>
                                <!-- <input type="file" class="form-control" name="vImageRideShare" id="vImageRideShare" onchange="previewImage(this, event);" data-img="rideshare_img"> -->
                                <input type="file" class="form-control FilUploader" name="vImageRideShare" id="vImageRideShare" data-img="rideshare_img">
                                <input type="hidden" class="form-control" name="vImageOldRideShare" id="vImageOldRideShare" value="<?= $vImageOldRideShare ?>">
                                <strong class="img-note">Note: Upload only png image size of 795px X 650px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                            </div>
                        </div>
                        </form>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveRideShareSection">Save</button>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($MODULES_OBJ->isEnableNearByService()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Nearby Services</div>
                        <div class="underline-section-title"></div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vNearbyServiceTitle_Default"
                                           name="vNearbyServiceTitle_Default"
                                           value="<?= $userEditDataArr['vNearbyServiceTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vNearbyServiceTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editNearbyServiceTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="NearbyServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="nearbyservice_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vNearbyServiceTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vNearbyServiceTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vNearbyServiceTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vNearbyServiceTitle_', '<?= $default_lang ?>');">
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveNearbyServiceTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vNearbyServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vNearbyServiceTitle_<?= $default_lang ?>" name="vNearbyServiceTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vNearbyServiceTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveNearbyServiceSection">Save</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block">
                                    <?php $NearByCount = 0; foreach ($NearByData as $NearBy) {
                                        if (!empty($NearBy['vImage']) && $NearByCount < 3) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_nearby_item'] . $NearBy['vImage'];
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $NearBy['vTitle'] ?></div>
                                        </div>
                                    
                                        <?php $NearByCount++; } } ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_nearby.png') ?>">
                                            <div class="service-img-title">More</div>
                                        </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#nearbyervices_modal">Manage Services for App</button>
                                </div>
                                <div>
                                    <strong class="img-note">Note: This is just a preview of how it will appear in the app. The actual result might differ within the app.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="nearbyervices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            Nearby Services
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
                                            foreach ($NearByData as $NearBy) {
                                                $vServiceDisplayOrder = $NearBy['iDisplayOrder'];
                                                $vServiceImg = "";
                                                if (!empty($NearBy['vImage'])) {
                                                    $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_nearby_item'] . $NearBy['vImage'];
                                                }

                                                $editUrl = $tconfig['tsite_url_main_admin'] . 'near_by_category_action.php?id=' . $NearBy['iNearByCategoryId'];
                                                ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php if (!empty($vServiceImg)) { ?>
                                                            <img src="<?= $vServiceImg ?>">
                                                        <?php } else { ?>
                                                            --
                                                        <?php } ?>
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $NearBy['vTitle'] ?></td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <?php
                                                        if ($NearBy['eStatus'] == 'Active') {
                                                            $status_img = "img/active-icon.png";
                                                        } else if ($NearBy['eStatus'] == 'Inactive') {
                                                            $status_img = "img/inactive-icon.png";
                                                        } else {
                                                            $status_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $NearBy['eStatus']; ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <select class="form-control" name="iDisplayOrderNearByServiceArr[]" data-serviceid="<?= $NearBy['iCategoryId'] ?>">
                                                            <?php for ($disp_order = 1; $disp_order <= scount($NearByData); $disp_order++) { ?>
                                                                <option value="<?= $disp_order ?>" <?= $vServiceDisplayOrder == $disp_order ? 'selected' : '' ?>><?= $disp_order ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $editUrl ?>" class="btn btn-primary" target="_blank">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php } 
																								
												$editUrl_more = $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=nearby_more_services&vLabel=LBL_MORE';

												$vServicemoreImg = $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_nearby.png');
												
												?>
												<tr>
													<td style="text-align: center; vertical-align: middle;">
														<?php if (!empty($vServicemoreImg)) { ?>
															<img src="<?= $vServicemoreImg ?>">
														<?php } else { ?>
															--
														<?php } ?>
													</td>
													<td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE'] ?></td>
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
                                        <button type="button" class="btn btn-default" onclick="saveDisplayOrderService('NearBy')">Save</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            </form>
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
<script src="../assets/js/modal_alert.js"></script>
<script type="text/javascript">



    var errormessage;
        if ($('#frm1').length !== 0) {
            $('#frm1').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImageRideShare: {extension: imageUploadingExtenstionjsrule},                    
                },
                messages: {
                    vImageRideShare:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
            });
        }
        var errormessage;
        if ($('#frm2').length !== 0) {
            $('#frm2').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImageRentEstate: {extension: imageUploadingExtenstionjsrule},                    
                },
                messages: {
                    vImageRentEstate:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
            });
        }
        var errormessage;
        if ($('#frm3').length !== 0) {
            $('#frm3').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImageRentCars: {extension: imageUploadingExtenstionjsrule},
                },
                messages: {
                    vImageRentCars:{
                        extension: imageUploadingExtenstionMsg
                    }                   
                },
            });
        }
        var errormessage;
        if ($('#frm4').length !== 0) {
            $('#frm4').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImageRentItem: {extension: imageUploadingExtenstionjsrule},
                },
                messages: {
                    vImageRentItem:{
                        extension: imageUploadingExtenstionMsg
                    }
                },
            });
        }

        <?php foreach ($TrackServiceDataArr as $k => $TrackService) { ?>
        var errormessage;
        if ($('#frmTrack<?= $TrackService['MemberTypeKey'] ?>').length !== 0) {
            $('#frmTrack<?= $TrackService['MemberTypeKey'] ?>').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImage<?= $TrackService['MemberTypeKey'] ?>: {extension: imageUploadingExtenstionjsrule},
                },
                messages: {
                    vImage<?= $TrackService['MemberTypeKey'] ?>:{
                        extension: imageUploadingExtenstionMsg
                    }
                  
                },
            });
        }
        <?php } ?>

    // $(".FilUploader").change(function () {
        
    //     var fileExtension = JSON.parse(imageUploadingExtenstionJson);  
        
    //     if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
    //         // alert("Only formats are allowed : " + fileExtension.join(', '));
    //         alert(imageUploadingExtenstionMsg);
    //         $(this).val('');
    //         return false;
    //     }
    //     previewImage(this, event);

    // });
    function editSearchBarTitle(action) {
        $('#searchbar_title_modal_action').html(action);
        $('#SearchBarTitle_Modal').modal('show');
    }

    function saveSearchBarTitle() {
        if ($('#vSearchBarTitle_<?= $default_lang ?>').val() == "") {
            $('#vSearchBarTitle_<?= $default_lang ?>_error').show();
            $('#vSearchBarTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vSearchBarTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vSearchBarTitle_Default').val($('#vSearchBarTitle_<?= $default_lang ?>').val());
        $('#vSearchBarTitle_Default').closest('.row').removeClass('has-error');
        $('#vSearchBarTitle_Default-error').remove();
        $('#SearchBarTitle_Modal').modal('hide');
    }

    $('#saveSearchBarSection').click(function() {
        var vSearchBarTitleArr = $('[name^="vSearchBarTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vSearchBarTitleArr, function(key, value) {
            if(value.name != "vSearchBarTitle_Default") {
                var name_key = value.name.replace('vSearchBarTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'SearchBar';
        saveHomeScreenData('saveSearchBarSection', postData, 'No');
    });

    function editTaxiServiceTitle(action) {
        $('#taxiservice_title_modal_action').html(action);
        $('#TaxiServiceTitle_Modal').modal('show');
    }

    function saveTaxiServiceTitle() {
        if ($('#vTaxiServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vTaxiServiceTitle_<?= $default_lang ?>_error').show();
            $('#vTaxiServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vTaxiServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vTaxiServiceTitle_Default').val($('#vTaxiServiceTitle_<?= $default_lang ?>').val());
        $('#vTaxiServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vTaxiServiceTitle_Default-error').remove();
        $('#TaxiServiceTitle_Modal').modal('hide');
    }

    $('#saveTaxiServiceSection').click(function() {
        var vTaxiServiceTitleArr = $('[name^="vTaxiServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vTaxiServiceTitleArr, function(key, value) {
            if(value.name != "vTaxiServiceTitle_Default") {
                var name_key = value.name.replace('vTaxiServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Ride';
        saveHomeScreenData('saveTaxiServiceSection', postData, 'No');
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

    function editDeliverSubTitle(action) {
        $('#deliver_subtitle_modal_action').html(action);
        $('#DeliverSubTitle_Modal').modal('show');
    }

    function saveDeliverSubTitle() {
        if ($('#vDeliverSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliverSubTitle_<?= $default_lang ?>_error').show();
            $('#vDeliverSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliverSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliverSubTitle_Default').val($('#vDeliverSubTitle_<?= $default_lang ?>').val());
        $('#vDeliverSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliverSubTitle_Default-error').remove();
        $('#DeliverSubTitle_Modal').modal('hide');
    }

    $('#saveDeliverSection').click(function() {
        var vDeliverTitleArr = $('[name^="vDeliverTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vDeliverTitleArr, function(key, value) {
            if(value.name != "vDeliverTitle_Default") {
                var name_key = value.name.replace('vDeliverTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vDeliverSubTitleArr = $('[name^="vDeliverSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vDeliverSubTitleArr, function(key, value) {
            if(value.name != "vDeliverSubTitle_Default") {
                var name_key = value.name.replace('vDeliverSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });
        var vImageDeliver = $('#vImageDeliver')[0].files[0];
        var vImageOldDeliver = $('#vImageOldDeliver').val();
        var vTitleColorDeliver = $('#vTitleColorDeliver').val();
        var vSubTitleColorDeliver = $('#vSubTitleColorDeliver').val();
        var vBgColorDeliver = $('#vBgColorDeliver').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageDeliver);
        postData.append('vImageOld', vImageOldDeliver);
        postData.append('vTxtTitleColor', vTitleColorDeliver);
        postData.append('vTxtSubTitleColor', vSubTitleColorDeliver);
        postData.append('vBgColor', vBgColorDeliver);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'Deliver');
        postData.append('ServiceTypeOther', 'Deliver');

        saveHomeScreenData('saveDeliverSection', postData);
    });


    function editDeliveryServiceTitle(action) {
        $('#deliveryservice_title_modal_action').html(action);
        $('#DeliveryServiceTitle_Modal').modal('show');
    }

    function saveDeliveryServiceTitle() {
        if ($('#vDeliveryServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliveryServiceTitle_<?= $default_lang ?>_error').show();
            $('#vDeliveryServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliveryServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        console.log($('#vDeliveryServiceTitle_<?= $default_lang ?>').val());
        $('#vDeliveryServiceTitle_Default').val($('#vDeliveryServiceTitle_<?= $default_lang ?>').val());
        $('#vDeliveryServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliveryServiceTitle_Default-error').remove();
        $('#DeliveryServiceTitle_Modal').modal('hide');
    }

    $('#saveDeliveryServiceSection').click(function() {
        var vDeliveryServiceTitleArr = $('[name^="vDeliveryServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vDeliveryServiceTitleArr, function(key, value) {
            if(value.name != "vDeliveryServiceTitle_Default") {
                var name_key = value.name.replace('vDeliveryServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'DeliveryServices';
        saveHomeScreenData('saveDeliveryServiceSection', postData, 'No');
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

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['vSubTitleArr'] = vSubTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'UberX';
        saveHomeScreenData('saveOnDemandServiceSection', postData, 'No');
    });

    function editVideoConsultTitle(action) {
        $('#videoconsult_title_modal_action').html(action);
        $('#VideoConsultTitle_Modal').modal('show');
    }

    function saveVideoConsultTitle() {
        if ($('#vVideoConsultTitle_<?= $default_lang ?>').val() == "") {
            $('#vVideoConsultTitle_<?= $default_lang ?>_error').show();
            $('#vVideoConsultTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vVideoConsultTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vVideoConsultTitle_Default').val($('#vVideoConsultTitle_<?= $default_lang ?>').val());
        $('#vVideoConsultTitle_Default').closest('.row').removeClass('has-error');
        $('#vVideoConsultTitle_Default-error').remove();
        $('#VideoConsultTitle_Modal').modal('hide');
    }

    function editVideoConsultSubTitle(action) {
        $('#videoconsult_subtitle_modal_action').html(action);
        $('#VideoConsultSubTitle_Modal').modal('show');
    }

    function saveVideoConsultSubTitle() {
        if ($('#vVideoConsultSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vVideoConsultSubTitle_<?= $default_lang ?>_error').show();
            $('#vVideoConsultSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vVideoConsultSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vVideoConsultSubTitle_Default').val($('#vVideoConsultSubTitle_<?= $default_lang ?>').val());
        $('#vVideoConsultSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vVideoConsultSubTitle_Default-error').remove();
        $('#VideoConsultSubTitle_Modal').modal('hide');
    }

    $('#saveVideoConsultSection').click(function() {
        var vVideoConsultTitleArr = $('[name^="vVideoConsultTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vVideoConsultTitleArr, function(key, value) {
            if(value.name != "vVideoConsultTitle_Default") {
                var name_key = value.name.replace('vVideoConsultTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vVideoConsultSubTitleArr = $('[name^="vVideoConsultSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vVideoConsultSubTitleArr, function(key, value) {
            if(value.name != "vVideoConsultSubTitle_Default") {
                var name_key = value.name.replace('vVideoConsultSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['vSubTitleArr'] = vSubTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'VideoConsult';
        saveHomeScreenData('saveVideoConsultSection', postData, 'No');
    });

    function editBiddingTitle(action) {
        $('#bidding_title_modal_action').html(action);
        $('#BiddingTitle_Modal').modal('show');
    }

    function saveBiddingTitle() {
        if ($('#vBiddingTitle_<?= $default_lang ?>').val() == "") {
            $('#vBiddingTitle_<?= $default_lang ?>_error').show();
            $('#vBiddingTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBiddingTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBiddingTitle_Default').val($('#vBiddingTitle_<?= $default_lang ?>').val());
        $('#vBiddingTitle_Default').closest('.row').removeClass('has-error');
        $('#vBiddingTitle_Default-error').remove();
        $('#BiddingTitle_Modal').modal('hide');
    }

    function editBiddingSubTitle(action) {
        $('#bidding_subtitle_modal_action').html(action);
        $('#BiddingSubTitle_Modal').modal('show');
    }

    function saveBiddingSubTitle() {
        if ($('#vBiddingSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vBiddingSubTitle_<?= $default_lang ?>_error').show();
            $('#vBiddingSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBiddingSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBiddingSubTitle_Default').val($('#vBiddingSubTitle_<?= $default_lang ?>').val());
        $('#vBiddingSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vBiddingSubTitle_Default-error').remove();
        $('#BiddingSubTitle_Modal').modal('hide');
    }

    $('#saveBiddingSection').click(function() {
        var vBiddingTitleArr = $('[name^="vBiddingTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vBiddingTitleArr, function(key, value) {
            if(value.name != "vBiddingTitle_Default") {
                var name_key = value.name.replace('vBiddingTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vBiddingSubTitleArr = $('[name^="vBiddingSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vBiddingSubTitleArr, function(key, value) {
            if(value.name != "vBiddingSubTitle_Default") {
                var name_key = value.name.replace('vBiddingSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['vSubTitleArr'] = vSubTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Bidding';
        saveHomeScreenData('saveBiddingSection', postData, 'No');
    });

    
    function editBSRTitle(action) {
        $('#bsr_title_modal_action').html(action);
        $('#BSRTitle_Modal').modal('show');
    }

    function saveBSRTitle() {
        if ($('#vBSRTitle_<?= $default_lang ?>').val() == "") {
            $('#vBSRTitle_<?= $default_lang ?>_error').show();
            $('#vBSRTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBSRTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBSRTitle_Default').val($('#vBSRTitle_<?= $default_lang ?>').val());
        $('#vBSRTitle_Default').closest('.row').removeClass('has-error');
        $('#vBSRTitle_Default-error').remove();
        $('#BSRTitle_Modal').modal('hide');
    }

    $('#saveBSRTitleSection').click(function() {
        var vBSRTitleArr = $('[name^="vBSRTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vBSRTitleArr, function(key, value) {
            if(value.name != "vBSRTitle_Default") {
                var name_key = value.name.replace('vBSRTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'BuySellRent';
        saveHomeScreenData('saveBSRTitleSection', postData, 'No');
    });

    function editRentEstateTitle(action) {
        $('#rentestate_title_modal_action').html(action);
        $('#RentEstateTitle_Modal').modal('show');
    }

    function saveRentEstateTitle() {
        if ($('#vRentEstateTitle_<?= $default_lang ?>').val() == "") {
            $('#vRentEstateTitle_<?= $default_lang ?>_error').show();
            $('#vRentEstateTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentEstateTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRentEstateTitle_Default').val($('#vRentEstateTitle_<?= $default_lang ?>').val());
        $('#vRentEstateTitle_Default').closest('.row').removeClass('has-error');
        $('#vRentEstateTitle_Default-error').remove();
        $('#RentEstateTitle_Modal').modal('hide');
    }

    function editRentEstateSubTitle(action) {
        $('#rentestate_subtitle_modal_action').html(action);
        $('#RentEstateSubTitle_Modal').modal('show');
    }

    function saveRentEstateSubTitle() {
        if ($('#vRentEstateSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vRentEstateSubTitle_<?= $default_lang ?>_error').show();
            $('#vRentEstateSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentEstateSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRentEstateSubTitle_Default').val($('#vRentEstateSubTitle_<?= $default_lang ?>').val());
        $('#vRentEstateSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vRentEstateSubTitle_Default-error').remove();
        $('#RentEstateSubTitle_Modal').modal('hide');
    }

    $('#saveRentEstateSection').click(function() {
        var vRentEstateTitleArr = $('[name^="vRentEstateTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRentEstateTitleArr, function(key, value) {
            if(value.name != "vRentEstateTitle_Default") {
                var name_key = value.name.replace('vRentEstateTitle', 'vCategoryName');
                vTitleArr[name_key] = value.value;
            }
        });

        var vRentEstateSubTitleArr = $('[name^="vRentEstateSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vRentEstateSubTitleArr, function(key, value) {
            if(value.name != "vRentEstateSubTitle_Default") {
                var name_key = value.name.replace('vRentEstateSubTitle', 'vCategoryDesc');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var vImageRentEstate = $('#vImageRentEstate')[0].files[0];
        var vImageOldRentEstate = $('#vImageOldRentEstate').val();
        var vTitleColorRentEstate = $('#vTitleColorRentEstate').val();
        var vBgColorRentEstate = $('#vBgColorRentEstate').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageRentEstate);
        postData.append('vImageOld', vImageOldRentEstate);
        postData.append('vTxtTitleColor', vTitleColorRentEstate);
        postData.append('vBgColor', vBgColorRentEstate);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'RentEstate');

        saveHomeScreenData('saveRentEstateSection', postData);
    });

    function editRentCarsTitle(action) {
        $('#rentcars_title_modal_action').html(action);
        $('#RentCarsTitle_Modal').modal('show');
    }

    function saveRentCarsTitle() {
        if ($('#vRentCarsTitle_<?= $default_lang ?>').val() == "") {
            $('#vRentCarsTitle_<?= $default_lang ?>_error').show();
            $('#vRentCarsTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentCarsTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRentCarsTitle_Default').val($('#vRentCarsTitle_<?= $default_lang ?>').val());
        $('#vRentCarsTitle_Default').closest('.row').removeClass('has-error');
        $('#vRentCarsTitle_Default-error').remove();
        $('#RentCarsTitle_Modal').modal('hide');
    }

    function editRentCarsSubTitle(action) {
        $('#rentcars_subtitle_modal_action').html(action);
        $('#RentCarsSubTitle_Modal').modal('show');
    }

    function saveRentCarsSubTitle() {
        if ($('#vRentCarsSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vRentCarsSubTitle_<?= $default_lang ?>_error').show();
            $('#vRentCarsSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentCarsSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRentCarsSubTitle_Default').val($('#vRentCarsSubTitle_<?= $default_lang ?>').val());
        $('#vRentCarsSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vRentCarsSubTitle_Default-error').remove();
        $('#RentCarsSubTitle_Modal').modal('hide');
    }

    $('#saveRentCarsSection').click(function() {
        var vRentCarsTitleArr = $('[name^="vRentCarsTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRentCarsTitleArr, function(key, value) {
            if(value.name != "vRentCarsTitle_Default") {
                var name_key = value.name.replace('vRentCarsTitle', 'vCategoryName');
                vTitleArr[name_key] = value.value;
            }
        });

        var vRentCarsSubTitleArr = $('[name^="vRentCarsSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vRentCarsSubTitleArr, function(key, value) {
            if(value.name != "vRentCarsSubTitle_Default") {
                var name_key = value.name.replace('vRentCarsSubTitle', 'vCategoryDesc');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var vImageRentCars = $('#vImageRentCars')[0].files[0];
        var vImageOldRentCars = $('#vImageOldRentCars').val();
        var vTitleColorRentCars = $('#vTitleColorRentCars').val();
        var vBgColorRentCars = $('#vBgColorRentCars').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageRentCars);
        postData.append('vImageOld', vImageOldRentCars);
        postData.append('vTxtTitleColor', vTitleColorRentCars);
        postData.append('vBgColor', vBgColorRentCars);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'RentCars');

        saveHomeScreenData('saveRentCarsSection', postData);
    });

    function editRentItemTitle(action) {
        $('#rentitem_title_modal_action').html(action);
        $('#RentItemTitle_Modal').modal('show');
    }

    function saveRentItemTitle() {
        if ($('#vRentItemTitle_<?= $default_lang ?>').val() == "") {
            $('#vRentItemTitle_<?= $default_lang ?>_error').show();
            $('#vRentItemTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentItemTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRentItemTitle_Default').val($('#vRentItemTitle_<?= $default_lang ?>').val());
        $('#vRentItemTitle_Default').closest('.row').removeClass('has-error');
        $('#vRentItemTitle_Default-error').remove();
        $('#RentItemTitle_Modal').modal('hide');
    }

    function editRentItemSubTitle(action) {
        $('#rentitem_subtitle_modal_action').html(action);
        $('#RentItemSubTitle_Modal').modal('show');
    }

    function saveRentItemSubTitle() {
        if ($('#vRentItemSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vRentItemSubTitle_<?= $default_lang ?>_error').show();
            $('#vRentItemSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRentItemSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRentItemSubTitle_Default').val($('#vRentItemSubTitle_<?= $default_lang ?>').val());
        $('#vRentItemSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vRentItemSubTitle_Default-error').remove();
        $('#RentItemSubTitle_Modal').modal('hide');
    }

    $('#saveRentItemSection').click(function() {
        var vRentItemTitleArr = $('[name^="vRentItemTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRentItemTitleArr, function(key, value) {
            if(value.name != "vRentItemTitle_Default") {
                var name_key = value.name.replace('vRentItemTitle', 'vCategoryName');
                vTitleArr[name_key] = value.value;
            }
        });

        var vRentItemSubTitleArr = $('[name^="vRentItemSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vRentItemSubTitleArr, function(key, value) {
            if(value.name != "vRentItemSubTitle_Default") {
                var name_key = value.name.replace('vRentItemSubTitle', 'vCategoryDesc');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var vImageRentItem = $('#vImageRentItem')[0].files[0];
        var vImageOldRentItem = $('#vImageOldRentItem').val();
        var vTitleColorRentItem = $('#vTitleColorRentItem').val();
        var vBgColorRentItem = $('#vBgColorRentItem').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageRentItem);
        postData.append('vImageOld', vImageOldRentItem);
        postData.append('vTxtTitleColor', vTitleColorRentItem);
        postData.append('vBgColor', vBgColorRentItem);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'RentItem');

        saveHomeScreenData('saveRentItemSection', postData);
    });

    function editRideShareTitle(action) {
        $('#rideshare_title_modal_action').html(action);
        $('#RideShareTitle_Modal').modal('show');
    }

    function saveRideShareTitle() {
        if ($('#vRideShareTitle_<?= $default_lang ?>').val() == "") {
            $('#vRideShareTitle_<?= $default_lang ?>_error').show();
            $('#vRideShareTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRideShareTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRideShareTitle_Default').val($('#vRideShareTitle_<?= $default_lang ?>').val());
        $('#vRideShareTitle_Default').closest('.row').removeClass('has-error');
        $('#vRideShareTitle_Default-error').remove();
        $('#RideShareTitle_Modal').modal('hide');
    }

    function editRideShareSubTitle(action) {
        $('#rideshare_subtitle_modal_action').html(action);
        $('#RideShareSubTitle_Modal').modal('show');
    }

    function saveRideShareSubTitle() {
        if ($('#vRideShareSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vRideShareSubTitle_<?= $default_lang ?>_error').show();
            $('#vRideShareSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vRideShareSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vRideShareSubTitle_Default').val($('#vRideShareSubTitle_<?= $default_lang ?>').val());
        $('#vRideShareSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vRideShareSubTitle_Default-error').remove();
        $('#RideShareSubTitle_Modal').modal('hide');
    }

    $('#saveRideShareSection').click(function() {
        var vRideShareTitleArr = $('[name^="vRideShareTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRideShareTitleArr, function(key, value) {
            if(value.name != "vRideShareTitle_Default") {
                var name_key = value.name.replace('vRideShareTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vRideShareSubTitleArr = $('[name^="vRideShareSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vRideShareSubTitleArr, function(key, value) {
            if(value.name != "vRideShareSubTitle_Default") {
                var name_key = value.name.replace('vRideShareSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });
        var vImageRideShare = $('#vImageRideShare')[0].files[0];
        var vImageOldRideShare = $('#vImageOldRideShare').val();
        var vTitleColorRideShare = $('#vTitleColorRideShare').val();
        var vSubTitleColorRideShare = $('#vSubTitleColorRideShare').val();
        var vBgColorRideShare = $('#vBgColorRideShare').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageRideShare);
        postData.append('vImageOld', vImageOldRideShare);
        postData.append('vTxtTitleColor', vTitleColorRideShare);
        postData.append('vTxtSubTitleColor', vSubTitleColorRideShare);
        postData.append('vBgColor', vBgColorRideShare);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'RideShare');

        saveHomeScreenData('saveRideShareSection', postData);
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


    function editMSTitle(action) {
        $('#ms_title_modal_action').html(action);
        $('#MSTitle_Modal').modal('show');
    }

    function saveMSTitle() {
        if ($('#vMSTitle_<?= $default_lang ?>').val() == "") {
            $('#vMSTitle_<?= $default_lang ?>_error').show();
            $('#vMSTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vMSTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vMSTitle_Default').val($('#vMSTitle_<?= $default_lang ?>').val());
        $('#vMSTitle_Default').closest('.row').removeClass('has-error');
        $('#vMSTitle_Default-error').remove();
        $('#MSTitle_Modal').modal('hide');
    }

    $('#saveMSTitleSection').click(function() {
        var vMSTitleArr = $('[name^="vMSTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vMSTitleArr, function(key, value) {
            if(value.name != "vMSTitle_Default") {
                var name_key = value.name.replace('vMSTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'MedicalServices';
        saveHomeScreenData('saveMSTitleSection', postData, 'No');
    });

    function editBookServiceMSTitle(action) {
        $('#bookservicems_title_modal_action').html(action);
        $('#BookServiceMSTitle_Modal').modal('show');
    }

    function saveBookServiceMSTitle() {
        if ($('#vBookServiceMSTitle_<?= $default_lang ?>').val() == "") {
            $('#vBookServiceMSTitle_<?= $default_lang ?>_error').show();
            $('#vBookServiceMSTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBookServiceMSTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBookServiceMSTitle_Default').val($('#vBookServiceMSTitle_<?= $default_lang ?>').val());
        $('#vBookServiceMSTitle_Default').closest('.row').removeClass('has-error');
        $('#vBookServiceMSTitle_Default-error').remove();
        $('#BookServiceMSTitle_Modal').modal('hide');
    }

    function editBookServiceMSSubTitle(action) {
        $('#bookservicems_subtitle_modal_action').html(action);
        $('#BookServiceMSSubTitle_Modal').modal('show');
    }

    function saveBookServiceMSSubTitle() {
        if ($('#vBookServiceMSSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vBookServiceMSSubTitle_<?= $default_lang ?>_error').show();
            $('#vBookServiceMSSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBookServiceMSSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBookServiceMSSubTitle_Default').val($('#vBookServiceMSSubTitle_<?= $default_lang ?>').val());
        $('#vBookServiceMSSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vBookServiceMSSubTitle_Default-error').remove();
        $('#BookServiceMSSubTitle_Modal').modal('hide');
    }

    $('#saveBookServiceMSSection').click(function() {
        var vBookServiceMSTitleArr = $('[name^="vBookServiceMSTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vBookServiceMSTitleArr, function(key, value) {
            if(value.name != "vBookServiceMSTitle_Default") {
                var name_key = value.name.replace('vBookServiceMSTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vBookServiceMSSubTitleArr = $('[name^="vBookServiceMSSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vBookServiceMSSubTitleArr, function(key, value) {
            if(value.name != "vBookServiceMSSubTitle_Default") {
                var name_key = value.name.replace('vBookServiceMSSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['vSubTitleArr'] = vSubTitleArr;
        postData['ViewType'] = 'TextBannerGridView';//'ListBtnView';
        postData['ServiceType'] = 'MedicalServices';
        postData['ServiceTypeMS'] = 'BookService';
        saveHomeScreenData('saveBookServiceMSSection', postData, 'No');
    });

    function editVideoConsultMSTitle(action) {
        $('#videoconsultms_title_modal_action').html(action);
        $('#VideoConsultMSTitle_Modal').modal('show');
    }

    function saveVideoConsultMSTitle() {
        if ($('#vVideoConsultMSTitle_<?= $default_lang ?>').val() == "") {
            $('#vVideoConsultMSTitle_<?= $default_lang ?>_error').show();
            $('#vVideoConsultMSTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vVideoConsultMSTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vVideoConsultMSTitle_Default').val($('#vVideoConsultMSTitle_<?= $default_lang ?>').val());
        $('#vVideoConsultMSTitle_Default').closest('.row').removeClass('has-error');
        $('#vVideoConsultMSTitle_Default-error').remove();
        $('#VideoConsultMSTitle_Modal').modal('hide');
    }

    function editVideoConsultMSSubTitle(action) {
        $('#videoconsultms_subtitle_modal_action').html(action);
        $('#VideoConsultMSSubTitle_Modal').modal('show');
    }

    function saveVideoConsultMSSubTitle() {
        if ($('#vVideoConsultMSSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vVideoConsultMSSubTitle_<?= $default_lang ?>_error').show();
            $('#vVideoConsultMSSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vVideoConsultMSSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vVideoConsultMSSubTitle_Default').val($('#vVideoConsultMSSubTitle_<?= $default_lang ?>').val());
        $('#vVideoConsultMSSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vVideoConsultMSSubTitle_Default-error').remove();
        $('#VideoConsultMSSubTitle_Modal').modal('hide');
    }

    $('#saveVideoConsultMSSection').click(function() {
        var vVideoConsultMSTitleArr = $('[name^="vVideoConsultMSTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vVideoConsultMSTitleArr, function(key, value) {
            if(value.name != "vVideoConsultMSTitle_Default") {
                var name_key = value.name.replace('vVideoConsultMSTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vVideoConsultMSSubTitleArr = $('[name^="vVideoConsultMSSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vVideoConsultMSSubTitleArr, function(key, value) {
            if(value.name != "vVideoConsultMSSubTitle_Default") {
                var name_key = value.name.replace('vVideoConsultMSSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['vSubTitleArr'] = vSubTitleArr;
        postData['ViewType'] = 'TextBannerGridView';//'ListBtnView';
        postData['ServiceType'] = 'MedicalServices';
        postData['ServiceTypeMS'] = 'VideoConsult';
        saveHomeScreenData('saveVideoConsultMSSection', postData, 'No');
    });

    function editMoreServiceMSTitle(action) {
        $('#moreservicems_title_modal_action').html(action);
        $('#MoreServiceMSTitle_Modal').modal('show');
    }

    function saveMoreServiceMSTitle() {
        if ($('#vMoreServiceMSTitle_<?= $default_lang ?>').val() == "") {
            $('#vMoreServiceMSTitle_<?= $default_lang ?>_error').show();
            $('#vMoreServiceMSTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vMoreServiceMSTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vMoreServiceMSTitle_Default').val($('#vMoreServiceMSTitle_<?= $default_lang ?>').val());
        $('#vMoreServiceMSTitle_Default').closest('.row').removeClass('has-error');
        $('#vMoreServiceMSTitle_Default-error').remove();
        $('#MoreServiceMSTitle_Modal').modal('hide');
    }

    function editMoreServiceMSSubTitle(action) {
        $('#moreservicems_subtitle_modal_action').html(action);
        $('#MoreServiceMSSubTitle_Modal').modal('show');
    }

    function saveMoreServiceMSSubTitle() {
        if ($('#vMoreServiceMSSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vMoreServiceMSSubTitle_<?= $default_lang ?>_error').show();
            $('#vMoreServiceMSSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vMoreServiceMSSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vMoreServiceMSSubTitle_Default').val($('#vMoreServiceMSSubTitle_<?= $default_lang ?>').val());
        $('#vMoreServiceMSSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vMoreServiceMSSubTitle_Default-error').remove();
        $('#MoreServiceMSSubTitle_Modal').modal('hide');
    }

    $('#saveMoreServiceMSSection').click(function() {
        var vMoreServiceMSTitleArr = $('[name^="vMoreServiceMSTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vMoreServiceMSTitleArr, function(key, value) {
            if(value.name != "vMoreServiceMSTitle_Default") {
                var name_key = value.name.replace('vMoreServiceMSTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vMoreServiceMSSubTitleArr = $('[name^="vMoreServiceMSSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vMoreServiceMSSubTitleArr, function(key, value) {
            if(value.name != "vMoreServiceMSSubTitle_Default") {
                var name_key = value.name.replace('vMoreServiceMSSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['vSubTitleArr'] = vSubTitleArr;
        postData['ViewType'] = 'TextBannerGridView';//'ListBtnView';
        postData['ServiceType'] = 'MedicalServices';
        postData['ServiceTypeMS'] = 'MoreService';
        saveHomeScreenData('saveMoreServiceMSSection', postData, 'No');
    });

    function editTrackServiceTitle(action) {
        $('#trackservice_title_modal_action').html(action);
        $('#TrackServiceTitle_Modal').modal('show');
    }

    function saveTrackServiceTitle() {
        if ($('#vTrackServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vTrackServiceTitle_<?= $default_lang ?>_error').show();
            $('#vTrackServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vTrackServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vTrackServiceTitle_Default').val($('#vTrackServiceTitle_<?= $default_lang ?>').val());
        $('#vTrackServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vTrackServiceTitle_Default-error').remove();
        $('#TrackServiceTitle_Modal').modal('hide');
    }

    $('#saveTrackServiceTitleSection').click(function() {
        var vTrackServiceTitleArr = $('[name^="vTrackServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vTrackServiceTitleArr, function(key, value) {
            if(value.name != "vTrackServiceTitle_Default") {
                var name_key = value.name.replace('vTrackServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'TrackAnyService';
        saveHomeScreenData('saveTrackServiceTitleSection', postData, 'No');
    });

    <?php foreach ($TrackServiceDataArr as $TrackService) { ?>
        function edit<?= $TrackService['MemberTypeKey'] ?>Title(action) {
            $('#<?= strtolower($TrackService['MemberTypeKey']) ?>_title_modal_action').html(action);
            $('#<?= $TrackService['MemberTypeKey'] ?>Title_Modal').modal('show');
        }

        function save<?= $TrackService['MemberTypeKey'] ?>Title() {
            if ($('#v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>').val() == "") {
                $('#v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>_error').show();
                $('#v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function () {
                    $('#v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>_error').hide();
                }, 5000);
                return false;
            }

            $('#v<?= $TrackService['MemberTypeKey'] ?>Title_Default').val($('#v<?= $TrackService['MemberTypeKey'] ?>Title_<?= $default_lang ?>').val());
            $('#v<?= $TrackService['MemberTypeKey'] ?>Title_Default').closest('.row').removeClass('has-error');
            $('#v<?= $TrackService['MemberTypeKey'] ?>Title_Default-error').remove();
            $('#<?= $TrackService['MemberTypeKey'] ?>Title_Modal').modal('hide');
        }

        function edit<?= $TrackService['MemberTypeKey'] ?>SubTitle(action) {
            $('#<?= strtolower($TrackService['MemberTypeKey']) ?>_subtitle_modal_action').html(action);
            $('#<?= $TrackService['MemberTypeKey'] ?>SubTitle_Modal').modal('show');
        }

        function save<?= $TrackService['MemberTypeKey'] ?>SubTitle() {
            if ($('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>').val() == "") {
                $('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>_error').show();
                $('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function () {
                    $('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>_error').hide();
                }, 5000);
                return false;
            }

            $('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_Default').val($('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_<?= $default_lang ?>').val());
            $('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_Default').closest('.row').removeClass('has-error');
            $('#v<?= $TrackService['MemberTypeKey'] ?>SubTitle_Default-error').remove();
            $('#<?= $TrackService['MemberTypeKey'] ?>SubTitle_Modal').modal('hide');
        }

        $('#save<?= $TrackService['MemberTypeKey'] ?>Section').click(function() {
            var v<?= $TrackService['MemberTypeKey'] ?>TitleArr = $('[name^="v<?= $TrackService['MemberTypeKey'] ?>Title_"]').serializeArray();
            var vTitleArr = {};
            $.each(v<?= $TrackService['MemberTypeKey'] ?>TitleArr, function(key, value) {
                if(value.name != "v<?= $TrackService['MemberTypeKey'] ?>Title_Default") {
                    var name_key = value.name.replace('v<?= $TrackService['MemberTypeKey'] ?>Title', 'vTitle');
                    vTitleArr[name_key] = value.value;
                }
            });

            var v<?= $TrackService['MemberTypeKey'] ?>SubTitleArr = $('[name^="v<?= $TrackService['MemberTypeKey'] ?>SubTitle_"]').serializeArray();
            var vSubTitleArr = {};
            $.each(v<?= $TrackService['MemberTypeKey'] ?>SubTitleArr, function(key, value) {
                if(value.name != "v<?= $TrackService['MemberTypeKey'] ?>SubTitle_Default") {
                    var name_key = value.name.replace('v<?= $TrackService['MemberTypeKey'] ?>SubTitle', 'vSubtitle');
                    vSubTitleArr[name_key] = value.value;
                }
            });
            var vImage<?= $TrackService['MemberTypeKey'] ?> = $('#vImage<?= $TrackService['MemberTypeKey'] ?>')[0].files[0];
            var vImageOld<?= $TrackService['MemberTypeKey'] ?> = $('#vImageOld<?= $TrackService['MemberTypeKey'] ?>').val();
            var vTitleColor<?= $TrackService['MemberTypeKey'] ?> = $('#vTitleColor<?= $TrackService['MemberTypeKey'] ?>').val();
            var vBgColor<?= $TrackService['MemberTypeKey'] ?> = $('#vBgColor<?= $TrackService['MemberTypeKey'] ?>').val();

            var postData = new FormData();
            postData.append('vTitleArr', JSON.stringify(vTitleArr));
            postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
            postData.append('vImage', vImage<?= $TrackService['MemberTypeKey'] ?>);
            postData.append('vImageOld', vImageOld<?= $TrackService['MemberTypeKey'] ?>);
            postData.append('vTxtTitleColor', vTitleColor<?= $TrackService['MemberTypeKey'] ?>);
            postData.append('vBgColor', vBgColor<?= $TrackService['MemberTypeKey'] ?>);
            postData.append('ViewType', 'TextBannerView');
            postData.append('ServiceType', 'Track<?= $TrackService['MemberTypeKey'] ?>');
            postData.append('ServiceTypeOther', '<?= $TrackService['eMemberType'] ?>');

            saveHomeScreenData('save<?= $TrackService['MemberTypeKey'] ?>Section', postData);
        });
    <?php } ?>

    function editNearbyServiceTitle(action) {
        $('#nearbyservice_title_modal_action').html(action);
        $('#NearbyServiceTitle_Modal').modal('show');
    }

    function saveNearbyServiceTitle() {
        if ($('#vNearbyServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#vNearbyServiceTitle_<?= $default_lang ?>_error').show();
            $('#vNearbyServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vNearbyServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vNearbyServiceTitle_Default').val($('#vNearbyServiceTitle_<?= $default_lang ?>').val());
        $('#vNearbyServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#vNearbyServiceTitle_Default-error').remove();
        $('#NearbyServiceTitle_Modal').modal('hide');
    }

    $('#saveNearbyServiceSection').click(function() {
        var vNearbyServiceTitleArr = $('[name^="vNearbyServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vNearbyServiceTitleArr, function(key, value) {
            if(value.name != "vNearbyServiceTitle_Default") {
                var name_key = value.name.replace('vNearbyServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'NearBy';
        saveHomeScreenData('saveNearbyServiceSection', postData, 'No');
    });

    function saveDisplayOrderService(ServiceType) {
        var iDisplayOrderArr = {};

        if(ServiceType == "Ride") {
            var DisplayOrderElem = $('[name^="iDisplayOrderTaxiServiceArr"]');
        } else if(ServiceType == "Delivery") {
            var DisplayOrderElem = $('[name^="iDisplayOrderDeliveryServiceArr"]');
        } else if(ServiceType == "UberX") {
            var DisplayOrderElem = $('[name^="iDisplayOrderUberXServiceArr"]');
        } else if(ServiceType == "Bidding") {
            var DisplayOrderElem = $('[name^="iDisplayOrderBidServiceArr"]');
        } else if(ServiceType == "NearBy") {
            var DisplayOrderElem = $('[name^="iDisplayOrderNearByServiceArr"]');
        } else if(ServiceType == "VideoConsult") {
            var DisplayOrderElem = $('[name^="iDisplayOrderVCServiceArr"]');
        }

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
             $('#loaderIcon').hide();
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
                    var new_mes = responseData.message.replace("<br>"," ");
                    console.log(new_mes);
                    //show_alert("", responseData.message, "", "Ok", "", function (btn_id) {}, true, true, true);
                }
            }
            else {
                $('#' + saveBtnId).find('i').remove();
                $('#' + saveBtnId).append(' <i class="fa fa-times"></i>');
                setTimeout(function() {
                    $('#' + saveBtnId).find('i').remove();
                }, 3000);
                alert("Something went wrong.");
               // show_alert("", "Something went wrong.", "", "Ok", "", function (btn_id) {}, true, true, true);
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

    $('.redirect-url').click(function() {
        window.open($(this).data('link-target'), '_blank');
    });
</script>
</body>
<!-- END BODY-->
</html>