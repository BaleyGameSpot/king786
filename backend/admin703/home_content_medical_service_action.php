<?php
include_once('../common.php');
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = "";
//$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
//$tbl_name = $script = 'homecontent_medicalservice_v2';
$tbl_name = $script = 'homecontent';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
$third_mid_image_three1 = $third_mid_title_three1 = $third_mid_title_three = $third_mid_desc_three1 = $mobile_app_bg_img1 = $third_mid_desc_one1 = '';
if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
//$template = 'CJDocv2';

if ($THEME_OBJ->isXThemeActive() == 'Yes') {
    $script = 'homecontent_medicalservice';
    $tbl_name = getAppTypeWiseHomeTable();
    $tbl_name = "homecontent_medicalservice_v2";
    $iLanguageMasId = 0;
    if (empty($vCode)) {
        $sql = "SELECT hc.vCode, lm.iLanguageMasId FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        $vCode = $db_data[0]['vCode'];
        $iLanguageMasId = $db_data[0]['iLanguageMasId'];
    }
    $earnBusinessDetailsquery = "SELECT learnServiceCatSection,lbusinessServiceCatSection FROM $tbl_name where vCode='" . $vCode . "'";
    $earnBusinessData = $obj->MySQLSelect($earnBusinessDetailsquery);
    if (empty($earnBusinessData[0]['learnServiceCatSection'])) {
        $earnDetails['earn']['title'] = 'Earn';
        $earnDetails['earn']['subtitle'] = 'Earn Handsome Commission';
        $earnDetails['earn']['desc'] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled.";
        $earnDetails['earn']['images'] = 'earn.svg';
        $earnDetailsJson = $obj->SqlEscapeString(json_encode($earnDetails));
        $query = "UPDATE `" . $tbl_name . "` SET
        `learnServiceCatSection` = '" . $earnDetailsJson . "' WHERE `vCode` = '" . $vCode . "'";
        $id = $obj->sql_query($query);
    }
    if (empty($earnBusinessData[0]['lbusinessServiceCatSection'])) {
        $businessDetails['business']['title'] = 'Business';
        $businessDetails['business']['subtitle'] = 'Business';
        $businessDetails['business']['desc'] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled.";
        $businessDetails['business']['images'] = 'business.svg';
        $businessDetailsJson = $obj->SqlEscapeString(json_encode($businessDetails));
        $query = "UPDATE `" . $tbl_name . "` SET
        `lbusinessServiceCatSection` = '" . $businessDetailsJson . "' WHERE `vCode` = '" . $vCode . "'";
        $id = $obj->sql_query($query);
    }
    $img_arr = $_FILES;
    /* echo "<pre>";
     print_r($img_arr);
     exit;*/
    /* print_R($_FILES);
     exit;*/
    //$img_arr['call_section_img'] = $_FILES['call_section_img'];
    if (!empty($img_arr)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:home_content_medical_service.php?id=" . $id . "&success=2");
            exit;
        }
        foreach ($img_arr as $key => $value) {

            $second_reg_img = '';
            if ($key == 'safe_section_img_first' || $key == 'register_section_img_first' || $key == 'how_it_work_img_first') {
                $second_reg_img = 1;
            }
            if ($key == 'safe_section_img_sec' || $key == 'register_section_img_sec' || $key == 'how_it_work_img_sec') {
                $second_reg_img = 2;
            }
            if ($key == 'safe_section_img_third' || $key == 'how_it_work_img_third') {
                $second_reg_img = 3;
            }
            if ($key == 'how_it_work_img_four') {
                $second_reg_img = 4;
            }
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                $image_object = $value['tmp_name'];
                $img_name = explode('.', $value['name']);
                $image_name = strtotime(date("H:i:s")) . "." . $img_name[1];
                sleep(1);
                if ($key == 'how_it_work_section_img') $key = 'lHowitworkSection';
                else if ($key == 'download_section_img') $key = 'lDownloadappSection';
                else if ($key == 'secure_section_img') $key = 'lSecuresafeSection';
                else if ($key == 'call_section_img') $key = 'lCalltobookSection';
                else if ($key == 'general_section_img') $key = 'lGeneralBannerSection';
                else if ($key == 'register_section_img_first' || $key == 'register_section_img_sec') $key = 'lRegisterSection';
                else if ($key == 'safe_section_img_first' || $key == 'safe_section_img_sec' || $key == 'safe_section_img_third') $key = 'lSafeSection';
                else if ($key == 'how_it_work_img_first' || $key == 'how_it_work_img_sec' || $key == 'how_it_work_img_third' || $key == 'how_it_work_img_four') $key = 'lHowitworkSection';
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
                    if ($message_print_id != "" && $sectionData['img_four'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_four'];
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
                    } else if ($second_reg_img == 2) {
                        $sectionData['img_sec'] = $img[0];
                    } else if ($second_reg_img == 3) {
                        $sectionData['img_third'] = $img[0];
                    } else if ($second_reg_img == 4) {
                        $sectionData['img_four'] = $img[0];
                    } else {
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
    }
    if (isset($_POST['submit'])) {

        function dataready($data)
        {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            $order = array("rn", "\\r\\n", "\\n", "\\r", "<p>&nbsp;</p>");
            $replace = array(" ", " ", " ", " ", "");
            $data = str_replace($order, $replace, $data);
            return $data;
        }

        $check_file_query = "SELECT lRegisterSection,lSafeSection,lGeneralBannerSection,lHowitworkSection,lSecuresafeSection,lDownloadappSection,lCalltobookSection,lBookServiceSection FROM $tbl_name where vCode='" . $vCode . "'";
        $check_file = $obj->MySQLSelect($check_file_query);
        $sectionData = json_decode($check_file[0]['lHowitworkSection'], true);
        $how_it_work_section_arr['title'] = isset($_POST['how_it_work_section_title']) ? $_POST['how_it_work_section_title'] : '';
        $how_it_work_section_arr['desc'] = isset($_POST['how_it_work_section_desc']) ? $_POST['how_it_work_section_desc'] : '';
        // $how_it_work_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $how_it_work_section = getJsonFromAnArrWithoutClean($how_it_work_section_arr);
        $sectionData = json_decode($check_file[0]['lDownloadappSection'], true);
        $download_section_arr['title'] = isset($_POST['download_section_title']) ? $_POST['download_section_title'] : '';
        $download_section_arr['subtitle'] = isset($_POST['download_section_sub_title']) ? $_POST['download_section_sub_title'] : '';
        $download_section_arr['desc'] = isset($_POST['download_section_desc']) ? $_POST['download_section_desc'] : '';
        $download_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $download_section = getJsonFromAnArr($download_section_arr);
        $sectionData = json_decode($check_file[0]['lSecuresafeSection'], true);
        $secure_section_arr['title'] = isset($_POST['secure_section_title']) ? $_POST['secure_section_title'] : '';
        $secure_section_arr['desc'] = isset($_POST['secure_section_desc']) ? $_POST['secure_section_desc'] : '';
        $secure_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $secure_section = getJsonFromAnArr($secure_section_arr);
        $sectionData = json_decode($check_file[0]['lCalltobookSection'], true);
        $call_section_arr['title'] = isset($_POST['call_section_title']) ? $_POST['call_section_title'] : '';
        $call_section_arr['desc'] = isset($_POST['call_section_desc']) ? $_POST['call_section_desc'] : '';
        $call_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $call_section = getJsonFromAnArr($call_section_arr);
        $vearnbusiness_category = isset($_POST['vearnbusiness_category']) ? $_POST['vearnbusiness_category'] : '';
        $vearnbusiness_category = json_encode($vearnbusiness_category);

        $lServiceSection = isset($_POST['lServiceSection']) ? $_POST['lServiceSection'] : '';
        $lSafeSection = isset($_POST['lSafeSection']) ? $_POST['lSafeSection'] : '';

        if ($THEME_OBJ->isMedicalServicev2ThemeActive() == "Yes") {
            $sectionData = json_decode($check_file[0]['lRegisterSection'], true);
            $register_section_arr['main_title'] = isset($_POST['register_section_main_title']) ? $_POST['register_section_main_title'] : '';
            $register_section_arr['main_subtitle'] = isset($_POST['register_section_main_subtitle']) ? $_POST['register_section_main_subtitle'] : '';
            $register_section_arr['main_desc'] = isset($_POST['register_section_main_desc']) ? $_POST['register_section_main_desc'] : '';
            $register_section_arr['title_first'] = isset($_POST['register_section_title_first']) ? $_POST['register_section_title_first'] : '';
            $register_section_arr['title_sec'] = isset($_POST['register_section_title_sec']) ? $_POST['register_section_title_sec'] : '';
            $register_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
            $register_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
            $register_section = getJsonFromAnArr($register_section_arr);
            $sectionData = json_decode($check_file[0]['lGeneralBannerSection'], true);
            $general_section_arr['title'] = isset($_POST['general_section_title']) ? $_POST['general_section_title'] : '';
            $general_section_arr['desc'] = isset($_POST['general_section_desc']) ? dataready($_POST['general_section_desc']) : '';
            $general_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
            $general_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
            $general_section = getJsonFromAnArr($general_section_arr);
            $sectionData = json_decode($check_file[0]['lSafeSection'], true);
            $safe_section_arr['title'] = isset($_POST['safe_section_title']) ? $_POST['safe_section_title'] : '';
            $safe_section_arr['desc'] = isset($_POST['safe_section_desc']) ? $_POST['safe_section_desc'] : '';
            $safe_section_arr['title_first'] = isset($_POST['safe_section_title_first']) ? $_POST['safe_section_title_first'] : '';
            $safe_section_arr['desc_first'] = isset($_POST['safe_section_desc_first']) ? $_POST['safe_section_desc_first'] : '';
            $safe_section_arr['title_sec'] = isset($_POST['safe_section_title_sec']) ? $_POST['safe_section_title_sec'] : '';
            $safe_section_arr['desc_sec'] = isset($_POST['safe_section_desc_sec']) ? $_POST['safe_section_desc_sec'] : '';
            $safe_section_arr['title_third'] = isset($_POST['safe_section_title_third']) ? $_POST['safe_section_title_third'] : '';
            $safe_section_arr['desc_third'] = isset($_POST['safe_section_desc_third']) ? $_POST['safe_section_desc_third'] : '';
            $safe_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
            $safe_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
            $safe_section_arr['img_third'] = isset($sectionData['img_third']) ? $sectionData['img_third'] : '';
            $safe_section = getJsonFromAnArr($safe_section_arr);
            $booking_section_arr['title'] = isset($_POST['book_section_title']) ? $_POST['book_section_title'] : '';
            $booking_section_arr['subtitle'] = isset($_POST['book_section_subtitle']) ? $_POST['book_section_subtitle'] : '';
            $book_section = getJsonFromAnArr($booking_section_arr);
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
        }

        //$vearnbusiness_category = isset($sectionData['vearnbusiness_category']) ? $sectionData['vearnbusiness_category'] : '';
        //$call_section = stripslashes(json_encode($call_section_arr));
        if (isset($_POST['vehicleHomeIcon'])) {
            $vehicle_category_ids = implode(',', array_keys($_POST['vehicleHomeIcon']));
        } else {
            $vehicle_category_ids = '';
        }

    }
} else {
}
if (isset($_POST['catlogo'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:home_content_medical_service.php?id=" . $id . "&success=2");
        exit;
    }
    if (isset($_FILES['vHomepageLogo']) && $_FILES['vHomepageLogo']['name'] != "") {
        $filecheck = basename($_FILES['vHomepageLogo']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vHomepageLogo']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        if ($flag_error == 1) {
            $_SESSION['success'] = '';
            $_SESSION['var_msg'] = '';
            header("Location:home_content_medical_service.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
            exit;
        }
    }
    $vacategoryid = isset($_POST['aid']) ? $_POST['aid'] : '';
    $img_arr = $_FILES;
    if ($THEME_OBJ->isCubexThemeActive() != 'Yes' && $THEME_OBJ->isCubeJekXThemeActive() != 'Yes' && $THEME_OBJ->isMedicalServicev2ThemeActive() != 'Yes') {
        if (!empty($img_arr)) {
            foreach ($img_arr as $key => $value) {
                if (!empty($value['name'])) {
                    $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                    //$temp_gallery = $img_path . '/';
                    $image_object = $value['tmp_name'];
                    $image_name = $value['name'];
                    $check_file_query = "SELECT " . $key . " FROM " . $sql_vehicle_category_table_name . " where iVehicleCategoryId='" . $vacategoryid . "'";
                    $check_file = $obj->MySQLSelect($check_file_query);
                    if ($message_print_id != "") {
                        $check_file = $img_path . '/' . $check_file[0][$key];
                        if ($check_file != '' && file_exists($check_file[0][$key])) {
                            @unlink($check_file);
                        }
                    }
                    $Photo_Gallery_folder = $img_path . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif');
                    //$img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder,$image_object,$image_name,'','png,jpg,jpeg,gif','');
                    if ($img[2] == "1") {
                        $_SESSION['success'] = '0';
                        $_SESSION['var_msg'] = $img[1];
                        header("location:" . $backlink);
                    }
                    if (!empty($img[0])) {
                        $sql = "UPDATE " . $sql_vehicle_category_table_name . " SET " . $key . " = '" . $img[0] . "' WHERE iVehicleCategoryId = '" . $vacategoryid . "'";
                        $obj->sql_query($sql);
                        $_SESSION['success'] = '1';
                        $_SESSION['var_msg'] = $img[1];
                    } else {
                        $_SESSION['success'] = '0';
                        $_SESSION['var_msg'] = $img[1];
                    }
                }
            }
        }
    }
}
if (isset($_POST['submit'])) {
    /*if (SITE_TYPE == 'Demo') {
        //header("Location:home_action.php?success=2");
        header("Location:home_content_cubex.php?id=" . $id . "&success=2");
        exit;
    }*/
    //    $q = "INSERT INTO ";
    //    $where = '';
    //    if ($id != '') {
    //        $q = "UPDATE ";
    //        $where = " WHERE `vCode` = '" . $vCode . "'";
    //    }
    //    //$call_section = $obj->SqlEscapeString($call_section);
    //    $query = $q . " `" . $tbl_name . "` SET
    // `vehicle_category_ids` = '" . $vehicle_category_ids . "',
    // `vearnbusiness_category` = '" . $vearnbusiness_category . "',
    // `lHowitworkSection` = '" . $how_it_work_section . "',
    // `lSecuresafeSection` = '" . $secure_section . "',
    // `lDownloadappSection` = '" . $download_section . "',
    // `lCalltobookSection` = '" . $call_section . "'" . $where; //die;
    //    $obj->sql_query($query);
    //    $id = ($id != '') ? $id : $obj->GetInsertId();
    //header("Location:make_action.php?id=".$id.'&success=1');
    $where = "`vCode` = '" . $vCode . "'";
    $query_data = array();
    $query_data['vehicle_category_ids'] = $vehicle_category_ids;
    $query_data['booking_ids'] = $booking_ids;
    $query_data['vearnbusiness_category'] = $vearnbusiness_category;
    $query_data['lHowitworkSection'] = $how_it_work_section;
    $query_data['lSecuresafeSection'] = $secure_section;
    $query_data['lDownloadappSection'] = $download_section;
    $query_data['lCalltobookSection'] = $call_section;
    $query_data['lBookServiceSection'] = $book_section;
    $query_data['lRegisterSection'] = $register_section;
    $query_data['lServiceSection'] = $lServiceSection;
    $query_data['lSafeSection'] = $lSafeSection;


    if ($THEME_OBJ->isMedicalServicev2ThemeActive() == "Yes") {
        $query_data['lGeneralBannerSection'] = $general_section;

    }



    $id = $obj->MySQLQueryPerform($tbl_name, $query_data, 'update', $where);

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:" . $backlink);
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT hc.*,lm.vTitle FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $id . "'";
    //$sql = "SELECT hc.*,lm.vTitle FROM homecontent as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE hc.id = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //print_R($db_data);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {


            $vCode = $value['vCode'];
            $eStatus = $value['eStatus'];
            $title = $value['vTitle'];
            $vehicle_category_ids = $value['vehicle_category_ids'];
            $vearnbusiness_category = $value['vearnbusiness_category'];
            $how_it_work_section = (array)json_decode($value['lHowitworkSection']);
            $secure_section = json_decode($value['lSecuresafeSection'], true);
            $download_section = json_decode($value['lDownloadappSection'], true);
            $call_section = json_decode($value['lCalltobookSection'], true);
            $learnServiceCatSection = json_decode($earnBusinessData[0]['learnServiceCatSection'], true);
            $lbusinessServiceCatSection = json_decode($earnBusinessData[0]['lbusinessServiceCatSection'], true);
            if ($THEME_OBJ->isMedicalServicev2ThemeActive() == "Yes") {
                $register_section = json_decode($value['lRegisterSection'], true);
                $general_section = json_decode($value['lGeneralBannerSection'], true);
                $general_section = json_decode($value['lGeneralBannerSection'], true);
                $safe_section = json_decode($value['lSafeSection'], true);
                $book_section = json_decode($value['lBookServiceSection'], true);
                $lServiceSection = $value['lServiceSection'];
                $lSafeSection = $value['lSafeSection'];
            }
        }
    }
}


$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable();
if ($THEME_OBJ->isCubexThemeActive() == 'Yes' || $THEME_OBJ->isMedicalServicev2ThemeActive() == 'Yes') {
    $ssql1 = $ssql2 = $ssql3 = '';
    if ($ufxEnable != 'Yes') {
        $ssql1 .= " AND eCatType!='ServiceProvider'";
    } else {
        $ssql2 .= " OR eCatType='ServiceProvider'";
    }
    $ssql .= " AND (iServiceId IN ($enablesevicescategory) OR eCatType IN ('Ride', 'MotoRide', 'Fly', 'Donation') OR (eFor = 'DeliveryCategory' AND eCatType = 'MoreDelivery')  $ssql2 )  $ssql1";
}
if ($THEME_OBJ->isCubeJekXThemeActive() == 'Yes') {
    if ($ufxEnable != 'Yes') {
        $ssql .= " AND eCatType!='ServiceProvider'";
    }
}
if (!$MODULES_OBJ->isAirFlightModuleAvailable(1)) {
    $ssql .= " AND eCatType != 'Fly'";
}
if (!$MODULES_OBJ->isDonationFeatureAvailable()) {
    $ssql .= " AND eCatType != 'Donation'";
}
$vcatdata = array();
$catquery = "SELECT iVehicleCategoryId,vHomepageLogo,vCategory_EN FROM  `" . $sql_vehicle_category_table_name . "` WHERE iParentId = 0 and eStatus = 'Active' $ssql ORDER BY iDisplayOrderHomepage";
$vcatdata = $obj->MySQLSelect($catquery);
//echo $vehicle_category_ids;
//$vehicle_category_ids = '';
$vcatdata_first = getSeviceCategoryDataForHomepage($vehicle_category_ids, 0, 1);

$vcatdata_sec = getSeviceCategoryDataForHomepage($vehicle_category_ids, 1, 1);

if (!empty($vehicle_category_ids)) {

    $vcatdata_main = array_merge($vcatdata_first, $vcatdata_sec);
} else {

    $vcatdata_main = $vcatdata_first;
}


//echo "<PRE>";print_R($vcatdata_first);
//print_R($vcatdata_sec);
//print_R($vcatdata_main);
if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
} else if (isset($_POST['catlogo']) && $_POST['catlogo'] == 'catlogo') {
    $required = '';
}
$display = "";
if ($THEME_OBJ->isMedicalServicev2ThemeActive() == "Yes") {
    $display = 'style="display: none"';
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
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $action; ?> Home Content (<?php echo $title; ?>)</h2>
                    <a href="home_content_medical_service.php" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
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
                    <form method="post" name="_home_content_form_ms" id="_home_content_form_ms" action=""
                          enctype='multipart/form-data'>
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="vCode" value="<?= $vCode; ?>">
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="home_content_medical_service.php"/>
                        <?php if ($THEME_OBJ->isMedicalServicev2ThemeActive() != 'Yes' && $THEME_OBJ->isCubexThemeActive() != 'Yes' && $THEME_OBJ->isCubeJekXThemeActive() != 'Yes' && $THEME_OBJ->isMedicalServicev2ThemeActive() != 'Yes') {
                        } else { ?>
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
                                            <input type="text" class="form-control" name="how_it_work_section_title"
                                                   id="how_it_work_section_title"
                                                   value="<?= $how_it_work_section['title']; ?>" placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>SubTitle<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="how_it_work_section_desc"
                                                   id="how_it_work_section_desc"
                                                   value="<?= $how_it_work_section['desc'];  ?>"
                                                   placeholder="SubTitle">
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col-lg-3">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Title#1</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <input type="text" class="form-control"
                                                           name="how_it_work_title_first"
                                                           value="<?= $how_it_work_section['title_first']; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Description#1</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" rows="5"
                                                              name="how_it_work_desc_first"
                                                              placeholder="Description"><?= $how_it_work_section['desc_first']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image#1</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($how_it_work_section['img_first'] != '') { ?>
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_first']; ?>"
                                                             class="innerbg_image"/>
                                                    <? } ?>
                                                    <input type="file" class="form-control" name="how_it_work_img_first"
                                                           value="<?= $how_it_work_section['img_first']; ?>">
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
                                                    <input type="text" class="form-control" name="how_it_work_title_sec"
                                                           value="<?= $how_it_work_section['title_sec']; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Description#2</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" rows="5" name="how_it_work_desc_sec"
                                                              placeholder="Description"><?= $how_it_work_section['desc_sec']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image#2</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($how_it_work_section['img_sec'] != '') { ?>
                                                        <!-- <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_sec']; ?>" class="innerbg_image"/> -->
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_sec']; ?>"
                                                             class="innerbg_image"/>
                                                    <? } ?>
                                                    <input type="file" class="form-control" name="how_it_work_img_sec"
                                                           value="<?= $how_it_work_section['img_sec']; ?>">
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
                                                    <input type="text" class="form-control"
                                                           name="how_it_work_title_third"
                                                           value="<?= $how_it_work_section['title_third']; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Description#3</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" rows="5"
                                                              name="how_it_work_desc_third"
                                                              placeholder="Description"><?= $how_it_work_section['desc_third']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image#3</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($how_it_work_section['img_third'] != '') { ?>
                                                        <!--  <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_third_' . $vCode]; ?>" class="innerbg_image"/> -->
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_third']; ?>"
                                                             class="innerbg_image"/>
                                                    <? } ?>
                                                    <input type="file" class="form-control" name="how_it_work_img_third"
                                                           value="<?= $how_it_work_section['img_third']; ?>">
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
                                                    <input type="text" class="form-control"
                                                           name="how_it_work_title_four"
                                                           value="<?= $how_it_work_section['title_four']; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Description#4</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" rows="5" name="how_it_work_desc_four"
                                                              placeholder="Description"><?= $how_it_work_section['desc_four']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image#4</label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($how_it_work_section['img_third'] != '') { ?>
                                                        <!--  <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_four_' . $vCode]; ?>" class="innerbg_image"/> -->
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['img_four']; ?>"
                                                             class="innerbg_image"/>
                                                    <? } ?>
                                                    <input type="file" class="form-control" name="how_it_work_img_four"
                                                           value="<?= $how_it_work_section['img_four']; ?>">
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

                            <!------------------------- how it work new --------------------->

                            <div class="body-div innersection" <?= $display ?>>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-12"><h3>Download Section</h3></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Title<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="download_section_title"
                                                   id="download_section_title"
                                                   value="<?= $download_section['title']; ?>" placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Subtitle<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="download_section_sub_title"
                                                   id="download_section_sub_title"
                                                   value="<?= $download_section['subtitle']; ?>" placeholder="Subtitle">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Description</label>
                                        </div>
                                        <div class="col-lg-12">
                                            <textarea class="form-control ckeditor" rows="10"
                                                      name="download_section_desc" id="download_section_desc"
                                                      placeholder="Description"><?= $download_section['desc']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <? if ($download_section['img'] != '') { ?>
                                                <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $download_section['img']; ?>"
                                                     class="innerbg_image"/>
                                            <? } ?>
                                            <input type="file" class="form-control FilUploader"
                                                   name="download_section_img" id="download_section_img"
                                                   accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 1920px * 405px.]</span>
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
                                            <input type="text" class="form-control" name="register_section_main_title"
                                                   id="register_section_main_title"
                                                   value="<?= $register_section['main_title']; ?>" placeholder="Title">
                                        </div>
                                    </div>
                                    <div style="display:none;" class="row">
                                        <div class="col-lg-12">
                                            <label>Subtitle<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control"
                                                   name="register_section_main_subtitle"
                                                   id="register_section_main_subtitle"
                                                   value="<?= $register_section['main_subtitle']; ?>"
                                                   placeholder="Subtitle">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Description<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="register_section_main_desc"
                                                   id="register_section_main_desc"
                                                   value="<?= $register_section['main_desc']; ?>"
                                                   placeholder="Description">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image Title 1<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="register_section_title_first"
                                                   id="register_section_title_first"
                                                   value="<?= $register_section['title_first']; ?>" placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image 1</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <? if ($register_section['img_first'] != '') { ?>
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_first']; ?>"
                                                     class="innerbg_image"/>
                                            <? } ?>
                                            <input type="file" class="form-control FilUploader"
                                                   name="register_section_img_first" id="register_section_img_first"
                                                   accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px.]</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image Title 2<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="register_section_title_sec"
                                                   id="register_section_title_sec"
                                                   value="<?= $register_section['title_sec']; ?>" placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Image 2</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <? if ($register_section['img_sec'] != '') { ?>
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $register_section['img_sec']; ?>"
                                                     class="innerbg_image"/>
                                            <? } ?>
                                            <input type="file" class="form-control FilUploader"
                                                   name="register_section_img_sec" id="register_section_img_sec"
                                                   accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px.]</span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /*--------------------- general_section --------------------*/-->
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
                                            <input type="text" class="form-control" name="general_section_title"
                                                   id="general_section_title" value="<?= $general_section['title']; ?>"
                                                   placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Description</label>
                                        </div>
                                        <div class="col-lg-12">
                                            <textarea class="form-control ckeditor" rows="10"
                                                      name="general_section_desc" id="general_section_desc"
                                                      placeholder="Description"><?= $general_section['desc']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>First Image</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <? if ($general_section['img'] != '') { ?>
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $general_section['img']; ?>"
                                                     class="innerbg_image"/>
                                            <? } ?>
                                            <input type="file" class="form-control FilUploader"
                                                   name="general_section_img" id="general_section_img"
                                                   accept=".png,.jpg,.jpeg,.gif">
                                            <br/>
                                            <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 547px.]</span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /*--------------------- general_section --------------------*/-->
                                  <!------------------------- Global Platform --------------------->
                                  <!------------------------- Global Platform --------------------->

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
                                            <input type="text" class="form-control" name="book_section_title"
                                                   id="book_section_title" value="<?= $book_section['title']; ?>"
                                                   placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>SubTitle</label>
                                        </div>
                                        <div class="col-lg-12">
                                            <input type="text" class="form-control" name="book_section_subtitle"
                                                   id="book_section_subtitle" value="<?= $book_section['subtitle']; ?>"
                                                   placeholder="Subtitle">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!------------------------- Book section --------------------->

                            <!-- Start Home icons area-->
                            <div class="body-div innersection">
                                <div class="row">
                                    <div class="col-lg-12"><h3>Sevice Section</h3></div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="lServiceSection" id="lServiceSection"
                                               value="<?= $lServiceSection; ?>" placeholder="Service Section Title"
                                               required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="lSafeSection" id="lSafeSection"
                                               value="<?= $lSafeSection; ?>" placeholder="Service Section SubTitle"
                                               required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-lg-10row" style="height:500px;overflow:scroll;">
                                        <label>Note : The Category name will be set in all other language defined from
                                            the Admin - Service Category Module.</label>
                                        <br/>
                                        <label>Service Icon</label>
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th style="text-align:center;">Icon</th>
                                                <th>Name</th>
                                                <th align="center" style="text-align:center;">Edit</th>
                                            </tr>
                                            </thead>
                                            <?php

                                            if (ENABLE_DYNAMIC_CREATE_PAGE == "Yes") {
                                                if (!empty($vcatdata_main)) {
                                                    $booking_ids = explode(',', $vehicle_category_ids);
                                                    //$booking_ids = explode(',',$booking_ids);
                                                    for ($i = 0; $i < scount($vcatdata_main); $i++) {
                                                        $disabled = $checkbooking = "";
                                                        if (in_array($vcatdata_main[$i]['iVehicleCategoryId'], $booking_ids)) {
                                                            $checkbooking = "checked";
                                                        } ?>
                                                        <tbody>
                                                        <td>
                                                            <input type="checkbox"
                                                                   name="vehicleHomeIcon[<?= $vcatdata_main[$i]['iVehicleCategoryId']; ?>]"
                                                                   id="vehicleHomeIcon"
                                                                   value="1" <?= $checkbooking; ?><?= $disabled; ?>>
                                                        </td>
                                                        <td align="center">
                                                            <?php if ($vcatdata_main[$i]['vHomepageLogoOurServices'] != '') { ?>
                                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=70&h=70&src=' . $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata_main[$i]['vHomepageLogoOurServices'] ?>"
                                                                     style="width:35px;height:35px;">
                                                            <? } ?>
                                                        </td>
                                                        <td>
                                                            <?= $vcatdata_main[$i]['vCatName']; ?>
                                                        </td>
                                                        <td align="center" class="action-btn001">
                                                            <div class="share-button openHoverAction-class"
                                                                 style="display: block;">
                                                                <label class="entypo-export"><span><img
                                                                                src="images/settings-icon.png"
                                                                                alt=""></span></label>
                                                                <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iDriverId']; ?> openHoverDetails openHoverDetailsNew">
                                                                    <ul>
                                                                        <li class="entypo-twitter entypo-twitter-new"
                                                                            data-network="twitter">
                                                                            <a href="<?php echo $tconfig["tsite_url_main_admin"] . "vehicle_category_action.php?id=" . $vcatdata_main[$i]['iVehicleCategoryId'] . "&homepage=1"; ?>">Edit
                                                                                Details</a>
                                                                        </li>
                                                                        <!--<li class="entypo-twitter entypo-twitter-new" data-network="twitter"><a href="<?php
                                                                        if (!empty($vcatdata_main[$i]['commonadminurl'])) {
                                                                            echo $tconfig["tsite_url_main_admin"] . $vcatdata_main[$i]['commonadminurl'] . "?id=" . $iLanguageMasId; ?>">Edit Common Page
                                                                        <? } else {
                                                                            echo $tconfig["tsite_url_main_admin"] . $vcatdata_main[$i]['adminurl'] . "?id=" . $iLanguageMasId; ?>">Edit Inner Page
                                                                        <? } ?></a></li>-->
                                                                        <? //if(!empty($vcatdata_main[$i]['commonadminurl'])) { ?>
                                                                        <li class="entypo-twitter entypo-twitter-new"
                                                                            data-network="twitter">
                                                                            <a href="<?php echo $tconfig["tsite_url_main_admin"] . $vcatdata_main[$i]['adminurl'] . "&id=" . $iLanguageMasId; ?>">Edit
                                                                                Inner Page
                                                                            </a></li>
                                                                        <? //} ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        </tbody>
                                                        <?php
                                                    }
                                                }
                                            }
                                            else {
                                                if (!empty($vcatdata)) {
                                                    for ($i = 0; $i < scount($vcatdata); $i++) {
                                                        $selectvehiclecat = explode(',', $vehicle_category_ids);
                                                        $check = "";
                                                        if (in_array($vcatdata[$i]['iVehicleCategoryId'], $selectvehiclecat)) {
                                                            $check = "checked";
                                                        }
                                                        ?>
                                                        <tbody>
                                                        <td>
                                                            <input type="checkbox"
                                                                   name="vehicleHomeIcon[<?= $vcatdata[$i]['iVehicleCategoryId']; ?>]"
                                                                   id="vehicleHomeIcon" value="1" <?= $check; ?>>
                                                        </td>
                                                        <td align="center">
                                                            <?php if ($vcatdata[$i]['vHomepageLogo'] != '') { ?>
                                                                <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo'] ?>"
                                                                     style="width:35px;height:35px;">
                                                            <? } ?>
                                                        </td>
                                                        <td>
                                                            <?= $vcatdata[$i]['vCategory_EN']; ?>
                                                        </td>
                                                        <td align="center">
                                                            <a href="<?php echo $tconfig["tsite_url_main_admin"] . "vehicle_category_action.php?id=" . $vcatdata[$i]['iVehicleCategoryId'] . "&homepage=1"; ?>"
                                                               data-toggle="tooltip" title="" data-original-title="Edit"
                                                               target="blank"><img src="img/edit-icon.png"
                                                                                   alt="Edit"></a>
                                                        </td>
                                                        </tbody>
                                                        <?php
                                                    }
                                                }
                                            }

                                            ?>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- End Home icons area-->
                        <?php } ?>
                        <!-- End Home Header area-->
                        <div class="row" style="display: none;">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox"
                                           name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class=" btn btn-default" name="submit" id="submit"
                                       value="<?= $action; ?> Home Content">
                                <input type="reset" value="Reset" class="btn btn-default">
                                <!-- 									<a href="javascript:void(0);" onclick="reset_form('_home_content_form');" class="btn btn-default">Reset</a> -->
                                <a href="home_content_medical_service.php" class="btn btn-default back_link">Cancel</a>
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
<?php
if ($THEME_OBJ->isMedicalServicev2ThemeActive() == "Yes") {
    $vcatdata = [];
}
if (!empty($vcatdata)) {
    for ($i = 0; $i < scount($vcatdata); $i++) {
        ?>
        <div class="modal fade" id="<?= $vcatdata[$i]['iVehicleCategoryId']; ?>" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <form method="post" name="test" id="test" action="" enctype='multipart/form-data'>
                <input type="hidden" name="aid" value="<?php echo $vcatdata[$i]['iVehicleCategoryId'] ?>"/>
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">Service Icon</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php
                                    if (!empty($vcatdata[$i]['vHomepageLogo'])) {
                                        ?>
                                        <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . '/' . $vcatdata[$i]['vHomepageLogo']; ?>"
                                             class="innerbg_image"/>
                                    <?php } ?>
                                </div>
                                <div class="col-lg-12">
                                    <span><b><?= $vcatdata[$i]['vCategory_EN']; ?></b></span>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="col-lg-12">
                                    <input type="file" class="form-control FilUploader" name="vHomepageLogo"
                                           id="vHomepageLogo" accept=".png,.jpg,.jpeg,.gif">
                                </div>
                                <br/>
                                <div class="col-lg-12">
                                    <span>Note: For Better Resolution Upload only image size of 360px*360px.</span>
                                </div>
                                <br/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="catlogo" value="catlogo" class="btn btn-primary">Save changes
                            </button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}
?>
<div data-backdrop="static" data-keyboard="false" class="modal fade" id="service_icon_modal" tabindex="-1" role="dialog"
     aria-labelledby="ServiceIconModel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <input type="hidden" name="removeidmodel" id="removeidmodel"/>
            <div class="modal-header"><h4>Remove Icon?</h4></div>
            <div class="modal-body"><p>Are you sure you cant to remove icon?</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-success btn-ok action_modal_submit">Ok</a></div>
        </div>
    </div>
</div>
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
        //history.go(-1);
        window.location.href = "home_content_medical_service.php?id=<?php echo $id ?>";


        <?php } ?>
        if ($("#previousLink").val() == "") { //alert('pre1');
            referrer = document.referrer;
            // alert(referrer);
        } else { //alert('pre2');
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "home_content_medical_service.php";
        } else { //alert('hi');
            //$("#backlink").val(referrer);
            referrer = "home_content_medical_service.php";
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
</script>
</body>
<!-- END BODY-->
</html>
