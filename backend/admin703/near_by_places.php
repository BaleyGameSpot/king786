<?php
include_once('../common.php');
$script = 'nearbyPlaces';
$tbl_name = "nearby_places";
if (!$userObj->hasPermission('view-places-nearby')) {
    $userObj->redirect();
}
$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$iNearByPlacesId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
$parentId = isset($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
$sub = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 0;
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$queryString = $parentId > 0 ? '?parentid=' . $parentId : '';
$iNearByCategoryId = isset($_REQUEST['iNearByCategoryId']) ? $_REQUEST['iNearByCategoryId'] : "";
if (!empty($iNearByPlacesId) && !empty($status)) {
    if (SITE_TYPE != 'Demo') {
        $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iNearByPlacesId  = '" . $iNearByPlacesId . "'");
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        if(!empty($iNearByCategoryId)){
            header("Location:near_by_places.php?iNearByCategoryId=$iNearByCategoryId");
        } else {
            header("Location:near_by_places.php");
        }
        exit;
    } else {
        $_SESSION['success'] = '2';

        if(!empty($iNearByCategoryId)){
            header("Location:near_by_places.php?iNearByCategoryId=$iNearByCategoryId");
        } else {
            header("Location:near_by_places.php");
        }
        
        exit();
    }
}
$ssql = '';
if ($keyword != '') {
    if ($eStatus != '') {
        $ssql .= " AND (np.vTitle LIKE '%" . clean($keyword) . "%') AND np.eStatus = '" . clean($eStatus) . "'";
    } else {
        $ssql .= " AND (np.vTitle LIKE '%" . clean($keyword) . "%')";
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND np.eStatus = '" . clean($eStatus) . "'";
}
if (isset($iNearByCategoryId) && !empty($iNearByCategoryId)) {
    $ssql .= " AND np.iNearByCategoryId = '" . clean($iNearByCategoryId) . "'";
}
if(empty($eStatus)){
     $ssql .= 'AND ( np.estatus = "Active" || np.estatus = "Inactive" )';
}
$var_filter = '';
$per_page = $DISPLAY_RECORD_NUMBER;
//$per_page = 1;
$total_results = $NEARBY_OBJ->getNearByPlacesTotalCount('admin', $ssql);
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
$ord = "ORDER BY np.iNearByPlacesId DESC";
$NearByPlaces = $NEARBY_OBJ->getNearByPlaces('admin', $ssql, $start, $per_page, $lang, $ord);
$endRecord = scount($NearByPlaces);
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . "&parentid=" . $parentId . $var_filter;
$NearByCategory = $NEARBY_OBJ->getNearByCategory('admin');
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | NearBy Places </title>
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
                        <h2>NearBy Places</h2>
                    </div>
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
                        <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword"
                                                                  value="<?php echo $keyword; ?>" class="form-control"/>
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

                        <?php if(isset($NearByCategory) && !empty($NearByCategory)){ ?>
                        <td width="15%" class="category_options" id="category_options">
                            <select  name = "iNearByCategoryId" id="iNearByCategoryId" class="form-control">
                                <option value="">Select category</option>
                                <?php foreach ($NearByCategory as $category) { ?>
                                    <option value='<?php echo $category['iNearByCategoryId']; ?>' <?php
                                    if ($category['iNearByCategoryId'] == $iNearByCategoryId) {
                                        echo "selected";
                                    }
                                    ?>><?php echo $category['vTitle'] . ($category['eStatus'] == "Inactive" ? ' (Inactive)' : ''); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                        <?php } ?>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'near_by_places.php'"/>
                            <?php if (!empty($NearByPlaces)) { ?>
                            <button type="button" onclick="showExportTypes('near_by_places')" class="btnalt button11">Export</button>
                            <?php } ?>
                        </td>
                        <?php
                        if ($userObj->hasPermission('create-places-nearby')) { ?>
                            <?php if(!empty($iNearByCategoryId)) {?>
                                <td width="30%"><a class="add-btn" href="near_by_places_action.php?iNearByCategoryId=<?php echo $iNearByCategoryId;?>" style="text-align: center;">Add Nearby Place</a></td>
                            <?php } else { ?>
                                <td width="30%"><a class="add-btn" href="near_by_places_action.php" style="text-align: center;">Add Nearby Place</a></td>
                            <?php } ?>
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
                                        <th width="12%">Place Name</th>
                                        <th width="8%">Place Category</th>
                                        <th width="25%">Address</th>
                                        <th width="5%" style="text-align: center;">Status</th>
                                        <?php if ($userObj->hasPermission(['edit-places-nearby' , 'edit-places-nearby','update-status-places-nearby'])) { ?>
                                        <th width="5%" style="text-align: center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($NearByPlaces) && scount($NearByPlaces) > 0) {
                                        foreach ($NearByPlaces as $Place) {
                                            $iNearByPlacesId = $Place['iNearByPlacesId'];
                                            $eStatus_ = $Place['eStatus'];
                                            $vIconImage = $Place['vImage'];
                                            $categoryName = $Place['categoryName'];
                                            $vAddress = $Place['vAddress'];
                                            $vTitle = $Place['vTitle'];

                                            $categoryStatus = '';
                                            if($Place['categoryStatus'] == "Inactive") {
                                                $categoryStatus = ' (Inactive)';
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $vTitle; ?></td>
                                                <td><?= $categoryName . $categoryStatus; ?></td>
                                                <td><?= $vAddress; ?></td>
                                                <td align="center">
                                                    <?php
                                                    if ($Place['eStatus'] == 'Active') {
                                                        $status_img = "img/active-icon.png";
                                                    } else if ($Place['eStatus'] == 'Inactive') {
                                                        $status_img = "img/inactive-icon.png";
                                                    } else {
                                                        $status_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip"
                                                         title="<?= $service_category['eStatus']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission(['edit-places-nearby' , 'edit-places-nearby','update-status-places-nearby'])) { ?>
                                                <td align="center" class="action-btn001">
                                                    <?php if ($userObj->hasPermission(['delete-places-nearby','update-status-places-nearby'])) { ?>
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export"><span><img
                                                                            src="images/settings-icon.png"
                                                                            alt=""></span></label>
                                                            <div class="social show-moreOptions for-two openPops_<?= $iNearByPlacesId; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission('edit-places-nearby')) { ?>
                                                                    <li class="entypo-twitter" data-network="twitter">
                                                                        <a href="near_by_places_action.php?id=<?= $iNearByPlacesId; ?>"
                                                                           data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a>
                                                                    </li>
                                                                    <?php } if ($userObj->hasPermission('update-status-places-nearby')) { ?>
                                                                    <li class="entypo-facebook" data-network="facebook">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="window.location.href='near_by_places.php?id=<?= $iNearByPlacesId; ?>&status=Active&eType=<?= $eType ?>&iNearByCategoryId=<?= $iNearByCategoryId ?>'"
                                                                           data-toggle="tooltip" title="Activate"><img
                                                                                    src="img/active-icon.png"
                                                                                    alt="<?= $eStatus_; ?>"></a>
                                                                    </li>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="window.location.href='near_by_places.php?id=<?= $iNearByPlacesId; ?>&status=Inactive&eType=<?= $eType ?>&iNearByCategoryId=<?= $iNearByCategoryId ?>'"
                                                                           data-toggle="tooltip" title="Deactivate">
                                                                            <img src="img/inactive-icon.png"
                                                                                 alt="<?= $eStatus_; ?>">
                                                                        </a>
                                                                    </li>
                                                                    <?php } if ($userObj->hasPermission('delete-places-nearby') && $eStatus_ != "Deleted") { ?>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);"
                                                                               onClick="window.location.href='near_by_places.php?id=<?= $iNearByPlacesId; ?>&status=Deleted&eType=<?= $eType ?>&iNearByCategoryId=<?= $iNearByCategoryId ?>'"
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
                                                    <?php } else { ?>
                                                        <a href="near_by_places_action.php"
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
                    <li>Administrator can Activate / Deactivate / Modify any Near By Service.</li>
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
    <input type="hidden" name="iNearByCategoryId" value="<?= $iNearByCategoryId?>">
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