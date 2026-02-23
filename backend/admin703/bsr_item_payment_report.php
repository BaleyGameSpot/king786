<?php
include_once('../common.php');

$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";

if (!$userObj->hasPermission('report-'.strtolower($eMasterType))) {
    $userObj->redirect();
}

$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
$iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');

$script = $eMasterType.'Report';

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$rdr_ssql = "";

if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And dRentItemPostDate > '" . WEEK_DATE . "'";
}

//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';
$iRentItemId = isset($_REQUEST['iRentItemId']) ? $_REQUEST['iRentItemId'] : '';

$ord = ' ORDER BY r.iRentItemPostId  DESC';

if ($sortby == 1) {

    if ($order == 0)
        $ord = " ORDER BY riderName ASC";
    else
        $ord = " ORDER BY riderName DESC";

}

if ($sortby == 2) {

    if ($order == 0)
        $ord = " ORDER BY vPlanName ASC";
    else
        $ord = " ORDER BY vPlanName DESC";

}

if ($sortby == 3) {

    if ($order == 0)
        $ord = " ORDER BY vTitleCat ASC";
    else

       $ord = " ORDER BY vTitleCat DESC";

}
if ($sortby == 4) {

    if ($order == 0)
        $ord = " ORDER BY r.dRentItemPostDate ASC";
    else

       $ord = " ORDER BY r.dRentItemPostDate DESC";

}
if ($sortby == 5) {

    if ($order == 0)
        $ord = " ORDER BY r.fAmount ASC";
    else

       $ord = " ORDER BY r.fAmount DESC";

}
if ($sortby == 6) {

    if ($order == 0)
        $ord = " ORDER BY r.eStatus ASC";
    else

       $ord = " ORDER BY r.eStatus DESC";

}

if ($sortby == 7) {

    if ($order == 0)
        $ord = " ORDER BY r.ePaid ASC";
    else

       $ord = " ORDER BY r.ePaid DESC";

}

if ($sortby == 8) {

    if ($order == 0)
        $ord = " ORDER BY rc.iMasterServiceCategoryId ASC";
    else

       $ord = " ORDER BY rc.iMasterServiceCategoryId DESC";

}
//End Sorting
// Start Search Parameters

$ssql = '';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchPaymentPlan = isset($_REQUEST['searchPaymentPlan']) ? $_REQUEST['searchPaymentPlan'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
//$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';

if ($startDate != '') {
    $ssql .= " AND Date(r.dRentItemPostDate) >='" . $startDate . "'";
}

if ($endDate != '') {
    $ssql .= " AND Date(r.dRentItemPostDate) <='" . $endDate . "'";
}


if ($searchRider != '') {

    $ssql .= " AND r.iUserId ='" . $searchRider . "'";
}

if($searchPaymentPlan != ""){
    $ssql .= " AND r.iPaymentPlanId ='" . $searchPaymentPlan . "'";
}

if ($iRentItemId != '') {

    $ssql .= " AND r.iItemCategoryId ='" . $iRentItemId . "'";
}

$trp_ssql = "";

if (SITE_TYPE == 'Demo') {

    $trp_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";

}

$eTypesql = "";
if($iMasterServiceCategoryId != ""){
    $eTypesql  = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
}
//Pagination Start

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$totalData = $obj->MySQLSelect("SELECT COUNT(r.iRentItemPostId) AS Total,r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 $eTypesql $ssql $trp_ssql");


$total_results = $totalData[0]['Total'];

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


$sql = "SELECT r.vRentItemPostNo,r.iItemSubCategoryId,r.iItemCategoryId,r.iUserId,r.tFieldsArr,r.iRentItemPostId,r.dRentItemPostDate,r.eStatus,r.eUserPayment,r.ePaid,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,rp.eFreePlan,rp.iTotalPost,rp.fAmount as planamount,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(rc.vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId,u.vTimeZone FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql} {$ssql} {$trp_ssql}  {$ord} LIMIT {$start}, {$per_page}";

$db_trip = $obj->MySQLSelect($sql);

$endRecord = scount($db_trip);

$driverIdArr = $userIdArr = array();


$var_filter = "";

foreach ($_REQUEST as $key => $val) {

    if ($key != "tpages" && $key != 'page')

        $var_filter .= "&$key=" . stripslashes($val);

}


$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

$Today = Date('Y-m-d');

$tdate = date("d") - 1;

$mdate = date("d");

$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));

