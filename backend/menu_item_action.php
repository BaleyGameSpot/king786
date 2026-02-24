<?php

// ============================================================
// LOGGING SYSTEM - menu_item_action.php
// Log file: logs/menu_item_action_YYYY-MM-DD.log
// ============================================================
define('MIA_LOG_DIR', __DIR__ . '/logs');
define('MIA_LOG_FILE', MIA_LOG_DIR . '/menu_item_action_' . date('Y-m-d') . '.log');

if (!is_dir(MIA_LOG_DIR)) {
    @mkdir(MIA_LOG_DIR, 0755, true);
}

function mia_log($step, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] [STEP: $step]";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $entry .= " " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $entry .= " " . $data;
        }
    }
    @file_put_contents(MIA_LOG_FILE, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Custom error handler - PHP errors bhi log hogi
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $types = [E_ERROR=>'ERROR', E_WARNING=>'WARNING', E_NOTICE=>'NOTICE',
              E_PARSE=>'PARSE', E_DEPRECATED=>'DEPRECATED', E_USER_ERROR=>'USER_ERROR',
              E_USER_WARNING=>'USER_WARNING', E_USER_NOTICE=>'USER_NOTICE'];
    $type = isset($types[$errno]) ? $types[$errno] : "UNKNOWN($errno)";
    mia_log("PHP_$type", "$errstr in $errfile on line $errline");
    return false; // default handler bhi chalaye
});

// Fatal errors (500 cause) bhi catch karo
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        mia_log("FATAL_ERROR", $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
    }
    mia_log("SCRIPT_END", "Script finished");
});

// Log script start
mia_log("SCRIPT_START", [
    'method'     => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'uri'        => $_SERVER['REQUEST_URI'] ?? '',
    'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
]);

// GET params log karo
mia_log("GET_PARAMS", [
    'id'      => $_GET['id'] ?? 'NOT_SET',
    'success' => $_GET['success'] ?? 'NOT_SET',
]);

// POST params log karo (sensitive values mask karke)
$post_log = $_POST;
unset($post_log['vImageTest']);
mia_log("POST_PARAMS", $post_log);

// SESSION key values log karo
mia_log("SESSION_DATA", [
    'sess_iUserId'  => $_SESSION['sess_iUserId'] ?? 'NOT_SET',
    'sess_eSystem'  => $_SESSION['sess_eSystem'] ?? 'NOT_SET',
    'sess_lang'     => $_SESSION['sess_lang'] ?? 'NOT_SET',
    'sess_signin'   => $_SESSION['sess_signin'] ?? 'NOT_SET',
]);

mia_log("INCLUDE_COMMON_START", "Loading common.php");
include_once('common.php');
mia_log("INCLUDE_COMMON_END", "common.php loaded successfully");

//added by SP for cubex changes on 07-11-2019

mia_log("XTHEME_CHECK_START", "Checking isXThemeActive");
if ($THEME_OBJ->isXThemeActive() == 'Yes') {
    mia_log("XTHEME_ACTIVE", "XTheme is active - loading cx-menu_item_action.php");
    include_once("cx-menu_item_action.php");
    exit;
}
mia_log("XTHEME_CHECK_END", "XTheme not active, continuing");

mia_log("REQUIRE_IMAGECROP_START", "Loading Imagecrop.class.php from: " . TPATH_CLASS . "/Imagecrop.class.php");
require_once(TPATH_CLASS . "/Imagecrop.class.php");
mia_log("REQUIRE_IMAGECROP_END", "Imagecrop.class.php loaded");

$thumb = new thumbnail();
mia_log("AUTH_CHECK_START", "Calling checkMemberAuthentication");
$AUTH_OBJ->checkMemberAuthentication();
mia_log("AUTH_CHECK_END", "Authentication passed");

$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
mia_log("SETROLE", "setRole('company', '$url')");
setRole($abc,$url);

mia_log("ESYSTEM_CHECK", [
    'sess_eSystem' => $_SESSION['sess_eSystem'] ?? 'NOT_SET',
    'required'     => 'DeliverAll',
    'match'        => (($_SESSION['sess_eSystem'] ?? '') == 'DeliverAll') ? 'YES' : 'NO',
]);
if($_SESSION["sess_eSystem"] != "DeliverAll")
{
    mia_log("ESYSTEM_REDIRECT", "sess_eSystem is not DeliverAll - redirecting to profile.php");
    header('Location:profile.php');
}
$script = 'MenuItems';
$tbl_name = 'menu_items';
$tbl_name1 = 'menuitem_options';

mia_log("DB_QUERY_CURRENCY", "Fetching default currency");
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
mia_log("DB_RESULT_CURRENCY", [
    'row_count' => count($db_currency),
    'data'      => $db_currency,
]);

$iCompanyId = $_SESSION['sess_iUserId'];
mia_log("COMPANY_ID", "iCompanyId = $iCompanyId");

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

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
mia_log("ACTION_DETERMINED", "id='$id', action='$action', success='$success'");

$iFoodMenuId = isset($_POST['iFoodMenuId']) ? $_POST['iFoodMenuId'] : '0';
$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : '';
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

$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$vImageTest = isset($_POST['vImageTest']) ? $_POST['vImageTest'] : '';

$BaseOptions = isset($_POST['BaseOptions']) ? $_POST['BaseOptions'] : '';
$OptPrice = isset($_POST['OptPrice']) ? $_POST['OptPrice'] : '';
$optType = isset($_POST['optType']) ? $_POST['optType'] : '';
$OptionId = isset($_POST['OptionId']) ? $_POST['OptionId'] : '';
$eDefault = isset($_POST['eDefault']) ? $_POST['eDefault'] : '';

