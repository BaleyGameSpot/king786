<?php
include_once('../common.php');

$script = "dashboard";
/* ------------------------------ org outstanding report ----------------------------- */
/* ------------------------------ org outstanding report end ----------------------------- */
/* -------------------------- TOtal Ride/Job chart last 5 day -------------------------- */
$ridetotaldate = [];
$rideTotalbydate = [];
for ($i = 4; $i > 0; $i--) {
    $startdate = date("Y-m-d 00:00:00", strtotime("-$i days"));
    $enddate = date("Y-m-d 23:59:59", strtotime("-$i days"));
    $totalRides = getTripStates('finished', $startdate, $enddate, '1');
    $ridetotaldate[] = date("Y-m-d", strtotime("-$i days"));
    $rideTotalbydate[] = $totalRides;
}
$totalRides = getTripStates('finished', date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"), '1');
$ridetotaldate[] = date("Y-m-d");
$rideTotalbydate[] = $totalRides;

/* ---------------------- month wise finished and cancelled ride get--------------------- */
$month = [];
$finishRidetotalByMonth = [];
$cancelledRidetotalByMonth = [];
/* ----------------------  month wise  finished and cancelled ride get end --------------------- */
/* ---------------------- month wise finished and cancelled order get --------------------- */
$order_month = [];
$finishOrdertotalByMonth = [];
$cancelledOrdertotalByMonth = [];
/* ----------------------  month wise  finished and cancelled order get end --------------------- */
/* --------------------------- month wise earning --------------------------- */
$earning_month = [];
$total_Earns = [];
/* --------------------------- month wise earning --------------------------- */
/* --------------------------- six month wise earning --------------------------- */
$six_earning_month = [];
$six_total_Earns = [];
/* --------------------------- six month wise earning --------------------------- */
/* ------------------------------ for the order ----------------------------- */
$processing_status_array = array('1', '2', '4', '5', '12');
$all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');
if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature('Yes')) {
    $processing_status_array = array('1', '2', '4', '5', '12', '13', '14');
    $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12', '13', '14');
}
/* ------------------------------ for the order ----------------------------- */
$company = getCompanyDetailsDashboard();
$driver = getDriverDetailsDashboard('');
$rider_count = getRiderCount();
$rider = $rider_count[0]['count(iUserId)'];
$totalEarns = getTotalEarns();


$active_rider_count = getRiderCount('active');
$active_rider_count_no = $active_rider_count[0]['count(iUserId)'];
$inactive_rider_count = getRiderCount('inactive');
$inactive_rider_count_no = $inactive_rider_count[0]['count(iUserId)'];


$user_status = ['Active', 'Inactive'];
$user_number = [(int)$active_rider_count_no,(int)$inactive_rider_count_no];


$UpcomingRide = getUpcomingRideDashboard();
$vehicleTypeArr = array();
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType , vc.vCategory_" . $default_lang . " AS subService, vcc.vCategory_" . $default_lang . " AS Service FROM vehicle_type left join " . $sql_vehicle_category_table_name . " as vc on vehicle_type.iVehicleCategoryId = vc.iVehicleCategoryId left join " . $sql_vehicle_category_table_name . " as vcc on vc.iParentId = vcc.iVehicleCategoryId WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
    $vehicleTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
    $vehicleTypeArr[$getVehicleTypes[$r]['iVehicleTypeId'] . "_subService"] = $getVehicleTypes[$r]['subService'];
    $vehicleTypeArr[$getVehicleTypes[$r]['iVehicleTypeId'] . "_service"] = $getVehicleTypes[$r]['Service'];
}
$totalOrganization = getOrganizationCount();
$totalRides = getTripStates('total');
$onRides = getTripStates('on ride');
$finishRides = getTripStates('finished');
$cancelRides = getTripStates('cancelled');
$actDrive = getDriverDetailsDashboard('active');
$inaDrive = getDriverDetailsDashboard('inactive');


