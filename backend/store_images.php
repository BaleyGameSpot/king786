<?php
    include_once('common.php');
    $AUTH_OBJ->checkMemberAuthentication();
    $abc = 'company';
    $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    setRole($abc,$url);
    
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
       
    $tbl_name = 'store_wise_banners';

    $iCompanyId = $_SESSION['sess_iUserId'];
   	$sql = "SELECT * FROM " . $tbl_name . " WHERE iCompanyId = ".$iCompanyId . " AND eStatus = 'Active' ORDER BY iUniqueId DESC";
   	$banner_data = $obj->MySQLSelect($sql);
    
    $sql = "select eSafetyPractices from company where iCompanyId = '" . $iCompanyId. "'";
    $db_data = $obj->sql_query($sql);
    $eSafetyPractices=$db_data[0]['eSafetyPractices'];
    
       // echo "<pre>"; print_r($_SESSION); exit;
   	$storeImgUrl = $tconfig["tsite_upload_images"];
	$storeImgPath = $tconfig["tsite_upload_images_panel"];

	$comfirmMessage = $langage_lbl['LBL_DELETE_IMG_CONFIRM_NOTE_WEB'];
    if(!$MODULES_OBJ->isEnableStorePhotoUploadFacility() && $eSafetyPractices == "No") { 
        header("location:profile");
        exit;
    }


