<section class="app-download-links">
    <?php if(!empty($IPHONE_APP_LINK)) { ?>
    <a href="<?= $IPHONE_APP_LINK ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-ios.png" alt="User IOS App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $apple_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $langage_lbl['LBL_RIDER_NAME_TXT_ADMIN'] ?></div>
            </div>
        </div>
    </a>
    <?php } if(!empty($IPHONE_APP_LINK_DRIVER)) { ?>
    <a href="<?= $IPHONE_APP_LINK_DRIVER ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-ios.png" alt="Driver IOS App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $apple_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] ?></div>
            </div>
        </div>
    </a>
    <?php } if(!empty($IPHONE_APP_LINK_STORE)) { ?>
    <a href="<?= $IPHONE_APP_LINK_STORE ?>" target="_blank">
        <div class="app-links">
            <div class="app-store-img">
                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_url'] ?>assets/img/download-app-ios.png" alt="Store IOS App">
            </div>
            <div class="app-store-content">
                <div class="app-store-name"><?= $apple_store_name ?></div>
                <div class="app-name"><?= $SITE_NAME . ' ' . $store_txt ?></div>
            </div>
        </div>
    </a>
    <?php } ?>
</section>