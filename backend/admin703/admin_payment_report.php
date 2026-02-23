<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-admin-earning')) {
    $userObj->redirect();
}
$script = 'Admin Payment_Report';
$eSystem = " AND eSystem = 'DeliverAll'";
function cleanNumber($num) {
    return str_replace(',', '', $num);
}
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY o.iOrderId DESC';

if ($sortby == 1) {
    if ($order == 0){
        $ord = " ORDER BY c.vCompany ASC";
    }else{
        $ord = " ORDER BY c.vCompany DESC";
    }  
}
if ($sortby == 4) {
    if ($order == 0){
        $ord = " ORDER BY o.tOrderRequestDate ASC";
    }else{
        $ord = " ORDER BY o.tOrderRequestDate DESC";
    }    
}

if ($sortby == 5) {
    if ($order == 0){
        $ord = " ORDER BY o.ePaymentOption ASC";
    } else {
        $ord = " ORDER BY o.ePaymentOption DESC";
    }    
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$serachOrderNo = isset($_REQUEST['serachOrderNo']) ? $_REQUEST['serachOrderNo'] : '';
$searchRestaurantPayment = isset($_REQUEST['searchRestaurantPayment']) ? $_REQUEST['searchRestaurantPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';

if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
    }
    if ($serachOrderNo != '') {
        $ssql .= " AND o.vOrderNo ='" . $serachOrderNo . "'";
    }
    if ($searchCompany != '') {
        $ssql .= " AND c.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchServiceType != '' && !in_array($searchServiceType, ['Genie', 'Runner', 'Anywhere'])) {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "' AND o.eBuyAnyService ='No'";
    }
    if ($searchServiceType == "Genie") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
    }
    if ($searchServiceType == "Runner") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
    }
    if ($searchRestaurantPayment != '') {
        $ssql .= " AND o.eRestaurantPaymentStatus ='" . $searchRestaurantPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND o.ePaymentOption ='" . $searchPaymentType . "'";
    }
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}
$ssql .= " AND sc.iServiceId IN(".$enablesevicescategory.")";
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

//Added By HJ On 31-06-2020 For Optimization Query Start
$sql1 = "SELECT o.iUserId,o.iDriverId,o.iOrderId,o.vOrderNo,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fTax,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.fOutStandingAmount,o.iStatusCode,os.vStatus,o.fTipAmount,o.eBuyAnyService,o.ePaymentOption FROM orders AS o LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode = '6' $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql1);

$orderIdArr = $companyIdArr =$userIdArr=$driverIdArr=$tripDataArr=$companyDataArr=$userDataArr=$driverDataArr= array();
for($g=0;$g<scount($totalData);$g++){
    $orderIdArr[] = $totalData[$g]['iOrderId'];
    $companyIdArr[] = $totalData[$g]['iCompanyId'];
    $userIdArr[] = $totalData[$g]['iUserId'];
    $driverIdArr[] = $totalData[$g]['iDriverId'];
}

if(scount($orderIdArr) > 0){
    $orderIdArr = array_unique($orderIdArr, SORT_REGULAR);
    $implodeOrderIds= implode(",",$orderIdArr);
    $tripData = $obj->MySQLSelect("SELECT fDeliveryCharge,iOrderId FROM trips WHERE iOrderId IN ($implodeOrderIds)");
    for($t=0;$t<scount($tripData);$t++){
        $tripDataArr[$tripData[$t]['iOrderId']] = $tripData[$t];
    }
}