$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_MANAGE_GALLARY']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php");?>
        <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
        <!-- End: Default Top Script and css-->
        <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" />
        <link href="assets/plugins/dropzone/css/dropzone.css" rel="stylesheet"/>
        <style type="text/css">
            .fileupload-preview {
                line-height: 150px;
            }

            .dropzone {
                border: 2px dashed #ccc;
                background: #fff;
                width: 100%;
                padding: 0;
                min-height: 361px;
            }

            .add-more-images {
                padding: 0 !important;
                border: 4px dashed #ffffff !important;
                background: transparent !important;
                cursor: pointer !important;
                display: none;
            }
            .add-more-images:hover, .dz-details img {
                cursor: pointer !important;
            }
            .add-more-images .dz-details img {
                right: 0;
                bottom: 0;
                margin: auto;
                height: 30px;
                width: 30px !important;
                cursor: pointer !important;
            }
            .add-more-images .dz-details {
                width: 126px !important;
                height: 156px !important;
                margin-bottom: 0 !important;
                cursor: pointer !important;
            }

            .dz-details, .dz-details img {
                width: 0px !important
                height: 0px !important
            }

            .dz-remove:hover {
                cursor: pointer;
            }
            .dropzone .add-more-images {
                display:none;
            }
            .dropzone.dz-started .add-more-images{
                display:inline-block;
            }

            .gallery-note {
                font-size: 14px;
                text-align: left;
                line-height: 24px;
                display: block;
                justify-content: center;
                margin: 15px 0 0;
                background-color: #ffffff;
                width: 100%;
                border: 2px solid #cccccc;
                padding: 15px;
            }

            .custom-model-body strong, .gallery-note strong {
                font-size: 16px;
                font-weight: bold;
            }

            .custom-model-body {
                font-size: 14px;
                line-height: 24px;    
            }

            .dz-error-message {
                text-align: center !important;
                margin-left: 18px !important;
                font-size: 12px !important;
            }
            
        </style>
        <?= "<script>localStorage.confirmmessage = '$comfirmMessage';</script>"; ?>
        <script type="text/javascript">
            var errormessage = "<?=$imgUploadingExtenstionMsg?>";
        	var existingFiles = [];
        </script>
        <?php
			for ($r = 0; $r < scount($banner_data); $r++) {
                if(!empty($banner_data[$r]['vImage'])) {
                    $imgTmpArr = array();
                    $imgTmpArr['imageUrl'] = $storeImgUrl . "/" . $banner_data[$r]['vImage'];
                    $imgTmpArr['imagePath'] = $storeImgPath . "/" . $banner_data[$r]['vImage'];
                    $imgFileSize = file_exists($imgTmpArr['imagePath']) ? filesize($imgTmpArr['imagePath']) : '';
                    $imgTmpArr['name'] = $banner_data[$r]['vImage'];
                    $imgTmpArr['size'] = $imgFileSize;
                
		?>
			<script>existingFiles.push(<?= json_encode($imgTmpArr); ?>);</script>
		<?php }
			}
		?>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php");?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php");?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1>
                                <?=$langage_lbl['LBL_MANAGE_GALLARY']; ?>
                              <!--   <?php if($MODULES_OBJ->isEnableStoreSafetyProcedure()) { ?>
                                    <span style="cursor: pointer; color: #000000" onclick="showImageNote()" style="display: none;"><i class="fa fa-question-circle-o"></i></span>
                                <?php } ?> -->
                            </h1>
                        </div>
                    </div>
                </div>
            </section>
            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <form action="<?= $tSiteUrl; ?>ajax_store_images_action.php?action=upload" class="dropzone" id="my-awesome-dropzone">
                        <div class="upload-images"></div>
                        <div class="add-more-images dz-preview dz-image-preview" style="display: none;">
                            <div class="dz-details">
                                <img src="<?= $tconfig['tsite_url'] ?>assets/img/plus.svg">
                            </div>
                        </div>
                    </form>
                   <!--  <div class="gallery-note" style="display: none"><?= $langage_lbl['LBL_UPLOAD_IMAGES_SAFETY_INFO'] ?></div> -->
                </div>
            </section>
            <!-- footer part -->
            <?php include_once('footer/footer_home.php');?>
            <!-- footer part end -->
            <!-- End:Banner page-->
            <div style="clear:both;"></div>
        </div>
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php');?>
        <script src="assets/plugins/dropzone/dropzone.js"></script>
        <script src="assets/js/modal_alert.js"></script>
        <script type="text/javascript">
            var myAwesomeDropzone = 'my-awesome-dropzone';
            Dropzone.options.myAwesomeDropzone = {
                clickable: ['.upload-images', '.dropzone'],
                dictDefaultMessage: '<?= addslashes($langage_lbl['LBL_CLICK_DROP_TO_UPLOAD_MSG']); ?>',
                parallelUploads: 1,
                acceptedFiles: 'image/*',
                uploadMultiple: true,
            };

            Dropzone.autoDiscover = false;
            var myDropzone = new Dropzone("#my-awesome-dropzone");

            myDropzone.on("addedfile", function(file) {
                console.log(file);
                var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/x-png', 'video/mp4', 'video/quicktime', 'video/x-ms-wmv', 'video/x-msvideo', 'video/x-flv', 'video/x-matroska', 'video/webm'];
                if (allowedTypes.indexOf(file.type) === -1 && typeof file.type !== "undefined") {
                    alert('Invalid file type. Only PNG, JPEG, JPG, MP4, MOV, WMV, AVI, FLV, MKV, and WEBM files are allowed.');
                    $(file.previewElement).remove();
                    var dz_preview = $(document).find('.dz-preview');
                    //console.log(dz_preview.length);
                    if(dz_preview.length == 1)
                    {
                        // console.log("dz_preview",dz_preview.length);
                        $("#my-awesome-dropzone").removeClass('dz-started');
                    }
                    return;
                }
                var img_src = URL.createObjectURL(file);
                $(file.previewElement).find('img').attr('data-dz-thumbnail', img_src);
                $(file.previewElement).find('img').attr('src', img_src);
                $(file.previewElement).find('img').attr('alt', file.name);

                addMoreImagesBlock();
                setTimeout(function(){
                    checkUploadedFiles();
                }, 200);
                $('.add-more-images').show();
            });

            myDropzone.on("removedfile", function(file) {
                // console.log(file);
                setTimeout(function(){
                    checkUploadedFiles();
                }, 200);
            });

            myDropzone.on("success", function(file, response) {
                response = JSON.parse(response);
                if(response.Action == 1) {
                    var img_src = '<?= $storeImgUrl ?>' + response.filename;
                    $(file.previewElement).find('img').attr('data-dz-thumbnail', img_src);
                    $(file.previewElement).find('img').attr('src', img_src);
                    $(file.previewElement).find('img').attr('alt', response.filename);
                }
            });

            $(document).on('click', '.dz-preview img:not(.add-more-images .dz-details img)', function() {
                var img_url = $(this).attr('src');
                window.open(img_url, '_blank');
            });

            $(document).ready(function() {
                if(existingFiles.length > 0)
                {
                    addMoreImagesBlock();
                }
                setTimeout(function(){
                    checkUploadedFiles();
                }, 200);
            });

            $(document).on('click', '.add-more-images', function() {
                $('.upload-images').trigger('click');
            });

            function addMoreImagesBlock() {
                var add_more_images = $('.add-more-images').clone();
                $('.add-more-images').remove();
                setTimeout(function(){ 
                    add_more_images.appendTo('#my-awesome-dropzone');
                }, 200);
            }

            function checkUploadedFiles() {
                var dz_preview = $(document).find('.dz-preview');
                // console.log(dz_preview.length);
                var myAwesomeDropzone1 = 'my-awesome-dropzone';
                if(dz_preview.length > 1)
                {
                    console.log("hiii111");
                    $('.add-more-images').show();
                    $('.gallery-note').hide();
                   // $("#my-awesome-dropzone").removeClass('dz-started');
                    Dropzone.options.myAwesomeDropzone1 = {
                        clickable: ['.upload-images'],
                        parallelUploads: 1
                    };
                }
                else {
                    console.log("hiii2222");
                    $('.add-more-images').hide();
                    $('.gallery-note').show();
                    $("#my-awesome-dropzone").removeClass('dz-started');
                    Dropzone.options.myAwesomeDropzone1 = {
                        clickable: ['.dropzone'],
                        dictDefaultMessage: '<?= addslashes($langage_lbl["LBL_DROP_IMAGES_UPLOAD"]) ?> <br><br> <span><?= addslashes($langage_lbl['LBL_OR_CLICK_UPLOAD']) ?></span>',
                        parallelUploads: 1
                    };
                }
            }

            function showImageNote() {
                show_alert("", "<?= addslashes(htmlspecialchars_decode($langage_lbl['LBL_UPLOAD_IMAGES_SAFETY_INFO'])) ?>","","","<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']) ?>",undefined,true,true,true);
            }
        </script>
        <!-- End: Footer Script -->
    </body>
</html>