<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-provider-bidding-payment-report')) {
    $userObj->redirect();
}

$script = 'Bidding_Payment_Report';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : 'Unsettelled';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if($action == 'export') {
    if (isset($_REQUEST['prevsearchDriver']) && !empty($_REQUEST['prevsearchDriver'])) {
        $searchDriver = isset($_REQUEST['prevsearchDriver']) ? $_REQUEST['prevsearchDriver'] : '';

    }
}
//select date start
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$Pmonday = date('Y-m-d', strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d', strtotime('sunday this week -1 week'));

//select date end

$whereDriverId = '';
$ssql = ' AND bp.eStatus = "Completed" ';

if ($searchDriver != '') {
    $ssql .= " AND bp.iDriverId ='" . $searchDriver . "'";
    $whereDriverId .= " AND iDriverId ='" . $searchDriver . "'";
}

if ($startDate != '') {
    $ssql .= " AND Date(bp.dBiddingDate) >='" . $startDate . "'";
    $whereDriverId .= " AND Date(dBiddingDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(bp.dBiddingDate) <='" . $endDate . "'";
    $whereDriverId .= " AND Date(dBiddingDate) <='" . $endDate . "'";
}

$ord = ' ORDER BY rd.iDriverId DESC';


$per_page = $DISPLAY_RECORD_NUMBER;
$sql = "SELECT 
        COUNT( DISTINCT rd.iDriverId ) AS Total 
        FROM register_driver AS rd 
        LEFT JOIN bidding_post AS bp ON bp.iDriverId=rd.iDriverId WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$tpages = $total_pages;
//page
$start = 0;
$show_page = 1;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}

//page
$sql = "SELECT rd.vBankAccountHolderName,rd.vAccountNumber,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code,rd.iDriverId,bp.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code 
                FROM register_driver AS rd 
                LEFT JOIN bidding_post AS bp ON bp.iDriverId=rd.iDriverId 
                WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql GROUP BY rd.iDriverId $ord LIMIT $start, $per_page";

$db_payment = $obj->MySQLSelect($sql);

$endRecord = scount($db_payment);
$driverPayment = [];
if (isset($db_payment) && !empty($db_payment)) {
    foreach ($db_payment as $driver_payment) {
        $driverPayment[$driver_payment['iDriverId']] = $driver_payment;
    }
    if ($action != 'export') {
        $iDriverIds = array_column($db_payment, 'iDriverId');
        $iDriverIds = implode(',', $iDriverIds);
        $whereDriverId .= " AND iDriverId IN (" . $iDriverIds . ")";
    }
}

/*------------------bidding post amount total-----------------*/
$sql_data = "SELECT fOutStandingAmount,eDriverPaymentStatus,ePaymentOption,iDriverId,fBiddingAmount,iBiddingPostId,fCommission FROM 
            bidding_post WHERE eStatus = 'Completed' AND iDriverId > 0 
            
             AND eDriverPaymentStatus='$searchDriverPayment' $whereDriverId 
             ORDER BY iBiddingPostId ASC";

$getDriverBiddingData = $obj->MySQLSelect($sql_data);

if (isset($getDriverBiddingData) && !empty($getDriverBiddingData)) {
    $BiddingPostData = [];
    foreach ($getDriverBiddingData as $BiddingData) {
        $BiddingPostData[$BiddingData['iBiddingPostId']] = $BiddingData;
    }
    $iBiddingPostIds = array_column($getDriverBiddingData, "iBiddingPostId");
    $iBiddingPostId = implode(',', $iBiddingPostIds);
    /*------------------bidding_offer-----------------*/
    $query = "SELECT amount,iBiddingPostId FROM bidding_offer WHERE `eStatus` = 'Accepted' AND iBiddingPostId IN (" . $iBiddingPostId . ") ORDER BY `IOfferId`";
    $bidding_final_offer = $obj->MySQLSelect($query);
    if (isset($bidding_final_offer) && !empty($bidding_final_offer)) {
        foreach ($bidding_final_offer as $offer) {
            $BiddingPostData[$offer['iBiddingPostId']]['fBiddingAmount'] = $offer['amount'];
        }
    }
    /*------------------bidding_offer-----------------*/
    $finalARR = [];
    foreach ($BiddingPostData as $BiddingPost) {
        $finalARR[$BiddingPost['iDriverId']][] = $BiddingPost;
    }
}
/*------------------bidding post amount total-----------------*/

