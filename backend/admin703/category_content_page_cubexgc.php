<?php
include_once('../common.php');
include_once('ajax_category_content_page.php');
$iLanguageMasId = $id = $_REQUEST['id'] ?? '';
if(empty($id)){
    $sql = "SELECT iLanguageMasId FROM language_master WHERE vCode = '" . $default_lang . "'";
    $language_master = $obj->MySQLSelect($sql);
    $iLanguageMasId = $id = $language_master[0]['iLanguageMasId'];
}
$sql = "SELECT vCode,vTitle FROM language_master WHERE iLanguageMasId = '" . $id . "'";
$db_data = $obj->MySQLSelect($sql);
$vCode = $db_data[0]['vCode'];
$title = $db_data[0]['vTitle'];
$sectionType = $_POST['Type'] ?? '';
$tbl_name = getAppTypeWiseHomeTable();

$script = 'ServiceSection';

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
$count_all = count($db_master);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$labelsOther = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_HOME_PAGE_TAB_USER_TXT', 'LBL_HOME_PAGE_TAB_DRIVER_TXT', 'LBL_HOME_PAGE_TAB_STORE_TXT', 'LBL_HOME_PAGE_TAB_COMPANY_TXT', 'LBL_HOME_PAGE_TAB_TAXI_SERVICE_TXT', 'LBL_HOME_PAGE_TAB_DELIVERY_SERVICE_TXT', 'LBL_HOME_PAGE_TAB_OTHER_SERVICE_TXT', 'LBL_HOME_PAGE_TAB_EARN_TXT', 'LBL_HOME_PAGE_TAB_STORE_BUSINESS_TXT', 'LBL_HOME_PAGE_TAB_COMPANY_BUSINESS_TXT') ");

