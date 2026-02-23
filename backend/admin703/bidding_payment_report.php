<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-provider-bidding-payment-report')) {
    $userObj->redirect();
}

$script = 'Bidding_Payment_Report';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : 'Unsettelled';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$serachBidNo = isset($_REQUEST['serachBidNo']) ? $_REQUEST['serachBidNo'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if($action == 'export') {
    $searchDriver = isset($_REQUEST['prevsearchDriver']) ? $_REQUEST['prevsearchDriver'] : '';
    $searchRider = isset($_REQUEST['prevsearchRider']) ? $_REQUEST['prevsearchRider'] : '';
    $searchDriverPayment = isset($_REQUEST['prevsearchDriverPayment']) ? $_REQUEST['prevsearchDriverPayment'] : '';
    $serachBidNo = isset($_REQUEST['prevserachBidNo']) ? $_REQUEST['prevserachBidNo'] : '';
    $startDate = isset($_REQUEST['prev_start']) ? $_REQUEST['prev_start'] : '';
    $endDate = isset($_REQUEST['prev_end']) ? $_REQUEST['prev_end'] : '';
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

if ($searchRider != '') {
    $ssql .= " AND bp.iUserId ='" . $searchRider . "'";
}

if ($serachBidNo != '') {
    $ssql .= " AND bp.vBiddingPostNo ='" . $serachBidNo . "'";
}
$ord = ' ORDER BY bp.iBiddingPostId DESC';
$per_page = $DISPLAY_RECORD_NUMBER;
/*$sql = "SELECT
        COUNT( DISTINCT rd.iDriverId ) AS Total 
        FROM register_driver AS rd 
        LEFT JOIN bidding_post AS bp ON bp.iDriverId=rd.iDriverId WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql";*/
$sql = "SELECT 
        COUNT(*) AS Total
        FROM bidding_post AS bp 
        JOIN register_user AS ru ON bp.iUserId = ru.iUserId
        WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page);
//page
$start = 0;
$show_page = 1;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
$sql = "SELECT 
        bp.fOutStandingAmount,bp.iBiddingPostId,bp.eDriverPaymentStatus,bp.vTaskStatus,bp.ePaymentOption, bp.dBiddingDate,bp.fCommission,bp.fBiddingAmount,bp.iBiddingPostId,bp.vBiddingPostNo,  CONCAT(ru.vName ,' ',ru.vLastName)  AS user_name,
        rd.iDriverId,ru.iUserId,
        CONCAT(rd.vName ,' ',rd.vLastName)  AS driver_name,ru.vTimeZone
        FROM bidding_post AS bp 
        JOIN register_user AS ru ON bp.iUserId = ru.iUserId
        LEFT JOIN register_driver AS rd ON bp.iDriverId=rd.iDriverId WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql $ord  LIMIT $start, $per_page";
$bidding_post_data = $obj->MySQLSelect($sql);
$BiddingPostData = [];
foreach ($bidding_post_data as $BiddingData) {
    /*------------------Commission-----------------*/
    $BiddingData = biddingWiseCal($BiddingData);
    /*------------------Commission-----------------*/
    $BiddingPostData[$BiddingData['iBiddingPostId']] = $BiddingData;
}
if (isset($BiddingPostData) && !empty($BiddingPostData)) {
    $iBiddingPostIds = array_column($BiddingPostData, "iBiddingPostId");
    $iBiddingPostId = implode(',', $iBiddingPostIds);
    /*------------------bidding_offer-----------------*/
    $query = "SELECT amount,iBiddingPostId FROM bidding_offer WHERE `eStatus` = 'Accepted' AND iBiddingPostId IN (" . $iBiddingPostId . ") ORDER BY `IOfferId`";
    $bidding_final_offer = $obj->MySQLSelect($query);
    if (isset($bidding_final_offer) && !empty($bidding_final_offer)) {
        foreach ($bidding_final_offer as $offer) {
            $BiddingPostData[$offer['iBiddingPostId']]['fBiddingAmount'] = $offer['amount'];
            $BiddingData = biddingWiseCal($BiddingPostData[$offer['iBiddingPostId']]);
            /*------------------Commission-----------------*/
            $BiddingPostData[$offer['iBiddingPostId']] = $BiddingData;
            /*------------------Commission-----------------*/
        }
    }
    /*------------------bidding_offer-----------------*/
}

function biddingWiseCal($BiddingData){

    $fBiddingAmount = $BiddingData['fBiddingAmount'];
    $fCommission_percentage = $BiddingData['fCommission'];
    $BiddingData['fCommissionAmount'] = $fCommission_percentage = ($fBiddingAmount * $fCommission_percentage) / 100;
    $BiddingData['driverPayment'] = $BiddingData['takeFromDriver'] =  $BiddingData['payToDriver'] = 0;
    if ($BiddingData['ePaymentOption'] == 'Cash') {
        $BiddingData['takeFromDriver'] = $fCommission_percentage;
        $BiddingData['driverPayment'] = ($fBiddingAmount - $fCommission_percentage) - $fBiddingAmount;
    } else if ($BiddingData['ePaymentOption'] == 'Card') {
        $BiddingData['driverPayment'] = $BiddingData['payToDriver'] += ($fBiddingAmount - $fCommission_percentage);
    } else if ($BiddingData['ePaymentOption'] == 'Wallet') {
        $BiddingData['driverPayment'] = $BiddingData['payToDriver'] += ($fBiddingAmount - $fCommission_percentage);
    }
    return $BiddingData;
}

function cleanNumber($num)
{
    return str_replace(',', '', $num);
}


/*------------------all-----------------*/
$sql = "SELECT 
        bp.fOutStandingAmount,bp.iBiddingPostId,bp.eDriverPaymentStatus,bp.vTaskStatus,bp.ePaymentOption, bp.dBiddingDate,bp.fCommission,bp.fBiddingAmount,bp.iBiddingPostId,bp.vBiddingPostNo,  CONCAT(ru.vName ,' ',ru.vLastName)  AS user_name,
        rd.iDriverId,ru.iUserId,
        CONCAT(rd.vName ,' ',rd.vLastName)  AS driver_name
        FROM bidding_post AS bp 
        JOIN register_user AS ru ON bp.iUserId = ru.iUserId
        LEFT JOIN register_driver AS rd ON bp.iDriverId=rd.iDriverId WHERE bp.eDriverPaymentStatus='$searchDriverPayment' $ssql $ord";
$bidding_post_data2 = $obj->MySQLSelect($sql);
$BiddingPostData2 = [];
foreach ($bidding_post_data2 as $BiddingData) {
    $BiddingPostData2[$BiddingData['iBiddingPostId']] = $BiddingData;
}
if (isset($BiddingPostData2) && !empty($BiddingPostData2)) {
    $iBiddingPostIds = array_column($BiddingPostData2, "iBiddingPostId");
    $iBiddingPostId2 = implode(',', $iBiddingPostIds);
    /*------------------bidding_offer-----------------*/
    $query = "SELECT amount,iBiddingPostId FROM bidding_offer WHERE `eStatus` = 'Accepted' AND iBiddingPostId IN (" . $iBiddingPostId2 . ") ORDER BY `IOfferId`";
    $bidding_final_offer = $obj->MySQLSelect($query);
    if (isset($bidding_final_offer) && !empty($bidding_final_offer)) {
        foreach ($bidding_final_offer as $offer) {
            $BiddingPostData2[$offer['iBiddingPostId']]['fBiddingAmount'] = $offer['amount'];
        }
    }
    /*------------------bidding_offer-----------------*/
}
$bidding_amount_cal = $BIDDING_OBJ->driverPaymentCal($BiddingPostData2);

/*------------------all-----------------*/


$systemTimeZone = date_default_timezone_get();
if(isset($action) && !empty($action) && $action == 'export')
{
    $header = '';
    $header .= $langage_lbl_admin['LBL_RIDE_NO_ADMIN'] . "\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "\t";
    $header .= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] . "\t";
    $header .= 'Job Date' . "\t";
    $header .= 'Total Fare' . "\t";
    $header .= 'Commission Amount' . "\t";
    $header .= 'Outstanding Amount' . "\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] .' pay / Take Amount' . "\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] .' Status' . "\t";
    $header .= 'Payment method' . "\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] .' Payment Status' . "\t";


    foreach ($BiddingPostData as $data1) {
        $data .= $data1['vBiddingPostNo'] . "\t";
        $data .= $data1['driver_name'] . "\t";
        $data .= $data1['user_name'] . "\t";

        $date_format_data_array = array(
            'tdate' => (!empty($data1['vTimeZone'])) ? converToTz($data1['dBiddingDate'], $data1['vTimeZone'], $systemTimeZone) : $data1['dBiddingDate'],
            'langCode' => $default_lang,
            'DateFormatForWeb' => 1
        );
        $get_dBiddingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($data1['vTimeZone'],$date_format_data_array['tdate']).")";

        $data .= $get_dBiddingDate_format['tDisplayDateTime'].$time_zone_difference_text."\t";//DateTime($data1['dBiddingDate'], '7') . "\t";
        $data .= formateNumAsPerCurrency($data1['fBiddingAmount'], '' ) . "\t";
        $data .= formateNumAsPerCurrency($data1['fCommissionAmount'], '' ) . "\t";
        $data .= '-' . "\t";
        $data .= formateNumAsPerCurrency($data1['driverPayment'], '' ) . "\t";
        $data .= $data1['vTaskStatus'] . "\t";
        $data .= $data1['ePaymentOption'] . "\t";
        $data .= $data1['eDriverPaymentStatus'] . "\t";
        $data .= "\n";
    }

    $data = str_replace("\r", "", $data);
    ob_clean();
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=bid_payment_reports.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Payment Report</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style>
        .setteled-class {
            background-color: #bddac5
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Payment Report (Bidding)</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search"/>

                    <span>
                        <a style="cursor:pointer"
                           onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                        <a style="cursor:pointer"
                           onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
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

                        <div class="col-lg-3 select001">
                            <select class="form-control filter-by-text" name='searchRider'
                                    data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>"
                                    id="searchRider">
                                <option value="">
                                    Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                            </select>
                        </div>
                    </span>
                </div>
                <div class="row payment-report payment-report1 payment-report2">

                    <div class="col-lg-3">
                        <select class="form-control" name='searchPaymentType'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select Payment Types</option>
                            <option value="Cash" <? if ($searchPaymentType == "Cash") { ?>selected <? } ?>>Cash</option>
                            <?php
                            $payMethod = "Card";
                            if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
                                $payMethod = "Wallet";
                            } ?>
                            <option value="Card"
                                    <? if ($searchPaymentType == "Card") { ?>selected <? } ?>><?= $payMethod; ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-control" name='searchDriverPayment'
                                data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                            <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment
                                Status
                            </option>
                            <option value="Settelled"
                                    <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Settled
                            </option>
                            <option value="Unsettelled"
                                    <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Unsettled
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <input type="text" id="serachBidNo" name="serachBidNo"
                               placeholder="<?php echo $langage_lbl_admin['LBL_BIDDING_TXT']; ?> Number"
                               class="form-control search-trip001" value="<?php echo $serachBidNo; ?>"/>
                    </div>
                </div>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'bidding_payment_report.php'"/>
                        <?php if (scount($BiddingPostData) > 0 && $userObj->hasPermission('export-provider-bidding-payment-report')) { ?>
                            <button type="button" onClick="exportlist()" class="export-btn001">
                                Export
                            </button>
                        <?php } ?>
                    </b>
                </div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form name="_list_form" id="_list_form" class="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                                <input type="hidden" name="iTripId" id="iTripId" value="">
                                <input type="hidden" name="ePayDriver" id="ePayDriver" value="">


                                <input type="hidden" name="prev_start" id="prev_start" value="<?= $startDate ?>">
                                <input type="hidden" name="prev_end" id="prev_end" value="<?= $endDate ?>">
                                <input type="hidden" name="prevsearchDriver" id="prevsearchDriver" value="<?= $searchDriver ?>">
                                <input type="hidden" name="prevsearchCompany" id="prevsearchCompany" value="<?= $searchCompany ?>">
                                <input type="hidden" name="prevsearchRider" id="prevsearchRider" value="<?= $searchRider ?>">
                                <input type="hidden" name="prevsearchDriverPayment" id="prevsearchDriverPayment" value="<?= $searchDriverPayment ?>">
                                <input type="hidden" name="prevserachBidNo" id="prevserachBidNo" value="<?= $serachBidNo ?>">

                                <input type="hidden" id="actionpay" name="action" value="">

                                <table class="table table-bordered" id="dataTables-example123">
                                    <thead>
                                    <?php $colspan_count = 12; ?>
                                    <tr>
                                        <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>
                                        <th width="10%">
                                            Provider/User
                                        </th>
                                        <th width="10%">
                                            Job Date
                                        </th>
                                        <th style="text-align:center;">Total Fare</th>
                                        <th style="text-align:center;">Commission Amount</th>
                                        <th style="text-align:center;">
                                            Outstanding Amount
                                        </th>
                                        <th style="text-align:center;">
                                            <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> pay / Take Amount
                                        </th>

                                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?>
                                            Status
                                        </th>
                                        <th style="text-align:center;">Payment method</th>
                                        <th style="text-align:center;"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                            Payment Status
                                        </th>
                                        <th width="150px">Settle</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $set_unsetarray = array();
                                    if (scount($BiddingPostData) > 0) {
                                        $systemTimeZone = date_default_timezone_get();
                                        foreach ($BiddingPostData as $data){
                                            $set_unsetarray[] = $BiddingData['eDriverPaymentStatus'];
                                            $date_format_data_array = array(
                                                'tdate' => (!empty($data['vTimeZone'])) ? converToTz($data['dBiddingDate'], $data['vTimeZone'], $systemTimeZone) : $data['dBiddingDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_dBiddingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($data['vTimeZone'],$date_format_data_array['tdate']).")";
                                        ?>


                                        <tr class="gradeA">
                                            <td>
                                                <?php  $link_page = "invoice_bids.php?iBiddingPostId=" . $data['iBiddingPostId']; ?>

                                                <a href="<?= $link_page ?>">
                                                <?php echo $data['vBiddingPostNo']; ?> </a></td>
                                            <td>
                                                <b><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> </b>
                                                <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $data['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data['driver_name']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?>
                                                <br>
                                                <b><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?> </b>
                                                <?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $data['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data['user_name']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
                                            </td>
                                            <td><?= $get_dBiddingDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($data['dBiddingDate'], '7'); ?></td>
                                            <td><?= formateNumAsPerCurrency($data['fBiddingAmount'], ''); ?></td>
                                            <td><?= formateNumAsPerCurrency($data['fCommissionAmount'], ''); ?></td>
                                            <td><?= formateNumAsPerCurrency($data['fOutStandingAmount'], ''); ?></td>
                                            <td><?= formateNumAsPerCurrency($data['driverPayment'], ''); ?> </td>
                                            <td><?= $data['vTaskStatus']; ?> </td>
                                            <td><?= $data['ePaymentOption']; ?> </td>
                                            <td><?= $data['eDriverPaymentStatus']; ?> </td>
                                            <td><?
                                                if ($data['eDriverPaymentStatus'] == "Settelled") {
                                                } else if ($data['eDriverPaymentStatus'] == 'Unsettelled') {
                                                    ?>
                                                    <input class="validate[required]" type="checkbox"
                                                           value="<?= $data['iBiddingPostId'] ?>"
                                                           id="iBiddingPostId_<?= $data['iBiddingPostId'] ?>"
                                                           name="iBiddingPostId[]">
                                                    <?
                                                }
                                                ?>
                                            </td>
                                        </tr>

                                    <?php } } else { ?>
                                        <tr class="gradeA">
                                            <td colspan="10" style="text-align:center;">No Payment Details Found.</td>
                                        </tr>
                                    <?php } ?>
                                    
                                    <tr>
                                        <?php $colspan_count = 10; ?>
                                        <td colspan="<?= $colspan_count ?>"></td>
                                        <?php if (in_array("Unsettelled", $set_unsetarray)) { ?>
                                            <td align="right">
                                                <a onClick="Paytodriver()" href="javascript:void(0);" class="btn btn-primary">Mark As Settled</a>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-lg-offset-6">
                    <div class="admin-notes">
                        <h4>Summary:</h4>
                        <ul>
                            <li><strong>Total Fare: </strong><?= formateNumAsPerCurrency(cleanNumber($bidding_amount_cal['total_amount']), ''); ?>
                            </li>

                            <li><strong>Total Cash
                                    Received: </strong><?= formateNumAsPerCurrency(cleanNumber($bidding_amount_cal['total_cash_received']), ''); ?>
                            </li>

                            <li><strong>Total Commission
                                    Amount: </strong><?= formateNumAsPerCurrency(cleanNumber($bidding_amount_cal['total_admin_commission']), ''); ?>
                            </li>


                            <li><strong>Total
                                        Tip: </strong><?= formateNumAsPerCurrency(cleanNumber($bidding_amount_cal['total_tip']), ''); ?></li>


                            <li><strong>Total Trip/Job Outstanding
                                    Amount: </strong><?= formateNumAsPerCurrency(cleanNumber($bidding_amount_cal['total_Outstanding']), ''); ?>
                            </li>


                            <li><strong>Total Driver pay / Take
                                    Amount: </strong><?= formateNumAsPerCurrency(cleanNumber($bidding_amount_cal['total_driver_payment']), ''); ?>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/bidding_payment_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>">
    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>">
    <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>">
    <input type="hidden" name="serachBidNo" value="<?php echo $serachBidNo; ?>">
    <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>">
    <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="eType" value="<?php echo $eType; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<div class="modal fade" id="fare_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <span id="fareRideNo"></span>
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id='faredata'></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <!--<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>-->
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/rider-icon.png" alt="">
                    </i>
                    <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail"></div>
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
<?php include_once('footer.php'); ?>
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
        $("#_list_form").attr("action", "bidding_payment_report.php");
        document._list_form.submit();
       // $("#_list_form").attr("action", act);
        return true;
    }

</script>
<!-- END BODY-->
</html>