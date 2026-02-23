<?php
include_once('../common.php');
require_once("library/validation.class.php");

if (!$userObj->hasPermission('manage-home-page-content')) {
    $userObj->redirect();
}

$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$iLanguageId = $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

if(empty($id)){
    $sql = "SELECT iLanguageMasId FROM language_master WHERE vCode = '" . $default_lang . "'";
    $language_master = $obj->MySQLSelect($sql);
    $iLanguageId = $id = $language_master[0]['iLanguageMasId'];
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = "";
//$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$tbl_name = 'homecontent';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
$third_mid_image_three1 = $third_mid_title_three1 = $third_mid_title_three = $third_mid_desc_three1 = $mobile_app_bg_img1 = $third_mid_desc_one1 = '';
if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$script = 'homecontent';
$tbl_name = getAppTypeWiseHomeTable();
$iLanguageMasId = 0;
if (empty($vCode)) {
    $sql = "SELECT hc.vCode, lm.iLanguageMasId FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vCode = $db_data[0]['vCode'];
    $iLanguageMasId = $db_data[0]['iLanguageMasId'];
}

if(isset($_REQUEST['banner_img']) && !empty($_REQUEST['banner_img'])) {
    $banner_img = $_REQUEST['banner_img'];

    if(file_exists($tconfig["tsite_upload_apptype_page_images_panel"] . $template . '/'.$banner_img)) {
        $check_file = $obj->MySQLSelect("SELECT * FROM $tbl_name where vCode = '" . $vCode . "'");

        $banner_section_data = json_decode($check_file[0]['lGeneralBannerSection'], true);
        $img_key = array_search($banner_img, $banner_section_data['img']);
        unset($banner_section_data['img'][$img_key]);
        $banner_section_data['img'] = array_values($banner_section_data['img']);

        $sectionDatajson = getJsonFromAnArr($banner_section_data);
        $where = " vCode = '" . $vCode . "'";
        $data_update['lGeneralBannerSection'] = $sectionDatajson;
        $obj->MySQLQueryPerform($tbl_name, $data_update, 'update', $where);

        @unlink($tconfig["tsite_upload_apptype_page_images_panel"] . $template . '/'.$banner_img);

        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_IMAGE_DELETE_SUCCESS_NOTE'];
        header("Location:homepage_content.php?id=" . $iLanguageId);
        exit;
    }
}

$img_arr = $_FILES;
if (!empty($img_arr)) {
    if (SITE_TYPE == 'Demo') {
        header("Location:homepage_content.php?id=" . $iLanguageId . "&success=2");
        exit;
    }

    foreach ($img_arr as $key => $value) {

       if( !empty($value['name']) && is_array($value['name'])){


           $validobj = new validation();
           $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"].', '.$tconfig["tsite_upload_video_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
           $error = $validobj->validateFileType($valueArr,$tconfig["tsite_upload_image_file_extensions"].','.$tconfig["tsite_upload_video_file_extensions"],$imgUploadingExtenstionMsg);
           if ($error){
               $_SESSION['success'] = '3';
               $_SESSION['var_msg'] = $error;
               header("location:"."homepage_content.php?id=".$id);
               exit;
           }

           $totalImageCount = count($value['name']);
           $valueArr = $value;
           for ($i =  0; $i < $totalImageCount ;  $i++)
           {
               $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
               $image_object = $valueArr['tmp_name'][$i];
               $img_name = explode('.',$valueArr['name'][$i]);
               $ext = $img_name[count($img_name) - 1];

               $randomString = uniqid('',true);
               $image_name = 'img_'.$randomString."_".strtotime(date("H:i:s"))."_".$img_name[count($img_name) - 1]. "." . $ext;
               sleep(1);
               if (stripos($key,'banner_section_img') !== false){
                   $key = 'lGeneralBannerSection';
               }
               $check_file_query = "SELECT ".$key." FROM $tbl_name where vCode='".$vCode."'";
               $check_file = $obj->MySQLSelect($check_file_query);
               $sectionData = json_decode($check_file[0][$key],true);
               if ($message_print_id != "" && $sectionData['img'] != ''){
                   $check_file = $img_path.$template.'/'.$sectionData['img'];
                   if ($check_file != '' && file_exists($check_file)){
                       @unlink($check_file);
                   }
               }
               $Photo_Gallery_folder = $img_path.$template."/";
               if (!is_dir($Photo_Gallery_folder)){
                   mkdir($Photo_Gallery_folder,0777);
                   chmod($Photo_Gallery_folder,0777);
               }
               $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder,$image_object,$image_name,'',$tconfig["tsite_upload_image_file_extensions"].','.$tconfig["tsite_upload_video_file_extensions"],$vCode);

               if ($img[2] == "1"){
                   $_SESSION['success'] = '0';
                   $_SESSION['var_msg'] = $img[1];
                   header("location:".$backlink);
               }
               if (!empty($img[0])){
                   if ($key == 'lGeneralBannerSection'){
                       $sectionData['img'][] = $img[0];
                   }
                   $sectionDatajson = getJsonFromAnArrWithoutClean($sectionData);
                   $where = " vCode = '".$vCode."'";
                   $Update[$key] = $sectionDatajson;
                   $obj->MySQLQueryPerform($tbl_name,$Update,'update',$where);
               }
           }
       }


        if (!empty($value['name']) && !is_array($value['name'])) {
            $validobj = new validation();
            $imgUploadingExtenstionMsg = str_replace("####", $tconfig["tsite_upload_image_file_extensions_validation"] . ', ' . $tconfig["tsite_upload_video_file_extensions_validation"], $langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
            $error = $validobj->validateFileType($value, $tconfig["tsite_upload_image_file_extensions"] . ',' . $tconfig["tsite_upload_video_file_extensions"], $imgUploadingExtenstionMsg);

            if($error){
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $error;
                header("location:" . "homepage_content.php?id=".$id);
                exit;
            }

            $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
            $image_object = $value['tmp_name'];
            $img_name = explode('.', $value['name']);
            $randomString = uniqid('', true);
            $image_name = 'img_'. $randomString ."_" . strtotime(date("H:i:s")) . "." . $img_name[count($img_name) - 1];

            sleep(1);
            $second_reg_img = 0;
            if ($key == 'how_it_work_img_first' || $key == 'register_section_img_first') {
                $second_reg_img = 1;
            }
            if ($key == 'how_it_work_img_sec' || $key == 'register_section_img_sec') {
                $second_reg_img = 2;
            }
            if ($key == 'how_it_work_img_third' || $key == 'register_section_img_third') {
                $second_reg_img = 3;
            }
            if ($key == 'how_it_work_img_four' || $key == 'register_section_img_fourth') {
                $second_reg_img = 4;
            }
            if ($key == 'register_section_img_fifth') {
                $second_reg_img = 5;
            }

            if(stripos($key, 'banner_section_img') !== false) $key = 'lGeneralBannerSection';
            else if ($key == 'register_section_img_first' || $key == 'register_section_img_sec' || $key == 'register_section_img_third' || $key == 'register_section_img_fourth' || $key == 'register_section_img_fifth') $key = 'lRegisterSection';
            else if ($key == 'how_it_work_img_first' || $key == 'how_it_work_img_sec' || $key == 'how_it_work_img_third' || $key == 'how_it_work_img_four' ) $key = 'lHowitworkSection';

            $check_file_query = "SELECT " . $key . " FROM $tbl_name where vCode='" . $vCode . "'";
            $check_file = $obj->MySQLSelect($check_file_query);
            $sectionData = json_decode($check_file[0][$key], true);
            if ($second_reg_img == 1) {
                if ($message_print_id != "" && $sectionData['img_first'] != '') {
                    $check_file = $img_path . $template . '/' . $sectionData['img_first'];
                    if ($check_file != '' && file_exists($check_file)) {
                        @unlink($check_file);
                    }
                }
            } else if ($second_reg_img == 2) {
                if ($message_print_id != "" && $sectionData['img_sec'] != '') {
                    $check_file = $img_path . $template . '/' . $sectionData['img_sec'];
                    if ($check_file != '' && file_exists($check_file)) {
                        @unlink($check_file);
                    }
                }
            } else if ($second_reg_img == 3) {
                if ($message_print_id != "" && $sectionData['img_third'] != '') {
                    $check_file = $img_path . $template . '/' . $sectionData['img_third'];
                    if ($check_file != '' && file_exists($check_file)) {
                        @unlink($check_file);
                    }
                }
            } else if ($second_reg_img == 4) {
                if ($key == 'how_it_work_img_four') {
                    if ($message_print_id != "" && $sectionData['img_four'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_four'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else {
                    if ($message_print_id != "" && $sectionData['img_fourth'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_fourth'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                }
                
            } else if ($second_reg_img == 5) {
                if ($message_print_id != "" && $sectionData['img_fifth'] != '') {
                    $check_file = $img_path . $template . '/' . $sectionData['img_fifth'];
                    if ($check_file != '' && file_exists($check_file)) {
                        @unlink($check_file);
                    }
                }
            } else {
                if ($message_print_id != "" && $sectionData['img'] != '') {
                    $check_file = $img_path . $template . '/' . $sectionData['img'];
                    if ($check_file != '' && file_exists($check_file)) {
                        @unlink($check_file);
                    }
                }
            }
            $Photo_Gallery_folder = $img_path . $template . "/";
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"] . ',' . $tconfig["tsite_upload_video_file_extensions"], $vCode);
            if ($img[2] == "1") {
                $_SESSION['success'] = '0';
                $_SESSION['var_msg'] = $img[1];
                header("location:" . $backlink);
            }
            if (!empty($img[0])) {
                if ($second_reg_img == 1) {
                    $sectionData['img_first'] = $img[0];
                } else if ($second_reg_img == 2) {
                    $sectionData['img_sec'] = $img[0];
                } else if ($second_reg_img == 3) {
                    $sectionData['img_third'] = $img[0];
                } else if ($second_reg_img == 4) {
                    if ($key == 'how_it_work_img_four') {
                        $sectionData['img_four'] = $img[0];
                    } else {
                        $sectionData['img_fourth'] = $img[0];
                    }
                } else if ($second_reg_img == 5) {
                    $sectionData['img_fifth'] = $img[0];
                } else {
                    if($key == 'lGeneralBannerSection') {
                        $sectionData['img'][] = $img[0];
                    } else {
                        $sectionData['img'] = $img[0];
                    }                    
                }
                $sectionDatajson = getJsonFromAnArrWithoutClean($sectionData);
                $where = " vCode = '" . $vCode . "'";
                $Update[$key] = $sectionDatajson;
                $obj->MySQLQueryPerform($tbl_name, $Update, 'update', $where);
            }
        }



    }
}

if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:homepage_content.php?id=" . $id . "&success=2");
        exit;
    }

    $check_file_query = "SELECT lGeneralBannerSection,lBookServiceSection,lSafeSection,lHowitworkSection,lRegisterSection FROM $tbl_name where vCode='" . $vCode . "'";
    $check_file = $obj->MySQLSelect($check_file_query);


    $sectionData = json_decode($check_file[0]['lGeneralBannerSection'], true);
    $banner_images_arr = isset($_POST['banner_images']) ? $_POST['banner_images'] : '';
    if(!empty($banner_images_arr)) {
        foreach ($sectionData['img'] as $banner_img) {
            if(!in_array($banner_img, $banner_images_arr)) {
                $banner_images_arr[] = $banner_img;
            }
        }    
    }
    $banner_section_arr['img'] = $banner_images_arr;
    $banner_section_arr = !(empty($sectionData)) ? array_merge($sectionData, $banner_section_arr) : $banner_section_arr;
    $banner_section = getJsonFromAnArrWithoutClean($banner_section_arr);


    $booking_section_arr['title'] = isset($_POST['book_section_title']) ? $_POST['book_section_title'] : '';
    $booking_section_arr['subtitle'] = isset($_POST['book_section_subtitle']) ? $_POST['book_section_subtitle'] : '';
    $book_section = getJsonFromAnArr($booking_section_arr);


    $sectionData = json_decode($check_file[0]['lSafeSection'], true);
    $safe_section_arr['title'] = isset($_POST['safe_section_title']) ? $_POST['safe_section_title'] : '';
    $safe_section = getJsonFromAnArr($safe_section_arr);


    $sectionData = json_decode($check_file[0]['lHowitworkSection'], true);
    $how_it_work_section_arr['title'] = isset($_POST['how_it_work_section_title']) ? $_POST['how_it_work_section_title'] : '';
    $how_it_work_section_arr['desc'] = isset($_POST['how_it_work_section_desc']) ? $_POST['how_it_work_section_desc'] : '';
    $how_it_work_section_arr['title_first'] = isset($_POST['how_it_work_title_first']) ? $_POST['how_it_work_title_first'] : '';
    $how_it_work_section_arr['desc_first'] = isset($_POST['how_it_work_desc_first']) ? $_POST['how_it_work_desc_first'] : '';

    $how_it_work_section_arr['title_sec'] = isset($_POST['how_it_work_title_sec']) ? $_POST['how_it_work_title_sec'] : '';
    $how_it_work_section_arr['desc_sec'] = isset($_POST['how_it_work_desc_sec']) ? $_POST['how_it_work_desc_sec'] : '';

    $how_it_work_section_arr['title_third'] = isset($_POST['how_it_work_title_third']) ? $_POST['how_it_work_title_third'] : '';
    $how_it_work_section_arr['desc_third'] = isset($_POST['how_it_work_desc_third']) ? $_POST['how_it_work_desc_third'] : '';

    $how_it_work_section_arr['title_four'] = isset($_POST['how_it_work_title_four']) ? $_POST['how_it_work_title_four'] : '';
    $how_it_work_section_arr['desc_four'] = isset($_POST['how_it_work_desc_four']) ? $_POST['how_it_work_desc_four'] : '';

    $how_it_work_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
    $how_it_work_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
    $how_it_work_section_arr['img_third'] = isset($sectionData['img_third']) ? $sectionData['img_third'] : '';
    $how_it_work_section_arr['img_four'] = isset($sectionData['img_four']) ? $sectionData['img_four'] : '';
    $how_it_work_section = getJsonFromAnArrWithoutClean($how_it_work_section_arr);


    $sectionData = json_decode($check_file[0]['lRegisterSection'], true);
    $register_section_arr['main_title'] = isset($_POST['register_section_main_title']) ? $_POST['register_section_main_title'] : '';
    $register_section_arr['main_subtitle'] = isset($_POST['register_section_main_subtitle']) ? $_POST['register_section_main_subtitle'] : '';
    $register_section_arr['main_desc'] = isset($_POST['register_section_main_desc']) ? $_POST['register_section_main_desc'] : '';
    $register_section_arr['title_first'] = isset($_POST['register_section_title_first']) ? $_POST['register_section_title_first'] : '';
    $register_section_arr['title_sec'] = isset($_POST['register_section_title_sec']) ? $_POST['register_section_title_sec'] : '';
    $register_section_arr['title_third'] = isset($_POST['register_section_title_third']) ? $_POST['register_section_title_third'] : '';
    $register_section_arr['title_fourth'] = isset($_POST['register_section_title_fourth']) ? $_POST['register_section_title_fourth'] : '';
    $register_section_arr['title_fifth'] = isset($_POST['register_section_title_fifth']) ? $_POST['register_section_title_fifth'] : '';
    $register_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
    $register_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
    $register_section_arr['img_third'] = isset($sectionData['img_third']) ? $sectionData['img_third'] : '';
    $register_section_arr['img_fourth'] = isset($sectionData['img_fourth']) ? $sectionData['img_fourth'] : '';
    $register_section_arr['img_fifth'] = isset($sectionData['img_fifth']) ? $sectionData['img_fifth'] : '';
    $register_section = getJsonFromAnArr($register_section_arr);

    
    $where = "`vCode` = '" . $vCode . "'";
    $query_data = array();
    $query_data['lGeneralBannerSection'] = $banner_section;
    $query_data['lBookServiceSection'] = $book_section;
    $query_data['lSafeSection'] = $safe_section;
    $query_data['lHowitworkSection'] = $how_it_work_section;
    $query_data['lRegisterSection'] = $register_section;

    $id = $obj->MySQLQueryPerform($tbl_name, $query_data, 'update', $where);
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }
    else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    header("location: homepage_content.php?id=" .$iLanguageId);
    exit;
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT hc.*,lm.vTitle FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $iLanguageId  . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCode = $value['vCode'];
            $eStatus = $value['eStatus'];
            $title = $value['vTitle'];
            
            $banner_section = json_decode($value['lGeneralBannerSection'], true);
            $book_section = json_decode($value['lBookServiceSection'], true);
            $safe_section = json_decode($value['lSafeSection'], true);
            $how_it_work_section = json_decode($value['lHowitworkSection'], true);
            $register_section = json_decode($value['lRegisterSection'], true);
            // echo "<pre>"; print_r($banner_section); exit;
        }
    }
}

if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
}

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);

