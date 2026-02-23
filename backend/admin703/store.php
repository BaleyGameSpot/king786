<?php
include_once('../common.php');
define("SERVICE_CATEGORIES", "service_categories");
define("CUISINE", "cuisine");
define("COMPANY", "company");
define("COMPANY_CUISINE", "company_cuisine");
define("FOOD_MENU", "food_menu");
define("MENU_ITEMS", "menu_items");
$isEnableAutoStoreOrder = 0;
if ($MODULES_OBJ->isEnableAutoAcceptStoreOrder() > 0) {
    $isEnableAutoStoreOrder = 1;
}
/* Use For Demo */
$storeIdArray = array('36');
/* Use For Demo */
if (!$userObj->hasPermission('view-store')) {
    $userObj->redirect();
}

if (isset($_POST['action']) && $_POST['action'] == "autoaccept") {
    $iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : "";
    $eAutoaccept = isset($_POST['eAutoaccept']) ? $_POST['eAutoaccept'] : "No";
    $sql = "SELECT iOrderId FROM orders WHERE iCompanyId = '$iCompanyId' And iStatusCode='1'";
    $totalData = $obj->MySQLSelect($sql);
    $where = " iCompanyId = '$iCompanyId'";
    $Data_update_Companies['eAutoaccept'] = $eAutoaccept;
    $Company_Update_id = $obj->MySQLQueryPerform("company", $Data_update_Companies, 'update', $where);
    if ($Company_Update_id == "1") {
        $successtype = "1";
        $successMsg = $langage_lbl_admin["LBL_DISABLE_AUTO_ACCEPT_ORDER_TXT"];
        if ($eAutoaccept == "Yes") {
            $successtype = "1";
            $successMsg = $langage_lbl_admin["LBL_AUTO_ACCEPT_ORDER_TXT"];
        }
        $_SESSION['success'] = $successtype;
        $_SESSION['var_msg'] = $successMsg;
    } else {
        $_SESSION['success'] = '2';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_ERROR_OCCURED"];
    }
    //header("Location:" . $tconfig["tsite_url_main_admin"] . "store.php");
    //exit;
}
$script = 'DeliverAllStore';
$eSystem = " AND  c.eSystem ='DeliverAll'";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY c.iCompanyId DESC';
if ($sortby == 1) {
    if ($order == 0) {
        $ord = " ORDER BY c.vCompany ASC";
    } else {
        $ord = " ORDER BY c.vCompany DESC";
    }
}
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY c.vEmail ASC";
    } else {
        $ord = " ORDER BY c.vEmail DESC";
    }
}
if ($sortby == 3) {
    if ($order == 0) {
        $ord = " ORDER BY `count` ASC";
    } else {
        $ord = " ORDER BY `count` DESC";
    }
}
if ($sortby == 4) {
    if ($order == 0) {
        $ord = " ORDER BY c.eStatus ASC";
    } else {
        $ord = " ORDER BY c.eStatus DESC";
    }
}
//End Sorting
$cmp_ssql = "";

