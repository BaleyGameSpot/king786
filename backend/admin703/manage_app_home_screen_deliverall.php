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
    if(in_array($db_value['eServiceType'], ["VideoConsult", "Bidding"])) {
        $tServiceDetailsDB = json_decode($db_value['tServiceDetails'], true);
        $db_data_arr[$db_value['eViewType']][$db_value['eServiceType']] = $db_value;
    } else {
        $db_data_arr[$ViewType] = $db_value;
    }
}

/* General Banners */
$bannerData = $obj->MySQLSelect("SELECT * FROM banners WHERE iServiceId = 0 AND vCode = '$default_lang' AND eType = 'General' AND eFor = 'General' AND eStatus = 'Active' ORDER BY iDisplayOrder LIMIT 0,3");

/* DeliverAll Services */
$vDeliverAllTitleArr = json_decode($db_data_arr['DeliverAll']['vTitle'], true);
foreach ($vDeliverAllTitleArr as $key => $value) {
    $key = str_replace('vTitle_', 'vDeliverAllTitle_', $key);
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
                    <div class="show-help-section section-title">Store Delivery Services</div>
                    <div class="underline-section-title"></div>
                    <?php if (scount($db_master) > 1) { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Title</label>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <input type="text" class="form-control" id="vDeliverAllTitle_Default"
                                       name="vDeliverAllTitle_Default"
                                       value="<?= $userEditDataArr['vDeliverAllTitle_' . $default_lang]; ?>"
                                       data-originalvalue="<?= $userEditDataArr['vDeliverAllTitle_' . $default_lang]; ?>"
                                       readonly="readonly" required>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-info" data-toggle="tooltip"
                                        data-original-title="Edit" onclick="editDeliverAllTitle('Edit')">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="modal fade" id="DeliverAllTitle_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                             data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content nimot-class">
                                    <div class="modal-header">
                                        <h4>
                                            <span id="deliverall_title_modal_action"></span>
                                            Title
                                            <button type="button" class="close" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vDeliverAllTitle_')">x
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        for ($i = 0; $i < $count_all; $i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];
                                            $vValue = 'vDeliverAllTitle_' . $vCode;
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
                                                                        onClick="getAllLanguageCode('vDeliverAllTitle_', 'EN');">
                                                                    Convert To All Language
                                                                </button>
                                                            </div>
                                                        <?php }
                                                    } else {
                                                        if ($vCode == $default_lang) { ?>
                                                            <div class="col-md-3 col-sm-3">
                                                                <button type="button" name="allLanguage"
                                                                        id="allLanguage" class="btn btn-primary"
                                                                        onClick="getAllLanguageCode('vDeliverAllTitle_', '<?= $default_lang ?>');">
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
                                                    onclick="saveDeliverAllTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                    onclick="resetToOriginalValue(this, 'vDeliverAllTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
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
                                <input type="text" class="form-control" id="vDeliverAllTitle_<?= $default_lang ?>"
                                       name="vDeliverAllTitle_<?= $default_lang ?>"
                                       value="<?= $userEditDataArr['vDeliverAllTitle_' . $default_lang]; ?>">
                            </div>
                        </div>
                    <?php } ?>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary save-section-btn" id="saveDeliverAllSection">Save</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <label>Services</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="manage-banner-section">
                                <button onclick="window.open('<?= $LOCATION_FILE_ARRAY['MASTER_CATEGORY_DELIVERALL'] ?>', '_blank')" class="manage-banner-btn manage-icon-btn">Manage Services for App Home Screen</button>
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
<? include_once('footer.php'); ?>
<script type="text/javascript" src="js/fancybox.umd.js"></script>
<script type="text/javascript" src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="../assets/js/modal_alert.js"></script>
<script type="text/javascript">
    function editDeliverAllTitle(action) {
        $('#deliverall_title_modal_action').html(action);
        $('#DeliverAllTitle_Modal').modal('show');
    }

    function saveDeliverAllTitle() {
        if ($('#vDeliverAllTitle_<?= $default_lang ?>').val() == "") {
            $('#vDeliverAllTitle_<?= $default_lang ?>_error').show();
            $('#vDeliverAllTitle_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vDeliverAllTitle_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vDeliverAllTitle_Default').val($('#vDeliverAllTitle_<?= $default_lang ?>').val());
        $('#vDeliverAllTitle_Default').closest('.row').removeClass('has-error');
        $('#vDeliverAllTitle_Default-error').remove();
        $('#DeliverAllTitle_Modal').modal('hide');
    }

    $('#saveDeliverAllSection').click(function() {
        var vDeliverAllTitleArr = $('[name^="vDeliverAllTitle_"]').serializeArray();
        var vTitleArr = {};
        $.each(vDeliverAllTitleArr, function(key, value) {
            if(value.name != "vDeliverAllTitle_Default") {
                var name_key = value.name.replace('vDeliverAllTitle', 'vTitle');
                vTitleArr[name_key] = value.value;
            }
        });

        var postData = new FormData();
        postData.append('vTitleArr', JSON.stringify(vTitleArr));

        postData.append('ViewType', 'GridView');
        postData.append('ServiceType', 'DeliverAll');

        saveHomeScreenData('saveDeliverAllSection', postData);
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
</script>
</body>
<!-- END BODY-->
</html>