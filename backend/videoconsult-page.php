<?php
include_once("common.php");
$showSignRegisterLinks = 1;
$table_name = getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang'];
$eFor = 'VideoConsult';
$_SESSION["navigatedPage"] = 'UberX';
if(isset($_REQUEST['iMedicalVehicleCategoryId']) && !empty($_REQUEST['iMedicalVehicleCategoryId'])){
    $sql_ufx_dynamic = " AND iVehicleCategoryId = ".$_REQUEST['iMedicalVehicleCategoryId'];
    $ride_data_query = "SELECT id FROM ".$table_name." WHERE eFor = '" . $eFor . "'".$sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);
    if(!empty($ride_data)) {
        $_REQUEST['iVehicleCategoryId'] = $_REQUEST['iMedicalVehicleCategoryId'];
    }
}
$ride_data = array();
if(ENABLE_DYNAMIC_CREATE_PAGE=="Yes") {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = ".$_REQUEST['iVehicleCategoryId'];
    $ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'".$sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);
}
if(empty($ride_data) || empty($ride_data[0]['lBannerSection'])) {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = 0";
    $ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'".$sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);
}
$banner_section = json_decode($ride_data[0]['lBannerSection'],true);
if(empty($banner_section['title_'.$vCode])) {
    $vCode = 'EN';
}
$inner_key = array('menu_title_','title_','sub_title_','desc_','img_','title_first_','desc_first_','img_first_','title_sec_','desc_sec_','img_sec_','title_third_','desc_third_','img_third_','title_fourth_','desc_fourth_','img_fourth_','title_fifth_','desc_fifth_','img_fifth_','title_six_','desc_six_','img_six_','main_title_','main_desc_','img2_');
$banner_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lBannerSection'],true),$vCode,$inner_key);
$how_it_work_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lHowitworkSection'],true),$vCode,$inner_key);

$key_arr = array("#SUPPORT_PHONE#","#SUPPORT_ADDRESS#","#SUPPORT_EMAIL#","#ANDROID_APP_LINK#","#IPHONE_APP_LINK#");
$val_arr = array($SUPPORT_PHONE,$COMPANY_ADDRESS,$SUPPORT_MAIL,$ANDROID_APP_LINK,$IPHONE_APP_LINK);
?>
<div class="common-inner-heading-section">
    <div class="common-inner-heading-section-inner">
        <h2 class="common-inner-heading"><?= $catname ?></h2>
    </div>
</div>
<section class="banner-section taxi-app bannermenu">
    <div class="banner-section-inner">
        <div class="banner-back">
            <div class="banner-image servicePage1 shine" id="1">
                <img class = "imageVisibilityHidden resizeImageWithJs " attr-image-src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>" alt="<?php echo $banner_section['img_alt_'.$vCode];?>" loading="lazy">
            </div>
        </div>
        <div class="categories-block">
            <div class="categories-caption active">
                <h2><?= $banner_section['title_'.$vCode];?></h2>
                <p><?= $banner_section['desc_'.$vCode];?></p>
            </div>
        </div>
    </div>
</section>
<!-- End -->

<section class="how-it-works-section taxi-variant otherservice page-section" id="how-it-works">
    <div class="how-it-works-section-inner">
        <div class="head-area">
            <h3><?php echo $how_it_work_section['title_'.$vCode];?></h3>
            <p><?php echo $how_it_work_section['desc_'.$vCode];?></p>
        </div>
    </div>
</section>
<script>
    $(document).ready(function ()
    {
        resizeImage("servicePage1", "attr-image-src" , "imageSrc");
    });
</script>