$cmp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $cmp_ssql = " And c.tRegistrationDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$select_cat = isset($_REQUEST['selectcategory']) ? stripslashes($_REQUEST['selectcategory']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
if (isset($_POST['copystore'])) {
    $companyId = $_POST['storeId'];
    $copyStores = $_POST['store_sel'];
    //echo "<pre>";print_r($_POST);die;
    if (scount($copyStores) > 0 && $companyId > 0) {
        $getStoreFoodMenu = $obj->MySQLSelect("SELECT * FROM " . FOOD_MENU . " WHERE iCompanyId='" . $companyId . "' AND eStatus!='eStatus'");
        for ($m = 0; $m < scount($getStoreFoodMenu); $m++) {
            $iFoodMenuId = $getStoreFoodMenu[$m]['iFoodMenuId'];
            $vMenu_EN = $getStoreFoodMenu[$m]['vMenu_EN'];
            $getMenuItems = $obj->MySQLSelect("SELECT * FROM " . MENU_ITEMS . " WHERE iFoodMenuId='" . $iFoodMenuId . "' AND eStatus!='eStatus'");
            for ($s = 0; $s < scount($copyStores); $s++) {
                $storeId = $copyStores[$s];
                if ($companyId != $storeId) {
                    //Start For Copy Food Menu and Menu Item Data
                    $chekc_food = $obj->MySQLSelect("SELECT iFoodMenuId FROM " . FOOD_MENU . " WHERE iCompanyId='" . $storeId . "' AND vMenu_EN='" . $vMenu_EN . "'");
                    if (scount($chekc_food) > 0) { // Update Exists Food menu Data
                        for ($f = 0; $f < scount($chekc_food); $f++) {
                            $oldFoodId = $chekc_food[$f]['iFoodMenuId'];
                            $updateFields = "";
                            unset($getStoreFoodMenu[$m]['iFoodMenuId']);
                            foreach ($getStoreFoodMenu[$m] as $key1 => $val1) {
                                if ($key1 == "iCompanyId") {
                                    $val1 = $storeId;
                                }
                                $updateFields .= ",`$key1`='" . $obj->cleanQuery(stripslashes($val1)) . "'";
                            }
                            if ($updateFields != "") {
                                $updateFields = trim($updateFields, ",");
                                $updateQuery = "UPDATE  `" . FOOD_MENU . "` SET $updateFields WHERE iFoodMenuId='" . $oldFoodId . "'";
                                $obj->sql_query($updateQuery);
                            }
                            for ($mi = 0; $mi < scount($getMenuItems); $mi++) {
                                $menuItemName = $getMenuItems[$mi]['vItemType_EN'];
                                $chekc_item = $obj->MySQLSelect("SELECT iMenuItemId FROM " . MENU_ITEMS . " WHERE iFoodMenuId='" . $oldFoodId . "' AND vItemType_EN='" . $menuItemName . "'");
                                $updateFields1 = "";
                                unset($getMenuItems[$mi]['iMenuItemId']);
                                foreach ($getMenuItems[$mi] as $key2 => $val2) {
                                    if ($key2 == "iFoodMenuId") {
                                        $val2 = $oldFoodId;
                                    }
                                    $updateFields1 .= ",`$key2`='" . $obj->cleanQuery(stripslashes($val2)) . "'";
                                }
                                if ($updateFields1 != "") {
                                    $updateFields1 = trim($updateFields1, ",");
                                    if (scount($chekc_item) > 0) {
                                        for ($mr = 0; $mr < scount($chekc_item); $mr++) {
                                            $food_ItemId = $chekc_item[$mr]['iMenuItemId'];
                                            $updateQuery1 = "UPDATE  `" . MENU_ITEMS . "` SET $updateFields1 WHERE iMenuItemId='" . $food_ItemId . "'";
                                            $obj->sql_query($updateQuery1);
                                        }
                                    } else {
                                        $updateQuery1 = "INSERT INTO  `" . MENU_ITEMS . "` SET $updateFields1";
                                        $obj->sql_query($updateQuery1);
                                        $food_ItemId = $obj->GetInsertId();
                                    }
                                }
                            }
                        }
                    } else { // Insert New Food menu Data
                        $updateFields = "";
                        unset($getStoreFoodMenu[$m]['iFoodMenuId']);
                        foreach ($getStoreFoodMenu[$m] as $key1 => $val1) {
                            if ($key1 == "iCompanyId") {
                                $val1 = $storeId;
                            }
                            $updateFields .= ",`$key1`='" . $obj->cleanQuery(stripslashes($val1)) . "'";
                        }
                        if ($updateFields != "") {
                            $updateFields = trim($updateFields, ",");
                            $updateQuery = "INSERT INTO  `" . FOOD_MENU . "` SET $updateFields";
                            $obj->sql_query($updateQuery);
                            $company_foodId = $obj->GetInsertId();
                            if ($company_foodId > 0) {
                                for ($mi = 0; $mi < scount($getMenuItems); $mi++) {
                                    $menuItemName = $getMenuItems[$mi]['vItemType_EN'];
                                    $updateFields1 = "";
                                    unset($getMenuItems[$mi]['iMenuItemId']);
                                    foreach ($getMenuItems[$mi] as $key2 => $val2) {
                                        if ($key2 == "iFoodMenuId") {
                                            $val2 = $company_foodId;
                                        }
                                        $updateFields1 .= ",`$key2`='" . $obj->cleanQuery(stripslashes($val2)) . "'";
                                    }
                                    if ($updateFields1 != "") {
                                        $updateFields1 = trim($updateFields1, ",");
                                        $updateQuery1 = "INSERT INTO  `" . MENU_ITEMS . "` SET $updateFields1";
                                        $obj->sql_query($updateQuery1);
                                        $food_ItemId = $obj->GetInsertId();
                                    }
                                }
                            }
                        }
                    }
                    //End For Copy Food Menu and Menu Item Data
                    //Start For Copy Company Cuisine Data
                    $getCompanyCuisine = $obj->MySQLSelect("SELECT * FROM " . COMPANY_CUISINE . " WHERE iCompanyId='" . $companyId . "'");
                    $existCompanyCuisine = $obj->MySQLSelect("SELECT * FROM " . COMPANY_CUISINE . " WHERE iCompanyId='" . $storeId . "'");
                    $cCuisinePkArr = array();
                    if (scount($existCompanyCuisine) > 0) {
                        for ($ec = 0; $ec < scount($existCompanyCuisine); $ec++) {
                            $cCuisinePkArr[] = $existCompanyCuisine[$ec]['ccId'];
                        }
                    }
                    for ($c = 0; $c < scount($getCompanyCuisine); $c++) {
                        $cCuisineId = $getCompanyCuisine[$c]['cuisineId'];
                        $cuisinePkId = $getCompanyCuisine[$c]['ccId'];
                        if (!in_array($cuisinePkId, $cCuisinePkArr)) {
                            //print_r($getCompanyCuisine[$c]);die;
                            $insertFields = "";
                            unset($getCompanyCuisine[$c]['ccId']);
                            foreach ($getCompanyCuisine[$c] as $key3 => $val3) {
                                if ($key3 == "iCompanyId") {
                                    $val3 = $storeId;
                                }
                                $insertFields .= ",`$key3`='" . $obj->cleanQuery(stripslashes($val3)) . "'";
                            }
                            //print_r($checkCompanyCuisine);die;
                            if ($insertFields != "") {
                                $insertFields = trim($insertFields, ",");
                                $checkCompanyCuisine = $obj->MySQLSelect("SELECT ccId FROM " . COMPANY_CUISINE . " WHERE iCompanyId='" . $storeId . "' AND cuisineId='" . $cCuisineId . "' ORDER BY ccId ASC");
                                if (scount($checkCompanyCuisine) > 0) {
                                    for ($cu = 0; $cu < scount($checkCompanyCuisine); $cu++) {
                                        $copmCId = $checkCompanyCuisine[$cu]['ccId'];
                                        $wherecId = "ccId='" . $copmCId . "'";
                                        if ($cu == 0) {
                                            $insertQuery = "UPDATE `" . COMPANY_CUISINE . "` SET $insertFields WHERE $wherecId";
                                            $obj->sql_query($insertQuery);
                                        } else {
                                            $deletequery = "DELETE FROM `" . COMPANY_CUISINE . "` WHERE $wherecId";
                                            $obj->sql_query($deletequery);
                                        }
                                    }
                                } else {
                                    $insertQuery = "INSERT INTO  `" . COMPANY_CUISINE . "` SET $insertFields";
                                    $obj->sql_query($insertQuery);
                                    $company_cuisineId = $obj->GetInsertId();
                                }
                            }
                        }
                    }
                    //End For Copy Company Cuisine Data
                }
            }
        }
    } else {
    }
}
$ssql = '';
if ($keyword != '') {
    $keyword_new = $keyword;
    $chracters = array(
        "(",
        "+",
        ")"
    );
    $removespacekeyword = preg_replace('/\s+/', '', $keyword);
    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
    if (is_numeric($keyword_new)) {
        $keyword_new = $keyword_new;
    } else {
        $keyword_new = $keyword;
    }
    if ($option != '') {
        $option_new = $option;
        if ($option == 'MobileNumber') {
            $option_new = "CONCAT(c.vCode,'',c.vPhone)";
        }
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND c.eStatus = '" . clean($eStatus) . "'";
            }
        }
        if ($select_cat != "") {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND sc.iServiceId = '" . clean($select_cat) . "' ";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
            }
        }
        if ($select_cat != "" && $eStatus != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
            }
        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
            }
        }
    } else {
        if ($eStatus == '' && $select_cat != "" && $keyword_new != "") {
            $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND sc.iServiceId = '" . clean($select_cat) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "')) AND sc.iServiceId = '" . clean($select_cat) . "'";
            }
        } else if ($eStatus != '' && $select_cat != "") {
            $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "')) AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            }
        } else if ($eStatus != '') {
            $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "')) AND c.eStatus = '" . clean($eStatus) . "'";
            }
        } else if ($select_cat != "") {
            $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "')) AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            }
        } else {
            $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "'))";
            }
        }
    }
} else if ($eStatus != '' && $select_cat != "" && $keyword == '') {
    $ssql .= " AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
} else if ($eStatus != '' && $keyword == '' && $select_cat == "") {
    $ssql .= " AND c.eStatus = '" . clean($eStatus) . "'";
} else if ($eStatus == '' && $keyword == '' && $select_cat != "") {
    $ssql .= " AND sc.iServiceId = '" . clean($select_cat) . "'";
}
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
    $ssql1 = " AND c.eBuyAnyService = 'No' AND (c.iServiceId IN ($enablesevicescategory)";
    $enablesevicescategory = str_replace(",", "|", $enablesevicescategory);
    $ssql1 .= " OR c.iServiceIdMulti REGEXP '(^|,)(" . $enablesevicescategory . ")(,|$)') ";
    $joinsql = " FIND_IN_SET(sc.iServiceId, c.iServiceId) OR FIND_IN_SET(sc.iServiceId, c.iServiceIdMulti) ";
} else {
    $ssql1 = " AND c.eBuyAnyService = 'No' AND c.iServiceId IN ($enablesevicescategory)";
    $joinsql = " sc.iServiceId = c.iServiceId ";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if (!empty($eStatus)) {
    $eStatus_sql = "";
} else {
    $eStatus_sql = " AND c.eStatus != 'Deleted'";
}
$sql = "SELECT COUNT(DISTINCT(c.iCompanyId)) AS Total FROM company AS c left join service_categories as sc on $joinsql WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $ssql1 $cmp_ssql";
$totalData = $obj->MySQLSelect($sql);
// echo $sql; exit;
$total_results = $totalData[0]['Total'];
$catIds = explode(",", getCurrentActiveServiceCategoriesIds()); // Added By HJ On 06-02-2020 For Solved 141 Mantis Issue #3321
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) {
    $page = 1;
}
//Pagination End
if (!empty($eStatus)) {
    $equery = "";
} else {
    $equery = " AND  c.eStatus != 'Deleted'";
}

