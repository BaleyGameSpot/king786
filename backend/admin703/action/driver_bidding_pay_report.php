<?php
include_once('../../common.php');
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$ePayDriver = isset($_REQUEST['ePayDriver']) ? $_REQUEST['ePayDriver'] : '';

if($action == "pay_driver" && $_REQUEST['ePayDriver'] == "Yes"){
    if(SITE_TYPE !='Demo'){
        foreach($_REQUEST['iDriverId'] as $ids) {
            $bidding_post = $obj->MySQLSelect("SELECT iBiddingPostId FROM `bidding_post` WHERE iDriverId = '".$ids."' AND eDriverPaymentStatus='Unsettelled'");

            $sql1 = " UPDATE bidding_post set eDriverPaymentStatus = 'Settelled'
			WHERE iDriverId = '".$ids."' AND eDriverPaymentStatus='Unsettelled'";
            $obj->sql_query($sql1);

            $iBiddingPostId = array_column($bidding_post, "iBiddingPostId");
            settlementMailSent($iBiddingPostId , 'PostBidding');
        }
        //echo "<pre>";print_r($db_payment1);exit;
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Record(s) marked as settled successfully.';
    }else {
        $_SESSION['success'] = '2';
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."driver_bidding_pay_report.php?".$parameters); exit;
}
?>