$SystemDiagnosticData = $DASHBOARD_OBJ->getSystemDiagnosticData();
$working = $missing = 0;
foreach ($SystemDiagnosticData as $SysData) {
    if ($SysData['value']) {
        $working++;
    } else {
        $missing++;
    }
}
$alerts = 3;
$server_status = ['Working', 'Errors', 'Alerts'];
$server_number = [$working, $missing, $alerts];
$server_working = $working;
$server_missing = $missing;
$currencyData = FetchDefaultCurrency();
$DefaultCurrencySymbol = $currencyData[0]['vSymbol'];
$dSetupYear = date('Y', strtotime($SETUP_INFO_DATA_ARR[0]['dSetupDate']));
// echo "<pre>"; print_r($dSetupYear); exit;
$permissions_row_1 = $permissions_row_col_1 = $permissions_row_col_2 = "No";
if ($userObj->hasPermission('view-users') || $userObj->hasPermission('view-providers') || $userObj->hasPermission('view-company') || ($userObj->hasPermission('view-store') && $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) || ($userObj->hasPermission('view-organization') && $MODULES_OBJ->isOrganizationModuleEnable()) || $userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('dashboard-total-orders')) {
    $permissions_row_1 = "Yes";
    if ($userObj->hasPermission('view-users') || $userObj->hasPermission('view-providers') || $userObj->hasPermission('view-company') || ($userObj->hasPermission('view-store') && $MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) || ($userObj->hasPermission('view-organization') && $MODULES_OBJ->isOrganizationModuleEnable())) {
        $permissions_row_col_1 = "Yes";
    }
    if ($userObj->hasPermission('dashboard-total-ride-jobs') || $userObj->hasPermission('dashboard-total-orders')) {
        $permissions_row_col_2 = "Yes";
    }
}
$org_enable = "No";
if ($MODULES_OBJ->isRideFeatureAvailable('Yes') && $MODULES_OBJ->isOrganizationModuleEnable()) {
    $org_enable = "Yes";
}
$style1 = "style = 'height:200px'";
$style2 = "style = 'height:115px'";
$style3 = "style = 'min-height:430px'";
$chartLoader = $tconfig['tsite_url_main_admin'] . "images/page-loader.gif";

$sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate,c.iCompanyId,rd.iDriverId FROM `document_list` AS dl LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid LEFT JOIN company AS c ON ( c.iCompanyId = dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store')) RIGHT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver') LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car') LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId AND dm.doc_name_EN != '' HAVING dm.doc_name_" . $default_lang . " != '' ORDER BY dl.edate  DESC LIMIT 0,5";
$db_notification = $obj->MySQLSelect($sql);
if (isset($_REQUEST['allnotification'])) {
    $sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate FROM `document_list` AS dl
        LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid
        LEFT JOIN company AS c ON (c.iCompanyId=dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store'))
        LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver')
        LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car')
        LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId ORDER BY dl.edate DESC";
    $db_notification = $obj->MySQLSelect($sql);
}
if($MODULES_OBJ->isEnableRentItemService()) {
    $iMasterServiceCategoryId = "8";
    $eTypesql = "";
    if($iMasterServiceCategoryId != ""){
        $eTypesql  = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
    }

    $eMasterType = get_value($master_service_category_tbl, 'eType', 'iMasterServiceCategoryId', $iMasterServiceCategoryId, '', 'true');
    $sql1 = "SELECT r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql} ORDER BY r.iRentItemPostId  DESC LIMIT 0,5";
    $db_bsr_data = $obj->MySQLSelect($sql1);
}
if($MODULES_OBJ->isEnableRentEstateService()) { 
    $iMasterServiceCategoryId1 = "9";
    $eTypesql1 = "";
    if($iMasterServiceCategoryId1 != ""){
        $eTypesql1  = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId1 . "'";
    }

    $eMasterType1 = get_value($master_service_category_tbl, 'eType', 'iMasterServiceCategoryId', $iMasterServiceCategoryId1, '', 'true');
    $sql2 = "SELECT r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql1} ORDER BY r.iRentItemPostId  DESC LIMIT 0,5";
    $db_bsr_data_generalitem = $obj->MySQLSelect($sql2);
}
if($MODULES_OBJ->isEnableRentCarsService()) {
    $iMasterServiceCategoryId2 = "10";
    $eTypesql2 = "";
    if($iMasterServiceCategoryId2 != ""){
        $eTypesql2 = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId2 . "'";
    }

    $eMasterType2 = get_value($master_service_category_tbl, 'eType', 'iMasterServiceCategoryId', $iMasterServiceCategoryId2, '', 'true');
    $sql3 = "SELECT r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql2} ORDER BY r.iRentItemPostId  DESC LIMIT 0,5";
    $db_bsr_data_cars = $obj->MySQLSelect($sql3);
}
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
    <title><?= $SITE_NAME; ?> | Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <![endif]-->
    <!-- GLOBAL STYLES -->
    <? include_once('global_files.php'); ?>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/new_main.css"/>
    <link rel="stylesheet" href="css/admin_new/dashboard.css">
    <script src="<?= $tconfig['tsite_url_main_admin'] ?>js/apexcharts.js"></script>
    <!-- END THIS PAGE PLUGINS-->
    <!--END GLOBAL STYLES -->
    <!-- PAGE LEVEL STYLES -->
    <!-- END PAGE LEVEL  STYLES -->
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 dasboard-main-responsive">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content" class="content_right">
        <div class="cintainerinner">

            <div class="row clearfix d-flex dashboard-stats">

                <?php if ($userObj->hasPermission('view-users')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head d-flex justify-space align-start">
                                    <strong>Users</strong>
                                    <?php if ($userObj->hasPermission('view-users')) {?>
                                        <a href="rider.php" class="viewsmall">View</a>
                                    <?php } ?>
                                </div>
                                <img id="UserStatuschart_loader" class="chart_loader"
                                     src="<?php echo $chartLoader; ?>">
                                <div id="UserStatuschart" <?php echo $style1; ?> ></div>
                            </div>
                            <div class="jobsrow">
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Today: </strong>
                                        <span class="count success-color"><?= number_format(scount(getUserDateStatus('today'))); ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>This Month: </strong>
                                        <span class="count success-color"><?= number_format(scount(getUserDateStatus('month'))); ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>This Year:</strong>
                                        <span class="count success-color"><?= number_format(scount(getUserDateStatus('year'))); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($userObj->hasPermission('dashboard-server-statistics') && $MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live") { ?>
                    <div class="col-sm-12 col-md-12 col-lg-6 dashboard-stats-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-head d-flex justify-space align-start">
                                    <strong>Server Statistics
                                        <small class="small-subtext">Last
                                            Updated: <?= date('d M Y') . " AT " . date('h:i A') ?></small>
                                    </strong>
                                    <?php if ($userObj->hasPermission('manage-server-admin-dashboard')) {?>
                                        <a href="server_admin_dashboard.php" class="viewsmall">View</a>
                                    <?php } ?>
                                </div>
                                <img id="serverStatuschart_loader" class="chart_loader"
                                     src="<?php echo $chartLoader; ?>">
                                <div id="serverStatuschart" <?php echo $style1; ?> ></div>
                            </div>
                            <div class="jobsrow">
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Working</strong>
                                        <span class="count success-color"><?= $server_working ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Errors</strong>
                                        <span class="count pending-color"><?= $server_missing ?></span>
                                    </div>
                                </div>
                                <div class="jobscol">
                                    <div class="card-foot">
                                        <strong>Alerts</strong>
                                        <span class="count proccess-color"><?= $alerts ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
 				

                <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')) { ?>
                    <div class="col-sm-12 col-md-12 col-lg-12 dashboard-stats-section">
                        <?php if (scount($latest_contactus) > 0) { ?>
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Contact Us Form Requests</strong>
                                        <?php if ($userObj->hasPermission('view-contactus-report')) { ?>
                                            <a href="contactus.php" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <ul class="statlist vertical Contact_Us">
                                        <?php $icon_color = 1; ?>

                                        <?php if (scount($latest_contactus) > 0 && !empty($latest_contactus)) {
                                            for ($i = 0; $i < scount($latest_contactus); $i++) {
                                                ?>
                                                <li>
                                                    <?php $tRequestDate = date("Y-m-d", strtotime($latest_contactus[$i]['tRequestDate']));
                                                    $queryString = "?action=search&iContactusId=" . $latest_contactus[$i]['iContactusId'];
                                                   // $queryString = "?action=search&searchUser=&searchDriver=&iContactusId=" . $latest_contactus[$i]['iContactusId'];
                                                    ?>
                                                    <?php if ($userObj->hasPermission('view-contactus-report')) { ?>
                                                        <a href="contactus.php<?= $queryString ?>" class="list-group-item" target="_blank">
                                                    <?php } else { ?>
                                                        <a href="#" class="list-group-item">
                                                    <?php } ?>
                                                        <?php if ($icon_color == 5) {
                                                            $icon_color = 1;
                                                        } ?>
                                                        <div>
                                                            <i class="icon-color<?= $icon_color ?> ri-notification-line"></i>
                                                            <div class="stat-block">
                                                                <b><?php echo clearName(validName($latest_contactus[$i]['vFirstname'] . ' ' . $latest_contactus[$i]['vLastname'])); ?> </b>
                                                                <span class="text-ellipse fullwidth"
                                                                      title="<?= clearGeneralText(removehtml($latest_contactus[$i]['tDescription'])); ?>"
                                                                      data-toggle="tooltip"
                                                                      data-placement="top"> <?= clearGeneralText(removehtml($latest_contactus[$i]['tDescription'])); ?></span>
                                                            </div>
                                                        </div>
                                                        <small class="text-color<?= $icon_color ?> normalfont">
                                                            <?= humanReadableTimingDashboard($latest_contactus[$i]['tRequestDate']); ?>
                                                        </small>
                                                    <?php if ($userObj->hasPermission('dashboard-contact-us-form Requests')) { ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        </a>
                                                    <?php } ?>
                                                </li>
                                                <?php $icon_color = $icon_color + 1 ?>
                                            <?php }
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card d-flex" <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="d-flex justify-space align-start">
                                        <strong>Contact Us Form Requests</strong>
                                        <a href="contactus.php" class="viewsmall">View All</a>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Contact Us Form Requests.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>


                <?php if ($userObj->hasPermission('dashboard-latest-ride-job')) { 
                    if($MODULES_OBJ->isEnableRentItemService()) {?>

                    <div class="col-sm-12 col-md-12 col-lg-12 dashboard-stats-section">
                        <?php if (scount($db_bsr_data) > 0) { ?>

                            <div class="card"  <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Latest General Item Listing</strong>
                                        <?php if ($userObj->hasPermission('view-all-'.strtolower($eMasterType))) { ?>
                                            <a href="all_bsr_items.php?eType=<?php echo getItemeTypeNew($eMasterType) ?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="common-table">
                                        <!-- Table -->
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Post Number</th>
                                                <th>User</th>
                                                <th>Listing Type</th>
                                                <th>Category</th>
                                                <th> Item Details</th>
                                                <th>Payment Plan</th>
                                                <th>Date of Posted</th>
                                                <th>Approved at</th>
                                                <th>Renewal Date</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php   foreach($db_bsr_data as $k => $value) { 
                                                 $reqArr = array('vCatName','eListingTypeWeb');
                                    
                                            $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value['iRentItemPostId'], "" , $default_lang,"","","",$reqArr);

                                            $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
                                            ?>
                                                <tr>
                                                    <td><?php echo $value['vRentItemPostNo'];?></td>
                                                    <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $value['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> <?= clearName($value['riderName']); ?> <?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>
                                                    <td><?php echo $getRentItemPostData['eListingTypeWeb'];?></td>
                                                    <td><?php echo $categoryDataArray[0];?><br/><?php if(trim($categoryDataArray[1]) != "") { echo "(". $categoryDataArray[1] .")"; } ?></td>
                                                    <td style="text-align: center;">
                                                        <?php if ($userObj->hasPermission('view-all-item-details-' . strtolower($eMasterType))) { ?>
                                                        <a class="btn btn-success btn-xs" href="item-details.php?iItemPostId=<?php echo $value['iRentItemPostId']?>" target="_blank">View Details</a>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $value['vPlanName'];?></td>
                                            <td style="text-align: center;"><?php echo DateTime($value['dRentItemPostDate']);?></td>
                                            <td style="text-align: center;"><?php echo  DateTime($value['dApprovedDate']);?></td>
                                            <td style="text-align: center;"><?php echo  DateTime($value['dRenewDate']);?><br/>
                                                   <?php $dRenewDate = strtotime($value['dRenewDate']);
                                                    $dApprovedDate = strtotime(date('Y-m-d H:i:s'));
                                                    $datediff = $dRenewDate - $dApprovedDate;
                                                    if($value['eStatus'] == "Approved" && $datediff > 0){
                                                        echo "(".round($datediff / (60 * 60 * 24)) ." days left)";
                                                    }
                                                    ?> 
                                            </td>
                                            <td style="text-align: center;"><?php echo $value['eStatus'];?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card d-flex <?php echo $style3; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-space align-start">
                                        <strong>Latest General Item Listing</strong>
                                        <?php if ($userObj->hasPermission('view-all-'.strtolower($eMasterType))) { ?>
                                            <a href="all_bsr_items.php?eType=<?php echo getItemeTypeNew($eMasterType) ?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Latest Item.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                <?php } if($MODULES_OBJ->isEnableRentEstateService()) {?>   

                    <div class="col-sm-12 col-md-12 col-lg-12 dashboard-stats-section">
                        <?php if (scount($db_bsr_data_generalitem) > 0) { ?>

                            <div class="card"  <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Latest Properties Listing</strong>
                                        <?php if ($userObj->hasPermission('view-all-'.strtolower($eMasterType1))) { ?>
                                            <a href="all_bsr_items.php?eType=<?php echo getItemeTypeNew($eMasterType1) ?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="common-table">
                                        <!-- Table -->
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Post Number</th>
                                                <th>User</th>
                                                <th>Listing Type</th>
                                                <th>Category</th>
                                                <th> Property Details</th>
                                                <th>Payment Plan</th>
                                                <th>Date of Posted</th>
                                                <th>Approved at</th>
                                                <th>Renewal Date</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php   foreach($db_bsr_data_generalitem as $k1 => $value1) { 
                                                 $reqArr = array('vCatName','eListingTypeWeb');
                                    
                                            $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value1['iRentItemPostId'], "" , $default_lang,"","","",$reqArr);

                                            $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
                                            ?>
                                                <tr>
                                                    <td><?php echo $value1['vRentItemPostNo'];?></td>
                                                    <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $value1['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> <?= clearName($value1['riderName']); ?> <?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>
                                                      <td><?php echo $getRentItemPostData['eListingTypeWeb'];?></td>
                                                    <td><?php echo $categoryDataArray[0];?><br/><?php if(trim($categoryDataArray[1]) != "") { echo "(". $categoryDataArray[1] .")"; } ?></td>
                                                    <td style="text-align: center;">
                                                        <?php if ($userObj->hasPermission('view-all-item-details-' . strtolower($eMasterType1))) { ?>
                                                        <a class="btn btn-success btn-xs" href="item-details.php?iItemPostId=<?php echo $value1['iRentItemPostId']?>" target="_blank">View Details</a>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $value1['vPlanName'];?></td>
                                            <td style="text-align: center;"><?php echo DateTime($value1['dRentItemPostDate']);?></td>
                                            <td style="text-align: center;"><?php echo  DateTime($value1['dApprovedDate']);?></td>
                                            <td style="text-align: center;"><?php echo  DateTime($value1['dRenewDate']);?><br/>
                                                   <?php $dRenewDate = strtotime($value1['dRenewDate']);
                                                    $dApprovedDate = strtotime(date('Y-m-d H:i:s'));
                                                    $datediff = $dRenewDate - $dApprovedDate;
                                                    if($value1['eStatus'] == "Approved" && $datediff > 0){
                                                        echo "(".round($datediff / (60 * 60 * 24)) ." days left)";
                                                    }
                                                    ?> 
                                            </td>
                                            <td style="text-align: center;"><?php echo $value1['eStatus'];?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card d-flex <?php echo $style3; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-space align-start">
                                        <strong>Latest Properties Listing</strong>
                                        <?php if ($userObj->hasPermission('view-all-'.strtolower($eMasterType1))) { ?>
                                            <a href="all_bsr_items.php?eType=<?php echo getItemeTypeNew($eMasterType1) ?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Latest Item.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                <?php } if($MODULES_OBJ->isEnableRentCarsService()) {?>    

                    <div class="col-sm-12 col-md-12 col-lg-12 dashboard-stats-section">
                        <?php if (scount($db_bsr_data_cars) > 0) { ?>

                            <div class="card"  <?php echo $style3; ?>>
                                <div class="card-body">
                                    <div class="card-head d-flex justify-space align-start">
                                        <strong>Latest Cars Listing</strong>
                                        <?php if ($userObj->hasPermission('view-all-'.strtolower($eMasterType2))) { ?>
                                            <a href="all_bsr_items.php?eType=<?php echo getItemeTypeNew($eMasterType2) ?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="common-table">
                                        <!-- Table -->
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Post Number</th>
                                                <th>User</th>
                                                <th>Listing Type</th>
                                                <th>Category</th>
                                                <th> Car Details</th>
                                                <th>Payment Plan</th>
                                                <th>Date of Posted</th>
                                                <th>Approved at</th>
                                                <th>Renewal Date</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php   foreach($db_bsr_data_cars as $k2 => $value2) { 
                                                 $reqArr = array('vCatName','eListingTypeWeb');
                                    
                                            $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value2['iRentItemPostId'], "" , $default_lang,"","","",$reqArr);

                                            $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
                                            ?>
                                                <tr>
                                                    <td><?php echo $value2['vRentItemPostNo'];?></td>
                                                    <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $value2['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> <?= clearName($value2['riderName']); ?> <?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>
                                                      <td><?php echo $getRentItemPostData['eListingTypeWeb'];?></td>
                                                    <td><?php echo $categoryDataArray[0];?><br/><?php if(trim($categoryDataArray[1]) != "") { echo "(". $categoryDataArray[1] .")"; } ?></td>
                                                    <td style="text-align: center;">
                                                        <?php if ($userObj->hasPermission('view-all-item-details-' . strtolower($eMasterType2))) { ?>
                                                        <a class="btn btn-success btn-xs" href="item-details.php?iItemPostId=<?php echo $value2['iRentItemPostId']?>" target="_blank">View Details</a>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $value2['vPlanName'];?></td>
                                            <td style="text-align: center;"><?php echo DateTime($value2['dRentItemPostDate']);?></td>
                                            <td style="text-align: center;"><?php echo  DateTime($value2['dApprovedDate']);?></td>
                                            <td style="text-align: center;"><?php echo  DateTime($value2['dRenewDate']);?><br/>
                                                   <?php $dRenewDate = strtotime($value2['dRenewDate']);
                                                    $dApprovedDate = strtotime(date('Y-m-d H:i:s'));
                                                    $datediff = $dRenewDate - $dApprovedDate;
                                                    if($value2['eStatus'] == "Approved" && $datediff > 0){
                                                        echo "(".round($datediff / (60 * 60 * 24)) ." days left)";
                                                    }
                                                    ?> 
                                            </td>
                                            <td style="text-align: center;"><?php echo $value2['eStatus'];?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card d-flex <?php echo $style3; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-space align-start">
                                        <strong>Latest Properties Listing</strong>
                                        <?php if ($userObj->hasPermission('view-all-'.strtolower($eMasterType2))) { ?>
                                            <a href="all_bsr_items.php?eType=<?php echo getItemeTypeNew($eMasterType2) ?>" class="viewsmall">View All</a>
                                        <?php } ?>
                                    </div>
                                    <div class="no-data-found">
                                        <strong>No Latest Item.</strong>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                
                <?php } } ?>

            </div>

        </div>

        <?php include_once('footer.php'); ?>
        <!--END PAGE CONTENT -->
    </div>

</div>

<script>
    var user_status = <?php echo json_encode($user_status); ?>;
    var user_number = <?php echo json_encode($user_number); ?>;

    var server_status = <?php echo json_encode($server_status); ?>;
    var server_number = <?php echo json_encode($server_number); ?>;

    /* --------------------------------- server_status_chart -------------------------------- */
    $(document).ready(function() {
        setTimeout(function() {
            getyear('', 'server_status_chart', '');

            /* --------------------------------- server_status_chart -------------------------------- */

        }, 5000);
    });

    function getyear(year, chart_type, getMonth) {
        var curr_elem = year;
        $(curr_elem).closest('.card-body').find('.chart_loader').show();
        var ridetotaldate = [];
        var rideTotalbydate = [];
        Y = '';
        if (year.value) {
            Y = year.value;
        }
    }

    var UserStatuschart = {
        series: user_number,
        labels: user_status,
        chart: {
            type: 'donut',
            height: 200,
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }],
        fill: {
            colors: ['#28c76f', '#ea5455']
        },
        legend: {
            show: true,

            markers: {
                fillColors: ['#28c76f', '#ea5455']
            },
        },
        tooltip: {
            colors: ['#28c76f', '#ea5455']
        },
        colors: ['#28c76f', '#ea5455']
    };

    var serverStatuschart = {
        series: server_number,
        labels: server_status,
        chart: {
            type: 'donut',
            height: 200,
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }],
        fill: {
            colors: ['#28c76f', '#ea5455', '#ff9900']
        },
        legend: {
            show: true,

            markers: {
                fillColors: ['#28c76f', '#ea5455', '#ff9900']
            },
        },
        tooltip: {
            colors: ['#28c76f', '#ea5455', '#ff9900']
        },
        colors: ['#28c76f', '#ea5455', '#ff9900']
    };

    $(window).load(function () {
        setTimeout(function () {
            var chart12 = new ApexCharts(document.querySelector("#serverStatuschart"), serverStatuschart);
            chart12.render();
            $("#serverStatuschart_loader").hide();


            var chart20 = new ApexCharts(document.querySelector("#UserStatuschart"), UserStatuschart);
            chart20.render();
            $("#UserStatuschart_loader").hide();

        }, 1000);
    });


    $(".common-table").mCustomScrollbar({
        axis: "x",
        theme: "minimal-dark",
        scrollInertia: 200
    });

    $(document).ready(function () {
    	console.log($('.dashboard-stats-section').length % 2);
        if ($('.dashboard-stats-section').length % 2 == 1) {
            $('.dashboard-stats-section:last-child').removeClass('col-lg-6').addClass('col-lg-12');
        }
    });
</script>

</body>
</html>