if (isset($finalARR) && !empty($finalARR)) {
    foreach ($finalARR as $driver_id => $driver_payment) {
        $driver_payment = $BIDDING_OBJ->driverPaymentCal($driver_payment);
        $driverPayment[$driver_id] = array_merge($driverPayment[$driver_id],$driver_payment);
    }
}

##################### Summary #####################

$sqlAll = "SELECT rd.vBankAccountHolderName,rd.vAccountNumber,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code,rd.iDriverId,bp.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code 
                FROM register_driver AS rd 
                LEFT JOIN bidding_post AS bp ON bp.iDriverId=rd.iDriverId 
                WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql GROUP BY rd.iDriverId $ord";
$db_paymentAll = $obj->MySQLSelect($sqlAll);

$driverPaymentAll = [];
if (isset($db_paymentAll) && !empty($db_paymentAll)) {
    foreach ($db_paymentAll as $driver_paymentAll) {
        $driverPaymentAll[$driver_paymentAll['iDriverId']] = $driver_paymentAll;
    }
}

/*------------------bidding post amount total-----------------*/
$sql_dataAll = "SELECT fOutStandingAmount,eDriverPaymentStatus,ePaymentOption,iDriverId,fBiddingAmount,iBiddingPostId,fCommission FROM 
            bidding_post WHERE eStatus = 'Completed' AND iDriverId > 0 
             AND eDriverPaymentStatus='$searchDriverPayment' $whereDriverId 
             ORDER BY iBiddingPostId ASC";
$getDriverBiddingDataAll = $obj->MySQLSelect($sql_dataAll);


if (isset($getDriverBiddingDataAll) && !empty($getDriverBiddingDataAll)) {
    $BiddingPostData = [];
    foreach ($getDriverBiddingDataAll as $BiddingDataAll) {
        $BiddingPostData[$BiddingDataAll['iBiddingPostId']] = $BiddingDataAll;
    }

    $iBiddingPostIds = array_column($getDriverBiddingDataAll, "iBiddingPostId");

    $iBiddingPostId = implode(',', $iBiddingPostIds);
    /*------------------bidding_offer-----------------*/
    $query = "SELECT amount,iBiddingPostId FROM bidding_offer WHERE `eStatus` = 'Accepted' AND iBiddingPostId IN (" . $iBiddingPostId . ") ORDER BY `IOfferId`";
    $bidding_final_offer = $obj->MySQLSelect($query);

    if (isset($bidding_final_offer) && !empty($bidding_final_offer)) {
        foreach ($bidding_final_offer as $offer) {
            $BiddingPostData[$offer['iBiddingPostId']]['fBiddingAmount'] = $offer['amount'];
        }
    }
    /*------------------bidding_offer-----------------*/
    $finalARRAll = [];
    foreach ($BiddingPostData as $BiddingPost) {
        $finalARRAll[$BiddingPost['iDriverId']][] = $BiddingPost;
    }
}
/*------------------bidding post amount total-----------------*/

if (isset($finalARRAll) && !empty($finalARRAll)) {
    foreach ($finalARRAll as $driver_id => $driver_paymentAll) {
        $driver_paymentAll = $BIDDING_OBJ->driverPaymentCal($driver_paymentAll);
        $driverPaymentAll[$driver_id] = array_merge($driverPaymentAll[$driver_id],$driver_paymentAll);
    }
}

$total_cash_receivedTemp = $total_OutstandingTemp = $final_amount_pay_providerTemp = $final_amount_take_from_providerTemp = 0;
foreach ($driverPaymentAll as  $paymentall) { 
    $total_cash_receivedTemp = $total_cash_receivedTemp + $paymentall['total_cash_received'];
    $total_OutstandingTemp = $total_OutstandingTemp + $paymentall['total_Outstanding'];
    $final_amount_pay_providerTemp = $final_amount_pay_providerTemp + $paymentall['final_amount_pay_provider'];
    $final_amount_take_from_providerTemp = $final_amount_take_from_providerTemp + $paymentall['final_amount_take_from_provider'];

}

