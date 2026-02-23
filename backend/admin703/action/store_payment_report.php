<?php
include_once('../../common.php');
ob_clean();



$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['actionpayment']) ? $_REQUEST['actionpayment'] : 'view';
$ePayRestaurant = isset($_REQUEST['ePayRestaurant']) ? $_REQUEST['ePayRestaurant'] : '';

if($action == "pay_restaurant" && $_REQUEST['ePayRestaurant'] == "Yes"){
	if(SITE_TYPE !='Demo'){
		$iOrderId = $_REQUEST['iOrderId'];
		for($k=0;$k<scount($iOrderId);$k++){
            $settledIOrderId[] = $iOrderId[$k];


            $query = "UPDATE orders SET eRestaurantPaymentStatus = 'Settled' WHERE iOrderId = '" .$iOrderId[$k]. "'";
            $obj->sql_query($query);


			/* $sql = "SELECT ePaymentDriverStatus from payments WHERE iOrderId = '".$iOrderId[$k]."' and ePaymentDriverStatus = 'UnPaid'";
			 $db_pay = $obj->MySQLSelect($sql);
			 if(scount($db_pay) > 0){
			   $query = "UPDATE payments SET ePaymentDriverStatus = 'Paid' WHERE iOrderId = '" .$iOrderId[$k]. "'";
			   $obj->sql_query($query);

			   $query = "UPDATE trips SET eDriverPaymentStatus = 'Settelled', ePayment_request = 'Yes' WHERE iOrderId = '" .$iOrderId[$k]. "'";
			   $obj->sql_query($query);
			 }else{*/
			/* }*/


		}
		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = 'Record(s) mark as settlled successful.'; 
	}else {
		$_SESSION['success'] = '2';
	}

    settlementMailSent($settledIOrderId , 'OrderStore');

	header("Location:".$tconfig["tsite_url_main_admin"]."store_payment_report.php?".$parameters); exit;
}
?>