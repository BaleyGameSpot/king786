<?php
include_once('../common.php');

$script = 'masterServiceMenu';
$tbl_name = "master_service_menu";

if (!$userObj->hasPermission('manage-our-service-menu')) {
    $userObj->redirect();
}
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
$iServiceMenuId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

if (isset($status) && !empty($status)) {
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = "2";
        header("Location:master_service_menu.php");
        exit;
    }
    $obj->sql_query("UPDATE " . $tbl_name . " SET eStatus = '" . $status . "' WHERE iServiceMenuId = '" . $iServiceMenuId . "'");
}

$subquery = "";
if(!$MODULES_OBJ->isRideFeatureAvailable()) {
    $subquery .= " AND eType != 'Ride'";
}
if(!$MODULES_OBJ->isDeliveryFeatureAvailable() && !$MODULES_OBJ->isEnableGenieFeature() && !$MODULES_OBJ->isEnableRunnerFeature()) {
    $subquery .= " AND eType != 'Deliver'";
}
if(!$MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    $subquery .= " AND eType != 'DeliverAll'";   
}
if(!$MODULES_OBJ->isUberXFeatureAvailable()) {
    $subquery .= " AND eType != 'UberX'";  
}
if(!$MODULES_OBJ->isEnableVideoConsultingService()) {
    $subquery .= " AND eType != 'VideoConsult'";  
}
if(!$MODULES_OBJ->isEnableBiddingServices()) {
    $subquery .= " AND eType != 'Bidding'";  
}
if(!$MODULES_OBJ->isEnableMedicalServices()) {
    $subquery .= " AND eType != 'MedicalServices'";  
}
if(!$MODULES_OBJ->isEnableTrackServiceFeature()) {
    $subquery .= " AND eType != 'TrackService'";
}
if(!$MODULES_OBJ->isEnableRideShareService()) {
    $subquery .= " AND eType != 'RideShare'"; 
}
if (!$MODULES_OBJ->isEnableNearByService()) {
    $subquery .= " AND eType != 'NearBy'";
}
if(!$MODULES_OBJ->isEnableRentItemService()) {
    $subquery .= " AND eType != 'RentItem'";  
}
if(!$MODULES_OBJ->isEnableTrackServiceFeature()) {
    $subquery .= " AND eType != 'TrackService'";  
}
if(!$MODULES_OBJ->isEnableRideShareService()) {
    $subquery .= " AND eType != 'RideShare'";  
}
if (!$MODULES_OBJ->isEnableRentEstateService()) {
    $subquery .= " AND eType != 'RentEstate'";
}
if (!$MODULES_OBJ->isEnableRentCarsService()) {
    $subquery .= " AND eType != 'RentCars'";
}
if (!$MODULES_OBJ->isEnableNearByService()) {
    $subquery .= " AND eType != 'NearBy'";
}

if (!$MODULES_OBJ->isEnableTrackAnyServiceFeature()) {
    $subquery .= " AND eType != 'TrackAnyService'";
}
$sql = "SELECT *,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitle,iServiceMenuId as id,(select count(*) from master_service_menu where iParentId = id) as child_id FROM " . $tbl_name . " WHERE iParentId = 0 $subquery  AND eStatus != 'Deleted' ORDER BY iDisplayOrder ASC";

$master_service_menu = $obj->MySQLSelect($sql);


/* ---------------------------------- for number ---------------------------------- */

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$master_service_categories = $obj->MySQLSelect("SELECT *, JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_" . $default_lang . "')) as vCategoryName FROM $master_service_category_tbl WHERE 1 = 1 $subquery AND eStatus = 'Active'");
$not_sql = " AND iVehicleCategoryId != 297";
foreach ($master_service_categories as $key => $value) {
    $ssql = getMasterServiceCategoryQuery($value['eType'],'',$menu = "Yes");
    if($value['eType'] == "Ride") {
        $ssql .= " AND eForMedicalService = 'No' ";
    } elseif($value['eType'] == "VideoConsult") {
        $vc_data = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT(iParentId)) as ParentIds FROM vehicle_category WHERE eVideoConsultEnable = 'Yes'");
        $ssql .= " AND iVehicleCategoryId IN (" . $vc_data[0]['ParentIds'] . ")";
    } elseif ($value['eType'] == "Bidding") {
        $category_data[0]['Total'] = $BIDDING_OBJ->getBiddingTotalCount('admin');
    }  elseif ($value['eType'] == "MedicalServices") {
        $ssql .= " AND eForMedicalService = 'Yes' ";
    } 
  
    if(!in_array($value['eType'], ['Bidding','NearBy','TrackService','RideShare','RentCars','RentEstate','RentItem'])) {
        $parent_id_sql = " AND iParentId='0' ";
        if ($value['eType'] == "MedicalServices") {
            $parent_id_sql = " AND (iParentId='0' OR iParentId = '3') ";
        }
        if (in_array($value['eType'], ['UberX', 'VideoConsult']) && $MODULES_OBJ->isEnableMedicalServices('Yes')) {
            $ssql .= " AND iVehicleCategoryId NOT IN (3,22,26,158) ";
        }
        $category_data = $obj->MySQLSelect("SELECT COUNT(iVehicleCategoryId) AS Total FROM ".$sql_vehicle_category_table_name."  WHERE  1 = 1 $parent_id_sql AND eStatus = 'Active' $not_sql  $ssql");
    }
    $master_service_categories[$key]['SubCategories'] = $category_data[0]['Total'];
}