if(scount($companyIdArr) > 0){
    $companyIdArr = array_unique($companyIdArr, SORT_REGULAR);
    $implodeCompanyIds= implode(",",$companyIdArr);
    $companyData = $obj->MySQLSelect("SELECT iCompanyId,vCompany,CONCAT(vCode,' ',vPhone) as resturant_phone FROM company WHERE iCompanyId IN ($implodeCompanyIds)");
    for($c=0;$c<scount($companyData);$c++){
        $companyDataArr[$companyData[$c]['iCompanyId']] = $companyData[$c];
    }
}
if(scount($userIdArr) > 0){
    $userIdArr = array_unique($userIdArr, SORT_REGULAR);
    $implodeUserIds= implode(",",$userIdArr);
    $userData = $obj->MySQLSelect("SELECT concat(vName,' ',vLastName) as riderName,CONCAT(vPhoneCode,' ',vPhone)  as user_phone,iUserId FROM register_user WHERE iUserId IN ($implodeUserIds)");
    for($u=0;$u<scount($userData);$u++){
        $userDataArr[$userData[$u]['iUserId']] = $userData[$u];
    }
}    

if(scount($driverIdArr) > 0){
    $driverIdArr = array_unique($driverIdArr, SORT_REGULAR);
    //echo "<pre>";print_r($driverIdArr);die;
    $implodeDriverIds= implode(",",$driverIdArr);
    $driverData = $obj->MySQLSelect("SELECT concat(vName,' ',vLastName) as drivername,CONCAT(vCode,' ',vPhone) as driver_phone,iDriverId FROM register_driver WHERE iDriverId IN ($implodeDriverIds)");
    for($d=0;$d<scount($driverData);$d++){
        $driverDataArr[$driverData[$d]['iDriverId']] = $driverData[$d];
    }
}

//Added By HJ On 31-06-2020 For Optimization Query End

//Added By HJ On 21-09-2020 For Optimize loop Query Start
$OrderItemBuyArr = array();
$order_buy_anything = $obj->MySQLSelect("SELECT eConfirm,fItemPrice,iOrderId FROM order_items_buy_anything");
for($b=0;$b<scount($order_buy_anything);$b++){
    $OrderItemBuyArr[$order_buy_anything[$b]['iOrderId']][]  = $order_buy_anything[$b];
}

//Added By HJ On 21-09-2020 For Optimize loop Query End

$tot_order_amount =$tot_site_commission=$tot_delivery_charges=$tot_offer_discount=$tot_admin_payment=$tot_outstanding_amount= $tot_admin_tax= 0.00;
foreach ($totalData as $dtps) {
    $orderId = $dtps['iOrderId'];
    $totalfare = $dtps['fTotalGenerateFare'];
    $fOffersDiscount = $dtps['fOffersDiscount'];
    $fDeliveryCharge = $dtps['fDeliveryCharge'];
    $site_commission = $dtps['fCommision'];
    $fOutStandingAmount = $dtps['fOutStandingAmount'];
    $fTipAmount = $dtps['fTipAmount'];
    $fTax = $dtps['fTax'];

    $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);
    $siteearnig = cleanNumber($site_commission) + cleanNumber($fDeliveryCharge) + cleanNumber($fOutStandingAmount) + cleanNumber($fTipAmount)- cleanNumber($fTax);
    $tripDelCharge = 0;
    if(isset($tripDataArr[$orderId])){
        $tripDelCharge = $tripDataArr[$orderId]['fDeliveryCharge'];
    }
    $subtotal = 0;
    if($dtps['eBuyAnyService'] == "Yes" && $dtps['ePaymentOption'] == "Card")
    {
        //$order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId = '" . $orderId . "'");
        //Added By HJ On 21-09-2020 For Optimize loop Query Start
        $order_buy_anything = array();
        if(isset($OrderItemBuyArr[$orderId])){
            $order_buy_anything = $OrderItemBuyArr[$orderId];
        }
        //Added By HJ On 21-09-2020 For Optimize loop Query End
        if(scount($order_buy_anything) > 0)
        {
            foreach ($order_buy_anything as $oItem) {
                if($oItem['eConfirm'] == "Yes")
                {
                    $subtotal += $oItem['fItemPrice'];    
                }
            }
        }
    }

    $driverearning = $tripDelCharge + $dtps['fTipAmount'] + $subtotal;
    $adminearning = $siteearnig - cleanNumber($driverearning);
    if($dtps['eBuyAnyService'] == "Yes")
    {
        $adminearning = cleanNumber($site_commission) + cleanNumber($fOutStandingAmount);
    }
    $tot_order_amount = $tot_order_amount + cleanNumber($totalfare);
    $tot_offer_discount = $tot_offer_discount + cleanNumber($fOffersDiscount);
    $tot_delivery_charges = $tot_delivery_charges + cleanNumber($fDeliveryCharge);
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $tot_outstanding_amount = $tot_outstanding_amount + cleanNumber($fOutStandingAmount);
    $tot_admin_payment = $tot_admin_payment + cleanNumber($adminearning);
    $tot_admin_tax = $tot_admin_tax + cleanNumber($fTax);
}

