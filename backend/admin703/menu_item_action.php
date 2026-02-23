<?php
include_once('../common.php');
if ($MODULES_OBJ->isEnableMultiOptionsToppings()) {
    include_once('menu_item_multioptions_action.php');
    exit;
}
$tbl_name = 'menu_items';
$tbl_name1 = 'menuitem_options';
$script = 'MenuItems';
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
$menuiParentId = 0;
function check_diff($arr1, $arr2)
{
    $check = (is_array($arr1) && scount($arr1) > 0) ? true : false;
    $result = ($check) ? ((is_array($arr2) && scount($arr2) > 0) ? $arr2 : array()) : array();
    if ($check) {
        foreach ($arr1 as $key => $value) {
            if (isset($result[$key])) {
                $result[$key] = array_diff($value, $result[$key]);
            }
            else {
                $result[$key] = $value;
            }
        }
    }
    return $result;
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : $_SESSION['success'];
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iFoodMenuId = isset($_POST['iFoodMenuId']) ? $_POST['iFoodMenuId'] : '0';
$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
$vSKU = isset($_POST['vSKU']) ? $_POST['vSKU'] : '';
//$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';
$eFoodType = isset($_POST['eFoodType']) ? $_POST['eFoodType'] : '';
$vHighlightName = isset($_POST['vHighlightName']) ? $_POST['vHighlightName'] : '';
$fOfferAmt = isset($_POST['fOfferAmt']) ? $_POST['fOfferAmt'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'on';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eAvailable_check = isset($_POST['eAvailable']) ? $_POST['eAvailable'] : 'off';
$eAvailable = ($eAvailable_check == 'on') ? 'Yes' : 'No';
$eRecommended_check = isset($_POST['eRecommended']) ? $_POST['eRecommended'] : 'off';
$eRecommended = ($eRecommended_check == 'on') ? 'Yes' : 'No';
$prescription_required_chk = isset($_POST['prescription_required']) ? $_POST['prescription_required'] : 'off';
$prescription_required = ($prescription_required_chk == 'on') ? 'Yes' : 'No';
/* $eRecommended_check = isset($_POST['eRecommended'])?$_POST['eRecommended']:'on';
      $eRecommended = ($eRecommended_check == 'on')?'Yes':'No';
    
      $eAvailable_check = isset($_POST['eAvailable'])?$_POST['eAvailable']:'on';
      $eAvailable = ($eAvailable_check == 'on')?'Yes':'No'; */
$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$vImageTest = isset($_POST['vImageTest']) ? $_POST['vImageTest'] : '';
$BaseOptions = isset($_POST['BaseOptions']) ? $_POST['BaseOptions'] : '';
$OptPrice = isset($_POST['OptPrice']) ? $_POST['OptPrice'] : '';
$optType = isset($_POST['optType']) ? $_POST['optType'] : '';
$OptionId = isset($_POST['OptionId']) ? $_POST['OptionId'] : '';
$eDefault = isset($_POST['eDefault']) ? $_POST['eDefault'] : '';
$options_lang_all = isset($_POST['options_lang_all']) ? $_POST['options_lang_all'] : '';
$vMenuItemOptionImage = isset($_POST['vMenuItemOptionImage']) ? $_POST['vMenuItemOptionImage'] : '';
// echo "<pre>"; print_r($_POST); exit;
foreach ($BaseOptions as $key => $value) {
    if (trim($value) != "") {
        $base_array[$key]['vOptionName'] = $value;
        $base_array[$key]['fPrice'] = $OptPrice[$key];
        $base_array[$key]['eOptionType'] = $optType[$key];
        $base_array[$key]['iOptionId'] = $OptionId[$key];
        $base_array[$key]['eDefault'] = $eDefault[$key];
        $base_array[$key]['eStatus'] = 'Active';
        $base_array[$key]['tOptionNameLang'] = trim(addslashes(stripslashes($options_lang_all[$key])), '\"');
        $base_array[$key]['vImage'] = !empty($vMenuItemOptionImage[$key]) ? $vMenuItemOptionImage[$key] : "";
    }
}
$AddonOptions = isset($_POST['AddonOptions']) ? $_POST['AddonOptions'] : '';
$AddonPrice = isset($_POST['AddonPrice']) ? $_POST['AddonPrice'] : '';
$optTypeaddon = isset($_POST['optTypeaddon']) ? $_POST['optTypeaddon'] : '';
$addonId = isset($_POST['addonId']) ? $_POST['addonId'] : '';
$addons_lang_all = isset($_POST['addons_lang_all']) ? $_POST['addons_lang_all'] : '';
$vMenuItemAddonImage = isset($_POST['vMenuItemAddonImage']) ? $_POST['vMenuItemAddonImage'] : '';
foreach ($AddonOptions as $key => $value) {
    if (trim($value) != "") {
        $addon_array[$key]['vOptionName'] = $value;
        $addon_array[$key]['fPrice'] = $AddonPrice[$key];
        $addon_array[$key]['eOptionType'] = $optTypeaddon[$key];
        $addon_array[$key]['iOptionId'] = $addonId[$key];
        $addon_array[$key]['eStatus'] = 'Active';
        $addon_array[$key]['tOptionNameLang'] = trim(addslashes(stripslashes($addons_lang_all[$key])), '\"');
        $addon_array[$key]['vImage'] = !empty($vMenuItemAddonImage[$key]) ? $vMenuItemAddonImage[$key] : "";
    }
}
$vTitle_store = array();
$vItemDesc_store = array();
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
        array_push($vItemDesc_store, $vValue_desc);
        $$vValue_desc = isset($_POST[$vValue_desc]) ? $_POST[$vValue_desc] : '';
    }
}
if (isset($_POST['btnsubmit'])) {
    // echo "<pre>";print_r($_POST);die;
    if ($action == "Add" && !$userObj->hasPermission('create-item')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Item.';
        header("Location:menu_item.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-item')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Item.';
        header("Location:menu_item.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:menu_item_action.php?id=" . $id . "&success=2");
        exit;
    }
    $img_path = $tconfig["tsite_upload_images_menu_item_path"];
    $temp_gallery = $img_path . '/';
    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    $vImgName = "";
    if ($image_name != "") {
        $oldFilePath = $temp_gallery . $oldImage;
        if ($oldImage != '' && file_exists($oldFilePath)) {
            unlink($img_path . '/' . $oldImage);
            /* unlink($img_path . '/1_' . $oldImage);
                  unlink($img_path . '/2_' . $oldImage);
                  unlink($img_path . '/3_' . $oldImage); */
        }
        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        $tsite_upload_image_file_extensions = explode(",", $tconfig["tsite_upload_image_file_extensions"]);
        if (!in_array($ext, $tsite_upload_image_file_extensions)) {
            $flag_error = 1;
            $var_msg = $tconfig["tsite_upload_image_file_extensions_validation_msg"];
        }
        if ($flag_error == 1) {
            getPostForm($_POST, $var_msg, "menu_item_action.php?success=0&var_msg=" . $var_msg);
            exit;
        }
        else {
            $Photo_Gallery_folder = $img_path . '/';
            //$img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
            $img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            $oldImage = $img1;
        }
    }
    if ($id != "") {
        $obj->MySQLSelect("DELETE FROM menuitem_options WHERE vOptionName = ''");
        $sql = "SELECT iDisplayOrder FROM `menu_items` where iMenuItemId = '$id'";
        $displayOld = $obj->MySQLSelect($sql);
        $oldDisplayOrder = $displayOld[0]['iDisplayOrder'];
        if ($oldDisplayOrder > $iDisplayOrder) {
            $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' AND iDisplayOrder < '$oldDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder + 1;
                for ($i = 0; $i < scount($db_orders); $i++) {
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                    $obj->sql_query($query);
                    echo $j;
                    $j++;
                }
            }
        }
        else if ($oldDisplayOrder < $iDisplayOrder) {
            $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder > '$oldDisplayOrder' AND iDisplayOrder <= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
            $db_orders = $obj->MySQLSelect($sql);
            if (!empty($db_orders)) {
                $j = $iDisplayOrder;
                for ($i = 0; $i < scount($db_orders); $i++) {
                    $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                    $obj->sql_query($query);
                    echo $j;
                    $j++;
                }
            }
        }
    }
    else {
        $sql = "SELECT * FROM `menu_items` where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
        $db_orders = $obj->MySQLSelect($sql);
        if (!empty($db_orders)) {
            $j = $iDisplayOrder + 1;
            for ($i = 0; $i < scount($db_orders); $i++) {
                $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '" . $db_orders[$i]['iMenuItemId'] . "'";
                $obj->sql_query($query);
                $j++;
            }
        }
    }
    for ($i = 0; $i < scount($vTitle_store); $i++) {
        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
        $strItemDesc = $_POST[$vItemDesc_store[$i]];
        $strItemTitle = $_POST[$vTitle_store[$i]];
    
        //$editItemDesc .= '`' . $vValue_desc . "`='" . $strItemDesc . "','`" . $vValue . "`='" . $strItemTitle . "',";
        $editItemDesc .= "`" . $vValue_desc . "`='" . $strItemDesc . "',`" . $vValue . "`='" . $strItemTitle . "',";
    }
    if ($editItemDesc != "") {
        $editItemDesc = trim($editItemDesc, ",");
    }
    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iMenuItemId` = '" . $id . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
                `iFoodMenuId` = '" . $iFoodMenuId . "',
                `vImage` = '" . $oldImage . "',
                `iDisplayOrder` = '" . $iDisplayOrder . "',
                `fPrice` = '" . $fPrice . "',
                `fOfferAmt` = '" . $fOfferAmt . "',
                `eFoodType` = '" . $eFoodType . "',
                `vHighlightName` = '" . $vHighlightName . "',
                `eAvailable` = '" . $eAvailable . "',
                `eRecommended`= '" . $eRecommended . "',
                `prescription_required` = '" . $prescription_required . "',
                `vSKU` = '" . $vSKU . "', 
                " . $editItemDesc . $where;
    // `vSKU` = '" . trim($vSKU) . "',
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    if (!empty($id)) {
        $uploadedImages = $_FILES['vMultiImage']['name'];
        if ($ENABLE_ITEM_MULTIPLE_IMAGE_VIDEO_UPLOAD == 'Yes' && !empty($uploadedImages[0])) {
            $upload_img = $MENU_ITEM_MEDIA_OBJ->uploadImageVideo($_FILES, $tconfig["tsite_upload_images_menu_item_path"]);
            foreach ($upload_img as $img) {
                $Data_update_option['vImage'] = $img;
                $Data_update_option['iMenuItemId'] = $id;
                if (isset($img) && !empty($img)) {
                    $menu_item_mediaid = $obj->MySQLQueryPerform('menu_item_media', $Data_update_option, 'insert');
                }
            }
        }
        $q = "SELECT * FROM menuitem_options WHERE iMenuItemId ='" . $id . "' AND eOptionType='Options'";
        $baseOptionOldData = $obj->MySQLSelect($q);
        if (scount($baseOptionOldData) > 0) {
            $BaseOptionsDiffres = check_diff($baseOptionOldData, $base_array);
            foreach ($BaseOptionsDiffres as $k => $BaseOptionsVal) {
                if (!empty($BaseOptionsVal['iOptionId'])) {
                    $newoptioidsArr[$k]['iOptionId'] = $BaseOptionsVal['iOptionId'];
                    $newoptioidsArr[$k]['iMenuItemId'] = $BaseOptionsVal['iMenuItemId'];
                }
            }
            // @todo : upload on lib and delete continue
            if (scount($newoptioidsArr) > 0) {
                foreach ($newoptioidsArr as $ky => $optionidArr) {
                    $q = "UPDATE ";
                    $where = " WHERE `iOptionId` = '" . $optionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $optionidArr['iMenuItemId'] . "'";
                    $baseupdatequery = $q . " `" . $tbl_name1 . "` SET
                            `eStatus` = 'Inactive'" . $where;
                    $obj->sql_query($baseupdatequery);
                }
            }
            $dst_dir = $tconfig["tsite_upload_images_menu_item_options_path"];
            if (scount($base_array) > 0) {
                foreach ($base_array as $key => $value) {
                    $Data_update_option = array();
                    if ($value['iOptionId'] == '') {
                        $Data_update_option['iMenuItemId'] = $id;
                        $Data_update_option['vOptionName'] = $value['vOptionName'];
                        $Data_update_option['fPrice'] = $value['fPrice'];
                        $Data_update_option['eStatus'] = $value['eStatus'];
                        $Data_update_option['eOptionType'] = $value['eOptionType'];
                        $Data_update_option['eDefault'] = $value['eDefault'];
                        $Data_update_option['tOptionNameLang'] = $value['tOptionNameLang'];
                        if (!empty($value['vImage'])) {
                            $Data_update_option['vImage'] = $value['vImage'];
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if (!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $id11 = $obj->MySQLQueryPerform($tbl_name1, $Data_update_option, 'insert');
                    }
                    else {
                        $where = " `iOptionId` = '" . $value['iOptionId'] . "'";
                        $Data_update_option['iMenuItemId'] = $id;
                        $Data_update_option['vOptionName'] = $value['vOptionName'];
                        $Data_update_option['fPrice'] = $value['fPrice'];
                        $Data_update_option['eStatus'] = $value['eStatus'];
                        $Data_update_option['eOptionType'] = $value['eOptionType'];
                        $Data_update_option['eDefault'] = $value['eDefault'];
                        $Data_update_option['tOptionNameLang'] = $value['tOptionNameLang'];
                        if (!empty($value['vImage'])) {
                            $Data_update_option['vImage'] = $value['vImage'];
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if (!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $id22 = $obj->MySQLQueryPerform($tbl_name1, $Data_update_option, 'update', $where);
                    }
                }
            }
        }
        else {
            if (scount($base_array) > 0) {
                foreach ($base_array as $key => $value) {
                    $Data_update_option = array();
                    $Data_update_option['iMenuItemId'] = $id;
                    $Data_update_option['vOptionName'] = $value['vOptionName'];
                    $Data_update_option['fPrice'] = $value['fPrice'];
                    $Data_update_option['eStatus'] = $value['eStatus'];
                    $Data_update_option['eOptionType'] = $value['eOptionType'];
                    $Data_update_option['eDefault'] = $value['eDefault'];
                    $Data_update_option['tOptionNameLang'] = $value['tOptionNameLang'];
                    if (!empty($value['vImage'])) {
                        $Data_update_option['vImage'] = $value['vImage'];
                        $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                        $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                        if (!is_dir($dst_dir)) {
                            mkdir($dst_dir, 0777);
                            chmod($dst_dir, 0777);
                        }
                        rename($src_path, $dst_path);
                    }
                    $id11 = $obj->MySQLQueryPerform($tbl_name1, $Data_update_option, 'insert');
                }
            }
        }
    }
    if (!empty($id)) {
        $q = "SELECT * FROM menuitem_options WHERE iMenuItemId ='" . $id . "' AND eOptionType='Addon'";
        $addonOptionOldData = $obj->MySQLSelect($q);
        if (scount($addonOptionOldData) > 0) {
            $addonOptionDiffres = check_diff($addonOptionOldData, $addon_array);
            foreach ($addonOptionDiffres as $j => $AddonOptionsVal) {
                if (!empty($AddonOptionsVal['iOptionId'])) {
                    $newoptioidsAddonArr[$j]['iOptionId'] = $AddonOptionsVal['iOptionId'];
                    $newoptioidsAddonArr[$j]['iMenuItemId'] = $AddonOptionsVal['iMenuItemId'];
                }
            }
            if (scount($newoptioidsAddonArr) > 0) {
                foreach ($newoptioidsAddonArr as $ky => $addonoptionidArr) {
                    $q = "UPDATE ";
                    $where = " WHERE `iOptionId` = '" . $addonoptionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $addonoptionidArr['iMenuItemId'] . "'";
                    $addonupdatequery = $q . " `" . $tbl_name1 . "` SET
                            `eStatus` = 'Inactive'" . $where;
                    $obj->sql_query($addonupdatequery);
                }
            }
            if (scount($addon_array) > 0) {
                foreach ($addon_array as $key => $value) {
                    $Data_update_option = array();
                    if ($value['iOptionId'] == '') {
                        $Data_update_option['iMenuItemId'] = $id;
                        $Data_update_option['vOptionName'] = $value['vOptionName'];
                        $Data_update_option['fPrice'] = $value['fPrice'];
                        $Data_update_option['eStatus'] = $value['eStatus'];
                        $Data_update_option['eOptionType'] = $value['eOptionType'];
                        $Data_update_option['tOptionNameLang'] = $value['tOptionNameLang'];
                        if (!empty($value['vImage'])) {
                            $Data_update_option['vImage'] = $value['vImage'];
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if (!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $id22 = $obj->MySQLQueryPerform($tbl_name1, $Data_update_option, 'insert');
                    }
                    else {
                        $where = " `iOptionId` = '" . $value['iOptionId'] . "'";
                        $Data_update_option['iMenuItemId'] = $id;
                        $Data_update_option['vOptionName'] = $value['vOptionName'];
                        $Data_update_option['fPrice'] = $value['fPrice'];
                        $Data_update_option['eStatus'] = $value['eStatus'];
                        $Data_update_option['eOptionType'] = $value['eOptionType'];
                        $Data_update_option['tOptionNameLang'] = $value['tOptionNameLang'];
                        if (!empty($value['vImage'])) {
                            $Data_update_option['vImage'] = $value['vImage'];
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if (!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $id11 = $obj->MySQLQueryPerform($tbl_name1, $Data_update_option, 'update', $where);
                    }
                }
            }
        }
        else {
            if (scount($addon_array) > 0) {
                foreach ($addon_array as $key => $value) {
                    $Data_update_option = array();
                    $Data_update_option['iMenuItemId'] = $id;
                    $Data_update_option['vOptionName'] = $value['vOptionName'];
                    $Data_update_option['fPrice'] = $value['fPrice'];
                    $Data_update_option['eStatus'] = $value['eStatus'];
                    $Data_update_option['eOptionType'] = $value['eOptionType'];
                    $Data_update_option['tOptionNameLang'] = $value['tOptionNameLang'];
                    if (!empty($value['vImage'])) {
                        $Data_update_option['vImage'] = $value['vImage'];
                        $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                        $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                        if (!is_dir($dst_dir)) {
                            mkdir($dst_dir, 0777);
                            chmod($dst_dir, 0777);
                        }
                        rename($src_path, $dst_path);
                    }
                    $id11 = $obj->MySQLQueryPerform($tbl_name1, $Data_update_option, 'insert');
                }
            }
        }
    }
    //header("Location:menu_item_action.php?id=" . $id . '&success=1');
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }
    else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("Location:" . $backlink);
    exit;
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT mi.*,f.iCompanyId FROM menu_items as mi LEFT JOIN food_menu as f on f.iFoodMenuId=mi.iFoodMenuId WHERE mi.iMenuItemId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $ssql = "";
    if (!$MODULES_OBJ->isEnableMultiOptionsToppings()) {
        $ssql = " AND iOptionsCategoryId = 0";
    }
    $sql1 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Options' AND eStatus = 'Active' $ssql ORDER BY eDefault";
    $db_optionsdata = $obj->MySQLSelect($sql1);
    // echo "<pre>"; print_r($db_optionsdata); exit();    
    $sql2 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Addon' AND eStatus = 'Active' $ssql";
    $db_addonsdata = $obj->MySQLSelect($sql2);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vItemType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
                $$vValue_desc = $value[$vValue_desc];
                $iFoodMenuId = $value['iFoodMenuId'];
                $oldImage = $value['vImage'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $fPrice = $value['fPrice'];
                $eAvailable = $value['eAvailable'];
                $eStatus = $value['eStatus'];
                $eRecommended = $value['eRecommended'];
                $fOfferAmt = $value['fOfferAmt'];
                $iCompanyId = $value['iCompanyId'];
                $eFoodType = $value['eFoodType'];
                $vHighlightName = $value['vHighlightName'];
                $prescription_required = $value['prescription_required'];
                $vSKU = $value['vSKU'];
                $arrLang[$vValue] = $$vValue;
                $arrLang[$vValue_desc] = $$vValue_desc;
            }
        }
    }
}
$qry_cat = "SELECT c.iServiceId FROM `food_menu` AS f LEFT JOIN company AS c ON c.iCompanyId = f.iCompanyId WHERE c.iCompanyId = '" . $iCompanyId . "' and  c.eStatus!='Deleted'";
$db_chk = $obj->MySQLSelect($qry_cat);
$EditServiceIdNew = $db_chk[0]['iServiceId'];
$sql_cat = "SELECT fm.iFoodMenuId,fm.vMenu_EN,c.vCompany,c.iCompanyId FROM food_menu AS fm
                LEFT JOIN `company` AS c ON c.iCompanyId = fm.iCompanyId WHERE fm.eStatus = 'Active'";
$db_menu = $obj->MySQLSelect($sql_cat);
// For Restaurants
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_" . $default_lang . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
    $ssql .= " AND (c.iServiceId IN (" . $enablesevicescategory . ")";
    $enablesevicescategory = str_replace(",", "|", $enablesevicescategory);
    $ssql .= " OR c.iServiceIdMulti REGEXP '(^|,)(" . $enablesevicescategory . ")(,|$)') ";
}
else {
    $ssql = " AND c.iServiceId IN (" . $enablesevicescategory . ")";
}
$sql = "SELECT c.iCompanyId,c.vCompany,c.iServiceId,c.vEmail FROM `food_menu` AS f LEFT JOIN company AS c ON c.iCompanyId = f.iCompanyId WHERE c.eStatus!='Deleted' $ssql GROUP BY f.iCompanyId ORDER BY `vCompany`";
$db_company = $obj->MySQLSelect($sql);
$sql = "SELECT lbl.vLabel,lbl.vCode,lbl.vValue FROM `language_master` as lm LEFT JOIN language_label as lbl ON lbl.vCode = lm.vCode WHERE lm.eStatus='Active' AND lbl.vLabel = 'LBL_REGULAR'";
$db_lbl = $obj->MySQLSelect($sql);
$lbl_regular = array();
foreach ($db_lbl as $lbl_value) {
    $rkey = 'tOptionNameLang_' . $lbl_value['vCode'];
    $lbl_regular = array_merge($lbl_regular, array($rkey => $lbl_value['vValue']));
}
$lbl_regular_txt = $lbl_regular['tOptionNameLang_' . $default_lang];
$lbl_regular_str = preg_replace('/"+/', '"', json_encode($lbl_regular));
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html><!--[if IE 8]>
<html lang="en" class="ie8"><![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"><![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Item <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!--<link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />-->
    <?
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        #options_fields .form-group,
        #addon_fields .form-group {
            margin-bottom: 0;
        }

        #options_fields .form-group:last-child,
        #addon_fields .form-group:last-child {
            padding-bottom: 0
        }

        /*body.modal-open {
                        overflow: hidden;
                    }*/
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <h2><?= $action; ?> Item </h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div><br/>
                    <? } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable ">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <? } elseif ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $_REQUEST['var_msg']; ?>
                        </div><br/>
                    <? } ?>
                    <? if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Record Not Updated .
                        </div><br/>
                    <? } ?>
                    <form name="menuItem_form" id="menuItem_form" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="iMenuItemIdedit" value="<?= $id; ?>"/>
                        <input type="hidden" name="oldImage" value="<?php echo $oldImage; ?>">
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="menu_item.php"/>
                        <? if ($action == 'Edit') { ?>
                            <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId']; ?>"/>
                        <? } ?>
                        <div class="row">
                            <div class="col-lg-6">
                                <?php
                                if ($action == 'Add') {
                                    if (scount($allservice_cat_data) <= 1) {
                                        ?>
                                        <input name="iServiceId" type="hidden" id="iServiceId" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId']; ?>"/>
                                    <?php } else { ?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <label>Service Type<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-12 col-sm-12">
                                                <select class="form-control" name='iServiceId' id="iServiceId" required onchange="changeserviceCategory(this.value)" id="iServiceId">
                                                    <option value="">Select</option>
                                                    <?php //foreach($db_company as $dbcm) {
                                                    ?>
                                                    <? for ($i = 0; $i < scount($service_cat_list); $i++) { ?>
                                                        <option value="<?= $service_cat_list[$i]['iServiceId'] ?>" <? if ($iServiceIdNew == $service_cat_list[$i]['iServiceId'] && $action == 'Add') { ?> selected <?php } else if ($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?>selected<? } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                                    <? } ?>
                                                    <?php //}
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>
                                            <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <select name="iCompanyId" class="form-control" id="iCompanyId" required onchange="changeMenuCategory(this.value)" <? if ($action == 'Edit') { ?> disabled <? } ?>>
                                            <option value="">Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></option>
                                            <?php foreach ($db_company as $dbc) { ?>
                                                <option value="<?php echo $dbc['iCompanyId']; ?>" <? if ($dbc['iCompanyId'] == $iCompanyId) { ?> selected<? } ?>><?php echo clearName($dbc['vCompany']); ?> - ( <?php echo clearEmail($dbc['vEmail']); ?> )</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <label><?php echo $langage_lbl_admin['LBL_MENU_CATEGORY_WEB_TXT'] ?>
                                            <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <select class="form-control" name='iFoodMenuId' required onChange="changeDisplayOrder(this.value, '<?php echo $id; ?>'); <?php if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) { ?> checkItemCategoryServiceType(this.value); <?php } ?>" id="iFoodMenuId">
                                            <option value=""><?php echo $langage_lbl_admin['LBL_SELECT_CATEGORY'] ?></option>
                                            <?php foreach ($db_menu as $dbmenu) { ?>
                                                <option value="<?= $dbmenu['iFoodMenuId'] ?>" <?= ($dbmenu['iFoodMenuId'] == $iFoodMenuId) ? 'selected' : ''; ?> <?php if (!empty($dbmenu['menuItems']) && scount($dbmenu['menuItems']) > 0) { ?><?php } ?>><?= $dbmenu['vMenu_' . $_SESSION['sess_lang']]; ?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>
                                <?php if (scount($db_master) > 1) { ?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <label><?= $langage_lbl_admin['LBL_MENU_ITEM_FRONT'] ?>
                                                <span class="red"> *</span></label>
                                        </div>
                                        <div class="<?= ($id != "") ? 'col-md-10 col-sm-10' : 'col-md-12 col-sm-12' ?>">
                                            <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>" id="vItemType_Default" name="vItemType_Default" value="<?= $arrLang['vItemType_' . $default_lang]; ?>" data-originalvalue="<?= $arrLang['vItemType_' . $default_lang]; ?>" readonly="readonly" <?php if ($id == "") { ?> onclick="editMenuItem('Add')" <?php } ?>>
                                        </div>
                                        <?php if ($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editMenuItem('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="modal fade" id="menu_item_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_MENU_ITEM_FRONT'] ?>
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vItemType_')">x</button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vItemType_' . $vCode;
                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?><?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if ($EN_available) {
                                                                if ($vCode == "EN") {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                            else {
                                                                if ($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-md-12 col-sm-12">
                                                                <label><?= $langage_lbl_admin['LBL_MENU_ITEM_FRONT'] ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" class="btn btn-primary" onClick="getAllLanguageCode('vItemType_', 'EN');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                }
                                                                else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" class="btn btn-primary" onClick="getAllLanguageCode('vItemType_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="modal-footer" style="margin-top: 0">
                                                    <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                        <strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                                    </h5>
                                                    <div class="nimot-class-but" style="margin-bottom: 0">
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveMenuItem()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vItemType_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                    </div>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <label><?= $langage_lbl_admin['LBL_MENU_ITEM_DESCRIPTION'] ?></label>
                                        </div>
                                        <div class="<?= ($id != "") ? 'col-md-10 col-sm-10' : 'col-md-12 col-sm-12' ?>">
                                            <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>" id="vItemDesc_Default" readonly="readonly" <?php if ($id == "") { ?> onclick="editMenuItemDesc('Add')" <?php } ?> data-originalvalue="<?= $arrLang['vItemDesc_' . $default_lang]; ?>" value="<?= $arrLang['vItemDesc_' . $default_lang]; ?>">
                                        </div>
                                        <?php if ($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editMenuItemDesc('Edit')">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="modal fade" id="item_desc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content nimot-class">
                                                <div class="modal-header">
                                                    <h4>
                                                        <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_MENU_ITEM_DESCRIPTION'] ?>
                                                        <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vItemDesc_')">x</button>
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    for ($i = 0; $i < $count_all; $i++) {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue_desc = 'vItemDesc_' . $vCode;
                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?><?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if ($EN_available) {
                                                                if ($vCode == "EN") {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                            else {
                                                                if ($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-md-12 col-sm-12">
                                                                <label><?= $langage_lbl_admin['LBL_MENU_ITEM_DESCRIPTION'] ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <input type="text" class="form-control" name="<?= $vValue_desc; ?>" id="<?= $vValue_desc; ?>" data-originalvalue="<?= $$vValue_desc; ?>" value="<?= $$vValue_desc; ?>">
                                                                <div class="text-danger" id="<?= $vValue_desc . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if ($EN_available) {
                                                                    if ($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" class="btn btn-primary" onClick="getAllLanguageCode('vItemDesc_', 'EN');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                }
                                                                else {
                                                                    if ($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type="button" class="btn btn-primary" onClick="getAllLanguageCode('vItemDesc_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="modal-footer" style="margin-top: 0">
                                                    <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                        <strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?>
                                                    </h5>
                                                    <div class="nimot-class-but" style="margin-bottom: 0">
                                                        <button type="button" class="save" style="margin-left: 0 !important" onclick="saveMenuItemDesc()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                        <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vItemDesc_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                    </div>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <label><?= $langage_lbl_admin['LBL_MENU_ITEM_FRONT'] ?>
                                                <span class="red"> *</span></label>
                                        </div>
                                        <div class="<?= ($id != "") ? 'col-md-10 col-sm-10' : 'col-md-12 col-sm-12' ?>">
                                            <input type="text" class="form-control" id="vItemType_<?= $default_lang ?>" name="vItemType_<?= $default_lang ?>" value="<?= $arrLang['vItemType_' . $default_lang]; ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <label><?= $langage_lbl_admin['LBL_MENU_ITEM_DESCRIPTION'] ?></label>
                                        </div>
                                        <div class="<?= ($id != "") ? 'col-md-10 col-sm-10' : 'col-md-12 col-sm-12' ?>">
                                            <textarea class="form-control" id="vItemDesc_<?= $default_lang ?>" name="vItemDesc_<?= $default_lang ?>"><?= $arrLang['vItemDesc_' . $default_lang]; ?></textarea>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if ($MODULES_OBJ->isEnableRequireMenuItemSKU()) { ?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <label><?php echo $langage_lbl_admin['LBL_MENU_ITEM_SKU_CODE_TXT'] ?><?= $MODULES_OBJ->isEnableRequireMenuItemSKU() ? '<span class="red"> *</span>' : "" ?></label>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <input type="text" class="form-control" name="vSKU" id="vSKU" value="<?= $vSKU; ?>" <?= $MODULES_OBJ->isEnableRequireMenuItemSKU() ? "required" : "" ?>>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <label><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'] ?>
                                            <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-12 col-sm-12" id="showDisplayOrder001">
                                        <?php if ($action == 'Add') { ?>
                                            <input type="hidden" name="total" value="<?php echo $count + 1; ?>">
                                            <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                <?php for ($i = 1; $i <= $count + 1; $i++) { ?>
                                                    <option value="<?php echo $i ?>" <?php
                                                    if ($i == $count + 1) echo 'selected';
                                                    ?>> <?php echo $i ?> </option>
                                                <?php } ?>
                                            </select>
                                        <?php } else { ?>
                                            <input type="hidden" name="total" value="<?php echo $iDisplayOrder; ?>">
                                            <select name="iDisplayOrder" id="iDisplayOrder" class="form-control" required>
                                                <?php for ($i = 1; $i <= $count; $i++) { ?>
                                                    <option value="<?php echo $i ?>" <?php if ($i == $iDisplayOrder) echo 'selected'; ?>> <?php echo $i ?> </option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                </div>
                                <!--    <div class="row">
                                                    <div class="col-md-12 col-sm-12">
                                                      <label>Status</label>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                      <div class="make-switch" data-on="success" data-off="warning" id="mySwitch">
                                                        <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> id="eStatus"/>
                                                      </div>
                                                    </div>
                                                    </div> -->
                                <?php if (!$MODULES_OBJ->isEnableItemMultipleImageVideoUpload()) { ?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <label><?php echo $langage_lbl_admin['LBL_MENU_ITEM_IMAGE'] ?>
                                                <span class="red" id="req_recommended"> *</span></label>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <div class="imageupload">
                                                <div class="file-tab">
                                                    <span id="single_img001">
                                                        <?php
                                                        $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $oldImage;
                                                        $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $oldImage;
                                                        if ($oldImage != "" && file_exists($imgpth)) {
                                                            ?>
                                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=250&h=250&src=' . $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                                        <?php } ?>
                                                    </span>
                                                    <div>
                                                        <input type="hidden" name="vImageTest" value="">
                                                        <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                                        <input name="vImage" onchange="preview_mainImg(event);" type="file" class="form-control">
                                                        <b>[Note: Recommended dimension is 4096px * 3072px (Aspect Ratio: 1.3333) and if this item is set as recommended then the item image is required.]</b>
                                                        <!--added by SP for required validation add in menu item image when recommended is on on 26-07-2019 -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                                else {
                                    echo $MENU_ITEM_MEDIA_OBJ->multiImageHTMl($langage_lbl_admin['LBL_MENU_ITEM_IMAGE_VIDEO'], $id);
                                } ?>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <label><?php echo $langage_lbl_admin['LBL_PRICE_FOR_MENU_ITEM'] ?> (In <?= $db_currency[0]['vName'] ?>)
                                            <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <input type="text" onkeyup="updateOptionPrice();" class="form-control" name="fPrice" id="fPrice" value="<?= $fPrice; ?>" required>
                                        <small>[<?php echo $langage_lbl_admin['LBL_NOTE_FRONT'] ?> <?php echo $langage_lbl_admin['LBL_NOTE_FOR_PRICE_MENU_ITEM'] ?>]</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <div class="row" style="padding-bottom:0; ">
                                                    <div class="col-lg-6">
                                                        <h5>
                                                            <b><?php echo $langage_lbl_admin['LBL_OPTIONS_MENU_ITEM'] ?></b>
                                                            <i class="icon-question-sign" id="helptxtchange" data-placement="top" data-toggle="tooltip" data-original-title='This feature can be used when you want to provide different options for the same product. The price would be added to the base price.For E.G.: Regular Pizza, Double Cheese Pizza etc.'></i>
                                                        </h5>
                                                    </div>
                                                    <div class="col-lg-6 text-right">
                                                        <button class="btn btn-success" type="button" onclick="options_fields();">
                                                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="panel-body" style="padding: 25px; overflow-y: auto;">
                                                <div id="options_fields">
                                                    <?
                                                    if (scount($db_optionsdata) > 0) {
                                                    $opt = 0;
                                                    foreach ($db_optionsdata

                                                    as $k => $option) {
                                                    $opt++;
                                                    ?>
                                                    <?php if ($option['eDefault'] == 'Yes') { ?>
                                                    <div class="form-group row eDefault removeclass<?= $opt ?>">
                                                    <div class="col-sm-5">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="<?= $option['fPrice'] ?>" placeholder="Price" readonly required="required">
                                                            <input type="hidden" name="optType[]" value="Options"/>
                                                            <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>"/><input type="hidden" name="eDefault[]" value="Yes"/>
                                                            <textarea name="options_lang_all[]" style="display: none;"><?= preg_replace('/"+/', '"', $option['tOptionNameLang']) ?></textarea>
                                                            <input type="hidden" name="vMenuItemOptionImage[]" value="">
                                                            <input type="hidden" name="vMenuItemOptionImgName" value="<?= $option['vImage'] ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-btn">

                                                                                        <span>
                                                                                            <button class="btn btn-info" type="button" onclick="edit_options_fields(0,1);" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                                                        </span>
                                                                    <span>
                                                                                            <button class="btn btn-danger" type="button" onclick="remove_options_fields('<?= $opt ?>');" data-toggle="tooltip" data-original-title="Remove" style="margin-right: 20px"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                                        </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                                <?php } else { ?>
                                                    <div class="form-group row removeclass<?= $opt ?>">
                                                        <div class="col-sm-5">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-5">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="<?= $option['fPrice'] ?>" placeholder="Price" readonly>
                                                                <input type="hidden" name="optType[]" value="Options"/>
                                                                <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>"/><input type="hidden" name="eDefault[]" value="No"/>
                                                                <textarea name="options_lang_all[]" style="display: none;"><?= trim($option['tOptionNameLang'], '"') ?></textarea>
                                                                <input type="hidden" name="vMenuItemOptionImage[]" value="">
                                                                <input type="hidden" name="vMenuItemOptionImgName" value="<?= $option['vImage'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-btn">
                                                                                        <span>
                                                                                            <button class="btn btn-info" type="button" onclick="edit_options_fields('<?= $opt ?>');" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                                                        </span>
                                                                        <span>
                                                                                            <button class="btn btn-danger" type="button" onclick="remove_options_fields('<?= $opt ?>');" data-toggle="tooltip" data-original-title="Remove" style="margin-right: 20px"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <?
                                                }
                                                }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel panel-default servicecatresponsive">
                                        <div class="panel-heading">
                                            <div class="row" style="padding-bottom:0;">
                                                <div class="col-lg-6">
                                                    <h5><b><?php echo $langage_lbl_admin['LBL_ADDON_FRONT'] ?>
                                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Addon/Topping Price will be additional amount which will added in base price'></i></b>
                                                    </h5>
                                                </div>
                                                <div class="col-lg-6 text-right">
                                                    <button class="btn btn-success" type="button" onclick="addon_fields();">
                                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 25px; overflow-y: auto;">
                                            <div id="addon_fields">
                                                <?
                                                if (scount($db_addonsdata) > 0) {
                                                    $a = 0;
                                                    foreach ($db_addonsdata as $k => $addon) {
                                                        $a++;
                                                        ?>
                                                        <div class="form-group row removeclassaddon<?= $a ?>">
                                                            <div class="col-sm-5">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="<?= $addon['vOptionName'] ?>" placeholder="Topping Name" required readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="<?= $addon['fPrice'] ?>" placeholder="Price" required readonly>
                                                                    <input type="hidden" name="optTypeaddon[]" value="Addon"/>
                                                                    <input type="hidden" name="addonId[]" value="<?= $addon['iOptionId'] ?>"/>
                                                                    <textarea name="addons_lang_all[]" style="display: none;"><?= trim($addon['tOptionNameLang'], '"') ?></textarea>
                                                                    <input type="hidden" name="vMenuItemAddonImage[]" value="">
                                                                    <input type="hidden" name="vMenuItemOptionImgName" value="<?= $addon['vImage'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-btn">
                                                                                    <span>
                                                                                        <button class="btn btn-info" type="button" onclick="edit_addon_fields('<?= $a ?>');" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                                                    </span>
                                                                            <span>
                                                                                        <button class="btn btn-danger" type="button" onclick="remove_addon_fields('<?= $a ?>');" data-toggle="tooltip" data-original-title="Remove" style="margin-right: 20px"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                                    </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>
                                                        <?
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <label><?php echo $langage_lbl_admin['LBL_OFFER_AMOUNT_MENU_ITEM'] ?>(%)
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set Offer amount on an item, if you want to show discounted/strikeout amount. E.g If Item Price is $100 but you want to sell it for $80, then set Offer Amount = 20%, hence the final price of this item is $80'></i></label>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <input type="text" class="form-control" name="fOfferAmt" id="fOfferAmt" value="<?= $fOfferAmt; ?>"/>
                                    <small><?php echo $langage_lbl_admin['LBL_NOTE_FRONT'] . " " . $langage_lbl_admin['LBL_DISCOUNT_NOTE']; ?></small>
                                </div>
                            </div>
                            <div class="row servicecatresponsive">
                                <div class="col-md-12 col-sm-12">
                                    <label>Food Type<span class="red">*</span></label>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <select class="form-control" name="eFoodType" id="eFoodType">
                                        <option value="">--Select--</option>
                                        <option value="Veg" <?
                                        if ($eFoodType == 'Veg') {
                                            echo 'selected';
                                        }
                                        ?>>Veg Food
                                        </option>
                                        <option value="NonVeg" <?
                                        if ($eFoodType == 'NonVeg') {
                                            echo 'selected';
                                        }
                                        ?>>Non Veg Food
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <label><?php echo $langage_lbl_admin['LBL_ITEM_IN_STOCK_WEB'] ?>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="If this item is set On by the store/restaurant then it will be available for <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>'s to order it, Set it off when the item is out of stock"></i></label>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="make-switch" data-on="success" data-off="warning" id="mySwitch">
                                        <input type="checkbox" name="eAvailable" <?= ($id != '' && $eAvailable == 'No') ? '' : 'checked'; ?> id="eAvailable"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <label><?php echo $langage_lbl_admin['LBL_IS_ITEM_RECOMMENDED'] ?>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Suggest the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>'s to order this item. The recommended items will be highlighted in the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> app with the image and display at the top section"></i></label>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="make-switch" data-on="success" data-off="warning" data-on-text="Yes" data-off-text="No" id="mySwitch1">
                                        <input type="checkbox" name="eRecommended" <?= ($id != '' && $eRecommended == 'No') ? '' : 'checked'; ?> id="eRecommended"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <label><?php echo $langage_lbl_admin['LBL_ITEM_TAG_NAME'] ?>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set the tag name to this item. Like, Best Seller, Most Popular"></i></label>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <select class="form-control" name="vHighlightName" id="vHighlightName">
                                        <option value="">Select Tag</option>
                                        <option value="LBL_BESTSELLER" <?
                                        if ($vHighlightName == 'LBL_BESTSELLER') {
                                            echo 'selected';
                                        }
                                        ?>><?php echo $langage_lbl_admin['LBL_BESTSELLER'] ?></option>
                                        <option value="LBL_NEWLY_ADDED" <?
                                        if ($vHighlightName == 'LBL_NEWLY_ADDED') {
                                            echo 'selected';
                                        }
                                        ?>><?php echo $langage_lbl_admin['LBL_NEWLY_ADDED'] ?></option>
                                        <option value="LBL_PROMOTED" <?
                                        if ($vHighlightName == 'LBL_PROMOTED') {
                                            echo 'selected';
                                        }
                                        ?>><?php echo $langage_lbl_admin['LBL_PROMOTED'] ?></option>
                                    </select>
                                </div>
                            </div>
                            <!-- For Prescription required start added by sneha  -->
                            <?php
                            if ($id == "" && $prescription_required == "No") {
                                $checked_prescription = "";
                            }
                            else if ($id != "" && $prescription_required == "No") {
                                $checked_prescription = "";
                            }
                            else if ($prescription_required == "Yes") {
                                $checked_prescription = "checked";
                            }
                            ?>
                            <div class="row" id="prescription_div">
                                <div class="col-md-12 col-sm-12">
                                    <label><?php echo $langage_lbl_admin['LBL_IS_PRESCRIPTION_REQUIRED'] ?>
                                        <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="This will allow user to upload the precription while placing the order"></i></label>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="make-switch" data-on="success" data-off="warning" data-on-text="Yes" data-off-text="No" id="mySwitch1">
                                        <input type="checkbox" name="prescription_required" <?php echo $checked_prescription; ?> id="prescription_required"/>
                                    </div>
                                </div>
                            </div>
                            <!-- For Prescription required end added by sneha  -->
                        </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-item')) || ($action == 'Add' && $userObj->hasPermission('create-item'))) { ?>
                            <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?php echo $langage_lbl_admin['LBL_Save']; ?>">
                        <?php } ?>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>
<!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none; z-index: 99999">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<div class="modal fade" id="add_options_toppings" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4>
                    <span id="option_addon_title"></span>
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="option_addon_type" id="option_addon_type">
                <input type="hidden" name="option_addon_action" id="option_addon_action">
                <input type="hidden" name="option_addon_id" id="option_addon_id">
                <input type="hidden" id="iOptionsCategoryId">
                <?php
                if (scount($db_master) > 1) {
                    for ($i = 0; $i < $count_all; $i++) {
                        $vCode = $db_master[$i]['vCode'];
                        $vTitle = $db_master[$i]['vTitle'];
                        $eDefault = $db_master[$i]['eDefault'];
                        $vValue = 'tOptionNameLang_' . $vCode;
                        $vValueName = 'tOptionName_' . $vCode;
                        $required = ($eDefault == 'Yes') ? 'required' : '';
                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                        if ($EN_available) {
                            if ($vCode == "EN") {
                                $class_option = 'class="col-md-6"';
                            }
                            else {
                                $class_option = 'class="col-md-12"';
                            }
                        }
                        else {
                            if ($vCode == $default_lang) {
                                $class_option = 'class="col-md-6"';
                            }
                            else {
                                $class_option = 'class="col-md-12"';
                            }
                        }
                        ?>
                        <div class="form-group row">
                            <div <?= $class_option ?>>
                                <label><span id="<?= $vValueName ?>">Option Name</span> (<?= $vTitle ?>)</label>
                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                            </div>
                            <?php
                            if (scount($db_master) > 1) {
                                if ($EN_available) {
                                    if ($vCode == "EN") { ?>
                                        <div class="col-md-6">
                                            <label>Option Price (Price In <?= $db_currency[0]['vName'] ?>)</label>
                                            <input type="text" class="form-control" name="item_option_topping_price" id="item_option_topping_price" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)">
                                            <div class="text-danger" id="item_option_topping_price_error" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                        </div>
                                    <?php }
                                }
                                else {
                                    if ($vCode == $defaultLang) { ?>
                                        <div class="col-md-6">
                                            <label>Option Price (Price In <?= $db_currency[0]['vName'] ?>)</label>
                                            <input type="text" class="form-control" name="item_option_topping_price" id="item_option_topping_price" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)">
                                            <div class="text-danger" id="item_option_topping_price_error" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                        </div>
                                    <?php }
                                }
                            }
                            ?>
                        </div>
                        <?php
                        if (scount($db_master) > 1) {
                            if ($EN_available) {
                                if ($vCode == "EN") { ?>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" onclick="getAllLanguageCode('tOptionNameLang_', 'EN');">Convert To All Language</button>
                                        </div>
                                    </div>
                                    <?php if (strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                                        <div class="form-group row" id="extra_img_upload">
                                            <div class="col-md-12 col-sm-12">
                                                <label id="option_addon_img_title"></label>
                                            </div>
                                            <div class="col-md-12 col-sm-12">
                                                <div class="imageupload">
                                                    <div class="file-tab">
                                                        <div>
                                                            <input type="hidden" name="vImageTest" value="">
                                                            <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                                            <input id="vMenuItemImage" type="file" class="form-control">
                                                            <b>[Note: This is only applicable for Kiosk order Apps only.]</b>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                }
                            }
                            else {
                                if ($vCode == $defaultLang) { ?>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" onclick="getAllLanguageCode('tOptionNameLang_', '<?= $default_lang ?>');">Convert To All Language</button>
                                        </div>
                                    </div>
                                    <?php if (strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                                        <div class="form-group row" id="extra_img_upload">
                                            <div class="col-md-12 col-sm-12">
                                                <label id="option_addon_img_title"></label>
                                            </div>
                                            <div class="col-md-12 col-sm-12">
                                                <div class="imageupload">
                                                    <div class="file-tab">
                                                            <span id="single_img001">
                                                                <?php
                                                                $imgpth = $tconfig["tsite_upload_images_menu_category_path"] . '/' . $oldImage;
                                                                $imgUrl = $tconfig["tsite_upload_images_menu_category"] . '/' . $oldImage;
                                                                if ($oldImage != "" && file_exists($imgpth)) {
                                                                    ?>
                                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=250&h=250&src=' . $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                                                <?php } ?>
                                                            </span>
                                                        <div>
                                                            <input type="hidden" name="vImageTest" value="">
                                                            <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                                            <input id="vMenuItemImage" type="file" class="form-control">
                                                            <b>[Note: This is only applicable for Kiosk order Apps only.]</b>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                }
                            }
                        }
                        ?><?php
                    }
                }
                else { ?>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label>Option Name (<?= $db_master[0]['vTitle'] ?>)</label>
                            <input type="text" class="form-control" name="tOptionNameLang_<?= $default_lang; ?>" id="tOptionNameLang_<?= $default_lang; ?>" placeholder="<?= $db_master[0]['vTitle']; ?> Value">
                            <div class="text-danger" id="<?= $vValue . '_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                        </div>
                        <div class="col-md-6">
                            <label>Option Price (Price In <?= $db_currency[0]['vName'] ?>)</label>
                            <input type="text" class="form-control" name="item_option_topping_price" id="item_option_topping_price" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)">
                            <div class="text-danger" id="item_option_topping_price_error" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                        </div>
                    </div>
                    <?php if (strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                        <div class="form-group row" id="extra_img_upload">
                            <div class="col-md-12 col-sm-12">
                                <label id="option_addon_img_title"></label>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="imageupload">
                                    <div class="file-tab">
                                            <span id="single_img001">
                                                <?php
                                                $imgpth = $tconfig["tsite_upload_images_menu_category_path"] . '/' . $oldImage;
                                                $imgUrl = $tconfig["tsite_upload_images_menu_category"] . '/' . $oldImage;
                                                if ($oldImage != "" && file_exists($imgpth)) {
                                                    ?>
                                                    <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=250&h=250&src=' . $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                                <?php } ?>
                                            </span>
                                        <div>
                                            <input type="hidden" name="vImageTest" value="">
                                            <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                            <input id="vMenuItemImage" type="file" class="form-control">
                                            <b>[Note: This is only applicable for Kiosk order Apps only.]</b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="modal-footer" style="margin-top: 0">
                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                    <strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                <div class="nimot-class-but" style="margin-bottom: 0">
                    <button type="button" class="save" id="add_options_toppings_btn" style="margin-left: 0 !important"><?= $langage_lbl['LBL_ADD']; ?></button>
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>
<? include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<!--<link href="../assets/css/imageUpload/bootstrap-imageupload.css" rel="stylesheet">-->
<!--For Faretype-->
<script>
    $('[data-toggle="tooltip"]').tooltip();
    var successMSG1 = '<?php echo $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }
</script>
<script>
    var myVar;

    function changeDisplayOrder(foodId, menuId, parentId) {
        var itemParentId = '';
        if (parentId != '') {
            itemParentId = parentId
        }

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_display_order.php',
            'AJAX_DATA': {
                iFoodMenuId: foodId,
                page: 'items',
                iMenuItemId: menuId
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#showDisplayOrder001").html('');
                $("#showDisplayOrder001").html(data);
            } else {
                console.log(response.result);
            }
        });

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_display_order.php',
            'AJAX_DATA': {
                method: 'getParentItems',
                page: 'items',
                iFoodMenuId: foodId,
                itemParentId: itemParentId
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#iParentId").html(data);
            } else {
                console.log(response.result);
            }
        });
    }

    function changeMenuCategory(iCompanyId) {
        var iFoodMenuId = '<?php echo $iFoodMenuId; ?>';

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_food_category.php',
            'AJAX_DATA': {
                iCompanyId: iCompanyId,
                iFoodMenuId: iFoodMenuId
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#iFoodMenuId").html('');
                $("#iFoodMenuId").html(data);
            } else {
                console.log(response.result);
            }
        });
    }

    var action = "<?= $action ?>";
    if (action == 'Add') {
        var iServiceIdNew = $("#iServiceId").val();
    } else {
        var iServiceIdNew = "<?= $EditServiceIdNew ?>";
    }

    // For Prescription required start added by sneha
    var ajaxData = {
        'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_chk_prescription.php',
        'AJAX_DATA': {
            iServiceIdNew: iServiceIdNew
        },
    };
    getDataFromAjaxCall(ajaxData, function (response) {
        if (response.action == "1") {
            var data = response.result;
            if (data == 'Yes') {
                $('#prescription_div').show();
            } else {
                $('#prescription_div').hide();
            }
        } else {
            console.log(response.result);
        }
    });
    // For Prescription required end added by sneha
    $(document).ready(function () {
        changeMenuCategory('<?php echo $iCompanyId; ?>', '<?php echo $iFoodMenuId; ?>');
        changeDisplayOrder('<?php echo $iFoodMenuId; ?>', '<?php echo $id; ?>', '<?php echo $menuiParentId; ?>');
        var servicecounts = '<? echo scount($service_cat_list) ?>';
        <?php if ($action == "Add") { ?>
        if (servicecounts > '1') {
            changeserviceCategory(iServiceIdNew);
        }
        <?php } ?>

        <?php if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) { ?>
        checkItemCategoryServiceType('<?php echo $iFoodMenuId; ?>');
        <?php } ?>
    });

    function changeserviceCategory(iServiceId) {
        var iCompanyId = '<?php echo $iCompanyId; ?>';
        var helpText = "This feature can be used when you want to provide different options for the same product. The price would be added to the base price.For E.G.: Regular Pizza, Double Cheese Pizza etc.";
        var baseOptionValue = "Regular";
        var basePrice = "";
        if (iServiceId > 1) {
            helpText = "This feature can be used when you want to provide different options for the same product.";
            baseOptionValue = "";
            basePrice = $("#fPrice").val();
        }
        if (iCompanyId == "" || iCompanyId == "0" || iCompanyId == 0) {
            // $("#BaseOptions").val(baseOptionValue);
            // $("#OptPrice").val(basePrice);
        }
        $("#helptxtchange").attr("data-original-title", helpText);

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_company_filter.php',
            'AJAX_DATA': {
                iServiceIdNew: iServiceId,
                iCompanyId: iCompanyId
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#iCompanyId").html('');
                $("#iCompanyId").html(data);
                $("#iCompanyId").trigger("change");
            } else {
                console.log(response.result);
            }
        });

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_chk_prescription.php',
            'AJAX_DATA': {
                iServiceIdNew: iServiceId
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                if (data == 'Yes') {
                    $('#prescription_div').show();
                } else {
                    $('#prescription_div').hide();
                }
            } else {
                console.log(response.result);
            }
        });
        // For Prescription required end added by sneha

        <?php if ($action == "Add") { ?>
        var serviceID = $('#iServiceId').val();
        <?php } else { ?>
        var serviceID = '<?= $EditServiceIdNew ?>';
        <?php } ?>

        $('.eDefault').remove();
        if (serviceID == 1 && $('#options_fields').find('div').length > 0 && $('#options_fields').find('div.eDefault').length == 0) {
            jsonObj = '<?= $lbl_regular_str ?>';

            var baseOptionValueDefault = '<?= $lbl_regular_txt ?>';

            var item_options_default = JSON.stringify(jsonObj);
            var item_default = "Yes";
        }

        var objTo = document.getElementById('options_fields');

        if (item_default == 'Yes') {
            var divtest1 = document.createElement("div");
            divtest1.setAttribute("class", "form-group row eDefault");
            divtest1.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValueDefault + '" placeholder="Option Name" readonly></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="0" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="Yes"/><textarea name="options_lang_all[]" style="display: none">' + item_options_default + '</textarea></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"><span><button class="btn btn-info" type="button" onclick="edit_options_fields(0, 1);" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button></span></div></div></div></div><div class="clear"></div>';
            objTo.prepend(divtest1);
        }

        if ($('#extra_img_upload').length > 0) {
            $('#extra_img_upload').val("");
            if (serviceID == 1) {
                $('#extra_img_upload').show();
            } else {
                $('#extra_img_upload').hide();
            }
        }
    }

    function preview_mainImg(event) {
        $("#single_img001").html('');
        $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
        $(".changeImg001").text('Change');
        $(".remove_main").show();
    }

    <? if (scount($db_optionsdata) > 0) { ?>
    var optionid = '<?= scount($db_optionsdata) ?>';
    <? } else { ?>
    var optionid = 0;
    <? } ?>


    $('#tOptionNameLang_<?= $default_lang ?>, #item_option_topping_price').on('keyup change', function () {
        if ($(this).val() != "") {
            $(this).next('.text-danger').hide();
        }
    });

    function options_fields() {
        $('#option_addon_title').html("<?= $langage_lbl_admin['LBL_ADD_OPTION'] ?>");
        $('#option_addon_type').val("options");
        $('#option_addon_action').val("add");
        $('#add_options_toppings_btn').html("<?= $langage_lbl_admin['LBL_ADD'] ?>");
        $('#option_addon_img_title').html("<?= $langage_lbl_admin['LBL_OPTION_IMG'] ?>");

        $('[name^=tOptionNameLang_], #item_option_topping_price').val("");
        $('#item_option_topping_price').prop('readonly', false);
        $('#add_options_toppings .modal-body').animate({
            scrollTop: 0
        }, 'fast');
        $("#vMenuItemImage").val(null);

        $('#add_options_toppings').modal('show');
    }

    $('#add_options_toppings_btn').click(function () {
        <?php if ($EN_available) { ?>
        if ($('#tOptionNameLang_EN').val().trim() == "") {
            $('#tOptionNameLang_EN_error').show();
            $('#tOptionNameLang_EN').focus();
            $('#tOptionNameLang_EN').val("");

            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#tOptionNameLang_EN_error').hide();
            }, 5000);
            return false;
        }
        <?php } else { ?>
        if ($('#tOptionNameLang_<?= $default_lang ?>').val().trim() == "") {
            $('#tOptionNameLang_<?= $default_lang ?>_error').show();
            $('#tOptionNameLang_<?= $default_lang ?>').focus();
            $('#tOptionNameLang_<?= $default_lang ?>').val("");

            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#tOptionNameLang_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        <?php } ?>


        if ($('#item_option_topping_price').val() == "") {
            $('#item_option_topping_price_error').show();
            $('#item_option_topping_price').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#item_option_topping_price_error').hide();
            }, 5000);
            return false;
        }

        <?php if ($action == "Add") { ?>
        var serviceID = $('#iServiceId').val();
        <?php } else { ?>
        var serviceID = '<?= $EditServiceIdNew ?>';
        <?php } ?>

        if ($('#item_option_topping_price').val() == 0 && serviceID > 1) {
            $('#item_option_topping_price_error').text('<?= $langage_lbl_admin['LBL_TRGAMT_VALIDATION_MAX_FRONT'] ?>');
            $('#item_option_topping_price_error').show();
            $('#item_option_topping_price').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#item_option_topping_price_error').hide();
                $('#item_option_topping_price_error').text('<?= $langage_lbl_admin['LBL_REQUIRED'] ?>');
            }, 5000);
            return false;
        }

        jsonObj = {};
        $('[name^=tOptionNameLang_]').each(function () {
            jsonObj[$(this).attr('name')] = $(this).val();
        });

        if ($('#option_addon_action').val() == "add") {
            if ($('#option_addon_type').val() == "options") {
                options_fields_add(jsonObj);
            } else {
                addon_fields_add(jsonObj);
            }
        } else {
            var option_id = $('#option_addon_id').val();

            if ($('#option_addon_type').val() == "options") {
                if (option_id == 0 && serviceID == 1) {
                    $('#options_fields').find('.eDefault').find('[name="BaseOptions[]"]').val(jsonObj.tOptionNameLang_<?= $default_lang ?>);
                    $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').text(JSON.stringify(jsonObj));
                    $('#options_fields').find('.eDefault').find('[name="OptPrice[]"]').val(0);
                } else {
                    $('#options_fields').find('.removeclass' + option_id).find('[name="BaseOptions[]"]').val(jsonObj.tOptionNameLang_<?= $default_lang ?>);
                    $('#options_fields').find('.removeclass' + option_id).find('[name="options_lang_all[]"]').text(JSON.stringify(jsonObj));
                    $('#options_fields').find('.removeclass' + option_id).find('[name="OptPrice[]"]').val($('#item_option_topping_price').val());
                }

                if (serviceID > 1) {
                    $('#fPrice').val($('#OptPrice').val());
                }
            } else {
                $('#addon_fields').find('.removeclassaddon' + option_id).find('[name="AddonOptions[]"]').val(jsonObj.tOptionNameLang_<?= $default_lang ?>);
                $('#addon_fields').find('.removeclassaddon' + option_id).find('[name="addons_lang_all[]"]').text(JSON.stringify(jsonObj));
                $('#addon_fields').find('.removeclassaddon' + option_id).find('[name="AddonPrice[]"]').val($('#item_option_topping_price').val());
            }

            var ENABLE_ORDER_FROM_STORE_KIOSK = '<?= ENABLE_ORDER_FROM_STORE_KIOSK ?>';
            if (ENABLE_ORDER_FROM_STORE_KIOSK == "Yes" && $("#vMenuItemImage")[0].files.length > 0) {

                var files = $('#vMenuItemImage')[0].files[0];
                var fd = new FormData();
                fd.append('vImage', files);

                showLoader();

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_upload_temp_image.php',
                    'AJAX_DATA': fd,
                    'REQUEST_DATA_TYPE': 'json',
                    'REQUEST_CONTENT_TYPE': false,
                    'REQUEST_PROCESS_DATA': false,
                };
                getDataFromAjaxCall(ajaxData, function (data) {
                    if (data.action == "1") {
                        var response = data.result;
                        if (response.Action == 1) {

                            if ($('#option_addon_type').val() == "options") {
                                var img_input = $('<input>').attr({
                                    type: 'hidden',
                                    name: 'vMenuItemOptionImage[]',
                                    value: response.message
                                });

                                if (option_id == 0 && serviceID == 1) {
                                    $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImage[]"]').remove();
                                    $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').append(img_input);
                                    $('#options_fields').find('.eDefault').find('[name="vMenuItemOptionImgName"]').val(response.message);
                                } else {
                                    $('#options_fields').find('.removeclass' + option_id).find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImage[]"]').remove();
                                    $('#options_fields').find('.removeclass' + option_id).find('[name="options_lang_all[]"]').closest('.form-group').append(img_input);
                                    $('#options_fields').find('.removeclass' + option_id).find('[name="vMenuItemOptionImgName"]').val(response.message);
                                }
                            } else {
                                var img_input = $('<input>').attr({
                                    type: 'hidden',
                                    name: 'vMenuItemAddonImage[]',
                                    value: response.message
                                });

                                $('#addon_fields').find('.removeclassaddon' + option_id).find('[name="addons_lang_all[]"]').closest('.form-group').find('[name="vMenuItemAddonImage[]"]').remove();
                                $('#addon_fields').find('.removeclassaddon' + option_id).find('[name="addons_lang_all[]"]').closest('.form-group').append(img_input);
                                $('#addon_fields').find('.removeclassaddon' + option_id).find('[name="vMenuItemOptionImgName"]').val(response.message);
                            }

                            hideLoader();
                            $('#add_options_toppings').modal('hide');
                        }
                    } else {
                        alert("<?= $langage_lbl_admin['LBL_TRY_AGAIN_LATER_TXT'] ?>");
                        hideLoader();
                        $('#add_options_toppings').modal('hide');
                    }
                });
            } else {
                $('#add_options_toppings').modal('hide');
            }
        }
    });

    $("#item_option_topping_price").on("keypress keyup blur paste keydown", function (event) {
        if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if ($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault();
    });

    $(document).on("keypress keyup blur paste keydown", '[name="BaseOptions[]"], [name="OptPrice[]"], [name="AddonOptions[]"], [name="AddonPrice[]"]', function (event) {
        event.preventDefault();
    });

    function options_fields_add(options, eDefault = 0) {
        var container_div = document.getElementById('options_fields');
        var count = container_div.getElementsByTagName('div').length;
        var serviceId = $("#iServiceId").val();
        var basePrice = 0;
        var baseOptionValue = "Regular";

        <?php if ($action == "Add") { ?>
        var serviceID = $('#iServiceId').val();
        <?php } else { ?>
        var serviceID = '<?= $EditServiceIdNew ?>';
        <?php } ?>

        var item_default = "No";

        if (serviceID == 1 && $('#options_fields').find('div').length == 0) {
            jsonObj = '<?= $lbl_regular_str ?>';

            var baseOptionValueDefault = '<?= $lbl_regular_txt ?>';

            var item_options_default = JSON.stringify(jsonObj);
            var item_default = "Yes";
        }

        baseOptionValue = options.tOptionNameLang_<?= $default_lang ?>;


        var item_options_all = JSON.stringify(options);

        if (serviceId > 1) {
            // baseOptionValue = "";
            var basePrice = $("#fPrice").val();
        }

        basePrice = $('#item_option_topping_price').val();

        if (count == 0) {
            optionid = 0;
        }
        optionid++;
        var objTo = document.getElementById('options_fields');


        if (item_default == 'Yes') {
            var divtest1 = document.createElement("div");
            divtest1.setAttribute("class", "form-group row eDefault");
            divtest1.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValueDefault + '" placeholder="Option Name" readonly></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="0" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="Yes"/><textarea name="options_lang_all[]" style="display: none" data-static="yes">' + item_options_default + '</textarea></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"><span><button class="btn btn-info" type="button" onclick="edit_options_fields(0, 1);" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button></span></div></div></div></div><div class="clear"></div>';
            objTo.appendChild(divtest1);

            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group row removeclass" + optionid);
            divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValue + '" placeholder="Option Name" readonly></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="' + basePrice + '" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/><textarea name="options_lang_all[]" style="display: none">' + item_options_all + '</textarea></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"><span><button class="btn btn-info" type="button" onclick="edit_options_fields(' + optionid + ');" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button></span><span> <button class="btn btn-danger" type="button" onclick="remove_options_fields(' + optionid + ');" data-toggle="tooltip" data-original-title="Remove" style="margin-right: 20px;"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></span></div></div></div></div><div class="clear"></div>';

            objTo.appendChild(divtest);
        } else {
            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group row removeclass" + optionid);
            divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValue + '" placeholder="Option Name" readonly></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="' + basePrice + '" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/><textarea name="options_lang_all[]" style="display: none">' + item_options_all + '</textarea></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"><span><button class="btn btn-info" type="button" onclick="edit_options_fields(' + optionid + ');" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button></span><span> <button class="btn btn-danger" type="button" onclick="remove_options_fields(' + optionid + ');" data-toggle="tooltip" data-original-title="Remove" style="margin-right: 20px;"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></span></div></div></div></div><div class="clear"></div>';

            objTo.appendChild(divtest);
        }

        if (serviceID > 1) {
            $('#fPrice').val($('#OptPrice').val());
        }

        var ENABLE_ORDER_FROM_STORE_KIOSK = '<?= ENABLE_ORDER_FROM_STORE_KIOSK ?>';
        if (ENABLE_ORDER_FROM_STORE_KIOSK == "Yes" && $("#vMenuItemImage")[0].files.length > 0) {

            var files = $('#vMenuItemImage')[0].files[0];
            var fd = new FormData();
            fd.append('vImage', files);

            showLoader();

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_upload_temp_image.php',
                'AJAX_DATA': fd,
                'REQUEST_DATA_TYPE': 'json',
                'REQUEST_CONTENT_TYPE': false,
                'REQUEST_PROCESS_DATA': false,
            };
            getDataFromAjaxCall(ajaxData, function (data) {
                if (data.action == "1") {
                    var response = data.result;
                    if (response.Action == 1) {
                        var img_input = $('<input>').attr({
                            type: 'hidden',
                            name: 'vMenuItemOptionImage[]',
                            value: response.message
                        });

                        var img_input_name = $('<input>').attr({
                            type: 'hidden',
                            name: 'vMenuItemOptionImgName',
                            value: response.message
                        });

                        if (optionid == 0 && serviceID == 1) {
                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImage[]"]').remove();
                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').append(img_input);
                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImgName"]').remove();
                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').append(img_input_name);
                        } else {
                            $('#options_fields').find('.removeclass' + optionid).find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImage[]"]').remove();
                            $('#options_fields').find('.removeclass' + optionid).find('[name="options_lang_all[]"]').closest('.form-group').append(img_input);
                            $('#options_fields').find('.removeclass' + optionid).find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImgName"]').remove();
                            $('#options_fields').find('.removeclass' + optionid).find('[name="options_lang_all[]"]').closest('.form-group').append(img_input_name);
                        }

                        hideLoader();
                        $('#add_options_toppings').modal('hide');
                    }
                } else {
                    alert("<?= $langage_lbl_admin['LBL_TRY_AGAIN_LATER_TXT'] ?>");
                    hideLoader();
                    $('#add_options_toppings').modal('hide');
                }
            });
        } else {
            $('#add_options_toppings').modal('hide');
        }

        $('[data-toggle="tooltip"]').tooltip();
    }

    function remove_options_fields(rid) {
        var option_fields_length = $('#options_fields').find('[name="BaseOptions[]"]').length;


        if (option_fields_length == 2) {
            $('.eDefault').remove();
            $('.removeclass' + rid).remove();
            var optionid = 0;
        } else {
            $('.removeclass' + rid).remove();
        }


        $("#fPrice").val($("#OptPrice").val());

    }
    <? if (scount($db_addonsdata) > 0) { ?>
    var addonid = '<?= scount($db_addonsdata) ?>';
    <? } else { ?>
    var addonid = 0;
    <? } ?>

    function addon_fields() {
        $('#option_addon_title').html("<?= $langage_lbl_admin['LBL_ADD_ADDON_TOPPING'] ?>");
        $('#option_addon_type').val("addons");
        $('#option_addon_action').val("add");
        $('#add_options_toppings_btn').html("<?= $langage_lbl_admin['LBL_ADD'] ?>");
        $('#option_addon_img_title').html("<?= $langage_lbl_admin['LBL_ADDON_TOPPING_IMG'] ?>");

        $('#item_option_topping_price').prop('readonly', false);
        $('[name^=tOptionNameLang_], #item_option_topping_price').val("");
        $('#add_options_toppings .modal-body').animate({
            scrollTop: 0
        }, 'fast');
        $("#vMenuItemImage").val(null);

        $('#add_options_toppings').modal('show');
    }

    function addon_fields_add(addon_toppings) {
        var item_addons = JSON.stringify(addon_toppings);

        var baseAddonValue = jsonObj.tOptionNameLang_<?= $default_lang ?>;
        var baseAddonPrice = $('#item_option_topping_price').val();

        addonid++;
        var objTo = document.getElementById('addon_fields');
        var divtest = document.createElement("div");
        divtest.setAttribute("class", "form-group row removeclassaddon" + addonid);
        divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="' + baseAddonValue + '" placeholder="Topping Name" required readonly></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="' + baseAddonPrice + '" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" required readonly><input type="hidden" name="addonId[]" value="" /><input type="hidden" name="optTypeaddon[]" value="Addon" /><textarea name="addons_lang_all[]" style="display: none">' + item_addons + '</textarea></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <span><button class="btn btn-info" type="button" onclick="edit_addon_fields(' + addonid + ');" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button></span><span><button class="btn btn-danger" type="button" onclick="remove_addon_fields(' + addonid + ');" style="margin-right: 20px;"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></span></div></div></div></div><div class="clear"></div>';
        objTo.appendChild(divtest);

        var ENABLE_ORDER_FROM_STORE_KIOSK = '<?= ENABLE_ORDER_FROM_STORE_KIOSK ?>';
        if (ENABLE_ORDER_FROM_STORE_KIOSK == "Yes" && $("#vMenuItemImage")[0].files.length > 0) {

            var files = $('#vMenuItemImage')[0].files[0];
            var fd = new FormData();
            fd.append('vImage', files);

            showLoader();

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_upload_temp_image.php',
                'AJAX_DATA': fd,
                'REQUEST_DATA_TYPE': 'json',
                'REQUEST_CONTENT_TYPE': false,
                'REQUEST_PROCESS_DATA': false,
            };
            getDataFromAjaxCall(ajaxData, function (data) {
                if (data.action == "1") {
                    var response = data.result;
                    if (response.Action == 1) {
                        var img_input = $('<input>').attr({
                            type: 'hidden',
                            name: 'vMenuItemAddonImage[]',
                            value: response.message
                        });

                        var img_input_name = $('<input>').attr({
                            type: 'hidden',
                            name: 'vMenuItemOptionImgName',
                            value: response.message
                        });

                        $('#addon_fields').find('.removeclassaddon' + addonid).find('[name="addons_lang_all[]"]').closest('.form-group').find('[name="vMenuItemAddonImage[]"]').remove();
                        $('#addon_fields').find('.removeclassaddon' + addonid).find('[name="addons_lang_all[]"]').closest('.form-group').append(img_input);
                        $('#addon_fields').find('.removeclassaddon' + addonid).find('[name="addons_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImgName"]').remove();
                        $('#addon_fields').find('.removeclassaddon' + addonid).find('[name="addons_lang_all[]"]').closest('.form-group').append(img_input_name);

                        hideLoader();
                        $('#add_options_toppings').modal('hide');
                    }
                } else {
                    alert("<?= $langage_lbl_admin['LBL_TRY_AGAIN_LATER_TXT'] ?>");
                    hideLoader();
                    $('#add_options_toppings').modal('hide');
                }
            });
        } else {
            $('#add_options_toppings').modal('hide');
        }
    }

    function remove_addon_fields(rid) {
        $('.removeclassaddon' + rid).remove();
    }

    function edit_options_fields(eid, eDefault = 0) {
        $('#option_addon_title').html("<?= $langage_lbl_admin['LBL_EDIT_OPTION'] ?>");
        $('#option_addon_type').val("options");
        $('#option_addon_id').val(eid);
        $('#option_addon_img_title').html("<?= $langage_lbl_admin['LBL_OPTION_IMG'] ?>");

        $("#vMenuItemImage").val(null);

        var option_values = $('.removeclass' + eid).find('[name="options_lang_all[]"]').text();
        var option_price = $('.removeclass' + eid).find('[name="OptPrice[]"]').val();
        var option_default = $('.removeclass' + eid).find('[name="eDefault[]"]').val();
        var option_BaseOptions = $('.removeclass' + eid).find('[name="BaseOptions[]"]').val();
        var option_Image = $('.removeclass' + eid).find('[name="vMenuItemOptionImgName"]').val();

        $('#item_option_topping_price').prop('readonly', false);
        if (eDefault == 1) {
            var option_BaseOptions = $('.eDefault').find('[name="BaseOptions[]"]').val();
            var option_values = $('.eDefault').find('[name="options_lang_all[]"]').text();
            var option_Image = $('.eDefault').find('[name="vMenuItemOptionImgName"]').val();

            var option_price = 0;
            //$('#item_option_topping_price').prop('readonly', true);
            var option_id_tmp = $('.eDefault').find('[name="OptionId[]"]').val();

            if (option_id_tmp == "") {
                option_values = JSON.parse(option_values);
            }
        }

        if (option_values != "") {
            option_values = JSON.parse(option_values);

            $('[name^=tOptionNameLang_]').each(function () {
                var attr_name = $(this).attr('name');

                $(this).val(option_values[attr_name]);
            });
        } else {
            <?php if ($EN_available) { ?>
            $('#tOptionNameLang_EN').val(option_BaseOptions);
            <?php } else { ?>
            $('#tOptionNameLang_<?= $default_lang ?>').val(option_BaseOptions);
            <?php } ?>
        }

        var option_addon_img_html = "";
        if ($('#option_addon_img_title').length > 0 && option_Image != "" && option_Image != undefined) {
            var img_status = UrlExists('<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '?time=' + (new Date().getTime()));
            console.log(img_status);
            if (img_status == true) {
                var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '" target="_blank">View Image</a>)';
            } else {
                var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_upload_images_menu_item_options'] ?>' + option_Image + '" target="_blank">View Image</a>)';
            }

            $('#option_addon_img_title').html("<?= $langage_lbl_admin['LBL_OPTION_IMG'] ?>" + option_addon_img_html);
        }


        $('#item_option_topping_price').val(option_price);
        $('#option_addon_action').val("edit");
        $('#add_options_toppings_btn').html("<?= $langage_lbl_admin['LBL_Save'] ?>");
        $('#add_options_toppings .modal-body').animate({
            scrollTop: 0
        }, 'fast');
        $('#add_options_toppings').modal('show');
    }

    function edit_addon_fields(eid) {
        $('#option_addon_title').html("<?= $langage_lbl_admin['LBL_EDIT_ADDON_TOPPING'] ?>");
        $('#option_addon_type').val("addons");
        $('#option_addon_id').val(eid);
        $('#option_addon_img_title').html("<?= $langage_lbl_admin['LBL_ADDON_TOPPING_IMG'] ?>");


        $("#vMenuItemImage").val(null);

        var addon_values = $('.removeclassaddon' + eid).find('[name="addons_lang_all[]"]').text();
        var addon_price = $('.removeclassaddon' + eid).find('[name="AddonPrice[]"]').val();
        var addon_AddonOptions = $('.removeclassaddon' + eid).find('[name="AddonOptions[]"]').val();
        var option_Image = $('.removeclassaddon' + eid).find('[name="vMenuItemOptionImgName"]').val();


        $('#item_option_topping_price').prop('readonly', false);

        if (addon_values != "") {
            addon_values = JSON.parse(addon_values);
            $('[name^=tOptionNameLang_]').each(function () {
                var attr_name = $(this).attr('name');
                $(this).val(addon_values[attr_name]);
            });
        } else {
            <?php if ($EN_available) { ?>
            $('#tOptionNameLang_EN').val(addon_AddonOptions);
            <?php } else { ?>
            $('#tOptionNameLang_<?= $default_lang ?>').val(addon_AddonOptions);
            <?php } ?>
        }

        var option_addon_img_html = "";
        if ($('#option_addon_img_title').length > 0 && option_Image != "" && option_Image != undefined) {
            var img_status = UrlExists('<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '?time=' + (new Date().getTime()));
            console.log(img_status);
            if (img_status == true) {
                var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '" target="_blank">View Image</a>)';
            } else {
                var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_upload_images_menu_item_options'] ?>' + option_Image + '" target="_blank">View Image</a>)';
            }

            $('#option_addon_img_title').html("<?= $langage_lbl_admin['LBL_ADDON_TOPPING_IMG'] ?>" + option_addon_img_html);
        }

        $('#item_option_topping_price').val(addon_price);
        $('#option_addon_action').val("edit");
        $('#add_options_toppings_btn').html("<?= $langage_lbl_admin['LBL_Save'] ?>");
        $('#add_options_toppings .modal-body').animate({
            scrollTop: 0
        }, 'fast');
        $('#add_options_toppings').modal('show');
    }

    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "menu_item.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
    if (iServiceIdNew == '1') {
        $(".servicecatresponsive").show();
        $("#eFoodType").attr("required", true);
    } else {
        $('#eFoodType').removeAttr('required');
        $(".servicecatresponsive").hide();
    }
    $(document).ready(function () {

        //added by SP for required validation add in menu item image when recommended is on on 26-07-2019 start
        $("#eRecommended").change(function () {
            var recommended_sel = '';
            recommended_sel = $("input[name='eRecommended']:checked").val();
            if (recommended_sel == 'on') {
                $('input[name="vImage"]').attr("required", "required");
                $('#req_recommended').show();
            } else {
                $('input[name="vImage"]').removeAttr("required");
                $('input[name="vImage"]').parents('.row').removeClass('has-error');
                $('#vImage-error').remove();
                $('#req_recommended').hide();
            }
        });
        //added by SP for required validation add in menu item image when recommended is on on 26-07-2019 end
        if($("#iServiceId").val() != '1'){
            $(".servicecatresponsive").hide();
        }
        $("#iServiceId").change(function () {
            var iServiceid = $(this).val();
            if (iServiceid == '1') {
                $(".servicecatresponsive").show();
                $("#eFoodType").attr("required", true);
            } else {
                $('#eFoodType').removeAttr('required');
                $(".servicecatresponsive").hide();
            }
        });
        //Added By HJ On 19-12-2019 For Remove Required Image Validation If Image exists Start
        var oldImageName = $("#imgnameedit").val();
        if (oldImageName != "") {
            $('input[name="vImage"]').removeAttr("required");
            $('input[name="vImage"]').parents('.row').removeClass('has-error');
            $('#vImage-error').remove();
        }
        //Added By HJ On 19-12-2019 For Remove Required Image Validation If Image exists End
    });

    function updateOptionPrice() {
        //var serviceId = $("#iServiceId").val();
        var serviceId = $("input[name='iServiceId']").val();
        var basePrice = 0;

        if (serviceId >= 1) {
            basePrice = $("#fPrice").val();
        }

        $("#OptPrice").val(basePrice);
    }

    function editMenuItem(action) {
        $('#modal_action').html(action);
        $('#menu_item_Modal').modal('show');
    }

    function saveMenuItem() {
        if ($('#vItemType_<?= $default_lang ?>').val() == "") {
            $('#vItemType_<?= $default_lang ?>_error').show();
            $('#vItemType_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vItemType_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vItemType_Default').val($('#vItemType_<?= $default_lang ?>').val());
        $('#vItemType_Default').closest('.row').removeClass('has-error');
        $('#vItemType_Default-error').remove();
        $('#menu_item_Modal').modal('hide');
    }

    function editMenuItemDesc(action) {
        $('#modal_action').html(action);
        $('#item_desc_Modal').modal('show');
    }

    function saveMenuItemDesc() {
        if ($('#vItemDesc_<?= $default_lang ?>').val() == "") {
            $('#vItemDesc_<?= $default_lang ?>_error').show();
            $('#vItemDesc_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#vItemDesc_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }

        $('#vItemDesc_Default').val($('#vItemDesc_<?= $default_lang ?>').val());
        $('#item_desc_Modal').modal('hide');
    }

    function UrlExists(url) {
        var final_url = "";
        var http = new XMLHttpRequest();
        http.open('HEAD', url, false);
        http.setRequestHeader('Cache-Control', 'no-store');
        http.onload = function () {
            final_url = http.responseURL;
        };
        http.send();
        if (final_url != "" && final_url.includes("Page-Not-Found")) {
            return false;
        }

        return http.status == 200;
    }

    function checkItemCategoryServiceType(iFoodMenuId) {
        $(".servicecatresponsive").hide();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_check_item_category.php',
            'AJAX_DATA': {
                iFoodMenuId: iFoodMenuId
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                if (data > 1) {
                    $(".servicecatresponsive").hide();
                    $("#eFoodType").attr("required", false);
                } else {
                    $(".servicecatresponsive").show();
                    $("#eFoodType").attr("required", true);
                }
            } else {
                console.log(response.result);
            }
        });
    }
</script>
<?php if ($MODULES_OBJ->isEnableItemMultipleImageVideoUpload()) {
    echo scriptForPreViewImage();
} ?>
</body>
<!-- END BODY-->
</html>