$banner_img_accept_ext = '.' . implode(",.", explode(",", $tconfig['tsite_upload_image_file_extensions'] . ',' . $tconfig['tsite_upload_video_file_extensions']));
?>
<!DOCTYPE html><!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Manage Web Home Page</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style>
        .body-div.innersection {
            box-shadow: -1px -2px 73px 2px #dedede;
            float: none;
        }

        .notes {
            font-weight: 700;
            font-style: italic;
        }

        .languageSelection{
            display: flex;
        }

        .languageSelection  p{
            margin: 0;
        }

        .ui-state-default {
            position: relative;
            margin: 0 0 0 55px;
        }

        .ui-state-default a {
            display: block;
        }
        
        .innerbg_image:after, .innerbg_image:before {
            position:absolute;
            opacity:0;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
        }
        .innerbg_image:after {
            content:'\A';
            width:100%; 
            height:100%;
            top:0; 
            left:0;
            background:rgba(0,0,0,0.75);
        }
        .innerbg_image:before {
            content: attr(data-content);
            width: 100%;
            color: #fff;
            z-index: 1;
            bottom: 42%;
            text-align: center;
            box-sizing: border-box;
            font-size: 16px;
            font-weight: 600;
        }
        .innerbg_image:hover:after, .innerbg_image:hover:before {
            opacity:1;
            cursor: pointer;
        }

        .banner-image-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            padding: 20px;
            background-color: #eeeeee;
            border: 1px solid #cccccc;
            gap: 25px 0;
            padding-left: 0;
        }

        .image-handle {
            position: absolute;
            left: -34px;
            background-color: #ffffff;
            padding: 2px 7px;
            font-size: 18px;
            border: 1px solid #aaaaaa;
            color: #000000;
            cursor: grab;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-8" >
                        <h2><?= $action; ?> Home Content (<?php echo $title; ?>)</h2>
                    </div>
                    <div class="col-lg-4 languageSelection">
                        <div class="col-lg-6" style="text-align: end;margin: auto;">
                            <p style="margin: 0; font-weight:700;" >Select Language:</p>
                        </div>
                        <select onchange="language_wise_page(this);" name="language" id="language"
                                class="form-control">
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
                    </div>
                </div>
            </div>
            <?php
            include('valid_msg.php');
            ?>
            <hr/>
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
                        <input type="hidden" name="backlink" id="backlink" value="homepage_content.php"/>
                        
                        <!-- /*--------------------- banner_section --------------------*/-->
                        <div class="body-div innersection">
                            <div class="form-group banner_section">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>General Banner Section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Images / Videos</label>
                                    </div>
                                    <?php if ($banner_section['img'] != '') { ?>
                                    <div class="col-lg-12">
                                        <ul id="sortable" class="banner-image-list">
                                            <?php foreach ($banner_section['img'] as $vImage) {
                                                $img_ext = pathinfo($vImage, PATHINFO_EXTENSION);
                                                if(in_array($img_ext, explode(",", $tconfig["tsite_upload_video_file_extensions"]))) {
                                                    $media_url = getVideoThumbImageGF($vImage, $tconfig["tsite_upload_apptype_page_images_panel"] . $template, $tconfig["tsite_upload_apptype_page_images"] . $template);
                                                    $media_url = $tconfig["tsite_url"].'resizeImg.php?h=150&src=' . $media_url;
                                                } else {
                                                    $media_url = $tconfig["tsite_url"].'resizeImg.php?h=150&src='.$tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $vImage;
                                                }
                                            ?>
                                            <li class="ui-state-default ui-draggable-handle innerbg_image" data-content="Delete" onclick="deleteBannerImage('<?= $vImage ?>')">
                                                <span class="image-handle"><i class="fa fa-arrows"></i></span>
                                                <a href="javascript:void(0);" >
                                                    <img src="<?= $media_url; ?>" />
                                                </a>
                                                <input type="hidden" name="banner_images[]" value="<?= $vImage ?>">
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <?php } ?>
                                    <div class="col-lg-6">
                                        <input type="file" class="form-control FilUploader" name="banner_section_img[]" id="banner_section_img" accept="<?= $banner_img_accept_ext ?>" multiple="multiple">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image/video size of 1920px X 600px. <br>Recommended format for video is mp4. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12"><hr /></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>About Us Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" value="<?= $langage_lbl_admin['LBL_ABOUT_US_TXT']; ?>" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages_action.php?id=' . getLanguageLabelId('LBL_ABOUT_US_TXT')?>" class="btn btn-info" target="_blank">
                                            <i class="glyphicon glyphicon-pencil" aria-hidden="true"></i>
                                        </a>                                         
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Download App Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" value="<?= $langage_lbl_admin['LBL_DOWNLOAD_APP_TXT']; ?>" readonly>
                                    </div>
                                    <div class="col-lg-2">
                                        <a href="<?= $tconfig['tsite_url_main_admin'] . 'languages_action.php?id=' . getLanguageLabelId('LBL_DOWNLOAD_APP_TXT')?>" class="btn btn-info" target="_blank">
                                            <i class="glyphicon glyphicon-pencil" aria-hidden="true"></i>
                                        </a>                                         
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!----------------------- banner_section ---------------------->

                        <!------------------------- Book section --------------------->
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Book section</h3>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="book_section_title" id="book_section_title" value="<?= $book_section['title']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>SubTitle</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="book_section_subtitle" id="book_section_subtitle" value="<?= $book_section['subtitle']; ?>" placeholder="Subtitle">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!------------------------- Book section --------------------->

                        <!------------------------- Global Platform --------------------->
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12"><h3>Global Platform</h3></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="safe_section_title" id="safe_section_menu_title" value="<?= $safe_section['title']; ?>" placeholder="Title">
                                    </div>
                                </div>
                                <div class="row" style="display: none;">
                                    <div class="col-lg-12">
                                        <label>Main Description</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <textarea class="form-control" rows="5" name="safe_section_desc"><?= $safe_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Note :  Click  <a target="_blank" href="service_section.php?id=<?= $iLanguageId; ?>">here</a> to manage services shown and its content.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!------------------------- Global Platform --------------------->

                        <!------------------------- how it work new --------------------->
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12"><h3>How It Works</h3></div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="how_it_work_section_title" id="how_it_work_section_title" value="<?= $how_it_work_section['title']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>SubTitle<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="how_it_work_section_desc" id="how_it_work_section_desc" value="<?= $how_it_work_section['desc']; ?>" placeholder="SubTitle" required>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#1</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="how_it_work_title_first" value="<?= $how_it_work_section['title_first']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#1</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="how_it_work_desc_first" placeholder="Description"><?= $how_it_work_section['desc_first']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#1</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($how_it_work_section['img_first'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=80&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_first']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_first" value="<?= $how_it_work_section['img_first']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 95px * 95px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#2</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="how_it_work_title_sec" value="<?= $how_it_work_section['title_sec']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#2</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="how_it_work_desc_sec" placeholder="Description"><?= $how_it_work_section['desc_sec']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#2</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($how_it_work_section['img_sec'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=80&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_sec']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_sec" value="<?= $how_it_work_section['img_sec']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 95px * 95px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#3</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="how_it_work_title_third" value="<?= $how_it_work_section['title_third']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#3</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="how_it_work_desc_third" placeholder="Description"><?= $how_it_work_section['desc_third']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#3</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($how_it_work_section['img_third'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=80&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_third']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_third" value="<?= $how_it_work_section['img_third']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 95px * 95px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#4</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="how_it_work_title_four" value="<?= $how_it_work_section['title_four']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#4</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="how_it_work_desc_four" placeholder="Description"><?= $how_it_work_section['desc_four']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#4</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($how_it_work_section['img_third'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=80&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_four']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_four" value="<?= $how_it_work_section['img_four']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 95px * 95px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!------------------------- how it work new --------------------->
                            
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Register section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_main_title" id="register_section_main_title" value="<?= $register_section['main_title']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div style="display:none;" class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_main_subtitle" id="register_section_main_subtitle" value="<?= $register_section['main_subtitle']; ?>" placeholder="Subtitle" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="register_section_main_desc" id="register_section_main_desc" value="<?= $register_section['main_desc']; ?>" placeholder="Description" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 1<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_first" id="register_section_title_first" value="<?= $register_section['title_first']; ?>" placeholder="Title" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 1</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_first'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_first']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_first" id="register_section_img_first" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 740px * 740px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 2<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_sec" id="register_section_title_sec" value="<?= $register_section['title_sec']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 2</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_sec'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_sec']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_sec" id="register_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 740px * 740px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 3<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_third" id="register_section_title_third" value="<?= $register_section['title_third']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 3</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_third'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_third']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_third" id="register_section_img_third" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1000px * 300px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 4<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_fourth" id="register_section_title_fourth" value="<?= $register_section['title_fourth']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 4</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_fourth'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_fourth']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_fourth" id="register_section_img_fourth" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 480px * 270px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 5<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_fifth" id="register_section_title_fifth" value="<?= $register_section['title_fifth']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 5</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_fifth'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_fifth']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_fifth" id="register_section_img_fifth" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 480px * 270px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- End Home Header area-->

                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">

                                <a href="homepage_content.php" class="btn btn-default back_link">Cancel</a>
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
    $(document).ready(function () {
        var referrer;
        <?php if ($goback == 1) { ?>
        alert('<?php echo $var_msg; ?>');

        window.location.href = "homepage_content.php?id=<?php echo $id ?>";


        <?php } ?>
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }

        if (referrer == "") {
            referrer = "homepage_content.php";
        } else {
            referrer = "homepage_content.php";
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

        $( "#sortable" ).sortable({
            delay: 150,
            revert: true,
            handle: ".image-handle",
            helper: 'clone',
            animation: 200,
            tolerance: "pointer"
        });

        $( "#sortable" ).disableSelection();
    });
    $(".FilUploader").change(function () {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif' , 'mp4', 'bmp', 'heic', 'mkv'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            alert("Only formats are allowed : " + fileExtension.join(', '));
            $(this).val('');
            return false;

        }
    });


    //it is bcoz when enter press in any input textbox, then two form so submit remove form and it will delete first icon so enter key disabled it.
    $('form input').keydown(function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
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

    function deleteBannerImage(image_name) {
        if (confirm("Are you sure you want to delete this image/video?") == true) {
            window.location.href = '<?= $tconfig['tsite_url_main_admin'] . 'homepage_content.php?id=' . $iLanguageId . '&banner_img=' ?>' + image_name;
        }
    }
</script>
</body>
<!-- END BODY-->
</html>