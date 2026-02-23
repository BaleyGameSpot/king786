<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-home-page-content')) {
    $userObj->redirect();
}

$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

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

if ($THEME_OBJ->isXThemeActive() == 'Yes') {
    $script = 'homecontent';
    $tbl_name = getAppTypeWiseHomeTable();

    $iLanguageMasId = 0;
    if (empty($vCode)) {
        $sql = "SELECT hc.vCode, lm.iLanguageMasId FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        $vCode = $db_data[0]['vCode'];
        $iLanguageMasId = $db_data[0]['iLanguageMasId'];
    }
    
    $img_arr = $_FILES;

    if (!empty($img_arr)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:homepage_content.php?id=" . $id . "&success=2");
            exit;
        }
        foreach ($img_arr as $key => $value) {

            if($key == 'safe_section_img_first' || $key == 'register_section_img_first' || $key == 'how_it_work_img_first')
            {
                $second_reg_img = 1;
            }
            if($key == 'safe_section_img_sec' || $key == 'register_section_img_sec' || $key == 'how_it_work_img_sec' )
            {
                $second_reg_img = 2;
            }
            if($key == 'how_it_work_img_third' )
            {
                $second_reg_img = 3;
            }
            if($key == 'how_it_work_img_four' )
            {
                $second_reg_img = 4;
            }
            if($key == 'call_section_img' || $key == 'secure_section_img' ){
                 $second_reg_img = 0;
            }
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                $image_object = $value['tmp_name'];
                $img_name = explode('.', $value['name']);
                $randomString = uniqid('', true);
                $image_name = 'img_'. $randomString ."_" . strtotime(date("H:i:s")) . "." . $img_name[scount($img_name) - 1];
                //$image_name = strtotime(date("H:i:s")) . "." . $img_name[scount($img_name) - 1];
                sleep(1);
                if ($key == 'how_it_work_section_img') $key = 'lHowitworkSection';
                else if ($key == 'download_section_img') $key = 'lDownloadappSection';
                else if ($key == 'secure_section_img') $key = 'lSecuresafeSection';
                else if ($key == 'call_section_img') $key = 'lCalltobookSection';
                else if ($key == 'general_section_img_sec') $key = 'lGeneralBannerSection';
                else if ($key == 'register_section_img_first' || $key == 'register_section_img_sec') $key = 'lRegisterSection';
                else if ($key == 'safe_section_img_first' || $key == 'safe_section_img_sec') $key = 'lSafeSection';
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
                }
                else if ($second_reg_img == 2) {
                    if ($message_print_id != "" && $sectionData['img_sec'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_sec'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                }else if ($second_reg_img == 3) {
                    if ($message_print_id != "" && $sectionData['img_third'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_third'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else if ($second_reg_img == 4) {
                    if ($message_print_id != "" && $sectionData['img_four'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_four'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                }
                else {
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
                }
                $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', 'svg,png,jpg,jpeg,gif', $vCode);
                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }
                if (!empty($img[0])) {
                    if ($second_reg_img == 1) {
                        $sectionData['img_first'] = $img[0];
                    }
                    else if ($second_reg_img == 2) {
                        $sectionData['img_sec'] = $img[0];
                    }
                    else if ($second_reg_img == 3) {
                        $sectionData['img_third'] = $img[0];
                    }
                    else if ($second_reg_img == 4) {
                        $sectionData['img_four'] = $img[0];
                    }
                    else {
                        $sectionData['img'] = $img[0];
                    }
                    $sectionDatajson = getJsonFromAnArrWithoutClean($sectionData);
                    
                    $sectionDataUpdate = array();
                    $sectionDataUpdate[$key] = $sectionDatajson;

                    $where = " vCode = '" . $vCode . "'";
                    $obj->MySQLQueryPerform($tbl_name, $sectionDataUpdate, 'update', $where);
                }
            }
        }
    }
    if (isset($_POST['submit'])) {

        $check_file_query = "SELECT lRegisterSection,lSafeSection,lGeneralBannerSection,lHowitworkSection,lSecuresafeSection,lDownloadappSection,lCalltobookSection,lBookServiceSection FROM $tbl_name where vCode='" . $vCode . "'";
        $check_file = $obj->MySQLSelect($check_file_query);
        $sectionData = json_decode($check_file[0]['lHowitworkSection'], true);
        $how_it_work_section_arr['title'] = isset($_POST['how_it_work_section_title']) ? $_POST['how_it_work_section_title'] : '';
        $how_it_work_section_arr['desc'] = isset($_POST['how_it_work_section_desc']) ? $_POST['how_it_work_section_desc'] : '';
        $how_it_work_section = getJsonFromAnArrWithoutClean($how_it_work_section_arr);
        
        $sectionData = json_decode($check_file[0]['lSecuresafeSection'], true);
        $secure_section_arr['title'] = isset($_POST['secure_section_title']) ? $_POST['secure_section_title'] : '';
        $secure_section_arr['desc'] = isset($_POST['secure_section_desc']) ? $_POST['secure_section_desc'] : '';
        $secure_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $secure_section = getJsonFromAnArrWithoutClean($secure_section_arr);
        $sectionData = json_decode($check_file[0]['lCalltobookSection'], true);
        $call_section_arr['title'] = isset($_POST['call_section_title']) ? $_POST['call_section_title'] : '';
        $call_section_arr['desc'] = isset($_POST['call_section_desc']) ? $_POST['call_section_desc'] : '';
        $call_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $call_section = getJsonFromAnArrWithoutClean($call_section_arr);
        
        $sectionData = json_decode($check_file[0]['lRegisterSection'], true);
        $register_section_arr['main_title'] = isset($_POST['register_section_main_title']) ? $_POST['register_section_main_title'] : '';
        $register_section_arr['main_subtitle'] = isset($_POST['register_section_main_subtitle']) ? $_POST['register_section_main_subtitle'] : '';
        $register_section_arr['main_desc'] = isset($_POST['register_section_main_desc']) ? $_POST['register_section_main_desc'] : '';
        $register_section_arr['title_first'] = isset($_POST['register_section_title_first']) ? $_POST['register_section_title_first'] : '';
        $register_section_arr['title_sec'] = isset($_POST['register_section_title_sec']) ? $_POST['register_section_title_sec'] : '';
        $register_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
        $register_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
        $register_section = getJsonFromAnArrWithoutClean($register_section_arr);

        $sectionData = json_decode($check_file[0]['lGeneralBannerSection'], true);
        $general_section_arr['title'] = isset($_POST['general_section_title']) ? $_POST['general_section_title'] : '';
        $general_section_arr['desc'] = isset($_POST['general_section_desc']) ? $_POST['general_section_desc'] : '';
        $general_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $general_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
        $general_section = getJsonFromAnArrWithoutClean($general_section_arr);

        $sectionData = json_decode($check_file[0]['lSafeSection'], true);


        $safe_section_arr['title'] = isset($_POST['safe_section_title']) ? $_POST['safe_section_title'] : '';
        $safe_section_arr['desc'] = isset($_POST['safe_section_desc']) ? $_POST['safe_section_desc'] : '';

        $safe_section_arr['title_first'] = isset($_POST['safe_section_title_first']) ? $_POST['safe_section_title_first'] : '';
        $safe_section_arr['desc_first'] = isset($_POST['safe_section_desc_first']) ? $_POST['safe_section_desc_first'] : '';

        $safe_section_arr['title_sec'] = isset($_POST['safe_section_title_sec']) ? $_POST['safe_section_title_sec'] : '';
        $safe_section_arr['desc_sec'] = isset($_POST['safe_section_desc_sec']) ? $_POST['safe_section_desc_sec'] : '';

        $safe_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
        $safe_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';

        $safe_section = getJsonFromAnArrWithoutClean($safe_section_arr);

        $sectionData = json_decode($check_file[0]['lHowitworkSection'], true);

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
        
        $vehicle_category_ids = '';
    }
}


if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:homepage_content.php?id=" . $id . "&success=2");
        exit;
    }

    $where = "`vCode` = '" . $vCode . "'";
    $query_data = array();
    $query_data['vehicle_category_ids'] = $vehicle_category_ids;
    $query_data['lHowitworkSection'] = $how_it_work_section;
    $query_data['lSecuresafeSection'] = $secure_section;
    $query_data['lCalltobookSection'] = $call_section;
    $query_data['lRegisterSection'] = $register_section;
    $query_data['lGeneralBannerSection'] = $general_section;
    $query_data['lSafeSection'] = $safe_section;

    $id = $obj->MySQLQueryPerform($tbl_name, $query_data, 'update', $where);
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }
    else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:" . $backlink);
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT hc.*,lm.vTitle FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCode = $value['vCode'];
            $eStatus = $value['eStatus'];
            $title = $value['vTitle'];
            $vehicle_category_ids = $value['vehicle_category_ids'];
            $how_it_work_section = json_decode($value['lHowitworkSection'], true);
            $secure_section = json_decode($value['lSecuresafeSection'], true);
            $call_section = json_decode($value['lCalltobookSection'], true);
            $register_section = json_decode($value['lRegisterSection'], true);
            $general_section = json_decode($value['lGeneralBannerSection'], true);
            $safe_section = json_decode($value['lSafeSection'], true);
        }
    }
}