$sql = "SELECT c.tSessionId,c.iServiceId,c.eAvailable,c.eAutoaccept,c.iCompanyId, c.vCompany, c.vEmail, c.vCode,c.vPhone, c.eStatus,c.iServiceId, c.tRegistrationDate,c.vTimeZone , sc.vServiceName_" . $default_lang . " as servicename, (SELECT COUNT(rd.iDriverId) FROM register_driver as rd WHERE rd.iCompanyId = c.iCompanyId AND rd.eStatus != 'Deleted' ) as driver_count FROM company AS c  left join service_categories as sc on $joinsql WHERE sc.eStatus != 'Delete' $eSystem $equery $ssql $ssql1 $cmp_ssql GROUP BY c.iCompanyId $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

$companyIdArr = $companyFoodCountArr = array();
for ($b = 0; $b < scount($data_drv); $b++) {
    $companyIdArr[] = $data_drv[$b]['iCompanyId'];
}
if (scount($companyIdArr) > 0) {
    $companyIdArr = array_unique($companyIdArr, SORT_REGULAR);
    $companyIds = implode(",", $companyIdArr);
    $getFoodCount = $obj->MySQLSelect("SELECT iCompanyId,count(iFoodMenuId) AS foodcatCount FROM food_menu WHERE iCompanyId IN ($companyIds) AND eStatus != 'Deleted' GROUP BY iCompanyId");

    for ($v = 0; $v < scount($getFoodCount); $v++) {
        $companyFoodCountArr[$getFoodCount[$v]['iCompanyId']] = $getFoodCount[$v]['foodcatCount'];
    }
}

