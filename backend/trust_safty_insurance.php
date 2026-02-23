<?php
include_once("common.php");
$PagesData = $obj->MySQLSelect("SELECT iPageId FROM `pages` WHERE iPageId = 7 AND eStatus = 'Active' ");
if(scount($PagesData)<=0) {
	header("location: Page-Not-Found");exit;
}
$vCode = $_SESSION['sess_lang'];
$isFromApp = isset($_REQUEST['isFromApp']) ? trim($_REQUEST['isFromApp']) : '';
if($isFromApp == 'Yes'){
    $vCode = isset($_REQUEST['vGeneralLang']) ? trim($_REQUEST['vGeneralLang']) : $_SESSION['sess_lang'];
}
$script="Trust Safty Insurance";
$meta = $STATIC_PAGE_OBJ->FetchStaticPage(7,$vCode);

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
</head>
<body>
     <!-- home page -->
    <div id="main-uber-page">
    <?php if($isFromApp != 'Yes') { ?>
	    <!-- Left Menu -->
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
			      <h2 class="header-page-b trip-detail"><?php } ?><?=$meta['page_title'];?></h2>
			      <!-- trips detail page -->
			      <div class="static-page">
			       <?=$meta['page_desc'];?>
			      </div>
			    </div>
			</div>
	    <!-- footer part -->
	    <?php include_once('footer/footer_home.php');?>
   	 <!-- footer part end -->
   	<?php } else { ?>
   		<?php if($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
			<div class="gen-cms-page" style="padding:10px 0;background-color:#fff;min-height: auto;">
			<div class="gen-cms-page-inner" style="min-height: auto;">
        <?php } else { ?>
			<div class="page-contant" style="padding:10px 0;background-color:#fff;min-height: auto;">
			<div class="page-contant-inner" style="min-height: auto;">
		<?php } ?>
				<div class="static-page" style="margin:0">
					<?=$meta['page_desc'];?>
				</div>
			</div>
			</div>
   		<?php } ?>
    <div style="clear:both;"></div>
</div>
<?php include_once('top/footer_script.php');?>
</body>
</html>
