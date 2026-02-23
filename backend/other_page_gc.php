<?php
include_once("common.php");

$table_name = getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang'];
$eFor = isset($_REQUEST['eFor']) ? $_REQUEST['eFor'] : '';
$eUserType = isset($_REQUEST['eUserType']) ? $_REQUEST['eUserType'] : '';

$page_content = $obj->MySQLSelect("SELECT * FROM $table_name WHERE eFor = '$eFor' AND eUserType = '$eUserType'");

$inner_key = array('title_','sub_title_','desc_','img_');

$banner_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($page_content[0]['lBannerSection'],true),$vCode,$inner_key);
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?=$SITE_NAME?> | <?php echo $page_content[0]['vMetaTitle']; ?></title>
   
    <meta name="keywords" value="<?= $page_content[0]['tMetaKeyword']; ?>" />
    <meta name="description" value="<?= $page_content[0]['tMetaDescription']; ?>" />
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
</head>
<body id="wrapper">
<!-- home page -->
<!-- home page -->

<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- First Section -->
    <?php include_once("top/header.php"); ?>
    <!-- home page end-->
    <!-- *************banner section start************* -->
    <section class="banner-split-section">
        <div class="banner-split-section-inner">
            <div class="banner-split-left">
                <div class="banner-split-title"><h6><?= $banner_section['title_' . $vCode] ?></h6></div>
                <div class="banner-split-subtitle"><h1><?= $banner_section['sub_title_' . $vCode] ?></h1></div>
                <a href="download-apps" class="banner-split-btn"><?= $langage_lbl['LBL_DOWNLOAD_APP_BTN_TXT'] ?></a>
            </div>
            <div class="banner-split-right">
                <div class="banner-split-img">
                    <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=550&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $banner_section['img_' . $vCode] ?>" alt="<?= $banner_section['img_alt_' . $vCode] ?>">
                </div>
            </div>
        </div>
    </section>
    <!-- *************banner section end************* -->

    <section class="page-content-description">
        <div class="page-content-description-inner">
            <?= $banner_section['desc_' . $vCode] ?>
        </div>
    </section>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
</div>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
</body>
</html>