<?php
include_once('../common.php');

$iLanguageId = $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

if (empty($id)) {
    $sql = "SELECT iLanguageMasId FROM language_master WHERE vCode = '" . $default_lang . "'";
    $language_master = $obj->MySQLSelect($sql);
    $iLanguageId = $id = $language_master[0]['iLanguageMasId'];
}
$url = "home_content_business_action.php";

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
//$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = "";
$eFor = 'Business';

$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$script = 'homecontentbusiness';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";

if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}

$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

//$tbl_name = 'content_cubex_details';
$tbl_name = getContentCMSHomeTable();

$sql = "SELECT count(id) as cnt FROM $tbl_name WHERE eFor = '" . $eFor . "'";
$db_efordata = $obj->MySQLSelect($sql);
$action = (!empty($db_efordata[0]['cnt'])) ? 'Edit' : 'Add';

if (empty($db_efordata[0]['cnt'])) {
    $q_enter = "INSERT INTO $tbl_name SET `eFor` = '".$eFor."'";
    $obj->sql_query($q_enter);
    $db_efordata[0]['cnt'] = 1;
}

$sql = "SELECT vCode,vTitle FROM language_master WHERE iLanguageMasId = '" . $id . "'";
$db_data = $obj->MySQLSelect($sql);
$vCode = $db_data[0]['vCode'];
$title = $db_data[0]['vTitle'];
   
$img_arr = $_FILES;

