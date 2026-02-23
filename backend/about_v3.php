<?php
include_once( "common.php" );
$vCode = $_SESSION['sess_lang'];
$showSignRegisterLinks = 1;

$isFromApp = isset($_REQUEST['isFromApp']) ? trim($_REQUEST['isFromApp']) : '';
if($isFromApp == 'Yes'){
    $vCode = isset($_REQUEST['vGeneralLang']) ? trim($_REQUEST['vGeneralLang']) : $_SESSION['sess_lang'];
}

$db_about = $STATIC_PAGE_OBJ->FetchStaticPage( 52, $_SESSION['sess_lang'] );
$page_title = $db_about['page_title'];
$pagesubtitle = json_decode( $db_about[0]['pageSubtitle'], true );
if ( empty( $pagesubtitle[ "pageSubtitle_" . $vCode ] ) ) {
    $vCode = 'EN';
    $db_about = $STATIC_PAGE_OBJ->FetchStaticPage( 52, $vCode );
    $page_title = $db_about['page_title'];
}
$pagesubtitle_lang = $pagesubtitle[ "pageSubtitle_" . $vCode ];

?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= ( isset( $_SESSION['eDirectionCode'] ) && $_SESSION['eDirectionCode'] != "" ) ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?= $db_about['meta_title']; ?></title>
    <meta name="keywords" content="<?= $db_about['meta_keyword']; ?>"/>
    <meta name="description" content="<?= $db_about['meta_desc']; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once( "top/top_script.php" ); ?>
    <!-- End: Default Top Script and css-->
    <?php if($isFromApp == "Yes") { ?>
    <style type="text/css">
        html {
            background-color: #fff;
        }

        .gen-cms-page {
            padding: 0 !important;
        }


        img{
            max-width: 100%;
            object-fit: cover;
            object-position: left;
        }
    </style>
    <?php } ?>
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <?php if($isFromApp == 'Yes') { ?>
        <?php if ( $THEME_OBJ->isXThemeActive() == 'Yes' ) { ?>
        <div class="gen-cms-page" style="background-color:#fff;padding:20px 0;">
        <div class="gen-cms-page-inner" style="min-height:auto;">
            <?php } else { ?>
                <div class="page-contant" style="background-color:#fff;padding:20px 0;">
                    <div class="page-contant-inner">
            <?php } ?>
                    <div class="static-page" style="margin:0">
                        <?= $pagesubtitle_lang; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php  } else { ?>
        <!-- Left Menu -->
        <?php include_once( "top/left_menu.php" ); ?>
        <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once( "top/header_topbar.php" ); ?>
        <!-- End: Top Menu-->
        <!-- contact page-->
        <?php if ( $THEME_OBJ->isXThemeActive() == 'Yes' ) { ?>
        <div class="gen-cms-page">
        <div class="gen-cms-page-inner">
            <h2 class="header-page">
                <?php } else { ?>
                <div class="page-contant">
                    <div class="page-contant-inner">
                        <h2 class="header-page trip-detail"><?php } ?><?= $page_title; ?></h2>
                        <!-- trips detail page -->
                        <p class="static-page">
                            <?= $pagesubtitle_lang; ?>
                        </p>
                    </div>
                </div>
                <!-- footer part -->
                <?php include_once( 'footer/footer_home.php' ); ?>
                <!-- footer part end -->
    <?php } ?>
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<!-- Footer Script -->
<?php include_once( 'top/footer_script.php' ); ?>
<!-- End: Footer Script -->
</body>
</html>