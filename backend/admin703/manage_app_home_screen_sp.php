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
    if(in_array($db_value['eServiceType'], ["VideoConsult", "Bidding"])) {
        $tServiceDetailsDB = json_decode($db_value['tServiceDetails'], true);
        $db_data_arr[$db_value['eViewType']][$db_value['eServiceType']] = $db_value;
    } else {
        $db_data_arr[$ViewType] = $db_value;
    }
}

/* General Banners */
$bannerData = $obj->MySQLSelect("SELECT * FROM banners WHERE iServiceId = 0 AND vCode = '$default_lang' AND eType = 'General' AND eFor = 'General' AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 0,3");

/* On-Demand Services */
if ($MODULES_OBJ->isUberXFeatureAvailable()) {
    $vOnDemandServiceTitleArr = json_decode($db_data_arr['UberX']['vTitle'], true);
    foreach ($vOnDemandServiceTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vOnDemandServiceTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
}

$ufxData = $obj->MySQLSelect("SELECT vc1.iVehicleCategoryId,vc1.eStatus , vc1.vCategory_$default_lang as vCategoryName, vc1.vListLogo3, vc1.vIconDetails, vc1.iDisplayOrder, vc1.iDisplayOrderVC, (SELECT COUNT(vc2.iVehicleCategoryId) as count FROM $sql_vehicle_category_table_name as vc2 WHERE vc2.iParentId = vc1.iVehicleCategoryId AND vc2.eVideoConsultEnable = 'Yes') as eVideoConsultEnableCount FROM $sql_vehicle_category_table_name as vc1 WHERE vc1.eCatType = 'ServiceProvider' AND vc1.eVideoConsultEnable = 'No' AND vc1.iParentId = '0' AND eStatus != 'Deleted' ORDER BY vc1.iDisplayOrder");

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

$labelsServices = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_VIDEO_CONSULTATION_TXT', 'LBL_BIDDING_POST_TASK_TITLE') ");

foreach ($labelsServices as $label) {
    if($label['vLabel'] == 'LBL_VIDEO_CONSULTATION_TXT') {
        $userEditDataArr['vVideoConsultHeaderTitle_' . $label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_BIDDING_POST_TASK_TITLE') {
        $userEditDataArr['vBiddingHeaderTitle_' . $label['vCode']] = $label['vValue'];
    }
}

/* Video Consult */
if ($MODULES_OBJ->isEnableVideoConsultingService()) {

    $vVideoConsultTitleArr = json_decode($db_data_arr['TextBannerView']['VideoConsult']['vTitle'], true);
    foreach ($vVideoConsultTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vVideoConsultTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vVideoConsultSubTitleArr = json_decode($db_data_arr['TextBannerView']['VideoConsult']['vSubtitle'], true);
    foreach ($vVideoConsultSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vVideoConsultSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldVideoConsult = $db_data_arr['TextBannerView']['VideoConsult']['vImage'];

    $VideoConsultLayoutDetails = json_decode($db_data_arr['TextBannerView']['VideoConsult']['tLayoutDetails'], true);
    $vTitleColorVideoConsult = $VideoConsultLayoutDetails['vTxtTitleColor']; 
    $vSubTitleColorVideoConsult = $VideoConsultLayoutDetails['vTxtSubTitleColor']; 
    $vBgColorVideoConsult = $VideoConsultLayoutDetails['vBgColor'];
}

/* Service Bid */
if ($MODULES_OBJ->isEnableBiddingServices()) {
    $vBiddingTitleArr = json_decode($db_data_arr['TextBannerView']['Bidding']['vTitle'], true);
    foreach ($vBiddingTitleArr as $key => $value) {
        $key = str_replace('vTitle_', 'vBiddingTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vBiddingSubTitleArr = json_decode($db_data_arr['TextBannerView']['Bidding']['vSubtitle'], true);
    foreach ($vBiddingSubTitleArr as $key => $value) {
        $key = str_replace('vSubtitle_', 'vBiddingSubTitle_', $key);
        $userEditDataArr[$key] = $value;
    }
    $vImageOldBidding = $db_data_arr['TextBannerView']['Bidding']['vImage'];

    $BiddingLayoutDetails = json_decode($db_data_arr['TextBannerView']['Bidding']['tLayoutDetails'], true);
    $vTitleColorBidding = $BiddingLayoutDetails['vTxtTitleColor']; 
    $vSubTitleColorBidding = $BiddingLayoutDetails['vTxtSubTitleColor']; 
    $vBgColorBidding = $BiddingLayoutDetails['vBgColor'];

    $ServiceBidData = $BIDDING_OBJ->getBiddingMaster('admin', '', 0, 0, $default_lang);
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
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOnDemandServiceTitle_<?= $default_lang ?>" name="vOnDemandServiceTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>">
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
                                    <div class="service-img-block service-img-grid">
                                    <?php $ufxCount = 0; foreach ($ufxData as $ufxService) {
                                        if (!empty($ufxService['vListLogo3']) && $ufxCount < 7) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ufxService['iVehicleCategoryId'] . "/" . $ufxService['vListLogo3'];
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ufxService['vCategoryName'] ?></div>
                                        </div>
                                    
                                    <?php $ufxCount++; } } ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_sp.png') ?>">
                                        <div class="service-img-title"><?= $langage_lbl_admin['LBL_MORE'] ?></div>
                                    </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#ondemandservices_modal" style="margin-top: 25px;">Manage Services for App</button>
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
                                            <?php } ?>
                                            <tr>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . 'webimages/icons/DefaultImg/' . getMoreServicesIconName('ic_more_sp.png') ?>">
                                                </td>
                                                <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE'] ?></td>
                                                <td style="text-align: center;vertical-align: middle;">--</td>
                                                <td style="text-align: center;vertical-align: middle;">--</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="<?= $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=ondemand_more_services&vLabel=LBL_MORE'; ?>" class="btn btn-primary" target="_blank">Edit</a>
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

                        <div class="row paddingbottom-0">
                            <div class="col-lg-12">
                                <div class="tab">
                                    <button class="tablinks manage-videoconsult-title-tab active" onclick="openTabContent(event, 'manage-videoconsult-title-content', 'tabcontent-videoconsult')">Title
                                    </button>
                                    <button class="tablinks manage-videoconsult-banner-tab" onclick="openTabContent(event, 'manage-videoconsult-banner-content', 'tabcontent-videoconsult')">Banner
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tabcontent tabcontent-videoconsult display-tab-content" id="manage-videoconsult-title-content">
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vVideoConsultHeaderTitle_Default"
                                               name="vVideoConsultHeaderTitle_Default"
                                               value="<?= $userEditDataArr['vVideoConsultHeaderTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vVideoConsultHeaderTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editVideoConsultHeaderTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="VideoConsultHeaderTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="videoconsultheader_title_modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vVideoConsultHeaderTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vVideoConsultHeaderTitle_' . $vCode;
                                                    $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '';
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
                                                                                onClick="getAllLanguageCode('vVideoConsultHeaderTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vVideoConsultHeaderTitle_', '<?= $default_lang ?>');">
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveVideoConsultHeaderTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vVideoConsultHeaderTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vVideoConsultHeaderTitle_<?= $default_lang ?>" name="vVideoConsultHeaderTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vVideoConsultHeaderTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="saveVideoConsultHeaderSection">Save</button>
                                </div>
                            </div>
                        </div>

                        <div class="tabcontent tabcontent-videoconsult" id="manage-videoconsult-banner-content">
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vVideoConsultTitle_Default"
                                               name="vVideoConsultTitle_Default"
                                               value="<?= $userEditDataArr['vVideoConsultTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vVideoConsultTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveVideoConsultTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vVideoConsultTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveVideoConsultSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vVideoConsultSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vVideoConsultTitle_<?= $default_lang ?>" name="vVideoConsultTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vVideoConsultTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vVideoConsultSubTitle_<?= $default_lang ?>" name="vVideoConsultSubTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vVideoConsultSubTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vTitleColorVideoConsult" class="form-control txt-color" value="<?= $vTitleColorVideoConsult ?>"/>
                                    <input type="hidden" name="vTitleColorVideoConsult" id="vTitleColorVideoConsult" value="<?= $vTitleColorVideoConsult ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vSubTitleColorVideoConsult" class="form-control txt-color" value="<?= $vSubTitleColorVideoConsult ?>"/>
                                    <input type="hidden" name="vSubTitleColorVideoConsult" id="vSubTitleColorVideoConsult" value="<?= $vSubTitleColorVideoConsult ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Background Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vBgColorVideoConsult" class="form-control bg-color" value="<?= $vBgColorVideoConsult ?>"/>
                                    <input type="hidden" name="vBgColorVideoConsult" id="vBgColorVideoConsult" value="<?= $vBgColorVideoConsult ?>">
                                </div>
                            </div>

                            <div class="row pb-10">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-4 col-sm-4 marginbottom-10">
                                    <?php if(!empty($vImageOldVideoConsult)) { ?>
                                    <div class="marginbottom-10">
                                        <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldVideoConsult; ?>" id="videoconsult_img">
                                    </div>
                                    <?php } ?>
                                    <input type="file" class="form-control" name="vImageVideoConsult" id="vImageVideoConsult" onchange="previewImage(this, event);" data-img="videoconsult_img">
                                    <input type="hidden" class="form-control" name="vImageOldVideoConsult" id="vImageOldVideoConsult" value="<?= $vImageOldVideoConsult ?>">
                                    <strong class="img-note">Note: Upload only png image size of 700px X 800px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="saveVideoConsultSection">Save</button>
                                </div>
                            </div>
                        </div>
                        

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block service-img-grid">
                                    <?php $ufxCountVC = 0; foreach ($ufxDataVC as $ufxServiceVC) {
                                        if (!empty($ufxServiceVC['vIconDetails']) && $ufxCountVC < 7) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_vehicle_category'] . "/" . $ufxServiceVC['iVehicleCategoryId'] . "/" . $ufxServiceVC['vIconDetails'];
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ufxServiceVC['vCategoryName'] ?></div>
                                        </div>
                                    
                                    <?php $ufxCountVC++; } } ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_vc_sp.png') ?>">
                                        <div class="service-img-title"><?= $langage_lbl_admin['LBL_MORE'] ?></div>
                                    </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#vcservices_modal" style="margin-top: 25px;">Manage Services for App</button>
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
                                            <?php } ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . 'webimages/icons/DefaultImg/' . getMoreServicesIconName('ic_more_vc_sp.png'); ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE'] ?></td>
                                                    <td style="text-align: center;vertical-align: middle;">--</td>
                                                    <td style="text-align: center;vertical-align: middle;">--</td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=video_more_services&vLabel=LBL_MORE' ?>" class="btn btn-primary" target="_blank">Edit</a>
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
                    <?php } ?>

                    <?php if ($MODULES_OBJ->isEnableBiddingServices()) { ?>
                        <hr />
                        <div class="show-help-section section-title">Service Bid</div>
                        <div class="underline-section-title"></div>

                        <div class="row paddingbottom-0">
                            <div class="col-lg-12">
                                <div class="tab">
                                    <button class="tablinks manage-bidding-title-tab active" onclick="openTabContent(event, 'manage-bidding-title-content', 'tabcontent-bidding')">Title
                                    </button>
                                    <button class="tablinks manage-bidding-banner-tab" onclick="openTabContent(event, 'manage-bidding-banner-content', 'tabcontent-bidding')">Banner
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tabcontent tabcontent-bidding display-tab-content" id="manage-bidding-title-content">
                            <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <input type="text" class="form-control" id="vBiddingHeaderTitle_Default"
                                               name="vBiddingHeaderTitle_Default"
                                               value="<?= $userEditDataArr['vBiddingHeaderTitle_' . $default_lang]; ?>"
                                               data-originalvalue="<?= $userEditDataArr['vBiddingHeaderTitle_' . $default_lang]; ?>"
                                               readonly="readonly" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editBiddingHeaderTitle('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal fade" id="BiddingHeaderTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                     data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="biddingheader_title_modal_action"></span>
                                                    Title
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vBiddingHeaderTitle_')">x
                                                    </button>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                for ($i = 0; $i < $count_all; $i++) {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vValue = 'vBiddingHeaderTitle_' . $vCode;
                                                    $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '' ;
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
                                                                                onClick="getAllLanguageCode('vBiddingHeaderTitle_', 'EN');">
                                                                            Convert To All Language
                                                                        </button>
                                                                    </div>
                                                                <?php }
                                                            } else {
                                                                if ($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type="button" name="allLanguage"
                                                                                id="allLanguage" class="btn btn-primary"
                                                                                onClick="getAllLanguageCode('vBiddingHeaderTitle_', '<?= $default_lang ?>');">
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
                                                            onclick="saveBiddingHeaderTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                            onclick="resetToOriginalValue(this, 'vBiddingHeaderTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                        <input type="text" class="form-control" id="vBiddingHeaderTitle_<?= $default_lang ?>"
                                               name="vBiddingHeaderTitle_<?= $default_lang ?>"
                                               value="<?= $userEditDataArr['vBiddingHeaderTitle_' . $default_lang]; ?>">
                                    </div>
                                </div>
                            <?php } ?>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="saveBiddingHeaderSection">Save</button>
                                </div>
                            </div>
                        </div>

                        <div class="tabcontent tabcontent-bidding" id="manage-bidding-banner-content">
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
                                    <label>Title Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vTitleColorBidding" class="form-control txt-color" value="<?= $vTitleColorBidding ?>"/>
                                    <input type="hidden" name="vTitleColorBidding" id="vTitleColorBidding" value="<?= $vTitleColorBidding ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vSubTitleColorBidding" class="form-control txt-color" value="<?= $vSubTitleColorBidding ?>"/>
                                    <input type="hidden" name="vSubTitleColorBidding" id="vSubTitleColorBidding" value="<?= $vSubTitleColorBidding ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Background Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" data-id="vBgColorBidding" class="form-control bg-color" value="<?= $vBgColorBidding ?>"/>
                                    <input type="hidden" name="vBgColorBidding" id="vBgColorBidding" value="<?= $vBgColorBidding ?>">
                                </div>
                            </div>

                            <div class="row pb-10">
                                <div class="col-lg-12">
                                    <label>Image</label>
                                </div>
                                <div class="col-md-4 col-sm-4 marginbottom-10">
                                    <?php if(!empty($vImageOldBidding)) { ?>
                                    <div class="marginbottom-10">
                                        <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldBidding; ?>" id="bidding_img">
                                    </div>
                                    <?php } ?>
                                    <input type="file" class="form-control" name="vImageBidding" id="vImageBidding" onchange="previewImage(this, event);" data-img="bidding_img">
                                    <input type="hidden" class="form-control" name="vImageOldBidding" id="vImageOldBidding" value="<?= $vImageOldBidding ?>">
                                    <strong class="img-note">Note: Upload only png image size of 740px X 993px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="button" class="btn btn-primary save-section-btn" id="saveBiddingSection">Save</button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Services</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="manage-banner-section">
                                    <div class="service-img-block service-img-grid">
                                    <?php $ServiceBidCount = 0; foreach ($ServiceBidData as $ServiceBid) {
                                        $vIconImage = $ServiceBid['vImage'];
                                        if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
                                            $vIconImage = $ServiceBid['vImage1'];
                                        }
                                        if (!empty($vIconImage) && $ServiceBidCount < 7) {
                                            $vServiceImg = $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig['tsite_upload_images_bidding'] .$vIconImage;
                                    ?>
                                        <div class="service-preview-img">
                                            <img src="<?= $vServiceImg ?>">
                                            <div class="service-img-title"><?= $ServiceBid['vTitle'] ?></div>
                                        </div>
                                    
                                    <?php $ServiceBidCount++; } } ?>
                                    <div class="service-preview-img">
                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=70&src=' . $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . getMoreServicesIconName('ic_more_bid_sp.png') ?>">
                                        <div class="service-img-title"><?= $langage_lbl_admin ['LBL_MORE'] ?></div>
                                    </div>
                                    </div>
                                    <button type="button" class="manage-banner-btn manage-icon-btn" data-toggle="modal" data-target="#bidservices_modal" style="margin-top: 25px;">Manage Services for App</button>
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
                                                if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) {
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
                                            <?php } ?>
                                                <tr>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig["tsite_url"] . 'webimages/icons/DefaultImg/' . getMoreServicesIconName('ic_more_bid_sp.png') ?>">
                                                    </td>
                                                    <td style="vertical-align: middle;"><?= $langage_lbl_admin['LBL_MORE'] ?></td>
                                                    <td style="text-align: center;vertical-align: middle;">--</td>
                                                    <td style="text-align: center;vertical-align: middle;">--</td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <a href="<?= $tconfig['tsite_url_main_admin'] . 'more_services_category_action.php?eFor=bid_more_services&vLabel=LBL_MORE' ?>" class="btn btn-primary" target="_blank">Edit</a>
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
<script src="../assets/js/modal_alert.js"></script>
<script type="text/javascript">
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

    $('#saveOnDemandServiceSection').click(function() {
        var vOnDemandServiceTitleArr = $('[name^="vOnDemandServiceTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vOnDemandServiceTitleArr, function(key, value) {
            if(value.name != "vOnDemandServiceTitle_Default") {
                var name_key = value.name.replace('vOnDemandServiceTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));

        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'UberX');

        saveHomeScreenData('saveOnDemandServiceSection', postData);
    });

    function editVideoConsultHeaderTitle(action) {
        $('#videoconsultheader_title_modal_action').html(action);
        $('#VideoConsultHeaderTitle_Modal').modal('show');
    }

    function saveVideoConsultHeaderTitle() {
        if ($('#vVideoConsultHeaderTitle_<?= $default_lang ?>').val() == "") {
            $('#vVideoConsultHeaderTitle_<?= $default_lang ?>_error').show();
            $('#vVideoConsultHeaderTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vVideoConsultHeaderTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vVideoConsultHeaderTitle_Default').val($('#vVideoConsultHeaderTitle_<?= $default_lang ?>').val());
        $('#vVideoConsultHeaderTitle_Default').closest('.row').removeClass('has-error');
        $('#vVideoConsultHeaderTitle_Default-error').remove();
        $('#VideoConsultHeaderTitle_Modal').modal('hide');
    }

    $('#saveVideoConsultHeaderSection').click(function() {
        var vVideoConsultHeaderTitleArr = $('[name^="vVideoConsultHeaderTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vVideoConsultHeaderTitleArr, function(key, value) {
            if(value.name != "vVideoConsultHeaderTitle_Default") {
                var name_key = value.name.replace('vVideoConsultHeaderTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'VideoConsult';

        saveHomeScreenData('saveVideoConsultHeaderSection', postData, 'No');
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
        var vImageVideoConsult = $('#vImageVideoConsult')[0].files[0];
        var vImageOldVideoConsult = $('#vImageOldVideoConsult').val();
        var vTitleColorVideoConsult = $('#vTitleColorVideoConsult').val();
        var vSubTitleColorVideoConsult = $('#vSubTitleColorVideoConsult').val();
        var vBgColorVideoConsult = $('#vBgColorVideoConsult').val();


        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageVideoConsult);
        postData.append('vImageOld', vImageOldVideoConsult);
        postData.append('vTxtTitleColor', vTitleColorVideoConsult);
        postData.append('vTxtSubTitleColor', vSubTitleColorVideoConsult);
        postData.append('vBgColor', vBgColorVideoConsult);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'VideoConsult');

        saveHomeScreenData('saveVideoConsultSection', postData);
    });

    function editBiddingHeaderTitle(action) {
        $('#biddingheader_title_modal_action').html(action);
        $('#BiddingHeaderTitle_Modal').modal('show');
    }

    function saveBiddingHeaderTitle() {
        if ($('#vBiddingHeaderTitle_<?= $default_lang ?>').val() == "") {
            $('#vBiddingHeaderTitle_<?= $default_lang ?>_error').show();
            $('#vBiddingHeaderTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBiddingHeaderTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBiddingHeaderTitle_Default').val($('#vBiddingHeaderTitle_<?= $default_lang ?>').val());
        $('#vBiddingHeaderTitle_Default').closest('.row').removeClass('has-error');
        $('#vBiddingHeaderTitle_Default-error').remove();
        $('#BiddingHeaderTitle_Modal').modal('hide');
    }

    $('#saveBiddingHeaderSection').click(function() {
        var vBiddingHeaderTitleArr = $('[name^="vBiddingHeaderTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vBiddingHeaderTitleArr, function(key, value) {
            if(value.name != "vBiddingHeaderTitle_Default") {
                var name_key = value.name.replace('vBiddingHeaderTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Bidding';

        saveHomeScreenData('saveBiddingHeaderSection', postData, 'No');
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
        var vImageBidding = $('#vImageBidding')[0].files[0];
        var vImageOldBidding = $('#vImageOldBidding').val();
        var vTitleColorBidding = $('#vTitleColorBidding').val();
        var vBgColorBidding = $('#vBgColorBidding').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageBidding);
        postData.append('vImageOld', vImageOldBidding);
        postData.append('vTxtTitleColor', vTitleColorBidding);
        postData.append('vBgColor', vBgColorBidding);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'Bidding');

        saveHomeScreenData('saveBiddingSection', postData);
    });

    function saveDisplayOrderService(ServiceType) {
        var iDisplayOrderArr = {};

        if(ServiceType == "UberX") {
            var DisplayOrderElem = $('[name^="iDisplayOrderUberXServiceArr"]');
        } else if(ServiceType == "Bidding") {
            var DisplayOrderElem = $('[name^="iDisplayOrderBidServiceArr"]');
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