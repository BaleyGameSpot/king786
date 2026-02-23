<?php
include_once('../common.php');

if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == "Yes" || $THEME_OBJ->isProKXThemeActive() == "Yes") {
    include_once 'category_content_page.php';
    exit;
} elseif ($THEME_OBJ->isProSPThemeActive() == "Yes" || $THEME_OBJ->isProBTYAIOThemeActive() == "Yes") {
    include_once 'category_content_page_uberx.php';
    exit;
} elseif ($MODULES_OBJ->isCubeXGcApp()) {
    include_once 'category_content_page_cubexgc.php';
    exit;
}


?>