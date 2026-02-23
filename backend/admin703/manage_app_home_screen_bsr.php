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

$master_service_categories = $obj->MySQLSelect("SELECT vCategoryName, JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vMasterCategoryName, eType, iMasterServiceCategoryId, vIconImage1, tCategoryDetails, vTextColor, vBgColor FROM $master_service_category_tbl WHERE eStatus = 'Active'");
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

/* General Banners */
$bannerData = $obj->MySQLSelect("SELECT * FROM banners WHERE iServiceId = 0 AND vCode = '$default_lang' AND eType = 'General' AND eFor = 'General' AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 0,3");

$vNewListingTitleArr = json_decode($db_data_arr['NewListingView']['vTitle'], true);
foreach ($vNewListingTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vNewListingTitle_', $key);
    $userEditDataArr[$key] = $value;
}
$vBtnTxtArr = json_decode($db_data_arr['NewListingView']['vBtnTxt'], true);
foreach ($vBtnTxtArr as $key => $value) {
    $userEditDataArr[$key] = $value;
}

/* Buy, Sell & Rent */
if ($MODULES_OBJ->isEnableRentEstateService()) {
    $vRentEstateTitleArr = json_decode($MasterCategoryArr['RentEstate']['vCategoryName'], true);
    foreach ($vRentEstateTitleArr as $key => $value) {
        $key = str_replace('vCategoryName_', 'vRentEstateTitle_', $key);
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
    $vImageOldRentItem = $MasterCategoryArr['RentItem']['vIconImage1'];

    $vTitleColorRentItem = $MasterCategoryArr['RentItem']['vTextColor']; 
    $vBgColorRentItem = $MasterCategoryArr['RentItem']['vBgColor'];
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
                    <div class="show-help-section section-title">List Now</div>
                    <div class="underline-section-title"></div>
                    <?php if (scount($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vNewListingTitle_Default"
                                       name="vNewListingTitle_Default"
                                       value="<?= $userEditDataArr['vNewListingTitle_' . $default_lang]; ?>"
                                       data-originalvalue="<?= $userEditDataArr['vNewListingTitle_' . $default_lang]; ?>"
                                       readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editNewListingTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="NewListingTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="newlisting_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vNewListingTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vNewListingTitle_' . $vCode;
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
                                                                        onClick="getAllLanguageCode('vNewListingTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vNewListingTitle_', '<?= $default_lang ?>');">
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
                                                    onclick="saveNewListingTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vNewListingTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Button Text</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vBtnTxt_Default" name="vBtnTxt_Default" value="<?= $userEditDataArr['vBtnTxt_' . $default_lang]; ?>" data-originalvalue="<?= $userEditDataArr['vBtnTxt_' . $default_lang]; ?>" readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editBtnTxt('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="BtnTxt_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="btntxt_modal_action"></span>
                                            Button Text
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vBtnTxt_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vBtnTxt_' . $vCode;
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
                                                    <label>Button Text (<?= $vTitle; ?>
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
                                                                        onClick="getAllLanguageCode('vBtnTxt_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vBtnTxt_', '<?= $default_lang ?>');">
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
                                            <button type="button" class="save" style="margin-left: 0 !important" onclick="saveBtnTxt()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vBtnTxt_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                <input type="text" class="form-control" id="vNewListingTitle_<?= $default_lang ?>" name="vNewListingTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vNewListingTitle_' . $default_lang]; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Button Text</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vBtnTxt_<?= $default_lang ?>" name="vBtnTxt_<?= $default_lang ?>" value="<?= $userEditDataArr['vBtnTxt_' . $default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveNewListingSection">Save</button>
                        </div>
                    </div>

                    <?php if ($MODULES_OBJ->isEnableRentEstateService() || $MODULES_OBJ->isEnableRentCarsService() || $MODULES_OBJ->isEnableRentItemService()) { 
                        $show_rentestate_tab = $show_rentcars_tab = $show_rentitem_tab = "";
                        $show_rentestate_content = $show_rentcars_content = $show_rentitem_content = "";
                        ?>
                        <hr/>
                        <div class="show-help-section section-title">Buy, Sell & Rent</div>
                        <div class="underline-section-title"></div>

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
                                                <input type="file" class="form-control" name="vImageRentEstate" id="vImageRentEstate" onchange="previewImage(this, event);" data-img="rentestate_img">
                                                <input type="hidden" class="form-control" name="vImageOldRentEstate" id="vImageOldRentEstate" value="<?= $vImageOldRentEstate ?>">
                                                <strong class="img-note">Note: Upload only png image size of 1024px X 618px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
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
                                                <input type="file" class="form-control" name="vImageRentCars" id="vImageRentCars" onchange="previewImage(this, event);" data-img="rentcars_img">
                                                <input type="hidden" class="form-control" name="vImageOldRentCars" id="vImageOldRentCars" value="<?= $vImageOldRentCars ?>">
                                                <strong class="img-note">Note: Upload only png image size of 1024px X 494px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
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
                                                <input type="file" class="form-control" name="vImageRentItem" id="vImageRentItem" onchange="previewImage(this, event);" data-img="rentitem_img">
                                                <input type="hidden" class="form-control" name="vImageOldRentItem" id="vImageOldRentItem" value="<?= $vImageOldRentItem ?>">
                                                <strong class="img-note">Note: Upload only png image size of 973px X 748px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> </strong>
                                            </div>
                                        </div>
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
    function editNewListingTitle(action) {
        $('#newlisting_title_modal_action').html(action);
        $('#NewListingTitle_Modal').modal('show');
    }

    function saveNewListingTitle() {
        if ($('#vNewListingTitle_<?= $default_lang ?>').val() == "") {
            $('#vNewListingTitle_<?= $default_lang ?>_error').show();
            $('#vNewListingTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vNewListingTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vNewListingTitle_Default').val($('#vNewListingTitle_<?= $default_lang ?>').val());
        $('#vNewListingTitle_Default').closest('.row').removeClass('has-error');
        $('#vNewListingTitle_Default-error').remove();
        $('#NewListingTitle_Modal').modal('hide');
    }

    function editBtnTxt(action) {
        $('#btntxt_modal_action').html(action);
        $('#BtnTxt_Modal').modal('show');
    }

    function saveBtnTxt() {
        if ($('#vBtnTxt_<?= $default_lang ?>').val() == "") {
            $('#vBtnTxt_<?= $default_lang ?>_error').show();
            $('#vBtnTxt_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vBtnTxt_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vBtnTxt_Default').val($('#vBtnTxt_<?= $default_lang ?>').val());
        $('#vBtnTxt_Default').closest('.row').removeClass('has-error');
        $('#vBtnTxt_Default-error').remove();
        $('#BtnTxt_Modal').modal('hide');
    }

    $('#saveNewListingSection').click(function() {
        var vNewListingTitleArr = $('[name^="vNewListingTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vNewListingTitleArr, function(key, value) {
            if(value.name != "vNewListingTitle_Default") {
                var name_key = value.name.replace('vNewListingTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var vBtnTxtNewListingArr = $('[name^="vBtnTxt_"]').serializeArray();
        var vBtnTxtArr = {};
        $.each(vBtnTxtNewListingArr, function(key, value) {
            if(value.name != "vBtnTxt_Default") {
                var name_key = value.name;
                vBtnTxtArr[name_key] = value.value;
            }
        });

        var postData = {};
        postData['vTitleArr'] = JSON.stringify(vTitleArr);
        postData['vBtnTxtArr'] = JSON.stringify(vBtnTxtArr);
        postData['ViewType'] = 'NewListingView';
        postData['ServiceType'] = 'NewListing';

        saveHomeScreenData('saveNewListingSection', postData, "No");
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

    $('#saveRentEstateSection').click(function() {
        var vRentEstateTitleArr = $('[name^="vRentEstateTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRentEstateTitleArr, function(key, value) {
            if(value.name != "vRentEstateTitle_Default") {
                var name_key = value.name.replace('vRentEstateTitle', 'vCategoryName');
                vTitleArr[name_key] = value.value;
            }
        });

        var vImageRentEstate = $('#vImageRentEstate')[0].files[0];
        var vImageOldRentEstate = $('#vImageOldRentEstate').val();
        var vTitleColorRentEstate = $('#vTitleColorRentEstate').val();
        var vBgColorRentEstate = $('#vBgColorRentEstate').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
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

    $('#saveRentCarsSection').click(function() {
        var vRentCarsTitleArr = $('[name^="vRentCarsTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRentCarsTitleArr, function(key, value) {
            if(value.name != "vRentCarsTitle_Default") {
                var name_key = value.name.replace('vRentCarsTitle', 'vCategoryName');
                vTitleArr[name_key] = value.value;
            }
        });

        var vImageRentCars = $('#vImageRentCars')[0].files[0];
        var vImageOldRentCars = $('#vImageOldRentCars').val();
        var vTitleColorRentCars = $('#vTitleColorRentCars').val();
        var vBgColorRentCars = $('#vBgColorRentCars').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
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

    $('#saveRentItemSection').click(function() {
        var vRentItemTitleArr = $('[name^="vRentItemTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vRentItemTitleArr, function(key, value) {
            if(value.name != "vRentItemTitle_Default") {
                var name_key = value.name.replace('vRentItemTitle', 'vCategoryName');
                vTitleArr[name_key] = value.value;
            }
        });

        var vImageRentItem = $('#vImageRentItem')[0].files[0];
        var vImageOldRentItem = $('#vImageOldRentItem').val();
        var vTitleColorRentItem = $('#vTitleColorRentItem').val();
        var vBgColorRentItem = $('#vBgColorRentItem').val();

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));
        postData.append('vImage', vImageRentItem);
        postData.append('vImageOld', vImageOldRentItem);
        postData.append('vTxtTitleColor', vTitleColorRentItem);
        postData.append('vBgColor', vBgColorRentItem);
        postData.append('ViewType', 'TextBannerView');
        postData.append('ServiceType', 'RentItem');

        saveHomeScreenData('saveRentItemSection', postData);
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