if(isset($action) && !empty($action) && $action == 'export')
{
    $header = '';

    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name" );
    $sheet->setCellValue('B1',  $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Bank Details");
    $sheet->setCellValue('C1', "Total Cash Received");
    $sheet->setCellValue('D1', "Total Outstanding Amount Take From " .$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash Bidding");
    $sheet->setCellValue('E1', "Final Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
    $sheet->setCellValue('F1', "Final Amount to take back from " .$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
    $sheet->setCellValue('G1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Payment Status");
    $i = 2;
    foreach ($driverPayment as  $payment) {
        $sheet->setCellValue('A' . $i, clearCmpName($payment['dname']));
        $sheet->setCellValue('B' . $i, "-");
        $sheet->setCellValue('C' . $i, formateNumAsPerCurrency($payment['total_cash_received'], '' ));
        $sheet->setCellValue('D' . $i,formateNumAsPerCurrency($payment['total_Outstanding'], '' ) );
        $sheet->setCellValue('E' . $i, formateNumAsPerCurrency($payment['final_amount_pay_provider'], '' ));
        $sheet->setCellValue('F' . $i, formateNumAsPerCurrency($payment['final_amount_take_from_provider'], '' ));
        $sheet->setCellValue('G' . $i, $payment['eDriverPaymentStatus']);
        $i++;
    }

    $Summary_array = array(
        "Total Cash Received" => formateNumAsPerCurrency($total_cash_receivedTemp, ''),
        "Total Outstanding Amount Take From ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." For Cash Bidding " => formateNumAsPerCurrency($total_OutstandingTemp, ''), 
        "Final Amount Pay to ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."" =>  formateNumAsPerCurrency($final_amount_pay_providerTemp, ''),
        "Final Amount to take back from ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ." " => formateNumAsPerCurrency($final_amount_take_from_providerTemp, '')
    );
    foreach ($Summary_array as $key => $value) {
        $sheet->setCellValue('F' . $i,$key);
        $sheet->setCellValue('G' . $i, $value);
        $i++;
    }
     // Auto-size columns
    foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=bid_payment_reports.xls');
    header('Cache-Control: max-age=0');
    exit;    
}

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'iDriverId') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
   
##################### Summary #####################
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Report</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <? include_once('global_files.php'); ?>
</head>
<body class="padTop53">
<div id="wrap">
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Report(Bidding)</h2>
                </div>
            </div>
            <hr/>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search"/>
                    <div>
                        <div style="float: left;">
                            <h3>Search by Date...</h3>
                        </div>
                    </div>
                    <span>
                        <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                        <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                        <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                        <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                        <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                        <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                        <a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                        <a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                    </span>
                    <span>
                        <input type="text" id="dp4" name="startDate" placeholder="From Date"
                               class="form-control" value="" readonly=""
                               style="cursor:default; background-color: #fff"/>
                        <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control"
                               value="" readonly="" style="cursor:default; background-color: #fff"/>
                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text driver_container" name='searchDriver'
                                    data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>"
                                    id="searchDriver">
                                <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                            </select>
                        </div>

                         <div class="col-lg-3">
                            <select class="form-control" name="searchDriverPayment">
                                <option value="Settelled"
                                        <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Payment
                                    Status - Settled
                                </option>
                                <option value="Unsettelled"
                                        <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Payment
                                    Status - Unsettled
                                </option>
                            </select>
                        </div>

                    </span>
                </div>
                <div class="row payment-report">

                </div>
                <div class="tripBtns001">
                <b>
                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                           title="Search"/>
                    <input type="button" value="Reset" class="btnalt button11"
                           onClick="window.location.href = 'driver_bidding_pay_report.php'"/>
                    <?php if (scount($driverPayment) > 0 && $userObj->hasPermission('export-provider-bidding-payment-report')) { ?>
                    <button type="button" onClick="exportlist()" class="export-btn001">Export</button>
                    <?php } ?>
                </b>
                </div>
            </form>
            <form name="_list_form" id="_list_form" class="_list_form table-responsive" method="post"
                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <input type="hidden" id="actionpay" name="action" value="pay_driver">
                <input type="hidden" name="ePayDriver" id="ePayDriver" value="">
                <input type="hidden" name="prev_start" id="prev_start" value="<?= $startDate ?>">
                <input type="hidden" name="prev_end" id="prev_end" value="<?= $endDate ?>">
                <input type="hidden" name="prev_order" id="prev_order" value="<?= $order ?>">
                <input type="hidden" name="prev_sortby" id="prev_sortby" value="<?= $sortby ?>">
                <input type="hidden" name="prevsearchDriver" id="prevsearchDriver" value="<?= $searchDriver ?>">
                <input type="hidden" name="prevsearchCompany" id="prevsearchCompany" value="<?= $searchCompany ?>">
                <table class="table table-striped table-bordered table-hover" id="dataTables-example123">
                    <thead>
                    <tr>
                        <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</th>
                        <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Bank Details</th>
                        <th style="text-align:center;">Total Cash Received</th>

                        <th style="text-align:center;">Total Outstanding Amount <br>Take
                            From <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For
                            Cash Bidding
                        </th>
                        <th style="text-align:center;">Final Amount Pay
                            to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></th>
                        <th style="text-align:center;">Final Amount to take back
                            from <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></th>
                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment
                            Status
                        </th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?
                    if (scount($driverPayment) > 0) { ?>
                        <?php foreach ($driverPayment as  $payment) { ?>
                            <tr class="gradeA">
                                <td>
                                    <?php echo  $payment['dname'];?>
                                </td>

                                <td align="center">
                                    <button type="button"
                                            onClick="show_driver_bank_details('<?= $payment['iDriverId']; ?>', '<?= clearCmpName($payment['dname']); ?>', '<?= clearCmpName($payment['vBankAccountHolderName']); ?>', '<?= $payment['vBankName']; ?>', '<?= clearCmpName($payment['vAccountNumber']); ?>', '<?= clearCmpName($payment['vBIC_SWIFT_Code']); ?>')"
                                            class="btn btn-success btn-xs">View Details
                                    </button>
                                </td>

                                <td align="center">
                                    <?php echo  formateNumAsPerCurrency($payment['total_cash_received'], '' );?>
                                </td>
                                <td align="center" >
                                    <?php echo  formateNumAsPerCurrency($payment['total_Outstanding'], '' );?>

                                </td>
                                <td align="center">
                                    <?php echo  formateNumAsPerCurrency($payment['final_amount_pay_provider'],'');?>
                                </td>
                                <td align="center">
                                    <?php echo  formateNumAsPerCurrency($payment['final_amount_take_from_provider'] , '' );?>
                                </td>

                                <td align="center">
                                    <?php echo  $payment['eDriverPaymentStatus'];?>
                                    <br>
                                    <a href="bidding_payment_report.php?action=search&startDate=<?= $startDate; ?>&endDate=<?= $endDate; ?>&searchDriver=<?= $payment['iDriverId']; ?>&searchDriverPayment=<?= $searchDriverPayment ?>"
                                       target="_blank"> [View Detail]
                                    </a>
                                </td>

                                <td align="center">
                                    <? if ($payment['eDriverPaymentStatus'] == 'Unsettelled') { ?>
                                        <input class="validate[required]" type="checkbox"
                                               value="<?= $payment['iDriverId'] ?>"
                                               id="iTripId_<?= $payment['iDriverId'] ?>" name="iDriverId[]">
                                    <? } ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <tr class="gradeA">
                            <td colspan="8" align="right">
                                <a href="javascript:void(0);" onClick="Paytodriver();" class="btn btn-primary">Mark As Settled</a>
                            </td>
                        </tr>
                    <? } else { ?>
                        <tr class="gradeA">
                            <td colspan="8" align="center">
                                No <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Details Found.
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>
            </form>
            <?php include('pagination_n.php'); ?>
            <!-- ##################### Summary ##################### -->
        <div class="row">
            <div class="col-lg-6 col-lg-offset-6">
                    <div class="admin-notes">
                    <h4>Summary:</h4>
                        <ul>
                            <li><strong>Total Cash Received: </strong><?= formateNumAsPerCurrency($total_cash_receivedTemp, ''); ?></li>
                            <li><strong>Total Outstanding Amount Take From <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash Bidding: </strong><?= formateNumAsPerCurrency($total_OutstandingTemp, ''); ?></li>
                            <li><strong>Final Amount Pay to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>: </strong><?= formateNumAsPerCurrency($final_amount_pay_providerTemp, ''); ?></li>
                            <li><strong>Final Amount to take back from <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>: </strong><?=formateNumAsPerCurrency($final_amount_take_from_providerTemp, ''); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- ##################### Summary ##################### -->
        </div>
        </div>
    </div>
</div>


<form name="pageForm" id="pageForm" action="action/driver_bidding_pay_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <span id="provideName"></span>
                    Bank Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <table border="1" class="table table-bordered" width="100%" align="center" cellspacing="5"
                       cellpadding="10px">
                    <tbody>
                    <tr>
                        <td class="text_design"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Account Name</td>
                        <td id="pacName"></td>
                    </tr>
                    <tr>
                        <td class="text_design">Bank Name</td>
                        <td id="pbankName"></td>
                    </tr>
                    <tr>
                        <td class="text_design">Account Number</td>
                        <td id="pacNumber"></td>
                    </tr>
                    <tr>
                        <td class="text_design">BIC/SWIFT Code</td>
                        <td id="psortcode"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons1">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>
<? include_once('footer.php'); ?>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<? include_once('searchfunctions.php'); ?>

</body>

<script>

    $('#dp4').datepicker()
        .on('changeDate', function (ev) {
            var endDate = $('#dp5').val();
            if (ev.date.valueOf() < endDate.valueOf()) {
                $('#alert').show().find('strong').text('The start date can not be greater then the end date');
            } else {
                $('#alert').hide();
                var startDate = new Date(ev.date);
                $('#startDate').text($('#dp4').data('date'));
            }
            $('#dp4').datepicker('hide');
        });
    $('#dp5').datepicker()
        .on('changeDate', function (ev) {
            var startDate = $('#dp4').val();
            if (ev.date.valueOf() < startDate.valueOf()) {
                $('#alert').show().find('strong').text('The end date can not be less then the start date');
            } else {
                $('#alert').hide();
                var endDate = new Date(ev.date);
                $('#endDate').text($('#dp5').data('date'));
            }
            $('#dp5').datepicker('hide');
        });

    $(document).ready(function () {
        $("#dp5").click(function () {
            $('#dp5').datepicker('show');
            $('#dp4').datepicker('hide');
        });

        $("#dp4").click(function () {
            $('#dp4').datepicker('show');
            $('#dp5').datepicker('hide');
        });

        if ('<?= $startDate ?>' != '') {
            $("#dp4").val('<?= $startDate ?>');
            $("#dp4").datepicker('update', '<?= $startDate ?>');
        }
        if ('<?= $endDate ?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate; ?>');
            $("#dp5").val('<?= $endDate; ?>');
        }
    });

    function todayDate() {
        $("#dp4").val('<?= $Today; ?>');
        $("#dp5").val('<?= $Today; ?>');
    }

    $("#Search").on('click', function () {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        } else {
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action + "?" + formValus;
        }
    });

    function yesterdayDate() {
        $("#dp4").val('<?= $Yesterday; ?>');
        $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
        $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
        $("#dp4").change();
        $("#dp5").change();
        $("#dp5").val('<?= $Yesterday; ?>');
    }

    function currentweekDate(dt, df) {
        $("#dp4").val('<?= $monday; ?>');
        $("#dp4").datepicker('update', '<?= $monday; ?>');
        $("#dp5").datepicker('update', '<?= $sunday; ?>');
        $("#dp5").val('<?= $sunday; ?>');
    }

    function previousweekDate(dt, df) {
        $("#dp4").val('<?= $Pmonday; ?>');
        $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
        $("#dp5").datepicker('update', '<?= $Psunday; ?>');
        $("#dp5").val('<?= $Psunday; ?>');
    }

    function currentmonthDate(dt, df) {
        $("#dp4").val('<?= $currmonthFDate; ?>');
        $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
        $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
        $("#dp5").val('<?= $currmonthTDate; ?>');
    }

    function previousmonthDate(dt, df) {
        $("#dp4").val('<?= $prevmonthFDate; ?>');
        $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
        $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
        $("#dp5").val('<?= $prevmonthTDate; ?>');
    }

    function currentyearDate(dt, df) {
        $("#dp4").val('<?= $curryearFDate; ?>');
        $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
        $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
        $("#dp5").val('<?= $curryearTDate; ?>');
    }

    function previousyearDate(dt, df) {
        $("#dp4").val('<?= $prevyearFDate; ?>');
        $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
        $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
        $("#dp5").val('<?= $prevyearTDate; ?>');
    }

    function exportlist() {
        $("#actionpay").val("export");
        var act = $("#_list_form").attr("action");
		var formValus = $("#pageForm").serialize();
        $("#_list_form").attr("action", "driver_bidding_pay_report.php?" + formValus);
        ShpSq6fAm7($("#_list_form"));
        document._list_form.submit();
        $("#_list_form").attr("action", act);
        return true;
    }

    function show_driver_bank_details(driverid, provideName, acName, bankName, acNumber, sortCode) {
        $("#provideName").text("");
        $("#pacName,#pbankName,#pacNumber,#psortcode").html("");
        if (acName == "" && sortCode == "" && bankName == "" && acNumber == "") {
            alert("<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> bank details are not available");
            return false;
        } else {
            $("#detail_modal").modal('show');
            $("#provideName").text(provideName + "'s");
            $("#pacName").html(acName);
            $("#pbankName").html(bankName);
            $("#pacNumber").html(acNumber);
            $("#psortcode").html(sortCode);
        }
    }
</script>
<!-- END BODY-->
</html>

