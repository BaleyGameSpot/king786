<?php
include_once('../common.php');
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : 0;

$url = "homepage_content.php";

if(isset($_REQUEST['back_link']) && !empty($_REQUEST['back_link'])){
    $url =$_REQUEST['back_link'];
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$vMetaTitle = isset($_REQUEST['vMetaTitle']) ? $_REQUEST['vMetaTitle'] : '';
$tMetaKeyword = isset($_REQUEST['tMetaKeyword']) ? $_REQUEST['tMetaKeyword'] : '';
$tMetaDescription = isset($_REQUEST['tMetaDescription']) ? $_REQUEST['tMetaDescription'] : '';

$previousLink = "";
$eFor = 'DeliverAll';

$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$script = 'ServiceSection';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";

if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}
 
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

//$tbl_name = 'content_cubex_details';
$tbl_name = getContentCMSHomeTable();

$sql = "SELECT count(id) as cnt FROM $tbl_name WHERE eFor = '" . $eFor . "' AND `iVehicleCategoryId` = '".$iVehicleCategoryId."'";
$db_efordata = $obj->MySQLSelect($sql);

$action = (!empty($db_efordata[0]['cnt'])) ? 'Edit' : 'Add';

//if(empty($vCode)) { 
$sql = "SELECT vCode,vTitle FROM language_master WHERE iLanguageMasId = '" . $id . "'";
$db_data = $obj->MySQLSelect($sql);
$vCode = $db_data[0]['vCode'];
$title = $db_data[0]['vTitle'];
//}

