<?php

$showSignRegisterLinks = 1;
$table_name = getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang'];
$eFor = 'DeliverAll';
$ride_data = array();

if (ENABLE_DYNAMIC_CREATE_PAGE == "Yes") {
    $sql_ufx_dynamic = " AND iVehicleCategoryId = " . $_REQUEST['iVehicleCategoryId'];
    $ride_data_query = "SELECT * FROM " . $table_name . " WHERE eFor = '" . $eFor . "'" . $sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);


    $catname = getCatNameForTitle($_REQUEST['iVehicleCategoryId']);
}

$emptyData = 0;
if (empty($ride_data) || empty($ride_data[0]['lBannerSection'])) {
    if ($THEME_OBJ->isCJXDoctorv2ThemeActive() == 'Yes') {
        $emptyData = 1;
    }
    $sql_ufx_dynamic = " AND iVehicleCategoryId = 0";
    $ride_data_query = "SELECT * FROM " . $table_name . " WHERE eFor = '" . $eFor . "'" . $sql_ufx_dynamic;
    $ride_data = $obj->MySQLSelect($ride_data_query);
}

$banner_section = json_decode($ride_data[0]['lBannerSection'], true);
if (empty($banner_section['title_' . $vCode])) {
    $vCode = 'EN';
}

$inner_key = array('menu_title_', 'title_', 'sub_title_', 'desc_', 'img_', 'title_first_', 'desc_first_', 'img_first_', 'title_sec_', 'desc_sec_', 'img_sec_', 'title_third_', 'desc_third_', 'img_third_', 'title_fourth_', 'desc_fourth_', 'img_fourth_', 'title_fifth_', 'desc_fifth_', 'img_fifth_', 'title_six_', 'desc_six_', 'img_six_', 'main_title_', 'main_desc_', 'img2_', 'hiw_img1_', 'hiw_img2_', 'hiw_img3_', 'hiw_img4_', 'hiw_img5_', 'hiw_img6_', 'cuisines_img1_', 'cuisines_img2_', 'cuisines_img3_', 'cuisines_img4_', 'cuisines_img5_', 'cuisines_img6_');

$banner_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lBannerSection'], true), $vCode, $inner_key);
$how_it_work_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lHowitworkSection'], true), $vCode, $inner_key);
$call_section = $LANG_OBJ->checkOtherLangDataExist(json_decode($ride_data[0]['lCalltobookSection'], true), $vCode, $inner_key);

$key_arr = array("#SUPPORT_PHONE#", "#SUPPORT_ADDRESS#", "#SUPPORT_EMAIL#", "#ANDROID_APP_LINK#", "#IPHONE_APP_LINK#");
$val_arr = array($SUPPORT_PHONE, $COMPANY_ADDRESS, $SUPPORT_MAIL, $ANDROID_APP_LINK, $IPHONE_APP_LINK);
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
              <img class = "imageVisibilityHidden resizeImageWithJs " attr-image-src="<?php echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $banner_section['img_' . $vCode]; ?>" alt="<?php echo $banner_section['img_alt_'.$vCode];?>" loading="lazy">
        </div>
        </div>
            <div class="categories-block">
                <div class="categories-caption active">
                    <h2><?php echo $banner_section['title_' . $vCode]; ?></h2>
                    <?php echo $banner_section['desc_' . $vCode]; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- End -->
    <?php if ($THEME_OBJ->isCJXDoctorv2ThemeActive() != 'Yes') { ?>
    <?php
    $tMessage_call = $call_section['desc_' . $vCode];
    $tMessage_call = str_replace($key_arr, $val_arr, $tMessage_call); ?>
    <section class="ordernow page-section" id="ordernow">
        <div class="ordernow-inner">
            <div class="ordernow-left servicePage2 shine">
                <img class = "imageVisibilityHidden resizeImageWithJs wow slideInLeft" data-wow-delay="0ms" data-wow-duration="1500ms" attr-image-src="<?php echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $call_section['img_' . $vCode]; ?>" alt="<?php echo $call_section['img_alt_'.$vCode];?>" loading="lazy">
            </div>
            <div class="ordernow-right">
                <div class="ordernow-caption">
                    <strong><?php echo $call_section['title_' . $vCode]; ?></strong>
                    <?php echo $tMessage_call; ?>
                    <a href="order-items" class="book-btn"><?= $langage_lbl['LBL_ORDER_ONLINE']; ?></a>
                </div>
            </div>
        </div>
    </section>

<?php } ?>
<script type="text/javascript">
    /*--------------------- when click on order now button redirect with id  --------------------*/
    document.getElementsByClassName("book-btn")[0].addEventListener('click', function (event) {
        event.preventDefault();
        const href = $(this).attr('href');
        const $id = window.location.href.split('/').pop();
        window.location.href = '<?php echo $tconfig['tsite_url']; ?>' + href + '/' + $id;
    });
    /*--------------------- when click on order now button redirect with id   --------------------*/
</script>

<script>
    $(document).ready(function ()
    {
        resizeImage("servicePage1", "attr-image-src" , "imageSrc");
        resizeImage("servicePage2", "attr-image-src" , "imageSrc");

    });
</script>