$UserTabTitleArr = $DriverTabTitleArr = $StoreTabTitleArr = $CompanyTabTitleArr = $TaxiServiceTitleArr = $DeliveryServiceTitleArr = $OtherServiceTitleArr = $DriverServiceTitleArr = $StoreServiceTitleArr = $CompanyServiceTitleArr = array();
foreach ($labelsOther as $label) {
    if($label['vLabel'] == 'LBL_HOME_PAGE_TAB_USER_TXT') {
        $UserTabTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_DRIVER_TXT') {
        $DriverTabTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_STORE_TXT') {
        $StoreTabTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_COMPANY_TXT') {
        $CompanyTabTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_TAXI_SERVICE_TXT') {
        $TaxiServiceTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_DELIVERY_SERVICE_TXT') {
        $DeliveryServiceTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_OTHER_SERVICE_TXT') {
        $OtherServiceTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_EARN_TXT') {
        $DriverServiceTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_STORE_BUSINESS_TXT') {
        $StoreServiceTitleArr[$label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_HOME_PAGE_TAB_COMPANY_BUSINESS_TXT') {
        $CompanyServiceTitleArr[$label['vCode']] = $label['vValue'];

    }
}

$vcatdata_first = getSeviceCategoryDataForHomepage([], 0, 1, 'Yes');
$vcatdata_sec = getSeviceCategoryDataForHomepage([], 1, 1, 'Yes');
$vcatdata_main = array_merge($vcatdata_first, $vcatdata_sec);
$vcatdata_main = array_unique($vcatdata_main, SORT_REGULAR);

$sort_order = array_column($vcatdata_main, 'iDisplayOrderHomepage');
array_multisort($sort_order, SORT_ASC, $vcatdata_main);

$table_name = getContentCMSHomeTable();
$content_cat_data = $obj->MySQLSelect("SELECT GROUP_CONCAT(iVehicleCategoryId) as iVehicleCategoryIds FROM $table_name WHERE eShowHomePage = 'Yes'");
$content_cat_ids = explode(",", $content_cat_data[0]['iVehicleCategoryIds']);

$TaxiServiceArr = $DeliveryServiceArr = $UserServiceArr = $DriverServiceArr = $StoreServiceArr = $CompanyServiceArr = array();
foreach ($vcatdata_main as $ServiceData) {
    $ServiceData['vServiceTitle'] = $ServiceData['vCatName'];
    $ServiceData['vServiceImage'] = $tconfig["tsite_upload_home_page_service_images"] . '/' . $ServiceData['vHomepageLogoOurServices'];
    $ServiceData['eShowHomePage'] = "No";
    $ServiceData['homepage_url'] = $tconfig["tsite_url_main_admin"] . "vehicle_category_action.php?id=" . $ServiceData['iVehicleCategoryId'] . "&homepage=1";
    $ServiceData['inner_page_url'] = $tconfig["tsite_url_main_admin"] . $ServiceData['adminurl'] . "&id=" . $iLanguageMasId;
    if(in_array($ServiceData['iVehicleCategoryId'], $content_cat_ids)) {
        $ServiceData['eShowHomePage'] = "Yes";
    }

    if(in_array($ServiceData['eCatType'], ['Ride', 'MotoRide', 'Rental', 'MotoRental', 'RidePool', 'RideSchedule', 'CorporateRide', 'RideSomeoneElse', 'TaxiBid', 'InterCity'])) {
        $TaxiServiceArr[$ServiceData['iVehicleCategoryId']] = $ServiceData;

    } elseif (in_array($ServiceData['eCatType'], ['DeliverAll', 'MoreDelivery', 'Genie', 'Runner', 'Anywhere']) && in_array($ServiceData['iVehicleCategoryId'], $content_cat_ids)) {
        $DeliveryServiceArr[$ServiceData['iVehicleCategoryId']] = $ServiceData;
    }

    if($ServiceData['eCatType'] == "MoreDelivery" || ($ServiceData['eCatType'] == "DeliverAll" && $ServiceData['iServiceId'] == "1") || $ServiceData['eCatType'] == "CorporateRide") {
        $CompanyServiceArr[] = $ServiceData;
    }
}
$content_data_other = $obj->MySQLSelect("SELECT id, eFor, eUserType, lBannerSection FROM $table_name WHERE eFor IN ('GiftCard', 'Payment', 'Reward', 'EarnDrive', 'EarnDeliver', 'EarnStore', 'Ads')");

foreach ($content_data_other as $service_content) {
    $lBannerSection = json_decode($service_content['lBannerSection'], true);
    $ServiceArr = array(
        'vServiceTitle' => $lBannerSection['vServiceTitle_' . $default_lang],
        'vServiceImage' => $tconfig["tsite_upload_home_page_service_images"] . '/' . $lBannerSection['vServiceImage'],
        'eFor' => $service_content['eFor'],
        'eUserType' => $service_content['eUserType'],
        'homepage_url' => $tconfig["tsite_url_main_admin"] . "otherservices_gc_content_action.php?id=" . $service_content['id'] . "&homepage=Yes",
        'inner_page_url' => $tconfig["tsite_url_main_admin"] . "otherservices_gc_content_action.php?id=" . $service_content['id'] . "&iLanguageMasId=" . $iLanguageMasId
    );    

    if($service_content['eUserType'] == "User" && in_array($service_content['eFor'], ['GiftCard', 'Payment', 'Reward'])) {
        $UserServiceArr[] = $ServiceArr;
    } elseif ($service_content['eUserType'] == "Driver" && in_array($service_content['eFor'], ['EarnDrive', 'EarnDeliver'])) {
        $DriverServiceArr[] = $ServiceArr;
    } elseif ($service_content['eUserType'] == "Store" && in_array($service_content['eFor'], ['EarnStore', 'Payment', 'EarnDeliver'])) {
        $StoreServiceArr[] = $ServiceArr;
    } elseif ($service_content['eUserType'] == "Company" && in_array($service_content['eFor'], ['GiftCard', 'Ads'])) {
        $CompanyServiceArr[] = $ServiceArr;
    }
}

$SERVICE_TABS_ARR = array(
    array(
        'TabTitle' => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'ServiceTitle' => $UserTabTitleArr, 
        'ServiceLabel' => 'LBL_HOME_PAGE_TAB_USER_TXT',
        'ServiceKey' => 'User',
        'display' => 'Yes',
        'ServiceData' => array(
            array(
                'ServiceTitle' => $TaxiServiceTitleArr,
                'ServiceLabel' => 'LBL_HOME_PAGE_TAB_TAXI_SERVICE_TXT',
                'ServiceKey' => 'Taxi',
                'Services' => $TaxiServiceArr
            ),
            array(
                'ServiceTitle' => $DeliveryServiceTitleArr,
                'ServiceLabel' => 'LBL_HOME_PAGE_TAB_DELIVERY_SERVICE_TXT',
                'ServiceKey' => 'Delivery',
                'Services' => $DeliveryServiceArr
            ),
            array(
                'ServiceTitle' => $OtherServiceTitleArr,
                'ServiceLabel' => 'LBL_HOME_PAGE_TAB_OTHER_SERVICE_TXT',
                'ServiceKey' => 'Other',
                'Services' => $UserServiceArr
            )           
        )
    ),
    array(
        'TabTitle' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
        'ServiceTitle' => $DriverTabTitleArr, 
        'ServiceLabel' => 'LBL_HOME_PAGE_TAB_DRIVER_TXT', 
        'ServiceKey' => 'Driver',
        'display' => 'No',
        'ServiceData' => array(
            array(
                'ServiceTitle' => $DriverServiceTitleArr,
                'ServiceLabel' => 'LBL_HOME_PAGE_TAB_EARN_TXT',
                'ServiceKey' => 'Earn',
                'Services' => $DriverServiceArr
            )            
        )
    ),
    array(
        'TabTitle' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
        'ServiceTitle' => $StoreTabTitleArr, 
        'ServiceLabel' => 'LBL_HOME_PAGE_TAB_STORE_TXT', 
        'ServiceKey' => 'Store',
        'display' => 'No',
        'ServiceData' => array(
            array(
                'ServiceTitle' => $StoreServiceTitleArr,
                'ServiceLabel' => 'LBL_HOME_PAGE_TAB_STORE_BUSINESS_TXT',
                'ServiceKey' => 'EarnStore',
                'Services' => $StoreServiceArr
            )            
        )
    ),
    array(
        'TabTitle' => $langage_lbl_admin['LBL_COMPANY_ADMIN_TXT'],
        'ServiceTitle' => $CompanyTabTitleArr, 
        'ServiceLabel' => 'LBL_HOME_PAGE_TAB_COMPANY_TXT',
        'ServiceKey' => 'Company',
        'display' => 'No',
        'ServiceData' => array(
            array(
                'ServiceTitle' => $CompanyServiceTitleArr,
                'ServiceLabel' => 'LBL_HOME_PAGE_TAB_COMPANY_BUSINESS_TXT',
                'ServiceKey' => 'Company',
                'Services' => $CompanyServiceArr
            )            
        )
    )
);
// echo "<pre>"; print_r($SERVICE_TABS_ARR); exit;

if ($sectionType == 'allTaxiservices') {
    echo AllTaxiService();
    exit;
}

if ($sectionType == 'Save') {
    echo TaxiServicesDisplayToHomePageForCubex();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        .service-title {
            padding: 10px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 5px;
            margin: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        hr.service-line {
            border: 1px solid;
            width: calc(100% - 20px);
            margin: 0 0 20px 10px;
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
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #ddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #ccc;
        }

        /* Style the tab content */
        .tabcontent {
            display: none;
        }

        .display-tab-content {
            display: block;
        }

        .toggle-list-inner .toggle-combo {
            padding: 21px 21px 14px;
        }

        .check-combo ul {
            padding-left: 14px;
        }
        .check-combo ul li {
            margin-bottom: 12px;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Service Home/Inner Page (<?php echo $title; ?>)</h2>
                    <a href="homepage_content.php?id=<?php echo $iLanguageMasId; ?>" class="back_link">
                        <input type="button" value="Back To Page" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <?php include('valid_msg.php'); ?>
            <div class="tab">
                <?php foreach ($SERVICE_TABS_ARR as $SERVICE_TAB) { ?>
                    <button class="tablinks manage-<?= strtolower($SERVICE_TAB['ServiceKey']) ?>-tab <?= $SERVICE_TAB['display'] == "Yes" ? "active" : ""; ?>" onclick="openTabContent(event, 'manage-<?= strtolower($SERVICE_TAB['ServiceKey']) ?>-content', 'tabcontent-memberservices')"><?= $SERVICE_TAB['TabTitle'] ?></button>
                <?php } ?>                
            </div>

            <?php foreach ($SERVICE_TABS_ARR as $SERVICE_TAB) { ?>
            <div class="body-div tabcontent tabcontent-memberservices <?= $SERVICE_TAB['display'] == "Yes" ? "display-tab-content" : ""; ?>" id="manage-<?= strtolower($SERVICE_TAB['ServiceKey']) ?>-content">
                <div class="form-group">
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <?php if (count($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Service Title - Tab</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_Default" name="v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_Default" value="<?= $SERVICE_TAB['ServiceTitle'][$default_lang]; ?>" data-originalvalue="<?= $SERVICE_TAB['ServiceTitle'][$default_lang]; ?>" readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editServiceLabel('Edit', '<?= $SERVICE_TAB['ServiceKey'] ?>')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="<?= strtolower($SERVICE_TAB['ServiceKey']) ?>tab_title_modal_action"></span>
                                                Service Title - Tab
                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_')">x</button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'v' . $SERVICE_TAB['ServiceKey'] . 'TabTitle_' . $vCode;
                                                $$vValue = $SERVICE_TAB['ServiceTitle'][$vCode];
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
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
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
                                                                            onClick="getAllLanguageCode('v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_', '<?= $default_lang ?>');">
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
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="saveServiceLabel('<?= $SERVICE_TAB['ServiceKey'] ?>')"><?= $langage_lbl['LBL_Save']; ?></button>

                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_<?= $default_lang ?>" name="v<?= $SERVICE_TAB['ServiceKey'] ?>TabTitle_<?= $default_lang ?>" value="<?= $SERVICE_TAB['ServiceTitle'][$default_lang]; ?>">
                                </div>
                            </div>
                            <?php } ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="save<?= $SERVICE_TAB['ServiceKey'] ?>TabTitleSection" onclick="saveServiceLabelSection('<?= $SERVICE_TAB['ServiceKey'] ?>', '<?= $SERVICE_TAB['ServiceLabel'] ?>')">Save</button>
                                </div>
                            </div>

                            <hr />
                            <?php foreach ($SERVICE_TAB['ServiceData'] as $sData) { ?>
                                <?php if (count($db_master) > 1) { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Service Title - <?= $SERVICE_TAB['ServiceKey'] != "User" ? $SERVICE_TAB['ServiceKey'] : $sData['ServiceKey'] ?></label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input type="text" class="form-control" id="v<?= $sData['ServiceKey'] ?>ServiceTitle_Default" name="v<?= $sData['ServiceKey'] ?>ServiceTitle_Default" value="<?= $sData['ServiceTitle'][$default_lang]; ?>" data-originalvalue="<?= $sData['ServiceTitle'][$default_lang]; ?>" readonly="readonly" required>
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editServiceTitle('Edit', '<?= $sData['ServiceKey'] ?>')">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="<?= $sData['ServiceKey'] ?>ServiceTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                         data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="<?= strtolower($sData['ServiceKey']) ?>service_title_modal_action"></span>
                                                        Title
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $sData['ServiceKey'] ?>ServiceTitle_')">x</button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'v' . $sData['ServiceKey'] . 'ServiceTitle_' . $vCode;
                                                        $$vValue = $sData['ServiceTitle'][$vCode];
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
                                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                                     style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (count($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('v<?= $sData['ServiceKey'] ?>ServiceTitle_', 'EN');">
                                                                                Convert To All Language
                                                                            </button>
                                                                        </div>
                                                                    <?php }
                                                                } else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('v<?= $sData['ServiceKey'] ?>ServiceTitle_', '<?= $default_lang ?>');">
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
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveServiceTitle('<?= $sData['ServiceKey'] ?>')"><?= $langage_lbl['LBL_Save']; ?></button>

                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'v<?= $sData['ServiceKey'] ?>ServiceTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                            <input type="text" class="form-control" id="v<?= $sData['ServiceKey'] ?>ServiceTitle_<?= $default_lang ?>" name="v<?= $sData['ServiceKey'] ?>ServiceTitle_<?= $default_lang ?>" value="<?= $sData['ServiceTitle'][$default_lang]; ?>">
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <button type="button" class="btn btn-primary save-section-btn" id="save<?= $sData['ServiceKey'] ?>ServiceSection" onclick="saveServiceTitleSection('<?= $sData['ServiceKey'] ?>', '<?= $sData['ServiceLabel'] ?>')">Save</button>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- <div class="col-lg-12"> -->
                                    <div class="col-lg-12" style="padding-bottom: 10px;">
                                        <label style="padding-top:20px">Services</label>
                                        <?php if($sData['ServiceKey'] == 'Taxi') { ?>
                                            <button type="button" class="add-btn" onclick="getTaxiServices()">Manage</button>
                                        <?php } ?>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="profile-earning">
                                            <div class="partation">
                                                <ul style="padding-left: 0px;" class="setings-list">
                                                    <?php foreach ($sData['Services'] as $Service) { ?>
                                                    <li>
                                                        <div class="toggle-list-inner">
                                                            <div class="toggle-combo">
                                                                <label>
                                                                    <div align="center">
                                                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=75&src=' . $Service['vServiceImage'] ?>">
                                                                    </div>
                                                                    <div style="margin: 0 0 0 25px;"><?= $Service['vServiceTitle'] ?></div>
                                                                </label>
                                                            </div>
                                                            <div class="check-combo">
                                                                <label>
                                                                    <ul>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a href="<?= $Service['homepage_url'] ?>" data-toggle="tooltip" title="" data-original-title="Edit">
                                                                                <img src="img/edit-new.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a target="_blank" href="<?= $Service['inner_page_url'] ?>" data-toggle="tooltip" title="" data-original-title="Edit Inner Page">
                                                                                <img src="img/edit-doc.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- </div> -->
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="modal fade" id="services_modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4>
                    Services - Home Page
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?= $iLanguageMasId; ?>">
                    <table class="table table-striped table-bordered table-hover"
                           id="service-table">
                        <thead>
                        <tr>
                            <th>Category</th>
                            <th>Display On Home page</th>
                        </tr>
                        </thead>
                        <tbody id="service-list"></tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer" style="text-align: left">
                <button type="button" class="btn btn-default" onclick="saveTaxiServices()">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
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

    function editServiceLabel(action, UserType) {
        $('#' + UserType + 'tab_title_modal_action').html(action);
        $('#' + UserType + 'TabTitle_Modal').modal('show');
    }
    function saveServiceLabel(UserType) {
        if ($('#v' + UserType + 'TabTitle_<?= $default_lang ?>').val() == "") {
            $('#v' + UserType + 'TabTitle_<?= $default_lang ?>_error').show();
            $('#v' + UserType + 'TabTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#v' + UserType + 'TabTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#v' + UserType + 'TabTitle_Default').val($('#v' + UserType + 'TabTitle_<?= $default_lang ?>').val());
        $('#v' + UserType + 'TabTitle_Default').closest('.row').removeClass('has-error');
        $('#v' + UserType + 'TabTitle_Default-error').remove();
        $('#' + UserType + 'TabTitle_Modal').modal('hide');
    }

    function saveServiceLabelSection(UserType, vLabel) {
        var vServiceLabelTitleArr = $('[name^="v' + UserType + 'TabTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vServiceLabelTitleArr, function(key, value) {
            if(value.name != "v" + UserType + "TabTitle_Default") {
                var name_key = value.name.replace('v' + UserType + 'TabTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'GeneralLabel';
        postData['vLangLabel'] = vLabel;
        saveHomeScreenData('save' + UserType + 'TabTitleSection', postData);
    }

    function editServiceTitle(action, UserType) {
        $('#' + UserType + 'service_title_modal_action').html(action);
        $('#' + UserType + 'ServiceTitle_Modal').modal('show');
    }
    function saveServiceTitle(UserType) {
        if ($('#v' + UserType + 'ServiceTitle_<?= $default_lang ?>').val() == "") {
            $('#v' + UserType + 'ServiceTitle_<?= $default_lang ?>_error').show();
            $('#v' + UserType + 'ServiceTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#v' + UserType + 'ServiceTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#v' + UserType + 'ServiceTitle_Default').val($('#v' + UserType + 'ServiceTitle_<?= $default_lang ?>').val());
        $('#v' + UserType + 'ServiceTitle_Default').closest('.row').removeClass('has-error');
        $('#v' + UserType + 'ServiceTitle_Default-error').remove();
        $('#' + UserType + 'ServiceTitle_Modal').modal('hide');
    }

    function saveServiceTitleSection(UserType, vLabel) {
        var vServiceTitleArr = $('[name^="v' + UserType + 'ServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vServiceTitleArr, function(key, value) {
            if(value.name != "v" + UserType + "ServiceTitle_Default") {
                var name_key = value.name.replace('v' + UserType + 'ServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });
        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'GeneralLabel';
        postData['vLangLabel'] = vLabel;
        saveHomeScreenData('save' + UserType + 'ServiceSection', postData);
    }

    function saveHomeScreenData(saveBtnId, postData) {
        $('#' + saveBtnId).prop('disabled', true);
        $('#' + saveBtnId).append(' <i class="fa fa-spinner fa-spin"></i>');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_manage_app_home_screen.php',
            'AJAX_DATA': postData
        };

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


    function getTaxiServices() {

        $("#loaderIcon").show();
        $('#service-list').html('');
        $('#services_modal').modal('show');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'category_content_page_cubexgc.php' ?>',
            'AJAX_DATA': {Type: 'allTaxiservices'},
            'REQUEST_DATA_TYPE': 'html',
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $("#loaderIcon").hide();
            if (response.action == "1") {
                var responseData = response.result;
                $('#service-table').show();
                $('#service-list').html(responseData);
                $('.make-switch')['bootstrapSwitch']();
            }
        });
    }


    function saveTaxiServices() {
        $("#loaderIcon").show();

        var iVehicleCategoryIdArr = [];
        var iVehicleCategoryIdRemoveArr = [];

        for (var i = 0; i < $('input[name="iVehicleCategoryId[]"]').length; i++) {
            if ($('input[name="iVehicleCategoryId[]"]').eq(i).is(":checked")) {
                iVehicleCategoryIdArr.push($('input[name="iVehicleCategoryId[]"]').eq(i).val());
            } else {
                iVehicleCategoryIdRemoveArr.push($('input[name="iVehicleCategoryId[]"]').eq(i).val());  
            }
        }

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'category_content_page_cubexgc.php' ?>',
            'AJAX_DATA': {
                Type: 'Save',
                iVehicleCategoryIdArr: iVehicleCategoryIdArr.toString(),
                iVehicleCategoryIdRemoveArr: iVehicleCategoryIdRemoveArr.toString()
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                location.reload();
            } else {
                $("#loaderIcon").hide();
            }
        });
    }
</script>
</body>
<!-- END BODY-->
</html>