<?php
include_once("common.php");

$showSignRegisterLinks = 1;
$table_name = getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang']; 
$eFor = 'Business';
$ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'";
$ride_data = $obj->MySQLSelect($ride_data_query);
if(empty($ride_data)){
    header('Location:' . $tconfig['tsite_url'] . 'Page-Not-Found');
    exit();
}
$banner_section = json_decode($ride_data[0]['lBannerSection'],true);
if(empty($banner_section['title_'.$vCode])) {
    $vCode = 'EN';
}

$inner_key = array('menu_title_','title_','sub_title_','desc_','img_','title_first_','desc_first_','img_first_','title_sec_','desc_sec_','img_sec_','title_third_','desc_third_','img_third_','title_fourth_','desc_fourth_','img_fourth_','title_fifth_','desc_fifth_','img_fifth_','title_six_','desc_six_','img_six_','main_title_','main_desc_','img2_');

$banner_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lBannerSection'],true),$vCode,$inner_key);
$how_it_work_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lHowitworkSection'],true),$vCode,$inner_key);

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?=$SITE_NAME?> | <?= $langage_lbl['LBL_FOOTER_LINK_BUSINESS'] ?></title>
	<!--<title><?php echo $meta_arr['meta_title'];?></title>
	<meta name="keywords" value="<?=$meta_arr['meta_keyword'];?>"/>
	<meta name="description" value="<?=$meta_arr['meta_desc'];?>"/>-->
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
</head>
<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>
        <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
		<?php include_once("top/header.php");?>
        <!-- End: First Section -->
<div class="common-inner-heading-section">
    <div class="common-inner-heading-section-inner">
        <h2 class="common-inner-heading"><?= $langage_lbl['LBL_FOOTER_LINK_BUSINESS'] ?></h2>
    </div>
</div>
<!-- *************banner section start************* -->
<section class="banner-section taxi-app bannermenu">
    <div class="banner-section-inner">
        <div class="banner-back">
            <div class="banner-image" id="1" style="display: block;">
                <div class="banner-image">
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>" alt="">
                </div>
            </div>
        </div>
        <div class="categories-block">
            <div class="categories-caption active">
                <h2><?= $banner_section['title_'.$vCode];?></h2>
                <p><?= str_replace("type=service provider", "type=provider", $banner_section['desc_'.$vCode]);?></p>
            </div>
        </div>
    </div>
    
</section>
<!-- *************banner section end************* -->

<!-- *************hot it works section start************* -->
<section class="how-it-works-section taxi-variant otherservice page-section" id="how-it-works">
    <div class="how-it-works-section-inner">
        <div class="head-area">
            <h3><?php echo $how_it_work_section['title_'.$vCode];?></h3>
            <?php echo $how_it_work_section['desc_'.$vCode];?>
        </div>
    </div>
</section>
<!-- *************hot it works section end************* -->
<!-- *************download section section start************* -->
<section class="get_app_area sec_pad page-section" id="download-apps">
    <div class="get_app_area-inner">
        <div class="get_app_area-left">
            <div class="get_app_content">
                <div class="section_title">
                    <h2><?= $langage_lbl['LBL_DOWNLOAD_ANDROID_IOS_APPS_FOR_EARN_PAGE_TXT']; ?></h2>
                </div>
            </div>
        </div>
        <div class="get_app_area-right app_image">
            <?php if (!empty($IPHONE_APP_LINK)) { ?>
                <div class="image_first">
                    <a href="<?= $IPHONE_APP_LINK?>" target="_blank"><img src="assets/img/footer-ios-store.svg" alt="" ></a>
                </div>
            <?php } ?>
            <?php if (!empty($ANDROID_APP_LINK)) { ?>
                <div class="image_two">
                    <a href="<?= $ANDROID_APP_LINK ?>" target="_blank"><img src="assets/img/footer-google-play.svg" alt=""></a>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
<!-- *************download section section end************* -->
 <!-- home page end-->
<!-- footer part -->
<?php include_once('footer/footer_home.php');?>

<div style="clear:both;"></div>
 <?php if($template!='taxishark'){?>
 </div>
 <?php } ?>
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->
</body>
</html>