if (!empty($img_arr)) {
    if (SITE_TYPE == 'Demo') {
        header("Location: $url&success=2");
        exit;
    }
    foreach ($img_arr as $key => $value) {
        if (!empty($value['name'])) { 
            $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
            //$temp_gallery = $img_path . '/';
            $image_object = $value['tmp_name'];
            $img_name = explode('.',$value['name']);
            $image_name = $img_name[0]."_".strtotime(date("H:i:s")).".".$img_name[1];

            $second_down_img = 0;
             $img_str = 'img_';
            if($key=='banner_section_img') $key = 'lBannerSection';

            $check_file_query = "SELECT " . $key . " FROM $tbl_name where eFor='" . $eFor . "'";
            $check_file = $obj->MySQLSelect($check_file_query);
            $sectionData = json_decode($check_file[0][$key],true);

            if($second_down_img==1) {
               if ($message_print_id != "" && $sectionData['img2_'.$vCode]!='') {
                   $check_file = $img_path . '/'.$template.'/' . $sectionData['img2_'.$vCode];
                   if ($check_file != '' && file_exists($check_file)) {
                       @unlink($check_file);
                   }

               }
            } else {
               if ($message_print_id != "" && $sectionData[$img_str.$vCode]!='') {
                   $check_file = $img_path . '/'.$template.'/' . $sectionData[$img_str.$vCode];
                   if ($check_file != '' && file_exists($check_file)) {
                       @unlink($check_file);
                   }

               }
            }

            $Photo_Gallery_folder = $img_path . "/".$template."/";
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }

            $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif,svg', $vCode);

            if ($img[2] == "1") {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = $img[1];
                header("location:" . $backlink);
            }

            if (!empty($img[0])) {
              $sectionData[$img_str.$vCode] = $img[0];
              $sectionDatajson = getJsonFromAnArr($sectionData);
              $sql = "UPDATE " . $tbl_name . " SET " . $key . " = '" . $sectionDatajson . "' WHERE eFor='" . $eFor . "'";
              $obj->sql_query($sql);
           } 
        }
    }
}
if(isset($_POST['submit'])) {

    $check_file_query = "SELECT lBannerSection,lHowitworkSection,lSecuresafeSection,lDownloadappSection,lServiceSection,lBenefitSection,lEarnSection FROM $tbl_name where eFor='" . $eFor . "'";
    $check_file = $obj->MySQLSelect($check_file_query);

    $sectionData = json_decode($check_file[0]['lBannerSection'],true);
    $banner_section_arr['title_'.$vCode] = isset($_POST['banner_section_title']) ? $_POST['banner_section_title'] : '';

    $banner_section_arr['desc_'.$vCode] = isset($_POST['banner_section_desc']) ? $_POST['banner_section_desc'] : '';
    $banner_section_arr['img_'.$vCode] = isset($sectionData['img_'.$vCode]) ? $sectionData['img_'.$vCode] : '';
    $banner_section_arr = !(empty($sectionData)) ? array_merge($sectionData,$banner_section_arr) : $banner_section_arr;
    $banner_section = getJsonFromAnArrWithoutClean($banner_section_arr); //addslashes because double quotes stored after slashes so while getting data no problem


    $sectionData = json_decode($check_file[0]['lHowitworkSection'],true);

    $how_it_work_section_arr['title_'.$vCode] = isset($_POST['how_it_work_section_title']) ? $_POST['how_it_work_section_title'] : '';
    $how_it_work_section_arr['desc_'.$vCode] = isset($_POST['how_it_work_section_desc']) ? $_POST['how_it_work_section_desc'] : '';
    $how_it_work_section_arr['img_'.$vCode] = isset($sectionData['img_'.$vCode]) ? $sectionData['img_'.$vCode] : '';
    $how_it_work_section_arr = !(empty($sectionData)) ? array_merge($sectionData,$how_it_work_section_arr) : $how_it_work_section_arr;


    $how_it_work_section = getJsonFromAnArrWithoutClean($how_it_work_section_arr);   
    
}

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location: $url&success=2");
        exit;
    }

    $where = " eFor = '" . $eFor . "'";
    $data_update['lBannerSection'] = $banner_section;
    $data_update['lHowitworkSection'] = $how_it_work_section;
    $data_update['eFor'] = $eFor;
    $obj->MySQLQueryPerform($tbl_name, $data_update, 'update', $where);

    $configUpdateData = [];
    if (isset($_POST['ANDROID_APP_LINK_EARN_PAGE']) && !empty($_POST['ANDROID_APP_LINK_EARN_PAGE'])) {
        $configUpdateData = [];
        $configUpdateData['vValue'] = $_POST['ANDROID_APP_LINK_EARN_PAGE'];
        $where = "vName = 'ANDROID_APP_LINK_EARN_PAGE'";
        $obj->MySQLQueryPerform('configurations', $configUpdateData, 'update', $where);
    }
    if (isset($_POST['IPHONE_APP_LINK_EARN_PAGE']) && !empty($_POST['IPHONE_APP_LINK_EARN_PAGE'])) {
        $configUpdateData = [];
        $configUpdateData['vValue'] = $_POST['IPHONE_APP_LINK_EARN_PAGE'];
        $where = "vName = 'IPHONE_APP_LINK_EARN_PAGE'";
        $obj->MySQLQueryPerform('configurations', $configUpdateData, 'update', $where);
    }

    if ($action == "Add") { 
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else { 
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    if ($action == 'Edit') {
        $backlink = $backlink."?id=".$id;
    }
    
    header("location:" . $backlink);
    exit;
}

if ($action == 'Edit') {
    $sql = "SELECT * FROM $tbl_name WHERE eFor = '" . $eFor . "'";
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) { 
        foreach ($db_data as $key => $value) {
            $banner_section = json_decode($value['lBannerSection'],true);
            $how_it_work_section = (array) json_decode($value['lHowitworkSection']);
        }
    }
}
if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
} 

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Business Home Content <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style>
            .body-div.innersection {
                box-shadow: -1px -2px 73px 2px #dedede;
                float: none;
            }
            .innerbg_image {
                width:auto;margin:10px 0;height: 150px; 
            }
            .notes {
                font-weight: 700;font-style: italic;
            }

            .download-section {
                border: 1px solid;
            }

            .download-section .download-section-header {
                padding: 0 30px;
            }

            .languageSelection {
                display: flex;
                align-items: center;
            }

            .languageSelection p {
                margin: 0;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-6">
                            <h2><?= $action; ?> Business Home Content (<?php echo $title; ?>)</h2>
                        </div>
                        <div class="col-lg-6 languageSelection">
                            <div class="col-lg-6" style="text-align: end;margin: auto;">
                                <p style="margin: 0; font-weight:700;">Select Language:</p>
                            </div>
                            <select onchange="language_wise_page(this);" name="language" id="language" class="form-control" style="margin-right: 15px">
                                <?php
                                foreach ($db_master as $dm) {
                                    $selected = '';
                                    if ($dm['iLanguageMasId'] == $id) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option <?php echo $selected; ?>
                                            value="<?php echo $dm['iLanguageMasId'] ?>"><?php echo $dm['vTitle'] ?> </option>
                                <?php }
                                ?>
                            </select>
                            <a href="<?= $url ?>" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <?php
                    include('valid_msg.php');
                    ?>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" name="_home_content_form" id="_home_content_form" action="" enctype='multipart/form-data'>
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="vCode" value="<?= $vCode; ?>">
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="<?= $url; ?>"/>
                                
                                 <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Banner section</h3></div></div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="banner_section_title"  id="banner_section_title" value="<?= $banner_section['title_'.$vCode]; ?>" placeholder="Title" required>
                                            </div>
                                        </div>
                                       
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="banner_section_desc"  id="banner_section_desc"  placeholder="Description"><?= $banner_section['desc_'.$vCode]; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($banner_section['img_'.$vCode] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template ."/" . $banner_section['img_'.$vCode]; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control FilUploader" name="banner_section_img"  id="banner_section_img" accept=".png,.jpg,.jpeg,.gif,.svg">
                                                <br/>
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 1730px * 600px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                 </div>

                                 <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>How It work section</h3></div></div>
                                       
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="how_it_work_section_title"  id="how_it_work_section_title" value="<?= $how_it_work_section['title_'.$vCode]; ?>" placeholder="Title" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="how_it_work_section_desc"  id="how_it_work_section_desc"  placeholder="Description"><?= $how_it_work_section['desc_'.$vCode]; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                                 
                                <!----------------------download section------------------------->
                                
                                <div class="body-div innersection download-section">
                                    <div class="form-group">
                                        <div class="row ">
                                            <div class="col-lg-12 download-section-header">
                                                <h3>Download Section</h3>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="col-lg-12">
                                                    <label>Title
                                                        <span class="red"> *</span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-12 Download_section_title">
                                                    <input readonly type="text" class="form-control"
                                                           name="Download_section_title"
                                                           id="Download_section_title"
                                                           value="<?php echo $langage_lbl_admin['LBL_DOWNLOAD_ANDROID_IOS_APPS_FOR_EARN_PAGE_TXT']; ?>"
                                                           placeholder="Title">
                                                    <button onClick="languageLabel('LBL_DOWNLOAD_ANDROID_IOS_APPS_FOR_EARN_PAGE_TXT')"
                                                            type="button" class="btn btn-info" data-toggle="tooltip"
                                                            data-original-title="Edit" onclick="editCategoryName('Edit')">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="col-lg-12">
                                                    <label>Iphone App Link
                                                        <span class="red"> *</span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <input type="text" class="form-control" name="IPHONE_APP_LINK_EARN_PAGE"
                                                           id="IPHONE_APP_LINK_EARN_PAGE"
                                                           value="<?php echo $IPHONE_APP_LINK_EARN_PAGE; ?>"
                                                           placeholder="Title" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="col-lg-12">
                                                    <label>Android App Link
                                                        <span class="red"> *</span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <input type="text" class="form-control" name="ANDROID_APP_LINK_EARN_PAGE"
                                                           id="ANDROID_APP_LINK_EARN_PAGE"
                                                           value="<?php echo $ANDROID_APP_LINK_EARN_PAGE; ?>"
                                                           placeholder="Title" required/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!----------------------download section------------------------->
                                
                                <!-- End Home Header area-->
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <a href="<?= $url; ?>" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        
        <?php include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
        <script src="../assets/plugins/ckeditor/config.js"></script>
        <script>
            CKEDITOR.replace('ckeditor', {
                allowedContent: {
                    i: {
                        classes: 'fa*'
                    },
                    span: true
                }
            });
        </script>
        <script>
            $(document).ready(function () {
                var referrer;
<?php if ($goback == 1) { ?>
                    alert('<?php echo $var_msg; ?>');
                    //history.go(-1);
                    window.location.href = "<?= $url ?>";


<?php } ?>
                if ($("#previousLink").val() == "") { //alert('pre1');
                    referrer = document.referrer;
                } else { 
                    referrer = $("#previousLink").val();
                }

                if (referrer == "") {
                    referrer = "<?= $url ?>";
                } else { //alert('hi');
                    //$("#backlink").val(referrer);
                    referrer = "<?= $url ?>";
                    // alert($("#backlink").val(referrer));
                }
                $(".back_link").attr('href', referrer);
            });
            /**
             * This will reset the CKEDITOR using the input[type=reset] clicks.
             */
            $(function () {
                if (typeof CKEDITOR != 'undefined') {
                    $('form').on('reset', function (e) {
                        if ($(CKEDITOR.instances).length) {
                            for (var key in CKEDITOR.instances) {
                                var instance = CKEDITOR.instances[key];
                                if ($(instance.element.$).closest('form').attr('name') == $(e.target).attr('name')) {
                                    instance.setData(instance.element.$.defaultValue);
                                }
                            }
                        }
                    });
                }
            });
            $(".FilUploader").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'svg'];
                if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only formats are allowed : " + fileExtension.join(', '));
                    $(this).val('');
                    return false;

                }
            });

            function language_wise_page(sel) {
                $("#loaderIcon").show();
                var url = window.location.href;
                url = new URL(url);
                url.searchParams.set("id", sel.value);
                window.location.href = url.href;
            }
        </script>
    </body>
    <!-- END BODY-->
</html>
