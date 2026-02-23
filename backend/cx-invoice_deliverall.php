<?php
    include_once('common.php');

    $sess_currency = $_SESSION['sess_currency'];
    $currencyData = checkCurrencyWiseFormatting($sess_currency);


    $tbl_name = 'orders';
    $script = "Order";
    $AUTH_OBJ->checkMemberAuthentication();
    $abc = 'rider,driver,company';
    $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    setRole($abc,$url);

    $vLang = "EN";
    if(isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != ""){
        $vLang = $_SESSION['sess_lang'];
    }
    $sessionUserType = "rider";
    if(isset($_SESSION['sess_user']) && $_SESSION['sess_user'] != ""){
        $sessionUserType = $_SESSION['sess_user'];
    }
    $sessionUserId = 0;
    if(isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] != ""){
        $sessionUserId = $_SESSION['sess_iUserId'];
    }
    //$encodeTripId = base64_encode(base64_encode(1203));

    $_REQUEST['iOrderId'] = base64_decode(base64_decode(trim($_REQUEST['iOrderId'])));
    $iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';
    if($iOrderId != ""){
         $checkOrderData = $obj->MySQLSelect("SELECT iUserId,iDriverId,iCompanyId,vDriverCompanyId FROM orders WHERE iOrderId = '" . $iOrderId . "'");

         if($sessionUserType == "rider" && $checkOrderData[0]['iUserId'] != $sessionUserId) {
            header('Location:myorder');
            exit();
         } else if($sessionUserType == "driver" && $checkOrderData[0]['iDriverId'] != $sessionUserId) {
            header('Location:driver-order');
            exit();
         }
         else if($sessionUserType == 'company' && $_SESSION['sess_eSystem'] == 'General') {
             if($checkOrderData[0]['vDriverCompanyId'] != 0){
                if($checkOrderData[0]['vDriverCompanyId'] != $sessionUserId){
                    header('Location:company-order');
                    exit();
                }
            } else {
                $checkICompanyId = $obj->MySQLSelect("SELECT iCompanyId FROM register_driver WHERE iDriverId = '" . $checkOrderData[0]['iDriverId'] . "'");
                $vDriverCompanyId = $checkICompanyId[0]['iCompanyId'];
                if($vDriverCompanyId != $sessionUserId){
                    header('Location:company-order');
                    exit();
                }
            }
        }
         else if ($sessionUserType == 'company' && $_SESSION['sess_eSystem'] == 'DeliverAll'){
            if($checkOrderData[0]['iCompanyId'] != $sessionUserId){
                header('Location:company-order');
                exit();
            }
        }
         else {
           /* header('Location:myorder');
            exit();*/
        }
    } else {

        header('Location:myorder');
        exit();
    }

    $ssql_order = " ord where 1=1";
    if($sessionUserType == "rider") {
       $ssql_order = " ord where iOrderId = '".$iOrderId."' AND iUserId = '".$sessionUserId."'";
    } else if($sessionUserType == "driver") {
        $ssql_order = " ord LEFT JOIN trips as t ON t.iOrderId=ord.iOrderId where ord.iOrderId = '".$iOrderId."' AND ord.iDriverId = '".$sessionUserId."'";
    } else if($_SESSION['sess_eSystem'] == 'General' && $_SESSION['sess_user'] == 'company'){
        $ssql_order = " ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId where ord.iOrderId = '".$iOrderId."' AND ord.vDriverCompanyId = '".$sessionUserId."'";
    } else {
        $ssql_order = " ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId where ord.iOrderId = '".$iOrderId."' AND ord.iCompanyId = '".$sessionUserId."'";
    }
    $user_order_sql = "select ord.iServiceId, ord.eTakeaway, ord.iCompanyId, ord.vImageDeliveryPref,ord.vDriverCompanyId from orders ".$ssql_order;
    $user_order_sql_data = $obj->MySQLSelect($user_order_sql);

    $iServiceId = 1;
    if(!empty($user_order_sql_data)){
        $iServiceId  = $user_order_sql_data[0]['iServiceId'];
        if ($sessionUserType == 'driver') {
            $db_order_data = FetchOrderFareDetailsForWeb($iOrderId, $sessionUserId, 'Driver');
        } else if ($sessionUserType == 'rider') {
            $db_order_data = FetchOrderFareDetailsForWeb($iOrderId, $sessionUserId, 'Passenger');
            $userData = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId = ".$db_order_data['iUserId']);
        } else if($_SESSION['sess_eSystem'] == 'General' && $_SESSION['sess_user'] == 'company'){
            $db_order_data = FetchOrderFareDetailsForWeb($iOrderId, '', 'Driver');
        } else {

            $db_order_data = FetchOrderFareDetailsForWeb($iOrderId, $sessionUserId, 'Company');
        }
    }

    $langage_lbl  = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);
    if ($sessionUserType == 'driver') {
        $UserType = 'Driver';
    } else if ($sessionUserType == 'rider') {
        $UserType = 'Passenger';
    } else {
        $UserType = 'Company';
    }

    $getratings = getrating($iOrderId);

    $iStatusCode = $db_order_data['iStatusCode'];

    $PUBSUB_TECHNIQUE = "SocketCluster"; //changed by sneha
    //$latlongSelect = $obj->MySQLSelect("SELECT vLatitude,vLongitude,tDestinationLatitude,tDestinationLongitude FROM `register_user` where iUserId = '" . $sessionUserId . "'");

    $vLatitude = $db_order_data['vRestuarantLocationLat'];
    $vLongitude = $db_order_data['vRestuarantLocationLong'];
    $vLatitudeSource = $db_order_data['vRestuarantLocationLat'];
    $vLongitudeSource = $db_order_data['vRestuarantLocationLong'];
    $tDestinationLatitude = $db_order_data['vLatitude'];
    $tDestinationLongitude = $db_order_data['vLongitude'];

    $latlongSelect = $obj->MySQLSelect("SELECT u.tDestinationLatitude as tDestinationLatitude, u.tDestinationLongitude as tDestinationLongitude, ua.vLatitude as vLatitude, ua.vLongitude as vLongitude FROM  `user_address` AS ua,  `register_user` AS u WHERE u.iUserId = '" . $sessionUserId . "' AND u.iUserId = ua.iUserId AND ua.eUserType =  'Rider'");
    //$vLatitude = $latlongSelect[0]['vLatitude'];
    //$vLongitude = $latlongSelect[0]['vLongitude'];
    //$tDestinationLatitude = $latlongSelect[0]['tDestinationLatitude'];
    //$tDestinationLongitude = $latlongSelect[0]['tDestinationLongitude'];
    if (empty($tDestinationLatitude) && empty($tDestinationLongitude)) {
        $tDestinationLatitude = $latlongSelect[0]['vLatitude'];
        $tDestinationLongitude = $latlongSelect[0]['vLongitude'];
    }

    $driverId = $db_order_data['iDriverId'];
    if ($driverId != 0) {
        $latlongSelect = $obj->MySQLSelect("SELECT vLatitude,vLongitude,vWorkLocationLatitude,vWorkLocationLongitude,eSelectWorkLocation FROM `register_driver` where iDriverId = '" . $driverId . "'");
        if ($latlongSelect[0]['eSelectWorkLocation'] == 'Dynamic') {
            $vLatitude = $latlongSelect[0]['vLatitude'];
            $vLongitude = $latlongSelect[0]['vLongitude'];
        } else {
            $vLatitude = $latlongSelect[0]['vWorkLocationLatitude'];
            $vLongitude = $latlongSelect[0]['vWorkLocationLongitude'];
        }
    }

    $tripsOrders = $obj->MySQLSelect("SELECT iTripId,tStartLat,tStartLong,tEndLat,tEndLong FROM `trips` where iOrderId = '" . $iOrderId . "'");
    if (!empty($tripsOrders)) {
        $iTripId = $tripsOrders[0]['iTripId'];
        //$vLatitude = $tripsOrders[0]['tStartLat'];
        //$vLongitude = $tripsOrders[0]['tStartLong'];
        //$tDestinationLatitude = $tripsOrders[0]['tEndLat'];
        //$tDestinationLongitude = $tripsOrders[0]['tEndLong'];
    }

    $siteUrl = $tconfig["tsite_url"];
    //Added By HJ On 13-02-2020 For Display Paymen Type Start
    $paymentType = ucwords($db_order_data['ePaymentOption']);
    if($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        if ($db_order_data['fNetTotal'] == 0 && $db_order_data['ePayWallet'] == "Yes") {
           $paymentType = ucwords($langage_lbl_admin['LBL_WALLET_TXT']);
        }
        else if ($db_order_data['fNetTotal'] > 0 && $db_order_data['ePayWallet'] == "Yes") {
           $paymentType = ucwords(strtolower($langage_lbl_admin['LBL_CASH_CAPS']));
        }
    } else if(isset($db_order_data['fNetTotal']) > 0 && $db_order_data['ePayWallet'] == "Yes"){
        if(strtoupper($db_order_data['ePaymentOption']) == "CARD"){
            //$paymentType = ucwords($langage_lbl_admin["LBL_PAY_BY_CARD_TXT"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
            $paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"]);
        }else if(strtoupper($db_order_data['ePaymentOption']) == "CASH"){
            //$paymentType = ucwords($langage_lbl_admin["LBL_PAY_BY_CASH_TXT"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
            $paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"]);
        }
    } else {
         if (strtoupper($db_order_data['ePaymentOption']) == 'CASH')
            $paymentType = ucwords($langage_lbl['LBL_CASH_CAPS']);
        else if (strtoupper($db_order_data['ePaymentOption']) == 'CARD')
            $paymentType = ucwords($langage_lbl['LBL_CARD_CAPS']);

    }
    //Added By HJ On 13-02-2020 For Display Paymen Type End

    $takeaway = 'No';
    //if($MODULES_OBJ->isTakeAwayEnable()) {
       //$takeawayenable = $MODULES_OBJ->isTakeAwayEnable();
       if($user_order_sql_data[0]['eTakeaway']=='Yes') {
          $prepareTime = $obj->MySQLSelect("select fPrepareTime from company where iCompanyId = ".$user_order_sql_data[0]['iCompanyId']);
          $preparetimedata = $prepareTime[0]['fPrepareTime']." ".$langage_lbl['LBL_MINUTES_TXT'];
          $takeaway = 'Yes';
       }
    //}

    // Added by HV for Delivery Preference
    if($MODULES_OBJ->isDeliveryPreferenceEnable()) {
        $selectedPrefSql = "SELECT selectedPreferences FROM orders WHERE iOrderId = ".$iOrderId;
        $selectedPrefData = $obj->MySQLSelect($selectedPrefSql);

        $selectedPrefIds = "";
        if($selectedPrefData[0]['selectedPreferences'] != "")
        {
            $selectedPrefIds = $selectedPrefData[0]['selectedPreferences'];
        }

        if($selectedPrefIds != "")
        {
            $vLang  = ($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : 'EN';
            $ssql = " WHERE iPreferenceId IN (".$selectedPrefIds.")";
            $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_VALUE(tTitle, '$.tTitle_".$vLang."')) as tTitle, JSON_UNQUOTE(JSON_VALUE(tDescription, '$.tDescription_".$vLang."')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences ".$ssql;

            $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);
        }
    }

    $currencySymbol = $db_order_data['vSymbol'];
    $currencycode = $db_order_data['currencycode'];

    $tipFeature = "No";
    if($MODULES_OBJ->isEnableDeliveryTipFeatureDeliverAll()) {
        $tipFeature = "Yes";

        $tip_amount_1 = array('title' => formateNumAsPerCurrency($TIP_AMOUNT_1,$currencycode), 'value' => $TIP_AMOUNT_1, 'equi-value' => "");
        $tip_amount_2 = array('title' => formateNumAsPerCurrency($TIP_AMOUNT_2,$currencycode), 'value' => $TIP_AMOUNT_2, 'equi-value' => "");
        $tip_amount_3 = array('title' => formateNumAsPerCurrency($TIP_AMOUNT_3,$currencycode), 'value' => $TIP_AMOUNT_3, 'equi-value' => "");

        if($DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL == "Percentage")
        {
            $tip_amount_1_value = round((($TIP_AMOUNT_1/100) * ($db_order_data['fSubTotal'] - $db_order_data['fOffersDiscount']) * $db_order_data['priceRatio']), 2);
            $tip_amount_2_value = round((($TIP_AMOUNT_2/100) * ($db_order_data['fSubTotal'] - $db_order_data['fOffersDiscount']) * $db_order_data['priceRatio']), 2);
            $tip_amount_3_value = round((($TIP_AMOUNT_3/100) * ($db_order_data['fSubTotal'] - $db_order_data['fOffersDiscount']) * $db_order_data['priceRatio']), 2);

            $tip_amount_1 = array('title' => $TIP_AMOUNT_1.'%', 'value' => ($TIP_AMOUNT_1/100), 'equi-value' => formateNumAsPerCurrency($tip_amount_1_value,$currencycode));
            $tip_amount_2 = array('title' => $TIP_AMOUNT_2.'%', 'value' => ($TIP_AMOUNT_2/100), 'equi-value' => formateNumAsPerCurrency($tip_amount_2_value,$currencycode));
            $tip_amount_3 = array('title' => $TIP_AMOUNT_3.'%', 'value' => ($TIP_AMOUNT_3/100), 'equi-value' => formateNumAsPerCurrency($tip_amount_3_value,$currencycode ));
        }
    }

    $eDriverPaymentStatus = "Unsettelled";
    $eDriverPaymentStatusData = $obj->MySQLSelect("SELECT eDriverPaymentStatus FROM `trips` where iOrderId = '" . $iOrderId . "'");

    if(scount($eDriverPaymentStatusData) > 0)
    {
        $eDriverPaymentStatus = $eDriverPaymentStatusData[0]['eDriverPaymentStatus'];
    }

    $scSql = "SELECT eShowTerms,eProofUpload,JSON_UNQUOTE(JSON_VALUE(tProofNote, '$.tProofNote_" . $vLang . "')) as tProofNote FROM service_categories WHERE iServiceId = ".$iServiceId;
    $scSqlData = $obj->MySQLSelect($scSql);
    $eShowTerms = $scSqlData[0]['eShowTerms'];
    $eProofUpload = $scSqlData[0]['eProofUpload'];
    $tProofNote = $scSqlData[0]['tProofNote'];

    $eShowTermsServiceCategories = "No";
    if($MODULES_OBJ->isEnableTermsServiceCategories() && $eShowTerms == "Yes" && $_SESSION['sess_user'] == "rider")
    {
        $eShowTermsServiceCategories = "Yes";
    }

    $eProofUploadServiceCategories = "No";
    if($MODULES_OBJ->isEnableProofUploadServiceCategories() && $eProofUpload == "Yes" && $_SESSION['sess_user'] == "rider")
    {
        $eProofUploadServiceCategories = "Yes";
    }
    // echo "<pre>"; print_r($db_order_data); exit;

    $ENABLE_PRESCRIPTION_UPLOAD = "No";
    if(ENABLE_PRESCRIPTION_UPLOAD == "Yes" && $iServiceId == "5")
    {
        $ENABLE_PRESCRIPTION_UPLOAD = "Yes";
    }

    $prescription_files = $obj->MySQLSelect("SELECT vImage FROM prescription_images WHERE order_id = $iOrderId");

    $date_format_data_array = array(
        'langCode' => $vLang,
        'DateFormatForWeb' => 1
    );
    $date_format_data_array['tdate'] =  $db_order_data['DeliveryDate'];
    $get_delivery_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
    $delivery_date_format = $get_delivery_date_format['tDisplayDate'];

    $delivery_pickup_date_format = "-";
    $delivery_drop_date_format = "-";
    if (isset($db_order_data['pickupTime']) && !empty($db_order_data['pickupTime'])) {
        $date_format_data_array['tdate'] =  $db_order_data['pickupTime'];
        $get_order_pickup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $delivery_pickup_date_format = $get_order_pickup_date_format['tDisplayTimeAbbr'];
    }
    if($db_order_data['iStatusCode'] == 6) { 
        $date_format_data_array['tdate'] =  $db_order_data['DeliveryDate'];
        $get_order_drop_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $delivery_drop_date_format = $get_order_drop_date_format['tDisplayTimeAbbr'];
    }

    
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>
        <style>
            .Msgbox{
            width:90%;padding-left:50px;text-align: center;
            }
            .marker {
            transform: rotate(-180deg);
            }
            .cancel {
            margin: 0 0 50px 0;
            }
            #delivery_pref_modal .delivery-pref-title {
            padding: 0 10px 20px 0;
            margin-top: 0
            }
            .delivery-pref-list {
            margin: 0 0 30px 0 !important;
            display: block !important;
            background-color: #ffffff !important;
            padding: 10px 0;
            }
            .delivery-pref-list li {
            text-align: left !important;
            padding: 10px 20px !important;
            width: 100% !important;
            border-right: none !important;
            margin-bottom: 0 !important;
            }
            .delivery-pref-list li label {
            font-weight: 600;
            }
            .delivery-pref-list li label span {
            margin-left: 5px;
            cursor: pointer;
            font-size: 18px
            }
            .delivery-pref-list li p {
            color: #404040;
            font-size: 12px;
            font-weight: 400;
            line-height: 18px;
            margin-top: 5px;
            }
            .delivery-pref-list .gen-btn {
            margin: 0;
            font-size: 14px;
            padding: 10px 20px;
            }
            .profile-earning-tip {
                margin-top: 0 !important
            }

            .profile-earning-tip li:first-child {
                border-top: 1px solid #dfdfdf !important;
            }

            /*.payment-delivery-tip .radio-toolbar label {
                padding: 12px 30px;
            }*/

            .payment-delivery-tip .radio-toolbar button {
                margin-left: 0;
            }

            .payment-delivery-tip .radio-toolbar .tip-amount-input {
                    margin-left: -7px;
            }

            #delivery_tip_section .invoice-data-holder {
                position: relative;
            }

            /*.payment-delivery-tip .remove-default-tip {
                right: 20px;
                top: 0;
                bottom: 10px;
                height: 10px;
                width: 10px;
            }*/

            #delivery_tip_section .overlay {
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                position: absolute;
                background: rgba(0,0,0,0.8);
                z-index: 99;
                display: none;
            }

            #delivery_tip_section .overlay__inner {
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                position: absolute;
            }

            #delivery_tip_section .overlay__content {
                left: 50%;
                position: absolute;
                top: 50%;
                transform: translate(-50%, -50%);
            }

            #delivery_tip_section .spinner {
                width: 50px;
                height: 50px;
                display: inline-block;
                border-width: 5px;
                border-color: rgba(255, 255, 255, 0.1);
                border-top-color: #ffffff;
                animation: spin 1s infinite linear;
                border-radius: 100%;
                border-style: solid;
                margin: 0;
            }

            @keyframes spin {
              100% {
                transform: rotate(360deg);
              }
            }

            /* Prescription Images */
            .prescription-files-section {
                background-color: #f5f5f5;
                border: 1px solid rgba(0,0,0,0.3);
                padding: 20px 20px 0 20px;
                text-align: center;
                width: 95%;
            }

            .prescription-files-block {
                display: flex;
                overflow-y: auto;
            }

            .prescription-files {
                width: 220px;
                border: 1px solid rgba(0,0,0,0.3);
                margin: 0 20px 20px 0;
                max-height: 200px;
                display: flex;
                flex: 1 0 auto;
            }



            .prescription-files a {
                width: 100%;
                background-color: #ffffff
            }

            .prescription-files img {
                width: 100%;
                height: 100%;
                object-fit: scale-down;
            }
            /* Prescription Images End */

            #map-canvas ul[role="menu"], #map-canvas li[role="menuitemcheckbox"] {
                width: auto;
            }

            #map-canvas li[role="menuitemcheckbox"] {
                margin-bottom: 0;
            }
        </style>
        <?php if ($UserType == 'Passenger') { ?>
        <script>
            $(document).ready(function () {
                var statuscode = '<?= $iStatusCode; ?>';
                //if(statuscode==5 || statuscode==6 || statuscode==11 || statuscode==13) {} else {
                if (statuscode == 6 || statuscode == 8) {
                } else {
                    var driverid = <?= $driverId; ?>;

                    var channel = 'ONLINE_DRIVER_LOC_<?= $driverId; ?>';
                    var channel1 = 'PASSENGER_<?= $sessionUserId; ?>';
                    var takeaway = '<?= $takeaway; ?>';

                    SOCKET_OBJ.subscribe(channel1, function (response) {
                       // var response = JSON.parse(data);

                        if (response.Message=='CabRequestAccepted') {
                            location.reload();
                        }

                        var ajaxData = {
                            'URL': '<?= $tconfig['tsite_url'] ?>cx-ajax_getOrderstatus.php',
                            'AJAX_DATA': {'order_details': response, 'iUserId': '<?= $sessionUserId; ?>', 'iUserType': '<?= $UserType; ?>','template':'<?= $template ?>','iOrderId':'<?= $iOrderId ?>'},
                            'REQUEST_DATA_TYPE': 'json'
                        };
                        getDataFromAjaxCall(ajaxData, function(response) {
                            if(response.action == "1") {
                                var data = response.result;
                                if(data!="No") {
                                    $("#orderstatusall").html(data);
                                    if (response.Message=='OrderConfirmByRestaurant' && takeaway=='Yes') {
                                        $(".preparetime").show();
                                    } else {
                                        $(".preparetime").hide();
                                    }
                                    if (response.Message=="OrderDelivered" && takeaway=='Yes') {
                                      $(".pickuptext").show();
                                    } else {
                                        $(".pickuptext").hide();

                                    }
                                }
                            }
                            else {
                                // console.log(response.result);
                            }
                        });

                        driverid = $("#driverid").val();
                        console.log(driverid);
                        if (driverid == 0) {

                            var ajaxData = {
                                'URL': '<?= $tconfig['tsite_url'] ?>cx-ajax_getOrderstatus.php',
                                'AJAX_DATA': {'chk_driver_assign': 1, 'order_details': response, 'iUserId': '<?= $sessionUserId; ?>', 'iUserType': '<?= $UserType; ?>','iOrderId':'<?= $iOrderId ?>'},
                                'REQUEST_DATA_TYPE': 'json'
                            };
                            getDataFromAjaxCall(ajaxData, function(response) {
                                if(response.action == "1") {
                                    var data = response.result;
                                    if (data != 0) {

                                        $("#driverid").val(data);
                                        var channel = 'ONLINE_DRIVER_LOC_' + data;
                                        subscribechannel(channel);
                                    }
                                }
                                else {
                                    console.log(response.result);
                                }
                            });
                        }
                    });
                }
                function subscribechannel(channel) {
                    SOCKET_OBJ.subscribe(channel, function (response)
                    {
                        //var response = JSON.parse(data);
                        console.log("Socket Cluster Message Found");
                        console.log(response);
                        handleResponse(response);
                    });
                }
            });
        </script>
        <?php } ?>
    </head>
    <body onLoad="history.forward()">
        <!-- home page -->
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- First Section -->
            <?php include_once("top/header.php"); ?>
            <!-- End: First Section -->
            <?php
                if($MODULES_OBJ->isDeliveryPreferenceEnable())
                {
                    include_once('contactless_pref_modal.php');
                }
                ?>
            <section class="profile-section">
                <div class="profile-section-inner">
                    <div class="profile-caption _MB0_">
                        <div class="page-heading">
                            <h1><?php if($takeaway=='Yes') {
                                echo $langage_lbl['LBL_Invoice']." - ".$langage_lbl['LBL_TAKE_AWAY'];
                                } else {
                                echo $langage_lbl['LBL_Invoice'];
                                } ?></h1>
                        </div>
                        <ul class="overview-detail">
                            <li>
                                <div class="overview-data">
                                    <strong><?= $langage_lbl['LBL_ORDER_NO_TXT']; ?></strong>
                                    <span><?= $db_order_data['vOrderNo']; ?></span>
                                </div>
                            </li>
                            <li>
                                <div class="overview-data">
                                    <strong><?= $langage_lbl['LBL_ORDER_STATUS_TXT']; ?></strong>
                                    <?php if($db_order_data['iStatusCode'] == 6 && $db_order_data['eTakeaway']=='Yes' && ($sessionUserType == 'company' || $sessionUserType == 'rider')) { ?>
                                    <span><?= $langage_lbl['LBL_PICKED_UP']; ?></span>
                                    <?php } else { ?>
                                    <span><?= $db_order_data['vStatus']; ?></span>
                                    <?php } ?>
                                </div>
                            </li>
                            <li>
                                <div class="overview-data">
                                    <strong><?= $langage_lbl['LBL_ORDER_DATE_TXT']; ?></strong>
                                    <span><?= $delivery_date_format;//@date('d M Y', @strtotime($db_order_data['DeliveryDate'])); ?></span>
                                </div>
                            </li>
                            <li>
                                <div class="overview-data">
                                    <strong><?= $langage_lbl['LBL_PASSENGER_NAME_TEXT_DL']; ?></strong>
                                    <span><?= clearName($db_order_data['UserName']) ?>
                                    <? if (!empty($getratings['UserRate'])) { ?>
                                    (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>)
                                    <? } ?>
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <div class="left-block">
                        <div class="inv-block-inner">
                            <?php if ($UserType == 'Passenger') { ?>
                            <input type="hidden" name="driverid" id="driverid" value="<?= $driverId; ?>">
                            <div class="gmap-div gmap-div1" id="invoice_map">
                                <div id="map-canvas" class="gmap3 google-map" style="height: 302px"></div>
                            </div>
                            <?php if($MODULES_OBJ->isDeliveryPreferenceEnable() && $selectedPrefIds != "") { ?>
                            <strong class="sub-block-title"><?= $langage_lbl['LBL_DELIVERY_PREF']; ?></strong>
                            <div class="track-order">
                                <ul class="delivery-pref-list">
                                    <?php foreach ($deliveryPrefSqlData as $delivery_pref) { ?>
                                    <li>
                                        <label>
                                        <?= $delivery_pref['tTitle'] ?>
                                        <?php if($delivery_pref['eContactLess'] == 'Yes') { ?>
                                        <span data-toggle="modal" data-target="#contactless_pref_modal" title="How it works?"><i class="fa fa-question-circle-o"></i></span>
                                        <?php } ?>
                                        </label>
                                        <p><?= $delivery_pref['tDescription'] ?></p>
                                        <?php if($iStatusCode == 6) { ?>
                                        <?php if($delivery_pref['eContactLess'] == 'Yes' && $user_order_sql_data[0]['vImageDeliveryPref'] != "") { ?>
                                        <p><a href="<?= $tconfig['tsite_upload_order_delivery_pref_images'].$user_order_sql_data[0]['vImageDeliveryPref']; ?>" target="_blank" class="gen-btn">View Image</a></p>
                                        <?php } } ?>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php } ?>
                            <strong class="sub-block-title"><?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></strong>
                            <?php } ?>
                            <div class="invoice-data-holder track-order">
                                <div>
                                    <?php if ($UserType != 'Passenger') { ?>
                                    <strong class="sub-block-title"><?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></strong>
                                    <?php } ?>
                                    <!--<div class="profile-image">
                                        <img src="images/user_place.svg" alt="">
                                        </div>-->
                                    <!--<div class="profile-image-blank"></div>-->
                                    <div class="inv-data profile-image-blank">
                                        <?php if ($sessionUserType == 'driver' || ($_SESSION['sess_eSystem'] == 'General' && $_SESSION['sess_user'] == 'company')) { ?>
                                        <div class="driver-info new-driver" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_DELIVERY_EARNING_FRONT']; ?></h3>
                                        </div>
                                        <div class="fare-breakdown">
                                            <div class="fare-breakdown-inner">
                                                <ul>
                                                    <? $c = 1; foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                                    <li>
                                                        <?= ($c == scount($db_order_data['History_Arr'])) ? '<span>'.$key.'</span>' : $key; ?>
                                                        <b><?= $value; ?></b>
                                                    </li>
                                                    <?php $c++; }
                                                        ?>
                                                </ul>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                        <?php }
                                        else if ($sessionUserType == 'rider') { ?>
                                        <strong><?= $langage_lbl['LBL_YOUR_ORDER']; ?></strong>
                                        <ul>
                                            <li><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></li>
                                            <?php
                                                $db_menu_item_list = $db_order_data['itemlist'];
                                                if (!empty($db_menu_item_list)) { ?>
                                            <li class="item-list">
                                                <? foreach ($db_menu_item_list as $key => $val) { ?>
                                                <div class="itme-row">
                                                    <?php if($db_order_data['eBuyAnyService'] == "Yes") { ?>
                                                    <span>
                                                        <?= $val['MenuItem']; ?> X <?= $val['iQty']; ?>
                                                        <? if ($val['SubTitle'] != '') { ?>
                                                        <strong style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</strong>
                                                        <? } ?>
                                                        <? if ($val['eDecline'] == "Yes") { ?>
                                                            <br/><strong style="font-size: 12px;">(<?= $langage_lbl['LBL_USER_DECLINED'] ?>)</strong>
                                                        <? } ?>
                                                        <? if ($val['eExtraPayment'] == "No" && $val['eItemAvailable'] == "Yes") { ?>
                                                            <br/><strong style="font-size: 12px;">(<?= $langage_lbl['LBL_PAYMENT_NOT_REQUIRED'] ?>)</strong>
                                                        <? } elseif ($val['eItemAvailable'] == "No") { ?>
                                                            <? if ($val['eExtraPayment'] == "No") { ?>
                                                            <br/><strong style="font-size: 12px;">(<?= $langage_lbl['LBL_ITEM_NO_PAYMENT_UNAVAILABLE'] ?>)</strong>
                                                            <?php } else { ?>
                                                            <br/><strong style="font-size: 12px;">(<?= $langage_lbl['LBL_ITEM_NOT_AVAILABLE'] ?>)</strong>
                                                            <? } ?>
                                                        <?php } ?>
                                                    </span>
                                                    <b><?= $val['fTotPrice']  ?></b>
                                                    <?php } else { ?>
                                                    <span><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?><? if ($val['SubTitle'] != '') { ?><strong style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</strong><? } ?></span>
                                                    <b><?= $val['fTotPrice'] ?></b>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>
                                            </li>
                                            <?php } ?>
                                            <? foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                            <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                            <?php } ?>

                                            <? if($db_order_data['fTipAmount'] > 0) { ?>
                                                <? foreach ($db_order_data['History_Arr_first'] as $key => $value) { ?>
                                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                                <? } ?>

                                                <? foreach ($db_order_data['History_Arr_Total'] as $key => $value) { ?>
                                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                                <? } ?>
                                            <? } else { ?>
                                                <? foreach ($db_order_data['History_Arr_first'] as $key => $value) { ?>
                                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                                <? } ?>
                                            <? } ?>
                                        </ul>
                                        <?php }
                                        else { ?>
                                        <strong><?= $langage_lbl['LBL_YOUR_ORDER']; ?></strong>
                                        <ul>
                                            <li><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></li>
                                            <?php
                                                $db_menu_item_list = $db_order_data['itemlist'];
                                                if (!empty($db_menu_item_list)) { ?>
                                            <li class="item-list">
                                                <? foreach ($db_menu_item_list as $key => $val) { ?>
                                                <div class="itme-row">
                                                    <span><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?><? if ($val['SubTitle'] != '') { ?><strong style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</strong><? } ?></span>
                                                    <b><?= $val['fTotPrice'] ?></b>
                                                </div>
                                                <?php } ?>
                                            </li>
                                            <?php } ?>
                                            <? foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                            <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                            <?php } ?>
                                            <? foreach ($db_order_data['History_Arr_first'] as $key => $value) { ?>
                                            <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                            <? } ?>
                                            <? foreach ($db_order_data['History_Arr_second'] as $key => $value) { ?>
                                            <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                            <? } ?>
                                        </ul>
                                        <?php } ?>
                                        <?php if ($db_order_data['iStatusCode'] == '8') { ?>
                                        <div class="panel panel-warning" style="margin-bottom: 0">
                                            <div class="panel-heading">
                                                <div><?= $langage_lbl["LBL_ORDER_CANCEL_WEB_TEXT"]; ?></div>
                                                <?php
                                                    if ($sessionUserType == 'company') {
                                                        if ($db_order_data['fRestaurantPaidAmount'] > 0) {
                                                            ?>
                                                <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>  : <?= $db_order_data['RestaurantPaidAmount']; ?></div>
                                                <?php } else { ?>
                                                <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                <?php
                                                    }
                                                    }
                                                    ?>
                                                <?php
                                                    if ($sessionUserType == 'driver') {
                                                        if ($db_order_data['fDriverPaidAmount'] > 0) {
                                                            ?>
                                                <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?= $db_order_data['DriverPaidAmount']; ?></div>
                                                <?php } else { ?>
                                                <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                <?php
                                                    }
                                                    }
                                                    ?>
                                                <?php if ($sessionUserType == 'rider') { ?>
                                                <div><?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?= $db_order_data['CancellationCharge']; ?>
                                                    <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                    ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                    <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                    ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                    <? } else if ($db_order_data['ePaymentOption'] == 'Card' && $db_order_data['eBuyAnyService'] == "No") { ?>
                                                    ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"] ?> )
                                                    <? } ?>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php } else if ($db_order_data['iStatusCode'] == '7') { ?>
                                        <div class="panel panel-warning" style="margin-bottom: 0">
                                            <div class="panel-heading">
                                                <div><?= $langage_lbl["LBL_ORDER_REFUND_WEB_TEXT"]; ?></div>
                                                <?php
                                                    if ($sessionUserType == 'company') {
                                                        if ($db_order_data['fRestaurantPaidAmount'] > 0) {
                                                            ?>
                                                <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> : <?= $db_order_data['RestaurantPaidAmount']; ?></div>
                                                <?php } else { ?>
                                                <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                <?php
                                                    }
                                                    }
                                                    ?>
                                                <?php
                                                    if ($sessionUserType == 'driver') {
                                                        if ($db_order_data['fDriverPaidAmount'] > 0) {
                                                            ?>
                                                <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?= $db_order_data['DriverPaidAmount']; ?></div>
                                                <?php } else { ?>
                                                <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                <?php
                                                    }
                                                    }
                                                    ?>
                                                <?php if ($sessionUserType == 'rider') { ?>
                                                <div> <?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?= $db_order_data['CancellationCharge']; ?>
                                                    <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                    ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                    <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                    ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                    <? } else if ($db_order_data['ePaymentOption'] == 'Card') { ?>
                                                    ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"] ?> )
                                                    <? } ?>
                                                </div>
                                                <div> <?= $langage_lbl["LBL_REFUND_WEB_TXT"] ?> : <?= $db_order_data['RefundAmount']; ?></div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="inv-destination-data inv__details test_head">
                                    <ul>
                                        <li>
                                            <i class="fa fa-map-marker"></i>
                                            <div class="invoice__right__side">
                                            <strong><?= $langage_lbl["LBL_RESTAURANT_ADDRESS"] ?>:</strong>
                                            <p>
                                                <?php if($sessionUserType != 'company') { ?>
                                                <?= clearName($db_order_data['vRestuarantLocation']); ?>
                                            </p>
                                            <?php } else { ?>
                                            <p>
                                            <?= $db_order_data['vRestuarantLocation']; ?>
                                            <?php } ?>
                                            </p>
                                        </div>
                                        </li>
                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <div class="invoice__right__side">
                                            <strong><?= $langage_lbl["LBL_ORDER_PICKUP_TIME"] ?>:</strong>
                                        <!-- // [HP] 25-10-2021 #region :  date change -->
                                        <p><? /*if (isset($db_order_data['pickupTime']) && !empty($db_order_data['pickupTime'])) {
                                                echo @date('h:i A', @strtotime($db_order_data['pickupTime']));
                                            } else {
                                                echo "-";
                                            } */ echo $delivery_pickup_date_format;?></p>
                                            </div>
                                        </li>
                                        <?php if($db_order_data['eTakeaway']!='Yes') { ?>
                                        <li>
                                            <i class="fa fa-map-marker"></i>
                                            <div class="invoice__right__side">
                                            <strong><?= $langage_lbl["LBL_USER_ADDRESS"] ?>:</strong>
                                            <p style="word-break: break-word;">
                                                <?php if($sessionUserType != 'company') { ?>
                                                <?= clearName($db_order_data['DeliveryAddress']); ?>
                                                <?php } else { ?>
                                                <?= $db_order_data['DeliveryAddress']; ?>
                                                <?php } ?>
                                            </p>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <div class="invoice__right__side">
                                            <strong><?= $langage_lbl["LBL_DELIVERY_TIME"] ?>:</strong>
                                            <p><? echo $delivery_drop_date_format;
					    //if($db_order_data['iStatusCode'] == 6) { echo @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); } else { echo "-"; } ?></p>
                                            </div>
                                        </li>
                                        <?php } ?>
                                        <?php if($db_order_data['eBuyAnyService']=='No') { ?>
                                        <li>
                                            <i class="fa fa-list-alt"></i>
                                            <div class="invoice__right__side">
                                            <strong><?= $langage_lbl["LBL_SPECIAL_INSTRUCTION_TXT"] ?>:</strong>
                                            <p><?= !empty(trim($db_order_data['vInstruction'])) ? clearName($db_order_data['vInstruction']) : $langage_lbl["LBL_NO_SPECIAL_INSTRUCTION"]; ?></p>
                                            </div>
                                        </li>
                                        <?php } ?>
                                        <?php if($db_order_data['eTakeaway']=='Yes' && $sessionUserType == 'company') { ?>
                                            <li>
                                                <i class="fa fa-first-order"></i>
                                                <div class="invoice__right__side">
                                                <strong><?= $langage_lbl["LBL_ORDER_PICKDUP"] ?>:</strong>
                                                <?php if($sessionUserType == 'company') { ?>
                                                <p><?= str_replace("#RESTAURANT_NAME#", $db_order_data['CompanyName'], $langage_lbl["LBL_TAKE_AWAY_ORDER_NOTE"]); ?></p>
                                                <?php } ?>
                                                </div>
                                            </li>
                                        <?php } ?>
                                        <?php if($db_order_data['fTipAmount'] > 0 && $sessionUserType == 'rider') { ?>
                                            <li>
                                                <i><img src="<?= $tconfig['tsite_url'].'assets/img/GiveTip.svg' ?>"></i>
                                                <div class="invoice__right__side">
                                                <strong><?= $langage_lbl['LBL_ORDER_TIP_TITLE_TXT']; ?>:</strong>
                                                <p><?= str_replace('#AMOUNT#',  formateNumAsPerCurrency($db_order_data['fTipAmount'], $currencycode), $langage_lbl['LBL_THANKS_DELIVERY_TIP']); ?></p>
                                            </div>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>

                            <?php if(($db_order_data['fTipAmount'] == 0 && in_array($db_order_data['iStatusCode'], [1,2,4,5]) && $sessionUserType == 'rider' && $db_order_data['ePaymentOption'] == "Card" && $takeaway == 'No' && $MODULES_OBJ->isEnableDeliveryTipFeatureDeliverAll() && $db_order_data['eBuyAnyService'] == "No" && $db_order_data['eOrderplaced_by'] == "User") || ($db_order_data['fTipAmount'] == 0 && $db_order_data['iStatusCode'] == 6 && $sessionUserType == 'rider' && $db_order_data['ePaymentOption'] == "Card" && $takeaway == 'No' && $MODULES_OBJ->isEnableDeliveryTipFeatureDeliverAll() && $eDriverPaymentStatus == "Unsettelled" && strtolower(ENABLE_DELIVERY_TIP_IN_HISTORY) == 'yes' && $db_order_data['eBuyAnyService'] == "No" && $db_order_data['eOrderplaced_by'] == "User")) { ?>
                            <div id="delivery_tip_section">
                                <strong class="sub-block-title" style="margin-top: 28px"><?= $langage_lbl['LBL_DELIVERY_TIP_TXT']; ?><span data-toggle="modal" data-target="#delivery_tip_modal" title="How it works?" style="margin-left: 10px; font-size: 18px; cursor: pointer;"><i class="fa fa-question-circle-o"></i></span></strong>
                                <div class="invoice-data-holder track-order">
                                    <div class="overlay">
                                        <div class="overlay__inner">
                                            <div class="overlay__content">
                                                <div class="spinner"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <p style="margin-bottom: 30px; width: 100%"><?= $langage_lbl['LBL_SELECT_TXT'].' '.strtolower($langage_lbl['LBL_OR_TXT']).' '.strtolower($langage_lbl['LBL_TIP_AMOUNT_ENTER_TITLE']) ?> </p>
                                    <div class="payment-delivery-tip">
                                        <div class="radio-toolbar">
                                            <div class="tip-amount-block">
                                                <input type="radio" id="tip_amount_1" name="tip_amount" value="<?= $tip_amount_1['value'] ?>">
                                                <label for="tip_amount_1">
                                                    <?= $tip_amount_1['title'] ?><br>
                                                    <span><?= $tip_amount_1['equi-value'] ?></span>
                                                </label>
                                                <span class="remove-default-tip">
                                                    <img src="<?= $tconfig['tsite_url'].'assets/img/cancel-new.svg'; ?>">
                                                </span>
                                            </div>

                                            <div class="tip-amount-block">
                                                <input type="radio" id="tip_amount_2" name="tip_amount" value="<?= $tip_amount_2['value'] ?>">
                                                <label for="tip_amount_2">
                                                    <?= $tip_amount_2['title'] ?><br>
                                                    <span><?= $tip_amount_2['equi-value'] ?></span>
                                                </label>
                                                <span class="remove-default-tip">
                                                    <img src="<?= $tconfig['tsite_url'].'assets/img/cancel-new.svg'; ?>">
                                                </span>
                                            </div>

                                            <div class="tip-amount-block">
                                                <input type="radio" id="tip_amount_3" name="tip_amount" value="<?= $tip_amount_3['value'] ?>">
                                                <label for="tip_amount_3">
                                                    <?= $tip_amount_3['title'] ?><br>
                                                    <span><?= $tip_amount_3['equi-value'] ?></span>
                                                </label>
                                                <span class="remove-default-tip">
                                                    <img src="<?= $tconfig['tsite_url'].'assets/img/cancel-new.svg'; ?>">
                                                </span>
                                            </div>

                                            <div class="tip-amount-block tip-amount-block-other">
                                                <input type="radio" id="tip_amount_4" name="tip_amount" value="other">
                                                <label for="tip_amount_4">
                                                    <?= $langage_lbl['LBL_OTHER_TXT'] ?>
                                                </label>
                                                <span class="remove-default-tip">
                                                    <img src="<?= $tconfig['tsite_url'].'assets/img/cancel-new.svg'; ?>">
                                                </span>
                                            </div>

                                            <div class="tip-amount-block-input" style="display: none;">
                                                <input type="text" class="tip-currency" value="<?= $currencySymbol ?>" disabled="disabled">
                                                <div>
                                                    <input maxlength="1000" type="text" class="tip-amount-input numberPlay" id="tip_amount_collect" name="tip_amount_collect" value="" placeholder="<?= $langage_lbl['LBL_TIP_AMOUNT_ENTER_TITLE'] ?>">
                                                    <img src="<?= $tconfig['tsite_url'].'assets/img/cancel.svg'; ?>" class="remove-tip-amount">

                                                </div>
                                            </div>
                                            <button type="button" id="add_tip_btn"><?= $langage_lbl['LBL_ADD_NOW'] ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php
                                include_once('delivery_tip_modal.php');
                                include_once('delivery_tip_success_modal.php');
                            ?>

                            <?php if($eProofUploadServiceCategories == "Yes" && !empty($db_order_data['vIdProofImg'])) { ?>
                            <div>&nbsp;</div>
                            <div id="identification_section">
                                <div class="invoice-data-holder">
                                    <h3><?= $langage_lbl['LBL_IDENTIFICATION'] ?></h3>
                                    <div class="invoice-right-bottom-img">
                                        <div class="beforeImgDiv">
                                            <a href="<?= $tconfig['tsite_upload_id_proof_service_categories_images']."Orders/".$db_order_data['vIdProofImg'] ?>" target="_blank">
                                                <img src="<?= $tconfig['tsite_upload_id_proof_service_categories_images']."Orders/".$db_order_data['vIdProofImg'] ?>" style="width:200px;" alt="<?= $langage_lbl['LBL_IDENTIFICATION'].' '.$langage_lbl['LBL_IMAGE'] ?>">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                            <?php if(scount($prescription_files) > 0) { ?>
                            <div>&nbsp;</div>
                            <div id="prescription_section">
                                <div class="invoice-data-holder">
                                    <h3><?= $langage_lbl['LBL_PRESCRIPTION'] ?></h3>
                                    <div class="prescription-files-section">
                                        <div class="prescription-files-block">
                                        <?php
                                            foreach ($prescription_files as $pfile) {
                                                $pfileExt = explode('.', $pfile['vImage']);
                                                $pfileExt = end($pfileExt);

                                                $img_src = $tconfig['tsite_upload_prescription_image'].'/'.$pfile['vImage'];
                                                $file_url = $tconfig['tsite_upload_prescription_image'].'/'.$pfile['vImage'];
                                                if(strtolower($pfileExt) == "pdf") {
                                                    $img_src = $tconfig['tsite_url'].'assets/img/pdf-placeholder.png';
                                                }
                                                elseif (strtolower($pfileExt) == "doc") {
                                                    $img_src = $tconfig['tsite_url'].'assets/img/doc-placeholder.png';
                                                }
                                                elseif (strtolower($pfileExt) == "docx") {
                                                    $img_src = $tconfig['tsite_url'].'assets/img/docx-placeholder.png';
                                                }
                                        ?>
                                            <div class="prescription-files">
                                                <a href="<?= $file_url ?>" target="_blank">
                                                    <img src="<?= $img_src ?>">
                                                </a>
                                            </div>
                                        <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="left-right">
                        <div class="track-order-data">
                            <div>
                                <div class="rest-name-holder">
                                    <i><img src="assets/img/apptype/<?php echo $template;?>/restaurant.svg" alt=""></i>
                                    <strong><?= $langage_lbl["LBL_RESTAURANT_NAME_TXT"] ?>:</strong>
                                    <p>
                                        <?php if($sessionUserType != 'company') { ?>
                                        <?= clearName($db_order_data['CompanyName']); ?>
                                        <?php } else { ?>
                                        <?= $db_order_data['CompanyName']; ?>
                                        <?php } ?>
                                        <? if (!empty($getratings['CompanyRate'])) { ?>
                                        (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>)
                                        <? } ?>
                                    </p>
                                </div>
                                <? if (!empty($db_order_data['DriverName'])) { ?>
                                <div class="rest-name-holder">
                                    <i><img src="assets/img/apptype/<?php echo $template;?>/scooter_driver.svg" alt=""></i>
                                    <strong><?= $langage_lbl['LBL_ORDER_DELIVERED_BY_DRIVER']; ?> : </strong>
                                    <p><?= $db_order_data['DriverName']; ?>
                                        <? if (!empty($getratings['DriverRate'])) { ?>
                                        (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>)
                                        <? } ?>
                                    </p>
                                </div>
                                <? } ?>
                                <?php if ($UserType == 'Passenger') { ?>
                                <ul id="orderstatusall">
                                    <?php
                                        $orderStatus_json = FetchOrderLiveTrackDetails($iOrderId, $sessionUserId, $UserType);
                                        $orderStatus = json_decode($orderStatus_json);
                                        // echo "<pre>"; print_r($orderStatus); exit;
                                        if($db_order_data['eTakeaway'] == 'Yes') {
                                            $img_array = array("time-left.svg","shop.svg","tick.svg");
                                        } else {
                                            $img_array = array("time-left.svg","shop.svg","taxi-driver.svg","deliver_scooter.svg","tick.svg","return.svg");
                                        }

                                        if($db_order_data['eBuyAnyService'] == 'Yes') {
                                            $img_array = array("time-left.svg","taxi-driver.svg","bullet.svg","credit-card.svg","deliver_scooter.svg","tick.svg");
                                        }
                                        //print_R($img_array); exit;
                                        $flag_cancelled = 0;
                                        $all_order_status = array();
                                        foreach ($orderStatus->message as $key => $value) {
                                            //echo "<pre>";print_R($value);die;
                                            $all_order_status[] = $value->iStatusCode;
                                            $time = date("h:i A", strtotime($value->dDate));
                                            if ($value->eCompleted == 'Yes') {
                                                if ($value->iStatusCode == 8 || $value->iStatusCode == 9 || $value->iStatusCode == 11) {
                                                    ?>
                                    <li class="cancel passed" date-time="<?= $time; ?>">
                                        <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/close.svg" alt=""></i>
                                        <strong><?= $value->vStatus; ?></strong>
                                        <p><?= $value->vStatus_Track; ?></p>
                                    </li>
                                    <?php
                                        $flag_cancelled = 1;
                                        } else {
                                        ?>
                                    <li class="passed" date-time="<?= $time; ?>">
                                        <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
                                        <strong><?= $value->vStatus; ?></strong><?php if ($value->iStatusCode == 5) { ?><a class="open-popup" data-id="call-info-model" onClick="displayDriverDetails('<?= $value->phoneCode; ?>', '<?= clearPhone($value->vPhone); ?>', '<?= clearName($value->driverName); ?>');" tell=""><img src="<?= $siteUrl; ?>assets/img/call-img.svg" width="15px" height="15px"  alt="" style="margin:0 0 0 10px"></a><?php } ?></strong>
                                        <?php /*if($value->eTakeaway == 'Yes') { ?>
                                        <p><?= str_replace('#RESTAURANT_NAME#',$db_order_data['CompanyName'],$langage_lbl['LBL_TAKE_AWAY_ORDER_NOTE']); ?></p>
                                        <?php } else {*/ ?>
                                        <p><?= $value->vStatus_Track; ?></p>
                                        <?php //} ?>
                                    </li>
                                    <?php
                                        }
                                        } else {
                                        ?>
                                    <li class="" id="status<?= $value->iStatusCode; ?>">
                                        <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
                                        <strong><?= $value->vStatus; ?></strong>
                                        <?php /*if($value->eTakeaway == 'Yes') { ?>
                                        <p><?= str_replace('#RESTAURANT_NAME#',$db_order_data['CompanyName'],$langage_lbl['LBL_TAKE_AWAY_ORDER_NOTE']); ?></p>
                                        <?php } else {*/ ?>
                                        <p><?= $value->vStatus_Track; ?></p>
                                        <?php //} ?>
                                    </li>
                                    <?php
                                        }
                                        }
                                        ?>
                                </ul>
                                <!-- // [HP] 25-10-2021  #region : Declined By Restaurant text change -->
                                <?php if ($value->iStatusCode == 9  && $flag_cancelled == 1) { ?>
                                    <!--<div class="order_cancel"><?= strtoupper($langage_lbl['LBL_ORDER_DECLINED']); ?></div>-->

                                <?php } else if ($flag_cancelled == 1) { ?>
                                <!--<div class="order_cancel"><?= strtoupper($langage_lbl['LBL_ORDER_CANCELLED']); ?></div>-->
                                <?php }
                                    if($takeaway=='Yes') { ?>
                                <div class="new-box-one preparetime" style="margin-top:20px; <?php if($value->OrderCurrentStatusCode==2) { ?> display:block; <? } else { ?> display:none; <? } ?>">
                                    <h4><b><?= $langage_lbl['LBL_REST_PREPARATION_TIME']?></b>: <?= $preparetimedata ?></h4>
                                    <div style="margin-top:5px"><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i>
                                        <span><?= $db_order_data['vRestuarantLocation'];  ?></span>
                                    </div>

                                    <div>
                                        <!-- <a class="gen-btn" target="new" href="https://www.google.com/maps/search/?api=1&query=<?= $vLatitudeSource ?>, <?= $vLongitudeSource; ?>" style="margin: 15px 0 0 0;"><?= $langage_lbl['LBL_NAVIGATE'] ?></a> -->
                                        <a class="gen-btn" target="new" href="https://www.google.com/maps/dir/<?= $vLatitudeSource.','.$vLongitudeSource.'/'.$tDestinationLatitude.','.$tDestinationLongitude ?>" style="margin: 15px 0 0 0;"><?= $langage_lbl['LBL_NAVIGATE'] ?></a>
                                    </div>

                                </div>
                                <div class="new-box-one pickuptext" style="margin-top:20px; <?php if($value->OrderCurrentStatusCode==6) { ?> display:block; <? } else { ?> display:none; <? } ?>">
                                    <h4><b><?= $langage_lbl["LBL_ORDER_PICKDUP"] ?></b>: <?= str_replace("#RESTAURANT_NAME#", $db_order_data['CompanyName'], $langage_lbl["LBL_TAKE_AWAY_ORDER_NOTE"]); ?></h4>
                                </div>
                                <? } ?>
                                <? } ?>
                            </div>
                            <div class="invoice-pay-type">
                                <?php
                                    //Added By HJ On 08-08-2019 For Solved Bug - 6722 Start
                                    $ePaymentOption = $db_order_data['ePaymentOption'];
                                    if ($db_order_data['ePaymentOption'] == "Card" && $db_order_data['ePayWallet'] == 'Yes') {
                                        $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                                    }
                                    //Added By HJ On 08-08-2019 For Solved Bug - 6722 End
                                    ?>
                                <strong><?= $langage_lbl['LBL_PAYMENT_TYPE_TXT']; ?> : </strong><strong><?= $paymentType; ?></strong>
                            </div>
                        </div>
                    </div>
                    <!-- COMMENTED BECOZ WHEN OPEN DIRECTLY INVOICE LINK OR COME FROM WEB CREATE ORDER THEN NOT WORKED.
                    <div class="btn" style="margin-top: 30px;">
                        <a onclick="javascript:window.top.close();" class="gen-btn"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                    </div>-->
                </div>
            </section>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <div class="custom-modal-main in" id="call-info-model">
            <div class="custom-modal">
                <div class="model-header">
                    <h4><?= $langage_lbl['LBL_DELIVER_DETAILS'] ?></h4>
                    <i class="icon-close" data-dismiss="modal"></i>
                </div>
                <div class="model-body">
                    <form class="add-new-card-data" name="frmcreditcard" id="frmcreditcard" onSubmit="return false;" novalidate="novalidate">
                        <!--<div class="close-icon">
                            <svg width="16" height="16" viewBox="0 0 14 14"><path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path></svg>
                            </div>-->
                        <div id="service_detail">
                            <p id="drverName"></p>
                            <p id="phnnumber"><?= $langage_lbl['LBL_NOT_FOUND']; ?></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include_once('top/footer_script.php'); ?>
        <script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript" async></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>assets/js/jquery_easing.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>assets/js/markerAnimate.js" defer></script>
        <script>
            $(document).ready(function () {
                <?php if(isset($_REQUEST['success']) && $_REQUEST['success'] == 1 && isset($_REQUEST['PAGE_TYPE']) && $_REQUEST['PAGE_TYPE'] == "ORDER_TIP_COLLECT" && !isset($_SESSION['delivery_tip_modal_open'])) { ?>
                    $('.custom-modal-main').removeClass('active');
                    $('#delivery_tip_success_modal').addClass('active');


                <?php $_SESSION['delivery_tip_modal_open'] = $iOrderId; } ?>
                $(document).on('click', '.open-popup', function (e) {
                    var DATAID = $(this).attr('data-id');
                    $('.small-model').removeClass('active');
                    $(document).find('#' + DATAID).addClass('active');
                });
                $(document).on('click', '.close-icon', function () {
                    $(this).closest('.product-model-overlay').removeClass('active');
                })

                $('body').keydown(function (e) {
                    if (e.keyCode == 27) {
                        $('.close-icon').trigger('click')
                    }
                });

                var e = $.Event("keydown", {
                    keyCode: 27
                });

                $('#escape').click(function () {
                    $("body").trigger(e);
                });
            })
            var iTripId = '<?= $iTripId; ?>';
            var latlng;
            var locallat;
            var locallang;
            var map;
            var interval3;
            var marker = [];
            var marker1 = [];
            var myOptions = [];
            // var directionsService = new google.maps.DirectionsService(); // For Route Services on map
            // var directionsOptions = {// For Polyline Route line options on map
                // polylineOptions: {
                    // strokeColor: '#FF7E00',
                    // strokeWeight: 5
                // }
            // };
            // var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);

            function moveToLocation(lat, lng) {
                var center = new google.maps.LatLng(lat, lng);
                map.panTo(center);
            }
            function handleResponse(response) {
                if (response.vLatitude != "" && response.vLongitude != "") {
                    console.log(response.vLatitude);
                    console.log(response.vLongitude);
                    $('.map-page').show();
                    latlng = new google.maps.LatLng(response.vLatitude, response.vLongitude);
                    myOptions = {
                        zoom: 4,
                        center: latlng,
                    }
                    var duration = parseInt(950);
                    if (duration < 0) {
                        duration = 1;
                    }
                    setTimeout(function () {
                        marker.animateTo(latlng, {easing: 'linear', duration: duration});
                        map.panTo(latlng); // For Move Google Map By Animate
                    }, 2000);
                }
            }
            function displayDriverDetails(phoneCode, phone, driverName) {
                $('#call-info-model').addClass('active');
                $("#drverName").text(driverName);
                if (phone != "") {
                    $("#phnnumber").text('<?= $langage_lbl['LBL_PHONE']; ?>: ' + "+" + phoneCode + " " + phone);
                }
                //$("#drverName").text('<?= $langage_lbl['LBL_PHONE']; ?>(' + driverName + ')');
            }
            function changeMarker(deg) {
                google.maps.event.clearListeners(map, 'idle');
            }
            function createPolyLine(cus_polyline) {
                        if(typeof flightPath !== 'undefined'){
                            flightPath.setMap(null);
                            flightPath ='';
                        }

                        flightPath = cus_polyline;
                        flightPath.setMap(map);
                    }
            function routeDirections(fromlat, fromlong, tolat, tolong) {
            // var directionsService = new google.maps.DirectionsService(); // For Route Services on map
            var directionsOptions = {// For Polyline Route line options on map
            polylineOptions: {
            strokeColor: '#FF7E00',
            strokeWeight: 5
            }
            };
            var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
                directionsDisplay.setMap(null); // Remove Previous Route.
                //console.log(fromlat + "from" + fromlong + "TO" + tolat + "-- " + tolong);
                if (fromlat != "" && tolat != "") {
                    var newFrom = fromlat + ", " + fromlong;
                    //if (eType == 'UberX') {
                    //var newTo = fromlat + ", " + fromlong;
                    //} else {
                    var newTo = tolat + ", " + tolong;
                    //}

                    //Make an object for setting route
                    var request = {
                        origin: newFrom, // From locations latlongs
                        destination: newTo, // To locations latlongs
                        travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                    };

                    //Draw route from the object


            var source_latitude = fromlat;
            var source_longitude = fromlong;
            var dest_latitude = tolat;
            var dest_longitude = tolong;
            var waypoint0 = newFrom;
            var waypoint1 = newTo;

            getReverseGeoDirectionCode(source_latitude,source_longitude,dest_latitude,dest_longitude,waypoint0,waypoint1,function(data_response){
                    //if((data_response.Action==0 && data_response.message=="LBL_DEST_ROUTE_NOT_FOUND") || data_response.status=="ZERO_RESULTS") {
                    //} else {



                        if (MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() == 'NONE'){
            $("#distance").val(data_response.routes[0].legs[0].distance.value);
            $("#duration").val(data_response.routes[0].legs[0].duration.value);
            var points = data_response.routes[0].overview_polyline.points;
            console.log(points);
            var polyPoints = google.maps.geometry.encoding.decodePath(points);
            // var polyPoints = data_response;
            directionsDisplay.setMap(null);
            directionsDisplay.setMap(map);
            directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
            createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
            points = '';
            data_response = [];
            polyPoints = '';
            temp_points = '';
            }else{
            // removePolyLine();
            $("#distance").val(data_response.distance);
            $("#duration").val(data_response.duration);
            var polyLinesArr = new Array();
            var i;

            if((data_response.data != 'undefined') && (data_response.data != undefined)){
            for (i = 0; i < (data_response.data).length; i++) {
            polyLinesArr.push({ lat: parseFloat(data_response.data[i].latitude), lng: parseFloat(data_response.data[i].longitude) });
            }
            var polyPoints = polyLinesArr;
            directionsDisplay.setMap(null);
            directionsDisplay.setMap(map);
            directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
            createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
            data_response = [];
            polyPoints = '';
            }
            }
                    //}
            });

                }
            }
            $('.map-page').show();
            var tEndLat1 = '<?= $tDestinationLatitude; ?>';
            var tEndLong1 = '<?= $tDestinationLongitude; ?>';
            var latdrv = '<?= $vLatitude; ?>';
            var longdrv = '<?= $vLongitude; ?>';
            var latdrvSource = '<?= $vLatitudeSource; ?>';
            var longdrvSource = '<?= $vLongitudeSource; ?>';

            setTimeout(function() {
                GOOGLE_MAP_OBJ.options.center = new google.maps.LatLng(parseFloat(latdrv), parseFloat(longdrv));
                GOOGLE_MAP_OBJ.options.zoom = 14;
                map = GOOGLE_MAP_OBJ.init('map-canvas');

                latlng = new google.maps.LatLng(latdrv, longdrv);
                latlngSource = new google.maps.LatLng(latdrvSource, longdrvSource);
                latlngdest = new google.maps.LatLng(tEndLat1, tEndLong1);

                var statuscode = '<?= $iStatusCode; ?>';
                if (statuscode==1 || statuscode==2) {
                } else {
                marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                    icon: {
                        url: '<?= $siteUrl; ?>webimages/upload/mapmarker/middle.png',
                        //scaledSize: new google.maps.Size(50, 50),
                        rotation: 90
                    },
                    id: 'marker'
                });
                }
                marker1 = new google.maps.Marker({
                    position: latlngdest,
                    map: map,
                    icon: {
                        url: '<?= $siteUrl; ?>webimages/upload/mapmarker/destination_marker.png',
                        scaledSize: new google.maps.Size(50, 50),
                        rotation: 90
                    },
                    id: 'marker1'
                });

                marker2 = new google.maps.Marker({
                    position: latlngSource,
                    map: map,
                    icon: {
                        url: '<?= $siteUrl; ?>webimages/upload/mapmarker/source_marker_store.png',
                        scaledSize: new google.maps.Size(50, 50),
                        rotation: 90
                    },
                    id: 'marker'
                });

                if (statuscode == 6 || statuscode == 8) {
                } else {
                    routeDirections(latdrv, longdrv, tEndLat1, tEndLong1);
                }
            }, 1000);


            $(document).on('click','[data-dismiss="modal"]',function(e){
                e.preventDefault();
                $(this).closest('.custom-modal-main').removeClass('active');
                $('body').css('overflow', 'auto');
            });

            $(document).on('keydown', 'body', function(e){
                if (e.which==27){
                   $('.custom-modal-main').removeClass('active');
                   $('.modal-backdrop').remove();
                   $('body').css('overflow', 'auto');
                }
            });

            $(document).on('click','[data-toggle="modal"]',function(e){
                e.preventDefault();
                var data_target = $(this).attr('data-target');
                $('.custom-modal-main').removeClass('active');
                $(document).find(data_target).addClass('active');
            });



            <?php if(($db_order_data['fTipAmount'] == 0 && in_array($db_order_data['iStatusCode'], [1,2,4,5]) && $sessionUserType == 'rider' && $db_order_data['ePaymentOption'] == "Card" && $takeaway == 'No' && $MODULES_OBJ->isEnableDeliveryTipFeatureDeliverAll() && $db_order_data['eBuyAnyService'] == "No") || ($db_order_data['fTipAmount'] == 0 && $db_order_data['iStatusCode'] == 6 && $sessionUserType == 'rider' && $db_order_data['ePaymentOption'] == "Card" && $takeaway == 'No' && $MODULES_OBJ->isEnableDeliveryTipFeatureDeliverAll() && $eDriverPaymentStatus == "Unsettelled" && strtolower(ENABLE_DELIVERY_TIP_IN_HISTORY) == 'yes' && $db_order_data['eBuyAnyService'] == "No")) { ?>
                $('[name="tip_amount"]').click(function() {
                    var tip_amount = $(this).val();
                    $('#tip_amount_collect').val(tip_amount);
                    $('[name="tip_amount"]').closest('div').find('.remove-default-tip').hide();
                    $('[name="tip_amount"]:checked').closest('div').find('.remove-default-tip').show();

                    if($(this).val() == "other")
                    {
                        $(this).prop('checked', false);
                        $(this).closest('.tip-amount-block').fadeOut('fast', function() {
                            $('.tip-amount-block-input').fadeIn('fast');
                            $('#tip_amount_collect').val("");
                            $('#tip_amount_collect').closest('.numberPlay').focus();
                            $('#tip_amount_collect').closest('div').find('.numberPlay').focus();
                            $('#tip_amount_collect').focus();
                        });
                    }
                    else {
                        $('.tip-amount-block-input').fadeOut('fast', function() {
                            $('.tip-amount-block-other').find('label').html("<?= $langage_lbl['LBL_OTHER_TXT'] ?>");
                            $('.tip-amount-block-other').fadeIn('fast');
                        });
                    }
                });

                $('#tip_amount_collect').keydown(function (e) {
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                            (e.keyCode == 65 && e.ctrlKey === true) ||
                            (e.keyCode == 67 && e.ctrlKey === true) ||
                            (e.keyCode == 88 && e.ctrlKey === true) ||
                            (e.keyCode >= 35 && e.keyCode <= 39)) {
                        return;
                    }
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                $('#tip_amount_collect').on('input', function() {

                    var tip_amount_collect = $(this).val();

                });

                $('#tip_amount_collect').blur(function() {

                    if($(this).val() == "" || $(this).val() <= 0 || typeof $('#tip_amount_collect').attr('formatted-value') === 'undefined' || $('#tip_amount_collect').attr('formatted-value') == "") {
                        $(this).val("");
                        $('.remove-default-tip').hide();
                        $('.tip-amount-block-input').fadeOut('fast', function() {
                            $('.tip-amount-block-other').fadeIn();
                        });

                        $('#tip_amount_4').prop('checked', false);
                        $('.tip-amount-block-other').find('label').html("<?= $langage_lbl['LBL_OTHER_TXT'] ?>");
                    } else {
                        $('.tip-amount-block-input').fadeOut('fast', function() {
                            $('.tip-amount-block-other').find('label').html('<?= $currencySymbol.' '; ?>' + $('#tip_amount_collect').attr('formatted-value'));
                            $('#tip_amount_4').prop('checked', true);
                            $('.tip-amount-block-other').fadeIn();
                        });
                    }



                  /*  if($(this).val() == "" || $(this).val() <= 0 || $('#tip_amount_collect').attr('formatted-value') == 'undefined')
                    {
                        $(this).val("");
                        $('.remove-default-tip').hide();
                        $('.tip-amount-block-input').fadeOut('fast', function() {
                            $('.tip-amount-block-other').fadeIn();
                        });

                        $('#tip_amount_4').prop('checked', false);
                        $('.tip-amount-block-other').find('label').html("<?= $langage_lbl['LBL_OTHER_TXT'] ?>");
                    }
                    else {
                        $('.tip-amount-block-input').fadeOut('fast', function() {
                            $('.tip-amount-block-other').find('label').html('<?= $currencySymbol.' '; ?>' + $('#tip_amount_collect').attr('formatted-value'));
                            $('#tip_amount_4').prop('checked', true);
                            $('.tip-amount-block-other').fadeIn();
                        });
                    }*/
                });

                $('.remove-default-tip').click(function() {
                    $('#tip_amount_collect').val("");
                    $(this).hide();
                    $(this).closest('div').find('input').prop('checked', false);
                    $('.tip-amount-block-other').find('label').html("<?= $langage_lbl['LBL_OTHER_TXT'] ?>");
                });

                $('.remove-tip-amount').click(function() {
                    $('#tip_amount_collect').val("");

                    $('.tip-amount-block-input').fadeOut('fast', function() {
                        $('.tip-amount-block-other').fadeIn('fast');
                    });
                    $('.remove-default-tip').hide();

                    $('#tip_amount_4').prop('checked', false);
                });

                $('#add_tip_btn').click(function() {
                    if($('#tip_amount_collect').val() == "")
                    {
                        alert('<?= $langage_lbl['LBL_TIP_AMOUNT'].' '.$langage_lbl['LBL_REQUIRED'] ?>');
                        $('#tip_amount_collect').focus();
                        return false;
                    }

                    if($('#tip_amount_collect').val() <= 0)
                    {
                        alert('Entered amount must be greater than 0.');
                        $('#tip_amount_collect').val("");
                        $('#tip_amount_collect').focus();
                        return false;
                    }

                    addOrderTipAmount();
                });

                function addOrderTipAmount()
                {
                    var selectedTipPos = $('[name="tip_amount"]:checked').attr('id');
                    if(jQuery.inArray(selectedTipPos, ['tip_amount_1', 'tip_amount_2', 'tip_amount_3', 'tip_amount_4']) !== -1) {
                        selectedTipPos = selectedTipPos.replace('tip_amount_', '');
                    }
                    else {
                        selectedTipPos = 0;
                    }
                    var tipAmount = $('#tip_amount_collect').val();

                    var data = {

                        "tSessionId": '<?=$userData[0]['tSessionId']?>',

                        "GeneralMemberId": '<?= $db_order_data['iUserId'] ?>',

                        "GeneralUserType": 'Passenger',

                        "type": 'OrderCollectTip',

                        "iOrderId": '<?=$iOrderId?>',

                        "fTipAmount": tipAmount,

                        "selectedTipPos": selectedTipPos,

                        "SYSTEM_TYPE": "WEB",

                        "eSystem": "DeliverAll",

                        "cancelUrl": encodeURIComponent(window.location.href)
                    };

                    $('#delivery_tip_section').find('.overlay').show();

                    // Added and commented by HV on 09-11-2020 as discussed with KS
                    getDataFromApi(data, function(response) {
                        var response = JSON.parse(response);

                        if(response.Action == '1') {
                            window.location.href = response.TIP_PAYMENT_URL;
                        } else {
                            alert(response.message1);
                            $('#delivery_tip_section').find('.overlay').hide();
                        }
                    });

                    // Added and commented by HV on 09-11-2020 as discussed with KS End
                }
            <?php } ?>





        </script>

        <?php if($currencyData['eReverseformattingEnable'] == "Yes"){ ?>
            <script>
                const numberFormatter = new NumberFormatter('.', ',', 2);
                numberFormatter.initializeNumberFormatting('.numberPlay');
            </script>
        <?php }else{ ?>
            <script>
                const numberFormatter = new NumberFormatter(',', '.', 2);
                numberFormatter.initializeNumberFormatting('.numberPlay');
            </script>

        <?php } ?>


    </body>
</html>
