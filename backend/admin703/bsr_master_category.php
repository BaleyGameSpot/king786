<?php
include_once('../common.php');
$tbl_name = "rent_items_category";
$eType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
if (!$userObj->hasPermission('view-service-category-' . strtolower($eType))) {
    $userObj->redirect();
}
if (!empty($eType)) {
    $iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eType, '', 'true');
    $catid = base64_encode(base64_encode($iMasterServiceCategoryId));
    $iMasterServiceCategoryId = base64_decode(base64_decode($catid));
    $eMasterType = $eType;
}
/*$catid = isset($_REQUEST['catid']) ? $_REQUEST['catid'] : "";

if(!empty($catid)){
    $iMasterServiceCategoryId = base64_decode(base64_decode($catid));
    $eMasterType = get_value('master_service_category', 'eType', 'iMasterServiceCategoryId', $iMasterServiceCategoryId, '', 'true');
}*/
$script = $eMasterType;
$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$iRentItemId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
$parentId = isset($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
$sub = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 0;
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$queryString = $parentId > 0 ? '?parentid=' . $parentId : '';
if (!empty($eType)) {
    $backurl = "bsr_master_category.php?parentid=$parentId&eType=" . $_REQUEST['eType'];
} else {
    $backurl = "bsr_master_category.php?parentid=" . $parentId;
}
if (!empty($iRentItemId) && !empty($status)) {
    if (SITE_TYPE != 'Demo') {
        $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iRentItemId  = '" . $iRentItemId . "'");
        /*if ($status == "Deleted") {
            $where = " iRentItemId = '$iRentItemId'";
            $query_p['eStatus'] = 'Deleted';

            $iRentItemIdNew1 = $obj->MySQLQueryPerform('rentitem_fields', $query_p, 'update', $where);

            $obj->sql_query("UPDATE `rent_item_fields_option` SET `eStatus` = 'Deleted' WHERE iRentFieldId = '" . $iRentItemIdNew1 . "'");
        }

        if ($status == "Active") {
            
            $where = " iRentItemId = '$iRentItemId'";
            $query_p['eStatus'] = 'Active';

            $iRentItemIdNew1 = $obj->MySQLQueryPerform('rentitem_fields', $query_p, 'update', $where);

            $obj->sql_query("UPDATE `rent_item_fields_option` SET `eStatus` = 'Active' WHERE iRentFieldId = '" . $iRentItemIdNew1 . "'");
        }*/
        header("Location:" . $backurl);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:" . $backurl);
        exit();
    }
}
$ssql = '';
if ($keyword != '') {
    if ($eStatus != '') {
        $ssql .= " AND (vTitle LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
    } else {
        $ssql .= " AND (vTitle LIKE '%" . clean($keyword) . "%')";
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
}
if (empty($eStatus)) {
    $ssql .= 'AND ( estatus = "Active" || estatus = "Inactive" )';
}
$ord = " ORDER BY iDisplayOrder ASC";
if ($sortby == 1) {
    $d = " SUBSTRING_INDEX(SUBSTRING_INDEX(vTitle,'vTitle_EN\":\"',-1),'\"',1)";
    if ($order == 0) $ord = " ORDER BY $d ASC"; else

        $ord = " ORDER BY $d DESC";
}
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY eStatus ASC";
    } else {
        $ord = " ORDER BY eStatus DESC";
    }
}
$var_filter = '';
$per_page = $DISPLAY_RECORD_NUMBER;
//$per_page = 1;
$total_results = $RENTITEM_OBJ->getRentItemTotalCount('admin', $parentId, $catid, $ssql);
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
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
if ($page <= 0) $page = 1;
//Pagination End
if ($iMasterServiceCategoryId != "") {
    $ssql .= " AND iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
} else {
    $ssql .= " AND iMasterServiceCategoryId = '0'";
}
if ($parentId > 0) {
    $master_service_categories = $RENTITEM_OBJ->getRentItemSubCategory('admin', $parentId, $ssql, $start, $per_page, $lang, $ord);
    $getrentitem = $RENTITEM_OBJ->getrentitem('admin', $parentId);
} else {
    $master_service_categories = $RENTITEM_OBJ->getRentItemMaster('admin', $ssql, $start, $per_page, $lang, $ord);
}
foreach ($master_service_categories as $key => $value) {
    $query = $RENTITEM_OBJ->getRentItemSubCategory('admin', $value['iRentItemId']);
    $master_service_categories[$key]['SubCategories'] = scount($query);
}

