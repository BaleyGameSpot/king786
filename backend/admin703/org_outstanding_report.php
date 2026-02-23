<?php
include_once('../common.php');


if (!$userObj->hasPermission('view-org-outstanding-report')) {
    $userObj->redirect();
}
$script = 'org_outstanding_report';
function cleanNumber($num) {
    return str_replace(',', '', $num);
}

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
//End Sorting
// Start Search Parameters
$ssql = '';
$ssqlsearchSettle = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchOrganization = isset($_REQUEST['searchOrganization']) ? $_REQUEST['searchOrganization'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$searchSettleUnsettle = isset($_REQUEST['searchSettleUnsettle']) ? $_REQUEST['searchSettleUnsettle'] : '1';
//$searchPaidby = $_REQUEST['searchPaidby'] ?? '';
$searchPaidby = 'org';
//echo $searchPaidby;exit;
$searchSettleUnsettlePagination = $searchSettleUnsettle;
$ssql = '';
if($searchPaidby=='org') {
    if($searchSettleUnsettle == '1'){
        //$ssql1 = " AND toa1.ePaidByOrganization ='No' ";
        $ssql1 = "AND (toa1.eBillGenerated ='No' AND toa1.ePaidByOrganization ='No')";
    } else if($searchSettleUnsettle == '0'){
        //$ssql1 = " AND toa1.ePaidByOrganization ='Yes' ";
        $ssql1 = "AND (toa1.eBillGenerated ='Yes' OR toa1.ePaidByOrganization ='Yes')";
    } else if($searchSettleUnsettle == '-1'){
        //$ssql1 = " AND toa1.ePaidByOrganization ='Yes' ";
        $ssql1 = "AND (toa1.eBillGenerated ='Yes' OR toa1.ePaidByOrganization ='Yes') ";
    }
} else {
    if($searchSettleUnsettle == '1'){
        $ssql1 = "AND toa1.ePaidByPassenger ='No' ";
    } else if($searchSettleUnsettle == '0'){
        $ssql1 = "AND toa1.ePaidByPassenger ='Yes' ";
    } else if($searchSettleUnsettle == '-1'){
        $ssql1 = "AND toa1.ePaidByPassenger ='Yes' ";
    } 
}
if ($searchOrganization != '') {
    $ssql .= "AND toa.iOrganizationId ='" . $searchOrganization . "'";
}
$sqlPaidby = $sqlPaidbysub = '';
if($searchPaidby=='org') {
    $sqlPaidbysub = "AND toa1.vTripPaymentMode = 'Organization'";
    $sqlPaidby = "AND toa.vTripPaymentMode = 'Organization'";
} else {
    $sqlPaidbysub = " AND toa1.vTripPaymentMode != 'Organization'";
    $sqlPaidby = "AND toa.vTripPaymentMode != 'Organization'";
}
$trp_ssql = "ORDER BY org.vCompany ASC";
//Pagination Start

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if($searchSettleUnsettle == '1'){
    $sql = "SELECT org.vCompany,org.vEmail,org.vCode,org.vPhone, toa.iOrganizationId,COUNT(toa.iTripOutstandId) AS Total, SUM(toa.fPendingAmount) as allSum, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '') as Remaining from trip_outstanding_amount AS toa LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId HAVING remaining>0 $trp_ssql";
    
    //$sql = "SELECT org.vCompany, toa.iOrganizationId,toa.iUserId,concat(ru.vName,' ',ru.vLastName) as riderName,ru.vCurrencyPassenger as userCurrency,cur.Ratio as currencyRatio, SUM(toa.fPendingAmount) as allSum, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId $sqlPaidbysub $ssql1 AND toa1.iUserId != '') as Remaining, concat(ru.vName,' ',ru.vLastName) as riderName from trip_outstanding_amount AS toa LEFT JOIN register_user AS ru ON toa.iUserId = ru.iUserId LEFT JOIN currency as cur ON cur.vName=ru.vCurrencyPassenger LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iUserId > 0 AND toa.iUserId != '' $sqlPaidby AND ru.vName!='NULL' $ssql GROUP BY toa.iUserId   HAVING remaining >0 $trp_ssql";
    $sqlAll=$sql;
}else if ($searchSettleUnsettle == '0'){
    $sql = "SELECT org.vCompany,org.vEmail,org.vCode,org.vPhone, toa.iOrganizationId,COUNT(toa.iTripOutstandId) AS Total,SUM(toa.fPendingAmount) AS allSum, (SUM(toa.fPendingAmount)-(SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != ''))as Remaining, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '') as PaidData from trip_outstanding_amount AS toa LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId HAVING allSum=PaidData $trp_ssql";
    
    //$sql = "SELECT org.vCompany, toa.iOrganizationId,toa.iUserId,concat(ru.vName,' ',ru.vLastName) as riderName,ru.vCurrencyPassenger as userCurrency,cur.Ratio as currencyRatio, SUM(toa.fPendingAmount) AS allSum, (SUM(toa.fPendingAmount)-(SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId $sqlPaidbysub $ssql1 AND toa1.iUserId != ''))as Remaining, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId $sqlPaidbysub $ssql1 AND toa1.iUserId != '') as PaidData,concat(ru.vName,' ',ru.vLastName) as riderName from trip_outstanding_amount AS toa LEFT JOIN register_user AS ru ON toa.iUserId = ru.iUserId  LEFT JOIN currency as cur ON cur.vName=ru.vCurrencyPassenger LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iUserId > 0 AND toa.iUserId != '' $sqlPaidby AND ru.vName!='NULL' $ssql GROUP BY toa.iUserId HAVING allSum=PaidData $trp_ssql";
    $sqlAll=$sql;
}else{
    $sql = "SELECT org.vCompany,org.vEmail,org.vCode,org.vPhone, toa.iOrganizationId,COUNT(toa.iTripOutstandId) AS Total,SUM(toa.fPendingAmount) AS allSum, (SUM(toa.fPendingAmount)-(SELECT (CASE WHEN ISNULL(SUM(toa1.fPendingAmount)) THEN 0 ELSE SUM(toa1.fPendingAmount) END) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '')) as Remaining from trip_outstanding_amount AS toa LEFT JOIN trips AS tr ON tr.iTripId = toa.iTripId LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId $trp_ssql";
    
    //$sql = "SELECT org.vCompany, toa.iOrganizationId,toa.iUserId,COUNT(toa.iTripOutstandId) AS Total,ru.vCurrencyPassenger as userCurrency, cur.Ratio as currencyRatio, SUM(toa.fPendingAmount) AS allSum,(SUM(toa.fPendingAmount)-(SELECT (CASE WHEN ISNULL(SUM(toa1.fPendingAmount)) THEN 0 ELSE SUM(toa1.fPendingAmount) END) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId $sqlPaidbysub $ssql1 AND toa1.iUserId != '')) as Remaining, concat(ru.vName,' ',ru.vLastName) as riderName from trip_outstanding_amount AS toa LEFT JOIN register_user AS ru ON toa.iUserId = ru.iUserId LEFT JOIN trips AS tr ON tr.iTripId = toa.iTripId LEFT JOIN currency as cur ON cur.vName=ru.vCurrencyPassenger LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iUserId > 0 AND toa.iUserId != '' AND ru.vName != '' AND ru.vName != 'NULL' $sqlPaidby $ssql GROUP BY toa.iOrganizationId  $ssql  $trp_ssql";
    $sqlAll=$sql;
}
$totalData = $obj->MySQLSelect($sql);
$total_results = scount($totalData);
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];        //it shows current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
$sql = $sqlAll. " LIMIT $start, $per_page"; // GET DATA PAGE WISE
$db_trip = $obj->MySQLSelect($sql);

