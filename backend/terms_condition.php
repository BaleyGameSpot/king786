<?php
include_once("common.php");

$PagesData = $obj->MySQLSelect("SELECT iPageId FROM `pages` WHERE iPageId = 4 AND eStatus = 'Active' ");
if(scount($PagesData)<=0) {
	  header("location: Page-Not-Found");exit;
}
$vCode = $_SESSION['sess_lang'];
$isFromApp = isset($_REQUEST['isFromApp']) ? trim($_REQUEST['isFromApp']) : '';
if($isFromApp == 'Yes'){
    $vCode = isset($_REQUEST['vGeneralLang']) ? trim($_REQUEST['vGeneralLang']) : $_SESSION['sess_lang'];
}

$script="Terms Condition";

$meta = $STATIC_PAGE_OBJ->FetchStaticPage(4,$vCode);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$meta['meta_title'];?></title>
	<meta name="keywords" content="<?=$meta['meta_keyword'];?>"/>
	<meta name="description" content="<?=$meta['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
    <?php if($isFromApp == "Yes") { ?>
    <style type="text/css">
        html {
            background-color: #fff;
        }

        .gen-cms-page {
            padding: 0 !important;
        }

        .gen-cms-page .static-page ul {
            padding-inline-start: 20px;
        }

        .gen-cms-page ul li {
            text-align: justify;
        }
    </style>
    <?php } ?>
</head>
<body>
    <!-- home page -->
    <div id="main-uber-page">
    <!-- Left Menu -->
	<?php if($isFromApp == 'Yes') { ?>
         <?php if($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
			<div class="gen-cms-page" style="background-color:#fff;padding:20px 0;">
				<div class="gen-cms-page-inner">
			<?php } else { ?>
				<div class="page-contant" style="background-color:#fff;padding:20px 0;">
				<div class="page-contant-inner">
			<?php } ?>
		      <div class="static-page" style="margin: 0;">
		        <?=$meta['page_desc'];?>
		      </div>
		    </div>
		</div>

	<?php } else { ?>
	    <?php include_once("top/left_menu.php");?>
	    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- contact page-->
         <?php if($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
			<div class="gen-cms-page">
				<div class="gen-cms-page-inner">
					<h2 class="header-page">
			<?php } else { ?>
		<div class="page-contant">
		<div class="page-contant-inner">
		      <h2 class="header-page trip-detail"><?php } ?><?=$meta['page_title'];?></h2>
		      <!-- trips detail page -->
		      <div class="static-page">
		        <?=$meta['page_desc'];?>
		      </div>
		    </div>
		</div>
	    <!-- footer part -->
	    <?php include_once('footer/footer_home.php');?>
	    <!-- footer part end -->
	<?php } ?>
    <!-- End:contact page-->
    <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
	<!-- End: Footer Script -->
</body>
</html>
