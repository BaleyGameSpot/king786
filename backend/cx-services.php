<?php
include_once("common.php");
$showSignRegisterLinks = 1;
$lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
$innerPage = "Yes";
if (ENABLE_DYNAMIC_CREATE_PAGE == "Yes") {
    if ($THEME_OBJ->isMedicalServicev2ThemeActive() == "No") {
        $cPage = 1;
    }

    if( isset($_REQUEST['service-bid']))
    {
        $serviceBidPage = 1;
    }
    $getCategoryData = getSeviceCategoryDataForHomepage($_REQUEST['iVehicleCategoryId'], 0, 1);
    if(isset($_REQUEST['service-bid'])) {
        $vCatTitlejson = get_value("bidding_service", "vTitle", 'iBiddingId', $_REQUEST['iVehicleCategoryId'], '', 'true');
        $tTitleArray = json_decode($vCatTitlejson,true);
        $catname = $tTitleArray['vTitle_'. $lang];
    } else {
    $catname = get_value(getVehicleCategoryTblName(), "vCategory_" . $lang, 'iVehicleCategoryId', $_REQUEST['iVehicleCategoryId'], '', 'true');
    }
   
    //$catname = $getCategoryData[0]['vCatName'];
    if(empty($getCategoryData)){
        header('Location:' . $tconfig['tsite_url'] . 'Page-Not-Found');
        exit();
    }
}

if($getCategoryData[0]['eCatType'] == 'MoreDelivery') {
    $eFor = 'Delivery';
} else if($getCategoryData[0]['eCatType'] == 'Genie' || $getCategoryData[0]['eCatType'] == 'Runner') {
    $eFor = 'Genie';
} else if($getCategoryData[0]['eCatType'] == 'RentEstate' || $getCategoryData[0]['eCatType'] == 'RentCars' || $getCategoryData[0]['eCatType'] == 'RentItem') {
    $eFor = 'BuySellRent';
} else if($getCategoryData[0]['eCatType'] == 'MotoRide') {
    $eFor = 'Moto';
} else if($getCategoryData[0]['eCatType'] == 'DeliverAll' || $getCategoryData[0]['eCatType'] == 'Ride' || $getCategoryData[0]['eCatType'] == 'VideoConsult' || $getCategoryData[0]['eCatType'] == 'ServiceBid' || $getCategoryData[0]['eCatType'] == 'MoreService' || $getCategoryData[0]['eCatType'] == 'MedicalService' || $getCategoryData[0]['eCatType'] == 'RideShare' || $getCategoryData[0]['eCatType'] == 'TrackAnyService' || $getCategoryData[0]['eCatType'] == 'NearBy') {
    $eFor = $getCategoryData[0]['eCatType'];
} else {
    $eFor = 'Otherservice';
}

$table_name = getContentCMSHomeTable();
if(ENABLE_DYNAMIC_CREATE_PAGE=="Yes") {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = ".$_REQUEST['iVehicleCategoryId'];
    $ride_meta_query = "SELECT vMetaTitle,tMetaDescription,tMetaKeyword FROM ".$table_name." WHERE 1 = 1 AND eFor = '".$eFor."' ".$sql_ufx_dynamic;
    $ride_metadata = $obj->MySQLSelect($ride_meta_query);
}

if(empty($ride_metadata)) {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = 0";
    $ride_meta_query = "SELECT vMetaTitle,tMetaDescription,tMetaKeyword FROM ".$table_name." WHERE  1 = 1 AND eFor = '".$eFor."' ".$sql_ufx_dynamic;
    $ride_metadata = $obj->MySQLSelect($ride_meta_query);
}

?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <?php if (!empty($ride_metadata[0]['vMetaTitle'])) { ?>
        <title><?=$SITE_NAME?> | <?php echo $ride_metadata[0]['vMetaTitle']; ?></title>
    <?php } else { ?>
        <title><?=$SITE_NAME?> | <?= $catname ?></title>
    <?php } ?>
   
    <meta name="keywords" value="<?= $ride_metadata[0]['tMetaKeyword']; ?>" />
    <meta name="description" value="<?= $ride_metadata[0]['tMetaDescription']; ?>" />
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
</head>
<body id="wrapper">
<!-- home page -->
<!-- home page -->
<?php if ($template != 'taxishark'){ ?>
<div id="main-uber-page">
    <?php } ?>
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- First Section -->
    <?php include_once("top/header.php");

    /*echo "<pre>";
    echo $getCategoryData[0]['includeurl'];*/

    include_once($getCategoryData[0]['includeurl']);
    if ($THEME_OBJ->isProThemeActive() == 'Yes') {
        include_once('include_download_section.php');
    }
    ?>
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
    <?php if ($template != 'taxishark'){ ?>
</div>
<?php } ?>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
</body>
</html>