$endRecord = scount($db_trip);
    $var_filter = "";
        foreach ($_REQUEST as $key => $val) {
            // UPDATED BY NM ON 29/8/20 BC PAGINATION GOT NO-REQUIRED PARAMETERS
            if (($key != "tpages") && ($key != 'page') && ($key == 'searchSettleUnsettle') || ($key == 'action'))
                $var_filter .= "&$key=" . stripslashes($val);
        }
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

$org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany";
$db_organization = $obj->MySQLSelect($org_sql);


############ Summary ############

$sqlAll = $sqlAll; // All GET DATA 
$db_tripAll = $obj->MySQLSelect($sqlAll);


    if (scount($db_tripAll) > 0) {
                    $AllTotalPendingAll = 0;
                    $AllTotalRemainingPendingAll = 0;
                    $j = 1;
                    for ($i = 0; $i < scount($db_tripAll); $i++) {
                        //$fPendingAmount = ($db_trip[$i]['allSum'] / $db_trip[$i]['currencyRatio']);
                        $fPendingAmountAll = $db_tripAll[$i]['allSum'];
                       
                        //$remainingPendingAmount = ($db_trip[$i]['Remaining'] != '') ? ($db_trip[$i]['Remaining'] / $db_trip[$i]['currencyRatio'] ) :'0';
                        $remainingPendingAmountAll = $db_tripAll[$i]['Remaining'];
                        $AllTotalPendingAll += $fPendingAmountAll;
                        $AllTotalRemainingPendingAll += $remainingPendingAmountAll;

    }
}