$base_array = array();
if (is_array($BaseOptions)) {
    foreach ($BaseOptions as $key => $value) {
        if (trim($value) != "") {
            $base_array[$key]['vOptionName'] = $value;
            $base_array[$key]['fPrice'] = isset($OptPrice[$key]) ? $OptPrice[$key] : 0;
            $base_array[$key]['eOptionType'] = isset($optType[$key]) ? $optType[$key] : 'Options';
            $base_array[$key]['iOptionId'] = isset($OptionId[$key]) ? $OptionId[$key] : '';
            $base_array[$key]['eDefault'] = isset($eDefault[$key]) ? $eDefault[$key] : 'No';
            $base_array[$key]['eStatus'] = 'Active';
        }
    }
}

$AddonOptions = isset($_POST['AddonOptions']) ? $_POST['AddonOptions'] : '';
$AddonPrice = isset($_POST['AddonPrice']) ? $_POST['AddonPrice'] : '';
$optTypeaddon = isset($_POST['optTypeaddon']) ? $_POST['optTypeaddon'] : '';
$addonId = isset($_POST['addonId']) ? $_POST['addonId'] : '';

$addon_array = array();
if (is_array($AddonOptions)) {
    foreach ($AddonOptions as $key => $value) {
        $addon_array[$key]['vOptionName'] = $value;
        $addon_array[$key]['fPrice'] = isset($AddonPrice[$key]) ? $AddonPrice[$key] : 0;
        $addon_array[$key]['eOptionType'] = isset($optTypeaddon[$key]) ? $optTypeaddon[$key] : 'Addon';
        $addon_array[$key]['iOptionId'] = isset($addonId[$key]) ? $addonId[$key] : '';
        $addon_array[$key]['eStatus'] = 'Active';
    }
}
$vTitle_store = $vItemDesc_store = array();
mia_log("DB_QUERY_LANG_MASTER", "Fetching active languages");
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
mia_log("DB_RESULT_LANG_MASTER", ['count' => $count_all]);
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
    mia_log("FORM_SUBMIT_START", "btnsubmit detected - form submission starting");
    $img_path = $tconfig["tsite_upload_images_menu_item_path"];
    $temp_gallery = $img_path . '/';
    $image_object = $_FILES['vImage']['tmp_name'] ?? '';
    $image_name = $_FILES['vImage']['name'] ?? '';
    $file_error = $_FILES['vImage']['error'] ?? -1;
    $vImgName = "";
    mia_log("IMAGE_UPLOAD_DATA", [
        'img_path'    => $img_path,
        'path_exists' => is_dir($img_path) ? 'YES' : 'NO',
        'path_writable' => is_writable($img_path) ? 'YES' : 'NO',
        'image_name'  => $image_name,
        'file_error'  => $file_error,
        'tmp_name'    => $image_object,
        'oldImage'    => $oldImage,
    ]);
    if ($image_name != "") {
        mia_log("IMAGE_PROCESS_START", "Processing new image: $image_name");
        $oldFilePath = $temp_gallery . $oldImage;
        if ($oldImage != '' && file_exists($oldFilePath)) {
            mia_log("IMAGE_DELETE_OLD", "Deleting old image: $oldFilePath");
            unlink($img_path . '/' . $oldImage);
        }
        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        mia_log("IMAGE_EXTENSION_CHECK", "Extension: $ext");
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            mia_log("IMAGE_EXTENSION_INVALID", "Invalid extension: $ext");
        }
        if ($flag_error == 1) {
            mia_log("IMAGE_ERROR_REDIRECT", "Image error - redirecting");
            getPostForm($_POST, $var_msg, "menu_item_action.php?success=0&var_msg=" . $var_msg);
            exit;
        } else {
            $Photo_Gallery_folder = $img_path . '/';
            mia_log("IMAGE_UPLOAD_START", "Calling GeneralUploadImage");
            $img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            mia_log("IMAGE_UPLOAD_RESULT", "Uploaded filename: $img1");
            $oldImage = $img1;
        }
    } else {
        mia_log("IMAGE_SKIP", "No new image uploaded - keeping existing: '$oldImage'");
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
    mia_log("BUILD_QUERY_START", "Building main INSERT/UPDATE query");
    $editItemDesc = $where = "";
    for ($i = 0; $i < scount($vTitle_store); $i++) {
        $vValue = 'vItemType_' . $db_master[$i]['vCode'];
        $vValue_desc = 'vItemDesc_' . $db_master[$i]['vCode'];
        if (!isset($_POST[$vItemDesc_store[$i]])) {
            mia_log("MISSING_POST_KEY", "POST key not set: " . $vItemDesc_store[$i]);
        }
        if (!isset($_POST[$vTitle_store[$i]])) {
            mia_log("MISSING_POST_KEY", "POST key not set: " . $vTitle_store[$i]);
        }
        $strItemDesc = $obj->SqlEscapeString(htmlspecialchars_decode(html_entity_decode($_POST[$vItemDesc_store[$i]]), ENT_QUOTES));
        $strItemTitle = $obj->SqlEscapeString(htmlspecialchars_decode(html_entity_decode($_POST[$vTitle_store[$i]]), ENT_QUOTES));
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
           `prescription_required` = '" . $prescription_required . "', " . $editItemDesc . ""
            . $where;
    mia_log("DB_QUERY_MAIN", "Executing main query: " . substr($query, 0, 500));
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    mia_log("DB_QUERY_MAIN_RESULT", "id after insert/update: '$id'");
    if (!empty($id)) {
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

            if (scount($base_array) > 0) {
                foreach ($base_array as $key => $value) {
                    if ($value['iOptionId'] == '') {
                        $q = "INSERT INTO ";
                        $where = '';
                    } else {
                        $q = "UPDATE ";
                        $where = " WHERE `iOptionId` = '" . $value['iOptionId'] . "'";
                    }
                    $basequery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eDefault` = '" . $value['eDefault'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($basequery);
                }
            }
        } else {
            if (scount($base_array) > 0) {
                foreach ($base_array as $key => $value) {
                    $q = "INSERT INTO ";
                    $where = '';
                    $basequery = $q . " `" . $tbl_name1 . "` SET
                    `iMenuItemId`= '" . $id . "',
                    `vOptionName` = '" . $value['vOptionName'] . "',
                    `fPrice` = '" . $value['fPrice'] . "',
                    `eDefault` = '" . $value['eDefault'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                    `eOptionType` = '" . $value['eOptionType'] . "'"
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
                    $addonquery = $q . " `" . $tbl_name1 . "` SET
                        `iMenuItemId`= '" . $id . "',
                        `vOptionName` = '" . $value['vOptionName'] . "',
                        `fPrice` = '" . $value['fPrice'] . "',
                        `eStatus` = '" . $value['eStatus'] . "',
                        `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($addonquery);
                }
            }
        } else {
            if (scount($addon_array) > 0) {
                foreach ($addon_array as $key => $value) {
                    $q = "INSERT INTO ";
                    $where = '';
                    $addonquery = $q . " `" . $tbl_name1 . "` SET
                    `iMenuItemId`= '" . $id . "',
                    `vOptionName` = '" . $value['vOptionName'] . "',
                    `fPrice` = '" . $value['fPrice'] . "',
                    `eStatus` = '" . $value['eStatus'] . "',
                    `eOptionType` = '" . $value['eOptionType'] . "'"
                            . $where;
                    $obj->sql_query($addonquery);
                }
            }
        }
    }
    //header("Location:menu_item_action.php?id=" . $id . '&success=1');
    if ($action == "Add") {
        $var_msg = 'Item Insert Successfully.';
    } else {
        $var_msg = 'Item Updated Successfully.';
    }
    mia_log("FORM_SUBMIT_SUCCESS", "Redirecting to menuitems.php - action='$action', id='$id'");
    header("Location:menuitems.php?success=1&var_msg=" . $var_msg);
    //header("Location:".$backlink);exit;
}

