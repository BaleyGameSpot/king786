<?php
include_once('../common.php');
$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
$iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
$script = $eMasterType . 'PaymentPlan';
$tbl_name = "rent_item_payment_plan";
if (!$userObj->hasPermission('view-payment-plan-' . strtolower($eMasterType))) {
    $userObj->redirect();
}
$lang = $LANG_OBJ->FetchDefaultLangData("vCode");
$iPaymentPlanId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
$sub = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 0;
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
if (!empty($iPaymentPlanId) && !empty($status)) {
    if (SITE_TYPE != 'Demo') {
        $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iPaymentPlanId  = '" . $iPaymentPlanId . "'");
        header("Location:item_payment_plans.php?eType=" . $_REQUEST['eType']);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:item_payment_plans.php?eType=" . $_REQUEST['eType']);
        exit();
    }
}
$ssql = '';
if ($keyword != '') {
    if ($eStatus != '') {
        $ssql .= " AND (vPlanName LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
    } else {
        $ssql .= " AND (vPlanName LIKE '%" . clean($keyword) . "%')";
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
}
if (empty($eStatus)) {
    $ssql .= 'AND ( eStatus = "Active" || eStatus = "Inactive" )';
}
$ord = " ORDER BY iPaymentPlanId ASC";
if ($sortby == 1) {
    $d = " SUBSTRING_INDEX(SUBSTRING_INDEX(vPlanName,'vPlanName_EN\":\"',-1),'\"',1)";
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
if ($iMasterServiceCategoryId != "") {
    $ssql .= " And iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
}
$var_filter = '';
$per_page = $DISPLAY_RECORD_NUMBER;
$total_results = $RENTITEM_OBJ->getPaymentPlanTotalCount('admin', $ssql);
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
$rent_item_payment_plan = $RENTITEM_OBJ->getRentItemPaymentPlan('admin', $ssql, $start, $per_page, $lang, '', $ord);
$endRecord = scount($rent_item_payment_plan);
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
    <title><?= $SITE_NAME ?> | Payment Plans</title>
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
                        <h2>Payment Plans</h2>
                    </div>
                </div>
                <hr/>
            </div>
                    <?php include('valid_msg.php'); ?>

                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">

                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">

                            <tbody>

                                <tr>

                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>

                                    <input type="hidden" name="option" id="option" value="">

                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" class="form-control" /></td>

                                    <td width="13%" class="estatus_options" id="eStatus_options">

                                        <select name="eStatus" id="estatus_value" class="form-control">

                                            <option value="">Select Status</option>

                                            <option value='Active' <?php  if ($eStatus == 'Active') {  echo "selected";  } ?> >Active</option>

                                            <option value="Inactive" <?php  if ($eStatus == 'Inactive') {   echo "selected"; }?> >Inactive</option>

                                            <option value="Deleted" <?php  if ($eStatus == 'Deleted') {   echo "selected"; }?> >Deleted</option>

                                        </select>

                                    </td>

                                    <td>
                                        <?php $reloadurl = "item_payment_plans.php?eType=".$_REQUEST['eType']; ?>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = '<?php echo $reloadurl;?>'" />
                                    </td>

                                    <?php if ($userObj->hasPermission('create-payment-plan-'.strtolower($eMasterType))) { ?>

                                        <td width="30%"><a class="add-btn" href="item_payment_plans_action.php?eType=<?php echo $_REQUEST['eType'];?>" style="text-align: center;">Add Payment Plan</a></td>

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

                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

                                        <table class="table table-striped table-bordered table-hover">

                                            <thead>

                                                <tr>

                                                    <th width="22%"><a href="javascript:void(0);" onClick="Redirect(1,<?php

                                                        if ($sortby == '1') {

                                                            echo $order;

                                                        } else {

                                                        ?> 0 <?php } ?>)">Name<?php

                                                        if ($sortby == 1) {

                                                            if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php  }

                                                        } else { ?> 
                                                            <i class="fa fa-sort" aria-hidden="true"></i> 
                                                        <?php } ?></a></th>

                                                    <th width="8%"  style="text-align: center;">  Days </th>

                                                    <th width="10%" style="text-align: center;">Post Number</th>

                                                    <th  width="10%" style="text-align: center;">Amount</th>


                                                    <th  width="8%" style="text-align: center;">

                                                        <a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {

                                                                echo $order;

                                                            } else {

                                                            ?>0<?php } ?>)"> Status <?php

                                                            if ($sortby == 1) {

                                                                if ($order == 0) {

                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php

                                                                }

                                                            } else {
                                                                ?> 
                                                                <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                            </a>

                                                    </th>

                                                    <th  width="8%" style="text-align: center;">Action</th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php if (!empty($rent_item_payment_plan) && scount($rent_item_payment_plan) > 0) {

                                                    foreach ($rent_item_payment_plan as $service_category) {

                                                        $iPaymentPlanId = $service_category['iPaymentPlanId'];

                                                        $eStatus = $service_category['eStatus'];

                                                        $eFreePlan = $service_category['eFreePlan'];
                                                        
                                                    ?>

                                                <tr>

                                                    <td><?= $service_category['vPlanName']; ?></td>
                                                    
                                                    <td align='center'> <?= $service_category['iTotalDays']; ?></td>

                                                    <td align='center'><?php if($service_category['iTotalPost'] > 0){  echo  $service_category['iTotalPost'];   } else {
                                                        echo  "-"; }?></td>

                                                    <td  align='center'><?php if($service_category['fAmount'] > 0){
                                                        echo formateNumAsPerCurrency($service_category['fAmount'],'');
                                                    } else {
                                                        echo  "-"; 
                                                    }?></td>
                                                    
                                                    <td  align='center'>

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

                                                    <td  class="action-btn001"  align='center'>

                                                        <?php if ($userObj->hasPermission('edit-payment-plan-'.strtolower($eMasterType))) { ?>

                                                        <div class="share-button openHoverAction-class" style="display: block;">

                                                            <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>

                                                            <div class="social show-moreOptions for-two openPops_<?= $iPaymentPlanId; ?>">
                                                                <ul>
                                                                    <li class="entypo-twitter" data-network="twitter">
                                                                        <a href="item_payment_plans_action.php?id=<?= $iPaymentPlanId; ?>&eType=<?= $_REQUEST['eType']; ?>"
                                                                           data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a>
                                                                    </li>
                                                                    <li class="entypo-facebook" data-network="facebook">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="window.location.href='item_payment_plans.php?id=<?= $iPaymentPlanId; ?>&status=Active&eType=<?= $_REQUEST['eType']; ?>'"
                                                                           data-toggle="tooltip" title="Activate">
                                                                            <img src="img/active-icon.png"
                                                                                 alt="<?= $service_category['eStatus']; ?>">
                                                                        </a>
                                                                    </li>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="window.location.href='item_payment_plans.php?id=<?= $iPaymentPlanId; ?>&status=Inactive&eType=<?= $_REQUEST['eType']; ?>'"
                                                                           data-toggle="tooltip" title="Deactivate">
                                                                            <img src="img/inactive-icon.png"
                                                                                 alt="<?= $service_category['eStatus']; ?>">
                                                                        </a>
                                                                    </li>
                                                                    <?php if ($userObj->hasPermission('delete-payment-plan-' . strtolower($eMasterType)) && $eFreePlan != "Yes") { ?>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);"
                                                                               onClick="changeStatusDelete('<?= $iPaymentPlanId; ?>','<?= $_REQUEST['eType']; ?>')"
                                                                               data-toggle="tooltip"
                                                                               title="Delete">
                                                                                <img src="img/delete-icon.png"
                                                                                     alt="Delete">
                                                                            </a>
                                                                            <!--  <a href="javascript:void(0);" onClick="window.location.href='item_payment_plans.php?id=<?= $iPaymentPlanId; ?>&status=Deleted&eType=<?= $_REQUEST['eType']; ?>'" data-toggle="tooltip" title="Delete"> <img src="img/delete-icon.png" alt="Delete"></a> -->
                                                                        </li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    <?php } else { ?>
                                                        <a href="item_payment_plans_action.php?id=<?= $iPaymentPlanId; ?>&eType=<?= $_REQUEST['eType']; ?>"
                                                           data-toggle="tooltip" title="Edit">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                    <?php } ?>
                                                </td>
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
                    <li>Administrator can Activate / Deactivate / Modify any Payment Plan.</li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="" method="post">

    <input type="hidden" name="page" id="page" value="<?= $page; ?>">

    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">

    <input type="hidden" name="iVehicleTypeId" id="iMainId01" value="">

    <input type="hidden" name="status" id="status01" value="">

    <input type="hidden" name="statusVal" id="statusVal" value="">

    <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>">

    <input type="hidden" name="option" value="<?= $option; ?>">

    <input type="hidden" name="keyword" value="<?= $keyword; ?>">

    <input type="hidden" name="eType" value="<?= $_REQUEST['eType']; ?>">

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

        var action = $("#_list_form").attr('action');

        var formValus = $("#frmsearch").serialize();

        window.location.href = action + "?eType=<?php echo $_REQUEST['eType'];?>&" + formValus;

        //window.location.href = action + "?" + formValus;

    });

    function changeStatusDelete(iPaymentPlanId, eType) {
        $('#is_dltSngl_modal').modal('show');
        $(".action_modal_submit").unbind().click(function () {
            window.location.href = 'item_payment_plans.php?id=' + iPaymentPlanId + '&status=Deleted&eType=' + eType + ''
        });
    }
</script>
</body>
<!-- END BODY-->
</html>