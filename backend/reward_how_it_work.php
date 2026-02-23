<?php

include 'common.php';



$lang = isset($_REQUEST['vLang']) ? $_REQUEST['vLang'] : '';

$iPageId = isset($_REQUEST['iPageId']) ? $_REQUEST['iPageId'] : '57';

if(empty($lang))

{

	$lang = $generalobj->getDefaultLangData("vCode");

}

$rideSafetyGuidelines = $obj->MySQLSelect("SELECT tPageDesc_".$lang." as tPageDesc FROM `pages` WHERE `iPageId` = ".$iPageId);

if(empty($rideSafetyGuidelines[0]['tPageDesc']))

{

	$lang = $generalobj->getDefaultLangData("vCode");

	$rideSafetyGuidelines = $obj->MySQLSelect("SELECT tPageDesc_".$lang." as tPageDesc FROM `pages` WHERE `iPageId` = ".$iPageId);

}

?>

<!DOCTYPE html>

<html>
<style type="text/css">
	.reward-content {
		padding: 0 15px;
	}
</style>
<body>
	<div class="reward-content">
		<?= str_replace('src="../assets/img/safety.png"', 'src="'.$tconfig['tsite_url'].'assets/img/safety.png'.'"', $rideSafetyGuidelines[0]['tPageDesc']) ?>
	</div>
</body>

</html>