// for Edit
if ($action == 'Edit') {
    mia_log("EDIT_FETCH_START", "Fetching menu item data for id='$id'");
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iMenuItemId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    mia_log("EDIT_FETCH_RESULT", ['row_count' => scount($db_data)]);

    $sql1 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Options' AND eStatus = 'Active'";
    $db_optionsdata = $obj->MySQLSelect($sql1);

    $sql2 = "SELECT * FROM " . $tbl_name1 . " WHERE iMenuItemId = '" . $id . "' AND eOptionType = 'Addon' AND eStatus = 'Active'";
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
                $eFoodType = $value['eFoodType'];
                $vHighlightName = $value['vHighlightName'];
                $prescription_required = $value['prescription_required'];
            }
        }
    }
}
mia_log("DB_QUERY_MENU_CAT", "Fetching menu categories for iCompanyId=$iCompanyId");
$sql_cat = "SELECT fm.*,c.vCompany,c.iServiceId FROM food_menu AS fm LEFT JOIN `company` as c ON c.iCompanyId=fm.iCompanyId WHERE fm.iCompanyId = $iCompanyId AND fm.eStatus = 'Active'";
$db_menu = $obj->MySQLSelect($sql_cat);
mia_log("DB_RESULT_MENU_CAT", ['row_count' => scount($db_menu)]);

