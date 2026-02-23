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


$userEditDataArr = $db_data_arr = array();
$db_data = $obj->MySQLSelect("SELECT * FROM $tbl_name");
foreach ($db_data as $db_value) {
    $ViewType = !empty($db_value['eServiceType']) ? $db_value['eServiceType'] : $db_value['eViewType'];
    if(in_array($db_value['eServiceType'], ["VideoConsult", "Other"])) {
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

$labelsServices = $obj->MySQLSelect("SELECT vCode, vLabel, vValue FROM language_label WHERE vLabel IN ('LBL_VIDEO_CONSULTATION_TXT', 'LBL_MEDICAL_MORE_SERVICES_TITLE') ");

foreach ($labelsServices as $label) {
    if($label['vLabel'] == 'LBL_VIDEO_CONSULTATION_TXT') {
        $userEditDataArr['vVideoConsultHeaderTitle_' . $label['vCode']] = $label['vValue'];

    } elseif ($label['vLabel'] == 'LBL_MEDICAL_MORE_SERVICES_TITLE') {
        $userEditDataArr['vOtherMSHeaderTitle_' . $label['vCode']] = $label['vValue'];
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
$vOtherMSTitleArr = json_decode($db_data_arr['TextBannerView']['Other']['vTitle'], true);
foreach ($vOtherMSTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vOtherMSTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vOtherMSSubTitleArr = json_decode($db_data_arr['TextBannerView']['Other']['vSubtitle'], true);
foreach ($vOtherMSSubTitleArr as $key => $value) {
    $key = str_replace('vSubtitle_', 'vOtherMSSubTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vImageOldOtherMS = $db_data_arr['TextBannerView']['Other']['vImage'];

$OtherMSLayoutDetails = json_decode($db_data_arr['TextBannerView']['Other']['tLayoutDetails'], true);
$vTitleColorOtherMS = $OtherMSLayoutDetails['vTxtTitleColor']; 
$vSubTitleColorOtherMS = $OtherMSLayoutDetails['vTxtSubTitleColor']; 
$vBgColorOtherMS = $OtherMSLayoutDetails['vBgColor'];

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
                                <input type="text" class="form-control" id="vOnDemandServiceTitle_<?= $default_lang ?>"
                                       name="vOnDemandServiceTitle_<?= $default_lang ?>"
                                       value="<?= $userEditDataArr['vOnDemandServiceTitle_' . $default_lang]; ?>">
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
                        <div class="col-lg-4">
                            <div class="manage-banner-section">
                                <button onclick="window.open('vehicle_category.php?eType=MedicalServices', '_blank')" class="manage-banner-btn manage-icon-btn">Manage Services for App Home Screen</button>
                            </div>
                        </div>
                    </div>

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
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveVideoConsultHeaderTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vVideoConsultHeaderTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vVideoConsultHeaderTitle_<?= $default_lang ?>"
                                           name="vVideoConsultHeaderTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vVideoConsultHeaderTitle_' . $default_lang]; ?>">
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
                        <div class="col-lg-4">
                            <div class="manage-banner-section">
                                <button onclick="window.open('vehicle_category.php?eType=MedicalServices', '_blank')" class="manage-banner-btn manage-icon-btn">Manage Services for App Home Screen</button>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <div class="show-help-section section-title">Other Medical Services</div>
                    <div class="underline-section-title"></div>

                    <div class="row paddingbottom-0">
                        <div class="col-lg-12">
                            <div class="tab">
                                <button class="tablinks manage-otherms-title-tab active" onclick="openTabContent(event, 'manage-otherms-title-content', 'tabcontent-otherms')">Title
                                </button>
                                <button class="tablinks manage-otherms-banner-tab" onclick="openTabContent(event, 'manage-otherms-banner-content', 'tabcontent-otherms')">Banner
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="tabcontent tabcontent-otherms display-tab-content" id="manage-otherms-title-content">
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOtherMSHeaderTitle_Default"
                                           name="vOtherMSHeaderTitle_Default"
                                           value="<?= $userEditDataArr['vOtherMSHeaderTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vOtherMSHeaderTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editOtherMSHeaderTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="OtherMSHeaderTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="othermsheader_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOtherMSHeaderTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vOtherMSHeaderTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vOtherMSHeaderTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOtherMSHeaderTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveOtherMSHeaderTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOtherMSHeaderTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vOtherMSHeaderTitle_<?= $default_lang ?>"
                                           name="vOtherMSHeaderTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vOtherMSHeaderTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveOtherMSHeaderSection">Save</button>
                            </div>
                        </div>
                    </div>

                    <div class="tabcontent tabcontent-otherms" id="manage-otherms-banner-content">
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOtherMSTitle_Default"
                                           name="vOtherMSTitle_Default"
                                           value="<?= $userEditDataArr['vOtherMSTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vOtherMSTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editOtherMSTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="OtherMSTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                 data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="otherms_title_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOtherMSTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vOtherMSTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vOtherMSTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOtherMSTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveOtherMSTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOtherMSTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vOtherMSSubTitle_Default"
                                           name="vOtherMSSubTitle_Default"
                                           value="<?= $userEditDataArr['vOtherMSSubTitle_' . $default_lang]; ?>"
                                           data-originalvalue="<?= $userEditDataArr['vOtherMSSubTitle_' . $default_lang]; ?>"
                                           readonly="readonly" required>
                                </div>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip"
                                            data-original-title="Edit" onclick="editOtherMSSubTitle('Edit')">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="OtherMSSubTitle_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="otherms_subtitle_modal_action"></span>
                                                Title
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOtherMSSubTitle_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vOtherMSSubTitle_' . $vCode;
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
                                                                            onClick="getAllLanguageCode('vOtherMSSubTitle_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('vOtherMSSubTitle_', '<?= $default_lang ?>');">
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
                                                        onclick="saveOtherMSSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'vOtherMSSubTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                    <input type="text" class="form-control" id="vOtherMSTitle_<?= $default_lang ?>"
                                           name="vOtherMSTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vOtherMSTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Subtitle</label>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <input type="text" class="form-control" id="vOtherMSSubTitle_<?= $default_lang ?>"
                                           name="vOtherMSSubTitle_<?= $default_lang ?>"
                                           value="<?= $userEditDataArr['vOtherMSSubTitle_' . $default_lang]; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vTitleColorOtherMS" class="form-control txt-color" value="<?= $vTitleColorOtherMS ?>"/>
                                <input type="hidden" name="vTitleColorOtherMS" id="vTitleColorOtherMS" value="<?= $vTitleColorOtherMS ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Subtitle Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vSubTitleColorOtherMS" class="form-control txt-color" value="<?= $vSubTitleColorOtherMS ?>"/>
                                <input type="hidden" name="vSubTitleColorOtherMS" id="vSubTitleColorOtherMS" value="<?= $vSubTitleColorOtherMS ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Background Color</label>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="color" data-id="vBgColorOtherMS" class="form-control bg-color" value="<?= $vBgColorOtherMS ?>"/>
                                <input type="hidden" name="vBgColorOtherMS" id="vBgColorOtherMS" value="<?= $vBgColorOtherMS ?>">
                            </div>
                        </div>

                        <div class="row pb-10">
                            <div class="col-lg-12">
                                <label>Image</label>
                            </div>
                            <div class="col-md-4 col-sm-4 marginbottom-10">
                                <?php if(!empty($vImageOldOtherMS)) { ?>
                                <div class="marginbottom-10">
                                    <img src="<?=$tconfig["tsite_url"].'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_app_home_screen_images'] . 'AppHomeScreen/' . $vImageOldOtherMS; ?>" id="otherms_img">
                                </div>
                                <?php } ?>
                                <input type="file" class="form-control" name="vImageOtherMS" id="vImageOtherMS" onchange="previewImage(this, event);" data-img="otherms_img">
                                <input type="hidden" class="form-control" name="vImageOldOtherMS" id="vImageOldOtherMS" value="<?= $vImageOldOtherMS ?>">
                                <strong class="img-note">Note: Upload only png image size of 740px X 993px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn btn-primary save-section-btn" id="saveOtherMSSection">Save</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <label>Services</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="manage-banner-section">
                                <button onclick="window.open('vehicle_category.php?eType=MedicalServices', '_blank')" class="manage-banner-btn manage-icon-btn">Manage Services for App Home Screen</button>
                            </div>
                        </div>
                    </div>
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
        var vBgColorVideoConsult = $('#vBgColorVideoConsult').val();


        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageVideoConsult);
        postData.append('vImageOld', vImageOldVideoConsult);
        postData.append('vTxtTitleColor', vTitleColorVideoConsult);
        postData.append('vBgColor', vBgColorVideoConsult);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'VideoConsult');

        saveHomeScreenData('saveVideoConsultSection', postData);
    });

    function editOtherMSHeaderTitle(action) {
        $('#othermsheader_title_modal_action').html(action);
        $('#OtherMSHeaderTitle_Modal').modal('show');
    }

    function saveOtherMSHeaderTitle() {
        if ($('#vOtherMSHeaderTitle_<?= $default_lang ?>').val() == "") {
            $('#vOtherMSHeaderTitle_<?= $default_lang ?>_error').show();
            $('#vOtherMSHeaderTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vOtherMSHeaderTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vOtherMSHeaderTitle_Default').val($('#vOtherMSHeaderTitle_<?= $default_lang ?>').val());
        $('#vOtherMSHeaderTitle_Default').closest('.row').removeClass('has-error');
        $('#vOtherMSHeaderTitle_Default-error').remove();
        $('#OtherMSHeaderTitle_Modal').modal('hide');
    }

    $('#saveOtherMSHeaderSection').click(function() {
        var vOtherMSHeaderTitleArr = $('[name^="vOtherMSHeaderTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vOtherMSHeaderTitleArr, function(key, value) {
            if(value.name != "vOtherMSHeaderTitle_Default") {
                var name_key = value.name.replace('vOtherMSHeaderTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = vTitleArr;
        postData['ViewType'] = 'TitleView';
        postData['ServiceType'] = 'Other';

        saveHomeScreenData('saveOtherMSHeaderSection', postData, 'No');
    });

    function editOtherMSTitle(action) {
        $('#otherms_title_modal_action').html(action);
        $('#OtherMSTitle_Modal').modal('show');
    }

    function saveOtherMSTitle() {
        if ($('#vOtherMSTitle_<?= $default_lang ?>').val() == "") {
            $('#vOtherMSTitle_<?= $default_lang ?>_error').show();
            $('#vOtherMSTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vOtherMSTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vOtherMSTitle_Default').val($('#vOtherMSTitle_<?= $default_lang ?>').val());
        $('#vOtherMSTitle_Default').closest('.row').removeClass('has-error');
        $('#vOtherMSTitle_Default-error').remove();
        $('#OtherMSTitle_Modal').modal('hide');
    }

    function editOtherMSSubTitle(action) {
        $('#otherms_subtitle_modal_action').html(action);
        $('#OtherMSSubTitle_Modal').modal('show');
    }

    function saveOtherMSSubTitle() {
        if ($('#vOtherMSSubTitle_<?= $default_lang ?>').val() == "") {
            $('#vOtherMSSubTitle_<?= $default_lang ?>_error').show();
            $('#vOtherMSSubTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vOtherMSSubTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vOtherMSSubTitle_Default').val($('#vOtherMSSubTitle_<?= $default_lang ?>').val());
        $('#vOtherMSSubTitle_Default').closest('.row').removeClass('has-error');
        $('#vOtherMSSubTitle_Default-error').remove();
        $('#OtherMSSubTitle_Modal').modal('hide');
    }

    $('#saveOtherMSSection').click(function() {
        var vOtherMSTitleArr = $('[name^="vOtherMSTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vOtherMSTitleArr, function(key, value) {
            if(value.name != "vOtherMSTitle_Default") {
                var name_key = value.name.replace('vOtherMSTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vOtherMSSubTitleArr = $('[name^="vOtherMSSubTitle_"]').serializeArray();
        var vSubTitleArr = {};
        $.each(vOtherMSSubTitleArr, function(key, value) {
            if(value.name != "vOtherMSSubTitle_Default") {
                var name_key = value.name.replace('vOtherMSSubTitle', 'vSubtitle');
                vSubTitleArr[name_key] = value.value;
            }
        });
        var vImageOtherMS = $('#vImageOtherMS')[0].files[0];
        var vImageOldOtherMS = $('#vImageOldOtherMS').val();
        var vTitleColorOtherMS = $('#vTitleColorOtherMS').val();
        var vBgColorOtherMS = $('#vBgColorOtherMS').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vSubTitleArr', JSON.stringify(vSubTitleArr));
        postData.append('vImage', vImageOtherMS);
        postData.append('vImageOld', vImageOldOtherMS);
        postData.append('vTxtTitleColor', vTitleColorOtherMS);
        postData.append('vBgColor', vBgColorOtherMS);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'Other');

        saveHomeScreenData('saveOtherMSSection', postData);
    });

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