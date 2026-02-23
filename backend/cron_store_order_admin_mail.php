<?php

//Create By HJ On 22-10-2019 For Send mail to admin when Not Accept Order of Delivery Driver Addon
include_once("common.php");

if ($ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes") {

    $getOrderData = $obj->MySQLSelect("SELECT vOrderNo,dCronExpiredDate,iOrderId FROM orders WHERE dCronExpiredDate != '0' AND iStatusCode = '2' AND eSentMailAdmin='No' AND tDriverIds != ''");

    if (scount($getOrderData) > 0) {
        for ($i = 0; $i < scount($getOrderData); $i++) {
            $currentTime = time();
            $dCronExpiredDate = $getOrderData[$i]['dCronExpiredDate'];
            $vOrderNo = $getOrderData[$i]['vOrderNo'];
            if ($currentTime > $dCronExpiredDate) {
                //echo $currentTime . "===" . $dCronExpiredDate;die;
                sendMailToAdmin($vOrderNo);
                $obj->sql_query("UPDATE orders SET eSentMailAdmin ='Yes' WHERE iOrderId=" . $getOrderData[$i]['iOrderId']);
            }
        }
    }
}
?>