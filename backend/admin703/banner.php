<?php
include_once '../common.php';
$script = 'Banners';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$ParameterUrl = $ssqlnearby = $ssqlbuyanyservice = "";
$permission_banner = "app-home-screen-banner";
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

if (isset($_REQUEST['eType']) && in_array($_REQUEST['eType'], ['Genie','Runner','Anywhere']) && $MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
    $ssqlbuyanyservice = " AND eType = '" . $_REQUEST['eType'] . "' ";
    if ($_REQUEST['eType'] == "Genie" || $_REQUEST['eType'] == "Anywhere") {
        $ssqlbuyanyservice = " AND eType = 'Genie' ";
    }
    $script = $_REQUEST['eType'] . '_banner';
    $ParameterUrl = '&eType=' . $_REQUEST['eType'];
    $permission_banner = "banner-genie-delivery";
    if ($_REQUEST['eType'] == "Runner") {
        $permission_banner = "banner-runner-delivery";
    }
} elseif (isset($_REQUEST['eForService']) && in_array($_REQUEST['eForService'], ['MoreDelivery', 'ServiceProvider', 'Bidding'])) {
    $eForBanner = isset($_REQUEST['eFor']) ? $_REQUEST['eFor'] : '';
    $eTypeBanner = "";
    $iVehicleCategoryIdSql = "";
    $script = 'MoreDelivery_banner';
    if ($eForBanner == "DeliveryCategory") {
        $eTypeBanner = "Deliver";
        $permission_banner = "banner-parcel-delivery";
    } elseif ($eForBanner == "DeliverAllCategory") {
        $eTypeBanner = "DeliverAll";
        $permission_banner = "banner-store";
    } elseif ($eForBanner == "UberX" || $eForBanner == "VideoConsult" || $eForBanner == "Bidding") {
        $eTypeBanner = $eForBanner;
        $iVehicleCategoryId = $_REQUEST['iVehicleCategoryId'];
        $iVehicleCategoryIdSql = " AND iVehicleCategoryId = '$iVehicleCategoryId' ";
        $eForBanner .= "&iVehicleCategoryId=" . $iVehicleCategoryId;
        $_REQUEST['eFor'] .= "&iVehicleCategoryId=" . $iVehicleCategoryId;
        // $permission_banner = "banner-uberx";
        $script = 'VehicleCategory_' . $eTypeBanner;
        if($eTypeBanner == "Bidding") {
            $script = 'bidding';
        }
    }
    $ssqlbuyanyservice = " AND eType = '$eTypeBanner' $iVehicleCategoryIdSql ";
    
    $ParameterUrl = '&eFor=' . $eForBanner . '&eForService=' . $_REQUEST['eForService'];
} elseif (isset($eType) && in_array($eType, ['NearBy'])) {
    $ssqlbuyanyservice = " AND eType = 'NearBy' ";
    $script = 'NearBy_banner';
    $ParameterUrl = '&eFor=NearBy&eType=NearBy';
    $permission_banner = "banners-" . strtolower($eType);
} else {
    $ssqlbuyanyservice = " AND eType = 'General'";
}
$permission_banner_view = "view-" . $permission_banner;
$permission_banner_create = "create-" . $permission_banner;
$permission_banner_edit = "edit-" . $permission_banner;
$permission_banner_delete = "delete-" . $permission_banner;
$permission_banner_update_status = "update-status-" . $permission_banner;
if (!$userObj->hasPermission($permission_banner_view)) {
    $userObj->redirect();
}
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
//Delete
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$vCodeDlt = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : '';
// Update eStatus
$iUniqueId = isset($_GET['iUniqueId']) ? $_GET['iUniqueId'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
//sort order
$flag = isset($_GET['flag']) ? $_GET['flag'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$pro = isset($_GET['pro']) ? $_GET['pro'] : '';
$tbl_name = 'banners';
$per_page = $DISPLAY_RECORD_NUMBER;
$langSearch = $default_lang;
if ($vCodeDlt != "") {
    $langSearch = $vCodeDlt;
}
if (!empty($_REQUEST['langSearch'])) {
    $langSearch = $_REQUEST['langSearch'];
}
$langsql = " AND vCode = '" . $langSearch . "'";
$whereserviceId = " AND iServiceId = 0";
$eBuyAnyService = (isset($_REQUEST['eType']) && in_array($_REQUEST['eType'], [
        'Genie',
        'Runner',
        'Anywhere'
    ])) ? 'eType=' . $_REQUEST['eType'] : '';
$eForService = (isset($_REQUEST['eForService']) && in_array($_REQUEST['eForService'], ['MoreDelivery', 'ServiceProvider', 'Bidding'])) ? 'eForService=' . $_REQUEST['eForService'] . '&eFor=' . $_REQUEST['eFor'] : '';
$vCodeLang = $vCodeDlt;
if ($vCodeDlt == "") {
    $vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
}
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
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
        if ($eStatus != '') {
            $ssql .= " AND vTitle LIKE '%" . clean($keyword_new) . "%' AND eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND vTitle = '" . clean($keyword_new) . "' AND eStatus = '" . clean($eStatus) . "'";
            }
        } else {
            $ssql .= " AND vTitle LIKE '%" . clean($keyword_new) . "%'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND vTitle = '" . clean($keyword_new) . "'";
            }
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND vTitle LIKE '%" . clean($keyword_new) . "%' AND eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND vTitle = '" . clean($keyword_new) . "' AND eStatus = '" . clean($eStatus) . "'";
            }
        } else {
            $ssql .= " AND vTitle LIKE '%" . clean($keyword_new) . "%' ";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND vTitle = '" . clean($keyword_new) . "' AND eStatus = '" . clean($eStatus) . "'";
            }
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
}
//delete record
if ($hdn_del_id != '') {
    if (SITE_TYPE != 'Demo') {
        $data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `" . $tbl_name . "` WHERE 1=1 AND vCode = '" . $vCodeLang . "' $whereserviceId $ssqlbuyanyservice";
        $data_rec = $obj->MySQLSelect($data_q);
        $order = isset($data_rec[0]['iDisplayOrder']) ? $data_rec[0]['iDisplayOrder'] : 0;
        $data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId = '" . $hdn_del_id . "' AND vCode = '" . $vCodeLang . "'$whereserviceId $ssqlbuyanyservice");
        if (scount($data_logo) > 0) {
            $iDisplayOrder = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
            $obj->sql_query("DELETE FROM `" . $tbl_name . "` WHERE iUniqueId = '" . $hdn_del_id . "' AND vCode = '" . $vCodeLang . "'$whereserviceId $ssqlbuyanyservice");
            if ($iDisplayOrder < $order) {
                for ($i = $iDisplayOrder + 1; $i <= $order; $i++) {
                    $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i . " AND vCode = '" . $vCodeLang . "'" . $whereserviceId . $ssqlbuyanyservice);
                }
            }
        }
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        header("Location:banner.php?langSearch=" . $vCodeLang.$ParameterUrl);
        exit();
    } else {
        $_SESSION['success'] = '2';
        header("Location:banner.php?langSearch=" . $vCodeLang.$ParameterUrl);
        exit();
    }
}
if (!empty($id) && $id != 0) {
    if (SITE_TYPE != 'Demo') {
        $updateSql = '';
        if ($eType == 'NearBy') {
            $updateSql = " AND eType IN ('NearBy')";
        } else if ($eType == 'Genie') {
            $updateSql = " AND eType IN ('Genie')";
        } else if ($eType == 'Runner') {
            $updateSql = " AND eType IN ('Runner')";
        } elseif (isset($_REQUEST['eFor'])) {
            if(!empty($_REQUEST['eFor'])) {
                if($_REQUEST['eFor'] != "DeliveryCategory") {
                    $updateSql = " AND eType IN ('" . $_REQUEST['eFor'] . "')";
                } else {
                    $updateSql = " AND eType IN ('Deliver')";
                }
            } else {
                $updateSql = " AND eType IN ('General')";
            }
        }

        if(isset($_REQUEST['iVehicleCategoryId'])) {
            $updateSql .= " AND iVehicleCategoryId = '" . $_REQUEST['iVehicleCategoryId'] . "'";
        }

        if ($flag == 'up') {
            $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId ='" . $id . "' AND vCode = '" . $vCodeLang . "' $whereserviceId $ssqlbuyanyservice AND eFor = 'General'");
            $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
            $val = $order_data - 1;
            if ($val > 0) {
                $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $order_data . "' WHERE eType NOT IN ('RentItem','RentEstate','RentCars') " . $updateSql . " AND  iDisplayOrder='" . $val . "' AND vCode = '" . $vCodeLang . "' $whereserviceId $ssqlbuyanyservice");
                $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $val . "' WHERE eType NOT IN ('RentItem','RentEstate','RentCars') " . $updateSql . " AND iUniqueId = '" . $id . "' AND vCode = '" . $vCodeLang . "'$whereserviceId $ssqlbuyanyservice");
            }
        } else if ($flag == 'down') {
            $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId ='" . $id . "' AND vCode = '" . $vCodeLang . "'$whereserviceId $ssqlbuyanyservice AND eFor = 'General'");
            $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
            $val = $order_data + 1;
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $order_data . "' WHERE eType NOT IN ('RentItem','RentEstate','RentCars') " . $updateSql . " AND iDisplayOrder='" . $val . "' AND vCode = '" . $vCodeLang . "' $whereserviceId $ssqlbuyanyservice");
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $val . "' WHERE eType NOT IN ('RentItem','RentEstate','RentCars') " . $updateSql . " AND iUniqueId = '" . $id . "' AND vCode = '" . $vCodeLang . "'$whereserviceId $ssqlbuyanyservice");
        }
        header("Location:banner.php?langSearch=" . $vCodeLang . $ParameterUrl);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:banner.php?langSearch=" . $vCodeLang . $ParameterUrl);
        exit();
    }
}
if ($iUniqueId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE `" . $tbl_name . "` SET eStatus = '" . $status . "' WHERE iUniqueId = '" . $iUniqueId . "' AND vCode = '" . $vCodeLang . "'$whereserviceId";
        $obj->sql_query($query);
        header("Location:banner.php?langSearch=" . $vCodeLang . $ParameterUrl);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:banner.php?langSearch=" . $vCodeLang . $ParameterUrl);
        exit();
    }
}
$db_dataAll = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE 1 $whereserviceId $langsql $ssql $ssqlbuyanyservice AND eFor = 'General' ORDER BY iDisplayOrder");
$total_results = scount($db_dataAll);
$total_pages = ceil($total_results / $per_page);//total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
$db_data = array_slice($db_dataAll, $start, $per_page);
$endRecord = scount($db_data);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$db_langdata = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active' ORDER BY iDispOrder");

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8">
<![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9">
<![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Home Page Banners</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once 'global_files.php'; ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once 'header.php'; ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Home Page Banners</h2>
                    <?php if ($userObj->hasPermission($permission_banner_create)) {
                        if ($langSearch != "") {
                            if ($eBuyAnyService != "") {
                                $add_banner = "?vCode=" . $langSearch;
                            } else {
                                $add_banner = "?vCode=" . $langSearch;
                            }
                        }
                        ?>
                        <a href="banner_action.php<?php echo $add_banner; ?><?= ($eBuyAnyService != "") ? '&' . $eBuyAnyService : (($eForService != "") ? '&' . $eForService : '') ?><?= (in_array($eType, [
                            "NearBy",
                            "Runner",
                            "Genie"
                        ])) ? '&eType=' . $eType : ''; ?>">
                            <input type="button" value="Add Banner" class="add-btn">
                        </a>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <?php include 'valid_msg.php'; ?>
            <?php /*if (scount($db_langdata) > 1) { ?>
                <div class="row">
                    <div class="col-lg-12">
                        <form action="" method="POST" name="frm_searchlang" class="form-inline">
                            <div class="form-group">
                                <label>Select Language: </label>
                                <select name="langSearch" class="form-control" style="width: auto; margin-right: 5px;">
                                    <? foreach ($db_langdata as $key => $value) { ?>
                                        <option value="<?= $value['vCode'] ?>" <? if ($value['vCode'] == $langSearch) echo "selected"; ?>><?= $value['vTitle'] ?></option>
                                    <? } ?>
                                </select>
                            </div>
                            <button type="submit" name="btn_search" id="btn_search" class="btn btn-default">Search</button>
                        </form>
                    </div>
                </div>
            <?php }*/ ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="10%" class=" padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="Title" <?php
                                if ($option == "Title") {
                                    echo "selected";
                                }
                                ?> >Title
                                </option>
                            </select>
                        </td>
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword"
                                   value="<?php echo $keyword; ?>" class="form-control"/>
                        </td>
                        <td width="13%" class="estatus_options" id="eStatus_options">
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
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'banner.php'"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Banner
                            </div>
                            <div class="panel-body">
                                <div>
                                    <table class="table responsive table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th width="10%" style="text-align:center;">Image</th>
                                                <th width="15%">Title</th>
                                                <!-- <th width="8%" style="text-align:center;">Language</th> -->
                                                <th  width="8%" style="text-align:center;">Display Order</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $count_all = scount($db_data);
                                        if ($count_all > 0) {
                                            $db_dataCnt = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE 1 $whereserviceId $langsql $ssqlbuyanyservice");
                                            $countData = scount($db_dataCnt);

                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vTitle = $db_data[$i]['vTitle'];
                                                $vImage = $db_data[$i]['vImage'];
                                                $vCode = $db_data[$i]['vCode'];
                                                $iDisplayOrder = $db_data[$i]['iDisplayOrder'];
                                                $eStatus = $db_data[$i]['eStatus'];
                                                $iUniqueId = $db_data[$i]['iUniqueId'];
                                                $checked = ($eStatus == "Active") ? 'checked' : '';
                                                ?>
                                                <tr class="gradeA">
                                                    <td width="10%" align="center">
                                                        <?php if ($vImage != '' && file_exists($tconfig['tsite_upload_images_panel'] . '/' . $vImage)) { ?>
                                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=100&src=' . $tconfig['tsite_upload_images'] . $vImage; ?>"
                                                                 height="50">
                                                            <?php
                                                        } else {
                                                            echo $vImage;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= $vTitle; ?></td>
                                                    <!-- <td align="center" width="15%"><?= $vCode; ?></td> -->
                                                    <td align="center" width="15%" >
                                                        <?php
                                                        if ($countData == 1) {
                                                            echo "-";
                                                        } else {
                                                            if ($iDisplayOrder != 1) { ?>
                                                                <a href="banner.php?id=<?= $iUniqueId; ?>&flag=up&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?><?= (isset($eType) && in_array($eType, ["NearBy", "Runner", "Genie" ])) ? '&eType=' . $eType : ''; ?><?= (isset($_REQUEST['eForService']) && in_array($_REQUEST['eForService'], ["MoreDelivery"])) ? '&eForService=' . $_REQUEST['eForService'] : ''; ?><?= (isset($_REQUEST['eFor']) && in_array($_REQUEST['eFor'], ["DeliveryCategory"])) ? '&eFor=' . $_REQUEST['eFor'] : ''; ?>">
                                                                    <button class="btn btn-warning">
                                                                        <i class="icon-arrow-up"></i>
                                                                    </button>
                                                                </a>
                                                            <?php }
                                                            if ($iDisplayOrder != $countData) { ?>
                                                                <a href="banner.php?id=<?= $iUniqueId; ?>&flag=down&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?><?= (isset($eType) &&  in_array($eType, ["NearBy", "Runner", "Genie" ])) ? '&eType=' . $eType : ''; ?><?= (isset($_REQUEST['eForService']) &&  in_array($_REQUEST['eForService'], ["MoreDelivery"])) ? '&eForService=' . $_REQUEST['eForService'] : ''; ?><?= (isset($_REQUEST['eFor']) &&  in_array($_REQUEST['eFor'], [
                                                                    "DeliveryCategory"])) ? '&eFor=' . $_REQUEST['eFor'] : ''; ?>">
                                                                    <button class="btn btn-warning">
                                                                        <i class="icon-arrow-down"></i>
                                                                    </button>
                                                                </a>
                                                            <?php }
                                                        } ?>
                                                    </td>
                                                   
                                                    <td width="10%"  align="center">
                                                        <?
                                                        if ($eStatus == 'Active') {
                                                            $dis_img = "img/active-icon.png";
                                                        } else if ($eStatus == 'Inactive') {
                                                            $dis_img = "img/inactive-icon.png";
                                                        } else if ($eStatus == 'Deleted') {
                                                            $dis_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $dis_img; ?>" alt="<?= $eStatus; ?>" data-toggle="tooltip" title="<?= $eStatus; ?>">
                                                    </td>

                                                    <td width="10%" align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class" style="display: block;">
                                                            <label class="entypo-export"><span><img src="images/settings-icon.png"  alt=""></span></label>
                                                            <div class="social show-moreOptions openPops_<?= $iUniqueId; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission($permission_banner_edit)) { ?>
                                                                    <li class="entypo-twitter" data-network="twitter">
                                                                        <a href="banner_action.php?id=<?= $iUniqueId; ?>&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?><?= ($eBuyAnyService != "") ? '&' . $eBuyAnyService : (($eForService != "") ? '&' . $eForService : '') ?>" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                        </a></li>
                                                                    <?php }  ?>
                                                                    <?php if ($userObj->hasPermission($permission_banner_update_status)) { ?>
                                                                        <li class="entypo-facebook" data-network="facebook"> 
                                                                            <a href="javascript:void(0);" onClick='window.location.href="banner.php?iUniqueId=<?= $iUniqueId; ?>&status=Active<?= ($eBuyAnyService != "") ? '&' . $eBuyAnyService : (($eForService != "") ? '&' . $eForService : '') ?>&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?><?= (in_array($eType, ["NearBy","Runner","Genie"])) ? '&eType=' . $eType : ''; ?>"' data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png" alt="<?php echo $eStatus; ?>">
                                                                            </a>
                                                                        </li>
                                                                        <li class="entypo-gplus" data-network="gplus">

                                                                            <a href="javascript:void(0);" onClick='window.location.href="banner.php?iUniqueId=<?= $iUniqueId; ?>&status=Inactive<?= ($eBuyAnyService != "") ? '&' . $eBuyAnyService : (($eForService != "") ? '&' . $eForService : '') ?>&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?><?= (in_array($eType, ["NearBy","Runner","Genie"])) ? '&eType=' . $eType : ''; ?>"' data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>">
                                                                            </a>

                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($userObj->hasPermission($permission_banner_delete)) {  ?>
                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="confirm_delete('<?= $iUniqueId; ?>','<?= $vCode; ?>','<?= ($eBuyAnyService != "") ? '&' : $eBuyAnyService?>','<?= (($eForService != "") ? '&' . $eForService : '') ?>','<?= $eType ?>');" data-toggle="tooltip"  title="Delete">
                                                                                <img src="img/delete-icon.png"   alt="Delete">
                                                                            </a></li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php //include('pagination_n.php'); ?>
                            </div>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iFaqcategoryId" id="iFaqcategoryId" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">
</form>
<?php include_once 'footer.php'; ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script>
    $(document).ready(function () {

        $('#dataTables-example').dataTable({

            //null,

            "aoColumns": [

                {"bSortable": false},

                null,

                {"bSortable": false},

                {"bSortable": false},

                // null,

                {"bSortable": false},
            ],
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all"
            }]

        });

    });

    $("#Search").on('click', function () {
        var formValus = $("#frmsearch").serialize();
        window.location.href = "banner.php?" + formValus;
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


    function confirm_delete(iUniqueId,vCode,eBuyAnyService,eForService,eType) {

        var confirm_ans = confirm("Are You sure You want to Delete Banner?");

        if (confirm_ans == true) {
            window.location.href = 'banner.php?hdn_del_id='+iUniqueId+'&vCode='+vCode+eBuyAnyService+eForService+'&eType='+eType;
        }
    }


</script>
</body>
<!-- END BODY-->
</html>