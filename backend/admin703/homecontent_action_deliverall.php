<?php
include_once('../common.php');
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
// $tbl_name = 'homecontentfood';
$tbl_name = getAppTypeWiseHomeTable();

$script = 'home_content';
$header_first_label = isset($_POST['header_first_label']) ? $_POST['header_first_label'] : '';
$third_sec_desc = isset($_POST['third_sec_desc']) ? $_POST['third_sec_desc'] : '';
$third_mid_desc_two1 = isset($_POST['third_mid_desc_two1']) ? $_POST['third_mid_desc_two1'] : '';
$third_mid_image_three1 = isset($_POST['third_mid_image_three1']) ? $_POST['third_mid_image_three1'] : '';
$home_banner_left_image = isset($_POST['home_banner_left_image']) ? $_POST['home_banner_left_image'] : '';
$header_second_label = isset($_POST['header_second_label']) ? $_POST['header_second_label'] : '';
$third_mid_desc_two = isset($_POST['third_mid_desc_two']) ? $_POST['third_mid_desc_two'] : '';
$home_banner_right_image = isset($_POST['home_banner_right_image']) ? $_POST['home_banner_right_image'] : '';
$third_sec_title = isset($_POST['third_sec_title']) ? $_POST['third_sec_title'] : '';
$third_mid_title_one = isset($_POST['third_mid_title_one']) ? $_POST['third_mid_title_one'] : '';
$third_mid_desc_three = isset($_POST['third_mid_desc_three']) ? $_POST['third_mid_desc_three'] : '';
$third_mid_image_two = isset($_POST['third_mid_image_two']) ? $_POST['third_mid_image_two'] : '';
$third_mid_title_two = isset($_POST['third_mid_title_two']) ? $_POST['third_mid_title_two'] : '';
$third_mid_desc_one = isset($_POST['third_mid_desc_one']) ? $_POST['third_mid_desc_one'] : '';
$third_mid_image_three = isset($_POST['third_mid_image_three']) ? $_POST['third_mid_image_three'] : '';
$third_mid_title_one1 = isset($_POST['third_mid_title_one1']) ? $_POST['third_mid_title_one1'] : '';
$taxi_app_right_desc = isset($_POST['taxi_app_right_desc']) ? $_POST['taxi_app_right_desc'] : '';
$taxi_app_bg_img = isset($_POST['taxi_app_bg_img']) ? $_POST['taxi_app_bg_img'] : '';
$mobile_app_right_title = isset($_POST['mobile_app_right_title']) ? $_POST['mobile_app_right_title'] : '';
$mobile_app_right_desc = isset($_POST['mobile_app_right_desc']) ? $_POST['mobile_app_right_desc'] : '';
$taxi_app_left_img = isset($_POST['taxi_app_left_img']) ? $_POST['taxi_app_left_img'] : '';
$third_mid_title_three1 = isset($_POST['third_mid_title_three1']) ? $_POST['third_mid_title_three1'] : '';
$third_mid_title_three = isset($_POST['third_mid_title_three']) ? $_POST['third_mid_title_three'] : '';
$third_mid_desc_three1 = isset($_POST['third_mid_desc_three1']) ? $_POST['third_mid_desc_three1'] : '';
$mobile_app_bg_img1 = isset($_POST['mobile_app_bg_img1']) ? $_POST['mobile_app_bg_img1'] : '';
$third_mid_title_two1 = isset($_POST['third_mid_title_two1']) ? $_POST['third_mid_title_two1'] : '';
$third_mid_desc_two1 = isset($_POST['third_mid_desc_two1']) ? $_POST['third_mid_desc_two1'] : '';
//echo '<prE>'; print_R($_REQUEST); exit;
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        //header("Location:home_action.php?success=2");
        header("Location:homecontent_action.php?id=" . $id . "&success=2");
        exit;
    }

    $img_arr = $_FILES;
    if (!empty($img_arr)) {
        foreach ($img_arr as $key => $value) {
            if ($key == 'safe_section_img_first' || $key == 'safe_section_img_sec' || $key == 'safe_section_img_third' || $key == 'safe_section_img_four' || $key == 'register_section_img_first'  || $key == 'register_section_img_sec') {
                if ($key == 'safe_section_img_first' || $key == 'register_section_img_first') {
                    $second_reg_img = 1;
                }
                if ($key == 'safe_section_img_sec' || $key == 'register_section_img_sec') {
                    $second_reg_img = 2;
                }
                if ($key == 'safe_section_img_third') {
                    $second_reg_img = 3;
                }
                if ($key == 'safe_section_img_four') {
                    $second_reg_img = 4;
                }
                if (!empty($value['name'])) {
                    $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                    $image_object = $value['tmp_name'];
                    $img_name = explode('.', $value['name']);
                    $image_name = strtotime(date("H:i:s")) . "." . $img_name[1];
                    sleep(1);
                    if ($key == 'how_it_work_section_img') $key = 'lHowitworkSection';
                    else if ($key == 'register_section_img_first' || $key == 'register_section_img_sec') $key = 'vDeliveryPartContent';
                    else if ($key == 'safe_section_img_first' || $key == 'safe_section_img_sec' || $key == 'safe_section_img_third' || $key == 'safe_section_img_four') $key = 'header_second_label';
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
                    }
                    else if ($second_reg_img == 3) {
                        if ($message_print_id != "" && $sectionData['img_third'] != '') {
                            $check_file = $img_path . $template . '/' . $sectionData['img_third'];
                            if ($check_file != '' && file_exists($check_file)) {
                                @unlink($check_file);
                            }
                        }
                    }
                    else if ($second_reg_img == 3) {
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
                        $sectionDatajson = getJsonFromAnArr($sectionData);
                        // $sql = "UPDATE " . $tbl_name . " SET " . $key . " = '" . $sectionDatajson . "' WHERE `vCode` = '" . $vCode . "'";
                        // $obj->sql_query($sql);
                        $sectionDataUpdate = array();
                        $sectionDataUpdate[$key] = $sectionDatajson;
                        $where = " vCode = '" . $vCode . "'";
                        $obj->MySQLQueryPerform($tbl_name, $sectionDataUpdate, 'update', $where);
                    }
                }
            }
            else {
                if (!empty($value['name'])) {
                    $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                    $temp_gallery = $img_path . '/';
                    $image_object = $value['tmp_name'];
                    $image_name = $value['name'];
                    $check_file_query = "SELECT " . $key . " FROM " . $tbl_name . " where vCode='" . $vCode . "'";
                    $check_file = $obj->MySQLSelect($check_file_query);
                    /* 		        if ($message_print_id != "") {
                      $check_file = $img_path .$template. '/' . $check_file[0][$key];
                      if ($check_file != '' && file_exists($check_file[0][$key]) ) {
                      @unlink($check_file);
                      }
                      } */
                    $Photo_Gallery_folder = $img_path . $template . "/";
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif', $vCode);
                    if ($img[2] == "1") {
                        $_SESSION['success'] = '0';
                        $_SESSION['var_msg'] = $img[1];
                        header("location:" . $backlink);
                    }
                    if (!empty($img[0])) {
                        $sql = "UPDATE " . $tbl_name . " SET " . $key . " = '" . $img[0] . "' WHERE `vCode` = '" . $vCode . "'";
                        $obj->sql_query($sql);
                    }
                }
            }
        }
    }
    $check_file_query = "SELECT header_second_label,vDeliveryPartContent  FROM $tbl_name where vCode='" . $vCode . "'";
    $check_file = $obj->MySQLSelect($check_file_query);
    $sectionData = json_decode($check_file[0]['header_second_label'], true);
    $safe_section_arr['title'] = isset($_POST['safe_section_title']) ? $_POST['safe_section_title'] : '';
    $safe_section_arr['desc'] = isset($_POST['safe_section_desc']) ? $_POST['safe_section_desc'] : '';
    $safe_section_arr['title_first'] = isset($_POST['safe_section_title_first']) ? $_POST['safe_section_title_first'] : '';
    $safe_section_arr['desc_first'] = isset($_POST['safe_section_desc_first']) ? $_POST['safe_section_desc_first'] : '';
    $safe_section_arr['title_sec'] = isset($_POST['safe_section_title_sec']) ? $_POST['safe_section_title_sec'] : '';
    $safe_section_arr['desc_sec'] = isset($_POST['safe_section_desc_sec']) ? $_POST['safe_section_desc_sec'] : '';
    $safe_section_arr['title_third'] = isset($_POST['safe_section_title_third']) ? $_POST['safe_section_title_third'] : '';
    $safe_section_arr['desc_third'] = isset($_POST['safe_section_desc_third']) ? $_POST['safe_section_desc_third'] : '';
    $safe_section_arr['title_four'] = isset($_POST['safe_section_title_four']) ? $_POST['safe_section_title_four'] : '';
    $safe_section_arr['desc_four'] = isset($_POST['safe_section_desc_four']) ? $_POST['safe_section_desc_four'] : '';
    $safe_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
    $safe_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
    $safe_section_arr['img_third'] = isset($sectionData['img_third']) ? $sectionData['img_third'] : '';
    $safe_section_arr['img_four'] = isset($sectionData['img_four']) ? $sectionData['img_four'] : '';
    $safe_section = getJsonFromAnArr($safe_section_arr);


    $sectionData = json_decode($check_file[0]['vDeliveryPartContent'], true);
    $register_section_arr['main_title'] = isset($_POST['register_section_main_title']) ? $_POST['register_section_main_title'] : '';
    $register_section_arr['main_subtitle'] = isset($_POST['register_section_main_subtitle']) ? $_POST['register_section_main_subtitle'] : '';
    $register_section_arr['main_desc'] = isset($_POST['register_section_main_desc']) ? $_POST['register_section_main_desc'] : '';
    $register_section_arr['title_first'] = isset($_POST['register_section_title_first']) ? $_POST['register_section_title_first'] : '';
    $register_section_arr['title_sec'] = isset($_POST['register_section_title_sec']) ? $_POST['register_section_title_sec'] : '';
    $register_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
    $register_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
    $register_section = getJsonFromAnArr($register_section_arr);



    $where = " vCode = '" . $vCode . "'";
    $Update['header_first_label'] = $header_first_label;
    $Update['third_sec_desc'] = $third_sec_desc;
    $Update['third_mid_desc_two1'] = $third_mid_desc_two1;
    $Update['third_mid_desc_one'] = $third_mid_desc_one;
    $Update['header_second_label'] = $header_second_label;
    $Update['third_mid_desc_two'] = $third_mid_desc_two;
    $Update['third_sec_title'] = $third_sec_title;
    $Update['third_mid_title_one'] = $third_mid_title_one;
    $Update['third_mid_desc_three'] = $third_mid_desc_three;
    $Update['third_mid_title_two'] = $third_mid_title_two;
    $Update['third_mid_title_three'] = $third_mid_title_three;
    $Update['third_mid_title_one1'] = $third_mid_title_one1;
    $Update['taxi_app_right_desc'] = $taxi_app_right_desc;
    $Update['mobile_app_right_title'] = $mobile_app_right_title;
    $Update['mobile_app_right_desc'] = $mobile_app_right_desc;
    $Update['third_mid_title_three1'] = $third_mid_title_three1;
    $Update['third_mid_desc_three1'] = $third_mid_desc_three1;
    $Update['third_mid_title_two1'] = $third_mid_title_two1;
    $Update['third_mid_desc_two1'] = $third_mid_desc_two1;
    $Update['header_second_label'] = $safe_section;
    $Update['vDeliveryPartContent'] = $register_section;

    /*$third_mid_title_two1 = isset($_POST['third_mid_title_two1']) ? $_POST['third_mid_title_two1'] : '';
    $third_mid_desc_two1 = isset($_POST['third_mid_desc_two1']) ? $_POST['third_mid_desc_two1'] : '';*/
    $id = $obj->MySQLQueryPerform($tbl_name, $Update, 'update', $where);
    /* $q = "INSERT INTO ";
     $where = '';
     if ($id != '') {
         $q = "UPDATE ";
         $where = " WHERE `vCode` = '" . $vCode . "'";
     }
     $query = $q . " `" . $tbl_name . "` SET
     `header_first_label` = '" . $header_first_label . "',
     `third_sec_desc` = '" . $third_sec_desc . "',
     `third_mid_desc_two1` = '" . $third_mid_desc_two1 . "',
     `third_mid_desc_one` = '" . $third_mid_desc_one . "',
     `header_second_label` = '" . $header_second_label . "',
     `third_mid_desc_two` = '" . $third_mid_desc_two . "',
     `third_sec_title` = '" . $third_sec_title . "',
     `third_mid_title_one` = '" . $third_mid_title_one . "',
     `third_mid_desc_three` = '" . $third_mid_desc_three . "',
     `third_mid_title_two` = '" . $third_mid_title_two . "',
     `third_mid_title_three` = '" . $third_mid_title_three . "',
     `third_mid_title_one1` = '" . $third_mid_title_one1 . "',
     `taxi_app_right_desc` = '" . $taxi_app_right_desc . "',
     `mobile_app_right_title` = '" . $mobile_app_right_title . "',
     `mobile_app_right_desc` = '" . $mobile_app_right_desc . "',
     `third_mid_title_three1` = '" . $third_mid_title_three1 . "',
     `third_mid_desc_three1` = '" . $third_mid_desc_three1 . "'" . $where; //die;
     $obj->sql_query($query);


     $id = ($id != '') ? $id : $obj->GetInsertId(); */
    //header("Location:make_action.php?id=".$id.'&success=1');
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Home Content Insert Successfully.';
    }
    else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Home Content Updated Successfully.';
    }
    header("location:" . $backlink);
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT hc.*,lm.vTitle FROM " . $tbl_name . " as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $third_mid_image_two1 = $value['third_mid_image_two1'];
            $vCode = $value['vCode'];
            $header_first_label = $value['header_first_label'];
            $third_sec_desc = $value['third_sec_desc'];
            $third_mid_desc_two1 = $value['third_mid_desc_two1'];
            $third_mid_desc_one = $value['third_mid_desc_one'];
            $home_banner_left_image = $value['home_banner_left_image'];
            $header_second_label = $value['header_second_label'];
            $third_mid_desc_two = $value['third_mid_desc_two'];
            $home_banner_right_image = $value['home_banner_right_image'];
            $third_mid_image_three1 = $value['third_mid_image_three1'];
            $third_sec_title = $value['third_sec_title'];
            $third_mid_title_one = $value['third_mid_title_one'];
            $third_mid_desc_three = $value['third_mid_desc_three'];
            $third_mid_image_two = $value['third_mid_image_two'];
            $third_mid_title_two = $value['third_mid_title_two'];
            $third_mid_title_three = $value['third_mid_title_three'];
            $third_mid_image_three = $value['third_mid_image_three'];
            $third_mid_title_one1 = $value['third_mid_title_one1'];
            $taxi_app_right_desc = $value['taxi_app_right_desc'];
            $taxi_app_bg_img = $value['taxi_app_bg_img'];
            $mobile_app_right_title = $value['mobile_app_right_title'];
            $mobile_app_right_desc = $value['mobile_app_right_desc'];
            $taxi_app_left_img = $value['taxi_app_left_img'];
            $third_mid_title_three1 = $value['third_mid_title_three1'];
            $third_mid_desc_three1 = $value['third_mid_desc_three1'];
            $mobile_app_bg_img1 = $value['mobile_app_bg_img1'];
            $eStatus = $value['eStatus'];
            $title = $value['vTitle'];
            $third_mid_title_two1 = $value['third_mid_title_two1'];
            $third_mid_desc_two1 = $value['third_mid_desc_two1'];
            $third_mid_image_three1 = $value['third_mid_image_three1'];
            $safe_section = json_decode($value['header_second_label'], true);
            $register_section = json_decode($value['vDeliveryPartContent'], true);
        }
    }
}


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
    <title>Admin | Home Content <?= $action; ?></title>
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

        .innerbg_image {
            width: auto;
            margin: 10px 0;
            height: 150px;
        }

        .notes {
            font-weight: 700;
            font-style: italic;
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
                    <h2><?= $action; ?> Home Content (<?php echo $title; ?>)</h2>
                    <a href="homecontent.php" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
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
                        <input type="hidden" name="backlink" id="backlink" value="homecontent.php"/>
                        <!-- Start Home Header area-->
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home First Section Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="header_first_label" id="header_first_label" value="<?= $header_first_label; ?>" placeholder="Home First Section Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home First Section Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="third_sec_desc" id="third_sec_desc" placeholder="Home First Section Description" required><?= $third_sec_desc; ?></textarea>
                                    </div>
                                </div>
                                <?php
                                if (strtoupper(ONLYDELIVERALL) != "YES") {
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home First Section First Button Description<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-12">
                                            <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_two1" id="third_mid_desc_two1" placeholder="Home First Section First Button Description" required><?= $third_mid_desc_two1; ?></textarea>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <!--<div class="row">
                                                <div class="col-lg-12">
                                                        <label>Home First Section Second Button Description<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-12">
                                                        <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_one"  id="third_mid_desc_one"  placeholder=">Home First Section Second Button Description" required><?= $third_mid_desc_one; ?></textarea>
                                                </div>
                                        </div>-->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home First Left DeliveryAll Image<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php //if($third_mid_image_three1 != '') {  ?>
                                        <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $third_mid_image_three1; ?>" class="innerbg_image"/>
                                        <?php //}  ?>
                                        <input type="file" class="form-control fileuploader" name="third_mid_image_three1" id="third_mid_image_three1" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 844px.]</span>
                                    </div>
                                </div>
                                <?php if ($THEME_OBJ->isDeliverallXv2ThemeActive() == 'No') { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home First Section Banner Image<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php if ($home_banner_left_image != '') { ?>
                                                <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $home_banner_left_image; ?>" class="innerbg_image"/>
                                            <?php } ?>
                                            <input type="file" class="form-control fileuploader" name="home_banner_left_image" id="home_banner_left_image" accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 844px.]</span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12"><h3>How It Works</h3></div>
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
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#1</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="safe_section_title_first" value="<?= $safe_section['title_first']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#1</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="safe_section_desc_first" placeholder="Description"><?= $safe_section['desc_first']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#1</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($safe_section['img_first'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_first']; ?>" class="innerbg_image"/> -->
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_first']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="safe_section_img_first" value="<?= $safe_section['img_first']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#2</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="safe_section_title_sec" value="<?= $safe_section['title_sec']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#2</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="safe_section_desc_sec" placeholder="Description"><?= $safe_section['desc_sec']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#2</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($safe_section['img_sec'] != '') { ?>
                                                    <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_sec']; ?>" class="innerbg_image"/> -->
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_sec']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="safe_section_img_sec" value="<?= $safe_section['img_sec']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#3</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="safe_section_title_third" value="<?= $safe_section['title_third']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#3</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="safe_section_desc_third" placeholder="Description"><?= $safe_section['desc_third']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#3</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($safe_section['img_third'] != '') { ?>
                                                    <!--  <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_third_' . $vCode]; ?>" class="innerbg_image"/> -->
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_third']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="safe_section_img_third" value="<?= $safe_section['img_third']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title#4</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <input type="text" class="form-control" name="safe_section_title_four" value="<?= $safe_section['title_four']; ?>" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Description#4</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <textarea class="form-control" rows="5" name="safe_section_desc_four" placeholder="Description"><?= $safe_section['desc_four']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Image#4</label>
                                            </div>
                                            <div class="col-lg-11">
                                                <?php if ($safe_section['img_third'] != '') { ?>
                                                    <!--  <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_four_' . $vCode]; ?>" class="innerbg_image"/> -->
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safe_section['img_four']; ?>" class="innerbg_image"/>
                                                <?php } ?>
                                                <input type="file" class="form-control" name="safe_section_img_four" value="<?= $safe_section['img_four']; ?>">
                                                <span class="notes">[Note: For Better Resolution Upload only image size of 78px * 78px.]</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-11">
                                        <hr>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($THEME_OBJ->isDeliverallXv2ThemeActive() == 'No') { ?>
                            <div class="body-div innersection">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home Second Section Title<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="header_second_label" id="header_second_label" value="<?= $header_second_label; ?>" placeholder="Home Second Section Title" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home Second Section Description<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-12">
                                            <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_two" id="third_mid_desc_two" placeholder="Home Second Section Description" required><?= $third_mid_desc_two; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home Second Section Right Banner Image<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php if ($home_banner_right_image != '') { ?>
                                                <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $home_banner_right_image; ?>" class="innerbg_image"/>
                                            <?php } ?>
                                            <input type="file" class="form-control fileuploader" name="home_banner_right_image" id="home_banner_right_image" accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 493px * 740px.]</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($THEME_OBJ->isDeliverallXv2ThemeActive() == 'No') { ?>
                            <div class="body-div innersection">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home Third Section Title<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="third_sec_title" id="third_sec_title" value="<?= $third_sec_title; ?>" placeholder="Home Third Section Title" required>
                                        </div>
                                    </div>
                                    <!--<div class="row">
                                            <div class="col-lg-12">
                                                <label>Home Third Section Second Text<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name="third_mid_title_one"  id="third_mid_title_one" value="<?= $third_mid_title_one; ?>" placeholder="Home Third Section Second Text" required>
                                            </div>
                                        </div>-->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home Third Section Description<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-12">
                                            <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_three" id="third_mid_desc_three" placeholder="Home Third Section Description" required><?= $third_mid_desc_three; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Home Third Section Banner Image<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php if ($third_mid_image_two != '') { ?>
                                                <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $third_mid_image_two; ?>" class="innerbg_image"/>
                                            <?php } ?>
                                            <input type="file" class="form-control fileuploader" name="third_mid_image_two" id="third_mid_image_two" accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 405px.]</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Forth Section First Text<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="third_mid_title_two" id="third_mid_title_two" value="<?= $third_mid_title_two; ?>" placeholder="Home Forth Section First Text" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Forth Section Second Text<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_one" id="third_mid_desc_one" placeholder=">Home First Section Second Button Description" required><?= $third_mid_desc_one; ?></textarea>
                                    </div>
                                </div>
                                <!--<div class="row">
                                                <div class="col-lg-12">
                                                        <label>Home Forth Section Second Text<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-6">
                                                        <input type="text" class="form-control" name="third_mid_title_three"  id="third_mid_title_three" value="<?= $third_mid_title_three; ?>" placeholder="Home Forth Section Second Text" required>
                                                </div>
                                        </div>-->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Forth Section Image<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($third_mid_image_three != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $third_mid_image_three; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control fileuploader" name="third_mid_image_three" id="third_mid_image_three" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 30px * 28px.]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Fifth Section Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="third_mid_title_one1" id="third_mid_title_one1" value="<?= $third_mid_title_one1; ?>" placeholder="Home Fifth Section Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Fifth Section Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="taxi_app_right_desc" id="taxi_app_right_desc" placeholder="Home Fifth Section Description" required><?= $taxi_app_right_desc; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Fifth Section Banner Image<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($taxi_app_bg_img != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $taxi_app_bg_img; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control fileuploader" name="taxi_app_bg_img" id="taxi_app_bg_img" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1027px * 520px.]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="display: none" class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Sixth Section Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="mobile_app_right_title" id="mobile_app_right_title" value="<?= $mobile_app_right_title; ?>" placeholder="Home Sixth Section Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Sixth Section Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="mobile_app_right_desc" id="mobile_app_right_desc" placeholder="Home Sixth Section Description" required><?= $mobile_app_right_desc; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Sixth Section Banner Image<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($taxi_app_left_img != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $taxi_app_left_img; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control fileuploader" name="taxi_app_left_img" id="taxi_app_left_img" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 505px.]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="display: none" class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Seventh Section Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="third_mid_title_two1" id="third_mid_title_two1" value="<?= $third_mid_title_two1; ?>" placeholder="Home Seventh Section Title" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Seventh Section Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_two1" id="third_mid_desc_two1" placeholder="Home Sixth Section Description" required><?= $third_mid_desc_two1; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Seventh Section Banner Image<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($third_mid_image_two1 != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $third_mid_image_two1; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control fileuploader" name="third_mid_image_two1" id="third_mid_image_two1" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 505px.]</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($THEME_OBJ->isDeliverallXv2ThemeActive() == 'No') { ?>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Eighth Section Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="third_mid_title_three1" id="third_mid_title_three1" value="<?= $third_mid_title_three1; ?>" placeholder="Home Seventh Section Title" required>
                                    </div>
                                </div>
                                <!--<div class="row">
                                                <div class="col-lg-12">
                                                        <label>Home Seventh Section Second Text<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-6">
                                                        <input type="text" class="form-control" name="third_mid_title_three"  id="third_mid_title_three" value="<?= $third_mid_title_three; ?>" placeholder="Home Forth Section Second Text" required>
                                                </div>
                                        </div>-->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Seventh Section Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="10" name="third_mid_desc_three1" id="third_mid_desc_three1" placeholder="Home Seventh Section Description" required><?= $third_mid_desc_three1; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Home Seventh Section Banner Image<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($mobile_app_bg_img1 != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/" . $mobile_app_bg_img1; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control fileuploader" name="mobile_app_bg_img1" id="mobile_app_bg_img1" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 427px.]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
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
                                        <input type="text" class="form-control" name="register_section_main_title" id="register_section_main_title" value="<?= $register_section['main_title']; ?>" placeholder="Title" >
                                    </div>
                                </div>
                                <div style="display:none;" class="row">
                                    <div class="col-lg-12">
                                        <label>Subtitle<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_main_subtitle" id="register_section_main_subtitle" value="<?= $register_section['main_subtitle']; ?>" placeholder="Subtitle" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_main_desc" id="register_section_main_desc" value="<?= $register_section['main_desc']; ?>" placeholder="Description" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 1<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_first" id="register_section_title_first" value="<?= $register_section['title_first']; ?>" placeholder="Title" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 1</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_first'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_first']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_first" id="register_section_img_first" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px.]</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image Title 2<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_title_sec" id="register_section_title_sec" value="<?= $register_section['title_sec']; ?>" placeholder="Title" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image 2</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php if ($register_section['img_sec'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_sec']; ?>" class="innerbg_image"/>
                                        <?php } ?>
                                        <input type="file" class="form-control FilUploader" name="register_section_img_sec" id="register_section_img_sec" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px.]</span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /*--------------------- general_section --------------------*/-->
                        <!-- End Home Header area-->
                        <div class="row" style="display: none;">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?= $action; ?> Home Content">
                                <input type="reset" value="Reset" class="btn btn-default">
                                <!-- 									<a href="javascript:void(0);" onclick="reset_form('_home_content_form');" class="btn btn-default">Reset</a> -->
                                <a href="homecontent.php" class="btn btn-default back_link">Cancel</a>
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
        if ($("#previousLink").val() == "") { //alert('pre1');
            referrer = document.referrer;
            // alert(referrer);
        } else { //alert('pre2');
            referrer = $("#previousLink").val();
        }

        if (referrer == "") {
            referrer = "homecontent.php";
        } else { //alert('hi');
            $("#backlink").val(referrer);
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
    $(".fileuploader").change(function () {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            alert("Only formats are allowed : " + fileExtension.join(', '));
            $(this).val('');
            return false;

        }
    });
</script>
</body>
<!-- END BODY-->
</html>