$endRecord = scount($data_drv);

$serviceIds = implode(",", $service_categories_ids_arr);
$getAllStore = $obj->MySQLSelect("SELECT c.iCompanyId, c.vCompany FROM company AS c WHERE c.iServiceId IN ($serviceIds) $eSystem $equery $ssql1 ");
$sql1 = "SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='store' AND status = 'Active'";
$doc_count_query = $obj->MySQLSelect($sql1);
$doc_count = scount($doc_count_query);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox' && !empty($val) && !is_array($val)) {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$catdata = ServiceData;
$service_cat_data = json_decode($catdata, true);
$languageArr = array();
$languageArr['LBL_TRY_AGAIN_LATER'] = $langage_lbl_admin['LBL_MISSED_DETAILS_MSG'];
$languageArr['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'] = $langage_lbl_admin['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'];
$languageArr['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'] = $langage_lbl_admin['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'];
$languageArr['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'] = $langage_lbl_admin['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'];
$languageArr['LBL_INFO_UPDATED_TXT'] = $langage_lbl_admin['LBL_INFO_UPDATED_TXT'];
$languageArr['DO_PHONE_VERIFY'] = $langage_lbl_admin['LBL_PHONE_VERIFIED_ERROR'];
$languageArr['SESSION_OUT'] = "SESSION_OUT";
$json_lang = json_encode($languageArr);
$acceptOrder = $MODULES_OBJ->isEnableAcceptingOrderFromWeb();
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <style>
        .multiselect {
            width: 533px !important;
        }

        .has-switch.deactivate, .has-switch.deactivate label, .has-switch.deactivate span {
            cursor: not-allowed !important;
        }
    </style>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="10%" class="padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="c.vCompany" <?php
                                if ($option == "c.vCompany") {
                                    echo "selected";
                                }
                                ?> >Name
                                </option>
                                <option value="c.vEmail" <?php
                                if ($option == 'c.vEmail') {
                                    echo "selected";
                                }
                                ?> >E-mail
                                </option>
                                <option value="MobileNumber" <?php
                                if ($option == 'MobileNumber') {
                                    echo "selected";
                                }
                                ?> >Mobile
                                </option>
                            </select>
                        </td>
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?= $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td width="15%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?> >Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?> >Inactive
                                </option>
                                <option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?> >Delete
                                </option>
                            </select>
                        </td>
                        <?php if (scount($service_cat_data) > 1) { ?>
                            <td width="20%" class="estatus_options" id="ecategory_options">
                                <select name="selectcategory" id="selectcategory" class="form-control">
                                    <option value="">Select Service Type</option>
                                    <?php foreach ($service_cat_data as $servicedata) { ?>
                                        <option value="<?= $servicedata['iServiceId'] ?>" <?php
                                        if ($select_cat == $servicedata['iServiceId']) {
                                            echo "selected";
                                        }
                                        ?> > <?= $servicedata['vServiceName']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        <?php } ?>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = '<?= $LOCATION_FILE_ARRAY['STORE.PHP']; ?>'"/>
                        </td>
                        <?php if ($userObj->hasPermission('create-store')) { ?>
                            <td width="15%">
                                <a class="add-btn" href="<?= $LOCATION_FILE_ARRAY['STORE_ACTION']; ?>"
                                   style="text-align: center;">
                                    Add <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></a>
                            </td>
                        <?php } ?>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export">
                            <div class="changeStatus col-lg-12 option-box-left">
                                        <span class="col-lg-2 new-select001">
                                            <?php if (
                                                $userObj->hasPermission([
                                                    'update-status-store',
                                                    'delete-store'
                                                ])
                                            ) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onChange="status_check(this.value);">
                                                <option value="">Select Action</option>
                                                <?php if ($userObj->hasPermission('update-status-store')) { ?>
                                                    <option value='Active' <?php
                                                    if ($option == 'Active') {
                                                        echo "selected";
                                                    }
                                                    ?> >Activate</option>
                                                    <option value="Inactive" <?php
                                                    if ($option == 'Inactive') {
                                                        echo "selected";
                                                    }
                                                    ?> >Deactivate</option>
                                                <?php } ?>
                                                    <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-store')) { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                    <?php } ?>
                                            </select>
                                            <?php } ?>
                                        </span>
                            </div>
                            <?php if (!empty($data_drv) && $userObj->hasPermission('export-store')) { ?>
                                <div class="panel-heading">
                                    <form name="_export_form" id="_export_form" method="post">
                                        <button type="button" onClick="showExportTypes('store')">Export</button>
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th align="center" width="3%" style="text-align:center;">
                                            <input type="checkbox" id="setAllCheck">
                                        </th>
                                        <th width="22%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>
                                                Name <?php
                                                if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Email <?php
                                                if ($sortby == 2) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if (scount($service_cat_data) > 1) { ?>
                                            <th width="10%">Service Type</th>
                                        <?php } ?>
                                        <th width="8%" style="text-align:center;">Item Categories</th>
                                        <th width="15%">Mobile</th>
                                        <th width="12%" style="text-align:center;">Registration Date</th>
                                        <?php if ($MODULES_OBJ->isStorePersonalDriverAvailable()) { ?>
                                            <th width="10%"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></th>
                                        <?php } ?>

                                        <?php if ($userObj->hasPermission('edit-store-document')) { ?>

                                            <th width="8%" class='align-center'>View/Edit Documents</th>
                                        <?php } ?>
                                        <?php if ($acceptOrder > 0) { ?>
                                            <th width="15%"
                                                class='align-center'><?= $langage_lbl_admin['LBL_ACCEPTING_ORDERS']; ?></th>
                                        <?php } ?>
                                        <?php if ($isEnableAutoStoreOrder > 0) { ?>
                                            <th width="15%" style="text-align:center;">Auto Accept</th>
                                        <?php } ?>
                                        <th width="6%" class='align-center' style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 4) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if (
                                            $userObj->hasPermission([
                                                'edit-store',
                                                'update-status-store',
                                                'delete-store'
                                            ])
                                        ) { ?>
                                            <th width="6%" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        //echo "<pre>";print_r($data_drv);die;
                                        $serverTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            $default = '';
                                            if ($data_drv[$i]['iCompanyId'] == 1) {
                                                $default = 'disabled';
                                            }
                                            $foodcatCount = 0;
                                            if (isset($companyFoodCountArr[$data_drv[$i]['iCompanyId']])) {
                                                $foodcatCount = $companyFoodCountArr[$data_drv[$i]['iCompanyId']];
                                            }
                                            $data_drv[$i]['foodcatCount'] = $foodcatCount;
                                            if (trim($data_drv[$i]['tSessionId']) == "") {
                                                $tSessionId = session_id() . time();
                                                $obj->sql_query("UPDATE company SET `tSessionId` = '" . $tSessionId . "' WHERE iCompanyId = '" . $data_drv[$i]['iCompanyId'] . "'");
                                                $data_drv[$i]['tSessionId'] = $tSessionId;
                                            }
                                            $radiobtn = "";
                                            if ($data_drv[$i]['eStatus'] != 'Active') {
                                                $radiobtn = "disabled='disabled'";
                                            }
                                            $date_format_data_array = array(
                                                'tdate' => (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tRegistrationDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['tRegistrationDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_registration_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr class="gradeA">
                                                <td align="center" style="text-align:center;">
                                                    <input type="checkbox" id="checkbox"
                                                           name="checkbox[]" <?= $default; ?>
                                                           value="<?= $data_drv[$i]['iCompanyId']; ?>"
                                                           data-count="<?= $data_drv[$i]['foodcatCount'] ?>"/>&nbsp;
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);"
                                                       onClick="show_store_details('<?= $data_drv[$i]['iCompanyId']; ?>')"
                                                       style="text-decoration: underline;"><?= clearName(stripslashes($data_drv[$i]['vCompany'])); ?>
                                                    </a>
                                                </td>
                                                <td><?php if ($data_drv[$i]['vEmail'] != '') {
                                                        echo clearEmail($data_drv[$i]['vEmail']);
                                                    } else {
                                                        echo '--';
                                                    } ?></td>
                                                <?php if (scount($service_cat_data) > 1) { ?>
                                                    <td>
                                                        <?php foreach ($service_cat_data as $servicedata) { ?>
                                                            <?php if ($servicedata['iServiceId'] == $data_drv[$i]['iServiceId'] && $select_cat == "") { ?>
                                                                <span><?= (isset($servicedata['vServiceName']) ? $servicedata['vServiceName'] : ''); ?></span>
                                                            <?php } elseif ($servicedata['iServiceId'] == $select_cat) { ?>
                                                                <span><?= (isset($servicedata['vServiceName']) ? $servicedata['vServiceName'] : ''); ?></span>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                                <td style="text-align:center;">
                                                    <?php if ($data_drv[$i]['foodcatCount'] > 0 && $userObj->hasPermission('view-item-categories')) { ?>
                                                        <a href="food_menu.php?iFoodMenuId=&option=c.vCompany&keyword=<?= stripslashes(ucfirst($data_drv[$i]['vCompany'])); ?>&eStatus="
                                                           target="_blank"><?= $data_drv[$i]['foodcatCount']; ?></a>
                                                        <?php
                                                    } else {
                                                        echo $data_drv[$i]['foodcatCount'];
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($data_drv[$i]['vPhone'])) { ?>
                                                        (+<?= $data_drv[$i]['vCode'] ?>) <?= clearPhone($data_drv[$i]['vPhone']); ?>
                                                    <?php } ?>
                                                </td>
                                                <td><?= $get_registration_date_format['tDisplayDate'].$get_registration_date_format['tDisplayTime'].$time_zone_difference_text;//DateTime($data_drv[$i]['tRegistrationDate']); ?></td>
                                                <?php if ($MODULES_OBJ->isStorePersonalDriverAvailable()) { ?>
                                                    <td style="text-align:center;">
                                                        <?php if ($userObj->hasPermission('view-providers')) { ?>
                                                            <a href="driver.php?iCompanyId=<?= $data_drv[$i]['iCompanyId'] ?>"
                                                               target="_blank"><?= $data_drv[$i]['driver_count'] ?></a>
                                                        <?php } else { ?>
                                                            <?= $data_drv[$i]['driver_count'] ?>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                                <?php if ($userObj->hasPermission('edit-store-document')) { ?>
                                                    <td align="center">
                                                        <a href="<?php echo $LOCATION_FILE_ARRAY['STORE_DOCUMENT_ACTION'] ?>?id=<?= $data_drv[$i]['iCompanyId']; ?>&action=edit">
                                                            <img src="img/edit-doc.png" alt="Edit Document">
                                                        </a>
                                                    </td>
                                                <?php } ?>
                                                <?php if ($acceptOrder > 0) { ?>
                                                    <td align="center" style="text-align:center;">
                                                        <div class="make-switch"
                                                             id="eAvailable_<?= $data_drv[$i]['iCompanyId']; ?>"
                                                             data-on="success" data-off="warning">
                                                            <input <?= $radiobtn ?>
                                                                    data-id="<?= $data_drv[$i]['iCompanyId']; ?>"
                                                                    data-status="<?= ($data_drv[$i]['eAvailable'] == "Yes") ? 'No' : 'Yes' ?>"
                                                                    onchange="return autoAcceptStatus(this,'eAvailable',event);"
                                                                    type="checkbox"
                                                                    id="eAvailable<?= $data_drv[$i]['iCompanyId']; ?>"
                                                                    data-estatus="<?= $data_drv[$i]['eStatus'] ?>"
                                                                    data-sessionId="<?= $data_drv[$i]['tSessionId']; ?>"
                                                                    data-serviceId="<?= $data_drv[$i]['iServiceId']; ?>"
                                                                    name="eAvailable" <?= ($data_drv[$i]['iCompanyId'] != '' && $data_drv[$i]['eAvailable'] == 'Yes') ? 'checked' : ''; ?>/>
                                                        </div>
                                                    </td>
                                                <?php } ?>
                                                <?php if ($isEnableAutoStoreOrder > 0) { ?>
                                                    <td align="center" style="text-align:center;">
                                                        <div class="make-switch"
                                                             id="eAutoaccept_<?= $data_drv[$i]['iCompanyId']; ?>"
                                                             data-on="success" data-off="warning">
                                                            <input <?= $radiobtn ?>
                                                                    data-id="<?= $data_drv[$i]['iCompanyId']; ?>"
                                                                    data-status="<?= ($data_drv[$i]['eAutoaccept'] == "Yes") ? 'No' : 'Yes' ?>"
                                                                    onchange="return autoAcceptStatus(this,'eAutoaccept',event);"
                                                                    type="checkbox"
                                                                    id="eAutoaccept<?= $data_drv[$i]['iCompanyId']; ?>"
                                                                    data-estatus="<?= $data_drv[$i]['eStatus'] ?>"
                                                                    data-sessionId="<?= $data_drv[$i]['tSessionId']; ?>"
                                                                    data-serviceId="<?= $data_drv[$i]['iServiceId']; ?>"
                                                                    name="eAutoaccept" <?= ($data_drv[$i]['iCompanyId'] != '' && $data_drv[$i]['eAutoaccept'] == 'Yes') ? 'checked' : ''; ?>/>
                                                        </div>
                                                        <!--<button data-id="<?= $data_drv[$i]['iCompanyId']; ?>" data-status="<?= ($data_drv[$i]['eAutoaccept'] == "Yes") ? 'No' : 'Yes' ?>" onclick="return autoAcceptStatus(this);" class="btn <?php if ($data_drv[$i]['eAutoaccept'] == "Yes") { ?>btn-primary<?php } ?>"><?= $data_drv[$i]['eAutoaccept'] ?></button>-->
                                                    </td>
                                                <?php } ?>
                                                <td align="center" style="text-align:center;">
                                                    <?php
                                                    if ($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip"
                                                         title="<?= $data_drv[$i]['eStatus']; ?>">
                                                </td>
                                                <?php if (
                                                    $userObj->hasPermission([
                                                        'edit-store',
                                                        'update-status-store',
                                                        'delete-store'
                                                    ])
                                                ) { ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <?php
                                                        //echo "<pre>";print_r($storeIdArray);die;
                                                        if (in_array($data_drv[$i]['iCompanyId'], $storeIdArray) && SITE_TYPE == 'Demo') {
                                                            ?>
                                                            <?php if ($userObj->hasPermission('edit-store')) { ?>
                                                                <a href="<?= $LOCATION_FILE_ARRAY['STORE_ACTION']; ?>?id=<?= $data_drv[$i]['iCompanyId']; ?>"
                                                                   data-toggle="tooltip" title="Edit">
                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                </a>
                                                            <?php } else {
                                                                echo "--";
                                                            } ?>
                                                        <?php } else { ?>
                                                            <div class="share-button share-button4 openHoverAction-class"
                                                                 style="display:block;">
                                                                <label class="entypo-export">
                                                                    <span><img src="images/settings-icon.png"
                                                                               alt=""></span>
                                                                </label>
                                                                <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iCompanyId']; ?>">
                                                                    <ul>
                                                                        <?php if ($userObj->hasPermission('edit-store')) { ?>
                                                                            <li class="entypo-twitter"
                                                                                data-network="twitter">
                                                                                <a href="<?= $LOCATION_FILE_ARRAY['STORE_ACTION']; ?>?id=<?= $data_drv[$i]['iCompanyId']; ?>"
                                                                                   data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png"
                                                                                         alt="Edit">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission('update-status-store')) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="checkitemcount(<?= $data_drv[$i]['iCompanyId']; ?>, '<?= $data_drv[$i]['foodcatCount']; ?>', 'Inactive')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatus('<?= $data_drv[$i]['iCompanyId']; ?>', 'Active')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Deactivate">
                                                                                    <img src="img/inactive-icon.png"
                                                                                         alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-store')) { ?>
                                                                            <?php if (!in_array($data_drv[$i]['iCompanyId'], [$DEMO_NOT_DEL_COMPANY_ID])) { ?>
                                                                                <li class="entypo-gplus"
                                                                                    data-network="gplus">
                                                                                    <a href="javascript:void(0);"
                                                                                       onClick="changeStatusDeletestore('<?= $data_drv[$i]['iCompanyId']; ?>')"
                                                                                       data-toggle="tooltip"
                                                                                       title="Delete">
                                                                                        <img src="img/delete-icon.png"
                                                                                             alt="Delete">
                                                                                    </a>
                                                                                </li>
                                                                                <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <!-- <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" data-name="<?= clearCmpName($data_drv[$i]['vCompany']); ?>" data-id="<?= $data_drv[$i]['iCompanyId']; ?>" onClick="copyStoreData(this)"  data-toggle="tooltip" title="Copy"> <img src="img/right-green.png" alt="Copy" > </a></li> -->
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <?php if (scount($service_cat_data) > 1 && $isEnableAutoStoreOrder > 0) { ?>
                                                <td colspan="12"> No Records Found.</td>
                                            <?php } else if (scount($service_cat_data) > 1 || $isEnableAutoStoreOrder > 0) { ?>
                                                <td colspan="12"> No Records Found.</td>
                                            <?php } else { ?>
                                                <td colspan="10"> No Records Found.</td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> module will list
                        all <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> on this page.
                    </li>
                    <li>Admin can Activate / Deactivate / Delete
                        any <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>.
                        Default <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> cannot be Activated / Deactivated
                        / Deleted.
                    </li>
                    <li>Admin can export data in XLS format.</li>
                    <li>This module will list the <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> registered as
                        a <?php if (in_array(1, $catIds)) { ?>Food delivery<?php }
                        if (in_array(2, $catIds)) { ?>,Grocery Delivery<?php }
                        if (in_array(3, $catIds)) { ?>, Wine Delivery<?php } ?>.
                        <br/>
                        ( * As per the package selection Paid services data will be shown here.)
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/store.php" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="iCompanyId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?= $option; ?>">
    <input type="hidden" name="keyword" value="<?= $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="selectcategory" id="selectcategory" value="<?= $select_cat; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
</div>
</div>
<div class="modal fade" id="copy_store" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 id="storenametxt"></h4>
            </div>
            <div class="modal-body">
                <form name="_company_form" id="_company_form" method="post" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-12">
                            <label>Select Store :</label>
                        </div>
                        <input type="hidden" id="storeid" name="storeId">
                        <div class="col-lg-12">
                            <select class="form-control" multiple="multiple" name='store_sel[]' id="store_sel">
                                <?php for ($s = 0; $s < scount($getAllStore); $s++) { ?>
                                    <option value="<?= $getAllStore[$s]['iCompanyId']; ?>"><?= stripslashes(ucfirst($getAllStore[$s]['vCompany'])); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-lg-12">
                            <input type="submit" class="btn btn-default" name="copystore" id="copystore" value="Copy">
                            <a href="<?= $LOCATION_FILE_ARRAY['STORE.PHP']; ?>"
                               class="btn btn-default back_link">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once('footer.php'); ?>
<script src="../assets/js/modal_alert.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css"/>
<script>
    var AUTO_ACCEPT_COMPANY_ID = "";
    var AUTO_ACCEPT_STATUS = "";
    var AUTO_ACCEPT_BUTTON = "";
    var AUTO_ACCEPT_SESSIONID = "";
    var AUTO_ACCEPT_SERVICEID = "";
    languagedata = <?php echo $json_lang; ?>;

    function checkitemcount(id, countitem, status) {
        if (countitem == 0) {
            var retVal = confirm("This <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> has not added any items yet. Confirm to activate this <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>?");
            if (retVal == true) {
                $("#pageForm").attr("action", "action/store.php");
                changeStatus(id, status);
                return true;
            } else {
                $("#pageForm").attr("action", "javascript:void(0);");
                return false;
            }
        } else {
            changeStatus(id, status);
        }
    }

    function autoAcceptStatus(elem, btnId, event) {
        //added this code becoz in one pc in chrome disable btn is clicked..
        estatus = $(elem).attr("data-estatus");
        if (estatus != 'Active') {
            event.preventDefault();
            return false;
        }
        AUTO_ACCEPT_COMPANY_ID = $(elem).attr("data-id");
        AUTO_ACCEPT_SESSIONID = $(elem).attr("data-sessionId");
        AUTO_ACCEPT_SERVICEID = $(elem).attr("data-serviceId");
        var status = $(elem).attr("data-status");
        AUTO_ACCEPT_STATUS = $(elem).attr("data-status");
        AUTO_ACCEPT_BUTTON = btnId
        var changeStatus = "disable";
        var classname = "switch-on";
        if (AUTO_ACCEPT_STATUS == "Yes") {
            classname = "switch-off";
        }
        document.getElementById(btnId + "_" + AUTO_ACCEPT_COMPANY_ID).getElementsByTagName('div')[0].className = classname;
        if (status == "Yes") {
            changeStatus = "enable";
        }
        show_alert("Attention", 'Are you sure to update selected record(s)?', "Cancel", "Ok", "", function (btn_id) {
            var typed = AUTO_ACCEPT_BUTTON;
            if (btn_id == 1) {
                if (AUTO_ACCEPT_BUTTON == "eAvailable") {
                    $(".loader-default").show();
                    checkStoreAvailability(AUTO_ACCEPT_COMPANY_ID, AUTO_ACCEPT_STATUS, AUTO_ACCEPT_SESSIONID, AUTO_ACCEPT_SERVICEID);
                } else {
                    updateStoreAvailability(AUTO_ACCEPT_COMPANY_ID, AUTO_ACCEPT_STATUS, typed);
                }
            }
        }, true, true, true);
        return false;
    }

    function updateStoreAvailability(companyId, updateStatus, method) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>action/store.php',
            'AJAX_DATA': {iCompanyId: companyId, statusVal: updateStatus, method: method},
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                $(".loader-default").hide();
                location.reload();
            } else {
                // console.log(response.result);
            }
        });
    }

    function checkStoreAvailability(companyId, updateStatus, sessionId, serviceId) {
        var sendrequestparam = {
            "tSessionId": sessionId,
            "GeneralMemberId": companyId,
            "iCompanyId": companyId,
            "iServiceId": serviceId,
            "GeneralUserType": 'Company',
            "UserType": 'Company',
            "type": 'UpdateRestaurantAvailability',
            "eAvailable": updateStatus,
            "CALL_TYPE": "Update",
            "test": "1",
            'async_request': false
        };
        sendrequestparam = $.param(sendrequestparam);
        getDataFromApi(sendrequestparam, function (response) {
            response = JSON.parse(response);
            $(".loader-default").hide();
            if (response.Action == '1') {
                show_alert("<?= addslashes($langage_lbl_admin['LBL_ATTENTION']); ?>", languagedata[response.message], "<?= addslashes($langage_lbl_admin['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                        location.reload();
                    }
                }, true, true, true);
            } else {
                show_alert("<?= addslashes($langage_lbl_admin['LBL_ATTENTION']); ?>", languagedata[response.message], "<?= addslashes($langage_lbl_admin['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                        //location.reload();
                    }
                }, true, true, true);
                $("#accept_order").attr("data-status", updateStatus);
                //alert(response.message);
            }
        });
    }

    //Remove Comment When Enable Copy Store Functionality By HJ On 08-08-2020 Start
    /*$(document).ready(function () {
        $('#store_sel').multiselect({
            enableCaseInsensitiveFiltering: true,
            includeSelectAllOption: true,
            maxHeight: 400
        });
    });*/
    //Remove Comment When Enable Copy Store Functionality By HJ On 08-08-2020 End
    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        } else {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                this.checked = '';
            });
        }
    });
    $("#Search").on('click', function () {
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
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

    function show_company_details(companyid) {
        $("#comp_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');
        if (companyid != "") {
            var ajaxData = {
                'URL': "<?= $tconfig['tsite_url_main_admin'] ?>ajax_store_details.php",
                'AJAX_DATA': "iCompanyId=" + companyid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#comp_detail").html(data);
                    $("#imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons").hide();
                }
            });
        }
    }

    function status_check(status) {
        if (status == "Active") {
            var zero_values = "No";
            $("input[type=checkbox]:checked").each(function () {
                var cnt = $(this).attr('data-count');
                if (cnt == 0) {
                    zero_values = "Yes";
                    return false;
                }
            });
            if (zero_values == "No") {
                ChangeStatusAll(status);
                $('#new-msg-activeid').html("Are you sure to activate selected record(s)?");
            } else {
                ChangeStatusAll(status);
                $('#new-msg-activeid').html('This <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> has not added any items yet? Are you sure to activate selected record(s)?');
            }
        } else {
            ChangeStatusAll(status);
            $('#new-msg-activeid').html("Are you sure to activate selected record(s)?");
        }
    }

    function copyStoreData(elem) {
        var storeId = $(elem).attr("data-id");
        var storeName = $(elem).attr("data-name");
        $("#storenametxt").html('<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>Store Data Copy : ' + storeName);
        $("#storeid").val(storeId);
        $('#store_sel option[value="' + storeName + '"]').remove();
        $("#copy_store").modal('show');
    }
</script>
</body>
<!-- END BODY-->
</html>