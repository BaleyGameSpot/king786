<?php
if(!empty($_SESSION['sess_iAdminUserId'])){
	$validation_lanauge_label = $langage_lbl_admin;
}else{
	$validation_lanauge_label = $langage_lbl;
}
?>
<script>
	// Hide Error Message Start
	$(function() {
	    setTimeout(function() {
	        $(".alert-success").delay(4000).fadeOut(2500);
	        $(".msgs_hide").delay(4000).fadeOut(2500);
	    }, 4000);
	});
	// Hide Error Message End
	 /* Name Messages */
	var requiredFieldMsg = "<?= isset($validation_lanauge_label['LBL_FEILD_REQUIRD']) ? $validation_lanauge_label['LBL_FEILD_REQUIRD'] : '' ?>";
	var NameMinLengthMsg = "<?=str_replace("{0}","2",$validation_lanauge_label['LBL_FIELD_MINLENGTH'])?>";
	var NameMaxLengthMsg = "<?=str_replace("{0}","30",$validation_lanauge_label['LBL_FIELD_MAXLENGTH'])?>";
	/* Phone Messages */
	var phoneAlreadyExistMsg = "<?= isset($validation_lanauge_label['LBL_PHONE_ALREADY_EXIST_MSG']) ? $validation_lanauge_label['LBL_PHONE_ALREADY_EXIST_MSG'] : '' ?>";
	var phoneActiveInactiveAgainMsg = "<?= isset($validation_lanauge_label['LBL_PHONE_ACTIVE_DEACTIVE_TRY_AGAIN_MSG']) ? $validation_lanauge_label['LBL_PHONE_ACTIVE_DEACTIVE_TRY_AGAIN_MSG'] : '' ?>";
	var phoneMinLengthMsg = "<?=str_replace("{0}","3",$validation_lanauge_label['LBL_FIELD_MINLENGTH'])?>";
	var phoneDigitMsg = "<?=$validation_lanauge_label['LBL_FIELD_DIGIT']?>";
	/* Password Messages */
	var passwordMinLengthMsg = "<?=str_replace("{0}","6",$validation_lanauge_label['LBL_FIELD_MINLENGTH'])?>";
	var passwordMaxLengthMsg = "<?=str_replace("{0}","13",$validation_lanauge_label['LBL_FIELD_MAXLENGTH'])?>";
	/* Image File Video Uploading Extension Messages */
	var imageUploadingExtenstionMsg = "<?=str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$validation_lanauge_label['LBL_FILE_UPLOADING_EXTENSION_MSG'])?>";
	var csvUploadingExtenstionMsg = "<?=str_replace("####","csv",$validation_lanauge_label['LBL_FILE_UPLOADING_EXTENSION_MSG'])?>";
	var docUploadingExtenstionMsg = "<?=str_replace("####",$tconfig["tsite_upload_docs_file_extensions_validation_extensions"],$validation_lanauge_label['LBL_FILE_UPLOADING_EXTENSION_MSG'])?>";
	var videoUploadingExtenstionMsg = "<?=str_replace("####",$tconfig["tsite_upload_video_file_extensions_validation"],$validation_lanauge_label['LBL_FILE_UPLOADING_EXTENSION_MSG'])?>";
	var imageUploadingExtenstionJson = '<?=$tconfig["tsite_upload_image_file_extensions_ary"]?>';
	var csvUploadingExtenstionJson = '<?=$tconfig["tsite_upload_csv_file_extensions_ary"]?>';
	var imageUploadingExtenstionjsrule = '<?=$tconfig["tsite_upload_image_file_extensions_js_rule"]?>';
	var docUploadingExtenstionjsrule = '<?=$tconfig["tsite_upload_docs_file_extensions_js_rule"]?>';
</script>