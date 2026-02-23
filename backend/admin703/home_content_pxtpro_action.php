<?php

$_REQUEST['ENABLE_DEBUG'] = 'Yes';
include_once('../common.php');

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
$message_print_id = $id;
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$cubexthemeonh = 0;
if ($THEME_OBJ->isPXTProThemeActive() == 'Yes' || $THEME_OBJ->isProPTXThemeActive() == 'Yes') {
    $cubexthemeonh = 1;
}
if ($cubexthemeonh == 1) {
    $script = 'homecontent_cubejekx';
    $tbl_name = getAppTypeWiseHomeTable();
  
    $iLanguageMasId = 0;
    if (empty($vCode)) {
        $sql = "SELECT hc.vCode, lm.iLanguageMasId FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        $vCode = $db_data[0]['vCode'];
        $iLanguageMasId = $db_data[0]['iLanguageMasId'];
    }
    $img_arr = $_FILES;

    //$img_arr['call_section_img'] = $_FILES['call_section_img'];
    if (!empty($img_arr)) {
        if (SITE_TYPE == 'Demo') {
            header("Location:home_content_ridecx.php?id=" . $id . "&success=2");
            exit;
        }
        //$img_arr['call_section_img'] = $_FILES['call_section_img'];
        foreach ($img_arr as $key => $value) {
            //if($key == 'vHomepageLogo') continue;
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_apptype_page_images_panel"];
                $image_object = $value['tmp_name'];
                $img_name = explode('.', $value['name']);
                //$image_name = $img_name[0] . "_" . strtotime(date("H:i:s")) . "." . $img_name[1];
                $randomString = uniqid('', true);
                $image_name = 'img_'. $randomString ."_" . strtotime(date("H:i:s")) . "." . $img_name[scount($img_name) - 1];

                sleep(1);
                $second_reg_img = 0;
                if ( $key == 'register_section_img_first') {
                    $second_reg_img = 1;
                }
                if ( $key == 'register_section_img_sec') {
                    $second_reg_img = 2;
                }
                if ( $key == 'register_section_img_third') {
                    $second_reg_img = 3;
                }
                if ( $key == 'register_section_img_fourth') {
                    $second_reg_img = 4;
                }
                if ( $key == 'register_section_img_fifth') {
                    $second_reg_img = 5;
                }


                if($key == "register_section_img_first" || $key == "register_section_img_sec" || $key == "register_section_img_third" || $key == "register_section_img_fourth" || $key == "register_section_img_fifth")
                {
                    $key = 'lRegisterSection';
                }

                if ($key == 'register_section_img_first') $img_str = 'img_first_'; else $img_str = 'img';

                if ($key == 'how_it_work_section_img') $key = 'lHowitworkSection'; else if ($key == 'travel_section_img') $key = 'lTravelSection'; else if ($key == 'pool_section_img') $key = 'lPoolSection'; else if ($key == 'call_section_img') $key = 'lCalltobookSection'; else if ($key == 'general_section_img') $key = 'lGeneralBannerSection'; else if ($key == 'general_section_img_sec') $key = 'lGeneralBannerSection'; else if ($key == 'safety_section_img') $key = 'lSecuresafeSection';else if ($key == 'lMainServiceSection_img') $key = 'lMainServiceSection';else if ($key == 'lUseSeviceSection_img') $key = 'lUseSeviceSection'; 
                /* For How it works Added By PJ  */
                for ($i = 1; $i <= 4; $i++) {
                    if ($key == 'how_it_work_section_hiw_img' . $i) {
                        $key = 'lHowitworkSection';
                        $img_str = 'hiw_img' . $i;
                    }
                }

                /* For Main Services Section Added By PJ  */
                for ($i = 1; $i <= 4; $i++) {
                    if ($key == 'lMainServiceSection_ms_img' . $i) {
                        $key = 'lMainServiceSection';
                        $img_str = 'ms_img' . $i;
                    }
                }

                /* For Main Services Section Added By PJ  */
                for ($i = 1; $i <= 2; $i++) {
                    if ($key == 'lUseSeviceSection_ms_img' . $i) {
                        $key = 'lUseSeviceSection';
                        $img_str = 'ms_img' . $i;
                    }
                }

                for ($i = 1; $i <= 2; $i++) {
                    if ($key == 'safety_section_ms_img' . $i) {
                        $key = 'lSecuresafeSection';
                        $img_str = 'ms_img' . $i;
                    }
                }

                appendDebugText($key , 'table filed name');
                $check_file_query = "SELECT " . $key . " FROM $tbl_name where vCode='" . $vCode . "'";
                $check_file = $obj->MySQLSelect($check_file_query);
                $sectionData = json_decode($check_file[0][$key], true);
                if ($second_safety_img == 1) {
                    if ($message_print_id != "" && $sectionData['img_first'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_first'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else if ($second_safety_img == 2) {
                    if ($message_print_id != "" && $sectionData['img_sec'] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData['img_sec'];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                } else if ($second_reg_img == 1) {
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
                } else {
                    
                    if ($message_print_id != "" && $sectionData[$img_str] != '') {
                        $check_file = $img_path . $template . '/' . $sectionData[$img_str];
                        if ($check_file != '' && file_exists($check_file)) {
                            @unlink($check_file);
                        }
                    }
                }
                $Photo_Gallery_folder = $img_path . $template . "/";
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img = $UPLOAD_OBJ->GeneralFileUploadHome($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif', $vCode);
               // print_R($img);
                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }
                if (!empty($img[0])) {
                    if ($second_gen_img == 1) {
                        $sectionData['img_sec'] = $img[0];
                    } else if ($second_safety_img == 1) {
                        $sectionData['img_first'] = $img[0];
                    } else if ($second_safety_img == 2) {
                        $sectionData['img_sec'] = $img[0];
                    } else if ($second_reg_img == 1) {
                        $sectionData['img_first'] = $img[0];
                    } else if ($second_reg_img == 2) {
                        $sectionData['img_sec'] = $img[0];
                    }else if ($second_reg_img == 3) {
                        $sectionData['img_third'] = $img[0];
                    }else if ($second_reg_img == 4) {
                        $sectionData['img_fourth'] = $img[0];
                    }else if ($second_reg_img == 5) {
                        $sectionData['img_fifth'] = $img[0];
                    } else {
                        $sectionData[$img_str] = $img[0];
                    }
                    $sectionDatajson = $obj->getJsonFromAnArr($sectionData);

                    $where = " vCode = '" . $vCode . "'";
                    $Update[$key] = $sectionDatajson;

                    $obj->MySQLQueryPerform($tbl_name, $Update, 'update', $where);

                    /*$sql = "UPDATE " . $tbl_name . " SET " . $key . " = '" . $sectionDatajson . "' WHERE `vCode` = '" . $vCode . "'";
                    $obj->sql_query($sql);*/
                }
            }
        }
    }
    if (isset($_POST['submit'])) {
        $check_file_query = "SELECT lHowitworkSection,lTravelSection,lSecuresafeSection,lPoolSection,lCalltobookSection,lGeneralBannerSection,lCalculateSection,lRegisterSection,lMainServiceSection,lUseSeviceSection FROM $tbl_name where vCode='" . $vCode . "'";
        $check_file = $obj->MySQLSelect($check_file_query);

        $sectionData = json_decode($check_file[0]['lGeneralBannerSection'], true);
        $general_section_arr['title'] = isset($_POST['general_section_title']) ? $_POST['general_section_title'] : '';
        $general_section_arr['desc'] = isset($_POST['general_section_desc']) ? $_POST['general_section_desc'] : '';
        $general_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $general_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
        $general_section_arr = !(empty($sectionData)) ? array_merge($sectionData, $general_section_arr) : $general_section_arr;
        $general_section = $obj->getJsonFromAnArr($general_section_arr);

        $sectionData = json_decode($check_file[0]['lHowitworkSection'], true);
        $how_it_work_section_arr['title'] = isset($_POST['how_it_work_section_title']) ? $_POST['how_it_work_section_title'] : '';
        $how_it_work_section_arr['desc'] = isset($_POST['how_it_work_section_desc']) ? $_POST['how_it_work_section_desc'] : '';
        $how_it_work_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $how_it_work_section_arr = !(empty($sectionData)) ? array_merge($sectionData, $how_it_work_section_arr) : $how_it_work_section_arr;
        /* For How it works Added By PJ 25 Sep 2019 */
        for ($i = 1; $i <= 4; $i++) {
            $how_it_work_section_arr['hiw_title' . $i] = isset($_POST['how_it_work_section_hiw_title' . $i]) ? $_POST['how_it_work_section_hiw_title' . $i] : '';
            $how_it_work_section_arr['hiw_desc' . $i] = isset($_POST['how_it_work_section_hiw_desc' . $i]) ? $_POST['how_it_work_section_hiw_desc' . $i] : '';
        }
        $how_it_work_section = $obj->getJsonFromAnArr($how_it_work_section_arr);

        $sectionData = json_decode($check_file[0]['lTravelSection'], true);
        $travel_section_arr['title'] = isset($_POST['travel_section_title']) ? $_POST['travel_section_title'] : '';
        $travel_section_arr['desc'] = isset($_POST['travel_section_desc']) ? $_POST['travel_section_desc'] : '';
        $travel_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $travel_section = $obj->getJsonFromAnArr($travel_section_arr);

        $sectionData = json_decode($check_file[0]['lPoolSection'], true);
        //$pool_section_arr['menu_title'] = isset($_POST['pool_section_menu_title']) ? $_POST['pool_section_menu_title'] : '';
        $pool_section_arr['title'] = isset($_POST['pool_section_title']) ? $_POST['pool_section_title'] : '';
        $pool_section_arr['desc'] = isset($_POST['pool_section_desc']) ? $_POST['pool_section_desc'] : '';
        $pool_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $pool_section_arr = !(empty($sectionData)) ? array_merge($sectionData, $pool_section_arr) : $pool_section_arr;
        $pool_section = $obj->getJsonFromAnArr($pool_section_arr);

        $sectionData = json_decode($check_file[0]['lSecuresafeSection'], true);
        $safety_section_arr['main_title'] = isset($_POST['safety_section_main_title']) ? $_POST['safety_section_main_title'] : '';
        $safety_section_arr['main_desc'] = isset($_POST['safety_section_main_desc']) ? $_POST['safety_section_main_desc'] : '';
        $safety_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $safety_section_arr = !(empty($sectionData)) ? array_merge($sectionData, $safety_section_arr) : $safety_section_arr;
        /* For How it works Added By PJ 25 Sep 2019 */
        for ($i = 1; $i <= 2; $i++) {
            $safety_section_arr['ms_title' . $i] = isset($_POST['safety_section_ms_title' . $i]) ? $_POST['safety_section_ms_title' . $i] : '';
            $safety_section_arr['ms_desc' . $i] = isset($_POST['safety_section_ms_desc' . $i]) ? $_POST['safety_section_ms_desc' . $i] : '';
        }
        appendDebugText($safety_section_arr, $separate = '00');
        $safety_section = $obj->getJsonFromAnArr($safety_section_arr);

        $sectionData = json_decode($check_file[0]['lCalltobookSection'], true);
        $call_section_arr['title'] = isset($_POST['call_section_title']) ? $_POST['call_section_title'] : '';
        $call_section_arr['desc'] = isset($_POST['call_section_desc']) ? $_POST['call_section_desc'] : '';
        $call_section_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $call_section = $obj->getJsonFromAnArr($call_section_arr);

       /* $sectionData = json_decode($check_file[0]['lRegisterSection'], true);
        $register_section_arr['main_title'] = isset($_POST['register_section_main_title']) ? $_POST['register_section_main_title'] : '';
        $register_section_arr['main_desc'] = isset($_POST['register_section_main_desc']) ? $_POST['register_section_main_desc'] : '';
        $register_section_arr['title_first'] = isset($_POST['register_section_title_first']) ? $_POST['register_section_title_first'] : '';
        $register_section_arr['desc_first'] = isset($_POST['register_section_desc_first']) ? $_POST['register_section_desc_first'] : '';
        $register_section_arr['title_sec'] = isset($_POST['register_section_title_sec']) ? $_POST['register_section_title_sec'] : '';
        $register_section_arr['desc_sec'] = isset($_POST['register_section_desc_sec']) ? $_POST['register_section_desc_sec'] : '';
        $register_section = $obj->getJsonFromAnArr($register_section_arr);*/



        $sectionData = json_decode($check_file[0]['lRegisterSection'], true);
        $register_section_arr['main_title'] = isset($_POST['register_section_main_title']) ? $_POST['register_section_main_title'] : '';
        $register_section_arr['main_subtitle'] = isset($_POST['register_section_main_subtitle']) ? $_POST['register_section_main_subtitle'] : '';
        $register_section_arr['main_desc'] = isset($_POST['register_section_main_desc']) ? $_POST['register_section_main_desc'] : '';


        $register_section_arr['title_first'] = isset($_POST['register_section_title_first']) ? $_POST['register_section_title_first'] : '';
        $register_section_arr['title_sec'] = isset($_POST['register_section_title_sec']) ? $_POST['register_section_title_sec'] : '';
        $register_section_arr['title_third'] = isset($_POST['register_section_title_third']) ? $_POST['register_section_title_third'] : '';
        $register_section_arr['title_fourth'] = isset($_POST['register_section_title_fourth']) ? $_POST['register_section_title_fourth'] : '';
        $register_section_arr['title_fifth'] = isset($_POST['register_section_title_fifth']) ? $_POST['register_section_title_fifth'] : '';

        $register_section_arr['desc_first'] = isset($_POST['register_section_desc_first']) ? $_POST['register_section_desc_first'] : '';
        $register_section_arr['desc_sec'] = isset($_POST['register_section_desc_sec']) ? $_POST['register_section_desc_sec'] : '';
        $register_section_arr['desc_third'] = isset($_POST['register_section_desc_third']) ? $_POST['register_section_desc_third'] : '';
        $register_section_arr['desc_fourth'] = isset($_POST['register_section_desc_fourth']) ? $_POST['register_section_desc_fourth'] : '';
        $register_section_arr['desc_fifth'] = isset($_POST['register_section_desc_fifth']) ? $_POST['register_section_desc_fifth'] : '';


        $register_section_arr['img_first'] = isset($sectionData['img_first']) ? $sectionData['img_first'] : '';
        $register_section_arr['img_sec'] = isset($sectionData['img_sec']) ? $sectionData['img_sec'] : '';
        $register_section_arr['img_third'] = isset($sectionData['img_third']) ? $sectionData['img_third'] : '';
        $register_section_arr['img_fourth'] = isset($sectionData['img_fourth']) ? $sectionData['img_fourth'] : '';
        $register_section_arr['img_fifth'] = isset($sectionData['img_fifth']) ? $sectionData['img_fifth'] : '';

        $register_section = getJsonFromAnArr($register_section_arr);

        $sectionData = json_decode($check_file[0]['lMainServiceSection'], true);
        $lMainServiceSection_arr['title'] = isset($_POST['lMainServiceSection_title']) ? $_POST['lMainServiceSection_title'] : '';
        $lMainServiceSection_arr['desc'] = isset($_POST['lMainServiceSection_desc']) ? $_POST['lMainServiceSection_desc'] : '';
        $lMainServiceSection_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $lMainServiceSection_arr = !(empty($sectionData)) ? array_merge($sectionData, $lMainServiceSection_arr) : $lMainServiceSection_arr;
        /* For How it works Added By PJ 25 Sep 2019 */
        for ($i = 1; $i <= 4; $i++) {
            $lMainServiceSection_arr['ms_title' . $i] = isset($_POST['lMainServiceSection_ms_title' . $i]) ? $_POST['lMainServiceSection_ms_title' . $i] : '';
            $lMainServiceSection_arr['ms_desc' . $i] = isset($_POST['lMainServiceSection_ms_desc' . $i]) ? $_POST['lMainServiceSection_ms_desc' . $i] : '';
        }
        $lMainServiceSection = $obj->getJsonFromAnArr($lMainServiceSection_arr);

        $sectionData = json_decode($check_file[0]['lUseSeviceSection'], true);
        $lUseSeviceSection_arr['title'] = isset($_POST['lUseSeviceSection_title']) ? $_POST['lUseSeviceSection_title'] : '';
        $lUseSeviceSection_arr['img'] = isset($sectionData['img']) ? $sectionData['img'] : '';
        $lUseSeviceSection_arr = !(empty($sectionData)) ? array_merge($sectionData, $lUseSeviceSection_arr) : $lUseSeviceSection_arr;
        /* For How it works Added By PJ 25 Sep 2019 */
        for ($i = 1; $i <= 2; $i++) {
            $lUseSeviceSection_arr['ms_title' . $i] = isset($_POST['lUseSeviceSection_ms_title' . $i]) ? $_POST['lUseSeviceSection_ms_title' . $i] : '';
            $lUseSeviceSection_arr['ms_desc' . $i] = isset($_POST['lUseSeviceSection_ms_desc' . $i]) ? $_POST['lUseSeviceSection_ms_desc' . $i] : '';
        }
        $lUseSeviceSection = $obj->getJsonFromAnArr($lUseSeviceSection_arr);
    }
}
if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        //header("Location:home_action.php?success=2");
        header("Location:home_content_ridecx.php?id=" . $id . "&success=2");
        exit;
    }
    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        //$where = " WHERE `vCode` = '" . $vCode . "'";
        $where = " `vCode` = '" . $vCode . "'";
    }
    //$call_section = $obj->SqlEscapeString($call_section);
    /*$query = $q . " `" . $tbl_name . "` SET
    `lGeneralBannerSection` = '" . $general_section . "',
    `lHowitworkSection` = '" . $how_it_work_section . "',
    `lTravelSection` = '" . $travel_section . "',
    `lPoolSection` = '" . $pool_section . "',
    `lRegisterSection` = '" . $register_section . "',
	`lSecuresafeSection` = '" . $safety_section . "',
    `lMainServiceSection` = '" . $lMainServiceSection . "',
    `lUseSeviceSection` = '" . $lUseSeviceSection . "',
	`lCalltobookSection` = '" . $call_section . "'" . $where;
    $obj->sql_query($query);*/

    $Update['lGeneralBannerSection'] = $general_section;
    $Update['lHowitworkSection'] = $how_it_work_section;
    $Update['lTravelSection'] = $travel_section;
    $Update['lPoolSection'] = $pool_section;
    $Update['lRegisterSection'] = $register_section;
    $Update['lSecuresafeSection'] = $safety_section;
    $Update['lMainServiceSection'] = $lMainServiceSection;
    $Update['lUseSeviceSection'] = $lUseSeviceSection;
    $Update['lCalltobookSection'] = $call_section;
    $id = $obj->MySQLQueryPerform($tbl_name, $Update, 'update', $where);
    //$id = ($id != '') ? $id : $obj->GetInsertId();
    //header("Location:make_action.php?id=".$id.'&success=1');
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



    $sql = "SELECT hc.*,lm.vTitle FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $iLanguageId . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCode = $value['vCode'];
            $title = $value['vTitle'];
            $eStatus = $value['eStatus'];
            $general_section = json_decode($value['lGeneralBannerSection'], true);
            $how_it_work_section = (array)json_decode($value['lHowitworkSection']);
            $safety_section = json_decode($value['lSecuresafeSection'], true);
            $call_section = json_decode($value['lCalltobookSection'], true);
            //$calculate_section = json_decode($value['lCalculateSection'],true);
            $travel_section = json_decode($value['lTravelSection'], true);
            $pool_section = json_decode($value['lPoolSection'], true);
            $register_section = json_decode($value['lRegisterSection'], true);

            $lMainServiceSection = json_decode($value['lMainServiceSection'], true);
            $lUseSeviceSection = json_decode($value['lUseSeviceSection'], true);
        }
    }


    $registerSection = [];
    $registerSectionCount = ['first','sec','third','fourth','fifth'];

    $i = 1;
    foreach ($registerSectionCount as $key => $value)
    {
        $val = [];
        $val['name'] = 'Image Title ' . $i;
        $val['desc'] = 'Image Desc Title ' . $i;
        $val['image'] = 'Image ' . $i;
        $val['prefix'] = $value;

        $val['name_value'] = $register_section['title_'.$value];
        $val['desc_value'] = $register_section['desc_'.$value];
        $val['image_value'] = $register_section['img_'.$value];


        $val['field_name'] = 'register_section_title_'.$value;
        $val['field_desc'] = 'register_section_desc_'.$value;
        $val['field_image'] = 'register_section_img_'.$value;



        $registerSection[$key] = $val;
        $i++;
    }
}

