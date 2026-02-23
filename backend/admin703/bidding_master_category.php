<?php
include_once('../common.php');
$script = 'bidding';
$tbl_name = "bidding_service";
if (!$userObj->hasPermission('view-bidding-category')) {
    $userObj->redirect();
}
$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$iMasterServiceCategoryId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
$parentId = isset($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
$sub = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 0;
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$queryString = $parentId > 0 ? '?parentid=' . $parentId : '';
if (!empty($iMasterServiceCategoryId) && !empty($status)) {
    if (SITE_TYPE != 'Demo') {
        $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iBiddingId  = '" . $iMasterServiceCategoryId . "'");
        header("Location:bidding_master_category.php" . $queryString);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:bidding_master_category.php");
        exit();
    }
}
$var_filter = '';
$per_page = $DISPLAY_RECORD_NUMBER;
$total_results = $BIDDING_OBJ->getBiddingTotalCount('admin', $parentId);
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
if ($parentId > 0) {
    $master_service_categories = $BIDDING_OBJ->getBiddingSubCategory('admin', $parentId, $ssql, $start, $per_page, $lang, $ord);
    $getbidding = $BIDDING_OBJ->getbidding('admin', $parentId);
} else {
    $master_service_categories = $BIDDING_OBJ->getBiddingMaster('admin', $ssql, $start, $per_page, $lang, $ord);
}
foreach ($master_service_categories as $key => $value) {
    $query = $BIDDING_OBJ->getBiddingSubCategory('admin', $value['iBiddingId']);
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
    <title><?= $SITE_NAME ?> |
        Bidding <?php if ($parentId != 0 && isset($getbidding['vTitle']) && !empty($getbidding['vTitle'])) {
        } else { ?><?php } ?> Services
    </title>
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
                        <h2>
                            Bidding <?php if ($parentId != 0 && isset($getbidding['vTitle']) && !empty($getbidding['vTitle'])) {
                            } else { ?><?php } ?>
                            Services <?php if ($parentId != 0 && isset($getbidding['vTitle']) && !empty($getbidding['vTitle'])) { ?> (<?= @$getbidding['vTitle'] ?>) <?php } ?></h2>
                    </div>
                    <?php if ($parentId != 0) { ?>
                        <a href="bidding_master_category.php?parentid=0">
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
                                <?php if ($userObj->hasPermission('delete-bidding-category')) { ?>
                                    <!-- <option value="Deleted" <?php
                                    // if ($eStatus == 'Deleted') {
                                    //     echo "selected";
                                    // }
                                    ?>>Deleted</option> -->
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = 'bidding_master_category.php?parentid=<?= $parentId; ?>'"/>
                        </td>
                        <?php
                        if ($userObj->hasPermission('create-bidding-category')) { ?>
                            <td width="30%">
                                <a class="add-btn" href="bidding_master_category_action.php?parentid=<?= $parentId; ?>"
                                   style="text-align: center;">Add Service
                                </a>
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
                        <div class="table-responsive1">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <div class="table table-striped  table-hover">
                                <?php if (!empty($master_service_categories) && scount($master_service_categories) > 0) { ?>
                                    <div class="profile-earning">
                                        <div class="partation">
                                            <ul style="padding-left: 0px;" class="setings-list">
                                                <?php
                                                foreach ($master_service_categories as $service_category) {
                                                    
                                                    $iMasterServiceCategoryId = $service_category['iBiddingId'];
                                                    $eStatus_ = $service_category['eStatus'];
                                                    $vIconImage = $service_category['vImage'];

                                                    if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3() && $parentId == 0) {
                                                        $vIconImage = $service_category['vImage1'];
                                                    }

                                                    if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV4() && $parentId == 0) {
                                                        $vIconImage = $service_category['vImage1'];
                                                    }
                                                    
                                                    $buttonStatus = $service_category['eStatus'];
                                                    $btnChecked = 0;
                                                    if ($service_category['eStatus'] == "Active") {
                                                        $btnChecked = 1;
                                                    }
                                                    ?>
                                                    <li>
                                                        <div class="toggle-list-inner">
                                                            <div class="toggle-combo">
                                                                <label>
                                                                    <div align="center">
                                                                        <?php if (!empty($vIconImage)) { ?>
                                                                            <img src="<?= $tconfig['tsite_upload_images_bidding'] . $vIconImage ?>" style="width: 100px">
                                                                        <?php } ?>
                                                                    </div>
                                                                    <div style="margin: 0 0 0 10px;">
                                                                        <td><?= $service_category['vTitle'] ?></td>
                                                                    </div>
                                                                </label>
                                                                <?php if ($userObj->hasPermission('update-status-bidding-category')) {
                                                                     // <?php if ($iMasterServiceCategoryId != $BIDDING_OBJ->other_id) { ?>
                                                                    <span class="toggle-switch">
                                                                        <input type="checkbox" <?php if ($btnChecked > 0) { ?> checked="" <?php } ?>  id="statusbutton" class="chk statusbutton" name="statusbutton" value="<?= $iMasterServiceCategoryId ?>" data-parantid = "<?= $parentId ?>"  data-fullurl="bidding_master_category.php?id=<?= $iMasterServiceCategoryId; ?>&parentid=<?= $parentId ?>&status=<?= ($btnChecked > 0) ? "Inactive" : "Active"?>" data-toggle="tooltip" title="<?= ($btnChecked > 0) ? "Active" : "Inactive" ?> " >
                                                                        <span class="toggle-base"></span>
                                                                    </span>
                                                                <?php  } ?>
                                                            </div>
                                                            <div class="check-combo">
                                                                <label id="defaultText_246">
                                                    
                                                                    <ul>
                                                                    <?php if ($userObj->hasPermission(['delete-bidding-category', 'edit-bidding-category', 'update-status-bidding-category'])) { ?>
                                                                        <?php 
                                                                        if ($userObj->hasPermission('edit-bidding-category')) { ?>
                                                                            <li class="entypo-twitter"
                                                                                data-network="twitter">
                                                                                <a href="bidding_master_category_action.php?id=<?= $iMasterServiceCategoryId; ?>"
                                                                                   data-toggle="tooltip"
                                                                                   title="Edit">
                                                                                    <img src="img/edit-new.png"
                                                                                         alt="Edit">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission('delete-bidding-category')) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <!-- <a href="javascript:void(0);" onClick="window.location.href='bidding_master_category.php?id=<?= $iMasterServiceCategoryId; ?>&parentid=<?= $parentId ?>&status=Deleted&eType=<?= $eType ?>'"
                                                                                   data-toggle="tooltip"
                                                                                   title="Delete">
                                                                                    <img src="img/delete-new.png" alt="Delete">
                                                                                </a> -->
                                                                                <a href="javascript:void(0);" onclick="changeStatusDeleteBiddingServices('<?php echo $iMasterServiceCategoryId; ?>','<?=$parentId;?>')" data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-new.png" alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                            <?php if ($parentId == 0 && ($iMasterServiceCategoryId != $BIDDING_OBJ->other_id)) { ?>
                                                                                <li class="entypo-gplus"
                                                                                    data-network="gplus">
                                                                                    <a href="bidding_master_category.php?parentid=<?= $iMasterServiceCategoryId; ?>" target="_blank"
                                                                                       data-toggle="tooltip"
                                                                                       title="View Subcategories (<?= $service_category['SubCategories']; ?>)" >
                                                                                        <img src="img/view-icon.png" alt="View">
                                                                                    </a>
                                                                                </li>
                                                                            <?php } }  ?>
                                                                        <?php  } ?>
                                                                        <?php if($userObj->hasPermission('view-banners-bidding')) { ?>
                                                                            <li class="entypo-twitter" data-network="twitter">
                                                                                <a href="banner.php?eForService=Bidding&eFor=Bidding&iVehicleCategoryId=<?= $iMasterServiceCategoryId ?>" target="_blank" data-toggle="tooltip" title="View Banners">
                                                                                    <img src="img/banner-icon.png" alt="Active">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if (strtoupper(ENABLE_SUB_PAGES) == 'YES' && $parentId == 0) { ?>
                                                                            <?php if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'Yes') {
                                                                                if ($userObj->hasPermission('manage-inner-page-bids')) { ?>
                                                                                    <li class="entypo-twitter" bdata-network="twitter">
                                                                                        <a href="servicebid_content_action.php?iVehicleCategoryId=<?= $iMasterServiceCategoryId; ?>&id=1" target="_blank" data-toggle="tooltip" title="Edit Inner Page">
                                                                                           <img src="img/edit-doc.png" alt="Edit">
                                                                                        </a>
                                                                                    </li>
                                                                                <?php } else {
                                                                                    echo "--";
                                                                                }
                                                                            } else { ?>
                                                                                <li class="entypo-twitter" bdata-network="twitter">
                                                                                    <a href="home_content_servicebid_action.php?iVehicleCategoryId=<?= $iMasterServiceCategoryId; ?>&id=1" target="_blank" data-toggle="tooltip" title="Edit Inner Page">
                                                                                       <img src="img/edit-doc.png" alt="Edit">
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        <?php } ?>
                                                                    </ul>
                                                                    
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php }
                                                ?></ul>
                                        </div>
                                    </div><?php
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="8"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                </div>
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
                    <li>Administrator can Activate / Deactivate / Modify any Bidding Service.</li>
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
    function changeStatusDeleteBiddingServices(iMasterServiceCategoryId,parentId) {
        $('#is_dltSngl_modal').modal('show');
        $(".action_modal_submit").unbind().click(function () {
            window.location.href = "?id="+iMasterServiceCategoryId+"&parentid="+parentId+"&status=Deleted";
        });
    }
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
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });

    $('.statusbutton').change(function() {
        window.location.href = $(this).data('fullurl');
    });
</script>
</body>
<!-- END BODY-->
</html>