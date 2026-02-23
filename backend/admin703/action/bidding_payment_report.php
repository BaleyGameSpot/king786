<?php

include_once('../../common.php');
ob_clean();



$reload = $_SERVER['REQUEST_URI'];
// echo "<pre>"; print_r($_REQUEST); die;

$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['actionpayment']) ? $_REQUEST['actionpayment'] : 'view';
$ePayDriver = isset($_REQUEST['ePayDriver']) ? $_REQUEST['ePayDriver'] : '';

$fieldName = 'eDriverPaymentStatus';

if ($action == "pay_driver" && $_REQUEST['ePayDriver'] == "Yes") {
    if (SITE_TYPE != 'Demo') {
        $iBiddingPostId = $_REQUEST['iBiddingPostId'];
        for ($k = 0; $k < scount($iBiddingPostId); $k++) {

            $settlediBiddingPostId[] = $iBiddingPostId[$k];

            $query = "UPDATE bidding_post SET $fieldName = 'Settelled' WHERE iBiddingPostId = '" . $iBiddingPostId[$k] . "'";
            $obj->sql_query($query);
        }
        settlementMailSent($settlediBiddingPostId , 'PostBidding');
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Record(s) marked as settled successfully.';
    } else {
        $_SESSION['success'] = '2';
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "bidding_payment_report.php?" . $parameters);
    exit;
}
?>