############ Summary ############

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Organization Outstanding Amount Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <style>
            .setteled-class{
                background-color:#bddac5;
            }
            .disabled-button{
                background-color:#428bca91 !important;
                cursor: not-allowed !important;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53" >
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
                                <h2>Organization Outstanding Amount Report</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <h3>Search...</h3>
                            <span>
                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text driver_container" name = 'searchOrganization' data-text="Select Organize" id="searchOrganization" style="height: 35px !important;">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_ORGANIZATION']; ?></option>
                                        <?php foreach ($db_organization as $dbd) { ?>
                                            <option value="<?php echo $dbd['iOrganizationId']; ?>" <?php
                                            if ($searchOrganization == $dbd['iOrganizationId']) {
                                                echo "selected";
                                            }
                                            ?>><?php echo clearName($dbd['driverName']); ?> --   <?php echo 'Email: '.clearEmail($dbd['vEmail']);  ?> </option>
                                                <?php } ?>
                                    </select>
                                </div>
                                <div class="col-lg-6"><b>
                                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                    <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'org_outstanding_report.php'"/>
                                    <?php if (scount($db_trip) > 0 && $userObj->hasPermission('export-org-outstanding-report')) { ?>
                                        <button type="button" onClick="reportExportTypes('org_outstanding_amount')" class="export-btn001" >Export</button>
                                    <?php  } ?></b>
                                </div>
                                <!--<div class="col-lg-3">
                                    <select class="form-control filter-by-text" name = "searchSettleUnsettle" data-text="Select <?php echo $langage_lbl_admin['LBL_SETTLE_UNSETTLE_TRANSACTION_ADMIN']; ?>" id="searchSettleUnsettle">
                                        <option value="-1">Select <?php echo $langage_lbl_admin['LBL_SETTLE_UNSETTLE_TRANSACTION_ADMIN']; ?></option>
                                        <option value="0"<?= ($searchSettleUnsettlePagination == '0') ? ' selected ' : ''; ?>><?php echo $langage_lbl_admin['LBL_SETTLED']; ?></option>
                                        <option value="1" <?= ($searchSettleUnsettlePagination == '1') ? ' selected ' : ''; ?>><?php echo $langage_lbl_admin['LBL_UNSETTLED']; ?></option>
                                    </select>
                                </div>-->
                            </span>
                        </div>
                        
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div >
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_user">
                                        <input type="hidden"  name="iTripId" id="iTripId" value="">
                                        <input type="hidden"  name="ePayDriver" id="ePayDriver" value="">
                                        <input type="hidden"  name="searchPaidby" id="searchPaidby" value="<?= $searchPaidby; ?>">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <th width="60%">Organization Contact Details</th>
                                                     <!--<th width="20%"><?= 'Total Outstanding Amount'; ?></th>-->
                                                    <th width="20%" style="text-align: center;">Outstanding Amount</th>
                                                    <th width="15%" style="text-align: center; ">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $set_unsetarray = array();
                                            if (scount($db_trip) > 0) {
                                                $AllTotalprice = 0;
                                                $AllTotalPending = 0;
                                                $AllTotalRemainingPending = 0;
                                                $j = 1;
                                                for ($i = 0; $i < scount($db_trip); $i++) {
                                                    //$fPendingAmount = ($db_trip[$i]['allSum'] / $db_trip[$i]['currencyRatio']);
                                                    $fPendingAmount = $db_trip[$i]['allSum'];
                                                   
                                                    //$remainingPendingAmount = ($db_trip[$i]['Remaining'] != '') ? ($db_trip[$i]['Remaining'] / $db_trip[$i]['currencyRatio'] ) :'0';
                                                    $remainingPendingAmount = $db_trip[$i]['Remaining'];
                                                    $AllTotalPending += $fPendingAmount;
                                                    $AllTotalRemainingPending += $remainingPendingAmount;
                                                    ?>
                                                    <tr class="gradeA <?= $class_setteled ?>">                                                            
                                                        <!--<td width="25%"><a href="javascript:void(0);" onClick="show_org_details('<?= $db_trip[$i]['iOrganizationId']; ?>')" style="text-decoration: underline;"><?= clearName($db_trip[$i]['vCompany']); ?></a><?= !empty($db_trip[$i]['vEmail']) ? " (".$db_trip[$i]['vEmail'].")" : " (-)" ?></td>-->
                                                        <td>
                                                       <?php if ($userObj->hasPermission('view-organization')) { ?><a href="javascript:void(0);" onClick="show_org_details('<?= $db_trip[$i]['iOrganizationId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($db_trip[$i]['vCompany']); ?><?php if ($userObj->hasPermission('view-organization')) { ?></a><?php } ?>
                                                        <?= !empty($db_trip[$i]['vEmail']) ? "<br>".clearEmail($db_trip[$i]['vEmail']) : ''; ?><br>
                                                        <?= clearPhone("(+".$db_trip[$i]['vCode'].") ".$db_trip[$i]['vPhone']); ?></td>
                                                        <!--<td width="20%"><?=  formateNumAsPerCurrency(cleanNumber($fPendingAmount),""); ?></td>-->
                                                        <td align="center"><?= formateNumAsPerCurrency(cleanNumber($remainingPendingAmount),""); ?></td>
                                                        
                                                        <td colspan="3"  align="center"><div class="row payment-report-button">
                                                            <?php 
                                                            $class = $disabled = '';
                                                            if($remainingPendingAmount == '0.00' || $remainingPendingAmount == '' || $remainingPendingAmount == 0 || $remainingPendingAmount == 0.00){
                                                                $disabled = ' disabled ';
                                                                $class = ' disabled-button ';
                                                            } ?>
                                                            <input <?= $disabled ?> class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iOrganizationId'] ?>" id="iTripId_<?= $db_trip[$i]['iOrganizationId'] ?>" name="unsettledUser[]">
                                    
                                                            <!--<span style="margin-right: 15px;">
                                                                <a <?= $disabled ?> onClick="MarkAsSettled('<?= $db_trip[$i]['iUserId']?>','<?=$db_trip[$i]['riderName']?>' )" href="javascript:void(0);"><button  <?= $disabled ?> class="btn-sm btn-primary <?= $class ?>" type="button">Mark As Settled</button></a>
                                                            </span>-->
                                                        </div>
                                                        </td>
                                                    </tr>
                                            <? } ?>
                                                <tr class="gradeA">
                                                    <td align="right"><b>Total</b></td>
                                                    <!--<td><?php formateNumAsPerCurrency(cleanNumber($AllTotalPending),""); ?></td>-->
                                                    <td align="center"><?= formateNumAsPerCurrency(cleanNumber($AllTotalRemainingPending),""); ?></td>
                                                    <td align="center">
                                                        <? if($searchSettleUnsettle!=0) { ?>
                                                            <a onClick="javascript:Paytouser(); return false;" href="javascript:void(0);"><button class="btn btn-primary">Mark As Settled</button></a>
                                                        <? } ?>
                                                    </td>
                                                </tr>
                                            <? } else { ?>
                                                <tr class="gradeA">
                                                    <td align="center" colspan="5">No Records Found.</td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php include('pagination_n.php'); ?>
                                    <!-- ############ Summary ############ -->
                                    <? if(scount($db_tripAll) > 0 ){ ?>
                                        <div class="row">
                                                <div class="col-lg-6 col-lg-offset-6">
                                                    <div class="admin-notes">
                                                        <h4>Summary:</h4>
                                                        <ul>
                                                            <li><strong>Outstanding Amount: </strong><?= formateNumAsPerCurrency($AllTotalRemainingPendingAll, ''); ?></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                        </div>
                                    <? } ?>
                                        <!-- ############ Summary ############ -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="clear"></div>
                    <div class="admin-notes">
                        <h4>Note:</h4>
                        <ul>
                            <li>This will list all the organizations whose payment is remaining for the services taken by their users.</li>
                            <li>This usually happens when user request for a service & organization is responsible to pay for that service.</li>
                            <li>You can communicate with the organization via mentioned contact details externally.</li>
                        </ul>
                    </div>
                    </div>
                    </div>
                    <!--END PAGE CONTENT -->
                    </div>
                    <!--END MAIN WRAPPER -->
                    <form name="pageForm" id="pageForm" action="ajax_settle_outstandingamount.php" method="post" >
                        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
                        <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
                        <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
                        <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
                        <input type="hidden" name="action" value="<?php echo $action; ?>" >
                        <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
                        <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>" >
                        <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>" >
                        <input type="hidden" name="searchSettleUnsettle" value="<?php echo $searchSettleUnsettlePagination; ?>" >
                        <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>" >
                        <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>" >
                        <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>" >
                        <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
                        <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
                        <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >
                        <input type="hidden" name="eType" value="<?php echo $eType; ?>" >
                        <input type="hidden" name="method" id="method" value="" >
                    </form>
                    <div class="modal fade " id="detail_modal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                        <div class="modal-dialog" >
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4>
                                        <i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>Organization Details<button type="button" class="close" data-dismiss="modal">x</button>
                                    </h4>
                                </div>
                                <div class="modal-body" style="max-height: 450px;overflow: auto;">
                                    <div id="imageIcons3">
                                        <div align="center">                                                                       
                                            <img src="default.gif"><br/>                                                            
                                            <span>Retrieving details,please Wait...</span>                       
                                        </div>    
                                    </div>
                                    <div id="org_detail" ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="fare_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                        <div class="modal-dialog" >
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4>
                                        <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
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
                    <div class="row loding-action" id="imageIcon" style="display:none;">
                        <div align="center">
                            <img src="default.gif">
                            <span>Mark as settle is in Process. Please Wait...</span>
                        </div>                                
                    </div>
<?php include_once('footer.php'); ?>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<!-- <link rel="stylesheet" href="css/select2/select2.min.css" />
<script src="js/plugins/select2.min.js"></script> -->
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<?php include_once('searchfunctions.php'); ?>
<script>
        function reset() {
        location.reload();
        }
        
         $('.entypo-export').click(function (e) {
            e.stopPropagation();
            var $this = $(this).parent().find('div');
            $(".openHoverAction-class div").not($this).removeClass('active');
            $this.toggleClass('active');
        });
        $(document).on("click", function (e) {
            if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                $(".show-moreOptions").removeClass("active");
            }
        });
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
    </script>
    </body>
    <!-- END BODY-->
    </html>