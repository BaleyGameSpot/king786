<?php
include_once('../common.php');
$tbl_name = 'trips';
if (!$userObj->hasPermission('manage-provider-payment')) {
    $userObj->redirect();
}
$script = 'Deliverall Driver Payment Report';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
//$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$db_drivers = $obj->MySQLSelect("select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName");
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$restaurantAdmin = "Store";
if (isset($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'])) {
    $restaurantAdmin = $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];
}

$ord = ' ORDER BY rd.iDriverId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY rd.iDriverId ASC"; else
        $ord = " ORDER BY rd.iDriverId DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY rd.vName ASC"; else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY rd.vBankAccountHolderName ASC"; else
        $ord = " ORDER BY rd.vBankAccountHolderName DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY rd.vBankName ASC"; else
        $ord = " ORDER BY rd.vBankName DESC";
}
//End Sorting
// Start Search Parameters
$ssql = $ssql1 = '';
if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(tr.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tr.tTripRequestDate) <='" . $endDate . "'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";
    }
}
//Select dates
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
$ssql .= " AND tr.iServiceId IN(" . $enablesevicescategory . ")";
$per_page = $DISPLAY_RECORD_NUMBER;
$sql = "select COUNT( DISTINCT rd.iDriverId ) AS Total from register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'DeliverAll' AND tr.iActive = 'Finished'  $ssql $ssql1 ";
$totalData = $obj->MySQLSelect($sql);

$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End

// ==============================
$sqlAll = "select rd.iDriverId,tr.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,CONCAT(rd.vCode,' ',rd.vPhone)  as user_phone,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code from register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'DeliverAll' AND tr.iActive = 'Finished' $ssql $ssql1 GROUP BY rd.iDriverId $ord";


$db_paymentAll = $obj->MySQLSelect($sqlAll);

$driverIdArr = array_column($db_paymentAll, "iDriverId");
$transferAmountArrAll = getTransforAmountbyDeliveryDriverId($driverIdArr, $ssql, 'Yes');
$transferAmountAllTemp = 0;
for ($i = 0; $i < scount($db_paymentAll); $i++) {
    $transferAmountAll = 0;
    if (isset($transferAmountArrAll[$db_paymentAll[$i]['iDriverId']])) {
        $transferAmountAll = $transferAmountArrAll[$db_paymentAll[$i]['iDriverId']];
    }
    $transferAmountAllTemp = $transferAmountAllTemp + $transferAmountAll;
}
// ==============================
 

$sql = "select rd.iDriverId,tr.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,CONCAT(rd.vCode,' ',rd.vPhone)  as user_phone,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code from register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'DeliverAll' AND tr.iActive = 'Finished' $ssql $ssql1 GROUP BY rd.iDriverId $ord LIMIT $start, $per_page";
$db_payment = $obj->MySQLSelect($sql);

$endRecord = scount($db_payment);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'iDriverId') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
//Added By HJ On 22-09-2020 For Optimize For Loop Query Start
$driverIdArr = array_column($db_payment, "iDriverId");
$transferAmountArr = getTransforAmountbyDeliveryDriverId($driverIdArr, $ssql, 'Yes');
//echo "<pre>";print_r($transferAmountArr);die;
for ($i = 0; $i < scount($db_payment); $i++) {
    $transferAmount = 0;
    if (isset($transferAmountArr[$db_payment[$i]['iDriverId']])) {
        $transferAmount = $transferAmountArr[$db_payment[$i]['iDriverId']];
    }
    $db_payment[$i]['transferAmount'] = $transferAmount;
    //$db_payment[$i]['transferAmount'] = getTransforAmountbyDeliveryDriverId($db_payment[$i]['iDriverId'],$ssql,'Yes');
}
//Added By HJ On 22-09-2020 For Optimize For Loop Query End




