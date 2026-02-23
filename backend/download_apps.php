<?php
include_once("common.php");

$PagesData = $obj->MySQLSelect("SELECT iPageId FROM `pages` WHERE iPageId = 61 AND eStatus = 'Active' ");
if(count($PagesData) <= 0) {
    header("location: Page-Not-Found");exit;
}

$vCode = $_SESSION['sess_lang'];
$meta = $STATIC_PAGE_OBJ->FetchStaticPage(61, $vCode);

$apple_store_name = "Apple Store";
$google_play_store_name = "Google Play";

$store_txt = $langage_lbl['LBL_STORE'];
$ServiceDataArr = json_decode(ServiceData, true);
if(count($ServiceDataArr) == 1 && $ServiceDataArr[0]['iServiceId'] == 1) {
    $store_txt = $langage_lbl['LBL_RESTAURANT'];
}

ob_start();
include_once "download_apps_android.php";
$android_app_html = ob_get_clean();

ob_start();
include_once "download_apps_ios.php";
$ios_app_html = ob_get_clean();

$support_email = '<a href="mailto:' . $SUPPORT_MAIL . '">' . $SUPPORT_MAIL . '</a>';
$meta['page_desc'] = str_replace(["#ANDROID_APPS#","#IOS_APPS#","#SUPPORT_EMAIL#"], [$android_app_html, $ios_app_html, $support_email], $meta['page_desc']);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content="<?=$meta['meta_keyword'];?>"/>
    <meta name="description" content="<?=$meta['meta_desc'];?>"/>
    <title><?=$meta['meta_title'];?></title>

    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
    <style>
        .gen-cms-page .static-page strong{
            font-size: 22px;
            font-weight: 500;
        }
    </style>
</head>
<body>
     <!-- home page -->
    <div id="main-uber-page">
        <!-- Left Menu -->
        <?php include_once("top/left_menu.php");?>
        <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <div class="gen-cms-page">
            <div class="gen-cms-page-inner">
                <h2 class="header-page"><?=$meta['page_title'];?></h2>
                <div class="static-page">
                    <?= $meta['page_desc']; ?>
                </div>
            </div>
        </div>
        <!-- footer part -->
        <?php include_once('footer/footer_home.php');?>
        <!-- footer part end -->
        <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <!-- End: Footer Script -->
</body>
</html>
