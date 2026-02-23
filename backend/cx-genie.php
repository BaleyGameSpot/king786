<?php
include_once("common.php");

$showSignRegisterLinks = 1; 
//$table_name = 'content_cubex_details';
$table_name = getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang'];
$eFor = 'Genie';
$_SESSION["navigatedPage"] = $eFor;

$ride_data = array();
if(ENABLE_DYNAMIC_CREATE_PAGE=="Yes") {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = ".$_REQUEST['iVehicleCategoryId'];
    $ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'".$sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);
    $catname = getCatNameForTitle($_REQUEST['iVehicleCategoryId']);
}
if(empty($ride_data) || empty($ride_data[0]['lBannerSection'])) {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = 0";
    $ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'".$sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);
}

//$ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "' AND `iVehicleCategoryId` = '".$iVehicleCategoryId."'";
//$ride_data = $obj->MySQLSelect($ride_data_query);
//
//$banner_section = json_decode($ride_data[0]['lBannerSection'],true);
//if(empty($banner_section['title_'.$vCode])) {
//    $vCode = 'EN';
//}

$inner_key = array('menu_title_','title_','sub_title_','desc_','img_','title_first_','desc_first_','img_first_','title_sec_','desc_sec_','img_sec_','title_third_','desc_third_','img_third_','title_fourth_','desc_fourth_','img_fourth_','title_fifth_','desc_fifth_','img_fifth_','title_six_','desc_six_','img_six_','main_title_','main_desc_','img2_');

$banner_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lBannerSection'],true),$vCode,$inner_key);
$how_it_work_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lHowitworkSection'],true),$vCode,$inner_key);
$secure_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lSecuresafeSection'],true),$vCode,$inner_key);
$download_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lDownloadappSection'],true),$vCode,$inner_key);
$call_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lCalltobookSection'],true),$vCode,$inner_key);
$earn_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lEarnSection'],true),$vCode,$inner_key);
$calculate_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lCalculateSection'],true),$vCode,$inner_key);
$cartype_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lCartypeSection'],true),$vCode,$inner_key);
$service_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lServiceSection'],true),$vCode,$inner_key);
$benefit_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lBenefitSection'],true),$vCode,$inner_key);

$menutitleHow = !empty($how_it_work_section['menu_title_'.$vCode]) ? $how_it_work_section['menu_title_'.$vCode] : $how_it_work_section['title_'.$vCode]; 
$menutitleSecure = !empty($secure_section['menu_title_'.$vCode]) ? $secure_section['menu_title_'.$vCode] : $secure_section['title_'.$vCode]; 
$menutitleDown = !empty($download_section['menu_title_'.$vCode]) ? $download_section['menu_title_'.$vCode] : $download_section['title_'.$vCode]; 
$menutitleCall = !empty($call_section['menu_title_'.$vCode]) ? $call_section['menu_title_'.$vCode] : $call_section['title_'.$vCode]; 
$menutitleEarn = !empty($earn_section['menu_title_'.$vCode]) ? $earn_section['menu_title_'.$vCode] : $earn_section['title_'.$vCode]; 
$menutitleCalc = !empty($calculate_section['menu_title_'.$vCode]) ? $calculate_section['menu_title_'.$vCode] : $calculate_section['title_'.$vCode]; 
$menutitleCar = !empty($cartype_section['menu_title_'.$vCode]) ? $cartype_section['menu_title_'.$vCode] : $cartype_section['title_'.$vCode];
$menutitleService = !empty($service_section['menu_title_'.$vCode]) ? $service_section['menu_title_'.$vCode] : $service_section['title_'.$vCode];
$menutitleBenefit = !empty($benefit_section['menu_title_'.$vCode]) ? $benefit_section['menu_title_'.$vCode] : $benefit_section['title_'.$vCode];

