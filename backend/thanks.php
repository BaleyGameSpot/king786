<?php
include_once("common.php");

$order_id = $_REQUEST['orderid'];
$order_id = base64_encode(base64_encode(trim($_REQUEST['orderid'])));
//invoice_deliverall.php?iOrderId=T1Rreg==
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
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
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_".strtoupper($fromOrder);
unset($_SESSION[$orderDetailsSession]);
unset($_SESSION[$userSession]);
unset($_SESSION[$orderUserSession]);
unset($_SESSION[$orderServiceSession]);
unset($_SESSION[$orderUserIdSession]);
unset($_SESSION[$orderAddressIdSession]);
unset($_SESSION[$orderCouponSession]);
unset($_SESSION[$orderCouponNameSession]);

unset($_SESSION[$orderCurrencyNameSession]);

unset($_SESSION[$orderLatitudeSession]);
unset($_SESSION[$orderLongitudeSession]);
unset($_SESSION[$orderAddressSession]);
unset($_SESSION[$orderDataSession]);

unset($_SESSION[$orderUserNameSession]);
unset($_SESSION[$orderCompanyNameSession]);
unset($_SESSION[$orderUserEmailSession]);
unset($_SESSION[$orderStoreIdSession]);
unset($_SESSION[$orderServiceNameSession]);


//payment gateway start
$cancelled = 0;
if (isset($_REQUEST['cancelled']) && $_REQUEST['cancelled'] == "true") {
    $cancelled = 1;
}
if ($_REQUEST['orderid'] && $_REQUEST['success'] == 1 && $cancelled == 0) {
    $ordrId = base64_decode(base64_decode(trim($order_id)));

    $queryOder = 'SELECT iOrderId FROM orders WHERE iOrderId = "' . $ordrId . '" AND iStatusCode != "1"';
    $unPlacedOrder = $obj->MySQLSelect($queryOder);


    if (scount($unPlacedOrder) > 0) {
        $sql1 = "UPDATE orders SET iStatusCode = '1' WHERE iOrderId = '" . $ordrId . "'";
        $db_company = $obj->sql_query($sql1);

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
    }
}
//payment gateway end
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?php echo $langage_lbl['LBL_MANUAL_STORE_THANK_YOU']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <link href="assets/css/apptype/<?= $template ?>/custom-order/OverlayScrollbars.css" rel="stylesheet">
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <?php include_once("top/validation.php"); ?>
        <!-- End: Default Top Script and css--> 
        <script>
            $(document).ready(function () {
                setTimeout(function () {
                   window.location.href = "<?php echo $tconfig["tsite_url"]; ?>invoice_deliverall.php?action=manual&iOrderId=<?php echo $order_id; ?>";
                }, 5000);
            });
        </script>

    </head>
    <body>
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- home page -->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant page-contant-new">
                <div class="page-contant-inner clearfix">

                    <!-- trips detail page -->
		<?php if($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
		<div class="thanks-holder">
			<div class="thanks-caption">
                    <?php } else { ?>
		    <div class="static-page-a">
			<div class="static-page-aa">
		    <?php } ?>
                            <img src="assets/img/custome-store/checked.svg" alt="" />
                        </div>
                        <h2> <?php echo $langage_lbl['LBL_MANUAL_STORE_THANK_YOU']; ?>!</h2>
                        <p><?php echo $langage_lbl['LBL_MANUAL_STORE_THANK_YOU_ORDER_PLACE_ORDER']; ?></p>
                    </div>
                </div>
            </div>
            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
	      <?php include_once('top/footer_script.php'); ?>
    </body>
</html>
<?php 
if($_SESSION[$orderDetailsSession]){
      unset($_SESSION[$orderDetailsSession]);
        unset($_SESSION[$userSession]);
        unset($_SESSION[$orderUserSession]);
        unset($_SESSION[$orderServiceSession]);
        unset($_SESSION[$orderUserIdSession]);
        unset($_SESSION[$orderAddressIdSession]);
        unset($_SESSION[$orderCouponSession]);
        unset($_SESSION[$orderCouponNameSession]);

        unset($_SESSION[$orderCurrencyNameSession]);
        //unset($_SESSION['sess_currentpage_url_mr']);
        unset($_SESSION[$orderLatitudeSession]);
        unset($_SESSION[$orderLongitudeSession]);
        unset($_SESSION[$orderAddressSession]);
        unset($_SESSION[$orderDataSession]);

        unset($_SESSION[$orderUserNameSession]);
        unset($_SESSION[$orderCompanyNameSession]);
        unset($_SESSION[$orderUserEmailSession]);
        unset($_SESSION[$orderStoreIdSession]);
        unset($_SESSION[$orderServiceNameSession]);
}
?>