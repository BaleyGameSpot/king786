<?php
include_once('../../common.php');
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$ePayDriver = isset($_REQUEST['ePayDriver']) ? $_REQUEST['ePayDriver'] : '';
if ($action == "pay_driver" && $ePayDriver == "Yes") {
    if (SITE_TYPE != 'Demo') {
        $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
        $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
        $ssql = '';
        if ($startDate != '') {
            $ssql .= " AND Date(tTripRequestDate) >='" . $startDate . "'";
        }
        if ($endDate != '') {
            $ssql .= " AND Date(tTripRequestDate) <='" . $endDate . "'";
        }
        $ssql .= " AND eDriverPaymentStatus='Unsettelled' AND eSystem = 'DeliverAll'";
        foreach ($_REQUEST['iDriverId'] as $ids) {

            $trip_data = $obj->MySQLSelect("SELECT iTripId FROM `trips` WHERE iDriverId = '" . $ids . "' AND eDriverPaymentStatus='Unsettelled' $ssql");

            $sql1 = " UPDATE trips set eDriverPaymentStatus = 'Settelled'
			WHERE iDriverId = '" . $ids . "' AND eDriverPaymentStatus='Unsettelled' $ssql";
            $obj->sql_query($sql1);

            $iTripId = array_column($trip_data, "iTripId");
            settlementMailSent($iTripId , 'OrderDriver');

        }
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Record(s) mark as settlled successful.';
    } else {
        $_SESSION['success'] = '2';
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "store_driver_pay_report.php?" . $parameters);
    exit;
}
?>