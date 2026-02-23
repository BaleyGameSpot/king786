<?php
include_once('common.php');

$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$userSession = "MANUAL_ORDER_" . strtoupper($fromOrder);
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderUserSession = "MANUAL_ORDER_USER_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
$orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_" . strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
$orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
$orderUserNameSession = "MANUAL_ORDER_USER_NAME_" . strtoupper($fromOrder);
$orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_" . strtoupper($fromOrder);
$orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_" . strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
$responce = $responced['OrderDetails'] = $OrderDetailss = array();
if (isset($_SESSION[$orderDetailsSession])) {
    $OrderDetailss = $_SESSION[$orderDetailsSession];
}

for ($ig = 0; $ig < scount($OrderDetailss); $ig++) {
    if ($OrderDetailss[$ig]['typeitem'] != 'remove') {
        $addoptions = array();
        $addoptions['iMenuItemId'] = $OrderDetailss[$ig]['iMenuItemId'];
        $addoptions['iFoodMenuId'] = $OrderDetailss[$ig]['iFoodMenuId'];
        $addoptions['vOptionId'] = $OrderDetailss[$ig]['vOptionId'];
        $addoptions['iQty'] = $OrderDetailss[$ig]['iQty'];
        $addoptions['vAddonId'] = $OrderDetailss[$ig]['vAddonId'];
        $addoptions['tInst'] = $OrderDetailss[$ig]['tInst'];
        $addoptions['typeitem'] = $OrderDetailss[$ig]['typeitem'];
        array_push($responced['OrderDetails'], $addoptions);
    }
}
$_SESSION[$orderDetailsSession] = $responced['OrderDetails'];
$_REQUEST["OrderDetails"] = json_encode($_SESSION[$orderDetailsSession]);
$OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
$CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
$paymentMethod = isset($_REQUEST["paymentMethod"]) ? $_REQUEST["paymentMethod"] : 'No';
$iAdminUserId_placedorder = $_SESSION[$orderUserSession];
$iServiceId = $_SESSION[$orderServiceSession];
$iUserId = $_SESSION[$orderUserIdSession];
$iUserAddressId = $_SESSION[$orderAddressIdSession];
$iCompanyId = isset($_SESSION[$orderStoreIdSession]) ? $_SESSION[$orderStoreIdSession] : '';
$fDeliverytime = 0;
$ePaymentOption = isset($_REQUEST["payment"]) ? $_REQUEST["payment"] : '';
$iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';

//Added By HJ On 25-02-2020 For Get Language Label Data As Per User Language Start
$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
$Ratio = $UserDetailsArr['Ratio'];
$currencySymbol = $UserDetailsArr['currencySymbol'];
$vLang = $UserDetailsArr['vLang'];
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
if ($vLang == "" || $vLang == NULL) {
    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}
