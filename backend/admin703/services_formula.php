<?php
$ATTR['TOTAL_FARE']['text'] = 'Total Fare';
$ATTR['TOTAL_FARE']['latter'] = 'A';
$ATTR['TOTAL_FARE']['FILED_NAME'] = $ATTR['TOTAL_FARE']['latter'].'='.$ATTR['TOTAL_FARE']['text'];
$ATTR['TOTAL_FARE']['DISC'] = 'The total amount paid by the user for the '.$langage_lbl_admin['LBL_TRIP_TXT_ADMIN'].'.';
$ATTR['CASH_RECEIVED']['text'] = 'Cash Received';
$ATTR['CASH_RECEIVED']['latter'] = 'B';
$ATTR['CASH_RECEIVED']['FILED_NAME'] = $ATTR['CASH_RECEIVED']['latter'].'='.$ATTR['CASH_RECEIVED']['text'];
$ATTR['CASH_RECEIVED']['DISC'] = 'The total amount paid by the user in cash.';
$ATTR['COMMISSION_AMOUNT']['text'] = 'Commission Amount';
$ATTR['COMMISSION_AMOUNT']['latter'] = 'C';
$ATTR['COMMISSION_AMOUNT']['FILED_NAME'] = $ATTR['COMMISSION_AMOUNT']['latter'].'='.$ATTR['COMMISSION_AMOUNT']['text'];
$ATTR['COMMISSION_AMOUNT']['DISC'] = 'The amount get by the admin from the commission.';
$ATTR['TOTAL_TAX']['text'] = 'Total Tax';
$ATTR['TOTAL_TAX']['latter'] = 'D';
$ATTR['TOTAL_TAX']['FILED_NAME'] = $ATTR['TOTAL_TAX']['latter'].'='.$ATTR['TOTAL_TAX']['text'];
$ATTR['TOTAL_TAX']['DISC'] = 'The total amount of tax applied to the '.$langage_lbl_admin['LBL_TRIP_TXT_ADMIN'].'.';
if (in_array(1,$enableTipCol)){

    $ATTR['TIP']['text'] = 'Tip';
    $ATTR['TIP']['latter'] = 'E';
    $ATTR['TIP']['FILED_NAME'] = $ATTR['TIP']['latter'].'='.$ATTR['TIP']['text'];
    $ATTR['TIP']['DISC'] = 'An additional amount paid by the user as a gratuity for the driver.';
}
$ATTR['TIP_OUTSTANDING_AMOUNT']['text'] = $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'].' Outstanding Amount';
$ATTR['TIP_OUTSTANDING_AMOUNT']['latter'] = 'F';
$ATTR['TIP_OUTSTANDING_AMOUNT']['FILED_NAME'] = $ATTR['TIP_OUTSTANDING_AMOUNT']['latter'].'='.$ATTR['TIP_OUTSTANDING_AMOUNT']['text'];
$ATTR['TIP_OUTSTANDING_AMOUNT']['DISC'] = 'The remaining balance due for the '.$langage_lbl_admin['LBL_TRIP_TXT_ADMIN'].'.';
if ($hotelPanel > 0 || $kioskPanel > 0){
    $ATTR['BOOKING_FEES']['text'] = 'Booking Fees';
    $ATTR['BOOKING_FEES']['latter'] = 'G';
    $ATTR['BOOKING_FEES']['FILED_NAME'] = $ATTR['BOOKING_FEES']['latter'].'='.$ATTR['BOOKING_FEES']['text'];
}
$ATTR['BOOKING_FEES']['DISC'] = 'The fee charged for booking the service. This booking fee is collected from the user when the trip is made through the kiosk app.';
$ATTR['PROVIDER_PAY_TAKE_AMOUNT']['text'] = 'Provider Pay / Take Amount';
$ATTR['PROVIDER_PAY_TAKE_AMOUNT']['latter'] = 'H';
$ATTR['PROVIDER_PAY_TAKE_AMOUNT']['FILED_NAME'] = $ATTR['PROVIDER_PAY_TAKE_AMOUNT']['latter'].'='.$ATTR['PROVIDER_PAY_TAKE_AMOUNT']['text'];
$ATTR['PROVIDER_PAY_TAKE_AMOUNT']['DISC'] = 'The amount pay or take to the provider after the service is completed.';
$ATTR['SITE_EARNING']['text'] = 'Site Earning';
$ATTR['SITE_EARNING']['latter'] = 'I';
$ATTR['SITE_EARNING']['FILED_NAME'] = $ATTR['SITE_EARNING']['latter'].'='.$ATTR['SITE_EARNING']['text'];
$ATTR['SITE_EARNING']['DISC'] = 'The amount earned by the site from the transaction.';
$ATTR['DRIVER_EARN_AMOUNT_TEXT'] = "Driver earn amount";
$ATTR['SITE_EARNING_TEXT'] = "Site Earning";
$ATTR['DRIVER_PAY_AMOUNT_TEXT'] = "Driver Pay Amount";
$ATTR['DRIVER_TAKE_AMOUNT_TEXT'] = "Take Amount";
$ATTR['CASH_RECEIVED_TEXT'] = "Cash Received";
?>

<!----------------------------------->
<?php function formula()
{
    global $ATTR;
    ?>

    <div>

        <p><b> The following formula calculates both the driver's earnings and the site's earnings. </b></p>
        <p>
            <?php
            if (isset($ATTR) && !empty($ATTR)){
                foreach ($ATTR as $A){
                    if (isset($A['FILED_NAME']) && !empty($A['FILED_NAME'])){
                        echo $A['FILED_NAME'].';  ';
                    }
                }
            }
            ?>

        </p>
        <p>
            <b><?=$ATTR['DRIVER_EARN_AMOUNT_TEXT'];?> </b> = ( <?=$ATTR['TOTAL_FARE']['latter'];?> <?=isset($ATTR['TIP']['latter'])?' + '.$ATTR['TIP']['latter']:'';?> ) - (<?=$ATTR['COMMISSION_AMOUNT']['latter'];?> + <?=$ATTR['TOTAL_TAX']['latter'];?> + <?=$ATTR['TIP_OUTSTANDING_AMOUNT']['latter'];?>  <?=isset($ATTR['BOOKING_FEES']['latter'])?' + '.$ATTR['BOOKING_FEES']['latter']:'';?>)
        </p>
        <p>
            <b> <?=$ATTR['SITE_EARNING_TEXT'];?> </b> = (<?=$ATTR['TOTAL_FARE']['latter'];?> -
            <b> <?=$ATTR['DRIVER_EARN_AMOUNT_TEXT'];?></b>)
        </p>
    </div>

    <br>
    <p><b> The following formula calculates the driver's settlement amount based on the payment method.</b></p>

    <ul>

        <li>
            <p> Payment Mode: Card, Wallet </p>
            <p> <?=$ATTR['DRIVER_PAY_AMOUNT_TEXT'];?> = <b> <?=$ATTR['DRIVER_EARN_AMOUNT_TEXT'];?></b></p>
            <p> <?=$ATTR['DRIVER_TAKE_AMOUNT_TEXT'];?> = 0 </p>
        </li>
        <li>
            <p> Payment Mode: Cash </p>
            <p> <?=$ATTR['DRIVER_PAY_AMOUNT_TEXT'];?> = 0 </p>
            <p> <?=$ATTR['DRIVER_TAKE_AMOUNT_TEXT'];?> =
                (<b> <?=$ATTR['DRIVER_EARN_AMOUNT_TEXT'];?></b> - <?=$ATTR['CASH_RECEIVED']['latter'];?>) </p>
        </li>
    </ul>

<?php } ?>

