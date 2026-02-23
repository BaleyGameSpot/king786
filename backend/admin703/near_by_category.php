<?php
include_once('../common.php');
$script = 'nearbyCategory';
$tbl_name = "nearby_category";
if (!$userObj->hasPermission('view-category-nearby')) {
    $userObj->redirect();
}
$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$iNearByCategoryId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
//$iNearByCategoryId = isset($_REQUEST['iNearByCategoryId']) ? $_REQUEST['iNearByCategoryId'] : '';

$queryString = (isset($parentId) && $parentId > 0) ? '?parentid=' . $parentId : '';
if (!empty($iNearByCategoryId) && !empty($status)) {
    if (SITE_TYPE != 'Demo') {
        $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iNearByCategoryId  = '" . $iNearByCategoryId . "'");
        header("Location:near_by_category.php" . $queryString);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:near_by_category.php");
        exit();
    }
}
$var_filter = '';
$per_page = $DISPLAY_RECORD_NUMBER;
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


$ord = " ORDER BY iDisplayOrder ASC";
if ($sortby == 1) {
    $d = " SUBSTRING_INDEX(SUBSTRING_INDEX(vTitle,'vTitle_EN\":\"',-1),'\"',1)";
    if ($order == 0)
        $ord = " ORDER BY $d ASC";
    else
        $ord = " ORDER BY $d DESC";
}
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY eStatus ASC";
    } else {
        $ord = " ORDER BY eStatus DESC";
    }
}
//$per_page = 1;
$total_results = $NEARBY_OBJ->getNearByCategoryTotalCount('admin', $ssql);


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
if ($page <= 0)
    $page = 1;
//Pagination End
$master_service_categories = $NEARBY_OBJ->getNearByCategory('admin', $ssql, $start, $per_page, $lang, $ord);
$endRecord = scount($master_service_categories);
//$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . "&parentid=" . $parentId . $var_filter;

?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | NearBy Places Category</title>
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
                        <h2> NearBy Places Category</h2>
                    </div>
                    <?php if (isset($parentId) && $parentId != 0) { ?>
                        <a href="near_by_category.php">
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
                        <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                        <input type="hidden" name="option" id="option" value="">
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" class="form-control"/>
                        </td>
                        <td width="13%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?>>Active </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?>>Inactive </option>
                                <option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?>>Delete </option>
                            </select>
                        </td>
                        <td >
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"  onClick="window.location.href = 'near_by_category.php'"/>
                        </td>
                        <?php
                        if ($userObj->hasPermission('create-category-nearby')) { ?>
                            <td width="30%">
                                <a class="add-btn" href="near_by_category_action.php" style="text-align: center;">Add NearBy Category</a>
                            </td>
                        <?php } ?>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th width="8%"  style="text-align: center;">Icon</th>
                                        <th width="30%"  ><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else { ?> 0 <?php } ?>)"> Category Name <?php
                                            if ($sortby == 1) {
                                                if ($order == 0) {
                                                    ?><i class="fa fa-sort-amount-asc"
                                                         aria-hidden="true"></i> <?php } else { ?><i
                                                        class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                }
                                            } else {
                                                ?> <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                        <th width="8%" style="text-align: center;">Places</th>
                                        <th width="8%" style="text-align: center;">Display Order</th>
                                        <th width="8%" style="text-align: center;">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                            ?> 0 <?php } ?>)">Status<?php
                                            if ($sortby == 2) {
                                                if ($order == 0) {
                                                    ?><i class="fa fa-sort-amount-asc"
                                                         aria-hidden="true"></i> <?php } else { ?><i
                                                        class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                }
                                            } else { ?> <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if ($userObj->hasPermission(['edit-category-nearby' , 'update-status-category-nearby' , 'delete-category-nearby'])) { ?>
                                        <th width="8%" style="text-align: center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($master_service_categories) && scount($master_service_categories) > 0) {
                                        foreach ($master_service_categories as $service_category) {
                                            $iNearByCategoryId = $service_category['iNearByCategoryId'];
                                            $eStatus_ = $service_category['eStatus'];
                                            $vIconImage = $service_category['vImage'];
                                            ?>
                                            <tr>
                                                <td style="text-align: center;">
                                                    <?php if(!empty($vIconImage)) { ?>
                                                    <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_nearby_item'] . $vIconImage ?>">
                                                    <?php } ?>
                                                </td>
                                                <td><?= $service_category['vTitle'] ?></td>
                                                <td style="text-align: center;" ><a class="add-btn-sub" href="near_by_places.php?iNearByCategoryId=<?php echo $iNearByCategoryId; ?>"
                                                       target="_blank">Add/View
                                                        (<?= $service_category['totalPlaces']; ?>) </a></td>
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
                                                    <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $service_category['eStatus']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission(['edit-category-nearby' , 'update-status-category-nearby' , 'delete-category-nearby'])) { ?>

                                                <td align="center" style="text-align:center;" class="action-btn001">
                                                    <div class="share-button openHoverAction-class"
                                                         style="display: block;">
                                                        <label class="entypo-export">
                                                            <span><img  src="images/settings-icon.png" alt=""></span>
                                                        </label>
                                                        <div class="social show-moreOptions for-two openPops_<?= $iNearByCategoryId; ?>">

                                                            <ul>
                                                                <?php if ($userObj->hasPermission('edit-category-nearby')) { ?>
                                                                <li class="entypo-twitter" data-network="twitter">
                                                                    <a href="near_by_category_action.php?id=<?= $iNearByCategoryId; ?>"
                                                                       data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                </li>
                                                                <?php } if ($userObj->hasPermission('update-status-category-nearby')) { ?>
                                                                <li class="entypo-facebook" data-network="facebook">
                                                                    <a href="javascript:void(0);" onClick="window.location.href='near_by_category.php?id=<?= $iNearByCategoryId; ?>&parentid=<?= $parentId ?>&status=Active&eType=<?= $eType ?>'" data-toggle="tooltip" title="Activate"><img src="img/active-icon.png" alt="<?= $eStatus_; ?>"></a>
                                                                </li>
                                                                <li class="entypo-gplus" data-network="gplus">
                                                                    <a href="javascript:void(0);" onClick="window.location.href='near_by_category.php?id=<?= $iNearByCategoryId; ?>&parentid=<?= $parentId ?>&status=Inactive&eType=<?= $eType ?>'"  data-toggle="tooltip" title="Deactivate"> <img src="img/inactive-icon.png" alt="<?= $eStatus_; ?>">
                                                                    </a>
                                                                </li>
                                                                <?php } if ($userObj->hasPermission('delete-category-nearby')) { ?>
                                                                    <li class="entypo-gplus" data-network="gplus"><a
                                                                                href="javascript:void(0);"
                                                                                onClick="window.location.href='near_by_category.php?id=<?= $iNearByCategoryId; ?>&parentid=<?= $parentId ?>&status=Deleted&eType=<?= $eType ?>'"
                                                                                data-toggle="tooltip"
                                                                                title="Delete">
                                                                            <img src="img/delete-icon.png"
                                                                                 alt="Delete">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
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
                    <li>Administrator can Activate / Deactivate / Modify any NearBy Service.</li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/service_type.php" method="post">
    <input type="hidden" name="parentid" id="parentid" value="<?= $parentId; ?>">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="iVehicleTypeId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>">
    <input type="hidden" name="option" value="<?= $option; ?>">
    <input type="hidden" name="keyword" value="<?= $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
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
</script>
</body>
<!-- END BODY-->
</html>