$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);
//Added By HJ On 25-02-2020 For Get Language Label Data As Per User Language End
$pay_data = [];
$orderdata = $obj->MySQLSelect("select fNetTotal,vCompany,iUserId,tUserWalletBalance,vOrderNo from orders where iOrderId = $iOrderId");
$fNetTotal = $orderdata[0]['fNetTotal'];
$vCompany = $orderdata[0]['vCompany'];
$iUserId = $orderdata[0]['iUserId'];
$tUserWalletBalance = $orderdata[0]['tUserWalletBalance'];
/* Check debit wallet For Count Total Fare  Start */
$user_wallet_debit_amount = 0;
if ($CheckUserWallet == "Yes") {
    $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($iUserId, "Rider");
    if ($user_available_balance > 0) {
        $totalCurrentActiveTripsArr = FetchTotalOngoingTrips($iUserId);
        $totalCurrentActiveTripsIdsArr = $totalCurrentActiveTripsArr['ActiveTripIds'];
        $totalCurrentActiveOrderIdsArr = $totalCurrentActiveTripsArr['ActiveOrderIds'];
        $totalCurrentActiveTripsCount = $totalCurrentActiveTripsArr['TotalCount'];
        /*         * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
        // Charge an amount that is autorized when trip was initially requested in case when multiple jobs are going on.
        if (($totalCurrentActiveTripsCount > 1 || in_array($iOrderId, $totalCurrentActiveOrderIdsArr) == false) && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
            $user_available_balance = $tUserWalletBalance;
        }
        /*         * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
    }
    if ($fNetTotal > $user_available_balance) {
        $fNetTotal = $fNetTotal - $user_available_balance;
        $user_wallet_debit_amount = $user_available_balance;
    } else {
        $user_wallet_debit_amount = $fNetTotal;
        $fNetTotal = 0;
    }
}
$generalConfigPaymentArr = $CONFIG_OBJ->getGeneralVarAll_Payment_Array();
if ($ePaymentOption == 'Card' && $fNetTotal > 0) {
    //$pay_data['tPaymentUserID'] = 'ch_1EI7fjHMmw2anrY62hw' . rand(10,1000);
    $pay_data['tPaymentUserID'] = 'REF_' . time();
    $pay_data['vPaymentUserStatus'] = "approved";
    //$pay_data['tPaymentDetails'] = '{"STRIPE_SECRET_KEY":"sk_test_S9nJKYA1qzl6LzKuFoSNhzc1","STRIPE_PUBLISH_KEY":"pk_test_w4Y4ZVaDVyfDDcyLvQacfNAz"}';
    $secretkey = $generalConfigPaymentArr['STRIPE_SECRET_KEY'];
    $publishkey = $generalConfigPaymentArr['STRIPE_PUBLISH_KEY'];
    $pay_data['tPaymentDetails'] = '{"STRIPE_SECRET_KEY":' . $secretkey . ',"STRIPE_PUBLISH_KEY":' . $publishkey . '}';
    $pay_data['iOrderId'] = $iOrderId;
    $pay_data['vPaymentMethod'] = $paymentMethod == 'flutterwave' ? 'flutterwave' : $APP_PAYMENT_METHOD;
    $pay_data['iUserId'] = $iUserId;
    $pay_data['eUserType'] = "Passenger";
    $pay_data['eEvent'] = "OrderPayment";
    $pay_data['iTripId'] = 0;
    //$pay_data['iAmountUser'] = $Order_data['fNetTotal'];
    $pay_data['iAmountUser'] = $fNetTotal;
    // $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
}

if ($fNetTotal == 0) {
    $returnArr['Action'] = "3";
    $returnArr['message'] = '';
    $returnArr['iOrderId'] = $iOrderId;
    echo json_encode($returnArr);
    exit;
}
$_SESSION['pay_data'] = $pay_data;
//$currency = $_SESSION['sess_vCurrency'];
$sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
$currencyData = $obj->MySQLSelect($sqlp);
$currency = $currencyData[0]['vName'];

$vSymbol = $currencyData[0]['vSymbol'];

    $amount = $fNetTotal;
   
    $sql = "SELECT tSessionId FROM register_user WHERE iUserId = ".$iUserId;
    $sqlData = $obj->MySQLSelect($sql);
    $tSessionId = $sqlData[0]['tSessionId'];
    $GeneralUserType = "Passenger";
    $GeneralMemberId = $iUserId;
    $cancelUrl = $_REQUEST['cancelUrl'];
    $PAGE_TYPE = "CHARGE_CARD";
    $orderNo = $orderdata[0]['vOrderNo'];
    $description = "Amount charged for ".$orderdata[0]['vOrderNo'];
    // echo $cancelUrl; exit;
    $extra_params = "tSessionId=".$tSessionId."&GeneralUserType=".$GeneralUserType."&GeneralMemberId=".$GeneralMemberId."&iUserId=".$iUserId."&PAGE_TYPE=".$PAGE_TYPE."&cancelUrl=".$cancelUrl."&AMOUNT=".$amount."&currency=".$currency."&order=".$fromOrder."&iOrderId=".$iOrderId."&description=".$description."&orderNo=".$orderNo."&vOrderNo=".$orderNo."&ePaymentOption=Card&vPayMethod=Instant&iServiceId=".$iServiceId."&SYSTEM_TYPE=WEB";

    $redirect_url = $tconfig["tsite_url"].'assets/libraries/webview/system_payment.php?'.$extra_params;
    //    if ($paymentMethod == 'stripe' && $fNetTotal > 0) { // Handle Stripe Payment 
    if($fNetTotal>0){ // Handle Stripe Payment 

        $returnArr['Action'] = "1";
        $returnArr['message'] = $redirect_url;
    }
    
    else{
        $returnArr['Action'] = "0";
        $returnArr['message'] = $languageLabelsArr["LBL_TRY_AGAIN_LATER_TXT"];
    }

echo json_encode($returnArr);
?>