<?php
include_once("common.php");

	//added by SP for cubex changes on 07-11-2019
if($THEME_OBJ->isXThemeActive() == 'Yes') {
		$PagesData = $obj->MySQLSelect("SELECT iPageId FROM `pages` WHERE iPageId = 52 AND eStatus = 'Active' ");
	  if(scount($PagesData)<=0) {
			header("location: Page-Not-Found");exit;
		}
        include_once("cx-about.php");
        exit;
	}

	$script="About Us";
	$meta = $STATIC_PAGE_OBJ->FetchStaticPage(1,$_SESSION['sess_lang']);
	 //echo "<pre>";print_r($_);exit;
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
<title>
<?=$meta['meta_title'];?>
</title>
<meta name="keywords" value="<?=$meta['meta_keyword'];?>"/>
<meta name="description" value="<?=$meta['meta_desc'];?>"/>
<!-- Default Top Script and css -->
<?php include_once("top/top_script.php");?>
<!-- End: Default Top Script and css-->
</head>
<body>
<div id="main-uber-page">
  <!-- Left Menu -->
  <?php include_once("top/left_menu.php");?>
  <!-- End: Left Menu-->
  <!-- home page -->
  <!-- Top Menu -->
  <?php include_once("top/header_topbar.php");?>
  <!-- End: Top Menu-->
  <!-- contact page-->
  <div class="page-contant">
    <div class="page-contant-inner">
      <h2 class="header-page trip-detail">
        <?=$meta['page_title'];?>
      </h2>
      <!-- trips detail page -->
      <div class="static-page">
        <?=$meta['page_desc'];?>
      </div>
    </div>
  </div>
  <!-- home page end-->
  <!-- footer part -->
  <?php include_once('footer/footer_home.php');?>
  <!-- End:contact page-->
  <div style="clear:both;"></div>
</div>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->
<!-- Powered by V3Cube.com -->
</body>
</html>
