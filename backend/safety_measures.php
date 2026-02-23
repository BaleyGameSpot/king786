<?php
include_once("common.php");

$PagesData = $obj->MySQLSelect("SELECT iPageId FROM `pages` WHERE iPageId = 54 AND eStatus = 'Active' ");
if(scount($PagesData)<=0) {
      header("location: Page-Not-Found");exit;
}

$fromapp = isset($_REQUEST['fromapp']) ? $_REQUEST['fromapp'] : "No";
$fromweb = isset($_REQUEST['fromweb']) ? $_REQUEST['fromweb'] : "No";
$iServiceIdNew = isset($_REQUEST['iServiceId']) ? $_REQUEST['iServiceId'] : "1";
$iServiceIdNew = base64_decode($iServiceIdNew);

if(strtoupper($fromweb) == 'YES') {
    $lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
} else {
    $lang = isset($_REQUEST['fromlang']) ? $_REQUEST['fromlang'] : "EN";
}
if(empty($lang)) $lang = "EN";

if($THEME_OBJ->isCubeJekXv3ThemeActive() == "Yes") {
    $safetyimg = "/webimages/icons/DefaultImg/ic_safety.png";
} else {
    $safetyimg = "/webimages/icons/DefaultImg/ic_store_safety.png";
}

$safetyimgUrl = (file_exists($tconfig["tpanel_path"].$safetyimg)) ? $tconfig["tsite_url"] . 'resizeImg.php?w=140&src=' . $tconfig["tsite_url"].$safetyimg : "";

$meta = $STATIC_PAGE_OBJ->FetchStaticPage(54,$lang);

$iCompanyId = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iCompanyId = base64_decode($iCompanyId);

$banner_images = 0;
if(!empty($iCompanyId))
{
    $company_data = $obj->MySQLSelect("SELECT eSafetyPractices FROM company WHERE iCompanyId = $iCompanyId");
    $eSafetyPractices = $company_data[0]['eSafetyPractices'];
    
    if($MODULES_OBJ->isEnableStorePhotoUploadFacility()) {
        $banner_data = $obj->MySQLSelect("SELECT * FROM store_wise_banners WHERE iCompanyId = ".$iCompanyId . " AND eStatus = 'Active' GROUP BY iUniqueId ORDER BY iUniqueId DESC");
        if(scount($banner_data) > 0) {
            $banner_images = 1;
        }
    }   
}

$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($lang, "1", $iServiceIdNew);

