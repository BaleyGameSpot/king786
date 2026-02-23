<?php
include_once('common.php');

if (isset($_REQUEST['order']))
{
    $ordrId = base64_decode(base64_decode(trim($_REQUEST['order'])));
    $orderData = $obj->MySQLSelect("SELECT iUserId FROM orders WHERE iOrderId = '$ordrId'");

    $userData = $obj->MySQLSelect("SELECT iUserId,vEmail,vName,vLastName,vCurrencyPassenger,vPhone FROM register_user WHERE iUserId = '" . $orderData[0]['iUserId'] . "'");

    $_SESSION['sess_iUserId'] = $userData[0]['iUserId'];
    $_SESSION['sess_vName'] = $userData[0]['vName'].' '.$userData[0]['vLastName'];
    $_SESSION['sess_vEmail'] = $userData[0]['vEmail'];
    $_SESSION['sess_vPhone'] = $userData[0]['vPhone'];
    $_SESSION['sess_user'] = "rider";
    $_SESSION['sess_vCurrency'] = $userData[0]['vCurrencyPassenger'];
}

$script = "Order";
//$tbl_name     = 'register_driver';
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = $startDate = $endDate = "";
$dateRange = isset($_POST['dateRange']) ? $_POST['dateRange'] : "";
if (isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") {
    $startDate = $_REQUEST['startDate'];
}
if (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "") {
    $endDate = $_REQUEST['endDate'];
}
if ($action != '') {
    if ($startDate != '') {
        $ssql .= " AND Date(ord.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(ord.tOrderRequestDate) <='" . $endDate . "'";
    }
}

if(isset($_SESSION['sessionOrderId']) && $_SESSION['sessionOrderId'] != "" && $_SESSION['sess_user'] == "rider")
{
    $userData = $obj->MySQLSelect("SELECT iUserId,vEmail,vStripeCusId,vStripePaymentMethod FROM register_user WHERE iUserId = ".$_SESSION['sess_iUserId']);

    $user_id = $userData[0]['iUserId'];
    $vEmail = $userData[0]['vEmail'];
    $vStripeCusId = $userData[0]['vStripeCusId'];

    if($vStripeCusId == "")
    {
        include 'assets/libraries/webview/stripe/init.php';

        $stripeSessionId = $_SESSION['sessionOrderId'];
        \Stripe\Stripe::setApiKey($STRIPE_SECRET_KEY);

        $session = \Stripe\Checkout\Session::retrieve($stripeSessionId);
        

        $intent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

        $payment_method = $intent->payment_method;

        $payment_method_details = $intent->charges->data[0]->payment_method_details;
        $last4 = $payment_method_details->card->last4;


        $customer = \Stripe\Customer::retrieve($intent->customer);
        $customer = \Stripe\Customer::update($intent->customer, ['email' => $vEmail]);

        $user_update['vStripeCusId'] = $intent->customer;
        $user_update['vCreditCard'] = $last4;
        $user_update['vStripePaymentMethod'] = $payment_method;

        $where_user = " iUserId = '$user_id'";
        $obj->MySQLQueryPerform('register_user', $user_update, 'update', $where_user);

        unset($_SESSION['sessionOrderId']);
    }
}
/* ADD BY PJ for handle the stripe payment  */

if(!empty($iCompanyId)){
    $sql = 'SELECT vLang FROM company WHERE iCompanyId = "' . $iCompanyId . '"';
    $vLangCode = $obj->MySQLSelect($sql)[0]['vLang'];
}        

if (empty($vLangCode)) {
    $sql = 'SELECT vCode FROM language_master WHERE eDefault = "Yes"';
    $vLangCode = $obj->MySQLSelect($sql)[0]['vCode'];
}
        