$btitle = $bdesc = $bimg = array();
if(!empty($benefit_section['title_first_'.$vCode])) {
    $btitle[] = $benefit_section['title_first_'.$vCode];
    $bdesc[] = $benefit_section['desc_first_'.$vCode];
    $bimg[] = $benefit_section['img_first_'.$vCode];
}
if(!empty($benefit_section['title_sec_'.$vCode])) {
    $btitle[] = $benefit_section['title_sec_'.$vCode];
    $bdesc[] = $benefit_section['desc_sec_'.$vCode];
    $bimg[] = $benefit_section['img_sec_'.$vCode];
}
if(!empty($benefit_section['title_third_'.$vCode])) {
    $btitle[] = $benefit_section['title_third_'.$vCode];
    $bdesc[] = $benefit_section['desc_third_'.$vCode];
    $bimg[] = $benefit_section['img_third_'.$vCode];
}
if(!empty($benefit_section['title_fourth_'.$vCode])) {
    $btitle[] = $benefit_section['title_fourth_'.$vCode];
    $bdesc[] = $benefit_section['desc_fourth_'.$vCode];
    $bimg[] = $benefit_section['img_fourth_'.$vCode];
}
if(!empty($benefit_section['title_fifth_'.$vCode])) {
    $btitle[] = $benefit_section['title_fifth_'.$vCode];
    $bdesc[] = $benefit_section['desc_fifth_'.$vCode];
    $bimg[] = $benefit_section['img_fifth_'.$vCode];
}
if(!empty($benefit_section['title_six_'.$vCode])) {
    $btitle[] = $benefit_section['title_six_'.$vCode];
    $bdesc[] = $benefit_section['desc_six_'.$vCode];
    $bimg[] = $benefit_section['img_six_'.$vCode];
}
$key_arr = array("#SUPPORT_PHONE#","#SUPPORT_ADDRESS#","#SUPPORT_EMAIL#","#ANDROID_APP_LINK#","#IPHONE_APP_LINK#");
$val_arr = array($SUPPORT_PHONE,$COMPANY_ADDRESS,$SUPPORT_MAIL,$ANDROID_APP_LINK,$IPHONE_APP_LINK);
?>

<!-- *************banner section start************* -->
<section class="banner-section taxi-app bannermenu">
    <div class="tab-row-holding">
        <ul class="tab-row">
            <li data-src="1" class="tab active">
                <a href="#how-it-works"><?= $menutitleHow ?></a>
            </li>
            <li data-src="2" class="tab">
                <a href="#our-benefits"><?= $menutitleBenefit ?></a>
            </li>
            <li data-src="4" class="tab">
                <a href="#download-apps"><?= $menutitleDown ?></a>
            </li>
            
        </ul>
    </div>
    <div class="banner-section-inner">
        <div class="categories-block">
            <div class="categories-caption active">
                <h2><?php echo $banner_section['title_'.$vCode];?> <span><?php echo $banner_section['sub_title_'.$vCode];?></span></h2>
                <?php echo $banner_section['desc_'.$vCode];?>
            </div>
        </div>
    </div>
    <div class="banner-back">
        <div class="banner-image" id="1" style="background-image: url(<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>); display: block;"></div>
    </div>
</section>
<!-- *************banner section end************* -->

<!-- *************hot it works section start************* -->
<section class="howitworks page-section" id="how-it-works">
    <div class="howitworks-inner">
        <div class="horizonatal-title">
            <h3><?php echo $how_it_work_section['title_'.$vCode];?></h3>
            <strong><?php echo $how_it_work_section['subtitle_'.$vCode];?></strong>
        </div>
        <?php echo !empty($how_it_work_section['desc_'.$vCode]) ? $how_it_work_section['desc_'.$vCode] : "";?>
            <!-- How it Works sub Topics -->
            <ul>
            <?php for ($i=1; $i <= 4; $i++) { ?>
                <?php if (!empty($how_it_work_section['hiw_title'.$i.'_'.$vCode]) && !empty($how_it_work_section['hiw_desc'.$i.'_'.$vCode])) { ?>


                    <li>
                        <i><img alt="" class="proc_ico" src="<?php echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$how_it_work_section['hiw_img'.$i.'_'.$vCode];?>" xss="removed">  </i>
                        <div class="works-caption"><strong><?php echo $how_it_work_section['hiw_title'.$i.'_'.$vCode];?></strong>
                        <p><?php echo $how_it_work_section['hiw_desc'.$i.'_'.$vCode];?></p>
                        </div>
                    </li>

                <?php } ?>
            <?php } ?>
            </ul>
            <!-- How it Works sub Topics End -->        
    </div>
   
