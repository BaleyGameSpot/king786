<?php
include_once('../common.php');

if (!$userObj->hasPermission('view-vehicle-category')) {
    $userObj->redirect();
}

$script = 'VehicleCategory';
if ($parent_ufx_catid != '0') {
    header("Location:vehicle_sub_category.php?sub_cid=" . $parent_ufx_catid);
    exit;
}

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDisplayOrder ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vCategory_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vCategory_" . $default_lang . " DESC";
}

if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}

if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY SubCategories ASC";
    else
        $ord = " ORDER BY SubCategories DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY iDisplayOrder ASC";
    else
        $ord = " ORDER BY iDisplayOrder DESC";
}
//End Sorting
$rdr_ssql = "";
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
if($MODULES_OBJ->isEnableAppHomeScreenLayout() && empty($eType)) {
    header('Location: ' . $tconfig['tsite_admin_url'] . 'master_service_category.php');
}

$ssql = $ssqlSearch = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssqlSearch .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssqlSearch .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssqlSearch .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssqlSearch .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssqlSearch .= " AND eStatus = '" . clean($eStatus) . "'";
}
$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
if($THEME_OBJ->isCubexThemeActive() == 'Yes' || $THEME_OBJ->isCubeXv2ThemeActive() == 'Yes') {
    $ssql1 = $ssql2 = $ssql3 = '';
    if ($ufxEnable!='Yes') {
        $ssql1 .= " AND eCatType!='ServiceProvider'";
    } else {
        $ssql2 .= " OR eCatType='ServiceProvider'";
    }
    $ssql .= " AND (iServiceId IN ($enablesevicescategory) OR eCatType IN ('Ride', 'MotoRide', 'Fly', 'Donation') OR (eFor = 'DeliveryCategory' AND eCatType = 'MoreDelivery')  $ssql2 )  $ssql1";
}
if($ufxEnable!='Yes') {
    $ssql .= " AND eCatType!='ServiceProvider'";
}
if(!$MODULES_OBJ->isAirFlightModuleAvailable(1)) {
    $ssql.= " AND eCatType != 'Fly'";
}
if(!$MODULES_OBJ->isDonationFeatureAvailable()) {
    $ssql .= " AND eCatType != 'Donation'";
}

if(!$MODULES_OBJ->isRideFeatureAvailable()) {
    $ssql .= " AND eCatType != 'Ride' AND eCatType != 'MotoRide' AND eCatType != 'Rental' AND eCatType != 'MotoRental'";
}
if(!$MODULES_OBJ->isDeliveryFeatureAvailable()) {
    $ssql .= " AND eCatType != 'Delivery' AND eCatType != 'MultipleDelivery' AND eCatType != 'MotoDelivery' AND eCatType != 'MoreDelivery'";
}
if(!$MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    $ssql .= " AND eCatType != 'DeliverAll'";  
}
if(!$MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
    $ssql .= " AND eCatType!='Genie' AND eCatType!='Runner' AND eCatType!='Anywhere'";
}

$MasterServiceCategory = "";
if(!empty($eType)) {
    $master_service_category = $obj->MySQLSelect("SELECT JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vCategoryName FROM $master_service_category_tbl WHERE eType = '$eType' ");

    $MasterServiceCategory = "(".$master_service_category[0]['vCategoryName'].")";
    $ssql = getMasterServiceCategoryQuery($eType);
    if($eType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()) {
        $vc_data = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT(iParentId)) as ParentIds FROM vehicle_category WHERE eVideoConsultEnable = 'Yes'");
        $ssql .= " AND iVehicleCategoryId IN (" . $vc_data[0]['ParentIds'] . ")";
    }
}
$ssql .= $ssqlSearch;

// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
//Added By HJ On 14-11-2019 For Manage Service Category Row As Per Design Start
$calSize = 3;
for ($f = $per_page; $f < ($per_page + $calSize); $f++) {
    $checkZero = $f / $calSize;
    $checkZero = is_numeric($checkZero) && floor($checkZero) != $checkZero;
    if (empty($checkZero)) {
        $per_page = $f;
        break;
    }
}
//Added By HJ On 14-11-2019 For Manage Service Category Row As Per Design End
if ($eStatus != '') {
    $estatusquery = "";
} else {
    $estatusquery = " AND eStatus != 'Deleted'";
}

$not_sql = " AND iVehicleCategoryId != 297";
$sql = "SELECT COUNT(iVehicleCategoryId) AS Total FROM ".$sql_vehicle_category_table_name."  WHERE  1 = 1 AND iParentId='0' $estatusquery $ssql $rdr_ssql $not_sql";
//echo $sql;die;
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
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
if ($page <= 0)
    $page = 1;
