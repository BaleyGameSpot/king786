<?php
include_once('../common.php');

if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == "Yes" || $THEME_OBJ->isProKXThemeActive() == "Yes") {
    include_once 'home_content_cubejekxv3pro_action.php';
    exit;

} elseif ($THEME_OBJ->isPXRDProThemeActive() == "Yes" || $THEME_OBJ->isProXRDThemeActive() == "Yes" ) {
    include_once 'home_content_pxrdpro_action.php';
    exit;

} elseif ($THEME_OBJ->isPXTProThemeActive() == "Yes" || $THEME_OBJ->isProPTXThemeActive() == "Yes") {
    include_once 'home_content_pxtpro_action.php';
    exit;

} elseif ($THEME_OBJ->isPXCProThemeActive() == "Yes") {
    include_once 'home_content_pxcpro_action.php';
    exit;

} elseif ($THEME_OBJ->isProSPThemeActive() == "Yes") {
    include_once 'home_content_prosp_action.php';
    exit;

} elseif($THEME_OBJ->isProDeliverallThemeActive() == "Yes"){
    include_once 'home_content_prodeliverall_action.php';
    exit;

} elseif($THEME_OBJ->isProDeliveryThemeActive() == "Yes"){
    include_once 'home_content_prodelivery_action.php';
    exit;

} elseif($THEME_OBJ->isProDeliveryKingThemeActive() == "Yes"){
    include_once 'home_content_prodeliveryking_action.php';
    exit;

} elseif($THEME_OBJ->isProRideShareThemeActive() == "Yes"){
    include_once 'home_content_prors_action.php';
    exit;

} elseif($THEME_OBJ->isProBuySellRentThemeActive() == "Yes"){
    include_once 'home_content_probsr_action.php';
    exit;

} elseif($THEME_OBJ->isProMSThemeActive() == "Yes"){
    include_once 'home_content_proms_action.php';
    exit;

} elseif ($THEME_OBJ->isProBTYAIOThemeActive() == "Yes") {
    include_once 'home_content_pro_beauty_aio_action.php';
    exit;

} elseif ($THEME_OBJ->isProCubeXGCThemeActive() == "Yes") {
    include_once 'home_content_cubexgc_action.php';
    exit;

}
?>