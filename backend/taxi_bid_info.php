<?php
include_once("common.php");

$vCode = 'EN';
$db_about = $STATIC_PAGE_OBJ->FetchStaticPage($TAXIBID_OBJ->taxiBidInfoPageId, $vCode);
$page_title = $db_about['page_title'];
$pagesubtitle = $db_about[0]["tPageDesc_" . $vCode];
if (empty($pagesubtitle["tPageDesc_" . $vCode])) {
    $vCode = 'EN';
    $db_about = $STATIC_PAGE_OBJ->FetchStaticPage($TAXIBID_OBJ->taxiBidInfoPageId, $vCode);
    $page_title = $db_about['page_title'];
}
$pagesubtitle_lang = $pagesubtitle;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
</head>
<style>
    .static-page{
        padding : 60px 20px 20px 20px;
    }
</style>
<body>

<!--<h2 class="header-page trip-detail"><?php /*= $page_title; */?></h2>-->
<div class="static-page">
    <?= $pagesubtitle_lang; ?>
</div>
</body>
</html>
