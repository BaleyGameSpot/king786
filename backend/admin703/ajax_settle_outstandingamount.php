<?php

include_once('../common.php');
// ob_clean();



$reload = $_SERVER['REQUEST_URI'];


$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$action = isset($_REQUEST['actionpayment']) ? $_REQUEST['actionpayment'] : '';
$userId = isset($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
$unsettledUser = isset($_REQUEST['unsettledUser']) ? $_REQUEST['unsettledUser'] : '';
$searchPaidby = isset($_REQUEST['searchPaidby']) ? $_REQUEST['searchPaidby'] : '';

 // echo "UPDATE trip_outstanding_amount SET ePaidByPassenger='Yes' WHERE iUserId=".$userId." AND ePaidByPassenger='No'";exit;
if ($action == "pay_user") {
    // if (SITE_TYPE != 'Demo') {
    if($searchPaidby=='org') {
        foreach($unsettledUser as $ids) {
			//$obj->sql_query("UPDATE trip_outstanding_amount SET ePaidByOrganization='Yes',ePaidByAdmin='Yes' WHERE iUserId=".$ids." AND ePaidByOrganization='No'");
			$obj->sql_query("UPDATE trip_outstanding_amount SET ePaidByOrganization='Yes',ePaidByAdmin='Yes' WHERE iOrganizationId=".$ids." AND ePaidByOrganization='No'");
		}
    } else {
        foreach($unsettledUser as $ids) {
			$obj->sql_query("UPDATE trip_outstanding_amount SET ePaidByPassenger='Yes',ePaidByAdmin='Yes' WHERE iUserId=".$ids." AND ePaidByPassenger='No'");
		}
    }
        //$obj->sql_query("UPDATE trip_outstanding_amount SET ePaidByPassenger='Yes' WHERE iUserId=".$userId." AND ePaidByPassenger='No'");
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Record(s) marked as settled successfully.';
    // } else {
    //     $_SESSION['success'] = '2';
    // }
    //if (isset($_REQUEST['orgpay']) && $_REQUEST['orgpay'] > 0) {
    //    header("Location:" . $tconfig["tsite_url_main_admin"] . "org_payment_report.php?" . $parameters);
    //} else {
        header("Location:" . $tconfig["tsite_url_main_admin"] . "outstanding_report.php?" . $parameters);
    //}
    exit;
}
?>      