<section class="app-download-links">
    <?php if(!empty($ANDROID_APP_LINK)) { ?>
    <a href="<?= $ANDROID_APP_LINK ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-android.png" alt="User Android App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $google_play_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $langage_lbl['LBL_RIDER_NAME_TXT_ADMIN'] ?></div>
            </div>
        </div>
    </a>
    <?php } if(!empty($ANDROID_APP_LINK_DRIVER)) { ?>
    <a href="<?= $ANDROID_APP_LINK_DRIVER ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-android.png" alt="Driver Android App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $google_play_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] ?></div>
            </div>
        </div>
    </a>
    <?php } if(!empty($ANDROID_APP_LINK_STORE)) { ?>
    <a href="<?= $ANDROID_APP_LINK_STORE ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-android.png" alt="Store Android App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $google_play_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $store_txt ?></div>
            </div>
        </div>
    </a>
    <?php } if(!empty($ANDROID_APP_LINK_KIOSK)) { ?>
    <a href="<?= $ANDROID_APP_LINK_KIOSK ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-android.png" alt="Kiosk Android App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $google_play_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $langage_lbl['LBL_KIOSK'] ?></div>
            </div>
        </div>
    </a>
    <?php } if(!empty($ANDROID_APP_LINK_KIOSK_STORE)) { ?>
    <a href="<?= $ANDROID_APP_LINK_KIOSK_STORE ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-android.png" alt="Food Kiosk Android App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $google_play_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $langage_lbl['LBL_FOOD_KIOSK_TXT'] ?></div>
            </div>
        </div>
    </a>
    <?php } ?>
</section>