//exit;
$ssql = getMasterServiceCategoryQuery('DeliverAll','',$menu = "Yes");
if ($MODULES_OBJ->isEnableMedicalServices('Yes')) {
    $ssql .= " AND iServiceId NOT IN (5, 11) ";
}
$category_data = $obj->MySQLSelect("SELECT COUNT(iVehicleCategoryId) AS Total FROM ".$sql_vehicle_category_table_name."  WHERE  1 = 1 AND iVehicleCategoryId NOT IN (185) AND iParentId='0' AND eStatus = 'Active' $ssql");
$category = [];
foreach ($master_service_categories as $key => $a) {
        $category[$a['eType']] = $a['SubCategories'];
}

$i = 0;
foreach($master_service_menu as $service_menu)
{
    if($service_menu['eType'] == 'DeliverAll')
    {
        $master_service_menu[$i]['child_id'] = $category_data[0]['Total'];
    } else if (in_array($service_menu['eType'], ['NearBy','TrackAnyService','RideShare','RentCars','RentEstate','RentItem'])) {
         $master_service_menu[$i]['child_id'] = $service_menu['child_id'];
    }  else {
        $master_service_menu[$i]['child_id']  = $category[$service_menu['eType']];
    }
    $i++;
}
/* ---------------------------------- for number ---------------------------------- */


?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->

<head>
    <meta charset="UTF-8" />
    <title><?= $SITE_NAME ?> | Manage Our Service Menu</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once('global_files.php'); ?>
    <style type="text/css">
        .table>tbody>tr>td {
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
        <?php include_once('left_menu.php'); ?>

        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
                <div id="add-hide-show-div">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Manage Our Service Menu</h2>

                        </div>
                        <!-- <a href="master_service_menu_action.php">
                            <input type="button" value="ADD" class="add-btn">
                        </a> -->

                       <?php if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'No') { ?>
                       <!-- <a href="home_content_cubejekx.php">
                            <input type="button" value="Back to Listing" class="add-btn">
                        </a>-->
                        <?php } ?>
                    </div>
                    <hr />
                </div>
                <?php include('valid_msg.php'); ?>

                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="clear:both;"></div>
                            <div class="table-responsive">
                                <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Category Name</th>
                                                <th style="width: 200px; text-align: center;">Service Category</th>
                                                <th style="width: 150px; text-align: center;">Display Order</th>
                                                <th style="text-align: center;">Status</th>
                                                <th style="text-align: center;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($master_service_menu) && scount($master_service_menu) > 0) {
                                                foreach ($master_service_menu as $service_menu) { ?>
                                                    <tr>

                                                        <td><?= $service_menu['vTitle'] ?></td>
                                                        <td style="text-align: center;">
                                                            <?php if ($service_menu['child_id'] > 0) { ?>
                                                                <a class="add-btn-sub" href="menu_service.php?eType=<?= $service_menu['eType']; ?>&id=<?= $service_menu['iServiceMenuId']; ?>" target="_blank">Add/View (<?= $service_menu['child_id']; ?>) </a>
                                                            <?php } else {
                                                                echo "-";
                                                            } ?>
                                                           
                                                            
                                                        </td>
                                                        <td style="text-align: center;"><?= $service_menu['iDisplayOrder'] ?></td>
                                                        <td style="text-align: center;">
                                                            <?php
                                                            if ($service_menu['eStatus'] == 'Active') {
                                                                $status_img = "img/active-icon.png";
                                                            } else {
                                                                $status_img = "img/inactive-icon.png";
                                                            }
                                                            ?>
                                                            <img src="<?= $status_img; ?>" alt="image" data-toggle="tooltip" title="<?= $service_menu['eStatus']; ?>">
                                                        </td>
                                                        <td align="center" style="text-align:center;" class="action-btn001">
                                                            <div class="share-button openHoverAction-class" style="display: block;">
                                                                <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                <div class="social show-moreOptions for-two openPops_<?= $service_menu['iServiceMenuId']; ?>">
                                                                    <ul>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a href="master_service_menu_action.php?id=<?= $service_menu['iServiceMenuId']; ?>" data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>

                                                                        <li class="entypo-facebook" data-network="facebook">
                                                                            <a href="javascript:void(0);" onClick="window.location.href='master_service_menu.php?id=<?= $service_menu['iServiceMenuId']; ?>&status=Active'" data-toggle="tooltip" title="Activate"><img src="img/active-icon.png" alt="<?= $service_menu['eStatus']; ?>"></a>
                                                                        </li>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);" onClick="window.location.href='master_service_menu.php?id=<?= $service_menu['iServiceMenuId']; ?>&status=Inactive'" data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png" alt="<?= $service_menu['eStatus']; ?>">
                                                                            </a>
                                                                        </li>

                                                                    </ul>
                                                                </div>
                                                            </div>
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
                        <li>Administrator can Activate / Deactivate / Modify any "Our Services" menu category.</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->

    <?php include_once('footer.php'); ?>
    <script>
        $('.entypo-export').click(function(e) {
            e.stopPropagation();
            var $this = $(this).parent().find('div');
            $(".openHoverAction-class div").not($this).removeClass('active');
            $this.toggleClass('active');
        });

        $(document).on("click", function(e) {
            if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                $(".show-moreOptions").removeClass("active");
            }
        });
    </script>
</body>
<!-- END BODY-->

</html>