if (empty($db_efordata[0]['cnt'])) {
   if(ENABLE_DYNAMIC_CREATE_PAGE=="Yes") {
      $idNew = $obj->sql_query("INSERT INTO $tbl_name
      (lBannerSection,lHowitworkSection,lSecuresafeSection,lDownloadappSection,lCalltobookSection,lEarnSection,lCalculateSection,lCartypeSection,lServiceSection,lBenefitSection,eFor,iVehicleCategoryId)
      SELECT                lBannerSection,lHowitworkSection,lSecuresafeSection,lDownloadappSection,lCalltobookSection,lEarnSection,lCalculateSection,lCartypeSection,lServiceSection,lBenefitSection,eFor,$iVehicleCategoryId
      FROM 
      $tbl_name WHERE 
      eFor = '".$eFor."' and iVehicleCategoryId = 0;");
   } else {
      $q_enter = "INSERT INTO $tbl_name SET `eFor` = '".$eFor."', `iVehicleCategoryId` = '".$iVehicleCategoryId."'";
      $obj->sql_query($q_enter);
   }
   $db_efordata[0]['cnt'] = 1;
   $action = (!empty($db_efordata[0]['cnt'])) ? 'Edit' : 'Add';
}

$img_arr = $_FILES;

if (!empty($img_arr)) {
    if (SITE_TYPE == 'Demo') {
        header("Location: $url&success=2");
        exit;
    }
    foreach ($img_arr as $key => $value) {
        if (!empty($value['name'])) { 
            $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
            $image_object = $value['tmp_name'];
            $img_name = explode('.',$value['name']);
            for ($i = 0; $i < scount($img_name) - 1; $i++) {
                $tmp1[] = $img_name[$i];
            }
            $file = implode("_", $tmp1);
            $ext = $img_name[scount($img_name) - 1];
            $image_name = strtotime(date("H:i:s")) . "." . $ext;
            //$image_name = $img_name[0]."_".strtotime(date("H:i:s")).".".$img_name[1];


            $img_str = 'img_';
            
            if($key=='banner_section_img') $key = 'lBannerSection';
            else if($key=='call_section_img') $key = 'lCalltobookSection';

            $check_file_query = "SELECT " . $key . " FROM $tbl_name where eFor='" . $eFor . "' AND iVehicleCategoryId = $iVehicleCategoryId";
            $check_file = $obj->MySQLSelect($check_file_query);
            $sectionData = json_decode($check_file[0][$key],true);

            if($second_down_img==1) {
               if ($message_print_id != "" && $sectionData['img2_'.$vCode]!='') {
                   $check_file = $img_path . $template . '/' . $sectionData['img2_'.$vCode];
                   if ($check_file != '' && file_exists($check_file)) {
                     if(ENABLE_DYNAMIC_CREATE_PAGE=="Yes") { } else {
                       @unlink($check_file); //why unlink removed reason is written in 25-03-2021
                     }
                   }

               }
            } else {
               if ($message_print_id != "" && $sectionData[$img_str.$vCode]!='') {
                   $check_file = $img_path . $template .'/' . $sectionData[$img_str.$vCode];
                   if ($check_file != '' && file_exists($check_file)) {
                     if(ENABLE_DYNAMIC_CREATE_PAGE=="Yes") { } else {
                       @unlink($check_file); //why unlink removed reason is written in 25-03-2021
                     }
                   }

               }
            }

            $Photo_Gallery_folder = $img_path . $template ."/";
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            sleep(1);
            $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif,svg', $vCode);

            if ($img[2] == "1") {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = $img[1];
                header("location:" . $backlink);
            }

            if($second_down_img==1) {
               if (!empty($img[0])) {
                    $sectionData['img2_'.$vCode] = $img[0];
                    $sectionDatajson = getJsonFromAnArrWithoutClean($sectionData);
                    $update_arr = array();
                    $update_arr[$key] = $sectionDatajson;
                    $where = " eFor = '" . $eFor . "' AND iVehicleCategoryId = ".$iVehicleCategoryId."";
                    $obj->MySQLQueryPerform($tbl_name, $update_arr, 'update', $where);
               }
            } else {
               if (!empty($img[0])) {
                    $sectionData[$img_str.$vCode] = $img[0];
                    $sectionDatajson = getJsonFromAnArrWithoutClean($sectionData);

                    $update_arr = array();
                    $update_arr[$key] = $sectionDatajson;
                    $where = " eFor = '" . $eFor . "' AND iVehicleCategoryId = ".$iVehicleCategoryId."";
                    $obj->MySQLQueryPerform($tbl_name, $update_arr, 'update', $where);
               }   
            }
        }
    }
}
if(isset($_POST['submit'])) {

      $check_file_query = "SELECT lBannerSection, lHowitworkSection, lCalltobookSection, lEarnSection FROM $tbl_name where eFor='" . $eFor . "' AND iVehicleCategoryId = $iVehicleCategoryId";
      $check_file = $obj->MySQLSelect($check_file_query);
      
      $sectionData = json_decode($check_file[0]['lBannerSection'],true);
      $banner_section_arr['title_'.$vCode] = isset($_POST['banner_section_title']) ? $_POST['banner_section_title'] : '';
      $banner_section_arr['desc_'.$vCode] = isset($_POST['banner_section_desc']) ? $_POST['banner_section_desc'] : '';
      $banner_section_arr['img_alt_'.$vCode] = isset($_POST['banner_section_img_alt']) ? $_POST['banner_section_img_alt'] : '';
      $banner_section_arr['img_'.$vCode] = isset($sectionData['img_'.$vCode]) ? $sectionData['img_'.$vCode] : '';
      $banner_section_arr = !(empty($sectionData)) ? array_merge($sectionData,$banner_section_arr) : $banner_section_arr;
      $banner_section = getJsonFromAnArrWithoutClean($banner_section_arr); //addslashes because double quotes stored after slashes so while getting data no problem


      $sectionData = json_decode($check_file[0]['lCalltobookSection'],true);
      $call_section_arr['menu_title_'.$vCode] = isset($_POST['call_section_menu_title']) ? $_POST['call_section_menu_title'] : '';
      $call_section_arr['title_'.$vCode] = isset($_POST['call_section_title']) ? $_POST['call_section_title'] : '';
      $call_section_arr['link_'.$vCode] = isset($_POST['call_section_link']) ? $_POST['call_section_link'] : '';
      $call_section_arr['desc_'.$vCode] = isset($_POST['call_section_desc']) ? $_POST['call_section_desc'] : '';
      $call_section_arr['img_alt_'.$vCode] = isset($_POST['call_section_img_alt']) ? $_POST['call_section_img_alt'] : '';
      $call_section_arr['img_'.$vCode] = isset($sectionData['img_'.$vCode]) ? $sectionData['img_'.$vCode] : '';
      $call_section_arr = !(empty($sectionData)) ? array_merge($sectionData,$call_section_arr) : $call_section_arr;
      $call_section = getJsonFromAnArrWithoutClean($call_section_arr);

    
    
}

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location: $url&success=2");
        exit;
    }

    $update_array = [];
    $update_array['lBannerSection'] = $banner_section;
    $update_array['lHowitworkSection'] = $how_it_work_section;
    $update_array['lCalltobookSection'] = $call_section;
    $update_array['eFor'] = $eFor;
    $update_array['vMetaTitle'] = $vMetaTitle;
    $update_array['tMetaKeyword'] = $tMetaKeyword;
    $update_array['tMetaDescription'] = $tMetaDescription;

    $where = " eFor = '" . $eFor . "' AND iVehicleCategoryId = '".$iVehicleCategoryId."'";
    
    $obj->MySQLQueryPerform($tbl_name, $update_array, 'update', $where);

    $configUpdateData = [];
    if (isset($_POST['ANDROID_APP_LINK']) && !empty($_POST['ANDROID_APP_LINK'])) {
        $configUpdateData = [];
        $configUpdateData['vValue'] = $_POST['ANDROID_APP_LINK'];
        $where = "vName = 'ANDROID_APP_LINK'";
        $obj->MySQLQueryPerform('configurations', $configUpdateData, 'update', $where);
    }
    if (isset($_POST['IPHONE_APP_LINK']) && !empty($_POST['IPHONE_APP_LINK'])) {
        $configUpdateData = [];
        $configUpdateData['vValue'] = $_POST['IPHONE_APP_LINK'];
        $where = "vName = 'IPHONE_APP_LINK'";
        $obj->MySQLQueryPerform('configurations', $configUpdateData, 'update', $where);
    }
    
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:" . $backlink);
    exit;
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM $tbl_name WHERE eFor = '" . $eFor . "' AND iVehicleCategoryId = $iVehicleCategoryId";
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) { 
        foreach ($db_data as $key => $value) {
            $banner_section = json_decode($value['lBannerSection'],true);
            $how_it_work_section = json_decode($value['lHowitworkSection'], true);
            $call_section = json_decode($value['lCalltobookSection'],true);
            $vMetaTitle = $value['vMetaTitle'];
            $tMetaKeyword = $value['tMetaKeyword'];
            $tMetaDescription = $value['tMetaDescription'];
        }
    }
}
if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
} 