if (!empty($db_menu[0]['iServiceId'])) {
    $iServiceId = $db_menu[0]['iServiceId'];
    mia_log("SERVICE_ID", "iServiceId = $iServiceId");
    $sql = "SELECT prescription_required FROM `service_categories` WHERE iServiceId = '" . $iServiceId . "'";
    $db_prescription = $obj->MySQLSelect($sql);
    $prescriptionchkbox_required = $db_prescription[0]['prescription_required'];
    mia_log("PRESCRIPTION_CHECK", "prescriptionchkbox_required = " . ($prescriptionchkbox_required ?? 'NOT_SET'));
} else {
    mia_log("SERVICE_ID_MISSING", "db_menu[0]['iServiceId'] is empty - iServiceId not set");
}
$helpText = "This feature can be used when you want to provide different options for the same product. The price would be added to the base price.For E.G.: Regular Pizza, Double Cheese Pizza etc.";
if ($iServiceId > 1) {
    $helpText = "This feature can be used when you want to provide different options for the same product.";
}
mia_log("RENDER_START", "Starting HTML render. iServiceId=" . ($iServiceId ?? 'NOT_SET'));
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?> <?= $action; ?></title>
        <?php include_once("top/top_script.php"); ?>
        <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style>
            /* ===== Menu Item Action - Professional UI ===== */
            .mia-hero {
                background: linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);
                padding: 26px 28px;
                position: relative; overflow: hidden;
            }
            .mia-hero::before {
                content:''; position:absolute; top:-50px; right:-50px;
                width:200px; height:200px; border-radius:50%;
                background:rgba(255,255,255,0.04);
            }
            .mia-hero-inner {
                display:flex; align-items:center;
                justify-content:space-between; flex-wrap:wrap; gap:12px;
                position:relative; z-index:1;
            }
            .mia-hero-title { color:#fff; font-size:22px; font-weight:700; margin:0 0 3px; }
            .mia-hero-sub   { color:rgba(255,255,255,.6); font-size:13px; margin:0; }
            .mia-back-btn {
                display:inline-flex; align-items:center; gap:7px;
                background:rgba(255,255,255,.1); color:#fff!important;
                border:1px solid rgba(255,255,255,.2); border-radius:8px;
                padding:9px 16px; font-size:13px; font-weight:500;
                text-decoration:none!important; transition:background .2s;
            }
            .mia-back-btn:hover { background:rgba(255,255,255,.18); }
            /* Form card */
            .mia-card {
                background:#fff; border-radius:0;
                padding: 28px 28px 32px;
            }
            .mia-section-title {
                font-size:15px; font-weight:700; color:#1a1a2e;
                margin:0 0 18px; padding-bottom:10px;
                border-bottom:2px solid #f0f2f7;
                display:flex; align-items:center; gap:8px;
            }
            .mia-section-title .mia-sec-icon {
                width:28px; height:28px; border-radius:7px;
                display:inline-flex; align-items:center; justify-content:center;
                font-size:14px;
            }
            .mia-row { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:18px; }
            .mia-field { flex:1; min-width:220px; }
            .mia-field.full { flex:0 0 100%; }
            .mia-label {
                display:block; font-size:12.5px; font-weight:600;
                color:#5a6478; margin-bottom:6px; text-transform:uppercase;
                letter-spacing:.4px;
            }
            .mia-label .req { color:#e94560; }
            .mia-input, .mia-select, .mia-textarea {
                width:100%; border:1.5px solid #dde1e7; border-radius:8px;
                padding:10px 13px; font-size:14px; color:#3d4451;
                background:#fafbff; outline:none; box-sizing:border-box;
                transition:border-color .2s, box-shadow .2s, background .2s;
                font-family:inherit;
            }
            .mia-input:focus,.mia-select:focus,.mia-textarea:focus {
                border-color:#e94560; background:#fff;
                box-shadow:0 0 0 3px rgba(233,69,96,.08);
            }
            .mia-textarea { min-height:80px; resize:vertical; }
            .mia-select { appearance:none; -webkit-appearance:none;
                background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23aab0bc'/%3E%3C/svg%3E");
                background-repeat:no-repeat; background-position:right 12px center;
                padding-right:32px; cursor:pointer;
            }
            .mia-note {
                font-size:11.5px; color:#aab0bc; margin-top:5px; display:block;
            }
            /* Image upload */
            .mia-img-preview {
                display:block; max-width:180px; max-height:180px;
                border-radius:12px; border:2px solid #e8ecf0;
                object-fit:cover; margin-bottom:10px;
            }
            .mia-file-area {
                border:2px dashed #dde1e7; border-radius:10px;
                padding:18px; text-align:center;
                background:#fafbff; cursor:pointer;
                transition:border-color .2s;
            }
            .mia-file-area:hover { border-color:#e94560; }
            .mia-file-area input[type=file] {
                display:block; width:100%; cursor:pointer; font-size:13px;
            }
            /* Options / Addons panel */
            .mia-panel {
                border:1.5px solid #e8ecf0; border-radius:12px;
                overflow:hidden; margin-bottom:18px;
            }
            .mia-panel-head {
                background:#f4f6fb; padding:14px 18px;
                display:flex; align-items:center; justify-content:space-between;
                border-bottom:1.5px solid #e8ecf0;
            }
            .mia-panel-head-title {
                font-size:13.5px; font-weight:700; color:#1a1a2e;
                display:flex; align-items:center; gap:6px;
            }
            .mia-panel-body { padding:18px; }
            .mia-opt-row {
                display:flex; gap:10px; align-items:center;
                margin-bottom:10px; flex-wrap:wrap;
            }
            .mia-opt-row .mia-input { flex:1; min-width:120px; }
            .mia-add-btn {
                display:inline-flex; align-items:center; gap:6px;
                background:#eafaf1; color:#27ae60; border:1.5px solid #c3e6cb;
                border-radius:8px; padding:8px 14px; font-size:13px; font-weight:600;
                cursor:pointer; transition:background .2s;
            }
            .mia-add-btn:hover { background:#d4efdf; }
            .mia-rem-btn {
                display:inline-flex; align-items:center; justify-content:center;
                width:32px; height:32px; border-radius:8px;
                background:#fdf2f2; color:#e74c3c; border:1.5px solid #fad7d7;
                cursor:pointer; transition:background .2s; flex-shrink:0;
            }
            .mia-rem-btn:hover { background:#fce8e8; }
            /* Toggle switches */
            .mia-toggle-row {
                display:flex; align-items:center; justify-content:space-between;
                padding:14px 0; border-bottom:1px solid #f0f2f7;
            }
            .mia-toggle-row:last-child { border-bottom:none; }
            .mia-toggle-label {
                font-size:13.5px; color:#3d4451; font-weight:500;
            }
            .mia-toggle-sublabel {
                font-size:11.5px; color:#aab0bc; margin-top:2px;
            }
            /* Alert */
            .mia-alert {
                display:flex; align-items:flex-start; gap:10px;
                padding:13px 16px; border-radius:8px; font-size:13.5px;
                margin-bottom:20px; animation: fadeIn .3s ease;
            }
            @keyframes fadeIn { from{opacity:0;transform:translateY(-5px)} to{opacity:1;transform:translateY(0)} }
            .mia-alert.success { background:#eafaf1; border-left:4px solid #27ae60; color:#1d6b3d; }
            .mia-alert.danger  { background:#fdf2f2; border-left:4px solid #e74c3c; color:#962d2d; }
            .mia-alert-close { margin-left:auto; background:none; border:none; cursor:pointer; opacity:.5; font-size:16px; color:inherit; }
            .mia-alert-close:hover { opacity:1; }
            /* Submit button */
            .mia-submit-area { padding-top:20px; border-top:1px solid #f0f2f7; margin-top:10px; }
            .mia-submit-btn {
                display:inline-flex; align-items:center; gap:8px;
                background:linear-gradient(135deg,#e94560,#c73652);
                color:#fff; border:none; border-radius:10px;
                padding:13px 32px; font-size:15px; font-weight:700;
                cursor:pointer; letter-spacing:.2px;
                box-shadow:0 4px 15px rgba(233,69,96,.35);
                transition:box-shadow .2s, transform .15s;
            }
            .mia-submit-btn:hover {
                box-shadow:0 6px 20px rgba(233,69,96,.45);
                transform:translateY(-1px);
            }
            /* Food type badge select */
            .mia-badge-row {
                display:flex; gap:10px; flex-wrap:wrap;
            }
            .mia-badge-opt {
                display:none;
            }
            .mia-badge-opt + label {
                padding:7px 16px; border:1.5px solid #dde1e7;
                border-radius:20px; font-size:13px; font-weight:500;
                color:#5a6478; cursor:pointer; transition:all .2s;
            }
            .mia-badge-opt:checked + label {
                background:#e94560; border-color:#e94560; color:#fff;
            }
            span.help-block { margin:0; padding:0; }
            @media(max-width:768px) {
                .mia-row { flex-direction:column; }
                .mia-field { min-width:100%; }
            }
        </style>
    </head>
    <body>
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>

            <!-- Hero Header -->
            <div class="mia-hero">
                <div class="mia-hero-inner">
                    <div>
                        <h1 class="mia-hero-title">
                            <?= ($action == 'Add') ? $langage_lbl['LBL_ACTION_ADD'] : $langage_lbl['LBL_EDIT']; ?>
                            <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?>
                        </h1>
                        <p class="mia-hero-sub"><?= ($action == 'Add') ? 'Add a new item to your menu' : 'Update the details of this menu item'; ?></p>
                    </div>
                    <a href="menuitems.php" class="mia-back-btn">
                        <span class="icon-arrow-left"></span>
                        <?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?>
                    </a>
                </div>
            </div>

            <div class="mia-card">
                <!-- Alerts -->
                <?php if ($success == 1) { ?>
                <div class="mia-alert success" id="mia-alert">
                    <span>&#10003;</span>
                    <span><?= $langage_lbl['LBL_Record_Updated_successfully']; ?></span>
                    <button class="mia-alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php } else if ($success == 2) { ?>
                <div class="mia-alert danger" id="mia-alert">
                    <span>&#9888;</span>
                    <span><?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?></span>
                    <button class="mia-alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
                <?php } ?>

                <form id="menuItem_form" name="menuItem_form" class="menuItemFormFront" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $id; ?>"/>
                    <input type="hidden" name="oldImage" value="<?= $oldImage; ?>">
                    <input type="hidden" name="previousLink" id="previousLink" value="<?= $previousLink; ?>"/>
                    <input type="hidden" name="backlink" id="backlink" value="menuitems.php"/>

                    <!-- Section 1: Basic Info -->
                    <div class="mia-section-title">
                        <span class="mia-sec-icon" style="background:#e8f4fd;color:#2980b9;">&#9776;</span>
                        Basic Information
                    </div>
                    <div class="mia-row">
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_MENU_CATEGORY_WEB_TXT'] ?> <span class="req">*</span></label>
                            <select class="mia-select" name="iFoodMenuId" required onchange="changeDisplayOrder(this.value, '<?= $id; ?>');">
                                <option value=""><?= $langage_lbl['LBL_SELECT_CATEGORY'] ?></option>
                                <?php foreach ($db_menu as $dbmenu) { ?>
                                <option value="<?= $dbmenu['iFoodMenuId'] ?>" <?= ($dbmenu['iFoodMenuId'] == $iFoodMenuId) ? 'selected' : ''; ?>>
                                    <?= $dbmenu['vMenu_' . $_SESSION['sess_lang']]; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_DISPLAY_ORDER_FRONT'] ?> <span class="req">*</span></label>
                            <span id="showDisplayOrder001">
                                <?php if ($action == 'Add') { ?>
                                <input type="hidden" name="total" value="<?= $count + 1; ?>">
                                <select name="iDisplayOrder" id="iDisplayOrder" class="mia-select" required>
                                    <?php for ($i = 1; $i <= $count + 1; $i++) { ?>
                                    <option value="<?= $i ?>" <?= ($i == $count + 1) ? 'selected' : ''; ?>><?= $i ?></option>
                                    <?php } ?>
                                </select>
                                <?php } else { ?>
                                <input type="hidden" name="total" value="<?= $iDisplayOrder; ?>">
                                <select name="iDisplayOrder" id="iDisplayOrder" class="mia-select" required>
                                    <?php for ($i = 1; $i <= $count; $i++) { ?>
                                    <option value="<?= $i ?>" <?= ($i == $iDisplayOrder) ? 'selected' : ''; ?>><?= $i ?></option>
                                    <?php } ?>
                                </select>
                                <?php } ?>
                            </span>
                        </div>
                    </div>

                    <?php
                    if ($count_all > 0) {
                        for ($i = 0; $i < $count_all; $i++) {
                            $vCode  = $db_master[$i]['vCode'];
                            $vTitle = $db_master[$i]['vTitle'];
                            $eDefault_lang = $db_master[$i]['eDefault'];
                            $vValue      = 'vItemType_' . $vCode;
                            $vValue_desc = 'vItemDesc_' . $vCode;
                            $required     = ($eDefault_lang == 'Yes') ? 'required' : '';
                            $required_msg = ($eDefault_lang == 'Yes') ? '<span class="req">*</span>' : '';
                    ?>
                    <div class="mia-row">
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_MENU_ITEM_FRONT'] ?> (<?= $vTitle ?>) <?= $required_msg ?></label>
                            <input type="text" class="mia-input" name="<?= $vValue ?>" id="<?= $vValue ?>" value="<?= $$vValue ?>" placeholder="<?= $vTitle ?>" <?= $required ?>>
                        </div>
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_MENU_ITEM_DESCRIPTION'] ?> (<?= $vTitle ?>)</label>
                            <textarea class="mia-textarea" name="<?= $vValue_desc ?>" id="<?= $vValue_desc ?>"><?= $$vValue_desc ?></textarea>
                        </div>
                    </div>
                    <?php } } ?>

                    <!-- Section 2: Pricing -->
                    <div class="mia-section-title" style="margin-top:10px;">
                        <span class="mia-sec-icon" style="background:#eafaf1;color:#27ae60;">&#36;</span>
                        Pricing
                    </div>
                    <div class="mia-row">
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_PRICE_FOR_MENU_ITEM'] ?> (<?= $db_currency[0]['vName'] ?>) <span class="req">*</span></label>
                            <input type="text" class="mia-input" name="fPrice" id="fPrice" value="<?= $fPrice; ?>" onkeyup="updateOptionPrice();" required>
                            <span class="mia-note"><?= $langage_lbl['LBL_NOTE_FOR_PRICE_MENU_ITEM'] ?></span>
                        </div>
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_OFFER_AMOUNT_MENU_ITEM'] ?> (%)</label>
                            <input type="text" class="mia-input" name="fOfferAmt" id="fOfferAmt" value="<?= $fOfferAmt; ?>" placeholder="0">
                            <span class="mia-note"><?= $langage_lbl['LBL_DISCOUNT_NOTE'] ?></span>
                        </div>
                    </div>

                    <!-- Section 3: Item Image -->
                    <div class="mia-section-title" style="margin-top:10px;">
                        <span class="mia-sec-icon" style="background:#fef9e7;color:#f39c12;">&#128247;</span>
                        <?= $langage_lbl['LBL_MENU_ITEM_IMAGE'] ?> <span style="color:#e94560;margin-left:3px;" id="req_recommended">*</span>
                    </div>
                    <div class="mia-row">
                        <div class="mia-field">
                            <span id="single_img001">
                                <?php
                                $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $oldImage;
                                $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $oldImage;
                                if ($oldImage != "" && file_exists($imgpth)) { ?>
                                <img src="<?= $imgUrl ?>" alt="Item Image" class="mia-img-preview">
                                <?php } ?>
                            </span>
                            <div class="mia-file-area">
                                <input type="hidden" name="vImageTest" value="">
                                <input type="hidden" id="imgnameedit" value="<?= trim($oldImage); ?>">
                                <input name="vImage" type="file" onchange="preview_mainImg(event);" style="margin:auto;">
                            </div>
                            <span class="mia-note">Recommended: 2048x2048px. Required when item is marked as recommended.</span>
                        </div>
                    </div>

                    <!-- Section 4: Options & Addons -->
                    <div class="mia-section-title" style="margin-top:10px;">
                        <span class="mia-sec-icon" style="background:#eef2ff;color:#3d5af1;">&#9881;</span>
                        Options &amp; Addons
                    </div>

                    <!-- Options Panel -->
                    <div class="mia-panel">
                        <div class="mia-panel-head">
                            <div class="mia-panel-head-title">
                                <?= $langage_lbl['LBL_OPTIONS_MENU_ITEM'] ?>
                                <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="<?= $helpText ?>"></i>
                            </div>
                            <button type="button" class="mia-add-btn" onclick="options_fields();">
                                <span class="glyphicon glyphicon-plus"></span> Add Option
                            </button>
                        </div>
                        <div class="mia-panel-body">
                            <div id="options_fields">
                                <?php
                                if (scount($db_optionsdata) > 0) {
                                    $opt = 0;
                                    foreach ($db_optionsdata as $k => $option) {
                                        $opt++;
                                        if ($option['eDefault'] == 'Yes') { ?>
                                        <div class="mia-opt-row eDefault">
                                            <input type="text" class="mia-input" name="BaseOptions[]" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" required>
                                            <input type="text" class="mia-input" name="OptPrice[]" value="<?= $option['fPrice'] ?>" placeholder="Price" readonly required>
                                            <input type="hidden" name="optType[]" value="Options">
                                            <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>">
                                            <input type="hidden" name="eDefault[]" value="Yes">
                                        </div>
                                        <?php } else { ?>
                                        <div class="mia-opt-row removeclass<?= $opt ?>">
                                            <input type="text" class="mia-input" name="BaseOptions[]" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" required>
                                            <input type="text" class="mia-input" name="OptPrice[]" value="<?= $option['fPrice'] ?>" placeholder="Price" required>
                                            <input type="hidden" name="optType[]" value="Options">
                                            <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>">
                                            <input type="hidden" name="eDefault[]" value="No">
                                            <button type="button" class="mia-rem-btn" onclick="remove_options_fields('<?= $opt ?>');">
                                                <span class="glyphicon glyphicon-minus"></span>
                                            </button>
                                        </div>
                                        <?php }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Addon Panel -->
                    <div class="mia-panel" <?= ($iServiceId != '1') ? 'style="display:none;"' : '' ?>>
                        <div class="mia-panel-head">
                            <div class="mia-panel-head-title">
                                <?= $langage_lbl['LBL_ADDON_FRONT'] ?>
                                <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Addon/Topping Price will be additional amount which will added in base price"></i>
                            </div>
                            <button type="button" class="mia-add-btn" onclick="addon_fields();">
                                <span class="glyphicon glyphicon-plus"></span> Add Topping
                            </button>
                        </div>
                        <div class="mia-panel-body">
                            <div id="addon_fields">
                                <?php
                                if (scount($db_addonsdata) > 0) {
                                    $a = 0;
                                    foreach ($db_addonsdata as $k => $addon) {
                                        $a++;
                                        ?>
                                        <div class="mia-opt-row removeclassaddon<?= $a ?>">
                                            <input type="text" class="mia-input" name="AddonOptions[]" value="<?= $addon['vOptionName'] ?>" placeholder="Topping Name" required>
                                            <input type="text" class="mia-input" name="AddonPrice[]" value="<?= $addon['fPrice'] ?>" placeholder="Price" required>
                                            <input type="hidden" name="optTypeaddon[]" value="Addon">
                                            <input type="hidden" name="addonId[]" value="<?= $addon['iOptionId'] ?>">
                                            <button type="button" class="mia-rem-btn" onclick="remove_addon_fields('<?= $a ?>');">
                                                <span class="glyphicon glyphicon-minus"></span>
                                            </button>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Settings -->
                    <div class="mia-section-title" style="margin-top:10px;">
                        <span class="mia-sec-icon" style="background:#fdf2f2;color:#e74c3c;">&#9878;</span>
                        Item Settings
                    </div>
                    <div class="mia-row">
                        <!-- Food Type -->
                        <div class="mia-field servicecatresponsive" <?= ($iServiceId != '1') ? 'style="display:none;"' : '' ?>>
                            <label class="mia-label"><?= $langage_lbl['LBL_FOOD_TYPE'] ?> <span class="req">*</span></label>
                            <select class="mia-select" name="eFoodType" id="eFoodType">
                                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?? '--Select--' ?></option>
                                <option value="Veg" <?= ($eFoodType == 'Veg') ? 'selected' : '' ?>><?= $langage_lbl['LBL_VEG_FOOD'] ?></option>
                                <option value="NonVeg" <?= ($eFoodType == 'NonVeg') ? 'selected' : '' ?>><?= $langage_lbl['LBL_NON_VEG_FOOD'] ?></option>
                            </select>
                        </div>
                        <!-- Tag -->
                        <div class="mia-field">
                            <label class="mia-label"><?= $langage_lbl['LBL_ITEM_TAG_NAME'] ?></label>
                            <select class="mia-select" name="vHighlightName" id="vHighlightName">
                                <option value="">-- <?= $langage_lbl['LBL_SELECT_TXT'] ?? 'Select' ?> --</option>
                                <option value="LBL_BESTSELLER" <?= ($vHighlightName == 'LBL_BESTSELLER') ? 'selected' : '' ?>><?= $langage_lbl['LBL_BESTSELLER'] ?></option>
                                <option value="LBL_NEWLY_ADDED" <?= ($vHighlightName == 'LBL_NEWLY_ADDED') ? 'selected' : '' ?>><?= $langage_lbl['LBL_NEWLY_ADDED'] ?></option>
                                <option value="LBL_PROMOTED" <?= ($vHighlightName == 'LBL_PROMOTED') ? 'selected' : '' ?>><?= $langage_lbl['LBL_PROMOTED'] ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div style="border:1.5px solid #e8ecf0; border-radius:12px; padding:0 18px; margin-bottom:20px;">
                        <div class="mia-toggle-row">
                            <div>
                                <div class="mia-toggle-label"><?= $langage_lbl['LBL_ITEM_IN_STOCK_WEB'] ?></div>
                                <div class="mia-toggle-sublabel">Set off when item is out of stock</div>
                            </div>
                            <div class="make-switch" data-on="success" data-off="warning">
                                <input type="checkbox" name="eAvailable" <?= ($id != '' && $eAvailable == 'No') ? '' : 'checked'; ?> id="eAvailable">
                            </div>
                        </div>
                        <div class="mia-toggle-row">
                            <div>
                                <div class="mia-toggle-label"><?= $langage_lbl['LBL_IS_ITEM_RECOMMENDED'] ?></div>
                                <div class="mia-toggle-sublabel">Highlighted in user app at top section</div>
                            </div>
                            <div class="make-switch" data-on="success" data-off="warning">
                                <input type="checkbox" name="eRecommended" <?= ($id != '' && $eRecommended == 'No') ? '' : 'checked'; ?> id="eRecommended">
                            </div>
                        </div>
                        <?php
                        $checked_prescription = "";
                        if ($prescription_required == "Yes") $checked_prescription = "checked";
                        ?>
                        <div class="mia-toggle-row" id="prescription_div" style="display:<?= ($prescriptionchkbox_required == 'Yes') ? 'flex' : 'none'; ?>;">
                            <div>
                                <div class="mia-toggle-label"><?= $langage_lbl_admin['LBL_IS_PRESCRIPTION_REQUIRED'] ?></div>
                                <div class="mia-toggle-sublabel">User uploads prescription at order time</div>
                            </div>
                            <div class="make-switch" data-on="success" data-off="warning" data-on-text="Yes" data-off-text="No">
                                <input type="checkbox" name="prescription_required" <?= $checked_prescription ?> id="prescription_required">
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="mia-submit-area">
                        <button type="submit" class="mia-submit-btn" name="btnsubmit" id="btnsubmit">
                            <span class="icon-ok"></span>
                            <?= $langage_lbl['LBL_Save'] ?? $action; ?> <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?>
                        </button>
                    </div>
                </form>
            </div>

            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>

        <?php
        include_once('top/footer_script.php');
        $lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
        ?>
        <link href="assets/css/imageUpload/bootstrap-imageupload.css" rel="stylesheet">
        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js"></script>
        <?php if ($lang != 'en') { include_once('otherlang_validation.php'); } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
        <style>span.help-block{margin:0;padding:0;}</style>
        <script>
        function changeDisplayOrder(foodId, menuId, parentId)
        {
            var itemParentId = '';
            if (parentId != '') {
                itemParentId = parentId
            }
            $.ajax({
                type: "POST",
                url: 'ajax_display_order.php',
                data: {iFoodMenuId: foodId, page: 'items', iMenuItemId: menuId},
                success: function (response)
                {
                    $("#showDisplayOrder001").html('');
                    $("#showDisplayOrder001").html(response);
                }
            });
            $.ajax({
                type: 'post',
                url: 'ajax_display_order.php',
                data: {method: 'getParentItems', page: 'items', iFoodMenuId: foodId, itemParentId: itemParentId},
                success: function (response) {
                    $("#iParentId").html(response);
                },
                error: function (response) {
                }
            });
        }
        $(document).ready(function () {
            changeDisplayOrder('<?php echo $iFoodMenuId; ?>', '<?php echo $id; ?>', '<?php echo $menuiParentId; ?>');
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
            var container_div = document.getElementById('options_fields');
            var count = container_div.getElementsByTagName('div').length;
            var serviceId = '<?= $iServiceId; ?>';
            var basePrice = 0;
            var baseOptionValue = "Regular";
            if (serviceId > 1) {
                baseOptionValue = "";
                var basePrice = $("#fPrice").val();
            }
            if (count == 0) {
                optionid = 0;
            }
            optionid++;

            var objTo = document.getElementById('options_fields')
            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group removeclass" + optionid);

            if (optionid == '1') {
                var divtest1 = document.createElement("div");
                divtest1.setAttribute("class", "form-group eDefault");
                divtest1.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" value="' + baseOptionValue + '" placeholder="Option Name" required="required"></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="' + basePrice + '" placeholder="Price" readonly><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="Yes"/></div></div><div class="clear"></div>';
                objTo.appendChild(divtest1);

                divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="" placeholder="Option Name"></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)"><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_options_fields(' + optionid + ');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
            } else {
                divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" value="" required="required" placeholder="Option Name"></div></div><div class="col-sm-5"><div class="form-group"><input type="text" class="form-control" id="OptPrice" name="OptPrice[]" value="" required="required" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" ><input type="hidden" name="OptionId[]" value="" /><input type="hidden" name="optType[]" value="Options" /><input type="hidden" name="eDefault[]" value="No"/></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_options_fields(' + optionid + ');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';
            }
            objTo.appendChild(divtest);
        }
        function remove_options_fields(rid) {
            var container_div = document.getElementById('options_fields');
            var count = container_div.getElementsByTagName('div').length;
            //alert(count);
            if (count == 16) {
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
            addonid++;
            var objTo = document.getElementById('addon_fields')
            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group removeclassaddon" + addonid);
            divtest.innerHTML = '<div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="" placeholder="Topping Name" required></div></div><div class="col-sm-5"><div class="form-group"> <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="" placeholder="Price (In <?= $db_currency[0]['vName'] ?>)" required><input type="hidden" name="addonId[]" value="" /><input type="hidden" name="optTypeaddon[]" value="Addon" /></div></div><div class="col-sm-2"><div class="form-group"><div class="input-group"><div class="input-group-btn"> <button class="btn btn-danger" type="button" onclick="remove_addon_fields(' + addonid + ');"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button></div></div></div></div><div class="clear"></div>';

            objTo.appendChild(divtest)
        }
        function remove_addon_fields(rid) {
            $('.removeclassaddon' + rid).remove();
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
                        fPrice: {required: true, number: true},
                        fOfferAmt: {number: true},
                        'BaseOptions[]': {required: true},
                        'OptPrice[]': {required: true, number: true},
                        'AddonOptions[]': {required: true}
                    },
                    messages: {
                        iCompanyId: {
                            //required: 'This field is required.'
                        },
                        iFoodMenuId: {
                           // required: 'This field is required.'
                        },
                        fPrice: {
                           // required: 'This field is required.'
                        }
                    },
                    submitHandler: function (form) {
                        if ($(form).valid())
                            form.submit();
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
    </body>
</html>

