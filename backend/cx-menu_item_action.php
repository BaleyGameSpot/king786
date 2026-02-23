<?php
    include_once('common.php');
 
    if($MODULES_OBJ->isEnableMultiOptionsToppings()) {
        include_once('cx-menu_item_multioptions_action.php');
        exit;
    }
    
    require_once(TPATH_CLASS . "/Imagecrop.class.php");
    
    $thumb = new thumbnail();
    
    $AUTH_OBJ->checkMemberAuthentication();
    
    $abc = 'company';
    
    $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    
    setRole($abc,$url);
    
    
    
    $script = 'MenuItems';
    
    $tbl_name = 'menu_items';
    
    $tbl_name1 = 'menuitem_options';
    
    if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol)) {
        $db_currency = $currencyData =  array();
        $currencyData['vName'] = $vSystemDefaultCurrencyName;
        $currencyData['vSymbol'] = $vSystemDefaultCurrencySymbol;
        $currencyData['Ratio'] = $vSystemDefaultCurrencyRatio;
        $db_currency[] = $currencyData;
    } else {
        $db_currency = $obj->MySQLSelect("select vName,vSymbol from currency where eDefault = 'Yes'");
    }
    $iCompanyId = $_SESSION['sess_iUserId'];
    
    
    if ( ! function_exists('check_diff')) {
        function check_diff($arr1, $arr2) {
        
            $check = (is_array($arr1) && scount($arr1) > 0) ? true : false;
        
            $result = ($check) ? ((is_array($arr2) && scount($arr2) > 0) ? $arr2 : array()) : array();
        
            if ($check) {
        
                foreach ($arr1 as $key => $value) {
        
                    if (isset($result[$key])) {
        
                        $result[$key] = array_diff($value, $result[$key]);
        
                    } else {
        
                        $result[$key] = $value;
        
                    }
        
                }
        
            }
        
            return $result;
        
        }
    }
    
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    
    $success = isset($_REQUEST['success']) ? $_REQUEST['success'] : $_SESSION['success'];
    
    $action = ($id != '') ? 'Edit' : 'Add';
    
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    
    $iFoodMenuId = isset($_POST['iFoodMenuId']) ? $_POST['iFoodMenuId'] : '0';
    
    $fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
    
    $iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
    
    $eFoodType = isset($_POST['eFoodType']) ? $_POST['eFoodType'] : '';
    
    $vHighlightName = isset($_POST['vHighlightName']) ? $_POST['vHighlightName'] : '';
    
    $fOfferAmt = isset($_POST['fOfferAmt']) ? $_POST['fOfferAmt'] : '';
    
    $eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'on';
    
    $eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
    
    $vSKU = isset($_POST['vSKU']) ? $_POST['vSKU'] : '';
    
    $eAvailable_check = isset($_POST['eAvailable']) ? $_POST['eAvailable'] : 'off';
    
    $eAvailable = ($eAvailable_check == 'on') ? 'Yes' : 'No';
      
    $eRecommended_check = isset($_POST['eRecommended']) ? $_POST['eRecommended'] : 'off';
    
    $eRecommended = ($eRecommended_check == 'on') ? 'Yes' : 'No';
    
    $prescription_required_chk = isset($_POST['prescription_required']) ? $_POST['prescription_required'] : 'off';
    
    $prescription_required = ($prescription_required_chk == 'on') ? 'Yes' : 'No';
    $oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
    
    $vImageTest = isset($_POST['vImageTest']) ? $_POST['vImageTest'] : '';
     
    $BaseOptions = isset($_POST['BaseOptions']) ? $_POST['BaseOptions'] : '';
    
    $OptPrice = isset($_POST['OptPrice']) ? $_POST['OptPrice'] : '';
    
    $optType = isset($_POST['optType']) ? $_POST['optType'] : '';
    
    $OptionId = isset($_POST['OptionId']) ? $_POST['OptionId'] : '';
    
    $eDefault = isset($_POST['eDefault']) ? $_POST['eDefault'] : '';
    
    $options_lang_all = isset($_POST['options_lang_all']) ? $_POST['options_lang_all'] : '';
    $vMenuItemOptionImage = isset($_POST['vMenuItemOptionImage']) ? $_POST['vMenuItemOptionImage'] : '';
    $menu_itemid = isset($_REQUEST['menu_itemid']) ? $_REQUEST['menu_itemid'] : "";
    
    foreach ($BaseOptions as $key => $value) {
    
        if (trim($value) != "") {
    
            $base_array[$key]['vOptionName'] = $value;
    
            $base_array[$key]['fPrice'] = $OptPrice[$key];
    
            $base_array[$key]['eOptionType'] = $optType[$key];
    
            $base_array[$key]['iOptionId'] = $OptionId[$key];
    
            $base_array[$key]['eDefault'] = $eDefault[$key];
    
            $base_array[$key]['eStatus'] = 'Active';
    
            $base_array[$key]['tOptionNameLang'] = addslashes(stripslashes($options_lang_all[$key]));
            $base_array[$key]['vImage'] = $vMenuItemOptionImage[$key];
    
        }
    
    }
    
    $AddonOptions = isset($_POST['AddonOptions']) ? $_POST['AddonOptions'] : '';
    
    $AddonPrice = isset($_POST['AddonPrice']) ? $_POST['AddonPrice'] : '';
    
    $optTypeaddon = isset($_POST['optTypeaddon']) ? $_POST['optTypeaddon'] : '';
    
    $addonId = isset($_POST['addonId']) ? $_POST['addonId'] : '';
    
    $addons_lang_all = isset($_POST['addons_lang_all']) ? $_POST['addons_lang_all'] : '';
    $vMenuItemAddonImage = isset($_POST['vMenuItemAddonImage']) ? $_POST['vMenuItemAddonImage'] : '';
    
    
    foreach ($AddonOptions as $key => $value) {
    
        $addon_array[$key]['vOptionName'] = $value;
    
        $addon_array[$key]['fPrice'] = $AddonPrice[$key];
    
        $addon_array[$key]['eOptionType'] = $optTypeaddon[$key];
    
        $addon_array[$key]['iOptionId'] = $addonId[$key];
    
        $addon_array[$key]['eStatus'] = 'Active';
    
        $addon_array[$key]['tOptionNameLang'] = addslashes(stripslashes($addons_lang_all[$key]));
        $addon_array[$key]['vImage'] = $vMenuItemAddonImage[$key];
    
    }
    
    $vTitle_store = $vItemDesc_store = array();
    
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
    
        $img_path = $tconfig["tsite_upload_images_menu_item_path"];
    
        $temp_gallery = $img_path . '/';
    
        $image_object = $_FILES['vImage']['tmp_name'];
    
        $image_name = $_FILES['vImage']['name'];
    
        $vImgName = "";
    
        if ($image_name != "") {
    
            $oldFilePath = $temp_gallery . $oldImage;
    
            if ($oldImage != '' && file_exists($oldFilePath)) {
    
                unlink($img_path . '/' . $oldImage);
    
            }
    
            $filecheck = basename($_FILES['vImage']['name']);
    
            $fileextarr = explode(".", $filecheck);
    
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
    
            $flag_error = 0;
            $tsite_upload_image_file_extensions = explode(",", $tconfig["tsite_upload_image_file_extensions"]);
            if (!in_array($ext, $tsite_upload_image_file_extensions)) {
    
                $flag_error = 1;
    
                $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
    
            }
    
            if ($flag_error == 1) {
    
                getPostForm($_POST, $var_msg, "menu_item_action.php?success=0&var_msg=" . $var_msg);
    
                exit;
    
            } else {
    
                $Photo_Gallery_folder = $img_path . '/';
    
                $img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
    
                $oldImage = $img1;
    
            }
    
        }
    
    
    
        if ($id != "") {
    
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
    
            } else if ($oldDisplayOrder < $iDisplayOrder) {
    
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
    
        } else {
    
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
    
        $editItemDesc = $where = "";
    
        for ($i = 0; $i < scount($vTitle_store); $i++) {
    
            $vValue = 'vItemType_' . $db_master[$i]['vCode'];
    
            $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
    
            // $strItemDesc = $obj->SqlEscapeString(htmlspecialchars_decode(html_entity_decode($_POST[$vItemDesc_store[$i]]), ENT_QUOTES));
    
            // $strItemTitle = $obj->SqlEscapeString(htmlspecialchars_decode(html_entity_decode($_POST[$vTitle_store[$i]]), ENT_QUOTES));
            $strItemDesc = $_POST[$vItemDesc_store[$i]];
    
            $strItemTitle = $_POST[$vTitle_store[$i]];
    
            //echo $vValue_desc;die;
    
            //$editItemDesc .= '`' . $vValue_desc . "`='" . $strItemDesc . "','`" . $vValue . "`='" . $strItemTitle . "',";
    
            $editItemDesc .= "`" . $vValue_desc . "`='" . $strItemDesc . "',`" . $vValue . "`='" . $strItemTitle . "',";
    
        }
    
        if ($editItemDesc != "") {
    
            $editItemDesc = trim($editItemDesc, ",");
    
        }
    
        $q = "INSERT INTO ";
    
        if ($id != '') {
    
            $q = "UPDATE ";
    
            $where = " WHERE `iMenuItemId` = '" . $id . "'";
    
        }
        $fPrice = $fPrice /$db_currency[0]['Ratio'];
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
               " . $editItemDesc . ""
    
                . $where;
        //`vSKU` = '" . trim($vSKU) . "',
      
        $obj->sql_query($query);
    
        $id = ($id != '') ? $id : $obj->GetInsertId();
    
        if (!empty($id)) {
            $uploadedImages = $_FILES['vMultiImage']['name'];
            if($ENABLE_ITEM_MULTIPLE_IMAGE_VIDEO_UPLOAD == 'Yes' && !empty($uploadedImages[0]))
            {
                $upload_img = $MENU_ITEM_MEDIA_OBJ->uploadImageVideo($_FILES, $tconfig["tsite_upload_images_menu_item_path"]);
                if(!empty($upload_img)){
                    foreach($upload_img as $img)
                    {   
                        $Data_update_option['vImage'] = $img;
                        $Data_update_option['iMenuItemId'] = $id;
                        if(isset($img) && !empty($img))
                        {
                            $menu_item_mediaid = $obj->MySQLQueryPerform('menu_item_media', $Data_update_option, 'insert');
                        }
                    }
                } else {
                    $_SESSION['success'] = "3";
                    $_SESSION['var_msg'] = $langage_lbl['LBL_FILE_NOT_SUPPORTED_ERROR'];
                    header("Location:menu_item_action.php?id=".$id."&action=edit");
                    // exit();
                }
                    
            }
    
            $baseOptionOldData = $obj->MySQLSelect("SELECT * FROM menuitem_options WHERE iMenuItemId ='" . $id . "' AND eOptionType='Options'");
    
            if (scount($baseOptionOldData) > 0) {
    
                $BaseOptionsDiffres = check_diff($baseOptionOldData, $base_array);
    
                foreach ($BaseOptionsDiffres as $k => $BaseOptionsVal) {
    
                    if (!empty($BaseOptionsVal['iOptionId'])) {
    
                        $newoptioidsArr[$k]['iOptionId'] = $BaseOptionsVal['iOptionId'];
    
                        $newoptioidsArr[$k]['iMenuItemId'] = $BaseOptionsVal['iMenuItemId'];
    
                    }
    
                }
    
                if (scount($newoptioidsArr) > 0) {
    
                    foreach ($newoptioidsArr as $ky => $optionidArr) {
    
                        $q = "UPDATE ";
    
                        $where = " WHERE `iOptionId` = '" . $optionidArr['iOptionId'] . "' AND `iMenuItemId` = '" . $optionidArr['iMenuItemId'] . "'";
    
                        $baseupdatequery = $q . " `" . $tbl_name1 . "` SET `eStatus` = 'Inactive'" . $where;
    
                        $obj->sql_query($baseupdatequery);
    
                    }
    
                }
    
    
                $dst_dir = $tconfig["tsite_upload_images_menu_item_options_path"];
                if (scount($base_array) > 0) {
    
                    foreach ($base_array as $key => $value) {
    
                        if ($value['iOptionId'] == '') {
    
                            $q = "INSERT INTO ";
    
                            $where = '';
    
                        } else {
    
                            $q = "UPDATE ";
    
                            $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
    
                        }
                        $img_update = "";
                        if(!empty($value['vImage'])) {
                            $img_update = " , `vImage` = '" . $value['vImage'] . "'";
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if(!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $value['fPrice'] = $value['fPrice'] /$db_currency[0]['Ratio'];
                        $basequery = $q . " `" . $tbl_name1 . "` SET
    
                            `iMenuItemId`= '" . $id . "',
    
                            `vOptionName` = '" . $value['vOptionName'] . "',
    
                            `fPrice` = '" . $value['fPrice'] . "',
    
                            `eDefault` = '" . $value['eDefault'] . "',
    
                            `eStatus` = '" . $value['eStatus'] . "',
    
                            `eOptionType` = '" . $value['eOptionType'] . "',
    
                            `tOptionNameLang` = '" . $value['tOptionNameLang'] . "'
                            $img_update "
    
                                . $where;
    
                        $obj->sql_query($basequery);
    
                    }
    
                }
    
            } else {
    
                if (scount($base_array) > 0) {
    
                    foreach ($base_array as $key => $value) {
                        $img_update = "";
                        if(!empty($value['vImage'])) {
                            $img_update = " , `vImage` = '" . $value['vImage'] . "'";
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if(!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $q = "INSERT INTO ";
    
                        $where = '';
                        $value['fPrice'] = $value['fPrice'] /$db_currency[0]['Ratio'];
                        $basequery = $q . " `" . $tbl_name1 . "` SET
    
                        `iMenuItemId`= '" . $id . "',
    
                        `vOptionName` = '" . $value['vOptionName'] . "',
    
                        `fPrice` = '" . $value['fPrice'] . "',
    
                        `eDefault` = '" . $value['eDefault'] . "',
    
                        `eStatus` = '" . $value['eStatus'] . "',
    
                        `eOptionType` = '" . $value['eOptionType'] . "',
    
                        `tOptionNameLang` = '" . $value['tOptionNameLang'] . "'
                        $img_update "
    
                            . $where;
    
                        $obj->sql_query($basequery);
    
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
    
                            `eStatus` = 'Inactive'"
    
                                . $where;
    
                        $obj->sql_query($addonupdatequery);
    
                    }
    
                }
    
    
    
                if (scount($addon_array) > 0) {
    
                    foreach ($addon_array as $key => $value) {
    
                        if ($value['iOptionId'] == '') {
    
                            $q = "INSERT INTO ";
    
                            $where = '';
    
                        } else {
    
                            $q = "UPDATE ";
    
                            $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
    
                        }
                        $img_update = "";
                        if(!empty($value['vImage'])) {
                            $img_update = " , `vImage` = '" . $value['vImage'] . "'";
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if(!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $value['fPrice'] = $value['fPrice'] /$db_currency[0]['Ratio'];
                        $addonquery = $q . " `" . $tbl_name1 . "` SET
    
                            `iMenuItemId`= '" . $id . "',
    
                            `vOptionName` = '" . $value['vOptionName'] . "',
    
                            `fPrice` = '" . $value['fPrice'] . "',
    
                            `eStatus` = '" . $value['eStatus'] . "',
    
                            `eOptionType` = '" . $value['eOptionType'] . "',
    
                            `tOptionNameLang` = '" . $value['tOptionNameLang'] . "'
                            $img_update "
    
                                . $where;
    
                        $obj->sql_query($addonquery);
    
                    }
    
                }
    
            } else {
    
                if (scount($addon_array) > 0) {
    
                    foreach ($addon_array as $key => $value) {
                        $img_update = "";
                        if(!empty($value['vImage'])) {
                            $img_update = " , `vImage` = '" . $value['vImage'] . "'";
                            $src_path = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/' . $value['vImage'];
                            $dst_path = $tconfig["tsite_upload_images_menu_item_options_path"] . $value['vImage'];
                            if(!is_dir($dst_dir)) {
                                mkdir($dst_dir, 0777);
                                chmod($dst_dir, 0777);
                            }
                            rename($src_path, $dst_path);
                        }
                        $q = "INSERT INTO ";
    
                        $where = '';
                        $value['fPrice'] = $value['fPrice'] /$db_currency[0]['Ratio'];
                        $addonquery = $q . " `" . $tbl_name1 . "` SET
    
                        `iMenuItemId`= '" . $id . "',
    
                        `vOptionName` = '" . $value['vOptionName'] . "',
    
                        `fPrice` = '" . $value['fPrice'] . "',
    
                        `eStatus` = '" . $value['eStatus'] . "',
    
                        `eOptionType` = '" . $value['eOptionType'] . "',
    
                        `tOptionNameLang` = '" . $value['tOptionNameLang'] . "'
                        $img_update "
    
                            . $where;
    
                        $obj->sql_query($addonquery);
    
                    }
    
                }
    
            }
    
        }
    
        //header("Location:menu_item_action.php?id=" . $id . '&success=1');
    
        if ($action == "Add") {
    
            $var_msg = $langage_lbl['LBL_ITEM_INSERTED_SUCCESSFULLY_TXT'];
    
        } else {
    
            $var_msg = $langage_lbl['LBL_ITEM_UPDATED_SUCCESSFULLY_TXT'];
    
        }
        
        if(!empty($menu_itemid)){
            header("Location:menuitems.php?success=1&menu_itemid=".$menu_itemid."&var_msg=" . $var_msg);
        } else {
            header("Location:menuitems.php?success=1&var_msg=" . $var_msg);
        }
    
        //header("Location:".$backlink);exit;
    
    }
    
    
    
    // for Edit
    
    if ($action == 'Edit') {
    
        $sql = "SELECT * FROM " . $tbl_name . " WHERE iMenuItemId = '" . $id . "'";
    
        $db_data = $obj->MySQLSelect($sql);
        
        $ssql = "";
        if(!$MODULES_OBJ->isEnableMultiOptionsToppings()) {
            $ssql = " AND iOptionsCategoryId = 0";
        }
        
        $sql1 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Options' AND eStatus = 'Active' $ssql ORDER BY eDefault";
    
        $db_optionsdata = $obj->MySQLSelect($sql1);
    
    
    
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
    
                    $fPrice = $value['fPrice']*$db_currency[0]['Ratio'];
    
                    $eAvailable = $value['eAvailable'];
    
                    $eStatus = $value['eStatus'];
    
                    $eRecommended = $value['eRecommended'];
    
                    $fOfferAmt = $value['fOfferAmt'];
    
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
    
    $sql_cat = "SELECT fm.*,c.vCompany,c.iServiceId FROM food_menu AS fm LEFT JOIN `company` as c ON c.iCompanyId=fm.iCompanyId WHERE fm.iCompanyId = $iCompanyId AND fm.eStatus = 'Active'";
    
    $db_menu = $obj->MySQLSelect($sql_cat);
    
    if (!empty($db_menu[0]['iServiceId'])) {
    
        $iServiceId = $db_menu[0]['iServiceId'];
    
        $sql = "SELECT prescription_required FROM `service_categories` WHERE iServiceId = '" . $iServiceId . "'";
    
        $db_prescription = $obj->MySQLSelect($sql);
    
        $prescriptionchkbox_required = $db_prescription[0]['prescription_required'];
    
    }
    
    $helpText = "This feature can be used when you want to provide different options for the same product. The price would be added to the base price.For E.G.: Regular Pizza, Double Cheese Pizza etc.";
    
    if ($iServiceId > 1) {
    
        $helpText = "This feature can be used when you want to provide different options for the same product.";
    
    }
    
    $sql = "SELECT lbl.vLabel,lbl.vCode,lbl.vValue FROM `language_master` as lm LEFT JOIN language_label as lbl ON lbl.vCode = lm.vCode WHERE lm.eStatus='Active' AND lbl.vLabel = 'LBL_REGULAR' ORDER BY `iDispOrder`";
    $db_lbl = $obj->MySQLSelect($sql);
    
    $lbl_regular = array();
    foreach ($db_lbl as $lbl_value) {
        $rkey = 'tOptionNameLang_'.$lbl_value['vCode'];
        $lbl_regular = array_merge($lbl_regular, array($rkey => $lbl_value['vValue']));
    }
    
    $lbl_regular_txt = $lbl_regular['tOptionNameLang_'.$default_lang];
    $lbl_regular_str = json_encode($lbl_regular);
    
    $company_data = $obj->MySQLSelect("SELECT iServiceId FROM company WHERE iCompanyId = $iCompanyId");
    $iServiceId = $company_data[0]['iServiceId']; 
    
    
    $sqlLang = "SELECT vCode FROM language_master WHERE eDefault = 'Yes'";
    $DefaultLanguageDB = $obj->MySQLSelect($sqlLang);
    $defaultLang = $DefaultLanguageDB[0]['vCode'];
    
    $EN_available = $LANG_OBJ->checkLanguageExist();
    $db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
    
    $menuItemFieldArr = array(
        array(
            'label'         => $langage_lbl['LBL_MENU_ITEM_FRONT'],
            'field_name'    => 'vItemType_',
            'modal_id'      => 'menu_item_Modal',
            'field_type'    => 'input_text'
        ),
        array(
            'label'         => $langage_lbl['LBL_MENU_ITEM_DESCRIPTION'],
            'field_name'    => 'vItemDesc_',
            'modal_id'      => 'item_desc_Modal',
            'field_type'    => 'input_text'
        )
    );
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?> <?= $action; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
            <link rel="stylesheet" href="assets/css/modal_alert.css"/>
        <style>
            .btn-convert-all{
                padding: 3px;
                color: #ffffff;
                background-color: #428bca;
                border-color: #357ebd;
                display: inline-block;
                margin-bottom: 0;
                font-size: 14px;
                font-weight: normal;
                line-height: 1.428571429;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                cursor: pointer;
                background-image: none;
                border: 1px solid transparent;
                border-radius: 4px;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                -o-user-select: none;
                user-select: none;
            }
            .loding-action {
                left: 0;
                margin: 0 auto;
                position: fixed;
                right: 0;
                top: 45%;
            }
            .option-addon-input {
                width: calc(86% - 300px);
                width: -moz-calc(86% - 300px);
                width: -o-calc(86% - 300px);
                width: -webkit-calc(50% - 91px);
                /* float: left; */
                margin-right: 20px;
                display: inline-block;
            }
            [dir=rtl] .option-addon-input {
                margin-left: 20px;
                margin-right: 0;
            }
            .option-addon-button {
                display: inline-block;
            }
            .option-addon-button button {
                padding: 17px;
            }
            .option-addon-button button span {
                font-size: 20px !important;
            }
            .loding-action {
                left: 0;
                margin: auto;
                position: fixed;
                right: 0;
                top: 0;
                bottom: 0;
                height: 100%;
                z-index: 999999999;
            }
            .loding-action div {
                left: 50%;
                position: absolute;
                top: 50%;
                transform: translate(-50%, -50%);
            }
            .text-danger {
                color: #ff0000;
                font-size: 14px;
                margin: 5px 0 0 8px
            }
            .modal-body {
                max-height: calc(100vh - 300px);
                overflow-y: auto;
                margin-right: 0;
                overflow-x: hidden;
                padding: 30px
            }
            #add_options_toppings .model-footer, .lang-transalation-modal .model-footer {
                border-top: 1px solid #cccccc;
            }
            .modal-input {
                width: 48%;
                float: left;
                position: relative;
            }
            .lang-transalation-modal .modal-input {
                width: 100%
            }
            #add_options_toppings .newrow, .lang-transalation-modal .newrow {
                float: left;
                width: 100%;
            }
            #add_options_toppings .text-danger, .lang-transalation-modal .text-danger {
                color: #ff0000;
                font-size: 14px;
            }
            #add_options_toppings .custom-modal, .lang-transalation-modal .custom-modal {
                height: auto;
            }
            #add_options_toppings .modal-content, .lang-transalation-modal .modal-content {
                height: 100%;
            }
            .icon-plus-button:before {
                content: "\e90b";
                font-weight: bold;
            }
            @media screen and (max-width:590px) {
                .option-addon-input, .modal-input {
                    width:100%;
                    margin-bottom:10px;
                }
                .option-addon-button button:first-child {
                    margin-left: 0
                }
                #options_fields .form-group, #addon_fields .form-group {
                    display: block !important;
                }
            }
            #options_fields .form-group:last-child, #addon_fields .form-group:last-child {
                margin-bottom: 0
            }
            #add_options_toppings .button-block .gen-btn, .lang-transalation-modal .button-block .gen-btn {
                margin-bottom: 0
            }
            #options_fields .form-group, #addon_fields .form-group {
                display: flex;
            }
            .item-cat-button button {
                padding: 17px;
                margin-left: 15px;
            }
            .item-cat-button button span {
                font-size: 20px !important;
            }
            .general-form textarea[readonly] {
                background-color: rgb(235,235,228);
            }
            .readonly-custom {
                background-color: #ffffff !important;
            }
            
            .lang-transalation-modal .general-form .form-group:last-child {
                margin-bottom: 0
            }
        </style>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?=$langage_lbl['LBL_MENU_ITEM_FRONT']; ?></h1>
                        </div>
                        <div class="button-block end">
                            <a href="menuitems.php" class="gen-btn" ><?=$langage_lbl['LBL_BACK_To_Listing_WEB']; ?></a>
                        </div>
                    </div>
                </div>
            </section>
            <div class="profile-section">
                <div class="profile-section-inner">
                    <!-- login in page -->
                    <div class="food-action-page">
                        <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <?php } else if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                       
                        <?php } else if ($success == 3) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo  !empty($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : $_SESSION['var_msg']; ?>
                            </div><br/>
                        <?php } ?>
                        <div style="clear:both;"></div>
                        <form id="menuItem_form" name="menuItem_form" class="menuItemFormFront general-form" method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $id; ?>"/>
                            <input  type="hidden" name="oldImage" value="<?php echo $oldImage; ?>">
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="menuitems.php"/>
                            <div class="partation" >
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'] ?><span class="red"> *</span></strong>
                                    <select   name = 'iFoodMenuId' required onChange="changeDisplayOrder(this.value, '<?php echo $id; ?>'); <?php if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) { ?> checkItemCategoryServiceType(this.value); <?php } ?>" >
                                        <option value=""><?php echo ucwords($langage_lbl['LBL_SELECT_CATEGORY']); ?></option>
                                        <?php foreach ($db_menu as $dbmenu) { ?>
                                        <option value = "<?= $dbmenu['iFoodMenuId'] ?>" <?= ($dbmenu['iFoodMenuId'] == $iFoodMenuId) ? 'selected' : ''; ?> <?php if (scount($dbmenu['menuItems']) > 0) { ?> <?php } ?> ><?= $dbmenu['vMenu_' . $_SESSION['sess_lang']]; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_DISPLAY_ORDER_FRONT'] ?><span class="red"> *</span></strong>
                                    <span id="showDisplayOrder001">
                                        <?php if ($action == 'Add') { ?>
                                        <input type="hidden" name="total" value="<?php echo $count + 1; ?>" >
                                        <select name="iDisplayOrder" id="iDisplayOrder"  required>
                                            <?php for ($i = 1; $i <= $count + 1; $i++) { ?>
                                            <option value="<?php echo $i ?>" 
                                                <?php
                                                    if ($i == $count + 1)
                                                    
                                                        echo 'selected';
                                                    
                                                    ?>> <?php echo $i ?> </option>
                                            <?php } ?>
                                        </select>
                                        <?php }else { ?>
                                        <input type="hidden" name="total" value="<?php echo $iDisplayOrder; ?>">
                                        <select name="iDisplayOrder" id="iDisplayOrder"  required>
                                            <?php for ($i = 1; $i <= $count; $i++) { ?>
                                            <option value="<?php echo $i ?>"
                                                <?php
                                                    if ($i == $iDisplayOrder)
                                                    
                                                        echo 'selected';
                                                    
                                                    ?>
                                                > <?php echo $i ?> </option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                    </span>
                                </div>
                                <?php 
                                foreach ($menuItemFieldArr as $menuItemField) { 
                                    $fieldLabel = $menuItemField['label'];
                                    $fieldName = $menuItemField['field_name'];
                                    $modal_id = $menuItemField['modal_id'];
                                    $fieldType = $menuItemField['field_type'];
                                ?>
                                <?php if(scount($db_master) > 1) { ?>
                                <div class="half-column">
                                    <strong>&nbsp;</strong>
                                    <div class="form-group" <?php if($id != "") { ?> style="display: flex;" <?php } ?>>
                                        
                                        <?php if($fieldType == "input_text") { ?>
                                            <label><?php echo $fieldLabel;?> <?php if($fieldName != "vItemDesc_") { ?><span class="red"> *</span><?php } ?></label>
                                            <input type="text" id="<?= $fieldName ?>Default" value="<?= $arrLang[$fieldName.$default_lang]; ?>" data-originalvalue="<?= $arrLang[$fieldName.$default_lang]; ?>" readonly="readonly" class="<?= ($id == "") ?  'readonly-custom' : '' ?>" <?php if($id == "") { ?> onclick="editItemDetails('Add', '<?= $modal_id ?>')" <?php } ?> <?php if($fieldName != "vItemDesc_") { ?> required="required" <?php } ?>> 
                                        <?php } else { ?>
                                            <label><?php echo $fieldLabel;?></label>
                                            <textarea id="<?= $fieldName; ?>Default" readonly="readonly" class="<?= ($id == "") ?  'readonly-custom' : '' ?>" <?php if($id == "") { ?> onclick="editItemDetails('Add', '<?= $modal_id ?>')" <?php } ?> data-originalvalue="<?= $arrLang[$fieldName.$default_lang]; ?>"><?= $arrLang[$fieldName.$default_lang]; ?></textarea>
                                        <?php } ?>
                                        <?php if($id != "") { ?>
                                        <div class="item-cat-button">
                                            <button type="button" class="gen-btn" onclick="editItemDetails('Edit', '<?= $modal_id ?>');"><span class="icon-edit" aria-hidden="true"></span></button>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    
                                </div>
                               
                                <div class="custom-modal-main in  fade lang-transalation-modal" id="<?= $modal_id ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="custom-modal">
                                        <div class="modal-content">
                                            <div class="model-header">
                                                <h4><span id="modal_action"></span> <?php echo $fieldLabel;?></h4>
                                                <i class="icon-close" data-dismiss="modal" onclick="resetToOriginalValue(this, '<?= $fieldName ?>')"></i>
                                            </div>
                                            <div class="modal-body">
                                                <div class="general-form">
                                                    <?php
                                                    if ($count_all > 0) 
                                                    {
                                                        for ($i = 0; $i < $count_all; $i++) 
                                                        {
                                                            $vCode = $db_master[$i]['vCode'];
                                                            $vTitle = $db_master[$i]['vTitle'];
                                                            $eDefault = $db_master[$i]['eDefault'];
                                                    
                                                            $vValue = $fieldName . $vCode;
                                                            $$vValue = $arrLang[$fieldName . $vCode];
                                                    
                                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                            $vCodeDefault = $default_lang;
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    $vCodeDefault = 'EN';
                                                                }
                                                                else {
                                                                    $vCodeDefault = $defaultLang;
                                                                }
                                                            }
                                                    ?>
                                                        
                                                            <div class="form-group newrow">
                                                                <div class="modal-input">
                                                                    <label><?php echo $fieldLabel;?> (<?= $vTitle ?>)</label>
                                                                    <input type="text" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>">
                                                                    <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="form-group newrow">
                                                                        <button type="button" class="gen-btn" onclick="getAllLanguageCode('<?= $fieldName ?>', 'EN');" style="margin: 0"><?= $langage_lbl['LBL_CONVERT_ALL_TXT']; ?></button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $defaultLang) { ?>
                                                                    <div class="form-group newrow">
                                                                        <button type="button" class="gen-btn" onclick="getAllLanguageCode('<?= $fieldName ?>', '<?= $defaultLang ?>');" style="margin: 0"><?= $langage_lbl['LBL_CONVERT_ALL_TXT']; ?></button>
                                                                    </div>
                                                                <?php }
                                                                }
                                                            }
                                                            
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="model-footer">
                                                <div class="button-block">
                                                    <button type="button" class="gen-btn" onclick="saveItemDetails('<?= $fieldName ?>', '<?= $modal_id ?>')"><?= $langage_lbl['LBL_ADD']; ?></button>
                                                    <button type="button" class="gen-btn" data-dismiss="modal" onclick="resetToOriginalValue(this, '<?= $fieldName ?>')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <div class="half-column">
                                    <strong>&nbsp;</strong>
                                    <div class="form-group" <?php if($id != "") { ?> style="display: flex;" <?php } ?>>
                                        
                                        <?php if($fieldType == "input_text") { ?>
                                            <label><?php echo $fieldLabel;?> <span class="red"> *</span></label>
                                            <input type="text" id="<?= $fieldName.$default_lang ?>" name="<?= $fieldName.$default_lang ?>" value="<?= $arrLang[$fieldName.$default_lang]; ?>" required="required"> 
                                        <?php } else { ?>
                                            <label><?php echo $fieldLabel;?></label>
                                            <textarea id="<?= $fieldName.$default_lang; ?>" name="<?= $fieldName.$default_lang; ?>" class=""><?= $arrLang[$fieldName.$default_lang]; ?></textarea>
                                        <?php } ?>
                                    </div>
                                    
                                </div>
                                <?php } } ?>
                                <?php if(!$MODULES_OBJ->isEnableItemMultipleImageVideoUpload()) { ?>
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_MENU_ITEM_IMAGE'] ?><span class="red" id="req_recommended"> *</span></strong>
                                    <div class="imageupload">
                                        <div class="file-tab">
                                            <span id="single_img001">
                                            <?php
                                                $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $oldImage;
                                                
                                                $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $oldImage;
                                                
                                                if ($oldImage != "" && file_exists($imgpth)) {
                                                
                                                    ?>
                                            <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 250px; max-height: 250px">
                                            <?php } ?>
                                            </span>
                                            <div>
                                                <input type="hidden" name="vImageTest" value="" >
                                                <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                                <div class="fileUploading" filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                    <input name="vImage" onchange="preview_mainImg(event);" type="file">
                                                </div>
                                                <small class="notes"><?= $langage_lbl['LBL_MENU_ITEM_IMAGE_NOTE'] ?></small>
                                                <!--added by SP for required validation add in menu item image when recommended is on on 26-07-2019 -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php if($MODULES_OBJ->isEnableRequireMenuItemSKU()) { ?>
                                <div class="form-group half">
                                    <strong>&nbsp;</strong>
                                    <div class="form-group">
                                        <label><?php echo $langage_lbl['LBL_MENU_ITEM_SKU_CODE_TXT'] ?> <?= $MODULES_OBJ->isEnableRequireMenuItemSKU() ? '<span class="red"> *</span>' : "" ?></label>
                                        <input type="text" name="vSKU" id="vSKU" value="<?= $vSKU; ?>" <?= $MODULES_OBJ->isEnableRequireMenuItemSKU() ? "required" : "" ?>>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="half-column">
                                    <div class="form-group">
                                        <label><?php echo $langage_lbl['LBL_PRICE_FOR_MENU_ITEM'] ?> (<?= $langage_lbl['LBL_IN']." ".$db_currency[0]['vName'] ?>) <span class="red"> *</span></label>
                                        <input type="text" onkeyup="updateOptionPrice();"  name="fPrice"  id="fPrice" value="<?= $fPrice; ?>" required>
                                    </div>
                                    <small><strong><?php echo $langage_lbl['LBL_NOTE_FRONT'] ?></strong> <?php echo $langage_lbl['LBL_NOTE_FOR_PRICE_MENU_ITEM'] ?> </small>
                                </div>
                                <div class="form-group half" >
                                    <strong><?php echo $langage_lbl['LBL_OFFER_AMOUNT_MENU_ITEM'] ?>(%) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set Offer amount on an item, if you want to show discounted/strikeout amount. E.g If Item Price is $100 but you want to sell it for $80, then set Offer Amount = 20%, hence the final price of this item is $80'></i></strong>
                                    <input type="text"  name="fOfferAmt"  id="fOfferAmt" value="<?= $fOfferAmt; ?>" />
                                    <small class="notes">
                                    <span><?php echo $langage_lbl['LBL_NOTE_FRONT'] ?></span> <?php echo $langage_lbl['LBL_DISCOUNT_NOTE'] ?>
                                    </small>
                                </div>
                                <div class="form-group half media_full">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5><b><?php echo $langage_lbl['LBL_OPTIONS_MENU_ITEM'] ?></b> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='<?= $helpText; ?>'></i></h5>
                                                </div>
                                                <div class="col-lg-6 text-right"><i onclick="options_fields();"> <span class="icon-plus-button" aria-hidden="true"></span> </i></div>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 25px;">
                                            <div id="options_fields">
                                                <?php
                                                    if (scount($db_optionsdata) > 0) {
                                                    
                                                        $opt = 0;
                                                    
                                                        foreach ($db_optionsdata as $k => $option) {
                                                    
                                                            $opt++;
                                                            $option['fPrice'] = $option['fPrice'] *$db_currency[0]['Ratio'];
                                                    
                                                    if(isset($option['tOptionNameLang']) && !empty($option['tOptionNameLang']))
                                                    {
                                                        $tOptionNameLang = json_decode($option['tOptionNameLang'], true);
                                                        if(isset($tOptionNameLang['tOptionNameLang_'.$_SESSION['sess_lang']]) && !empty($tOptionNameLang['tOptionNameLang_'.$_SESSION['sess_lang']]))
                                                        {
                                                            $option['vOptionName'] = $tOptionNameLang['tOptionNameLang_'.$_SESSION['sess_lang']];    
                                                        }
                                                    }
                                                            ?>
                                                <?php if ($option['eDefault'] == 'Yes') { ?>
                                                <div class="form-group eDefault">
                                                    <div class="option-addon-input">
                                                        <input type="text"  id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" readonly>
                                                    </div>
                                                    <div class="option-addon-input">
                                                        <input type="text"  id="OptPrice" name="OptPrice[]"  value="<?= $option['fPrice'] ?>" placeholder="Price" readonly required="required">
                                                        <input type="hidden" name="optType[]" value="Options" />
                                                        <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>" /><input type="hidden" name="eDefault[]" value="Yes"/>
                                                        <textarea name="options_lang_all[]" style="display: none;"><?= preg_replace('/"+/', '"', $option['tOptionNameLang']) ?></textarea>
                                                        <input type="hidden" name="vMenuItemOptionImage[]" value="">
                                                        <input type="hidden" name="vMenuItemOptionImgName" value="<?= $option['vImage'] ?>">
                                                    </div>
                                                    <div class="option-addon-button">
                                                        <button type="button" class="gen-btn" onclick="edit_options_fields(0,1);"><span class="icon-edit" aria-hidden="true"></span></button>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                                <?php } else { ?>
                                                <div class="form-group removeclass<?= $opt ?>">
                                                    <div class="option-addon-input">
                                                        <input type="text"  id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" readonly>
                                                    </div>
                                                    <div class="option-addon-input">
                                                        <input type="text"  id="OptPrice" name="OptPrice[]" required="required" value="<?= $option['fPrice'] ?>" placeholder="Price" readonly>
                                                        <input type="hidden" name="optType[]" value="Options" />
                                                        <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>" /><input type="hidden" name="eDefault[]" value="No"/>
                                                        <textarea name="options_lang_all[]" style="display: none;"><?= trim($option['tOptionNameLang'], '"') ?></textarea>
                                                        <input type="hidden" name="vMenuItemOptionImage[]" value="">
                                                        <input type="hidden" name="vMenuItemOptionImgName" value="<?= $option['vImage'] ?>">
                                                    </div>
                                                    <div class="option-addon-button">
                                                        <button type="button" class="gen-btn" onclick="edit_options_fields('<?= $opt ?>');"><span class="icon-edit" aria-hidden="true"></span></button><button type="button" class="gen-btn" onclick="remove_options_fields('<?= $opt ?>');"><span class="icon-cancel" aria-hidden="true"></span></button>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                                <?php
                                                    }
                                                    
                                                    }
                                                    
                                                    }
                                                    
                                                    ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group half media_full">
                                    <div class="panel panel-default" <?php if ($iServiceId != '1') { ?> style="display:none;" <?php } ?>>
                                        <div class="panel-heading">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5><b><?php echo $langage_lbl['LBL_ADDON_FRONT'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Addon/Topping Price will be additional amount which will added in base price'></i></b></h5>
                                                </div>
                                                <div class="col-lg-6 text-right"><i onclick="addon_fields();"> <span class="icon-plus-button" aria-hidden="true"></span> </i></div>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 25px;">
                                            <div id="addon_fields">
                                                <?php
                                                    if (scount($db_addonsdata) > 0) {
                                                    
                                                        $a = 0;
                                                    
                                                        foreach ($db_addonsdata as $k => $addon) {
                                                            
                                                            $a++;
                                                            $addon['fPrice'] = $addon['fPrice']*$db_currency[0]['Ratio'];
                                                    if(isset($addon['tOptionNameLang']) && !empty($addon['tOptionNameLang']))
                                                    {
                                                        $tOptionNameLang = json_decode($addon['tOptionNameLang'], true);
                                                        if(isset($tOptionNameLang['tOptionNameLang_'.$_SESSION['sess_lang']]) && !empty($tOptionNameLang['tOptionNameLang_'.$_SESSION['sess_lang']]))
                                                        {
                                                            $addon['vOptionName'] = $tOptionNameLang['tOptionNameLang_'.$_SESSION['sess_lang']];    
                                                        }
                                                    }
                                                            ?>
                                                <div class="form-group removeclassaddon<?= $a ?>">
                                                    <div class="option-addon-input">
                                                        <input type="text"  id="AddonOptions" name="AddonOptions[]" value="<?= $addon['vOptionName'] ?>" placeholder="Topping Name" required readonly>
                                                    </div>
                                                    <div class="option-addon-input">
                                                        <input type="text"  id="AddonPrice" name="AddonPrice[]" value="<?= $addon['fPrice'] ?>" placeholder="Price" required readonly>
                                                        <input type="hidden" name="optTypeaddon[]" value="Addon" />
                                                        <input type="hidden" name="addonId[]" value="<?= $addon['iOptionId'] ?>" />
                                                        <textarea name="addons_lang_all[]" style="display: none;"><?= trim($addon['tOptionNameLang'], '"') ?></textarea>
                                                        <input type="hidden" name="vMenuItemAddonImage[]" value="">
                                                        <input type="hidden" name="vMenuItemOptionImgName" value="<?= $addon['vImage'] ?>">
                                                    </div>
                                                    <div class="option-addon-button">
                                                        <button type="button" class="gen-btn" onclick="edit_addon_fields('<?= $a ?>');"><span class="icon-edit" aria-hidden="true"></span></button>
                                                        <button type="button" class="gen-btn" onclick="remove_addon_fields('<?= $a ?>');"><span class="icon-cancel" aria-hidden="true"></span></button>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                                <?php
                                                    }
                                                    
                                                    }
                                                    
                                                    ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group half foodType" <?php if ($iServiceId != '1') { ?> style="display:none;" <?php } ?>>
                                    <strong><?= $langage_lbl['LBL_FOOD_TYPE'] ?><span class="red">*</span></strong>
                                    <select name="eFoodType"  id="eFoodType" <?php if ($iServiceId == '1') { ?> required <?php } ?>>
                                        <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                        <option value="Veg" <?php
                                            if ($eFoodType == 'Veg') {
                                            
                                                echo 'selected';
                                            
                                            }
                                            
                                            ?>><?= $langage_lbl['LBL_VEG_FOOD'] ?></option>
                                        <option value="NonVeg" <?php
                                            if ($eFoodType == 'NonVeg') {
                                            
                                                echo 'selected';
                                            
                                            }
                                            
                                            ?>><?= $langage_lbl['LBL_NON_VEG_FOOD'] ?></option>
                                    </select>
                                </div>
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_ITEM_TAG_NAME'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Set the tag name to this item. Like, Best Seller, Most Popular"></i></strong>
                                    <select  name="vHighlightName"  id="vHighlightName">
                                        <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                        <option value="LBL_BESTSELLER" <?php
                                            if ($vHighlightName == 'LBL_BESTSELLER') {
                                            
                                                echo 'selected';
                                            
                                            }
                                            
                                            ?>><?php echo $langage_lbl['LBL_BESTSELLER'] ?></option>
                                        <option value="LBL_NEWLY_ADDED" <?php
                                            if ($vHighlightName == 'LBL_NEWLY_ADDED') {
                                            
                                                echo 'selected';
                                            
                                            }
                                            
                                            ?>><?php echo $langage_lbl['LBL_NEWLY_ADDED'] ?></option>
                                        <option value="LBL_PROMOTED" <?php
                                            if ($vHighlightName == 'LBL_PROMOTED') {
                                            
                                                echo 'selected';
                                            
                                            }
                                            
                                            ?>><?php echo $langage_lbl['LBL_PROMOTED'] ?></option>
                                    </select>
                                </div>
                                <div class="half-column" style="margin-bottom:20px">
                                    <div class="toggle-list-inner">
                                        <div class="toggle-combo" style="border-bottom: 0">
                                            <label><?php echo $langage_lbl['LBL_ITEM_IN_STOCK_WEB'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="If this item is set On by the restaurant then it will be available for user\'s to order it, Set it off when the item is out of stock"></i></label>
                                            <span class="toggle-switch">
                                            <input type="checkbox" name="eAvailable" <?= ($id != '' && $eAvailable == 'No') ? '' : 'checked'; ?> id="eAvailable" />
                                            <span class="toggle-base"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="half-column" style="margin-bottom:20px">
                                    <div class="toggle-list-inner">
                                        <div class="toggle-combo" style="border-bottom: 0">
                                            <label><?php echo $langage_lbl['LBL_IS_ITEM_RECOMMENDED'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Suggest the user's to order this item. The recommended items will be highlighted in the user app with the image and display at the top section"></i></label>
                                            <span class="toggle-switch">
                                            <input type="checkbox" name="eRecommended" <?= ($id != '' && $eRecommended == 'No') ? '' : 'checked'; ?> id="eRecommended" />
                                            <span class="toggle-base"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    if ($id == "" && $prescription_required == "No") {
                                    
                                        $checked_prescription = "";
                                    
                                    } else if ($id != "" && $prescription_required == "No") {
                                    
                                        $checked_prescription = "";
                                    
                                    } else if ($prescription_required == "Yes") {
                                    
                                        $checked_prescription = "checked";
                                    
                                    }
                                    
                                    ?>
                                <div class="half-column" style="display:<?php if ($prescriptionchkbox_required == 'Yes') { ?>block<?php } else { ?>none<?php } ?>">
                                    <div class="toggle-list-inner">
                                        <div class="toggle-combo">
                                            <label><?php echo $langage_lbl['LBL_IS_PRESCRIPTION_REQUIRED'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Suggest the user's to order this item. The recommended items will be highlighted in the user app with the image and display at the top section"></i></label>
                                            <span class="toggle-switch">
                                            <input type="checkbox" name="prescription_required" <?php echo $checked_prescription; ?> id="prescription_required" />
                                            <span class="toggle-base"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php if($ENABLE_ITEM_MULTIPLE_IMAGE_VIDEO_UPLOAD == 'Yes')
                                { ?>
                                    <div class="form-group full  item-multiple-banner">
                                    <div class="panel-heading">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <h5><b id="manage_option_title">Item Images/Videos </b><span class="red">*</span></h5>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <?php echo  $MENU_ITEM_MEDIA_OBJ->multiImageHTMl('',$id); ?>
                                        <?php if ($MODULES_OBJ->isEnableItemMultipleImageVideoUpload()) {
                                            echo scriptForPreViewImage();
                                        } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                                if($action == "Add") {
                                    $actionbtn = $langage_lbl['LBL_ACTION_ADD'];
                                } else {
                                    $actionbtn = $langage_lbl['LBL_EDIT'];
                                }
                                ?>
                            <input type="submit" class="gen-btn item-submittion" name="btnsubmit" id="btnsubmit" value="<?php echo $langage_lbl['LBL_Save']; ?>" >
                        </form>
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!-- footer part -->
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">
                <img src="default.gif">   
                <p></p>
                <span>Language Translation is in Process. Please Wait...</span>
            </div>
        </div>
        <div class="custom-modal-main in  fade" id="add_options_toppings" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="custom-modal">
                <div class="modal-content">
                    <div class="model-header">
                        <h4><span id="option_addon_title"></span></h4>
                        <i class="icon-close" data-dismiss="modal"></i>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="option_addon_type" id="option_addon_type">
                        <input type="hidden" name="option_addon_action" id="option_addon_action">
                        <input type="hidden" name="option_addon_id" id="option_addon_id">
                        <div class="general-form">
                            <?php
                                if(scount($db_master) > 1) 
                                {
                                    for ($i = 0; $i < $count_all; $i++) 
                                    {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];
                                
                                        $vValue = 'tOptionNameLang_' . $vCode;
                                        $vValueName = 'tOptionName_' . $vCode;
                                
                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                
                                if($EN_available) {
                                    if($vCode == "EN") {
                                        $class_option = 'class="modal-input" style="margin-right: 20px"';
                                    } else {
                                        $class_option = 'class="modal-input-full"';
                                    }
                                } else {
                                    if($vCode == $default_lang) {
                                        $class_option = 'class="modal-input" style="margin-right: 20px"';
                                    } else {
                                        $class_option = 'class="modal-input-full"';
                                    } 
                                }
                            ?>
                            <div class="form-group newrow">
                                <div <?= $class_option ?>>
                                    <label>Option Name (<?= $vTitle ?>)</label>
                                    <input type="text" name="<?= $vValue; ?>" id="<?= $vValue; ?>" <?= $required; ?>>
                                    <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                </div>
                                <?php
                                    if (scount($db_master) > 1) {
                                        if($EN_available) {
                                            if($vCode == "EN") { ?>
                                            <div class="modal-input">
                                                <label>Option Price  ( Price In <?= $db_currency[0]['vName'] ?>)</label>
                                                <input type="text" name="item_option_topping_price" id="item_option_topping_price">
                                                <div class="text-danger" id="item_option_topping_price_error" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                            </div>
                                        <?php }
                                        } else { 
                                            if($vCode == $defaultLang) { ?>
                                            <div class="modal-input">
                                                <label>Option Price  ( Price In <?= $db_currency[0]['vName'] ?>)</label>
                                                <input type="text" name="item_option_topping_price" id="item_option_topping_price">
                                                <div class="text-danger" id="item_option_topping_price_error" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                            </div>
                                        <?php }
                                        }
                                    }
                                ?>
                            </div>
                            <?php
                                if (scount($db_master) > 1) {
                                    if($EN_available) {
                                        if($vCode == "EN") { ?>
                                        <div class="form-group newrow">
                                            <button type="button" class="gen-btn" onclick="getAllLanguageCode('tOptionNameLang_', 'EN');" style="margin: 0"><?= $langage_lbl['LBL_CONVERT_ALL_TXT']; ?></button>
                                        </div>
                                        <?php if(strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                                            <div class="form-group newrow" id="extra_img_upload">
                                                <div class="modal-input-full">
                                                    <div id="option_addon_img_title"></div>
                                                </div>
                                                <div class="modal-input-full">
                                                    <div class="imageupload">
                                                        <div class="file-tab">
                                                            <div>
                                                                <div class="fileUploading" filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                                    <input id="vMenuItemImage" type="file">
                                                                </div>
                                                                <b><?= $langage_lbl['LBL_KIOSK_NOTE'] ?></b>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        }
                                    } else { 
                                        if($vCode == $defaultLang) { ?>
                                        <div class="form-group newrow">
                                            <button type="button" class="gen-btn" onclick="getAllLanguageCode('tOptionNameLang_', '<?= $default_lang ?>');" style="margin: 0"><?= $langage_lbl['LBL_CONVERT_ALL_TXT']; ?></button>
                                        </div>
                                        <?php if(strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                                            <div class="form-group newrow" id="extra_img_upload">
                                                <div class="modal-input-full">
                                                    <div id="option_addon_img_title"></div>
                                                </div>
                                                <div class="modal-input-full">
                                                    <div class="imageupload">
                                                        <div class="file-tab">
                                                            <div>
                                                                <div class="fileUploading" filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                                    <input id="vMenuItemImage" type="file">
                                                                </div>
                                                                <b><?= $langage_lbl['LBL_KIOSK_NOTE'] ?></b>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        }
                                    }
                                }
                            ?>
                            
                            <?php } } else { ?>
                            <div class="form-group newrow">
                                <div class="modal-input" style="margin-right: 20px">
                                    <label>Option Name (<?= $db_master[0]['vTitle'] ?>)</label>
                                    <input type="text" name="tOptionNameLang_<?= $default_lang; ?>" id="tOptionNameLang_<?= $default_lang; ?>">
                                    <div class="text-danger" id="<?= 'tOptionNameLang_<?= $default_lang; ?>_error'; ?>" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                </div>
                                <div class="modal-input">
                                    <label>Option Price (Price In <?= $db_currency[0]['vName'] ?>)</label>
                                    <input type="text" name="item_option_topping_price" id="item_option_topping_price">
                                    <div class="text-danger" id="item_option_topping_price_error" style="display: none;"><?= $langage_lbl['LBL_REQUIRED'] ?></div>
                                </div>
                            </div>
                            <?php if(strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES") { ?>
                                <div class="form-group newrow" id="extra_img_upload">
                                    <div class="modal-input-full">
                                        <div id="option_addon_img_title"></div>
                                    </div>
                                    <div class="modal-input-full">
                                        <div class="imageupload">
                                            <div class="file-tab">
                                                <div>
                                                    <div class="fileUploading" filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                        <input id="vMenuItemImage" type="file">
                                                    </div>
                                                    <b><?= $langage_lbl['LBL_KIOSK_NOTE'] ?></b>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                                }
                                ?>
                        </div>
                    </div>
                    <div class="model-footer">
                        <div class="button-block">
                            <button type="button" class="gen-btn" id="add_options_toppings_btn"><?= $langage_lbl['LBL_ADD']; ?></button>
                            <button type="button" class="gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once('footer/footer_home.php'); ?>
        <!-- footer part end -->
        <!-- End:contact page-->
        <div style="clear:both;"></div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php
            include_once('top/footer_script.php');
            
            $lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
            
            ?>
        <style>
            span.help-block{
            margin:0;
            padding: 0;
            }
            .gen-btn.item-submittion {
            margin: 10px 0 0 0;
            }
        </style>
        
        <?php if ($lang != 'en') { ?>
        <?php //include_once('otherlang_validation.php');?>
        <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
        <?php } ?>
<?php
if(isset($_SESSION['success'])){
    $_SESSION['success'] = "";
}
if(isset($_SESSION['var_msg'])){
    $_SESSION['var_msg'] = "";
}
?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <link href="assets/css/imageUpload/bootstrap-imageupload.css" rel="stylesheet">
        <script src="assets/js/modal_alert.js"></script>
        <script>
            var myVar;
            function changeDisplayOrder(foodId, menuId, parentId)
            
            {
            
                var itemParentId = '';
            
                if (parentId != '') {
            
                    itemParentId = parentId
            
                }
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_display_order.php',
                    'AJAX_DATA': {iFoodMenuId: foodId, page: 'items', iMenuItemId: menuId},
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $("#showDisplayOrder001").html('');
            
                        $("#showDisplayOrder001").html(data); 
                    }
                    else {
                        console.log(response.result);
                    }
                });
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_display_order.php',
                    'AJAX_DATA': {method: 'getParentItems', page: 'items', iFoodMenuId: foodId, itemParentId: itemParentId},
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $("#iParentId").html(data);
                    }
                    else {
                        console.log(response.result);
                    }
                });
            
            }
            
            $(document).ready(function () {
            
                changeDisplayOrder('<?php echo $iFoodMenuId; ?>', '<?php echo $id; ?>', '<?php echo $menuiParentId; ?>');
                <?php if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) { ?>
                    checkItemCategoryServiceType('<?php echo $iFoodMenuId; ?>');
                <?php } ?>


                resizeImage("setImageRatio1JS", "attr-image-src", "imageSrc");


            });
            
            function preview_mainImg(event)
            
            {
            
                $("#single_img001").html('');
            
                $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
            
                $(".changeImg001").text('Change');
            
                $(".remove_main").show();
            
            }
            
            
            
            <?php if (scount($db_optionsdata) > 0) { ?>
            
                        var optionid = '<?= scount($db_optionsdata) ?>';
            
            <?php } else { ?>
            
                        var optionid = 0;
            
            <?php } ?>
            
            
            
            function options_fields() {
                $('#option_addon_title').html("<?= $langage_lbl['LBL_ADD_OPTION'] ?>");
                $('#option_addon_type').val("options");
                $('#option_addon_action').val("add");
                $('#add_options_toppings_btn').html("<?= $langage_lbl['LBL_ADD'] ?>");
                $('#option_addon_img_title').html("<?= $langage_lbl['LBL_OPTION_IMG'] ?>");
                
                $('[name^=tOptionNameLang_], #item_option_topping_price').val("");
                $('#item_option_topping_price').prop('readonly', false);
                $("#vMenuItemImage").val(null);
                
                $('#add_options_toppings').modal('show'); 
                
                $('#add_options_toppings').addClass('active'); 
                general_label();
            }
            
            $('#add_options_toppings_btn').click(function() {
                <?php if($EN_available) { ?>
                if($('#tOptionNameLang_EN').val().trim() == "")
                {
                    $('#tOptionNameLang_EN_error').show();
                    $('#tOptionNameLang_EN').focus();
                    $('#tOptionNameLang_EN').val("");
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tOptionNameLang_EN_error').hide();
                    }, 5000);
                    return false;
                }
                <?php } else { ?>
                if($('#tOptionNameLang_<?= $default_lang ?>').val().trim() == "")
                {
                    $('#tOptionNameLang_<?= $default_lang ?>_error').show();
                    $('#tOptionNameLang_<?= $default_lang ?>').focus();
                    $('#tOptionNameLang_<?= $default_lang ?>').val("");
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tOptionNameLang_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }
                <?php } ?>
            
                if($('#item_option_topping_price').val() == "")
                {
                    $('#item_option_topping_price_error').show();
                    $('#item_option_topping_price').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#item_option_topping_price_error').hide();
                    }, 5000);
                    return false;
                }
            
                var serviceID = '<?= $iServiceId ?>';
                
                if($('#item_option_topping_price').val() == 0 && serviceID > 1)
                {
                    $('#item_option_topping_price_error').text('<?= $langage_lbl['LBL_TRGAMT_VALIDATION_MAX_FRONT'] ?>');
                    $('#item_option_topping_price_error').show();
                    $('#item_option_topping_price').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#item_option_topping_price_error').hide();
                        $('#item_option_topping_price_error').text('<?= $langage_lbl['LBL_REQUIRED'] ?>');
                    }, 5000);
                    return false;
                }
                jsonObj = {};
                $('[name^=tOptionNameLang_]').each(function() {
                    jsonObj[$(this).attr('name')] = $(this).val();
                });
            
                if($('#option_addon_action').val() == "add")
                {
                    if($('#option_addon_type').val() == "options")
                    {
                        options_fields_add(jsonObj);    
                    }
                    else {
                        addon_fields_add(jsonObj);
                    }
                }
                else {
                    var option_id = $('#option_addon_id').val();
            
                    if($('#option_addon_type').val() == "options")
                    {
                        if(option_id == 0 && serviceID == 1)
                        {
                            $('#options_fields').find('.eDefault').find('[name="BaseOptions[]"]').val(jsonObj.tOptionNameLang_<?= $default_lang ?>);
                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').text(JSON.stringify(jsonObj));
                            $('#options_fields').find('.eDefault').find('[name="OptPrice[]"]').val(0);
                        } 
                        else {
                            $('#options_fields').find('.removeclass'+option_id).find('[name="BaseOptions[]"]').val(jsonObj.tOptionNameLang_<?= $default_lang ?>);
                            $('#options_fields').find('.removeclass'+option_id).find('[name="options_lang_all[]"]').text(JSON.stringify(jsonObj));
                            $('#options_fields').find('.removeclass'+option_id).find('[name="OptPrice[]"]').val($('#item_option_topping_price').val());
                        }
            
                        if(serviceID > 1)
                        {
                            $('#fPrice').val($('#OptPrice').val());    
                        }
                    }
                    else {
                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="AddonOptions[]"]').val(jsonObj.tOptionNameLang_<?= $default_lang ?>);
                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="addons_lang_all[]"]').text(JSON.stringify(jsonObj));
                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="AddonPrice[]"]').val($('#item_option_topping_price').val());
                    }
                    var ENABLE_ORDER_FROM_STORE_KIOSK = '<?= ENABLE_ORDER_FROM_STORE_KIOSK ?>';
                    if(ENABLE_ORDER_FROM_STORE_KIOSK == "Yes" && $("#vMenuItemImage")[0].files.length > 0) {
                        
                        var files = $('#vMenuItemImage')[0].files[0];
                        var fd = new FormData();
                        fd.append('vImage', files);
                        
                        showLoader();
                        var ajaxData = {
                            'URL': '<?= $tconfig['tsite_url'] ?>ajax_upload_temp_image.php',
                            'AJAX_DATA': fd,
                            'REQUEST_DATA_TYPE': 'json',
                            'REQUEST_CONTENT_TYPE': false,
                            'REQUEST_PROCESS_DATA': false,
                        };
                        getDataFromAjaxCall(ajaxData, function(data) {
                            if(data.action == "1") {
                                var response = data.result;
                                if(response.Action == 1) {
                                    if($('#option_addon_type').val() == "options") {
                                        var img_input = $('<input>').attr({
                                            type: 'hidden',
                                            name: 'vMenuItemOptionImage[]',
                                            value: response.message
                                        });
                                        if(option_id == 0 && serviceID == 1)
                                        {
                                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.option-addon-input').find('[name="vMenuItemOptionImage[]"]').remove();
                                            $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.option-addon-input').append(img_input);
                                            $('#options_fields').find('.eDefault').find('[name="vMenuItemOptionImgName"]').val(response.message);
                                        } 
                                        else {
                                            $('#options_fields').find('.removeclass'+option_id).find('[name="options_lang_all[]"]').closest('.option-addon-input').find('[name="vMenuItemOptionImage[]"]').remove();
                                            $('#options_fields').find('.removeclass'+option_id).find('[name="options_lang_all[]"]').closest('.option-addon-input').append(img_input);
                                            $('#options_fields').find('.removeclass'+option_id).find('[name="vMenuItemOptionImgName"]').val(response.message);
                                        }
                                    }
                                    else {
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
                                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="addons_lang_all[]"]').closest('.option-addon-input').find('[name="vMenuItemAddonImage[]"]').remove();
                                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="addons_lang_all[]"]').closest('.option-addon-input').append(img_input);
                                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="addons_lang_all[]"]').closest('.option-addon-input').find('[name="vMenuItemOptionImgName"]').remove();
                                        $('#addon_fields').find('.removeclassaddon'+option_id).find('[name="addons_lang_all[]"]').closest('.option-addon-input').append(img_input_name);
                                    }
                                    hideLoader();
                                    $('#add_options_toppings').removeClass('active');  
                                }   
                            }
                            else {
                                alert("<?= $langage_lbl['LBL_TRY_AGAIN_LATER_TXT'] ?>");
                                hideLoader();
                                $('#add_options_toppings').removeClass('active');
                            }
                        });
                    }
                    else {
                        $('#add_options_toppings').removeClass('active');  
                    }
                }
            });
            
            $("#item_option_topping_price").on("keypress keyup blur paste keydown",function (event) {
                if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {
            
                } else {
                    event.preventDefault();
                }
                
                if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
                    event.preventDefault();
            });
            
            $(document).on("keypress keyup blur paste keydown", '[name="BaseOptions[]"], [name="OptPrice[]"], [name="AddonOptions[]"], [name="AddonPrice[]"]',function (event) {
                event.preventDefault();
            });
            
            function options_fields_add(options, eDefault = 0)
            {
                var container_div = document.getElementById('options_fields');
                var count = container_div.getElementsByTagName('div').length;
                var serviceId = '<?= $iServiceId ?>';
                var basePrice = 0;
                var baseOptionValue = "Regular";
            
                var serviceID = '<?= $iServiceId ?>';
            
                var item_default = "No";
            
                if(serviceID == 1 && $('#options_fields').find('div').length == 0)
                {
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
                    divtest1.setAttribute("class", "form-group eDefault");
                    divtest1.innerHTML = '<div class="option-addon-input"><input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValueDefault + '" placeholder="Option Name" readonly></div><div class="option-addon-input"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="0" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="Yes"/><textarea name="options_lang_all[]" style="display: none" data-static="yes">'+ item_options_default +'</textarea></div><div class="option-addon-button"><button class="gen-btn" type="button" onclick="edit_options_fields(0, 1);"><span class="icon-edit" aria-hidden="true"></span></button></div><div class="clear"></div>';
                    objTo.appendChild(divtest1);
            
                    var divtest = document.createElement("div");
                    divtest.setAttribute("class", "form-group removeclass" + optionid);
                    divtest.innerHTML = '<div class="option-addon-input"><input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValue + '" placeholder="Option Name" readonly></div><div class="option-addon-input"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="' + basePrice + '" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/><textarea name="options_lang_all[]" style="display: none">'+ item_options_all +'</textarea></div><div class="option-addon-button"><button class="gen-btn" type="button" onclick="edit_options_fields(' + optionid + ');"><span class="icon-edit" aria-hidden="true"></span></button><button class="gen-btn" type="button" onclick="remove_options_fields(' + optionid + ');"> <span class="icon-cancel" aria-hidden="true"></span></button></div><div class="clear"></div>';
            
                    objTo.appendChild(divtest);
                }
                else {
                    var divtest = document.createElement("div");
                    divtest.setAttribute("class", "form-group row removeclass" + optionid);
                    divtest.innerHTML = '<div class="option-addon-input"><input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="' + baseOptionValue + '" placeholder="Option Name" readonly></div><div class="option-addon-input"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="' + basePrice + '" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/><textarea name="options_lang_all[]" style="display: none">'+ item_options_all +'</textarea></div><div class="option-addon-button"><button class="gen-btn" type="button" onclick="edit_options_fields(' + optionid + ');"><span class="icon-edit" aria-hidden="true"></span></button><button class="gen-btn" type="button" onclick="remove_options_fields(' + optionid + ');"> <span class="icon-cancel" aria-hidden="true"></span></button></div><div class="clear"></div>';
            
                    objTo.appendChild(divtest);
                }
                
                if(serviceID > 1)
                {
                    $('#fPrice').val($('#OptPrice').val());    
                }
                var ENABLE_ORDER_FROM_STORE_KIOSK = '<?= ENABLE_ORDER_FROM_STORE_KIOSK ?>';
                if(ENABLE_ORDER_FROM_STORE_KIOSK == "Yes" && $("#vMenuItemImage")[0].files.length > 0) {
                    
                    var files = $('#vMenuItemImage')[0].files[0];
                    var fd = new FormData();
                    fd.append('vImage', files);
                    
                    showLoader();
                    var ajaxData = {
                        'URL': '<?= $tconfig['tsite_url'] ?>ajax_upload_temp_image.php',
                        'AJAX_DATA': fd,
                        'REQUEST_DATA_TYPE': 'json',
                        'REQUEST_CONTENT_TYPE': false,
                        'REQUEST_PROCESS_DATA': false,
                    };
                    getDataFromAjaxCall(ajaxData, function(data) {
                        if(data.action == "1") {
                            var response = data.result;
                            if(response.Action == 1) {
                                var img_input = $('<input>').attr({
                                    type: 'hidden',
                                    name: 'vMenuItemOptionImage[]',
                                    value: response.message
                                });
                                if(optionid == 0 && serviceID == 1)
                                {
                                    $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImage[]"]').remove();
                                    $('#options_fields').find('.eDefault').find('[name="options_lang_all[]"]').closest('.form-group').append(img_input);
                                    $('#options_fields').find('.eDefault').find('[name="vMenuItemOptionImgName"]').val(response.message);
                                } 
                                else {
                                    $('#options_fields').find('.removeclass'+optionid).find('[name="options_lang_all[]"]').closest('.form-group').find('[name="vMenuItemOptionImage[]"]').remove();
                                    $('#options_fields').find('.removeclass'+optionid).find('[name="options_lang_all[]"]').closest('.form-group').append(img_input);
                                    $('#options_fields').find('.removeclass'+optionid).find('[name="vMenuItemOptionImgName"]').val(response.message);
                                }
                                hideLoader();
                                $('#add_options_toppings').removeClass('active');   
                            }  
                        }
                        else {
                            alert("<?= $langage_lbl['LBL_TRY_AGAIN_LATER_TXT'] ?>");
                            hideLoader();
                            $('#add_options_toppings').removeClass('active');
                        }
                    });
                }
                else {
                    $('#add_options_toppings').removeClass('active');  
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
            }
            <?php if (scount($db_addonsdata) > 0) { ?>
                var addonid = '<?= scount($db_addonsdata) ?>';
            <?php } else { ?>
                var addonid = 0;
            <?php } ?>
            function addon_fields() {
                $('#option_addon_title').html("<?= $langage_lbl['LBL_ADD_ADDON_TOPPING'] ?>");
                $('#option_addon_type').val("addons");
                $('#option_addon_action').val("add");
                $('#add_options_toppings_btn').html("<?= $langage_lbl['LBL_ADD'] ?>");
                $('#option_addon_img_title').html("<?= $langage_lbl['LBL_ADDON_TOPPING_IMG'] ?>");
            
                $('[name^=tOptionNameLang_], #item_option_topping_price').val("");
            
                $("#add_options_toppings").find(".modal-body").scrollTop(0);
                $("#vMenuItemImage").val(null);
                $('#add_options_toppings').addClass('active');
                general_label();
            }
            
            function addon_fields_add(addon_toppings)
            {
                var item_addons = JSON.stringify(addon_toppings);
                
                var baseAddonValue = jsonObj.tOptionNameLang_<?= $default_lang ?>;
                var baseAddonPrice = $('#item_option_topping_price').val();
            
                addonid++;
                var objTo = document.getElementById('addon_fields');
                var divtest = document.createElement("div");
                divtest.setAttribute("class", "form-group removeclassaddon" + addonid);
                divtest.innerHTML = '<div class="option-addon-input"><input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="' + baseAddonValue + '" placeholder="Topping Name" required readonly></div><div class="option-addon-input"><input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="' + baseAddonPrice + '" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" required readonly><input type="hidden" name="addonId[]" value="" /><input type="hidden" name="optTypeaddon[]" value="Addon" /><textarea name="addons_lang_all[]" style="display: none">'+ item_addons +'</textarea></div><div class="option-addon-button"><button class="gen-btn" type="button" onclick="edit_addon_fields(' + addonid + ');" data-toggle="tooltip" data-original-title="Edit"><span class="icon-edit" aria-hidden="true"></span></button><button class="gen-btn" type="button" onclick="remove_addon_fields(' + addonid + ');"> <span class="icon-cancel" aria-hidden="true"></span></button></div><div class="clear"></div>';
            
                objTo.appendChild(divtest);
                var ENABLE_ORDER_FROM_STORE_KIOSK = '<?= ENABLE_ORDER_FROM_STORE_KIOSK ?>';
                if(ENABLE_ORDER_FROM_STORE_KIOSK == "Yes" && $("#vMenuItemImage")[0].files.length > 0) {
                    
                    var files = $('#vMenuItemImage')[0].files[0];
                    var fd = new FormData();
                    fd.append('vImage', files);
                    
                    showLoader();
                    var ajaxData = {
                        'URL': '<?= $tconfig['tsite_url'] ?>ajax_upload_temp_image.php',
                        'AJAX_DATA': fd,
                        'REQUEST_DATA_TYPE': 'json',
                        'REQUEST_CONTENT_TYPE': false,
                        'REQUEST_PROCESS_DATA': false,
                    };
                    getDataFromAjaxCall(ajaxData, function(data) {
                        if(data.action == "1") {
                            var response = data.result;
                            if(response.Action == 1) {
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
                                $('#addon_fields').find('.removeclassaddon'+addonid).find('[name="addons_lang_all[]"]').closest('.option-addon-input').find('[name="vMenuItemAddonImage[]"]').remove();
                                $('#addon_fields').find('.removeclassaddon'+addonid).find('[name="addons_lang_all[]"]').closest('.option-addon-input').append(img_input);
                                $('#addon_fields').find('.removeclassaddon'+addonid).find('[name="addons_lang_all[]"]').closest('.option-addon-input').find('[name="vMenuItemOptionImgName"]').remove();
                                $('#addon_fields').find('.removeclassaddon'+addonid).find('[name="addons_lang_all[]"]').closest('.option-addon-input').append(img_input_name);
                                hideLoader();
                                $('#add_options_toppings').removeClass('active');    
                            }
                        }
                        else {
                            alert("<?= $langage_lbl['LBL_TRY_AGAIN_LATER_TXT'] ?>");
                            hideLoader();
                            $('#add_options_toppings').removeClass('active');
                        }
                    });
                }
                else {
                    $('#add_options_toppings').removeClass('active');  
                }
            }
            function remove_addon_fields(rid) {
                $('.removeclassaddon' + rid).remove();
            }
            
            function edit_options_fields(eid, eDefault = 0)
            {
                $('#option_addon_title').html("<?= $langage_lbl['LBL_EDIT_OPTION'] ?>");
                $('#option_addon_type').val("options");
                $('#option_addon_id').val(eid);
                $('#option_addon_img_title').html("<?= $langage_lbl['LBL_OPTION_IMG'] ?>");    
            
                var option_values = $('.removeclass'+eid).find('[name="options_lang_all[]"]').text();
                var option_price = $('.removeclass'+eid).find('[name="OptPrice[]"]').val();
                var option_default = $('.removeclass'+eid).find('[name="eDefault[]"]').val();
                var option_default_value = $('.removeclass'+eid).find('[name="BaseOptions[]"]').val();
                var option_Image = $('.removeclass'+eid).find('[name="vMenuItemOptionImgName"]').val();
            
                $('#item_option_topping_price').prop('readonly', false);
                if(eDefault == 1)
                {
                    var option_values = $('.eDefault').find('[name="options_lang_all[]"]').text();
                    var option_Image = $('.eDefault').find('[name="vMenuItemOptionImgName"]').val();
                    if($('.eDefault').find('[name="options_lang_all[]"]').attr('data-static') === undefined) {
                        console.log("here");
                        option_values =  option_values.substring(1, option_values.length-1);    
                    }
                    var option_price = 0;
                    $('#item_option_topping_price').prop('readonly', true);
                    var option_id_tmp = $('.eDefault').find('[name="OptionId[]"]').val();
            
                    if(option_id_tmp == "" && option_values != "")
                    {
                       option_values = JSON.parse(option_values); 
                    }
                }
            
                if(option_values != "")
                {
                    // console.log(option_values);
                    option_values = JSON.parse(option_values);
                    $('[name^=tOptionNameLang_]').each(function() {
                        var attr_name = $(this).attr('name');
                        $(this).val(option_values[attr_name]);
                    });     
                }
                else {
                    if(option_default_value == "" || option_default_value == undefined)
                    {
                        option_default_value = $('#options_fields').find('.eDefault').find('[name="BaseOptions[]"]').val();
                    }
            
                    $('[name^=tOptionNameLang_<?= $default_lang ?>]').val(option_default_value);    
                }
                
                var option_addon_img_html = "";
                if($('#option_addon_img_title').length > 0 && option_Image != "" && option_Image != undefined) {
                    var img_status = UrlExists('<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '?time=' + (new Date().getTime()));
                    console.log(img_status);
                    if(img_status == true) {
                        var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '" target="_blank">View Image</a>)';    
                    }
                    else {
                        var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_upload_images_menu_item_options'] ?>' + option_Image + '" target="_blank">View Image</a>)';    
                    }
                            
                    $('#option_addon_img_title').html("<?= $langage_lbl['LBL_OPTION_IMG'] ?>" + option_addon_img_html);    
                }                  
            
                $('#item_option_topping_price').val(option_price);
                $('#option_addon_action').val("edit");
                $('#add_options_toppings_btn').html("<?= $langage_lbl['LBL_Save'] ?>");
                
                $("#add_options_toppings").find(".modal-body").scrollTop(0);
                $('#add_options_toppings').addClass('active');
                general_label();
            }
            
            function edit_addon_fields(eid)
            {
                $('#option_addon_title').html("<?= $langage_lbl['LBL_EDIT_ADDON_TOPPING'] ?>");
                $('#option_addon_type').val("addons");
                $('#option_addon_id').val(eid);
                $('#option_addon_img_title').html("<?= $langage_lbl['LBL_ADDON_TOPPING_IMG'] ?>");
            
                $("#vMenuItemImage").val(null);
                var addon_values = $('.removeclassaddon'+eid).find('[name="addons_lang_all[]"]').text();
                var addon_price = $('.removeclassaddon'+eid).find('[name="AddonPrice[]"]').val();
                var addon_default_value = $('.removeclassaddon'+eid).find('[name="AddonOptions[]"]').val();
                var option_Image = $('.removeclassaddon'+eid).find('[name="vMenuItemOptionImgName"]').val();
                
                $('#item_option_topping_price').prop('readonly', false);
            
                if(addon_values != "")
                {
                    addon_values = JSON.parse(addon_values);
                    $('[name^=tOptionNameLang_]').each(function() {
                        var attr_name = $(this).attr('name');
                        $(this).val(addon_values[attr_name]);
                    });    
                }
                else {
                    $('[name^=tOptionNameLang_<?= $default_lang ?>]').val(addon_default_value);
                }
                var option_addon_img_html = "";
                if($('#option_addon_img_title').length > 0 && option_Image != "" && option_Image != undefined) {
                    var img_status = UrlExists('<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '?time=' + (new Date().getTime()));
                    console.log(img_status);
                    if(img_status == true) {
                        var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_url'] ?>webimages/temp_item_option_images/' + option_Image + '" target="_blank">View Image</a>)';    
                    }
                    else {
                        var option_addon_img_html = ' (<a href="<?= $tconfig['tsite_upload_images_menu_item_options'] ?>' + option_Image + '" target="_blank">View Image</a>)';    
                    }
                    
                    $('#option_addon_img_title').html("<?= $langage_lbl['LBL_ADDON_TOPPING_IMG'] ?>" + option_addon_img_html);    
                }
            
                $('#item_option_topping_price').val(addon_price);
                $('#option_addon_action').val("edit");
                $('#add_options_toppings_btn').html("<?= $langage_lbl['LBL_Save'] ?>");
                
                $("#add_options_toppings").find(".modal-body").scrollTop(0);
                $('#add_options_toppings').addClass('active');
                general_label();
            }
            
            $(document).ready(function () {
            
                //added by SP for required validation add in menu item image when recommended is on on 26-07-2019 start
            
                $("#eRecommended").change(function () {
            
                    var recommended_sel = '';
            
                    recommended_sel = $("input[name='eRecommended']:checked").val();
            
                    if(recommended_sel=='on') {
            
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
            
            
            
                $("#iServiceId").change(function () {
            
                    var iServiceid = $(this).val();
            
                    if (iServiceid == '1') {
            
                        $(".servicecatresponsive").show();
            
                    } else {
            
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
            
        </script>
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
            var errormessage;
            $.validator.addMethod("dollarsscents", function(value, element) {
                return this.optional(element) || /^\d{0,4}(\.\d{0,2})?$/i.test(value);
            }, "You can add upto two decimal places");
            if ($('#menuItem_form').length !== 0) {
            
                $('#menuItem_form').validate({
            
                    ignore: 'input[type=hidden]',
            
                    errorClass: 'help-block error',
            
                    errorElement: 'span',
            
                    onkeyup: function (element) {
            
                        $(element).valid()
            
                    },
            
                    highlight: function (e) {
            
                        if ($(e).attr("name") == "OptPrice[]" || $(e).attr("name") == "AddonOptions[]" || $(e).attr("name") == "BaseOptions[]") {
            
            
            
                            $(e).closest('.row .form-group').removeClass('has-success has-error').addClass('has-error');
            
                        } else {
            
                            $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
            
                        }
            
                        $(e).closest('.help-block').remove();
            
                    },
            
                    success: function (e) {
            
                        e.closest('.row .form-group').removeClass('has-success has-error');
            
                        e.closest('.row').removeClass('has-success has-error');
            
                        e.closest('.help-block').remove();
            
                        e.closest('.help-inline').remove();
            
                    },
            
                    rules: {
            
                        iCompanyId: {required: true},
            
                        iFoodMenuId: {required: true},
            
                        fPrice: {required: true, number: true, dollarsscents: true},
            
                        fOfferAmt: {number: true},
                        vSKU:{
                            alphanumericspace: true,
                            remote: {
                                url: 'ajax_check_item_sku.php',
                                type: "post",
                                data: {
                                    iMenuItemId:'<?php echo $id; ?>',
                                    iFoodMenuId:'<?php echo $iFoodMenuId; ?>'
                                },
                                beforeSend : function(xhr) {
                                    mO4u1yc3dx(xhr);
                                },
                                dataFilter: function (response) {
                                    //response = $.parseJSON(response);
                                    //response = response.trim();
                                    if (response == 'false') {
                                        errormessage = "<?= addslashes($langage_lbl['LBL_SKU_EXISTS_MSG']); ?>";
                                        return false;
                                    } else {
                                        return true;
                                    }
                                },
                            }
                        },
                        'BaseOptions[]': {required: true},
            
                        'OptPrice[]': {required: true, number: true},
            
                        'AddonOptions[]': {required: true}
            
                    },
                    messages: {
                        vSKU: {remote: function () {
                            return errormessage;
                        }},
                    },
            
                    submitHandler: function (form) {
            
                        if ($(form).valid()) {
                            ShpSq6fAm7(form);
                            form.submit();
                        }
                            
            
                        return false; // prevent normal form posting
            
                    }
            
                });
            
            }
            
            function updateOptionPrice() {
            
                var serviceId = '<?= $iServiceId; ?>';
            
                var basePrice = 0;
            
                if (serviceId > 1) {
            
                    basePrice = $("#fPrice").val();
            
                }
            
                $("#OptPrice").val(basePrice);
            
            }
            
        </script>
        <script type="text/javascript" language="javascript">
            function general_label() {
                $(document).on('focusin','.form-group input,.form-group textarea',function(){
                    $(this).closest('.form-group').addClass('floating');
                });
                $(document).on('focusout','.form-group input,.form-group textarea',function(){
                    if($(this).val() == ""){
                        $(this).closest('.form-group').removeClass('floating');
                    }
                });
                
                $(document).on('focusin','.form-group input,.form-group textarea',function(){
                    $(this).parent('relation-parent').closest('.form-group').addClass('floating');
                });
                $(document).on('focusout','.form-group input,.form-group textarea',function(){
                    if($(this).val() == ""){
                        $(this).parent('relation-parent').closest('.form-group').removeClass('floating');
                    }
                });
                
                $( ".general-form .form-group" ).each(function( index ) {
                    $this = $(this).find('input');
                    if($this.val() == ""){
                        $this.closest('.form-group').removeClass('floating');
                    }else {
                        $this.closest('.form-group').addClass('floating');   
                    }
                })
                $( ".gen-from .form-group" ).each(function( index ) {
                    $this = $(this).find('input');
                    if($this.val() == ""){
                        $this.closest('.form-group').removeClass('floating');
                    }else {
                        $this.closest('.form-group').addClass('floating');   
                    }
                })
                $( ".general-form .form-group" ).each(function( index ) {
                    $this = $(this).find('textarea');
                    if($this.val() == ""){
                        $this.closest('.form-group').removeClass('floating');
                    }else {
                        $this.closest('.form-group').addClass('floating');   
                    }
                });
                
                $('#add_options_toppings .modal-body').animate({ scrollTop: 0 }, 'fast');
            }
            
            function editItemDetails(action, modal_id)
            {
                $('#modal_action').html(action);
                $('#'+modal_id).find(".modal-body").scrollTop(0);
                $('#'+modal_id).addClass('active'); 
            }
            
            function saveItemDetails(field_name, modal_id)
            {
                if($.trim($('#'+field_name+'<?= $defaultLang ?>').val()) == "") {
                    $('#'+field_name+'<?= $defaultLang ?>_error').show();
                    $('#'+field_name+'<?= $defaultLang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        <?php if($EN_available) { ?>
                        $('#'+field_name+'EN_error').hide();
                        <?php } else { ?>
                        $('#'+field_name+'<?= $defaultLang ?>_error').hide();
                        <?php } ?>
                    }, 5000);
                    return false;
                }
                $('#'+field_name+'Default').val($('#'+field_name+'<?= $default_lang ?>').val());
                if($('#'+field_name+'<?= $default_lang ?>').val() == "")
                {
                    $('#'+field_name+'Default').val($('#'+field_name+'<?= $defaultLang ?>').val());
                }
                $('#'+modal_id).removeClass('active'); 
                general_label();
            }
            function UrlExists(url)
            {
                var final_url = "";
                var http = new XMLHttpRequest();
                http.open('HEAD', url, false);
                http.setRequestHeader('Cache-Control', 'no-store');
                http.onload = function () {
                    final_url = http.responseURL;
                };
                http.send();
                if(final_url != "" && final_url.includes("Page-Not-Found")) {
                    return false;
                }
                return http.status == 200;
            }
            function checkItemCategoryServiceType(iFoodMenuId) {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_check_item_category.php',
                    'AJAX_DATA': {iFoodMenuId: iFoodMenuId},
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        if(data > 1) {
                            $(".foodType").hide();    
                        }
                        else {
                            $(".foodType").show();    
                        }
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }
        </script>
    </body>
</html>