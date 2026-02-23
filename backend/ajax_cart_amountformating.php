<?php
include_once("common.php");


$fullprice = isset($_REQUEST['fullprice']) ? $_REQUEST['fullprice'] : '';
$CurrencyCode = isset($_REQUEST['CurrencyCode']) ? $_REQUEST['CurrencyCode'] : '';

$fullprice = formateNumAsPerCurrency($fullprice,$CurrencyCode);
echo $fullprice;
?>