if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
}
else if (isset($_POST['catlogo']) && $_POST['catlogo'] == 'catlogo') {
    $required = '';
}

$display = 'style="display: none"';

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
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
    <? include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style>
        .body-div.innersection {
            box-shadow: -1px -2px 73px 2px #dedede;
            float: none;
        }

        .innerbg_image {
            width: auto;
            margin: 10px 0;
            height: 100px;
            
        }

        .hiw_img {
            padding: 10px;
            background-color: #CCCCCC;
            border-radius: 10px;
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
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
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
                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div><br/>
                    <? } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <? } ?>
                    <form method="post" name="_home_content_form" id="_home_content_form" action="" enctype='multipart/form-data'>
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="vCode" value="<?= $vCode; ?>">
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="homepage_content.php"/>
                        
                        <!-- /*--------------------- general_section --------------------*/-->
                        <div class="body-div innersection">
                            <div class="form-group general_section">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>General Banner Section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="general_section_title" id="general_section_title" value="<?= $general_section['title']; ?>" placeholder="Title">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="general_section_desc" id="general_section_desc" placeholder="Description"><?= $general_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>First Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($general_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $general_section['img']; ?>" class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="general_section_img_sec" id="general_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /*--------------------- general_section --------------------*/-->
                              <!------------------------- Global Platform --------------------->
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12"><h3>Global Platform</h3></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Menu Title</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="safe_section_title" id="safe_section_menu_title" value="<?= $safe_section['title']; ?>" placeholder="Menu Title">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Main Description</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <textarea class="form-control" rows="5" name="safe_section_desc"><?= $safe_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12"><h4>Global Platform service Data</h4></div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#1</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="safe_section_title_first" value="<?= $safe_section['title_first']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#1</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control" rows="5" name="safe_section_desc_first" placeholder="Description"><?= $safe_section['desc_first']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#1</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <? if ($safe_section['img_first'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_first']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control" name="safe_section_img_first" value="<?= $safe_section['img_first']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#2</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="safe_section_title_sec" value="<?= $safe_section['title_sec']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#2</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <textarea class="form-control" rows="5" name="safe_section_desc_sec" placeholder="Description"><?= $safe_section['desc_sec']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#2</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <? if ($safe_section['img_sec'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_sec']; ?>" class="innerbg_image"/>
                                                <? } ?>
                                                <input type="file" class="form-control" name="safe_section_img_sec" value="<?= $safe_section['img_sec']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div><!------------------------- Global Platform --------------------->
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
                                    <div class="col-lg-6">
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
                                                <? if ($how_it_work_section['img_first'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_first']; ?>" class="innerbg_image hiw_img"/>
                                                <? } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_first" value="<?= $how_it_work_section['img_first']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
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
                                                <? if ($how_it_work_section['img_sec'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_sec']; ?>" class="innerbg_image hiw_img"/>
                                                <? } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_sec" value="<?= $how_it_work_section['img_sec']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
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
                                                <? if ($how_it_work_section['img_third'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_third']; ?>" class="innerbg_image hiw_img"/>
                                                <? } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_third" value="<?= $how_it_work_section['img_third']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
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
                                                <? if ($how_it_work_section['img_third'] != '') { ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_four']; ?>" class="innerbg_image hiw_img"/>
                                                <? } ?>
                                                <input type="file" class="form-control" name="how_it_work_img_four" value="<?= $how_it_work_section['img_four']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-11">
                                        <hr>
                                    </div>
                                </div>
                            </div>
                        </div>

                    
                        
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12"><h3>Taxi Section</h3></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="secure_section_title" id="secure_section_title" value="<?= $secure_section['title']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="secure_section_desc" id="secure_section_desc" placeholder="Description"><?= $secure_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($secure_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $secure_section['img']; ?>" class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="secure_section_img" id="secure_section_img" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 564px * 570px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12"><h3>Delivery Section</h3></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="call_section_title" id="call_section_title" value="<?= $call_section['title']; ?>" placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                        <h5>[Note: Please use #SUPPORT_PHONE# predefined tags to display the support phone value. Please go to Settings >> General section to change the values of above predefined tags.]</h5>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="call_section_desc" id="call_section_desc" placeholder="Description"><?= $call_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($call_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $call_section['img']; ?>" class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="call_section_img" id="call_section_img" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
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
                                    <div class="col-lg-6">
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
                                        <? if ($register_section['img_first'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_first']; ?>" class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_first" id="register_section_img_first" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
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
                                        <? if ($register_section['img_sec'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_sec']; ?>" class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_sec" id="register_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- End Home Header area-->

                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">
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

<? include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="../assets/plugins/ckeditor/ckeditor.js"></script>
<script src="../assets/plugins/ckeditor/config.js"></script>
<script>
    /* CKEDITOR.replace('ckeditor', {
         allowedContent: {
             i: {
                 classes: 'fa*'
             },
             span: true
         }
     });*/
</script>
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
    });
    $(".FilUploader").change(function () {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif'];
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
    $('.entypo-export').click(function (e) {
        e.stopPropagation();
        var $this = $(this).parent().find('div');
        $(".openHoverAction-class div").not($this).removeClass('active');
        $this.toggleClass('active');
    });

    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
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
</script>
</body>
<!-- END BODY-->
</html>