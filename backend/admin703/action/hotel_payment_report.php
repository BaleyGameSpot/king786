<?php
include_once('../../common.php');
ob_clean();

$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['actionpayment']) ? $_REQUEST['actionpayment'] : 'view';
$ePayDriver = isset($_REQUEST['ePayDriver']) ? $_REQUEST['ePayDriver'] : '';

if($action == "pay_driver" && $_REQUEST['ePayDriver'] == "Yes"){
	if(SITE_TYPE !='Demo'){
		$iTripId = $_REQUEST['iTripId'];
		for($k=0;$k<scount($iTripId);$k++)
        {
            $iTripIds[] = $iTripId[$k];

		   $query = "UPDATE trips SET eHotelPaymentStatus = 'Settelled', ePayment_request = 'Yes' WHERE iTripId = '" .$iTripId[$k]. "'";
		   $obj->sql_query($query);
		}

        settlementMailSent($iTripIds , 'Hotel');

		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = 'Record(s) mark as settlled successful.'; 
	}else {
		$_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."hotel_payment_report.php?".$parameters); exit;
}
?>