$db_master = $obj->MySQLSelect("SELECT * FROM `language_master` ORDER BY `iDispOrder`");
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Delivery Home Content <?= $action; ?></title>
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
                        <div class="col-lg-12">
                            <?php /*if($THEME_OBJ->isCJXDoctorv2ThemeActive()!='Yes') { ?>
                            <h2><?= $action; ?> Deliverall Home Content (<?php echo $title; ?>)</h2>
                            <?php } else { ?>
                            <h2><?= $action; ?> Doctor inner Home Content (<?php echo $title; ?>)</h2>
                            <?php }*/ ?>

                            <div class="col-lg-8" >
                                <h2><?= $action; ?> <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Delivery Home Content</h2>
                            </div>
                            <div class="col-lg-4 languageSelection">
                                <div class="col-lg-6" style="text-align: end;margin: auto;">
                                    <p style="margin: 0; font-weight:700;" >Select Language:</p>
                                </div>
                                <select onchange="language_wise_page(this);" name="language" id="language" class="form-control">
                                    <?php
                                    foreach ($db_master as $dm) {
                                        $selected = '';
                                        if ($dm['iLanguageMasId'] == $id) {
                                            $selected = 'selected';
                                        }
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $dm['iLanguageMasId'] ?>"><?php echo $dm['vTitle'] ?> </option>
                                    <?php }
                                    ?>
                                </select>
                            </div>
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
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>" class="innerbg_image"/> -->
                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control FilUploader" name="banner_section_img"  id="banner_section_img" accept=".png,.jpg,.jpeg,.gif,.svg">
                                                <br/>
                                                <span class="notes"><b><?php echo constVal("IMAGE_RATIO_INSTRUCTION_NOTES"); ?></b></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Image alt</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="banner_section_img_alt" id="banner_section_img_alt" value="<?= $banner_section['img_alt_'.$vCode]; ?>" placeholder="Background Image alt Text">
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                                <?php if($THEME_OBJ->isCJXDoctorv2ThemeActive()!='Yes') { ?>

                                <div class="body-div innersection">
                                    <div class="form-group">
                                        <div class="row"><div class="col-lg-12"><h3>Call Section</h3></div></div>
                                        
                                        <div class="row">
                                           <div class="col-lg-12">
                                               <label>Title<span class="red"> *</span></label>
                                           </div>
                                           <div class="col-lg-6">
                                               <input type="text" class="form-control" name="call_section_title"  id="call_section_title" value="<?= $call_section['title_'.$vCode]; ?>" placeholder="Title" required>
                                           </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description</label>
                                                <h5>[Note: Please use #SUPPORT_PHONE# predefined tags to display the support phone value. Please go to Settings >> General section to change the values of above predefined tags.]</h5> 
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control ckeditor" rows="10" name="call_section_desc"  id="call_section_desc"  placeholder="Description"><?= $call_section['desc_'.$vCode]; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php if ($call_section['img_'.$vCode] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$call_section['img_'.$vCode]; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control FilUploader" name="call_section_img"  id="call_section_img" accept=".png,.jpg,.jpeg,.gif,.svg">
                                                <br/>
                                                 <span class="notes"><b><?php echo constVal("IMAGE_RATIO_INSTRUCTION_NOTES"); ?></b></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Background Image alt</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="call_section_img_alt" id="call_section_img_alt" value="<?= $call_section['img_alt_'.$vCode]; ?>" placeholder="Background Image alt Text">
                                            </div>
                                        </div>                                   
                                    </div>
                                 </div>

                         
                                 <?php } ?>

                                <!----------------------download section------------------------->

                                <div class="body-div innersection download-section">
                                    <div class="form-group">
                                        <div class="row ">
                                            <div class="col-lg-12 download-section-header">
                                                <h3>Download Section</h3>
                                                <p>Note: The content editing provided below will reflect in "Download" section of all inner pages.</p>
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
                                                           value="<?php echo $langage_lbl_admin['LBL_DOWNLOAD_ANDROID_IOS_APPS_TXT']; ?>"
                                                           placeholder="Title">

                                                    <button onClick="languageLabel('LBL_DOWNLOAD_ANDROID_IOS_APPS_TXT')"
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
                                                    <input type="text" class="form-control" name="IPHONE_APP_LINK"
                                                           id="IPHONE_APP_LINK"
                                                           value="<?php echo $IPHONE_APP_LINK; ?>"
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
                                                    <input type="text" class="form-control" name="ANDROID_APP_LINK"
                                                           id="ANDROID_APP_LINK"
                                                           value="<?php echo $ANDROID_APP_LINK; ?>"
                                                           placeholder="Title" required/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!----------------------download section------------------------->
                                <!-- End Home Header area-->
                                <div class="body-div innersection seo_section">
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Meta Title</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" name="vMetaTitle"  id="vMetaTitle" value="<?= htmlspecialchars($vMetaTitle); ?>" placeholder="Meta Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Meta Keyword</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" name="tMetaKeyword"  id="tMetaKeyword" value="<?= htmlspecialchars($tMetaKeyword); ?>" placeholder="Meta Keyword">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Meta Description</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" rows="10" name="tMetaDescription"  id="<?= $tMetaDescription; ?>"  placeholder="<?= $tMetaDescription; ?> Value" <?= $required; ?>> <?= $tMetaDescription; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">
                <img src="default.gif">
            </div>
        </div>
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
    
            $('.ckeditor').ckeditor({
                enterMode : CKEDITOR.ENTER_BR,
                shiftEnterMode: CKEDITOR.ENTER_P
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
                    // alert(referrer);
                } else { //alert('pre2');
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
                //alert($(".back_link").attr('href',referrer));	
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

            function deleteIcon(ele) {
                var id = $(ele).attr('data-id');
                $('#removeidmodel').val(id);

                $('#service_icon_modal').modal('show');

                return false;

            }
            $(".action_modal_submit").unbind().click(function () {
                var id = $('#removeidmodel').val();
                $('#removeidmodel').val('');
                $('#removeIconFrom_' + id).click();
                return true;

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