$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));

$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));

$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));

$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));

$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));

$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));

$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$Pmonday = date('Y-m-d', strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d', strtotime('sunday this week -1 week'));

$pSql = "";
if($iMasterServiceCategoryId != ""){
    $pSql  = " And iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
}

$rent_item_payment_plan = $RENTITEM_OBJ->getRentItemPaymentPlan("admin", $pSql, 0, 0, $default_lang);

$ordersql = " ORDER BY iMasterServiceCategoryId,iDisplayOrder";
$rSql = "AND iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "' AND ( estatus = 'Active' || estatus = 'Inactive' )";
$rentitem = $RENTITEM_OBJ->getRentItemMaster('admin',$rSql,0,0,$default_lang,$ordersql);

/*------------------For The Summery-----------------*/
$sql = "SELECT r.vRentItemPostNo,r.iUserId,r.iRentItemPostId,r.dRentItemPostDate,r.eStatus,r.eUserPayment,r.ePaid,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,rp.eFreePlan,rp.iTotalPost,rp.fAmount as planamount,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql} {$ssql} {$trp_ssql}";
$db_trip_summery = $obj->MySQLSelect($sql);
$TotalAmount = 0;
if(!empty($db_trip_summery)){ 
    foreach($db_trip_summery as $k => $value) {
        $pSql = "";
        if($iMasterServiceCategoryId != ""){
            $pSql  = " And rp.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
        }
  
        $getlog =  "SELECT rl.iRentItemPostId,rl.iPaymentPlanId, MAX(rl.iTotalPost) AS MaxTotalPost,r.tPaymentPlanDetails FROM rentitem_payment_log as rl LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=rl.iPaymentPlanId LEFT JOIN  rentitem_post as r on r.iRentItemPostId=rl.iRentItemPostId WHERE 1=1 AND rl.iUserId= '".$value['iUserId']."' $pSql ORDER BY MaxTotalPost DESC";
        $userlogData = $obj->MySQLSelect($getlog);
        $userlogData = $userlogData[0];                             
        if($value['eFreePlan'] == "Yes"){ 
        } else if ($value['iTotalPost'] == "0") { 
            $TotalAmount += $value['planamount'];
        } else {
            if($userlogData['iRentItemPostId'] == $value['iRentItemPostId']){      
                if(!empty($userlogData['tPaymentPlanDetails'])){
                    $tPaymentPlanDetails = json_decode($userlogData['tPaymentPlanDetails'], true);
                    $TotalAmount += $tPaymentPlanDetails['fAmount'];
                } else {
                    $TotalAmount += $value['planamount'];
                }
            }
        }
    }
}                                        
/*------------------For The Summery-----------------*/
?>

<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>

    <meta charset="UTF-8"/>

    <title><?= $SITE_NAME ?> | Payment Report</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <?php include_once('global_files.php'); ?>

    <style type="text/css">
        .form-group .row {
            padding: 0;
        }
        .pending-trip {
            cursor: pointer;
            position: absolute;
            margin: 2px 0 0 5px;
        }

        .table-striped > tbody > tr:nth-child(odd).setteled-class > td,
        .setteled-class{
            background-color:#bddac5 !important
        }

        .table-hover > tbody > tr:hover > td, .table-hover > tbody > tr:hover > th {
            background-color:#bddac5 !important
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
                        <h2>Payment Reports</h2>
                    </div>
                </div>
                <hr/>
            </div>

            <?php include('valid_msg.php'); ?>

            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">

                <div class="Posted-date mytrip-page">

                    <input type="hidden" name="action" value="search"/>

                    <?php if ($eMasterType == "RentItem") { ?>
                        <h3>Search Items ...</h3>
                    <?php } else if ($eMasterType == "RentEstate") { ?>
                        <h3>Search Properties ...</h3>
                    <?php } else if ($eMasterType == "RentCars") { ?>
                        <h3>Search Cars ...</h3>
                    <?php } ?>

                    <span>
                        <a style="cursor:pointer" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>

                        <a style="cursor:pointer"  onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>

                        <a style="cursor:pointer" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>

                        <a style="cursor:pointer" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>

                        <a style="cursor:pointer" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>

                        <a style="cursor:pointer" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>

                        <a style="cursor:pointer" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>

                        <a style="cursor:pointer" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                    </span>
                </div>

                <div class="Posted-date form-group">
                    <div class="row">
                        <div class="col-lg-3">
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control selectedDate" value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>

                        <div class="col-lg-3">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control selectedDate"  value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>

                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name='searchRider'  data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>"  id="searchRider">
                                <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name='searchPaymentPlan'  data-text="Select Payment Plan"  id="searchPaymentPlan">

                                <option value="">Select Payment Plan</option>
                                <?php foreach($rent_item_payment_plan as $k=>$PaymentPlanData){ ?>
                                    <option value="<?php echo $PaymentPlanData['iPaymentPlanId'];?>" <?php if($PaymentPlanData['iPaymentPlanId'] == $searchPaymentPlan){ echo 'selected';}?> > <?php echo $PaymentPlanData['vPlanName'];?> </option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                         <div class="col-lg-3">
                            <select name="iRentItemId" class="form-control filter-by-text"  id="iRentItemId">
                                <option value="">Select Category</option>
                                <?php
                                foreach ($rentitem as $rentkey => $rentitemval) { ?>
                                    <option value="<?= $rentitemval['iRentItemId'];?>"  <?= $rentitemval['iRentItemId'] == $iRentItemId ? "selected" : "" ?> ><?= $rentitemval['vTitle']?></option>
                                <?php  } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tripBtns001">
                    <b>
                         <?php $reloadurl = "bsr_item_payment_report.php?eType=".$_REQUEST['eType']; ?>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"  title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"  onClick="window.location.href = '<?php echo $reloadurl;?>'"/>
                        <?php if (!empty($db_trip)) { ?>
                            <button type="button" onclick="showExportTypes('payment_report')" class="export-btn001">Export</button>
                        <?php } ?>
                    </b>

                </div>

            </form>


            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                <input type="hidden" id="actionpay" name="actionpay" value="pay_user">
                                <input type="hidden" name="ePayUser" id="ePayUser" value="">
                                <input type="hidden" name="prev_start" id="prev_start" value="<?= $startDate ?>">
                                <input type="hidden" name="prev_end" id="prev_end" value="<?= $endDate ?>">
                                <input type="hidden" name="prev_order" id="prev_order" value="<?= $order ?>">
                                <input type="hidden" name="prev_sortby" id="prev_sortby" value="<?= $sortby ?>">
                                <input type="hidden" name="prevsearchUser" id="prevsearchUser" value="<?= $searchUser ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="8%">Post Number</th>

                                            <th width="12%"> <a href="javascript:void(0);" onClick="Redirect(1,  <?php  if ($sortby == '1') {  echo $order; } else { ?>0<?php } ?>)">User <?php if ($sortby == 1) {  if ($order == 0) { ?><i class="fa fa-sort-amount-asc"  aria-hidden="true"></i> <?php } else { ?><i  class="fa fa-sort-amount-desc"  aria-hidden="true"></i><?php } } else { ?><i class="fa fa-sort" aria-hidden="true"></i><?php } ?></a></th>
                                            <?php //if($eMasterType != "RentEstate") { ?>
                                                <th width="8%">Listing Type</th>
                                            <?php //} ?>


                                            <th width="12%" class="align-left">  <a href="javascript:void(0);" onClick="Redirect(3,
                                             <?php if ($sortby == '3') {  echo $order;  } else { ?>0 <?php } ?>)">Category <?php if ($sortby == 3) {   if ($order == 0) { ?><i class="fa fa-sort-amount-asc"  aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc"   aria-hidden="true"></i><?php } } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a> </th>

                                            <?php if($eMasterType == "RentItem"){ ?>
                                                
                                                <th width="12%" style="text-align: center;"> Item Details</th>
                                            <?php } else if($eMasterType == "RentEstate") { ?>
                                                
                                                <th width="12%" style="text-align: center;"> Property Details</th>
                                            <?php } else if($eMasterType == "RentCars") { ?>
                                                
                                                <th width="12%" style="text-align: center;"> Car Details</th>
                                            <?php } ?>


                                            <th width="8%" class="align-left">  <a href="javascript:void(0);" onClick="Redirect(2,
                                             <?php if ($sortby == '2') {  echo $order;  } else { ?>0<?php } ?>)">Payment Plan <?php if ($sortby == 2) {   if ($order == 0) { ?><i class="fa fa-sort-amount-asc"  aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc"   aria-hidden="true"></i><?php } } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a> </th>
                                    
                                            <th width="12%" style="text-align: center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {    echo $order; } else {  ?>0<?php } ?>)">Date of Posted <?php if ($sortby == 4) { if ($order == 0) { ?><i class="fa fa-sort-amount-asc"  aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc"aria-hidden="true"></i><?php }  } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                            <th width="10%"  style="text-align: right;"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') {    echo $order; } else {  ?>0<?php } ?>)"> Amount <?php if ($sortby == 5) { if ($order == 0) { ?><i class="fa fa-sort-amount-asc"  aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc"aria-hidden="true"></i><?php }  } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                            <th width="12%" style="text-align: center;"><a href="javascript:void(0);" onClick="Redirect(6, <?php

                                                if ($sortby == '6') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)">
                                                    Status
                                                    <?php if ($sortby == 6) {
                                                        if ($order == 0) {
                                                            ?><i class="fa fa-sort-amount-asc"

                                                                 aria-hidden="true"></i> <?php } else { ?><i

                                                                class="fa fa-sort-amount-desc"

                                                                aria-hidden="true"></i><?php }

                                                    } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php if(!empty($db_trip)){ 
                                        $systemTimeZone = date_default_timezone_get();
                                        foreach($db_trip as $k => $value) {
                                                $class_setteled = "";
                                                if ($value['eUserPayment'] == 'Settled') {
                                                    $class_setteled = "setteled-class";
                                                }
                                                $getRentItemPostData = $RENTITEM_OBJ->getFieldsDataArray($value['tFieldsArr'],$value['iRentItemPostId'],$default_lang);
                                                
                                                if($value['iItemSubCategoryId'] > 0 ){
                                                    $reqArr = array('vTitle');
                                                    $subsql = " AND iRentItemId = '" . $value['iItemSubCategoryId'] . "'";
                                                    $DatanewArr = $RENTITEM_OBJ->getRentItemSubCategory('webservice', $value['iItemCategoryId'], $subsql, '', '', $default_lang, '', $reqArr);
                                                }

                                                if(!empty($DatanewArr)){
                                                    $vCatName = $value['vTitleCat'] ." (". $DatanewArr[0]['vTitle'] .")" ;
                                                } else {
                                                    $vCatName = $value['vTitleCat'];
                                                }

                                                $pSql = "";
                                                if($iMasterServiceCategoryId != ""){
                                                    $pSql  = " And rp.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
                                                }
                                          
                                                $getlog =  "SELECT rl.iRentItemPostId,rl.iPaymentPlanId, MAX(rl.iTotalPost) AS MaxTotalPost,r.tPaymentPlanDetails FROM rentitem_payment_log as rl LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=rl.iPaymentPlanId LEFT JOIN  rentitem_post as r on r.iRentItemPostId=rl.iRentItemPostId WHERE 1=1 AND rl.iUserId= '".$value['iUserId']."' $pSql ORDER BY MaxTotalPost DESC";
                                                $userlogData = $obj->MySQLSelect($getlog);
                                                $userlogData = $userlogData[0];

                                                $date_format_data_array = array(
                                                    'tdate' => (!empty($value['vTimeZone'])) ? converToTz($value['dRentItemPostDate'],$value['vTimeZone'], $systemTimeZone) : $value['dRentItemPostDate'],
                                                    'langCode' => $default_lang,
                                                    'DateFormatForWeb' => 1
                                                );
                                                $get_dRentItemPostDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                                                ?>
                                            <tr class="gradeA <?= $class_setteled ?>">
                                                <td><?php echo $value['vRentItemPostNo'];?></td>
                                                 <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $value['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> 
                                                <?= clearName($value['riderName']); ?>
                                                <?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>
                                                <td><?php echo $getRentItemPostData[$value['iRentItemPostId']]['eListingTypeWeb'];?></td>

                                                <td><?php echo $vCatName;?></td>
                                                
                                                <td style="text-align: center;"><a class="btn btn-success btn-xs" href="item-details.php?iItemPostId=<?php echo $value['iRentItemPostId']?>" target="_blank">View Details</a></td>
                                                <td><?php echo $value['vPlanName'];?></td>
                                                <td style="text-align: center;"><?php echo $get_dRentItemPostDate_format['tDisplayDate']."<br/>".$get_dRentItemPostDate_format['tDisplayTime'].$time_zone_difference_text ;//DateTime($value['dRentItemPostDate']);?></td>
                                                <td style="text-align: right;">
                                                    <?php 
                                                    if($value['eFreePlan'] == "Yes"){ 
                                                        echo "-"; 
                                                    } else if ($value['iTotalPost'] == "0") {
                                                        echo formateNumAsPerCurrency($value['planamount'],""); 
                                                    } else {
                                                        $planvalue = "";
                                                        if($userlogData['iRentItemPostId'] == $value['iRentItemPostId']){      
                                                            if(!empty($userlogData['tPaymentPlanDetails'])){
                                                                $tPaymentPlanDetails = json_decode($userlogData['tPaymentPlanDetails'], true);
                                                                $planvalue = formateNumAsPerCurrency($tPaymentPlanDetails['fAmount'],"");
                                                            } else {
                                                                $planvalue = formateNumAsPerCurrency($value['planamount'],""); 
                                                            }
                                                        }
                                                        
                                                        if($planvalue == ""){
                                                            echo '-';
                                                        } else {
                                                            echo $planvalue;
                                                        }

                                                    }?>
                                                </td>
                                                <td style="text-align: center;"><?php echo $value['eStatus'];?></td>
                                            </tr>
                                        <?php } ?>
                  

                                    <?php } else { ?>
                                            <td colspan="10" >No Records Found.</td>
                                    <? } ?>
                                    </tbody>
                                </table>
                            </form>

                            <?php include('pagination_n.php'); ?>
                        </div>
                   </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-lg-offset-6">
                    <div class="admin-notes">
                        <h4>Summary:</h4>
                        <ul>
                            <li><strong>Total Amount: </strong><?= formateNumAsPerCurrency($TotalAmount, ''); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<form name="pageForm" id="pageForm" action="action/bsr_item_payment_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="action" value="<?= $action; ?>">
    <input type="hidden" name="searchPaymentPlan" value="<?= $searchPaymentPlan; ?>">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?= $vStatus; ?>">
    <input type="hidden" name="eType" value="<?php echo $_REQUEST['eType'];?>">
    <input type="hidden" name="promocode" value="<?= $promocode; ?>">
    <!-- for reset -->
    <input type="hidden" name="iTripId" id="iMainId01" value="">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="iRentItemId" value="<?= $iRentItemId;?>">
</form>

</div>
<? include_once('footer.php'); ?>
<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i><?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<? include_once('searchfunctions.php'); ?>
<script>

    $('#dp4').datepicker().on('changeDate', function (ev) {
        var endDate = $('#dp5').val();
        if (ev.date.valueOf() < endDate.valueOf()) {
            $('#alert').show().find('strong').text('The start date can not be greater then the end date');
        } else {
            $('#alert').hide();
            var startDate = new Date(ev.date);
            $('#startDate').text($('#dp4').data('date'));
        }
        $('#dp4').datepicker('hide');
    });
    $('#dp5').datepicker().on('changeDate', function (ev) {
        var startDate = $('#dp4').val();
        if (ev.date.valueOf() < startDate.valueOf()) {
            $('#alert').show().find('strong').text('The end date can not be less then the start date');
        } else {
            $('#alert').hide();
            var endDate = new Date(ev.date);
            $('#endDate').text($('#dp5').data('date'));
        }
        $('#dp5').datepicker('hide');
    });

    $(document).ready(function () {
        $("#dp5").click(function () {
            $('#dp5').datepicker('show');
            $('#dp4').datepicker('hide');
        });
        $("#dp4").click(function () {
            $('#dp4').datepicker('show');
            $('#dp5').datepicker('hide');
        });
        if ('<?= $startDate ?>' != '') {
            $("#dp4").val('<?= $startDate ?>');
            $("#dp4").datepicker('update', '<?= $startDate ?>');
        }
        if ('<?= $endDate ?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate; ?>');
            $("#dp5").val('<?= $endDate; ?>');
        }
    });

    function todayDate() {

        $("#dp4").val('<?= $Today; ?>');

        $("#dp5").val('<?= $Today; ?>');

    }

    function reset() {

        location.reload();

    }

   function yesterdayDate() {

        $("#dp4").val('<?= $Yesterday; ?>');

        $("#dp4").datepicker('update', '<?= $Yesterday; ?>');

        $("#dp5").datepicker('update', '<?= $Yesterday; ?>');

        $("#dp4").change();

        $("#dp5").change();

        $("#dp5").val('<?= $Yesterday; ?>');

    }

    function currentweekDate(dt, df) {

        $("#dp4").val('<?= $monday; ?>');

        $("#dp4").datepicker('update', '<?= $monday; ?>');

        $("#dp5").datepicker('update', '<?= $sunday; ?>');

        $("#dp5").val('<?= $sunday; ?>');

    }

    function previousweekDate(dt, df) {

        $("#dp4").val('<?= $Pmonday; ?>');

        $("#dp4").datepicker('update', '<?= $Pmonday; ?>');

        $("#dp5").datepicker('update', '<?= $Psunday; ?>');

        $("#dp5").val('<?= $Psunday; ?>');

    }

    function currentmonthDate(dt, df) {

        $("#dp4").val('<?= $currmonthFDate; ?>');

        $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');

        $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');

        $("#dp5").val('<?= $currmonthTDate; ?>');

    }

    function previousmonthDate(dt, df) {

        $("#dp4").val('<?= $prevmonthFDate; ?>');

        $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');

        $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');

        $("#dp5").val('<?= $prevmonthTDate; ?>');

    }

    function currentyearDate(dt, df) {

        $("#dp4").val('<?= $curryearFDate; ?>');

        $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');

        $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');

        $("#dp5").val('<?= $curryearTDate; ?>');

    }

    function previousyearDate(dt, df) {

        $("#dp4").val('<?= $prevyearFDate; ?>');

        $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');

        $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');

        $("#dp5").val('<?= $prevyearTDate; ?>');

    }

    $("#Search").on('click', function () {

        if ($("#dp5").val() < $("#dp4").val()) {

            alert("From date should be lesser than To date.")

            return false;

        } else {

            var action = $("#_list_form").attr('action');

            var formValus = $("#frmsearch").serialize();

            //window.location.href = action + "?" + formValus;

            window.location.href = action + "?eType=<?php echo $_REQUEST['eType'];?>&" + formValus;

        }

    });
    

</script>
</body>
<!-- END BODY-->
</html>