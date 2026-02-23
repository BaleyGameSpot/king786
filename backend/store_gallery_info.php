<?php
include 'common.php';

$lang = isset($_REQUEST['vLang']) ? $_REQUEST['vLang'] : '';

$langLabels = $LANG_OBJ->FetchLanguageLabels($lang, "1");
$content = explode('<br><br>', $langLabels['LBL_UPLOAD_IMAGES_SAFETY_INFO']);
$content_checklist = array_map('trim', array_filter(explode('- ', $content[1])));
// echo "<pre>"; print_r($content_checklist); exit;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<link href="https://fonts.googleapis.com/css?family=Poppins:100,400,500,600,700,800,900&display=swap" rel="stylesheet"/>
	<style type="text/css">
		html, body {
			font-family: 'poppins' !important;
			margin: 0;
			padding: 0;
		}
		span {
			color: #8FA1B4;
			font-size: 14px;
		}

		ul {
			border: 1px solid #E1E1E1;
			border-radius: 10px;
			background-color: #F6F6F6;
			padding: 10px 30px;
		}

		ul li {
			padding-bottom: 5px;
		}

		ul li:last-child {
			padding-bottom: 0;
		}

		.store-galley-img {
			text-align: center;
			margin-bottom: 10px;
    		padding: 70px 0 0;
		}

		.store-galley-img img {
			width: 175px;
		}

		.store-galley-content {
			margin: 0 15px;
		}
	</style>
</head>
<body>
	<div class="store-galley-img"><img src="<?= $tconfig['tsite_url'] ?>assets/img/store-gallery.png"></div>
	<div class="store-galley-content">
		<div><?= $content[0] ?></div>
		<?php if(!empty($content_checklist)) {?>
		<ul>
			<?php foreach ($content_checklist as $list) { ?>
			<li><?= $list ?></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</body>
</html>