$show_images = 0;
// $eSafetyPractices = 'No';
if(($eSafetyPractices =='Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()) || ($MODULES_OBJ->isEnableStorePhotoUploadFacility() && $banner_images == 1)) { 
    $show_images = 1;
}

if($show_images == 0) {
    header('Location:profile');
    exit;
}

$topBarHeight = isset($_REQUEST['topBarHeight']) ? $_REQUEST['topBarHeight'] : "0";
$header_padding = "";
$header_margin = 'style="margin-top: 35px"';
if($topBarHeight > 0) {
    $header_padding = 'style="padding-top: calc(' . $topBarHeight . 'px - 46px)"';    
    $header_margin = 'style="margin-top: 70px"';
}

if(strtoupper($fromweb) == 'YES') {
    $header_margin = 'style="margin-top: 0"';
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$meta['meta_title'];?></title>
    <meta name="keywords" value="<?=$meta['meta_keyword'];?>"/>
    <meta name="description" value="<?=$meta['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <link rel="stylesheet" href="<?= $tconfig['tsite_url_main_admin'] ?>css/fancybox.css" />
    <!-- End: Default Top Script and css-->
    <style type="text/css">
        html, body {
            background-color: #ffffff;
        }

        .gen-cms-page {
            margin: 35px 0 0 0;
        }
        .banner-img-section {
            margin-bottom: 30px;
            display: flex;
            overflow-y: auto;
            padding: 10px;
        }

        .banner-img {
            width: 200px;
            height: auto;
            padding: 0;
            margin-right: 30px;
            flex: 1 0 auto;
            display: flex;
            max-width: 200px;
            max-height: 200px;
            -webkit-box-shadow: 0px 0px 5px 5px rgba(0,0,0,0.1);
            -moz-box-shadow: 0px 0px 5px 5px rgba(0,0,0,0.1);
            box-shadow: 0px 0px 5px 5px rgba(0,0,0,0.1);
            border-radius: 10px;
            transition: 0.3s;
        }

        .banner-img img {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .banner-img:hover {
            cursor: pointer;
            -webkit-box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            -moz-box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .banner-images-header-web {
            padding: 0 0 50px 10px;
            float: left;
            width: 100%;
        }
        .banner-images-header-web .banner-images-title-border {
            border-bottom: 5px solid #000000;
            width: 50px;
            margin: 0;
        }

        .banner-images-header-web .banner-images-title {
            color: #0D2366;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .banner-images-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            padding: 0;
            z-index: 1030;
        }

        .banner-images-content {
            color: #ffffff;
            font-size: 18px;
            padding: 8px 20px;
            float: left;
            width: 100%;
        }

        .banner-images-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            float: left;
            width: 100%;
            font-size: 18px;
            line-height: 1.5;
        }

        #close-action a {
            color: #ffffff !important;
            font-size: 16px;
            text-decoration: none;
        }

        <?php if(!($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes") ) { ?>
            .gen-cms-page h2.header-page {
                border: none;
            }

            .banner-img-section {
                display: block;
                text-align: center;
                margin-top: 1rem;
                padding-top: 0;
            }

            .banner-img {
                width: 30%;
                max-width: 30%;
                height: auto;
                max-height: fit-content;
                margin-bottom: 30px;
                display: inline-block;
                box-shadow: none;
                height: 300px;
                max-height: 300px;
            }

            .banner-img:nth-child(3n+3) {
                margin-right: 0
            }

            .banner-img img {
                -webkit-box-shadow: 0px 0px 5px 5px rgba(0,0,0,0.1);
                -moz-box-shadow: 0px 0px 5px 5px rgba(0,0,0,0.1);
                box-shadow: 0px 0px 5px 5px rgba(0,0,0,0.1);
                object-fit: cover;
            }

            .banner-images-title-border {
                border-bottom: 5px solid #007BFF;
                width: 50px;
                margin: 0;
                float: left;
            }

            #main-uber-page {
                height: 100vh;
                overflow-y: auto;
            }

            @media screen and (max-device-width: 767px) {
                .banner-img {
                    width: 45%;
                    max-width: 45%;
                    height: 250px;
                    max-height: 250px;
                    margin-bottom: 30px;
                }

                .banner-img:nth-child(3n+3) {
                    margin-right: 30px;
                }

                .banner-img:nth-child(2n+2) {
                    margin-right: 0
                }
            }

            @media screen and (max-device-width: 480px) {

                .banner-img-section {
                    display: block;
                    margin-bottom: 0
                }

                .banner-img {
                    width: 100%;
                    max-width: 100%;
                    height: auto;
                    max-height: fit-content;
                    margin-bottom: 30px;
                    margin-right: 0 !important;
                }

                .banner-img:last-child {
                    margin-bottom: 0
                }
            }
        <?php } ?>
        <?php if($THEME_OBJ->isCubeJekXv3ThemeActive() == "No") { ?>
            .header-page {
                font-size: 20px !important;
                font-weight: bold !important;
                line-height: 22px;
                color: #000000 !important;
                padding-bottom: 0 !important;
            }

            .header-page:after, .gen-cms-page ul li:before {
                display: none;
            }

            .gen-cms-page p {
                line-height: 22px;
            }

            .gen-cms-page ul {
                list-style-type: disc;
                padding: 20px;
                padding-inline-start: 20px !important;
                border: 1px solid #E6E6E6;
                background-color: #F6F6F6;
                border-radius: 10px;
            }

            .gen-cms-page ul li {
                padding: 0;
                font-size: 15px;
                margin-bottom: 15px;
            }

            .gen-cms-page ul li strong {
                font-weight: bold;
                font-size: 18px;
            }

        <?php } ?>
    </style>
</head>
<body>
    <!-- home page -->
    <div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php if(strtoupper($fromweb) == "YES") { include_once("top/header_topbar.php"); } ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <?php if($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
    <div class="gen-cms-page" <?= $header_margin ?>>
        <?php if($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes" && strtoupper($fromapp) == "YES") { ?>
        <div class="banner-images-header" <?= $header_padding ?>>
            <div class="banner-images-content">
                <div class="banner-images-title">
                    <div>&nbsp;</div>
                    <div>&nbsp;</div>
                    <div id="close-action">
                        <a href="<?= $tconfig['tsite_url'] ?>assets/libraries/webview/failure.php?success=0&page_action=close">Close</a>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="gen-cms-page-inner">
            <?php if($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes") { ?>
            <div style="text-align: center; margin-bottom: 30px"><img src="<?= $safetyimgUrl ?>"></div>
            <h2 class="header-page"><?=$meta['page_title'];?></h2>
            <?php } ?>

            <?php } else { ?>
            <div class="page-contant">
                <div class="page-contant-inner">
                    <?php if($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes") { ?>
                    <h2 class="header-page trip-detail"><?=$meta['page_title'];?></h2>
                    <?php } ?>
                    
                    <?php } ?>
                    
                    <?php if(!($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes") && strtoupper($fromweb) != "YES") { ?>
                    <div class="banner-images-header" <?= $header_padding ?>>
                        <div class="banner-images-content">
                            <div class="banner-images-title">
                                <div>&nbsp;</div>
                                <div><?= $languageLabelsArr['LBL_RESTAURANT_TXT_ADMIN'] ?> Images</div>
                                <div id="close-action">
                                    <a href="<?= $tconfig['tsite_url'] ?>assets/libraries/webview/failure.php?success=0&page_action=close">Close</a>
                                </div>
                            </div>
                            <!-- <div class="banner-images-title-border"></div> -->
                        </div>
                    </div>
                    <?php } ?>

                    <?php if(strtoupper($fromweb) == "YES" && !($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes")) { ?>
                        <div class="banner-images-header-web">
                            <div class="banner-images-title"><?= $languageLabelsArr['LBL_RESTAURANT_TXT_ADMIN'] ?> Images</div>
                            <div class="banner-images-title-border"></div>
                        </div>
                    <?php } ?>

                    <?php if($banner_images == 1) { $img_count = $img_count1 = 1; ?>
                    <div class="banner-img-section">
                        <?php foreach ($banner_data as $banner) { ?>
                            <?php if(!empty($banner['vImage'])) { ?>
                                <div class="banner-img">
                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&h=400&src='.$tconfig['tsite_upload_images'].$banner['vImage'] ?>" data-fancybox="gallery" data-src="<?= $tconfig['tsite_upload_images'].$banner['vImage'] ?>">
                                </div>
                            <?php $img_count++;} ?>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    
                    <?php if($MODULES_OBJ->isEnableStoreSafetyProcedure() && $eSafetyPractices == "Yes") { ?>
                    <div class="static-page">
                        <?=$meta['page_desc'];?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!-- footer part -->
            <?php if(strtoupper($fromweb) == "YES") { include_once('footer/footer_home.php'); } ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <!-- End: Footer Script -->
    <script type="text/javascript" src="<?= $tconfig['tsite_url_main_admin'] ?>js/fancybox.umd.js"></script>
    <script>
        Fancybox.bind("[data-fancybox]", {
            Toolbar: {
                display: ["counter", "close"],
            },
        });
    </script>
</body>
</html>