</section>
<!-- *************hot it works section end************* -->

<!-- ************* benefits section section start ************* -->
<section class="benefits page-section" id="our-benefits">
    <div class="benefits-inner">
        <div class="horizonatal-title">
            <h3><?= $benefit_section['main_title_'.$vCode]; ?></h3>
            <strong><?= $benefit_section['main_desc_'.$vCode]; ?></strong>
        </div>    
        <div class="benefits-row">
            <div class="benefits-left">
                <ul>
                    <?php if(!empty($btitle[0])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[0]; ?>" alt=""></i>
                        <strong><?= $btitle[0]; ?></strong>
                        <p><?= $bdesc[0]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[2])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[2]; ?>" alt=""></i>
                        <strong><?= $btitle[2]; ?></strong>
                        <p><?= $bdesc[2]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[4])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[4]; ?>" alt=""></i>
                        <strong><?= $btitle[4]; ?></strong>
                        <p><?= $bdesc[4]; ?></p>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="benefits-middle data-middle">
                <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$benefit_section['img_'.$vCode]; ?>" alt="">
            </div>
            <div class="benefits-right">
                <ul>
                    <?php if(!empty($btitle[1])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[1]; ?>" alt=""></i>
                        <strong><?= $btitle[1]; ?></strong>
                        <p><?= $bdesc[1]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[3])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[3]; ?>" alt=""></i>
                        <strong><?= $btitle[3]; ?></strong>
                        <p><?= $bdesc[3]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[5])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[5]; ?>" alt=""></i>
                        <strong><?= $btitle[5]; ?></strong>
                        <p><?= $bdesc[5]; ?></p>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- ************* benefits section section end ************* -->

<!-- *************download section section start************* -->
<?php
$tMessage_link1 = $download_section['link1_'.$vCode];
$tMessage_link1 = str_replace($key_arr, $val_arr, $tMessage_link1);
$tMessage_link2 = $download_section['link2_'.$vCode];
$tMessage_link2 = str_replace($key_arr, $val_arr, $tMessage_link2); 
?>
<section class="get_app_area sec_pad page-section" id="download-apps">
    <div class="get_app_area-inner">
        <div class="get_app_area-left">
            <div class="get_app_content">
                <div class="section_title">
                    <h2><?php echo $download_section['title_'.$vCode];?></h2>
                </div>
                <?php echo $download_section['desc_'.$vCode];?>
                <a href="<?= $tMessage_link1; ?>" class="app_btn slider_btn" target="_blank"><img src="assets/img/apptype/<?php echo $template;?>/play-store.png" alt=""><?=$langage_lbl['LBL_GOOGLE_PLAY']; ?></a>
                <a href="<?= $tMessage_link2; ?>" class="app_btn_two slider_btn" target="_blank"><img src="assets/img/apptype/<?php echo $template;?>/apple-store.png" alt=""><?=$langage_lbl['LBL_APP_STORE']; ?></a>
            </div>
        </div>
        <div class="get_app_area-right app_image">
            <div class="image_first">
                <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_'.$vCode]; ?>" alt="">
                <div class="shadow_bottom"></div>
            </div>
            <div class="image_two">
                <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img2_'.$vCode]; ?>" alt="">
                <div class="shadow_bottom"></div>
            </div>
        </div>
    </div>
</section>
<!-- *************download section section end************* -->

<!-- *************call to section end************* -->
<?php
$tMessage_call = $call_section['desc_'.$vCode];
$tMessage_call = str_replace($key_arr, $val_arr, $tMessage_call); ?>

<!-- *************call to section end************* -->