?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?=$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?> Payout Report</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?> Payout Report</h2>
                </div>
            </div>
            <hr/>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page">
                    <input type="hidden" name="action" value="search"/>
                    <h3>Search by Date...</h3>
                    <span>
								<a onClick="return todayDate('dp4','dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
								<a onClick="return yesterdayDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
								<a onClick="return currentweekDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
								<a onClick="return previousweekDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
								<a onClick="return currentmonthDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
								<a onClick="return previousmonthDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
								<a onClick="return currentyearDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
								<a onClick="return previousyearDate('dFDate','dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
								</span>
                    <span>
                                                                <!-- changed by me -->
								<input type="text" id="dp4" name="startDate" placeholder="From Date"
                                       class="form-control" value="" readonly=""
                                       style="cursor:default; background-color: #fff"/>
								<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control"
                                       value="" readonly="" style="cursor:default; background-color: #fff"/>
                        
                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text driver_container" name='searchDriver'
                                            data-text="Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?>"
                                            id="searchDriver">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?></option>
                                            <?php foreach ($db_drivers as $dbd) { ?>
                                                <option value="<?php echo $dbd['iDriverId']; ?>" <?php if ($searchDriver == $dbd['iDriverId']) {
                                                    echo "selected";
                                                } ?>><?php echo clearName($dbd['driverName']); ?> - ( <?php echo clearEmail($dbd['vEmail']); ?> )</option>
                                            <?php } ?>
                                    </select>
                                </div>

                                <div class="tripBtns001">
                                <b>
									<input type="submit" value="Search" class="btnalt button11" id="Search"
                                           name="Search" title="Search"/>
									<input type="button" value="Reset" class="btnalt button11"
                                           onClick="window.location.href = 'store_driver_pay_report.php'"/>
									 <?php if (scount($db_payment) > 0 && SITE_TYPE != 'Demo' && $userObj->hasPermission('export-provider-payment')) { ?>
                                         <button type="button" onClick="exportlist()"
                                                 class="export-btn001">Export</button>
                                     <?php } ?> </b>
                                </div>
							</span>
                    <div class="tripBtns001">
                    </div>
                </div>
            </form>
            <form name="_list_form" id="_list_form" class="_list_form" method="post"
                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <input type="hidden" id="actionpay" name="action" value="pay_driver">
                <input type="hidden" name="ePayDriver" id="ePayDriver" value="">
                <input type="hidden" name="prev_start" id="prev_start" value="<?= $startDate ?>">
                <input type="hidden" name="prev_end" id="prev_end" value="<?= $endDate ?>">
                <input type="hidden" name="prev_order" id="prev_order" value="<?= $order ?>">
                <input type="hidden" name="prev_sortby" id="prev_sortby" value="<?= $sortby ?>">
                <input type="hidden" name="prevsearchDriver" id="prevsearchDriver" value="<?= $searchDriver ?>">
                <table class="table table-striped table-bordered table-hover" id="dataTables-example123">
                    <thead>
                    <tr>
                        <th width="22%">
                            <a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {
                                echo $order;
                            } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?>
                                Name <?php if ($sortby == 2) {
                                    if ($order == 0) { ?>
                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                } else { ?>
                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                        </th>
                        <th width="18%">
                            <a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {
                                echo $order;
                            } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?>
                                <br/>
                                Account Name <?php if ($sortby == 3) {
                                    if ($order == 0) { ?>
                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                } else { ?>
                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                        </th>
                        <th width="10%">
                            <a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {
                                echo $order;
                            } else { ?>0<?php } ?>)">Bank Name <?php if ($sortby == 4) {
                                    if ($order == 0) { ?>
                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                } else { ?>
                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                        </th>
                        <th width="12%">Account Number</th>
                        <th width="8%">Sort Code</th>
                        <!-- <th>Expected Amount Pay <br/> to <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT'] ?></th> -->
                        <th width="12%" style="text-align:center;">Final Amount Pay
                            <br/>
                            to <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT'] ?></th>
                        <th width="12%" style="text-align:center;"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT']; ?>
                            <br/>
                            Payment Status
                        </th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (scount($db_payment) > 0) {
                        for ($i = 0; $i < scount($db_payment); $i++) {
                            $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($db_payment[$i]['iDriverId'], "Driver");
                            ?>
                            <tr class="gradeA">
                                <td>
                                    <?php if ($db_payment[$i]['user_phone'] != '') {
                                        ?>
                                       <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_payment[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_payment[$i]['dname']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?> 
                                        <?php
                                       
                                        echo '<br>';
                                        echo '<b>Phone: </b> +' . clearPhone($db_payment[$i]['user_phone']);
                                        echo '<p><b>Wallet Balance:</b> ' . formateNumAsPerCurrency($user_available_balance, '') . '</p>';
                                    } else { ?>
										  <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $db_payment[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_payment[$i]['dname']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?> 
									   <?php } ?>
                                </td>
                                <td><?= ($db_payment[$i]['vBankAccountHolderName'] != "") ? clearName(" " . $db_payment[$i]['vBankAccountHolderName']) : '---'; ?></td>
                                <td><?= ($db_payment[$i]['vBankName'] != "") ? clearName(" " . $db_payment[$i]['vBankName']) : '---'; ?></td>
                                <td><?= ($db_payment[$i]['vAccountNumber'] != "") ? clearName(" " . $db_payment[$i]['vAccountNumber']) : '---'; ?></td>
                                <td><?= ($db_payment[$i]['vBIC_SWIFT_Code'] != "") ? clearName(" " . $db_payment[$i]['vBIC_SWIFT_Code']) : '---'; ?></td>
                                <td align="center">
                                    <?php
                                    if ($db_payment[$i]['transferAmount'] > 0) {
                                        echo formateNumAsPerCurrency($db_payment[$i]['transferAmount'], '');
                                    } else {
                                        echo "---";
                                    }
                                    ?>
                                </td>
                                <td align="center"><?= $db_payment[$i]['eDriverPaymentStatus']; ?>
                                    <br/>
                                    <?php if ($userObj->hasPermission('manage-payment-report')) { ?>
                                        <a href="driver_payment_report.php?action=search&startDate=<?= $startDate; ?>&endDate=<?= $endDate; ?>&searchDriver=<?= $db_payment[$i]['iDriverId']; ?>&searchDriverPayment=Unsettelled"
                                           target="_blank">[View Detail]
                                        </a>
                                    <?php } ?>
                                </td>
                                <td align="center">
                                    <?php if ($db_payment[$i]['eDriverPaymentStatus'] == 'Unsettelled') { ?>
                                        <input class="validate[required]" type="checkbox"
                                               value="<?= $db_payment[$i]['iDriverId'] ?>"
                                               id="iTripId_<?= $db_payment[$i]['iDriverId'] ?>" name="iDriverId[]">
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="gradeA">
                            <td colspan="14" align="right">
                                <div class="row">
									<span style="margin:26px 13px 0 0;">
										<a onClick="javascript:Paytodriver(); return false;" href="javascript:void(0);"><button
                                                    class="btn btn-primary ">Mark As Settled</button></a>
									</span>
                                </div>
                            </td>
                        </tr>
                    <?php } else { ?>
                        <tr class="gradeA">
                            <td colspan="13" style="text-align:center;"> No Payment Details Found.</td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </form>
            <?php include('pagination_n.php'); ?>
            
            <div class="row">
                <div class="col-lg-6 col-lg-offset-6">
                    <div class="admin-notes">
                        <h4>Summary:</h4>
                        <ul>
                            <li><strong>Final Amount Pay to <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT'] ?>: </strong><?= formateNumAsPerCurrency($transferAmountAllTemp, ''); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        </div>
    </div>

</div>
<!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/store_driver_pay_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="action" id="action" value="pay_driver">
</form>
<div class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_DRIVER_COMPANY_TXT'] ?> Details
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
<link rel="stylesheet" href="css/select2/select2.min.css"/>
<script src="js/plugins/select2.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
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

        if ('<?=$startDate?>' != '') {
            $("#dp4").val('<?=$startDate?>');
            $("#dp4").datepicker('update', '<?=$startDate?>');
        }
        if ('<?=$endDate?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate;?>');
            $("#dp5").val('<?= $endDate;?>');
        }
    });

    function setRideStatus(actionStatus) {
        window.location.href = "trip.php?type=" + actionStatus;
    }

    function todayDate() {
        $("#dp4").val('<?= $Today;?>');
        $("#dp5").val('<?= $Today;?>');
    }

    function reset() {
        location.reload();

    }

    function yesterdayDate() {
        $("#dp4").val('<?= $Yesterday;?>');
        $("#dp4").datepicker('update', '<?= $Yesterday;?>');
        $("#dp5").datepicker('update', '<?= $Yesterday;?>');
        $("#dp4").change();
        $("#dp5").change();
        $("#dp5").val('<?= $Yesterday;?>');
    }

    function currentweekDate(dt, df) {
        $("#dp4").val('<?= $monday;?>');
        $("#dp4").datepicker('update', '<?= $monday;?>');
        $("#dp5").datepicker('update', '<?= $sunday;?>');
        $("#dp5").val('<?= $sunday;?>');
    }

    function previousweekDate(dt, df) {
        $("#dp4").val('<?= $Pmonday;?>');
        $("#dp4").datepicker('update', '<?= $Pmonday;?>');
        $("#dp5").datepicker('update', '<?= $Psunday;?>');
        $("#dp5").val('<?= $Psunday;?>');
    }

    function currentmonthDate(dt, df) {
        $("#dp4").val('<?= $currmonthFDate;?>');
        $("#dp4").datepicker('update', '<?= $currmonthFDate;?>');
        $("#dp5").datepicker('update', '<?= $currmonthTDate;?>');
        $("#dp5").val('<?= $currmonthTDate;?>');
    }

    function previousmonthDate(dt, df) {
        $("#dp4").val('<?= $prevmonthFDate;?>');
        $("#dp4").datepicker('update', '<?= $prevmonthFDate;?>');
        $("#dp5").datepicker('update', '<?= $prevmonthTDate;?>');
        $("#dp5").val('<?= $prevmonthTDate;?>');
    }

    function currentyearDate(dt, df) {
        $("#dp4").val('<?= $curryearFDate;?>');
        $("#dp4").datepicker('update', '<?= $curryearFDate;?>');
        $("#dp5").datepicker('update', '<?= $curryearTDate;?>');
        $("#dp5").val('<?= $curryearTDate;?>');
    }

    function previousyearDate(dt, df) {
        $("#dp4").val('<?= $prevyearFDate;?>');
        $("#dp4").datepicker('update', '<?= $prevyearFDate;?>');
        $("#dp5").datepicker('update', '<?= $prevyearTDate;?>');
        $("#dp5").val('<?= $prevyearTDate;?>');
    }

    function exportlist() {
        $("#actionpay").val("export");
        $("#pageForm").attr("action", "export_store_driver_pay_report.php");
        ShpSq6fAm7($("#pageForm"));
        document.pageForm.submit();
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

    $('body').on('keyup', '.select2-search__field', function () {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ($(".select2-results__options").is(".select2-results__message")) {
            $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });

    function formatDesign(item) {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        //console.log(item);
        var selectionText = item.text.split("--");
        if (selectionText[2] != null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2] + '</span>');
        } else if (selectionText[2] == null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if (selectionText[2] != null && selectionText[1] == null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }
        //$(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        return $returnString;
    }

    function formatDesignnew(item) {
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#searchDriver").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Driver',
                            company_id: $('#searchCompany option:selected').val(),
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;

                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");

                        return {
                            results: $.map(data, function (item) {

                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };

                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });

    var sId = '<?= $searchDriver;?>';
    var sSelect = $('select.filter-by-text#searchDriver');
    var itemname;
    var itemid;
    if (sId != '') {
        
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Driver',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelect.append(option).trigger('change');
            } else {
                
            }
        });
    }
</script>
</body>
<!-- END BODY-->
</html>