$cancelled = 0;
if (isset($_REQUEST['cancelled']) && $_REQUEST['cancelled'] == "true") {
    $cancelled = 1;
}
if (isset($_REQUEST['order']) && $_REQUEST['success'] == 1 && $cancelled == 0) {
    
    $ordrId = base64_decode(base64_decode(trim($_REQUEST['order'])));

    $queryOder = 'SELECT vOrderNo,iOrderId,eCheckUserWallet,iUserId,fNetTotal,iCompanyId,iServiceId FROM orders WHERE iOrderId = "' . $ordrId . '" AND iStatusCode != "1"';
    $unPlacedOrder = $obj->MySQLSelect($queryOder);
    if (scount($unPlacedOrder) > 0) {
        $where = " iOrderId = '$ordrId'";
        $CheckUserWallet = $unPlacedOrder[0]['eCheckUserWallet'];
        $iUserId = $unPlacedOrder[0]['iUserId'];
        $fNetTotal = $unPlacedOrder[0]['fNetTotal'];
        $vOrderNo = $unPlacedOrder[0]['vOrderNo'];
        $iOrderId = $unPlacedOrder[0]['iOrderId'];
        $user_wallet_debit_amount = 0;
        if ($CheckUserWallet == "Yes") {
            $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($iUserId, "Rider");
            if ($fNetTotal > $user_available_balance) {
                $fNetTotal = $fNetTotal - $user_available_balance;
                $user_wallet_debit_amount = $user_available_balance;
            } else {
                $user_wallet_debit_amount = $fNetTotal;
                $fNetTotal = 0;
            }
            //echo $fNetTotal."=====".$user_wallet_debit_amount."=====".$user_available_balance;
            $data['fNetTotal'] = $fNetTotal;

            //Added By HJ On 13-02-2020 For Deduct Wallet Amount From User Wallet When Payment Flow 1 (Remain For Payment Flow 2 and 3) (SP Will Check then Put Conditiom For 2 and 3 In main) Dicuss With SP Start
            if ($user_wallet_debit_amount > 0 && $SYSTEM_PAYMENT_FLOW == "Method-1") {
                $dDate = date("Y-m-d H:i:s");
                $ePaymentStatus = "Unsettelled";
                $tDescription = "#LBL_DEBITED_BOOKING_DL#" . " " . $vOrderNo;
                $WALLET_OBJ->PerformWalletTransaction($iUserId, "Rider", $user_wallet_debit_amount, "Debit", 0, "Booking", $tDescription, $ePaymentStatus, $dDate, $iOrderId);
                // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
            }
            //Added By HJ On 13-02-2020 For Deduct Wallet Amount From User Wallet When Payment Flow 1 (Remain For Payment Flow 2 and 3) (SP Will Check then Put Conditiom For 2 and 3 In main) Dicuss With SP End
        }

        $data['ePaid'] = "Yes";
        $data['iStatusCode'] = 1;
        //$data['fNetTotal'] = 0;
        $data['fWalletDebit'] = $user_wallet_debit_amount;
        $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);

        $sql1 = "UPDATE order_status_logs SET iStatusCode = '1' WHERE iOrderId = '" . $ordrId . "'";
        $db_company = $obj->sql_query($sql1);

        /* Update Payment table as Successful Payment */
        $sql = 'SELECT iPaymentId FROM payments WHERE iOrderId = "' . $ordrId . '"';
        $paymentData = $obj->MySQLSelect($sql);
        if ($db_company == 1 && scount($paymentData) == 0) {
            $pay_data = $_SESSION['pay_data'];

            /* Change tPaymentDetails for Flutterwave Transaction */
            if (isset($_REQUEST['flwref']) && $_REQUEST['flwref'] != '') {
                $transactionArray = array('flwref' => $_REQUEST['flwref'], 'txref' => $_REQUEST['txref']);
                $pay_data['tPaymentDetails'] = json_encode($transactionArray);
            }

            $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
        }
        
        $db_companydata = $obj->MySQLSelect("select eAutoaccept,vCountry,eBuyAnyService from `company` where iCompanyId = '" . $unPlacedOrder[0]['iCompanyId'] . "'");
        if ((isset($db_companydata[0]['eAutoaccept']) && $db_companydata[0]['eAutoaccept'] == "Yes" && $MODULES_OBJ->isEnableAutoAcceptStoreOrder()) || $db_companydata[0]['eBuyAnyService'] == "Yes") { // If Store have enable and Admin Side Enable Setting
            //echo $iOrderId;die;
            if ($data_order[0]['iStatusCode'] != "2" && $db_companydata[0]['eBuyAnyService'] == "No") {
                $returnArr1 = ConfirmOrderByRestaurantcall($unPlacedOrder[0]['iCompanyId'], $ordrId); // For Auto Accept order From Store
            }
            
            if ($vCountry == "") {
                $vCountry = $db_companydata[0]['vCountry'];
            }
            sendAutoRequestToDriver($ordrId, $vCountry, $db_companydata[0]['eBuyAnyService']); // For Send Request to Drivers
        }
            
        // # Send Notification To Company ##
        $iCompanyId = $unPlacedOrder[0]['iCompanyId'];
        $iServiceId = $unPlacedOrder[0]['iServiceId'];
        $CompanyMessage = "OrderRequested";

        
        $orderreceivelbl = $langage_lbl['LBL_NEW_ORDER_PLACED_TXT'] . " " . $vOrderNo;
        $alertMsg = $orderreceivelbl;

        $tableName = "company";
        $iMemberId_VALUE = $iCompanyId;
        $iMemberId_KEY = "iCompanyId";
        
        $sql = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,eAppTerminate,eDebugMode,eHmsDevice FROM $tableName WHERE $iMemberId_KEY = $iMemberId_VALUE";
        $AppData = $obj->MySQLSelect($sql);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        $iGcmRegId = $AppData[0]['iGcmRegId'];
        $tSessionId = $AppData[0]['tSessionId'];
        $eAppTerminate = $AppData[0]['eAppTerminate'];
        $eDebugMode = $AppData[0]['eDebugMode'];
        $eHmsDevice = $AppData[0]['eHmsDevice'];
        
        $message_arr['tSessionId'] = $tSessionId;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['Message'] = $CompanyMessage;
        $message_arr['MsgCode'] = strval(time() . mt_rand(1000, 9999));
        $message_arr['iOrderId'] = $iOrderId;
        $message_arr['eSystem'] = "DeliverAll";
        $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

        $data_CompanyRequest = array();
        $data_CompanyRequest['iCompanyId'] = $iCompanyId;
        $data_CompanyRequest['iOrderId'] = $iOrderId;
        $data_CompanyRequest['tMessage'] = $message_pub;
        $data_CompanyRequest['vMsgCode'] = $message_arr['MsgCode'];
        $data_CompanyRequest['dAddedDate'] = @date("Y-m-d H:i:s");
        $requestId = addToCompanyRequest2($data_CompanyRequest);

        $channelName = "COMPANY_" . $iCompanyId;
        $generalDataArr[] = array(
            'eDeviceType'       => $eDeviceType,
            'deviceToken'       => $iGcmRegId,
            'alertMsg'          => $alertMsg,
            'eAppTerminate'     => $eAppTerminate,
            'eDebugMode'        => $eDebugMode,
            'eHmsDevice'        => $eHmsDevice,
            'message'           => $message_arr,
            'channelName'       => $channelName
        );

        $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_COMPANY);
        
        unset($_SESSION['sessionOrderId']);
        if($_SESSION['sess_user'] == "rider") {
            unset($_SESSION['ORDER_DETAILS_USER']);
        }

        header('Location: ' . $tconfig['tsite_url'] . 'invoice_deliverall.php?iOrderId=' . base64_encode(base64_encode($iOrderId)));
        exit;
    }
}

