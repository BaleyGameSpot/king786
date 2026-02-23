<?php
include_once('../../common.php');

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?',$reload);
$parameters = $urlparts[1];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$ePayRestaurant = isset($_REQUEST['ePayRestaurant']) ? $_REQUEST['ePayRestaurant'] : '';
if($action == "pay_restaurant" && $_REQUEST['ePayRestaurant'] == "Yes"){
	if(SITE_TYPE !='Demo'){
        $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
        $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
        $ssql = '';
        if($startDate!=''){
            $ssql.=" AND Date(tOrderRequestDate) >='".$startDate."'";
        }
        if($endDate!=''){
            $ssql.=" AND Date(tOrderRequestDate) <='".$endDate."'";
        }
		foreach($_REQUEST['iCompanyId'] as $ids) {
            $orders_data = $obj->MySQLSelect("SELECT iOrderId FROM `orders` WHERE iCompanyId = '".$ids."' AND eRestaurantPaymentStatus='Unsettled' $ssql ");
            $sql1 = " UPDATE orders set eRestaurantPaymentStatus = 'Settled'
			WHERE iCompanyId = '".$ids."' AND eRestaurantPaymentStatus='Unsettled' $ssql";
            $obj->sql_query($sql1);
            if(!empty($orders_data)){
	            $iOrderId = array_column($orders_data, "iOrderId");
	            settlementMailSent($iOrderId , 'OrderStore');
            }
		}
		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = 'Record(s) mark as settlled successful.'; 
	}else {
		$_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."restaurants_pay_report.php?".$parameters); exit;
}
?>