$endRecord = scount($master_service_categories);
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . "&parentid=" . $parentId . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?>
        | <?php if ($parentId != 0 && isset($getrentitem['vTitle']) && !empty($getrentitem['vTitle'])) {
        } else { ?><?php } ?> <?php if ($parentId == 0) {
            echo 'Categories';
        } ?><?php if ($parentId != 0 && isset($getrentitem['vTitle']) && !empty($getrentitem['vTitle'])) { ?> Sub Categories <?php } ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style type="text/css">
        .table > tbody > tr > td {

            vertical-align: middle;

        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?php if ($parentId != 0 && isset($getrentitem['vTitle']) && !empty($getrentitem['vTitle'])) {
                            } else { ?><?php } ?> <?php if ($parentId == 0) {
                                echo 'Categories';
                            } ?> <?php if ($parentId != 0 && isset($getrentitem['vTitle']) && !empty($getrentitem['vTitle'])) { ?> <?= @$getrentitem['vTitle'] ?> (Sub Categories) <?php } ?></h2>
                    </div>
                    <?php if ($parentId != 0) { ?>
                        <a href="bsr_master_category.php?parentid=0&eType=<?php echo $_REQUEST['eType']; ?>">
                            <input type="button" value="Back to Listing" class="add-btn">
                        </a>
                    <?php } ?>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <input type="hidden" name="parentid" value="<?php echo $parentId; ?>">
                        <?php if (!empty($eType)) { ?>
                            <input type="hidden" name="eType" value="<?= $_REQUEST['eType']; ?>"/>
                        <?php } ?>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <input type="hidden" name="option" id="option" value="">
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td width="13%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?>>Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?>>Inactive
                                </option>
                                <option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?>>Delete
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = '<?php echo $backurl; ?>'"/>
                            <?php if (!empty($master_service_categories)) { ?>
                            <button type="button" onclick="showExportTypes('bsr_master_category')" class="btnalt button11">Export</button>
                            <?php } ?>
                        </td>
                        <?php if ($userObj->hasPermission('create-service-category-' . strtolower($eType))) {
                            if ($eType != "") { ?>
                                <td width="30%">
                                    <a class="add-btn"
                                       href="bsr_master_category_action.php?parentid=<?= $parentId; ?>&eType=<?= $_REQUEST['eType'] ?>"
                                       style="text-align: center;">Add <?php if ($parentId != 0) {
                                            echo 'Sub ';
                                        } ?> Category
                                    </a>
                                </td>
                            <?php } else { ?>
                                <td width="30%">
                                    <a class="add-btn" href="bsr_master_category_action.php?parentid=<?= $parentId; ?>"
                                       style="text-align: center;">Add <?php if ($parentId != 0) {
                                            echo 'Sub ';
                                        } ?> Category
                                    </a>
                                </td>
                            <?php }
                        } ?>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div style="clear:both;"></div>
                        <div class="table-responsive1">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <?php if(!empty($parentId)){ ?>
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if ($parentId == 0) { ?>
                                            <th style="width: 100px; text-align: center;">Icon</th>
                                        <?php } ?>
                                        <th>
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Title<?php
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
                                        <?php if ($parentId == 0 && ($eMasterType != "RentEstate" && $eMasterType != "RentCars")) { ?>
                                            <th style="width: 200px; text-align: center;">SubCategories</th>
                                        <?php } ?>
                                        <th style="width: 10%; text-align: center;">Display Order</th>
                                        <th style="width: 10%; text-align: center;">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status<?php
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
                                        <?php if ($userObj->hasPermission(['update-service-category-'. strtolower($eType) , 'delete-service-category-'. strtolower($eType) ,'update-status-service-category-' . strtolower($eType) ])) { ?>
                                        <th style="width: 10%; text-align: center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($master_service_categories) && scount($master_service_categories) > 0) {
                                        foreach ($master_service_categories as $service_category) {
                                            $iRentItemId = $service_category['iRentItemId'];
                                            $eStatus_ = $service_category['eStatus'];
                                            $vIconImage = $service_category['vImage'];
                                            ?>
                                            <tr>
                                                <?php if ($parentId == 0) { ?>
                                                    <td style="text-align: center;">
                                                        <?php if ($vIconImage != "") { ?>
                                                            <img src="<?= $tconfig['tsite_upload_images_rent_item'] . $vIconImage ?>"
                                                                 style="width: 35px">
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                                <td><?= $service_category['vTitle'] ?></td>
                                                <?php if ($parentId == 0 && ($eMasterType != "RentEstate" && $eMasterType != "RentCars")) { ?>
                                                    <td style="text-align: center;">
                                                        <a class="add-btn-sub"
                                                           href="bsr_master_category.php?parentid=<?= $iRentItemId; ?>&eType=<?= $_REQUEST['eType']; ?>"
                                                           target="_blank">Add/View
                                                            (<?= $service_category['SubCategories']; ?>)
                                                        </a>
                                                    </td>
                                                <?php } else { ?>
                                                    <?php if ($parentId == 0 && (empty($iMasterServiceCategoryId) && empty($eMasterType))) { ?>
                                                        <td style="text-align: center;">
                                                            ---
                                                        </td>
                                                    <?php } ?>
                                                <?php } ?>
                                                <td style="text-align: center;"><?= $service_category['iDisplayOrder'] ?></td>
                                                <td style="text-align: center;">
                                                    <?php
                                                    if ($service_category['eStatus'] == 'Active') {
                                                        $status_img = "img/active-icon.png";
                                                    } else if ($service_category['eStatus'] == 'Inactive') {
                                                        $status_img = "img/inactive-icon.png";
                                                    } else {
                                                        $status_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip"
                                                         title="<?= $service_category['eStatus']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission(['update-service-category-'. strtolower($eType) , 'delete-service-category-'. strtolower($eType) ,'update-status-service-category-' . strtolower($eType) ])) { ?>
                                                

                                                <td align="center" style="text-align:center;" class="action-btn001">
                                                    <?php if ($userObj->hasPermission('update-status-service-category-' . strtolower($eType))) { ?>
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export">
                                                                <span><img src="images/settings-icon.png" alt=""></span>
                                                            </label>
                                                            <div class="social show-moreOptions for-two openPops_<?= $iRentItemId; ?>">
                                                                <ul>
                                                                    <li class="entypo-twitter" data-network="twitter">
                                                                        <?php if ($eType != "") { ?>
                                                                            <a href="bsr_master_category_action.php?id=<?= $iRentItemId; ?>&eType=<?= $_REQUEST['eType'] ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        <?php } else { ?>
                                                                            <a href="bsr_master_category_action.php?id=<?= $iRentItemId; ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        <?php } ?>
                                                                    </li>
                                                                    <li class="entypo-facebook" data-network="facebook">
                                                                        <?php if ($eType != "") { ?>
                                                                            <a href="javascript:void(0);"
                                                                               onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Active&eType=<?= $_REQUEST['eType'] ?>'"
                                                                               data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png"
                                                                                     alt="<?= $eStatus_; ?>">
                                                                            </a>
                                                                        <?php } else { ?>
                                                                            <a href="javascript:void(0);"
                                                                               onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Active&eType=<?= $_REQUEST['eType'] ?>'"
                                                                               data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png"
                                                                                     alt="<?= $eStatus_; ?>">
                                                                            </a>
                                                                        <?php } ?>
                                                                    </li>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <?php if ($eType != "") { ?>
                                                                            <a href="javascript:void(0);"
                                                                               onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Inactive&eType=<?= $_REQUEST['eType'] ?>'"
                                                                               data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png"
                                                                                     alt="<?= $eStatus_; ?>">
                                                                            </a>
                                                                        <?php } else { ?>
                                                                            <a href="javascript:void(0);"
                                                                               onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Inactive&eType=<?= $_REQUEST['eType'] ?>'"
                                                                               data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png"
                                                                                     alt="<?= $eStatus_; ?>">
                                                                            </a>
                                                                        <?php } ?>
                                                                    </li>
                                                                    <?php if ($userObj->hasPermission('delete-service-category-' . strtolower($eType)) && $service_category['eStatus'] != 'Deleted') { ?>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <?php if ($eType != "") { ?>
                                                                                <!-- <a href="javascript:void(0);"
                                                                                   onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Deleted&eType=<?= $eType ?>'"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a> -->
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatusDelete('<?= $iRentItemId; ?>','<?= $parentId ?>','<?= $_REQUEST['eType'] ?>')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            <?php } else { ?>
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Deleted&eType=<?= $_REQUEST['eType'] ?>'"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            <?php } ?>
                                                                        </li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    <?php } else { ?>
                                                        <a href="bsr_master_category_action.php?id=<?= $iRentItemId; ?>"
                                                           data-toggle="tooltip" title="Edit">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                                <?php } ?>
                                                
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="5">No records found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table> 
                                <?php } else { ?>
                                <div class="table table-striped  table-hover">
                                    <div class="profile-earning">
                                        <div class="partation">
                                            <ul style="padding-left: 0px;" class="setings-list">
                                                <?php
                                               if (!empty($master_service_categories) && scount($master_service_categories) > 0) {
                                                     foreach ($master_service_categories as $service_category) {
                                                        
                                                        $iRentItemId = $service_category['iRentItemId'];
                                                        $eStatus_ = $service_category['eStatus'];
                                                        $vIconImage = $service_category['vImage'];
                                                        
                                                        $buttonStatus = $service_category['eStatus'];
                                                        $btnChecked = 0;
                                                        if ($buttonStatus == "Active") {
                                                            $btnChecked = 1;
                                                        }
                                                        //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 End
                                                        ?>
                                                        <li>
                                                            <div class="toggle-list-inner">
                                                                <div class="toggle-combo">
                                                                    <label>
                                                                       <?php if ($parentId == 0) { ?>
                                                                        <div align="center">
                                                                            <?php if ($vIconImage != "") { ?>
                                                                            <img src="<?= $tconfig['tsite_upload_images_rent_item'] . $vIconImage ?>" style="width:100px;">
                                                                            <?php } ?>
                                                                        </div>
                                                                        <?php } ?>

                                                                        <div style="margin: 0 0 0 10px;">
                                                                            <td><?= $service_category['vTitle'] ?></td>
                                                                        </div>
                                                                    </label>
                                                                    <?php if ($userObj->hasPermission('update-status-service-category-' . strtolower($eType))) { ?>
                                                                        <span class="toggle-switch">
                                                                            <input type="checkbox" <?php if ($btnChecked > 0) { ?> checked="" <?php } ?>  id="statusbutton" class="chk statusbutton" name="statusbutton"  onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=<?= ($btnChecked > 0) ? "Inactive" : "Active" ?>&eType=<?= $_REQUEST['eType'] ?>'" data-toggle="tooltip"title="<?= ($btnChecked > 0) ? "Active" : "Inactive" ?> " >
                                                                            <span class="toggle-base"></span>
                                                                        </span>
                                                                    <?php } ?>
                                                                </div>
                                                                <div class="check-combo">
                                                                    <label id="defaultText_246">
                                                                        <ul>
                                                                            <?php if($userObj->hasPermission(['update-service-category-'. strtolower($eType) , 'delete-service-category-'. strtolower($eType) ,'update-status-service-category-' . strtolower($eType) ])) { ?>
                                                                            <?php if($userObj->hasPermission('update-status-service-category-' . strtolower($eType))) { ?>
                                                                                <?php if(!empty($eType)) { ?>
                                                                                <li class="entypo-facebook" data-network="facebook">
                                                                                    <a href="javascript:void(0);" onClick="changeStatusDelete('<?= $iRentItemId; ?>','<?= $parentId ?>','<?= $_REQUEST['eType'] ?>')"
                                                                                       data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-new.png" alt="Delete">
                                                                                    </a>
                                                                                </li>
                                                                                <li class="entypo-twitter" data-network="twitter">
                                                                                        <a href="bsr_master_category_action.php?id=<?= $iRentItemId; ?>&eType=<?= $_REQUEST['eType'] ?>" data-toggle="tooltip" title="Edit">
                                                                                            <img src="img/edit-new.png" alt="Edit">
                                                                                        </a>
                                                                                </li>
                                                                                <?php } else { ?>
                                                                                <li class="entypo-facebook" data-network="facebook">
                                                                                    <a href="javascript:void(0);"  onClick="window.location.href='bsr_master_category.php?id=<?= $iRentItemId; ?>&parentid=<?= $parentId ?>&status=Deleted&eType=<?= $_REQUEST['eType'] ?>'" data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-new.png" alt="Delete">
                                                                                    </a>
                                                                                </li>
                                                                                <li class="entypo-twitter" data-network="twitter">
                                                                                        <a href="bsr_master_category_action.php?id=<?= $iRentItemId; ?>" data-toggle="tooltip" title="Edit">
                                                                                            <img src="img/edit-new.png" alt="Edit">
                                                                                        </a>
                                                                                </li>
                                                                                <?php } ?>
                                                                            <?php } ?>
                                                            
                                                                            <?php } else { ?>
                                                                                <li class="entypo-twitter" data-network="twitter">
                                                                                    <a href="bsr_master_category_action.php?id=<?= $iRentItemId; ?>" data-toggle="tooltip" title="Edit">
                                                                                        <img src="img/edit-new.png" alt="Edit">
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                            <?php if ($parentId == 0 && ($eMasterType != "RentEstate" && $eMasterType != "RentCars")) { ?>
                                                                                <li class="entypo-gplus" data-network="gplus">
                                                                                    <a  href="bsr_master_category.php?parentid=<?= $iRentItemId; ?>&eType=<?= $_REQUEST['eType']; ?>" target="_blank" data-toggle="tooltip" title="<?php echo "SubCategories (".$service_category['SubCategories'].")"; ?>">
                                                                                            <img src="img/view-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                                        </a>
                                                                                </li>
                                                                            <?php }  ?>
                                                                        </ul>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php } ?>
                                            </ul>
                                        </div>
                                    </div><?php
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="5"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                </div>
                                <?php  } ?>
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
                    <?php if ($parentId == 0) { ?>
                        <li>Administrator can Activate / Deactivate / Delete OR Modify any categories.</li>
                    <?php } else { ?>
                        <li>Administrator can Activate / Deactivate / Delete OR Modify any Sub categories.</li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="action" value="<?= $action; ?>">
    <input type="hidden" name="searchPaymentPlan" value="<?= $searchPaymentPlan; ?>">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?= $vStatus; ?>">
    <input type="hidden" name="eType" value="<?= $_REQUEST['eType']; ?>">
    <input type="hidden" name="iTripId" id="iMainId01" value="">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="keyword" value="<?= $keyword?>">
    <input type="hidden" name="eStatus" value="<?= $eStatus?>">
    <input type="hidden" name="parent_id" value="<?php echo $_REQUEST['parentid']; ?>">
</form>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script>
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

    $("#Search").on('click', function () {

        //$('html').addClass('loading');

        var action = $("#_list_form").attr('action');

        //alert(action);

        var formValus = $("#frmsearch").serialize();

        window.location.href = action + "?" + formValus;

    });

    function changeStatusDelete(iRentItemId, parentId, eType) {
        $('#is_dltSngl_modal').modal('show');
        $(".action_modal_submit").unbind().click(function () {
            window.location.href = 'bsr_master_category.php?id=' + iRentItemId + '&parentid=' + parentId + '&status=Deleted&eType=' + eType + ''
        });
    }
</script>
</body>
<!-- END BODY-->
</html>