/* End handle the stripe payment  */
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
      $vLang = $default_lang;
}


$sql = "SELECT u.vCurrencyPassenger,ord.fTotalGenerateFare, ord.fRoundingAmount, ord.eRoundingType, ord.iOrderId, ord.vOrderNo,ord.vTimeZone,sc.vServiceName_" . $vLang . " as vServiceName, ord.tOrderRequestDate, ord.fNetTotal, ord.iStatusCode, ord.iUserId, Concat(u.vName,' ',u.vLastName) as Username,cmp.vCompany,ordst.vStatus_".$vLang." as vStatus,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = ord.iOrderId) as TotalItem,ord.eTakeaway,ord.eBuyAnyService,ord.eForPickDropGenie From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode LEFT JOIN register_user as u ON u.iUserId = ord.iUserId LEFT JOIN service_categories as sc on sc.iServiceId=ord.iServiceId WHERE ord.iStatusCode NOT IN ('11','12') AND ord.iUserId = '" . $_SESSION['sess_iUserId'] . "' " . $ssql . " AND IF(ord.eTakeaway = 'Yes' && ordst.iStatusCode = 6, ordst.eTakeaway='Yes', ordst.eTakeaway != 'Yes') AND IF(ord.eBuyAnyService = 'Yes' && ordst.iStatusCode IN (1,4,13,14), ordst.eBuyAnyService = 'Yes', ordst.eBuyAnyService = 'No') AND ord.iServiceId IN ($enablesevicescategory) ORDER BY ord.iOrderId DESC ";

