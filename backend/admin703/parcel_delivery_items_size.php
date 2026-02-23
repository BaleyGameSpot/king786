<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-item-size-parcel-delivery')) {
    $userObj->redirect();
}
$script = 'ParcelDeliveryItemSize';
/* get make */
$hdn_del_id = isset($_POST['hdn_del_id']) ? $_POST['hdn_del_id'] : '';
$iItemSizeCategoryId = isset($_REQUEST['iItemSizeCategoryId']) ? $_REQUEST['iItemSizeCategoryId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$tbl_name = 'parcel_delivery_items_size_info';

$ord = ' ORDER BY iItemSizeCategoryId ASC';
$eStatussql = " AND eStatus != 'Deleted'";

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iItemSizeCategoryId) AS Total FROM $tbl_name WHERE 1=1 $eStatussql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
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
$data_drv = $obj->MySQLSelect("SELECT *, JSON_UNQUOTE(JSON_VALUE(tTitle, '$.tTitle_$default_lang')) as tTitle, JSON_UNQUOTE(JSON_VALUE(tSubtitle, '$.tSubtitle_$default_lang')) as tSubtitle FROM " . $tbl_name . " WHERE 1=1 $eStatussql $ord LIMIT $start, $per_page ");
$endRecord = scount($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Parcel Delivery Items Size</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Item Size Details</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>

            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th align="center" style="text-align:center;">Status</th>
                                        <?php if ($userObj->hasPermission([
                                            'edit-item-size-parcel-delivery',
                                            'update-status-item-size-parcel-delivery',
                                        ])) { ?>
                                            <th align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        for ($i = 0; $i < scount($data_drv); $i++) { ?>
                                            <tr class="gradeA">
                                                <td align="center">
                                                    <?php if ($data_drv[$i]['vImage'] != '' && file_exists($tconfig['tsite_upload_images_parcel_delivery_items_size_path'] . '/' . $data_drv[$i]['vImage'])) { ?>
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=50&src=' . $tconfig['tsite_upload_images_parcel_delivery_items_size'] . '/' . $data_drv[$i]['vImage']; ?>" >
                                                    <?php } else echo "--"; ?>
                                                </td>
                                                <td><?= $data_drv[$i]['tTitle']; ?></td>
                                                <td><?= $data_drv[$i]['tSubtitle']; ?></td>
                                                <td align="center" style="text-align:center;">
                                                    <?php
                                                    if ($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus']; ?>"
                                                         data-toggle="tooltip" title="<?= $data_drv[$i]['eStatus']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission([
                                                    'edit-item-size-parcel-delivery',
                                                    'update-status-item-size-parcel-delivery'
                                                ])) { ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export">
                                                                <span><img src="images/settings-icon.png" alt=""></span>
                                                            </label>
                                                            <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iItemSizeCategoryId']; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission('edit-item-size-parcel-delivery')) { ?>
                                                                        <li class="entypo-twitter"
                                                                            data-network="twitter">
                                                                            <a href="parcel_delivery_items_size_action.php?id=<?= $data_drv[$i]['iItemSizeCategoryId']; ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>

                                                                    <?php if ($data_drv[$i]['eDefault'] != 'Yes') { ?>
                                                                        <?php if ($userObj->hasPermission('update-status-item-size-parcel-delivery')) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatus('<?php echo $data_drv[$i]['iItemSizeCategoryId']; ?>', 'Inactive')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatus('<?php echo $data_drv[$i]['iItemSizeCategoryId']; ?>', 'Active')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Deactivate">
                                                                                    <img src="img/inactive-icon.png"
                                                                                         alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="5"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>
                        Parcel Delivery Item Size module will list all item size details on this page.
                    </li>
                    <li>
                        Administrator can Activate / Deactivate any Parcel Delivery Item Size.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/parcel_delivery_items_size.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iItemSizeCategoryId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
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
</script>
</body>
<!-- END BODY-->
</html>