if (isset($_POST['submit']) && $_POST['submit'] == 'submit') {
    $required = 'required';
} else if (isset($_POST['catlogo']) && $_POST['catlogo'] == 'catlogo') {
    $required = '';
}

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$script = 'homecontent';
?>
<!DOCTYPE html>
<!--[if IE 8]>
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
            height: 150px;
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
                        </div>
                        <br/>
                    <? } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <? } ?>
                    <form method="post" name="_home_content_form" id="_home_content_form" action=""
                          enctype='multipart/form-data'>
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="vCode" value="<?= $vCode; ?>">
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="homepage_content.php"/>
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
                                        <textarea class="form-control" rows="3" name="general_section_desc" id="general_section_desc" placeholder="Description"><?= $general_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>First Image(Background image)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($general_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $general_section['img']; ?>"
                                                 class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="general_section_img"
                                               id="general_section_img" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 609px * 1903px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>How It work section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="how_it_work_section_title"
                                               id="how_it_work_section_title"
                                               value="<?= $how_it_work_section['title']; ?>" placeholder="Title"
                                               required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="how_it_work_section_desc" id="how_it_work_section_desc" placeholder="Description"><?= $how_it_work_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <!-- How It Works Blocks -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>How It Works Blocks</h3>
                                        <p>(Note : Title and Description are required for show this blocks on page..)
                                        </p>
                                        <hr/>
                                    </div>
                                    <?php for ($i = 1; $i <= 4; $i++) { ?>
                                        <div class="col-lg-3">
                                            <!-- Title -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Title <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <input type="text" class="form-control"
                                                           name="how_it_work_section_hiw_title<?php echo $i; ?>"
                                                           id="how_it_work_section_hiw_title<?php echo $i; ?>"
                                                           value="<?= $how_it_work_section['hiw_title' . $i]; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <!-- Description  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Description <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" name="how_it_work_section_hiw_desc<?php echo $i; ?>" id="how_it_work_section_hiw_desc<?php echo $i; ?>" value="<?= $how_it_work_section['hiw_desc' . $i]; ?>" placeholder="Description" rows="5"><?= $how_it_work_section['hiw_desc' . $i]; ?></textarea>
                                                </div>
                                            </div>
                                            <!-- Image  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Image <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($how_it_work_section['hiw_img' . $i] != '') { ?>
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $how_it_work_section['hiw_img' . $i]; ?>"
                                                             class="innerbg_image"/ style="max-height:100px;">
                                                    <? } ?>
                                                    <input type="file" class="form-control FilUploader"
                                                           name="how_it_work_section_hiw_img<?php echo $i; ?>"
                                                           id="how_it_work_section_hiw_img<?php echo $i; ?>"
                                                           accept=".png,.jpg,.jpeg,.gif,.svg">
                                                    <br/>
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 50px * 50px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <!-- How It Works Blocks End -->
                            </div>
                        </div>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Pool & Rental Section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="travel_section_title"
                                               id="travel_section_title" value="<?= $travel_section['title']; ?>"
                                               placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="travel_section_desc" id="travel_section_desc" placeholder="Description"><?= $travel_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($travel_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $travel_section['img']; ?>"
                                                 class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="travel_section_img"
                                               id="travel_section_img" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 564px * 570px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Travel Section</h3>
                                    </div>
                                </div>
                                <!--  <div class="row">
                                           <div class="col-lg-12">
                                               <label>Menu Title</label>
                                           </div>
                                           <div class="col-lg-6">
                                               <input type="text" class="form-control" name="pool_section_menu_title"  id="pool_section_menu_title" value="<?= $pool_section['menu_title']; ?>" placeholder="Menu Title">
                                           </div>
                                       </div> -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="pool_section_title"
                                               id="pool_section_title" value="<?= $pool_section['title']; ?>"
                                               placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="pool_section_desc" id="pool_section_desc" placeholder="Description"><?= $pool_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($pool_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $pool_section['img']; ?>"
                                                 class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="pool_section_img"
                                               id="pool_section_img" accept=".png,.jpg,.jpeg,.gif,.svg">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 860px * 445px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Main Services section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="lMainServiceSection_title"
                                               id="lMainServiceSection_title"
                                               value="<?= $lMainServiceSection['title']; ?>" placeholder="Title"
                                               required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="lMainServiceSection_desc" id="lMainServiceSection_desc" placeholder="Description"><?= $lMainServiceSection['desc']; ?></textarea>
                                    </div>
                                </div>
                                <!-- Main Services Blocks -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Services Blocks</h3>
                                        <p>(Note : Title and Description are required for show this blocks on page..)
                                        </p>
                                        <hr/>
                                    </div>
                                    <?php for ($i = 1; $i <= 4; $i++) { ?>
                                        <div class="col-lg-3">
                                            <!-- Title -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Title <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <input type="text" class="form-control" name="lMainServiceSection_ms_title<?php echo $i; ?>"
                                                           id="lMainServiceSection_ms_title<?php echo $i; ?>"
                                                           value="<?= $lMainServiceSection['ms_title' . $i]; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <!-- Description  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Description <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" name="lMainServiceSection_ms_desc<?php echo $i; ?>" id="lMainServiceSection_ms_desc<?php echo $i; ?>" value="<?= $lMainServiceSection['ms_desc' . $i]; ?>" placeholder="Description" rows="5"><?= $lMainServiceSection['ms_desc' . $i]; ?></textarea>
                                                </div>
                                            </div>
                                            <!-- Image  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Image <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($lMainServiceSection['ms_img' . $i] != '') { ?>
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $lMainServiceSection['ms_img' . $i]; ?>"
                                                             class="innerbg_image"/ style="max-height:100px;">
                                                    <? } ?>
                                                    <input type="file" class="form-control FilUploader"
                                                           name="lMainServiceSection_ms_img<?php echo $i; ?>"
                                                           id="lMainServiceSection_ms_img<?php echo $i; ?>"
                                                           accept=".png,.jpg,.jpeg,.gif,.svg">
                                                    <br/>
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 150px * 80px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <!-- Main Services Blocks End -->
                            </div>
                        </div>

                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Why Use section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="lUseSeviceSection_title"
                                               id="lUseSeviceSection_title"
                                               value="<?= $lUseSeviceSection['title']; ?>" placeholder="Title"
                                               required>
                                    </div>
                                </div>
                                 <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($lUseSeviceSection['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $lUseSeviceSection['img']; ?>"
                                                 class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader"
                                               name="lUseSeviceSection_img" id="lUseSeviceSection_img"
                                               accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                                <!-- Use Blocks -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Services Blocks</h3>
                                        <p>(Note : Title and Description are required for show this blocks on page..)
                                        </p>
                                        <hr/>
                                    </div>
                                    <?php for ($i = 1; $i <= 2; $i++) { ?>
                                        <div class="col-lg-6">
                                            <!-- Title -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Title <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <input type="text" class="form-control" name="lUseSeviceSection_ms_title<?php echo $i; ?>"
                                                           id="lUseSeviceSection_ms_title<?php echo $i; ?>"
                                                           value="<?= $lUseSeviceSection['ms_title' . $i]; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <!-- Description  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Description <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" name="lUseSeviceSection_ms_desc<?php echo $i; ?>" id="lUseSeviceSection_ms_desc<?php echo $i; ?>" value="<?= $lUseSeviceSection['ms_desc' . $i]; ?>" placeholder="Description" rows="5"><?= $lUseSeviceSection['ms_desc' . $i]; ?></textarea>
                                                </div>
                                            </div>
                                            <!-- Image  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Image <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($lUseSeviceSection['ms_img' . $i] != '') { ?>
                                                        <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $lUseSeviceSection['ms_img' . $i]; ?>" / >
                                                    <? } ?>
                                                    <input type="file" class="form-control FilUploader"
                                                           name="lUseSeviceSection_ms_img<?php echo $i; ?>"
                                                           id="lUseSeviceSection_ms_img<?php echo $i; ?>"
                                                           accept=".png,.jpg,.jpeg,.gif,.svg">
                                                    <br/>
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 60px * 60px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <!--Use Blocks End -->
                            </div>
                        </div>

                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Safety section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title <span class="red"> *</span> </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="safety_section_main_title"
                                               id="safety_section_main_title"
                                               value="<?= $safety_section['main_title']; ?>" placeholder="Title"
                                               required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="safety_section_main_desc" id="safety_section_main_desc" placeholder="Description"><?= $safety_section['main_desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($safety_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=300&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safety_section['img']; ?>"
                                                 class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader"
                                               name="safety_section_img" id="safety_section_img"
                                               accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 450px * 520px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>
                                <!-- Use Blocks -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Services Blocks</h3>
                                        <p>(Note : Title and Description are required for show this blocks on page..)
                                        </p>
                                        <hr/>
                                    </div>
                                    <?php for ($i = 1; $i <= 2; $i++) { ?>
                                        <div class="col-lg-6">
                                            <!-- Title -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Title <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <input type="text" class="form-control" name="safety_section_ms_title<?php echo $i; ?>"
                                                           id="safety_section_ms_title<?php echo $i; ?>"
                                                           value="<?= $safety_section['ms_title' . $i]; ?>"
                                                           placeholder="Title">
                                                </div>
                                            </div>
                                            <!-- Description  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Description <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <textarea class="form-control" name="safety_section_ms_desc<?php echo $i; ?>" id="safety_section_ms_desc<?php echo $i; ?>" value="<?= $safety_section['ms_desc' . $i]; ?>" placeholder="Description" rows="5"><?= $safety_section['ms_desc' . $i]; ?></textarea>
                                                </div>
                                            </div>
                                            <!-- Image  -->
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Block Image <?php echo $i; ?></label>
                                                </div>
                                                <div class="col-lg-11">
                                                    <? if ($safety_section['ms_img' . $i] != '') { ?>
                                                        <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $safety_section['ms_img' . $i]; ?>" / >
                                                    <? } ?>
                                                    <input type="file" class="form-control FilUploader"
                                                           name="safety_section_ms_img<?php echo $i; ?>"
                                                           id="safety_section_ms_img<?php echo $i; ?>"
                                                           accept=".png,.jpg,.jpeg,.gif,.svg">
                                                    <br/>
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 60px * 60px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <!--Use Blocks End -->
                            </div>
                        </div>

                        <div class="body-div innersection">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h3>Call Section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="call_section_title"
                                               id="call_section_title" value="<?= $call_section['title']; ?>"
                                               placeholder="Title" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description</label>
                                        <h5>[Note: Please use #SUPPORT_PHONE# predefined tags to display the support
                                            phone value. Please go to Settings >> General section to change the values
                                            of above predefined tags.]
                                        </h5>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control ckeditor" rows="3" name="call_section_desc" id="call_section_desc" placeholder="Description"><?= $call_section['desc']; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Image</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($call_section['img'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $call_section['img']; ?>"
                                                 class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="call_section_img"
                                               id="call_section_img" accept=".png,.jpg,.jpeg,.gif">
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
                                        <h3>Register Section</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Main Title
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="register_section_main_title"
                                               id="register_section_main_title"
                                               value="<?= $register_section['main_title']; ?>" placeholder="Title"
                                               required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Main Description
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="register_section_main_desc" id="register_section_main_desc" placeholder="Description"><?= $register_section['main_desc']; ?></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description 2
                                            <span class="red"> *</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="3" name="register_section_desc_sec" id="register_section_desc_sec" placeholder="Description"><?= $register_section['desc_sec']; ?></textarea>
                                    </div>
                                </div>


                                <!----------------------------------->

                                <?php foreach($registerSection as $rs){ ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $rs['name'] ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="<?php echo $rs['field_name'] ?>" id="<?php echo $rs['field_name'] ?>" value="<?php echo $rs['name_value'] ?>" placeholder="Title" required>
                                    </div>
                                </div>

                                    <!--<div class="row">
                                        <div class="col-lg-12">
                                            <label><?php /*echo $rs['desc'] */?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="<?php /*echo $rs['field_desc'] */?>" id="<?php /*echo $rs['field_desc'] */?>" value="<?php /*echo $rs['desc_value'] */?>" placeholder="Title" required>
                                        </div>
                                    </div>-->


                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $rs['image']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <? if ($rs['image_value'] != '') { ?>
                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=200&src=' . $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $rs['image_value']; ?>" class="innerbg_image"/>
                                        <? } ?>
                                        <input type="file" class="form-control FilUploader" name="<?php echo $rs['field_image'] ?>" id="<?php echo $rs['field_image'] ?>" accept=".png,.jpg,.jpeg,.gif">
                                        <br/>
                                        <span class="notes">[Note: For Better Resolution Upload only image size of 740px * 740px. <br> <?= IMAGE_INSTRUCTION_NOTES ?> ]</span>
                                    </div>
                                </div>

                                <?php } ?>
                                <!----------------------------------->



                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class=" btn btn-default" name="submit" id="submit"
                                       value="<?= $action; ?> Home Content">
                                <a href="home_content_ridecx.php" class="btn btn-default back_link">Cancel</a>
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
        window.location.href = "home_content_ridecxv2_action.php?id=<?php echo $id ?>";
        <?php } ?>
        if ($("#previousLink").val() == "") { //alert('pre1');
            referrer = document.referrer;
            // alert(referrer);
        } else { //alert('pre2');
            referrer = $("#previousLink").val();
        }

        if (referrer == "") {
            referrer = "home_content_ridecx.php";
        } else { //alert('hi');
            //$("#backlink").val(referrer);
            referrer = "home_content_ridecx.php";
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