$db_order_detail = $obj->MySQLSelect($sql);

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

//$invoice_icon = "driver-view-icon.png";
//$canceled_icon = "canceled-invoice.png";
if (file_exists($logogpath . "driver-view-icon.png")) {
    $invoice_icon = $logogpath . "driver-view-icon.png";
} else {
    $invoice_icon = "assets/img/driver-view-icon.png";
}


$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);

$serviceIdArray = array();
$serviceIdArray = array_column($allservice_cat_data, 'iServiceId');
$restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (scount($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $restaurant = $langage_lbl['LBL_RESTAURANT'];
    } else {
        $restaurant = $langage_lbl['LBL_STORE'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
        <!--<title><?= $SITE_NAME ?></title>-->
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_ORDERS_TXT']; ?></title>
        <meta name="keywords" value=""/>
        <meta name="description" value=""/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <style type="text/css">
            .grey-color {
                color: grey !important
            }
        </style>
    </head>
     

    <body id="wrapper">
        <!-- home page -->
        <!-- home page -->
        <?php if ($template != 'taxishark') { ?>
            <div id="main-uber-page">
            <?php } ?>
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- First Section -->
            <?php include_once("top/header.php"); ?>
            <!-- End: First Section -->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?= $langage_lbl['LBL_ORDERS_TXT']; ?></h1>
                        </div>

                        <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                            <input type="hidden" name="action" value="search" />
                            <div class="filters-column mobile-full">
                                <label><?= $langage_lbl['LBL_ORDER_SEARCH_BY_DATE']; ?></label>
                                <select id="timeSelect" name="dateRange">
                                    <option value="">Select</option>
                                    <option value="today" <?php
                                    if ($dateRange == 'today') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></option>
                                    <option value="yesterday" <?php
                                    if ($dateRange == 'yesterday') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></option>
                                    <option value="currentWeek" <?php
                                    if ($dateRange == 'currentWeek') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></option>
                                    <option value="previousWeek" <?php
                                    if ($dateRange == 'previousWeek') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></option>
                                    <option value="currentMonth" <?php
                                    if ($dateRange == 'currentMonth') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></option>
                                    <option value="previousMonth" <?php
                                    if ($dateRange == 'previousMonth') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_PREVIOUS'].' '.$langage_lbl['LBL_MONTH_TXT']; ?></option>
                                    <option value="currentYear" <?php
                                    if ($dateRange == 'currentYear') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></option>
                                    <option value="previousYear" <?php
                                    if ($dateRange == 'previousYear') {
                                        echo 'selected';
                                    }
                                    ?>><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></option>

                                </select>
                            </div>
                            <div class="filters-column mobile-half">
                                <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?></label>
                                <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                <i class="icon-cal" id="from-date"></i>
                            </div>
                            <div class="filters-column mobile-half">
                                <label><?= $langage_lbl['LBL_MYTRIP_TO_DATE']; ?></label>
                                <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_MYTRIP_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                                <i class="icon-cal" id="to-date"></i>
                            </div>
                            <div class="filters-column mobile-full">
                                <button class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_Search']; ?></button>
                                <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                                <a href="myorder" class="gen-btn"><?= $langage_lbl['LBL_RESET'] ?></a>
                            </div>
                        </form>

                    </div>
                </div>
            </section>
            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <div class="table-holder">
                        <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
                            <thead>
                                <tr>
                                    <? if (scount($allservice_cat_data) > 1) { ?>
                                        <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_TYPE'] ?></th>
                                    <? } ?>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_NO_TXT']; ?></th>   
                                    <th width="17%" style="text-align: center;"><?= $langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $restaurant ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_TOTAL_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
                                    <th style="text-align: center;"><?= $langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if(scount($db_order_detail)){
                                    $orderIdArr =$orderIdCurrencyArr= array();
                                    for ($i = 0; $i < scount($db_order_detail); $i++) {
                                        $orderIdArr[]= $db_order_detail[$i]['iOrderId'];
                                    }
                                    
                                    if(scount($orderIdArr) > 0){
                                        $implodeOrderIds = implode(",",$orderIdArr);
                                      
                                        $DataTrips = $obj->MySQLSelect("SELECT iOrderId,vCurrencyPassenger FROM trips WHERE iOrderId IN ($implodeOrderIds)");
                                        for($gh=0;$gh<scount($DataTrips);$gh++){
                                            $orderIdCurrencyArr[$DataTrips[$gh]['iOrderId']] = $DataTrips[$gh]['vCurrencyPassenger'];
                                        }
                                      
                                    }
                                }
                                for ($i = 0; $i < scount($db_order_detail); $i++) {
                                    $iOrderIdNew = $db_order_detail[$i]['iOrderId'];
                                    $getUserCurrencyLanguageDetails = getUserCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'], $iOrderIdNew);
                                    $currencySymbol = $getUserCurrencyLanguageDetails['currencySymbol'];
                                    $currencycode = $getUserCurrencyLanguageDetails['currencycode'];
                                    $Ratio = $getUserCurrencyLanguageDetails['Ratio'];

                                    $fNetTotalratio = $db_order_detail[$i]['fNetTotal'] * $Ratio; // Comment By HJ On 07-05-2020 As Per Discuss With KS
                                    //$fNetTotalratio = $db_order_detail[$i]['fTotalGenerateFare'] * $Ratio; // Added By HJ On 07-05-2020 As Per Discuss With KS
                                    $systemTimeZone = date_default_timezone_get();
                                    if ($db_order_detail[$i]['tOrderRequestDate'] != "" && $db_order_detail[$i]['vTimeZone'] != "") {
                                        $tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'], $db_order_detail[$i]['vTimeZone'], $systemTimeZone);
                                    } else {
                                        $tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
                                    }
                                    //$tripsql = "SELECT vCurrencyPassenger FROM trips WHERE iOrderId='" . $iOrderIdNew . "'";
                                    //$DataTrips = $obj->MySQLSelect($tripsql);
                                    
                                    if(isset($currencyAssociateArr[$db_order_detail[$i]['vCurrencyPassenger']])){
                                        $currData = array();
                                        $currData[] = $currencyAssociateArr[$db_order_detail[$i]['vCurrencyPassenger']];
                                        $currData[0]['vCurrencyPassenger'] = $db_order_detail[$i]['vCurrencyPassenger'];
                                        //echo "<pre>";print_r($currData);die;
                                    }else{
                                        $currData = $obj->MySQLSelect("SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger, cu.ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $_SESSION['sess_iUserId'] . "'");
                                    }
                                    $tripCurrency = "USD";
                                    if(isset($orderIdCurrencyArr[$iOrderIdNew])){
                                        $tripCurrency = $orderIdCurrencyArr[$iOrderIdNew];
                                    }
                                    $samecur = ($tripCurrency == $currData[0]['vCurrencyPassenger']) ? 1 : 0;

                                    $vServiceName = '';
                                    if (scount($allservice_cat_data) > 1) {
                                        $vServiceName = $db_order_detail[$i]['vServiceName'];
                                    }

                                    if ($db_order_detail[$i]['eBuyAnyService'] == "Yes") {
                                        $vServiceName = $langage_lbl['LBL_OTHER_DELIVERY'];
                                        if ($db_order_detail[$i]['eForPickDropGenie'] == "Yes") {
                                            $vServiceName = $langage_lbl['LBL_RUNNER'];
                                        }
                                        if(in_array($db_order_detail[$i]['iStatusCode'], [1,2]))
                                        {
                                            $db_order_detail[$i]['vStatus'] = $langage_lbl['LBL_ORDER_PLACED'];
                                        }
                                    }
                                    // New Date Format
                                    $date_format_data_array = array(
                                        'tdate' => $tOrderRequestDate,
                                        'langCode' => $vLangCode,
                                        'DateFormatForWeb' => 1
                                    );
                                    $get_request_date_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                    ?>
                                    <tr class="gradeA">
                                        <? if (scount($allservice_cat_data) > 1) { ?>
                                            <td><?= $vServiceName; ?></td>
                                        <? } ?>
                                        <td align="center" data-order="<?php echo $db_order_detail[$i]['iOrderId']; ?>">
                                            <?=$db_order_detail[$i]['vOrderNo'];?>
                                            <?= $db_order_detail[$i]['eTakeaway'] == 'Yes' ? '<br><span class="grey-color">'.$langage_lbl['LBL_TAKE_AWAY'].'</span>' : ''?>
                                        </td>
                                        <td><span style="display:none;"><?= strtotime($tOrderRequestDate) ?></span><?= $get_request_date_format['tDisplayDateTime'];//DateTime1($tOrderRequestDate, 'yes'); ?></td>
                                        <td align="center">
                                            <?php if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] != "company") { ?>
                                                <?= clearName($db_order_detail[$i]['vCompany']); ?>
                                            <?php } else { ?>
                                                <?= $db_order_detail[$i]['vCompany']; ?>
                                            <?php } ?>
                                        </td>
                                        <td align="center"><?= $db_order_detail[$i]['TotalItem']; ?></td>
                                        <td align="center">
                                        <? 
                                        if (isset($db_order_detail[$i]['fRoundingAmount']) && !empty($db_order_detail[$i]['fRoundingAmount']) && $db_order_detail[$i]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes" && $MODULES_OBJ->isEnableRoundingMethod()) {
                                        $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($db_order_detail[$i]['fNetTotal'] * $Ratio, $db_order_detail[$i]['fRoundingAmount'], $db_order_detail[$i]['eRoundingType']); ////start
                                       
                                         echo formateNumAsPerCurrency($roundingOffTotal_fare_amountArr['finalFareValue'],$currencycode);
                                        ?>
                                        <?} else {?>
                                        <?= formateNumAsPerCurrency($fNetTotalratio,$currencycode); ?>
                                          <? } ?>  
                                        </td>
                                        
                                        <td align="center"><?= str_replace("#STORE#", $db_order_detail[$i]['vCompany'], $db_order_detail[$i]['vStatus']); ?></td>
                                        <td align="center" width="10%">
                                            <a target = "_blank" href="invoice_deliverall.php?iOrderId=<?= base64_encode(base64_encode($db_order_detail[$i]['iOrderId'])) ?>">
                                                <img alt="" src="<?php echo $invoice_icon; ?>">
                                            </a>
                                        </td>       
                                    </tr>
                                <? } ?>     
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>

            <div style="clear:both;"></div>
            <?php if ($template != 'taxishark') { ?>
            </div>
        <?php } ?>
        <!-- footer part end -->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/js/jquery-ui.min.js"></script>
        <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


        <script type="text/javascript">
            if ($('#my-trips-data').length > 0) {
                $('#my-trips-data').DataTable({"oLanguage": langData,"order": [[2, "desc"]]});
            }

            $(document).on('change', '#timeSelect', function (e) {
                e.preventDefault();

                var timeSelect = $(this).val();

                if (timeSelect == 'today') {
                    todayDate('dp4', 'dp5')
                }
                if (timeSelect == 'yesterday') {
                    yesterdayDate('dFDate', 'dTDate')
                }
                if (timeSelect == 'currentWeek') {
                    currentweekDate('dFDate', 'dTDate')
                }
                if (timeSelect == 'previousWeek') {
                    previousweekDate('dFDate', 'dTDate')
                }
                if (timeSelect == 'currentMonth') {
                    currentmonthDate('dFDate', 'dTDate')
                }
                if (timeSelect == 'previousMonth') {
                    previousmonthDate('dFDate', 'dTDate')
                }
                if (timeSelect == 'currentYear') {
                    currentyearDate('dFDate', 'dTDate')
                }
                if (timeSelect == 'previousYear') {
                    previousyearDate('dFDate', 'dTDate')
                }

            }); 

            $(document).ready(function () {
                $("#dp4").datepicker({
                    dateFormat: "yy-mm-dd",
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-100:+10"
                });
                $("#dp5").datepicker({
                    dateFormat: "yy-mm-dd",
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-100:+10"
                });
                if ('<?= $startDate ?>' != '') {
                    $("#dp4").val('<?= $startDate ?>');
                    $("#dp4").datepicker('refresh');
                }
                if ('<?= $endDate ?>' != '') {
                    $("#dp5").val('<?= $endDate; ?>');
                    $("#dp5").datepicker('refresh');
                }

            });
            function reset() {
                location.reload();
            }
            function todayDate()
            {
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
            }
            function yesterdayDate()
            {
                $("#dp4").val('<?= $Yesterday; ?>');
                $("#dp5").val('<?= $Yesterday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentweekDate(dt, df)
            {
                $("#dp4").val('<?= $monday; ?>');
                $("#dp5").val('<?= $sunday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousweekDate(dt, df)
            {
                $("#dp4").val('<?= $Pmonday; ?>');
                $("#dp5").val('<?= $Psunday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentmonthDate(dt, df)
            {
                $("#dp4").val('<?= $currmonthFDate; ?>');
                $("#dp5").val('<?= $currmonthTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousmonthDate(dt, df)
            {
                $("#dp4").val('<?= $prevmonthFDate; ?>');
                $("#dp5").val('<?= $prevmonthTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentyearDate(dt, df)
            {
                $("#dp4").val('<?= $curryearFDate; ?>');
                $("#dp5").val('<?= $curryearTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousyearDate(dt, df)
            {
        
                $("#dp4").val('<?= $prevyearFDate; ?>');
                $("#dp5").val('<?= $prevyearTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function checkvalid() {
                if ($("#dp5").val() < $("#dp4").val()) {
                    //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
                    bootbox.dialog({
                        message: "<h4><?php echo addslashes($langage_lbl['LBL_FROM_TO_DATE_ERROR_MSG']); ?></h4>",
                        buttons: {
                            danger: {
                                label: "OK",
                                className: "btn-danger"
                            }
                        }
                    });
                    return false;
                }
            }
        </script>
        

        <!-- End: Footer Script -->
    </body>
</html>