//Pagination End
if (!empty($eStatus)) {
    $sql = "SELECT vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo,vc.vListLogo1,vc.vListLogo2,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType, (select count(iVehicleCategoryId) from ".$sql_vehicle_category_table_name." where iParentId = vc.iVehicleCategoryId AND eStatus != 'Deleted') as SubCategories FROM ".$sql_vehicle_category_table_name." as vc WHERE  vc.iParentId='0' $ssql $rdr_ssql $not_sql $ord LIMIT $start, $per_page";
} else {
    $sql = "SELECT vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo,vc.vListLogo1,vc.vListLogo2,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType,  (select count(iVehicleCategoryId) from ".$sql_vehicle_category_table_name." where iParentId = vc.iVehicleCategoryId AND eStatus != 'Deleted') as SubCategories FROM ".$sql_vehicle_category_table_name." as vc WHERE eStatus != 'Deleted' AND vc.iParentId='0' $ssql $rdr_ssql $not_sql $ord LIMIT $start, $per_page";
}
//echo $sql;exit;
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status

$eServiceType = !empty($eType) ? '&eServiceType='.$eType : '';
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div" class="vehicleCategorylist">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?> <?= $MasterServiceCategory ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?= $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php
                                            if ($eStatus == 'Active') {
                                                echo "selected";
                                            }
                                            ?> >Active</option>
                                            <option value="Inactive" <?php
                                            if ($eStatus == 'Inactive') {
                                                echo "selected";
                                            }
                                            ?> >Inactive</option>
                                            <?php if ($userObj->hasPermission('delete-vehicle-category')) { ?>
                                                <option value="Deleted" <?php
                                                if ($eStatus == 'Deleted') {
                                                    echo "selected";
                                                }
                                                ?>>Deleted</option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="hidden" value="<?=$_REQUEST['eType']?>" id="eType" name="eType" title="eType" />
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'vehicle_category.php<?= (!empty($eType) ? '?eType='.$eType : '') ?>'"/>
                                    </td>
                                    <?php if(!empty($eType)) {
                                        if($eType == "UberX") { ?>
                                            <?php if ($userObj->hasPermission('create-vehicle-category')) { ?>
                                            <td width="30%"><a class="add-btn" href="vehicle_category_action.php<?= (!empty($eType) ? '?eServiceType='.$eType : '') ?>" style="text-align: center;">Add <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?></a></td>
                                            <?php } ?>
                                        <?php } } else {
                                            if ($parent_ufx_catid == "0" && $ufxEnable == "Yes") { ?>
                                            <?php if ($userObj->hasPermission('create-vehicle-category')) { ?>
                                            <td width="30%"><a class="add-btn" href="vehicle_category_action.php<?= (!empty($eType) ? '?eServiceType='.$eType : '') ?>" style="text-align: center;">Add <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?></a></td>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export vehicle-cat">
                                    <!--<div class="changeStatus col-lg-12 option-box-left">
                                        <span class="col-lg-2 new-select001">
                                            <?php if ($userObj->hasPermission(['update-status-vehicle-category', 'delete-vehicle-category'])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-vehicle-category')) { ?>
                                                        <option value='Active' <?php
                                                        if ($option == 'Active') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                        <option value="Inactive" <?php
                                                        if ($option == 'Inactive') {
                                                            echo "selected";
                                                        }
                                                        ?> >View Subcategories</option>                                
                                                            <?php } ?>
                                                            <?php if ($userObj->hasPermission('delete-vehicle-category')) { ?>
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
                                </div>-->
                                <div style="clear:both;"></div>
                                <div class="table-responsive1">
                                    <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <div class="table table-striped  table-hover">
                                            <div class="profile-earning">
                                                <div class="partation">
                                                    <ul style="padding-left: 0px;" class="setings-list">
                                                        <?php
                                                        if (!empty($data_drv)) {
                                                            for ($i = 0; $i < scount($data_drv); $i++) {
                                                                $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/ios/3x_" . $data_drv[$i]['vLogo'];
                                                                
                                                                $bannerPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/" . $data_drv[$i]['vBannerImage'];
                                                                if ($data_drv[$i]['vLogo'] == "") {
                                                                    $logoPath = $bannerPath;
                                                                }

                                                                if($MODULES_OBJ->isEnableAppHomeScreenLayoutV2()) {
                                                                    $logoPath = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $data_drv[$i]['iVehicleCategoryId'] . "/" . $data_drv[$i]['vListLogo2'];
                                                                }
                                                                //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 Start
                                                                $buttonText = "View";
                                                                if ($data_drv[$i]['eCatType'] == "ServiceProvider") {
                                                                    $buttonText = "Add/ View";
                                                                }
                                                                //echo "<pre>";print_r($data_drv[$i]['eStatus']);die;
                                                                //$buttonStatus = "Active";
                                                                $buttonStatus = $data_drv[$i]['eStatus'];
                                                                $btnChecked = 0;
                                                                if ($data_drv[$i]['eStatus'] == "Active") {
                                                                    $btnChecked = 1;
                                                                    //$buttonStatus = "Inactive";
                                                                }
                                                                //Added By HJ On 30-07-2019 For Solved Bug - 225 Server - 4988 End
                                                                ?>
                                                                <li>
                                                                    <div class="toggle-list-inner">
                                                                        <div class="toggle-combo">
                                                                            <label><div align="center">             
                                                                                    <img src="<?= $logoPath; ?>" style="width:100px;">
                                                                                </div> <div style="margin: 0 0 0 10px;"> <td><?= $data_drv[$i]['vCategory']; ?></td></div>
                                                                            </label>
                                                                            <?php if ($userObj->hasPermission('update-status-vehicle-category')) { ?>
                                                                                <span class="toggle-switch">
                                                                                    <input type="checkbox" <?php if ($btnChecked > 0) { ?>checked=""<?php } ?> onClick="changeStatus('<?= $data_drv[$i]['iVehicleCategoryId']; ?>', '<?= $buttonStatus; ?>')" id="statusbutton" class="chk" name="statusbutton" value="246">
                                                                                    <span class="toggle-base"></span>
                                                                                </span>
                                                                            <?php } ?>
                                                                        </div>
                                                                        <div class="check-combo">
                                                                            <label id="defaultText_246">
                                                                                <ul>
                                                                                    <?php if ($userObj->hasPermission('edit-vehicle-category')) { ?>
                                                                                    <li class="entypo-twitter" data-network="twitter"><a href="vehicle_category_action.php?id=<?= $data_drv[$i]['iVehicleCategoryId'].$eServiceType; ?>" data-toggle="tooltip" title="Edit">
                                                                                            <img src="img/edit-new.png" alt="Edit">
                                                                                        </a></li>
                                                                                        <?php } ?>
                                                                                    <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-vehicle-category') && $data_drv[$i]['eCatType'] == "ServiceProvider") { ?>
                                                                                        <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatusDelete('<?= $data_drv[$i]['iVehicleCategoryId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                                                <img src="img/delete-new.png" alt="<?= $data_drv[$i]['eStatus']; ?>" >
                                                                                            </a></li>
                                                                                    <?php } if (($data_drv[$i]['eCatType'] != "ServiceProvider" && $data_drv[$i]['eCatType'] != "MoreDelivery") && $data_drv[$i]['iParentId'] == 0) { ?>
                                                                                    <?php } else { ?>
                                                                                        <?php if ($userObj->hasPermission('view-vehicle-category')) { ?>
                                                                                            <li class="entypo-gplus" data-network="gplus"><a href="vehicle_sub_category.php?sub_cid=<?= $data_drv[$i]['iVehicleCategoryId'].$eServiceType ?>" target="_blank" data-toggle="tooltip" title="View Subcategories">
                                                                                                    <img src="img/view-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>" >   
                                                                                                </a></li>
                                                                                        <?php
                                                                                        }
                                                                                    }
                                                                                    ?>

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
                                </div>
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
                            Main Category module will list all Main Category on this page.
                        </li>
                        <?php if ($userObj->hasPermission('delete-vehicle-category')) { ?>
                            <li>Administrator can Delete / View Subcategories / Delete any Main Category</li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
    <form name="pageForm" id="pageForm" action="action/vehicle_category.php" method="post" >
        <input type="hidden" name="page" id="page" value="<?= $page; ?>">
        <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
        <input type="hidden" name="iVehicleCategoryId" id="iMainId01" value="" >
        <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>" >
        <input type="hidden" name="status" id="status01" value="" >
        <input type="hidden" name="statusVal" id="statusVal" value="" >
        <input type="hidden" name="option" value="<?= $option; ?>" >
        <input type="hidden" name="keyword" value="<?= $keyword; ?>" >
        <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >
        <input type="hidden" name="order" id="order" value="<?= $order; ?>" >
        <input type="hidden" name="method" id="method" value="" >
        <input type="hidden" name="eType" value="<?= $eType ?>" >
    </form>
<?php include_once('footer.php'); ?>
    <script>
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
            //$('html').addClass('loading');
            var action = $("#_list_form").attr('action');
            //alert(action);
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
    </script>
</body>
<!-- END BODY-->
</html>
