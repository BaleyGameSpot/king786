<?php
include_once('../../common.php');




$reload = $_SERVER['REQUEST_URI'];
// echo "<pre>"; print_r($_REQUEST); die;

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$ePayDriver = isset($_REQUEST['ePayDriver']) ? $_REQUEST['ePayDriver'] : '';

if($action == "pay_driver" && $_REQUEST['ePayDriver'] == "Yes"){

    $ssql = " AND eSystem = 'General'";
    if($MODULES_OBJ->isDeliverAllFeatureAvailable()) {

        $ssql = " AND (eSystem = 'General' OR eSystem = 'DeliverAll') AND iServiceId = '0'";
    }

    
	if(SITE_TYPE !='Demo'){
		foreach($_REQUEST['iDriverId'] as $ids) {

            $trip_data = $obj->MySQLSelect("SELECT vRideNo,tTripRequestDate,iDriverId,iTripId FROM `trips` WHERE iDriverId = '".$ids."' AND eDriverPaymentStatus='Unsettelled' $ssql ");


			$sql1 = " UPDATE trips set eDriverPaymentStatus = 'Settelled'
			WHERE iDriverId = '".$ids."' AND eDriverPaymentStatus='Unsettelled' $ssql";
			$obj->sql_query($sql1);

            $iTripId = array_column($trip_data, "iTripId");

            settlementMailSent($iTripId , 'Ride');
		}
		//echo "<pre>";print_r($db_payment1);exit;
		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = 'Record(s) marked as settled successfully.'; 
	}else {
		$_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."driver_pay_report.php?".$parameters); exit;
}
?>