$total_results = scount($totalData);
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
//Added By HJ On 31-06-2020 For Optimization Query Start
$sql = "SELECT o.iUserId,o.iDriverId,o.iOrderId,o.vOrderNo,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.fTax,o.eRestaurantPaymentStatus,o.ePaymentOption,o.fOutStandingAmount,o.iStatusCode,os.vStatus,o.fTipAmount,o.eBuyAnyService, o.eForPickDropGenie,o.vTimeZone FROM orders AS o LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode = '6' $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);

for($m=0;$m<scount($db_trip);$m++){
    $iOrderId = $db_trip[$m]['iOrderId'];
    $iCompanyId = $db_trip[$m]['iCompanyId'];
    $iUserId = $db_trip[$m]['iUserId'];
    $iDriverId = $db_trip[$m]['iDriverId'];
    $vCompany = $resturant_phone =$drivername=$driver_phone=$riderName=$user_phone= "";
    if(isset($companyDataArr[$iCompanyId])){
        $vCompany = $companyDataArr[$iCompanyId]['vCompany'];
        $resturant_phone = $companyDataArr[$iCompanyId]['resturant_phone'];
    }
    $db_trip[$m]['vCompany'] = $vCompany;
    $db_trip[$m]['resturant_phone'] = $resturant_phone;

    if(isset($driverDataArr[$iDriverId])){
        $drivername = $driverDataArr[$iDriverId]['drivername'];
        $driver_phone = $driverDataArr[$iDriverId]['driver_phone'];
    }
    $db_trip[$m]['drivername'] = $drivername;
    $db_trip[$m]['driver_phone'] = $driver_phone;
    if(isset($userDataArr[$iUserId])){
        $riderName = $userDataArr[$iUserId]['riderName'];
        $user_phone = $userDataArr[$iUserId]['user_phone'];
    }
    $db_trip[$m]['riderName'] = $riderName;
    $db_trip[$m]['user_phone'] = $user_phone;
    $driverearning = 0;
    if(isset($tripDataArr[$iOrderId])){
        $driverearning = $tripDataArr[$iOrderId]['fDeliveryCharge'];
    }

    $subtotal = 0;
    if($db_trip[$m]['eBuyAnyService'] == "Yes" && $db_trip[$m]['ePaymentOption'] == "Card")
    {
        $order_buy_anything = array();
        if(isset($OrderItemBuyArr[$iOrderId])){
            $order_buy_anything = $OrderItemBuyArr[$iOrderId];
        }
        //Added By HJ On 21-09-2020 For Optimize loop Query End
        if(scount($order_buy_anything) > 0)
        {
            foreach ($order_buy_anything as $oItem) {
                if($oItem['eConfirm'] == "Yes")
                {
                    $subtotal += $oItem['fItemPrice'];    
                }
            }
        }
    }
    $db_trip[$m]['item_subtotal'] = $subtotal;
    $db_trip[$m]['driverearning'] = $driverearning;
    
}
//Added By HJ On 31-06-2020 For Optimization Query End
$endRecord = scount($db_trip);
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

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
$cardText = "Card";
if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    $cardText = "Wallet";
}
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 End
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Deliveries</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <style>
            .setteled-class{
                background-color:#bddac5
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main Loading -->
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
                                <h2>Admin Earning Report (<?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Deliveries)</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <h3>Search by Date...</h3>
                            <span>
                                <a style="cursor:pointer" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                <a style="cursor:pointer" onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                <a style="cursor:pointer" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                <a style="cursor:pointer" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                <a style="cursor:pointer" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                <a style="cursor:pointer" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                            </span> 
                            <span>
                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />
                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text" name = 'searchCompany' data-text="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>" id="searchCompany">
                                        <option value="">Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <input type="text" id="serachOrderNo" name="serachOrderNo" placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> Number" class="form-control search-trip001" value="<?= $serachOrderNo; ?>"/>
                                </div>
                            </span>
                        </div>

                        <div class="row payment-report payment-report1 payment-report2">
                            <div class="col-lg-3">
                                <select class="form-control" name='searchPaymentType' data-text="Select <?= $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select Payment Type</option>
                                    <option value="Cash" <?php if ($searchPaymentType == "Cash") { ?>selected <?php } ?>>Cash</option>
                                    <option value="Card" <?php if ($searchPaymentType == "Card") { ?>selected <?php } ?>><?= $cardText; ?></option>
                                </select>
                            </div>
<?php if (scount($allservice_cat_data) > 1) { ?>
                                <div class="col-lg-2 select001" style="padding-right:15px;">
                                    <select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type" id="searchServiceType">
                                        <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                        <?php foreach ($allservice_cat_data as $value) { ?>
                                            <option value="<?= $value['iServiceId']; ?>" <?php if ($searchServiceType == $value['iServiceId']) {
                                        echo "selected";
                                    } ?>><?= clearName($value['vServiceName']); ?></option>
                                        <?php } ?>    
                                        <?php if($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                                            <option value="Genie" <?php
                                            if ($searchServiceType == "Genie") {
                                                echo "selected";
                                            }
                                            ?>><?= clearName($langage_lbl_admin['LBL_OTHER_DELIVERY']); ?></option>
                                            <option value="Runner" <?php
                                            if ($searchServiceType == "Runner") {
                                                echo "selected";
                                            }
                                            ?>><?= clearName($langage_lbl_admin['LBL_RUNNER']); ?></option>
                                        <?php } ?>      
                                    </select>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'admin_payment_report.php'"/>
                            <?php if (scount($db_trip) > 0 && SITE_TYPE != 'Demo' && $userObj->hasPermission('export-admin-earning')) { ?>
                                <button type="button" onClick="exportlist()" class="export-btn001" >Export</button>
                            <?php } ?>
                            </b>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_restaurant">
                                        <input type="hidden"  name="iOrderId" id="iOrderId" value="">
                                        <input type="hidden"  name="ePayRestaurant" id="ePayRestaurant" value="">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                <?php if (scount($allservice_cat_data) > 1) { ?>
                                                        <th style="text-align:center;">Service Type</th>
                                                <?php } ?>
                                                    <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_NO_ADMIN_DL']; ?># </th>
                                                    <th width="9%" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
                                                        echo $order;
                                                    } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Date <?php if ($sortby == 4) {
                                                        if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th style="text-align:center;">A=Total Order Amount</th>
                                                    <th style="text-align:center;">B=Site Commision</th>
                                                    <th width="4%" style="text-align:center;">C=Delivery Charges</th>
                                                    <!-- <th style="text-align:right;">D=Promocode</th> -->
                                                    <th style="text-align:center;">D=OutStanding Amount</th>
                                                    <th style="text-align:center;">E=Delivery Tip</th>
                                                     <th style="text-align:center;">F=Tax</th>
                                                    <th style="text-align:center;">G=<?= $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"]?> Pay Amount</th>
                                                    <!-- <th style="text-align:right;">G = B+C+D+E-F<br/>Admin Earning Amount</th> -->
                                                    <th style="text-align:center;">H=Admin Earning Amount</th>
                                                    <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Status</th>
                                                    <th style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') {
                                                    echo $order;
                                                } else { ?>0<?php } ?>)">Payment method<?php if ($sortby == 5) {
                                                    if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $set_unsetarray = array();
                                                if (scount($db_trip) > 0) {

                                                    $systemTimeZone = date_default_timezone_get();
                                                    for ($i = 0; $i < scount($db_trip); $i++) {
                                                        $class_setteled = "";
                                                        if ($db_trip[$i]['eRestaurantPaymentStatus'] == 'Settelled') {
                                                            $class_setteled = "setteled-class";
                                                        }
                                                        $totalfare = $db_trip[$i]['fTotalGenerateFare'];
                                                        $site_commission = $db_trip[$i]['fCommision'];
                                                        $fOffersDiscount = $db_trip[$i]['fOffersDiscount'];
                                                        $fDeliveryCharge = $db_trip[$i]['fDeliveryCharge'];
                                                        $fOutStandingAmount = $db_trip[$i]['fOutStandingAmount'];
                                                        $fTipAmount = $db_trip[$i]['fTipAmount'];
                                                         $fTax = $db_trip[$i]['fTax'];

                                                        $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);

                                                        $set_unsetarray[] = $db_trip[$i]['eRestaurantPaymentStatus'];

                                                        if (!empty($db_trip[$i]['drivername'])) {
                                                            $drivername = $db_trip[$i]['drivername'];
                                                        } else {
                                                            $drivername = '--';
                                                        }

                                                        $siteearnig = cleanNumber($site_commission) + cleanNumber($fDeliveryCharge) + cleanNumber($fOutStandingAmount) + cleanNumber($fTipAmount)- cleanNumber($fTax);
                                                        $driverearning = $db_trip[$i]['driverearning'] + cleanNumber($fTipAmount) + $db_trip[$i]['item_subtotal'];
                                                        $adminearning = $siteearnig - cleanNumber($driverearning);

                                                        if (scount($allservice_cat_data) > 1) {
                                                            $vServiceName = $db_trip[$i]['vServiceName'];
                                                        }

                                                        if($db_trip[$i]['eBuyAnyService'] == "Yes")
                                                        {
                                                            $vServiceName = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
                                                            if($db_trip[$i]['eForPickDropGenie'] == "Yes")
                                                            {
                                                                $vServiceName = $langage_lbl_admin['LBL_RUNNER'];
                                                            }
                                                            $adminearning = cleanNumber($site_commission) + cleanNumber($fOutStandingAmount);
                                                        }
                                                        
                                                        $date_format_data_array = array(
                                                            'tdate' => (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['tOrderRequestDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone) : $db_trip[$i]['tOrderRequestDate'],
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $get_tOrderRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                        ?>
                                                        <tr class="gradeA <?= $class_setteled ?>">
                                                                <?php if (scount($allservice_cat_data) > 1) { ?>
                                                                <td align="center"><?= $vServiceName ?></td>
                                                                <?php } ?>
                                                                <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                <td align="center"><a href="order_invoice.php?iOrderId=<?= $db_trip[$i]['iOrderId'] ?>" target="_blank"><?php echo $db_trip[$i]['vOrderNo']; ?></a></td>
                                                                <?php } else { ?>
                                                                <td align="center"><?php echo $db_trip[$i]['vOrderNo']; ?></td>
                                                                <?php } ?>
                                                            <td align="center"><?= $get_tOrderRequestDate_format['tDisplayDate']."<br/>".$get_tOrderRequestDate_format['tDisplayTime'].$time_zone_difference_text;
                                                            //DateTime($db_trip[$i]['tOrderRequestDate']); ?></td>
                                                            <td align="center">
                                                                <?php
                                                                if ($db_trip[$i]['fTotalGenerateFare'] != "" && $db_trip[$i]['fTotalGenerateFare'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fTotalGenerateFare'], '');
                                                                    
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td align="center"><?php if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) {
                                                                echo formateNumAsPerCurrency($db_trip[$i]['fCommision'], '');
                                                            
                                                                } else {
                                                                    echo '-';
                                                            } ?></td>
                                                            <td align="center"><?php if ($db_trip[$i]['fDeliveryCharge'] != "" && $db_trip[$i]['fDeliveryCharge'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fDeliveryCharge'], '');
                                                                } else {
                                                                    echo '-';
                                                                } ?></td>
                                                                <!--  <td align="right"><?php if ($db_trip[$i]['fOffersDiscount'] != "" && $db_trip[$i]['fOffersDiscount'] != 0) {
                                                                echo formateNumAsPerCurrency($db_trip[$i]['fOffersDiscount'], '');
                                                                
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>  -->
                                                            <td align="center"><?php
                                                                if ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], '');
                                                                    
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>                                            
                                                              <!-- <td align="right">
                                                                <?php
                                                                if ($restaurant_payment != "" && $restaurant_payment != 0) {
                                                                    echo formateNumAsPerCurrency($restaurant_payment, '');
                                                                    
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                              </td> -->
                                                              <td align="center"><?php
                                                                if ($db_trip[$i]['fTipAmount'] != "" && $db_trip[$i]['fTipAmount'] != 0) {
                                                                    echo formateNumAsPerCurrency($db_trip[$i]['fTipAmount'], '');
                                                                    
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>  
                                                                 <td align="center"><?php if ($db_trip[$i]['fTax'] != "" && $db_trip[$i]['fTax'] != 0) {
                                                                echo formateNumAsPerCurrency($db_trip[$i]['fTax'], '');
                                                                
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?></td>
                                                            <td align="center">
                                                            <?php
                                                            if ($driverearning != "" && $driverearning != 0) {
                                                                echo formateNumAsPerCurrency($driverearning, '');
                                                                
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                            </td>
                                                            <td align="center">
                                                            <?php
                                                            if ($adminearning != "" && $adminearning != 0) {
                                                                if($adminearning < 0)
                                                                {
                                                                    echo "-".str_replace("-", "", formateNumAsPerCurrency($adminearning, ''));
                                                                }
                                                                else{
                                                                    echo formateNumAsPerCurrency($adminearning, ''); 
                                                                }
                                                                
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                            </td>
                                                            <td align="center"><?= $db_trip[$i]['vStatus']; ?></td>
                                                            <td align="center">
                                                                <?php
                                                                    $ePaymentOption = $db_trip[$i]['ePaymentOption'];
                                                                    if ($db_trip[$i]['ePaymentOption'] == 'Card') {
                                                                        $ePaymentOption = $cardText;
                                                                    }
                                                                ?>
                                                                <?= $ePaymentOption; ?>
                                                            </td>
                                                            <!--  <td><?= $db_trip[$i]['eRestaurantPaymentStatus']; ?></td>
                                                            <td>
                                                                <?php if ($db_trip[$i]['eRestaurantPaymentStatus'] == 'Unsettelled') { ?>
                                                                    <input class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iOrderId'] ?>" id="iOrderId_<?= $db_trip[$i]['iOrderId'] ?>" name="iOrderId[]">
                                                                <?php } ?>
                                                            </td> -->
                                                        </tr>
                                                    <?php }  
                                                    } else { ?>
                                                    <tr class="gradeA">
                                                        <td colspan="11"  align="center"><?= $langage_lbl_admin['LBL_NO_RECORDS_FOUND1']; ?></td>
                                                    </tr>
                                                    <?php } ?>
                                                    <?php /*
                                                    <tr class="gradeA">
                                                        <td colspan="11" align="right">Total Fare</td>
                                                        <td align="right" colspan="3"><?= formateNumAsPerCurrency($tot_order_amount, '');  ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="11" align="right">Total Site Commision</td>
                                                        <td  align="right" colspan="3"><?= formateNumAsPerCurrency($tot_site_commission, '');  ?></td>
                                                    </tr>
                                                     <tr class="gradeA">
                                                        <td colspan="11" align="right">Total Tax</td>
                                                        <td  align="right" colspan="3"><?= formateNumAsPerCurrency($tot_admin_tax, '');  ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="11" align="right">Total Delivery Charges</td>
                                                        <td  align="right" colspan="3"><?= formateNumAsPerCurrency($tot_delivery_charges, '');  ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="11" align="right">Total Outstanding Amount</td>
                                                        <td  align="right" colspan="3"><?= formateNumAsPerCurrency($tot_outstanding_amount, ''); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="11" align="right">Total Admin Earning  Payment</td>
                                                        <td  align="right" colspan="3"><?= formateNumAsPerCurrency($tot_admin_payment, '');?></td>
                                                    </tr>

                                                        <?php //if (in_array("Unsettelled", $set_unsetarray)) {   ?>
                                                            <!--  <tr class="gradeA">
                                                                 <td colspan="10" align="right"><div class="row payment-report-button">
                                                                 <span style="margin-right: 15px;">
                                                                 <a onClk="PaytoRestaurant()" href="javascript:void(0);"><button class="btn btn-primary" type="button">Mark As Settelled</button></a>
                                                                 </span>
                                                                 </div>
                                                                 </td>
                                                             </tr> -->
                                                        <?php //} ?>
                                                    <?php */ ?>                                                    
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php include('pagination_n.php'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="row">
                        <div class="col-lg-6 col-lg-offset-6">
                            <div class="admin-notes">
                                <h4>Summary:</h4>
                                <ul>
                                    <li><strong>Total Fare: </strong><?= formateNumAsPerCurrency($tot_order_amount, '');  ?></li>
                                    <li><strong>Total Site Commission: </strong><?= formateNumAsPerCurrency($tot_site_commission, ''); ?></li>
                                    <li><strong>Total Tax: </strong><?= formateNumAsPerCurrency($tot_admin_tax, ''); ?></li>
                                    <li><strong>Total Delivery Charges: </strong><?= formateNumAsPerCurrency($tot_delivery_charges, ''); ?></li>
                                    <li><strong>Total Outstanding Amount: </strong><?= formateNumAsPerCurrency($tot_outstanding_amount, ''); ?></li>
                                    <li><strong>Total Admin Earning: </strong><?= formateNumAsPerCurrency($tot_admin_payment, ''); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li><strong>Admin Earning Amount: </strong><br>H = B + C + D + E - F - G</li>
                            <?php if($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                            <li>
                                <strong>Admin Earning Amount for <?= $langage_lbl_admin['LBL_OTHER_DELIVERY'].'/'.$langage_lbl_admin['LBL_RUNNER'] ?> Feature: </strong><br>H = B + D
                            </li>
                            <?php } ?>
                        </ul>
                  </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/admin_payment_report.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?= $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?= $order; ?>" >
            <input type="hidden" name="action" value="<?= $action; ?>" >
            <input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>" >
            <input type="hidden" name="serachOrderNo" value="<?= $serachOrderNo; ?>" >
            <input type="hidden" name="searchPaymentType" value="<?= $searchPaymentType; ?>" >
            <input type="hidden" name="searchServiceType" value="<?= $searchServiceType; ?>" >
            <input type="hidden" name="searchRestaurantPayment" value="<?= $searchRestaurantPayment; ?>" >
            <input type="hidden" name="startDate" value="<?= $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?= $endDate; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

<?php include_once('footer.php'); ?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <script>
                $('#dp4').datepicker()
                        .on('changeDate', function (ev) {
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
                $('#dp5').datepicker()
                        .on('changeDate', function (ev) {
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

                        function setRideStatus(actionStatus) {
                            window.location.href = "trip.php?type=" + actionStatus;
                        }
                        function todayDate() {
                            $("#dp4").val('<?= $Today; ?>');
                            $("#dp5").val('<?= $Today; ?>');
                        }
                        function reset() {
                            location.reload();
                        }
                        function yesterdayDate()
                        {
                            $("#dp4").val('<?= $Yesterday; ?>');
                            $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                            $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                            $("#dp4").change();
                            $("#dp5").change();
                            $("#dp5").val('<?= $Yesterday; ?>');
                        }
                        function currentweekDate(dt, df)
                        {
                            $("#dp4").val('<?= $monday; ?>');
                            $("#dp4").datepicker('update', '<?= $monday; ?>');
                            $("#dp5").datepicker('update', '<?= $sunday; ?>');
                            $("#dp5").val('<?= $sunday; ?>');
                        }
                        function previousweekDate(dt, df)
                        {
                            $("#dp4").val('<?= $Pmonday; ?>');
                            $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                            $("#dp5").datepicker('update', '<?= $Psunday; ?>');
                            $("#dp5").val('<?= $Psunday; ?>');
                        }
                        function currentmonthDate(dt, df)
                        {
                            $("#dp4").val('<?= $currmonthFDate; ?>');
                            $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                            $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                            $("#dp5").val('<?= $currmonthTDate; ?>');
                        }
                        function previousmonthDate(dt, df)
                        {
                            $("#dp4").val('<?= $prevmonthFDate; ?>');
                            $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                            $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                            $("#dp5").val('<?= $prevmonthTDate; ?>');
                        }
                        function currentyearDate(dt, df)
                        {
                            $("#dp4").val('<?= $curryearFDate; ?>');
                            $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                            $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                            $("#dp5").val('<?= $curryearTDate; ?>');
                        }
                        function previousyearDate(dt, df)
                        {
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
                                window.location.href = action + "?" + formValus;
                            }
                        });
                        $(function () {
                            $("select.filter-by-text#searchServiceType").each(function () {
                                $(this).select2({
                                    placeholder: $(this).attr('data-text'),
                                    allowClear: true
                                }); //theme: 'classic'
                            });
                        });
                        
                        function exportlist(){
                            $("#actionpay").val("export");
                            $("#pageForm").attr("action","export_admin_payment_report.php");
                            ShpSq6fAm7($("#pageForm"));
                            document.pageForm.submit();
                        }

    $('body').on('keyup', '.select2-search__field', function() {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ( $( ".select2-results__options" ).is( ".select2-results__message" ) ) {
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });
    function formatDesign(item) {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        //console.log(item);
        var selectionText = item.text.split("--");
        if(selectionText[2] != null && selectionText[1] != null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2]+'</span>');
        } else if(selectionText[2] == null && selectionText[1] != null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if(selectionText[2] != null && selectionText[1] == null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }
        //$(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        return $returnString;
    };

    function formatDesignnew(item){
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#searchCompany").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                   // quietMillis:100,
                    data: function (params) {
                      // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype:'Store',
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;
                    
                        if(data.length < 10){
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        
                        return {
                            results: $.map(data, function (item) {

                                if(item.Phoneno != '' && item.vEmail != ''){
                                    var textdata = item.fullName  + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if(item.Phoneno == '' && item.vEmail != ''){
                                    var textdata = item.fullName  + "--" + "Email: " + item.vEmail;
                                } else if(item.Phoneno != '' && item.vEmail == ''){
                                    var textdata = item.fullName  + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                              more: more
                            }
                        };

                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });
    var sIdCompany = '<?= $searchCompany;?>';
    var sSelectCompany = $('select.filter-by-text#searchCompany');
    var itemname;
    var itemid;
    if(sIdCompany != ''){        

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompany + '&usertype=Store',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function(response) {
            if(response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if(item.Phoneno != '' && item.vEmail != ''){
                        var textdata = item.fullName  + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if(item.Phoneno == '' && item.vEmail != ''){
                        var textdata = item.fullName  + "--" + "Email: " + item.vEmail;
                    } else if(item.Phoneno != '' && item.vEmail == ''){
                        var textdata = item.fullName  + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectCompany.append(option);;    
            }
            else {
                console.log(response.result);
            }
        });
    }

        </script>
    </body>
    <!-- END BODY-->
</html>