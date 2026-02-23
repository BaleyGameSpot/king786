<?php
include 'common.php';

$lang = isset($_REQUEST['vLang']) ? $_REQUEST['vLang'] : '';
$iPageId = isset($_REQUEST['iPageId']) ? $_REQUEST['iPageId'] : '55';
if(empty($lang))
{
	$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
}
$rideSafetyGuidelines = $obj->MySQLSelect("SELECT tPageDesc_".$lang." as tPageDesc FROM `pages` WHERE `iPageId` = ".$iPageId);
if(empty($rideSafetyGuidelines[0]['tPageDesc']))
{
	$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
	$rideSafetyGuidelines = $obj->MySQLSelect("SELECT tPageDesc_".$lang." as tPageDesc FROM `pages` WHERE `iPageId` = ".$iPageId);
}

$rideSafetyGuidelines[0]['tPageDesc'] =  str_replace("#ffffff", "white", $rideSafetyGuidelines[0]['tPageDesc']);
if($THEME_OBJ->isCubeJekXv3ThemeActive() == "Yes") {
	$rideSafetyGuidelines[0]['tPageDesc'] =  preg_replace("/#[0-9A-Fa-f]{6}/i", $SYSTEM_THEME_COLORS['APP_THEME_COLOR'], $rideSafetyGuidelines[0]['tPageDesc']);	
}

?>
<!DOCTYPE html>
<html>
<body>
	<?= str_replace('src="../assets/img/', 'src="'.$tconfig['tsite_url'].'assets/img/', html_entity_decode($rideSafetyGuidelines[0]['tPageDesc'])) ?>
</body>
</html>